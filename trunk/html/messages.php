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
 * Copyright (c) 2015
 *
 * Christoph Waninger
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/messages.inc.php");

error_reporting(E_ALL);
$player = $_SESSION['player'];

$_SESSION['player']->setActivePage(basename(__FILE__));
start_page();
start_body();

//echo "Diese Seite ist erst im Aufbau! :P" . '<br />' . '<br />';

 
 // Zuletzt gewählte Ansicht in Session speichern
if(isset($show)) {
  $_SESSION['message_lastshow'] = $show;
}
else {
  if(isset($_SESSION['message_lastshow'])) {
    $show = $_SESSION['message_lastshow'];
  }
  else {
    $show = "read";
  }
}
?> 

<table cellspacing = "1" cellpadding="0" border="0">
  <tr height="20" class="tblhead">
    <td <? printActive("read"); ?> class="tblhead"><a href="<?php echo "$PHP_SELF?show=read"; ?>">Nachrichten lesen</a></td>
    <td <? printActive("write"); ?> class="tblhead"><a href="<?php echo "$PHP_SELF?show=write"; ?>">Nachrichten Verfassen</a></td>
    <td <? printActive("archiv"); ?> class="tblhead"><a href="<?php echo "$PHP_SELF?show=archiv"; ?>">Nachrichten-Archiv</a></td>
    <td <? printActive("settings"); ?> class="tblhead"><a href="<?php echo "$PHP_SELF?show=settings"; ?>">Nachrichten-Einstellungen</a></td>
	<?php if (get_show_fights($player->getID())==1) echo '<td '.printActive("settings").'class="tblhead"><a href="'."$PHP_SELF?show=fights".'">Kampfberichte/Einheits-Bewegungen</a></td>';?>
  </tr>
</table>

<div id="stats">
<?php
switch($show) {
  case "read":
	message_read($player->getID());
	//echo "Ist noch immer im Aufbau aber funktioniert";
    break;
	
  case "write":
    message_write($player->getID());
    //echo "Ist noch immer im Aufbau aber funktioniert";
    break;
	
  case "archiv":
	message_archiv($player->getID());
    //echo "Ist noch immer im Aufbau aber funktioniert";
    break;
	
  case "settings":
	message_settings($player->getID());
    //echo "Ist noch immer im Aufbau aber funktioniert";
    break;
	
  case "fights":
	message_show_fights($player->getID());
    //echo "Ist noch immer im Aufbau aber funktioniert";
    break;
	
  default:
    echo "Fehler.";
} // switch
?>
</div>


