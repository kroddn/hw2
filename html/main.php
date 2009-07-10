<?php
/***************************************************
 * Copyright (c) 2005-2008 by Holy-Wars2.de
 *
 * written by Markus Sinner, Gordon Meiser
 * 
 ***************************************************/
include_once("includes/session.inc.php");
include_once("includes/news.inc.php");
include_once("includes/tournament.inc.php");
$player->setActivePage(basename(__FILE__));

start_page();
?>


<script language="JavaScript">
<!--
function setjs() {
 if(navigator.product == 'Gecko') {
   document.loginform["interface"].value = 'mozilla';
 }else if(navigator.appName == 'Microsoft Internet Explorer' &&
    navigator.userAgent.indexOf("Mac_PowerPC") > 0) {
    document.loginform["interface"].value = 'konqueror';
 }else if(navigator.appName == 'Microsoft Internet Explorer' &&
 document.getElementById && document.getElementById('ietest').innerHTML) {
   document.loginform["interface"].value = 'ie';
 }else if(navigator.appName == 'Konqueror') {
    document.loginform["interface"].value = 'konqueror';
 }else if(window.opera) {
   document.loginform["interface"].value = 'opera';
 }
}
function updateframe() {
	top.res.location.href="top.php";
}
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

<style type="text/css">
.tooltip {
	position: absolute;
	display: none;
	background-color: #FFFFFF;
	border:1px solid black;
}
</style>
<?php
start_body(false);

function getClanStatusInNames($clanstatus) {
	$stat=" Member ";
	if ($clanstatus == 63)
		$stat = " Ordensleiter ";
	if ($clanstatus == 1)
		$stat = " Finanzminister ";
	if ($clanstatus == 2)
		$stat = " Innenminister ";
  if ($clanstatus == 3)
		$stat = " Finanzminister, Innenminister ";
	if ($clanstatus == 4)
		$stat = " Au&szlig;enminister ";
  if ($clanstatus == 5)
		$stat = " Finanzminister, Au&szlig;enminister ";
	if ($clanstatus == 6)
		$stat = " Innenminister, Au&szlig;enminister ";
	if ($clanstatus == 7)
		$stat = " Finanzminister, Au&szlig;enminister, Innenminister ";

	return $stat;
}
function getNewClanfTopics($player) {
	$string="<a href=\"cf_index.php\" style=\"color:black;\" onclick=\"updateframe();\" target=\"_self\" class=\"statusline\">Keine neuen Forenbeitr&auml;ge</a>";
	//get players last forum visit
	$res2=mysql_query("SELECT user_lastvisit FROM clanf_users WHERE username='".intval($player)."'");
	$data2=mysql_fetch_assoc($res2);
	$lastseen=date("d.m.Y H:i",$data2['user_lastvisit']);

	//get cat_id of players clan (put it in session next review)
	$res1=mysql_query("SELECT cat_id FROM clanf_categories WHERE cat_order = '".$_SESSION['player']->clan."'");
	$data1=mysql_fetch_assoc($res1);
	$clan_cat=$data1['cat_id'];

	$res3=mysql_query("SELECT clanf_posts.post_time as lastpost FROM `clanf_posts` LEFT JOIN clanf_forums ON clanf_forums.forum_id = clanf_posts.forum_id WHERE clanf_forums.cat_id='".$clan_cat."' AND clanf_posts.post_time > '".$data2['user_lastvisit']."'");
	$data3=mysql_fetch_assoc($res3);
	if(mysql_num_rows($res3)>0)
		$string="<a href=\"cf_search.php?search_id=newposts\" target=\"_self\" onclick=\"updateframe();\" style=\"color:black;\" class=\"statusline\">Neue Forenbeitr&auml;ge</a>";
	return $string;
}

if($player->getNewMessages() < 1) {
  $newmsg="Ihr habt <a href=\"messages.php\"><b>keine</b></a> neuen Nachrichten!";
} elseif($player->getNewMessages() == 1) {
  $newmsg="Ihr habt <a href=\"messages.php\"><b>eine neue</b></a> Nachricht!";
} else {
  $newmsg="Ihr habt <a href=\"messages.php\"><b>".$player->getNewMessages()." neue</b></a> Nachrichten!";
}
?>

