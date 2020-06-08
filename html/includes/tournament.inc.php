<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2010 
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

define("TOURNAMENT_PART_BONUSPOINT", 5);
define("TOURNAMENT_HOLD_BONUSPOINT", 2);

define("TOURNAMENT_MIN_PLAYERS", 2);
define("TOURNAMENT_MAX_PLAYERS", 64);
define("TOURNAMENT_AUTOCREATE_MAX_PLAYERS", 24);

define("TOURNAMENT_MIN_GOLD", 1000);
define("TOURNAMENT_MAX_GOLD", 50000);

define("TOURNAMENT_DURATION", 900); // 15 Minuten Dauer
define("TOURNAMENT_MAX_PART", 4);
define("TOURNAMENT_MAX_PART_PREMIUM", 8);

define("TOURNAMENT_MAX_ORGANIZE", 3);
define("TOURNAMENT_MAX_ORGANIZE_PREMIUM", 6);


define("TOURNAMENT_DEBUG", 1);
define("TOURNAMENT_DEBUG_ARRAYS", 0); // einen var_dump machen lassen
define("TOURNAMENT_MODULO", 300);

function create_tournament($time, $maxplayers) {
  $time2 = date("H",$time);
  //$gold = (($maxplayers - ($maxplayers % 8)) / 8) * 5000;
  if(($time2 >= 14) && ($time2 <= 24)) {
      do_mysql_query("INSERT INTO tournament (time,maxplayers,gold) VALUES (".$time.",".$maxplayers.",2500)");
  }
  else {
      do_mysql_query("INSERT INTO tournament (time,maxplayers,gold) VALUES (".$time.",".$maxplayers.",1000)");
  }
}


/**
 * Turniere durchführen.
 *
 * Die Funktion greift alle Turniere auf, die noch
 * nicht durchgeführt und 
 */
