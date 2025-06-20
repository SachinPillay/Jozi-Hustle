-- phpMyAdmin SQL Dump
-- Jozi Hustle Database - Clean Version
-- Created for unified user system

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Drop database if exists and create fresh
--
DROP DATABASE IF EXISTS `jozi_hustle`;
CREATE DATABASE `jozi_hustle` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `jozi_hustle`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `blocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
-- NOTE: Admin user (ID: 1) - Email: admin@jozihustle.com, Password: admin123
--

INSERT INTO `users` (`id`, `name`, `surname`, `email`, `phone`, `gender`, `dob`, `password`, `blocked`) VALUES
(1, 'Admin', 'User', 'admin@jozihustle.com', '011 123 4567', 'Male', '1990-01-01', '$2y$10$5i6JzeL6HQVBbRKs53Go9eBM071ozwp9rrsGCZrV290bJ/NeMxQz6', 0),
(2, 'Shaw', 'RE', 'ero@gmail.com', '065 445 8890', 'Male', '2004-12-03', '$2y$10$TiTe81ITYtHb1msqISl30OqVM4JZaLvUMbS/JLr412uKroHJOU.qm', 0),
(3, 'WEQ', 'fgyu', 'qw@gmail.com', '089 234 1243', 'Male', '2025-06-17', '$2y$10$2TpX9T40Ikp3O3fjzVYhaO4gCxvrtttZUPR6aqxT5ZnkBGOZmW8Mm', 0),
(4, 'Keanan', 'ERES', 'ee@gmail.com', NULL, NULL, NULL, '$2y$10$mnIR0DgG4s4Za4IpyGMuYOluvISx6s9Ps6xj15OomBBfBKUsUNFQ.', 0),
(5, 'shafiq', 'Ramla', 're@gmail.com', NULL, 'Male', '2025-06-18', '$2y$10$UdpdtCZlk3hLQuNKIQHdNeCfauI4Bb4vfuAtw3bYvltlakq8xZazS', 0),
(6, 'Keanan', 'Ramla', 'op@gmail.com', '087 667 7890', 'Male', '2025-06-23', '$2y$10$7BvOCUeWcC96Jf.K72UCD.n7LoydGbbUaWqp6jjmwNID6HaaxF5Xa', 0),
(7, 'SER', 'WE', 'ds@gmai.com', '087 556 3443', 'Male', '2025-06-23', '$2y$10$ibOSaq9WXmgdehowE6bvsuLBhkDp0DyttcBiP3o84JA17rR.GmcOy', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('Food','Electronics','Clothing','Beverages','Household Contents','Other') DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `status` enum('available','sold') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `blocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `seller_id`, `title`, `price`, `description`, `short_description`, `image`, `category`, `rating`, `status`, `blocked`) VALUES
(1, 1, 'Aromat', 35.00, 'an all-purpose savory seasoning powder produced by Knorr', NULL, 'assets/uploads/6841d7a29d06e_Knorr Aromat Original Seasoning 75 gm is a good….jpeg', 'Food', 0.00, 'available', 0),
(2, 1, 'Jungle Oats', 40.00, 'a brand of breakfast oats, particularly popular in South Africa, known for being 100% whole grain, all-natural, and high in fiber', NULL, 'assets/uploads/6841d7d2a067d_2e1421e5-0dd7-4397-bb96-8639b14e344f.jpeg', 'Food', 0.00, 'available', 0),
(3, 1, 'Willard Flings', 20.00, 'Popular in South Africa, Willards Flings are a "party in a packet" snack made from maize. They have a distinct savoury and somewhat tangy flavour and are light, airy, and melt-in-your-mouth.', NULL, 'assets/uploads/6841d899d3fce_938bb00f-c4f7-4737-b444-e522fca4aedc.jpeg', 'Food', 0.00, 'available', 0),
(4, 1, 'Technik Wireless Mouse', 125.00, 'A computer input device that uses wireless technology, such as Bluetooth or a USB receiver, to connect to a computer or other device without the need for a physical cord', NULL, 'assets/uploads/6841d9ea1b17a_Wireless Mouse RGB Rechargeable Bluetooth Mouse….jpeg', 'Electronics', 0.00, 'available', 0),
(5, 1, 'Sonic Headphones', 250.00, 'Experience immersive, high-quality sound with these sleek, comfortable headphones designed for all-day listening and crystal-clear audio.', NULL, 'assets/uploads/6841daa70ffbe_edb4b56d-d624-4037-863f-78e0303a746b.jpeg', 'Electronics', 0.00, 'available', 0),
(6, 1, 'White Socks', 38.00, 'White socks that are soft and breathable for all-day comfort and a polished, timeless appearance.', NULL, 'assets/uploads/6841dbd879098_Men\'s White Eco-Friendly Crew Socks - Nothing New®.jpeg', 'Clothing', 0.00, 'available', 0),
(7, 1, 'Microphone', 20.00, 'Professional microphone for recording and streaming', NULL, 'assets/uploads/6841dd69a74fa_35 Gifts You Can Amazon Prime.jpeg', 'Electronics', 0.00, 'available', 0);

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `buyer_id`, `seller_id`, `message`, `timestamp`, `is_read`) VALUES
(1, 2, 1, 'hello', '2025-06-05 10:36:15', 0),
(2, 3, 1, 'Is this product still available?', '2025-06-05 10:36:28', 0),
(3, 2, 1, 'do you like the product', '2025-06-05 10:43:01', 0),
(4, 4, 1, 'What is the condition?', '2025-06-05 11:02:27', 0),
(5, 3, 1, 'Can we negotiate the price?', '2025-06-05 11:03:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `ad_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `buyer_id`, `ad_id`, `created_at`) VALUES
(1, 2, 5, '2025-06-06 08:26:15'),
(2, 3, 4, '2025-06-06 08:26:15'),
(3, 4, 1, '2025-06-06 08:26:15');

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ads`
--
ALTER TABLE `ads`
  ADD CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`) ON DELETE CASCADE;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
