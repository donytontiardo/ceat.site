<?php
/*
 * Mamdani fuzzy inference: how well a student's nine potential grades (0-10)
 * fit each of five computer careers. The memberships and rules per career were
 * set by an expert; this file only holds them as data and evaluates them.
 *
 * Fuzzification: every subject has three sets, buruk (poor), cukup (fair) and
 * bagus (good), as trapezoids:
 *     buruk  1 up to a, falling to 0 at b
 *     cukup  rising from a to b, 1 from b to c, falling to 0 at d
 *     bagus  0 up to c, rising to 1 at d
 * Some subjects give bagus its own c/d (the 'bagus' key).
 *
 * Inference: one rule per combination of sets that all have a membership above
 * 0; its strength is the smallest of the nine memberships. A rule says TINGGI
 * (high fit) when the career's key subjects are all at least cukup with at most
 * one of them below bagus, or when every subject reaches the career's minimum
 * level (0 buruk, 1 cukup, 2 bagus). Otherwise it says RENDAH (low fit).
 *
 * Defuzzification: weighted average over the samples 10, 20, ... 100 of the
 * output, RENDAH weighing the samples up to 50 and TINGGI those from 80, the
 * stronger of the two the ramp in between.
 */

const SUBJECTS = ['visual', 'numerical', 'verbal', 'sequential', 'spatial', '3d', 'system', 'vocabulary', 'figurework'];

const BURUK = 0;
const CUKUP = 1;
const BAGUS = 2;

function shape(float $a, float $b, float $c, float $d, ?array $bagus = null): array
{
    return ['buruk' => [$a, $b], 'cukup' => [$a, $b, $c, $d], 'bagus' => $bagus ?? [$c, $d]];
}

function careers(): array
{
    static $careers = null;
    if ($careers !== null) {
        return $careers;
    }
    $wide = shape(2, 4, 6, 8);
    $narrow = shape(2, 3, 4, 6);
    $mid = shape(2, 3, 5, 7);
    $low = shape(1, 2, 2, 3, [2, 4]);
    $lowWide = shape(1, 3, 3, 5);

    return $careers = [
        'CHE' => [
            'name' => 'Computer Hardware Engineer',
            'shapes' => [$wide, $wide, $narrow, $low, $wide, $wide, $wide, $narrow, $narrow],
            'keys' => [0, 1, 4, 5, 6],
            'minimum' => [1, 1, 2, 2, 1, 1, 1, 2, 2],
        ],
        'CNS' => [
            'name' => 'Computer Network Specialist',
            'shapes' => [$wide, $narrow, $wide, $narrow, $wide, $lowWide, $narrow, $mid, $narrow],
            'keys' => [0, 2, 4],
            'minimum' => [1, 2, 1, 2, 1, 2, 2, 2, 2],
        ],
        'CP' => [
            'name' => 'Computer Programmer',
            'shapes' => [$mid, $wide, $narrow, $wide, $wide, $low, $mid, $narrow, $narrow],
            'keys' => [1, 3, 4],
            'minimum' => [2, 1, 2, 1, 1, 2, 2, 2, 2],
        ],
        'CSA' => [
            'name' => 'Computer System Analyst',
            'shapes' => [$wide, $mid, $wide, $narrow, $wide, $low, $narrow, $wide, $lowWide],
            'keys' => [0, 2, 4, 7],
            'minimum' => [1, 2, 1, 2, 1, 2, 2, 1, 2],
        ],
        'DA' => [
            'name' => 'Database Administrator',
            'shapes' => [$narrow, $low, $wide, $wide, $wide, $low, $narrow, $wide, $low],
            'keys' => [2, 3, 4, 7],
            'minimum' => [2, 2, 1, 1, 1, 2, 2, 1, 2],
        ],
    ];
}

