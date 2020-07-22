<?php

include_once("includes/db.inc.php");
include_once("includes/statistic.inc.php");
include_once("includes/diplomacy.common.php");
include_once("includes/session.inc.php");
include_once("includes/multi.inc.php");

start_page();
start_body(false);

if($_SESSION['player']->isMultihunter()) {
?>
<table width="600" cellspacing="0" cellpadding="0" border="0">
 <tr>
  <td colspan="7" align="center">
	&nbsp;
  </td>
 </tr>
 <tr>
  <td colspan="7" align="center">
	<h3>Multihuntermenu</h3>
  </td>
 </tr>
 <tr>
  <td colspan="7" align="center">
	&nbsp;
  </td>
 </tr>
 <tr align="center">
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_playerinfo" value="1">
      <input type="submit" value="Spielerinfo">
      </form>
  </td>
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_blockplayer" value="1">
      <input type="submit" value="Spieler sperren">
      <? 
      if(isset($showid)) {
        $blockname = do_mysql_query_fetch_array("SELECT name FROM player WHERE id = ".$showid);
        if($blockname && $blockname['name'])
        echo '<input type="hidden" name="blockname" value="'.$blockname['name'].'">';
      }
      ?>
      </form>
  </td>
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_define_suspicion" value="1">
      <input type="submit" value="Verdacht eintragen">
      <? 
      if(isset($showid)) {
        $blockname = do_mysql_query_fetch_array("SELECT name FROM player WHERE id = ".$showid);
        if($blockname && $blockname['name'])
        echo '<input type="hidden" name="blockname" value="'.$blockname['name'].'">';
      }
      ?>   
      </form>
  </td>
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_double" value="1">
      <input type="submit" value="Verdächtige anzeigen">
      </form>
  </td>
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_exceptions" value="1">
      <input type="submit" value="Exceptions">
      </form>
  </td>
  <?
  if ($player->isAdmin()) {
  ?>
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_delplayer" value="1">
      <input type="submit" value="Spieler löschen">
      </form>
  </td>
  <?
  }
  ?>
  <?
  if ($player->isAdmin() || $player->isMaintainer()) {
  ?>
  <td align="center">
      <form action="multihunter.php" method="POST">
      <input type="hidden" name="sh_player_locked" value="1">
      <input type="submit" value="Gesperrte Spieler anzeigen">
      </form>
  </td>
  <?
  }
  ?>
 </tr>
 <tr>
  <td colspan="7"><hr></td>
 </tr>
</table>
<?
if ($sh_playerinfo==1) show_playerinfo();
if ($sh_blockplayer==1) show_blockplayer();
if ($sh_define_suspicion==1) show_define_suspicion();
if ($sh_double==1) show_double();
if ($sh_delplayer==1) show_delplayer();
if ($sh_exceptions==1) show_mh_exceptions('multihunter.php');
if ($sh_player_locked==1) show_player_locked();
}


if (!$player->isMultihunter()) {
  header("Status: 404 Not Found");
  exit;
}

if ($player->isMultihunter()) {
  if (isset($name)) {
    if (!checkBez($name, 2, 40))
      show_fatal_error("Ungültiger Spielername: ".$name);

    $res = do_mysql_query("SELECT id FROM player WHERE name like '".$name."'");
    if ($data = mysqli_fetch_assoc($res))
      $id = $data['id'];
  }

  if (isset($id) && isset($type) && isset($lock)) {
    $control_res = do_mysql_query("SELECT name FROM player WHERE id=".$id);
    if ($control = mysqli_fetch_assoc($control_res)) {
      $statusdescr = getStatusdescr($type, $reason);
      if ($statusdescr) {
        do_mysql_query("INSERT INTO log_lock(id,reason,admin,time) VALUES (".$id.",'".do_mysql_escape_string($statusdescr)."',".$player->getID().",UNIX_TIMESTAMP() )");
        do_mysql_query("UPDATE player SET status=2,statusdescription='".do_mysql_escape_string($statusdescr)."',clan=NULL,clanstatus=0 WHERE id=".$id);
        do_mysql_query("DELETE FROM relation WHERE type=2 AND (id1=".$id." OR id2=".$id.")");

        echo "<b class='error'>Der Spieler ".$control['name']." wurde mit dem Grund ".$statusdescr." gesperrt</b>";
        show_player_info($id);
      }
      else
      echo "<b class='error'>Kein Grund angegeben.</b>";
    }
  }

  if (isset($id) && isset($susp)) {
  	$control_res = do_mysql_query("SELECT name,status,statusdescription FROM player WHERE id=".$id);
  	if ($control = mysqli_fetch_assoc($control_res)) {
  		if ($reason) {
  			// Edit Kroddn: Status 1 rausgenommen
  			if ( $control['status'] != 2 ) {
  				if (do_mysql_escape_string($control['statusdescription']))
  				$reason = $control['statusdescription']."\n".$reason;
  				do_mysql_query("UPDATE player SET status=3,statusdescription='".do_mysql_escape_string($reason)."' WHERE id=".$id);
  				echo "<b class='error'>Der Spieler ".$control['name']." wurde mit dem Grund ".$reason." unter Verdacht gestellt.</b>";
  				show_player_info($id);
  			}
  			else {
  				echo "<b class='error'>Der Spieler mit der ID ".$id." ist noch nicht aktiviert oder schon gesperrt.</b>";
  			}
  		}
  		else {
  			echo "<b class='error'>Kein Grund angegeben.</b>";
  		}
  	}
  }

  if (isset($pvalidate)) {
    validate_player_exception($pvalidate, '');
  }
  if (isset($evalidate)) {
    validate_exception($evalidate. '');
  }

 }

