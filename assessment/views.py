"""The pages: home, accounts, the test, results."""

from datetime import timedelta
from pathlib import Path

from django.conf import settings
from django.contrib import messages
from django.contrib.auth import authenticate, login as auth_login, logout as auth_logout
from django.contrib.auth.decorators import login_required
from django.db import IntegrityError, transaction
from django.http import Http404, HttpResponseNotFound
from django.shortcuts import get_object_or_404, redirect, render
from django.template.loader import render_to_string
from django.urls import reverse
from django.utils import timezone
from django.views.decorators.http import require_POST

from .accounts import (LOGIN_WINDOW, MAX_PASSWORD_LENGTH, clear_login_failures, client_ip, login_blocked,
                       record_login_failure, validate_registration)
from .fuzzy import career_fits
from .models import MAX_AGE, MIN_AGE, Attempt, Question, SectionInfo, SectionRun, Student
from .scoring import grade_result
from .sections import MAX_ANSWER_LENGTH, SECTIONS, mark_answer, next_section

REFERENCE_PROFILE = [8, 8, 4, 8, 8, 5, 6, 4, 6]   # "Computer dan IT", per subject


def now():
    """The site's clock (tests replace it to check the time limits)."""
    return timezone.now()


def index(request):
    return render(request, "assessment/index.html")


def not_found(request, exception=None):
    return HttpResponseNotFound(render_to_string("assessment/not_found.html", request=request))


# ── accounts ───────────────────────────────────────────────────────────────
def register(request):
    if request.user.is_authenticated:
        return redirect("index")
    if request.method == "POST":
        errors, form = validate_registration(request.POST)
        if not errors:
            try:
                with transaction.atomic():
                    Student.objects.create_user(form["nim"], form["name"], form["age"], form["password"])
            except IntegrityError:
                errors = ["NIM sudah terdaftar."]
        if not errors:
            messages.info(request, "Akun dibuat. Silakan masuk.")
            return redirect("login")
        for error in errors:
            messages.error(request, error)
        return redirect("register")
    return render(request, "assessment/register.html",
                  {"min_age": MIN_AGE, "max_age": MAX_AGE, "max_password": MAX_PASSWORD_LENGTH})


def login(request):
    if request.user.is_authenticated:
        return redirect("index")
    if request.method == "GET":
        return render(request, "assessment/login.html")

    nim = request.POST.get("nim", "").strip()
    ip = client_ip(request)
    if login_blocked(nim, ip):
        messages.error(request, f"Terlalu banyak percobaan masuk. Coba lagi dalam "
                                f"{int(LOGIN_WINDOW.total_seconds()) // 60} menit.")
        return redirect("login")
    # Django hashes once for an unknown NIM too, so both answers take as long; an
    # imported bcrypt password is re-hashed with the current hasher on success
    student = authenticate(request, nim=nim, password=request.POST.get("password", ""))
    if student is None:
        record_login_failure(nim, ip)
        messages.error(request, "NIM atau kata sandi salah.")
        return redirect("login")
    clear_login_failures(nim)
    auth_login(request, student)           # a new session id: no fixation
    return redirect("index")


@require_POST
def logout(request):
    auth_logout(request)                   # deletes the session on the server
    return redirect("index")


# ── the test ───────────────────────────────────────────────────────────────
def section_seconds(name: str) -> int:
    return settings.SECTION_SECONDS.get(name, SECTIONS[name].seconds)


def current_attempt(request) -> Attempt | None:
    attempt_id = request.session.get("attempt")
    return Attempt.objects.filter(id=attempt_id, student=request.user).first() if attempt_id else None


def after_section(attempt: Attempt, name: str) -> str:
    """Where to go once a section is over: the next section's instructions, or the result."""
    following = next_section(name)
    if following is None:
        return reverse("result", args=[attempt.id])
    return reverse("section", args=[following])


def section_and_attempt(request, name):
    if name not in SECTIONS:
        raise Http404
    attempt = current_attempt(request)
    return SECTIONS[name], attempt


def run_of(attempt: Attempt, name: str) -> SectionRun | None:
    return attempt.runs.filter(section=name).first()


@login_required
def test(request):
    return render(request, "assessment/test.html", {"sections": SECTIONS.values()})


