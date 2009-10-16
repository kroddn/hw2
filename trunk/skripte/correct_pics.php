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
 * Fehlende Bilder durch Wiesen ersetzen.
 */


$run_path = getenv("PWD");

if (stristr($run_path, "admintools")) {
  echo $run_path;
  echo "\nStart aus dem falschen Verzeichnis heraus.\nAus html/ starten!\n";
  die();
}

// Datenbankconnect
include_once("includes/db.inc.php");

$hit = $miss = $corrected = $sum = 0;
$map = do_mysql_query("SELECT id,type,pic FROM map ORDER BY pic");
while($pic = mysql_fetch_assoc($map)) {
  if(!file_exists("images/ingame/".$pic['pic'].".gif")) {
    //printf("%s.gif ", $pic['pic']);
    if($pic['type']==1) {
      $new = "";
      for($p=0; $p<5; $p++) {
        if($pic['pic'][$p] == '1') {
          $new.= '1';
        }
        else {
          $new.= '2';
        }
      }

      if(!strcmp($new, "22122")) {
        $new.= rand(0,2);
      }
      
      $sql = "UPDATE map set pic = '$new' where id =".$pic['id'];
      do_mysql_query($sql);
      echo "-> $new.gif\n";
      $corrected++;
    }
    if($pic['type']==2) {
		  do_mysql_query("UPDATE map set pic = '22222' where id =".$pic['id']);
		  echo "-> 22222.gif\n";
		  $corrected++;
		}
    if($pic['type']==3) {
		  do_mysql_query("UPDATE map set pic = '22322' where id =".$pic['id']);
		  echo "-> 22322.gif\n";
		  $corrected++;
		}
		else if($pic['type']==4){
		  do_mysql_query("UPDATE map set pic = '22422' where id =".$pic['id']);
		  echo "-> 22422.gif\n";
		  $corrected++;
		}
    $miss++;
  }
  else {
    $hit++;
  }
  $sum++;
}

echo "\n$sum Bilder insgesamt, $hit Bilder vorhanden, $miss Bilder fehlen, $corrected Bildfehler behoben\n";
if($hit+$corrected == $sum) echo "Alle Bildfehler erfolgreich korrigiert!\n";
?>
