<?php
/***************************************************
* Copyright (c) 2003-2004 by holy-wars2.de
*
* written by 
* Gordon Meiser
*
* This File must not be used without permission	!
***************************************************/

include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/player.class.php");
include_once("includes/util.inc.php");
?>
<html>
<head>
<title>Premium-Account Menu</title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<?
if (!$player->isPremSeller()) {
  header("Status: 404 Not Found");
  exit;
}
?>
<table width="600" cellspacing="0" cellpadding="0" border="0">
 <tr>
  <td colspan="6" align="center">
	&nbsp;
  </td>
 </tr>
 <tr>
  <td colspan="6" align="center">
	<h3>Premium-Account Menu</h3>
  </td>
 </tr>
 <tr>
  <td colspan="6" align="center">
	&nbsp;
  </td>
 </tr>
 <tr align="center">
  <td align="center">
      <form action="premiumadmin.php" method="POST">
      <input type="hidden" name="sh_premiuminfo" value="1">
      <input type="submit" value="Aktive Premium-Accounts anzeigen">
      </form>
  </td>
  <td align="center">
      <form action="premiumadmin.php" method="POST">
      <input type="hidden" name="sh_orderedpremiumaccs" value="1">
      <input type="submit" value="Premium-Account-Aufträge anzeigen">
      </form>
  </td>
  <td align="center">
      <form action="premiumadmin.php" method="POST">
      <input type="hidden" name="sh_useraddpremiumacc" value="1">
      <input type="submit" value="Premium-Account anlegen (User)">
      </form>
  </td>
 </tr>
 <tr>
  <td colspan="3"><hr></td>
 </tr>
</table>
<?php

if (isset($delete_orderedpremiumacc)) {
  $del_orderedpremacc = do_mysql_query("DELETE FROM premiumacc where id=".$delete_orderedpremiumacc);
  echo "Premium-Account-Auftrag des Spielers mit der PAcc-ID: ".$delete_orderedpremiumacc." erfolgreich gelöscht!";
}

if(isset($edit_orderedpremiumacc))
   {
     $p = do_mysql_query_fetch_assoc("SELECT player FROM premiumacc WHERE id = ".$edit_orderedpremiumacc);
     $maxexp = get_max_expire($p['player']);
     
     $prem_duration_out = get_duration($prem_duration);
     $prem_payd = get_cost($prem_type, $prem_duration);
     
     // Falls $maxexp gültig ist, dann wird dieses mitbenutzt
     $prem_expire = max($maxexp, time()) + ($prem_duration_out * 24 * 60 * 60);
     $prem_start  = max($maxexp, time());
     $prem_paydtime = time();
     $edit_premacc_mysql = do_mysql_query("UPDATE premiumacc set expire = ".$prem_expire.", start = ".$prem_start.", paydtime = ".$prem_paydtime.", payd = ".$prem_payd.", type = ".abs($prem_type)." where id = ".$edit_orderedpremiumacc."");
    
     echo "Premium-Account-Auftrag des Spielers mit der ID: ".$edit_orderedpremiumacc." erfolgreich aktiviert!<br>";
    
     $pid = do_mysql_query_fetch_assoc("SELECT player FROM premiumacc WHERE id = $edit_orderedpremiumacc");

     // Nachricht an Spieler
     $sql = sprintf("INSERT INTO message (recipient,date,header,body) ".
		    "VALUES (%d, UNIX_TIMESTAMP(), '%s', '%s')",
		    $pid['player'],
		    "Premium-Account aktiviert",
		    "Ihr Premium-Account wurde soeben aktiviert. Bitte loggen Sie sich neu ein!\n\nFalls Sie [b]NoADS[/b] inklusive haben, dann können Sie im Menü MyHW2 unter der Rubrik Grafik/Menü die Werbebanner dauerhaft abschalten.\n\n[b]Wir bedanken uns für Ihre Unterstützung![/b] Ihre Zahlung leistet einen nicht unerheblichen Anteil an der Finanzierung des Projektes Holy-Wars 2.");
     do_mysql_query($sql);
     do_mysql_query("UPDATE player SET cc_messages=1 WHERE id = ".$pid['player']);
   }

   
