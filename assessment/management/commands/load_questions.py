"""python manage.py load_questions

Fills the section info and the questions from assessment/question_bank.py. Questions
already in the database are replaced, so changes made in the admin are lost: run it
on a new site, or to go back to the built-in questions."""

from pathlib import Path

from django.core.management.base import BaseCommand
from django.db import transaction

from assessment import question_bank
from assessment.models import Question, SectionInfo
from assessment.sections import SECTIONS

STATIC = Path(__file__).resolve().parents[2] / "static"


class Command(BaseCommand):
    help = "Load the built-in questions (assessment/question_bank.py), replacing the ones in the database."

    def handle(self, *args, **options):
        bank = question_bank.questions(STATIC)
        with transaction.atomic():
            for name, type_questions in question_bank.SECTION_INFO.items():
                SectionInfo.objects.update_or_create(section=name, defaults={"type_questions": type_questions})
            for name in SECTIONS:
                Question.objects.filter(section=name).delete()
                Question.objects.bulk_create([Question(section=name, number=number, **fields)
                                              for number, fields in enumerate(bank[name], 1)])
                self.stdout.write(f"{name}: {len(bank[name])} questions")
        self.stdout.write(f"{len(question_bank.REVIEW)} answers to review: see REVIEW in assessment/question_bank.py")
