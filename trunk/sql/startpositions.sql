--
-- Tabellenstruktur für Tabelle `startpositions`
--

CREATE TABLE IF NOT EXISTS `startpositions` (
  `x` smallint(5) NOT NULL default '0',
  `y` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`x`,`y`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
