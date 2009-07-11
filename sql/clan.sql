CREATE TABLE IF NOT EXISTS `clan` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `description` text collate latin1_german2_ci,
  `points` int(10) default '0',
  `gold` bigint(20) NOT NULL default '0',
  `tax` float NOT NULL default '0',
  `toplist` tinyint(3) default NULL,
  `chat` varchar(32) collate latin1_german2_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=0  ;