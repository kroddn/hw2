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
 * Stefan Neubert
 * Stefan Hasenstab
 * Laurenz Gamper
 * Gordon Meiser
 * Markus Sinner
 *
 * This File must not be used without permission!
 ***************************************************/

include_once("includes/config.inc.php");
include_once("includes/db.inc.php");
include_once("includes/market.class.php");
include_once("includes/session.inc.php");
include_once("includes/player.class.php");
include_once("includes/cities.class.php");
include_once("includes/banner.inc.php");

if(defined("HISPEED") && HISPEED) {
  start_page();
  start_body();
  echo "<h1>Marktplatz ist in der HiSpeed deaktiviert.</h1>";
  end_page();
  die();
}

$player->setActivePage(basename(__FILE__));

if ($aufgeben) {
  $market->put($wantsType, $wantsQuant, $hasType, $hasQuant);
  $help = $wantsType;
  $wantsType = $hasType;
  $hasType = $help;
}

if ($recipient) {
  $market->sendRes($type, $quant, $recipient);
}

if ($id) {
  $market->click($id);
}

if (isset($sendback)) {
  $market->sendBack($sendback);
 }

if (isset($del)) {
  $market->delOffer($del);
 }

if (isset($punish)) {
  $market->punishOffer($punish);
 }

if (isset($wantsType)) 
  $market->setWantsType($wantsType);

if (isset($hasType))  
  $market->setHasType($hasType);

if (isset($sort))
  $market->setSort($sort);

if (isset($prev)) {
  $market->show_prev();
 }

if (isset($next)) {
  $market->show_next();
 }


if (isset($all) || isset($view)) {
  unset($own);
}

if (isset($own))
     $market->setOwn(1);
else
     $market->setOwn(0);
     
// Seitenstart     
start_page();
start_body();


$error = $player->canTrade();
if ($error != null) {
  echo "<h1 class='error'>".$error."</h1>";
  end_page();
  die();
}
?>
<h1 style="margin:2px; ">Marktplatz in <? echo $_SESSION['cities']->getACName(); ?></h1>
<form name="marketform" action="marketplace.php" method="POST">
<table cellspacing="1" width="520" cellpadding="0" border="0">
<tr>
	<td class="tblhead" colspan="4"><strong>Ressourcen senden</strong></td>
</tr>

<tr>
<td nowrap class="tblbody">Empf�nger eingeben: <input type='text' name='recipient' <?if (isset($sendto)) echo 'value="'.$sendto.'" '; ?> size='17'>
<?
echo "&nbsp;<select onChange=\"if(this.value != '') document.marketform.recipient.value=this.value\" name=\"addressbook\" size=\"1\" style=\"width:100px;\">";
echo "<option value=\"\">Empf�nger w�hlen</option>\n";

if (is_premium_adressbook()) {
  echo "<option value=\"\">  -- Adressbuch --</option>\n";
  $adress = $player->getAdressbookPlayers();
  foreach ($adress as $i=>$adr) {
    echo '<option value="'.$adr['name'].'">'.$adr['nicename']."</option>\n";     
  }    

  echo "<option value=\"\">  -- Alliierte --</option>\n";
  $allies = $player->getAlliedPlayers();
  foreach ($allies as $i=>$ally) {
    echo '<option value="'.$ally[1].'">'.$ally[1].($ally[2] ? " (O)" : " (A)")."</option>\n";
  }
}
else {
  echo "<option value=\"\">Adressbuch nur</option>\n";  
  echo "<option value=\"\">f�r Besitzer eines</option>\n";  
  echo "<option value=\"\">Premium-Accounts.</option>\n";  
}
?>
</td>
		<td nowrap class="tblbody">Menge: <input type='text' name='quant' size='6'></td>
		<td class="tblbody">
		<select name="type">
			<option value='gold'>Gold</option>
			<option value='wood'>Holz</option>
			<option value='iron'>Eisen</option>
			<option value='stone'>Stein</option>
		</select>
		</td>
		<td class="tblbody"><input type='submit' name='send' value=' Senden '></td>
	</tr>
</table>
<div class="little_space"></div>
<table width="520" cellspacing="1" cellpadding="0" border="0">
	<tr>
		<td class="tblhead" colspan="8"><strong>Angebote anzeigen</strong></td>
	</tr>
	<tr>
		<td class="tblbody" colspan="2">
