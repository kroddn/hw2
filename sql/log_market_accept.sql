-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 10:40
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
-- Tabellenstruktur für Tabelle `log_market_accept`
--

CREATE TABLE IF NOT EXISTS `log_market_accept` (
  `offerer` int(11) NOT NULL DEFAULT '0',
  `acceptor` int(11) NOT NULL DEFAULT '0',
  `wantsType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') COLLATE latin1_german2_ci NOT NULL DEFAULT 'gold',
  `wantsQuant` int(11) NOT NULL DEFAULT '0',
  `hasType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') COLLATE latin1_german2_ci NOT NULL DEFAULT 'gold',
  `hasQuant` int(11) NOT NULL DEFAULT '0',
  `acctime` int(11) NOT NULL DEFAULT '0',
  `offtime` int(11) NOT NULL DEFAULT '0',
  `city` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
