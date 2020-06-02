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
 * Copyright (c) 2006 by Holy-Wars2.de
 *
 * written by Markus Sinner
 *
 * 01. Aug 2006
 ***************************************************/
require_once("includes/session.inc.php");
require_once("includes/tournament.inc.php");
require_once("includes/premium.inc.php");

start_page();

define("TOURNAMENT_CONFIRM_TEXT", "JETZT Attacke!!!");
?>
<script language="JavaScript">
//<!--
function toggleVisibility() {
    table = document.getElementById("tournamentDone");
    text  = document.getElementById("toggleDone");
    
    if(table.style.display == 'none')
    {
        table.style.display = 'inline';
        text.innerHTML   = 'Ausblenden';
    }
    else
    {
        table.style.display = 'none';
        text.innerHTML   = 'Anzeigen';
    }
}
//-->
</script>
<?
start_body();

if(defined("HISPEED") && HISPEED) {
  echo "<h1>Turniere in der HiSpeed deaktiviert.";
  end_page();
  die();
}

$inform = $error = null;
$pid = $_SESSION['player']->getID();

// Den RPG-Charakter holen
$rpg = getRPG($pid);


if(!isset($gold))  $gold  = TOURNAMENT_MIN_GOLD;
if(!isset($maxp))  $maxp  = TOURNAMENT_MIN_PLAYERS*2;
if(!isset($stime)) $stime = 240;

$max_organize = is_premium_tournament() ? TOURNAMENT_MAX_ORGANIZE_PREMIUM : TOURNAMENT_MAX_ORGANIZE;
$part_max = is_premium_tournament() ? TOURNAMENT_MAX_PART_PREMIUM : TOURNAMENT_MAX_PART;

if(isset($newtournament) && is_numeric($gold) && is_numeric($maxp) && is_numeric($stime)) {
  $count_tournaments = do_mysql_query_fetch_assoc("SELECT count(*) AS c FROM tournament ".
						  "WHERE organizer = $pid AND time > unix_timestamp()");

  if($gold < TOURNAMENT_MIN_GOLD) {
    $error = "Ihr müsst mindestens ".TOURNAMENT_MIN_GOLD." Gold einsetzen.";
  }
  else if($gold > TOURNAMENT_MAX_GOLD) {
    $error = "Ihr könnt maximal ".TOURNAMENT_MAX_GOLD." Gold einsetzen.";
  }
  else if($gold > $_SESSION['player']->getGold()) {
    $error = "Ihr bestitzt nicht soviel Gold.";
  }
  else if($maxp < TOURNAMENT_MIN_PLAYERS*2) {
    $error = "Ihr müsst mindestens ".(TOURNAMENT_MIN_PLAYERS*2)." Maximalteilnehmer angeben";
  }
  else if($maxp > TOURNAMENT_MAX_PLAYERS) {
    $error = "Es dürfen maximal ".(TOURNAMENT_MAX_PLAYERS)." Spieler an einem Turnier teilnehmen.";
  }
  else if($count_tournaments['c'] >=  $max_organize) {
    $error = "Ihr dürft nicht mehr als ".TOURNAMENT_MAX_ORGANIZE." (".TOURNAMENT_MAX_ORGANIZE_PREMIUM." bei Premium-Usern) Turniere veranstalten.";
  }
  else if($stime < 60) {
    $error = "Ihr müsst mindestens 60 Minuten eingeben.";
  }
  else if($stime > 60*24*7) {
    $error = "Mehr als ".(60*24*7)." Stunden (7 Tage) im Vorraus können keine Turniere angesetzt werden.";
  }
  else {
    do_mysql_query("UPDATE player SET gold = gold - $gold,cc_resources=1 WHERE id = $pid AND gold >= $gold");
    if(mysqli_affected_rows($GLOBALS['con']) > 0) {
      $sql = sprintf("INSERT INTO tournament (organizer, gold, maxplayers, time) ".
		     "VALUES (%d, %d, %d, round((unix_timestamp() + 60*%d)/%d)*%d )",
      $pid, $gold, $maxp, $stime, TOURNAMENT_MODULO, TOURNAMENT_MODULO);
      if($_SESSION['player']->isAdmin())
      echo $sql;

      do_mysql_query($sql);
    }
  }
}


if(isset($calc) && $_SESSION['player']->isMaintainer()) {
  calc_tournaments();
}


