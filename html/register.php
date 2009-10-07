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
 * Markus Sinner
 * Gordon Meiser
 *
 * This File must not be used without permission	!
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/config.inc.php");

$pagetitle = "Registrierung";

require_once("start_header.php");
?>
<!-- </table><br/> -->

<tr class="tblbody">
<td colspan="3" align="center">
<h1>Registrierung</h1>
<? playing_div(); ?>
</td>
</tr>

<tr>
<td colspan="3" align="center">

<table cellspacing="1" cellpadding="0" border="0" width="90%" align="center"  style="margin-top: 10px;">
	<tr>
		<td class="tblhead" colspan="2" align="center" style="font-size: 14px;"><b>Allgemeine Nutzungsbedingungen von Holy-Wars 2</b><br></td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 1</b></td>
		<td class="tblbody" >Um an Holy-Wars 2 teilnehmen zu k�nnen ist es notwendig den Nutzungsbedingungen
                             zustimmen. Diese beziehen sich auf das gesamte Angebot von Holy-Wars 2.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 2</b></td>
		<td class="tblbody" >Die Teilnahme am Spielgeschehen kann jederzeit mit L�schung des Accounts beendet werden.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 3</b></td>
        <td class="tblbody" >Holy-Wars 2 stellt nur eine Plattform bereit, �ber die sich Mitglieder 
                             untereinander verst�ndigen k�nnen. F�r den Inhalt dieser Kommunikation sind
                             die Mitglieder selbst verantwortlich. Die Betreiber behalten sich vor, die 
                             Mitgliedschaft bei der Verbreitung pornographischer, rassistischer, beleidigender
                             oder gegen geltendes Recht verstossender Inhalte zu beenden.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 4</b></td>
		<td class="tblbody" >Holy-Wars 2 �bernimmt keine Haftung f�r Sch�den, die durch die Benutzung des 
                             Angebots entstehen oder entstanden sind. Schadensersatz ist in jedem Falle ausgeschlossen.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 5</b></td>
		<td class="tblbody" >Es ist untersagt, Seiten dieses Angebots mit einem anderen Programm ausser einem 
                             g�ngigen Browser oder einem von Holy-Wars 2 explizit zur Verf�gung gestelltem 
                             Programm abzurufen. Dies bezieht sich besonders auf sogenannte Bots, Skripte 
                             oder auch andere Programme, die das Webinterface ersetzen sollen.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 6</b></td>
		<td class="tblbody" >Der Spielaccount inkl. aller dazugeh�rigen Daten ist Eigentum und verbleibt 
                             im Eigentum der Betreiber von Holy-Wars 2. Jede Ver�u�erung ist untersagt.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 7</b></td>
        <td class="tblbody" >Die Betreiber behalten sich vor, Accounts, mit welchen auf dem Server Daten 
                             ver�ndert wurden, oder auf andere Weise vors�tzlich oder fahrl�ssig der 
                             reibungslose Betrieb von Holy-Wars 2 behindert wurde, zu l�schen und rechtliche 
                             Schritte einzuleiten.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 8</b></td>
		<td class="tblbody" >Es besteht kein Anspruch auf die Teilnahme an Holy-Wars 2. Die Betreiber behalten 
                             sich das Recht vor, ohne Angabe von Gr�nden Accounts zu sperren oder l�schen.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 9.1</b></td>
		<td class="tblbody" >Jedes Mitglied verpflichtet sich nur <b>einen Account</b> zu erstellen und zu nutzen.</td>
	</tr>
    <tr>
        <td class="tblhead" valign="top" nowrap><b>� 9.2</b></td>
        <td class="tblbody" >Die Regeln &quot;<i>Sitting, Pushing, Multiaccounting und Multi-Exceptions</i>&quot; sind verpflichtend (siehe unten).</td>
    </tr>	
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 10</b></td>
        <td class="tblbody" >Das Mitglied willigt in die Speicherung aller Daten ein, die f�r die Erkennung und 
                             Verfolgung von Zuwiderhandlungen gegen diese Nutzungsbestimmungen dienlich sind.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 11</b></td>
		<td class="tblbody" >Nutzerdaten werden entsprechend der Vorschriften des
                             Datenschutzgesetzes der Bundesrepublik Deutschland gespeichert und behandelt. 
                             An die angegebene Email-Adresse werden ausschlie�lich Informationen gesendet, 
                             die f�r das Spiel von Belang sind.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 12</b></td>
		<td class="tblbody" >Holy-Wars 2 spielt im Mittelalter. Dementsprechend sollte auch die Namenswahl, 
                             etc. erfolgen. Schwere Zuwiederhandlungen werden ggf. geahndet.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 13</b></td>
		<td class="tblbody" >Die grunds�tzliche Teilnahme an Holy-Wars 2 ist kostenlos. Gegen Spenden 
                             sind <a href="premium.php">Premium-Accounts</a> m�glich</td>
	</tr>	
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 14</b></td>
        <td class="tblbody" >Die Betreiber behalten sich vor, die Nutzungsbedingungen jederzeit 
                             und ohne Ank�ndigung zu �ndern.</td>
	</tr>	
	<tr>
		<td class="tblhead" valign="top" nowrap><b>� 15</b></td>
		<td class="tblbody" >Ist einer dieser Paragraphen ung�ltig, so bleibt die Wirksamkeit 
                             der anderen davon unber�hrt.</td>
	</tr>
