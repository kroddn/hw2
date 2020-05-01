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
 * Copyright (c) 2003-2005
 *
 * Stefan Neubert
 * Stefan Hasenstab
 * Gordon Meiser 
 * Markus Sinner <kroddn@psitronic.de>
 *
 * This File must not be used without permission
 ***************************************************/

unset($selectPlayerName);

// $login muss gesetzt sein, damit in session.inc.php kein redirect
// auf die Login-Seite durchgeführt wird
$login = true;

require_once("includes/db.inc.php");
include_once("includes/util.inc.php");
include_once("includes/library.class.php");
include_once("includes/market.class.php");
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
//include_once("includes/browser.inc.php");
include_once("includes/premium.inc.php");
include_once("includes/sms.func.php");

if (defined("GFX_PATH_LOCAL")) {
  $imagepath = GFX_PATH_LOCAL;
}
else {
  $imagepath = "images/ingame";
}
$csspath = $imagepath."/css";

if ( isset($ref) && is_numeric($ref) ) {
  $recruiter = $ref;
}


if (isset($_COOKIE['hw2_recruiter'])) {
  if (is_numeric($_COOKIE['hw2_recruiter'])) {
    $cookieref = true;
    $recruiter = intval($_COOKIE['hw2_recruiter']);
    $ref = $recruiter;
  }
}
else if (isset($recruiter)) {
  // Referer gesetzt. Ein Cookie anlegen. Läuft nach 28 Tagen ab
  $cookieref = false;
  if (is_numeric($recruiter))
    $ref = intval($recruiter);
  else
    unset($recruiter);
}

if (isset($recruiter)) {
  $serverhost = $_SERVER['HTTP_HOST'];
  if (strcasecmp($serverhost, "www.holy-wars2.de") == 0) $serverhost = "holy-wars2.de";

  $recruiter_res = do_mysqli_query("SELECT name FROM player WHERE id = ".mysqli_escape_string($GLOBALS['con'], $recruiter) );
  if (mysqli_num_rows($recruiter_res)) {
    $recruiter_name = mysqli_fetch_assoc($recruiter_res);
    $recruiter_name = $recruiter_name['name'];
    setcookie ("hw2_recruiter", $recruiter, time() + 28*24*60*60, "/", $serverhost, false );   
  }
  else {
    unset($recruiter);
    unset($ref);
    // Cookie löschen
    setcookie ("hw2_recruiter", "", 1, "/", $serverhost, false );
  }
}

if(isset($_POST['loginprocess']) && strlen($_POST['loginname']) > 0 && strlen($_POST['loginpassword']) > 0) {
  $loginprocess = 1;
  $loginname = $_POST['loginname'];
  $loginpassword = $_POST['loginpassword'];
}


if ($loginprocess) {
  if(!defined("ROUND_FINISHED")) {
    include("includes/login.func.php");
    $_SESSION['loginerror'] = hw2_login($loginname, $loginpassword, $sec_code);
  }
  else {
    $_SESSION['loginerror'] = "Diese Runde ist beendet.";
  }
}




// status = 3 bedeutet verdächtige Spieler, die aber nicht gesperrt sind.
$res = do_mysqli_query("SELECT count(*),religion FROM player WHERE religion IS NOT NULL AND activationkey IS NULL AND (status IS NULL OR status=3) GROUP BY religion ORDER BY religion");

if(mysqli_num_rows($res) < 2) {
  $nr[0] = $nr[1] = -1;
}
else {
  $nr[0] = mysqli_fetch_array($res);
  $nr[1] = mysqli_fetch_array($res);
}

if (defined("BOOKING_ALLOWED") && BOOKING_ALLOWED) {
  $book_res = do_mysqli_query("SELECT count(*) FROM booking WHERE status = 0");
  $book = mysqli_fetch_array($book_res);
}


?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $pagetitle; if(isset($title)) echo " - ".$title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="SHORTCUT ICON" href="favicon.ico" type="image/ico">
<script language="JavaScript" src="js/infopopup.js" type="text/javascript"></script>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
</head>

