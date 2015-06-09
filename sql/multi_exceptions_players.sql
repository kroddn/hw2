-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 08:22
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
-- Tabellenstruktur für Tabelle `multi_exceptions_players`
--

CREATE TABLE IF NOT EXISTS `multi_exceptions_players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eid` int(10) NOT NULL DEFAULT '0',
  `player` int(10) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `valid` tinyint(4) NOT NULL DEFAULT '0',
  `validatetime` int(11) NOT NULL DEFAULT '0',
  `mh` int(11) NOT NULL DEFAULT '0',
  `note` text COLLATE latin1_german2_ci NOT NULL,
  `comment` text COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`eid`,`player`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;
