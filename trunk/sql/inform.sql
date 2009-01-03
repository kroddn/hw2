CREATE TABLE `inform` (
`infid` INT NOT NULL AUTO_INCREMENT ,
time   INT NOT NULL,
`topic` VARCHAR( 100 ) NOT NULL ,
`text` TEXT NOT NULL ,
PRIMARY KEY ( `infid` )
);


CREATE TABLE `inform_player` (
infid  INT NOT NULL,
player INT NOT NULL,
time   INT NOT NULL,
PRIMARY KEY ( `infid`, player )
);