</table>

<table cellspacing="1" cellpadding="0" border="0" width="90%" align="center" style="margin-top: 10px;">
	<tr>
		<td class="tblhead" colspan="2" align="center" style="font-size: 14px;"><b>Anmerkungen und Verhaltenskodex</b><br></td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>zu � 3</b></td>
        <td class="tblbody" >Von allen teilnehmenden Personen wird erwartet, dass sie einen 
                             &quot;guten Umgang&quot; miteinander pflegen. Dies beschr�nkt sich 
                             nicht nur auf das Spiel selbst, sondern auch auf alle damit 
                             verbundenen Kommunikationsplattformen wie Forum, IRC, ICQ oder Email, 
                             selbst dann wenn eine der betreffenden Personen keinen Account im Spiel betreibt.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>zu � 3</b></td>
        <td class="tblbody" >Von der F�hrung der Orden im Spiel wird erwartet, dass sie Verst��e 
                             ihrer Mitglieder gegen die Nutzungsbedingungen dem Betreiber melden, 
                             und selbst mit Tadel oder Kritik reagieren. Bei wissentlicher 
                             Nichtbeachtung dieser Bedingung kann vom Betreiber gegen den Orden 
                             und dessen F�hrung vorgegangen werden.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>zu � 12</b></td>
        <td class="tblbody" >Es existieren sogenannte <i>NameHunter</i>, die st�ndig und regelm��ig 
                             die Namensgebung im Spiel beobachten. Diese leiten entsprechende 
                             Schritte f�r eine Ahndung ein.</td>
	</tr>

</table>

<table cellspacing="1" cellpadding="0" border="0" width="90%" align="center" style="margin-top: 10px;">
	<tr>
		<td class="tblhead" colspan="2" align="center" style="font-size: 14px;"><b>Sitting, Pushing, Multiaccounting und Multi-Exceptions</b><br></td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>zu � 9.1 und �9.2</b></td>
        <td class="tblbody" >Automatische Testroutinen �berpr�fen, ob ein Spieler mehrere Accounts betreibt.
                             In Verdachtsf�llen werden die betroffenen Accounts gesperrt und nach einiger Zeit 
                             vollst�ndig gel�scht.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>Pushing</b></td>
        <td class="tblbody" >Das Versenden von Rohstoffen an kleinere Spieler zur Aufbaust�tze ist erlaubt. 
                             Der umgekehrte Weg jedoch wird als Pushing angesehen und ist verboten. Die 
                             Anwendung dieser Regel wird situationsabh�ngig von einem Multihunter entschieden.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>Sitting</b></td>
        <td class="tblbody" >Sitting, also das eingreifen in einen nicht eigenen Account, ist grunds�tzlich verboten.</td>
	</tr>
	<tr>
		<td class="tblhead" valign="top" nowrap><b>Exceptions</b></td>
                <td class="tblbody" >Spielen mehrere Spieler �ber einen Router oder �ber denselben Computer (gleiche IP-Adresse), 
dann <b>muss</b> eine sogenannte Multi-Exception in den Einstellungen beantragt werden. Jeglicher Fluss von Resourcen zwischen den betroffenen
Spielern ist ausnahmslos untersagt, auch wenn die Ressourcen �ber Dritte weitergeleitet werden. In solchen F�llen wird auch das Weiterleiten
der Resourcen geahndet.
</td>
	</tr>


</table>

<? if (! isset($info)) { ?>
<p></p>
<form action="register2.php" method="POST">
<input type="submit" value=" zustimmen ">
<input type="button" onClick="window.location.href='index.php';" value=" ablehnen ">
</form>
<? } ?>

</td>
</tr>
</table>
</table>

</center>

</body>
</html>
