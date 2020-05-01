<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2015
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
 * Copyright (c) 2003-2006
 *
 * Stefan Neubert
 * Stefan Hasenstab
 * Markus Sinner <kroddn@cmi.gotdns.org>
 * Gordon Meiser
 *
 ***************************************************/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
include_once("includes/clan.class.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");
include_once("includes/session.inc.php");

$player->setActivePage(basename(__FILE__));
$clan = $_SESSION['clan'];
//$error_string = "";

if (isset($writemsg) && isset($clanmember)) {
  header("Location:messages2.php?msgrecipient=".resolvePlayerName($clanmember));
  die();
}
if (isset($gotomarket) && isset($clanmember)) {
  header("Location:marketplace.php?sendto=".resolvePlayerName($clanmember));
  die();
}
if (isset($writemsgbew) && isset($clanapplicant) && strlen($clanapplicant) > 1) {
  
  $text = do_mysqli_query_fetch_assoc("SELECT clanapplicationtext FROM player WHERE name='".resolvePlayerName($clanapplicant)."'");
  
  header("Location:messages2.php?msgrecipient=".resolvePlayerName($clanapplicant).
	 "&msgbody=".urlencode("\n\n[quote]".$text['clanapplicationtext'])."[/quote]");
  die();
}

if (isset($newClan) && isset($clanName)) {
  // Returns null on success
  $error_string = $clan->newClan($clanName)."<br>";
}

if (isset($joinClan) && isset($clanID)) {
  $clan->appClan($clanID, $applText);
}

if (isset($payGold)) {
  if (isset($pay))
    $clan->payIn($pay);
  if (isset($payOut) && isset($clanmember))
    $clan->payOut($clanmember, $payOut);
}

if (isset($accApp) && isset($clanapplicant)) {
  $clan->accMember($clanapplicant);
}

if (isset($askpromote) && isset($clanmember) && isset($status)) {
  if ($status < 63) {
    $result = $clan->promote($clanmember, $status);
    if ($result!=null) {
      $error_string = $result."<br>";
    }
  }

}
if (isset($promote) && isset($clanmember) && isset($status))
     $result = $clan->promote($clanmember, $status);
     if ($result!=null) {
       $error_string = $result."<br>";
     }

     if (isset($demote) && isset($clanmember) && isset($status)) {
       $clan->demote($clanmember, $status);
     }

if (isset($fire) && isset($clanmember)) {
  $clan->leaveClan($clanmember);
}

if (isset($leave)) {
  $clan->leaveClan($player->getID());
}

if (isset($dropApp) && isset($clanapplicant)) {
  $clan->dropApp($clanapplicant);
}

if (isset($setTax)) {
  $clan->setTax($setTax);
}

if (isset($newRel) && isset($clanname) && isset($type)) {
  $clan->changeRelation(trim($clanname), $type);
}

if (isset($delbnd) && isset($friend)) {
     $clan->changeRelation($friend, 1);
}

if (isset($delnap) && isset($nap)) {
  $clan->changeRelation($nap, 1);
}

if (isset($peace) && isset($enemy)) {
  $clan->changeRelation($enemy, 1);
}

if (isset($accpeace) && isset($neut)) {
  $clan->accReqRelation($neut);
}

if (isset($accnap) && isset($nap)) {
  $clan->accReqRelation($nap);
}

if (isset($accbnd) && isset($bnd)) {
  $clan->accReqRelation($bnd);
}

if (isset($delpeace) && isset($neut)) {
  $clan->delReqRelation($neut, 1);
}

if (isset($delnap) && isset($nap)) {
  $clan->delReqRelation($nap, 2);
}

if (isset($delbnd) && isset($bnd)) {
  $clan->delReqRelation($bnd, 3);
}

if (isset($delreqpeace) && isset($reqneut)) {
  $clan->delReqRelation($reqneut, -1);
}

if (isset($delreqnap) && isset($reqnap)) {
  $clan->delReqRelation($reqnap, -2);
}

if (isset($delreqbnd) && isset($reqbnd)) {
  $clan->delReqRelation($reqbnd, -3);
}

if (isset($desc) && isset($setdesc)) {
  $clan->updateDescription($desc);
}

if($clan->getID()) {
  $res = do_mysqli_query("SELECT * FROM clan WHERE id=".$clan->getID());    
  $myclan = mysqli_fetch_assoc($res);
}

