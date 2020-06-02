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
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
include_once("includes/log.inc.php");
include_once("includes/player.class.php");

// Seite starten
start_page();
$id = $_GET['id'];
$name = $_GET['name'];
$show = $_GET['show'];
$exactmatch = $_GET['exactmatch'];
?>

<script language="JavaScript">
<!--
function parent_goto(ref){
  if(ref != ""){
    opener.location.href=ref;
    //    window.close();
  }       
}

function minimapname(name) {
  var win = window.open("usermap.php?name="+name,"Karte","width=840,height=820,left=0,top=0,status=no,scrollbars=yes,dependent=yes");
  win.focus();
}
//-->
</script>
<? 
start_body();

//check for errors
if(isset($name) && (!isset($id))) {
  $admin_id = $_SESSION['player']->isMaintainer() && is_numeric($name) ? intval($name) : 0;
  $count=1;
  $name=trim($name);
  if (strlen($name)<3 && $admin_id == 0 ) {
    echo '<h1 class="error">Bitte mindestens 3 Zeichen zur Suche eingeben!</h1><p>';
  }
  else {
    switch ($show) {
      case "player":
        if($admin_id != 0) {
          print_playerinfo($admin_id);
          $count++;
        }
        else {
          if($_SESSION['player']->isMaintainer() && strpos($name, "@") !== FALSE)
          {
            $name = mysqli_escape_string($GLOBALS['con'], $name);
            $res = do_mysql_query("SELECT id FROM player WHERE email LIKE '%".$name."%' ".
                                    " ORDER BY name != '".$name."' LIMIT 20 ");

          }
          // Suche nach IP-Adresse, 4 Mal Zahlen mit Punkt getrennt
          else if($_SESSION['player']->isMaintainer() && preg_match('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/i', $name))
          {
            $name = mysqli_escape_string($GLOBALS['con'], $name);
            $res = do_mysql_query("SELECT DISTINCT p.id FROM log_login l LEFT JOIN player p ON p.login=l.name".
                                    " WHERE l.ip LIKE '%".$name."%' ".
                                    " LIMIT 20 ");

          }
          else {
            $name = mysqli_escape_string($GLOBALS['con'], $name);
            $mask = isset($_REQUEST['exactmatch']) ? $name : "%".$name."%";
            
            $res = do_mysql_query("SELECT id FROM player WHERE name LIKE '$mask' ".
            ($_SESSION['player']->isMaintainer() ? " OR login LIKE '$mask'" : "").
                                  " ORDER BY name != '".$name."' LIMIT 10 ");
          }
        }

        while ($data = mysqli_fetch_assoc($res)) {
          if ($count>1) {
            echo "\n<hr>";
          }
          $id = $data['id'];
          print_playerinfo($id);
          $count++;
        }
        
        break; // player
      case "clan" :
        $name = mysqli_escape_string($GLOBALS['con'], $name);
        $res = do_mysql_query("SELECT id FROM clan WHERE name like '%".$name."%' LIMIT 10");

        while ($data = mysqli_fetch_assoc($res)) {
          if ($count>1) {
            echo "\n<hr>";
          }
          $id = $data['id'];
          print_claninfo($id);
          $count++;
        }
        break;
      case "town":
        $res = do_mysql_query("SELECT id FROM city WHERE name like '%".$name."%' ORDER BY name != '".$name."' LIMIT 10");
        while ($data = mysqli_fetch_assoc($res)) {
          if ($count>1) {
            echo "\n<hr>";
          }
          $id = $data['id'];
          print_towninfo($id);
          $count++;
        }
        break;
    } // switch
  }
} // if (isset($name) && (!isset($id))) {

else {
  if (!isset($id)) {
    echo '<h1 class="error">Fehler: keine ID angegeben</h1><p>';
  } 
  else {
    switch ($show) {
    case "player": 
      echo '<h1 class="error">Fehler: die Anzeige von Spielerdaten über deren ID wird aus Sicherheitsgründen nicht mehr unterstützt. Bitte melden Sie die Stelle, von wo aus dieser Aufruf stattgefunden hat, damit dieser Fehler behoben werden kann.</h1><p>'; 
      break; 
    
    case "town":   print_towninfo($id);   break;
    case "clan":   print_claninfo($id);   break;
    } // switch
  }
}

