<?php
/*
 * The original fuzzy*.php files with exactly the four bug fixes applied as text
 * patches, so the tests can show the rewrite computes what the original meant.
 * Each patch must match the number of times expected, or loading fails.
 *
 *   1. "if ($inputNilai[3] = 2)" assigns instead of comparing: the middle set
 *      was forced to 1 and the grade overwritten for the checks after it.
 *   2. The rule strength took figurework's value when two memberships tied for
 *      the lowest; it is the minimum of the nine.
 *   3. fuzzycp.php: sequential's cukup set wrote buruk on its plateau and
 *      falling edge, so a sequential grade of 4 to 8 counted as poor.
 *   4. Defuzzification: when RENDAH was stronger than TINGGI, the samples
 *      between 50 and 80 used 0 instead of the sample and its ramp.
 */

function load_fixed_legacy(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    $expected = ['fuzzyche' => 1, 'fuzzycns' => 1, 'fuzzycp' => 1, 'fuzzycsa' => 2, 'fuzzyda' => 3];
    foreach ($expected as $name => $assignments) {
        $code = file_get_contents(__DIR__ . "/$name.php");

        $code = preg_replace('/if \(\$inputNilai\[(\d)\] = (\d)\)/', 'if ($inputNilai[$1] == $2)', $code, -1, $n);
        assert_patched($name, 'assignment', $n, $assignments);

        $code = str_replace(
            '} else $nilaiKondisi[$k] = $nilaifig[$r];',
            '} else $nilaiKondisi[$k] = $nilaifig[$r];' . "\n" . '$nilaiKondisi[$k] = min($nilaivisual[$i], '
            . '$nilainumerical[$j], $nilaiverbal[$l], $nilaisequence[$m], $nilaispatial[$n], $nilaitd[$o], '
            . '$nilaisystem[$p], $nilaivocab[$q], $nilaifig[$r]);',
            $code, $n);
        assert_patched($name, 'minimum', $n, 1);

        if ($name === 'fuzzycp') {
            $code = str_replace('$sequenceburuk = ((-$inputNilai[3] + 8) / 2);',
                                '$sequencecukup = ((-$inputNilai[3] + 8) / 2);', $code, $n);
            assert_patched($name, 'sequential typo (falling edge)', $n, 1);
            $code = preg_replace('/(\(\$inputNilai\[3\] >= 4 && \$inputNilai\[3\] <= 6\) \{\s*)\$sequenceburuk = 1;/',
                                 '$1$sequencecukup = 1;', $code, -1, $n);
            assert_patched($name, 'sequential typo (plateau)', $n, 1);
        }

        $code = preg_replace(
            '/\$titik_sampelZ\[\$k\] = \(\(\(80 - \$titik_sampelZ\[\$k\]\) \/ 30\) \* 1000\);\s*'
            . '\$pengaliZ\[\$k\] = \(\$pengaliZ\[\$k\] \/ 1000\);/',
            '$titik_sampelZ[$k] = $titik_sampel; $pengaliZ[$k] = (80 - $titik_sampel) / 30;',
            $code, -1, $n);
        assert_patched($name, 'defuzzification', $n, 1);

        $code = preg_replace('/function ' . $name . '\(/', 'function fixed_' . $name . '(', $code, 1, $n);
        assert_patched($name, 'rename', $n, 1);
        eval('?>' . $code);
    }
}

function assert_patched(string $file, string $patch, int $count, int $expected): void
{
    if ($count !== $expected) {
        throw new RuntimeException("$file.php: patch '$patch' matched $count times, expected $expected");
    }
}

function load_original_legacy(): void
{
    foreach (['fuzzyche', 'fuzzycns', 'fuzzycp', 'fuzzycsa', 'fuzzyda'] as $name) {
        require_once __DIR__ . "/$name.php";
    }
}
