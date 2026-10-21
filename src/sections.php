<?php
/*
 * The nine test sections, in the order they are taken.
 *
 *   label     shown to the student
 *   seconds   time limit, enforced by the server from the first question on
 *   length    answer slots in the result column (K blank, B correct, S wrong)
 *   input     how a question is answered:
 *               image_choice  radio buttons under images (visual)
 *               text_choice   radio buttons with the choices_N columns
 *               number/text   one field
 *               fields        N fields marked one by one against the answer's
 *                             characters (spatial: Y/N, system: letters)
 *               pair          2 fields joined and marked as one answer (sequential)
 *   choices   number of choices or fields
 *   image     images/<section>/<n>.jpg: always, if_exists or none
 */

const SECTIONS = [
    'visual' => ['label' => 'Visual', 'seconds' => 600, 'length' => 32,
                 'input' => 'image_choice', 'choices' => 4, 'image' => 'if_exists'],
    'numerical' => ['label' => 'Numerical', 'seconds' => 480, 'length' => 20,
                    'input' => 'number', 'choices' => 1, 'image' => 'always'],
    'verbal' => ['label' => 'Verbal', 'seconds' => 600, 'length' => 33,
                 'input' => 'text_choice', 'choices' => 5, 'image' => 'none'],
    'sequential' => ['label' => 'Sequential', 'seconds' => 900, 'length' => 32,
                     'input' => 'pair', 'choices' => 2, 'image' => 'always'],
    'spatial' => ['label' => 'Spatial', 'seconds' => 600, 'length' => 70,
                  'input' => 'fields', 'choices' => 5, 'image' => 'always'],
    '3d' => ['label' => '3D', 'seconds' => 300, 'length' => 30,
             'input' => 'text', 'choices' => 1, 'image' => 'always'],
    'system' => ['label' => 'System', 'seconds' => 240, 'length' => 216,
                 'input' => 'fields', 'choices' => 12, 'image' => 'always'],
    'vocabulary' => ['label' => 'Vocabulary', 'seconds' => 300, 'length' => 40,
                     'input' => 'text_choice', 'choices' => 4, 'image' => 'none'],
    'figurework' => ['label' => 'Figurework', 'seconds' => 600, 'length' => 30,
                     'input' => 'text', 'choices' => 1, 'image' => 'none'],
];

const MAX_ANSWER_LENGTH = 20;

function section(string $name): ?array
{
    return SECTIONS[$name] ?? null;
}

/** The section after $name, or null after the last one. */
function next_section(string $name): ?string
{
    $names = array_keys(SECTIONS);
    $i = array_search($name, $names, true);
    return $i === false ? null : ($names[$i + 1] ?? null);
}

/** Answer slots a question takes in the result column. */
function slots_per_question(array $section): int
{
    return $section['input'] === 'fields' ? $section['choices'] : 1;
}

/** The empty result column of a section: one K per slot. */
function blank_result(array $section): string
{
    return str_repeat('K', $section['length']);
}

/** The answer fields a question's form posts, in order. */
function answer_fields(array $section): array
{
    if ($section['input'] === 'fields' || $section['input'] === 'pair') {
        return array_map(fn($i) => "choice$i", range(1, $section['choices']));
    }
    return ['choice'];
}

/**
 * The marks (K/B/S) for one submitted question: one mark, or one per field
 * for 'fields'. Answers are compared with PHP's == as before, so "0.20" still
 * matches "0.2"; surrounding whitespace is ignored.
 */
function mark_answer(array $section, string $correct, array $given): string
{
    $given = array_map(fn($v) => trim((string) $v), $given);
    if ($section['input'] === 'fields') {
        $marks = '';
        foreach ($given as $i => $value) {
            $marks .= mark($correct[$i] ?? '', $value);
        }
        return $marks;
    }
    return mark($correct, implode('', $given));
}

function mark(string $correct, string $given): string
{
    if ($given === '') {
        return 'K';
    }
    return $correct == $given ? 'B' : 'S';
}