<? 
if (isset($recruiter)) echo '<!-- Recruiter = '.$recruiter.", Server ".$_SERVER['HTTP_HOST']." -->\n"; 
if ($cookieref) echo "<!-- CookieRef true -->\n";
?>
<body style="margin: 0; background-image: url(<? echo $imagepath; ?>/title/bg1.gif);" alink="#FF0000" vlink="#FF0000">
<center>
<p>
<table width="950" style="background-image: url(<? echo $imagepath; ?>/bg.gif);" border="0" cellspacing="5">
	<tr>
		<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td style="background-image: url(<? echo $imagepath; ?>/title/balken.jpg);" colspan="3"><img
					src="<? echo $imagepath; ?>/title/point.gif" width="2" height="10"
					border="0" alt=""></td>
			</tr>

			<tr bgcolor="#000000">
				<td colspan="2">
				<div align="left"><a href="http://portal.holy-wars2.de"><img
					src="<? echo $imagepath; ?>/title/title.jpg" width="554"
					height="80" border="0" alt=""></a></div>
				</td>
				<td>
				<div align="right"><a href="http://portal.holy-wars2.de"><img
					src="<? echo $imagepath; ?>/title/knighthead.jpg" width="186"
					height="80" border="0" alt=""></a></div>
				</td>
			</tr>

			<tr>
				<td style="background-image: url(<? echo $imagepath; ?>/title/balken.jpg);" colspan="3"><img
					src="<? echo $imagepath; ?>/title/point.gif" width="2" height="10"
					border="0" alt=""></td>
			</tr>

			<tr>
				<td colspan="3">
				<table width="100%" style="margin: 0; height: 30px;">
					<!-- Reiter -->
					<tr>
						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';">
							<? if(strstr($PHP_SELF, 'login.php')) {
							 echo '<a style="display: block; width: 100%;" href="portal.php">Home</a>';
							} 
							else { 
							 echo '<a style="display: block; width: 100%;" href="login.php">Login</a>';
							}							 
							 ?>					       
					   </td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;" href="register.php">Registrieren</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;" href="activate.php">Aktivieren</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;"
							href="">Forum</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;" href="portalchat.php">Chat</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;"
							href="">Zeitung</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;" href="screenshots.php">Screenshots</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;" target="_parent"
							href="library.php?standalone=1&amp;start=1">Techtree und<br>
						Wiki-Hilfe</a></td>

						<td width="10%" valign="middle" align="center" class="tblhead"
							onmouseout="this.style.backgroundColor='#F0F0AA';"
							onmouseover="this.style.backgroundColor='#FFFFC8';"><a
							style="display: block; width: 100%;" href="impressum.php">Impressum
						/<br>
						Kontakt</a></td>
					</tr>
				</table>
				<!-- Reiter --></td>
			</tr>
			<tr style="height: 3px;">
				<td colspan="3"></td>
			</tr>
			<tr>
				<td colspan="3" class="tblhead" align="center"><span
					style="font-size: 16px; font-weight: bold;">Holy-Wars 2:
				Multiplayer-Strategie im Browser</span><br>

				<!-- <span style="font-size:18px; font-weight:bold;">Willkommen bei <? echo isset($pagetitle) ? $pagetitle : "Holy-Wars 2"; ?></span> -->

				</td>
			</tr>
			<tr style="height: 5px">
				<td colspan="3"></td>
			</tr>

			<?
if(isset($selectPlayerName)) {
?>
			<tr>
				<td width="22%" valign="top"></td>
				<td width="53%" valign="top" align="center" class="tblbody">
				<h1>Spielernamen und Startposition wählen</h1>
				<form method="post" action="choose_name.php"></form>
				</td>
				<td width="22%" valign="top"></td>
			</tr>
			<?
  die("</td></tr></table></body></html>");
}



function print_news_table() {
  if($_GET['news'])
    $resnews=do_mysqli_query("SELECT topic,text FROM news WHERE id=".intval($_GET['news']));
  else
    $resnews=do_mysqli_query("SELECT topic,text FROM news ORDER BY time DESC");
  $dataNews=mysqli_fetch_assoc($resnews);

  $resN=do_mysqli_query("SELECT id, time, topic FROM news ORDER BY id DESC");
  
  echo "<table width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">\n";

  echo "<tr class=\"tblhead\"><td height=\"25\">\n";
  if(mysqli_num_rows($resN)==0) {
    echo "<b>Keine Neuigkeiten</b></td></tr>\n";
  }
  else {
    echo "<b>Neuigkeiten</b></td></tr>\n";
    
    if(mysqli_num_rows($resN)>1) {
      echo "<tr class=\"tblbody\"><td valign=\"middle\" height=\"30\">\n";
      while($dataN=mysqli_fetch_assoc($resN)) {
	echo "<span style=\"float:left;\"><a href=\"".$PHP_SELF."?news=".$dataN['id']."\">".$dataN['topic']."</a></span><span style=\"float:right;\">(".date("d.m.Y",$dataN['time']).")</span><br>";
      }
      echo "</td></tr>";
    }
    echo "<tr class=\"tblhead\"><td style=\"padding-top: 5px; color: blue; font-weight:bold; text-align:center\">".$dataNews['topic']."</td></tr>";
    echo "<tr><td width=\"350\" class=\"tblbody\">";
    echo $dataNews['text'];
    echo "</td></tr>\n";
  }
  
  echo "</table>\n\n";  
}

