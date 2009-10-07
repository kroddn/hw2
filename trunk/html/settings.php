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
 * Copyright (c) 2005
 *
 * Markus Sinner
 *
 * This File must not be used without permission
 ***************************************************/
include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
include_once("includes/map.class.php");
include_once("includes/player.class.php");
include_once("includes/banner.inc.php");
include_once("includes/multi.inc.php");
include_once("includes/premium.inc.php");
include_once("includes/sms.func.php");

$_SESSION['player']->setActivePage(basename(__FILE__));

// Einstellungen neu laden
if (isset($loadsettings)) {
  $_SESSION['player']->loadSettings();
}

if(isset($_REQUEST['activatetutorial']) ) {
  if($_REQUEST['activatetutorial'] == 1) $_SESSION['player']->tutorialInc(1);
  if($_REQUEST['activatetutorial'] == 0) $_SESSION['player']->tutorialInc(-1);
}

if (isset($acceptsmsrules)) {
  if (!isset($ireadit) || $ireadit != 1) {
    $errormsg = "Bitte stimmen Sie den Nutzungsbedingungen vollst�ndig zu, indem Sie das entsprechende Feld ankreuzen.";
  }
  else {
    $errormsg = accept_sms_rules();
  }
}

// Einstellungen �ndern
if (isset($changesettings) || isset($savesettings)) {
  foreach($arr_settings as $i => $name) {
    $_SESSION['settings'][$name] = $$name   == 1;    
  }
  
  // Die Einstellungen auch abspeichern
  if(isset($savesettings)) {
    if ($_SESSION['premium_flags'] > 0 ) {
      $_SESSION['player']->saveSettings();
    }
    else {
      $errormsg = "Die M�glichkeit des dauerhaften Abspeicherns ist Besitzern eines Premium-Accounts vorbehalten.";
    }
  }

  $extra_code = '<script type="text/javascript">parent.navigation.location.reload();</script>';
}

if (isset($changesignature)) {
  // Sigantur anpassen
  $res = $_SESSION['player']->updateMsgSignature($msgsignature);
  if ($res != null) {
    $errormsg = $res;
  }
}

if(isset($gfx_path_value)) {
  echo "\n<!-- $gfx_path_value -->\n";
  $gfx_path_value = trim($gfx_path_value);

  switch($gfx_path_value) {
    case "":
      $errormsg = "Der �bermittelte Grafikpfad war leer.";
      break;
      case "HOWLY4":
        //if (defined("TEST_MODE") && TEST_MODE) {
          $gfx_path = "http://www.howlywood.de/holynewer/images/ingame_v4";
          break;
        //}
      case "SERVER4":
        if(defined("OLD_GAME")) {
          $errormsg = "Diese Grafik-Version wird nur auf neueren Servern unterst�tzt.";
        }
        else if (defined("TEST_MODE") && TEST_MODE) {
          $gfx_path = "images/ingame_v4";
        }
        else {
          $gfx_path = GFX_PATH_LOCAL;
          $errormsg = "Noch nicht freigegeben. Versuchen Sie es morgen wieder.";
        }
     break;
    case "SERVER3":
      $gfx_path = GFX_PATH_LOCAL;
      break;
    default:
      {
        $gfx_path_value = str_replace("\\\\", "/", $gfx_path_value);
        $gfx_path_value = str_replace("\\", "/", $gfx_path_value);
        
        $pos = strpos($gfx_path_value, "images/ingame");

        if ($pos === false) {
          $gfx_path = "file://".str_replace("gfx_version.gif", "", $gfx_path_value);
        }
        else {
          // Falls images/ingame vorkommt...
          $gfx_path = "file://".substr($gfx_path_value, 0, $pos);
        }
      } // default
  } // switch
}

if (isset($changegfx) && !isset($errormsg) ) { 
  $_SESSION['player']->updateGFX_PATH($gfx_path);
  $_SESSION['imagepath'] = $gfx_path;
  $_SESSION['hwathome']  = 1;
  redirect_to("index.php");  
}

// TestGFX weiter unten
if (isset($testgfx) && isset($gfx_path) ) {
  include("includes/testgfx.inc.php");
  die();
}


if ( (!defined("SPEED") || !SPEED) && isset($holidaymode) && isset($holidaydurance)) {
  if($holidayaccept) {
    $errormsg = $_SESSION['player']->holidayMode($holidaydurance);
    if ($errormsg == null) {
      $infomsg = "Urlaubsmodus aktiv bis ".date("d.m.Y G:i", time() + $holidaydurance*24*3600).".<br>Sie wurden ausgeloggt.";
    }
  }
  else {
    $errormsg = "Sie m�ssen 'Ja' im Feld neben<br>der L�nge des Urlaubs w�hlen";
  }
}

if (isset($changesms)) {
  $sms = trim($sms);
  $sms = ereg_replace( "[^0-9a-zA-Z\+]", "", $sms );
  
  // Falls die Nummer mit 01 anf�ngt ersetze die 0 durch +49
  if (substr($sms,0,2) == "01") {
    $sms = "+49".substr($sms, 1);
  }
  

  if (strlen($sms) == 0) {
    $_SESSION['player']->changeSMS(null);
  }
  // RegEx �berpr�fung der SMS-Nummer
  else if ( valid_sms_nr($sms) ) {
    $_SESSION['player']->changeSMS($sms);
  }
  else {
    $errormsg = "Die SMS Empfangsnummer ist ung�ltig.";
  }
}


if (isset($changesms_sender_nr)) {
  $sms_sender_number = trim($sms_sender_number);
  $sms_sender_number = ereg_replace( "[^0-9a-zA-Z\+]", "", $sms_sender_number );
  
  // Falls die Nummer mit 01 anf�ngt ersetze die 0 durch +49
  if (substr($sms_sender_number,0,2) == "01") {
    $sms_sender_number = "+49".substr($sms_sender_number, 1);
  }
  

  if (strlen($sms_sender_number) == 0) {
    $_SESSION['player']->changeSMSSenderNumber(null);
  }
  // RegEx �berpr�fung der SMS-Nummer
  else if ( valid_sms_nr($sms_sender_number) ) {
    $_SESSION['player']->changeSMSSenderNumber($sms_sender_number, 
                                               (isset($show_sender_nr) && $show_sender_nr == 1) );
  }
  else {
    $errormsg = "Die Absenderkennung ist ung�ltig.";
  }
}


if (isset($change_email)) {
  $errormsg = $_SESSION['player']->changeEMail($email);
}

if (isset($change_desc) && isset($theText)) {
  $theText = stripslashes($theText);
  $_SESSION['player']->updateDescription($theText);
}

