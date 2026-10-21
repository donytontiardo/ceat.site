# ceat.site

The aptitude assessment website using the Mamdani fuzzy inference model, by Dony Tontiardo.

Students take nine timed sections (visual, numerical, verbal, sequential, spatial,
3D, system, vocabulary, figurework). Their scores become nine potential grades
(0–10), and a fuzzy model rates how well those grades fit five computer careers:
Computer Hardware Engineer, Computer Network Specialist, Computer Programmer,
Computer System Analyst and Database Administrator. The membership functions and
rules were set by an expert, for computer engineering students.

## Layout

```
public/              the web root: pages, css, js, images
  index.php          home
  regis.php          create an account          login.php     log in
  auth_process.php   register / log in / log out (POST only)
  indextest.php      the sections and each section's instructions
  question.php       one question, with the section's timer
  process.php        start a test, record an answer (POST only)
  indexresult.php    the student's tests        final.php     one test's result
src/
  app.php            settings, security headers, session, CSRF, helpers
  store.php          every database query (prepared statements only)
  sections.php       the nine sections: time limits, answer types, marking
  scoring.php        scores -> progress -> potential grades (the grading tables)
  fuzzy.php          the fuzzy model: memberships, rules, defuzzification
  accounts.php       registration rules, password checks
  views/             page header and footer, section instructions
schema.sql           the database, for a new install
migrations/          changes for a database from the original version
tests/               unit and end-to-end tests (see below)
```

## Install

Needs PHP 8.1+ with `pdo_mysql` and `mbstring`, and MySQL or MariaDB.

1. Create a database and run `schema.sql`, then fill `info_section` and the
   `section_*` tables with the questions. A database from the original version:
   back it up and run `migrations/2026-10-21-hardening.sql` instead.
2. Copy `config.example.php` to `config.php` and fill in the database login.
3. Point the web server's document root at `public/`, and serve it over HTTPS.
   With Apache, the `.htaccess` files also keep everything outside `public/`
   unreachable if the root is set to the project folder by mistake.

## Security

- Every query is a prepared statement; section and column names come from a fixed list.
- Passwords are stored with `password_hash()`. Accounts from the original version,
  which stored plain text, log in once more and are then re-hashed automatically.
- Failed logins are limited: 5 per NIM or address in 15 minutes. A wrong password
  and an unknown NIM give the same message and take the same time.
- Every form carries a CSRF token; logging out is a POST.
- The session cookie is HttpOnly, SameSite=Lax and Secure under HTTPS, and the
  session id changes at login.
- Everything a student types is escaped on output. Question text is shown as
  HTML, so only administrators may write the `section_*` tables.
- A Content Security Policy allows only the site's own scripts; there are no inline
  scripts. Chart.js 4.4.1 is served from `public/js` (MIT license alongside).
- The time limits are kept by the server: a section's clock starts at its first
  question, and answers after it ends are not recorded.
- A student can only see their own results.
- Errors are logged on the server; the student sees a plain message.

## Scoring fixes

The rewrite fixes five bugs of the original version. Fuzzy career scores
therefore differ from the original for most students (in the tests, 5062 of
5550 scores).

1. `if ($x = 2)` assigned instead of comparing (in every career's file). The fair
   set was forced to 1 and the grade was overwritten for the checks after it.
2. A rule's strength took figurework's value whenever two memberships tied for the
   lowest; it is now the minimum of the nine, as Mamdani's AND requires.
3. Computer Programmer: the sequential fair set wrote the poor set on its plateau
   and falling edge, so a sequential grade of 4 to 8 counted as poor.
4. Defuzzification: when the low-fit output was stronger, the samples between 50
   and 80 added nothing; they now follow the low-fit ramp, as the high-fit side did.
   Because of this bug every student without a high-fit rule scored exactly 30,
   which the result page read as "incomplete answers"; it now checks for no
   high-fit rule directly.
5. Grading: some exact scores fell between two grade ranges because of floating
   point and got grade 0 (e.g. 65 correct in system: 65 × 0.46 = 29.900000000000002,
   between 29.90 and 29.91). Scores are now rounded to the tables' two decimals.

Also: the Database Administrator result showed its code "DA" instead of its name;
answers ignore surrounding spaces; and the result page lists all five careers'
scores (the former `final2.php`).

## Tests

```
php tests/run.php        # unit tests
tests/e2e.sh             # the site end to end (needs mariadb-server and curl)
```

The unit tests check the fuzzy model against the original `fuzzy*.php` files with
only the four fixes above applied as text patches (`tests/legacy/fixed.php`),
over thousands of inputs, and the grading against the original `final.php`
code for every score and age. The end-to-end test registers, logs in and takes a
test on a scratch MariaDB database, and checks the security measures above.
