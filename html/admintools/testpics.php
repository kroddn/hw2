<?php
$run_path = getenv("PWD");

if (stristr($run_path, "admintools")) {
  echo $run_path;
  echo "\nStart aus dem falschen Verzeichnis heraus.\nAus html/ starten!\n";
  die();
}
// Datenbankconnect
include("includes/db.inc.php");

$hit = $miss = 0;
$map = do_mysql_query("SELECT DISTINCT pic FROM map ORDER BY pic");
while($pic = mysql_fetch_assoc($map)) {
  if(!file_exists("images/ingame/".$pic['pic'].".gif")) {
    echo $pic['pic'].".gif\n";
    $miss++;
  }
  else {
    $hit++;
  }
}

echo "\n$hit vorhanden, $miss fehlen\n";
?>
