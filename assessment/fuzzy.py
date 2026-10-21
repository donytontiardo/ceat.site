"""Mamdani fuzzy inference: how well a student's nine potential grades (0-10)
fit each of five computer careers. The memberships and rules per career were
set by an expert; this module only holds them as data and evaluates them.

Fuzzification: every subject has three sets, buruk (poor), cukup (fair) and
bagus (good), as trapezoids:
    buruk  1 up to a, falling to 0 at b
    cukup  rising from a to b, 1 from b to c, falling to 0 at d
    bagus  0 up to c, rising to 1 at d
Some subjects give bagus its own c/d.

Inference: one rule per combination of sets that all have a membership above
0; its strength is the smallest of the nine memberships. A rule says TINGGI
(high fit) when the career's key subjects are all at least cukup with at most
one of them below bagus, or when every subject reaches the career's minimum
level (0 buruk, 1 cukup, 2 bagus). Otherwise it says RENDAH (low fit).

Defuzzification: weighted average over the samples 10, 20, ... 100 of the
output, RENDAH weighing the samples up to 50 and TINGGI those from 80, the
stronger of the two the ramp in between.
"""

from dataclasses import dataclass
from itertools import product

SUBJECTS = ["visual", "numerical", "verbal", "sequential", "spatial", "3d", "system", "vocabulary", "figurework"]
BURUK, CUKUP, BAGUS = 0, 1, 2


@dataclass(frozen=True)
class Shape:
    a: float
    b: float
    c: float
    d: float
    bagus: tuple[float, float] | None = None   # its own rise, when not c..d

    def memberships(self, x: float) -> tuple[float, float, float]:
        a, b, c, d = self.a, self.b, self.c, self.d
        buruk = 1.0 if x <= a else (b - x) / (b - a) if x < b else 0.0
        if x <= a or x >= d:
            cukup = 0.0
        elif x < b:
            cukup = (x - a) / (b - a)
        elif x <= c:
            cukup = 1.0
        else:
            cukup = (d - x) / (d - c)
        c, d = self.bagus or (c, d)
        bagus = 0.0 if x <= c else (x - c) / (d - c) if x < d else 1.0
        return buruk, cukup, bagus


@dataclass(frozen=True)
class Career:
    code: str
    name: str
    shapes: tuple[Shape, ...]       # one per subject, in SUBJECTS order
    keys: tuple[int, ...]           # key subjects
    minimum: tuple[int, ...]        # minimum level per subject for the all-round rule

    def is_high_fit(self, levels: tuple[int, ...]) -> bool:
        key_levels = [levels[k] for k in self.keys]
        if BURUK not in key_levels and key_levels.count(CUKUP) <= 1:
            return True
        return all(level >= minimum for level, minimum in zip(levels, self.minimum))


WIDE = Shape(2, 4, 6, 8)
NARROW = Shape(2, 3, 4, 6)
MID = Shape(2, 3, 5, 7)
LOW = Shape(1, 2, 2, 3, bagus=(2, 4))
LOW_WIDE = Shape(1, 3, 3, 5)

CAREERS = [
    Career("CHE", "Computer Hardware Engineer",
           (WIDE, WIDE, NARROW, LOW, WIDE, WIDE, WIDE, NARROW, NARROW),
           keys=(0, 1, 4, 5, 6), minimum=(1, 1, 2, 2, 1, 1, 1, 2, 2)),
    Career("CNS", "Computer Network Specialist",
           (WIDE, NARROW, WIDE, NARROW, WIDE, LOW_WIDE, NARROW, MID, NARROW),
           keys=(0, 2, 4), minimum=(1, 2, 1, 2, 1, 2, 2, 2, 2)),
    Career("CP", "Computer Programmer",
           (MID, WIDE, NARROW, WIDE, WIDE, LOW, MID, NARROW, NARROW),
           keys=(1, 3, 4), minimum=(2, 1, 2, 1, 1, 2, 2, 2, 2)),
    Career("CSA", "Computer System Analyst",
           (WIDE, MID, WIDE, NARROW, WIDE, LOW, NARROW, WIDE, LOW_WIDE),
           keys=(0, 2, 4, 7), minimum=(1, 2, 1, 2, 1, 2, 2, 1, 2)),
    Career("DA", "Database Administrator",
           (NARROW, LOW, WIDE, WIDE, WIDE, LOW, NARROW, WIDE, LOW),
           keys=(2, 3, 4, 7), minimum=(2, 2, 1, 1, 1, 2, 2, 1, 2)),
]


@dataclass
class Fit:
    code: str
    name: str
    score: float     # 0-100
    low: float       # strongest RENDAH rule
    high: float      # strongest TINGGI rule


def fuzzy_fit(grades: list[float], career: Career) -> Fit:
    # only the sets a grade belongs to: at most 2 per subject, so at most 2^9 rules
    active = []
    for grade, shape in zip(grades, career.shapes):
        active.append([(level, value) for level, value in enumerate(shape.memberships(float(grade))) if value > 0])

    low = high = 0.0
    for combination in product(*active):
        levels = tuple(level for level, _ in combination)
        strength = min(value for _, value in combination)
        if career.is_high_fit(levels):
            high = max(high, strength)
        else:
            low = max(low, strength)
    return Fit(career.code, career.name, defuzzify(low, high), low, high)


def defuzzify(low: float, high: float) -> float:
    numerator = denominator = 0.0
    for z in range(10, 101, 10):
        if z <= 50:
            weight = low
        elif z >= 80:
            weight = high
        elif low > high:
            weight = (80 - z) / 30
        else:
            weight = (z - 50) / 30
        numerator += z * weight
        denominator += weight
    return numerator / denominator if denominator > 0 else 0.0


@dataclass
class Careers:
    fits: list[Fit]      # best first; ties keep the order of CAREERS
    conclusive: bool     # False when every grade is 0 or no career has a single high-fit rule

    @property
    def best(self) -> Fit:
        return self.fits[0]


def career_fits(grades: list[float]) -> Careers:
    fits = [fuzzy_fit(grades, career) for career in CAREERS]
    conclusive = max(grades) > 0 and max(fit.high for fit in fits) > 0
    return Careers(sorted(fits, key=lambda fit: -fit.score), conclusive)