function calc_tournaments() {
  if (!defined("ENABLE_TOURNAMENT") || !ENABLE_TOURNAMENT)
  return;

  tDebug("<h2 style=\"color: red;\">Starte Turniere</h2>\n");

  $tourn = do_mysql_query("SELECT t.*,p.name FROM tournament t LEFT JOIN player p ON p.id = t.organizer ".
			  " WHERE t.calctime IS NULL ".
			  " AND unix_timestamp() > t.time+".TOURNAMENT_DURATION.
			  " ORDER BY t.time,tid");

  $num = mysqli_num_rows($tourn);
  $num_tour = $num;

  tDebug("$num Turniere warten auf Durchführung\n<ul>\n");
  while($t = do_mysql_fetch_assoc($tourn)) {
    unset($R);

    $start = $t['time'] + TOURNAMENT_DURATION;
    tDebug("<li>Turnier ".$t['tid']." startet: ".date("d.m.y H:i", $start).".");
    tDebug("<br>\nVeranstalter: ".($t['name'] ? $t['name'] : "(Server)" ));

    $text = "\nVeranstalter: ".($t['name'] ? $t['name'] : "(Server)");


    // Teilnehmer berechnen
    $parts = do_mysql_query("SELECT tp.*,p.name FROM tournament_players tp ".
			    " LEFT JOIN player p ON p.id = tp.player ".
			    " WHERE tid = ".$t['tid']." AND booktime IS NOT NULL ORDER BY rand()");
    $num = mysqli_num_rows($parts);
    $num_players = $num;
    $max_players = $t['maxplayers'];
    $bonus = TOURNAMENT_HOLD_BONUSPOINT * $num;

    tDebug("<br>\n".$num." bestätigte Teilnehmer <b>".
    ( $num < $t['maxplayers'] / 2
    ? "<font color=\"red\">(nicht genug)</font>"
    : "<font color=\"green\">(genug)</font>" ).
	   "</b>:\n<ul style=\"margin-top: 0px;\"><!-- Teilnehmer -->\n");

    $text .= ", ".$num." Teilnehmer\n[list]";

    $i = 0;
    $ids = "(";
    while($p = do_mysql_fetch_assoc($parts)) {
      $ids .= $p['player'].",";

      $R[$i] = getRPG($p['player']);
      $R[$i]['name'] = $p['name'];
      $R[$i]['nr']   = $i;
      $R[$i]['fortune'] = 0; // Glück mit 0 initialisieren
      $i++;
      tDebug("<li>".$p['name']."\n");
      $text .= "\n[*]".$p['name'];
    }
    $ids .= "0)";

    do_mysql_query("UPDATE rpg SET tournaments = tournaments+1 WHERE player IN $ids");

    tDebug("</ul><!-- Teilnehmer -->\n");
    $text .= "\n[/list]\n";

    /**
     * Nun die Kämpfe austragen.
     */
    if($t['maxplayers'] >= TOURNAMENT_MIN_PLAYERS &&
    $num >= $t['maxplayers'] / 2) {
      tDebug($R);
      $text .= "Das Turnier Nr. ".$t['tid']." beginnt...\n";
      $result = tournament_fight($R, $text);

      /* Gewinner ausgeben */
      $firstgold = round($t['gold'] * 0.75);
      $secondgold= $t['gold'] - $firstgold;

      tDebug("\nGewinner des Turniers:\n<ol><!-- Winners -->\n");
      $text .= "\nGewinner des Turniers:[list]";

      tDebug("<li>".$result['first']['name'].", erhält $firstgold Gold Prämie</li>\n");
      tDebug("<li>".$result['second']['name'].", erhält $secondgold Gold Prämie</li>\n");  
      $text .= "\n[*] ".$result['first']['name'].", erhält $firstgold Gold Prämie";
      $text .= "\n[*] ".$result['second']['name'].", erhält $secondgold Gold Prämie\n[/list]";

      do_mysql_query("UPDATE player SET gold=gold+$firstgold,cc_resources=1".
		     " WHERE id = ".$result['first']['player']);
      do_mysql_query("UPDATE player SET gold=gold+$secondgold,cc_resources=1".
		     " WHERE id = ".$result['second']['player']);

      tDebug("</ol><!-- Winners -->\n");

      echo "\n<div class='msg'>".bbCode($text)."</div>\n";
      echo "\n\n<!--\n$text\n-->";

      // Die Nachricht nun an alle beteiligten Spieler senden
      $organizer_sent = false;
      foreach($R as $pl) {
        $topic = "Turnierteilnahme: erfolglos";
        if($result['first']['player']==$pl['player'])
        $topic = "Turnierteilnahme: Sieger!";
        else if($result['second']['player'] == $pl['player'])
        $topic = "Turnierteilnahme: Zweitplatzierter!";

        $body = $text;
        if($t['organizer'] == $pl['player']) {
          $body .= "\n\n[b]Ihr erhaltet $bonus Bonuspunkte für die Ausrichtung des Turniers[/b].";
          $organizer_sent = true;
        }

        inform_tournament_player($pl['player'], $topic, $body);
      }

      if(!$organizer_sent && $t['organizer']) {
        $body .= "\n\n[b]Ihr erhaltet $bonus Bonuspunkte für die Ausrichtung des Turniers[/b].";
        inform_tournament_player($t['organizer'], $topic, $body);
      }

      tDebug($R);

      // Den Verpassern ne Nachricht schicken
      $notparts = do_mysql_query("SELECT player FROM tournament_players ".
				 " WHERE tid = ".$t['tid']." AND booktime IS NULL");
      while($pl = do_mysql_fetch_assoc($notparts)) {
        $topic = "Turnier verpasst";
        $body = "[b]Ihr selbst habt das Turnier verpasst[/b], weil Ihr Eure Teilnahme nicht rechtzeitig bestätigt habt.\n\n";
        $body .= $text;
        inform_tournament_player($pl['player'], $topic, $body);
      }
    }
    else {
      if($t['maxplayers'] < TOURNAMENT_MIN_PLAYERS) {
        // Fehler. So ein Turnier sollte es nicht geben!
        tDebug("FEHLER: maxplayers zu klein!<br>\n");
      }
      else {
        // Turnier findet nicht statt: Teilnehmermangel
        // Den Verpassern ne Nachricht schicken
        $players = do_mysql_query("SELECT player FROM tournament_players ".
				  " WHERE tid = ".$t['tid']);
        while($pl = do_mysql_fetch_assoc($players)) {
          $topic = "Turnier ausgefallen";
          $body = "Das Turnier ist ausgefallen, weil nicht genügend Teilnehmer bestätigt haben.\n\n";
          $body .= $text;
          inform_tournament_player($pl['player'], $topic, $body);
        }
      }
    }

    do_mysql_query("UPDATE tournament SET calctime = UNIX_TIMESTAMP() WHERE tid = ".$t['tid']);
    if($t['organizer']) {
      do_mysql_query("UPDATE player SET bonuspoints = bonuspoints + $bonus WHERE id = ".$t['organizer']);
    }


    /*define("TOURNAMENT_MIN_PLAYERS", 4);
     define("TOURNAMENT_MAX_PLAYERS", 64);*/
    // Test wieviel Turniere noch ausgeschrieben sind
    /*$tourtodo = do_mysql_query("SELECT * FROM tournament WHERE time + ".TOURNAMENT_DURATION." > unix_timestamp( ) AND time < unix_timestamp( ) + (24*60*60)");
    $numtodo24 = mysqli_num_rows($tourtodo);*/
    $maxtour = do_mysql_query("SELECT max(time) as max FROM tournament");
    $get_maxtour = do_mysql_fetch_assoc($maxtour);
    $maxtour_time = $get_maxtour['max'];

    // Wenn weniger als 10 Turniere innerhalb der nächsten 24h
    //if($numtodo24 < 10) {
    if(($num_players / $max_players) >= 1) {
      tDebug("\nLetztes Turnier war voll -> erstelle Turnier mit erhöhter Spielerzahl ...\n");
      if (($max_players + 4) <= TOURNAMENT_AUTOCREATE_MAX_PLAYERS) $max_players += 4;
      create_tournament($maxtour_time + 7200, $max_players);
      	
      // Sonderbehandlung, noch schnell ein weiteres Turnier innerhalb der nächsten 3h einführen (falls möglich) 
      $tour3 = do_mysql_query("SELECT * FROM tournament WHERE time > unix_timestamp( ) AND time < unix_timestamp( ) + (3*60*60)");
    		$numtodo3 = mysqli_num_rows($tour3);
    		if($numtodo3>0) {
    		  $timenum = 0;
    		  while($get_tour3 = do_mysql_fetch_assoc($tour3)) {
    		    $time3[$timenum] = $get_tour3['time'];
    		    $timenum++;
    		  }
    		  $inserted = 0;
    		  $timediff = 10;
    		  while(($inserted==0) && ($timediff<=170)) {
    		    $testtime = round(((time() + 60*$timediff)/TOURNAMENT_MODULO)*TOURNAMENT_MODULO);
    		    for($i=0;$i<$numtodo3;$i++) {
    		      if($time3[$i]!=$testtime) {
    		        $inserted = 1;
    		        create_tournament($testtime,$max_players);
    		        tDebug("\nZusätzliches Turnier innerhalb der nächsten 3h eingefügt ...\n");
    		        break;
    		      }
    		    }
    		    $timediff+=10;
    		  }
    		}
    		else {
    		  $testtime = round(((time() + 60*60)/TOURNAMENT_MODULO)*TOURNAMENT_MODULO);
    		  create_tournament($testtime,$max_players);
    		  tDebug("\nZusätzliches Turnier findet in 1h statt ...\n");	
    		}
    }
    else if((($num_players / $max_players) >= 0.75) && (($num_players / $max_players) < 1)) {
      tDebug("\nLetztes Turnier zu mehr als 75% belegt -> erstelle Turnier mit gleicher Spielerzahl ...\n");
      create_tournament($maxtour_time+7200,$max_players);
    }
    else if((($num_players / $max_players) >= 0.25) && (($num_players / $max_players) < 0.75)) 		{
      tDebug("\nLetztes Turnier zu weniger als 75% belegt -> erstelle Turnier mit erniedrigter Spielerzahl ...\n");
      if ($max_players >= TOURNAMENT_MIN_PLAYERS + 4) $max_players -= 4;
      create_tournament($maxtour_time+7200,$max_players);
    }
    //}
  } // while($t ...
  tDebug("</ul>\n<p>\n");
} // function calc_tournaments() 