/** [buruk, cukup, bagus] memberships of grade $x in one subject's shape. */
function memberships(float $x, array $shape): array
{
    [$a, $b] = $shape['buruk'];
    if ($x <= $a) {
        $buruk = 1.0;
    } elseif ($x < $b) {
        $buruk = ($b - $x) / ($b - $a);
    } else {
        $buruk = 0.0;
    }

    [$a, $b, $c, $d] = $shape['cukup'];
    if ($x <= $a || $x >= $d) {
        $cukup = 0.0;
    } elseif ($x < $b) {
        $cukup = ($x - $a) / ($b - $a);
    } elseif ($x <= $c) {
        $cukup = 1.0;
    } else {
        $cukup = ($d - $x) / ($d - $c);
    }

    [$c, $d] = $shape['bagus'];
    if ($x <= $c) {
        $bagus = 0.0;
    } elseif ($x < $d) {
        $bagus = ($x - $c) / ($d - $c);
    } else {
        $bagus = 1.0;
    }

    return [$buruk, $cukup, $bagus];
}

function is_high_fit(array $levels, array $career): bool
{
    $belowBagus = 0;
    foreach ($career['keys'] as $subject) {
        if ($levels[$subject] === BURUK) {
            $belowBagus = 2;
            break;
        }
        if ($levels[$subject] === CUKUP) {
            $belowBagus++;
        }
    }
    if ($belowBagus <= 1) {
        return true;
    }
    foreach ($career['minimum'] as $subject => $minimum) {
        if ($levels[$subject] < $minimum) {
            return false;
        }
    }
    return true;
}

/**
 * The fit of nine grades to one career: ['score' => 0-100, 'low' => strongest
 * RENDAH rule, 'high' => strongest TINGGI rule].
 */
function fuzzy_fit(array $grades, array $career): array
{
    // only the sets a grade belongs to: at most 2 per subject, so at most 2^9 rules
    $active = [];
    foreach (array_values($grades) as $subject => $grade) {
        $sets = [];
        foreach (memberships((float) $grade, $career['shapes'][$subject]) as $level => $value) {
            if ($value > 0) {
                $sets[] = [$level, $value];
            }
        }
        $active[] = $sets;
    }

    $low = $high = 0.0;
    $walk = function (int $subject, array $levels, float $strength) use (&$walk, &$low, &$high, $active, $career) {
        if ($subject === count($active)) {
            if (is_high_fit($levels, $career)) {
                $high = max($high, $strength);
            } else {
                $low = max($low, $strength);
            }
            return;
        }
        foreach ($active[$subject] as [$level, $value]) {
            $levels[$subject] = $level;
            $walk($subject + 1, $levels, min($strength, $value));
        }
    };
    $walk(0, [], 1.0);

    return ['score' => defuzzify($low, $high), 'low' => $low, 'high' => $high];
}

function defuzzify(float $low, float $high): float
{
    $numerator = $denominator = 0.0;
    for ($z = 10; $z <= 100; $z += 10) {
        if ($z <= 50) {
            $weight = $low;
        } elseif ($z >= 80) {
            $weight = $high;
        } elseif ($low > $high) {
            $weight = (80 - $z) / 30;
        } else {
            $weight = ($z - 50) / 30;
        }
        $numerator += $z * $weight;
        $denominator += $weight;
    }
    return $denominator > 0 ? $numerator / $denominator : 0.0;
}

/**
 * Every career's fit for the nine grades, best first (ties keep the order of
 * careers()), and whether any conclusion can be drawn: none when every grade
 * is 0 or no career has a single high-fit rule.
 */
function career_fits(array $grades): array
{
    $fits = [];
    foreach (careers() as $code => $career) {
        $fits[$code] = fuzzy_fit($grades, $career) + ['name' => $career['name']];
    }
    $conclusive = max($grades) > 0 && max(array_column($fits, 'high')) > 0;
    uasort($fits, fn($x, $y) => $y['score'] <=> $x['score']);
    return ['fits' => $fits, 'conclusive' => $conclusive];
}
