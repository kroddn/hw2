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
 
/*

TODO:

- Marktsteuern, gemäss den Forschungen setzen

DB:

id (Angebotsid)
player (playerid)
wantsType (gesuchtes Gut, Typ (gold|wood|iron|stone))
wantsQuant (Menge des gesuchten Gutes)
hasType (gebotenes Gut, s. wantsType)
hasQuant (Menge des gebotenen Gutes)
ratio (Verhältnis wantsQuand/hasQuand, Fixkommazahl)
time (timestamp von der Aufgabe des Angebots)
city (id der stadt aus welcher waffen kommen)
*/

include_once("includes/util.inc.php");
include_once("includes/diplomacy.common.php");


function getCityName($id) {
	$res = do_mysql_query("SELECT name FROM city WHERE id=".$id);
	$data = mysqli_fetch_assoc($res);
	$cityname = $data['name'];
	return $cityname;
}

function getWeapons($id) {
    $res1 = do_mysql_query("SELECT shortrange,longrange,armor,horse,max_shortrange,max_longrange,max_armor,max_horse FROM city WHERE id=".$id);
    $data1= mysqli_fetch_array($res1);
    $res2 = do_mysql_query("SELECT sum(count * res_storage) AS storagelimit, sum(count * res_shortrange) AS resshort, sum(count * res_longrange) AS reslong, sum(count * res_armor) AS resarmor, sum(count * res_horse) AS reshorse FROM building LEFT JOIN citybuilding ON citybuilding.building = building.id WHERE city = ".$id);
    $data2 = mysqli_fetch_array($res2);
    $w['shortrange']=$data1['shortrange']+0;
    $w['longrange']=$data1['longrange']+0;
    $w['armor']=$data1['armor']+0;
    $w['horse']=$data1['horse']+0;
    $w['max_shortrange']=$data1['max_shortrange']+0;
    $w['max_longrange']=$data1['max_longrange']+0;
    $w['max_armor']=$data1['max_armor']+0;
    $w['max_horse']=$data1['max_horse']+0;
    $w['storagelimit']=$data2['storagelimit']+0;
    $w['resshort']=$data2['resshort']+0;
    $w['reslong']=$data2['reslong']+0;
    $w['resarmor']=$data2['resarmor']+0;
    $w['reshorse']=$data2['reshorse']+0;
    return $w;
}

class Market {

  var $player;
  var $cities;
  var $playername;
  var $playerstatus;
  var $wantsType;
  var $hasType;
  var $start;
  var $num;
  var $sort;
  var $own;
  var $resarray = array('gold','wood','iron','stone','shortrange','longrange','armor','horse');
  var $cityres = array('shortrange','longrange','armor','horse');
  var $resallarray = array('gold','wood','iron','stone','shortrange','longrange','armor','horse','all');
  var $gerres = array('gold'=>'Gold', 'wood'=>'Holz', 'iron'=>'Eisen', 'stone'=>'Stein', 'shortrange'=>'Nahkampfwaffen', 'longrange'=>'Fernkampfwaffen', 'armor'=>'Rüstungen', 'horse'=>'Pferde');  
  var $steuer = 0.97;

  //Konstruktor
  function Market($id) {
    $this->player = $id;
    $player_db_res = do_mysql_query("SELECT name,hwstatus FROM player WHERE id=".$id);
    $player_db = mysqli_fetch_assoc($player_db_res);
    $this->playername = $player_db['name'];
    $this->playerstatus = $player_db['hwstatus'];
    $this->start = 0;

    // Anzahl der Angebote pro Seite auf dem Markt
    $this->num = get_market_size();

    $this->sort = 'ASC';
    $this->own = 0;
    $this->hasType = 'gold';
    $this->wantsType = 'wood';
  }

 
  
  function getStart() {
    return $this->start;
  }

  function getNum() {
    return $this->num;
  }

  function getPage() {
    return $this->start / $this->num + 1;
  }
  
  function getOwn() {
    return $this->own;
  }

  function getHasType() {
    return $this->hasType;
  }

  function getWantsType() {
    return $this->wantsType;
  }

  function getSort() {
    return $this->sort;
  }

  function show_next() {
    $this->start += $this->num;
  }

  function show_prev() {
    $this->start -= $this->num;
    if ($this->start < 0)
      $this->start = 0;
  }

  function setHasType($type) {
    if (in_array($type, $this->resallarray))
    if ($this->hasType != $type) {
      $this->hasType = $type;
      $this->start = 0;
    }
  }

  function setWantsType($type) {
    if (in_array($type, $this->resallarray))
    if ($this->wantsType != $type) {
      $this->wantsType = $type;
      $this->start = 0;
    }
  }

  function setSort($type) {
    if (($type == 'ASC') || ($type == 'DESC'))
      $this->sort = $type;
  }

  function setOwn($own) {
    if ($own)
      $this->own = 1;
    else
      $this->own = 0;
  }

  function validateAddRes($quant, $type, $player, $city) {
    $quant = intval($quant);
    $city = intval($city);
    if ($quant <= 0)
      return "Mein Herr! Nichts kann man nicht handeln!";
    if (!in_array($type, $this->resarray))
      return "Mein Herr! ".$type." kennt man hier nicht.";
    if (in_array($type, $this->cityres)) {
      $res = do_mysql_query("SELECT sum(count * res_storage) AS storage FROM building LEFT JOIN citybuilding ON citybuilding.building = building.id WHERE citybuilding.city = ".$city);
      $data = mysqli_fetch_assoc($res);
      if ($quant > $data['storage'])
	return "Mein Herr! Euer Lagermeister meldet, dass das Lager um ".($quant-$data[$type])." Einheiten zu klein ist";
      //auskommentiert, da diese Funktion noch für reines input checking benutzt wird
      //else {
      //do_mysql_query("UPDATE city SET reserve_".$type."= reserve_".$type." + ".$quant." WHERE owner=".$player." AND id =".$city);
      //echo "<b class=\"noerror\">Es wurde Lagerplatz f&uuml;r ".$quant." ".$this->gerres[$type]." reserviert!</b><br>";
      //}
    }
    return NULL;
  }