function get_max_expire($player) {
  $res = do_mysql_query("SELECT max(expire) AS maxexp FROM premiumacc ".
						 "WHERE player = ".$player);
  $maxres = mysql_fetch_assoc($res);
  if($maxres && $maxres['maxexp'] > 0)
    $maxexp = $maxres['maxexp'];
  else
    $maxexp = 0;

  return $maxexp;
}
   
function get_duration($prem_duration) {
   $prem_duration_out = null;
   
   switch ($prem_duration)
     {
     case "1":
       $prem_duration_out = 31;
       break;
     case "3":
       $prem_duration_out = 92;
       break;
     case "6":
       $prem_duration_out = 184;
       break;
     case "12":
       $prem_duration_out = 366;
       break;
     case "24":
       $prem_duration_out = 732;
       break;
     } // switch
   return $prem_duration_out;
 }


function get_cost($prem_type, $prem_duration) {
   $prem_payd = null;
   switch ($prem_type)
     {
     case "-1":
       switch ($prem_duration)
	 {
	 case "6":
	   $prem_payd = 800;
	   break;
	 case "12":
	   $prem_payd = 1500;
	   break;
	 } // switch
       break;
     case "-3":
       switch ($prem_duration)
	 {
	 case "1":
	   $prem_payd = 299;
	   break;
	 case "3":
	   $prem_payd = 600;
	   break;
	 case "6":
	   $prem_payd = 1100;
	   break;
	 case "12":
	   $prem_payd = 2000;
	   break;
	 } // switch
       break;
     case "-15":
       switch ($prem_duration)
	 {
	 case "1":
	   $prem_payd = 399;
         break;
	 case "3":
	   $prem_payd = 1000;
	   break;
	 case "6":
	   $prem_payd = 1900;
	   break;
	 case "12":
	   $prem_payd = 3500;
	   break;
	 } // switch
       break;
     case "-31":
       switch ($prem_duration)
	 {
	 case "12":
	   $prem_payd = 5000;
	   break;
	 case "24":
	   $prem_payd = 9000;
	   break;
	 } // switch
       break;
     } // switch
   return $prem_payd;
 }

function compute_date($timedate) {
	return date("d.m.Y H:i:s", $timedate);
}

function show_useraddpremiumacc() {
?>
	<table>
	<form method='POST' action='premiumadmin.php'>
	<tr><td>Spieler-ID:</td><td><input type='text' name='prem_playerid'></td></tr>
	<tr><td>Account Typ:</td><td><select name='prem_type'>
	<option value='-1'>Premium NoAd</option>
	<option selected value='-3'>Premium Lite</option>
	<!-- <option value='-7'>Premium Medium</option> -->
	<option value='-15'>Premium Pro</option>
	<option value='-31'>Premium Ultra</option>
	</select></td></tr>
	<tr><td>Laufzeit:</td><td><select name='prem_duration'>
	<option selected value='1'>1 Monat</option>
	<option value='3'>3 Monate</option>
	<option value='6'>6 Monate</option>
	<option value='12'>12 Monate</option>
	<option value='24'>24 Monate</option>
	</select></td></tr>
	<input type="hidden" name="useraddpremiumacc" value="1">
	<tr><td><input type='submit' name='create_userpremiumacc' value='Create Premium-Account'></td></tr>
	</form>
	</table>
	
	<?
}

