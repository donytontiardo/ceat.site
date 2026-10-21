<?php
require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/accounts.php';

require_post();

switch ($_POST['action'] ?? '') {
    case 'register':
        [$errors, $form] = validate_registration($_POST);
        if ($errors) {
            array_map('flash', $errors);
            redirect('regis.php');
        }
        if (find_user_by_nim($form['nim']) !== null) {
            flash('NIM sudah terdaftar.');
            redirect('regis.php');
        }
        try {
            create_user($form['name'], $form['age'], $form['nim'], $form['password']);
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {      // a duplicate NIM registered at the same moment
                throw $e;
            }
            flash('NIM sudah terdaftar.');
            redirect('regis.php');
        }
        flash('Akun dibuat. Silakan masuk.');
        redirect('login.php');

    case 'login':
        $nim = trim(text_field($_POST, 'nim'));
        $password = text_field($_POST, 'password');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (login_blocked($nim, $ip)) {
            flash('Terlalu banyak percobaan masuk. Coba lagi dalam ' . LOGIN_WINDOW_MINUTES . ' menit.');
            redirect('login.php');
        }
        $user = find_user_by_nim($nim);
        // the same check and message whether or not the NIM exists: no account enumeration
        $check = check_password($user['password'] ?? DUMMY_PASSWORD_HASH, $password);
        if ($user === null || !$check['ok']) {
            record_login_failure($nim, $ip);
            flash('NIM atau kata sandi salah.');
            redirect('login.php');
        }
        if ($check['rehash']) {
            set_password_hash((int) $user['id_user'], $password);
        }
        clear_login_failures($nim);
        log_in($user);
        redirect('index.php');

    case 'logout':
        log_out();
        redirect('index.php');

    default:
        http_response_code(400);
        exit('Permintaan tidak valid.');
}
