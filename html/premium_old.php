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

include("includes/db.inc.php"); 
include("includes/player.class.php");
session_start();
?>
<html>
<head>
 <title>HW2-NoADS und HW2-Premium-Account</title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<link rel="stylesheet" href="<? echo $GLOBALS['$csspath']; ?>/hw.css" type="text/css">

<style>
body,div,a {
  font-family: Tahoma;
  font-size: 12px;
  color: 000000;
  margin: 2px;
}
h1 {  font-size: 18px;  }
h2 {  font-size: 14px; color: 202020; }
</style>

<body marginwidth="0" marginheight="0" topmargin="2" leftmargin="2" background="<? echo $imagepath; ?>/bg.gif">
<table width="600" align="center"><tr><td>
<center><a href='noads.php'><img src='<? echo $imagepath; ?>/noads.jpg' border='0' alt='Premium Acc, kein Banner'></a></center><br>

<?php
if(isset($_SESSION['player'])) {
  $pid = $_SESSION['player']->getID();
  $sql = "SELECT count(*) AS cnt FROM premiumacc WHERE player = ".$pid;
  $cnt = do_mysqli_query_fetch_assoc($sql);
  
  if($cnt['cnt'] > 0)
  {
    if($_SESSION['premium_flags'] == 0)
    {
?>
      <h1>Premium Pro testen</h1>
      Ihr habt/hattet bereits einen Testaccount beantragt.
<?
    }
  }
  else 
  {
    $days = defined("PREMIUM_TEST_DURATION") ? PREMIUM_TEST_DURATION : 7;
    
    if(isset($_REQUEST['testpremium']))
    {
      $type = 15;
      
      $sql  = sprintf("INSERT INTO premiumacc (player, type, start, expire, paytext) ".
                      "VALUES (%d, %d, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()+%d, 'Premium-Pro KOSTENLOS testen.')",
                      $pid, $type, $days * 3600 * 24);
      do_mysqli_query($sql);
      printf("<h1>Premium-Pro Erfolgreich aktiviert</h1>\nBitte <b>loggen Sie sich neu ein</b>, um den Account zu aktivieren!");
    }
    else 
    {
      printf("<h1>Premium Pro testen</h1>\nIhr könnt %d Tage Premium-Pro <b>kostenlos</b> und uneingeschränkt* testen! Einfach auf den folgenden Button klicken:<p>\n", $days);
?>  
      <form method="GET" action="premium.php">
        <input type="submit" name="testpremium" value=" Jetzt kostenlos testen! "/>
      </form>
      Die Besonderheiten von Premium Pro sind <a href="#accounts">weiter unten</a> einsehbar.<br>
      <small>*) Keine SMS, kein Namensschutz</small>
<?
    } // else if(isset($REQUEST['testpremium']))
  }
} 
?>
<h1>Werbefrei (NoADS) und Premium-Accounts ab EUR 2,99</h1>
<i>Sie spielen Holy-Wars 2 gerne und oft? 
Sie möchten weitere Funktionalitäten im Spiel nutzen? 
Durch eine freiwillige Spende erhalten Sie als Spieler Zugang zu vielen weiteren Features.
Jedoch entstehen durch diese Features keine Vorteile gegenüber anderen Spielern, 
welche keinen Premiumaccount besitzen. Es wird nur der "Komfort" gesteigert.
<p>
Je nach Höhe der Spende an das Holy-Wars 2 Projekt richten wir den gewünschten NoADS
oder Premium-Account ein.</i>
<p>
Durch die Spende entstehen keinerlei Rechtsansprüche der Spielleitung gegenüber. 
Bei Ausfall oder Abschaltung des Spieles werden keine Spenden rückerstattet. 
Jede Spende kommt der Spielentwicklung und Erhaltung zugute.
<p>
Wir weisen noch einmal ausdrücklich darauf hin, dass auch bei der Nutzung eines
Premium-Accounts die 
<a target="_new" href="https://<? echo $_SERVER['HTTP_HOST'];?>/register.php?info=1">Nutzungsbedingungen</a>
weiterhin einzuhalten sind. Bei Verstößen und Löschung des Accounts verfallen
auch sämtliche Premium-Merkmale.
<hr>
<h1>Erwerb eines Premium-Accounts</h1>
Es stehen Ihnen mehrere Zahlungsm&ouml;glichkeit Verf&uuml;gung.<br>
Geben Sie als Verwendungszweck Ihre <b>Spieler-ID 
<? 
if (isset($_SESSION['player'])) {
 echo "(".$_SESSION['player']->getID().")"; 
} 
else {
 echo "(diese finden Sie unter Einstellungen, sobald Sie eingeloggt sind) ";
}
?>
</b> an,  sowie den gewünschten Premium-Account.
<ul>
<li>
<h2>Banküberweisung</h2>
Hier die Daten des HW2-Kontos:<br>
<? include("includes/konto.php"); ?>
<? 
/*
<li>
<h2>Premium-Lite per SMS</h2>
*/ ?>
<? //include("includes/premium_sms.php");?>

