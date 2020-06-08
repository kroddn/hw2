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

/***************************************************
 * written 2005-2007 for Holy-Wars 2
 *				
 * by
 * Markus Sinner <kroddn@holy-wars2.de>
 *
 * This File must not be used without permission
 ***************************************************/

// Funktionen für Premium-Accounts
//
// die Daten für Premiem-Accounts des Spielers befinden
// sich in einer Extra-Tabelle "premiumacc"

// Tabelledefinition und erklärung siehe sql/premium.sql

// für defines benötigt
include_once("includes/db.config.php");  

// --- Konstanten für Werbefreie Accs ---
// Normalerweise sind für ein FLAG auch alle darunterliegenden Flags
// gesetzt. Ausnahme ist biesher NOADS, das kann man "ausschalten" für
// Leute, die Zusätzlich zum Premium-Acc doch noch Werbebanner klicken
// wollen.
define("PREMIUM_NOADS",  1);
define("PREMIUM_LITE",   2);
define("PREMIUM_MEDIUM", 4);
define("PREMIUM_PRO",    8);
define("PREMIUM_ULTRA", 16);

// Ende von Weihnachten
define("XMAS_END", 1135638172);


/**
 * Liefere eine Zeile/Array mit dem aktuell laufenden Premium-Datensatz zurück.
 * Die gültigen Datensätze werden nach Type sortiert, damit immer der "beste"
 * Premium-Account aktiviert wird.
 */
function get_premium_row($pid) 
{
  // Hole den den neuesten gültigen Datensatz
  $result = do_mysql_query ("SELECT type,expire,payd FROM premiumacc ".
                            " WHERE player = ".intval($pid)." AND type > 0 AND start < UNIX_TIMESTAMP() ".
                            "   AND (expire > UNIX_TIMESTAMP() OR expire = 0) ".
                            " ORDER BY type DESC, expire!=0, expire DESC LIMIT 1");
  return mysqli_fetch_array($result);
}


/**
 * Diese Funktion returniert die akutell gültigen
 * Premium-Flags des Spielers
 **/
function get_premium_flags($pid) {
  $flag = get_premium_row($pid);
  if ($flag) {
    return $flag['type'];
  }
  else {
    return 0;
  }
}


/**
 * Ablaufdatum des Accounts als TIMESTAMP zurückliefern
 */
function get_premium_payd($pid) {
  $flag = get_premium_row($pid);
  
  if ($flag) {
    return $flag['payd']; 
  }
  else
    return null;
}

/**
 * Ablaufdatum des Accounts als TIMESTAMP zurückliefern
 */
function get_premium_expire($pid) {
  $flag = get_premium_row($pid);
  
  if ($flag) {
    return $flag['expire']; 
  }
  else
    return null;
}

/**
 * Ablaufdatum des Accounts als Datum/Uhrzeit String zurückliefern
 */
function get_premium_expire_string($expire)
{
  if ($expire != null) {
    if ($expire == 0) 
      return "nie";
    else
      return date("d.m.Y G:i", $expire);
  }
  else {
    // Weihnachtsgeschenk :-)
    if (time() < XMAS_END) {
      return date("d.m.Y G:i", XMAS_END);
    }
    return "jetzt";
  }
}


// Ist der Account Premium??
function is_premium () {
  return $_SESSION['premium_flags'] > 0;
}

// Ist der Account Premium??
function is_premium_payd () {
  return $_SESSION['premium_flags'] > 0 && $GLOBALS['premium_payd'] > 0;
}

// Ist der Account werbefrei?
function is_premium_noads () {
  if(isset($_SESSION['first_login']) && $_SESSION['first_login']) return true;
  $prem_flag = isset($_SESSION['premium_flags']) ? $_SESSION['premium_flags'] : 0;
  
  return $prem_flag & PREMIUM_NOADS && $_SESSION['settings']['hide_banner'];
}


// Hat der Premium-Account Turnier-Vorteile?
function is_premium_tournament () {
  return $_SESSION['premium_flags'] & PREMIUM_NOADS;
}


// Hat der Account ein Adressbuch?
function is_premium_adressbook () {
  // Weihnachtsgeschenk :-)
  if (time() < XMAS_END) {
    return true;
  }

  return $_SESSION['premium_flags'] >= PREMIUM_LITE;
}