  //clean input
  function addRes($quant, $type, $player, $city) {
    if (in_array($type, $this->cityres)) {
      do_mysql_query("UPDATE city SET ".$type." = ".$type."+".$quant." WHERE id = ".$city);
      do_mysql_query("UPDATE city SET reserve_".$type."=reserve_".$type."-".$quant." WHERE owner=".$player." AND id=".$city);
	echo "<b class=\"noerror\">Es wurde Lagerplatz f&uuml;r ".$quant." ".$this->gerres[$type]." freigegeben!</b><br>";
    } else
      do_mysql_query("UPDATE player SET ".$type."=".$type."+".$quant.",cc_ressources=1 WHERE id=".$player);
  }

  function validateSubRes($quant, $type, $player, $city) {
    $quant = intval($quant);
    $city = intval($city);
    if ($quant <= 0) {
      return "Mein Herr! Nichts kann man nicht handeln!";
    }
    if (!in_array($type, $this->resarray)) {
      return "Mein Herr! ".$type." kennt man hier nicht.";
    }
    
    if (in_array($type, $this->cityres)) {
      $res = do_mysql_query("SELECT ".$type." FROM city WHERE id = ".$city);
      $data = mysqli_fetch_assoc($res);
      if ($quant > $data[$type])
      return "Mein Herr! Ihr habt nur ".$data[$type]." ".$this->gerres[$type]." statt ".$quant." auf Lager.";
    }
    else {
      $res = do_mysql_query("SELECT ".$type." FROM player WHERE id = ".$player);
      $data = mysqli_fetch_assoc($res);
      if ($quant > $data[$type])
      return "Mein Herr! Ihr habt nur ".$data[$type]." ".$this->gerres[$type]." statt ".$quant.".";
    }
    return NULL;
  }

  // clean input
  function subRes($quant, $type, $player, $city) {
    if (in_array($type, $this->cityres))
      do_mysql_query("UPDATE city SET ".$type." = ".$type."-".$quant." WHERE id = ".$city);
    else
      do_mysql_query("UPDATE player SET ".$type."=".$type."-".$quant.",cc_ressources=1 WHERE id=".$player);
  }

  //function remove_offer($id) {
  //update city set shortrange=shortrange+1 where owner=8 order by  if(id=497205,1,0)  DESC ,capital desc limit 1

