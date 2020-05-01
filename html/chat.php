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

include("includes/db.inc.php"); 
include("includes/player.class.php"); 
include("includes/session.inc.php"); 
?>
<html>
<head>
<title>Chat</title>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<script language="JavaScript">
<!--
function nickvalid() {
   var nick = document.loginform.Nickname.value;
   if(nick.match(/^[A-Za-z0-9\[\]\{\}^\\\|\_\-`]{1,32}$/))
      return true;
   alert('Bitte einen gültigen Nicknamen eingeben!');
   document.loginform.Nickname.value = nick.replace(/[^A-Za-z0-9\[\]\{\}^\\\|\_\-`]/g, '');
   return false;
}
//-->
</script>

</head>
<style>
body,div,a {
  font-family: Tahoma;
  font-size: 12px;
  color: 000000;
  margin: 2px;
}
h1 {  font-size: 18px;  }
h2 {  font-size: 14px; color: 202020; }
</style>

<body marginwidth="0" marginheight="0" topmargin="2" leftmargin="2" background="<? echo $imagepath; ?>/bg.gif">
<h1>Einrichtung eines Clients (Tutorial)</h1>
Für die Einrichtung eines Chat-Programms haben wir eine Hilfe zusammengestellt: 
<a href="http://www.holy-wars2.de/wiki/index.php/IRC-Anleitung">Hier klicken</a>.
<p>
Falls Du bereits einen IRC-Client auf Deinem PC installiert hast 
(z.B. XChat oder MIRC), dann kannst Du zum Holy-Wars 2 Chat über
den Server <b>irc.holy-wars2.de</b> und <b>Port 6666</b> verbinden. 
In den Channel von HW2 gelangst Du dann mit dem Befehl <b>/j #hw2</b>.
<h1>Ohne eigenen Client</h1>
<table border="0"><tr valign="top"><td>
<form method="post" style="padding:0px; margin:0px;" 
 action="webchat/index.php" name="loginform" 
 onsubmit="return nickvalid()" target="_blank">	
<input type="hidden" name="interface" value="nonjs">
<table cellpadding="1" cellspacing="1" width="270">
 <tr class="tblhead">
  <td align="center" colspan="4" height="20"><b style="font-size:14px;">JAVA basiert</b>
  </td>
 </tr>
 <tr class="tblbody">
  <td class="tblbody">Nick:</td><td><input class="back" size="10" type="text" name="Nickname" value="<? echo $_SESSION['player']->name; ?>"></td>
  <td class="tblbody">Passwort:</td>
  <td>
   <input class="back" size="10" type="password" name="password" value="">
  </td>
 </tr>
 <tr><td align="center" colspan="4" class="tblhead">
    <input class="back" type="submit" value="Chatten">
    <input class="back" type="submit" name="register" value="Registrieren">&nbsp;&nbsp;
	<input type="hidden" name="name" value="HW2-Chatter::<? echo $_SESSION['player']->name; ?>">
	<input type="hidden" name="email" value="<? echo $_SESSION['player']->getEmail(); ?>">
	<input type="hidden" name="Server" value="irc.holy-wars2.de">
	<input type="hidden" name="Port" value="6666">
	<input type="hidden" name="Channel" value="#hw2">
	<input type="hidden" name="Password" value="">
	<input type="hidden" name="Format" value="mirc">
    <p>
    Der Chat wird in einem neuen Fenster geöffnet.
  </td>
 </tr>
 <tr class="tblbody">
  <td colspan="4" align="center">
Java kann man <a href="http://java.com/de/download/" target="_blank">hier</a> runterladen.
  </td>
 </tr>
</table>
</form>
</td><td>
<table cellpadding="1" cellspacing="1" width="270">
 <tr class="tblhead">
  <td align="center" height="20"><b style="font-size:14px;">CGI::IRC</b>
  </td>
 </tr>
 <tr class="tblbody">
    <td>Falls der Java-basierte IRC nicht funktioniert, haben wir 
 noch einen weiteren Chat zur Verfügung:<br> 
 <a href="http://www.holy-wars2.de/cgiirc" target="_blank">Hier klicken</a>.
  </td>
 </tr>
</table>
</td>
</tr></table>
<p>
<h2>Zur Erinnerung</h2>
Hier sind ein paar IRC-Befehle kurz zusammengefasst. Bitte beachten,
dass der / zu Beginn eines Befehls elementar wichtig ist. <p>
<ul>
<li>Einen weiteren Channel betreten:<br>
<code>/j #channelname</code><br>
<li>Manche IRC-Channels verlangen es, dass ein Nickname registriert
ist. Dazu muss man sich aber auch jedesmal identifizieren. Das geht
 so:<br>
<code>/ns identify PASSWORT</code><br>
Hierbei den / nicht vergessen, sonst verrät man das Passwort!
<!-- <li>Man kann auch einstellen, welche Channels beim Identifizieren
 betreten werden sollen:<br>
<code>/ns set ajoin #channel1, #channel2<br>
So listet man diese Channels wieder auf:<br>
<code>/ns set ajoin list<br>
-->
<li>Weitere Hilfe zu möglichen Befehlen:<br>
<code>/ns help</code>
</ul>
<p>

<p>
<hr>
<p>
<h2>Premium-Lite per SMS?</h2>
<? 
//include("includes/premium_sms.php"); Not found
end_page();
?>

