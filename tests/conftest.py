import re

import pytest
from django.test import Client

from assessment import views
from assessment.models import Question, SectionInfo

# two questions per section, enough to walk through every kind of answer
QUESTIONS = {
    "visual": [("Find the one that is most <i>different</i>", "2"), ("Which shape comes next?", "1")],
    "numerical": [("Angka yang hilang?", "4"), ("Angka yang hilang?", "12")],
    "verbal": [("Which town is furthest east?", "1", ["Newport", "Flatpeak", "Daybridge"]),
               ("Who is the tallest?", "3", ["A", "B", "C", "D", "E"])],
    "sequential": [("Isi 1 dan 2", "ED"), ("Isi 1 dan 2", "AB")],
    "spatial": [("Alternatif?", "NNYYN"), ("Alternatif?", "YYYYY")],
    "3d": [("Sisi A?", "iii"), ("Sisi B?", "i")],
    "system": [("Tulis hurufnya", "GFHKKBJDLGAC"), ("Tulis hurufnya", "ABCDEFGHIJKL")],
    "vocabulary": [("Memiliki", "1", ["Menguasai", "Membayar", "Merelakan", "Melepas"]),
                   ("Membeli", "2", ["Menjual", "Membayar", "Meminjam", "Memberi"])],
    "figurework": [("Berapa hasil dari 1/5?", "0.20"), ("Berapa 1/4?", "0.25")],
}
PASSWORD = "rahasia123"


@pytest.fixture
def questions(db):
    for section, items in QUESTIONS.items():
        SectionInfo.objects.create(section=section, type_questions="Contoh")
        for number, (text, answer, *choices) in enumerate(items, 1):
            extra = {f"choice_{i}": c for i, c in enumerate(choices[0] if choices else [], 1)}
            Question.objects.create(section=section, number=number, text=text, answer=answer, **extra)


class Clock:
    """Replaces the site's clock (views.now) so time limits can be tested without waiting."""

    def __init__(self):
        from django.utils import timezone
        self.t = timezone.now()

    def __call__(self):
        return self.t

    def advance(self, seconds):
        from datetime import timedelta
        self.t += timedelta(seconds=seconds)


@pytest.fixture
def clock(monkeypatch):
    clock = Clock()
    monkeypatch.setattr(views, "now", clock)
    return clock


class Browser:
    """A client that checks CSRF like a real server and posts the last page's token along."""

    def __init__(self):
        self.client = Client(enforce_csrf_checks=True)
        self.csrf = ""

    def get(self, path):
        response = self.client.get(path)
        match = re.search(r'name="csrfmiddlewaretoken" value="([^"]+)"', response.content.decode())
        if match:
            self.csrf = match.group(1)
        return response

    def post(self, path, data=None, csrf=True):
        data = dict(data or {})
        if csrf:
            data.setdefault("csrfmiddlewaretoken", self.csrf)
        return self.client.post(path, data)

    def text(self, path):
        return self.get(path).content.decode()

    def register(self, name, nim, age, password=PASSWORD):
        self.get("/register")
        return self.post("/register", {"name": name, "nim": nim, "age": age, "password": password})

    def login(self, nim, password=PASSWORD):
        self.get("/login")
        return self.post("/login", {"nim": nim, "password": password})

    def answer(self, section, number, **fields):
        """Open the question (which starts its section's clock), then answer it."""
        self.get(f"/test/{section}/{number}")
        return self.post(f"/test/{section}/{number}", fields)


@pytest.fixture
def browser(questions, clock):
    return Browser()


def location(response) -> str:
    return f"{response.status_code} {response.headers.get('Location', '')}"
