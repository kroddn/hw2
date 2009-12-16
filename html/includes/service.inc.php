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
 * Laurenz Gamper
 * Gordon Meiser
 * Markus Sinner
 * This File must not be used without permission    !
 ***************************************************/
require_once ("includes/db.inc.php");
require_once ("includes/config.inc.php");
require_once ("includes/fight.func.php");
require_once ("includes/util.inc.php");
require_once ("includes/ressources.inc.php");
require_once ("includes/diplomacy.common.php");
require_once ("includes/tournament.inc.php");
require_once ("includes/premium.inc.php");

$debug = getenv ("DEBUG");
if ($debug || DEBUG ) {
  echo "Debugmodus!\n";
  define ("DEBUG_SERVICE", 1);
}


/**
 * Siedler kommen an
 * 
 * @param $end		ID der Ziel-Stelle in der "map"
 * @param $start    ID der Ausgangs-Stelle/Stadt in er "map"
 * @param $endtime  Ankunftszeitpunkg
 * @param $aid		army->aid
 * @param $missiondata		Entspricht Anzahl der Siedler
 * @param $religion			Religion
 * @param $owner			ID des Besitzers des Siedlungstrupps
 * @return unknown_type
 */
function arrivalSettle($end, $start, $endtime, $aid, $missiondata, $religion, $owner) {
  getMapSize($fx, $fy);

  // Feld checken ( ob schon belegt die Umgegend )
  $xy   = do_mysql_query_fetch_assoc("SELECT x,y FROM map WHERE id = ".$end); 
  $x    = $xy['x']; 
  $y    = $xy['y']; 
  
  // $fx und $fy ist Kartengröße
  $sql = "SELECT city.owner AS owner, city.id AS id FROM city, map AS map1 ".
    " WHERE city.id=map1.id ".
    " AND map1.x >= ".max(0, $x - 4)." AND map1.x <= ".min($fx-1, $x + 4).
    " AND map1.y >= ".max(0, $y - 4)." AND map1.y <= ".min($fy-1, $y + 4);

  $res2 = do_mysql_query($sql);

  // Eine Stadt an dieser Stelle existiert bereits - Umkehren
  if ($data2 = mysql_fetch_assoc($res2)) {
    do_mysql_query("UPDATE army SET mission='return',end=".$start.",start=".$end.",endtime=endtime+endtime-starttime,starttime=".$endtime." WHERE aid=".$aid);
    do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$owner.", UNIX_TIMESTAMP(),'Stadtgründung fehlgeschlagen','Eine Stadt konnte nicht gegründet werden, weil das Gebiet bereits von einer anderen Stadt belegt wurde.',3)");
  } 
  else {
    // eventuelle Startposition entfernen
    $res3 = do_mysql_query("SELECT startpositions.x AS x, startpositions.y AS y FROM startpositions,map WHERE startpositions.x>=map.x-4 AND startpositions.x<=map.x+4 AND startpositions.y>=map.y-4 AND startpositions.y<=map.y+4 AND map.id=".$end);
    while ($data3 = mysql_fetch_assoc($res3)) {
      do_mysql_query("DELETE FROM startpositions WHERE x=".$data3['x']." AND y=".$data3['y']);
    }
    
    // Stadteintrag anfertigen und armyblock setzen
    $newname = "neue Stadt ".$end."-".$aid;
    do_mysql_query("INSERT INTO city (id,name,population,religion,food,owner,attackblock) VALUES (".$end.",'".mysql_escape_string($newname)."',".$missiondata.",".$religion.",0,".$owner.", UNIX_TIMESTAMP() + ".(BLOCKSETTLEARMY).")");
    do_mysql_query("DELETE FROM army WHERE aid=".$aid);
    

    // Armeedaten löschen und Bewachung hinzufügen
    $res4 = do_mysql_query("SELECT unit, count FROM armyunit WHERE armyunit.aid=".$aid);
    while ($data4 = mysql_fetch_assoc($res4)) {
      do_mysql_query("INSERT INTO cityunit (city, unit, count, owner) VAluES (".$end.", ".$data4['unit'].", ".$data4['count'].", ".$owner.")");
    }
    
    // Jetzt die Armeen löschen
    do_mysql_query("DELETE FROM armyunit WHERE aid=".$aid);

    // Bauernhof bauen
    //TODO: define festlegen für Bauernhof
    do_mysql_query("INSERT INTO citybuilding (city,building,count) VALUES (".$end.",1,1)");
    
    // Nachricht schreiben
    do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$owner.",".$endtime.",'Gründung: ".$newname."','Ein neue Siedlung wurde auf Euer Geheiß hin gegründet. ".$missiondata." Seelen sind in dieser neuen Siedlung beheimatet.',3)");
    // cc_towns aktualisieren
    do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$owner);
  }
}

/**
 * Eine Armee mit Mission "Verlegung" kommt an
 * @param $end
 * @param $owner
 * @param $endtime
 * @param $missiondata
 * @param $aid
 * @param $missionstr
 * @return unknown_type
 */
function arrivalMove($end, $owner, $endtime, $missiondata, $aid, $missionstr) {
  $res1 = do_mysql_query("SELECT owner,name FROM city WHERE id=".$end);
  $data1 = mysql_fetch_assoc($res1);

  //falls ein siedlertrupp zurückkommt, dann ew wieder erhöhen
  if ($missiondata != NULL) {
    // Missionskosten werden NICHT erstattet, die Bevölkerung aber schon
    do_mysql_query("UPDATE city SET population=population+".$missiondata." WHERE id=".$end);
  }
  $res2 = do_mysql_query("SELECT unit, count FROM cityunit WHERE city=".$end." AND owner=".$owner);
  while ($data2 = mysql_fetch_assoc($res2)) {
    $cg[$data2['unit']] = $data2['count'];
  }
  $res3 = do_mysql_query("SELECT unit,count,name FROM armyunit,unit WHERE armyunit.unit=unit.id AND armyunit.aid=".$aid);
  while ($data3 = mysql_fetch_assoc($res3)) {    
    if ($cg[$data3['unit']] != null)
      do_mysql_query("UPDATE cityunit SET count=count+".$data3['count']." WHERE unit=".$data3['unit']." AND owner=".$owner." AND city=".$end);
    else
      do_mysql_query("INSERT INtO cityunit VALUES (".$end.",".$data3['unit'].",".$data3['count'].",".$owner.")");

    $unitstr .= $data3['count']." ".$data3['name'].", ";
  }
  do_mysql_query("DELETE FROM army WHERE aid=".$aid);
  do_mysql_query("DELETE FROM armyunit WHERE aid=".$aid);
  do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$owner.",".$endtime.",'".$missionstr.": ".$data1['name']."','".$unitstr." sind wohlbehalten in ".$data1['name']." eingetroffen.',4)");
  // cc_messages aktualisieren
  do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$owner);
}


/**
 * Testet ob Siedler gelöscht werden müssen oder zu Umkehr bewegt werden müssen.
 * TODO @Morlock: Dokumentieren
 */
function remove_settler($city_id) {
  //echo " -> OK\n";
  $get_settler_data = do_mysql_query("SELECT aid as id, start, end, starttime, endtime, missiondata as settler FROM army WHERE start=".$city_id." AND mission='settle'");
  if(mysql_num_rows($get_settler_data)>0) {
    while($settler_data = mysql_fetch_assoc($get_settler_data)) {
      // wenn weniger als 25 Siedler -> umkehren lassen
      if($settler_data['settler']<25) {
        if($settler_data['start']!=$settler_data['end']){
          do_mysql_query("UPDATE army SET end = ".$settler_data['start'].", mission = 'return' WHERE aid=".$settler_data['id']);
          if (DEBUG_SERVICE) echo "remove_settler: Weniger als 25 Siedler unterwegs -> Rueckruf\n";
        }
      }
      // wenn 0 Siedler -> kompletten Trupp löschen
      if($settler_data['settler']==0) {
        $test_helptroops = do_mysql_query("SELECT aid FROM armyunit WHERE aid=".$settler_data['id']);
        if(mysql_num_rows($test_helptroops)==0) {
          do_mysql_query("DELETE FROM army WHERE aid=".$settler_data['id']);
          if (DEBUG_SERVICE) echo "remove_settler: 0 Siedler gefunden -> Loesche Siedler\n";
        }
        else {
          if (DEBUG_SERVICE) echo "remove_settler: 0 Siedler gefunden + Geleitschutz -> Tue nichts\n";
        }
      }
    }
  }
}

/**
 * Testet, ob Siedlertrupps unterwegs sind, und behandelt sie entsprechend -> siehe Kommentare
 * Auslöser ist Eroberung oder Brandschatzung.
 * TODO @Morlock: Dokumentieren
 */
function check_settler($city_id) {
  echo " -> OK\n";
  $get_settler_data = do_mysql_query("SELECT aid as id, start, end, starttime, endtime, missiondata as settler FROM army WHERE start=".$city_id." AND mission='settle'");
  if(mysql_num_rows($get_settler_data)>0) {
		while($settler_data = mysql_fetch_assoc($get_settler_data)) {
			$get_settler_data2 = do_mysql_query("SELECT aid FROM armyunit WHERE aid=".$settler_data['id']);
			// wenn Siedler ohne Trupp unterwegs sind -> Siedler löschen
			if(!mysql_num_rows($get_settler_data2)) {
				do_mysql_query("DELETE FROM army WHERE aid=".$settler_data['id']);
				if (DEBUG_SERVICE) echo "check_settler: Siedler ohne Begleitschutz -> Siedler loeschen\n";
			}
			// wenn Siedler mit Trupp unterwegs ist -> Siedler entfernen Trupp rückkehren lassen
			// wenn Siedler noch nicht auf Rückkehr sind
			else if($settler_data['start']!=$settler_data['end']){
				do_mysql_query("UPDATE army SET end = ".$settler_data['start'].", mission = 'return', missiondata = 0 WHERE aid=".$settler_data['id']);
				if (DEBUG_SERVICE) echo "check_settler: Siedler mit Begleitschutz -> Siedler loeschen + Umkehr\n";
			}
			// der Trupp ist bereits auf der Rückreise -> nur Siedler entfernen
			else {
			  do_mysql_query("UPDATE army SET mission = 'return', missiondata = 0 WHERE aid=".$settler_data['id']);
			  if (DEBUG_SERVICE) echo "check_settler: Siedler mit Begleitschutz -> Siedler nur loeschen, Trupp schon auf Umkehr\n";
			}
		} 
	}
}