function playeronline_table() {
  $res1=do_mysqli_query("SELECT player.clanstatus as clstatus, player_online.uid AS id, player.clan AS clan, player.name AS name, player_online.lastclick AS click FROM player_online LEFT JOIN player ON player_online.uid=player.id WHERE player_online.lastclick >= ".(time()-300));
  echo "<table width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
  echo "<tr class=\"tblhead\"><td height=\"22\" valign=\"middle\" colspan=\"3\"><b>Spieler online</b></td></tr>\n";
  echo "<tr class=\"tblbody\"><td height=\"20\" valign=\"middle\" colspan=\"3\">";
  if(mysqli_num_rows($res1) == 1)
    echo "Momentan ist <b>ein Spieler</b> online!";
  elseif(mysqli_num_rows($res1) > 1)
    echo "Momentan sind <b>".mysqli_num_rows($res1)."</b> Spieler online!";
  else
    echo "Momentan sind <b>keine</b> Spieler online!";
    
  echo "</td></tr></table>\n\n";
} // playeronline_table()


function bestplayer_table() {
  $res2=do_mysqli_query("SELECT name, religion FROM player WHERE toplist > 0 ORDER BY toplist ASC LIMIT 0,5");
  echo "<table style=\"td {padding:3px;} margin-bottom:5px;\" width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
  echo "<tr class=\"tblhead\"><td height=\"22\" valign=\"middle\" colspan=\"3\"><b>Top-5 Spieler</b>&nbsp;&nbsp;<a style=\"font-size:10px;\" href=\"toplist.php\">&gt;(Top 100 hier)&lt;</a></td></tr>\n";
  while($data2=mysqli_fetch_assoc($res2)) {
    echo "<tr class=\"tblbody\"><td height=\"20\" valign=\"middle\" width=\"98%\">";
    echo $data2['name']."</td><td width=\"15\" align=\"center\">";
    
    echo getReliImage($data2['religion']);
    echo "</td></tr>\n";
  }
  echo "</table>\n\n";
}

function bestclan_table() {
  $res2=do_mysqli_query("SELECT clan.name, player.religion AS religion FROM clan LEFT JOIN player ON player.clan = clan.id WHERE clan.toplist > 0 GROUP BY player.clan ORDER BY clan.toplist ASC LIMIT 0,5");
  echo "<table style=\"td {padding:3px;} margin-bottom:5px;\" width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
  echo "<tr class=\"tblhead\"><td height=\"25\" valign=\"middle\" colspan=\"3\"><b>Top-5 Orden</b>&nbsp;&nbsp;<a href=\"toplist.php?show=clan\" style=\"font-size:10px;\">&gt;(Top 100 hier)&lt;</a></td></tr>\n";
  while($data2=mysqli_fetch_assoc($res2)) {
    echo "<tr class=\"tblbody\"><td height=\"20\" valign=\"middle\" width=\"98%\">";
    echo $data2['name']."</td><td width=\"15\" align=\"center\">";
    
    echo getReliImage($data2['religion']);
    echo "</td></tr>\n";
  }
  echo "</table>\n\n";
}


function zitat_table() {
  echo "<table style=\"td {padding:3px;} margin-bottom:5px;\" width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
  $zitat=do_mysqli_query("SELECT player.name as player, text FROM zitate LEFT JOIN player ON zitate.player=player.id WHERE active='1' ORDER BY RAND() LIMIT 1");

  if(mysqli_num_rows($zitat) > 0) {
    $zitat=mysqli_fetch_assoc($zitat);
    echo "<tr class=\"tblhead\"><td height=\"22\" valign=\"middle\" colspan=\"3\">";
    
    if($zitat['player']) {
      echo "<b>Der Weise ".$zitat['player']." sprach</b>";
    }
    else {
      echo "<b>Ein Weiser Mann sprach</b>";
    }
    
    echo "</td></tr>\n";
    echo "<tr class=\"tblbody\"><td height=\"30\" valign=\"middle\">";
    echo $zitat['text'];
  }
  else {
    echo "<tr class=\"tblhead\"><td height=\"22\" valign=\"middle\" colspan=\"3\"><b>Ein weiser Mann sprach einst</b></td></tr>\n";
    echo "<tr class=\"tblbody\"><td height=\"30\" valign=\"middle\">";
    echo "Es sind noch keine Zitate vorhanden. Schreibt neue im Men&uuml;punkt MyHW2!";
  }
  echo "</td></tr>\n";
  echo "</table>\n\n";
}


