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
* Copyright (c) 2003-2004
*
* written by
* Stefan Neubert
* Stefan Hasenstab
* Markus Sinner kroddn@psitronic.de
*
* This File must not be used without permission	!
***************************************************/
       
// Array zum matchen von Settings zu Binär-Masken
$GLOBALS['arr_settings']
     = array(0 => 'map_own',
             1 => 'forum_own',
             2 => 'library_own',
             3 => 'hide_banner',
             4 => 'disable_login_counter'
            );

class Player {	
  var $msgsignature;
  var $regtime;
  var $ip;
  var $id;
  var $name;
  var $login;
  var $description;
  var $email;
  var $sms;
  var $religion;
  var $recruiter;
  var $gold;
  var $wood;
  var $iron;
  var $stone;
  var $rp;
  var $bonuspoints;
  var $lastclickbonuspoints;
  var $points;
  var $avgpoints;
  var $clan;
  var $clanstatus;
  var $clanapplication;
  var $mapsize;
  var $newmessages;
  var $activepage;
  var $activemsgcategory;
  var $gfx_path;
  var $hwstatus;
  var $lastclick;
  var $sid;
  var $scouttime;
  var $messages_sent;
  var $mapversion;
  var $tutorialLvl;
  
  // Steuert den Neulingsschutz
  // Level 5: Angriffsschutz
  var $nooblevel;
  
  // Konstruktor
  function Player($id, $sid) {
    $res1=do_mysql_query("SELECT name, login, settings, signature, description, email, sms, religion, gold, wood, iron, stone, rp, bonuspoints, lastclickbonuspoints, round(pointsavg/pointsupd) as avgpoints, points, clan, clanstatus, clanapplication, mapsize, mapversion, gfx_path, hwstatus, regtime, nooblevel, recruiter, tutorial FROM player WHERE id=".intval($id) );        
    $res2=do_mysql_query("SELECT count(*) FROM message WHERE recipient=".intval($id)." AND !(status & ".(MSG_RECIPIENT_READ|MSG_RECIPIENT_DELETED).")");
    $res3=do_mysql_query("SELECT lastclick FROM player_online WHERE uid=".intval($id) );
    $data3=mysql_fetch_assoc($res3);
    
    $db_player = mysql_fetch_assoc($res1);

    if($db_player['religion'] == null || $db_player['name'] == null) {
      die("<html><body><b>religion oder name ist NULL. Dies sollte nicht passieren...</b><br>Deine ID ist <b>".$id."</b></body></html>");
    }

    $num2 = mysql_fetch_array($res2);

    $this->newmessages = $num2[0];
    $this->msgsignature=$db_player['signature'];
    $this->regtime=$db_player['regtime'];
    $this->ip=getenv('REMOTE_ADDR');
    $this->id=$id;
    $this->name=$db_player['name'];
    $this->login=$db_player['login'];
    $this->description=$db_player['description'];
    $this->email=$db_player['email'];
    $this->sms=$db_player['sms'];
    $this->religion=$db_player['religion'];
    $this->gold=$db_player['gold'];
    $this->wood=$db_player['wood'];
    $this->iron=$db_player['iron'];
    $this->stone=$db_player['stone'];
    $this->rp=$db_player['rp'];
    $this->bonuspoints=$db_player['bonuspoints'];
    $this->lastclickbonuspoints=$db_player['lastclickbonuspoints'];
    $this->points=$db_player['points'];
    $this->avgpoints=$db_player['avgpoints'];
    $this->clan=$db_player['clan'];
    $this->clanstatus=$db_player['clanstatus'];
    $this->clanapplication=$db_player['clanapplication'];
    $this->mapsize=$db_player['mapsize'];
    $this->activemsgcategory=-1;
    $this->saveIp();
    $this->gfx_path=$db_player['gfx_path'];
    $this->normalizeGFX_PATH();
    
    $this->hwstatus=$db_player['hwstatus'];
    $this->lastclick=$data3['lastclick'];
    $this->sid=$sid;
    $this->nooblevel=$db_player['nooblevel'];
    $this->recruiter=$db_player['recruiter'];
    
    $this->mapversion = $db_player['mapversion'];
    $this->tutorialLvl = $db_player['tutorial'];
    
    // Postausgang
    // Zähle die Nachrichten im Postausgang
    if (get_message_archive_size () > 0) {
      $this->messages_sent = -1;
    }
    else {
      $this->messages_sent = 0;
    }
    
    // Calculate Scouttime
    $this->scoutime=$this->getScoutTime();

    // Einstellungen laden
    $this->loadSettings( intval($db_player['settings']) );    
  }

  
  function tutorialInc($to) {
    do_mysql_query("UPDATE player SET tutorial = ".intval($to)." WHERE id = ".$this->id);
    $this->tutorialLvl = $to;
  }