/**
 * Führe den Stadtangriff durch
 * 
 * @param $defenders  Array der Stadtverteidiger. Die ID des Stadtbesitzers ist da immer
 *                    mit drin
 * @param $unitowner 
 * @param $end
 * @param $aid
 * @param $tactic
 * @return unknown_type
 */
function attCity($defenders, $unitowner, $end, $aid, $tactic) {
  if (DEBUG_SERVICE)
    echo "\n Called attCity(".$defenders.",".$unitowner.",".$end.",".$aid.",".$tactic.") ";

  $res_city = do_mysql_query("SELECT owner FROM city WHERE id=".$end);
  $data_city = mysql_fetch_assoc($res_city);
  
  $res2 = do_mysql_query("SELECT unit, count FROM armyunit WHERE aid=".$aid);
  $i = 0;
  if (DEBUG_SERVICE) {
    echo "\nTruppen vor dem Kampf\n";
    echo "Angreifer ".$unitowner." greift an mit folgenden Truppen:\n";
  }

  while ($data2 = mysql_fetch_assoc($res2)) {
    $at[$i]['id'] = $data2['unit'];
    $at[$i]['count'] = $data2['count'];
    $at[$i]['player'] = $unitowner;
    if (DEBUG_SERVICE) {
      echo " * Einheit: ".$data2['unit'].", Anzahl: ".$data2['count']."\n";
    }
    $i ++;
  }
  $i = 0;
  if($data_city['owner']) {
    foreach ($defenders as $defowner) {
      $cityunits = do_mysql_query("SELECT unit,count FROM cityunit WHERE city=".$end." AND owner=".$defowner);
      if (DEBUG_SERVICE) echo "Verteidiger ".$defowner." verteidigt mit folgenden Truppen:\n";
      while ($units = mysql_fetch_assoc($cityunits)) {
        $df[$i]['id'] = $units['unit'];
        $df[$i]['count'] = $units['count'];
        $df[$i]['player'] = $defowner;
        if (DEBUG_SERVICE) echo "  Einheit: ".$units['unit'].", Anzahl: ".$units['count']." Besitzer: ".$defowner."\n";
        $i ++;
      }
    } // foreach
  }
  else {
    // Stadt ist Herrenlos
    $cityunits = do_mysql_query("SELECT unit,count FROM cityunit WHERE city=".$end);
    if (DEBUG_SERVICE) echo "Verteidiger HERRENLOS verteidigt mit folgenden Truppen:\n";
    while ($units = mysql_fetch_assoc($cityunits)) {
      $df[$i]['id'] = $units['unit'];
      $df[$i]['count'] = $units['count'];
      // $df[$i]['player'] = $defowner; // Owner ist NULL
      if (DEBUG_SERVICE) echo "  Einheit: ".$units['unit'].", Anzahl: ".$units['count']." Besitzer: HERRENLOS\n";
      $i ++;
    }
  }
  
  $res4 = do_mysql_query("SELECT sum(res_defense) FROM citybuilding,building WHERE citybuilding.building=building.id AND city=".$end);
  if ($data4 = mysql_fetch_array($res4))
    $defbonus = $data4[0] ? $data4[0] : 0;
  else
    $defbonus = 0;

  $erg = fight($at, $df, $defbonus, $tactic);
  
  if ($erg == false)
    return NULL;
  return $erg;
  
} // attCity


/**
 * 
 * @param $end
 * @param $endtime
 * @param $attowner
 * @param $defenders
 * @param $defowner      Kann NULL sein => Herrenlose Stadt
 * @param $erg
 * @param $attstr
 * @param $defstr
 * @param $playerkilled
 * @param $cityName
 * @return unknown_type
 */
function attMSG($end, $endtime, $attowner, $defenders, $defowner, $erg, $attstr, $defstr, $playerkilled, $cityName) {
  if($defowner) {
    $playerName =  resolvePlayerName($defowner);
    if (!$playerName) {
      log_fatal_error("attMsg(playerName): DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." defender: ".$defowner);
    }
  }
  else {
    $playerName = "HERRENLOS";
  }
  
  
  $playerName2 = resolvePlayerName($attowner);
  if (!$playerName2) {
    log_fatal_error("attMsg(playerName2): DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." defender: ".$defowner);
  }
  if (!$cityName) {
    log_fatal_error("attMsg(cityName): Endstadt: ".$end.", DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." defender: ".$defowner);
  }

  $message['attacker'] = "Die Stadt <b>".$cityName."</b> des Spielers <b>".$playerName."</b> wurde auf Euer Geheiß hin angegriffen. Eure Truppen konnten den Sieg erringen. ".$attstr."\n\n";
  $message['attacker'] .= "<b>Folgende Einheiten überlebten die Schlacht:</b>\n";
  $message['defender'] = "Die Stadt <b>".$cityName."</b> wurde von <b>".$playerName2."</b> angegriffen. Die verteidigenden Truppen waren dem Feind leider unterlegen. ".$defstr."\n\n";
  $message['defender'] .= "<b>Folgende Einheiten des Angreifers überlebten die Schlacht:</b>\n";
  for ($i = 0; $i < sizeof($erg); $i ++) {
    $res1 = do_mysql_query("SELECT name FROM unit WHERE id = '".$erg[$i]['id']."'");
    $data1 = mysql_fetch_assoc($res1);
    $message['attacker'] .= $data1['name'].": ".$erg[$i]['count']."\n";
    if($erg[$i]['player']) {
      $uowner = do_mysql_query("SELECT name FROM player WHERE id=".$erg[$i]['player']);
      $uownerName = mysql_fetch_assoc($uowner);
    }
    else {
      $uownerName['name'] = "herrenlosen Adligen";
    }
    $message['defender'] .= $data1['name'].": ".$erg[$i]['count']." von ".$uownerName['name']."\n";
  }

  if ($playerkilled == true) {
    $message['attacker'] .= "\n\n<b>Sie haben die letzte Stadt des Spielers ".$playerName." zerstört. Seine Streitkräfte wurden in alle vier Winde verstreut und stehen nichtmehr unter seinem Kommando. Doch ".$playerName." selbst wurde nicht gefasst und ist wohl in ferne Gebiete geflohen...</b>";
    $message['defender'] .= "\n\n<b>Sire, welch Unglück! Unsere letzte Stadt wurde zerstört! Eure Streitkräfte sind in alle vier Winde verstreut und entziehen sich Eurem Kommando, während Ihr selbst aber fliehen konntet. <br>[i]Ihr schwört Rache und baut Euer Imperium erneut auf...[/i]</b>";
  }
  do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$attowner.",".$endtime.",'Sieg: ".$cityName."','".$message['attacker']."',4)");
  do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$attowner);

  // Nachrichten an die Verteidiger
  if(!$defenders || sizeof($defenders) == 0) {
    if($defowner) {
    if (DEBUG_SERVICE) echo " Message an Besitzer\n";

    do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$defowner.",".$endtime.",'Niederlage: ".$cityName."','".$message['defender']."',4)");
    do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$defowner);
    }
    else {
      if (DEBUG_SERVICE) echo "HERRENLOS erhält keinen Bericht\n";
    }
  }
  else {
    foreach ($defenders as $def) {
      if (DEBUG_SERVICE)
        echo " Message an Defender $def";
      
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$def.",".$endtime.",'Niederlage: ".$cityName."','".$message['defender']."',4)");
      do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$def);
    }
  }
}

