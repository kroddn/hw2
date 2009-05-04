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
  $error = $cities->settle(intval($coordx),intval($coordy),intval($settler), $sg);
}
if (isset($setpoplimit)) {
  $cities->updatePopulationlimit(intval($poplimit));
}
if ($setcityname) {
  $error = $cities->updateCityName($cityname);  
}

if (isset($ratio)) {
  $cities->convert($ratio);
}

// Religion stimmt nicht...
if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY && $_SESSION['player']->getReligion() != $cities->getACReligion() ) {
  // Religions-Geb�ude �berpr�fen, die in dieser Stadt gebaut sind
  $sql = sprintf("SELECT convert_loyality,name FROM citybuilding LEFT JOIN building ON building.id = citybuilding.building".
  " WHERE city = %d AND convert_loyality IS NOT NULL AND religion = %d ".
  " ORDER BY convert_loyality ASC", $_SESSION['cities']->getActiveCity(), $_SESSION['player']->getReligion() ) ;
  $res_convert_loyality = do_mysql_query($sql);
  $num_convert_buildings = mysql_num_rows($res_convert_loyality);

  $data = do_mysql_query_fetch_assoc("SELECT loyality FROM city WHERE id = ".$_SESSION['cities']->getActiveCity());
  $current_loyality = $data ? $data['loyality'] : 0;

  if($num_convert_buildings > 0) {
    $loy = mysql_fetch_assoc($res_convert_loyality);
    $has_convert_building = true;
    $has_convert_loyality = $current_loyality >= $loy['convert_loyality'];   
  }
  else {
    $has_convert_building = false;
    $has_convert_loyality = false;
  }
  
  if($_REQUEST['do_convert']) {
    if(!$has_convert_building) {
      $error = "Ihr besitzt kein Geb�ude, das Euch eine Konvertierung erlauben w�rde!";
    }
    else if(!$has_convert_loyality) {
      $error = "Eure Einwohner sind noch nicht loyal genug, um zu konvertieren.";
    }
    else {
      $error = $cities->convert(100);
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
  echo "<h3>Ihre Siedler befinden sich auf dem Weg zur Gr�ndung einer neuen Siedlung.</h3><a href=\"general.php\">Zum Generalstab</a>.<p>\n";
}

if ($inform!=null && strlen($inform) > 0) {
  echo "<h3 class='inform'>".$inform."</h3>\n";
}

$maxcities = $fpcost[get_adm_level($player->getID())][0];
$act       = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM city WHERE owner = ".$player->getID() );
$actcities = $act['c'];
$sett      = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM army WHERE owner = ".$player->getID()." AND mission='settle'");
$settlers  = $sett['c'];

$cd=$cities->getCityData();

$res1=do_mysql_query("SELECT sum(citybuilding.count * building.res_attraction) AS attr,sum(citybuilding.count * building.res_food) AS incfood,city.id AS id,city.food AS food FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.id = ".$cd['id']." GROUP BY id");
$data1 = mysql_fetch_assoc($res1);
//MYSQLd abh�nige �nderung -> Vgl. statistic.inc.php
$data1['attr'] +=1000;
?>
<table width="500" cellspacing="1" cellpadding="0" border="0">
  <tr height="32">
    <td class="tblhead" style="font-size: 16px"><? echo getReliImage($_SESSION['cities']->getACReligion()); ?>
    Stadt <b><?php echo $cd['name']; ?></b></td>
  </tr>
</table>
<br />

<form action="<? echo $PHP_SELF; ?>" method="POST">
<table width="500" cellspacing="1" cellpadding="1" border="0">
  <tr>
    <td colspan="4" class="tblhead"><b>�bersicht</b></td>
  </tr>
  <tr>
    <td class="tblbody" width="150">Einwohner</td>
    <td class="tblbody" align="right" width="50"><b><? echo $cd['population']?></b></td>
    <td class="tblbody">Momentane Attraktivit&auml;t:</td>
    <td class="tblbody" style="text-align: right; font-weight: bold;"><? echo $data1['attr']; ?></td>
  </tr>
  <tr>
    <td class="tblbody" width="150">-> davon verf�gbar</td>
    <td class="tblbody" align="right" width="50"><b><? echo $cd['apopulation']?></b></td>
    <td class="tblbody" width="150">Nahrungsproduktion:</td>
    <td class="tblbody" style="text-align: right; font-weight: bold;"><? echo $data1['incfood']; ?></td>
  </tr>
  <tr>
    <td colspan="4" class="tblhead" width="200"><b>St�dtisches
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

<form name="newsettle" action="<? echo $PHP_SELF; ?>" method="GET">
<table cellspacing="1" width="500" cellpadding="0" border="0">
  <tr>
    <td colspan="2" class="tblhead"><b>Neue Stadt gr�nden</b></td>
  </tr>
  <?
  if(defined("START_POS_NEW") && START_POS_NEW) {
    printf('<tr><td colspan="2" class="tblbody">Die <font color="#FF0000">Regeln zum Gr�nden neuer St�dte</font> wurden ge�ndert. Weiter Informationen <a href="library.php?topic=Stadtgr">hier in der Bibliothek</b>.</td></tr>');
  }
  $ackerbau = do_mysql_query("SELECT * FROM playerresearch WHERE player=".$_SESSION['player']->getID()." AND research=5");

  if(mysql_num_rows($ackerbau) == 0) {
    echo "<tr><td colspan='2' class='tblbody' style='padding: 10px;'>Ihr m��t zun�chst <b>Ackerbau erforschen</b>, um neue Siedlungen errichten zu k�nnen. <a href='research.php'>Hier gehts zur Forschung</a>.</td></tr>";
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
    echo "\n<tr><td colspan='2' class='tblbody' style='font-size: 9px; padding-left: 10px; '>Siedler verbrauchen <b>Nahrung</b>, genauso wie B�rger.<br>Bei Nahrungsmangel sterben erst B�rger und dann Siedler</td></tr>";
    echo "<tr><td colspan='2' class='tblhead'><b>Begleitschutz</b></td></tr>";
    $guards=$cities->getCityUnits();
    if (sizeof($guards)==0) {
      echo "<tr><td colspan='2' class='tblbody'>(kein Begleitschutz m�glich)</td></tr>";
    }
    else {
      // Java-Skript-Code f�r Unit-Speed
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


    echo "<tr><td class='tblbody'>Mit Ihrer aktuellen Verwaltungsstufe k�nnen Sie $maxcities St�dte 100% effektiv verwalten. Sie haben $actcities".($settlers>0 ? " und gr�nden $settlers neue St�dte!" : ".")."</td><td class='tblbody' align='left'><input type='submit' name='settle' value=' aufbrechen ' ".
        // Schutz einbauen dass Noobs nicht aus versehen zuviel Siedeln
    ($actcities + $settlers >= $maxcities
    ? 'onClick="return confirm(\'Eine weitere Stadt w�rde die Effektivit�t Ihrer Verwaltung schm�lern. Genaueres k�nnt Ihr in der Bibliothek unter Spielprinzipien -> St�dte -> Verwaltung nachlesen.'.
    "\\n\\n".'Seid Ihr sicher, dass ihr noch eine Stadt gr�nden wollt?\')"'
         : "").
    "></td></tr>";
}
else {
  echo "<tr><td colspan='2' style='padding: 10px;' class='tblbody'>nicht m�glich, <b>nicht gen�gend Einwohner</b> (mindestens 50 ben�tigt)</td></tr>";
}
?>
</table>
</form>

<?php
if ($_SESSION['player']->getReligion() != $cities->getACReligion()) {
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
  zur�ckzuf�hren. Abh�ngig von der Loyalit�t der hiesigen Einwohner
  (derzeit <b><? echo round($current_loyality/100); ?>%</b>) wird es 
  m�glich, sie zu konvertieren. Mit etwas Gl�ck gewinnt Ihr durch die
  Konvertierung sogar religi�se Anh�nger, die mit Euch in die 
  Schlacht ziehen wollen.
  
  </td>
</tr>
    <?
    //$possible_loy_buildings = do_mysql_query("SELECT * FROM building WHERE religion = ".$_SESSION['player']->getReligion()." AND convert_loyality IS NOT NULL ORDER BY convert_loyality DESC");

    if($has_convert_building) {
      printf('<tr class="tblbody"><td colspan="2">Ihr <b>ben�tigt %s %d%% Loyalit�t</b> zum Konvertieren dieser Stadt, %s &quot;%s&quot;.<br>', 
      $has_convert_loyality ? "nur" : "<font color=\"red\"><u>erst</u></font>", round($loy['convert_loyality']/100),
      $has_convert_loyality ? "dank Eures Geb�udes" : "erm�glicht durch Euer Geb�ude", $loy['name']);
       
      if($has_convert_loyality) {
        echo "<br>\n";
        echo '<form action="'.$PHP_SELF.'" method="POST"><input name="do_convert" type="submit" value=" Konvertieren " onClick="return confirm(\'Sind Sie sicher?\')"></form>';
      }
      
      if(!$has_convert_loyality)  {
        $possible_loy_buildings = do_mysql_query("SELECT * FROM building WHERE religion = ".$_SESSION['player']->getReligion()." AND convert_loyality < ".$loy['convert_loyality']." ORDER BY convert_loyality DESC");
        if(mysql_num_rows($possible_loy_buildings) > 0) {
          echo " Ihr k�nnt den Wert Senken, indem Ihr Folgendes errichtet:\n";
          $res = $possible_loy_buildings;
        }
      }
       
    }
    else {
      printf('<tr class="tblbody"><td colspan="2">Ihr k�nnt die Einwohner dieser Stadt nicht konvertieren. Baut zun�chst eines der Geb�ude:');
      $res = do_mysql_query("SELECT * FROM building WHERE religion = ".$_SESSION['player']->getReligion()." AND convert_loyality IS NOT NULL ORDER BY convert_loyality DESC");
    }

    if($res) {
      echo "<ul>";
      while($b = mysql_fetch_assoc($res)) {
        printf("\n<li><b>%s</b>, konvertieren ab %d%% Loyalit�t m�glich.", $b['name'], round($b['convert_loyality']/100));
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
  Gold pro Einwohner, der Rest k�mpft gegen die Stadtbewachung)</td>
</tr>
<tr>
  <td class="tblbody">
  <form action="<? echo $PHP_SELF; ?>" method="POST">
  <input type="text"
         name="ratio" value="" size="3">% <input type="submit"
         value=" Konvertieren "
         onClick="return confirm(\'Sind Sie sicher?\')"></form>
  </td>
</tr>
  <?
} // keine LOYALIT�T
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
 <form action="<? echo $PHP_SELF; ?>" method="GET" >
  <tr>
    <td class="tblhead" width="150"><b>Stadtname</b></td>
    <td class="tblbody" align="right" width="175"><input type='text'
      name='cityname' value='<? echo $cd['name']?>' style='width: 100%;'
      maxlength="50"></td>
    <td width="75" class="tblbody"><input type="submit"
      name="setcityname" value=" �ndern " style="width: 100%;"></td>
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