Sucht:&nbsp;
		<select name="hasType">
			<option  <?php if ($market->getHasType() == "gold") {echo "selected";} ?>  value='gold'>Gold</option>
			<option <?php if ($market->getHasType() == "wood") {echo "selected";} ?>   value='wood'>Holz</option>
			<option <?php if ($market->getHasType() == "iron") {echo "selected";} ?>   value='iron'>Eisen</option>
			<option <?php if ($market->getHasType() == "stone") {echo "selected";} ?>   value='stone'>Stein</option>
			<option <?php if ($market->getHasType() == "shortrange") {echo "selected";} ?>   value='shortrange'>Nahkampfwaffen</option>
			<option <?php if ($market->getHasType() == "longrange") {echo "selected";} ?>   value='longrange'>Fernkampfwaffen</option>
			<option <?php if ($market->getHasType() == "armor") {echo "selected";} ?>   value='armor'>R�stungen</option>
			<option <?php if ($market->getHasType() == "horse") {echo "selected";} ?>   value='horse'>Pferde</option>
			<option  <?php if ($market->getHasType() == "all") {echo "selected";} ?>   value='all'>*</option>
		</select>
		</td>
		<td class="tblbody" colspan="2">
Bietet:&nbsp;
		<select name="wantsType">
			<option <?php if ($market->getWantsType() == "gold") {echo "selected";} ?>  value='gold'>Gold</option>
			<option <?php if ($market->getWantsType() == "wood") {echo "selected";} ?>   value='wood'>Holz</option>
			<option <?php if ($market->getWantsType() == "iron") {echo "selected";} ?>   value='iron'>Eisen</option>
			<option <?php if ($market->getWantsType() == "stone") {echo "selected";} ?>   value='stone'>Stein</option>
			<option <?php if ($market->getWantsType() == "shortrange") {echo "selected";} ?>   value='shortrange'>Nahkampfwaffen</option>
			<option <?php if ($market->getWantsType() == "longrange") {echo "selected";} ?>   value='longrange'>Fernkampfwaffen</option>
			<option <?php if ($market->getWantsType() == "armor") {echo "selected";} ?>   value='armor'>R�stungen</option>
			<option <?php if ($market->getWantsType() == "horse") {echo "selected";} ?>   value='horse'>Pferde</option>
			<option <?php if ($market->getWantsType() == "all") {echo "selected";} ?>   value='all'>*</option>
		</select>
		</td>
		<td class="tblbody" style="text-align:center;"><input type='submit' name='view' value=' Los '></td>

<?php
  if ($player->isMarketmod())
    echo '<td class="tblbody" colspan="4">';
  else
    echo '<td class="tblbody" colspan="2">';


if (isset($own)) {
   echo "<input type='submit' name='all' value=' Alle Angebote '></td></tr>\n<tr>";
   echo "<input type='hidden' name='own' value='1'></td></tr>\n<tr>";
   if ($player->isMarketmod())
     echo '<td class="tblhead" colspan="6" align="center">';
   else
     echo '<td class="tblhead" colspan="4" align="center">';

   echo "<b class=\"error\">Ihr seht Eure eigenen Angebote</b>\n";
}
else {
  echo "<input type='submit' name='own' value=' Eigene Angebote '>\n";
}
?>
</td>
</tr>
</table>
<div class="little_space"></div>
<table width="520" cellpadding="0" cellspacing="1">
<tr>
  <td colspan="1" class="tblhead" width="100" style="text-align:center;"><b>Spieler</b></td>
  <td colspan="2" class="tblhead" width="150" style="text-align:center;"><b>sucht</b></td>
  <td colspan="2" class="tblhead" width="150" style="text-align:center;"><b>bietet</b></td>
  <td colspan="1" class="tblhead" width="50" style="text-align:center;"><b>Verh&auml;ltnis</b></td>
  <td colspan="1" class="tblhead" width="50" style="text-align:center;">&nbsp;</td>
<?php
  if ($player->isMarketmod())
    echo "<td colspan='3' class='tblhead'><b>Marktmoderation</b></td>";
	echo "</tr>";

