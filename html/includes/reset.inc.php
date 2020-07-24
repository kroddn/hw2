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
* Copyright (c) 2006 by holy-wars2.de
*
* written by
* Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/util.inc.php");
include_once("includes/admin.inc.php");
include_once("admintools/import.config.php");

if(!isset($_SESSION['player']) || !$_SESSION['player']->isAdmin()) {
  echo "<h1 class=\"error\">Unbefugt</h1>";
  die("</body></html>");
}

function inform_players() {
  return;
}

/**
 * Durchführung eines Resets
 *
 * Das Skript sollte alle notwendigen Schritte durchführen,
 * damit eine Runde neu beginnt. 
 *
 * Existierende Spieler bleiben erhalten.
 */
function reset_players($delete = false) {
  // Premium-Accs sichern
  do_mysql_query("TRUNCATE premiumacc_old");
  do_mysql_query("INSERT INTO premiumacc_old ".
                 " SELECT * FROM premiumacc WHERE player >= 10");
  
  
  if($delete)
  {
    do_mysql_query("DELETE FROM player WHERE id >= 10");
    do_mysql_query("DELETE FROM premiumacc WHERE player >= 10");
    do_mysql_query("DELETE FROM sms_send WHERE sender >= 10");
    do_mysql_query("DELETE FROM sms_settings WHERE player >= 10");
    do_mysql_query("ALTER TABLE player auto_increment = 10");
  }



  // Ressourcen der "Admins" zurücksetzen
  do_mysql_query("UPDATE player SET ".
		 " lastres=unix_timestamp(),gold=" . START_GOLD . ",wood=" . START_WOOD . ",stone=" . START_STONE . ",iron=0,rp=" . START_RP . ",".
		 " pos=NULL, religion=NULL, name=NULL, activationtime=NULL, signature=NULL, ".
		 " points=0, pointsavg=0, pointsupd=0,".
		 " cc_towns=1, cc_resources=1, cc_messages=1, nooblevel=5,".
		 " clan=NULL, clanstatus=0, clanapplication=NULL, avatar=0,".
		 " lastclickbonuspoints=0,".
		 " signature = NULL, holiday = 0, toplist = NULL");

  do_mysql_query("TRUNCATE researching");
  do_mysql_query("TRUNCATE req_relation");
  do_mysql_query("TRUNCATE relation");
  do_mysql_query("TRUNCATE playerresearch");
  do_mysql_query("TRUNCATE market");
  do_mysql_query("TRUNCATE player_monument");
  do_mysql_query("TRUNCATE player_online");
  do_mysql_query("DELETE FROM addressbook WHERE player IS NOT NULL");
  do_mysql_query("TRUNCATE message");
  do_mysql_query("TRUNCATE multi_exceptions");
  do_mysql_query("TRUNCATE multi_exceptions_players");
  do_mysql_query("TRUNCATE namechange");
  do_mysql_query("TRUNCATE player_monument");
  do_mysql_query("TRUNCATE player_online");

  // Multi-Cookies expired setzen, damit beim nächsten Login neue 
  // generiert werden.
  do_mysql_query("UPDATE multi_trap SET expired = UNIX_TIMESTAMP()");

  do_mysql_query("TRUNCATE zitate");
  
  // Avatare löschen
  reset_avatar_dir();
  
  // Gesperrte Spieler löschen
  $res = do_mysql_query("SELECT id,login FROM player WHERE status = 2");
  while($p = do_mysql_fetch_assoc($res)) {
    echo "Lösche Spieler '".$p['login']."' <br>\n";
    RemovePlayer_old($p['id']);
  }

  return null;
}


/**
 * Städte resetten.
 */
function reset_cities() {
  do_mysql_query("TRUNCATE cityunit");
  do_mysql_query("TRUNCATE cityunit_ordered");
  do_mysql_query("TRUNCATE citybuilding");
  do_mysql_query("TRUNCATE citybuilding_ordered");
  do_mysql_query("TRUNCATE city");
  do_mysql_query("TRUNCATE armyunit");
  do_mysql_query("TRUNCATE army");

}

/**
 * Clans löschen
 */ 
function reset_clans() {
  do_mysql_query("TRUNCATE clan");
  do_mysql_query("TRUNCATE clanrel");
  do_mysql_query("TRUNCATE req_clanrel"); 
  do_mysql_query("TRUNCATE clanlog");
  do_mysql_query("TRUNCATE clanlog_admin");
}


function reset_rpg() {
  do_mysql_query("TRUNCATE tournament");
  do_mysql_query("TRUNCATE tournament_players");
  do_mysql_query("TRUNCATE rpg");
  
  // Neue Turniere einfügen
  for($i = 0; $i < 4; $i++) {
    $current_day = date("Y-m-d", time() + $i*24*3600);
    $sql = gen_tournament_sql_code($current_day);
    $arr = explode(";", $sql);
    foreach($arr AS $s) {
      do_mysql_query($s);
    }
  }
}