function show_premiuminfo() {
  $get_premaccs = do_mysql_query("SELECT id,player,type,start,expire,payd,paydtime,paytext FROM premiumacc WHERE expire>0 ORDER BY id");
  if (mysql_num_rows($get_premaccs)) {
    echo "&nbsp;&nbsp;Aktuelle(s) Zeit/Datum: ".date("d.m.Y H:i:s", time())."<br>";
    echo "<br>\n<table>";
    echo '<tr><td colspan="7" class="tblhead"><b>Premium-Accounts</b></td></tr>';
    echo '<tr class="tblhead">';
    echo '<th>ID</th>';
    echo '<th>Spieler</th>';
    echo '<th>Typ</th>';
    echo '<th>startet</th>';
    echo '<th>endet</th>';
    echo '<th>Preis<br>Cent</th>';
    echo '<th>Zeitpunkt d.<br>Bezahlung</th>';
    echo '<th>Beschreibung</th>';
    echo '</tr>';
      
    while ($premaccs = mysql_fetch_assoc($get_premaccs)) {
      $get_playername = do_mysql_query("SELECT name FROM player WHERE id=".$premaccs['player']);
      $playername = mysql_fetch_assoc($get_playername);
      echo "<tr class='tblbody'>";
      echo "  <td>".$premaccs['id']."</td>\n";
      echo "  <td>[".$premaccs['player']."] ".$playername['name']."</td>\n";
      echo "  <td align='right'>".$premaccs['type']."</td>\n";
      echo "  <td><font style='";
      if( time() < max($premaccs['paydtime'],  $premaccs['start']) )
        echo "color: red; font-weight: bold;'>";
      else
        echo "color: darkgreen;'>";
      echo compute_date(max($premaccs['paydtime'],  $premaccs['start']) )."</font></td>\n";
      
      echo "  <td><font style='";
      if( time() > $premaccs['expire'])
        echo "color: red; font-weight: bold;'>";
      else
        echo "color: darkgreen;'>";
      echo compute_date($premaccs['expire'])."</font></td>\n";
      echo "  <td align='right'>".$premaccs['payd']."</td>\n";
      echo "  <td>".compute_date($premaccs['paydtime'])."</td>\n";
      echo "  <td>".$premaccs['paytext']."</td>\n";
      echo "</tr>";
    }
    echo "</table>\n<br><br>";
  }
}

function show_orderedpremiumaccs() {
  $get_premaccs = do_mysql_query("SELECT id,player,type,expire,payd,paydtime,paytext FROM premiumacc where expire=0");
  if (mysql_num_rows($get_premaccs)) {
    echo "&nbsp;&nbsp;Aktuelle(s) Zeit/Datum: ".date("d.m.Y H:i:s", time())."<br>";
    echo "<br>\n<table>";
    echo '<tr><td colspan="9" class="tblhead"><b>Premium-Accounts</b></td></tr>';
    echo '<td class="tblhead" colspan=2></td>';
    echo '<td class="tblhead"><b>ID</b></td>';
    echo '<td class="tblhead"><b>Spieler</b></td>';
    echo '<td class="tblhead"><b>Account-Typ</b></td>';
    echo '<td class="tblhead"><b>Account endet</b></td>';
    echo '<td class="tblhead"><b>Dauer (in Monaten)</b></td>';
    echo '<td class="tblhead"><b>Zeitpunkt der Auftragstellung</b></td>';
    echo '<td class="tblhead"><b>Beschreibung</b></td>';
    echo '</tr>';
    while ($premaccs = mysql_fetch_assoc($get_premaccs)) {
      $get_playername = do_mysql_query("SELECT name FROM player WHERE id=".$premaccs['player']);
      $playername = mysql_fetch_assoc($get_playername);
      echo "<tr>";
      echo "<td class='tblbody' width=\"16\"><a href=\"".$PHP_SELF."?delete_orderedpremiumacc=".$premaccs['id']."\"><img src=\"".GFX_PATH_LOCAL."/ps_del.png\" border=\"0\" alt=\"L&ouml;schen\"></a></td>\n";
      echo "<td class='tblbody' width=\"16\"><a href=\"".$PHP_SELF."?edit_orderedpremiumacc=".$premaccs['id']."&prem_duration=".$premaccs['payd']."&prem_type=".$premaccs['type']."\"><img src=\"".GFX_PATH_LOCAL."/ps_fixed.png\" border=\"0\" alt=\"Aktivieren\"></a></td>\n";
      echo "<td class='tblbody'>".$premaccs['id']."</td>";
      echo "<td class='tblbody'>[".$premaccs['player']."] ".$playername['name']."</td>";
      echo "<td class='tblbody'>".$premaccs['type']."</td>";
      echo "<td class='tblbody'>".compute_date($premaccs['expire'])."</td>";
      echo "<td class='tblbody'>".$premaccs['payd']."</td>";
      echo "<td class='tblbody'>".compute_date($premaccs['paydtime'])."</td>";
      echo "<td class='tblbody'>".$premaccs['paytext']."</td>";
      echo "</tr>";
    }
    echo "</table>\n<br><br>";
  }
}


function get_prem_name($prem_type) {
  switch ($prem_type)
	{
    case -1:
    case 1:
	  return "Premium NoAds";
    case -3:
    case 3:
      return "Premium Lite";
    case -7:
    case 7:
	  return "Premium Medium";
    case -15:
    case 15:
	  return "Premium Pro";
    case -31:
    case 31:
	  return "Premium Ultra";
    default:
      return null;
	}
}