<center>
<table id="maininfo" cellpadding="0" cellspacing="0" width="550" border="0" align="center">

<tr><td valign="top" colspan="2" align="center"> 
<a href="settings.php?show=account" title="Hier klicken, um für HW2 zu werben">
  <img border="0" alt="Holy-Wars 2 Banner" width="468" height="60"
   src="/hw2_banner.php/1.gif">
</a>
</td></tr>

<tr><td valign="top">
<!-- Zeitung -->
<div class="box" style="border: 1px solid #606060;">
<table cellpadding="1" cellspacing="1" width="100%">
<tr class="tblhead"><td colspan="2" height="25" align="center" valign="middle">
Der <span style="font-size: 16px; font-weight: bold;">Holy-Wars 2 Bote</span> - <span style="color:#CC2A20; font-size: 18px; font-weight: bold;"><blink>15. Ausgabe!</blink></span>
</td></tr>
<tr class="tblbody">
 <td>
 Neuigkeiten aus den Heiligen L&auml;ndern!
 <br>
   <a target="_blank" href="http://www.holy-wars2.de/wiki/index.php/Zeitung">
    <img border="0" src="http://www.holy-wars2.de/Bote.jpg">
    
   </a>
 </td>
 <td>
 <b>Redaktion:</b><br>
 Hauptschreiberling SirUllrich<br/>
 &lt;- <a target="_blank" href="http://www.holy-wars2.de/wiki/index.php/Zeitung">Bringt mir die Zeitung!</a>
 </td>
 </tr>
</table>
</div>

<?
if (defined("ENABLE_TOURNAMENT") && ENABLE_TOURNAMENT) {
  $t = do_mysql_query_fetch_assoc("SELECT time, time < UNIX_TIMESTAMP() AND time + ".TOURNAMENT_DURATION." > UNIX_TIMESTAMP() AS now ".
				  " FROM tournament WHERE time + ".TOURNAMENT_DURATION." > UNIX_TIMESTAMP() ORDER BY time ASC LIMIT 1");
    ?>

  <div class="box" style="border: thin black solid;padding-bottom: 2px;" align="center">
  <?
  user_online_table();
  ?>
  </div>

<? 
  if(!defined("HISPEED") || !HISPEED) { 
?>
  <div class="box" style="border: thin red solid;padding-bottom: 2px; padding-top: 2px;" align="center">
    <span style="color: red; font-size: 16px;">Ritterturniere!</span><br>
     <? if($t) echo "Nächstes Turnier findet ".($t['now'] ? '<font color="red"><b>JETZT</b></font>' : "um ".date("d.m.y H:i", $t['time']))." statt.<br>"; ?>
     <a href="tournament.php">Klicke hier, um Ruhm und Reichtum zu erlangen.</a>
     <br>
   </div>  
<? 
  } // if(!defined("HISPEED") || !HISPEED)
}

if($player->getGFX_PATH() == NULL || !$_SESSION['hwathome']) 
{
?>
 <div class="box" style="border: thin red solid;" align="center">
    <span style="color: #DD0000; font-size: 16px;">Sie haben keinen Lokalen HW Ordner gesetzt.</span><br> 
    Bitte laden Sie sich das neueste Grafikpaket herunter!<br>
    (<a href="grafikpaket/">Download</a>)  (<a href="settings.php#grapa">Installation</a>).<br>
    Durch den Einsatz des Grafikpaketes beschleunigt sich der Aufbau der Seiten deutlich. Ausserdem schonen Sie damit unseren Server.
 </div>
<?
}

if (defined("HW2DEV")) {
  echo "<div class=\"box\" style=\"border: 1px solid blue;\"><center><h1 style=\"color:blue;\">ACHTUNG: dies ist die HW2 Entwicklungsversion (HW2DEV)!</h1></center></div>";
}

