--
-- Tabellenstruktur für Tabelle `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(10) NOT NULL auto_increment,
  `sender` varchar(40) collate latin1_german2_ci NOT NULL default 'SERVER',
  `recipient` int(10) NOT NULL default '0',
  `date` int(10) NOT NULL default '0',
  `status` int(10) NOT NULL default '0',
  `category` tinyint(3) NOT NULL default '0',
  `header` text collate latin1_german2_ci NOT NULL,
  `body` text collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `recipient` (`recipient`),
  KEY `sender` (`sender`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci ;
