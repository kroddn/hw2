<?
header("Content-type: text/javascript");

?>
function chgfx(elem, gfx) {
  elem.src=gfx;
}


function framelayer(href) {
  tip = document.getElementById("tooltip");
  tip.style.visibility="hidden";

  hl = document.getElementById("headline");
  hl.style.visibility="hidden";


  fl = document.getElementById("framelayer");
  fl.style.visibility="visible";

  lif=document.getElementById("layeriframe");
  lif.src=href;

  // Return false, damit die onClick den Link-Aufruf abbricht
  return false;
}


function hideFrameLayer() {
  fl = document.getElementById("framelayer");
  fl.src="opening.php";
  fl.style.visibility="hidden";

  tip = document.getElementById("tooltip");
  tip.style.visibility="visible";
  
  hl = document.getElementById("headline");
  hl.style.visibility="visible";
}


function tooltip(input) {
  tip = document.getElementById("tooltip");

  if(isNaN(input)) {
    tip.innerHTML = input;
  }
  else {
    id = input;
           
    switch(id) {
    case 1: // Game 1
    case 2: // Game 2
    case 3: // Speed
    case 22: // Runde läuft aus
      tip.innerHTML = tooltips[id];
      break;
    case 0:
    default:  
      tip.innerHTML = default_tooltip;
    }
  }
}

var tooltips = new Array();

<?php
include_once("includes/db.inc.php");
include_once("includes/db.class.php");
$db = new DB("mysql");

// Abfrage der Spielerunden
// Nicht vergessen, Neueinträge oben im JavaScript hinzuzufügen!
$rounds = array(1 => "hw2_game1", 
                2 => "hw2_game2", 
                3 => "hw2_speed",
                22=> null);

foreach($rounds AS $id => $database) {
  if($id == 22) {
    $text = "Diese Runde ist beendet. Derzeit ist kein Neustart geplant, game1 läuft allerdings weiter.";        
  }
  else if($database == null) {
    $text = "Diese Runde startet am 08.09.2007";    
  }
  else {
    $sql = sprintf("SELECT value FROM %s.config WHERE name = 'starttime'", $database);
    $starttime = $db->query_fetch_array($sql);
    $sql = sprintf("SELECT value FROM %s.config WHERE name = 'roundname'", $database);
    $roundname = $db->query_fetch_array($sql);
    $sql = sprintf("SELECT value FROM %s.config WHERE name = 'tick'", $database);
    $tick = $db->query_fetch_array($sql);
    $sql = sprintf("SELECT value FROM %s.config WHERE name = 'settleradius'", $database);
    $radius = $db->query_fetch_array($sql);
    $sql = sprintf("SELECT max(x)+1 AS y, max(y)+1 AS y FROM %s.map", $database);
    $mapsize = $db->query_fetch_array($sql);

    if($starttime[0] > time()) {
      $players['christ'] = $players['moslem'] = 1;
      $cities[0] = 2;
    }
    else {
      $sql = sprintf("SELECT count( IF(religion=1, 1, NULL) ) AS christ, count( IF(religion=2, 1, NULL)) AS moslem ".
                     "FROM %s.player WHERE religion IS NOT NULL AND activationkey IS NULL AND (status IS NULL OR status=3)", $database);
      $players = $db->query_fetch_assoc($sql);
      $sql = sprintf("SELECT count(*) FROM %s.city", $database);
      $cities = $db->query_fetch_array($sql);
    }
    
    $text = sprintf("Die Runde <span class=\"fat_u\">%s</span> %s %s.<p>".
                    "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">".
                    "<tr><td>Ticklänge:</td><td>%d Minuten</td></tr>".
                    "<tr><td>Kartengröße:</td><td>%d x %d</td></tr>".                    
                    "<tr><td>Siedlungsradius:</td><td>%d Sektoren</td></tr>".
                    "<tr><td>Christen:</td><td>%d</td></tr>".
                    "<tr><td>Moslems:</td><td>%d</td></tr>".
                    "<tr><td>Siedlungen:</td><td>%d</td></tr>".
                    "</table>",
                    $roundname[0] ? $roundname[0] : $database, 
                    $starttime[0] > time() ? "startet am" : "l&auml;uft seit",
                    date("d.m.y", $starttime[0]),
                    $tick[0]/60,
                    $mapsize[0], $mapsize[1],
                    $radius[0],
                    $players['christ'], $players['moslem'],
                    $cities[0]);
    
    $text = str_replace("'", "\'", $text);
    
  }

  printf("tooltips[%d] = '%s';\n", $id, $text);
}

$default_tooltip = "F&uuml;hrt Euer Zeigeger&auml;t (Maus) &uuml;ber ein Symbol auf dem Schild oder &uuml;ber eine andere Schaltfl&auml;che.";

echo "var default_tooltip = '".$default_tooltip."';\n";
?>