if (isset($change_passwd)) {
  if(empty($oldpw) || empty($newpw1) || empty($newpw2))
    $errormsg .= "Sie haben nicht alle Felder ausgef�llt.";
  else {
    $err = $_SESSION['player']->changePassword($oldpw, $newpw1, $newpw2);
    if($err == null) 
      $infomsg = "Passwort erfolgreich ge�ndert.";
    else
      $errormsg .= $err;
  }
}

if ($multi_note && $multi_players) {
  request_exception($multi_note, $multi_players); 
}   


if (isset($acc_delete_process)) {
    if(defined("HISPEED")) {
      $errormsg = "L�schen in der HiSpeed deaktiviert.";
    }
    else {
      $del=true;
      if($acc_delete0==1) $del=false;
      if($acc_delete1==1) $del=false;
      if($acc_delete2==1) $del=false;

      if($del==true) {
        $_SESSION['player']->Remove_old();
      }
    }
}

if(isset($_POST['zitattext']) && isset($_POST['zitat'])) {
  if (strlen($_POST['zitattext']) < 10) {
    $errormsg = "Zitattext zu kurz.";
  }
  else {
    do_mysql_query("INSERT INTO zitate (player,text) VALUES ('".$_SESSION['player']->id."','".mysql_escape_string(strip_tags($_POST['zitattext']))."')");
  }
}


//avatar delete
if(isset($_GET['delete'])) {
  if($_GET['delete']=="avatar") {
    $filename=AVATAR_DIR.$_SESSION['player']->id.".jpg";
    if(is_file($filename)) {
      $del=unlink($filename);
    }
    if($del) {
      do_mysql_query("UPDATE player SET avatar=NULL WHERE id='".$_SESSION['player']->id."'");
      $infomsg = "Avatar gel�scht.";
    }
  }
}

//avatar check if upload
if ($_FILES['userfile']) { 
	$upload_dir = AVATAR_DIR; 
	$errormsg = do_upload($upload_dir, $upload_url);
    if ($errormsg == null) 
      $infomsg = "Der Upload war erfolgreich. Ein Namehunter wird ihren Avatar in K�rze freischalten!";
} 

//avatar file handle
function do_upload($upload_dir, $upload_url) { 
  $temp_name = $_FILES['userfile']['tmp_name']; 
  $file_name = $_FILES['userfile']['name']; 
  $file_type = $_FILES['userfile']['type']; 
  $file_size = $_FILES['userfile']['size']; 
  $result    = $_FILES['userfile']['error']; 
  $file_url  = $upload_url.$file_name; 
  $file_path = $upload_dir.$file_name; 

  if ( $file_name =="") { 
    return "Ung�ltiger Dateiname"; 
  } 
    
  else if ( $file_size > 1024*50) { 
    return "Die Datei ist gr��er als 50KB."; 
  } 
  else {
    $size = GetImageSize ($_FILES['userfile']['tmp_name']);
    if($size['mime'] == "image/jpeg") {
      $maxx=100;
      $maxy=100;
      $name=strtolower(substr($_FILES['userfile']['name'],0,-4)).".jpg";
		
      if ($size[0]>$size[1]) {
        $sizemin[0]=$maxx;
        $ratio=round(($size[1]/$size[0]),2);
        $sizemin[1]=($maxx*$ratio);
      }
      if ($size[1]>$size[0]) {
        $sizemin[1]=$maxy;
        $ratio=round(($size[0]/$size[1]),2);
        $sizemin[0]=($maxy*$ratio);
      }
      if ($size[1]==$size[0]) {
        $sizemin[1]=$maxy;
        $sizemin[0]=$maxx;
      }
      $im=@imagecreatefromjpeg($temp_name);
      $small = imagecreatetruecolor($sizemin[0], $sizemin[1]);
      ImageCopyResampled($small, $im, 0, 0, 0, 0, $sizemin[0], $sizemin[1], $size[0], $size[1]);
      ImageDestroy($im);
      $path=$upload_dir.$_SESSION['player']->id.".jpg";
      if (ImageJPEG($small,$path,100)) {
        do_mysql_query("UPDATE player SET avatar='1' WHERE id='".$_SESSION['player']->id."'");
      } 
      else {
        return "Fehler beim Umwandeln des Bildes.";
      }
    } 
    else {
      return "Das ist kein JPEG Bild!";
    }
  }
  // Alle Namehunter Informieren
  $nhs = do_mysql_query("SELECT id FROM player WHERE hwstatus&8");
  while($nh = mysql_fetch_assoc($nhs)) {
    $sql = sprintf("INSERT INTO message (recipient,date,header,body,category) ".
                   "VALUES (%d,UNIX_TIMESTAMP(),'%s','%s',9)",
                   $nh['id'],
                   "Neuer Avatar f�r ".($_SESSION['player']->getName()),
                   "Es liegt ein Avatar f�r ".($_SESSION['player']->getName())." zur Freigabe bereit.");
    do_mysql_query($sql);
    do_mysql_query("UPDATE player SET cc_messages=1 WHERE id = ".$nh['id']);
  }

  return null;
} 
?>

<? start_page(); ?>

<script language="JavaScript" src="js/bbcode_functions.js"></script>
<script language="JavaScript" src="js/settings_functions.js"></script>
<script>
<!--
function setClickHint(str) 
{
    hint = document.getElementById("clickhint");
    hint.innerHTML = str;
}
// -->
</script>
<?
if (isset($extra_code)) echo $extra_code."\n";

start_body();
?>
<div align="center">
<h1>My Holy-Wars 2 - Einstellungen zum Spiel</h1>
<?
// Fehlermeldungen
if ($errormsg) {
  echo '<h1 class="error">Fehler</h2><font class="error" size="+1">'.$errormsg."</font><p>"; 
}
if ($infomsg) {
  echo '<h1><font color="#008000" size="+1">'.$infomsg."</font><p>"; 
}

if (!isset($_SESSION['settings_lastshow'])) {
  session_register("settings_lastshow");
  $_SESSION['settings_lastshow'] = "account";
}

// Default-Wert falls kein Men�punkt gew�hlt ist
if (!isset($show)) {
  $show = $_SESSION['settings_lastshow'];
}
else {
  $_SESSION['settings_lastshow'] = $show;
}

switch($show) {
 case "account" :
 case "game" :
 case "grapa" :
 case "sms" :
 case "ads" :
 case "premium" :
   show_settings_menu($show);
   $showfunction = "show_".$show;
   $showfunction();
   break;
 default:
   // Kein g�ltiger Men�punkt
   show_wrong();
   $_SESSION['settings_lastshow'] = $show="account";
   echo "<p>\n";
   show_settings_menu($show);
}
?>
</div>
<br>
<br>

<? 

end_page();


