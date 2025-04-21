-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3310
-- Generation Time: Apr 13, 2025 at 01:59 PM
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
-- Database: `mathology`
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
) ;

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
(2, 'Lim', 'Mei Ling', 0, '1990-07-22', 'Bachelor', 'Good communicator', 'In Progress'),
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
-- Table structure for table `instructor_timetable`
--

CREATE TABLE `instructor_timetable` (
  `id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_timetable`
--

INSERT INTO `instructor_timetable` (`id`, `last_name`, `first_name`, `course`, `day`, `start_time`, `end_time`, `approved_at`) VALUES
(1, 'Chew', 'Teck Guan', 'Chemistry Basics', 'Monday', '09:00:00', '11:00:00', '2023-10-15 06:30:00'),
(2, 'Yap', 'Sook Fun', 'Probability', 'Wednesday', '13:00:00', '15:00:00', '2023-10-16 02:15:00'),
(3, 'Khoo', 'Wei Ming', 'Statistics', 'Friday', '10:00:00', '12:00:00', '2023-10-17 03:20:00'),
(4, 'Chan', 'Mei Chen', 'English for Math', 'Tuesday', '14:00:00', '16:00:00', '2023-10-18 01:45:00'),
(5, 'Goh', 'Kok Seng', 'Linear Algebra', 'Thursday', '11:00:00', '13:00:00', '2023-10-19 08:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_timetable_requests`
--

CREATE TABLE `instructor_timetable_requests` (
  `id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_timetable_requests`
--

INSERT INTO `instructor_timetable_requests` (`id`, `last_name`, `first_name`, `course`, `day`, `start_time`, `end_time`, `status`, `rejection_reason`, `requested_at`) VALUES
(1, 'Tan', 'Kok Wei', 'Advanced Calculus', 'Monday', '14:00:00', '16:00:00', 'pending', NULL, '2023-11-01 01:15:00'),
(2, 'Lim', 'Mei Ling', 'Algebra Basics', 'Wednesday', '10:00:00', '12:00:00', 'pending', NULL, '2023-11-02 02:30:00'),
(3, 'Chong', 'Ahmad bin', 'Physics Fundamentals', 'Friday', '13:00:00', '15:00:00', 'pending', NULL, '2023-11-03 03:45:00'),
(4, 'Ng', 'Siew Yee', 'Geometry', 'Tuesday', '09:00:00', '11:00:00', 'pending', NULL, '2023-11-04 06:20:00'),
(5, 'Lee', 'Chin Fatt', 'Trigonometry', 'Thursday', '16:00:00', '18:00:00', 'pending', NULL, '2023-11-05 00:50:00');

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
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Relationship_with_Student` varchar(50) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Postcode` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `primary_contact_number`
--

INSERT INTO `primary_contact_number` (`Last_Name`, `First_Name`, `Relationship_with_Student`, `Phone`, `Email`, `Address`, `Postcode`) VALUES
('Tan', 'Kok Wei', 'Parent', '0123456789', 'kokwei.tan@gmail.com', '12, Jalan Bukit, Taman Sentosa', 52000),
('Lim', 'Siew Mei', 'Guardian', '0167891234', 'siewmei.lim@yahoo.com', '45, Lorong Bunga, Taman Indah', 47300),
('Chong', 'Ahmad bin', 'Parent', '0198765432', 'ahmad.chong@hotmail.com', '78, Jalan Merdeka, Bandar Baru', 58200),
('Ng', 'Lay Har', 'Parent', '0134567890', 'layhar.ng@gmail.com', '23, Jalan SS2/24, Petaling Jaya', 47301),
('Lee', 'Chee Meng', 'Guardian', '0171234567', 'cheemeng.lee@outlook.com', '56, Lorong Damai, Taman Desa', 58100),
('Wong', 'Mei Fong', 'Parent', '0112345678', 'meifong.wong@gmail.com', '89, Jalan Puchong, Taman Kinrara', 47100),
('Chew', 'Boon Seng', 'Parent', '0145678901', 'boonseng.chew@yahoo.com', '34, Jalan Ipoh, Sentul', 51000),
('Yap', 'Hui Ling', 'Guardian', '0189012345', 'huiling.yap@gmail.com', '67, Lorong Gasing, Taman Megah', 47400),
('Khoo', 'Teck Guan', 'Parent', '0129876543', 'teckguan.khoo@live.com', '90, Jalan Klang, Taman Sri', 52100),
('Chan', 'Sook Yee', 'Parent', '0191234567', 'sookyee.chan@gmail.com', '15, Jalan Ampang, Kuala Lumpur', 50450),
('Goh', 'Wei Liang', 'Parent', '0137894561', 'weiliang.goh@gmail.com', '22, Jalan Setia, Taman Setiawangsa', 54200),
('Lau', 'Poh Ling', 'Guardian', '0162345678', 'pohling.lau@yahoo.com', '33, Lorong Kenari, Taman Melawati', 53100),
('Teo', 'Chin Hock', 'Parent', '0178901234', 'chinhock.teo@outlook.com', '44, Jalan Perak, Taman Tun', 60000),
('Ho', 'Mei Yin', 'Parent', '0113456789', 'meiyin.ho@gmail.com', '55, Jalan SS3/12, Kelana Jaya', 47300),
('Ong', 'Kok Leong', 'Guardian', '0149012345', 'kokleong.ong@live.com', '66, Lorong Cempaka, Taman Cheras', 56100),
('Low', 'Siew Lan', 'Parent', '0124567890', 'siewlan.low@gmail.com', '77, Jalan Bukit Bintang, KLCC', 55100),
('Soh', 'Teck Ming', 'Parent', '0195678901', 'teckming.soh@yahoo.com', '88, Jalan Kuchai Lama, Taman Kuchai', 58200),
('Chua', 'Li Ping', 'Guardian', '0131234567', 'liping.chua@gmail.com', '99, Lorong Seri, Taman Seri Bahtera', 51200),
('Pang', 'Wei Keong', 'Parent', '0167894561', 'weikeong.pang@outlook.com', '11, Jalan Damansara, Damansara Heights', 50490),
('Yeoh', 'Siew Ching', 'Parent', '0172345678', 'siewching.yeoh@gmail.com', '22, Jalan Bangsar, Bangsar Baru', 59100),
('Sim', 'Kok Fatt', 'Parent', '0118901234', 'kokfatt.sim@live.com', '33, Lorong Utama, Taman Utama', 47150),
('Kong', 'Mei Ling', 'Guardian', '0143456789', 'meiling.kong@yahoo.com', '44, Jalan SS15/4, Subang Jaya', 47500),
('Foo', 'Chin Wei', 'Parent', '0129012345', 'chinwei.foo@gmail.com', '55, Jalan PJS 11/9, Bandar Sunway', 46150),
('Liew', 'Sook Fun', 'Parent', '0194567890', 'sookfun.liew@outlook.com', '66, Lorong Indah, Taman Indah Permai', 52200),
('Tay', 'Kok Seng', 'Guardian', '0135678901', 'kokseng.tay@gmail.com', '77, Jalan Klang Lama, Taman Overseas', 58200),
('Koh', 'Mei Yee', 'Parent', '0161234567', 'meiyee.koh@yahoo.com', '88, Jalan USJ 2/3, USJ 2', 47600),
('Chin', 'Wei Hong', 'Parent', '0177894561', 'weihong.chin@live.com', '99, Lorong Damai 2, Taman Damai', 56000),
('Toh', 'Siew Ling', 'Guardian', '0112345678', 'siewling.toh@gmail.com', '11, Jalan SS22/41, Petaling Jaya', 47400),
('Heng', 'Kok Wai', 'Parent', '0148901234', 'kokwai.heng@outlook.com', '22, Jalan Bukit, Taman Bukit Mewah', 52100),
('Poon', 'Li Mei', 'Parent', '0123456789', 'limei.poon@yahoo.com', '33, Lorong Seri Kembangan, Seri Kembangan', 43300),
('Kwan', 'Teck Soon', 'Parent', '0199012345', 'tecksoon.kwan@gmail.com', '44, Jalan Puchong, Taman Puchong Utama', 47100),
('Liang', 'Siew Har', 'Guardian', '0134567890', 'siewhar.liang@live.com', '55, Jalan SS3/29, Kelana Jaya', 47300),
('Yeap', 'Chin Fatt', 'Parent', '0161234567', 'chinfatt.yeap@gmail.com', '66, Lorong Gasing 2, Taman Megah', 47400),
('Ooi', 'Mei Chen', 'Parent', '0177894561', 'meichen.ooi@outlook.com', '77, Jalan Ampang Hilir, Desa Pandan', 55100),
('Tham', 'Kok Leong', 'Guardian', '0112345678', 'kokleong.tham@yahoo.com', '88, Jalan SS15/5, Subang Jaya', 47500),
('See', 'Siew Yee', 'Parent', '0148901234', 'siewyee.see@gmail.com', '99, Lorong Indah 3, Taman Indah Permai', 52200),
('Phang', 'Wei Ming', 'Parent', '0123456789', 'weiming.phang@live.com', '11, Jalan Bukit Bintang, KLCC', 55100),
('Chia', 'Li Ying', 'Guardian', '0199012345', 'liying.chia@gmail.com', '22, Jalan PJS 11/7, Bandar Sunway', 46150),
('Tiew', 'Kok Seng', 'Parent', '0134567890', 'kokseng.tiew@outlook.com', '33, Lorong Seri, Taman Seri Bahtera', 51200),
('Law', 'Mei Ling', 'Parent', '0161234567', 'meiling.law@yahoo.com', '44, Jalan SS22/19, Petaling Jaya', 47400);

-- --------------------------------------------------------

--
-- Table structure for table `secondary_contact_number`
--

CREATE TABLE `secondary_contact_number` (
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Relationship_with_Student` varchar(50) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secondary_contact_number`
--

INSERT INTO `secondary_contact_number` (`Last_Name`, `First_Name`, `Relationship_with_Student`, `Phone`) VALUES
('Tan', 'Wei Ling', 'Sibling', '0129876543'),
('Lim', 'Kok Fatt', 'Parent', '0165432109'),
('Chong', 'Mei Yee', 'Guardian', '0198761234'),
('Ng', 'Teck Wei', 'Sibling', '0134561234'),
('Lee', 'Siew Lan', 'Parent', '0177894561'),
('Wong', 'Chin Hock', 'Guardian', '0112347890'),
('Chew', 'Li Ping', 'Sibling', '0145671234'),
('Yap', 'Kok Seng', 'Parent', '0189014567'),
('Khoo', 'Mei Chen', 'Guardian', '0123459876'),
('Chan', 'Wei Hong', 'Sibling', '0191237890'),
('Goh', 'Siew Har', 'Parent', '0137891234'),
('Lau', 'Teck Guan', 'Guardian', '0162347890'),
('Teo', 'Li Ying', 'Sibling', '0178904561'),
('Ho', 'Kok Wai', 'Parent', '0113451234'),
('Ong', 'Mei Ling', 'Guardian', '0149017890'),
('Low', 'Chin Wei', 'Sibling', '0124561234'),
('Soh', 'Siew Yee', 'Parent', '0195674561'),
('Chua', 'Wei Ming', 'Guardian', '0131237890'),
('Pang', 'Li Mei', 'Sibling', '0167891234'),
('Yeoh', 'Teck Soon', 'Parent', '0172344561'),
('Sim', 'Siew Ching', 'Guardian', '0118907890'),
('Kong', 'Kok Leong', 'Sibling', '0143451234'),
('Foo', 'Mei Yin', 'Parent', '0129014567'),
('Liew', 'Wei Keong', 'Guardian', '0194567890'),
('Tay', 'Sook Fun', 'Sibling', '0135671234'),
('Koh', 'Chin Fatt', 'Parent', '0161237890'),
('Chin', 'Mei Yee', 'Guardian', '0177891234'),
('Toh', 'Kok Seng', 'Sibling', '0112344561'),
('Heng', 'Siew Ling', 'Parent', '0148907890'),
('Poon', 'Wei Hong', 'Guardian', '0123451234'),
('Kwan', 'Siew Har', 'Sibling', '0199014567'),
('Liang', 'Teck Guan', 'Parent', '0134567890'),
('Yeap', 'Li Ying', 'Guardian', '0161234567'),
('Ooi', 'Kok Wai', 'Sibling', '0177897890'),
('Tham', 'Mei Ling', 'Parent', '0112341234'),
('See', 'Chin Wei', 'Guardian', '0148904561'),
('Phang', 'Siew Yee', 'Sibling', '0123457890'),
('Chia', 'Wei Ming', 'Parent', '0199011234'),
('Tiew', 'Li Mei', 'Guardian', '0134564561'),
('Law', 'Teck Soon', 'Sibling', '0161237890');

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
  `School_Intake` tinyint(1) DEFAULT NULL,
  `Current_School_Grade` text DEFAULT NULL,
  `School` varchar(100) DEFAULT NULL,
  `Mathology_Level` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `Last_Name`, `First_Name`, `Gender`, `DOB`, `School_Syllabus`, `School_Intake`, `Current_School_Grade`, `School`, `Mathology_Level`) VALUES
(1, 'Tan', 'Wei Jie', 1, '2008-05-12', 'IGCSE, Math, Science', 1, 'Year 10', 'Kingsway International School', 'Intermediate'),
(2, 'Lim', 'Mei Ling', 0, '2007-09-23', 'SPM, Math, English', 1, 'Form 4', 'SMK Damansara Jaya', 'Advanced'),
(3, 'Chong', 'Kai Wen', 1, '2009-03-15', 'IGCSE, Physics, Chemistry', 0, 'Year 9', 'Sri KDU International School', 'Beginner'),
(4, 'Ng', 'Xiu Mei', 0, '2006-11-07', 'SPM, Biology, History', 1, 'Form 5', 'SMK Taman Connaught', 'Intermediate'),
(5, 'Lee', 'Jun Hao', 1, '2010-01-19', 'KSSR, Math, Malay', 1, 'Standard 6', 'SJK(C) Chung Hwa', 'Beginner'),
(6, 'Wong', 'Siew Ling', 0, '2008-07-30', 'IGCSE, Math, Economics', 1, 'Year 11', 'Garden International School', 'Advanced'),
(7, 'Chew', 'Boon Keat', 1, '2007-12-04', 'SPM, Chemistry, Add Math', 1, 'Form 4', 'SMK Sri Permata', 'Intermediate'),
(8, 'Yap', 'Hui Xin', 0, '2009-06-18', 'IGCSE, English, Geography', 0, 'Year 10', 'Cempaka International School', 'Beginner'),
(9, 'Khoo', 'Zhi Wei', 1, '2006-08-22', 'SPM, Physics, Math', 1, 'Form 5', 'SMK Kepong Baru', 'Advanced'),
(10, 'Chan', 'Yi Ting', 0, '2011-02-14', 'KSSR, Science, English', 1, 'Standard 5', 'SJK(C) Lick Hung', 'Beginner'),
(11, 'Goh', 'Jia Hao', 1, '2008-04-09', 'IGCSE, Math, Business', 1, 'Year 10', 'Sunway International School', 'Intermediate'),
(12, 'Lau', 'Xin Yi', 0, '2007-10-27', 'SPM, Biology, English', 1, 'Form 4', 'SMK Bukit Jalil', 'Advanced'),
(13, 'Teo', 'Ming Zhe', 1, '2009-09-11', 'IGCSE, Chemistry, Math', 0, 'Year 9', 'Taylor’s International School', 'Beginner'),
(14, 'Ho', 'Pei Ling', 0, '2006-03-25', 'SPM, Add Math, Physics', 1, 'Form 5', 'SMK Seri Bintang Utara', 'Intermediate'),
(15, 'Ong', 'Wei Kang', 1, '2010-05-03', 'KSSR, Math, Malay', 1, 'Standard 6', 'SJK(C) Yuk Chai', 'Beginner'),
(16, 'Low', 'Shu Fen', 0, '2008-12-16', 'IGCSE, Economics, Math', 1, 'Year 11', 'British International School', 'Advanced'),
(17, 'Soh', 'Jian Wei', 1, '2007-06-29', 'SPM, Chemistry, Math', 1, 'Form 4', 'SMK Taman Desa', 'Intermediate'),
(18, 'Chua', 'Mei Qi', 0, '2009-08-08', 'IGCSE, English, Science', 0, 'Year 10', 'Nexus International School', 'Beginner'),
(19, 'Pang', 'Wei Jun', 1, '2006-10-13', 'SPM, Physics, Add Math', 1, 'Form 5', 'SMK Seafield', 'Advanced'),
(20, 'Yeoh', 'Xin Ru', 0, '2011-07-21', 'KSSR, Math, English', 1, 'Standard 5', 'SJK(C) Kuen Cheng', 'Beginner'),
(21, 'Sim', 'Kai Jie', 1, '2008-02-17', 'IGCSE, Math, Physics', 1, 'Year 10', 'HELP International School', 'Intermediate'),
(22, 'Kong', 'Hui Min', 0, '2007-11-05', 'SPM, Biology, Chemistry', 1, 'Form 4', 'SMK Bandar Utama', 'Advanced'),
(23, 'Foo', 'Jun Wei', 1, '2009-04-28', 'IGCSE, Geography, Math', 0, 'Year 9', 'St. John’s International School', 'Beginner'),
(24, 'Liew', 'Xin Yi', 0, '2006-12-09', 'SPM, Math, English', 1, 'Form 5', 'SMK Taman SEA', 'Intermediate'),
(25, 'Tay', 'Zhi Hao', 1, '2010-03-14', 'KSSR, Science, Malay', 1, 'Standard 6', 'SJK(C) Han Chiang', 'Beginner'),
(26, 'Koh', 'Pei Wen', 0, '2008-09-02', 'IGCSE, Economics, Math', 1, 'Year 11', 'Uplands International School', 'Advanced'),
(27, 'Chin', 'Wei Xiang', 1, '2007-08-19', 'SPM, Physics, Add Math', 1, 'Form 4', 'SMK Subang Utama', 'Intermediate'),
(28, 'Toh', 'Jia Yi', 0, '2009-10-25', 'IGCSE, English, Chemistry', 0, 'Year 10', 'Alice Smith School', 'Beginner'),
(29, 'Heng', 'Jun Kai', 1, '2006-05-31', 'SPM, Math, Biology', 1, 'Form 5', 'SMK Damansara Utama', 'Advanced'),
(30, 'Poon', 'Xin Tong', 0, '2011-01-08', 'KSSR, Math, English', 1, 'Standard 5', 'SJK(C) Puay Chai', 'Beginner'),
(31, 'Kwan', 'Wei Lun', 1, '2008-06-14', 'IGCSE, Math, Physics', 1, 'Year 10', 'Kolej Tuanku Ja’afar', 'Intermediate'),
(32, 'Liang', 'Shu Qi', 0, '2007-03-22', 'SPM, Chemistry, English', 1, 'Form 4', 'SMK Sultan Ismail', 'Advanced'),
(33, 'Yeap', 'Zhi Cong', 1, '2009-07-16', 'IGCSE, Geography, Math', 0, 'Year 9', 'Marlborough College Malaysia', 'Beginner'),
(34, 'Ooi', 'Hui Shan', 0, '2006-09-28', 'SPM, Add Math, Physics', 1, 'Form 5', 'SMK Convent Green Lane', 'Intermediate'),
(35, 'Tham', 'Wei Jie', 1, '2010-11-11', 'KSSR, Science, Malay', 1, 'Standard 6', 'SJK(C) Jalan Davidson', 'Beginner'),
(36, 'See', 'Xin Wei', 0, '2008-01-30', 'IGCSE, Math, Economics', 1, 'Year 11', 'Fairview International School', 'Advanced'),
(37, 'Phang', 'Jun Wei', 1, '2007-05-07', 'SPM, Physics, Math', 1, 'Form 4', 'SMK Seri Hartamas', 'Intermediate'),
(38, 'Chia', 'Mei Xin', 0, '2009-12-20', 'IGCSE, English, Science', 0, 'Year 10', 'IGB International School', 'Beginner'),
(39, 'Tiew', 'Zhi Yang', 1, '2006-04-15', 'SPM, Add Math, Chemistry', 1, 'Form 5', 'SMK Aminuddin Baki', 'Advanced'),
(40, 'Law', 'Jia Wen', 0, '2011-06-23', 'KSSR, Math, English', 1, 'Standard 5', 'SJK(C) Chung Kwok', 'Beginner');

-- --------------------------------------------------------

--
-- Table structure for table `student_timetable`
--

CREATE TABLE `student_timetable` (
  `id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_timetable`
--

INSERT INTO `student_timetable` (`id`, `last_name`, `first_name`, `course`, `day`, `start_time`, `end_time`, `approved_at`) VALUES
(1, 'Wong', 'Siew Ling', 'Economics Math', 'Monday', '10:00:00', '12:00:00', '2023-10-15 06:25:00'),
(2, 'Chew', 'Boon Keat', 'Chemistry Math', 'Wednesday', '16:00:00', '18:00:00', '2023-10-16 03:40:00'),
(3, 'Yap', 'Hui Xin', 'English for Science', 'Friday', '14:00:00', '16:00:00', '2023-10-17 02:55:00'),
(4, 'Khoo', 'Zhi Wei', 'Advanced Physics', 'Tuesday', '13:00:00', '15:00:00', '2023-10-18 00:20:00'),
(5, 'Chan', 'Yi Ting', 'Basic Science', 'Thursday', '09:00:00', '11:00:00', '2023-10-19 07:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_timetable_requests`
--

CREATE TABLE `student_timetable_requests` (
  `id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_timetable_requests`
--

INSERT INTO `student_timetable_requests` (`id`, `last_name`, `first_name`, `course`, `day`, `start_time`, `end_time`, `status`, `rejection_reason`, `requested_at`) VALUES
(1, 'Tan', 'Wei Jie', 'IGCSE Math Prep', 'Monday', '16:00:00', '18:00:00', 'pending', NULL, '2023-11-01 04:30:00'),
(2, 'Lim', 'Mei Ling', 'SPM Add Math', 'Wednesday', '14:00:00', '16:00:00', 'pending', NULL, '2023-11-02 05:45:00'),
(3, 'Chong', 'Kai Wen', 'Physics Basics', 'Friday', '09:00:00', '11:00:00', 'pending', NULL, '2023-11-03 02:20:00'),
(4, 'Ng', 'Xiu Mei', 'Biology Concepts', 'Tuesday', '11:00:00', '13:00:00', 'pending', NULL, '2023-11-04 07:10:00'),
(5, 'Lee', 'Jun Hao', 'Elementary Math', 'Thursday', '15:00:00', '17:00:00', 'pending', NULL, '2023-11-05 01:30:00');

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
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`instructor_id`),
  ADD KEY `idx_instructor_names` (`Last_Name`,`First_Name`);

--
-- Indexes for table `instructor_timetable`
--
ALTER TABLE `instructor_timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instructor_timetable_requests`
--
ALTER TABLE `instructor_timetable_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_student_payment` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `idx_student_names` (`Last_Name`,`First_Name`);

--
-- Indexes for table `student_timetable`
--
ALTER TABLE `student_timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_timetable_requests`
--
ALTER TABLE `student_timetable_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `student_timetable`
--
ALTER TABLE `student_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_timetable_requests`
--
ALTER TABLE `student_timetable_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
