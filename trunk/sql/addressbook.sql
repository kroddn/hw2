--
-- Tabellenstruktur für Tabelle `addressbook`
--

CREATE TABLE IF NOT EXISTS `addressbook` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `player` int(11) default NULL,
  `sms` varchar(20) collate latin1_german2_ci default NULL,
  `nicename` varchar(50) collate latin1_german2_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `owner_2` (`owner`,`player`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Adressbuch. Entweder player oder sms muß gesetzt sein';