if ($player->isAdmin() || $player->isMaintainer()) {
  if (isset($delete)) {
    if ($delete == "player" && strlen($plrName) > 0) {
      $res = getPlayerResultSet($plrName);
      

      if ($data = mysqli_fetch_assoc($res)) {
        show_player_info($data['id']);
        $time_test = do_mysql_query("SELECT lastseen FROM player WHERE id='".$data['id']."'");
        $get_time = mysqli_fetch_assoc($time_test);
        if ((time()-$get_time['lastseen'] > 28*24*3600) && ($player->isMaintainer())) {
          $delmaintainer = 1;
          echo "<br><br>Bist du sicher, dass du den Spieler ".$plrName." löschen willst, wenn ja dann schreibe: \"Ja, kille den Kerl verdammt nochmal\" ins untenstehende Textfeld<br>\n";
          echo '<form action="multihunter.php" method="POST">';
          echo "<input type='text' name='confirm' value='' size='35'><br>";
          echo '<input type="hidden" name="confirmdelete" value="'.$data['id'].'">';
          echo '<input type="hidden" name="confirmdel" value="player">';
          echo '<input type="submit" value=" bestätigen "';
          echo "</form>\n";
        }
        if (($delmaintainer != 1) && ($player->isAdmin())) {
          echo "<br><br>Bist du sicher, dass du den Spieler ".$plrName." löschen willst, wenn ja dann schreibe: \"Ja, kille den Kerl verdammt nochmal\" ins untenstehende Textfeld<br>\n";
          echo '<form action="multihunter.php" method="POST">';
          echo "<input type='text' name='confirm' value='' size='35'><br>";
          echo '<input type="hidden" name="confirmdelete" value="'.$data['id'].'">';
          echo '<input type="hidden" name="confirmdel" value="player">';
          echo '<input type="submit" value=" bestätigen "';
          echo "</form>\n";
        }
      }
      else {
        printf("Spieler existiert nicht.");
      }
    }
  } // if (isset($delete)) {

  if (isset($confirmdel)) {
    if ($confirmdel == "player")
      if ($confirm == "Ja, kille den Kerl verdammt nochmal") {
	do_mysql_query("INSERT INTO log_delete(id,reason,admin,time) VALUES (".$confirmdelete.",'',".$player->getID().",".time().")");
	RemovePlayerAbandoneCities($confirmdelete);
	echo '<b class="error">Der Spieler mit der ID '.$confirmdelete.' wurde erfolgreich gelöscht</b><br>';
    }
  }
}

if ($player->isAdmin() || $player->isMaintainer()) {
  if (isset($reactivate)) {
    if ($reactivate == "player") {
      if (!checkBez($plrName, 2, 40))
      show_fatal_error("Ungültiger Spielername: ".$plrName);
      $res = do_mysql_query("SELECT id FROM player WHERE name='".$plrName."'");
      if ($data = mysqli_fetch_assoc($res)) {
        show_player_info($data['id']);
        echo "<hr><h1>Spieler Re-Aktivieren</h1>\n";
        echo "<a name=\"reactivate\">Bist du sicher</a>, dass du den Spieler ".$plrName." reaktivieren willst, wenn ja dann schreibe: \"Ja, reaktivier den Mistkerl\" ins untenstehende Textfeld<br>\n";

        echo '<form action="multihunter.php" method="POST">';
        echo "<b>Bestätigung</b> <input type='text' name='confirm' value='' size='35'><br>";
        echo "<b>Grund der Reaktivierung</b> <input type='text' name='reason' value='' size='35'><br>";
        echo '<input type="hidden" name="confirmreactivation" value="'.$data['id'].'">';
        echo '<input type="hidden" name="confirmreac" value="player">';
        echo '<input type="submit" value=" bestätigen "';
        echo "</form>\n";
      }
      else {
        printf("<p>Spieler nicht gefunden!<p>");
      }
    }
  }
  if (isset($confirmreac)) {
    if ($confirmreac == "player")
    if ($confirm == "Ja, reaktivier den Mistkerl") {
      do_mysql_query("INSERT INTO log_reactivate(id,reason,releaser,time) VALUES ('".$confirmreactivation."','".$reason."','".$player->getID()."','".time()."')");
      do_mysql_query("UPDATE player set status=NULL, statusdescription=NULL where id='".$confirmreactivation."'");
      echo '<b class="error">Der Spieler mit der ID '.$confirmreactivation.' wurde erfolgreich reaktiviert</b><br>';
    }
  }
}


