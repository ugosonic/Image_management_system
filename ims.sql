-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2025 at 01:08 AM
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
-- Database: `ims`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`name`, `value`) VALUES
('currency_sign', '£');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(7) NOT NULL,
  `doctor_id` varchar(7) NOT NULL,
  `test_category_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Requested','In Progress','Completed') DEFAULT 'Requested',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(7) NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `condition` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `usergroup` enum('Patient') DEFAULT 'Patient'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_id`, `name`, `title`, `email`, `password`, `phone`, `address`, `date_of_birth`, `condition`, `created_at`, `usergroup`) VALUES
(1, '2738260', 'kingsley Aguagwa ', 'Mr. ', 'ugosonic@gmail.com', 'ugosoni', '07459943902', '66 All Saints Road', '2006-06-06', '', '2025-01-06 13:53:20', 'Patient'),
(3, '1632111', 'Kingsley Ugonna Aguagwa', 'mr', 'test@gmail.com', '$2y$10$888nW20rNPYbtxpvVF7SG.DHCA86/4RMxxlSdCW9lqq8vFST7jete', '07459943902', '66 All Saints Road', '2016-03-09', '', '2025-01-06 14:20:05', 'Patient'),
(4, '9496367', 'Ivy Jones', 'Mrs', 'ivy@gmail.com', '$2y$10$hwTOiP1ZFJFbhtAuq5ANfOVdERnsnSOjpaC6KReugpZPIltuV1J5G', '07459943902', '66 All Saints Road', '1998-12-07', '', '2025-01-20 23:06:29', 'Patient');

-- --------------------------------------------------------

--
-- Table structure for table `radiology_images`
--

CREATE TABLE `radiology_images` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `radiology_images`
--

