--
-- Tabellenstruktur für Tabelle `cityunit_ordered`
--

CREATE TABLE IF NOT EXISTS `cityunit_ordered` (
  `uid` int(10) NOT NULL auto_increment,
  `city` int(10) NOT NULL default '0',
  `unit` int(10) NOT NULL default '0',
  `count` int(10) NOT NULL default '0',
  `time` int(10) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci ;
