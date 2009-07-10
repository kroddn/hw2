<?php
/***************************************************
* Copyright (c) 2005-2008 by Holy-Wars 2
*
* written by 
*  Gordon Meiser
*  Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/log.inc.php");
include_once("includes/session.inc.php");
include_once("includes/banner.inc.php");
?>

<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 <meta http-equiv="imagetoolbar" content="no">

<LINK rel="SHORTCUT ICON" href="favicon.ico" type="image/ico"/>
<title><? echo $pagetitle; ?></title>
<script type="text/javascript">
<!--
  var kakadu_x = 475;
  var kakadu_y = 20;

  var mapmenu_x = 450;
  var mapmenu_y = 300;

  var kakadu_visible = true;
  var mapmenu_visible = true;
-->
</script>

</head>
<? echo "<!-- ".$_SESSION['adsmagic']." -->\n"; ?>

<frameset rows="95,*" border=0 frameborder=0 framespacing=0>
  <frame name="logo" src="logo.php" scrolling=no marginwidth=0 marginheight=0 noresize>
  <frameset cols="155,*">
    <frame name="navigation" src="navigation.php" scrolling=no marginwidth=0 marginheight=0 noresize>
    <frameset name="middle" rows="25,*,25">
      <frame name="res" src="top.php" scrolling=no marginwidth=0 marginheight=0 noresize>
<!--      <frame name="bordertop" src="bordertop.php" scrolling=no marginwidth=0 marginheight=0 norezize> -->
      <frameset cols="30,*,30">
	    <frame name="borderleft" src="borderleft.php" scrolling=no marginwidth=0 marginheight=0 noresize>
        <frame name="main" src="main.php" marginwidth=0 marginheight=0 norezize>
        <frame name="borderright" src="borderright.php" scrolling=no marginwidth=0 marginheight=0 norezize>
      </frameset>
      <frame name="borderbottom" src="borderbottom.php" scrolling=no marginwidth=0 marginheight=0 norezize>
    </frameset>
  </frameset>
</frameset>
<noframes>
Ihr Browser unterstützt keine Frames...
</noframes>
</html>