// Datei abschliessen
if (isset($popup)) {
  echo '<br><br><a href="javascript:window.close()"><u>Schliessen</u></a>';
}
else {
  echo '<br><br><a href="'.$HTTP_REFERER.'"><u>Zurück</u></a>';
}
?>

<script language="JavaScript">
<!-- Begin
  window.focus();
// End -->
</script>

</body>
</html>

<?
/***
 ** Funktionen
 ***/
function print_playerinfo ($id) {
  global $popup, $imagepath;
  $id = intval($id);
  
  $res1 = do_mysql_query("SELECT avatar, status, p.id AS id, p.name, p.login, p.description AS descr, religion, sms, p.clan AS clanid, clan.name AS clanname, clan.id AS clanid FROM player AS p LEFT JOIN clan ON p.clan=clan.id WHERE p.id=".$id);
  if ($data1 = mysqli_fetch_assoc($res1)) {
    if ($data1['religion'] == 1)
      $relstr = "Christentums";
    else
      $relstr = "Islams";

    // Spielername ausgeben	
	echo "<table width=\"400\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\"><tr>\n";
	echo "<td class=\"tblhead\" colspan=\"3\" width=\"100%\">\n";
	echo "<h3 style=\"margin-bottom:0px;\">".$data1['name'];
	if($_SESSION['player']->isMultihunter()) {
	  echo " [ ".$data1['id']." ]";
	}
	echo "</h3>";
  	if($_SESSION['player']->isMaintainer()) {
	  echo " / Login: <b>".$data1['login']."</b>";
	}
	
	echo "\n</td>\n<td class=\"tblhead\" width=\"100\">";
    
    if ($data1['sms'] != null) {
      echo '<a title="An diesen Spieler einer SMS senden" href="sms.php?recipient='.urlencode($data1['name']).'"><img border="0" src="'.$imagepath.'/sms.gif"></a>';
    }
    
    $rowspan = 3;
    if($data1['clanid'] > 0) $rowspan+=1;
    
	echo "</td></tr>\n<tr><td class=\"tblbody\" colspan=\"3\">".getReliImage($data1['religion'])." Anhänger des ".$relstr.'</td>';
         
        echo '<td class="tblhead" rowspan="'.$rowspan.'" style="padding: 0px;" align="center">';
        if($data1['avatar']==2) 
          printf('<img title="Avatar" src="avatar.php?id=%d">', $data1['id']);
        else          
          printf('<img title="Avatar" src="%s/avatar_dummy.jpg">', $GLOBALS['imagepath']);
                 
        echo "<br><a href=\"settings.php?show=game#newavatar\">Will ich auch!</a></td></tr>\n";

        
	if ($data1['clanid'] > 0) {
	  echo "<tr><td class=\"tblbody\" colspan=\"3\">Krieger des Ordens ".$data1['clanname']."</td></tr>\n";
    }
    
    echo "<tr><td class=\"tblbody\" width=\"24%\" style=\"text-align:center;\"><a href=\"messages2.php?msgrecipient=".urlencode($data1['name'])."\">Nachricht schreiben</a></td>";
    
    echo "<td width=\"24%\" class=\"tblbody\" style=\"text-align:center;\">".
      ($data1['clanid'] > 0 
       ? "<a href=\"info.php?show=clan&id=".$data1['clanid']."\">Zur Ordensseite</a>"
       : "-"
       ).
      "</td>";
    
	
	echo "<td width=\"24%\" class=\"tblbody\"><a href='diplomacy.php?name=".urlencode($data1['name'])."'>Diplomatie</td></tr>";

    echo '<tr><td class="tblhead" colspan="3" align="center">'.player_to_adr($data1['name']).'</td></tr>';

    echo "</table>\n";

	echo "</table>\n";


    // EIn Link für Multihunter
    if ($_SESSION['player']->isMultihunter()) {
      echo "<p>".get_href("Multihunter-überprüfung", "multihunter.php?showid=".$data1['id'] );      
      switch ($data1['status']) {
      case 2: echo "<br><b class='error'>Spieler bereits gesperrt</b>"; break;
      case 3: echo "<br><b class='error'>Spieler unter Verdacht</b>"; break;	
      } // switch
    }

	if($data1['descr']) {
		echo "<table style=\"margin-top:10px;\" width=\"400\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\"><tr>\n";
		echo "<td class=\"tblhead\"><strong>H&ouml;rt, was sein Botschafter verkündet:</strong></td></tr>\n";
		echo "<tr><td class=\"tblbody\">".bbCode($data1['descr'])."</td></tr>\n";
		echo "</table>\n";
	}

    echo "<table style=\"margin-top:10px;\" width=\"400\" cellpadding=\"1\" cellspacing=\"1\">\n";
    echo "<tr><td width=\"40\" rowspan=\"2\"><img title=\"Alle Städte zeigen\" src=\"".$imagepath."/windrose_klein.gif\" onclick=\"minimapname('".urlencode($data1['name'])."');\"></td><td class=\"tblhead\" width=\"100%\"><strong>".$data1['name']."s St&auml;dte</strong></td></tr>\n";
    echo "<tr><td class=\"tblbody\"><a href=\"#\" onclick=\"minimapname('".urlencode($data1['name'])."');\">Alle St&auml;dte auf einen Blick</a></td></tr>\n";
    echo "</table>\n";
    
    echo "<table cellpadding=\"1\" cellspacing=\"1\" width=\"400\" style=\"margin-top:10px;\">\n";
    echo "<tr class=\"tblhead\" style=\"font-weight:bold;\">";
    echo "<td>Name</td><td>Koord</td><td>Stadtgröße</td><td>Religion</td><td>Aktion</td></tr>";

    $res2 = do_mysql_query("SELECT city.id,x,y,name,population,prosperity,religion,capital ".
                           " FROM city LEFT JOIN map ON city.id=map.id ".
                           " WHERE city.owner=".$id." ORDER BY capital DESC, y ASC");
    while ($data2 = mysqli_fetch_assoc($res2)) {
      echo '<tr class="tblbody">';
      echo "<td><a href='info.php?"."show=town&id=".$data2['id']. ( isset($popup) ? "&popup=".$popup : "")."'>".( ($data2['capital']) ? "<font color=red>".$data2['name']."</font>" : $data2['name'] )."</a></td>";
      echo "<td>".get_href($data2['x']."/".$data2['y'], "map.php?gox=".$data2['x']."&goy=".$data2['y'])."</td>";
      echo "<td nowrap>".get_population_string($data2['population'], $data2['prosperity'])."</td>";
      if ($data2['religion'] == 1)
	echo "<td>Christ</td>";
      else
	echo "<td>Islam</td>";

      echo "<td>".get_href("Angreifen", "general.php?selectx=".$data2['x']."&selecty=".$data2['y']);

      echo "</td></tr>";
    
    } // while
    echo "</table><br><br>";


  }
}


