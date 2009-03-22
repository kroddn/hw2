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
 0: hostile
 1: neutral
 2: friendly
 */
include_once ("includes/diplomacy.common.php");
include_once ("includes/util.inc.php");

class diplomacy {

  var $player;
  var $name;
  var $religion;

  //no input checking
  function Diplomacy($player, $name, $religion) {
    $this->player = $player;
    $this->name = $name;
    $this->religion = $religion;
  }

  //clean input
  function message($player, $header, $text) {
    do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".intval($player).", UNIX_TIMESTAMP(),'".mysql_escape_string($header)."','".mysql_escape_string($text)."',1)");
    do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".intval($player) );
  }

  function clanDiplo($id1, $id2, $type) {
    $id1 = intval($id1);
    $id2 = intval($id2);

    $res1 = do_mysql_query("SELECT id,name,clan FROM player WHERE id=".$id1);
    $res2 = do_mysql_query("SELECT id,name,clan FROM player WHERE id=".$id2);
    if (($data1 = mysql_fetch_assoc($res1)) &&
        ($data2 = mysql_fetch_assoc($res2)) &&
        ($data1['clan'] > 0) &&
        ($data2['clan'] > 0))    
        {
          if (($data1['clan'] == $data2['clan']) && $type == 0) {
            $res7 = do_mysql_query("SELECT id FROM player WHERE clan=".$data1['clan']."  AND clanstatus & 4");;
            while($data7 = mysql_fetch_assoc($res7))
            $this->message($data7['id'], "Bruderkrieg zwischen ".$data1['name']." und ".$data2['name'].".", "Der Spieler ".$data1['name']." hat seinem und eurem Ordensbruder ".$data2['name']." den Krieg erklärt.");
          }
          $res3 =do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$data1['clan']." AND id2=".$data2['clan'].") OR (id1=".$data2['clan']." AND id2=".$data1['clan'].")");
          if ($data3 = mysql_fetch_assoc($res3)) {
            $res4 = do_mysql_query("SELECT name FROM clan WHERE id=".$data1['clan']);
            $res5 = do_mysql_query("SELECT name FROM clan WHERE id=".$data2['clan']);
            if (($data4= mysql_fetch_assoc($res4)) && ($data5 = mysql_fetch_assoc($res5))) {
              if (($data3['type'] == 0) && ($type == 2)) {

                $res7 = do_mysql_query("SELECT id FROM player WHERE (clan=".$data1['clan']." OR clan=".$data2['clan'].") AND clanstatus & 4") or die(mysql_error());
                while($data7 = mysql_fetch_assoc($res7))
                $this->message($data7['id'], "Krieg von ".$data4['name']." und ".$data5['name']." unterlaufen.", "Die Spieler ".$data1['name']." und ".$data2['name']." haben trotz eures Ordenskrieges ein Bündnis geschlossen.");

              } else if (($data['type'] == 2) && ($type == 0)) {
                $res7 = do_mysql_query("SELECT id FROM player WHERE (clan=".$data1['clan']." OR clan=".$data2['clan'].") AND clanstatus & 4") or die(mysql_error());
                while($data7 = mysql_fetch_assoc($res7))
                $this->message($data7['id'], "NAP von ".$data4['name']." und ".$data5['name']." gebrochen.", "Durch eine Kriegserklärung von ".$data1['name']." vom Orden ".$data4['name']." gegen ".$data2['name']." vom Orden ".$data5['name']." wurde eurer NAP verletzt.");

              } else if (($data['type'] == 3) && ($type == 0)) {
                $res7 = do_mysql_query("SELECT id FROM player WHERE (clan=".$data1['clan']." OR clan=".$data2['clan'].") AND clanstatus & 4") or die(mysql_error());
                while($data7 = mysql_fetch_assoc($res7))
                $this->message($data7['id'], "Bündnis von ".$data4['name']." und ".$data5['name']." gebrochen.", "Durch eine Kriegserklärung von ".$data1['name']." vom Orden ".$data4['name']." gegen ".$data2['name']." vom Orden ".$data5['name']." wurde eurer Bündnis gebrochen.");
              }
            }
          }
        } // if data1...data2
  }

  function neutral($pid) {
    $pid = intval($pid);
    $res1 = do_mysql_query("SELECT id,name FROM player WHERE id=".$pid) ;
    if (($this->player != $pid) && ($data1=mysql_fetch_assoc($res1))) {
      $res2 = do_mysql_query("SELECT type FROM relation WHERE (id1=".$pid." AND id2=".$this->player.") OR (id1=".$this->player." AND id2=".$pid.")");
      if ($data2 = mysql_fetch_assoc($res2)) {
        if ($data2['type'] == 0) {
          setDBreq_rel("req_relation", $this->player, $pid, 1);
          $this->message($pid, "Friedensangebot von ".$this->name, "Der Spieler ".$this->name." hat euch ein Friedensangebot gemacht.");
          $this->message($this->player, "Friedensangebot an ".$data1['name'], "Ihr habt dem Spieler ".$data1['name']." ein Friedensangebot gemacht.");
        } else {
          delDBrel("relation", $pid, $this->player);
           
          if ($data2['type'] == 2) {
            $this->message($pid, $this->name." kündigt Bündnis", "Der Spieler ".$this->name." hat euer Bündnis gekündigt.");
            $this->message($this->player, "Bündnis mit ".$data1['name']." gekündigt", "Ihr habt das Bündnis mit ".$data1['name']." gekündigt");
          }
        }
      }
    }
  }

  // Returns NULL if no problems arise
  function canNotWar($id1, $id2) {
    $id1 = intval($id1);
    $id2 = intval($id2);
    
    $res = do_mysql_query("SELECT unit.name AS uname,cityunit.count, city.name AS cname, p1.name AS pname FROM unit,cityunit,city,player AS p1,player AS p2 WHERE unit.id=cityunit.unit AND cityunit.city=city.id AND p1.id=cityunit.owner AND p2.id=city.owner AND ((p1.id=".$id1." AND p2.id=".$id2.") OR (p1.id=".$id2." AND p2.id=".$id1."))");
    if (mysql_num_rows($res)) {
      while ($data = mysql_fetch_assoc($res)) {
        $msg .= $data1['count']." ".$data['uname']." von ".$data['pname']." in ".$data['cname']."\n";
      }
      return $msg;
    }


    // Nachprüfen, ob bereits Truppenbewegungen zum zukünftigen Gegner unterwegs sind.
    $res = do_mysql_query("SELECT count(*) AS cnt".
			  " FROM army LEFT JOIN city AS endcity ON army.end = endcity.id".
			  " WHERE army.owner = $id1 AND endcity.owner = $id2"
    );
    $data = mysql_fetch_assoc($res);
    if($data['cnt'] > 0) {
      return "Ihr habt noch ".$data['cnt']." Truppenbewegung(en) zu Städten dieses Herrschers aktiv. ".	
	         "Euer Ehrgefühl verlangt, dass Ihr Angriffe erst nach einer Kriegserklärung startet.";
    }

    // Kein Fehler. Return einfach
    return NULL;
  }

  function hostile($pid) {
    $pid = intval($pid);
    $res1 = do_mysql_query("SELECT name FROM player WHERE id=".$pid);
    if (($this->player != $pid) && ($data1=mysql_fetch_assoc($res1))) {
      if ($msg = $this->canNotWar($this->player, $pid))
      $this->message($this->player, "Kriegserklärung an ".$data1['name']." fehlgeschlagen", "Um ".$data1['name']." den Krieg erklären zu können müssen sich folgende Truppen zurückziehen:\n\n".$msg);
      else {
        // Allen Armeen mit Ausgangspunkt Feind und Laufzeit kleiner
        // als die Scoutzeit eine neue Ausgangsstadt zuteilen.
        $resA = do_mysql_query("SELECT city.id, city.name AS cname, x,y,army.* FROM army ".
			      " LEFT JOIN city ON city.id = army.start LEFT JOIN map ON map.id = city.id ".
			      " WHERE city.owner = ".$pid." AND army.owner = ".$this->player);
        while($cit = mysql_fetch_assoc($resA)) {
          // Die nächste eigenen Stadt finden
          $speed= do_mysql_query_fetch_assoc("SELECT min(speed) AS speed ".
					     "FROM armyunit LEFT JOIN unit ON unit.id = armyunit.unit ".
					     "WHERE armyunit.aid = ".$cit['aid']);
           
          $next = getNearestOwnCity($cit['x'],$cit['y'],$this->player);
          $time = ceil($next[1] * 3600 / ARMY_SPEED_FACTOR * $GLOBALS['speedmatrix'][$speed['speed']]);
           
          // DEBUG
          if($_SESSION['player']->isAdmin()) {
            echo "<p><pre>";
            var_dump($cit);
            printf ("\nNearest: %d:%d Speed: %d Time: %d\n", $next[2], $next[3], $speed['speed'], $time);
            echo "</pre>";
          }
           
          if($next[0]) {
            do_mysql_query("UPDATE army SET start = ". $next[0].", starttime=starttime-".$time.
			   " WHERE aid = ".$cit['aid']);
          }
        }

        setDBrel("relation", $this->player, $pid, 0);
        delDBreq_rel("req_relation", $this->player, $pid);
        $this->message($pid, "Kriegserklärung von ".$this->name, "Der Spieler ".$this->name." hat euch den Krieg erklärt, möge der bessere gewinnen.");
        $this->message($this->player, "Kriegserklärung an ".$data1['name'], "Ihr habt dem Spieler ".$data1['name']." den Krieg erklärt, möge der bessere gewinnen.");
        $this->clanDiplo($this->player, $pid, 0);

        // Neulingsschutz deaktivieren
        $_SESSION['player']->setNoobLevel(0);

      }
    }
  }

  function canNotBND($id1, $id2) {
    $res = do_mysql_query("SELECT city.name AS cname,p1.name AS pname FROM city,army,player AS p1, player AS p2 WHERE city.id=army.end AND p1.id=city.owner AND p2.id=army.aid AND ((p1.id=".$id1." AND p2.id=".$id2.") OR (p1.id=".$id2." AND p2.id=".$id1.")) AND army.mission IN ('attack', 'despoil', 'burndown')");
    if (mysql_num_rows($res)) {
      while ($data = mysql_fetch_assoc($res)) {
        $msg .= "Angriff von ".$data['pname']." auf ".$data['cname']."\n";
      }
      return $msg;
    } else
    return NULL;
  }

  function offerBND($pid) {
    $pid = intval($pid);
    
    $res1 = do_mysql_query("SELECT id,name,religion FROM player WHERE id=".$pid);
    if (($data1 = mysql_fetch_assoc($res1)) && ($this->religion == $data1['religion'])) {
      if ($msg = $this->canNotBND($this->player,$pid)) {
        $this->message($this->player, "Bündnisangebot an ".$data1['name']." fehlgeschlagen", "Ihr wollt dem Spieler ".$data1['name']." ein Bündnisangebot unterbreiten, folgende Angriffe stehen dem noch entgegen:\n\n".$msg);
        return "Fehlgeschlagen. Prüft Eure <a href=\"messages.php\">Nachrichten</a>!";
      }
      else {
        setDBreq_rel("req_relation", $this->player, $pid, 2);
        $this->message($pid, "Bündnisangebot von ".$this->name, "Der Spieler ".$this->name." hat euch ein Bündnisangebot gemacht.");
        $this->message($this->player, "Bündnisangebot an ".$data1['name'], "Ihr habt dem Spieler ".$data1['name']." ein Bündnisangebot gemacht.");
        return null;
      }
    }
    else {
      $m = "Wehe, du willst einen Bündnis mit einem Ungläugigen, mögen dir die Gläubigen jedes Haar einzeln ausrupfen!";
      $this->message($this->player, "Bündnisangebot an Ungläubigen", $m);
      return $m;
    }
  }

  function friendly ($pid) {
    $pid = intval($pid);

    if($this->player == $pid) return "Es mag ja lustig sein, Verträge mit sich selbst abzuschließen, ist aber diesem Spiel nicht dienlich.";
    
    $res1 = do_mysql_query("SELECT id,name,religion FROM player WHERE id=".$pid);
    if( $data1 = mysql_fetch_assoc($res1) ) {
      $res2 = do_mysql_query("SELECT type FROM relation WHERE (id1=".$pid." AND id2=".$this->player.") OR (id1=".$this->player." AND id2=".$pid.")");
      if ($data2 = mysql_fetch_assoc($res2)) {
        if ($data2['type'] <2) {
          if ($data2['type'] == 0) {
            setDBreq_rel("req_relation", $this->player, $pid, 1);
            $this->message($pid, "Friedensangebot von ".$this->name, "Der Spieler ".$this->name." hat euch ein Friedensangebot gemacht.");
            $this->message($this->player, "Friedensangebot an ".$data1['name'], "Ihr habt dem Spieler ".$data1['name']." ein Friedensangebot gemacht.");
            return null;
          }
          else {
            return $this->offerBND($pid);
          }
        }
      }
      else{
        return $this->offerBND($pid);
      }
    }
    else {
      return "Dieser Spieler existiert nicht.";
    }
  }

  function accReqRelation($pid) {
    $pid = intval($pid);
    
    if($this->player == $pid) return "Es mag ja lustig sein, Verträge mit sich selbst abzuschließen, ist aber diesem Spiel nicht dienlich.";
    
    $res1 = do_mysql_query("SELECT id,name FROM player WHERE id=".$pid);
    if( $data1 = mysql_fetch_assoc($res1) ) {
      $res2 = do_mysql_query("SELECT type FROM req_relation WHERE (id1=".$pid.") AND (id2=".$this->player.")");
      $res3 = do_mysql_query("SELECT type FROM relation WHERE (id1=".$pid." AND id2=".$this->player.") OR (id1=".$this->player." AND id2=".$pid.")");
      if ($data2 = mysql_fetch_assoc($res2)) {
        if ($data3 = mysql_fetch_assoc($res3)) {
          if ($data2['type'] > $data3['type']) {
            setDBrel("relation", $this->player, $pid, $data2['type']);
            delDBreq_rel("req_relation", $pid, $this->player);
            if ($data2['type'] == 1) {
              $this->message($pid, "Frieden mit ".$this->name, "Der Spieler ".$this->name." hat euer Friedensangebot angenommen, mögen eure Völker fortan in Frieden und Eintracht leben.");
              $this->message($this->player, "Frieden mit ".$data1['name'], "Ihr habt das Friedensangebot von ".$data1['name']." angenommen, mögen eure Völker fortan in Frieden und Eintracht leben.");
            } else {
              $this->message($pid, "Bündnis von ".$this->name." angenommen", "Der Spieler ".$this->name." hat euer Bündnisangebot angenommen.");
              $this->message($this->player, "Bündnisangebot von ".$data1['name']." angenommen", "Ihr habt das Bündnisangebot von ".$data1['name']." angenommen.");
              $this->clanDiplo($this->player, $pid, 2);
            }
          } else if ($data2['type'] == 2) {
            setDBrel("relation", $this->player, $pid, $data2['type']);
            delDBreq_rel("req_relation", $pid, $this->player);
            $this->message($pid, "Bündnis von ".$this->name." angenommen", "Der Spieler ".$this->name." hat euer Bündnisangebot angenommen.");
            $this->message($this->player, "Bündnisangebot von ".$data1['name']." angenommen", "Ihr habt das Bündnisangebot von ".$data1['name']." angenommen.");
            $this->clanDiplo($this->player, $pid, 2);
          }
        } 
        else if ($data2['type'] == 2) {
          setDBrel("relation", $this->player, $pid, $data2['type']);
          delDBreq_rel("req_relation", $pid, $this->player);
          $this->message($pid, "Bündnis von ".$this->name." angenommen", "Der Spieler ".$this->name." hat euer Bündnisangebot angenommen.");
          $this->message($this->player, "Bündnisangebot von ".$data1['name']." angenommen", "Ihr habt das Bündnisangebot von ".$data1['name']." angenommen.");
          $this->clanDiplo($this->player, $pid, 2);
        }
      } // $res2
      else {
        return "Ihr habt keine Relation zu diesem Spieler.";
      }
    }
    else {
      return "Dieser Spieler existiert nicht.";
    }
    
    return null;
  }

  function delReqRelation($pid, $type) {
    $pid = intval($pid);
    $res1 = do_mysql_query("SELECT id,name FROM player WHERE id=".$pid);
    if ($data1 = mysql_fetch_assoc($res1)) {
      //abuse of delDBrel, not a bug
      $result = delDBrel("req_relation", $pid, $this->player);
      if($result == 0) return "Es besteht kein Angebot an diesen Spieler.";
      
      if ($type== 1) {
        $this->message($pid, "Friedensangebot von ".$this->name." abgelehnt", "Der Spieler ".$this->name." hat euer Angebot um Frieden ausgeschlagen.");
        $this->message($this->player, "Friedensangebot von ".$data1['name']." abgelehnt", "Ihr habt das Friedensangebot des Spielers ".$data1['name']." ausgeschlagen.");
      } else if ($type== -1) {
        $this->message($pid, "Friedensangebot von ".$this->name." zurückgezogen", "Der Spieler ".$this->name." hat sein Friedensangebot zurückgezogen.");
        $this->message($this->player, "Friedensangebot an ".$data1['name']." zurückgezogen", "Ihr habt das Friedensangebot an den Spieler ".$data1['name']." zurückgezogen.");
      } else if ($type == 2) {
        $this->message($pid, "Bündnisangebot von ".$this->name." abgelehnt", "Der Spieler ".$this->name." hat euer Angebot um ein Bündnis ausgeschlagen.");
        $this->message($this->player, "Bündnisangebot von ".$data1['name']." abgelehnt", "Ihr habt das Bündnisangebot des Spielers ".$data1['name']." ausgeschlagen.");
      } else if ($type== -2) {
        $this->message($pid, "Bündnisangebot von ".$this->name." zurückgezogen", "Der Spieler ".$this->name." hat sein Bündnisangebot zurückgezogen.");
        $this->message($this->player, "Bündnisangebot an ".$data1['name']." zurückgezogen", "Ihr habt das Bündnisangebot an den Spieler ".$data1['name']." zurückgezogen.");
      }
    }
    else {
      return "Dieser Spieler existiert nicht.";
    }
    
    return null;
  }

  
  function changeRelation($playername, $type) {
    if (!checkBez($playername, 2, 40)) return "Ungültiger Spielername";
    $res = do_mysql_query("SELECT id,nooblevel FROM player WHERE name='".mysql_escape_string($playername)."'");

    if ($data = mysql_fetch_assoc($res)) {
      $type = intval($type);
      if ($type == 0) {
        $res = do_mysql_query("SELECT * FROM relation ".
			    " WHERE type=0 AND (id1 = ".$data['id']." AND id2 = ".$this->player.
			    " OR id2 = ".$data['id']." AND id1 = ".$this->player.")");
        if(mysql_num_rows($res)>0) return "Ihr befindet euch bereits im Krieg mit diesem Spieler";

        // Wenn das Nooblevel des Ziels zu hoch ist dann keinen Angriff zulassen
        if ($data['nooblevel'] > 0 && $data['nooblevel'] >= $_SESSION['player']->getNoobLevel() ) {
          return "Der Spieler befindet sich noch im Neulingsschutz, daher könnt Ihr den Spieler nicht angreifen!";
        }
        else {
          return $this->hostile($data['id']);
        }
      }
      else if($type == 2) {
        return $this->friendly($data['id']);
      }
      else if($type == 1) {
        return $this->neutral($data['id']);
      }
    }
    else {
      return "Dieser Spieler existiert nicht.";
    }

    return null;
  }
}

?>