@login_required
@require_POST
def start_test(request):
    attempt = Attempt.objects.create(student=request.user, started_at=now())
    request.session["attempt"] = attempt.id
    return redirect("section", next(iter(SECTIONS)))


@login_required
def section(request, name):
    s, attempt = section_and_attempt(request, name)
    if attempt is None:
        return redirect("test")
    run = run_of(attempt, name)
    if run is not None and run.seconds_left(now()) == 0:
        return redirect(after_section(attempt, name))
    info = SectionInfo.objects.filter(section=name).first()
    return render(request, "assessment/section.html", {
        "section": s, "count": Question.objects.filter(section=name).count(),
        "type_questions": info.type_questions if info else "-", "minutes": section_seconds(name) // 60,
        "instructions": f"assessment/instructions/{name}.html",
    })


@login_required
def question(request, name, number):
    s, attempt = section_and_attempt(request, name)
    if attempt is None:
        return redirect("test")
    count = Question.objects.filter(section=name).count()
    if not 1 <= number <= count:
        raise Http404
    if request.method == "POST":
        return answer(request, attempt, s, number, count)

    run = run_of(attempt, name)
    if run is not None and run.seconds_left(now()) == 0:
        return redirect(after_section(attempt, name))
    q = get_object_or_404(Question, section=name, number=number)
    if run is None:                          # the section's clock starts at its first question, once
        run, _ = SectionRun.objects.get_or_create(
            attempt=attempt, section=name,
            defaults={"deadline": now() + timedelta(seconds=section_seconds(name)),
                      "marks": s.blank_result})

    image = f"images/{name}/{number}.jpg"
    static_dir = Path(__file__).parent / "static"
    show_image = s.image == "always" or (s.image == "if_exists" and (static_dir / image).is_file())
    return render(request, "assessment/question.html", {
        "section": s, "number": number, "count": count, "question": q,
        "seconds": run.seconds_left(now()), "next_url": after_section(attempt, name),
        "image": image if show_image else None, "max_answer": MAX_ANSWER_LENGTH,
        "choices": list(enumerate(q.choices, 1)) if s.input == "text_choice" else [],
        "choice_images": [(i, f"images/{name}/{number}_{i}.jpg") for i in range(1, s.choices + 1)],
        "fields": range(1, s.choices + 1),
    })


def answer(request, attempt, section, number, count):
    with transaction.atomic():
        run = attempt.runs.select_for_update().filter(section=section.name).first()
        # the server's clock decides: no answer before the section started or after it ended
        if run is None or not run.accepts_answers(now()):
            return redirect(after_section(attempt, section.name))
        given = [request.POST.get(field, "")[:MAX_ANSWER_LENGTH] for field in section.answer_fields]
        correct = Question.objects.filter(section=section.name, number=number).values_list("answer", flat=True).first()
        marks = mark_answer(section, correct or "", given)
        position = (number - 1) * section.slots_per_question
        if position + len(marks) > section.length:
            raise RuntimeError(f"{section.name} has more questions than its result has slots")
        run.marks = run.marks[:position] + marks + run.marks[position + len(marks):]
        run.save(update_fields=["marks"])

    if number == count:
        return redirect(after_section(attempt, section.name))
    return redirect("question", section.name, number + 1)


# ── results ────────────────────────────────────────────────────────────────
@login_required
def results(request):
    return render(request, "assessment/results.html", {"attempts": request.user.attempts.all()})


def potential_color(grade: int) -> str:
    return f"rgba({min(255, (10 - grade) * 30)}, 99, 132, 0.6)"


@login_required
def result(request, attempt_id):
    attempt = get_object_or_404(Attempt, id=attempt_id, student=request.user)   # only the student's own tests
    grades = grade_result(attempt.marks(), request.user.age)
    potentials = [grade["potensi"] for grade in grades.values()]
    chart_data = {
        "labels": [s.label for s in SECTIONS.values()],
        "progress": [grade["progress"] for grade in grades.values()],
        "potential": potentials,
        "potentialColors": [potential_color(p) for p in potentials],
        "reference": REFERENCE_PROFILE,
    }
    return render(request, "assessment/result.html", {
        "attempt": attempt, "started": timezone.localtime(attempt.started_at), "grades": grades.values(),
        "careers": career_fits(potentials), "sections": SECTIONS.values(), "chart_data": chart_data,
    })
