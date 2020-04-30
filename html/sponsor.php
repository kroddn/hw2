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


start_page(); 
start_body();
?>

<h1>Besuchen Sie unseren Sponsor:</h1>
<table border="0" cellpadding="0" cellspacing="0">
<tr><td style="border: solid black 2px;">
<?php
include_once("includes/banner.inc.php");

if (isset($bannerpage)) 
     printBanner($bannerpage);    
else if (isset($id))
     printBanner($id);
else
     printBanner();
?>
</td><td width="99%"></td></tr>
<tr height="10"><td colspan="2"></td></tr>
<tr><td colspan="2">
<a href="all.php">Zurück zur Übersicht der Werbepartner</a>
</td></tr>
</table>

</body>
</html>
