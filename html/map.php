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
 * Copyright (c) 2003-2005
 *
 * Stefan Neubert
 * Stefan Hasenstab
 * Markus Sinner <sinner@psitronic.de>
 *
 * This File must not be used without permission
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
// Needed before session starts...
include_once("includes/map.class.php");
include_once("includes/session.inc.php");

$player->setActivePage(basename(__FILE__));

start_page();
?>

<script language="JavaScript">
<!--
// JavaScript-Funktion öffnet Kindfenster für die Suche nach einer Städten
function popUp(sel) {
  if(sel=="world")
    var win = window.open("usermap.php?uid="+<?php echo $_SESSION['player']->id; ?>,"Weltkarte","width=820,height=800,left=0,scrollbars=yes,top=0,dependent=yes");
  else if(sel=="help")
    var win = window.open("library.php?s1=0&s2=0&s3=0&menu=0","Hilfe","width=700,height=650,left=0,top=0,scrollbars=yes,dependent=yes");
  win.focus();
}

var force_position = true;
var force_kakadu_x = 300;
var force_kakadu_y = 300;


function scrollit(x,y) {
 x = (x*40)-150;
 y = (y*40)-100;
 scroll(x,y);

   // Tutorial scrollen
   if(top && top.kakadu_x != null && x > 100 && y > 100) {
     force_kakadu_x = x + 250;
     force_kakadu_y = y -  50;
   }
}

function hideNavElements() {
	var setText;
	var changeView;
	if(document.getElementById("windrose").style.display=="none") {
		changeView="inline";
		setText="Navigation ausblenden";
	} else {
		changeView="none";
		setText="Navigation einblenden";
	}
	document.getElementById("optNavElements").innerHTML=setText;
	document.getElementById("windrose").style.display=changeView;
	document.getElementById("nav11").style.display=changeView;
	document.getElementById("nav12").style.display=changeView;
	document.getElementById("nav13").style.display=changeView;
	document.getElementById("nav21").style.display=changeView;
	document.getElementById("nav22").style.display=changeView;
	document.getElementById("nav23").style.display=changeView;

}
function tipThis(a,b) {
	document.getElementById("i"+b).innerHTML = a;
	document.getElementById("j"+b).innerHTML = a;
}

function hideCity(id) {
	if(document.getElementById("i"+id).style.display == "none") {
		document.getElementById("i"+id).style.display = "inline";
		document.getElementById("j"+id).style.display = "inline";
		document.getElementById("a"+id).style.display = "inline";
		document.getElementById("b"+id).style.display = "inline";
		document.getElementById("c"+id).style.display = "inline";
	} else {
		document.getElementById("i"+id).style.display = "none";
		document.getElementById("j"+id).style.display = "none";
		document.getElementById("a"+id).style.display = "none";
		document.getElementById("b"+id).style.display = "none";
		document.getElementById("c"+id).style.display = "none";
	}
}
// Nur für IE 5+ und NN 6+
ie5=(document.getElementById && document.all && document.styleSheets)?1:0;
nn6=(document.getElementById && !document.all)?1:0;

// Kontextmenü initialisieren
if (ie5 || nn6) {
  menuWidth=122, menuHeight=183;
  menuStatus=0;

  // Rechter Mausklick: Menü anzeigen, linker Mausklick: Menü verstecken
  document.oncontextmenu=showMenu; //oncontextmenu geht nicht bei NN 6.01
  document.onmouseup=hideMenu;
}

// Kontextmenü anzeigen
function showMenu(e) {
  if(ie5) {
    if(event.clientX>menuWidth) xPos=event.clientX-menuWidth+document.body.scrollLeft;
    else xPos=event.clientX+document.body.scrollLeft;
    if (event.clientY>menuHeight) yPos=event.clientY-menuHeight+document.body.scrollTop;
    else yPos=event.clientY+document.body.scrollTop;
  }
  else {
    if(e.pageX>menuWidth+window.pageXOffset) xPos=e.pageX-menuWidth;
    else xPos=e.pageX;
    if(e.pageY>menuHeight+window.pageYOffset) yPos=e.pageY-menuHeight;
    else yPos=e.pageY;
  }
  document.getElementById("menu").style.left=xPos;
  document.getElementById("menu").style.top=yPos;
  menuStatus=1;
  return false;
}

