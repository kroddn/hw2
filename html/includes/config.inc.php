<?php
/*************************************************************************
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
 * This File must not be used without permission
 ***************************************************/

$pagetitle = "Holy-Wars 2 - Runde #2";

// define("URL_BASE", "http://speed.holy-wars2.de");
if (!defined('URL_BASE')) define('URL_BASE', 'http://localhost');

// Standard-GFX Path, falls kein Grafikpfad gewuenscht
if (!defined('GFX_PATH')) define('GFX_PATH', 'images/ingame');
// Relativ zum Lokalen Grafikpfad:
if (!defined('GFX_PATH_LOCAL')) define('GFX_PATH_LOCAL', 'images/ingame');
if (!defined('GFX_VERSION')) define('GFX_VERSION', '3.20');

// Pfad fuer die Avatare ---- Ka wof�r das ist ....
if (!defined('AVATAR_DIR')) define('AVATAR_DIR', '/home/hw2_speed/avatars/');

$messagetypes = array("Spieler","Orden/Diplomatie","Handel","Bau","Milit�r","RolePlay", 9 => "Team-Nachrichten", 10 => "Gesendete Nachrichten");
$messagecolors = array("B79F87","B19695","9EB96E","D1B96E","D1866E","f7c8f2",9 => "FF8000", 10 => "3F8D8D");
$strategycategories= array("Landkarte","St�dte","Spieler","Bedienung","Diplomatie","Fragen und Antworten (FAQ)", "Krieg und Kampf");
$buildingcategories = array("Ressourcengewinnung","Verarbeitung und Lagerung","�ffentliche Einrichtungen","Ausbildung und Waffenproduktion","Stadtverteidigung und -verwaltung","Sondergeb�ude", "Hauptstadtgeb&auml;ude");
$researchcategories = array("Reichsverwaltung","Technik","�ffentliche Einrichtungen","Milit�r","Stadtverteidigung und -verwaltung");
$religions = array("Christentum","Islam");
$unittypes = array("Nahk�mpfer","Fernk�mpfer","Kavallerie");
$speedtypes=array("sehr langsam","langsam","mittel","schnell","sehr schnell");
if (!defined('SPEED_BOTE')) define('SPEED_BOTE', 5);
if (!defined('ADVANCED_TOP')) define('ADVANCED_TOP', 1);
$fpcost[1]=array(3,0.3);
$fpcost[2]=array(5,0.25);
$fpcost[3]=array(7,0.2);
$fpcost[4]=array(9,0.15);
$fpcost[5]=array(11,0.1);
$goldcost[1]=array(3,0.3);
$goldcost[2]=array(5,0.25);
$goldcost[3]=array(7,0.2);
$goldcost[4]=array(9,0.15);
$goldcost[5]=array(11,0.1);


$fpcost[1]=array(3,0.3);
$fpcost[2]=array(5,0.25);
$fpcost[3]=array(7,0.2);
$fpcost[4]=array(9,0.15);
$fpcost[5]=array(11,0.1);
$goldcost[1]=array(3,0.3);
$goldcost[2]=array(5,0.25);
$goldcost[3]=array(7,0.2);
$goldcost[4]=array(9,0.15);
$goldcost[5]=array(11,0.1);

if (!defined('MAX_PLAYER')) define('MAX_PLAYER', 2000);

//Message Defines
if (!defined('MESSAGECAT_SENT')) define('MESSAGECAT_SENT', 5);
//Bitmask for Messages              //  0000.0000.0000.0000
//Bitmasks for Sender
if (!defined('MSG_SENDER_DELETED')) define('MSG_SENDER_DELETED', 1);  			//  0000.0000.0000.0001
if (!defined('MSG_SENDER_READ')) define('MSG_SENDER_READ', 2); 	       			//  0000.0000.0000.0010
//Bitmasks for Recipient
if (!defined('MSG_RECIPIENT_DELETED')) define('MSG_RECIPIENT_DELETED', 16); 	//  0000.0000.0001.0000
if (!defined('MSG_RECIPIENT_READ')) define('MSG_RECIPIENT_READ', 32); 		    //  0000.0000.0010.0000
//Bitmasks for Clans
if (!defined('MSG_CLANMSG')) define('MSG_CLANMSG', 256); 					    //  0000.0001.0000.0000
if (!defined('MSG_CLANMINISTERMSG')) define('MSG_CLANMINISTERMSG', 512); 		//  0000.0010.0000.0000
if (!defined('MSG_CLANFOUNDERMSG')) define('MSG_CLANFOUNDERMSG', 1024);			//  0000.0100.0000.0000
//Generell Bitmasks
if (!defined('MSG_ADMINMSG')) define('MSG_ADMINMSG', 4096); 			        //  0001.0000.0000.0000
?>
