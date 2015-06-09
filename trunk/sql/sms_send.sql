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
-- Tabellenstruktur für Tabelle `sms_send`
--

CREATE TABLE IF NOT EXISTS `sms_send` (
  `sms_id` int(10) NOT NULL AUTO_INCREMENT,
  `sender` int(10) NOT NULL DEFAULT '0',
  `sendernr` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `realsendernr` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `nr` varchar(20) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `text` varchar(160) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `cost` int(10) NOT NULL DEFAULT '15',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `sent_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`sms_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=4521 ;
