--
-- Tabellenstruktur für Tabelle `log_err`
--

CREATE TABLE IF NOT EXISTS `log_err` (
  `id` int(11) NOT NULL auto_increment,
  `errstr` text collate latin1_german2_ci NOT NULL,
  `time` int(11) NOT NULL default '0',
  `referer` text collate latin1_german2_ci NOT NULL,
  `fixed` tinyint(4) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

