-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 04, 2026 at 02:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pau_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` enum('booked','checked-in','completed') DEFAULT 'booked',
  `qr_code` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `service_id`, `date`, `time`, `status`, `qr_code`, `created_at`) VALUES
(1, 2, 1, '2026-04-10', '17:06:00', 'booked', 'APPT-69cfe584b3233-USR-2', '2026-04-03 16:06:28'),
(2, 2, 12, '2026-04-25', '23:09:00', 'booked', 'APPT-69d03aa053ccd-USR-2', '2026-04-03 22:09:36'),
(3, 2, 14, '2026-04-16', '22:17:00', 'booked', 'APPT-69d03bad85709-USR-2', '2026-04-03 22:14:05'),
(4, 2, 14, '2026-04-17', '22:24:00', 'booked', 'APPT-69d03df8cacd7-USR-2', '2026-04-03 22:23:52'),
(5, 2, 11, '2026-04-18', '22:27:00', 'booked', 'APPT-69d03e60cbbb8-USR-2', '2026-04-03 22:25:36'),
(6, 2, 12, '2026-04-17', '22:33:00', 'booked', 'APPT-69d04013d3b5b-USR-2', '2026-04-03 22:32:51'),
(7, 2, 11, '2026-04-25', '22:39:00', 'booked', 'APPT-69d040e0a96bc-USR-2', '2026-04-03 22:36:16'),
(8, 2, 13, '2026-04-25', '01:39:00', 'booked', 'APPT-69d041ae8265b-USR-2', '2026-04-03 22:39:42'),
(9, 2, 13, '2026-04-25', '01:39:00', 'booked', 'APPT-69d041b024af4-USR-2', '2026-04-03 22:39:44'),
(10, 2, 12, '2026-04-11', '22:43:00', 'booked', 'APPT-69d041e6c80cb-USR-2', '2026-04-03 22:40:38'),
(11, 2, 11, '2026-04-09', '22:45:00', 'booked', 'APPT-69d04210dcdba-USR-2', '2026-04-03 22:41:20'),
(12, 2, 16, '2026-04-06', '22:44:00', 'booked', 'APPT-69d0425d805e4-USR-2', '2026-04-03 22:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('in-app','email') DEFAULT 'in-app',
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `status`, `created_at`) VALUES
(12, 2, 'Your appointment for Residence on 2026-04-06 at 22:44 is confirmed.\n\n━━━━━━━━━━━━━━━━━━━━━━\nStaff: Grace Adjei\nLocation: Hall of Residence A, Room 001\n━━━━━━━━━━━━━━━━━━━━━━\n\nQueue Position: #7\nPlease arrive 10 minutes before your scheduled time.\n\nYou will be notified when it\'s your turn.', 'in-app', 'unread', '2026-04-03 22:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `status` enum('waiting','serving','done') DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `appointment_id`, `position`, `status`) VALUES
(1, 6, 1, 'waiting'),
(2, 7, 2, 'waiting'),
(3, 8, 3, 'waiting'),
(4, 9, 4, 'waiting'),
(5, 10, 5, 'waiting'),
(6, 11, 6, 'waiting'),
(7, 12, 7, 'waiting');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `faculty_name` varchar(255) DEFAULT 'Staff',
  `building` varchar(255) DEFAULT 'Main Building'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `faculty_name`, `building`) VALUES
(1, 'General Consultation', 'Standard appointment service', 'Staff', 'Main Building'),
(11, 'Admissions', NULL, 'Abena Osei', 'Room 102, Ground Floor'),
(12, 'Registry', NULL, 'Ama Serwaa', 'Room 500'),
(13, 'Finance', NULL, 'John Mensah', 'Room 101, Ground Floor'),
(14, 'Student Affairs', NULL, 'Michael Tetteh', 'Student Center, Room 301'),
(15, 'Library', NULL, 'Emmanuel Ntow', 'Library'),
(16, 'Residence', NULL, 'Grace Adjei', 'Hall of Residence A, Room 001'),
(17, 'Health Services', NULL, 'Dr. Kofi Annan', 'Health Center, Room 105');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@dqssa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-03 15:11:56'),
(2, 'Fatma Ali', 'fatmaali@gmail.com', '$2y$10$YaG.xQE1rkz8Gj.q5kdB7u.zfYlBI9oEJo04Z0pfPRitOoMW0yrb6', 'student', '2026-04-03 15:24:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
