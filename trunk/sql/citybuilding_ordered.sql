CREATE TABLE IF NOT EXISTS `citybuilding_ordered` (
  `bid` int(10) NOT NULL auto_increment,
  `city` int(10) NOT NULL default '0',
  `building` int(10) NOT NULL default '0',
  `count` smallint(5) NOT NULL default '0',
  `time` int(10) NOT NULL default '0',
  PRIMARY KEY  (`bid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=171558 ;
