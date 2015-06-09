-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 08:21
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
-- Tabellenstruktur für Tabelle `multi_trap`
--

CREATE TABLE IF NOT EXISTS `multi_trap` (
  `mid` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `code` varchar(32) COLLATE latin1_german2_ci NOT NULL DEFAULT '' COMMENT 'Code im Cookie',
  `player` int(10) NOT NULL DEFAULT '0' COMMENT 'Spieler',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT 'Zeitpunkt der Erstellung',
  `used` int(11) NOT NULL DEFAULT '0' COMMENT 'Anzahl der Aufrufe.',
  `expired` int(11) DEFAULT NULL COMMENT 'Wann lief der code ab?',
  PRIMARY KEY (`mid`),
  UNIQUE KEY `code` (`code`),
  KEY `player` (`player`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=88060 ;