function reset_config()
{
  do_mysql_query("DELETE FROM config WHERE name IN ('settleradius')");

  // Set settleradius
  if(defined("HISPEED") && HISPEED)  
      $reset_radius = 5;
  else if(defined("SPEED") && SPEED)   
    $reset_radius = 2;
  else             
    $reset_radius = 3;
  
  
  do_mysql_query("INSERT INTO config (name,value,creationtime,updatetime)".
                 " VALUES ('settleradius', '".$reset_radius."', UNIX_TIMESTAMP(), UNIX_TIMESTAMP() )");


  // starttime nur löschen, wenns kleiner wie aktuell ist
  $start_time = getConfig("starttime");
  if($start_time < time()) {
    do_mysql_query("DELETE FROM config WHERE name = 'starttime'");
    do_mysql_query("INSERT INTO config (name,value,creationtime,updatetime)".
                   " VALUES ('starttime', (UNIX_TIMESTAMP()+120), UNIX_TIMESTAMP(), UNIX_TIMESTAMP() )");
  }
  
  // Nochmal starttime aus der DB lesen
  $start_time = getConfig("starttime");

  // Endzeitpunkt löschen, falls einer gesetzt war
  if(0) {
    do_mysql_query("DELETE FROM config WHERE name = 'endtime'");

    // Bei der HiSpeed automatisch das Ende der Runde auf start + 18 Std. setzen
    if( defined("HISPEED") && HISPEED) {
      $end_time =  $start_time + 18*3600;
      do_mysql_query("INSERT INTO config (name,value,creationtime,updatetime)".
                   " VALUES ('starttime', '".$end_time."', UNIX_TIMESTAMP(), UNIX_TIMESTAMP() )");
    }
  }
}

function create_map($map_name, $map_size) {
  set_time_limit(60);
  echo "<p>Erstelle neue Map</p>";
  require_once("includes/image.func.php");
  makeMapImg("maps/".$map_name, $map_size, $map_size);
  //define("NEW_MAP", $map_name);
  flush();
}



function reset_map() {
  set_time_limit(60);
  echo "<p>FillMapWithRes";
  include("admintools/fillmapwithres.php");
  flush();
  set_time_limit(60);
  echo "<p>Import";
  include("admintools/import.php");
  flush();
  set_time_limit(30);
  echo "<p>StartPos";
  include("admintools/startpos.php");


  set_time_limit(30);
  echo "<p>CorrectPics";
  include("admintools/correct_pics.php");
  flush();
}

function reset_logs() {
  do_mysql_query("TRUNCATE log_army");
  do_mysql_query("TRUNCATE log_armyunit");
  do_mysql_query("TRUNCATE log_bofh_player");
  do_mysql_query("TRUNCATE log_browser");
  do_mysql_query("TRUNCATE log_cputime");
  do_mysql_query("TRUNCATE log_delete");
  do_mysql_query("TRUNCATE log_err");
  do_mysql_query("TRUNCATE log_lock");
  do_mysql_query("TRUNCATE log_login");
  do_mysql_query("TRUNCATE log_market_accept");
  do_mysql_query("TRUNCATE log_market_send");
  do_mysql_query("TRUNCATE log_marketmod");
  do_mysql_query("TRUNCATE log_mysqlerr");
  do_mysql_query("TRUNCATE log_player_deleted");
  do_mysql_query("TRUNCATE log_reactivate");
  do_mysql_query("TRUNCATE log_password_send");
  do_mysql_query("TRUNCATE log_mysqlerr");
  do_mysql_query("TRUNCATE log_err");
  do_mysql_query("TRUNCATE log_convert");


  $dir = opendir("../log");
  if($dir) {
    while(false !== ($f = readdir($dir)) ) {
      $d = "../log/".$f;
      if(is_file($d)) {
        echo "Lösche $d<br>\n";
        unlink($d);
      }
      else
      echo "Ingoriere $d<br>\n";
    }
    closedir($dir);
  }
  else {
    echo "Konnte ../log nicht öffnen";
    return;
  }
  

}

function reset_avatar_dir() {
  if(!defined("AVATAR_DIR")) {
    echo "AVATAR_DIR nicht definiert";
    return;
  }
  if(!is_dir(AVATAR_DIR)) {
    echo AVATAR_DIR." existiert nicht";
    return;
  }


  $dir = opendir(AVATAR_DIR);
  if($dir) {
    echo "Bereinige ".AVATAR_DIR."<br>\n";
    while(false !== ($f = readdir($dir)) ) {
      $d = AVATAR_DIR."/".$f;
      if(is_file($d)) {
	echo "Lösche $d<br>\n";
	unlink($d);
      }
      else
	echo "Ingoriere $d<br>\n";
    }
    closedir($dir);
  }
  else {
    echo "Konnte ".AVATAR_DIR." nicht öffnen";
    return;
  }
}


