<?php
/***************************************************************************
 *                                index.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: index.php,v 1.99.2.3 2004/07/11 16:46:15 acydburn Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

include_once("./includes/config.inc.php");
include_once("./includes/db.inc.php");
include_once("./includes/util.inc.php");
include_once("./includes/clan.class.php");
include_once("./includes/player.class.php");
include_once("./includes/banner.inc.php");
include_once("./includes/session.inc.php");


function checkRegistered($id) {
  $res1=mysql_query("SELECT username FROM clanf_users WHERE user_id='".intval($id)."'");
  // Falls Account noch nicht existiert, lege ihn an.
  if(mysql_num_rows($res1) < 1) {
    $res2=mysql_query("SELECT password FROM player WHERE id='".$_SESSION['player']->id."'");
    $data2=mysql_fetch_assoc($res2);
    $query="INSERT INTO clanf_users ".
           " (user_id,user_active,username,user_password,user_level,user_timezone,user_lang,user_dateformat,user_allowbbcode,user_allowsmile) ".
           " VALUES ('".$_SESSION['player']->id."','1','".$_SESSION['player']->name."','".$data2['password']."','0','0:00','0','d M Y h:i a','1','1')";
    $res3=mysql_query($query);
  }
  else {
    // Ordensleiter
    if($_SESSION['player']->clanstatus == 63) {
      $res2=mysql_query("SELECT group_id FROM clanf_user_group WHERE user_id = '".$_SESSION['player']->id."' AND group_id = '3'");
      if(mysql_num_rows($res2) < 1) {
        mysql_query("INSERT INTO clanf_user_group (group_id,user_id,user_pending) VALUES ('3','".$_SESSION['player']->id."','0')");
      }
    }
    return true;
  }
}


if(checkRegistered($_SESSION['player']->id)) {
	/*
	echo "user exists!";
	exit; */
}


function checkLeaderRights() {
	if($_SESSION['player']->clanstatus == 63) {
		return true;
	}
	else {
		header("Location: cf_index.php");
		exit;
	}
}

/**
 * Teste, ob der Clan des Spielers auch Besitzer des Forums ist
 * 
 */
function checkClanOwner($forum_id) {
  // franzl hat "cat_order" als Clan-ID missbraucht. Das muss geprüft werden.
  if($_SESSION['player']->clan > 0) {
    
    $sql = "SELECT * FROM clanf_forums LEFT JOIN clanf_categories USING(cat_id)".
                          " WHERE cat_order = ".$_SESSION['player']->clan.
                          "  AND clanf_forums.forum_id = ".mysql_escape_string($forum_id);
    
    $res = do_mysql_query($sql);
    if(mysql_num_rows($res) == 0) {
      // Nicht erlaubt
      header("Location: cf_index.php?error=notallowed");
      exit;
    }
    else {
      return true;
    }
  }
  else {
    header("Location: cf_index.php?error=noclan");
    exit;
  }
}


if($_GET['lockforum']) {
	checkLeaderRights();
	checkClanOwner($_GET['lockforum']);
	mysql_query("UPDATE clanf_forums SET forum_status = '1' WHERE forum_id = '".mysql_escape_string($_GET['lockforum'])."'");
	header("Location: cf_index.php?setup=1");
	exit;
}
if($_GET['unlockforum']) {
	checkLeaderRights();
	checkClanOwner($_GET['unlockforum']);
	mysql_query("UPDATE clanf_forums SET forum_status = '0' WHERE forum_id = '".mysql_escape_string($_GET['unlockforum'])."'");
	header("Location: cf_index.php?setup=1");
    exit;
}
if($_GET['privforum']) {
	checkLeaderRights();
	checkClanOwner($_GET['privforum']);
	mysql_query("UPDATE clanf_forums SET forum_status = '2' WHERE forum_id = '".mysql_escape_string($_GET['privforum'])."'");
	header("Location: cf_index.php?setup=1");
    exit;
}
if($_GET['unprivforum']) {
	checkLeaderRights();
	checkClanOwner($_GET['unprivforum']);
	mysql_query("UPDATE clanf_forums SET forum_status = '0' WHERE forum_id = '".mysql_escape_string($_GET['unprivforum'])."'");
	header("Location: cf_index.php?setup=1");
    exit;
}
if($_POST['fname']) {
	$res0=mysql_query("SELECT max( forum_id ) as newfid FROM clanf_forums");
	$data0=mysql_fetch_assoc($res0);
	$res1=mysql_query("SELECT cat_id FROM clanf_categories WHERE cat_order = '".$_SESSION['player']->clan."'");
	$data1=mysql_fetch_assoc($res1);
	// echo "Forum erstellt";
	$res2=mysql_query("INSERT INTO clanf_forums (forum_id, cat_id, forum_name, forum_desc) VALUES ('".($data0['newfid']+1)."','".$data1['cat_id']."','".$_POST['fname']."','".$_POST['fdesc']."')");
	mysql_query("INSERT INTO clanf_auth_access (group_id, forum_id, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_sticky, auth_announce, auth_vote, auth_pollcreate, auth_attachments, auth_mod) VALUES ('3', '".($data0['newfid']+1)."', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1')");
	header("Location: cf_index.php");
    exit;
}
if($_POST['setupforum']) {
	checkLeaderRights();
	mysql_query("INSERT INTO clanf_categories (cat_title,cat_order) VALUES ('".$_SESSION['player']->clan."','".$_SESSION['player']->clan."')");
	mysql_query("INSERT INTO clanf_user_group (group_id,user_id,user_pending) VALUES ('3','".$_SESSION['player']->id."','0')");
}
if($_POST['edtforum']) {
	checkLeaderRights();
	mysql_query("UPDATE clanf_forums SET forum_name='".($_POST['edtname'])."', forum_order='".($_POST['edtorder'])."', forum_desc='".($_POST['edtdesc'])."' WHERE forum_id='".intval($_POST['edtforum'])."'");
	header("Location: cf_index.php");
	exit;
}