/**
 * Spieler möchte an einem Turnier teilnehmen
 *
 * "tournamentmagic" dient dazu, dass man nicht mehrmals die Seite neu lädt.
 */
if(isset($tid) && isset($part) &&
(!isset($_SESSION['tournamentmagic']) || $tmagic == $_SESSION['tournamentmagic']))
{

  $tid = intval($tid);
  $part= intval($part);

  $tres = do_mysql_query("SELECT t.*,tp.player AS part,".
			 " unix_timestamp() > time+".TOURNAMENT_DURATION." AS t_over,".
			 " unix_timestamp() > time AND unix_timestamp() < time+".TOURNAMENT_DURATION." AS now".
			 " FROM tournament t ".
			 " LEFT JOIN tournament_players tp".
			 "  ON tp.tid=t.tid AND tp.player = ".$pid.
			 " WHERE t.tid = $tid");
  if(mysqli_num_rows($tres) == 1) {
    $t = do_mysql_fetch_assoc($tres);

    if($t['t_over']) {
      $error = "Turnier vorüber.";
    }
    else {
      switch($part) {
        case 0:
          if($t['part'] && $t['now']) {
            $error = "Turnier läuft gerade.";
          }
          else if($t['part']) {
            $inform = "Rückzieher."; 
            do_mysql_query("DELETE FROM tournament_players WHERE tid=$tid AND player=$pid");
          }
          else {
            $error  = "Nicht angemeldet.";
          }
          break;

        case 1:
          if($t['part']) {
            $error  = "Bereits angemeldet.";
          }
          else {
            $actsql = "SELECT count(*) AS cnt,".
            " count( if( time-".$t['time']."<".TOURNAMENT_DURATION.
            "        AND ".$t['time']."-time<".TOURNAMENT_DURATION.", TRUE, NULL)) AS parallel ".
            " FROM tournament_players LEFT JOIN tournament USING(tid) ".
            " WHERE player = $pid AND time + ".TOURNAMENT_DURATION."> UNIX_TIMESTAMP()";

            $actres = do_mysql_query($actsql);
            if($_SESSION['player']->isAdmin()) {
              echo $actsql;
            }
            $act = do_mysql_fetch_assoc($actres);
             
            if($act['cnt'] >= $part_max) {
              $error = "Maximal ".TOURNAMENT_MAX_PART." (".(TOURNAMENT_MAX_PART_PREMIUM)." für Premium-User) Voranmeldungen für Turniere erlaubt.";
            }
            else if($act['parallel']) {
              $error = "Bereits ein Turnier parallel.";
            }
            else {
              do_mysql_query("UPDATE player SET bonuspoints = bonuspoints-".TOURNAMENT_PART_BONUSPOINT.
              " WHERE id = $pid AND bonuspoints >= ".TOURNAMENT_PART_BONUSPOINT);
                            
              $allow_it = true;
              
              
              // Für Newbs, die bisher noch an keinem Turnier teilgenommen hatten, wird
              // die Teilnahme gestattet. Einfach schauen, ob es schon eine Anmeldung gab/gibt.
              if(mysqli_affected_rows($GLOBALS['con']) == 0) {                
                $test_count = do_mysql_query("SELECT tid FROM tournament_players WHERE player = $pid LIMIT 1");
                if(mysqli_num_rows($test_count) > 0) {
                  $error = "Ihr habt nicht genügend Bonuspunkte!";
                  $allow_it = false;
                }                   
              }
              
              if($allow_it) {
                $inform = "Teilnahme angemeldet. <b>Vergesst nicht</b>: wenn das Turnier beginnt, müsst Ihr Euch".
                "<br> erneut hier melden und ".TOURNAMENT_CONFIRM_TEXT." bestätigen! (zwischen ".date("d.m.y H:i", $t['time'])." und ".date("H:i", $t['time']+TOURNAMENT_DURATION).")";
                do_mysql_query("INSERT INTO tournament_players (tid,player) VALUES ($tid, $pid)");
                $_SESSION['player']->updateResources();
              }
            }
          }
          break;

        case 2:
          if(!$t['now']) {
            $error = "Turnier läuft noch nicht";
          }
          else if($t['part']) {
            $inform = "Turnier jetzt bestreiten";
            
            do_mysql_query("UPDATE tournament_players SET booktime=UNIX_TIMESTAMP() ".
            " WHERE tid=$tid AND player=$pid AND booktime IS NULL");
            if(mysqli_affected_rows($GLOBALS['con']) > 0) {
              do_mysql_query("UPDATE player SET bonuspoints = bonuspoints+".TOURNAMENT_PART_BONUSPOINT.
              " WHERE id = $pid");
              $_SESSION['player']->updateResources();
            }
          }
          else {
            $error = "Nicht für das Turnier angemeldet.";
          }
          break;
        default:
          $error = "Unbekannte Funktion";
      } // switch
    }
  } // if (mysqli_num_rows($t) == 1)
  else {
    $error =  "Schummeln?";
  }
}