  function tutorialLevel() {
    return $this->tutorialLvl;
  }

  // gibt die ID zurück
	function getID() {
		return $this->id;
	}
	function getReligion() {
		return $this->religion;
	}
	function getEMail() {
		return $this->email;
	}
	function getSMS() {
          if ($this->sms == null || strlen($this->sms) < 10)
            return null;
          else
            return $this->sms;
	}
    function changeSMS($sms) {
      if ($sms == null) {
        do_mysql_query("UPDATE player SET sms = NULL WHERE id=".$this->id);
      }
      else {
        do_mysql_query("UPDATE player SET sms = '".mysql_escape_string($sms)."' WHERE id=".$this->id );
      }          
      $this->sms = $sms;
    }

    function showSMSSenderNumber() {
      $sms_res = do_mysql_query("SELECT * FROM sms_settings WHERE player = ".$this->id );
      if (mysql_num_rows($sms_res) == 1) {
        // Include für Konstanten
        include_once("includes/sms.func.php");
        $sms_settings = mysql_fetch_assoc($sms_res);
        
        // Es müssen einige Bedingungen erfüllt sein
        // 1. Der Spieler muß Premium Pro haben
        // 2. Er muß ne gültige Nummer hinterlegt haben
        // 3. Er muß eingeschaltet haben, dass seine Nummer auch übertragen wird.
        if ((is_premium_set_sms_sender() || $this->isAdmin()) &&            
            $sms_settings['sms_nr_show'] > 0
            )
          {
            return true;
          }

      }
      return false;
    }
      
    function isValidSMSSenderNumber() {
      if($this->isAdmin()) return true;

      $sms_res = do_mysql_query("SELECT * FROM sms_settings WHERE player = ".$this->id );
      if (mysql_num_rows($sms_res) == 1) {
        $sms_settings = mysql_fetch_assoc($sms_res);
        
        if (( is_premium_set_sms_sender() ) &&            
            $sms_settings['sms_nr_verified'] > 0)
            return true;            
      }
      return false;
    }


    /* Gibt die SMS-Absenderkennung zurück*/
	function getSMSSenderNumber() {
      $sms_res = do_mysql_query("SELECT * FROM sms_settings WHERE player = ".$this->id );
      if (mysql_num_rows($sms_res) == 1) {
        // Include für Konstanten
        include_once("includes/sms.func.php");
        $sms_settings = mysql_fetch_assoc($sms_res);
        
        // Es müssen einige Bedingungen erfüllt sein
        // 1. Der Spieler muß Premium Pro haben
        // 2. Er muß ne gültige Nummer hinterlegt haben
        // 3. Er muß eingeschaltet haben, dass seine Nummer auch übertragen wird.
        if ((is_premium_set_sms_sender() || $this->isAdmin()) &&
            $sms_settings['sms_nr'] &&             
            valid_sms_nr($sms_settings['sms_nr']) )
          {
            return $sms_settings['sms_nr'];
          }

      }

      return null;
	}
    
