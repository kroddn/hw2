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
* Copyright (c) 2004-2005 by IG HW2
*
* Markus Sinner, Gordon Meiser
*
* This File must not be used without permission!
***************************************************/
include ("includes/db.inc.php");
include ("includes/config.inc.php");
include ("includes/util.inc.php");


// Verbotene Emailadressen. KLEIN schreiben!!!
$forbidden_emails = array();
$forbidden_emails[0] = "hans.joergen@gmx.net";
$forbidden_emails[1] = "pfandlrene@gmx.net";
$forbidden_emails[2] = "reel-big-fish@edumail.at";
$forbidden_emails[3] = "hansiball@gmx.at";
$forbidden_emails[4] = "alex_klana@hotmail.com";
$forbidden_emails[5] = "Alexander_Kronberger@gmx.at";
$forbidden_emails[6] = "bw.hubschi@chello.at";
$forbidden_emails[7] = "reel-big-fish@gmx.at";
$forbidden_emails[8] = "punisher2@gmx.at";
$forbidden_emails[9] = "lord.dreganos@gmx.at";
$forbidden_emails[10]= "asyysy@web.de"; // "Spamming" kroddn 13.05.2006
$forbidden_emails[11]= "ceto55@hotmail.de"; // Derbstes Multiusing und Beledigigun
$forbidden_emails[12]= "cetin5534@yahoo.de";
$forbidden_emails[13]= "stevenbaui@yahoo.de"; // IRC: <ich> halt deien fresse du huren sohn ich fick dich und deien server wirst schon sehn du nap das iss unfair wir haben noch nie beschissen 
$forbidden_emails[14]= "lordsquall@fastem.com"; // http://forum.holy-wars2.de/viewtopic.php?t=10095
$forbidden_emails[15]= "onur_1987@hotmail.com"; // Email am 24.08. um 09:41
$forbidden_emails[16]= "respeckt52@hotmail.de"; // Email am 20.09. um 10:10

$res0=do_mysql_query("SELECT id FROM player");
$erg0=mysql_num_rows($res0);

$mapsize = do_mysql_query_fetch_assoc("SELECT max(x)+1 AS x, max(y)+1 AS y FROM map");
$fx = $mapsize['x'];
$fy = $mapsize['y'];

$qry_startlocations = do_mysql_query("SELECT count(*) as c, floor(y/".$fy."*6) as pos FROM startpositions GROUP BY pos");

$locsum = 0;
while ($res_sl=mysql_fetch_assoc($qry_startlocations)) {
  $loc[$res_sl['pos']]=$res_sl['c'];
  $locsum+=$res_sl['c'];
}

