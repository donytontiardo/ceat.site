<?php
/* A minimal test runner: test('name', fn) collects, run_tests() runs and reports. */

declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(function (int $severity, string $message, string $file, int $line) {
    // the legacy fixtures are the original code, warnings included; only the rewrite must be clean
    if (str_contains($file, '/tests/legacy/') || str_contains($file, "eval()'d")) {
        return true;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$GLOBALS['tests'] = [];

function test(string $name, callable $body): void
{
    $GLOBALS['tests'][$name] = $body;
}

function check(bool $condition, string $message = 'check failed'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function check_same($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(trim("$message expected " . var_export($expected, true)
                                        . ', got ' . var_export($actual, true)));
    }
}

function run_tests(?string $filter = null): int
{
    $failed = 0;
    foreach ($GLOBALS['tests'] as $name => $body) {
        if ($filter && !str_contains($name, $filter)) {
            continue;
        }
        $start = microtime(true);
        try {
            $body();
            printf("  ok    %s (%.1fs)\n", $name, microtime(true) - $start);
        } catch (Throwable $e) {
            $failed++;
            printf("  FAIL  %s\n        %s\n", $name, $e->getMessage());
        }
    }
    printf("%d tests, %d failed\n", count($GLOBALS['tests']), $failed);
    return $failed ? 1 : 0;
}
