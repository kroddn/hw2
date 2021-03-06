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
* Copyright (c) 2003-2007
*
* Markus Sinner
* Gordon Meiser
*
***************************************************/
$GLOBALS['session.inc.php'] = true;

include_once("register.globals.inc.php");
require_once("includes/config.inc.php");
require_once("includes/db.inc.php");
require_once("includes/premium.inc.php");
include_once("includes/cities.class.php");
include_once("includes/research.class.php");
include_once("includes/player.class.php");
require_once("includes/db.class.php");
require_once("includes/banner.inc.php");
include_once("includes/page.func.php");

session_set_cookie_params (0); // Cookie nach Beenden des Browsers löschen
session_start();

if (isPlayerSet()) {
  $player = $_SESSION['player'];
}

$click = new stdClass();
$_SESSION['click'] = $click;
// Bannerzähler hochsetzen
if (isset($bannerplus)) {
  $ad->banner_id+=$bannerplus;
}

// Sponsorenseite anzeigen
if (isset($bannerpage)) {
  include("sponsor.php");
  die();
}


// Flag auswerten, ob nur ein Banner angezeigt werden soll
if ( isset($banner) || isset($magic) && $ad->magic == $magic) {
  if (isset($banner)) {
    outputBannerImage($banner);
  }
  else {
    outputBannerImage();
  }
  die();
}


// FIXME: den kram dorthin, wo er gebraucht wird! Nicht jedesmal machen...
// status = 3 bedeutet verdächtige Spieler, die aber nicht gesperrt sind.
$res = do_mysql_query("SELECT count(*),religion FROM player WHERE religion IS NOT NULL AND activationkey IS NULL AND (status IS NULL OR status=3) GROUP BY religion ORDER BY religion");

// Ein Array mit den Anzahlen zusammenbauen
$registered[0] = mysqli_fetch_array($res);
$registered[1] = mysqli_fetch_array($res);

// Die "Raten" erst ab mehr als 100 Spielern setzen
if ($registered[0][0] + $registered[1][0] > 100) {
  $christratio = ($registered[0][0]) / $registered[1][0];
  $islamratio = 1 / $christratio;
}
else {
  $christratio = $islamratio = 1.0;
}


// Logout ausfuehren
if($logout) {
  logout();
  session_destroy();
  //do_log("User logged out, session destroyed...");  
  $GLOBALS['error'] = "logout";
  goto_login();
}

// Automatisch ausloggen wenn die Sessionzeit überschritten wurde
if(isPlayerSet() && time() > $_SESSION['session_duration'] + $_SESSION['login_time']) {
  logout();
  session_destroy();
  $GLOBALS['error'] = "session_duration_exceeded&limit=".$_SESSION['session_duration']."&duration=".( time() -  $_SESSION['login_time']);
  goto_login();
}


// noclickcount darf nicht vom Browser kommen - in dem Fall
// wird es entfernt.
if(isset($_REQUEST['noclickcount'])) {
  unset($noclickcount);
}


// Clickzählung, ausser bei Skripten mit dem entsprechenden Flag
if (!$noclickcount) {
  $click->count++;
  $click->last = time();    
}

// Bonuspunkte?
if(defined("CLICK_BONUSPOINTS_TIME")) {
  if(isPlayerSet() &&
     time() > $click->last_bonus + CLICK_BONUSPOINTS_TIME )
    {
      $_SESSION['player']->addClickBonuspoints();
      $click->last_bonus = $_SESSION['player']->lastclickbonuspoints;
    }
}


// Inform-Hinweise
if(!$noclickcount && isPlayerSet()) {
  include("includes/inform.func.php");
}


// Alle 89 Clicks einen Hinweis fürs GP anzeigen
if(!$noclickcount && $click->count % 89 == 15 && !is_premium_no_click_hint() &&  ($hwathome != 1 || $player->getGFX_Path() == null) ) {
  if( !isset($_SESSION['first_login']) || !$_SESSION['first_login'] ) {
    include("grapa.php");
    exit();
  }
}

//// Alle 223 Clicks einen Hinweis abbilden
//if(!$noclickcount && $click->count % 523 == 199 && !is_premium_no_click_hint() ) {
//  include("halt.php");
//  exit();
//}
//else // Alle 287 Clicks einen Hinweis zum Werben neuer Spieler einbinden

if(!$noclickcount && $click->count % 687 == 200 && !is_premium_no_click_hint() ) {
  include("recruit.php");
  exit();
}


// Wenn es kein Login ist und $player nicht gesetzt ist, dann gibts wohl keine
// Session. Also zum Login forwarden
// $login = true wird von login.php gesetzt
if(isset($GLOBALS['login']) || isset($GLOBALS['loginprocess']) || isset($GLOBALS['standalone'])) {
  // Einfach weitermachen, nichts tun
  
}
else if(!isset($_SESSION) || !isset($_SESSION['player']) ) {
  if (!isset($_SESSION)) {
    $GLOBALS['error'] = "no_session";
  } else {
    $GLOBALS['error'] = "no_player";
  }
  //$GLOBALS['error'] = "no_session";
  session_destroy();
  goto_login();
}
// Das hier geht klar, wenn der Spieler richtig eingeloggt ist.
else if ( isPlayerSet() ) {
  // Falls Grafikpack installiert ist, die Variablen überschreiben
  if( $player->getGFX_PATH() != null && $hwathome == 1) {    
    $_SESSION['imagepath'] = $player->getGFX_PATH();
  }
  else {
    $_SESSION['imagepath'] = defined("GFX_PATH") ? GFX_PATH : "images/ingame";
  }

  // CSS Path darf nie auf lokale Pfade zeigen
  $_SESSION['csspath']       = (defined("GFX_PATH_LOCAL") ? GFX_PATH_LOCAL : $imagepath ) ."/css";
  $_SESSION['layoutcsspath'] = (strncasecmp($_SESSION['imagepath'], "http://", 7) == 0) ? $_SESSION['imagepath']."/css" : $_SESSION['csspath'];
 
  // Wenn die Runde noch nicht freigegeben wurde...
  if(!check_round_startet() && !$player->isAdmin() ) {
    logout();
    session_destroy();
    //do_log("User logged out, session destroyed...");
    $GLOBALS['error'] = "round_not_yet_started";
    goto_login();
  }
  
  // Wenn die Runde beendet...
  if(check_round_ended() && !$player->isAdmin() ) { // && !$player->isAdmin()
    logout();
    session_destroy();
    //do_log("User logged out, session destroyed...");
    $GLOBALS['error'] = "round_ended";
    goto_login();
  }
  
  // Alle 3 Minuten einen Lastclick eintragen
  if ($player->getlastclick() < (time() - 180)){
    $player->updatelastclick();
  }
}


function isPlayerSet() {
  return isset($_SESSION['player']) && strtolower(get_class($_SESSION['player'])) == "player";
}


/**
 * Aktualisiere das log_login mit dem Zeitpunkt des logouts
 * @return unknown_type
 */
function logout() {
  if ( isPlayerSet() ) {
    $_SESSION['player']->logout();
  }
}


function goto_login() {
  redirect_to();
}

?>
