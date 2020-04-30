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
 * Stefan Neubert, Stefan Hasenstab, Laurenz Gamper
 *													*
 * This File must not be used without permission	!
 ***************************************************/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/player.class.php");
include_once("includes/diplomacy.common.php");
include_once("includes/diplomacy.class.php");
include_once("includes/session.inc.php");
include_once("includes/banner.inc.php");

$_SESSION['player']->setActivePage(basename(__FILE__));

if (isset($newRel)) {
  if(isset($playername) && isset($type)) {
    $setrel_error = $diplomacy->changeRelation(trim($playername), $type);
  }
  else {
    $setrel_error = "Ihr habt keinen Namen eingegeben.";
  }
}
else if (isset($delbnd)) {
  if(isset($friend)) {
    $setrel_error = $diplomacy->changeRelation($friend, 1);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($peace)) {
  if(isset($enemy)) {
    $setrel_error = $diplomacy->changeRelation($enemy, 1);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($accpeace)) {
  if(isset($neut)) {
    $setrel_error = $diplomacy->accReqRelation($neut);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($accbnd)) {
  if(isset($bnd)) {
    $setrel_error = $diplomacy->accReqRelation($bnd);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($delpeace)) {
  if(isset($neut)){
    $setrel_error = $diplomacy->delReqRelation($neut, 1);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($delreqbnd)) { 
  if(isset($bnd)) {
    $setrel_error = $diplomacy->delReqRelation($bnd, 2);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($delownpeace)) {
  if(isset($reqneut)) {
    $setrel_error = $diplomacy->delReqRelation($reqneut, -1);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}
else if (isset($delownreqbnd)) {
  if(isset($reqbnd)) {
    $setrel_error = $diplomacy->delReqRelation($reqbnd, -2);
  }
  else {
    $setrel_error = "Ihr habt keinen Spieler aus der Liste ausgewählt.";
  }
}

start_page();
?>
<script language="JavaScript">
<!--
function minimap() {
  window.open("diplomap.php","Karte","width=840,height=820,left=0,top=0,status=no,scrollbars=yes,dependent=yes");
}
//-->
</script>
<?
start_body();
if ($setrel_error != null) {
  echo '<div style="font-size: 12px;" class="error"><b>Fehler:</b> '.$setrel_error.'</div><p>';
}
?>
<p>
<img src="<? echo $imagepath;?>/windrose_klein.gif" onclick="minimap()";>
<font color="#FF2020"><b>Neu: </b></font><a onclick="minimap(); return false;" href="diplomap.php">Diplomatie-Karte</a><br>
Auf dieser Karte sehen Sie Freunde und Feinde im Überblick.
<p>
<form action="<? echo $PHP_SELF; ?>" method="GET">
<table>
<tr class="tblhead">
<td>Feinde</td>
<td>Verb&uuml;ndete</td>
</tr>
<tr class="tblbody">
<td>
<select size="5" name="enemy">
<?php

$res1 = do_mysqli_query("(SELECT player.name AS name FROM relation LEFT JOIN player ON relation.id1=player.id ".
                       " WHERE relation.type=0 AND relation.id2=".$_SESSION['player']->getID().
                       ") UNION (".
                       "SELECT player.name AS name FROM relation LEFT JOIN player ON relation.id2=player.id ".
                       " WHERE relation.type=0 AND relation.id1=".$_SESSION['player']->getID().
                       ") ORDER BY name"
                       );

//$res2 = do_mysqli_query("SELECT player.name AS name FROM relation LEFT JOIN player ON relation.id2=player.id ".
//                       " WHERE relation.type=0 AND relation.id1=".$_SESSION['player']->getID());

while ($data1 = mysqli_fetch_assoc($res1))
  echo '<option value="'.$data1['name'].'">'.$data1['name']."</option>\n";
  //while ($data2 = mysqli_fetch_assoc($res2))
  //  echo '<option value="'.$data2['name'].'">'.$data2['name']."</option>";
echo "</select>";
echo "</td><td>";

echo '<select size="5" name="friend">';
$res1 = do_mysqli_query("(SELECT player.name AS name FROM relation LEFT JOIN player ON relation.id1=player.id ".
                       " WHERE relation.type=2 AND relation.id2=".$_SESSION['player']->getID().
                       ") UNION (".
                       "SELECT player.name AS name FROM relation LEFT JOIN player ON relation.id2=player.id ".
                       " WHERE relation.type=2 AND relation.id1=".$_SESSION['player']->getID().
                       ") ORDER BY name"
                       );

//$res2 = do_mysqli_query("SELECT player.name AS name FROM relation LEFT JOIN player ON relation.id2=player.id ".
//" WHERE relation.type=2 AND relation.id1=".$_SESSION['player']->getID());
while ($data1 = mysqli_fetch_assoc($res1))
  echo '<option value="'.$data1['name'].'">'.$data1['name']."</option>";
//while ($data2 = mysqli_fetch_assoc($res2))
//  echo '<option value="'.$data2['name'].'">'.$data2['name']."</option>";
echo "</select>";
echo "</td></tr>";
echo '<tr class="tblbody">';
echo '<td><input type="submit" name="peace" value=" Frieden anbieten "></td>';
echo '<td><input type="submit" name="delbnd" value=" B&uuml;ndnis k&uuml;ndigen "></td></tr>';
echo '<tr></tr>';
echo '<tr class="tblhead"><td>fremde Friedensangebote</td><td>fremde B&uuml;ndnisangebote</td>';
echo '<tr class="tblbody"><td>';
echo '<select size="5" name="neut">';
$res3 = do_mysqli_query("SELECT player.name AS name, player.id AS id FROM req_relation LEFT JOIN player ON req_relation.id1=player.id WHERE req_relation.type=1 AND req_relation.id2=".$_SESSION['player']->getID());
while ($data3 = mysqli_fetch_assoc($res3))
  echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
echo '</select></td><td>';
echo '<select size="5" name="bnd">';
$res3 = do_mysqli_query("SELECT player.name AS name, player.id AS id FROM req_relation LEFT JOIN player ON req_relation.id1=player.id WHERE req_relation.type=2 AND req_relation.id2=".$_SESSION['player']->getID());
while ($data3 = mysqli_fetch_assoc($res3))
  echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
echo '</select></td></tr>';
echo '<tr class="tblbody">';
echo '<td><input type="submit" name="accpeace" value=" Frieden annehmen "></td>';
echo '<td><input type="submit" name="accbnd" value=" B&uuml;ndnis annehmen "></td></tr>';
echo '<tr class="tblbody">';
echo '<td><input type="submit" name="delpeace" value=" Frieden ablehnen "></td>';
echo '<td><input type="submit" name="delreqbnd" value=" B&uuml;ndnis ablehnen "></td></tr>';
echo '<tr class="tblhead"><td>eigene Friedensangebote</td><td>eigene B&uuml;ndnisangebote</td>';
echo '<tr class="tblbody"><td>';
echo '<select size="5" name="reqneut">';
$res3 = do_mysqli_query("SELECT player.name AS name, player.id AS id FROM req_relation LEFT JOIN player ON req_relation.id2=player.id WHERE req_relation.type=1 AND req_relation.id1=".$_SESSION['player']->getID());
while ($data3 = mysqli_fetch_assoc($res3))
  echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
echo '</select></td><td>';
echo '<select size="5" name="reqbnd">';
$res3 = do_mysqli_query("SELECT player.name AS name, player.id AS id FROM req_relation LEFT JOIN player ON req_relation.id2=player.id WHERE req_relation.type=2 AND req_relation.id1=".$_SESSION['player']->getID());
while ($data3 = mysqli_fetch_assoc($res3))
  echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
echo '</select></td></tr>';
echo '<tr class="tblbody">';
echo '<td><input type="submit" name="delownpeace" value=" Frieden zur&uuml;ckziehen "></td>';
echo '<td><input type="submit" name="delownreqbnd" value=" B&uuml;ndnis zur&uuml;ckziehen "></td></tr>';
echo "</table></form>";
echo "<br><table>";
echo '<form action="'.$PHP_SELF.'" method="POST" '.($_SESSION['player']->getNoobLevel() > 0 ? ' onSubmit="if(this.type.value==0) return confirm(\'Wenn Sie Krieg erkl�ren, dann verlieren sie Ihren Neulingsschutz!\')"' : '').'>';
echo '<tr class="tblhead"><td colspan="4">Neue diplomatische Beziehung</td></tr>';
echo '<tr class="tblbody"><td>Spielername</td>';
echo "<td><input type='text' name='playername' value='".$name."' NAOsize='12'></td>";
echo '<td><select name="type">';
echo '<option value="2">B&uuml;ndnis</option>';
echo '<option value="0">Krieg</option>';
echo '<td><input type="submit" name="newRel" value=" Senden ">';
if ($_SESSION['player']->getNoobLevel() > 0) echo '<tr class="tblhead"><td colspan="4">Durch eine Kriegserkl�rung verlieren Sie Ihren Neulingsschutz!</td></tr>';
echo "</select></td></tr>";

?>
</table>
<br><br>
</form>

<? end_page(); ?>