/****************************/
/*** Hier die Funktionen  ***/
/****************************/
function show_settings_menu($show) {
  $hints = array("account" => "Einstellungen zum Account - Emailadresse, Passwort, Urlaubsmodus, Multi-Exceptions etc.",
                 "game" => "Einstellungen zur Spielfigur - Beschreibung, Nachrichten-Signatur und Avatar",
                 "grapa" => "Grafikpack einrichten und Hauptmen� konfigurieren",
                 "sms" => "SMS-Absenderkennung oder Empfangsnummer einrichten, SMS-Kontingent verwalten oder freischalten ",
                 "premium" => "Premium-Account: Einstellungen einrichten und einsehen. Adressbuch einsehen.",
                 "ads" => "F&ouml;rdere HW2: HW2 Banner, Forensignatur" );
?>
<!-- Men� -->
<table id="settings_menu" name="settings_menu" cellspacing = "1" cellpadding="0">
  <tr>
    <td onMouseOver="setClickHint('<? echo $hints['account']; ?>')" nowrap width="20%" <? if ($show=="account") echo 'class="active"';?>>
    <a href="?show=account">Account</a></td>
    
    <td onMouseOver="setClickHint('<? echo $hints['game']; ?>')"    nowrap width="20%" <? if ($show=="game")    echo 'class="active"';?>>
    <a href="?show=game">Spiel</a></td>
    
    <td onMouseOver="setClickHint('<? echo $hints['grapa']; ?>')"   nowrap width="20%" <? if ($show=="grapa")   echo 'class="active"';?>>
    <a href="?show=grapa">Grafik / Men�</a></td>
    
    <td onMouseOver="setClickHint('<? echo $hints['sms']; ?>')"     nowrap width="20%" <? if ($show=="sms")     echo 'class="active"';?>>
    <a href="?show=sms">SMS</a></td>
    
    <td onMouseOver="setClickHint('<? echo $hints['premium']; ?>')" nowrap width="20%" <? if ($show=="premium") echo 'class="active"';?>>
    <a href="?show=premium">Premium-Account</a></td>
    
    <td onMouseOver="setClickHint('<? echo $hints['ads']; ?>')" nowrap width="20%" <? if ($show=="ads") echo 'class="active"';?>>
    <a href="?show=ads">F&ouml;rdere HW2</a></td>
  </tr>
  </table>
  <table id="settings_menu" name="settings_menu" cellspacing = "1" cellpadding="0">
  <tr>
  <? 
  if ($show=="account") {
    echo '<td align="center" valign="top"><a href="#acc_allinf" style="font-size:85%;">Allgemeine Informationen</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_bonus" style="font-size:85%;">Bonuspunkte</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_email" style="font-size:85%;">Emailadresse &auml;ndern</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_pass" style="font-size:85%;">Passwort &auml;ndern</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_accdel" style="font-size:85%;">Account l&ouml;schen</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_vacmod" style="font-size:85%;">Urlaubsmodus</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_mulexc" style="font-size:85%;">Multi-Exceptions</a></td>';
    echo '<td align="center" valign="top"><a href="#acc_zitat" style="font-size:85%;">Zitat erstellen</a></td>';
  }
  else if ($show=="game") {
    echo '<td align="center" valign="top"><a href="#game_player" style="font-size:85%;">Spieler-Beschreibung</a></td>';
    echo '<td align="center" valign="top"><a href="#game_sig" style="font-size:85%;">Signatur &auml;ndern</a></td>';
    echo '<td align="center" valign="top"><a href="#game_avatar" style="font-size:85%;">Avatar einstellen</a></td>';
    echo '<td align="center" valign="top"><a href="#game_tutorial" style="font-size:85%;">Tutorial steuern</a></td>';
  }
  else if ($show=="grapa") {
    echo '<td align="center" valign="top"><a href="#grapa_pack" style="font-size:85%;">Grafikpack</a></td>';
    echo '<td align="center" valign="top"><a href="#grapa_menu" style="font-size:85%;">Menu und sonstige Einstellungen</a></td>';
  }
  else if ($show=="sms") {
    echo '<td align="center" valign="top"><a href="#sms_getnr" style="font-size:85%;">Empfangsnummer &auml;ndern</a></td>';
    echo '<td align="center" valign="top"><a href="#sms_sendnr" style="font-size:85%;">Absenderkennung einstellen</a></td>';
    echo '<td align="center" valign="top"><a href="#sms_konto" style="font-size:85%;">SMS Kontingent aufladen</a></td>';
    echo '<td align="center" valign="top"><a href="#sms_get" style="font-size:85%;">SMS Empfang</a></td>';
    echo '<td align="center" valign="top"><a href="#sms_premium" style="font-size:85%;">Premium Lite per SMS</a></td>';
    echo '<td align="center" valign="top"><a href="#sms_use" style="font-size:85%;">Nutzungsbedingungen</a></td>';
  }
  else if ($show=="premium") {
    echo '<td align="center" valign="top"><a href="#premium_info" style="font-size:85%;">Allgemeine Informationen</a></td>';
    echo '<td align="center" valign="top"><a href="#premium_abook" style="font-size:85%;">Adressbuch</a></td>';
    echo '<td align="center" valign="top"><a href="#premium_sms" style="font-size:85%;">Premium Lite per SMS</a></td>';
    echo '<td align="center" valign="top"><a href="#premium_banner" style="font-size:85%;">Werbebanner ein/ausschalten</a></td>';
  }
  ?>
  </tr>
</table>
<p>
<? 
} // function show_settings_menu()



// Falsche Men�option
function show_wrong() { ?>
<p>
Diese Men�option existiert nicht. 
<? 
} // show_wrong() 



function show_sms_acceptrules() {
?>
<table id="settings">
<tr class="tblhead_22"><td colspan="2"><h2>Bedingungen zur Benutzung des SMS-Systems akzeptieren</h2></td></tr>
<tr class="tblbody">
 <td colspan="2" align="center">
   <table width="400"><tr><td>
   <b>Zur Nutzung des SMS-Services von Holy-Wars 2 m�ssen Sie die 
   Nutzungsbedinungen akzeptieren.</b><br>
   Erst dann wird Ihnen der Zugang zu den Services freigeschaltet.<p>
   <hr>
   <? include("includes/sms_rules.html"); ?>
   <hr>
   <center>
   <form id="theSettingsForm" name="theSettingsForm" action="settings.php" method="post">
   <input type="checkbox" name="ireadit" value="1"> Ich habe die Nutzungsbedingungen gelesen und bin einverstanden.<p>
   <input type="submit" name="acceptsmsrules" value=" Zustimmen ">
   <input type="reset"  name="declinesmsrules" value=" Ablehnen ">
   </form>
   </center>
  </div>
 </td></tr>
</table>
<?
}



