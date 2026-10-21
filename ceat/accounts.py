"""Registration rules and password checks, free of the database so they can be tested."""

import hmac
import re

import bcrypt
from werkzeug.security import check_password_hash, generate_password_hash

MIN_AGE, MAX_AGE = 10, 99
MIN_PASSWORD_LENGTH, MAX_PASSWORD_LENGTH = 8, 128
MAX_NAME_LENGTH = 100
_NIM = re.compile(r"[0-9]{1,20}")

# checked when the NIM is unknown, so that answer takes as long as a wrong password
_dummy_hash = None


def dummy_hash() -> str:
    global _dummy_hash
    if _dummy_hash is None:
        _dummy_hash = hash_password("not-a-real-password")
    return _dummy_hash


def hash_password(password: str) -> str:
    return generate_password_hash(password)       # scrypt


def validate_registration(form) -> tuple[list[str], dict]:
    """(errors, clean values) for a registration form. Errors are messages for the
    student; the clean values are only meaningful when there are none."""
    name = form.get("name", "").strip()
    nim = form.get("nim", "").strip()
    password = form.get("password", "")
    try:
        age = int(form.get("age", ""))
    except ValueError:
        age = None

    errors = []
    if not name or len(name) > MAX_NAME_LENGTH:
        errors.append(f"Nama harus diisi, paling panjang {MAX_NAME_LENGTH} karakter.")
    if not _NIM.fullmatch(nim):
        errors.append("NIM hanya berisi angka, paling panjang 20 digit.")
    if age is None or not MIN_AGE <= age <= MAX_AGE:
        errors.append(f"Usia harus angka antara {MIN_AGE} dan {MAX_AGE}.")
    if not MIN_PASSWORD_LENGTH <= len(password) <= MAX_PASSWORD_LENGTH:
        errors.append(f"Kata sandi minimal {MIN_PASSWORD_LENGTH} dan paling panjang {MAX_PASSWORD_LENGTH} karakter.")
    return errors, {"name": name, "nim": nim, "age": age, "password": password}


def check_password(stored: str, password: str) -> tuple[bool, bool]:
    """(matches, needs a new hash) for a stored password.

    Besides this site's own hashes it accepts what an imported database holds:
    bcrypt hashes from the PHP version ($2y$...) and plain text from the original
    one. Those log in, and the caller then replaces them with a new hash."""
    if stored.startswith(("scrypt:", "pbkdf2:")):
        return check_password_hash(stored, password), False
    if stored.startswith(("$2y$", "$2b$", "$2a$")):
        try:
            ok = bcrypt.checkpw(password.encode()[:72], ("$2b$" + stored[4:]).encode())
        except ValueError:
            ok = False
        return ok, ok
    ok = stored != "" and hmac.compare_digest(stored.encode(), password.encode())
    return ok, ok
