<?
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

include_once ("./includes/db.inc.php");
include_once ("./includes/config.inc.php");
include_once ("./includes/fight.func.php");
include_once ("./includes/util.inc.php");
define("Fight_Draw", 0);
define("Fight_AttWin", 1);
define("Fight_DefWin", 2);

class FightClass {
  //Einzulesende Daten
  //array mit id und namen des spielers 
  var $defenders = array ();
  //zweidimensionales array mit id, count, player der armeen
  var $defarmys = array ();
  //array mit id und namen des spielers 
  var $attackers = array ();
  //zweidimensionales array mit id, count, player der armeen
  var $attarmys = array ();
  //Stadtname
  var $cityname;
  //Stadtbevölkerung
  var $citypopulation;
  //Stadtwohlstand
  var $cityprosperity;
  //Cityowner
  //array mit id und namen des spielers
  var $cityowner = array ();
  //Taktik
  var $tactic;
  //Deffbonus
  var $defbonus;
  //Ergebnisdaten
  var $survived_defarmys = array ();
  var $survived_attarmys = array ();
  //Fight_Draw, Fight_AttWin or Fight_DefWin
  var $result;
  function FightClass() {
  }
  //Methoden zum einlesen von Daten
  //Manuell einen Spieler mitsamt Armee hinzufügen
  function AddPlayerArmy($isattacker, $playerid, $playername, $army) {
    if ($isattacker == true) {
      $this->attackers[$playerid] = $playername;
      foreach ($army as $id => $onearmy) {
        $onearmy['player'] = $playerid;
        $this->attarmys[] = $onearmy;
      }
    } else {
      $this->defenders[$playerid] = $playername;
      foreach ($army as $id => $onearmy) {
        $onearmy['player'] = $playerid;
        $this->defarmys[] = $onearmy;
      }
    }
  }
  //Einlesen von DB
  function AddCityFromDB($cityid) {
    //stadtbesitzer und bevölkerung ermitteln
    $rescityowner = "SELECT city.owner AS id, player.name AS name, population,prosperity FROM city LEFT JOIN player ON player.id = city.owner WHERE city.id=".$cityid;
    $this->cityowner = mysqli_fetch_assoc($rescityowner);
    //Stadtbevölkerung setzen
    $this->citypopulation = $this->cityowner['population'];
    $this->cityprosperity = $this->cityowner['prosperity'];
    //Stadtbevölkerung aus cityowner wieder löschen ;)
    unset ($this->cityowner['population']);
    //alle Verteidiger ermitteln
    $resdefers = do_mysql_query("SELECT DISTINCT owner AS id, player.name AS name FROM cityunit, player WHERE player.id = owner AND city=".$cityid);
    while ($defender = mysqli_fetch_assoc($resdefers)) {
      //Armee dieses Verteidiger ermitteln
      $cityunits = do_mysql_query("SELECT unit,count FROM cityunit WHERE city=".$cityid." AND owner=".$defender['id']);
      $df = array ();
      while ($units = mysqli_fetch_assoc($cityunits)) {
        $df[]['id'] = $units['unit'];
        $df[]['count'] = $units['count'];
        $df[]['player'] = $defender['id'];
      }
      //Verteidiger hinzufügen
      $this->AddPlayerArmy(false, $defender['id'], $defender['name'], $df);
      if (DEBUG)
        echo " Defender:".$defender['name']." ";
      //Defbonus ermitteln
      $resdeffbonus = do_mysql_query("SELECT sum(res_defense) FROM citybuilding,building WHERE citybuilding.building=building.id AND city=".$end);
      if ($deffbonus = mysqli_fetch_array($resdeffbonus))
        $this->defbonus = $deffbonus[0] ? $deffbonus[0] : 0;
      else
        $this->defbonus = 0;
    }
  }
  function AddAtterFromDB($armyid) {
    //Name ermitteln
    $resarmyowner = "SELECT army.owner AS id, player.name AS name FROM army, player WHERE player.id = army.owner AND army.aid=".$armyid;
    $armyowner = mysqli_fetch_assoc($resarmyowner);
    //Armee ermitteln
    $resatter = do_mysql_query("SELECT unit, count FROM armyunit WHERE aid=".$armyid);
    while ($atter = mysqli_fetch_assoc($resatter)) {
      $at[$i]['id'] = $atter['unit'];
      $at[$i]['count'] = $atter['count'];
      $at[$i]['player'] = $armyowner['id'];
    }
    //Hinzufügen
    $this->AddPlayerArmy(true, $armyowner['id'], $armyowner['name'], $at);
  }
  //Kampf beginnen
  function Fight() {
    //Attacker ermitteln
    $attackerid;
    foreach ($this->attackers as $id => $name)
      $attackerid = $id;
    //Kampf berechnen
    $erg = fight($this->attarmys, $this->defarmys, $this->defbonus, $this->tactic);
    //Unentschieden?
    if ($erg == NULL || $erg == false) {
      if (DEBUG)
        echo " No one wins ";
      $this->result = Fight_Draw;
      $this->survived_defarmys = null;
      $this->survived_attarmys = null;
    }
    //Verteidiger gewonnen
    elseif ($erg[0]['player'] != $attackerid) {
      if (DEBUG)
        echo " DEFENDER wins ";
      $this->result = Fight_DefWin;
      foreach($erg as $key => $value)
        $this->survived_defarmys[$key] = $value;
      $this->survived_attarmys = null;
    }
    //Angreifer gewonnen
    else {
      if (DEBUG)
        echo " ATTACKER wins ";
      $this->result = Fight_AttWin;
      $this->survived_deffarmys = null;
      foreach($erg as $key => $value)
        $this->survived_attarmys[$key] = $value;      
    }
    return $this->result;
  }
  function GetMessageFor($isattacker, $playerid) {
    $msg = "";
    //Tageszeit angeben
    $hour = date("H", time());
    // Morgenstunden
    if ($hour >= 5 && $hour < 9) {
      $msg .= "Der Nebel der Nacht verzieht sich langsam und euch wird bewusst das ein grausamer Kampf vor euch steht.";
    }
    // Nachtstunden
    elseif ($hour < 5 || $hour > 21) {
      $msg .= "Die Nacht wird von den Fackeln der Armeen erhellt die sich zu dieser Schlacht treffen.";
    }
    // Tag
    else {
      $msg .= "Ihr seht über das Schlachtfeld und euch wird bewusst das hier bald etliche Tote und Verwundete liegen.";
    }
    $msg .= "\n";
    //    $msg .= get_long_population_string($this->citypopulation, true);
    $msg .= get_population_string($this->citypopulation, $this->cityprosperity, true);
    $msg .= " ".$this->cityname." ";
    if ($playerid == $this->cityowner['id']) {
      $msg .= " in euerem Besitz ";
    } else {
      $msg .= " im Besitz von ";
      $msg .= $this->cityowner['name'];
    }
    if ($this->defbonus <= 0)
      $msg .= " ist nicht befestigt";
    elseif ($this->defbonus <= 20) $msg .= " ist leicht befestigt";
    elseif ($this->defbonus <= 50) $msg .= " ist gut befestigt";
    elseif ($this->defbonus <= 100) $msg .= " ist stark befestigt";
    else
      $msg .= " gleicht einer Festung";
    $msg .= ".\n\n";
    $attackername;
    foreach ($this->attackers as $id => $name)
      $attackername = $name;
    if ($isattacker == true)
      $msg .= "Euer Heer besteht aus folgenden Einheiten:\n";
    else
      $msg .= "Ihr zählt auf Seiten eueres Angreifers ".$attackername." folgendes Heer:\n";
    
    foreach ($this->attarmys as $id => $onearmy) {
      $res1 = mysqli_query($GLOBALS['con'], "SELECT name FROM unit WHERE id = '".$onearmy['id']."'");
      $data1 = mysqli_fetch_assoc($res1);
      $msg .= $data1['name'].": ".$onearmy['count']."\n";
    }
    $msg .= "\n";
    if ($isattacker == true)
      $msg .= "Die Verteidiger bieten folgendes Heer auf:\n";
    else
      $msg .= "Ihr zählt auf Seiten der tapferenen Verteidiger folgendes Heer:\n";
    foreach ($this->defarmys as $id => $onearmy) {
      $res1 = mysqli_query($GLOBALS['con'], "SELECT name FROM unit WHERE id = '".$onearmy['id']."'");
      $data1 = mysqli_fetch_assoc($res1);
      $msg .= $data1['name'].": ".$onearmy['count']." von ".$this->defenders[$onearmy['player']]."\n";
    }
    $msg .= "\n";
    if ($this->tactic == 0) {
      $tacticmsg = "offensiv";
    }
    elseif ($this->tactic == 1) {
      $tacticmsg = "defensiv";
    }
    elseif ($this->tactic == 2) {
      $tacticmsg = "erstürmen";
    }
    //Angriffsart angeben
    if ($isattacker == true) {
      if ($this->tactic != 2)
        $msg .= "Ihr schickt euere Truppen ".$tacticmsg." gegen die Verteidiger von ".$this->cityname.".";
      else
        $msg .= "Euere Truppen beginnen ".$cityname." zu ".$tacticmsg.".";
    } else {
      if ($this->tactic != 2)
        $msg .= $this->cityname." wird von ".$attackername." ".$tacticmsg." angegriffen.";
      else
        $msg .= "Die Truppen von ".$attackername." beginnen ".$this->cityname." zu ".$tacticmsg.".";
    }
    $msg .= "\n\n";
    if ($this->result != Fight_Draw) {
      $survived_armys = array ();
      if ($this->result == Fight_AttWin) {
        foreach($this->survived_attarmys as $key => $value)
          $survived_armys[$key] = $value;
        if ($isattacker == true)
          $msg .= "My Lord! Euere Truppen waren erfolgreich. Ihr habt gesiegt!";
        else
          $msg .= "Es war ein Dunkler Tag für euer Reich, Mylord.\nEure Truppen konnten den Sieg leider nicht erringen.";
      } else {
        foreach($this->survived_defarmys as $key => $value)
          $survived_armys[$key] = $value;
        if ($isattacker == true)
          $msg .= "Es war ein Dunkler Tag für euer Reich, Mylord.\nEure Truppen konnten den Sieg leider nicht erringen.";
        else
          $msg .= "My Lord! Euere Truppen waren erfolgreich. Ihr habt gesiegt!";
      }
      $msg .= "\n\nFolgende Einheiten überlebten die Schlacht:\n";
      foreach ($survived_armys as $id => $onearmy) {
        $res1 = mysqli_query($GLOBALS['con'], "SELECT name FROM unit WHERE id = '".$onearmy['id']."'");
        $data1 = mysqli_fetch_assoc($res1);
        if ($this->result == Fight_AttWin)
          $msg .= $data1['name'].": ".$onearmy['count']."\n";
        else
          $msg .= $data1['name'].": ".$onearmy['count']." von ".$this->defenders[$onearmy['player']]."\n";
      }
    } else
      $msg .= "Die Schlacht ist zuende und niemand hat sie überlebt. Was für ein grausamer Tag.";
    return $msg;
  }
}
?>