function print_claninfo ($id) {
  global $popup;
  $id = intval($id);

  $res1 = do_mysql_query("SELECT name,description FROM clan WHERE id=".$id);
  $data1 = mysqli_fetch_assoc($res1);

  $res2 = do_mysql_query("SELECT name,points,religion,clanstatus FROM player WHERE clan = ".$id." ORDER BY clanstatus DESC");


  echo "<table width=\"400\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\"><tr>\n";
  echo "<td class=\"tblhead\" colspan=\"2\">\n";
  echo "<h3 style=\"margin-bottom:0px;\">".$data1['name']."</h3>\n";
  echo "</td></tr>\n";
  $data2 = mysqli_fetch_assoc($res2);
  if($data2['religion'] == 1) {
    echo "<tr><td class=\"tblbody\" colspan=\"2\">Verfechter des Christentums</td></tr>\n";
  } else {
    echo "<tr><td class=\"tblbody\" colspan=\"2\">Krieger des Islams</td></tr>\n";
  }
  echo "<tr><td>&nbsp;</td></tr>\n";
  echo "<tr><td class=\"tblbody\">".bbCode($data1['description'])."</td></tr>\n";
  echo "<tr><td>&nbsp;</td></tr>\n";
  echo "</table>\n";

  echo "<table width=\"400\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\">\n";
  echo "<tr><td class=\"tblhead\">Feinde</td></tr>";
  echo "<tr><td class=\"tblbody\">";

  $resA = do_mysql_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=0 AND clanrel.id2=".$id);
  $resB = do_mysql_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=0 AND clanrel.id1=".$id);
  if(mysqli_num_rows($resA) < 1 && mysqli_num_rows($resB) < 1)
  echo "-";
  while ($dataA = mysqli_fetch_assoc($resA))
  echo "<a href=\"info.php?show=clan&name=".$dataA['name']."\">".$dataA['name']."</a><br />\n";
  while ($dataB = mysqli_fetch_assoc($resB))
  echo "<a href=\"info.php?show=clan&name=".$dataB['name']."\">".$dataB['name']."</a><br />\n";

  echo "</td></tr>\n";

  // Nichtangriffspakte
  echo "<tr><td class=\"tblhead\">Nichtangriffspakte</td></tr>";
  echo "<tr><td class=\"tblbody\">";

  $resA = do_mysql_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=2 AND clanrel.id2=".$id);
  $resB = do_mysql_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=2 AND clanrel.id1=".$id);
  if(mysqli_num_rows($resA) < 1 && mysqli_num_rows($resB) < 1)
  echo "-";
  while ($dataA = mysqli_fetch_assoc($resA))
  echo "<a href=\"info.php?show=clan&name=".$dataA['name']."\">".$dataA['name']."</a><br />\n";
  while ($dataB = mysqli_fetch_assoc($resB))
  echo "<a href=\"info.php?show=clan&name=".$dataB['name']."\">".$dataB['name']."</a><br />\n";
   
  echo "</td></tr>\n";

  // Bündnisse
  echo "<tr><td class=\"tblhead\">Bündnisse</td></tr>";
  echo "<tr><td class=\"tblbody\">";

  $resA = do_mysql_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=3 AND clanrel.id2=".$id);
  $resB = do_mysql_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=3 AND clanrel.id1=".$id);
  if(mysqli_num_rows($resA) < 1 && mysqli_num_rows($resB) < 1)
  echo "-";
  while ($dataA = mysqli_fetch_assoc($resA))
  echo "<a href=\"info.php?show=clan&name=".$dataA['name']."\">".$dataA['name']."</a><br />\n";
  while ($dataB = mysqli_fetch_assoc($resB))
  echo "<a href=\"info.php?show=clan&name=".$dataB['name']."\">".$dataB['name']."</a><br />\n";
  echo "</td></tr></table><br />\n";

  // Status
  $res2 = do_mysql_query("SELECT name,points,religion,clanstatus FROM player WHERE clan = ".$id." ORDER BY clanstatus DESC, name");
  echo "<table width=\"400\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\">\n";
  $cat = -1;
  while ($data2 = mysqli_fetch_assoc($res2)) {
    if($data2['clanstatus'] == 63)
    $clanstatus = "Ordensleiter";
    elseif($data2['clanstatus'] == 0)
    $clanstatus = "Member";
    else {
      if($data2['clanstatus'] & 1)
      $clanstatus = "Finanzminister";
      if($data2['clanstatus'] & 2)
      $clanstatus = "Innenminister";
      if($data2['clanstatus'] & 4)
      $clanstatus = "Au&szlig;enminister";
    }

    if($data2['religion'] == 1)
    $religion = "Christ";
    else
    $religion = "Islam";
     
    // Zeile anhängen falls immernoch derselbe clanstatus
    if($cat == $clanstatus) {
      echo "<tr><td class=\"tblbody\"><a href=\"info.php?show=player&name=".$data2['name']."\">".$data2['name']."</a></td><td class=\"tblbody\" style=\"text-align:right; padding-right:4px;\">".number_format($data2['points'],0,",",".")."&nbsp;Pt.</td><td class=\"tblbody\">".$religion."</td></tr>";
    }
    else {
      echo "<tr><td class=\"tblhead\" colspan=\"3\">".$clanstatus."</td></tr>\n";
      echo "<tr><td class=\"tblbody\"><a href=\"info.php?show=player&name=".$data2['name']."\">".$data2['name']."</a></td><td class=\"tblbody\" style=\"text-align:right; padding-right:4px;\">".number_format($data2['points'],0,",",".")."&nbsp;Pt.</td><td class=\"tblbody\">".$religion."</td></tr>";
      $cat = $clanstatus;
    }
  }
  echo "</table>\n";
}



