--
-- Tabellenstruktur für Tabelle `city`
--

CREATE TABLE IF NOT EXISTS `city` (
  `id` int(10) NOT NULL default '0',
  `name` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `population` smallint(5) NOT NULL default '200',
  `populationlimit` int(10) default NULL,
  `religion` tinyint(4) NOT NULL default '0',
  `capital` tinyint(1) NOT NULL default '0',
  `rawwood` int(11) NOT NULL default '0',
  `rawiron` int(10) NOT NULL default '0',
  `rawstone` int(10) NOT NULL default '0',
  `food` int(10) NOT NULL default '0',
  `prosperity` int(11) NOT NULL default '0',
  `loyality` int(11) NOT NULL default '10000' COMMENT 'Loyalität',
  `prev_loyality` int(11) NOT NULL default '0' COMMENT 'Loyalität des Vorbesitzers',
  `owner` int(10) default NULL,
  `prev_owner` int(11) default NULL COMMENT 'Vorbesitzer der Stadt',
  `shortrange` int(10) NOT NULL default '0',
  `longrange` int(10) NOT NULL default '0',
  `armor` int(10) NOT NULL default '0',
  `horse` int(10) NOT NULL default '0',
  `max_shortrange` int(10) NOT NULL default '0',
  `max_longrange` int(10) NOT NULL default '0',
  `max_armor` int(10) NOT NULL default '0',
  `max_horse` int(10) NOT NULL default '0',
  `reserve_shortrange` int(10) NOT NULL default '0',
  `reserve_longrange` int(10) NOT NULL default '0',
  `reserve_armor` int(10) NOT NULL default '0',
  `reserve_horse` int(10) NOT NULL default '0',
  `attackblock` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
