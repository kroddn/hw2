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
 * Copyright (c) 2003-2008
 *
 * Gordon Meiser, Markus Sinner
 *
 * This File must not be used without permission
 **************************************************/
include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
include_once("includes/diplomacy.common.php");
include_once("includes/cities.class.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");
include_once("includes/session.inc.php");
include_once("includes/barracks.func.php");

$_SESSION['player']->setActivePage(basename(__FILE__));

// Span einer Einheit
define("UNIT_SPAN_TEMPLATE", "<span class=\"unit\" title=\"%s\"><img style=\"float: left; margin:0px;\" alt=\"%s\" title=\"%s\" src=\"%s/%s\">%d</span>\n");

if(!isset($from)) {
  $from=$_SESSION['cities']->getActiveCity();
}
else {
  if (!isset($producem)) $foreign = true;
  $from=intval($from);
}

$error = null;
if(isset($producem))
{
  $coordx = intval($coordx);
  $coordy = intval($coordy);
  switch($gtyp) {
    case 0:
      // Normal Attack
      $error = $_SESSION['cities']->attack($coordx, $coordy, $unit, $from, $_SESSION['player'], $tactic, 'attack');
      break;
    case 1:
      // Siege aktiviert. Siehe includes/db.config.php
      if (ENABLE_SIEGE) {
        $error = $_SESSION['cities']->attack($coordx, $coordy, $unit, $from, $_SESSION['player'], $tactic, 'siege');
      }
      break;
    case 2:
      //despoil
      $error = $_SESSION['cities']->attack($coordx, $coordy, $unit, $from, $_SESSION['player'], $tactic, 'despoil');
      break;
    case 3:
      //burndown
      $error = $_SESSION['cities']->attack($coordx, $coordy, $unit, $from, $_SESSION['player'], $tactic, 'burndown');
      break;
    case 4:
      //move
      $error = $_SESSION['cities']->move($coordx, $coordy, $unit, $from, $_SESSION['player'], $tactic);
      break;
    default:
      do_log("An error Occured while choosing the attack type!");
      die("An error Occured while choosing the attack type!");
      break;
  }
  $cityid_res = do_mysql_query("SELECT id FROM map WHERE x = $coordx AND y = $coordy");
  if ($cityid_res && mysqli_num_rows($cityid_res)>0) {
    $cityid   = do_mysql_fetch_assoc($cityid_res);
    $markcity = $cityid['id'];
  }
}

if (isset($_REQUEST['disarm'])) {
  $error = $_SESSION['cities']->disarmCityUnits($from, $unit);
}
if(isset($sendback)) {
  $error = $_SESSION['cities']->sendBack(intval($unit), intval($owner));
}

// Truppen zurückpfeiffen
if (isset($comeback) && is_numeric($comeback)) {
  $comeback = intval($comeback);

  // Die Armee holen
  // Wenn die Armee noch nicht angekommen ist, dann nur die Differenz bilden aus gelaufener und eigentlich zu laufende zeit,
  // ansonsten die volle Laufzeit
  $res_armies=do_mysql_query("SELECT LEAST(UNIX_TIMESTAMP(), endtime) - starttime AS dif, army.owner, army.end, start, starttime, endtime, city.id AS city, city.name AS cityname, map.x, map.y ".
                       " FROM army LEFT JOIN city ON army.start=city.id LEFT JOIN map ON army.start=map.id WHERE army.aid=".$comeback );



  // Na is doch klar, nur was machen wenn der Datensatz überhaupt exisitert
  if (mysqli_num_rows($res_armies) == 1) {
    $data1=do_mysql_fetch_assoc($res_armies);

    if ( $data1['owner'] != $_SESSION['player']->getID()) {
      $error = "Sire, Ihr könnt doch keine Armee befehligen, über die Ihr kein Kommando habt!";
    }
    else if ( $data1['cityname'] && $data1['end'] == $data1['start']) {
      // Falls die Armee schon zurückkehrt
      $error = "Sire, diese Armee ist bereits auf der Rückreise nach &quot;".$data1['cityname']."&quot;!";
    }
    else {
      if ( $data1['mission'] != 'return' ) {
        // Einfach Ziel auf Ursprung setzen
        do_mysql_query("UPDATE army SET end = start, mission = 'return', starttime=UNIX_TIMESTAMP(), endtime=UNIX_TIMESTAMP() + ".$data1['dif']." WHERE aid = ".$comeback);
      }
      // Wenn die Ursprungsstadt nicht mehr existiert, dann abdrehen und
      // zur nächstgelegenen Stadt laufen
      else {
        $dif = $data1['dif'];

        // Feststellen wo die Armee denn hinwollte
        $res_back = do_mysql_query("SELECT map.x as x, map.y as y, end, start FROM army LEFT JOIN map ON map.id=army.end WHERE army.aid=".$comeback);
        if(mysqli_num_rows($res_back) == 1) {
          $data2    = do_mysql_fetch_assoc($res_back);

          // nächstgelegene Stadt bestimmen
          $cit = do_mysql_query ("SELECT city.id, city.name, round(sqrt( (".$data2['x']."-x)*(".$data2['x']."-x)+(".$data2['y']."-y)*(".$data2['y']."-y))) AS dist FROM city LEFT JOIN map USING(id) WHERE owner = ".$_SESSION['player']->GetID()." ORDER BY dist LIMIT 1");
          if (mysqli_num_rows($cit) && $city = do_mysql_fetch_assoc($cit) ) {
            do_mysql_query("UPDATE army SET end = ".$city['id'].", mission = 'return', starttime=UNIX_TIMESTAMP(), endtime=UNIX_TIMESTAMP() + ".$dif." WHERE aid = ".$comeback);

            log_fatal_error("Armee kehrt nach ID ".$city['id']." zurück, da die Heimatstadt verloren ist");
          }
          else {
            show_log_fatal_error("Sie besitzen keine Städte mehr!", "Funktion Comeback: Spieler besitzt keine Städte mehr.");
            $error = "Sie besitzen keine Städte mehr!";
          }
        } // if mysqli_num_rows($res_back) 
      } // else
    }
  } // if num_rows
  else {
    $error = "Diese Armee existiert nicht.";
  }
} // if (isset($comeback))


