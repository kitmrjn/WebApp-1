-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2025 at 07:26 AM
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
-- Database: `vincenthinks_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `helpful_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answer_ratings`
--

CREATE TABLE `answer_ratings` (
  `id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answer_reports`
--

CREATE TABLE `answer_reports` (
  `id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `question_type` enum('course','general') NOT NULL DEFAULT 'general',
  `category` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `user_id`, `title`, `content`, `created_at`, `question_type`, `category`, `status`) VALUES
(141, 19, 'BSBA', 'Test', '2025-04-02 05:56:57', 'general', 'BSBA', 'approved'),
(142, 6, 'BSHM', 'sdsad', '2025-04-02 06:00:36', 'general', 'BSHM', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `question_photos`
--

CREATE TABLE `question_photos` (
  `photo_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `photo_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_photos`
--

INSERT INTO `question_photos` (`photo_id`, `question_id`, `photo_path`) VALUES
(119, 141, '../uploads/67ecd1a9f33b9.jpg'),
(120, 141, '../uploads/67ecd1aa0ff26.jpg'),
(121, 142, '../uploads/67ecd28447979.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course` varchar(50) DEFAULT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `profile_picture`, `password`, `created_at`, `course`, `student_number`, `role`) VALUES
(1, 'Jerald', 'plukjerald.samonte@gmail.com', NULL, '$2y$10$WCkS6kHuYkKJJWUqoJLqmOALgo09RASrUajBAHmrvrPjvnu6Qrx8.', '2025-03-08 07:53:19', NULL, NULL, 'admin'),
(2, 'Kenley', 'kenleysamonte26@gmail.com', NULL, '$2y$10$1L8/WZLu.0S3RZN2ikznpuV/P8ggeGn4dyxprbNGezfFqlTSCjNpS', '2025-03-08 08:25:46', NULL, NULL, 'user'),
(3, 'Kit', 'kitmarjohnb@gmail.com', 'uploads/profile_pictures/user_3_67eca157bdfbd.png', '123456789', '2025-03-09 09:08:50', 'FACULTY', 'AY2022-01297', 'admin'),
(4, '123', 'dada', NULL, '$2y$10$atFpdaypJjcnUQ0LJH.rPuqXslFj7q5pI.iKdmKDRWGrh94q9IQcK', '2025-03-10 15:51:36', NULL, NULL, 'user'),
(5, 'Jin', 'Jinhular26@gmail.com', NULL, '$2y$10$QVbNse.x2TE/3PePMvC2GuayodJadQ8h2Ub/Gx1HHn0I8k.u9hcae', '2025-03-10 15:58:22', NULL, NULL, 'user'),
(6, 'Albert', 'test@gmail.com', 'uploads/profile_pictures/user_6_67eca0b08d0dd.jpg', '$2y$10$yN5VqN6TOf9jOpbXgSHB3ey4viPxkvWf4D5oHQV6KgDn1kTrFGqSW', '2025-03-11 16:39:55', 'BSIT', 'AY2022-01298', 'admin'),
(7, 'lalaq', 'albertdowwminicsragol03@gmail.com', NULL, '$2y$10$b6D/hGuclG1ScpXuSE11h.FRB6rpP84qN8Vj7LTIq4nYJrkTk49b.', '2025-03-11 17:32:44', 'BSED', NULL, 'user'),
(8, 'Angelica', 'angelicalim@gmail.com', NULL, '$2y$10$geBxlvivEnjahix403rAcun6rfSwQyKkq8z0tSrTa1RpIrgDwX5ji', '2025-03-11 17:46:14', 'ICT', NULL, 'user'),
(9, 'ASsddwd', 'wdadwadadw@gmail.com', NULL, '$2y$10$ji5zcbFdPSR4eK8NiIYouu9ECpDNdGReRpUCNK8jLz6nDmsq44DRi', '2025-03-11 17:50:26', 'ICT', NULL, 'user'),
(10, 'maniga', 'maniga@gmail.com', NULL, '$2y$10$D6KZ4dFg8BKUEx9rrYlnn.apaMTfEi5x6pMAOGYu8cqZ/OlypRate', '2025-03-20 16:11:52', 'Elementary', NULL, 'user'),
(11, 'testhost', 'hest@gmail.com', NULL, '$2y$10$PMC5ImMUzrbq8eBgid4fNuGAOoV3BLQuMXYu5PXf6f7FnxlCM0wIy', '2025-03-27 09:19:54', 'GAS', NULL, 'user'),
(12, 'inamibert', 'bert@gmail.com', 'uploads/profile_pictures/user_12_67e6b9db99882.jpg', '$2y$10$CHD7eq.Ed3mRMvgNXV0fce7UhIBi1yY9dK2.Vh39r9jnZJwsemFIS', '2025-03-27 13:46:33', 'BSIT', NULL, 'user'),
(13, 'RojAngelo', 'albert@gmail.com', 'uploads/profile_pictures/user_13_67e6db69b8f6d.jpg', '$2y$10$boev2emyGUbQBDznNf2TMeYiPVwG2CGfNZJw4xJEPsl3ZKxRkpEhK', '2025-03-28 16:42:07', 'BSIT', NULL, 'user'),
(14, 'Noela-Quirante', 'noelaquirante@gmail.com', NULL, '$2y$10$jMcIj.EA/r09EptgdXIPLuxIIOamKV41d4T9yg0uvQ75WJWdMDybG', '2025-04-01 06:10:54', 'BSIT', NULL, 'user'),
(15, 'Lihaya', 'kumbaga@gmail.clm', NULL, '$2y$10$p4nB0xv1qynwW3CYgK4luOZREG7A8Am.rubq1gMBsH8aYwvouKJGe', '2025-04-01 06:28:28', 'BSBA', NULL, 'user'),
(16, 'KitMarjohn', 'mjbolwar@gmail.com', NULL, '$2y$10$xYjTmc7wvVZRdQDvpbtjceUvjllnGsh8RAO.KRbzllDRtHcBX8BQC', '2025-04-01 10:26:42', 'BSIT', NULL, 'user'),
(17, 'lehaya', 'lehaya@gmail.com', NULL, '$2y$10$nqWkgU3W8iCIGAYEqw.efO4nZafdHVXCVPQ4vvDTQJJYCbm81DBqm', '2025-04-02 02:32:38', 'ICT', NULL, 'user'),
(18, 'Kit Marjohn Bagasol', 'kitbagasolpogi@outlook.com', NULL, '$2y$10$nJxV8lIULOAl3CX1970OFebQAzphFT2InW49w04U8CA6CPgCNOLIm', '2025-04-02 04:11:07', 'GAS', 'AY2022-01296', 'user'),
(19, 'Rilliane Divina', 'divina@gmail.com', NULL, '$2y$10$NImXRfNExSyD4mstxDaWKODJKe2BLPjJ0dxOnlwRaFe3dBDUU.pvC', '2025-04-02 04:39:54', 'BSIT', 'AY2022-01299', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `answer_ratings`
--
ALTER TABLE `answer_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`answer_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `answer_reports`
--
ALTER TABLE `answer_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_id` (`answer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `fk_questions_users` (`user_id`);

--
-- Indexes for table `question_photos`
--
ALTER TABLE `question_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `answer_ratings`
--
ALTER TABLE `answer_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `answer_reports`
--
ALTER TABLE `answer_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `question_photos`
--
ALTER TABLE `question_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `answer_ratings`
--
ALTER TABLE `answer_ratings`
  ADD CONSTRAINT `answer_ratings_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`answer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answer_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `answer_reports`
--
ALTER TABLE `answer_reports`
  ADD CONSTRAINT `answer_reports_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`answer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answer_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_photos`
--
ALTER TABLE `question_photos`
  ADD CONSTRAINT `question_photos_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
