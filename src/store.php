<?php
/*
 * Every database query of the site. All take their values as bound parameters;
 * the only names put into SQL text are section names and result columns, and
 * those come from the SECTIONS whitelist, never from the request.
 */

declare(strict_types=1);

require_once __DIR__ . '/sections.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $db = config('db');
        $pdo = new PDO($db['dsn'], $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}

/** `section_<name>` for a known section; anything else is a programming error. */
function section_table(string $name): string
{
    if (!isset(SECTIONS[$name])) {
        throw new InvalidArgumentException("unknown section $name");
    }
    return "`section_$name`";
}

// ── users ─────────────────────────────────────────────────────────────────
function find_user_by_nim(string $nim): ?array
{
    return query('SELECT id_user, name, nim, age, password FROM user WHERE nim = ?', [$nim])->fetch() ?: null;
}

function find_user(int $id): ?array
{
    return query('SELECT id_user, name, nim, age FROM user WHERE id_user = ?', [$id])->fetch() ?: null;
}

function create_user(string $name, int $age, string $nim, string $password): void
{
    query('INSERT INTO user (name, age, nim, password) VALUES (?, ?, ?, ?)',
          [$name, $age, $nim, password_hash($password, PASSWORD_DEFAULT)]);
}

function set_password_hash(int $id, string $password): void
{
    query('UPDATE user SET password = ? WHERE id_user = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
}

// ── login throttling ──────────────────────────────────────────────────────
const LOGIN_MAX_FAILURES = 5;
const LOGIN_WINDOW_MINUTES = 15;

function login_blocked(string $nim, string $ip): bool
{
    $failures = query('SELECT COUNT(*) FROM login_attempt WHERE (nim = ? OR ip = ?)
                       AND attempted_at > NOW() - INTERVAL ' . LOGIN_WINDOW_MINUTES . ' MINUTE',
                      [$nim, $ip])->fetchColumn();
    return $failures >= LOGIN_MAX_FAILURES;
}

function record_login_failure(string $nim, string $ip): void
{
    query('INSERT INTO login_attempt (nim, ip, attempted_at) VALUES (?, ?, NOW())', [$nim, $ip]);
    query('DELETE FROM login_attempt WHERE attempted_at < NOW() - INTERVAL 1 DAY');
}

function clear_login_failures(string $nim): void
{
    query('DELETE FROM login_attempt WHERE nim = ?', [$nim]);
}

// ── questions ─────────────────────────────────────────────────────────────
function question_count(string $section): int
{
    // asked on every question and answer; the question sets do not change during a test
    return $_SESSION['question_counts'][$section]
        ??= (int) query('SELECT COUNT(*) FROM ' . section_table($section))->fetchColumn();
}

function find_question(string $section, int $number): ?array
{
    $choices = SECTIONS[$section]['input'] === 'text_choice'
        ? ', ' . implode(', ', array_map(fn($i) => "choices_$i", range(1, SECTIONS[$section]['choices'])))
        : '';
    return query("SELECT question_number, question_text$choices FROM " . section_table($section)
                 . ' WHERE question_number = ?', [$number])->fetch() ?: null;
}

function correct_answer(string $section, int $number): ?string
{
    $answer = query('SELECT answer FROM ' . section_table($section) . ' WHERE question_number = ?',
                    [$number])->fetchColumn();
    return $answer === false ? null : (string) $answer;
}

function section_info(string $section): array
{
    return query('SELECT type_questions, time FROM info_section WHERE name_section = ?', [$section])->fetch()
        ?: ['type_questions' => '-', 'time' => intdiv(SECTIONS[$section]['seconds'], 60)];
}

// ── results ───────────────────────────────────────────────────────────────
function create_result(int $userId): int
{
    $columns = ['id_user', 'waktu', 'tanggal'];
    $values = [$userId, date('H:i:s'), date('d/m/Y')];
    foreach (SECTIONS as $name => $section) {
        $columns[] = "result_$name";
        $values[] = blank_result($section);
    }
    query('INSERT INTO result (' . implode(', ', $columns) . ') VALUES ('
          . implode(', ', array_fill(0, count($values), '?')) . ')', $values);
    return (int) db()->lastInsertId();
}

/** One test of one user: a result id of someone else's test finds nothing. */
function find_result(int $resultId, int $userId): ?array
{
    return query('SELECT * FROM result WHERE id_result = ? AND id_user = ?', [$resultId, $userId])->fetch() ?: null;
}

function results_of(int $userId): array
{
    return query('SELECT id_result, waktu, tanggal FROM result WHERE id_user = ? ORDER BY id_result',
                 [$userId])->fetchAll();
}

/** Write $marks into a section's result column from slot $position (0-based), in one statement. */
function write_marks(int $resultId, int $userId, string $section, int $position, string $marks): void
{
    section_table($section);                       // throws for a section that is not in SECTIONS
    $column = "`result_$section`";
    query("UPDATE result SET $column = INSERT($column, ?, ?, ?) WHERE id_result = ? AND id_user = ?",
          [$position + 1, strlen($marks), $marks, $resultId, $userId]);
}
