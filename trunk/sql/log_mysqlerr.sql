--
-- Tabellenstruktur für Tabelle `log_mysqlerr`
--

CREATE TABLE IF NOT EXISTS `log_mysqlerr` (
  `id` int(11) NOT NULL auto_increment,
  `qry` text collate latin1_german2_ci NOT NULL,
  `err` text collate latin1_german2_ci NOT NULL,
  `time` int(11) NOT NULL default '0',
  `scriptname` text collate latin1_german2_ci,
  `referer` text collate latin1_german2_ci NOT NULL,
  `fixed` int(11) default NULL,
  `player` int(11) default NULL,
  `get_str` text collate latin1_german2_ci,
  `post_str` text collate latin1_german2_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci ;