function stat_market_send_to($id) {
  global $player;
  
  //Ressourcensendungen
  $id = intval($id);
  echo "<table>";
  echo '<tr><td colspan="7" class="tblhead"><b>Ressourcensendungen von anderen Spielern</b></td></tr>';
  echo '<tr><td class="tblhead"><b>Sender</b></td>';
  echo '<td class="tblhead"><b>Ressource</b></td>';
  if ($player->isAdmin())
    echo '<td colspan="1" class="tblhead"><b>Menge</b></td>';
  echo '<td class="tblhead"><b>Zeit</b></td>';
  echo '</tr>';
  $gerres = array('gold'=>'Gold', 'wood'=>'Holz', 'iron'=>'Eisen', 'stone'=>'Stein');
  $got_res = do_mysql_query("SELECT type,quant,idfrom,player.name,time FROM log_market_send LEFT JOIN player ON idfrom=player.id WHERE idto=".$id." ORDER BY time DESC LIMIT 100");
  while ($got = mysqli_fetch_assoc($got_res)) {
    echo "<tr>\n  <td class=\"tblbody\"><b><a href='multihunter.php?showid=".$got['idfrom']."'>".$got['name']."</a></b>(".$got['idfrom'].")</td>";
    echo "  <td class=\"tblbody\">".$gerres[$got['type']]."</td>";
    if ($player->isAdmin()) {
      echo " <td class=\"tblbody\">".$got['quant']."</td>";
    }
    else {
      echo "<td>&nbsp></td>";
    }
    echo "  <td class=\"tblbody\">".date("d.m.y H:i",$got['time'])."</td></tr>";
  }
  echo "</table>";
}

function stat_market_accept($id) {
  global $player;
  
  // Angenommene Marktangebote
  echo "<table>";
  echo '<tr><td colspan="9" class="tblhead"><b>Angenommene Marktangebote durch andere Spieler</b></td></tr>';
  echo '<tr><td class="tblhead"><b>Käufer</b></td>';
  echo '<td class="tblhead" colspan="2"><b>Käufer gibt</b></td>';
  //  if ($player->isAdmin())
  //    echo '<td colspan="1" class="tblhead"><b>Menge (Angebot)</b></td>';
  echo '<td class="tblhead" colspan="2"><b>Käufer erhält</b></td>';
  //  if ($player->isAdmin())
  //    echo '<td colspan="1" class="tblhead"><b>Menge (Nachfrage)</b></td>';
  echo '<td class="tblhead"><b>Zeit (Aufgabe)</b></td>';
  echo '<td class="tblhead"><b>Zeit (Annahme)</b></td>';
  echo '<td class="tblhead"><b>Zeit Diff.</b></td>';
  echo '<td class="tblhead"><b>Stadt</b></td>';
  echo '</tr>';
  
  $gerres = array('gold'=>'Gold','wood'=>'Holz','iron'=>'Eisen','stone'=>'Stein','shortrange'=>'Kurzwaffen','longrange'=>'Langwaffen','armor'=>'Rüstung','horse'=>'Pferde');
  
  $got_res = do_mysql_query("SELECT acceptor,wantsType,wantsQuant,hasType,hasQuant,acctime,offtime,city,player.name FROM log_market_accept LEFT JOIN player ON acceptor=player.id WHERE offerer=".$id." ORDER BY offtime DESC LIMIT 100");
  while ($got = mysqli_fetch_assoc($got_res)) {
    echo "<tr>\n  <td class=\"tblbody\"><b><a href='multihunter.php?showid=".$got['acceptor']."'>".$got['name']."</a></b>(".$got['acceptor'].")</td>";

    // Spieler will...
    if ($player->isMaintainer()) {
      echo " <td class=\"tblbody\" align=\"right\">".$got['wantsQuant']."</td>";
    }
    else {
      echo "<td>&nbsp></td>";
    }
    echo "  <td class=\"tblbody\" style=\"color:#008000;\">".$gerres[$got['wantsType']]."</td>\n";

    // Spieler hat...
    if ($player->isMaintainer()) {
      echo " <td class=\"tblbody\" align=\"right\">".$got['hasQuant']."</td>";
    }
    else {
      echo "<td>&nbsp></td>";
    }
    echo "  <td class=\"tblbody\" style=\"color:#CC0000;\">".$gerres[$got['hasType']]."</td>\n";
    echo "  <td class=\"tblbody\">".date("d.m.y H:i:s",$got['offtime'])."</td>";
    echo "  <td class=\"tblbody\">".date("d.m.y H:i:s",$got['acctime'])."</td>";
    $timediff = $got['acctime']-$got['offtime'];
    $hours   = floor( $timediff / 3600 );
    $minutes = floor( ($timediff - $hours*3600) / 60);
    $seconds = floor( ($timediff - $hours*3600 - $minutes*60) );
    echo "  <td class=\"tblbody\">".sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds )."</td>";
    echo "  <td class=\"tblbody\">".$got['city']."</td></tr>";
  }
  echo "</table>";	
}


