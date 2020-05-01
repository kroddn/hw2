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
 * written by
 * Stefan Neubert
 * Stefan Hasenstab
 * Markus Sinner <kroddn@psitronic.de>
 *
 *  This File must not be used without permission!
 ***************************************************/

include_once("includes/db.inc.php");
include_once("includes/research.class.php");
include_once("includes/cities.class.php");
include_once("includes/player.class.php");
include_once("includes/util.inc.php");
include_once("includes/config.inc.php");
include_once("includes/session.inc.php");
include_once("includes/banner.inc.php");

$player->setActivePage(basename(__FILE__));

$builderror = null;

if (isset($build) && isset($count) && ($count>0)) {
  $builderror = $_SESSION['cities']->build($build,$count);
} 
else if (isset($stop)) {
  $_SESSION['cities']->abortBuilding($stop);
} 
else if (isset($destroy)) {
  $error = $_SESSION['cities']->destroyBuilding($destroy, 1);
} 
else if (isset($destroyall)) {
  $error = $_SESSION['cities']->destroyBuilding($destroyall, 99);
} 
else if (isset($destroy2)) {
  $error = $_SESSION['cities']->destroyBuildingUninvented($destroy2);
}


start_page();
start_body();

if ($error!=null) {
  echo "<h1 class='error'>Fehler: ".$error."</h1>\n";
}
if($builderror != null) {
  echo '<h1 class="error">Fehler: '.$builderror."</h1>\n"; 
}
?>
<table border="0">
<tr><td>
<h1>Baumeister in <? echo $_SESSION['cities']->getACName()." ".getReliImage($_SESSION['cities']->getACReligion()); ?> </h1>
<? if (BUILDINGSPEED > 1) echo "Errichten wird um Faktor ".BUILDINGSPEED." beschleunigt.<p>"; ?>

Eine Anleitung findet sich <a href="library.php?s1=0&s2=3&s3=1">hier in der Bibliothek</a>. Dort wird auch der fundamentale <font color="red">Unterschied</font> zwischen <b>Lehmgruben</b> und <b>Steinbrüchen</b> erklärt!<p>
<b>Achtung:</b> es sollten NIE mehr verarbeitende Gebäude gebaut werden als man erzeugende Gebäude hat. Es reicht zum Beispiel <b>1 Sägewerk für 2 Holzfäller</b>.
</td></tr>
<tr><td>

<table cellspacing="1" cellpadding="0" border="0">

<?

$actcat=-1;

$bld=$_SESSION['cities']->getBuildings();

