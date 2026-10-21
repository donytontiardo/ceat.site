<?php

require_once __DIR__ . '/../src/scoring.php';
require_once __DIR__ . '/legacy/grading.php';

const AGES = [10, 13, 14, 15, 16, 17, 18, 19, 20, 25, 40];

function result_row(string $section, int $correct): array
{
    $row = [];
    foreach (SECTIONS as $name => $s) {
        $row["result_$name"] = blank_result($s);
    }
    $length = SECTIONS[$section]['length'];
    $row["result_$section"] = str_repeat('B', $correct) . str_repeat('S', $length - $correct);
    return $row;
}

test('grading equals the original for every score and age, except the grade-0 gaps it fixes', function () {
    $gaps = [];
    foreach (SECTIONS as $name => $section) {
        foreach (range(0, $section['length']) as $correct) {
            foreach (AGES as $age) {
                $row = result_row($name, $correct);
                $old = legacy_grade($row, $age);
                $new = grade_result($row, $age)[$name];
                $at = "$name, $correct correct, age $age:";
                check_same($old["result_$name"], $new['score'], "$at score");
                check(abs($old["result_{$name}_progress"] - $new['progress']) < 1e-9, "$at progress");
                if ($old["result_{$name}_potensi"] === 0 && $new['score'] > 0) {
                    $gaps["$name $correct"] = $new['potensi'];   // the original fell between two ranges
                    continue;
                }
                check_same($old["result_{$name}_potensi"], $new['potensi'], "$at potensi");
            }
        }
    }
    // exactly the cases found in the original: system 65/132 correct, and under 15 vocabulary 3/6, figurework 5
    check_same(['system 65', 'system 132', 'vocabulary 3', 'vocabulary 6', 'figurework 5'], array_keys($gaps));
    // 65 x 0.46 = 29.90 (grade 3's bound), 132 x 0.46 = 60.72 (6), 3 x 2.27 = 6.81 (1),
    // 6 x 2.27 = 13.62 (2), 5 x 3.13 = 15.65 (grade 4 starts at 15.65)
    check_same([3, 6, 1, 2, 4], array_values($gaps), 'grades given to the former gaps');
});

test('a blank test grades to all zeros', function () {
    $row = [];
    foreach (SECTIONS as $name => $section) {
        $row["result_$name"] = blank_result($section);
    }
    foreach (grade_result($row, 20) as $name => $grade) {
        check_same(0, $grade['score'], $name);
        check_same(0, $grade['potensi'], $name);
    }
});

test('answers are marked as before', function () {
    $s = SECTIONS;
    check_same('K', mark_answer($s['numerical'], '4', ['']));
    check_same('B', mark_answer($s['numerical'], '4', ['4']));
    check_same('S', mark_answer($s['numerical'], '4', ['5']));
    check_same('B', mark_answer($s['figurework'], '0.20', ['0.2']), 'numbers compare by value, as with ==');
    check_same('B', mark_answer($s['3d'], 'iii', [' iii ']), 'whitespace ignored');
    check_same('S', mark_answer($s['3d'], 'iii', ['III']), 'case still matters');
    check_same('B', mark_answer($s['sequential'], 'ED', ['E', 'D']), 'pair joins its two fields');
    check_same('K', mark_answer($s['sequential'], 'ED', ['', '']));
    check_same('BSKBB', mark_answer($s['spatial'], 'NNYYN', ['N', 'Y', '', 'Y', 'N']));
    check_same('BBS', mark_answer(['input' => 'fields'] + $s['system'], 'GF', ['G', 'F', 'H']),
               'a field past the answer key is wrong, not an error');
});

test('section order and slots', function () {
    check_same('numerical', next_section('visual'));
    check_same(null, next_section('figurework'));
    check_same(null, next_section('nope'));
    check_same(5, slots_per_question(SECTIONS['spatial']));
    check_same(1, slots_per_question(SECTIONS['sequential']));
    check_same(['choice1', 'choice2'], answer_fields(SECTIONS['sequential']));
    check_same(['choice'], answer_fields(SECTIONS['verbal']));
    // the result columns must hold every question's slots: spatial 14 x 5, system 18 x 12
    check_same(70, 14 * slots_per_question(SECTIONS['spatial']));
    check_same(216, 18 * slots_per_question(SECTIONS['system']));
});