if($error == null) {
  $_SESSION['tournamentmagic'] = rand(10000,99999);
  unset($_SESSION['tournamentmagic']);
}
?>

<style type="text/css">
tr.col0 {
	background: #FFEFDF;
	text-align: center;
}

tr.col1 {
	background: #FFDFEF;
	text-align: center;
}

tr.col2 {
	background: #AAEEAA;
	text-align: center;
	font-weight: bold;
}

tr.col3 {
	background: #FF9090;
	text-align: center;
}

div.error {
	background: #FFC090;
	border: 1px solid black;
	color: black;
	width: 500px;
	padding: 8px;
	margin-bottom: 5px;
}

div.inform {
	background: #90FF90;
	border: 1px solid black;
	color: black;
	width: 500px;
	padding: 8px;
	margin-bottom: 5px;
}

th {
	font-size: 12px;
}
</style>

<h1>Turniere</h1>
Ihr könnt hier an Ritter-Turnieren teilnehmen. Die Teilnahme ist im
Prinzip kostenlos.
<p><span style="font-size: 16px;">Hier die Regeln <b>zur Teilnahme</b>
in Kurzfassung:</span>
<ul style="width: 600px;">
	<li>Die Sieger eines Turnieres erhalten Gold.
	
	
	<li>Für die Anmeldung an einem Turnier müsst ihr <? echo TOURNAMENT_PART_BONUSPOINT; ?>
	Bonuspunkte berappen. Diese werden Euch aber bei der Bestätigung der
	Teilnahme wieder gutgeschrieben.<br>
	Neue Spieler dürfen EIN Turnier ohne Abzug von Bonuspunkten bestreiten!
	
	
	<li>Habt Ihr Euch zu einem Turnier gemeldet, dann müsst Ihr Euch
	innerhalb der angegebenen Zeitspanne auf die Turnierseite begeben und
	dort <b>Eure Teilnahme bestätigen</b>!
	
	
	<li>Bestätigt Ihr die Teilnahme nicht, dann sind die <? echo TOURNAMENT_PART_BONUSPOINT; ?>
	Bonuspunkte verloren.
	
	
	<li>Es müssen mindestens die Häfte der maximalen Teilnehmer ihre
	Teilnahme am Turnier bestätigt haben. Ansonsten füllt es aus.
	
	
	<li>Das Turnier startet automatisch am Ende der angegebenen Zeitspanne.
	Ihr bekommt eine Nachricht, wie das Ergebnis ausgefallen ist.
	
	
	<li>Haben sich mehr Spieler angemeldet, als das Turnier angibt, dann
	spielen jene Spieler mit, die sich am frühesten angemeldet haben. Es
	ist also lohnend, sich früh anzumelden. Die übrigen Spieler sind dann
	halt leider Zuschauer.
	
	
	<li>Ein Spieler kann an <? echo TOURNAMENT_MAX_PART;?> (<? echo TOURNAMENT_MAX_PART_PREMIUM;?>,
	wenn er Premium-User ist) Turnier<? if(TOURNAMENT_MAX_ORGANIZE > 1) echo "en";?>
	teilnehmen

</ul>

<h2 style="color: red">Fehler bitte im Forum melden!</h2>
Derzeit bekannte Fehler:
<p>
<ul>
	<li>Es können mehr Spieler teilnehmen, als eigentlich maximal
	eingestellt ist.