    function changeSMSSenderNumber($sms, $show = null) {
      if ($sms == null) {
        include_once("includes/sms.func.php");
        do_mysql_query("UPDATE sms_settings SET sms_nr = NULL, sms_nr_verified = NULL, sms_nr_show = ".
                       ($show == null || !$show ? "0" : 1).
                       " WHERE player=".$this->id);
      }
      else {
        do_mysql_query("UPDATE sms_settings SET sms_nr = '".mysql_escape_string($sms)."', sms_nr_verified = NULL, sms_nr_show = ".
                       ($show == null || !$show ? "0" : 1).
                       " WHERE player=".$this->id);
      }          
    }


	function getSMSRemaining() {
		return $_SESSION['sms_contingent'];
	}
	// gibt den Namen zurück
	function getName() {
		return $this->name;
	}

	function getLogin() {
		return $this->login;
	}
	// gibt den Namen zurück
	function getRecruiter() {
          if ($this->recruiter)
            return $this->recruiter;
          else
            return null;
	}
  // gibt den Namen zurück
  function getRegTime() {
    return $this->regtime;
  }
	// gibt den Punktestand zurück
	function getPoints() {
		$sql=mysql_query("SELECT points FROM player WHERE id = ".$this->id);
		$data=mysql_fetch_assoc($sql);
		$this->points=$data['points'];
		return $this->points;
	}
	// gibt den Punktestand zurück
	function getAvgPoints() {
		$sql=mysql_query("SELECT round(pointsavg/pointsupd) as points FROM player WHERE id = ".$this->getID());
		$data=mysql_fetch_assoc($sql);
		$this->avgpoints=$data['points'];
		return $this->avgpoints;
	}
	// gibt das Vermögen an Gold zurück
	function getGold() {
		return $this->gold;
	}
	// gibt das Vermögen an Holz zurück
	function getWood() {
		return $this->wood;
	}
	// gibt das Vermögen an Eisen zurück
	function getIron() {
		return $this->iron;
	}
	// gibt das Vermögen an Stein zurück
	function getStone() {
		return $this->stone;
	}
	// gibt die Anzahl der Forschungspunkte zurück
	function getRp() {
		return $this->rp;
	}
	// gibt die Anzahl der Bonuspunkte zurück
	function getBonuspoints() {
		return $this->bonuspoints;
	}
	function setBonuspoints($val) {
	  $val = intval($val);
	  $this->bonuspoints = $val;
	  do_mysql_query("UPDATE player SET bonuspoints = ".intval($val)." WHERE id = ".$this->id);
	}
	
  function addClickBonuspoints() {
    if (!defined("CLICK_BONUSPOINTS")) return 0;
	
    do_mysql_query("UPDATE player SET ".
		   " bonuspoints = bonuspoints + ".CLICK_BONUSPOINTS.",".
		   " lastclickbonuspoints = UNIX_TIMESTAMP() ".
		   " WHERE UNIX_TIMESTAMP() > lastclickbonuspoints + ".CLICK_BONUSPOINTS_TIME.
		   " AND id = ".$this->id);
    if(mysql_affected_rows() > 0) {
      $this->bonuspoints += CLICK_BONUSPOINTS;
      $this->lastclickbonuspoints = time();
      return 1;
    }    

    return 0;
  }

  function getLastClickBonusPoints() {
    return $this->lastclickbonuspoints;
  }

	// IP management
	function getIp() {
		return $this->ip;
	}
	// Kartengröße
	function getMapSize() {
		return $this->mapsize;
	}
	function getMsgSignature($raw=false) {
	  if ( is_premium_signature () && 
           isset($this->msgsignature) && 
           strlen($this->msgsignature) > 0 ) {
	    return ($raw ? "" : "\n\n\n").$this->msgsignature;	    
	  }
	  else {
        return ($raw ? "" : "\n\n\n")."Gezeichnet\n".$this->name."\n-----\nPersönliche Signaturen sind für Besitzer eines Premium-Accounts möglich. Weitere Infos unter dem Menüpunkt Einstellungen!";
	  }	  
	}

	// Anzahl neuer Nachrichten
	function getNewMessages() {
		return $this->newmessages;
	}

