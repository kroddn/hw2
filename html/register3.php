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
 * Copyright (c) 2003											
 *													
 * Stefan Neubert, Stefan Hasenstab					
 *													
 * This File must not be used without permission!
 ****************************************************/
include ("includes/db.inc.php");
include ("includes/config.inc.php");
?>
<html>
<head>
<title><? echo $pagetitle; ?> Registrierung durchgeführt</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="30" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<div align="center">
<table width="480" cellspacing="1" cellpadding="0" border="0">
	<tr>
		<td align="center">
		<h1>Registrierung durchgeführt</h1>
		<br>
		<br>
		</td>
	</tr>
	<?
if (isset($recruiter)) {
echo '<tr><td align="center">Sie wurden von Spieler '.$recruiter_name.' geworben</td></tr>';
}
?>
	<tr>
		<td align="center">Herzlichen Glückwunsch, ihr Account wurde
		erfolgreich angelegt. In einigen Minuten sollten sie eine <b>Email mit
		Ihrem Aktivierungskey erhalten</b>.	
		<br>		
        Der Account muss bis spätestens 48h nach Anmeldung aktiviert werden. Dies erreichen Sie, indem Sie
        die Schritte in Ihrer Empfangenen Email durchführen, oder die Aktivierung nach Anleitung auf der
        Hauptseite im Spiel durchführen. 
		</td>
	</tr>
    <tr>
        <td align="center">     
        <h1>Jetzt einloggen</h1>
        Die Account-Aktivierung können Sie später durchführen.
        <br>
        <a style="font-size: 13px;" href="login.php<?php if(isset($_REQUEST['name'])) echo "?name=".urlencode($_REQUEST['name']);  ?>"><b>Zum Login hier klicken</b></a>
        <br>
        <hr>      
        <br>
        </td>
    </tr>
	<tr>
		<td align="center">
		  <a href="activate.php"><b>Zur Aktivierung</b></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php"><b>Zum Portal</b></a>
		  <hr>		
		</td>
	</tr>
	<tr>
		<td align="left">
		<center><h1>Wußtet Ihr schon?</h1></center>
		Für das Werben neuer Spieler erhaltet Ihr Bonuspunkte. Diese könnt ihr
		nutzen für:
		<ul>
			<li>Kampfsimulator</li>
			<li>Spielen von Turnieren, in denen Gold zu gewinnen ist</li>
			<li>Einlösen von Premiumaccounts</li>
		</ul>
		Neben dem Werben neuer Spieler gibt es noch weiter Möglichkeiten, um
		an Bonuspunkte zu gelangen.
		<p>Weiter Informationen dazu finden sich im Spiel unter dem Menüpunkt
		&quot;MyHW2&quot; in der Rubrik &quot;Account-Einstellungen&quot;.
		
		
		<p><center><img src="/hw2_banner.php/1.gif"></center>
		
		</td>
	</tr>
</table>
</div>
</body>
</html>