</ul>
<hr>
<h1>Die Turniere</h1>
Euch stehen
<? echo $_SESSION['player']->getBonuspoints(); ?>
Bonuspunkte zur Verfügung.
<p><?
if(isset($part)) {
  if($inform != null) {
    echo '<div class="inform">'.$inform."</div>\n";
    if(!is_premium_noads())
    include("includes/easyad_layer.html");
  }
  if($error != null) {
    echo "<a name=\"mark\"></a>\n";
    echo '<div class="error"><b>Fehler: </b>'.$error."</div>\n";
  }
}

switch($sort) {
  case "gold":
    $order = "gold DESC";
    break;
  case "player":
    $order = "name ASC";
    break;
  case "booknr":
    $order = "count DESC";
    break;
  case "time":
  default:
    $order = "t.time,tid";
}

echo '<table cellpadding="0" cellspacing="0" width="600"><tr><td><h2>Eigenes Turnier veranstalten</h2></td>';
echo '<td><a href="#ownTournament">Hier klicken</a></td></tr></table>';


echo '<a name="tableDone"></a><table cellpadding="0" cellspacing="0" width="600"><tr><td><h2>Vergangene Turniere</h2></td>';
echo '<td><a href="#tableDone" id="toggleDone" onClick="toggleVisibility();">Anzeigen</a></td></tr></table>';

// Tabelle starten
printTableHeader(0);

$tourn = do_mysql_query("SELECT t.tid,t.gold,t.time,t.calctime,t.maxplayers,o.name,p.booktime,".
			"  count(tp.player) AS count, count(tp.booktime) AS count_book, ".
			"  unix_timestamp() > t.time+".TOURNAMENT_DURATION." AS t_over,".
			"  unix_timestamp() > t.time AND unix_timestamp() < t.time+".TOURNAMENT_DURATION." AS now, ".
			"  count(if(tp.player=".$pid.",TRUE,NULL)) AS me,".
			"  res.name AS res_name, pr.research AS res_has ".
			" FROM tournament t LEFT JOIN tournament_players tp USING(tid)".
			"  LEFT JOIN player o ON o.id = t.organizer ".
			"  LEFT JOIN playerresearch pr ON pr.research = t.require_research AND pr.player = ".$pid.
			"  LEFT JOIN research res ON res.id = t.require_research".
			"  LEFT JOIN tournament_players p ON (p.tid = t.tid AND p.player = ".$pid.") ".
			" WHERE unix_timestamp() < t.time + 48*3600 ".
			" GROUP BY t.tid ORDER BY t_over DESC, $order");

$i=0;
$isover = true;

