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

include("includes/db.inc.php"); 
include("includes/player.class.php");
session_start();
?>
<html>
<head>
 <title>HW2-NoADS und HW2-Premium-Account</title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<link rel="stylesheet" href="<? echo $GLOBALS['$csspath']; ?>/hw.css" type="text/css">

<style>
body,div,a {
  font-family: Tahoma;
  font-size: 12px;
  color: 000000;
  margin: 2px;
}
h1 {  font-size: 18px;  }
h2 {  font-size: 14px; color: 202020; }
</style>

<body marginwidth="0" marginheight="0" topmargin="2" leftmargin="2" background="<? echo $imagepath; ?>/bg.gif">
<table width="600" align="center"><tr><td>
<center><a href='noads.php'><img src='<? echo $imagepath; ?>/noads.jpg' border='0' alt='Premium Acc, kein Banner'></a></center><br>

<?php
if(isset($_SESSION['player'])) {
  $pid = $_SESSION['player']->getID();
  $sql = "SELECT count(*) AS cnt FROM premiumacc WHERE player = ".$pid;
  $cnt = do_mysql_query_fetch_assoc($sql);
  
  if($cnt['cnt'] > 0)
  {
    if($_SESSION['premium_flags'] == 0)
    {
?>
      <h1>Premium Pro testen</h1>
      Ihr habt/hattet bereits einen Testaccount beantragt.
<?
    }
  }
  else 
  {
    $days = defined("PREMIUM_TEST_DURATION") ? PREMIUM_TEST_DURATION : 7;
    
    if(isset($_REQUEST['testpremium']))
    {
      $type = 15;
      
      $sql  = sprintf("INSERT INTO premiumacc (player, type, start, expire, paytext) ".
                      "VALUES (%d, %d, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()+%d, 'Premium-Pro KOSTENLOS testen.')",
                      $pid, $type, $days * 3600 * 24);
      do_mysql_query($sql);
      printf("<h1>Premium-Pro Erfolgreich aktiviert</h1>\nBitte <b>loggen Sie sich neu ein</b>, um den Account zu aktivieren!");
    }
    else 
    {
      printf("<h1>Premium Pro testen</h1>\nIhr könnt %d Tage Premium-Pro <b>kostenlos</b> und uneingeschränkt* testen! Einfach auf den folgenden Button klicken:<p>\n", $days);
?>  
      <form method="GET" action="premium.php">
        <input type="submit" name="testpremium" value=" Jetzt kostenlos testen! "/>
      </form>
      Die Besonderheiten von Premium Pro sind <a href="#accounts">weiter unten</a> einsehbar.<br>
      <small>*) Keine SMS, kein Namensschutz</small>
<?
    } // else if(isset($REQUEST['testpremium']))
  }
} 
?>
