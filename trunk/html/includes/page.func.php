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

/**
 * Neue HW2-Seite beginnen.
 * FIXME: nach diesem Aufruf sollte 
 *  include_once("includes/update.inc.php");
 * eingebunden werden, damit Top und Bottom-Leite aktualisiert werden
 */
function start_page() { 
  $GLOBALS['page_started'] = TRUE;
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 <title><? echo $GLOBALS['pagetitle']; ?></title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 <meta http-equiv="imagetoolbar" content="no">

 <!--[if gt IE 6]>
  <link rel="stylesheet" href="<? echo $GLOBALS['csspath']; ?>/ie6.css" type="text/css">
 <![endif]-->

 <link rel="stylesheet" href="<? echo $GLOBALS['csspath']; ?>/hw.css?20091202" type="text/css">
 <link rel="stylesheet" href="<? echo $GLOBALS['layoutcsspath']; ?>/layout_v01.css" type="text/css">
</head>

<script language="JavaScript" src="js/timer.js" type="text/javascript" ></script>
<script language="JavaScript" src="js/infopopup_v01.js" type="text/javascript"></script>


<?
  if($GLOBALS['session.inc.php'] && isPlayerSet() ) {
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
      if(isset($_REQUEST['message'])) {
        $href .= "&message=".urlencode($_REQUEST['message']);
      }
    }
    else {
      $href = "login.php?redirect=1&SELF=".$PHP_SELF."&error=".$error;
    }
  }  
  
  // Falls bereits eine Seite gestartet wurde, einen JavaScript Redirekt machen
  if(!isset($GLOBALS['page_started']) || !$GLOBALS['page_started']) {
    start_page();
  }
  
  $url = $href."?error=".$error;
  ?>
    <script language="JavaScript" type="text/javascript">
    <!--
      if(parent) {
    	  parent.window.location.href='<? echo $url; ?>';
      }
      else {
    	  window.location.href='<? echo $url; ?>';
      }
    // -->
    </script>
    Konnte Player nicht initialisieren oder Session-Fehler.<br>
    Wahrscheinlich ist <b>JavaScript</b> nicht aktiviert.
    <p>
    <a href="login.php?error=<? echo $error; ?>">Neues Login</a>
    <p>
    <a href="http://www.holy-wars2.de/portal.php">Hier</a> gehts zum Portal.
           
  <? 

  if($whatisthat) {
    include("portal.php");
  } 
  
  end_page();
  
  // Wichtig: verarbeitung abbrechen!
  exit();
} //  redirect_to
 

?>