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
 
function insert_new_player(&$p) {
  //$pos  = $p['pos'];
  $login = $p['login'];
  $key = createKey();
  
  /*
  if ($p['pos']) {
    $religion = ceil( ($p['pos']) / 3);
  }
  else {
    if ($p['religion']) {
      $religion = $p['religion'];
      $pos = "NULL";
    }
    else {
      return "Weder 'religion' noch 'pos' wurden als Parameter übergeben!";
    }
  }
  */

  if ($p['sms'] == null) {
    $sms = "NULL";
  }
  else {
    $sms = "'".$sms."'";
  }

  if (!$p['bonuspoints']) {
    $bonuspoints = 0;
  }

  if ($p['md5pw']) {
    $md5pw = $p['md5pw'];
  }
  else {
    $md5pw = md5(trim($p['pw']));
  }
  
  $religion = "NULL";
  $mainres = do_mysql_query("INSERT INTO player (login,password,email,register_email,sms,lastseen,religion,status,statusdescription,activationkey,regtime,recruiter,pos,bonuspoints,cc_messages) VALUES ('".trim($p['login'])."','".$md5pw."','".trim($p['email'])."', '".trim($p['email'])."', ".$sms.", UNIX_TIMESTAMP(), ".$religion.", 1,'Noch nicht aktiviert','".$key."', UNIX_TIMESTAMP(), ".( !isset($p['ref']) || $p['ref'] == 0 ? "NULL" : $p['ref']).", NULL, $bonuspoints, 1)");

  if (!$mainres) {
    return "Fehler. Konnte Spieler nicht anlegen";
  }

  $playerid = mysql_insert_id($GLOBALS['con']);
  $p['pid'] = $playerid;
  
  if(isset($_SESSION) && isset($_SESSION['player']) && $_SESSION['player']->isAdmin()) {
    echo "PID: $playerid ";
  }
  
	  
  if(defined("URL_BASE"))
    $hw2_base_url = URL_BASE;
  else 
    $hw2_base_url = "http://game2.holy-wars2.de";     
  
  $nameencode = urlencode($login);
  
  mail($p['email'], 
       "Registrierung bei Holy-Wars 2", 
"Sehr geehrter Teilnehmer,
ihr Account wurde erfolgreich erstellt. Willkommen bei Holy-Wars 2!
	
Ihre Zugansdaten:
Login: $login
Passwort: sollte bekannt sein
Aktivierungscode: $key

Nach Ihrem ersten Login können Sie einen Spielernamen wählen!

"
  .( 
  (defined("NEW_ACTIVATION") && NEW_ACTIVATION)
  // *********************** Neue Aktivierung *******************************
  ? "Um Ihren Account vollständig zu aktivieren, klicken Sie einfach auf
\"Antworten\" und senden diese komplette Email an uns zurück. 
Wichtig ist, dass die Absender-Emailadresse mit der während der Registrierung
angegebenen Adresse übereinstimmmt und dass die folgende Zeile bei uns ankommt:  
Aktivierungscode: $key"
  // *********************** alte Aktivierung *******************************
  : "Der Account lässt sich über diesen Link aktivieren.
$hw2_base_url/activate.php?activationcode=$key&loginname=$nameencode&activate=1

Falls dieser Link nicht funktioniert, können Sie auch auf der Login-Seite
die Aktivierung aufrufen, und den Aktivierungscode eingeben."
  
)."

Vergessen Sie nicht, nach dem ersten Login Ihre Ausgangsstadt umzubenennen und
weitere Informationen unter dem Menüpunkt MyHW2 zu hinterlegen!

Bitte installieren Sie auch gleich den Grafik-Pack, um unseren Server zu
schonen und die Spielgeschwindigkeit zu erhöhen. Download unter:
".(defined("URL_BASE") ? URL_BASE : "http://www.holy-wars2.de" )."/grafikpaket/hw_grafik_v".(defined("GFX_VERSION") ? GFX_VERSION : "2.03").".zip

Diese eMail wurde automatisch generiert.
		
Viel Spaß wünscht Ihnen das Team von Holy-Wars 2", "FROM: mail@holy-wars2.de");
  

  if ($p['ref'] != null) {
    $res7=do_mysql_query("SELECT id FROM player WHERE id = ".$p['ref']);
    if(mysql_num_rows($res7)>0 && $p['ref'] != $playerid) {
      $bonus = RECRUIT_BONUSPOINTS;
      do_mysql_query("UPDATE player SET bonuspoints = bonuspoints+".$bonus  ." WHERE id = ".$p['ref']);
    }
  }


  $sql = sprintf("INSERT INTO message (recipient,date,header,body) ".
                 "VALUES (%d, UNIX_TIMESTAMP(),'%s','%s')",
                 $playerid,
                 "Erste Schritte (wichtig!!!)",
                 
                 "Herzlich Willkommen zu Holy-Wars 2.\n\n".
                 "Bevor Ihr sofort loslegt, bitte beachtet unbedingt folgende Punkte:\n".
                 "1. Baut NICHT gleich alle bzw. zu viele Gebäude. Das treibt Euch in den Ruin!\n".
                 "2. Lest Euch unbedingt das Tutorial durch (siehe Bibliothek).\n".
                 "3. Gründet 2 neue Städte, nicht mehr (wird im Tutorial erklärt).\n".
		         "   Städte gründen könnt Ihr im Rathaus (Menüpunkt).\n".
		         "4. Verdient Euch etwas Gold, indem Ihr an Ritterturnieren teilnehmt\n\n".
                 "zu 1.: ein Sägewerk und zwei Holzfäller reichen anfangs vollkommen aus\n\n".
                 "Und nun viel Spaß beim Spiel!"                 
                 );


  do_mysql_query($sql);
  
  return null;
}
?>