        // Erhöhen der gesendeten Nachrichten
	function incSentMessages() {
          if ($this->messages_sent != -1) {
            $this->messages_sent++;
          }
        }

	// Anzahl neuer Nachrichten
	function getSentMessages() {
          if ($this->messages_sent == -1) {
            $msgsent = do_mysql_query_fetch_array("SELECT count(*) AS c FROM message WHERE !(status & ".MSG_SENDER_DELETED.") AND sender = '".mysql_escape_string($this->name)."' AND date>=".intval($this->regtime) );
            $this->messages_sent = $msgsent['c'];
          }
          return $this->messages_sent;
	}

	// Finazminister
	function isMinisterFinance() {
	  return $this->clanstatus & 1;
	}

	// Innenminister
	function isMinisterInterior() {
	  return $this->clanstatus & 2;
	}

	// Aussenminister
	function isMinisterForeign () {
	  return $this->clanstatus & 4;
	}

	// Minister
	function isMinister() {
	  return $this->clanstatus > 0;
	}
	
	
	function isTeamMember() {
	  return $this->hwstatus > 0;
	}
	
	
	//ist der Spieler Admin?
	function isAdmin() {
	  if ($this->hwstatus & 1)
	    return 1;
	  else 
	    return 0;
	}

	//ist der Spieler Multihunter?
	function isMultihunter() {
	  if ($this->hwstatus & 2)
	    return 1;
	  else 
	    return 0;
	}

	//ist der Spieler Marktmod?
	function isMarketmod() {
	  if ($this->hwstatus & 4)
	    return 1;
	  else 
	    return 0;
	}

	//ist der Spieler Namenshunter?
	function isNamehunter() {
	  if ($this->hwstatus & 8)
	    return 1;
	  else 
	    return 0;
	}

        // eine Stufe unter Admin...
	function isMaintainer() {
	  if ($this->hwstatus & 16)
	    return 1;
	  else 
	    return 0;
	}
	
	//hat Zugriff auf Vergabe der PremiumAccs
	function isPremSeller() {
	  if ($this->hwstatus & 32)
	    return 1;
	  else 
	    return 0;
	}


	function canTrade() {
	  $res = do_mysql_query ("SELECT research FROM playerresearch WHERE player=".$this->getID()." AND research=".MARKETRESEARCH );
	  if (mysql_num_rows($res) > 0)
	    return null;
	  else
	    return "Ihr habt die Forschung Märkte noch nicht abgeschlossen!";
	}

	function getScoutTime() {
      return getPlayerScoutTime($this->getID());
	}
	
	
	// Anzahl neuer Nachrichten aktualisieren
	function updateNewMessages() {
          $res1=do_mysql_query("SELECT count(*) FROM message WHERE recipient=".$this->id." AND !(status & ".(MSG_RECIPIENT_READ|MSG_RECIPIENT_DELETED).")" );
          $num = mysql_fetch_array($res1);	
	  $this->newmessages = $num[0];
	}

	// Ressourcen aktualisieren
	function updateResources() {
		$res1=do_mysql_query("SELECT gold, wood, stone, iron, rp, nooblevel, bonuspoints FROM player WHERE id=".$this->id);
		$data=mysql_fetch_assoc($res1);
		$this->gold = $data['gold'];
		$this->wood = $data['wood'];
		$this->iron = $data['iron'];
		$this->stone = $data['stone'];
		$this->rp = $data['rp'];
		$this->nooblevel = $data['nooblevel'];
		$this->bonuspoints = $data['bonuspoints'];
	}
	
	// ändern der Nutzerdaten
	function changeSettings($newmapsize) {
		$sql = sprintf("UPDATE player SET mapsize=%u WHERE id=%u", $newmapsize, $this->id);
		do_mysql_query($sql);
		do_log(sprintf("Mapsize changed: mapsize %u", $newmapsize));
		$this->mapsize=$newmapsize;
	}




