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
 
/**************************************************
 * Copyright (c) 2003-2005
 *
 * Stefan Neubert, Stefan Hasenstab
 * Markus Sinner <sinner@psitronic.de>
 *
 * This File must not be used without permission!
 ***************************************************/

include_once("includes/config.inc.php");
include_once("includes/db.config.php");
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
include_once("includes/cities.class.php");
include_once("includes/map.class.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");
include_once("includes/ressources.inc.php");

$player->setActivePage(basename(__FILE__));

if ($settle) {
  $error = $_SESSION['cities']->settle(intval($coordx),intval($coordy),intval($settler), $sg);
}
if (isset($setpoplimit)) {
  $_SESSION['cities']->updatePopulationlimit(intval($poplimit));
}
if ($setcityname) {
  $error = $_SESSION['cities']->updateCityName($cityname);  
}

if (isset($ratio)) {
  $_SESSION['cities']->convert($ratio);
}

// Religion stimmt nicht...
if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY && $_SESSION['player']->getReligion() != $_SESSION['cities']->getACReligion() ) {
  // Religions-Gebäude überprüfen, die in dieser Stadt gebaut sind
  $sql = sprintf("SELECT convert_loyality,name FROM citybuilding LEFT JOIN building ON building.id = citybuilding.building".
  " WHERE city = %d AND convert_loyality IS NOT NULL AND religion = %d ".
  " ORDER BY convert_loyality ASC", $_SESSION['cities']->getActiveCity(), $_SESSION['player']->getReligion() ) ;
  $res_convert_loyality = do_mysql_query($sql);
  $num_convert_buildings = mysqli_num_rows($res_convert_loyality);

  $data = do_mysql_query_fetch_assoc("SELECT loyality FROM city WHERE id = ".$_SESSION['cities']->getActiveCity());
  $current_loyality = $data ? $data['loyality'] : 0;

  if($num_convert_buildings > 0) {
    $loy = mysqli_fetch_assoc($res_convert_loyality);
    $has_convert_building = true;
    $has_convert_loyality = $current_loyality >= $loy['convert_loyality'];   
  }
  else {
    $has_convert_building = false;
    $has_convert_loyality = false;
  }
  
  if($_REQUEST['do_convert']) {
    if(!$has_convert_building) {
      $error = "Ihr besitzt kein Gebäude, das Euch eine Konvertierung erlauben würde!";
    }
    else if(!$has_convert_loyality) {
      $error = "Eure Einwohner sind noch nicht loyal genug, um zu konvertieren.";
    }
    else {
      $error = $_SESSION['cities']->convert(100);
      if($error == null)
        $inform = "Siedlung konvertiert!";
    }
  }
}

start_page();
// Jetzt updaten
include_once("includes/update.inc.php");

?>
<style type="text/css">
tr {
  height: 15px;
}
</style>
<?
include("includes/walktime_js.inc.php");
start_body();

if ($error!=null && strlen($error) > 0) {
  if(isset($coordx) && isset($coordy)) {
    $newsettle = 1;
    $x = $coordx;
    $y = $coordy;
  }
  echo "<h1 class='error'>Es ist ein Fehler aufgetreten: ".$error."</h1>\n";
}
else {
  if ($settle)
  echo "<h3>Ihre Siedler befinden sich auf dem Weg zur Gründung einer neuen Siedlung.</h3><a href=\"general.php\">Zum Generalstab</a>.<p>\n";
}

if ($inform!=null && strlen($inform) > 0) {
  echo "<h3 class='inform'>".$inform."</h3>\n";
}

$maxcities = $fpcost[get_adm_level($player->getID())][0];
$act       = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM city WHERE owner = ".$player->getID() );
$actcities = $act['c'];
$sett      = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM army WHERE owner = ".$player->getID()." AND mission='settle'");
$settlers  = $sett['c'];

$cd=$_SESSION['cities']->getCityData();

$res1=do_mysql_query("SELECT sum(citybuilding.count * building.res_attraction) AS attr,sum(citybuilding.count * building.res_food) AS incfood,city.id AS id,city.food AS food FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.id = ".$cd['id']." GROUP BY id");
$data1 = mysqli_fetch_assoc($res1);
//MYSQLd abhänige Änderung -> Vgl. statistic.inc.php
$data1['attr'] +=1000;
?>
<table width="500" cellspacing="1" cellpadding="0" border="0">
  <tr height="32">
    <td class="tblhead" style="font-size: 16px"><? echo getReliImage($_SESSION['cities']->getACReligion()); ?>
    Stadt <b><?php echo $cd['name']; ?></b></td>
  </tr>
