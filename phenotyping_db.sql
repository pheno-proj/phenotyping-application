-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 10:42 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phenotyping_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_performance`
--

CREATE TABLE `academic_performance` (
  `performance_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `subject` varchar(50) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `recorded_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-10 22:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `alert`
--

CREATE TABLE `alert` (
  `alert_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `alert_type` enum('Behavioral','Academic','General') DEFAULT 'General',
  `status` enum('New','Read','Resolved') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `behavior`
--

CREATE TABLE `behavior` (
  `behavior_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `behavior_type` varchar(100) DEFAULT NULL,
  `observation_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `severity_level` enum('Low','Medium','High') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `child_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `counselor`
--

CREATE TABLE `counselor` (
  `counselor_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `counselor`
--

INSERT INTO `counselor` (`counselor_id`, `full_name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'test', 'test3@test.com', '$2y$10$6TP7vaw2wSoZs3NwnkYz3.XyURJZyMjLr1RVTrVXiVSjN/233vYJq', '0512345678', '2026-02-11 00:34:12');

-- --------------------------------------------------------

--
-- Table structure for table `parent`
--

CREATE TABLE `parent` (
  `parent_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parent`
--

INSERT INTO `parent` (`parent_id`, `full_name`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
(1, 'test', 'test2@test.com', '$2y$10$geIjUzl6AsllNKsGuYuikOzkT0RA2wyiTTaNR5AMLBzrZLYFMni.q', '0512345678', 'test', '2026-02-11 00:33:24');

-- --------------------------------------------------------

--
-- Table structure for table `result`
--

CREATE TABLE `result` (
  `result_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `analysis_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `prediction` varchar(255) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `full_name`, `email`, `password`, `specialization`, `phone`, `created_at`) VALUES
(1, 'test', 'test@test.com', '$2y$10$Jr8/ha.69UyHyyv808UvpeMWncctNimBgrisZCDVBAWozm50oNBOm', 'test', '0512345678', '2026-02-11 00:32:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_performance`
--
ALTER TABLE `academic_performance`
  ADD PRIMARY KEY (`performance_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `alert`
--
ALTER TABLE `alert`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `behavior`
--
ALTER TABLE `behavior`
  ADD PRIMARY KEY (`behavior_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`child_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `counselor`
--
ALTER TABLE `counselor`
  ADD PRIMARY KEY (`counselor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `parent`
--
ALTER TABLE `parent`
  ADD PRIMARY KEY (`parent_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_performance`
--
ALTER TABLE `academic_performance`
  MODIFY `performance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alert`
--
ALTER TABLE `alert`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `behavior`
--
ALTER TABLE `behavior`
  MODIFY `behavior_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `counselor`
--
ALTER TABLE `counselor`
  MODIFY `counselor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parent`
--
ALTER TABLE `parent`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_performance`
--
ALTER TABLE `academic_performance`
  ADD CONSTRAINT `academic_performance_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE;

--
-- Constraints for table `alert`
--
ALTER TABLE `alert`
  ADD CONSTRAINT `alert_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE;

--
-- Constraints for table `behavior`
--
ALTER TABLE `behavior`
  ADD CONSTRAINT `behavior_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE;

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`parent_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `children_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `result_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
