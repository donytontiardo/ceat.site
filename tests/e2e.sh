#!/usr/bin/env bash
# The site end to end: a scratch MariaDB, PHP's built-in server, and curl as the
# browser. Needs mariadb-server, php (pdo_mysql, mbstring) and curl.
#     tests/e2e.sh
set -uo pipefail

ROOT=$(cd "$(dirname "$0")/.." && pwd)
WORK=$(mktemp -d)
PORT=${CEAT_TEST_PORT:-8099}
BASE="http://127.0.0.1:$PORT"
export CEAT_TEST_SOCKET="$WORK/db.sock"
export CEAT_CONFIG="$ROOT/tests/e2e/config.php"
PASSED=0
FAILED=0

cleanup() {
    [ -n "${PHP_PID:-}" ] && kill "$PHP_PID" 2>/dev/null
    [ -n "${DB_PID:-}" ] && kill "$DB_PID" 2>/dev/null
    wait 2>/dev/null
    rm -rf "$WORK"
}
trap cleanup EXIT

# ── database and server ───────────────────────────────────────────────────
mariadb-install-db --no-defaults --datadir="$WORK/data" --auth-root-authentication-method=normal \
    >"$WORK/install.log" 2>&1 || { cat "$WORK/install.log"; exit 1; }
mariadbd --no-defaults --datadir="$WORK/data" --socket="$CEAT_TEST_SOCKET" --skip-networking \
    --user=root --pid-file="$WORK/db.pid" >"$WORK/db.log" 2>&1 &
DB_PID=$!
for _ in $(seq 100); do [ -S "$CEAT_TEST_SOCKET" ] && break; sleep 0.1; done
sql() { mariadb --no-defaults -uroot --socket="$CEAT_TEST_SOCKET" -N -B ceat_test "$@"; }
mariadb --no-defaults -uroot --socket="$CEAT_TEST_SOCKET" -e 'CREATE DATABASE ceat_test'
sql <"$ROOT/schema.sql" && sql <"$ROOT/tests/e2e/fixtures.sql" || exit 1

php -S "127.0.0.1:$PORT" -t "$ROOT/public" >"$WORK/php.log" 2>&1 &
PHP_PID=$!
for _ in $(seq 100); do curl -s -o /dev/null "$BASE/" && break; sleep 0.1; done

