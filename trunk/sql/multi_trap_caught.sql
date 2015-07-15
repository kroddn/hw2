-- Tables needed by multihunter / multitrap

CREATE TABLE `multi_trap_caught` (
 `multi` INT NOT NULL ,
 `cookieowner` INT NOT NULL ,
 `time` INT NOT NULL,
 `code` varchar(20), NOT NULL
) ;

ALTER TABLE `multi_trap_caught` ADD INDEX ( `multi` , `cookieowner` ) ;
