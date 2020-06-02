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
 * Copyright (c) 2003-2005
 *
 * Stefan Neubert
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 * This File must not be used without permission!
 ***************************************************/
require_once("start_header.php");

$imagepath = "images/ingame";
$csspath = "images/ingame/css";

if ($getkey) {
  $sql_login = do_mysql_query("SELECT id,email,register_email,status FROM player ".
                                " WHERE login = '".mysqli_escape_string($GLOBALS['con'], $loginname)."'");
  if (mysqli_num_rows($sql_login) > 0) {
    $db_login = do_mysql_fetch_assoc($sql_login);
    if ($email == $db_login['email'] || $email == $db_login['register_email']) {
      $key = createKey();
      do_mysql_query("UPDATE player SET activationkey='$key' WHERE id=".$db_login['id']);
      $body = "Sehr geehrter Teilnehmer,\n
				Mit dem folgenden Aktivierungscode können Sie ihr Passwort im entsprechenden Menü ändern!\n
		
				Aktivierungscode: $key\r\n
		
				Der Key verfällt, falls Sie sich mit ihrem alten Passwort einloggen.\n
		
				Diese eMail wurde automatisch generiert. Bitte antworten Sie nicht dieser eMail-Adresse.\n		
		
				Viel Spaß wünscht Ihnen das Team von Holy-Wars";

      $ip = getenv('REMOTE_ADDR');
      mail($email, "Holy-Wars 2: Passwort vergessen", $body, "FROM: no-reply@holy-wars2.de");
      if($email != $db_login['register_email']) {
        mail($db_login['register_email'], "Holy-Wars 2: Passwort vergessen", $body, "FROM: no-reply@holy-wars2.de");
        do_mysql_query("INSERT INTO log_password_send (player,time,email,ip) ".
			   " VALUES (".$db_login['id'].", UNIX_TIMESTAMP(), '".$db_login['register_email']."', '".$ip."')"
			   );
      }

      // Passwort-Request loggen, um Missbrauch verfolgen zu können
      do_mysql_query("INSERT INTO log_password_send (player,time,email,ip) ".
                         " VALUES (".$db_login['id'].", UNIX_TIMESTAMP(), '".$email."', '".$ip."')"
                         );

      $msg = "Die eMail wurde erfolgreich versand, bitte prüfen Sie ihren Maileingang.";
    }
    else {$senderror = "Die eingegebene eMail Adresse stimmt nicht mit der hinterlegten überein.";}
  }
  else {$senderror = "Es existiert kein Account mit diesem Namen!";}
}
elseif ($changepw) {
	$sql_login = do_mysql_query("SELECT id,activationkey FROM player WHERE login = '".mysqli_escape_string($GLOBALS['con'], $pname)."'");
	if (mysqli_num_rows($sql_login)>0) {
		$db_login = do_mysql_fetch_assoc($sql_login);
		if ($db_login['status']==NULL) {
			if (checkPassword($pw1)) {
				if ($pw1==$pw2) {
				    if($db_login['activationkey'] == null || strlen($db_login['activationkey']) == 0) {
				      $changeerror = "Sie haben noch keinen Aktivierungscode beantragt.";
				    }
					else if(strlen($activationcode) > 0 && $activationcode == $db_login['activationkey']) {
					  do_mysql_query("UPDATE player SET activationkey=NULL, password='".md5(trim($pw1))."' WHERE id=".$db_login['id']) or die(mysqli_error($GLOBALS['con']));
                              include("forgotpw2.php");
                              die();
					}
					else { $changeerror = "Der eingegebene Aktivierungscode ist falsch."; }
				}
				else { $changeerror = "Die Passwörter stimmen nicht überein."; }
			}
			else {$changeerror = "Das gewählte Passwort ist zu kurz.";}
		}
		else {$changeerror = "Ihr Accountstatus lässt keine Passwortänderung zu.";}
	}
	else {$changeerror = "Es existiert kein Account mit diesem Login!";}
}
?>

<!--
<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body alink="#FF0000" vlink="#FF0000" background="<? echo $imagepath; ?>/bg.gif">
-->
<tr><td colspan="3" valign="top" align="center">

<form name="requestkey" action="<? echo $_SERVER['PHP_SELF'];  ?>" method="POST">
<div align="center">
<table width="400" cellspacing="1" cellpadding="0" border="0">
	<tr>
		<td colspan="2" align="center"><h1>Passwort vergessen</h1>
		<br><br>
		<? if ($senderror) echo "<div class='error'><b>$senderror</div><br>"; if ($msg) echo "<div class='noerror'><b>$msg</b></div></b><br>";?></td>
	</tr>
	<tr>
		<td class="tblhead" colspan="2" align="center">Aktivierungscode beantragen</td>
	</tr>
	<tr>
		<td class="tblbody"><b>Login:</b></td>
		<td class="tblbody"><input type="text" name="loginname"></td>
	</tr>
	<tr>
		<td class="tblbody"><b>eMail:</b></td>
		<td class="tblbody"><input type="text" name="email"></td>
	</tr>	
	<tr>
		<td colspan="2" class="tblbody">
		<b>Ihnen wird ein Aktivierungscode zugesendet, mit dem das Passwort geändert werden kann (siehe unten).</b>
		<br>		
		Hinweis: Spielername und eMail sind die im Spiel verwendeten
        Einstellungen. Aus Sicherheitsgründen ist nur der Versand an die hinterlegte eMail Adresse möglich,
        <b>zusätzlich</b> wird an die Email-Adresse gesendet, die zur Registrierung verwendet wurde.<br>
</td>
	</tr>
</table>
<br><input type="submit" value=" Aktivierungscode senden " name="getkey"><br><br><br><br><br><br>
</form>

<form name="changepw" action="<? echo $_SERVER['PHP_SELF'];  ?>" method="POST">
<table width="400" cellspacing="1" cellpadding="0" border="0">
	<tr>
		<td colspan="2" align="center"><? if ($changeerror) echo "<div class='error'><b>$changeerror</b></div><br>"; ?></td>
	</tr>
	<tr>
		<td class="tblhead" colspan="2" align="center">Neues Passwort setzen</td>
	</tr>
	<tr>
		<td class="tblbody"><b>Login:</b></td>
		<td class="tblbody"><input type="text" name="pname"></td>
	</tr>
	<tr>
		<td colspan="2" class="tblbody">&nbsp;</td>
	</tr>
	<tr>
		<td class="tblbody"><b>neues Passwort</b></td>
		<td class="tblbody"><input autocomplete="off" type="password" name="pw1"></td>
	</tr>
	<tr>
		<td class="tblbody"><b>Wiederholung Passwort</b></td>
		<td class="tblbody"><input autocomplete="off" type="password" name="pw2"></td>
	</tr>
	<tr>
		<td colspan="2" class="tblbody">&nbsp;</td>
	</tr>
	<tr>
		<td class="tblbody"><b>Aktivierungscode:</b></td>
		<td class="tblbody"><input type="text" name="activationcode"></td>
	</tr>
</table>
<br>
<input type="submit" value=" Passwort ändern " name="changepw">
<input type="button" onClick="window.location.href='index.php';" value=" zurück ">
</div>
</form>

</td></tr></table> <!-- Start-Header-Table -->

</body>
</html>
