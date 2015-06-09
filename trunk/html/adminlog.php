<?php
/***************************************************
* Copyright (c) 2003-2004 by holy-wars2.de
*
* written by
* Gordon Meiser
* Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/player.class.php");
include_once("includes/session.inc.php");

if ($delete_log_mysqlerr=="1") {
        if (isset($id_log_mysqlerr)) {
	  foreach ($id_log_mysqlerr as $error)
	    do_mysql_query("DELETE FROM log_mysqlerr WHERE id=".$error);
	}
	if (isset($fixed_log_mysqlerr)) {
	  foreach ($fixed_log_mysqlerr as $error)
	    do_mysql_query("UPDATE log_mysqlerr SET fixed=".$player->getID()." WHERE id=".$error);
	}
}
if ($delete_log_err=="1") {
	if (isset($id_log_err)) {
	  foreach ($id_log_err as $error)
	    do_mysql_query("DELETE FROM log_err WHERE id=".$error);
	}
	if (isset($fixed_log_err)) {
	  foreach ($fixed_log_err as $error)
	    do_mysql_query("UPDATE log_err SET fixed=".$player->getID()." WHERE id=".$error);
	}
}
?>
<html>
<head>
<title>Logtool</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
if($player->isMaintainer()) {
	//log_mysqlerr darstellen
	echo "<form target=\"_self\" action=\"".$PHP_SELF."\" method=\"POST\"><input type=\"hidden\" name=\"delete_log_mysqlerr\" value=\"1\" />";
	echo "<table width=\"600\" border=\"0\" style=\"width:500px;\">";
	echo "<tr class=\"tblhead\" width=\"100%\"><td colspan=\"8\" class=\"tblhead\"><b>log_mysqlerr Tabelle (die letzten 50 Einträge)</td></tr>\n";
	echo "<tr class=\"tblhead\" width=\"100%\">\n";
	echo "<td><img src=\"".$imagepath."/ad_del.png\" alt=\"L&ouml;schen\"></td>\n";
	echo "<td><img src=\"".$imagepath."/ad_fixed.png\" alt=\"Fixed\"></td>\n";
	echo "<td>ID</td><td width=\"200\">Query</td>\n";
	echo "<td>Error</td>\n";
	echo "<td>Zeit</td>\n";
	echo "<td>Scriptfile und Referer</td>\n";
	echo "<td>Fixed</td></tr>\n";
	$log_mysqlerr=do_mysql_query("SELECT log_mysqlerr.*, player.name as fixed_player FROM log_mysqlerr LEFT JOIN player ON player.id=fixed ORDER BY id DESC LIMIT 0,50");
    
	while($get_log_mysqlerr=mysql_fetch_assoc($log_mysqlerr)) {
		if ($get_log_mysqlerr['fixed']) {
			echo "<tr class=\"tblbody\" width=\"500\">";
		} else {
			echo "<tr class=\"tbldanger\" width=\"500\">";
		}
		echo "<td><input name=\"id_log_mysqlerr[]\" type=\"checkbox\" value='".$get_log_mysqlerr['id']."'></td>";
		echo "<td><input name=\"fixed_log_mysqlerr[]\" type=\"checkbox\" value='".$get_log_mysqlerr['id']."'></td>";
		echo "<td>".$get_log_mysqlerr['id']."</td>";
		echo "<td>".$get_log_mysqlerr['qry']."</td>";
		echo "<td>".$get_log_mysqlerr['err']."</td>";
		echo "<td>".date("d.m.y H:i",$get_log_mysqlerr['time'])."</td>";
		echo "<td><pre>".$get_log_mysqlerr['scriptname']."</pre>\n".
              "<p>Referer: ".$get_log_mysqlerr['referer']."</td>";
		echo "<td>&nbsp;".$get_log_mysqlerr['fixed_player']."</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "<br />";
	echo "<table><tr><td><input type=\"submit\" type=\"button\" value=\"Änderung übernehmen\" class=\"submit\"></form></td></tr>\n</table>";
	//log_err darstellen
	echo "<br><br>";
	echo "<form target=\"_self\" action=\"".$PHP_SELF."\" method=\"POST\">
        <input type=\"hidden\" name=\"delete_log_err\" value=\"1\">";
	echo "<table width=\"650\">";
	echo "<tr class=\"tblhead\"><td colspan=7><b>log_err Tabelle (die letzten 50 Einträge)</td></tr>\n";
	echo "<tr class=\"tblhead\"><td><img src=\"".$imagepath."/ad_del.png\" alt=\"L&ouml;schen\"></td><td><img src=\"".$imagepath."/ad_fixed.png\" alt=\"Fixed\"></td><td>ID</td><td>Error-String</td><td>Zeit</td><td>Referer</td><td>Fixed</td></tr>\n";
	$log_err=do_mysql_query("SELECT * FROM log_err WHERE 1 order by id desc limit 0,50");
	while($get_log_err=mysql_fetch_assoc($log_err)) {
		echo "<tr class=\"tblbody\" width=\"100%\">";
		echo "<td><input name=\"id_log_err[]\" type=\"checkbox\" value='".$get_log_err['id']."'></td>";
		echo "<td><input name=\"fixed_log_err[]\" type=\"checkbox\" value='".$get_log_mysqlerr['id']."'></td>";
		echo "<td>".$get_log_err['id']."</td>";
		echo "<td>".$get_log_err['errstr']."</td>";
		echo "<td>".date("d.m.y H:i",$get_log_err['time'])."</td>";
		echo "<td>".$get_log_err['referer']."</td>";
		echo "<td>".$get_log_err['fixed_player']."</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "<table><tr><td><input type=\"submit\" type=\"button\" value=\"Änderung übernehmen\" class=\"submit\"></form></td></tr>\n</table>";
}
else {
  echo '<h1 class="error">Ihr seid kein Maintainer</h1>';
}


?>
</body>
</html>