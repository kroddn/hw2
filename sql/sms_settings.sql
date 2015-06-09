-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 10:37
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
-- Tabellenstruktur für Tabelle `sms_settings`
--

CREATE TABLE IF NOT EXISTS `sms_settings` (
  `player` int(11) NOT NULL DEFAULT '0',
  `contingent` int(11) NOT NULL DEFAULT '1',
  `contingent_freesms` int(11) NOT NULL DEFAULT '1',
  `contingent_used` int(11) NOT NULL DEFAULT '0',
  `options` bigint(20) NOT NULL DEFAULT '0',
  `sms_nr` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `sms_nr_verified` int(11) DEFAULT NULL,
  `sms_nr_show` tinyint(4) NOT NULL DEFAULT '0',
  `created` int(10) NOT NULL DEFAULT '0',
  `updated` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`player`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Eintrag wird erstellt, sobald ein Spieler den SMS-Bedingunge';
