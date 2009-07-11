--
-- Tabellenstruktur für Tabelle `rpg`
--

CREATE TABLE IF NOT EXISTS `rpg` (
  `player` int(11) NOT NULL default '0',
  `createtime` int(10) NOT NULL default '0',
  `level` int(11) NOT NULL default '1',
  `xp` int(11) NOT NULL default '0',
  `strength` int(11) NOT NULL default '1' COMMENT 'Stärke',
  `stamina` int(11) NOT NULL default '1' COMMENT 'Ausdauer',
  `dexterity` int(11) NOT NULL default '1' COMMENT 'Gewandtheit',
  `intuition` int(11) NOT NULL default '1' COMMENT 'Intuition',
  `tournaments` int(11) NOT NULL default '0',
  `fights` int(11) NOT NULL default '0',
  `victories` int(11) NOT NULL default '0',
  `defeats` int(11) NOT NULL default '0',
  PRIMARY KEY  (`player`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
