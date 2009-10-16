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
 * Stefan Neubert
 * Stefan Hasenstab
 * Markus Sinner <kroddn@psitronic.de>
 *
 * This File must not be used without permission	!
 ***************************************************/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
require("includes/research.class.php");
include_once("includes/cities.class.php");
include_once("includes/player.class.php");
include_once("includes/db.class.php");
session_start();
include_once("includes/util.inc.php");
include_once("includes/banner.inc.php");
include_once("includes/page.func.php");

//$player->setActivePage(basename(__FILE__));

if (!isset($show)) {
  $show = 'player';
}

start_page();
?>
<script>
<!--
wmtt = null;
document.onmousemove = updateWMTT;
function updateWMTT(e) {
  x = (document.all) ? window.event.x + document.body.scrollLeft : e.pageX;
  y = (document.all) ? window.event.y + document.body.scrollTop  : e.pageY;
  if (wmtt != null) {
    wmtt.style.left = (x + 20) + "px";
      wmtt.style.top 	= (y - 40) + "px";
  }
}

function showWMTT(id) {
  wmtt = document.getElementById(id);
  wmtt.style.display = "block"
}

function hideWMTT() {
  wmtt.style.display = "none";
}
//-->
</script>
<style type="text/css">
.tooltip {
	position: absolute;
	display: none;
	background-color: #FFFFFF;
	border:1px solid black;
}
</style>

<?
start_body();
?>
<table><tr valign="top"><td valign="top">

<table cellpadding="0" cellspacing="1" border="0" width="500">
<tr class="tblhead" height="20">
	<td><a <? printActive("player"); ?> href="<? echo $PHP_SELF; ?>?show=player">Spieler</a></td>
	<td><a <? printActive("player_avg"); ?> href="<? echo $PHP_SELF; ?>?show=player_avg">Durchschnitt</a></td>
	<td><a <? printActive("town"); ?> href="<? echo $PHP_SELF; ?>?show=town">St&auml;dte</a></td>
	<td><a <? printActive("population"); ?> href="<? echo $PHP_SELF; ?>?show=population">Einwohner</a></td>
  <?php if(!defined("HISPEED") ) { ?>
	<td><a <? printActive("clan"); ?> href="<? echo $PHP_SELF; ?>?show=clan">Orden</a></td>
  <?php } ?>
	<td><a <? printActive("honor"); ?> href="<? echo $PHP_SELF; ?>?show=honor">Bonuspunkte</a></td>
	<td><a <? printActive("div"); ?> href="<? echo $PHP_SELF; ?>?show=div">Diverses</a></td>
</tr>
<tr>
<td colspan="7" align="center" style="padding: 8px;">
<a title="Der HW2-Bote ist da! Hier klicken." alt="Grafik HW2-Bote" 
   href="http://www.holy-wars2.de/wiki/index.php/Zeitung" target="_blank">
    <img src="http://www.holy-wars2.de/Bote.jpg"><br>
    Der <i>Holy-Wars 2 Bote</i> ist da! Hier klicken.
</a>
<p>
Im <a href="chat.php">IRC-Chat</a> können weitere Spieler und deren Toplisten-Platzierung 
erfragt werden. 
</td>
</tr>
</table>


<table cellpadding="0" cellspacing="1" border="0" width="500">
<tr class="tblhead">
<?php
// Abhängig von show die entsprechende Funktion aufrufen
switch($show) {
  case "player":
  case "player_avg":
  case "town":
  case "population":
  case "clan":
  case "div":
  case "honor":
    // Funktion zusammenbauen und aufrufen
    $function = "top_".$show;
    $function();
    break;
}
?>
</tr>
<tr height="60"><td></td></tr>
</table>

</td>

<!-- Start AD Table -->
<td valign="top">
<div style="height: 602px; font-size: 10px; font-family: 'Times New Roman',Times,serif">
<? 
$timemod = time() % 3600;

