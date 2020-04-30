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
* Copyright (c) 2005
*
* written by Markus Sinner <kroddn@psitronic.de>
*/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/banner.inc.php");
include_once("includes/session.inc.php");
?>

<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<div align="center">
<table width="600"><tr><td height="250" valign="center">
<hr>
<div align="center"><h1 class="error">Reichtum? St�rke? Ruhm!!</h1></div>
<font size="+1">
<b>Hast Du bereits weitere Spieler geworben?</b> Wenn ja, hast Du das auch mit deinem <a href="settings.php?show=account">
Werbe-Link unter dem Men�punkt Einstellungen</a> getan? Dafür gibt es n�mlich eine Belohnung: <b>Bonuspunkte</b>!
<p>
<center>Dein Link lautet: 
<input type="text" size="50" value="https://<? echo $_SERVER['HTTP_HOST'];?>/register.php?ref=<? echo $player->getID(); ?>"></center>
<p>
Sobald sich ein Bekannter, Freund oder sonstwer unter dieser Adresse anmeldet, bekommt Ihr Bohnunspunkte gutgeschrieben.
</font>
<hr>
</td></tr>
<tr height="20"></tr>
<tr><td>
<center>
<font size="+1"><b>Werbeanzeige:</b></font>
<p>
<? show_banner(0); ?>
</center>
<p>
Holy-Wars II ist zwar ein kostenloses Browserspiel, aber für uns Betreiber keinesfalls kostenfrei! 
Die technische Ausstattung in Form von Servern verursacht regelmäßige Kosten, 
welche wir zum Großteil <b>aus Werbung finanzieren</b>.</font><p>
<hr>
<p>
<center>Vergesst bitte nicht, <b>regelmäßig</b> für uns zu voten:<p>
<? //include ("includes/vote.inc.php");?>

</font>
<p>
<hr>
<p>
<center><a href="javascript:window.location.reload()">Weiter gehts hier (letzte Aktion wiederholen)!</a></center>

</td></tr>

</table>
</div>
</body>
</html>