function show_sms () { 
  check_sms_settings();
  if ($_SESSION['sms_may_send'] == true) {
?>

<table id="settings">
<tr class="tblhead_22"><td colspan="2"><h2><a name="sms_getnr">SMS Empfangsnummer �ndern</a></h2></td></tr>
<tr class="tblbody">
  <td colspan="2" align="center">
<!-- SMS Conf Table -->
<table width="468"><tr><td colspan="2">
<b>Geben Sie hier Ihre Mobilfunknummer an, auf der Sie SMS empfangen k�nnen. 
Andere Spieler k�nnen Ihnen dann SMSe senden. </b>
</td></tr>
<tr><td>
Die Nummer wird jedoch keinem Spieler
angezeigt, ein Versand ist nur aus Holy-Wars 2 heraus m�glich! Dies dient Ihrer
Privatsph�re.<p>
<form name="theSMSForm" action="settings.php" method="post" target="main">
<input type="text" name="sms" value="<? echo $_SESSION['player']->getSMS(); ?>">&nbsp;&nbsp;
<input type="submit" name="changesms" value=" �ndern ">
</form>
<p>
Zur Zeit k�nnen wir nur <b>Rufnummern aus Deutschland</b> annehmen.<br>
Geben Sie Ihre Nummer in der Form <b>0172 12345678</b> ein! 
Das System bringt die Nummer dann automatisch in ein internationales
Format mit +49 am Anfang (also nicht wundern).
</td>
<td valign="top" align="right" width="100"><a href="sms.php"><img border="0" src="<? echo $GLOBALS['imagepath']; ?>/sms.gif"></a></td>

</tr></table><!-- SMS Conf Table -->

  </td>
</tr>


<tr height="10"><td></td></tr>
<a name="sendernr"></a>
<tr class="tblhead_22">
 <td><h2><a name="sms_sendnr">SMS Absenderkennung einstellen</a></h2></td>
</tr>
<tr><td colspan="2" align="center">
<table width="468"><tr><td colspan="2">
<b>Sie k�nnen an dieser Stelle eine
Absenderkennung f�r den SMS-Versand einstellen.</b>
<br>
Beim Empf�nger einer HW2SMS
wird dann Ihre Nummer anstelle der Nummer von Holy-Wars 2  angezeigt. Der
Empf�nger kann Ihnen dann direkt antworten.<P>
<?
  if (is_premium_set_sms_sender()) {
?>
<form name="theSMSSenderForm" action="settings.php" method="post" target="main">
<?
if ($_SESSION['player']->getSMSSenderNumber() == null) {
  echo "Geben Sie hier die Nummer ein.<br>\n";
}
else if ($_SESSION['player']->isValidSMSSenderNumber()) {
  echo "Die eingestellte Nummer ist g�ltig und �berpr�ft.<br>\n";
}
else {
  echo "<b class=\"error\">Die Nummer wurde noch nicht auf G�ltigkeit �berpr�ft</b>.".
  " Wenden Sie sich an einen Administrator.<br>\n";
}
?>
<input type="text" name="sms_sender_number" value="<? echo $_SESSION['player']->getSMSSenderNumber(); ?>">
&nbsp;&nbsp;
<input type="submit" name="changesms_sender_nr" value=" �ndern "><br>
<input <? if($_SESSION['player']->showSMSSenderNumber()) echo "checked"; ?>
 type="checkbox" name="show_sender_nr" value="1"> Nummer beim Versand anzeigen
</form>
<p>
Zur Zeit k�nnen wir nur <b>Rufnummern aus Deutschland</b> annehmen.<br>
Geben Sie Ihre Nummer in der Form <b>0172 12345678</b> ein! 
Das System bringt die Nummer dann automatisch in ein internationales
Format mit +49 am Anfang (also nicht wundern).
<?  
  }
  else {
    echo '<h2 class="error">Sie d�rfen leider keine Absenderkennung einstellen.</h2> Weiter Informationen '.
      'auf der <a href="premium.php">Premium-Account Informationsseite</a>.';
  } 
?>
</td></tr>
<tr><td>

</td></tr>
</table><!-- SMS Sender Table -->
</td></tr>


<!-- SMS Kontingent -->
<a name="charge"></a>
<tr class="tblhead_22"><td colspan="2">
<h2><a name="sms_konto">SMS Kontingent aufladen</a></h2>
</td></tr>
<tr class="tblbody">
<td colspan="2" align="center">
<b>Um einem Mitspieler eine SMS auf sein Empfangsger�t zu senden, m�ssen
Sie Ihr Kontingent aufladen.</b><p>
Beim Versand einer SMS �ber das Holy-Wars 2 SMS-System wird dann eine
SMS von Ihrem Kontingent abgezogen.<br>
Dies geschieht �hnlich einer Prepayd-Karte eines Mobilfunk-Anbieters.<p>
Um Ihr SMS-Kontingent aufzuladen, nehmen Sie Kontakt mit einem<br>
<a target="_blank" href="impressum.php">Holy-Wars 2 Administrator</a> auf.
</td>
</tr>

<!-- Premium SMS an Account -->

<tr class="tblhead_22"><td colspan="2"><h2><a name="sms_get">SMS Empfang</a></h2></td></tr>
<tr class="tblbody">
  <td colspan="2" align="center">
<b>Sie k�nnen SMS auf Ihren Holy-Wars 2 Account empfangen.</b><p>
Dazu mu� ein Mitspieler eine kostenpflichtige SMS an Holy-Wars 2 senden, 
Sie zahlen daf�r nichts. 
  </td>
</tr>
<tr height="10"><td></td></tr>

<tr class="tblhead_22">
 <td><h2><a name="sms_premium">Premium-Lite per SMS</a></h2></td>
</tr>
<tr><td colspan="2" align="center">
<? include("includes/premium_sms.php"); ?>
</td></tr>

<!-- SMS Regeln -->
<a name="sms_rules"></a>
<tr class="tblhead_22"><td colspan="2">
<h2><a name="sms_use">Nutzungsbedingungen des SMS-Systems</a></h2>
</td></tr>
<tr class="tblbody">
 <td colspan="2" align="center">
   <b>Sie haben den Nutzungsbedingungen bereits zugestimmt.<p>
   <hr>
   <table width="400"><tr><td>
    <? include("includes/sms_rules.html"); ?>
    </td></tr>
   </table>
   <hr>
  </td>
</tr>
</table>
<?
  }
  else {
    show_sms_acceptrules(); 
  }
} // function


