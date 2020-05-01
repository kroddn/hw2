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


if( isset($_REQUEST['accept_inform']) )
{
  if(isset($_REQUEST['confirm']) && $_REQUEST['confirm'] ) {
    $sql = sprintf("SELECT * FROM inform LEFT JOIN inform_player ".
                   " ON (inform.infid = inform_player.infid AND inform_player.player = %d) ".
                   " WHERE inform_player.player IS NULL AND inform.infid = %d",
                   $_SESSION['player']->getID(), $_REQUEST['infid']);
    
    $res = do_mysqli_query($sql);
    if(mysqli_num_rows($res) > 0) {
      $sql = sprintf("INSERT INTO inform_player (infid, player, time) VALUES(%d, %d, UNIX_TIMESTAMP())",
                     $_REQUEST['infid'], $_SESSION['player']->getID() );
      do_mysqli_query($sql);
    }
    unset($error);
  }
  else {
    $error = "Setzen Sie ein Häkchen!";
  }
}

$sql = sprintf("SELECT inform.* FROM inform LEFT JOIN inform_player ".
                      " ON (inform.infid = inform_player.infid AND inform_player.player = %d) ".
                      " WHERE inform_player.player IS NULL ".
                      "  AND (expire IS NULL OR NOT expire < UNIX_TIMESTAMP())".
                      " ORDER BY inform.time",
               $_SESSION['player']->getID() );
$res = do_mysqli_query($sql);
if(mysqli_num_rows($res) > 0 ) {
  $inform = mysqli_fetch_assoc($res);
 
  show_inform($inform);
  exit();
}

function show_inform($inf) {
  start_page();
  start_body();
?>
<h1>Wichtige Information</h1>
Bitte lesen Sie nachfolgende Informationen durch und bestätigen
Sie die Kenntnisnahme durch Setzen eines Häkchens bzw. Ankreuzen
der entsprechenden Schaltfläche.
<p>
<hr>
<?
  if(isset($GLOBALS['error']) && strlen($GLOBALS['error']) > 0) {
    echo '<b class="error">'.$GLOBALS['error'].'</b><p>';
  }
  
  echo "Datum: ".date("d.m.Y G:i", $inf['time'])."<br>\n";
  echo "Betreff: <b>".$inf['topic']."</b><p>\n";
  
  echo '<div id="inform" style="width: 500px;">';
  echo $inf['text'];
  echo "</div>";
?>  
  <form method="GET" action="<? echo $PHP_SELF;?>">
  <input type="hidden" name="infid" value="<? echo $inf['infid']; ?>">
  <input type="checkbox" name="confirm" value="1"> <input type="submit" name="accept_inform" value=" Gelesen ">
  </form>
<?
end_page();
}
?>