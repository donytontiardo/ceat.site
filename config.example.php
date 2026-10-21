<?php
// Copy to config.php and fill in. config.php is not committed: it holds the database password.
return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=ceat;charset=utf8mb4',
        'user' => 'ceat',
        'password' => '',
    ],
    'timezone' => 'Asia/Jakarta',
    // true when the site is served over HTTPS (it should be): secure cookies and HSTS
    'https' => true,
    // shorter time limits per section, e.g. for a trial run: ['visual' => 60]
    'section_seconds' => [],
];