if ($register) {
	$registration=true;
	if(defined("MAX_PLAYER")) {
          if( MAX_PLAYER == 0 ) { $registration=false; $errmsg="Die Registrierung neuer Accounts ist eingestellt.";}
          else if  (MAX_PLAYER <= $erg0) { $registration=false; $errmsg="Momentan sind keine freien Plätze mehr verfügbar (maximal ".MAX_PLAYER." Spieler in dieser Runde).";}
        }

	if (!checkBez($name, 3, 40)) {$registration=false; $errmsg="Das gewählte Login ist zu kurz, zu lang oder verstößt gegen die Namenskonventionen.";}
	if ($registration) {
		$res1=do_mysql_query("SELECT id FROM player WHERE login LIKE '".trim($name)."'");
                // 48 Stunden Anmeldesperre für den gleichen Namen
                $del_lock_time = 48*3600;
		$del =do_mysql_query("SELECT FROM_UNIXTIME( 28800 + deltime - deltime%28800 +".$del_lock_time.") FROM log_player_deleted ".
                                     " WHERE login LIKE '".trim($name)."' AND deltime + ".$del_lock_time." > UNIX_TIMESTAMP()");
		if (mysql_num_rows($res1)>0) {$registration=false; $errmsg="Es gibt schon einen Spieler mit diesem Login";}
                if (mysql_num_rows($del)>0) {
                  $time = mysql_fetch_array($del);
                  $registration=false;
                  $errmsg="Die Anmeldung dieses Logins ist bis ca. ".$time[0]." nicht möglich, weil vor kurzem ein Spieler mit diesem Login existierte.";
                }
                if (strspn($name, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜßäöü ") < strlen($name))
		{
			$errmsg="Die Anmeldung dieses Logins ist nicht möglich. Es sind nur Buchstaben [a-z,A-Z] und Leerzeichen erlaubt.";
			$registration=false;
		}
	}
	if ($registration) {
		if (!checkPassword($pw1)) {$registration=false; $errmsg="Das gewählte Passwort ist zu kurz.";}
		elseif (!($pw1==$pw2)) {$registration=false; $errmsg="Die Passwörter stimmen nicht überein.";}
		elseif (!checkEmail($email)) {$registration=false; $errmsg="Sie haben keine oder eine falsche eMail-Adresse angegeben.";}
	}

	/*
	if ($registration) {
		if (($pos<1) || ($pos>6)) {$registration=false; $errmsg="Sie haben keine Ausgangslage gewählt";}
	}
	*/
	
	if ($registration) {
		$email = strtolower($email);
		if (in_array($email,$forbidden_emails)) {$registration=false; $errmsg="Gibt es nicht noch andere Online-Games, bei denen du die Admins beleidigen willst?";}
	}
	
	if ($registration) {
          //$p['pos']      = $pos;
          $p['login']    = $name;
          $p['email']    = $email;
          $p['pw']       = $pw1;
          $p['ref']      = $recruiter;

          include_once("includes/newplayer.func.php");
          $result = insert_new_player($p);

          if($result == null) {
            header("Location:register3.php");
          }
          else {
            $registration = false;
            $errmsg = $result;
          }
	}
}

require_once("start_header.php");

echo "</table><br /><center>\n";

?>
<html>
<head>
<title><? echo $pagetitle; ?> Registrierung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="30" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<div align="center">
<?php
if(defined("MAX_PLAYER")) {
  if( MAX_PLAYER == 0 ) { 
    echo "<div class='error'><b>";
    echo "Die Registrierung neuer Accounts ist eingestellt.";
    echo "</b></div><br></div>\n</body>\n</html>";
    exit;    
  }
  else if  (MAX_PLAYER <= $erg0) { 
    echo "<div class='error'><b>";
    echo "Momentan sind keine freien Plätze mehr verfügbar (maximal ".MAX_PLAYER." Spieler in dieser Runde).";
    echo "</b></div><br></div>\n</body>\n</html>";
    exit;
  }
}

?>
<form action="<? echo $PHP_SELF; ?>" method="POST">
<table width="500" cellspacing="1" cellpadding="0" border="0">
	<tr height="0">
		<td width="200"></td><td width="300"></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><b style="font-size: 14px;">Registrierung</b><br><br><? if ($errmsg) echo "<div class='error'><b>$errmsg</b></div><br>"; ?></td>
	</tr>
	<tr>
		<td colspan="2" class="tblhead" align="center">
		<br>
		<h2 style="color:red;">Login bedeutet nicht Spielername</h2>
		Spielername und Login sind zwei getrennt zu wählende Namen! Beim ersten Login
		kann man den Spielernamen und Startposition sowie die Religion auswählen.
		<br>
		&nbsp;
		</td>
	</tr>
	<tr>
		<td class="tblbody"><b>Login</b></td>
		<td class="tblbody"><input type="text" name="name"></td>
	</tr>
	<tr>
		<td colspan="2" class="tblbody" align="center">
		<i>Hinweis: Mindestlänge 4 Zeichen, generell sind nur folgende Zeichen erlaubt: a-z,A-Z,Leerzeichen<br>Achtung: zur <b>Erhöhung der Sicherheit</b> sollte das Login nicht mit dem Spielernamen übereinstimmen!</i><p>
		
		</td>
	</tr>
	<tr>
		<td class="tblbody"><b>Passwort</b></td>
		<td class="tblbody"><input type="password" name="pw1"></td>
	</tr>
	<tr>
		<td class="tblbody"><b>Wiederholung Passwort</b></td>
		<td class="tblbody"><input type="password" name="pw2"></td>
	</tr>
	<tr>
		<td colspan="2" class="tblbody" align="center">Benutzen Sie ein sicheres Passwort!</td>
	</tr>
	<tr>
		<td class="tblbody"><b>eMail</b></td>
		<td class="tblbody"><input type="text" name="email"></td>
	</tr>
		<td colspan="2" class="tblbody" align="center">
		<font color="#FF0000"><b>ACHTUNG:</b></font> Derzeit haben wir <font color="#FF0000"><b>Probleme bei der Zustellung von Emails an GMX und Web.de</b></font>. In solchen Fällen bitte im IRC melden oder eine Email schreiben.
		<p>Hinweis: manche Email-Provider (wie Yahoo, Gmail) sortieren die Aktivierungs-Emails in den Spam-Ordner!</td>
	<tr>
	</tr>
	<tr>
		<td colspan="2" class="tblbody" align="center">&nbsp;</td>
	</tr>

	<tr>
		<td colspan="2" class="tblbody">Wichtiger Hinweis: Zur Erkennung von sogenannten "Multis" (Spieler, die sich unfaire Vorteile durch Nutzen mehrerer Accounts verschaffen wollen) führen wir unter anderem eine Prüfung ihrer Adresse (IP) durch. Wir sperren generell alle Spieler mit gleicher Adresse.
<br>Sollten sich in Ihrem Haushalt schon Holy-Wars 2 Spieler befinden, so melden Sie sich nach der Registrierung bei einem Multihunter oder besuchen Sie unseren Chatraum im <a target="_blank" href="http://www.holy-wars2.de/cgiirc">IRC</a> und schildern Sie uns Ihre Situation inklusive der Angabe aller betroffenen Spieler. Eine Genehmigung für öffentliche Einrichtungen wie Schulen, Internet-Cafe oder andere öffentlich zugänglichen Orten erteilen wir nur in Ausnahmefällen</td>
	</tr>
<tr><td  colspan="2" align="center">
<? if (isset($recruiter)) echo '<input type="hidden" name="recruiter" value="'.$recruiter.'">'; ?>
<input type="submit" name="register" value=" registrieren ">
<input type="button" onClick="window.location.href='index.php';" value=" abbrechen ">
</form>
</td></tr>
<?
if (isset($recruiter_name)) {
echo '<tr><td  colspan="2" class="tblbody" align="center">Sie wurden von Spieler '.$recruiter_name.' geworben</td></tr>';
}
?>
</table>

</div>
</body>
</html>
