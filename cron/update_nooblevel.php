<?
/**
 * update_nooblevel.php
 *
 * Neulingsschutz nach x Wochen aktualisieren
 *
 * 23. Jun 2007 written by Markus Sinner <kroddn@holy-wars2.de>
 */
if(!file_exists("includes/db.inc.php")) {
  echo "Wechseln Sie vor ausführung in das HTML-Verzeichnis\n";
  echo "einer HW2-Installation!\n";
  die();
}

include_once("includes/db.inc.php");
include_once("includes/util.inc.php");

printf("\n===========================\n%s\n", date("d.m.y G:i:s", time()));


/** 
 * Runden-Start-Zeitpunkt auslesen
 *
 * Wird dazu verwendet, um Regtime richtig zu bewerten. 
 * Ist regtime nämlich früher als starttime, dann hat der
 * Spieler 
 */
$starttime = getRoundStartTime();
printf("Rundenstart: %s (%d)<br>\n", date("d.m.Y - H:i:s", $starttime), $starttime );


// Zeit 3 Wochen bzw. NOOB_TIME
$noobtime =  defined("NOOB_TIME") ? NOOB_TIME : 21 * 24*3600;

// Falls die Runde weniger lange als NOOB_TIME läuft, dann verliert
// man natürlich keinen Noobschutz.
if($starttime + $noobtime > time()) 
{
  printf("Runde läuft noch keine %d Tage (erst %d)\n", 
         intval( $noobtime/(24*3600) ), 
         intval( (time()-$starttime) / (24*3600) ));
  die();
}
else if($starttime > 0)
{
  printf("Runde läuft bereits %d Tage\n", 
         intval( (time()-$starttime) / (24*3600) ));
}

// Alle Spieler auswählen, die schon XX Tage spielen.
// activationtime wird gesetzt, wenn der Spieler seinen Namen und Startposition wählt
$sql = sprintf("SELECT * FROM player WHERE nooblevel<>0 AND activationtime + %d < UNIX_TIMESTAMP()", 
               $noobtime, $noobtime);
echo $sql."\n";
$players = do_mysql_query($sql);

while($p = mysql_fetch_assoc($players))
{
  printf("'%s' [%d] verliert Noobschutz\n", strlen($p['name']) > 0 ? $p['name'] : $p['login'], $p['id'] );
  do_mysql_query("UPDATE player SET nooblevel = 0, cc_messages = 1 WHERE nooblevel <> 0 AND id = ".$p['id']);

  $sql = sprintf("INSERT INTO message (recipient,date,header,body) ".
                 "VALUES (%d, UNIX_TIMESTAMP(),'%s','%s')",
                 $p['id'], "Neulingsschutz abgelaufen", 
                 "Euer Neulingsschutz ist abgelaufen.\n".
                 "Ihr seid nun der Gewalt anderer Spieler ausgesetzt.\n\n".
                 "Einige Tipps:\n".
                 "1. Sucht Euch einen Orden!\n".
                 "2. Schreibt Eure Nachbarn an und bittet um Frieden\n");
  do_mysql_query($sql);
}

?>