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

function get_adm_level($id) {
  $res=do_mysql_query("SELECT max(research.management) FROM research, playerresearch WHERE research.id = playerresearch.research AND playerresearch.player = ".intval($id) );
  $data=mysql_fetch_row($res);
  return $data[0];
}

function get_eff($citycount, $level) {
  $effcost[1]=array(3,0.3);
  $effcost[2]=array(5,0.25);
  $effcost[3]=array(7,0.2);
  $effcost[4]=array(9,0.15);
  $effcost[5]=array(11,0.1);
  $effcost[6]=array(15,0.1);
  
  return max(0.1,(1-max(0,($citycount-$effcost[$level][0])*$effcost[$level][1])));
}

function get_inc_res($raw, $capacity, $consum, $storage, $prodfactor) {
  //raw enthält die gelagerten Res UND die aktuelle Produktion
  if ($raw<0) {
    $res['res'] = 0;
    $res['raw'] = $raw;
  } else {
    if ($raw >= (floor($capacity * $prodfactor) * $consum)) {
      $res['res'] = floor($capacity * $prodfactor);
      $res['raw'] = floor(min($storage, $raw - ($res['res']*$consum)));
    } else {
      $res['res'] = floor($raw/$consum);
      $res['raw'] = floor($raw % $consum);
    }
  }
  return $res;
}


function get_inc_wep($raw, $capacity, $consum, $storage, $reserve_storage, $stock, $prodfactor, $limit) {
  $max = floor(min($capacity*$prodfactor, max(0, $limit-$stock-$reserve_storage)));  
  //raw enthält die gelagerten Res UND die aktuelle Produktion
  if ($max + $stock > $storage) {
    $max = $storage - $stock;
  }

  if ($max < 0) {
    $res['res'] = $max;
    $res['raw'] = $raw;
  }
  else if ($raw<0) {
    $res['res'] = 0;
    $res['raw'] = $raw;
  }
  else {
    if ($raw >= ceil($consum * $max)) {
      $res['res'] = $max;
      $res['raw'] = $raw - ceil($res['res']*$consum);
    }
    else {
      $res['res'] = floor($raw/$consum);
      $res['raw'] = floor($raw % $consum);
    }
  }

  return $res;
}

function get_city_ucost($city, $pid) {
  $pid = intval($pid);
  $res = do_mysql_query("SELECT round(sum(count*cost), 2) FROM city LEFT JOIN cityunit ON city.id=cityunit.city LEFT JOIN unit ON cityunit.unit=unit.id WHERE city.id=".$city." AND cityunit.owner=".$pid);
  $data = mysql_fetch_row($res);
  return $data[0];
}

function get_external_cucost($pid) {
  $pid = intval($pid);
  $res = do_mysql_query("SELECT round(sum( count * cost ), 2) FROM city LEFT JOIN cityunit ON city.id = cityunit.city LEFT JOIN unit ON cityunit.unit = unit.id WHERE city.owner != ".$pid." AND cityunit.owner = ".$pid);
  $data = mysql_fetch_row($res);
  return $data[0];
}


/**
 * Armeekosten, laufende Männer.
 * Armeen, die bereits belagern, kosten nichts.
 */
function get_army_cost($pid) {
  $pid = intval($pid);
  $no_cost_siege = defined("SIEGE_ARMIES_NO_COST") && SIEGE_ARMIES_NO_COST;
	
  $army_res = do_mysql_query("SELECT round(sum(count * u.cost), 2) ".
                             " FROM armyunit au LEFT JOIN army a ON a.aid = au.aid LEFT JOIN unit u ON u.id = au.unit ".
                             ($no_cost_siege ? " WHERE NOT(a.mission = 'siege' AND a.endtime < UNIX_TIMESTAMP() ) " : "").
                             " AND a.owner=".intval($pid) );
  $army = mysql_fetch_row($army_res);


  return $army[0];
}