# ── helpers ───────────────────────────────────────────────────────────────
JAR="$WORK/a.jar"
as() { JAR="$WORK/$1.jar"; }                       # switch browser (cookie jar)
get() { curl -s -b "$JAR" -c "$JAR" "$BASE/$1"; }
status() { curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" "$BASE/$1"; }
token() { get "$1" | grep -o 'name="csrf" value="[^"]*"' | head -1 | sed 's/.*value="//; s/"$//'; }
# post PATH [field=value ...] -> "<status> <Location>"; the token of the last page fetched is added
post() {
    local path=$1; shift
    local args=()
    for kv in "$@"; do args+=(--data-urlencode "$kv"); done
    curl -s -o "$WORK/body" -w '%{http_code} %{redirect_url}' -b "$JAR" -c "$JAR" \
        --data-urlencode "csrf=$CSRF" "${args[@]}" "$BASE/$path" | sed "s#$BASE/##"
}
expect() {   # expect DESCRIPTION ACTUAL EXPECTED
    if [ "$2" == "$3" ]; then PASSED=$((PASSED + 1)); else
        FAILED=$((FAILED + 1)); echo "  FAIL  $1"; echo "        expected: $3"; echo "        got:      $2"; fi
}
expect_contains() {   # expect_contains DESCRIPTION TEXT NEEDLE
    if [[ "$2" == *"$3"* ]]; then PASSED=$((PASSED + 1)); else
        FAILED=$((FAILED + 1)); echo "  FAIL  $1 (no '$3')"; fi
}
expect_missing() {
    if [[ "$2" != *"$3"* ]]; then PASSED=$((PASSED + 1)); else
        FAILED=$((FAILED + 1)); echo "  FAIL  $1 (found '$3')"; fi
}
answer() {   # answer SECTION N field=value ...: open the question, then send the answer
    local section=$1 n=$2; shift 2
    CSRF=$(token "question.php?section=$section&n=$n")
    post process.php action=answer "section=$section" "number=$n" "$@"
}
result() { sql -e "SELECT result_$1 FROM result WHERE id_result = ${2:-1}"; }

# ── headers ───────────────────────────────────────────────────────────────
HEADERS=$(curl -s -D - -o /dev/null "$BASE/")
expect_contains 'content security policy' "$HEADERS" "script-src 'self';"
expect_contains 'no framing' "$HEADERS" 'X-Frame-Options: DENY'
expect_contains 'session cookie is HttpOnly and SameSite' "$HEADERS" 'HttpOnly; SameSite=Lax'

# ── register, log in ──────────────────────────────────────────────────────
as a
CSRF=$(token regis.php)
expect 'short password refused' "$(post auth_process.php action=register name=Ani nim=1001 age=21 password=short)" '303 regis.php'
expect_contains 'and says why' "$(get regis.php)" 'Kata sandi minimal 8'
CSRF=$(token regis.php)
expect 'a form field sent as an array is refused, not a crash' "$(post auth_process.php action=register 'name[]=x' nim=1001 age=21 password=rahasia123)" '303 regis.php'
CSRF=$(token regis.php)
expect 'register' "$(post auth_process.php action=register 'name=<script>alert(1)</script>' nim=1001 age=21 password=rahasia123)" '303 login.php'
expect_contains 'password stored as a hash' "$(sql -e "SELECT password FROM user WHERE nim = '1001'")" '$2y$'
CSRF=$(token regis.php)
expect 'duplicate NIM refused' "$(post auth_process.php action=register name=Budi nim=1001 age=22 password=rahasia456)" '303 regis.php'
expect_contains 'and says why' "$(get regis.php)" 'NIM sudah terdaftar'

CSRF=$(token login.php)
expect 'wrong password' "$(post auth_process.php action=login nim=1001 password=salah12345)" '303 login.php'
expect_contains 'same message as an unknown NIM' "$(get login.php)" 'NIM atau kata sandi salah'
CSRF=nope
expect 'a POST without the right token is refused' "$(post auth_process.php action=login nim=1001 password=rahasia123)" '400 '
expect 'GET on the form handler is refused' "$(status auth_process.php)" '400'
CSRF=$(token login.php)
expect 'log in' "$(post auth_process.php action=login nim=1001 password=rahasia123)" '303 index.php'
HOME_PAGE=$(get index.php)
expect_contains 'the name is escaped' "$HOME_PAGE" '&lt;script&gt;alert(1)&lt;/script&gt;'
expect_missing 'no raw script from the name' "$HOME_PAGE" '<script>alert(1)'

# ── the test ──────────────────────────────────────────────────────────────
CSRF=$(token indextest.php)
expect 'start the test' "$(post process.php action=start)" '303 indextest.php?section=visual'
expect_contains 'section intro counts the questions' "$(get 'indextest.php?section=visual')" 'Jumlah Pertanyaan: 2'
QUESTION=$(get 'question.php?section=visual&n=1')
expect_contains 'question page' "$QUESTION" 'Question 1 of 2 (Visual)'
expect_contains 'question HTML from the database is kept' "$QUESTION" 'paling <i>berbeda</i>'
expect_contains 'the timer gets the seconds left' "$QUESTION" 'data-seconds="600"'

expect 'visual 1 correct' "$(answer visual 1 choice=2)" '303 question.php?section=visual&n=2'
expect 'visual 2 wrong, next section' "$(answer visual 2 choice=3)" '303 indextest.php?section=numerical'
expect 'visual marks' "$(result visual)" "BS$(printf 'K%.0s' $(seq 30))"

answer numerical 1 choice=4 >/dev/null
answer numerical 2 choice= >/dev/null
expect 'numerical: right, blank' "$(result numerical | cut -c1-3)" 'BKK'
answer verbal 1 choice=1 >/dev/null
answer verbal 2 choice=2 >/dev/null
expect 'verbal: right, wrong' "$(result verbal | cut -c1-3)" 'BSK'
answer sequential 1 choice1=E choice2=D >/dev/null
answer sequential 2 choice1=B choice2=A >/dev/null
expect 'sequential pairs' "$(result sequential | cut -c1-3)" 'BSK'
answer spatial 1 choice1=N choice2=Y choice3= choice4=Y choice5=N >/dev/null
answer spatial 2 choice1=Y choice2=Y choice3=Y choice4=Y choice5=Y >/dev/null
expect 'spatial: a mark per field' "$(result spatial | cut -c1-11)" 'BSKBBBBBBBK'
answer 3d 1 'choice= iii ' >/dev/null
answer 3d 2 choice=I >/dev/null
expect '3d: spaces ignored, case kept' "$(result 3d | cut -c1-3)" 'BSK'

# system has a 2-second limit in the test config: the second answer comes too late
fields=(); i=1
for c in G F H K K B J D L G A C; do fields+=("choice$i=$c"); i=$((i + 1)); done
answer system 1 "${fields[@]}" >/dev/null
CSRF=$(token 'question.php?section=system&n=2')
sleep 6
expect 'a late answer is not taken' "$(post process.php action=answer section=system number=2 choice1=A)" '303 indextest.php?section=vocabulary'
expect 'system: first question only' "$(result system | cut -c1-25)" "BBBBBBBBBBBBKKKKKKKKKKKKK"
expect 'the expired section sends on' "$(status 'question.php?section=system&n=1')" '303'

answer vocabulary 1 choice=1 >/dev/null
answer vocabulary 2 choice=2 >/dev/null
expect 'vocabulary' "$(result vocabulary | cut -c1-3)" 'BBK'
answer figurework 1 choice=0.2 >/dev/null
expect 'last answer leads to the result' "$(answer figurework 2 choice=0.30)" '303 final.php'
expect 'figurework: 0.2 equals 0.20' "$(result figurework | cut -c1-3)" 'BSK'

FINAL=$(get final.php)
expect_contains 'result page' "$FINAL" 'Hasil Akhir'
expect_contains 'all five careers listed' "$FINAL" 'Database Administrator'
expect_contains 'chart data as JSON' "$FINAL" 'id="chart-data"'
expect_contains 'results list links the test' "$(get indexresult.php)" 'final.php?id=1'

# ── what must not work ────────────────────────────────────────────────────
expect 'section names are a whitelist' "$(status 'question.php?section=visual%60--&n=1')" '404'
expect 'question numbers must exist' "$(status 'question.php?section=visual&n=99')" '404'
expect 'result ids are numbers' "$(status 'final.php?id=1%20OR%201%3D1')" '404'

as b
CSRF=$(token regis.php)
post auth_process.php action=register name=Budi nim=2002 age=19 password=rahasia789 >/dev/null
CSRF=$(token login.php)
post auth_process.php action=login nim=2002 password=rahasia789 >/dev/null
expect "another student's result is not found" "$(status 'final.php?id=1')" '404'
expect_missing "nor listed" "$(get indexresult.php)" 'final.php?id=1'
CSRF=$(token index.php)
expect 'no answers without a started test' "$(post process.php action=answer section=visual number=1 choice=2)" '303 indextest.php'

# ── old plain-text passwords, guessing, logging out ───────────────────────
sql -e "INSERT INTO user (name, age, nim, password) VALUES ('Lama', 20, '3003', 'plainpass1')"
as c
CSRF=$(token login.php)
expect 'an old plain-text account still logs in' "$(post auth_process.php action=login nim=3003 password=plainpass1)" '303 index.php'
expect_contains 'and its password is now a hash' "$(sql -e "SELECT password FROM user WHERE nim = '3003'")" '$2y$'

as d
for _ in 1 2 3 4 5; do CSRF=$(token login.php); post auth_process.php action=login nim=1001 password=tebak12345 >/dev/null; done
CSRF=$(token login.php)
post auth_process.php action=login nim=1001 password=rahasia123 >/dev/null
expect_contains 'five failures lock the NIM for a while' "$(get login.php)" 'Terlalu banyak percobaan'

as a
CSRF=$(token index.php)
expect 'log out' "$(post auth_process.php action=logout)" '303 index.php'
expect 'logged out: no result pages' "$(status final.php)" '303'

expect 'the server logged no PHP warnings' "$(grep -ciE 'warning|notice|deprecated|fatal' "$WORK/php.log")" '0'

echo "$PASSED passed, $FAILED failed"
[ "$FAILED" -eq 0 ] || { echo "--- server log (last 20 lines)"; tail -20 "$WORK/php.log"; }
[ "$FAILED" -eq 0 ]
