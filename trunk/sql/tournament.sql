-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 10:38
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
-- Tabellenstruktur für Tabelle `tournament`
--

CREATE TABLE IF NOT EXISTS `tournament` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `organizer` int(11) DEFAULT NULL COMMENT 'Ist NULL bei Server-Turnieren',
  `time` int(10) NOT NULL DEFAULT '0',
  `calctime` int(10) DEFAULT NULL COMMENT 'Berechnungszeitpunkt',
  `maxplayers` int(11) NOT NULL DEFAULT '20' COMMENT 'Maximalanzahl Teilnehmer',
  `gold` int(11) NOT NULL DEFAULT '1000',
  `require_research` int(11) NOT NULL DEFAULT '1' COMMENT 'Welche Forschung wird zur Teilnahme an diesem Turnier benötigt?',
  PRIMARY KEY (`tid`),
  KEY `organizer` (`organizer`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=46 ;
