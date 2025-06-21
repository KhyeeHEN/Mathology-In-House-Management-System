-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2025 at 05:54 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u656820910_mathology`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `record_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `timetable_datetime` datetime NOT NULL COMMENT 'Scheduled date/time of the session',
  `attendance_datetime` datetime DEFAULT NULL COMMENT 'Actual date/time of attendance',
  `hours_attended` decimal(4,1) DEFAULT 0.0 COMMENT 'In 0.5 increments (e.g., 1.5 hours)',
  `hours_replacement` decimal(4,1) DEFAULT 0.0 COMMENT 'Replacement hours booked',
  `hours_remaining` decimal(4,1) DEFAULT 0.0 COMMENT 'Remaining balance (e.g., for packages)',
  `status` enum('attended','missed','replacement_booked','topup') DEFAULT 'attended',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `course` int(11) NOT NULL COMMENT 'Course name (matches student/instructor timetable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`record_id`, `student_id`, `instructor_id`, `timetable_datetime`, `attendance_datetime`, `hours_attended`, `hours_replacement`, `hours_remaining`, `status`, `created_at`, `updated_at`, `course`) VALUES
(1, 1, 1, '2023-11-01 16:00:00', '2023-11-01 16:05:00', 3.0, 0.0, 178.0, 'missed', '2025-04-13 06:36:42', '2025-06-18 08:45:15', 1),
(2, 6, 1, '2023-10-16 10:00:00', '2023-10-16 10:10:00', 2.0, 0.0, 16.0, 'attended', '2025-04-13 06:36:42', '2025-06-21 05:20:29', 2),
(3, 7, 2, '2023-10-18 16:00:00', '2023-10-18 16:00:00', 1.5, 0.0, 14.5, 'attended', '2025-04-13 06:36:42', '2025-06-21 05:20:33', 3),
(4, 9, 2, '2023-10-19 13:00:00', '2023-10-19 13:05:00', 2.5, 0.0, 17.5, 'attended', '2025-04-13 06:36:42', '2025-06-21 05:20:36', 4),
(5, 10, NULL, '2023-10-19 09:00:00', '2023-10-19 09:10:00', 1.5, 0.0, 18.5, 'attended', '2025-04-13 06:36:42', '2025-06-11 12:07:26', 5),
(6, 16, NULL, '2023-10-25 15:00:00', NULL, 0.0, 0.0, 15.0, 'missed', '2025-04-13 06:36:42', '2025-06-11 12:07:26', 6),
(7, 3, NULL, '2023-11-03 09:00:00', NULL, 0.0, 0.0, 20.0, 'missed', '2025-04-13 06:36:42', '2025-06-11 12:07:26', 7),
(8, 5, NULL, '2023-11-05 15:00:00', '2025-06-11 22:10:00', 0.0, 2.0, 18.0, 'attended', '2025-04-13 06:36:42', '2025-06-11 14:10:45', 8),
(9, 2, NULL, '2023-11-02 14:00:00', NULL, 0.0, 1.5, 19.5, 'missed', '2025-04-13 06:36:42', '2025-06-11 12:07:26', 9),
(10, 4, NULL, '2023-11-04 11:00:00', '2023-11-04 11:15:00', 1.0, 0.0, 19.0, 'attended', '2025-04-13 06:36:42', '2025-06-11 12:07:26', 10),
(20, 10, 1, '2025-06-19 17:10:00', '2025-07-01 17:10:00', 6.0, 4.0, 0.0, 'attended', '2025-06-18 09:10:38', '2025-06-21 05:20:24', 1),
(27, NULL, 26, '0000-00-00 00:00:00', NULL, 0.0, 0.0, 0.0, 'attended', '2025-06-21 05:39:48', '2025-06-21 05:39:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` enum('Regular','Maintenance','Intensive','Super Intensive') NOT NULL,
  `level` enum('Pre-Primary and Primary','Secondary','Upper Secondary','Post Secondary') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `level`, `created_at`) VALUES