function defWin($aid, $end, $endtime, $attowner, $defenders, $defowner, $erg) {
  if (DEBUG_SERVICE)
    echo " DEFWIN ";

  do_mysql_query("DELETE FROM army WHERE aid = ".$aid);
  do_mysql_query("DELETE FROM armyunit WHERE aid = ".$aid);
  do_mysql_query("DELETE FROM cityunit WHERE city = ".$end);
  for ($i = 0; $i < sizeof($erg); $i ++) {
    $owner_str = $erg[$i]['player'] ? $erg[$i]['player'] : "NULL";
    $sql = "INSERT INTO cityunit VALUES ('".$end."','".$erg[$i]['id']."','".$erg[$i]['count']."',".$owner_str.")";
    if (DEBUG_SERVICE)
      echo $sql."<br>\n";

    do_mysql_query($sql);
  }

  if($defowner) {
    $playerName = resolvePlayerName($defowner);
    if (!$playerName)
      log_fatal_error("defWin(playerName): DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." defname: ".$defplayerName);
  }
  else {
    $playerName = "HERRENLOS";
  }
    
  $playerName2 = resolvePlayerName($attowner);
  if (!$playerName2) {
    log_fatal_error("defWin(playerName2): DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." defname: ".$defplayerName);
  }
  
  $cityName = resolveCityName($end);
  if (!$cityName) {
    log_fatal_error("defWin(cityName): Endstadt: ".$end.", DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." defname: ".$defplayerName);
  }
  // Nachricht schreiben
  $message['attacker'] = "Die Stadt <b>".$cityName."</b> im Besitz von <b>".$playerName."</b> wurde auf Euer Geheiß hin angegriffen. Eure Truppen unterlagen jedoch den dort verteidigenden Einheiten.\n\n";
  $message['attacker'] .= "<b>Folgende Einheiten des Verteidigers überlebten die Schlacht:</b>\n";
  $message['defender'] = "Die Stadt <b>".$cityName."</b> wurde von <b>".$playerName2."</b> angegriffen. Die verteidigenden Truppen konnten die Stadt erfolgreich halten.\n\n";
  $message['defender'] .= "<b>Folgende Einheiten überlebten die Schlacht:</b>\n";

  for ($i = 0; $i < sizeof($erg); $i ++) {
    $res1 = do_mysql_query("SELECT name FROM unit WHERE id = '".$erg[$i]['id']."'");
    $data1 = mysql_fetch_assoc($res1);
    if($erg[$i]['player']) {
      $uowner = do_mysql_query("SELECT name FROM player WHERE id=".$erg[$i]['player']);
      $uownerName = mysql_fetch_assoc($uowner);
    }
    else {
      $uownerName = "HERRENLOS";
    }    
    
    if ($erg[$i]['id'] == DORFBEWOHNER_ID) {
      $count = $erg[$i]['count'];
      if ($count > 5000)
        $round_fak = 500;
      else
        if ($count > 2000)
          $round_fak = 200;
        else
          if ($count > 1000)
            $round_fak = 100;
          else
            if ($count > 140)
              $round_fak = 30;
            else
              $round_fak = 20;

      $message['attacker'] .= $data1['name'].": ca. ".round($count / $round_fak) * $round_fak."\n";
    } 
    else {
      $message['attacker'] .= $data1['name'].": ".$erg[$i]['count']."\n";
    }
    $message['defender'] .= $data1['name'].": ".$erg[$i]['count']." von ".$uownerName['name']."\n";
  }
  do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$attowner.",".$endtime.",'Angriff aufgerieben: ".$cityName."','".$message['attacker']."',4)");
  do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$attowner);
  
  
  if (!isset ($defenders) || sizeof($defenders) == 0) {
    alert("DEFENDERS NULL!!!\n");
    if($defowner) {
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$defowner.",".$endtime.",'Angreifer besiegt: ".$cityName."','".$message['defender']."',4)");
      do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$defowner);
    }
  } 
  else {   
    foreach ($defenders as $def) {
      if (DEBUG_SERVICE)
        echo " Message an Defender $def\n";
      
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$def.",".$endtime.",'Angreifer besiegt: ".$cityName."','".$message['defender']."',4)");
      do_mysql_query("UPDATE player SET cc_towns=1,cc_messages=1 WHERE id=".$def);
    }
  } // else
}

function fightDraw($aid, $end, $endtime, $attowner, $defowner, $defenders) {
  do_mysql_query("DELETE FROM army WHERE aid = ".$aid);
  do_mysql_query("DELETE FROM armyunit WHERE aid = ".$aid);
  do_mysql_query("DELETE FROM cityunit WHERE city = ".$end);
  
  if($defowner) {
    $playerName = resolvePlayerName($defowner);
    if (!$playerName) {
      log_fatal_error("fightDraw(playerName): DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." def: ".$defowner);
    }
  }
  else {
    $playerName = "HERRENLOS";
  }

  $playerName2 = resolvePlayerName($attowner);    
  if (!$playerName2) {
    log_fatal_error("fightDraw(playerName2): DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." def: ".$defowner);
  }

  $cityName = resolveCityName($end);
  if (!$cityName) {
    log_fatal_error("fightDraw(cityName): Endstadt: ".$end.", DefOwn: ".$defowner.", AttOwn: ".$attowner." attstr: ".$attstr.", defstr: ".$defstr." def: ".$defowner);
  }
  // Nachricht schreiben

  $message['attacker'] = "Die Stadt <b>".$cityName."</b> des Spielers <b>".$playerName."</b> wurde auf Euer Geheiß hin angegriffen. Der Kampf endete mit einem unentschieden.\n\n";
  $message['defender'] = "Eure Stadt <b>".$cityName."</b> wurde vom Spieler <b>".$playerName2."</b> angegriffen. Der Kampf endete mit einem unentschieden.\n\n";

  do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$attowner.",".$endtime.",'Angriff: ".$cityName."','".$message['attacker']."',4)");
  do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$attowner);

  foreach ($defenders as $def) {
    do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$def);
    do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$def.",".$endtime.",'Angriff auf: ".$cityName."','".$message['defender']."',4)");
  }
}

//Reason:
//0 == Es wird keine Nachricht verschickt
//1 == Stadt nicht mehr vorhanden
//2 == Kämpfe nicht gegen Neutralen Spieler
//3 == Ausgangsstadt existiert nicht mehr, neue Zielstadt suchen (Bei Missionstyp return)
function goBack($armyid, $reason) {
  if (DEBUG_SERVICE)
    echo " goBack(".$armyid.",".$reason.") ";
  //Armeedaten holen
  $res1 = do_mysql_query("SELECT aid, start, end, starttime, endtime, mission, missiondata, owner, tactic, player.religion AS religion FROM army,player WHERE army.owner=player.id AND army.aid=".$armyid);
  $data1 = mysql_fetch_assoc($res1);
  $oldend = $data1['end'];
  if ($data1['mission'] == "return" || $data1['end'] == $data1['start']) {
    //Ausgangsstadt holen
    $res7 = do_mysql_query("SELECT name,owner FROM city,map WHERE map.id=city.id AND city.id=".$data1['start']);
    //Stadt vorhanden und uns nicht neutral gesinnt?
    $data7 = mysql_fetch_assoc($res7);
    if ($data7 != FALSE && ($data7['owner'] == $data1['owner'] || getWarRel($data7['owner'], $data1['owner']) != 1)) {
      if (DEBUG_SERVICE)
        echo " Stadt vorhanden, einfach zurück armyowner: ".$data1['owner']." stadtowner: ".$data7['owner']." ";
      //Zurück zur vorhanden Ausgangsstadt
      do_mysql_query("UPDATE army SET end = start, mission = 'return', endtime = (".time()." + (endtime - starttime)), starttime=".time()." WHERE aid = ".$armyid);
    } else {
      //Neue Zielstadt berechnen
      $pos_res = do_mysql_query("SELECT x,y FROM map WHERE id=".$data1['end']);
      if ($pos = mysql_fetch_assoc($pos_res)) {
        $cty = getNearestOwnCity($pos['x'], $pos['y'], $data1['owner']);
        $armyunits_res = do_mysql_query("SELECT speed FROM armyunit,unit WHERE armyunit.unit=unit.id AND armyunit.aid=".$data1['aid']." ORDER BY speed ASC LIMIT 1");
        if ($armyunits = mysql_fetch_assoc($armyunits_res)) {
          $speed = $armyunits['speed'];
        } else {
          $speed = 1;
        }
        $wt = computeWalktime($pos['x'], $pos['y'], $cty[2], $cty[3], $speed, null);
        
        if (DEBUG_SERVICE)
          echo " Speed auf ".$speed." gesetzt Startpos X: ".$pos['x']." Startpos Y: ".$pos['y']." NextCityX: ".$cty[2]." NextCityY: ".$cty[3]." WalkTime: ".$wt." ";
        do_mysql_query("UPDATE army SET end = ".$cty[0].", start= ".$cty[0].", mission = 'return', endtime = ". (time() + $wt).", starttime=".time()." WHERE aid = ".$data1['aid']);
      } else {
        log_fatal_error("id ".$data1['owner']." in map nicht gefunden, aid: ".$data1['aid']);
      }
    }
  } else {
    do_mysql_query("UPDATE army SET end = start, mission = 'return', endtime = (".time()." + (endtime - starttime)), starttime=".time()." WHERE aid = ".$armyid);
  }
  if ($reason != 0) {
    $pos_res = do_mysql_query("SELECT name,owner,x,y FROM map LEFT JOIN city USING(id) WHERE map.id=".$oldend);
    $pos = mysql_fetch_assoc($pos_res);
    
    if ($reason == 1) {
      $message = "Die Stadt an den Koordinaten ".$pos['x'].":".$pos['y']." ist nicht mehr vorhanden.";
    }
    elseif ($reason == 2) {
      $message = "Stadt ".$pos['name']." an ".$pos['x'].":".$pos['y']." wurde nicht angegriffen, da uns der Herrscher nicht feindlich gesinnt ist.";
    }
    elseif ($reason == 3) {
      $message = "Die Heimatstadt an ".$pos['x'].":".$pos['y']." existiert nicht mehr. Es wurde eine neue gesucht.";
    }
    do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['owner'].",".time().",'Heimkehr angeordnet','".$message."',4)");
    // cc_messages aktualisieren
    do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$data1['owner']);
  }
}

