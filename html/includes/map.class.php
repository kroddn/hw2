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
require("includes/map.v3.class.php");

?>