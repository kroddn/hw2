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
 * Copyright (c) 2003
 *
 * Stefan Neubert, Stefan Hasenstab
 *
 * This File must not be used without permission	!
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/config.inc.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
include_once("includes/research.class.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");

$player->setActivePage(basename(__FILE__));

$research = $_SESSION['research'];

if (isset($rid)) {
  $error = $research->startResearch($rid);
  if($error === null) {
    header_redirect("research.php?started=1");
    exit;
  }
}

if (isset($abort)) {
  $research->abortResearching();
  header_redirect("research.php?aborted=1");
  exit;
}

start_page();
?>
<script language="JavaScript">
<!--
function showhide_research(id) {
  if(document.getElementById("sh"+id).innerHTML == "anzeigen") {
    document.getElementById("sh"+id).innerHTML = "ausblenden";
    document.getElementById("cat"+id).style.display = "inline";
  } 
  else {
    document.getElementById("sh"+id).innerHTML = "anzeigen";
    document.getElementById("cat"+id).style.display = "none";
  }
}
//-->
</script>
<? start_body(); ?>
<h1>Forschung</h1>
<?
if ($error!=null && strlen($error) > 0) {
  echo "<h2 class='error'>Es ist ein Fehler aufgetreten: ".$error."</h2>\n";
}

if (RESEARCHSPEED > 1) echo "Forschung wird um Faktor ".RESEARCHSPEED." beschleunigt.<p>";

if( defined("RESEARCH_BIGSCHOOL") && defined("RESEARCH_LIBRARY") ) {
  $sql =
    "SELECT count(*) AS cnt FROM playerresearch ".
    " WHERE research IN (".RESEARCH_BIGSCHOOL.",".RESEARCH_LIBRARY.") ".
    " AND player = ".$_SESSION['player']->id;

  $schools = do_mysql_query_fetch_assoc($sql);
}
else {
  $schools['cnt'] = 2;
}
if($schools['cnt'] < 2) { ?>
Ihr könnt <b><? echo $schools['cnt']+1;?> Forschung<? if($schools['cnt']>0) echo "en"; ?> gleichzeitig</b> durchführen.<br>
Durch Erforschung von <? if($schools['cnt'] == 0) echo "Klassensystem und "?> Bibliotheken erhöht sich dieses Limit <? if($schools['cnt'] == 0) echo"<b>jeweils</b>"; ?> um 1.
<? 
}
echo "<p>";


$res0 = do_mysql_query("SELECT rid FROM researching WHERE player = ".$_SESSION['player']->getID());
$countResearching = mysqli_num_rows($res0);

if($countResearching >= $schools['cnt']+1) {
  $maxResearches = 1;

  echo "<table style=\"margin:1px;\" width=\"498\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
  echo "<tr><td class=\"tblhead\">Während Ihr darauf wartet, dass die Forschung abgeschlossen ist, könntet Ihr für Holy-Wars 2 voten.</td></tr>";

  echo "<tr><td class=\"tblhead\">";
  //include("includes/vote.inc.php");
  echo "</td></tr><tr><td colspan=\"2\" height=\"5\"></td></tr></table>";
} 
else {
  $maxResearches = 0;
}


