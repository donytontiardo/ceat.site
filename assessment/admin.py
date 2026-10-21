"""The admin (/admin/), for staff: questions, section info, students and their tests."""

from django.contrib import admin
from django.contrib.auth.admin import UserAdmin
from django.contrib.auth.forms import AdminUserCreationForm, UserChangeForm

from .fuzzy import career_fits
from .models import Attempt, Question, SectionInfo, SectionRun, Student
from .scoring import grade_result


@admin.register(Question)
class QuestionAdmin(admin.ModelAdmin):
    list_display = ["section", "number", "answer", "short_text"]
    list_filter = ["section"]
    search_fields = ["text", "answer"]
    ordering = ["section", "number"]
    fieldsets = [
        (None, {"fields": ["section", "number", "text", "answer"]}),
        ("Choices (verbal and vocabulary)", {"fields": [f"choice_{i}" for i in range(1, 6)], "classes": ["collapse"]}),
    ]

    @admin.display(description="text")
    def short_text(self, question):
        return question.text[:80]


@admin.register(SectionInfo)
class SectionInfoAdmin(admin.ModelAdmin):
    list_display = ["section", "type_questions"]


class StudentCreationForm(AdminUserCreationForm):
    class Meta:
        model = Student
        fields = ["nim", "name", "age"]


class StudentChangeForm(UserChangeForm):
    class Meta:
        model = Student
        fields = "__all__"


@admin.register(Student)
class StudentAdmin(UserAdmin):
    add_form = StudentCreationForm
    form = StudentChangeForm
    list_display = ["nim", "name", "age", "is_staff", "last_login"]
    list_filter = ["is_staff", "is_active"]
    search_fields = ["nim", "name"]
    ordering = ["nim"]
    fieldsets = [
        (None, {"fields": ["nim", "password"]}),
        ("Student", {"fields": ["name", "age"]}),
        ("Access", {"fields": ["is_active", "is_staff", "is_superuser", "groups", "user_permissions"]}),
        ("Dates", {"fields": ["last_login"]}),
    ]
    add_fieldsets = [(None, {"fields": ["nim", "name", "age", "password1", "password2"]})]
    readonly_fields = ["last_login"]


class SectionRunInline(admin.TabularInline):
    model = SectionRun
    extra = 0
    can_delete = False
    readonly_fields = ["section", "deadline", "marks"]

    def has_add_permission(self, request, obj=None):
        return False


@admin.register(Attempt)
class AttemptAdmin(admin.ModelAdmin):
    list_display = ["student", "started_at", "best_fit"]
    list_filter = ["started_at"]
    search_fields = ["student__nim", "student__name"]
    readonly_fields = ["student", "started_at", "best_fit"]
    inlines = [SectionRunInline]

    @admin.display(description="best fit")
    def best_fit(self, attempt):
        grades = grade_result(attempt.marks(), attempt.student.age)
        careers = career_fits([grade["potensi"] for grade in grades.values()])
        if not careers.conclusive:
            return "-"
        return f"{careers.best.name} ({careers.best.score:.2f})"

    def has_add_permission(self, request):
        return False
