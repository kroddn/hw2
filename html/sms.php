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

/***************************************************
 * Copyright (c) 2003-2005
 *													
 * Markus Sinner
 *													
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/banner.inc.php");
include_once("includes/sms.func.php");


if(defined("HISPEED") && HISPEED) {
  start_page();
  start_body();
  echo "<h1>SMS-Versand ist in der HiSpeed deaktiviert.</h1>";
  end_page();
  die();
}

check_sms_settings();
$sms_sent = false;

if(isset($smsbody)) $smsbody = stripslashes(trim($smsbody));
if(isset($recipient)) $recipient = stripslashes(trim($recipient));


if (isset($send) && isset($recipient)) {
  // Der Versand </b>kostet momentan nichts<b>, allerdings kann es zu erheblichen Verzögerungen bei der Zustellung kommen, ebenso wird eine Zustellung </b>nicht<b> garantiert! </b><p>
  
  $inform = "";

  if (strlen($recipient) > 0) {
    if ( 0 == strcmp(substr($recipient, 0,3), "+49") ) {
      $rec['id']  = null;
      $rec['sms'] = $recipient;
    }
    else if (isset($ismsprefix) && !(FALSE === array_search($ismsprefix, $GLOBALS['smsprefixes'])) ) {
      $rec['id']  = null;
      $rec['sms'] = preg_replace("/^0/", "+49", $ismsprefix).$recipient;
    }
    else {
      $rec_res = do_mysqli_query("SELECT * FROM player WHERE name LIKE '".mysqli_escape_string($GLOBALS['con'], $recipient)."'");
      if(mysqli_num_rows($rec_res) == 1) {
        $rec = mysqli_fetch_assoc($rec_res);
      }
    }
  }
  
  if ( !isset($rec) ) {
    if ($rec_res)
      $inform .= "Es existiert kein Spieler mit dem Namen <i>$recipient</i>.";
    else 
      $inform .= "Ihr habt keinen Spieler oder keine gültige Nummer eingegeben.";
  }
  else {
    // Weitere Fehler behandeln
    if ($_SESSION['sms_may_send'] != true) {
      $inform .= SMS_SEND_ACCEPT;
    }
    else if ($rec['sms'] == null) {
      $inform .= 'Leider hat Spieler &quot;<i>'.$recipient.'</i>&quot; noch keine SMS-Nummer hinterlegt. Benutzen Sie eine <a href="messages2.php?msgrecipient='.$recipient.'">Standard-Nachrichten</a> und weisen Sie ggf. den Spieler darauf hin, dass er eine gültige Nummer hinterleft.';
    }
    else if(!isset($smsbody)) {
      $inform .= "Sie haben keine Nachricht eingegeben.";
    }
    else if(strlen($smsbody) < 1 || strlen($smsbody) > SMS_MAX_LEN)  {
      $inform .= "Entweder haben Sie eine zu kurze Nachricht eingegeben, oder die Nachricht ist länger als ".SMS_MAX_LEN." Zeichen.";      
    }
    else if($_SESSION['player']->getSMSRemaining() < 1) {
      $inform .= "Ihr SMS-Kontingent ist leer! Laden Sie es auf.";
    }    
    else {
      $smsbody_send = $smsbody;

      // Funktion liefern NULL zurück, falls es klappt!
      $ret = sms_send($rec['sms'], $smsbody_send);

      if ( $ret != null ) {
        $inform .= "Der Versand ist fehlgeschlagen: ".$ret;
      }
      else {
        // Spieler eine SMS abziehen!!!
        $inform .= "Die SMS wurde erfolgreich versendet.";
        $sms_sent = true;
      }
      
      // Die SMS wird nochmal dem Adressat geschickt
      // Falls $recipient ne SMS-Nummer war, dann CC an den Spielernamen der Nummer
      if ( valid_sms_nr($recipient) && $rec == null ) {
        $msg_cc = null;
      }
      else {
        $msg_cc = $rec['id'];
      }


      // Zum Debugging wird ne Mail an die Admins versand
      if(0) {
        $test_mail = 
                   sprintf("Der Spieler %s (ID: %d) wollte eine SMS an %s (Nr: %s) senden. ".
                           "Der Text lautet:\r\n--------------------------------------\r\n".
                           "%s\r\n--------------------------------------\r\n".
                           "Ergebnis: %s\r\n\r\nCC ging an %s",
                           $_SESSION['player']->getName(), 
                           $_SESSION['player']->getID(),
                           $recipient, 
                           $rec['sms'], 
                           $smsbody_send,                          
                           ($ret==null ? "OK" : $ret), 
                           $msg_cc );
      }// if 0       
    }    
  }
}


if (isset($newsms)) {
  unset($recipient);
  unset($body);
  unset($ismsprefix);
}

start_page();
?>


<script language="JavaScript" src="js/sms.js"></script>
<script language="JavaScript">
<!-- Begin
function openhelp (url) {
  var helpwin = window.open(url,"Hilfe","width=500,height=500,left=0,top=0,scrollbars=yes,dependent=yes");
  helpwin.focus();
  return false;
}
// End -->
</script>
<? 
start_body(); 

if($help) { include("includes/sms_help.html"); die("</body></html>"); } ?>
<br>
<div align="center" style="width: 600px;">
<h1>Holy-Wars 2 SMS-System</h1>
<form name="smsform" id="theForm" target="_self" action="<? echo $_SERVER['PHP_SELF'];  ?>" method="POST" 
onSubmit="document.getElementById('waitmessage').style.visibility='visible';">
 <table cellspacing="1" cellpadding="0" border="0" width="500">
<? 
if (isset($inform)) {
  //  $inform = '';    
  echo '<tr class="tblbody" height="30"><td colspan="3">';
  printf('<div align="center" style="font-size: 12px; font-weight: bold; color: %s; margin-bottom: 5px;">%s</div>',
	 $sms_sent ? "#009000" : "red", $inform);
  echo '</td></tr><tr height="5"></tr>'; 
}
?>
  <tr class="tblhead"><td colspan="2"><b>SMS an Spieler oder Handynummer senden</b></td>
   <td width="40" rowspan="3"><a href="sms.php"><img border="0" src="<? echo $imagepath; ?>/sms.gif"></a></td>
  </tr>
  <tr class="tblbody"><td width="160"><b>Verbleibende SMS:</b></td><td><font style="font-size:14px;"><b><? echo $_SESSION['player']->getSMSremaining(); ?></b></font>
&nbsp;&nbsp;<a href="settings.php?show=sms#charge">(aufladen)</a></td></tr>
  <tr class="tblbody"><td><b>Empfänger oder Nummer:</b><br>(Nummer beginnt mit +49)</td>
<td valign="middle">

<select name="ismsprefix">
   <option value="">Vorwahl:</option>
   <option value="">---------</option>
     <? 
     foreach(get_prefix_array() as $prefix) {
       echo '   <option value="'.$prefix.'"';
       if ($prefix == $ismsprefix) echo " selected";
       echo ">$prefix</option>\n";
     }
     ?>
   <option value="">Intern.</option>
  </select>

<input tabindex="1" type="text" name="recipient" style="width: 80px;" value="<? echo $recipient;?>">
&nbsp;&nbsp;
<a style="font-size: 12px; color: red; float:right;" title="Anleitung zum SMS-Versand." onClick="return openhelp('sms.php?help=1')" href="sms.php?help=1">Anleitung</a><br>
<!-- Adressbuch -->
<select onChange="if(this.value!='') { document.smsform.recipient.value=this.value; document.smsform.ismsprefix='Intern.'; }" name="adrbook" size="1" stlye="width:140px;">
<option value="">Adressbucheintrag wählen</option>
<?
  $adress = $player->getSMSAdressbook();
  foreach ($adress as $i=>$adr) {
    echo '<option value="'.$adr['sms'].'">'.$adr['nicename'].
      (strcmp($adr['nicename'], $adr['sms']) == 0 ? " (hinterlegt)" : " (".$adr['sms'].")" ).
      "</option>\n";     
  }    
?>
</select>
<br style="margin: 2px;">Adressbuch nun editierbar!
<a href="edit_adr.php">Hier klicken</a>.

</td></tr>
  </tr>
  
  <tr height="5"></tr> <!-- Leerzeile -->
  <tr class="tblhead"><td colspan="3" align="center">
<? 
$nr = $_SESSION['player']->getSMSSenderNumber();
if (is_premium_set_sms_sender() && $nr != null) {
  if(!$_SESSION['player']->isValidSMSSenderNumber()) {
    echo 'Die SMS-Absenderkennung ('.$nr.') ist noch nicht verifiziert.';    
  }
  else if ($_SESSION['player']->showSMSSenderNumber()) {
    echo 'Eure <a href="settings.php?show=sms#sendernr">SMS-Absenderkennung</a> ist '.$nr;
  }
  else {
    echo 'Eure SMS-Absenderkennung ('.$nr.') ist deaktiviert. Aktiviert sie in '.
      '<a href="settings.php?show=sms#sendernr">MyHW2</a>.';
  }
}
else {
  echo 'Als <a href="settings.php?show=sms#sendernr">SMS-Absenderkennung</a> wird Holy-Wars 2 übermittelt. ';
  if (is_premium_set_sms_sender()) {
    echo '<br>Als Premium-Account-Besitzer dürft ihr diese Nummer in '.
      '<a href="settings.php?show=sms#sendernr">MyHW2</a> konfigurieren.';
  }
  else {
    echo '<br>Mit einem Premium-Pro-Account dürft Ihr diese Nummer selbst wählen.';
  }
}
?>
  </td></tr>

  <tr height="5"></tr> <!-- Leerzeile -->

  <tr class="tblhead"><td colspan="3" align="center">
Vergesst nicht, Euch als Absender der SMS kennlich zu machen.<br>
   <textarea <? if($sms_sent) echo "disabled"; ?> tabindex="2" id="theSMS" name="smsbody" 
   enctype="text/plain"
   cols="50" rows="4" maxlength="160" style="width:350px;"
   onfocus="charsleft(this);" onblur="charsleft(this);" 
   onkeydown="charsleft(this);" onkeypress="charsleft(this);" onkeyup="charsleft(this);"
   ><? if(isset($smsbody)) echo htmlentities($smsbody); ?></textarea>
    <br>
   Noch <span id="remaining_sms">160</span> Zeichen frei.
  </td></tr>
<tr><td class="tblhead" align="center" colspan="3" style="padding:4px; ">
<?
if ( $_SESSION['sms_may_send'] ) {
  if ($sms_sent) {    
    echo '<input type="submit" name="newsms" value=" Neue SMS ">';
  }
  else {
?>
Sie haben den <a href="settings.php?show=sms#sms_rules">Bedingungen zum SMS-Versand</a> zugestimmt.<p>
<input tabindex="3" type="submit" name="send" value=" SMS versenden ">
<input onClick="return confirm('Nachricht wirklich zurücksetzen?')" type="reset" value=" Zurücksetzen ">
<?
  }
}
else {
  echo SMS_SEND_ACCEPT;
}
?>
</td></tr>
  <tr height="5"></tr> <!-- Leerzeile -->
  <tr class="tblhead"><td colspan="3" align="center">SMS-Versand ist ein <a target="main" href="settings.php?show=sms#sms_rules">kostenpflichtiger Dienst</a>.<br>
Ihr SMS-Kontingent (verbleibende SMS) können Sie durch überweisung <a href="settings.php?show=sms#charge">aufladen</a>!</td></tr>
 </table>
</form>
<div style="padding: 10px; visibility: hidden; border: 5px solid yellow; background-color: orange; position: absolute; top: 250; left: 100; width:300px; height: 180px;" id="waitmessage" name="waitmessage">
<b>
Bitte warten Sie, während das System ihre Eingaben prüft und die SMS versendet.<p>
Klicken Sie nicht auf Stop!
</b>
<p>
<p>
Sie können Bonuspunkte in SMS umwandeln. Um Bonuspunkte zu erhalten sollten
Sie neue Spieler werben. Wie das geht können Sie unter dem Menüpunkt
&quot;My HW2&quot; erfahren.
</div>
</div>
</body>
</html>