if($_GET['setup'] || $_GET['setmod'] || $_GET['lockforum'] || $_GET['unlockforum'] || $_GET['editforum']) {
?>
<html>
<head>
<style type="text/css">
<!--
/*
  The original subSilver Theme for phpBB version 2+
  Created by subBlue design
  http://www.subBlue.com

  NOTE: These CSS definitions are stored within the main page body so that you can use the phpBB2
  theme administration centre. When you have finalised your style you could cut the final CSS code
  and place it in an external file, deleting this section to save bandwidth.
*/

/* General page style. The scroll bar colours only visible in IE5.5+ */
body {
	background-image:url('http://hw2.z-gaming.de/images/ingame/bg.gif');
	scrollbar-base-color: FFFFC8;
	scrollbar-3dlight-color: FFFFC8;
	scrollbar-arrow-color: 000000;
	scrollbar-darkshadow-color: FFFFC8;
	scrollbar-face-color: F0F0AA;
	scrollbar-highlight-color: FFFFC8;
	scrollbar-shadow-color: FFFFC8;
	scrollbar-track-color: FFFFC8;
}

/* General font families for common tags */
font,th,td,p { font-family: Verdana, Arial, Helvetica, sans-serif }
a:link,a:active,a:visited { color : #000000; }
a:hover		{ text-decoration: underline; color :brown; }
hr	{ height: 0px; border: solid #000000 0px; border-top-width: 1px;}

/* This is the border line & background colour round the entire page */
.bodyline	{ background-color: #FFFFFF; border: 1px #000000 solid; }

/* This is the outline round the main forum tables */
.forumline	{ border: 1px #000000 solid; }

/* Main table cell colours and backgrounds */
td.row1	{ background-color: #FFFFC8; }
td.row2	{ background-color: #F0F0AA; }
td.row3	{ background-color: #FFFFC8; }

/*
  This is for the table cell above the Topics, Post & Last posts on the index.php page
  By default this is the fading out gradiated silver background.
  However, you could replace this with a bitmap specific for each forum
*/
td.rowpic {
		background-color: #F0F0AA;
		background-image: url(templates/subSilver/images/cellpic2.jpg);
		background-repeat: repeat-y;
}

/* Header cells - the blue and silver gradient backgrounds */
th	{
	color: #000000; font-size: 11px; font-weight : bold;
	background-color: #F0F0AA; height: 25px;
	background-image: url(templates/subSilver/images/cellpic3.gif);
}

td.cat,td.catHead,td.catSides,td.catLeft,td.catRight,td.catBottom {
			background-image: url(templates/subSilver/images/cellpic1.gif);
			background-color:#FFFFC8; border: #FFFFFF; border-style: solid; height: 28px;
}
.tblhead {
	background-color: F0F0AA;
	padding:3px;
}
.tblbody {
	background-color: FFFFC8;
	padding:3px;
}
table.td {
	padding:3px;
}
/*
  Setting additional nice inner borders for the main table cells.
  The names indicate which sides the border will be on.
  Don't worry if you don't understand this, just ignore it :-)
*/
td.cat,td.catHead,td.catBottom {
	height: 29px;
	border-width: 0px 0px 0px 0px;
}
th.thHead,th.thSides,th.thTop,th.thLeft,th.thRight,th.thBottom,th.thCornerL,th.thCornerR {
	background-color:#F0F0AA;font-weight: bold; border: #FFFFFF; border-style: solid; height: 1px;
}
td.row3Right,td.spaceRow {
	background-color:#F0F0AA; border: #FFFFFF; border-style: solid;
}

th.thHead,td.catHead { font-size: 12px; border-width: 1px 1px 0px 1px; }
th.thSides,td.catSides,td.spaceRow	 { border-width: 0px 1px 0px 1px; }
th.thRight,td.catRight,td.row3Right	 { border-width: 0px 1px 0px 0px; }
th.thLeft,td.catLeft	  { border-width: 0px 0px 0px 1px; }
th.thBottom,td.catBottom  { border-width: 0px 1px 1px 1px; }
th.thTop	 { border-width: 1px 0px 0px 0px; }
th.thCornerL { border-width: 1px 0px 0px 1px; }
th.thCornerR { border-width: 1px 1px 0px 0px; }

/* The largest text used in the index page title and toptic title etc. */
.maintitle	{
	font-weight: bold; font-size: 22px; font-family: "Trebuchet MS",Verdana, Arial, Helvetica, sans-serif;
	text-decoration: none; line-height : 120%; color : #000000;
}

/* General text */
.gen { font-size : 12px; }
.genmed { font-size : 11px; }
.gensmall { font-size : 10px; }
.gen,.genmed,.gensmall { color : #000000; }
a.gen,a.genmed,a.gensmall { color: #006699; text-decoration: none; }
a.gen:hover,a.genmed:hover,a.gensmall:hover	{ color: #DD6900; text-decoration: underline; }

/* The register, login, search etc links at the top of the page */
.mainmenu		{ font-size : 11px; color : #000000 }
a.mainmenu		{ text-decoration: none; color : #006699;  }
a.mainmenu:hover{ text-decoration: underline; color : #DD6900; }

/* Forum category titles */
.cattitle		{ font-weight: bold; font-size: 12px ; letter-spacing: 1px; color : #006699}
a.cattitle		{ text-decoration: none; color : #006699; }
a.cattitle:hover{ text-decoration: underline; }

/* Forum title: Text and link to the forums used in: index.php */
.forumlink		{ font-weight: bold; font-size: 12px; color : #006699; }
a.forumlink 	{ text-decoration: none; color : #006699; }
a.forumlink:hover{ text-decoration: underline; color : #DD6900; }

/* Used for the navigation text, (Page 1,2,3 etc) and the navigation bar when in a forum */
.nav			{ font-weight: bold; font-size: 11px; color : #000000;}
a.nav			{ text-decoration: none; color : #006699; }
a.nav:hover		{ text-decoration: underline; }

/* titles for the topics: could specify viewed link colour too */
.topictitle,h1,h2	{ font-weight: bold; font-size: 11px; color : #000000; }
a.topictitle:link   { text-decoration: none; color : #000000; }
a.topictitle:visited { text-decoration: none; color : #000000; }
a.topictitle:hover	{ text-decoration: underline; color : #000000; }

/* Name of poster in viewmsg.php and viewtopic.php and other places */
.name			{ font-size : 11px; color : #000000;}

/* Location, number of posts, post date etc */
.postdetails		{ font-size : 10px; color : #000000; }

/* The content of the posts (body of text) */
.postbody { font-size : 12px; line-height: 18px}
a.postlink:link	{ text-decoration: none; color : #006699 }
a.postlink:visited { text-decoration: none; color : #5493B4; }
a.postlink:hover { text-decoration: underline; color : #DD6900}

/* Quote & Code blocks */
.code {
	font-family: Courier, 'Courier New', sans-serif; font-size: 11px; color: #006600;
	background-color: #FAFAFA; border: #D1D7DC; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}

.quote {
	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; color: #444444; line-height: 125%;
	background-color: #FAFAFA; border: #D1D7DC; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}

/* Copyright and bottom info */
.copyright		{ font-size: 10px; font-family: Verdana, Arial, Helvetica, sans-serif; color: #444444; letter-spacing: -1px;}
a.copyright		{ color: #444444; text-decoration: none;}
a.copyright:hover { color: #000000; text-decoration: underline;}

/* Form elements */
input,textarea, select {
	color : #000000;
	font: normal 11px Verdana, Arial, Helvetica, sans-serif;
	border-color : #000000;
}

/* The text input fields background colour */
input.post, textarea.post, select {
	background-color : #FFFFFF;
}

input { text-indent : 2px; }

/* The buttons used for bbCode styling in message post */
input.button {
	background-color : #EFEFEF;
	color : #000000;
	font-size: 11px; font-family: Verdana, Arial, Helvetica, sans-serif;
}

/* The main submit button option */
input.mainoption {
	background-color : #FAFAFA;
	font-weight : bold;
}

/* None-bold submit button */
input.liteoption {
	background-color : #FAFAFA;
	font-weight : normal;
}

/* This is the line in the posting page which shows the rollover
  help line. This is actually a text box, but if set to be the same
  colour as the background no one will know ;)
*/
.helpline { background-color: #DEE3E7; border-style: none; }

a { color:#000000; }

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("templates/subSilver/formIE.css");
-->
</style>
</head>
<body bgcolor="#E5E5E5" text="#000000" link="#006699" vlink="#5493B4">
<?php

} // if ...

if($_GET['setup']) {
	checkLeaderRights();
	echo "<table style=\"width:550px; font-size:12px;\">\n";
	$res1=mysql_query("SELECT cat_title, cat_order FROM clanf_categories WHERE cat_order='".$_SESSION['player']->clan."'");
	if(!mysql_num_rows($res1)) {
		echo "<tr><td colspan=\"4\" class=\"tblhead\" height=\"30\" valign=\"middle\"><b>Internes Forum Administrieren</b></td></tr>";
		echo "<tr><td colspan=\"4\" class=\"tblhead\"><b>Forum einrichten</b></td></tr>\n";
		echo "<tr><td colspan=\"4\" class=\"tblbody\" style=\"padding:20px;\">\n";
		echo "Um das interne Forensystem nutzen zu k&ouml;nnen m&uuml;sst Ihr es zuvor einrichten!<br />\n";
		echo "Lasst eurem Zeigeger&auml;t daher freien Lauf und aktiviert mit Ihm die nachstehende Schaltfl&auml;che!<br />\n";
		echo "</td></tr>\n";
		echo "<tr><td colspan=\"4\" class=\"tblhead\" style=\"text-align:center;\">\n";
		echo "<form method=\"post\" action=\"".$PHP_SELF."\">\n";
		echo "<br /><input type=\"submit\" style=\"font-weight:bold;\" name=\"setupforum\" value=\"MyLord ".$_SESSION['player']->name."! Klickt hier um das Forum einzurichten >>\" />\n";
		echo "</form>\n";
		echo "</td></tr>\n";
	} else {
		echo "<tr><td colspan=\"5\" class=\"tblhead\" height=\"30\" valign=\"middle\"><b>Internes Forum Administrieren</b></td></tr>";
		$res1=mysql_query("SELECT cat_id FROM clanf_categories WHERE cat_order = '".$_SESSION['player']->clan."'");
		$data1=mysql_fetch_assoc($res1);
		echo "<tr><td colspan=\"5\" class=\"tblhead\"><b>Forenliste</b></td></tr>";
		$res2=mysql_query("SELECT forum_id, forum_status, forum_name FROM clanf_forums WHERE cat_id = '".$data1['cat_id']."' ORDER BY forum_order ASC");
		while($data2=mysql_fetch_assoc($res2)) {
			echo "<tr><td class=\"tblbody\" width=\"250\">\n";
			if($data2['forum_status']==1)
				echo $data2['forum_name']."</td><td class=\"tblbody\" width=\"100\"><a href=\"".$PHP_SELF."?unlockforum=".$data2['forum_id']."\">entsperren</a></td><td class=\"tblbody\" width=\"100\">";
			else
				echo $data2['forum_name']."</td><td class=\"tblbody\" width=\"100\"><a href=\"".$PHP_SELF."?lockforum=".$data2['forum_id']."\">sperren</a></td><td class=\"tblbody\" width=\"100\">";

			if($data2['forum_status']==2)
        echo "<a href=\"".$PHP_SELF."?unprivforum=".$data2['forum_id']."\">F&uuml;r alle Member</a></td><td class=\"tblbody\" width=\"100\">";
			else
				echo "<a href=\"".$PHP_SELF."?privforum=".$data2['forum_id']."\">Ministerforum</a></td><td class=\"tblbody\" width=\"100\">";

			echo "<a href=\"".$PHP_SELF."?deleteqry=".$data2['forum_id']."\">l&ouml;schen</a></td>";
			echo "<td class=\"tblbody\" width=\"100\"><a href=\"".$PHP_SELF."?editforum=".$data2['forum_id']."\">bearbeiten</a></td></tr>\n";
		}
		echo "<tr><td class=\"tblhead\" colspan=\"5\">";
		echo "<a href=\"".$PHP_SELF."?setmod=true\"><b>Moderatoren verwalten</b></a>";
		echo "</td></tr>";
		echo "<tr><td class=\"tblhead\" colspan=\"5\"><b>Forum anlegen</b></td></tr>";
		echo "<tr><td class=\"tblbody\" colspan=\"5\" style=\"text-align:center;\">\n";
		echo "<form method=\"post\" action=\"".$PHP_SELF."\">";
		echo "<input type=\"text\" style=\"width:550px;\" name=\"fname\" value=\"Forentitel\" /><br />";
		echo "<textarea name=\"fdesc\"style=\"width:550px; heigth:200px;\" rows=\"5\" />Optionale Beschreibung</textarea><br />";
		echo "<input type=\"submit\" value=\"Forum anlegen\" />";
		echo "</forum>";
		echo "</td></tr>";
		echo "<tr><td class=\"tblbody\" colspan=\"5\" height=\"30\" valign=\"middle\"><a href=\"javascript:history.back(1)\">zur&uuml;ck</a></td></tr>";
		exit;
	}
	echo "</table>\n";
	exit;
}
if($_GET['deleteqry']) {
	echo "Wollen Sie dieses Forum <b>unwideruflich</b> l&ouml;schen?<br /><br />";
	echo "<a href=\"javascript:history.back(1)\">Nein</a> - ";
	echo "<a href=\"".$PHP_SELF."?deleteforum=".$_GET['deleteqry']."\">Ja</a><br />";
}
if($_GET['deleteforum']) {
	checkLeaderRights();
	$res1=mysql_query("SELECT post_id FROM clanf_posts WHERE forum_id='".$_GET['deleteforum']."'");
	while($data1=mysql_fetch_assoc($res1)) {
		mysql_query("DELETE FROM clanf_posts_text WHERE post_id='".$data1['post_id']."'");
	}
	mysql_query("DELETE FROM clanf_posts WHERE forum_id='".$_GET['deleteforum']."'");
	mysql_query("DELETE FROM clanf_topics WHERE forum_id='".$_GET['deleteforum']."'");
	mysql_query("DELETE FROM clanf_auth_access WHERE forum_id='".$_GET['deleteforum']."'");
	mysql_query("DELETE FROM clanf_forums WHERE forum_id='".$_GET['deleteforum']."'");
}
if($_GET['makemod']) {
	checkLeaderRights();
	mysql_query("INSERT INTO clanf_user_group (group_id,user_id,user_pending) VALUES ('3','".mysql_escape_string($_GET['makemod'])."','0')");
	header("Location: ".$PHP_SELF."?setmod=true");
}
if($_GET['removemod']) {
	checkLeaderRights();
	$res1=mysql_query("SELECT clanstatus FROM player WHERE id='".mysql_escape_string($_GET['removemod'])."'");
	$data1=mysql_fetch_assoc($res1);
	if($data1['clanstatus'] != 63) {
		mysql_query("DELETE FROM clanf_user_group WHERE group_id='3' AND user_id='".mysql_escape_string($_GET['removemod'])."'");
		header("Location: ".$PHP_SELF."?setmod=true");
	} else {
		echo "<b class=\"error\">Ordensführern können die Moderatorenrechte nicht entzogen werden!</b><br />";
	}
}
if($_GET['setmod']) {
	checkLeaderRights();
	$res1=mysql_query("SELECT id, name FROM player WHERE clan = '".$_SESSION['player']->clan."'");
	echo "<table width=\"550\" style=\"font-size:12px;\">\n";
	echo "<tr><td colspan=\"4\" class=\"tblhead\" height=\"30\" valign=\"middle\"><b>Internes Forum Administrieren</b></td></tr>";
	echo "<tr><td colspan=\"2\" height=\"30\" valign=\"middle\" class=\"tblbody\"><b>Moderatoren verwalten</b></td></tr>";
	while($data1=mysql_fetch_assoc($res1)) {
		$res2=mysql_query("SELECT user_id FROM clanf_user_group WHERE group_id = '3' AND user_id='".$data1['id']."'");
		if(mysql_num_rows($res2) > 0)
			echo "<tr><td class=\"tblhead\" width=\"300\"><b>".$data1['name']."</b></td><td class=\"tblbody\"><a href=\"".$PHP_SELF."?removemod=".$data1['id']."\">Moderatorenrechte entziehen</a></td></tr>";
		else
			echo "<tr><td class=\"tblhead\" width=\"300\">".$data1['name']."<td class=\"tblbody\"><a href=\"".$PHP_SELF."?makemod=".$data1['id']."\">Moderatorenrechte vergeben</a></td></tr>";
	}
	echo "<tr><td colspan=\"2\" class=\"tblbody\" height=\"30\" valign=\"middle\"><a href=\"javascript:history.back(1)\">zur&uuml;ck</a></td></tr>\n";
	echo "</table>\n";
	exit;
}
if($_GET['editforum']) {
	checkLeaderRights();

	$res1=mysql_query("SELECT cat_id FROM clanf_categories WHERE cat_order = '".$_SESSION['player']->clan."'");
	$data1=mysql_fetch_assoc($res1);
	$clan_cat=$data1['cat_id'];
	$res2=mysql_query("SELECT forum_name, forum_desc, forum_order, cat_id FROM clanf_forums WHERE forum_id='".$_GET['editforum']."'");
	$data2=mysql_fetch_assoc($res2);

	if($data2['cat_id'] != $data1['cat_id'])
		exit;

	echo "<table style=\"width:550px; font-size:12px;\">\n";
	echo "<tr><td colspan=\"4\" class=\"tblhead\" height=\"30\" valign=\"middle\"><b>Internes Forum Administrieren</b></td></tr>";
	echo "<tr><td class=\"tblhead\" colspan=\"4\"><b>Forum bearbeiten</b></td></tr>";
	echo "<tr><td class=\"tblbody\" colspan=\"4\" style=\"text-align:center;\">\n";
	echo "<form method=\"post\" action=\"".$PHP_SELF."\">";
	echo "<input type=\"text\" style=\"width:550px;\" name=\"edtname\" value=\"".$data2['forum_name']."\" /><br />";
	echo "<textarea name=\"edtdesc\"style=\"width:550px; heigth:200px;\" rows=\"5\" />".$data2['forum_desc']."</textarea><br />";
	echo "</td></tr>";
	echo "<tr><td colspan=\"2\" width=\"200\" class=\"tblhead\"><b>Rangfolge</b></td>\n";
	echo "<td class=\"tblbody\" colspan=\"2\"><input type=\"text\" name=\"edtorder\" value=\"".$data2['forum_order']."\" maxlength=\"2\" size=\"3\" />\n";
	echo "</td></tr>\n";
	echo "<tr><td class=\"tblhead\" colspan=\"4\" style=\"text-align:center;\">\n";
	echo "<input type=\"hidden\" value=\"".$_GET['editforum']."\" name=\"edtforum\" />\n";
	echo "<input type=\"submit\" value=\"Forum &auml;ndern\" />\n";
	echo "</td></tr>";
	echo "</form>";
	echo "</td></tr>";
	echo "<tr><td colspan=\"4\" class=\"tblbody\" height=\"30\" valign=\"middle\"><a href=\"javascript:history.back(1)\">zur&uuml;ck</a></td></tr>\n";
	echo "</table>\n";
	exit;
}


define('IN_PHPBB', true);
$phpbb_root_path = './clanforum/';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX);




if($userdata['session_logged_in'] != 1)
	include_once("cf_login.php");
init_userprefs($userdata);
//
// End session management
//



if($userdata['user_id']==-1) {
	session_end($userdata['session_id'], $userdata['user_id']);
	echo "you are anonymous! (cf_index)";
	exit;
	header("Location: cf_index.php");
}

############################################################ AUTO LOGOUT
// for second session management <-- useful
if($_SESSION['player']->name!=$userdata['username']) {
	if( $userdata['session_logged_in'] ) {
		session_end($userdata['session_id'], $userdata['user_id']);
	}
	/* echo "user != hw_user --> dying (cf_index)";
	exit; */
	header("Location: cf_index.php");
	exit;
}



$viewcat = ( !empty($HTTP_GET_VARS[POST_CAT_URL]) ) ? $HTTP_GET_VARS[POST_CAT_URL] : -1;

if( isset($HTTP_GET_VARS['mark']) || isset($HTTP_POST_VARS['mark']) )
{
	$mark_read = ( isset($HTTP_POST_VARS['mark']) ) ? $HTTP_POST_VARS['mark'] : $HTTP_GET_VARS['mark'];
}
else
{
	$mark_read = '';
}

//
// Handle marking posts
//
if( $mark_read == 'forums' )
{
	if( $userdata['session_logged_in'] )
	{
		setcookie($board_config['cookie_name'] . '_f_all', time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
	}

	$template->assign_vars(array(
		"META" => '<meta http-equiv="refresh" content="3;url='  .append_sid("cf_index.$phpEx") . '">')
	);

	$message = $lang['Forums_marked_read'] . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . append_sid("cf_index.$phpEx") . '">', '</a> ');

	message_die(GENERAL_MESSAGE, $message);
}
//
// End handle marking posts
//

$tracking_topics = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t"]) : array();
$tracking_forums = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_f"]) : array();

//
// If you don't use these stats on your index you may want to consider
// removing them
//
$total_posts = get_db_stat('postcount');
$total_users = get_db_stat('usercount');
$newest_userdata = get_db_stat('newestuser');
$newest_user = $newest_userdata['username'];
$newest_uid = $newest_userdata['user_id'];

if( $total_posts == 0 )
{
	$l_total_post_s = $lang['Posted_articles_zero_total'];
}
else if( $total_posts == 1 )
{
	$l_total_post_s = $lang['Posted_article_total'];
}
else
{
	$l_total_post_s = $lang['Posted_articles_total'];
}

if( $total_users == 0 )
{
	$l_total_user_s = $lang['Registered_users_zero_total'];
}
else if( $total_users == 1 )
{
	$l_total_user_s = $lang['Registered_user_total'];
}
else
{
	$l_total_user_s = $lang['Registered_users_total'];
}


//
// Start page proper
//
$sql = "SELECT c.cat_id, c.cat_title, c.cat_order, clan.name as clan_name
	FROM " . CATEGORIES_TABLE . " c 
	LEFT JOIN clan ON c.cat_order=clan.id
	ORDER BY c.cat_order";
if( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query categories list', '', __LINE__, __FILE__, $sql);
}

$category_rows = array();
while( $category_rows[] = $db->sql_fetchrow($result) );
$db->sql_freeresult($result);

if( ( $total_categories = count($category_rows) ) )
{
	//
	// Define appropriate SQL
	//

	$res1=mysql_query("SELECT cat_id FROM clanf_categories WHERE cat_order = '".$_SESSION['player']->clan."'");
	$data1=mysql_fetch_assoc($res1);
	$clanforumid=$data1['cat_id'];

			$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id
				FROM (( " . FORUMS_TABLE . " f
				LEFT JOIN " . POSTS_TABLE . " p ON p.post_id = f.forum_last_post_id )
				LEFT JOIN " . USERS_TABLE . " u ON u.user_id = p.poster_id )
				WHERE cat_id='".$clanforumid."' 
				ORDER BY f.cat_id, f.forum_order";

	if($_SESSION['player']->clanstatus == 63) {
		echo "<br /><div style=\"width:100%; padding:4px; border: 1px solid red; text-align:center;\"><a href=\"".$PHP_SELF."?setup=".$_SESSION['player']->clan."\">Forum Administrieren</a></div><br />";
	}

	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forums information', '', __LINE__, __FILE__, $sql);
	}

	$forum_data = array();
	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_data[] = $row;
	}
	$db->sql_freeresult($result);


	if ( !($total_forums = count($forum_data)) )
	{
		message_die(GENERAL_MESSAGE, $lang['No_forums']);
	}


	//
	// Obtain a list of topic ids which contain
	// posts made since user last visited
	//
	if ( $userdata['session_logged_in'] )
	{
		$sql = "SELECT t.forum_id, t.topic_id, p.post_time 
			FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
			WHERE p.post_id = t.topic_last_post_id 
				AND p.post_time > " . $userdata['user_lastvisit'] . " 
				AND t.topic_moved_id = 0"; 
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not query new topic information', '', __LINE__, __FILE__, $sql);
		}

		$new_topic_data = array();
		while( $topic_data = $db->sql_fetchrow($result) )
		{
			$new_topic_data[$topic_data['forum_id']][$topic_data['topic_id']] = $topic_data['post_time'];
		}
		$db->sql_freeresult($result);
	}

	//
	// Obtain list of moderators of each forum
	// First users, then groups ... broken into two queries
	//
	$sql = "SELECT aa.forum_id, u.user_id, u.username 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
		WHERE aa.auth_mod = " . TRUE . " 
			AND g.group_single_user = 1 
			AND ug.group_id = aa.group_id 
			AND g.group_id = aa.group_id 
			AND u.user_id = ug.user_id 
		GROUP BY u.user_id, u.username, aa.forum_id 
		ORDER BY aa.forum_id, u.user_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
	}

	$forum_moderators = array();
	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="' . append_sid("info.$phpEx?show=player&name=".$row['username']) . '">' . $row['username'] . '</a>';
	}
	$db->sql_freeresult($result);

	$sql = "SELECT aa.forum_id, g.group_id, g.group_name 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
		WHERE aa.auth_mod = " . TRUE . " 
			AND g.group_single_user = 0 
			AND g.group_type <> " . GROUP_HIDDEN . "
			AND ug.group_id = aa.group_id 
			AND g.group_id = aa.group_id 
		GROUP BY g.group_id, g.group_name, aa.forum_id 
		ORDER BY aa.forum_id, g.group_id";

	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
	}

	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="' . append_sid("cf_groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $row['group_id']) . '">' . $row['group_name'] . '</a>';
	}
	$db->sql_freeresult($result);

	//
	// Find which forums are visible for this user
	//

	
	$is_auth_ary = array();
	$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata, $forum_data);
	


	//
	// Start output of page
	//
	define('SHOW_ONLINE', true);
	$page_title = $lang['Index'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		'body' => 'index_body.tpl')
	);


	$template->assign_vars(array(
		'TOTAL_POSTS' => sprintf($l_total_post_s, $total_posts),
		'TOTAL_USERS' => sprintf($l_total_user_s, $total_users),
		'NEWEST_USER' => sprintf($lang['Newest_user'], '<a href="' . append_sid("info.$phpEx?show=player&amp;name=$newest_user") . '">', $newest_user, '</a>'), 

		'FORUM_IMG' => $images['forum'],
		'FORUM_NEW_IMG' => $images['forum_new'],
		'FORUM_LOCKED_IMG' => $images['forum_locked'],

		'L_FORUM' => $lang['Forum'],
		'L_TOPICS' => $lang['Topics'],
		'L_REPLIES' => $lang['Replies'],
		'L_VIEWS' => $lang['Views'],
		'L_POSTS' => $lang['Posts'],
		'L_LASTPOST' => $lang['Last_Post'], 
		'L_NO_NEW_POSTS' => $lang['No_new_posts'],
		'L_NEW_POSTS' => $lang['New_posts'],
		'L_NO_NEW_POSTS_LOCKED' => $lang['No_new_posts_locked'], 
		'L_NEW_POSTS_LOCKED' => $lang['New_posts_locked'], 
		'L_ONLINE_EXPLAIN' => $lang['Online_explain'], 

		'L_MODERATOR' => $lang['Moderators'], 
		'L_FORUM_LOCKED' => $lang['Forum_is_locked'],
		'L_MARK_FORUMS_READ' => $lang['Mark_all_forums'], 

		'U_MARK_READ' => append_sid("cf_index.$phpEx?mark=forums"))
	);


	//
	// Okay, let's build the index
	//


	for($i = 0; $i < $total_categories; $i++)
	{

		$cat_id = $category_rows[$i]['cat_id'];

		//
		// Should we display this category/forum set?
		//
		$display_forums = true;
		for($j = 0; $j < $total_forums; $j++)
		{
			if ( $is_auth_ary[$forum_data[$j]['forum_id']]['auth_view'] && $forum_data[$j]['cat_id'] == $cat_id )
			{
				$display_forums = true;
			}

			if($cat_id!=$clanforumid) {
				$display_forums = false;
			}

		}
		//
		// Yes, we should, so first dump out the category
		// title, then, if appropriate the forum list
		//

		if ( $display_forums )
		{

			$template->assign_block_vars('catrow', array(
				'CAT_ID' => $cat_id,
				'CAT_DESC' => $category_rows[$i]['clan_name'],
				'U_VIEWCAT' => append_sid("cf_index.$phpEx?" . POST_CAT_URL . "=$cat_id"))
			);
		
			if ( $viewcat == $cat_id || $viewcat == -1 )
			{
				for($j = 0; $j < $total_forums; $j++)
				{
					if ( $forum_data[$j]['cat_id'] == $cat_id )
					{
						$forum_id = $forum_data[$j]['forum_id'];

						if ( $is_auth_ary[$forum_id]['auth_view'] )
						{
							if ( $forum_data[$j]['forum_status'] == FORUM_LOCKED )
							{
								$folder_image = $images['forum_locked']; 
								$folder_alt = $lang['Forum_locked'];
							}
							else
							{
								$unread_topics = false;
								if ( $userdata['session_logged_in'] )
								{
									if ( !empty($new_topic_data[$forum_id]) )
									{
										$forum_last_post_time = 0;

										while( list($check_topic_id, $check_post_time) = @each($new_topic_data[$forum_id]) )
										{
											if ( empty($tracking_topics[$check_topic_id]) )
											{
												$unread_topics = true;
												$forum_last_post_time = max($check_post_time, $forum_last_post_time);

											}
											else
											{
												if ( $tracking_topics[$check_topic_id] < $check_post_time )
												{
													$unread_topics = true;
													$forum_last_post_time = max($check_post_time, $forum_last_post_time);
												}
											}
										}

										if ( !empty($tracking_forums[$forum_id]) )
										{
											if ( $tracking_forums[$forum_id] > $forum_last_post_time )
											{
												$unread_topics = false;
											}
										}

										if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all']) )
										{
											if ( $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f_all'] > $forum_last_post_time )
											{
												$unread_topics = false;
											}
										}

									}
								}

								$folder_image = ( $unread_topics ) ? $images['forum_new'] : $images['forum']; 
								$folder_alt = ( $unread_topics ) ? $lang['New_posts'] : $lang['No_new_posts']; 
							}

							$posts = $forum_data[$j]['forum_posts'];
							$topics = $forum_data[$j]['forum_topics'];

							if ( $forum_data[$j]['forum_last_post_id'] )
							{
								$last_post_time = create_date($board_config['default_dateformat'], $forum_data[$j]['post_time'], $board_config['board_timezone']);

								$last_post = $last_post_time . '<br />';

								$last_post .= ( $forum_data[$j]['user_id'] == ANONYMOUS ) ? ( ($forum_data[$j]['post_username'] != '' ) ? $forum_data[$j]['post_username'] . ' ' : $lang['Guest'] . ' ' ) : "<a href=\"".append_sid("info.$phpEx?show=player&amp;name=".$forum_data[$j]['username'])."\">".$forum_data[$j]['username']."</a>";
								
								$last_post .= '<a href="' . append_sid("cf_viewtopic.$phpEx?"  . POST_POST_URL . '=' . $forum_data[$j]['forum_last_post_id']) . '#' . $forum_data[$j]['forum_last_post_id'] . '"><img src="clanforum/' . $images['icon_latest_reply'] . '" border="0" alt="' . $lang['View_latest_post'] . '" title="' . $lang['View_latest_post'] . '" /></a>';
							}
							else
							{
								$last_post = $lang['No_Posts'];
							}

							if ( count($forum_moderators[$forum_id]) > 0 )
							{
								$l_moderators = ( count($forum_moderators[$forum_id]) == 1 ) ? $lang['Moderator'] : $lang['Moderators'];
								$moderator_list = implode(', ', $forum_moderators[$forum_id]);
							}
							else
							{
								$l_moderators = '&nbsp;';
								$moderator_list = '&nbsp;';
							}

							$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
							$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

//hack
							$viewf=false;
							if($forum_data[$j]['forum_status']<2) {
								$viewf=true;
							} else {
								if($_SESSION['player']->clanstatus > 1) {
									$viewf=true;
                  $forum_data[$j]['forum_name'] .= '<span class="genmed"> (Ministerforum)</span>';
                }
							}

							if($viewf==true) {
							$template->assign_block_vars('catrow.forumrow',	array(
								'ROW_COLOR' => '#' . $row_color,
								'ROW_CLASS' => $row_class,
								'FORUM_FOLDER_IMG' => $folder_image, 
								'FORUM_NAME' => $forum_data[$j]['forum_name'],
								'FORUM_DESC' => $forum_data[$j]['forum_desc'],
								'POSTS' => $forum_data[$j]['forum_posts'],
								'TOPICS' => $forum_data[$j]['forum_topics'],
								'LAST_POST' => $last_post,
								'MODERATORS' => $moderator_list,

								'L_MODERATOR' => $l_moderators, 
								'L_FORUM_FOLDER_ALT' => $folder_alt, 

								'U_VIEWFORUM' => append_sid("cf_viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))
							);
							}
						}
					}
				}
			}
		}
	} // for ... categories

}// if ... total_categories
else
{
	message_die(GENERAL_MESSAGE, $lang['No_forums']);
}

//
// Generate the page
//
$template->pparse('body');

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>