</table>
<br />

<form action="<? echo $_SERVER['PHP_SELF'];  ?>" method="POST">
<table width="500" cellspacing="1" cellpadding="1" border="0">
  <tr>
    <td colspan="4" class="tblhead"><b>übersicht</b></td>
  </tr>
  <tr>
    <td class="tblbody" width="150">Einwohner</td>
    <td class="tblbody" align="right" width="50"><b><? echo $cd['population']?></b></td>
    <td class="tblbody">Momentane Attraktivit&auml;t:</td>
    <td class="tblbody" style="text-align: right; font-weight: bold;"><? echo $data1['attr']; ?></td>
  </tr>
  <tr>
    <td class="tblbody" width="150">-> davon verfügbar</td>
    <td class="tblbody" align="right" width="50"><b><? echo $cd['apopulation']?></b></td>
    <td class="tblbody" width="150">Nahrungsproduktion:</td>
    <td class="tblbody" style="text-align: right; font-weight: bold;"><? echo $data1['incfood']; ?></td>
  </tr>
  <tr>
    <td colspan="4" class="tblhead" width="200"><b>Städtisches
    Einwohnerlimit</b></td>
  </tr>
  <tr>
    <td class="tblbody" width="150">Limit (min. 100, max 99999)</td>
    <td class="tblbody" align="right" width="50"><input type='text'
      name='poplimit' value='<? echo $cd['populationlimit']?>' size='3'
      maxlength='5'></td>
    <td colspan="2" class="tblbody"><input type="submit"
      name="setpoplimit" value=" aktualisieren "></td>
  </tr>
</table>
</form>


<?
$maySettle = false;
?>

<form name="newsettle" action="<? echo $_SERVER['PHP_SELF'];  ?>" method="GET">
<table cellspacing="1" width="500" cellpadding="0" border="0">
  <tr>
    <td colspan="2" class="tblhead"><b>Neue Stadt gründen</b></td>
  </tr>
  <?
  if(defined("START_POS_NEW") && START_POS_NEW) {
    printf('<tr><td colspan="2" class="tblbody">Die <font color="#FF0000">Regeln zum Gründen neuer Städte</font> wurden geändert. Weiter Informationen <a href="library.php?topic=Stadtgr">hier in der Bibliothek</b>.</td></tr>');
  }
  $ackerbau = do_mysql_query("SELECT * FROM playerresearch WHERE player=".$_SESSION['player']->getID()." AND research=5");

  if(mysqli_num_rows($ackerbau) == 0) {
    echo "<tr><td colspan='2' class='tblbody' style='padding: 10px;'>Ihr müsst zunächst <b>Ackerbau erforschen</b>, um neue Siedlungen errichten zu können. <a href='research.php'>Hier gehts zur Forschung</a>.</td></tr>";
  }
  else if($cd['apopulation']>=50) {
    echo "<tr><td class='tblbody' width='400'>Koordinaten<br><a class=\"small\" href=\"map.php?grid=1\">(zum Suchen einer geeigneten Stelle hier klicken</a>)</td>\n";
    echo "<td class='tblbody' nowrap width='170'><input type='text' name='coordx' id='coordx' size='4' maxlength='3'".
    ( (isset($newsettle) && isset($x)) ? (" value='".$x."'") : "" ).
    "> : <input type='text' name='coordy' id='coordy' size='4' maxlength='3'".
    ( (isset($newsettle) && isset($y)) ? (" value='".$y."'") : "" )."></td></tr>";
    echo "<tr class='tblbody'><td>Anzahl Siedler (50 bis ".$cd['apopulation'].")</td><td><input style='".(isset($newsettle) ? 'background-color: #FFA0C0;': "")."' type='text' name='settler' id='settler' size='6' maxlength='4' onkeyup='updateTravelTime()' onChange='updateTravelTime()'></td></tr>\n";
    echo '<tr class="tblbody"><td>Zeit bis Ankunft / Speed</td>';
    echo ' <td><input type="text" style="text-align: right" name="traveltime" id="traveltime" value="n/a" size="9" readonly> h / <span id="armyspeed">3</span></td></tr>';
    echo "\n<tr><td colspan='2' class='tblbody' style='font-size: 9px; padding-left: 10px; '>Siedler verbrauchen <b>Nahrung</b>, genauso wie Bürger.<br>Bei Nahrungsmangel sterben erst Bürger und dann Siedler</td></tr>";
    echo "<tr><td colspan='2' class='tblhead'><b>Begleitschutz</b></td></tr>";
    $guards=$_SESSION['cities']->getCityUnits();
    if (!$guards || sizeof($guards)==0) {
      echo "<tr><td colspan='2' class='tblbody'>(kein Begleitschutz möglich)</td></tr>";
    }
    else {
      // Java-Skript-Code für Unit-Speed
      $unitspeeds = "";
      // Achtung: Index geht von 1-size
      $i = 0;
      foreach($guards AS $guard) {
        $unitspeeds .= sprintf("unitspeeds[%d] = %d;\n", $i, $guard[2]);
        echo "  <tr>\n    <td class='tblbody' width='190'>".$guard['name']." (max. ".$guard[1].")</td>\n    <td class='tblbody' align='left'><input type='text' name='sg[".$guard[0]."]' id='unit".$i."' size='8' onkeyup='updateTravelTime()' onChange='updateTravelTime()'></td></tr>\n";
        $i++;
      }
    } // else
      ?>
  <script language="JavaScript">
    <!--
    max_unitspeed = 3; // Siedler Maximal Speed 3
    <? echo $unitspeeds; ?>
    //-->
    </script>
    <?


    echo "<tr><td class='tblbody'>Mit Ihrer aktuellen Verwaltungsstufe können Sie $maxcities Städte 100% effektiv verwalten. Sie haben $actcities".($settlers>0 ? " und gründen $settlers neue Städte!" : ".")."</td><td class='tblbody' align='left'><input type='submit' name='settle' value=' aufbrechen ' ".
        // Schutz einbauen dass Noobs nicht aus versehen zuviel Siedeln
    ($actcities + $settlers >= $maxcities
    ? 'onClick="return confirm(\'Eine weitere Stadt würde die Effektivität Ihrer Verwaltung schmälern. Genaueres könnt Ihr in der Bibliothek unter Spielprinzipien -> Städte -> Verwaltung nachlesen.'.
    "\\n\\n".'Seid Ihr sicher, dass ihr noch eine Stadt gründen wollt?\')"'
         : "").
    "></td></tr>";
}
else {
  echo "<tr><td colspan='2' style='padding: 10px;' class='tblbody'>nicht möglich, <b>nicht genügend Einwohner</b> (mindestens 50 benötigt)</td></tr>";
}
?>
</table>
</form>

