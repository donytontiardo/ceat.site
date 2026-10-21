<?php

require_once __DIR__ . '/../src/fuzzy.php';
require_once __DIR__ . '/legacy/fixed.php';

const LEGACY_NAMES = ['CHE' => 'fuzzyche', 'CNS' => 'fuzzycns', 'CP' => 'fuzzycp', 'CSA' => 'fuzzycsa', 'DA' => 'fuzzyda'];

/** Inputs to compare on: edge vectors, every single-subject sweep, and random grades. */
function grade_vectors(int $random, bool $integers = true): array
{
    $vectors = [];
    foreach (range(0, 10) as $v) {
        $vectors[] = array_fill(0, 9, $v);
    }
    foreach (range(0, 8) as $subject) {
        foreach (range(0, 10) as $v) {
            $grades = array_fill(0, 9, 5);
            $grades[$subject] = $v;
            $vectors[] = $grades;
        }
    }
    mt_srand(20211007);
    for ($i = 0; $i < $random; $i++) {
        $vectors[] = array_map(fn() => $integers ? mt_rand(0, 10) : mt_rand(0, 1000) / 100, range(0, 8));
    }
    return $vectors;
}

test('the engine equals the original scoring with its four bugs fixed (integer grades)', function () {
    load_fixed_legacy();
    foreach (grade_vectors(1500) as $grades) {
        foreach (LEGACY_NAMES as $code => $legacy) {
            $expected = ('fixed_' . $legacy)(...$grades);
            $actual = fuzzy_fit($grades, careers()[$code])['score'];
            check(abs($expected - $actual) < 1e-9,
                  "$code " . json_encode($grades) . ": legacy $expected, rewrite $actual");
        }
    }
});

test('the engine equals the fixed original on fractional grades too', function () {
    load_fixed_legacy();
    foreach (array_slice(grade_vectors(400, false), -400) as $grades) {
        foreach (LEGACY_NAMES as $code => $legacy) {
            $expected = ('fixed_' . $legacy)(...$grades);
            $actual = fuzzy_fit($grades, careers()[$code])['score'];
            check(abs($expected - $actual) < 1e-9,
                  "$code " . json_encode($grades) . ": legacy $expected, rewrite $actual");
        }
    }
});

test('the original scored exactly 30 when, and only when, no high-fit rule fired', function () {
    // career_fits() replaces the old "every career scored 30" check with "no career has a high-fit rule"
    foreach (LEGACY_NAMES as $code => $legacy) {
        $source = file_get_contents(__DIR__ . "/legacy/$legacy.php");
        $source = str_replace("function $legacy(", "function with_high_$legacy(", $source);
        $source = str_replace('return $hasildeffuzzifikasi;', 'return [$hasildeffuzzifikasi, $terbesarY];', $source);
        eval('?>' . $source);
    }
    foreach (grade_vectors(300) as $grades) {
        foreach (LEGACY_NAMES as $code => $legacy) {
            [$score, $high] = ('with_high_' . $legacy)(...$grades);
            check(($score == 30) === ($high == 0), "$code " . json_encode($grades) . ": score $score, high $high");
        }
    }
});

test('memberships of the shapes', function () {
    $wide = careers()['CHE']['shapes'][0];          // 2, 4, 6, 8
    check_same([1.0, 0.0, 0.0], memberships(0, $wide));
    check_same([1.0, 0.0, 0.0], memberships(2, $wide));
    check_same([0.5, 0.5, 0.0], memberships(3, $wide));
    check_same([0.0, 1.0, 0.0], memberships(5, $wide));
    check_same([0.0, 0.5, 0.5], memberships(7, $wide));
    check_same([0.0, 0.0, 1.0], memberships(10, $wide));
    $low = careers()['CHE']['shapes'][3];           // sequential: cukup peaks at 2, bagus rises 2..4
    check_same([0.0, 1.0, 0.0], memberships(2, $low));
    check_same([0.0, 0.0, 0.5], memberships(3, $low));   // the original forced cukup to 1 here
});

test('career_fits ranks best first and spots inconclusive grades', function () {
    $all = career_fits([9, 3, 8, 6, 9, 2, 7, 5, 4]);
    $previous = INF;
    foreach ($all['fits'] as $fit) {
        check($fit['score'] <= $previous, 'not sorted best first');
        $previous = $fit['score'];
    }
    check($all['conclusive']);
    check(!career_fits(array_fill(0, 9, 0))['conclusive'], 'all zeros is inconclusive');
    check(!career_fits(array_fill(0, 9, 1))['conclusive'], 'all poor is inconclusive');
});

test('how many integer inputs score differently from the thesis version', function () {
    // informational: the fixes change these scores; nothing to assert beyond running
    load_original_legacy();
    $changed = $total = 0;
    foreach (grade_vectors(1000) as $grades) {
        foreach (LEGACY_NAMES as $code => $legacy) {
            $total++;
            if (abs($legacy(...$grades) - fuzzy_fit($grades, careers()[$code])['score']) > 1e-9) {
                $changed++;
            }
        }
    }
    printf("        %d of %d career scores differ from the original (bug fixes)\n", $changed, $total);
});
