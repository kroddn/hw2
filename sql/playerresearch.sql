--
-- Tabellenstruktur für Tabelle `playerresearch`
--

CREATE TABLE IF NOT EXISTS `playerresearch` (
  `player` int(10) NOT NULL default '0',
  `research` int(10) NOT NULL default '0',
  PRIMARY KEY  (`player`,`research`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