<?php
if ($_SESSION['player']->getReligion() != $_SESSION['cities']->getACReligion()) {
  echo "<p>";
  echo '<table cellspacing="1" cellpadding="0" border="0" width="500">';

  if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
    ?>
<tr class="tblhead">
  <td><? echo getReliImage($_SESSION['cities']->getACReligion()); ?></td>
  <td align="center"><b>Heidnische Stadt!</b></td>
</tr>
<tr class="tblbody">
  <td colspan="2">Diese Stadt besitzt einen anderen Glauben als Ihr!
  <p>Eure Aufgabe ist es, die Einwohner zum rechten Glauben
  zurückzuführen. Abhängig von der Loyalität der hiesigen Einwohner
  (derzeit <b><? echo round($current_loyality/100); ?>%</b>) wird es 
  möglich, sie zu konvertieren. Mit etwas Glück gewinnt Ihr durch die
  Konvertierung sogar religißse Anhänger, die mit Euch in die 
  Schlacht ziehen wollen.
  
  </td>
</tr>
    <?
    //$possible_loy_buildings = do_mysql_query("SELECT * FROM building WHERE religion = ".$_SESSION['player']->getReligion()." AND convert_loyality IS NOT NULL ORDER BY convert_loyality DESC");

    if($has_convert_building) {
      printf('<tr class="tblbody"><td colspan="2">Ihr <b>benötigt %s %d%% Loyalität</b> zum Konvertieren dieser Stadt, %s &quot;%s&quot;.<br>', 
      $has_convert_loyality ? "nur" : "<font color=\"red\"><u>erst</u></font>", round($loy['convert_loyality']/100),
      $has_convert_loyality ? "dank Eures Gebäudes" : "ermöglicht durch Euer Gebäude", $loy['name']);
       
      if($has_convert_loyality) {
        echo "<br>\n";
        echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><input name="do_convert" type="submit" value=" Konvertieren " onClick="return confirm(\'Sind Sie sicher?\')"></form>';
      }
      
      if(!$has_convert_loyality)  {
        $possible_loy_buildings = do_mysql_query("SELECT * FROM building WHERE religion = ".$_SESSION['player']->getReligion()." AND convert_loyality < ".$loy['convert_loyality']." ORDER BY convert_loyality DESC");
        if(mysqli_num_rows($possible_loy_buildings) > 0) {
          echo " Ihr könnt den Wert Senken, indem Ihr Folgendes errichtet:\n";
          $res = $possible_loy_buildings;
        }
      }
       
    }
    else {
      printf('<tr class="tblbody"><td colspan="2">Ihr könnt die Einwohner dieser Stadt nicht konvertieren. Baut zunächst eines der Gebäude:');
      $res = do_mysql_query("SELECT * FROM building WHERE religion = ".$_SESSION['player']->getReligion()." AND convert_loyality IS NOT NULL ORDER BY convert_loyality DESC");
    }

    if($res) {
      echo "<ul>";
      while($b = mysqli_fetch_assoc($res)) {
        printf("\n<li><b>%s</b>, konvertieren ab %d%% Loyalität möglich.", $b['name'], round($b['convert_loyality']/100));
      }
      echo "\n</ul>";
    }
    echo "</td></tr>\n";
}
else {
  ?>
<tr>
  <td class="tblhead" align="center"><b>Stadt konvertieren</b></td>
</tr>
<tr>
  <td class="tblbody">Einwohner bekehren (kostet <? echo CONVERT_COST; ?>
  Gold pro Einwohner, der Rest kämpft gegen die Stadtbewachung)</td>
</tr>
<tr>
  <td class="tblbody">
  <form action="<? echo $_SERVER['PHP_SELF'];  ?>" method="POST">
  <input type="text"
         name="ratio" value="" size="3">% <input type="submit"
         value=" Konvertieren "
         onClick="return confirm(\'Sind Sie sicher?\')"></form>
  </td>
</tr>
  <?
} // keine LOYALITÄT
echo "</table><p>";
}


