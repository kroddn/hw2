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
* Initial version: Stefan Neubert, Stefan Hasenstab
* 
* Copyright (c) 2003-2007
* Markus Sinner
* 
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/cities.class.php");
include_once("includes/player.class.php");
include_once("includes/util.inc.php");
include_once("includes/session.inc.php");
include_once("includes/banner.inc.php");
include_once("includes/barracks.func.php");

// Der Tick wird für die schöne Anzeige der Zeit gebraucht
define("TICK", getConfig("tick", 1800));


$_SESSION['player']->setActivePage(basename(__FILE__));

if ($setweapons) {
  $_SESSION['cities']->updateWeapons(intval($max_shortrange), intval($max_longrange), intval($max_armor), intval($max_horse));
}

if ($buildunit) {
  $error = $_SESSION['cities']->recruit($unit,intval($unitcount));
  $show = 2;
}
if ($stop) {
  $error = $_SESSION['cities']->abortUnit($stop);
  $show = 2;
}

if (isset($_REQUEST['from'])) {
  $from=intval($_REQUEST['from']);
}
else {
  $from=$_SESSION['cities']->getActiveCity();
}

if (isset($_REQUEST['disarm'])) {  
  $error = $_SESSION['cities']->disarmCityUnits($from, $unit);
  $show = 4;
}


start_page();
?>

<script language="JavaScript" type="text/javascript">
function showhide_barracks(id) {
	for(i = 1; i<=4; i++) {
		if(document.getElementById("tbl"+i)) {
			document.getElementById("tbl"+i).style.display = "none";
		}
	}
	if(id==2) {
		document.getElementById("tbl2").style.display = "inline";
		document.getElementById("tbl3").style.display = "inline";
	}
	else {
		document.getElementById("tbl"+id).style.display = "inline";
	}
}
</script>
<?
start_body();

if ($error!=null && strlen($error) > 0) {
  echo "<h1 class='error'>Es ist ein Fehler aufgetreten: ".$error."</h1>\n";
}

?>
<table style="width:650px; margin-bottom:1px;" class="tblhead"><tr>
<td style="padding-left:4px; padding-top:4px; padding-bottom:4px; margin-bottom:1px; font-weight:bold;" ><?php echo getReliImage($_SESSION['cities']->getACReligion())." <span style=\"font-size: 16px;\">".$_SESSION['cities']->activecityname."</font>"; ?></td>
</tr></table>
<div style="width:650px;">
 <div class="tblbody" style="text-align:center; float:left; width:20%;"><a href="#" onclick="showhide_barracks('1');">Waffenproduktion</a></div>
 <div class="tblbody" style="text-align:center; margin-left:1px; float:left; width:20%;"><a href="#" onclick="showhide_barracks('2');">Truppenausbildung</a></div>
 <div class="tblbody" style="text-align:center; margin-left:1px; float:left; width:20%;"><a href="#" onclick="showhide_barracks('4');">Truppen entlassen</a></div>
 <div class="tblbody" style="text-align:center; margin-left:1px; float:left; width:20%;"><a target="_blank" href="fightsim.php">Kampfsimulator</a></div>
 <div style="clear:left" />

<form action="<? echo $PHP_SELF; ?>" method="POST" name="res" id="res">
<table id="tbl1" cellspacing="1" cellpadding="0" border="0" width="550" style="display:none;">
<tr><td class="tblhead" colspan="7" style="padding-top:4px; padding-bottom:4px;"><b>Waffenproduktion in <?php echo $_SESSION['cities']->activecityname; ?></b></td></tr>
<tr class="tblhead" style="font-weight:bold;">
	<td width="20">&nbsp;</td>
	<td style="text-align:center;">Kosten</td>
	<td width="60" style="text-align:center;">im Lager</td>
	<td width="90" style="text-align:center;">Prod. Grenze</td>
	<td width="85" style="text-align:center;">max. Lager</td>
	<td width="90" style="text-align:center;">Zuwachs/Tick</td>
	<td width="95" style="text-align:center;">Lager voll in:</td>
</tr>

<?php
$data=$_SESSION['cities']->getWeapons();

