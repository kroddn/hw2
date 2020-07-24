-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. Februar 2009 um 17:12
-- Server Version: 5.0.32
-- PHP-Version: 5.2.0-8+etch13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


--
-- Datenbank: `hw2_game1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `building`
--

CREATE TABLE IF NOT EXISTS `building` (
  `id` int(10) NOT NULL default '0',
  `name` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `description` text collate latin1_german2_ci,
  `gold` int(10) NOT NULL default '0',
  `wood` int(10) NOT NULL default '0',
  `stone` int(10) NOT NULL default '0',
  `time` int(10) NOT NULL default '0',
  `religion` tinyint(3) default NULL,
  `makescapital` int(10) NOT NULL default '0',
  `convert_loyality` int(11) default NULL COMMENT 'Ab welcher Loyalität kann das Gebäude konvertieren',
  `points` int(10) NOT NULL default '0',
  `type` tinyint(3) NOT NULL default '0',
  `typelevel` tinyint(3) NOT NULL default '0',
  `category` tinyint(3) NOT NULL default '0',
  `maxy` int(11) default NULL,
  `req_fields` set('none','city','plains','forest','mountain','water','s1','s2','s3','s4','s5','s6','s7','s8') collate latin1_german2_ci NOT NULL default '',
  `req_research` int(10) default NULL,
  `req_type` tinyint(3) default NULL,
  `req_special` int(10) default NULL,
  `res_gold` smallint(6) default NULL,
  `res_rawwood` smallint(6) default NULL,
  `res_rawstone` smallint(6) default NULL,
  `res_rawiron` smallint(6) default NULL,
  `res_food` smallint(6) default NULL,
  `res_wood` smallint(6) default NULL,
  `res_stone` smallint(6) default NULL,
  `res_iron` smallint(6) default NULL,
  `res_rp` smallint(6) default NULL,
  `res_attraction` smallint(6) default NULL,
  `res_defense` smallint(6) default NULL,
  `res_training1` smallint(6) default NULL,
  `res_training2` smallint(6) default NULL,
  `res_training3` smallint(6) default NULL,
  `res_shortrange` smallint(6) default NULL,
  `res_longrange` smallint(6) default NULL,
  `res_armor` smallint(6) default NULL,
  `res_horse` smallint(6) default NULL,
  `res_scouttime` mediumint(9) default NULL,
  `res_foodstorage` int(11) default NULL,
  `res_storage` int(11) default NULL,
  `lib_link` varchar(20) collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;