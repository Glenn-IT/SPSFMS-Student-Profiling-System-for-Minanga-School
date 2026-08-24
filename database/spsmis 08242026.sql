-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2026 at 03:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spsmis`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `audience` enum('all','student','teacher') DEFAULT 'all',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `audience`, `posted_at`) VALUES
(1, 'Welcome to S.Y. 2025–2026!', 'Minanga Integrated School welcomes all students to the new school year. Classes begin on June 3, 2025.', 'all', '2025-05-31 23:00:00'),
(2, 'Card Giving — Q1', 'First quarter report cards will be distributed on October 10, 2025. Parents are required to attend.', 'student', '2025-09-25 00:00:00'),
(3, 'Teachers Meeting', 'Monthly faculty meeting scheduled on June 28, 2025 at 2:00 PM in the conference room.', 'teacher', '2025-06-20 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `school_year` varchar(10) NOT NULL DEFAULT '2025-2026',
  `grade_level` varchar(20) NOT NULL,
  `section` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `q1` decimal(5,2) DEFAULT NULL,
  `q2` decimal(5,2) DEFAULT NULL,
  `q3` decimal(5,2) DEFAULT NULL,
  `q4` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `remarks` enum('Passed','Failed','') DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `school_year`, `grade_level`, `section`, `subject`, `q1`, `q2`, `q3`, `q4`, `final_grade`, `remarks`, `created_at`, `updated_at`) VALUES
(57, 30, '2025-2026', 'Grade 1', 'Rizal', 'Filipino', 80.00, 80.00, 80.00, 80.00, 80.00, 'Passed', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(58, 30, '2025-2026', 'Grade 1', 'Rizal', 'English', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(59, 30, '2025-2026', 'Grade 1', 'Rizal', 'Mathematics', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(60, 30, '2025-2026', 'Grade 1', 'Rizal', 'Science', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(61, 30, '2025-2026', 'Grade 1', 'Rizal', 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(62, 30, '2025-2026', 'Grade 1', 'Rizal', 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(63, 30, '2025-2026', 'Grade 1', 'Rizal', 'MAPEH', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33'),
(64, 30, '2025-2026', 'Grade 1', 'Rizal', 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, '', '2026-08-17 16:55:33', '2026-08-17 16:55:33');

-- --------------------------------------------------------

--
-- Table structure for table `report_signatories`
--

CREATE TABLE `report_signatories` (
  `id` int(10) UNSIGNED NOT NULL,
  `prepared_by_type` enum('teacher','custom') NOT NULL DEFAULT 'teacher',
  `prepared_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `prepared_by_name` varchar(150) DEFAULT NULL,
  `prepared_by_title` varchar(150) DEFAULT NULL,
  `noted_by_type` enum('teacher','custom') NOT NULL DEFAULT 'custom',
  `noted_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `noted_by_name` varchar(150) DEFAULT NULL,
  `noted_by_title` varchar(150) DEFAULT 'School Head / Principal',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_signatories`
--

INSERT INTO `report_signatories` (`id`, `prepared_by_type`, `prepared_by_user_id`, `prepared_by_name`, `prepared_by_title`, `noted_by_type`, `noted_by_user_id`, `noted_by_name`, `noted_by_title`, `updated_at`) VALUES
(1, 'teacher', 16, 'Juan Uno', 'Grade 1 - Section Rizal', 'custom', 16, 'Dr. Stephen Strange', 'Sorcerer Supreme', '2026-08-18 02:49:58');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `grade_level`, `section_name`, `created_at`) VALUES
(18, 'Grade 1', 'Rizal', '2026-08-17 16:29:06'),
(20, 'Grade 1', 'Mabini', '2026-08-17 16:53:17'),
(21, 'Grade 1', 'Maria Clara', '2026-08-17 16:53:29');

-- --------------------------------------------------------

--
-- Table structure for table `security_questions`
--

CREATE TABLE `security_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_questions`
--

