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
(6, 1, 'auth.login', '2026-08-26 11:52:59'),
(7, 1, 'waste.report.created', '2026-09-01 08:12:10'),
(8, 1, 'collection.request.created', '2026-09-01 08:15:44'),
(9, 1, 'recycling.submission.created', '2026-09-02 10:05:33'),
(10, 2, 'announcement.created', '2026-09-02 11:00:00'),
(11, 1, 'recycling.appointment.created', '2026-09-03 09:30:00'),
(12, 3, 'collection.completed', '2026-09-04 14:20:00'),
(13, 4, 'recycling.submission.approved', '2026-09-05 10:45:00'),
(14, 1, 'reward.redeemed', '2026-09-05 11:00:00'),
(15, 1, 'auth.login', '2026-09-06 08:00:00');

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
(1, 'Welcome to EcoBin', 'Use EcoBin to report waste, schedule collection and participate in recycling.', 2, '2026-08-22 02:58:03'),
(2, 'Recycling Drive — September 2026', 'EcoBin is hosting a special recycling drive throughout September. Submit your recyclables and earn double points this month!', 2, '2026-09-01 09:00:00'),
(3, 'Collection Schedule Notice', 'Waste collection services will be temporarily unavailable on 8 September 2026 (public holiday). Please reschedule accordingly.', 2, '2026-09-05 10:00:00');

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
(1, 1, 'auth.login', 'User', 1, '{"entity":"User","entity_id":1}', '::1', '2026-08-21 20:59:04'),
(2, 2, 'auth.login', 'User', 2, '{"entity":"User","entity_id":2}', '::1', '2026-08-21 21:09:26'),
(3, 1, 'auth.login', 'User', 1, '{"entity":"User","entity_id":1}', '::1', '2026-08-21 21:09:55'),
(4, 3, 'auth.login', 'User', 3, '{"entity":"User","entity_id":3}', '::1', '2026-08-21 21:10:19'),
(5, 4, 'auth.login', 'User', 4, '{"entity":"User","entity_id":4}', '::1', '2026-08-21 21:10:32'),
(6, 1, 'auth.login', 'User', 1, '{"entity":"User","entity_id":1}', '::1', '2026-08-26 11:52:59'),
(7, 1, 'waste.report.created', 'WasteReport', 1, '{"entity":"WasteReport","entity_id":1,"category":"Household Waste"}', '::1', '2026-09-01 08:12:10'),
(8, 1, 'collection.request.created', 'CollectionRequest', 1, '{"entity":"CollectionRequest","entity_id":1}', '::1', '2026-09-01 08:15:44'),
(9, 2, 'collection.assigned', 'CollectionRequest', 1, '{"entity":"CollectionRequest","entity_id":1,"staff_id":3}', '::1', '2026-09-02 09:00:00'),
(10, 3, 'collection.completed', 'CollectionRequest', 1, '{"entity":"CollectionRequest","entity_id":1,"status":"Completed"}', '::1', '2026-09-04 14:20:00'),
(11, 1, 'recycling.submission.created', 'RecyclingSubmission', 1, '{"entity":"RecyclingSubmission","entity_id":1,"material":"Plastic"}', '::1', '2026-09-02 10:05:33'),
(12, 4, 'recycling.submission.approved', 'RecyclingSubmission', 1, '{"entity":"RecyclingSubmission","entity_id":1,"points":75}', '::1', '2026-09-05 10:45:00'),
(13, 1, 'reward.redeemed', 'RewardTransaction', 2, '{"entity":"RewardTransaction","entity_id":2,"points":-50}', '::1', '2026-09-05 11:00:00');

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

--
-- Dumping data for table `collection_requests`
--

INSERT INTO `collection_requests` (`id`, `waste_report_id`, `resident_id`, `preferred_date`, `scheduled_date`, `collection_staff_id`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 1, '2026-09-03', '2026-09-04', 3, 'Completed', 'Collected on schedule. All waste cleared.', '2026-09-01 08:15:44'),
(2, 2, 1, '2026-09-08', '2026-09-09', 3, 'Assigned', NULL, '2026-09-05 09:00:00'),
(3, 3, 1, '2026-09-12', NULL, NULL, 'Pending', NULL, '2026-09-06 10:30:00');

-- --------------------------------------------------------


CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'System',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'Collection Scheduled', 'Your waste collection has been scheduled for 04 Sep 2026.', 'Collection', 1, '2026-09-02 09:05:00'),
(2, 3, 'New Collection Assigned', 'You have been assigned a waste collection scheduled for 04 Sep 2026.', 'Collection', 1, '2026-09-02 09:05:01'),
(3, 1, 'Collection Completed', 'Your waste collection has been completed. Thank you!', 'Collection', 1, '2026-09-04 14:25:00'),
(4, 1, 'Recycling Approved', 'Your recycling submission has been approved. 75 points have been credited to your account.', 'Reward', 0, '2026-09-05 10:50:00'),
(5, 1, 'Reward Redeemed', 'Your reward redemption of 50 points has been processed successfully.', 'Reward', 0, '2026-09-05 11:05:00'),
(6, 3, 'New Collection Assigned', 'You have been assigned a waste collection scheduled for 09 Sep 2026.', 'Collection', 0, '2026-09-05 09:05:00'),
(7, 1, 'Appointment Confirmed', 'Your recycling appointment at EcoBin Setapak Recycling Centre is now Confirmed.', 'Recycling', 0, '2026-09-06 09:00:00');

-- --------------------------------------------------------


CREATE TABLE `recycling_appointments` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `appointment_at` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycling_appointments`
--

INSERT INTO `recycling_appointments` (`id`, `resident_id`, `center_id`, `appointment_at`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-09-07 10:00:00', 'Confirmed', '2026-09-03 09:30:00'),
(2, 1, 1, '2026-09-10 14:00:00', 'Pending', '2026-09-06 08:45:00');

-- --------------------------------------------------------


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

--
-- Dumping data for table `recycling_submissions`
--

INSERT INTO `recycling_submissions` (`id`, `resident_id`, `center_id`, `material`, `weight_kg`, `points`, `status`, `created_at`) VALUES
(1, 1, 1, 'Plastic', 5.00, 75, 'Approved', '2026-09-02 10:05:33'),
(2, 1, 1, 'Metal', 2.50, 50, 'Approved', '2026-09-05 10:00:00'),
(3, 1, 1, 'Paper', 3.00, 30, 'Pending', '2026-09-06 09:15:00');

-- --------------------------------------------------------


CREATE TABLE `reward_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reward_transactions`
--

INSERT INTO `reward_transactions` (`id`, `user_id`, `points`, `type`, `description`, `created_at`) VALUES
(1, 1, 75, 'Earn', 'Points earned for recycling 5.00 kg of Plastic.', '2026-09-05 10:50:00'),
(2, 1, -50, 'Redeem', 'Reward redemption: EcoBin Eco Bag (50 pts).', '2026-09-05 11:00:00'),
(3, 1, 50, 'Earn', 'Points earned for recycling 2.50 kg of Metal.', '2026-09-05 10:55:00');

-- --------------------------------------------------------


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
-- Dumping data for table `waste_reports`
--

INSERT INTO `waste_reports` (`id`, `resident_id`, `category`, `description`, `image`, `priority`, `waste_size`, `latitude`, `longitude`, `address`, `status`, `created_at`) VALUES
(1, 1, 'Household Waste', 'Large pile of household trash near the roadside. Has been there for 3 days and is attracting pests.', NULL, 'High', 'Large', 3.1882700, 101.7000500, 'Jalan Gombak, Setapak, 53000 Kuala Lumpur', 'Completed', '2026-09-01 08:12:10'),
(2, 1, 'Bulky Item', 'Old sofa and broken cabinet left outside the apartment block. Residents are complaining.', NULL, 'Normal', 'Large', 3.1901200, 101.7023400, 'Jalan Pahang, Setapak, 53000 Kuala Lumpur', 'In Progress', '2026-09-05 08:50:00'),
(3, 1, 'Hazardous Waste', 'Discarded paint cans and chemical containers found near the playground. Urgent removal needed.', NULL, 'Urgent', 'Medium', 3.1875300, 101.6998700, 'Taman Setapak Indah, 53000 Kuala Lumpur', 'Pending', '2026-09-06 10:25:00');


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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `collection_requests`
--
ALTER TABLE `collection_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recycling_appointments`
--
ALTER TABLE `recycling_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recycling_centers`
--
ALTER TABLE `recycling_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `recycling_submissions`
--
ALTER TABLE `recycling_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `waste_reports`
--
ALTER TABLE `waste_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
