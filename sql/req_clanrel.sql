--
-- Tabellenstruktur f�r Tabelle `req_clanrel`
--

CREATE TABLE IF NOT EXISTS `req_clanrel` (
  `id1` int(11) NOT NULL default '0',
  `id2` int(11) NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
