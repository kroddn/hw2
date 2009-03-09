<?
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

//include("include/session.php");
//page_begin("Banner");

include_once("includes/db.inc.php");
include_once("include/db.class.php");
include_once("include/banner.inc.php");
include_once("include/player.class.php");
?>

<html>
<head>
<title>HW2 - Bannerpartner</title>
<script src="js/functions_v45.js" type="text/javascript"></script>
</head>

<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body class="mainbody" background="<? echo $imagepath; ?>/bg.gif">
<h1>Holy Wars 2 Werbepartner (Banner)</h1>
<b>Sie möchten auch auf unseren Seiten für Ihre Produkte oder
Dienstleistungen werben?</b><p>
Wir binden Ihre Werbemittel direkt ein,
ohne umständliche Ad-Server oder Banner-Provider. 
Ausserdem führen wir für Ihre Werbemittel eine View und Clickstatistik.
<p>
Schreiben Sie uns an! Wir unterbreiten Ihnen gerne ein individuelles Angebot.<br>
Kontaktdaten erfahren Sie im <a href="impressum.php" target="_blank">Impressum</a>.
<p>
<h1>Aktuelle Werbepartner</h1>
Hinweis: das Klicken auf diese Banner über diese Seite ist <b>deaktiviert</b>, 
um das Einhalten der allgemeinen Geschäftsbedigungen unserer Werbepartnern 
zu gewährleisten.<p>

<table><tr valign="top"><td valign="top">
<hr>
<? include("ads/openinventory_486x60.php"); ?>
<hr>
<?
$db = new DB(DB_TYPE);
session_start();

if(!isset($_SESSION['player']) || !$player->isMaintainer()) {
  if($db->get_db_type() == "mysql")
    $expire = "WHERE (expiretime IS NULL OR expiretime > UNIX_TIMESTAMP())";
  else {
    $expire = "WHERE (expiretime IS NULL OR expiretime > now() )";
  }
}
else {
  $expire = "";
}

$sql = "SELECT * FROM ".BANNER_TABLE." $expire ORDER BY banner_id";
echo "<!-- $sql -->\n";
$b = $db->query($sql);
while($banner = $db->fetch_object($b)) {
  echo "<h3>[".$banner->banner_id."]".$banner->bannername.
    (isset($_SESSION['player']) && $player->isMaintainer() ? " - ".$banner->showcount."/".$banner->clickcount.", exp: ".$banner->expiretime  : "").
    "</h3>\n";

  if ($banner->expiretime != null && $banner->expiretime < time()) {
    echo "<b>Banner expired</b><p>\n";
  }
  else {
    printBannerNoRef($banner->banner_id);
    echo "<br>\n";
  }
  
  if ($banner->expiretime == null || $banner->expiretime >= time()) {
    echo '<a href="'.MAGIC_SCRIPT.'?magic='.md5(rand()).'&bannerpage='.$banner->banner_id.'">-Nur dieses Banner-</a>&nbsp;&nbsp;';
  }

  if (isset($_SESSION['player']) && $player->isMaintainer()) {
    echo '<a href="banner/'.$banner->localfile.'">-Lokale Datei-</a>&nbsp;&nbsp;';
    echo '<a href="'.$banner->remotefile.'">-Original Datei-</a>';
  }    

  echo "<hr>\n\n\n\n";
} // while
?>
<hr>
<script type="text/javascript" src="http://www.sponsorads.de/script.php?s=6865"></script>
</td>
<td valign="top">
<? include("ads/openinventory_120x600.php");?>
<p>
<? include("ads/sponsorads-skyscraper.php");?>
<p>
<? include("ads/ebay_160x600.php");?>

<p>
</td></tr>
<tr><td colspan="2">
<hr>
<? include("ads/ebay_728x90.php");?>
<hr>
<? include("ads/openinventory_728x90.php");?>
<hr>
</td></tr>
</table>
</body></html>

