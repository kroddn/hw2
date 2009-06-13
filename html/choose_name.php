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

/**
 * Auswahl des Namens und der Religion/Startposition
 *
 * (C) 2005-2008 psitronic IT-Solutions  
 *     written by Markus Sinner, 02.12.2005
 */

session_start();

// Sichergehen, dass diese Datei nur mit gültigen Parametern geöffnet wird.
if(!isset($_SESSION['db_login'])) {
  header("Location: login.php?login_error=nodblogin");
  exit();
}

$error = null;
$pos = intval($pos);

if(isset($selectname)) {
  include_once ("includes/util.inc.php");

  // Führende und abschließende Leerzeichen weglassen
  $playername = trim($_REQUEST['playername']);
  
  
  if(!checkBez($playername)) {
    $error = "Der gewählte Name ist zu kurz, zu lang, verstößt gegen die Namenskonventionen oder enthält doppelte Leerzeichen.";
  }
  else {      
    include_once ("includes/db.inc.php");
    
    // Prüfen, ob der Name bereits vergeben ist
    $check = do_mysql_query("SELECT * FROM player WHERE name = '".mysql_escape_string($playername)."'");
    if(mysql_num_rows($check) > 0) {
      $error = "Der Spielername '$playername' ist bereits vergeben.";
      unset($playername);
    }
    else if($pos < 1 || $pos > 6 ) {
      $error = "Ungültige Startposition.";
    }
    else if($_SESSION['db_login']['name'] != null) {
      $error = "Ihr habt bereits einen Namen gewählt. Schummler?";
    }
    else {
      $religion = ceil($pos / 3);
      
      $sql = sprintf("UPDATE player SET pos = %d, religion = %d, name = '%s', activationtime=UNIX_TIMESTAMP(), lastres=0 WHERE id = %d" , 
                     $pos, $religion, mysql_escape_string($playername), $_SESSION['db_login']['id']);
      do_mysql_query($sql);
     
      // Noobschutz bei 
      if(defined("HISPEED")) {
        do_mysql_query("UPDATE player SET nooblevel = 0 WHERE id = ".$_SESSION['db_login']['id']);
      }
      
      $_SESSION['sec_key'] = "magic1234";
      include("includes/login.func.php");
      $GLOBALS['login'] = true;
            
      $error = hw2_login($_SESSION['db_login']['login'] , null, $_SESSION['sec_key'], true);
      unset($_SESSION['db_login']);
      if($error) {        
        die($error);
      }
      else {
        die("Startpos chosen");
      }
      
      
      
      /*
      session_destroy();
      header("Location: login.php?name=".urlencode($loginname));
      exit();
      */
    }
  }
}

// Standard HW2-Header
require_once("start_header.php");


if(defined("START_POS_NEW") && START_POS_NEW) {
  checkSettleRadius();
  for($reli = 1; $reli <= 2; $reli++) {
    for($p = 0; $p < 3; $p++) {
      $where = getStartPosWhere($p, $reli);

      if($db_login['hwstatus'] & 63) {
        echo $where."<br>";
      }

      $count = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM startpositions WHERE ".$where);
      $locsum = $count['c'];
      if($reli == 1) $loc[$p]   = intval($count['c']);
      else           $loc[5-$p] = intval($count['c']);
    }
  }
  // Jetzt muß noch korrigiert werden für das alte Skript.
}
else {
  $mapsize = do_mysql_query_fetch_assoc("SELECT max(x)+1 AS x, max(y)+1 AS y FROM map");
  $fx = $mapsize['x'];
  $fy = $mapsize['y'];

  $qry_startlocations = do_mysql_query("SELECT count(*) as c, floor(y/".$fy."*6) as pos FROM startpositions GROUP BY pos");

  $locsum = 0;
  while ($res_sl=mysql_fetch_assoc($qry_startlocations)) {
    $loc[$res_sl['pos']]=$res_sl['c'];
    $locsum+=$res_sl['c'];
  }
  // Korrektur: Startpost 2+3 sind eigentlich eine Position
  $loc[2] = $loc[3] = $loc[2] + $loc[3];
}

if(!isset($christratio))
     die("FATAL ERROR");

?>

<tr>
  <td width="22%" valign="top"></td>
  <td width="53%" valign="top" class="tblbody">
<center><h1>Spielernamen und Startposition wählen</h1></center>
Wählt nun den Namen, unter dem Ihr in der Welt bekannt sein
wollt. Um die Sicherheit zu erhöhen wird empfohlen, dass Spielername
und Login nicht identisch sind.
<p>
<form method="get" action="choose_name.php">
<table width="500" cellspacing="1" cellpadding="0" border="0">
 <tr height="0">
  <td width="200"></td><td width="300"></td>
 </tr>

<? if($error != null) { ?>
 <tr>
 <td colspan="2" style="font-weight: bold; color: red; font-size: 14px;" align="center">Fehler: <? echo $error; ?></td>
 </tr>
<? } ?>

 <tr>
    <td class="tblbody"><b>Spielername</b></td>
    <td class="tblbody"><input type="text" name="playername" <?if(isset($playername)) echo ' value="'.$playername.'"'?>></td>
 </tr>

 <tr>
 <td colspan="2" class="tblbody" align="center"><hr></td>
 </tr>