function arrivalArmy() {
  if (DEBUG_SERVICE)
    echo " arrivalArmy() aufgerufen ";

  // Alle Armeen auswählen die jetzt auf "Bereit" stehen
  $res1 = do_mysql_query("SELECT aid, start, end, starttime, endtime, mission, missiondata, owner, tactic, player.religion AS religion FROM army,player WHERE army.owner=player.id AND endtime<=".time()." ORDER BY endtime, aid");

  echo " Datensätze: ".mysql_numrows($res1)."\n";

  //Jede Armee einzeln durchgehen
  while ($data1 = mysql_fetch_assoc($res1)) {
    //Falls siedeln, dann die Funktion das abfangen lassen
    if ($data1['mission'] == "settle") {
      arrivalSettle($data1['end'], $data1['start'], $data1['endtime'], $data1['aid'], $data1['missiondata'], $data1['religion'], $data1['owner']);
      continue;
    }

    //Alle restlichen Missionstypen brauchen eine Stadt, also schauen wir nach ob eine vorhanden ist
    $res7 = do_mysql_query("SELECT name,owner,population,capital,x,y FROM city,map WHERE map.id=city.id AND city.id=".$data1['end']);

    //Stadt vorhanden?
    if ($data7 = mysql_fetch_assoc($res7)) {
      if (DEBUG_SERVICE)
        echo " Stadt vorhanden\n ";

      // Wenn wir selbst Besitzer der Zieltadt sind, dann kann es nur Heimkehr oder Verschiebung sein.
      if ($data1['owner'] == $data7['owner']) {
        if (($data1['mission'] == "move") || ($data1['mission'] == 'return')) {
          if ($data1['mission'] == "move")
            $missionstr = "Verschiebung";
          else
            $missionstr = "Heimkehr";
        } 
        else {
          $missionstr = "Verschiebung (war: ";
          if ($data1['mission'] == "attack")
            $missionstr .= "Angriff)";
          if ($data1['mission'] == "burndown")
            $missionstr .= "Brandschatzen)";
          if ($data1['mission'] == "despoil")
            $missionstr .= "Plündern)";
        }
        arrivalMove($data1['end'], $data1['owner'], $data1['endtime'], $data1['missiondata'], $data1['aid'], $missionstr);
        continue;
      } // Wir selbst sind besitzer -> Rückkehr oder Verschiebung


      //Beziehung zum Besitzer der Stadt ermitteln
      $relation = getWarRel($data1['owner'], $data7['owner']);
      if (DEBUG_SERVICE)
        printf(" Beziehung zum Besitzer der Stadt (PID %d) ermittelt: %d\n", $data7['owner'], $relation);

      // Wenn die Stadt einem Gegner gehöhrt, kann es nur Angriff sein
      if ($relation == 0) {
        $mission = $data1['mission'];
        // Falls kein Angriff eingestellt war, auf attack umstellen (default)
        if (!in_array($mission, array ("attack", "burndown", "despoil", "siege"))) {
          $mission = "attack";
        }

        //arrivalAttack($mission, $cityid,     $cityowner,      $cityname,      $citypopulation,      $armyid,       $armyowner,      $endtime,          $tactic);
        if (DEBUG_SERVICE)
          echo " Mission: ".$mission." CityID: ".$data1['end']." CityOwner: ".$data7['owner']." CityName: ".$data7['name']." CityPopulation: ".$data7['population']." ArmyID: ".$data1['aid']." ArmyOwner: ".$data1['owner']." ArmyArrival: ".$data1['endtime']." Tactic: ".$data1['tactic']."\n";

        //log_fatal_error(" Mission: ".$mission." CityID: ".$data1['end']." CityOwner: ".$data7['owner']." CityName: ".$data7['name']." CityPopulation: ".$data7['population']." ArmyID: ".$data1['aid']." ArmyOwner: ".$data1['owner']." ArmyArrival: ".$data1['endtime']." Tactic: ".$data1['tactic']." ");
        if ($mission == "siege") {
          // Belagerung. Nichts ausser logging
          echo "Belagerung. CityID: ".$data1['end']." CityOwner: ".$data7['owner']." CityName: ".$data7['name']." CityPopulation: ".$data7['population']." ArmyID: ".$data1['aid']." ArmyOwner: ".$data1['owner']." ArmyArrival: ".$data1['endtime']." Tactic: ".$data1['tactic']."\n";
          
        }
        else {
          arrivalAttack($mission, $data1['end'], $data7['owner'], $data7['name'], $data7['population'], $data1['aid'], $data1['owner'], $data1['endtime'], $data1['tactic']);
        } // else
      } // if ($relation == 0)
      //Falls die Stadt einem Verbündeten gehöhrt, kann es immer nur Stationieren sein
      elseif ($relation == 2) {
        $missionstr = "Stationierung";
        arrivalMove($data1['end'], $data1['owner'], $data1['endtime'], $data1['missiondata'], $data1['aid'], $missionstr);
      }
      //Falls sie sich neutral gegenüber stehen, wird Rückkehr angeohrdnet
      else {
        goBack($data1['aid'], 2);
      }
    } 
    else {
      // Falls keine Stadt vorhanden ist, zurückkehren
      // wenn schon auf dem Rückweg und keine Stadt vorhanden ist, nächstegelegene Suchen
      if (DEBUG_SERVICE)
        echo " Stadt nicht vorhanden ";

      if ($data1['mission'] == 'return') {
        // Die Stadt gibts nicht mehr, also Rückkehr
        goBack($data1['aid'], 3);
      } 
      else {
        goBack($data1['aid'], 1);
      }
    }
    
    // Ende
  }
}



function CheckPlayerDeleted($playerid, $ignorecity) {
  // Nachschauen ob der Spieler noch Städte hat
  $res10 = do_mysql_query("SELECT id,name FROM city WHERE owner = ".$playerid." AND id <> ".$ignorecity);
  if (DEBUG_SERVICE)
    echo " CheckPlayerDeleted(".$playerid.",".$ignorecity.") ";
  if (mysql_num_rows($res10) == 0) {
    echo " restart_player ";
    restart_player($playerid);
    return true;
  } 
  else {
    echo " Spieler existiert noch ";
    return false;
  }
}


/**
 * Hole die (eindeutigen) IDs der zusätzlichen Verteidiger einer Stadt.
 * 
 * @param $cityid
 * @param $cityowner
 * @return unknown_type
 */
function getDefenderIDs($cityid, $cityowner) {
  // leeres Array nalegen
  $def = array();
  
  
  if (DEBUG_SERVICE) {
    echo " CityID: ".$cityid." CityOwner: ".($cityowner ? $cityowner : "NULL")." ";
  }
    
  // Nicht herrenlos
  if($cityowner) {
    // Stadtverteidiger gehört auf alle Fälle dazu!
    array_push($def, $cityowner);
    
    $defenders_res = do_mysql_query("SELECT DISTINCT owner FROM cityunit WHERE owner != ".$cityowner." AND city=".$cityid);

    // Sichergehen das der Stadtbesitzer als Verteidiger eingetragen ist (damit er aufjedenfall einen Kampfbericht bekommt)    
    while ($defender = mysql_fetch_assoc($defenders_res)) {
      array_push($def, $defender['owner']);
      if (DEBUG_SERVICE)
        echo " Defender:".$defender['owner']." \n";
    }
  }
  else {
     if (DEBUG_SERVICE) echo " HERRENLOS, keine defender_ids\n";
  }
  
  return $def;
}


