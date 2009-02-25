-
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `value` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `creationtime` int(11) NOT NULL default '0',
  `updatetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