function print_you_know_table() {
  echo "<table style=\"td {padding:3px;} margin-bottom:5px;\" width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
  $present=do_mysqli_query("SELECT player.name as name, (player.points) AS points, sum(city.population) AS population, count(city.id) as citycount, clan.name as clan, player.clanstatus as clanstatus, player.toplist AS toplistplayer, player.religion AS religion FROM player LEFT JOIN clan ON player.clan=clan.id LEFT JOIN city ON city.owner=player.id WHERE player.toplist<='50' GROUP BY player.name ORDER BY RAND() LIMIT 1");
  $present=mysqli_fetch_assoc($present);
  
  echo "<tr class=\"tblhead\"><td height=\"25\" valign=\"middle\" colspan=\"3\">";
  
  if(defined("ROUND_FINISHED")) {
    echo "Kanntet Ihr <b>".$present['name']."?</b>";
  }
  else {
    echo "Kennt Ihr schon <b>".$present['name']."?</b>";
  }

  echo "</td></tr>\n";

  echo "<tr class=\"tblbody\"><td height=\"30\" valign=\"middle\" style=\"text-align:justify;\">";

  $name=$present['name'];
  $points=$present['points'];
  $population=$present['population'];
  if ($population> 10000) $roundfak = 500;
  else if ($population> 8000) $roundfak = 120;
  else if ($population>4000) $roundfak = 80;
  else if ($population>1000) $roundfak = 40;
  else $roundfak = 20;
  if($population>10000)
    $population=(round(($population/100),0) * 100);
  else
    $population=round(($population/$roundfak) * $roundfak);
  $c_cities = $present['citycount'];
  $clan_res=$present['clan'];
  $clanstatus=$present['clanstatus'];
  $top=$present['toplistplayer'];
  if($present['religion'] == 1) {$religion = "Christentums"; $enemy="die Ungl&auml;ubigen";} else {$religion = "Islams"; $enemy="das Christenpack"; }
  if($clan_res!= '' || $clan_res != NULL) {
    $clan_res="Er ist Mitglied des Ordens <b>".$clan_res."</b> ";
    if($clanstatus==63)
      $clan_res.="in welchem er als Ordensf&uuml;hrer stets um das Wohlbefinden seiner Ordensmitglieder bem&uuml;ht ist. ";
    elseif($clanstatus>0)
      $clan_res.="in welchem er eine wichtige Ministerposition einnimmt! ";
    else
      $clan_res.="an dessen Seite er gegen ".$enemy." in die Schlacht zieht! ";
  }
  if($top >0 && $top <= 10) {
    $top="Mit sagenhaften <b>".$points." Punkten</b> nimmt er die ausgezeichnete <b>".$top."te Position</b> in der Spieler-Toplist ein! ";
  } else {
    $top="Er h&auml;lt mit <b>".$points." Punkten</b> in der Toplist den <b>".$top."ten Rang</b>. ";
  }
  $rnd=rand(0,2);
  if($rnd==0) {
    if($c_cities > 0) {$city="Au&szlig;erdem sorgt er in <b>".$c_cities." St&auml;dten</b> f&uuml;r das Wohlergehen von ca. ".get_rounded_population($population)." Einwohnern!";}
    echo $name." ist Anh&auml;nger des <b>".$religion."</b>. ".$clan_res."<br>".$top." ".$city;
  }elseif($rnd==1) {
    if($c_cities > 0) {$city="In <b>".$c_cities." St&auml;dten</b> hat ".$name." f&uuml;r ca. ".get_rounded_population($population)." Untertanen zu sorgen!"; }
    echo $city." ".$name." ist Anh&auml;nger des <b>".$religion."</b>!<br>".$clan_res." ".$top;
  } else {
    if($c_cities > 0) {$city="Er herrscht in <b>".$c_cities."</b> St&auml;dten &uuml;ber ca. ".get_rounded_population($population)." Einwohner!";}
    echo $name." ist Anh&auml;nger des ".$religion."! ".$clan_res."<br>".$city." ".$top;
  }
  echo "</td></tr>\n";
  echo "</table>\n\n";
} // function

function playing_div() {
  global $nr;
  printf('<div style="margin-top:3px;">Zur Zeit spielen <b>%s</b> registrierte und aktivierte Spieler, davon sind <b>%s christliche</b> und <b>%s islamische</b> Spieler.',
         $nr[0] === -1 ? "?" : $nr[0][0]+$nr[1][0], $nr[0] === -1 ? "?" : $nr[0][0], $nr[1] === -1 ? "?" : $nr[1][0]);
  
  if (defined("BOOKING_ALLOWED") && BOOKING_ALLOWED) {
    echo "\nFür die neue Runde sind <b>".$book[0]."</b> Spieler vorangemeldet.\n";
  }

  echo "</div>\n";
}
?>