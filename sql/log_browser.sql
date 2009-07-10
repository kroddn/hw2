CREATE TABLE IF NOT EXISTS `log_browser` (
  `id` int(11) NOT NULL auto_increment,
  `browser` varchar(250) collate latin1_german2_ci NOT NULL default '',
  `version` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `logins` int(10) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci ;