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

include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
include_once("includes/session.inc.php");
include_once("includes/monument.inc.php");
?>
<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<? include("includes/update.inc.php"); ?>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body background="<? echo $imagepath; ?>/monu_test_bg.jpg">
<?php
print_monuments();
?>
</body>
</html>
