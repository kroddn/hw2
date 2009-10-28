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

/**
 * function fight() - Kampfberechnung
 *  
 * Struktur Angreifer/Verteidiger/Ausgabe
 * $xxx[$i]['id'] = Unit-ID
 * $xxx[$i]['count'] = Unit-Anzahl
 * $xxx[$i]['player'] = Spieler-ID (wem die Einheit gehört: wichtig bei Belagerung mit mehreren Beteiligten)
 *
 *
 */
function fight($at, $df, $defensebonus, $tactic) {
    // Einheitsdaten einlesen
    $res1 = mysql_query("SELECT id, type, damage, bonus1, bonus2, bonus3, life FROM unit");
    while ($data1 = mysql_fetch_assoc($res1)) {
      // 0 = Offensiv
      if ($tactic == 0) {
        //$data1['damage'] = ceil($data1['damage'] * 1.1);
        //$data1['life'] = ceil($data1['life'] * 0.9);
        $data1['damage']= $data1['damage'];
        $data1['life']  = $data1['life'];
        $damagefactor   = 1.11;
        $lifefactor     = 0.9;
        $bonusfactor    = 1.05;
      }
      // 1 = Defensiv
      elseif ($tactic == 1) {
        //$data1['damage'] = ceil($data1['damage'] * 0.9);
        //$data1['life'] = ceil($data1['life'] * 1.1);
        $data1['damage']= $data1['damage'];
        $data1['life']  = $data1['life'];
        $damagefactor   = 0.91;
        $lifefactor     = 1.1;
        $bonusfactor    = 0.95;
      }
      // 2 = Erstürmen
      elseif ($tactic == 2) {
        //$data1['damage'] = ceil($data1['damage'] * 1.22);
        //$data1['life'] = ceil($data1['life'] * 0.75);
        $data1['damage']= $data1['damage'];
        $data1['life']  = $data1['life'];
        $damagefactor   = 1.15;
        $lifefactor     = 0.85;
        $bonusfactor    = 1.20;
      }
      
      $ud[$data1['id']]['type']   = $data1['type'];
      $ud[$data1['id']]['damage'] = $data1['damage'];
      $ud[$data1['id']]['bonus1'] = $data1['bonus1'];
      $ud[$data1['id']]['bonus2'] = $data1['bonus2'];
      $ud[$data1['id']]['bonus3'] = $data1['bonus3'];
      $ud[$data1['id']]['life']   = $data1['life'];
    }

    // Angreifer einlesen
    for ($i = 0; $i < sizeof($at); ++ $i) {
        $at[$i]['hitpoints'] = $at[$i]['count'] * $ud[$at[$i]['id']]['life'] * $lifefactor;
    }
    // Verteidiger einlesen
    for ($i = 0; $i < sizeof($df); ++ $i) {
        $df[$i]['hitpoints'] = $df[$i]['count'] * $ud[$df[$i]['id']]['life'];
    }
    // Vorbereitungen
    $combat = true;
    // Kampfrunden
    while ($combat) {
        // Die Fälle abdecken dass eine oder beide der Armeen besiegt wurde
        if (sizeof($df) == 0 && sizeof($at) != 0) {            
            return $at;
        }
        elseif (sizeof($at) == 0 && sizeof($df) != 0) {           
            return $df;
        }
        elseif (sizeof($at) == 0 && sizeof($df) == 0) {            
            return false;
        }
        
        // Ansonsten hier weiter im Schleifendurchlauf        
        $atcount = 0;
        $atmain = 0;
        $atbonus1 = 0;
        $atbonus2 = 0;
        $atbonus3 = 0;
        $dfcount = 0;
        $dfmain = 0;
        $dfbonus1 = 0;
        $dfbonus2 = 0;
        $dfbonus3 = 0;
        // Schaden errechnen
        for ($i = 0; $i < sizeof($at); ++ $i) {
            $atcount  += $at[$i]['count'];
            $atmain   += $at[$i]['count'] * $ud[$at[$i]['id']]['damage'] * $damagefactor;
            $atbonus1 += $at[$i]['count'] * $ud[$at[$i]['id']]['bonus1'];
            $atbonus2 += $at[$i]['count'] * $ud[$at[$i]['id']]['bonus2'];
            $atbonus3 += $at[$i]['count'] * $ud[$at[$i]['id']]['bonus3'];
        }
        for ($i = 0; $i < sizeof($df); ++ $i) {
            $dfcount += $df[$i]['count'];
            $dfmain += $df[$i]['count'] * $ud[$df[$i]['id']]['damage'];
            $dfbonus1 += $df[$i]['count'] * $ud[$df[$i]['id']]['bonus1'];
            $dfbonus2 += $df[$i]['count'] * $ud[$df[$i]['id']]['bonus2'];
            $dfbonus3 += $df[$i]['count'] * $ud[$df[$i]['id']]['bonus3'];
        }
        // Schadensreduzierung durch Defense-Bonus
        $atmain   *= 100 / ($defensebonus +100);
        $atbonus1 *= 100 * $bonusfactor / ($defensebonus +100);
        $atbonus2 *= 100 * $bonusfactor / ($defensebonus +100);
        $atbonus3 *= 100 * $bonusfactor / ($defensebonus +100);

        // Schaden verteilen		
        $tmpcount = 0;
        for ($i = 0; $i < sizeof($at); ++ $i) {
            $tmp = $at[$i]['hitpoints'] - ($at[$i]['count'] / $atcount) * $dfmain;
            if ($ud[$at[$i]['id']]['type'] == 1)
                $tmp -= ($at[$i]['count'] / $atcount) * $dfbonus1;
            elseif ($ud[$at[$i]['id']]['type'] == 2)
                $tmp -= ($at[$i]['count'] / $atcount) * $dfbonus2;
            elseif ($ud[$at[$i]['id']]['type'] == 3)
                $tmp -= ($at[$i]['count'] / $atcount) * $dfbonus3;
            if ($tmp > 0) { // wenn noch Einheiten überlebt haben
                $attemp[$tmpcount]['aid'] = $at[$i]['aid'];
                $attemp[$tmpcount]['id'] = $at[$i]['id'];
                $attemp[$tmpcount]['hitpoints'] = $tmp;
                $attemp[$tmpcount]['count'] = ceil($tmp / ($ud[$at[$i]['id']]['life'] * $lifefactor));
                $attemp[$tmpcount]['player'] = $at[$i]['player'];
                ++ $tmpcount;
            }
        }
        $at = $attemp;
        unset ($attemp);
        $tmpcount = 0;
        for ($i = 0; $i < sizeof($df); ++ $i) {
            $tmp = $df[$i]['hitpoints'] - ($df[$i]['count'] / $dfcount) * $atmain;
            if ($ud[$df[$i]['id']]['type'] == 1)
                $tmp -= ($df[$i]['count'] / $dfcount) * $atbonus1;
            elseif ($ud[$df[$i]['id']]['type'] == 2)
                $tmp -= ($df[$i]['count'] / $dfcount) * $atbonus2;
            elseif ($ud[$df[$i]['id']]['type'] == 3)
                $tmp -= ($df[$i]['count'] / $dfcount) * $atbonus3;
            if ($tmp > 0) { // wenn noch Einheiten überlebt haben
                $dftemp[$tmpcount]['aid'] = $df[$i]['aid'];
                $dftemp[$tmpcount]['id'] = $df[$i]['id'];
                $dftemp[$tmpcount]['hitpoints'] = $tmp;
                $dftemp[$tmpcount]['count'] = ceil($tmp / $ud[$df[$i]['id']]['life']);
                $dftemp[$tmpcount]['player'] = $df[$i]['player'];
                ++ $tmpcount;
            }
        }
        $df = $dftemp;
        unset ($dftemp);
    }
}

