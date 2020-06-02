<?php
include_once("includes/db.inc.php");
include_once("includes/util.inc.php");
include_once("includes/config.inc.php");
include_once("includes/ressources.inc.php");

define("TICK", getConfig("tick"));

function prettyNum($number, $komma=2) {
  $num = str_replace(",", ".", $number);
  if($num > 0) {
    return "<span style=\"color:green;\">+".$num."</span>";
  } else {
    if ($num == 0) {
      return "<span style=\"color:black;\">".$num."</span>";
    } else {
      return "<span style=\"color:red;\">".$num."</span>";
    }
  }
}


function getTimer($id) {
  static $timerid=1;
  $tick = TICK;
  $last_res=do_mysql_query("SELECT lastres FROM player WHERE id = ".$id);
  if ($last=do_mysql_fetch_assoc($last_res)) {
    $next = ($last['lastres']+$tick)-time();
    return "<b>Verbleibende Zeit bis zum n&auml;chsten Tick:</b>&nbsp;<span class=\"noerror\" id=\"1\"><script type=\"text/javascript\">addTimer(".$next.",".$timerid.");</script></span>&nbsp;";
    $timerid++;
  }
}

function stat_gold($id) {
  global $imagepath;
  if($_GET['order'] == "city") { $orderby = "ORDER BY city.name ASC"; }
  elseif($_GET['order'] == "tax") { $orderby = "ORDER BY pop DESC"; }
  elseif($_GET['order'] == "building") { $orderby = "ORDER BY incgold DESC"; }
  elseif($_GET['order'] == "troups") { $orderby = "ORDER BY incfood DESC"; }
  elseif($_GET['order'] == "arm") { $orderby = "ORDER BY food DESC"; }
  elseif($_GET['order'] == "sum") { $orderby = "ORDER BY foodstorage DESC"; }
  else { $orderby = "ORDER BY city.capital DESC, city.id ASC"; }

  $id = intval($id);
  $res1=do_mysql_query("SELECT
			sum(citybuilding.count * building.res_horse) AS inchorse,
			sum(citybuilding.count * building.res_gold) AS incgold,
			sum(citybuilding.count * building.res_storage) AS storage,
			sum(citybuilding.count * building.res_attraction) AS attr,
			city.id AS id,
            city.name AS name,
            city.capital AS capital,
			city.population AS pop,
			city.prosperity,
			city.horse AS horse,
			city.max_horse AS max_horse,
			city.reserve_horse AS reserve_horse
			FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.owner = ".$id." GROUP BY id ".$orderby);

  $admlvl = get_adm_level($id);
  $citycount = mysqli_num_rows($res1);
  $res_eff = get_eff(floor($citycount/2), $admlvl);
  $eff = get_eff($citycount, $admlvl)*$res_eff;

  echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"600\">";
  echo "<tr><td class=\"tblhead\"><b>Gold</b></td>";
  echo "<td class=\"tblbody\" colspan=\"5\" style=\"text-align:right;\">".getTimer($id)."</td></tr>";
  if ($eff < 1) {
    echo "<tr class='tblhead'><td colspan='3'><b>Verwaltung (".round($eff*100)."%)</b></td><td colspan='3'><b>Gebäudeverwaltung (".round($res_eff*100)."%)</b></td></tr>";
  }
  echo "<tr class=\"tblhead\" style=\"font-weight:bold; text-align:center;\">";
  echo "<td style=\"text-align:left;\"><a href=\"kingdom.php?show=gold&order=city\">Stadt</a></td>";
  echo "<td><a href=\"kingdom.php?show=gold&order=tax\">Steuer</a></td>";
  echo "<td><a href=\"kingdom.php?show=gold&order=building\">Geb&auml;ude</a></td>";
  echo "<td>Truppen</td>";
  echo "<td><img src=\"".$imagepath."/horse.gif\"></td>";
  echo "<td>Summe</td>";
  echo "</tr>";

  $res4= do_mysql_query("SELECT gold,clan FROM player WHERE id=".$id);
  $data4 = do_mysql_fetch_assoc($res4);
  $gold = $data4['gold'];
  $tottax = 0;
  $totincgold = 0;
  $totctyucost = 0;
  $tothorsegold = 0;
  $totgold = 0;

  $ucost = get_ucost($id);

  while ($data1 = do_mysql_fetch_assoc($res1)) {
    $horse = get_inc_wep($gold-$ucost, $data1['inchorse'], HORSE_COST,  $data1['storage'], $data1['reserve_horse'], $data1['horse'], HORSE_PRODFACTOR*$res_eff, $data1['max_horse']);

    $attr = $data1['attr'] + 1000;

    if ($data1['prosperity'] >= $attr) {
      $tax = floor($data1['pop']/10)*GOLD_PRODFACTOR*$eff;
      $prosp_low = false;
    }
    else {
      $tax = 0;
      $prosp_low = true;
      if (!isset($prosp_text))
        $prosp_text= "*) Keine Steuern: Wohlstand in dieser Stadt zu niedrig.";
    }

    $bldgold = floor(($data1['incgold'] > 0) ? ($data1['incgold']*$res_eff) : $data1['incgold']);
    $cityucost = get_city_ucost($data1['id'], $id);
    //normalerweise interessiert der Verbrauch nicht, daher hier die umständliche Methode
    $horsecost = ($gold-$horse['raw'])-$ucost;
    $cost = $tax + $bldgold - $cityucost - $horsecost;

    //Truppenkosten sind in $gold schon drin
    $gold += $cost + $cityucost;

    $tottax += $tax;
    $totincgold += $bldgold;
    $totctyucost += $cityucost;
    $tothorsecost += $horsecost;
    $totcost += $cost;

    echo '<tr class="tblbody">';
    echo '<td nowrap class="cityname">'.get_city_htmllink_koords($data1)."</td>\n";
    echo "<td style=\"text-align:right; padding-right:6px;\">".
      prettyNum(number_format($tax,0,",",".")).
      ($prosp_low ? " *" : "").
      "</td>\n";

    echo "<td style=\"text-align:right; padding-right:6px;\">".prettyNum(number_format($bldgold,0,",","."))."</td>\n";
    echo "<td style=\"text-align:right; padding-right:6px;\">".prettyNum(number_format(-$cityucost,2,",","."))."</td>\n";
    echo "<td style=\"text-align:right; padding-right:6px;\">".prettyNum(number_format(-$horsecost,0,",","."))."</td>\n";
    echo "<td align=\"right\">".prettyNum(number_format($cost,2,",","."),2)."</td>\n";
    echo "</tr>\n";
  }

  echo "<tr class='tblhead'><td>Summe</td>";
  echo "<td style=\"text-align:right; padding-right:6px;\"><b>".prettyNum(number_format($tottax,0,",","."))."</b></td>";
  echo "<td style=\"text-align:right; padding-right:6px;\"><b>".prettyNum(number_format($totincgold,0,",","."))."</b></td>";
  echo "<td style=\"text-align:right; padding-right:6px;\"><b>".prettyNum(number_format(-$totctyucost,2,",","."))."</b></td>";
  echo "<td style=\"text-align:right; padding-right:6px;\"><b>".prettyNum(number_format(-$tothorsecost,0,",","."))."</b></td>";
  echo "<td align=\"right\"><b>".prettyNum(number_format($totcost,2,",","."))."</b></td>";
  //echo "<td>&nbsp;</td>";
  echo "</tr>";

  if (isset($prosp_text)) {
    echo '<tr class="tblhead"><td colspan="6">'.$prosp_text."</td></tr>";

  }
  
  //Armeeunterhalt = laufende Truppen ohne Belagerung
  $ucost = get_army_cost($id);
  echo "<tr>";
  echo "<td colspan='2' style='border: 0;'></td>";
  echo "<td class='tblbody' colspan='3'>- Armee auf Mission</td>";
  echo "<td class='tblbody' align=\"right\"><strong style=\"color:red;\">-".number_format($ucost,2,",",".")."</strong></td>";
  echo "</tr>";
  $totcost -= $ucost;

  // Nur informativ über die gesparten Kosten für Belagernde Armeen  informieren
  if( defined("SIEGE_ARMIES_NO_COST") && SIEGE_ARMIES_NO_COST) {
    // Belagernde Truppen
    $ucost_siege = get_army_cost_siege($id);
    echo "<tr>";
    echo "<td colspan='2' style='border: 0;'></td>";
    echo "<td class='tblbody' colspan='3'>kostenfreie belagernde Armeen</td>";
    echo "<td class='tblbody' align=\"right\"><strong style=\"color:#DDDDDD;\">".number_format($ucost_siege,2,",",".")."</strong></td>";
    echo "</tr>";
    //$totcost -= $ucost_siege;
  } 	     

  // Ordenssteuer
  $tax_factor = 1; // Wird von get_clan_tax überschrieben
  //  $tax_base_sum = $tottax+$totincgold-$tothorsegold;
  $tax_base_sum = $tottax; // Change 14.11.2008 - Ordensteuern nur noch aus Steuergeldern
  $clantax = get_clan_tax($data4['clan'],  $tax_base_sum, $tax_factor);
  echo "<tr>";
  echo "<td colspan='2' style='border: 0;'></td>";
  echo "<td class='tblbody' colspan='3'>- Ordenssteuer ".$tax_factor."% aus ".number_format($tax_base_sum,0,",",".")."</td>";
  echo "<td class='tblbody' align=\"right\"><strong style=\"color:red;\">-".number_format($clantax,2,",",".")."</strong></td>";
  echo "</tr>";
  $totcost -= $clantax;


  //Armeeunterhalt = in fremden Städten stationierte Truppen
  $scost = get_external_cucost($id);
  echo "<tr>";
  echo "<td colspan='2' style='border: 0;'></td>";
  echo "<td class='tblbody' colspan='3'>- Truppen in fremden St&auml;dten</td>";
  echo "<td class='tblbody' align=\"right\"><strong style=\"color:red;\">-".number_format($scost,2,",",".")."</strong></td>";
  echo "</tr>";
  $totcost -= $scost;

  echo "<tr>";
  echo "<td colspan='2' style='border: 0;'></td>";
  echo "<td class='tblhead' colspan='3'><b>Total</b></td>";
  echo "<td class='tblhead' align=\"right\"><b>".prettyNum(number_format($totcost,0,",","."))."</b></td>";
  echo "</tr>";
  echo "</table>";
}



function stat_cities($id) {
  if($_GET['order'] == "city")      { $orderby = "ORDER BY city.name ASC"; }
  elseif($_GET['order'] == "ew")    { $orderby = "ORDER BY city.population DESC"; }
  elseif($_GET['order'] == "attr")  { $orderby = "ORDER BY attr DESC"; }
  elseif($_GET['order'] == "pros")  { $orderby = "ORDER BY prosperity DESC"; }
  elseif($_GET['order'] == "food1") { $orderby = "ORDER BY incfood DESC"; }
  elseif($_GET['order'] == "food2") { $orderby = "ORDER BY food DESC"; }
  elseif($_GET['order'] == "food3") { $orderby = "ORDER BY foodstorage DESC"; }
  elseif($_GET['order'] == "fp")    { $orderby = "ORDER BY fpew DESC"; }
  elseif($_GET['order'] == "loy")   { $orderby = "ORDER BY loyality DESC"; }
  else { $orderby = "ORDER BY city.capital DESC, city.id ASC"; }

  $id = intval($id);
            
  $res1=do_mysql_query("SELECT
			sum(citybuilding.count * building.res_rp) AS research,
			sum(citybuilding.count * building.res_foodstorage) AS foodstorage,
			sum(citybuilding.count * building.res_food) AS incfood,
			sum(citybuilding.count * building.res_attraction) AS attr,
            sum(citybuilding.count * building.res_rp)*city.population AS fpew,
            city.religion,
			city.id,
            city.name,
            city.capital,
			city.population AS pop,
            city.prosperity,
			round(city.loyality/100) AS loy,
            round(city.prev_loyality/100) AS prev_loy,
			city.food,
			city.populationlimit AS poplimit
			FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city
            LEFT JOIN building ON building.id = citybuilding.building
            WHERE city.owner = ".$id." GROUP BY id ".$orderby);

  $admlvl = get_adm_level($id);
  $citycount = mysqli_num_rows($res1);
  $res_eff = get_eff(floor($citycount/2), $admlvl);
  $eff = get_eff($citycount, $admlvl)*$res_eff;
?>
<table cellspacing="0" cellpadding="0">
<?
  echo "<tr>";
  echo "<td class=\"tblhead\"><b>Stadtinfo</b></td>";
  echo "<td class=\"tblbody\" colspan=\"9\" style=\"text-align:right;\">".getTimer($id)."</td>";
  echo "</tr>";
  echo '<tr class="tblhead" align="center">';
  echo '<td align="left"><a href="kingdom.php?order=city">Stadt</a></td>';
  echo "<td colspan=\"2\"><a href=\"kingdom.php?order=ew\">Einwohner<br>Bürger</a> / ";
  echo "<b>Siedler</b></td>";
  if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY)
    echo "<td><a href=\"kingdom.php?order=loy\">LOY</a></td>\n";
    
  echo "<td><a href=\"kingdom.php?order=attr\">ATTR</a></td>";
  echo "<td><a href=\"kingdom.php?order=pros\">WS</a></td>";
  echo "<td colspan=\"3\"><a href=\"kingdom.php?order=food1\">Nahrung<br>Prod.</a> / ";
  echo "<a href=\"kingdom.php?order=food2\">Lager</a>".
    " / <a href=\"kingdom.php?order=food3\">maximal</a></td>";
  echo "<td><a href=\"kingdom.php?order=fp\">FP</a></td>";
  echo "</tr>";

  // Gesamtsumme der Spalten (für Legende und Leerzeilen)
  if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY)
    $totcols = 10;
  else
    $totcols = 9;

  $totpop = 0;
  $totsettler = 0;
  $totloy = 0;
  $totattr = 0;
  $totprosp= 0;
  $totres = 0;
  $totdiffpop = 0;
  $totdiffset = 0;
  $totincfood = 0;
  $totfood = 0;
  $totfoodstorage = 0;

  while ($data1 = do_mysql_fetch_assoc($res1)) {
    $city_siege = getSiegeTime($data1['id']);
    $siege_factor = getSiegeFactor($city_siege);

    if ($siege_factor < 1) {
      $siege = '!!! Achtung: eine oder mehrere Städte befinden sich unter Belagerung. <font color="red">Nach einer 24 Stunden Belagerung wird keine Nahrung mehr produziert!</font>';
      $data1['incfood'] = round($data1['incfood'] * $siege_factor);
    }

    $data1['attr'] +=1000;

		// Siedler seperat anzeigen
  	$settler_data = do_mysql_query_fetch_array("SELECT sum(missiondata) as settler_sum FROM army WHERE start = ".$data1['id']." AND owner=".$id."");
  	if ($settler_data['settler_sum']==NULL)
		{
			$settler_data['settler_sum']=0;
		}

    $newpop = get_new_pop($data1['incfood']+$data1['food'], $data1['attr'], $data1['pop']+$settler_data['settler_sum'], $data1['poplimit']);
    if ($data1['poplimit']>0) {
      // Hinweis ausgeben, dass ein Limit gesetzt wurde
      $inform = "Bei den mit <b>*</b> markierten Städten haben Sie ein Bevölkerungslimit gesetzt! Dies können Sie im Rathaus der Stadt einsehen.";
    }
			
    $research = round(get_city_research($eff, $newpop-$settler_data['settler_sum'], RESEARCHEW, $data1['research']) * $siege_factor);
    $diffpop = $newpop-($data1['pop']+$settler_data['settler_sum']);

    $totloy  += $data1['loy'];
    $totattr += $data1['attr'];
    $totpop  += $data1['pop'];
    $totsettler += $settler_data['settler_sum'];
    $totprosp   += $data1['prosperity'];
    $totincfood += $data1['incfood'];
    $totfood    += $data1['food'];
    $totfoodstorage += $data1['foodstorage'];

    $diffstrpop = "";
    $diffstrset = "";

    if ($diffpop != 0) {
      if(($newpop-$settler_data['settler_sum'])+$diffpop>=1) {
        $totdiffpop += $diffpop;
	$diffstrpop = prettyNum($diffpop);
	//$diffstrset = "&nbsp;";
      }
      else if(($newpop-$settler_data['settler_sum'])+$diffpop<1) {
	if($data1['pop']==1) {
	  $totdiffset += $diffpop;
	  //$diffstrpop = "&nbsp;";
	  $diffstrset = prettyNum($diffpop);
	}
	else {
	  $totdiffpop += $diffpop-($newpop-$settler_data['settler_sum'])+1;
	  $totdiffset += $diffpop-($diffpop-($newpop-$settler_data['settler_sum'])+1);
	  $diffstrpop = prettyNum($diffpop-($newpop-$settler_data['settler_sum'])+1);
	  $diffstrset = prettyNum($diffpop-($diffpop-($newpop-$settler_data['settler_sum'])+1));
	}
      }
    } 

    $orangelimit = defined("SPEED")&&SPEED ? 150 : 50;
    if ($data1['incfood'] - ($data1['pop'] + $settler_data['settler_sum']) < 0) 
      $popcolorpop='font-weight: bold; color: red;';
    else if ($data1['incfood'] - ($data1['pop'] + $settler_data['settler_sum']) < $orangelimit)
      $popcolorpop='color: #FF8000;';
    else 
      $popcolorpop='';
    
    $popcolorset='';

    if ($data1['prosperity'] < $data1['attr']) {
      $prospcolor= 'style="font-weight: bold; color: red;"';    
      $researchtext = "0*";
    }
    else {
      $prospcolor= 'style="color: green;"';
      $researchtext = $research;
      $totres += $research;
    }

    if($data1['food'] == 0) {
      $foodstoragecolor = 'style="font-weight: bold; color: red;"';
    }
    else if($data1['foodstorage'] > $data1['food']) 
      $foodstoragecolor = "";
    else
      $foodstoragecolor = 'style="color: orange;"';
    
    if ($data1['prosperity'] <= 2 * $data1['attr']) 
      $prosptext    = prettyNumber($data1['prosperity']);    
    else
      $prosptext    = get_prosperity_attrib($data1['pop'], $data1['prosperity']);

      
      /**********************************/
      /** Eigentliche Tabelle erzeugen **/
    echo '<tr class="tblbody" align="right" valign="top">';
    
    printf('<td nowrap class="cityname">%s%s'."</td>", get_city_htmllink_koords($data1), $data1['poplimit']>0 ? " *": "" );    
    echo '<td style="border-left:0px; padding-left:0px;'.$popcolorpop.'">'.
      prettyNumber(intval($data1['pop'])).' '.$diffstrpop.'</td>';
    echo '<td width="35" style="border-left:0px; padding-left:0px;'.$popcolorset.'">'.$settler_data['settler_sum'].' '.$diffstrset.'</td>';
    //echo '<td style="margin-left:0px; padding-left:0px;">'.$diffstr."</td>";
    if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
      if($data1['prev_loy'] > $data1['loy'] ) {
        printf('<td style="font-weight: bold; color: red;" title="Die Einwohner schwören noch dem Vorbesitzer die Treue (%d %% Loyalität)">', $data1['prev_loy'] );
      }
      else {
        echo "<td>";
      }
      echo prettyNumber($data1['loy'])." %</td>";
      
    }
    echo "<td>".prettyNumber($data1['attr'])."</td>";
    echo "<td $prospcolor>".$prosptext."</td>";
    echo "<td>".prettyNumber($data1['incfood']).($city_siege > 0 ? " !!!" : "")."</td>";
    echo "<td>".prettyNumber($data1['food'])."</td>";
    echo "<td $foodstoragecolor>".prettyNumber($data1['foodstorage'])."</td>";
    echo "<td $prospcolor>".$researchtext."</td>";
    echo "</tr>\n";
  }

  if ($totdiffpop != 0) {
    $totdiffpop = prettyNum($totdiffpop);
  } else {
    $totdiffpop = "";
  }
  if ($totdiffset != 0) {
    $totdiffset = prettyNum($totdiffset);
  } else {
    $totdiffset = "";
  }



  echo '<tr align="right" class="tblhead">';
  echo '<td align="left"><b>Total / Tick</b></td>';
  echo '<td style="border-left:0px;padding-left:1px;">'.prettyNumber($totpop).' '.$totdiffpop.'</font></td>';
  //echo '<td style="padding-left:0px;">'.$diffstr."</td>";
  echo "<td>".$totsettler." ".$totdiffset."</td>";
  if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY)
    echo "<td>".prettyNumber( round($totloy/$citycount))." %</td>";
    
  echo "<td>".prettyNumber($totattr)."</td>";
  echo "<td>".get_prosperity_attrib($totpop, $totprosp)."</td>";
  echo "<td>".prettyNumber($totincfood)."</td>";
  echo "<td>".prettyNumber($totfood)."</td>";
  echo "<td>".prettyNumber($totfoodstorage)."</td>";
  echo "<td>".prettyNumber($totres)."</td>";
  echo "</tr>";
  if ($eff < 1)
    echo "<tr class=\"tblhead\"><td colspan=\"$totcols\"><b>Sie haben lediglich eine Effektivität von ".round($eff*100)."%</b></td></tr>";
  if (isset($inform) || isset($siege)) {
    echo "<tr class=\"tblbody\"><td colspan=\"$totcols\">$inform<br>$siege</td></tr>";
  }
  echo "<tr class=\"tblhead\" height=\"5\"><td colspan=\"$totcols\"></td></tr>";
