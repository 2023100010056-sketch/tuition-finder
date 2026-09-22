-- ============================================================
-- Tuition Finder - Schema for InfinityFree (import version)
-- ============================================================
-- This file does NOT create the database, because on InfinityFree
-- the database must be created from the control panel first
-- (MySQL Databases -> New Database).
--
-- How to use:
--   1. Control panel -> MySQL Databases -> create a database
--   2. Click phpMyAdmin next to that database
--   3. Select the database on the left, open the Import tab
--   4. Choose this file and press Import
--
-- Demo accounts (all use the password  Pass@123 ):
--   guardian@demo.com | tutor@demo.com | admin@demo.com
-- ============================================================

-- ------------------------------------------------------------
-- Table: users
-- Every person who can log in. A user may be a guardian/student
-- who posts tuitions, or a tutor who browses them.
-- ------------------------------------------------------------
DROP TABLE IF EXISTS tuition_posts;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)        NOT NULL,
    email         VARCHAR(150)        NOT NULL UNIQUE,
    phone         VARCHAR(20)         NOT NULL,
    role          ENUM('guardian','tutor','admin') NOT NULL DEFAULT 'guardian',
    password_hash VARCHAR(255)        NOT NULL,
    created_at    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: tuition_posts
-- The main entity of the application (the CRUD entity).
-- posted_by references users(user_id): a post belongs to exactly
-- one user, and only that user may edit or delete it.
-- ------------------------------------------------------------
CREATE TABLE tuition_posts (
    post_id      INT AUTO_INCREMENT PRIMARY KEY,
    posted_by    INT            NOT NULL,
    title        VARCHAR(150)   NOT NULL,
    class_level  VARCHAR(50)    NOT NULL,
    subject      VARCHAR(100)   NOT NULL,
    area         VARCHAR(100)   NOT NULL,
    salary       INT            NOT NULL,
    days_per_week TINYINT       NOT NULL,
    medium       ENUM('Bangla','English','Both') NOT NULL DEFAULT 'Bangla',
    description  TEXT           NULL,
    status       ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_post_user
        FOREIGN KEY (posted_by) REFERENCES users(user_id)
        ON DELETE CASCADE,
    INDEX idx_subject (subject),
    INDEX idx_area (area),
    INDEX idx_class (class_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Sample data
-- All three demo accounts use the password:  Pass@123
-- (the hash below was produced with PHP password_hash())
--
-- NOTE: the admin account can only be created here, from SQL.
-- The public registration form never lets anybody choose the
-- 'admin' role, so an ordinary visitor cannot become an admin.
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, phone, role, password_hash) VALUES
('Demo Guardian', 'guardian@demo.com', '01700000001', 'guardian',
 '$2y$12$jUK5BUXQUlUdQW6/R3YV9OeYqhrkcyF6qZNY80SG4xSFk409RlRMO'),
('Demo Tutor', 'tutor@demo.com', '01700000002', 'tutor',
 '$2y$12$jUK5BUXQUlUdQW6/R3YV9OeYqhrkcyF6qZNY80SG4xSFk409RlRMO'),
('Site Admin', 'admin@demo.com', '01700000003', 'admin',
 '$2y$12$jUK5BUXQUlUdQW6/R3YV9OeYqhrkcyF6qZNY80SG4xSFk409RlRMO');

INSERT INTO tuition_posts
 (posted_by, title, class_level, subject, area, salary, days_per_week, medium, description) VALUES
(1, 'Math tutor needed for Class 9', 'Class 9', 'Mathematics', 'Mirpur', 5000, 3, 'Bangla',
 'Looking for an experienced tutor for general mathematics. Evening time preferred.'),
(1, 'English tutor for Class 6 student', 'Class 6', 'English', 'Dhanmondi', 4000, 3, 'English',
 'Student needs help with grammar and writing skills.'),
(1, 'Physics & Chemistry - HSC 1st year', 'HSC', 'Physics', 'Uttara', 8000, 4, 'Bangla',
 'Both physics and chemistry required. University student preferred.');
