-- phpMyAdmin SQL Dump
-- version 4.7.8
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 12, 2018 at 02:54 PM
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
-- Table structure for table `accountprofile`
--

DROP TABLE IF EXISTS `accountprofile`;
CREATE TABLE IF NOT EXISTS `accountprofile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(60) NOT NULL,
  `lastName` varchar(60) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `customerNo` varchar(100) NOT NULL,
  `cardNo` varchar(60) NOT NULL,
  `msisdn` varchar(12) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` varchar(1) NOT NULL DEFAULT '0',
  `addressLine1` varchar(60) DEFAULT NULL,
  `addressCity` varchar(60) DEFAULT NULL,
  `addressCountry` varchar(60) NOT NULL DEFAULT 'Tanzania',
  `dob` date NOT NULL,
  `currency` varchar(5) NOT NULL DEFAULT 'TZS',
  `state` varchar(5) NOT NULL DEFAULT 'ON',
  `active` varchar(1) NOT NULL DEFAULT '0',
  `nationality` varchar(60) DEFAULT NULL,
  `balance` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `accountprofile`
--

INSERT INTO `accountprofile` (`id`, `firstName`, `lastName`, `gender`, `customerNo`, `cardNo`, `msisdn`, `email`, `status`, `addressLine1`, `addressCity`, `addressCountry`, `dob`, `currency`, `state`, `active`, `nationality`, `balance`) VALUES
(1, 'John', 'Doe', '', '255789654555', '', '255789654555', '', '1', '', 'Dar', '', '1978-06-15', 'TZS', 'ON', '0', '', 0),
(2, 'Salma', 'Kanji Lalji', '', '255789654700', '', '255789654700', '', '1', '', 'Mbeya', '', '1997-01-10', 'TZS', 'ON', '0', '', 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `card`
--

INSERT INTO `card` (`id`, `name`, `msisdn`, `uid`, `card`, `status`, `fulltimestamp`, `registeredby`, `confirmedby`, `registertimestamp`, `confirmtimestamp`, `pin`, `failcount`, `imsi`, `balance`, `bonus`, `loyalty`, `alert`, `obal`, `cbal`, `dealer`, `last_transaction`, `reference`, `language`, `fuel_scheme`, `fuel_scheme_name`, `fuel_balance`, `state`, `dailytrans`, `active`, `email`, `stolen`, `initial`, `holdinglimit`, `dailylimit`, `tier`, `comments`, `suspense`, `fuel_last_transaction`, `fuel_client`, `fuel_reference`, `fuel_obal`, `fuel_cbal`, `phone`, `ussd`, `fuel_lube`, `fuel_bill_pay`, `fuel_any_vehicle`, `veh_reg_num`, `type`, `master`, `pan`, `fuel_bonus`, `fuel_bonus_avail`) VALUES
(9, 'John Doe', '255789654555', '-', '', '1', '2018-07-12 15:56:59', 'SelcomTranssetAPI', 'SelcomTranssetAPI', '2018-07-12 15:56:59', '2018-07-12 15:56:59', '1234', 0, '-', '0', '0', '0', '1', '0', '0', 'Selcom', NULL, '176221524112', 'SWA', '0', '-', '0', 'ON', '0', '1', '', '0', '0', '5000000', '5000000', 'A', '-', '0', NULL, '-', '-', '0', '0', '', '1', '0', '0', '1', '-', 'PRIMARY', '-', '-', '0', '0'),
(10, 'Salma Kanji Lalji', '255789654700', '-', '', '1', '2018-07-12 15:59:16', 'SelcomTranssetAPI', 'SelcomTranssetAPI', '2018-07-12 15:59:16', '2018-07-12 15:59:16', '1234', 0, '-', '0', '0', '0', '1', '0', '0', 'Selcom', NULL, '213118272100', 'SWA', '0', '-', '0', 'ON', '0', '1', '', '0', '0', '5000000', '5000000', 'A', '-', '0', NULL, '-', '-', '0', '0', '', '1', '0', '0', '1', '-', 'PRIMARY', '-', '-', '0', '0');

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
  `transid` varchar(60) NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `fulltimestamp`, `transid`, `reference`, `vendor`, `card`, `msisdn`, `amount`, `result`, `message`, `obal`, `cbal`, `charge`, `name`, `type`, `comments`, `status`, `initiated`, `completed`, `initiate_ts`, `complete_ts`, `channel`, `utilitycode`, `utilityref`, `dealer`, `narration`, `fuel_type`, `fuel_rate`, `fuel_volume`, `fuel_bonus`) VALUES
(39, '2018-07-12 16:43:36', '01052018161000', '495160143122', 'TRANSSNET', 'XXXX XXXX XXXX ', '255789654700', '10', '051', 'Insufficient funds', '0', '0', '0', 'Salma Kanji Lalji', 'DEBIT', '-', '0', '-', '-', '2018-07-12 16:43:36', '2018-07-12 16:43:36', 'APP', 'P2P', '255789654555', 'Selcom', '-', '-', '0', '0', '0');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