if($data['shortrange'] == 0)         { $data['shortrange'] = "<span style=\"color:red;\">".$data['shortrange']."</span>"; }
if($data['longrange'] == 0)          { $data['longrange'] = "<span style=\"color:red;\">".$data['longrange']."</span>"; }
if($data['armor'] == 0)              { $data['armor'] = "<span style=\"color:red;\">".$data['armor']."</span>"; }
if($data['horse'] == 0)              { $data['horse'] = "<span style=\"color:red;\">".$data['horse']."</span>"; }
if($data['resshort'] == 0)           { $data['resshort'] = "<span style=\"color:red;\">".$data['resshort']."</span>"; }
if($data['reslong'] == 0)            { $data['reslong'] = "<span style=\"color:red;\">".$data['reslong']."</span>"; }
if($data['resarmor'] == 0)           { $data['resarmor'] = "<span style=\"color:red;\">".$data['resarmor']."</span>"; }
if($data['reshorse'] == 0)           { $data['reshorse'] = "<span style=\"color:red;\">".$data['reshorse']."</span>"; }
if($data['reserve_shortrange'] != 0) { $marketshortrange = "(*".$data['reserve_shortrange'].")"; }
if($data['reserve_longrange'] != 0)  { $marketlongrange = "(*".$data['reserve_longrange'].")"; }
if($data['reserve_armor'] != 0)      { $marketarmor = "(*".$data['reserve_armor'].")"; }
if($data['reserve_horse'] != 0)      { $markethorse = "(*".$data['reserve_horse'].")"; }

echo "<tr><td class='tblbody'><img src='".$imagepath."/sword.gif' alt='Schwert'></td><td align='right' class='tblbody'>".SHORTRANGE_COST." <img src='".$imagepath."/iron2.gif'></td><td class='tblbody' style=\"text-align:right; padding-right:8px;\"><b>".$data['shortrange']."</b></td><td class='tblbody'><input type='text' name='max_shortrange' value='".$data['max_shortrange']."' size='3' style=\"width:100%;\"></td><td align='left' class='tblbody'><b class='pos'><a style='color: blue;' onClick='res.max_shortrange.value=".$data['storagelimit']."; return false;'>".$data['storagelimit']."</a> ".$marketshortrange."</b></td><td align='center' class='tblbody'><b>".$data['resshort']."</b></td><td align='right' class='tblbody'>";
if ($data['resshort']>0 && is_numeric($data['shortrange'])) 
  echo "ca. ".max(0,ceil( (TICK/3600)*(($data['max_shortrange']-$data['reserve_shortrange'])-$data['shortrange'])/$data['resshort'])).":00"; 
else 
  echo "-";
echo " <img src='".$imagepath."/time.gif'></td></tr>";

echo "<tr><td class='tblbody'><img src='".$imagepath."/bow.gif' alt='Bogen'></td><td align='right' class='tblbody'>".LONGRANGE_COST." <img src='".$imagepath."/wood2.gif'></td><td class='tblbody' style=\"text-align:right; padding-right:8px;\"><b>".$data['longrange']."</b></td><td class='tblbody'><input type='text' name='max_longrange' value='".$data['max_longrange']."' size='3' style=\"width:100%;\"></td><td align='left' class='tblbody'><b class='pos'><a style='color: blue;' onClick='res.max_longrange.value=".$data['storagelimit']."'; return false;>".$data['storagelimit']."</a> ".$marketlongrange."</b></td><td align='center' class='tblbody'><b>".$data['reslong']."</b></td><td align='right' class='tblbody'>";
if ($data['reslong']>0 && is_numeric($data['longrange'])) 
  echo "ca. ".max(0,ceil( (TICK/3600)*(($data['max_longrange']-$data['reserve_longrange'])-$data['longrange'])/$data['reslong'])).":00"; 
else
  echo "-";
echo " <img src='".$imagepath."/time.gif'></td></tr>";

echo "<tr><td class='tblbody'><img src='".$imagepath."/armor.gif' alt='Rüstung'></td><td align='right' class='tblbody'>".ARMOR_COST." <img src='".$imagepath."/iron2.gif'></td><td class='tblbody' style=\"text-align:right; padding-right:8px;\"><b>".$data['armor']."</b></td><td class='tblbody'><input type='text' name='max_armor' value='".$data['max_armor']."' size='3' style=\"width:100%;\"></td><td align='left' class='tblbody'><b class='pos'><a style='color: blue;' onClick='res.max_armor.value=".$data['storagelimit']."; return false;'>".$data['storagelimit']."</a> ".$marketarmor."</b></td><td align='center' class='tblbody'><b>".$data['resarmor']."</b></td><td align='right' class='tblbody'>";
if ($data['resarmor']>0 && is_numeric($data['armor'])) 
  echo "ca. ".max(0,ceil( (TICK/3600)*(($data['max_armor']-$data['reserve_armor'])-$data['armor'])/$data['resarmor'])).":00"; 
