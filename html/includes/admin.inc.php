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


function reset_clanforum_now() {
  do_mysqli_query("TRUNCATE clanf_forums");
  do_mysqli_query("TRUNCATE clanf_auth_access");
  do_mysqli_query("TRUNCATE clanf_posts");
  do_mysqli_query("TRUNCATE clanf_posts_text");
  do_mysqli_query("TRUNCATE clanf_topics");
  do_mysqli_query("TRUNCATE clanf_user_group");
  do_mysqli_query("TRUNCATE clanf_users");
  do_mysqli_query("TRUNCATE clanf_sessions");
  do_mysqli_query("TRUNCATE clanf_categories");
  do_mysqli_query("TRUNCATE clanf_search_wordlist");
  do_mysqli_query("TRUNCATE clanf_search_wordmatch");
  do_mysqli_query("TRUNCATE clanf_vote_desc");
  do_mysqli_query("TRUNCATE clanf_vote_results");
  do_mysqli_query("TRUNCATE clanf_vote_voters");

  do_mysqli_query("INSERT INTO `clanf_users` VALUES (-1, 1, 'Cheater', '', 0, 0, 0, 0, 0, 6302, 2.00, NULL, NULL, 'd.M.Y H:i', 0, 0, 0, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, 'Das hier solltest du nie sehen...\r\n\r\nACHTUNG!\r\nPostet dieser User liegt ein Bug vor!\r\nBitte melde diesen Beitrag einen Admin.\r\n(Zuständig: morlock)\r\nDanke!', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)");
}


function list_player_not_activated () {
  
  echo "<h1>Noch nicht aktivierte Spieleraccounts</h1>\n";
  echo 'Die Spieler sind nach der Reihenfolge Ihrer Anmeldung sortiert. <a href="#last">Ganz unten</a> sollte man die zuletzt angemeldeten finden.<p>';
  echo 'Grün markierte Spieler sind bereits seit mind. 2 Tagen erstellt und nicht aktiviert worden.<br>';

  $sql = "SELECT id, login, email, activationkey, status,
 unix_timestamp() - regtime AS inactive_seconds,
 from_unixtime(regtime)     AS regtime_time,
 from_unixtime(lastseen)    AS lastseen_time,
 regtime                    AS regtime_unixtime,
 lastseen                   AS lastseen_unixtime
FROM player WHERE activationkey IS NOT NULL AND status = 1
ORDER BY id";
  $players = do_mysqli_query($sql);
  $num = mysqli_num_rows($players);

  $i = 0;
  echo "<pre>$sql</pre>\n";
  echo "$num Spieler nicht aktiviert.<br>\n";
  echo "<p><table>\n";
  echo " <tr class=\"tblhead\"><td>[ID] Login</td><td>Act.Key</td><td>eMail</td><td>Regtime</td><td>Lastseen</td><td>Inaktiv<br>in Stunden</td><td>Aktion</td></tr>\n";

  while ( $p = mysqli_fetch_array($players) ) {
    $i++;
    echo 
      ' <tr class="'.($p['regtime_unixtime'] != $p['lastseen_unixtime'] 
                      ? 'tbldanger' 
                      : ($p['inactive_seconds'] > 60*60*24*2 ? 'tblokay' : 'tblbody') ).'">'.
      "<td>".($num-$i == 10 ? '<a name="last"></a>' : "" ).
      "[".$p['id'].'] <a href="multihunter.php?showid='.$p['id'].'">'.$p['login']."</a> (".$p['status'].")</td>\n".
      '<td>'.$p['activationkey']."</td>\n".
      '<td><a href="mailto:'.$p['email'].'">'.$p['email']."</a></td>\n".
      '<td align="right" title="regtime_unixtime: '.$p['regtime_unixtime'].'">'.$p['regtime_time']. "</td>\n".
      '<td align="right" title="lastseen_unixtime: '.$p['lastseen_unixtime'].'">'.$p['lastseen_time']."</td>\n".
      '<td align="right" title="Sekunden: '.$p['inactive_seconds'].'">'.formatTime($p['inactive_seconds'])." h</td>\n".
      // Länger als xxx Tage nicht aktiviert...
      "<td>".
      '<a onClick="return confirm(\'Seid Ihr sicher, dass Ihr den Spieler löschen wollt?\')" href="?delete='.$p['id'].'">Löschen</a>&nbsp;&nbsp;'. 
      '<a onClick="return confirm(\'Spieler aktivieren?\')" href="activate.php?activate=1&activationcode='.$p['activationkey'].'&loginname='.$p['login'].'">Aktivieren</a>&nbsp;&nbsp;'.
      
      "</td>\n".      
      "</tr>\n";
  }


  echo "</table><hr>\n";
  
} // list_player_not_activated