// Kontextmenü verstecken
function hideMenu(e) {
  if (menuStatus==1 && ((ie5 && event.button==1) || (nn6 && e.which==1))) {
    setTimeout("document.getElementById('menu').style.top=-250",250);
    menuStatus=0;
  }
}

function showCityBorder(leftP,topP,x,y) {
	document.getElementById("cityBorder").style.display="inline";
	document.getElementById("cityBorder").style.left=((parseInt(leftP)*40)-120)+"px";
	document.getElementById("cityBorder").style.top=((parseInt(topP)*40)-120)+"px";
	document.getElementById("citySettle").style.display="inline";
	document.getElementById("citySettle").style.left=((parseInt(leftP)*40)-120)+"px";
	document.getElementById("citySettle").style.top=((parseInt(topP)*40)-120)+"px";
	writeThis="<table width=\"200\" height=\"100%\" border=\"0\"><tr><td valign=\"middle\" align=\"center\" style=\"font-size:12px; font-weight:bold;\">MyLord! Sollen eure Siedler in diese entfernten Gefielde aufbrechen?<br><br>Koordinaten: "+x+":"+y+"<br><br><input type=\"button\" onclick=\"document.location.href='townhall.php?newsettle=true&x="+x+"&y="+y+"';\" value=\"So sei es!\" />&nbsp;&nbsp;<input type=\"button\" onclick=\"document.getElementById('cityBorder').style.display='none'; document.getElementById('citySettle').style.display='none';\" style=\"margin-top:5px;\" value=\"Nein, lasst es\" /></td></tr></table>";
	document.getElementById("citySettle").innerHTML=writeThis;
}
// -->
</script>
<style>
.coordDiv {
	width:39px;
	height:39px;
	text-align:center;
	border-bottom:1px dashed white;
	border-right: 1px dashed white;
}
#cityBorder {    
    opacity: 0.5; /* ab FireFox 3.1 */ 
    -moz-opacity:0.5; 
    filter:Alpha(opacity=50, finishopacity=50, style=2); 
    background-color: white;
	
	position:absolute;
	z-index:310;
	display:none;
	width:200px;
	height:200px;	
}

#citySettle {
	border:1px solid black;
	position:absolute;
	z-index:311;
	display:none;
	width:200px;
	height:200px;
	padding:0px;
	text-align:center;
}

</style>
<?php
// use Session $map - security ;-)
$map = $_SESSION['map'];

//Kein Banner!
start_body(false);

if (isset($grid)) {
  if ($grid==1) $map->switchGrid(true);
  else $map->switchGrid(false);
}

if (isset($gox) && isset($goy) && $gox != null && $goy != null) {
  $cox = $gox;
  $coy = $goy;
}
else {
  $cd = $cities->getCityData();
  $res1=do_mysql_query("SELECT x,y FROM map WHERE id = '".$cd['id']."'");
  $data1 = mysql_fetch_assoc($res1);  
  $coy = $data1['y'];
  $cox = $data1['x'];
}

if (isset($moveright)) {
  $map->moveRight();
}
elseif (isset($moveleft)) {
  $map->moveLeft();
}
elseif (isset($movetop)) {
  $map->moveTop();
}
elseif (isset($movebottom)) {
  $map->moveBottom();
}
elseif (isset($movehalfright)) {
  $map->moveHalfRight();
}
elseif (isset($movehalfleft)) {
  $map->moveHalfLeft();
}
elseif (isset($movehalftop)) {
  $map->moveHalfTop();
}
elseif (isset($movehalfbottom)) {
  $map->moveHalfBottom();
}
elseif (isset($gox) && isset($goy) && $gox != null && $goy != null) {
  $map->moveXY($gox,$goy);
}
else {
  $cd=$cities->getCityData();
  $res1=do_mysql_query("SELECT x,y FROM map WHERE id = '".$cd['id']."'");
  $data1 = mysql_fetch_assoc($res1);  
  $map->moveXY($data1['x'],$data1['y']);
}

$map->output();
$map->centerJavascript($cox,$coy);
 
end_page();
?>
