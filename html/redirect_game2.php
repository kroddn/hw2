<?php
$title = "Umleitung";
require_once("start_header.php");

$starttime = getRoundStartTime();

define("ROUND_FINISHED", "1");
?>
<tr>
	<td width="24%" valign="top">
<?php
zitat_table();
print_you_know_table();
?>
</td>
<td width="52%" valign="top">

<div class="tblbody" style="padding: 8px;">

<center><h1>Das Ende der Runde &quot;game1&quot;</h1></center>
<p>
Die Runde &quot;game1&quot; ist abgeschalten worden.
</p>
<p>
Bis auf Weiteres wird auf einem anderen Server die Runde
&quot;game2&quot; weiterlaufen. Am Ende der Runde 
&quot;game2&quot; wird eine Verschmelzung stattfinden 
und zukünftig nur noch eine Hauptrunde gestartet.
</p>
<p>
Wir erhoffen uns, dass durch diese Maßnahme etwas mehr
Spielspaß einhergeht, da die Spieler so nicht mehr auf
zwei Server verstreut spielen.
</p>
<p>
<center><h2>Weiter zu game2</h2></center>
Um zur Runde &quot;game2&quot; zu gelangen, bitte hier
den Link anklicken:<br>
<center><a href="http://game2.holy-wars2.de">http://game2.holy-wars2.de</a></center>
</p>
</div>
	</td>
	<td width="24%" valign="top">
<?php
print_news_table();
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