  //unfertig
  function _put($wantsType, $wantsQuant, $hasType, $hasQuant) {
    if (($msg = $this->validateSubRes($hasQuant, $hasType, $_SESSION['player']->getID(), $_SESSION['cities']->activecity)) || ($msg = $this->validateAddRes($wantsQuant, $wantsType, $_SESSION['player']->getID(), $_SESSION['cities']->getActiveCity()))) {
      show_error($msg);
      return;
    } else {
      $this->subRes($hasType, $hasQuant, $_SESSION['player']->getID(),$_SESSION['cities']->getActiveCity());

      //add offer

      do_mysql_query("INSERT INTO market (player,wantsType,wantsQuant,hasType,hasQuant,ratio,timestamp,city) VALUES (".$this->player.",'".$wantsType."',".$wantsQuant.",'".$hasType."',".$hasQuant.",".($wantsQuant/$hasQuant).",".time().",".$_SESSION['cities']->activecity.")");
    }
  }


function put($wantsType, $wantsQuant, $hasType, $hasQuant) {
	$wantsQuant = intval($wantsQuant);
	$hasQuant = intval($hasQuant);
    
	//input checking, (co2)
    if (($msg = $this->validateSubRes($hasQuant, $hasType, $_SESSION['player']->getID(), $_SESSION['cities']->activecity)) || ($msg = $this->validateAddRes($wantsQuant, $wantsType, $_SESSION['player']->getID(), $_SESSION['cities']->getActiveCity()))) {
      show_error($msg);
      return ;
    }    
    //end input checking
	if($wantsType == $hasType) {
		echo '<b class="error">Seid Ihr ein Narr? Ihr wollt Gleiches gegen Gleiches eintauschen?</b>';
		return false;
	}
	if($hasQuant < 1) {
		echo '<b class="error">MyLord! Geschenkt wird euch nichts! Gebt ein Anbot ab!</b>';
		return false;
	}
	if($wantsQuant < 100) {
		echo '<b class="error">MyLord! Die Handelsmenge liegt bei mindestens 100 Einheiten!</b>';
		return false;
	}
    $num = do_mysql_query_fetch_array("SELECT count(*) AS cnt FROM market ".
                                      " WHERE wantsType='$wantsType' AND hasType = '$hasType'".
                                      " AND player = ".$_SESSION['player']->getID() );
    if(defined("MARKET_LIMIT") && $num['cnt'] >= MARKET_LIMIT) {
      echo '<b class="error">MyLord! Ihr dürft nicht mehr als '.MARKET_LIMIT.' Angebote pro Kategorie aufgeben!</b>';
      return false;
    }

	if($wantsType == "shortrange" || $wantsType == "longrange" || $wantsType == "armor" || $wantsType == "horse") {
      $resA = do_mysql_query("SELECT id,".$wantsType.",reserve_".$wantsType." FROM city WHERE id =".$_SESSION['cities']->activecity."");
      $dataA = mysqli_fetch_assoc($resA);
      $dataweapons = getWeapons($dataA['id']);
      if(($dataweapons['storagelimit']-$dataA['reserve_'.$wantsType]) < ($dataA[$wantsType] + $wantsQuant)) {
        echo "<b class=\"error\">MyLord! So viele ".$this->gerres[$wantsType]." k&ouml;nnt Ihr in ".$_SESSION['cities']->activecityname." nicht lagern! Baut euer Lager aus!<br>";
        echo "Im Lager befinden sich ".$dataA[$wantsType]." ".$this->gerres[$wantsType]."!<br/>";
        echo "Von der maximalen Lagerkapazit&auml;t (".$dataweapons['storagelimit'].") wurden ".$dataA['reserve_'.$wantsType]." f&uuml;r bereits existierende Marktangebote reserviert!<br/>";
        echo "Es haben maximal ".($dataweapons['storagelimit']-$dataA['reserve_'.$wantsType]-$dataA[$wantsType])." weitere ".$this->gerres[$wantsType]." im Lager Platz</b>";
        return false;
      } else {
        $reserve = 1;
      }
	}
	if($hasType == "shortrange" || $hasType == "longrange" || $hasType == "armor" || $hasType == "horse") {
      $res = do_mysql_query("SELECT ".$hasType." AS ress FROM city WHERE owner=".$this->player." AND id = ".$_SESSION['cities']->activecity);
      $data = mysqli_fetch_assoc($res);
      if($data['ress'] < $hasQuant) {
        echo '<b class="error">Ihr habt zu wenige '.$this->gerres[$hasType].' in '.$_SESSION['cities']->activecityname.' um euer Angebot aufzugeben.<b><br>';
        echo '<b class="error">Es befinden sich lediglich '.$data['ress'].' Einheiten im Lager!';
      } else {
        do_mysql_query("UPDATE city SET ".$hasType."=".$hasType."-".($hasQuant)." WHERE owner=".$this->player." AND id = ".$_SESSION['cities']->activecity);
        do_mysql_query("UPDATE player SET cc_resources = 1 WHERE id = ".$_SESSION['player']->id);
        do_mysql_query("INSERT INTO market (player,wantsType,wantsQuant,hasType,hasQuant,ratio,timestamp,city) VALUES (".$this->player.",'".$wantsType."',".$wantsQuant.",'".$hasType."',".$hasQuant.",".($wantsQuant/$hasQuant).",".time().",'".$_SESSION['cities']->activecity."')");
        echo '<b class="noerror">Das Angebot wurde am Marktplatz positioniert!<br>';
        if($reserve == 1) {
          do_mysql_query("UPDATE city SET reserve_".$wantsType."=".($dataA['reserve_'.$wantsType]+$wantsQuant)." WHERE owner=".$this->player." AND name ='".$_SESSION['cities']->activecityname."'");
          echo "<b class=\"noerror\">Es wurde Lagerplatz f&uuml;r ".$wantsQuant." ".$this->gerres[$wantsType]." reserviert!</b><br>";
        }
      }
	}
    else {
      $wantsQuant = intval($wantsQuant);
      $hasQuant = intval($hasQuant);
      if (($wantsQuant > 0) && ($hasQuant > 0) && in_array($wantsType, $this->resarray) && in_array($hasType, $this->resarray)) {
        $res = do_mysql_query("SELECT ".$hasType." FROM player WHERE id=".$this->player);
        $data = mysqli_fetch_assoc($res);
        if ($data[$hasType]>=$hasQuant) {
          do_mysql_query("UPDATE player SET ".$hasType."=".$hasType."-".($hasQuant).",cc_resources=1 WHERE id=".$this->player);
          do_mysql_query("INSERT INTO market (player,wantsType,wantsQuant,hasType,hasQuant,ratio,timestamp,city) VALUES (".$this->player.",'".$wantsType."',".$wantsQuant.",'".$hasType."',".$hasQuant.",".($wantsQuant/$hasQuant).",".time().",".$_SESSION['cities']->activecity.")");
          if($reserve == 1) {
            do_mysql_query("UPDATE city SET reserve_".$wantsType."=".($dataA['reserve_'.$wantsType]+$wantsQuant)." WHERE owner=".$this->player." AND name ='".$_SESSION['cities']->activecityname."'");
            echo "<b class=\"noerror\">Es wurde Lagerplatz f&uuml;r ".$wantsQuant." ".$this->gerres[$wantsType]." reserviert!</b><br>";
          }
        } else {
          echo '<b class="error">Ihr habt zu wenig '.$this->gerres[$hasType].' um ihr Angebot zu bezahlen<b><br>';
        }
      }
	}
}

