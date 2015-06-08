-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Juni 2015 um 10:34
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
-- Tabellenstruktur für Tabelle `log_player_deleted`
--

CREATE TABLE IF NOT EXISTS `log_player_deleted` (
  `id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `name` varchar(50) COLLATE latin1_german2_ci DEFAULT NULL,
  `description` text COLLATE latin1_german2_ci NOT NULL,
  `password` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `register_email` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `ip` varchar(15) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `lastseen` int(11) NOT NULL DEFAULT '0',
  `religion` tinyint(4) DEFAULT '0',
  `gold` bigint(20) NOT NULL DEFAULT '0',
  `wood` int(11) NOT NULL DEFAULT '0',
  `iron` int(11) NOT NULL DEFAULT '0',
  `stone` int(11) NOT NULL DEFAULT '0',
  `rp` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `clan` int(11) NOT NULL DEFAULT '0',
  `clanstatus` smallint(6) NOT NULL DEFAULT '0',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `statusdescription` tinyint(4) NOT NULL DEFAULT '0',
  `regtime` int(11) NOT NULL DEFAULT '0',
  `recruiter` int(11) DEFAULT NULL,
  `lastres` int(11) NOT NULL DEFAULT '0',
  `deltime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
