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


include_once("includes/db.inc.php");
include_once("includes/clan.class.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
$_SESSION['player']->setActivePage(basename(__FILE__));
start_page();
?>
<script>
<!--
wmtt = null;
document.onmousemove = updateWMTT;
function updateWMTT(e) {
  x = (document.all) ? window.event.x + document.body.scrollLeft : e.pageX;
  y = (document.all) ? window.event.y + document.body.scrollTop  : e.pageY;
  if (wmtt != null) {
    wmtt.style.left = (x + 20) + "px";
      wmtt.style.top 	= (y - 40) + "px";
  }
}

function showWMTT(id) {
  wmtt = document.getElementById(id);
  wmtt.style.display = "block"
}

function hideWMTT() {
  wmtt.style.display = "none";
}
//-->
</script>

<? 

start_body();

if(!$_SESSION['player']->clanstatus) {
  echo "<p1>Ihr seid nicht befugt diese Information abzurufen!</p1></body></html>";
  exit;
}

// set Clan to in session saved clan of user due to globals deprecation 
$clan = NULL;
if($_SESSION['player']->clan) {
  $clan = $_SESSION['clan'];
}

if( $_GET['activity']==true) {
  if($_SESSION['player']->clan) {
    echo "<table cellpadding=\"1\" cellspacing=\"1\">\n";
    echo "<tr>\n";
    echo "  <td class=\"tblhead\">Name</td>\n";
    // echo "  <td class=\"tblhead\">Registriert seit:</td>\n";
    echo "  <td class=\"tblhead\" colspan=\"2\">Letzte Aktivität (Datum/aktueller Status):</td>\n";
    echo "  <td class=\"tblhead\">Punkte</td>\n";
    echo "  <td class=\"tblhead\">Durch.Punkte</td>\n";
    echo "  <td class=\"tblhead\">Ordensrang</td>\n";
    echo "  <td class=\"tblhead\">&nbsp;</td>\n";
    echo "</tr>\n";

    $res1=do_mysql_query("SELECT id,name,clanstatus, regtime,lastseen,points,".
                         "       round(pointsavg/pointsupd) as avgpoints ".
                         " FROM player ".
                         " WHERE clan=".$_SESSION['player']->clan." ORDER BY name ASC");
    while($data1=mysqli_fetch_object($res1)) {
      //$p=&new player($data1['id'],0);
      $p = $data1;
      
      echo "<tr>";
      echo "  <td class=\"tblbody\">".get_info_link($p->name,"player",1)."</td>\n";
      
      // echo "  <td class=\"tblbody\">".date("d.m.Y H:i",$p->regtime)."</td>\n";      
      $online = do_mysql_query_fetch_assoc("SELECT lastclick FROM player_online WHERE uid = ".$p->id);
      
      if ( time() - $online['lastclick'] < 3*60 ) {
        echo "  <td class=\"tblbody\">".date("d.m.Y H:i",$online['lastclick'])."</td>\n";
        echo "  <td class=\"tblbody\"><span title=\"In den letzen 3 Minuten online gewesen\" style=\"color:green; font-weight: bold\">online</span></td>\n";
      }
      else if ( time() - $online['lastclick'] < 5*60 ){
        echo "  <td class=\"tblbody\">".date("d.m.Y H:i",$online['lastclick'])."</td>\n";
        echo "  <td class=\"tblbody\"><span title=\"In den letzen 5 Minuten online gewesen\" style=\"color:orange; font-weight: bold\">online</span></td>\n";
      }
      else {
        echo "  <td class=\"tblbody\">".date("d.m.Y H:i",$p->lastseen)."</td>\n";
        echo "  <td class=\"tblbody\"><span style=\"color:red;\">offline</span></td>\n";
      }
      
      echo "  <td class=\"tblbody\">".prettyNumber($p->points)."</td>\n";
      echo "  <td class=\"tblbody\">".prettyNumber($p->avgpoints)."</td>\n";
      echo "  <td class=\"tblbody\">".getPlayerClanFunctions($p->clanstatus)."</td>\n";
      $num = getAttacksAgainstPlayer($p->id);
      if($num > 0)
        echo "  <td class=\"tblbody\"><span style=\"color:red;\">wird angegriffen (".$num.")!</span></td>\n";
      else
        echo "  <td class=\"tblbody\">&nbsp;</td>\n";
      echo "</tr>\n";
    } // while
    echo "</table>";
  }
  else {
    echo "Fehler. Bitte neu einloggen";
  }  
} 
else {
  if (!$_SESSION['player']->isMinisterFinance()) {
    echo "<p1>Ihr seid nicht befugt diese Information abzurufen!</p1></body></html>";
    exit;
  }

  switch($_REQUEST['order']) { 
  case "payd":
    $order = "amount DESC, player.name";
    break;
  case "tax":
    $order = "tax DESC, player.name";
    break;
  case "sum":
    $order = "sums DESC, player.name";
    break;
  case "name":
  default:
    $order = "player.clan=".$clan->getID()." DESC, player.name";
  }

  
  $under = ' style="text-decoration: underline;"';

  echo "Zum Sortieren auf die Spaltenüberschrift klicken!<p>";
  echo "<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\">\n";
  echo "<tr>\n";
  echo "	<td class=\"tblhead\"><a href=\"?order=name\"".(!isset($_REQUEST['order']) || $_REQUEST['order'] == "name" ? $under : "").">Spielername</a></td>\n";
  echo "	<td class=\"tblhead\"><a href=\"?order=payd\"".($_REQUEST['order'] == "payd" ? $under : "").">Ein-/Auszahlungen</a></td>\n";
  echo "	<td class=\"tblhead\"><a href=\"?order=tax\"".($_REQUEST['order'] == "tax" ? $under : "").">Entrichtete Steuern</a></td>\n";
  echo "	<td class=\"tblhead\"><a href=\"?order=sum\"".($_REQUEST['order'] == "sum" ? $under : "").">Gesamtbetrag</a></td>\n";
  echo "</tr>\n";

  $deleted_players = 0;
$playerids = do_mysql_query("SELECT player.name, player.id, player.clan, clanlog.tax, clanlog.amount, clanlog.tax+clanlog.amount AS sums".
                            " FROM clanlog LEFT JOIN player ON playerid=player.id where clanlog.clan=".$clan->getID().
                            " ORDER BY ".$order);

while( $get_playerids = mysqli_fetch_assoc($playerids) ) {
  if ( $get_playerids['name'] != null ) {
    echo "<tr class=\"tblbody\" align=\"right\"><td width=\"120\" align=\"left\">".$get_playerids['name'];
    if ($get_playerids['clan']!=$clan->getID()) { echo " *"; }
    echo "</td>
            <td width='120' class='tblbody'>".prettyNumber($get_playerids['amount'])."</td>
            <td width='120' class='tblbody'>".prettyNumber($get_playerids['tax'])."</td>
            <td width='120' class='tblbody'>".prettyNumber($get_playerids['sums'])."</td>
            </tr>";
    //Aufsummierung
    $amount+=$get_playerids['amount'];
    $tax+=$get_playerids['tax'];
    $gesamt+=$get_playerids['tax']+$get_playerids['amount'];
  }
  else {
    // Spieler gelöscht
    $amount_del+=$get_playerids['amount'];
    $tax_del+=$get_playerids['tax'];
    $gesamt_del+=$get_playerids['tax']+$get_playerids['amount'];

    $deleted_players++;
  }
} // while

 if ($deleted_players > 0) {
   echo "<tr class=\"tblhead\" align=\"right\">
	<td align=\"left\"><b>Gelöschte Spieleraccounts</b></td>
	<td>".prettyNumber($amount_del)."</td>
	<td>".prettyNumber($tax_del)."</td>
	<td>".prettyNumber($gesamt_del)."</td>
</tr>\n";
 }
echo ("<tr class=\"tblhead\" align=\"right\">
	<td align=\"left\"><b>Gesamt (inkl. gelöschte)</b></td>
	<td><b>".prettyNumber($amount + $amount_del)."</b></td>
	<td><b>".prettyNumber($tax + $tax_del)."</b></td>
	<td><b>".prettyNumber($gesamt + $gesamt_del)."</b></td>
</tr>\n");
echo "<tr><td> &nbsp </td></tr>";
echo "<tr><td><a href=\"clan.php\">zurück</a></td></tr>";
echo "<tr><td> &nbsp </td></tr>";
echo "<tr><td><b>Bemerkung:</b> </td></tr>";
echo "<tr><td colspan=\"4\">Mit * gekennzeichnete Spieler gehüren nicht mehr dem Orden an!</td></tr>";
echo "</table>\n";
}

?>