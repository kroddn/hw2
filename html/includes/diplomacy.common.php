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


function delDBrel($table, $id1, $id2) {
  $id1 = intval($id1);
  $id2 = intval($id2);
  do_mysql_query("DELETE FROM ".$table." WHERE (id1=".$id1." AND id2=".$id2.") OR (id1=".$id2." AND id2=".$id1.")");
}

function delDBreq_rel($table, $id1, $id2) {
  $id1 = intval($id1);
  $id2 = intval($id2);
  do_mysql_query("DELETE FROM ".$table." WHERE (id1=".$id1." AND id2=".$id2.") OR (id2=".$id1." AND id1=".$id2.")");
}

function setDBrel($table, $id1, $id2, $type) {
  $id1 = intval($id1);
  $id2 = intval($id2);
  $type = intval($type);
  
  delDBrel($table, $id1, $id2);
  do_mysql_query("INSERT INTO ".$table." (id1,id2,type) VALUES (".$id1.",".$id2.",".$type.")");
}

function setDBreq_rel($table, $id1, $id2, $type) {
  $id1 = intval($id1);
  $id2 = intval($id2);
  $type = intval($type);
  
  delDBreq_rel($table, $id1, $id2);
  do_mysql_query("INSERT INTO ".$table." (id1,id2,type) VALUES (".$id1.",".$id2.",".$type.")");
}


//0 == Feind
//1 == Neutral
//2 == Verbündet
function getRel($id1, $id2) {
  $id1 = intval($id1);
  $id2 = intval($id2);
  
  $res = do_mysql_query("SELECT type FROM relation WHERE (id1=".$id1." AND id2=".$id2.") OR (id1=".$id2." AND id2=".$id1.")");
  if ($data = mysql_fetch_assoc($res))
    return $data['type'];
  else 
    return 1;
}

//0 == Feind
//1 == Neutral oder wenn einer der Spieler keinen Clan hat!
//2 == NAP
//3 == Verbündet
//player ID's
function getClanRel($id1,$id2) {
  $id1 = intval($id1);
  $id2 = intval($id2);
  
  if ($id1 > 0 && $id2 > 0) {
    $clans_res = do_mysql_query("SELECT clan FROM player WHERE id=".$id1." OR id=".$id2);
    if (($clan1 = mysql_fetch_assoc($clans_res)) && ($clan2 = mysql_fetch_assoc($clans_res)) && $clan1['clan'] && $clan2['clan']) {
      $res = do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$clan1['clan']." AND id2=".$clan2['clan'].") OR (id1=".$clan2['clan']." AND id2=".$clan1['clan'].")");
      if ($data = mysql_fetch_assoc($res))
	return $data['type'];
      else 
	return 1;
    } else
      return 1;
  } else
    return 1;
}

//Funktion die in service.inc.php benützt wird um herauszufinden ob gekämpft wird!
//Sofern der Spieler mit dem anderen Spieler nicht verbündet ist, aber die Orden verbündet sind, wird neutral zurückgegeben.
//Return Wert:
//0 == Feind
//1 == Neutral
//2 == Verbündet
function getWarRel($id1, $id2) {  
  if (!$id1)
    log_fatal_error("getWarRel() id1 unset");
  if (!$id2)
    log_fatal_error("getWarRel() id2 unset");

  $id1 = intval($id1);
  $id2 = intval($id2);
    
  //Spielerbeziehung ermitteln
  $rel = getPlayerRelation($id1,$id2);
  switch($rel)
  {
    //Beim Aufruf von getWarRel sollte es nicht vorkommen das $id1 gleich $id2 ist
    case 5:
      log_fatal_error("getWarRel() should not be run with same ids");
    //Falls es ein Ordensbruder ist, dann ist man mit ihm verbündet
    case 4:
    //Falls Orden verbündet oder normales Bündnis => verbündet
    case 3:
      return 2;
    //NAP = neutral
    case 2:
      return 1;
    //An sich nurnoch Krieg hier möglich ;)
    default:
      return $rel;
  }
}

/* Spieler Beziehung ermitteln
 *  Wird  in map.v3.class.php und aufwerts eingesetzt um die Farben der Städte
 *  anzuzeigen
 *  param:  2 PlayerID's
 *  return: 0 = Krieg
 *          1 = neutral
 *          2 = NAP
 *          3 = Bund 
 *          4 = Ordensbruder 
 *          5 = gleicher Spieler */
function getPlayerRelation($id1, $id2) {
  // gleicher Spieler
  if ( $id1 == $id2 ) {    
    return 5;
  }
  
  //Spielerbeziehung heranziehen
  $rel = getRel($id1, $id2);
  
  //Falls die Beziehung verbündet ist, dann auf 3 setzen
  if($rel == 2)
    return 3;
  
  //Falls neutrale Beziehung. Ordensbeziehung heranziehen
  if($rel == 1)
  {
    // Orden der Spieler ermitteln
    $ressameclan = do_mysql_query( 'SELECT player1.clan=player2.clan as type '.
           'FROM player as player1, player as player2 '.
           'WHERE player1.id ='.$id1.' AND player2.id='.$id2 );
    $sameclan = mysql_fetch_assoc($ressameclan);    
    //Sind die beiden im selben Orden?
    if($sameclan['type'] == 1)
      return 4;
    else
      //Wenn nicht, Ordensbeziehung heranziehen
      $rel = getClanRel($id1, $id2);
  }
  
  return $rel;
}  
?>
