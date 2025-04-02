-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3310
-- Generation Time: Apr 02, 2025 at 11:24 AM
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
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
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

INSERT INTO `instructor` (`Last_Name`, `First_Name`, `Gender`, `DOB`, `Highest_Education`, `Remark`, `Training_Status`) VALUES
('Tan', 'Kok Wei', 1, '1985-03-15', 'Bachelor, Master', 'Experienced in Math', 'Completed'),
('Lim', 'Mei Ling', 0, '1990-07-22', 'Bachelor', 'Good communicator', 'In Progress'),
('Chong', 'Ahmad bin', 1, '1978-11-30', 'Master, PhD', 'Specializes in Science', 'Completed'),
('Ng', 'Siew Yee', 0, '1988-05-10', 'Bachelor', 'Needs more training', 'In Progress'),
('Lee', 'Chin Fatt', 1, '1982-09-18', 'Bachelor, Master', 'Excellent in Physics', 'Completed'),
('Wong', 'Li Ying', 0, '1995-01-25', 'Bachelor', 'New instructor', 'In Progress'),
('Chew', 'Teck Guan', 1, '1980-04-12', 'Master', 'Strong in Chemistry', 'Completed'),
('Yap', 'Sook Fun', 0, '1987-08-05', 'Bachelor, Master', 'Dedicated teacher', 'Completed'),
('Khoo', 'Wei Ming', 1, '1992-12-20', 'Bachelor', 'Needs to improve', 'In Progress'),
('Chan', 'Mei Chen', 0, '1983-06-14', 'Master, PhD', 'Expert in English', 'Completed'),
('Goh', 'Kok Seng', 1, '1975-02-28', 'Bachelor, Master', 'Veteran instructor', 'Completed'),
('Lau', 'Siew Har', 0, '1990-10-09', 'Bachelor', 'Good with students', 'In Progress'),
('Teo', 'Chin Wei', 1, '1986-03-03', 'Master', 'Specializes in Geography', 'Completed'),
('Ho', 'Li Mei', 0, '1993-07-17', 'Bachelor', 'Enthusiastic', 'In Progress'),
('Ong', 'Teck Soon', 1, '1981-11-11', 'Bachelor, Master', 'Strong in History', 'Completed'),
('Low', 'Siew Ling', 0, '1989-04-25', 'Master', 'Great at Biology', 'Completed'),
('Soh', 'Wei Keong', 1, '1984-08-30', 'Bachelor', 'Needs more experience', 'In Progress'),
('Chua', 'Mei Yin', 0, '1991-02-14', 'Bachelor, Master', 'Excellent in Literature', 'Completed'),
('Pang', 'Kok Wai', 1, '1979-06-20', 'Master, PhD', 'Expert in Add Math', 'Completed'),
('Yeoh', 'Siew Ching', 0, '1985-12-05', 'Bachelor', 'Promising instructor', 'In Progress');

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

