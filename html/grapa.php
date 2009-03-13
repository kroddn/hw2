<?php
/***************************************************
* Copyright (c) 2005
*
* written by Markus Sinner <kroddn@psitronic.de>
*
*/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/banner.inc.php");
include_once("includes/page.func.php");

start_page();
start_body();
?>

<h1 class="error">Euer Grafikpaket belastet den Server!</h1>
<table width="600"><tr><td>
<font size="+1">Um den Server zu entlasten, installiert bitte das Grafikpaket. Wie das Funktioniert ist <a href="settings.php#grapa">in den Einstellungen (jetzt hier klicken)</a> beschrieben.</font><p>
<center>
<font size="+1"><b>Am besten gleich installieren!</b></font>
</center>
<p>
<hr>
<p>
<center><a href="javascript:window.location.reload()">Weiter gehts hier (letzte Aktion wiederholen)!</a></center>
<hr>
<p>
<div align="center">
<? 
$timemod = time() % 3600;
if($timemod < 1200){
  include("includes/ebay.flash.html");
  echo "</div>";
}
else {
    include("ads/sponsorads-leaderboard.php");
    echo "</div>";
    include("ads/sponsorads-framelayer.php");
}
?>
</td></tr>

</table>

<?php echo "</body></html>\n"; ?>