?>
<tr class="tblbody"><td colspan="<?echo $totcols;?>">
<h3>Legende:</h3>
<b>Tick:</b> Abrechnungsintervall (alle <? echo get_tick_interval(); ?> )<br>
<b>EW:</b> Einwohner<br>
<b>FP:</b> Forschungspunkte<br>
<b>ATTR:</b> Attraktivität<br>
<? 
if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY) {
  $loy_growth = defined("LOYALITY_GROWTH") ? LOYALITY_GROWTH/100 : 1.0;
  echo "<b>LOY:</b> Loyalität. Steigt um ca. ".$loy_growth."% pro Tick <br>\n";
}
?>
<b>WS:</b> Wohlstand. Wenn höher als ATTR (grün), zahlen die Einwohner Steuern und erwirtschaften Forschungspunkte.<br>
<font color="#FF8000">EW orange</font> bedeuted,
dass die Nahrung bald knapp werden k&ouml;nnte (unter <? echo $orangelimit; ?> Einwohner Nahrungs&uuml;berschuss)

</td></tr>
</table>
<?
}

function get_tick_interval() {
  $tick = TICK;
  $tickString = "";
  if ($tick < 60) {
    $tickString = "$tick Sekunden"; 
  } else {
    $tickString = floor($tick/60) . "Minuten " . $tick % 60 . " Sekunden";
  }
  return $tickString;
}



