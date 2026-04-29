-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 08:05 AM
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
-- Database: `agriflow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `device_controls`
--

CREATE TABLE `device_controls` (
  `id` int(11) NOT NULL,
  `mode` varchar(10) DEFAULT 'manual',
  `pump_status` tinyint(1) DEFAULT 0,
  `selected_plant` varchar(50) DEFAULT 'general',
  `initialized` tinyint(1) DEFAULT 0,
  `auto_lock` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_controls`
--

INSERT INTO `device_controls` (`id`, `mode`, `pump_status`, `selected_plant`, `initialized`, `auto_lock`) VALUES
(1, 'manual', 0, 'general', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pump_logs`
--

CREATE TABLE `pump_logs` (
  `id` int(11) NOT NULL,
  `start_time` datetime DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `liters_used` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pump_logs`
--

INSERT INTO `pump_logs` (`id`, `start_time`, `end_time`, `duration_seconds`, `liters_used`) VALUES
(41, '2026-04-26 16:04:53', '2026-04-26 16:05:02', 9, 0),
(42, '2026-04-26 16:05:29', '2026-04-26 16:05:49', 20, 0),
(43, '2026-04-26 16:05:54', '2026-04-26 16:06:25', 31, 0),
(44, '2026-04-26 16:14:56', '2026-04-26 16:14:57', 1, 0.033),
(45, '2026-04-26 16:15:07', '2026-04-26 16:15:33', 26, 0.858),
(46, '2026-04-26 16:16:55', '2026-04-26 16:17:21', 26, 0.858);

-- --------------------------------------------------------

--
-- Table structure for table `sensor_logs`
--

CREATE TABLE `sensor_logs` (
  `id` int(11) NOT NULL,
  `moisture` float DEFAULT NULL,
  `temp` float DEFAULT NULL,
  `ph` float DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_logs`
--

INSERT INTO `sensor_logs` (`id`, `moisture`, `temp`, `ph`, `timestamp`) VALUES
(3130, 3.3, 33.7, 6.6, '2026-04-26 08:37:50'),
(3131, 3.3, 33.7, 6.5, '2026-04-26 08:37:55'),
(3132, 3.3, 33.7, 6.5, '2026-04-26 08:38:00'),
(3133, 3.3, 33.7, 6.4, '2026-04-26 08:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$V8eg2MSJglqkC3vkl1ge.u7B.BFUZq9TZvsneWl6eZeV3Vty26mk.', '2026-04-02 08:54:09'),
(3, 'Raffy Tulfo Action', 'raffy@gmail.com', '$2y$10$/leKRmlZ6LkppXlIRVF33Oi.ex6l6d1F7gX1nt55J9wxWQJ9qVbVK', '2026-04-27 08:32:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `device_controls`
--
ALTER TABLE `device_controls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pump_logs`
--
ALTER TABLE `pump_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sensor_logs`
--
ALTER TABLE `sensor_logs`
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
-- AUTO_INCREMENT for table `pump_logs`
--
ALTER TABLE `pump_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `sensor_logs`
--
ALTER TABLE `sensor_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3134;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
