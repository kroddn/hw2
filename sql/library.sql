--
-- Tabellenstruktur für Tabelle `library`
--

CREATE TABLE IF NOT EXISTS `library` (
  `id` int(10) NOT NULL auto_increment,
  `category` tinyint(4) NOT NULL default '0',
  `topic` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `description` mediumtext collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=35 ;