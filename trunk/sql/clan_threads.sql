-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Juni 2015 um 08:01
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
-- Tabellenstruktur für Tabelle `clan_threads`
--

CREATE TABLE IF NOT EXISTS `clan_threads` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `clan` int(10) NOT NULL DEFAULT '0',
  `forum` int(10) NOT NULL DEFAULT '0',
  `reply` int(10) NOT NULL DEFAULT '0',
  `player` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT 'Cheater',
  `timestamp` varchar(10) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `topic` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '-',
  `text` text COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;