function arrivalAttack($mission, $cityid, $cityowner, $cityname, $citypopulation, $armyid, $armyowner, $endtime, $tactic) {
  echo " ArrivalAttack: ArmyOwner: ".$armyowner."<br>\n";

  if (DEBUG_SERVICE)
    echo "DOING ".$mission;
  //Da Angriff, erstmal alle Verteidiger und Angreifer zusammenzählen

  //Flag ob Spieler Spieler augelöscht wurde
  $playerkilled = false;

  //Alle Verteidiger zusammenzaehlen
  $defender_ids = getDefenderIDs($cityid, $cityowner);  
  
  
  if (0   && DEBUG_SERVICE) {
    echo "Verteidiger-IDs:\n";
    var_dump($defender_ids);
    echo "\n";
  }
  
  
  $erg = attCity($defender_ids, $armyowner, $cityid, $armyid, $tactic);

  
  if (DEBUG_SERVICE)
    echo " DONE attCity \n";

  if (DEBUG_SERVICE)
    var_dump($erg[0]);

  //Unentschieden?
  if ($erg == NULL || $erg == false) {
    if (DEBUG_SERVICE)
      echo " No one wins ";
    fightDraw($armyid, $cityid, $endtime, $armyowner, $cityowner, $defender_ids);
  }
  //Verteidiger gewonnen
  elseif ($erg[0]['player'] != $armyowner) {
    if (DEBUG_SERVICE) {
      echo " DEFENDER ".$erg[0]['player']." wins \n";
	    echo "Truppen nach dem Kampf\n";
		  for ($t = 0; $t < sizeof($erg); ++ $t) {
		  	echo "Einheit: ".$erg[$t]['id'].", Anzahl: ".$erg[$t]['count'].", Besitzer: ".$erg[$t]['player']."\n";
		  }
    }
    defWin($armyid, $cityid, $endtime, $armyowner, $defender_ids, $cityowner, $erg);
  }
  //Angreifer gewonnen
  else {
    if (DEBUG_SERVICE) {
      echo " ATTACKER ".$erg[0]['player']." wins \n";
      echo "Truppen vor dem Kampf\n";
      $get_att_troops = do_mysql_query("SELECT unit, count FROM armyunit WHERE aid=".$armyid);
      $t = 0;
      while ($att_troops = mysql_fetch_assoc($get_att_troops)) {
			  $tba[$t]['id'] = $att_troops['unit'];
			  $tba[$t]['count'] = $att_troops['count'];
			  echo "Einheit: ".$att_troops['unit'].", Anzahl: ".$att_troops['count']."\n";
			  $t++;
			}
      echo "Truppen nach dem Kampf\n";
		  for ($t = 0; $t < sizeof($erg); ++$t) {
		    //$taa[$t]['id'] = $erg['unit'];
			  //$taa[$t]['count'] = $erg['count'];
			  $taa[$erg[$t]['id']] = $erg[$t]['count'];
		  	echo "Einheit: ".$erg[$t]['id'].", Anzahl: ".$erg[$t]['count']."\n";
		  }
		  echo "Verluste waehrend des Kampfes\n";
		  for ($t = 0; $t < sizeof($tba); ++$t) {
			  if(isset($taa[$tba[$t]['id']])) {
				  $loss[$t]['id'] = $tba[$t]['id'];
					$loss[$t]['count'] = $tba[$t]['count'] - $taa[$tba[$t]['id']];
				  echo "Einheit: ".$loss[$t]['id'].", Anzahl: ".$loss[$t]['count']."\n";
				}
				else {
				  echo "Einheit: ".$tba[$t]['id'].", Anzahl: ".$tba[$t]['count']." wurde komplett zerstört\n";
				}
			}
    }

    //Alle verteidigenden Einheiten entfernen
    do_mysql_query("DELETE FROM armyunit WHERE aid = ".$armyid);
    do_mysql_query("DELETE FROM cityunit WHERE city = ".$cityid);
    

    //Erobern
    if ($mission == "attack") {
      if (DEBUG_SERVICE)
        echo " EROBERN ";
      do_mysql_query("DELETE FROM army WHERE aid = ".$armyid);
      do_mysql_query("DELETE FROM cityunit_ordered WHERE city = ".$cityid);
      do_mysql_query("DELETE FROM citybuilding_ordered WHERE city = ".$cityid);
      do_mysql_query("DELETE FROM citybuilding WHERE city = ".$cityid." AND building IN (SELECT id FROM building WHERE makescapital=1)");
      
      /**
       * Loyalität Ändern.
       */
      if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
        $c = do_mysql_query_fetch_assoc("SELECT * FROM city WHERE id  = ".$cityid );        
 
        $newloy = defined("MIN_LOYALITY") && MIN_LOYALITY > 0 ? MIN_LOYALITY : 0;
        
        // Wenn der Angreifer der "alte" Stadtbesitzer ist, dann bekommt er seine
        // alte Loyalität zurück, sofern sie noch hoch genug ist.
        if($armyowner == $c['prev_owner']) {
          if($c['prev_loyality'] > $newloy)
            $newloy = $c['prev_loyality'];
            
          $sql = sprintf("UPDATE city SET capital=0, prev_owner = owner, prev_loyality = loyality, owner = %d, loyality = %d, populationlimit = NULL  WHERE id = %d",
                           $armyowner, $newloy, $cityid
                           );
          echo $sql."\n";
          do_mysql_query($sql);
        }        
        // Ansonsten wird der Angreifer zum neuen Besitzer, hat MIN_LOYALITY,
        // und der aktuelle Besitzer wird zum alten Besitzer, falls seine
        // derzeitige Loyalität hoch genug ist.
        else {
          if($c['loyality'] > $c['prev_loyality']) {
            // Der aktuelle Stadtbesitzer hat den ganz alten Stadtbesitzer an 
            // Loyalität überholt und wird zum "alten Stadtbesitzer"
            $prev_loy   = $c['loyality'];
            $prev_owner = $c['owner']; 
          }
          else {
            // Alles bleibt beim alten
            $prev_loy   = $c['prev_loyality'];
            $prev_owner = $c['prev_owner'];
          }
          
          // Aktualisieren. prev_owner kann auch NULL sein, wenn die Stadt vorher herrenlos war
          $sql = sprintf("UPDATE city SET capital=0, owner = %d, prev_owner = %s, loyality = %d, prev_loyality = %d, populationlimit = NULL WHERE id = %d",
                          $armyowner, $prev_owner ? $prev_owner : "NULL", $newloy, $prev_loy, $cityid
                          );
                          
          echo $sql."\n";
          do_mysql_query($sql);
        }        
      }
      else {
        do_mysql_query("UPDATE city SET capital=0, owner = '".$armyowner."', populationlimit = NULL WHERE id = '".$cityid."'");
      }      
      
      // Markt Rücknahmen
      marketRetractionForCity($cityid);
      // Nochmal vorsichtshalber alles auf 0 setzen...
      do_mysql_query("UPDATE city SET reserve_shortrange=0, reserve_longrange = 0, ".
                     " reserve_armor = 0,reserve_horse = 0 ".
                     " WHERE id = '".$cityid."'");

      
      //Schauen ob der Spieler noch Städte hat
      if($cityowner) {
        $playerkilled = CheckPlayerDeleted($cityowner, $cityid);
        if($playerkilled)
        remove_player_armies($cityowner);
      }
	
      // Die Überlebenden als Stadtwache eintragen
      for ($i = 0; $i < sizeof($erg); $i ++) {
        do_mysql_query("INSERT INTO cityunit VALUES ('".$cityid."','".$erg[$i]['id']."','".$erg[$i]['count']."',".($armyowner ? $armyowner : "NULL").")");
      }
      $attstr = "Ihr habt die Stadt erobert.";
      $defstr = "Die Stadt wurde erobert.";
      
      // Siedlertrupps behandeln
      echo "\ncheck_settler aufrufen [erobern]";
      check_settler($cityid);
    }
    //Brandschatzen
    elseif ($mission == "burndown") {
      // Beim Brandschatzen wird zunächst gegen die Stadtbevölkerung angetreten
      if (DEBUG_SERVICE)
        echo " BRAND \n";

      for ($i = 0; $i < sizeof($erg); $i ++) {
        $burnat[$i]['id'] = $erg[$i]['id'];
        $burnat[$i]['count'] = $erg[$i]['count'];
        $burnat[$i]['player'] = $armyowner;
      }

      $burndef[0]['id'] = DORFBEWOHNER_ID;
      $burndef[0]['count'] = $citypopulation;
      $burndef[0]['player'] = $cityowner;

      $burnres = do_mysql_query("SELECT sum(res_defense) FROM citybuilding,building WHERE citybuilding.building=building.id AND id=".$cityid);
      if ($burndata = mysql_fetch_assoc($burnres))
        $defbonus = $defbonus ? $defbonus : 0;
      else
        $defbonus = 0;

      $burnerg = fight($burnat, $burndef, $defbonus, $tactic);

      // Angreifer hat gegen Dorfbewohner gewonnen
      if ($burnerg[0]['player'] == $armyowner) {
        // Armee-Einheiten wieder einfügen
        for ($i = 0; $i < sizeof($burnerg); $i ++) {
          do_mysql_query("INSERT INTO armyunit VALUES  (".$armyid.", ".$burnerg[$i]['id'].", ".$burnerg[$i]['count'].")");
        }
        
        //Schauen ob der Spieler noch Städte hat
        if($cityowner) {        
          $playerkilled = CheckPlayerDeleted($cityowner, $cityid);
          if($playerkilled)
            remove_player_armies($cityowner);
        }
        
        // Noch plündern vorm Brandschatzen
        $city['cid']     = $cityid;
        $city['owner']   = $cityowner;
        $city['attacker']= $armyowner;

        $price = compute_despoil_new($city, $burnerg, true);

        //Heimkehr
        goBack($armyid, 0);

        //Stadt löschen
        removeCity($cityid);


        $attstr = "\nEure treuen Untertanen hatten den Auftrag, bis auf den letzten Stein alles niederzubrennen. ";
        $defstr = "\nDie Bewohner wurden barbarisch niedergemetzelt, die Gebäude in Brand gesteckt und alles ".
          "geplündert, was den Feinden zwischen die Pranken geriet. Über die Menge an Gold, welches die ".
          "Feinde erbeutet haben, sind uns keine Informationen bekannt.";
        
        if ($price['gold'] > 0) {
          $attstr .= "Einige verrichteten diese Aufgabe mit Freude und raubten alles, ".
            "was noch irgendwie wertvoll erschien.\n<b>Ihr habt ".$price['gold']." Gold erbeutet</b>, bevor der ".
            "letzte Einwohner getötet oder vertrieben war.";

          if ($price['cgold'] > 0) {
            $attstr .= "\nEinige Eurer Truppen entdeckten in den Gebäuden des ehemaligen Herrschers einige ".
              "Vorräte an Gold und konnten ".$price['cgold']." Gold aus der Ordenskasse rauben.";
          }
        }
        else {
          $attstr .= "Noch bevor die letzten Häuser in Brand gesteckt und die letzten Einwohner getötet ".
            "oder vertrieben waren, wurde Euren Truppen bereits klar, dass hier <b>nichts zu rauben war</b>.";
          if ($price['cgold'] > 0) {
            $attstr .= "\nZu ihrer Freude fanden einige wenige Truppen Gelder aus der Ordenskasse des ".
              "ehemaligen Herrschers. Immern konnten so ".$price['cgold']." Gold erbeutet werden.";
          }
        }
        
        $erg = $burnerg;
      }
      elseif ($burnerg == NULL || $erg == false) {
        do_mysql_query("DELETE FROM army WHERE aid = ".$armyid);
        do_mysql_query("UPDATE city SET population=1 WHERE id=".$cityid);
        $attstr = "\nIhr konntet die Stadtverteidigung zwar besiegen, die Stadt jedoch konnte nicht gebrandschatzt werden, derweil eure Truppen von der erzürnten Bevölkerung in Stücke gerissen wurden.";
        $defstr = "\nDie Stadtverteidigung wurde besiegt, beherzte Stadtbewohner konnten die Stadt jedoch retten. Viele bezahlten ihren Mut mit ihrem Leben.";
        unset ($erg);
        $erg = $brunerg;
        //Stadtbewohner haben gewonnen			
      }
      else {
        do_mysql_query("DELETE FROM army WHERE aid = ".$armyid);
        do_mysql_query("UPDATE city SET population=".$burnerg[0]['count']." WHERE id=".$cityid);
        $attstr = "\nIhr konntet die Stadtverteidigung zwar besiegen, die Stadt jedoch konnte nicht gebrandschatzt werden, derweil eure Truppen von der erzürnten Bevölkerung in Stücke gerissen wurden.";
        $defstr = "\nDie Stadtverteidigung wurde besiegt, beherzte Stadtbewohner konnten die Stadt jedoch retten. Viele bezahlten ihren Mut mit ihrem Leben.";
        unset ($erg);
        $erg = $burnerg;
      }
      // Siedlertrupps behandeln
      echo "\ncheck_settler aufrufen [brandschatzen]";
      check_settler($cityid);
    }
    //Plündern
    elseif ($mission == "despoil") {
      if (DEBUG_SERVICE)
        echo " PLÜNDERN ";

      goBack($armyid, 0);
      for ($i = 0; $i < sizeof($erg); $i ++) {
        do_mysql_query("INSERT INTO armyunit VALUES  (".$armyid.", ".$erg[$i]['id'].", ".$erg[$i]['count'].")");
      }
      //despoil part of the script
      $desp['cid'] = $cityid;
      $desp['ew'] = $citypopulation;
      $desp['owner'] = $cityowner;
      $desp['attacker'] = $armyowner;

      if (defined("NEW_DESPOIL") && NEW_DESPOIL) {
        if (DEBUG_SERVICE)
          echo "NEUES PLÜNDERN\n";

        $price = compute_despoil_new($desp, $erg);
        var_dump($price);
        echo "price['settler'] 2 = ".$price['settler']."\n";
        // Plünderung OK
        if ($price['gold'] > 0) {
          $attstr = "\nNach der erfolgreichen Überwindung der Stadtverteidigung habt Ihr die Stadt ".
            "aufs äusserste ausgenommen und alles geraubt, was Eure Mannen tragen konnten.";
          if($price['penalty'] > 0) {
            $attstr .= " Ganze <b>".$price['penalty']." Mannen des Pöbels</b> mußten mit ihrem Leben bezahlen.";
          }
          if($price['settler'] > 0) {
            $attstr .= " Ebenso haben <b>".$price['settler']." Siedler des Gegners</b> ihr Leben gelassen, da ihr einen Grossteil des Nahrungsnachschubs geraubt habt.";
          }
          $attstr .= "\n<b>Ihr habt ".$price['gold']." Gold geplündert</b>.";

          $defstr = "\nDer Schurke hat Eure wehrlosen Bürger auf niederträchtigste Weise beraubt und ".
            "um Ihr Hab und Gut gebracht. ";
          if($price['penalty'] > 0) {
            $defstr .= "Dabei kamen <b>".$price['penalty']."</b> wehrlose <b>Bürger</b> ums Leben.";
          }
          if($price['settler'] > 0) {
            $defstr .= " <b>".$price['settler']."</b> eurer <b>Siedler mussten ihr Leben gelassen, da die plündernden Horden des Gegners einen Grossteil des Nahrungsnachschubs geraubt haben.";
          }
          $defstr .= "\n<b>Die Truppen raubten Güter im Wert von ".
            $price['gold']." Gold</b>.";    
        }
        // Zu wenig Truppen übrig
        else if ($price['gold'] < 0) {
          $attstr = "\nEure Truppen konnten zwar die Stadtverteidigung besiegen, doch blieb ".
            "Ihnen nicht genügend Kraft, um die erzürnten Bürger auszurauben. Sie traten die Flucht an.".
            "\n<b>Ihr habt kein Gold geplündert!</b>";          
          
          $defstr = "\nDie Angreifer wichen vor den erzürnten Bürgern zurück und traten die Flucht an. ".
            "\n<b>Sie raubten keine Güter</b>.";    
        }
        else {
          $attstr = "\nNachdem Eure Truppen die Stadtverteidigung siegreich hinter sich gelassen ".
            "hatten, fanden sie armseelige und kümmerliche Behausungen vor. Elend und Hunger schwappten ".
            "ihnen entgegen. Nach kurzer Zeit gaben Eure Truppen die Suche nach Wertvollem auf und kehrten ".
            "mit leeren Händen nach Hause.".
            "\n<b>Ihr habt kein Gold geplündert</b>.";          
          
          $defstr = "\nDie vielen armen Bürger hatten nichts, was den Feinden hätte in die Hände fallen ".
            "können.\n<b>Die Truppen gingen leer aus</b>.";    
        }

        if ($price['cgold'] > 0) {
          $attstr .= "\nSire! In den Verwaltungsgebäuden der Stadt lagerten Mittel aus der Ordenskasse. ".
            "Wir haben uns kräftig bedient und <b>zusätzliche ".$price['cgold']." Gold gestohlen</b>!";

          $defstr .= "\nEin schlechter Tag für uns. <b>Aus der Ordenskasse wurden ".
            $price['cgold']." Gold geraubt</b>. Was werden Eure Ordensbrüder dazu sagen?";         
        }

      }
      // OLD DESPOIL
      else {
        $price = compute_despoil($desp, $erg);
        
        $resstr = "Gold: ".$price['gold']."\nHolz: ".$price['wood']."\nEisen: ".$price['iron']."\nStein: ".$price['stone'];
        $resstr .= "\n\nVom Markt:\n\nGold: ".$price['mgold']."\nHolz: ".$price['mwood']."\nEisen: ".$price['miron']."\nStein: ".$price['mstone'];
        
        if ($price['cgold'] > 0)
          $resstr .= "\n\nDavon waren aus der Ordenskasse: ".$price['cgold']." Gold.";

        $attstr = "\nSie konnten die Stadt erfolgreich plündern und folgende Ressourcen erbeuten:\n\n".$resstr;
        $defstr = "\nEr konnte die Stadt plündern und erbeutete folgende Ressourcen:\n\n".$resstr;
      }
    }
    
    attMSG($cityid, $endtime, $armyowner, $defender_ids, $cityowner, $erg, $attstr, $defstr, $playerkilled, $cityname);
  } // else Angreifer gewonnen
}