function show_grapa() 
{ 
?>

<table id="settings">
 <tr class="tblhead_22">
  <td colspan="2"><h2><a name="grapa_pack">Grafikpack / Lokaler HW2-Ordner</a></h2></td>
 </tr>
 <tr class="tblbody">
  <td colspan="2" align="center">
    <table width="400"><tr><td>
      <h3>1. Grafikpaket runterladen</h3>
Den aktuellen Grafikpack Version <b><? echo GFX_VERSION; ?></b> finden Sie <a target="_new" href="grafikpaket/hw_grafik_v<? echo GFX_VERSION; ?>.zip">hier</a>.
<? $gp_update = "grafikpaket/hw_grafik_update_v".GFX_VERSION.".zip";
 if (file_exists($gp_update)) { 
   echo '<br>Falls Sie nur einige neue Grafiken ben�tigen, dann finden Sie ein Update <a href="'.$gp_update.'">hier</a>.';
 }
?>
    <h3>2. Entpacken</h3>
Entpacken Sie den Grafikpfad auf Ihre Festplatte.<br>
Unter Windows XP k�nnen Sie
die Datei im Explorer entpacken, indem Sie mit der rechten Maustaste darauf
klicken und dann &quot;Alle Extrahieren&quot; ausw�hlen.
    <h3>3. Grafikpfad einstellen</h3>
      Klicken Sie auf das Feld &quot;Durchsuchen&quot; (oder &quot;Browse&quot;) und w�hlen Sie die Datei <b>gfx_version.gif</b> an der Stelle aus,
    wo Sie den Grafikpack (Schritt 2.) ausgepackt haben. Benutzen Sie den Button &quot;Testen&quot; zum �berpr�fen, ob alles in Ordnung ist.
    <hr>
    Der aktuelle Grafikpfad lautet: 
 <? 
    if ($_SESSION['player']->getGFX_PATH() != null) {
      $version = $_SESSION['player']->getGFX_PATH();
      $pos = strpos($version, "images/ingame");
      if($pos) {
        $version = substr($version, 0, $pos);
      }
	
	  $version .= "/gfx_version.gif";
      
      echo '<pre>'.$_SESSION['player']->getGFX_PATH()."</pre>";
      
      echo '<br>Lokale Version: <img src="'.$version.'">';
      echo '&nbsp;&nbsp;Server Version: <img src="gfx_version.gif">';
      echo "<p>Beide Grafiken sollten �bereinstimmen.";
    }
    else 
      echo "<b>&lt;bisher kein Pfad gesetzt&gt;</b>";
 ?>
<hr>
    <form onSubmit="return false" name="theFileForm" method="get" enctype="multipart/form-data">
    <input class="input" accept="image/gif" width="50" type="file" name="gfx_path_browse" value=" Durchsuchen "
           onchange="document.theGFXForm.gfx_path_value.value=document.theFileForm.gfx_path_browse.value;"
    />
     
    <select class="input" name="gfx_path_select" 
            onchange="document.theGFXForm.gfx_path_value.value=document.theFileForm.gfx_path_select.value"
    >
      <option value="">Oder hier ausw�hlen</option>
<!--  <option value="SERVER4">Server Version 4 (Standard)</option> -->
      <option value="SERVER3">Server Version 3</option>
      <option value="HOWLY4">Version 4 by Howly</option>
    </select>
    </form>
    <!-- onSubmit="document.theGFXForm.gfx_path_value.value=document.theFileForm.gfx_path_select.value;"  -->
    <form name="theGFXForm" action="settings.php" method="get">
    <input type="text" name="gfx_path_value">
    <input type="hidden" name="changegfx">
    <input type="button" onClick="testGfxPath(document.theGFXForm.gfx_path_value.value)" name="testgfx"   value=" Testen ">
    <input type="submit" 
    onClick="if(document.theGFXForm.gfx_path_value.value=='') { alert('Sie m�ssen zuerst einen Pfad ausw�hlen oder eingeben!'); return false;}" 
    name="changegfx" 
    value=" �ndern ">
    </form>
    <p>
    Falls es zu Darstellungsfehlern kommt erst einmal aus- und neu einloggen!<br>
    <b>ACHTUNG:</b> FireFox ab 1.0 und Mozilla ab 1.8a5 m�ssen zur Verwendung umkonfiguriert werden. 
    Wie das funktioniert ist beschrieben, wenn man den Button &quot;Testen&quot; klickt.
    </td></tr>
    </table>
   </td>		
  </tr>


<tr class="tblhead_22">
  <td colspan="2"><h2><a name="grapa_menu">Men� und sonstige Einstellungen</a></h2></td>
</tr>
  <tr class="tblbody">    
   <td colspan="2">
    <form name="theMenuForm" method="get">
    <center>
<div style="width:400px; border: 1px solid black; text-align: left; margin: 4px; ">
<?php
  {
    settings_check_button_br("forum_own",               "Forum in einem extra Fenster �ffnen.");    
    settings_check_button_br("map_own",                 "Karte in einem extra Fenster �ffnen.");
    settings_check_button_br("library_own",             "Bibliothek in einem extra Fenster �ffnen.");
    settings_check_button_br("hide_banner",             "Werbe-Banner nicht anzeigen (Premium-Account).");    
    settings_check_button_br("disable_login_counter",   "Login-Ticker ausblenden.");

    if ($_SESSION['player']->isAdmin()) {
      $setts = do_mysql_query_fetch_assoc("SELECT settings FROM player WHERE id = ".$_SESSION['player']->getID());
      echo "<pre>";
      printf("%x\n", $setts['settings']);
      var_dump($_SESSION['settings']);
      echo "</pre>";
    }
  }
?>
    </div>
        <input type="submit" name="changesettings" value=" Einstellungen setzen ">&nbsp;
        <input type="submit" name="savesettings" value=" Dauerhaft speichern " 
onClick="<?
if ($_SESSION['premium_flags'] > 0) 
  echo "return confirm('Wollen Sie die Einstellungen wirklich abspeichern?')";
else
  echo "alert('Dies funktioniert nur f�r Besitzer eines Premium-Accounts.'); return false;";
?>
">&nbsp;
        <input type="submit" name="loadsettings" value=" Letze Version laden "><p>
Bei &quot;Dauerhaft Speichern&quot; werden die Einstellungen 
auf dem Server gespeichert und sind nach jedem Login wieder verf�gbar.
    </center>
    </form><!-- theMenuForm -->
   </td>   
  </tr>
 
</table>
<?
}

function settings_check_button_br($name, $text) {
  echo '<input style="text-align:left; " value="1" name="'.$name.'" type="checkbox"';
  if ($_SESSION['settings'][$name]) echo " checked";
  echo '> '.$text."<br>\n";
}


