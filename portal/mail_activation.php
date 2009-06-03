<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2009 
    by Markus Sinner

    "Holy-Wars 2" is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Former copyrights see below.
 **************************************************************************/

/**
 * Empfange emails über STDIN. Durchsuche die Email nach entsprechender Zeichenkette.
 * http://www.exim.org/exim-html-3.20/doc/html/spec_18.html
 * 
 * ==== Maildaemon ====
 * 
 * Der Maildaemon muss so konfiguriert werden, dass er eine Email an dieses PHP-Skript
 * übergibt.
 * Bei EXIM4 erreicht man das, indem man in der alias oder virtual Konfiguration eine Zeile
 * einfügt, derart:
 *    targetaddress:    "|/usr/bin/php -f /path/to/mail_activation.php
 * 
 * Wird dann eine Email an <targetaddress@domain.xx> gesendet, wird mail_activation.php
 * ausgeführt und der Inhalt des Emails über STDIN gesendet.
 * 
 * Manchnal kann es sinnvoll sein, zusätzlich zu dieser PIPE noch einen Empfänger
 * anzugeben, damit bei einem Fehler des Skriptes die Email zugestellt wird.
 */

define("LOGFILE", "/tmp/parse_email.log");

file_put_contents(LOGFILE, "SENDER: ".getenv("SENDER")."\n", FILE_APPEND);

// read from stdin
$fd = fopen("php://stdin", "r");
if($fd) {
  $bytes = 0;
  $email = "";
  
  // Read until eof
  while (!feof($fd)) {
    // Read maximum of 64KB - bigger mails should
    // not arrive.
    if($bytes < 65536) {
      $email .= fread($fd, 1024);
    }
    $bytes += 1024;
  }
  fclose($fd);

  
  parse_email($email);
}
else {
  // Error - no contents in email
  file_put_contents(LOGFILE, "No content.\n", FILE_APPEND);
}

/**
 * Verarbeite die Email.
 * Sucht nach "From:" und nach "Subject:" im Email.
 * Dort sollte die Emailadresse des Ansenders und die ID des 
 * zugehörigen Spielers zu finden sein.
 * 
 */
function parse_email($email) {
  file_put_contents(LOGFILE, $email, FILE_APPEND);
  
  echo "NOT IMPLEMENTED YET!\r\n";
  exit(1);
}

?>