</ul>
<hr>
<a name="accounts"></a>
<h1>Premium-Accounts, Laufzeit und Kosten</h1>
<h2 <? if($mark=='noads') echo 'style="color: blue;"'; ?>>HW2 NoADS</h2>
<ul>
<li <? if($mark=='noads') echo 'style="color: blue;"'; ?>>HW2 Werbefrei!
<li>Keine Unterbrechung durch "Halt" -Seite
<li>Keine Werbebanner, somit schnellerer Spielfluss
<li>Autologout erst nach 2 Stunden
<li>8 Angebote pro Seite auf dem Marktplatz
<li>Erweiterte Statistiken
<li>Mehr Turniere bestreiten und mehr Turniere veranstalten
<li>&quot;voice&quot; im IRC-Channel
</ul>
<table><tr><td>
6 Monate EUR 8,00<br />
12 Monate EUR 15,00<br />
</td><td>
<? if(isset($_SESSION['player'])) paypal_button("8.00", "sechs Monate Premium NoADS"); ?>
</td></tr></table>

<hr><h2>HW2 Premium Lite</h2>
<ul>
<li <? if($mark=='noads') echo 'style="color: blue;"'; ?>>HW2 NoADS inklusive!
<li>12 Angebote pro Seite auf dem Marktplatz
<li>Nachrichtenarchiv für gesendete Nachrichten
<li>50 Nachrichten für 4 Wochen zugänglich
<li>Autologout erst nach 12 Stunden
<li>Avatar auch ohne Toplistenposition
<li>Adressbuch mit Verbündeten und Ordensbrüdern - auch für Marktplatz 
<li><font color="#FF2020"><b>NEU!!!</b></font> Adressbuch auch für SMS-Empfänger 
<li><font color="#FF2020"><b>NEU!!!</b></font> Diplomatie-Karte: Verbündete und Feinde im Überblick
<li>Signatur bearbeitbar
</ul>
30 Tage EUR 2,99 
<? if( defined("PREMIUM_SMS") && PREMIUM_SMS ) { ?>
oder <b>SMS* an 89998</b> mit Text &quot;<code><B><? 
echo SMS_PREMIUM_KEYWORD." "; 
if (isset($_SESSION['player'])) {
 echo $_SESSION['player']->getID(); 
} 
else {
 echo "&lt;ID&gt;";
}
?>
</B></code>&quot;
<? } ?>
<br />
<table><tr><td>
3 Monate EUR 6,00<br />
6 Monate EUR 11,00<br />
12 Monate EUR 20,00<br />
</td><td>
<? if(isset($_SESSION['player'])) paypal_button("6.00", "drei Monate Premium Lite"); ?>
</td></tr></table>
<? if( defined("PREMIUM_SMS") && PREMIUM_SMS ) { ?>
<p>
<font size="-1">*) EUR 2,99 pro SMS aus allen <b>deutschen</b> Netzen, zuzüglich eventueller Betreibergebühren.</font>
<? } ?>
<hr>
<a name="premiumpro"><h2>HW2 Premium Pro</h2></a>
<ul>
<li>HW2 Premium Lite inklusive!
<li>20 Angebote pro Seite auf dem Marktplatz
<li>200 Nachrichten für 4 Wochen zugänglich
<li>Nachrichtenarchiv kann per Email an den Benutzer verschickt werden
<li>Namensschutz jeweils bis 1 Jahr nach Ablauf des HW2 Premium Pro Accounts
<li>Emailadresse @ holy-wars2.de mit Weiterleitung
<li>Kein Autologout, <font color="#FF2020">Kein Security-Code</font>
<li><b>10 SMS</b> pro Monat kostenlos
<li><font color="#FF2020"><b>NEU!!!</b></font> Eigene Mobilfunknummer als Absenderkennung
bei SMS-Versand einstellbar, so dass Empfänger direkt aufs Handy antworten kann.
</ul>
<table><tr><td>
3 Monate EUR 10,00<br />
6 Monate EUR 19,00<br />
12 Monate EUR 35,00<br />
</td><td>
<? if(isset($_SESSION['player'])) paypal_button("10.00", "drei Monate Premium Pro"); ?>
</td></tr></table>
<?
/*
<hr><h2>HW2 Premium Ultra</h2>
<ul>
<li>HW2 Premium Pro inklusive!
<li>unbegrenzt (10000) Nachrichten für 8 Wochen zugänglich
<li>Namensschutz lifetime ab 1 Jahr Laufzeit
<li>Eigene Abteilung im HW2-Forum mit Subdomäne, z.B. <a href="http://wunschname-forum.holy-wars2.de/" target="_blank">http://wunschname-forum.holy-wars2.de/</a>
<li><b>20 SMS</b> pro Monat kostenlos
<li><font color="#FF2020"><b>NEU!!!</b></font> Eigene Mobilfunknummer als Absenderkennung 
bei SMS-Versand einstellbar, so dass Empfänger direkt aufs Handy antworten kann.
</ul>
12 Monate EUR 50,00<br />
24 Monate EUR 90,00<br />
*/ 
?>
<hr>

</td></table></body></html>


<?
function paypal_button($eur = "2.99", $desc="30 Tage Premium Lite")
{
?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
   <input type="hidden" name="cmd" value="_xclick">
   <input type="hidden" name="business" value="paypal@holy-wars2.de">
   <input type="hidden" name="item_name" value="<? echo  SMS_PREMIUM_KEYWORD." ".$_SESSION['player']->getID().", ".$desc; ?>">
   <? 
   if($eur != null) { 
     echo '<input type="hidden" name="amount" value="'.$eur.'">';
     echo '<input type="hidden" name="currency_code" value="EUR">';
   } 
  ?>
   <input type="hidden" name="no_shipping" value="2">
   <input type="hidden" name="no_note" value="1">
   <input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but01.gif" style="border: none"
          name="submit" alt="Bezahlen Sie mit PayPal - schnell, einfach und sicher!">
   <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<?
}
?>
