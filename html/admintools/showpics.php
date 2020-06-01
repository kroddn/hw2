<html><body><table>

<?php
echo "<!-- ".getcwd()." -->\n";
if (stristr(getcwd(), "admintools")) {
  chdir("..");
}

// Datenbankconnect
include_once("includes/config.inc.php");
include_once("includes/db.config.php");
include_once("includes/log.inc.php");
include_once("includes/db.inc.php");

$hit = $miss = 0;
$map = do_mysql_query("SELECT pic,type,count(*) AS cnt FROM map GROUP by pic ORDER BY type,pic");
while($pic = mysql_fetch_assoc($map)) {
  
  echo '<tr><td>';
  switch ($pic['type']) {
  case 1: echo "Wasser"; break;
  case 2: echo "Wiese"; break;
  case 3: echo "Wald"; break;
  case 4: echo "Berg"; break;
  }
  echo "</td>\n";
  if(!file_exists("images/ingame/".$pic['pic'].".gif")) {
    echo "<td>fehlt</td>\n";
    $miss++;
  }
  else {     
    echo '<td><img src="/images/ingame_v3/'.$pic['pic'].".gif\"></td>";
    $hit++;
  }
  echo "\n<td>".$pic['pic'].".gif</td>\n";
  echo "</tr>\n";   
}
echo "</table>";
echo "\n$hit vorhanden, $miss fehlen\n";


?>
</body></html>