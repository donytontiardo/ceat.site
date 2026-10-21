"""CEAT: an aptitude test whose results a Mamdani fuzzy model maps to computer careers."""

import os
from pathlib import Path

from flask import Flask, g, render_template
from werkzeug.middleware.proxy_fix import ProxyFix

from . import db
from .security import check_csrf, csrf_token, load_user, security_headers


def create_app(test_config: dict | None = None) -> Flask:
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_mapping(
        DATABASE=str(Path(app.instance_path) / "ceat.sqlite"),
        TIMEZONE="Asia/Jakarta",
        SECTION_SECONDS={},              # shorter time limits per section, e.g. for a trial run
        PROXIES=0,                       # reverse proxies in front (nginx: 1), for the client's address
        SESSION_COOKIE_NAME="ceat_session",
        SESSION_COOKIE_HTTPONLY=True,
        SESSION_COOKIE_SAMESITE="Lax",
        SESSION_COOKIE_SECURE=True,      # served over HTTPS; set False only for local testing
        MAX_CONTENT_LENGTH=64 * 1024,    # no form here comes close
    )
    if test_config is None:
        app.config.from_pyfile("config.py", silent=True)   # instance/config.py, written by `flask init`
        app.config.from_prefixed_env("CEAT")               # or CEAT_SECRET_KEY=... etc.
    else:
        app.config.update(test_config)

    from .cli import register_commands
    register_commands(app)
    if not app.config.get("SECRET_KEY"):
        # `flask --app ceat init` writes one; until then only the commands work
        @app.before_request
        def no_secret_key():
            raise RuntimeError("No SECRET_KEY: run `flask --app ceat init`, or set CEAT_SECRET_KEY")
        return app
    if app.config["PROXIES"]:
        n = int(app.config["PROXIES"])
        app.wsgi_app = ProxyFix(app.wsgi_app, x_for=n, x_proto=n)

    app.before_request(check_csrf)
    app.before_request(load_user)
    app.after_request(security_headers)
    app.teardown_appcontext(db.close_db)
    app.jinja_env.globals["csrf_token"] = csrf_token

    @app.context_processor
    def user():
        return {"user": g.get("user")}

    @app.errorhandler(404)
    def not_found(_error):
        return render_template("not_found.html"), 404

    @app.errorhandler(400)
    def bad_request(_error):
        return "Permintaan tidak valid. Muat ulang halaman dan coba lagi.", 400

    @app.errorhandler(500)
    def server_error(_error):
        return "Terjadi kesalahan. Silakan coba lagi.", 500

    from .views import bp
    app.register_blueprint(bp)
    os.makedirs(app.instance_path, exist_ok=True)
    return app
