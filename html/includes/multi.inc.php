<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2010 
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

ob_start();

include_once("includes/player.class.php");

function request_exception($notes, $players) {
  $plist = split(",", $players);
  show_error($players."  XX  ".join(";", $plist));
  foreach ($plist as $p) {
    if (checkBez($p, 4, 40)) {
      $pid_res = do_mysqli_query("SELECT id FROM player WHERE name='".trim($p)."'");
      $pid = mysqli_fetch_assoc($pid_res);
      if ($pid['id'] > 0)
	$ids[] = $pid['id'];
      else {
	show_error("Der Spieler mit dem Namen '".$p."' konnte nicht gefunden werden.");
	return;
      }
    } 
    else {
      show_error("Der Name '".$p."' entspricht nicht den Namenskonventionen.");
      return;
    }
  }

  //TODO: überprüfen, ob schon eine gleichartige exception existiert, wenn ja, einfach notes hinzufügen

  do_mysqli_query("INSERT INTO multi_exceptions (type, time, valid, comment) VALUES (0, ".time().", 0,'".mysqli_escape_string($GLOBALS['con'], $notes)."')");
  $exid = mysqli_insert_id($GLOBALS['con']);

  $notes = strip_tags($notes);
  do_mysqli_query("INSERT INTO multi_exceptions_players (eid,player,time,valid) VALUES (".$exid.",".$_SESSION['player']->getID().",".time().",0)");
  foreach ($ids as $id) {
    do_mysqli_query("INSERT INTO multi_exceptions_players (eid,player,time,valid) VALUES (".$exid.",".$id.",".time().",0)");
  }
}


function exception_exists($id) {
  $id = intval($id);
  $res = do_mysqli_query("SELECT id FROM multi_exceptions WHERE id = ".$id);
  if (mysqli_num_rows($res)) 
    return 1;
  else
    show_error("Keine Exception unter der id '".$id."' vorhanden.");

  return NULL;
}


function add_comment($id, $comment) {
  if (exception_exists($id)) {
    $comment = strip_tags($comment);
    do_mysqli_query("UPDATE multi_exceptions SET comment='".mysqli_escape_string($GLOBALS['con'], $comment)."' WHERE id=".intval($id) );
  }
}


function player_exception_exists($id) {
  $id = intval($id);

  $ex_res = do_mysqli_query("SELECT id FROM multi_exceptions_players WHERE id=".$id);
  if (mysqli_num_rows($ex_res))
    return 1;
  else
    show_error("Keine Spielerexception unter der id ".$id);
  return NULL;
}


function add_player_comment($id, $comment) {
  if (player_exception_exists($id, $pid, $time)) {
      $comment = strip_tags($comment);
      do_mysqli_query("UPDATE multi_exceptions_player SET comment='".mysqli_escape_string($GLOBALS['con'], $comment)."' WHERE id=".$id);
  }
}


function validate_exception($id, $comment="Kein Kommentar angegeben") {
  global $player;
  if (exception_exists($id)) {
    if( $player->isAdmin() ) {
      $comment = strip_tags($comment);
      do_mysqli_query("UPDATE multi_exceptions SET valid=1,mh=".$_SESSION['player']->getID().",validatetime=".time()." WHERE id=".$id);
    }
    else {
      show_fatal_error("Ihr seid kein Spielleiter, daher dazu nicht berechtigt!");
    }
  }
}


function validate_player_exception($id) {
  global $player;

  if (player_exception_exists($id)) {
    if($player->isAdmin()) {
      $comment = strip_tags($comment);
      do_mysqli_query("UPDATE multi_exceptions_players SET valid=1,comment='".mysqli_escape_string($GLOBALS['con'], $comment)."',mh=".$_SESSION['player']->getID().",validatetime=".time()." WHERE id=".$id);
    }
    else {
      show_fatal_error("Ihr seid kein Spielleiter, daher dazu nicht berechtigt!");
    }
  }
}


function show_own_exceptions() {
  $ex_res = do_mysqli_query("SELECT multi_exceptions.id,multi_exceptions.valid FROM multi_exceptions,multi_exceptions_players WHERE multi_exceptions.id=multi_exceptions_players.eid AND multi_exceptions_players.player=".$_SESSION['player']->getID());
  if (!mysqli_num_rows($ex_res))
    return NULL;
  while ($ex = mysqli_fetch_assoc($ex_res)) {
    $p_res = do_mysqli_query("SELECT player.name,multi_exceptions_players.valid FROM player,multi_exceptions_players WHERE player.id=multi_exceptions_players.player AND multi_exceptions_players.eid=".$ex['id']);
    echo "<table><tr><td><b>Exception ".$ex['id']."</b>";
    if (!$ex['valid']) {
      echo " &nbsp;&nbsp;<i><font color='red'>nicht freigeschaltet</font></i>\n";
    }
    else {
      echo " &nbsp;&nbsp;<i><font color='green'>genehmigt</font></i>\n";
    }
    echo "<br>\n".$ex['comment']."\n";
    
    echo "</td></tr>";
    while ($p = mysqli_fetch_assoc($p_res)) {
      echo "<tr><td>&nbsp;&nbsp;".$p['name'];
      if (!$p['valid']) {
        echo " &nbsp;&nbsp;<i><font color='red'>nicht freigeschaltet</font></i>\n";
      }
      else {
        echo " &nbsp;&nbsp;<i><font color='green'>genehmigt</font></i>\n";
      }
      echo "</td></tr>\n";
    }
  }
  echo "</table>\n";
}


