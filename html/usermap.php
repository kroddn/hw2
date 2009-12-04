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
 * Copyright (c) 2004 by holy-wars2.de
 *
 * written by Markus Sinner <kroddn@psitronic.de>
 *
 * This File must not be used without permission
 ***************************************************/
 include_once("includes/db.inc.php");
 include_once("includes/session.inc.php");
 if(isset($_GET['uid']))
 {
    start_page();
    start_body();
    echo '<h1 class="error">Fehler: die Anzeige von Spielerdaten über deren ID wird aus Sicherheitsgründen nicht mehr unterstützt. Bitte melden Sie die Stelle, von wo aus dieser Aufruf stattgefunden hat, damit dieser Fehler behoben werden kann.</h1><p>';
    end_page();
 }
 else if(isset($_GET['name'])) {
    $what = "WHERE player.name = '".mysql_escape_string($_GET['name'])."' OR player.id = ".$_SESSION['player']->id;
    include_once("includes/worldmap.inc.php");
 }
 else {
    $what = "WHERE player.id = ".$_SESSION['player']->id;
    include_once("includes/worldmap.inc.php");
 }
 
?>
