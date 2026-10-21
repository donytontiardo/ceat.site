# ceat.site

The aptitude assessment website using the Mamdani fuzzy inference model, by Dony Tontiardo.

Students take nine timed sections (visual, numerical, verbal, sequential, spatial,
3D, system, vocabulary, figurework). Their scores become nine potential grades
(0–10), and a fuzzy model rates how well those grades fit five computer careers:
Computer Hardware Engineer, Computer Network Specialist, Computer Programmer,
Computer System Analyst and Database Administrator. The membership functions and
rules were set by an expert, for computer engineering students.

This is the Python version (Flask, SQLite). The PHP versions are in the git
history: the original site at `bf8e06f`, its hardened rewrite at `6a52edc`.

## Layout

```
ceat/
  __init__.py      create_app(): settings, security hooks, error pages
  views.py         the pages: home, register, login, the test, results
  security.py      security headers, CSRF, login sessions kept in the database
  db.py            every database query (bound parameters only)
  schema.sql       the SQLite database
  sections.py      the nine sections: time limits, answer types, marking
  scoring.py       scores -> progress -> potential grades (the grading tables)
  fuzzy.py         the fuzzy model: memberships, rules, defuzzification
  accounts.py      registration rules, password checks
  cli.py           flask init, flask import-mysql
  templates/       pages, and each section's instructions
  static/          css, js (Chart.js 4.4.1, MIT), images
tests/             pytest: scoring, the site end to end, the MySQL import
```

URLs: `/`, `/register`, `/login`, `/logout` (POST), `/test`, `/test/<section>`,
`/test/<section>/<n>`, `/results`, `/results/<id>`.

## Run it

Needs Python 3.10+.

```
python -m venv .venv && . .venv/bin/activate
pip install -e '.[server]'
flask --app ceat init          # instance/config.py with a secret key, and instance/ceat.sqlite
```

Then load the questions into `info_section` and the `section_*` tables, or copy
everything (accounts, results, questions) from the PHP site's MySQL database:

```
pip install -e '.[mysql]'
flask --app ceat import-mysql --host localhost --user ceat --database ceat
```

Passwords are copied as they are (plain text from the original site, bcrypt from
the PHP rewrite) and replaced with a new hash at each student's next login. A NIM
the original site registered twice keeps its first account.

Local try-out over plain HTTP:

```
CEAT_SESSION_COOKIE_SECURE=false flask --app ceat run
```

In production, run it with gunicorn behind a web server that serves HTTPS, and
tell it about that proxy so login throttling sees the students' real addresses:

```
CEAT_PROXIES=1 gunicorn -w 4 -b 127.0.0.1:8000 'ceat:create_app()'
```

Settings go in `instance/config.py` (or `CEAT_<NAME>` environment variables):
`SECRET_KEY`, `DATABASE`, `TIMEZONE` (Asia/Jakarta), `PROXIES`,
`SESSION_COOKIE_SECURE`, and `SECTION_SECONDS` for shorter time limits in a trial
run, e.g. `{"visual": 60}`.

## Security

- Every query uses bound parameters; section and column names come from a fixed list.
- Passwords are hashed with scrypt.
- Failed logins are limited: 5 per NIM or address in 15 minutes. A wrong password
  and an unknown NIM give the same message and take the same time.
- Logins are sessions stored in the database; the cookie only carries a random
  key. Logging out ends the session on the server, so a copied cookie stops
  working, and sessions expire after 2 idle hours.
- Every form carries a CSRF token; logging out is a POST.
- The session cookie is HttpOnly, SameSite=Lax and Secure.
- Templates escape everything a student types. Question text is shown as HTML,
  so only administrators may write the `section_*` tables.
- A Content Security Policy allows only the site's own scripts; there are no
  inline scripts.
- The server keeps the time limits: a section's clock starts at its first
  question, is stored with the test and never restarts, and answers after it
  ends are not recorded.
- A student can only see their own results.

## Scoring fixes

The PHP rewrite fixed five bugs of the original site, and this version keeps the
same results (the tests check it against reference data from that version):

1. `if ($x = 2)` assigned instead of comparing (in every career's file).
2. A rule's strength took figurework's value whenever two memberships tied for
   the lowest; it is now the minimum of the nine.
3. Computer Programmer: a sequential grade of 4 to 8 counted as poor.
4. Defuzzification added nothing between 50 and 80 when the low-fit output was
   stronger, so every student without a high-fit rule scored exactly 30, which the
   result page read as "incomplete answers"; it now checks for no high-fit rule.
5. Grading: some exact scores fell between two grade ranges in floating point and
   got grade 0 (65 correct in system: 29.900000000000002). The grading now counts
   in hundredths, exactly.

Fuzzy career scores therefore differ from the original site for most students.

## Tests

```
pip install -e '.[test]'
pytest
```

- `test_scoring.py`: the fuzzy model and grading against reference outputs of the
  PHP version (`tests/fixtures/php_reference.json`, 1310 grade sets and every score
  at nine ages), which was checked against the original code with the fixes above.
- `test_app.py`: registering, logging in, a whole test, the time limits, and what
  must not work (injection, other students' results, missing CSRF tokens, stolen
  cookies, password guessing).
- `test_import.py`: `flask import-mysql` from both earlier MySQL schemas on a
  scratch MariaDB; skipped where `mariadbd` is not installed.
