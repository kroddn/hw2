<?php
/***************************************************
 * Copyright (c) 2003-2005
 *
 * Stefan Neubert
 * Stefan Hasenstab
 * Gordon Meiser
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/market.class.php");
include_once("includes/session.inc.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");
include_once("includes/sms.func.php");

$player->setActivePage(basename(__FILE__));

if(isset($category)) {
  $player->setActiveMsgcategory($category);
}

$sizecategory = 10000;
$sizepostfach = get_message_archive_size();
//Falls Kategory = Gesendete Nachrichten, dann nachprüfen wieviele Nachrichten man max haben darf
if($player->getActiveMsgcategory() == MESSAGECAT_SENT)
$sizecategory = $sizepostfach;

if(isset($delmarked) && sizeof($mark)>0){
  // Player-ID als Sicherheit
  $sql="UPDATE message SET status=status | ".MSG_RECIPIENT_DELETED."  WHERE recipient=".$player->getID()." AND id IN (";
  for ($i=0;$i<sizeof($mark);++$i) {
    if ($i>0) $sql.=",";
    $sql.=$mark[$i];
  }
  $sql.=")";
  do_mysql_query($sql);

  if($sizepostfach != 0)
  {
    $sql="UPDATE message SET status=status | ".MSG_SENDER_DELETED." WHERE sender='".$player->getName()."' AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($mark);++$i) {
      if ($i>0) $sql.=",";
      $sql.=$mark[$i];
    }
    $sql.=")";
    do_mysql_query($sql);
    // Wurden welche gelöscht, dann Postausgang neu berechnen
    if (mysql_affected_rows() > 0) {
      $player->messages_sent = -1;
    }
  }

  do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$_SESSION['player']->getID());
}

if(isset($readmarked) && sizeof($mark)>0){
  // Player-ID als Sicherheit
  $sql="UPDATE message SET status=status | ".MSG_RECIPIENT_READ." WHERE recipient=".$player->getID()." AND id IN (";
  for ($i=0;$i<sizeof($mark);++$i) {
    if ($i>0) $sql.=",";
    $sql.=$mark[$i];
  }
  $sql.=")";
  do_mysql_query($sql);

  if($sizepostfach != 0)
  {
    // Player-ID als Sicherheit
    $sql="UPDATE message SET status=status | ".MSG_SENDER_READ." WHERE sender='".$player->getName()."' AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($mark);++$i) {
      if ($i>0) $sql.=",";
      $sql.=$mark[$i];
    }
    $sql.=")";
    do_mysql_query($sql);
  }

  do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$_SESSION['player']->getID());
}
 
if(isset($mailmarked)){
  if (sizeof($mark)>0) {

    /* Absender */
 	$sender = "Mail-Center Holy-Wars 2 <noreply@holy-wars2.de>";

 	/* Betreff */
 	$subject = "Holy-Wars 2 - Nachrichtensicherung per E-Mail";

 	/* Baut Header der Mail zusammen */
 	$headers .= "From:" . $sender . "\n";
 	$headers .= "X-Mailer: PHP/" . phpversion(). "\n";
 	$headers .= "Content-type: text/html\n";

 	//Mailtext erstellen
 	$mailtext = "<html><head></head><body bgcolor=\"C3AB7C\"><table bgcolor=\"C3AB7C\">";
 	$mailtext .= "<tr><td colspan=2>Hinweis: Die nun folgenden Nachrichten sind in Kategorien unterteilt. Innerhalb dieser sind die Nachrichten nach Datum (absteigend) sortiert.</td></tr><tr><td colspan=2>&nbsp;</td></tr></table>";

  	//Kategorien auslesen
  	$sql0="SELECT category as cat, sender FROM message WHERE (recipient = ".$_SESSION['player']->getID()." OR sender = '".$_SESSION['player']->getName()."') AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($mark);++$i) {
      if ($i>0) {
        $sql0.=",";
      }
      $sql0.=$mark[$i];
      $marked[$i]['id']=$mark[$i];
    }
    $sql0.=") ORDER by date DESC";
    $getsql = do_mysql_query($sql0);
    $i=0;
    while ($getcat = do_mysql_fetch_assoc($getsql)) {
      $marked[$i]['cat']=$getcat['cat'];
      if ($getcat['sender']==$_SESSION['player']->getName()) {
      	$marked[$i]['sended']=1;
      }
      else {
        $marked[$i]['sended']=0;
      }
      $i++;
    }


    // Spielernachrichten auslesen
    $playermes = 0;
    $sql1="SELECT recipient, date, header, body FROM message WHERE recipient = ".$_SESSION['player']->getID()." AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($marked);++$i) {
	  if (($marked[$i]['cat']==0) && ($marked[$i]['sended']==0)) {
        if ($playermes>0) {$sql1.=",";}
        $sql1.=$marked[$i]['id'];
        $playermes+=1;
      }
    }
    $sql1.=") ORDER by date DESC";
    if ($playermes>0) {
        $getplayermessages = do_mysql_query($sql1);
          $mailtext .= "<table bgcolor=".$messagecolors[0].">";
          $mailtext .= "<tr><td colspan=2><b>Kategorie: Spieler</b></td></tr>";
          $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          while ($playermessages = do_mysql_fetch_assoc($getplayermessages)) {
            $mailtext .= "<tr><td valign=\"top\"><b>Absender</b></td>";
            $mailtext .= "<td>".$playermessages['sender']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Datum</b></td>";
            $mailtext .= "<td>".date("d.m.Y H:i:s",$playermessages['date'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Betreff</b></td>";
            $mailtext .= "<td>".$playermessages['header']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Nachricht</b></td>";
            $mailtext .= "<td>".bbcode($playermessages['body'])."</td></tr>";
            $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          }
          $mailtext .= "<tr><td colspan=2>&nbsp;</td></tr></table>";
	}

	// Orden-/Diplomatie-Nachrichten auslesen
	$clandiplomes = 0;
    $sql2="SELECT sender, date, header, body FROM message WHERE recipient = ".$_SESSION['player']->getID()." AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($marked);++$i) {
	  if ($marked[$i]['cat']==1 && $marked[$i]['id']) {
        if ($clandiplomes>0) {$sql2.=",";}
        echo "<!-- ".$marked[$i]['id']." -->\n";
        $sql2.=$marked[$i]['id'];
        $clandiplomes+=1;
      }
    }
    $sql2.=") ORDER by date DESC";
    if ($clandiplomes>0) {
      echo "<!-- $sql2 -->";
      $getclandiplomessages = do_mysql_query($sql2);
          $mailtext .= "<table bgcolor=".$messagecolors[1].">";
          $mailtext .= "<tr><td colspan=2><b>Kategorie: Orden/Diplomatie</b></td></tr>";
          $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          while ($clandiplomessages = do_mysql_fetch_assoc($getclandiplomessages)) {
            $mailtext .= "<tr><td valign=\"top\"><b>Absender</b></td>";
            $mailtext .= "<td>".$clandiplomessages['sender']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Datum</b></td>";
            $mailtext .= "<td>".date("d.m.Y H:i:s",$clandiplomessages['date'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Betreff</b></td>";
            $mailtext .= "<td>".$clandiplomessages['header']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Nachricht</b></td>";
            $mailtext .= "<td>".bbcode($clandiplomessages['body'])."</td></tr>";
            $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          }
          $mailtext .= "<tr><td colspan=2>&nbsp;</td></tr></table>";
	}

	// Handelsnachrichten auslesen
	$marketmes = 0;
    $sql3="SELECT sender, date, header, body FROM message WHERE recipient = ".$_SESSION['player']->getID()." AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($marked);++$i) {
	  if ($marked[$i]['cat']==2) {
        if ($marketmes>0) {$sql3.=",";}
        $sql3.=$marked[$i]['id'];
        $marketmes+=1;
      }
    }
    $sql3.=") ORDER by date DESC";
    if ($marketmes>0) {
        $getmarketmessages = do_mysql_query($sql3);
          $mailtext .= "<table bgcolor=".$messagecolors[2].">";
          $mailtext .= "<tr><td colspan=2><b>Kategorie: Handel</b></td></tr>";
          $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          while ($marketmessages = do_mysql_fetch_assoc($getmarketmessages)) {
            $mailtext .= "<tr><td valign=\"top\"><b>Absender</b></td>";
            $mailtext .= "<td>".$marketmessages['sender']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Datum</b></td>";
            $mailtext .= "<td>".date("d.m.Y H:i:s",$marketmessages['date'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Betreff</b></td>";
            $mailtext .= "<td>".$marketmessages['header']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Nachricht</b></td>";
            $mailtext .= "<td>".bbcode($marketmessages['body'])."</td></tr>";
            $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          }
          $mailtext .= "<tr><td colspan=2>&nbsp;</td></tr></title>";
	}

	// Baunachrichten auslesen
	$buildmes = 0;
    $sql4="SELECT sender, date, header, body FROM message WHERE recipient = ".$_SESSION['player']->getID()." AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($marked);++$i) {
	  if ($marked[$i]['cat']==3) {
        if ($buildmes>0) {$sql4.=",";}
        $sql4.=$marked[$i]['id'];
        $buildmes+=1;
      }
    }
    $sql4.=") ORDER by date DESC";
    if ($buildmes>0) {
        $getbuildmessages = do_mysql_query($sql4);
          $mailtext .= "<table bgcolor=".$messagecolors[3].">";
          $mailtext .= "<tr><td colspan=2><b>Kategorie: Bau</b></td></tr>";
          $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          while ($buildmessages = do_mysql_fetch_assoc($getbuildmessages)) {
            $mailtext .= "<tr><td valign=\"top\"><b>Absender</b></td>";
            $mailtext .= "<td>".$buildmessages['sender']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Datum</b></td>";
            $mailtext .= "<td>".date("d.m.Y H:i:s",$buildmessages['date'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Betreff</b></td>";
            $mailtext .= "<td>".$buildmessages['header']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Nachricht</b></td>";
            $mailtext .= "<td>".bbcode($buildmessages['body'])."</td></tr>";
            $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          }
          $mailtext .= "<tr><td colspan=2>&nbsp;</td></tr></table>";
	}

	// Militärrichten auslesen
	$warmes = 0;
    $sql5="SELECT sender, date, header, body FROM message WHERE recipient = ".$_SESSION['player']->getID()." AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($marked);++$i) {
	  if ($marked[$i]['cat']==4) {
        if ($warmes>0) {$sql5.=",";}
        $sql5.=$marked[$i]['id'];
        $warmes+=1;
      }
    }
    $sql5.=") ORDER by date DESC";
	if ($warmes>0) {
        $getwarmessages = do_mysql_query($sql5);
          $mailtext .= "<table bgcolor=".$messagecolors[4].">";
          $mailtext .= "<tr><td colspan=2><b>Kategorie: Militär</b></td></tr>";
          $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          while ($warmessages = do_mysql_fetch_assoc($getwarmessages)) {
            $mailtext .= "<tr><td valign=\"top\"><b>Absender</b></td>";
            $mailtext .= "<td>".$warmessages['sender']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Datum</b></td>";
            $mailtext .= "<td>".date("d.m.Y H:i:s",$warmessages['date'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Betreff</b></td>";
            $mailtext .= "<td>".$warmessages['header']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Nachricht</b></td>";
            $mailtext .= "<td>".bbcode($warmessages['body'])."</td></tr>";
            $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          }
          $mailtext .= "<tr><td colspan=2>&nbsp;</td></tr></table>";
	}

	 // Gesendet Nachrichten auslesen
    $sendedmes = 0;
    $sql6="SELECT sender, recipient, date, header, body FROM message WHERE sender = '".$_SESSION['player']->getName()."' AND date>=".$player->getRegTime()." AND id IN (";
    for ($i=0;$i<sizeof($marked);++$i) {
	  if (($marked[$i]['cat']==0) && ($marked[$i]['sended']==1)) {
        if ($sendedmes>0) {$sql6.=",";}
        $sql6.=$marked[$i]['id'];
        $sendedmes+=1;
      }
    }
    $sql6.=") ORDER by date DESC";
    if ($sendedmes>0) {
      //do_mysql_num_rows($getsendedmessages) > 0) {
      $getsendedmessages = do_mysql_query($sql6);

          $mailtext .= "<table bgcolor=".$messagecolors[5].">";
          $mailtext .= "<tr><td colspan=2><b>Kategorie: Gesendete Nachrichten</b></td></tr>";
          $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          while ($sendedmessages = do_mysql_fetch_assoc($getsendedmessages)) {
            $mailtext .= "<tr><td valign=\"top\"><b>Absender</b></td>";
            $mailtext .= "<td>".$sendedmessages['sender']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Empfänger</b></td>";
            $mailtext .= "<td>".resolvePlayerName($sendedmessages['recipient'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Datum</b></td>";
            $mailtext .= "<td>".date("d.m.Y H:i:s",$sendedmessages['date'])."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Betreff</b></td>";
            $mailtext .= "<td>".$sendedmessages['header']."</td></tr>";
            $mailtext .= "<tr><td valign=\"top\"><b>Nachricht</b></td>";
            $mailtext .= "<td>".bbcode($sendedmessages['body'])."</td></tr>";
            $mailtext .= "<tr><td colspan=2><hr></td></tr>";
          }
          $mailtext .= "<tr><td colspan=2>&nbsp;</td></tr></table>";
	}
	/*$mailtext .= "<tr><td colspan=2>playermes=".$playermes.",playermes=".$playermes.",clandiplomes=".$clandiplomes.",buildmes=".$buildmes.",marketmes=".$marketmes.",warmes=".$warmes.", sizeof(marked)=".sizeof($marked);
	for ($i=0;$i<sizeof($marked);++$i) {
	  $mailtext .= "marked[".$i."]['cat']=".$marked[$i]['cat'].",";
    }
    $mailtext .= "</td></tr>";*/
	$mailtext .="</table></body></html>";
	if($_SESSION['msg_backup_count']) {
	  $_SESSION['msg_backup_count']++;
	}
	else {
	  $_SESSION['msg_backup_count'] = 1;
	}
	if(isset($_SESSION['msg_backup_count']) && $_SESSION['msg_backup_count'] <= 6) {
	  mail($player->getEMail(), $subject, $mailtext, $headers);
      $msgnoerror = "Ausgewählte Nachrichten erfolgreich per E-Mail versendet";
    }
    else {
      $msgerror = "Max. Anzahl von Backups der Nachrichten pro Session überschritten";
    }
  }
  else {
    $msgerror = "Keine Nachrichten ausgewählt";
  }
 }


?>

<? start_page(); ?>

<script language="JavaScript">
  <!-- Begin
var checkflag = "false";
function check(field) {
  if (checkflag == "false") {
    for (i = 0; i < field.length; i++) {
      field[i].checked = true;
    }
    checkflag = "true";
  }
  else {
    for (i = 0; i < field.length; i++) {
      field[i].checked = false;
    }
    checkflag = "false";
  }
}
// End -->
</script>
<? 
start_body(false); 
?>
<table cellspacing="0" cellpadding="0" border="0" width="768">
 <tr>
  <td colspan="2" align="center">
<? 
if(!is_premium_noads()) {
  echo '<div align="center">';

  $timemod = time() % 3600;
  if($timemod < 600 ) {
    include_once("includes/banner.inc.php");
    show_banner(0);
  }
  else if($timemod < 1200 ) {
    if($timemod % 2 == 0) {
      include("ads/ebay_728x90.php");
    }
    else {
      include("ads/ebay_relevance_728x90.php");
    }
  }
  else if($timemod < 1800 ) {
    include("ads/qualigo_leaderboard.php");
  }
  else if($timemod < 2400 ) {
    include("ads/openinventory_728x90.php");
  }
  else if($timemod < 2700) { 
    include("ads/ebay_video_728x90.php");    
  }
  else if($timemod < 3000) {
    include_once("includes/banner.inc.php");
    show_banner(0);
  }
  else {
    include("ads/affilimatch-leaderboard.php");
  }

  echo '</div>';
} // !noads
?>
  </td>
 </tr>

 <tr><td colspan="2">&nbsp;</td></tr>

 <tr>
 <td valign="top" width="250" style="padding: 1px">
  <table cellspacing="1" cellpadding="0" border="0">
  <tr class="tblhead"><td colspan="3"><b>Nachrichten</b</td></tr>
  <tr><td colspan="3">&nbsp;</tr>
<tr><td class="tblhead" width='236'>&nbsp;</td><td align="center" class="tblhead" width='22'><a target="main" href="messages.php?category=-1"><b>neu</b></a></td><td align="center" class="tblhead" width='22'><b>ges</b></td></tr>
<?

//anzahl der nachrichten in einer kategorie bekommen

$sqlcommand = "SELECT count(id) AS c, category FROM message WHERE recipient=".$player->getID()." AND !(status & ".MSG_RECIPIENT_DELETED.") GROUP BY category";
//performance tweak, falls kein premium account

if ($sizepostfach > 0) {
  $res1 = do_mysql_query($sqlcommand);
  while ($db_msg = do_mysql_fetch_assoc($res1)) {
    $arrmsg[$db_msg['category']]=$db_msg['c'];
  }
}
// Gesendete Nachrichten
$arrmsg[MESSAGECAT_SENT]= $player->getSentMessages();

// Anzahl der ungelesenen nachrichten in einer kategorie bekommen
$sqlcommand = "SELECT count(id) AS c, category FROM message WHERE recipient=".$player->getID()." AND !(status & ".(MSG_RECIPIENT_DELETED|MSG_RECIPIENT_READ).") GROUP BY category";

$res2 = do_mysql_query($sqlcommand);
while ($db_unreadmsg = do_mysql_fetch_assoc($res2)) {
  $arrunreadmsg[$db_unreadmsg['category']]=$db_unreadmsg['c'];
}
// Gesendete Nachrichten
$arrunreadmsg[MESSAGECAT_SENT]="-";


$mwidth = 210;
foreach($messagetypes AS $i => $mtype) {
  echo "<tr><td width='".$mwidth."' bgcolor=".$messagecolors[$i]."><a target='main' href='messages.php?category=$i'><b>".$mtype."</b></a></td>";
  echo "<td align='center' width='20' bgcolor=".$messagecolors[$i]."><b>";
  if (isset($arrunreadmsg[$i])) echo $arrunreadmsg[$i]; else echo "0";
  echo "</b></td>";
  if ($sizepostfach > 0) {
    echo "<td align='center' width='20' bgcolor=".$messagecolors[$i]."><b>";
    if (isset($arrmsg[$i])) echo $arrmsg[$i]; else echo "0";
  }
  else {
    echo "<td title='Anzeige nur f&uuml;r Premium-User' align='center' width='20' bgcolor=".$messagecolors[$i]."><b>";
    echo "?";
  }
  echo "</b></td></tr>";
}
?>
</table><p>
<center>
<a target="main" href="sms.php" title="Neue SMS verfassen"><img src="<? echo $imagepath; ?>/sms.gif" border="0"></a>&nbsp;&nbsp;&nbsp;
<a target="main" href="messages2.php"><b>neue Nachricht verfassen</b></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<p>
<hr>
<p>
</center>
</td>


<td valign="top" style="padding: 1px">
  <?
if ($player->getActiveMsgcategory()==-1) {
  $res1 = do_mysql_query("SELECT id, sender, date, recipient, !(status & ".MSG_RECIPIENT_READ.") AS unread, category, header FROM message WHERE recipient=".$player->getID()." AND !(status & ".(MSG_RECIPIENT_READ|MSG_RECIPIENT_DELETED).") ORDER BY date DESC");
}
else {
  if($player->getActiveMsgcategory()==MESSAGECAT_SENT) {
   //Alle gesendeten Nachrichten aus der DB laden
   $res1 = do_mysql_query("SELECT id, sender, date, recipient, !(status & ".MSG_SENDER_READ.") AS unread, category, header FROM message WHERE sender='".$player->getName()."' AND !(status & ".MSG_SENDER_DELETED.") AND date>=".$player->getRegTime()." ORDER BY date DESC LIMIT ".$sizecategory);
  }
  else {
    //Alle Nachrichten aus der aktiven Kategorie aus der DB laden
   $res1 = do_mysql_query("SELECT id, sender, date, recipient, !(status & ".MSG_RECIPIENT_READ.") AS unread, category, header FROM message WHERE category=".$player->getActiveMsgcategory()." AND !(status & ".MSG_RECIPIENT_DELETED.") AND recipient=".$player->getID()." ORDER BY date DESC LIMIT ".$sizecategory);
  }
}
?>
<form target="_self" action="messages.php" method="POST">
  <table cellspacing="1" cellpadding="0" border="0" >
  <tr class="tblhead"><td colspan="4"><b>
<? if ($player->getActiveMsgcategory()==-1) {
  echo "Neue Nachrichten";
}
else {
  echo "Kategorie: ".$messagetypes[$player->getActiveMsgcategory()];
  if ($player->getActiveMsgcategory() == MESSAGECAT_SENT) {
    echo " ($sizecategory werden angezeigt)";
  }
}
?>
</b></td></tr>
<tr><td colspan="4">&nbsp;</td></tr>

<?
if($msgerror) {
  echo '<tr><td style="padding:10px;" colspan="4" class="error" align="center"><b>'.$msgerror.'</b></td></tr>';
}
if($msgnoerror) {
  echo '<tr><td style="padding:10px;" colspan="4"class="noerror" align="center"><b>'.$msgnoerror.'</b></td></tr>';
}

$num_mess = 0;

if($sizecategory == 0) {
  echo "<tr><td height='40'colspan='4' class='tblbody'>Diese Feature ist <a href=\"premium.php\">Premium-Accounts</a> vorbehalten.</td></tr>";
}
else if(defined("OLD_GAME") && $_SESSION['player']->getActiveMsgcategory() == 5) {
  echo "<tr><td height='40'colspan='4' class='tblbody'>RPG-Featurities sind nur in den neuen Runden verfügbar.</td></tr>";  
}
else {
  if($_SESSION['player']->getActiveMsgcategory() == 9 && !$_SESSION['player']->isTeamMember()) {
    ?>
    <tr><td height='40'colspan='4' class='tblbody'>
    Hier kommuniziert das Holy-Wars 2 Team. Ihr seid aber leider kein Team-Mitglied.<p>
    Falls Ihr Interesse habt, Holy-Wars 2 zu unterstützen oder mitzuwirken, dann
    gibt es mehrere Möglichkeiten:
    <list>
    <li>Zunächst ist es wichtig, aktiv im Forum und im IRC zu sein.</li>
    <li>Regelmässiges Voten bringt Holy-Wars 2 voran und sorgt für neue Spieler.</li>
    <li>Da sich Holy-Wars 2 berwiegend aus Werbung finanziert, sollten <b>Popup-Blocker ausgeschalten</b> werden.
    <li>Bei regelmässigem und positivem Wirken besteht die Möglichkeit, als HW2-Betatester
        in das HW2-Team einzusteigen. Von dort aus stehen dann mit der Zeit weitere Posten
        zur Verfügung.
    </list>
    </td></tr>
    <?
  }
  
  $num_mess = do_mysql_num_rows($res1);
  if ($num_mess == 0) {
    if ($player->getActiveMsgcategory()==-1) {
      echo "<tr><td colspan='4' class='tblbody'>Keine neuen Nachrichten vorhanden</td></tr>";
    }
    else {
      echo "<tr><td colspan='4' class='tblbody'>Keine Nachrichten in dieser Kategorie vorhanden</td></tr>";
    }
  }
  
  if($num_mess > 10) {
    printButtons();
    echo '<tr><td colspan="4">&nbsp;</td></tr>';
  }

while ($db_message = do_mysql_fetch_assoc($res1)) {
  echo "<tr>";
  if ($db_message['unread']!=0) {
    $fett = "<b>";
    $fettend = "</b>";
  }
  else {
    $fett = "";
    $fettend = "";
  }
  if($db_message['recipient']!=$player->getID()) {
    $playername = resolvePlayerName($db_message['recipient']);
    $thiscolour = $messagecolors[MESSAGECAT_SENT];
  }
  else {
    $playername = $db_message['sender'];
    //$thiscolour = strncmp($db_message['sender'], "MULTI", 5)==0 ? "#FF8000" : $messagecolors[$db_message['category']];
    $thiscolour = $messagecolors[$db_message['category']];
  }
  echo "<td width='5' class='nopadding' bgcolor='".$thiscolour."'><input type='checkbox' name='mark[]' value='".$db_message['id']."' class='noborder'></td>";
  echo "<td width='75' nowrap bgcolor='".$thiscolour."'>$fett".date("d.m.y H:i",$db_message['date'])."$fettend</td>";
  echo "<td width='140' bgcolor='".$thiscolour."'>$fett";
  if($db_message['category'] == 0 && $playername != "SERVER") {
    echo "<a onClick=\"playerinfo('".urlencode($playername)."'); return false;\" href=\"info.php?show=player&name=".urlencode($playername)."\">".$playername."</a>\n";
  }
  else {
    echo $db_message['sender'];
  }
  echo "$fettend</td>";
  echo "<td width='200' bgcolor='".$thiscolour."'><a target='main' href='messages2.php?show=".$db_message['id']."'><b>".$db_message['header']."</b></a></td>";
  echo "</tr>";
 }
} //if($sizecategory != 0)
?>
<tr><td colspan="4">&nbsp;</td></tr>

<? 
if($num_mess > 0) {
  printButtons();
}
?>
</form>

<? if($msgnoerror) echo "<tr><td colspan=\"4\"><br><div class='noerror'><b>".$msgnoerror."</b></div></td></tr>"; ?>
<? if($msgerror) echo "<tr><td colspan=\"4\"><br><div class='error'><b>".$msgerror."</b></div></td></tr>"; ?>
<tr height="20"><td colspan="4">&nbsp;</td></tr>
<?php if(!defined("HISPEED") || !HISPEED) { ?>
    <tr><td align="center" colspan="4" style="padding: 10px; background-color: B0B0B0;">
    <h1>SMS über Holy-Wars 2!</h1>
    <?
    check_sms_settings(); 
    if ($_SESSION['sms_contingent'] > 0) 
      echo 'Ihr habt noch <b style="color: red; font-size: 12px;">'.$_SESSION['sms_contingent']." SMS</b> zum Versand zur Verfügung.\n<p>\n<a class=\"green\" href=\"sms.php\">SMS verfassen (hier oder auf das SMS-Symbol klicken)</a>";
    else 
      echo 'Leider habt ihr derzeit <b style="color: red; font-size: 12px;">kein SMS</b> zum Versand zur Verfügung.';
?>
<p>
<a href="settings.php?show=sms">Hier für SMS-Anmeldung/Verwaltung klicken</a>.
</td></tr>
<?php } // if(!defined("HISPEED") || !HISPEED) ?>

<tr height="20"><td colspan="4">&nbsp;</td></tr>
<tr><td align="center" colspan="4" style="padding:0px;"><? //not https safe content include("includes/vote.inc.php"); ?>
</td></tr>
</table>
</td>
</tr>

</table>

<?
end_page();


function printButtons() {
?>
<tr><td class="nopadding" colspan="2"><input type="checkbox" name="markall" onclick="check(this.form.elements)" class="noborder">&nbsp;alle markieren</td>
<td class="nopadding" colspan="2">
<input type="submit" value="als gelesen markieren" name="readmarked">
&nbsp;<input
<?
if (get_message_archive_size() > 0) {
  echo " onClick=\"return confirm('Wollen Sie die Nachrichten per Mail verschicken?'); return false;\" ";
}
else {
  echo " onClick=\"alert('Diese Funktion ist Premium-Accounts vorbehalten!'); return false;\" ";
}
?>
type="submit" value="per Email sichern" name="mailmarked">
&nbsp;<input type="submit" value="löschen" name="delmarked">
</td>
</tr>
<? } ?>