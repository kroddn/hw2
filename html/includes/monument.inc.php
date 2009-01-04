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
 * written by Markus Sinner
 ***************************************************/

function print_monuments ($gox, $goy) {
  global $imagepath;
  
  
  $width  = 250;
  $height = 250;

  $mons = do_mysql_query( 'SELECT * '.
                          'FROM player_monument '.
                          'LEFT JOIN monument USING(m_id) '.
                          'LEFT JOIN player ON player.id = player_monument.player '.
                          'WHERE x >= '.$gox.' AND x <= '.($gox+39).' AND y >= '.$goy.' AND y <= '.($goy+39).' '.
                          'ORDER BY y,x');
    
  while ( $mon = mysql_fetch_array($mons)) {
    echo '<div id="" style="position:absolute;left:'.($mon['x'] - $width/2).'; top:'.($mon['y']).'; z-index:400">';
    echo '<table border="0" width="'.$width.'" height="'.$height.'" background="'.$imagepath.'/monument/'.$mon['m_id'].'.gif">';
    echo '<tr><td width="65">&nbsp;</td><td width="125" align="center" valign="bottom"><font size="-1" color="grey" ><b>'.
      $mon['text'].
      "auf ".$mon['x'].":".$mon['y'].
      '</b></font></td><td width="60">&nbsp;</td></tr>';
    echo '</table>';
    echo "\n</div>\n";
  } // while
} // function print_monuments ()

?>