while($t = do_mysql_fetch_assoc($tourn)) {
  $starttime = $t['time'];
  $endtime   = $t['time']+TOURNAMENT_DURATION;

  $url= '<a href="'.$_SERVER['PHP_SELF'].'?tmagic='.$tournamentmagic.'&tid='.$t['tid'].'&part=%d">%s</a>';
  if($t['t_over']) {
    $action = "vorüber";
    if($_SESSION['player']->isMaintainer() && $t['calctime']) {
      $action .= ", berechnet";
    }
  }
  else {
    // Wenn die vergangenen Tourniere fertig sind, dann die aktuellen Turniere
    // anzeigen:
    if($isover) {
      $isover = false;
      echo '</table><hr><table cellpadding="0" cellspacing="0" width="600"><tr><td><h2>Zukünftige Turniere</h2></td></tr></table>';

      printTableHeader(1);
    }

    if($t['me'] > 0) {
      if($t['now']) {
        if($t['booktime'])
        $action = 'Ihr Kämpft!';
        else
        $action = sprintf($url, 2, TOURNAMENT_CONFIRM_TEXT);
      }
      else
      $action = sprintf($url, 0,'Rückzieher'); 
    }
    else {
      $action = sprintf($url, 1,'Anmelden');
    }
  } // else "over"

  printf("<tr class=\"col%d\"><td>%d</td><td nowrap>%s <b>%s</b></td><td style=\"padding: 0px;\">-</td><td nowrap>%s</td>".
	 "<td>%s</td><td>%s</td><td>%s / %d / %d</td><td>%s</td></tr>\n",
  $t['now'] ? 2 : ($t['t_over'] ? 3 : $i), $t['tid'],
  date("d.m.y", $starttime), date("H:i", $starttime), date("H:i", $endtime),
  $t['name'] ? $t['name'] : "Server",
  $t['gold'] > TOURNAMENT_MAX_GOLD ? "<b>".$t['gold']." !!!</b>" : $t['gold'],
  $t['count_book'] < $t['maxplayers']/2
  ? '<span style="color: red; font-weight: bold;" title="Bisher nehmen nicht genug Spieler am Turnier teil!">'.$t['count_book']."</span>"
  : $t['count_book'],
  $t['count'], $t['maxplayers'],
  $action
  );

  $i = ++$i%2;
}
?>
</table>
<p><span style="font-size: 16px;">Hier die Regeln <b>für Veranstalter</b>
in Kurzfassung:</span>
<ul style="width: 600px;">
	<li>Jeder Spieler kann <? echo TOURNAMENT_MAX_ORGANIZE;?> (<? echo TOURNAMENT_MAX_ORGANIZE_PREMIUM;?>,
	wenn er Premium-User ist) Turnier<? if(TOURNAMENT_MAX_ORGANIZE > 1) echo "e";?>
	veranstalten. Bereits absolvierte Turniere zählen natürlich nicht mit.
	
	
	
	<li>Der Veranstalter legt die Höhe des Preisgeldes fest. Diesen Betrag
	erhält der Veranstalter nicht mehr zurück.
	
	
	<li>Pro teilnehmendem Spieler erhält der Veranstalter <? echo TOURNAMENT_HOLD_BONUSPOINT; ?>
	Bonuspunkt<? if(TOURNAMENT_HOLD_BONUSPOINT > 1) echo "e"; ?>.
	
	
	<li>Der Veranstalter legt die maximale Anzahl an Teilnehmern fest. Das
	Turnier <b>findet nicht statt</b>, wenn weniger als die Hälfte dieser
	Zahl ihre Teilnahme <b>bestätigt</b> haben.
	
	
	<li>Die Mindestanzahl von <? echo TOURNAMENT_MIN_PLAYERS; ?>
	Teilnehmern gilt hier natürlich auch. Das bedeutet, der Veranstalter
	muss als Maximalzahl mindestens <? echo 2*TOURNAMENT_MIN_PLAYERS; ?>
	Teilnehmer eintragen.

</ul>

<a name="ownTournament"></a>
<h2>Turnier veranstalten</h2>
Hier könnt Ihr ein Turnier veranstalten.
<p><?
if(isset($newtournament)) {
  if($inform != null) {
    echo '<div class="inform">'.$inform."</div>\n";
    if(!is_premium_noads())
    include("includes/sponsorads-popdown.html");
  }
  if($error != null) {
    echo "<a name=\"mark\"></a>\n";
    echo '<div class="error"><b>Fehler: </b>'.$error."</div>\n";
  }
}
?>
<form method="get" action="<? echo $_SERVER['PHP_SELF'];  ?>">
<table>
	<tr>
		<td>Preisgeld:</td>
		<td><input name="gold" size="10" value="<? echo $gold; ?>"> Gold</td>
	</tr>
	<tr>
		<td>Teilnehmer:</td>
		<td><input name="maxp" size="10" value="<? echo $maxp; ?>"> maximal</td>
	</tr>
	<tr>
		<td>Start in :</td>
		<td><input size="4" name="stime" value="<? echo $stime; ?>"> Minuten</td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="newtournament"
			value="Turnier veranstalten"
			onClick="return confirm('Sind Sie sicher, dass Sie das Turnier veranstalten möchten?')">
		</td>
	</tr>
</table>
</form>


<?
if( $_SESSION['player']->isMaintainer() ) {
  echo "<h2>Maintainer Funktionen</h2>\n";
  echo '<a href="?calc=1">Tournierberechnung starten</a><p>';
}

if($error != null) {
  echo "<script language=\"javascript\">\n";
  echo " window.location.hash = \"mark\";\n</script>\n";
}

end_page();




function printTableHeader($nr) {
  ?>
<table cellspacing="0"
<? if($nr == 0) echo 'id="tournamentDone" style="display: none"'; ?>>
	<tr>
		<th width="10">Nr.</th>
		<th width="120" colspan="3"><a href="?sort=time">Zeitraum</a></th>
		<th width="80"><a href="?sort=player">Veranstalter</a></th>
		<th width="80"><a href="?sort=gold">Preisgeld</a></th>
		<th width="80"><a href="?sort=booknr">Angemeldet</a></th>
		<th width="130">Aktion</th>
	</tr>
	<?
}
?>