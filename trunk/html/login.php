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


$title = "Login";
require_once("start_header.php");
$login = true;

?>
<tr>
	<td width="24%" valign="top">
<?php
playeronline_table();
bestplayer_table();

bestclan_table();

zitat_table();

?>
</td>
<td width="52%" valign="top" align="center">


<?
if(defined("ROUND_FINISHED")) {
  echo "Diese Runde ist beendet.";
}
else {
?>

<script language="JavaScript" type="text/javascript">
<!--
function whatisseccode() {
  var win = window.open("whatisseccode.php","SecurityCode","width=600,height=550,left=0,top=0,scrollbars=yes,dependent=yes");
  win.focus();
}
// -->
</script>

<!-- LoginServer -->
<form name="login" action="<?if (defined('LOGIN_SERVER')) echo LOGIN_SERVER; ?>login.php" method="POST" style="padding:0px; margin:0px;">
<table cellspacing="1" cellpadding="0" border="0" width="100%" style="border:1px solid #D7D796;">
<tr class="tblhead">
 <td colspan="3"  height="30">
<h1>Login <? echo $pagetitle; ?></h1>
<? 
  playing_div();
  
  echo "<br>\n";
  
  printRoundTimes();
?>
 </td>
</tr>
<? if ($loginerror) echo "<tr class=\"tblbody\" ><td height=\"30\" colspan=\"3\"><b><div class=\"error\">".$loginerror."</font></div></b></td></tr>"; ?>
<tr class="tblbody">
  <td height="50"class="tblhead"><label for="loginname" style="font-weight:bold;">Login:</label></td>
  <td class="tblbody">
    <input id="loginname" tabindex="1" type="text" name="loginname" <?if(isset($name)) echo 'value="'.$name.'"'; ?> >
    <center><a href="register.php" style="font-size: 10px;">Anmelden/Registrieren</a></center>
  </td>
<td rowspan="5" width="245">
<center>
<?php
if (!defined('NO_SECURITY') || !NO_SECURITY  ) {
  echo '<a href="login.php" tabindex="5"><img alt="Security-Code" src="'.( defined('LOGIN_SERVER') ? LOGIN_SERVER : "").'sec_code.php" border="0"></a><br>';
  echo "\n";
  $style = 'style="padding: 2px; width: 25px; height: 25px; margin: 2px;"';
  for ($i=1; $i<11; $i++) {    
    echo '<input type="button" '.$style.' value="'.($i%10).'" onClick="var v=document.getElementById(\'sec_code\'); v.value=v.value+\''.($i%10).'\';">'."\n";
    if($i % 3 == 0) echo "<br>\n";
  }
  echo '&nbsp;<input type="button" '.$style.' value="D" onClick="document.getElementById(\'sec_code\').value=\'\'">'."\n";
}
else {
  echo "&nbsp;";
}
?>
</center>
        </td>
	</tr>
	<tr>
	  <td height="50" class="tblhead"><label for="loginpassword" style="font-weight:bold;">Passwort:</label></td>
	  <td class="tblbody"><input type="password" tabindex="2" id="loginpassword" name="loginpassword"><br>
                <center><a href="forgotpw.php" style="font-size: 10px;">Passwort vergessen?</a></center></td>
	</tr>
	<tr>
	 <td height="50" class="tblhead"><label for="sec_code" style="font-weight:bold;">Security<br>Code:</label></td>
	 <td class="tblbody">
<?    if (!defined('NO_SECURITY') || !NO_SECURITY ) {
        echo '<input type="text" tabindex="3" id="sec_code" name="sec_code">';
        echo "<br>\n<center><a href=\"whatisseccode.php\" style=\"font-size: 10px;\" onClick=\"whatisseccode(); return false; \">Was ist das?</a></center>";
      }
      else echo "<center>Security Code <b>deaktiviert</b></center>"; ?>
     </td>
	</tr>
	<tr>
		<td height="20" colspan="2" class="tblhead">
		 <b>Lokales Grafikpaket aktivieren?</b>
        </td>	
    </tr>
    <tr>		
		<td class="tblbody" colspan="2">
		  <center>
			<label for="gp1" style="font-weight:bold;">Ja </label><input id="gp1" class="noborder" type="radio" name="hwathome" value="1" <? if (!defined("TEST_MODE") || !TEST_MODE) echo "checked"; ?>>
			<label for="gp0" style="font-weight:bold;">Nein </label><input id="gp0" class="noborder" type="radio" name="hwathome" value="0" <? if (defined("TEST_MODE") && TEST_MODE) echo "checked"; ?>>
	      </center>
          Sollten im Spiel Grafiken fehlen, dann antworten Sie hier mit &quot;Nein&quot;.
          <br>
          Falls Sie unsicher sind, belassen Sie die Antwort auf &quot;Ja&quot;.              
		</td>
	</tr>
	<tr>
		<td colspan="3" class="tblhead" align="center"><input type="submit" tabindex="4" value=" einloggen " name="loginprocess"></td>
	</tr>
</table>
</form>
<?                    
} // else of if(!defined("ROUND_FINISHED"))
?>

	</td>
	<td width="24%" valign="top">
<?php
print_news_table();
print_you_know_table();
?>
</td>
</tr>
</table>
</td></tr></table>

<?
 if (!defined('NO_SECURITY') || !NO_SECURITY) {
?>
 <script language="JavaScript" type="text/javascript">
 <!--
  document.getElementById("sec_code").focus();
 // -->
 </script>
<?
}

if($redirect==1) {
  include("ads/openinventory_popup.php");
}
?>

</center>
</body>
</html>

