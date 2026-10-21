from datetime import timedelta

from django.conf import settings
from django.contrib.auth.models import AbstractBaseUser, BaseUserManager, PermissionsMixin
from django.core.validators import MaxValueValidator, MinValueValidator
from django.db import models
from django.utils import timezone

from .sections import MAX_ANSWER_LENGTH, SECTIONS

SECTION_CHOICES = [(name, section.label) for name, section in SECTIONS.items()]
MIN_AGE, MAX_AGE = 10, 99


class StudentManager(BaseUserManager):
    def create_user(self, nim, name, age, password=None, **extra):
        student = self.model(nim=nim, name=name, age=age, **extra)
        student.set_password(password)
        student.save(using=self._db)
        return student

    def create_superuser(self, nim, name, age, password=None, **extra):
        return self.create_user(nim, name, age, password, is_staff=True, is_superuser=True, **extra)


class Student(AbstractBaseUser, PermissionsMixin):
    """A student, logging in with their NIM. Staff accounts also open the admin."""
    nim = models.CharField("NIM", max_length=20, unique=True)
    name = models.CharField("nama", max_length=100)
    age = models.PositiveSmallIntegerField("usia", validators=[MinValueValidator(MIN_AGE), MaxValueValidator(MAX_AGE)])
    is_active = models.BooleanField(default=True)
    is_staff = models.BooleanField(default=False)

    objects = StudentManager()
    USERNAME_FIELD = "nim"
    REQUIRED_FIELDS = ["name", "age"]

    class Meta:
        verbose_name = "student"

    def __str__(self):
        return f"{self.nim} {self.name}"


class SectionInfo(models.Model):
    section = models.CharField(max_length=20, choices=SECTION_CHOICES, primary_key=True)
    type_questions = models.CharField("jenis pertanyaan", max_length=100)

    class Meta:
        verbose_name = "section info"

    def __str__(self):
        return SECTIONS[self.section].label


class Question(models.Model):
    """One question of a section. text and the choices may hold HTML: they are
    shown as written, so only staff edit them (in the admin)."""
    section = models.CharField(max_length=20, choices=SECTION_CHOICES)
    number = models.PositiveIntegerField()
    text = models.TextField()
    answer = models.CharField(max_length=MAX_ANSWER_LENGTH * 12,
                              help_text="The correct choice number, or the text to type. Spatial and system: "
                                        "one character per field, e.g. NNYYN.")
    choice_1 = models.TextField(blank=True)
    choice_2 = models.TextField(blank=True)
    choice_3 = models.TextField(blank=True)
    choice_4 = models.TextField(blank=True)
    choice_5 = models.TextField(blank=True)

    class Meta:
        constraints = [models.UniqueConstraint(fields=["section", "number"], name="question_number")]
        ordering = ["section", "number"]

    def __str__(self):
        return f"{SECTIONS[self.section].label} {self.number}"

    @property
    def choices(self) -> list[str]:
        return [getattr(self, f"choice_{i}") for i in range(1, SECTIONS[self.section].choices + 1)]


class Attempt(models.Model):
    """One test taken by a student."""
    student = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE, related_name="attempts")
    started_at = models.DateTimeField(default=timezone.now)

    class Meta:
        ordering = ["started_at", "id"]

    def __str__(self):
        return f"{self.student} {timezone.localtime(self.started_at):%d/%m/%Y %H:%M}"

    def marks(self) -> dict[str, str]:
        """result_<section> -> its marks (K blank, B correct, S wrong), blank for sections not started."""
        runs = {run.section: run.marks for run in self.runs.all()}
        return {f"result_{name}": runs.get(name, section.blank_result) for name, section in SECTIONS.items()}


class SectionRun(models.Model):
    """A section of an attempt: when it ends (set at its first question, never moved)
    and the marks so far, one per answer slot."""
    attempt = models.ForeignKey(Attempt, on_delete=models.CASCADE, related_name="runs")
    section = models.CharField(max_length=20, choices=SECTION_CHOICES)
    deadline = models.DateTimeField()
    marks = models.CharField(max_length=max(s.length for s in SECTIONS.values()))

    class Meta:
        constraints = [models.UniqueConstraint(fields=["attempt", "section"], name="one_run_per_section")]

    def seconds_left(self, now) -> int:
        return max(0, int((self.deadline - now).total_seconds()))

    def accepts_answers(self, now, grace=timedelta(seconds=3)) -> bool:
        # an answer sent as the timer runs out still counts
        return now <= self.deadline + grace


class LoginFailure(models.Model):
    """A failed login of the last day, to slow down password guessing."""
    nim = models.CharField(max_length=20, db_index=True)
    ip = models.GenericIPAddressField(db_index=True)
    at = models.DateTimeField(default=timezone.now, db_index=True)
