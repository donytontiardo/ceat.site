import re
import sqlite3
from pathlib import Path

import pytest

from ceat import create_app, db
from ceat.db import init_db

FIXTURES = Path(__file__).parent / "fixtures"


class Clock:
    """Replaces the site's clock (db.now) so time limits can be tested without waiting."""

    def __init__(self, start=1_800_000_000):
        self.t = start

    def __call__(self):
        return self.t

    def advance(self, seconds):
        self.t += seconds


@pytest.fixture
def clock(monkeypatch):
    clock = Clock()
    monkeypatch.setattr(db, "now", clock)
    return clock


@pytest.fixture
def app(tmp_path, clock):
    database = tmp_path / "ceat.sqlite"
    init_db(str(database))
    with sqlite3.connect(database) as conn:
        conn.executescript((FIXTURES / "questions.sql").read_text())
    return create_app({"SECRET_KEY": "test", "DATABASE": str(database), "SESSION_COOKIE_SECURE": False,
                       "SECTION_SECONDS": {"system": 2}, "TESTING": True})


@pytest.fixture
def sql(app):
    def run(statement, params=()):
        with sqlite3.connect(app.config["DATABASE"]) as conn:
            return conn.execute(statement, params).fetchall()
    return run


class Browser:
    """A test client that keeps the last page's CSRF token and posts it along."""

    def __init__(self, client):
        self.client = client
        self.csrf = ""

    def get(self, path, **kwargs):
        response = self.client.get(path, **kwargs)
        match = re.search(r'name="csrf" value="([^"]+)"', response.get_data(as_text=True))
        if match:
            self.csrf = match.group(1)
        return response

    def post(self, path, data=None, csrf=True):
        data = dict(data or {})
        if csrf:
            data.setdefault("csrf", self.csrf)
        return self.client.post(path, data=data)

    def text(self, path):
        return self.get(path).get_data(as_text=True)

    def register(self, name, nim, age, password):
        self.get("/register")
        return self.post("/register", {"name": name, "nim": nim, "age": age, "password": password})

    def login(self, nim, password):
        self.get("/login")
        return self.post("/login", {"nim": nim, "password": password})

    def answer(self, section, number, **fields):
        """Open the question (which starts its section's clock), then answer it."""
        self.get(f"/test/{section}/{number}")
        return self.post(f"/test/{section}/{number}", fields)


@pytest.fixture
def browser(app):
    return Browser(app.test_client())


@pytest.fixture
def new_browser(app):
    return lambda: Browser(app.test_client())


def location(response) -> str:
    return f"{response.status_code} {response.headers.get('Location', '')}"
