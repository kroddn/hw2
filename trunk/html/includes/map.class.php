<?php
/***************************************************
 * Copyright (c) 2004
 *
 * Stefan Neubert, Stefan Hasenstab
 *
 * This File must not be used without permission!
 ***************************************************/

/* Der ganze Unterscheidungskram geht so eh nicht 
if (isset($player)) {
  $version = $player->getMapVersion();
  // echo "<!-- Player set. Map Version $version -->\n";
}
else {
  $version = MAP_VERSION;
  // echo "<!-- Player NOT set. Map Version $version -->\n";
}
*/
// Abhängig von der Spielereinstellung eine Karte einpappen

//include("includes/map.v0.class.php");
//include("includes/map.v1.class.php");
//include("includes/map.v2.class.php");
include("includes/map.v3.class.php");

?>