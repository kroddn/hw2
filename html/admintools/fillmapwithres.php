<?php
define("CONSOLE", 0);

if(!function_exists("getpng")) {
  function getPNG($filename) {
    echo "Loading $filename<br>\n";
    $img = imagecreatefrompng($filename);
    if(!$img /*|| strlen($img) == 0*/)
      die ("Datei Fehlt: '$filename'\n");
    return $img;
  }
}

define("MAPNAME", "admintools/map.png");
define("SPECIALMAPNAME", "admintools/special.png");

$run_path = getenv("PWD");

if (stristr($run_path, "admintools")) {
  echo $run_path;
  echo "\nStart aus dem falschen Verzeichnis heraus.\nAus html/ starten!\n";
  die();
  
}
else {
  echo "foo\n";
  require_once("includes/db.config.php");
  require_once("import.config.php");
  require_once("admintools/map.func.php");

  session_name("admintools");
  session_start();

  // Zunächst die Map neu generieren
  //$map     = "kanada.png";
  $map = "map_root.png";
  //if (defined("NEW_MAP")) {
  //  $map = NEW_MAP;
  //} elseif (defined("MAP_DEFAULT")) {
  //  $map = MAP_DEFAULT;
  //}
  
  echo "<P>Kopiere Vorlagen von Karte und Spezial-Ressourcen\n";
  $to_copy = "maps/$map";
  echo "<P>cp $to_copy => ".MAPNAME."\n";
  unlink("admintools/map.png");
  if(!copy($to_copy, MAPNAME)) 
    die("Konnte $map nicht kopieren\n");
  

  $i = getPNG(MAPNAME);
  $sx = ImageSX($i);
  $sy = ImageSY($i);
  if(!defined("MAP_SIZE_X")) define("MAP_SIZE_X", $sx);
  if(!defined("MAP_SIZE_Y")) define("MAP_SIZE_Y", $sy);
  echo "Kartengröße: ".MAP_SIZE_X." x ".MAP_SIZE_Y."<br>\n";
  
  unlink(SPECIALMAPNAME);
  // Leeres PNG anlegen
  $special_res = imagecreatetruecolor($sx, $sy);
  imagecolortransparent($special_res);
  imagepng($special_res, SPECIALMAPNAME);
  imagedestroy($special_res); // aufräumen

  // Hauptprogramm
  $starttime = time();

  $percent_stone = isset($_GET['res_stone']) ? $_GET['res_stone'] : PERCENT_STONE;
  $percent_wood = isset($_GET['res_wood']) ? $_GET['res_wood'] : PERCENT_WOOD;
  $res_fish = isset($_GET['res_fish']) ? $_GET['res_fish'] : AMOUNT_SPECIAL_FISH;
  $res_pearls = isset($_GET['res_pearls']) ? $_GET['res_pearls'] : AMOUNT_SPECIAL_PEARLS;
  $res_wine = isset($_GET['res_wine']) ? $_GET['res_wine'] : AMOUNT_SPECIAL_WINE;
  $res_wheat = isset($_GET['res_wheat']) ? $_GET['res_wheat'] : AMOUNT_SPECIAL_WHEAT;
  $res_furbs = isset($_GET['res_furbs']) ? $_GET['res_furbs'] : AMOUNT_SPECIAL_FURBS;
  $res_herbs = isset($_GET['res_herbs']) ? $_GET['res_herbs'] : AMOUNT_SPECIAL_HERBS;
  $res_metal = isset($_GET['res_metal']) ? $_GET['res_metal'] : AMOUNT_SPECIAL_METAL;
  $res_gems = isset($_GET['res_gems']) ? $_GET['res_gems'] : AMOUNT_SPECIAL_GEMS;

  $amount = round((MAP_SIZE_X * MAP_SIZE_Y) * $percent_stone / 100);
  echo $percent_stone . "% Steine: " . $amount . "<br>";

  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10 );
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("mountain"),getcol("forest"),$amount/10);
  echo $amount." Steine erstellt!<br>\n";
 
  $amount = round((MAP_SIZE_X * MAP_SIZE_Y) * $percent_wood / 100);
  echo $percent_wood . "% Bäume: " . $amount . "<br>";

  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  fillmapwithres(getcol("grassland"),getcol("forest"),getcol("mountain"),$amount/10);
  echo $amount." Bäume erstellt!<br>\n";

  // Specialres eintragen
  fillmapwithspecialwaterres(getcol("water"),getcol("pearls"), $res_pearls);
  echo "Add ". $res_pearls . " Pearls<br>\n"; 
  fillmapwithspecialwaterres(getcol("water"),getcol("fish"), $res_fish);
  echo "Add ". $res_fish . " Fish<br>\n"; 

  fillmapwithspecialres(getcol("grassland"),getcol("wine"), $res_wine);
  echo "Add ". $res_wine . " Wine<br>\n"; 
  fillmapwithspecialres(getcol("grassland"),getcol("wheat"), $res_wheat);
  echo "Add ". $res_wheat . " Wheat<br>\n"; 
  fillmapwithspecialres(getcol("forest"),getcol("furs"), $res_furbs);
  echo "Add ". $res_furbs . " Furb<br>\n"; 
  fillmapwithspecialres(getcol("forest"),getcol("herbs"), $res_herbs);
  echo "Add ". $res_herbs . " Herbs<br>\n"; 
  fillmapwithspecialres(getcol("mountain"),getcol("metal"), $res_metal);
  echo "Add ". $res_metal . " Metal<br>\n"; 
  fillmapwithspecialres(getcol("mountain"),getcol("gems"), $res_gems);
  echo "Add ". $res_gems . " Gems<br>\n"; 

  $time = time() - $starttime;
  echo "\nAlle Eintragungen erfolgreich vorgenommen!\n<br>Dauer: $time sekunden<br>\n";
}

