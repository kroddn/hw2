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
 * Copyright (c) 2005-2008
 *
 * Gordon Meiser
 * Markus Sinner
 *
 ***************************************************/
// Navigation nicht zu den Klicks hinzuzählen
$GLOBALS['noclickcount'] = 1;

include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");

$chattarget = $libtarget = $forumtarget = $maptarget = 'target="main"';

if ($_SESSION['settings']['map_own'])
     $maptarget = 'target="_blank"';
if ($_SESSION['settings']['forum_own'])
     $forumtarget = 'target="_blank"';
if ($_SESSION['settings']['library_own'])
     $libtarget = 'target="_blank"';

?>
<html>
<head>
<title><? echo $pagetitle; ?></title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 <meta http-equiv="imagetoolbar" content="no">
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body class="nopadding" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/left.gif">

<img src="<? echo $imagepath; ?>/menu_line.gif"><br>
<a target="main" href="main.php"><img alt="Home" border="0" src="<? echo $imagepath; ?>/menu_home.gif"></a><br>
<a target="main" href="premium.php"><img alt="Premium-Account" border="0" src="<? echo $imagepath; ?>/menu_premium.gif"></a><br>
<a target="main" href="toplist.php"><img alt="Toplist" border="0" src="<? echo $imagepath; ?>/menu_toplist.gif"></a><br>
<a <? echo $forumtarget; ?> href="http://forum.holy-wars2.de"><img alt="Forum" border="0" src="<? echo $imagepath; ?>/menu_forum_klein.gif"></a><img src="<? echo $imagepath; ?>/menu_slash.gif""><a <? echo $chattarget; ?> href="chat.php"><img alt="Chat" border="0" src="<? echo $imagepath; ?>/menu_chat.gif"></a><br>
<a target="main" href="settings.php"><img title="Diverse Einstellungen zu Account, Spieler, Grafik und Menü" alt="My Holy-Wars 2" border="0" src="<? echo $imagepath; ?>/menu_settings.gif"></a><br>
<img src="<? echo $imagepath; ?>/menu_balken1.gif"><br>
<a target="main" href="kingdom.php"><img alt="Finanzhof" border="0" src="<? echo $imagepath; ?>/menu_kingdom.gif"></a><br>
<a target="main" href="messages.php"><img alt="Nachrichten" border="0" src="<? echo $imagepath; ?>/menu_messages.gif"></a><br>
<a target="main" href="diplomacy.php"><img alt="Diplomatie" border="0" src="<? echo $imagepath; ?>/menu_diplomacy.gif"></a><br>
<a target="main" href="clan.php"><img alt="Orden" border="0" src="<? echo $imagepath; ?>/menu_clan.gif"></a><br>
<a <? echo $maptarget; ?> href="map.php"><img alt="Karte" border="0" src="<? echo $imagepath; ?>/menu_map.gif"></a><br>
<a target="main" href="marketplace.php"><img alt="Markt" border="0" src="<? echo $imagepath; ?>/menu_marketplace.gif"></a><br>
<a target="main" href="research.php"><img alt="Forschung" border="0" src="<? echo $imagepath; ?>/menu_research.gif"></a><br>
<a <?echo $libtarget; ?> href="library.php"><img alt="Biblio" border="0" src="<? echo $imagepath; ?>/menu_library.gif"></a><br>
<img src="<? echo $imagepath; ?>/menu_balken1.gif"><br>
<a target="main" href="buildings.php"><img alt="Gebäude" border="0" src="<? echo $imagepath; ?>/menu_buildings.gif"></a><br>
<a target="main" href="barracks.php"><img alt="Kaserne" border="0" src="<? echo $imagepath; ?>/menu_barracks.gif"></a><br>
<a target="main" href="general.php"><img alt="Generalstab" border="0" src="<? echo $imagepath; ?>/menu_general.gif"></a><br>
<a target="main" href="townhall.php"><img alt="Rathaus" border="0" src="<? echo $imagepath; ?>/menu_townhall.gif"></a><br>
<?php 
if(!defined("HISPEED") || !HISPEED) {  
  echo '<a target="main" href="tournament.php"><img alt="Abenteuer" border="0" src="'.$imagepath.'/menu_adventure.gif"></a><br>';
}
?>
<img src="<? echo $imagepath; ?>/menu_balken2.gif"><br>
</body>
</html>

