-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 10:42
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
-- Tabellenstruktur für Tabelle `log_marketmod`
--

CREATE TABLE IF NOT EXISTS `log_marketmod` (
  `modid` int(11) NOT NULL DEFAULT '0',
  `player` int(11) NOT NULL DEFAULT '0',
  `type` enum('return','delete','punish') COLLATE latin1_german2_ci NOT NULL DEFAULT 'return',
  `wantsType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') COLLATE latin1_german2_ci NOT NULL DEFAULT 'gold',
  `wantsQuant` int(11) NOT NULL DEFAULT '0',
  `hasType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') COLLATE latin1_german2_ci NOT NULL DEFAULT 'gold',
  `hasQuant` int(11) NOT NULL DEFAULT '0',
  `City` int(11) NOT NULL DEFAULT '0',
  `timeOffer` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `rel` tinyint(4) DEFAULT NULL,
  `clanrel` tinyint(4) DEFAULT NULL,
  `penaltyType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') COLLATE latin1_german2_ci DEFAULT NULL,
  `penaltyQuant` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
