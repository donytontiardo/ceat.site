"""manage.py import_mysql against a real MariaDB, for both earlier schemas: the
original thesis site (no result ids, plain-text passwords, a NIM allowed twice)
and the hardened PHP site (tests/fixtures/mysql_schema.sql, bcrypt hashes).
Skipped where MariaDB is not installed."""

import shutil
import subprocess
import time
from io import StringIO
from pathlib import Path

import bcrypt
import pytest
from django.core.management import CommandError, call_command

from assessment.models import Attempt, Question, SectionRun, Student

from .conftest import Browser

FIXTURES = Path(__file__).parent / "fixtures"
SECTION_NAMES = ["visual", "numerical", "verbal", "sequential", "spatial", "3d", "system", "vocabulary", "figurework"]
pytestmark = [pytest.mark.skipif(not shutil.which("mariadbd"), reason="needs mariadb-server"), pytest.mark.django_db]

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
    for name in SECTION_NAMES)


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


def run_import(socket, database):
    out = StringIO()
    call_command("import_mysql", socket=str(socket), user="root", password="", database=database, stdout=out)
    return out.getvalue()


def result_values(user_id):
    return (f"({user_id}, '09:30:00', '07/10/2021', '{'B' * 32}', '{'K' * 20}', '{'S' * 33}', '{'K' * 32}', "
            f"'{'B' * 70}', '{'K' * 30}', '{'K' * 216}', '{'K' * 40}', '{'K' * 30}')")


def test_import_the_original_site(mariadb):
    socket, sql = mariadb
    sql("original", ORIGINAL_SCHEMA + (FIXTURES / "questions.sql").read_text()
        + "INSERT INTO user (name, age, nim, password) VALUES ('Ani', '21', '1001', 'plainpass1'),"
          " ('Ani lagi', '21', '1001', 'other'), ('Budi', '19', '2002', 'plainpass2');"
        + f"INSERT INTO result VALUES {result_values(1)}, {result_values(2)}, {result_values(3)};")
    out = run_import(socket, "original")
    assert "students: 2 imported, 1 with a duplicate NIM left out" in out
    assert "tests: 2 imported, 1 of left-out students skipped" in out

    ani = Student.objects.get(nim="1001")
    assert ani.password.startswith("pbkdf2_sha256$"), "plain text is hashed during the import"
    assert ani.check_password("plainpass1")
    attempt = Attempt.objects.get(student=ani)
    assert attempt.started_at.strftime("%d/%m/%Y") == "07/10/2021"
    assert attempt.runs.get(section="visual").marks == "B" * 32
    assert SectionRun.objects.count() == 2 * 9
    assert Question.objects.filter(section="system").count() == 2
    assert Question.objects.get(section="verbal", number=1).choice_3 == "Daybridge"


def test_import_the_hardened_php_site_and_log_in(mariadb):
    socket, sql = mariadb
    php_hash = bcrypt.hashpw(b"rahasia123", bcrypt.gensalt()).decode().replace("$2b$", "$2y$", 1)
    sql("hardened", (FIXTURES / "mysql_schema.sql").read_text() + (FIXTURES / "questions.sql").read_text()
        + f"INSERT INTO user (name, age, nim, password) VALUES ('Ani', 21, '1001', '{php_hash}');"
        + "INSERT INTO result (id_user, waktu, tanggal, result_visual, result_numerical, result_verbal, "
          "result_sequential, result_spatial, result_3d, result_system, result_vocabulary, result_figurework) "
          f"VALUES {result_values(1)};")
    run_import(socket, "hardened")
    with pytest.raises(CommandError, match="already has students"):
        run_import(socket, "hardened")

    browser = Browser()
    browser.get("/login")
    assert browser.post("/login", {"nim": "1001", "password": "rahasia123"}).headers["Location"] == "/"
    attempt = Attempt.objects.get()
    assert f"/results/{attempt.id}" in browser.text("/results")
    assert "Hasil Akhir" in browser.text(f"/results/{attempt.id}")
    assert Student.objects.get().password.startswith("pbkdf2_sha256$"), "bcrypt replaced at login"
