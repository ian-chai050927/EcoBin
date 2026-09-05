-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2026 at 12:22 PM
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
-- Database: `ecobin`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `created_at`) VALUES
(1, 1, 'auth.login', '2026-08-21 20:59:04'),
(2, 2, 'auth.login', '2026-08-21 21:09:26'),
(3, 1, 'auth.login', '2026-08-21 21:09:55'),
(4, 3, 'auth.login', '2026-08-21 21:10:19'),
(5, 4, 'auth.login', '2026-08-21 21:10:32'),
(6, 1, 'auth.login', '2026-08-26 11:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_by`, `created_at`) VALUES
(1, 'Welcome to EcoBin', 'Use EcoBin to report waste, schedule collection and participate in recycling.', 2, '2026-08-22 02:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity` varchar(80) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'auth.login', 'User', 1, '{\"entity\":\"User\",\"entity_id\":1}', '::1', '2026-08-21 20:59:04'),
(2, 2, 'auth.login', 'User', 2, '{\"entity\":\"User\",\"entity_id\":2}', '::1', '2026-08-21 21:09:26'),
(3, 1, 'auth.login', 'User', 1, '{\"entity\":\"User\",\"entity_id\":1}', '::1', '2026-08-21 21:09:55'),
(4, 3, 'auth.login', 'User', 3, '{\"entity\":\"User\",\"entity_id\":3}', '::1', '2026-08-21 21:10:19'),
(5, 4, 'auth.login', 'User', 4, '{\"entity\":\"User\",\"entity_id\":4}', '::1', '2026-08-21 21:10:32'),
(6, 1, 'auth.login', 'User', 1, '{\"entity\":\"User\",\"entity_id\":1}', '::1', '2026-08-26 11:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `collection_requests`
--

CREATE TABLE `collection_requests` (
  `id` int(11) NOT NULL,
  `waste_report_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `scheduled_date` date DEFAULT NULL,
  `collection_staff_id` int(11) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'System',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycling_appointments`
--

CREATE TABLE `recycling_appointments` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `appointment_at` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycling_centers`
--

CREATE TABLE `recycling_centers` (
  `id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `address` varchar(500) NOT NULL,
  `accepted_materials` varchar(255) NOT NULL,
  `availability` varchar(30) NOT NULL DEFAULT 'Open',
  `operating_hours` varchar(120) DEFAULT 'Mon - Fri: 9:00 AM - 5:00 PM',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycling_centers`
--

INSERT INTO `recycling_centers` (`id`, `operator_id`, `name`, `address`, `accepted_materials`, `availability`, `operating_hours`, `created_at`) VALUES
(1, 4, 'EcoBin Setapak Recycling Centre', 'Setapak, Kuala Lumpur', 'Plastic, Paper, Metal, E-Waste', 'Open', 'Mon - Fri: 9:00 AM - 5:00 PM', '2026-08-22 02:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `recycling_submissions`
--

CREATE TABLE `recycling_submissions` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `material` varchar(80) NOT NULL,
  `weight_kg` decimal(8,2) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward_transactions`
--

CREATE TABLE `reward_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `config_key` varchar(80) NOT NULL,
  `config_value` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`config_key`, `config_value`, `updated_at`) VALUES
('collection.max_daily', '50', '2026-08-22 02:58:03'),
('recycling.points_per_kg', '10', '2026-08-22 02:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(40) NOT NULL DEFAULT 'Resident',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `email_verified_at`, `verification_token`, `reset_token`, `reset_expires_at`, `created_at`) VALUES
(1, 'Demo Resident', 'resident@ecobin.test', '$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y', 'Resident', 'Active', '2026-08-22 02:58:03', NULL, NULL, NULL, '2026-08-22 02:58:03'),
(2, 'Demo Admin', 'admin@ecobin.test', '$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y', 'Admin', 'Active', '2026-08-22 02:58:03', NULL, NULL, NULL, '2026-08-22 02:58:03'),
(3, 'Collector Amir', 'collector@ecobin.test', '$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y', 'Collection Staff', 'Active', '2026-08-22 02:58:03', NULL, NULL, NULL, '2026-08-22 02:58:03'),
(4, 'Recycle Operator', 'operator@ecobin.test', '$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y', 'Recycling Center Operator', 'Active', '2026-08-22 02:58:03', NULL, NULL, NULL, '2026-08-22 02:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `waste_reports`
--

CREATE TABLE `waste_reports` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'Normal',
  `waste_size` varchar(30) NOT NULL DEFAULT 'Medium',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_announcement_admin` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collection_requests`
--
ALTER TABLE `collection_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `waste_report_id` (`waste_report_id`),
  ADD KEY `fk_collection_resident` (`resident_id`),
  ADD KEY `fk_collection_staff` (`collection_staff_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_user` (`user_id`);

--
-- Indexes for table `recycling_appointments`
--
ALTER TABLE `recycling_appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appt_resident` (`resident_id`),
  ADD KEY `fk_appt_center` (`center_id`);

--
-- Indexes for table `recycling_centers`
--
ALTER TABLE `recycling_centers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_center_operator` (`operator_id`);

--
-- Indexes for table `recycling_submissions`
--
ALTER TABLE `recycling_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_submission_resident` (`resident_id`),
  ADD KEY `fk_submission_center` (`center_id`);

--
-- Indexes for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reward_user` (`user_id`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`config_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waste_reports`
--
ALTER TABLE `waste_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `collection_requests`
--
ALTER TABLE `collection_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recycling_appointments`
--
ALTER TABLE `recycling_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recycling_centers`
--
ALTER TABLE `recycling_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `recycling_submissions`
--
ALTER TABLE `recycling_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `waste_reports`
--
ALTER TABLE `waste_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcement_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `collection_requests`
--
ALTER TABLE `collection_requests`
  ADD CONSTRAINT `fk_collection_report` FOREIGN KEY (`waste_report_id`) REFERENCES `waste_reports` (`id`),
  ADD CONSTRAINT `fk_collection_resident` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_collection_staff` FOREIGN KEY (`collection_staff_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `recycling_appointments`
--
ALTER TABLE `recycling_appointments`
  ADD CONSTRAINT `fk_appt_center` FOREIGN KEY (`center_id`) REFERENCES `recycling_centers` (`id`),
  ADD CONSTRAINT `fk_appt_resident` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `recycling_centers`
--
ALTER TABLE `recycling_centers`
  ADD CONSTRAINT `fk_center_operator` FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `recycling_submissions`
--
ALTER TABLE `recycling_submissions`
  ADD CONSTRAINT `fk_submission_center` FOREIGN KEY (`center_id`) REFERENCES `recycling_centers` (`id`),
  ADD CONSTRAINT `fk_submission_resident` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  ADD CONSTRAINT `fk_reward_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `waste_reports`
--
ALTER TABLE `waste_reports`
  ADD CONSTRAINT `fk_waste_resident` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
