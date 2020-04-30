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

// Diese Seite nicht zu den Clicks zählen
$GLOBALS['noclickcount'] = 1;

include("includes/db.inc.php");
require("includes/cities.class.php");
require("includes/player.class.php");
include("includes/session.inc.php");

/**
 * Timer nur anzeigen, wenn Benutzer das nicht deaktiviert hat.
 * @return unknown_type
 */
function session_timer() { 
  if(!$_SESSION['settings']['disable_login_counter']) {
    $remaining =  $_SESSION['session_duration'] + $_SESSION['login_time'] - time();
    echo "<span class='statusline' id='session_remaining'><script type=\"text/javascript\">addTimer(".$remaining.",'session_remaining');</script></span>";
  }
}


if (isset($citylist)) {
  $_SESSION['cities']->setActiveCity($citylist);
}

$not_actualize[0] = "map.php";
$not_actualize[1] = "clan.php";
$not_actualize[2] = "kingdom.php";
$not_actualize[3] = "toplist.php";
$not_actualize[4] = "info.php";
$not_actualize[5] = "diplomacy.php";
$not_actualize[6] = "settings.php";
$not_actualize[7] = "library.php";
$not_actualize[8] = "research.php";
$not_actualize[9] = "messages.php";
$not_actualize[10]= "messages2.php";
//$not_actualize[2] = "marketplace.php";

?>

<html>
<head>
<title><? echo $pagetitle; ?></title>
<script language="JavaScript" src="js/timer.js"></script>
</head> 
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css"> 
<body class="nopadding" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif"> 
<table cellspacing="0" cellpadding="0" border="0"> 
<tr> 
<td rowspan="2" height="25" width="30" class="nopadding" nowrap background="<? echo $imagepath; ?>/border_bottomleft.gif"></td> 
<td colspan="11" height="5"></td> 
<td rowspan="2" height="25" width="30" class="nopadding" nowrap background="<? echo $imagepath; ?>/border_bottomright.gif"></td> 
</tr> 
<tr> 
<td height="20" class="nopadding" background="<? echo $imagepath; ?>/border_bottom.gif">
<select class="statusline" name="test" background="<? echo $imagepath; ?>/border_bottom.gif"
onChange="location.href='borderbottom.php?citylist='+this.value+''<? if ( array_search($player->getActivePage(), $not_actualize) === false) echo "; parent.main.location.href='locator.php'"; ?>">
<?

$citylist=$_SESSION['cities']->getCities();
if(sizeof($citylist) < 1) {
  $_SESSION['cities']->updateCities();
  $citylist=$_SESSION['cities']->getCities();
}

$acity=$_SESSION['cities']->getActiveCity();
for ($i=0;$i<sizeof($citylist);++$i) {
	echo "<option class='statusline' value='".$citylist[$i]['id']."' ";
	if ($acity==$citylist[$i]['id']) echo "selected";
	echo ">".$citylist[$i]['name']."</option>";
}
?>
</select>
</td>
<td height="20" width="15" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_bottom.gif"></td>

<td align="center" height="20" nowrap background="<? echo $imagepath; ?>/border_bottom.gif"><a class="statusline" target="main" href="messages.php?show=read"><b><? echo $player->getNewMessages(); ?></b></a></td>
<td align="center" height="20" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_bottom.gif"><a target="main" href="messages.php?show=read"><img border="0" alt="Nachrichten" src="<? echo $imagepath; ?>/message.gif"></a></td>
<td align="center" height="20" nowrap background="<? echo $imagepath; ?>/border_bottom.gif">&nbsp;<a class="statusline" target="main" href="research.php"><b><? echo $player->getRp(); ?></b></a></td>
<td align="center" height="20" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_bottom.gif"><a target="main" href="research.php"><img border="0" alt="Forschungspunkte" src="<? echo $imagepath; ?>/rp.gif"></a></td>
<td height="20" width="15" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_bottom.gif"></td>
<td height="20" width="35" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_outgoingright.gif"></td>
<td height="20" width="100%" nowrap class="nopadding"></td>
<td height="20" width="35" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_outgoingleft.gif"></td>
<td align="center" height="20" nowrap class="nopadding" style="padding-left: 2px; padding-right: 2px;" background="<? echo $imagepath; ?>/border_bottom.gif">
<a title="Dies ist die maximale Zeit, die Ihr eingeloggt bleiben könnt." class="statusline" target="_parent" href="navigation.php?logout=1">Logout
&nbsp;&nbsp;<? session_timer(); ?></a>
</td>
</tr>
</table>
</body>
</html>
