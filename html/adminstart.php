<?php
$START_TIME=microtime();
/***************************************************
* Copyright (c) 2003-2004 by holy-wars.com
*
* written by Gordon Meiser
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/player.class.php");
include_once("includes/session.inc.php");

if($player->isNamehunter()) {
  if($_GET['delete']) {
	do_mysql_query("DELETE FROM zitate WHERE id='".$_GET['delete']."'");
  }
  if($_GET['activate']) {
	do_mysql_query("UPDATE zitate SET active='1', admin='".$_SESSION['player']->id."' WHERE id='".$_GET['activate']."'");
  }
}
?>

<html>
<head>
<title>Zitate</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?php
if($player->isNamehunter()) {
	echo "<table width=\"790\">";
	echo "<tr class=\"tblhead\" width=\"100%\"><td colspan=\"8\"><b>Noch nicht aktivierte Zitate</td></tr>\n";
	echo "<tr class=\"tblhead\" width=\"100%\" style=\"font-weight:bold;\">\n";
	echo "<td width=\"10\">&nbsp;</td>\n";
	echo "<td>Spieler</td>\n";
	echo "<td>Text</td>\n";
	echo "<td width=\"80\">Aktion</td>\n";
	echo "</tr>";
	$zitat=do_mysql_query("SELECT zitate.id as id,active,player.name as player,text,admin FROM zitate LEFT JOIN player ON zitate.player=player.id WHERE active != '1' ORDER BY id DESC");
	while($get_zitat=mysqli_fetch_assoc($zitat)) {
		echo "<tr class=\"tblbody\" width=\"100%\">";
		echo "<td width=\"16\"><a href=\"".$_SERVER['PHP_SELF']."?delete=".$get_zitat['id']."\"><img src=\"".$imagepath."/ad_del.png\" border=\"0\" alt=\"L&ouml;schen\"></a></td>\n";
		echo "<td width=\"30\">".$get_zitat['player']."</td>";
		echo "<td>".nl2br($get_zitat['text'])."</td>";
		echo "<td><a href=\"".$_SERVER['PHP_SELF']."?activate=".$get_zitat['id']."\">aktiviern</a></td>\n";
		echo "</tr>";
	}
	echo "</table>";
	echo "<br />";

	echo "<table width=\"790\">";
	echo "<tr class=\"tblhead\" width=\"100%\"><td colspan=\"8\"><b>Aktivierte Zitate</td></tr>\n";
	echo "<tr class=\"tblhead\" width=\"100%\" style=\"font-weight:bold;\">\n";
	echo "<td width=\"10\">&nbsp;</td>\n";
	echo "<td>Spieler</td>\n";
	echo "<td>Text</td>\n";
	echo "<td width=\"80\">Aktiviert</td>\n";
	echo "</tr>";
	$zitat=do_mysql_query("SELECT zitate.id as id,active,player.name as player,text,admin FROM zitate LEFT JOIN player ON zitate.player=player.id WHERE active = '1' ORDER BY id ASC");
	while($get_zitat=mysqli_fetch_assoc($zitat)) {
		$res=do_mysql_query("SELECT name FROM player WHERE id='".$get_zitat['admin']."'");
		$admin=mysqli_fetch_assoc($res);
		echo "<tr class=\"tblbody\" width=\"100%\">";
		echo "<td width=\"16\"><a href=\"".$_SERVER['PHP_SELF']."?delete=".$get_zitat['id']."\"><img src=\"".$imagepath."/ad_del.png\" border=\"0\" alt=\"L&ouml;schen\"></a></td>\n";
		echo "<td width=\"30\">".$get_zitat['player']."</td>";
		echo "<td>".nl2br($get_zitat['text'])."</td>";
		echo "<td>".$admin['name']."</td>\n";
		echo "</tr>";
	}
	echo "</table>";
}
else {
  echo "Unbefugt";
}
?>
</body>
</html>
