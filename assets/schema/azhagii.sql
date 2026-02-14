-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2026 at 06:02 PM
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
-- Database: `ziya`
--

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`id`, `name`, `code`, `address`, `city`, `status`, `createdAt`) VALUES
(1, 'M. Kumarasamy College of Engineering', 'MKCE', 'Karur', 'Karur', 'active', '2026-02-13 10:11:15'),
(2, 'K. Ramakrishnan College of Engineering', 'KRCE', 'Tiruchirappalli', 'Tiruchirappalli', 'active', '2026-02-14 07:35:30');

-- --------------------------------------------------------

--
-- Table structure for table `coursecolleges`
--

CREATE TABLE `coursecolleges` (
  `id` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `collegeId` int(11) NOT NULL,
  `assignedBy` int(11) DEFAULT NULL,
  `assignedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coursecolleges`
--

INSERT INTO `coursecolleges` (`id`, `courseId`, `collegeId`, `assignedBy`, `assignedAt`) VALUES
(1, 1, 1, 1, '2026-02-13 10:13:38'),
(2, 2, 1, NULL, '2026-02-13 11:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `coursecontent`
--

CREATE TABLE `coursecontent` (
  `id` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `contentType` enum('video','pdf','text','link') NOT NULL,
  `contentData` text DEFAULT NULL,
  `uploadedBy` int(11) DEFAULT NULL,
  `collegeId` int(11) DEFAULT NULL,
  `sortOrder` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `subjectId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coursecontent`
--

INSERT INTO `coursecontent` (`id`, `courseId`, `title`, `description`, `contentType`, `contentData`, `uploadedBy`, `collegeId`, `sortOrder`, `status`, `createdAt`, `subjectId`) VALUES
(1, 1, 'Hello', 'Hiii', 'pdf', '', NULL, 1, 0, 'active', '2026-02-13 10:33:01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `courseCode` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `courseType` varchar(50) DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `syllabus` varchar(500) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `regulation` varchar(50) DEFAULT NULL,
  `academicYear` varchar(20) DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `status` enum('draft','pending','active','rejected','archived') DEFAULT 'draft',
  `approvedBy` int(11) DEFAULT NULL,
  `approvedAt` timestamp NULL DEFAULT NULL,
  `rejectionReason` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `courseCode`, `description`, `category`, `courseType`, `thumbnail`, `syllabus`, `semester`, `regulation`, `academicYear`, `createdBy`, `status`, `approvedBy`, `approvedAt`, `rejectionReason`, `createdAt`, `updatedAt`) VALUES
(1, 'Python', '', 'Python Programming', 'Programming', 'theory', '', NULL, '1', '', '', 1, 'active', NULL, NULL, NULL, '2026-02-13 10:13:25', '2026-02-13 13:18:45'),
(2, 'ATCD', 'AM1001', 'Hello ATCD', 'System Design', 'theory', '', 'uploads/content/syllabus_1770980665_9614.pdf', '2', 'R2023', '2025-2026', NULL, 'active', NULL, '2026-02-13 11:05:12', NULL, '2026-02-13 11:04:25', '2026-02-13 11:05:12');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `studentId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `status` enum('active','completed','dropped') DEFAULT 'active',
  `enrolledAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `completedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `studentId`, `courseId`, `progress`, `status`, `enrolledAt`, `completedAt`) VALUES
(4, 11, 1, 0, 'active', '2026-02-14 16:00:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `eventDate` date NOT NULL,
  `eventTime` time DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `organizer` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `capacity` int(11) DEFAULT 100,
  `imageUrl` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','completed','cancelled') DEFAULT 'upcoming',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profilerequests`
--

CREATE TABLE `profilerequests` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `requestReason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolvedAt` timestamp NULL DEFAULT NULL,
  `resolvedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profilerequests`
--

INSERT INTO `profilerequests` (`id`, `userId`, `requestReason`, `status`, `createdAt`, `resolvedAt`, `resolvedBy`) VALUES
(2, 1, 'AA', 'approved', '2026-02-13 14:22:55', '2026-02-13 15:55:58', 1),
(4, 11, 'Need to Update my Github ID', 'approved', '2026-02-14 09:11:24', '2026-02-14 13:56:13', 1),
(5, 11, 'AA', 'approved', '2026-02-14 13:56:35', '2026-02-14 13:56:40', 1),
(6, 11, 'AA', 'approved', '2026-02-14 13:56:49', '2026-02-14 13:56:56', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `courseId`, `title`, `code`, `description`, `status`, `createdAt`) VALUES
(1, 1, 'PY', 'PY001', 'Hajd', 'active', '2026-02-13 10:25:26');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `subjectId`, `title`, `description`, `createdBy`, `status`, `createdAt`) VALUES
(1, 1, 'SS', 'AA', NULL, 'active', '2026-02-13 10:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superAdmin','adminAzhagii','azhagiiCoordinator','azhagiiStudents') NOT NULL,
  `collegeId` int(11) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `year` varchar(20) DEFAULT NULL,
  `rollNumber` varchar(20) DEFAULT NULL,
  `azhagiiID` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profilePhoto` varchar(255) DEFAULT NULL,
  `githubUrl` varchar(255) DEFAULT NULL,
  `linkedinUrl` varchar(255) DEFAULT NULL,
  `hackerrankUrl` varchar(255) DEFAULT NULL,
  `leetcodeUrl` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `isLocked` tinyint(1) DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `password`, `role`, `collegeId`, `department`, `year`, `rollNumber`, `azhagiiID`, `phone`, `dob`, `gender`, `address`, `profilePhoto`, `githubUrl`, `linkedinUrl`, `hackerrankUrl`, `leetcodeUrl`, `status`, `isLocked`, `createdAt`, `bio`) VALUES
(1, 'Jayanthan Senthilkumar', 'admin@ziyaa.com', 'ziyaa', '$2y$10$tkUk22h4kZqjSpw1pZZA7eKK.p/LNeo6JBQ.jYO9iqd3Kyz7o4Q0G', 'superAdmin', NULL, NULL, NULL, NULL, NULL, '+918825756388', '2004-11-18', 'Male', '1/64/4, Muthukrishnapuram, Vennaimalai, Karur', 'uploads/profiles/profile_1_1771045160.jpg', 'https://syraa.app', 'https://syraa.app', 'https://syraa.app', 'https://syraa.app', 'active', 1, '2026-02-13 04:26:38', 'Hello'),
(10, 'Harish', 'itsmejayanthan@gmail.com', 'harish', 'Harish@1234', 'azhagiiStudents', 1, 'AIML', 'IV year', '927622BAL014', 'AZGMKCE0001', '+918825756388', '2025-11-30', 'Male', 'Dindigul', 'uploads/profiles/profile_reg_1771054004_779cb904.jpg', '', '', '', '', 'active', 1, '2026-02-14 07:26:44', 'Hiii'),
(11, 'Supriya S', 'supriyaasrinivasan@gmail.com', 'supriya', 'Sups@1012', 'azhagiiStudents', 1, 'CSE', 'I year', '927625BCS167', 'AZGMKCE0002', '8825756388', '2007-12-10', 'Female', 'Salem', 'uploads/profiles/927625BCS167.jpg', '', '', '', '', 'active', 1, '2026-02-14 08:45:58', 'Hello'),
(12, 'Anusiya', 'nishanthini@gmail.com', 'nishanthini', '$2y$10$9kYX9LgUD5rBLIym6cb1yOSZwYXAqv4raHS2gotxBuqshnr8eG8MG', 'azhagiiStudents', 1, 'AIML', 'I year', '927625BAM001', 'AZGMKCE0003', '+918825756388', '2009-01-01', 'Female', 'Karur', 'uploads/profiles/927625BAM001.jpg', '', '', '', '', 'active', 0, '2026-02-14 16:41:16', 'Hey Azhagii');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `coursecolleges`
--
ALTER TABLE `coursecolleges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_course_college` (`courseId`,`collegeId`),
  ADD KEY `college_id` (`collegeId`),
  ADD KEY `assigned_by` (`assignedBy`);

--
-- Indexes for table `coursecontent`
--
ALTER TABLE `coursecontent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`courseId`),
  ADD KEY `uploaded_by` (`uploadedBy`),
  ADD KEY `college_id` (`collegeId`),
  ADD KEY `subject_id` (`subjectId`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `courseCode` (`courseCode`),
  ADD KEY `created_by` (`createdBy`),
  ADD KEY `approved_by` (`approvedBy`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`studentId`,`courseId`),
  ADD KEY `course_id` (`courseId`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profilerequests`
--
ALTER TABLE `profilerequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`userId`),
  ADD KEY `resolved_by` (`resolvedBy`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`courseId`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subjectId`),
  ADD KEY `created_by` (`createdBy`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `roll_number` (`rollNumber`),
  ADD UNIQUE KEY `azhagiiID` (`azhagiiID`),
  ADD KEY `college_id` (`collegeId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coursecolleges`
--
ALTER TABLE `coursecolleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coursecontent`
--
ALTER TABLE `coursecontent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profilerequests`
--
ALTER TABLE `profilerequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `coursecolleges`
--
ALTER TABLE `coursecolleges`
  ADD CONSTRAINT `coursecolleges_ibfk_1` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coursecolleges_ibfk_2` FOREIGN KEY (`collegeId`) REFERENCES `colleges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coursecolleges_ibfk_3` FOREIGN KEY (`assignedBy`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coursecontent`
--
ALTER TABLE `coursecontent`
  ADD CONSTRAINT `coursecontent_ibfk_1` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coursecontent_ibfk_2` FOREIGN KEY (`uploadedBy`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coursecontent_ibfk_3` FOREIGN KEY (`collegeId`) REFERENCES `colleges` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coursecontent_ibfk_4` FOREIGN KEY (`subjectId`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`approvedBy`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`approvedBy`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`studentId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profilerequests`
--
ALTER TABLE `profilerequests`
  ADD CONSTRAINT `profilerequests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profilerequests_ibfk_2` FOREIGN KEY (`resolvedBy`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`courseId`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`subjectId`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topics_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`collegeId`) REFERENCES `colleges` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