    // Aktuelle Session-Einstellungen speichern
    function saveSettings() {
      global $arr_settings;

      $newsettings = 0;
      
      // Die Maske für die Settings aufbauen.
      foreach ($arr_settings as $i => $name) {
        $mask = pow(2, $i);
        if($_SESSION['settings'][$name]) {
          $newsettings |= $mask;
        }
      }
      
      do_mysql_query("UPDATE player SET settings = ".$newsettings." WHERE id = ".$this->id);
      return null;
    }

    // Einstellungen wiederherstellen
    function loadSettings($tmpsettings = null) {
      global $arr_settings;
      
      // Zuerst den Settings-Array mit Assoc-Namen aufbauen
      $_SESSION['settings'] = array();      
      
      if ($tmpsettings == null) {
        $tmpsettings = do_mysql_query_fetch_assoc("SELECT settings FROM player WHERE id = ".$this->id);      
        $tmpsettings = intval($settings['settings']);
      }      

      // Die Bits der settings durchlaufen. $mask beginnt bei 2^0 bis 2^63
      foreach ($arr_settings as $i => $name) {
        $mask = pow(2, $i);
        $_SESSION['settings'][$name] = ($tmpsettings & $mask) > 0;
      }
    }

 
	function changeEMail($value) {
		if( strlen($value) < 4 || $value == $this->getEMail()) {
          return NULL;
		}
        
        $sql=sprintf("UPDATE player SET email='%s' WHERE id = %u", $value, $this->id);
        do_mysql_query($sql);
        $this->email=$value;
	}

  function changePassword($oldpw, $newpw1, $newpw2) {
    $res=mysql_query("SELECT id FROM player WHERE password = md5('".mysql_escape_string($oldpw)."') AND id = ".$this->getID());
    if(mysql_num_rows($res)==1) {
      if($newpw1==$newpw2) {
        mysql_query("UPDATE player SET password = md5('".mysql_escape_string($newpw1)."') WHERE id = ".$this->getID());
        mysql_query("UPDATE clanf_users SET user_password = md5('".mysql_escape_string($newpw1)."') WHERE user_id = ".$this->getID());
        // do_log(sprintf("Password changed: %s", md5($newpw1)) );
        return null;
      }
      else return "Das beiden neuen Passwörter stimmen nicht überein.<br>";
    }
    else return "Das Alte Passwort stimmt nicht mit dem gespeicherten überein.<br>";
  }
    
	function saveIp() {
		$sql = sprintf("UPDATE player SET ip='%s' WHERE id=%u", $this->ip, $this->id);
		do_mysql_query($sql);
	}
	// aktuelle Seite zurückgeben
	function getActivePage() {
	  if (!isset($this->activepage) || $this->activepage==null) {
	    return "main.php";
	  }
	  else {	    
	    return $this->activepage;
	  }
	}	
	// aktuelle Seite setzen
	function setActivePage($page) {
		$this->activepage=$page;
	}
	function setActiveMsgcategory($cat) {
		$this->activemsgcategory=$cat;
	}
	function getActiveMsgcategory() {
		return $this->activemsgcategory;
	}
	
	// Sollte bereits durch normalizeGFX_PATH() richtig gesetzt sein.
	function getGFX_PATH() {
	  return $this->gfx_path;
	}
	
	function updateGFX_PATH($path) {
	  $this->gfx_path=$path;
	  $this->normalizeGFX_PATH();
	  if($this->gfx_path == null) {
	    $escaped_path = "NULL";
	  }
	  else {
	    $escaped_path = "'".mysql_escape_string($path)."'";
	  }
	   
	  // $path ist hier bereits escaped
	  do_mysql_query("UPDATE player SET gfx_path = ".$escaped_path." WHERE id = ".$this->id);
	}
	
	function normalizeGFX_PATH() {
	  $this->gfx_path = $this->normalizeGfxPath( $this->gfx_path );
	}
	
