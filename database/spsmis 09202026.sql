-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2026 at 03:40 PM
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
  `t1` decimal(5,2) DEFAULT NULL,
  `t2` decimal(5,2) DEFAULT NULL,
  `t3` decimal(5,2) DEFAULT NULL,
  `q1` decimal(5,2) DEFAULT NULL,
  `q2` decimal(5,2) DEFAULT NULL,
  `q3` decimal(5,2) DEFAULT NULL,
  `q4` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `remarks` enum('Passed','Failed','') DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` int(10) UNSIGNED NOT NULL,
  `year_label` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `year_label`, `is_active`, `start_date`, `end_date`, `created_at`) VALUES
(1, '2024-2025', 0, '2024-06-03', '2025-03-28', '2026-09-11 13:03:14'),
(2, '2025-2026', 0, '2025-06-02', '2026-03-27', '2026-09-11 13:03:14'),
(3, '2026-2027', 1, '2026-06-01', '2027-03-26', '2026-09-11 13:03:14');

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
(22, 'Grade 1', 'Rizal', '2026-08-29 17:30:03'),
(23, 'Grade 7', 'Bonifacio', '2026-08-29 17:30:26'),
(25, 'Grade 11', 'STEM', '2026-08-29 17:46:19'),
(26, 'Kindergarten', 'Sampaguita', '2026-09-20 13:32:45');

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
(32, '109283746101', 'Grade 1', 'Rizal', 'Joshua', 'Reyes', 'Dela Cruz', 'Male', '2012-05-14', 13, 'Tagalog', 'Roman Catholic', 'Purok 1, Minanga, Naguilian, Isabela', 'Maria Dela Cruz', 'Antonio Dela Cruz', 'Maria Dela Cruz', 'Mother', '09171234501', 'joshua.delacruz@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(33, '109283746102', 'Grade 1', 'Rizal', 'Angela', 'Santos', 'Bautista', 'Female', '2012-08-22', 13, 'Ilocano', 'Roman Catholic', 'Purok 2, Minanga, Naguilian, Isabela', 'Elena Bautista', 'Rogelio Bautista', 'Elena Bautista', 'Mother', '09171234502', 'angela.bautista@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(34, '109283746103', 'Grade 7', 'Bonifacio', 'Christian', 'Garcia', 'Mendoza', 'Male', '2013-01-10', 12, 'Ilocano', 'Iglesia ni Cristo', 'Purok 3, Minanga, Naguilian, Isabela', 'Teresa Mendoza', 'Danilo Mendoza', 'Danilo Mendoza', 'Father', '09171234503', 'christian.mendoza@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:51:29'),
(35, '109283746104', 'Grade 1', 'Rizal', 'Princess Nicole', 'Alvarez', 'Aquino', 'Female', '2012-11-03', 13, 'Tagalog', 'Roman Catholic', 'Purok 1, Minanga, Naguilian, Isabela', 'Lourdes Aquino', 'Ferdinand Aquino', 'Lourdes Aquino', 'Mother', '09171234504', 'princess.aquino@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(36, '109283746105', 'Grade 11', 'STEM', 'Mark Joseph', 'Tolentino', 'Ramos', 'Male', '2013-03-19', 12, 'Ilocano', 'Roman Catholic', 'Purok 4, Minanga, Naguilian, Isabela', 'Gloria Ramos', 'Eduardo Ramos', 'Eduardo Ramos', 'Father', '09171234505', 'mark.ramos@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:52:02'),
(37, '109283746106', 'Grade 11', 'STEM', 'Bea Bianca', 'Pascual', 'Santos', 'Female', '2012-07-29', 13, 'Tagalog', 'Roman Catholic', 'Purok 2, Minanga, Naguilian, Isabela', 'Rowena Santos', 'Arthur Santos', 'Rowena Santos', 'Mother', '09171234506', 'bea.santos@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:52:08'),
(38, '109283746107', 'Grade 11', 'STEM', 'John Paul', 'Mercado', 'Villanueva', 'Male', '2013-09-15', 12, 'Ilocano', 'Roman Catholic', 'Purok 5, Minanga, Naguilian, Isabela', 'Carmencita Villanueva', 'Paulino Villanueva', 'Carmencita Villanueva', 'Mother', '09171234507', 'johnpaul.villanueva@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:52:23'),
(39, '109283746108', 'Grade 1', 'Rizal', 'Samantha Mae', 'Navarro', 'Castro', 'Female', '2012-12-01', 13, 'Ilocano', 'Born Again', 'Purok 4, Minanga, Naguilian, Isabela', 'Analyn Castro', 'Gilbert Castro', 'Analyn Castro', 'Mother', '09171234508', 'samantha.castro@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(40, '109283746109', 'Grade 1', 'Rizal', 'Gabriel', 'Soriano', 'Flores', 'Male', '2013-04-18', 12, 'Tagalog', 'Roman Catholic', 'Purok 3, Minanga, Naguilian, Isabela', 'Jocelyn Flores', 'Mario Flores', 'Mario Flores', 'Father', '09171234509', 'gabriel.flores@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(41, '109283746110', 'Grade 7', 'Bonifacio', 'Kyla Jane', 'Bernardo', 'Ignacio', 'Female', '2012-06-11', 13, 'Ilocano', 'Roman Catholic', 'Purok 1, Minanga, Naguilian, Isabela', 'Marites Ignacio', 'Nestor Ignacio', 'Marites Ignacio', 'Mother', '09171234510', 'kyla.ignacio@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:51:12'),
(42, '109283746111', 'Grade 7', 'Bonifacio', 'Daniel Angelo', 'Cortez', 'Morales', 'Male', '2013-02-25', 12, 'Tagalog', 'Roman Catholic', 'Purok 5, Minanga, Naguilian, Isabela', 'Gemma Morales', 'Ramon Morales', 'Ramon Morales', 'Father', '09171234511', 'daniel.morales@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:51:43'),
(43, '109283746112', 'Grade 7', 'Bonifacio', 'Chloe Abigail', 'Delos Santos', 'Manalo', 'Female', '2012-10-09', 13, 'Ilocano', 'Roman Catholic', 'Purok 2, Minanga, Naguilian, Isabela', 'Victoria Manalo', 'Christopher Manalo', 'Victoria Manalo', 'Mother', '09171234512', 'chloe.manalo@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:51:20'),
(44, '109283746113', 'Grade 11', 'STEM', 'Ethan James', 'Lim', 'Tan', 'Male', '2013-08-30', 12, 'Tagalog', 'Roman Catholic', 'Purok 3, Minanga, Naguilian, Isabela', 'Grace Tan', 'Henry Tan', 'Grace Tan', 'Mother', '09171234513', 'ethan.tan@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:52:16'),
(45, '109283746114', 'Grade 7', 'Bonifacio', 'Jasmine Faith', 'Rivera', 'Gomez', 'Female', '2012-04-05', 13, 'Ilocano', 'Iglesia ni Cristo', 'Purok 4, Minanga, Naguilian, Isabela', 'Rosalina Gomez', 'Edgardo Gomez', 'Rosalina Gomez', 'Mother', '09171234514', 'jasmine.gomez@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:51:01'),
(46, '109283746115', 'Grade 11', 'STEM', 'Justin Ryan', 'Salvador', 'Pineda', 'Male', '2013-11-20', 12, 'Tagalog', 'Roman Catholic', 'Purok 1, Minanga, Naguilian, Isabela', 'Corazon Pineda', 'Arnulfo Pineda', 'Corazon Pineda', 'Mother', '09171234515', 'justin.pineda@example.com', '2025-2026', 'active', '2026-08-29 17:26:21', '2026-08-29 17:51:53');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `grade_type` enum('kindergarten','elementary','jhs','shs') NOT NULL,
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
(28, 'Physical Education and Health', 'shs', '2026-08-17 16:08:47'),
(29, 'TLE', 'jhs', '2026-09-11 12:55:26'),
(30, 'TECHNO', 'jhs', '2026-09-20 12:20:04'),
(31, 'Literacy and Language', 'kindergarten', '2026-09-20 13:32:45'),
(32, 'Mathematics', 'kindergarten', '2026-09-20 13:32:45'),
(33, 'Socio-Emotional Development', 'kindergarten', '2026-09-20 13:32:45'),
(34, 'Values Education', 'kindergarten', '2026-09-20 13:32:45'),
(35, 'Physical Health and Motor Development', 'kindergarten', '2026-09-20 13:32:45'),
(36, 'Understanding the Physical and Natural Environment', 'kindergarten', '2026-09-20 13:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_classes`
--

