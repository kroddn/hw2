--
-- Tabellenstruktur für Tabelle `player_online`
--

CREATE TABLE IF NOT EXISTS `player_online` (
  `uid` int(10) NOT NULL default '0',
  `lastclick` int(10) NOT NULL default '0',
  `sid` varchar(50) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
