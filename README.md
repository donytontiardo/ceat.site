# ceat.site

The aptitude assessment website using the Mamdani fuzzy inference model, by Dony Tontiardo.

Students take nine timed sections (visual, numerical, verbal, sequential, spatial,
3D, system, vocabulary, figurework). Their scores become nine potential grades
(0–10), and a fuzzy model rates how well those grades fit five computer careers:
Computer Hardware Engineer, Computer Network Specialist, Computer Programmer,
Computer System Analyst and Database Administrator. The membership functions and
rules were set by an expert, for computer engineering students.

This is the Django version, with SQLite. Earlier versions are in the git history:
the original PHP site at `bf8e06f`, its hardened PHP rewrite at `6a52edc`, and a
Flask version on the `python-flask` branch.

## Layout

```
ceatsite/          settings, URLs, WSGI
assessment/        the app
  models.py        Student, Question, SectionInfo, Attempt, SectionRun, LoginFailure
  views.py         home, register, login, the test, results
  admin.py         the admin: questions, students, tests
  accounts.py      registration rules, login throttling, the client's address
  sections.py      the nine sections: time limits, answer types, marking
  scoring.py       scores -> progress -> potential grades (the grading tables)
  fuzzy.py         the fuzzy model: memberships, rules, defuzzification
  management/commands/import_mysql.py
  templates/, static/   pages, section instructions, css, js (Chart.js 4.4.1, MIT), images
tests/             pytest: scoring, the site end to end, the MySQL import
```

URLs: `/`, `/register`, `/login`, `/logout` (POST), `/test`, `/test/<section>`,
`/test/<section>/<n>`, `/results`, `/results/<id>`, and `/admin/` for staff.

## Run it

Needs Python 3.12+. Settings come from environment variables:

| variable | |
|---|---|
| `CEAT_SECRET_KEY` | required; e.g. `python -c "import secrets; print(secrets.token_urlsafe(50))"` |
| `CEAT_ALLOWED_HOSTS` | the site's domain names, comma-separated |
| `CEAT_DATABASE` | the SQLite file (default `db.sqlite3` here) |
| `CEAT_HTTPS` | `1` (default): secure cookies, HSTS, http:// redirected to https:// |
| `CEAT_PROXIES` | reverse proxies in front, e.g. `1` behind nginx |
| `CEAT_TIMEZONE` | default `Asia/Jakarta` |
| `CEAT_SECTION_SECONDS` | shorter time limits for a trial run, e.g. `visual:60,numerical:60` |
| `CEAT_DEBUG` | `1` only on a development machine |

```
python -m venv .venv && . .venv/bin/activate
pip install -e '.[server]'
python manage.py migrate
python manage.py createsuperuser      # a staff account (NIM, name, age, password) for /admin/
python manage.py collectstatic
gunicorn -w 4 -b 127.0.0.1:8000 ceatsite.wsgi
```

Run gunicorn behind a web server that serves HTTPS (and set `CEAT_PROXIES=1`).
For a local try-out: `CEAT_DEBUG=1 CEAT_HTTPS=0 python manage.py runserver`.

Questions, section info and accounts are managed in the admin at `/admin/`. To copy
everything (students, tests, questions) from the PHP site's MySQL database instead:

```
pip install -e '.[mysql]'
python manage.py import_mysql --host localhost --user ceat --database ceat
```

Plain-text passwords of the original site are hashed during the import; bcrypt
hashes of the PHP rewrite are kept and replaced at each student's next login. A NIM
the original site registered twice keeps its first account. The 9 question tables
become one `Question` model.

`manage.py check --deploy` still names two settings that depend on your domain:
`SECURE_HSTS_INCLUDE_SUBDOMAINS` and `SECURE_HSTS_PRELOAD`. Turn them on only if
every subdomain is served over HTTPS.

## Security

Mostly Django's own, configured strictly:

- The ORM binds every value; section names come from a fixed list.
- Passwords hashed with PBKDF2 and checked against Django's validators (length,
  common passwords, all-numeric). Older hashes are upgraded at login.
- Sessions are stored in the database: logging out ends the session on the
  server, and sessions expire after 2 idle hours. The cookies are HttpOnly,
  SameSite=Lax and Secure.
- CSRF protection on every form; logging out is a POST.
- Failed logins are limited: 5 per NIM or address in 15 minutes. A wrong password
  and an unknown NIM give the same message and take the same time.
- Templates escape everything a student types. Question text is shown as HTML, so
  it is written only by staff, in the admin.
- A Content Security Policy allows only the site's own scripts and no inline
  scripts or styles, on the site and in the admin (a test checks every page).
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
- `test_app.py`: registering, logging in, a whole test, the time limits, the
  admin, what must not work (injection, other students' results, missing CSRF
  tokens, copied cookies, password guessing), no page needing inline scripts or
  styles, and collecting the static files for production.
- `test_import.py`: `import_mysql` from both earlier MySQL schemas on a scratch
  MariaDB; skipped where `mariadbd` is not installed.