if (!is_premium_noads()) {   
  // In gewissen abständen rotieren
  if($timemod < 500) {
      //include("includes/easyad_skyscraper.html");
      include("ads/openinventory_120x600.php");
  }
  // FirstAffait nur zwischen 23 und 5 Uhr!
  else if ( $timemod < 1000 && (date("G") >= 23 || date("G") <= 5) ) {
    include("ads/firstaffair_140x600.php");
  }
  else if ( $timemod < 1600) {
    include("ads/sponsorads-skyscraper.php");
  }   
  else if ($timemod < 2200) {
    include("ads/qualigo_leaderboard.php");    
  }
  else if ($timemod < 2800) {
    include("ads/ebay_160x600.php");    
  }
  else if ($timemod < 3100) {
    include("ads/mobile-skyscraper.php");
  }
  else if ($timemod < 3400) {
    include("ads/ing_diba_120x600.php");
  }
  else {
      //include("includes/easyad_skyscraper.html");
      include("ads/openinventory_120x600.php");
  }
} // if (!is_premium_noads()) 
?>
</div>
<p>
<div style="height: 602px; font-size: 10px; font-family: 'Times New Roman',Times,serif">
<?
if (!is_premium_noads()) {
  if ( $timemod < 1000) {
    include("ads/ebay_160x600.php");
  }  
  else if ($timemod < 1500) {
    include("includes/qualigo.160x600.html");    
  }
  else if ( $timemod < 2200) {
    include("ads/buch24-skyscraper.html");
  }
  else if ( $timemod < 3000) {
    include("ads/sponsorads-skyscraper.php");
  }
  else if ($timemod < 3500) {
    include("includes/affilimatch-skyscraper.html");
  }
  else {
    include("includes/ing_diba_120x600.html");
  }
}
//if (!is_premium_noads()) include("includes/easyad_skyscraper_portal.html");
?>
</div>
</td><!-- AD Table -->

</tr>
</table>

<? end_page(); ?>

<? 
function top_town () {     
  echo "<td>#</td>";
  echo "<td>&nbsp;</td>";
  echo "<td>Name</td>";
  echo "<td>Einwohner</td>";
  echo "<td>Loyalität</td>";
  echo "<td>Besitzer</td>";
  //echo "<td>Orden</td>";

  $res1 = do_mysql_query("SELECT id,owner,name,population,prosperity,capital,loyality,religion FROM city ORDER BY population DESC LIMIT 100");

  $i=0;
  while ($data1 = mysql_fetch_assoc($res1)) {
    if($data1['owner']) {
      $res2 = do_mysql_query("SELECT player.name, player.religion, clan.name AS clan FROM player LEFT JOIN clan ON player.clan = clan.id WHERE player.id=".$data1['owner']);
      $data2 = mysql_fetch_assoc($res2);
    }
    else {
      $data2 = null;
    }

    $i++;
    echo "<tr class=\"tblbody\">";
    echo "<td nowrap>".$i.".</td>";
    echo '<td class="padding-top: 0; padding-bottom: 0px;">'.getReliImage($data1['religion'])."</td>";
    echo "<td nowrap>".get_city_htmllink($data1, false)."</td>";
    echo "<td nowrap>".get_population_string($data1['population'], $data1['prosperity'])."</td>";
    echo "<td nowrap>".get_loyality_string($data1['loyality'])."</td>";
    echo "<td nowrap>".($data2 ? (getReliImage($data2['religion'])." ".get_info_link($data2['name'], 'player', 1) ) : "Herrenlos")."</td>";
  }//while
}  // function top_town ()



function top_player() {
  global $player;

  echo "<td>#</td><td>&nbsp;</td>";
  echo "<td>Name</td>";
  echo "<td>Punkte</td>";
  //echo "<td>Religion</td>";
  if(!defined("HISPEED") ) {
    echo "<td>Orden</td>";
  }
  echo "</tr>";

  $res3 = do_mysql_query("SELECT player.id as id, player.avatar as avatar, player.status as locked, player.name AS name, (player.points) AS points, player.religion AS religion, clan.name AS clan, clan.id AS clanid FROM player LEFT JOIN clan ON player.clan = clan.id WHERE activationkey IS NULL AND player.name IS NOT NULL ORDER BY points DESC LIMIT 100");
  
  $i=0;
  while ($data3 = mysql_fetch_assoc($res3)) {
    $i++;
    echo "<tr class=\"tblbody\">";
    echo "<td>".$i.".</td>";
    echo '<td class="padding-top: 0; padding-bottom: 0px;">'.getReliImage($data3['religion'])."</td>";
    
    echo "<td nowrap>";
    echo get_info_link($data3['name'],'player',1);
    print_multi($data3);

    echo "</td><td>".number_format($data3['points'],0,",",".")."</td>";
    if(!defined("HISPEED") ) {
      echo '<td><a href="info.php?show=clan&id='.$data3['clanid'].'">'.$data3['clan']."</a></td>";
    }
    echo"</tr>\n";    
  }
} //  top_player()