function show_game() { ?>

<table id="settings">
<form id="theSettingsForm" name="theSettingsForm" action="settings.php" method="post">
<tr class="tblhead_22">
 <td colspan="2"><h2><a name="game_player">Spieler-Beschreibung</a></h2></td>
</tr>
<tr class="tblbody">
 <td colspan="2" align="center"><textarea id="theText" name="theText" rows="5" cols="45" name="theText"><? echo $_SESSION['player']->getDescription(); ?></textarea></td>
</tr>
<script type="text/javascript">
  document.theBBForm = document.theSettingsForm;
</script>
<?php
insertBBForm(2);
?>
<tr class="tblbody">
 <td colspan="2" align="center"><input type="submit" name="change_desc" value=" Beschreibung �ndern "></td><br>
</tr>


<tr class="tblhead_22">
 <td colspan="2"><h2><a name="game_sig">Signatur �ndern</a></h2></td>
</tr>
<tr class="tblbody">
<td colspan="2" align="center">
<? 
   if (is_premium_signature()) {
     echo 'Euer <a href="premium.php">Premium-Account</a> erlaubt Euch die �nderung der Nachrichten-Signatur<p>';
     echo '<textarea id="msgsignature" rows="4" cols="45" name="msgsignature">'.$_SESSION['player']->getMsgSignature(true).'</textarea><p>';
     echo '<input type="submit" name="changesignature" value=" Signatur �ndern ">';
     echo '</td>';     
   }
   else {
     echo 'Um eine Signatur setzen zu d�rfen m�ssen Sie einen <a href="premium.php">Premium-Account</a> besitzen!</td>';
   }
  ?>
   
   </tr>
  </form>
  <a name="newavatar"></a>
  <tr class="tblhead_22"><td colspan="2"><h2><a name="game_avatar">Avatar einstellen</a></h2></td>
  </tr>
  <tr>
  <td colspan="2" class="tblbody">
<?php
{     
  if(defined("AVATAR_TOP_POINTS")) {
    $avatar_top_points = AVATAR_TOP_POINTS;
  }
  else {
    $avatar_top_points = "1000000";    
  }

  $res=do_mysql_query("SELECT toplist FROM player WHERE toplist <= '100' AND toplist > '0' AND points >='".$avatar_top_points."' AND id='".$_SESSION['player']->id."'");
  
  if(mysql_num_rows($res)>0 || is_premium_avatar() ) {
    if(!is_file( AVATAR_DIR.$_SESSION['player']->id.".jpg")) {
      echo "Hier k&ouml;nnt ihr einen Avatar auf den Server laden.<br />";
      echo "Bei dem Bild muss es sich um ein JPEG Bild handel!";
      echo "<form name=\"upload\" id=\"upload\" ENCTYPE=\"multipart/form-data\" method=\"post\">Datei hochladen <input type=\"file\" id=\"userfile\" name=\"userfile\">\n";
      echo "<input type=\"submit\" name=\"upload\" value=\"Datei Hochladen\"> \n";
      echo "\n";
    } 
    else {
      echo "<div style=\"float:left; margin-right:20px;\"><img src=\"avatar.php?id=".$_SESSION['player']->id."\"></div>";
      echo "Dein Avatar wurde hochgeladen";
      $res=do_mysql_query("SELECT avatar FROM player WHERE id = '".$_SESSION['player']->id."'");
      $img=mysql_fetch_assoc($res);
      if($img['avatar']==1) {
        echo ".<p><b class=\"error\">Dein Avatar muss erst von einem Namehunter freigeschalten werden!</b><p>";
        echo "<a href=\"".$PHP_SELF."?delete=avatar\">Avatar l&ouml;schen</a>\n";
      } 
      elseif($img['avatar']==2) {
        echo " und ist freigeschalten!<p>";
        echo "<a href=\"".$PHP_SELF."?delete=avatar\">Avatar l&ouml;schen</a><p>\n";
      } 
      else {
        do_mysql_query("UPDATE player SET avatar=NULL WHERE id='".$_SESSION['player']->id."'");
      }
    }
  }

  {
    echo '<p>Um einen Avatar setzen zu d�rfen m�ssen Sie einen '.
    '<a href="premium.php">Premium-Account</a> besitzen '.
    'oder in der Top-100 mit mindestens '.prettyNumber($avatar_top_points).' Punkten auftauchen!';
  }
}
  ?>
</td>
</tr>

 
  <tr class="tblhead_22"><td colspan="2"><h2><a name="game_tutorial">Tutorial steuern</a></h2></td>
  </tr>
  <tr>
   
   <td colspan="2" class="tblbody" align="center" style="padding-bottom: 15px;">
    <img src="<?php echo GFX_PATH_LOCAL; ?>/mage_small.gif" align="left" />
    Der interaktive Helfer kann hier aktiviert oder abgeschalten werden.
    <p>
    <a href="?activatetutorial=1">Tutorial aktivieren</a>&nbsp;&nbsp;&nbsp;<a href="?activatetutorial=0">Tutorial deaktivieren</a>
    <br>  
  </td>
</tr>
</form>
</table>
<?
}

function show_premium() { ?>

<table id="settings">
<tr class="tblhead_22">
 <td><h2><a name="premium_info">Allgemeine Informationen</a></h2></td>
</tr>
<tr><td><b>Premium-Flags:</b> <? echo $_SESSION['premium_flags'];?></td></tr>
<tr><td><b>No-ADS (HW2 werbefrei?):</b> 
<? if($GLOBALS['premium_flags'] & PREMIUM_NOADS) {
     echo " Ja</b>"; 
     if(! $_SESSION['settings']['hide_banner'])
       echo ', Ihnen wird aber Werbung eingeblendet. Dies k�nnen Sie im Reiter <a href="settings.php?show=grapa">Grafik/Men�</a> �ndern. ';
     else
       echo ', die Werbung ist abgeschaltet.';
}
 else {   
   echo " Nein.</b> Ihnen wird Werbung eingeblendet. "; 
 } ?>
</td></tr>
<tr><td><b>Laufzeit:</b> bis <? echo get_premium_expire_string($_SESSION['premium_expire']); ?> Uhr</td></tr>
<tr><td><b>Maximale Sitzungs-Dauer:</b> <? echo $_SESSION['session_duration'] / 3600; ?> Stunden.</td></tr>
<tr><td><b>Gr&ouml;&szlig;e des Nachrichten-Archivs:</b> <? echo get_message_archive_size(); ?> Nachrichten.</td></tr>
<tr><td align="center">-&gt; <a href="premium.php">Hier findet Ihr Informationen zum Premium-Account</a> -&lt;</td></tr>


<tr height="10"><td></td></tr>
<tr class="tblhead_22">
 <td><h2><a name="premium_abook">Adressbuch</a></h2></td>
</tr>
<tr><td colspan="2" align="center">
<? include("includes/adr.func.php"); 
 print_address_book();
  ?>
</td></tr>

<tr height="10"><td></td></tr>
<tr class="tblhead_22">
 <td><h2><a name="premium_sms">Premium-Lite per SMS</a></h2></td>
</tr>
<tr><td colspan="2" align="center">
<? include("includes/premium_sms.php"); ?>
</td></tr>


<tr height="10"><td></td></tr>
<tr class="tblhead_22">
 <td><h2><a name="premium_banner">Werbebanner ein/ausschalten</a></h2></td>
</tr>
<tr><td colspan="2" align="center">
Unter <a href="settings.php?show=grapa">&quot;Grafik / Men�&quot;</a> k�nnen Sie Banner ein- und ausschalten, sofern
Ihr Premium-Account das zul�sst.
</td></tr>


</table>
<?
}

