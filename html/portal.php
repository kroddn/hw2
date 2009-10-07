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

require_once("start_header.php");

?>

<tr valign="top" style="padding: 0px; margin: 0px;">
	<td width="24%" valign="top" style="padding: 0px; margin: 0px;"><?php
	$res1=do_mysql_query("SELECT player.clanstatus as clstatus, player_online.uid AS id, player.clan AS clan, player.name AS name, player_online.lastclick AS click FROM player_online LEFT JOIN player ON player_online.uid=player.id WHERE player_online.lastclick >= ".(time()-300));
	echo "<table style=\"td {padding:3px;} margin-bottom:5px;\" width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
	echo "<tr class=\"tblhead\"><td height=\"20\" valign=\"middle\" colspan=\"3\"><b>Spieler online</b></td></tr>\n";
	echo "<tr class=\"tblbody\"><td height=\"30\" valign=\"middle\" colspan=\"3\">";
	if(mysql_num_rows($res1) == 1)
		echo "Momentan ist <b>ein Spieler</b> online!";
	elseif(mysql_num_rows($res1) > 1)
		echo "Momentan sind <b>".mysql_num_rows($res1)."</b> Spieler online!";
	else
		echo "Momentan sind <b>keine</b> Spieler online!";
	echo "</td></tr></table>";

bestplayer_table();
bestclan_table();
zitat_table();
?></td>

	<td width="52%" valign="top" align="center"><!-- Middle Content / Story -->
	<table cellspacing="1" cellpadding="0" width="100%"
		style="border: 1px solid #D7D796;">
		<tr class="tblhead" valign="middle">
			<td height="25" align="center"><span style="font-size: 12px;"><b>Ohne</b>
			Download im Browser spielbar. <a style="color: red"
				href="register.php">Jetzt <u>kostenlos</u> anmelden</a>!</span> <? playing_div(); ?>
			</td>
		</tr>
		<tr class="tblbody">

			<td>
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td><font style="font-family: Tahoma; font-size: 11px;"><b><i>Vorbei
					geht Ihr an den Bogen- und Armbrustschützen, die Euren
					heimkehrenden Reitern den Rückzug decken. Doch kein Gegner wagt
					sich in die Reichweite der Pfeile. Noch nicht. Weniger als die
					Hälfte eurer Männer ist zurückgekommen. Der Ausbruch war
					fehlgeschlagen. Nun war die Stadt eingekreist. Ihr geht die
					Doppelmauer entlang, klopft hier eine Schulter, reißt dort einen
					Scherz. Und die Männer schöpfen für den Augenblick Mut.
					Verzweifelte Hoffnung erfüllt ihre Augen und doch verschwindet sie,
					sobald Ihr euch wegdreht. Immer noch eilen Männer auf die Mauern,
					Speertruppen, Panzerpieken, aber auch Leute der Stadtmiliz eilen
					hin und her und leisten ihren Dienst. Ihr blickt in die Stadt und
					Eure Augen wandern an den Mauern Eurer Festung hinauf. Der letzte
					Rückzugspunkt, scheinbar uneinnehmbar. Und doch schien es
					hoffnungslos. Endlos erstreckte sich das Heer der Ungläubigen. Dann
					unvermittelt, setzen die Trommeln ein. Ihr kniet nieder und sprecht
					ein Gebet. Überall auf der Mauer tun es die Männer Euch gleich,
					knien nieder und beten. Und dann beginnt es....</i></b></font></td>
					<td width="2"></td>
					<td width="88" valign="top" align="right"><?
					define("VOTE_VERTICAL", 1);
					include ("includes/vote.inc.php");
					?></td>
				</tr>
			</table>
			<p><a href="register.php" title="Jetzt kostenlos registrieren"><img
				src="<? echo $imagepath; ?>/logo_80x80.gif" alt="HW2 Logo Schild"
				align="left" border="0"></a> <font
				style="font-family: Tahoma; font-size: 11px;"> Wir schreiben
			tiefstes Mittelalter. Papst Urban II. hat zu den Kreuzzügen
			aufgerufen und der heilige Krieg beginnt. Wählet zwischen dem
			christlichen oder dem islamischen Glauben und übernehmet die
			Herrschaft über Euer eigenes Reich. Gebietet Ihr am Anfang nur über
			ein einfaches Dorf, werdet Ihr mit der Zeit ein <b>gewaltiges Reich</b>
			Euer Eigen nennen. Gründet neue Städte oder erobert bereits
			bestehende. Seht Eure Dörfer zu prunkvollen Städten heranwachsen und
			gewaltige Heere werden auf Euer Wort hören. Führet die Einwohner zum
			rechten Glauben und zerschmettert die Ungläubigen. Doch Ihr seid
			nicht alleine. Erbittet Aufnahme in einem der mächtigen Orden Eures
			Glaubens und erklimmt die Hierarchie! Oder gründet Euren eigenen
			Orden und führet ihn zu Macht, Ruhm und Ansehen. Es liegt allein in
			Eurer Hand....</font>
			
			
			<p>
			
			
			<center><a href="register.php">Jetzt anmelden! (hier klicken)</a></center>

			</td>
		</tr>
		<tr class="tblbody">
			<td></td>
		</tr>
	</table>
	<!-- Middle Content / Story --></td>
	<td width="24%" valign="top">
	   <?php
	   print_news_table();
	   print_you_know_table();
	   ?>
	</td>
</tr>

</table> <!-- Content Table -->

</td></tr></table> <!-- Overall Table -->
<?
 include("includes/sponsorads-magiccorner.html");
?>

</center>
</body>
</html>
