--
-- Tabellenstruktur für Tabelle `armyunit`
--

CREATE TABLE IF NOT EXISTS `armyunit` (
  `aid` int(10) NOT NULL default '0',
  `unit` int(10) NOT NULL default '0',
  `count` int(10) NOT NULL default '0',
  PRIMARY KEY  (`aid`,`unit`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
