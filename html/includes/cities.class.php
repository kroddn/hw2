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
 * Copyright (c) 2003-2007
 *
 * Markus Sinner
 * Gordon Meiser 
 * Stefan Neubert, Stefan Hasenstab
 *
 * This File must not be used without permission!
 ***************************************************/

include_once ("diplomacy.common.php");
include_once ("diplomacy.class.php");

class Cities {
  var $cities;
  var $activecity;
  var $activecityname;
  var $player;
  var $player_religion;
  var $city_religion;
  var $city_loyality;
  var $city_x;
  var $city_y;
  var $city_water;
  var $city_plains;
  var $city_forest;
  var $city_mountain;
  var $city_s1;
  var $city_s2;
  var $city_s3;
  var $city_s4;
  var $city_s5;
  var $city_s6;
  var $city_s7;
  var $city_s8;

  //-------------------- Konstruktor (Hauptstadt wird aktive Stadt)------------
  function Cities($input_playerid, $input_playerreligion) {
    $this->activecity = 0;
    $counter = 0;
    $this->player = intval($input_playerid);
    $this->player_religion = $input_playerreligion;
    $res1 = do_mysql_query("SELECT id, name, capital FROM city WHERE owner=".($this->player)." ORDER BY capital DESC, id ASC");
    // Keine Städte, also neu starten
    if (mysql_num_rows($res1) == 0) {
      restart_player($this->player);
      
      $res1 = do_mysql_query("SELECT id, name, capital FROM city WHERE owner=".($this->player)." ORDER BY capital DESC, id ASC");
      if (mysql_num_rows($res1) == 0) {
        die("<html><body>Ausnahmefehler: Keine Position mehr frei. Melden Sie sich bei einem Admin!</body></html>");
      }
    }
    while ($db_city = mysql_fetch_assoc($res1)) {
      $this->cities[$counter]['id'] = $db_city['id'];
      $this->cities[$counter]['name'] = $db_city['name'];
      $this->cities[$counter]['capital'] = $db_city['capital'];
      if ($db_city['capital'] == 1)
        $this->setActiveCity($db_city['id']);
      ++ $counter;
    }
    if ($this->activecity == 0)
      $this->setActiveCity($this->cities[0]['id']);
  }

  //------------------------------Functions--------------------------------------

