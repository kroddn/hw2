<?php
/***************************************************
 * Copyright (c) 2003-2006 by holy-wars2.de
 *
 * written by Gordon Meiser, Markus Sinner
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/player.class.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");

if($player->isNamehunter()) {
  if(isset($_GET['delete'])) {
    if($_GET['delete']) {
      $upload_dir = AVATAR_DIR; 
      $filename=$upload_dir.$_GET['delete'].".jpg";
      if(is_file($filename)) {
        $del=unlink($filename);
      }
      if($del==true) {
        do_mysql_query("UPDATE player SET avatar=NULL WHERE id='".$_GET['delete']."'");
      }
    }
  }
  if(isset($_GET['publish'])) {
    if($_GET['publish']) {
      do_mysql_query("UPDATE player SET avatar='2' WHERE id='".$_GET['publish']."'");
    }
  }
}

?>
<html>
<head>
<title>Avatar</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?php
if($player->isNamehunter()) {
	echo "<table width=\"400\">\n";
	echo "<tr class=\"tblhead\"><td colspan=\"5\"><b>Noch nicht freigegebene Avatare</b></td></tr>\n";
	echo "<tr class=\"tblhead\">\n";
	echo "<td width=\"100\">&nbsp;</td>\n";
	echo "<td>Spieler</td>\n";
	echo "<td>Datum</td>\n";
	echo "<td>&nbsp;</td>\n";
	echo "<td>&nbsp;</td>\n";
	echo "</tr>\n";
    
    $sql = "SELECT id, name FROM player WHERE avatar='1'";
    if ($order == 'date') {
      //$sql.= " ORDER BY date";
    }
    
	$ava1=do_mysql_query($sql);
	while($avatar=mysql_fetch_assoc($ava1)) {
		echo "<tr class=\"tblbody\">\n";
		echo "<td>";
		echo "<img src=\"avatar.php?id=".$avatar['id']."\" />";
		echo "</td>\n";
		echo "<td>".$avatar['name']."</td>\n";
		echo "<td>";
		echo showAvatarDate($avatar['id']);
		echo "</td>\n";
		echo "<td><a href=\"".$_SERVER['PHP_SELF']."?delete=".$avatar['id']."\">l&ouml;schen</a></td>\n";
		echo "<td><a href=\"".$_SERVER['PHP_SELF']."?publish=".$avatar['id']."\">akzeptieren</a></td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "<br />";

//accepted
	echo "<table width=\"400\">\n";
	echo "<tr class=\"tblhead\"><td colspan=\"4\"><b>Freigegebene Avatare</b></td></tr>\n";
	echo "<tr class=\"tblhead\">\n";
	echo "<td width=\"100\">&nbsp;</td>\n";
	echo "<td>Spieler</td>\n";
	echo "<td><a href=\"?order=date\">Datum</a></td>\n";
	echo "<td>&nbsp;</td>\n";
	echo "</tr>\n";
	$ava1=do_mysql_query("SELECT id, name FROM player WHERE avatar='2'");
	while($avatar=mysql_fetch_assoc($ava1)) {
		echo "<tr class=\"tblbody\">\n";
		echo "<td>";
		echo "<img src=\"avatar.php?id=".$avatar['id']."\" />";
		echo "</td>\n";
		echo "<td>".$avatar['name']."</td>\n";
		echo "<td>";
		echo showAvatarDate($avatar['id']);
		echo "</td>\n";
		echo "<td><a href=\"".$_SERVER['PHP_SELF']."?delete=".$avatar['id']."\">l&ouml;schen</a></td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
}
?>
</body>
</html>