INSERT INTO `students` (`Last_Name`, `First_Name`, `Gender`, `DOB`, `School_Syllabus`, `School_Intake`, `Current_School_Grade`, `School`, `Mathology_Level`) VALUES
('Tan', 'Wei Jie', 1, '2008-05-12', 'IGCSE, Math, Science', 1, 'Year 10', 'Kingsway International School', 'Intermediate'),
('Lim', 'Mei Ling', 0, '2007-09-23', 'SPM, Math, English', 1, 'Form 4', 'SMK Damansara Jaya', 'Advanced'),
('Chong', 'Kai Wen', 1, '2009-03-15', 'IGCSE, Physics, Chemistry', 0, 'Year 9', 'Sri KDU International School', 'Beginner'),
('Ng', 'Xiu Mei', 0, '2006-11-07', 'SPM, Biology, History', 1, 'Form 5', 'SMK Taman Connaught', 'Intermediate'),
('Lee', 'Jun Hao', 1, '2010-01-19', 'KSSR, Math, Malay', 1, 'Standard 6', 'SJK(C) Chung Hwa', 'Beginner'),
('Wong', 'Siew Ling', 0, '2008-07-30', 'IGCSE, Math, Economics', 1, 'Year 11', 'Garden International School', 'Advanced'),
('Chew', 'Boon Keat', 1, '2007-12-04', 'SPM, Chemistry, Add Math', 1, 'Form 4', 'SMK Sri Permata', 'Intermediate'),
('Yap', 'Hui Xin', 0, '2009-06-18', 'IGCSE, English, Geography', 0, 'Year 10', 'Cempaka International School', 'Beginner'),
('Khoo', 'Zhi Wei', 1, '2006-08-22', 'SPM, Physics, Math', 1, 'Form 5', 'SMK Kepong Baru', 'Advanced'),
('Chan', 'Yi Ting', 0, '2011-02-14', 'KSSR, Science, English', 1, 'Standard 5', 'SJK(C) Lick Hung', 'Beginner'),
('Goh', 'Jia Hao', 1, '2008-04-09', 'IGCSE, Math, Business', 1, 'Year 10', 'Sunway International School', 'Intermediate'),
('Lau', 'Xin Yi', 0, '2007-10-27', 'SPM, Biology, English', 1, 'Form 4', 'SMK Bukit Jalil', 'Advanced'),
('Teo', 'Ming Zhe', 1, '2009-09-11', 'IGCSE, Chemistry, Math', 0, 'Year 9', 'Taylor’s International School', 'Beginner'),
('Ho', 'Pei Ling', 0, '2006-03-25', 'SPM, Add Math, Physics', 1, 'Form 5', 'SMK Seri Bintang Utara', 'Intermediate'),
('Ong', 'Wei Kang', 1, '2010-05-03', 'KSSR, Math, Malay', 1, 'Standard 6', 'SJK(C) Yuk Chai', 'Beginner'),
('Low', 'Shu Fen', 0, '2008-12-16', 'IGCSE, Economics, Math', 1, 'Year 11', 'British International School', 'Advanced'),
('Soh', 'Jian Wei', 1, '2007-06-29', 'SPM, Chemistry, Math', 1, 'Form 4', 'SMK Taman Desa', 'Intermediate'),
('Chua', 'Mei Qi', 0, '2009-08-08', 'IGCSE, English, Science', 0, 'Year 10', 'Nexus International School', 'Beginner'),
('Pang', 'Wei Jun', 1, '2006-10-13', 'SPM, Physics, Add Math', 1, 'Form 5', 'SMK Seafield', 'Advanced'),
('Yeoh', 'Xin Ru', 0, '2011-07-21', 'KSSR, Math, English', 1, 'Standard 5', 'SJK(C) Kuen Cheng', 'Beginner'),
('Sim', 'Kai Jie', 1, '2008-02-17', 'IGCSE, Math, Physics', 1, 'Year 10', 'HELP International School', 'Intermediate'),
('Kong', 'Hui Min', 0, '2007-11-05', 'SPM, Biology, Chemistry', 1, 'Form 4', 'SMK Bandar Utama', 'Advanced'),
('Foo', 'Jun Wei', 1, '2009-04-28', 'IGCSE, Geography, Math', 0, 'Year 9', 'St. John’s International School', 'Beginner'),
('Liew', 'Xin Yi', 0, '2006-12-09', 'SPM, Math, English', 1, 'Form 5', 'SMK Taman SEA', 'Intermediate'),
('Tay', 'Zhi Hao', 1, '2010-03-14', 'KSSR, Science, Malay', 1, 'Standard 6', 'SJK(C) Han Chiang', 'Beginner'),
('Koh', 'Pei Wen', 0, '2008-09-02', 'IGCSE, Economics, Math', 1, 'Year 11', 'Uplands International School', 'Advanced'),
('Chin', 'Wei Xiang', 1, '2007-08-19', 'SPM, Physics, Add Math', 1, 'Form 4', 'SMK Subang Utama', 'Intermediate'),
('Toh', 'Jia Yi', 0, '2009-10-25', 'IGCSE, English, Chemistry', 0, 'Year 10', 'Alice Smith School', 'Beginner'),
('Heng', 'Jun Kai', 1, '2006-05-31', 'SPM, Math, Biology', 1, 'Form 5', 'SMK Damansara Utama', 'Advanced'),
('Poon', 'Xin Tong', 0, '2011-01-08', 'KSSR, Math, English', 1, 'Standard 5', 'SJK(C) Puay Chai', 'Beginner'),
('Kwan', 'Wei Lun', 1, '2008-06-14', 'IGCSE, Math, Physics', 1, 'Year 10', 'Kolej Tuanku Ja’afar', 'Intermediate'),
('Liang', 'Shu Qi', 0, '2007-03-22', 'SPM, Chemistry, English', 1, 'Form 4', 'SMK Sultan Ismail', 'Advanced'),
('Yeap', 'Zhi Cong', 1, '2009-07-16', 'IGCSE, Geography, Math', 0, 'Year 9', 'Marlborough College Malaysia', 'Beginner'),
('Ooi', 'Hui Shan', 0, '2006-09-28', 'SPM, Add Math, Physics', 1, 'Form 5', 'SMK Convent Green Lane', 'Intermediate'),
('Tham', 'Wei Jie', 1, '2010-11-11', 'KSSR, Science, Malay', 1, 'Standard 6', 'SJK(C) Jalan Davidson', 'Beginner'),
('See', 'Xin Wei', 0, '2008-01-30', 'IGCSE, Math, Economics', 1, 'Year 11', 'Fairview International School', 'Advanced'),
('Phang', 'Jun Wei', 1, '2007-05-07', 'SPM, Physics, Math', 1, 'Form 4', 'SMK Seri Hartamas', 'Intermediate'),
('Chia', 'Mei Xin', 0, '2009-12-20', 'IGCSE, English, Science', 0, 'Year 10', 'IGB International School', 'Beginner'),
('Tiew', 'Zhi Yang', 1, '2006-04-15', 'SPM, Add Math, Chemistry', 1, 'Form 5', 'SMK Aminuddin Baki', 'Advanced'),
('Law', 'Jia Wen', 0, '2011-06-23', 'KSSR, Math, English', 1, 'Standard 5', 'SJK(C) Chung Kwok', 'Beginner');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
