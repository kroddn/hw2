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
 * Copyright (c) 2005-2007
 *
 * Markus Sinner
 *
 * This File must not be used without permission
 ***************************************************/

/****
 GRANT SELECT , INSERT , UPDATE ON `global`.`banner` TO 'hw2_2006'@'%';
 GRANT SELECT , INSERT , UPDATE ON `global`.`banner` TO 'hw2_2006_06'@'%';
 GRANT SELECT , INSERT , UPDATE ON `global`.`banner` TO 'hw2_speed'@'%';
 GRANT SELECT , INSERT , UPDATE ON `global`.`banner` TO 'hw2dev'@'%';
 GRANT SELECT , INSERT , UPDATE ON `global`.`banner` TO 'hw2_oldgame1'@'%';
****/

include_once("includes/premium.inc.php");
define("MAGIC_SCRIPT", "index.php");
define("MAGIC_SUBID", "hw2");
define("MAGIC_DEFAULT_IMG", "images/ingame_v3/noads.jpg");

define("DB_TYPE", "mysql");
define("BANNER_TABLE", "global.banner");

include_once("includes/db.class.php");
include_once("/home/wwwroot/strength-and-honor-game.de/htdocs/include/banner2.inc.php");


/**
 * Erklärung:
 * show_banner(0) - Standartbanner
 * show_banner(1) - Skyscraper
 * show_banner(3) - PopUp
 * show_banner(2) - Halfsize Banner
 * show_banner(4) - Standardbanner für Startseite
 **/
function show_banner($bid, $force=false) {
  global $ad;
  
  if(isset($_SESSION['first_login']) && $_SESSION['first_login'] && !$force) {
    return;
  }
  else if(is_premium_noads() && !$force) {
    return;

    return "\n\n<div id='noads' width='478' height='60'><a href='noads.php'><img src='".(isset($imagepath) ? $imagepath."/noads.jpg" : MAGIC_DEFAULT_IMG)."' border='0' alt='Premium Acc, kein Banner'></a></div>\n";
  }
  else {
    $timemod = round(time() / 60) % 8;
    echo "<!-- TIME ".time().", MOD: ".$timemod." -->";
    
    if ( $timemod % 2 == 0) {
      printBanner();
    }
    else {
      echo "<div valign='middle' width='478' height='100'>";
      if ($timemod == 1 || $timemod == 5) {
        include("includes/affilimatch.468.html");
      }
      else {
        // Banner mit TKP Bezahlung
        if ($bid == 4) {
          include("includes/easyad_468_portal.html");
        }
        else {
          include("includes/easyad_468.html");
        }
      }
      echo "</div>";
    }
    if (isset($_SESSION['player'])) {
      if ($_SESSION['player']->isMaintainer()) {
	echo "ID: ".$ad->banner_id.' <a href="?bannerplus=-1">--</a> <a href="?bannerplus=1">++</a> ';
      }
      echo '<a style="color: blue;" href="premium.php?mark=noads#accounts">Werbefrei?</a>';     
      echo '&nbsp;&nbsp;&nbsp;<a href="all.php">Werbepartner anzeigen</a> / <a href="all.php">Partner werden</a>';
      echo '&nbsp;&nbsp;&nbsp;<a style="color: red;" href="premium.php">HW2 Premium-Account</a>';
    }

    echo "<br>\n";
  }
}


/**
 * Eigene Advertising Funktion. Damit keine Banner-Blocker aktiv werden
 * können wird diese Methode von der index.php aufgerufen zusammen mit
 * einem parameter $magic. Ist $magic auf den zufälligen Wert für ADS
 * gesetzt, dann wird eine Werbeseite generiert, ansonsten wird ganz
 * normal die index.php weiterverarbeitet.
 */ 
function show_ads_page($magic) {
?>
<html><body style="margin:0px;  font-size: 11px; font-family: Arial,sans-serif;">
<div style="width: 474; height:56; border: 2px solid white; ">
<b>Sehen Sie an dieser Stelle normalerweise ein Werbe-Banner?</b><br>
&nbsp;Wenn <b>ja</b>, dann ist das gut für Holy-Wars.<br>
&nbsp;Wenn <b>nein</b>, dann boykottieren Sie (vielleicht unabsichtlich) die Weiterentwicklung von  Holy-Wars.<br>
&nbsp;Für weiter Infos <a target="main" href="bannerblock.php">hier klicken</a>.
</div>
</body></html>
<?
} // function show_ads_page($magic)
?>