INSERT INTO `radiology_images` (`id`, `request_id`, `image_path`, `description`, `uploaded_by`, `uploaded_at`) VALUES
(7, 1, 'uploads/category_4/Skull_2_NAME^NONE_Volume_2.jpg', 'back of the skull', 'Unknown Staff', '2025-01-13 23:30:52'),
(8, 1, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', 'side of the skulll', 'Unknown Staff', '2025-01-13 23:31:30'),
(9, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', 'Area of the picture ', 'Unknown Staff', '2025-01-14 00:07:05'),
(10, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-14 00:07:05'),
(11, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_2.jpg', '', 'Unknown Staff', '2025-01-14 00:07:05'),
(13, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', 'Making it the best image ', 'Unknown Staff', '2025-01-15 08:29:44'),
(14, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_3.jpg', 'Testing this code ', 'Unknown Staff', '2025-01-15 08:51:36'),
(15, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', 'ok', 'Unknown Staff', '2025-01-15 08:53:33'),
(16, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', 'This is the skull of a human being ', 'Unknown Staff', '2025-01-15 08:54:24'),
(17, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-15 09:12:50'),
(18, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-15 09:19:21'),
(19, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-15 09:21:35'),
(20, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', '', '1', '2025-01-15 09:24:46'),
(21, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_3.jpg', '', '1', '2025-01-15 09:28:22'),
(22, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', '1', '2025-01-15 09:33:56'),
(23, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_3.jpg', '', '1', '2025-01-15 09:35:00'),
(24, 4, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', '', 'Unknown Staff', '2025-01-15 09:59:39'),
(25, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', '', '1', '2025-01-15 10:04:07'),
(26, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', '1', '2025-01-15 10:07:11'),
(27, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-15 10:31:03'),
(28, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-15 10:34:41'),
(29, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Unknown Staff', '2025-01-15 10:35:44'),
(30, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_3.jpg', '', 'Unknown Staff', '2025-01-15 11:05:25'),
(31, 5, 'uploads/category_4/Skull_2_NAME^NONE_Volume_3.jpg', '', 'Kingsley Ugonna Aguagwa', '2025-01-15 11:10:16'),
(32, 6, 'uploads/category_1/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Kingsley Ugonna Aguagwa', '2025-01-15 11:11:51'),
(34, 7, 'uploads/category_1/Skull_2_NAME^NONE_Volume_0.PNG', '', 'Kingsley Ugonna Aguagwa', '2025-01-15 11:42:49'),
(35, 8, 'uploads/category_2/Skull_2_NAME^NONE_Volume_1.jpg', '', 'Kingsley Ugonna Aguagwa', '2025-01-15 20:35:25'),
(36, 9, 'uploads/category_1/Skull_2_NAME^NONE_Volume_3.jpg', '', 'Kingsley Ugonna Aguagwa', '2025-01-16 21:32:17'),
(37, 14, 'uploads/category_4/Skull_2_NAME^NONE_Volume_0.PNG', 'this is a skull', 'Kingsley Ugonna Aguagwa', '2025-01-20 23:57:19');

-- --------------------------------------------------------

--
-- Table structure for table `radiology_requests`
--

CREATE TABLE `radiology_requests` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(7) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending',
  `completed_at` datetime DEFAULT NULL,
  `completed_by` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `radiology_requests`
--

INSERT INTO `radiology_requests` (`id`, `patient_id`, `category_id`, `subcategory_id`, `created_at`, `status`, `completed_at`, `completed_by`) VALUES
(1, '1632111', 4, 6, '2025-01-12 17:20:55', 'Completed', NULL, NULL),
(2, '2738260', 2, 2, '2025-01-12 18:47:01', 'Completed', NULL, NULL),
(3, '1632111', 1, 1, '2025-01-13 13:40:10', 'Completed', NULL, NULL),
(4, '1632111', 4, 6, '2025-01-14 00:06:17', 'Completed', NULL, '0'),
(5, '1632111', 4, 6, '2025-01-15 09:33:40', 'Completed', '2025-01-15 11:00:35', 'king'),
(6, '1632111', 1, 1, '2025-01-15 11:11:33', 'Completed', '2025-01-15 12:11:55', 'Kingsley'),
(7, '1632111', 1, 1, '2025-01-15 11:26:08', 'Completed', '2025-01-15 12:26:14', 'Kingsley Ug'),
(8, '1632111', 2, 2, '2025-01-15 20:34:45', 'Completed', '2025-01-15 21:35:40', 'Kingsley Ug'),
(9, '1632111', 1, 1, '2025-01-16 21:28:30', 'Completed', '2025-01-16 22:32:18', 'Kingsley Ugonna Aguagwa'),
(10, '1632111', 4, 6, '2025-01-17 08:29:38', 'Pending', NULL, NULL),
(11, '1632111', 2, 2, '2025-01-17 08:57:22', 'Paid', NULL, NULL),
(12, '1632111', 4, 6, '2025-01-20 22:16:57', 'Pending', NULL, NULL),
(13, '1632111', 1, 1, '2025-01-20 22:36:59', 'Pending', NULL, NULL),
(14, '9496367', 4, 6, '2025-01-20 23:56:06', 'Completed', '2025-01-21 00:57:30', 'Kingsley Ugonna Aguagwa');

-- --------------------------------------------------------

--
-- Table structure for table `staff_registration`
--

CREATE TABLE `staff_registration` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(7) NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `usergroup` enum('Doctor','Radiologist') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_registration`
--

INSERT INTO `staff_registration` (`id`, `staff_id`, `name`, `title`, `email`, `password`, `phone`, `usergroup`, `created_at`) VALUES
(1, '1740984', 'Kingsley Ugonna Aguagwa', 'Mr', 'radiologist@gmail.com', '$2y$10$NyB5bCYnBdBb1Oej4g9gvO3UMt1zOEXa4i7vCAMolNXOvyfpGOLyK', '07459943902', 'Radiologist', '2025-01-10 16:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `test_categories`
--

CREATE TABLE `test_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) DEFAULT 0.00,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_categories`
--

INSERT INTO `test_categories` (`id`, `category_name`, `created_at`, `price`, `file_path`) VALUES
(1, 'MRI', '2025-01-11 23:52:28', 0.00, 'uploads/category_1'),
(2, 'x-RAY', '2025-01-11 23:53:38', 0.00, 'uploads/category_2'),
(4, 'CT-SCAN', '2025-01-12 08:24:33', 0.00, 'uploads/category_4');

-- --------------------------------------------------------

--
-- Table structure for table `test_subcategories`
--

CREATE TABLE `test_subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_subcategories`
--

INSERT INTO `test_subcategories` (`id`, `category_id`, `subcategory_name`, `price`, `created_at`) VALUES
(1, 1, 'Head MRI', 250.00, '2025-01-11 23:53:00'),
(2, 2, 'Left- Lower Limb X-ray', 125.00, '2025-01-11 23:55:12'),
(3, 2, 'Right  Lower Limb X-ray ', 128.00, '2025-01-11 23:56:05'),
(6, 4, 'Chest Scan', 167.00, '2025-01-12 08:31:38'),
(8, 4, 'head scan ', 40.00, '2025-01-20 23:55:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `patient_id` (`patient_id`);

--
-- Indexes for table `radiology_images`
--
ALTER TABLE `radiology_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `radiology_requests`
--
ALTER TABLE `radiology_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_registration`
--
ALTER TABLE `staff_registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `staff_id` (`staff_id`);

--
-- Indexes for table `test_categories`
--
ALTER TABLE `test_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_subcategories`
--
ALTER TABLE `test_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `radiology_images`
--
ALTER TABLE `radiology_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `radiology_requests`
--
ALTER TABLE `radiology_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `staff_registration`
--
ALTER TABLE `staff_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test_categories`
--
ALTER TABLE `test_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `test_subcategories`
--
ALTER TABLE `test_subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `test_subcategories`
--
ALTER TABLE `test_subcategories`
  ADD CONSTRAINT `test_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `test_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
