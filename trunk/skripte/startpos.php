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


require_once("admintools/import.config.php");

$run_path = getenv("PWD");

$x = 0;
$y = 0;

if (stristr($run_path, "admintools")) {
  echo $run_path;
  echo "\nStart aus dem falschen Verzeichnis heraus.\nAus html/ starten!\n";
  die();
}

// Datenbankconnect
include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
getMapSize($sx, $sy);
define(MAP_SIZE_X, $sx);
define(MAP_SIZE_Y, $sy);

// Wenn Startreihe gesetzt, dann weitermachen
if ((isset($x)) && (isset($y))) {
	$end=computeStartPositions($x,$y);
	if (($end['x']<MAP_SIZE_X) && ($end['y']<MAP_SIZE_Y)) 
	  echo "<meta http-equiv='refresh' content='0; URL='startpos.php?x=".$end['x']."&y=".$end['y']."'>";
}

if ((isset($x)) && (isset($y))) {
	echo "\r".($end['y']/MAP_SIZE_Y*100)."% ausgeführt";
}
else {
	echo "<a href='startpos.php?x=0&y=0'>Berechnung der Startpositionen starten</a>";
}


/**
 * Nun die eigentlichen Startpositionen generieren
 * @param $x
 * @param $y
 * @return unknown_type
 */
function computeStartPositions($x,$y) {
	// Farben definieren
	$img=ImageCreateFromPNG("admintools/colors.png");
	$grassland=imagecolorat($img,1,0);

	// Neuer Import gestartet -> alte Karte löschen
	if (($x==0) && ($y==0)) {
      mysql_query("TRUNCATE startpositions");      
    }

	// Zeit setzen
	$starttime=time();

	// Bild laden
	$img=imagecreatefrompng("admintools/map.png");

	$xy['x']=$x;
	$xy['y']=$y;

	// Schleife 120 Sekunden lang ausführen
	while ($starttime+120>time()) {
		unset($arr);
		$count=0;
		for ($j=$xy['y']+3;$j<$xy['y']+7;++$j) {
			for ($i=$xy['x']+3;$i<$xy['x']+7;++$i) {
				if (imagecolorat($img,$i,$j)==$grassland) {
					$arr[$count][0]=$i;
					$arr[$count][1]=$j;
					++$count;
				}
			}
		}
		if ($count>1) {
			$field=mt_rand(0,$count-1);
			mysql_query("INSERT INTO startpositions VALUES (".$arr[$field][0].",".$arr[$field][1].")");
		}
		$xy['x']+=10;
		if ($xy['x']==MAP_SIZE_X) {
			$xy['x']=0;
			$xy['y']+=10;
		}
		// wenn Ende erreicht, abbrechen
		if ($xy['y']==MAP_SIZE_Y) break;
	}
	return $xy;
}
echo "\n";
?>