INSERT INTO `security_questions` (`id`, `question`, `created_at`) VALUES
(1, 'What is the name of your first pet?', '2026-08-10 15:59:47'),
(2, 'What is your mother\'s maiden name?', '2026-08-10 15:59:47'),
(3, 'What city were you born in?', '2026-08-10 15:59:47'),
(5, 'Who is ang iyong IDOl?', '2026-08-10 16:12:52');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `lrn` varchar(20) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `section` varchar(50) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `birthdate` date NOT NULL,
  `age` tinyint(3) UNSIGNED NOT NULL,
  `mother_tongue` varchar(80) DEFAULT NULL,
  `religion` varchar(80) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_relation` varchar(50) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `school_year` varchar(10) DEFAULT '2025-2026',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `lrn`, `grade_level`, `section`, `first_name`, `middle_name`, `last_name`, `sex`, `birthdate`, `age`, `mother_tongue`, `religion`, `address`, `mother_name`, `father_name`, `guardian_name`, `guardian_relation`, `contact`, `email`, `school_year`, `status`, `created_at`, `updated_at`) VALUES
(30, '102953060094', 'Grade 1', 'Rizal', 'Juan', 'Isa', 'Uno', 'Male', '2000-08-08', 26, 'Cebuano', 'Roman Catholic', 'sample', 'sample', 'sample', 'sample', 'Mother', '09557977409', 'sample@gmail.com', '2025-2026', 'active', '2026-08-17 16:54:46', '2026-08-17 16:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `grade_type` enum('elementary','jhs','shs') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `grade_type`, `created_at`) VALUES
(1, 'Filipino', 'elementary', '2026-08-17 16:08:47'),
(2, 'English', 'elementary', '2026-08-17 16:08:47'),
(3, 'Mathematics', 'elementary', '2026-08-17 16:08:47'),
(4, 'Science', 'elementary', '2026-08-17 16:08:47'),
(5, 'Araling Panlipunan', 'elementary', '2026-08-17 16:08:47'),
(6, 'Edukasyon sa Pagpapakatao', 'elementary', '2026-08-17 16:08:47'),
(7, 'MAPEH', 'elementary', '2026-08-17 16:08:47'),
(9, 'Filipino', 'jhs', '2026-08-17 16:08:47'),
(10, 'English', 'jhs', '2026-08-17 16:08:47'),
(11, 'Mathematics', 'jhs', '2026-08-17 16:08:47'),
(12, 'Science', 'jhs', '2026-08-17 16:08:47'),
(13, 'Araling Panlipunan', 'jhs', '2026-08-17 16:08:47'),
(14, 'Edukasyon sa Pagpapakatao', 'jhs', '2026-08-17 16:08:47'),
(15, 'Technology and Livelihood Education', 'jhs', '2026-08-17 16:08:47'),
(16, 'MAPEH', 'jhs', '2026-08-17 16:08:47'),
(17, 'Oral Communication', 'shs', '2026-08-17 16:08:47'),
(18, 'Reading and Writing', 'shs', '2026-08-17 16:08:47'),
(19, 'Komunikasyon at Pananaliksik', 'shs', '2026-08-17 16:08:47'),
(20, '21st Century Literature', 'shs', '2026-08-17 16:08:47'),
(21, 'Contemporary Philippine Arts', 'shs', '2026-08-17 16:08:47'),
(22, 'Media and Information Literacy', 'shs', '2026-08-17 16:08:47'),
(23, 'General Mathematics', 'shs', '2026-08-17 16:08:47'),
(24, 'Statistics and Probability', 'shs', '2026-08-17 16:08:47'),
(25, 'Earth and Life Science', 'shs', '2026-08-17 16:08:47'),
(26, 'Physical Science', 'shs', '2026-08-17 16:08:47'),
(27, 'Introduction to Philosophy', 'shs', '2026-08-17 16:08:47'),
(28, 'Physical Education and Health', 'shs', '2026-08-17 16:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `position` varchar(150) DEFAULT NULL,
  `teaching_subjects` text DEFAULT NULL,
  `advisory_grade` varchar(20) DEFAULT NULL,
  `advisory_subject` varchar(150) DEFAULT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `sec_question` varchar(200) DEFAULT NULL,
  `sec_answer` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `password`, `name`, `email`, `position`, `teaching_subjects`, `advisory_grade`, `advisory_subject`, `lrn`, `grade_level`, `section`, `status`, `sec_question`, `sec_answer`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', '$2y$10$Q.SdFBStLDDarKN81g7zSuxQKX1oqb4JjUzFrIRra6xxViWNaX4.u', 'Adminsss', 'admin@minanga.edu.ph', 'School Administrator', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'Who is ang iyong IDOl?', 'sample', '2026-06-19 18:37:58', '2026-08-10 16:12:59'),
(16, 'teacher', 'juanuno', '$2y$10$AmK3j5bZGBvY9Yzy.K/RFuDKHIMKs8ecb9chp4wlfBiQXQmoKcFUi', 'Juan Uno', 'juanuno@gmail.com', 'Grade 1 - Section Rizal', 'Mathematics, Science, MAPEH', 'Grade 1', 'Rizal', NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-17 16:53:51', '2026-08-24 13:27:38'),
(17, 'teacher', 'pedrouno', '$2y$10$li.udvmOBMNlgQnnDfcCleSicdkap8nEz4UR6ESCnevA1A9JtPCZq', 'Pedro Uno', 'pedro@gmail.com', 'Grade 1 - Section Maria Clara', NULL, 'Grade 1', 'Maria Clara', NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-18 15:49:26', '2026-08-18 15:49:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_grade` (`student_id`,`school_year`,`subject`);

--
-- Indexes for table `report_signatories`
--
ALTER TABLE `report_signatories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_section` (`grade_level`,`section_name`);

--
-- Indexes for table `security_questions`
--
ALTER TABLE `security_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `question` (`question`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lrn` (`lrn`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_subject` (`name`,`grade_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `report_signatories`
--
ALTER TABLE `report_signatories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `security_questions`
--
ALTER TABLE `security_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
