CREATE TABLE log_convert (
`time` INT NOT NULL COMMENT 'Zeitpunkt',
`city` INT NOT NULL COMMENT 'Stadt',
`player` INT NOT NULL COMMENT 'Spieler',
`loyality` INT NOT NULL COMMENT 'Loyalität zum Zeitpunkt der Konvertierung',
`bonus` INT NOT NULL COMMENT 'Bonustruppen'
) ENGINE = MYISAM COMMENT = 'Logging der Konvertierungen'
;

CREATE INDEX idx_city_player ON log_convert (city, player);

