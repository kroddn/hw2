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
* Copyright (c) 2004-2006 by Holy-Wars 2
*
* written by Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/util.inc.php");

function print_news($limit) {
  $limit = intval($limit);
  if($limit < 8) 
    $limit = 8;
    
  echo '<table align="center" border="0" width="90%"><tr><td>';
  $res = do_mysql_query ("SELECT id,text,topic,from_unixtime(time) as time FROM news ORDER BY id DESC LIMIT ".$limit);
  if (mysql_num_rows($res)<1) {
    echo "Keine News!";
  }
  else {
    echo "<ul style=\"padding-left: 5px;\">\n";
    while ($news = mysql_fetch_assoc($res)) {
      echo "<li><b>".$news['topic']."</b> (".$news['time'].")<br>".$news['text'];      
    } // while
    echo "</ul>\n";
  } // else

  echo "</td></tr></table>\n";
} // function
?>