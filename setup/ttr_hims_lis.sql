-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- โฮสต์: localhost
-- เวลาในการสร้าง: 03 ก.ค. 2017  16:07น.
-- เวอร์ชั่นของเซิร์ฟเวอร์: 5.5.54-0ubuntu0.14.04.1
-- รุ่นของ PHP: 5.5.9-1ubuntu4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- ฐานข้อมูล: `ttr_hims`
--

-- --------------------------------------------------------

--
-- โครงสร้างตาราง `lis_order`
--

CREATE TABLE IF NOT EXISTS `lis_order` (
  `lis_number` bigint(20) NOT NULL,
  `message_date` datetime NOT NULL,
  `patient_id` bigint(20) NOT NULL,
  `patient_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `reference_number` bigint(20) NOT NULL,
  `accept_time` datetime NOT NULL,
  `request_div` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'หน่วยงานที่ส่งตรวจ',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lis_number`,`reference_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='HL7 order from Roche Infinity';

-- --------------------------------------------------------

--
-- โครงสร้างตาราง `lis_result`
--

CREATE TABLE IF NOT EXISTS `lis_result` (
  `lis_number` bigint(20) NOT NULL,
  `lis_code` int(11) NOT NULL,
  `test` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `lab_type` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `lab_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `result_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `unit` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `result_date` date NOT NULL,
  `technical_time` datetime NOT NULL,
  `medical_time` datetime NOT NULL,
  `normal_range` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lis_number`,`lis_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
