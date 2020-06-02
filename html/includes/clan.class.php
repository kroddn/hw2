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
Clanstatus:
0 Normales Mitglied
1 Finanzminister
2 Innenminister
4 Aussenminister
63 Ordensgründer
*/

/*
Clanbeziehungen:
0 Feindlich
1 Neutral
2 NAP
3 Bündnis
*/

include_once("includes/util.inc.php");
include_once("includes/diplomacy.common.php");

class Clan {
  var $id;
  var $status;
  var $player;
  var $name;
  var $player_name;
  var $description;

  // Constructor
  function Clan($player) {
    $this->id = 0;
    $this->status = 0;
    $this->player = intval($player);

    $res1 = do_mysql_query("SELECT name,clan,clanstatus FROM player WHERE id=".$this->player);
    if ($data1 = do_mysql_fetch_assoc($res1)) {
      $this->status = $data1['clanstatus'];	 
      $this->player_name = $data1['name'];

      $this->update();
    } // if ($data1 = do_mysql_fetch_assoc($res1))
  }
  

  function checkClanName($name) {
    $str=strtolower($name);
    $str=$str." -01234567890_abcdefghijklmnopqrstuvwxyz";
    $str=count_chars($str,3);
    $res=($str==" -0123456789_abcdefghijklmnopqrstuvwxyz");
    if (strlen(trim($name))<4) $res=false;
    if (strlen(trim($name))>40) $res=false;
    return $res;
  }

  function getID() {
    return $this->id;
  }

  function getStatus() {
    return $this->status;
  }

  function update() {
  	$res = do_mysql_query("SELECT clan,clanstatus FROM player WHERE id=".$this->player);
  	$data = do_mysql_fetch_assoc($res);
  	if ($data['clan']> 0) {
  		$this->id=$data['clan'];
  		$this->status=$data['clanstatus'];

  		$res2 = do_mysql_query("SELECT name,description FROM clan WHERE id=".$data['clan']);
  		if ($data2 = do_mysql_fetch_assoc($res2)) {
  			$this->description = $data2['description'];
  			$this->name = $data2['name'];
  		}
  	}
  	else {
  		$this->id     = 0;
  		$this->status = 0;
  		$this->name   = null;
  		$this->description = null;
  	}
  }

