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

/***************************************************
* Copyright (c) 2003
*
* Stefan Neubert, Stefan Hasenstab
*
* This File must not be used without permission !
***************************************************/

// Diese Seite nicht zu den Clicks zählen
$GLOBALS['noclickcount'] = 1;

include("includes/session.inc.php");
?>
<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body class="nopadding" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<table height="100%" cellspacing="0" cellpadding="0" border="0">
<tr>

<td width="30" height="100%" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_right.gif"></td>
</tr>

<!--

<td width="20" height="35" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_right.gif"></td>
</tr>
<tr>
<td width="20" height="35" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_outgoingbottom.gif"></td>
</tr>
<tr>
<td width="20" height="100%" nowrap class="nopadding"></td>
</tr>
<tr>
<td width="20" height="35" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_outgoingtop.gif"></td>
</tr>
<tr>
<td width="20" height="35" nowrap class="nopadding" background="<? echo $imagepath; ?>/border_right.gif"></td>
</tr>
-->
</table>
</body>
</html>