else
  echo "-";
echo " <img src='".$imagepath."/time.gif'></td></tr>";

echo "<tr><td class='tblbody'><img src='".$imagepath."/horse.gif' alt='Pferd'></td><td align='right' class='tblbody'>".HORSE_COST." <img src='".$imagepath."/gold2.gif'></td><td class='tblbody' style=\"text-align:right; padding-right:8px;\"><b>".$data['horse']."</b></td><td class='tblbody'><input type='text' name='max_horse' value='".$data['max_horse']."' size='3' style=\"width:100%;\"></td><td align='left' class='tblbody'><b class='pos'><a style='color: blue;' onClick='res.max_horse.value=".$data['storagelimit']."; return false;'>".$data['storagelimit']."</a> ".$markethorse."</b></td><td align='center' class='tblbody'><b>".$data['reshorse']."</b></td><td align='right' class='tblbody'>";
if ($data['reshorse']>0 && is_numeric($data['horse'])) 
  echo "ca. ".max(0,ceil( (TICK/3600)*(($data['max_horse']-$data['reserve_horse'])-$data['horse'])/$data['reshorse'])).":00"; 
else 
  echo "-";
echo " <img src='".$imagepath."/time.gif'></td></tr>";
?>

<tr class="tblbody"><td colspan="7">*) <i>Anzahl ist f&uuml;r eingehende Marktangebote reserviert</i>
</td></tr>
<tr class="tblhead"><td colspan="7" style="text-align:center;"><input style="margin-top:5px; margin-bottom:5px;" type="submit" name="setweapons" value=" Produktionsgrenze aktualisieren "><p><a href="library.php?s1=0&s2=3&s3=2">Wie zum Geier kann ich Waffen produzieren?</a></td></tr>
</table>
</form>

<table id="tbl2" width="550" cellspacing="1" cellpadding="0" border="0" style="display:none; margin-top:10px;">
<tr><td class="tblhead" colspan="12"><b>Truppenausbildung</b></td></tr>
<tr class="tblhead" style="font-weight:bold; text-align:center;">
	<td>&nbsp;</td>
	<td>Lvl</td>
	<td colspan="5">Kosten</td>
	<td>Dauer</td>
	<td>Ress f&uuml;r max.</td>
	<td>Stationiert</td>
	<td colspan="2">Ausbilden</td>
</tr>
<?
if(!(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY)) {
  if($_SESSION['cities']->getACReligion() != $_SESSION['player']->getReligion()) {
    echo "<tr><td class=\"tblbody\" width=\"140\" colspan=\"12\">Heidnische Stadt! Konvertiert die Einwohner im Rathaus.</td></tr>";
  }
}

$units=$_SESSION['cities']->getUnits();
if (sizeof($units)==0) {
  echo "<tr><td class=\"tblbody\" width=\"140\" colspan=\"12\">Keine Ausbildung möglich</td></tr>";
}

$nobarracks = true;

