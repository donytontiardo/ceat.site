"""From a finished test's result columns to the nine subject grades:

    score     correct answers, plus a bonus for a full score in some sections
    progress  score as 0-10 of the section's maximum
    potensi   the 0-10 potential grade the fuzzy model takes: score x factor
              looked up in the section's table (some depend on age)

The factors and bounds are in hundredths, so the lookup is exact integer
arithmetic: in floating point 65 x 0.46 is 29.900000000000002, which once fell
between two ranges and graded 0.
"""

from .sections import SECTIONS

# factor, then the upper bound of grades 1..10 (grade 10 also takes anything above 100), in hundredths
POTENTIAL_TABLES = {
    "visual": (286, [286, 572, 1430, 2000, 2860, 3714, 4862, 5714, 8008, 10000]),
    "numerical": (455, [455, 910, 1820, 2275, 3185, 3640, 5005, 5915, 8190, 10000]),
    "verbal": (286, [286, 858, 1716, 2288, 2860, 3432, 3990, 4562, 7335, 10000]),
    "sequential": (286, [286, 858, 1716, 2288, 2860, 3432, 4576, 5434, 7722, 10000]),
    "spatial": (143, [572, 1287, 2574, 3718, 4290, 4862, 5434, 6006, 8008, 10000]),
    "3d": (303, [909, 1818, 2727, 3636, 4242, 4848, 5757, 6363, 8181, 10000]),
    "system": (46, [690, 1380, 2990, 4600, 5336, 6072, 6854, 7591, 8786, 10000]),
}

# (below this age, factor, bounds); the last group has no limit
AGE_POTENTIAL_TABLES = {
    "vocabulary": [
        (15, 227, [681, 1362, 2951, 4113, 4994, 5675, 6456, 6810, 8399, 10000]),
        (18, 238, [684, 1428, 2964, 4222, 5016, 5950, 6384, 7140, 8208, 10000]),
        (None, 250, [750, 1500, 3250, 4750, 5500, 6250, 7000, 7500, 8750, 10000]),
    ],
    "figurework": [
        (15, 313, [313, 939, 1564, 1878, 2817, 3756, 4695, 5634, 7825, 10000]),
        (19, 323, [323, 969, 1615, 1938, 2907, 3876, 4845, 5814, 8075, 10000]),
        (None, 333, [333, 999, 1665, 1998, 2997, 3996, 4995, 5994, 7992, 10000]),
    ],
}


def full_score_bonus(section: str, age: int) -> int:
    if section in ("visual", "numerical", "sequential", "3d"):
        return 3
    if section == "verbal":
        return 2
    if section == "vocabulary":
        return 2 if age < 16 else 1 if age < 20 else 0
    if section == "figurework":
        return 2 if age <= 15 else 1 if age < 19 else 0
    return 0


def potential_table(section: str, age: int) -> tuple[int, list[int]]:
    if section in POTENTIAL_TABLES:
        return POTENTIAL_TABLES[section]
    for below, factor, bounds in AGE_POTENTIAL_TABLES[section]:
        if below is None or age < below:
            return factor, bounds
    raise LookupError(section)


def potential(section: str, score: int, age: int) -> int:
    """Grade 0-10 of a score: 0 for no score, else the first grade whose bound it does not pass."""
    factor, bounds = potential_table(section, age)
    value = score * factor
    if value <= 0:
        return 0
    for grade, upper in enumerate(bounds, 1):
        if value <= upper:
            return grade
    return 10


def grade_result(row, age: int) -> dict[str, dict]:
    """section -> {'score', 'progress', 'potensi'} for a result row and the student's age."""
    grades = {}
    for name, section in SECTIONS.items():
        maximum = section.length
        score = (row[f"result_{name}"] or "")[:maximum].count("B")
        if score == maximum:
            score += full_score_bonus(name, age)
        grades[name] = {
            "score": score,
            "progress": 10 if score >= maximum else score * 10 / maximum,
            "potensi": potential(name, score, age),
        }
    return grades
