<?php
/* Registration rules and password checks, free of the database so they can be tested. */

declare(strict_types=1);

const MIN_AGE = 10;
const MAX_AGE = 99;
const MIN_PASSWORD_LENGTH = 8;
const MAX_PASSWORD_BYTES = 72;     // bcrypt ignores anything past 72 bytes
const MAX_NAME_LENGTH = 100;
// checked when the NIM is unknown, so that answer takes as long as a wrong password
const DUMMY_PASSWORD_HASH = '$2y$10$dixwQhDFQs25.Qgc5TxYEO3LQuGubp9Z.Hib5f1NOPo569/CcZu7q';

/** A form field as text; '' when missing or not text (name[]=x sends an array). */
function text_field(array $input, string $key): string
{
    $value = $input[$key] ?? '';
    return is_string($value) ? $value : '';
}

/**
 * [errors, clean values] for a registration form. Errors are messages for the
 * student; clean values are only meaningful when there are none.
 */
function validate_registration(array $input): array
{
    $name = trim(text_field($input, 'name'));
    $nim = trim(text_field($input, 'nim'));
    $age = filter_var(text_field($input, 'age'), FILTER_VALIDATE_INT,
                      ['options' => ['min_range' => MIN_AGE, 'max_range' => MAX_AGE]]);
    $password = text_field($input, 'password');

    $errors = [];
    if ($name === '' || mb_strlen($name) > MAX_NAME_LENGTH) {
        $errors[] = 'Nama harus diisi, paling panjang ' . MAX_NAME_LENGTH . ' karakter.';
    }
    if (!preg_match('/^[0-9]{1,20}$/', $nim)) {
        $errors[] = 'NIM hanya berisi angka, paling panjang 20 digit.';
    }
    if ($age === false) {
        $errors[] = 'Usia harus angka antara ' . MIN_AGE . ' dan ' . MAX_AGE . '.';
    }
    if (mb_strlen($password) < MIN_PASSWORD_LENGTH || strlen($password) > MAX_PASSWORD_BYTES) {
        $errors[] = 'Kata sandi minimal ' . MIN_PASSWORD_LENGTH . ' karakter dan paling panjang '
                  . MAX_PASSWORD_BYTES . ' byte.';
    }
    return [$errors, ['name' => $name, 'nim' => $nim, 'age' => (int) $age, 'password' => $password]];
}

/**
 * Does $password match the stored value? ['ok' => bool, 'rehash' => bool].
 * Accounts made before hashing stored the password as plain text: those still
 * log in once, and 'rehash' tells the caller to replace it with a hash.
 */
function check_password(string $stored, string $password): array
{
    if (password_get_info($stored)['algo'] !== null) {
        $ok = password_verify($password, $stored);
        return ['ok' => $ok, 'rehash' => $ok && password_needs_rehash($stored, PASSWORD_DEFAULT)];
    }
    $ok = $stored !== '' && hash_equals($stored, $password);
    return ['ok' => $ok, 'rehash' => $ok];
}
