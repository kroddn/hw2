--
-- Tabellenstruktur für Tabelle `army`
--

CREATE TABLE IF NOT EXISTS `army` (
  `aid` int(10) NOT NULL auto_increment,
  `owner` int(10) NOT NULL default '0',
  `start` int(10) NOT NULL default '0',
  `end` int(10) NOT NULL default '0',
  `starttime` int(10) NOT NULL default '0',
  `endtime` int(10) NOT NULL default '0',
  `mission` set('settle','move','attack','despoil','burndown','return','siege') collate latin1_german2_ci NOT NULL default '',
  `missiondata` int(10) NOT NULL default '0',
  `tactic` int(11) NOT NULL default '0',
  PRIMARY KEY  (`aid`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

