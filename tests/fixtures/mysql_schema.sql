-- CEAT database (MySQL / MariaDB). A new install: create the database, run this,
-- then fill info_section and the section_* tables with the questions.
-- An existing install from before the rewrite: run migrations/2026-10-21-hardening.sql instead.

CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    nim VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,          -- password_hash(); never plain text
    UNIQUE KEY user_nim (nim)
) DEFAULT CHARSET = utf8mb4;

-- One row per test taken. Each result_* column holds one mark per answer slot:
-- K blank, B correct, S wrong.
CREATE TABLE result (
    id_result INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    waktu VARCHAR(8) NOT NULL,                -- HH:MM:SS the test started
    tanggal VARCHAR(10) NOT NULL,             -- DD/MM/YYYY
    result_visual VARCHAR(32) NOT NULL,
    result_numerical VARCHAR(20) NOT NULL,
    result_verbal VARCHAR(33) NOT NULL,
    result_sequential VARCHAR(32) NOT NULL,
    result_spatial VARCHAR(70) NOT NULL,
    result_3d VARCHAR(30) NOT NULL,
    result_system VARCHAR(216) NOT NULL,
    result_vocabulary VARCHAR(40) NOT NULL,
    result_figurework VARCHAR(30) NOT NULL,
    KEY result_user (id_user),
    CONSTRAINT result_user FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE
) DEFAULT CHARSET = utf8mb4;

-- Failed logins of the last day, to slow down password guessing.
CREATE TABLE login_attempt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    KEY login_attempt_nim (nim, attempted_at),
    KEY login_attempt_ip (ip, attempted_at)
) DEFAULT CHARSET = utf8mb4;

CREATE TABLE info_section (
    name_section VARCHAR(20) PRIMARY KEY,     -- visual, numerical, ... figurework
    type_questions VARCHAR(100) NOT NULL,
    time INT NOT NULL,                        -- minutes (the site enforces src/sections.php)
    instruction TEXT
) DEFAULT CHARSET = utf8mb4;

-- The questions. question_text and choices_N may hold HTML; only the site's
-- administrators write them. answer: the correct choice number or text.
CREATE TABLE section_visual (question_number INT PRIMARY KEY, question_text TEXT NOT NULL,
                             answer VARCHAR(20) NOT NULL) DEFAULT CHARSET = utf8mb4;
CREATE TABLE section_numerical LIKE section_visual;
CREATE TABLE section_sequential LIKE section_visual;
CREATE TABLE section_spatial LIKE section_visual;
CREATE TABLE section_3d LIKE section_visual;
CREATE TABLE section_system LIKE section_visual;
CREATE TABLE section_figurework LIKE section_visual;
CREATE TABLE section_verbal (question_number INT PRIMARY KEY, question_text TEXT NOT NULL,
                             answer VARCHAR(20) NOT NULL, choices_1 TEXT, choices_2 TEXT,
                             choices_3 TEXT, choices_4 TEXT, choices_5 TEXT) DEFAULT CHARSET = utf8mb4;
CREATE TABLE section_vocabulary LIKE section_verbal;
