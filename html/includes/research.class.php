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
 * Copyright (c) 2003-2005
 *
 * Stefan Neubert, Stefan Hasenstab
 *
 * This File must not be used without permission!
 **************************************************/
class Research {	
  var $research;
  var $player;
  var $religion;
  var $globalstatus;

  // Konstruktor (Hauptstadt wird aktive Stadt)
  function Research($input_playerid, $input_playerreligion) {
    $this->player   = $input_playerid;
    $this->religion = $input_playerreligion;
    $res1 = do_mysql_query("SELECT research FROM playerresearch WHERE player=".$input_playerid) ;
    while ($db_res = mysqli_fetch_assoc($res1)) {
      $this->research[$db_res['research']]=true;
    }
  }
	
  function update() {
    $res1 = do_mysql_query("SELECT research FROM playerresearch WHERE player=".$this->player) ;
    while ($db_res = mysqli_fetch_assoc($res1)) {
      $this->research[$db_res['research']]=true;
    }
  }

  function isResearched($rs) {
    return $this->research[$rs];
  }
	
  function checkRequirements($rs) {
    $res1 = do_mysql_query("SELECT * FROM req_research WHERE research_id = ".intval($rs) );
    $rows = mysqli_num_rows($res1);
    $hits=0;
    while ($req=mysqli_fetch_assoc($res1)) {
      if ($this->isResearched($req['req_research'])) {
        $hits++;
      }
    }
    if ($rows==0 || $rows==$hits) $status=true;
    else $status=false;

    return $status;
  }
	
  function isResearching() {
    $res1 = do_mysql_query("SELECT rid, starttime, endtime FROM researching WHERE player = '".$this->player."'");
    if(mysqli_num_rows($res1)==1) {
      $res = mysqli_fetch_assoc($res1);
      return $res;
    }
    else return FALSE;
  }
	
  function getResearches() {
    $res2 = do_mysql_query("SELECT id, name, rp, time, religion, typ, typlevel, category, lib_link FROM research WHERE (religion = '".$this->religion."' OR religion is NULL) ORDER BY category, typ, typlevel");
    $i=0;

    while ($rs=mysqli_fetch_assoc($res2)) {
      if ($this->checkRequirements($rs['id']) == true) {
	$out[$i]['content']=$rs;
	
	if ($this->isResearched($rs['id'])!=FALSE) {
	  $i++;
	  $out[$i]['status']=1;
	}
	else {
	  $out[$i]['status']=0;
	  $i++;
	}
	
      }
      $researching=$this->isResearching();
      if ($researching!=FALSE) {
	$this->globalstatus=$researching;
      }
    }
		
    return $out;
  }
	
  function checkRID($rid) {
    $rid = intval($rid);
    if($rid == 0) return FALSE;

    $res1 = do_mysql_query("SELECT id FROM research WHERE id = ".$rid );
    if (mysqli_num_rows($res1)) {
      $res2 = do_mysql_query("SELECT research FROM playerresearch WHERE player = '".$this->player."' AND research = '".$rid."'");
      if (!mysqli_num_rows($res2)) {
        $res2 = do_mysql_query("SELECT rid FROM researching WHERE player = '".$this->player."' AND rid = '".$rid."'");
        if (!mysqli_num_rows($res2)) {
          return TRUE;
        }
        else return FALSE;
      }
      else return FALSE;
    }
    else return FALSE;
  }
	
  
  function startResearch($rid) {
    $rid = intval($rid);
    if ($this->checkRID($rid) == TRUE) {
      if(!$this->checkRequirements($rid)) {
        log_fatal_error("Spieler ".$_SESSION['player']->id." wollte schummeln: Forschungsvoraussetzungen nicht erfüllt.");
        return "Forschungsvoraussetzungen nicht erfüllt.";
      }
      
      $res1 = do_mysql_query("SELECT rp FROM player WHERE id = '".$this->player."'");
      $res2 = do_mysql_query("SELECT rp,time,management FROM research WHERE id = '".$rid."'");
      $data = mysqli_fetch_assoc($res1);
      $rdata = mysqli_fetch_assoc($res2);

      if( defined("RESEARCH_SCHOOL") && defined("RESEARCH_BIGSCHOOL") ) {
        $sql =
	  "SELECT count(*) AS cnt FROM playerresearch ".
	  " WHERE research IN (".RESEARCH_SCHOOL.",".RESEARCH_BIGSCHOOL.") ".
	  " AND player = ".$_SESSION['player']->id;

        $schools = do_mysql_query_fetch_assoc($sql);
      }
      else {
        $schools['cnt'] = 2;
      }


      $resA = do_mysql_query("SELECT rid FROM researching WHERE player = '".$this->player."'");
      if (mysqli_num_rows($resA) < $schools['cnt']+1) {
        if ($data['rp'] >= $rdata['rp']) {
          if($rdata['management'] == 6 && !is_premium_payd ) {
            return "Diese Forschung ist Spielern mit bezahltem Premium-Account vorbehalten.";
          }
          //do_log("Researching ordered. Started researching on rid ".$rid);
          do_mysql_query("UPDATE player SET rp = (rp - '".$rdata['rp']."'), cc_messages=1, cc_resources=1 WHERE id = '".$this->player."'");
          do_mysql_query("INSERT INTO researching (player,rid,starttime,endtime) VALUES ('".$this->player."', '".$rid."', UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + ".( max(MIN_RESEARCH_TIME, round($rdata['time'] / RESEARCHSPEED)) )." )");
          return null; // OK
        }
        else {
          return "Nicht genügend Forschungspunkte (RP)";
        }
      }
      else {
        return "Die maximale Anzahl gleichzeitiger Forschungen ist bereits erreicht.";
      }
    }
    else 
        return "Ungültige Anweisung";
  }
	
  function abortResearching() {
    $res1 = do_mysql_query("SELECT rid, player FROM researching WHERE player = '".$this->player."'");
    $res2 = mysqli_fetch_assoc($res1);
    
    if (mysqli_num_rows($res1)==1) {
      $rid = $res2['rid'];
      $res2 = do_mysql_query("SELECT rp, name FROM research WHERE id = '".$rid."'");
      $rdata = mysqli_fetch_assoc($res2);
      $nrp = floor($rdata['rp']/2);
      do_mysql_query("UPDATE player SET rp= (rp + '".$nrp."'), cc_messages=1,cc_resources=1 WHERE id = '".$this->player."'");
      do_mysql_query("DELETE FROM researching WHERE player = '".$this->player."'");
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$this->player.", UNIX_TIMESTAMP(),'Abbruch: ".$rdata['name']."','Dieser Forschauftrag wurde auf Euer Geheiß hin abgebrochen.\n\nAbzüglich der Unkosten erhaltet Ihr ".$nrp." Forschungspunkte zurück.',3)");
      do_log("Researching aborted. Requested on abortResearching() [".$rdata['name']."]");
    }
		
  }
}
?>