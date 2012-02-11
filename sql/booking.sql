CREATE TABLE IF NOT EXISTS `booking` (
  `bookid` int(11) NOT NULL auto_increment,
  `booktime` int(11) NOT NULL default '0',
  `oldpid` int(11) NOT NULL default '0',
  `oldname` varchar(50) NOT NULL default '',
  `name` varchar(50)  NOT NULL default '',
  `password` varchar(50)  NOT NULL default '',
  `bonuspoints` int(11) NOT NULL default '0',
  `religion` int(11) NOT NULL default '0',
  `zone` tinyint(4) NOT NULL default '0',
  `email` varchar(100)  NOT NULL default '',
  `sms` varchar(20) default NULL,
  `recruiter` int(11) default NULL,
  `status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`bookid`)
)  AUTO_INCREMENT=1 ;