function updateRes() {
  $tick = TICK;

  // alle auswählen deren Ressourcen (Einwohner,Gold,Städte etc.) aktualisiert werden müssen
  $upt = time();
  $res1 = do_mysql_query("SELECT id,name,lastres,religion,wood,iron,stone,gold,clan,points,avatar, ".
			             " holiday>UNIX_TIMESTAMP() AS holiday".
                         " FROM player WHERE lastres >0 AND lastres+".$tick."<= UNIX_TIMESTAMP() ".
                         "  AND name IS NOT NULL");
  
  echo " Tick = ".$tick.", Upt = '".date("d.m.y G:i:s", $upt)."' ($upt), Datensätze: ".mysql_numrows($res1)."\n";
  


  
  // Schleifenzähler
  $loop=0;
  while ($data1 = mysql_fetch_assoc($res1)) {
    // URLAUBSMODUS ???    
    if ($data1['holiday'])
      $holiday = true;
    else
      $holiday = false;


    printf("\nSpieler %d, Gold: %d\n", $data1['id'], $data1['gold']);  
      
    if($holiday && DEBUG_SERVICE) 
      echo "Spieler '".$data1['id']."' [".$data1['id']."] im Urlaub\n";

    $updatecount = floor(($upt - $data1['lastres']) / $tick);
    if (++$loop % 20 == 1) {
      echo "  ".date("d.m.y G:i:s")." Erreiche Durchlauf $loop der while-Schleife\n";
    }

    if ($updatecount > 1) {
      echo "  ".date("d.m.y G:i:s")." Updatecount = ".$updatecount." ... Problem? -> pid=".$data1['id']."  lastres='".date("d.m.y G:i:s", $data1['lastres'])."' (".$data1['lastres'].")\n";
    }

    
    $flags = get_premium_flags($pid);

    // Avatar überprüfen, ggf. löschen
    if($data1['avatar'] > 0) {
      $pid = $data1['id'];
      
      // Hat der Spieler KEIN Premium UND unter 1 MIO Punkte?      
      $avatar_top_points = defined("AVATAR_TOP_POINTS") ? AVATAR_TOP_POINTS : 1000000;
      if($avatar_top_points > $data1['points'] && $flags == 0 ) {
        if(DEBUG_SERVICE)
          echo "Spieler hat kein Premium mehr oder nicht genug Punkte -> Lösche Avatar\n";
            
        // Avatar weg. Settings weg.
        do_mysql_query("UPDATE player SET avatar = 0, settings=0 WHERE id = ".$pid);

        $filename=AVATAR_DIR.$pid.".jpg";
        if(is_file($filename)) {
          unlink($filename);
        }
      }      
    }
    
    // FIXME: Einstellungen und Signatur für nicht-mehr-premium-user zurücksetzen
    
    
    for ($i = 0; $i < $updatecount; ++ $i) {
      // Gold initialisieren. Wird am ende der Schleife angepasst
      $gold = $data1['gold'];
    
      // Stadtbezogene Daten ermitteln
      $pop = 0;      
      $citycount = 0;
      $research = 0;
      $totwood = 0;
      $totiron = 0;
      $totstone = 0;
      $res2 = do_mysql_query("
SELECT                                    
 sum(citybuilding.count * building.res_shortrange) AS incshortrange,
 sum(citybuilding.count * building.res_longrange) AS inclongrange,
 sum(citybuilding.count * building.res_armor) AS incarmor,
 sum(citybuilding.count * building.res_horse) AS inchorse,
 sum(citybuilding.count * building.res_storage) AS storage,
 sum(citybuilding.count * building.res_wood) AS incwood,
 sum(citybuilding.count * building.res_rawwood) AS incrawwood,
 sum(citybuilding.count * building.res_iron) AS inciron,
 sum(citybuilding.count * building.res_rawiron) AS incrawiron,
 sum(citybuilding.count * building.res_stone) AS incstone,
 sum(citybuilding.count * building.res_rawstone) AS incrawstone,
 sum(citybuilding.count * building.res_rp) AS research,
 sum(citybuilding.count * building.res_gold) AS incgold,
 coalesce(sum(citybuilding.count * building.res_foodstorage), 0) AS foodstorage,
 coalesce(sum(citybuilding.count * building.res_food), 0) AS incfood,
 coalesce(sum(citybuilding.count * building.res_attraction), 0) AS attr,
 city.id AS id,
 city.name AS name,
 city.population AS pop,
 city.prosperity,
 city.loyality,
 city.prev_loyality,
 city.food,
 city.religion,
 city.rawwood,
 city.rawiron,
 city.rawstone,
 city.shortrange,
 city.longrange,
 city.armor,
 city.horse,
 city.reserve_shortrange,
 city.reserve_longrange,
 city.reserve_armor,
 city.reserve_horse,
 city.max_shortrange,
 city.max_longrange,
 city.max_armor,
 city.max_horse,
 city.populationlimit AS poplimit
 FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city 
 LEFT JOIN building ON building.id = citybuilding.building 
 WHERE city.owner = ".$data1['id']." GROUP BY id ORDER BY city.capital DESC, city.id ASC"
);
    

      $citycount = mysql_num_rows($res2);
      $adm_level = get_adm_level($data1['id']);

      
      $res_eff = get_eff(floor($citycount / 2), $adm_level);        
      $eff = get_eff($citycount, $adm_level) * $res_eff;
      
      if ($holiday) {
        $res_eff = 0;
        $eff = round($eff / 2);
      }
    

      $totwood = $data1['wood'];
      $totiron = $data1['iron'];
      $totstone= $data1['stone'];
      $ucost = get_ucost($data1['id']);

      // Halbe Truppekosten im Urlaubsmodus
      if ($holiday) 
        $gold -= round($ucost/2);
      else
        $gold -= $ucost;

      // Steuern mit 0 initialisieren
      $taxes = 0;

      /*** WHILE ***/
      while ($data2 = mysql_fetch_assoc($res2)) {
        $herrenlos = $data1['id'] == null;
        
        // Wenn länger als 30 Minuten belagert wurde...
        $siege_time   = getSiegeTime($data2['id']);
        $siege_factor = getSiegeFactor( $siege_time );        

        if ( $siege_factor < 1) {
          if (DEBUG_SERVICE) {
            echo "  Stadt ".$data2['name']." (".$data2['id'].") unter Belagerung! Time: ".$siege_time.", Faktor: ".$siege_factor.", citz: ".$data2['pop']."\n";
          }
        }
        else if ($siege_factor > 1) {
          alert("FEHLER bei Belagerung von ".$data2['name']." (".$data2['id']."): siege_factor = ".$siege_factor." > 1!\n");
        }
        
        $storage = $data2['storage'];

        $data2['incrawwood']  = round($data2['incrawwood'] *$siege_factor);
        $data2['incrawiron']  = round($data2['incrawiron'] *$siege_factor);
        $data2['incrawstone'] = round($data2['incrawstone']*$siege_factor);

        //wood
        $wood = get_inc_res($data2['rawwood'] + ($data2['incrawwood'] * RAWWOOD_PRODFACTOR * $res_eff), $data2['incwood'], 2, $storage, WOOD_PRODFACTOR * $res_eff);
        
        //iron
        $iron = get_inc_res($data2['rawiron'] + ($data2['incrawiron'] * RAWIRON_PRODFACTOR * $res_eff), $data2['inciron'], 2, $storage, IRON_PRODFACTOR * $res_eff);
        
        //stone
        $stone = get_inc_res($data2['rawstone'] + ($data2['incrawstone'] * RAWSTONE_PRODFACTOR * $res_eff), $data2['incstone'], 2, $storage, STONE_PRODFACTOR * $res_eff);
        
        //ja, Lager haben eigentlich die 5fache Kapazität (1x res, 4x waffen)
        $storage = $data2['storage'];
        
        //oben ist jeweils das Neuproduzierte in $res
          
        //shortrange
        $shortrange = get_inc_wep($totiron + $iron['res'], $data2['incshortrange'], SHORTRANGE_COST, $storage, $data2['reserve_shortrange'], $data2['shortrange'], SHORTRANGE_PRODFACTOR * $res_eff, $data2['max_shortrange']);
        $iron['res'] = $shortrange['raw'];

        //longrange
        $longrange = get_inc_wep($totwood + $wood['res'], $data2['inclongrange'], LONGRANGE_COST, $storage, $data2['reserve_longrange'], $data2['longrange'], LONGRANGE_PRODFACTOR * $res_eff, $data2['max_longrange']);
        $wood['res'] = $longrange['raw'];

        //horse
        $horse = get_inc_wep($gold, $data2['inchorse'], HORSE_COST, $storage, $data2['reserve_horse'], $data2['horse'], HORSE_PRODFACTOR * $res_eff, $data2['max_horse']);
        $gold = $horse['raw'];

        //armor
        //$iron enthält das ganze Eisen, siehe shortrange
        $armor = get_inc_wep($iron['res'], $data2['incarmor'], ARMOR_COST, $storage, $data2['reserve_armor'], $data2['armor'], ARMOR_PRODFACTOR * $res_eff, $data2['max_armor']);
        $iron['res'] = $armor['raw'];

        //wood und iron werden schon bei der Waffenprodukion aufs Gesamte gesetzt
        $totwood   = $wood['res'];
        $totiron   = $iron['res'];
        $totstone += $stone['res'];

        // Unter Belagerung gibts keine Nahrung
        $data2['incfood'] = round($data2['incfood'] * $siege_factor);
        
        $attr =  $data2['attr'] + 1000;
        
        // Wohlstand. Doppelter Zuwachs bei Wohlstand < ATTR
        if ($data2['prosperity'] < $attr) 
          $prosperity_grow = round($data2['pop'] / 5);
        else
          $prosperity_grow = round($data2['pop'] / 10);

        // Neuer Wohlstand
        $prosperity = $data2['prosperity'] + $prosperity_grow;
        
        if ($prosperity > PROSPERITY_MAX_FACTOR*$attr) $prosperity = PROSPERITY_MAX_FACTOR *$attr;
        if ($prosperity < 0) alarm("prosperity < 0, Stadt ".$data2['id']."\n".__LINE__);
                 
        
        /*********************** Loyalität behandeln *************************/
        // Loyalität wird in Hundertstel Prozent gespeichert (0-10000)
        define("MAX_LOYALITY", 10000);
        
        if(defined("LOYALITY_GROWTH")) 
          $loygrowth = LOYALITY_GROWTH;
        else 
          $loygrowth = 100;
        
          // 10.000 ist der Maximalwert
        $loyality = min(MAX_LOYALITY, $data2['loyality'] + $loygrowth);
        
        if( $data2['prev_loyality'] + $loyality > MAX_LOYALITY) {
          $prev_loyality = MAX_LOYALITY - $loyality;
        }
        else {
          $prev_loyality = $data2['prev_loyality'];
        }
        
        // HERRENLOS behandlung
        if($herrenlos) {
          $settler_data['settler_sum'] = 0;
        }
        else {
          /**************************** Sonderbehandlung Siedler **************/
          // Siedler in die Nahrungsberechnung mit einbeziehen
          $settler_data = do_mysql_query_fetch_array("SELECT sum(missiondata) as settler_sum FROM army WHERE start = ".$data2['id']." AND owner=".$data1['id']);          
        }
        
        // population_settler = pop + settler
        $population_settler = get_new_pop($data2['food'] + $data2['incfood'], $attr, $data2['pop']+$settler_data['settler_sum'], $data2['poplimit']);
        
        // population = population_settler - settler
        $population = $population_settler - $settler_data['settler_sum'];
        
        if($settler_data['settler_sum']==NULL) {
          $settler_data['settler_sum'] = 0;
        }
        //if (DEBUG_SERVICE) echo "Buerger_Siedler_alt = ".($data2['pop']+$settler_data['settler_sum']).", Buerger_Siedler_neu = ".$population_settler.", Buerger_neu = ".$population.", Siedler = ".$settler_data['settler_sum']."\n"; 
        
        
        // Wenn Gesamtzahl Einwohner < Anzahl Siedler oder Bürger < 1
        if(($population_settler<$settler_data['settler_sum']) || ($population<1)) {
          $settler_sum_old = $settler_data['settler_sum'];
          $settler_sum_new = $settler_data['settler_sum'] - abs($population) - 1;
          $population = 1;
          if (DEBUG_SERVICE) echo "Siedler sterben -> settler_sum_old = ".$settler_sum_old.", settler_sum_new = ".$settler_sum_new."\n";
          $get_settler_data = do_mysql_query("SELECT aid,missiondata as settler FROM army WHERE start = ".$data2['id']." AND owner=".$data1['id']);
          $settler_num = mysql_num_rows($get_settler_data);
          $settler_sum_new2 = 0;
          $count_settler_units = 0;
          while ($settler_data = mysql_fetch_assoc($get_settler_data)) {
            // Prozentzahl der sterbenden Siedler pro Trupp
            $settler_percentage = $settler_data['settler']/$settler_sum_old;
            $new_settler_amount = floor($settler_percentage * $settler_sum_new);
            do_mysql_query("UPDATE army SET missiondata = ".$new_settler_amount." WHERE aid = ".$settler_data['aid']);
            if (DEBUG_SERVICE) echo "army = ".$settler_data['aid'].", old_settler = ".$settler_data['settler'].", new_settler = ".$new_settler_amount.", settler_percentage = ".$settler_percentage."\n";
            $settler_sum_new2 += $new_settler_amount;
            //$last_aid = $settler_data['aid'];
            //$last_amount = $new_settler_amount;
            // ID's und Grösse der Siedlertrupps in einem Feld speichern
            $settler_array[$count_settler_units]['id'] = $settler_data['aid'];
            $settler_array[$count_settler_units]['count'] = $new_settler_amount;
            $count_settler_units++;
          }
          // Test, ob korrekte Anzahl Siedler gestorben ist
          // wenn nicht zufälligen Trupp auswählen und Differenz abziehen
          if($settler_sum_new2<$settler_sum_new) {
            //echo "Zu wenig Siedler gestorben -> gleiche aus: ".($settler_sum_new-$settler_sum_new2)." Siedler zusätzlich gestorben in army = ".$last_aid.", last_amount = ".$last_amount."\n";
            $choose_settler_array_id = mt_rand(0,$count_settler_units-1);
            if (DEBUG_SERVICE) {
              echo "id = ".$settler_array[$choose_settler_array_id]['id'].", count = ".$settler_array[$choose_settler_array_id]['count'].", number = ".$choose_settler_array_id."\n";
              echo "Zu wenig Siedler gestorben -> gleiche aus: ".($settler_sum_new-$settler_sum_new2)." Siedler zusätzlich gestorben in army = ".$settler_array[$choose_settler_array_id]['id']."\n";
              echo "UPDATE army SET missiondata = ".($settler_array[$choose_settler_array_id]['count']-($settler_sum_new-$settler_sum_new2))." WHERE aid = ".$settler_array[$choose_settler_array_id]['id']."\n";
            }
            do_mysql_query("UPDATE army SET missiondata = ".($settler_array[$choose_settler_array_id]['count']-($settler_sum_new-$settler_sum_new2))." WHERE aid = ".$settler_array[$choose_settler_array_id]['id']);
          }
        }
        
        // müssen Siedler gelöscht oder zur Rückkehr gebracht werden?
        //echo "Aufruf von remove_settler in UpdateRes";
        remove_settler($data2['id']);

        // Sonderbehandlung Siedler Ende


        // Nahrung
        $foodstorage = max($data2['food'] + $data2['incfood'] - ($population + $settler_data['settler_sum']), 0);
        $foodstorage_old = max($data2['food'] + $data2['incfood'] - $population, 0);
        if($foodstorage > $data2['foodstorage']) $foodstorage = $data2['foodstorage'];

        echo "\n cityid=".$data2['id'].",foodstorage=".$foodstorage_old.", foodstorage_new=".$foodstorage;

        do_mysql_query("UPDATE city SET population=".$population.", food=".$foodstorage.", prosperity=".$prosperity.", loyality=".$loyality.", prev_loyality=".$prev_loyality.", rawwood=rawwood+".($wood['raw']-$data2['rawwood']).", rawiron=rawiron+".($iron['raw']-$data2['rawiron']).", rawstone=rawstone+".($stone['raw']-$data2['rawstone']).", shortrange=shortrange+'".$shortrange['res']."', longrange=longrange+'".$longrange['res']."', armor=armor+'".$armor['res']."', horse=horse+'".$horse['res']."' WHERE id = ".$data2['id']);
        
        if(!$herrenlos) {
          // Einnahmen der Gebäude berechnen:
          // Im Urlaubsmodus ist res_eff auf 0
          if ($data2['incgold'] > 0)
            $gold+= floor($data2['incgold'] * $res_eff);
          else
            $gold+= floor($data2['incgold']);


          // Steuern und Forschung
          // werden nur dann eingenommen, wenn die Stadt genügend Wohlstand hat
          if ($data2['prosperity'] >= $attr) {
            $citytax   = floor($population/10) * GOLD_PRODFACTOR * $eff;
            $taxes+= $citytax;
            $gold += $citytax;
            $research += round(get_city_research($eff, $population, RESEARCHEW, $data2['research']) * $siege_factor);
          }
        }
      } // while data2 = ...


      if(!$herrenlos) { 
        // ClanTAX / Ordensteuer berechnen
        // $tax = get_clan_tax($data1['clan'], $gold - $data1['gold'] + $ucost, $tax_rate_dummy);
        // Change 14.11.2008 - Ordensteuern nur noch aus Steuergeldern
        $tax = get_clan_tax($data1['clan'], $taxes, $tax_rate_dummy);

        if ($tax) {
          $gold -= $tax;
          log_clan_tax($data1['id'], $data1['clan'], $tax);
          do_mysql_query("UPDATE clan SET gold=gold+".$tax." WHERE id=".$data1['clan']);
        }

        if($gold-$data1['gold'] != 0) {
          echo ",income=".($gold-$data1['gold']);
        }

        // Gold wird nur differentiell updated, weil sonst eine Race-Condition entstehen könnte
        // (Wenn der Spieler während des Service-Laufs den Goldbetrag ändert
        do_mysql_query("UPDATE player SET gold=gold+".($gold-$data1['gold']).
                     ", rp=rp+".$research.
                     ", wood=wood+".(intval($totwood)-$data1['wood']).
                     ", iron=iron+".(intval($totiron)-$data1['iron']).
                     ", stone=stone+".(intval($totstone)-$data1['stone']).
                     ", cc_resources=1,lastres=lastres+".$tick." WHERE id=".$data1['id']);
      }
    } // for updatecount
  }
}

