<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2009 
    by Markus Sinner, Gordon Meiser, Laurenz Gamper, Stefan Neubert

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
 * Funktionen zum Versand (bzw. Empfang) von SMS aus den Skripten heraus.
 * 
 *
 *(C) 2005-2008 psitronic IT-Solutions  
 *    written by Markus Sinner, 02.12.2005
 */

// Include config mit Username/Passwort
include_once("/etc/hw2/sms.config.php");

define("SMS_MAX_LEN", 160);
define("SMS_SEND_ACCEPT", 'Sie müssen zunächst den <a href="settings.php?show=sms">Bedingungen zum SMS-Versand</a> zustimmen. Erst dann ist der Versand freigeschalten.');

define("SMS_TEXT_APPEND", " - holy-wars2.de");
define("SMS_TEXT_APPEND_LONG", " - jetzt bei holy-wars2.de registrieren");

// sms_settings options
define("SMS_SETTINGS_OPTION_SEND_OWN_NUMBER", 1);


/***********
-- SQL-Tabelle
-- 
-- 
CREATE TABLE sms_send(
  sms_id int(10) unsigned NOT NULL auto_increment,
  sender int(10) unsigned NOT NULL,   -- Referenz auf player Tabelle
  sendernr varchar(20),               -- Nr. des Absenders
  nr     varchar(20)      NOT NULL,   -- Adressat
  text   varchar(160)     NOT NULL,   -- Inhalt der SMS
  cost   int(10)          NOT NULL DEFAULT 15, -- Kosten der SMS in CENT
  
  create_time int(10) unsigned NOT NULL,              -- Wann wurde die SMS vom Spieler zum versenden eingestellt
  sent_time   int(10) unsigned DEFAULT NULL,          -- Wann wurde die SMS versand?

  PRIMARY KEY  (sms_id)
);
***********/

$GLOBALS['smsprefixes']
= array("0150",
        "0151",
        "0152",
        "01520",
        "0156", // Neue TD1-Vorwahl
        "01577",
        "0160",
        "0162",
        "0163",
        "0168",
        "0170",
        "0171",
        "0172",
        "0173",
        "0174",
        "0175",
        "0176",
        "0177",
        "0178",
        "0179"
        );

function get_prefix_array() {
  return $GLOBALS['smsprefixes'];
}

// Eine SMS hat dir Form +4917012345678
// sie startet mit +49
// darauf folgt 15x, 16x oder 17x
// dann kommt eine 7-8 stellige Nummer
function valid_sms_nr($nr) {
  $smsRegEx = "^\+491[5-7][0-9][0-9]{7,8}$";

  return ereg($smsRegEx, $nr);
}


/**
 * SMS an eine Nummer senden.
 */
function sms_send($nr, $text) {
  // Prüfen, ob der Service überhaupt aktiviert wurde
  if( !defined("SMS_SERVICE") || !SMS_SERVICE ) {
    return "SMS_DEACTIVATED";
  }

  // Nur alle 10 Sekunden zulassen
  if (time() - $_SESSION['last_sent_sms'] < 10)
    return "Zwischen dem Versand zweier SMS müssen Sie mindestens 10 Sekunden warten.";

  // Gültigkeit der Nummer überprüfen
  if ( !valid_sms_nr($nr) ) {
    return "Die Nummer des Empfängers ($nr) ist ungültig.";
  }


  // Leerzeichen abschneiden
  $text = trim($text);
  
  // Länge der Nachricht prüfen
  $len = strlen($text);
  if ($len > SMS_MAX_LEN ) {
    return "Text ist länger als ".SMS_MAX_LEN." Zeichen";
  }

  // Falls noch genug Platz ist, dann hängen wir Werbung an.
  if (SMS_MAX_LEN - $len >= strlen(SMS_TEXT_APPEND_LONG) ) {
    $text .= SMS_TEXT_APPEND_LONG;
  }
  else if (SMS_MAX_LEN - $len >= strlen(SMS_TEXT_APPEND) ) {
    $text .= SMS_TEXT_APPEND;
  }    
  
  // Zeitpunkt des Versandes nun zwischenspeichern
  $_SESSION['last_sent_sms'] = time(); 
  
  $sender = null;
  if ($_SESSION['player']->showSMSSenderNumber() && $_SESSION['player']->isValidSMSSenderNumber() ) {
    $sender = $_SESSION['player']->getSMSSenderNumber();
  }

  // Erstmal ins log eintragen
  $sql = sprintf("INSERT INTO sms_send(nr, text, sender, sendernr, create_time) ".
                 " VALUES ('%s', '%s', %d, %s, UNIX_TIMESTAMP() )", 
                 $nr, 
                 mysql_escape_string($text), 
                 $_SESSION['player']->getID(), 
                 $sender == null ? "NULL" : "'$sender'" );

  // Debug for Admins
  if ($_SESSION['player']->isAdmin()) {
    echo "\n<br>".$sql."<br>\n";
  }
  
  // INSERT
  $res = do_mysql_query($sql);
  $sms_id = mysql_insert_id();
  
  // An dieser Stelle nun wirklich die SMS versenden
  do_mysql_query("UPDATE sms_settings SET updated=UNIX_TIMESTAMP(), ".
                 " contingent=contingent-1, contingent_used=contingent_used+1, ".
                 " contingent_freesms = contingent_freesms - 1 ".
                 "WHERE contingent > 0 AND player = ".$_SESSION['player']->getID());
  if (mysql_affected_rows() > 0) {
    // An dieser Stelle prüfen, ob der Spieler ne Absenderkennung
    // eingestellt hat. In diesem Falle wird die Absenderkennung
    // eingestellt.
    $result = real_send_text_sms($nr, $text, $sender);

    if (strlen($result) > 0) {
      do_mysql_query("UPDATE sms_settings SET updated=UNIX_TIMESTAMP(), ".
                     " contingent=contingent+1, contingent_used=contingent_used-1, ".
                     " contingent_freesms = contingent_freesms + 1 ".
                     "WHERE contingent >= 0 AND player = ".$_SESSION['player']->getID());
      check_sms_settings();
      return $result;
    }
    
    do_mysql_query("UPDATE sms_send SET sent_time = UNIX_TIMESTAMP() WHERE sms_id = ".$sms_id);
    check_sms_settings();
    return null;
  }
  else {
    return "Ihr Kontingent ist aufgebraucht.";
  }
}