function getcol($col) {
  // Farben definieren
  if(!isset($_SESSION['init_cols'])) 
    {
      initColors();
    }
  
//   $img=getPNG("admintools/colors.png");


  return $_SESSION[$col];
}


function fillmapwithres($condition, $replacement, $exclude, $runs) {
  $water = $_SESSION['water'];

  // Bild laden
  echo "5 ";
  $img=getPNG(MAPNAME);
  
  $i = 0;

  // Schleife
  while ($i<$runs) {
    $x = mt_rand(0, MAP_SIZE_X);
    $y = mt_rand(0, MAP_SIZE_Y);
    $pixel = imagecolorat($img,$x,$y);
    if (($pixel == $condition) &&
    (imagecolorat($img,$x-1,$y) != $water) &&
    (imagecolorat($img,$x+1,$y) != $water) &&
    (imagecolorat($img,$x,$y-1)  != $water) &&
    (imagecolorat($img,$x,$y+1) != $water) &&
    //(imagecolorat($img,$x-1,$y-1) != $water) &&
    //(imagecolorat($img,$x+1,$y-1) != $water) &&
    //(imagecolorat($img,$x-1,$y+1) != $water) &&
    //(imagecolorat($img,$x+1,$y+1) != $water) &&
    (imagecolorat($img,$x-1,$y) != $exclude) &&
    (imagecolorat($img,$x+1,$y) != $exclude) &&
    (imagecolorat($img,$x,$y-1)  != $exclude) &&
    (imagecolorat($img,$x,$y+1) != $exclude)) //&&
    //(imagecolorat($img,$x-1,$y-1) != $exclude) &&
    //(imagecolorat($img,$x+1,$y-1) != $exclude) &&
    //(imagecolorat($img,$x-1,$y+1) != $exclude) &&
    //(imagecolorat($img,$x+1,$y+1) != $exclude))
    {
    	imagesetpixel($img,$x,$y,$replacement);
    	$i++;
    }

    if(CONSOLE) {
      echo "\rRun $i";
    }
  }
  //echo $i." ".$replacement." Res eingefügt!";
  imagepng($img, MAPNAME);
}