	function normalizeGfxPath($path) {
	  $local = defined("GFX_PATH_LOCAL") ? GFX_PATH_LOCAL : "images/ingame";
	  if($path == null) { 
	    return null;
	  }
	  else {
	    $path = trim( $path );
	    if(strlen($path) == 0 || 
	       strncasecmp($path, $local, strlen($local)) == 0) {
	      $path = null;
	    }
	    else if(!stristr($path, "images/ingame")) {
	      // Der String images/ingame HAT darin vorzukommen!
	      $path .= "/".($local);
	    }
	  }
	  return $path;
	} // function normalizeGFX_PATH()
	
	function getlastclick() {
		return $this->lastclick;
	}
	function getSID() {
		return $this->sid;
	}
	function updatelastclick() {
		$this->lastclick=time();
		$res=mysql_query("SELECT uid FROM player_online WHERE uid = '".$this->getID()."'");
		if(mysql_num_rows($res)==1)
		{
			mysql_query("UPDATE player_online SET lastclick = UNIX_TIMESTAMP() WHERE uid = ".$this->getID());
		}
		else
		{
			mysql_query("INSERT INTO player_online VALUES (".$this->getID().", UNIX_TIMESTAMP(), '".$this->getSID()."')");
		}
	}
	function updateDescription($desc) {
	  $desc=save_html($desc);
	  do_mysql_query("UPDATE player set description = '".mysql_escape_string($desc)."' WHERE id = ".$this->getID());
	  $this->description=$desc;
	}
	function updateMsgSignature($sig) {
          $maxlen = 500;
          $maxlines = 5;
	  $sig=save_html($sig);
          if (strlen($sig) > $maxlen) {
            return "Die Signatur enthält zu viele Zeichen. Maximal ".$maxlen." Zeichen sind erlaubt!";
          }
          if (substr_count($sig, "\n") >= $maxlines) {
            return "Die Signatur ist zu lang. Maximal ".$maxlines." Zeilen sind erlaubt!";
          }

	  do_mysql_query("UPDATE player set signature = '".mysql_escape_string($sig)."' WHERE id = ".$this->getID());
	  $this->msgsignature=$sig;
          return null;
	}
	function getDescription() {
		return $this->description;
	}


	// Funktion gibt die Einträge aus dem Adressbuch zurück
    //
    // $adr[x][0] enthält player.id
    // $adr[x][1] enthält player.name
    // $adr[x][2] enthält nicename
    // $adr[x][3] enthält sms    
	function getAdressbookPlayers () {
      $adr = array();
      $sql = 
        "SELECT a.player AS id, p.name AS name, coalesce(a.nicename,p.name) AS nicename, a.sms AS sms ".
        " FROM addressbook a LEFT JOIN player p ON p.id = a.player ".
        " WHERE a.owner = ".$this->getID()." AND a.player IS NOT NULL".
        " ORDER BY nicename";
      $adr_res = do_mysql_query($sql);
      $num = $adr_res ? mysql_num_rows($adr_res) : 0;
      for($i = 0; $i < $num; $i++) {
        $adr[$i] = mysql_fetch_assoc($adr_res);
      }

      return $num==0 ? null : $adr;
    }


	function getSMSAdressbook () {
      $adr = array();
      $sql = 
        "SELECT coalesce(a.nicename, p.name, a.sms) as nicename, coalesce(p.name,a.sms) AS sms ".
        " FROM addressbook a LEFT JOIN player p ON p.id = a.player".
        " WHERE owner = ".$this->getID().
        " AND (a.sms IS NOT NULL ".
        "   OR p.name IS NOT NULL AND p.sms IS NOT NULL ".
        " )".
        " ORDER BY nicename";
      $adr_res = do_mysql_query($sql);
      $num = $adr_res ? mysql_num_rows($adr_res) : 0;
      for($i = 0; $i < $num; $i++) {
        $adr[$i] = mysql_fetch_assoc($adr_res);
      }

      return $num==0 ? null : $adr;
    }


