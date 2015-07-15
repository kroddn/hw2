--
-- Tabellenstruktur für Tabelle `config_message`
--

CREATE TABLE IF NOT EXISTS `config_message` (
  `playerid` int(4) NOT NULL,
  `rows_per_page` int(3) NOT NULL,
  `ignore_read` int(1) NOT NULL default '0',
  `show_archive` int(1) NOT NULL default '0',
  `ignore_build` int(1) NOT NULL default '0',
  `show_fights` int(1) NOT NULL default '0',
  PRIMARY KEY (`playerid`);
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;