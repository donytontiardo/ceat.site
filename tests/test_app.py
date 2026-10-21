"""The site end to end through Django's test client (with CSRF checks on), on a
scratch database with two questions per section (conftest.QUESTIONS)."""

import bcrypt
import pytest
from django.contrib.auth.hashers import make_password

from assessment.models import Attempt, SectionRun, Student

from .conftest import PASSWORD, Browser, location

pytestmark = pytest.mark.django_db


def logged_in(browser, nim="1001", name="Ani", age=21):
    browser.register(name, nim, age)
    browser.login(nim)
    return browser


def started(browser):
    logged_in(browser)
    browser.get("/test")
    browser.post("/test/start")
    return browser


def marks(section):
    return SectionRun.objects.get(section=section).marks


# ── headers ────────────────────────────────────────────────────────────────
def test_security_headers_and_cookies(browser):
    response = browser.get("/register")
    assert "script-src 'self';" in response.headers["Content-Security-Policy"]
    assert response.headers["X-Frame-Options"] == "DENY"
    assert response.headers["X-Content-Type-Options"] == "nosniff"
    logged_in(browser)
    session = browser.client.cookies["ceat_session"]
    assert session["httponly"] and session["samesite"] == "Lax"


# ── accounts ───────────────────────────────────────────────────────────────
def test_register_validates_and_hashes(browser):
    assert location(browser.register("Ani", "1001", 21, "short")) == "302 /register"
    assert "terlalu pendek" in browser.text("/register")          # Django's validators, in Indonesian
    assert location(browser.register("Ani", "1001", 21, "12345678")) == "302 /register"
    assert location(browser.register("Ani", "1001", 21)) == "302 /login"
    assert Student.objects.get(nim="1001").password.startswith("pbkdf2_sha256$")
    assert location(browser.register("Budi", "1001", 22, "rahasia456")) == "302 /register"
    assert "NIM sudah terdaftar" in browser.text("/register")
    assert location(browser.register("Budi", "12a", 5)) == "302 /register"
    page = browser.text("/register")
    assert "NIM hanya berisi angka" in page and "Usia harus angka" in page


def test_login(browser):
    browser.register("<script>alert(1)</script>", "1001", 21)
    assert location(browser.login("1001", "salah12345")) == "302 /login"
    assert "NIM atau kata sandi salah" in browser.text("/login")
    assert location(browser.login("9999")) == "302 /login"
    assert "NIM atau kata sandi salah" in browser.text("/login"), "same message for an unknown NIM"
    assert location(browser.login("1001")) == "302 /"
    home = browser.text("/")
    assert "&lt;script&gt;alert(1)&lt;/script&gt;" in home
    assert "<script>alert(1)" not in home


def test_posts_need_the_csrf_token(browser):
    browser.register("Ani", "1001", 21)
    browser.get("/login")
    assert browser.post("/login", {"nim": "1001", "password": PASSWORD}, csrf=False).status_code == 403
    assert browser.post("/login", {"nim": "1001", "password": PASSWORD, "csrfmiddlewaretoken": "x" * 64}).status_code == 403
    assert browser.client.get("/logout").status_code == 405


def test_logout_ends_the_session_on_the_server(browser):
    logged_in(browser)
    stolen = browser.client.cookies["ceat_session"].value
    browser.get("/")
    assert location(browser.post("/logout")) == "302 /"
    assert location(browser.get("/results")) == "302 /login?next=/results"
    thief = Browser()
    thief.client.cookies["ceat_session"] = stolen
    assert location(thief.get("/results")) == "302 /login?next=/results", "a copied cookie is worth nothing"


def test_login_throttling(browser):
    browser.register("Ani", "1001", 21)
    guesser = Browser()
    for _ in range(5):
        guesser.login("1001", "tebak12345")
    guesser.login("1001")
    assert "Terlalu banyak percobaan" in guesser.text("/login")


def test_imported_passwords_log_in_and_are_rehashed(browser):
    php_hash = bcrypt.hashpw(b"phppass12", bcrypt.gensalt()).decode()
    Student.objects.create(nim="4004", name="PHP", age=20, password="bcrypt$" + php_hash)
    Student.objects.create(nim="3003", name="Lama", age=20, password=make_password("plainpass1"))
    assert location(browser.login("4004", "phppass12")) == "302 /"
    assert Student.objects.get(nim="4004").password.startswith("pbkdf2_sha256$")
    browser.get("/")
    browser.post("/logout")
    assert location(browser.login("3003", "plainpass1")) == "302 /"


