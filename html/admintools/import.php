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


$errorcount=0;

if(!function_exists("getpng")) {
  function getPNG($filename) {

    echo "Loading $filename<br>\n";
    $img = imagecreatefrompng($filename);
    if(!$img /*|| strlen($img) == 0 */)
      die ("Datei Fehlt: $filename\n");
    return $img;
  }
}

$run_path = getenv("PWD");

if (stristr($run_path, "admintools")) {
  echo $run_path;
  echo "\nStart aus dem falschen Verzeichnis heraus.\nAus html/ starten!\n";
  die();
}
else {
  // Datenbankconnect
  require_once("includes/db.inc.php");
  include_once("admintools/import.config.php");
  include_once("admintools/map.func.php");

  session_name("admintools");
  session_start();
  unset($_SESSION['init_cols']);

  ini_set ("max_execution_time", "180");
  
  initColors();

  $error = imgToDB(0);
  if($error != null)
    echo "\nFEHLER: $error\n";
}



// Der Code war h�sslich, also hab ich ihn noch ein bisschen h�sslicher gemacht (co2)
function typeNr($col) {
  $type = NULL;

  switch ($col) {
  case $_SESSION['grassland']:
    $type = 2;
    break;
  case $_SESSION['water']:
    $type = 1;
    break;
  case $_SESSION['forest']:
    $type = 3;
    break;
  case $_SESSION['mountain']:
    $type = 4;
    break;
  case $_SESSION['pearls']:
    $type = 1;
    break;
  case $_SESSION['fish']:
    $type = 2;
    break;
  case $_SESSION['wine']:
    $type = 3;
    break;
  case $_SESSION['wheat']:
    $type = 4;
    break;
  case $_SESSION['furs']:
    $type = 5;
    break;
  case $_SESSION['herbs']:
    $type = 6;
    break;
  case $_SESSION['metal']:
    $type = 7;
    break;
  case $_SESSION['gems']:
    $type = 8;
    break;
  default:
  }

  return $type;
}