/**
 * Nachricht an einen Tournier-Teilnehmer schicken
 */
function inform_tournament_player($pl, $topic, $body) {
  if(is_numeric($pl) && $pl > 0) {
    $sql = sprintf("INSERT INTO message (date,category,recipient,sender,header,body) ".
		   " VALUES (UNIX_TIMESTAMP(), 5, %d, '%s', '%s', '%s')",
    $pl, "Der Turnierbote", $topic, $body);
    do_mysql_query($sql);
    do_mysql_query("UPDATE player SET cc_messages=1 WHERE id = ".$pl);
  }
  else {
    echo "Fehler: pl not numeric\n";
  }
}


function getRPG($pid) {
  $rpg = do_mysql_query_fetch_assoc("SELECT * FROM rpg WHERE player = ".$pid);
  if(!$rpg) {
    do_mysql_query("INSERT INTO rpg (player, createtime) VALUES (".$pid.", UNIX_TIMESTAMP())");
    $rpg = do_mysql_query_fetch_assoc("SELECT * FROM rpg WHERE player = ".$pid);   
    if(!$rpg)
	die("Konnte Ihren RPG-Charakter nicht anlegen.</body></html>");
  }
  return $rpg;
}


/**
 * Turnierkampf findet statt.
 */
function tournament_fight(&$OR, &$text) {
  $R = $OR;

  $round=0;
  tDebug("<ul style=\"margin-top: 0px;\">");
  $text .= "[list]";

  // Solange noch mehr als 2 Leute kämpfen:
  while( ($c = sizeof($R)) > 1 && $round++ < 20) {
    $j = 0;
    $log = pow(2, floor(log($c) / log(2)));
    tDebug("<li>Runde $round, Teilnehmer: $c, Grenze: $log");
    $text .= "\n[*][u]Runde ${round}[/u], Teilnehmer: $c";

    // Bei ungerader Teilnehmerzahl kommt einer durch Losentscheid weiter
    if($c % 2 == 1) {
      // Wenn nur einer mehr als eine 2er-Potenz mitspielt,
      // dann kicke einen raus.
      $fortune = rand(0, $c-1);
      if($c - $log == 1) {
        tDebug("<br>".$R[$fortune]['name']." (".$fortune.") fliegt durch Losentscheid raus");
        $text .= "\n[b]".$R[$fortune]['name']."[/b] fliegt durch Losentscheid raus";
      }
      else {
        tDebug("<br>".$R[$fortune]['name']." (".$fortune.") kommt durch Losentscheid weiter");
        $text .= "\n[b]".$R[$fortune]['name']."[/b] kommt durch Losentscheid weiter";

        // Glück erhöhen, damit derjenige beim nächstenmal nicht nochmal Glück hat
        $R[$fortune]['fortune'] += 1;

        // In nächste Runde übertragen
        $RN[$j++] = $R[$fortune];
      }
      // Den Eintrag in dieser Runde löschen
      unset($R[$fortune]);
    }

    $round2 = 0;
    // Nun jeweils 2 kämpfen und den Sieger weiterkommen lassen (Kampfrunden)
    while(sizeof($R) > 0 && $round2++ < 20) {
      tDebug("<br>Kampf $round2 mit ".sizeof($R)." Teilnehmern - ");
      $text .= "\nKampf $round2 - ";

      $a = array_pop($R); $b = array_pop($R);
      if(tournament_duell($a, $b) == 0) {
        $OR[$a['nr']]['victories']++;
        $OR[$b['nr']]['defeats']++;
        // Rückgabewerte speichern
        $ret['second'] = &$b;
        $ret['first']  = &$a;
        $RN[$j++] = $a;

        // Den Datensatz des Verlieres updaten, der nun $round Kämpfe ausgetragen hat
        do_mysql_query("UPDATE rpg SET fights = fights + $round WHERE player = ".$b['player']);
      }
      else {
        $OR[$a['nr']]['defeats']++;
        $OR[$b['nr']]['victories']++;
        // Rückgabewerte speichern
        $ret['second'] = &$a;
        $ret['first']  = &$b;
        $RN[$j++] = $b;

        // Den Datensatz des Verlieres updaten, der nun $round Kämpfe ausgetragen hat
        do_mysql_query("UPDATE rpg SET fights = fights + $round WHERE player = ".$a['player']);
      }
      tDebug($ret['first']['name']." besiegt ".$ret['second']['name']);
      $text .= "[b]".$ret['first']['name']."[/b] besiegt [b]".$ret['second']['name']."[/b]";
    } // while
    
    $R = $RN;
    unset($RN);
  } // while
 
  tDebug("</ul>");
  $text .= "\n[/list]\n";

  // Den Datensatz des Verlieres updaten, der nun $round Kämpfe ausgetragen hat
  do_mysql_query("UPDATE rpg SET fights = fights + $round,victories=victories+1 WHERE player = ".$ret['first']['player']);

  return $ret;
}

function tournament_duell($a, $b) {
  mt_srand((double)microtime() * 1000000);
  $zufall = mt_rand();
  if($zufall % 2 == 0) return 0; else return 1;
}


function tDebug($str) {
  if(TOURNAMENT_DEBUG) {
    if(is_array($str)) {
      if(TOURNAMENT_DEBUG_ARRAYS) {
        echo "<pre>\n";
        var_dump($str);
        echo "\n</pre>\n";
      }
    }
    else {
      echo $str;
    }
  }
}
?>