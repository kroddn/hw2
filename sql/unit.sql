-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. Februar 2009 um 17:10
-- Server Version: 5.0.32
-- PHP-Version: 5.2.0-8+etch13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `hw2_game1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `unit`
--

CREATE TABLE IF NOT EXISTS `unit` (
  `id` tinyint(3) NOT NULL default '0',
  `name` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `description` text collate latin1_german2_ci,
  `type` tinyint(3) NOT NULL default '0',
  `religion` tinyint(1) NOT NULL default '0',
  `req_research` int(10) default NULL,
  `level` tinyint(4) NOT NULL default '0',
  `damage` tinyint(3) NOT NULL default '0',
  `bonus1` tinyint(3) NOT NULL default '0',
  `bonus2` tinyint(3) NOT NULL default '0',
  `bonus3` tinyint(3) NOT NULL default '0',
  `life` tinyint(3) NOT NULL default '0',
  `speed` tinyint(3) NOT NULL default '0',
  `gold` int(10) NOT NULL default '0',
  `shortrange` tinyint(3) NOT NULL default '0',
  `longrange` tinyint(3) NOT NULL default '0',
  `armor` tinyint(3) NOT NULL default '0',
  `horse` tinyint(3) NOT NULL default '0',
  `time` int(10) NOT NULL default '0',
  `cost` float NOT NULL default '0',
  `points` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

--
-- Daten für Tabelle `unit`
--

INSERT INTO `unit` (`id`, `name`, `description`, `type`, `religion`, `req_research`, `level`, `damage`, `bonus1`, `bonus2`, `bonus3`, `life`, `speed`, `gold`, `shortrange`, `longrange`, `armor`, `horse`, `time`, `cost`, `points`) VALUES
(1, 'Milizen', 'sehr schwache Kurzwaffeninfanteristen (Nahkämpfer)', 1, 1, 1, 0, 5, 0, 0, 0, 24, 3, 10, 0, 0, 0, 0, 3600, 0.2, 0),
(2, 'Speerträger', 'schwache Stangenwaffeninfanteristen (Nahkämpfer)', 1, 1, 11, 1, 11, 0, 0, 4, 55, 3, 15, 1, 0, 1, 0, 7200, 0.3, 4),
(3, 'Bogenschützen', 'schwache Fernkämpfer', 2, 1, 12, 1, 9, 7, 0, 0, 45, 3, 20, 0, 1, 0, 0, 7200, 0.3, 4),
(4, 'Berittene Hauptmänner', 'mittelstarke, sehr schnelle Kavalleristen', 3, 1, 13, 1, 10, 0, 5, 0, 59, 5, 25, 1, 0, 1, 1, 7200, 0.3, 4),
(5, 'Pikeniere', 'mittelstarke Stangenwaffeninfanteristen (Nahkämpfer)', 1, 1, 94, 2, 13, 0, 0, 8, 75, 2, 30, 1, 0, 1, 0, 14400, 0.4, 6),
(6, 'Armbrustschützen', 'mittelstarke Fernkämpfer', 2, 1, 98, 2, 14, 12, 0, 0, 75, 2, 40, 0, 1, 1, 0, 14400, 0.4, 6),
(7, 'Feudalritter', 'starke, schnelle Kavalleristen', 3, 1, 104, 2, 13, 0, 8, 0, 81, 4, 50, 1, 0, 1, 1, 14400, 0.4, 6),
(8, 'Panzerpikeniere', 'sehr starke aber sehr langsame Stangenwaffeninfanteristen (Nahkämpfer)', 1, 1, 95, 3, 15, 0, 0, 20, 120, 1, 70, 1, 0, 1, 0, 21600, 0.5, 10),
(9, 'Arbalestenschützen', 'sehr starke Fernkämpfer', 2, 1, 100, 3, 20, 12, 0, 0, 95, 2, 80, 0, 1, 1, 0, 21600, 0.5, 10),
(10, 'Königliche Ritter', 'sehr starke, schnelle Kavalleristen', 3, 1, 105, 3, 20, 0, 15, 0, 115, 4, 100, 1, 0, 1, 1, 21600, 0.5, 10),
(31, 'Milizen', 'sehr schwache Kurzwaffeninfanteristen (Nahkämpfer)', 1, 2, 1, 0, 6, 0, 0, 0, 19, 3, 10, 0, 0, 0, 0, 3600, 0.2, 0),
(32, 'Abessinen', 'schwache Kurzwaffeninfanteristen (Nahkämpfer)', 1, 2, 11, 1, 11, 0, 0, 5, 50, 3, 15, 1, 0, 1, 0, 7200, 0.3, 4),
(33, 'Turkmenen', 'schwache Fernkämpfer', 2, 2, 12, 1, 12, 6, 0, 0, 44, 3, 20, 0, 1, 0, 0, 7200, 0.3, 4),
(34, 'Faris', 'mittelstarke, sehr schnelle Kavalleristen', 3, 2, 13, 1, 11, 0, 5, 0, 54, 5, 25, 1, 0, 1, 1, 7200, 0.3, 4),
(35, 'Muwahid', 'mittelstarke Stangenwaffeninfanteristen (Nahkämpfer)', 1, 2, 94, 2, 16, 0, 0, 8, 70, 2, 30, 1, 0, 1, 0, 14400, 0.4, 6),
(36, 'Janitscharen-Bogenschützen', 'mittelstarke Fernkämpfer', 2, 2, 99, 2, 17, 9, 0, 0, 60, 2, 45, 0, 1, 1, 0, 14400, 0.4, 6),
(37, 'Ghulam', 'starke, schnelle Kavalleristen', 3, 2, 104, 2, 16, 0, 7, 0, 72, 4, 50, 1, 0, 1, 1, 14400, 0.4, 6),
(38, 'Janitscharen-Infanterie', 'starke Kurzwaffeninfanteristen (Nahkämpfer)', 1, 2, 95, 3, 20, 0, 0, 13, 95, 2, 65, 1, 0, 1, 0, 21600, 0.5, 10),
(39, 'Mameluken', 'sehr starke Fernkämpfer', 2, 2, 103, 3, 22, 18, 0, 0, 100, 2, 100, 0, 1, 1, 0, 21600, 0.5, 10),
(40, 'Sipahi', 'sehr starke, schnelle Kavalleristen', 3, 2, 105, 3, 20, 0, 12, 0, 105, 4, 100, 1, 0, 1, 1, 21600, 0.5, 10),
(100, 'Dorfbewohner', 'Ein erzörnter Dorfbewohner, dessen Stadt gebrandschatzt werden soll.', 1, 0, NULL, 1, 1, 0, 0, 0, 2, 1, 0, 0, 0, 0, 0, 0, 0.1, 2),
(101, 'Lindwurm', 'Grausamer, Feuerspeiender Drache', 0, 3, NULL, 0, 127, 0, 0, 0, 127, 5, 0, 0, 0, 0, 0, 0, 0.1, 2),
(102, 'Berittene Bogenschützen', NULL, 3, 3, NULL, 2, 10, 5, 5, 0, 56, 5, 35, 1, 1, 1, 1, 7200, 0.4, 4),
(21, 'Kreuzritter', 'Bonuseinheit. Streng gläubige Elite-Soldaten, die für die Kirche auf Kreuzzug gehen', 1, 1, NULL, 5, 30, 0, 0, 0, 127, 2, 0, 0, 0, 0, 0, 0, 0, 5),
(51, 'Dschihadkrieger', 'Bonuseinheit. Schnelle, streng gläubige Elite-Kämpfer für den islamischen Dschihad.', 1, 2, NULL, 5, 35, 0, 0, 0, 100, 3, 0, 0, 0, 0, 0, 0, 0, 5);