for($i=0;$i<=sizeof($bld);++$i) {

  if($bld[$i]['show']) {
    if ($bld[$i]['category']>$actcat){
      if ($actcat>=0 && $_SESSION['player']->getNoobLevel() > 0) {
        echo '<tr><td colspan="16" class="tblbody" align="center">';
        echo'Achtung: bauen Sie <b>keine unnötigen Gebäude</b>. Lesen Sie sich zuerst die Bibliothek durch (auf den Gebäudenamen klicken)</td></tr>';
      }
      echo "<tr><td colspan='16'>&nbsp;</td></tr><tr><td colspan='3' class='tblhead'><b>".$buildingcategories[$bld[$i]['category']]."</b></td>";
      echo "<td colspan=\"3\" class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">Kosten</td>";
      echo "<td class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">Dauer</td>";
      echo "<td class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">Maximal</td>";
      echo "<td colspan=\"7\" class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">Ausbaumenge</td>";
      echo "<td class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">&nbsp;</td>";
      echo "</tr>\n";
      
      $actcat=$bld[$i]['category'];      
    }
    
    echo "<tr><td align='center' class='tblbody' width='15'>";

    if ($bld[$i]['isbuild']>0) echo "<b>".$bld[$i]['isbuild']."</b>";
    else echo "-";
    echo "</td>";

    echo "<td align='center' class='tblbody' width='15'>";
    if ($bld[$i]['inbuild']>0) echo "<b class='noerror'>".$bld[$i]['inbuild']."</b>";
    else echo "-";
    echo "</td>";

    // Alter Link... funktioniert nur so lange wie keinen  neuen Gebäude kommen
    //echo "<td class='tblbody' width='160'><a target='_new' href='library.php".$bld[$i]['lib']."'>";
    $class = $bld[$i]['religion'] != null && $bld[$i]['religion'] != $_SESSION['player']->getReligion() ? "style='color: FF4040;' title='religionsfremdes Gebäude' " : "";
    printf("<td class='tblbody' width='160'><a %s target='_new' href='library.php?s1=1&s2=%d&s3=0&building_id=%d'>",
           $class, $bld[$i]['category'], $bld[$i]['id'] );
    echo $bld[$i]['name'];
    echo "</a></td>";

    echo "<td align='right' class='tblbody' width='55'>";
    echo $bld[$i]['gold']." <img src='".$imagepath."/gold2.gif' alt=\"Gold\">";
    echo "</td>";

    echo "<td align='right' class='tblbody' width='45'>";
    echo $bld[$i]['wood']." <img src='".$imagepath."/wood2.gif' alt=\"Holz\">";
    echo "</td>";

    echo "<td align='right' class='tblbody' width='45'>";
    echo $bld[$i]['stone']." <img src='".$imagepath."/stone2.gif' alt=\"Stein\">";
    echo "</td>";

    echo "<td align='right' class='tblbody' width='45'>";
    echo formatTime(round($bld[$i]['time'] / BUILDINGSPEED))." <img src='".$imagepath."/time.gif'>";
    echo "</td>";


    echo "<td align='center' class='tblbody' width='50'>";
    $possible = $bld[$i]['actpossible'];

    if ($bld[$i]['possible']==999) {

      echo "<b class='pos'>";

      if ($possible>=12) echo "12+";

      else if ($possible < 0) echo "0";

      else echo $possible;

      echo " (<img src='".$imagepath."/infinite.gif'>)</b>";

    }

    elseif ($bld[$i]['possible']>0) echo "<b class='pos'>".( ($possible < 0) ? "0" : $possible)." (".$bld[$i]['possible'].")</b>";

    else echo "-";

    echo "</td>";

    
    /******** Die Menge möglicher zu bauender Gebäude auflisten ******/
    $empty = 6;
    if ($possible>=1) {
      $maxpos = 1;
      $empty--;
      echo "<td width='6' class='tblhead'><a ".($bld[$i]['makescapital'] ? 'onClick="return confirm(\'Dies ist ein Hauptstadtgebäude... Ihr wisst was ihr tut?\')"' : "")."target='main' href='buildings.php?build=".$bld[$i]['id']."&count=1'><b>1</b></a></td>";
    }

    if ($possible>=2) {
      $maxpos = 2;
      $empty--;      
      echo "<td width='6' class='tblhead'><a target='main' href='buildings.php?build=".$bld[$i]['id']."&count=2'><b>2</b></a></td>";
    }

    if ($possible>=3) {
      $maxpos = 3;
      $empty--;
      echo "<td width='6' class='tblhead'><a target='main' href='buildings.php?build=".$bld[$i]['id']."&count=3'><b>3</b></a></td>";
    }    
    
    if ($possible>=4){
      $maxpos = 4;
      $empty--;
      echo "<td width='6' class='tblhead'><a target='main' href='buildings.php?build=".$bld[$i]['id']."&count=4'><b>4</b></a></td>";
    }

    
    if ($possible>=6) {
      $maxpos = 6;
      $empty--;
      echo "<td width='6' class='tblhead'><a target='main' href='buildings.php?build=".$bld[$i]['id']."&count=6'><b>6</b></a></td>";
    }

    if ($possible>=8) {
      $maxpos = 8;
      $empty--;     
      echo "<td width='6' class='tblhead'><a target='main' href='buildings.php?build=".$bld[$i]['id']."&count=8'><b>8</b></a></td>";
    }

    if ($possible>=9) {
      $maxpos = $possible;
      $empty--;
      if($bld[$i]['possible']==999 && $possible>=12) {
        $pcount = 12;
        $class = "";
      }
      else {
        $pcount = $possible;
        $class  = "style='color: #FF4040;'";
      }
      echo "<td width='6' class='tblhead'><a target='main' $class href='buildings.php?build=".$bld[$i]['id']."&count=$pcount'><b>$pcount</b></a></td>";
    }

    if($empty >= 0 && $maxpos < $possible && $possible > 0) {
      $empty--;
      $pcount = $possible;
      echo "<td width='6' class='tblhead'><a target='main' href='buildings.php?build=".$bld[$i]['id']."&count=$pcount'><b>$pcount</b></a></td>";
    }

    while($empty>=0) {
      echo "<td width='6' class='tblbody'>&nbsp;</td>";
      $empty--;
    }


    if ($bld[$i]['destroy']) { 
      printf("<td align='center' width='15' class='tblbody'><a title='Abreißen' href='buildings.php?destroy=%d' %s><b class='error'>x</b></a>",
             $bld[$i]['id'], 
             $bld[$i]['makescapital'] ? 'onClick="return confirm(\'Dies ist ein Hauptstadtgebäude... Seid ihr ganz sicher?\')"' : ""  );
             
      if($bld[$i]['isbuild'] > 1) {
        printf("&nbsp;<a title='Alle abreißen' onClick=\"return confirm('Alle %d %s abreißen?')\" href='buildings.php?destroyall=%d'><b class='error'>XX</b></a></td>",
               $bld[$i]['isbuild'], $bld[$i]['name'], $bld[$i]['id']);
      }
      echo "</td>";
    }      
    else echo "<td align='center' width='15' class='tblbody'>-</td>";

    echo "</tr>";

  }

}

if ($actcat==-1) {

  echo "<tr><td class='tblhead' width='300' colspan=\"7\"><b>Gebäude</b></td></tr>";

  echo "<tr><td class='tblbody' width='300' colspan=\"7\">es können derzeit keine Gebäude gebaut werden. überprüfen Sie Ihre <a href='research.php'>Forschungen</a>.</td></tr>";

}

