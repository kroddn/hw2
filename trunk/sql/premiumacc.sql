--
-- Tabellenstruktur für Tabelle `premiumacc`
--

CREATE TABLE IF NOT EXISTS `premiumacc` (
  `id` int(11) NOT NULL auto_increment,
  `player` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '1',
  `start` int(11) NOT NULL default '0',
  `expire` int(11) NOT NULL default '0',
  `payd` int(11) default NULL,
  `paydtime` int(11) default NULL,
  `paytext` varchar(200) collate latin1_german2_ci default NULL,
  `sms_nr` varchar(20) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=0;