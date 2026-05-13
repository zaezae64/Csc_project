-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 07:38 PM
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
-- Database: `media_archive`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `Comment_ID` int(11) NOT NULL,
  `Page_ID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`Comment_ID`, `Page_ID`, `user_id`, `comment_text`, `created_at`) VALUES
(1, 2, 3, 'poop', '2026-04-30 17:35:10'),
(2, 1, 2, 'Amazing game', '2026-04-30 17:37:22');

-- --------------------------------------------------------

--
-- Table structure for table `media_images`
--

CREATE TABLE `media_images` (
  `Image_ID` int(11) NOT NULL,
  `Page_ID` int(11) NOT NULL,
  `ImagePath` varchar(255) NOT NULL,
  `UploadedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_images`
--

INSERT INTO `media_images` (`Image_ID`, `Page_ID`, `ImagePath`, `UploadedAt`) VALUES
(1, 1, 'uploads/media/media_1_1776037610_6595.jpg', '2026-04-12 23:46:50'),
(2, 2, 'uploads/media/media_2_1776136834_9101.png', '2026-04-14 03:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `media_page`
--

CREATE TABLE `media_page` (
  `Page_ID` int(11) NOT NULL,
  `Sub_ID` int(11) NOT NULL,
  `MediaDesc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_page`
--

INSERT INTO `media_page` (`Page_ID`, `Sub_ID`, `MediaDesc`) VALUES
(1, 2, 'Description pending moderator update.'),
(2, 3, 'modified description'),
(3, 4, 'paper test');

-- --------------------------------------------------------

--
-- Table structure for table `media_page_tags`
--

CREATE TABLE `media_page_tags` (
  `Page_ID` int(11) NOT NULL,
  `Tag_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_page_tags`
--

INSERT INTO `media_page_tags` (`Page_ID`, `Tag_ID`) VALUES
(1, 1),
(1, 3),
(1, 4),
(2, 5),
(3, 6);

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `Sub_ID` int(11) NOT NULL,
  `MediaName` varchar(100) NOT NULL,
  `SubmitDesc` text NOT NULL,
  `User_ID` int(11) NOT NULL,
  `AcceptStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`Sub_ID`, `MediaName`, `SubmitDesc`, `User_ID`, `AcceptStatus`) VALUES
(2, 'Zelda: Majora\'s Mask', '', 2, 'Accepted'),
(3, 'Mario 64', '', 2, 'Accepted'),
(4, 'Paper Mario', 'paper test', 3, 'Accepted'),
(5, 'Metroid', 'Metroid is epic', 3, 'Accepted');

-- --------------------------------------------------------

--
-- Table structure for table `submission_tags`
--

CREATE TABLE `submission_tags` (
  `Sub_ID` int(11) NOT NULL,
  `Tag_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_tags`
--

INSERT INTO `submission_tags` (`Sub_ID`, `Tag_ID`) VALUES
(2, 1),
(2, 3),
(2, 4),
(3, 5),
(4, 6),
(5, 7);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `Tag_ID` int(11) NOT NULL,
  `TagName` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`Tag_ID`, `TagName`) VALUES
(1, 'Nintendo'),
(2, 'Video Game'),
(3, 'test'),
(4, 'zelda'),
(5, 'Mario'),
(6, 'RPG'),
(7, 'shooter');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `usertype` varchar(20) NOT NULL,
  `account_status` varchar(20) NOT NULL,
  `FlairTags` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL DEFAULT '',
  `hint_question` varchar(255) NOT NULL DEFAULT '',
  `hint_answer` varchar(255) NOT NULL DEFAULT '',
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `usertype`, `account_status`, `FlairTags`, `password_hash`, `hint_question`, `hint_answer`, `profile_image`, `bio`) VALUES
(1, 'testuser', 'member', 'active', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'What was the name of your first pet?', 'fido', NULL, NULL),
(2, 'cactus', 'admin', 'active', '', '$2y$10$5akfhCdf2sKZTAckWXtYaeUzk8McVGeDLf23rUpIHC2N0f4sTxyNi', 'test', 'test', 'uploads/profile/profile_2_1777244542.png', NULL),
(3, 'Zaezae', 'standard', 'active', '', '$2y$10$CkBmoAmZdrrnzxBvwdT9UuSWTj8baxXn3.sa.aJLt6R9xep/9H6Ne', 'test', 'test', 'uploads/profile/profile_3_1777244836.png', 'This is a test for zaezae64');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`Comment_ID`),
  ADD KEY `Page_ID` (`Page_ID`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media_images`
--
ALTER TABLE `media_images`
  ADD PRIMARY KEY (`Image_ID`),
  ADD KEY `Page_ID` (`Page_ID`);

--
-- Indexes for table `media_page`
--
ALTER TABLE `media_page`
  ADD PRIMARY KEY (`Page_ID`),
  ADD KEY `Sub_ID` (`Sub_ID`);

--
-- Indexes for table `media_page_tags`
--
ALTER TABLE `media_page_tags`
  ADD KEY `Page_ID` (`Page_ID`),
  ADD KEY `Tag_ID` (`Tag_ID`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`Sub_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `submission_tags`
--
ALTER TABLE `submission_tags`
  ADD PRIMARY KEY (`Sub_ID`,`Tag_ID`),
  ADD KEY `Tag_ID` (`Tag_ID`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`Tag_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `Comment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media_images`
--
ALTER TABLE `media_images`
  MODIFY `Image_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `media_page`
--
ALTER TABLE `media_page`
  MODIFY `Page_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `Sub_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `Tag_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`Page_ID`) REFERENCES `media_page` (`Page_ID`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `media_images`
--
ALTER TABLE `media_images`
  ADD CONSTRAINT `media_images_ibfk_1` FOREIGN KEY (`Page_ID`) REFERENCES `media_page` (`Page_ID`);

--
-- Constraints for table `media_page`
--
ALTER TABLE `media_page`
  ADD CONSTRAINT `media_page_ibfk_1` FOREIGN KEY (`Sub_ID`) REFERENCES `submission` (`Sub_ID`);

--
-- Constraints for table `media_page_tags`
--
ALTER TABLE `media_page_tags`
  ADD CONSTRAINT `media_page_tags_ibfk_1` FOREIGN KEY (`Page_ID`) REFERENCES `media_page` (`Page_ID`),
  ADD CONSTRAINT `media_page_tags_ibfk_2` FOREIGN KEY (`Tag_ID`) REFERENCES `tags` (`Tag_ID`);

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `submission_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `submission_tags`
--
ALTER TABLE `submission_tags`
  ADD CONSTRAINT `submission_tags_ibfk_1` FOREIGN KEY (`Sub_ID`) REFERENCES `submission` (`Sub_ID`),
  ADD CONSTRAINT `submission_tags_ibfk_2` FOREIGN KEY (`Tag_ID`) REFERENCES `tags` (`Tag_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
