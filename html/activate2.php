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
 
/**************************************************
 * Copyright (c) 2006-2007
 *
 *
 * Markus Sinner
 *
 * This File must not be used without permission!
 ****************************************************/
include ("includes/db.inc.php");
include ("includes/config.inc.php");
include ("includes/util.inc.php");

$imagepath = "images/ingame";
$csspath = "images/ingame/css";

?>
<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="30" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<div align="center">
<table width="480" cellspacing="1" cellpadding="0" border="0">
	<tr>
		<td align="center"><b>Aktivierung</b><br><br></td>
	</tr>
	<tr>
		<td align="center">Ihr Account wurde erfolgreich aktiviert<br><br></td>
	</tr>
	<tr>
		<td align="center"><a href="index.php"><b>Zum Portal</b></a><br><br></td>
	</tr>
<br>
<br>
<br>
</div>
</body>
</html>
