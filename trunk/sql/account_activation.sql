-- Erzeuge diese Tabelle in der Datenbank "global"
-- 
-- Der Benutzer account_activation hat vollzugriff auf diese Tabelle,
-- und Zugriff auf die player-Tabellen der Spielrunden.



CREATE TABLE IF NOT EXISTS `account_activation` (
  keyid int(10) NOT NULL PRIMARY KEY auto_increment,
  activationkey varchar(32) NOT NULL,
  game1 varchar(16) NOT NULL, -- game1, game2, speed, hispeed
  
  
  
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;  