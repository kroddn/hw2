<!-- This is part of CGI:IRC 0.5
  == http://cgiirc.sourceforge.net/
  == Copyright (C) 2000-2002 David Leadbeater <cgiirc@dgl.cx>
  == Released under the GNU GPL
  -->

<?
function webchat($nick = null, $channels = "#hw2") {
  if($nick == null)
    $nick = "HW2_".rand(1000,9999);
?>

<script language="JavaScript"><!--
function setjs() {
 if(navigator.product == 'Gecko') {
   document.loginform["interface"].value = 'mozilla';
 }else if(window.opera && document.childNodes) {
   document.loginform["interface"].value = 'opera7';
 }else if(navigator.appName == 'Microsoft Internet Explorer' &&
    navigator.userAgent.indexOf("Mac_PowerPC") > 0) {
    document.loginform["interface"].value = 'konqueror';
 }else if(navigator.appName == 'Microsoft Internet Explorer' &&
 document.getElementById && document.getElementById('ietest').innerHTML) {
   document.loginform["interface"].value = 'ie';
 }else if(navigator.appName == 'Konqueror') {
    document.loginform["interface"].value = 'konqueror';
 }else if(window.opera) {
   document.loginform["interface"].value = 'opera';
 }
}
function nickvalid() {
   var nick = document.loginform.Nickname.value;
   if(nick.match(/^[A-Za-z0-9\[\]\{\}^\\\|\_\-`]{1,32}$/))
      return true;
   alert('Please enter a valid nickname');
   document.loginform.Nickname.value = nick.replace(/[^A-Za-z0-9\[\]\{\}^\\\|\_\-`]/g, '');
   return false;
}
//-->
</script>
<form method="post" action="cgi-bin/cgiirc/irc.cgi" name="loginform" onsubmit="setjs();return nickvalid()">
<input type="hidden" name="interface" value="nonjs">
<table border="0" cellpadding="5" cellspacing="0">
<tr><td colspan="4" align="center" bgcolor="#c0c0dd"><b>CGI:IRC Login</b></td></tr>
<tr><td align="right" bgcolor="#f1f1f1">Nickname</td><td align="left" bgcolor="#f1f1f1"><input type="text" name="Nickname" value="<? echo $nick; ?>"></td><td colspan="2" bgcolor="#f1f1f1"></td></tr>

<!--<tr><td align="right" bgcolor="#f1f1f1">Server</td><td align="left" bgcolor="#f1f1f1"><input type="text" name="Server" value="webirc.holy-wars2.de" disabled="1"></td><td colspan="2" bgcolor="#f1f1f1"></td></tr>
-->
<input type="hidden" name="Server" value="webirc.holy-wars2.de">

<tr><td align="right" bgcolor="#f1f1f1">Channel</td><td align="left" bgcolor="#f1f1f1"><input type="text" name="Channel" value="<? echo $channels; ?>"></td><td colspan="2" bgcolor="#f1f1f1"></td></tr>
<tr><td align="left" bgcolor="#d9d9d9">
</td><td colspan="3" align="right" bgcolor="#d9d9d9">
<input type="submit" value="Login" style="background-color: #d9d9d9">
</td></tr></table></form>

<small id="ietest"><a href="http://cgiirc.sourceforge.net/">CGI:IRC</a> 0.5.4 (2004/01/29)<br />
</small>

<?
} // function webchat($nick = null)
?>