  /**
   * Assumes clean input.
   */
  function accept($id) {
    $res1 = do_mysql_query("SELECT wantsType,wantsQuant,hasType,hasQuant,player,timestamp,city FROM market WHERE id=".$id);
    if ($data1 = mysqli_fetch_assoc($res1)) {

      //input checking, (co2)
      if (($msg = $this->validateSubRes($data1['wantsQuant'], $data1['wantsType'], $_SESSION['player']->getID(), $_SESSION['cities']->activecity)) || ($msg = $this->validateAddRes($data1['hasQuant'], $data1['hasType'], $_SESSION['player']->getID(), $_SESSION['cities']->getActiveCity()))) {
        return $msg;
      }      
      //end input checking
      
      // FALL 1: Aktuelle spieler hat Rüstungsgüter, die müssen gesonder behandelt werden
      if($data1['hasType'] == "shortrange" || $data1['hasType'] == "longrange" || $data1['hasType'] == "armor" || $data1['hasType'] == "horse") {
        $cityname = $_SESSION['cities']->activecityname;
        $res2 = do_mysql_query("SELECT id,".$data1['hasType']." FROM city WHERE name='".$cityname."'");
        $data2 = mysqli_fetch_assoc($res2);        

        $dataweapons = getWeapons($data2['id']);
        if(($data2[$data1['hasType']]+$data1['hasQuant']) <= $dataweapons['storagelimit']) {
          if($data1['wantsType'] == "shortrange" || $data1['wantsType'] == "longrange" || $data1['wantsType'] == "armor" || $data1['wantsType'] == "horse") {
            do_mysql_query("UPDATE city SET ".$data1['hasType']." = ".$data1['hasType']."+".$data1['hasQuant']." WHERE name = '".$_SESSION['cities']->activecityname."'");
            do_mysql_query("UPDATE city SET ".$data1['wantsType']." = ".$data1['wantsType']."-".$data1['wantsQuant']." WHERE name = '".$_SESSION['cities']->activecityname."'");
            do_mysql_query("UPDATE city SET reserve_".$data1['wantsType']." = reserve_".$data1['wantsType']."-".$data1['wantsQuant']." WHERE id = '".$data1['city']."'");
            do_mysql_query("UPDATE city SET ".$data1['wantsType']." = ".$data1['wantsType']."+".round(($data1['wantsQuant']*$this->steuer),0)." WHERE id = '".$data1['city']."'");
            $res3 = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
            if ($data3 = mysqli_fetch_assoc($res3)) {
              $res4 = do_mysql_query("SELECT name FROM city WHERE id=".$data1['city']);
              $data4 = mysqli_fetch_assoc($res4);
              do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['player'].",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Der Spieler [b]".$data3['name']."[/b] hat ihr Angebot [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] gegen [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] angenommen.\nNach Steuern wurden euch [b]".round(($data1['wantsQuant']*$this->steuer),0)." ".$this->gerres[$data1['wantsType']]."[/b] gutgeschrieben. Die Waffen wurden in Eurer Stadt [b]".$data4['name']."[/b] eingelagert.',2)");
              do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$_SESSION['player']->getID().",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Ihr habt soeben ein Angebot von [b]".resolvePlayerName($data1['player'])."[/b] angenommen. Ihr habt [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] an Euren Handelspartner gesendet und im Gegenzug [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] erhalten. Die Waffen wurden in Eurer Stadt [b]".$_SESSION['cities']->activecityname."[/b] eingelagert.',2)");
              do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$data1['player']);
              do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$this->player);
            }
            do_mysql_query("DELETE FROM market WHERE id=".$id);
          } 
          else {
            do_mysql_query("UPDATE city SET ".$data1['hasType']." = ".$data1['hasType']."+".$data1['hasQuant']." WHERE name = '".$_SESSION['cities']->activecityname."'");
            do_mysql_query("UPDATE player SET ".$data1['wantsType']." = ".$data1['wantsType']."-".$data1['wantsQuant']." WHERE id = ".$_SESSION['player']->id);
            do_mysql_query("UPDATE player SET ".$data1['wantsType']." = ".$data1['wantsType']."+".round(($data1['wantsQuant']*$this->steuer),0)." WHERE id = ".$data1['player']);
            do_mysql_query("INSERT INTO log_market_accept (offerer,acceptor,wantsType,wantsQuant,hasType,hasQuant,acctime,offtime,city) VALUES (".$data1['player'].",".$this->player.",'".$data1['wantsType']."',".round(($data1['wantsQuant']*$this->steuer),0).",'".$data1['hasType']."',".$data1['hasQuant'].",".time().",".$data1['timestamp'].", ".$data1['city'].")");
            $res3 = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
            if ($data3 = mysqli_fetch_assoc($res3)) {
              do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['player'].",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Der Spieler ".$data3['name']." hat ihr Angebot ".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]." gegen ".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]." angenommen. Nach Steuern wurden euch ".round(($data1['wantsQuant']*$this->steuer),0)." ".$this->gerres[$data1['wantsType']]." gutgeschrieben.',2)");
              do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$_SESSION['player']->getID().",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Ihr habt soeben ein Angebot von [b]".resolvePlayerName($data1['player'])."[/b] angenommen. Ihr habt [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] an Euren Handelspartner gesendet und im Gegenzug [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] erhalten. Die Waffen wurden in Eurer Stadt [b]".$_SESSION['cities']->activecityname."[/b] eingelagert.',2)");
              do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$data1['player']);
              do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$this->player);
            }
            do_mysql_query("DELETE FROM market WHERE id=".$id);
            // Inform
            echo "<b class=\"error\">".$data1['hasQuant']." ".$data1['hasType']." wird nun in Ihrer Stadt ".$_SESSION['cities']->activecityname." gelagert!</b><br>";
          }
        } 
        else {
          return "Ihr Lager in ".$cityname." platzt aus allen N&auml;hten, baut das Lager aus!";
        }
      }
      // FALL 2: Spieler bietet normale Resourcen, SUCHT aber Rüstungsgüter
      else if($data1['wantsType'] == "shortrange" || $data1['wantsType'] == "longrange" || $data1['wantsType'] == "armor" || $data1['wantsType'] == "horse") {
        $res2 = do_mysql_query("SELECT id,".$data1['wantsType']." FROM city WHERE name='".$_SESSION['cities']->activecityname."'");
        $data2 = mysqli_fetch_assoc($res2);
        $cityname = $_SESSION['cities']->activecityname;
        $dataweapons = getWeapons($data2['id']);

        // Lagert genug in der Stadt?
        if($data2[$data1['wantsType']] >= $data1['wantsQuant']) {
          do_mysql_query("UPDATE city SET ".$data1['wantsType']." = ".$data1['wantsType']."-".$data1['wantsQuant']." WHERE name = '".$_SESSION['cities']->activecityname."'");
          do_mysql_query("UPDATE player SET ".$data1['hasType']." = ".$data1['hasType']."+".$data1['hasQuant']." WHERE id = ".$_SESSION['player']->id);
          do_mysql_query("UPDATE city SET ".$data1['wantsType']." = ".$data1['wantsType']."+".round(($data1['wantsQuant']*$this->steuer),0)." WHERE id = ".$data1['city']);
          do_mysql_query("UPDATE city SET reserve_".$data1['wantsType']." = reserve_".$data1['wantsType']."-".$data1['wantsQuant']." WHERE id = ".$data1['city']);
          do_mysql_query("INSERT INTO log_market_accept (offerer,acceptor,wantsType,wantsQuant,hasType,hasQuant,acctime,offtime,city) VALUES (".$data1['player'].",".$this->player.",'".$data1['wantsType']."',".round(($data1['wantsQuant']*$this->steuer),0).",'".$data1['hasType']."',".$data1['hasQuant'].",".time().",".$data1['timestamp'].", ".$data1['city'].")");

          $res3 = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
          if ($data3 = mysqli_fetch_assoc($res3)) {
            $res4 = do_mysql_query("SELECT name FROM city WHERE id=".$data1['city']);
            $data4 = mysqli_fetch_assoc($res4);
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['player'].",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Der Spieler [b]".$data3['name']."[/b] hat ihr Angebot [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] gegen [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] angenommen. Nach Steuern wurden euch [b]".round(($data1['wantsQuant']*$this->steuer),0)." ".$this->gerres[$data1['wantsType']]."[/b] gutgeschrieben. Die Waffen wurden in Eurer Stadt [b]".$data4['name']."[/b] eingelagert.',2)");
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$_SESSION['player']->getID().",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Ihr habt soeben ein Angebot von [b]".resolvePlayerName($data1['player'])."[/b] angenommen. Ihr habt [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] an Euren Handelspartner gesendet und im Gegenzug [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] erhalten.',2)");
            do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$data1['player']);
            do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$this->player);
          }

          do_mysql_query("DELETE FROM market WHERE id=".$id);
        }
        else {
          return "In der Stadt".$_SESSION['cities']->activecityname." lagern nicht gen&uuml;gend ".$this->gerres[$data1['wantsType']];
        }
      }
      // FALL 3: Resourcen gegen Resourcen
      else {
        $res2 = do_mysql_query("SELECT ".$data1['wantsType']." FROM player WHERE id=".$this->player);
        $data2 = mysqli_fetch_assoc($res2);
        if ($data2[$data1['wantsType']]>=$data1['wantsQuant']) {
          do_mysql_query("UPDATE player SET ".$data1['hasType']."=".$data1['hasType']."+".($data1['hasQuant']).",".$data1['wantsType']."=".$data1['wantsType']."-".$data1['wantsQuant'].",cc_resources=1 WHERE id=".$this->player);
          do_mysql_query("UPDATE player SET ".$data1['wantsType']."=".$data1['wantsType']."+".round(($data1['wantsQuant']*$this->steuer),0).",cc_resources=1,cc_messages=1 WHERE id=".$data1['player']);
          do_mysql_query("DELETE FROM market WHERE id=".$id);
          do_mysql_query("INSERT INTO log_market_accept (offerer,acceptor,wantsType,wantsQuant,hasType,hasQuant,acctime,offtime,city) VALUES (".$data1['player'].",".$this->player.",'".$data1['wantsType']."',".round(($data1['wantsQuant']*$this->steuer),0).",'".$data1['hasType']."',".$data1['hasQuant'].",".time().",".$data1['timestamp'].",".$data1['city'].")");
          $res2 = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
          if ($data2 = mysqli_fetch_assoc($res2)) {
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['player'].",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Der Spieler [b]".$data2['name']."[/b] hat ihr Angebot [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] gegen [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] angenommen. Nach Steuern wurden euch [b]".round(($data1['wantsQuant']*$this->steuer),0)." ".$this->gerres[$data1['wantsType']]."[/b] gutgeschrieben.',2)");
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$_SESSION['player']->getID().",".time().",'Angenommen: ".$this->gerres[$data1['hasType']]." gegen ".$this->gerres[$data1['wantsType']]."','Ihr habt soeben ein Angebot von [b]".resolvePlayerName($data1['player'])."[/b] angenommen. Ihr habt [b]".$data1['wantsQuant']." ".$this->gerres[$data1['wantsType']]."[/b] an Euren Handelspartner gesendet und im Gegenzug [b]".$data1['hasQuant']." ".$this->gerres[$data1['hasType']]."[/b] erhalten.',2)");
            do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$data1['player']);
            do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$this->player);
          }
        }
        else {
          return "Ihr besitzt nicht genug Resourcen.";
        }
      }
    }
  } // function

