-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 03:47 AM
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
-- Database: `hcc_schedule`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `email`, `full_name`) VALUES
(1, 'admin', '$2y$10$bEZv6vTQKG2DwwH45380tuI.9mJsQt.JbxydBWvIY3Ns5fDyqMbTu', 'admin@admin.com', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `curriculum`
--

CREATE TABLE `curriculum` (
  `curriculum_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
  `semester` enum('1st Semester','2nd Semester','Summer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `irregular_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`irregular_id`, `student_id`, `subject_id`, `section_id`) VALUES
(34, 4, 3, 21),
(35, 4, 5, 1),
(36, 4, 6, 23),
(32, 4, 7, 3),
(33, 4, 8, 22);

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_code` varchar(20) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
  `semester` enum('1st Semester','2nd Semester','Summer') NOT NULL,
  `course` varchar(100) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `schedule_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `schedule_time_start` time DEFAULT NULL,
  `schedule_time_end` time DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_code`, `section_name`, `year_level`, `semester`, `course`, `academic_year`, `schedule_day`, `schedule_time_start`, `schedule_time_end`, `room`, `instructor`, `subject_id`) VALUES
(1, 'IT2A', 'BSIT-2A', '2nd Year', '2nd Semester', 'BSIT', '2025-2026', 'Monday', '17:00:00', '19:30:00', 'Computer Lab A', 'Rommel San Pablo Malinao', 5),
(2, 'CS1A', 'BSCS-1A', '1st Year', '2nd Semester', 'BSCS', '2025-2026', 'Tuesday', '17:00:00', '19:30:00', 'Computer Lab A', 'Joseph Cris Valdez', 1),
(3, 'IT1B', 'BSIT-1B', '1st Year', '2nd Semester', 'BSIT', '2025-2026', 'Monday', '09:30:00', '11:00:00', 'Computer Lab B', 'Clark Anderson', 7),
(4, 'IT1B-CS1A', 'BSCS-1A - BSIT-1B', '1st Year', '2nd Semester', 'BSIT & BSCS', '2025-2026', 'Friday', '12:00:00', '02:30:00', 'Gymnasium', 'Lyka Parungao', 2),
(21, 'CS1A-IT1B', 'BSIT-1B - BSCS-1A', '1st Year', '2nd Semester', 'BSIT & BSCS', '2025-2026', 'Thursday', '09:30:00', '11:00:00', 'Drawing Room', 'Grosby Aguilar Dela Cruz', 3),
(22, 'IT1B&CS1A', 'BSCS-1A - BSIT-1B', '1st Year', '2nd Semester', 'BSIT and BSCS', '2025-2026', 'Wednesday', '17:00:00', '19:30:00', 'C35', 'Xavier Pangan', 8),
(23, 'IT2B', 'BSIT-2B', '2nd Year', '2nd Semester', 'BSIT', '2025-2026', 'Friday', '12:00:00', '14:00:00', 'Gymnasium', 'Gerald Cano', 6),
(24, 'CS-1A', 'BSCS-1A', '1st Year', '2nd Semester', 'BSCS', '2025-2026', 'Saturday', '13:00:00', '15:30:00', 'C32', 'Jose Manalo', 4),
(26, 'IT-2B', '2B BSIT', '2nd Year', '2nd Semester', 'BSED', '2025-2026', 'Monday', '09:30:00', '11:00:00', 'Drawing Room', 'Abigael Suba Caliwag', 9);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
  `course` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_number`, `first_name`, `last_name`, `email`, `password`, `contact_number`, `year_level`, `course`) VALUES
(4, '2026-0001', 'Renz', 'Maghinang', 'aichiken3245@gmail.com', '$2y$10$jkd44tAW08Vh8Rolo/.yQ.KUGTnC.61Tt.XEkgnaRVFWdM7pBRqyK', '0969 069 3481', '2nd Year', 'BSIT'),
(5, '2026-0002', 'Caleb Joshua', 'Nonan', 'caleb.nonan@gmail.com', '$2y$10$3W4r4M4cGSPCWyrT/ritie453HUFLYQZJyZmbjnXHoia4IH.Y8f7W', '0912 345 6789', '1st Year', 'BSCS');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `units` int(11) NOT NULL CHECK (`units` > 0),
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
  `semester` enum('1st Semester','2nd Semester','Summer') NOT NULL,
  `course` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `description`, `units`, `year_level`, `semester`, `course`) VALUES
(1, 'WD-0001', 'Web Development', 'erm', 6, '1st Year', '2nd Semester', 'BSCS'),
(2, 'PFIT-0002', 'PFIT 2', 'erm', 3, '1st Year', '2nd Semester', 'ANY'),
(3, 'DM-0001', 'Discrete Math', 'erm', 6, '1st Year', '2nd Semester', 'BSIT & BSCS'),
(4, 'THEO-02', 'Theology 2', 'erm', 3, '1st Year', '2nd Semester', 'ANY'),
(5, 'NW-0001', 'Networking 1', 'erm', 6, '2nd Year', '2nd Semester', 'BSIT'),
(6, 'PFIT-0004', 'Sports', 'erm', 3, '2nd Year', '2nd Semester', 'ANY'),
(7, 'HCI-0001', 'Human & Computer Interactions', 'erm', 5, '1st Year', '2nd Semester', 'BSIT'),
(8, 'ITE-0001', 'IT Era', 'erm', 5, '1st Year', '2nd Semester', 'BSIT & BSCS'),
(9, 'PURCOM-0001', 'Purpossive Communication', 'erm', 3, '2nd Year', '2nd Semester', 'BSED');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD PRIMARY KEY (`curriculum_id`),
  ADD UNIQUE KEY `unique_curriculum` (`subject_id`,`course`,`year_level`,`semester`),
  ADD KEY `idx_curriculum_course` (`course`),
  ADD KEY `idx_curriculum_year` (`year_level`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`irregular_id`),
  ADD UNIQUE KEY `unique_irregular_enrollment` (`student_id`,`subject_id`,`section_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `idx_irregular_student` (`student_id`),
  ADD KEY `idx_irregular_subject` (`subject_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `section_code` (`section_code`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_section_code` (`section_code`),
  ADD KEY `idx_section_course` (`course`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_student_number` (`student_number`),
  ADD KEY `idx_student_course` (`course`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `idx_subject_code` (`subject_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `curriculum`
--
ALTER TABLE `curriculum`
  MODIFY `curriculum_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `irregular_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD CONSTRAINT `curriculum_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE SET NULL;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