if ($market->getOwn() == 1) {
  $res1 = do_mysql_query("SELECT wantsType,wantsQuant,hasType,hasQuant,market.id AS id,ratio,player,city.name AS cname".
			 " FROM market LEFT JOIN player ON market.player=player.id LEFT JOIN city ON city.id=market.city".
			 " WHERE player=".$player->getID()." LIMIT ".$market->getStart().",".$market->getNum());
} 
else {
  $qry="SELECT wantsType,wantsQuant,hasType,hasQuant,market.id AS id,ratio,player,player.name,city.name AS cname".
    " FROM market LEFT JOIN player ON market.player=player.id LEFT JOIN city ON city.id=market.city ";
  if ($market->getHasType() != "all") {
    $qry .= "WHERE wantsType='".$market->getHasType()."'";
    if ($market->getWantsType() != "all") {
      $qry .= " AND hasType='".$market->getWantsType()."'";
    }
  } else if ($market->getWantsType() != "all") {
    $qry .= "WHERE hasType='".$market->getWantsType()."'";
  }
  $qry .= " ORDER BY ratio ".$market->getSort()." LIMIT ".$market->getStart().",".$market->getNum();
  $res1 = do_mysql_query($qry);
 }

while ($data1=mysql_fetch_array($res1)) {
  echo "\n<tr";
  if($data1['player']==$player->getID() && $data1['cname']) echo ' title="nach '.$data1['cname'].'"';
  echo ">\n";
  echo "  <td nowrap class=\"tblbody\"><a ".($data1['player']==$player->getID() ? 'style="color:blue;" ' : "")."href=\"info.php?show=player&name=".$data1['name']."\">".$data1['name']."</a></td>";
  echo "  <td nowrap class=\"tblbody\" style=\"text-align:right;\">".$data1['wantsQuant']."</td>";
  echo "  <td nowrap class=\"tblbody\">".$market->gerres[$data1['wantsType']]."</td>";
  echo "  <td nowrap class=\"tblbody\" style=\"text-align:right;\">".$data1['hasQuant']."</td>";
  echo "  <td nowrap class=\"tblbody\">".$market->gerres[$data1['hasType']]."</td>";  

  echo "  <td class=\"tblbody\" style=\"text-align:center;\"><table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"\n";
  echo "<tr><td width=\"23\" style=\"padding:0px;text-align:right;\">1</td><td style=\"padding:0px;\" width=\"4\">:</td><td style=\"padding:0px;\" width=\"23\">".round(($data1['hasQuant']/$data1['wantsQuant']),2)."</td></tr>";
  echo "</table></td>\n";
  echo "  <td class=\"tblbody\"><a href='marketplace.php?id=".$data1['id'].(isset($own) ? "&own=1' " : "' ").
    ($data1['player']==$player->getID() && $data1['cname'] ? 'title="nach '.$data1['cname'].'"' : "").">";

  if ($data1['player'] == $player->getID()) {
    echo "Zur&uuml;cknehmen";
  } 
  else {  
    echo "Annehmen";
  }

  echo "</a></td>";
  if ($player->isMarketmod()) {
    echo "  <td class=\"tblbody\"><a href='marketplace.php?sendback=".$data1['id']."'>Zur�ckschicken</a></td>";
    echo "  <td class=\"tblbody\"><a href='marketplace.php?del=".$data1['id']."'>L�schen</a></td>";
    echo "  <td class=\"tblbody\"><a onClick=\"return confirm('Wirklich bestrafen?')\" href='marketplace.php?punish=".$data1['id']."'>Bestrafen</a></td>";
  }
  echo "</tr>";
 }

//echo "</table><br>";
//echo "<table width=\"520\"><tr>";

echo '<tr><td align="center" colspan="'.($player->isMarketmod() ? "10" : "7" ).'" class="tblbody">Es werden '.$market->getNum().' Angebote pro Seite angezeigt. <a href="premium.php">Mehr?</a></td></tr>';

echo "<tr>";

if ($player->isMarketmod()) {
  echo "<td colspan=\"6\" class=\"tblbody\" align=\"right\"><input accesskey=\",\"  type=\"submit\" name=\"prev\" value=\"<< (ALT+,)\"";
}
else {
  echo "<td colspan=\"3\" class=\"tblbody\" align=\"right\"><input accesskey=\",\" type=\"submit\" name=\"prev\" value=\"<< (ALT+,)\"";
}

if ($market->getStart() == 0) {echo " disabled ";}
echo "></td><td colspan=\"3\" class=\"tblbody\"></td>";
echo "<td class=\"tblbody\" align=\"left\"><input accesskey=\".\" type=\"submit\" name=\"next\" value=\"(ALT+.) >>\"";
if (mysql_num_rows($res1) < $market->getNum()) {echo " disabled ";}
echo "></td></tr>";
?>
<input type="hidden" name="sort" value="<?php echo $sort; ?>">
</form>
</table>

