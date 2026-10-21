"""The site end to end through Flask's test client, on a scratch SQLite database
with two questions per section (tests/fixtures/questions.sql)."""

from conftest import location

PASSWORD = "rahasia123"


def logged_in(browser, nim="1001", name="Ani", age=21):
    browser.register(name, nim, age, PASSWORD)
    browser.login(nim, PASSWORD)
    return browser


def started(browser):
    logged_in(browser)
    browser.get("/test")
    browser.post("/test/start")
    return browser


def result(sql, section, result_id=1):
    return sql(f'SELECT "result_{section}" FROM result WHERE id_result = ?', (result_id,))[0][0]


# ── headers ────────────────────────────────────────────────────────────────
def test_security_headers_and_cookie(browser):
    response = browser.get("/register")
    assert "script-src 'self';" in response.headers["Content-Security-Policy"]
    assert response.headers["X-Frame-Options"] == "DENY"
    cookie = response.headers["Set-Cookie"]
    assert "HttpOnly" in cookie and "SameSite=Lax" in cookie


# ── accounts ───────────────────────────────────────────────────────────────
def test_register_validates_and_hashes(browser, sql):
    assert location(browser.register("Ani", "1001", 21, "short")) == "302 /register"
    assert "Kata sandi minimal 8" in browser.text("/register")
    assert location(browser.register("Ani", "1001", 21, PASSWORD)) == "302 /login"
    assert sql("SELECT password FROM user WHERE nim = '1001'")[0][0].startswith("scrypt:")
    assert location(browser.register("Budi", "1001", 22, "rahasia456")) == "302 /register"
    assert "NIM sudah terdaftar" in browser.text("/register")


def test_form_fields_sent_as_lists_are_refused(browser):
    browser.get("/register")
    response = browser.client.post("/register", data={"csrf": browser.csrf, "name": ["a", "b"], "nim": ["1", "2"],
                                                      "age": "21", "password": PASSWORD})
    assert response.status_code == 302                      # the first value is used; no crash


def test_login(browser):
    browser.register("<script>alert(1)</script>", "1001", 21, PASSWORD)
    assert location(browser.login("1001", "salah12345")) == "302 /login"
    assert "NIM atau kata sandi salah" in browser.text("/login")
    assert location(browser.login("9999", PASSWORD)) == "302 /login"
    assert "NIM atau kata sandi salah" in browser.text("/login"), "same message for an unknown NIM"
    assert location(browser.login("1001", PASSWORD)) == "302 /"
    home = browser.text("/")
    assert "&lt;script&gt;alert(1)&lt;/script&gt;" in home
    assert "<script>alert(1)" not in home


def test_posts_need_the_session_token(browser):
    browser.register("Ani", "1001", 21, PASSWORD)
    browser.get("/login")
    assert browser.post("/login", {"nim": "1001", "password": PASSWORD}, csrf=False).status_code == 400
    assert browser.post("/login", {"nim": "1001", "password": PASSWORD, "csrf": "nope"}).status_code == 400
    assert browser.client.get("/logout").status_code == 405


def test_logout_ends_the_session_on_the_server(app, browser):
    logged_in(browser)
    stolen = browser.client.get_cookie("ceat_session").value
    browser.get("/")
    assert location(browser.post("/logout")) == "302 /"
    assert location(browser.get("/results")) == "302 /login"
    thief = app.test_client()
    thief.set_cookie("ceat_session", stolen)
    assert location(thief.get("/results")) == "302 /login", "a copy of the cookie is worth nothing after logout"


def test_idle_sessions_expire(browser, clock):
    logged_in(browser)
    assert browser.get("/results").status_code == 200
    clock.advance(3 * 3600)
    assert location(browser.get("/results")) == "302 /login"


def test_login_throttling(browser, new_browser):
    browser.register("Ani", "1001", 21, PASSWORD)
    guesser = new_browser()
    for _ in range(5):
        guesser.login("1001", "tebak12345")
    guesser.login("1001", PASSWORD)
    assert "Terlalu banyak percobaan" in guesser.text("/login")


