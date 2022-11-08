-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2022 at 04:59 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticket_module`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE `admin_user` (
    `admin_id` int(8) NOT NULL,
    `email` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `cid` varchar(19) NOT NULL,
    `level` varchar(5) NOT NULL,
    `session_id` varchar(255),
    `created_on` datetime DEFAULT current_timestamp()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  INSERT INTO `admin_user` (email,name,password,cid,level) VALUES("admin@admin.bt","Administrator",MD5("admin@admin.bt"),"00000000000","0");
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `citizens`
  --
  
  CREATE TABLE `citizens` (
    `cid` varchar(19) NOT NULL,
    `dob` date NOT NULL,
    `dzongkhag` varchar(255) NOT NULL,
    `first_name` varchar(255) NOT NULL,
    `middle_name` varchar(255) NOT NULL,
    `last_name` varchar(255) NOT NULL,
    `phonenumber` varchar(15) NOT NULL,
    `image_id` int(8) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `events`
  --
  
  CREATE TABLE `events` (
    `id` int(8) NOT NULL,
    `name` varchar(255) NOT NULL,
    `address` varchar(255) NOT NULL,
    `capacity` int(6) NOT NULL,
    `country` varchar(255) NOT NULL,
    `start_datetime` datetime NOT NULL,
    `end_datetime` datetime NOT NULL,
    `image_id` int(8) NOT NULL,
    `ticket_offset` int(15) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `images`
  --
  
  CREATE TABLE `images` (
    `id` int(8) NOT NULL,
    `bin` blob NOT NULL,
    `format` varchar(5) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `logs`
  --
  
  CREATE TABLE `logs` (
    `admin_id` int(8) NOT NULL,
    `action` varchar(1024) NOT NULL,
    `datetime` datetime DEFAULT current_timestamp(),
    `event_id` int(8) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `luckydraw`
  --
  
  CREATE TABLE `luckydraw` (
    `event_id` int(8) NOT NULL,
    `no_of_winners` int(3) NOT NULL,
    `no_of_consolations` int(3) NOT NULL,
    `is_drawing` tinyint(1) NOT NULL DEFAULT 0,
    `winners` varchar(2048) NOT NULL,
    `consolation_winners` varchar(2048) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `minor`
  --
  
  CREATE TABLE `minor` (
    `cid` varchar(19) NOT NULL,
    `dob` date NOT NULL,
    `first_name` varchar(255) NOT NULL,
    `middle_name` varchar(255) NOT NULL,
    `last_name` varchar(255) NOT NULL,
    `parent_cid` varchar(19) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `otp`
  --
  
  CREATE TABLE `otp` (
    `cid` int(8) NOT NULL,
    `otp` varchar(10) NOT NULL,
    `valid_till` datetime NOT NULL,
    `attempts` int(3) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `registration_requests`
  --
  
  CREATE TABLE `registration_requests` (
    `id` int(8) NOT NULL,
    `event_id` int(8) NOT NULL,
    `cid` varchar(19) NOT NULL,
    `register_datetime` datetime DEFAULT current_timestamp(),
    `other_cids` varchar(1024) NOT NULL,
    `withdrawn` tinyint(1) NOT NULL DEFAULT 0,
    `is_allowed` tinyint(1) NOT NULL DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  --
  -- Indexes for dumped tables
  --
  
  --
  -- Indexes for table `admin_user`
  --
  ALTER TABLE `admin_user`
    ADD PRIMARY KEY (`admin_id`);
  
  --
  -- Indexes for table `citizens`
  --
  ALTER TABLE `citizens`
    ADD PRIMARY KEY (`cid`);
  
  --
  -- Indexes for table `events`
  --
  ALTER TABLE `events`
    ADD PRIMARY KEY (`id`);
  
  --
  -- Indexes for table `images`
  --
  ALTER TABLE `images`
    ADD PRIMARY KEY (`id`);
  
  --
  -- Indexes for table `logs`
  --
  ALTER TABLE `logs`
    ADD PRIMARY KEY (`admin_id`);
  
  --
  -- Indexes for table `luckydraw`
  --
  ALTER TABLE `luckydraw`
    ADD PRIMARY KEY (`event_id`);
  
  --
  -- Indexes for table `minor`
  --
  ALTER TABLE `minor`
    ADD PRIMARY KEY (`cid`);
  
  --
  -- Indexes for table `otp`
  --
  ALTER TABLE `otp`
    ADD PRIMARY KEY (`cid`);
  
  --
  -- Indexes for table `registration_requests`
  --
  ALTER TABLE `registration_requests`
    ADD PRIMARY KEY (`id`);
  COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
