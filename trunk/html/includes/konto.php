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
 ?>
<h2>ACHTUNG: unsere Konto-Nr. hat sich geändert!</h2>
<table border="0" cellpadding="0" 
       style="font-size: 12px; font-family: monospace">
<tr><td width="130">Inhaber:</td><td width="300">Markus Sinner</td></tr>
<tr><td>KtoNr.:</td><td>4305365</td></tr>
<tr><td>BLZ:</td>   <td>55350010</td></tr>
<tr><td>Institut:</td><td> Sparkasse Worms-Alzey-Ried</td></tr>
<tr><td>Verwendungszweck:</td><td>
<? echo SMS_PREMIUM_KEYWORD." "; 
if (isset($_SESSION['player'])) {
 echo $_SESSION['player']->getID(); 
} 
else {
 echo "&lt;ID&gt;";
}
?>
<tr><td colspan="2">&nbsp;</td><td>
<tr><td>IBAN:</td><td>DE87 5535 0010 0004 3053 65</td></tr>
<tr><td>BIC (SWIFT-Code):</td><td>MALADE51WOR</td></tr>
<tr><td colspan="2"><i>Beim IBAN/BIC-Verfahren aus nicht-EU-Ländern bitte OUR als Zahlungstyp angeben!</i></td></tr>
</table>
<h2>Zahlung per Paypal</h2>
Holy-Wars 2 unterstützt <img src="http://pics.ebaystatic.com/aw/pics/paypal/logoPaypal.gif">.<br>
Um eine Zahlung vorzunehmen, überweisen Sie den Betrag an <img src="images/email3.png">.
Vergessen Sie auch dabei nicht, Ihre HW2 ID und den gewünschten Premium-Account mit anzugeben.<br>
