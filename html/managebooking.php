<?php
/***************************************************
* Copyright (c) 2005
*
* written by
* Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.config.php");

include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/admin.inc.php");
?>

<html>
<head>
<title>Voranmeldungen</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
if(!$player->isMaintainer()) {
  die ("<h1 class=\"error\">Ihr seid kein Maintainer!</h1></body></html>");
}

if(isset($activate) && $activate > 0) {
  activate_from_booking($activate);
}

if(isset($getoldpremiumacc) && isset($bookid)) {
  $oldpid = do_mysql_query_fetch_assoc("SELECT b.oldpid,p.id FROM booking b LEFT JOIN player p USING(name) WHERE bookid = $bookid");

  if(defined("BOOKING_OLD_PREMIUM"))
    get_old_premiumacc($oldpid['id'], $oldpid['oldpid'], BOOKING_OLD_PREMIUM);
  else
    get_old_premiumacc($oldpid['id'], $oldpid['oldpid']);
}

?>

<h1>Voranmeldungen freischalten</h1>

<a href="?activate=1">1 freischalten.</a><br>
<a href="?activate=5">5 freischalten.</a><br>
<a href="?activate=10">10 freischalten.</a>
<p>

<table><tr><td></td><td>Zeit</td><td>Old-ID</td><td>Old Name</td><td>Name</td><td>PW(MD5)</td><td colspan="3"></td><td>Email</td><td>SMS</td><td colspan="2"></td></tr>
<?
$book = do_mysql_query("SELECT * FROM booking ORDER BY bookid");

$i = 0;
while ($b = mysql_fetch_assoc($book)) {
  echo '<tr class="book'.$i.'">';
  foreach($b as $k => $v) {
    if($k == 'booktime')
      echo "<td nowrap>".date("d.m.y G:i:s",$v)." ($v)</td>\n";
    else
      echo "<td>$v</td>\n";
  }
  
  $res2 = do_mysql_query("SELECT * FROM player p LEFT JOIN premiumacc pa ON pa.player = p.id WHERE name LIKE '".$b['name']."' AND pa.id IS NOT NULL");

  if (mysql_num_rows($res2) == 0)
    echo "<td><a href=\"managebooking.php?bookid=".$b['bookid']."&getoldpremiumacc=1\">Premium</a></td>\n";
  echo "</tr>\n";

  $i = ($i+1)%2;
}
?>

</table>
</body>
</html>