function print_towninfo ($id) {
  global $popup;
  $id = intval($id);
  
  $res1 = do_mysql_query("SELECT clan.name as clan, city.name AS cityname,player.name AS playername,player.id as playerid,population,capital,x,y,prosperity,city.religion as creligion ".
                         " FROM city LEFT JOIN player ON (player.id=city.owner) LEFT JOIN clan ON player.clan=clan.id LEFT JOIN map ON city.id=map.id ".
                         " WHERE city.id=".$id);

  if ($data1 = mysqli_fetch_assoc($res1)) {
    echo "<br> <br>";
    echo '<table><tr class="tblhead"><td colspan="2">Stadtinfo</td></tr>';
    echo "<tr class='tblbody'><td>Name</td><td>".$data1['cityname']."</tr>\n";
    echo "<tr class='tblbody'><td>Konfession</td><td>".($data1['creligion'] == 1 ? "Christlich" : "Islamisch")."</tr>\n";
    if($data1['playername']) {
      echo "<tr class='tblbody'><td>Besitzer</td><td><a href='".$_SERVER['PHP_SELF']."?show=player&name=".urlencode($data1['playername']).(isset($popup) ? "&popup=".$popup : "")."'>".$data1['playername']."</a></tr>";
      echo "<tr class='tblbody'><td>Orden</td><td>".$data1['clan']."</tr>\n";      
    }
    else {
      echo "<tr class='tblbody'><td>&nbsp;</td><td><b>Herrenlose</b> Stadt</td></tr>";
    }

    // Die Bevölkerung nicht genau anzeigen
    echo "<tr class='tblbody'><td>Stadtgröße</td><td nowrap>".get_population_string($data1['population'], $data1['prosperity'])."</tr>\n";    

    // Hauptstadt anzeigen
    if ($data1['capital'])
      echo "<tr class='tblbody'><td>Hauptstadt</td><td>Ja</tr>\n";
    else
      echo "<tr class='tblbody'><td>Hauptstadt</td><td>Nein</tr>\n";


    echo "<tr class='tblbody'><td>Koordinaten</td><td>".get_href($data1['x']."/".$data1['y'], "map.php?gox=".$data1['x']."&goy=".$data1['y']) ."( ".$id." )</td></tr>";
    echo "<tr class='tblbody'><td>Aktion</td>";

    echo "<td>".get_href("Angreifen", "general.php?selectx=".$data1['x']."&selecty=".$data1['y']);

    if ($_SESSION['player']->isAdmin()) {
      echo '&nbsp;&nbsp;<a onClick="return confirm(\'Diese Stadt löschen?\')" href="adminmaintain.php?citydelete='.$id.'">Löschen</a>';
    }
    
    echo "</td></tr>";

    // Die näheste eigene Stadt bestimmen
    echo "<tr class='tblbody'><td>Nahe Städte</td><td>";
    $cit = do_mysql_query ("SELECT city.id,name,round(sqrt( (".$data1['x']."-x)*(".$data1['x']."-x)+(".$data1['y']."-y)*(".$data1['y']."-y))) AS dist FROM city LEFT JOIN map USING(id) WHERE owner = ".$_SESSION['player']->GetID()." AND city.id != ".$id." ORDER BY dist LIMIT 3");
    
    while ($city=mysqli_fetch_assoc($cit)) {
      echo $city['name']." (".$city['dist'].")<br>";
    }

    $num_atts = do_mysql_query_fetch_assoc("SELECT count(*) AS cnt FROM army WHERE army.end = ".$id.
                                           " AND owner = ".$_SESSION['player']->GetID());
    if($num_atts['cnt'] > 0) {
      echo '<tr class="tblbody"><td colspan="2">'.$num_atts['cnt'].
        " eigene <a target='main' href='general.php?markcity=".$id."'>Truppenbewegungen</a></td></tr>\n";
    }

    echo "</td></tr></table>";
  }
} // function print_towninfo ($id)



// Create a string used for href
// if the info is a popup, use javascript for opening the link
function get_href( $titel, $url ) {
  global $popup;

  if (isset($popup)) {
    return "<a href=\"javascript:parent_goto('".$url."')\">".$titel."</a>";
  }
  else {
    return "<a href=\"".$url."\">".$titel."</a>";
  }
}
?>
