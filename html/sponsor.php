<? 
start_page(); 
start_body();
?>

<h1>Besuchen Sie unseren Sponsor:</h1>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td style="border: solid black 2px;">
<?php
include_once("include/banner.inc.php");

if (isset($bannerpage)) 
     printBanner($bannerpage);    
else if (isset($id))
     printBanner($id);
else
     printBanner();
?>
</td><td width="99%"></td></tr>
<tr height="10"><td colspan="2"></td></tr>
<tr><td colspan="2">
<a href="all.php">Zurück zur Übersicht der Werbepartner</a>
</td></tr>
</table>

</body>
</html>