function stat_terrain($id) {
  global $imagepath;
  $id = intval($id);
?>
<table cellspacing="1" cellpadding="0" border="0">
 <tr>
  <td class="tblhead" colspan="14"><b>Nutzfelder und Sonderressourcen</b></td>
  </tr>
  <tr class="tblhead">
  <th style="font-size: 12px;">Stadt</th>
  <th><img src="<? echo $imagepath;?>/1_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s1_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s2_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/2_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s3_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s4_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/3_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s5_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s6_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/4_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s7_40402D.gif"></th>
  <th><img src="<? echo $imagepath;?>/s8_40402D.gif"></th>
  <th style="font-size: 12px;">Summe</th>
</tr>
<?
  
  $cities=do_mysql_query("SELECT city.id,name,x,y FROM city LEFT JOIN map USING(id) ".
			 " WHERE city.owner = ".$id.
			 " ORDER BY city.capital DESC, city.id ASC");
  while($c = do_mysql_fetch_assoc($cities)) {
    $citysum = 0;

    echo "<tr class=\"tblbody\" align=\"center\">\n";
    printf('<td align=\"left\"><a href="javascript:towninfo(\'%d\')">%s</a></td>', 
	   $c['id'], $c['name']);
  
    for($i=0; $i<4; $i++) {
      $x = $c['x']; $y = $c['y'];
      // Die Felder um die Stadt auswählen, jedoch ohne das Stadtfeld selbst
      
      $s1 = $i*2+1;
      $s2 = $i*2+2;
      $sql = "SELECT count(*) AS c ".
	($_SESSION['premium_flags'] > 0 
	 ?( " , count(if(special = ".$s1.",TRUE,NULL)) AS s1, ".
	    " count(if(special = ".$s2.",TRUE,NULL)) AS s2 ")
	 : "").
	"FROM map ".
	"WHERE x <= $x+2 AND x >= $x-2".
	" AND  y <= $y+2 AND y >= $y-2".
	" AND NOT (x = $x AND y = $y)".
	" AND type = ".($i+1);
      //echo "<!-- $sql -->\n";
      $f = do_mysql_query_fetch_assoc($sql);

      if($_SESSION['premium_flags'] > 0 ) {
	$sql_template = 
	  "SELECT coalesce(sum(count),0) AS c FROM citybuilding c LEFT JOIN building b ON b.id=c.building ".
	  " WHERE c.city = %d AND b.req_fields = 's%d'";


	$sql = sprintf($sql_template, $c['id'], $s1);
	$tmp  = do_mysql_query_fetch_assoc($sql);
	$b[0] = intval($tmp['c']);


	$sql = sprintf($sql_template, $c['id'], $s2);
	$tmp  = do_mysql_query_fetch_assoc($sql);
	$b[1] = intval($tmp['c']);
      }

      echo "\n<!-- \n";
      var_dump($b);
      echo "-->\n";

      if($_SESSION['premium_flags'] > 0){
        $amount1 = ($b[0] > 0)
                    ? ($b[0] < $f['s1'] ? "<font color='#40BB20'>" : "<font color='darkgreen'>"). "<b>(".$b[0].")</b></font>"
                    : ($f['s1'] > 0 ? "(-)" : "");
        $amount2 =  $b[1] > 0
                    ? ($b[1] < $f['s2'] ? "<font color='#40BB20'>" : "<font color='darkgreen'>"). "<b>(".$b[1].")</b></font>"
                    : ($f['s2'] > 0 ? "(-)" : "");
               
        printf("\n<td>%d</td><td>%s %s</td><td>%s %s</td>\n",
        $f['c'],
        $f['s1'] > 0 ? $f['s1'] : "-",
        $amount1,
        $f['s2'] > 0 ? $f['s2'] : "-",
        $amount2 
        );

	$sum['s'.$s1] += $f['s1']; 
	$sum['s'.$s2] += $f['s2'];
	$sum['all'] += $f['s1'] + $f['s2'];
	$citysum += $f['s1'] + $f['s2'];
      }
      else {
	printf("\n<td>%d</td><td>?</td><td>?</td>\n", $f['c']);
      }

      $sum['c'.$i] += $f['c'];
    } // for
    
    printf("<td>%s</td></tr>\n", $citysum);
  }
  echo '<tr class="tblhead" align="center"><td align=\"left\">Summe</td>';
  for($i=0; $i<4; $i++) {
    if($_SESSION['premium_flags'] > 0){
      printf("<td>%d</td><td>%d</td><td>%d</td>", $sum['c'.$i], $sum['s'.($i*2+1)], $sum['s'.($i*2+2)] );
    }
    else {
      printf("\n<td>%d</td><td>?</td><td>?</td>\n", $sum['c'.$i]);
    }
  }
  printf("<td>%s</td></tr>\n", $_SESSION['premium_flags'] > 0 ? $sum['all'] : "?");
  
  if($_SESSION['premium_flags'] == 0){
    echo '<tr class="tblhead" align="center"><td colspan="14" style="font-size: 9px;">*) die erweiterte Statistik ist Besitzern eines <a href="premium.php">Premium-Accounts</a> zugänglich. Das ganze sieht dann in etwa so aus (<a href="'.GFX_PATH_LOCAL.'/nutzfelder.jpg" target="_blank">hier klicken</a>).</td>';
    
  }
}