/**
 * Formatiert das Resultres als Ancor (a href).
 * @param $res   Ein assoz. Array, muss beinhalten: id, name, login, status aus der Tabelle player
 * @return unknown_type
 */
function get_player_link($res) {
  switch($res['status']) {
    case 3:
      $color = "#FFAF00;"; break;
    case 2:
      $color = "FF0000"; break;
    default:
      $color = "#000000";
  }

  // Nachschauen ob eine akzeptierte exception vorliegt
  return sprintf("<a style=\"color: %s;\" href='multihunter.php?showid=%d'>%s</a> [ %d ]",
                  $color,
                  $res['id'],
                  // Den Login anzeigen, falls Spieler Maintainer ist und Name leer und der ausführende User Maintainer ist
                  (strlen($res['name']) == 0 && $_SESSION['player']->isMaintainer()) ? "( ".$res['login']." )" : $res['name'],
                  $res['id'] );
}


function same_ips($id) {
  $id = intval($id);
  echo "<table>";
  echo "<tr class='tblhead'><td>Gemeinsame IP's mit anderen Spielern</td></tr>";
  $ownIPs_res = do_mysql_query("SELECT DISTINCT ip FROM log_login WHERE id=".$id." ORDER BY time DESC LIMIT 30");
  while ($ownIPs = mysqli_fetch_assoc($ownIPs_res)) {
    echo "<tr>";
    echo "<td class='tblhead'>".$ownIPs['ip']."</td>";
    $ips_res = do_mysql_query("SELECT DISTINCT log_login.id,player.name,player.login,player.status FROM log_login LEFT JOIN player ON player.id=log_login.id WHERE log_login.ip='".do_mysql_escape_string($ownIPs['ip'])."' AND log_login.id<>".$id." ORDER BY time DESC LIMIT 20");
    while($ips = mysqli_fetch_assoc($ips_res)) {
      if ($ips['id'] != $id) {       
        printf("  <td nowrap>%s</td>\n", get_player_link($ips) ); 
      }
    }
    echo "</tr>";
  }
  echo "</table";
}


function same_emails($id) {
  $id = intval($id);
  echo "<table>\n";
  echo "<tr class='tblhead'><td colspan='2'>Spieler mit der gleichen email</td></tr>\n";
  $emails_res = do_mysql_query("SELECT p2.name AS name, p2.login AS login, p2.id AS id, p2.status AS status ".
                               "FROM player AS p1, player AS p2 WHERE p1.id=".$id." AND p1.id<>p2.id AND p1.email=p2.email");
  while ($emails = mysqli_fetch_assoc($emails_res)) {
    printf("  <tr class='tblbody'><td nowrap>%s</td></tr>\n", get_player_link($emails));
  }
  echo "</table>\n";
}

function same_pws($id) {
  $id = intval($id);
  echo "<table>";
  echo "<tr class='tblhead'><td colspan='2'>Spieler mit der gleichem pw</td></tr>";
  $pw_res = do_mysql_query("SELECT p2.login AS login, p2.name AS name, p2.id AS id, p2.status AS status FROM player AS p1, player AS p2 WHERE p1.id=".$id." AND p1.id<>p2.id AND p1.password=p2.password");
  while ($pw = mysqli_fetch_assoc($pw_res)) {
    printf("<tr class='tblbody'><td>%s</td></tr>", get_player_link($pw) );
  }
  echo "</table>";
}


function list_multi_trap($id) {
  $id = intval($id);
  echo "<table>";
  echo "<tr class='tblhead'><td colspan='2'>Spieler in Cookiefalle</td></tr>";
  $sql = sprintf("SELECT id, name, login, status FROM player WHERE id IN (SELECT distinct(multi) FROM multi_trap_caught WHERE cookieowner = %d UNION SELECT distinct(cookieowner) FROM multi_trap_caught WHERE multi = %d)",
                 $id, $id);
  $res = do_mysql_query($sql);
  while ($m = mysqli_fetch_assoc($res)) {
    printf("<tr class='tblbody'><td>%s</td></tr>", get_player_link($m) );
  }
  echo "</table>";
}