CREATE TABLE `teacher_classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `section` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_classes`
--

INSERT INTO `teacher_classes` (`id`, `teacher_id`, `grade_level`, `section`, `created_at`) VALUES
(754, 34, 'Kindergarten', 'Sampaguita', '2026-09-20 13:38:27'),
(755, 34, 'Grade 7', 'Bonifacio', '2026-09-20 13:38:27'),
(756, 34, 'Grade 11', 'STEM', '2026-09-20 13:38:27');

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
(19, 'student', 'student1', '$2y$10$/xtDhWzb6voDxB4ix3IIleAPmNwvWKYhETe3poz.iCj3fkZmepx.6', 'Joshua Reyes Dela Cruz', 'joshua.delacruz@example.com', NULL, NULL, NULL, NULL, '109283746101', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-09-11 13:27:58'),
(20, 'student', 'student2', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Angela Santos Bautista', 'angela.bautista@example.com', NULL, NULL, NULL, NULL, '109283746102', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(21, 'student', 'student3', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Christian Garcia Mendoza', 'christian.mendoza@example.com', NULL, NULL, NULL, NULL, '109283746103', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(22, 'student', 'student4', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Princess Nicole Alvarez Aquino', 'princess.aquino@example.com', NULL, NULL, NULL, NULL, '109283746104', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(23, 'student', 'student5', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Mark Joseph Tolentino Ramos', 'mark.ramos@example.com', NULL, NULL, NULL, NULL, '109283746105', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(24, 'student', 'student6', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Bea Bianca Pascual Santos', 'bea.santos@example.com', NULL, NULL, NULL, NULL, '109283746106', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(25, 'student', 'student7', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'John Paul Mercado Villanueva', 'johnpaul.villanueva@example.com', NULL, NULL, NULL, NULL, '109283746107', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(26, 'student', 'student8', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Samantha Mae Navarro Castro', 'samantha.castro@example.com', NULL, NULL, NULL, NULL, '109283746108', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(27, 'student', 'student9', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Gabriel Soriano Flores', 'gabriel.flores@example.com', NULL, NULL, NULL, NULL, '109283746109', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(28, 'student', 'student10', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Kyla Jane Bernardo Ignacio', 'kyla.ignacio@example.com', NULL, NULL, NULL, NULL, '109283746110', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(29, 'student', 'student11', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Daniel Angelo Cortez Morales', 'daniel.morales@example.com', NULL, NULL, NULL, NULL, '109283746111', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(30, 'student', 'student12', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Chloe Abigail Delos Santos Manalo', 'chloe.manalo@example.com', NULL, NULL, NULL, NULL, '109283746112', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(31, 'student', 'student13', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Ethan James Lim Tan', 'ethan.tan@example.com', NULL, NULL, NULL, NULL, '109283746113', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(32, 'student', 'student14', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Jasmine Faith Rivera Gomez', 'jasmine.gomez@example.com', NULL, NULL, NULL, NULL, '109283746114', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(33, 'student', 'student15', '$2y$10$LqaXJCOdSBCjApNpKxbsouXI/N96CBKnIgxx56w7CkU9XrCakIgiW', 'Justin Ryan Salvador Pineda', 'justin.pineda@example.com', NULL, NULL, NULL, NULL, '109283746115', 'Grade 1', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-08-29 17:26:21', '2026-08-29 17:26:21'),
(34, 'teacher', 'juan', '$2y$10$a/lIkJdiCZh9167Jy9BAr.nXs/ngJdfbIJDAedMb38sysMicDzHb6', 'Juan Dela Cruz', 'juan@gmail.com', 'Kindergarten - Section Sampaguita, Grade 7 - Section Bonifacio, Grade 11 - Section STEM', 'Filipino', 'Kindergarten', 'Sampaguita', NULL, NULL, NULL, 'active', NULL, NULL, '2026-08-29 17:32:16', '2026-09-20 13:38:27');

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
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year_label` (`year_label`);

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
-- Indexes for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teacher_grade_sec` (`teacher_id`,`grade_level`,`section`),
  ADD UNIQUE KEY `uq_section_adviser` (`grade_level`,`section`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `report_signatories`
--
ALTER TABLE `report_signatories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `security_questions`
--
ALTER TABLE `security_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=771;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD CONSTRAINT `teacher_classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
