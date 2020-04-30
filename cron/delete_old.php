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
 * delete_old.php
 *
 * Inaktive Spieler löschen. Spieler im Urlaubsmodus werden
 * selbstverständlich ausgelassen.
 *
 * 15. Mar 2006 written by Markus Sinner <kroddn@holy-wars2.de>
 */
if(!file_exists("includes/db.inc.php")) {
  echo "Wechseln Sie vor ausführung in das HTML-Verzeichnis\n";
  echo "einer HW2-Installation!\n";
  die();
}

include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
include_once("includes/admin.inc.php");

printf("\n===========================\n%s\n", date("d.m.y G:i:s", time()));

// Maximalzeit eines inaktiven Spielers
if (defined("MAX_INACTIVE")){
  if(MAX_INACTIVE < 7*24*3600) {
    die("Seltsamer Wert, kleiner als 7 Tage???");
  }
  $max_inactive = MAX_INACTIVE;
  echo "MAX_INACTIVE defined: ".($max_inactive/(24*3600))."\n";
}
else {
  $max_inactive = 60*(24*3600);
}

// Zeit, die einem Spieler gegeben wird, sich nach
// dem Urlaubsmodus erneut einzuloggen
if (defined("HOLIDAY_TOLERANCE")) {
  $holiday_tolerance = HOLIDAY_TOLERANCE;
  echo "HOLIDAY_TOLERANCE defined: ".($holiday_tolerance/(24*3600))."\n";
}
else {
  $holiday_tolerance = 7*(24*3600);
  echo "holiday tolerance: ".($holiday_tolerance/(24*3600))."\n";
}

echo "\nStarte. L�sche inaktive (l�nger als ".round($max_inactive/(3600*24), 2)." Tage)\n\n";

// Zunächst die ganz alten Spieler löschen
$players = do_mysqli_query(
"SELECT *,unix_timestamp()-lastseen AS inactive
 FROM player
 WHERE (
  unix_timestamp()-lastseen > $max_inactive
  AND coalesce(holiday,0) + $holiday_tolerance < unix_timestamp()
  AND hwstatus = 0
 )
 OR (
  markdelete > 0 AND markdelete < UNIX_TIMESTAMP() 
 )
 ORDER BY lastseen");

$deleted=0;
while($p = mysqli_fetch_assoc($players)) {
  if(deleteResult($p)) {
    $deleted++;
  }
}



echo "\nInsgesamt gelöscht: $deleted\n";


/**
 * L�sche Spieler wirklich jetzt.
 * 
 * @param $p  Assoziativer Array, wie er von mysqli_fetch_assoc kommt
 * @return unknown_type
 */
function deleteResult($p) {
  global $max_inactive;
  
  if($p['markdelete'] > 0) {
    echo "Player '".$p['name']." [".$p['login']."]" ."' zum L�schen markiert ".date("d.m.y H:i", $p['markdelete'])."\n";
    RemovePlayerAbandoneCities($p['id']);
    return true;
  }
  else {
    echo "Player '".$p['name']." [".$p['login']."]"."' inaktiv ".round($p['inactive'] / (3600*24), 2)." Tagen\n";
    $login = do_mysqli_query("SELECT *,from_unixtime(time) AS zeit, unix_timestamp()-time AS inactive ".
                          " FROM log_login ".
                          " WHERE id = ".$p['id']." AND inputpw = dbpw AND inputseccode = dbseccode".
                          " ORDER BY time DESC LIMIT 1");
    if(mysqli_num_rows($login) > 0) {
      $l = mysqli_fetch_assoc($login);
      echo " LastLogin Versuch: ".$l['zeit']." (".round($l['inactive'] / (3600*24), 2)." Tage)\n";
      if ($l['inactive'] > $max_inactive) {
        echo " lösche ".$p['id']." - ".$p['name']." jetzt\n";
        if(defined("ABANDONE_CITIES") && ABANDONE_CITIES) {
          RemovePlayerAbandoneCities($p['id']);
        }
        else {
          RemovePlayer_old($p['id']);
        }
        return true;
      }
    }
    else {
      echo " Bisher nie eingeloggt.\n";
      echo " lösche ".$p['id']." - ".$p['login']." jetzt\n";
      if(defined("ABANDONE_CITIES") && ABANDONE_CITIES) {
        RemovePlayerAbandoneCities($p['id']);
      }
      else {
        RemovePlayer_old($p['id']);
      }
      return true;
    }
  }
  return false;
}

?>