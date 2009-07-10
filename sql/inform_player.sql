--
-- Tabellenstruktur für Tabelle `inform_player`
--

CREATE TABLE IF NOT EXISTS `inform_player` (
  `infid` int(11) NOT NULL default '0',
  `player` int(11) NOT NULL default '0',
  `time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`infid`,`player`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
