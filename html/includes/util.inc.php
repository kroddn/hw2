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
 * Copyright (c) 2005-2007
 *
 * Markus Sinner, Gordon Meiser
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/log.inc.php");
include_once("includes/db.config.php");

$GLOBALS['speedmatrix'] = array(1=>3.5, 2=>2.5, 3=>2, 4=>1.5, 5=>1);


function getUnitImage($data3)
{
  return sprintf('%s_lev%d_%d.gif', $data3['religion'] == 1 ? "c" : "i", $data3['level'], $data3['type']);
}

function getUnitLibLink($unit) {
  return sprintf("library.php?s1=2&s2=%d&s3=0&unit_id=%d", $unit['religion']-1, $unit['id']);
}

function getReliImage($religion) {
  if(intval($religion) <= 0) return;
  
  $reli_str = $religion == 1 ? "Christ" : "Islam";        
  return sprintf("<img align=\"top\" src=\"%s/reli_icon_%d.gif\" alt=\"%s\" title=\"%s\"/>", $GLOBALS['imagepath'], $religion, $reli_str, $reli_str);  
}

// link for adding a player to adressbook
function player_to_adr($name) {
  return (is_premium_adressbook() 
          ? '<a href="edit_adr.php?addname='.urlencode($name).'">'
          : '<a onClick="alert(\'Diese Funktion ist Besitzern eines Premium-Accounts vorbehalten\')">'
          ).
    'Spieler zum Adressbuch hinzufügen.</a>';
}


if(!function_exists("checkBez")) {
  function checkBez($bez, $lenmin=4, $lenmax=50) {
    $str=strtolower($bez);
    if(strpos($str, "  ") !== FALSE) return false;

    $str=$str." -01234567890_abcdefghijklmnopqrstuvwxyzÄÖÜßäöü";
    $str=count_chars($str,3);
    $res=($str==" -0123456789_abcdefghijklmnopqrstuvwxyzÄÖÜßäöü");
    if (strlen(trim($bez))<$lenmin) $res=false;
    if (strlen(trim($bez))>$lenmax) $res=false;
    return $res;
  }
}


if(!function_exists("checkWordLength")) {
  function checkWordLength($bez) {
    // Wordlength deactivated by Kroddn
    return true;

    // echo "<!-- $bez -->";
    $bez=split("[\n ]", $bez);
    for ($i=0;$i<sizeof($bez);++$i) {
      if (strlen($bez[$i])>50) return false;
    }
    return true;
  }
}


if(!function_exists("checkEmail")) {
  function checkEmail($email) {
    return eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$",$email);
  }
}


if(!function_exists("checkPassword")) {
  function checkPassword($pw) {
    return (strlen(trim($pw))>3);
  }
}


if(!function_exists("createKey")) {
  function createKey() {
    $abc = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,1,2,3,4,5,6,7,8,9";
    $abcarray = explode(",",$abc);
    mt_srand((double)microtime()*1000000);
    for ($i=0;$i<10;$i++){
      $zufall = mt_rand(0,35);
      $pass .= $abcarray[$zufall];
    }
    return $pass;
  }
}

/**
 * Ist die Runde schon gestartet?
 * @return boolean TRUE, falls ja. FALSE wenn nicht.
 */
function check_round_startet() {
  $start_time = getConfig("starttime");
  return time() > $start_time;
  
}

if(!function_exists("formatTime")) {
  function formatTime($stamp) {
    $hours = floor($stamp / 3600);
    $stamp = $stamp - $hours * 3600;
    $minutes = floor($stamp / 60);

    if ($minutes<10) $minutes="0".$minutes;

    return $hours.":".$minutes;
  }
}


if(!function_exists("computeDif")) {
  function computeDif($x,$y,$x2,$y2) {
    $difx=abs($x-$x2);
    $dify=abs($y-$y2);
    $dif=sqrt(pow($difx,2)+pow($dify,2));
    return $dif;
  }
}


function computeWalktime($startx, $starty, $endx, $endy, $speed, $unitcount = -1) {
  global $speedmatrix;
  $dif=computeDif($startx,$starty,$endx,$endy);

  $wt= ceil( $dif*3600 * $speedmatrix[$speed] / ARMY_SPEED_FACTOR);

  // Truppenlaufzeit verlängern. Maximal 2 Tage
  if ($unitcount >= 0 && defined("ARMY_TIME_PER_1000")) {
    $wt += max(ARMY_TIME_TO_PREPARE, min(round($unitcount * ARMY_TIME_PER_1000 / 1000), ARMY_MAX_TIME));
  }
  
  return $wt;
}



if(!function_exists("getNearestOwnCity")) {
  function getNearestOwnCity($x,$y,$pid) {
    $res1=do_mysql_query("SELECT city.id AS id,x,y FROM city,map WHERE city.id=map.id AND city.owner=".intval($pid) );
    $dif=9999999999;
    $n[0]=0;
    while ($data1=mysql_fetch_assoc($res1)) {
      $tmp=computeDif($data1['x'],$data1['y'],$x,$y);
      if ($tmp<$dif) {
        $dif=$tmp;
        $n[0]=$data1['id'];
        $n[1]=$dif;
        $n[2]=$data1['x'];
        $n[3]=$data1['y'];
      }
    }
    return $n;
  }
}


if(!function_exists("getNxNy")) {
  function getNxNy($sX, $sY, $wt, $aid, $end)
  {
    global $speedmatrix;
    $res=do_mysql_query("SELECT speed, end FROM army, armyunit WHERE armyunit.aid=army.aid AND army.aid = '".intval($aid)."'");
    $speed=10;
    if(@mysql_num_rows($res)==0)
    {
      $speed=3;
    }
    else
    {
      while($data1=mysql_fetch_assoc($res))
      {
        if($data1['speed'] < $speed) $speed=$data1['speed'];
      }
    }
    $endXY = mysql_fetch_assoc(do_mysql_query("SELECT x, y FROM map WHERE id = ".intval($end)) );
    $n=ceil($wt / 3600 / $speedmatrix[$speed]);
    $A['x'] = $sX;
    $A['y'] = $sY;
    $B['x'] = $endXY['x'];
    $B['y'] = $endXY['y'];
    $D = sqrt(pow(($B['x']-$A['x']), 2)+pow(($B['y']-$A['y']), 2));

    $ret['x'] = floor(($n*(abs($B['y']-$A['y'])))/$D);
    $ret['y'] = floor(((abs($B['y']-$A['y']))-($n*(abs($B['y']-$A['y']))))/$D);

    die("".$ret['x']."");
    return $ret;

  }
}


if(!function_exists("checkSettle")) {
  function checkSettle($ax,$ay,$plr) {
    $res1=do_mysql_query("SELECT id FROM army,map WHERE x=".intval($ax)." AND y=".intval($ay)." AND army.end=map.id AND army.owner = ".intval($plr)." AND mission = 'settle'");
    if (mysql_num_rows($res1)>0) return false;
    else return true;
  }
}


if(!function_exists("do_log")) {
  function do_log($message) {    
    //in Pension geschickt von co2, bei Gelegenheit ganz entfernen
    /*
     global $player;
     $fp = fopen("./../../hw_log/".$player->name.".".$player->id.".".date("Y-m-d").".log", "a");
     $writing = fwrite($fp, "[".getenv('REMOTE_ADDR')." - ".date("Y-F-l - H:i:s")."] ".$message."\n");
     fclose($fp);
     if($writing)
     {
     return 1;
     }
     else return 0;
     */
  }
}


if(!function_exists("remove_player_armies")) {
  function remove_player_armies($id) {
    $res1=do_mysql_query("SELECT aid FROM army WHERE owner = ".intval($id) );
    if(mysql_num_rows($res1)) {
      while($data1=mysql_fetch_assoc($res1)) {
        do_mysql_query("DELETE FROM armyunit WHERE aid=".$data1['aid']);
        do_mysql_query("DELETE FROM army WHERE aid=".$data1['aid']);
      }
    }

    //Stadttruppen löschen
    do_mysql_query("DELETE FROM cityunit WHERE owner = ".intval($id) );
  }
}


if (!function_exists("removeCity")) {
  function removeCity($id) {
    $id = intval($id);
    marketRetractionForCity($id);
    do_mysql_query("DELETE FROM citybuilding WHERE city = ".$id);
    do_mysql_query("DELETE FROM citybuilding_ordered WHERE city = ".$id);
    do_mysql_query("DELETE FROM cityunit WHERE city = ".$id);
    do_mysql_query("DELETE FROM cityunit_ordered WHERE city = ".$id);
    do_mysql_query("DELETE FROM city WHERE id = ".$id);


    $xy=mysql_fetch_assoc(do_mysql_query("SELECT x, y FROM map WHERE id = ".$id));
    // Nur einfügen wenn es die Startpos noch nicht gibt
    if ($xy['x'] != NULL && $xy['y'] != NULL &&
    mysql_numrows(do_mysql_query("SELECT x, y FROM startpositions ".
                                     "WHERE x = ".$xy['x']." AND y = ".$xy['y'])) == 0 ) 
    {
      do_mysql_query("INSERT INTO startpositions(x,y) VALUES (".$xy['x'].", ".$xy['y'].")");
    }
  }
}



function gen_tournament_sql_code($current_day) {
  //  echo $current_day."\n";
  
  $sql = "";
  for($start = 6; $start <= 24; $start+=2) {
    
    $t = strtotime($current_day." ".($start%24).":00");    
    $t_str = date("D M d, Y G:i",$t);
    
    if($start == 20 || $start == 14)
      $sql .= "INSERT INTO tournament (time,maxplayers,gold) VALUES ($t,16,5000); -- ".$t_str."\n";
    else
      $sql .= "INSERT INTO tournament (time,maxplayers,gold) VALUES ($t,8,2000); -- ".$t_str."\n";
  }
  
  return $sql;
}



