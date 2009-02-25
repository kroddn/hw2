--
-- Tabellenstruktur für Tabelle `cityunit`
--

CREATE TABLE IF NOT EXISTS `cityunit` (
  `city` int(10) NOT NULL default '0',
  `unit` int(10) NOT NULL default '0',
  `count` int(10) NOT NULL default '0',
  `owner` int(10) NOT NULL default '0',
  PRIMARY KEY  (`city`,`unit`,`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