function stat_res($id) {
  global $imagepath;

  $id = intval($id);
  $res1=do_mysql_query("SELECT
 coalesce(sum(citybuilding.count * building.res_storage), 0)  AS storage,
 coalesce(sum(citybuilding.count * building.res_wood), 0)     AS incwood,
 coalesce(sum(citybuilding.count * building.res_rawwood), 0)  AS incrawwood,
 coalesce(sum(citybuilding.count * building.res_iron), 0)     AS inciron,
 coalesce(sum(citybuilding.count * building.res_rawiron), 0)  AS incrawiron,
 coalesce(sum(citybuilding.count * building.res_stone), 0)    AS incstone,
 coalesce(sum(citybuilding.count * building.res_rawstone), 0) AS incrawstone,
 coalesce(sum(citybuilding.count * building.res_rp), 0)       AS research,
 city.id AS id,
 city.name AS name,
 city.capital AS capital,
 city.rawwood  AS rawwood,
 city.rawiron  AS rawiron,
 city.rawstone AS rawstone
FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.owner = ".$id." GROUP BY id ORDER BY city.capital DESC, city.id ASC");

  $admlvl = get_adm_level($id);
  $citycount = mysqli_num_rows($res1);
  $res_eff = get_eff(floor($citycount/2), $admlvl);
  $eff = get_eff($citycount, $admlvl)*$res_eff;

  echo "<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\">";
  echo "<tr>";
  echo "<td class=\"tblhead\"><b>Ressourcen</b></td>";
  echo "<td class=\"tblbody\" colspan=\"10\" style=\"text-align:right;\">".getTimer($id)."</td>";
  echo "</tr>";
  echo "<tr class=\"tblhead\" align=\"center\">";
  echo '<td align="left">Stadt</td>';
  echo '<td colspan="2">Produktion<br>Holz <i>(max)</i></td>';
  echo "<td>Lager<br>Rohholz</td>";
  echo '<td colspan="2">Produktion<br>Eisen <i>(max)</i></td>';
  echo "<td>Lager<br>Eisenerz</td>";
  echo '<td colspan="2">Produktion<br>Stein <i>(max)</i></td>';
  echo "<td>Lager<br>Bruchstein</td>";
  echo "<td>Lagerraum</td>";
  echo "</tr>";

  $totwood = 0;
  $totiron = 0;
  $totstone = 0;

  $totrawwood = 0;
  $totrawiron = 0;
  $totrawstone = 0;

  $totstoragerawwood = 0;
  $totstoragerawiron = 0;
  $totstoragerawstone = 0;

  $totstorage = 0;

  while ($data1 = do_mysql_fetch_assoc($res1)) {
    $siege_factor = getSiegeFactor($city_siege);

    if ($siege_factor < 1) {
      $data1['incrawwood']  = round($data1['incrawwood'] * $siege_factor);
      $data1['incrawiron']  = round($data1['incrawiron'] * $siege_factor);
      $data1['incrawstone'] = round($data1['incrawstone']* $siege_factor);
    }

    $storage = $data1['storage'];

    //wood
    $wood = get_inc_res($data1['rawwood'] + ($data1['incrawwood'] * RAWWOOD_PRODFACTOR*$res_eff), $data1['incwood'], 2, $storage, WOOD_PRODFACTOR*$res_eff);
    //$storage -= $wood['raw'];

    //iron
    $iron = get_inc_res($data1['rawiron'] + ($data1['incrawiron'] * RAWIRON_PRODFACTOR*$res_eff), $data1['inciron'], 2, $storage, IRON_PRODFACTOR*$res_eff);
    //$storage -= $iron['raw'];

    //stone
    $stone = get_inc_res($data1['rawstone'] + ($data1['incrawstone'] * RAWSTONE_PRODFACTOR*$res_eff), $data1['incstone'], 2, $storage, STONE_PRODFACTOR*$res_eff);
    //$storage -= $stone['raw'];

    // IN $res['raw'] steht drin, wieviel verbraucht wird
    $rawwood = $wood['raw']  -$data1['rawwood'];
    $rawiron = $iron['raw']  -$data1['rawiron'];
    $rawstone = $stone['raw']-$data1['rawstone'];

    $storagewood  = $data1['rawwood'];
    $storageiron  = $data1['rawiron'];
    $storagestone = $data1['rawstone'];

    $totwood += $wood['res'];
    $totiron += $iron['res'];
    $totstone += $stone['res'];
    $totrawwood += $rawwood;
    $totrawiron += $rawiron;
    $totrawstone += $rawstone;

    $totstoragewood += $storagewood;
    $totstorageiron += $storageiron;
    $totstoragestone += $storagestone;

    $totstorage += intval($data1['storage']);

    echo "<tr class=\"tblbody\" align=\"right\">";
    echo '<td nowrap class="cityname">'.get_city_htmllink_koords($data1)."</td>";

    echo "<td>".$wood['res']."</td><td><i>(".$data1['incwood']*WOOD_PRODFACTOR*$res_eff.")</i></td>";    
    echo "<td>".$storagewood;
    if ($rawwood != 0) {
      echo " ".prettyNum($rawwood);
    }


    echo "</td><td>".$iron['res']."</td><td><i>(".$data1['inciron']*IRON_PRODFACTOR*$res_eff.")</i></td>";
    echo "<td>".$storageiron;
    if ($rawiron != 0) {
      echo " ".prettyNum($rawiron);
    }

    
    echo "</td><td>".$stone['res']."</td><td><i>(".$data1['incstone']*STONE_PRODFACTOR*$res_eff.")</i></td>";
    echo "<td>".$storagestone;
    if ($rawstone != 0) {
      echo " ".prettyNum($rawstone);
    }
    echo "</td><td>".intval($data1['storage'])."</td>";
    echo "</tr>";
  } // while

  echo "<tr align=\"right\" class=\"tblhead\">\n";
  echo "<td align=\"left\">Total / Zeiteinheit</td>\n";
  echo '<td colspan="2">'.$totwood."</td>\n";
  echo "<td>".$totstoragewood;
  if ($totrawwood != 0) {
    echo " ".prettyNum($totrawwood);
  }
  echo '</td><td colspan="2">'.$totiron."</td>\n";
  echo "<td>".$totstorageiron;
  if ($totrawiron != 0) {
    echo " ".prettyNum($totrawiron);
  }
  echo '</td><td colspan="2">'.$totstone."</td>\n";
  echo "<td>".$totstoragestone;
  if ($totrawstone != 0) {
    echo " ".prettyNum($totrawstone);
  }
  echo "</td><td>".$totstorage."</td>\n";
  echo "</tr>";
  if ($res_eff < 1) {
    echo "<tr class=\"tblhead\"><td colspan='11'><b>Sie haben lediglich eine Geb&auml;udeeffektivität von ".round($res_eff*100)."%</b></td></tr>\n";
  }
  echo "<tr class=\"tblhead\" height=\"5\"><td colspan='11'></td></tr>";
  echo "<tr class=\"tblbody\"><td colspan='11'><h3>Legende:</h3>Man benötigt für 1 Endprodukt 2 Rohstoffe (z.B. 2 Bruchstein für 1 Stein).<br>Mehr als <i>maximal</i> kann von den errichteten Gebäuden nicht produziert werden (Kapazität des Gebäudes).<br>überschüssige Rohstoffe landen in den Lagern.</td></tr>";



  echo "</table>";
}

function stat_weapons($id) {
  global $imagepath;
  $id = intval($id);
  $res1=do_mysql_query("SELECT id, lastres, religion, wood, iron, stone, gold, clan FROM player WHERE id = ".$id);
  $data1=do_mysql_fetch_assoc($res1);

  $res2=do_mysql_query("SELECT
			sum(citybuilding.count * building.res_shortrange) AS incshortrange,
			sum(citybuilding.count * building.res_longrange) AS inclongrange,
			sum(citybuilding.count * building.res_armor) AS incarmor,
			sum(citybuilding.count * building.res_horse) AS inchorse,
			sum(citybuilding.count * building.res_storage) AS storage,
			sum(citybuilding.count * building.res_wood) AS incwood,
			sum(citybuilding.count * building.res_rawwood) AS incrawwood,
			sum(citybuilding.count * building.res_iron) AS inciron,
			sum(citybuilding.count * building.res_rawiron) AS incrawiron,
			sum(citybuilding.count * building.res_stone) AS incstone,
			sum(citybuilding.count * building.res_rawstone) AS incrawstone,
			sum(citybuilding.count * building.res_rp) AS research,
			sum(citybuilding.count * building.res_gold) AS incgold,
			sum(citybuilding.count * building.res_foodstorage) AS foodstorage,
			sum(citybuilding.count * building.res_food) AS incfood,
			sum(citybuilding.count * building.res_attraction) AS attr,
			sum(citybuilding.count * building.res_training1 / building.res_training1) AS train1,
			sum(citybuilding.count * building.res_training2 / building.res_training2) AS train2,
			sum(citybuilding.count * building.res_training3 / building.res_training3) AS train3,
			sum(citybuilding.count * building.res_defense) AS defbonus,
			city.id AS id,
			city.name AS name,
			city.population AS pop,
            city.capital AS capital,
			city.food AS food,
			city.religion AS religion,
			city.rawwood AS rawwood,
			city.rawiron AS rawiron,
			city.rawstone AS rawstone,
			city.shortrange AS shortrange,
			city.longrange AS longrange,
			city.armor AS armor,
			city.horse AS horse,
			city.reserve_shortrange AS reserve_shortrange,
			city.reserve_longrange AS reserve_longrange,
			city.reserve_armor AS reserve_armor,
			city.reserve_horse AS reserve_horse,
			city.max_shortrange AS max_shortrange,
			city.max_longrange AS max_longrange,
			city.max_armor AS max_armor,
			city.max_horse AS max_horse,
			city.populationlimit AS poplimit
			FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.owner = ".$id." GROUP BY id ORDER BY city.capital DESC, city.id ASC");

  $admlvl = get_adm_level($id);
  $citycount = mysqli_num_rows($res2);
  $res_eff = get_eff(floor($citycount/2), $admlvl);
  $eff = get_eff($citycount, $admlvl)*$res_eff;

  $sumgoldcost = 0;
  $sumwoodcost = 0;
  $sumironcost = 0;
  $sumshortinc = 0;
  $sumironinc = 0;
  $sumarmorinc = 0;
  $sumhorseinc = 0;
  $tottrain1 = 0;
  $tottrain2 = 0;
  $tottrain3 = 0;
  $totwood = $data1['wood'];
  $totiron = $data1['iron'];
  $totstone = $data1['stone'];
  $gold = $data1['gold'];
  $ucost = get_ucost($id);
  $gold -= $ucost;

  echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\">\n";
  echo "<tr class=\"tblbody\" align=\"center\">\n";
  echo "<td class=\"tblhead\" align=\"left\"><strong>Rüstungsgüter</strong></td>\n";
  echo "<td colspan=\"4\">Waffenproduktion</td>\n";
  echo "<td colspan=\"3\">Kosten</td>\n";
  echo "<td colspan=\"5\">An Lager</td>\n";
  //echo "<td>Lager</td>\n";
  echo "<td colspan=\"3\">Kasernen</td>\n";
  echo "<td colspan=\"1\">DefBon.</td>\n";
  echo "</tr>\n";
  
  echo "<tr align=\"center\" class=\"tblhead\">\n";
  echo "<td width=\"150\" align=\"left\"><strong>Stadt</strong></td>\n";
  // Produktion
  echo "<td width=\"25\"><img src='".$imagepath."/sword.gif' alt='Schwert'></td>\n";
  echo "<td width=\"25\"><img src='".$imagepath."/bow.gif' alt='Bogen'></td>\n";
  echo "<td width=\"25\"><img src='".$imagepath."/armor.gif' alt='Rüstung'></td>\n";
  echo "<td width=\"25\"><img src='".$imagepath."/horse.gif' alt='Pferd'></td>\n";
  
  // Kosten
  echo "<td width=\"30\"><img src='".$imagepath."/wood2.gif' alt='Holz'></td>\n";
  echo "<td width=\"30\"><img src='".$imagepath."/iron2.gif' alt='Eisen'></td>\n";
  echo "<td width=\"30\"><img src='".$imagepath."/gold2.gif' alt='Gold'></td>\n";
  
  echo "<td width=\"36\"><img src='".$imagepath."/sword.gif' alt='Schwert'></td>\n";
  echo "<td width=\"36\"><img src='".$imagepath."/bow.gif' alt='Bogen'></td>\n";
  echo "<td width=\"36\"><img src='".$imagepath."/armor.gif' alt='Rüstung'></td>\n";
  echo "<td width=\"36\"><img src='".$imagepath."/horse.gif' alt='Pferd'></td>\n";
  echo "<td width=\"36\"><strong>max</strong></td>\n";
  echo "<td width=\"20\"><strong>kl</strong></td>\n";
  echo "<td width=\"20\"><strong>gr</strong></td>\n";
  echo "<td width=\"20\"><strong>or</strong></td>\n";
  echo "<td width=\"20\">&nbsp;</td>\n";
  echo "</tr>\n";


  while($data2=do_mysql_fetch_assoc($res2)) {
    $storage = $data2['storage'];

    //wood
    $wood = get_inc_res($data2['rawwood'] + ($data2['incrawwood'] * RAWWOOD_PRODFACTOR*$res_eff), $data2['incwood'], 2, $data2['storage'], WOOD_PRODFACTOR*$res_eff);
    $storage -= $wood['raw'];

    //iron
    $iron = get_inc_res($data2['rawiron'] + ($data2['incrawiron'] * RAWIRON_PRODFACTOR*$res_eff), $data2['inciron'], 2, $data2['storage'], IRON_PRODFACTOR*$res_eff);
    $storage -= $iron['raw'];

    //ja, Lager haben eigentlich die 5fache Kapazität (1x res, 4x waffen)
    $storage = $data2['storage'];

    //oben ist jeweils das Neuproduzierte in $res

    $totwood += $wood['res'];
    $totiron += $iron['res'];

    //shortrange
    $shortrange = get_inc_wep($totiron, $data2['incshortrange'], SHORTRANGE_COST,  $data2['storage'], $data2['reserve_shortrange'], $data2['shortrange'], SHORTRANGE_PRODFACTOR*$res_eff, $data2['max_shortrange']);
    $iron['res'] = $shortrange['raw'];

    //longrange
    $longrange = get_inc_wep($totwood, $data2['inclongrange'], LONGRANGE_COST,  $data2['storage'], $data2['reserve_longrange'], $data2['longrange'], LONGRANGE_PRODFACTOR*$res_eff, $data2['max_longrange']);
    $wood['res'] = $longrange['raw'];

    //horse
    $horse = get_inc_wep($gold, $data2['inchorse'], HORSE_COST,  $data2['storage'], $data2['reserve_horse'], $data2['horse'], HORSE_PRODFACTOR*$res_eff, $data2['max_horse']);

    //armor
    //$iron enthält das ganze Eisen, siehe shortrange
    $armor = get_inc_wep($iron['res'], $data2['incarmor'], ARMOR_COST,  $data2['storage'], $data2['reserve_armor'], $data2['armor'], ARMOR_PRODFACTOR*$res_eff, $data2['max_armor']);

    echo "<tr class=\"tblbody\" align=\"right\">\n";
    echo '<td nowrap class="cityname">'.get_city_htmllink_koords($data2)."</td>\n";
    echo "<td>".($shortrange['res'])."</td>\n";
    echo "<td>".($longrange['res'])."</td>\n";
    echo "<td>".($armor['res'])."</td>\n";
    echo "<td>".($horse['res'])."</td>\n";
    echo "<td>".($totwood-$longrange['raw'])."</td>\n";
    echo "<td>".($totiron-$armor['raw'])."</td>\n";
    echo "<td>".($gold-$horse['raw'])."</td>\n";
    $res3 = do_mysql_query("SELECT sum(longrange) as costlong, sum(shortrange) as costshort, sum(armor) as costarmor, sum(horse) as costhorse FROM city WHERE id=".$data2['id']);
    $data3 = do_mysql_fetch_assoc($res3);
    echo "<td>".number_format($data3['costshort'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costlong'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costarmor'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costhorse'], 0,",",".")."</td>\n";
    $res4 = do_mysql_query("SELECT sum(count * res_storage) AS storagelimit FROM building LEFT JOIN citybuilding ON citybuilding.building = building.id WHERE city = ".$data2['id']) or die(mysqli_error($GLOBALS['con']));
    $data4 = mysqli_fetch_array($res4);
    echo "<td>".number_format($data4['storagelimit'] + 0, 0,",",".")."</td>\n";
    echo "<td>".($data2['train1'] + 0)."</td>\n";
    echo "<td>".($data2['train2'] + 0)."</td>\n";
    echo "<td>".($data2['train3'] + 0)."</td>\n";
    echo "<td>".($data2['defbonus'] + 0)."</td>\n";
    echo "</tr>\n";
    $sumshortinc += $shortrange['res'];
    $sumlonginc += $longrange['res'];
    $sumarmorinc += $armor['res'];
    $sumhorseinc += $horse['res'];
    $sumgoldcost += $gold-$horse['raw'];
    $sumwoodcost += $totwood-$longrange['raw'];
    $sumironcost += $totiron-$armor['raw'];
    $sumI += $data3['costshort'];
    $sumJ += $data3['costlong'];
    $sumK += $data3['costarmor'];
    $sumL += $data3['costhorse'];
    $sumM += $data4['storagelimit'];
    $gold = $horse['raw'];
    $totwood = $longrange['raw'];
    $totiron = $armor['raw'];
    $tottrain1 += $data2['train1'];
    $tottrain2 += $data2['train2'];
    $tottrain3 += $data2['train3'];
  }
  echo "<tr class=\"tblhead\" align=\"right\">\n";
  echo "<td width=\"150\" align=\"left\"><a href=\"info.php?show=city&name=".$data2['name']."\">".$data2['name']."</a></td>\n";
  echo "<td>".$sumshortinc."</td>\n";
  echo "<td>".$sumlonginc."</td>\n";
  echo "<td>".$sumarmorinc."</td>\n";
  echo "<td>".$sumhorseinc."</td>\n";
  echo "<td>".$sumwoodcost."</td>\n";
  echo "<td>".$sumironcost."</td>\n";
  echo "<td>".$sumgoldcost."</td>\n";
  echo "<td>".$sumI."</td>\n";
  echo "<td>".$sumJ."</td>\n";
  echo "<td>".$sumK."</td>\n";
  echo "<td>".$sumL."</td>\n";
  echo "<td>".$sumM."</td>\n";
  echo "<td>".$tottrain1."</td>\n";
  echo "<td>".$tottrain2."</td>\n";
  echo "<td>".$tottrain3."</td>\n";
  echo "<td>&nbsp;</td>\n";
  echo "</tr>\n";
  if ($res_eff < 1) {
    echo "<tr class=\"tblhead\"><td colspan='8'><b>Sie haben lediglich eine Geb&auml;udeeffektivität von ".round($res_eff*100)."%</b></td></tr>";
  }
  echo "</table>\n";
}

function stat_building_ordered($id, $sort) {
  $id=intval($id);
  $sort=intval($sort);

  if($sort == "3") {
    function bld_sortier_funktion($a, $b) {
      return strnatcasecmp($a['still'],$b['still']);
    }
  }
  elseif($sort == "2") {
    function bld_sortier_funktion($a, $b) {
      return strnatcasecmp($a['count'],$b['count']);
    }
  }
  elseif($sort == "1") {
    function bld_sortier_funktion($a, $b) {
      return strnatcasecmp($a['bname'],$b['bname']);
    }
  }
  elseif($sort == "0") {
    function bld_sortier_funktion($a, $b) {
      return strnatcasecmp($a['name'],$b['name']);
    }
  }
  else{
    function bld_sortier_funktion($a, $b) {
      return strnatcasecmp($a['still'],$b['still']);
    }
    $sort = 3;
  }

  echo "<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" width=\"600\">";
  echo "<tr>";
  echo "<td class=\"tblhead\"><b>Geb&auml;ude in Bau</b></td>";
  echo "<td class=\"tblbody\" colspan=\"3\" style=\"text-align:right\">";
  echo "<table cellspacing=\"1\" cellpadding=\"0\">";
  echo "<tr><td><b>Sortierung:</b></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=build&sort=0\">";
  if($sort == "0") echo "<b><i>Stadt</i></b>";
  else echo "Stadt";
  echo "</a></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=build&sort=1\">";
  if($sort == "1") echo "<b><i>Geb&auml;ude</i></b>";
  else echo "Geb&auml;ude";
  echo "</a></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=build&sort=2\">";
  if($sort == "2") echo "<b><i>Anzahl</i></b>";
  else echo "Anzahl";
  echo "</a></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=build&sort=3\">";
  if($sort == "3") echo "<b><i>Zeit</i></b>";
  else echo "Zeit";
  echo "</a></td>";
  echo "</tr>";
  echo "</table>";
  echo "</td>";
  echo "</tr>\n";
  echo "<tr class=\"tblhead\">";
  echo "<td>Stadt</td>";
  echo "<td>Geb&auml;ude</td>";
  echo "<td>Anzahl</td>";
  echo "<td>Fertig in:</td>";
  echo "</tr>";

  $buildings_res = do_mysql_query("SELECT building.name AS bname,citybuilding_ordered.time AS time,citybuilding_ordered.count AS count,city.name AS name,city.id FROM building,city,citybuilding_ordered WHERE citybuilding_ordered.city=city.id AND building.id=citybuilding_ordered.building AND city.owner=".$id);

  $build = array();
  while ($buildings = do_mysql_fetch_assoc($buildings_res)) {
    $still = ($buildings['time']-time());
    array_push($build, array('id' => $buildings['id'],'name'=>$buildings['name'],'bname'=>$buildings['bname'],'count'=>$buildings['count'],'still'=>$still));
    $count++;
  }

  if($count > 0) {
    usort($build, 'bld_sortier_funktion');

    $i=0;
    foreach( $build as $b){

      echo "<tr class=\"tblbody\">";
      echo '<td nowrap class="cityname">'.get_city_htmllink_koords($b)."</td>";
      echo "<td>".$b['bname']."</td>";
      echo "<td style=\"text-align:center;\">".$b['count']."</td>";
      echo "<td><span class=\"noerror\" id=\"".$i."\"><script type=\"text/javascript\">addTimer(".$b['still'].",".$i.");</script></span></td>";
      echo "</tr>\n";
      $i++;
    }
  } else {
    echo "<tr class=\"tblbody\">";
    echo "<td colspan=\"4\" style=\"text-align:center; font-weight:bold;\">Ihr baut keine Geb&auml;ude!</td>";
    echo "</tr>\n";
  }
  echo "</table>";
}

function stat_troop_ordered($id,$sort) {
  $id = intval($id);
  $sort = intval($sort);

  if($sort == "3") {
    function troop_sortier_funktion($a, $b) {
      return strnatcasecmp($a['still'],$b['still']);
    }
  }
  elseif($sort == "2") {
    function troop_sortier_funktion($a, $b) {
      return strnatcasecmp($a['count'],$b['count']);
    }
  }
  elseif($sort == "1") {
    function troop_sortier_funktion($a, $b) {
      return strnatcasecmp($a['uname'],$b['uname']);
    }
  }
  elseif($sort == "0") {
    function troop_sortier_funktion($a, $b) {
      return strnatcasecmp($a['name'],$b['name']);
    }
  }
  else{
    function troop_sortier_funktion($a, $b) {
      return strnatcasecmp($a['still'],$b['still']);
    }
    $sort = 3;
  }

  echo "<table cellspacing=\"1\" cellpadding=\"0\" width=\"600\">";
  echo "<tr>";
  echo "<td class=\"tblhead\"><b>Truppen in Ausbildung</b></td>";
  echo "<td class=\"tblbody\" colspan=\"3\" style=\"text-align:right\">";
  echo "<table cellspacing=\"1\" cellpadding=\"0\">";
  echo "<tr><td><b>Sortierung:</b></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=troops&sort=0\">";
  if($sort == "0") echo "<b><i>Stadt</i></b>";
  else echo "Stadt";
  echo "</a></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=troops&sort=1\">";
  if($sort == "1") echo "<b><i>Einheit</i></b>";
  else echo "Einheit";
  echo "</a></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=troops&sort=2\">";
  if($sort == "2") echo "<b><i>Anzahl</i></b>";
  else echo "Anzahl";
  echo "</a></td>";
  echo "<td class=\"tblhead\"><a href=\"".$_SERVER['PHP_SELF']."?show=troops&sort=3\">";
  if($sort == "3") echo "<b><i>Zeit</i></b>";
  else echo "Zeit";
  echo "</a></td>";
  echo "</tr>";
  echo "</table>";
  echo "</td>";
  echo "</tr>\n";
  echo "<tr class=\"tblhead\">";
  echo "<td>Stadt</td>";
  echo "<td>Einheit</td>";
  echo "<td>Anzahl</td>";
  echo "<td>Fertig in:</td>";
  echo "</tr>";

  $troops_res = do_mysql_query("SELECT cityunit_ordered.count AS count,cityunit_ordered.time AS time,unit.name AS uname,city.name AS name,city.id as id FROM cityunit_ordered,city,unit WHERE cityunit_ordered.city=city.id AND cityunit_ordered.unit=unit.id AND city.owner=".$id);

  $troop = array();
  while ($troops = do_mysql_fetch_assoc($troops_res)) {
    $still = ($troops['time']-time());
    array_push($troop, array('id'=>$troops['id'],'name'=>$troops['name'],'uname'=>$troops['uname'],'count'=>$troops['count'],'still'=>$still));
    $count++;
  }

  if($count > 0) {
    usort($troop, troop_sortier_funktion);
    $i=0;
    foreach ($troop as $t){

      echo "<tr class=\"tblbody\">";
      echo '<td nowrap class="cityname">'.get_city_htmllink_koords($t)."</td>";
      echo "<td>".$t['uname']."</td>";
      echo "<td style=\"text-align:center;\">".$t['count']."</td>";
      echo "<td><span class=\"noerror\" id=\"".$i."\"><script type=\"text/javascript\">addTimer(".$t['still'].",".$i.");</script></span></td>";
      echo "</tr>\n";
      $i++;
    }
  } else {
    echo "<tr class=\"tblbody\">";
    echo "<td colspan=\"4\" style=\"text-align:center; font-weight:bold;\">Ihr bildet keine Truppen aus!</td>";
    echo "</tr>\n";
  }

  echo "</table>";

}

//only for bofh
function stat_troop_city($id) {
  $id = intval($id);
  $troops_res = do_mysql_query("SELECT".
	" unit.id AS unitid,unit.name AS uname,unit.cost,cityunit.count,city.name AS cname, p1.name AS pname, city.id AS cid ".
    " FROM unit,cityunit,city,player AS p1,player AS p2 LEFT JOIN map on map.id=city.id WHERE unit.id=cityunit.unit AND cityunit.city=city.id AND p1.id=city.owner AND p2.id=cityunit.owner AND p2.id=".$id." ORDER BY city.owner=".$_SESSION['player']->getID()." DESC, city.capital DESC, city.id");
  $cid = NULL;

  echo "<form action=".$_SERVER['PHP_SELF'].' method="POST">';
  echo '<table cellspacing="1" cellpadding="0" border="0">';
  echo '<tr class="tblhead"><td>Einheit</td>';
  echo '<td>Stadt</td>';
  echo '<td>Stadtbesitzer</td>';
  echo '<td>Anzahl</td></tr>';

  while ($troops = do_mysql_fetch_assoc($troops_res)) {
    echo "<tr class='tblbody'>\n";
    echo "<td>".$troops['uname']."</td>\n";
    echo "<td>".$troops['cname']."</td>\n";
    echo "<td>".$troops['pname']."</td>\n";
    echo "<td><input disabled type='text' name='unit[".$troops['uid']."][".$troops['cid']."' size='8'> (max. ".$troops['count'].")</td>\n";
    echo "</tr>\n";
  }

  echo "</table>";
  echo '<p><input type="submit" name="disarm" value="Truppen entlassen">';
  echo "</p>";
  echo "</form>";
}

function stat_wert($id) {
  $id = intval($id);
  $res1 = do_mysql_query("SELECT id,name FROM city WHERE owner = ".$id);
  echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";
  echo "<tr style=\"text-align:center;\">\n";
  echo "<td class=\"tblhead\" width=\"50\">&nbsp;</td>\n";
  echo "<td class=\"tblbody\" width=\"150\" colspan=\"3\">Geb&auml;ude</td>\n";
  echo "<td class=\"tblbody\" width=\"250\" colspan=\"5\">Truppen</td>\n";
  echo "<td class=\"tblbody\" width=\"200\" colspan=\"4\">Lager</td>\n";
  echo "</tr>\n";
  echo "<tr class=\"tblhead\" style=\"text-align:center; font-weight:bold;\">\n";
  echo "<td class=\"tblhead\" style=\"text-align:left;\">Stadt</td>\n";
  echo "<td class=\"tblhead\">Gold</td>\n";
  echo "<td class=\"tblhead\">Holz</td>\n";
  echo "<td class=\"tblhead\">Stein</td>\n";
  echo "<td class=\"tblhead\">Gold</td>\n";
  echo "<td class=\"tblhead\">Nahk.</td>\n";
  echo "<td class=\"tblhead\">Fernk.</td>\n";
  echo "<td class=\"tblhead\">R&uuml;st.</td>\n";
  echo "<td class=\"tblhead\">Pferde</td>\n";
  echo "<td class=\"tblhead\">Nahk.</td>\n";
  echo "<td class=\"tblhead\">Fernk.</td>\n";
  echo "<td class=\"tblhead\">R&uuml;st.</td>\n";
  echo "<td class=\"tblhead\">Pferde</td>\n";
  echo "</tr>\n";
  while($data1 = do_mysql_fetch_assoc($res1)) {
    $res2 = do_mysql_query("SELECT sum(citybuilding.count * gold) as costgold, sum(citybuilding.count * wood) as costwood, sum(citybuilding.count * stone) as coststone FROM citybuilding,building WHERE citybuilding.building=building.id AND city=".$data1['id']);
    $data2 = do_mysql_fetch_assoc($res2);
    echo "<tr class=\"tblbody\" style=\"text-align:right; padding-right:5px;\">\n";
    echo '<td nowrap class="cityname">'.get_city_htmllink_koords($data1)."</td>";
   // echo "<td style=\"text-align:left;\"><a href=\"info.php?show=town&name=".$data1['name']."\">".$data1['name']."</a></td>\n";
    echo "<td>".number_format($data2['costgold'],0,",",".")."</td>\n";
    echo "<td>".number_format($data2['costwood'],0,",",".")."</td>\n";
    echo "<td>".number_format($data2['coststone'],0,",",".")."</td>\n";
    $sumA += $data2['costgold'];
    $sumB += $data2['costwood'];
    $sumC += $data2['coststone'];
    $res3 = do_mysql_query("SELECT sum(cityunit.count * gold) as costgold, sum(cityunit.count * longrange) as costlong, sum(cityunit.count * shortrange) as costshort, sum(cityunit.count * armor) as costarmor, sum(cityunit.count * horse) as costhorse FROM cityunit,unit WHERE cityunit.unit=unit.id AND city=".$data1['id']);
    $data3 = do_mysql_fetch_assoc($res3);
    echo "<td>".number_format($data3['costgold'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costshort'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costlong'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costarmor'],0,",",".")."</td>\n";
    echo "<td>".number_format($data3['costhorse'],0,",",".")."</td>\n";
    $sumD += $data3['costgold'];
    $sumE += $data3['costshort'];
    $sumF += $data3['costlong'];
    $sumG += $data3['costarmor'];
    $sumH += $data3['costhorse'];
    $res4 = do_mysql_query("SELECT sum(longrange) as costlong, sum(shortrange) as costshort, sum(armor) as costarmor, sum(horse) as costhorse FROM city WHERE id=".$data1['id']);
    $data4 = do_mysql_fetch_assoc($res4);
    echo "<td>".number_format($data4['costshort'],0,",",".")."</td>\n";
    echo "<td>".number_format($data4['costlong'],0,",",".")."</td>\n";
    echo "<td>".number_format($data4['costarmor'],0,",",".")."</td>\n";
    echo "<td>".number_format($data4['costhorse'],0,",",".")."</td>\n";
    $sumI += $data4['costshort'];
    $sumJ += $data4['costlong'];
    $sumK += $data4['costarmor'];
    $sumL += $data4['costhorse'];
    echo "</tr>\n";
  }

  echo "<tr class=\"tblhead\" style=\"font-weight:bold; text-align:right; padding-right:5px;\">\n";
  echo "<td style=\"text-align:left;\">Summe: </td>\n";
  echo "<td>".number_format($sumA,0,",",".")."</td>\n";
  echo "<td>".number_format($sumB,0,",",".")."</td>\n";
  echo "<td>".number_format($sumC,0,",",".")."</td>\n";

  echo "<td>".number_format($sumD,0,",",".")."</td>\n";
  echo "<td>".number_format($sumE,0,",",".")."</td>\n";
  echo "<td>".number_format($sumF,0,",",".")."</td>\n";
  echo "<td>".number_format($sumG,0,",",".")."</td>\n";
  echo "<td>".number_format($sumH,0,",",".")."</td>\n";

  echo "<td>".number_format($sumI,0,",",".")."</td>\n";
  echo "<td>".number_format($sumJ,0,",",".")."</td>\n";
  echo "<td>".number_format($sumK,0,",",".")."</td>\n";
  echo "<td>".number_format($sumL,0,",",".")."</td>\n";
  echo "</tr>\n";

  echo "</table>";
  echo "<br><br>";
  echo "<table width=\"300\" cellpadding=\"0\" cellspacing=\"1\">\n";
  echo "<tr><td class=\"tblhead\" colspan=\"2\"><strong>Verm&ouml;gen</strong></td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Gold</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumA+$sumD+$_SESSION['player']->gold),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Holz</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumB+$_SESSION['player']->wood),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Eisen</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($_SESSION['player']->iron),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Stein</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumC+$_SESSION['player']->stone),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Nahkampfwaffen</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumE+$sumI),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Fernkampfwaffen</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumF+$sumJ),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">R&uuml;stungen</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumG+$sumK),0,",",".")."</td></tr>\n";
  echo "<tr><td class=\"tblhead\" style=\"width:100px; font-weight:bold;\">Pferde</td><td class=\"tblbody\" style=\"text-align:right;padding-right:5px;\">".number_format(($sumH+$sumL),0,",",".")."</td></tr>\n";
  echo "</table>\n";
}

//only for bofh
function stat_troop_army($id) {
  $id = intval($id);
  echo '<p><a href="'.$_SERVER['PHP_SELF'].'?delallarmies=1"><b class="error">Alle Armeen dieses Spielers löschen(DISALBED)</b></a></p>';
  echo '<table cellspacing="1" cellpadding="0" border="0" width="600">';
  $armies_res = do_mysql_query("SELECT aid,start,end,map.id,x,y,starttime,endtime,mission,missiondata,city.name as cityname,player.name AS playername FROM army LEFT JOIN map ON map.id=army.end LEFT JOIN city ON map.id=city.id LEFT JOIN player ON player.id=city.owner WHERE army.owner=".$id." ORDER BY endtime ASC");
  if (mysqli_num_rows($armies_res)==0) {
    echo "<tr><td colspan='6' class='tblbody'>keine Truppenbewegungen</td></tr>";
  }
  $troops_res=do_mysql_query("SELECT armyunit.unit AS unit, army.aid AS aid, unit.name AS name, count FROM unit, armyunit, army WHERE army.aid=armyunit.aid AND unit.id=armyunit.unit AND army.owner=".$id);
  while ($troops=do_mysql_fetch_assoc($troops_res)) {
    $sg[$troops['aid']][$troops['name']] = $troops['count'];
  }
  while ($armies=do_mysql_fetch_assoc($armies_res)) {
    $remaining=$armies['endtime']-time();
    $txt="";
    switch ($armies['mission']) {
    case "settle";  { $missiontext="Siedeln"; break;}
    case "move";    { $missiontext="Verlegung"; break;}
    case "return";  { $missiontext="Heimkehr"; break;}
    case "attack";  { $missiontext="Angriff"; break; }
    case "burndown";{ $missiontext="Brandschatzen"; break; }
    case "despoil"; { $missiontext="Plündern"; break; }
    }
    if (sizeof($sg[$armies['aid']])>0) {
      foreach ($sg[$armies['aid']] as $key=>$value) {
	$txt=$txt.", ".$value." ".$key;
      }
    }
    echo "<tr valign='top'><td width='160' valign='top' class='tblbody'>".
      "<a href='map.php?gox=".$armies['x']."&goy=".$armies['y']."'>".(isset($armies['cityname']) ? ($armies['cityname']."<br>") : "").(isset($armies['playername']) ? ($armies['playername']."<br>") : "")."&nbsp;(".$armies['x']." : ".$armies['y'].")</a></td><td width='60' align='center' valign='top' class='tblbody'>".$missiontext."</td><td width='350' class='tblbody'>".$armies['missiondata']." Siedler".$txt."</td><td align='center' valign='top' width='50' class='tblbody'><span class='noerror' id='".$armies['aid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$armies['aid'].");</script></span></td><td align='center' valign='top' width='60' class='tblbody'><a target='main' href='".$_SERVER['PHP_SELF']."?delarmy=".$armies['aid']."'><b class='error'>Löschen</b></a></td><td align='center' valign='top' width='200' class='tblbody'></td></td></tr>";
  }
  echo "</table>";
}

//only for bofh
function stat_troop_enemy($id) {
  $id = intval($id);
  echo '<table cellspacing="1" cellpadding="0" width="600">';
  echo '<tr><td class="tblhead"><b>Fremde Angriffe</b></td></tr>';
  $spy_res=do_mysql_query("SELECT army.aid AS aid, army.owner AS owner, army.start AS start, army.end AS end, army.endtime AS endtime, army.mission FROM army, city WHERE army.end = city.id AND city.owner = ".$id." AND city.owner <> army.owner ORDER BY endtime ASC");
  if(mysqli_num_rows($spy_res)>0) {
    while($spy=do_mysql_fetch_assoc($spy_res)) {

      switch ($spy['mission']) {
      case "settle";  { $missiontext="Siedeln"; break;}
      case "move";    { $missiontext="Verlegung"; break;}
      case "return";  { $missiontext="Heimkehr"; break;}
      case "attack";  { $missiontext="Angriff"; break; }
      case "burndown";{ $missiontext="Brandschatzen"; break; }
      case "despoil"; { $missiontext="Plündern"; break; }
      }

      $spy_unit_res=do_mysql_query("SELECT unit,count,unit.name AS name FROM armyunit,unit WHERE armyunit.unit=unit.id AND aid = ".$spy['aid']);
      echo '<tr class="tblbody">';
      echo '<td colspan="2">';
      $remaining = $spy['endtime'] - time();
      $timerline = "<span class='noerror' id='".$spy['aid']."'><script type=\"text/javascript\">addTimer(".$remaining.",".$spy['aid'].");</script></span>";
      echo 'Die Armee des Spielers <b>'.resolvePlayerName($spy['owner'])."(".$missiontext.')</b> erreicht ihre Stadt <b>'.resolveCityName($spy['end']).'</b> in '.$timerline;
      echo '</td>';
      echo '</tr>';
      echo '<tr class="tblbody">';
      echo '<td width="50%" valign="top">Eure Aufklärer konnten folgende Einheiten erspähen:</td>';
      echo '<td valign="top">';
      while($spy_unit=do_mysql_fetch_assoc($spy_unit_res)) {
	echo "<b>".$spy_unit['name']."</b>: ".$spy_unit['count']."<br />";
      }
      echo '</td>';
      echo '</tr>';

    }
  } else echo "<tr class='tblbody'><td>keine Feindesaufklärungen</td></tr>";
  echo "</table>";
}


function stat_troopoverview($id) {
  $id = intval($id);
  $data = array();



  // Zunächst gesamtübersicht
  $sql1 = " 
   SELECT name, sum(count) AS cnt FROM cityunit 
                   LEFT JOIN unit ON cityunit.unit = unit.id
   WHERE cityunit.owner = ".$id." GROUP BY name ORDER BY name";
  
  $sql2 = "
  SELECT name, sum(count) AS cnt FROM army 
                   LEFT JOIN armyunit USING(aid) 
                   LEFT JOIN unit ON armyunit.unit = unit.id
   WHERE army.owner = ".$id." GROUP BY name ORDER BY name";

  $units1 = do_mysql_query( $sql1 );
  $units2 = do_mysql_query( $sql2 );
  
  while( $unit = mysqli_fetch_array($units1)) {
    $unitssum += $unit['cnt'];
    $cityunits[$unit['name']] = $unit['cnt'];
    $units[$unit['name']] = $unit['cnt'];		
  }
  $cityunitssum = $unitssum;
  $unitssum = 0;
  while( $unit = mysqli_fetch_array($units2)) {
    $unitssum += $unit['cnt'];
    $movingunits[$unit['name']] = $unit['cnt'];

    if ($units[$unit['name']] > 0)
      $units[$unit['name']] += $unit['cnt'];
    else 
      $units[$unit['name']] = $unit['cnt'];
  }
  $movingunitssum = $unitssum;
  $unitssum += $cityunitssum ;

  echo( '<table cellspacing="1" cellpadding="0" width="400">'.
        '  <tr class="tblhead">'.
        '    <td><b>Truppen gesamt</b></td>'.
        '    <td>Armee</td>'.
        '    <td>Stadt</td>'.
        '    <td>Gesamt</td>'.
        '  </tr>' );

  if(sizeof($units) > 0) {
    foreach($units AS $unit=>$cnt) {    
      if (strlen($unit) == 0) {
        echo "\n<!-- Error -->\n";
        continue;
      }
      echo '<tr class="tblbody">';
      echo "\n<td>".$unit."</td>\n";
      echo '<td align="right">'.prettyNumber($movingunits[$unit])."</td>\n";
      echo '<td align="right">'.prettyNumber($cityunits[$unit])."</td>\n";
      echo '<td align="right"><b>'.prettyNumber($cnt)."</b></td>\n";
      echo "</tr>\n";      
    }
  
    echo '<tr class="tblhead">';
    echo '<td>Summe</td> ';
    echo '<td align="right">'.prettyNumber($movingunitssum)."</td>\n";
    echo '<td align="right">'.prettyNumber($cityunitssum)."</td>\n";
    echo '<td align="right"><b>'.prettyNumber($unitssum)."</b></td></tr>\n";
  }
  else {
    // Keine Truppen...
    echo('<tr class="tblbody">'.
         '  <td colspan="4" style="text-align:center;">'.
         '   <br /><strong style="color:red">Keine Truppen!</strong><br/>Tut etwas dagegen!<br/><br/>'.
         '  </td>'.
         '</tr>' );

  }

  echo "</table>\n<p>\n";


  $sql = ( 'SELECT uplayer.name as uowner,'.
           '       uplayer.id   as uownerid,'.
           '       cplayer.name as cowner,'.
           '       cplayer.id   as cownerid,'.
           '       unit.name as uname,'.
           '       city.name as cname,'.
           '       city.id   as cid,'.
           '       unit.cost as cost,'.
           '       cityunit.count as count, '.
           '       map.y as y '.
           'FROM player as uplayer,player as cplayer,cityunit,city,unit,map '.
           'WHERE uplayer.id=cityunit.owner AND'.
           '      cplayer.id=city.owner AND'.
           '      unit.id=cityunit.unit AND'.
           '      city.id=cityunit.city AND'.
           '      city.id=map.id AND'.
           '      (city.owner='.$id.' OR cityunit.owner='.$id.') '.
           'ORDER BY cplayer.id!='.$id.',cowner,capital DESC,cid,uplayer.id!='.$id.',uowner,unit.id' );

  $sth = do_mysql_query( $sql );

  while ( $res = do_mysql_fetch_assoc($sth) ) {
    $data[$res['cname']]['player'][$res['uowner']][$res['uname']] = $res['count'];
    $data[$res['cname']]['owner']   = $res['cowner'];
    $data[$res['cname']]['ownerid'] = $res['cownerid'];
    $data[$res['cname']]['cid']     = $res['cid'];
    $owners[$res['uowner']] = $res['uownerid'];
    $owners[$res['cowner']] = $res['cownerid'];
  }

  echo( '<table cellspacing="1" cellpadding="0" width="400">'.
	'  <tr>'.
	'    <td class="tblhead"><b>Stadt</b></td>'.
	'    <td class="tblhead"><b>Truppenübersicht</b></td>'.
	'  </tr>' );

  if (sizeof($data)>0) {
    foreach ( $data as $cname => $tmp1 ) {
      echo( '  <tr class="tblbody">'.
            '    <td valign="top"><a href="javascript:towninfo(\''.$data[$cname]['cid'].'\')">'.
            '<strong>'.$cname.'</strong></a> im Besitz von '.
            '<strong><a href="javascript:playerinfo(\''.urlencode($data[$cname]['owner']).'\')">'.
            $data[$cname]['owner'].'</a></strong></td>'.
            '    <td>' );
      foreach ( $tmp1['player'] as $uowner => $tmp2 ) {
        echo( '    Truppen von <a href="javascript:playerinfo(\''.urlencode($uowner).'\')"><strong>'.$uowner.'</strong></a>'.
              '    <ul>' );
        foreach ( $tmp2 as $uname => $count ) {
          if ($count > 0) 
            echo( '<li>'.prettyNumber($count).' '.$uname.'</li>' );
        }
        echo( '    </ul>' );
      }
      echo( '    </td></tr>' );
    }
  } 
  else {
    echo( '  <tr class="tblbody">'.
          '    <td colspan="2" style="text-align:center;">'.
          '      <br /><strong style="color:red">Sie haben keine stationierten Truppen!</strong><br /><br />'.
          '    </td>'.
          '  </tr>' );
  }
  echo( '</table>' );
}

?>