if(!function_exists("removePlayer")) {
  function removePlayer($id) {
    $id = intval($id);

    $delplayer_res = do_mysql_query("SELECT * FROM player WHERE id=".$id);
    if ($delplayer=mysql_fetch_assoc($delplayer_res)) {
      //Spieler löschen
      remove_player_armies($id);
      do_mysql_query("DELETE FROM market WHERE player = ".$id);
      //Nachrichten löschen
      do_mysql_query("DELETE FROM message WHERE recipient  = ".$id);
      //Forschende Forschungen löschen
      do_mysql_query("DELETE FROM researching WHERE player = ".$id);
      // Namensänderungen löschen
      do_mysql_query("DELETE FROM namechange WHERE id = ".$id);
      //Forschungen löschen
      do_mysql_query("DELETE FROM playerresearch WHERE player = ".$id);
      //Beziehungen löschen
      do_mysql_query("DELETE FROM relation WHERE id1=".$id." OR id2=".$id);
      do_mysql_query("DELETE FROM req_relation WHERE id1=".$id." OR id2=".$id);
      // Turnierteilnahmen löschen
      do_mysql_query("DELETE FROM tournament_players WHERE player = ".$id.
		     " AND tid IN (SELECT tid FROM tournament WHERE calctime IS NULL)");

      //Städte löschen
      $cty_res = do_mysql_query("SELECT id FROM city WHERE owner=".$id);
      while ($cty = mysql_fetch_assoc($cty_res)) {
        removeCity($cty['id']);
      }

      if ($delplayer['recruiter']) {
        $recruiter = $delplayer['recruiter'];
      }
      else {
        $recruiter = "NULL";
      }
      // Spieler im internen Forum nicht l&ouml;schen, sondern Eintr&auml;ge auf Spieler "Gel&ouml;schter Spieler" umbiegen
      $check_deleted_forums_user_res = do_mysql_query("SELECT * FROM `clanf_users` WHERE user_id = -2");
      if ($check_deleted_forums_user=mysql_fetch_assoc($check_deleted_forums_user_res)) {
        do_mysql_query("UPDATE `clanf_posts` set poster_id = -2 WHERE poster_id=".$id);
        do_mysql_query("UPDATE `clanf_topics` set topic_poster = -2 WHERE topic_poster=".$id);
        do_mysql_query("DELETE FROM clanf_users WHERE user_id = ".$id);
      }
      else {
        do_mysql_query("INSERT INTO `clanf_users` (`user_id`, `user_active`, `username`, `user_password`, `user_session_time`, `user_session_page`, `user_lastvisit`, `user_regdate`, `user_level`, `user_posts`, `user_timezone`, `user_style`, `user_lang`, `user_dateformat`, `user_new_privmsg`, `user_unread_privmsg`, `user_last_privmsg`, `user_emailtime`, `user_viewemail`, `user_attachsig`, `user_allowhtml`, `user_allowbbcode`, `user_allowsmile`, `user_allowavatar`, `user_allow_pm`, `user_allow_viewonline`, `user_notify`, `user_notify_pm`, `user_popup_pm`, `user_rank`, `user_avatar`, `user_avatar_type`, `user_email`, `user_icq`, `user_website`, `user_from`, `user_sig`, `user_sig_bbcode_uid`, `user_aim`, `user_yim`, `user_msnm`, `user_occ`, `user_interests`, `user_actkey`, `user_newpasswd`) VALUES ('-2', '1', 'Gelöschter Spieler', MD5('bla'), '0', '0', '0', '0', '0', '0', '1.00', NULL, NULL, 'd M Y H:i', '0', '0', '0', NULL, NULL, NULL, '1', '1', '1', '1', '1', '1', '1', '0', '0', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);");
        do_mysql_query("UPDATE `clanf_posts` set poster_id = -2 WHERE poster_id=".$id);
        do_mysql_query("UPDATE `clanf_topics` set topic_poster = -2 WHERE topic_poster=".$id);
        do_mysql_query("DELETE FROM clanf_users WHERE user_id = ".$id);
      }
      
      // Jetzt den Spieler löschen
      do_mysql_query("DELETE FROM player WHERE id = ".$id);

      $playername  = $delplayer['name'] ? "'".$delplayer['name']."'" : "NULL";
      $reli_string = $delplayer['religion'] ? $delplayer['religion'] : "NULL";

      do_mysql_query("INSERT INTO log_player_deleted(id,login,name,recruiter,description,password,email,register_email, ip,lastseen,religion,gold,wood,iron,stone,rp,points,clan,clanstatus,status,statusdescription,lastres,deltime,regtime) VALUES (".$delplayer['id'].",'".$delplayer['login']."',".$playername.",".$recruiter.",'".mysql_escape_string($delplayer['description'])."','".$delplayer['password']."','".$delplayer['email']."', '".$delplayer['register_email']."', '".$delplayer['ip']."',".$delplayer['lastseen'].",".$reli_string.",".$delplayer['gold'].",".$delplayer['wood'].",".$delplayer['iron'].",".$delplayer['stone'].",".$delplayer['rp'].",".$delplayer['points'].",'".$delplayer['clan']."','".$delplayer['clanstatus']."','".$delplayer['status']."','".mysql_escape_string($delplayer['statusdescritiption'])."',".$delplayer['lastres'].",".time().",".$delplayer['regtime'].")");

      // Bonuspunkte wieder abziehen
      if ($delplayer['recruiter']) {
        $bonus = RECRUIT_BONUSPOINTS;
        do_mysql_query("UPDATE player SET bonuspoints = bonuspoints - ".$bonus." WHERE id = ".$recruiter);
      }
    } // if
  }
}


if(!function_exists("removeClan")) {
  //assumes clean and trusted input
  function removeClan ($clan) {
    $clan = intval($clan);
    do_mysql_query("UPDATE player SET clan=NULL WHERE clan=".$clan);
    do_mysql_query("UPDATE player SET clanapplication=NULL WHERE clan=".$clan);
    do_mysql_query("DELETE FROM req_clanrel WHERE id1=".$clan." OR id2=".$clan);
    do_mysql_query("DELETE FROM clanrel WHERE id1=".$clan." OR id2=".$clan);
    do_mysql_query("DELETE FROM clan WHERE id=".$clan);
  }
}


if(!function_exists("resolveCityName")) {
  function resolveCityName($id)
  {
    $id = intval($id);
    $res=do_mysql_query("SELECT name FROM city WHERE id = ".$id);
    if(mysql_num_rows($res)==0)
    {
      return false;
    }
    else
    {
      $data=mysql_fetch_assoc($res);
      return $data['name'];
    }

  }
}


