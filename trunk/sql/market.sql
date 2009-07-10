--
-- Tabellenstruktur für Tabelle `market`
--

CREATE TABLE IF NOT EXISTS `market` (
  `id` int(10) NOT NULL auto_increment,
  `player` int(10) NOT NULL default '0',
  `wantsType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') collate latin1_german2_ci NOT NULL default 'gold',
  `wantsQuant` int(10) NOT NULL default '0',
  `hasType` enum('gold','wood','iron','stone','shortrange','longrange','armor','horse') collate latin1_german2_ci NOT NULL default 'gold',
  `hasQuant` int(10) NOT NULL default '0',
  `ratio` float NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  `city` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ratio` (`ratio`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;
