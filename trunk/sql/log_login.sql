--
-- Tabellenstruktur für Tabelle `log_login`
--

CREATE TABLE IF NOT EXISTS `log_login` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `inputpw` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `dbpw` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `status` tinyint(3) NOT NULL default '0',
  `inputseccode` varchar(15) collate latin1_german2_ci NOT NULL default '',
  `dbseccode` varchar(10) collate latin1_german2_ci NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  `logouttime` int(10) default NULL,
  `ip` varchar(15) collate latin1_german2_ci NOT NULL default '',
  `user_agent` varchar(200) collate latin1_german2_ci default NULL,
  KEY `ip` (`ip`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;