if(!function_exists("resolvePlayerName")) {
  function resolvePlayerName($id)
  {
    $id = intval($id);
    $res=do_mysql_query(
      "SELECT name FROM player WHERE id = ".$id."
       UNION
       SELECT name FROM log_player_deleted WHERE id = ".$id);
    if(mysql_num_rows($res)==0)
    {
      return false;
    }
    else
    {
      $data=mysql_fetch_assoc($res);
      return $data['name'];
    }

  }
}


if (!function_exists("player_exists")) {
  function player_exists($id) {
    $id = intval($id);
    $res = do_mysql_query("SELECT id FROM player WHERE id = ".$id);
    return mysql_num_rows($res);
  }
}


if(!function_exists("save_html")) {
  //found this somewhere in the Net
  function save_html($str) {

    //listed of tags that will not be striped but whose attributes will be
    $allowed = "br|b|i|p|u|a|center|hr";
    $oldstr = "";

    while($str != $oldstr) {
      $oldstr = $str;
      //nuke script and header tags and anything inbetween
      $str = preg_replace("'<script[^>]*>.*</script>'siU", "", $str);
      $str = preg_replace("'<head[^>]*>.*</head>'siU", "", $str);

      //start nuking those suckers. don you just love MS Word's HTML?
      $str = preg_replace("/<((?!\/?($allowed)\b)[^>]*>)/xis", "", $str);
      $str = preg_replace("/<($allowed).*?>/i", "<\\1>", $str);
    }

    return $str;
  }
}


/** function creates a HTML link based on paramter
 * The array has to include:
 * $data['name']
 * $data['id']
 */
if(!function_exists("get_city_htmllink")) {
  function get_city_htmllink($data, $printid = true) {
    // Coord of actual city
    $res_coord = do_mysql_query("SELECT x,y FROM map WHERE id=".$data['id']);
    $coord = mysql_fetch_assoc($res_coord);
    return "<a ".($data['capital'] ? 'class="red"' :'')." href=\"javascript:towninfo('".$data['id']."')\">".$data['name']."</a>".( ($printid && $data['id']) ? (" ( ".$data['id']." )") : "");
  }
}

if(!function_exists("get_city_htmllink_koords")) {
  function get_city_htmllink_koords($data) {
    // Coord of actual city
    $res_coord = do_mysql_query("SELECT religion, x,y FROM city LEFT JOIN map USING(id) WHERE city.id=".$data['id']);
    $coord = mysql_fetch_assoc($res_coord);
    return getReliImage($coord['religion'])." <a ".($data['capital'] ? 'class="red"' :'')." href=\"javascript:towninfo('".$data['id']."')\">".$data['name']."</a> <a href=\"map.php?gox=".$coord['x']."&goy=".$coord['y']."\">(".$coord['x'].":".$coord['y'].")</a> [ ".$data['id']." ]";
  }
}


function getMapSize(&$x, &$y) {
  $mapsize = do_mysql_query_fetch_assoc("SELECT max(x)+1 AS x, max(y)+1 AS y FROM map");
  $x = $mapsize['x'];
  $y = $mapsize['y'];
}


if(!function_exists("restart_player")) {
  function restart_player($id) {
    $id = intval($id);
    if($id == null || $id == 0) log_fatal_error("RestartPlayer failed: id null\n");

    $res6=do_mysql_query("SELECT id FROM research r ".
			 " LEFT JOIN playerresearch pr ON (r.id=pr.research AND pr.player=$id) ".
			 " WHERE rp = 0 AND pr.player IS NULL");

    // Startforschungen (jene mit 0 Forschungspunkten) einfügen
    while($data6=mysql_fetch_assoc($res6)) {
      do_mysql_query("INSERT INTO playerresearch (player,research) VALUES (".$id.", ".$data6['id'].")");
    }


    $citys = do_mysql_query("SELECT id FROM city WHERE owner=".$id);

    if ( mysql_num_rows($citys) == 0) 
    {
      remove_player_armies($id);

      // Startposition finden...
      if(defined("START_POS_NEW") && START_POS_NEW) 
      {
        $where = searchStartPosNew($id);
      }
      else {
        $where = searchStartPos($id);
      }

      // Ist noch eine Startpos frei?
      if($where != null && $where['id']) {
        $player_res = do_mysql_query("SELECT pos,nooblevel,religion,name FROM player WHERE id=".$id);
        $p = mysql_fetch_assoc($player_res);

        // Wenn der Spieler noch keinen Namen/Religion gewählt hat
        if( $p['name'] == null )
          {
            redirect_to("login.php");           
          }

        // Hauptstadt erhält Residenz
        //$capital = $p['pos'] != null || $p['nooblevel'] >= 5;
        $capital = true; // Dem User immer eine Residenz geben
        
        // Ganz neue Spieler erstmal vor Werbung verschonen
        if($p['pos'] != null || $p['nooblevel'] >= 5) {
          $_SESSION['first_login'] = true;
        }

        insertCity($where['id'], $id, $p['religion'], $capital);
          
        // Nun Updaten
        do_mysql_query("UPDATE player SET pos = NULL, cc_messages=1, cc_towns=1, lastres=UNIX_TIMESTAMP() WHERE id = ".$id);

        do_mysql_query("UPDATE player SET gold = gold+6000, wood=wood+100, stone=stone+100, cc_resources = 1 WHERE gold <= 30000 AND id = ".$id);

        $_SESSION['player']->newmessages++;
        
      }
      else {
        log_fatal_error("Keine Startpos gefunden.");
      }
    }
    else {
      log_fatal_error("Spieler $id hat noch Städte");
    }
  }
  
  
}



function getStartPosWhere($pos, $reli) {
  $arr = getStartPos($pos, $reli);
  if($arr != null && ($size = sizeof($arr)) > 0) {
    $where = "(";
    for($i=0; $i<$size; $i++) {
      if($i > 0) {
        $where.= " OR ";
      }
      $where .= sprintf(" (x >= %d AND y >= %d AND x < %d AND y < %d) ", $arr[$i][0], $arr[$i][1], $arr[$i][2], $arr[$i][3] );
    }
    $where .= ")";
    return $where;
  }
  else {
    return "1=0";
  }
}




/**
 * Diese Funktion schaut, ob es nötig würde, den Siedlungsradius
 * zu erweitern.
 */
function checkSettleRadius() {
  $sum = 0;
  $extend = false;
   
  
  for($reli = 1; $reli <= 2; $reli++) {
    for($p = 0; $p < 3; $p++) {

      $where = getStartPosWhere($p, $reli);
      $c = do_mysql_query_fetch_assoc("SELECT count(*) as c FROM startpositions WHERE ".$where);
      
      $i = ($reli-1)*3 + $p;
      $pos[$i] = $c['c'];
      $sum += $pos[$i];
    }
  }
  echo "<p>";
  
  getMapSize($fx, $fy);
  $old = getSettleRadius();
  
  $tolerance = 5;
  if($old < $fx/80) {
    for($i=0; $i<sizeof($pos) && !$extend; $i++) {
      //echo "Pos[$i] = ".$pos[$i]."<br>";
      if($pos[$i] <= $tolerance) {
        echo "Ein Gebiet hat weniger als ".$tolerance." Plätze. Radius muß erweitert werden!<br>";
        $extend = true;
      }
    }
    //echo "Insgesamt: $sum<br>";


  $tolerance = 60;
  if($sum <= $tolerance) {
    echo "Insgesamt zu wenige Plätze (<".$tolerance."). Radius muß erweitert werden!<br>";
  	$extend = true;
  }

    if($extend) {
      setConfig("settleradius", $old + 1);
    }
  }
  else {
    echo "Maximaler Siedlungsradius erreicht.";
  }
}


/**
 * Startposition im neuen Platzierungsskript.
 * 
 * Es gibt 3 Gebiete je Religion:
 * - Hinterland: Dies sind die Sektoren am oberen bzw. unteren Rand des Siedlungs-Ringes
 * - Übergangsgebiet: die Gebiete nördlich bzw. südlich des Äquators, ohne Hinterland
 * - Kriegsgebiet - die Sektoren am Äquator, nicht im Siedlungsring
 *
 * Die Startposition des Spielers:
 * 0 - Hinterland
 * 1 - Übergangsgebiet
 * 2 - Kriegsgebiet
 * 
 * Diese Funktion gibt einen Array mit Gebieten zurück, die in dem
 * abgefragten Gebiet liegen. Die Arrays haben jeweils 4 Werte, nämlich
 * die Ecken eines Rechtecks, also z.B.
 * arr[0] = { 0,0 , 100,100 }; Rechteck 100x100
 * arr[1] = {200,0, 250,100 }; Rechteck  50x100
 */
function getStartPos($pos, $reli, $radius=null, $fx=null, $fy=null) {
  if($radius == null)
    $radius = getSettleRadius();

  if($radius < 2) $radius = 2;
    
  if($fx == null || $fy == null)
    getMapSize($fx, $fy);

  if($radius >= $fx/80) {
    $radius = intval($fx/80);
    $maximum = true;
  }
  else
    $maximum = false;


  if( ($fx%80) > 0)
    $correct = 20;
  else 
    $correct = 0;

  switch($pos) {

    // Hinterland
    // Beschreibt ein einfaches Rechteck.
    case 0:
      $factor = 1; //$radius > 4 ? 2 : 1;

      if($maximum) {
        $xs = 0;
        $xe = $fx;
        $ys = ($reli == 1) ? 0       : ($fy - $fy/5) ;
        $ye = ($reli == 1) ? ($fy/5) : $fy;
      }
      else {
        $xs = $fx/2 - $radius*40*$factor - $correct;
        $xe = $fx/2 + $radius*40*$factor + $correct;
        $ys = $fy/2 + ($reli == 1 ? (-$radius*40    - $correct) : $radius*40-40 + $correct);
        $ye = $fy/2 + ($reli == 1 ? (-$radius*40+40 - $correct) : $radius*40    + $correct);
      }
      
      $arr[0] = Array( $xs, $ys, $xe, $ye );
      return $arr;
      // Übergangsgebiet
      // Zwei Rechtecke, jeweils von Kartenmitte bis an Rand
    case 1:
      if($maximum) {
        $xs = 0;
        $xe = $fx;

        if($fy >= 600) {
          $ys = ($reli == 1) ? $fy/5       : ($fy/2 +80)   ;
          $ye = ($reli == 1) ? ($fy/2 -80) : ($fy - $fy/5) ;
        }
        else {
          $ys = ($reli == 1) ? $fy/5       : ($fy/2 +40)   ;
          $ye = ($reli == 1) ? ($fy/2 -40) : ($fy - $fy/5) ;
        }

        $arr[0] = Array( $xs, $ys, $xe, $ye );
      }
      else {
        $xs = $fx/2 - $radius*40 - $correct;
        $xe = $fx/2 - $radius*40 + 40 - ($radius < 4 ? -$correct: $correct) ;
        $ys = $fy/2 + ($reli == 1 ? -$radius*40+40 - $correct : $correct );
        $ye = $fy/2 + ($reli == 1 ? -$correct                 : $radius*40-40+$correct );
        $arr[0] = Array( $xs, $ys, $xe, $ye ); 

        $xs = $fx/2 + $radius*40-40 + ($radius < 4 ? -$correct : $correct);
        $xe = $fx/2 + $radius*40    + $correct;
        $arr[1] = Array( $xs, $ys, $xe, $ye ); 
      }
      return $arr;
    case 2:
      if($maximum) {
        $xs = 0;
        $xe = $fx;
    
        if($fy >= 600) {
          $ys = $fy/2 -80;
          $ye = $fy/2 +80;
        }
        else {
          $ys = $fy/2 -40;
          $ye = $fy/2 +40;
        }
      }
      else {

        $xs = $fx/2 - $radius*40 + 40 - $correct;
        $xe = $fx/2 + $radius*40 - 40 + $correct;

        // Bei sehr kleinem Radius korrigiere das Kriegsgebiet
        if($radius < 4) $correct = -$correct;

        $ys = $fy/2 -40-$correct;
        $ye = $fy/2 +40+$correct;
      }

      
      $arr[0] = Array( $xs, $ys, $xe, $ye );
      return $arr;
    default:
      log_fatal_error("Startposition $pos nicht gültig!");
  }
  
  return null;
}


function searchStartPosNew($pid) {
  $pid = intval($pid);
  $player_res = do_mysql_query("SELECT religion,pos,nooblevel FROM player WHERE id=".$pid);

  if ($p = mysql_fetch_assoc($player_res)) {
    if ($p['pos'] != null) {
      if($p['pos'] > 3)
        $pos = 6 - $p['pos'];
      else
        $pos = $p['pos'] - 1;
    }
    else {
      // Falls der Spieler zum zweiten Mal platziert wird, dann ab ins Hinterland!
      $pos = 0;
    }
    
    $tries = 0;
    while ($tries++ < 5) 
      {
        $where  = getStartPosWhere($pos, $p['religion']);
        $res = do_mysql_query("SELECT * FROM startpositions WHERE ".$where);
        
        $num = mysql_num_rows($res);
        if($num == 0) {
          printf("<html><body>Es sind keine Startpositionen mehr frei. Bitte melden Sie sich bei einem Administrator.\n</body></html>\n", $where, $num );
          return null;
        }
        else {
          // printf("<html><body>%s<br>\nStartPositions: %d<br>\n</body></html>\n", $where, $num );

          $res = do_mysql_query("SELECT * FROM startpositions WHERE ".$where." ORDER BY rand() LIMIT 1");

          if($res && ($s = mysql_fetch_assoc($res))) {
            $x = $s['x'];
            $y = $s['y'];

            // Nachschauen, ob es Städte in der Nähe gibt
            $sql = sprintf("SELECT x,y FROM city AS c LEFT JOIN map USING(id) ".
            " WHERE (x - %d <= 4 AND x - %d >= -4) AND ".
            "       (y - %d <= 4 AND y - %d >= -4) ",
            $x, $x, $y, $y
            );
             
            
            // Kontlikt-Positionen auflösen und löschen.
            $conflict = do_mysql_query($sql);
            $num = mysql_num_rows($conflict);
            if($num > 0) {
              $msg = sprintf("%d conflicting cities for startpos (%d,%d)\n", $num, $x, $y);
              log_fatal_error($msg);
              do_mysql_query("DELETE FROM startpos WHERE x = $x AND y = $y");

              // Weitersuchen
              continue;
            }


            $xy = do_mysql_query_fetch_assoc("SELECT id FROM map WHERE x = $x AND y = $y");
            if($xy) {
              $ret['id'] = $xy['id'];
              $ret['x']  = $x;
              $ret['y']  = $y;
              $search = false;
              return $ret;
            } //  if($xy)
            else {
              return null;
            }
          } /* if($res && ($s = mysql_fetch_assoc($res))) */
        }
      } // while
  }
  else {
    log_fatal_error("Spieler %pid exisitiert nicht.");
  }
  
  return null;
}


function searchStartPos($pid) {
  getMapSize($fx, $fy);
  $pid = intval($pid);
  $player_res = do_mysql_query("SELECT religion,pos,nooblevel FROM player WHERE id=".$pid);

  if ($p = mysql_fetch_assoc($player_res)) {
    if ($p['pos'] != null) {
      $rndnum = ($p['pos'] - 1) % 3;
    }
    else {
      $rndnum = uniqid(rand())%3;
    }

    if ($p['religion'] == 1) {
      if ($rndnum==0) {
        $ystart=0; $yend=floor($fy/6)-1;
      }
      else if ($rndnum==1) {
        $ystart=floor($fy/6); $yend=floor($fy*2/6)-1;
      }
      else {
        $ystart=floor($fy*2/6); $yend=ceil($fy*4/6)-1;
      }
    }
    else if ($p['religion'] == 2) {
      if ($rndnum==0) {
        $ystart=floor($fy*2/6); $yend=ceil($fy*4/6)-1;
      }
      else if ($rndnum==1) {
        $ystart=ceil($fy*4/6); $yend=ceil($fy*5/6)-1;
      }
      else {
        $ystart=ceil($fy*5/6); $yend=$fy-1;
      }
    }


    $search = true;
    while ($search) {
      $startpos_res=do_mysql_query("SELECT x,y FROM startpositions WHERE y>=".$ystart." AND y<=".$yend." ORDER BY RAND() LIMIT 1");
      // sollte nur sehr sehr selten eintreten
      if (mysql_num_rows($startpos_res)==0) {
        log_fatal_error("Startpositionen ausgegangen ystart:".$ystart.", yend:".$yend);
        return;
      }
      $xy = mysql_fetch_assoc($startpos_res);
      $cityid_res = do_mysql_query("SELECT id FROM map WHERE x=".$xy['x']." AND y=".$xy['y']." AND type = 2");
      $inway = do_mysql_query("SELECT city.id FROM city LEFT JOIN map USING(id) ".
                                  " WHERE x >= ".max(0, $xy['x']-4)." AND x <= ".min($fx-1, $xy['x']+4)." ".
                                  " AND y >= ".max(0, $xy['y']-4)." AND y <= ".min($fy-1, $xy['y']+4) );

      // Es dürfen keine Städte im weg sein und die Startpos muß gültig sein
      if (mysql_num_rows($cityid_res) > 0 && mysql_num_rows($inway) == 0) {
        $city = mysql_fetch_assoc($cityid_res);
        $ret['id'] = $city['id'];
        $ret['x']  = $xy['x'];
        $ret['y']  = $xy['y'];
        return $ret;
      }
    } // while
  } // if $p
  else {
    log_fatal_error("Spieler %pid exisitiert nicht.");
  }
  return null;
}

/**
 * Neue Stadt einfügen
 */
function insertCity($mapid, $owner, $reli, $capital = false) {
  do_mysql_query("INSERT INTO city (id,name,capital,religion,owner,prosperity) VALUES (".$mapid.",'Startstadt ".$mapid."',".( $capital ? "1" : "0" ).",".$reli.",".$owner.",".($capital ? 2000 : 1000).")");

  // Bei der ERSTEN Startstadt ein paar Extra Gebäude gewähren
  if ($capital) {
    if (defined("BUILDING_HS_LVL1")) {
      do_mysql_query("INSERT INTO citybuilding (city,building,count) VALUES (".$mapid.", ".BUILDING_HS_LVL1.", 1)");
    }
    //if (defined("BUILDING_SCHOOL"))
    //  do_mysql_query("INSERT INTO citybuilding (city,building,count) VALUES (".$mid['id'].", ".BUILDING_SCHOOL.", 1)");
  }
  else {
    // Farm nur einfügen, wenn es nicht die Startstadt ist und bereits ne Residenz erhalten hat
    do_mysql_query("INSERT INTO citybuilding (city,building,count) VALUES (".$mapid.",1,1)");
  }

  $xy = do_mysql_query_fetch_assoc("SELECT x,y FROM map WHERE id = ".$mapid);
  do_mysql_query("DELETE FROM startpositions WHERE x=".$xy['x']." and y=".$xy['y']);
   
  do_mysql_query("INSERT INTO message (category, recipient, sender, date, header, body) VALUES (3, ".$owner.", 'SERVER', UNIX_TIMESTAMP(), 'Startstadt umbenennen!', 'Ihnen wurde ein automatischer Name für die Startstadt generiert. Benennt diese Stadt im Rathaus um!')");
}


/**
 * Stadtgrössenstufe auf Basis der Einwohnerzahl ermitteln
 * param:  Einwohnerzahl
 * return: Stadtgrössenstufe */
if(!function_exists("get_citysize_level")) {
  function get_citysize_level($pop) {
    if ($pop>=18001) return 9;
    if ($pop>=12001) return 8;
    if ($pop>=6000)  return 7;
    if ($pop>=3000)  return 6;
    if ($pop>=1000)  return 5;
    if ($pop>=500)   return 4;
    if ($pop>=200)   return 3;
    if ($pop>=50)    return 2;
    return 1;
  }
}


if (!function_exists("get_siege_min_armysize")) {
  function get_siege_min_armysize() {

  }
}

/**
 * Einen gerundeten Wert für Einwohner zurückliefern,
 * damit man anhand der Toplist keine Rückschlüsse auf
 * Rekrutierungen ziehen kann.
 *
 * @param unknown_type $amount
 * @return unknown
 */
function get_rounded_value($amount) {
  if ($amount > 80000) $roundfak = 10000;
  else if ($amount > 50000) $roundfak = 5000;
  else if ($amount > 20000) $roundfak = 2000;
  else if ($amount> 10000) $roundfak = 1000;
  else if ($amount> 5000) $roundfak = 500;
  else if ($amount>2000) $roundfak = 100;
  else if ($amount>1000) $roundfak = 40;
  else $roundfak = 20;

  return round($amount / $roundfak) * $roundfak;
}

function get_rounded_population($amount) {
  return get_rounded_value($amount); 
}


function getLoyalityCostFactor($loy) {
  return ceil( 1.0 + (100.0 - $loy) / 20 );
}


function get_loyality_string($loy) {
  if(!defined("ENABLE_LOYALITY") || ENABLE_LOYALITY == 0) return "100%";
  
  $max = defined("MAX_LOYALITY") ? MAX_LOYALITY : 10000.0;
  
  if     ($loy*100 / $max < 10) return "rebellisch";
  else if($loy*100 / $max < 25) return "erzürnt";
  else if($loy*100 / $max < 50) return "abgeneigt";
  else if($loy*100 / $max < 75) return "moderat";
  else if($loy*100 / $max < 90) return "loyal";
  else return "sehr loyal";
}

// Strings für Wohlstand
// f = female, m = male, n = neutrum
$prosperity_strings['a'] = array ( "Bettelarm",   "Arm",  "Florierend",
                                   "Wohlhabend", "Reich",  "Stinkreich" );
$prosperity_strings['f'] = array ( "Bettelarme ",   "Arme ",  "Florierende ",
                                   "Wohlhabende ", "Reiche ",  "Stinkreiche " );
$prosperity_strings['m'] = array ( "Bettelarmer ",  "Armer ", "Florierender ",
                                   "Wohlhabender ","Reicher ", "Stinkreicher " );
$prosperity_strings['n'] = array ( "Bettelarmes ",  "Armes ", "Florierendes ",
                                   "Wohlhabendes ","Reiches ", "Stinkreiches " );

function get_prosperity_level($pop, $prosp) {
  if ($pop < 10) $pop = 10;
  if ($prosp / $pop < 2)      $l = 0;
  else if ($prosp / $pop < 5) $l = 1;
  else if ($prosp / $pop < 10)$l = 2;
  else if ($prosp / $pop < 20)$l = 3;
  else if ($prosp / $pop < 40)$l = 4;
  else                        $l = 5;
  return $l;
}

function get_prosperity_attrib($pop, $prosp) {
  return $GLOBALS['prosperity_strings']['a'][get_prosperity_level($pop, $prosp)];
}

if(!function_exists("get_population_string")) {
  function get_population_string($pop, $prosp=-1, $with_title = false) {
    global $prosperity_strings;
    if ($prosp >= 0)
    $p = $prosperity_strings;

    $l = get_prosperity_level($pop, $prosp);

    switch( get_citysize_level($pop) ) {
      case 9:
        return ($with_title ? "Die " : "").$p['f'][$l]."Weltstadt";
      case 8:
        return ($with_title ? "Die " : "").$p['f'][$l]."Metropole";
      case 7:
        return ($with_title ? "Die " : "").$p['f'][$l]."Großstadt";
      case 6:
        return ($with_title ? "Die " : "").$p['f'][$l]."Stadt";
      case 5:
        return ($with_title ? "Die " : "").$p['f'][$l]."Kleinstadt";
      case 4:
        return $with_title ? ("Das ".$p['f'][$l]."große Dorf") : ($p['n'][$l]."großes Dorf");
      case 3:
        return $with_title ? ("Das ".$p['f'][$l]."Dorf") : ($p['n'][$l]."Dorf");
      case 2:
        return ($with_title ? "Die " : "").$p['f'][$l]."Siedlung";
      case 1:
      default:
        return $with_title ? ("Der ".$p['f'][$l]."Bauernhof") : ($p['m'][$l]."Bauernhof");
    }
  }
}

if(!function_exists("get_long_population_string")) {
  function get_long_population_string($pop, $isgrossgeschrieben) {
    $ret;
    switch( get_citysize_level($pop) ) {
      case 9:
      case 8:
      case 7:
      case 6:
      case 5:
      case 2:
        if($isgrossgeschrieben == true)
        $ret = "Die ";
        else
        $ret = "die ";
        break;
      case 4:
      case 3:
        if($isgrossgeschrieben == true)
        $ret = "Das ";
        else
        $ret = "das ";
        break;
      case 1:
      default:
        if($isgrossgeschrieben == true)
        $ret = "Der ";
        else
        $ret = "der ";
        break;
    }
    $ret .= get_population_string($pop);
    return $ret;
  }
}


if(!function_exists("getExtBrDate")) {
  function getExtBrDate($timestamp) {
    if($timestamp == 0) {
      $timestamp = "-";
    } else {
      $year = date("Y",$timestamp);
      $month = date("m",$timestamp);
      $dayD = date("w",$timestamp);
      $day = date("d",$timestamp);
      $hours = date("G",$timestamp);
      $mins = date("i",$timestamp);
      $timestamp = $day.".".$month.".".$year."<br />".$hours.":".$mins;
    }
    return $timestamp;
  }
}


if(!function_exists("getExtDate")) {
  function getExtDate($timestamp) {
    if($timestamp == 0) {
      $timestamp = "-";
    } else {
      $year = date("Y",$timestamp);
      $month = date("m",$timestamp);
      $dayD = date("w",$timestamp);
      $day = date("d",$timestamp);
      $hours = date("G",$timestamp);
      $mins = date("i",$timestamp);
      $timestamp = $day.".".$month.".".$year." - ".$hours.":".$mins;
    }
    return $timestamp;
  }
}


if(!function_exists("quote")) {
  function quote($string) {
    $string = ereg_replace("\[quote\]","<div class=\"tblhead\" style=\"margin:5px; padding:3px; border: 1px dashed #BABA69;background-color:#FFFFE5;\"><b><i>Zitat:</i></b><br/>",$string);
    $string = ereg_replace("\[/quote\]","</div>",$string);
    $string = ereg_replace("\[doped\]","<br /><br /><div style=\"font-size:10px; color:#BABA69;\">",$string);
    $string = ereg_replace("\[/doped\]","</div>",$string);
    $string = str_replace(":)","<img src=\"./images/smiles/icon_smile.gif\" border=\"0\" />",$string);
    $string = str_replace(":D","<img src=\"./images/smiles/icon_biggrin.gif\" border=\"0\" />",$string);
    $string = str_replace(":(","<img src=\"./images/smiles/icon_sad.gif\" border=\"0\" />",$string);
    $string = str_replace(":o:","<img src=\"./images/smiles/icon_surprised.gif\" border=\"0\" />",$string);
    $string = str_replace(":shock:","<img src=\"./images/smiles/icon_eek.gif\" border=\"0\" />",$string);
    $string = str_replace(":?","<img src=\"./images/smiles/icon_confused.gif\" border=\"0\" />",$string);
    $string = str_replace(":lol:","<img src=\"./images/smiles/icon_lol.gif\" border=\"0\" />",$string);
    $string = str_replace(":x","<img src=\"./images/smiles/icon_mad.gif\" border=\"0\" />",$string);
    $string = str_replace(":P","<img src=\"./images/smiles/icon_razz.gif\" border=\"0\" />",$string);
    $string = str_replace(":oops:","<img src=\"./images/smiles/icon_redface.gif\" border=\"0\" />",$string);
    $string = str_replace(":cry:","<img src=\"./images/smiles/icon_cry.gif\" border=\"0\" />",$string);
    $string = str_replace(":evil:","<img src=\"./images/smiles/icon_evil.gif\" border=\"0\" />",$string);
    $string = str_replace(":twisted:","<img src=\"./images/smiles/icon_twisted.gif\" border=\"0\" />",$string);
    $string = str_replace(":roll:","<img src=\"./images/smiles/icon_rolleyes.gif\" border=\"0\" />",$string);
    $string = str_replace(":wink:","<img src=\"./images/smiles/icon_wink.gif\" border=\"0\" />",$string);
    $string = str_replace(":!:","<img src=\"./images/smiles/icon_exclaim.gif\" border=\"0\" />",$string);
    $string = str_replace(":quest:","<img src=\"./images/smiles/icon_question.gif\" border=\"0\" />",$string);
    $string = str_replace(":idea:","<img src=\"./images/smiles/icon_idea.gif\" border=\"0\" />",$string);
    $string = str_replace(":arrow:","<img src=\"./images/smiles/icon_arrow.gif\" border=\"0\" />",$string);
    $string = str_replace(":root:","<img src=\"ich-bin-root.jpg\" border=\"0\" />",$string);
    return $string;
  }
}


if(!function_exists("getClanStatusByName")) {
  function getClanStatusByName($name) {
    $res0=do_mysql_query("SELECT clanstatus,points,religion FROM player WHERE name = '".mysql_escape_string($name)."'");
    $data0 = mysql_fetch_assoc($res0);
    $status = $data0['clanstatus'];
    $points = number_format($data0['points'],0,",",".");
    $religion = $data0['religion'];
    if($religion == 1) { $religion = "Christ"; } else { $religion = "Moslem"; }
    if ($status == 63) {
      $status = "Ordensleiter";
    } else {
      if ($status == 0) { $status = "Member"; }
      if ($status == 1) { $status = "Finanzminister"; }
      if ($status == 2) { $status = "Innenminister"; }
      if ($status == 4) { $status = "Au&szlig;enminister"; }
    }
    $status = $status."<br /><br />".$points."&nbsp;Pt.<br />".$religion;
    return $status;
  }
}


if(!function_exists("insertBBForm")) {
  function insertBBForm($colspan) {
    echo "<tr class=\"tblbody\"><td colspan=\"".$colspan."\" style=\"text-align:center;\">\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[b]','[/b]')\"><b>B</b></button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[i]','[/i]')\"><i>I</i></button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[u]','[/u]')\"><u>U</u></button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[quote]','[/quote]')\">Quote</button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[code]','[/code]')\">Code</button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[list]','[/list]')\">List</button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[img]','[/img]',2)\">IMG</button>\n";
    echo "<button class=\"tblbody\" style=\"width:12%;\" onClick=\"return bbCode('[url]','[/url]',3)\">URL</button>\n";
    echo "</tr><tr class=\"tblbody\"><td colspan=\"".$colspan."\" style=\"text-align:center;\">\n";
    echo "<select style=\"font-weight:bold;\" class=\"tblhead\" name=\"color\" onchange=\"return bbCode('[color=' + this.form.color.options[this.form.color.selectedIndex].value + ']', '[/color]')\">\n";
    echo "<option value=\"0\" selected=\"selected\">Schriftfarbe</option>\n";
    echo "<option value=\"#000000\">Standard</option>\n";
    echo "<option style=\"color:darkred; background-color:transparent\" value=\"darkred\">Dunkelrot</option>\n";
    echo "<option style=\"color:red; background-color:transparent\" value=\"red\">Rot</option>\n";
    echo "<option style=\"color:orange; background-color:transparent\" value=\"orange\">Orange</option>\n";
    echo "<option style=\"color:brown; background-color:transparent\" value=\"brown\">Braun</option>\n";
    echo "<option style=\"color:yellow; background-color:transparent\" value=\"yellow\">Gelb</option>\n";
    echo "<option style=\"color:green; background-color:transparent\" value=\"green\">Grün</option>\n";
    echo "<option style=\"color:olive; background-color:transparent\" value=\"olive\">Oliv</option>\n";
    echo "<option style=\"color:cyan; background-color:transparent\" value=\"cyan\">Cyan</option>\n";
    echo "<option style=\"color:blue; background-color:transparent\" value=\"blue\">Blau</option>\n";
    echo "<option style=\"color:darkblue; background-color:transparent\" value=\"darkblue\">Dunkelblau</option>\n";
    echo "<option style=\"color:indigo; background-color:transparent\" value=\"indigo\">Indigo</option>\n";
    echo "<option style=\"color:violet; background-color:transparent\" value=\"violet\">Violett</option>\n";
    echo "<option style=\"color:white; background-color:transparent\" value=\"white\">Weiß</option>\n";
    echo "<option style=\"color:black; background-color:transparent\" value=\"black\">Schwarz</option>\n";
    echo "<option style=\"color:cadetblue; background-color:transparent\" value=\"cadetblue\">Kadet Blau</option>\n";
    echo "<option style=\"color:coral; background-color:transparent\" value=\"coral\">Koralle</option>\n";
    echo "<option style=\"color:crimson; background-color:transparent\" value=\"crimson\">Crimson</option>\n";
    echo "<option style=\"color:tomato; background-color:transparent\" value=\"tomato\">Tomate</option>\n";
    echo "<option style=\"color:seagreen; background-color:transparent\" value=\"seagreen\">See Grün</option>\n";
    echo "<option style=\"color:darkorchid; background-color:transparent\" value=\"darkorchid\">Dunkle Orchidee</option>\n";
    echo "<option style=\"color:chocolate; background-color:transparent\" value=\"chocolate\">Schokolade</option>\n";
    echo "<option style=\"color:deepskyblue; background-color:transparent\" value=\"deepskyblue\">Tiefseeblau</option>\n";
    echo "<option style=\"color:gold; background-color:transparent\" value=\"gold\">Gold</option>\n";
    echo "<option style=\"color:gray; background-color:transparent\" value=\"gray\">Grau</option>\n";
    echo "<option style=\"color:midnightblue; background-color:transparent\" value=\"midnightblue\">Mitternachtsblau</option>\n";
    echo "<option style=\"color:darkgreen; background-color:transparent\" value=\"darkgreen\">Dunkelgrün</option>\n";
    echo "</select>\n";
    echo "<select class=\"tblhead\" style=\"font-weight:bold;\" name=\"font\" onchange=\"return bbCode('[size=' + this.form.font.options[this.form.font.selectedIndex].value + ']', '[/size]');\">\n";
    echo "<option value=\"0\" selected=\"selected\">Schriftgröße</option>\n";
    echo "<option value=\"7\">Winzig</option>\n";
    echo "<option value=\"9\">Klein</option>\n";
    echo "<option value=\"12\">Normal</option>\n";
    echo "<option value=\"18\">Groß</option>\n";
    echo "<option  value=\"24\">Riesig</option>\n";
    echo "</select>&nbsp;\n";
    echo "<a href=\"#\" onclick=\"document.theForm.theText.value = '';\">Textfeld leeren</a>\n";
    echo "</td></tr>\n";
    echo "<tr><td class=\"tblbody\" colspan=\"".$colspan."\" style=\"text-align:center; padding-top:5px;padding-bottom:5px;\">\n";
    echo "<img src=\"./images/smiles/icon_smile.gif\" border=\"0\" onclick=\"return bbCode(':)','')\" alt=\":)\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_biggrin.gif\" border=\"0\" onclick=\"return bbCode(':D','')\" alt=\":D\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_sad.gif\" border=\"0\" onclick=\"return bbCode(':(','')\" alt=\":(\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_surprised.gif\" border=\"0\" onclick=\"return bbCode(':o:','')\" alt=\":o\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_eek.gif\" border=\"0\" onclick=\"return bbCode(':shock:','')\" alt=\":shock:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_confused.gif\" border=\"0\" onclick=\"return bbCode(':?','')\" alt=\":?\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_lol.gif\" border=\"0\" onclick=\"return bbCode(':lol:','')\" alt=\":lol:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_mad.gif\" border=\"0\" onclick=\"return bbCode(':x','')\" alt=\":x\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_razz.gif\" border=\"0\" onclick=\"return bbCode(':P','')\" alt=\":P\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_redface.gif\" border=\"0\" onclick=\"return bbCode(':oops:','')\" alt=\":oops:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_cry.gif\" border=\"0\" onclick=\"return bbCode(':cry:','')\" alt=\":cry:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_evil.gif\" border=\"0\" onclick=\"return bbCode(':evil:','')\" alt=\":evil:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_twisted.gif\" border=\"0\" onclick=\"return bbCode(':twisted:','')\" alt=\":twisted:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_rolleyes.gif\" border=\"0\" onclick=\"return bbCode(':roll:','')\" alt=\":roll:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_wink.gif\" border=\"0\" onclick=\"return bbCode(':wink:','')\" alt=\":wink:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_exclaim.gif\" border=\"0\" onclick=\"return bbCode(':!:','')\" alt=\":!:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_question.gif\" border=\"0\" onclick=\"return bbCode(':quest:','')\" alt=\":?:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_idea.gif\" border=\"0\" onclick=\"return bbCode(':idea:','')\" alt=\":idea:\" />&nbsp;\n";
    echo "<img src=\"./images/smiles/icon_arrow.gif\" border=\"0\" onclick=\"return bbCode(':arrow:','')\" alt=\":arrow:\" />&nbsp;\n";
    echo "</td></tr>\n";
  }
}


if(!function_exists("bbCode")) {
  function bbCode($text, $url = TRUE, $bb = TRUE) {
    $max_l = 100;

    $lword_replace = "-<br />";
    $header_php = '<br/><br/><div>PHP-CODE:<br/><br/><code>';
    $footer_php = '</code></div><br/>';
    $header_quote = '<div class=\"tblhead\" style=\"margin-top:5px; margin-bottom:5px; margin-left:5px; margin-right:5px;\">Zitat:<br/>';
    $footer_quote = '</div>';
    $header_code = '<br/><br/><div>CODE:<br/><br/><pre>';
    $footer_code = '</pre></div><br/>';

    $c = md5(time());
    $pattern = "/\[php\](.*?)\[\/php\]/si";
    preg_match_all ($pattern, $text, $results);
    for($i=0;$i<count($results[1]);$i++) {
      $text = str_replace($results[1][$i], $c.$i.$c, $text);
    }

    $text = htmlentities($text);
    $lines = explode("\n", $text);
    $merk = $max_l;
    for($n=0;$n<count($lines);$n++) {
      $words = explode(" ",$lines[$n]);
      $count_w = count($words)-1;
      if($count_w >= 0) {
        for($i=0;$i<=$count_w;$i++) {
          $max_l = $merk;
          $tword = trim($words[$i]);
          $tword = preg_replace("/\[(.*?)\]/si", "", $tword);
          $all = substr_count($tword, "http://") + substr_count($tword, "https://") + substr_count($tword, "www.") + substr_count($tword, "ftp://");
          if($all > 0) {
            $max_l = 200;
          }
          if(strlen($tword)>$max_l) {
            $words[$i] = chunk_split($words[$i], $max_l, $lword_replace);
            $length = strlen($words[$i])-5;
            $words[$i] = substr($words[$i],0,$length);
          }
        }
        $lines[$n] = implode(" ", $words);
      } else {
        $lines[$n] = chunk_split($lines[$n], $max_l, $lword_replace);
      }
    }
    $text = implode("\n", $lines);
    $text = nl2br($text);

    if($url) {
      $text = preg_replace('"(( |^)((ftp|http|https){1}://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)"i',
			   '<a href="\1" target="_blank">\\1</a>', $text);
      $text = preg_replace('"( |^)(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)"i',
			   '\\1<a href="http://\2" target="_blank">\\2</a>', $text);
      $text = preg_replace('"([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})"i',
			   '<a href="mailto:\1">\\1</a>', $text);
    }

    if($bb) {
      $text = preg_replace("/\[b\](.*?)\[\/b\]/si",
			   "<b>\\1</b>", $text);
      $text = preg_replace("/\[i\](.*?)\[\/i\]/si",
			   "<i>\\1</i>", $text);
      $text = preg_replace("/\[u\](.*?)\[\/u\]/si",
			   "<u>\\1</u>", $text);
      $text = preg_replace("/\[list\](.*?)\[\/list\]/si",
			   "<ul>\\1</ul>", $text);
      $text = preg_replace("/\[list=(.*?)\](.*?)\[\/list\]/si",
			   "<ol type=\"\\1\">\\2</ol>", $text);
      $text = preg_replace("/\[\*\](.*?)\\n/si",
			   "<li>\\1</li>", $text);
      $text = preg_replace("/\[align=(.*?)\](.*?)\[\/align\]/si",
			   "<div align=\"\\1\">\\2</div>", $text);
      $text = preg_replace("/\[color=(.*?)\](.*?)\[\/color\]/si",
			   "<font color=\"\\1\">\\2</font>", $text);
      $text = preg_replace("/\[size=(.*?)\](.*?)\[\/size\]/si",
			   //"<font size=\"\\1\">\\2</font>", $text);
			   "<font style=\"font-size:12px;\">\\2</font>", $text);

      if(0) {
        $text = preg_replace("/\[img\](.*?)\[\/img\]/si",
			     "<img src=\"\\1\" border=\"0\" maxwidth=\"100%\">", $text);
      }

      $text = preg_replace("/\[code\](.*?)\[\/code\]/si",
			   $header_code.'\\1'.$footer_code, $text);
      $text = preg_replace("/\[url=http:\/\/(.*?)\](.*?)\[\/url\]/si",
			   "<a href=\"http://\\1\" target=\"_blank\">\\2</a>", $text);
      $text = preg_replace("/\[url=(.*?)\](.*?)\[\/url\]/si",
			   "<a href=\"http://\\1\" target=\"_blank\">\\2</a>", $text);
      $text = preg_replace("/\[email=(.*?)\](.*?)\[\/email\]/si",
			   "<a href=\"mailto:\\1\">\\2</a>", $text);
    }

    for($i=0;$i<count($results[1]);$i++) {
      ob_start();
      highlight_string(trim($results[1][$i]));
      $ht = ob_get_contents();
      ob_end_clean();
      $all = $header_php.$ht.$footer_php;
      if(function_exists("str_ireplace")) {
        $text = str_replace("[php]".$c.$i.$c."[/php]",$all,$text);
      } else {
        $text = str_replace("[php]".$c.$i.$c."[/php]",$all,$text);
        $text = str_replace("[PHP]".$c.$i.$c."[/PHP]",$all,$text);
      }
    }
    $text = quote($text);
    $text = ereg_replace("&lt;/b&gt;","</b>",$text);
    $text = ereg_replace("&lt;b&gt;","<b>",$text);
    $text = ereg_replace("&lt;br /&gt;","<br />",$text);
    $text = ereg_replace("&lt;br/&gt;","<br/>",$text);
    $text = ereg_replace("&lt;br&gt;","<br>",$text);
    return $text;
  }
}


/** 
 *  param:  Player Objekt, Map Startpositionen x und y
 *  return: Map Objekt Referenz  
 */
if(!function_exists("MapFactory")) {
  function &MapFactory($player) {
    getMapSize($fx, $fy);
    //    $mapsize = do_mysql_query_fetch_assoc("SELECT max(x)+1 AS x, max(y)+1 AS y FROM map");
    //    $fx = $mapsize['x'];
    //    $fy = $mapsize['y'];

    // Abhängig von Spielereinstellung Map instanziieren
    switch ($player->getMapVersion()) {
      case "v0":
        include_once("includes/map.v0.class.php");
        return new MapVersion0($fx,$fy,floor($player->getMapSize()/100),($player->getMapSize()%100),
        40,40,$player->getID());
        break;
      case "v1":
        include_once("includes/map.v1.class.php");
        return new MapVersion1($fx,$fy,floor($player->getMapSize()/100),($player->getMapSize()%100),
        40,40,$player->getID());
        break;
      case "v2":
        include_once("includes/map.v2.class.php");
        return new MapVersion2($fx,$fy, floor($player->getMapSize()/100),($player->getMapSize()%100),
        40,40,$player->getID());
        break;
      case "v3":
      default:
        include_once("includes/map.v3.class.php");
        return new MapVersion3($fx,$fy,floor($player->getMapSize()/100),($player->getMapSize()%100),
        40,40,$player->getID());
        break;
    }
  }
}

/* eagle 27.11.04: Ordenbezeichnung ermitteln
 *  param:  Ordens-ID
 *  return: Ordenbezeichnung */
if(!function_exists("get_clan_name")) {
  function get_clan_name($id) {
    $res = do_mysql_query( 'SELECT name '.
			   'FROM clan '.
			   'WHERE id = '.intval($id) );
    $res = mysql_fetch_assoc($res);
    return $res['name'];
  }
}

if(!function_exists("showAvatarDate")) {
  function showAvatarDate($id) {
    $upload_dir = AVATAR_DIR;
    $filename=$upload_dir.intval($id).".jpg";
    if (file_exists($filename)) {
      echo date("d.m.Y H:i", filemtime($filename));
    }
  }
}

function get_info_link($name, $type, $avatar=0) {
  global $imagepath;

  switch ($type) {
    case "player" :
      $name_text = strlen($name) < 25 ? $name : substr($name, 0, 25)."<br>".substr($name, 25);
      
      if($avatar != 1) {
        $string = "<a href=\"info.php?show=player&exactmatch=1&name=".urlencode($name)."\">".$name_text.' <img src="'.$imagepath."/avatar_exists.gif\" border=\"0\"></a>";
      }
      else {
        $string = "<a href=\"info.php?show=player&exactmatch=1&name=".urlencode($name)."\">".$name_text."</a>";

        $res1 = do_mysql_query("SELECT id,avatar FROM player WHERE name='".$name."'");
        $data1 = mysql_fetch_assoc($res1);
        if($data1['avatar']==2) {
          $string = "<a href=\"info.php?show=player&exactmatch=1&name=".urlencode($name)."\"  onMouseOver=\"showWMTT('".$data1['id']."')\" onMouseOut=\"hideWMTT()\">".$name_text.' <img src="'.$imagepath."/avatar_exists.gif\" border=\"0\"></a>\n";
          $string .= " <div class=\"tooltip\" id=\"".$data1['id']."\"><img src=\"avatar.php?id=".$data1['id']."\" /></div>";
        }
      }
      break;
    case "clan":
      $string="<a href=\"info.php?show=clan&name=".urlencode($name)."\">".$name."</a>";
      break;
    default:
      $string="<a href=\"info.php?show=clan&name=".urlencode($name)."\">".$name."</a>";
  } // switch
  return $string;
}


/** Macht eine Tabellen-Zelle "highlighted" wenn der Parameter $show == $name **/
function printActive($name) {
  if($GLOBALS['show'] == $name) echo 'style="text-decoration: underline; "';
}

/* Ist die Stadt unter Belagerung?
 *
 * gibt die Anzahl der Sekunden zurück, seitdem die Stadt unter Belagerung steht
 * -1 wird returniert, falls die Stadt nicht unter Belagerung steht
 */
function getSiegeTime ($id) {
  $res = do_mysql_query("SELECT max(UNIX_TIMESTAMP()-endtime) AS maxtime ".
                        " FROM army WHERE endtime < UNIX_TIMESTAMP() AND army.mission='siege' AND army.end = ".intval($id) );
  if (mysql_numrows($res) == 0) {
    return -1;
  }
  else {
    $siegetime = mysql_fetch_assoc($res);
    if ($siegetime['maxtime'] > 0 )
    return $siegetime['maxtime'];
    else
    return -1;
  }
}


// Produktionsfaktor unter Belagerung
function getSiegeFactor ($time) {
  if(defined("SPEED") && SPEED )
    $max_siege_time =  6*3600;
  else
    $max_siege_time = 24*3600;

  // Produktionsfaktor
  if ($time > $max_siege_time)
  return 0;
  else if ($time <= 0)
  return 1;
  else
  return 1 - $time / $max_siege_time;
}


// marktplatz rücknahem!!!
// erklärung franzl falls sich hier jemand frag wtf ist das
// wurde ein waffenangebot oder eine waffennachfrage am markt positioniert wird lagerplatz dafür reserviert
// dieser muss frei gegeben werden. außerdem müssen die waffen wieder zurück in die stadt
// wenn jemand ne function dafür schreiben will... gerne
function marketRetractionForCity($cityid) {
  $resA = do_mysql_query("SELECT id,wantsType,wantsQuant,hasType,hasQuant,player,timestamp,city FROM market WHERE city=".intval($cityid) );

  while ($dataA = mysql_fetch_assoc($resA)) {
    if ($dataA['wantsType'] == "shortrange" ||
        $dataA['wantsType'] == "longrange" ||
        $dataA['wantsType'] == "armor" ||
        $dataA['wantsType'] == "horse")
    {
      // lager wantsQuant Type frei geben
      //do_mysql_query("UPDATE city SET reserve_".$dataA['wantsType']." = reserve_".$dataA['wantsType']."-".$dataA['wantsQuant']." WHERE id = '".$data1['end']."'");
      if ($dataA['hasType'] == "shortrange" ||
      $dataA['hasType'] == "longrange" ||
      $dataA['hasType'] == "armor" ||
      $dataA['hasType'] == "horse") {
        do_mysql_query("UPDATE city SET reserve_".$dataA['hasType']." = reserve_".$dataA['hasType']."-".$dataA['hasQuant']." WHERE id = '".$data1['end']."'");
        do_mysql_query("UPDATE city SET ".$dataA['hasType']." = ".$dataA['hasType']."+".$dataA['hasQuant']." WHERE id = '".$data1['end']."'");
      }
      else {
        do_mysql_query("UPDATE player SET ".$dataA['hasType']." = ".$dataA['hasType']."+".$dataA['hasQuant']." WHERE id = '".$dataA['player']."'");
        $resB = do_mysql_query("SELECT name FROM city WHERE id = ".$dataA['city']);
        $dataB = mysql_fetch_assoc($resB);
        $gerres = array ('gold' => 'Gold', 'wood' => 'Holz', 'iron' => 'Eisen', 'stone' => 'Stein', 'shortrange' => 'Nahkampfwaffen', 'longrange' => 'Fernkampfwaffen', 'armor' => 'Rüstungen', 'horse' => 'Pferde');
        $message = "Sie haben die Stadt ".$dataB['name']." verloren!<br>Das dort Aufgegebene Angebot [Gesucht: ".$dataA['wantsQuant']." ".$gerres[$dataA['wantsType']]." | Geboten: ".$dataA['hasQuant']." ".$gerres[$dataA['hasType']]."] wurde vom Markt genommen.<br>Die Markth&auml;ndler refundieren euch ".$dataA['hasQuant']." ".$gerres[$dataA['hasType']]."!";
        do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$dataA['player'].",".time().",'Rückerstattung','".$message."',2)");
      }
      
      do_mysql_query("DELETE FROM market WHERE id=".$dataA['id']);
    } // wantsType
    else {
      if ($dataA['hasType'] == "shortrange" ||
          $dataA['hasType'] == "longrange" ||
          $dataA['hasType'] == "armor" ||
          $dataA['hasType'] == "horse")
      {
        do_mysql_query("UPDATE city SET reserve_".$dataA['hasType']." = reserve_".$dataA['hasType']."-".$dataA['hasQuant']." WHERE id = '".$data1['end']."'");
        do_mysql_query("UPDATE city SET ".$dataA['hasType']." = ".$dataA['hasType']."+".$dataA['hasQuant']." WHERE id = '".$data1['end']."'");
        $resB = do_mysql_query("SELECT name FROM city WHERE id = ".$dataA['city']);
        $dataB = mysql_fetch_assoc($resB);
        $gerres = array ('gold' => 'Gold', 'wood' => 'Holz', 'iron' => 'Eisen', 'stone' => 'Stein', 'shortrange' => 'Nahkampfwaffen', 'longrange' => 'Fernkampfwaffen', 'armor' => 'Rüstungen', 'horse' => 'Pferde');
        $message = "Sie haben die Stadt ".$dataB['name'].
            " verloren!<br>Das dort Aufgegebene Angebot [Gesucht: ".$dataA['wantsQuant'].
            " ".$gerres[$dataA['wantsType']]." | Geboten: ".$dataA['hasQuant']." ".
        $gerres[$dataA['hasType']].
            "] wurde vom Markt genommen.<br>Die Markth&auml;ndler refundieren euch nichts!";
        do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$dataA['player'].",".time().",'Rückerstattung','".$message."',2)");
        do_mysql_query("DELETE FROM market WHERE id=".$dataA['id']);
      }
    }    
  } // while
}