if (BOOKING_ALLOWED) {
  echo '<div class="box" style="border: thin red solid; " align="center">';
  echo "<h1 class='error'>Voranmeldung zu <u>".BOOKING_ROUND_NAME."</u> online!</h1>\n";
  echo '<a href="booking.php">Du kannst Dich über diesen Link für die kommende Runde voranmelden.</a>';
  echo "\n</div>\n";
}

echo "</td><td valign=\"top\">";
define("VOTE_VERTICAL", 1); 
include ("includes/vote.inc.php"); 
echo "</td>";

echo "</tr>";


// table 3
if($_GET['news']) {
  $resnews=do_mysql_query("SELECT topic,text FROM news WHERE id='".intval($_GET['news'])."'");
}
else {
  $resnews=do_mysql_query("SELECT topic,text FROM news ORDER BY time DESC");
}
$dataNews=mysql_fetch_assoc($resnews);

echo "<tr><td colspan=\"2\" style=\"padding: 0px;\">\n";


echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">\n";
echo "<tr class=\"tblhead\" height=\"25\"><td width=\"210\" style=\"font-weight: bold; font-size:16px; color: #AF4080;\">Neuigkeiten</td><td align=\"center\" style=\"font-weight: bold; font-size:16px;\"><u>".$dataNews['topic']."</u></td></tr>\n";
echo "<tr valign=\"top\" class=\"tblhead\"><td style=\"padding: 3px; \">\n";
$resN=do_mysql_query("SELECT id, time, topic FROM news ORDER BY id DESC");
while($dataN=mysql_fetch_assoc($resN)) {
  echo "<span style=\"float:left;\"><a href=\"".$PHP_SELF."?news=".$dataN['id']."\">".$dataN['topic']."</a></span><span style=\"float:right;\">".date("d.m.Y",$dataN['time'])."</span><br>";
}
echo "</td><td class=\"tblbody\" style=\"padding: 3px;\">";
echo $dataNews['text'];
echo "</td></tr>\n";
echo "</table>\n";

echo "</tr>\n";

echo "<tr><td colspan=\"2\" style=\"padding: 0px;\">\n";

// table 4
echo "<table width=\"100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\">\n";
	echo "<tr><td valign=\"top\">\n";
	echo "<table width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">\n";
	echo "<tr class=\"tblhead\"><td height=\"20\" colspan=\"2\"><b>MyLord ".$_SESSION['player']->name."! Ihr seid zur&uuml;ck!</b></td></tr>";
	echo "<tr class=\"tblbody\"><td>".$newmsg."</td>\n";
	echo "<td>".getNewClanfTopics($_SESSION['player']->name)."</td></tr>\n";

// toplist player
	echo "<tr><td class=\"tblbody\" height=\"25\" colspan=\"2\">\n";
	$restoplist=do_mysql_query("SELECT toplist FROM player WHERE id='".$_SESSION['player']->id."'");
	$toplist=mysql_fetch_assoc($restoplist);
	$pos=$toplist['toplist'];
	if($pos != NULL || pos != 0) {
		if(pos == 1) {echo "MyLord! Ihr seid <b>der Beste!</b> Seht selbst in der <a href=\"toplist.php\">Toplist</a>!";}
		elseif($pos <= 10) {echo "MyLord! Ihr seid <b>".$toplist['toplist']."ter</b> und damit einer der besten Spieler! Seht selbst in der <a href=\"toplist.php\">Toplist</a>!";}
		elseif($pos <= 20) {echo "MyLord! Ihr seid als <b>".$toplist['toplist']."ter</b> unter den besten 20 Spielern in der <a href=\"toplist.php\">Toplist</a> vertreten!";}
		elseif($pos <= 50) {echo "MyLord! Ihr seid als <b>".$toplist['toplist']."ter</b> unter den besten 50 Spielern in der <a href=\"toplist.php\">Toplist</a> vertreten!";}
		elseif($pos >= 50) {echo "MyLord! Ihr seid als <b>".$toplist['toplist']."ter</b> in der <a href=\"toplist.php\">Toplist</a> vertreten!";}
	} else {
		echo "MyLord! Euer Name scheint in der <a href=\"toplist.php\">Toplist</a> nicht auf!! Im <a href=\"chat.php\">IRC-Chat</a> k&ouml;nnt Ihr Eure Platzierung erfragen.";
	}
	echo "</td></tr>\n";