/*
Beispiel

$at[0]['id']=2;
$at[0]['count']=10;
$at[0]['player']=1;
$at[1]['id']=3;
$at[1]['count']=10;
$at[1]['player']=1;
$df[0]['id']=11;
$df[0]['count']=38;
$df[0]['player']=2;
print_r(fight($at,$df,5));
*/
function compute_despoil($city, $army) {

    /*************Computes the despoil value**************/
    //Playerdata
    $res1 = do_mysql_query("SELECT gold, wood, iron, stone, clan, clanstatus FROM player WHERE id = ".$city['owner']);
    $data1 = mysql_fetch_assoc($res1);

    //Hitpoints computing
    for ($i = 0; $i < sizeof($army); $i ++) {
        $count += $army[$i]['count'];
    }
    $hitpoints = $count * $city['ew'] / 300;

    //division by zero ist ungesund
    $price['gold'] = 1;
    $price['wood'] = 1;
    $price['stone'] = 1;
    //Building Data

    $worth_res = do_mysql_query("SELECT sum(gold*count) as gold, sum(wood*count) as wood, sum(stone*count) as stone FROM building, citybuilding WHERE building.id = citybuilding.building AND citybuilding.city = ".$city['cid']);
    $worth = mysql_fetch_assoc($worth_res);

    $res2 = do_mysql_query("SELECT gold*count as gold, wood*count as wood, stone*count as stone, (points*count) AS pts FROM building, citybuilding WHERE building.id = citybuilding.building AND citybuilding.city = ".$city['cid']." HAVING pts < ".$hitpoints." ORDER BY RAND()");
    while (($data2 = mysql_fetch_assoc($res2)) && ($hitpoints > 0)) {
        $hitpoints -= $data2['pts'];
        $price['gold']  += $data2['gold'] * 2;
        $price['wood']  += $data2['wood'] * 2;
        $price['stone'] += $data2['stone']* 2;
    }


    $prop['gold'] = $price['gold'] / $worth['gold'];
    $prop['wood'] = $price['wood'] / $worth['wood'];
    $prop['stone']= $price['stone']/ $worth['stone'];
    $prop['iron'] = ($prop['wood'] + $prop['stone']) / 2.0;

    $market_res = do_mysql_query("SELECT id,hasType,hasQuant,ratio,wantsQuant FROM market WHERE player=".$city['owner']);
    while ($market = mysql_fetch_assoc($market_res)) {
      $price["m".$market['hasType']] += floor($market['hasQuant'] * $prop[$market['hasType']]);
      $market['hasQuant'] -= floor($market['hasQuant'] * $prop[$market['hasType']]);
      $market['wantsQuant'] = $market['hasQuant'] * $market['ratio'];
      do_mysql_query("UPDATE market SET hasQuant=".$market['hasQuant'].", wantsQuant=".$market['wantsQuant']." WHERE id=".$market['id']);
    }

    do_mysql_query("DELETE FROM market WHERE hasQuant=0 OR wantsQuant=0");
    

    if ($data1['clan'] > 0) {
        if ($data1['clanstatus'] & 1) {
            $clan_res = do_mysql_query("SELECT clan.gold,count(*) as finance FROM clan,player WHERE clan.id=player.clan AND player.clanstatus & 1 AND clan.id=".$data1['clan']." GROUP BY clan.id");
            if ($clan_data = mysql_fetch_assoc($clan_res)) {
                $price['cgold'] = floor(($prop['gold'] / $clan_data['finance']) * $clan_data['gold']);
                
                // Plünderung aus OK beschränken
                if ($price['cgold'] > $worth['gold'] * 2) {
                  $price['cgold'] = $worth['gold'] * 2;
                }

		// Manchmal kommt es vor, dass cgold negativ wird. Damit ein Clan
		// Kein Gold dazu bekommt, wird diese Abfrage gemacht
		if ($price['cgold'] > 0) {
		  echo " ".$price['cgold']." Gold von Clan ".$data1['clan']."<br>\n";
                  do_mysql_query("UPDATE clan SET gold=gold-".$price['cgold']." WHERE clan.id=".$data1['clan']);
 		}
		else {
		  echo " ACHTUNG: cgold < 0! ".$price['cgold']." Gold von Clan ".$data1['clan']." (kein SQL Update)<br>\n";
		}
            }
        }
    }


    //complete
    if ($price['gold'] > $data1['gold'])
        $price['gold'] = $data1['gold'];
    if ($price['wood'] > $data1['wood'])
        $price['wood'] = $data1['wood'];
    if ($price['stone'] > $data1['stone'])
        $price['stone'] = $data1['stone'];

    // Kalkuliere Eisen-Plünderung anhand von Stein und Holz
    $price['iron'] = ($price['stone'] +  $price['wood']) / 2;
    if ($price['iron'] > $data1['iron'])
        $price['iron'] = $data1['iron'];

    if ($price['gold'] < 1)
        $price['gold'] = 0;
    if ($price['wood'] < 1)
        $price['wood'] = 0;
    if ($price['iron'] < 1)
        $price['iron'] = 0;
    if ($price['stone'] < 1)
        $price['stone'] = 0;

    do_mysql_query("UPDATE player SET gold = (gold-".$price['gold']."), wood=(wood-".$price['wood']."), iron=(iron-".$price['iron']."), stone=(stone-".$price['stone']."), cc_resources=1 WHERE id = ".$city['owner']);
    // add gold clan
    if (isset ($price['cgold'])) {
        if ($price['cgold'] < 1)
            $price['cgold'] = 0;
        $price['gold'] = $price['gold'] + $price['cgold'];
    }
    do_mysql_query("UPDATE player SET gold = (gold+".($price['gold']+$price['mgold'])."), wood=(wood+".($price['wood']+$price['mwood'])."), iron=(iron+".($price['iron']+$price['miron'])."), stone=(stone+".($price['stone']+$price['mstone'])."), cc_resources=1 WHERE id = ".$city['attacker']);

    return $price;
}







