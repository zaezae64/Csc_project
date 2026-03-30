-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 07:20 PM
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
-- Table structure for table `media_page`
--

CREATE TABLE `media_page` (
  `Page_ID` int(11) NOT NULL,
  `Sub_ID` int(11) NOT NULL,
  `MediaDesc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_page_tags`
--

CREATE TABLE `media_page_tags` (
  `Page_ID` int(11) NOT NULL,
  `Tag_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `Sub_ID` int(11) NOT NULL,
  `MediaName` varchar(100) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `AcceptStatus` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_tags`
--

CREATE TABLE `submission_tags` (
  `Sub_ID` int(11) NOT NULL,
  `Tag_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `Tag_ID` int(11) NOT NULL,
  `TagName` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `usertype` varchar(20) NOT NULL,
  `account_status` varchar(20) NOT NULL,
  `FlairTags` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

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
  ADD PRIMARY KEY (`Sub_ID`),
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
-- AUTO_INCREMENT for table `media_page`
--
ALTER TABLE `media_page`
  MODIFY `Page_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `Sub_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission_tags`
--
ALTER TABLE `submission_tags`
  MODIFY `Sub_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `Tag_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
