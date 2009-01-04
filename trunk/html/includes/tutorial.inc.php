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
 * Copyright (c) 2008
 *
 * Markus Sinner  <kroddn@holy-wars2.de>
 *
 ********************************************/

include_once("includes/db.class.php");

$_SESSION['db'] = new DB("mysql");


/**
 * print the html-code of kakadu
 */
function print_kakadu() {
  global $popup;
  
  $request_string = $_SERVER['REQUEST_URI'];
  $db = $_SESSION['db'];

  $imagepath = GFX_PATH_LOCAL;

  echo "\n\n<!-- Tutorial start. Level ".$_SESSION['player']->tutorialLevel()." ! SELF: ".$request_string." -->\n";

  $target_page = $db->query_fetch_array("SELECT page,level FROM global.tutorial_topics ".
                                        " WHERE level > ".($_SESSION['player']->tutorialLevel())." ORDER BY level,sublevel LIMIT 1" );
  if ($target_page[0]) {
    if (ereg($target_page[0], $request_string)) {
      echo "\n<!-- Tutorial increase -->\n";      
      $_SESSION['player']->tutorialInc($target_page[1]);
    }
  }

  if (isset($popup)) return;
?>
 <div name="kakadu" id="kakadu" style="visibility: hidden; position:absolute; width:260px; height:220px; top: 400px; left: 400px; padding: 0px; margin: 0px; border:1px solid white; z-index:10000;">
  <div valign="middle" onDblclick="rollinKakadu()" onMousedown="startMoveMenu('kakadu');" onMouseup="stopMoveKakadu();" style="position:absolute; width:260px; height:17px; margin:0px; padding: 0px; z-index:9; font-size:10px; background-color:red;" title="Kakadu verschieben">
  Lvl.<? echo $_SESSION['player']->tutorialLevel();?>:<span name="sublevel" id="sublevel">0</span>
  </div>
  <div style="position:absolute; top:0; left: 225; width:35px; height:17px;  margin:0px; z-index:10;">
<img onClick="rollinKakadu()" id="kakaduRollinImg" src="<? echo $imagepath; ?>/dialog_top.png" title="Kakadu einrollen"><img onClick="hideKakadu()" src="<? echo $imagepath; ?>/dialog_close.png" title="Kakadu wegschicken. Er erscheint dann auf der nächsten Seite wieder.">
  </div>

  <div name="kakaduframe" id="kakaduframe" style="visibility: hidden; position:absolute; top:0; left: 0; width:260px; height:260px;">
 
   <div name="kakaduframe_hint" id="kakaduframe_hint" style="visibility: hidden; position:absolute; top:18; left: 0; width:260px; height:18px; border: 1px solid black; background-color: #FFFF00;">Komplett deaktivierbar in <a href="settings.php?show=game#game_tutorialsettings.php">MyHW2!</a></div>

   <div onMouseout="kakatext.innerHTML=message" onClick="kakatext.innerHTML='**aarg**\nFinger weg!!!';" onMousein="kakatext.innerHTML='**aarg**\nFinger weg!!!';" style="position:absolute; top: 18; left:-22; z-index:2; width:105px; height:180px; margin:0px; z-index:3;">
   <img border="0" src="<? echo $imagepath; ?>/mage.gif">
   </div>

   <div style="background:url('<? echo $imagepath; ?>/sprechblase.gif') no-repeat; position:absolute; top: 18; left:76; z-index:3; border:0px; width:182px; height:182px; margin:0px; z-index:3;">
    <div name="kakadutext" id="kakadutext" style="background: none; border-style: none; margin-left:38px; margin-top:6px; width:140px; height:169px; font-family:Arial,sans-serif; line-height:11px; color: black; font-size: 10px;">
    </div>
   </div>

   <div style="position:absolute; top:198; left:150; width:80px; height:15px; margin: 0px;"><center>
    <img src="<? echo $imagepath; ?>/dialog_start.png" onClick="firstTopic()">
    <img src="<? echo $imagepath; ?>/dialog_rew.png" onClick="prevTopic()">
    <img src="<? echo $imagepath; ?>/dialog_fwd.png" onClick="nextTopic()">
    <img src="<? echo $imagepath; ?>/dialog_end.png" onClick="lastTopic()"></center>
   </div>
                                                                                                                                                        
   <div name="debugging" id="debugging" style="visibility: hidden; position:absolute; background-color: white; top:220; left:0; width:260px; height:40px;">
    <textarea readonly name="debug" id="debug" style="width:260px; height:20px;  font-family:Arial,sans-serif;  font-size: 10px;">Debug</textarea>
    <textarea readonly name="debug2" id="debug2" style="width:260px; height:20px; font-family:Arial,sans-serif; font-size: 10px;">Debug2</textarea>
   </div>

  </div>
</div>

<script type="text/javascript">
<!--
var activeMoveMenu = null;
var glob_mouse_x;
var glob_mouse_y;

// Funktionen, die bei globalen Funktionen aufgerufen werden sollen (nach der Reihe)
var onmousedownFunctions = new Array();
var onmouseupFunctions   = new Array();
var onmousemoveFunctions = new Array();
var debugFunctions       = new Array();

onmousemoveFunctions.push("moveMenu");
onmouseupFunctions.push("stopMoveMenu");

document.onmousedown= executeOnmousedownFunctions;
window.onmousedown  = executeOnmousedownFunctions;
document.onmouseup  = executeOnmouseupFunctions;
window.onmouseup    = executeOnmouseupFunctions;
document.onmousemove= executeOnmousemoveFunctions;
window.onmousemove  = executeOnmousemoveFunctions;


function doDebug(param) {  
  for (i=0; i<debugFunctions.length; i++) {
    // Es wird eine Temporäre Funktion erzeugt, die wiederum nur 
    // ein Befehlt enthält: sie ruft die im Array gespeicherte 
    // Funktion auf.
    tmp = new Function ("str", debugFunctions[i]+"(str)");
    tmp(param);                
  }
}

function executeOnmousedownFunctions(param) {
  for (i=0; i<onmousedownFunctions.length; i++) {
    // Es wird eine Temporäre Funktion erzeugt, die wiederum nur 
    // ein Befehlt enthält: sie ruft die im Array gespeicherte 
    // Funktion auf.
    tmp = new Function ("ev", onmousedownFunctions[i]+"(ev)");
    tmp(param);
  }
  // Deactivate Selection of text while menu moving
  return activeMoveMenu == null;
}

function executeOnmouseupFunctions(param) {
  for (i=0; i<onmouseupFunctions.length; i++) {
    // Es wird eine Temporäre Funktion erzeugt, die wiederum nur 
    // ein Befehlt enthält: sie ruft die im Array gespeicherte 
    // Funktion auf.
    tmp = new Function ("ev", onmouseupFunctions[i]+"(ev)");
    tmp(param);
  }
}

function executeOnmousemoveFunctions(param) {
  retFunc = true;
  for (i=0; i<onmousemoveFunctions.length; i++) {
    // Es wird eine Temporäre Funktion erzeugt, die wiederum nur 
    // ein Befehlt enthält: sie ruft die im Array gespeicherte 
    // Funktion auf.
    tmp = new Function ("ev", onmousemoveFunctions[i]+"(ev)");
    ret = tmp(param);
    if (ret == -1) retFunc = false
  }
  return false;
}


function startMoveMenu(elem) {
  activeMoveMenu = elem;

  document.onselectstart=new Function ("return false");
  document.onselect=new Function ("return false");

  doDebug("startMoveMenu("+elem+")");
}

function stopMoveMenu(elem) {
  if(activeMoveMenu != null)
    doDebug("stopMoveMenu("+elem+")");

  activeMoveMenu = null;
  document.onselectstart=new Function ("return true");
  document.onselect=new Function ("return true");

}

function moveMenu(ev) {
  var dx = glob_mouse_x;
  var dy = glob_mouse_y;
        
  if (ev != null && ev.pageX != null) {
    glob_mouse_x = ev.pageX;
    glob_mouse_y = ev.pageY;    
  }
  else if (window.event) {
    glob_mouse_x = window.event.clientX;
    glob_mouse_y = window.event.clientY;
  }

  if (activeMoveMenu == null) {
    doDebug("MoveMenu() null");
    return 1;
  }
        
  dx = dx - glob_mouse_x;
  dy = dy - glob_mouse_y;
  doDebug("MoveMenu() gx:"+glob_mouse_x+",gy:"+glob_mouse_y+",dx:"+dx+",dy:"+dy+",Move:"+activeMoveMenu);

  if ( dx != 0 || dy != 0 ) {
    var elem = document.getElementById(activeMoveMenu)
    var new_x = parseInt(elem.style.left) - dx;
    var new_y = parseInt(elem.style.top)  - dy;
  
    if (new_y < 10) new_y = 10;
    if (new_x < 10) new_x = 10;

    elem.style.left = new_x + "px";
    elem.style.top  = new_y + "px";
  }

  return -1;
}


var top=parent;
var topicStrings = new Array();
<? printTutorialTopics(); ?>

var message = topicStrings[0];
var topicPos = 0;

var kaka = document.getElementById("kakadu");
var sublevel = document.getElementById("sublevel");
var kakatext;
var kakadu_x = null;
var kakadu_y = null;

if(top.kakadu_x != null && top.kakadu_y != null) {
  // force_position wird z.B. auf der Karte gesetzt, um den Vogel
  // ganz weit an den Rand zu schieben.
  if (typeof force_position !== 'undefined' && force_position) {
    kakadu_x = force_kakadu_x;
    kakadu_y = force_kakadu_y;
  }
  else {
    if (top.kakadu_y > 700) top.kakadu_y = 700;
    if (top.kakadu_x > 800) top.kakadu_x = 800;  
    if (top.kakadu_y < 10) top.kakadu_y = 10;
    if (top.kakadu_x < 10) top.kakadu_x = 10;  
    kakadu_x = top.kakadu_x;
    kakadu_y = top.kakadu_y;
  }
  kaka.style.left = kakadu_x + "px";
  kaka.style.top  = kakadu_y + "px";
}

function nextTopic() {
  if (topicPos < topicStrings.length-1) topicPos++;
  message = topicStrings[topicPos];
  kakatext.innerHTML=message;
  document.getElementById("sublevel").innerHTML= ""+topicPos;
}
function prevTopic() {
  if (topicPos > 0) topicPos--;
  message = topicStrings[topicPos];
  kakatext.innerHTML=message;
  document.getElementById("sublevel").innerHTML= ""+topicPos;
}
function firstTopic() {
  topicPos = 0;
  message = topicStrings[topicPos];
  kakatext.innerHTML=message;
  document.getElementById("sublevel").innerHTML= ""+topicPos;
}
function lastTopic() {
  topicPos = topicStrings.length - 1;
  message = topicStrings[topicPos];
  kakatext.innerHTML=message;
  document.getElementById("sublevel").innerHTML= ""+topicPos;
}


function hideHint() {
  document.getElementById("kakaduframe_hint").style.visibility="hidden";
}

function showHint() {
  document.getElementById("kakaduframe_hint").style.visibility="visible";
}

function hideKakadu() {
  document.getElementById("kakadu").style.visibility="hidden";
  document.getElementById("kakaduframe").style.visibility="hidden";
<? if ($_SESSION['player']->isAdmin()) echo 'document.getElementById("debugging").style.visibility="hidden";'; ?>

  showHint();
  // Nach 4 Sekunden wegmachen                                         
  setTimeout("hideHint()", 3000);
}

function showKakadu() {
  hideHint()
  document.getElementById("kakadu").style.visibility="visible";
  document.getElementById("kakaduframe").style.visibility="visible";
}

// Anfangs ist das Teil natürlich eingerollt
var kakaduRolledIn;

function rollinKakadu() {
  img = document.getElementById("kakaduRollinImg");
  if (kakaduRolledIn) {
    // Kakadu ausrollen
    img.src = '<? echo $imagepath; ?>/dialog_top.png';
    document.getElementById("kakadu").style.height="220px";
    document.getElementById("kakaduframe").style.height="244px"; 
    document.getElementById("kakaduframe").style.visibility="visible";
<? if ($_SESSION['player']->isAdmin()) echo 'document.getElementById("debugging").style.visibility="visible";'; ?>
    top.kakadu_visible = true;
    hideHint()
  }
  else {
    // Kakadu einrollen
    img.src = '<? echo $imagepath; ?>/dialog_bottom.png';
    document.getElementById("kakadu").style.height="17px";    
    document.getElementById("kakaduframe").style.height="0px"; 
    document.getElementById("kakaduframe").style.visibility="hidden";
<? if ($_SESSION['player']->isAdmin()) echo 'document.getElementById("debugging").style.visibility="hidden";'; ?>
    top.kakadu_visible = false;
  }
  kakaduRolledIn = !kakaduRolledIn;
}


function stopMoveKakadu() {
  stopMoveMenu();

  // Save actual position for next windows
  if(top.kakadu_x != null && top.kakadu_y != null) {
    top.kakadu_x = parseInt(kaka.style.left);
    top.kakadu_y = parseInt(kaka.style.top);
  }
}

function startMoveKakadu() {
  // global gesetzt  var kaka = document.getElementById("kakadu");
  //  if(kaka.style.left)
  //    kakadu_x = parseInt(kaka.style.left);
  //  if(kaka.style.top)
  //    kakadu_y = parseInt(kaka.style.top);

  startMoveMenu("kakadu");
}

function doKakaduMouseMove(ev) {
  moveMenu(ev, "kakadu");
}

function debugKakadu(str) {
  document.getElementById("debug").value=str;
}
debugFunctions.push("debugKakadu");

kakatext=document.getElementById("kakadutext")
kakatext.innerHTML=message;

// Zeig ihn :-)
showKakadu();    

// Falls er vorher eingerollt war, dann roll ihn wieder ein!
if (top && top.kakadu_visible != null && top.kakadu_visible == false) {
  rollinKakadu();
}

<? if ($_SESSION['player']->isAdmin()) { echo 'if (top.kakadu_visible) document.getElementById("debugging").style.visibility="visible";'; }?>

-->
</script>  
<?

} // print_kakadu()


