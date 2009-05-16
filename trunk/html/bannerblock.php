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
*
*/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/banner.inc.php");

?>

<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<div align="center">
<h1 class="error">Informationen zur Bannerwerbung in Holy-Wars 2</h1>
<table width="600"><tr><td style="font-size:12px; font-family: Arial,sans-serif;" >
<hr>
Hier <b>sollte nun ein Werbebanner erscheinen</b> (zwischen den 2 Trennlinien direkt unter diesem Text):<br>
<? show_banner(0, true); ?>
<hr>
<br>
Erscheint hier <b>KEIN Banner</b>, dann benutzt Ihr Browser wohl einen Banner-Blocker.
Um Holy-Wars 2 wenigstens ein wenig zu Unterst�tzen sollten Sie sich bem�hen, diese
Banner-Blockade f�r Holy-Wars 2 aufzuheben.
<p>
<b>Der Hintergrund:</b><br>Die technische Ausstattung in Form von Servern verursacht regelm��ige Kosten, 
welche wir zum Gro�teil <b>aus Werbung finanzieren</b>. Mit dem regelm��igen Besuch 
unserer Sponsoren sichert Ihr den kostenlosen Betrieb von Holy-Wars II.<p>
<center>
<b>Schaut bitte regelm��ig bei unseren Sponsoren vorbei!</b>
</center>
<p>
<hr>
<p>
Des weiteren habt Ihr die M�glichkeit, Holy-Wars II durch den <a href="premium.php">Erwerb eines Premium-Accounts</a> zu Unterst�tzen. 
Ein solcher Account ist werbefrei und bietet - je nach H�he der Spende - zus�tzliche Vorteile. Unter anderem erscheint dann keine
Werbung mehr <img src="http://www.holy-wars2.de/images/smiles/icon_lol.gif">.
<hr>
</td></tr>

</table>




</div>
</body>
</html>
