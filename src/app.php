<?php
/*
 * Loaded first by every page: settings, error handling, security headers, the
 * session and the helpers the pages share.
 */

declare(strict_types=1);

require_once __DIR__ . '/sections.php';
require_once __DIR__ . '/store.php';

function config(string $key, $default = null)
{
    return $GLOBALS['config'][$key] ?? $default;
}

// ── security headers and session ─────────────────────────────────────────
function send_security_headers(): void
{
    // every script is a file of this site; inline scripts and styles never run
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; "
           . "style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; "
           . "img-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'; object-src 'none'");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Cross-Origin-Opener-Policy: same-origin');
    if (config('https', true)) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

const SESSION_IDLE_SECONDS = 2 * 3600;   // the whole test takes about 70 minutes

function start_session(): void
{
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ceat_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (bool) config('https', true),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    $now = time();
    if (isset($_SESSION['seen']) && $now - $_SESSION['seen'] > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['seen'] = $now;
}

// ── CSRF ──────────────────────────────────────────────────────────────────
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Stop a POST that is not a POST or carries no valid token for this session. */
function require_post(): void
{
    $token = $_POST['csrf'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}

// ── users and the test in progress ────────────────────────────────────────
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect('login.php');
    }
    return $user;
}

function log_in(array $user): void
{
    session_regenerate_id(true);       // a new session id at every login: no fixation
    $_SESSION['user'] = ['id' => (int) $user['id_user'], 'name' => $user['name'], 'nim' => $user['nim']];
    unset($_SESSION['test']);
}

function log_out(): void
{
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600] + array_intersect_key(
        $params, array_flip(['path', 'domain', 'secure', 'httponly', 'samesite'])));
    session_destroy();
}

/** The test this session is taking: ['result_id', 'deadlines' => [section => unix time]]. */
function current_test(): ?array
{
    return $_SESSION['test'] ?? null;
}

function require_test(): array
{
    require_login();
    $test = current_test();
    if ($test === null) {
        redirect('indextest.php');
    }
    return $test;
}

function section_seconds(string $name): int
{
    return (int) (config('section_seconds')[$name] ?? SECTIONS[$name]['seconds']);
}

/** Seconds left in a section; null before its first question is shown. */
function seconds_left(string $name): ?int
{
    $deadline = $_SESSION['test']['deadlines'][$name] ?? null;
    return $deadline === null ? null : max(0, $deadline - time());
}

function start_section_clock(string $name): int
{
    $_SESSION['test']['deadlines'][$name] ??= time() + section_seconds($name);
    return seconds_left($name);
}

/** Where to go once a section is over: the next section's instructions, or the result. */
function after_section(string $name): string
{
    $next = next_section($name);
    return $next === null ? 'final.php' : 'indextest.php?section=' . urlencode($next);
}

// ── output ────────────────────────────────────────────────────────────────
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 303);
    exit;
}

function flash(string $message): void
{
    $_SESSION['flash'][] = $message;
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function render_header(string $page = ''): void
{
    $user = current_user();
    require __DIR__ . '/views/header.php';
}

function render_footer(): void
{
    require __DIR__ . '/views/footer.php';
}

function not_found(): void
{
    http_response_code(404);
    render_header();
    echo '<main><div class="container"><h2>Halaman tidak ditemukan</h2>'
       . '<p><a class="start" href="index.php">Kembali</a></p></div></main>';
    render_footer();
    exit;
}

// ── start: everything above is defined by now ─────────────────────────────
$GLOBALS['config'] = require (getenv('CEAT_CONFIG') ?: __DIR__ . '/../config.php');
date_default_timezone_set(config('timezone', 'Asia/Jakarta'));

ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e) {
    error_log('ceat: ' . $e);         // details go to the server log only
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'Terjadi kesalahan. Silakan coba lagi.';
});

send_security_headers();
start_session();