?>
<html>
<head>
<title><? echo $pagetitle; ?></title>
<script language="javascript">
function showhide(id) {
  var i;
  for(i = 1; i <= 4; i++) {
    if(document.getElementById("lay"+i)) {
      document.getElementById("lay"+i).style.display = "none";
    }
  }
  if(id) {
    if(document.getElementById("lay"+id)) {
      document.getElementById("lay"+id).style.display = "inline";
    }
  }
}
function clanmap() {
  window.open("clanmap.php","Karte","width=1000,height=900,left=0,top=0,status=no,scrollbars=yes,dependent=yes");
}
function ministry(id) {
  var i;
  for(i = 1; i <= 4; i++) {
    if(document.getElementById("min"+i)) {
      document.getElementById("min"+i).style.display = "none";
      document.getElementById("mis"+i).style.display = "none";
      if(i <= 3) {
	document.getElementById("misub"+i).style.display = "none";
      }
    }
  }	 
  if(id) {
    if(document.getElementById("min"+id)) {
      document.getElementById("min"+id).style.display = "inline";
      document.getElementById("mis"+id).style.display = "inline";
      if(id <= 3) {
	document.getElementById("misub"+id).style.display = "inline";
      }
    }
  }
}
function bbCode(start,end,ext) {
  var txtarea = document.theForm.theText;
  txtarea.focus();
  if(ext) {
    if(ext == 1) {
      start = prompt("Geben Sie eine Farbe an. zb: red,blue,green,...","");
      if(start == null) {
	return
	  } else {
	    start = "[color="+start+"]";
	  }
    }
    if(ext == 2) {
      start = prompt("Geben Sie hier die URL des Bildes ein!\nzb: https://www.domain.com/myimage.jpg","");
      if(start == null) {
	return
	  } else {
	    start = "[img]"+start;
	  }
    }
    if(ext == 3) {
      start = prompt("Geben Sie hier die URL an.\nzb: https://www.domain.com","");
      if(start == null) {
					return
					  } else {
					    start = "[url="+start+"]"+start;
					  }
    }
  }
  if (window.getSelection) {
    text = window.getSelection();
    window.getSelection() = start+text+end;
  } else if (document.getSelection) {
    text = document.getSelection();
    document.getSelection() = start+text+end;
  } else if (document.selection) {
    text = document.selection.createRange().text;
    document.selection.createRange().text = start+text+end;
  }
}
</script>
</head>
<? include("includes/update.inc.php"); ?>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<div align="center">
<? echo show_banner(0); ?>
</div><br>

<?php
$_SESSION['clan']->update();

if ($askpromote && ($status == 63) && intval($clanmember) ) {
  $res = do_mysqli_query("SELECT name FROM player WHERE id=".mysqli_real_escape_string($clanmember) );
  if ($data = mysqli_fetch_assoc($res)) {
    echo "Sie wollen ".$data['name'].' zum Ordensleiter erheben, ist dies wirlich euer Wille? Bestätigen sie dies bitte mit einem Klick <a href="'.$PHP_SELF.'?promote=1&status=63&clanmember='.$clanmember.'"><u>zu bestätigen</u></a> oder <a href="'.$PHP_SELF.'"><u>abzulehnen</u></a>';
    exit;
  }
}

