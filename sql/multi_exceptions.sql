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
-- Tabellenstruktur für Tabelle `multi_exceptions`
--

CREATE TABLE IF NOT EXISTS `multi_exceptions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `expire` int(10) DEFAULT NULL,
  `valid` tinyint(4) DEFAULT NULL,
  `validatetime` int(11) DEFAULT NULL,
  `mh` int(11) DEFAULT NULL,
  `comment` text COLLATE latin1_german2_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;
