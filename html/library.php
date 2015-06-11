<?php
/***************************************************
* Copyright (c) 2003-2007
*
* Stefan Neubert
* Stefan Hasenstab
* Markus Sinner <sinner@holy-wars2.de>
*
* This File must not be used without permission	!
***************************************************/
if ($frame) {
?>
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>Techtree</title>
 </head>

<frameset rows="*" border=0 frameborder=0 framespacing=0>
  <frame name="logo" src="library.php?standalone=1" scrolling=no marginwidth=0 marginheight=0 noresize>
</frameset>
</html>
<?
  exit();
}

include_once("includes/db.inc.php");
include_once("includes/config.inc.php");
include_once("includes/library.class.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
include_once("includes/banner.inc.php");

if (!isset($library)) {
  if($standalone==1 && !isset($player) || $start==1) {
    $library=new Library(-1);
    session_register("library");
  }
  else {
    $library = new Library($player->getID());
    session_register("library");    
  }
}


// Bei Info wird nicht active Page gesetzt, weil das
// stören würde
//if (isset($player) && $player ) {
//  $player->setActivePage(basename(__FILE__));
//}

if (isset($open)) {
  if (isset($opensub)) { 
    $library->opensub(intval($open),intval($opensub));
  }
  else $library->open(intval($open));
} // if (isset($open))
elseif (isset($close)) {
  if (isset($closesub)) $library->closesub(intval($close),intval($closesub));
  else $library->close(intval($close));
} // elseif (isset($close))
if (isset($s1) && isset($s2)) {
  $library->opensub(intval($s1), intval($s2));
  if (isset($s3)) {
    $library->setActive(intval($s1),intval($s2),intval($s3));
  }
  else {
    $library->setActive(intval($s1),intval($s2), -1 );
    $library->opensub(intval($s1),intval($s2));
  }
} // if


if ($standalone == 1) {
  include("start_header.php");  
}
else {
    if(isset($GLOBALS['topic'])) {
      $library->setActive(0, 0, 0);
    }
?>

<html>
<head>
<title><? echo $pagetitle; ?></title>
 <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
 <META HTTP-EQUIV="Expires" CONTENT="-1">
</head>
<? if (!$standalone) include("includes/update.inc.php"); ?>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<? 
   } // not standalone

if($library->isActive()) $data=$library->getInfo();  ?>
<table cellspacing='1' cellpadding='0' border='0'>
<tr>
<?
if (!isset($menu) || $menu != 0) echo "<td class='tblhead'><h3>Bibliothek</h3></td>\n";
if($library->isActive()) echo "<td class='tblhead'><h3>".$data['topic']."</h3></td>"; 
?>
</tr>
<tr>
<?
if(!isset($menu) || $menu != 0) {
  echo "<td class='tblbody' width='250' valign='top'>\n";
  $library->output();
  echo "</td>";
}

if($library->isActive()) echo "<td class='tblbody' width='".($menu == 0 ? "650" : "500")."' valign='top'><br>".$data['description']."<br></td>"; 
?>
</td>
</tr>
<?
echo "<tr>\n";
echo "<td class='tblbody' ".(!isset($menu) || $menu != 0 ? "colspan='2'" : "")." align='center'>\n";
echo show_banner(0); 
echo "\n<p>\n";
echo "</td>\n";
echo "</tr>\n";
?>
<tr>
<td class='tblbody' <? if(!isset($menu) || $menu != 0) echo "colspan='2'"; ?> align='center'>
In der <a target="_new" href="http://www.holy-wars2.de/wiki">Holy-Wars Wiki</a> findet Ihr weitere Informationen, Neuigkeiten und FAQ.
</td></tr>
</table>

<? end_page(); ?>