// toplist clan
	echo "<tr><td class=\"tblbody\" height=\"25\" colspan=\"2\">\n";
	$restoplist=do_mysql_query("SELECT toplist FROM clan WHERE id='".$_SESSION['player']->clan."'");
	$toplist=mysql_fetch_assoc($restoplist);
	$pos=$toplist['toplist'];
	if($pos != NULL || pos != 0) {
		if(pos == 1) {echo "MyLord! Ihr geh&ouml;rt <b>dem Besten Orden</b> an! Seht selbst in der <a href=\"toplist.php?show=clan\">Toplist</a>!";}
		elseif($pos <= 10) {echo "MyLord! Euer Orden ist der <b>".$toplist['toplist']."t</b> Beste! Seht selbst in der <a href=\"toplist.php?show=clan\">Toplist</a>!";}
		elseif($pos <= 20) {echo "MyLord! Eurer Orden ist als <b>".$toplist['toplist']."ter</b> unter den Besten 20 Orden! Die <a href=\"toplist.php?show=clan\">Toplist</a> beweist es!";}
		elseif($pos <= 50) {echo "MyLord! Euer Orden ist in der <a href=\"toplist.php?show=clan\">Toplist</a> als ".$toplist['toplist']."ter vertreten!";}
	} else {
		echo "MyLord! Euer Orden ist nicht in der <a href=\"toplist.php?show=clan\">Toplist</a> vertreten!";
	}
	echo "</td></tr>\n";

	echo "<tr class=\"tblhead\"><td height=\"20\" colspan=\"2\"><b>Dies solltet Ihr wissen</b></td></tr>";
	echo "<tr class=\"tblbody\"><td colspan=\"2\">";
// builing troups and buildings
 	$buildings_res = do_mysql_query("SELECT building.name AS bname,citybuilding_ordered.time AS time,citybuilding_ordered.count AS count,city.name AS name,city.id FROM building,city,citybuilding_ordered WHERE citybuilding_ordered.city=city.id AND building.id=citybuilding_ordered.building AND city.owner=".$_SESSION['player']->id);
  	$troops_res = do_mysql_query("SELECT cityunit_ordered.count AS count,cityunit_ordered.time AS time,unit.name AS uname,city.name AS name,city.id as id FROM cityunit_ordered,city,unit WHERE cityunit_ordered.city=city.id AND cityunit_ordered.unit=unit.id AND city.owner=".$_SESSION['player']->id);
	$prf_building = "sind";
	$troops="Truppen";
	if(mysql_num_rows($troops_res) == 1)
		$troops = "Truppe";

        if(mysql_num_rows($buildings_res) < 1) {
          $num1="sind keine Auftr&auml;ge";
	} elseif(mysql_num_rows($buildings_res) == 1) {
          $num1="ist ein Auftrag";
	} else {
          $num1="sind ".mysql_num_rows($buildings_res)." Aufträge";
	}

	if(mysql_num_rows($troops_res)==1) {
		$num2="eine";
	} elseif(mysql_num_rows($troops_res)<1) {
		$num2="keine";
	} else {
		$num2=mysql_num_rows($troops_res);
	}
	echo "Im Moment ".$num1." für Geb&auml;ude aktiv  und ".$num2." ".$troops." in Bau!";
	echo "</td></tr>\n";
	echo "<tr class=\"tblbody\"><td colspan=\"2\">\n";
// population
		$resP=do_mysql_query("SELECT sum(population) as population FROM city WHERE owner='".$_SESSION['player']->id."'");
		$dataP=mysql_fetch_assoc($resP);
	echo $dataP['population']." Einwohner bewohnen Eure St&auml;dte!\n";
	echo "</td></tr>\n";
	echo "<tr class=\"tblhead\"><td colspan=\"2\" height=\"20\"><b>Truppenaufkl&auml;rung</b></td></tr>\n";
	echo "<tr class=\"tblbody\"><td colspan=\"2\" height=\"25\">\n";