<?php
if($christratio > 1.0 ) { ?>
 <tr>
  <td colspan="2" class="tblbody" align="center"><font color="red" size="+2">Anhänger des Islam gesucht!</font><br>Zur Zeit sind die Christen auf dem Vormarsch. Darum überlegt Euch gut, ob Ihr nicht auf Seiten des Islam einsteigen wollt (dem Spielspaß kommt es zugute).
  </td>
 </tr>
<? 
} 
?>
 <tr height="40" class="tblbody"><td colspan="2" valign="middle" align="center">
   Angemeldet: <? echo $registered[0][0]." Christen und ". $registered[1][0]. " Moslems. <b>Wählt ".($registered[0][0] >= $registered[1][0] ? "Islam" : "Christentum").",</b> um das Gleichgewicht zu wahren."; ?>
 </td></tr>
 
 <tr height="40" class="tblbody"><td colspan="2" valign="middle" align="center">
   <b><font size="+1">Ausgangslage wählen</font> (<? echo $locsum; ?> offene Positionen)</b>
 </td></tr>
 <tr class="tblbody">
 <td align="center">
   <? if(defined("START_POS_NEW") && START_POS_NEW) { 
     echo "Siedlungsradius: ".getSettleRadius();
   }
   else {
     echo '<a target="_blank" href="http://forum.holy-wars2.de/viewtopic.php?p=107853#107853">Änderung nächste Runde!<br>(hier klicken)</a>';
   }
   ?>
 </td>
 <td nowrap width="300" >
Für Details auf die Karte klicken!
</td>
</tr>
<tr class="tblbody">
<td>
   <div align="right"><a href="settlemap.php" target="_blank"><img border="0" src="<? echo 'maps/'.REGISTER_MAP; ?>"></a></div>
</td>
<td valign="middle" align="left">
<?php

if ($loc[0]>0 && $christratio < REGISTER_RATIO_LIMIT_HARD)   echo "<input class='register' type='radio' name='pos' value='1' ".($pos==1 ? "checked" : "").">&nbsp;Christentum, Hinterland (Zone I) <b>(".$loc[0].' offen)</b><div class="register"><hr class="register"></div>';
else echo "<div class='error'><input class='noborder' type='radio' name='pos' value='' disabled>&nbsp;Christentum, Hinterland (Zone I)</div>";

if ($loc[1]>0 && $christratio < REGISTER_RATIO_LIMIT_MEDIUM) echo "<input class='register' type='radio' name='pos' value='2' ".($pos==2 ? "checked" : "").">&nbsp;Christentum (Zone II) <b>(".$loc[1].' offen)</b><div class="register"><hr  class="register"></div>';
else echo "<div class='error'><input class='noborder' type='radio' name='pos' value='' disabled>&nbsp;Christentum (Zone II)</div>";

if ($loc[2]>0 && $christratio < REGISTER_RATIO_LIMIT_LIGHT)  echo "<input class='register' type='radio' name='pos' value='3' ".($pos==3 ? "checked" : "").">&nbsp;Christentum, Kriegsgebiet (Zone III) <b>(".($loc[2]+$loc[3])." offen)</b><br>";
else echo "<div class='error'><input class='noborder' type='radio' name='pos' value='' disabled>&nbsp;Christentum, Kriegsgebiet (Zone III)</div>";

if ($loc[3]>0 && $islamratio  < REGISTER_RATIO_LIMIT_LIGHT)  echo "<input class='register' type='radio' name='pos' value='4' ".($pos==4 ? "checked" : "").">&nbsp;Islam, Kriegsgebiet (Zone III) <b>(".($loc[2]+$loc[3]).' offen)</b><div class="register"><hr class="register"></div>';
else echo "<div class='error'><input class='noborder' type='radio' name='pos' value='' disabled>&nbsp;Islam, Kriegsgebiet (Zone III)</div>";

if ($loc[4]>0 && $islamratio  < REGISTER_RATIO_LIMIT_MEDIUM) echo "<input class='register' type='radio' name='pos' value='5' ".($pos==5 ? "checked" : "").">&nbsp;Islam (Zone IV) <b>(".$loc[4].' offen)</b><div class="register"><hr class="register"></div>';
else echo "<div class='error'><input class='noborder' type='radio' name='pos' value='' disabled>&nbsp;Islam (Zone IV)</div>";

if ($loc[5]>0 && $islamratio  < REGISTER_RATIO_LIMIT_HARD)   echo "<input class='register' type='radio' name='pos' value='6' ".($pos==6 ? "checked" : "").">&nbsp;Islam, Hinterland (Zone V) <b>(".$loc[5]." offen)</b><br>";
else echo "<div class='error'><input class='noborder' type='radio' name='pos' value='' disabled>&nbsp;Islam, Hinterland (Zone V)</div>";
?>

        </td>
	</tr>
	<tr>
		<td colspan="2" class="tblbody" align="center">Hinweis: Im <i>Umkämpften Gebiet</i> ist die Wahrscheinlichkeit sehr hoch, dass man früh im Spiel angegriffen wird. Auch im <i>Hinterland</i> ist keine Sicherheit gewährleistet. Ihr könnt jederzeit von Gleichgläubigen überfallen werden.</td>
	</tr>
		<tr>
		<td colspan="2" class="tblbody" align="center">&nbsp;</td>
	</tr>
  </td>
  <td width="22%" valign="top"></td>
</tr>
<tr><td  colspan="2" align="center">
<input type="submit" name="selectname" value=" durchführen ">
<input type="button" onClick="window.location.href='index.php';" value=" abbrechen ">
</td></tr>
</table>
</form>

<!-- Page End -->
</td>
</tr>
</table>
</body>
</html>