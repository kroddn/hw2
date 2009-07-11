--
-- Tabellenstruktur für Tabelle `zitate`
--

CREATE TABLE IF NOT EXISTS `zitate` (
  `id` int(5) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL default '0',
  `player` int(5) NOT NULL default '0',
  `text` text collate latin1_german2_ci NOT NULL,
  `admin` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci  ;
 