"""The scoring against reference data from the PHP version (tests/fixtures/php_reference.json),
whose engine was itself checked against the original thesis code with its bugs fixed."""

import json
from pathlib import Path

import pytest

from assessment.fuzzy import CAREERS, career_fits
from assessment.scoring import grade_result
from assessment.sections import SECTIONS, mark_answer, next_section

REFERENCE = json.loads((Path(__file__).parent / "fixtures" / "php_reference.json").read_text())


def test_fuzzy_matches_the_php_engine():
    for case in REFERENCE["fuzzy"]:
        result = career_fits(case["grades"])
        assert result.conclusive == case["conclusive"], case["grades"]
        assert [fit.code for fit in result.fits] == [code for code, *_ in case["fits"]], case["grades"]
        for fit, (code, score, low, high) in zip(result.fits, case["fits"]):
            assert fit.score == pytest.approx(score, abs=1e-9), (case["grades"], code)
            assert fit.low == pytest.approx(low, abs=1e-12)
            assert fit.high == pytest.approx(high, abs=1e-12)


def test_grading_matches_the_php_engine():
    for name, correct, age, expected in REFERENCE["grading"]:
        row = {f"result_{n}": s.blank_result for n, s in SECTIONS.items()}
        row[f"result_{name}"] = "B" * correct + "S" * (SECTIONS[name].length - correct)
        grade = grade_result(row, age)[name]
        assert grade["score"] == expected["score"], (name, correct, age)
        assert grade["progress"] == pytest.approx(expected["progress"], abs=1e-12), (name, correct, age)
        assert grade["potensi"] == expected["potensi"], (name, correct, age)


def test_former_grade_gaps():
    # these exact scores fell between two ranges in the original and graded 0
    def grade(section, correct, age=20):
        row = {f"result_{n}": s.blank_result for n, s in SECTIONS.items()}
        row[f"result_{section}"] = "B" * correct
        return grade_result(row, age)[section]["potensi"]
    assert grade("system", 65) == 3          # 65 x 0.46 = 29.90
    assert grade("system", 132) == 6         # 60.72
    assert grade("vocabulary", 3, age=14) == 1
    assert grade("figurework", 5, age=14) == 4


def test_blank_and_poor_grades_are_inconclusive():
    assert not career_fits([0] * 9).conclusive
    assert not career_fits([1] * 9).conclusive
    assert career_fits([9, 3, 8, 6, 9, 2, 7, 5, 4]).conclusive


def test_database_administrator_has_its_name():
    assert [career.name for career in CAREERS][-1] == "Database Administrator"


@pytest.mark.parametrize("section, correct, given, marks", [
    ("numerical", "4", [""], "K"),
    ("numerical", "4", ["4"], "B"),
    ("numerical", "4", ["5"], "S"),
    ("figurework", "0.20", ["0.2"], "B"),          # numbers compare by value, as PHP's == did
    ("figurework", "1/4", ["1/4"], "B"),
    ("figurework", "1/4", ["0.25"], "S"),
    ("3d", "iii", [" iii "], "B"),                  # whitespace ignored
    ("3d", "iii", ["III"], "S"),                    # case still matters
    ("sequential", "ED", ["E", "D"], "B"),          # a pair is joined
    ("sequential", "ED", ["", ""], "K"),
    ("spatial", "NNYYN", ["N", "Y", "", "Y", "N"], "BSKBB"),
    ("system", "GF", ["G", "F", "H"], "BBS"),        # a field past the answer key is wrong
])
def test_answers_are_marked_as_before(section, correct, given, marks):
    assert mark_answer(SECTIONS[section], correct, given) == marks


def test_sections():
    assert next_section("visual") == "numerical"
    assert next_section("figurework") is None
    assert next_section("nope") is None
    assert SECTIONS["spatial"].slots_per_question * 14 == SECTIONS["spatial"].length
    assert SECTIONS["system"].slots_per_question * 18 == SECTIONS["system"].length
    assert SECTIONS["sequential"].answer_fields == ["choice1", "choice2"]
