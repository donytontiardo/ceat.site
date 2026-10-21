<?php

require_once __DIR__ . '/../src/accounts.php';

test('registration accepts a valid form and trims it', function () {
    [$errors, $form] = validate_registration(['name' => '  Ani  ', 'nim' => '123', 'age' => '21', 'password' => 'rahasia12']);
    check_same([], $errors);
    check_same(['name' => 'Ani', 'nim' => '123', 'age' => 21, 'password' => 'rahasia12'], $form);
});

test('registration refuses bad fields, one message each', function () {
    [$errors] = validate_registration(['name' => '', 'nim' => '12a', 'age' => '3', 'password' => 'short']);
    check_same(4, count($errors));
    [$errors] = validate_registration(['name' => str_repeat('a', 101), 'nim' => str_repeat('1', 21),
                                       'age' => '100', 'password' => str_repeat('x', 73)]);
    check_same(4, count($errors), 'upper limits');
    [$errors] = validate_registration(['name' => ['array'], 'nim' => '1', 'age' => '20', 'password' => 'rahasia12']);
    check_same(1, count($errors), 'a non-string field is refused, not an error');
});

test('passwords: hashes, rehashing, and old plain-text accounts', function () {
    $hash = password_hash('rahasia12', PASSWORD_DEFAULT);
    check_same(['ok' => true, 'rehash' => false], check_password($hash, 'rahasia12'));
    check_same(['ok' => false, 'rehash' => false], check_password($hash, 'salah'));
    check_same(['ok' => true, 'rehash' => true], check_password('plainpass', 'plainpass'),
               'plain text still logs in once, to be hashed');
    check_same(['ok' => false, 'rehash' => false], check_password('plainpass', 'other'));
    check_same(['ok' => false, 'rehash' => false], check_password(DUMMY_PASSWORD_HASH, ''),
               'the dummy hash used for unknown NIMs matches nothing typed');
    $weak = password_hash('rahasia12', PASSWORD_BCRYPT, ['cost' => 4]);
    check_same(['ok' => true, 'rehash' => true], check_password($weak, 'rahasia12'), 'an outdated hash is renewed');
});