# ── the test ───────────────────────────────────────────────────────────────
def test_a_whole_test(browser, clock):
    logged_in(browser)
    browser.get("/test")
    assert location(browser.post("/test/start")) == "302 /test/visual"
    assert "Jumlah Pertanyaan: 2" in browser.text("/test/visual")
    page = browser.text("/test/visual/1")
    assert "Question 1 of 2 (Visual)" in page
    assert "paling <i>berbeda</i>" in page, "question HTML written by staff is kept"
    assert 'data-seconds="600"' in page

    assert location(browser.answer("visual", 1, choice="2")) == "302 /test/visual/2"
    assert location(browser.answer("visual", 2, choice="3")) == "302 /test/numerical"
    assert marks("visual") == "BS" + "K" * 30

    browser.answer("numerical", 1, choice="4")
    browser.answer("numerical", 2, choice="")
    assert marks("numerical")[:3] == "BKK"
    assert "Daybridge" in browser.text("/test/verbal/1")
    browser.answer("verbal", 1, choice="1")
    browser.answer("verbal", 2, choice="2")
    assert marks("verbal")[:3] == "BSK"
    browser.answer("sequential", 1, choice1="E", choice2="D")
    browser.answer("sequential", 2, choice1="B", choice2="A")
    assert marks("sequential")[:3] == "BSK"
    browser.answer("spatial", 1, choice1="N", choice2="Y", choice3="", choice4="Y", choice5="N")
    browser.answer("spatial", 2, **{f"choice{i}": "Y" for i in range(1, 6)})
    assert marks("spatial")[:11] == "BSKBBBBBBBK"
    browser.answer("3d", 1, choice=" iii ")
    browser.answer("3d", 2, choice="I")
    assert marks("3d")[:3] == "BSK"

    # system has a 2-second limit in the test settings: the second answer comes too late
    browser.answer("system", 1, **{f"choice{i}": c for i, c in enumerate("GFHKKBJDLGAC", 1)})
    browser.get("/test/system/2")
    clock.advance(6)
    assert location(browser.post("/test/system/2", {"choice1": "A"})) == "302 /test/vocabulary"
    assert marks("system")[:25] == "B" * 12 + "K" * 13
    assert browser.get("/test/system/1").status_code == 302, "the expired section sends on"

    browser.answer("vocabulary", 1, choice="1")
    browser.answer("vocabulary", 2, choice="2")
    assert marks("vocabulary")[:3] == "BBK"
    browser.answer("figurework", 1, choice="0.2")
    attempt = Attempt.objects.get()
    assert location(browser.answer("figurework", 2, choice="0.30")) == f"302 /results/{attempt.id}"
    assert marks("figurework")[:3] == "BSK"

    page = browser.text(f"/results/{attempt.id}")
    assert "Hasil Akhir" in page and "Database Administrator" in page and 'id="chart-data"' in page
    assert f"/results/{attempt.id}" in browser.text("/results")


def test_a_section_clock_never_restarts(browser, clock):
    started(browser)
    browser.get("/test/visual/1")
    first = SectionRun.objects.get().deadline
    clock.advance(100)
    browser.get("/test/visual/1")
    assert SectionRun.objects.get().deadline == first
    assert 'data-seconds="500"' in browser.text("/test/visual/2")


def test_what_must_not_work(browser):
    started(browser)
    attempt = Attempt.objects.get()
    assert browser.get("/test/visual%60--/1").status_code == 404, "section names are a whitelist"
    assert browser.get("/test/visual/99").status_code == 404
    assert browser.get(f"/results/{attempt.id}%20OR%201=1").status_code == 404

    other = logged_in(Browser(), nim="2002", name="Budi")
    assert other.get(f"/results/{attempt.id}").status_code == 404, "another student's result is not found"
    assert f"/results/{attempt.id}" not in other.text("/results")
    other.get("/")
    assert location(other.post("/test/visual/1", {"choice": "2"})) == "302 /test", "no answers without a test"
    assert location(browser.post("/test/visual/1", {"choice": "2"})) == "302 /test/numerical", \
        "no answer before the section's first question was shown"


# ── admin ──────────────────────────────────────────────────────────────────
def test_the_admin_is_for_staff_only(browser):
    logged_in(browser)
    assert browser.get("/admin/assessment/question/").status_code == 302
    Student.objects.create_superuser("9000", "Admin", 30, "adminpass123")
    admin = Browser()
    admin.get("/admin/login/")
    admin.post("/admin/login/", {"username": "9000", "password": "adminpass123"})
    page = admin.text("/admin/assessment/question/?section__exact=verbal")
    assert "Kota mana yang paling jauh" in page
    assert admin.get("/admin/assessment/attempt/").status_code == 200
    assert admin.get("/admin/assessment/student/add/").status_code == 200


def test_no_page_needs_inline_scripts_or_styles(browser):
    """The Content Security Policy blocks inline scripts and styles: no page may rely on them."""
    import re
    started(browser)
    browser.get("/test/visual/1")
    Student.objects.create_superuser("9000", "Admin", 30, "adminpass123")
    admin = Browser()
    admin.get("/admin/login/")
    admin.post("/admin/login/", {"username": "9000", "password": "adminpass123"})
    attempt = Attempt.objects.get()
    pages = [(browser, p) for p in ["/", "/test", "/test/visual", "/test/visual/1", "/test/verbal/1",
                                    "/test/system/1", "/results", f"/results/{attempt.id}"]]
    pages += [(admin, p) for p in ["/admin/", "/admin/assessment/question/", "/admin/assessment/question/1/change/",
                                   "/admin/assessment/student/add/", f"/admin/assessment/attempt/{attempt.id}/change/"]]
    for client, path in pages:
        html = client.text(path)
        inline_scripts = [s for s in re.findall(r"<script(?![^>]*\bsrc=)([^>]*)>", html) if "application/json" not in s]
        assert not inline_scripts, path
        assert not re.findall(r'\sstyle="', html), path
        assert not re.findall(r'\son[a-z]+="', html), path


def test_static_files_collect_for_production(tmp_path, settings):
    """The production storage hashes every file and checks the references inside them."""
    from django.core.management import call_command
    settings.STATIC_ROOT = tmp_path
    settings.STORAGES = {**settings.STORAGES, "staticfiles": {
        "BACKEND": "whitenoise.storage.CompressedManifestStaticFilesStorage"}}
    call_command("collectstatic", interactive=False, verbosity=0)
    assert (tmp_path / "staticfiles.json").exists()
