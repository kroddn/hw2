html/
 Hier befinden sich die PHP Skripte fuer den Webserver

html/includes/db.config.php
 Diese Datei konfiguriert den Webserver. Dort sind auch
 die Passwoerter fuer die DB hinterlegt. Zugriff nur fuer
 Admins.
 FIXME: sollte zukuenftig nach html/config/xxx


cron/
 Hier bedinden sich Skripte fuer regelmaessige Ausfuehrung.

cron/hwha
 Daemon, der Bau-Auftraege und Forschung fertig stellt.

cron/service.sh
html/includes/service.php
 PHP-Skript fuer den Service-Daemon. Mit service.sh kann
 der Daemon gestartet werden. Das ausfuehren benoetigt root-Rechte,
 um den Daemon "nice" zu starten.


sql/
 Hier befinden sich SQL Skripte zum Aufbau der MySQL Datenbank.
 Ein Setup-Skript gibt es derzeit nicht.