/**
 * SMS wirklich versenden.
 *
 * Ruft das HTTP-Interface der Firma "Mobile Marketing System" auf.
 * http://www.mobile-marketing-system.de/faq/http-schnittstelle.
 * 
 * Die Konstanten SMS_xxx sind in /etc/hw2/sms.config.php definiert.
 */
function real_send_text_sms($empfaenger, $text, $absender_nr = null) {
  if($text == null || strlen($text) < 1) return "FAIL_SMS_EMPTY";
  $text = trim($text);
  if (strlen($text) > SMS_MAX_LEN) return "FAIL_SMS_MAX_LEN";
  
  $username     = SMS_SERVER_USERNAME; // Kundennummer eintragen
  $password     = SMS_SERVER_PASSWORD; // in den Einstellungen definiertes Passwort
  $kostenstelle = SMS_SERVER_KOSTENSTELLE;
  
  //  $absender = "84040"; // 99ct
  //$absender = "88881"; // 49ct
  //$absender = "HolyWars2de";

  //  $url = "http://login.mobile-marketing-system.de/xmlrpc/send_extern.php";
  $url = SMS_SERVER_URL;

  // Route 2 ist billiger, es lässt sich aber kein absender einstellen
  if(1) {
    $route    = "route1";
    $absender = "HolyWars2de";
  }
  else {
    $route    = "route2";
  }
      
  // Bei Premium-Pro Usern ist die Absendernummer einstellbar,
  // das nun hier durchführen. In diesem Fall MUSS route1 benutzt
  // werden, da nur dort der Absender frei konfigurierbar ist.
  if ($absender_nr != null) {
    $absender = $absender_nr;
    $route    = "route1";
  }

  $sms_query = sprintf("%s".
                       "?username=%s&password=%s&text=%s".
                       "&recipient=%s&route=%s".
                       "&cost_centre=%s%s",
                       $url,
                       urlencode($username),
                       urlencode($password),
                       urlencode($text),
                       urlencode($empfaenger),
		       $route,
                       urlencode($kostenstelle),
                       isset($absender) ? "&sender=".urlencode($absender) : ""
                       );
                       
  // Im Admin-Account Debug-Ausgaben anzeigen
  if($_SESSION['player']->isAdmin()) {
    echo "\n<!-- $sms_query -->\n";
  }
  
  
  if(function_exists("curl_init"))
  {
    $ch = curl_init($sms_query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
  }
  else
  {
    // Siehe http://de2.php.net/manual/en/reserved.variables.php#reserved.variables.phperrormsg
    ini_set("track_errors", "1");

    $fp = @fopen($sms_query, "r");

    $errormsg = $php_errormsg;
    ini_set("track_errors", "0");

    if($fp) {
      $result = ""; // Leer initialisieren
      
      while(!feof($fp))
      {
        $result .= fread($fp, 4096);
      }
      fclose($fp);
    }
    else {
      $result = "Fehler beim Öffnen";
      log_fatal_error("SMS-Senden schlug fehl: '".$errormsg."'");
    }
  }

  if(stristr($result, "OK MSG")) {
    $result = null;
  }
  else {
      // FIXME: hier könnte man die unter:
      // http://www.mobile-marketing-system.de/faq/http-schnittstelle/#http-schnittstelle-meldet-fehler
      // zu findenden Resultcodes auswerten.
    if($result == "011") {
      $result = "&quot;Ungültige Zeichen (Nicht GSM-Zeichen) im Nachrichtentext&quot;";
    }   
  }
  
  
  return $result;
}


function check_sms_settings() {
  $sms_res = do_mysql_query("SELECT * FROM sms_settings WHERE player = ".$_SESSION['player']->getID());
  if (mysql_num_rows($sms_res) == 1) {
    $sms_settings = mysql_fetch_assoc($sms_res);
    $_SESSION['sms_may_send']   = true;
    $_SESSION['sms_contingent'] = $sms_settings['contingent'];
    if ( $_SESSION['sms_contingent'] < 0 ) {
      send_contingent_warn();
    }
  }
  else {
    $_SESSION['sms_may_send']   = false;
    $_SESSION['sms_contingent'] = 0;
  }
}


function send_contingent_warn () {
  $headers  = "MIME-Version: 1.0\n";
  $headers .= "FROM: sms@holy-wars2.de\n";
  $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
  
  $body = sprintf("Achtung. Der Spieler %s hat ein negatives SMS-Kontingent: %d.\nBitte Überprüfen.",
                  $_SESSION['player']->getName(),
                  $_SESSION['sms_contingent']
                  );

  mail( "admin@holy-wars2.de", "SMS Kontingent unter 0", $body, $headers );
}

function accept_sms_rules() {
  if (time() < 1135871808+53*3600)
    $default_contingent = 10;
  else 
    $default_contingent = 1;


  $sql = sprintf("INSERT INTO sms_settings (player, contingent, created, updated)".
                 " VALUES (%s, %d, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                 $_SESSION['player']->getID(),
                 $default_contingent);
  
  do_mysql_query($sql);
}
?>