function top_player_avg() {
  global $player;

  echo "<td>#</td>";
  echo "<td>&nbsp;</td>";
  echo "<td>Name</td>";
  echo "<td>Schnitt</td>";
  //echo "<td>Religion</td>";
  echo "<td>Orden</td>";  

  $res3 = do_mysql_query("SELECT player.id, player.avatar, player.status as locked, player.name, round(player.pointsavg/player.pointsupd) AS points, player.religion, clan.name AS clan FROM player LEFT JOIN clan ON player.clan = clan.id WHERE activationkey IS NULL AND player.name IS NOT NULL ORDER BY points DESC LIMIT 100");
  
  $i=0;
  while ($data3 = mysql_fetch_assoc($res3)) {
    $i++;
    echo "<tr class=\"tblbody\">";
    echo "<td>".$i.".</td>";
    echo '<td class="padding-top: 0; padding-bottom: 0px;">'.getReliImage($data3['religion'])."</td>";
    echo "<td nowrap>";
    echo get_info_link($data3['name'],'player',1);

    print_multi($data3);

    echo "</td><td>".prettyNumber($data3['points'])."</td>";
    echo "<td nowrap>".get_info_link($data3['clan'],'clan')."</td>";
  }
} //  top_player()




function top_population() {
  echo "<td>#</td>";
  echo "<td>&nbsp;</td>";
  echo "<td>Name</td>";
  echo "<td>Einwohner</td>";
//  echo "<td>Religion</td>";
  echo "<td>Orden</td>";

  $res = do_mysql_query("SELECT sum(population) as population, player.id, player.name, ".
			" player.religion, clan.name as clan ".
			" FROM city LEFT JOIN player ON player.id = city.owner ".
			" LEFT JOIN clan ON player.clan = clan.id ".
			" GROUP BY owner ORDER BY population DESC LIMIT 100"); 

  // $res1 = do_mysql_query("SELECT player.id AS id, player.name AS name, player.religion AS religion, clan.name AS clan FROM player LEFT JOIN clan ON player.clan = clan.id");
  $pos = 1;

  while ($data1 = mysql_fetch_assoc($res)) {
//    if ($data1['religion'] == 1)
//      $religion = "Christ";
//    else
//      $religion = "Islam";


    echo "<tr class=\"tblbody\">\n";
    echo "<td>".$pos++.".</td>";
    echo '<td nowrap class="padding-top: 0; padding-bottom: 0px;">'.getReliImage($data1['religion'])."</td>";
    echo "<td nowrap>".get_info_link($data1['name'],'player',1)."</td>";
    echo "<td nowrap>ca. ".prettyNumber(get_rounded_population($data1['population']))."</td>";
    //echo "<td>".$religion."</td>";
    echo "<td nowrap>".get_info_link($data1['clan'],'clan')."</td>\n";
  }// while
} // top_population()



