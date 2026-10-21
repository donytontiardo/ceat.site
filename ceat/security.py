"""Security headers, CSRF, and logged-in sessions kept in the database.

The browser's cookie (signed by Flask) holds only a CSRF token, flash messages
and a random login key; the login itself is a row in login_session, so logging
out ends it everywhere and an old copy of the cookie is worth nothing."""

import hashlib
import hmac
import secrets
from functools import wraps

from flask import abort, current_app, g, redirect, request, session, url_for

from . import db

SESSION_IDLE_SECONDS = 2 * 3600      # the whole test takes about 70 minutes


def security_headers(response):
    # every script is a file of this site; inline scripts and styles never run
    response.headers["Content-Security-Policy"] = (
        "default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; "
        "font-src https://fonts.gstatic.com; img-src 'self'; form-action 'self'; frame-ancestors 'none'; "
        "base-uri 'none'; object-src 'none'")
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["Referrer-Policy"] = "same-origin"
    response.headers["Cross-Origin-Opener-Policy"] = "same-origin"
    if current_app.config["SESSION_COOKIE_SECURE"]:
        response.headers["Strict-Transport-Security"] = "max-age=31536000"
    return response


# ── CSRF ───────────────────────────────────────────────────────────────────
def csrf_token() -> str:
    if "csrf" not in session:
        session["csrf"] = secrets.token_hex(32)
    return session["csrf"]


def check_csrf():
    """Every POST must carry this session's token."""
    if request.method == "POST":
        token = request.form.get("csrf", "")
        if not hmac.compare_digest(token.encode(), session.get("csrf", "").encode()) or not token:
            abort(400)


# ── login sessions ─────────────────────────────────────────────────────────
def _key_hash(key: str) -> str:
    return hashlib.sha256(key.encode()).hexdigest()


def load_user():
    """g.user and g.session_key for the request, from the cookie's login key."""
    g.user = None
    key = session.get("login")
    if key:
        g.user = db.find_session(_key_hash(key), SESSION_IDLE_SECONDS)
        if g.user is None:
            session.pop("login")


def log_in(user_id: int):
    session.clear()                  # a new cookie, new CSRF token, nothing from before the login
    key = secrets.token_urlsafe(32)
    db.create_session(_key_hash(key), user_id)
    session["login"] = key


def log_out():
    key = session.get("login")
    if key:
        db.delete_session(_key_hash(key))
    session.clear()


def current_session_hash() -> str:
    return _key_hash(session["login"])


def login_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if g.user is None:
            return redirect(url_for("main.login"))
        return view(*args, **kwargs)
    return wrapped