if ($sh_premiuminfo==1) show_premiuminfo();
if ($sh_orderedpremiumaccs==1) show_orderedpremiumaccs();
if ($sh_useraddpremiumacc==1) show_useraddpremiumacc();

if ($useraddpremiumacc==1) {
  $get_playername = do_mysql_query("SELECT name FROM player WHERE id=".$prem_playerid);
  $playername = mysql_fetch_assoc($get_playername);
  $prem_paydtime = time();
  echo "<table>";
  echo "<tr><td colspan=2><b><u>Bestätigung der Eingabe</u></b></td></tr>";
  echo "<tr><td><b>Spielername:</b></td><td>".$playername['name']."</td></tr>";
  echo "<tr><td><b>Spieler-ID:</b></td><td>".$prem_playerid."</td></tr>";
  
  $prem_type_output = get_prem_name($prem_type);
  
  if ($confirm_useraddpremiumacc == 1) {
    $add_userpremacc_mysql = do_mysql_query("INSERT INTO premiumacc (player,type,expire,payd,paydtime,paytext) VALUES (".$prem_playerid.",".$prem_type.",0,".$prem_duration.",".$prem_paydtime.",'".$prem_playername." bestellt ".$prem_type_output."-Account - Dauer: ".$prem_duration." Monate')");
    echo "Premium-Account-Auftrag wurde <b>erfolgreich</b> an das HW2-Admin Team weitergeleitet!";
    $added = true;
  }
  else {
    $added = false;
    echo "Warte auf <b>Bestätigung</b>.";
  }
  $cost = get_cost($prem_type, $prem_duration);

  if($cost != null) {
    echo "<tr><td><b>Art des Premium-Accounts:</b></td><td>".$prem_type_output."</td></tr>";
    echo "<tr><td><b>Dauer:</b></td><td>".$prem_duration." Monate</td></tr>";
    echo "<tr><td><b>Auftragszeitpunkt:</b></td><td>".compute_date($prem_paydtime)."</td></tr>";

    $maxexp = get_max_expire($prem_playerid);
    $exp = max(time(), $maxexp) + (get_duration($prem_duration) * 24 * 60 * 60);
    echo "<tr><td valign='top'><b>Läuft ab:</b></td><td>".compute_date($exp);
    if($maxexp > 0) {
      echo "<br><b>Ein anderer Account läuft bis ".compute_date($maxexp)."</b>";
    }
	echo "</td></tr>";
    
	echo "<tr><td><b>Kosten:</b></td><td>".$cost."</td></tr>";
    
    echo "<tr><td><form action=\"premiumadmin.php\" method=\"POST\">";
    echo "<input type=\"hidden\" name=\"useraddpremiumacc\" value=\"1\">";
    echo "<input type=\"hidden\" name=\"confirm_useraddpremiumacc\" value=\"1\">";    
    echo "<input type=\"hidden\" name=\"prem_playerid\" value=\"".$prem_playerid."\">";
    echo "<input type=\"hidden\" name=\"prem_paydtime\" value=\"".$prem_paydtime."\">";
    echo "<input type=\"hidden\" name=\"prem_playername\" value=\"".$playername['name']."\">";
    echo "<input type=\"hidden\" name=\"prem_type\" value=\"".$prem_type."\">";
    echo "<input type=\"hidden\" name=\"prem_type_output\" value=\"".$prem_type_output."\">";
    echo "<input type=\"hidden\" name=\"prem_duration\" value=\"".$prem_duration."\">";
    if (!$added)
      echo "<input type=\"submit\" value=\"bestätigen\">";
    
    echo "</form></td>";
  }
  else {
    echo "<h2 class=\"error\">Diese Kombination existiert nicht.</h2>";
  }

  echo "<td><form action=\"premiumadmin.php\" method=\"POST\">";
  echo "<input type=\"hidden\" name=\"sh_useraddpremiumacc\" value=\"1\">";
  echo "<input type=\"submit\" value=\"zurück\">";
  echo "</form></td></tr>";
  echo "<tr><td colspan=2><font size=-2>Hinweise: Die Freischaltung des Premium-Accounts erfolgt erst nach Geldeingang!</font></td></tr>";
}
?>
</body>
</html>