//file ist die aufrufende Datei, clean
function show_mh_exceptions($file, $id=null) {
  $ex_res = do_mysqli_query("SELECT DISTINCT multi_exceptions.id,multi_exceptions.valid,multi_exceptions.comment FROM multi_exceptions, multi_exceptions_players WHERE multi_exceptions.id=multi_exceptions_players.eid".($id ? " AND multi_exceptions_players.player=$id": "") );
  if (!mysqli_num_rows($ex_res))
    return NULL;
  while ($ex = mysqli_fetch_assoc($ex_res)) {
    $p_res = do_mysqli_query("SELECT player.name,multi_exceptions_players.valid,multi_exceptions_players.comment,multi_exceptions_players.note,multi_exceptions_players.id FROM player,multi_exceptions_players WHERE player.id=multi_exceptions_players.player AND multi_exceptions_players.eid=".$ex['id']);
    echo "<table><tr><td>Exception ".$ex['id'];
    if ($ex['comment']) {
      echo " (".$ex['comment'].")";
    }
    else {
      echo " (kein Kommentar angegeben)";
    }
    if (!$ex['valid']) {
      echo " <b> (<a href='".$file."?evalidate=".$ex['id']."'>zustimmen/bestätigen</a>)</b>";
    }
    else {
      echo " &nbsp;&nbsp;<b><font color='green'>genehmigt</font></b>";
    }
    echo "</td></tr>";
    while ($p = mysqli_fetch_assoc($p_res)) {
      echo "<tr><td>&nbsp;&nbsp;".$p['name'];
      if (!$p['valid']) {
	      echo " <b> (<a href='".$file."?pvalidate=".$p['id']."'>zustimmen/bestätigen</a>)</b>";
        }
      else {
        echo " &nbsp;&nbsp;<b><font color='green'>genehmigt</font></b>";
      }
      echo "</td></tr>";
    }
  }
  echo "</table>";
}


function adminprintf() {
  
}

/******** Multi-Trap *********
 * Das MultiCookie wird auf dem Client gespeichert.
 * - Name des Cookies ist "hw2_localdata"
 * 
 * 
 * 
 * @param $p  Klasse Player des zu ködernden Multis 
 */
define("MULTI_TRAP_COOKIE_NAME", "hw2_localdata");
define("MULTI_TRAP_COOKIE_EXPIRE", 28*24*60*60);

function multi_trap($p) {
  $mh_url = URL_BASE."/multihunter.php?showid=";

  // Wird ein Cookie übermittelt?
  if(isset($_COOKIE[MULTI_TRAP_COOKIE_NAME])) {
    $code = $_COOKIE[MULTI_TRAP_COOKIE_NAME];
    
    $res = do_mysqli_query("SELECT multi_trap.*,name FROM multi_trap ".
                          " LEFT JOIN player ON player.id = multi_trap.player ".
                          " WHERE (expired IS NULL OR expired > UNIX_TIMESTAMP()) ".
                          "  AND code = '".mysqli_escape_string($GLOBALS['con'], $code)."'");
    
    // Es kann nur einen Eintrag geben (weil code UNIQUE ist)
    if(mysqli_num_rows($res) == 1) {
      $dbcookie = mysqli_fetch_assoc($res);
      // Cookie gibts zumindest mal...

      // Hochzählen
      do_mysqli_query("UPDATE multi_trap SET used = used + 1 WHERE mid = ".$dbcookie['mid']);
      
      if($dbcookie['player'] == $p->getID()) {
        // Cookie gehört dem Spieler, also nur upcount
      }
      else {
        // oO ... hier ist ein Multi entlarvt.

        // Multi-Login loggen!
        multi_trap_log($p->getID(), $dbcookie['player'], $code);
                
        // Message an MultiHunter, aber nur bei den ersten beiden Malen und jedes 10te Mal.
        // sonst wird man überschüttet
        if($dbcookie['used'] < 2 || $dbcookie['used'] % 10 == 0) {
          $body = sprintf("Der Spieler [url=%s%d]%s (%d)[/url] wurde als Multi von Spieler [url=%s%d]%s (%d)[/url] entlarvt. Er ist zum %dten mal in die Cookie-Falle getappt.\nDer Multicode war: [ %s ].",
                          $mh_url, $p->getID(), $p->getName(), $p->getID(), $mh_url, $dbcookie['player'], $dbcookie['name'], $dbcookie['player'], $dbcookie['used'], $code);
          $topic = sprintf("Multi entlarvt: %s -> %s", $p->getName(), $dbcookie['name']);
          message_to_multihunter($topic, $body);
        }
      }
      
      multi_trap_set_cookie($code);
    }
    else {
      // Cookie gibts nicht... komisch, hat der Spieler manipuliert?
      multi_trap_gen_new($p);
      
      $body = sprintf("Ungültiges MultiCookie '%s' des Spielers [url=%s%d]%s (%d)[/url]. ".
                      "Dieses Cookie wurde nie ausgeliefert.",
                      $code, $mh_url, $p->getID(), $p->getName(), $p->getID(), $count['cnt']);

      $topic = sprintf("Ungültiges MultiCookie: %s", $p->getName());
      message_to_multihunter($topic, $body);      
    }
  }
  else {
    // Cookie gibts (noch) nicht oder wurde vom Spieler gelöscht

    // Cookies zählen, die der Spieler bereits hat
    $sql = sprintf("SELECT count(*) AS cnt FROM multi_trap WHERE player = %d", $p->getID());
    $count = do_mysqli_query_fetch_assoc($sql);
    
    // Neues Multicookie setzen
    multi_trap_gen_new($p);
    

    // Ein leeres Cookie ist erlaubt. Danach wird geloggt
    if($count > 1)
      {
        multi_trap_log_nocookie($p->getID(), $count['cnt'] );    
      }


    
    $inform_mh = true; // An Multihunter melden?
    $tolerance = 5;    // Ab 5 ungültigen Logins loggen, 
    $interval  = 20;   // alle 20te Male loggen
    if($count['cnt'] >= $tolerance && $count['cnt']%$interval == $tolerance && $inform_mh) {      
      $body = sprintf("Der Spieler [url=%s%d]%s (%d)[/url] hat beim Login kein MultiCookie ".
                      "geliefert, obwohl er %d MultiCookies in der Datenbank hat.".
                      " Hat er das gelöscht? Skandal!",
                      $mh_url, $p->getID(), $p->getName(), $p->getID(), $count['cnt']);

      $topic = sprintf("Kein MultiCookie %dx: %s", $count['cnt'], $p->getName());
      message_to_multihunter($topic, $body);      
    }

  }
}