// Ausfall wagen
if (isset($attacksiege)) {
  include_once("includes/fight.func.php");

  // calculate
  $error = attackSiege($attacksiege);

  // Show the enemies
  $show = 2;
}


$orderby = $incorderby = "endtime ASC";
if(isset($_REQUEST['order']))
{
  // orderby setzen
  switch($_REQUEST['order'])
  {
    case "owner":
    case "end":
    case "cityname" :
    case "start":
    case "mission":
      $orderby = $_REQUEST['order'].", endtime ASC";
  }

  // incorder setzen
  switch($_REQUEST['order'])
  {
    case "owner":
    case "end":
    case "cityname" :
      $incorderby = $_REQUEST['order'].", endtime ASC";
  }
}

start_page();

// JavaScript zum Berechnen der Laufzeit
include("includes/walktime_js.inc.php");
?>
<script language="JavaScript">
<!--
function fillInKoords(elem) {
  if(cities[elem] && cities[elem][0] && cities[elem][1]) {
    document.attackform.coordx.value = cities[elem][0];
    document.attackform.coordy.value = cities[elem][1];
    updateTravelTime();
  }
  else {
    document.getElementById("premiumerror").innerHTML = 'Auswahlliste nur für Premium-User!';
  }
}

// -->
</script>

<?
start_body();

if($error != null && strlen($error) > 0) {
  echo "<h3 class='error'>Es ist ein Fehler aufgetreten: ".$error."</h3>";
  if(isset($coordx)) $selectx = $coordx;
  if(isset($coordy)) $selecty = $coordy;
}

// Verbündete Stadt ausgewählt
if(isset($_GET['from'])) {
  $resA=do_mysql_query("SELECT name, x, y from city LEFT JOIN map USING(id) WHERE city.id = ".$_GET['from']);
  $dataA = do_mysql_fetch_assoc($resA);
  $cityname = $dataA['name']." <i style=\"color: green;\">(verbündete Stadt)</i>";
  $city_x = $dataA['x'];
  $city_y = $dataA['y'];
  ?>
<script language="JavaScript">
<!--
	this_x = <? echo $dataA['x']; ?>;
	this_y = <? echo $dataA['y']; ?>;
//-->
</script>
  <?
}
else {
  $cityname = $_SESSION['cities']->activecityname;
  $city_x   = $_SESSION['cities']->city_x;
  $city_y   = $_SESSION['cities']->city_y;
}
?>
<table style="width: 660px; margin-bottom: 1px;" class="tblhead">
  <tr>
    <td style="padding-left: 2px; padding-top: 4px; padding-bottom: 4px; margin-bottom: 1px; font-weight: bold;">
      <?php echo $cityname." (".$city_x.":".$city_y.")"; ?>
    </td>
  </tr>
  <tr>
    <td align="center">
      <div style="width: 660px;">
        <div class="tblbody"
          style="text-align: center; float: left; width: 128px;">
          <a href="#" onclick="showhide('1');">Truppenbewegung</a>
        </div>
        <div class="tblbody"
          style="text-align: center; margin-left: 2px; float: left; width: 128px;">
          <a href="#" onclick="showhide('2');">Feindaufklärung</a>
        </div>
        <div class="tblbody"
          style="text-align: center; margin-left: 2px; float: left; width: 128px;">
          <a href="#" onclick="showhide('3');">Neue Mission</a>
        </div>
        <div class="tblbody"
          style="text-align: center; margin-left: 2px; float: left; width: 128px;">
          <a href="#" onclick="showhide('4')">Fremdstationierung</a>
        </div>
        <div class="tblbody"
          style="text-align: center; margin-left: 2px; float: left; width: 128px;">
          <a href="#" onclick="showhide('5');">Truppen entlassen</a>
        </div>
      </div>
    </td>
  </tr>
