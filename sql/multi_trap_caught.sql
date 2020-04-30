-- Tables needed by multihunter / multitrap

CREATE TABLE `multi_trap_caught` (
 `multi` INT NOT NULL ,
 `cookieowner` INT NOT NULL ,
 `time` INT NOT NULL,
 `code` varchar(32) COLLATE latin1_german2_ci NOT NULL DEFAULT '' COMMENT 'Code im Cookie'
) ;

ALTER TABLE `multi_trap_caught` ADD INDEX ( `multi` , `cookieowner` ) ;
