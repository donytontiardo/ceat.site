"""Every database query of the site. Values are always bound parameters; the only
names put into SQL text are section names and result columns, and those come from
the SECTIONS whitelist, never from the request."""

import sqlite3
import time
from datetime import datetime
from pathlib import Path
from zoneinfo import ZoneInfo

from flask import current_app, g

from .sections import SECTIONS

LOGIN_MAX_FAILURES = 5
LOGIN_WINDOW_SECONDS = 15 * 60


def get_db() -> sqlite3.Connection:
    if "db" not in g:
        g.db = sqlite3.connect(current_app.config["DATABASE"], timeout=10)
        g.db.row_factory = sqlite3.Row
        g.db.execute("PRAGMA foreign_keys = ON")
    return g.db


def close_db(_error=None):
    db = g.pop("db", None)
    if db is not None:
        db.close()


def init_db(path: str):
    """Create a new database at `path` from schema.sql."""
    with sqlite3.connect(path) as db:
        db.execute("PRAGMA journal_mode = WAL")      # readers do not wait for the writer
        db.executescript((Path(__file__).parent / "schema.sql").read_text())


def query(sql: str, params=()) -> sqlite3.Cursor:
    return get_db().execute(sql, params)


def write(sql: str, params=()) -> sqlite3.Cursor:
    db = get_db()
    with db:                                          # commits, or rolls back on error
        return db.execute(sql, params)


def section_table(name: str) -> str:
    if name not in SECTIONS:
        raise ValueError(f"unknown section {name}")
    return f'"section_{name}"'


def now() -> int:
    return int(time.time())


# ── users ──────────────────────────────────────────────────────────────────
def find_user_by_nim(nim: str):
    return query("SELECT id_user, name, nim, age, password FROM user WHERE nim = ?", (nim,)).fetchone()


def find_user(user_id: int):
    return query("SELECT id_user, name, nim, age FROM user WHERE id_user = ?", (user_id,)).fetchone()


def create_user(name: str, age: int, nim: str, password_hash: str):
    write("INSERT INTO user (name, age, nim, password) VALUES (?, ?, ?, ?)", (name, age, nim, password_hash))


def set_password_hash(user_id: int, password_hash: str):
    write("UPDATE user SET password = ? WHERE id_user = ?", (password_hash, user_id))


# ── login sessions and throttling ─────────────────────────────────────────
def create_session(key_hash: str, user_id: int):
    t = now()
    write("INSERT INTO login_session (key_hash, id_user, created_at, seen_at) VALUES (?, ?, ?, ?)",
          (key_hash, user_id, t, t))


def find_session(key_hash: str, idle_seconds: int):
    """The session and its user, if it was used within `idle_seconds`; marks it used now."""
    row = query("""SELECT s.key_hash, s.id_result, u.id_user, u.name, u.nim FROM login_session s
                   JOIN user u ON u.id_user = s.id_user WHERE s.key_hash = ? AND s.seen_at > ?""",
                (key_hash, now() - idle_seconds)).fetchone()
    if row is not None:
        write("UPDATE login_session SET seen_at = ? WHERE key_hash = ?", (now(), key_hash))
    return row


def delete_session(key_hash: str):
    write("DELETE FROM login_session WHERE key_hash = ?", (key_hash,))


def set_session_test(key_hash: str, result_id: int | None):
    write("UPDATE login_session SET id_result = ? WHERE key_hash = ?", (result_id, key_hash))


def login_blocked(nim: str, ip: str) -> bool:
    failures = query("SELECT COUNT(*) FROM login_attempt WHERE (nim = ? OR ip = ?) AND attempted_at > ?",
                     (nim, ip, now() - LOGIN_WINDOW_SECONDS)).fetchone()[0]
    return failures >= LOGIN_MAX_FAILURES


def record_login_failure(nim: str, ip: str):
    write("INSERT INTO login_attempt (nim, ip, attempted_at) VALUES (?, ?, ?)", (nim, ip, now()))
    write("DELETE FROM login_attempt WHERE attempted_at < ?", (now() - 86400,))


def clear_login_failures(nim: str):
    write("DELETE FROM login_attempt WHERE nim = ?", (nim,))


# ── questions ──────────────────────────────────────────────────────────────
def question_count(section: str) -> int:
    counts = g.setdefault("question_counts", {})
    if section not in counts:
        counts[section] = query(f"SELECT COUNT(*) FROM {section_table(section)}").fetchone()[0]
    return counts[section]


def find_question(section: str, number: int):
    s = SECTIONS[section]
    choices = "".join(f", choices_{i}" for i in range(1, s.choices + 1)) if s.input == "text_choice" else ""
    return query(f"SELECT question_number, question_text{choices} FROM {section_table(section)} "
                 "WHERE question_number = ?", (number,)).fetchone()


def correct_answer(section: str, number: int) -> str | None:
    row = query(f"SELECT answer FROM {section_table(section)} WHERE question_number = ?", (number,)).fetchone()
    return None if row is None else str(row[0])


def section_info(section: str) -> dict:
    row = query("SELECT type_questions, time FROM info_section WHERE name_section = ?", (section,)).fetchone()
    return dict(row) if row else {"type_questions": "-", "time": SECTIONS[section].seconds // 60}


# ── results ────────────────────────────────────────────────────────────────
def create_result(user_id: int) -> int:
    started = datetime.now(ZoneInfo(current_app.config["TIMEZONE"]))
    columns = ["id_user", "waktu", "tanggal"] + [f"result_{name}" for name in SECTIONS]
    values = [user_id, started.strftime("%H:%M:%S"), started.strftime("%d/%m/%Y")]
    values += [s.blank_result for s in SECTIONS.values()]
    cursor = write(f"INSERT INTO result ({', '.join(columns)}) VALUES ({', '.join('?' * len(values))})", values)
    return cursor.lastrowid


def find_result(result_id: int, user_id: int):
    """One test of one user: a result id of someone else's test finds nothing."""
    return query("SELECT * FROM result WHERE id_result = ? AND id_user = ?", (result_id, user_id)).fetchone()


def results_of(user_id: int):
    return query("SELECT id_result, waktu, tanggal FROM result WHERE id_user = ? ORDER BY id_result",
                 (user_id,)).fetchall()


def write_marks(result_id: int, user_id: int, section: str, position: int, marks: str):
    """Write `marks` into a section's result column from slot `position` (0-based), in one statement."""
    section_table(section)                           # raises for a section that is not in SECTIONS
    column = f'"result_{section}"'
    write(f"UPDATE result SET {column} = substr({column}, 1, ?) || ? || substr({column}, ?) "
          "WHERE id_result = ? AND id_user = ?",
          (position, marks, position + len(marks) + 1, result_id, user_id))


# ── section clocks ─────────────────────────────────────────────────────────
def deadline(result_id: int, section: str) -> int | None:
    row = query("SELECT deadline FROM section_clock WHERE id_result = ? AND section = ?",
                (result_id, section)).fetchone()
    return None if row is None else row[0]


def start_clock(result_id: int, section: str, seconds: int) -> int:
    """The section's deadline, set now if it has none: a clock never restarts."""
    write("INSERT OR IGNORE INTO section_clock (id_result, section, deadline) VALUES (?, ?, ?)",
          (result_id, section, now() + seconds))
    return deadline(result_id, section)
