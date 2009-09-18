<?
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

// Careful. This needs "includes/vote.inc.php" from folder "includes" in html Sources.

// This must be defined for include to works
define("GFX_PATH_LOCAL", "");
?>
<html>
<head>
<title>Vote 4 HW2</title>
</head>
<link rel="stylesheet" href="http://game2.holy-wars2.de/images/ingame_v3/css/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" alink="#FF0000" vlink="#FF0000" background="bg.gif">
<center>
<p>
<p>
<table><tr><td width="400">
<?
include("includes/vote.inc.php");
?>
</td></tr></table>
</center>
</body>
</html>