// Ist der Account vom Hinweis auf Werbung (HALT!) befreit?
function is_premium_no_click_hint () {
  // Weihnachtsgeschenk :-)
  if (time() < XMAS_END) {
    return true;
  }
  
  if(isset($_SESSION['first_login']) && $_SESSION['first_login']) return true;
  
  return $_SESSION['premium_flags'] >= PREMIUM_NOADS;
}


// Darf der Account eine eigene Signatur setzen?
function is_premium_signature () {
  // Weihnachtsgeschenk :-)
  if (time() < XMAS_END) {
    return true;
  }

  return $_SESSION['premium_flags'] >= PREMIUM_LITE;
}


// Darf er nen Avatar hochladen?
function is_premium_avatar () {
  return $_SESSION['premium_flags'] >= PREMIUM_LITE;
}


// Wieviel Postausgang hat der Spieler
function get_message_archive_size () {
  $premium_flags = $_SESSION['premium_flags'];
  
  if ($premium_flags & PREMIUM_ULTRA)  return 10000;
  if ($premium_flags & PREMIUM_PRO)    return   200;
  if ($premium_flags & PREMIUM_MEDIUM) return   100;
  if ($premium_flags & PREMIUM_LITE)   return    50;

  // Weihnachtsgeschenk :-)
  if (time() < XMAS_END) {
    return 50;
  }

  if ($premium_flags & PREMIUM_NOADS)  return     0;
  return 0;
}


/** 
 * Die maximale Sitzungsdauer, nach der man rausgeworfen wird
 * (unabhängig von der Session-Livetime des Apache) (normalerweise 60 Minuten)
 */
function get_premium_session_time() {
  $premium_flags  = $_SESSION['premium_flags'];
  $premium_expire = $_SESSION['premium_expire'];
  
  if(defined("OLD_GAME") && OLD_GAME)     $default = SESSION_DEFAULT_TIME / 3; // OLD
  else if( defined("HISPEED") && HISPEED) $default = SESSION_DEFAULT_TIME / 6;  // HiSpeedrunde
  else if( defined("SPEED") && SPEED)     $default = SESSION_DEFAULT_TIME / 2; // Speedrunde
  else                                    $default = SESSION_DEFAULT_TIME; // Normale Session-Länge
  
  // Den Ablauf des PA einberechnen.
  $max = max($premium_expire-time(), $default);
  
  // Falls Premium-Account in Besitz, entsprechende Sessionzeiten returnieren
  if ($premium_flags & PREMIUM_ULTRA)  return min($max, 480 * SESSION_DEFAULT_TIME);
  if ($premium_flags & PREMIUM_PRO)    return min($max, 480 * SESSION_DEFAULT_TIME);
  if ($premium_flags & PREMIUM_MEDIUM) return min($max,  24 * SESSION_DEFAULT_TIME);
  if ($premium_flags & PREMIUM_LITE)   return min($max,  12 * SESSION_DEFAULT_TIME);

  // Weihnachtsgeschenk :-)
  if (time() < XMAS_END) {
    return 12*3600;
  }

  if ($premium_flags & PREMIUM_NOADS)  return   4 * SESSION_DEFAULT_TIME;

  return $default;
}


// Wieviel Angebote bekommt der Spieler pro Seite
function get_market_size () {
  $premium_flags  = $_SESSION['premium_flags'];
  
  if ($premium_flags & PREMIUM_ULTRA)  return 20;
  if ($premium_flags & PREMIUM_PRO)    return 20;
  if ($premium_flags & PREMIUM_MEDIUM) return 20;
  if ($premium_flags & PREMIUM_LITE)   return 12;

  // Weihnachtsgeschenk :-)
  if (time() < XMAS_END) {
    return 12;
  }

  if ($premium_flags & PREMIUM_NOADS)  return 8;
  return 5;
}


function is_premium_set_sms_sender() {
  return $_SESSION['premium_flags'] >= PREMIUM_PRO;
}


function is_premium_diplomap() {
  return $_SESSION['premium_flags'] >= PREMIUM_LITE;
}

function printPaypal() {
  echo '<h3>Wenn ihr mich unterstützen wollt :)</h3>';
  echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">';
  echo '<input type="hidden" name="cmd" value="_s-xclick" border="0"/>';
  echo '<input type="hidden" name="hosted_button_id" value="2J98MFNZKBMT4" />';
  echo '<input type="image" style="border-style: none;"  src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_SM.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Spenden mit dem PayPal-Button" />';
  echo '<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1" />';
  echo '</form>';
}
?>