function show_ads() {
  $url = "http://".$_SERVER['HTTP_HOST']."/register.php?recruiter=".$_SESSION['player']->getID();
?>

  <table id="settings">
  <!-- Spieler werben -->
  <tr class="tblhead_22">
    <td colspan="2" align="center"><h2>Dein Link zum Werben neuer Spieler</h2></td>
  </tr>
  <tr class="tblbody">
    <td colspan="2" style="padding:10px;"><center><span style="font-weight: bold; background-color: white; padding: 4px; margin-top: 4px;"><? echo $url; ?></span></center>
<p>Langfristig erh�ltst Du f�r jeden geworbenen Spieler <u>Bonuspunkte</u>!
Es gibt jeweils 1000 Punkte f�r Dich, sobald der geworbene Spieler eine
dieser drei Forschungen abschlie�t: <i>H�here Bildung</i>, <i>Fachrichtungen</i> und <i>Konstitutionelle Monarchie</i>.
<p>
Wer aktive Spieler wirbt hat also klare Vorteile, da er bis 
zu 3000 Bonuspunkte pro geworbenem Spieler erh�lt.
<p>
<b>Tipp</b>: Platziere den Link auf einer Homepage, in Diskussionsforen als Signatur oder sende Ihn per Email an Deine Freunde. Der Link ist nicht dazu gedacht, ihn wild in Foren, G�steb�chern oder Chats zu posten oder damit zu spammen.<p>
<center>
<b>HTML-Code f�r die Homepage:</b><br>
<div style="width: 530px; font-size: 9px; background-color: white; padding: 4px; margin-top: 4px;">
<? {
  $code = '<a href="'.$url.'" target="_blank" title="Hier klicken und kostenlos bei Holy-Wars 2 anmelden"><img border="0" alt="Holy-Wars 2 Banner" src="http://www.holy-wars2.de/hw2_banner.php/1.gif" width="468" height="60"></a>';
  echo htmlentities($code);
}
?>
</div>
<b>Code f�r PHPBB-Foren, z.B. in der Signatur:</b><br>
<div style="width: 530px; font-size: 9px; background-color: white; padding: 4px; margin-top: 4px;">
<?
  $Rbbcode = '[url='.$url.'][img]http://www.holy-wars2.de/hw2_banner.php/1.gif[/img][/url]';
  echo htmlentities($Rbbcode);
?>
</div>
<br><b>Dies sieht dann so aus:</b><br>
<? echo $code; ?>
</center>

</tr>   
</table>
  <?
}