function prettyNumber($num) {
  $num=number_format($num,0,",",".");
  return $num;
}

function checkSettleCoords($ax,$ay) {
  getMapSize($fx, $fy);
  $ax = intval($ax);
  $ay = intval($ay);
  
  $res2 = do_mysql_query( 'SELECT id '.
                          'FROM map '.
                          'WHERE x='.$ax.' AND y='.$ay." AND type='2'");
  if (mysql_num_rows($res2) == 0)
  return "Es handelt sich nicht um ein Wiesenfeld";

  $res1 = do_mysql_query('SELECT city.name, city.id, map.x, map.y '.
                         'FROM map LEFT JOIN city ON city.id = map.id '.
                         'WHERE map.x>='.max(0,$ax-4).' AND map.x<='.min($fx-1, $ax+4).
                         ' AND map.y>='.max(0, $ay-4).' AND map.y<='.min($fx-1, $ay+4).
                         ' AND city.id IS NOT NULL' );
  if ( mysql_num_rows($res1) > 0) {
    $name = mysql_fetch_assoc($res1);
    return sprintf("Dieses Feld wird von einer anderen Stadt namens ".
				   "<a onClick=\"towninfo(%d); return false; \" href=\"info.php?show=town&id=%d\">%s</a> auf (%d:%d) blockiert.", 
    $name['id'], $name['id'], $name['name'], $name['x'], $name['y']);
  }


  return null;
}

