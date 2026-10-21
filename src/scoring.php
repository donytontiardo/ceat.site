<?php
/*
 * From a finished test's result columns to the nine subject grades:
 *   score     correct answers, plus a bonus for a full score in some sections
 *   progress  score as 0-10 of the section's maximum
 *   potensi   the 0-10 potential grade the fuzzy model takes: score x factor
 *             looked up in the section's table (some depend on age)
 */

require_once __DIR__ . '/sections.php';

/** factor, then the upper bound of grades 1..10 (grade 10 also takes anything above 100). */
const POTENTIAL_TABLES = [
    'visual' => [2.86, [2.86, 5.72, 14.30, 20.00, 28.60, 37.14, 48.62, 57.14, 80.08, 100]],
    'numerical' => [4.55, [4.55, 9.10, 18.20, 22.75, 31.85, 36.40, 50.05, 59.15, 81.90, 100]],
    'verbal' => [2.86, [2.86, 8.58, 17.16, 22.88, 28.60, 34.32, 39.90, 45.62, 73.35, 100]],
    'sequential' => [2.86, [2.86, 8.58, 17.16, 22.88, 28.60, 34.32, 45.76, 54.34, 77.22, 100]],
    'spatial' => [1.43, [5.72, 12.87, 25.74, 37.18, 42.90, 48.62, 54.34, 60.06, 80.08, 100]],
    '3d' => [3.03, [9.09, 18.18, 27.27, 36.36, 42.42, 48.48, 57.57, 63.63, 81.81, 100]],
    'system' => [0.46, [6.90, 13.80, 29.90, 46.00, 53.36, 60.72, 68.54, 75.91, 87.86, 100]],
];

/** age groups (below this age) -> table; the last group has no limit */
const AGE_POTENTIAL_TABLES = [
    'vocabulary' => [
        [15, 2.27, [6.81, 13.62, 29.51, 41.13, 49.94, 56.75, 64.56, 68.10, 83.99, 100]],
        [18, 2.38, [6.84, 14.28, 29.64, 42.22, 50.16, 59.50, 63.84, 71.40, 82.08, 100]],
        [null, 2.50, [7.50, 15.00, 32.50, 47.50, 55.00, 62.50, 70.00, 75.00, 87.50, 100]],
    ],
    'figurework' => [
        [15, 3.13, [3.13, 9.39, 15.64, 18.78, 28.17, 37.56, 46.95, 56.34, 78.25, 100]],
        [19, 3.23, [3.23, 9.69, 16.15, 19.38, 29.07, 38.76, 48.45, 58.14, 80.75, 100]],
        [null, 3.33, [3.33, 9.99, 16.65, 19.98, 29.97, 39.96, 49.95, 59.94, 79.92, 100]],
    ],
];

function full_score_bonus(string $section, int $age): int
{
    switch ($section) {
        case 'visual':
        case 'numerical':
        case 'sequential':
        case '3d':
            return 3;
        case 'verbal':
            return 2;
        case 'vocabulary':
            return $age < 16 ? 2 : ($age < 20 ? 1 : 0);
        case 'figurework':
            return $age <= 15 ? 2 : ($age < 19 ? 1 : 0);
        default:
            return 0;
    }
}

function potential_table(string $section, int $age): array
{
    if (isset(POTENTIAL_TABLES[$section])) {
        return POTENTIAL_TABLES[$section];
    }
    foreach (AGE_POTENTIAL_TABLES[$section] as [$below, $factor, $bounds]) {
        if ($below === null || $age < $below) {
            return [$factor, $bounds];
        }
    }
    throw new LogicException("no potential table for $section");
}

/**
 * Grade 0-10 of a score: 0 for no score, else the first grade whose bound it does not pass.
 * The product is rounded to the bounds' 2 decimals: 65 x 0.46 is 29.900000000000002 in
 * floating point, which fell between the old ranges (29.90 and 29.91) and graded 0.
 */
function potential(string $section, int $score, int $age): int
{
    [$factor, $bounds] = potential_table($section, $age);
    $value = round($score * $factor, 2);
    if ($value <= 0) {
        return 0;
    }
    foreach ($bounds as $i => $upper) {
        if ($value <= $upper) {
            return $i + 1;
        }
    }
    return 10;
}

/** section => ['score', 'progress', 'potensi'] for a result row and the student's age. */
function grade_result(array $row, int $age): array
{
    $grades = [];
    foreach (SECTIONS as $name => $section) {
        $max = $section['length'];
        $score = substr_count(substr((string) ($row["result_$name"] ?? ''), 0, $max), 'B');
        if ($score === $max) {
            $score += full_score_bonus($name, $age);
        }
        $grades[$name] = [
            'score' => $score,
            'progress' => $score >= $max ? 10 : $score * 10 / $max,
            'potensi' => potential($name, $score, $age),
        ];
    }
    return $grades;
}