for($i=0;$i<sizeof($units);++$i) {
  $possible = max(0, $units[$i]['possible']);
  
  // FIXME: Durch das max wird verhindert, dass dem Benutzer hier negative Werte angezeigt werden
  // Dieser "Bug" m�sste eigentlich in cities.class.php gefixt werden.
  $maxpossible = max(0, $units[$i]['maxpossible']);
  
  if ($maxpossible>0) $nobarracks = false;
  
  $img = getUnitImage($units[$i]);
  $href= getUnitLibLink($units[$i]);

  echo "<form action=".$PHP_SELF." method='POST'>";
  printf('<tr class="tblbody"><td nowrap width="100"><a href="%s" target="_blank"><img src="%s/%s" alt="%s" title="%s" border="0"> %s</a></td>',
         $href, $GLOBALS['imagepath'], $img, $units[$i]['name'], $units[$i]['name'], $units[$i]['name']);
  
  echo "\n <td align='center' width='15'><b>L".$units[$i]['level']."</b></td>";
  
  echo "\n <td align='right' width='40'>".$units[$i]['gold']." <img src='".$imagepath."/gold2.gif'></td>\n";
  // Waffenkosten. Je nach Menge die entsprechende Anzahl an Symbolen ausgeben (0-4 Icons)
  echo "\n <td align='right'>\n";
  for ($j=0;$j<$units[$i]['shortrange'];++$j) echo "<img src='".$imagepath."/sword.gif' alt='Schwert'>";
  echo "</td>";
  echo "\n <td align='right'>";
  for ($j=0;$j<$units[$i]['longrange'];++$j) echo "<img src='".$imagepath."/bow.gif' alt='Bogen'>";
  echo "</td>\n";
  echo "<td align='right'>";
  for ($j=0;$j<$units[$i]['armor'];++$j) echo "<img src='".$imagepath."/armor.gif' alt='Rüstung'>";
  echo "</td>\n";
  echo "<td align='right' width='10'>";
  for ($j=0;$j<$units[$i]['horse'];++$j) echo "<img src='".$imagepath."/horse.gif' alt='Pferd'>";
  echo "</td>";
  
  echo "<td align='right' width='45'>".formatTime($units[$i]['time']/RECRUITSPEED)." <img src='".$imagepath."/time.gif'></td>\n";
  echo "<td align='right' width='80'>";
  echo "<a class='pos' onclick=\"javascript:document.getElementById('unit".$units[$i]['id']."').value = '".$possible."'; return true;\" >";
  echo $possible." (".$maxpossible.")</a></td>";
  
  $res1 = do_mysqli_query("SELECT count FROM cityunit WHERE unit=".$units[$i]['id']." AND city=".$_SESSION['cities']->getActiveCity()." AND owner=".$_SESSION['player']->getID());
  $data1 = mysqli_fetch_assoc($res1);
  echo "<td align='right' width='40'><b>";
  if ($data1['count']) {
    echo $data1['count'];
  }
  else {
    echo "0";
  }
  echo "</b></td>\n";
  echo "<td align='right' width=\"60\"><input type='hidden' name='buildunit' value='true'><input type='hidden' name='unit' value='".$units[$i]['id']."'><input id='unit".$units[$i]['id']."' type='text' name='unitcount' size='3' style=\"width:100%;\"></td>";
  echo "<td align='right' width=\"10\"><input class='noborder' type='image' src='".$imagepath."/arrow.gif'></td>";
  echo "</tr>\n";
  echo "</form>";
}

if ($nobarracks) {
  echo '<tr><td class="tblbody" colspan="12" align="center" class="error"><b>Ihr m�sst zuerst (weitere) Kasernen errichten!</b></td></tr>';
}

$costFactor = getLoyalityCostFactor( round($_SESSION['cities']->getACLoyality()/100));
if ( $costFactor > 1 ) {
  printf('<tr><td class="tblhead" colspan="12" align="center" style="color: #992020; font-weight: bold; ">Diese Stadt ist <u>%s</u>! Die Kosten für Rekrutierungen betragen das <u>%d-fache</u>.<br>Die Mehrkosten bekommt Ihr nicht zurückerstattet, falls Ihr die Rekrutierung abbrecht.</td></tr>', 
         get_loyality_string( round($_SESSION['cities']->getACLoyality()/100) ), $costFactor);
}

if (defined("RECRUITSPEED") && RECRUITSPEED > 1) {
  echo '<tr><td class="tblhead_22" colspan="12" align="center"><font color="blue">Ausbildung um Faktor '.RECRUITSPEED.' beschleunigt!</font></td></tr>';
}

echo "</table>\n";

