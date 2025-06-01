-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 01, 2025 at 11:18 AM
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
  `status` enum('attended','missed','replacement_booked') DEFAULT 'attended',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `course` varchar(100) NOT NULL COMMENT 'Course name (matches student/instructor timetable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`record_id`, `student_id`, `instructor_id`, `timetable_datetime`, `attendance_datetime`, `hours_attended`, `hours_replacement`, `hours_remaining`, `status`, `created_at`, `updated_at`, `course`) VALUES
(1, 1, NULL, '2023-11-01 16:00:00', '2023-11-01 16:05:00', 2.0, 0.0, 18.0, 'attended', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'IGCSE Math Prep'),
(2, 6, NULL, '2023-10-16 10:00:00', '2023-10-16 10:10:00', 2.0, 0.0, 16.0, 'attended', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Economics Math'),
(3, 7, NULL, '2023-10-18 16:00:00', '2023-10-18 16:00:00', 1.5, 0.0, 14.5, 'attended', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Chemistry Math'),
(4, 9, NULL, '2023-10-19 13:00:00', '2023-10-19 13:05:00', 2.5, 0.0, 17.5, 'attended', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Advanced Physics'),
(5, 10, NULL, '2023-10-19 09:00:00', '2023-10-19 09:10:00', 1.5, 0.0, 18.5, 'attended', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Basic Science'),
(6, 16, NULL, '2023-10-25 15:00:00', NULL, 0.0, 0.0, 15.0, 'missed', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Economics Math'),
(7, 3, NULL, '2023-11-03 09:00:00', NULL, 0.0, 0.0, 20.0, 'missed', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Physics Basics'),
(8, 5, NULL, '2023-11-05 15:00:00', NULL, 0.0, 2.0, 18.0, 'replacement_booked', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Elementary Math'),
(9, 2, NULL, '2023-11-02 14:00:00', NULL, 0.0, 1.5, 19.5, 'missed', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'SPM Add Math'),
(10, 4, NULL, '2023-11-04 11:00:00', '2023-11-04 11:15:00', 1.0, 0.0, 19.0, 'attended', '2025-04-13 06:36:42', '2025-04-13 06:36:42', 'Biology Concepts');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `level`, `created_at`) VALUES
(1, 'IGCSE Math Prep', 'Preparation for IGCSE Mathematics exam', 'Intermediate', '2025-04-25 12:52:47'),
(2, 'SPM Add Math', 'Additional Mathematics for SPM level', 'Advanced', '2025-04-25 12:52:47'),
(3, 'Elementary Math', 'Basic mathematics for primary school', 'Beginner', '2025-04-25 12:52:47'),
(4, 'Physics Basics', 'Introduction to Physics concepts', 'Beginner', '2025-04-25 15:16:00'),
(5, 'Advanced Physics', 'Higher level Physics topics', 'Advanced', '2025-04-25 15:16:00');

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
  `Training_Status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`instructor_id`, `Last_Name`, `First_Name`, `Gender`, `DOB`, `Highest_Education`, `Remark`, `Training_Status`) VALUES
(1, 'Tan', 'Kok Wei', 1, '1985-03-15', 'Bachelor, Master', 'Experienced in Math', 'Completed'),
(2, 'Lim', 'Mei Lin', 0, '1990-07-22', 'Bachelor', 'Good communicator', 'In Progress'),
(3, 'Chong', 'Ahmad bin', 1, '1978-11-30', 'Master, PhD', 'Specializes in Science', 'Completed'),
(4, 'Ng', 'Siew Yee', 0, '1988-05-10', 'Bachelor', 'Needs more training', 'In Progress'),
(5, 'Lee', 'Chin Fatt', 1, '1982-09-18', 'Bachelor, Master', 'Excellent in Physics', 'Completed'),
(6, 'Wong', 'Li Ying', 0, '1995-01-25', 'Bachelor', 'New instructor', 'In Progress'),
(7, 'Chew', 'Teck Guan', 1, '1980-04-12', 'Master', 'Strong in Chemistry', 'Completed'),
(8, 'Yap', 'Sook Fun', 0, '1987-08-05', 'Bachelor, Master', 'Dedicated teacher', 'Completed'),
(9, 'Khoo', 'Wei Ming', 1, '1992-12-20', 'Bachelor', 'Needs to improve', 'In Progress'),
(10, 'Chan', 'Mei Chen', 0, '1983-06-14', 'Master, PhD', 'Expert in English', 'Completed'),
(11, 'Goh', 'Kok Seng', 1, '1975-02-28', 'Bachelor, Master', 'Veteran instructor', 'Completed'),
(12, 'Lau', 'Siew Har', 0, '1990-10-09', 'Bachelor', 'Good with students', 'In Progress'),
(13, 'Teo', 'Chin Wei', 1, '1986-03-03', 'Master', 'Specializes in Geography', 'Completed'),
(14, 'Ho', 'Li Mei', 0, '1993-07-17', 'Bachelor', 'Enthusiastic', 'In Progress'),
(15, 'Ong', 'Teck Soon', 1, '1981-11-11', 'Bachelor, Master', 'Strong in History', 'Completed'),
(16, 'Low', 'Siew Ling', 0, '1989-04-25', 'Master', 'Great at Biology', 'Completed'),
(17, 'Soh', 'Wei Keong', 1, '1984-08-30', 'Bachelor', 'Needs more experience', 'In Progress'),
(18, 'Chua', 'Mei Yin', 0, '1991-02-14', 'Bachelor, Master', 'Excellent in Literature', 'Completed'),
(19, 'Pang', 'Kok Wai', 1, '1979-06-20', 'Master, PhD', 'Expert in Add Math', 'Completed'),
(20, 'Yeoh', 'Siew Ching', 0, '1985-12-05', 'Bachelor', 'Promising instructor', 'In Progress');

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
(2, 1, 2, '2023-09-01', 'active'),
(3, 2, 3, '2023-09-01', 'active'),
(4, 3, 4, '2023-09-01', 'active'),
(5, 4, 5, '2023-09-01', 'active');

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
(1, 'Monday', '09:00:00', '11:00:00', '2023-10-15 06:30:00', 'active', 1, ''),
(2, 'Wednesday', '13:00:00', '15:00:00', '2023-10-16 02:15:00', 'active', 1, ''),
(3, 'Friday', '10:00:00', '12:00:00', '2023-10-17 03:20:00', 'active', NULL, ''),
(4, 'Tuesday', '14:00:00', '16:00:00', '2023-10-18 01:45:00', 'active', NULL, ''),
(5, 'Thursday', '11:00:00', '13:00:00', '2023-10-19 08:10:00', 'active', NULL, '');

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
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL COMMENT 'Links to students table',
  `payment_method` enum('credit_card','cash','bank_transfer') NOT NULL,
  `payment_mode` enum('monthly','quarterly','semi_annually') NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL COMMENT 'E.g., 1500.00',
  `deposit_status` enum('yes','no') DEFAULT 'no',
  `payment_status` enum('paid','unpaid','pending') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `student_id`, `payment_method`, `payment_mode`, `payment_amount`, `deposit_status`, `payment_status`, `payment_date`) VALUES
(1, 1, 'credit_card', 'monthly', 500.00, 'yes', 'paid', '2025-04-13 06:53:06'),
(2, 5, 'cash', 'quarterly', 1200.00, 'yes', 'paid', '2025-04-13 06:53:06'),
(3, 6, 'bank_transfer', 'semi_annually', 2000.00, 'no', 'unpaid', '2025-04-13 06:53:06'),
(4, 3, 'credit_card', 'monthly', 500.00, 'yes', 'pending', '2025-04-13 06:53:06'),
(5, 12, 'cash', 'quarterly', 1200.00, 'no', 'unpaid', '2025-04-13 06:53:06'),
(6, 7, 'bank_transfer', 'semi_annually', 2000.00, 'yes', 'paid', '2025-04-13 06:53:06'),
(7, 9, 'credit_card', 'monthly', 500.00, 'no', 'unpaid', '2025-04-13 06:53:06'),
(8, 16, 'cash', 'quarterly', 1200.00, 'yes', 'pending', '2025-04-13 06:53:06'),
(9, 4, 'bank_transfer', 'semi_annually', 2000.00, 'yes', 'paid', '2025-04-13 06:53:06'),
(10, 20, 'credit_card', 'monthly', 500.00, 'no', 'unpaid', '2025-04-13 06:53:06');

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
  `Mathology_Level` text DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `status` enum('active','inactive','graduated') DEFAULT 'active',
  `How_Did_You_Heard_About_Us` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `Last_Name`, `First_Name`, `Gender`, `DOB`, `School_Syllabus`, `School_Intake`, `Current_School_Grade`, `School`, `Mathology_Level`, `enrollment_date`, `status`, `How_Did_You_Heard_About_Us`) VALUES
(1, 'Tan', 'Wei Jie', 1, '2008-05-12', 'IGCSE, Math, Science', '1', 'Year 10', 'Kingsway International School', 'Intermediate', NULL, 'active', NULL),
(2, 'Lim', 'Mei Ling', 0, '2007-09-23', 'SPM, Math, English', '1', 'Form 4', 'SMK Damansara Jaya', 'Advanced', NULL, 'active', NULL),
(3, 'Chong', 'Kai Wen', 1, '2009-03-15', 'IGCSE, Physics, Chemistry', '0', 'Year 9', 'Sri KDU International School', 'Beginner', NULL, 'active', NULL),
(4, 'Ng', 'Xiu Mei', 0, '2006-11-07', 'SPM, Biology, History', '1', 'Form 5', 'SMK Taman Connaught', 'Intermediate', NULL, 'active', NULL),
(5, 'Lee', 'Jun Hao', 1, '2010-01-19', 'KSSR, Math, Malay', '1', 'Standard 6', 'SJK(C) Chung Hwa', 'Beginner', NULL, 'active', NULL),
(6, 'Wong', 'Siew Ling', 0, '2008-07-30', 'IGCSE, Math, Economics', '1', 'Year 11', 'Garden International School', 'Advanced', NULL, 'active', NULL),
(7, 'Chew', 'Boon Keat', 1, '2007-12-04', 'SPM, Chemistry, Add Math', '1', 'Form 4', 'SMK Sri Permata', 'Intermediate', NULL, 'active', NULL),
(8, 'Yap', 'Hui Xin', 0, '2009-06-18', 'IGCSE, English, Geography', '0', 'Year 10', 'Cempaka International School', 'Beginner', NULL, 'active', NULL),
(9, 'Khoo', 'Zhi Wei', 1, '2006-08-22', 'SPM, Physics, Math', '1', 'Form 5', 'SMK Kepong Baru', 'Advanced', NULL, 'active', NULL),
(10, 'Chan', 'Yi Ting', 0, '2011-02-14', 'KSSR, Science, English', '1', 'Standard 5', 'SJK(C) Lick Hung', 'Beginner', NULL, 'active', NULL),
(11, 'Goh', 'Jia Hao', 1, '2008-04-09', 'IGCSE, Math, Business', '1', 'Year 10', 'Sunway International School', 'Intermediate', NULL, 'active', NULL),
(12, 'Lau', 'Xin Yi', 0, '2007-10-27', 'SPM, Biology, English', '1', 'Form 4', 'SMK Bukit Jalil', 'Advanced', NULL, 'active', NULL),
(13, 'Teo', 'Ming Zhe', 1, '2009-09-11', 'IGCSE, Chemistry, Math', '0', 'Year 9', 'Taylor’s International School', 'Beginner', NULL, 'active', NULL),
(14, 'Ho', 'Pei Ling', 0, '2006-03-25', 'SPM, Add Math, Physics', '1', 'Form 5', 'SMK Seri Bintang Utara', 'Intermediate', NULL, 'active', NULL),
(15, 'Ong', 'Wei Kang', 1, '2010-05-03', 'KSSR, Math, Malay', '1', 'Standard 6', 'SJK(C) Yuk Chai', 'Beginner', NULL, 'active', NULL),
(16, 'Low', 'Shu Fen', 0, '2008-12-16', 'IGCSE, Economics, Math', '1', 'Year 11', 'British International School', 'Advanced', NULL, 'active', NULL),
(17, 'Soh', 'Jian Wei', 1, '2007-06-29', 'SPM, Chemistry, Math', '1', 'Form 4', 'SMK Taman Desa', 'Intermediate', NULL, 'active', NULL),
(18, 'Chua', 'Mei Qi', 0, '2009-08-08', 'IGCSE, English, Science', '0', 'Year 10', 'Nexus International School', 'Beginner', NULL, 'active', NULL),
(19, 'Pang', 'Wei Jun', 1, '2006-10-13', 'SPM, Physics, Add Math', '1', 'Form 5', 'SMK Seafield', 'Advanced', NULL, 'active', NULL),
(20, 'Yeoh', 'Xin Ru', 0, '2011-07-21', 'KSSR, Math, English', '1', 'Standard 5', 'SJK(C) Kuen Cheng', 'Beginner', NULL, 'active', NULL),
(21, 'Sim', 'Kai Jie', 1, '2008-02-17', 'IGCSE, Math, Physics', '1', 'Year 10', 'HELP International School', 'Intermediate', NULL, 'active', NULL),
(22, 'Kong', 'Hui Min', 0, '2007-11-05', 'SPM, Biology, Chemistry', '1', 'Form 4', 'SMK Bandar Utama', 'Advanced', NULL, 'active', NULL),
(23, 'Foo', 'Jun Wei', 1, '2009-04-28', 'IGCSE, Geography, Math', '0', 'Year 9', 'St. John’s International School', 'Beginner', NULL, 'active', NULL),
(24, 'Liew', 'Xin Yi', 0, '2006-12-09', 'SPM, Math, English', '1', 'Form 5', 'SMK Taman SEA', 'Intermediate', NULL, 'active', NULL),
(25, 'Tay', 'Zhi Hao', 1, '2010-03-14', 'KSSR, Science, Malay', '1', 'Standard 6', 'SJK(C) Han Chiang', 'Beginner', NULL, 'active', NULL),
(26, 'Koh', 'Pei Wen', 0, '2008-09-02', 'IGCSE, Economics, Math', '1', 'Year 11', 'Uplands International School', 'Advanced', NULL, 'active', NULL),
(27, 'Chin', 'Wei Xiang', 1, '2007-08-19', 'SPM, Physics, Add Math', '1', 'Form 4', 'SMK Subang Utama', 'Intermediate', NULL, 'active', NULL),
(28, 'Toh', 'Jia Yi', 0, '2009-10-25', 'IGCSE, English, Chemistry', '0', 'Year 10', 'Alice Smith School', 'Beginner', NULL, 'active', NULL),
(29, 'Heng', 'Jun Kai', 1, '2006-05-31', 'SPM, Math, Biology', '1', 'Form 5', 'SMK Damansara Utama', 'Advanced', NULL, 'active', NULL),
(30, 'Poon', 'Xin Tong', 0, '2011-01-08', 'KSSR, Math, English', '1', 'Standard 5', 'SJK(C) Puay Chai', 'Beginner', NULL, 'active', NULL),
(31, 'Kwan', 'Wei Lun', 1, '2008-06-14', 'IGCSE, Math, Physics', '1', 'Year 10', 'Kolej Tuanku Ja’afar', 'Intermediate', NULL, 'active', NULL),
(32, 'Liang', 'Shu Qi', 0, '2007-03-22', 'SPM, Chemistry, English', '1', 'Form 4', 'SMK Sultan Ismail', 'Advanced', NULL, 'active', NULL),
(33, 'Yeap', 'Zhi Cong', 1, '2009-07-16', 'IGCSE, Geography, Math', '0', 'Year 9', 'Marlborough College Malaysia', 'Beginner', NULL, 'active', NULL),
(34, 'Ooi', 'Hui Shan', 0, '2006-09-28', 'SPM, Add Math, Physics', '1', 'Form 5', 'SMK Convent Green Lane', 'Intermediate', NULL, 'active', NULL),
(35, 'Tham', 'Wei Jie', 1, '2010-11-11', 'KSSR, Science, Malay', '1', 'Standard 6', 'SJK(C) Jalan Davidson', 'Beginner', NULL, 'active', NULL),
(36, 'See', 'Xin Wei', 0, '2008-01-30', 'IGCSE, Math, Economics', '1', 'Year 11', 'Fairview International School', 'Advanced', NULL, 'active', NULL),
(37, 'Phang', 'Jun Wei', 1, '2007-05-07', 'SPM, Physics, Math', '1', 'Form 4', 'SMK Seri Hartamas', 'Intermediate', NULL, 'active', NULL),
(38, 'Chia', 'Mei Xin', 0, '2009-12-20', 'IGCSE, English, Science', '0', 'Year 10', 'IGB International School', 'Beginner', NULL, 'active', NULL),
(39, 'Tiew', 'Zhi Yang', 1, '2006-04-15', 'SPM, Add Math, Chemistry', '1', 'Form 5', 'SMK Aminuddin Baki', 'Advanced', NULL, 'active', NULL),
(40, 'Law', 'Jia Wen', 0, '2011-06-23', 'KSSR, Math, English', '1', 'Standard 5', 'SJK(C) Chung Kwok', 'Beginner', NULL, 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `student_course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('active','completed','dropped') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`student_course_id`, `student_id`, `course_id`, `enrollment_date`, `status`) VALUES
(1, 10, 1, '2025-04-25', 'active'),
(2, 7, 1, '2025-04-25', 'active'),
(3, 38, 1, '2025-04-25', 'active'),
(4, 27, 1, '2025-04-25', 'active'),
(5, 3, 1, '2025-04-25', 'active'),
(6, 18, 1, '2025-04-25', 'active'),
(7, 23, 1, '2025-04-25', 'active'),
(8, 11, 1, '2025-04-25', 'active'),
(9, 29, 1, '2025-04-25', 'active'),
(10, 14, 1, '2025-04-25', 'active'),
(11, 9, 1, '2025-04-25', 'active'),
(12, 26, 1, '2025-04-25', 'active'),
(13, 22, 1, '2025-04-25', 'active'),
(14, 31, 1, '2025-04-25', 'active'),
(15, 12, 1, '2025-04-25', 'active'),
(16, 40, 1, '2025-04-25', 'active'),
(17, 5, 1, '2025-04-25', 'active'),
(18, 32, 1, '2025-04-25', 'active'),
(19, 24, 1, '2025-04-25', 'active'),
(20, 2, 1, '2025-04-25', 'active'),
(21, 16, 1, '2025-04-25', 'active'),
(22, 4, 1, '2025-04-25', 'active'),
(23, 15, 1, '2025-04-25', 'active'),
(24, 34, 1, '2025-04-25', 'active'),
(25, 19, 1, '2025-04-25', 'active'),
(26, 37, 1, '2025-04-25', 'active'),
(27, 30, 1, '2025-04-25', 'active'),
(28, 36, 1, '2025-04-25', 'active'),
(29, 21, 1, '2025-04-25', 'active'),
(30, 17, 1, '2025-04-25', 'active'),
(31, 1, 1, '2025-04-25', 'active'),
(32, 25, 1, '2025-04-25', 'active'),
(33, 13, 1, '2025-04-25', 'active'),
(34, 35, 1, '2025-04-25', 'active'),
(35, 39, 1, '2025-04-25', 'active'),
(36, 28, 1, '2025-04-25', 'active'),
(37, 6, 1, '2025-04-25', 'active'),
(38, 8, 1, '2025-04-25', 'active'),
(39, 33, 1, '2025-04-25', 'active'),
(40, 20, 1, '2025-04-25', 'active'),
(64, 10, 2, '2025-04-25', 'active'),
(65, 7, 2, '2025-04-25', 'active'),
(66, 27, 1, '2025-04-25', 'active'),
(67, 3, 1, '2025-04-25', 'active'),
(68, 18, 1, '2025-04-25', 'active'),
(69, 23, 3, '2025-04-25', 'active'),
(70, 11, 3, '2025-04-25', 'active'),
(71, 29, 3, '2025-04-25', 'active'),
(72, 14, 3, '2025-04-25', 'active'),
(73, 9, 1, '2025-04-25', 'active'),
(74, 26, 3, '2025-04-25', 'active'),
(75, 22, 2, '2025-04-25', 'active'),
(76, 12, 1, '2025-04-25', 'active'),
(77, 5, 3, '2025-04-25', 'active'),
(78, 24, 1, '2025-04-25', 'active'),
(79, 2, 3, '2025-04-25', 'active'),
(80, 16, 2, '2025-04-25', 'active'),
(81, 4, 2, '2025-04-25', 'active'),
(82, 15, 1, '2025-04-25', 'active'),
(83, 19, 2, '2025-04-25', 'active'),
(84, 30, 1, '2025-04-25', 'active'),
(85, 21, 1, '2025-04-25', 'active'),
(86, 17, 3, '2025-04-25', 'active'),
(87, 1, 2, '2025-04-25', 'active'),
(88, 25, 2, '2025-04-25', 'active'),
(89, 13, 2, '2025-04-25', 'active'),
(90, 28, 2, '2025-04-25', 'active'),
(91, 6, 1, '2025-04-25', 'active'),
(92, 8, 3, '2025-04-25', 'active'),
(93, 20, 3, '2025-04-25', 'active'),
(95, 1, 1, '2023-09-01', 'active'),
(96, 1, 2, '2023-09-01', 'active'),
(97, 2, 3, '2023-09-01', 'active'),
(98, 3, 4, '2023-09-01', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `student_timetable`
--

CREATE TABLE `student_timetable` (
  `id` int(11) NOT NULL,
  `course` varchar(100) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','cancelled') DEFAULT 'active',
  `student_course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_timetable`
--

INSERT INTO `student_timetable` (`id`, `course`, `day`, `start_time`, `end_time`, `approved_at`, `status`, `student_course_id`) VALUES
(1, 'IGCSE Math Prep', 'Monday', '10:00:00', '12:00:00', '2023-10-15 06:25:00', 'active', 37),
(2, 'IGCSE Math Prep', 'Wednesday', '16:00:00', '18:00:00', '2023-10-16 03:40:00', 'active', 2),
(3, 'IGCSE Math Prep', 'Friday', '14:00:00', '16:00:00', '2023-10-17 02:55:00', 'active', 38),
(4, 'IGCSE Math Prep', 'Tuesday', '13:00:00', '15:00:00', '2023-10-18 00:20:00', 'active', 11),
(5, 'IGCSE Math Prep', 'Thursday', '09:00:00', '11:00:00', '2023-10-19 07:30:00', 'active', 1),
(6, 'IGCSE Math Prep', 'Monday', '16:00:00', '18:00:00', '2025-04-30 14:01:19', 'active', 31);

--
-- Triggers `student_timetable`
--
DELIMITER $$
CREATE TRIGGER `set_student_course_name` BEFORE INSERT ON `student_timetable` FOR EACH ROW BEGIN
    DECLARE course_name VARCHAR(100);
    SELECT c.course_name INTO course_name 
    FROM courses c
    JOIN student_courses sc ON c.course_id = sc.course_id
    WHERE sc.student_course_id = NEW.student_course_id;
    SET NEW.course = course_name;
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
(5, 'Thursday', '15:00:00', '17:00:00', 'pending', NULL, '2023-11-05 01:30:00', 77, 'Elementary Math');

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
(1, 'weijie@example.com', 'pass', 'student', 1, NULL, '2025-05-04 13:39:40'),
(2, 'meiling@example.com', 'pass', 'student', 2, NULL, '2025-05-04 13:53:19'),
(3, 'kokweitan@example.com', 'pass', 'instructor', NULL, 1, '2025-05-11 06:14:59'),
(4, 'darrshan@example.com', 'pass', 'admin', NULL, NULL, '2025-05-11 06:19:46');

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
  ADD KEY `idx_timetable_date` (`timetable_datetime`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

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
  ADD KEY `fk_student_course` (`student_course_id`);

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
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `instructor_course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `instructor_timetable`
--
ALTER TABLE `instructor_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `instructor_timetable_requests`
--
ALTER TABLE `instructor_timetable_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `primary_contact_number`
--
ALTER TABLE `primary_contact_number`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `secondary_contact_number`
--
ALTER TABLE `secondary_contact_number`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `student_course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `student_timetable`
--
ALTER TABLE `student_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_timetable_requests`
--
ALTER TABLE `student_timetable_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`);

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
  ADD CONSTRAINT `fk_student_course` FOREIGN KEY (`student_course_id`) REFERENCES `student_courses` (`student_course_id`);

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
