<?php
$START_TIME=microtime();
/***************************************************
* Copyright (c) 2003-2004 by holy-wars.com
*
* written by Gordon Meiser
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/db.inc.php");
include_once("includes/player.class.php");
include_once("includes/session.inc.php");

function getBrowserStat_day($time) {
	$month=date("m",time());
	$day=date("d",time());
	$year=date("Y",time());
	$today = mktime(12, 0, 0, $month, $day, $year); 
	$res=do_mysqli_query("SELECT id FROM log_browser WHERE browser='".browser_detection( 'browser' )."' AND version='".browser_detection( 'number' )."' AND timestamp='".$today."'");
	if(mysqli_num_rows($res) > 0) {
		do_mysqli_query("UPDATE log_browser set logins=logins+1 WHERE browser='".browser_detection( 'browser' )."' AND version='".browser_detection( 'number' )."' AND timestamp='".$today."'");
	} else {
		do_mysqli_query("INSERT INTO log_browser (browser, version, timestamp, logins) VALUES ('".browser_detection( 'browser' )."','".browser_detection( 'number' )."','".$today."','1')") or die ("error");
	}
}
function today() {
  $month=date("m",time());
  $day=date("d",time());
  $year=date("Y",time());
  $today = mktime(12, 0, 0, $month, $day, $year); 
  return $today;
}
function prevDay($time) {
  $day=($time-((60*60)*24));
  return $day;
}
function nextDay($time) {
  $day=($time+((60*60)*24));
  return $day;
}
function prevMonth($time) {
  $day=($time-((60*60)*24)*30);
  return $month;
}
function nextMonth($time) {
  $day=($time+((60*60)*24)*30);
  return $month;
}
function getBrowserName($name) {
  if($name=="ie")
    $browser="Internet Explorer";
  elseif($name=="ns")
    $browser="Netscape Navigator";
  elseif($name=="moz")
    $browser="Mozilla / Firefox";
  elseif($name=="op")
    $browser="Opera";
  elseif($name=="konq")
    $browser="Konqueror";
  elseif($name=="saf")
    $browser="Safari";
  else
    $browser=$name;

  return $browser;
}
function getStatBar($total,$obj) {
  $result=round((($obj/$total)*100),0);
  return $result;
}
function getPercent($total,$obj) {
  $result=round((($obj/$total)*100),2);
  return $result." %";
}
function hasLog($time) {
  $res1=do_mysqli_query("SELECT id FROM log_browser WHERE timestamp='".$time."'");
  if(mysqli_num_rows($res1) > 0)
    return true;
  else
    return false;
}
?>
<html>
<head>
<title>Log</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?php
if($player->isAdmin()) {

  if($_POST['day'] && $_POST['year'] && $_POST['month']) {
    $timestamp=mktime(12,0,0,$month,$day,$year);
  } elseif($_GET['time']) {
    $timestamp=$_GET['time'];
  } else {
    $timestamp=today();
  }

  echo "<center>";
  echo "<form method=\"post\" action=\"".$PHP_SELF."\" style=\"padding:0px; margin:0px;\">\n";
  echo "<table width=\"500\">\n";
  echo "<tr><td class=\"tblhead\" colspan=\"13\" align=\"center\" height=\"30\" valign=\"middle\"><b>Browser Statistik</b></td></tr>";
  echo "<tr class=\"tblbody\"><td class=\"tblhead\"><b>Jahr:</b></td>";
  for($i=1;$i<=6;$i++) { echo "<td colspan=\"2\" align=\"center\"><a href=\"".$PHP_SELF."?view=year&year=".(2003+$i)."\">".(2003+$i)."</a></td>"; }
  echo "</tr>";
  echo "<tr class=\"tblbody\"><td class=\"tblhead\"><b>Monat:</b></td>";
  for($i=1;$i<=12;$i++) {
    if($_GET['year']) {$year=$_GET['year']; } else { $year=date("Y",time()); }
    echo "<td align=\"center\"><a href=\"".$PHP_SELF."?view=month&year=".$year."&month=".$i."\">".$i."</a></td>"; 
  }
  echo "</tr>";
  echo "</table>";
  echo "<table width=\"500\">\n";
  echo "<tr>";
  echo "<td class=\"tblhead\"><b>Datum w&auml;hlen</b></td>";
  echo "<td colspan=\"2\" class=\"tblbody\">";
  echo "<label for=\"day\">Tag:&nbsp;</label><input type=\"text\" maxlength=\"2\" id=\"day\" name=\"day\" size=\"2\" />";
  echo "<label for=\"month\">&nbsp;&nbsp;&nbsp;Monat:&nbsp;</label><select id=\"month\" name=\"month\">";
  for($i=1;$i<=12;$i++) {echo "<option value=\"".$i."\">".$i."</option>"; }
  echo "</select>";
  echo "<label for=\"year\">&nbsp;&nbsp;&nbsp;Jahr:&nbsp;</label><input type=\"text\" maxlength=\"4\" id=\"year\" name=\"year\" size=\"4\" />";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Zu Datum wechseln\" />";
  echo "</td>";
  echo "</td></tr>";
  echo "</table>";
  echo "</form>";

//month
  if($_GET['view']=="month") {

    if($_GET['year'] && $_GET['month']) {
      $start=mktime(12,0,0,$_GET['month'],1,$_GET['year']);
      $hasDays=date("t",$start);
      $stop=mktime(12,0,0,$_GET['month'],$hasDays,$_GET['year']);
      $timestamp=$start;
    } elseif($_GET['str']) {
      $month=date("m",$_GET['str']);
      $year=date("Y",$_GET['str']);
      $start=mktime(12,0,0,$month,1,$year);
      $hasDays=date("t",$start);
      $stop=mktime(12,0,0,$month,$hasDays,$year);
      $timestamp=$start;
    } else {
      $month=date("m",time());
      $year=date("Y",time());
      $start=mktime(12,0,0,$month,1,$year);
      $hasDays=date("t",$start);
      $stop=mktime(12,0,0,$month,$hasDays,$year);
      $timestamp=$start;
    }

    echo "<table width=\"500\">\n";
    echo "<tr class=\"tblhead\" height=\"30\">";
    echo "<td><a href=\"".$PHP_SELF."?view=month&str=".($timestamp-((60*60)*24)*30)."\">Voriger Monat</a></td>";
    echo "<td align=\"center\"><b>";
    echo date("F",$timestamp)." ".date("Y",$timestamp);
    echo "</b></td>";
    echo "<td align=\"right\"><a href=\"".$PHP_SELF."?view=month&str=".($timestamp+((60*60)*24)*32)."\">N&auml;chster Monat</a></td>";
    echo "</tr>";
    echo "<tr class=\"tblbody\">";
    echo "<td colspan=\"3\" align=\"center\">";

    unset($total);
    //detailed month
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    $res1=do_mysqli_query("SELECT logins, browser, version FROM log_browser WHERE timestamp>='".$start."' AND timestamp <='".$stop."' ORDER BY logins DESC");
    $res2=do_mysqli_query("SELECT logins, browser, version FROM log_browser WHERE timestamp>='".$start."' AND timestamp <='".$stop."' ORDER BY logins DESC");
    while($data1=mysqli_fetch_assoc($res1)) { $total+=$data1['logins']; }
    echo "<tr class=\"tblhead\"><td colspan=\"4\"><b>Detaillierte Monats&uuml;bersicht</b></td></tr>";
    while($data2=mysqli_fetch_assoc($res2)) {
      echo "<tr>";
      echo "<td width=\"100\">".getBrowserName($data2['browser'])."</td>";
      echo "<td width=\"20\">".$data2['version']."</td>";
      echo "<td width=\"125\"><div style=\"width:".getStatBar($total,$data2['logins'])."%; background-color:navy; height:7px;\"> </div></td>";
      echo "<td width=\"105\" align=\"right\">".$data2['logins']." [".getPercent($total,$data2['logins'])."]</td>";
      echo "</tr>";
    }
    echo "<tr><td align=\"right\" colspan=\"4\" style=\"border-top:1px dashed black;\"><b>".$total."</b> [100%]</td></tr>";
    echo "</table>\n";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
  
  /*
   else {
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    echo "<tr class=\"tblhead\" height=\"40\"><td colspan=\"4\" valign=\"middle\" align=\"center\"><b>Keine Daten gefunden!</b></td></tr>";
    echo "</table><br />";
  }*/
  echo "<br /></td></tr></table>";

} elseif($_GET['view']=="year") {
//year

    if($_GET['year']) {
      $start=mktime(12,0,0,1,1,$_GET['year']);
      $stop=mktime(12,0,0,12,31,$_GET['year']);
      $timestamp=$start;
    } else {
      $year=date("Y",time());
      $start=mktime(12,0,0,1,1,$year);
      $stop=mktime(12,0,0,12,31,$year);
      $timestamp=$start;
    }

    echo "<table width=\"500\">\n";
    echo "<tr class=\"tblhead\" height=\"30\">";
    echo "<td><a href=\"".$PHP_SELF."?view=month&str=".($timestamp-((60*60)*24)*30)."\">Voriger Monat</a></td>";
    echo "<td align=\"center\"><b>";
    echo date("F",$timestamp)." ".date("Y",$timestamp);
    echo "</b></td>";
    echo "<td align=\"right\"><a href=\"".$PHP_SELF."?view=month&str=".($timestamp+((60*60)*24)*32)."\">N&auml;chster Monat</a></td>";
    echo "</tr>";
    echo "<tr class=\"tblbody\">";
    echo "<td colspan=\"3\" align=\"center\">";

    //grouped year
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    $res1=do_mysqli_query("SELECT sum(logins) as logins, browser, version FROM log_browser WHERE timestamp>='".$start."' AND timestamp <='".$stop."' GROUP BY browser ORDER BY logins DESC");
    $res2=do_mysqli_query("SELECT sum(logins) as logins, browser, version FROM log_browser WHERE timestamp>='".$start."' AND timestamp <='".$stop."' GROUP BY browser ORDER BY logins DESC");
    while($data1=mysqli_fetch_assoc($res1)) { $total+=$data1['logins']; }
    echo "<tr class=\"tblhead\"><td colspan=\"3\"><b>Gruppierte Jahres&uuml;bersicht</b></td></tr>";
    while($data2=mysqli_fetch_assoc($res2)) {
      echo "<tr>";
      echo "<td width=\"100\">".getBrowserName($data2['browser'])."</td>";
      echo "<td width=\"170\"><div style=\"width:".getStatBar($total,$data2['logins'])."%; background-color:navy; height:7px;\"> </div></td>";
      echo "<td width=\"80\" align=\"right\">".$data2['logins']." [".getPercent($total,$data2['logins'])."]</td>";
      echo "</tr>";
    }
    echo "<tr><td align=\"right\" colspan=\"3\" style=\"border-top:1px dashed black;\"><b>".$total."</b> [100%]</td></tr>";
    echo "</table>\n";

    unset($total);
    //detailed year
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    $res1=do_mysqli_query("SELECT logins, browser, version FROM log_browser WHERE timestamp>='".$start."' AND timestamp <='".$stop."' ORDER BY logins DESC");
    $res2=do_mysqli_query("SELECT logins, browser, version FROM log_browser WHERE timestamp>='".$start."' AND timestamp <='".$stop."' ORDER BY logins DESC");
    while($data1=mysqli_fetch_assoc($res1)) { $total+=$data1['logins']; }
    echo "<tr class=\"tblhead\"><td colspan=\"4\"><b>Detaillierte Jahres&uuml;bersicht</b></td></tr>";
    while($data2=mysqli_fetch_assoc($res2)) {
      echo "<tr>";
      echo "<td width=\"100\">".getBrowserName($data2['browser'])."</td>";
      echo "<td width=\"20\">".$data2['version']."</td>";
      echo "<td width=\"125\"><div style=\"width:".getStatBar($total,$data2['logins'])."%; background-color:navy; height:7px;\"> </div></td>";
      echo "<td width=\"105\" align=\"right\">".$data2['logins']." [".getPercent($total,$data2['logins'])."]</td>";
      echo "</tr>";
    }
    echo "<tr><td align=\"right\" colspan=\"4\" style=\"border-top:1px dashed black;\"><b>".$total."</b> [100%]</td></tr>";
    echo "</table>\n";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "<br /></td></tr></table>";

} else {

  unset($total);
  echo "<table width=\"500\">\n";
  echo "<tr class=\"tblhead\" height=\"30\">";
  echo "<td><a href=\"".$PHP_SELF."?time=".prevDay($timestamp)."\">Voriger Tag</a></td>";
  echo "<td align=\"center\"><b>";
  echo date("d.m.Y",$timestamp);
  echo "</b></td>";
  echo "<td align=\"right\"><a href=\"".$PHP_SELF."?time=".nextDay($timestamp)."\">N&auml;chster Tag</a></td>";
  echo "</tr>";
  echo "<tr class=\"tblbody\">";
  echo "<td colspan=\"3\" align=\"center\">";
  if(hasLog($timestamp)) {
    //grouped day
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    $res1=do_mysqli_query("SELECT sum(logins) as logins, browser, version FROM log_browser WHERE timestamp='".$timestamp."' GROUP BY browser ORDER BY logins DESC");
    $res2=do_mysqli_query("SELECT sum(logins) as logins, browser, version FROM log_browser WHERE timestamp='".$timestamp."' GROUP BY browser ORDER BY logins DESC");
    while($data1=mysqli_fetch_assoc($res1)) { $total+=$data1['logins']; }
    echo "<tr class=\"tblhead\"><td colspan=\"3\"><b>Gruppierte Tages&uuml;bersicht</b></td></tr>";
    while($data2=mysqli_fetch_assoc($res2)) {
      echo "<tr>";
      echo "<td width=\"100\">".getBrowserName($data2['browser'])."</td>";
      echo "<td width=\"170\"><div style=\"width:".getStatBar($total,$data2['logins'])."%; background-color:navy; height:7px;\"> </div></td>";
      echo "<td width=\"80\" align=\"right\">".$data2['logins']." [".getPercent($total,$data2['logins'])."]</td>";
      echo "</tr>";
    }
    echo "<tr><td align=\"right\" colspan=\"3\" style=\"border-top:1px dashed black;\"><b>".$total."</b> [100%]</td></tr>";
    echo "</table>\n";

    unset($total);
    //detailed day
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    $res1=do_mysqli_query("SELECT logins, browser, version FROM log_browser WHERE timestamp='".$timestamp."' ORDER BY logins DESC");
    $res2=do_mysqli_query("SELECT logins, browser, version FROM log_browser WHERE timestamp='".$timestamp."' ORDER BY logins DESC");
    while($data1=mysqli_fetch_assoc($res1)) { $total+=$data1['logins']; }
    echo "<tr class=\"tblhead\"><td colspan=\"4\"><b>Detaillierte Tages&uuml;bersicht</b></td></tr>";
    while($data2=mysqli_fetch_assoc($res2)) {
      echo "<tr>";
      echo "<td width=\"100\">".getBrowserName($data2['browser'])."</td>";
      echo "<td width=\"20\">".$data2['version']."</td>";
      echo "<td width=\"125\"><div style=\"width:".getStatBar($total,$data2['logins'])."%; background-color:navy; height:7px;\"> </div></td>";
      echo "<td width=\"105\" align=\"right\">".$data2['logins']." [".getPercent($total,$data2['logins'])."]</td>";
      echo "</tr>";
    }
    echo "<tr><td align=\"right\" colspan=\"4\" style=\"border-top:1px dashed black;\"><b>".$total."</b> [100%]</td></tr>";
    echo "</table>\n";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
  } else {
    echo "<table width=\"350\" style=\"margin-top:20px;\">\n";
    echo "<tr class=\"tblhead\" height=\"40\"><td colspan=\"4\" valign=\"middle\" align=\"center\"><b>Keine Daten gefunden!</b></td></tr>";
    echo "</table><br />";
  }
  }
}
?>
</center>
</body>
</html>
<?
// Insert Into DB the execution time of this script
list($START_MICRO, $START_SEC) = explode(" ",$START_TIME);
list($END_MICRO, $END_SEC) = explode(" ",microtime());
do_mysqli_query("INSERT INTO log_cputime (file,start,time) VALUES ('".__FILE__."', ".$START_SEC.",".round(($END_MICRO+$END_SEC-$START_MICRO-$START_SEC)*1000).")");
?>