/** 
 * Als Rückgabe ist eigentlich nur $price['gold'] und $price['cgold'] 
 * von Interesse.
 */      
function compute_despoil_new($city, $army, $burn = false) {
  echo "\nArmee:\n";
  var_dump($army);

  $count = 0;
  $price['gold'] = -1;
  $price['cgold']= -1;
  $price['all']  = 0;

  //Hitpoints computing
  for ($i = 0; $i < sizeof($army); $i ++) {
    $count     += $army[$i]['count'];
    $hitpoints += $army[$i]['hitpoints'];
  }
  $hitpoints = round( $hitpoints ) ;
 
  // Feststellen, obs ne Hauptstadt ist
  $city_data = do_mysql_query_fetch_array("SELECT owner,capital,prosperity,population,sum(res_attraction) AS attr".
                                          " FROM city LEFT JOIN citybuilding cb ON cb.city=city.id ".
                                          " LEFT JOIN building b ON b.id=cb.building ".
                                          " WHERE city.id = ".$city['cid']." GROUP BY city.id");
  // Minimum ist 1000
  $city_data['attr'] = $city_data['attr'] + 1000;

  echo "Capital? ".$city_data['capital'].", Population: ".$city_data['population'].
    ", Attr: ".$city_data['attr'].", Prosp: ".$city_data['prosperity']."\n";
  

  if ($burn) {
    echo "Stadt wird niedergebrannt.\n";
    $max_gold = $hitpoints * 2 + rand(0, $hitpoints/2);
  }
  else if ( $hitpoints*2 < $city_data['population'] ) {
    echo "Zu wenig Truppen.\n";
    // Nichts
    return $price;
  }
  else {
  	if($city_data['owner']) {
  	  // Siedler zur Stadtbevölkerung addieren
  	  $settler_data = do_mysql_query_fetch_array("SELECT sum(missiondata) as settler_sum FROM army WHERE start = ".$city['cid']." AND owner=".$city_data['owner']."");
  	}
  	
  	if($settler_data['settler_sum'] == NULL) {
  	  $settler_data['settler_sum'] = 0;
  	}
  	 
    $max_gold = $hitpoints * 2 - ($city_data['population'] + $settler_data['settler_sum']) + rand(0, $hitpoints/2);
  }

  if ($city_data['prosperity'] < $max_gold) {
    // Alles leergeraubt
    $price['all']  = 1;
    $price['gold'] = $city_data['prosperity'];   
    $prosperity = 0;    
  }
  else {
    $price['gold'] = $max_gold;
    $prosperity = $city_data['prosperity'] - $max_gold;
  }
  
  if ($price['gold'] > 0) {
    // Bevölkerungsstrafe maximal 50% der Bevölkerung
    /*$price['penalty'] = round(min(0.5, $price['gold'] * 5 
                                  / (PROSPERITY_MAX_FACTOR * $city_data['attr'])
                                  * ($city_data['population']*2/$city_data['attr'])
                                  )                               
                              * $city_data['population']);*/
    $people_sum_old = ($city_data['population'] + $settler_data['settler_sum']);
    $population_sum_old = $city_data['population'];
    $settler_sum_old = $settler_data['settler_sum'];
    $population_percentage_old = ($population_sum_old / $people_sum_old);
    $settler_percentage_old = ($settler_sum_old / $people_sum_old);
    // Bevölkerungsstrafe maximal 50% der (Bevölkerung + Siedler)
    $price['penalty'] = round(min(0.5, $price['gold'] * 5 
                                  / (PROSPERITY_MAX_FACTOR * $city_data['attr'])
                                  * ($people_sum_old*2/$city_data['attr']))                               
                              		* $people_sum_old);
    // Neue Anzahl der Siedler und Bürger ausrechnen
    $people_sum_new = ($people_sum_old - $price['penalty']);
    //$population_sum_new = $city_data['population'];
    //$settler_sum_old = $settler_data['settler_sum'];
    echo "OLD: people_sum_old = ".$people_sum_old.", population_sum_old = ".$population_sum_old.", settler_sum_old = ".$settler_sum_old."\n";

    //if($population_sum_old-$people_sum_new>=1) {
    if($people_sum_new>$settler_sum_old) {
      $population_sum_new = $people_sum_new - $settler_sum_old;
      $price['penalty'] = $people_sum_old - $people_sum_new;
      $price['settler'] = 0;

      echo "NEW1: people_sum_new = ".$people_sum_new.", population_sum_new = ".$population_sum_new.", settler_sum_new = ".$settler_sum_old."\n";
    }
    /*else if($population_sum_old==$people_sum_new) {
     $price['penalty'] = 0;
     $price['settler'] = 0;
     }*/
    else {
      $population_sum_new = 1;
      $settler_sum_new = $settler_sum_old - ($people_sum_old - $people_sum_new - ($population_sum_old-1));
      $price['penalty'] = $population_sum_old - $population_sum_new;
      $price['settler'] = $settler_sum_old - $settler_sum_new;
       
      echo "NEW2: people_sum_new = ".$people_sum_new.", population_sum_new = ".$population_sum_new.", settler_sum_new = ".$settler_sum_new."\n";
       
      $get_settler_data = do_mysql_query("SELECT aid,missiondata as settler FROM army WHERE start = ".$city['cid']." AND owner=".$city_data['owner']);
      $settler_num = mysql_num_rows($get_settler_data);
      $settler_sum_new2 = 0;
      $count_settler_units = 0;
      while ($settler_data = mysql_fetch_assoc($get_settler_data)) {
        // Prozentzahl der sterbenden Siedler pro Trupp
        $settler_percentage = $settler_data['settler']/$settler_sum_old;
        $new_settler_amount = floor($settler_percentage * $settler_sum_new);
        do_mysql_query("UPDATE army SET missiondata = ".$new_settler_amount." WHERE aid = ".$settler_data['aid']);
        echo "army = ".$settler_data['aid'].", old_settler = ".$settler_data['settler'].", new_settler = ".$new_settler_amount.", settler_percentage = ".$settler_percentage."\n";
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
        echo "id = ".$settler_array[$choose_settler_array_id]['id'].", count = ".$settler_array[$choose_settler_array_id]['count'].", number = ".$choose_settler_array_id."\n";
        echo "Zu wenig Siedler gestorben -> gleiche aus: ".($settler_sum_new-$settler_sum_new2)." Siedler zusätzlich gestorben in army = ".$settler_array[$choose_settler_array_id]['id']."\n";
        echo "UPDATE army SET missiondata = ".($settler_array[$choose_settler_array_id]['count']-($settler_sum_new-$settler_sum_new2))." WHERE aid = ".$settler_array[$choose_settler_array_id]['id']."\n";
        do_mysql_query("UPDATE army SET missiondata = ".($settler_array[$choose_settler_array_id]['count']-($settler_sum_new-$settler_sum_new2))." WHERE aid = ".$settler_array[$choose_settler_array_id]['id']);
      }
    }
    echo "price['settler'] = ".$price['settler']."\n";
  }
  else {
    $price['penalty'] = 0;
    $price['settler'] = 0;
  }

  printf( "Maxgold: %d, Price: %d, Penalty: %d\n", $max_gold, $price['gold'], $price['penalty'] );
  
  
  // Wenn jemand ein Finanzminister ist und dessen Hauptstadt
  // angegriffen wird, dann dort nochmal was raushauen.
  if ($city_data['capital']) {
    // Hauptstadt. Nachschauen, was der Besitzer ist
    $data1 = do_mysql_query_fetch_array("SELECT clan, clanstatus FROM player WHERE id = ".$city['owner']);
    if ( $data1['clan'] > 0 && $data1['clanstatus'] & 1 ) {
      $clan_res = do_mysql_query("SELECT clan.gold,count(*) as finance FROM clan,player WHERE clan.id=player.clan AND player.clanstatus & 1 AND clan.id=".$data1['clan']." GROUP BY clan.id");
    
      if ($clan_data = mysql_fetch_assoc($clan_res)) {
        printf("%d Finanziminster, %d Gold in der OK\n", $clan_data['finance'], $clan_data['gold']);
      
        // Wir nehmen an, dass jeder Finanzminister die gleiche Menge Gold gelagert hat
        // Damit das nicht zu krass wird, maximal 10 Finanzminister
        if ($clan_data['finance'] > 10)
          $clan_data['finance'] = 10;
      
        $price['cgold'] = round($clan_data['gold'] / $clan_data['finance']);

        // Die Menge begrenzen. Aus ner armen Stadt wird auch weniger OK geplündert
        if ($price['cgold'] > $price['gold']) {
          if ($burn)
            $price['cgold'] = rand( $price['gold'], min($price['cgold'], $max_gold) );
          else 
            $price['cgold'] = rand( $price['gold'], min(2*$price['gold'], $price['cgold']) );
        }      


        // Manchmal kommt es vor, dass cgold negativ wird. Damit ein Clan
        // Kein Gold dazu bekommt, wird diese Abfrage gemacht
        if ($price['cgold'] > 0) {
          echo " ".$price['cgold']." Gold von Clan ".$data1['clan']."<br>\n";
          do_mysql_query("UPDATE clan SET gold=gold-".$price['cgold']." WHERE clan.id=".$data1['clan']);
        }
      }
    } // is Finance
  } // capital
  
  // Stadt-Verarmung
  do_mysql_query("UPDATE city ".
                 "SET prosperity = ".$prosperity.",".
                 " population = population - ".$price['penalty'].
                 " WHERE id = ".$city['cid']);
  
  // add gold clan
  if (isset ($price['cgold'])) {
    if ($price['cgold'] < 1)
      $price['cgold'] = 0;
  }
  
  do_mysql_query("UPDATE player SET cc_resources=1, gold=gold + ".($price['gold']+$price['cgold']).
                 " WHERE id = ".$city['attacker']);
  
  return $price;
}




