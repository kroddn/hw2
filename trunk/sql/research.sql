--
-- Tabellenstruktur für Tabelle `research`
--

CREATE TABLE IF NOT EXISTS `research` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `description` text collate latin1_german2_ci,
  `rp` int(10) NOT NULL default '0',
  `time` int(10) NOT NULL default '0',
  `religion` tinyint(3) default NULL,
  `points` int(10) NOT NULL default '0',
  `typ` tinyint(3) NOT NULL default '0',
  `typlevel` tinyint(3) NOT NULL default '0',
  `category` tinyint(3) NOT NULL default '0',
  `management` tinyint(3) default NULL,
  `lib_link` varchar(20) collate latin1_german2_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=267 ;

--
-- Daten für Tabelle `research`
--

INSERT INTO `research` (`id`, `name`, `description`, `rp`, `time`, `religion`, `points`, `typ`, `typlevel`, `category`, `management`, `lib_link`) VALUES
(1, 'Leibeigenschaft (1)', 'Ermöglicht die effiziente Verwaltung von 3 Städten', 0, 0, NULL, 0, 0, 0, 0, 1, '?s1=3&s2=0&s3=0');
