--
-- Tabellenstruktur für Tabelle `clanrel`
--

CREATE TABLE IF NOT EXISTS `clanrel` (
  `id1` int(11) NOT NULL default '0',
  `id2` int(11) NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '1',
  UNIQUE KEY `id1` (`id1`,`id2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;