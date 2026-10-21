-- Brings a database from before the rewrite up to schema.sql. Back it up first.
-- Existing passwords stay plain text until each student logs in once; the site
-- then replaces them with a hash. Tell students to log in, or reset the others.

-- passwords: room for a hash, and one account per NIM
ALTER TABLE user MODIFY password VARCHAR(255) NOT NULL;
-- fails if a NIM was registered twice: keep one of those accounts, then run it again
ALTER TABLE user ADD UNIQUE KEY user_nim (nim);

-- results: an id per test, instead of finding a test by its time and date
ALTER TABLE result ADD COLUMN id_result INT AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE result ADD KEY result_user (id_user);

CREATE TABLE login_attempt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    KEY login_attempt_nim (nim, attempted_at),
    KEY login_attempt_ip (ip, attempted_at)
) DEFAULT CHARSET = utf8mb4;

-- add.php wrote these; nothing reads them. Drop them if they exist and are not needed:
-- DROP TABLE questions_tak, choices_tak;
