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

if (!isset($topic) and isset($input)) {
	$err = "Topic fehlt! Bitte ergänzen und dann nochmal abschicken!";
}

if (!isset($text) and isset($input)) {
	$err = "Text fehlt! Bitte ergänzen und dann nochmal abschicken!";
}

if (!isset($topic) and !isset($text) and isset($input)) {
	$err = "Topic und Text fehlen! Bitte ergänzen und dann nochmal abschicken!";
}

if (isset($topic) and isset($text) and isset($input)) {
	if(!$_POST['change']) {
		do_mysqli_query("INSERT INTO news (time, topic, text) VALUES (".time().", '".$topic."','".$text."')") or die(mysqli_error($GLOBALS['con']));
		$err = "Die News wurde erfolgreich in die Datenbank eingetragen!";
	} else {
		do_mysqli_query("UPDATE news SET topic='".$topic."', text='".$text."' WHERE id='".$_POST['change']."'") or die(mysqli_error($GLOBALS['con']));
		$err = "Die News wurde erfolgreich ge&auml;ndert!";
	}
}
if($_GET['delete']) {
	do_mysqli_query("DELETE FROM news WHERE id='".$_GET['delete']."'") or die(mysqli_error($GLOBALS['con']));
	$err = "Die Nachricht wurde erfolgreich gel&ouml;scht!";
}
?>
<html>
<head>
<title>News</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?php
if($player->isAdmin()) {

if(!$_GET['edit']) {
	echo "<form action=\"".$PHP_SELF."\" method=\"POST\">\n";
	echo "<table width=\"300\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">\n";
	echo "<tr><td class=\"error\">\n";
	if (isset($err)) { echo $err; } else echo " &nbsp ";
	echo "</td></tr>\n";
	echo "<tr><td class=\"tblhead\" align=\"left\"><b>Topic (max. 40 Zeichen)</b></td></tr>\n";
	echo "<tr><td class=\"tblbody\"><input type=\"text\" size=\"40\" maxlength=\"40\" style=\"width:100%;\" name=\"topic\"></td></tr>\n";
	echo "<tr><td class=\"tblhead\" align=\"left\"><b>Text</b></td></tr>\n";
	echo "<tr><td class=\"tblbody\"><textarea rows=\"5\" cols=\"50\" style=\"width:100%;\" name=\"text\"></textarea></td></tr>\n";
	echo "<tr class=\"tblhead\"><td align=\"center\">\n";
	echo "<input type=\"hidden\" name=\"input\" value=\"1\">\n";
	echo "<input type=\"submit\" name=\"news_in\" value=\" News einstellen \">\n";
	echo "<input type=\"reset\" value=\" abbrechen \">\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	$edit=do_mysqli_query("SELECT topic,text FROM news WHERE id='".$_GET['edit']."'");
	$edit=mysqli_fetch_assoc($edit);
	echo "<form action=\"".$PHP_SELF."\" method=\"POST\">\n";
	echo "<table width=\"300\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">\n";
	echo "<tr><td class=\"error\">\n";
	if (isset($err)) { echo $err; } else echo " &nbsp ";
	echo "</td></tr>\n";
	echo "<tr><td class=\"tblhead\" align=\"left\"><b>Topic (max. 40 Zeichen)</b></td></tr>\n";
	echo "<tr><td class=\"tblbody\"><input type=\"text\" value=\"".$edit['topic']."\" size=\"40\" maxlength=\"40\" style=\"width:100%;\" name=\"topic\"></td></tr>\n";
	echo "<tr><td class=\"tblhead\" align=\"left\"><b>Text</b></td></tr>\n";
	echo "<tr><td class=\"tblbody\"><textarea rows=\"5\" cols=\"50\" style=\"width:100%;\" name=\"text\">".$edit['text']."</textarea></td></tr>\n";
	echo "<tr class=\"tblhead\"><td align=\"center\">\n";
	echo "<input type=\"hidden\" name=\"input\" value=\"1\">\n";
	echo "<input type=\"hidden\" name=\"change\" value=\"".$_GET['edit']."\">\n";
	echo "<input type=\"submit\" name=\"news_in\" value=\" News bearbeiten \">\n";
	echo "<input type=\"reset\" value=\" abbrechen \">\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}
//news darstellen
	echo "<table width=\"790\">";
	echo "<tr class=\"tblhead\" width=\"100%\"><td colspan=\"8\"><b>News (die letzten 10 Einträge)</td></tr>\n";
	echo "<tr class=\"tblhead\" width=\"100%\" style=\"font-weight:bold;\">\n";
	echo "<td colspan=\"2\">&nbsp;</td>\n";
	echo "<td>ID</td>\n";
	echo "<td>Time</td>\n";
	echo "<td>Topic</td>\n";
	echo "<td>Text</td>\n";
	echo "</tr>";
	$news=do_mysqli_query("SELECT * FROM news WHERE 1 order by id desc limit 0,10");
	while($get_news=mysqli_fetch_assoc($news)) {
		echo "<tr class=\"tblbody\" width=\"100%\">";
		echo "<td width=\"16\"><a href=\"".$PHP_SELF."?delete=".$get_news['id']."\"><img src=\"".$imagepath."/ad_del.png\" border=\"0\" alt=\"L&ouml;schen\"></a></td>\n";
		echo "<td width=\"16\"><a href=\"".$PHP_SELF."?edit=".$get_news['id']."\"><img src=\"".$imagepath."/ad_fixed.png\" border=\"0\" alt=\"Bearbeiten\"></a></td>\n";
		echo "<td width=\"30\">".$get_news['id']."</td>";
		echo "<td width=\"80\">".date("d.m.y H:i",$get_news['time'])."</td>";
		echo "<td>".$get_news['topic']."</td>";
		echo "<td>".$get_news['text']."</td>";
		echo "</tr>";
	}
	echo "</table>";
}
?>
</body>
</html>
<?
// Insert Into DB the execution time of this script
list($START_MICRO, $START_SEC) = explode(" ",$START_TIME);
list($END_MICRO, $END_SEC) = explode(" ",microtime());
do_mysqli_query("INSERT INTO log_cputime (file,start,time) VALUES ('".__FILE__."', ".$START_SEC.",".round(($END_MICRO+$END_SEC-$START_MICRO-$START_SEC)*1000).")");
?>