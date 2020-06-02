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
<script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="3763d83c-1489-4708-b693-505c8c39e6c7" data-blockingmode="auto" type="text/javascript"></script>

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
<style>
* {
  box-sizing: border-box;
}

body {
  font-family: Arial, Helvetica, sans-serif;
}

/* Style the header */
header {
  background-color: #666;
  text-align: center;
  color: white;
}

/* Create two columns/boxes that floats next to each other */
nav {
  float: left;
  background: #ccc;
  padding: 10px;
  background-image: url(images/ingame/left.gif);
}

/* Style the list inside the menu */
nav ul {
  list-style-type: none;
  padding: 0;
}

article {
  float: left;
  width: 70%;
  background-image: url(images/ingame/bg.gif);  
}

aside {
  float: left;
  background: #ccc;
  padding: 10px;
  background-image: url(images/ingame/left.gif);
}

/* Clear floats after the columns */
section:after {
  content: "";
  display: table;
  clear: both;
}

/* Style the footer */
footer {
  background-color: #777;
  padding: 10px;
  text-align: center;
  color: white;
}

#top_bar {
  height: 10px;
}



/* Responsive layout - makes the two columns/boxes stack on top of each other instead of next to each other, on small screens */
@media (max-width: 600px) {
  nav, article {
    width: 100%;
    height: auto;
  }
}
</style>
</head>
<? echo "<!-- ".$_SESSION['adsmagic']." -->\n"; ?>


<header>
  <?php include 'logo.php'; ?>
</header>


<nav>
  <?php include 'navigation.php'; ?>
</nav>
  
<article>
  <div id="top_bar">
    <?php include 'top.php'; ?>
  </div>
  <div>
    <?php 
      include 'borderleft.php';
      include 'main.php';
      include 'borderright.php'; 
    ?>
  </div>
</article>
<aside>
  <div><p>hier kommt bald was</p></div>
</aside>


<footer>
  <?php include 'borderbottom.php'; ?>
</footer>
</html>
