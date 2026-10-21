"""Settings. Anything secret or specific to a server comes from CEAT_* environment
variables (see the README); nothing secret is in the repository."""

import os
from pathlib import Path

from django.utils.csp import CSP

BASE_DIR = Path(__file__).resolve().parent.parent


def env(name: str, default: str = "") -> str:
    return os.environ.get(f"CEAT_{name}", default)


def env_flag(name: str, default: bool) -> bool:
    return env(name, "1" if default else "0").lower() in ("1", "true", "yes")


SECRET_KEY = env("SECRET_KEY")              # required: Django refuses to start without one
DEBUG = env_flag("DEBUG", False)
ALLOWED_HOSTS = [h for h in env("ALLOWED_HOSTS", "localhost,127.0.0.1").split(",") if h]
HTTPS = env_flag("HTTPS", True)             # served over HTTPS (it should be)
PROXIES = int(env("PROXIES", "0"))          # reverse proxies in front (nginx: 1)

INSTALLED_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",
    "assessment",
]

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
    "whitenoise.middleware.WhiteNoiseMiddleware",
    "django.contrib.sessions.middleware.SessionMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
    "django.contrib.auth.middleware.AuthenticationMiddleware",
    "django.contrib.messages.middleware.MessageMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
    "django.middleware.csp.ContentSecurityPolicyMiddleware",
]

ROOT_URLCONF = "ceatsite.urls"
WSGI_APPLICATION = "ceatsite.wsgi.application"

TEMPLATES = [{
    "BACKEND": "django.template.backends.django.DjangoTemplates",
    "APP_DIRS": True,
    "OPTIONS": {"context_processors": [
        "django.template.context_processors.request",
        "django.contrib.auth.context_processors.auth",
        "django.contrib.messages.context_processors.messages",
    ]},
}]

DATABASES = {
    "default": {
        "ENGINE": "django.db.backends.sqlite3",
        "NAME": env("DATABASE", str(BASE_DIR / "db.sqlite3")),
        "OPTIONS": {"transaction_mode": "IMMEDIATE", "timeout": 10,
                    "init_command": "PRAGMA journal_mode=WAL;"},   # readers do not wait for the writer
    }
}
DEFAULT_AUTO_FIELD = "django.db.models.BigAutoField"

# ── accounts ───────────────────────────────────────────────────────────────
AUTH_USER_MODEL = "assessment.Student"
LOGIN_URL = "login"
PASSWORD_HASHERS = [
    "django.contrib.auth.hashers.PBKDF2PasswordHasher",
    "django.contrib.auth.hashers.ScryptPasswordHasher",
    # bcrypt hashes imported from the PHP site; replaced by PBKDF2 at each student's next login
    "django.contrib.auth.hashers.BCryptPasswordHasher",
]
AUTH_PASSWORD_VALIDATORS = [
    {"NAME": "django.contrib.auth.password_validation.MinimumLengthValidator", "OPTIONS": {"min_length": 8}},
    {"NAME": "django.contrib.auth.password_validation.CommonPasswordValidator"},
    {"NAME": "django.contrib.auth.password_validation.NumericPasswordValidator"},
]

# ── sessions and cookies ───────────────────────────────────────────────────
SESSION_COOKIE_NAME = "ceat_session"
SESSION_COOKIE_AGE = 2 * 3600               # 2 idle hours: the whole test takes about 70 minutes
SESSION_SAVE_EVERY_REQUEST = True
SESSION_EXPIRE_AT_BROWSER_CLOSE = True
SESSION_COOKIE_SECURE = CSRF_COOKIE_SECURE = HTTPS
CSRF_COOKIE_HTTPONLY = True

# ── security headers ───────────────────────────────────────────────────────
X_FRAME_OPTIONS = "DENY"
SECURE_REFERRER_POLICY = "same-origin"
SECURE_HSTS_SECONDS = 31536000 if HTTPS else 0
SECURE_SSL_REDIRECT = HTTPS                 # http:// requests are sent to https://
if PROXIES:
    SECURE_PROXY_SSL_HEADER = ("HTTP_X_FORWARDED_PROTO", "https")
# every script is a file of this site; inline scripts never run
SECURE_CSP = {
    "default-src": [CSP.SELF],
    "script-src": [CSP.SELF],
    "style-src": [CSP.SELF, "https://fonts.googleapis.com"],
    "font-src": ["https://fonts.gstatic.com"],
    "img-src": [CSP.SELF, "data:"],
    "form-action": [CSP.SELF],
    "frame-ancestors": [CSP.NONE],
    "base-uri": [CSP.NONE],
    "object-src": [CSP.NONE],
}

# ── language, time, static files ──────────────────────────────────────────
LANGUAGE_CODE = "id"
TIME_ZONE = env("TIMEZONE", "Asia/Jakarta")
USE_I18N = True
USE_TZ = True

STATIC_URL = "static/"
STATIC_ROOT = BASE_DIR / "staticfiles"
STORAGES = {
    "default": {"BACKEND": "django.core.files.storage.FileSystemStorage"},
    "staticfiles": {"BACKEND": "whitenoise.storage.CompressedManifestStaticFilesStorage"},
}

# shorter time limits per section for a trial run, e.g. CEAT_SECTION_SECONDS=visual:60,numerical:60
SECTION_SECONDS = {name: int(seconds) for name, seconds in
                   (pair.split(":") for pair in env("SECTION_SECONDS").split(",") if pair)}

LOGGING = {
    "version": 1,
    "handlers": {"console": {"class": "logging.StreamHandler"}},
    "loggers": {"django": {"handlers": ["console"], "level": "WARNING"}},
}
