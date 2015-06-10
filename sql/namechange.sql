-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Juni 2015 um 07:59
-- Server Version: 5.1.72
-- PHP-Version: 5.3.3-7+squeeze17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `hw2_game1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `namechange`
--

CREATE TABLE IF NOT EXISTS `namechange` (
  `id` int(11) NOT NULL DEFAULT '0',
  `oldname` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `newname` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `reason` text COLLATE latin1_german2_ci,
  `nh1` int(11) NOT NULL DEFAULT '0',
  `time1` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `nh2` int(11) DEFAULT NULL,
  `time2` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nh3` int(11) DEFAULT NULL,
  `time3` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `changed` smallint(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
