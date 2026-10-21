"""Registration rules, login throttling and the client's address."""

import re
from datetime import timedelta

from django.conf import settings
from django.contrib.auth.password_validation import validate_password
from django.core.exceptions import ValidationError
from django.db.models import Q
from django.utils import timezone

from .models import MAX_AGE, MIN_AGE, LoginFailure, Student

MAX_NAME_LENGTH = 100
MAX_PASSWORD_LENGTH = 128
LOGIN_MAX_FAILURES = 5
LOGIN_WINDOW = timedelta(minutes=15)
_NIM = re.compile(r"[0-9]{1,20}")


def validate_registration(form) -> tuple[list[str], dict]:
    """(errors, clean values) for a registration form; the values are only
    meaningful when there are no errors."""
    name = form.get("name", "").strip()
    nim = form.get("nim", "").strip()
    password = form.get("password", "")
    try:
        age = int(form.get("age", ""))
    except ValueError:
        age = None

    errors = []
    if not name or len(name) > MAX_NAME_LENGTH:
        errors.append(f"Nama harus diisi, paling panjang {MAX_NAME_LENGTH} karakter.")
    if not _NIM.fullmatch(nim):
        errors.append("NIM hanya berisi angka, paling panjang 20 digit.")
    if age is None or not MIN_AGE <= age <= MAX_AGE:
        errors.append(f"Usia harus angka antara {MIN_AGE} dan {MAX_AGE}.")
    if len(password) > MAX_PASSWORD_LENGTH:
        errors.append(f"Kata sandi paling panjang {MAX_PASSWORD_LENGTH} karakter.")
    else:
        try:
            validate_password(password, Student(nim=nim, name=name))
        except ValidationError as e:
            errors.extend(e.messages)
    return errors, {"name": name, "nim": nim, "age": age, "password": password}


def client_ip(request) -> str:
    """The student's address: REMOTE_ADDR, or behind CEAT_PROXIES reverse proxies the
    address the outermost of them saw (proxies append to X-Forwarded-For)."""
    if settings.PROXIES:
        forwarded = [a.strip() for a in request.META.get("HTTP_X_FORWARDED_FOR", "").split(",") if a.strip()]
        if len(forwarded) >= settings.PROXIES:
            return forwarded[-settings.PROXIES]
    return request.META.get("REMOTE_ADDR", "")


def login_blocked(nim: str, ip: str) -> bool:
    recent = LoginFailure.objects.filter(Q(nim=nim) | Q(ip=ip), at__gt=timezone.now() - LOGIN_WINDOW)
    return recent.count() >= LOGIN_MAX_FAILURES


def record_login_failure(nim: str, ip: str):
    LoginFailure.objects.create(nim=nim[:20], ip=ip or "0.0.0.0")
    LoginFailure.objects.filter(at__lt=timezone.now() - timedelta(days=1)).delete()


def clear_login_failures(nim: str):
    LoginFailure.objects.filter(nim=nim).delete()
