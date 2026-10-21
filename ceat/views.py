"""The pages: home, accounts, the test, results."""

import sqlite3
from pathlib import Path

from flask import Blueprint, abort, current_app, flash, g, redirect, render_template, request, url_for

from . import db
from .accounts import (MAX_AGE, MAX_PASSWORD_LENGTH, MIN_AGE, MIN_PASSWORD_LENGTH, check_password, dummy_hash,
                       hash_password, validate_registration)
from .fuzzy import career_fits
from .scoring import grade_result
from .sections import MAX_ANSWER_LENGTH, SECTIONS, mark_answer, next_section
from .security import current_session_hash, log_in, log_out, login_required

bp = Blueprint("main", __name__)

ANSWER_GRACE_SECONDS = 3          # an answer sent as the timer runs out still counts
REFERENCE_PROFILE = [8, 8, 4, 8, 8, 5, 6, 4, 6]   # "Computer dan IT", per subject


@bp.route("/")
def index():
    return render_template("index.html")


# ── accounts ───────────────────────────────────────────────────────────────
@bp.route("/register", methods=["GET", "POST"])
def register():
    if g.user is not None:
        return redirect(url_for("main.index"))
    if request.method == "POST":
        errors, form = validate_registration(request.form)
        if not errors and db.find_user_by_nim(form["nim"]) is not None:
            errors = ["NIM sudah terdaftar."]
        if not errors:
            try:
                db.create_user(form["name"], form["age"], form["nim"], hash_password(form["password"]))
            except sqlite3.IntegrityError:            # the same NIM registered at the same moment
                errors = ["NIM sudah terdaftar."]
        if not errors:
            flash("Akun dibuat. Silakan masuk.")
            return redirect(url_for("main.login"))
        for error in errors:
            flash(error)
        return redirect(url_for("main.register"))
    return render_template("register.html", min_age=MIN_AGE, max_age=MAX_AGE,
                           min_password=MIN_PASSWORD_LENGTH, max_password=MAX_PASSWORD_LENGTH)


@bp.route("/login", methods=["GET", "POST"])
def login():
    if g.user is not None:
        return redirect(url_for("main.index"))
    if request.method == "GET":
        return render_template("login.html")

    nim = request.form.get("nim", "").strip()
    password = request.form.get("password", "")
    ip = request.remote_addr or ""
    if db.login_blocked(nim, ip):
        flash(f"Terlalu banyak percobaan masuk. Coba lagi dalam {db.LOGIN_WINDOW_SECONDS // 60} menit.")
        return redirect(url_for("main.login"))
    user = db.find_user_by_nim(nim)
    # the same check and message whether or not the NIM exists: no account enumeration
    ok, rehash = check_password(user["password"] if user else dummy_hash(), password)
    if user is None or not ok:
        db.record_login_failure(nim, ip)
        flash("NIM atau kata sandi salah.")
        return redirect(url_for("main.login"))
    if rehash:
        db.set_password_hash(user["id_user"], hash_password(password))
    db.clear_login_failures(nim)
    log_in(user["id_user"])
    return redirect(url_for("main.index"))


@bp.route("/logout", methods=["POST"])
def logout():
    log_out()
    return redirect(url_for("main.index"))


# ── the test ───────────────────────────────────────────────────────────────
def section_seconds(name: str) -> int:
    return current_app.config["SECTION_SECONDS"].get(name, SECTIONS[name].seconds)


def seconds_left(name: str) -> int | None:
    """Seconds left in a section of the current test; None before its first question."""
    deadline = db.deadline(g.user["id_result"], name)
    return None if deadline is None else max(0, deadline - db.now())


def after_section(name: str) -> str:
    """Where to go once a section is over: the next section's instructions, or the result."""
    following = next_section(name)
    if following is None:
        return url_for("main.result", result_id=g.user["id_result"])
    return url_for("main.section_intro", section=following)


def current_section(name: str):
    """The section and the test in progress, or 404 / back to the start."""
    if name not in SECTIONS:
        abort(404)
    if g.user["id_result"] is None:
        abort(redirect(url_for("main.test")))
    return SECTIONS[name]


@bp.route("/test")
@login_required
def test():
    return render_template("test.html", sections=SECTIONS.values())


@bp.route("/test/start", methods=["POST"])
@login_required
def start_test():
    result_id = db.create_result(g.user["id_user"])
    db.set_session_test(current_session_hash(), result_id)
    return redirect(url_for("main.section_intro", section=next(iter(SECTIONS))))


@bp.route("/test/<section>")
@login_required
def section_intro(section):
    s = current_section(section)
    if seconds_left(section) == 0:
        return redirect(after_section(section))
    return render_template("section.html", section=s, count=db.question_count(section),
                           info=db.section_info(section), minutes=section_seconds(section) // 60)


@bp.route("/test/<section>/<int:number>", methods=["GET", "POST"])
@login_required
def question(section, number):
    s = current_section(section)
    if not 1 <= number <= db.question_count(section):
        abort(404)
    if request.method == "POST":
        return answer(section, s, number)
    if seconds_left(section) == 0:
        return redirect(after_section(section))
    row = db.find_question(section, number)
    if row is None:
        abort(404)
    deadline = db.start_clock(g.user["id_result"], section, section_seconds(section))
    image = f"images/{section}/{number}.jpg"
    show_image = s.image == "always" or (
        s.image == "if_exists" and (Path(current_app.static_folder) / image).is_file())
    return render_template("question.html", section=s, number=number, question=row,
                           count=db.question_count(section), seconds=max(0, deadline - db.now()),
                           next_url=after_section(section), image=image if show_image else None,
                           max_answer=MAX_ANSWER_LENGTH)


def answer(section_name, section, number):
    result_id = g.user["id_result"]
    # the server's clock decides: no answer before the section started or after it ended
    deadline = db.deadline(result_id, section_name)
    if deadline is None or db.now() > deadline + ANSWER_GRACE_SECONDS:
        return redirect(after_section(section_name))

    given = [request.form.get(field, "")[:MAX_ANSWER_LENGTH] for field in section.answer_fields]
    marks = mark_answer(section, db.correct_answer(section_name, number) or "", given)
    position = (number - 1) * section.slots_per_question
    if position + len(marks) > section.length:
        raise RuntimeError(f"section_{section_name} has more questions than result_{section_name} has slots")
    db.write_marks(result_id, g.user["id_user"], section_name, position, marks)

    if number == db.question_count(section_name):
        return redirect(after_section(section_name))
    return redirect(url_for("main.question", section=section_name, number=number + 1))


# ── results ────────────────────────────────────────────────────────────────
@bp.route("/results")
@login_required
def results():
    return render_template("results.html", results=db.results_of(g.user["id_user"]))


def potential_color(grade: int) -> str:
    return f"rgba({min(255, (10 - grade) * 30)}, 99, 132, 0.6)"


@bp.route("/results/<int:result_id>")
@login_required
def result(result_id):
    row = db.find_result(result_id, g.user["id_user"])     # only the student's own tests
    if row is None:
        abort(404)
    age = db.find_user(g.user["id_user"])["age"]
    grades = grade_result(row, age)
    potentials = [grade["potensi"] for grade in grades.values()]
    chart_data = {
        "labels": [s.label for s in SECTIONS.values()],
        "progress": [grade["progress"] for grade in grades.values()],
        "potential": potentials,
        "potentialColors": [potential_color(p) for p in potentials],
        "reference": REFERENCE_PROFILE,
    }
    return render_template("result.html", result=row, grades=grades, careers=career_fits(potentials),
                           sections=SECTIONS.values(), chart_data=chart_data)
