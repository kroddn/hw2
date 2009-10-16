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
 * Copyright (c) 2005
 *
 * Markus Sinner
 * Gordon Meiser
 * Lorenz Gamper
 *
 ***************************************************/
require_once("includes/config.inc.php");
require_once("includes/db.config.php");
require_once("includes/log.inc.php");
require_once("conf/db.conf.php"); // Zugangsdaten zur Datenbank

// Set default images
if (!isset($imagepath)) {
  if (defined("GFX_PATH"))
    $imagepath = GFX_PATH;
  else
    $imagepath = "images/ingame";
}
if (!isset($csspath )) {
  if (defined("GFX_PATH_LOCAL")) 
    $csspath = GFX_PATH_LOCAL."/css";
  else
    $csspath = $imagepath."/css";
}

$GLOBALS['con'] = mysql_pconnect( DBHOST, DBUSER, DBPASSWD);
mysql_select_db( DBSELECT, $GLOBALS['con']);

function do_mysql_query_fetch_array($sql) {
  return mysql_fetch_assoc(do_mysql_query($sql));
}


function do_mysql_query_fetch_assoc($sql) {
  return mysql_fetch_assoc(do_mysql_query($sql));
}


/**
 * Eigene MYSQL Query Funktion
 * 
 * @param $string 
 * @param $conn   Mysql-Connection, auf die das Query abgesetzt wird. NULL bedeuted Standard-Session Verbindung.
 * @param $abort  Falls TRUE wird nach dem Logging eine Fehlermeldung ausgegeben und die weitere Verarbeitung
 *                abgebrochen. Falls FALSE wird die Verarbeitung nach dem Fehler fortgesetzt (z.B. bei diversen
 *                queries, die abgearbeitet werden MÜSSEN, um keinen Fehler in der DB zu erzeugen)
 * @return Rückgabewert entspricht dem der Funktion mysql_query
 */
function do_mysql_query($string, $conn=null, $abort = true) {
  global $HTTP_SERVER_VARS, $_GET, $_POST, $player;

  $get = $post = $pid = $scriptname = "NULL";

  if (sizeof($_GET)>0) {
    $get = "?";
    foreach ($_GET as $key => $value) {
      if ($get!="?") $get .= "&";
      $get .= $key."=".$value;
    }
    $get = "'".mysql_escape_string($get)."'";
  }

  if (sizeof($_POST)>0) {
    $post = "?";
    foreach ($_POST as $key => $value) {
      if ($post != "?") $post .= "&";
      $post .= $key."=".$value;
    }
    $post = "'".mysql_escape_string($post)."'";
  }

  // Hm. Das hier ist noch problematisch...
  if (isset($player) && strtolower(get_class($player)) == "player" ) {
    include_once("includes/player.class.php");
    $pid = $player->getID();
  }

  // Speichern, welches Skript aufgerufen wurde. __FILE__ würde ja
  // leider immer db.inc.php zurückliefern
  $scriptname = $_SERVER['SCRIPT_FILENAME'];

  // AAAAlso. Ich stand eben hier davor und wusste nichtmehr was der Code macht :-)
  // so, mal kurz dokumentieren. Im Hintergrund schwallt Richi mal wieder wirres Zeug
  // Die SQL Query ($string) wird ausgeführt. Falls das Result ($res) fehlerhaft ist,
  // dann wird der Fehler in der DB gespeichert. An dieser Stelle haben wir versucht,
  // den gläsernen User gleich mitzuspeichern :-)
  if($conn == NULL) {
    $conn = $GLOBALS["con"];
  } 
  
  if (! ($res = mysql_query($string, $conn)) ) {
    ob_start();
    debug_print_backtrace();
    // jetzt wird der content ins log geschrieben.
    $trace = ob_get_contents();
    ob_end_clean();
    
    $scripttrace = mysql_escape_string($scriptname."\n". $trace);
    $sql = "INSERT INTO log_mysqlerr(qry,err,time,scriptname,referer, player, post_str, get_str) VALUES ('".mysql_escape_string($string)."',\n'".mysql_escape_string(mysql_error())."',\n".time().",\n'".$scripttrace."',\n'".mysql_escape_string($HTTP_SERVER_VARS['HTTP_REFERER'])."',\n $pid,\n $post,\n $get)";

    // Diese Query wird nun auf alle Fälle auf das Session-Objekt "con" ausgeführt,
    // weil die Fehlermeldungen auf alle Fälle im Gameserver-Log landen sollen.
    $res = mysql_query($sql, $GLOBALS["con"]);

    // Wenn das hier passiert dann is wohl was mti MySQL kaputt
    if (!$res) {
      echo '<br>Ein SEHR schwerwiegender Fehler ist aufgetreten! Bitte sofort an die Admins wenden: <a href="admin@holy-wars2.de">admin@holy-wars2.de</a>:<br>'.mysql_error()."<br>";
    }

    $errid = mysql_insert_id();

    $errStr = (strtolower(get_class($player)) == "player" && $player->isAdmin() ? '<a href="adminlog.php">' : '<a href="mailto:admin@holy-wars2.de">') ."MYSQL:".$errid."</a>";

    if($abort) {
      show_log_fatal_error($errStr);
    }
    else {
      show_error($errStr);
    }
  }
  else {
    // alles okay
    return $res;
  }
}


?>
