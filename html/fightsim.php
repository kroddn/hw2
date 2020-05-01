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


include_once ("includes/db.inc.php");
include_once ("includes/session.inc.php");
include_once ("includes/fight.class.php");
?>

<html>
<head>
<title>Tool zum Simulieren von Kämpfen</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<style>
input {
 width:50;
 text-align:right;
 padding-right:1px;
}
</style>
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
$allowed = defined("FIGHTSIM_NEED_POINTS") && ($_SESSION['player']->getBonuspoints() >= FIGHTSIM_NEED_POINTS);

if (defined("HW2DEV") && HW2DEV && !$_SESSION['player'] ->isMaintainer() ) {
  $allowed = false;
  echo "<h1 class=\"error\">Kampfsimulator in HW2DEV nur für Maintainer!</h1>";
}
else if (!$allowed) {
    echo "<h1 class=\"error\">Ihr habt nicht genügend Bonuspunkte!</h1>";
    echo "Ihr benötigt wenigstens ".FIGHTSIM_NEED_POINTS." Bonuspunkte. ";
    echo "<a href='settings.php?show=account'>Weitere Infos (hier klicken).</a><p>";
}
if ($_SESSION['player']->isMaintainer()) {
  echo "<b>Maintainer override</b><p>";
}

echo '<table> <!-- Main and Ads -->';
if (isset ($fight) && ($allowed || $_SESSION['player']->isAdmin()) ) {
  echo '<tr><td colspan="2" valign="top">';

  $fightresult;
  $fight = new FightClass();

  $fight->defbonus = $deffbon;
  $fight->tactic = $tac;
  $fight->citypopulation = $citypop;

  $fight->cityname = "SimStadt";
  $defftotal = array ();
  $atttotal = array ();
  foreach($a as $key => $value)
  {
    if($value > 0)
      {      
        $att = array ();
        $att['id'] = $key;
        $att['count'] = $value;
        $atttotal[] = $att;
      }    
  }

  foreach($d as $key => $value)
  {
    if($value > 0)
      {
        $deff = array ();
        $deff['id'] = $key;
        $deff['count'] = $value;
        $defftotal[] = $deff;
      }    
  }
 
  if(count($defftotal) <= 0) {
  }
  else if (count($atttotal) <= 0) {
    echo '<h1 class="error">Es muss mindestens eine Einheit angreifen!</h1>';        
  }
  else {

    $fight->AddPlayerArmy(false, 1, "Verteidiger", $defftotal);
    $fight->cityowner['name'] = "Verteidiger";
    $fight->cityowner['id'] = 1;
    
    $fight->AddPlayerArmy(true, 2, "Angreifer", $atttotal);
    $fight->Fight();
    $_SESSION['player']->setBonusPoints($_SESSION['player']->getBonuspoints() - FIGHTSIM_NEED_POINTS);
    
    $fightresult = $fight->GetMessageFor(false, 1);
    echo "<table cellpadding='0' cellspacing='1' border='0' width='100%'>\n";
    echo "<tr class=\"tblhead\"><td colspan='3'><b>Nachricht lesen</b></td></tr>\n";
    echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
    echo "<tr><td class='tblhead' colspan='2'>&nbsp;</td>";
    echo "<td class='tblhead' rowspan='3'>";
    echo "</td></tr>\n";
    echo "<tr><td width='60' class='tblbody'><b>Absender</b></td><td width='400' class='tblbody'>"."Server"."</td></tr>\n";
    echo "<tr><td width='60' class='tblbody'><b>Titel</b></td><td width='400' class='tblbody'>"."Kampfsimulation"."</td></tr>\n";
    echo "<tr><td width='460' height='150' valign='top' colspan='3' class='msg'>".bbCode($fightresult)."</td></tr>\n";
    echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
    echo "</table>";
    
  }

  echo "</td></tr>\n";
} 
else {
  $deffbon = 0;
  if (!isset($tac)) $tac = 0;
  $citypop = 1000;
}
?>
<tr><td valign="top">

<form action="<? echo $PHP_SELF; ?>" method="POST">
<table cellspacing="1" cellpadding="0" border="0">
<tr><td class="tblhead" colspan="3" style="padding-top:4px; padding-bottom:4px;"><b>Armeen</b></td></tr>
<tr class="tblhead" style="font-weight:bold;">
  <td width="200" style="text-align:center;">Name</td>
  <td width="60" style="text-align:center;">Angreifer</td>
  <td width="60" style="text-align:center;">Verteidiger</td>
</tr>
<?

$resUnits = do_mysqli_query("SELECT id,name,religion,level,type FROM unit ORDER BY religion,id ASC");
while ($Unit = mysqli_fetch_assoc($resUnits)) {
  echo "<tr>";
  $img = $Unit['religion'] == 1 || $Unit['religion'] == 2 ? '<img src="'.$GLOBALS['imagepath']."/".getUnitImage($Unit).'"> ' : "";
  printf('<td class="tblbody">%s%s</td>', 
         $img, $Unit['name']);
         
  if (isset ($fight)) {
    printf('<td align="right" class="tblbody"><input type="text" name="a[%d]" value="%d"></td>',
          $Unit['id'], $_POST['a'][$Unit['id']] );
    printf('<td align="right" class="tblbody"><input type="text" name="d[%d]" value="%d"></td>',
          $Unit['id'], $_POST['d'][$Unit['id']] );
  }
  else {
    printf('<td align="right" class="tblbody"><input type="text" name="a[%d]" value="0"></td>', 
            $Unit['id']);
    printf('<td align="right" class="tblbody"><input type="text" name="d[%d]" value="0"></td>', 
           $Unit['id']);    
  }
  echo "</tr>\n";
}
?>
</table>
<br>
<form action="<? echo $PHP_SELF; ?>" method="POST">
<table cellspacing="1" cellpadding="0" border="0">
<tr><td class="tblhead" colspan="2" style="padding-top:4px; padding-bottom:4px;"><b>Einstellungen</b></td></tr>
<tr class="tblhead" style="font-weight:bold;">
  <td width="200" style="text-align:center;">Einstellung</td>
  <td width="130" style="text-align:center;">Wert</td>
</tr>
<tr>
<td class='tblbody'>Verteidigungswert</td>
<td class='tblbody'><input type='text' name='deffbon' value='<?echo $deffbon?>' style=\"width:100%;\"></td>
</tr>

<tr>
<td class='tblbody'>Taktik</td>
<td class='tblbody'>
<select name="tac">
<option value="0" <? if($tac == 0) echo "selected"; ?>>Offensiv</option>
<option value="1" <? if($tac == 1) echo "selected"; ?>>Defensiv</option>
<option value="2" <? if($tac == 2) echo "selected"; ?>>Erstürmen</option>
</select>
</td>
</tr>

<tr>
<td class='tblbody'>Stadtbevölkerung</td>
<td class='tblbody'><input type='text' name='citypop' value='<?echo $citypop?>' style=\"width:100%;\"></td>
</tr>
<tr>
<td colspan="2" align="center">
Die Verwendung des Kampfsimulators <b>kostet <? echo FIGHTSIM_NEED_POINTS; ?> Bonuspunkte</b>.<br>
Euch <b>bleiben <? echo $_SESSION['player']->getBonuspoints(); ?> Bonuspunkte</b> zur Verwendung.
<p>
<input style="margin-top:5px; margin-bottom:5px; width:120px;" type="submit" name="fight" value="Kampf berechnen">
</td>
</table>
</form>

</td>

</tr>
</table>
</body>
</html>