"""The nine test sections, in the order they are taken, and how answers are marked.

Each section:
    label     shown to the student
    seconds   time limit, kept by the server from the first question on
    length    answer slots in the result column (K blank, B correct, S wrong)
    input     how a question is answered:
                image_choice  radio buttons under images (visual)
                text_choice   radio buttons with the choices_N columns
                number/text   one field
                fields        N fields marked one by one against the answer's
                              characters (spatial: Y/N, system: letters)
                pair          2 fields joined and marked as one answer (sequential)
    choices   number of choices or fields
    image     static/images/<section>/<n>.jpg: always, if_exists or none
"""

import re
from dataclasses import dataclass


@dataclass(frozen=True)
class Section:
    name: str
    label: str
    seconds: int
    length: int
    input: str
    choices: int
    image: str

    @property
    def slots_per_question(self) -> int:
        return self.choices if self.input == "fields" else 1

    @property
    def blank_result(self) -> str:
        return "K" * self.length

    @property
    def answer_fields(self) -> list[str]:
        if self.input in ("fields", "pair"):
            return [f"choice{i}" for i in range(1, self.choices + 1)]
        return ["choice"]


SECTIONS = {s.name: s for s in [
    Section("visual", "Visual", 600, 32, "image_choice", 5, "if_exists"),
    Section("numerical", "Numerical", 480, 20, "number", 1, "always"),
    Section("verbal", "Verbal", 600, 33, "text_choice", 5, "none"),
    Section("sequential", "Sequential", 900, 32, "pair", 2, "always"),
    Section("spatial", "Spatial", 600, 70, "fields", 5, "always"),
    Section("3d", "3D", 300, 30, "text", 1, "always"),
    Section("system", "System", 240, 216, "fields", 12, "always"),
    Section("vocabulary", "Vocabulary", 300, 40, "text_choice", 4, "none"),
    Section("figurework", "Figurework", 600, 30, "text", 1, "none"),
]}

MAX_ANSWER_LENGTH = 20


def next_section(name: str) -> str | None:
    """The section after `name`, or None after the last one."""
    names = list(SECTIONS)
    if name not in names:
        return None
    i = names.index(name)
    return names[i + 1] if i + 1 < len(names) else None


# A numeric string as PHP reads one: answers that are both numeric compare by value,
# so "0.2" matches "0.20" as it always did.
_NUMERIC = re.compile(r"[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?")


def answers_match(correct: str, given: str) -> bool:
    if _NUMERIC.fullmatch(correct) and _NUMERIC.fullmatch(given):
        return float(correct) == float(given)
    return correct == given


def mark(correct: str, given: str) -> str:
    if given == "":
        return "K"
    return "B" if answers_match(correct, given) else "S"


def mark_answer(section: Section, correct: str, given: list[str]) -> str:
    """The marks (K/B/S) for one submitted question: one mark, or one per field
    for 'fields'. Surrounding whitespace is ignored."""
    given = [value.strip() for value in given]
    if section.input == "fields":
        return "".join(mark(correct[i] if i < len(correct) else "", value) for i, value in enumerate(given))
    return mark(correct, "".join(given))
