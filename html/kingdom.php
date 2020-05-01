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
 * Copyright (c) 2005-2006
 *
 * Gordon Meiser, Markus Sinner
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/db.inc.php");
require_once("includes/cities.class.php");
require_once("includes/research.class.php");
include_once("includes/session.inc.php");
include_once("includes/statistic.inc.php");

$player = $_SESSION['player'];

$_SESSION['player']->setActivePage(basename(__FILE__));
start_page();
start_body();

$tick = getConfig("tick");

echo "<!-- TICK: $tick; -->";

$upt=time();
$res5=do_mysqli_query("SELECT lastres FROM player WHERE id = ".$_SESSION['player']->getID());
while ($data5=mysqli_fetch_assoc($res5)) {
  $next = ($data5['lastres']+$tick)-$upt;
  $ticker = "<b>Verbleibende Zeit bis zum n&auml;chsten Tick: ".$next."</b>&nbsp;<span class=\"noerror\" id=\"1\"><script type=\"text/javascript\">addTimer(".$next.",1);</script></span>&nbsp;";
}

// Zuletzt gewählte Ansicht in Session speichern
if(isset($show)) {
  $_SESSION['kingdom_lastshow'] = $show;
}
else {
  if(isset($_SESSION['kingdom_lastshow'])) {
    $show = $_SESSION['kingdom_lastshow'];
  }
  else {
    $show = "city";
  }
}
?>

<table cellspacing = "1" cellpadding="0" border="0">
  <tr height="20" class="tblhead">
    <td <? printActive("city"); ?>><a href="<?php echo $_SERVER['PHP_SELF']."?show=city"; ?>">Allgemeines</a></td>
    <td <? printActive("ressources"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=ressources"; ?>">Ressourcen</a></td>
    <td <? printActive("weapons"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=weapons"; ?>">Rüstung/Militär  </a></td>
    <td <? printActive("gold"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=gold"; ?>">Goldbilanz</a></td>
    <td <? printActive("build"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=build"; ?>">Gebäudebau</a></td>
    <td <? printActive("troops"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=troops"; ?>">Truppenausbildung</a></td>
    <td <? printActive("wert"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=wert"; ?>">Buchwert</a></td>
    <td <? printActive("troopoverview"); ?>class="tblhead"><a href="<?php echo $_SERVER['PHP_SELF']."?show=troopoverview"; ?>">Truppenübersicht</a></td>
  </tr>
</table>

<div id="stats">
<?php
switch($show) {
  case "ressources":
    stat_res($player->getID());
    echo "<p>";
    stat_terrain($player->getID());
    break;

  case "city":
    stat_cities($player->getID());
    break;

  case "weapons":
    stat_weapons($player->getID());
    break;
  
  case "gold":
    stat_gold($player->getID());
    break;

  case "build":
    stat_building_ordered($player->getID(),$sort);
    break;

  case "troops":
    stat_troop_ordered($player->getID(),$sort);
    break;

  case "wert":
    stat_wert($player->getID());
    break;

  case "troopoverview":
    stat_troopoverview($player->getID());
    break;
    
  default:
    echo "Fehler.";
} // switch
?>
</div>
 
<table width="500"><tr><td align="center">
<? if (!is_premium_noads() and false) { ?>
<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<A STYLE="color: blue;" HREF="http://partners.webmasterplan.com/click.asp?ref=249139&site=3237&type=text&tnb=2" TARGET="_new">Flirt-Fever!
Kostenlos anmelden und coole Leute mit Bild kennenlernen!<br></a><A HREF="http://partners.webmasterplan.com/click.asp?ref=249139&site=3237&type=text&tnb=2" TARGET="_new">Klick hier!<br></a><IMG SRC="http://banners.webmasterplan.com/view.asp?site=3237&ref=249139&b=0&type=text&tnb=2" BORDER="0" WIDTH="1" HEIGHT="1">
<!-- END PARTNER PROGRAM -->
			      <? } ?>
</td></tr></table>


<? end_page(); ?>