  function abortBuilding($bid) {
    $bid = intval($bid);
    $res1 = do_mysql_query("SELECT gold,wood,stone,citybuilding_ordered.count AS count,building.name AS bname FROM building,citybuilding_ordered ".
                           " WHERE citybuilding_ordered.building=building.id AND citybuilding_ordered.city=".$this->activecity." AND citybuilding_ordered.bid=".$bid);
    if ($data = mysql_fetch_assoc($res1)) {
      $gold  = floor($data['gold']  * $data['count'] / 2);
      $wood  = floor($data['wood']  * $data['count'] / 2);
      $stone = floor($data['stone'] * $data['count'] / 2);
      do_mysql_query("DELETE FROM citybuilding_ordered WHERE bid=".$bid);
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) ".
                     "VALUES ('SERVER',".$this->player.",UNIX_TIMESTAMP(),'Abbruch: ".mysql_escape_string($data['bname'])." (".$data['count'].")','Der Bauauftrag in ".mysql_escape_string($this->activecityname)." wurde auf Euer Geheiß hin abgebrochen.\n\n<b>".mysql_escape_string($data['bname'])."</b>\nAnzahl: ".$data['count']."\n\nAbzüglich der Unkosten erhaltet Ihr ".$gold." Gold, ".$wood." Holz und ".$stone." Stein zurück.', 3)");
      do_mysql_query("UPDATE player SET gold=gold+".$gold.", wood=wood+".$wood.", stone=stone+".$stone.",cc_messages=1,cc_resources=1 ".
                     " WHERE id=".$this->player);
      // do_log("Buildingabort ordered: abortBuilding(".$bid.")");
    }
  }

  /**
   * Ausbildung abbrechen.
   * 
   * 
   * @param $uid
   * @return unknown_type
   */
  function abortUnit($uid) {
    $uid = intval($uid);
    // Die query ist so gestaltet, dass der Besitzer nur seine eigenen Truppen abbrechen kann.
    // Eine zusätzliche Abfrage nach "owner" ist also nicht nötig.
    $res1 = do_mysql_query("SELECT gold,shortrange,longrange,armor,horse,count,name,city ".
                           " FROM cityunit_ordered co LEFT JOIN unit u ON co.unit=u.id ".
                           "WHERE city=".$this->activecity." AND uid=".$uid);

    if ($data1 = mysql_fetch_assoc($res1)) {
      $gold = floor($data1['gold'] * $data1['count'] / 2);
      $sr = $data1['shortrange'] * $data1['count'];
      $lr = $data1['longrange'] * $data1['count'];
      $a = $data1['armor'] * $data1['count'];
      $h = $data1['horse'] * $data1['count'];
      
      //FIXME: Race-Condition anfällig. Besser: if affected rows == 1 
      do_mysql_query("DELETE FROM cityunit_ordered WHERE uid=".$uid);
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) ".
                     " VALUES ('SERVER',".$this->player.",UNIX_TIMESTAMP(),'Abbruch: ".$data1['name']." (".$data1['count'].")','Die Ausbildung in ".$this->activecityname." wurde auf Euer Geheiß hin abgebrochen.\n\n<b>".$data1['name']."</b>\nAnzahl: ".$data1['count']."\n\nAbzüglich der Unkosten erhaltet Ihr ".$gold." Gold, ".$sr." Nahkampfwaffen, ".$lr." Fernkampfwaffen, ".$a." Rüstungen und ".$h." Pferde zurück.',4)");
      do_mysql_query("UPDATE city SET population=population+".$data1['count'].", shortrange=shortrange+".$sr.", longrange=longrange+".$lr.", armor=armor+".$a.", horse=horse+".$h." WHERE id=".$data1['city']);
      do_mysql_query("UPDATE player SET gold=gold+".$gold.",cc_messages=1,cc_resources=1 WHERE id=".$this->player);
      
      return null;
    }
    else {
      return "Dieser Ausbildungstrupp existiert nicht.";
    }
   
  }

  
  function build($building, $count) {
    $count = intval($count);
    $building = intval($building);
    if ($count < 0) {
      return "Schon mal ".$count." Gebäude gebaut?";
    }
    
    $res2 = do_mysql_query("SELECT gold, wood, stone, time, makescapital FROM building WHERE id=".$building);
    $data2 = mysql_fetch_assoc($res2);
    
    // Nachschauen, ob bereits ein Hauptstadtgebäude in Bau.
    if($data2['makescapital']) {
      $test = do_mysql_query("SELECT name FROM citybuilding_ordered cbo LEFT JOIN city ON city.id=cbo.city ".
                             "WHERE building IN (SELECT id FROM building WHERE makescapital = 1) ".
                             "AND city IN (SELECT id FROM city WHERE owner = ".$this->player.")");
      
      if (mysql_num_rows($test) > 0) {
        $cityname = mysql_fetch_array($test);
        $error = "Ihr baut bereits ein Hauptstadtgebäude in ".$cityname['name'];
        return $error;
      }
    }
      
    // Darf das Gebäude theoretisch gebaut werden
    $pbld = $this->checkBuilding($building);
    if ($pbld >= $count) {
      // Sind genügend Ressourcen da
      $res1 = do_mysql_query("SELECT gold, wood, stone FROM player WHERE id=".$this->player);
      $data1 = mysql_fetch_assoc($res1);
      if ($data2) {
        if (($data1['gold'] >= $data2['gold'] * $count) && ($data1['wood'] >= $data2['wood'] * $count) && ($data1['stone'] >= $data2['stone'] * $count)) {
          // bauen
          do_mysql_query("UPDATE player SET gold=gold-".($data2['gold'] * $count).", wood=wood-".($data2['wood'] * $count).", stone=stone-".($data2['stone'] * $count).",cc_resources=1 WHERE id=".$this->player);
          do_mysql_query("INSERT INTO citybuilding_ordered (city,building,count,time) VALUES (".$this->activecity.",".$building.",".$count.",UNIX_TIMESTAMP()+".round($data2['time'] / BUILDINGSPEED).")");
        }
      }
    }
    
    return null;
  }

  function checkBuilding($bld) {
    $data = $this->getBuildings();
    for ($i = 0; $i < sizeof($data); ++ $i) {
      if ($data[$i]['id'] == $bld)
        return $data[$i]['possible'];
    }
  }

  function checkDestroyable($bld) {
    $data = $this->getBuildings();
    for ($i = 0; $i < sizeof($data); ++ $i) {
      if ($data[$i]['id'] == $bld)
        return $data[$i]['destroy'];
    }
  }

  function checkDestroyableUninvented($bld) {
    $data = $this->getuninventedBuildings();
    for ($i = 0; $i < sizeof($data); ++ $i) {
      if ($data[$i]['id'] == $bld)
        return $data[$i]['destroy'] = true;
    }
  }

  function checkUnit($unit) {
    $data = $this->getUnits();
    for ($i = 0; $i < sizeof($data); ++ $i) {
      if ($data[$i]['id'] == $unit)
        return $data[$i]['possible'];
    }
  }

  function getScoutTime($id) {
    $id = intval($id);
    $res_scout = do_mysql_query("SELECT max(res_scouttime) AS scouttime FROM building, citybuilding"." WHERE building.res_scouttime IS NOT NULL"." AND building.id = citybuilding.building "." AND citybuilding.city = ".$id);
    $data_scout = mysql_fetch_assoc($res_scout);

    if ($data_scout['scouttime'] > 0)
      return $data_scout['scouttime'];
    else
      return 0;
  }

  function underAttack($id) {
    $pid = $_SESSION['player']->getID();

    if (!$id || $id == "") die("Fehler underAttack: ID nicht gesetzt");
    if (!$pid || $pid == "") die ("Fehler underAttack: PID falsch");

    $scouttime = $this->getScoutTime($id);
    $armies = do_mysql_query("SELECT army.aid FROM army WHERE army.end = ".intval($id)." AND army.owner <> ".$pid."   AND army.owner NOT IN (SELECT id1 FROM relation WHERE id2 = ".$pid." AND type = 2 UNION SELECT id2 FROM relation WHERE id1 = ".$pid." AND type = 2 ) AND army.endtime <= UNIX_TIMESTAMP() + ".$scouttime );

    return mysql_num_rows($armies);

  }

  //Anzahl der sichtbaren Truppen die auf eine Stadt zumaschieren
  //Rückgabewert:
  // $ret[0] = feindliche
  // $ret[1] = neutrale
  // $ret[2] = verbündete
  function getCountVisibleArmys($cityid)
  {
    $ret[0] = 0;
    $ret[1] = 0;
    $ret[2] = 0;
    $cityid = intval($cityid);
    $res_city = do_mysql_query("SELECT x, y, owner, name FROM city LEFT JOIN map USING (id) WHERE city.id=".$cityid);

    // Keine Städte, keine Angriffe :-)
    if (mysql_num_rows($res_city) < 1) {
      return $ret;
    }
    
    $data_city = mysql_fetch_assoc($res_city);    
    $scouttime = $this->getScoutTime($cityid);

    //Falls es nicht unsere Stadt ist, scouttime runtersetzen da nachrichten von verbündeten eine gewisse zeit brauchen
    // FIXME: Wird dieser Code überhaupt gebraucht??? (by kroddn)
    if($data_city['owner'] != $_SESSION['player']->getID())
    {
      //nächste Stadt von uns aus finden
      $nearestcity = getNearestOwnCity($data_city['x'],$data_city['y'],$_SESSION['player']->getID());

      //laufzeit eines botens ermitteln
      $walktime =  computeWalktime($data_city['x'], $data_city['y'], $nearestcity[2], $nearestcity[3], SPEED_BOTE);
      
      //sofern 'n viertel der scouttime grösser ist als die laufzeit eines botens, dann die 4tel laufzeit nehmen
      //ist zwar unrealistisch, aber 'n bestimmte minimum scouttime ist besser
      if($scouttime*0.25 > $walktime)
        $scouttime *= 0.25;
      else
        $scouttime = $walktime;
    }

    $res_armies = do_mysql_query("SELECT army.aid, army.owner AS owner FROM army WHERE army.end = ".$cityid." AND army.owner <> ".$data_city['owner']." AND army.endtime <= UNIX_TIMESTAMP() + ".$scouttime);
    
    //alle armeen durchgehen und nach warrel sortieren
    while($data_armies = mysql_fetch_assoc($res_armies))
    {
      $ret[intval(getWarRel($data_city['owner'],$data_armies['owner']))] += 1;
    }
    return $ret;
  }

  //Anzahl der sichtbaren Truppen die auf den Spieler zumaschieren
  //Rückgabewert:
  // $ret[0] = feindliche
  // $ret[1] = neutrale
  // $ret[2] = verbündete
  function getArmyTopView()
  {
    $ret[0] = 0;
    $ret[1] = 0;
    $ret[2] = 0;
    $ret[3] = 0;
    $cit = $this->cities;
    foreach ($cit as $key=>$city) {
      $attsatcity = $this->getCountVisibleArmys($city['id']);
      $ret[0] += $attsatcity[0];
      $ret[1] += $attsatcity[1];
      $ret[2] += $attsatcity[2];
    }
    return $ret;
  }


  function checkBuildingsDestroyable() {
    if ($this->underAttack($this->getActiveCity())) {
      return "Es befinden sich Armeen im Anmarsch auf Eure Stadt, Sire. Alle Männer treffen Kriegsvorbereitung!";
    }
    
    $needloy = defined("DESTRUCT_LOYALITY") ? DESTRUCT_LOYALITY : 25;
    if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY && ($this->city_loyality < $needloy * 100) ) {
      return "Die öffentliche Ordnung ist noch nicht wiederhergestellt. Ihr könnt Gebäude erst ab einer Loyalität von ${needloy}% abreißen!";
    }
    
    // Gebäude können in der Tat abgerissen werden
    return null;
  }
  

  function destroyBuilding($bld, $count = 1) {
    $error = $this->checkBuildingsDestroyable();
    if($error != null) return $error;

    $bld = intval($bld);
    while($count>0 && $this->checkDestroyable($bld)) 
    {
      $count--;
      $res1 = do_mysql_query("SELECT building.id as id,gold,wood,stone,building.res_foodstorage as foodstorage, building.res_storage as storage, city.reserve_longrange as reslong, city.reserve_shortrange as resshort, city.reserve_armor as resarmor, city.reserve_horse as reshorse, building.name as bname,citybuilding.count AS count FROM building, citybuilding, city ".
                             " WHERE citybuilding.building=building.id AND city=".$this->activecity." AND citybuilding.building=".$bld);
      if ($data = mysql_fetch_assoc($res1)) {
        $gold = floor($data['gold'] / 2);
        $wood = floor($data['wood'] / 2);
        $stone = floor($data['stone'] / 2);
        if ($data['storage'] > 0) {
          $resX = do_mysql_query("SELECT sum( citybuilding.count * building.res_storage )  AS scndstorage, count( citybuilding.count )  AS count, sum( city.reserve_shortrange )  AS resshort, sum( city.reserve_longrange )  AS reslong, sum( city.reserve_armor )  AS resarmor, sum( city.reserve_horse )  AS reshorse, city.id AS id FROM city LEFT  JOIN citybuilding ON city.id = citybuilding.city LEFT  JOIN building ON building.id = citybuilding.building WHERE city.id =".$this->activecity." GROUP  BY id");
          $dataX = mysql_fetch_assoc($resX);
          // Lagergebäude
          if (($dataX['scndstorage'] - $data['storage']) < ($dataX['reslong'] / $dataX['count']) || ($dataX['scndstorage'] - $data['storage']) < ($dataX['resshort'] / $dataX['count']) || ($dataX['scndstorage'] - $data['storage']) < ($dataX['ressarmor'] / $dataX['count']) || ($dataX['scndstorage'] - $data['storage']) < ($dataX['resshorse'] / $dataX['count'])) {
            return "<b class=\"error\">Geb&auml;de wird benötigt!</b>";
          }
        }
        
        if ($data['count'] > 1) {
          do_mysql_query("UPDATE citybuilding SET count=count-1 WHERE building=".$data['id']." AND city=".$this->activecity);
        } 
        else {
          do_mysql_query("DELETE FROM citybuilding WHERE building=".$data['id']." AND city=".$this->activecity);
        }
        if ($data['storage'] > 0) {
          $res2 = do_mysql_query("SELECT sum( citybuilding.count * building.res_storage ) AS storage, sum( citybuilding.count * building.res_foodstorage ) AS foodstorage, city.id AS id, city.population AS pop, city.food AS food, city.religion AS religion, city.rawwood AS rawwood, city.rawiron AS rawiron, city.rawstone AS rawstone, city.shortrange AS shortrange, city.longrange AS longrange, city.armor AS armor, city.horse AS horse, city.reserve_shortrange AS reserve_shortrange, city.reserve_longrange AS reserve_longrange, city.reserve_armor AS reserve_armor, city.reserve_horse AS reserve_horse, city.max_shortrange AS max_shortrange, city.max_longrange AS max_longrange, city.max_armor AS max_armor, city.max_horse AS max_horse, city.populationlimit AS poplimit FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.id =".$this->activecity." GROUP BY id");
          $data2 = mysql_fetch_assoc($res2);
          $data2['storage'] = ($data2['storage'] > 0) ? $data2['storage'] : 0;
          // Lagerbestände dem Lagerplatz anpassen
          if ($data2['storage'] < $data2['shortrange']) {
            do_mysql_query("UPDATE city SET shortrange = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
          if ($data2['storage'] < $data2['longrange']) {
            do_mysql_query("UPDATE city SET longrange = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
          if ($data2['storage'] < $data2['armor']) {
            do_mysql_query("UPDATE city SET armor = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
          if ($data2['storage'] < $data2['horse']) {
            do_mysql_query("UPDATE city SET horse = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
        }
        else if ($data['foodstorage'] > 0) {
          $res2 = do_mysql_query("SELECT sum( citybuilding.count * building.res_foodstorage ) AS foodstorage, sum( citybuilding.count * building.res_foodstorage ) AS foodstorage, city.id AS id, city.population AS pop, city.food AS food, city.religion AS religion, city.rawwood AS rawwood, city.rawiron AS rawiron, city.rawstone AS rawstone, city.shortrange AS shortrange, city.longrange AS longrange, city.armor AS armor, city.horse AS horse, city.reserve_shortrange AS reserve_shortrange, city.reserve_longrange AS reserve_longrange, city.reserve_armor AS reserve_armor, city.reserve_horse AS reserve_horse, city.max_shortrange AS max_shortrange, city.max_longrange AS max_longrange, city.max_armor AS max_armor, city.max_horse AS max_horse, city.populationlimit AS poplimit FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.id =".$this->activecity." GROUP BY id");
          $data2 = mysql_fetch_assoc($res2);
          $res3 = do_mysql_query("SELECT food AS overallfood FROM city WHERE city.id =".$this->activecity);
          $data3 = mysql_fetch_assoc($res3);
          //echo "is food ".$data2['foodstorage']." < ".$data3['overallfood']." ->";
          if ($data2['foodstorage'] < $data3['overallfood']) {
            do_mysql_query("UPDATE city SET food = ". ($data2['foodstorage'] > 0 ? $data2['foodstorage'] : 0)." WHERE id = ".$this->activecity);
          }
        }

        do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.",UNIX_TIMESTAMP(),'Abriss: ".mysql_escape_string($data['bname'])."','Ein Gebäude in ".mysql_escape_string($this->activecityname)." wurde auf Euer Geheiß hin abgerissen.\n\n<b>".mysql_escape_string($data['bname'])."</b>\nAnzahl: 1\n\nAus der Verwertung der Bausubstanz und dem Verkauf der Einrichtung erhaltet Ihr ".$gold." Gold, ".$wood." Holz und ".$stone." Stein zurück.',3)");
        do_mysql_query("UPDATE player SET gold=gold+".$gold.", wood=wood+".$wood.", stone=stone+".$stone.",cc_messages=1,cc_resources=1 WHERE id=".$this->player);
      }
    } // while
    
    
    $this->updateCities();
    return null;
  }
  
  
  function destroyBuildingUninvented($bld) {
    $error = $this->checkBuildingsDestroyable();
    if($error != null) return $error;
    $bld = intval($bld);    
    $pdst = $this->checkDestroyableUninvented($bld);
    if ($pdst == true) {
      $res1 = do_mysql_query("SELECT building.id as id,gold,wood,stone,building.res_foodstorage as foodstorage, building.res_storage as storage, city.reserve_longrange as reslong, city.reserve_shortrange as resshort, city.reserve_armor as resarmor, city.reserve_horse as reshorse, building.name as bname,citybuilding.count AS count FROM building, citybuilding, city WHERE citybuilding.building=building.id AND city=".$this->activecity." AND citybuilding.building=".$bld);
      if ($data = mysql_fetch_assoc($res1)) {
        $gold = floor($data['gold'] / 4);
        $wood = floor($data['wood'] / 4);
        $stone = floor($data['stone'] / 4);
        if ($data['storage'] > 0) {
          $resX = do_mysql_query("SELECT sum( citybuilding.count * building.res_storage )  AS scndstorage, count( citybuilding.count )  AS count, sum( city.reserve_shortrange )  AS resshort, sum( city.reserve_longrange )  AS reslong, sum( city.reserve_armor )  AS resarmor, sum( city.reserve_horse )  AS reshorse, city.id AS id FROM city LEFT  JOIN citybuilding ON city.id = citybuilding.city LEFT  JOIN building ON building.id = citybuilding.building WHERE city.id =".$this->activecity." GROUP  BY id");
          $dataX = mysql_fetch_assoc($resX);
          if (($dataX['scndstorage'] - $data['storage']) < ($dataX['reslong'] / $dataX['count']) || ($dataX['scndstorage'] - $data['storage']) < ($dataX['resshort'] / $dataX['count']) || ($dataX['scndstorage'] - $data['storage']) < ($dataX['ressarmor'] / $dataX['count']) || ($dataX['scndstorage'] - $data['storage']) < ($dataX['resshorse'] / $dataX['count'])) {
            return "<b class=\"error\">Geb&auml;de wird benötigt!</b>";
          }
        }
        if ($data['count'] > 1) {
          do_mysql_query("UPDATE citybuilding SET count=count-1 WHERE building=".$data['id']." AND city=".$this->activecity);
        } else {
          do_mysql_query("DELETE FROM citybuilding WHERE building=".$data['id']." AND city=".$this->activecity);
        }
        if ($data['storage'] > 0) {
          $res2 = do_mysql_query("SELECT sum( citybuilding.count * building.res_storage ) AS storage, sum( citybuilding.count * building.res_foodstorage ) AS foodstorage, city.id AS id, city.population AS pop, city.food AS food, city.religion AS religion, city.rawwood AS rawwood, city.rawiron AS rawiron, city.rawstone AS rawstone, city.shortrange AS shortrange, city.longrange AS longrange, city.armor AS armor, city.horse AS horse, city.reserve_shortrange AS reserve_shortrange, city.reserve_longrange AS reserve_longrange, city.reserve_armor AS reserve_armor, city.reserve_horse AS reserve_horse, city.max_shortrange AS max_shortrange, city.max_longrange AS max_longrange, city.max_armor AS max_armor, city.max_horse AS max_horse, city.populationlimit AS poplimit FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.id =".$this->activecity." GROUP BY id");
          $data2 = mysql_fetch_assoc($res2);
          $data2['storage'] = ($data2['storage'] > 0) ? $data2['storage'] : 0;
          if ($data2['storage'] < $data2['shortrange']) {
            do_mysql_query("UPDATE city SET shortrange = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
          if ($data2['storage'] < $data2['longrange']) {
            do_mysql_query("UPDATE city SET longrange = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
          if ($data2['storage'] < $data2['armor']) {
            do_mysql_query("UPDATE city SET armor = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
          if ($data2['storage'] < $data2['horse']) {
            do_mysql_query("UPDATE city SET horse = ".$data2['storage']." WHERE id = ".$this->activecity);
          }
        }
        else if ($data['foodstorage'] > 0) {
          $res2 = do_mysql_query("SELECT sum( citybuilding.count * building.res_foodstorage ) AS foodstorage, sum( citybuilding.count * building.res_foodstorage ) AS foodstorage, city.id AS id, city.population AS pop, city.food AS food, city.religion AS religion, city.rawwood AS rawwood, city.rawiron AS rawiron, city.rawstone AS rawstone, city.shortrange AS shortrange, city.longrange AS longrange, city.armor AS armor, city.horse AS horse, city.reserve_shortrange AS reserve_shortrange, city.reserve_longrange AS reserve_longrange, city.reserve_armor AS reserve_armor, city.reserve_horse AS reserve_horse, city.max_shortrange AS max_shortrange, city.max_longrange AS max_longrange, city.max_armor AS max_armor, city.max_horse AS max_horse, city.populationlimit AS poplimit FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.id =".$this->activecity." GROUP BY id");
          $data2 = mysql_fetch_assoc($res2);
          $res3 = do_mysql_query("SELECT food AS overallfood FROM city WHERE city.id =".$this->activecity);
          $data3 = mysql_fetch_assoc($res3);
          //echo "is food ".$data2['foodstorage']." < ".$data3['overallfood']." ->";
          if ($data2['foodstorage'] < $data3['overallfood']) {
            do_mysql_query("UPDATE city SET food = ". ($data2['foodstorage'] > 0 ? $data2['foodstorage'] : 0)." WHERE id = ".$this->activecity);
          }
        }

        do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.",UNIX_TIMESTAMP(),'Abriss: ".$data['bname']."','Ein von Euch noch unerforschtes oder ein religionsfremdes Gebäude in ".$this->activecityname." wurde auf Euer Geheiß hin abgerissen.\n\n<b>".$data['bname']."</b>\nAnzahl: 1\n\nAus der Verwertung der Bausubstanz und dem Verkauf der Einrichtung erhaltet Ihr ".$gold." Gold, ".$wood." Holz und ".$stone." Stein zurück.',3)");
        do_mysql_query("UPDATE player SET gold=gold+".$gold.", wood=wood+".$wood.", stone=stone+".$stone.",cc_messages=1,cc_resources=1 WHERE id=".$this->player);
      }
    }
    $this->updateCities();
    return null;
  }

  function getActiveCity() {
    return $this->activecity;
  }

  function getACReligion() {
    return $this->city_religion;
  }

  function getACName() {
    return $this->activecityname;
  }

  /**
   * Loyalität der aktuellen Stadt. Achtung: Returniert Hundertstel Prozent!
   *
   * @return loyality
   */
  function getACLoyality() {
    
    return $this->city_loyality;
  }
  
  function getBuildings() {
    getMapSize($fx, $fy);
    
    
    $res5 = do_mysql_query("SELECT gold,wood,stone FROM player WHERE id=".$this->player);

    // hack -> wurde stadt gerade erobert können die gebäude dennoch abgerissen werden.
    // wird hier verhindert
    $resA = do_mysql_query("SELECT owner FROM city WHERE id=".$this->activecity);
    $dataA = mysql_fetch_assoc($resA);
    if ($dataA['owner'] != $this->player) {
      echo "<b class=\"error\">Dies ist nicht mehr Eure Stadt. Bitte loggt Euch erneut ein!</b><p>";
      exit;
    }

    $data = mysql_fetch_assoc($res5);
    $res1 = do_mysql_query("SELECT building, count, type, typelevel FROM citybuilding LEFT JOIN building ON citybuilding.building=building.id WHERE city=".$this->activecity);
    while ($def1 = mysql_fetch_assoc($res1)) {
      $bld1[$def1['building']] += $def1['count'];
      $typ1[$def1['type']][0] += $def1['count'];
      $typ1[$def1['type']][$def1['typelevel']] += $def1['count'];
    }
    $res2 = do_mysql_query("SELECT building, count, type, typelevel FROM citybuilding_ordered LEFT JOIN building ON citybuilding_ordered.building=building.id WHERE city=".$this->activecity);
    while ($def2 = mysql_fetch_assoc($res2)) {
      $bld2[$def2['building']] += $def2['count'];
      $typ2[$def2['type']][0] += $def2['count'];
      $typ2[$def2['type']][$def2['typelevel']] += $def2['count'];
    }
    // maxy wird in Promille Angeben. Standard ist 500 oder -500
    $sql = sprintf("SELECT round(maxy*%d/1000) as maxy,id,religion,name,gold,wood,stone,".
                   "       req_fields,req_type,req_special,req_research,type,typelevel,time,category,makescapital,lib_link ".
                   " FROM building LEFT JOIN playerresearch ON building.req_research = playerresearch.research ".
                   " WHERE %s playerresearch.player=%d ORDER BY category, id", 
                   $fy, (defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) ? "" : "(religion IS  NULL OR religion=".$this->city_religion.") AND", $this->player);
                   
    $res4 = do_mysql_query($sql);
    $count = 0;
    while ($bld = mysql_fetch_array($res4)) {
      // Darf Gebäude in diesem Gebiet gebaut werden?
      if($bld['maxy'] != NULL && 
	      (
	       // Wenn maxy größer 0 ist, dann muß die koordinate größergleich maxy sein
	       // andernfalls ist sie kleiner maxy.
	       $bld['maxy'] > 0 && $this->city_y <  $bld['maxy']  ||
	       $bld['maxy'] < 0 && $this->city_y >= -$bld['maxy']  
	      )
	     ) {
        continue;
      }
      
      // DEBUG - FIXME: $$ richtig?
      // echo "\n\n<!-- ".$$bld['maxy']." -->\n\n";


      $b[$count]['maxy'] = $bld['maxy'];
      $b[$count]['makescapital'] = $bld['makescapital'];
      $b[$count]['id'] = $bld['id'];
      $b[$count]['name'] = $bld['name'];
      $b[$count]['religion'] = $bld['religion'];
      $b[$count]['gold'] = $bld['gold'];
      $b[$count]['wood'] = $bld['wood'];
      $b[$count]['stone'] = $bld['stone'];
      $b[$count]['time'] = $bld['time'];
      $b[$count]['category'] = $bld['category'];
      $b[$count]['isbuild'] = 0 + $bld1[$bld['id']];
      $b[$count]['inbuild'] = 0 + $bld2[$bld['id']];
      $b[$count]['lib'] = $bld['lib_link'];


      $newloy = defined("ENABLE_LOYALITY") && ENABLE_LOYALITY;
      /**
       * Religionsspezifische Gebäude dürfen gebaut werden, wenn
       * a) Neues Loy-System: Besitzer hat gleiche Religion wie Gebäude
       * b) Altes Konv-Sys: Besitzer hat gleiche Religion wie Stadt
       */
      if (  $bld['religion'] != NULL && 
            ($newloy && $bld['religion'] != $this->player_religion ||
             !$newloy && $this->city_religion != $this->player_religion )
            )
        {
          $b[$count]['possible'] = 0;
        }
      else if (($bld['req_type'] != NULL) && ($typ1[$bld['req_type']][0] == 0)) {
        $b[$count]['possible'] = 0;
      }
      elseif (($bld['req_special'] != NULL) && ($bld1[$bld['req_special']] == 0)) {
        $b[$count]['possible'] = 0;
      } 
      else {
        switch ($bld['req_fields']) {
        case "none" :
          {
            // unendlich viele
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = 999;
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            break;
          }
        case "city" :
          {
            // 1 - Anzahl schon existierender Gebäude des Typs - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = 1 - $typ1[$bld['type']][0] - $typ2[$bld['type']][$bld['typelevel']];
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            break;
          }
        case "plains" :
          {
            // Anz Wiesen - Anzahl schon existierender Gebäude des Typs - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = $this->city_plains - $typ1[$bld['type']][0] - $typ2[$bld['type']][$bld['typelevel']];
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            break;
          }
        case "forest" :
          {
            // Anz Wälder - Anzahl schon existierender Gebäude des Typs - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = $this->city_forest - $typ1[$bld['type']][0] - $typ2[$bld['type']][$bld['typelevel']];
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            break;
          }
        case "mountain" :
          {
            // Anz Gebirge - Anzahl schon existierender Gebäude des Typs - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = $this->city_mountain - $typ1[$bld['type']][0] - $typ2[$bld['type']][$bld['typelevel']];
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            break;
          }
        case "water" :
          {
            // Anz Wasserflächen - Anzahl schon existierender Gebäude des Typs - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = $this->city_water - $typ1[$bld['type']][0] - $typ2[$bld['type']][$bld['typelevel']];
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            break;
          }
        case "s1" :
        case "s2" :
        case "s3" :
        case "s4" :
        case "s5" :
        case "s6" :
        case "s7" :
        case "s8" :
          {
            $nameres = "city_".$bld['req_fields'];
            // Anz Wasserflächen - Anzahl schon existierender Gebäude des Typs - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            if ($bld['typelevel'] == 1)
              $b[$count]['possible'] = $this-> $nameres - $typ1[$bld['type']][0] - $typ2[$bld['type']][$bld['typelevel']];
            // Anzahl Vorgebäude (Stufe -1) - Anzahl schon in Bau befindlicher Gebäude gleicher Stufe
            else
              $b[$count]['possible'] = $typ1[$bld['type']][$bld['typelevel'] - 1] - $typ2[$bld['type']][$bld['typelevel']];
            
          }
          break;
        // FIXME: default für Fehler-Abfangen?
        }
      } // else
      

      if ($bld['gold'] > 0)
        $actp[0] = $data['gold'] / $bld['gold'];
      else
        $actp[0] = 999999;
      if ($bld['wood'] > 0)
        $actp[1] = $data['wood'] / $bld['wood'];
      else
        $actp[1] = 999999;
      if ($bld['stone'] > 0)
        $actp[2] = $data['stone'] / $bld['stone'];
      else
        $actp[2] = 999999;
      $b[$count]['actpossible'] = min(floor(min($actp)), $b[$count]['possible']);
      if (($b[$count]['isbuild'] > 0) && ($typ1[$bld['type']][$bld['typelevel']] - $typ2[$bld['type']][$bld['typelevel'] + 1] > 0))
        $b[$count]['destroy'] = true;
      else
        $b[$count]['destroy'] = false;
      if (($b[$count]['possible'] > 0) || ($b[$count]['isbuild'] > 0) || ($b[$count]['inbuild'] > 0))
        $b[$count]['show'] = true;


      ++ $count;
    }
    return $b;
  }

  function getCities() {
    return $this->cities;
  }

  function getCityData() {
    // Sicherheitshalber diese Abfrage
    if ($this->activecity == 0) {
      show_log_fatal_error("<b>activecity [".$_SESSION['player']->getID()."]</b> nicht gesetzt. Bitte diesen Fehler einem Administrator melden!");
    }

    $res1 = do_mysql_query("SELECT id,population,populationlimit FROM city WHERE id=".$this->activecity);

    $data1 = mysql_fetch_assoc($res1);
    $d['population'] = $data1['population'];
    if ($data1['populationlimit'] == NULL)
      $d['populationlimit'] = "-------";
    else
      $d['populationlimit'] = $data1['populationlimit'];
    $d['apopulation'] = max(0, $data1['population'] - 100);
    $d['name'] = $this->activecityname;
    $d['id'] = $data1['id'];

    return $d;
  }


  /**
   * Einheiten in der Stadt zurückliefern.
   * Sorgt dafür, dass nur die Einheiten des Spielers zurückgeliefert werden.
   *
   * @return Einen Array Array
   *         $units[x][0] -> ID
   *         $units[x][1] -> Menge der stationierten
   *         $units[x][2] -> Geschwindigkeit
   */
  function getCityUnits($id = null) {
    $id = $id == null ? $this->activecity : intval($id);
    $res1 = mysql_query("SELECT unit, count, unit.speed,religion,type,level,name FROM cityunit, unit WHERE cityunit.city='".$id."' AND cityunit.owner='".$this->player."' AND unit.id = cityunit.unit ORDER BY unit");
    $i = 1;
    while ($data1 = mysql_fetch_assoc($res1)) {
      $data[$i][0] = $data1['unit'];
      $data[$i][1] = $data1['count'];
      $data[$i][2] = $data1['speed'];
      $data[$i]['type']     = $data1['type'];
      $data[$i]['religion'] = $data1['religion'];
      $data[$i]['level']    = $data1['level'];
      $data[$i]['name']     = $data1['name'];
      $i ++;
    }
    return $data;
  }
  
  function getInBuild() {
    $res1 = do_mysql_query("SELECT bid,count,citybuilding_ordered.time AS time,name FROM citybuilding_ordered,building WHERE building.id=citybuilding_ordered.building AND city=".$this->activecity." ORDER BY time");
    $count = 0;
    while ($data = mysql_fetch_assoc($res1)) {
      $ib[$count]['bid'] = $data['bid'];
      $ib[$count]['name'] = $data['name'];
      $ib[$count]['count'] = $data['count'];
      $ib[$count]['time'] = $data['time'];
      ++ $count;
    }
    return $ib;
  }

  function getInRecruit() {
    $res1 = do_mysql_query("SELECT uid,count,cityunit_ordered.time AS time,name FROM cityunit_ordered,unit WHERE unit.id=cityunit_ordered.unit AND city=".$this->activecity." ORDER BY time");
    $count = 0;
    while ($data = mysql_fetch_assoc($res1)) {
      $ir[$count]['uid'] = $data['uid'];
      $ir[$count]['name'] = $data['name'];
      $ir[$count]['count'] = $data['count'];
      $ir[$count]['time'] = $data['time'];
      ++ $count;
    }
    return $ir;
  }

  function getUnits() {
    $this->setActiveCity($this->activecity);

    $count = 0;
    $lvl0 =  $lvl1 =  $lvl2 =  $lvl3 = 0;

    $res1 = do_mysql_query("SELECT id, type, name, gold, shortrange, longrange, armor, horse, time, cost, level, unit.religion as religion, player, research FROM unit, playerresearch WHERE (religion=".$this->player_religion." OR religion IS NULL) AND req_research = research AND player = '".$this->player."'");
    $res2 = do_mysql_query("SELECT sum(count*res_training1) AS t1, sum(count*res_training2) AS t2, sum(count*res_training3) AS t3,city.shortrange AS shortrange, city.longrange AS longrange, city.armor AS armor, city.horse AS horse, population FROM  building,citybuilding,city WHERE citybuilding.city=".$this->activecity." AND citybuilding.building=building.id AND citybuilding.city=city.id GROUP by city.id");
    $res3 = do_mysql_query("SELECT gold FROM player WHERE id=".$this->player);
    $data2 = mysql_fetch_assoc($res2);
    $data3 = mysql_fetch_assoc($res3);

    // Bereits in Ausbildung befindliche Einheiten
    $res4 = do_mysql_query("SELECT count,level FROM unit, cityunit_ordered WHERE unit.id=cityunit_ordered.unit AND city=".$this->activecity);
    while ($data4 = mysql_fetch_assoc($res4)) {
      switch ($data4['level']) {
        case 0 :
          {
            $lvl0 += $data4['count'];
            break;
          }
        case 1 :
          {
            $lvl1 += $data4['count'];
            break;
          }
        case 2 :
          {
            $lvl2 += $data4['count'];
            break;
          }
        case 3 :
          {
            $lvl3 += $data4['count'];
            break;
          }
      }
    }


    // FIXME! Hier ist der Wurm drin, manchmal kann man mehr Einheiten ausbilden, als eigentlich
    // Kapazität da ist...

    // maximal mögliche einheiten von level1,level2,level3
    // Für Level1-Truppen ist die Rechnung leicht. Alle verfügbaren Kapazitäten in Betracht ziehen
    $level1 = $data2['t1'] + $data2['t2'] + $data2['t3'] - $lvl3 - $lvl2 - $lvl1 - $lvl0;
    $level0 = $level1; // level0 sind ja nur Bauern/Milizen, gleichbehandlet mit Lvl1

    $level2 = $data2['t2'] + $data2['t3'] - $lvl3 - $lvl2 -   max(0, $lvl1 - $data2['t1'])   -   max(0, $lvl0 - $data2['t1']);
    //$level2 = $data2['t2'] + $data2['t3'] - $lvl3 - $lvl2 -   max(0, $lvl1+$lvl0 - $data2['t1']);
    $level3 = $data2['t3'] - $lvl3  -   max(0, $lvl2 - $data2['t2'])   -   max(0, $lvl1 - $data2['t1'])  -  max(0, $lvl0 - $data2['t1']) ;
    //$level3 = $data2['t3'] - $lvl3  -   max(0, $lvl2 - $data2['t2'])   -   max(0, $lvl1+$lvl0 - $data2['t1']);


    while ($data1 = mysql_fetch_assoc($res1)) {
      $u[$count]['id'] = $data1['id'];
      $u[$count]['name'] = $data1['name'];
      $u[$count]['level'] = $data1['level'];
      $u[$count]['religion'] = $data1['religion'];
      $u[$count]['type'] = $data1['type'];
         
      if ( defined("ENABLE_LOYALITY") && ENABLE_LOYALITY ) {
        $u[$count]['gold'] = $data1['gold'] * getLoyalityCostFactor( round($this->city_loyality/100) );
      }
      else { 
        $u[$count]['gold'] = $data1['gold'];
      }
      
      $u[$count]['shortrange'] = $data1['shortrange'];
      $u[$count]['longrange'] = $data1['longrange'];
      $u[$count]['armor'] = $data1['armor'];
      $u[$count]['horse'] = $data1['horse'];
      $u[$count]['time'] = $data1['time'];
      $u[$count]['cost'] = $data1['cost'];      
      
      $lvl = "level".$data1['level'];
      if ($data1['shortrange'] <> 0)
        $pre1 = floor($data2['shortrange'] / $data1['shortrange']);
      else
        $pre1 = 1000000;
      if ($data1['longrange'] <> 0)
        $pre2 = floor($data2['longrange'] / $data1['longrange']);
      else
        $pre2 = 1000000;
      if ($data1['armor'] <> 0)        
      
        $pre3 = floor($data2['armor'] / $data1['armor']);
      else
        $pre3 = 1000000;
      if ($data1['horse'] <> 0)
        $pre4 = floor($data2['horse'] / $data1['horse']);
      else
        $pre4 = 1000000;        
        
      //											    						  <level>              <gold>						<min-einwohner>      	  <waffen,rüstungen,pferde>

      $u[$count]['maxpossible'] = $$lvl;
      
      
      // Ausbildung beim neuen Loyalitäts-System IMMER möglich
      if ( defined("ENABLE_LOYALITY") && ENABLE_LOYALITY || $this->player_religion == $this->city_religion)
        $u[$count]['possible'] = min($$lvl, floor( $data3['gold']  / $u[$count]['gold']), max(0, $data2['population'] - 100), $pre1, $pre2, $pre3, $pre4);
      else
        $u[$count]['possible'] = 0;
      
      ++ $count;
    }
    return $u;
  }
  

  function getWeapons() {
    $res1 = do_mysql_query("SELECT shortrange,longrange,armor,horse,max_shortrange,max_longrange,max_armor,max_horse,reserve_shortrange,reserve_longrange,reserve_armor,reserve_horse FROM city WHERE id=".$this->activecity);
    $data1 = mysql_fetch_array($res1);
    $res2 = do_mysql_query("SELECT sum(count * res_storage) AS storagelimit, sum(count * res_shortrange) AS resshort, sum(count * res_longrange) AS reslong, sum(count * res_armor) AS resarmor, sum(count * res_horse) AS reshorse FROM building LEFT JOIN citybuilding ON citybuilding.building = building.id WHERE city = ".$this->activecity);
    $data2 = mysql_fetch_array($res2);
    // FIXME: intval benutzen statt dieses komische +0 ?
    $w['shortrange']         = $data1['shortrange'] + 0;
    $w['longrange']          = $data1['longrange'] + 0;
    $w['armor']              = $data1['armor'] + 0;
    $w['horse']              = $data1['horse'] + 0;
    $w['max_shortrange']     = $data1['max_shortrange'] + 0;
    $w['max_longrange']      = $data1['max_longrange'] + 0;
    $w['max_armor']          = $data1['max_armor'] + 0;
    $w['max_horse']          = $data1['max_horse'] + 0;
    $w['storagelimit']       = $data2['storagelimit'] + 0;
    $w['resshort']           = $data2['resshort'] + 0;
    $w['reslong']            = $data2['reslong'] + 0;
    $w['resarmor']           = $data2['resarmor'] + 0;
    $w['reshorse']           = $data2['reshorse'] + 0;
    $w['reserve_shortrange'] = $data1['reserve_shortrange'];
    $w['reserve_longrange']  = $data1['reserve_longrange'];
    $w['reserve_armor']      = $data1['reserve_armor'];
    $w['reserve_horse']      = $data1['reserve_horse'];
    return $w;
  }

  function recruit($unit, $count) {
    $unit  = intval($unit);
    $count = intval($count);
    if ($count <= 0) {
      return "Schon mal ".$count." Einheiten ausgebildet?";
    }
    
    // Darf die Einheit theoretisch gebaut werden
    $punit = $this->checkUnit($unit);
    
    if ($punit >= $count) {
      // Sind genügend Ressourcen da
      $res1 = do_mysql_query("SELECT gold, shortrange, longrange, armor, horse, time FROM unit WHERE id=".$unit);
      if ($data1 = mysql_fetch_assoc($res1)) {
        if ( defined("ENABLE_LOYALITY") && ENABLE_LOYALITY ) {
          $this->setActiveCity($this->activecity);
          $cost = $data1['gold'] * $count * getLoyalityCostFactor( round($this->city_loyality/100) );
        }
        else {
          $cost = $data1['gold'] * $count;
        }
        do_mysql_query("UPDATE player SET gold=gold-".$cost.",cc_resources=1 WHERE id=".$this->player);
        do_mysql_query("UPDATE city SET population=population-".$count.", shortrange=shortrange-".$data1['shortrange'] * $count.", longrange=longrange-".$data1['longrange'] * $count.", armor=armor-".$data1['armor'] * $count.", horse=horse-".$data1['horse'] * $count." WHERE id=".$this->activecity);
        $time = $data1['time'];
        if (defined("RECRUITSPEED")) $time = round($time / RECRUITSPEED);
        do_mysql_query("INSERT INTO cityunit_ordered (city,unit,count,time) VALUES (".$this->activecity.",".$unit.",".$count.",UNIX_TIMESTAMP()+".$time.")");
      }
    }
  }

  function setActiveCity($input_city) {
    if (!isset ($input_city) || !$input_city)
      $input_city = $this->activecity;

    $res1 = do_mysql_query("SELECT x, y, religion, loyality, name FROM city,map WHERE city.id=map.id AND city.id=".$input_city." AND owner=".$this->player);
    if (mysql_num_rows($res1) > 0) {
      $city = mysql_fetch_assoc($res1);
      $this->activecity = $input_city;
      $this->activecityname = $city['name'];
      $this->city_x = $city['x'];
      $this->city_y = $city['y'];
      $this->city_religion = $city['religion'];
      $this->city_loyality = $city['loyality'];
      $this->city_water = 0;
      $this->city_plains = 0;
      $this->city_mountain = 0;
      $this->city_forest = 0;
      $this->city_s1 = 0;
      $this->city_s2 = 0;
      $this->city_s3 = 0;
      $this->city_s4 = 0;
      $this->city_s5 = 0;
      $this->city_s6 = 0;
      $this->city_s7 = 0;
      $this->city_s8 = 0;
      $res2 = do_mysql_query("SELECT m1.type,m1.special FROM map AS m1, map AS m2 WHERE m2.id =".$input_city." AND m1.x >= m2.x - 2 AND m1.x <= m2.x + 2 AND m1.y >= m2.y - 2 AND m1.y <= m2.y + 2 AND ( m1.id <> m2.id )");
      while ($field = mysql_fetch_assoc($res2)) {
        switch ($field['type']) {
          case 1 :
            ++ $this->city_water;
            break;
          case 2 :
            ++ $this->city_plains;
            break;
          case 3 :
            ++ $this->city_forest;
            break;
          case 4 :
            ++ $this->city_mountain;
            break;
        }
        switch ($field['special']) {
          case 1 :
            ++ $this->city_s1;
            break;
          case 2 :
            ++ $this->city_s2;
            break;
          case 3 :
            ++ $this->city_s3;
            break;
          case 4 :
            ++ $this->city_s4;
            break;
          case 5 :
            ++ $this->city_s5;
            break;
          case 6 :
            ++ $this->city_s6;
            break;
          case 7 :
            ++ $this->city_s7;
            break;
          case 8 :
            ++ $this->city_s8;
            break;
        }
      }
    }
    else {
      echo "<b class=\"error\">Dies ist nicht mehr Eure Stadt. Bitte loggt Euch erneut ein!</b><br><br>";
      exit;
    }
  }

  
  function settle($x, $y, $s, $sg) {
    $stl = true;
    $x = intval($x);
    $y = intval($y);
    
    $ackerbau = do_mysql_query("SELECT research FROM playerresearch WHERE player=".$_SESSION['player']->getID()." AND research=5");
    if(mysql_num_rows($ackerbau) == 0) {
      return "Ihr müßt zunächst Ackerbau erforschen.";
    }

    getMapSize($fx, $fy);

    if ($x < 2 || $y < 2 || $x >= $fx-2 || $y >= $fy-2) {
      return "x- und y-Koordinaten m&uuml;ssen zwischen 2 und ".($fx-2)." liegen!";
    }

    if ($this->player_religion != $this->city_religion)
      return "Die Ungläubigen sind nicht bereit, für Euch zu siedeln. Konvertiert zuerst diese Stadt (im Rathaus).";
      
    $this->setActiveCity($this->activecity);
      
    if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
      // Loyalität kleiner als XX % -> Siedler verweigern

      $min_settle_loy = 75;
      if ($this->city_loyality < $min_settle_loy * 100) {
        return "Die Einwohner sind noch nicht loyal genug, um sich für Euch in solch ein gefährliches Unterfangen zu stürzen. Ihr benötigt mindestens ".$min_settle_loy."% Loyalität.";
      }
    }

    if(!checkSettle($x, $y, $this->player))
      return 'Dieses Feld <a href="map.php?gox='.$x.'&goy='.$y.'">('.$x.':'.$y.')</a> wird bereits besiedelt!';     

    if ( ($err = checkSettleCoords($x, $y)) != null )
      return "Koordinaten nicht gültig: $err";

    // Überprüfen, ob das Ziel ausserhalb des besiedelbaren Bereiches liegt.
      
    if ($this->underAttack($this->activecity))
      return "MyLord! Die Siedler verweigern aufgrund erh&ouml;hter kriegerischer Vorkommnisse in der Region den Marschbefehl!";

    $cd = $this->getCityData();
    $s = intval($s);
    if (($cd['apopulation'] < $s) || ($s < 50))
      return "Nicht genügend Einwohner in der Stadt oder nicht genügend Siedler ausgewählt (mindestens 50).";

    // Sind genügend Ressourcen da um einen Bauernhof zu bauen
    //nur einen Bauernhof 'mitnehmen'
    $building = 1; // Bauernhof
    $bcount = 1;
    $res1 = do_mysql_query("SELECT gold, wood, stone FROM player WHERE id=".$this->player);
    $res2 = do_mysql_query("SELECT gold, wood, stone, time FROM building WHERE id=".$building);
    $data1 = mysql_fetch_assoc($res1);
    if ($cost = mysql_fetch_assoc($res2)) {
      if( $data1['gold'] >= $cost['gold'] * $bcount && $data1['wood'] >= $cost['wood'] * $bcount && $data1['stone'] >= $cost['stone'] * $bcount ) {
        // Res werden später abgezogen
      }
      else
        return "Ihnen fehlen die Mittel um die Siedler ausreichend auszustatten! Ihr benötigt genug Resourcen für ein Bauerngut (".($cost['gold'] * $bcount)." Gold, ".($cost['wood'] * $bcount)." Holz und ".($cost['stone'] * $bcount)." Stein).";
    }

    // FIXME: kann man das nicht durch $cities->getCityUnits() ersetzen?
    $res1 = do_mysql_query("SELECT unit,count,speed FROM cityunit LEFT JOIN unit ON unit.id = cityunit.unit WHERE city=".$this->activecity." AND owner=".$this->player);
    while ($data1 = mysql_fetch_assoc($res1)) {
      $g[$data1['unit']]['count'] = $data1['count'];
      $g[$data1['unit']]['speed'] = $data1['speed'];
    }
    
    $speed = 3;
    $unitcount=0;
    if ($sg != NULL) {
      foreach ($sg as $key => $value) {
        if(intval($value) < 0) {
          return "Schummler! Schonmal ne Armee mit $value Einheiten befehligt?";
        }
        if(intval($value) == 0) continue;        
        
        if($speed > $g[$key]['speed']) {
            $speed = $g[$key]['speed'];
            echo "<!-- Speed $speed -->\n";
        }
        
        if( intval($value) > $g[$key]['count'] )
        {
          $unitcount += intval($g[$key]['count']);
        } 
        else {
          $unitcount += intval($value);
        }
      }
    }
 
    // Darf der Spieler überhaupt hierhin siedeln?
    if(defined("START_POS_NEW") && START_POS_NEW) {
      if( intval($x/40) != intval($this->city_x/40) || intval($y/40) != intval($this->city_y/40) ) {
        $needcount = round(sqrt(($x - $this->city_x)*($x - $this->city_x) + ($y - $this->city_y)*($y - $this->city_y)));
        // Nicht im Heimatsektor. Nachschauen, ob genug Begleitschutz mitgesendet wurde.
        if($unitcount < $needcount) {
          return "Ihr könnt Euren Heimatsektor nicht ohne ausreichend Geleitschutz verlassen (mindestens $needcount beliebige Einheiten, Aushebung in der <a href=\"barracks.php\">Kaserne</a>)!";
        }
      
        // Ist das Ziel im Siedlungsgebiet?
        $msg = isInSettleArea($x, $y);
        if($msg != null)
        return $msg;
      }
    }

    // Unit-Speed muß
    $wt = computeWalktime($x, $y, $this->city_x, $this->city_y, $speed, $unitcount);

    do_mysql_query("UPDATE player SET gold=gold-".$cost['gold'] * $bcount.", wood=wood-".$cost['wood'] * $bcount.", stone=stone-".$cost['stone'] * $bcount.",cc_resources=1 WHERE id=".$this->player);

    $res2 = do_mysql_query("SELECT id FROM map WHERE x=".$x." AND y=".$y);
    $data2 = mysql_fetch_assoc($res2);
    do_mysql_query("UPDATE city SET population=population-".$s." WHERE id=".$this->activecity);
    $curr_time = time();
    do_mysql_query("INSERT INTO army (owner,start,end,starttime,endtime,mission,missiondata) ".
                   " VALUES (".$this->player.",".$this->activecity.",".$data2['id'].", UNIX_TIMESTAMP(), UNIX_TIMESTAMP() +".$wt.",'settle',".$s.")");
    $army = mysql_insert_id();

    // Dasselbe nochmal loggen
    do_mysql_query("INSERT INTO log_army (id, owner,start,end,time,endtime,mission,missiondata) VALUES (".$army.",".$this->player.",".$this->activecity.",".$data2['id'].",".$curr_time.",". ($curr_time + $wt).",'settle',".$s.")");

    if ($sg != NULL) {
      foreach ($sg as $key => $value) {
        if (intval($value) > 0) {
          if (intval($value) == $g[$key]['count']) {
            do_mysql_query("DELETE FROM cityunit WHERE unit=".$key." AND city=".$this->activecity." AND owner=".$this->player);
          } 
          else {
            do_mysql_query("UPDATE cityunit SET count=count-".intval($value)." WHERE unit=".$key." AND city=".$this->activecity." AND owner=".$this->player);
          }
          do_mysql_query("INSErt INTO armyunit VALUES (".$army.",".$key.",".intval($value).")");

          // Und wieder loggen ;-)
          do_mysql_query("INSERT INTO log_armyunit VALUES (".$army.",".$key.",".intval($value).")");
        }
      }
    }


    return null;
  }


  function checkValidCity($x, $y) {
    // FIXME: falls eine Stadt auf einem Feld != 2 sitzt dann gibts hier probleme
    $res1 = @mysql_query("SELECT city.id FROM city, map WHERE map.type = 2 AND map.id = city.id AND x = ".intval($x)." AND y = ".intval($y));
    if (@mysql_num_rows($res1))
      return true;
    else
      return false;
  }

  function checkUnitCount($array) {
    foreach ($array as $key => $value) {
      if (!is_int($value) || $value < 0)
        return FALSE;
    }
  }

  /**
   * Vorbedingungen von Angriff und Truppenverlegung prüfen.
   * @param $data1 Ist Ein- und Ausgabevariable!
   */
  function checkAttackMove($x, $y, $from, &$data1) {
    $x = intval($x);
    $y = intval($y);
    
    if ($_SESSION['player']->getGold() <= 0) {
      return "Ihnen fehlen die Mittel, einen Angriff zu starten (kein Gold).";
    }

    
    if (getSiegeTime($this->activecity) > 0) {
      return "Die Stadt steht unter Belagerung. Es ist lediglich ein Ausfall möglich.";
    }

    if (!$this->checkValidCity($x, $y))
      return "Ziel ungültig.";
      
      
    // $data1 is Ein- und Ausgabevariable!!!
    $res = do_mysql_query("SELECT city.owner, city.population as pop, map.x, map.y, ".
                           "      attackblock-UNIX_TIMESTAMP() AS rest, attackblock > UNIX_TIMESTAMP() AS isblock".
                           " FROM city LEFT JOIN player ON city.owner=player.id LEFT JOIN map ON map.id = city.id".
                           " WHERE city.id = ".$from);
    
    if (!($check = mysql_fetch_assoc($res)) || !$check['owner']  ) {
      return "Ausgangsstadt ungültig";
    }
    
    
    // check if blocked
    if ($check['isblock']) {
      $diff = $check['rest'];
      if ($diff >= 82800) {
        $in = "23 Stunden und ".date("i", $diff)." Minuten";
      }
      else {
        $in = date("H:i", $diff)." Stunden";
      }
      return "Ihre Truppen verweigern aufgrund der Bitte Ihrer Dorfbewohner den Marschbefehl.<br/>Die neu gegr&uuml;ndete Stadt muss noch mindestens ".$in." bewacht werden!";
    }
    
    // $data1 is Ein- und Ausgabevariable!!!
    $res1 = do_mysql_query("SELECT player.nooblevel, player.holiday, map.id,city.owner,city.population as pop ".                      
                           " FROM map LEFT JOIN city USING(id) LEFT JOIN player ON city.owner=player.id ".
                           " WHERE x=".$x." AND y=".$y);
    
    if (!($data1 = mysql_fetch_assoc($res1)) || !$data1['owner'] ) {
      return "Zielstadt ungültig";
    }
    
    if ($data1['owner'] < 10) {
       return "Admin-Städte können nicht angegriffen werden.";
    }
    
    
    // Keine Truppenverlegungen zu Noobs erlauben
    if ($data1['owner'] != $this->player) {
      // Verbiete Angriffe aus dem Siedlungsradius, falls das konfiguriert ist.
      if(defined("DISABLE_SETTLEAREA_ATTACK") && DISABLE_SETTLEAREA_ATTACK) {
        // Ist Ausgangsstadt Siedlungsgebiet?
        $msg = isInSettleArea($data1['x'], $data1['y']);
        if($msg != null) {
          return $msg."<br>Angriffe aus dem Siedlungsring heraus sind nicht erlaubt. Siedelt zuerst nach innen oder wartet auf die Erweiterung des Radius.";
        }
      }


      if ($data1['nooblevel'] > 0) {
        return "Der Spieler befindet sich noch im Neulingsschutz. Es sind keine Truppenbewegungen dorthin erlaubt.";
      }
      
      // Im Urlaub keine Truppenbewegungen dorthin erlauben
      if ($data1['holiday'] > time() ) {
        $err = "Der Spieler befindet sich im Urlaubsmodus.";
        show_error($err);
        return $err;
      }
      
      if(defined("START_POS_NEW") && START_POS_NEW) {
        $resB = do_mysql_query("SELECT x,y FROM map WHERE id= ".$from);
        $dataB = mysql_fetch_assoc($resB);
        $tox = $dataB['x'];
        $toy = $dataB['y'];
        
        // Ausserhalb des aktuellen Sektors darf nicht in den Siedlungsbereich angegriffen werden
        if( intval($x/40) != intval($tox/40) || intval($y/40) != intval($toy/40)) {
          $mesg = isInSettleArea($x, $y);

          if($mesg != null)
            return $mesg;
        }
      }
      
      if ($_SESSION['player']->isMultihunter() && !$_SESSION['player']->isAdmin()) {
        $status_res = do_mysql_query("SELECT status FROM player WHERE id=".$data1['owner']);
        if (($status = mysql_fetch_assoc($status_res)) &&
             $status['status'] == 2 ) {
          return "Multihunter können keine Gesperrten angreifen bzw. Truppen verlegen.";
        }
      }
    } // if ($data1['owner'] != $this->player)
    
    return null;
  } // function
  
  
  
  function move($x, $y, $u, $from, $player, $tactic) {
    $tactic = intval($tactic);
    $from = intval($from);
    $x = intval($x);
    $y = intval($y);
    $stl = true;

    $error = $this->checkAttackMove($x, $y, $from, $data1);
    if($error != null) return $error;
    

    if (!isset ($data1['owner'])) {
      $error = "data1['owner'] NULL in ".__FILE__.":".__LINE__;
      show_log_fatal_error($error, $error);
    }

    if ($data1['owner'] != $this->player) {
      //Sind Spieler verbündet?
      if (getWarRel($data1['owner'],$this->player) != 2) {
        return "Diese Stadt gehört Euch nicht oder Ihr habt kein Bündnis mit dem Besitzer dieser Stadt.";
      }
    } // if ($data1['owner'] != $this->player)

    $unitcount = 0;
    $cu = $this->getCityUnits($from);
    if ($stl == true) {
      $speed = 10;
      foreach ($cu as $unit) {
        if (intval($u[$unit[0]]) < 0) {
          return "Tsts, MyLord... Ihr wolltet Einheiten losschicken, deren dimensionale Existenz nicht gewährleistet ist.";
        }
        
        if (intval($u[$unit[0]]) > 0) {
          // Einheit zur gesamtzahl dazuzählen
          $unitcount+=$u[$unit[0]];

          if ($speed > $unit[2]) {
            $speed = $unit[2];
          }
        }
      }

      if($unitcount <= 0) return "Keine Einheiten ausgewählt.";
      
      $resB = do_mysql_query("SELECT x,y FROM map WHERE id= ".$from);
      $dataB = mysql_fetch_assoc($resB);
      $wt = computeWalktime($x, $y, $dataB['x'], $dataB['y'], $speed, $unitcount);

      do_mysql_query("INSERT INTO army (owner,start,end,starttime,endtime,mission,missiondata,tactic) VALUES ( '".$this->player."', '".$from."', '".$data1['id']."', '".time()."', '". (time() + $wt)."', 'move', 'NULL', '".$tactic."')");
      $aid = mysql_insert_id();
      $k = 1;
      foreach ($u as $key => $value) {
        if ($cu[$k][0] == $key && $value > 0) {
          if ($cu[$k][1] < $value) {
            $value = $cu[$k][1];
          }
          
          
          do_mysql_query("INSERT IntO armyunit VALUES ('".$aid."', '".intval($key)."', '".intval($value)."')");
          if ($cu[$k][1] > $value) {
            do_mysql_query("UPDATE cityunit SET count = (count - ".intval($value).") WHERE owner = ".$this->player." AND unit = ".intval($key)." AND city = ".$from);
          }
          else {
            do_mysql_query("DELETE FROM cityunit WHERE owner = ".$this->player." AND  city = ".$from." AND unit = ".intval($key));
          }
          $sumunits += $value;
        }
        $k ++;
      }

      if ($sumunits <= 0) {
        do_mysql_query("DELETE FROM army WHERE aid=".$aid);
        return;
      }
    }
  }


  function attack($x, $y, $u, $from, $player, $tactic, $type) {
    $tactic = intval($tactic);
    $from = intval($from);
    $x = intval($x);
    $y = intval($y);
    $stl = true;

    
    if (!in_array($type, array ("attack", "burndown", "despoil", "siege")))
      return "Unbekannter Missionstyp.";
      
    $error = $this->checkAttackMove($x, $y, $from, $data1);
    if($error != null) return $error;
    
    if($this->player == $data1['owner'])
      return "Ihr werdet Euch doch wohl nicht selbst angreifen wollen? Benutzt den Missionstyp &quot;Verlegen&quot;.";
    
    $rel = getWarRel($this->player, $data1['owner']);
    if ($rel == 2) {
      //Bündnisse müssen manuell entfernt werden
      return "Ihr habt mit eurem Gegner ein Bündnis. Dies müßt Ihr vorher kündigen.";
    }
    if($rel == 1) {
      $err = $_SESSION['diplomacy']->changeRelation(resolvePlayerName($data1['owner']), 0);
      if ($err != null) {
        return $err;
      }
      else {
        show_error("KRIEG!".$clanrel." X ".$rel." X ".resolvePlayerName($data1['owner']));
      }
    }

    $cu = $this->getCityUnits($from);

    $speed = 10;
    $unitcount = 0;
    foreach($cu as $unit) {
      if(intval($u[$unit[0]]) < 0) {
        return "Tsts, MyLord... Ihr wolltet Einheiten losschicken, deren dimensionale Existenz nicht gewährleistet ist.";
      }
      
      if (intval($u[$unit[0]]) > 0) {
        // Anpassung, falls der User mehr eingegeben hat, als in der Stadt vorhanden
        if(intval($u[$unit[0]]) > $unit[1])
          $u[$unit[0]] = $unit[1];
            
        // Einheit zur gesamtzahl dazuzählen
        $unitcount += $u[$unit[0]];

        if ($speed > $unit[2]) {
          $speed = $unit[2];
        }
      }
    }

    if($unitcount <= 0) return "Keine Einheiten ausgewählt.";
    
    $siege_armysize = round($data1['pop'] / 1000) * 50 + 50;
    if ($type == 'siege' && $unitcount < $siege_armysize) {
      return "Sire, diese Hand voll Soldaten reicht nicht, um die Zielstadt belagern zu können. Schickt mindestens ".$siege_armysize." Mann!";
    }

    $resB = do_mysql_query("SELECT x,y FROM map WHERE id= ".$from);
    $dataB = mysql_fetch_assoc($resB);
    $wt = computeWalktime($x, $y, $dataB['x'], $dataB['y'], $speed, $unitcount);

    do_mysql_query("INSERT INTO army (owner,start,end,starttime,endtime,mission,missiondata,tactic) VALUES ( '".$this->player."', '".$from."', '".$data1['id']."', UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + ".$wt.", '".$type."', 'NULL', '".$tactic."')");
    $aid = mysql_insert_id();


    $k = 1;
    foreach ($u as $key => $value) {
      if (($cu[$k][0] == $key) && ($value > 0)) {
        if ($cu[$k][1] < $value) {
          $value = $cu[$k][1];
        }
        do_mysql_query("InsERT INTO armyunit VALUES ('".$aid."', '".intval($key)."', '".intval($value)."')");
        if ($cu[$k][1] > $value) {
          do_mysql_query("UPDATE cityunit SET count = (count - ".intval($value).") WHERE owner = ".$this->player." AND  unit = ".intval($key)." AND city = ".$from);
        }
        else {
          do_mysql_query("DELETE FROM cityunit WHERE owner = ".$this->player." AND  city = ".$from." AND unit = ".$key);
        }
        $sumunits += $value;  
      }
      $k ++;
    }

    if ($sumunits <= 0) {
      do_mysql_query("DELETE FROM army WHERE aid=".$aid);
      return;
    }

    //time equal starttime from army
    do_mysql_query("INSERT INTO log_army (id,owner,start,end,time,endtime,mission,missiondata,tactic) VALUES (".$aid.",'".$this->player."', '".$from."', '".$data1['id']."', '".time()."', '". (time() + $wt)."', '".$type."', 'NULL', '".$tactic."')");
    $k = 1;
    foreach ($u as $key => $value) {
      if ($cu[$k][0] == $key && $value > 0) {
        if ($cu[$k][1] >= $value) {
          do_mysql_query("INSERT INTO log_armyunit(aid,unit,count) VALUES ('".$aid."', '".$key."', '".$value."')");
        }
      }
      $k++;
    }
    
    return null;
  }

  function updateCities() {
    $counter = 0;
    $check = false;
    unset ($this->cities);
    
    
    $res1 = do_mysql_query("SELECT id, name, capital FROM city WHERE owner=".$this->player." ORDER BY capital DESC, id ASC");
    if (mysql_num_rows($res1) == 0) {      
      restart_player($this->player);

      // FIXME: hier vielleicht ein return, damit keine Race-Conditions
      // mit anderen Skripten auftreten?
    }
      
    while ($db_city = mysql_fetch_assoc($res1)) {
      $this->cities[$counter]['id'] = $db_city['id'];
      $this->cities[$counter]['name'] = $db_city['name'];
      $this->cities[$counter]['capital'] = $db_city['capital'];
      if ($db_city['id'] == $this->activecity)
        $check = true;
      ++ $counter;
    }
    if (!$check) {
      $this->setActiveCity($this->cities[0]['id']);
    }
  }

  function updateCityName($cityname) {
    $cityname = trim($cityname);

    // Bei gleichem Namen nichts tun
    if(strcasecmp($this->activecityname, $cityname) == 0) return null;

    // Dafür sorgen, dass kein Unfug eingegeben wird
    if (!checkBez($cityname) ) {
      return "Der gewählte Stadtname ist zu kurz, zu lang, verstößt gegen die Namenskonventionen oder enthält doppelte Leerzeichen.";
    }

    $res1 = do_mysql_query("SELECT id FROM city WHERE name LIKE '".$cityname."'");
    if(mysql_num_rows($res1) == 0 ) {
      do_mysql_query("UPDATE city SET name='".$cityname."' WHERE id=".$this->activecity);
      do_mysql_query("UPDATE player SET cc_towns=1 WHERE id=".$this->player);
      $this->activecityname = $cityname;
      $this->updateCities();
      return null;
    }
    else
      {
        return "Eine Stadt mit diesem Namen existiert bereits.";
      }
  }

  function updatePopulationlimit($poplimit) {
    if (($poplimit < 100) || ($poplimit > 99999))
      $poplimit = "NULL";
    do_mysql_query("UPDATE city SET populationlimit=".$poplimit." WHERE id=".$this->activecity);
  }
  function updateWeapons($max_shortrange, $max_longrange, $max_armor, $max_horse) {
    $wp = $this->getWeapons();
    do_mysql_query("UPDATE city SET max_shortrange=".min($wp['storagelimit'], $max_shortrange).", max_longrange=".min($wp['storagelimit'], $max_longrange).", max_armor=".min($wp['storagelimit'], $max_armor).", max_horse=".min($wp['storagelimit'], $max_horse)." WHERE id=".$this->activecity);
    //mysql_query("UPDATE city SET max_shortrange=".$max_shortrange.", max_longrange=".$max_longrange.", max_armor=".$max_armor.", max_horse=".$max_horse." WHERE id=".$this->activecity) or die(mysql_error());
  }

  
  function getUnitName($id) {
    $res = do_mysql_query("SELECT name FROM unit WHERE id = ".$id);
    $data = mysql_fetch_assoc($res);
    return $data['name'];
  }


  /**
   * Stadt convertieren.
   *
   */
  function convert($ratio) {
    $convertcost = CONVERT_COST;
    
    if ($this->underAttack($this->activecity)) {
      return "Diese Stadt wird angegriffen, Ihr könnt sie nicht konvertieren";
    }
    if($this->player_religion == $this->city_religion) {
      return "Diese Stadt hat bereits Eure Religion";
    }
    
    $amount  = 0;
    $message = sprintf('Die Stadt [b]%s[/b] wurde friedlich konvertiert, es kam zu keinen Unruhen.', $this->activecityname);
    
    // NEUE Konvertierung
    if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
      // Konvertierungssperre
      $next = 3600*24*28; // 28 Tage

      
      $sql = sprintf("SELECT max(time) + %d AS next, max(time) + %d > UNIX_TIMESTAMP() AS notallowed ".
                     " FROM log_convert WHERE city = %d AND player = %d", 
                     $next, $next, $this->activecity, $this->player);
      $max = do_mysql_query_fetch_assoc($sql);
      if($max && $max['notallowed']) {
        return sprintf("Ihr könnt erst wieder am %s konvertieren.",  date("d.m.Y H:i", $max['next']) );
      }

      $sql = sprintf("SELECT population AS pop,round(loyality/100) AS loy FROM city WHERE id = %d", 
                     $this->activecity);                     
      $citz = do_mysql_query_fetch_assoc($sql);

      
      // Wieviel Truppen gibts?
      $bonusfactor = 2.5;

      if($citz['loy'] > 75) {
        $bonusfactor += ($citz['loy']-75) / 10;
      }
      
      // Bonustruppen
      $amount = max(0, round($citz['pop'] * $bonusfactor / 100));

      // Die Konvertierung der Stadt durchführen
      // Dieses Update greift nur DANN, wenn die Religion != Spieler-Religion
      $sql = sprintf("UPDATE city SET religion = %d, population=population-%d WHERE id = %d AND religion <> %d", 
                     $this->player_religion, $amount, $this->activecity, $this->player_religion);
      do_mysql_query($sql);
      if (mysql_affected_rows() == 0) {
        return "Stadt besitzt bereits Eure Religion oder gehört Euch nicht";
      }
      

      if($amount > 0) {
        // Eine Level-5 Einheit für den Spieler auswählen
        $sql = sprintf("SELECT u.id,count,name,religion,level,type ".
                       " FROM unit u LEFT JOIN cityunit cu ON (u.id=cu.unit AND cu.city=%d AND cu.owner=%d) ".
                       " WHERE religion = %d AND level=5 ORDER BY rand() LIMIT 1", 
                       $this->activecity, $this->player, $this->player_religion);
        
        $uid = do_mysql_query_fetch_assoc($sql);


        // Level-5 Einheiten als Bonus einfügen
        if($uid['count'] && $uid['count']>0) {
          $sql = sprintf("UPDATE cityunit SET count=count+%d WHERE city=%d AND unit=%d AND owner=%d",
                         $amount, $this->activecity, $uid['id'], $this->player );
          do_mysql_query($sql);
        }
        else {
          $sql = sprintf("INSERT INTO cityunit (city,unit,count,owner) VALUES (%d,%d,%d,%d)",
                         $this->activecity, $uid['id'], $amount, $this->player);
          do_mysql_query($sql);
        }
        //echo $sql."\n";
        
        $img = getUnitImage($uid);
        $message .= "\n\n";
        $message .= sprintf("Eurer Ruf eilt Euch vorraus, und die Kunde über Eure Taten im heiligen Land verbreiten sich geschwind. ".
                            "Um Eure Mission gegen die Ungläubigen zur unterstützen, ".
                            "haben sich [b]%d %s[/b] Eurer Armee in dieser Stadt angeschlossen.", $amount, $uid['name']);       
      } // if($amount > 0) 
      
      
      $sql = sprintf("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER', %d, UNIX_TIMESTAMP(), 'Konvertierung von <b>%s</b> erfolgreich', '%s', 4)",
                     $this->player, $this->activecityname, mysql_escape_string($message) );
      do_mysql_query($sql);                     
      do_mysql_query("UPDATE player SET cc_messages=1, cc_towns=1, cc_resources=1 WHERE id=".$this->player);
                 
      $sql = sprintf("INSERT INTO log_convert (time, city, player, loyality, bonus) VALUES (UNIX_TIMESTAMP(), %d, %d, %d, %d)",
                     $this->activecity, $this->player, $citz['loy'], $amount);
      do_mysql_query($sql);

      // Stadt aktualisieren
      $this->setActiveCity($this->activecity);      
      return null;
    } // if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
    
    
    // ALTE Konvertierung, läuft nur, wenn !ENABLE_LOYALITY
    $ratio = intval($ratio);
   
    if ($ratio < 0)
    $ratio = 0;
    if ($ratio > 100)
    $ratio = 100;

    $res1 = do_mysql_query("SELECT population FROM city WHERE id=".$this->activecity);
    $res2 = do_mysql_query("SELECT gold FROM player WHERE id=".$this->player);
    if (($data1 = mysql_fetch_assoc($res1)) && ($data2 = mysql_fetch_assoc($res2))) {
      if ($data2['gold'] < floor($data1['population'] / 100 * $ratio * $convertcost)) {
        return "Zu wenig Gold";
      }
      do_mysql_query("UPDATE player SET gold=gold-". (floor($data1['population'] / 100 * $ratio) * $convertcost)." WHERE id=".$this->player);

      do_mysql_query("DELETE FROM citybuilding_ordered WHERE city=".$this->activecity);
      $relbld = do_mysql_query("SELECT citybuilding.building AS bld FROM citybuilding,building WHERE citybuilding.building=building.id AND building.religion IS NOT NULL AND citybuilding.city=".$this->activecity);
      while ($relblddata = mysql_fetch_assoc($relbld)) {
        do_mysql_query("DELETE FROM citybuilding WHERE city=".$this->activecity." AND building=".$relblddata['bld']);
      }

      if ($ratio < 100) {
        $cit_fight = floor($data1['population'] * (1 - $ratio / 100));

        $res3 = do_mysql_query("SELECT unit,count FROM cityunit WHERE city=".$this->activecity." AND owner=".$this->player);
        $i = 0;
        while ($data3 = mysql_fetch_assoc($res3)) {
          $at[$i]['id'] = $data3['unit'];
          $at[$i]['count'] = $data3['count'];
          $at[$i]['player'] = $this->player;
          $i ++;
        }

        if ($cit_fight > 0) {
          $towndef[0]['id'] = DORFBEWOHNER_ID;
          $towndef[0]['count'] = $cit_fight;
          $towndef[0]['player'] = 0;
        }

        include_once ("includes/fight.func.php");
        $erg = fight($at, $towndef, 0, 0);
        if ($erg) {
          //success
          if ($erg[0]['player'] == $this->player) {
            do_mysql_query("DELETE FROM cityunit WHERE city = ".$this->activecity." AND owner=".$this->player);
            for ($i = 0; $i < sizeof($erg); $i ++) {
              do_mysql_query("INSERT INTO cityunit (city,unit,count,owner) VALUES ('".$this->activecity."','".$erg[$i]['id']."','".$erg[$i]['count']."','".$this->player."')");
            }

            $msg = "Die Stadt ".$this->activecityname." wurde erfolgreich konvertiert. Die Aktion hat ". (floor($data1['population'] / 100 * $ratio) * $convertcost)." Gold gekostet, von ".$data1['population']." Einwohnern haben ". ($data1['population'] - $cit_fight)." überlebt.\n\n<b>Folgende Einheiten überlebten den Kampf:</b>\n";
            for ($i = 0; $i < sizeof($erg); $i ++) {
              $ures = do_mysql_query("SELECT name FROM unit WHERE id = '".$erg[$i]['id']."'");
              $udata = mysql_fetch_assoc($ures);
              $msg .= $udata['name'].": ".$erg[$i]['count']."\n";
            }
            do_mysql_query("UPDATE city SET population=". (max(1,$data1['population'] - $cit_fight)).",religion=".$this->player_religion." WHERE id=".$this->activecity);
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.",".time().",'Konvertierung: ".$this->activecityname." erfolgreich','".$msg."',4)");
            //failure
          } else {
            do_mysql_query("DELETE FROM cityunit WHERE city = ".$this->activecity." AND owner=".$this->player);

            $res4 = do_mysql_query("SELECT sum(count) AS numbld FROM citybuilding WHERE city=".$this->activecity);
            $data4 = mysql_fetch_assoc($res4);
            $res5 = do_mysql_query("SELECT building, count FROM citybuilding WHERE city=".$this->activecity." ORDER BY RAND()");
            $destory = $data4['numbld'] / 4;
            while ($data5 = mysql_fetch_assoc($res5)) {
              if ($destory > 0) {
                if ($data5['count'] > 0) {
                  do_mysql_query("UPDATE citybuilding SET count=". (floor($data5['count'] / 2))." WHERE city=".$this->activecity." AND building=".$data5['building']);
                  $destroy -= floor($data5['count'] / 2);
                }
                else {
                  do_mysql_query("DELETE FROM citybuilding WHERE city=".$this->activecity." AND building=".$data5['building']);
                  $destroy --;
                }
              }
            }
            do_mysql_query("DELETE FROM citybuilding WHERE city=".$this->activecity." AND count=0");
            do_mysql_query("UPDATE city SET population=".intval($erg[0]['count'])." WHERE id=".$this->activecity);
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.", UNIX_TIMESTAMP(),'Konvertierung: ".$this->activecityname." fehlgeschlagen','Eure Truppen wurden von den Dorfbewohnern aus ".$this->activecityname." besiegt. Zahlreiche Gebäude wurden bei den Unruhen zerstört, die Aktion hat ". (floor($data1['population'] / 100 * $ratio) * $convertcost)." Gold gekostet. Von ".$data1['population']." Einwohnern haben ".intval($erg[0]['count'])." überlebt.',4)");
          }
        }
        else {
          do_mysql_query("DELETE FROM cityunit WHERE city = ".$this->activecity." AND owner=".$this->player);
          do_mysql_query("UPDATE city SET religion=".$this->player_religion." WHERE id=".$this->activecity);
          do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.", UNIX_TIMESTAMP(),'Konvertierung: ".$this->activecityname." erfolgreich','Die Stadt ".$this->activecityname." wurde erfolgreich konvertiert. Die Aktion hat ". (floor($data1['population'] / 100 * $ratio) * $convertcost)." Gold gekostet, von ".$data1['population']." Einwohnern hat kein einziger überlebt.\n\n<b>Alle Truppen wurden vernichtet</b>',4)");
        }
      }
      else {
        do_mysql_query("UPDATE city SET religion=".$this->player_religion." WHERE id=".$this->activecity);
        do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.", UNIX_TIMESTAMP(),'Konvertierung: ".$this->activecityname." erfolgreich','Die Stadt ".$this->activecityname." wurde friedlich konvertiert, es kam zu keinen Unruhen. Die Aktion hat ". (floor($data1['population'] / 100 * $ratio) * $convertcost)." Gold gekostet',4)");
      }
    }

    $this->setActiveCity($this->activecity);

    do_mysql_query("UPDATE player SET cc_messages=1, cc_towns=1, cc_resources=1 WHERE id=".$this->player);
  } // convert


  /**
   * Eine fremde stationierte Einheite zurücksenden.
   * 
   * @param $unit
   * @param $owner
   * @return null bei erfolg, sonst einen String mit einer Fehlermeldung
   */
  function sendBack($unit, $owner) {
    if (getSiegeTime($this->activecity) >= 0) {
      return "Die Stadt steht unter Belagerung. Die Herzöge weigern sich, die Stadt schutzlos zu lassen.";
    }
    
    $owner = intval($owner);
    $unit  = intval($unit);
    
    $sql = sprintf("SELECT cityunit.*, speed, name ".
				   " FROM cityunit LEFT JOIN unit ON unit.id = cityunit.unit".
				   " WHERE city = %d AND unit = %d AND owner = %d",
                   $this->getActiveCity(),
                   $unit,
                   $owner);
                   
    $res = do_mysql_query($sql);
    if(mysql_num_rows($res) == 1) {
      $cu = mysql_fetch_assoc($res);
      $noc = getNearestOwnCity($this->city_x, $this->city_y, $owner);
      $cid = $noc[0];
      $to_res = do_mysql_query("SELECT name FROM city WHERE id= ".$cid);
      $to = mysql_fetch_assoc($to_res);
      //printf("SendBack nach %s <a href='map.php?gox=%d&goy=%d'>%d:%d</a><br>\n", $to['name'], $noc[2], $noc[3], $noc[2], $noc[3]);
      
      $wt = computeWalktime($this->city_x, $this->city_y, $noc[2], $noc[3], $cu['speed']);

      //printf("Speed: %d Walktime: %d<br>\n", $cu['speed'], $wt);
      $sql = sprintf("INSERT INTO army (owner,start,end,starttime,endtime,mission) VALUES ".
				     "(%d, %d, %d, %s, %s, %s)",
                     $owner, $noc[0], $noc[0], "UNIX_TIMESTAMP()", "UNIX_TIMESTAMP() + ".$wt, "'return'"
                     );
      do_mysql_query($sql);
      //printf("%s<br>", $sql);
      $aid = mysql_insert_id();
      $sql = sprintf("INSERT INTO armyunit (aid, unit, count) VALUES (%d, %d, %d)",
                     $aid, $unit, $cu['count']);
      do_mysql_query($sql);
      //printf("%s<br>", $sql);
      $sql = sprintf("DELETE FROM cityunit WHERE owner = %d AND city = %s AND unit = %d",
                     $owner, $this->activecity, $unit);
      do_mysql_query($sql);
      //printf("%s<br>", $sql);
      
      // Nachricht an den Spieler schicken
      $body = sprintf("Eure Einheit [b]%s[/b] in [b]%s[/b] (bestehend aus %d Mann) wurde vom Stadtbesitzer [b]%s[/b] freigestellt und ist nun auf dem Rückweg in Eure Stadt [b]%s[/b].",
                      $cu['name'], $this->activecityname, $cu['count'], $_SESSION['player']->getName(), $to['name'] );
                      
      $sql = sprintf("INSERT INTO message (recipient, date, header, body, category) ".
					 " VALUES (%d, UNIX_TIMESTAMP(), 'Einheit aus <b>%s</b> zurückgesendet', '%s', 4)",
                     $owner, $this->activecityname, mysql_escape_string($body) );
      //printf("%s<br>", $sql);                     
      do_mysql_query($sql);
      do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$owner);
      
      // OK
      return null;
    }
    else {
      return "Diese Einheit könnt Ihr nicht befehligen.";
    }
  }
  
  
  /**
   * Truppen entlassen. 
   * 
   * @param $from  ID der Stadt. Kann auch die ID einer verbündeten Stadt enthalten.
   * @param $u     Array der zu entlassenden Truppen
   * @return null bei Erfolg, ansonsten einen String mit einer Fehlermeldung
   */
  function disarmCityUnits($from, $u) {
    $from = intval($from);

    if (getSiegeTime($from) >= 0) {
      return "Die Stadt steht unter Belagerung. Die Herzöge weigern sich, die Stadt schutzlos zu lassen.";     
    }

    // Die stationieren Einheiten der Stadt holen
    // Hier werden nur die Einheiten des aktuellen Spielers zurückgeliefert
    $cu = $this->getCityUnits($from);

    //var_dump($u);

    $k = 1;
    foreach ($u as $key => $value) {
      if ($cu[$k][0] == $key && $value > 0) {
        if ($cu[$k][1] > $value) {          
          do_mysql_query("UPDATE cityunit SET count = (count - ".intval($value).") WHERE unit = ".intval($key)." AND city = ".$from." AND owner=".$this->player);
        }
        else {
          $value = $cu[$k][1];
          do_mysql_query("DELETE FROM cityunit WHERE city = ".$from." AND unit = ".$key." AND owner=".$this->player);        
        }
        
        // Logging
        $sql = sprintf("INSERT INTO log_disarm (time,player,city,unit,count) VALUES (UNIX_TIMESTAMP(), %d, %d, %d, %d)",
                       $this->player, $from, $key, $value);
        do_mysql_query($sql);
      }
      $k ++;
    }
  }
  
  /**
   * Unerforschte Gebäude anzeigen
   * 
   * @return unknown_type
   */
  function getuninventedBuildings() {
    # Erforschte Gebäude ermitteln
    $invented = do_mysql_query("SELECT id, name FROM building, playerresearch WHERE (building.req_research = playerresearch.research) AND playerresearch.player=".$this->player." ORDER BY id");
    $count = 0;
    while ($get_invented = mysql_fetch_array($invented)) {
      $bld[$count]['id'] = $get_invented['id'];
      ++ $count;
    }
    # Alle Gebäude der aktuellen Stadt ermitteln
    $existing = do_mysql_query("SELECT cb.building,religion FROM citybuilding cb LEFT JOIN building b ON b.id=cb.building ".
                               " WHERE city=".$this->activecity." AND count>0 ORDER BY cb.building");
    $count2 = 0;
    while ($get_existing = mysql_fetch_array($existing)) {
      $bld2[$count2]['building'] = $get_existing['building'];
      $bld2[$count2]['religion'] = $get_existing['religion'];
      ++ $count2;
    }
    # Gebäude vergleichen
    for ($i = 0; $i <= sizeof($bld) - 1; ++ $i) {
      for ($j = 0; $j <= sizeof($bld2) - 1; ++ $j) {
        if ($bld2[$j]['building'] == $bld[$i]['id']) {
          $bld2[$j]['building'] = -1;
        }
      }
    }
    $count = 0;
    for ($i = 0; $i <= sizeof($bld2) - 1; ++ $i) {
      if ($bld2[$i]['building'] > 0) {
//         Rückgabewert belegen und vorher auf richtige Religion testen
//         $religion = do_mysql_query("SELECT religion FROM building WHERE id=".$bld2[$i]['building']);
//         $get_religion = mysql_fetch_row($religion);

        // Daten abfragen
        $building = do_mysql_query("SELECT name, gold, wood, stone, lib_link FROM building WHERE id=".$bld2[$i]['building']);
        $get_building = mysql_fetch_array($building);
        $citybuilding = do_mysql_query("SELECT count FROM citybuilding WHERE building=".$bld2[$i]['building']." and city=".$this->activecity);
        $get_citybuilding = mysql_fetch_array($citybuilding);
        $citybuilding_ordered = do_mysql_query("SELECT count FROM citybuilding_ordered WHERE city=".$this->activecity." and building=".$bld2[$i]['building']);
        $get_citybuilding_ordered = mysql_fetch_array($citybuilding_ordered);
# Return-Variable belegen
        $ret[$count]['existing'] = $get_citybuilding['count'];
        $ret[$count]['working'] = $get_citybuilding_ordered['count'];
        $ret[$count]['name'] = $get_building['name'];
        $ret[$count]['gold'] = $get_building['gold'];
        $ret[$count]['wood'] = $get_building['wood'];
        $ret[$count]['stone'] = $get_building['stone'];
        $ret[$count]['id'] = $bld2[$i]['building'];
        $ret[$count]['lib_link'] = $get_building['lib_link'];

        // Religion des Gebäudes könnte anders sein als die des Spielers...
        if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
          $ret[$count]['same_religion'] = true;
        }
        else {
          $ret[$count]['same_religion'] = $bld2[$i]['religion'] == $this->player_religion || $get_religion[0] == NULL;
        }

        $count ++;
      }
    } // for
    return $ret;
  }
}
?>
