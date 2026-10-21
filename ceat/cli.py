"""Commands: `flask --app ceat init` and `flask --app ceat import-mysql`."""

import secrets
import sqlite3
from pathlib import Path

import click

from .db import init_db
from .sections import SECTIONS

QUESTION_TABLES = [f"section_{name}" for name in SECTIONS]
CHOICE_COLUMNS = [f"choices_{i}" for i in range(1, 6)]


def register_commands(app):
    @app.cli.command("init")
    def init():
        """Write instance/config.py with a new secret key, and create the database."""
        instance = Path(app.instance_path)
        instance.mkdir(parents=True, exist_ok=True)
        config = instance / "config.py"
        if not config.exists():
            config.write_text(f"SECRET_KEY = {secrets.token_hex(32)!r}\n")
            config.chmod(0o600)
            click.echo(f"wrote {config}")
        database = Path(app.config["DATABASE"])
        if database.exists():
            click.echo(f"{database} exists; left as it is")
        else:
            init_db(str(database))
            click.echo(f"created {database}")

    @app.cli.command("import-mysql")
    @click.option("--host", default="localhost")
    @click.option("--port", default=3306)
    @click.option("--socket", default=None, help="a Unix socket instead of host and port")
    @click.option("--user", required=True)
    @click.option("--password", prompt=True, hide_input=True, default="")
    @click.option("--database", required=True)
    def import_mysql(host, port, socket, user, password, database):
        """Copy users, results and questions from the MySQL database of the PHP site
        into the (new, empty) SQLite database."""
        try:
            import pymysql
        except ImportError:
            raise click.ClickException("needs PyMySQL: pip install pymysql")
        source = pymysql.connect(host=host, port=port, unix_socket=socket, user=user, password=password,
                                 database=database, cursorclass=pymysql.cursors.DictCursor)
        target = sqlite3.connect(app.config["DATABASE"])
        target.execute("PRAGMA foreign_keys = ON")
        for line in import_database(source, target):
            click.echo(line)


def _rows(source, table: str) -> list[dict]:
    with source.cursor() as cursor:
        cursor.execute(f"SELECT * FROM `{table}`")
        return list(cursor.fetchall())


def import_database(source, target: sqlite3.Connection):
    """Copy the PHP site's tables into an empty SQLite database; yields a report line per table."""
    if target.execute("SELECT COUNT(*) FROM user").fetchone()[0]:
        raise click.ClickException("the SQLite database already has users; import into a new one")

    with target:
        kept, duplicates = {}, 0
        for row in _rows(source, "user"):
            if row["nim"] in {u["nim"] for u in kept.values()}:
                duplicates += 1           # the old schema allowed a NIM twice; the first account stays
                continue
            kept[row["id_user"]] = row
            # passwords are copied as they are (bcrypt or plain text) and re-hashed at the next login
            target.execute("INSERT INTO user (id_user, name, age, nim, password) VALUES (?, ?, ?, ?, ?)",
                           (row["id_user"], row["name"], int(row["age"]), str(row["nim"]), row["password"] or ""))
        yield f"user: {len(kept)} imported, {duplicates} with a duplicate NIM left out"

        imported = skipped = 0
        result_columns = ["waktu", "tanggal"] + [f"result_{name}" for name in SECTIONS]
        for row in _rows(source, "result"):
            if row["id_user"] not in kept:
                skipped += 1
                continue
            columns = ["id_user"] + result_columns
            values = [row["id_user"]] + [row[c] for c in result_columns]
            if "id_result" in row:            # the original version had no result ids: new ones are assigned
                columns.insert(0, "id_result")
                values.insert(0, row["id_result"])
            target.execute(f"INSERT INTO result ({', '.join(columns)}) VALUES ({', '.join('?' * len(values))})",
                           values)
            imported += 1
        yield f"result: {imported} imported, {skipped} of left-out users skipped"

        info = _rows(source, "info_section")
        target.executemany("INSERT INTO info_section (name_section, type_questions, time, instruction) "
                           "VALUES (:name_section, :type_questions, :time, :instruction)",
                           [{"instruction": None, **row} for row in info])
        yield f"info_section: {len(info)} imported"

        for table in QUESTION_TABLES:
            rows = _rows(source, table)
            for row in rows:
                columns = ["question_number", "question_text", "answer"]
                columns += [c for c in CHOICE_COLUMNS if c in row and table in ("section_verbal", "section_vocabulary")]
                target.execute(f'INSERT INTO "{table}" ({", ".join(columns)}) VALUES ({", ".join("?" * len(columns))})',
                               [str(row[c]) if c != "question_number" and row[c] is not None else row[c]
                                for c in columns])
            yield f"{table}: {len(rows)} imported"