	// Funktion gibt einen Array der Spieler,ID zurück,
	// die mit ihm selbst verbündet sind
	function getAlliedPlayers () {
	  $allied_players = null;
	  $my_id = $this->getID();
	  $i = 0;

	  $sql = 
            "SELECT id1 as id,name,'0' as clan FROM relation LEFT JOIN player ON player.id=id1 WHERE id2=".$my_id." AND type=2".
            " UNION ".
            "SELECT id2 as id,name,'0' as clan FROM relation LEFT JOIN player ON player.id=id2 WHERE id1=".$my_id." AND type=2".
            ($this->clan ? " UNION SELECT id,name,'1' AS clan FROM player WHERE clan = ".$this->clan :"").
            " ORDER BY name";
	  $allies = do_mysql_query ($sql);
	  while ($ally = mysql_fetch_assoc($allies)) {
	    $allied_players[$i][0] = $ally['id'];
	    $allied_players[$i][1] = $ally['name'];
	    $allied_players[$i][2] = $ally['clan'];
	    $i++;
	  }
	  
	  return $allied_players;
	}


    function holidayMode($duration) {
      $duration = intval($duration);
      if($duration < 10) {
        return "Ihr müßt mindestens 10 Tage Urlaub wählen.";
      }
      if($duration > 35) {
        return "Mehr als 35 Tage werden nicht zugelassen.";
      }
      
      $error = "";

      // War der Spieler schon im Urlaub?
      $MIN_BACK = 14*24*3600; // 14 Tage 
      $last = do_mysql_query_fetch_array("SELECT unix_timestamp()-holiday AS t, ".
                                         " from_unixtime(holiday+".$MIN_BACK.") AS ts ".
                                         " FROM player WHERE id = ".$this->id);
      if ($last['t'] < $MIN_BACK) {
        return "Ihr wart in letzter Zeit bereits im Urlaubsmodus.<br>Ihr könnt erst wieder am ".$last['ts'].
          " in den Urlaubsmodus wechseln.";
      }
      
      
      // Alle Bedingungen abchecken
      // Hat der Spieler noch Truppen unterwegs?
      if (mysql_num_rows(do_mysql_query("SELECT * FROM army ".
                                        "WHERE mission != 'return' AND owner = ".$this->id)
                         ) > 0) {
        $error .= "<li>Ihr habt noch Armeen in Bewegung. Zieht diese zurück.";
      }
      // Hat der Spieler noch Truppen unterwegs?
      if (mysql_num_rows(do_mysql_query("SELECT * FROM cityunit LEFT JOIN city ON city.id = cityunit.city ".
                                        "WHERE city.owner != ".$this->id." AND cityunit.owner =".$this->id)
                         ) > 0) {
        $error .= "<li>Ihr habt noch Truppen bei Verbündeten. Zieht diese zurück (Siehe Verwaltung-&gt;Truppenübersicht)";
      }
      

      
      if ( $error != "" ) {
        return "Folgende Bedingungen sind noch nicht erfüllt:<ul>".$error."</ul>";
      }

      // Sekunden und Stunden Multiplizieren
      $duration *= 24*3600;
      do_mysql_query("UPDATE player SET holiday=UNIX_TIMESTAMP()+$duration WHERE id = ".$this->id);      
      
      $sql = sprintf("INSERT INTO log_holiday (player,time,duration,ip) VALUES (%d, UNIX_TIMESTAMP(), %d, '%s')",
                       $this->id, $duration, $_SERVER['REMOTE_ADDR']);
      do_mysql_query($sql);

      session_destroy();
      $_SESSION['session_duration'] = 0;
      return null;
    }

	function getMapVersion() {
	  return $this->mapversion;
	}

    function getNoobLevel() {
      return $this->nooblevel;
    }
    
    function setNoobLevel($level) {
      $this->nooblevel = $level;
      do_mysql_query("UPDATE player SET nooblevel = $level WHERE id = ".$this->id);
    }
    
    function getNumberOfAttacks() {      
      $scouttime = $this->getScoutTime();
      if($scouttime > 0) {
        $num = getAttacksAgainstPlayer($this->getID());
      }
      return $num;
    }
    
  function getClanFunction() {
    return getPlayerClanFunctions($this->clanstatus);
  }
}
?>