function top_clan(){
  $i=0;
  echo "<td>#</td>";
  echo "<td>&nbsp;</td>";
  echo "<td><a href=\"toplist.php?show=clan&sortby=name\">Name</a></td>";
  echo "<td><a href=\"toplist.php?show=clan&sortby=punkte\">Punkte</a></td>";
  echo "<td><a href=\"toplist.php?show=clan&sortby=mitglieder\">Mitglieder</a></td>";
  echo "<td><a href=\"toplist.php?show=clan&sortby=durchschnitt\">Durchschnitt</a></td>";
  //echo "<td>Religion</td>";
  echo "<td>&nbsp;</td>";
  echo "</tr>";
  switch($_GET['sortby'])
  {
    case "name": 
    {
      $orderby = "name asc";
      break;
    }	
    case "punkte":
    {
      $orderby = "clan.points desc";
      break;
    }
    case "mitglieder":
    {
      $orderby = "num desc";
      break;
    }
    case "durchschnitt":
    {
      $orderby = "medium desc";
      break;
    }
    default:
    {
      $orderby = "clan.points desc";
      break;
    }
  }

  $res = do_mysql_query("SELECT clan.name, clan.id AS clanid, clan.points, player.religion,count(*) AS num, floor( clan.points / count( * ) ) AS
medium FROM clan LEFT JOIN player ON clan.id=player.clan GROUP BY player.clan ORDER BY ".$orderby." LIMIT 100");
  while ($data = mysql_fetch_assoc($res)) {
    $i++;
    echo "<tr class=\"tblbody\">";
    echo "<td>".$i.".</td>";
    echo "<td>".getReliImage($data['religion'])."</td>";
    echo "<td nowrap><a href=\"info.php?show=clan&id=".$data['clanid']."\">".$data['name']."</a></td>";
    echo "<td>".prettyNumber($data['points'])."</td>";
    echo "<td>".$data['num']."</td>";
    echo "<td>".prettyNumber($data['medium'])."</td>";
//    if ($data['religion'] == 1)
//      echo "<td>Christentum</td>";
//    else
//      echo "<td>Islam</td>";
    echo "<td><a href=\"toplist.php?show=clan&sortby=".$_GET['sortby']."&clanmembers=".$data['clanid']."#cl".$data['clanid']."\">&gt;</a></td>\n";
    echo "</tr>";
    //edit franzl 15.08.04
    if($_GET['clanmembers'] == $data['clanid']) {
      echo "<tr><td colspan=\"7\">\n";
      $res2 = do_mysql_query("SELECT name,points,religion,clanstatus FROM player WHERE clan = ".$data['clanid']." ORDER BY points DESC");
      echo "<table id=\"cl".$data['clanid']."\" cellpadding=\"0\" cellspacing=\"1\" width=\"100%\" border=\"0\">\n";
      echo "<tr><td width=\"40%\"></td>\n";
      echo "<td class=\"tblhead\" width=\"20%\"><strong>Rang</strong></td>\n";
      echo "<td class=\"tblhead\" width=\"20%\"><strong>Religion</strong></td>\n";
      echo "<td class=\"tblhead\" width=\"20%\"><strong>Punkte</strong></td>\n";
      echo "</tr>\n";
      while ($data2 = mysql_fetch_assoc($res2)) {
	if($data2['religion'] == 1) { $religion = "Christentum"; } else { $religion = "Islam"; }
			
	if ($data2['clanstatus'] > 0) {
	  if ($data2['clanstatus'] == 63)
	    $clanstatus = "Ordensleiter";
	  else {
	    if ($data2['clanstatus'] & 1)
	      $clanstatus = "Finanzminister";
	    if ($data2['clanstatus'] & 2)
	      $clanstatus = "Innenminister";
	    if ($data2['clanstatus'] & 4)
	      $clanstatus =  "Aussenminister";	
	  }
	} else {
	  $clanstatus = "Member";
	}
			
	echo "<tr><td class=\"tblhead\">".get_info_link($data2['name'],'player',1)."</td><td class=\"tblbody\">".$clanstatus."</td><td class=\"tblbody\">".$religion."</td><td class=\"tblbody\">".number_format($data2['points'],0,",",".")."</td></tr>\n";
      }
      echo "</table>\n";
      echo "</td>\n";
    }
  }
} // top_clan




function top_honor() {
  global $player;
?>

<td colspan="5">
<br>Bonuspunkte haben keinen Einfluss auf das Spiel an sich. Man kann Bonuspunkte im Moment durch Werbung von neuen Mitspielern über den Link in den <a href="settings.php?show=account">Einstellungen</a> gewinnen, oder indem man sich regelmässig einloggt (siehe <a target="_blank" href="http://forum.holy-wars2.de/viewtopic.php?t=7735#98554">hier</a>).<br>
</td></tr><tr class="tblhead">
  
<td width="10">Platz</td>
<td nowrap>Name</td>
<td width="10">Bonuspunkte</td>
<td width="10" nowrap>Religion</td>
<td nowrap>Orden</td>

<?
  $res3 = do_mysql_query("SELECT player.name AS name, (player.bonuspoints + coalesce(sum(player_monument.honor),0) ) AS points, player.religion AS religion, clan.name AS clan FROM player LEFT JOIN clan ON player.clan = clan.id LEFT JOIN player_monument ON player.id=player_monument.player WHERE activationkey IS NULL AND player.name IS NOT NULL GROUP BY player.name ORDER BY points DESC LIMIT 100");
  
  $i=0;
  while ($data3 = mysql_fetch_assoc($res3)) {
    if($data3['points'] <= 0) break;
    $i++;
    echo "<tr class=\"tblbody\">";
    echo "<td align=\"right\">".$i.".</td>";
    echo "<td>".get_info_link($data3['name'],'player',1);
    $points = $data3['points'];
    if($points > 20) {
        $points = get_rounded_value($points);
        $points_string = sprintf("ca. %s", number_format($points,0,",","."));
    }
    else {
      $points_string = "unter 20";
    }
    echo "</td><td align=\"right\">".$points_string."</td>";
    
    if ($data3['religion'] == 1)
      echo "<td>Christentum</td>";
    else
      echo "<td>Islam</td>";
    echo "<td>".get_info_link($data3['clan'],'clan')."</td>";
  }
} //  top_honor()




// Ein paar witzige, belanglose Dinge ausgeben
function top_div () {
  echo "<td>";

  echo "<h2>Allgemein</h2>\n";
  printRoundTimes();
  
  
  $sql = "SELECT avg(points), sum(rp) AS rp, count(*) AS cnt ".
    " FROM player ".
    " WHERE religion IS NOT NULL AND (STATUS IS NULL OR STATUS = 3) GROUP BY religion ORDER BY religion";

  $res = do_mysql_query($sql);
  $christ = mysql_fetch_array($res);
  $islam  = mysql_fetch_array($res);

  $sql = "SELECT sum(population) AS pop, count(*) AS cnt ".
    " FROM city ".
    " GROUP BY religion ORDER BY religion";
  $res = do_mysql_query($sql);
  $christcities = mysql_fetch_array($res);
  $islamcities  = mysql_fetch_array($res);
  $sumcities = $islamcities['cnt'] + $christcities['cnt'];
 
  getMapSize($mx, $my);
  $mapsize = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM map WHERE type != 1");
  $density = round( 100 * $sumcities / ($mapsize['c']/25), 2 );
		    
  $settle = do_mysql_query_fetch_assoc("SELECT count(*) AS cnt,sum(missiondata) as settlers FROM army ".
                                       " WHERE mission = 'settle' ");
  echo "<p>";
  echo "<h2>Herrscher</h2>\n";

  echo "Auf der bekannten Welt sind <b>".prettyNumber($christ['cnt']).
    " christliche</b> Recken und Fürsten bekannt, denen sich <b>".prettyNumber($islam['cnt']).
    " islamische</b> Statthalter und Kämpfer entgegenstellen (".prettyNumber($islam['cnt']+$christ['cnt'])." Spieler). ";
  echo "Die Christen haben sich eine <b>Durchschnittspunktezahl von ".prettyNumber($christ[0])."</b> erkämpft. Denen stehen <b>".prettyNumber($islam[0])." Punkte</b> der islamischen Anhänger gegenüber.<p>\n";

  echo "<p>In jedem Winkel der bekannten Welt streben Kleriker und Lehrmeister nach neuem Wissen. ".
    "Auf Seiten des Christentums haben sich hierbei <b>".prettyNumber($christ['rp']).
    "</b> FP angesammelt, die islamischen Herrscher haben <b>".prettyNumber($islam['rp']).
    "</b> FP erzielt.\n";
  

  $ctime = $utime = 0;
  
  echo "<h2>Besiedlung und Städte</h2>\n";
  echo "Die Welt ist zu ".$density."% besiedelt und besteht aus ".$mx." * ".$my." Feldern. ";
  if(defined("START_POS_NEW") && START_POS_NEW) {
      echo "Der <b>Siedlungsradius</b> beträgt ".getSettleRadius()." Sektoren (".(getSettleRadius()*40)." Felder in jede Richtung). ";
      if(getConfigTimes('settleradius', $ctime, $utime) && $utime > 0) {
          printf("Die letzte Aktualisierung des Siedlungsradius war am %s um %s.",
                 date("d.m.Y", $utime), date("H:i", $utime) );
      }
  }
  
  echo "<p>";

  if($christcities['cnt'] > 0 && $islamcities['cnt']) {
  echo "Die <b>Christen bewohnen ".
    prettyNumber($christcities['cnt'])." Städte</b> mit ".prettyNumber($christcities['pop']).
    " Einwohnern, <b>".prettyNumber($islamcities['cnt'])." islamische Städte</b> werden von ".
    prettyNumber($islamcities['pop'])." Einwohnern bevölkert. Das sind ".
    prettyNumber($christcities['pop']/$christcities['cnt']).
    " Einwohner pro christlicher und ".prettyNumber($islamcities['pop']/$islamcities['cnt']).
    " Einwohner pro islamischer Stadt.<br>";
  }
  else {
    echo "Es existieren keine ".($christcities['cnt'] == 0 ? "christlichen" : "islamischen")." Siedlungen.<br>";
  }
    
  echo "<b>".prettyNumber($settle['cnt'])." Trupps</b> sind mit <b>".
    prettyNumber($settle['settlers'])." Siedlern</b> auf dem Weg zur Gründung neuer Welten und neuer Zivilisationen.\n<p>";

    
  echo "<h2>Krieg</h2>\n";

  $war = do_mysql_query_fetch_assoc("SELECT id1, name, count(*) as cnt ".
				    " FROM relation LEFT JOIN player ON id1 = player.id ".
                                    " WHERE type = 0 GROUP BY id1,name ORDER BY cnt DESC LIMIT 1");
  if ($war['name'] != null)
    echo "Der größte Kriegstreiber der Welt ist <b>".$war['name']."</b> mit ".$war['cnt']." ausgesprochenen Kriegserklärungen.\n";
  else
    echo "Hm. So wie es scheint gibt es keine Kriege...\n";
    
  
  $war = do_mysql_query_fetch_assoc("SELECT id2, name, count(*) as cnt ".
				    " FROM relation LEFT JOIN player ON id2 = player.id ".
                                    " WHERE type = 0 GROUP BY id2,name ORDER BY cnt DESC LIMIT 1");
  if ($war['name'] != null)
    echo "<br>Das größte Opfer heißt <b>".$war['name']."</b> - ihm wurde ".$war['cnt']." mal der Krieg erklärt.\n";     
  else
    echo "<br>Und wie schon gesagt. Komischerweise gibts keine Kriege...\n";

  $siege  = do_mysql_query_fetch_assoc("SELECT count(*) as cnt FROM army ".
                                       "WHERE mission = 'siege' AND unix_timestamp( ) > endtime");
  $attack = do_mysql_query_fetch_assoc("SELECT count(*) as cnt FROM army ".
                                       " WHERE mission != 'settle' AND mission != 'move' AND mission != 'return' ".
                                       " AND unix_timestamp( ) < endtime");


  echo "<p>Barden berichten, dass weltweit <b>".$siege['cnt']." Belagerungen</b> im Gange sind, ";
  echo "während <b>".prettyNumber($attack['cnt'])." Armeen</b> zu Schlachten unterwegs sind.<p>";

  echo '<p style="margin-top: 30px; margin-bottom: 30px; "><center>';
  
//  if (!is_premium_noads())
//    include("includes/getprice-square.html");
  if (!is_premium_noads())
    include("ads/ing_diba_300x250.php");

  echo "</center><p>&nbsp;</td>";
}


function print_multi($data) {
  if (isset($_SESSION['player']) && $_SESSION['player']->isMultihunter()) {
    switch ($data['locked']) {
    case 2: echo " <font color=\"#DD2020\">gsp.</font>"; break;
    case 3: echo " <font color=\"#DD2020\">Vrd.</font>"; break;
    } // switch
  }// if 
}

?>