function list_player_locked () {
  echo "<h1>Gesperrte Spieleraccounts</h1>\n";
  echo 'Die Spieler sind nach der Reihenfolge Ihres letzen Logins sortiert. <a href="#lastlocked">Ganz unten</a>.';
  echo "<p><table>\n";
  echo " <tr class=\"tblhead\"><td>[ID] Spielername</td><td>Act.Key</td><td>eMail</td><td>Regtime</td><td>Lastseen</td><td>Inaktiv<br>in Stunden</td><td>Aktion</td></tr>\n";

  $players = do_mysqli_query("SELECT id, name, email, activationkey, status, ".
                            "  unix_timestamp() - lastseen AS inactive_seconds,".
                            "  from_unixtime(regtime)      AS regtime_time,".
                            "  from_unixtime(lastseen)     AS lastseen_time,".
                            "  regtime                     AS regtime_unixtime,".
                            "  lastseen                    AS lastseen_unixtime".
                            " FROM player WHERE activationkey IS NULL AND status IN (1,2) ".
                            " ORDER BY id");
  $num = mysqli_num_rows($players);
  $i = 0;

  while ( $p = mysqli_fetch_array($players) ) {
    $i++;
    echo 
      ' <tr class="tblbody">'.
      "<td>".($num-$i == 10 ? '<a name="lastlocked"></a>' : "" ).
      "[".$p['id'].'] <a href="multihunter.php?showid='.$p['id'].'">'.$p['name']."</a> (".$p['status'].")</td>\n".
      '<td>'.$p['activationkey']."</td>\n".
      '<td><a href="mailto:'.$p['email'].'">'.$p['email']."</a></td>\n".
      '<td align="right" title="regtime_unixtime: '.$p['regtime_unixtime'].'">'.$p['regtime_time']. "</td>\n".
      '<td align="right" title="lastseen_unixtime: '.$p['lastseen_unixtime'].'">'.$p['lastseen_time']."</td>\n".
      '<td align="right" title="Sekunden: '.$p['inactive_seconds'].'">'.formatTime($p['inactive_seconds'])." h</td>\n".
      // Länger als xxx Tage nicht aktiviert...
      "<td>".
      '<a onClick="return confirm(\'Seid Ihr sicher, dass Ihr den Spieler löschen wollt?\')" href="?delete='.$p['id'].'">Löschen</a>&nbsp;&nbsp;'. 
      '<a onClick="return confirm(\'Sperre aufheben?\')" href="adminmaintain.php?reactivate=1&id='.$p['id'].'">Freigeben</a>&nbsp;&nbsp;'.
      
      "</td>\n".      
      "</tr>\n";
  }


  echo "</table><hr>\n";
  
} // list_player_not_activated



