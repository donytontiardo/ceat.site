<?php
/*
 * php tests/run.php [filter]    unit tests: scoring against the original code, answers, helpers
 * tests/e2e.sh                  the site end to end on a scratch MariaDB database
 */

require __DIR__ . '/lib.php';
foreach (glob(__DIR__ . '/test_*.php') as $file) {
    require $file;
}
exit(run_tests($argv[1] ?? null));