function printTutorialTopics() {
  $request_string = $_SERVER['REQUEST_URI'];
  $db = $_SESSION['db'];

  // Nun die Texte aus der DB greifen und in den Java-Array hauen
  $tut_res = $db->query("SELECT * FROM global.tutorial_topics ".
                        " WHERE level = ".$_SESSION['player']->tutorialLevel()." ORDER BY sublevel");
  $num_r = $db->num_rows($tut_res);

  if ($num_r>0) {
    $found = false;
    while ($tut = $db->fetch_array($tut_res)) {
      $lasttut = $tut;
      if (!$tut['page'] || ereg($tut['page'], $request_string)) {
        echo "topicStrings.push('&nbsp;".$tut['tut_text']."');\n";
        $found = true;
      }
    }
    if (!$found && $lasttut['page'] != null) {
      echo "topicStrings.push('<br>Das Tutorial geht hier nicht weiter.\\n\\nDu mußt nun zu ".$lasttut['page'].", um weiterzumachen.');\n";
    }
  } 
  else if (!$tut['page']) {
    echo "topicStrings.push('<br>Leider ist das Tutorial an dieser Stelle zuende. Solltest Du Vorschläge haben, wie man das Tutorial noch verbessern kann, dann kontaktiere uns doch! Kontaktinformationen kannst Du unter Nachrichten oder im Forum  finden.');\n";    
  }
}

?>