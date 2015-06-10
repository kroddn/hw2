-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Juni 2015 um 11:12
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
-- Tabellenstruktur für Tabelle `log_bofh_player`
--

CREATE TABLE IF NOT EXISTS `log_bofh_player` (
  `id` int(11) NOT NULL DEFAULT '0',
  `oldname` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `newname` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `oldpasswd` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `newpasswd` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `oldemail` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `newemail` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `oldreligion` tinyint(4) NOT NULL DEFAULT '0',
  `newreligion` tinyint(4) NOT NULL DEFAULT '0',
  `oldgold` bigint(20) NOT NULL DEFAULT '0',
  `newgold` bigint(20) NOT NULL DEFAULT '0',
  `oldwood` int(11) NOT NULL DEFAULT '0',
  `newwood` int(11) NOT NULL DEFAULT '0',
  `oldiron` int(11) NOT NULL DEFAULT '0',
  `newiron` int(11) NOT NULL DEFAULT '0',
  `oldstone` int(11) NOT NULL DEFAULT '0',
  `newstone` int(11) NOT NULL DEFAULT '0',
  `oldrp` int(11) NOT NULL DEFAULT '0',
  `newrp` int(11) NOT NULL DEFAULT '0',
  `oldclan` smallint(6) DEFAULT '0',
  `newclan` smallint(6) DEFAULT '0',
  `oldclanstatus` tinyint(4) NOT NULL DEFAULT '0',
  `newclanstatus` tinyint(4) NOT NULL DEFAULT '0',
  `oldclanapplication` smallint(6) DEFAULT '0',
  `newclanapplication` smallint(6) DEFAULT '0',
  `admin` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
