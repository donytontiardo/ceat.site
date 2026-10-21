"""python manage.py import_mysql --user ceat --database ceat

Copies students, tests and questions from the MySQL database of the PHP site (the
original one or its hardened rewrite) into this site's empty database."""

from datetime import datetime
from getpass import getpass
from zoneinfo import ZoneInfo

from django.conf import settings
from django.contrib.auth.hashers import make_password
from django.core.management.base import BaseCommand, CommandError
from django.db import transaction

from assessment.models import Attempt, Question, SectionInfo, SectionRun, Student
from assessment.sections import SECTIONS


def django_password(stored: str | None) -> str:
    """The stored password as a Django hash. Plain text (the original site) is hashed
    now; bcrypt (the PHP rewrite) cannot be, so it is kept and Django replaces it with
    its own hash at the student's next login."""
    if not stored:
        return make_password(None)                       # unusable: the student needs a reset
    if stored.startswith(("$2y$", "$2b$", "$2a$")):
        return "bcrypt$$2b$" + stored[4:]
    return make_password(stored)


def started_at(row) -> datetime:
    """The test's start from its tanggal (DD/MM/YYYY) and waktu (HH:MM:SS) columns."""
    try:
        moment = datetime.strptime(f"{row['tanggal']} {row['waktu']}", "%d/%m/%Y %H:%M:%S")
    except (TypeError, ValueError):
        moment = datetime(2000, 1, 1)
    return moment.replace(tzinfo=ZoneInfo(settings.TIME_ZONE))


class Command(BaseCommand):
    help = "Copy students, tests and questions from the PHP site's MySQL database."

    def add_arguments(self, parser):
        parser.add_argument("--host", default="localhost")
        parser.add_argument("--port", type=int, default=3306)
        parser.add_argument("--socket", help="a Unix socket instead of host and port")
        parser.add_argument("--user", required=True)
        parser.add_argument("--password", help="asked for when not given")
        parser.add_argument("--database", required=True)

    def handle(self, *args, **options):
        try:
            import pymysql
        except ImportError:
            raise CommandError("needs PyMySQL: pip install pymysql")
        password = options["password"] if options["password"] is not None else getpass("MySQL password: ")
        source = pymysql.connect(host=options["host"], port=options["port"], unix_socket=options["socket"],
                                 user=options["user"], password=password, database=options["database"],
                                 cursorclass=pymysql.cursors.DictCursor)
        if Student.objects.exists():
            raise CommandError("this database already has students; import into a new one")
        with transaction.atomic():
            for line in import_database(source):
                self.stdout.write(line)


def rows(source, table: str) -> list[dict]:
    with source.cursor() as cursor:
        cursor.execute(f"SELECT * FROM `{table}`")
        return list(cursor.fetchall())


def import_database(source):
    students, duplicates = {}, 0
    for row in rows(source, "user"):
        nim = str(row["nim"]).strip()
        if any(s.nim == nim for s in students.values()):
            duplicates += 1               # the original site allowed a NIM twice; the first account stays
            continue
        students[row["id_user"]] = Student.objects.create(
            nim=nim, name=row["name"], age=int(row["age"]), password=django_password(row["password"]))
    yield f"students: {len(students)} imported, {duplicates} with a duplicate NIM left out"

    imported = skipped = 0
    for row in rows(source, "result"):
        student = students.get(row["id_user"])
        if student is None:
            skipped += 1
            continue
        start = started_at(row)
        attempt = Attempt.objects.create(student=student, started_at=start)
        # the old tests are finished: every section's clock has run out
        SectionRun.objects.bulk_create([
            SectionRun(attempt=attempt, section=name, deadline=start,
                       marks=(row[f"result_{name}"] or section.blank_result)[:section.length])
            for name, section in SECTIONS.items()])
        imported += 1
    yield f"tests: {imported} imported, {skipped} of left-out students skipped"

    info = rows(source, "info_section")
    SectionInfo.objects.bulk_create([SectionInfo(section=row["name_section"], type_questions=row["type_questions"])
                                     for row in info if row["name_section"] in SECTIONS])
    yield f"section info: {len(info)} imported"

    for name, section in SECTIONS.items():
        questions = rows(source, f"section_{name}")
        Question.objects.bulk_create([Question(
            section=name, number=row["question_number"], text=row["question_text"] or "", answer=str(row["answer"]),
            **{f"choice_{i}": row.get(f"choices_{i}") or "" for i in range(1, 6)}) for row in questions])
        yield f"{name}: {len(questions)} questions imported"
