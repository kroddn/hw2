-- Tabelle für Speicherung der MailFail Fehlermeldungen vom
-- SMTP Server


CREATE TABLE IF NOT EXISTS `mailfail` (
  failid int(10) NOT NULL PRIMARY KEY auto_increment ,
  email varchar(256) NOT NULL UNIQUE,
  type int DEFAULT 1,
  inserttime int NOT NULL,  -- UNIX_TIMESTAMP()
  source text               -- Grund oder Quelle für die Eintragung
  
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;