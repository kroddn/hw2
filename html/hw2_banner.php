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
 
/****
 GRANT SELECT , INSERT , UPDATE ON `global`.`hw2banneraccess` TO 'hw2banner'@'%';
****/

// Auf der Main-Seite ein Banner ohne Klickaufforderung
$referer = $HTTP_SERVER_VARS['HTTP_REFERER'];
$ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
$request = $HTTP_SERVER_VARS['HTTP_HOST'].$HTTP_SERVER_VARS['REQUEST_URI'];


if(stristr($referer, "main.php") &&
   stristr($referer, "holy-wars2.de"))
{
  $bannername = "hw2_anibanner_intern.gif";
}
else {
  $bannername = "hw2_anibanner1.gif";
}

$f = fopen($bannername, "r");
if($f) {
  header("Content-Type: image/gif");
  header('Last-Modified: '.
         gmdate('D, d M Y H:i:s', filemtime($bannername)).' GMT');

  $content = fread($f, filesize($bannername) );
  fclose($f);  
  echo $content;
  flush();
}

// Den Referer nun noch in die DB eintragen
/* Nicht sicher wofür gerade gut - eventuel anpassen
TODO: Check -> Was hat es hiermit auf sich
@mysql_pconnect("85.10.202.10", "hw2banner", "GkV9TlQw8");
@mysql_select_db("banner");
$sql = sprintf("INSERT INTO global.hw2banneraccess (time, banner, ip, referer, request)".
               " VALUES (UNIX_TIMESTAMP(), '%s', '%s', %s, '%s')",
               mysqli_escape_string($GLOBALS['con'], $bannername),
               mysqli_escape_string($GLOBALS['con'], $ip),
               strlen($referer) > 0 ? "'".mysqli_escape_string($GLOBALS['con'], $referer)."'" : "NULL",
               mysqli_escape_string($GLOBALS['con'], $request)
             );

@mysqli_query($GLOBALS['con'], $sql);
*/               

die();
?>