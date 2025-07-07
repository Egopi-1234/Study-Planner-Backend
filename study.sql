-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2025 at 06:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `study`
--

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `due_time` time NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `name`, `subject`, `due_date`, `due_time`, `file_path`, `user_id`, `created_at`) VALUES
(4, 'Chapter 1 Notes', 'Biology', '2025-05-20', '15:00:00', 'uploads/materials/1746964785_Screenshot 2025-03-25 084000.png', 2, '2025-05-11 11:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `password_otps`
--

CREATE TABLE `password_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_otps`
--

INSERT INTO `password_otps` (`id`, `email`, `otp`, `created_at`, `expires_at`) VALUES
(1, 'vishnu@gmail.com', '931243', '2025-05-11 14:34:40', '2025-05-11 14:39:40'),
(2, 'vishnu@gmail.com', '791546', '2025-05-11 14:35:55', '2025-05-11 14:40:55');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL,
  `description` varchar(500) NOT NULL,
  `Date` date NOT NULL,
  `time` time NOT NULL,
  `Priority` enum('High','Medium','Low') NOT NULL,
  `user_Id` int(11) NOT NULL,
  `status` enum('Pending','Complete') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `description`, `Date`, `time`, `Priority`, `user_Id`, `status`) VALUES
(2, 'daily', 'todo it ', '2026-04-15', '12:00:00', 'High', 2, 'Pending'),
(3, 'check 2', 'todo it ', '2025-05-12', '12:00:00', 'Medium', 2, 'Complete'),
(4, 'daily', 'todo it ', '2025-10-12', '12:00:00', 'Low', 2, 'Pending'),
(5, 'daily', 'todo it ', '2025-10-12', '12:00:00', 'High', 2, 'Pending'),
(6, 'check 1', 'todo it ', '2025-01-01', '12:00:00', 'High', 2, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` int(10) DEFAULT NULL,
  `Dept_info` varchar(225) DEFAULT NULL,
  `profile_image` varchar(500) NOT NULL DEFAULT 'upload/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `Username`, `email`, `password`, `phone`, `Dept_info`, `profile_image`) VALUES
(2, 'vishnu', 'vishnu@gmail.com', '12345679', 2147483647, 'B.E (IT),4 years', 'uploads/img_682042f060855.png'),
(3, 'gopie', 'gopie852@gmail.com', '12345679', NULL, NULL, 'upload/default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_otps`
--
ALTER TABLE `password_otps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_Id` (`user_Id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_otps`
--
ALTER TABLE `password_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_Id`) REFERENCES `users` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
