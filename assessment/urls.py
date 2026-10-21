from django.urls import path

from . import views

urlpatterns = [
    path("", views.index, name="index"),
    path("register", views.register, name="register"),
    path("login", views.login, name="login"),
    path("logout", views.logout, name="logout"),
    path("test", views.test, name="test"),
    path("test/start", views.start_test, name="start_test"),
    path("test/<str:name>", views.section, name="section"),
    path("test/<str:name>/<int:number>", views.question, name="question"),
    path("results", views.results, name="results"),
    path("results/<int:attempt_id>", views.result, name="result"),
]
