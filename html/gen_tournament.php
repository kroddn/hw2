<?
include("includes/util.inc.php");

if(sizeof($argv) > 2) {
  for($i=1; $i<sizeof($argv); $i++) {
    echo gen_tournament_sql_code($argv[$i]);
  }
}
else {
  if(sizeof($argv) == 2) {
    $current_day = $argv[1];
  }
  else {     
    $current_day = date("Y-m-d");
  }
  echo gen_tournament_sql_code($current_day);
}
?>