function show_locked() {
  $player_locked_res = do_mysql_query("SELECT id,name,statusdescription FROM player WHERE status=2");
  if (do_mysql_num_rows($player_locked_res)) {
    echo "\n<table>";
    echo '<tr><td colspan="2" class="tblhead"><b>gesperrte Spieler('.do_mysql_num_rows($player_locked_res).')</b></td></tr>';
    echo "<tr class='tblhead'>";
    echo "<td>Name</td>";
    echo "<td>Grund</td>";
    echo "</tr>\n";
    while ($player_locked = mysqli_fetch_assoc($player_locked_res)) {
      echo "<tr class='tblbody'>";
      echo "<td><a href='multihunter.php?showid=".$player_locked['id']."'>".$player_locked['name']."</a>(".$player_locked['id'].")</td>";
      echo "<td>".$player_locked['statusdescription']."</td>";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
}

function show_suspicious() {
  $player_susp_res = do_mysql_query("SELECT id,name,statusdescription FROM player WHERE status=3");
  if (do_mysql_num_rows($player_susp_res)) {
    echo "\n<table>";
    echo '<tr><td colspan="2" class="tblhead"><b>Verdächtige Spieler('.do_mysql_num_rows($player_susp_res).')</b></td></tr>';
    echo "<tr class='tblhead'>";
    echo "<td>Name</td>";
    echo "<td>Grund</td>";
    echo "</tr>\n";
    while ($player_susp = mysqli_fetch_assoc($player_susp_res)) {
      echo "<tr class='tblbody'>";
      echo "<td><a href='multihunter.php?showid=".$player_susp['id']."'>".$player_susp['name']."</a>(".$player_susp['id'].")</td>";
      echo "<td>".$player_susp['statusdescription']."</td>";
      echo "</tr>\n";
    }
    echo "</table>\n";
  }
}

function show_player_info($id) {
  global $player;
  $id = intval($id);

  $playerinfo_res = do_mysql_query("SELECT player.*,from_unixtime(player.lastseen) as seen, from_unixtime(player.regtime) as regtime, from_unixtime(player.activationtime) as activationtime, rec.name as recname ".
				   " FROM player LEFT JOIN player AS rec ON rec.id=player.recruiter ".
				   " WHERE player.id=".$id);
  if ($playerinfo = mysqli_fetch_assoc($playerinfo_res)) {
    echo "\n<table>";
    echo '<form action="multihunter.php" method="POST">';
    echo '<input type="hidden" name="showplrid" value="'.$id.'">';
    echo "<tr><td valign='top'>";
    echo "\n<table>";
    echo "<tr class=tblhead><td colspan='3'>Spielerinfo</td></tr>";
    printf("<tr class='tblbody'><td>Name</td><td><a target=\"_blank\" href=\"info.php?show=player&exactmatch=1&name=%s\">%s</a></td></tr>",
    urlencode($playerinfo['name']), $playerinfo['name']);
    if ($_SESSION['player']->isMaintainer()) {
      echo "\n<tr class='tblbody'><td>Login</td><td>".$playerinfo['login']."</td></tr>";
    }
    echo "<tr class='tblbody'><td>ID</td><td>".$id."</td></tr>";
    echo "<tr class='tblbody'><td>Religion</td><td>";
    if ($playerinfo['religion'] == 1)
    echo "Christentum";
    else
    echo "Islam";

    echo "\n<tr class='tblbody'><td>Punkte</td><td>".number_format($playerinfo['points'])."</td></tr>";

    if ($_SESSION['player']->isMaintainer()) {
      echo "\n<tr class='tblbody'><td>Nooblevel</td><td>".$playerinfo['nooblevel']."</td></tr>";
      echo "\n<tr class='tblbody'><td>Email</td><td>".$playerinfo['email']."</td></tr>";
      echo "\n<tr class='tblbody'><td>RegEmail</td><td>".$playerinfo['register_email']."</td></tr>";
      echo "\n<tr class='tblbody'><td>LastSeen</td><td>".$playerinfo['seen']."</td></tr>";
      echo "\n<tr class='tblbody'><td>RegTime</td><td>".$playerinfo['regtime']."</td></tr>";
      echo "\n<tr class='tblbody'><td>ActivationTime</td><td>".$playerinfo['activationtime']."</td></tr>";
      echo "\n<tr class='tblbody'><td>Recruiter</td><td>".$playerinfo['recname']."</td></tr>";

      $row = get_premium_row($id);
      echo "\n<tr class='tblbody'><td>Premium</td><td>";
      if($row && $row['type'] > 0) {
        echo "[".$row['type']."] ".($row['payd'] ?"bezahlt":"testen")." bis ".date("d.m.Y G:i", $row['expire']);
      }
      else {
        echo "NEIN";
      }
      echo "</td></tr>";
      
      if($playerinfo['markdelete']) {
        printf("\n<tr class='tblbody'><td colspan=\"2\">Am %s zum Löschen markiert</td></tr>", date("d.m.Y G:i", $playerinfo['markdelete']) );
      }
    }

    if (!is_null($playerinfo['clan'])) {
      $res2 = do_mysql_query("SELECT name FROM clan WHERE id=".$playerinfo['clan']);
      if ($data2 = mysqli_fetch_assoc($res2))
      echo "\n<tr class='tblbody'><td>Orden</td><td>".$data2['name']."</td></tr>";
      echo "\n<tr class='tblbody'><td>Ordensämter</td><td>";
      if ($playerinfo['clanstatus'] > 0) {
        if ($playerinfo['clanstatus'] == 63)
        echo " Ordensleiter";
        else {
          if ($playerinfo['clanstatus'] & 1)
          echo " Finanzen ";
          if ($playerinfo['clanstatus'] & 2)
          echo " Inneres ";
          if ($playerinfo['clanstatus'] & 4)
          echo " &Auml;usseres ";
        }
      }
      echo "</td></tr>\n";
      $sql = "SELECT tax + amount AS gold FROM clanlog WHERE playerid=".$id." AND clan=".$playerinfo['clan'];
      $gold = do_mysql_query_fetch_assoc($sql);
      echo "\n<tr class='tblbody'><td>Kassenstand</td><td>".$gold['gold']."</td></tr>\n";
    }
    
    if (!is_null($playerinfo['clanapplication'])) {
      $res2 = do_mysql_query("SELECT name FROM clan WHERE id=".$playerinfo['clanapplication']);
      if ($data2 = mysqli_fetch_assoc($res2))
      echo "\n<tr class='tblbody'><td>Ordensbewerbung</td><td>".$data2['name']."</td></tr>";
    }
    echo "</table>\n<p>";
    
    // ENTSPERREN link einfügen
    if ($playerinfo['status'] == 2) {
      printf('<a href="multihunter.php?reactivate=player&plrName=%s#reactivate">Entsperren</a><p>', urlencode($playerinfo['name']));
      echo "<font size=+1><b>Gesperrt</b></font> weil: ";
       
       
    }
    if ($playerinfo['statusdescription'])
    echo $playerinfo['statusdescription']."<p>";

    show_mh_exceptions("multihunter.php", $id);
    same_ips($id);
    same_emails($id);
    same_pws($id);
    list_multi_trap($id);

    stat_market_send_to($id);
    stat_market_accept($id);
     
    echo "<hr>";
  }
  else {
    echo "<b class='error'>Spielerinfo für $id Abruf fehlgeschlagen</b>";
  }
}

function getStatusdescr($type, $reason) {

  switch($type) {
    case 'multi':
      return "<b>Ihr Account wurde wegen Verdacht auf Multiusing gesperrt.</b> Falls Sie glauben, dass Sie zu unrecht dieses Vergehens beschuldigt wurden, dann bitte bei einem Multihunter melden.<p><p><u><b>Mail:</b></u>&nbsp;<a href='mailto:multihunter@holy-wars2.de'>multihunter@holy-wars2.de</a> oder per <a href='http://www.holy-wars2.de/kontakt.php'>Kontaktformular</a>.<br>\n";
      break;
    case 'pushing':
      return "<b>Ihr Account wurde aufgrund von Accountpushing gesperrt.</b> Falls Sie glauben, dass Sie zu unrecht dieses Vergehens beschuldigt wurden, dann bitte bei einem Multihunter melden.<p><p><u><b>Mail:</b></u>&nbsp;<a href='mailto:multihunter@holy-wars2.de'>multihunter@holy-wars2.de</a>.<br>\n";
      break;
    case 'insult':
      return "<b>Ihr Account wurde wegen Beleidigung von Mitspielern oder ungebührlichem Verhalten gesperrt</b> und wird demnächst einer vollständigen Löschung zum Opfer fallen.";
      break;
    case 'rechtex':
      return "<b>Ihr Account wurde wegen Benutzung nationalsozialistischer Kennzeichen gesperrt.</b>";
      break;
    case 'par12':
      return "<b>Ihr Account wurde wegen Verstoss gegen §12 der Nutzungsbedingungen gesperrt</b>. Falls Sie glauben, dass Sie zu unrecht dieses Vergehens beschuldigt wurden, dann bitte bei einem Multihunter melden.<p><p><u><b>Mail:</b></u>&nbsp;<a href='mailto:multihunter@holy-wars2.de'>multihunter@holy-wars2.de</a> oder per <a href='http://www.holy-wars2.de/kontakt.php'>Kontaktformular</a>.<br>\n";
      break;
    case 'cust':
      return $reason;
      break;
  }
}


if (isset($showplrname)) {
  $res = getPlayerResultSet($showplrname);
  
//  if (!checkBez($showplrname, 2, 40))
//    show_fatal_error("Ungültiger Spielername: ".$showplrname);
//  $res = do_mysql_query("SELECT id FROM player WHERE name like '%".$showplrname."%' ORDER BY name != '".$showplrname."'");
  
  while ($res && ($data = mysqli_fetch_assoc($res)) ) {
    show_player_info($data['id']);
  }
}


if (isset($showid)) {
  show_player_info($showid);
}


function getPlayerResultSet($plrName) {
  $res = null;
  
  if(strlen($plrName) > 0) {
    if(is_numeric($plrName)) {
      $res = do_mysql_query("SELECT id FROM player WHERE id = ".$plrName);
    }
    else if(strpos($plrName, "@")) {
      $mail = do_mysql_escape_string($plrName);
      $sql = sprintf("SELECT id FROM player WHERE email LIKE '%%%s%%' ORDER BY email != '%s' LIMIT 10", $mail, $mail);
      $res = do_mysql_query($sql);
    }
    else {
      if (!checkBez($plrName, 2, 40))
      show_fatal_error("Ungültiger Spielername: ".$plrName);
      
      $name = do_mysql_escape_string($plrName);
      $sql = sprintf("SELECT id FROM player WHERE name LIKE '%%%s%%' ORDER BY name != '%s' LIMIT 10", $name, $name);
      $res = do_mysql_query($sql);
    }
  }
  
  return $res;
}

function show_playerinfo() {
  global $infoname;
  echo "<br>\n<table>";
  echo '<form action="multihunter.php" method="POST">';
  echo '<tr class="tblhead"><td colspan="3"><b>Spielerinfo</b></td></tr>'; 
  echo "\n<tr class='tblbody'><td colspan=\"3\">Suchbar ist ID, Name, Email.</td></tr>\n";
  echo '<tr class="tblbody"><td>Name:</td><td>';
  echo '<form action="mulithunter.php" method="POST">';
  if (isset($infoname)) echo "<input type='text' name='showplrname' value='".$infoname."' size='35'>";
  else echo "<input type='text' name='showplrname' value='' size='35'>";
  echo '</td><td>';
  echo '<input type="submit"  value=" Info ">';
  echo '</td></tr>';
  echo '</form>';
  echo "</table>\n<br><br>";
}

function show_blockplayer() {
  global $blockname;
  echo "<br>\n<table>";
  echo '<form action="multihunter.php" method="POST">';
  echo '<tr class="tblhead"><td colspan="2"><b>Spieler sperren</b></td></tr><tr class="tblbody"><td>Name:</td><td>';
  if (isset($blockname)) echo "\n<input type='text' name='name' value='".$blockname."' size='35'>\n";
  else echo "\n<input type='text' name='name' value='' size='35'>\n";
  echo '</td></tr><tr class="tblbody"><td width="50%" valign="top"><b>Grund</b></td><td>';
  echo "<input type='radio' name='type' value='multi'>Multiusing<br>\n";
  echo "<input type='radio' name='type' value='pushing'>Pushing<br>\n";
  echo "<input type='radio' name='type' value='insult'>Beleidigung<br>\n";
  echo "<input type='radio' name='type' value='rechtex'>Verwendung rechtsextremer Namen<br>\n";
  echo "<input type='radio' name='type' value='par12'>Verstoss gegen Â§ 12<br>\n";
  echo "<input type='radio' name='type' value='cust'>\n";
  echo '<textarea rows="3" cols="45" name="reason"></textarea></td></tr>';
  echo "<tr class='tblbody' colspan='2'><td>";
  echo '<input type="submit" name="lock" value=" sperren ">';
  echo '</td></tr>';
  echo '</form>';
  echo "</table>\n";
}

function show_define_suspicion() {
  global $suspname, $blockname;
  echo "<br>\n<table>";
  echo '<form action="multihunter.php" method="GET">';
  echo '<tr class="tblhead"><td colspan="2"><b>Verdacht eintragen</b></td></tr><tr class="tblbody"><td>Name:</td><td>';
  if (isset($suspname))  echo "\n<input type='text' name='name' value='".$suspname."' size='35'>\n";
  else if (isset($blockname)) echo "\n<input type='text' name='name' value='".$blockname."' size='35'>\n";
  else echo "\n<input type='text' name='name' value='' size='35'>\n";
  echo '</td></tr><tr class="tblbody"><td width="50%" valign="top"><b>Grund</b></td><td>';
  echo '<textarea rows="3" cols="45" name="reason"></textarea></td></tr>';
  echo "<tr class='tblbody' colspan='2'><td>";
  echo '<input type="submit" name="susp" value=" eintragen ">';
  echo '</td></tr>';
  echo "</form>\n";
  echo "</table>\n";
}

function show_delplayer() {
  echo "<br>\n<table>";
  echo '<form action="multihunter.php" method="POST">';
  echo '<input type="hidden" name="delete" value="player">';
  echo '<tr class="tblhead"><td colspan="3"><b>Spieler löschen</b></td></tr><tr class="tblbody"><td>Name:</td><td>';
  echo "<input type='text' name='plrName' value='' size='35'>";
  echo '</td><td>';
  echo '<input type="submit" value=" löschen ">';
  echo '</td></tr>';
  echo '</form>';
  echo "</table>";
}

function show_player_locked() {
  echo "<h1>Gesperrte Spieleraccounts</h1>\n";
  echo 'Die Spieler sind nach der Reihenfolge Ihres letzen Logins sortiert. <a href="#lastlocked">Ganz unten</a>.<br>';
  echo 'Maintainer können nur Spieler löschen, welche länger als 4 Wochen (672h) gesperrt sind!<br>';

  $players = do_mysql_query("SELECT id, login, name, email, activationkey, status, ".
                            "  unix_timestamp() - lastseen AS inactive_seconds,".
                            "  from_unixtime(regtime)      AS regtime_time,".
                            "  from_unixtime(lastseen)     AS lastseen_time,".
                            "  regtime                     AS regtime_unixtime,".
                            "  lastseen                    AS lastseen_unixtime".
                            " FROM player WHERE activationkey IS NULL AND status IN (1,2) ".
                            " ORDER BY id");
  $num = do_mysql_num_rows($players);
  $i = 0;

  echo "<p>$num Spieler gesperrt..";
  echo "<table>\n";
  echo " <tr class=\"tblhead\"><td>[ID] Spielername</td><td>Act.Key</td><td>eMail</td><td>Regtime</td><td>Lastseen</td><td>Inaktiv<br>in h</td><td colspan=\"2\">Aktion</td></tr>\n";
  
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
      "<form action=\"multihunter.php\" method=\"POST\">".
      "<input type=\"hidden\" name=\"delete\" value=\"player\">".
      "<input type=\"hidden\" name=\"plrName\" value=\"".$p['name']."\">".
      "<input type=\"submit\" value=\"löschen\">".
      "</form></td>".
      "<td>".
      "<form action=\"multihunter.php\" method=\"POST\">".
      "<input type=\"hidden\" name=\"reactivate\" value=\"player\">".
      "<input type=\"hidden\" name=\"plrName\" value=\"".$p['name']."\">".
      "<input type=\"submit\" value=\"reaktivieren\">".
      "</form></td>".
      "</td>\n".
      "</tr>\n";
  }


  echo "</table><hr>\n";

}

function show_double() {
  echo "<h1>Verdächtige Spieleraccounts</h1>\n";
  echo 'Hier werden Spieler aufgelistet, welche entweder die gleiche IP, die gleiche Email, oder das gleiche Passwort haben.<br>';
  echo 'Die Spieler sind nach Namen sortiert. <a href="#lastdouble">Ganz unten</a>.<br>';
  echo "<p><table>\n";
  echo " <tr class=\"tblhead\"><td>ID1</td><td>ID2</td><td>Name1</td><td>Name2</td><td>eMail</td><td>Passwort</td></tr>\n";

  $doubles = do_mysql_query("SELECT DISTINCT s1.id AS id1, s2.id AS id2, s1.name AS n1, s2.name AS n2, s1.status AS s1, s2.status AS s2, s1.email AS e1, s2.email AS e2, s1.password AS p1, s2.password AS p2 FROM player AS s1, player AS s2 WHERE (s1.password = s2.password OR s1.email = s2.email) AND s1.id <> s2.id   ORDER BY s1.name");
  $num = do_mysql_num_rows($doubles);
  while ( $d = mysqli_fetch_array($doubles) ) {
    echo '<tr class="tblbody">';
    echo '<td>'.$d['id1'].'</td>';
    echo '<td>'.$d['id2'].'</td>';
    echo '<td>'.$d['n1'].'<font size=-2>&nbsp;(';
    echo '<a href="multihunter.php?sh_playerinfo=1&infoname='.$d['n1'].'">Info</a>';
    if ($d['s1']!=3) echo '|<a href="multihunter.php?sh_define_suspicion=1&suspname='.$d['n1'].'">verdächtigen</a>';
    if ($d['s1']!=2) echo '|<a href="multihunter.php?sh_blockplayer=1&blockname='.$d['n1'].'">sperren</a>)';
    if ($d['s1']==3) echo "</font><font size=-2 color='red'>&nbsp;verdächtigt";
    if ($d['s1']==2) echo "</font><font size=-2 color='red'>&nbsp;gesperrt";
    if ($d['s1']==1) echo "</font><font size=-2 color='red'>&nbsp;unaktiviert";
    echo '</font></td>';
    echo '<td>'.$d['n2'].'<font size=-2>&nbsp;(';
    echo '<a href="multihunter.php?sh_playerinfo=1&infoname='.$d['n2'].'">Info</a>';
    if ($d['s2']!=3) echo '|<a href="multihunter.php?sh_define_suspicion=1&suspname='.$d['n2'].'">verdächtigen</a>';
    if ($d['s2']!=2) echo '|<a href="multihunter.php?sh_blockplayer=1&blockname='.$d['n2'].'">sperren</a>)';
    if ($d['s2']==3) echo "</font><font size=-2 color='red'>&nbsp;verdächtigt";
    if ($d['s2']==2) echo "</font><font size=-2 color='red'>&nbsp;gesperrt";
    if ($d['s2']==1) echo "</font><font size=-2 color='red'>&nbsp;unaktiviert";
    echo '</font></td>';
    if ($d['e1']==$d['e2']) echo '<td><font color="red">gleich</font></td>';
    else echo '<td><font color="green">ungleich</font></td>';
    if ($d['p1']==$d['p2']) echo '<td><font color="red">gleich</font></td>';
    else echo '<td><font color="green">ungleich</font></td>';
    echo '</tr>';
  }
  echo "</table><hr>\n";
}

/*
echo "<br>";
show_locked();
echo "<br>";

show_suspicious();
 echo "<br/>";
 show_mh_exceptions('multihunter.php');*/

?>

</body>
</html>
