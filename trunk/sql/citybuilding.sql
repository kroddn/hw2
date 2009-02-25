--
-- Tabellenstruktur für Tabelle `citybuilding`
--

CREATE TABLE IF NOT EXISTS `citybuilding` (
  `city` int(10) NOT NULL default '0',
  `building` int(10) NOT NULL default '0',
  `count` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`city`,`building`),
  KEY `building` (`building`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
