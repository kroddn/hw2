-- phpMyAdmin SQL Dump
-- version 2.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 07. April 2008 um 10:44
-- Server Version: 5.0.32
-- PHP-Version: 5.2.0-8+etch10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `global`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tutorial_topics`
--

CREATE TABLE IF NOT EXISTS `tutorial_topics` (
  ` tut_id` int(11) NOT NULL auto_increment,
  `level` int(11) NOT NULL,
  `sublevel` int(11) NOT NULL,
  `page` varchar(100) collate latin1_german2_ci default NULL,
  `pagetitle` varchar(100) collate latin1_german2_ci default NULL COMMENT 'Titel der Seite, auf der dieses Topic angezeigt wird',
  `tut_text` text collate latin1_german2_ci,
  PRIMARY KEY  (` tut_id`),
  UNIQUE KEY `level` (`level`,`sublevel`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=74 ;