function do_reset($magic, $reset_map = true) {
  if(strlen($magic)<1 || $magic != $_SESSION['reset_magic']) {
    $_SESSION['reset_magic'] = createKey();
    echo "<h1 class=\"error\">Magic ungültig. Zum Reset bitte das gültige Magic eingeben</h1>";
    return;
  }

  echo '<div style="width: 500px; padding: 5px; background-color: yellow;">';
  

  echo "Setze Städte zurück<br>\n";
  reset_cities();

  echo "Informiere Spieler über Reset<br>\n";
  inform_players();

  echo "Setze Spieler zurück<br>\n";
  reset_players();
  
  echo "Setze Clans zurück<br>\n";
  reset_clans();
  
  echo "Logs resetten<br>\n";
  reset_logs();

  echo "Resette RPG<br>\n";
  reset_rpg();

  echo "Clanforum resetten<br>\n";
  reset_clanforum_now();


  echo "Resette Konfiguration<br>\n";
  reset_config();


  if($reset_map) {

    if ($_GET["map_size"] != "" && $_GET["map_name"] != "") {
      echo "Map erstellen<br>\n";
      create_map($_GET["map_name"], $_GET["map_size"]);
    }

    echo "Map generieren<br>\n";

    $error = reset_map();
    if($error != null) 
      echo '<font color="red">'.$error."</font>";
  }
  else {
    echo "KEINE Map<br>\n";
  }

    ?>
<p>
<h1>Noch zu erledigen:</h1>
<ul>
<!-- <li>BOOKING_ALLOWED in <i>includes/db.config.php</i> deaktivieren -->
<!-- <li>BOOKING_ROUND_NAME in <i>includes/db.config.php</i> anpassen  -->
<li>pagetitle in <i>includes/config.inc.php</i> anpassen
<li>roundname in SQL-Tabelle <i>config</i> anpassen
<li><b>SERVICE</b> neu starten!
<li>Dann normales Login freigeben (.htaccess löschen)
</ul>
      
<?
    echo "</div>";
} // do_reset



function reset_game_form() {
  $_SESSION['reset_magic'] = createKey();
?>
<hr>
  <h2>Runde <? echo $GLOBALS['pagetitle']; ?> resetten?</h2>
  <div style="width: 500px;">
  <style> .container input { width: 100%; }</style>
  <form action="" method="GET" onSubmit="return confirm('Wirklich <? echo $GLOBALS['pagetitle']; ?> Resetten???');">
    <p>Bitte Bestägtigungskey eingeben</p>
    <p>
      <label for="reset_magic"><? echo $_SESSION['reset_magic']; ?></label>
      <input name="reset_magic"> 
    </p>
    <p>
      <label for="map_name">Dateiname von Map</label>
      <input name="map_name">
    </p>
    <p>
      <label for="map_size">Mapgröße</label>
      <input name="map_size">
    </p>
    <p>
      <label for="res_wood">Wood in Percent</label>
      <input name="res_wood" value="<?php echo defined('PERCENT_WOOD') ? PERCENT_WOOD : "";?>" required>
    </p>
    <p>
      <label for="res_stone">Stone in Percent</label>
      <input name="res_stone" value="<?php echo defined('PERCENT_STONE') ? PERCENT_STONE : "";?>" required>
    </p>
    <p>
      <label for="res_fish">Fisch</label>
      <input name="res_fish" value="<?php echo defined('AMOUNT_SPECIAL_FISH') ? AMOUNT_SPECIAL_FISH : "";?>" required>
    </p>
    <p>
      <label for="res_pearls">Pearls</label>
      <input name="res_pearls" value="<?php echo defined('AMOUNT_SPECIAL_PEARLS') ? AMOUNT_SPECIAL_PEARLS : "";?>" required>
    </p>
    <p>
      <label for="res_wine">Wine</label>
      <input name="res_wine" value="<?php echo defined('AMOUNT_SPECIAL_WINE') ? AMOUNT_SPECIAL_WINE : "";?>" required>
    </p>
    <p>
      <label for="res_wheat">Wheat</label>
      <input name="res_wheat" value="<?php echo defined('AMOUNT_SPECIAL_WHEAT') ? AMOUNT_SPECIAL_WHEAT : "";?>" required>
    </p>
    <p>
      <label for="res_furbs">Furbs</label>
      <input name="res_furbs" value="<?php echo defined('AMOUNT_SPECIAL_FURBS') ? AMOUNT_SPECIAL_FURBS : "";?>" required>
    </p>
    <p>
      <label for="res_herbs">Herbs</label>
      <input name="res_herbs" value="<?php echo defined('AMOUNT_SPECIAL_HERBS') ? AMOUNT_SPECIAL_HERBS : "";?>" required>
    </p>
    <p>
      <label for="res_metal">Metal</label>
      <input name="res_metal" value="<?php echo defined('AMOUNT_SPECIAL_METAL') ? AMOUNT_SPECIAL_METAL : "";?>" required>
    </p>    
    <p>
      <label for="res_gems">Gems</label>
      <input name="res_gems" value="<?php echo defined('AMOUNT_SPECIAL_GEMS') ? AMOUNT_SPECIAL_GEMS : "";?>" required>
    </p>
    <p>
      <input type="submit" name="do_reset" value=" RESET ">
      <input type="checkbox" name="reset_map" value="1" checked="1" type="hidden">
    </p>
  </form>

<?
}
?> 