if(isset($newsettle)) {
  ?>
<script language="JavaScript">
<!-- Begin
document.getElementById("settler").focus();
// End -->
</script>
  <?
}
else {
  ?>


<table width="400" cellspacing="1" cellpadding="0" border="0">  
 <form action="<? echo $_SERVER['PHP_SELF'];  ?>" method="GET" >
  <tr>
    <td class="tblhead" width="150"><b>Stadtname</b></td>
    <td class="tblbody" align="right" width="175"><input type='text'
      name='cityname' value='<? echo $cd['name']?>' style='width: 100%;'
      maxlength="50"></td>
    <td width="75" class="tblbody"><input type="submit"
      name="setcityname" value=" ändern " style="width: 100%;"></td>
  </tr>
 </form>
</table>


<table width="400" cellspacing="1" cellpadding="0" border="0">
  <form action="info.php" method="POST">
  
  
  <tr>
    <td class="tblhead" width="150"><b>Spieler suchen</b></td>
    <td class="tblbody" align="right" width="175"><input type='text'
      name='name' value='' style='width: 100%;'></td>
    <td width="75" class="tblbody"><input type="hidden" name="show"
      value="player"><input type="submit" value=" suchen "
      style="width: 100%;"></td>
  </tr>
  </form>
</table>

<table width="400" cellspacing="1" cellpadding="0" border="0">
  <form action="info.php" method="POST">
  
  
  <tr>
    <td class="tblhead" width="150"><b>Stadt suchen</b></td>
    <td class="tblbody" align="right" width="175"><input type='text'
      name='name' value='' style='width: 100%;'></td>
    <td width="75" class="tblbody"><input type="hidden" name="show"
      value="town"><input type="submit" value=" suchen "
      style="width: 100%;"></td>
  </tr>
  </form>
</table>

<table width="400" cellspacing="1" cellpadding="0" border="0">
  <form action="info.php" method="POST">
  
  
  <tr>
    <td class="tblhead" width="150"><b>Orden suchen</b></td>
    <td class="tblbody" align="right" width="175"><input type='text'
      name='name' value='' style='width: 100%;'></td>
    <td width="75" class="tblbody"><input type="hidden" name="show"
      value="clan"><input type="submit" value=" suchen "
      style="width: 100%;"></td>
  </tr>
  </form>
</table>

<table width="400" cellspacing="1" cellpadding="0" border="0">
  <form action="map.php" method="GET">
  
  
  <tr>
    <td class="tblhead" width="150"><b>Koordinaten suchen</b></td>
    <td class="tblbody" width="175"><input type='text' name='gox'
      value='' size='3' maxlength='3'>&nbsp;<input type='text'
      name='goy' value='' size='3' maxlength='3'> <input type="checkbox"
      name="grid" value="1">Gitter?</td>
    <td width="75" class="tblbody"><input type="submit" value=" suchen "
      style="width: 100%;"></td>
  </tr>
  </form>
</table>
<? } ?>
<br>
<br>
<? end_page(); ?>