(1, 'Regular', 'Pre-Primary and Primary', '2025-06-11 07:36:24'),
(2, 'Regular', 'Secondary', '2025-06-11 07:36:24'),
(3, 'Regular', 'Upper Secondary', '2025-06-11 07:36:24'),
(4, 'Regular', 'Post Secondary', '2025-06-11 07:36:24'),
(5, 'Maintenance', 'Pre-Primary and Primary', '2025-06-11 07:36:24'),
(6, 'Maintenance', 'Secondary', '2025-06-11 07:36:24'),
(7, 'Maintenance', 'Upper Secondary', '2025-06-11 07:36:24'),
(8, 'Maintenance', 'Post Secondary', '2025-06-11 07:36:24'),
(9, 'Intensive', 'Pre-Primary and Primary', '2025-06-11 07:36:24'),
(10, 'Intensive', 'Secondary', '2025-06-11 07:36:24'),
(11, 'Intensive', 'Upper Secondary', '2025-06-11 07:36:24'),
(12, 'Intensive', 'Post Secondary', '2025-06-11 07:36:24'),
(13, 'Super Intensive', 'Pre-Primary and Primary', '2025-06-11 07:36:24'),
(14, 'Super Intensive', 'Secondary', '2025-06-11 07:36:24'),
(15, 'Super Intensive', 'Upper Secondary', '2025-06-17 17:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `course_fees`
--

CREATE TABLE `course_fees` (
  `fee_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `package_hours` int(11) NOT NULL,
  `time` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_fees`
--

INSERT INTO `course_fees` (`fee_id`, `course_id`, `fee_amount`, `package_hours`, `time`, `last_updated`) VALUES
(1, 1, 280.00, 8, 'Monthly', '2025-06-13 13:09:25'),
(2, 1, 800.00, 24, 'Quarterly', '2025-06-13 13:09:25'),
(3, 1, 1560.00, 48, 'Half Yearly', '2025-06-13 13:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `instructor_id` int(11) NOT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Gender` tinyint(1) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `Highest_Education` text DEFAULT NULL,
  `Remark` varchar(100) DEFAULT NULL,
  `Training_Status` varchar(50) DEFAULT NULL,
  `Employment_Type` enum('Part-Time','Full-Time') NOT NULL DEFAULT 'Full-Time',
  `Working_Days` varchar(100) DEFAULT NULL,
  `Worked_Days` int(11) NOT NULL DEFAULT 0,
  `Total_Hours` float NOT NULL,
  `contact` varchar(12) NOT NULL,
  `hiring_status` enum('true','false') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`instructor_id`, `Last_Name`, `First_Name`, `Gender`, `DOB`, `Highest_Education`, `Remark`, `Training_Status`, `Employment_Type`, `Working_Days`, `Worked_Days`, `Total_Hours`, `contact`, `hiring_status`) VALUES
(1, 'Tan', 'Kok Wei', 1, '1985-03-15', 'Bachelor, Master', 'Experienced in Math', 'Completed', 'Full-Time', NULL, 11, 0, '60196226533', 'true'),
(2, 'Lim', 'Mei Lin', 0, '1990-07-22', 'Bachelor', 'Good communicator', 'In Progress', 'Part-Time', 'Monday,Wednesday,Friday', 0, 0, '60126578956', 'true'),
(3, 'Chong', 'Ahmad bin', 1, '1978-11-30', 'Master, PhD', 'Specializes in Science', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(4, 'Ng', 'Siew Yee', 0, '1988-05-10', 'Bachelor', 'Needs more training', 'In Progress', 'Part-Time', NULL, 0, 0, '', 'true'),
(5, 'Lee', 'Chin Fatt', 1, '1982-09-18', 'Bachelor, Master', 'Excellent in Physics', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(6, 'Wong', 'Li Ying', 0, '1995-01-25', 'Bachelor', 'New instructor', 'In Progress', 'Part-Time', 'Monday,Wednesday,Friday', 0, 0, '', 'true'),
(7, 'Chew', 'Teck Guan', 1, '1980-04-12', 'Master', 'Strong in Chemistry', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(8, 'Yap', 'Sook Fun', 0, '1987-08-05', 'Bachelor, Master', 'Dedicated teacher', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(9, 'Khoo', 'Wei Ming', 1, '1992-12-20', 'Bachelor', 'Needs to improve', 'In Progress', 'Part-Time', 'Monday,Wednesday,Friday', 0, 0, '', 'true'),
(10, 'Chan', 'Mei Chen', 0, '1983-06-14', 'Master, PhD', 'Expert in English', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(11, 'Goh', 'Kok Seng', 1, '1975-02-28', 'Bachelor, Master', 'Veteran instructor', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(12, 'Lau', 'Siew Har', 0, '1990-10-09', 'Bachelor', 'Good with students', 'In Progress', 'Part-Time', 'Tuesday,Thursday', 0, 0, '', 'true'),
(13, 'Teo', 'Chin Wei', 1, '1986-03-03', 'Master', 'Specializes in Geography', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(14, 'Ho', 'Li Mei', 0, '1993-07-17', 'Bachelor', 'Enthusiastic', 'In Progress', 'Part-Time', 'Tuesday,Thursday', 0, 0, '', 'true'),
(15, 'Ong', 'Teck Soon', 1, '1981-11-11', 'Bachelor, Master', 'Strong in History', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(16, 'Low', 'Siew Ling', 0, '1989-04-25', 'Master', 'Great at Biology', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(17, 'Soh', 'Wei Keong', 1, '1984-08-30', 'Bachelor', 'Needs more experience', 'In Progress', 'Part-Time', 'Tuesday,Thursday', 0, 0, '', 'true'),
(18, 'Chua', 'Mei Yin', 0, '1991-02-14', 'Bachelor, Master', 'Excellent in Literature', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(19, 'Pang', 'Kok Wai', 1, '1979-06-20', 'Master, PhD', 'Expert in Add Math', 'Completed', 'Full-Time', NULL, 0, 0, '', 'true'),
(20, 'Yeoh', 'Siew Ching', 0, '1985-12-05', 'Bachelor', 'Promising instructor', 'In Progress', 'Part-Time', 'Tuesday,Thursday', 0, 0, '', 'true'),
(24, 'Han', 'Chi Yuen', 1, '2025-06-11', 'Bachelor, Master', '', 'Completed', 'Full-Time', NULL, 0, 5, '0196092409', 'true'),
(26, 'Siew Sin', 'Tan', 1, '1999-12-11', 'Bachelor, Master', '', 'Completed', 'Full-Time', 'Friday', 0, 8, '6012345678', 'true');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_courses`
--

CREATE TABLE `instructor_courses` (
  `instructor_course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_courses`
--

INSERT INTO `instructor_courses` (`instructor_course_id`, `instructor_id`, `course_id`, `assigned_date`, `status`) VALUES
(1, 1, 1, '2023-09-01', 'active'),
(2, 1, 6, '2023-09-01', 'active'),
(3, 2, 3, '2023-09-01', 'active'),
(4, 3, 4, '2023-09-01', 'active'),
(5, 4, 5, '2023-09-01', 'active'),
(7, 1, 4, '0000-00-00', 'active'),
(10, 15, 7, '0000-00-00', 'active'),
(11, 24, 14, '2025-06-20', 'active'),
(12, 1, 2, '0000-00-00', 'active'),
(13, 1, 5, '0000-00-00', 'active'),
(15, 26, 1, '2025-06-21', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_timetable`
--

CREATE TABLE `instructor_timetable` (
  `id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','cancelled') DEFAULT 'active',
  `instructor_course_id` int(11) DEFAULT NULL,
  `course` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_timetable`
--

INSERT INTO `instructor_timetable` (`id`, `day`, `start_time`, `end_time`, `approved_at`, `status`, `instructor_course_id`, `course`) VALUES
(1, 'Monday', '09:00:00', '11:00:00', '2023-10-15 06:30:00', 'active', NULL, '1'),
(2, 'Wednesday', '13:00:00', '15:00:00', '2023-10-16 02:15:00', 'active', NULL, '0'),
(3, 'Friday', '10:00:00', '12:00:00', '2023-10-17 03:20:00', 'active', NULL, '4'),
(4, 'Tuesday', '14:00:00', '16:00:00', '2023-10-18 01:45:00', 'active', NULL, '4'),
(5, 'Thursday', '11:00:00', '13:00:00', '2023-10-19 08:10:00', 'active', NULL, '4'),
(7, 'Friday', '10:00:00', '12:00:00', '2025-06-10 15:40:44', 'active', 3, '0'),
(8, 'Tuesday', '10:00:00', '22:00:00', '2025-06-10 15:50:48', 'active', NULL, '3'),
(9, 'Monday', '13:00:00', '14:00:00', '2025-06-10 15:51:11', 'active', NULL, '2'),
(10, 'Wednesday', '04:59:00', '08:09:00', '2025-06-10 16:13:23', 'active', 13, 'Maintenance'),
(11, 'Monday', '06:00:00', '07:00:00', '2025-06-10 16:34:19', 'active', 12, 'Regular'),
(14, 'Wednesday', '06:00:00', '08:00:00', '2025-06-19 05:08:36', 'active', 10, '0'),
(16, 'Tuesday', '10:00:00', '12:00:00', '2025-06-20 06:59:26', 'active', 1, 'Regular'),
(19, 'Friday', '14:00:00', '16:00:00', '2025-06-21 05:39:48', 'active', 15, '1');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_timetable_requests`
--

CREATE TABLE `instructor_timetable_requests` (
  `id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `instructor_course_id` int(11) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_timetable_requests`
--

INSERT INTO `instructor_timetable_requests` (`id`, `day`, `start_time`, `end_time`, `status`, `rejection_reason`, `requested_at`, `instructor_course_id`, `course`) VALUES
(1, 'Monday', '14:00:00', '16:00:00', 'pending', NULL, '2023-11-01 01:15:00', NULL, NULL),
(2, 'Wednesday', '10:00:00', '12:00:00', 'pending', NULL, '2023-11-02 02:30:00', NULL, NULL),
(3, 'Friday', '13:00:00', '15:00:00', 'pending', NULL, '2023-11-03 03:45:00', NULL, NULL),
(4, 'Tuesday', '09:00:00', '11:00:00', 'pending', NULL, '2023-11-04 06:20:00', NULL, NULL),
(5, 'Thursday', '16:00:00', '18:00:00', 'pending', NULL, '2023-11-05 00:50:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','instructor','admin') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `user_id`, `user_type`, `start_date`, `end_date`, `reason`, `status`, `requested_at`, `approved_by`, `approved_at`) VALUES
(1, 2, 'student', '2025-06-03', '2025-06-05', 'Family vacation', 'pending', '2025-06-02 10:51:07', NULL, NULL),
(2, 3, 'instructor', '2025-06-10', '2025-06-12', 'Medical leave', 'pending', '2025-06-02 10:51:07', NULL, NULL),
(3, 1, 'student', '2025-06-02', '2025-06-04', 'family problem', 'approved', '2025-06-02 11:26:21', 4, '2025-06-02 11:37:35'),
(8, 4, 'student', '2025-06-03', '2025-06-04', 'rrr', 'pending', '2025-06-02 11:40:14', NULL, NULL),
(9, 3, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:30', NULL, NULL),
(10, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:34', NULL, NULL),
(11, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:36', NULL, NULL),
(12, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'approved', '2025-06-02 11:59:37', 4, '2025-06-19 17:37:46'),
(13, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:37', NULL, NULL),
(14, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:38', NULL, NULL),
(15, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:38', NULL, NULL),
(16, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:39', NULL, NULL),
(17, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:40', NULL, NULL),
(18, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:40', NULL, NULL),
(19, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:41', NULL, NULL),
(20, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:42', NULL, NULL),
(21, 2, 'student', '2025-06-16', '2025-05-20', 'sry', 'pending', '2025-06-02 11:59:42', NULL, NULL),
(22, 2, 'student', '2025-06-20', '2025-06-21', 'travel japan', 'pending', '2025-06-06 15:33:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `one_time_fees`
--

CREATE TABLE `one_time_fees` (
  `fee_name` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `one_time_fees`
--

INSERT INTO `one_time_fees` (`fee_name`, `amount`) VALUES
('assessment', 50.00),
('deposit', 100.00),
('registration', 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL COMMENT 'Links to students table',
  `payment_method` enum('credit_card','cash','bank_transfer','ewallet') NOT NULL,
  `payment_mode` enum('monthly','quarterly','semi_annually') NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL COMMENT 'E.g., 1500.00',
  `deposit_status` enum('yes','no') DEFAULT 'no',
  `payment_status` enum('paid','unpaid','pending') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `invoice_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `student_id`, `payment_method`, `payment_mode`, `payment_amount`, `deposit_status`, `payment_status`, `payment_date`, `invoice_path`) VALUES
(2, 5, 'cash', 'quarterly', 1200.00, 'yes', 'paid', '2025-04-13 06:53:06', NULL),
(3, 6, 'bank_transfer', 'semi_annually', 2000.00, 'no', 'unpaid', '2025-04-13 06:53:06', NULL),
(4, 3, 'credit_card', 'monthly', 500.00, 'yes', 'pending', '2025-04-13 06:53:06', NULL),
(5, 12, 'cash', 'quarterly', 1200.00, 'no', 'unpaid', '2025-04-13 06:53:06', NULL),
(6, 7, 'bank_transfer', 'semi_annually', 2000.00, 'yes', 'paid', '2025-04-13 06:53:06', NULL),
(7, 9, 'credit_card', 'monthly', 500.00, 'no', 'unpaid', '2025-04-13 06:53:06', NULL),
(8, 16, 'cash', 'quarterly', 1200.00, 'yes', 'pending', '2025-04-13 06:53:06', NULL),
(9, 4, 'bank_transfer', 'semi_annually', 2000.00, 'yes', 'paid', '2025-04-13 06:53:06', NULL),
(10, 20, 'credit_card', 'monthly', 500.00, 'no', 'unpaid', '2025-04-13 06:53:06', NULL),
(19, 1, 'cash', 'monthly', 480.00, 'yes', 'pending', '2025-06-16 21:48:33', NULL),
(30, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 07:41:30', NULL),
(31, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 07:48:22', NULL),
(33, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 07:55:09', '../../Pages/invoice/Invoice_INV-20250617-33.pdf'),
(34, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 08:06:17', '../../Pages/invoice/Invoice_INV-20250617-34.pdf'),
(35, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 08:10:37', '../../Pages/invoice/Invoice_INV-20250617-35.pdf'),
(37, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 12:31:36', '../../Pages/invoice/Invoice_INV-20250617-37.pdf'),
(38, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 12:49:23', '../../Pages/invoice/Invoice_INV-20250617-38.pdf'),
(39, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 12:53:05', '../../Pages/invoice/Invoice_INV-20250617-39.pdf'),
(40, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 13:09:28', '../../Pages/invoice/Invoice_INV-20250617-40.pdf'),
(41, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 13:44:46', '../../Pages/invoice/Invoice_INV-20250617-41.pdf'),
(42, 1, '', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 13:59:02', '../../Pages/invoice/Invoice_INV-20250617-42.pdf'),
(46, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 14:49:56', '../../Pages/invoice/Invoice_INV-20250617-46.pdf'),
(47, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 14:59:55', '../../Pages/invoice/Invoice_INV-20250617-47.pdf'),
(48, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-17 15:01:59', '../../Pages/invoice/Invoice_INV-20250617-48.pdf'),
(49, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:17:03', NULL),
(50, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:17:09', NULL),
(51, 1, 'bank_transfer', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:23:54', NULL),
(52, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:24:53', NULL),
(53, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:25:14', NULL),
(54, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:28:22', NULL),
(55, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:28:31', NULL),
(56, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:29:54', '../../Pages/invoice/Invoice_INV-20250618-56.pdf'),
(57, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:36:10', NULL),
(58, 1, 'ewallet', 'monthly', 280.00, 'yes', 'paid', '2025-06-18 04:38:09', NULL),
(59, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 04:58:32', '../../Pages/invoice/Invoice_INV-20250618-59.pdf'),
(60, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 06:21:43', '../../Pages/invoice/Invoice_INV-20250618-60.pdf'),
(61, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 06:35:51', '../../Pages/invoice/Invoice_INV-20250618-61.pdf'),
(62, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 06:44:31', '../../Pages/invoice/Invoice_INV-20250618-62.pdf'),
(63, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 06:57:32', '../../Pages/invoice/Invoice_INV-20250618-63.pdf'),
(64, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 07:28:08', '../../Pages/invoice/Invoice_INV-20250618-64.pdf'),
(65, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 07:35:25', '../../Pages/invoice/Invoice_INV-20250618-65.pdf'),
(66, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 07:46:31', '../../Pages/invoice/Invoice_INV-20250618-66.pdf'),
(67, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 08:00:23', '../../Pages/invoice/Invoice_INV-20250618-67.pdf'),
(68, 1, 'ewallet', 'monthly', 280.00, 'no', 'paid', '2025-06-18 08:30:04', '../../Pages/invoice/Invoice_INV-20250618-68.pdf'),
(69, 1, 'ewallet', 'monthly', 2800.00, 'no', 'paid', '2025-06-18 08:45:15', '../../Pages/invoice/Invoice_INV-20250618-69.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `primary_contact_number`
--

CREATE TABLE `primary_contact_number` (
  `contact_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Relationship_with_Student` varchar(50) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Postcode` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `primary_contact_number`
--

INSERT INTO `primary_contact_number` (`contact_id`, `student_id`, `Last_Name`, `First_Name`, `Relationship_with_Student`, `Phone`, `Email`, `Address`, `Postcode`, `created_at`) VALUES
(1, 1, 'Tan', 'Kok Wei', 'Parent', '0123456789', 'kokwei.tan@gmail.com', '12, Jalan Bukit, Taman Sentosa', 52000, '2025-06-01 06:28:24'),
(2, 2, 'Lim', 'Siew Mei', 'Guardian', '0167891234', 'siewmei.lim@yahoo.com', '45, Lorong Bunga, Taman Indah', 47300, '2025-06-01 06:28:24'),
(3, 3, 'Chong', 'Ahmad bin', 'Parent', '0198765432', 'ahmad.chong@hotmail.com', '78, Jalan Merdeka, Bandar Baru', 58200, '2025-06-01 06:28:24'),
(4, 4, 'Ng', 'Lay Har', 'Parent', '0134567890', 'layhar.ng@gmail.com', '23, Jalan SS2/24, Petaling Jaya', 47301, '2025-06-01 06:28:24'),
(5, 5, 'Lee', 'Chee Meng', 'Guardian', '0171234567', 'cheemeng.lee@outlook.com', '56, Lorong Damai, Taman Desa', 58100, '2025-06-01 06:28:24'),
(6, 6, 'Wong', 'Mei Fong', 'Parent', '0112345678', 'meifong.wong@gmail.com', '89, Jalan Puchong, Taman Kinrara', 47100, '2025-06-01 06:28:24'),
(7, 7, 'Chew', 'Boon Seng', 'Parent', '0145678901', 'boonseng.chew@yahoo.com', '34, Jalan Ipoh, Sentul', 51000, '2025-06-01 06:28:24'),
(8, 8, 'Yap', 'Hui Ling', 'Guardian', '0189012345', 'huiling.yap@gmail.com', '67, Lorong Gasing, Taman Megah', 47400, '2025-06-01 06:28:24'),
(9, 9, 'Khoo', 'Teck Guan', 'Parent', '0129876543', 'teckguan.khoo@live.com', '90, Jalan Klang, Taman Sri', 52100, '2025-06-01 06:28:24'),
(10, 10, 'Chan', 'Sook Yee', 'Parent', '0191234567', 'sookyee.chan@gmail.com', '15, Jalan Ampang, Kuala Lumpur', 50450, '2025-06-01 06:28:24'),
(11, 11, 'Goh', 'Wei Liang', 'Parent', '0137894561', 'weiliang.goh@gmail.com', '22, Jalan Setia, Taman Setiawangsa', 54200, '2025-06-01 06:28:24'),
(12, 12, 'Lau', 'Poh Ling', 'Guardian', '0162345678', 'pohling.lau@yahoo.com', '33, Lorong Kenari, Taman Melawati', 53100, '2025-06-01 06:28:24'),
(13, 13, 'Teo', 'Chin Hock', 'Parent', '0178901234', 'chinhock.teo@outlook.com', '44, Jalan Perak, Taman Tun', 60000, '2025-06-01 06:28:24'),
(14, 14, 'Ho', 'Mei Yin', 'Parent', '0113456789', 'meiyin.ho@gmail.com', '55, Jalan SS3/12, Kelana Jaya', 47300, '2025-06-01 06:28:24'),
(15, 15, 'Ong', 'Kok Leong', 'Guardian', '0149012345', 'kokleong.ong@live.com', '66, Lorong Cempaka, Taman Cheras', 56100, '2025-06-01 06:28:24'),
(16, 16, 'Low', 'Siew Lan', 'Parent', '0124567890', 'siewlan.low@gmail.com', '77, Jalan Bukit Bintang, KLCC', 55100, '2025-06-01 06:28:24'),
(17, 17, 'Soh', 'Teck Ming', 'Parent', '0195678901', 'teckming.soh@yahoo.com', '88, Jalan Kuchai Lama, Taman Kuchai', 58200, '2025-06-01 06:28:24'),
(18, 18, 'Chua', 'Li Ping', 'Guardian', '0131234567', 'liping.chua@gmail.com', '99, Lorong Seri, Taman Seri Bahtera', 51200, '2025-06-01 06:28:24'),
(19, 19, 'Pang', 'Wei Keong', 'Parent', '0167894561', 'weikeong.pang@outlook.com', '11, Jalan Damansara, Damansara Heights', 50490, '2025-06-01 06:28:24'),
(20, 20, 'Yeoh', 'Siew Ching', 'Parent', '0172345678', 'siewching.yeoh@gmail.com', '22, Jalan Bangsar, Bangsar Baru', 59100, '2025-06-01 06:28:24');

-- --------------------------------------------------------

--
-- Table structure for table `secondary_contact_number`
--

CREATE TABLE `secondary_contact_number` (
  `contact_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Relationship_with_Student` varchar(50) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secondary_contact_number`
--

INSERT INTO `secondary_contact_number` (`contact_id`, `student_id`, `Last_Name`, `First_Name`, `Relationship_with_Student`, `Phone`, `created_at`) VALUES
(1, 1, 'Tan', 'Wei Ling', 'Sibling', '0129876543', '2025-06-01 06:28:37'),
(2, 2, 'Lim', 'Kok Fatt', 'Parent', '0165432109', '2025-06-01 06:28:37'),
(3, 3, 'Chong', 'Mei Yee', 'Guardian', '0198761234', '2025-06-01 06:28:37'),
(4, 4, 'Ng', 'Teck Wei', 'Sibling', '0134561234', '2025-06-01 06:28:37'),
(5, 5, 'Lee', 'Siew Lan', 'Parent', '0177894561', '2025-06-01 06:28:37'),
(6, 6, 'Wong', 'Chin Hock', 'Guardian', '0112347890', '2025-06-01 06:28:37'),
(7, 7, 'Chew', 'Li Ping', 'Sibling', '0145671234', '2025-06-01 06:28:37'),
(8, 8, 'Yap', 'Kok Seng', 'Parent', '0189014567', '2025-06-01 06:28:37'),
(9, 9, 'Khoo', 'Mei Chen', 'Guardian', '0123459876', '2025-06-01 06:28:37'),
(10, 10, 'Chan', 'Wei Hong', 'Sibling', '0191237890', '2025-06-01 06:28:37'),
(11, 11, 'Goh', 'Siew Har', 'Parent', '0137891234', '2025-06-01 06:28:37'),
(12, 12, 'Lau', 'Teck Guan', 'Guardian', '0162347890', '2025-06-01 06:28:37'),
(13, 13, 'Teo', 'Li Ying', 'Sibling', '0178904561', '2025-06-01 06:28:37'),
(14, 14, 'Ho', 'Kok Wai', 'Parent', '0113451234', '2025-06-01 06:28:37'),
(15, 15, 'Ong', 'Mei Ling', 'Guardian', '0149017890', '2025-06-01 06:28:37'),
(16, 16, 'Low', 'Chin Wei', 'Sibling', '0124561234', '2025-06-01 06:28:37'),
(17, 17, 'Soh', 'Siew Yee', 'Parent', '0195674561', '2025-06-01 06:28:37'),
(18, 18, 'Chua', 'Wei Ming', 'Guardian', '0131237890', '2025-06-01 06:28:37'),
(19, 19, 'Pang', 'Li Mei', 'Sibling', '0167891234', '2025-06-01 06:28:37'),
(20, 20, 'Yeoh', 'Teck Soon', 'Parent', '0172344561', '2025-06-01 06:28:37');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Gender` tinyint(1) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `School_Syllabus` text DEFAULT NULL,
  `School_Intake` varchar(20) DEFAULT NULL,
  `Current_School_Grade` text DEFAULT NULL,
  `School` varchar(100) DEFAULT NULL,
  `Mathology_Level` enum('1','2','3','4','5','6','7','8','9') DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `status` enum('active','inactive','graduated') DEFAULT 'active',
  `address` varchar(100) NOT NULL,
  `How_Did_You_Heard_About_Us` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `Last_Name`, `First_Name`, `Gender`, `DOB`, `School_Syllabus`, `School_Intake`, `Current_School_Grade`, `School`, `Mathology_Level`, `enrollment_date`, `status`, `address`, `How_Did_You_Heard_About_Us`) VALUES
(1, 'Tan', 'Wei Jie', 1, '2008-05-12', 'IGCSE, Math, Science', 'January', 'Year 10', 'Kingsway International School', '3', '2025-06-17', 'active', '12, Jalan Bunga 1, Bandar Subang, 47600', 'Referral from friends'),
(2, 'Lim', 'Mei Ling', 0, '2007-09-23', 'SPM, Math, English', 'January', 'Form 4', 'SMK Damansara Jaya', '7', NULL, 'active', '35, Jalan Bersatu 5, Taman Bersatu, 47000 Sungai Buloh, Selangor', 'Referral from friends'),
(3, 'Chong', 'Kai Wen', 1, '2009-03-15', 'IGCSE, Physics, Chemistry', '0', 'Year 9', 'Sri KDU International School', '1', NULL, 'active', '45, Jalan Bukit Bintang, 55100 Kuala Lumpur, Wilayah Persekutuan', NULL),
(4, 'Ng', 'Xiu Mei', 0, '2006-11-07', 'SPM, Biology, History', '1', 'Form 5', 'SMK Taman Connaught', '6', NULL, 'active', '101, Jalan Rimba 6, Taman Rimba, 43650 Bandar Baru Bangi, Selangor', NULL),
(5, 'Lee', 'Jun Hao', 1, '2010-01-19', 'KSSR, Math, Malay', '1', 'Standard 6', 'SJK(C) Chung Hwa', '5', NULL, 'active', '23, Jalan Dataran 3, Taman Dataran, 40000 Shah Alam, Selangor', NULL),
(6, 'Wong', 'Siew Ling', 0, '2008-07-30', 'IGCSE, Math, Economics', '1', 'Year 11', 'Garden International School', '4', NULL, 'active', '5, Jalan Impian 10, Taman Impian, 43100 Hulu Langat, Selangor', NULL),
(7, 'Chew', 'Boon Keat', 1, '2007-12-04', 'SPM, Chemistry, Add Math', '1', 'Form 4', 'SMK Sri Permata', '6', NULL, 'active', '9, Jalan Mawar 2, Taman Mawar, 40200 Shah Alam, Selangor', NULL),
(8, 'Yap', 'Hui Xin', 0, '2009-06-18', 'IGCSE, English, Geography', '0', 'Year 10', 'Cempaka International School', '2', NULL, 'active', '49, Jalan Damai 1, Taman Damai, 50000 Kuala Lumpur, Wilayah Persekutuan', NULL),
(9, 'Khoo', 'Zhi Wei', 1, '2006-08-22', 'SPM, Physics, Math', '1', 'Form 5', 'SMK Kepong Baru', '8', NULL, 'active', '24, Jalan Setia 2, Taman Setia, 47600 Subang Jaya, Selangor', NULL),
(10, 'Chan', 'Yi Ting', 0, '2011-02-14', 'KSSR, Science, English', '1', 'Standard 5', 'SJK(C) Lick Hung', '5', NULL, 'active', '66, Jalan Melur 5, Taman Melur, 43300 Seri Kembangan, Selangor', NULL),
(11, 'Goh', 'Jia Hao', 1, '2008-04-09', 'IGCSE, Math, Business', '1', 'Year 10', 'Sunway International School', '3', NULL, 'active', '12, Jalan Setia 3, Taman Setia, 47301 Petaling Jaya, Selangor', NULL),
(12, 'Lau', 'Xin Yi', 0, '2007-10-27', 'SPM, Biology, English', '1', 'Form 4', 'SMK Bukit Jalil', '1', NULL, 'active', '21, Jalan Perdana 5, Taman Perdana, 43600 Bandar Baru Bangi, Selangor', NULL),
(13, 'Teo', 'Ming Zhe', 1, '2009-09-11', 'IGCSE, Chemistry, Math', '0', 'Year 9', 'Taylor’s International School', '1', NULL, 'active', '7, Jalan Alamanda 4, Taman Alamanda, 40400 Shah Alam, Selangor', NULL),
(14, 'Ho', 'Pei Ling', 0, '2006-03-25', 'SPM, Add Math, Physics', '1', 'Form 5', 'SMK Seri Bintang Utara', '7', NULL, 'active', '53, Jalan Merpati 2, Taman Merpati, 42100 Klang, Selangor', NULL),
(15, 'Ong', 'Wei Kang', 1, '2010-05-03', 'KSSR, Math, Malay', '1', 'Standard 6', 'SJK(C) Yuk Chai', '2', NULL, 'active', '19, Jalan Sains 8, Taman Sains, 43650 Bandar Baru Bangi, Selangor', NULL),
(16, 'Low', 'Shu Fen', 0, '2008-12-16', 'IGCSE, Economics, Math', '1', 'Year 11', 'British International School', '2', NULL, 'active', '34, Jalan Damai 5, Taman Damai, 43500 Semenyih, Selangor', NULL),
(17, 'Soh', 'Jian Wei', 1, '2007-06-29', 'SPM, Chemistry, Math', '1', 'Form 4', 'SMK Taman Desa', '7', NULL, 'active', '82, Jalan Raya 6, Taman Raya, 47600 Subang Jaya, Selangor', NULL),
(18, 'Chua', 'Mei Qi', 0, '2009-08-08', 'IGCSE, English, Science', '0', 'Year 10', 'Nexus International School', '5', NULL, 'active', '27, Jalan Pahlawan 8, Taman Pahlawan, 47500 Sungai Buloh, Selangor', NULL),
(19, 'Pang', 'Wei Jun', 1, '2006-10-13', 'SPM, Physics, Add Math', '1', 'Form 5', 'SMK Seafield', '7', NULL, 'active', '24, Jalan Taman Indah, Taman Indah, 41300 Klang, Selangor', NULL),
(20, 'Yeoh', 'Xin Ru', 0, '2011-07-21', 'KSSR, Math, English', '1', 'Standard 5', 'SJK(C) Kuen Cheng', '4', NULL, 'active', '6, Jalan Bayu 2, Taman Bayu, 47600 Subang Jaya, Selangor', NULL),
(21, 'Sim', 'Kai Jie', 1, '2008-02-17', 'IGCSE, Math, Physics', '1', 'Year 10', 'HELP International School', '7', NULL, 'active', '42, Jalan Sungai 4, Taman Sungai, 43100 Hulu Langat, Selangor', NULL),
(22, 'Kong', 'Hui Min', 0, '2007-11-05', 'SPM, Biology, Chemistry', '1', 'Form 4', 'SMK Bandar Utama', '2', NULL, 'active', '18, Jalan Rimba 7, Taman Rimba, 43500 Semenyih, Selangor', NULL),
(23, 'Foo', 'Jun Wei', 1, '2009-04-28', 'IGCSE, Geography, Math', '0', 'Year 9', 'St. John’s International School', '2', NULL, 'active', '55, Jalan Cemerlang 3, Taman Cemerlang, 43100 Hulu Langat, Selangor', NULL),
(24, 'Liew', 'Xin Yi', 0, '2006-12-09', 'SPM, Math, English', '1', 'Form 5', 'SMK Taman SEA', '7', NULL, 'active', '28, Jalan Suria 2, Taman Suria, 47100 Puchong, Selangor', NULL),
(25, 'Tay', 'Zhi Hao', 1, '2010-03-14', 'KSSR, Science, Malay', '1', 'Standard 6', 'SJK(C) Han Chiang', '1', NULL, 'active', '33, Jalan Mentari 3, Taman Mentari, 46100 Petaling Jaya, Selangor', NULL),
(26, 'Koh', 'Pei Wen', 0, '2008-09-02', 'IGCSE, Economics, Math', '1', 'Year 11', 'Uplands International School', '3', NULL, 'active', '63, Jalan Impian 7, Taman Impian, 43100 Hulu Langat, Selangor', NULL),
(27, 'Chin', 'Wei Xiang', 1, '2007-08-19', 'SPM, Physics, Add Math', '1', 'Form 4', 'SMK Subang Utama', '5', NULL, 'active', '77, Jalan Pantai 2, Taman Pantai, 47100 Puchong, Selangor', NULL),
(28, 'Toh', 'Jia Yi', 0, '2009-10-25', 'IGCSE, English, Chemistry', '0', 'Year 10', 'Alice Smith School', '7', NULL, 'active', '15, Jalan Cempaka 4, Taman Cempaka, 42000 Port Klang, Selangor', NULL),
(29, 'Heng', 'Jun Kai', 1, '2006-05-31', 'SPM, Math, Biology', '1', 'Form 5', 'SMK Damansara Utama', '3', NULL, 'active', '39, Jalan Merdeka 4, Taman Merdeka, 47000 Sungai Buloh, Selangor', NULL),
(30, 'Poon', 'Xin Tong', 0, '2011-01-08', 'KSSR, Math, English', '1', 'Standard 5', 'SJK(C) Puay Chai', '1', NULL, 'active', '50, Jalan Alam 5, Taman Alam, 42500 Telok Panglima Garang, Selangor', NULL),
(31, 'Kwan', 'Wei Lun', 1, '2008-06-14', 'IGCSE, Math, Physics', '1', 'Year 10', 'Kolej Tuanku Ja’afar', '4', NULL, 'active', '2, Jalan Kiara 6, Taman Kiara, 52100 Kuala Lumpur, Selangor', NULL),
(32, 'Liang', 'Shu Qi', 0, '2007-03-22', 'SPM, Chemistry, English', '1', 'Form 4', 'SMK Sultan Ismail', '5', NULL, 'active', '14, Jalan Serene 2, Taman Serene, 47500 Sungai Buloh, Selangor', NULL),
(33, 'Yeap', 'Zhi Cong', 1, '2009-07-16', 'IGCSE, Geography, Math', '0', 'Year 9', 'Marlborough College Malaysia', '3', NULL, 'active', '26, Jalan Gemilang 3, Taman Gemilang, 41000 Klang, Selangor', NULL),
(34, 'Ooi', 'Hui Shan', 0, '2006-09-28', 'SPM, Add Math, Physics', '1', 'Form 5', 'SMK Convent Green Lane', '3', NULL, 'active', '32, Jalan Titiwangsa 5, Taman Titiwangsa, 43600 Bandar Baru Bangi, Selangor', NULL),
(35, 'Tham', 'Wei Jie', 1, '2010-11-11', 'KSSR, Science, Malay', '1', 'Standard 6', 'SJK(C) Jalan Davidson', '4', NULL, 'active', '50, Jalan Setia 9, Taman Setia, 47100 Puchong, Selangor', NULL),
(36, 'See', 'Xin Wei', 0, '2008-01-30', 'IGCSE, Math, Economics', '1', 'Year 11', 'Fairview International School', '7', NULL, 'active', '70, Jalan Damai 6, Taman Damai, 43000 Kajang, Selangor', NULL),
(37, 'Phang', 'Jun Wei', 1, '2007-05-07', 'SPM, Physics, Math', '1', 'Form 4', 'SMK Seri Hartamas', '6', NULL, 'active', '8, Jalan Sri Permai 3, Taman Sri Permai, 42700 Banting, Selangor', NULL),
(38, 'Chia', 'Mei Xin', 0, '2009-12-20', 'IGCSE, English, Science', '0', 'Year 10', 'IGB International School', '2', NULL, 'active', '9, Jalan Pahlawan 6, Taman Pahlawan, 47200 Subang Jaya, Selangor', NULL),
(39, 'Tiew', 'Zhi Yang', 1, '2006-04-15', 'SPM, Add Math, Chemistry', '1', 'Form 5', 'SMK Aminuddin Baki', '7', NULL, 'active', '16, Jalan Meranti 3, Taman Meranti, 43300 Seri Kembangan, Selangor', NULL),
(40, 'Law', 'Jia Wen', 0, '2011-06-23', 'KSSR, Math, English', '1', 'Standard 5', 'SJK(C) Chung Kwok', '8', NULL, 'active', '22, Jalan Taman Anggerik 4, Taman Anggerik, 47000 Sungai Buloh, Selangor', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `student_course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('active','completed','dropped') DEFAULT 'active',
  `program_start` date DEFAULT NULL,
  `program_end` date DEFAULT NULL,
  `hours_per_week` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`student_course_id`, `student_id`, `course_id`, `enrollment_date`, `status`, `program_start`, `program_end`, `hours_per_week`) VALUES
(2, 7, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(3, 38, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(4, 27, 4, '2025-04-25', 'active', NULL, NULL, NULL),
(5, 3, 5, '2025-04-25', 'active', NULL, NULL, NULL),
(6, 18, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(7, 23, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(8, 11, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(9, 29, 4, '2025-04-25', 'active', NULL, NULL, NULL),
(10, 14, 5, '2025-04-25', 'active', NULL, NULL, NULL),
(11, 9, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(12, 26, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(13, 22, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(14, 31, 4, '2025-04-25', 'active', NULL, NULL, NULL),
(15, 12, 5, '2025-04-25', 'active', NULL, NULL, NULL),
(16, 40, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(17, 5, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(18, 32, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(19, 24, 4, '2025-04-25', 'active', NULL, NULL, NULL),
(20, 2, 5, '2025-04-25', 'active', NULL, NULL, NULL),
(21, 16, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(22, 4, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(23, 15, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(24, 34, 4, '2025-04-25', 'active', NULL, NULL, NULL),
(25, 19, 5, '2025-04-25', 'active', NULL, NULL, NULL),
(26, 37, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(27, 30, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(28, 36, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(29, 21, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(30, 17, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(31, 1, 1, '2025-04-25', 'active', '2025-06-17', '2025-07-17', 2.00),
(32, 25, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(33, 13, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(34, 35, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(35, 39, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(36, 28, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(37, 6, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(38, 8, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(39, 33, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(40, 20, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(64, 10, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(65, 7, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(66, 27, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(67, 3, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(68, 18, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(69, 23, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(70, 11, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(71, 29, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(72, 14, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(73, 9, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(74, 26, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(75, 22, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(76, 12, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(77, 5, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(78, 24, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(79, 2, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(80, 16, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(81, 4, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(82, 15, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(83, 19, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(84, 30, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(85, 21, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(86, 17, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(88, 25, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(89, 13, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(90, 28, 2, '2025-04-25', 'active', NULL, NULL, NULL),
(91, 6, 1, '2025-04-25', 'active', NULL, NULL, NULL),
(92, 8, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(93, 20, 3, '2025-04-25', 'active', NULL, NULL, NULL),
(97, 2, 3, '2023-09-01', 'active', NULL, NULL, NULL),
(98, 3, 4, '2023-09-01', 'active', NULL, NULL, NULL),
(100, 2, 1, '2025-06-02', 'active', NULL, NULL, NULL),
(106, 1, 3, '2025-06-08', 'active', '2025-06-17', '2025-07-17', 2.00),
(109, 1, 12, '2025-06-16', 'active', '2025-06-17', '2025-07-17', 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `student_timetable`
--

CREATE TABLE `student_timetable` (
  `id` int(11) NOT NULL,
  `course` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','cancelled') DEFAULT 'active',
  `student_course_id` int(11) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_timetable`
--

INSERT INTO `student_timetable` (`id`, `course`, `day`, `start_time`, `end_time`, `approved_at`, `status`, `student_course_id`, `instructor_id`) VALUES
(1, 1, 'Monday', '10:00:00', '12:00:00', '2023-10-15 06:25:00', 'active', 37, 1),
(2, 2, 'Wednesday', '16:00:00', '18:00:00', '2023-10-16 03:40:00', 'active', 2, 1),
(3, 6, 'Friday', '14:00:00', '16:00:00', '2023-10-17 02:55:00', 'active', 38, 1),
(4, 4, 'Tuesday', '13:00:00', '15:00:00', '2023-10-18 00:20:00', 'active', 11, 2),
(6, 5, 'Monday', '16:00:00', '18:00:00', '2025-04-30 14:01:19', 'active', 31, 1),
(8, 3, 'Monday', '03:45:00', '04:59:00', '2025-06-02 08:41:50', 'active', 100, NULL),
(14, 6, 'Thursday', '15:00:00', '17:00:00', '2025-06-08 06:19:49', 'active', 77, NULL),
(15, 7, 'Friday', '15:00:00', '17:00:00', '2025-06-08 06:20:23', 'active', 106, NULL);

--
-- Triggers `student_timetable`
--
DELIMITER $$
CREATE TRIGGER `set_student_course_name` BEFORE INSERT ON `student_timetable` FOR EACH ROW BEGIN
    DECLARE course_id INT;
    
    -- Select the course_id corresponding to the student's course
    SELECT c.course_id 
    INTO course_id
    FROM courses c
    JOIN student_courses sc ON c.course_id = sc.course_id
    WHERE sc.student_course_id = NEW.student_course_id;
    
    -- Set the course column of the new student_timetable row
    SET NEW.course = course_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student_timetable_requests`
--

CREATE TABLE `student_timetable_requests` (
  `id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_course_id` int(11) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_timetable_requests`
--

INSERT INTO `student_timetable_requests` (`id`, `day`, `start_time`, `end_time`, `status`, `rejection_reason`, `requested_at`, `student_course_id`, `course`) VALUES
(1, 'Monday', '16:00:00', '18:00:00', 'approved', NULL, '2023-11-01 04:30:00', 31, 'IGCSE Math Prep'),
(2, 'Wednesday', '14:00:00', '16:00:00', 'pending', NULL, '2023-11-02 05:45:00', NULL, NULL),
(3, 'Friday', '09:00:00', '11:00:00', 'pending', NULL, '2023-11-03 02:20:00', NULL, NULL),
(4, 'Tuesday', '11:00:00', '13:00:00', 'pending', NULL, '2023-11-04 07:10:00', NULL, NULL),
(5, 'Thursday', '15:00:00', '17:00:00', 'approved', NULL, '2023-11-05 01:30:00', 77, 'Elementary Math'),
(6, 'Monday', '05:59:00', '07:08:00', 'rejected', '', '2025-06-02 08:59:52', 22, 'SPM Add Math'),
(7, 'Monday', '05:00:00', '07:00:00', 'pending', NULL, '2025-06-06 15:32:47', 79, 'Elementary Math');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `class_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `venue` varchar(100) NOT NULL,
  `lecturer` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `student_name`, `class_date`, `start_time`, `end_time`, `venue`, `lecturer`, `description`, `created_at`) VALUES
(1, 'Mathematics', 'Darrshan', '2025-04-11', '09:00:00', '10:30:00', 'Room 201', 'Prof. Johnson', 'Advanced Calculus: Differential Equations', '2025-04-06 12:29:30'),
(2, 'Physics', 'Darrshan', '2025-04-11', '14:00:00', '16:00:00', 'Physics Lab B', 'Dr. Martinez', 'Experimental Methods in Wave Mechanics', '2025-04-06 12:29:30'),
(3, 'Chemistry', 'Darrshan', '2025-04-18', '10:30:00', '11:30:00', 'Room 305', 'Dr. Chen', 'Organic Chemistry Assessment', '2025-04-06 12:29:30'),
(4, 'English', 'Darrshan', '2025-04-21', '11:00:00', '11:30:00', 'Room 102', 'Prof. Williams', 'Submit your comparative literature essays', '2025-04-06 12:29:30'),
(5, 'Biology', 'Darrshan', '2025-04-26', '13:30:00', '15:30:00', 'Biology Lab A', 'Dr. Thompson', 'Group presentation on cellular biology', '2025-04-06 12:29:30'),
(6, 'Computer Science', 'Darrshan', '2025-05-08', '20:43:31', '21:43:39', 'A-L4-102', 'Prof. Rohan', 'Testing ', '2025-04-06 12:44:48'),
(9, 'Issues in IT', 'Darrshan', '2025-05-13', '20:47:50', '21:47:56', 'Test Venue', 'Mr. Shan', 'Test', '2025-04-06 12:48:53'),
(10, 'Mathematics', 'John Doe', '2023-06-15', '09:00:00', '10:30:00', 'Room 101', 'Prof. Smith', 'Algebra basics', '2025-05-01 12:04:41'),
(11, 'Physics', 'Jane Smith', '2023-06-16', '11:00:00', '12:30:00', 'Lab 2', 'Dr. Johnson', 'Newtonian mechanics', '2025-05-01 12:04:41'),
(12, 'Chemistry', 'John Doe', '2023-06-17', '14:00:00', '15:30:00', 'Lab 1', 'Dr. Brown', 'Organic chemistry', '2025-05-01 12:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','instructor','student') NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`, `student_id`, `instructor_id`, `created_at`) VALUES
(1, 'weijie@example.com', '$2y$10$hJNNIjBDfTn8LHXt0xY2mu69rWPv1uJHZsss9ausqq6HEDhtiW8Sq', 'student', 1, NULL, '2025-05-04 13:39:40'),
(2, 'meiling@example.com', '$2y$10$PSjS6hmKvxnDlz8hVbcdxuHfLhAByHXcTTJxzVo0qOiOZ5W9Ekq62', 'student', 2, NULL, '2025-05-04 13:53:19'),
(3, 'kokweitan@example.com', '$2y$10$JP5jRhIHgz3YogglxhQcuuDDlfEjvcRmQcOrst5q3YnzR8oRAPLMC', 'instructor', NULL, 1, '2025-05-11 06:14:59'),
(4, 'admin@example.com', 'pass', 'admin', NULL, NULL, '2025-05-11 06:19:46'),
(18, 'admin2@example.com', '$2y$10$f7PYTFbC/AQCFqmhXpdy5uLXn09Etmcy.lIvGieWNob0VuD.Tgfj.', 'admin', NULL, NULL, '2025-06-12 11:17:11'),
(19, 'chiyuenhan@example.com', '$2y$10$yMnSeYJRQrRAuOFCz7SIXeSSJdZsvxuE4Hes1IpwQyPHOdgwQbDlG', 'instructor', NULL, 24, '2025-06-20 02:21:37'),
(40, 'tanss@example.com', '$2y$10$fBSzmzPJOZAmGAR4iv1t0erypjX1e3Z31GCV0/BNYpA5mxYhSuI3.', 'instructor', NULL, 26, '2025-06-21 05:39:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `idx_student_attendance` (`student_id`),
  ADD KEY `idx_instructor_attendance` (`instructor_id`),
  ADD KEY `idx_timetable_date` (`timetable_datetime`),
  ADD KEY `idx_course` (`course`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `course_fees`
--
ALTER TABLE `course_fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `fk_course_fee_course` (`course_id`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`instructor_id`),
  ADD KEY `idx_instructor_names` (`Last_Name`,`First_Name`);

--
-- Indexes for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD PRIMARY KEY (`instructor_course_id`),
  ADD UNIQUE KEY `instructor_id` (`instructor_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `instructor_timetable`
--
ALTER TABLE `instructor_timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_course_id` (`instructor_course_id`);

--
-- Indexes for table `instructor_timetable_requests`
--
ALTER TABLE `instructor_timetable_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_course_id` (`instructor_course_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `one_time_fees`
--
ALTER TABLE `one_time_fees`
  ADD PRIMARY KEY (`fee_name`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_student_payment` (`student_id`);

--
-- Indexes for table `primary_contact_number`
--
ALTER TABLE `primary_contact_number`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `secondary_contact_number`
--
ALTER TABLE `secondary_contact_number`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `idx_student_names` (`Last_Name`,`First_Name`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`student_course_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student_timetable`
--
ALTER TABLE `student_timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student_course` (`student_course_id`),
  ADD KEY `fk_student_timetable_instructor` (`instructor_id`),
  ADD KEY `fk_courses_course_id` (`course`);

--
-- Indexes for table `student_timetable_requests`
--
ALTER TABLE `student_timetable_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_course_id` (`student_course_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `course_fees`
--
ALTER TABLE `course_fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `instructor_course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `instructor_timetable`
--
ALTER TABLE `instructor_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `instructor_timetable_requests`
--
ALTER TABLE `instructor_timetable_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `primary_contact_number`
--
ALTER TABLE `primary_contact_number`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `secondary_contact_number`
--
ALTER TABLE `secondary_contact_number`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `student_course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `student_timetable`
--
ALTER TABLE `student_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `student_timetable_requests`
--
ALTER TABLE `student_timetable_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`),
  ADD CONSTRAINT `attendance_records_ibfk_3` FOREIGN KEY (`course`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `course_fees`
--
ALTER TABLE `course_fees`
  ADD CONSTRAINT `fk_course_fee_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD CONSTRAINT `instructor_courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`),
  ADD CONSTRAINT `instructor_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `instructor_timetable`
--
ALTER TABLE `instructor_timetable`
  ADD CONSTRAINT `instructor_timetable_ibfk_1` FOREIGN KEY (`instructor_course_id`) REFERENCES `instructor_courses` (`instructor_course_id`);

--
-- Constraints for table `instructor_timetable_requests`
--
ALTER TABLE `instructor_timetable_requests`
  ADD CONSTRAINT `instructor_timetable_requests_ibfk_1` FOREIGN KEY (`instructor_course_id`) REFERENCES `instructor_courses` (`instructor_course_id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `primary_contact_number`
--
ALTER TABLE `primary_contact_number`
  ADD CONSTRAINT `primary_contact_number_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `secondary_contact_number`
--
ALTER TABLE `secondary_contact_number`
  ADD CONSTRAINT `secondary_contact_number_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `student_timetable`
--
ALTER TABLE `student_timetable`
  ADD CONSTRAINT `fk_courses_course_id` FOREIGN KEY (`course`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `fk_student_course` FOREIGN KEY (`student_course_id`) REFERENCES `student_courses` (`student_course_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_student_timetable_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`);

--
-- Constraints for table `student_timetable_requests`
--
ALTER TABLE `student_timetable_requests`
  ADD CONSTRAINT `student_timetable_requests_ibfk_1` FOREIGN KEY (`student_course_id`) REFERENCES `student_courses` (`student_course_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
