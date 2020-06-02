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

include("includes/db.inc.php");
include("includes/util.inc.php");

if(!isset($radius)) $radius = getSettleRadius();


  
//getMapSize($tx, $ty);

// Einzeichnen.
if(isset($_REQUEST['map'])) {
  $remote = FALSE !== stristr($_REQUEST['map'], "https://");
  if($remote) {    
    die("Forbidden: no remote maps allowed");
  }

  $img = getPNG($_REQUEST['map']);
}
else {
  $img = getPNG("maps/map.png");
}

$sx = ImageSX($img);
$sy = ImageSY($img);

//if($radius > $sx/80 ) $radius = intval( ($sx+40)/80);


if($_REQUEST['img'] == 1) {
  
  for($reli = 1; $reli <= 2; $reli++) {
    for($p = 0; $p < 3; $p++) {
      $where = getStartPos($p, $reli, $radius, $sx, $sy);
      // $count = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM startpositions WHERE ".$where);
      if($reli == 1) $loc[$p]   = $where;
      else           $loc[5-$p] = $where;
    }
  }

$cols = Array(
  Array(0x40, 0x5F, 0x5F),
  Array(0x00, 0x2E, 0x2E),
  Array(0xFF, 0x10, 0x10),
  Array(0xFF, 0x10, 0x10),
  Array(0xFF, 0xCC, 00),
  Array(0xFF, 0xFF, 10),
);

$strings = Array("Chr. Hinterland", "", "Kriegsgebiet", "Kriegsgebiet", "", "Isl. Hinterland");

for($i=0; $i<6; $i++) {
  $color = imagecolorallocate($img, $cols[$i][0], $cols[$i][1], $cols[$i][2]);
  foreach($loc[$i] AS $l) {
    imagerectangle($img, $l[0],   $l[1],   $l[2]-1, $l[3]-1, $color);
    imagerectangle($img, $l[0]+1, $l[1]+1, $l[2]-2, $l[3]-2, $color);
    imagerectangle($img, $l[0]+2, $l[1]+2, $l[2]-3, $l[3]-3, $color);
    imagerectangle($img, $l[0]+3, $l[1]+3, $l[2]-4, $l[3]-4, $color);
    imagestring($img, 5, $l[0]+4, $l[1]+4, $strings[$i], 0); 
  }
}

header("content-type: image/png");
ImagePNG($img);

}
else {
  $ref = "settlemap.php?img=1&radius=$radius";
  if(isset($_REQUEST['map'])) {
    $ref .= "&map=".$_REQUEST['map'];
  }

 ?> 
  <html><body>
  <form>
  <input name="radius" value="<? echo $radius;?>"/>
  <input type="submit" value=" Radius anzeigen "/>
  <? if(isset($_REQUEST['map'])) echo '<input type="hidden" name="map" value="'.$_REQUEST['map'].'"/>'; ?>
  <input type="button" value="-" onClick="form.radius.value = parseInt(form.radius.value) - 1;"/>
  <input type="button" value="+" onClick="form.radius.value = parseInt(form.radius.value) + 1;"/>
  <input type="button" value="Default" onClick="form.radius.value = <? echo getSettleRadius(); ?> ;"/>
  Der aktuelle Radius ist: <? echo getSettleRadius(); ?>
  </form>
  
  <img src="<? echo $ref; ?>"/>
  <p>
  <pre>
<?
      if(false) {
      var_dump( getStartPos(0, 1) );
      var_dump( getStartPos(1, 1) );
      var_dump( getStartPos(2, 1) );
      var_dump( getStartPos(2, 2) );
      var_dump( getStartPos(1, 2) );
      var_dump( getStartPos(0, 2) );
      }
?>
  </pre>
  <p>
  </body></html>

<? 
}




function getPNG($filename) {
  $img = imagecreatefrompng($filename);
  if(!$img)
    die ("Datei Fehlt: '$filename'\n");
  
  //    fclose($f);
  return $img;
}

?>