// Unerforschte Gebäude anzeigen
$show=$_SESSION['cities']->getuninventedBuildings();
if ($show) {
  // Unerforschte Gebäude anzeigen
  echo "<tr><td colspan='14'>&nbsp;</td></tr><tr><td colspan=\"3\" class='tblhead'><b>Unerforschte Gebäude</b></td>";
  echo "<td colspan=\"3\" class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">Erstattungskosten</td>";
  echo "<td align='center' width='15' class='tblbody'></td>";
  echo "</tr>\n";
  for($i=0;$i<=sizeof($show)-1;++$i) {
    if ($show[$i]['same_religion']==true) {
      echo "<tr>";
      echo "<td align='center' class='tblbody' width='15'><b>".$show[$i]['existing']."</b></td>";
      if ($show[$i]['working']) {
        echo "<td align='center' class='tblbody' width='15'><b class='noerror'>".$show[$i]['working']."<b></td>";
      }
      else {
        echo "<td align='center' class='tblbody' width='15'> - </td>";
      }
      echo "<td align='left' class='tblbody' width='160'><b><a target='_new' href='library.php".$show[$i]['lib_link']."'>".$show[$i]['name']."</b></a></td>";
      echo "<td align='center' class='tblbody' width='55'>".floor($show[$i]['gold']/4)." <img src='".$imagepath."/gold2.gif'></td>";
      echo "<td align='center' class='tblbody' width='45'>".floor($show[$i]['wood']/4)." <img src='".$imagepath."/wood2.gif'></td>";
      echo "<td align='center' class='tblbody' width='45'>".floor($show[$i]['stone']/4)." <img src='".$imagepath."/stone2.gif'></td>";
      echo "<td align='center' width='15' class='tblbody'><a href='buildings.php?destroy2=".$show[$i]['id']."'><b class='error'>X</b></a></td>";
      echo "<tr>";
    }
  }
# Religionsfremde Gebäude anzeigen
  for($i=0;$i<=sizeof($show)-1;++$i) {
    if ($show[$i]['same_religion']==false) {
      $show_other_religion_building=true;
    }
  }

  if ($show_other_religion_building==true) {
    echo "<tr><td colspan='14'>&nbsp;</td></tr><tr><td colspan=\"3\" class='tblhead'><b>Religionsfremde Gebäude</b></td>";
    echo "<td colspan=\"3\" class=\"tblbody\" style=\"text-align:center; font-weight:bold;\">Erstattungskosten</td>";
    echo "<td align='center' width='15' class='tblbody'></td>";
    echo "</tr>\n";
    for($i=0;$i<=sizeof($show)-1;++$i) {
      if ($show[$i]['same_religion']==false) {
        echo "<tr>";
        echo "<td align='center' class='tblbody' width='15'><b>".$show[$i]['existing']."</b></td>";
        if ($show[$i]['working']) {
          echo "<td align='center' class='tblbody' width='15'><b class='noerror'>".$show[$i]['working']."<b></td>";
        }
        else {
          echo "<td align='center' class='tblbody' width='15'> - </td>";
        }
        echo "<td align='left' class='tblbody' width='160'><b><a target='_new' href='library.php".$show[$i]['lib_link']."'>".$show[$i]['name']."</b></a></td>";
        echo "<td align='center' class='tblbody' width='55'>".floor($show[$i]['gold']/4)." <img src='".$imagepath."/gold2.gif'></td>";
        echo "<td align='center' class='tblbody' width='45'>".floor($show[$i]['wood']/4)." <img src='".$imagepath."/wood2.gif'></td>";
        echo "<td align='center' class='tblbody' width='45'>".floor($show[$i]['stone']/4)." <img src='".$imagepath."/stone2.gif'></td>";
        echo "<td align='center' width='15' class='tblbody'><a href='buildings.php?destroy2=".$show[$i]['id']."'><b class='error'>X</b></a></td>";
        echo "<tr>";
      }
    }
  }
} // Ende unerforschte Gebäude anzeigen

echo "</table><p>";

echo "<table cellspacing='1' cellpadding='0' border='0'>";

echo "<tr><td width='300' class='tblhead' colspan='4'><b>Aktuelle Bauten in ".$_SESSION['cities']->activecityname.":</a></b></td></tr>";

$ib=$_SESSION['cities']->getInBuild();

if (sizeof($ib)==0) {

  echo "<tr><td class='tblbody' width='300'>es existieren derzeit keine Aufträge</td></tr>";

}

for($i=0;$i<sizeof($ib);++$i) {

  $remaining=$ib[$i]['time']-time();

  echo "<tr>";

  echo "<td class='tblbody' width='15' align='center'>".$ib[$i]['count']."</td>";

  echo "<td class='tblbody' width='175'>".$ib[$i]['name']."</td>";

  echo "<td class='tblbody' width='40' align='center'><span class='noerror' id='".$ib[$i]['bid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$ib[$i]['bid'].");</script></span></td>";

  echo "<td class='tblbody' width='70' align='center'><a target='main' href='buildings.php?stop=".$ib[$i]['bid']."'><b class='error'>abbrechen</b></a></td>";

  echo "</tr>";

}



echo "</table>";

?>

</td>

<td></td>

</tr>

</table>

<? end_page(); ?>