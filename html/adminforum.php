<?php
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
include_once("includes/admin.inc.php");
$error=false;

if( !$_SESSION['player']->isAdmin() )
  exit;

if($_GET['setsame']) {
  if($_GET['setsame']=="pwd") {
    $res=do_mysqli_query("UPDATE clanf_users SET user_password='".$_GET['pass']."' WHERE user_id='".$_GET['id']."'");
    if(!$res)
      $error="<b class=\"error\">Aktion fehlgeschlagen!</b><br />";
  }
  if($_GET['setsame']=="name") {
    $res=do_mysqli_query("UPDATE clanf_users SET username='".$_GET['name']."' WHERE user_id='".$_GET['id']."'");
    if(!$res)
      $error="<b class=\"error\">Aktion fehlgeschlagen!</b><br />";
  }
}

if($_GET['reset']=="true") {
  echo "<a href=\"".$PHP_SELF."?reset=doIt\">Forum wirklich reseten</a>\n";
  exit;
} 
elseif($_GET['reset']=="doIt") {
  reset_clanforum_now();
  exit;
}

if($_GET['delete']) {
  if($_GET['delete']=="topic") {
    $res=do_mysqli_query("DELETE FROM clanf_topics WHERE topic_id='".$_GET['id']."'");
    $res=do_mysqli_query("DELETE FROM clanf_posts WHERE topic_id='".$_GET['id']."'");
    if(!$res)
      echo $error;
  } elseif($_GET['delete']=="post") {
    $res=do_mysqli_query("DELETE FROM clanf_posts WHERE post_id='".$_GET['id']."'");
    if(!$res)
      echo $error;
  }
}
?>
<html>
<head>
<title>Ordensforum</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?php
if($player->isAdmin()) {
	if($error) {
		echo "<b class=\"error\">".$error."</b>";
		echo "<hr />";
	}
	echo "<table width=\"300\">\n";
	echo "<tr class=\"tblhead\"><td colspan=\"3\" height=\"30\"><b>Forenfehler breinigen</b></td></tr>\n";
	echo "<tr class=\"tblbody\"><td colspan=\"3\">Hier k&ouml;nnen die g&auml;ngigsten Fehler des internen Ordensforums bereinigt werden.</td></tr>\n";
//pwd
	echo "<tr class=\"tblhead\"><td colspan=\"3\"><b>Passwortkonflikte</b></td></tr>\n";
	$res1=mysqli_query($GLOBALS['con'], "SELECT clanf_users.user_id as uid, clanf_users.user_password as pwdcf, player.password as pwd, player.id AS id, player.name AS name FROM clanf_users LEFT JOIN player ON player.id=clanf_users.user_id WHERE clanf_users.user_password != player.password");
	if(mysqli_num_rows($res1) > 0) {
		while($data1=mysqli_fetch_assoc($res1)) {
			echo "<tr><td colspan=\"2\" class=\"tblbody\">".$data1['name']."</td>";
			//echo "<td>".$data1['pwdcf'].":".$data1['pwd']."</td>";
			echo "<td class=\"tblhead\"><a href=\"".$PHP_SELF."?setsame=pwd&id=".$data1['uid']."&pass=".$data1['pwd']."\">bereinigen</a></td></tr>\n";
		}
	} else {
		echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"40\" valign=\"middle\" align=\"center\">Es liegen keine Passwortkonflikte vor</td></tr>\n";
	}
//name
	$res1=mysqli_query($GLOBALS['con'], "SELECT clanf_users.user_id as uid, clanf_users.username, player.name, player.name AS name FROM clanf_users LEFT JOIN player ON player.id=clanf_users.user_id WHERE clanf_users.username != player.name");
	echo "<tr class=\"tblhead\"><td colspan=\"3\"><b>Namenskonflikte</b></td></tr>\n";
	if(mysqli_num_rows($res1) > 0) {
		while($data1=mysqli_fetch_assoc($res1)) {
			echo "<tr><td colspan=\"2\" class=\"tblbody\">".$data1['name']."</td>";
			echo "<td class=\"tblhead\"><a href=\"".$PHP_SELF."?setsame=name&id=".$data1['uid']."&name=".$data1['name']."\">bereinigen</a></td></tr>\n";
		}
	} else {
		echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"40\" valign=\"middle\" align=\"center\">Es liegen keine Namenskonflikte vor</td></tr>\n";
	}
//topics
	$res1=mysqli_query($GLOBALS['con'], "SELECT topic_id as id, topic_replies FROM clanf_topics WHERE topic_poster = '-1'");
	echo "<tr class=\"tblhead\"><td colspan=\"3\"><b>Fehlerhafte Topics (Anonymous Topics)</b></td></tr>\n";
	if(mysqli_num_rows($res1) > 0) {
		while($data1=mysqli_fetch_assoc($res1)) {
			echo "<tr>";
			echo "<td class=\"tblhead\">".$data1['id']."</td>";
			echo "<td class=\"tblbody\">(".$data1['topic_replies'].") Antworte(n)</td>";
			echo "<td class=\"tblhead\"><a href=\"".$PHP_SELF."?delete=topic&id=".$data1['id']."\">bereinigen</a></td>\n";
			echo "</tr>";
		}
	} else {
		echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"40\" valign=\"middle\" align=\"center\">Es liegen keine Konflikte vor</td></tr>\n";
	}

//posts
	$res1=mysqli_query($GLOBALS['con'], "SELECT post_id as id FROM clanf_posts WHERE poster_id = '-1'");
	echo "<tr class=\"tblhead\"><td colspan=\"3\"><b>Fehlerhafte Posts (Anonymous Posts)</b></td></tr>\n";
	if(mysqli_num_rows($res1) > 0) {
		echo "<b>Bei folgenden Posts ist ein Login-Fehler aufgetreten:</b><br />";
		while($data1=mysqli_fetch_assoc($res1)) {
			echo "<tr>";
			echo "<td class=\"tblbody\" colspan=\"2\">".$data1['id']."</td>";
			echo "<td class=\"tblhead\"><a href=\"".$PHP_SELF."?delete=post&id=".$data1['id']."\">bereinigen</a></td>\n";
			echo "</tr>";
		}
	} else {
		echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"40\" valign=\"middle\" align=\"center\">Es liegen keine Konflikte vor</td></tr>\n";
	}

	echo "<tr class=\"tblhead\"><td colspan=\"3\" height=\"40\" valign=\"middle\" align=\"center\"><a style=\"color:red;\" href=\"".$PHP_SELF."?reset=true\">Forum RESET</a><br />Kann NICHT R&uuml;ckg&auml;ngig gemacht werden!</td></tr>\n";
	echo "</table>";
}
?>
</body>
</html>
