-- CEAT database (SQLite). Created by `flask --app ceat init`.

PRAGMA foreign_keys = ON;

CREATE TABLE user (
    id_user INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    age INTEGER NOT NULL,
    nim TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL                    -- a password hash, never plain text
);

-- One row per test taken. Each result_* column holds one mark per answer slot:
-- K blank, B correct, S wrong.
CREATE TABLE result (
    id_result INTEGER PRIMARY KEY AUTOINCREMENT,
    id_user INTEGER NOT NULL REFERENCES user (id_user) ON DELETE CASCADE,
    waktu TEXT NOT NULL,                      -- HH:MM:SS the test started
    tanggal TEXT NOT NULL,                    -- DD/MM/YYYY
    result_visual TEXT NOT NULL,
    result_numerical TEXT NOT NULL,
    result_verbal TEXT NOT NULL,
    result_sequential TEXT NOT NULL,
    result_spatial TEXT NOT NULL,
    result_3d TEXT NOT NULL,
    result_system TEXT NOT NULL,
    result_vocabulary TEXT NOT NULL,
    result_figurework TEXT NOT NULL
);
CREATE INDEX result_user ON result (id_user);

-- When each section of a test ends: set at its first question, never moved.
CREATE TABLE section_clock (
    id_result INTEGER NOT NULL REFERENCES result (id_result) ON DELETE CASCADE,
    section TEXT NOT NULL,
    deadline INTEGER NOT NULL,                -- unix time
    PRIMARY KEY (id_result, section)
);

-- Logged-in sessions. The browser holds a random key; only its SHA-256 is stored.
CREATE TABLE login_session (
    key_hash TEXT PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES user (id_user) ON DELETE CASCADE,
    id_result INTEGER REFERENCES result (id_result) ON DELETE SET NULL,   -- the test in progress
    created_at INTEGER NOT NULL,
    seen_at INTEGER NOT NULL
);

-- Failed logins of the last day, to slow down password guessing.
CREATE TABLE login_attempt (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nim TEXT NOT NULL,
    ip TEXT NOT NULL,
    attempted_at INTEGER NOT NULL
);
CREATE INDEX login_attempt_nim ON login_attempt (nim, attempted_at);
CREATE INDEX login_attempt_ip ON login_attempt (ip, attempted_at);

CREATE TABLE info_section (
    name_section TEXT PRIMARY KEY,            -- visual, numerical, ... figurework
    type_questions TEXT NOT NULL,
    time INTEGER NOT NULL,                    -- minutes (the site keeps ceat/sections.py)
    instruction TEXT
);

-- The questions. question_text and choices_N may hold HTML; only the site's
-- administrators write them. answer: the correct choice number or text.
CREATE TABLE section_visual (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_numerical (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_sequential (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_spatial (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_3d (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_system (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_figurework (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL);
CREATE TABLE section_verbal (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL,
                             choices_1 TEXT, choices_2 TEXT, choices_3 TEXT, choices_4 TEXT, choices_5 TEXT);
CREATE TABLE section_vocabulary (question_number INTEGER PRIMARY KEY, question_text TEXT NOT NULL, answer TEXT NOT NULL,
                                 choices_1 TEXT, choices_2 TEXT, choices_3 TEXT, choices_4 TEXT, choices_5 TEXT);
