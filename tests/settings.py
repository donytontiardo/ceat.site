"""Settings for pytest: the real ones, over plain HTTP, with a 2-second system section."""

from ceatsite.settings import *  # noqa: F401,F403

SECRET_KEY = "test"
DEBUG = False
SESSION_COOKIE_SECURE = CSRF_COOKIE_SECURE = False
SECURE_HSTS_SECONDS = 0
SECURE_SSL_REDIRECT = False
SECTION_SECONDS = {"system": 2}
DATABASES["default"]["NAME"] = ":memory:"  # noqa: F405
DATABASES["default"]["OPTIONS"] = {}       # noqa: F405
STORAGES["staticfiles"] = {"BACKEND": "django.contrib.staticfiles.storage.StaticFilesStorage"}  # noqa: F405
MIDDLEWARE = [m for m in MIDDLEWARE if "whitenoise" not in m]  # noqa: F405  (no collected static files here)
