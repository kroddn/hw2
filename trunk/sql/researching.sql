--
-- Tabellenstruktur für Tabelle `researching`
--

CREATE TABLE IF NOT EXISTS `researching` (
  `player` int(10) NOT NULL default '0',
  `rid` int(10) NOT NULL default '0',
  `starttime` int(10) NOT NULL default '0',
  `endtime` int(10) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
