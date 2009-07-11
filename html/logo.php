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

// Diese Seite nicht zu den Klicks hinzuzählen
$GLOBALS['noclickcount'] = 1;

include_once("includes/db.inc.php");
include_once("includes/session.inc.php");

$logo = $imagepath."/logo.jpg";
if(defined("HW2DEV") && HW2DEV ) {
  $logo = $imagepath."/logo-dev.jpg";
}
else if(defined("HISPEED") && HISPEED ) {
  $logo = $imagepath."/logohispeed.jpg";
}
else if(defined("SPEED") && SPEED) {
  $logo = $imagepath."/logospeed.jpg";
}


?>
<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<style>
nologo {
  font-family: Tahoma;
  font-size: 12px;
  font-face: bold;
  color: #000000;
}
h1 {
  padding-top: 1px;
  padding-bottom: 1px;
  margin-top: 1px;
  margin-bottom: 1px;
}
</style>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body class="nopadding" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0">
<img class="toplogo" src="<? echo $logo; ?>" alt="Kein Logo?">
<br>
<h1>Fehlen Ihnen Grafiken?</h1>
<div class="nologo">
Dann haben Sie sich mit Grafikpack eingeloggt, obwohl dieser nicht oder unvollständig installiert ist. In diesem Fall überprüfen Sie unter <span class="click" onClick="parent.main.location='settings.php'">Einstellungen (clicken)</span>, ob Sie den richtigen Pfad gewählt haben. Funktioniert dies auch nicht, dann loggen Sie sich neu ein und wählen Sie auf der Loginseite im entsprechenden Feld aus, dass Sie keinen Grafikpack installiert haben (<span class="click" onClick="parent.location='navigation.php?logout=1'">Jetzt hier klicken für Logout</span>).<br>
Falls Ihnen nur einzelne Grafiken fehlen, können Sie diese 
<span class="click" onClick="parent.location='<? echo $imagepath; ?>'">hier herunter laden</span>.
</div>
</body>
</html>
