"""`flask import-mysql` against a real MariaDB, for both earlier schemas: the original
thesis site (no result ids, plain-text passwords, a NIM allowed twice) and the
hardened PHP site (tests/fixtures/mysql_schema.sql, bcrypt hashes). Skipped
where MariaDB is not installed."""

import shutil
import sqlite3
import subprocess
import time
from pathlib import Path

import bcrypt
import pytest

from ceat import create_app
from ceat.db import init_db

FIXTURES = Path(__file__).parent / "fixtures"
pytestmark = pytest.mark.skipif(not shutil.which("mariadbd"), reason="needs mariadb-server")

ORIGINAL_SCHEMA = """
CREATE TABLE user (id_user INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), age VARCHAR(3), nim VARCHAR(20),
                   password VARCHAR(50));
CREATE TABLE result (id_user INT, waktu VARCHAR(8), tanggal VARCHAR(10), result_visual VARCHAR(32),
    result_numerical VARCHAR(20), result_verbal VARCHAR(33), result_sequential VARCHAR(32),
    result_spatial VARCHAR(70), result_3d VARCHAR(30), result_system VARCHAR(216),
    result_vocabulary VARCHAR(40), result_figurework VARCHAR(30));
CREATE TABLE info_section (name_section VARCHAR(20), type_questions VARCHAR(100), time INT, instruction TEXT);
""" + "".join(
    f"CREATE TABLE section_{name} (question_number INT, question_text TEXT, answer VARCHAR(20)"
    + (", choices_1 TEXT, choices_2 TEXT, choices_3 TEXT, choices_4 TEXT, choices_5 TEXT"
       if name in ("verbal", "vocabulary") else "") + ");"
    for name in ["visual", "numerical", "verbal", "sequential", "spatial", "3d", "system", "vocabulary", "figurework"])


@pytest.fixture(scope="module")
def mariadb(tmp_path_factory):
    work = tmp_path_factory.mktemp("mariadb")
    socket = work / "db.sock"
    subprocess.run(["mariadb-install-db", "--no-defaults", f"--datadir={work / 'data'}",
                    "--auth-root-authentication-method=normal"], check=True, capture_output=True)
    server = subprocess.Popen(["mariadbd", "--no-defaults", f"--datadir={work / 'data'}", f"--socket={socket}",
                               "--skip-networking", "--user=root", f"--pid-file={work / 'db.pid'}"],
                              stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    for _ in range(100):
        if socket.exists():
            break
        time.sleep(0.1)

    def sql(database, script):
        subprocess.run(["mariadb", "--no-defaults", "-uroot", f"--socket={socket}", database],
                       input=script, text=True, check=True)
    sql("", "CREATE DATABASE original; CREATE DATABASE hardened;")
    yield socket, sql
    server.terminate()
    server.wait()


def run_import(tmp_path, socket, database, create=True):
    target = tmp_path / f"{database}.sqlite"
    if create:
        init_db(str(target))
    app = create_app({"SECRET_KEY": "test", "DATABASE": str(target)})
    out = app.test_cli_runner().invoke(args=["import-mysql", f"--socket={socket}", "--user=root",
                                             "--password=", f"--database={database}"])
    return out, sqlite3.connect(target)


def results_values(user_id):
    return f"({user_id}, '09:30:00', '07/10/2021', '{'B' * 32}', '{'K' * 20}', '{'S' * 33}', '{'K' * 32}', " \
           f"'{'B' * 70}', '{'K' * 30}', '{'K' * 216}', '{'K' * 40}', '{'K' * 30}')"


def test_import_the_original_site(mariadb, tmp_path):
    socket, sql = mariadb
    sql("original", ORIGINAL_SCHEMA + (FIXTURES / "questions.sql").read_text()
        + "INSERT INTO user (name, age, nim, password) VALUES ('Ani', '21', '1001', 'plainpass1'),"
          " ('Ani lagi', '21', '1001', 'other'), ('Budi', '19', '2002', 'plainpass2');"
        + f"INSERT INTO result VALUES {results_values(1)}, {results_values(2)}, {results_values(3)};")
    out, db = run_import(tmp_path, socket, "original")
    assert out.exit_code == 0, out.output
    assert "user: 2 imported, 1 with a duplicate NIM left out" in out.output
    assert "result: 2 imported, 1 of left-out users skipped" in out.output
    assert db.execute("SELECT nim, password FROM user ORDER BY nim").fetchall() == [
        ("1001", "plainpass1"), ("2002", "plainpass2")]      # re-hashed at the next login
    assert db.execute("SELECT id_result, id_user, result_visual FROM result").fetchall() == [
        (1, 1, "B" * 32), (2, 3, "B" * 32)]
    assert db.execute("SELECT COUNT(*) FROM section_system").fetchone()[0] == 2
    assert db.execute("SELECT choices_3 FROM section_verbal WHERE question_number = 1").fetchone()[0] == "Daybridge"


def test_import_the_hardened_php_site_and_log_in(mariadb, tmp_path):
    socket, sql = mariadb
    php_hash = bcrypt.hashpw(b"rahasia123", bcrypt.gensalt()).decode().replace("$2b$", "$2y$", 1)
    sql("hardened", (FIXTURES / "mysql_schema.sql").read_text() + (FIXTURES / "questions.sql").read_text()
        + f"INSERT INTO user (name, age, nim, password) VALUES ('Ani', 21, '1001', '{php_hash}');"
        + f"INSERT INTO result (id_user, waktu, tanggal, result_visual, result_numerical, result_verbal, "
          f"result_sequential, result_spatial, result_3d, result_system, result_vocabulary, result_figurework) "
          f"VALUES {results_values(1)};")
    out, db = run_import(tmp_path, socket, "hardened")
    assert out.exit_code == 0, out.output
    assert db.execute("SELECT id_result FROM result").fetchall() == [(1,)]

    again, _ = run_import(tmp_path, socket, "hardened", create=False)
    assert again.exit_code != 0 and "already has users" in again.output

    from conftest import Browser
    app = create_app({"SECRET_KEY": "test", "DATABASE": str(tmp_path / "hardened.sqlite"),
                      "SESSION_COOKIE_SECURE": False})
    browser = Browser(app.test_client())
    browser.get("/login")
    assert browser.post("/login", {"nim": "1001", "password": "rahasia123"}).headers["Location"] == "/"
    assert "/results/1" in browser.text("/results")
    assert db.execute("SELECT password FROM user").fetchone()[0].startswith("scrypt:")