function multi_trap_gen_new($p) {
  $code = md5( ($p->getName()).($p->getID()).(time()) );

  $sql = sprintf("INSERT INTO multi_trap (code, player, createtime) VALUES ('%s', %d, UNIX_TIMESTAMP())",
                   mysqli_escape_string($GLOBALS['con'], $code), $p->getID());
  do_mysqli_query($sql);
  multi_trap_set_cookie($code);
}


function multi_trap_set_cookie($code) {
  setcookie(MULTI_TRAP_COOKIE_NAME, $code, time() + MULTI_TRAP_COOKIE_EXPIRE);
  $log = sprintf("setcookie(%s, %s, %s)", MULTI_TRAP_COOKIE_NAME, $code, time() + MULTI_TRAP_COOKIE_EXPIRE);
  //log_fatal_error($log);
}


/**
 * Eine Nachricht an alle Multihunter senden.
 */
function message_to_multihunter($header, $body) {
  $res = do_mysqli_query("SELECT * FROM player WHERE hwstatus & 2");
  while($to = mysqli_fetch_array($res)) {
    $sql = sprintf("INSERT INTO message (sender, recipient, header, body, date, category) VALUES ('MULTITRAP', %d, '%s', '%s', UNIX_TIMESTAMP(), 9)",
                   $to['id'], mysqli_escape_string($GLOBALS['con'], $header), mysqli_escape_string($GLOBALS['con'], $body)); 
    do_mysqli_query($sql);
    do_mysqli_query("UPDATE player SET cc_messages = 1 WHERE id = ".$to['id']);
  }
}


/**
 * Loggen, wenn zwei Spieler dasslebe Cookie benutzt haben.
 */
function multi_trap_log($multi, $cookieowner, $code = "") {
  $sql = sprintf("INSERT INTO multi_trap_caught (multi, cookieowner, time, code) VALUES (%d, %d, UNIX_TIMESTAMP(), '%s')",
                 $multi, $cookieowner, mysqli_escape_string($GLOBALS['con'], $code));
  do_mysqli_query($sql);
}


/**
 * Loggen, wenn ein Spieler ein Cookie haben müsste,  aber keines hat. 
 * Eventuell löscht sein Browser nach einem Neustart alle Cookies,
 * aber trotzdem loggen.
 */
function multi_trap_log_nocookie($player, $count) {
  $sql = sprintf("INSERT INTO multi_trap_nocookie (player, count, time) VALUES (%d, %d, UNIX_TIMESTAMP())", $player, $count);
  do_mysqli_query($sql);
}


function send_multi_warn() {

  $topic = "Verwarnung";
$warn = "
Hallo,

hiermit erhältst Du eine Verwarnung. Resourcen-Transfers zwischen Spieler, die über die gleiche IP / gleiche Internetleitung oder gar über denselben PC einloggen, dürfen keinerlei Resourcen untereinander tauschen.

Lies dazu die Informationen unter MyHW2->Account ganz unten unter Multi-Exception durch.

Sollten erneut Resourcen ausgetauscht werden, dann kommt es zu einer Sperre Deines und der anderen betroffenen Accounts.

Gezeichnet
Ein Multihunter
";

}
ob_end_flush();

?>