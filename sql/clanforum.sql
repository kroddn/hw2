-- phpMyAdmin SQL Dump
-- version 2.6.0
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 26. Dezember 2004 um 19:00
-- Server Version: 4.0.22
-- PHP-Version: 4.3.10-2
-- 
-- Datenbank: `hwnewdev`
-- 

-- --------------------------------------------------------
-- 
-- Tabellenstruktur für Tabelle `clanf_auth_access`
-- 

CREATE TABLE `clanf_auth_access` (
  `group_id` mediumint(8) NOT NULL default '0',
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `auth_view` tinyint(1) NOT NULL default '0',
  `auth_read` tinyint(1) NOT NULL default '0',
  `auth_post` tinyint(1) NOT NULL default '0',
  `auth_reply` tinyint(1) NOT NULL default '0',
  `auth_edit` tinyint(1) NOT NULL default '0',
  `auth_delete` tinyint(1) NOT NULL default '0',
  `auth_sticky` tinyint(1) NOT NULL default '0',
  `auth_announce` tinyint(1) NOT NULL default '0',
  `auth_vote` tinyint(1) NOT NULL default '0',
  `auth_pollcreate` tinyint(1) NOT NULL default '0',
  `auth_attachments` tinyint(1) NOT NULL default '0',
  `auth_mod` tinyint(1) NOT NULL default '0',
  KEY `group_id` (`group_id`),
  KEY `forum_id` (`forum_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_auth_access`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_banlist`
-- 

CREATE TABLE `clanf_banlist` (
  `ban_id` mediumint(8) unsigned NOT NULL auto_increment,
  `ban_userid` mediumint(8) NOT NULL default '0',
  `ban_ip` varchar(8) NOT NULL default '',
  `ban_email` varchar(255) default NULL,
  PRIMARY KEY  (`ban_id`),
  KEY `ban_ip_user_id` (`ban_ip`,`ban_userid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_banlist`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_categories`
-- 

CREATE TABLE `clanf_categories` (
  `cat_id` mediumint(8) unsigned NOT NULL auto_increment,
  `cat_title` varchar(100) default NULL,
  `cat_order` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cat_id`),
  KEY `cat_order` (`cat_order`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_categories`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_config`
-- 

CREATE TABLE `clanf_config` (
  `config_name` varchar(255) NOT NULL default '',
  `config_value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_name`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_config`
-- 

INSERT INTO `clanf_config` VALUES ('config_id', '1');
INSERT INTO `clanf_config` VALUES ('board_disable', '0');
INSERT INTO `clanf_config` VALUES ('sitename', 'Ordensforum');
INSERT INTO `clanf_config` VALUES ('site_desc', 'A _little_ text to describe your forum');
INSERT INTO `clanf_config` VALUES ('cookie_name', 'hw2clanforum');
INSERT INTO `clanf_config` VALUES ('cookie_path', '/');
INSERT INTO `clanf_config` VALUES ('cookie_domain', '');
INSERT INTO `clanf_config` VALUES ('cookie_secure', '0');
INSERT INTO `clanf_config` VALUES ('session_length', '300');
INSERT INTO `clanf_config` VALUES ('allow_html', '1');
INSERT INTO `clanf_config` VALUES ('allow_html_tags', 'b,i,u,pre');
INSERT INTO `clanf_config` VALUES ('allow_bbcode', '1');
INSERT INTO `clanf_config` VALUES ('allow_smilies', '1');
INSERT INTO `clanf_config` VALUES ('allow_sig', '1');
INSERT INTO `clanf_config` VALUES ('allow_namechange', '0');
INSERT INTO `clanf_config` VALUES ('allow_theme_create', '0');
INSERT INTO `clanf_config` VALUES ('allow_avatar_local', '0');
INSERT INTO `clanf_config` VALUES ('allow_avatar_remote', '0');
INSERT INTO `clanf_config` VALUES ('allow_avatar_upload', '0');
INSERT INTO `clanf_config` VALUES ('enable_confirm', '0');
INSERT INTO `clanf_config` VALUES ('override_user_style', '0');
INSERT INTO `clanf_config` VALUES ('posts_per_page', '15');
INSERT INTO `clanf_config` VALUES ('topics_per_page', '50');
INSERT INTO `clanf_config` VALUES ('hot_threshold', '25');
INSERT INTO `clanf_config` VALUES ('max_poll_options', '10');
INSERT INTO `clanf_config` VALUES ('max_sig_chars', '255');
INSERT INTO `clanf_config` VALUES ('max_inbox_privmsgs', '50');
INSERT INTO `clanf_config` VALUES ('max_sentbox_privmsgs', '25');
INSERT INTO `clanf_config` VALUES ('max_savebox_privmsgs', '50');
INSERT INTO `clanf_config` VALUES ('board_email_sig', 'Thanks, The Management');
INSERT INTO `clanf_config` VALUES ('board_email', 'forum@holy-wars2.de');
INSERT INTO `clanf_config` VALUES ('smtp_delivery', '0');
INSERT INTO `clanf_config` VALUES ('smtp_host', '');
INSERT INTO `clanf_config` VALUES ('smtp_username', '');
INSERT INTO `clanf_config` VALUES ('smtp_password', '');
INSERT INTO `clanf_config` VALUES ('sendmail_fix', '0');
INSERT INTO `clanf_config` VALUES ('require_activation', '0');
INSERT INTO `clanf_config` VALUES ('flood_interval', '15');
INSERT INTO `clanf_config` VALUES ('board_email_form', '0');
INSERT INTO `clanf_config` VALUES ('avatar_filesize', '6144');
INSERT INTO `clanf_config` VALUES ('avatar_max_width', '80');
INSERT INTO `clanf_config` VALUES ('avatar_max_height', '80');
INSERT INTO `clanf_config` VALUES ('avatar_path', 'images/avatars');
INSERT INTO `clanf_config` VALUES ('avatar_gallery_path', 'images/avatars/gallery');
INSERT INTO `clanf_config` VALUES ('smilies_path', 'images/smiles');
INSERT INTO `clanf_config` VALUES ('default_style', '1');
INSERT INTO `clanf_config` VALUES ('default_dateformat', 'D M d, Y g:i a');
INSERT INTO `clanf_config` VALUES ('board_timezone', '0');
INSERT INTO `clanf_config` VALUES ('prune_enable', '1');
INSERT INTO `clanf_config` VALUES ('privmsg_disable', '0');
INSERT INTO `clanf_config` VALUES ('gzip_compress', '0');
INSERT INTO `clanf_config` VALUES ('coppa_fax', '');
INSERT INTO `clanf_config` VALUES ('coppa_mail', '');
INSERT INTO `clanf_config` VALUES ('record_online_users', '6');
INSERT INTO `clanf_config` VALUES ('record_online_date', '1103975272');
INSERT INTO `clanf_config` VALUES ('server_name', 'www.holy-wars2.de');
INSERT INTO `clanf_config` VALUES ('server_port', '80');
INSERT INTO `clanf_config` VALUES ('script_path', '/clanforum/');
INSERT INTO `clanf_config` VALUES ('version', '.0.11');
INSERT INTO `clanf_config` VALUES ('board_startdate', '1103730913');
INSERT INTO `clanf_config` VALUES ('default_lang', 'english');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_confirm`
-- 

CREATE TABLE `clanf_confirm` (
  `confirm_id` char(32) NOT NULL default '',
  `session_id` char(32) NOT NULL default '',
  `code` char(6) NOT NULL default '',
  PRIMARY KEY  (`session_id`,`confirm_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_confirm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_disallow`
-- 

CREATE TABLE `clanf_disallow` (
  `disallow_id` mediumint(8) unsigned NOT NULL auto_increment,
  `disallow_username` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`disallow_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_disallow`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_forum_prune`
-- 

CREATE TABLE `clanf_forum_prune` (
  `prune_id` mediumint(8) unsigned NOT NULL auto_increment,
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `prune_days` smallint(5) unsigned NOT NULL default '0',
  `prune_freq` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`prune_id`),
  KEY `forum_id` (`forum_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_forum_prune`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_forums`
-- 

CREATE TABLE `clanf_forums` (
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `cat_id` mediumint(8) unsigned NOT NULL default '0',
  `forum_name` varchar(150) default NULL,
  `forum_desc` text,
  `forum_status` tinyint(4) NOT NULL default '0',
  `forum_order` mediumint(8) unsigned NOT NULL default '1',
  `forum_posts` mediumint(8) unsigned NOT NULL default '0',
  `forum_topics` mediumint(8) unsigned NOT NULL default '0',
  `forum_last_post_id` mediumint(8) unsigned NOT NULL default '0',
  `prune_next` int(11) default NULL,
  `prune_enable` tinyint(1) NOT NULL default '0',
  `auth_view` tinyint(2) NOT NULL default '0',
  `auth_read` tinyint(2) NOT NULL default '0',
  `auth_post` tinyint(2) NOT NULL default '0',
  `auth_reply` tinyint(2) NOT NULL default '0',
  `auth_edit` tinyint(2) NOT NULL default '0',
  `auth_delete` tinyint(2) NOT NULL default '0',
  `auth_sticky` tinyint(2) NOT NULL default '0',
  `auth_announce` tinyint(2) NOT NULL default '0',
  `auth_vote` tinyint(2) NOT NULL default '0',
  `auth_pollcreate` tinyint(2) NOT NULL default '0',
  `auth_attachments` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `forums_order` (`forum_order`),
  KEY `cat_id` (`cat_id`),
  KEY `forum_last_post_id` (`forum_last_post_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_forums`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_groups`
-- 

CREATE TABLE `clanf_groups` (
  `group_id` mediumint(8) NOT NULL auto_increment,
  `group_type` tinyint(4) NOT NULL default '1',
  `group_name` varchar(40) NOT NULL default '',
  `group_description` varchar(255) NOT NULL default '',
  `group_moderator` mediumint(8) NOT NULL default '0',
  `group_single_user` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`group_id`),
  KEY `group_single_user` (`group_single_user`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

-- 
-- Daten für Tabelle `clanf_groups`
-- 

INSERT INTO `clanf_groups` VALUES (1, 1, 'Anonymous', 'Personal User', 0, 1);
INSERT INTO `clanf_groups` VALUES (2, 1, 'Admin', 'Personal User', 1, 0);
INSERT INTO `clanf_groups` VALUES (3, 1, 'Moderatoren', '', 1, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_posts`
-- 

CREATE TABLE `clanf_posts` (
  `post_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `forum_id` smallint(5) unsigned NOT NULL default '0',
  `poster_id` mediumint(8) NOT NULL default '0',
  `post_time` int(11) NOT NULL default '0',
  `poster_ip` varchar(8) NOT NULL default '',
  `post_username` varchar(25) default NULL,
  `enable_bbcode` tinyint(1) NOT NULL default '1',
  `enable_html` tinyint(1) NOT NULL default '0',
  `enable_smilies` tinyint(1) NOT NULL default '1',
  `enable_sig` tinyint(1) NOT NULL default '1',
  `post_edit_time` int(11) default NULL,
  `post_edit_count` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`post_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_id` (`topic_id`),
  KEY `poster_id` (`poster_id`),
  KEY `post_time` (`post_time`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_posts`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_posts_text`
-- 

CREATE TABLE `clanf_posts_text` (
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `bbcode_uid` varchar(10) NOT NULL default '',
  `post_subject` varchar(60) default NULL,
  `post_text` text,
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_posts_text`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_privmsgs`
-- 

CREATE TABLE `clanf_privmsgs` (
  `privmsgs_id` mediumint(8) unsigned NOT NULL auto_increment,
  `privmsgs_type` tinyint(4) NOT NULL default '0',
  `privmsgs_subject` varchar(255) NOT NULL default '0',
  `privmsgs_from_userid` mediumint(8) NOT NULL default '0',
  `privmsgs_to_userid` mediumint(8) NOT NULL default '0',
  `privmsgs_date` int(11) NOT NULL default '0',
  `privmsgs_ip` varchar(8) NOT NULL default '',
  `privmsgs_enable_bbcode` tinyint(1) NOT NULL default '1',
  `privmsgs_enable_html` tinyint(1) NOT NULL default '0',
  `privmsgs_enable_smilies` tinyint(1) NOT NULL default '1',
  `privmsgs_attach_sig` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`privmsgs_id`),
  KEY `privmsgs_from_userid` (`privmsgs_from_userid`),
  KEY `privmsgs_to_userid` (`privmsgs_to_userid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_privmsgs`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_privmsgs_text`
-- 

CREATE TABLE `clanf_privmsgs_text` (
  `privmsgs_text_id` mediumint(8) unsigned NOT NULL default '0',
  `privmsgs_bbcode_uid` varchar(10) NOT NULL default '0',
  `privmsgs_text` text,
  PRIMARY KEY  (`privmsgs_text_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_privmsgs_text`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_ranks`
-- 

CREATE TABLE `clanf_ranks` (
  `rank_id` smallint(5) unsigned NOT NULL auto_increment,
  `rank_title` varchar(50) NOT NULL default '',
  `rank_min` mediumint(8) NOT NULL default '0',
  `rank_special` tinyint(1) default '0',
  `rank_image` varchar(255) default NULL,
  PRIMARY KEY  (`rank_id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `clanf_ranks`
-- 

INSERT INTO `clanf_ranks` VALUES (1, 'Site Admin', -1, 1, NULL);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_search_results`
-- 

CREATE TABLE `clanf_search_results` (
  `search_id` int(11) unsigned NOT NULL default '0',
  `session_id` varchar(32) NOT NULL default '',
  `search_array` text NOT NULL,
  PRIMARY KEY  (`search_id`),
  KEY `session_id` (`session_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_search_results`
-- 

INSERT INTO `clanf_search_results` VALUES (769373391, '982c6218483f6be6d7257bdc8b1fa8df', 'a:7:{s:14:"search_results";s:1:"1";s:17:"total_match_count";i:1;s:12:"split_search";N;s:7:"sort_by";i:0;s:8:"sort_dir";s:4:"DESC";s:12:"show_results";s:6:"topics";s:12:"return_chars";i:200;}');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_search_wordlist`
-- 

CREATE TABLE `clanf_search_wordlist` (
  `word_text` varchar(50) binary NOT NULL default '',
  `word_id` mediumint(8) unsigned NOT NULL auto_increment,
  `word_common` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`word_text`),
  KEY `word_id` (`word_id`)
) TYPE=MyISAM AUTO_INCREMENT=54 ;

-- 
-- Daten für Tabelle `clanf_search_wordlist`
-- 

INSERT INTO `clanf_search_wordlist` VALUES (0x6578616d706c65, 1, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x706f7374, 2, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x7068706262, 3, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x696e7374616c6c6174696f6e, 4, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x64656c657465, 5, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x746f706963, 6, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x666f72756d, 7, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x73696e6365, 8, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x65766572797468696e67, 9, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x7365656d73, 10, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x776f726b696e67, 11, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x77656c636f6d65, 12, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6c6a6b6c6a6b6c6a6b6c, 13, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x74657374, 14, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6173646661736466, 27, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6672616e7a6c, 17, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x796568, 18, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x617366, 19, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x7465736174, 20, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6161616161616161, 21, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x62626262626262626262, 22, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6161616161616161616161, 26, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x68616861, 28, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x686f686f686f, 29, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x617364666173646661736466, 30, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x61627374696d6d656e, 31, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x61616161, 32, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x62626262, 33, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x7465737432, 43, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x637279, 42, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6675636b, 36, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6e6577, 37, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x746872656164, 38, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x79656565, 39, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6b65776c, 40, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6c6f6c, 41, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x61736664, 53, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6561736664, 48, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x7465737474, 49, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6173646661736664, 50, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x6173666173, 51, 0);
INSERT INTO `clanf_search_wordlist` VALUES (0x61736466, 52, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_search_wordmatch`
-- 

CREATE TABLE `clanf_search_wordmatch` (
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `word_id` mediumint(8) unsigned NOT NULL default '0',
  `title_match` tinyint(1) NOT NULL default '0',
  KEY `post_id` (`post_id`),
  KEY `word_id` (`word_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_search_wordmatch`
-- 

INSERT INTO `clanf_search_wordmatch` VALUES (1, 1, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 2, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 3, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 4, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 5, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 6, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 7, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 8, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 9, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 10, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 11, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 12, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 3, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (2, 13, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (2, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (3, 29, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (3, 27, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (4, 17, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (4, 14, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (4, 18, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (5, 20, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (5, 19, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (6, 22, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (6, 21, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (7, 22, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (7, 21, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (3, 26, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (3, 28, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (8, 30, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (8, 27, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (9, 31, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (10, 33, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (10, 32, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (18, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (18, 14, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (17, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (17, 42, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (13, 36, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (13, 37, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (13, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (13, 38, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (14, 36, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (14, 39, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (14, 37, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (14, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (14, 38, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (15, 36, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (15, 40, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (15, 39, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (15, 37, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (15, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (15, 38, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (16, 41, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (2, 52, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (20, 14, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (20, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (21, 14, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (22, 43, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (24, 48, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (24, 41, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (24, 49, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (25, 50, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (25, 42, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (25, 51, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 14, 1);
INSERT INTO `clanf_search_wordmatch` VALUES (1, 14, 0);
INSERT INTO `clanf_search_wordmatch` VALUES (2, 53, 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_sessions`
-- 

CREATE TABLE `clanf_sessions` (
  `session_id` char(32) NOT NULL default '',
  `session_user_id` mediumint(8) NOT NULL default '0',
  `session_start` int(11) NOT NULL default '0',
  `session_time` int(11) NOT NULL default '0',
  `session_ip` char(8) NOT NULL default '0',
  `session_page` int(11) NOT NULL default '0',
  `session_logged_in` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`session_id`),
  KEY `session_user_id` (`session_user_id`),
  KEY `session_id_ip_user_id` (`session_id`,`session_ip`,`session_user_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_sessions`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_smilies`
-- 

CREATE TABLE `clanf_smilies` (
  `smilies_id` smallint(5) unsigned NOT NULL auto_increment,
  `code` varchar(50) default NULL,
  `smile_url` varchar(100) default NULL,
  `emoticon` varchar(75) default NULL,
  PRIMARY KEY  (`smilies_id`)
) TYPE=MyISAM AUTO_INCREMENT=43 ;

-- 
-- Daten für Tabelle `clanf_smilies`
-- 

INSERT INTO `clanf_smilies` VALUES (1, ':D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO `clanf_smilies` VALUES (2, ':-D', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO `clanf_smilies` VALUES (3, ':grin:', 'icon_biggrin.gif', 'Very Happy');
INSERT INTO `clanf_smilies` VALUES (4, ':)', 'icon_smile.gif', 'Smile');
INSERT INTO `clanf_smilies` VALUES (5, ':-)', 'icon_smile.gif', 'Smile');
INSERT INTO `clanf_smilies` VALUES (6, ':smile:', 'icon_smile.gif', 'Smile');
INSERT INTO `clanf_smilies` VALUES (7, ':(', 'icon_sad.gif', 'Sad');
INSERT INTO `clanf_smilies` VALUES (8, ':-(', 'icon_sad.gif', 'Sad');
INSERT INTO `clanf_smilies` VALUES (9, ':sad:', 'icon_sad.gif', 'Sad');
INSERT INTO `clanf_smilies` VALUES (10, ':o', 'icon_surprised.gif', 'Surprised');
INSERT INTO `clanf_smilies` VALUES (11, ':-o', 'icon_surprised.gif', 'Surprised');
INSERT INTO `clanf_smilies` VALUES (12, ':eek:', 'icon_surprised.gif', 'Surprised');
INSERT INTO `clanf_smilies` VALUES (13, ':shock:', 'icon_eek.gif', 'Shocked');
INSERT INTO `clanf_smilies` VALUES (14, ':?', 'icon_confused.gif', 'Confused');
INSERT INTO `clanf_smilies` VALUES (15, ':-?', 'icon_confused.gif', 'Confused');
INSERT INTO `clanf_smilies` VALUES (16, ':???:', 'icon_confused.gif', 'Confused');
INSERT INTO `clanf_smilies` VALUES (17, '8)', 'icon_cool.gif', 'Cool');
INSERT INTO `clanf_smilies` VALUES (18, '8-)', 'icon_cool.gif', 'Cool');
INSERT INTO `clanf_smilies` VALUES (19, ':cool:', 'icon_cool.gif', 'Cool');
INSERT INTO `clanf_smilies` VALUES (20, ':lol:', 'icon_lol.gif', 'Laughing');
INSERT INTO `clanf_smilies` VALUES (21, ':x', 'icon_mad.gif', 'Mad');
INSERT INTO `clanf_smilies` VALUES (22, ':-x', 'icon_mad.gif', 'Mad');
INSERT INTO `clanf_smilies` VALUES (23, ':mad:', 'icon_mad.gif', 'Mad');
INSERT INTO `clanf_smilies` VALUES (24, ':P', 'icon_razz.gif', 'Razz');
INSERT INTO `clanf_smilies` VALUES (25, ':-P', 'icon_razz.gif', 'Razz');
INSERT INTO `clanf_smilies` VALUES (26, ':razz:', 'icon_razz.gif', 'Razz');
INSERT INTO `clanf_smilies` VALUES (27, ':oops:', 'icon_redface.gif', 'Embarassed');
INSERT INTO `clanf_smilies` VALUES (28, ':cry:', 'icon_cry.gif', 'Crying or Very sad');
INSERT INTO `clanf_smilies` VALUES (29, ':evil:', 'icon_evil.gif', 'Evil or Very Mad');
INSERT INTO `clanf_smilies` VALUES (30, ':twisted:', 'icon_twisted.gif', 'Twisted Evil');
INSERT INTO `clanf_smilies` VALUES (31, ':roll:', 'icon_rolleyes.gif', 'Rolling Eyes');
INSERT INTO `clanf_smilies` VALUES (32, ':wink:', 'icon_wink.gif', 'Wink');
INSERT INTO `clanf_smilies` VALUES (33, ';)', 'icon_wink.gif', 'Wink');
INSERT INTO `clanf_smilies` VALUES (34, ';-)', 'icon_wink.gif', 'Wink');
INSERT INTO `clanf_smilies` VALUES (35, ':!:', 'icon_exclaim.gif', 'Exclamation');
INSERT INTO `clanf_smilies` VALUES (36, ':?:', 'icon_question.gif', 'Question');
INSERT INTO `clanf_smilies` VALUES (37, ':idea:', 'icon_idea.gif', 'Idea');
INSERT INTO `clanf_smilies` VALUES (38, ':arrow:', 'icon_arrow.gif', 'Arrow');
INSERT INTO `clanf_smilies` VALUES (39, ':|', 'icon_neutral.gif', 'Neutral');
INSERT INTO `clanf_smilies` VALUES (40, ':-|', 'icon_neutral.gif', 'Neutral');
INSERT INTO `clanf_smilies` VALUES (41, ':neutral:', 'icon_neutral.gif', 'Neutral');
INSERT INTO `clanf_smilies` VALUES (42, ':mrgreen:', 'icon_mrgreen.gif', 'Mr. Green');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_themes`
-- 

CREATE TABLE `clanf_themes` (
  `themes_id` mediumint(8) unsigned NOT NULL auto_increment,
  `template_name` varchar(30) NOT NULL default '',
  `style_name` varchar(30) NOT NULL default '',
  `head_stylesheet` varchar(100) default NULL,
  `body_background` varchar(100) default NULL,
  `body_bgcolor` varchar(6) default NULL,
  `body_text` varchar(6) default NULL,
  `body_link` varchar(6) default NULL,
  `body_vlink` varchar(6) default NULL,
  `body_alink` varchar(6) default NULL,
  `body_hlink` varchar(6) default NULL,
  `tr_color1` varchar(6) default NULL,
  `tr_color2` varchar(6) default NULL,
  `tr_color3` varchar(6) default NULL,
  `tr_class1` varchar(25) default NULL,
  `tr_class2` varchar(25) default NULL,
  `tr_class3` varchar(25) default NULL,
  `th_color1` varchar(6) default NULL,
  `th_color2` varchar(6) default NULL,
  `th_color3` varchar(6) default NULL,
  `th_class1` varchar(25) default NULL,
  `th_class2` varchar(25) default NULL,
  `th_class3` varchar(25) default NULL,
  `td_color1` varchar(6) default NULL,
  `td_color2` varchar(6) default NULL,
  `td_color3` varchar(6) default NULL,
  `td_class1` varchar(25) default NULL,
  `td_class2` varchar(25) default NULL,
  `td_class3` varchar(25) default NULL,
  `fontface1` varchar(50) default NULL,
  `fontface2` varchar(50) default NULL,
  `fontface3` varchar(50) default NULL,
  `fontsize1` tinyint(4) default NULL,
  `fontsize2` tinyint(4) default NULL,
  `fontsize3` tinyint(4) default NULL,
  `fontcolor1` varchar(6) default NULL,
  `fontcolor2` varchar(6) default NULL,
  `fontcolor3` varchar(6) default NULL,
  `span_class1` varchar(25) default NULL,
  `span_class2` varchar(25) default NULL,
  `span_class3` varchar(25) default NULL,
  `img_size_poll` smallint(5) unsigned default NULL,
  `img_size_privmsg` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`themes_id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `clanf_themes`
-- 

INSERT INTO `clanf_themes` VALUES (1, 'subSilver', 'subSilver', 'subSilver.css', '', 'E5E5E5', '000000', '000000', '000000', '000000', '000000', 'EFEFEF', 'DEE3E7', 'D1D7DC', '', '', '', '98AAB1', '006699', 'FFFFFF', 'cellpic1.gif', 'cellpic3.gif', 'cellpic2.jpg', 'FAFAFA', 'FFFFFF', '', 'row1', 'row2', '', 'Verdana, Arial, Helvetica, sans-serif', 'Trebuchet MS', 'Courier, ''Courier New'', sans-serif', 10, 11, 12, '444444', '006600', 'FFA34F', '', '', '', NULL, NULL);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_themes_name`
-- 

CREATE TABLE `clanf_themes_name` (
  `themes_id` smallint(5) unsigned NOT NULL default '0',
  `tr_color1_name` char(50) default NULL,
  `tr_color2_name` char(50) default NULL,
  `tr_color3_name` char(50) default NULL,
  `tr_class1_name` char(50) default NULL,
  `tr_class2_name` char(50) default NULL,
  `tr_class3_name` char(50) default NULL,
  `th_color1_name` char(50) default NULL,
  `th_color2_name` char(50) default NULL,
  `th_color3_name` char(50) default NULL,
  `th_class1_name` char(50) default NULL,
  `th_class2_name` char(50) default NULL,
  `th_class3_name` char(50) default NULL,
  `td_color1_name` char(50) default NULL,
  `td_color2_name` char(50) default NULL,
  `td_color3_name` char(50) default NULL,
  `td_class1_name` char(50) default NULL,
  `td_class2_name` char(50) default NULL,
  `td_class3_name` char(50) default NULL,
  `fontface1_name` char(50) default NULL,
  `fontface2_name` char(50) default NULL,
  `fontface3_name` char(50) default NULL,
  `fontsize1_name` char(50) default NULL,
  `fontsize2_name` char(50) default NULL,
  `fontsize3_name` char(50) default NULL,
  `fontcolor1_name` char(50) default NULL,
  `fontcolor2_name` char(50) default NULL,
  `fontcolor3_name` char(50) default NULL,
  `span_class1_name` char(50) default NULL,
  `span_class2_name` char(50) default NULL,
  `span_class3_name` char(50) default NULL,
  PRIMARY KEY  (`themes_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_themes_name`
-- 

INSERT INTO `clanf_themes_name` VALUES (1, 'The lightest row colour', 'The medium row color', 'The darkest row colour', '', '', '', 'Border round the whole page', 'Outer table border', 'Inner table border', 'Silver gradient picture', 'Blue gradient picture', 'Fade-out gradient on index', 'Background for quote boxes', 'All white areas', '', 'Background for topic posts', '2nd background for topic posts', '', 'Main fonts', 'Additional topic title font', 'Form fonts', 'Smallest font size', 'Medium font size', 'Normal font size (post body etc)', 'Quote & copyright text', 'Code text colour', 'Main table header text colour', '', '', '');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_topics`
-- 

CREATE TABLE `clanf_topics` (
  `topic_id` mediumint(8) unsigned NOT NULL auto_increment,
  `forum_id` smallint(8) unsigned NOT NULL default '0',
  `topic_title` char(60) NOT NULL default '',
  `topic_poster` mediumint(8) NOT NULL default '0',
  `topic_time` int(11) NOT NULL default '0',
  `topic_views` mediumint(8) unsigned NOT NULL default '0',
  `topic_replies` mediumint(8) unsigned NOT NULL default '0',
  `topic_status` tinyint(3) NOT NULL default '0',
  `topic_vote` tinyint(1) NOT NULL default '0',
  `topic_type` tinyint(3) NOT NULL default '0',
  `topic_first_post_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_last_post_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_moved_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `topic_moved_id` (`topic_moved_id`),
  KEY `topic_status` (`topic_status`),
  KEY `topic_type` (`topic_type`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `clanf_topics`
-- 

INSERT INTO `clanf_topics` VALUES (1, 1, 'test', 5, 1104082388, 13, 1, 0, 0, 0, 1, 2, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_topics_watch`
-- 

CREATE TABLE `clanf_topics_watch` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(8) NOT NULL default '0',
  `notify_status` tinyint(1) NOT NULL default '0',
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`),
  KEY `notify_status` (`notify_status`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_topics_watch`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_user_group`
-- 

CREATE TABLE `clanf_user_group` (
  `group_id` mediumint(8) NOT NULL default '0',
  `user_id` mediumint(8) NOT NULL default '0',
  `user_pending` tinyint(1) default NULL,
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_user_group`
-- 

INSERT INTO `clanf_user_group` VALUES (3, 3, 0);
INSERT INTO `clanf_user_group` VALUES (3, 3, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_users`
-- 

CREATE TABLE `clanf_users` (
  `user_id` mediumint(8) NOT NULL default '0',
  `user_active` tinyint(1) default '1',
  `username` varchar(50) NOT NULL default '',
  `user_password` varchar(32) NOT NULL default '',
  `user_session_time` int(11) NOT NULL default '0',
  `user_session_page` smallint(5) NOT NULL default '0',
  `user_lastvisit` int(11) NOT NULL default '0',
  `user_regdate` int(11) NOT NULL default '0',
  `user_level` tinyint(4) default '0',
  `user_posts` mediumint(8) unsigned NOT NULL default '0',
  `user_timezone` decimal(5,2) NOT NULL default '0.00',
  `user_style` tinyint(4) default NULL,
  `user_lang` varchar(255) default NULL,
  `user_dateformat` varchar(14) NOT NULL default 'd M Y H:i',
  `user_new_privmsg` smallint(5) unsigned NOT NULL default '0',
  `user_unread_privmsg` smallint(5) unsigned NOT NULL default '0',
  `user_last_privmsg` int(11) NOT NULL default '0',
  `user_emailtime` int(11) default NULL,
  `user_viewemail` tinyint(1) default NULL,
  `user_attachsig` tinyint(1) default NULL,
  `user_allowhtml` tinyint(1) default '1',
  `user_allowbbcode` tinyint(1) default '1',
  `user_allowsmile` tinyint(1) default '1',
  `user_allowavatar` tinyint(1) NOT NULL default '1',
  `user_allow_pm` tinyint(1) NOT NULL default '1',
  `user_allow_viewonline` tinyint(1) NOT NULL default '1',
  `user_notify` tinyint(1) NOT NULL default '1',
  `user_notify_pm` tinyint(1) NOT NULL default '0',
  `user_popup_pm` tinyint(1) NOT NULL default '0',
  `user_rank` int(11) default '0',
  `user_avatar` varchar(100) default NULL,
  `user_avatar_type` tinyint(4) NOT NULL default '0',
  `user_email` varchar(255) default NULL,
  `user_icq` varchar(15) default NULL,
  `user_website` varchar(100) default NULL,
  `user_from` varchar(100) default NULL,
  `user_sig` text,
  `user_sig_bbcode_uid` varchar(10) default NULL,
  `user_aim` varchar(255) default NULL,
  `user_yim` varchar(255) default NULL,
  `user_msnm` varchar(255) default NULL,
  `user_occ` varchar(100) default NULL,
  `user_interests` varchar(255) default NULL,
  `user_actkey` varchar(32) default NULL,
  `user_newpasswd` varchar(32) default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `user_session_time` (`user_session_time`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_users`
-- 

INSERT INTO `clanf_users` VALUES (-1, 1, 'Cheater', '', 0, 0, 0, 0, 0, 0, 0.00, NULL, NULL, 'd M Y H:i', 0, 0, 0, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_vote_desc`
-- 

CREATE TABLE `clanf_vote_desc` (
  `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_text` text NOT NULL,
  `vote_start` int(11) NOT NULL default '0',
  `vote_length` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_vote_desc`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_vote_results`
-- 

CREATE TABLE `clanf_vote_results` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
  `vote_option_text` varchar(255) NOT NULL default '',
  `vote_result` int(11) NOT NULL default '0',
  KEY `vote_option_id` (`vote_option_id`),
  KEY `vote_id` (`vote_id`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_vote_results`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_vote_voters`
-- 

CREATE TABLE `clanf_vote_voters` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_user_id` mediumint(8) NOT NULL default '0',
  `vote_user_ip` char(8) NOT NULL default '',
  KEY `vote_id` (`vote_id`),
  KEY `vote_user_id` (`vote_user_id`),
  KEY `vote_user_ip` (`vote_user_ip`)
) TYPE=MyISAM;

-- 
-- Daten für Tabelle `clanf_vote_voters`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `clanf_words`
-- 

CREATE TABLE `clanf_words` (
  `word_id` mediumint(8) unsigned NOT NULL auto_increment,
  `word` char(100) NOT NULL default '',
  `replacement` char(100) NOT NULL default '',
  PRIMARY KEY  (`word_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `clanf_words`
-- 

        