function list_player_new ($hours = 48) {
  echo "<h1>Neue Spieler in den letzten $hours Stunden</h1>\n";
  echo 'Die Spieler sind nach der Reihenfolge Ihres letzen Logins sortiert. <a href="#lastnew">Ganz unten</a>.';
  echo "<p><table>\n";
  echo " <tr class=\"tblhead\"><td>[ID] Spielername</td><td>Act.Key</td><td>eMail</td><td>Regtime</td><td>Lastseen</td><td>Inaktiv<br>in Stunden</td><td>Aktion</td></tr>\n";

  $players = do_mysqli_query("SELECT id, name, email, activationkey, status, ".
                            "  unix_timestamp() - lastseen AS inactive_seconds,".
                            "  from_unixtime(regtime)      AS regtime_time,".
                            "  from_unixtime(lastseen)     AS lastseen_time,".
                            "  regtime                     AS regtime_unixtime,".
                            "  lastseen                    AS lastseen_unixtime".
                            " FROM player WHERE unix_timestamp()-regtime < 60*60*".intval($hours).
                            " ORDER BY id");
  $num = mysqli_num_rows($players);
  $i = 0;

  while ( $p = mysqli_fetch_array($players) ) {
    $i++;
    echo 
      ' <tr class="tblbody">'.
      "<td>".($num-$i == 10 ? '<a name="lastnew"></a>' : "" ).
      "[".$p['id'].'] <a href="multihunter.php?showid='.$p['id'].'">'.$p['name']."</a> (".$p['status'].")</td>\n".
      '<td>'.$p['activationkey']."</td>\n".
      '<td><a href="mailto:'.$p['email'].'">'.$p['email']."</a></td>\n".
      '<td align="right" title="regtime_unixtime: '.$p['regtime_unixtime'].'">'.$p['regtime_time']. "</td>\n".
      '<td align="right" title="lastseen_unixtime: '.$p['lastseen_unixtime'].'">'.$p['lastseen_time']."</td>\n".
      '<td align="right" title="Sekunden: '.$p['inactive_seconds'].'">'.formatTime($p['inactive_seconds'])." h</td>\n".
      // Länger als xxx Tage nicht aktiviert...
      "<td>".
      '<a onClick="return confirm(\'Seid Ihr sicher, dass Ihr den Spieler löschen wollt?\')" href="?delete='.$p['id'].'">Löschen</a>&nbsp;&nbsp;'. 
      '<a onClick="return confirm(\'Sperre aufheben?\')" href="adminmaintain.php?reactivate=1&id='.$p['id'].'">Freigeben</a>&nbsp;&nbsp;'.
      
      "</td>\n".      
      "</tr>\n";
  }


  echo "</table><hr>\n";
  
} // list_player_new


function get_old_premiumacc($pid, $oldpid, $oldtable = "premiumacc") {
  $con_old = mysql_connect( DBHOST_OLD, DBUSER_OLD, DBPASSWD_OLD);
  if(!$con_old) {
    echo mysqli_error($GLOBALS['con']);
    die();
  }
  mysql_select_db( DBSELECT_OLD, $con_old);

  echo "Teste, ob er Premium-User war (".$oldpid."). ";
  $sql = 
    "SELECT pa.*,p.name ".
    " FROM ".mysqli_escape_string($GLOBALS['con'], $oldtable)." pa LEFT JOIN player p ON p.id = pa.player".
    " WHERE player = ".$oldpid.
    "  AND expire > UNIX_TIMESTAMP() AND payd IS NOT NULL ".
    " ORDER BY expire DESC";
	

  echo "\n<!-- $sql -->\n";
  
  $prem = mysqli_query($GLOBALS['con'], $sql);
  if($prem) {
    if (mysqli_num_rows($prem) > 0) {
      $num = 0;
      while($premium = mysqli_fetch_assoc($prem)) {
	$sql = sprintf("INSERT INTO premiumacc ".
		       " (player, type, expire, payd, paydtime,paytext) ".
		       " VALUES (%d, %d, %d, 0, UNIX_TIMESTAMP(), '%s')",
		       $pid, $premium['type'] ,$premium['expire'],                        
		       "Premium-Account ".$premium['name']." aus alter Runde übernommen."
		       );
	do_mysqli_query($sql);
	$num++;
      }
      echo "<b>Ja</b>. $num Stück übernommen<br>";
    }
    else {
      echo "Nein. <br>";
    }      
  }
  else {
    echo "<P>SQL Fehler: ".mysqli_error($GLOBALS['con']);
  }  
}


function activate_from_booking($nr = 1) {
  $nr = intval($nr);
  $bookdata = do_mysqli_query("SELECT * FROM booking WHERE status = 0 ORDER BY bookid LIMIT $nr");

  $i = 0;
  while ($book = mysqli_fetch_assoc($bookdata)) {
    echo "Lege Spieler <b>".$book['name']."</b> an. ";
    $p['pid']      = null;
    $p['pos']      = $book['zone'];
    $p['name']     = $book['name'];
    $p['email']    = $book['email'];
    //    $p['ref']      = $book['recruiter'];
    $p['md5pw']    = $book['password'];
    $p['sms']      = $book['sms'];

    include_once("includes/newplayer.func.php");
    $result = insert_new_player($p);
    
    if ($result==null) {
      do_mysqli_query("UPDATE booking SET status=1 WHERE bookid = ".$book['bookid']);
      echo "done. ";
      get_old_premiumacc($p['pid'], $book['oldpid']);
    }
    else {
      echo "Fehler: ".$result."<br>";
    }
          
    echo "<p>";
  }
}
?>