/**
 * Armeekosten Belagerungen
 * Armeen, die bereits belagern, kosten nichts. Dieser Wert ist also nur informativ
 */
function get_army_cost_siege($pid) {
  $pid = intval($pid);
  $army_res = do_mysql_query("SELECT round(sum(count * u.cost), 2) ".
                             " FROM armyunit au LEFT JOIN army a ON a.aid = au.aid LEFT JOIN unit u ON u.id = au.unit ".
                             " WHERE a.mission = 'siege' AND a.endtime < UNIX_TIMESTAMP() ".
                             "       AND a.owner=".$pid);
  $army = mysql_fetch_row($army_res);


  return $army[0];
}



/**
 * Truppenkosten, stehende Männer
 */
function get_ucost($pid) {
  $pid = intval($pid);
  $city_res = do_mysql_query("SELECT round(sum(count*cost)) FROM city LEFT JOIN cityunit ON city.id=cityunit.city LEFT JOIN unit ON cityunit.unit=unit.id WHERE cityunit.owner=".$pid);
  $city = mysql_fetch_row($city_res);

  $army = get_army_cost($pid);

  return $city[0]+$army;
}


/**
 * Berechne die Ordenssteuer fÃ¼r Orden $clan und Einkommen $gold.
 * FÃ¼llt die Variable $tax mit dem Steuersatz, falls nicht-Null
 */
function get_clan_tax($clan, $gold, &$tax) {
  $clantax = 0;
  $taxrate = 0;

  if ($clan) {
    $res=do_mysql_query("SELECT tax FROM clan WHERE id=".$clan);
    if ($data = mysql_fetch_row($res)) {
      if($data[0] > 0) {
         $taxrate = $data[0]*100;     

        $clantax = $gold*$data[0];
        $clantax = round(($clantax>0)?$clantax:0);
      }
    }
  }
  if($tax != null)
    $tax = $taxrate;

  return $clantax;
}

function log_clan_tax($pid, $clan, $gold) {
  $pid = intval($pid);
  
  $clanlogdata1 = do_mysql_query("SELECT * from clanlog where playerid=".$pid." and clan=".$clan);
  if ($get_clanlogdata1 = mysql_fetch_assoc($clanlogdata1)) {
    do_mysql_query("UPDATE clanlog SET tax=tax+".$gold.", time_tax=UNIX_TIMESTAMP() WHERE playerid=".$pid." AND clan=".$clan);
  } else {
    do_mysql_query("INSERT INTO `clanlog` (`playerid`,`clan`, `tax`, `time_tax`) VALUES (".$pid.",".$clan.",".$gold.", UNIX_TIMESTAMP() )");
  }
}

/*
function get_city_inc_gold($eff, $res_eff, $pop, $prod, $troop, $bld, $factor) {
  if ($bld > 0) {
    $res = floor($bld*$res_eff);
  } else {
    $res = floor($bld);
  }
  $res -= $prod;
  $res -= $troop;

  $res += floor($pop/10)*$factor*$eff;

  return $res;
}
*/

function get_city_research($eff, $pop, $research_ew, $research) {
  return round($eff*(round($pop/$research_ew)*$research)/2.);
}

function get_new_pop($food, $attr, $pop, $limit) {
  $newpop = $pop;
  if ($newpop > $food) {
    $newpop = floor(max($food, $newpop - ($newpop-$food)/4));
  }

  // Zuwachs / Verlust durch Attraktion berechnen
  $diff = ($attr-$newpop)/1000;
  if ($limit == NULL) {
    $limit=100000000;
  }
  if ($limit > $newpop) {
    $lim=$limit;
  } else {
    $lim=$newpop;
  }

  if ($diff>0) {
    $newpop=min($lim, $attr, ($newpop + ceil($diff)*2));
  } else {
    $newpop=max($attr,($newpop+floor($diff)*2));
  }

  return $newpop;
}

?>