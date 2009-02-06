<?
//include("include/session.php");
//page_begin("Banner");

include_once("includes/db.inc.php");
include_once("include/db.class.php");
include_once("include/banner.inc.php");
include_once("include/player.class.php");
?>

<html>
<head>
<title>HW2 - Bannerpartner</title>
<script src="js/functions_v45.js" type="text/javascript"></script>
</head>

<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body class="mainbody" background="<? echo $imagepath; ?>/bg.gif">
<h1>Holy Wars 2 Werbepartner (Banner)</h1>
<b>Sie möchten auch auf unseren Seiten für Ihre Produkte oder
Dienstleistungen werben?</b><p>
Wir binden Ihre Werbemittel direkt ein,
ohne umständliche Ad-Server oder Banner-Provider. 
Ausserdem führen wir für Ihre Werbemittel eine View und Clickstatistik.
<p>
Schreiben Sie uns an! Wir unterbreiten Ihnen gerne ein individuelles Angebot.<br>
Kontaktdaten erfahren Sie im <a href="impressum.php" target="_blank">Impressum</a>.
<p>
<h1>Aktuelle Werbepartner</h1>
Hinweis: das Klicken auf diese Banner über diese Seite ist <b>deaktiviert</b>, 
um das Einhalten der allgemeinen Geschäftsbedigungen unserer Werbepartnern 
zu gewährleisten.<p>

<table><tr valign="top"><td valign="top">
<hr>
<? include("ads/openinventory_486x60.php"); ?>
<hr>
<?
$db = new DB(DB_TYPE);
session_start();

if(!isset($_SESSION['player']) || !$player->isMaintainer()) {
  if($db->get_db_type() == "mysql")
    $expire = "WHERE (expiretime IS NULL OR expiretime > UNIX_TIMESTAMP())";
  else {
    $expire = "WHERE (expiretime IS NULL OR expiretime > now() )";
  }
}
else {
  $expire = "";
}

$sql = "SELECT * FROM ".BANNER_TABLE." $expire ORDER BY banner_id";
echo "<!-- $sql -->\n";
$b = $db->query($sql);
while($banner = $db->fetch_object($b)) {
  echo "<h3>[".$banner->banner_id."]".$banner->bannername.
    (isset($_SESSION['player']) && $player->isMaintainer() ? " - ".$banner->showcount."/".$banner->clickcount.", exp: ".$banner->expiretime  : "").
    "</h3>\n";

  if ($banner->expiretime != null && $banner->expiretime < time()) {
    echo "<b>Banner expired</b><p>\n";
  }
  else {
    printBannerNoRef($banner->banner_id);
    echo "<br>\n";
  }
  
  if ($banner->expiretime == null || $banner->expiretime >= time()) {
    echo '<a href="'.MAGIC_SCRIPT.'?magic='.md5(rand()).'&bannerpage='.$banner->banner_id.'">-Nur dieses Banner-</a>&nbsp;&nbsp;';
  }

  if (isset($_SESSION['player']) && $player->isMaintainer()) {
    echo '<a href="banner/'.$banner->localfile.'">-Lokale Datei-</a>&nbsp;&nbsp;';
    echo '<a href="'.$banner->remotefile.'">-Original Datei-</a>';
  }    

  echo "<hr>\n\n\n\n";
} // while
?>
<hr>
<script type="text/javascript" src="http://www.sponsorads.de/script.php?s=6865"></script>
</td>
<td valign="top">
<? include("ads/openinventory_120x600.php");?>
<p>
<? include("includes/sponsorads-skyscraper.html");?>
<p>
<? include("includes/ebay.160x600.html");?>
<p>
</td></tr>
<tr><td colspan="2">
<hr>
<? include("includes/ebay.728x90.html");?>
<hr>
<? include("ads/openinventory_728x90.php");?>
<hr>
</td></tr>
</table>
</body></html>