def test_old_passwords_are_rehashed(browser, sql):
    import bcrypt
    php_hash = bcrypt.hashpw(b"phppass12", bcrypt.gensalt()).decode().replace("$2b$", "$2y$", 1)
    sql("INSERT INTO user (name, age, nim, password) VALUES ('Lama', 20, '3003', 'plainpass1'),"
        " ('PHP', 20, '4004', ?)", (php_hash,))
    assert location(browser.login("3003", "plainpass1")) == "302 /"
    assert sql("SELECT password FROM user WHERE nim = '3003'")[0][0].startswith("scrypt:")
    browser.get("/")                       # logging in gave the session a new CSRF token
    assert location(browser.post("/logout")) == "302 /"
    assert location(browser.login("4004", "phppass12")) == "302 /"
    assert sql("SELECT password FROM user WHERE nim = '4004'")[0][0].startswith("scrypt:")


# ── the test ───────────────────────────────────────────────────────────────
def test_a_whole_test(browser, sql, clock):
    logged_in(browser)
    browser.get("/test")
    assert location(browser.post("/test/start")) == "302 /test/visual"
    assert "Jumlah Pertanyaan: 2" in browser.text("/test/visual")
    page = browser.text("/test/visual/1")
    assert "Question 1 of 2 (Visual)" in page
    assert "paling <i>berbeda</i>" in page, "question HTML from the database is kept"
    assert 'data-seconds="600"' in page

    assert location(browser.answer("visual", 1, choice="2")) == "302 /test/visual/2"
    assert location(browser.answer("visual", 2, choice="3")) == "302 /test/numerical"
    assert result(sql, "visual") == "BS" + "K" * 30

    browser.answer("numerical", 1, choice="4")
    browser.answer("numerical", 2, choice="")
    assert result(sql, "numerical")[:3] == "BKK"
    browser.answer("verbal", 1, choice="1")
    browser.answer("verbal", 2, choice="2")
    assert result(sql, "verbal")[:3] == "BSK"
    browser.answer("sequential", 1, choice1="E", choice2="D")
    browser.answer("sequential", 2, choice1="B", choice2="A")
    assert result(sql, "sequential")[:3] == "BSK"
    browser.answer("spatial", 1, choice1="N", choice2="Y", choice3="", choice4="Y", choice5="N")
    browser.answer("spatial", 2, **{f"choice{i}": "Y" for i in range(1, 6)})
    assert result(sql, "spatial")[:11] == "BSKBBBBBBBK"
    browser.answer("3d", 1, choice=" iii ")
    browser.answer("3d", 2, choice="I")
    assert result(sql, "3d")[:3] == "BSK"

    # system has a 2-second limit in the test config: the second answer comes too late
    browser.answer("system", 1, **{f"choice{i}": c for i, c in enumerate("GFHKKBJDLGAC", 1)})
    browser.get("/test/system/2")
    clock.advance(6)
    assert location(browser.post("/test/system/2", {"choice1": "A"})) == "302 /test/vocabulary"
    assert result(sql, "system")[:25] == "B" * 12 + "K" * 13
    assert browser.get("/test/system/1").status_code == 302, "the expired section sends on"

    browser.answer("vocabulary", 1, choice="1")
    browser.answer("vocabulary", 2, choice="2")
    assert result(sql, "vocabulary")[:3] == "BBK"
    browser.answer("figurework", 1, choice="0.2")
    assert location(browser.answer("figurework", 2, choice="0.30")) == "302 /results/1"
    assert result(sql, "figurework")[:3] == "BSK"

    page = browser.text("/results/1")
    assert "Hasil Akhir" in page and "Database Administrator" in page and 'id="chart-data"' in page
    assert "/results/1" in browser.text("/results")


def test_a_section_clock_never_restarts(browser, sql, clock):
    started(browser)
    browser.get("/test/visual/1")
    first = sql("SELECT deadline FROM section_clock")[0][0]
    clock.advance(100)
    browser.get("/test/visual/1")
    assert sql("SELECT deadline FROM section_clock")[0][0] == first
    assert 'data-seconds="500"' in browser.text("/test/visual/2")


def test_what_must_not_work(browser, new_browser):
    started(browser)
    assert browser.get("/test/visual%60--/1").status_code == 404, "section names are a whitelist"
    assert browser.get("/test/visual/99").status_code == 404
    assert browser.get("/results/1%20OR%201=1").status_code == 404

    other = new_browser()
    logged_in(other, nim="2002", name="Budi")
    assert other.get("/results/1").status_code == 404, "another student's result is not found"
    assert "/results/1" not in other.text("/results")
    other.get("/")
    assert location(other.post("/test/visual/1", {"choice": "2"})) == "302 /test", "no answers without a test"
    assert location(browser.post("/test/visual/1", {"choice": "2"})) == "302 /test/numerical", \
        "no answer before the section's first question was shown"
