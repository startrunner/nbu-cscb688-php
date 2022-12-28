-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2022 at 07:55 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_chat`
--
CREATE DATABASE IF NOT EXISTS `php_chat` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `php_chat`;

-- --------------------------------------------------------

--
-- Table structure for table `personal_message`
--

DROP TABLE IF EXISTS `personal_message`;
CREATE TABLE IF NOT EXISTS `personal_message` (
  `id` varchar(36) NOT NULL,
  `ref_sender` varchar(36) NOT NULL,
  `ref_recipient` varchar(36) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `text` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ref_recipient` (`ref_recipient`),
  KEY `ref_sender` (`ref_sender`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `id` varchar(36) NOT NULL,
  `ref_user` varchar(36) NOT NULL,
  `expiry_time` datetime NOT NULL,
  `cookie_value` varchar(300) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cookie_value` (`cookie_value`),
  KEY `fk__session__ref_user` (`ref_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` varchar(36) NOT NULL,
  `login` varchar(100) NOT NULL,
  `pass_hash` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `personal_message`
--
ALTER TABLE `personal_message`
  ADD CONSTRAINT `fk__personal_message__ref_recipient` FOREIGN KEY (`ref_recipient`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk__personal_message__ref_sender` FOREIGN KEY (`ref_sender`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `fk__session__ref_user` FOREIGN KEY (`ref_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