function fillmapwithspecialres($condition, $replacement, $runs) {
  global $current;

  // Bild laden
  //echo "1 ";
  $img=getPNG(MAPNAME);
  //echo "2 ";
  $specialimg=getPNG(SPECIALMAPNAME);
  $i = 0;

  // Schleife
  while ($i<$runs) {
  	//mt_srand((double)microtime() * 1000000);
    $x = mt_rand(0,MAP_SIZE_X);
    $y = mt_rand(0,MAP_SIZE_Y);
    $pixel = imagecolorat($img,$x,$y);
    if ($pixel == $condition)
    {
    	imagesetpixel($specialimg,$x,$y,$replacement);
    	$i++;
    }
  }
  //echo $i." Spezialres eingefügt!";
  imagepng($specialimg, SPECIALMAPNAME);
}

function fillmapwithspecialwaterres($condition, $replacement, $runs) {
  global $water, $current, $pearls, $fish, $wine, $wheat, $furs, $herbs, $metal, $gems;

  // Bild laden
  //echo "3 ";
  $img=getPNG(MAPNAME);
  
  //echo "4 ";
  $specialimg=getPNG(SPECIALMAPNAME);
  $i = 0;

  // Schleife
  while ($i<$runs) {
    mt_srand((double)microtime() * 1000000);
    $x = mt_rand(0,MAP_SIZE_X);
    $y = mt_rand(0,MAP_SIZE_Y);
    $pixel = imagecolorat($img,$x,$y);
    if (($pixel == $condition) && (
    	(imagecolorat($img,$x-2,$y)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x-2,$y+1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x-2,$y+2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x-1,$y+2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x,$y+2)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x+1,$y+2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x+2,$y+2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x+2,$y+1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x+2,$y)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x+2,$y-1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x+2,$y-2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x+1,$y-2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x,$y-2)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x-1,$y-2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x-2,$y-2) != $_SESSION['water']) ||
    	(imagecolorat($img,$x-2,$y-1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x-1,$y)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x-1,$y+1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x,$y+1)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x+1,$y+1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x+1,$y)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x+1,$y-1) != $_SESSION['water']) ||
    	(imagecolorat($img,$x,$y-1)   != $_SESSION['water']) ||
    	(imagecolorat($img,$x-1,$y-1) != $_SESSION['water'])
    	) &&
    	(
    	(imagecolorat($img,$x-2,$y)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-2,$y+1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-2,$y+2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-1,$y+2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x,$y+2)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+1,$y+2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+2,$y+2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+2,$y+1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+2,$y)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+2,$y-1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+2,$y-2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+1,$y-2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x,$y-2)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-1,$y-2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-2,$y-2) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-2,$y-1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-1,$y)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-1,$y+1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x,$y+1)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+1,$y+1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+1,$y)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x+1,$y-1) != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x,$y-1)   != $_SESSION['pearls']) ||
    	(imagecolorat($img,$x-1,$y-1) != $_SESSION['pearls'])
    	) 
    	&&
    	(
    	(imagecolorat($img,$x-2,$y)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-2,$y+1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-2,$y+2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-1,$y+2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x,$y+2)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+1,$y+2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+2,$y+2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+2,$y+1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+2,$y)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+2,$y-1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+2,$y-2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+1,$y-2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x,$y-2)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-1,$y-2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-2,$y-2) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-2,$y-1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-1,$y)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-1,$y+1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x,$y+1)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+1,$y+1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+1,$y)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x+1,$y-1) != $_SESSION['fish']) ||
    	(imagecolorat($img,$x,$y-1)   != $_SESSION['fish']) ||
    	(imagecolorat($img,$x-1,$y-1) != $_SESSION['fish'])
    	) 