<div class="little_space"></div>
<table width="520" cellspacing="1" cellpadding="0" border="0">
<form action="<? echo $PHP_SELF; ?>" method="POST">
	<tr>
		<td class="tblhead" colspan="4"><strong>Angebot aufgeben</strong></td>
        </tr>
	<tr>
		<td class="tblbody">Gesucht</td>
		<td class="tblbody">
		<select   name="wantsType">
			<option  <?php if ($market->getHasType() == "gold") {echo "selected";} ?>  value='gold'>Gold</option>
			<option <?php if ($market->getHasType() == "wood") {echo "selected";} ?>   value='wood'>Holz</option>
			<option <?php if ($market->getHasType() == "iron") {echo "selected";} ?>   value='iron'>Eisen</option>
			<option <?php if ($market->getHasType() == "stone") {echo "selected";} ?>   value='stone'>Stein</option>
			<option <?php if ($market->getHasType() == "shortrange") {echo "selected";} ?>   value='shortrange'>Nahkampfwaffen</option>
			<option <?php if ($market->getHasType() == "longrange") {echo "selected";} ?>   value='longrange'>Fernkampfwaffen</option>
			<option <?php if ($market->getHasType() == "armor") {echo "selected";} ?>   value='armor'>R�stungen</option>
			<option <?php if ($market->getHasType() == "horse") {echo "selected";} ?>   value='horse'>Pferde</option>
		</select>
		</td>
		<td class="tblbody">Menge: <input type='text' name='wantsQuant' size='8'></td>
		<td class="tblbody" style="vertical-align:middle; text-align:center;" rowspan="2">
            <input style="width: 100px; " type='submit' name='aufgeben' value=' Aufgeben '><br>
<?
            if ($aufgeben) {
              echo '<input accesskey="n" style="margin-top: 4px; width: 100px; " type="button" value=" Nochmal (Alt+N)" onClick="'.
              ($GLOBALS['premium_flags'] >= PREMIUM_LITE ? "window.location.reload()" : "alert('Nur f�r Premium-Account')").'">';
            }            
?>
        </td>
		
	</tr>
	<tr>
		<td class="tblbody">Geboten</td>
		<td class="tblbody">
		<select   name="hasType">
			<option <?php if ($market->getWantsType() == "gold") {echo "selected";} ?>  value='gold'>Gold</option>
			<option <?php if ($market->getWantsType() == "wood") {echo "selected";} ?>   value='wood'>Holz</option>
			<option <?php if ($market->getWantsType() == "iron") {echo "selected";} ?>   value='iron'>Eisen</option>
			<option <?php if ($market->getWantsType() == "stone") {echo "selected";} ?>   value='stone'>Stein</option>
			<option <?php if ($market->getWantsType() == "shortrange") {echo "selected";} ?>   value='shortrange'>Nahkampfwaffen</option>
			<option <?php if ($market->getWantsType() == "longrange") {echo "selected";} ?>   value='longrange'>Fernkampfwaffen</option>
			<option <?php if ($market->getWantsType() == "armor") {echo "selected";} ?>   value='armor'>R�stungen</option>
			<option <?php if ($market->getWantsType() == "horse") {echo "selected";} ?>   value='horse'>Pferde</option>
		</select>
		</td>
		<td class="tblbody">Menge: <input type='text' name='hasQuant' size='8'></td>
	</tr>

</table>
<div class="little_space"></div>
<table width="520" cellspacing="1" cellpadding="0" border="0">
 <tr>
  <td align="center" class="tblhead"><strong>Unsinnige Angebote oder �berflutung des Marktes werden bestraft!.</strong><p>
<? if (!is_premium_noads()) { ?>
<hr>
<!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
<A style="color: blue;" HREF="http://partners.webmasterplan.com/click.asp?ref=249139&site=3175&type=text&tnb=1" TARGET="_new">Auktionsideen.de<br></a>Verdienen Sie Geld mit Auktionen! Die besten Tipps f&uuml;r Ebay & Co. finden Sie auf Auktionsideen.de<br><A HREF="http://partners.webmasterplan.com/click.asp?ref=249139&site=3175&type=text&tnb=1" TARGET="_new" style="color: blue;">Jeden Monat neu!<br></a><IMG SRC="http://banners.webmasterplan.com/view.asp?site=3175&ref=249139&b=0&type=text&tnb=1" BORDER="0" WIDTH="1" HEIGHT="1">
<!-- END PARTNER PROGRAM -->
<hr>
<p>
<? } ?>
</td>
 <tr>
</table>
</form>
</div>

<? end_page(); ?>