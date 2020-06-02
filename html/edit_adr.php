<?php
/***************************************************
 * Copyright (c) 2006
 *
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 ***************************************************/
include_once("includes/session.inc.php");
include_once("includes/sms.func.php");
include_once("includes/adr.func.php");

/**
 * Adressbuch
 *
 * Die Adressbuch-Tabelle enthält sowohl Spieler als
 * auch SMS-Nummern.
 * 
 * Ist "nicename" bei einem Eintrag gesetzt, dann wird
 * dieser angezeigt, ansonsten eben Name oder SMS.
 *
 * Gemeinsame Funktionen für das Adressbuch sind in
 * includes/adr.func.php zu finden.
 */
//$error  = "";
//$inform = "";

if(isset($GLOBALS['addname'])) {
  $error = adr_add_name($GLOBALS['addname']);
  if ($error == null) {
    $inform = "Der Spieler &quot;".$GLOBALS['addname']."&quot; wurde Ihrem Adressbuch hinzugefügt";
  }
}
else if (isset($GLOBALS['del'])) {
  $error = adr_del_entry($GLOBALS['del']);
  if ($error == null) {
    $inform = "Eintrag gelöscht.";
  }  
}

?>




<html>
<head>
<title><? echo $pagetitle; ?></title>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/bg.gif">
<center>
<?
echo '<div align="center">'.show_banner(0)."</div>";

if (isset($edit)) {
  $error = adr_edit_entry($edit);
}
else if (isset($GLOBALS['addentry'])) {
  if (!isset($addnew) && (!isset($iname) || strlen($iname) == 0)) {
    $error = "Sie müssen eine Bezeichnung für den Eintrag eingeben";
  }
  else if (isset($ismsprefix) && isset($ismsnr) ) {
    $sms = $ismsprefix.$ismsnr;
    if (!valid_sms_nr($sms))
      $error = "Die Nummer ist ungültig.";
    else {
      $error = adr_add_sms($iname, $sms);
      if ($error == null) {
        $inform = "Eintrag &quot;$iname&quot; mit SMS $sms angelegt.<br>".
          "Dieser Eintrag erscheint nun in Ihrem Adressbuch auf der ".
          '<a href="sms.php">SMS-Senden-Seite</a>.';
        $adddone = true;
      }
    }
  }
  if (!isset($adddone)) 
    adr_new_entry_form();
}


if($error != null && strlen($error) > 0) {
  echo '<div align="center" style="font-size: 12px; color: red; margin-bottom: 5px;"><h2>Fehler</h2><b>'.$error."</b></div><p>\n";
}

if($inform != null && strlen($inform) > 0) {
  echo '<div align="center" style="font-size: 12px; color: darkgreen; margin-bottom: 5px;"><b>'.$inform."</b></div><p>\n";
}


echo "<h1>Ihr Adressbuch</h1>\n";
print_address_book();
echo "<hr>Ihr Adressbuch können Sie jederzeit unter MyHW2 in der Rubrik Premium-Account einsehen.";
?>

</center>

<script language="JavaScript">
<!-- Begin
  window.focus();
// End -->
</script>

</body>
</html>
