<?php
/***************************************************
* Copyright (c) 2003-2004 by holy-wars.com
*
* written by 
* Gordon Meiser
* Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
?>

<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>

<frameset rows="50,*" border=0 frameborder=0 framespacing=0>
  <frame name="admintop" src="admintop.php" scrolling=no marginwidth=0 marginheight=0 noresize>
  <frame name="adminmain" src="adminlog.php" marginwidth=0 marginheight=0 norezize>
</frameset>
<noframes></noframes>

</html>
