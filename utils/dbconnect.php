<?php

//namespace dbconnect;

$prerequisite='

CREATE DATABASE IF NOT EXISTS `ticket_module`;

USE `ticket_module`;

CREATE TABLE IF NOT EXISTS `admin_user` (
    `admin_id` int(8) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `cid` varchar(19) NOT NULL,
    `level` varchar(5) NOT NULL,
    `session_id` varchar(255),
    `created_on` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`admin_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  INSERT INTO `admin_user` (admin_id,email,name,password,cid,level) VALUES(1,"admin@admin.bt","Administrator",MD5("admin@admin.bt"),"00000000000","0");
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `citizens`
  --
  
  CREATE TABLE IF NOT EXISTS `citizens` (
    `cid` varchar(50) NOT NULL,
    `dob` date NOT NULL,
    `dzongkhag` varchar(255) NOT NULL,
    `first_name` varchar(255) NOT NULL,
    `middle_name` varchar(255) NOT NULL,
    `last_name` varchar(255) NOT NULL,
    `phonenumber` varchar(15) NOT NULL,
    `image_id` int(8) NOT NULL,
    PRIMARY KEY (`cid`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
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
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `images`
  --
  
  CREATE TABLE IF NOT EXISTS `images` (
    `id` int(8) NOT NULL AUTO_INCREMENT,
    `bin` longblob NOT NULL,
    `format` varchar(5) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `logs`
  --
  
  CREATE TABLE IF NOT EXISTS `logs` (
    `admin_id` varchar(255) NOT NULL,
    `action` varchar(1024) NOT NULL,
    `datetime` datetime DEFAULT current_timestamp(),
    `event_id` int(8) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `luckydraw`
  --
  
  CREATE TABLE IF NOT EXISTS `luckydraw` (
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
  
  CREATE TABLE IF NOT EXISTS `minor` (
    `cid` varchar(50) NOT NULL,
    `dob` date NOT NULL,
    `first_name` varchar(255) NOT NULL,
    `middle_name` varchar(255) NOT NULL,
    `last_name` varchar(255) NOT NULL,
    `parent_cid` varchar(19) NOT NULL,
    PRIMARY KEY (`cid`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `otp`
  --
  
  CREATE TABLE IF NOT EXISTS `otp` (
    `cid` varchar(255) NOT NULL,
    `otp` varchar(10) NOT NULL,
    `valid_till` datetime NOT NULL,
    `attempts` int(3) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  -- --------------------------------------------------------
  
  --
  -- Table structure for table `registration_requests`
  --
  
  CREATE TABLE IF NOT EXISTS `registration_requests` (
    `id` int(8) NOT NULL AUTO_INCREMENT,
    `event_id` int(8) NOT NULL,
    `cid` varchar(255) NOT NULL,
    `register_datetime` datetime DEFAULT current_timestamp(),
    `other_cids` varchar(1024) NOT NULL,
    `withdrawn` tinyint(1) NOT NULL DEFAULT 0,
    `is_allowed` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  COMMIT;

';

DEFINE('BASE_URL','http://127.0.0.1/');
$settings = parse_ini_file("settings/config.ini", true);

DEFINE ('DB_USER', $settings["db"]["username"]);
DEFINE ('DB_PSWD', $settings["db"]["password"]); 
DEFINE ('DB_HOST', $settings["db"]["url"]); 
DEFINE ('DB_NAME', $settings["db"]["database"]); 


try {
    $conn = new mysqli(DB_HOST,DB_USER,DB_PSWD);
    $conn->multi_query($prerequisite);

}
catch (exception $e) {
    header( 'HTTP/1.0 500 Internal Server Error', TRUE, 500 );
    die( header( 'location: error-500' ) );
}

if($conn->connect_error) {
    header( 'HTTP/1.0 500 Internal Server Error', TRUE, 500 );
    die( header( 'location: error-500' ) );
}
