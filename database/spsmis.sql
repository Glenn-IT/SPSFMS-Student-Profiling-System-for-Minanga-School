-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 05:03 PM
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
(1, 9, '2025-2026', 'Grade 7', 'Rizal', 'Filipino', 88.00, 87.00, 89.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(2, 9, '2025-2026', 'Grade 7', 'Rizal', 'English', 85.00, 86.00, 84.00, 87.00, 86.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(3, 9, '2025-2026', 'Grade 7', 'Rizal', 'Mathematics', 90.00, 92.00, 91.00, 93.00, 92.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(4, 9, '2025-2026', 'Grade 7', 'Rizal', 'Science', 87.00, 88.00, 86.00, 89.00, 88.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(5, 9, '2025-2026', 'Grade 7', 'Rizal', 'Araling Panlipunan', 83.00, 84.00, 85.00, 86.00, 85.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(6, 9, '2025-2026', 'Grade 7', 'Rizal', 'Edukasyon sa Pagpapakatao', 90.00, 91.00, 92.00, 91.00, 91.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(7, 9, '2025-2026', 'Grade 7', 'Rizal', 'Technology and Livelihood Education', 88.00, 87.00, 89.00, 88.00, 88.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(8, 9, '2025-2026', 'Grade 7', 'Rizal', 'MAPEH', 86.00, 87.00, 88.00, 87.00, 87.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(9, 10, '2025-2026', 'Grade 7', 'Rizal', 'Filipino', 92.00, 93.00, 91.00, 94.00, 93.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(10, 10, '2025-2026', 'Grade 7', 'Rizal', 'English', 94.00, 95.00, 93.00, 96.00, 95.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(11, 10, '2025-2026', 'Grade 7', 'Rizal', 'Mathematics', 88.00, 89.00, 90.00, 91.00, 90.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(12, 10, '2025-2026', 'Grade 7', 'Rizal', 'Science', 91.00, 92.00, 90.00, 93.00, 92.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(13, 10, '2025-2026', 'Grade 7', 'Rizal', 'Araling Panlipunan', 89.00, 90.00, 91.00, 92.00, 91.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(14, 10, '2025-2026', 'Grade 7', 'Rizal', 'Edukasyon sa Pagpapakatao', 95.00, 94.00, 96.00, 95.00, 95.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(15, 10, '2025-2026', 'Grade 7', 'Rizal', 'Technology and Livelihood Education', 90.00, 91.00, 92.00, 91.00, 91.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(16, 10, '2025-2026', 'Grade 7', 'Rizal', 'MAPEH', 93.00, 92.00, 94.00, 93.00, 93.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(17, 11, '2025-2026', 'Grade 7', 'Rizal', 'Filipino', 78.00, 79.00, 80.00, 81.00, 80.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(18, 11, '2025-2026', 'Grade 7', 'Rizal', 'English', 75.00, 76.00, 74.00, 77.00, 76.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(19, 11, '2025-2026', 'Grade 7', 'Rizal', 'Mathematics', 82.00, 83.00, 81.00, 84.00, 83.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(20, 11, '2025-2026', 'Grade 7', 'Rizal', 'Science', 79.00, 78.00, 80.00, 79.00, 79.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(21, 11, '2025-2026', 'Grade 7', 'Rizal', 'Araling Panlipunan', 76.00, 77.00, 78.00, 79.00, 78.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(22, 11, '2025-2026', 'Grade 7', 'Rizal', 'Edukasyon sa Pagpapakatao', 83.00, 84.00, 85.00, 84.00, 84.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(23, 11, '2025-2026', 'Grade 7', 'Rizal', 'Technology and Livelihood Education', 80.00, 81.00, 82.00, 81.00, 81.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(24, 11, '2025-2026', 'Grade 7', 'Rizal', 'MAPEH', 77.00, 78.00, 79.00, 80.00, 79.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(25, 12, '2025-2026', 'Grade 7', 'Rizal', 'Filipino', 85.00, 86.00, 87.00, 88.00, 87.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(26, 12, '2025-2026', 'Grade 7', 'Rizal', 'English', 88.00, 89.00, 87.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(27, 12, '2025-2026', 'Grade 7', 'Rizal', 'Mathematics', 72.00, 73.00, 71.00, 74.00, 73.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(28, 12, '2025-2026', 'Grade 7', 'Rizal', 'Science', 83.00, 84.00, 82.00, 85.00, 84.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(29, 12, '2025-2026', 'Grade 7', 'Rizal', 'Araling Panlipunan', 87.00, 88.00, 89.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(30, 12, '2025-2026', 'Grade 7', 'Rizal', 'Edukasyon sa Pagpapakatao', 90.00, 91.00, 92.00, 91.00, 91.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(31, 12, '2025-2026', 'Grade 7', 'Rizal', 'Technology and Livelihood Education', 85.00, 86.00, 87.00, 86.00, 86.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(32, 12, '2025-2026', 'Grade 7', 'Rizal', 'MAPEH', 89.00, 90.00, 88.00, 91.00, 90.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(33, 13, '2025-2026', 'Grade 7', 'Rizal', 'Filipino', 70.00, 71.00, 69.00, 72.00, 71.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(34, 13, '2025-2026', 'Grade 7', 'Rizal', 'English', 73.00, 72.00, 74.00, 73.00, 73.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(35, 13, '2025-2026', 'Grade 7', 'Rizal', 'Mathematics', 68.00, 69.00, 67.00, 70.00, 69.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(36, 13, '2025-2026', 'Grade 7', 'Rizal', 'Science', 72.00, 71.00, 73.00, 72.00, 72.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(37, 13, '2025-2026', 'Grade 7', 'Rizal', 'Araling Panlipunan', 74.00, 75.00, 76.00, 75.00, 75.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(38, 13, '2025-2026', 'Grade 7', 'Rizal', 'Edukasyon sa Pagpapakatao', 78.00, 79.00, 80.00, 79.00, 79.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(39, 13, '2025-2026', 'Grade 7', 'Rizal', 'Technology and Livelihood Education', 76.00, 77.00, 78.00, 77.00, 77.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(40, 13, '2025-2026', 'Grade 7', 'Rizal', 'MAPEH', 74.00, 75.00, 73.00, 76.00, 75.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(41, 19, '2025-2026', 'Grade 11', 'STEM', 'Oral Communication', 90.00, 91.00, 89.00, 92.00, 91.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(42, 19, '2025-2026', 'Grade 11', 'STEM', 'Reading and Writing', 88.00, 89.00, 87.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(43, 19, '2025-2026', 'Grade 11', 'STEM', 'Komunikasyon at Pananaliksik', 85.00, 86.00, 84.00, 87.00, 86.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(44, 19, '2025-2026', 'Grade 11', 'STEM', '21st Century Literature', 87.00, 88.00, 86.00, 89.00, 88.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(45, 19, '2025-2026', 'Grade 11', 'STEM', 'General Mathematics', 92.00, 93.00, 94.00, 95.00, 94.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(46, 19, '2025-2026', 'Grade 11', 'STEM', 'Statistics and Probability', 90.00, 91.00, 92.00, 93.00, 92.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(47, 19, '2025-2026', 'Grade 11', 'STEM', 'Earth and Life Science', 88.00, 89.00, 87.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(48, 19, '2025-2026', 'Grade 11', 'STEM', 'Physical Science', 91.00, 92.00, 90.00, 93.00, 92.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(49, 20, '2025-2026', 'Grade 11', 'STEM', 'Oral Communication', 95.00, 94.00, 96.00, 95.00, 95.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(50, 20, '2025-2026', 'Grade 11', 'STEM', 'Reading and Writing', 93.00, 94.00, 92.00, 95.00, 94.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(51, 20, '2025-2026', 'Grade 11', 'STEM', 'Komunikasyon at Pananaliksik', 91.00, 92.00, 90.00, 93.00, 92.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(52, 20, '2025-2026', 'Grade 11', 'STEM', '21st Century Literature', 94.00, 95.00, 93.00, 96.00, 95.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(53, 20, '2025-2026', 'Grade 11', 'STEM', 'General Mathematics', 89.00, 90.00, 91.00, 92.00, 91.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(54, 20, '2025-2026', 'Grade 11', 'STEM', 'Statistics and Probability', 87.00, 88.00, 89.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(55, 20, '2025-2026', 'Grade 11', 'STEM', 'Earth and Life Science', 92.00, 93.00, 91.00, 94.00, 93.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(56, 20, '2025-2026', 'Grade 11', 'STEM', 'Physical Science', 88.00, 89.00, 87.00, 90.00, 89.00, 'Passed', '2026-06-19 18:37:58', '2026-06-19 18:37:58');

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
(1, '100000000001', 'Grade 1', 'Mabini', 'Ana', 'B.', 'Garcia', 'Female', '2018-03-12', 7, 'Cebuano', 'Roman Catholic', 'Purok 1, Minanga, Cagayan de Oro', 'Luz B. Garcia', 'Roberto Garcia', 'Luz B. Garcia', 'Mother', '09171234001', 'ana.garcia@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(2, '100000000002', 'Grade 1', 'Mabini', 'Carlo', 'D.', 'Mendoza', 'Male', '2018-05-20', 7, 'Cebuano', 'Roman Catholic', 'Purok 2, Minanga, Cagayan de Oro', 'Elena D. Mendoza', 'Jose Mendoza', 'Elena D. Mendoza', 'Mother', '09171234002', 'carlo.mendoza@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(3, '100000000003', 'Grade 1', 'Mabini', 'Diana', 'F.', 'Cruz', 'Female', '2018-07-08', 7, 'Cebuano', 'Iglesia ni Cristo', 'Purok 3, Minanga, Cagayan de Oro', 'Perla F. Cruz', 'Armando Cruz', 'Perla F. Cruz', 'Mother', '09171234003', 'diana.cruz@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(4, '100000000004', 'Grade 1', 'Mabini', 'Emilio', 'S.', 'Torres', 'Male', '2018-01-15', 7, 'Cebuano', 'Roman Catholic', 'Purok 4, Minanga, Cagayan de Oro', 'Rosa S. Torres', 'Manuel Torres', 'Rosa S. Torres', 'Mother', '09171234004', 'emilio.torres@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(5, '100000000005', 'Grade 1', 'Mabini', 'Fatima', 'L.', 'Villanueva', 'Female', '2018-09-22', 7, 'Maranao', 'Islam', 'Purok 5, Minanga, Cagayan de Oro', 'Aisha L. Villanueva', 'Ahmad Villanueva', 'Aisha L. Villanueva', 'Mother', '09171234005', 'fatima.villanueva@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(6, '100000000006', 'Grade 4', 'Bonifacio', 'Gerald', 'M.', 'Pascual', 'Male', '2015-04-10', 10, 'Cebuano', 'Roman Catholic', 'Purok 1, Minanga, Cagayan de Oro', 'Nora M. Pascual', 'Fernando Pascual', 'Nora M. Pascual', 'Mother', '09171234006', 'gerald.pascual@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(7, '100000000007', 'Grade 4', 'Bonifacio', 'Hannah', 'C.', 'Reyes', 'Female', '2015-06-18', 10, 'Cebuano', 'Born Again', 'Purok 2, Minanga, Cagayan de Oro', 'Carmen C. Reyes', 'Eduardo Reyes', 'Carmen C. Reyes', 'Mother', '09171234007', 'hannah.reyes@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(8, '100000000008', 'Grade 4', 'Bonifacio', 'Ivan', 'R.', 'Lim', 'Male', '2015-02-28', 10, 'Bisaya', 'Roman Catholic', 'Purok 3, Minanga, Cagayan de Oro', 'Teresita R. Lim', 'William Lim', 'Teresita R. Lim', 'Mother', '09171234008', 'ivan.lim@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(9, '123456789001', 'Grade 7', 'Rizal', 'Juan', 'P.', 'Dela Cruz', 'Male', '2012-08-14', 13, 'Cebuano', 'Roman Catholic', 'Purok 2, Minanga, Cagayan de Oro', 'Nelia P. Dela Cruz', 'Roberto Dela Cruz', 'Nelia P. Dela Cruz', 'Mother', '09181234001', 'juan.delacruz@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(10, '123456789002', 'Grade 7', 'Rizal', 'Maria', 'C.', 'Santos', 'Female', '2012-03-25', 13, 'Cebuano', 'Roman Catholic', 'Purok 4, Minanga, Cagayan de Oro', 'Gloria C. Santos', 'Eduardo Santos', 'Gloria C. Santos', 'Mother', '09181234002', 'maria.santos@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(11, '123456789003', 'Grade 7', 'Rizal', 'Pedro', 'A.', 'Reyes', 'Male', '2012-11-07', 13, 'Cebuano', 'Iglesia ni Cristo', 'Purok 6, Minanga, Cagayan de Oro', 'Caridad A. Reyes', 'Alejandro Reyes', 'Caridad A. Reyes', 'Mother', '09181234003', 'pedro.reyes@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(12, '123456789004', 'Grade 7', 'Rizal', 'Lourdes', 'B.', 'Fernandez', 'Female', '2012-05-30', 13, 'Cebuano', 'Roman Catholic', 'Purok 7, Minanga, Cagayan de Oro', 'Mercy B. Fernandez', 'Carlos Fernandez', 'Mercy B. Fernandez', 'Mother', '09181234004', 'lourdes.fernandez@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(13, '123456789005', 'Grade 7', 'Rizal', 'Ramon', 'E.', 'Bautista', 'Male', '2012-01-19', 13, 'Bisaya', 'Roman Catholic', 'Purok 8, Minanga, Cagayan de Oro', 'Josefa E. Bautista', 'Ernesto Bautista', 'Josefa E. Bautista', 'Mother', '09181234005', 'ramon.bautista@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(14, '123456789006', 'Grade 8', 'Luna', 'Sofia', 'G.', 'Aquino', 'Female', '2011-04-22', 14, 'Cebuano', 'Roman Catholic', 'Purok 1, Minanga, Cagayan de Oro', 'Leticia G. Aquino', 'Ramon Aquino', 'Leticia G. Aquino', 'Mother', '09191234001', 'sofia.aquino@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(15, '123456789007', 'Grade 8', 'Luna', 'Marco', 'T.', 'Ramos', 'Male', '2011-09-13', 14, 'Cebuano', 'Born Again', 'Purok 2, Minanga, Cagayan de Oro', 'Virginia T. Ramos', 'Dante Ramos', 'Virginia T. Ramos', 'Mother', '09191234002', 'marco.ramos@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(16, '123456789008', 'Grade 8', 'Luna', 'Cristina', 'V.', 'Navarro', 'Female', '2011-12-05', 14, 'Cebuano', 'Roman Catholic', 'Purok 3, Minanga, Cagayan de Oro', 'Bella V. Navarro', 'Nestor Navarro', 'Bella V. Navarro', 'Mother', '09191234003', 'cristina.navarro@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(17, '123456789009', 'Grade 10', 'Mabini', 'Jerome', 'O.', 'Castillo', 'Male', '2009-07-17', 16, 'Cebuano', 'Roman Catholic', 'Purok 5, Minanga, Cagayan de Oro', 'Rosario O. Castillo', 'Ignacio Castillo', 'Rosario O. Castillo', 'Mother', '09201234001', 'jerome.castillo@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(18, '123456789010', 'Grade 10', 'Mabini', 'Kathleen', 'D.', 'Soriano', 'Female', '2009-02-14', 16, 'Cebuano', 'Roman Catholic', 'Purok 6, Minanga, Cagayan de Oro', 'Elsa D. Soriano', 'Andres Soriano', 'Elsa D. Soriano', 'Mother', '09201234002', 'kathleen.soriano@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(19, '123456789011', 'Grade 11', 'STEM', 'Lorenzo', 'P.', 'Miranda', 'Male', '2008-06-30', 17, 'Cebuano', 'Roman Catholic', 'Purok 1, Minanga, Cagayan de Oro', 'Patricia P. Miranda', 'Luis Miranda', 'Patricia P. Miranda', 'Mother', '09211234001', 'lorenzo.miranda@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(20, '123456789012', 'Grade 11', 'STEM', 'Michelle', 'R.', 'Santos', 'Female', '2008-10-25', 17, 'Cebuano', 'Roman Catholic', 'Purok 2, Minanga, Cagayan de Oro', 'Aida R. Santos', 'Victor Santos', 'Aida R. Santos', 'Mother', '09211234002', 'michelle.santos@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(21, '123456789013', 'Grade 11', 'STEM', 'Noel', 'C.', 'Garcia', 'Male', '2008-04-08', 17, 'Bisaya', 'Roman Catholic', 'Purok 3, Minanga, Cagayan de Oro', 'Celia C. Garcia', 'Rodrigo Garcia', 'Celia C. Garcia', 'Mother', '09211234003', 'noel.garcia@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(22, '123456789014', 'Grade 11', 'STEM', 'Olivia', 'M.', 'Dela Torre', 'Female', '2008-08-16', 17, 'Cebuano', 'Born Again', 'Purok 4, Minanga, Cagayan de Oro', 'Gina M. Dela Torre', 'Oscar Dela Torre', 'Gina M. Dela Torre', 'Mother', '09211234004', 'olivia.delatorre@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(23, '123456789015', 'Grade 11', 'ABM', 'Paolo', 'N.', 'Cruz', 'Male', '2008-01-12', 17, 'Cebuano', 'Roman Catholic', 'Purok 5, Minanga, Cagayan de Oro', 'Norma N. Cruz', 'Benjamin Cruz', 'Norma N. Cruz', 'Mother', '09211234005', 'paolo.cruz@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(24, '123456789016', 'Grade 11', 'ABM', 'Queenie', 'S.', 'Flores', 'Female', '2008-03-29', 17, 'Cebuano', 'Roman Catholic', 'Purok 6, Minanga, Cagayan de Oro', 'Susan S. Flores', 'Danilo Flores', 'Susan S. Flores', 'Mother', '09211234006', 'queenie.flores@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(25, '123456789017', 'Grade 12', 'HUMSS', 'Rafael', 'J.', 'Morales', 'Male', '2007-05-21', 18, 'Cebuano', 'Roman Catholic', 'Purok 7, Minanga, Cagayan de Oro', 'Imee J. Morales', 'Felix Morales', 'Imee J. Morales', 'Mother', '09221234001', 'rafael.morales@student.minanga.edu.ph', '2025-2026', 'active', '2026-06-19 18:37:58', '2026-06-19 18:37:58');

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

INSERT INTO `users` (`id`, `role`, `username`, `password`, `name`, `email`, `position`, `lrn`, `grade_level`, `section`, `status`, `sec_question`, `sec_answer`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', '$2y$10$texB0QQT3BpqAHc1/NHa/eUgRUiw0cJBo0wKfa.thk6heF7ZMWtWa', 'Maria L. Reyes', 'admin@minanga.edu.ph', 'School Administrator', NULL, NULL, NULL, 'active', 'What is the name of your first pet?', 'Bantay', '2026-06-19 18:37:58', '2026-06-27 16:38:01'),
(2, 'teacher', 'teacher', '$2y$10$rSyq7hwCZfUouUG4OYZZ1.Ij.uUsi4drJsWA29dzn5EqL4zcVItGW', 'Ricardo G. Santos', 'rsantos@minanga.edu.ph', 'Grade 7 Adviser / Math Teacher', NULL, NULL, NULL, 'active', 'What is your mother\'s maiden name?', 'Dela Cruz', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(3, 'teacher', 'teacher2', '$2y$10$RfCf9Eiwxpq.KCaZl1fa6OdZPJS1c3XHZ1Rfj6pf0Lro3YSi.gufa', 'Josephine A. Villanueva', 'jvillanueva@minanga.edu.ph', 'Grade 1 Adviser / Filipino Teacher', NULL, NULL, NULL, 'active', 'What city were you born in?', 'Cagayan de Oro', '2026-06-19 18:37:58', '2026-06-19 18:37:58'),
(4, 'student', 'student2025', '$2y$10$uUD1po43Zh8wF1mSBFSvAeLf6..9NQtS9cSrfJKzimr6t.kWgdj4C', 'Juan P. Dela Cruz', 'juan.delacruz@student.minanga.edu.ph', NULL, '123456789001', 'Grade 7', 'Rizal', 'active', 'What is the name of your elementary school?', 'Minanga Elementary', '2026-06-19 18:37:58', '2026-06-19 18:37:58');

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
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lrn` (`lrn`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