if ($error_string != null)
     echo "<b class='error'>".$error_string."</b><br>";


     if ($clan->getID() > 0) {
       // Print some Links

	echo "<div style=\"margin-left:3px;\">\n";
	echo "<div class=\"tblhead\" style=\"width:125px;float:left;text-align:center;\"><a href=\"#\" onclick=\"showhide(1)\">Ordensgeschehen</a></div>\n";
	if ($_SESSION['clan']->getStatus()&2)
	  echo "<div class=\"tblhead\" style=\"margin-left:1px; width:125px;float:left;text-align:center;\"><a href=\"#\" onclick=\"showhide(2)\">Bewerbungen</a></div>\n";
	if ($_SESSION['clan']->getStatus()&4)
	  echo "<div class=\"tblhead\" style=\"margin-left:1px; width:125px;float:left;text-align:center;\"><a href=\"#\" onclick=\"showhide(3)\">Außenministerium</a></div>";
	if ($_SESSION['clan']->getStatus()&63)
	  echo "<div class=\"tblhead\" style=\"margin-left:1px; width:125px;float:left;text-align:center;\"><a href=\"#\" onclick=\"showhide(4)\">Ordensbeschreibung</a></div>\n";
	echo "</div><br />\n";
	
	
	echo '<form action="'.$PHP_SELF.'" method="POST">';
	$res1 = do_mysqli_query("SELECT id,name,points,clanstatus FROM player WHERE clan=".$clan->getID()." ORDER BY clanstatus DESC,name ASC");

	// ORDENSGESCHEHEN
	
	echo "<table id=\"lay1\" border=\"0\"><tr><td valign=\"top\">";
	echo "<table width=\"100%\">";
	echo '<tr class="tblhead"><td>Ordensbr&uuml;der</td></tr>';
	echo '<tr class="tblbody"><td>';
	echo '<select size="10" style="width:200px;" name="clanmember">';
    while ($data1 = mysqli_fetch_assoc($res1)) {
		echo ' <option value="'.$data1['id'].'">'.$data1['name']."(".$data1['points'].")";
		if ($data1['clanstatus'] > 0) {
			if ($data1['clanstatus'] == 63)
				echo " Ordensleiter";
			else {
				if ($data1['clanstatus'] & 1)
					echo " Finanzen ";
				if ($data1['clanstatus'] & 2)
					echo " Inneres ";
				if ($data1['clanstatus'] & 4)
					echo " &Auml;usseres ";
			}
		}
		echo "</option>\n";
	}
	echo "</select>\n";
    echo "</td></tr>\n";
    echo "</table>";
    echo "</td><td valign='top'>";
	echo "<table width=\"300\" border=\"0\">\n";
	echo "<tr><td class=\"tblhead\"><a href=\"#\" onClick=\"document.getElementById('ag').style.display = 'inline'; document.getElementById('ok').style.display = 'none'; document.getElementById('ov').style.display = 'none';\">Allgemeines</a></td></tr>\n";
	echo "<tr id=\"ag\"><td>\n";
	echo '<input type="submit" width="300" style="width:300px;" name="writemsg" value=" Nachricht Senden "/><br/>';
	echo '<input type="submit" width="300" style="margin-top:2px; width:300px;" name="gotomarket" value=" Marktplatz "/><br/>';
	if ($clan->getStatus() & 2) {
		echo '<input type="submit" width="300" style="margin-top:2px; width:300px;" onClick="return confirm(\'Sicher?\')" name="fire" value=" Entlassen " />';
	}
	echo "</td></tr>\n";
	echo "<tr><td class=\"tblhead\"><a href=\"#\" onClick=\"document.getElementById('ag').style.display = 'none'; document.getElementById('ok').style.display = 'inline'; document.getElementById('ov').style.display = 'none';\">Ordenskasse</a></td></tr>\n";
	echo "<tr id=\"ok\" style=\"display:none;\"><td>\n";
    // ----------- Ordenskasse ------------- // 
    echo "<table width=\"300\">";
    $res2 = do_mysqli_query("SELECT gold,tax FROM clan WHERE id=".$clan->getID());
    $data2 = mysqli_fetch_assoc($res2) or die("Das Gold des Ordens wurde geraubt!");
    echo '<tr class="tblbody"><td width="75">Verm&ouml;gen</td><td width="225">'.number_format($data2['gold'],0,",",".").'</td></tr>';
    echo '<tr class="tblbody"><td width="75">Einzahlen</td><td width="225"><input type="text" name="pay" size="5"></td></tr>';
    echo '<tr class="tblbody"><td width="75">Steuern</td><td width="225">'.intval($data2['tax']*100).'%</td></tr>';
    if ($clan->getStatus() & 1) {
		$member = mysqli_fetch_assoc(do_mysqli_query("SELECT count(*) as c FROM player WHERE clan = ".$clan->getID()));
	 	if ($member['c'] >= 5) {
	    	echo '<tr class="tblbody"><td width="75">Auszahlen</td><td width="225"><input type="text" name="payOut" size="5"></td></tr>';
	 	}
	 	else {
	    	echo '<tr class="tblbody"><td colspan="2">Auszahlen erst ab 5 Mitgliedern möglich!</td></tr>';
	    }
		echo '<tr class="tblbody"><td width="75">Steuerfuss</td><td width="225"><input type="text" name="setTax" size="3" value="'.intval($data2['tax']*100).'"></td></tr>';
	}
	echo "<tr class=\"tblbody\"><td width=\"120\" valign=\"top\">\n";
	$res9=do_mysqli_query("SELECT player.name as name, player.id as id, player.clan as clan, clanlog.tax as tax, clanlog.amount as amount FROM clanlog LEFT JOIN player ON playerid=player.id WHERE player.id='".$_SESSION['player']->id."' AND clanlog.clan='".$clan->getID()."'");
	$data9=mysqli_fetch_assoc($res9);
	$perc_in=0;
	echo "Eure Einzahlungen<br /><i>&nbsp;&nbsp;&nbsp;(inkl. Steuern)</i></td><td>".number_format($data9['amount'],0,",",".")." - ";
	if($data2['gold'] == 0) { $data2['gold']=1; }
	if($data9['amount'] == 0) { $data9['amount']=1; }
	$perc_in=round((($data9['amount']/$data2['gold'])*100),2);
	if($data9['amount']<0)
		$perc_in=0;
	echo "Entspricht ".$perc_in." %<br />";
	$perc_in=0;
	if(($data9['amount']+$data9['tax']) != 0 || $data2['gold'] != 0) {
		echo number_format(($data9['amount']+$data9['tax']),0,",",".")." - ";
		$perc_in=round(((($data9['amount']+$data9['tax'])/$data2['gold'])*100),2);
		if(($data9['amount']+$data9['tax'])<0)
			$perc_in=0;
	}
	echo "Entspricht ".$perc_in." %<br />";
	echo "</td>";
	echo "</td></tr>\n";
    echo "</table>";
    echo '<center><input type="submit" name="payGold" style="width:300px;" value="                   Zahlung t&auml;tigen                   " /></center>';
	echo "</td></tr>\n";
	if ($clan->getStatus() & 1) {
		echo "<tr><td class=\"tblhead\"><a href=\"clanlog.php\">Ordenskasse - Zahlungsstatistik</a></td></tr>";
	}
	if ($clan->getStatus() & 63) {
		echo "<tr><td class=\"tblhead\"><a href=\"clanlog.php?activity=true\">Ordensbr&uuml;der</a></td></tr>";
	}
	if ($clan->getStatus() > 0) {
	  echo "<tr><td class=\"tblhead\"><a href=\"#\" onClick=\"document.getElementById('ag').style.display = 'none'; document.getElementById('ok').style.display = 'none'; document.getElementById('ov').style.display = 'inline';\">Ordensverwaltung</a></td></tr>\n";
	  echo "<tr id=\"ov\" style=\"display:none;\"><td>\n";
	  echo "<table style=\"width:300px;\">";
	  echo '<tr class="tblbody"><td>';
	  if ($clan->getStatus() & 63)
	    echo '<input type="radio" name="status" value="63"> Ordensleiter<br>';
	  if ($clan->getStatus() & 1)
	    echo '<input type="radio" name="status" value="1"> Finanzminister<br>';
	  if ($clan->getStatus() & 2)
	    echo '<input type="radio" name="status" value="2"> Innenminister<br>';
	  if ($clan->getStatus() & 4)
	    echo '<input type="radio" name="status" value="4"> Aussenminister<br>';
	  echo "</td></tr>";
	  echo "</table>";
	  echo '<input type="submit" style="width:150px;" name="askpromote" value=" Bef&ouml;rdern ">&nbsp;';
	  echo '<input type="submit" style="width:150px;" name="demote" value=" Degradieren ">';
	}
	echo "</td></tr><tr class=\"tblhead\"><td><a href=\"clanmap.php\" onclick=\"clanmap(); return false;\">Ordenskarte</a></td></tr>\n";
	echo "</td></tr><tr class=\"tblhead\"><td><a href=\"cf_index.php\">Ordensforum</a></td></tr>\n";
	echo "</table>";
	echo "</td></tr></table>\n";

	
	// BEWERBUNGEN
	if ($clan->getStatus() & 2) {
		echo "<table style=\"margin-top:4px; display:none;\" id=\"lay2\">\n";
	 	echo '<tr class="tblhead"><td colspan="2">Bewerber</td></tr>';
	 	echo '<tr class="tblbody"><td>';
	 	echo '<select size="5" style="width:200px;" name="clanapplicant">';
	 	$resapp = do_mysqli_query("SELECT id,name,points FROM player WHERE clanapplication=".$clan->getID()." ORDER BY points DESC");
	 	while ($dataapp = mysqli_fetch_assoc($resapp))
	   		echo '<option value="'.$dataapp['id'].'">'.$dataapp['name']."(".$dataapp['points'].")".'</option>';
	 	echo "</select>";
	 	echo "</td>";
	 	echo '<td valign="top"><input type="submit" style="width:300px;" name="accApp" value=" Aufnehmen "><br />';
	 	echo '<input type="submit" style="margin-top:1px; width:300px;" name="dropApp" value=" Ablehnen "><br />';
		echo "<input type=\"submit\" width=\"300\" style=\"margin-top:1px; width:300px;\" name=\"writemsgbew\" value=\" Nachricht Senden \" /></td></tr>\n";
	 	echo "</table>\n";
	}
	echo "</form>";

		echo '<form action="'.$PHP_SELF.'" method="POST">';	
	// AUßENMINISTERIUM
	if ($clan->getStatus() & 4) {	
		echo "<table id=\"lay3\" style=\"margin-top:4px; display:none; width:500px;\">";
		echo '<tr class="tblhead"><td colspan="6">Aussenministerium</td></tr>';
		echo '<tr class="tblbody"><td colspan="6">';
		echo "<a href=\"#\" onclick=\"ministry(1)\">Aktueller Status</a>\n";
		echo " - <a href=\"#\" onclick=\"ministry(2)\">Fremde Angebote</a>\n";
		echo " - <a href=\"#\" onclick=\"ministry(3)\">Eigene Angebote</a>\n";
		echo " - <a href=\"#\" onclick=\"ministry(4)\">Neue Beziehung</a></td></tr>";
		echo '<tr><td colspan="6">&nbsp;</td></tr>';
		echo '<tr class="tblhead" id="min1">';
		echo '<td colspan="2" width="33%">Feinde</td>';
		echo '<td colspan="2" width="33%">NAP\'s</td>';
		echo '<td colspan="2" width="33%">Verb&uuml;ndete</td>';
		echo "</tr>\n";
		echo '<tr class="tblbody" id="mis1">';
		echo '<td colspan="2">';
		echo '<select  style="width:100%;" size="5" name="enemy">';

	 $res1 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=0 AND clanrel.id2=".$clan->getID());
	 $res2 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=0 AND clanrel.id1=".$clan->getID());
	 while ($data1 = mysqli_fetch_assoc($res1))
	   echo '<option value="'.$data1['name'].'">'.$data1['name']."</option>";
	 while ($data2 = mysqli_fetch_assoc($res2))
	   echo '<option value="'.$data2['name'].'">'.$data2['name']."</option>";
	 echo "</select>";
	 echo '</td><td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="nap">';
	 $res1 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=2 AND clanrel.id2=".$clan->getID());
	 $res2 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=2 AND clanrel.id1=".$clan->getID());
	 while ($data1 = mysqli_fetch_assoc($res1))
	   echo '<option value="'.$data1['name'].'">'.$data1['name']."</option>";
	 while ($data2 = mysqli_fetch_assoc($res2))
	   echo '<option value="'.$data2['name'].'">'.$data2['name']."</option>";
	 echo "</select>";
	 echo '</td><td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="friend">';
	 $res1 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=3 AND clanrel.id2=".$clan->getID());
	 $res2 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=3 AND clanrel.id1=".$clan->getID());
	 while ($data1 = mysqli_fetch_assoc($res1))
	   echo '<option value="'.$data1['name'].'">'.$data1['name']."</option>";
	 while ($data2 = mysqli_fetch_assoc($res2))
	   echo '<option value="'.$data2['name'].'">'.$data2['name']."</option>";
	 echo "</select>";
	 echo "</td></tr>";
	 echo '<tr class="tblbody" id="misub1">';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="peace" value=" Frieden anbieten "></td>';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delnap" value=" NAP kündigen "></td>';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delbnd" value=" B&uuml;ndnis k&uuml;ndigen "></td></tr>';
	 echo '<tr></tr>';
	 // Angebote - Fremde
	 echo '<tr class="tblhead" id="min2" style="display:none"><td colspan="2" width="33%">Friedensangebote</td><td colspan="2" width="33%">NAP-Angebote</td><td colspan="2" width="33%">B&uuml;ndnisangebote</td>';
	 echo '<tr class="tblbody" id="mis2" style="display:none"><td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="neut">';
	 $res3 = do_mysqli_query("SELECT clan.name AS name, clan.id AS id FROM req_clanrel LEFT JOIN clan ON req_clanrel.id1=clan.id WHERE req_clanrel.type=1 AND req_clanrel.id2=".$clan->getID());
	 while ($data3 = mysqli_fetch_assoc($res3))
	   echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
	 echo '<td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="nap">';
	 $res3 = do_mysqli_query("SELECT clan.name AS name, clan.id AS id FROM req_clanrel LEFT JOIN clan ON req_clanrel.id1=clan.id WHERE req_clanrel.type=2 AND req_clanrel.id2=".$clan->getID());
	 while ($data3 = mysqli_fetch_assoc($res3))
	   echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
	 echo '</select></td>';
	 echo '</select></td><td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="bnd">';
	 $res3 = do_mysqli_query("SELECT clan.name AS name, clan.id AS id FROM req_clanrel LEFT JOIN clan ON req_clanrel.id1=clan.id WHERE req_clanrel.type=3 AND req_clanrel.id2=".$clan->getID());
	 while ($data3 = mysqli_fetch_assoc($res3))
	   echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
	 echo '</select></td></tr>';
	 echo '<tr class="tblbody" id="misub2" style="display:none">';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="accpeace" value=" Frieden annehmen "><input type="submit" style="margin-top:1px; width:100%;" name="delpeace" value=" Frieden ablehnen "></td>';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="accnap" value=" NAP annehmen "><input type="submit" style="margin-top:1px; width:100%;" name="delnap" value=" NAP ablehnen "></td>';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="accbnd" value=" B&uuml;ndnis annehmen "><input type="submit" style="margin-top:1px; width:100%;" name="delbnd" value=" B&uuml;ndnis ablehnen "></td></tr>';
//	 echo '<tr class="tblbody">';
//	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delpeace" value=" Frieden ablehnen "></td>';
//	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delnap" value=" NAP ablehnen "></td>';
//	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delbnd" value=" B&uuml;ndnis ablehnen "></td></tr>';
	 echo '<tr class="tblhead" id="min3" style="display:none"><td colspan="2" width="33%">Friedensangebote</td><td colspan="2" width="33%">NAP-Angebote</td><td colspan="2" width="33%">B&uuml;ndnisangebote</td>';
	 echo '<tr class="tblbody" id="mis3" style="display:none"><td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="reqneut">';
	 $res3 = do_mysqli_query("SELECT clan.name AS name, clan.id AS id FROM req_clanrel LEFT JOIN clan ON req_clanrel.id2=clan.id WHERE req_clanrel.type=1 AND req_clanrel.id1=".$clan->getID());
	 while ($data3 = mysqli_fetch_assoc($res3))
	   echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
	 echo '<td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="reqnap">';
	 $res3 = do_mysqli_query("SELECT clan.name AS name, clan.id AS id FROM req_clanrel LEFT JOIN clan ON req_clanrel.id2=clan.id WHERE req_clanrel.type=2 AND req_clanrel.id1=".$clan->getID());
	 while ($data3 = mysqli_fetch_assoc($res3))
	   echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
	 echo '</select></td>';
	 echo '</select></td><td colspan="2">';
	 echo '<select style="width:100%;" size="5" name="reqbnd">';
	 $res3 = do_mysqli_query("SELECT clan.name AS name, clan.id AS id FROM req_clanrel LEFT JOIN clan ON req_clanrel.id2=clan.id WHERE req_clanrel.type=3 AND req_clanrel.id1=".$clan->getID());
	 while ($data3 = mysqli_fetch_assoc($res3))
	   echo '<option value="'.$data3['id'].'">'.$data3['name']."</option>";
	 echo '</select></td></tr>';
	 echo '<tr class="tblbody" id="misub3" style="display:none">';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delreqpeace" value=" Frieden zur&uuml;ckziehen "></td>';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delreqnap" value=" NAP zur&uuml;ckziehen "></td>';
	 echo '<td colspan="2"><input type="submit" style="width:100%;" name="delreqbnd" value=" B&uuml;ndnis zur&uuml;ckziehen "></td></tr>';
	 echo '<tr><td></td></tr>';
	 echo '<tr class="tblhead" id="min4" style="display:none"><td colspan="4">Neue diplomatische Beziehung</td></tr>';
	 echo '<tr class="tblbody" id="mis4" style="display:none"><td>Ordensname</td>';
	// echo "<td><input type='text' name='clanname' size='12'></td>";
	 
	 echo "<td width=\"150\"><select name=\"clanname\" style=\"width:150px;\">\n";
         //	 $resX = do_mysqli_query("SELECT DISTINCT clan.id,clan.name,player.religion FROM clan LEFT JOIN player ON clan.id=player.clan WHERE player.religion=".$player->getReligion()." ORDER BY name DESC") or die(mysqli_error($GLOBALS['con']));
	 $resX = do_mysqli_query("SELECT DISTINCT clan.id,clan.name FROM clan ORDER BY name");
	   while ($dataX = mysqli_fetch_assoc($resX))
	     echo '<option value="'.$dataX['name'].'">'.$dataX['name'].'</option>';
	 echo "</select>\n";
	 echo '<td><select name="type">';
	 echo '<option value="3">B&uuml;ndnis</option>';
	 echo '<option value="2">Nichtangriffspakt</option>';

	 
	 echo '<option value="0">Krieg</option>';
	 echo '<td><input type="submit" name="newRel" value=" Senden ">';
	 echo "</select></td></tr>";
	 echo "</table>";
       } 
       else {	 
	 echo "<br /><hr>";
	 echo "<table width=\"525\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";
	 echo '<tr class="tblhead"><td colspan="3"><strong>Ordenspolitik</strong></td></tr>';
	 echo '<tr class="tblhead">';
	 echo '<td width="175" style="text-align:center; font-weight:bold;">Feinde</td>';
	 echo '<td width="175" style="text-align:center; font-weight:bold;">NAP\'s</td>';
	 echo '<td width="175" style="text-align:center; font-weight:bold;">Verb&uuml;ndete</td>';
	 echo "</tr>";
	 echo '<tr class="tblbody">';
	 echo '<td>';

	 $res1 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=0 AND clanrel.id2=".$clan->getID());
	 $res2 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=0 AND clanrel.id1=".$clan->getID());
	 while ($data1 = mysqli_fetch_assoc($res1))
	   echo '<a href="info.php?show=clan&name='.$data1['name'].'">'.$data1['name']."</a><br/>";
	 while ($data2 = mysqli_fetch_assoc($res2))
	   echo '<a href="info.php?show=clan&name='.$data2['name'].'">'.$data2['name']."</a><br/>";

	 echo '</td><td>';

	 $res1 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=2 AND clanrel.id2=".$clan->getID()) or die(mysqli_error($GLOBALS['con']));
	 $res2 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=2 AND clanrel.id1=".$clan->getID()) or die(mysqli_error($GLOBALS['con']));
	 while ($data1 = mysqli_fetch_assoc($res1))
	   echo '<a href="info.php?show=clan&name='.$data1['name'].'">'.$data1['name']."</a><br/>";
	 while ($data2 = mysqli_fetch_assoc($res2))
	   echo '<a href="info.php?show=clan&name='.$data2['name'].'">'.$data2['name']."</a><br/>";

	 echo '</td><td>';

	 $res1 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id1=clan.id WHERE clanrel.type=3 AND clanrel.id2=".$clan->getID()) or die(mysqli_error($GLOBALS['con']));
	 $res2 = do_mysqli_query("SELECT clan.name AS name FROM clanrel LEFT JOIN clan ON clanrel.id2=clan.id WHERE clanrel.type=3 AND clanrel.id1=".$clan->getID()) or die(mysqli_error($GLOBALS['con']));
	 while ($data1 = mysqli_fetch_assoc($res1))
	   echo '<a href="info.php?show=clan&name='.$data1['name'].'">'.$data1['name']."</a><br/>";
	 while ($data2 = mysqli_fetch_assoc($res2))
	   echo '<a href="info.php?show=clan&name='.$data2['name'].'">'.$data2['name']."</a><br/>";

	 echo "</td></tr>";
	 echo "</table>";
       }
	 echo "</form>";
	 
	// ORDENSBESCHREIBUNG
       if ($clan->getStatus() == 63) {
	 echo '<form name="theForm" action="'.$PHP_SELF.'" method="POST">';	   
	 echo '<table style="display:none; margin-top:4px;" id="lay4" width="500"><tr class="tblhead"><td>Ordensbeschreibung</td></tr><tr class="tblbody">';
	 echo '<td><textarea id="theText" rows="15" cols="45" style="width:100%;" name="desc">'.$clan->getDescription().'</textarea></td></tr>';
	 insertBBForm(1);
	 echo '<tr><td class="tblhead"><input type="submit" style="width:500px;" name="setdesc" value=" Ordensbeschreibung ändern "></td></tr></table>';
	 echo "</form>";
       }
       if (isset($myclan))
	 echo "Sie sind Mitglied des Ordens <b>".$myclan['name'].'</b>. (<a onClick="return confirm(\'Wirklich Austreten?\')"  href="'.$PHP_SELF.'?leave=1"><u>Austreten</u></a>)';

     } 
     else {
       echo '<form action="'.$PHP_SELF.'" method="POST">';
       $res1 = do_mysqli_query("SELECT clanapplication FROM player WHERE id=".$player->getID());
       if ($data1 = mysqli_fetch_assoc($res1))
	 if ($data1['clanapplication'] > 0) {
	   $res2 = do_mysqli_query("SELECT name FROM clan WHERE id=".$data1['clanapplication']);
	   if ($data2 = mysqli_fetch_assoc($res2))
	     echo "Sie bewerben sich um die Mitgliedschaft im Orden ".$data2['name']. '(<a href="'.$PHP_SELF.'?dropApp=1&clanapplicant='.$player->getID().'">Abbrechen</a>)';
	   else
	     echo 'Der Orden, bei dem ihr euch um die Mitgliedschaft beworbenhabt, existiert nicht mehr. (<a href="'.$PHP_SELF.'?dropApp=1&clanapplicant='.$player->getID().'">Click!</a>)';
	 } else {
	   echo "<table>";
	   echo '<tr class="tblhead"><td colspan="2">Orden gr&uuml;nden</td></tr>';
	   echo '<tr class="tblbody"><td>Ordensname</td><td><input type="text" name="clanName" size="30"></td></tr>';
	   echo '</table>';
	   echo '<input type="submit" name="newClan" value=" Gr&uuml;nden " onClick="return confirm(\'Habt Ihr den Hinweis zum Thema sinnvolle Ordensnamen gelesen?\')"><br>';
	   echo '<span style="font-size: 9px;">Die Spielleitung legt wert darauf, dass keine unsinnigen Ordensnamen verwendet werden.<br> Wir behalten es uns vor, nicht passende Orden ohne Vorwarnung umzubenennen oder gar zu löschen.';
	   echo "<p>";
	   echo "<table>";
	   echo '<tr class="tblhead"><td nowrap><b>Einem Orden beitreten</b></td><td><b>Bewerbungstext</b></td>';
	   echo "</tr>\n";
	   echo '<tr class="tblbody" valign="top"><td>';
	   echo '<select size="6" name="clanID">';
	   $res = do_mysqli_query("SELECT DISTINCT clan.id,clan.name,player.religion FROM clan LEFT JOIN player ON clan.id=player.clan WHERE player.religion=".$player->getReligion()." ORDER BY name DESC") or die(mysqli_error($GLOBALS['con']));
	   while ($data = mysqli_fetch_assoc($res))
	     echo '<option value="'.$data['id'].'">'.$data['name'].'</option>';
	   echo "</select></td>\n";
	   echo '<td rowspan="2"><textarea name="applText" rows="8" cols="50">';
	   if (isset($applText)) echo $applText;
	   echo "</textarea></td></tr>";
	   echo "</table>";
	   echo '<input '.($player->getNoobLevel() > 0 ? 'onClick="return confirm(\'In einem Orden wirkt der Neulingsschutz nicht, falls der Orden Krieg führt. Seid Ihr also sicher, dass Ihr beitreten möchtet?\')"': "").' type="submit" name="joinClan" value=" Beitreten ">';
	 }
       echo "</form>";
     }

     
end_page();
?>