</table>

<div id="tbl1" style="width: 660px; display: none; margin-top: 10px;" />
 <!-- Tabelle genau in der Größe des darüberliegenden div formatieren -->
 <table cellspacing="1" cellpadding="0" border="0" style="width: 660px;">
  <tr height="20">
    <td colspan="7" class="tblhead"><strong>Aktuelle Truppenbewegungen</strong></td>
  </tr>
  <tr class="tblhead">
    <td width="70"><a
    <?php if($_REQUEST['order']=="start") echo 'style="text-decoration: underline;"' ?>
      href="?order=start">Ausgangspunkt</a></td>
    <td width="70"><a
    <? if($_REQUEST['order']=="cityname") echo 'style="text-decoration: underline;"' ?>
      href="?order=cityname">Ziel</a></td>
    <td width="50"><a
    <? if($_REQUEST['order']=="mission") echo 'style="text-decoration: underline;"' ?>
      href="?order=mission">Auftrag</a></td>
    <td>Armeezusammensetzung</td>
    <td width="50"><a
    <? if(!isset($_REQUEST['order']) || $_REQUEST['order']=="endtime") echo 'style="text-decoration: underline;"' ?>
      href="?order=endtime">Status</a></td>
    <td>Aktion</td>
  </tr>
  <?
  $res_moving = do_mysql_query("SELECT aid,start,end,army.owner,map.id,x,y,starttime,endtime,mission,missiondata,".
                     " city.name as cityname,city.owner AS cowner ".
		     "FROM army LEFT JOIN map ON map.id=army.end LEFT JOIN city ON map.id=city.id ".
		     "WHERE army.owner=".$_SESSION['player']->getID()." ORDER BY ".$orderby);
  if (mysqli_num_rows($res_moving) == 0) {
    echo "<tr><td colspan='7' class='tblbody'>keine Truppenbewegungen</td></tr>";
  }
  
  while ($data1=do_mysql_fetch_assoc($res_moving)) {
    $remaining=$data1['endtime']-time();
    $unittxt="";

    switch ($data1['mission']) {
      case "settle":   $missiontext='<img src="'.$GLOBALS['imagepath'].'/settle.png"    title="Siedeln"    alt="Siedeln">'; break;
      case "move":     $missiontext='<img src="'.$GLOBALS['imagepath'].'/move.png"      title="Verlegung" alt="Verlegung">'; break;
      case "return":   $missiontext='<img src="'.$GLOBALS['imagepath'].'/return.png"    title="Heimkehr" alt="Heimkehr">'; break;
      case "attack":   $missiontext='<img src="'.$GLOBALS['imagepath'].'/schwerter.gif" title="übernehmen" alt="übernehmen">'; break; 
      case "burndown": $missiontext='<img src="'.$GLOBALS['imagepath'].'/death.gif"     title="Brandschatzen" alt="Brandschatzen">'; break;
      case "despoil":  $missiontext='<img src="'.$GLOBALS['imagepath'].'/despoil.gif"   title="Plündern" alt="Plündern">'; break; 
      case "siege":    $missiontext='<img src="'.$GLOBALS['imagepath'].'/siege.png"     title="Belagern" alt="Belagern">'; break;
      default :        $missiontext="Unbekannt";
    }


        $res2=do_mysql_query("SELECT armyunit.unit AS unit, unit.name AS name, count, religion, type, level".
                       " FROM armyunit LEFT JOIN unit ON unit.id = armyunit.unit ".
                       " WHERE aid = ".$data1['aid']);
        while ($data2=do_mysql_fetch_assoc($res2)) {
          $unittxt .= sprintf(UNIT_SPAN_TEMPLATE,
                              $data2['name'], $data2['name'], $data2['name'],
                              $GLOBALS['imagepath'], getUnitImage($data2), $data2['count'] );
        }

        if($data1['mission'] != "return" ) {
          $confirm = " onClick=\"return confirm('Rückkehr wirklich anordnen?')\" ";
          $backtext = "<a ".$confirm." target='main' href='general.php?comeback=".$data1['aid']."'><b class='error'>Rückkehr anordnen</b></a>\n";
        }
        else {
          $backtext = "&nbsp;";
        }

        //ende
        $resX=do_mysql_query("SELECT city.id,name,x,y,owner AS cowner FROM city,map WHERE city.id = ".$data1['start']." AND map.id = city.id");
        $dataX=do_mysql_fetch_assoc($resX);

        echo "<tr><td nowrap valign='top' class='tblbody' ".($markcity == $dataX['id'] ? " id='markcity'>" : ">" ).
    '<a '.($dataX['cowner']==$_SESSION['player']->getID() ? 'class="noerror"' : "" ).
    ' href="javascript:towninfo(\''.$dataX['id']."')\">".(isset($dataX['name']) ? ($dataX['name']."<br/>") : "").
    "</a>&nbsp;<a href='map.php?gox=".$dataX['x']."&goy=".$dataX['y']."'>(".$dataX['x']." : ".$dataX['y'].")</a><span style=\"visibility:hidden;\"> =&gt;</span></td>\n";  

        echo "<td nowrap valign='top' class='tblbody' ".($markcity == $data1['end'] ? " id='markcity'>" : ">" ).
    '<a '.($data1['cowner']==$_SESSION['player']->getID() ? 'class="noerror"' : "" ).
    '  href="javascript:towninfo(\''.$data1['end']."')\">".(isset($data1['cityname']) ? ($data1['cityname']."<br/>") : "").
    "</a>&nbsp;<a href='map.php?gox=".$data1['x']."&goy=".$data1['y']."'>(".$data1['x']." : ".$data1['y'].")</a></td>\n".

    "<td align='center' valign='top' class='tblbody'>".$missiontext."</td>\n";
        echo "<td class='tblbody'>";
        if($data1['missiondata'] != 0)
          echo $data1['missiondata']." Siedler, ";
        
        echo $unittxt."</td>";

        // Verbleibende Zeit
        echo "<td nowrap align='center' valign='top' class='tblbody'>";
        if ($remaining > 0 ) {
          //    $timestr = date("d.m.Y H:i:s",$data1['endtime']);
          $timestr = date("H:i:s",$data1['endtime'])." Uhr";
          $fulltimestr = date("d.m.Y H:i:s",$data1['endtime'])." Uhr";

          echo "<span class='noerror' title='Ankunft ".$fulltimestr."' id='".$data1['aid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$data1['aid'].");</script></span>\n";
          echo "<br><span style='color: 88AA88;'>".$timestr."</span>\n";
        }
        else {
          echo "<span class='noerror' ";

          $remaining = ceil(-$remaining/60);
          if ($data1['mission'] == 'siege') {
            echo ">belagert seit<br>".$remaining." Minuten";
          }
          else {
            echo 'title="Bereit seit '.$remaining.' Minuten">bereit';
          }

          echo "</span>";
        }
        echo "</td>\n<td align='center' valign='top' nowrap class='tblbody'>".$backtext."</td>";

        echo "</tr>\n";

        echo "<tr style=\"height: 4px;\"><td colspan=\"6\"></td></tr>\n\n";

      }
      ?>
 </table>
</div>

<div id="tbl2" style="width: 660px; display: none; margin-top: 10px;">
 <!-- Tabelle genau in der Größe des darüberliegenden div formatieren -->
 <table cellspacing="1" cellpadding="0" border="0" style="width: 660px;">
  <tr>
    <td colspan="1" class="tblhead"><strong>Feindaufkl&auml;rung</strong></td>
    <td colspan="2" nowrap class="tblhead">Sortierung: <?
    $sort = array("endtime" => "Ankunft", "owner" => "Besitzer", "cityname" => "Zielstadt");
    foreach($sort AS $o => $text )
    {
      printf('&nbsp;&nbsp;<a href="?enemy=1&order=%s" style="%s">%s</a>',
      $o,
      !isset($_REQUEST['order']) && $o == "endtime" || $_REQUEST['order'] == $o ? "text-decoration:underline;" : "",
      $text);
    }
    ?></td>
  </tr>
  <?php
  // Hier wird nun jeder Armee-Datensatz solange verjoint, bis die Stadt auf die
  // die Armee unterwegs ist ein Gebäude mit aufklärung hat. Existiert ein solches
  // dann wird geschaut ob es innerhalb der Aufklärungszeit ankommt.
  // Datensätze von Armeen, die zu Städten unterwegs sind in denen noch keine solches
  // Gebäude errichtet ist (NULL-JOINS) fallen also sofort heraus.
  $res_spy = do_mysql_query("SELECT
 army.aid AS aid, army.owner AS owner, player.name AS ownername, army.start AS start, city.name as cityname,
 army.end AS end, army.endtime AS endtime, army.missiondata AS missiondata, army.mission AS mission 
 FROM army
  LEFT JOIN player ON player.id=army.owner
  LEFT JOIN city ON army.end = city.id
  LEFT JOIN citybuilding ON city.id=citybuilding.city
  LEFT JOIN building ON building.id = citybuilding.building
 WHERE city.owner = ".$_SESSION['player']->getID()."
       AND city.owner <> army.owner
 GROUP BY army.aid, res_scouttime HAVING army.endtime <= UNIX_TIMESTAMP() + COALESCE(max(res_scouttime), 0)
 ORDER BY ".$incorderby );

  if(mysqli_num_rows($res_spy)>0) {
    while($data_spy=do_mysql_fetch_assoc($res_spy)) {
      $res_spy_unit=do_mysql_query("SELECT armyunit.unit, armyunit.unit AS id, count,name,religion,type,level ".
                                   " FROM armyunit LEFT JOIN unit ON armyunit.unit = unit.id ".
                                   " WHERE aid = ".$data_spy['aid']." ORDER BY level");
      $remaining = $data_spy['endtime'] - time();

      $timestr = date("H:i:s", $data_spy['endtime'])." Uhr";
      $fulltimestr = date("d.m.Y H:i:s", $data_spy['endtime'])." Uhr";

      $timerline = "<span class='noerror' title='Ankunft ".$fulltimestr ."' id='".$data_spy['aid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$data_spy['aid'].");</script></span>";

      //FIXME: Code ist redundant / wird so auch in main.php eingesetzt
      //       verschmelzen in eien Funktion!
      echo '<tr class="tblbody">';
      if ($remaining > 0) {
        echo '<td colspan="2" nowrap>Die Armee des Spielers <a href="javascript:playerinfo(\''.urlencode($data_spy['ownername']).'\')"><b>'.$data_spy['ownername'].'</b></a>'.
          ' erreicht Eure Stadt <a href="javascript:towninfo(\''.$data_spy['end'].'\')"><b>'.$data_spy['cityname'].'</b></a> in ';

        echo '</td><td align="right">';
        echo $timerline;
        echo " <span style='color: 88AA88;'>(".$timestr.")</span>\n";
      }
      else {

        echo '<td colspan="3">Die Armee des Spielers <a href="javascript:playerinfo(\''.urlencode($data_spy['ownername']).'\')"><b>'.$data_spy['ownername'].'</b></a>'.
          ' steht vor Eurer Stadt <a href="javascript:towninfo(\''.$data_spy['end'].'\')"><b>'.$data_spy['cityname']."</b></a>. ";
        // Bei Belagerung einen entsprechenden Text vorbereiten
        if ($data_spy['mission'] == 'siege') {
          $siegetime = ceil(-$remaining/60);
          echo "<br><font class='noerror'>Sire! Diese Armee <b>belagert unsere Stadt</b> seit ".$siegetime." Minuten. Wenn wir nichts unternehmen wird das schwerwiegende Konsequenzen haben!</font>";
          echo ' <a onClick="return confirm(\'Wollt Ihr die belagernden Heere angreifen und auf Euren Verteidigungsbonus verzichten?\')" href="general.php?attacksiege='.$data_spy['end'].'">Ausfall wagen!</a>';
        }
        else {
          echo "<font class='noerror'>Die Schlacht steht unmittelbar bevor.</font>\n";
        }
      }
      echo '</td>';
      echo "</tr>\n";
      echo '<tr class="tblbody">';
      echo '<td valign="top">Eure Aufklärer konnten folgende Einheiten erspähen:</td>';
      echo '<td valign="top" colspan="2" nowrap>';
      $unitcount = 0;
      while($data_spy_unit = do_mysql_fetch_assoc($res_spy_unit)) {
        $unitcount++;
        //        echo "<b>".$_SESSION['cities']->getUnitName($data_spy_unit['unit'])."</b>: ".$data_spy_unit['count']." ()<br>";
        printf(UNIT_SPAN_TEMPLATE,
        $data_spy_unit['name'], $data_spy_unit['name'], $data_spy_unit['name'],
        $GLOBALS['imagepath'], getUnitImage($data_spy_unit), $data_spy_unit['count'] );
        // , $data_spy_unit['name']
      }
      // Siedler nur anzeigen, wenn KEIN Geleitschutz dabei.
      if ($unitcount == 0 && $data_spy['missiondata'] > 0) {
        echo "<b>Männer, Frauen und Kinder: </b>".$data_spy['missiondata']."<br>";
      }
      echo '</td>';
      echo "</tr>\n";
      echo "<tr style=\"height: 4px;\"><td colspan=\"3\"></td></tr>\n\n";
    }
  }
  else if($_SESSION['player']->getScoutTime() <= 0) {
    echo "<tr class='tblbody'><td colspan=\"3\"><font class='error'>kein Aufklärungsgebäude errichtet!</font></td></tr>";
  }
  else {
    echo "<tr class='tblbody'><td colspan=\"3\">Keine Feindesaufklärungen</td></tr>";
  }

  ?>
 </table>
</div>

<div id="tbl3" style="width: 660px; display: none; margin-top: 10px;">
 <form action="<? echo $_SERVER['PHP_SELF'];  ?>" method="POST" name="attackform">
 <!-- Tabelle genau in der Größe des darüberliegenden div formatieren -->
 <table cellspacing="1" cellpadding="0" border="0" style="width: 660px;">
  <tr>
    <td colspan="3" class="tblhead"><strong>Neue Mission starten</strong></td>
  </tr>
  <tr class="tblhead">
    <td>Koordinaten</td>
    <td>Missionstyp</td>
    <td>Taktik</td>
  </tr>
  <tr class="tblbody">
    <td valign="top" align="center"><select
      onChange="fillInKoords(this.value)">
      <option value="0">Stadt auswählen...</option>
      <?
      $js = "";
      $me = $_SESSION['player']->getId();
      $sql = sprintf("
SELECT city.name,owner,x,y,player.name AS pname FROM city LEFT JOIN map USING(id) LEFT JOIN player ON player.id=city.owner
WHERE owner = %d OR 
  owner IN (SELECT id1 FROM relation WHERE id2=%d AND type=2) OR
  owner IN (SELECT id2 FROM relation WHERE id1=%d AND type=2)
ORDER BY owner != %d, owner, y
", 
      $me, $me, $me, $me);

      $cres = do_mysql_query($sql);
      $pos = 0;
      while($c = mysqli_fetch_object($cres)) {
        printf("   <option value=\"%d\">%s %s</option>\n", $pos, $c->name, $c->owner != $me ? "(".$c->pname.")" : "(eigene)");
        if(is_premium_adressbook ()) {
          $js .= sprintf(" cities[%d] = new Array('%d','%d');\n", $pos, $c->x, $c->y);
        }
        $pos++;
      }
      ?>
    </select> <script language="JavaScript">
<!--
<? echo $js; ?>
// -->
</script> 
<br>
    <input type="text" id="coordx" name="coordx"
      onkeyup="updateTravelTime()" onChange="updateTravelTime()"
      value="<? if (isset($selectx)) echo $selectx;?>" 
      size="4"
      maxlength="4"> : 
    <input type="text" id="coordy" name="coordy"
      onkeyup="updateTravelTime()" onChange="updateTravelTime()"
      value="<? if (isset($selecty)) echo $selecty;?>" 
      size="4"
      maxlength="4">
    <br>
    <span id="premiumerror" style="color: red; font-weight: bold">&nbsp;</span>
    <p>Laufzeit: <input type="text" style="text-align: right"
      name="traveltime" id="traveltime" value="n/a" size="9" readonly> h<br>
    Geschwindigkeit: <span id="armyspeed">???</span>
    
    </td>
    <td valign="top">
    <table border="0" width="100%;" cellpadding="0" cellspacing="0">
      <tr>
        <td><input type="radio" name="gtyp" value="0" checked>
        &Uuml;bernehmen&nbsp;</td>
        <td><input type="radio" name="gtyp" value="4"> Verlegen<br />
        </td>
      </tr>
      <tr>
        <td><input type="radio" name="gtyp" value="2"> Plündern&nbsp;</td>
        <td><input type="radio" name="gtyp" value="3"> Brandschatzen</td>
      </tr>
      <?
      if (ENABLE_SIEGE) {
      ?>
      <tr>
        <td><input type="radio" name="gtyp" value="1"> Belagern<br />
        </td>
        <td></td>
      </tr>
      <?php 
         if(defined("SIEGE_ARMIES_NO_COST") && SIEGE_ARMIES_NO_COST) { 
      ?>
             <tr>
               <td colspan="2">Belagernde (angekommene) Armeen<br>
               sind unterhaltsfrei!</td>
             </tr>
       <?php 
         } // SIEGE_ARMIES_NO_COST
      } // if (ENABLE_SIEGE)
      ?>
    </table>
    </td>
    <td valign="top">
    <input type="radio" name="tactic" value="2">Erstürmen<br />
    <input type="radio" name="tactic" value="0" checked> Offensiv<br />
    <input type="radio" name="tactic" value="1"> Defensiv</td>
  </tr>
  <?php
  echo '<input type="hidden" name="from" value="'.$from.'">';
  $res_cityunits = do_mysql_query("SELECT unit.id AS id,unit.name AS name,city.name AS cname, unit.cost as cost,count,speed,unit.type,unit.level,unit.religion ".
  						          " FROM cityunit,unit,city ".
  						          " WHERE unit.id=cityunit.unit AND city.id=cityunit.city AND cityunit.city=".$from." AND cityunit.owner=".$_SESSION['player']->getID().
  						          " ORDER BY cityunit.unit");

  if (mysqli_num_rows($res_cityunits)==0) {
    echo "<tr><td colspan='3' class='tblbody' style=\"padding-top:10px; padding-bottom:10px; font-weight:bold; color:red; text-align:center;\">Es sind keine Einheiten in " . $_SESSION['cities']->activecityname . " verfügbar!</td></tr>";
  }
  else {
    $elem_num = 0;
    $unitspeeds = "";
    
    // Den ersten Datensatz holen, damit der Stadtname bekannt ist
    $data1 = do_mysql_fetch_assoc($res_cityunits);
    echo '<tr><td colspan="3" class="tblhead"><b>Eigene Truppen in '.$data1['cname'].'</b></td></tr>';
    do {
      echo "<tr class='tblbody'>\n";
      $img = getUnitImage($data1);
      $href= getUnitLibLink($data1);

      printf("<td><a href=\"%s\"><img border=\"0\" src=\"%s/%s\" alt=\"%s\" title=\"%s\"> %s</a>", $href, $GLOBALS['imagepath'], $img, $data1['name'], $data1['name'], $data1['name']);
      echo "<td>Unterhaltskosten: ".number_format($data1['cost'],2,",",".")." (pro Einheit)</td>\n";
      echo "<td nowrap><input type='text' name='unit[".$data1['id']."]' id='unit".$elem_num ."' tabindex='".$elem_num."' onkeyup='updateTravelTime();' onChange='updateTravelTime();'  size='8'> ";
      echo '<a onClick="javascript:document.getElementById(\'unit'.$elem_num.'\').value = \''.$data1['count'].'\'; updateTravelTime(); return true; ">';
      echo '(max. '.$data1['count'].")</a></td>\n";
      echo "</tr>\n";

      $unitspeeds .= sprintf("unitspeeds[%d] = %d;\n", $elem_num, $data1['speed']);
      $elem_num++;
    } while ($data1 = do_mysql_fetch_assoc($res_cityunits));
  }
  ?>
  <script language="JavaScript">
<!--
<? echo $unitspeeds; ?>
//-->
</script>
<?
// Aktuelle Stadt gewählt, verbündete stationierte Truppen anzeigen
if ($from == $_SESSION['cities']->getActiveCity()) {
  $res2 = do_mysql_query("SELECT unit.id AS id,unit.name AS uname,count,owner,player.name AS pname FROM cityunit LEFT JOIN unit ON unit.id=cityunit.unit LEFT JOIN player ON player.id=cityunit.owner WHERE cityunit.city=".$from." AND cityunit.owner<>".$_SESSION['player']->getID()." ORDER BY owner, cityunit.unit");
  if (mysqli_num_rows($res2)>0) {
    echo '<tr class="tblhead"><td colspan="3"><b>Verbündete Truppen</b></td></tr>';
    while ($data2 = do_mysql_fetch_assoc($res2)) {
      echo "\n<tr class='tblbody'>\n";
      echo "<td>".$data2['uname']."</td>\n";
      echo "<td>".$data2['pname']."</td>\n";
      // Anzahl und zurückschicken-Link
      echo "<td>".$data2['count']."&nbsp;";
      printf('<a href="?sendback=1&unit=%d&owner=%d" onClick="return confirm(\'Seid Ihr sicher, dass Ihr diese Einheiten zurückschicken wollt?\')"><img alt="Grafik fehlt" title="Einheit zum Besitzer zurückschicken" border="0" src="%s/%s"></a>', 
             $data2['id'], $data2['owner'], $imagepath, "delete.png");
      echo "</td>\n";
      echo "</tr>";
    }
  }
}
?>
  <tr>
    <td colspan="3" class="tblhead" align="center">
    <h3>Missionstypen</h3>
    Beschreibung zu den einzelnen Missions-Typen und Taktiken sind in
    der <a href="library.php?open=0">Bibliothek</a> unter <b>Krieg und
    Kampf zu finden.</b>
    <h3>Truppen-Laufzeit</h3>
    Die Laufzeit zur Zielstadt ist priär abhängig von der Entfernung
    und Geschwindigkeit der langsamsten Einheit in der Armee.<br>
    Je nach Größe der Armee dauert es zwischen <?php echo round(ARMY_TIME_TO_PREPARE/3600, 2); ?> Stunden
    und <?php echo round(ARMY_MAX_TIME/86400, 2); ?> Tagen (Realzeit), 
    bis die Armee überhaupt losmarschiert (die sogenannte Vorbereitungszeit). 
    Je 1.000 Mann erhöht sich die Laufzeit um <?php echo round(ARMY_TIME_PER_1000/60, 2); ?> Minuten, 
    eine Armee benötigt aber mindestens <?php echo (ARMY_TIME_TO_PREPARE/60); ?> Minuten Vorbereitungszeit.
    
    <?php 
    	// Ausgabe für Testrunden.
    	if (ARMY_SPEED_FACTOR > 10) 
    		echo "<h1 class='error'>Die Laufzeiten sind auf Faktor ".ARMY_SPEED_FACTOR.".</h1>"; 
   	?>
    </td>
  </tr>
  <tr class="tblbody">
    <td colspan="3" style="text-align: center;"><input
      style="margin-bottom: 5px; margin-top: 5px;" type="submit"
      name="producem" value="Mission starten" onclick="checkfields()"></td>
  </tr>
 </table>
 </form>
</div>


<?php
// Fremdstationierungen anzeigen
$res_garrison = do_mysql_query("SELECT x,y,cityunit.owner AS uowner, unit.id AS unitid, unit.name AS uname, unit.cost, cityunit.count,city.name AS cname, p1.name AS pname, city.id AS cid, city.owner AS cowner ".
                      " FROM unit,cityunit,city,map,player AS p1,player AS p2 ".                     
                      " WHERE map.id=city.id AND unit.id=cityunit.unit AND cityunit.city=city.id AND p1.id=city.owner AND p1.id<>p2.id AND p2.id=cityunit.owner AND p2.id=".$_SESSION['player']->getID().
                      " ORDER BY city.id,unitid");

$cid = "";


?>
<div id="tbl4" style="width: 660px; display: none; margin-top: 10px;">
 <input type="hidden" name="from" value="<?php echo $from; ?>">
 <table cellspacing="1" cellpadding="0" border="0"style="width: 660px;">
  <tr class="tblhead"><td colspan="3"><strong>In folgenden fremden St&auml;dten wurden Truppen stationiert:</strong></td></tr>
  <tr class="tblbody"><td colspan="3">&nbsp;</td></tr>
  <?php 
  if (mysqli_num_rows($res_garrison) > 0) {
  	while($data=do_mysql_fetch_assoc($res_garrison)) {
  	  if($cid != $data['cid']) {
    	echo '<tr><td colspan="3" class="tblhead">'.
        '<a href="javascript:towninfo(\''.$data['cid'].'\')">'.$data['cname']."</a> ".
        '(<a href="map.php?gox='.$data['x'].'&goy='.$data['y'].'">'.$data['x'].':'.$data['y'].'</a>)'.
        ' von <a href="javascript:playerinfo(\''.urlencode($data['pname']).'\')">'.$data['pname'].'</a> ';

    	$href= $_SERVER['PHP_SELF'].'?from='.$data['cid'];
    	if(isset($selectx)) $href .= "&selectx=".$selectx;
    	if(isset($selecty)) $href .= "&selecty=".$selecty;


    	echo '<a href="'.$href."\">... in die Stadt wechseln</a></td></tr>\n";

    	$cid = $data['cid'];
    }

    echo "<tr class='tblbody'>\n";
    echo "<td>".$data['uname']."</td>\n";
    echo "<td>Unterhaltskosten: ".$data['cost']." (pro Einheit)</td>\n";
    echo "<td>".$data['count']."</td>\n";
    echo "</tr>\n";
  	}
  }
  else {
  	echo "<tr class=\"tblbody\"><td colspan=\"3\" style=\"text-align:center;\"><br /><strong style=\"color:red\">Sie haben keine Truppen in fremden Städten stationiert!</strong><br /><br /></td></tr>";
  }
  ?>
 </table>
</div>


<div id="tbl5" style="width: 660px; display: none; margin-top: 10px;">
<?php barracks_disarm_table($from); ?>
</div>

<?php 
// Welcher Teil soll gezeigt werden
$show = 1;
if (isset($selectx)) {
  $show = 3;
}
if (isset($enemy)) {
  $show = 2;
}
if (isset($foreign)) {
  $show = 3;
}
if (isset($_REQUEST['disarm'])) {
  $show = 5;
}

?> 
<script language="JavaScript">
<!--
showhide('<? echo $show; ?>');
-->
</script>
<div style="clear: left" />
<p>

<h1>NEU: Der Kampfsimulator.</h1>
<a href="fightsim.php" target="_blank">Hier klicken</a>
 
<? end_page(); ?>