$rs=$research->getResearches();
for ($i=0;$i<sizeof($rs);$i++) {
  if(!isset($rs[$i]['content']['category'])) {
    continue;
  }
  if (isset($rs[$i]['content']['category']) && 
      $rs[$i]['content']['category'] != $rs[$i - 1]['content']['category']){
    //Neue Kategorie
    if($rs[$i]['content']['category'] != 0)
      echo "</table>\n\n";      
    
    echo "<table style=\"margin:1px;\" width=\"498\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
    echo "<tr><td colspan=\"2\" height=\"5\"></td></tr>\n<tr><td width=\"75%\" style=\"margin-left:2px; padding-left:10px;\" class=\"tblhead\"><a href=\"library.php?".$rs[$i]['content']['lib']."\">".$researchcategories[$rs[$i]['content']['category']]."</a></td><td class=\"tblhead\" style=\"text-align:right; padding-right:10px;\" width=\"15%\"><span id=\"sh".$rs[$i]['content']['category']."\" style=\"cursor:hand; font-weight:bold;\" onclick=\"showhide(".$rs[$i]['content']['category'].")\">ausblenden</span></td></tr>\n";
      
    if ($rs[$i]['content']['category'] == 0 && $player->getNoobLevel() > 0) {
      $starttime = getRoundStartTime();
      $noobtime = defined("SPEED") && SPEED ? (7*24*60*60) : (3*7*24*60*60);

      echo "<tr><td align=\"center\" class=\"tblhead\" colspan=\"2\"><font color=\"red\">Beim Erforschen einer höheren Stufe der Reichsverwaltung verliert Ihr Euren Neulingsschutz!<br>Der Neulingsschutz wirkt bis ".
        (date("d.m.Y H:i:s", ($starttime > $player->getActivationTime() ? $starttime : $player->getActivationTime()) + $noobtime) ).", danach verfällt er automatisch.</font></td></tr>\n";
    }

    //$player->getNoobLevel() > 0 &&
    if ($rs[$i]['content']['category'] == 1) {
      getMapSize($fx, $fy);

      if(round($fy/2) < $_SESSION['cities']->city_y) {
        $direction ="südlich";
        $hintsearch="Lehmgewinnung";
        $hintnotsearch="Steinabbau";
        $hintwhen = "Norden";
      }
      else {
        $direction ="nördlich";
        $hintsearch="Steinabbau";
        $hintnotsearch="Lehmgewinnung";
        $hintwhen = "Süden";
      }

      echo "<tr><td align=\"center\" class=\"tblhead\" colspan=\"2\"><font color=\"red\">Eure aktuelle Stadt ist <b>$direction</b> des Holy-Wars-Äquators gelegen. Daher solltet Ihr <b>$hintsearch</b> forschen. $hintnotsearch solltet Ihr erst erforschen, wenn Ihr eine Stadt im $hintwhen gegründet oder erobert habt.</font></td></tr>\n";
    }


    echo "</table>\n\n";
    echo "<table id=\"cat".$rs[$i]['content']['category']."\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\" style=\"width:500px;\">\n";
  }
	
  echo "<tr class=\"tblbody\">";
  // Alter Link passt nich mehr
  //$href = "library.php".$rs[$i]['content']['lib_link'];
  $href =  "library.php?s1=3&s2=".$rs[$i]['content']['category']."&s3=0&research_id=".$rs[$i]['content']['id'];
  
  echo "<td width=\"55%\"><a href=\"".$href."\">".$rs[$i]['content']['name']."</a></td>";
  echo "<td width=\"15%\" style=\"text-align:right; padding-right:10px;\">".$rs[$i]['content']['rp']." <img src=\"".$imagepath."/rp.gif\"></td>";
  echo "<td width=\"15%\" style=\"text-align:right; padding-right:10px;\">".formatTime(max(MIN_RESEARCH_TIME, $rs[$i]['content']['time']/RESEARCHSPEED))." <img src=\"".$imagepath."/time.gif\"></td>";
  
  
  $res1 = do_mysql_query("SELECT rid,starttime,endtime FROM researching WHERE player = '".$_SESSION['player']->id."' AND rid = '".$rs[$i]['content']['id']."'");
  $count1 = mysqli_num_rows($res1);
  $data1 = mysqli_fetch_assoc($res1);
  
  if($count1) {
    $remaining=$data1['endtime'] - time();
    echo "<td width=\"15%\" class=\"tblhead\"><span class=\"noerror\" id=\"".$rs[$i]['content']['id']."\"><script type=\"text/javascript\">addTimer('".$remaining."',".$rs[$i]['content']['id'].");</script></span></td>";
  }
  else {		
    $res2 = do_mysql_query("SELECT research FROM playerresearch WHERE player = '".$_SESSION['player']->id."' AND research = '".$rs[$i]['content']['id']."'");
    $data2 = mysqli_fetch_assoc($res2);
    
    if($data2['research'] == $rs[$i]['content']['id']) {
      echo "<td width=\"15\" class=\"tblhead\" align=\"center\">";
      if($_SESSION['player']->tutorialLevel() > 0) {
        echo '<a href="research.php?started=">';
      }
      echo "<img alt=\"-\" src=\"".$imagepath."/research_ok.gif\" border=\"0\">";
      if($_SESSION['player']->tutorialLevel() > 0) {
        echo '</a>';
      }
      echo "</td>\n";
    } 
    else {
      if($maxResearches == 0) {
	if($_SESSION['player']->rp >= $rs[$i]['content']['rp']) {
	  $areabuilding = false;
	  echo "<td width=\"15\" class=\"tblhead\" align=\"center\"><a href=\"research.php?rid=".$rs[$i]['content']['id']."\"".($areabuilding ? 'onClick="return confirm(\'Diese Forschung führt zu gebietsabhändigen Gebäuden. Seid Ihr sicher, dass Ihr forschen wollt?\')"' : "")."><img src=\"".$imagepath."/arrow.gif\" border=\"0\"></a></td>";
	} 
	else {
	  echo "<td width=\"15\" class=\"tblhead\" align=\"center\"><img src=\"".$imagepath."/arrowred.gif\" border=\"0\"></td>";
	}
      } 
      else {
	// Es laufen schon 3...
	echo "<td width=\"15\" class=\"tblhead\" align=\"center\">-</td>";
      }
    }
  }		
  echo "</tr>\n"; 
} // for
echo "</table>\n\n</td></tr>\n</table>\n\n";
?>
</table>
<p>
<?php 
$research->globalstatus = false; 

end_page();
?>
