--
-- Tabellenstruktur für Tabelle `map`
--

CREATE TABLE IF NOT EXISTS `map` (
  `id` int(10) NOT NULL default '0',
  `x` mediumint(8) NOT NULL default '0',
  `y` mediumint(8) NOT NULL default '0',
  `type` tinyint(3) NOT NULL default '0',
  `special` tinyint(3) default NULL,
  `pic` char(8) collate latin1_german2_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `xy` (`x`,`y`),
  KEY `type` (`type`),
  KEY `special` (`special`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
