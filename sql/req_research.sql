--
-- Tabellenstruktur für Tabelle `req_research`
--

CREATE TABLE IF NOT EXISTS `req_research` (
  `research_id` int(10) NOT NULL default '0',
  `req_research` int(10) NOT NULL default '0',
  PRIMARY KEY  (`research_id`,`req_research`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;