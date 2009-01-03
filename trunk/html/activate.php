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
 * franzl
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 * This File must not be used without permission!
 ***************************************************/
require_once("start_header.php");

$imagepath = "images/ingame";
$csspath = "images/ingame/css";

if($activate) {
  $sql_login = do_mysql_query("SELECT id, status, activationkey FROM player WHERE login = '".mysql_escape_string($loginname)."'");
  if(mysql_num_rows($sql_login)>0) {
    $db_login = mysql_fetch_assoc($sql_login);
    if ($db_login['status']==1) {
      if ($activationcode == $db_login['activationkey']) {
        do_mysql_query("UPDATE player SET status=NULL, statusdescription=NULL, activationkey=NULL WHERE id=".$db_login['id']) or die(mysql_error());
        echo "<tr><td colspan='3' valign='top' align='center'><div class='error'><h1>Ihr Account wurde erfolgreich aktiviert!</h2></div><br>\n";
        echo "<a href='login.php?name=".urlencode($loginname)."'>Zum Login</a>";
        die("</td></tr></table> <!-- Start-Header-Table -->");

      }
      else {
        $activationerror = "Der Aktivierungscode ist falsch";
      }
    } // $db_login['status']==1
    else if ($db_login['status'] == null) {
      $activationerror = "Ihr Account ist bereits aktiviert, Sie können sich also ganz normal einloggen.";
    }
    else {
      $activationerror = "Ihr Accountstatus lässt keine Aktivierung zu.";
    }
  }
  else {$activationerror = "Es existiert kein Account mit diesem Login!";}
} // if($activate)
?>
<form name="login" action="<? echo $PHP_SELF; ?>" method="POST">
<tr><td colspan="3" valign="top">

<div align="center">
<table cellspacing="1" cellpadding="0" border="0">
	<tr>
		<td colspan="2" align="center"><b>Aktivierung</b><p><? if ($activationerror) echo "<div class='error'><b>$activationerror</b></div><br>"; ?></td>
	</tr>
	<tr>
		<td class="tblhead" colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="tblbody"><b>Login:</b></td>
		<td class="tblbody"><input type="text" name="loginname" <? if ($loginname) echo 'value="'.$loginname.'"'; ?>></td>
	</tr>
	<tr>
		<td class="tblbody"><b>Aktivierungscode:</b></td>
		<td class="tblbody"><input type="text" name="activationcode" <? if ($activationcode) echo 'value="'.$activationcode.'"'; ?>></td>
	</tr>
        <tr><td colspan="2"><center><a href="forgotpw.php">Passwort vergessen?</a></center></td></tr>

</table>
<br><input type="submit" value=" aktivieren " name="activate">
<input type="button" onClick="window.location.href='index.php';" value=" zurück ">
</div>
</form>
<p>
<center>
<div style="width: 480px; padding: 5px; background-color: #D0D000;">Sie haben immernoch keine Aktivierungsmail erhalten?<br>
Normalerweise sollte die Email innerhalb von wenigen Minuten bei Ihnen sein,<br>
gedulden Sie sich also noch einen Moment.<p>
Sollte innerhalb der nächsten Stunden wider Erwarten <b>keine Email</b><br>
eintreffen, dann liegt wohl ein Problem beim Versand vor. Wenden Sie sich<br>
bitte per Email an einen Multihunter oder besuchen Sie den IRC,<br>
unter Angabe Ihres Logins und der Email-Adresse, unter der Sie sich angemeldet haben.
</div>
</center>

</td></tr></table> <!-- Start-Header-Table -->

<p>
<div align="center">
<? include("ads/easyad_728.php"); ?>
</div>

</body>
</html>
