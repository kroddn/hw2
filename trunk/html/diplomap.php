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
 * Copyright (c) 2007 by Holy-Wars 2
 *
 * written by Markus Sinner <kroddn@psitronic.de>
 *
 * This File must not be used without permission
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");



if(is_premium_diplomap() || $_SESSION['player']->getNoobLevel() > 0 || TEST_MODE && $test == 1) {
  $what = "WHERE city.name IS NOT NULL AND (relation.type=0 OR relation.type=2 OR (player.id = ".$_SESSION['player']->id." AND relation.type IS NULL))";

  // Wähle die Farben anhand der Beziehung.
  $takecoloursby = 'rel';

  // Zweite Abfrage, für die clan-relations
  if($_SESSION['player']->clan != null) {
    $clanid = $_SESSION['player']->clan;

    // Der SQL-String wird zusammengebaut aus den Feinden und Freunden.
    // 
    $GLOBALS['clan_sql'] = "
      (
		SELECT 14 AS rel, map.x, map.y, city.id, city.owner AS ownerid, city.name AS name, player.name AS owner 
 		FROM city LEFT JOIN player ON player.id = city.owner LEFT JOIN map ON map.id = city.id
		WHERE player.clan IN 
		(SELECT id1 FROM clanrel WHERE id2 = $clanid AND type = 0
			UNION 
 	     SELECT id2 FROM clanrel WHERE id1 = $clanid AND type = 0)
	   )
	UNION
      (
		SELECT 2 AS rel, map.x, map.y, city.id, city.owner AS ownerid, city.name AS name, player.name AS owner 
 		FROM city LEFT JOIN player ON player.id = city.owner LEFT JOIN map ON map.id = city.id
		WHERE player.clan IN 
		(SELECT id1 FROM clanrel WHERE id2 = $clanid AND type = 3
			UNION 
 	         SELECT id2 FROM clanrel WHERE id1 = $clanid AND type = 3
                ) OR player.clan = $clanid
	   )
	";
  }
  
  include_once("includes/worldmap.inc.php");
}
else {
  start_page();
  start_body();
     
  echo '<h1 class="error">Diese Funktion ist Spielern mit Premium-Account und Spielern im Neulingsschutz vorbehalten.</h1>';
  if(TEST_MODE)
    echo '<a href="diplomap.php?test=1">In der Testrunde hier klicken.</a>'; 
}

end_page();
?>