// running troops
$scouttime = $player->getScoutTime();
if($scouttime > 0) {
  $res_spy = do_mysql_query("SELECT ".
			    " army.aid AS aid, army.owner AS owner, army.end, endtime, mission ".
			    " FROM army ".
			    " LEFT JOIN city ON army.end = city.id ".
			    " LEFT JOIN citybuilding ON city.id=citybuilding.city ".
			    " LEFT JOIN building ON building.id = citybuilding.building ".
			    " WHERE city.owner = ".$player->getID().
			    " AND city.owner <> army.owner ".
			    " GROUP BY army.aid, res_scouttime HAVING army.endtime <= (UNIX_TIMESTAMP() + max(res_scouttime)) ".
			    " ORDER BY endtime ASC" );
  if(mysql_num_rows($res_spy)>0) {
    while($data_spy=mysql_fetch_assoc($res_spy)) {
      $res_spy_unit=mysql_query("SELECT unit, count FROM armyunit WHERE aid = ".$data_spy['aid']);
      $remaining = $data_spy['endtime'] - time();
      $timerline = "<span class='noerror' id='".$data_spy['aid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$data_spy['aid'].");</script></span>";

      //FIXME: Code ist redundant / wird so auch in general.php eingesetzt
      //       verschmelzen in eien Funktion!
      if($remaining > 0) {
        echo 'Eine Armee des Spielers <b>'.resolvePlayerName($data_spy['owner']).'</b> erreicht Eure Stadt <b>'.resolveCityName($data_spy['end']).'</b> in '.$timerline."<br>\n";

      }
      else {
        echo 'Eine Armee des Spielers <b>'.resolvePlayerName($data_spy['owner']).'</b> ';
        
        if($data_spy['mission'] == 'siege') {
          $siegetime = ceil(-$remaining/60);
          echo "belagert Eure Stadt <b>".resolveCityName($data_spy['end'])."</b> seit $siegetime Minuten ";
        }
        else {
          echo "steht vor Eurer Stadt <b>".resolveCityName($data_spy['end']).'</b> in '.$timerline."<br>\n";
        }
        echo "<br>\n";
      }
      
    }
  }
  else echo "Es sind keine feindlichen Armeen im Anmarsch!";
 }
 else echo "Ihr habt kein Aufkl&auml;rungsgeb&auml;ude errichtet!";

	echo "</td></tr>\n";
echo "</table>\n";



echo "</td></tr>\n";
echo "</table>\n";
echo "</center>";

if (!is_premium_noads() && 
    strstr($HTTP_SERVER_VARS['HTTP_REFERER'], "index.php")) {
  $timemod = time() % 3600;
  
  if($timemod < 3300) {
    include("ads/openinventory_popup.php");
  }
  else {
    // Check Deinen Sex
    include("includes/sponsorads-framelayer.html");
  }
}


end_page();


function user_online_table() {
  // user online table
  $show_online_list = false;
  $i=0;
  $res1=do_mysql_query("SELECT player.clanstatus as clstatus, player_online.uid AS id, player.clan AS clan, player.name AS name, player_online.lastclick AS click FROM player_online LEFT JOIN player ON player_online.uid=player.id WHERE player_online.lastclick >= (UNIX_TIMESTAMP() - 300) ORDER BY player.clanstatus DESC");

  echo "<table cellpadding=\"2\" cellspacing=\"1\" width=\"100%\">";
  echo "<tr class=\"tblhead\"><td valign=\"middle\" colspan=\"3\"><b>Spieler online</b></td></tr>\n";
  echo "<tr class=\"tblbody\"><td valign=\"middle\" colspan=\"3\">Momentan sind ".mysql_num_rows($res1)." Spieler online!</td></tr>";

  if($_SESSION['player']->clan > 0) {
    echo "<tr class=\"tblhead\"><td height=\"20\" valign=\"middle\" colspan=\"3\"><b>Folgende Ordensbr&uuml;der sind online</td></tr>";
	  
    while($data1=mysql_fetch_assoc($res1)) {
      if($data1['clan']==$_SESSION['player']->clan && $data1['id'] != $_SESSION['player']->getID()) {
        echo "<tr class=\"tblbody\">\n";
        echo "<td class=\"tblhead\">".get_info_link($data1['name'], "player",1)."</td>\n";
        echo "<td width=\"80\">".getClanStatusInNames($data1['clstatus'])."</td>";
        echo "<td width=\"110\"><a href=\"messages2.php?msgrecipient=".$data1['name']."\">Nachricht senden</a></td>\n";
        $i++;
        echo "</tr>\n";
      }
    }
    if($i==0) {
      echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"30\" valign=\"middle\">Keine!</td></tr>";
    }
  }
	
	
	
  // wenn geheimdienst errichtet
  if($show_online_list) {
    $res2=do_mysql_query("SELECT research FROM playerresearch WHERE player='".$_SESSION['player']->id."' AND research='111'");
    if(mysql_num_rows($res2) > 0) {
      echo "<tr class=\"tblhead\"><td height=\"20\" valign=\"middle\" colspan=\"3\"><b>Eurer Geheimdienst berichtet</td></tr>";
      echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"30\" valign=\"middle\">\n";
      $output="";
      $res1=do_mysql_query("SELECT player.clanstatus as clstatus, player_online.uid AS id, player.clan AS clan, player.name AS name, player_online.lastclick AS click FROM player_online LEFT JOIN player ON player_online.uid=player.id WHERE player_online.lastclick >= (UNIX_TIMESTAMP() - 300) ORDER BY player.clanstatus DESC");
      $enemy=0;
      $friend=0;
      while($data1=mysql_fetch_assoc($res1)) {
        if($data1['clan'] != $_SESSION['player']->clan) {
          $output.="<tr class=\"tblbody\">\n";
          $output.="<td class=\"tblhead\">".get_info_link($data1['name'], "player",1)."</a></td>\n";
          $res3=do_mysql_query("SELECT type FROM relation WHERE id1='".$_SESSION['player']->id."' AND id2='".$data1['id']."'");
          $res4=do_mysql_query("SELECT type FROM relation WHERE id2='".$_SESSION['player']->id."' AND id1='".$data1['id']."'");
          if(mysql_num_rows($res3)>0) {$data5=mysql_fetch_assoc($res3);}
          if(mysql_num_rows($res4)>0) {$data5=mysql_fetch_assoc($res4);}
          $output.="<td width=\"80\">";
          if(mysql_num_rows($res3)>0 || mysql_num_rows($res4)>0) {
            if($data5['type']==0) {
              $output.="Feind";
              $enemy=$enemy+1;
            }
            if($data5['type']==2) {
              $output.="Verb&uuml;ndeter";
              $friend=$friend+1;
            }
          }
          $output.="</td>";
          $output.="<td width=\"110\" class=\"tblhead\"><a href=\"messages2.php?msgrecipient=".$data1['name']."\">Nachricht senden</a></td>\n";
          $i++;
          $output.="</tr>\n";
          unset($data5);
        }
      }
      if($enemy==0) {
        echo "Im Moment sind <b>keine Feinde</b> und ";
      } elseif($enemy==1) {
        echo "Im Moment ist <b>ein Feind</b> und ";
      } else {
        echo "Im Moment sind <b>".$enemy." Feinde</b> und ";
      }
      if($friend==0) {
        echo "<b>keine Verb&uuml;ndeten</b> online!";
      } elseif($friend==1) {
        echo "<b>ein Verb&uuml;ndeter</b> online!";
      } else {
        echo "<b>keine Verb&uuml;ndeten</b> online!";
      }

      echo "</td></tr>";
      echo $output;
    }
  } // show_online_list
  else {
    echo "\n<!-- Liste der Online-Spieler ist auf unbestimmte Zeit deaktiviert.-->\n";
    //echo "<tr class=\"tblbody\"><td colspan=\"3\" height=\"30\" valign=\"middle\">Liste der Online-Spieler ist auf unbestimmte Zeit deaktiviert.</td></tr>";
  }
	
  echo "</table>\n";
  // end user online
}

?>