echo "<p>";
echo "<table id=\"tbl3\" width=\"550\" cellspacing='1' cellpadding='0' border='0' style=\"display:none; margin-top:10px;\">";
echo "<tr><td class='tblhead' colspan='4'><b>Laufende Ausbildung in ".$_SESSION['cities']->activecityname.":</a></b></td></tr>";
$ir=$_SESSION['cities']->getInRecruit();
if (sizeof($ir)==0) {
	echo "<tr><td class='tblbody' colspan='4'>es werden derzeit keine Truppen ausgebildet</td></tr>";
}
else {
  for($i=0;$i<sizeof($ir);++$i) {
    $remaining=$ir[$i]['time']-time();
    echo "<tr>";
    echo "<td class='tblbody' width='15' align='center'>".$ir[$i]['count']."</td>";
    echo "<td class='tblbody' width='140'>".$ir[$i]['name']."</td>";
    echo "<td class='tblbody' width='40' align='center'><span class='noerror' id='".$ir[$i]['uid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$ir[$i]['uid'].");</script></span></td>";
    echo "<td class='tblbody' width='70' align='center'><a target='main' href='barracks.php?stop=".$ir[$i]['uid']."'><b class='error'>abbrechen</b></a></td>";
    echo "</tr>";
  }
}

if(!is_premium_noads()) {
?>
<tr><td class="tblhead" colspan="4" align="center" height="12"></td></tr>
<tr><td class="tblbody" colspan="4" align="center" heigth="">

<!-- Textlink -->
<?php //<script type="text/javascript" src="http://www.sponsorads.de/script.php?s=6869"></script> ?>
</td></tr>
<? 
} // if(!is_premium_noads()) 
?>
</table>

<div id="tbl4" style="width: 660px; display: none; margin-top: 10px;">
<?php barracks_disarm_table( $_SESSION['cities']->getActiveCity() ); ?>
</div>

<?php
if($old_code) {
  // Code deaktiviert
  $res1 = do_mysqli_query("SELECT unit.id AS uid,unit.name AS uname,city.name AS cname, unit.cost as cost,count,type,unit.level AS level,unit.religion AS religion ".
                       " FROM cityunit,unit,city ".
                       " WHERE unit.id=cityunit.unit AND city.id=cityunit.city AND cityunit.city=".$_SESSION['cities']->getActiveCity()." AND cityunit.owner=".$_SESSION['player']->getID().
                       " ORDER BY cityunit.unit");

  if (mysqli_num_rows($res1)>0) {
    //mysqli_data_seek($res1, 0);
    echo "<form action=".$PHP_SELF.' method="GET" name="disarmform">';
    echo '<table id="tbl4" cellspacing="1" cellpadding="0" border="0" width="550" style="margin-top:10px; display:none;">';
    echo "<tr class=\"tblhead\"><td colspan=\"4\"><strong>Einheiten entlassen</strong></td></tr>";
    echo '<input type="hidden" name="from" value="'.$from.'">';
    echo '<tr class="tblhead">';
    echo '<td width="150">Typ</td>';
    echo '<td width="70" style="text-align:center;">Kosten / Tick</td>';
    echo '<td width="40">&nbsp;</td>';
    echo '<td width="40">Stationiert</td>';
    echo '</tr>';
    $sumcount = 0;
    $sumcost = 0;
    $num = 1;
    while ($data1 = mysqli_fetch_assoc($res1)) {
      $img = getUnitImage($data1);
      $href= getUnitLibLink($data1);

      //echo '<form action="'.$PHP_SELF.'" method="POST">';
      printf('<tr class="tblbody"><td><a href="%s" target="_blank"><img src="%s/%s" alt="%s" title="%s" border="0"> %s</a></td>',
      $href, $GLOBALS['imagepath'], $img, $data1['uname'], $data1['uname'], $data1['uname']);

      //echo "<td><a href=\"library.php?s1=2&s2=0&s3=".($data1['uid']-1)."\">".$data1['uname']."</a></td>"
      echo "\n<td style=\"text-align:right; padding-right:15px;\">".number_format(($data1['cost']*$data1['count']),2,",",".")."</td>\n";
      echo "<td><input type='text'  tabindex='".$num."' name='unit[".$data1['uid']."]' size='8'></td><td style=\"text-align:right; padding-right:15px;\">".$data1['count']."</td>\n";
      echo "</tr>\n";
      //echo "</form>";

      $sumcount += $data1['count'];
      $sumcost += ($data1['cost']*$data1['count']);
      $num++;
    }
    echo "<tr class='tblhead'><td></td><td style=\"font-weight:bold; text-align:right; padding-right:15px;\">".number_format($sumcost,2,",",".")."</td><td></td><td style=\"font-weight:bold; text-align:right; padding-right:15px;\">".$sumcount."</td></tr>";
    echo '<tr class="tblbody"><td colspan="4" style="text-align:center;"><input style="margin-bottom:5px; margin-top:5px;" type="submit" name="disarm" value="Truppen entlassen">';
    echo "</td></tr>";
    echo "</table>";
    echo "</form>";
  }
  else {
    echo '<table id="tbl4" cellspacing="1" cellpadding="0" border="0" width="400" style="margin-top:10px; display:none;">';
    echo "<tr class=\"tblhead\"><td colspan=\"4\"><strong>Truppen entlassen</strong></td></tr>";
    echo "<tr class=\"tblbody\"><td colspan=\"4\" style=\"text-align:center;\"><br /><strong style=\"color:red\">In dieser Stadt sind keine Truppen stationiert!</strong><br /><br /></td></tr>";
    echo "</table>\n";
  }
}



if(!isset($show)) {
  $show = 1;
}
?>

<script type="text/javascript" language="JavaScript">
<!--
showhide_barracks('<?php  echo $show; ?>');
-->
</script>

<? end_page(); ?>