function updateToplist() {
  //player
  do_mysql_query("UPDATE player SET toplist=NULL");
  $res3 = do_mysql_query("SELECT player.status as locked, player.id AS id, player.name AS name, (player.points) AS points, player.religion AS religion, clan.name AS clan FROM player LEFT JOIN clan ON player.clan = clan.id ORDER BY points DESC LIMIT 100");
  $i=0;
  while ($data3 = mysql_fetch_assoc($res3)) {
    $i++;
    do_mysql_query("UPDATE player SET toplist='".$i."' WHERE id='".$data3['id']."'");
  }
  //clan
  do_mysql_query("UPDATE clan SET toplist=NULL");
  $res4 = do_mysql_query("SELECT clan.name AS name, clan.id AS clanid, clan.points AS points FROM clan ORDER BY clan.points DESC LIMIT 100");
  $i=0;
  while ($data4 = mysql_fetch_assoc($res4)) {
    $i++;
    do_mysql_query("UPDATE clan SET toplist='".$i."' WHERE id='".$data4['clanid']."'");
  }
}


// Alarm-String an Admins schicken
function alarm ($string, $title = "ACHTUNG: Service ALARM") {
  mail("service@holy-wars2.de", $title , $string, "FROM: service@holy-wars2.de");
  echo "ALARM: ".$string."\n";
}




