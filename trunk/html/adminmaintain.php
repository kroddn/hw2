<?php
/***************************************************
* Copyright (c) 2003-2004 by holy-wars2.de
*
* written by
* Gordon Meiser
* Markus Sinner <kroddn@psitronic.de>
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/admin.inc.php");
?>

<html>
<head>
<title>Tool zur Wartung und Ausführung administrativer Aufgaben</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
if(!$player->isMaintainer()) {
  die ("<h1 class=\"error\">Ihr seid kein Maintainer!</h1></body></html>");
}

// Startpositionen finden, auf denen Städte stehen
if($ext_startpos) {
  $startpos = do_mysql_query("
    SELECT s.x AS sx,s.y AS sy,c.name AS cname,map.x AS cx, map.y AS cy FROM city AS c 
    LEFT JOIN map ON map.id=c.id
    LEFT JOIN startpositions AS s ON (
    (s.x - map.x <= 4 AND s.x - map.x >= -4) AND
    (s.y - map.y <= 4 AND s.y - map.y >= -4)
    )
    WHERE s.x IS NOT NULL"
  );

  echo "<h2>".mysql_numrows($startpos)." Konflikt-Positionen gefunden:</h2>\n";
  $tmp = "<h3>SQL Befehle:</h3>\n";
  while($S = mysql_fetch_assoc($startpos)) {
    echo "Startpos ".$S['sx'].":".$S['sy']." nahe ".$S['cname']." (".$S['cx'].":".$S['cy'].")<br>";
    $tmp .= "DELETE FROM startpositions WHERE x = ".$S['sx']." AND y = ".$S['sy'].";<br>\n";
  }
  echo $tmp;
  echo "<p><a href=\"?ext_startpos=1\">Erweitert</a><p><hr>\n";
  die ("</body></html");
}



echo 'Clicks: '.$click->count.', <a target="_new" href="'.MAGIC_SCRIPT.'?magic='.$ad->magic.'">Magic: '.$ad->magic."</a>, Banner-ID: ".$ad->banner_id.
' <a href="?bannerplus=1">++</a> <a href="all.php">all</a><hr>';


// Stadt löschen
if (isset($citydelete) && $player->isAdmin() && !$data2['owner']) {
  $city_test = do_mysql_query_fetch_array("SELECT city.name, player.id AS cityowner FROM city LEFT JOIN player ON player.id=city.owner WHERE city.id =".$citydelete);

  echo "<h2>Lösche Stadt ".$city_test['name']."</h2>\n";

  if ($city_test['cityowner']) {
    echo '<font color="red" size="+1">Städte mit Besitzern können nicht gelöscht werden!</font>';
  }
  else {
    removeCity($citydelete);
    echo "Stadt gelöscht";
  }
  echo "<hr>\n";
}

// Inaktiven Spieler löschen
if (isset($delete)) {
  echo "<h2>Lösche Spieler...</h2>\n";
    
  $player_test = do_mysql_query_fetch_array("SELECT * FROM player WHERE id =".$delete);

  // Nur Löschen wenn er aktuell gesperrt ist, aktiviert war und 7 Tage gesperrt war.
  // oder wenn ein nicht-aktivierter account mindestens 48 stunden alt ist
  $activated_locked  = $player_test['status'] != null && $player_test['activationkey'] == null && ($player_test['lastseen'] + 7*24*3600 < time());
  $not_activated_old = $player_test['activationkey'] != null && $player_test['status'] == 1 && ($player_test['lastseen'] + 2*24*3600) < time();

  if (  $activated_locked  || $not_activated_old ){
    removePlayer($delete);
    echo '<h2>Spieler gelöscht!</h2><a href="adminmaintain.php">Zurück zur Wartung</a>.';
    die ("</body></html");
  }
  else {
    echo '<font color="red" size="+1">Der Spieler ist nicht lange genug gesperrt und kann daher nur von einem Administrator gelöscht werden!</font>';
    die ("</body></html");
  }
  echo "<hr>\n";
}


$res_buildings = do_mysql_query("SELECT city,building FROM citybuilding LEFT JOIN city on citybuilding.city =  city.id WHERE city.id IS NULL");
$num_buildings = mysql_numrows($res_buildings);
// Gebäude ohne zugehörige Städte löschen
if (isset($deletebuildings)) {  
  while ($del = mysql_fetch_assoc($res_buildings)) {
    $sql =  "DELETE FROM citybuilding WHERE city = ".$del['city']." AND building=".$del['building'];
    echo $sql."<br>";
    do_mysql_query($sql);
  }

  echo "Gelöscht...";
  echo "<hr>\n";
}



// Neu angelegte Spieler anzeigen
echo "\n<p>\n";

// status = 3 bedeutet verdächtige Spieler, die aber nicht gesperrt sind.
$res = do_mysql_query("SELECT count(*),religion FROM player WHERE religion IS NOT NULL AND activationkey IS NULL AND (status IS NULL OR status=3) GROUP BY religion ORDER BY religion");

$nr[0] = mysql_fetch_array($res);
$nr[1] = mysql_fetch_array($res);
?>
Zur Zeit spielen <b><?php echo ($nr[0][0]+$nr[1][0]); ?></b> registrierte und aktivierte Spieler,
davon sind <b><? echo $nr[0][0];?> christliche </b> und <b><? echo $nr[1][0];?> islamische</b> Spieler.<br>
<?
$new_players = do_mysql_query_fetch_array("SELECT count(*) as cnt FROM player WHERE unix_timestamp()-regtime < 60*60*48");
$new_players_act = do_mysql_query_fetch_array("SELECT count(*) as cnt FROM player WHERE unix_timestamp()-regtime < 60*60*48 AND activationkey IS NULL");
echo "In den letzten 48 Stunden haben sich ".$new_players['cnt']." Spieler neu angemeldet. Davon sind ".$new_players_act['cnt'].
' auch aktiviert. <a href="?listnewplayers=1">Hier für eine Liste klicken</a>.<br>';
if (isset($listnewplayers)) {
  list_player_new();
}


// Gelöschte Spieler anzeigen
$new_players_del = do_mysql_query_fetch_array("SELECT count(*) as cnt FROM log_player_deleted WHERE unix_timestamp()-regtime < 60*60*48");
$new_players_sms = do_mysql_query_fetch_array("SELECT count(*) as cnt FROM player WHERE sms IS NOT NULL");

echo $new_players_del['cnt']." Spieler wurden gelöscht.<br>";
echo $new_players_sms['cnt']." Spieler haben SMS hinterlegt<p>";
echo "<p><hr>";


// Städte ohne Besitzer
$city_without_owner = do_mysql_query_fetch_array("SELECT count(*) AS cnt FROM city LEFT JOIN player ON player.id=city.owner WHERE player.id IS NULL");
echo 'Es gibt <b>'.$city_without_owner['cnt'].' Städte ohne Spieleraccount</b>. Manuelle Löschung notwendig.<p><hr>';


// Gebäude ohne Stadt (wird oben abgefragt)
echo 'Es gibt <b>'.$num_buildings.' Gebäude ohne zugehörige Stadt</b> (eigentlich müsste hier immer 0 stehen!!!). <a href="?deletebuildings=1">Diese JETZT löschen.</a><p>';
echo "<p><hr>";


$startpos = do_mysql_query("select s.x,s.y from startpositions s left join map using(x,y) left join city using(id) where city.name is  not null");
printf('Es gibt <b>%d Startpositionen</b>, auf denen aber schon Städte stehen! Falls hier irgendwann &gt;0 steht, bitte im Orga-Channel besprechen! <a href="?ext_startpos=1">Erweitert</a><p>', mysql_numrows($startpos));
printf('Siedlungsradius: %d', getSettleRadius());

echo "<p><hr>";



list_player_not_activated();


list_player_locked();

if($_SESSION['player']->IsAdmin()) {
  include_once("includes/reset.inc.php");

  if(isset($_GET['do_reset']) && isset($_GET['reset_magic'])) {
    do_reset($_GET['reset_magic'], $_GET['reset_map'] ? true : false  );
  }
  
  reset_game_form();
}

?>

</body></html>