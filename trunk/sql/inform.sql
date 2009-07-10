--
-- Tabellenstruktur für Tabelle `inform`
--

CREATE TABLE IF NOT EXISTS `inform` (
  `infid` int(11) NOT NULL auto_increment,
  `time` int(11) NOT NULL default '0',
  `expire` int(11) default NULL,
  `topic` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `text` text collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`infid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci ;