function getPlayerClanFunctions($clanstatus) {
  if ($clanstatus & 1)
  $str.=" Finanzen ";
  if ($clanstatus & 2)
  $str.=" Inneres ";
  if ($clanstatus & 4)
  $str.=" &Auml;usseres ";
  if ($clanstatus == 63)
  $str=" Ordensleiter ";

  if (!$str)
  $str="Member";
  return $str;
}


function getPlayerScoutTime($pid) {
  $res_scout = do_mysql_query("SELECT max(res_scouttime) AS scouttime FROM building, citybuilding, city ".
                              " WHERE building.res_scouttime IS NOT NULL".
                              " AND building.id = citybuilding.building ".
                              " AND citybuilding.city = city.id AND city.owner = ".$pid );
  $data_scout= mysql_fetch_assoc($res_scout);

  if($data_scout['scouttime'] > 0)
  return $data_scout['scouttime'];
  else
  return 0;
}


function getAttacksAgainstPlayer($pid) {
  $num=0;
  $res_spy = do_mysql_query("
SELECT 
army.aid AS aid, army.owner AS owner, army.start AS start, army.end AS end,
army.endtime AS endtime, army.missiondata AS missiondata
 FROM army
  LEFT JOIN city ON army.end = city.id
  LEFT JOIN citybuilding ON city.id=citybuilding.city
  LEFT JOIN building ON building.id = citybuilding.building
 WHERE city.owner = $pid
  AND city.owner <> army.owner
  AND army.owner NOT IN (
    SELECT id1 FROM relation WHERE id2 = $pid AND type = 2
     UNION 
    SELECT id2 FROM relation WHERE id1 = $pid AND type = 2
  )
 GROUP BY army.aid, res_scouttime
 HAVING army.endtime <= (UNIX_TIMESTAMP() + COALESCE(max(res_scouttime), 0))
");

  if(mysql_num_rows($res_spy)>0) {
    $num=mysql_num_rows($res_spy);
  }
  return $num;
}

function dateDiff($t1,$t2) {
  $differenz=$t1-$t2;
  $tage=intval($differenz/60/60/24);
  $stunden=intval($differenz/60/60)%24;
  $minuten=intval($differenz/60)%60;
  $sekunden=$differenz%60;
  ($tage>1) ? $str.=$tage." Tage " : $str.="";
  ($tage>0) ? $str.="1 Tag " : $str.="";
  $str.=$stunden."h ".$minuten."m ". $sekunden."s";
  return $str;

}


function getConfigTimes($name, &$createtime, &$updatetime) {
  $res = do_mysql_query("SELECT updatetime, creationtime FROM config ".
                        " WHERE name='".mysql_escape_string($name)."'");
  if(mysql_num_rows($res) == 0 || !($val = mysql_fetch_assoc($res))) {
      $createtime = $updatetime = null;
      return false;
  }
  else {
      $createtime = $val['creationtime'];
      $updatetime = $val['updatetime'];;
      return true;
  }
}


function getConfig($name, $default = null) {
  $res = do_mysql_query("SELECT value FROM config WHERE name='".mysql_escape_string($name)."'");
  if(mysql_num_rows($res) == 0 || !($val = mysql_fetch_assoc($res))) {
    return $default;
  }
  else {
    return $val['value'];
  }
}


function setConfig($name, $val) {
  if($val == null || strlen($val) < 1) return false;
  
  $res = do_mysql_query("SELECT value FROM config WHERE name='".mysql_escape_string($name)."'");
  if(mysql_num_rows($res) == 0) {
    $sql = sprintf("INSERT INTO config (name, value, creationtime, updatetime) ".
				   " VALUES ('%s', '%s', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                   mysql_escape_string($name), mysql_escape_string($val));
    do_mysql_query($sql);
  }
  else {
    $sql = sprintf("UPDATE config SET value = '%s', updatetime = UNIX_TIMESTAMP() ".
				   " WHERE name LIKE '%s'",
                   mysql_escape_string($val), mysql_escape_string($name));
    do_mysql_query($sql);
  }
}

/**
 * Funktionen für das Platzierungsskript
 */

 /**
  * Den Radius des besiedelbaren Bereichs zurückgeben.
  *
  * Der Radius gibt den Sektorenabstand zur Mitte der Karte an. Dies beschreibt
  * einen "Ring" von Sektoren im Abstand r um die Mitte der Karte, in dem die
  * neuen Spieler platziert werden. Spieler aus weiter innen dürfen nicht in bzw.
  * über diesen Ring hinaus siedeln.
  *
  * Ein Sektor hat 40x40 Felder.
  *
  * Beispiel einer Karte mit 400x400 Felder = 10x10 Sektoren und settleRadius 3:
  *
  * # # # # # # # # # #
  * # # # # # # # # # #
  * # # X X X X X X # #  X = Sektoren genau auf dem Radius
  * # # X O O O O X # #  0 = Sektoren innerhalb, uneingeschränkt siedelbar
  * # # X O O O O X # #
  * # # X O O O O X # #  in diesem Fall wäre x > 280 oder y > 280
  * # # X O O O O X # #                 oder x < 120 oder y < 120
  * # # X X X X X X # #  ausserhalb des erlaubten Radius
  * # # # # # # # # # #
  * # # # # # # # # # #
  *
  */
function getSettleRadius() {
  define("MIN_SETTLE_RADIUS", 2);
  $radius = getConfig('settleradius');

  if($radius != null && intval($radius) > MIN_SETTLE_RADIUS) {
    return intval($radius);
  }
  else {
    return MIN_SETTLE_RADIUS;
  }
}



/**
 * Ist das Ziel innerhalb des Siedlungsbereiches für den Spieler?
 * 
 * @return null, falls JA
 *         Einen Fehlerstring andernfalls
 */
function isInSettleArea($x, $y)
{
  $r = getSettleRadius();
  if($r == 0) return null;

  getMapSize($fx, $fy);
  
  // Falls der Radius schon weiter als die Kartengröße ist:
  if($fx / 80 <= $r) return null;
  

  //$helplink = "Weitere Informationen findet Ihr <a href=\"library.php?s1=0&s2=1&s3=10\">hier in der Bibliothek.</a>";
  $helplink = "Weitere Informationen findet Ihr <a href=\"library.php?topic=Stadtgr\">hier in der Bibliothek.</a>";
  
  $correct = ($fx%80==0 ? 0 : 20);
  $r--;
  $max = $fx/2 + 40*$r + $correct;
  if($x > $max ) return "X-Koordinate (".$x."&gt;".$max.") liegt im für Neubesiedlung bzw. Angriffen gesperrten Bereich. $helplink";
  $max = $fy/2 + 40*$r + $correct;
  if($y > $max ) return "Y-Koordinate (".$y."&gt;".$max.") liegt im für Neubesiedlung bzw. Angriffen gesperrten Bereich. $helplink";
  $max = $fx/2 - 40*$r - $correct;
  if($x < $max ) return "X-Koordinate (".$x."&lt;".$max.") liegt im für Neubesiedlung bzw. Angriffen gesperrten Bereich. $helplink";
  $max = $fy/2 - 40*$r - $correct;
  if($y < $max ) return "Y-Koordinate (".$y."&lt;".$max.") liegt im für Neubesiedlung bzw. Angriffen gesperrten Bereich. $helplink";

  return null;
}


function getRoundStartTime()
{
  $starttime = do_mysql_query_fetch_assoc("SELECT value FROM config WHERE name='starttime'");
  return intval( ($starttime && $starttime['value']) ? $starttime['value'] : 0 );
}
?>