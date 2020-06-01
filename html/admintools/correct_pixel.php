<?php

require("admintools/import.config.php");
$mapname = "admintools/testmap2.png";
$specialmapname = "admintools/special.png";

$run_path = getenv("PWD");

if (stristr($run_path, "admintools")) {
  echo $run_path;
  echo "\nStart aus dem falschen Verzeichnis heraus.\nAus html/ starten!\n";
  die();
}
else {

  // Farben definieren
  $img1=ImageCreateFromPNG("admintools/colors.png");
  $water=imagecolorat($img1,0,0);
  $grassland=imagecolorat($img1,1,0);
  $forest=imagecolorat($img1,2,0);
  $mountain=imagecolorat($img1,3,0);
  $img2=imagecreatefrompng($mapname);
  $counter = 0;
  for($i=0;$i<MAP_SIZE_X;$i++) {
	  for($j=0;$j<MAP_SIZE_Y;$j++) {
	  	if((imagecolorat($img2,$i,$j) != $grassland) && (imagecolorat($img2,$i,$j) != $water)) {
	  	  echo imagecolorat($img2,$i,$j)." - ";
				imagesetpixel($img2,$i,$j,$grassland);
				echo imagecolorat($img2,$i,$j)." - Pixelfehler bei ($i|$j) - ".$grassland."\n";
				$counter++;
			}
		}
	}
	imagepng($img2,$mapname);
echo "\n$counter Pixelfehler korrigiert!\n";

}
?>