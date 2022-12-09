-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 09, 2022 at 01:54 PM
-- Server version: 10.3.37-MariaDB-0ubuntu0.20.04.1
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+06:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticket_module`
--
CREATE DATABASE IF NOT EXISTS `ticket_module` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ticket_module`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE IF NOT EXISTS `admin_user` (
  `admin_id` int(8) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cid` varchar(19) NOT NULL,
  `level` varchar(5) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_on` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_user`
--

INSERT INTO `admin_user` (`admin_id`, `email`, `name`, `password`, `cid`, `level`, `session_id`, `created_on`) VALUES(1, 'admin@admin.bt', 'Administrator', 'a289d649338577b8d3792f1fbb7b16cd', '00000000000', '0', 'ifuhvgj0ao1ssk2eotbhnrjt2o', '2022-11-08 17:07:07');
INSERT INTO `admin_user` (`admin_id`, `email`, `name`, `password`, `cid`, `level`, `session_id`, `created_on`) VALUES(2, 'kyoezer@moic.gov.bt', 'Kinley Yoezer', '77c4f518fbe0503f5db28772e06dc9a4', '11508003516', '1', 'ms6mad9gsmksng3gb4p9flai0l', '2022-11-22 10:10:33');
INSERT INTO `admin_user` (`admin_id`, `email`, `name`, `password`, `cid`, `level`, `session_id`, `created_on`) VALUES(3, 'phuntsho.gayenden@gmail.com', 'Phuntsho Gayenden', '48456e6c0db15d0250ceff3881b8aa11', '11512005551', '1', 'up997dn0qllq8ljb4ddjhgbl9f', '2022-11-22 15:26:40');
INSERT INTO `admin_user` (`admin_id`, `email`, `name`, `password`, `cid`, `level`, `session_id`, `created_on`) VALUES(4, 'unamgay@dit.gov.bt', 'Ugyen Namgay', '78650c88c3b6d2738aec38809907149c', '11302000754', '1', 'kc842gj99o9ds3p33lqjqbphhb', '2022-11-24 10:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `citizens`
--

CREATE TABLE IF NOT EXISTS `citizens` (
  `cid` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `dzongkhag` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phonenumber` varchar(15) NOT NULL,
  `image_id` int(8) NOT NULL,
  `gender` varchar(10) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizen_roles`
--

CREATE TABLE IF NOT EXISTS `citizen_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` varchar(25) NOT NULL,
  `role` varchar(50) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `capacity` int(6) NOT NULL,
  `country` varchar(255) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `image_id` int(8) NOT NULL,
  `ticket_offset` int(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `bin` mediumblob NOT NULL,
  `format` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `admin_id` varchar(255) NOT NULL,
  `action` varchar(1024) NOT NULL,
  `datetime` datetime DEFAULT current_timestamp(),
  `event_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `luckydraw`
--

CREATE TABLE IF NOT EXISTS `luckydraw` (
  `ticket` varchar(15) NOT NULL,
  `cid` varchar(19) NOT NULL,
  `event_id` int(5) NOT NULL,
  `is_winner` tinyint(1) NOT NULL DEFAULT 0,
  `selected_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `minor`
--

CREATE TABLE IF NOT EXISTS `minor` (
  `cid` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `parent_cid` varchar(19) NOT NULL,
  `gender` varchar(10) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE IF NOT EXISTS `otp` (
  `cid` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `valid_till` datetime NOT NULL,
  `attempts` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_requests`
--

CREATE TABLE IF NOT EXISTS `registration_requests` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `event_id` int(8) NOT NULL,
  `cid` varchar(19) NOT NULL,
  `register_datetime` datetime DEFAULT current_timestamp(),
  `other_cids` varchar(1024) NOT NULL,
  `withdrawn` tinyint(1) NOT NULL DEFAULT 0,
  `dzongkhag` varchar(50) NOT NULL,
  `gewog` varchar(50) NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
