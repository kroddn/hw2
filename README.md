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





Die SQL-Dateien aus dem Ordner SQL sind die Tabellendefinitionen, und die haben GENAU den Sinn :-)

Leider gibts noch kein Script, dass die Dateien in der richtigen Reihenfolge an MYSQL übergibt.

Ich würde mich freuen, wenn jemand ein Bash oder PHP-Script dafür schreiben möchte.

Die Mysql Datenbank muss als "collation" latin1_german2_ci haben.

Es fehlen sicherlich auch noch Tabellen. Die aktuell verfügbaren sollten aber reichen, so dass zumindest mal ein Login stattfinden kann.