function imgToDB($startrow) {
  $errors = 0;

  // Neuer Import gestartet -> alte Karte löschen
  if ($startrow==0) {
    echo "Tabelle leeren\n";
    mysqli_query($GLOBALS['con'], "TRUNCATE map");

    echo "Indizes löschen\n";
    mysqli_query($GLOBALS['con'], "ALTER TABLE `map` DROP PRIMARY KEY");
    mysqli_query($GLOBALS['con'], "ALTER TABLE `map` DROP INDEX xy");
    mysqli_query($GLOBALS['con'], "ALTER TABLE `map` DROP INDEX type");
    mysqli_query($GLOBALS['con'], "ALTER TABLE `map` DROP INDEX special");

    //mysqli_query($GLOBALS['con'], "ALTER TABLE map AUTO_INCREMENT=0");

    echo "Lösche Startpos\n";
    mysqli_query($GLOBALS['con'], "TRUNCATE startpositions");
  }

  // Bilder laden
  $img = getPNG("admintools/map.png");
  $img2= getPNG("admintools/special.png");

  $sx = ImageSX($img);
  $sy = ImageSY($img);
  if(!defined("MAP_SIZE_X")) define("MAP_SIZE_X", $sx);
  if(!defined("MAP_SIZE_Y")) define("MAP_SIZE_Y", $sy);
    
  echo "Größe: ".MAP_SIZE_X."x".MAP_SIZE_Y."\n";
  
  //  Z�hler erstellen
  $row=$startrow;
  $id_counter = 0;


  // Hauptschleife
  for ($j=$startrow;$j < MAP_SIZE_Y;$j++) {
    printf("\rKartenzeile: %03d. %d%%<br>", $j, round(100*$j/MAP_SIZE_Y) );

    for ($i=0;$i < MAP_SIZE_X;++$i) {
      // Aktuelles Feld
      $type = typeNr(imagecolorat($img,$i,$row));

      // Bei Wiesen gibts nur einen Typ -> 22222
      if ($type == 2) {
	$pic = "22222";
      }
      else {
	// Feld OBERHALB
	if ($row > 0) {
	  $pic = typeNr(imagecolorat($img,$i,$row-1));
	}
	else {
	  $pic = typeNr(imagecolorat($img,$i,$row));
	}

	// Feld LINKS
	if ($i > 0) {
	  $pic .= typeNr(imagecolorat($img,$i-1,$row));
	}
	else {
	  $pic .= typeNr(imagecolorat($img,$i,$row));
	}

	if ($type == NULL) {
          if($errorcount > 1000) die("Mehr als 1000 Fehler in Karte. Breche ab!");

	  echo "\nType NULL: setze $i:$row auf 2.<br>\n";
	  $type = 2;
          $errorcount++;
	}
	$pic .= $type;

	// Feld RECHTS
	if ($i < MAP_SIZE_X - 1) {
	  $pic .= typeNr(imagecolorat($img,$i+1,$row));
	} else {
	  $pic .= typeNr(imagecolorat($img,$i,$row));
	}

	// Feld UNTERHALB
	if ($row < MAP_SIZE_Y - 1) {
	  $pic .= typeNr(imagecolorat($img,$i,$row+1));
	} else {
	  $pic .= typeNr(imagecolorat($img,$i,$row));
	}
      } // type != 2


      /**** Spezialgrafiken... ***/
      // Seen
      if ($pic == "22122") 
	{
	  $seeRand = $row*$i %4;
	  $pic .= $seeRand;
	}


      $special = typeNr(imagecolorat($img2,$i,$row));
      if (($special == 1) ||
      	  ($special == 2) ||
      	  ($special == 3) ||
      	  ($special == 4) ||
      	  ($special == 5) ||
      	  ($special == 6) ||
      	  ($special == 7) ||
      	  ($special == 8)
          ) {
        $sql = "INSERT INTO map(id,x,y,type,special,pic) VALUES (".$id_counter.",".$i.",".$row.",".$type.",".$special.",'".$pic."')";
        if(!mysqli_query($GLOBALS['con'], $sql)) {
          echo "\nSQL:\n'$sql'\nFehler: ".mysqli_error($GLOBALS['con'])."\n";
          $errors++;
        }
      }
      else
        {
          $sql = "INSERT INTO map(id,x,y,type,pic) VALUES (".$id_counter.",".$i.",".$row.",".$type.",'".$pic."')";
          if(!mysqli_query($GLOBALS['con'], $sql)) {
            echo "\nSQL:\n'$sql'\nFehler: ".mysqli_error($GLOBALS['con'])."\n";
            $errors++;
          }
        }
      $id_counter++;
    }
    // wenn Ende erreicht, abbrechen
    ++$row;
    if ($row== MAP_SIZE_Y) break;
  }
  echo "\nIndizes erstellen";
  if (!mysqli_query($GLOBALS['con'], "ALTER TABLE `map` ADD PRIMARY KEY ( id )"))
    echo "Fehler: ".mysqli_error($GLOBALS['con'])."\n";
  mysqli_query($GLOBALS['con'], "ALTER TABLE `map` ADD UNIQUE xy (x, y)");
  mysqli_query($GLOBALS['con'], "ALTER TABLE `map` ADD INDEX x (x)");
  mysqli_query($GLOBALS['con'], "ALTER TABLE `map` ADD INDEX y (y)");
  mysqli_query($GLOBALS['con'], "ALTER TABLE `map` ADD INDEX type (type)");
  mysqli_query($GLOBALS['con'], "ALTER TABLE `map` ADD INDEX special (special)");

  echo "\nKarte erfolgreich importiert! $errors Fehler.\n";
  echo "ACHTUNG: startpos neu generieren!!!\n";

  return null;
}


?>