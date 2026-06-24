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
  `position`     VARCHAR(150) DEFAULT NULL,
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
