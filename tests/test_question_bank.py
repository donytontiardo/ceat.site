"""The built-in questions (assessment/question_bank.py) and manage.py load_questions."""

import re
from io import StringIO
from pathlib import Path

import pytest
from django.core.management import call_command

from assessment import question_bank
from assessment.models import Attempt, Question, SectionInfo
from assessment.sections import MAX_ANSWER_LENGTH, SECTIONS

from .conftest import Browser

pytestmark = pytest.mark.django_db
STATIC = Path(__file__).resolve().parent.parent / "assessment" / "static"


@pytest.fixture
def loaded(db):
    call_command("load_questions", stdout=StringIO())


def test_every_answer_slot_has_a_question(loaded):
    """The grading tables assume each section's full length: its questions must fill it exactly."""
    for name, section in SECTIONS.items():
        assert Question.objects.filter(section=name).count() * section.slots_per_question == section.length, name
    assert SectionInfo.objects.count() == len(SECTIONS)


def test_loading_again_replaces_the_questions(loaded):
    Question.objects.filter(section="verbal", number=1).update(answer="5")
    call_command("load_questions", stdout=StringIO())
    assert Question.objects.get(section="verbal", number=1).answer == "2"
    assert Question.objects.count() == sum(len(q) for q in question_bank.questions(STATIC).values())


ANSWER_FORMS = {
    "numerical": r"\d+",
    "sequential": r"[A-R]{2}",
    "spatial": r"[YN]{5}",
    "3d": r"i|ii|iii|iv|v",
    "system": r"[A-X]{12}",
    "figurework": r"\d+(\.\d{2,})?|\d+/\d+",
}


def test_answers_can_be_given(loaded):
    for q in Question.objects.all():
        section = SECTIONS[q.section]
        if q.section in ANSWER_FORMS:
            assert re.fullmatch(ANSWER_FORMS[q.section], q.answer), q
        elif section.input == "image_choice":
            assert (STATIC / f"images/visual/{q.number}_{q.answer}.jpg").is_file(), q
        else:
            assert 1 <= int(q.answer) <= section.choices and all(q.choices), q
        assert len(q.answer) <= MAX_ANSWER_LENGTH * section.slots_per_question


def test_images_of_every_picture_question_exist(loaded):
    for name, section in SECTIONS.items():
        if section.image == "always":
            for number in Question.objects.filter(section=name).values_list("number", flat=True):
                assert (STATIC / f"images/{name}/{number}.jpg").is_file(), (name, number)


def fields(section, answer: str) -> dict[str, str]:
    if section.input == "fields":
        return {f"choice{i}": c for i, c in enumerate(answer, 1)}
    if section.input == "pair":
        return {"choice1": answer[0], "choice2": answer[1:]}
    return {"choice": answer}


def test_the_answer_key_scores_full_marks(loaded, clock):
    browser = Browser()
    browser.register("Ani", "1001", 21)
    browser.login("1001")
    browser.get("/test")
    browser.post("/test/start")
    for q in Question.objects.all():
        browser.answer(q.section, q.number, **fields(SECTIONS[q.section], q.answer))
    marks = Attempt.objects.get().marks()
    for name, section in SECTIONS.items():
        assert marks[f"result_{name}"] == "B" * section.length, name