  /**
   * Nachricht an Minister, z.B. bei Bewerbungen
   *  
   * @param $clanid
   * @param $mask
   * @param $header
   * @param $body
   * @return unknown_type
   */
  function minister_msg($clanid, $mask, $header, $body) {
    $res1= do_mysql_query("SELECT id FROM player WHERE clan=".$clanid."  AND clanstatus & ".$mask);
    while($data1 = do_mysql_fetch_assoc($res1)) {
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['id'].",".time().",'".$header."','".$body."',1)");
      do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$data1['id']);
    }
  }
  

  /**
   * Darf dieser Spieler einen Clan Anführen/Minister sein?
   * 
   * @param $id
   * @return null bei okay, einen string als Fehlermeldung andernfalls
   */
  function canLeadClan($id) {
    $RESEARCH_LEAD_CLAN = CLANLEADRESEARCH;
    $res = do_mysql_query ("SELECT research FROM playerresearch WHERE player=".$id." AND research=".$RESEARCH_LEAD_CLAN );
    if (mysqli_num_rows($res) > 0)
      return null; // alles okay
    else
      return "Sie haben nicht die benötigten Kenntnisse (Forschung: <a href=\"library.php?s1=3&s2=0&s3=0&research_id=117\">Ordensführung</a>) um einen Orden erfolgreich führen zu können!";
  }

  
  /**
   * Neuen Orden gründen.
   * 
   * @returns null on success, else an errormessage
   */
  function newClan ($name) {
    $can = $this->canLeadClan($this->player);
    if ($can != null)
      return $can;

    if ((!$this->checkClanName($name)) || ($this->id > 0))
      return "Ordensname nicht in Ordnung oder bereits zu einem Orden gehörend";

    // Check for double clan
    $res1 = do_mysql_query("SELECT id FROM clan WHERE name='".mysqli_escape_string($GLOBALS['con'], $name)."'");
    if (mysqli_num_rows($res1) > 0) return "Orden existiert bereits" ;

    // Clan anlegen
    do_mysql_query("INSERT INTO clan (name, gold) VALUES ('".mysqli_escape_string($GLOBALS['con'], $name)."',0)");
    
    // FIXME: hier vielleicht mysql_last_insert_id benutzen?
    $res1 = do_mysql_query("SELECT id FROM clan WHERE name='".mysqli_escape_string($GLOBALS['con'], $name)."'");
    
  	if(mysqli_num_rows($res1)) {
	    // Den aktuellen Spieler zur Ordensführung des neuen Clans erheben
	    if ($data1 = do_mysql_fetch_assoc($res1))
	      do_mysql_query("UPDATE player SET clan=".$data1['id'].",clanapplication=NULL,clanstatus=63,clanapplicationtext=NULL WHERE id=".$this->player);
	    $this->id = $data1['id'];
	    $this->status = 63;
	    $_SESSION['player']->clan = $data1['id'];
	    $_SESSION['player']->clanstatus = 63;
	
	    return null;
  	}
  	else {
  		return "Fehler beim Anlegen eines Ordens. Bitte Admin informieren.";
  	}
  }

  /**
   * Bei einem Orden bewerben
   * 
   * @param $id
   * @param $applText
   * @return unknown_type
   */
  function appClan($id, $applText = null) {
  	if ($this->id > 0) return "Ihr befindet Euch bereits in einem Orden"; // Spieler ha bereits nen clan
  	
    $id = intval($id);
    if ($applText == null || strlen($applText) == 0) {
      $text = "(Der Spieler hat keinen Bewerbungstext eingegeben.)";
    }
    else {
      $text = "Der Spieler übermittelte Euch folgende Nachricht:\n[quote]".htmlentities($applText, ENT_QUOTES)."[/quote]";
    }
    
    $res1 = do_mysql_query("SELECT id FROM clan WHERE id=".$id);
    if (do_mysql_fetch_assoc($res1)) {
    	$res2 = do_mysql_query("SELECT religion FROM player WHERE clan=".$id." AND clanstatus=63");
    	$res3 = do_mysql_query("SELECT religion,name FROM player WHERE id=".$this->player);
    	if (($data2 = do_mysql_fetch_assoc($res2)) && ($data3 = do_mysql_fetch_assoc($res3))) {
    		if ($data2['religion'] == $data3['religion']) {
    			do_mysql_query("UPDATE player SET clanapplication=".$id.",clanapplicationtext='".mysqli_escape_string($GLOBALS['con'], $text)."' WHERE id=".$this->player);
    			 
    			$this->minister_msg($id, 2, "Bewerbung von ".mysqli_escape_string($GLOBALS['con'], $data3['name']), "Der Spieler ".mysqli_escape_string($GLOBALS['con'], $data3['name'])." hat sich um Mitgliedschaft in eurem Orden beworben.\n\n".mysqli_escape_string($GLOBALS['con'], $text) );
    		}
    	}
    }
  }

  /**
   * Bewerbung abbrechen.
   * 
   * @param $player
   * @return unknown_type
   */
  function dropApp($player) {
    $player = intval($player);
    $this->update();
    if ($player == $this->player) {
      do_mysql_query("UPDATE player SET clanapplication=NULL,clanapplicationtext=NULL WHERE id=".$player);
    } 
    else {
    	$res1 = do_mysql_query("SELECT clanapplication FROM player WHERE id=".$player);
    	if ($data1 = do_mysql_fetch_assoc($res1)) {
    		if (($data1['clanapplication'] == $this->id) && ($this->status & 2)) {
    			do_mysql_query("UPDATE player SET clanapplication=NULL,clanapplicationtext=NULL WHERE id=".$player);
    			do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$player.", UNIX_TIMESTAMP(),'Bewerbung abgelehnt','Eure Bewerbung wurde vom Orden ".mysqli_escape_string($GLOBALS['con'], $this->name)." abgelehnt', 1)");
    			do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$player);
    		}
    	}
    }
    $this->update();
  }

  
  /**
   * Orden verlassen 
   * 
   * @param $player
   * @return unknown_type
   */
  function leaveClan($player) {
    $this->update();
    $player=intval($player);
    $clan = $this->id;
    $res = do_mysql_query("SELECT id FROM player WHERE clanstatus=63 AND clan=".$clan);
    //der letzte Ordensleiter
    if (($this->status == 63) && (mysqli_num_rows($res) == 1) && ($player == $this->player)) {
    	$res = do_mysql_query("SELECT gold FROM clan WHERE id=".$clan);
    	if($data = do_mysql_fetch_assoc($res))
    	  do_mysql_query("UPDATE player SET clan=NULL,gold=gold+".$data['gold'].",cc_resources=1 WHERE id=".$this->player);
    	removeClan($clan);
    }
    
    // Spieler tritt selber aus
    if (($player == $this->player)) {
    	do_mysql_query("UPDATE player SET clan=NULL WHERE id=".$player);
        $_SESSION['player']->clan = 0;


    	//Nachricht an OL und Innenminister wegen Austritt eines Members
    	$get_leave_name = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
    	$leave_name =  do_mysql_fetch_assoc($get_leave_name);
    	$msgheader = "Mitglied aus Orden ausgetreten";
    	$msgbody = "Der Spieler <b>".$leave_name['name']."</b> hat soeben Euren Orden verlassen!";
    	$res1= do_mysql_query("SELECT id FROM player WHERE clan=".$this->id." AND clanstatus & 2");
    	while($data1 = do_mysql_fetch_assoc($res1)) {
    		do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['id'].", UNIX_TIMESTAMP(),'".mysqli_escape_string($GLOBALS['con'], $msgheader)."','".mysqli_escape_string($GLOBALS['con'], $msgbody)."',1)");
    		do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$data1['id']);
    	}
    	$this->update();
    }
    // Innenminister wirft member raus
    else if (($this->status & 2)) {
    	$res = do_mysql_query("SELECT clan,clanstatus FROM player WHERE id=".$player);
    	if ($data = do_mysql_fetch_assoc($res)) {
    		if (($data['clan'] == $this->id) && ($data['clanstatus'] <63)) {
    			do_mysql_query("UPDATE player SET clan=NULL WHERE id=".$player);
    		}
    		 
    		//Nachricht an OL und Innenminister wegen Austritt eines Members
    		$get_kicker_name = do_mysql_query("SELECT name FROM player WHERE id=".$this->player);
    		$kicker_name =  do_mysql_fetch_assoc($get_kicker_name);
    		$get_kicked_name = do_mysql_query("SELECT name FROM player WHERE id=".$player);
    		$kicked_name =  do_mysql_fetch_assoc($get_kicked_name);
    		$msgheader = "Mitglied aus Orden entlassen";
    		$msgbody = "Der Innenminister <b>".$kicker_name['name']."</b> hat soeben den Spieler  <b>".$kicked_name['name']."</b> aus dem Orden entlassen!";
    		$res1= do_mysql_query("SELECT id FROM player WHERE clan=".$this->id." AND clanstatus & 2");
    		while($data1 = do_mysql_fetch_assoc($res1)) {
    			do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['id'].", UNIX_TIMESTAMP(),'".mysqli_escape_string($GLOBALS['con'], $msgheader)."','".mysqli_escape_string($GLOBALS['con'], $msgbody)."',1)");
    			do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$data1['id']);
    		}
    	}
    }
    else {
      return "Ihr seid dazu nicht befugt.";
    }
  }

  function accMember ($player) {
    $this->update();
    $player = intval($player);
    $res = do_mysql_query("SELECT clanapplication FROM player WHERE id=".$player);
    if ($data = do_mysql_fetch_assoc($res)) {
    	if (($data['clanapplication'] == $this->id) && ($this->status & 2))
    		do_mysql_query("UPDATE player SET clan=".$this->id.",clanapplication=NULL,clanapplicationtext=NULL,clanstatus=0 WHERE id=".$player);
    }
  }

  
  function payIn ($gold) {
    $this->update();
    $gold = intval($gold);
    if ($gold > 0) {
    	$res = do_mysql_query("SELECT gold FROM player WHERE id=".$this->player);
    	if (($data = do_mysql_fetch_assoc($res)) && ($data['gold'] >= $gold)) {
    		do_mysql_query("UPDATE player SET gold=gold-".$gold.",cc_resources=1 WHERE id=".$this->player);
    		do_mysql_query("UPDATE clan SET gold=gold+".$gold." WHERE id=".$this->id);
    		//clanlog
    		$clanlogdata1 = do_mysql_query("SELECT * from clanlog where playerid=".$this->player." and clan=".$this->id);
    		if ($get_clanlogdata1 = do_mysql_fetch_assoc($clanlogdata1)) {
    			do_mysql_query("UPDATE clanlog set amount=amount+".$gold.", time_amount=".time()." where playerid=".$this->player." and clan=".$this->id);
    		}
    		else {
    			do_mysql_query("INSERT INTO `clanlog` (`playerid`,`clan`, `amount`, `time_amount`) VALUES ('".$this->player."','".$this->id."','".$gold."', UNIX_TIMESTAMP() )");
    		}
    		// clanlog_admin, für jede Transaktion einen Eintrag
    		do_mysql_query("INSERT INTO `clanlog_admin` (`playerid`,`clan`, `amount`, `time`) VALUES ('".$this->player."','".$this->id."','".$gold."', UNIX_TIMESTAMP() )");
    	}
    }
  }

  function payOut ($player, $gold) {
    $this->update();
    $gold = intval($gold);
    $player = intval($player);
    if($player == 0) return "Kein gültiger Spieler ausgewählt.";
    if($gold < 1) return "Gebt einen gültigen Betrag zur Auszahlung an.";
    
    // Finanzminister?
    if( $this->status & 1 ) {
    	$res1 = do_mysql_query("SELECT clan FROM player WHERE id=".$player);
    	$res2 = do_mysql_query("SELECT gold FROM clan WHERE id=".$this->id);
    	if (($data1 = do_mysql_fetch_assoc($res1)) &&
    		($data2 = do_mysql_fetch_assoc($res2)) &&
    		($data1['clan'] == $this->id) &&
    		($data2['gold'] >= $gold)
    	){
    		do_mysql_query("UPDATE clan SET gold=gold-".$gold." WHERE id=".$this->id);
    		do_mysql_query("UPDATE player SET gold=gold+".$gold.", cc_resources=1, cc_messages=1 WHERE id=".$player);
    		//Nachricht an Spieler
    		$msgheader = "Auszahlung aus Ordenskasse";
    		$msgbody = "Euch wurde soeben der Betrag von <b>".prettyNumber($gold)."</b> Goldstücken durch Euren Finanzminister <b>".$this->player_name."</b> ausbezahlt!";
    		do_mysql_query("INSERT INTO message (sender,recipient,date,category,header,body) VALUES ('SERVER',".$player.",".time().",1,'".$msgheader."','".$msgbody."')");

    		//Nachricht an alle Finanzminister und OL wegen Auszahlung
    		$get_rec_name = do_mysql_query("SELECT name FROM player WHERE id=".$player);
    		$rec_name =  do_mysql_fetch_assoc($get_rec_name);
    		$msgheader = "Auszahlung aus Ordenskasse";
    		$msgbody = "Dem Ordensmitglied <b>".$rec_name['name']."</b> wurden soeben ".prettyNumber($gold)." Goldstücke durch den Finanzminister <b>".$this->player_name."</b> ausbezahlt!";
    		$res1= do_mysql_query("SELECT id FROM player WHERE clan=".$this->id."  AND clanstatus & 1");
    		while($data1 = do_mysql_fetch_assoc($res1)) {
    			do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data1['id'].",".time().",'".$msgheader."','".$msgbody."',1)");
    			do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$data1['id']);
    		}
    		//clanlog
    		$clanlogdata1 = do_mysql_query("SELECT * from clanlog where playerid=".$player." and clan=".$this->id);
    		if ($get_clanlogdata1 = do_mysql_fetch_assoc($clanlogdata1)) {
    			do_mysql_query("UPDATE clanlog set amount=amount-".$gold.", time_amount=".time()." where playerid=".$player." and clan=".$this->id);
    		}
    		else {
    			do_mysql_query("INSERT INTO `clanlog` (`playerid`,`clan`, `amount`, `time_amount`) VALUES (".$player.",".$this->id.",-".$gold.", UNIX_TIMESTAMP() )");
    		}
    		//clanlog_admin
    		do_mysql_query("INSERT INTO `clanlog_admin` (`playerid`,`clan`, `amount`, `time`) VALUES (".$player.",".$this->id.",-".$gold.", UNIX_TIMESTAMP() )");
    	}
    }
    else {
    	return "Diese Funktion ist Finanzministern vorbehalten!";
    }
  }

  /**
   * Spieler befürdern 
   * @param $player
   * @param $mask
   * @return unknown_type
   */
  function promote($player, $mask) {
    $this->update();
    $player = intval($player);
    $mask = intval($mask);

    $res = do_mysql_query("SELECT name,clan,clanstatus FROM player WHERE id=".$player);
    if (($data = do_mysql_fetch_assoc($res)) && ($data['clan'] == $this->id)) {
    	// returns null if player is able to "lead"
    	$can = $this->canLeadClan($player);

    	// Player can lead
    	if ($can == null) {
    		$mask = $mask & $this->status;
    		$mask = $mask | $data['clanstatus'];
    		do_mysql_query("UPDATE player SET clanstatus=".$mask." WHERE id=".$player);
    		return null;
    	}
    	else {
    		return "Der Spieler <b>".$data['name']."</b> besitzt noch nicht die notwendigen Kenntnisse, um eine Führungsposition in Eurem Orden zu begleiten! Informiert jenen, auf dass er sobald seine Fähigkeiten und Kenntnisse aufbessert.";
    	}
    }
  }

  
  function demote($player, $mask) {
    $this->update();
    $player = intval($player);
    $mask = intval($mask);
    $mask = $mask & 7;
    $res = do_mysql_query("SELECT clan,clanstatus FROM player WHERE id=".$player);
    if (($data = do_mysql_fetch_assoc($res)) && ($data['clan'] == $this->id) && ($data['clanstatus'] < 8)) {
      $mask = $mask & $this->status;
      $mask = $mask ^ $data['clanstatus'];
      $mask = $mask & $data['clanstatus'];

      do_mysql_query("UPDATE player SET clanstatus=".$mask." WHERE id=".$player);
    }
  }

  function setTax($tax) {
    $this->update();
    $tax = intval($tax);
    $tax = ($tax>100)?100:$tax;
    $tax = ($tax<0)?0:$tax;
    $tax /= 100;
    if ($this->status & 1)
      do_mysql_query("UPDATE clan SET tax=".$tax." WHERE id=".$this->id);
  }

  function updateDescription($desc) {
    $this->update();
    if (($this->status == 63) && ($this->id)) {
      $desc=save_html($desc);
      do_mysql_query("UPDATE clan SET description = '".mysqli_escape_string($GLOBALS['con'], $desc)."' WHERE id = ".$this->id);
      $this->description=$desc;
    }
  }

  function getDescription() {
    return $this->description;
  }


  //diplomacy part
  function diploMessage($id, $header, $text) {
    $res = do_mysql_query("SELECT id FROM player WHERE clan=".intval($id)." AND clanstatus & 4");
    while($data = do_mysql_fetch_assoc($res)) {
      do_mysql_query("INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',".$data['id'].", UNIX_TIMESTAMP(),'".mysqli_escape_string($GLOBALS['con'], $header)."','".mysqli_escape_string($GLOBALS['con'], $text)."',1)");
      do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$data['id']);
    }
  }

  function neutral($clan) {
  	$clan = intval($clan);
  	$res1 = do_mysql_query("SELECT id,name FROM clan WHERE id=".$clan);
  	if (($this->id != $clan) && ($this->status & 4) && ($data1 = do_mysql_fetch_assoc($res1))) {
  		$res2 = do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")") or die(mysqli_error($GLOBALS['con']));
  		if ($data2 = do_mysql_fetch_assoc($res2)) {
  			if ($data2['type'] == 0) {
  				$res3 = do_mysql_query("SELECT type FROM req_clanrel WHERE id1=".$this->id." AND id2=".$clan) ;
  				if (mysqli_num_rows($res3)) {
  					do_mysql_query("UPDATE req_clanrel SET type=1 WHERE id1=".$this->id." AND id2=".$clan);
  				}
  				else {
  					do_mysql_query("INSERT INTO req_clanrel (id1,id2,type) VALUES (".$this->id.",".$clan.",1)") ;
  				}
  				$this->diploMessage($clan, "Friedensangebot von ".$this->name, "Der Orden ".$this->name." hat euch ein Friedensangebot gemacht.");
  				$this->diploMessage($this->id, "Friedensangebot an ".$data1['name'], "Ihr habt dem Orden ".$data1['name']." ein Friedensangebot gemacht.");
  			} else {
  				do_mysql_query("DELETE FROM clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")");
  				if ($data2['type'] == 3) {
  					$this->diploMessage($clan, $this->name." kündigt Bündnis", "Der Orden ".$this->name." hat euer Bündnis gekündigt.");
  					$this->diploMessage($this->id, "Bündnis mit ".$data1['name']." gekündigt", "Ihr habt das Bündnis mit ".$data1['name']." gekündigt");
  				} 
  				else if ($data2['type'] == 2) {
  					$this->diploMessage($clan, $this->name." kündigt NAP", "Der Orden ".$this->name." hat euer Nichtangriffspakt gekündigt.");
  					$this->diploMessage($this->id, "NAP mit ".$data1['name']." gekündigt", "Ihr habt den Nichtangriffspakt mit ".$data1['name']." gekündigt");
  				}
  			}
  		}
  	}
  }


  function canNotWar($id1, $id2) {
    $msg = NULL;

    $id1 = intval($id1);
    $id2 = intval($id2);
    
    $res1 = do_mysql_query("SELECT p1.name AS p1name,p2.name AS p2name,c1.name AS c1name,c2.name AS c2name FROM player AS p1, clan AS c1, player AS p2,clan AS c2,relation WHERE p1.clan=c1.id AND p2.clan=c2.id AND relation.type=2 AND ((relation.id1=p1.id AND relation.id2 = p2.id) OR (relation.id1=p2.id AND relation.id2=p1.id)) AND c1.id=".$id1." AND c2.id=".$id2);
    if (mysqli_num_rows($res1)) {
    	$msg = "Kriege:\n\n";
    	while ($data1 = do_mysql_fetch_assoc($res1)) {
    		$msg .= "Bündnis zwischen ".$data1['p1name']." (".$data1['c1name'].") und ".$data1['p2name']." (".$data1['c2name'].")\n";
    	}
    }

    $res2 = do_mysql_query("SELECT unit.name AS uname,cityunit.count,city.name AS cname, p1.name AS p1name, p2.name AS p2name,c1.name AS c1name, c2.name AS c2name FROM unit,cityunit,city,player AS p1,clan AS c1,player AS p2,clan AS c2 WHERE unit.id=cityunit.unit AND cityunit.city=city.id AND p1.id=cityunit.owner AND p2.id=city.owner AND ((p1.clan=".$id1." AND p2.clan=".$id2.") OR (p1.clan=".$id2." AND p2.clan=".$id1.")) AND p1.clan=c1.id AND p2.clan=c2.id");
    if (mysqli_num_rows($res2)) {
    	$msg .= "\nStadtbesatzung:\n\n";
    	while ($data2 = do_mysql_fetch_assoc($res2)) {
    		$msg .= $data2['count']." ".$data2['uname']." von ".$data2['p1name']." (".$data2['c1name'].") in ".$data['cname']." von ".$data2['p2name']."(".$data2['c2name'].")\n";
    	}
    	return $msg;
    } else
    return NULL;
  }

  function hostile($clan) {
  	$clan = intval($clan);
  	$res1 = do_mysql_query("SELECT id,name FROM clan WHERE id=".$clan);
  	if (($this->id != $clan) && ($this->status & 4) && ($data1 = do_mysql_fetch_assoc($res1))) {
  		if ($msg = $this->canNotWar($this->id, $clan)) {
  			$this->diploMessage($this->id, "Kriegserklärung an ".$data1['name']." fehlgeschlagen", $this->player_name." hat versucht ".$data1['name']." den Krieg zuer erklären folgende Hindernisse stellen sich dem noch in den Weg:\n\n".$msg);
  		}
  		else{
  			setDBrel("clanrel", $this->id, $clan, 0);
  			delDBreq_rel("req_clanrel", $this->id, $clan);
  			$this->diploMessage($clan, "Kriegserklärung von ".$this->name, "Der Orden ".$this->name." hat euch den Krieg erklärt. Möge der Bessere gewinnen.");
  			$this->diploMessage($this->id, "Kriegserklärung an ".$data1['name'], "Ihr habt dem Orden ".$data1['name']." den Krieg erklärt. Möge der Bessere gewinnen.");
  		}
  	}
  }

  function canNotNAP_BND($id1, $id2) {
  	$msg = NULL;
  	$id1 = intval($id1);
    $id2 = intval($id2);
    
  	$res1 = do_mysql_query("SELECT p1.name AS p1name,p2.name AS p2name,c1.name AS c1name,c2.name AS c2name FROM player AS p1, clan AS c1, player AS p2,clan AS c2,relation WHERE p1.clan=c1.id AND p2.clan=c2.id AND relation.type=0 AND ((relation.id1=p1.id AND relation.id2 = p2.id) OR (relation.id1=p2.id AND relation.id2=p1.id)) AND c1.id=".$id1." AND c2.id=".$id2);
  	if (mysqli_num_rows($res1)) {
  		$msg = "Kriege:\n\n";
  		while ($data1 = do_mysql_fetch_assoc($res1)) {
  			$msg .= "Krieg zwischen ".$data1['p1name']." (".$data1['c1name'].") und ".$data1['p2name']." (".$data1['c2name'].")\n";
  		}
  	}

  	$res2 = do_mysql_query("SELECT city.name AS cname,p1.name AS pname, p2.name AS defname, clan.name AS clanname FROM city,army,clan,player AS p1, player AS p2 WHERE city.id=army.end AND p1.id=city.owner AND p2.id=army.aid AND ((p1.clan=".$id1." AND p2.clan=".$id2.") OR (p1.clan=".$id2." AND p2.clan=".$id1.")) AND army.mission IN ('attack', 'despoil', 'burndown') AND clan.id=p2.clan");
  	if (mysqli_num_rows($res2)) {
  		$msg .= "\nAngriffe:\n\n";
  		while ($data2 = do_mysql_fetch_assoc($res2)) {
  			$msg .= "Angriff von ".$data2['pname']." (".$data2['clanname'].") auf ".$data2['cname']." von ".$data2['defname']."\n";
  		}
  	}
  	return $msg;
  }

  function offerNAP($clan) {
  	$clan = intval($clan);
  		
  	$res1 = do_mysql_query("SELECT name FROM clan WHERE id=".$clan);
  	if ($data1 = do_mysql_fetch_assoc($res1)) {
  		if ($msg = $this->canNotNAP_BND($clan,$this->id)) {
  			$this->diploMessage($this->id, "NAP Angebot an ".$data1['name']." fehlgeschlagen", $this->player_name." wollte dem Orden ".$data1['name']." NAP Angebot unterbreiten, Hindernisse stehen dem noch entgegen:\n\n".$msg);
  		}
  		else {
  			setDBreq_rel("req_clanrel", $this->id, $clan, 2);
  			$this->diploMessage($clan, "Angebot für NAP von ".$this->name, "Der Orden ".$this->name." hat euch ein Angebot für einen Nichtangriffspakt gemacht.");
  			$this->diploMessage($this->id, "Angebot für NAP an ".$data1['name'], $this->player_name." hat dem Orden ".$data1['name']." ein Angebot für einen Nichtangriffspakt gemacht.");
  		}
  	}
  }

  function nap($clan) {
    $clan = intval($clan);
    
    $res1 = do_mysql_query("SELECT id,name FROM clan WHERE id=".$clan) or die(mysqli_error($GLOBALS['con']));
    if (($this->id != $clan) && ($this->status & 4) && ($data1 = do_mysql_fetch_assoc($res1))) {
    	$res2 = do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")") or die(mysqli_error($GLOBALS['con']));
    	if ($data2 = do_mysql_fetch_assoc($res2)) {
    		if ($data2['type']>2) {
    			do_mysql_query("UPDATE clanrel SET type=2 WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")") or die(mysqli_error($GLOBALS['con']));
    			$this->diploMessage($clan, $this->name."hat Bündnis in NAP umgewandelt", "Der Orden ".$this->name." euer Bündnis gekündigt, will aber trotzem weiterhin in Frieden mit euch leben.");
    			$this->diploMessage($this->id, "Bündnis mit ".$data1['name']." in NAP umgewandelt", $this->player_name." hat das Bündnis mit dem Orden ".$data1['name']." gekündigt, wollt aber trotzem weiterhin in Frieden mit ihnen leben.");
    		} 
    		else {
    			$res3 = do_mysql_query("SELECT type FROM req_clanrel WHERE id1=".$this->id." AND id2=".$clan);
    			if ($data2['type'] == 0) {
    				if (mysqli_num_rows($res3)) {
    					do_mysql_query("UPDATE req_clanrel SET type=1 WHERE id1=".$this->id." AND id2=".$clan) ;
    				}
    				else {
    					do_mysql_query("INSERT INTO req_clanrel (id1,id2,type) VALUES (".$this->id.",".$clan.",1)");
    				}
    				$this->diploMessage($clan, "Friedensangebot von ".$this->name, "Der Orden ".$this->name." hat euch ein Friedensangebot gemacht.");
    				$this->diploMessage($this->id, "Friedensangebot an ".$data1['name'], "Ihr habt dem Orden ".$data1['name']." ein Friedensangebot gemacht.");
    			} 
    			else {
    				$this->offerNAP($clan);
    			}
    		}
    	} else
    	$this->offerNAP($clan);
    }
  }

  function offerBND($clan) {
  	$clan = intval($clan);
  	
  	$res1 = do_mysql_query("SELECT name FROM clan WHERE id=".$clan);
  	$res2 = do_mysql_query("SELECT religion FROM player WHERE clan=".$this->id." AND clanstatus=63");
  	$res3 = do_mysql_query("SELECT religion FROM player WHERE clan=".$clan." AND clanstatus=63");
  	if (($data1 = do_mysql_fetch_assoc($res1)) && ($data2 = do_mysql_fetch_assoc($res2)) && ($data3 = do_mysql_fetch_assoc($res3))) {
  		if ($data2['religion'] == $data3['religion']) {
  			if ($msg = $this->canNotNAP_BND($clan,$this->id)) {
  				$this->diploMessage($this->id, "Bündnisangebot an ".$data1['name']." fehlgeschlagen", $this->player_name." wollte dem Orden ".$data1['name']." ein Bündnisangebot unterbreiten, Hindernisse stehen dem noch entgegen:\n\n".$msg);
  			}
  			else {
  				setDBreq_rel("req_clanrel", $this->id, $clan, 3);
  				$this->diploMessage($clan, "Bündnisangebot von ".$this->name, "Der Orden ".$this->name." hat euch ein Bündnisangebot gemacht.");
  				$this->diploMessage($this->id, "Bündnisangebot an ".$data1['name'], $this->player_name." hat dem Orden ".$data1['name']." ein Bündnisangebot gemacht.");
  			}
  		}
  		else{
  			$this->diploMessage($this->id, "Bündnisangebot an Ungläubigen", $this->player_name." wollte dem Ungläubigen Orden ".$data1['name']." ein Bündnis anbieten, mögen ihm die Gläubigen jedes Haar einzeln ausrupfen!");
  		}
  	}
  }

  function friendly ($clan) {
    $clan = intval($clan);

    $res1 = do_mysql_query("SELECT id,name FROM clan WHERE id=".$clan) or die(mysqli_error($GLOBALS['con']));
    if (($this->id != $clan) && ($this->status & 4) && ($data1 = do_mysql_fetch_assoc($res1))) {
    	$res2 = do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")") or die(mysqli_error($GLOBALS['con']));
    	if ($data2 = do_mysql_fetch_assoc($res2)) {
    		if ($data2['type'] <3) {
    			$res3 = do_mysql_query("SELECT type FROM req_clanrel WHERE id1=".$this->id." AND id2=".$clan) or die(mysqli_error($GLOBALS['con']));
    			if ($data2['type'] == 0) {
    				if (mysqli_num_rows($res3))
    				do_mysql_query("UPDATE req_clanrel SET type=1 WHERE id1=".$this->id." AND id2=".$clan) or die(mysqli_error($GLOBALS['con']));
    				else
    				do_mysql_query("INSERT INTO req_clanrel (id1,id2,type) VALUES (".$this->id.",".$clan.",1)") or die(mysqli_error($GLOBALS['con']));
    				$this->diploMessage($clan, "Friedensangebot von ".$this->name, "Der Orden ".$this->name." hat euch ein Friedensangebot gemacht.");
    				$this->diploMessage($this->id, "Friedensangebot an ".$data1['name'], "Ihr habt dem Orden ".$data1['name']." ein Friedensangebot gemacht.");
    			} else {
    				$this->offerBND($clan);
    			}
    		}
    	}
    	else {
    		$this->offerBND($clan);
    	}
    }
  }

  function accReqRelation($clan) {
  	$clan = intval($clan);

  	$res1 = do_mysql_query("SELECT id,name FROM clan WHERE id=".$clan);
  	if (($this->id != $clan) && ($data1 = do_mysql_fetch_assoc($res1)) && ($this->status & 4)) {
  		$res2 = do_mysql_query("SELECT type FROM req_clanrel WHERE (id1=".$clan.") AND (id2=".$this->id.")") or die(mysqli_error($GLOBALS['con']));
  		$res3 = do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")");
  		if ($data2 = do_mysql_fetch_assoc($res2)) {
  			if ($data3 = do_mysql_fetch_assoc($res3)) {
  				if ($data2['type'] > $data3['type']) {
  					$res4 = do_mysql_query("SELECT type FROM clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")");
  					if ($data4 = do_mysql_fetch_assoc($res4)) {
  						do_mysql_query("UPDATE clanrel SET type=".$data2['type']." WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")");
  					}
  					else {
  						do_mysql_query("INSERT INTO clanrel (id1,id2,type) VALUES (".$this->id.",".$clan.",".$data2['type'].")");
  					}
  					do_mysql_query("DELETE FROM req_clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")");
  				}
  			} 
  			else if ($data2['type'] > 1) {
  				do_mysql_query("INSERT INTO clanrel (id1,id2,type) VALUES (".$this->id.",".$clan.",".$data2['type'].")");
  				do_mysql_query("DELETE FROM req_clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")");
  			}
  			
  			if ($data2['type'] == 1) {
  				$this->diploMessage($clan, "Frieden mit ".$this->name, "Der Orden ".$this->name." hat euer Friedensangebot angenommen, mögen eure Völker fortan in Frieden und Eintracht leben.");
  				$this->diploMessage($this->id, "Frieden mit ".$data1['name'], "Ihr habt das Friedensangebot von ".$data1['name']." angenommen, mögen eure Völker fortan in Frieden und Eintracht leben.");
  			} 
  			else if ($data2['type'] == 1) {
  				$this->diploMessage($clan, "NAP von ".$this->name." angenommen", "Der Orden ".$this->name." hat euer Angebot für einen Nichtangriffspakt angenommen.");
  				$this->diploMessage($this->id, "NAPangebot von ".$data1['name']." angenommen", "Ihr habt das Angebot für einen Nichtangriffspakt ".$data1['name']." angenommen.");
  			}
  			else if ($data2['type'] == 3) {
  				$this->diploMessage($clan, "Bündnis von ".$this->name." angenommen", "Der Orden ".$this->name." hat euer Bündnisangebot angenommen.");
  				$this->diploMessage($this->id, "Bündnisangebot von ".$data1['name']." angenommen", "Ihr habt das Bündnisangebot von ".$data1['name']." angenommen.");
  			}
  		}
  	}
  }

  function delReqRelation($clan, $type) {
  	$clan = intval($clan);
  	
  	$res1 = do_mysql_query("SELECT id,name FROM clan WHERE id=".$clan);
  	if (($this->status & 4) && ($data1=do_mysql_fetch_assoc($res1))) {
  		do_mysql_query("DELETE FROM req_clanrel WHERE (id1=".$clan." AND id2=".$this->id.") OR (id1=".$this->id." AND id2=".$clan.")") or die(mysqli_error($GLOBALS['con']));
  		if ($type== 1) {
  			$this->diploMessage($clan, "Friedensangebot von ".$this->name." abgelehnt", "Der Orden ".$this->name." hat euer Angebot um Frieden ausgeschlagen.");
  			$this->diploMessage($this->id, "Friedensangebot von ".$data1['name']." abgelehnt", "Ihr habt das Friedensangebot des Ordens ".$data1['name']." ausgeschlagen.");
  		} 
  		else if ($type== -1) {
  			$this->diploMessage($clan, "Friedensangebot von ".$this->name." zurückgezogen", "Der Orden ".$this->name." hat sein Friedensangebot zurückgezogen.");
  			$this->diploMessage($this->id, "Friedensangebot an ".$data1['name']." zurückgezogen", "Ihr habt das Friedensangebot an den Orden ".$data1['name']." zurückgezogen.");
  		} 
  		else if ($type== 2) {
  			$this->diploMessage($clan, "NAP Angebot von ".$this->name." abgelehnt", "Der Orden hat ".$this->name." hat euer Angebot um einen Nichtangriffspakt ausgeschlagen");
  			$this->diploMessage($this->id, "NAP Angebot von ".$data1['name']." abgelehnt", "Ihr habt das Angebot für einen Nichtangriffspakt des Ordens ".$data1['name']." ausgeschlagen.");
  		} 
  		else if ($type == -2) {
  			$this->diploMessage($clan, "NAP Angebot von ".$this->name." zurückgezogen", "Der Orden hat ".$this->name." hat euer Angebot um einen Nichtangriffspakt zurückgezogen");
  			$this->diploMessage($this->id, "NAP Angebot von ".$data1['name']." zurückgezogen", "Ihr habt das Angebot für einen Nichtangriffspakt des Ordens ".$data1['name']." zurückgezogen.");
  		} 
  		else if ($type == 3) {
  			$this->diploMessage($clan, "Bündnisangebot von ".$this->name." abgelehnt", "Der Orden ".$this->name." hat euer Angebot um ein Bündnis ausgeschlagen.");
  			$this->diploMessage($this->id, "Bündnisangebot von ".$data1['name']." abgelehnt", "Ihr habt das Bündnisangebot des Ordens ".$data1['name']." ausgeschlagen.");
  		} 
  		else if ($type== -3) {
  			$this->diploMessage($clan, "Bündnisangebot von ".$this->name." zurückgezogen", "Der Orden ".$this->name." hat sein Bündnisangebot zurückgezogen.");
  			$this->diploMessage($this->id, "Bündnisangebot an ".$data1['name']." zurückgezogen", "Ihr habt das Bündnisangebot an den Orden ".$data1['name']." zurückgezogen.");
  		}
  	}
  }

  function changeRelation($clanname, $type) {
  	if (!$this->checkClanName($clanname)) {
  		echo "<b class='error'>Ungültiger Ordensname</b>";
  		return "Ungültiger Ordensname";
  	}
  	$res = do_mysql_query("SELECT id FROM clan WHERE name='".mysqli_escape_string($GLOBALS['con'], $clanname)."'");
  	if (($data = do_mysql_fetch_assoc($res)) && ($this->status & 4)) {
  		$type = intval($type);
  		if ($type == 0)
  		  $this->hostile($data['id']);
  		else if($type == 3)
  		  $this->friendly($data['id']);
  		else if($type == 2)
  		  $this->nap($data['id']);
  		else if($type == 1)
  		  $this->neutral($data['id']);
  	}
  }
}