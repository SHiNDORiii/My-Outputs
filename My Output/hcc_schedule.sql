-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 01:36 PM
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
  `admin_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('super_admin','admin','faculty','registrar') DEFAULT 'faculty',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_code` varchar(20) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `year_level` enum('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `strand` varchar(50) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT 40,
  `semester` enum('1st Semester','2nd Semester') NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `birth_date` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `year_level` enum('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','Pending','Transferred','Graduated') DEFAULT 'Pending',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `email`, `password`, `first_name`, `last_name`, `gender`, `birth_date`, `contact_number`, `address`, `year_level`, `section_id`, `enrollment_status`, `is_active`, `created_at`, `last_login`) VALUES
(5, 'HCCP-2026-0001', 'aichiken3245@gmail.com', '$2y$10$LllTg.i09fsGnOTa1qXPC.X2QJw9rtnLKaSgJDM0FiAiXSnvlTpSO', 'Renz Darelle', 'Maghinang', 'Male', '2005-03-24', '0969 069 3481', 'Bambang, Candaba, Pampanga', 'Grade 10', NULL, 'Enrolled', 1, '2026-03-22 12:25:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `units` int(11) DEFAULT 3,
  `subject_type` enum('Core','Applied','Specialized','Elective') NOT NULL,
  `year_level` enum('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `strand` varchar(50) DEFAULT NULL,
  `semester` enum('1st Semester','2nd Semester') NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `schedule_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday') DEFAULT NULL,
  `schedule_start` time DEFAULT NULL,
  `schedule_end` time DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `units`, `subject_type`, `year_level`, `strand`, `semester`, `school_year`, `teacher_id`, `room`, `schedule_day`, `schedule_start`, `schedule_end`, `is_active`) VALUES
(1, 'MATH10', 'Mathematics 10', 3, 'Core', 'Grade 10', NULL, '2nd Semester', '2025-2026', NULL, 'Room 204', 'Monday', '08:30:00', '10:00:00', 1),
(2, 'CHEM10', 'Chemistry', 3, 'Core', 'Grade 10', NULL, '2nd Semester', '2025-2026', NULL, 'Lab B', 'Tuesday', '10:15:00', '11:45:00', 1),
(3, 'ENG10', 'English Literature', 3, 'Core', 'Grade 10', NULL, '2nd Semester', '2025-2026', NULL, 'Room 108', 'Wednesday', '13:30:00', '15:00:00', 1),
(4, 'PE10', 'Physical Education', 2, 'Elective', 'Grade 10', NULL, '2nd Semester', '2025-2026', NULL, 'Gym', 'Thursday', '15:10:00', '16:40:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transferee`
--

CREATE TABLE `transferee` (
  `transferee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `previous_school` varchar(100) DEFAULT NULL,
  `previous_year_level` varchar(20) DEFAULT NULL,
  `applying_for_year_level` enum('Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `application_status` enum('Pending','Approved','Rejected','Enrolled') DEFAULT 'Pending',
  `evaluation_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_number` (`admin_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `section_code` (`section_code`),
  ADD KEY `adviser_id` (`adviser_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `transferee`
--
ALTER TABLE `transferee`
  ADD PRIMARY KEY (`transferee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transferee`
--
ALTER TABLE `transferee`
  MODIFY `transferee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`adviser_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE SET NULL;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