//assumes clean input
function takeBack($id) {
    $res = do_mysql_query("SELECT id,wantsType,wantsQuant,hasType,hasQuant,player,city FROM market WHERE id=".$id);
    if ($data = mysqli_fetch_assoc($res)) {
		if($data['hasType'] == "shortrange" || $data['hasType'] == "longrange" || $data['hasType'] == "armor" || $data['hasType'] == "horse") {
   			$res2 = do_mysql_query("SELECT ".$data['hasType']." FROM city WHERE id=".$data['city']);
			$data2 = mysqli_fetch_assoc($res2);
			$exists = mysqli_num_rows($res2);
			if ($exists == 1) {
				$dataweapons = getWeapons($data['city']);
				$cityname = getCityName($data['city']);
				$city = $data['city'];
				$free = ($dataweapons['storagelimit']-$data2[$data['hasType']]);
				$op = ($dataweapons['storagelimit']-($data2[$data['hasType']]+$data['hasQuant']));
			} else {
				echo "<b class=\"error\">Die Stadt von der das Angebot abgegeben wurde existiert nicht mehr!</b><br />";
				$resA = do_mysql_query("SELECT id,name,".$data['hasType']." FROM city WHERE capital = 1 AND owner =".$_SESSION['player']->id);
				$dataA = mysqli_fetch_assoc($resA);
				$dataweapons = getWeapons($dataA['id']);
				$cityname = $dataA['name'];
				echo "<b class=\"error\">Die Markth&auml;ndler liefern das Angebot daher nur in Ihre Hauptstadt ".$cityname."!</b><br />";
				$city = $dataA['id'];
				$free = ($dataweapons['storagelimit']-$dataA[$data['hasType']]);
				$op = ($dataweapons['storagelimit']-($dataA[$data['hasType']]+$data['hasQuant']));
			}
			if($op >= 0) {
				do_mysql_query("UPDATE city SET ".$data['hasType']."=".$data['hasType']."+".$data['hasQuant']." WHERE id=".$city);
				do_mysql_query("DELETE FROM market WHERE id=".$id);
				if($exists == 1) {
					echo "<b class=\"noerror\">Die angebotenen ".$data['hasQuant']." ".$this->gerres[$data['hasType']]." werden wieder in der Stadt ".$cityname." gelagert!</b></br/>";
					if($data['wantsType'] == "shortrange" || $data['wantsType'] == "longrange" || $data['wantsType'] == "armor" || $data['wantsType'] == "horse") {
						do_mysql_query("UPDATE city SET reserve_".$data['wantsType']."= reserve_".$data['wantsType']."-".$data['wantsQuant']." WHERE owner=".$this->player." AND name ='".$_SESSION['cities']->activecityname."'");
						echo "<b class=\"noerror\">Es wurde Lagerplatz f&uuml;r ".$data['wantsQuant']." ".$this->gerres[$data['wantsType']]." frei gegeben!</b><br>";
					}
				} else {
					echo "<b class=\"noerror\">Die angebotenen ".$data['hasQuant']." ".$this->gerres[$data['hasType']]." werden jetzt in der Stadt ".$cityname." gelagert!</b></br/>";
				}
			} else {
				if($free < 0) { $free = 0; };
				echo "<b class=\"error\">In der Stadt ".$cityname." in der das Angebot aufgegeben wurde, ist zu wenig Lagerplatz verf&uuml;gbar!</b><br />\n";
				echo "<b class=\"error\">Es ist lediglich Lagerplatz f&uuml;r weitere ".$free." ".$this->gerres[$data['hasType']]." verf&uuml;gbar!</b><br />";
				echo "<b class=\"error\">Das Angebot konnte nicht zur&uuml;ck geholt werden. Bauen Sie Ihr Lager aus!</b><br />";
			}

		} else {
			do_mysql_query("UPDATE player SET ".$data['hasType']."=".$data['hasType']."+".$data['hasQuant'].",cc_resources=1 WHERE id=".$this->player);
			echo "<b class=\"noerror\">Das Angebot wurde zur&uuml;ck genommen!</b><br />\n";
			// Reservierten Lagerplatz wieder frei geben
			// Problem wenn die Stadt nicht mehr existiert! bzw. nicht mehr in seinem Eigentum ist
			$resB = do_mysql_query("SELECT id,wantsType,wantsQuant,hasType,hasQuant,player,city FROM market WHERE id=".$id);
			$dataB = mysqli_fetch_assoc($resB);
			if($dataB['wantsType'] == "shortrange" || $dataB['wantsType'] == "longrange" || $dataB['wantsType'] == "armor" || $dataB['wantsType'] == "horse") {
				do_mysql_query("UPDATE city SET reserve_".$dataB['wantsType']."= reserve_".$dataB['wantsType']."-".$dataB['wantsQuant']." WHERE owner=".$this->player." AND id ='".$dataB['city']."'");
				echo "<b class=\"noerror\">Der reservierte Lagerplatz wurde wieder frei gegeben!</b>\n";
			}

			do_mysql_query("DELETE FROM market WHERE id=".$id);
		}
	}
}

  /**
   *
   * @param $id
   * @return unknown_type
   */
  function click($id) {
    $id = intval($id);
    $res = do_mysql_query("SELECT wantsType,wantsQuant,hasType,hasQuant,player,city FROM market WHERE id=".$id);
    if ($data = mysqli_fetch_assoc($res)) {
      // Falls das Angebot dem aktuellen Spieler gehört, ziehe es zurück
      if ($data['player'] == $this->player) {
        $this->takeBack($id);
        return null;
      } 
      else {
        // Prüfen, ob die handeln dürfen
        if(null != ($error = $this->checkMayTrade($this->player, $id))) {
          return $error;
        }
        
        return $this->accept($id);
      }
    }
  }


  /**
   * 
   * @param $type
   * @param $quant
   * @param $to    Spieler NAME (String)
   * @return unknown_type
   */
  function sendRes($type, $quant, $to) {
    $quant = intval($quant);
    if(!checkBez($to, 3, 40)) return "Ungültiger Spielername (falsche Zeichen)";
    if($quant <= 0) return "Netter Versuch.";     
    
    if (in_array($type, $this->resarray)) {      
      $res1 = do_mysql_query("SELECT id FROM player WHERE name='".mysqli_escape_string($GLOBALS['con'], $to)."'");
      if($data1 = mysqli_fetch_assoc($res1)) {
        // Prüfen, ob die handeln dürfen
        if(null != ($error = $this->checkMayTrade($this->player, $data1['id']))) {
          return $error;
        }
        
        $res2 = do_mysql_query("SELECT ".$type." FROM player WHERE id=".$this->player);
        $data2 = mysqli_fetch_assoc($res2);
        if ($data2[$type]>=$quant) {
          do_mysql_query("UPDATE player SET ".$type."=".$type."-".$quant.",cc_messages=1,cc_resources=1 WHERE id=".$this->player);
          do_mysql_query("UPDATE player SET ".$type."=".$type."+".floor($quant*0.97).",cc_messages=1,cc_resources=1 WHERE id=".$data1['id']);
          //MULTILOG START
          do_mysql_query("INSERT INTO log_market_send (idfrom,idto,quant,type,time) VALUES (".$this->player.",".$data1['id'].",".$quant.",'".$type."',UNIX_TIMESTAMP() )");
          // do_mysql_query("INSERT INTO log_multi_market VALUES ('', '".$this->player."', '".$data1['id']."', '".time()."')");

          //MULTILOG ENDE
          $res3 = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
          if ($data3 = mysqli_fetch_assoc($res3)) {
            // Nachrichten an beide Spieler
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['id'].",".time().",'".$quant." ".$this->gerres[$type]." VON ".$data3['name']."','Der Spieler ".$data3['name']." hat euch ".$quant." ".$this->gerres[$type]." gesandt. Nach Steuern wurden euch ".floor($quant*0.97)." gutgeschrieben.',2)");
            do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.",".time().",'".$quant." ".$this->gerres[$type]." AN ".$to."','Ihr habt dem Spieler ".$to." ".$quant." ".$this->gerres[$type]." gesandt.',2)");
          }
        }
        else {
          return "Ihr besitzt nicht genug davon.";
        }
      }
      else {
        return "Dieser Spieler existiert nicht.";
      }
    }
    else {
      return "Die Resource $type existiert nicht.";
    } 
  }

  
  
  /**
   *  MarketMod-Funktion: Angebot zurücksenden
   */
  function sendBack($id) {
    global $player;
    if ($this->playerstatus & 4) {
      $offer_res = do_mysql_query("SELECT wantsType,wantsQuant,hasType,hasQuant,player,City,timestamp FROM market WHERE id=".$id);
      if ($offer = mysqli_fetch_assoc($offer_res)) {
        // Admins dürfen alles. Marktmoderatoren nicht, wenn Sie befeindet sind mit diesem Spieler
        if ($player->isAdmin() || !((getRel($offer['player'], $this->player) == 0) || (getClanRel($offer['player'], $this->player) == 0))) {
          do_mysql_query("INSERT INTO log_marketmod(modid,player,type,wantsType,wantsQuant,hasType,hasQuant,City,timeOffer,time,rel,clanrel) VALUES (".$this->player.",".$offer['player'].",'return','".$offer['wantsType']."',".$offer['wantsQuant'].",'".$offer['hasType']."',".$offer['hasQuant'].",".$offer['City'].",".$offer['timestamp'].",".time().",".(getRel($offer['player'], $this->player)).",".(getClanRel($offer['player'], $this->player)).")");
          do_mysql_query("DELETE FROM market WHERE id=".$id);
          do_mysql_query("UPDATE player SET ".$offer['hasType']."=".$offer['hasType']."+".$offer['hasQuant']." WHERE id=".$offer['player']);
          do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$offer['player'].",".time().",'Zurückgeschickt: ".$this->gerres[$offer['hasType']]." gegen ".$this->gerres[$offer['wantsType']]."','Ein Marktmod hat ihr Angebot ".$offer['hasQuant']." ".$this->gerres[$offer['hasType']]." gegen ".$offer['wantsQuant']." ".$this->gerres[$offer['wantsType']]." zurückgeschickt, das Angebot wurde entfernt und die angebotenen Ressourcen wurden euch gutgeschrieben.',2)");
          do_mysql_query("UPDATE player SET cc_resources=1,cc_messages=1 WHERE id=".$offer['player']);
          if (($offer['wantsType']=="shortrange") || ($offer['wantsType']=="longrange") || ($offer['wantsType']=="armor") || ($offer['wantsType']=="horse"))
          {
            do_mysql_query("UPDATE city SET reserve_".$offer['wantsType']."=reserve_".$offer['wantsType']."-".$offer['wantsQuant']." WHERE owner=".$offer['player']." AND id = ".$offer['City']);
          }
        }
        else {
          echo "Ihr wollt doch nicht einem Feind ein Angebot zurückschicken?";
        } // if (!((getRel($offer['player'], $this->player) == 0) || (getClanRel($offer['player'], $this->player) == 0)))
      }
    }
    else {
      return "Diese Funktion ist Marktmoderatoren vorbehalten. Versucht Ihr DoS-Attacken?";
    }

    return null;
  }

  /**
   *  MarketMod-Funktion: Angebot löschen
   */
  function delOffer($id) {
    global $player;
    if ($this->playerstatus & 4) {
      $offer_res = do_mysql_query("SELECT wantsType,wantsQuant,hasType,hasQuant,player,City,timestamp FROM market WHERE id=".$id);
      
      if ($offer = mysqli_fetch_assoc($offer_res)) {
        // Admins dürfen alles. Marktmoderatoren nicht, wenn Sie befeindet sind mit diesem Spieler
        if ($player->isAdmin() || !((getRel($offer['player'], $this->player) == 0) || (getClanRel($offer['player'], $this->player) == 0))) {
          do_mysql_query("INSERT INTO log_marketmod(modid,player,type,wantsType,wantsQuant,hasType,hasQuant,City,timeOffer,time,rel,clanrel) VALUES (".$this->player.",".$offer['player'].",'delete','".$offer['wantsType']."',".$offer['wantsQuant'].",'".$offer['hasType']."',".$offer['hasQuant'].",".$offer['City'].",".$offer['timestamp'].",".time().",".(getRel($offer['player'], $this->player)).",".(getClanRel($offer['player'], $this->player)).")");
          do_mysql_query("DELETE FROM market WHERE id=".$id);
          do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$offer['player'].",".time().",'Gelöscht: ".$this->gerres[$offer['hasType']]." gegen ".$this->gerres[$offer['wantsType']]."','Ein Marktmod hat ihr Angebot ".$offer['hasQuant']." ".$this->gerres[$offer['hasType']]." gegen ".$offer['wantsQuant']." ".$this->gerres[$offer['wantsType']]." gelöscht, weil es unverhältnismäßig oder sinnlos war.',2)");
          do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$offer['player']);
          if (($offer['wantsType']=="shortrange") || ($offer['wantsType']=="longrange") || ($offer['wantsType']=="armor") || ($offer['wantsType']=="horse"))
          {
            do_mysql_query("UPDATE city SET reserve_".$offer['wantsType']."=reserve_".$offer['wantsType']."-".$offer['wantsQuant']." WHERE owner=".$offer['player']." AND id = ".$offer['City']);
          }
        }
        else {
          return "Ihr wollt doch nicht einem Feind ein Angebot löschen?";
        }
      }
    }
    else {
      return "Diese Funktion ist Marktmoderatoren vorbehalten. Versucht Ihr DoS-Attacken?";
    }
    
    return null;
  }

  
  /**
   *  MarketMod-Funktion: Angebot bestrafen.
   *  Löscht das Angebot und bestraft den Spieler.
   */
  function punishOffer($id) {
    global $player;

    if ($this->playerstatus & 4) {
      $offer_res = do_mysql_query("SELECT wantsType,wantsQuant,hasType,hasQuant,player,ratio,City,timestamp FROM market WHERE id=".$id);
      if ($offer = mysqli_fetch_assoc($offer_res)) {
        
        // Admins dürfen alles. Marktmoderatoren nicht, wenn Sie befeindet sind mit diesem Spieler
        if ($player->isAdmin() || !((getRel($offer['player'], $this->player) == 0) || (getClanRel($offer['player'], $this->player) == 0))) {
          do_mysql_query("DELETE FROM market WHERE id=".$id);
          $fine=($offer['ratio']>1?$offer['ratio']:1/$offer['ratio'])*$offer['hasQuant'];
          do_mysql_query("INSERT INTO log_marketmod(modid,player,type,wantsType,wantsQuant,hasType,hasQuant,City,timeOffer,time,rel,clanrel,penaltyType,penaltyQuant) VALUES (".$this->player.",".$offer['player'].",'punish','".$offer['wantsType']."',".$offer['wantsQuant'].",'".$offer['hasType']."',".$offer['hasQuant'].",".$offer['City'].",".$offer['timestamp'].",".time().",".(getRel($offer['player'], $this->player)).",".(getClanRel($offer['player'], $this->player)).",'".$offer['hasType']."',".$fine.")");
          do_mysql_query("UPDATE player SET ".$offer['hasType']."=".$offer['hasType']."-".$fine." WHERE id=".$offer['player']);
          do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$offer['player'].",".time().",'Gelöscht: ".$this->gerres[$offer['hasType']]." gegen ".$this->gerres[$offer['wantsType']]."','Ein Marktmod hat ihr Angebot ".$offer['hasQuant']." ".$this->gerres[$offer['hasType']]." gegen ".$offer['wantsQuant']." ".$this->gerres[$offer['wantsType']]." gelöscht, weil es sehr unverhältnismäßig oder sinnlos war. Ihr wurdet zudem mit ".$fine." ".$this->gerres[$offer['hasType']]." bestraft.',2)");
          do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$offer['player']);
          if (($offer['wantsType']=="shortrange") || ($offer['wantsType']=="longrange") || ($offer['wantsType']=="armor") || ($offer['wantsType']=="horse"))
          {
            do_mysql_query("UPDATE city SET reserve_".$offer['wantsType']."=reserve_".$offer['wantsType']."-".$offer['wantsQuant']." WHERE owner=".$offer['player']." AND id = ".$offer['City']);
          }
        }
        else {
            return "Ihr wollt doch nicht einen Feind für ein Angebot bestrafen?";
        }
      }
    }
    else {
      return "Diese Funktion ist Marktmoderatoren vorbehalten. Versucht Ihr DoS-Attacken?";
    }
    
    
    return null;
  }
  
  
  /**
   * Prüfen, ob zwei Spieler miteinander Handeln dürfen.
   * 
   * @param $id1  ID des ersten Spielers, mit dem der Handel stattfindet.
   * @param $id2  ID des ersten Spielers, mit dem der Handel stattfindet.
   * 
   * @return null, falls die beiden Spieler handeln dürfen.
   *         Andernfalls eine Fehlermeldung, die beschreibt, wieso die Spieler nicht handeln dürfen
   */
  function checkMayTrade($id1, $id2) {
    // Multiexception testen
    $check = checkMultiException($id1, $id2);
    
    if($check != null) {
        $error = sprintf("Der Handel mit diesem Spieler ist untersagt, weil eine MultiException vorliegt. Begründung: &quot;%s&quot;.", $check );
        return $error;
    }    
    
    return null;
  }

}
