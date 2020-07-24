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