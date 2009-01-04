<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2009 
    by Markus Sinner, Gordon Meiser, Laurenz Gamper, Stefan Neubert

    "Holy-Wars 2" is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Former copyrights see below.
 **************************************************************************/


function start_page() {
?>
<html>
<head>
 <title><? echo $GLOBALS['pagetitle']; ?></title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 <meta http-equiv="imagetoolbar" content="no">
</head>

<script language="JavaScript" src="js/timer.js"></script>
<script language="JavaScript" src="js/infopopup_v01.js"></script>

<!--[if gt IE 6]>
<link rel="stylesheet" href="<? echo $GLOBALS['csspath']; ?>/ie6.css" type="text/css">
<![endif]-->

<link rel="stylesheet" href="<? echo $GLOBALS['csspath']; ?>/hw_v06.css" type="text/css">
<link rel="stylesheet" href="<? echo $GLOBALS['layoutcsspath']; ?>/layout_v01.css" type="text/css">

<?
  if($GLOBALS['session.inc.php']) {
    echo "<!-- UPDATE -->\n";
    include_once("includes/update.inc.php");
  }
} // start_page



function start_body($banner = true) {
?>
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $GLOBALS['imagepath']; ?>/bg.gif">
<? if($banner) { ?>
 <div align="center">
  <? echo show_banner(0); ?>
 </div>
<br>
<? } // if banner
} // start_body()


function end_page() {
  if(!$GLOBALS['standalone'] && isset($_SESSION['player']) && $_SESSION['player']->tutorialLevel() >= 0) {
    include("includes/tutorial.inc.php");
    print_kakadu();
  }
 
  echo "</body>\n</html>\n";
}


function redirect_to($href = null) {
  global $error, $PHP_SELF;

  if ($href==null) {
    // Falls das redirekt vom index.php kommt dann portal zeigen. Ansonsten login.
    if (strstr($PHP_SELF, "index.php")) {
      $href = "portal.php?SELF=".$PHP_SELF;
    }
    else {
      $href = "login.php?redirect=1&SELF=".$PHP_SELF."&error=".$error;
    }
  }  
  ?>
 <html><body>
    <script language="JavaScript">
    <!-- Begin
    parent.window.location.href='<? echo $href;?>';
 // End -->
 </script>
Konnte Player nicht initialisieren oder Session-Fehler.<p><a href="javascript:parent.window.location.href='login.php?error=<? echo $error; ?>'">Neues Login</a>
<p>
<a href="http://www.holy-wars2.de/portal.php">Hier</a> gehts zum Portal.
<? 
  include("portal.php"); 
  echo '</body></html>'; 
  exit;
}


?>