//    	&&
//    	(
//    	(imagecolorat($img,$x-2,$y) != $wine) ||
//    	(imagecolorat($img,$x-2,$y+1) != $wine) ||
//    	(imagecolorat($img,$x-2,$y+2) != $wine) ||
//    	(imagecolorat($img,$x-1,$y+2) != $wine) ||
//    	(imagecolorat($img,$x,$y+2) != $wine) ||
//    	(imagecolorat($img,$x+1,$y+2) != $wine) ||
//    	(imagecolorat($img,$x+2,$y+2) != $wine) ||
//    	(imagecolorat($img,$x+2,$y+1) != $wine) ||
//    	(imagecolorat($img,$x+2,$y) != $wine) ||
//    	(imagecolorat($img,$x+2,$y-1) != $wine) ||
//    	(imagecolorat($img,$x+2,$y-2) != $wine) ||
//    	(imagecolorat($img,$x+1,$y-2) != $wine) ||
//    	(imagecolorat($img,$x,$y-2) != $wine) ||
//    	(imagecolorat($img,$x-1,$y-2) != $wine) ||
//    	(imagecolorat($img,$x-2,$y-2) != $wine) ||
//    	(imagecolorat($img,$x-2,$y-1) != $wine) ||
//    	(imagecolorat($img,$x-1,$y) != $wine) ||
//    	(imagecolorat($img,$x-1,$y+1) != $wine) ||
//    	(imagecolorat($img,$x,$y+1) != $wine) ||
//    	(imagecolorat($img,$x+1,$y+1) != $wine) ||
//    	(imagecolorat($img,$x+1,$y) != $wine) ||
//    	(imagecolorat($img,$x+1,$y-1) != $wine) ||
//    	(imagecolorat($img,$x,$y-1) != $wine) ||
//    	(imagecolorat($img,$x-1,$y-1) != $wine)
//    	)
//    	 &&
//    	(
//    	(imagecolorat($img,$x-2,$y) != $wheat) ||
//    	(imagecolorat($img,$x-2,$y+1) != $wheat) ||
//    	(imagecolorat($img,$x-2,$y+2) != $wheat) ||
//    	(imagecolorat($img,$x-1,$y+2) != $wheat) ||
//    	(imagecolorat($img,$x,$y+2) != $wheat) ||
//    	(imagecolorat($img,$x+1,$y+2) != $wheat) ||
//    	(imagecolorat($img,$x+2,$y+2) != $wheat) ||
//    	(imagecolorat($img,$x+2,$y+1) != $wheat) ||
//    	(imagecolorat($img,$x+2,$y) != $wheat) ||
//    	(imagecolorat($img,$x+2,$y-1) != $wheat) ||
//    	(imagecolorat($img,$x+2,$y-2) != $wheat) ||
//    	(imagecolorat($img,$x+1,$y-2) != $wheat) ||
//    	(imagecolorat($img,$x,$y-2) != $wheat) ||
//    	(imagecolorat($img,$x-1,$y-2) != $wheat) ||
//    	(imagecolorat($img,$x-2,$y-2) != $wheat) ||
//    	(imagecolorat($img,$x-2,$y-1) != $wheat) ||
//    	(imagecolorat($img,$x-1,$y) != $wheat) ||
//    	(imagecolorat($img,$x-1,$y+1) != $wheat) ||
//    	(imagecolorat($img,$x,$y+1) != $wheat) ||
//    	(imagecolorat($img,$x+1,$y+1) != $wheat) ||
//    	(imagecolorat($img,$x+1,$y) != $wheat) ||
//    	(imagecolorat($img,$x+1,$y-1) != $wheat) ||
//    	(imagecolorat($img,$x,$y-1) != $wheat) ||
//    	(imagecolorat($img,$x-1,$y-1) != $wheat)
//    	) 
//    	&&
//    	(
//    	(imagecolorat($img,$x-2,$y) != $furs) ||
//    	(imagecolorat($img,$x-2,$y+1) != $furs) ||
//    	(imagecolorat($img,$x-2,$y+2) != $furs) ||
//    	(imagecolorat($img,$x-1,$y+2) != $furs) ||
//    	(imagecolorat($img,$x,$y+2) != $furs) ||
//    	(imagecolorat($img,$x+1,$y+2) != $furs) ||
//    	(imagecolorat($img,$x+2,$y+2) != $furs) ||
//    	(imagecolorat($img,$x+2,$y+1) != $furs) ||
//    	(imagecolorat($img,$x+2,$y) != $furs) ||
//    	(imagecolorat($img,$x+2,$y-1) != $furs) ||
//    	(imagecolorat($img,$x+2,$y-2) != $furs) ||
//    	(imagecolorat($img,$x+1,$y-2) != $furs) ||
//    	(imagecolorat($img,$x,$y-2) != $furs) ||
//    	(imagecolorat($img,$x-1,$y-2) != $furs) ||
//    	(imagecolorat($img,$x-2,$y-2) != $furs) ||
//    	(imagecolorat($img,$x-2,$y-1) != $furs) ||
//    	(imagecolorat($img,$x-1,$y) != $furs) ||
//    	(imagecolorat($img,$x-1,$y+1) != $furs) ||
//    	(imagecolorat($img,$x,$y+1) != $furs) ||
//    	(imagecolorat($img,$x+1,$y+1) != $furs) ||
//    	(imagecolorat($img,$x+1,$y) != $furs) ||
//    	(imagecolorat($img,$x+1,$y-1) != $furs) ||
//    	(imagecolorat($img,$x,$y-1) != $furs) ||
//    	(imagecolorat($img,$x-1,$y-1) != $furs)
//    	) 
//    	&&
//    	(
//    	(imagecolorat($img,$x-2,$y) != $herbs) ||
//    	(imagecolorat($img,$x-2,$y+1) != $herbs) ||
//    	(imagecolorat($img,$x-2,$y+2) != $herbs) ||
//    	(imagecolorat($img,$x-1,$y+2) != $herbs) ||
//    	(imagecolorat($img,$x,$y+2) != $herbs) ||
//    	(imagecolorat($img,$x+1,$y+2) != $herbs) ||
//    	(imagecolorat($img,$x+2,$y+2) != $herbs) ||
//    	(imagecolorat($img,$x+2,$y+1) != $herbs) ||
//    	(imagecolorat($img,$x+2,$y) != $herbs) ||
//    	(imagecolorat($img,$x+2,$y-1) != $herbs) ||
//    	(imagecolorat($img,$x+2,$y-2) != $herbs) ||
//    	(imagecolorat($img,$x+1,$y-2) != $herbs) ||
//    	(imagecolorat($img,$x,$y-2) != $herbs) ||
//    	(imagecolorat($img,$x-1,$y-2) != $herbs) ||
//    	(imagecolorat($img,$x-2,$y-2) != $herbs) ||
//    	(imagecolorat($img,$x-2,$y-1) != $herbs) ||
//    	(imagecolorat($img,$x-1,$y) != $herbs) ||
//    	(imagecolorat($img,$x-1,$y+1) != $herbs) ||
//    	(imagecolorat($img,$x,$y+1) != $herbs) ||
//    	(imagecolorat($img,$x+1,$y+1) != $herbs) ||
//    	(imagecolorat($img,$x+1,$y) != $herbs) ||
//    	(imagecolorat($img,$x+1,$y-1) != $herbs) ||
//    	(imagecolorat($img,$x,$y-1) != $herbs) ||
//    	(imagecolorat($img,$x-1,$y-1) != $herbs)
//    	) 
//    	&&
//    	(
//    	(imagecolorat($img,$x-2,$y) != $metal) ||
//    	(imagecolorat($img,$x-2,$y+1) != $metal) ||
//    	(imagecolorat($img,$x-2,$y+2) != $metal) ||
//    	(imagecolorat($img,$x-1,$y+2) != $metal) ||
//    	(imagecolorat($img,$x,$y+2) != $metal) ||
//    	(imagecolorat($img,$x+1,$y+2) != $metal) ||
//    	(imagecolorat($img,$x+2,$y+2) != $metal) ||
//    	(imagecolorat($img,$x+2,$y+1) != $metal) ||
//    	(imagecolorat($img,$x+2,$y) != $metal) ||
//    	(imagecolorat($img,$x+2,$y-1) != $metal) ||
//    	(imagecolorat($img,$x+2,$y-2) != $metal) ||
//    	(imagecolorat($img,$x+1,$y-2) != $metal) ||
//    	(imagecolorat($img,$x,$y-2) != $metal) ||
//    	(imagecolorat($img,$x-1,$y-2) != $metal) ||
//    	(imagecolorat($img,$x-2,$y-2) != $metal) ||
//    	(imagecolorat($img,$x-2,$y-1) != $metal) ||
//    	(imagecolorat($img,$x-1,$y) != $metal) ||
//    	(imagecolorat($img,$x-1,$y+1) != $metal) ||
//    	(imagecolorat($img,$x,$y+1) != $metal) ||
//    	(imagecolorat($img,$x+1,$y+1) != $metal) ||
//    	(imagecolorat($img,$x+1,$y) != $metal) ||
//    	(imagecolorat($img,$x+1,$y-1) != $metal) ||
//    	(imagecolorat($img,$x,$y-1) != $metal) ||
//    	(imagecolorat($img,$x-1,$y-1) != $metal)
//    	) 
//    	&&
//    	(
//    	(imagecolorat($img,$x-2,$y) != $gems) ||
//    	(imagecolorat($img,$x-2,$y+1) != $gems) ||
//    	(imagecolorat($img,$x-2,$y+2) != $gems) ||
//    	(imagecolorat($img,$x-1,$y+2) != $gems) ||
//    	(imagecolorat($img,$x,$y+2) != $gems) ||
//    	(imagecolorat($img,$x+1,$y+2) != $gems) ||
//    	(imagecolorat($img,$x+2,$y+2) != $gems) ||
//    	(imagecolorat($img,$x+2,$y+1) != $gems) ||
//    	(imagecolorat($img,$x+2,$y) != $gems) ||
//    	(imagecolorat($img,$x+2,$y-1) != $gems) ||
//    	(imagecolorat($img,$x+2,$y-2) != $gems) ||
//    	(imagecolorat($img,$x+1,$y-2) != $gems) ||
//    	(imagecolorat($img,$x,$y-2) != $gems) ||
//    	(imagecolorat($img,$x-1,$y-2) != $gems) ||
//    	(imagecolorat($img,$x-2,$y-2) != $gems) ||
//    	(imagecolorat($img,$x-2,$y-1) != $gems) ||
//    	(imagecolorat($img,$x-1,$y) != $gems) ||
//    	(imagecolorat($img,$x-1,$y+1) != $gems) ||
//    	(imagecolorat($img,$x,$y+1) != $gems) ||
//    	(imagecolorat($img,$x+1,$y+1) != $gems) ||
//    	(imagecolorat($img,$x+1,$y) != $gems) ||
//    	(imagecolorat($img,$x+1,$y-1) != $gems) ||
//    	(imagecolorat($img,$x,$y-1) != $gems) ||
//    	(imagecolorat($img,$x-1,$y-1) != $gems)
//    	)
    	)
    {
    	imagesetpixel($specialimg,$x,$y,$replacement);
    	$i++;
    }
  }
  //echo $i." SpecialWaterRes eingefügt!";
  imagepng($specialimg,SPECIALMAPNAME);
}


if (1) 
{
}
?>