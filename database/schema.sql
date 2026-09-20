-- SPSMIS Database Schema
-- Run this in phpMyAdmin or via: mysql -u root spsmis < schema.sql
-- Then run setup.php once to seed data.

CREATE DATABASE IF NOT EXISTS `spsmis`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `spsmis`;

-- ─── Users (admin, teachers, students login accounts) ───────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role`         ENUM('admin','teacher','student') NOT NULL,
  `username`     VARCHAR(50) NOT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `name`         VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL,
  `position`          VARCHAR(150) DEFAULT NULL,
  `advisory_grade`    VARCHAR(20)  DEFAULT NULL,
  `advisory_subject`  VARCHAR(50)  DEFAULT NULL,
  `teaching_subjects` TEXT         DEFAULT NULL,
  `lrn`          VARCHAR(20)  DEFAULT NULL,
  `grade_level`  VARCHAR(20)  DEFAULT NULL,
  `section`      VARCHAR(50)  DEFAULT NULL,
  `status`       ENUM('active','inactive') DEFAULT 'active',
  `sec_question` VARCHAR(200) DEFAULT NULL,
  `sec_answer`   VARCHAR(200) DEFAULT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Teacher Advisory Classes (Multiple advisory classes per teacher) ─────────
CREATE TABLE IF NOT EXISTS `teacher_classes` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `teacher_id`  INT UNSIGNED NOT NULL,
  `grade_level` VARCHAR(20) NOT NULL,
  `section`     VARCHAR(50) NOT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_teacher_grade_sec` (`teacher_id`, `grade_level`, `section`),
  UNIQUE KEY `uq_section_adviser` (`grade_level`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Students ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `students` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lrn`              VARCHAR(20) NOT NULL,
  `grade_level`      VARCHAR(20) NOT NULL,
  `section`          VARCHAR(50) NOT NULL,
  `first_name`       VARCHAR(80) NOT NULL,
  `middle_name`      VARCHAR(80) DEFAULT NULL,
  `last_name`        VARCHAR(80) NOT NULL,
  `sex`              ENUM('Male','Female') NOT NULL,
  `birthdate`        DATE NOT NULL,
  `age`              TINYINT UNSIGNED NOT NULL,
  `mother_tongue`    VARCHAR(80)  DEFAULT NULL,
  `religion`         VARCHAR(80)  DEFAULT NULL,
  `address`          TEXT         DEFAULT NULL,
  `mother_name`      VARCHAR(100) DEFAULT NULL,
  `father_name`      VARCHAR(100) DEFAULT NULL,
  `guardian_name`    VARCHAR(100) DEFAULT NULL,
  `guardian_relation` VARCHAR(50) DEFAULT NULL,
  `contact`          VARCHAR(20)  DEFAULT NULL,
  `email`            VARCHAR(150) DEFAULT NULL,
  `school_year`      VARCHAR(10)  DEFAULT '2025-2026',
  `status`           ENUM('active','inactive') DEFAULT 'active',
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_lrn` (`lrn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Grades ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `grades` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id`  INT UNSIGNED NOT NULL,
  `school_year` VARCHAR(10) NOT NULL DEFAULT '2025-2026',
  `grade_level` VARCHAR(20) NOT NULL,
  `section`     VARCHAR(50) NOT NULL,
  `subject`     VARCHAR(100) NOT NULL,
  `t1`          DECIMAL(5,2) DEFAULT NULL,
  `t2`          DECIMAL(5,2) DEFAULT NULL,
  `t3`          DECIMAL(5,2) DEFAULT NULL,
  `q1`          DECIMAL(5,2) DEFAULT NULL,
  `q2`          DECIMAL(5,2) DEFAULT NULL,
  `q3`          DECIMAL(5,2) DEFAULT NULL,
  `q4`          DECIMAL(5,2) DEFAULT NULL,
  `final_grade` DECIMAL(5,2) DEFAULT NULL,
  `remarks`     ENUM('Passed','Failed','') DEFAULT '',
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_grade` (`student_id`,`school_year`,`subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Announcements ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `announcements` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`     VARCHAR(200) NOT NULL,
  `body`      TEXT NOT NULL,
  `audience`  ENUM('all','student','teacher') DEFAULT 'all',
  `posted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Security Questions ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `security_questions` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question`   VARCHAR(255) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `security_questions` (`id`, `question`) VALUES
(1, 'What is the name of your first pet?'),
(2, 'What is your mother\'s maiden name?'),
(3, 'What city were you born in?'),
(4, 'What is the name of your elementary school?');

-- ─── Report Signatories ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `report_signatories` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prepared_by_type`    ENUM('teacher','custom') NOT NULL DEFAULT 'teacher',
  `prepared_by_user_id` INT UNSIGNED DEFAULT NULL,
  `prepared_by_name`    VARCHAR(150) DEFAULT NULL,
  `prepared_by_title`   VARCHAR(150) DEFAULT NULL,
  `noted_by_type`       ENUM('teacher','custom') NOT NULL DEFAULT 'custom',
  `noted_by_user_id`    INT UNSIGNED DEFAULT NULL,
  `noted_by_name`       VARCHAR(150) DEFAULT NULL,
  `noted_by_title`      VARCHAR(150) DEFAULT 'School Head / Principal',
  `updated_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Sections ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sections` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `grade_level`  VARCHAR(20) NOT NULL,
  `section_name` VARCHAR(100) NOT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_grade_section` (`grade_level`, `section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `sections` (`grade_level`, `section_name`) VALUES
('Kindergarten', 'Sampaguita'),
('Grade 1', 'Mabini'),
('Grade 2', 'Mabini'),
('Grade 3', 'Mabini'),
('Grade 4', 'Bonifacio'),
('Grade 5', 'Bonifacio'),
('Grade 6', 'Bonifacio'),
('Grade 7', 'Rizal'),
('Grade 8', 'Luna'),
('Grade 9', 'Luna'),
('Grade 10', 'Mabini'),
('Grade 11', 'STEM'),
('Grade 11', 'ABM'),
('Grade 11', 'HUMSS'),
('Grade 12', 'STEM'),
('Grade 12', 'ABM'),
('Grade 12', 'HUMSS');

-- ─── Subjects ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `subjects` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `grade_type` ENUM('kindergarten','elementary','jhs','shs') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_subject_level` (`name`, `grade_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `subjects` (`name`, `grade_type`) VALUES
('Literacy and Language', 'kindergarten'),
('Mathematics', 'kindergarten'),
('Socio-Emotional Development', 'kindergarten'),
('Values Education', 'kindergarten'),
('Physical Health and Motor Development', 'kindergarten'),
('Understanding the Physical and Natural Environment', 'kindergarten'),
('Filipino', 'elementary'),
('English', 'elementary'),
('Mathematics', 'elementary'),
('Science', 'elementary'),
('Araling Panlipunan', 'elementary'),
('Edukasyon sa Pagpapakatao', 'elementary'),
('MAPEH', 'elementary'),
('Mother Tongue', 'elementary'),
('Filipino', 'jhs'),
('English', 'jhs'),
('Mathematics', 'jhs'),
('Science', 'jhs'),
('Araling Panlipunan', 'jhs'),
('Edukasyon sa Pagpapakatao', 'jhs'),
('Technology and Livelihood Education', 'jhs'),
('MAPEH', 'jhs'),
('Oral Communication', 'shs'),
('Reading and Writing', 'shs'),
('Komunikasyon at Pananaliksik', 'shs'),
('21st Century Literature', 'shs'),
('Contemporary Philippine Arts', 'shs'),
('Media and Information Literacy', 'shs'),
('General Mathematics', 'shs'),
('Statistics and Probability', 'shs'),
('Earth and Life Science', 'shs'),
('Physical Science', 'shs'),
('Introduction to Philosophy', 'shs'),
('Physical Education and Health', 'shs');

-- ─── School Years ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `school_years` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `year_label`  VARCHAR(20) NOT NULL UNIQUE,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 0,
  `start_date`  DATE DEFAULT NULL,
  `end_date`    DATE DEFAULT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `school_years` (`year_label`, `is_active`, `start_date`, `end_date`) VALUES
('2024-2025', 0, '2024-06-03', '2025-03-28'),
('2025-2026', 1, '2025-06-02', '2026-03-27'),
('2026-2027', 0, '2026-06-01', '2027-03-26');