function show_account() { 
  $url = "http://".$_SERVER['HTTP_HOST']."/register.php?recruiter=".$_SESSION['player']->getID();
?>

<table id="settings">
<form id="theSettingsForm" name="theSettingsForm" action="settings.php" method="post">             
<tr class="tblhead_22">
    <td colspan="2"><h2><a name="acc_allinf">Allgemeine Informationen</a></h2>(bitte ID bei Supportanfrage mit angeben)</td>
  </tr>
  <? if(!defined("OLD_GAME")) {?>
			<tr class="tblbody">
				<td width="50%"><b>Login</b></td>
				<td><? echo $_SESSION['player']->getLogin(); ?></td>
			</tr>
  <? } ?>
  <tr class="tblbody">
    <td width="50%"><b>Spielername</b></td>
    <td><? echo $_SESSION['player']->getName(); ?></td>
  </tr>
  <tr class="tblbody">
    <td width="50%"><b>Account-ID</b></td>
    <td><? echo $_SESSION['player']->getID(); ?></td>
  </tr>
	<tr class="tblbody" valign="top">
         <td width="50%"><b>Session-Infos</b></td>
         <td>
          IP-Adresse: <? echo $_SESSION['player']->getIp(); ?><br>
          Sitzungsdauer: <? echo round( (time() - $_SESSION['login_time'])/60); ?> Minuten<br>
          Seitenaufrufe: <? echo $_SESSION['click']->count; ?> (<? echo ceil(60 * $_SESSION['click']->count/ (time() - $_SESSION['login_time']) ); ?> pro Minute)
         </td>
	</tr>
	<tr class="tblbody">
		<td width="50%"><b>Eigener Punktestand</b> (wird alle 30 Minuten aktualisiert)</td>
		<td><? echo prettyNumber($_SESSION['player']->getPoints())." (Durchschnitt ".prettyNumber($_SESSION['player']->getAvgPoints()).")"; ?></td>
	</tr>

<a name="bonuspts"></a>
<tr class="tblhead_22">
    <td colspan="2"><h2><a name="acc_bonus">Bonuspunkte</a></h2></td>
</tr>
	<tr class="tblbody">
		<td width="50%"><b>Verbleibende Bonuspunkte</b></td>
		<td><? echo $_SESSION['player']->getBonuspoints(); ?></td>
	</tr>
<? if(!defined("OLD_GAME")) {?>	
	<tr class="tblbody">
		<td width="50%"><b>Letzer Klick-Bonuspunkt</b></td>
		<td><? echo date("d.m.Y G:i", $_SESSION['player']->getLastClickBonusPoints()); ?></td>
	</tr>
<? } ?>
	<tr class="tblbody">
	  <td colspan="2" style="padding: 10px">
          <i>Wie bekomme ich Bonuspunkte?</i><br>
          durch...
          <ul style="margin: 0px; padding-left: 15px">
	     <li>Werben neuer Mitspieler (siehe <a href="settings.php?show=ads">&quot;F�rdere HW2&quot;</a>)
	     <li>&quot;Klick-Bonuspunkte&quot;: regelm�ssiges Einloggen bzw. Klicken (einmal pro Stunde einen Punkt)
	     <li>Veranstalten von <a href="tournament.php">Ritterturnieren</a>.
          </ul>
          </td>
	</tr>


<tr class="tblhead_22">
 <td colspan="2"><h2><a name="acc_email">Emailadresse �ndern</a></h2></td>
</tr>
  <tr class="tblbody">
    <td colspan="2" align="center">
      <input type="text" name="email" value="<? echo $_SESSION['player']->getEMail(); ?>">&nbsp;&nbsp;
      <input type="submit" name="change_email" value=" Emailadresse �ndern ">
    </td>
  </tr>



  <tr height="10"><td></td></tr><tr class="tblhead_22">
    <td colspan="2"><h2><a name="acc_pass">Passwort �ndern</a></h2></td>
  </tr>
  <tr class="tblbody"><td align="right"><input type="password" name="oldpw"> </td><td> (altes Passwort)</td></tr>
  <tr class="tblbody"><td align="right"><input type="password" name="newpw1"></td><td> (neues Passwort)</td></tr>
  <tr class="tblbody"><td align="right"><input type="password" name="newpw2"></td><td> (Passwort wiederholen)</td></tr>
  <tr class="tblbody">
    <td colspan="2" align="center"><input type="submit" name="change_passwd" value=" Passwort �ndern "></td>
  </tr>


  <tr height="10"><td></td></tr><tr class="tblhead_22">
    <td colspan="2"><h2><a name="acc_accdel">Account l�schen</a></h2></td>
  </tr>
  <tr class="tbldanger">
    <td colspan="2" align="center">
      <table border="0" cellpadding="0" cellspacing="0">
	<tr>
	  <td><b>#1</b>&nbsp;<select name="acc_delete0"><option value="0">JA</option><option value="1" selected>NEIN</option></select></td>
	  <td><b>#2</b>&nbsp;<select name="acc_delete1"><option value="0">JA</option><option value="1" selected>NEIN</option></select></td>
	  <td><b>#3</b>&nbsp;<select name="acc_delete2"><option value="0">JA</option><option value="1" selected>NEIN</option></select></td>
        </tr>
	<tr><td colspan="3">&nbsp;</td></tr>
	<tr>
	  <td colspan="3" align="center"><input type="submit" name="acc_delete_process" value="ACCOUNT L�SCHEN"></td>
	</tr>
      </table>
    </td>
  </tr>
  <tr class="tbldanger">
    <td colspan="2" align="center">Achtung: Eine Accountl�schung kann nicht r�ckg�ngig gemacht werden!</td>
  </tr>
  <tr height="10"><td></td></tr><tr class="tblhead_22">
    <td colspan="2"><h2><a name="acc_vacmod">Urlaubsmodus</a></h2></td>
  </tr>
<? if (!defined("SPEED") || !SPEED) { ?>
  <tr><td class="tblbody" style="padding:10px;" colspan="2">
   <b>Falls Ihr f�r l�ngere Zeit nicht in Euren Account schauen k�nnt, dann
   k�nnt Ihr hier in den Urlaubsmodus wechseln.</b><p>
   Folgendes tritt ein, wenn ein Spieler im Urlaubsmodus ist
   <ul>
    <li>Keine neuen Angriffe auf den Spieler sind m�glich. Bestehende Angriffe laufen weiter.
    <li>Der Spieler hat nur noch 50% Truppenkosten.
    <li>Der Spieler erh�lt keine Ressourcen- und keine Ausr�stungs-Produktion.
    <li>Der Spieler erh�lt 50% weniger Steuern und Forschungspunkte.
    <li>Alle Einnahmen aus Geb�uden sind 0. Bei Ausgaben werden diese weiterhin gezahlt.
    <li>Der Spieler kann sich erst wieder nach Ablauf des Urlaubsmodus einloggen.
    <li>Zwischen 10 bis 35 Tagen kann der Urlaubsmodus gew�hlt werden.
    <li>Nach Ablauf des Urlaubsmodus ist 14 Tage lang kein neuer Urlaubsmodus m�glich.
    <li>W�hrend und noch 7 Tage lang nach Ablauf des Urlaubsmodus ist der Spieler vor dem Inaktivit�ts-Skript sicher und wird nicht wegen Inaktivit�t gel�scht.
   </ul>
   Folgende Vorbedingungen m�ssen erf�llt sein:                                  
   <ul>
    <li>Der Spieler darf keine Truppen in fremden St�dten stationiert haben.
    <li>Keine eigenen Truppenbewegungen (ausser R�ckkehr) d�rfen aktiv sein.
   </ul>
   <p>
   <center>
   Tage: <input type="text" name="holidaydurance" value="14" size="4">
   <input type="submit" name="holidaymode" value=" Urlaubsmodus aktivieren ">
   <select name="holidayaccept"><option value="1">JA</option><option value="0" selected>Nein</option></select>
   <p>
   Achtung: der Urlaubsmodus wirkt sofort!
   </center>
  </td></tr>
<? 
  } // !SPEED
  else {
?>
  <tr><td class="tblbody" style="padding:10px;" colspan="2">Urlaubsmodus in dieser Runde nicht verf�gbar.</td></tr>
<?  
  }
?>

<a name="exception"></a>
  <tr class="tblhead_22">
    <td colspan="2"><h2><a name="acc_mulexc">Multi-Exceptions</a></h2></td>
  </tr>
  <tr class="tblbody">
    <td colspan="2" style="padding:10px;">Hier kann man eine sogenannte Multi-Exception beantragen. Die ist eine Ausnahmeregelung f�r Spieler,
die sich �ber eine gemeinsame Internet-Leitung (z.B. einen DSL-Router oder am Netzwerk auf der Arbeit) in Holy-Wars 2
einloggen. Die Exception verhindert, dass man gesperrt wird, wenn man �ber die gleiche IP-Adresse verbunden wird.<p>
Die Exception verhindert jedoch nicht, dass man aufgrund von Sitting oder Multiaccounting gesperrt werden kann. 
Zwischen Spielern, f�r die eine Exception akzeptiert wurde, <b>d�rfen keine Resourcen transferiert werden</b> 
(weder per Versenden noch �ber Angebote akzeptieren, auch nicht �ber Dritte).<p>
Exceptions werden nachtr�glich nicht wieder aufgehoben!
<hr>
</td></tr>
<tr class="tblbody">
    <td colspan="2"><? show_own_exceptions();?></td>
  </tr>
	<tr class="tblbody">
		<td colspan="2">
                  <table>
		    <tr><td colspan="2"><b>Multiexception beantragen:</b><br>
		    Betroffene Spieler (ohne Dich) durch , (Komma) separieren. Beispiel: User1, User2, User3
                    </td></tr>
		    <tr><td>Spieler</td><td>Begr�ndung</td></tr>
       		    <tr><td><input type="text" name="multi_players" size="20"></td><td><input type="text" name="multi_note" size="40"></td></tr>
		    <tr><td colspan="2"><input type="submit" name="change" value="Beantragen"></td></tr>
		  </table>
                </td>
	</tr>
	<tr class="tblbody">
		<td colspan="2">&nbsp;</td>
	</tr>

        
	<tr class="tblhead_22">
		<td colspan="2"><h2><a name="acc_zitat">Neues Zitat erstellen</a></h2></td>
	</tr>
	<tr>
		<td colspan="2" class="tblbody" text-align="center">	   
		<textarea style="width:100%;" name="zitattext" rows="5"></textarea>
		<input type="submit" name="zitat" value="Zitat an Administratoren abschicken" />
		</td>
	</tr>

  <tr class="tblbody">
    <td colspan="2">&nbsp;</td>
  </tr>
</form>

</table>

<?
} // function show_account()
?>