/* Verteidiger wagt einen Ausfall gegen die Belagerer
 *
 * Dabei kämpfen sämtliche Verteidiger gegen sämtliche Belagerer.
 * 
 */
function attackSiege($cityid) {
  global $player;

  $cityowner = $player->getID();
  $cityname = do_mysql_query_fetch_assoc("SELECT name FROM city WHERE id = ".$cityid);
  $cityname = $cityname['name'];
  $siege_time = getSiegeTime($cityid);

  // Die Einheitennamen in einen array legen
  $unit_res = mysql_query("SELECT id, type, name FROM unit");
  while ($unit = mysql_fetch_assoc($unit_res)) {
    $arr_units[$unit['id']] = $unit['name'];
  }
  
  // Spieler wagt einen Ausfall
  $sql = 
    "SELECT DISTINCT player.name AS playername, city.name AS cityname, army.aid AS aid, army.owner AS owner, UNIX_TIMESTAMP() - endtime AS siegetime".
    " FROM army LEFT JOIN city ON city.id = army.end LEFT JOIN player ON army.owner = player.id".
    " WHERE army.end = ".$cityid." AND city.owner = ".$cityowner." AND army.endtime < unix_timestamp() AND mission = 'siege' ".
    " ORDER BY army.owner";

  $siege_res = do_mysql_query ($sql);
  
  if (mysql_numrows($siege_res) == 0) {
    return "Diese Stadt ist nicht unter Belagerung oder gehört nicht Euch.\n";
  }
  else if ($siege_time < SALLY_MIN_SIEGE_TIME) {
    return "Sire! Wir sind noch nicht auf einen Ausfall vorbereitet. Gebt uns noch Zeit (es muss mindestens ".round(SALLY_MIN_SIEGE_TIME/60)." Minuten belagert worden sein).\n";  
  }
  else {
    // Ein Kampf beginnen, bei dem der Belagerer zum Verteidiger und der Stadtbesitzer zum Angreifer wird
    // Taktik ist in diesem Fall erstürmen
    $bonus = 0;    

    /*
     * Die "Angreifer" aus der Stadt bestimmen
     */
    $res = do_mysql_query("SELECT unit,count,owner, player.name AS playername ".
                          " FROM cityunit LEFT JOIN player ON player.id = cityunit.owner ".
                          " WHERE city = ".$cityid." ORDER BY owner != ".$cityowner.", owner");
    $i = 0;
    while ($citydef = mysql_fetch_assoc($res)) {
      $arr_citydef[$i]['id']     = $citydef['unit'];
      $arr_citydef[$i]['count']  = $citydef['count'];
      $arr_citydef[$i]['player'] = $citydef['owner'];
      $i++;

      $arr_players[$citydef['owner']] = $citydef['playername'];

      $def_unit_count += $citydef['count'];
      $def_army_text .= " ".$citydef['count']." ".$arr_units[$citydef['unit']]." von ".$citydef['playername']."\n";
    }
    
    /*
     * Die "Verteidiger" aus den Armeen bestimmen
     */
    $i = 0;
    while ($siege_army = mysql_fetch_assoc($siege_res)) {
      $arr_players[$siege_army['owner']] = $siege_army['playername'];

      $siege_army_text .= "\neiner Armee unter dem Kommando von <b>".$siege_army['playername']."</b>, die aus folgenden Einheiten bestand:\n";
      $siege_army_count ++;

      $siege_armyunits = do_mysql_query("SELECT unit,count FROM armyunit WHERE aid = ".$siege_army['aid']);
      while ($unit = mysql_fetch_assoc($siege_armyunits)) {
        $arr_siege[$i]['aid']    = $siege_army['aid'];
        $arr_siege[$i]['id']     = $unit['unit'];
        $arr_siege[$i]['count']  = $unit['count'];
        $arr_siege[$i]['player'] = $siege_army['owner'];
        $i++;
        
        $siege_unit_count += $unit['count'];
        $siege_army_text .= " ".$unit['count']." ".$arr_units[$unit['unit']]."\n";
      }
    } // $siege_army


    $fightresult = fight($arr_citydef, $arr_siege, $bonus, 2);
    
    if ($fightresult) {
      if ($fightresult[0]['player'] == $cityowner){
        echo "<h2>Stadtbesitzer gewinnt, Ausfall erfolgreich</h2>";
        $wintext = "Die Belagerung wurde durchbrochen, alle vor der Stadt lagernden Truppen wurden aufgerieben!";
        $topictext = "Belagerung bei ".$cityname." durchbrochen";

        // Wenn der Stadtbesitzer gewinnt, dann alle belagernden Armeen und Ihre Truppen
        // löschen
        $aid = -1;
        foreach ($arr_siege as $army) {
          if ($aid != $army['aid']) {
            $aid = $army['aid'];
            $sql = "DELETE FROM armyunit WHERE aid = ".$aid;
            do_mysql_query($sql);
            $sql = "DELETE FROM army WHERE aid = ".$aid;
            do_mysql_query($sql);
          }
        }

        // Die Stadtverteidigung aktualisieren
        $sql = "DELETE FROM cityunit WHERE city = ".$cityid;
        do_mysql_query($sql);
        // do_mysql_query($sql);
        foreach ($fightresult as $cityunit) {
          $sql = sprintf("INSERT INTO cityunit (city, unit, count, owner) VALUES (%s, %s, %s, %s)",
                         $cityid,
                         $cityunit['id'],
                         $cityunit['count'],
                         $cityunit['player']);
          do_mysql_query($sql);

          $surviving_units .= " ".$cityunit['count']." ".$arr_units[$cityunit['id']]." von ".$arr_players[$cityunit['player']]."\n";
        }
      }
      else {
        echo "<h2>Belagerer gewinnt. Stadt ist nun unverteidigt.</h2>";
        $wintext = "Die Stadtverteidigung konnte die Belagerung nicht durchbrechen! Somit ist die Stadt nun wehrlos.";
        $topictext = "Ausfall bei ".$cityname." gescheitert";

        // Wenn der Stadtbesitzer ist unterlegen 
        // Also alle Stadtruppen löschen
        $sql = "DELETE FROM cityunit WHERE city = ".$cityid;
        do_mysql_query($sql);

        // Jetzt wirds schwierig, denn die ganzen angreifenden Armeen müssen
        // aktualisiert werden
        // zunächst die alten Armeen löschen
        $aid = -1;
        foreach ($arr_siege as $army) {
          if ($aid != $army['aid']) {
            $aid = $army['aid'];
            // Armee wechselt, die Armee löschen
            $sql = "DELETE FROM armyunit WHERE aid = ".$aid;
            do_mysql_query($sql);
            $empty_armies .= $aid.", ";
          }
        }
        // noch eine Null dranhängen, weil die aid in der form "11,12,332,44," zusammengebaut sind
        $empty_armies .= "0";

        // Nun die überlebenden einfügen
        $aid = -1;
        foreach ($fightresult as $army) {
          $surviving_units .= " ".$army['count']." ".$arr_units[$army['id']]." von ".$arr_players[$army['player']]."\n";
          if ($aid != $army['aid']) {
            $aid = $army['aid'];
            $resulting_armies .= $aid.", ";           
          }
          $sql = sprintf("INSERT INTO armyunit (aid, unit, count) VALUES (%s, %s, %s)",
                         $aid,
                         $army['id'],
                         $army['count']);
          do_mysql_query($sql, null, false);
        }
        // Wieder ne 0 dranhängen, wie oben
        $resulting_armies .= "0";
        $sql = "DELETE FROM army WHERE aid IN (".$empty_armies.") AND aid NOT IN (".$resulting_armies.")";
        do_mysql_query($sql);
      }
    }
    else {
      echo "<h2>Unentschieden</h2>";
      $wintext = "Die beiden Armeen haben sich bis auf den letzten Mann ausgelöscht!";
      $topictext = "Belagerung durchbrochen";
    }
  }
  echo "<p>\n";

  $attack_siege_size = "gewaltigen";

  $text  = "Vor der Stadt <b>".$cityname."</b> lagerten ".$siege_unit_count." Einheiten in ".$siege_army_count." Armeen, ";
  $text .= "die seit ".round($siege_time/60)." Minuten die Stadt unter Belagerung hielten.\n";
  $text .= "Der kühne Statthalter suchte einen günstigen Zeitpunkt, um einen ".$attack_siege_size." Ausfall aus den geschützen Mauern der Stadt zu wagen. ";
  $text .= "Er befehligte ".$def_unit_count." Mann, bestehend aus:\n";
  $text .= $def_army_text;
  $text .= "\nund stand nun folgenden Belagerern auf offenem Feld gegenüber:\n";
  $text .= $siege_army_text;
  $text .= "\n<b>".$wintext."</b>\nDabei überlebten folgende Einheiten die Schlacht:\n";
  $text .= $surviving_units;

  //  echo $text;
  
  foreach ($arr_players AS $recipient => $playername) {
    $sql = sprintf("INSERT INTO message (recipient, body, header, sender, date, category) VALUES (%s, '%s', '%s', '%s', %s, %s)",
                   $recipient, mysql_escape_string($text), mysql_escape_string($topictext), "SERVER", "UNIX_TIMESTAMP()", "4" );
    do_mysql_query($sql);
    do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$recipient);
  }

  // Fehlerfrei durchgelaufen
  return null;
}
?>
