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
ob_start();
/**
 * Einen Spieler einloggen
 * 
 * @param $loginname     Account login
 * @param $loginpassword Account Passwort im Klartext
 * @param $sec_code      Security Code. Muss mit $_SESSION['sec_key'] übereinstimmen, falls dieser gesetzt ist.
 * @param $nopw          Falls true, wird ohne Passwortvergleich eingeloggt
 * 
 * @return null bei Erfolg, einen String mit einer Fehlermeldung andernfalls
 */
function hw2_login($loginname, $loginpassword, $sec_code, $nopw = false) {
  $GLOBALS['loginprocess'] = true;
   
  // define("DISABLE_MARKET", 1);
  
  include_once("includes/util.inc.php");
  //  include_once("includes/library.class.php");
  if(!defined("DISABLE_MARKET")) {
    include_once("includes/market.class.php");
  }
  include_once("includes/diplomacy.common.php");
  include_once("includes/clan.class.php");
  include_once("includes/diplomacy.class.php");
  include_once("includes/player.class.php");
  include_once("includes/research.class.php");
  include_once("includes/map.class.php");
  include_once("includes/cities.class.php");
  include_once("includes/session.inc.php");
  include_once("includes/db.class.php");
  include_once("includes/banner.inc.php");
  include_once("includes/browser.inc.php");
  include_once("includes/premium.inc.php");
  include_once("includes/sms.func.php");
  
  // Session starten, falls noch nicht geschehen...
  session_start();

  $loginerror = null;
  
  if (checkBez($loginname, 2, 40)) {
    $sql_login = do_mysql_query("SELECT id, login, name, password, status, hwstatus, statusdescription, activationkey, holiday FROM player WHERE login = '".mysqli_escape_string($GLOBALS['con'], $loginname)."'");
    if ($db_login = do_mysql_fetch_assoc($sql_login)) {
      if ($db_login['status'] == "") { $db_login['status'] = 0; }
      $agent  = getenv('HTTP_USER_AGENT');
      do_mysql_query("INSERT INTO log_login(id,name,inputpw,dbpw,status,inputseccode,dbseccode,time,ip,user_agent,sid) ".
                     "VALUES (".$db_login['id'].",'".mysqli_escape_string($GLOBALS['con'], $loginname)."','".md5($loginpassword)."','".$db_login['password']."','".$db_login['status']."','".mysqli_escape_string($GLOBALS['con'], $sec_code)."','".$_SESSION['sec_key']."', UNIX_TIMESTAMP(),'".getenv('REMOTE_ADDR')."', '".mysqli_escape_string($GLOBALS['con'], $agent)."', '".session_id()."')");

      $GLOBALS['premium_flags']  = get_premium_flags ($db_login['id']);
      $GLOBALS['premium_expire'] = get_premium_expire($db_login['id']);
      $GLOBALS['premium_payd']   = get_premium_payd($db_login['id']);
      
      $secure = defined('NO_SECURITY') && NO_SECURITY || isset($_SESSION['sec_key']) && !strcmp($sec_code, $_SESSION['sec_key']) || $GLOBALS['premium_flags'] & PREMIUM_PRO;
      if ($secure) {
        unset($_SESSION['sec_key']);
        if ( $nopw || md5($loginpassword) == $db_login['password']) {
          // Einloggen für aktivierte, nicht aktivierte und verdächtige Accounts zulassen
          if ($db_login['status'] == NULL || $db_login['status'] == 3 || $db_login['status'] == 1) {
            if ($db_login['holiday'] < time()) 
            {
              if(check_round_ended() && $db_login['hwstatus'] != 63)
                return "Die Runde ist beendet!";
                
              if(!check_round_startet() && $db_login['hwstatus'] != 63)
                return "Die Runde hat noch nicht begonnen!";
                
              // Löschmarkierung zurücksetzen
              do_mysql_query("UPDATE player SET markdelete=0 where id=".$db_login['id']);
              
              if ( $db_login['password'] != NULL && $db_login['status'] != 1) {
                do_mysql_query("UPDATE player SET activationkey=NULL where id=".$db_login['id']);
              }
              
              // Prüfen, ob der Account schon 'name' gesetzt hat.
              // falls nicht, dann den Auswahlbildschirm hierfür anzeigen
              if($db_login['name'] == null) {
                // Weiter unten wird die Variable wieder ausgewertet
                $_SESSION['db_login'] = $db_login;
                //$_SESSION['selectname'] = true;
                header_redirect("choose_name.php"); 
                
                exit();
              }
              else {
                // ****************** Login ist Durch! **************************
                
                // Premium Account Speichern
                $_SESSION['premium_flags']  = $GLOBALS['premium_flags'];
                $_SESSION['premium_payd']  = $GLOBALS['premium_payd'];
                $_SESSION['premium_expire'] = $GLOBALS['premium_expire'];

                // Player anlegen
                $player = new Player($db_login['id'], session_id());

                $_SESSION['player']= $player;
                
                // Multi-Falle
                if(!defined("HISPEED") || !HISPEED) {                
                  include_once("includes/multi.inc.php");
                  multi_trap($player);
                }
                
                // Abhängig von Spielereinstellung Map instanziieren
                $map = MapFactory($player);
                $map->centerOnCapital($player->getID());
                $_SESSION['map'] = $map;

                // Diverse Klassen für die Session initialisieren
                $cities = new Cities($player->getID(),$player->getReligion());
                $_SESSION['cities'] = $cities;

                // Library wird ab sofort in library.php initialisiert
                //                $library = new Library($player->getID());
                //                session_register("library");

                if(!defined("DISABLE_MARKET")) {
                	$market = new Market($player->getID());
                	$_SESSION['market'] = $market;
                }
                
                $research = new Research($player->getID(),$player->getReligion());
                $_SESSION['research'] = $research;
                
                $clan = new Clan($player->getID());
                $_SESSION['clan'] = $clan;
                
                $diplomacy = new Diplomacy($player->getID(), $player->getName(), $player->getReligion());
                $_SESSION['diplomacy'] = $diplomacy;
                
                if($GLOBALS['hwathome']==1) {
                  $_SESSION['hwathome'] = 1;                
                }
                do_mysql_query("UPDATE player SET cc_messages=0, lastseen=UNIX_TIMESTAMP() where id=".$db_login['id']);
                $_SESSION['player']->updatelastclick();

                

                // Zeitpunkt des logins
                $login_time = time();
                $_SESSION['login_time'] = $login_time;
                $session_duration = get_premium_session_time();
                $_SESSION['session_duration'] = $session_duration;                

                
                $_SESSION['click']->last       = time()-10;
                $_SESSION['click']->count      = 0;
                $_SESSION['click']->last_bonus = $player->lastclickbonuspoints;
                
                $my_own_db = new DB("mysql");
                $_SESSION['my_own_db'] = $my_own_db;
                
                //$banner = $_SESSION['my_own_db']->query_fetch_array("SELECT banner_id FROM ".BANNER_TABLE." WHERE (expiretime IS NULL OR expiretime > ".time().") ORDER BY ".$my_own_db->random_function_name()." LIMIT 1");
                //$_SESSION['ad']->banner_id = $banner[0];
                //$_SESSION['ad']->magic = md5(rand());
                
                check_sms_settings();
                
                logBrowser();

                if($_SESSION['player']->isAdmin()) {
                  header_redirect("index.php");
                }
                else {
                  header_redirect("index.php");
                }
                
                exit(0);
              } // else of name == null
            }
            else {
              $loginerror = "Ihr befindet Euch im Urlaubsmodus bis ".date("d.m.Y G:i",$db_login['holiday'].".");
            }
          }
          elseif ($db_login['status'] == 1) {$loginerror = "Der Account ist noch nicht aktiviert.";}
          // Gesperrt
          elseif ($db_login['status'] == 2) {$loginerror = $db_login['statusdescription'];}
          else {$loginerror = "Unbekannter Status";}
        }
        else {$loginerror = "Das eingegebene Passwort ist falsch!";
        }
      } // Security
      else {
        if (!isset($_SESSION['sec_key']) || strlen($_SESSION['sec_key']) < 1) {
          $loginerror = "Cookie Fehler: der Security Code konnte nicht überprüft werden.<br> ".
            "Ihr Browser sendet offensichtlich kein gültiges Cookie. überprüfen Sie die Einstellungen und löschen Sie gegebenenfalls Ihre Cookies.";
        }
        else {
          $loginerror = "Security Code falsch oder gar nicht eingegeben! Falls dieser Fehler öfter auftritt ".
            "<a target=\"_new\" href=\"http://forum.holy-wars2.de/viewtopic.php?t=6876\">_HIER_</a> klicken.<br>".
            "Premium-Pro-User können OHNE Security-Code einloggen!";
        }

      }
    }
    else {$loginerror = "Es existiert kein Account mit diesem Login!<br>Habt Ihr vielleicht Spielername und Login verwechselt?";}
  }
  else {
    $loginerror = "Ungültige Zeichen im Login!";
  }
  
  
  return $loginerror;
} // function
ob_end_flush();
?>