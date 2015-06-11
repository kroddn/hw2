<?php
/***************************************************
 * Copyright (c) 2004-2008 by holy-wars2.de
 *
 * written by Markus Sinner <kroddn@psitronic.de>
 *
 * This File must not be used without permission
 ***************************************************/

//edit by obligaron:
//paramter für das include
//$what  Abfrage für mögliches WHERE, GROUP BY, SORT BY oder sonstwas (Beispiel: $what = "WHERE player.id = ".$_GET['uid'].";)
//$takecoloursby  Nach welchen kriterien er die Farbe vergeben soll. Dies geschieht per Modu, also muss es ein int Wert sein, der hochgezählt wird (Beispiel: $takecoloursby = "religion";)

include_once("includes/banner.inc.php");

start_page();
start_body(false);

// Die eigene ID wird öfter benötigt, besser lesbar
$my_pid = $_SESSION['player']->getID();


/*** HERE START ***/
if (!isset($what)) {
  echo "<b>Haha, ihr seid vielleicht ein Held! Welche Karte möchtet ihr denn gerne betrachten? Gebt gefälligst Infos an!<b></body></html>";
  die();
}
include_once("includes/util.inc.php");
getMapSize($mapsize_x, $mapsize_y);

$sql = "SELECT 
  player.id as ownerid, player.name as owner, player.religion as religion, relation.type as rel,
  city.name as name, city.id as id, 
  map.x as x, map.y as y
 FROM player
 LEFT JOIN relation ON (relation.id1 = ".$_SESSION['player']->id." and relation.id2 = player.id) OR (relation.id2 = ".$_SESSION['player']->id." and relation.id1 = player.id)
 LEFT JOIN city ON player.id=city.owner
 LEFT JOIN map ON city.id=map.id
".$what;

/* Werbe-Div*/
printf('<div align="center" style="position:absolute; z-index: 5; top: %d; left:20;">', $mapsize_y + 20);
//if($_SESSION['player']->isAdmin()) echo "<br>\n$sql";
if(!is_premium_noads()) {
  echo show_banner(0);
}
echo "</div>";

$worldcities = do_mysql_query($sql);

//  echo "\n<!-- ".$sql."-->\n";
// SQL-String für Clan-Diplomatie-Karte
echo "\n<!-- ".$clan_sql."-->\n";
if(!isset($_REQUEST['clan_sql']) && isset($GLOBALS['clan_sql'])) {
  $clancities = do_mysql_query($GLOBALS['clan_sql']);
}
else {
  $clancities = null;
}
  
// Ein Untergrundbild anlegen:


// Ein Array von Images, die bei unterschiedlichen Spielern die
// Erkennung einfach gestalten soll
$image[0] = "/clanmap0.gif";
$image[1] = "/clanmap1.gif";
$image[2] = "/clanmap2.gif";
$image[3] = "/clanmap3.gif";
$image[4] = "/clanmap4.gif";


// Von $acolor werden immer genau zwei Farben benutzt, $acolor[$i] und $acolor[$i+3]
$acolor[0] = "#FF0000";
$acolor[1] = "#AA0055";
$acolor[2] = "#008000";
$acolor[3] = "#FF0000";
$acolor[4] = "#FF5555";
$acolor[5] = "#109010";
$acolor[6] = "#FF55AA";
$acolor[7] = "#FFAA55";
$acolor[8] = "#FFAAFF";
$acolor[9] = "#FFFF55";
$acolor[10] = "#550000";
$acolor[11] = "#550055";
$acolor[12] = "#5500AA";
$acolor[13] = "#555500";
$acolor[14] = "#FF4000";
$acolor[15] = "#555555";
$acolor[16] = "#5555FF";
$acolor[17] = "#FF2040";
$acolor[18] = "#55AA00";
$acolor[19] = "#55AA55";
$i=0;

// edit franzl
// Sektoren und Quadranten
$width = 200;
$zindex = 1;

$sect=0;
$sectrow=0;
$quad=1;
$quadrow=0;
$quadmult=1;

$quad_count= $mapsize_y/200;

if(function_exists("getSettleRadius")) {
  $r = getSettleRadius();
  if($r*80 < $mapsize_x) 
    {
      $mid = intval($mapsize_x / 80);
      if($mapsize_x % 80 != 0)
        $cor = 1;
      else
        $cor = 0;
    }
  else
    unset($r);
}

/**
 * Den Hintergrund aufbauen
 */
if(defined("WORLD_MAP") && file_exists("maps/".WORLD_MAP) ) {
   $clanmap_path = "maps/".WORLD_MAP;
}
else {
   $clanmap_path = GFX_PATH_LOCAL."/clanmap.jpg";
}

echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"".$mapsize_x."\" height=\"".$mapsize_y."\" style=\"color:white; background-image:url('".$clanmap_path."');\">\n";
for($i = 1; $i <= $mapsize_x / 40; $i++) {
  $sectrow++;
  echo "\t<tr>\n";
  for($j = 1; $j <= $mapsize_y / 40; $j++) {
    if(isset($mid) && isset($r) &&
       $i <= $mid+$r+$cor && $j <= $mid+$r+$cor && $i>$mid-$r && $j>$mid-$r &&
       ($mid+$r+$cor == $i || $mid+$r+$cor == $j || $mid-$r+1 == $i || $mid-$r+1 == $j) 
       ) {
      $color = '#70AA70';
    }
    else { 
      $color = "white";
    }
	       
    $sect++;
    echo "\t\t<td $style width=\"40\" height=\"40\" onClick=\"opener.document.location.href = 'map.php?gox=".(($j*40)-40)."&goy=".(($i*40)-40)."'; \" onmouseover=\"this.style.backgroundColor = '#CCCCCC';\" onmouseout=\"this.style.backgroundColor = '';\" style=\"cursor:pointer; color:".$color."; border-left:1px solid white; border-bottom:1px solid white; text-align:center;\">Q: ".((($quadmult-1)*$quad_count)+$quad)."<br />S: ".($sect+($sectrow*5)-5)."</td>\n";
    if($sect==5) {$sect=0; $quad++;}
  }
  echo "\t</tr>\n";
  if($sectrow==5) {$sectrow=0;}
  $quadrow++;
  if($quadrow==5) {
    $quadmult++;
    $quad=1;
    $quadrow=0;
  } else { 
    $quad=1; 
  }
}
echo "</table>\n";

if (!isset($takecoloursby))
{
    $takecoloursby = "ownerid";
}

/**
 * Zunächst wird das Standard-SQL-Result durchiteriert.
 * Danach folgen eventuell zusätzliche "clancities".
 * 
 * $takecoloursby kann auf einen Wert gesetzt werden, der dann die
 * Wahl der Farbe bestimmt. Sinnvolle Werte sind 'id' oder 'rel',
 * falls es sich um eine Beziehungskarte handelt.
 *
 * $city benötigt die Assoc-Indizes:
 * - id
 * - name
 * - ownerid
 * - owner (name)
 * - x und y
 */
while( null != ($city = mysql_fetch_assoc($worldcities)) || $clancities != null && null != ($city =  mysql_fetch_assoc($clancities))  ) {  
  if($city[$takecoloursby] == null) {
    $i = 10;
  }
  else {
    $i=$city[$takecoloursby] % sizeof($acolor);
  }
  
  // Das DIV um die Grafik herum sorgt für die Anzeige der Tooltips
  echo "<div onmouseover=\"document.getElementById('a".$city['id']."').style.display = 'inline';document.getElementById('b".$city['id']."').style.display = 'inline';\" onmouseout=\"document.getElementById('a".$city['id']."').style.display = 'none';document.getElementById('b".$city['id']."').style.display = 'none';\" style='position:absolute; display:block; z-index:501; height:5px; top:".($city['y']-5)."; left:".($city['x']-5)."'>";

  
  echo " <a href='map.php?gox=".$city['x']."&goy=".$city['y']."' target=\"main\" onmouseup=\"\">";

  // Die eigentliche 'Grafik' für die Stadt wird aus Border und BGColor zusammengebaut
  $bg = $city['ownerid'] == $my_pid ? "#FFFFFF" : $acolor[$i];
  $border = $acolor[($i+3)%sizeof($acolor) ];
  printf('<img border="0" style="border: 2px solid %s; background-color: %s" width="4" height="4" src="%s/dummy.gif">', 
         $border, $bg, $imagepath);
  echo " </a></div>\n";
  
  // Der Tooltip folgt...
  // Position des Tooltips
  $top  = $city['y']+20;
  $left = $city['x']-20;
  echo "<div id=\"a".$city['id']."\" style='display:none; background-color:white; filter:Alpha(opacity=50, finishopacity=50, style=502); z-index:2; border:1px solid black; text-align:center; height:28px; width:160px; position:absolute; top:".$top."; left:".$left."'>\n";
  echo "<strong>".$city['name']."</strong> im Besitz von <strong>".$city['owner']."</strong>";
  echo "</div>\n";
  echo "<div id=\"b".$city['id']."\" style='display:none; border:1px solid black; text-align:center; height:28px; width:160px; z-index:503; position:absolute; top:".$top."; left:".$left."'>\n";
  echo "<strong>".$city['name']."</strong> im Besitz von <strong>".$city['owner']."</strong>";
  echo "</div>\n";
} // while

// Werbung nicht immer anzeigen, nur 2 von 12 Minuten
if (!is_premium_noads() ) {
  $timemod = time() % 12;

  switch($timemod) {
    case 1: include("ads/openinventory_layer.php"); break;
    case 2: include("ads/sponsorads-framelayer.php"); break;
  }
}

end_page();
?>