/********************************************************************/
echo "\n\n*******************************************************\n";
if(!defined("TICK")) {
  $res = do_mysql_query("SELECT value FROM config WHERE name='tick'");
  if(mysql_num_rows($res) > 0) {
    $t = mysql_fetch_array($res);
    define("TICK", $t[0]);
  }
  else {
    define("TICK", 1800);
  }
  echo "Manually defined TICK: ".TICK."\n";
}
     

//Endlosschleife
$sleepmin = 10; 

if ( defined("SERVICE_SLEEP_MAX") ) {
  $sleepmax = SERVICE_SLEEP_MAX;    
}
else {
  $sleepmax = 60;
}

// Hier ist der Einstiegspunkt für den Service
while (1) {
  $now = time();
   
  
  $start_time = getRoundStartTime();
  
  if($start_time > 0 && $start_time < time()) {
    $end_time = getRoundEndTime();

    if($end_time > 0 && $end_time < time()) {
      $msg = "Die Runde ist beendet. Endtime: ".date("d.m.y G:i:s", $end_time)." [ ".$end_time." ].";
      echo "\n".$msg."\n";
      alarm($msg, __FILE__." beendet. Rundenende.");
      exit();
    }

    echo "\n--------------------------\n".date("d.m.y G:i:s")." - arrivalArmy()\n";
    arrivalArmy();
    echo "\n--------------------------\n".date("d.m.y G:i:s")." - updateRes()\n";
    updateRes();
    echo "\n--------------------------\n".date("d.m.y G:i:s")." - updateToplist()\n";
    updateToplist();
    echo "\n--------------------------\n".date("d.m.y G:i:s")." - calc_tournaments()\n";
    calc_tournaments();
    echo "\n--------------------------\n".date("d.m.y G:i:s")." - done.....\n";
    
    // Die Schleife sollte nun so lange schlafen, bis eine Minute vergangen ist. 
    $sleep = $sleepmax - (time() - $now);
  }
  else {
    echo "\n--------------------------\n".date("d.m.y G:i:s")." - Runde noch nicht gestartet.\n";
    
    // Nochmal schlafen legen
    $sleep = $start_time - $now > 180 ? 60 : $sleepmax;
  }
  
  
  
  
  // Falls der Thread mal länger als eine Minute gebraucht hat ($sleep wäre dann < 0)
  if ($sleep < $sleepmin) {
    $sleep = $sleepmin;

    if(SERVICE_SLEEP_MAX > 10) {
      // Vielleicht sollte man hier noch ein mail an den Admin generieren
      $txt = "Service '".__FILE__."' brauchte ".(time()-$now)." Sekunden. Maximum: ".(SERVICE_SLEEP_MAX-$sleepmin)." Sekunden!";
      if($pagetitle) {
        alarm($txt, "Service ".$pagetitle);
      }
      else {
        alarm($txt, "Service HW2.x");
      }
    }
  }
  echo "Sleeping $sleep Seconds\n";

  // xx Sekunden warten, schützt vor überhitzung ;-)
  sleep($sleep);
  
  echo "Awaken\n\n";
  
} // while(1)


?>
