<?php
/***************************************************
* Copyright (c) 2003-2004 by holy-wars2.de
*
* written by
* Gordon Meiser
* Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/admin.inc.php");
?>

<html>
<head>
<title>Tool zur Wartung und Ausführung administrativer Aufgaben</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
if(!$player->isMaintainer()) {
  die ("<h1 class=\"error\">Ihr seid kein Maintainer!</h1></body></html>");
}


if($_SESSION['player']->IsAdmin()) {
  include_once("includes/reset.inc.php");

  if(isset($_GET['do_reset']) && isset($_GET['reset_magic'])) {
    do_reset($_GET['reset_magic'], $_GET['reset_map'] ? true : false  );
  }
  
  reset_game_form();
}

?>

</body></html>