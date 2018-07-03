-- phpMyAdmin SQL Dump
-- version 4.7.8
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 03, 2018 at 02:31 PM
-- Server version: 5.7.19
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `selcom_transset`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card` varchar(16) NOT NULL,
  `firstName` varchar(60) NOT NULL,
  `lastName` varchar(60) NOT NULL,
  `accountNo` varchar(100) NOT NULL,
  `msisdn` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT '0',
  `addressLine1` varchar(60) NOT NULL,
  `addressCity` varchar(60) NOT NULL,
  `addressCountry` varchar(60) NOT NULL,
  `dob` date NOT NULL,
  `currancy` varchar(5) NOT NULL,
  `state` varchar(5) NOT NULL DEFAULT 'ON',
  `active` varchar(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

DROP TABLE IF EXISTS `card`;
CREATE TABLE IF NOT EXISTS `card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '-',
  `msisdn` varchar(12) DEFAULT '-',
  `uid` varchar(16) DEFAULT '-',
  `card` varchar(16) DEFAULT '-',
  `status` varchar(1) DEFAULT '0',
  `fulltimestamp` datetime DEFAULT NULL,
  `registeredby` varchar(20) DEFAULT NULL,
  `confirmedby` varchar(20) DEFAULT NULL,
  `registertimestamp` datetime DEFAULT NULL,
  `confirmtimestamp` datetime DEFAULT NULL,
  `pin` varchar(4) DEFAULT '1234',
  `failcount` int(11) DEFAULT '0',
  `imsi` varchar(25) DEFAULT '-',
  `balance` varchar(25) DEFAULT '0',
  `bonus` varchar(25) DEFAULT '0',
  `loyalty` varchar(25) DEFAULT '0',
  `alert` varchar(1) DEFAULT '1',
  `obal` varchar(20) DEFAULT '0',
  `cbal` varchar(20) DEFAULT '0',
  `dealer` varchar(20) DEFAULT 'SELCOM',
  `last_transaction` datetime DEFAULT NULL,
  `reference` varchar(20) DEFAULT NULL,
  `language` varchar(3) DEFAULT 'SWA',
  `fuel_scheme` varchar(1) DEFAULT '0',
  `fuel_scheme_name` varchar(12) DEFAULT '-',
  `fuel_balance` varchar(25) DEFAULT '0',
  `state` varchar(3) DEFAULT 'ON',
  `dailytrans` varchar(10) DEFAULT '0',
  `active` varchar(1) DEFAULT '0',
  `email` varchar(100) DEFAULT '-',
  `stolen` varchar(1) DEFAULT '0',
  `initial` varchar(1) DEFAULT '0',
  `holdinglimit` varchar(10) DEFAULT '5000000',
  `dailylimit` varchar(10) DEFAULT '5000000',
  `tier` varchar(20) DEFAULT 'A',
  `comments` varchar(500) DEFAULT '-',
  `suspense` varchar(25) DEFAULT '0',
  `fuel_last_transaction` datetime DEFAULT NULL,
  `fuel_client` varchar(12) DEFAULT '-',
  `fuel_reference` varchar(20) DEFAULT '-',
  `fuel_obal` varchar(20) DEFAULT '0',
  `fuel_cbal` varchar(20) DEFAULT '0',
  `phone` varchar(12) DEFAULT '-',
  `ussd` varchar(1) DEFAULT '1',
  `fuel_lube` varchar(1) DEFAULT '0',
  `fuel_bill_pay` varchar(1) DEFAULT '0',
  `fuel_any_vehicle` varchar(1) DEFAULT '1',
  `veh_reg_num` varchar(100) DEFAULT '-',
  `type` varchar(10) DEFAULT 'PRIMARY',
  `master` varchar(16) DEFAULT '-',
  `pan` varchar(20) DEFAULT '-',
  `fuel_bonus` varchar(25) DEFAULT '0',
  `fuel_bonus_avail` varchar(25) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `card_test` (`card`),
  KEY `msisdn` (`msisdn`),
  KEY `status` (`status`),
  KEY `imsi` (`imsi`),
  KEY `reference` (`reference`),
  KEY `name` (`name`),
  KEY `alert` (`alert`),
  KEY `language` (`language`),
  KEY `uid` (`uid`),
  KEY `active` (`active`),
  KEY `fuel_scheme` (`fuel_scheme`),
  KEY `fuel_scheme_name` (`fuel_scheme_name`),
  KEY `fuel_client` (`fuel_client`),
  KEY `fulltimestamp` (`fulltimestamp`),
  KEY `last_transaction` (`last_transaction`),
  KEY `fuel_last_transaction` (`fuel_last_transaction`),
  KEY `fuel_any_vehicle` (`fuel_any_vehicle`),
  KEY `veh_reg_num` (`veh_reg_num`),
  KEY `pan` (`pan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `incoming`
--

DROP TABLE IF EXISTS `incoming`;
CREATE TABLE IF NOT EXISTS `incoming` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fulltimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paramtimestamp` timestamp NOT NULL,
  `method` varchar(60) NOT NULL,
  `transId` varchar(60) NOT NULL,
  `payload` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--

DROP TABLE IF EXISTS `ledger`;
CREATE TABLE IF NOT EXISTS `ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fulltimestamp` datetime DEFAULT NULL,
  `vendor` varchar(12) DEFAULT NULL,
  `card` varchar(20) DEFAULT NULL,
  `transtype` varchar(12) DEFAULT NULL,
  `transid` varchar(50) DEFAULT NULL,
  `reference` varchar(20) DEFAULT NULL,
  `amount` varchar(20) DEFAULT NULL,
  `obal` varchar(20) DEFAULT NULL,
  `cbal` varchar(20) DEFAULT NULL,
  `charge` varchar(20) DEFAULT NULL,
  `msisdn` varchar(12) DEFAULT '-',
  `channel` varchar(20) DEFAULT 'M-PESA',
  `utilitycode` varchar(20) DEFAULT '-',
  `utilityref` varchar(20) DEFAULT '-',
  `name` varchar(100) DEFAULT '-',
  PRIMARY KEY (`id`),
  KEY `fulltimestamp` (`fulltimestamp`),
  KEY `reference` (`reference`),
  KEY `transid` (`transid`),
  KEY `vendor` (`vendor`),
  KEY `transtype` (`transtype`),
  KEY `msisdn` (`msisdn`),
  KEY `channel` (`channel`),
  KEY `utilityref` (`utilityref`),
  KEY `card` (`card`),
  KEY `utilitycode` (`utilitycode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fulltimestamp` datetime DEFAULT NULL,
  `transid` varchar(50) DEFAULT NULL,
  `reference` varchar(20) DEFAULT NULL,
  `vendor` varchar(12) DEFAULT NULL,
  `card` varchar(20) DEFAULT NULL,
  `msisdn` varchar(12) DEFAULT NULL,
  `amount` varchar(20) DEFAULT NULL,
  `result` varchar(8) DEFAULT '111',
  `message` varchar(1024) DEFAULT NULL,
  `obal` varchar(20) DEFAULT '0',
  `cbal` varchar(20) DEFAULT '0',
  `charge` varchar(20) DEFAULT '0',
  `name` varchar(100) DEFAULT '-',
  `type` varchar(10) DEFAULT 'CREDIT',
  `comments` varchar(100) DEFAULT '-',
  `status` varchar(10) DEFAULT '0',
  `initiated` varchar(50) DEFAULT '-',
  `completed` varchar(50) DEFAULT '-',
  `initiate_ts` datetime DEFAULT NULL,
  `complete_ts` datetime DEFAULT NULL,
  `channel` varchar(20) DEFAULT 'M-PESA',
  `utilitycode` varchar(20) DEFAULT '-',
  `utilityref` varchar(20) DEFAULT '-',
  `dealer` varchar(20) DEFAULT '-',
  `narration` varchar(160) DEFAULT '-',
  `fuel_type` varchar(10) DEFAULT '-',
  `fuel_rate` varchar(10) DEFAULT '0',
  `fuel_volume` varchar(10) DEFAULT '0',
  `fuel_bonus` varchar(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `transid` (`transid`),
  KEY `reference` (`reference`),
  KEY `fulltimestamp` (`fulltimestamp`),
  KEY `vendor` (`vendor`),
  KEY `till` (`card`),
  KEY `result` (`result`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `initiated` (`initiated`),
  KEY `completed` (`completed`),
  KEY `initiate_ts` (`initiate_ts`),
  KEY `complete_ts` (`complete_ts`),
  KEY `channel` (`channel`),
  KEY `utilitytype` (`utilitycode`),
  KEY `utilityref` (`utilityref`),
  KEY `dealer` (`dealer`),
  KEY `fuel_type` (`fuel_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
