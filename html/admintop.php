<?php
/***************************************************
 * Copyright (c) 2003-2007 by Holy-Wars 2
 *
 * written by Gordon Meiser, Markus Sinner
 *
 * This File must not be used without permission	!
 ***************************************************/
include_once("includes/session.inc.php");
?>
<html>
<head>
<title>Admintools</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
if($player->isAdmin() || $player->isMaintainer()) {
?>
<table width="790" cellspacing="1" cellpadding="0" border="0">
 <tr>
  <td> &nbsp </td>
 </tr>
 <tr>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminlog.php">Logdateien</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminnews.php">News</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminstart.php">Zitate</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminavatar.php">Avatare</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminmaintain.php">Wartung</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="managebooking.php">Booking</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="newsletter.php">Newsletter</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminstats.php">Browser</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="fightsim.php">Fightsim</a></td>
  <td width="60" class="tblhead" align="center"><a target="adminmain" href="adminforum.php">Clanforum</a></td>
  <td width="60" class="tblhead" align="center"><a target="main" href="/phpmyadmin">phpMyAdmin</a></td>
  <td width="60" class="tblhead" align="center"><a target="main" href="http://hw2dev.z-gaming.de/websvn/listing.php?repname=hw2&path=%2F&sc=0">webSVN</a></td>
 </tr>
</table>
<?
}
?>
</body>
</html>
