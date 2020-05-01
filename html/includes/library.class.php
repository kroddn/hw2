<?php
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2011
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
 * Markus Sinner <sinner@psitronic.de>
 *
 * This File must not be used without permission
 ***************************************************/
class Library {
  var $category;
  var $subcategory;
  var $element;
  var $expand;
  var $subexpand;
  var $active;
  var $player;
  var $building;
  var $topic;
	
  // Konstruktor
  function Library($uid) {
    global $researchcategories;

    require("includes/config.inc.php");	

    $this->player=$uid;
    $this->category[0]="Spielprinzipien";
    $this->category[1]="Gebäude";
    $this->category[2]="Einheiten";
    $this->category[3]="Forschungen";
    $this->expand[0]=false;
    $this->expand[1]=false;
    $this->expand[2]=false;
    $this->expand[3]=false;
    for ($i=0;$i<sizeof($strategycategories);++$i) {
      $this->subcategory[0][$i]=$strategycategories[$i];
      $this->subexpand[0][$i]=false;
    }
    for ($i=0;$i<sizeof($buildingcategories);++$i) {
      $this->subcategory[1][$i]=$buildingcategories[$i];
      $this->subexpand[1][$i]=false;
    }
    for ($i=0;$i<sizeof($religions);++$i) {
      $this->subcategory[2][$i]=$religions[$i];
      $this->subexpand[2][$i]=false;
    }
    for ($i=0;$i<sizeof($researchcategories);++$i) {
      $this->subcategory[3][$i]=$researchcategories[$i];
      $this->subexpand[3][$i]=false;
    }

    $count=0;
    $res1=do_mysqli_query("SELECT id,topic,category FROM library ORDER BY category,topic");
    while($data1=mysqli_fetch_assoc($res1)) {
			if ($this->element[0][$data1['category']]) {
				$temp=sizeof($this->element[0][$data1['category']]);
				$this->element[0][$data1['category']][$temp][0]=$data1['topic'];
				$this->element[0][$data1['category']][$temp][1]=$data1['id'];
			}
    }
    $res2=do_mysqli_query("SELECT id,name,category FROM building ORDER BY category,id");
    while($data2=mysqli_fetch_assoc($res2)) {
			if ($this->element[1][$data2['category']]) {
				$temp=sizeof($this->element[1][$data2['category']]);
				$this->element[1][$data2['category']][$temp][0]=$data2['name'];
				$this->element[1][$data2['category']][$temp][1]=$data2['id'];
			}
    }
    $res3=do_mysqli_query("SELECT id,name,religion AS religion,level,type FROM unit ORDER BY religion,id");
    while($data3=mysqli_fetch_assoc($res3)) {
			if ($this->element[2][$data3['religion']-1]) {
				$temp=sizeof($this->element[2][$data3['religion']-1]);
				$this->element[2][$data3['religion']-1][$temp][0]=$data3['name'];
				$this->element[2][$data3['religion']-1][$temp][1]=$data3['id'];
				$img = $GLOBALS['imagepath']."/".getUnitImage($data3);
				$this->element[2][$data3['religion']-1][$temp][2]=$img;
			}
    }
    $res4=do_mysqli_query("SELECT id,name,category FROM research ORDER BY category,typ,typlevel");
    while($data4=mysqli_fetch_assoc($res4)) {
			if ($this->element[3][$data4['category']]) {
				$temp=sizeof($this->element[3][$data4['category']]);
				$this->element[3][$data4['category']][$temp][0]=$data4['name'];
				$this->element[3][$data4['category']][$temp][1]=$data4['id'];
			}
    }
  }
	
  function output() {
    global $standalone;
    if ($standalone==1)
      $LINK = $PHP_SELF."?standalone=1&";
    else
      $LINK = $PHP_SELF."?";

    require("includes/config.inc.php");
    global $imagepath;
    global $csspath;
    echo "<table cellspacing='0' cellpadding='0' border='0'>";
    echo "<tr><td class='nopadding'><img src='".$imagepath."/library1.gif'></td><td colspan='4' width='100%'><b>ROOT</b></td></tr>\n";

    //echo "<tr><td class='nopadding'></td><td class='nopadding'><a target='_new' href='wiki/index.php/Tutorial'><img border='0' src='".$imagepath."/library2.gif'></a></td><td colspan='3' width='100%'><a target='_new' href='wiki/index.php/Tutorial'><b>Spielstart</b></a></td></tr>\n";

    for($i=0;$i<sizeof($this->category);++$i) {
      if ($this->expand[$i]==true) {
        echo "<tr><td class='nopadding'></td><td class='nopadding'><a href='".$LINK."close=".$i."'><img border='0' src='".$imagepath."/library1.gif'></a></td><td colspan='3' width='100%'><a href='".$LINK."close=".$i."'><b>".$this->category[$i]."</b></a></td></tr>\n";
        for($j=0;$j<sizeof($this->subcategory[$i]);++$j) {
          if ($this->subexpand[$i][$j]==true) {
            echo "<tr><td class='nopadding'></td><td class='nopadding'></td><td class='nopadding'><a href='".$LINK."close=".$i."&closesub=".$j."'><img border='0' src='".$imagepath."/library1.gif'></a></td><td colspan='2' width='100%'><a href='".$LINK."close=".$i."&closesub=".$j."'><b>".$this->subcategory[$i][$j]."</b></a></td></tr>\n";
            for ($k=0;$k<sizeof($this->element[$i][$j]);++$k) {
              $image_entry = $this->element[$i][$j][$k][2];

              // Einträge ausgeben
              echo "<!-- Start Entry -->\n<tr><td class='nopadding'></td><td class='nopadding'></td><td class='nopadding'></td>";
			  if(!$image_entry)
			  {
                echo "<td class='nopadding'><img src='".$imagepath."/library3.gif'></td>";
                echo "<td style='padding: 0px; ' width='100%' valign='middle' nowrap>";
			  }
			  else
			  {
			    echo "<td colspan='2' style='padding: 0px; ' width='100%' valign='middle' nowrap>";
			  }
			  
			  echo "<a href='".$LINK."s1=".$i."&s2=".$j."&s3=".$k."'";
              
              
              // Blau markieren, wenn ein Eintrag angeklickt wurde
              if($this->topic != null && $this->element[$i][$j][$k][0] == $this->topic) {
                $this->active[0] = $i;
                $this->active[1] = $j;
                $this->active[2] = $k;
                $mark = "blue";
              }
              // Rot markieren, wenn er durch Direktlink angezeigt wurde
              else if(!isset($_REQUEST['building_id']) && !isset($_REQUEST['unit_id']) && 
                      $this->topic == null && $i == $this->active[0] && $j == $this->active[1] && $k == $this->active[2] ) 
              {
                $mark = "red";
              }
              else {
                $mark = null;
              }
              if($mark) echo " style='color: $mark; font-weight: normal; '";

              // <a tag schließen
              echo ">";
              
			  // Mini-Icon einblenden
              if($image_entry)
              {
                printf('<img border="0" src="%s"> ', $image_entry); 
              }
              
              // Name der Forschung
              echo $this->element[$i][$j][$k][0];
              echo "</a></td></tr>\n";
            }
          }
          else {
            echo "<tr><td class='nopadding'></td><td class='nopadding'></td><td class='nopadding'><a href='".$LINK."open=".$i."&opensub=".$j."'><img border='0' src='".$imagepath."/library2.gif'></a></td><td colspan='2' width='100%'><a href='".$LINK."open=".$i."&opensub=".$j."'><b>".$this->subcategory[$i][$j]."</b></a></td></tr>\n";
          }
        } // for
      }
      else echo "<tr><td class='nopadding'></td><td class='nopadding'><a href='".$LINK."open=".$i."'><img border='0' src='".$imagepath."/library2.gif'></a></td><td colspan='3' width='100%'><a href='".$LINK."open=".$i."'><b>".$this->category[$i]."</b></a></td></tr>\n";
    }
    echo "</table>\n";
  }
	
  function opensub($cat,$subcat) {
    $this->open($cat);
    if (($subcat<sizeof($this->category[$cat])) || ($cat>-1) || ($subcat>-1)) $this->subexpand[$cat][$subcat]=true;
  }
  function closesub($cat,$subcat) {
    if (($subcat<sizeof($this->category[$cat])) || ($cat>-1) || ($subcat>-1)) $this->subexpand[$cat][$subcat]=false;
  }

  function open($cat) {
    if (($cat<sizeof($this->category)) || ($cat>-1)) $this->expand[$cat]=true;
  }
  function close($cat) {
    if (($cat<sizeof($this->category)) || ($cat>-1)) $this->expand[$cat]=false;
  }
	
  function setActive($part1,$part2,$part3) {
    if (isset($this->element[$part1][$part2][$part3][0])) {
      $this->active[0]=$part1;
      $this->active[1]=$part2;
      $this->active[2]=$part3;
    }
  }
	
  function isActive() {
		if ($this->active) {
			if (sizeof($this->active)==3)  return true;
		}
    return false;
  }
	
  function getInfo() {
    global $standalone, $building_id, $research_id, $unit_id, $unittypes, $religions;

    require_once("includes/config.inc.php");
    global $imagepath;
    global $csspath;
    
    $this->topic = null;

      
    switch ($this->active[0]) {
      // Normale Library-Einträge
    case 0: {
      if(isset($GLOBALS['topic']) && strlen($GLOBALS['topic']) > 0) {
        $topic = strtolower($GLOBALS['topic']);
        $res1=do_mysqli_query("SELECT topic,description,category FROM library ".
        					 " WHERE topic LIKE '%".mysqli_escape_string($GLOBALS['con'], $topic)."%'");
        $this->active[2] = null;
      }
      else {
        $res1=do_mysqli_query("SELECT topic,description FROM library ".
        					 " WHERE id=".$this->element[$this->active[0]][$this->active[1]][$this->active[2]][1]);
      }
      while($data1=mysqli_fetch_assoc($res1)) {
        $this->topic = $data1['topic'];
        $info['topic']=$data1['topic'];
        $info['description']=$data1['description'];
        if($data1['category'] != null) {
          $this->opensub(0, $data1['category']);
        }
      }
      return $info;
      break;
    }
      // Gebäude
    case 1: {
      getMapSize($fx, $fy);
      $bid = isset($building_id) ? $building_id : $this->element[$this->active[0]][$this->active[1]][$this->active[2]][1];

      $res1=do_mysqli_query("SELECT * FROM building ".
                           "WHERE id=".intval($bid) );
      while($data1=mysqli_fetch_assoc($res1)) {
	$info['topic']=$data1['name'];
	
	if ($data1['description']==NULL) $info['description']="kein Beschreibungstext vorhanden";
	else $info['description']=$data1['description'];
	
	if(defined("ENABLE_LOYALITY") && ENABLE_LOYALITY && $data1['convert_loyality'] != null)
	{
	  $info['description'] .= sprintf("<br>Ermöglicht das <b>Konvertieren</b> einer Stadt ab einer Loyalität von %d%%.", $data1['convert_loyality']/100);
	}
	
	$info['description'].="<br><br><br>--- <b>Daten:</b> ---<br><br>";
	$info['description'].="<b>".$data1['gold']."</b> <img src='".$imagepath."/gold2.gif'> (Gold)<br>";
	$info['description'].="<b>".$data1['wood']."</b> <img src='".$imagepath."/wood2.gif'> (Holz)<br>";
	$info['description'].="<b>".$data1['stone']."</b> <img src='".$imagepath."/stone2.gif'> (Stein)<br>";
	$factor = defined("BUILDINGSPEED") ? BUILDINGSPEED : 1;
	$info['description'].="<b>".formatTime($data1['time'] / $factor)."</b> <img src='".$imagepath."/time.gif'> (Bauzeit)<br><br>";

	$info['description'].="<b>".$data1['points']."</b> Punkte<br><br>";

	if ($data1['religion']==NULL) $info['description'].="keine Religionsvorraussetzung<p>";
	else {
	   $info['description'].= getReliImage($data1['religion']);
	   $info['description'].=" <b>".$religions[$data1['religion']-1]."</b> (Religionsvorraussetzung)<p>";
	}

	if ($data1['maxy']==NULL) $info['description'].="keine Gebietsbeschränkungen<p>";
	else {
	  $maxy = $fy * $data1['maxy'] / 1000;
          if ($maxy > 0) {
            $info['description'].="Nur <b>südlich von Y = ".$maxy."</b> (Gebietsbeschränkung)<p>";
          }
          else {
            $info['description'].="Nur <b>nördlich von Y = ".(-$maxy-1)."</b> (Gebietsbescänkung)<p>";
          }

	}

	//Forschungen
	$res_res=do_mysqli_query("SELECT id,name,category FROM research WHERE id = ".$data1['req_research']);
	if(mysqli_num_rows($res_res)>0) {
	  $research=mysqli_fetch_assoc($res_res);
	  if ($this->player==-1) {
	    $have = true;
	  }
	  else {
	    $check_have=do_mysqli_query("SELECT research FROM playerresearch WHERE research = ".$research['id']." AND player=".$this->player);
	    $have = mysqli_num_rows($check_have) > 0;
	  }
      
	  $info['description'].="<b>Benötigte Forschung:</b><br>";
	  $info['description'].='<a '.($have ? "" : 'class="red"').' href="library.php?s1=3&s2='.$research['category']."&s3=0&research_id=".$research['id']."&standalone=".$standalone.'">'.$research['name']."</a><br>";
	}
					
	//Forschungen Ende
	switch ($data1['req_fields']) {
	case "city": { $info['description'].="<b>1</b> Gebäude pro Stadt<br><br>"; break; }
	case "plains": { $info['description'].="<b>1</b> Gebäude pro Wiesenfeld<br><br>"; break; }
	case "forest": { $info['description'].="<b>1</b> Gebäude pro Waldfeld<br><br>"; break; }
	case "mountain": { $info['description'].="<b>1</b> Gebäude pro Gebirgsfeld<br><br>"; break; }
	case "water": { $info['description'].="<b>1</b> Gebäude pro Wasserfeld<br><br>"; break; }
	case "s1": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Perlen<br><br>"; break; }
	case "s2": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Fisch<br><br>"; break; }
	case "s3": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Wein<br><br>"; break; }
	case "s4": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Weizen<br><br>"; break; }
	case "s5": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Pelze<br><br>"; break; }
	case "s6": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Kräuter<br><br>"; break; }
	case "s7": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Gold<br><br>"; break; }
	case "s8": { $info['description'].="<b>1</b> Gebäude pro Sonderfeld Edelstein<br><br><br>"; break; }
	default: { $info['description'].="<br><br><br>"; break; }
	}
    
	if ($data1['req_type']!=NULL) {
	  $info['description'].="Vorraussetzung (eines der Gebäude):<br>";
	  $res2=do_mysqli_query("SELECT id,name,category FROM building WHERE type=".$data1['req_type']);
	}
	elseif ($data1['req_special']!=NULL) {
	  $info['description'].="Vorraussetzung:<br>";
	  $res2=do_mysqli_query("SELECT id,name,category FROM building WHERE id=".$data1['req_special']);
	}
	elseif ($data1['typelevel']>1) {
	  $info['description'].="Vorraussetzung (eines der Gebäude):<br>";
	  $res2=do_mysqli_query("SELECT id,name,category FROM building WHERE type=".$data1['type']." AND typelevel=".($data1['typelevel']-1));;
	}
    
	while($res2 && $build=mysqli_fetch_assoc($res2)) {
	  $link = 'library.php?s1=1&s2='.$build['category']."&s3=0&building_id=".$build['id']."&standalone=".$standalone;
	  $info['description'].= '<a href="'.$link.'">'.$build['name']."</a><br>\n";
	}
    

	if ($data1['typelevel']>1) {
	  $info['description'].="<br>Das Gebäude <b>ersetzt</b> das(die) oben genannte(n) Gebäude.<br>";
	}
	$info['description'].="<br><br><br>--- <b>Auswirkungen</b> ---<br><br>";
	if ($data1['res_gold']==NULL) $info['description'].="keine Unterhaltskosten/Einnahmen<br>";
	elseif ($data1['res_gold']<0) $info['description'].="<b>".-$data1['res_gold']."</b> <img src='".$imagepath."/gold2.gif'> / Tick (Unterhaltskosten)<br>";
	else $info['description'].="<b>".$data1['res_gold']."</b> <img src='".$imagepath."/gold2.gif'> / Tick (Einnahmen)<br>";

	if ($data1['res_rawwood']>0) $info['description'].="<b>".$data1['res_rawwood']*RAWWOOD_PRODFACTOR."</b> Rohholz/Tick (Ressourcenerzeugung)<br>";
	if ($data1['res_rawiron']>0) $info['description'].="<b>".$data1['res_rawiron']*RAWIRON_PRODFACTOR."</b> Eisenerz/Tick (Ressourcenerzeugung)<br>";
	if ($data1['res_rawstone']>0) $info['description'].="<b>".$data1['res_rawstone']*RAWSTONE_PRODFACTOR."</b> Bruchstein/Tick (Ressourcenerzeugung)<br>";
	if ($data1['res_food']>0) $info['description'].="<b>".$data1['res_food']."</b> Nahrung/Tick (Ressourcenerzeugung)<br>";
	if ($data1['res_wood']>0) $info['description'].="<b>".$data1['res_wood']*WOOD_PRODFACTOR."</b> <img src='".$imagepath."/wood2.gif'>/Tick (Ressourcenerzeugung, bei Verbrauch von doppelt soviel Rohholz)<br>";
	if ($data1['res_iron']>0) $info['description'].="<b>".$data1['res_iron']*IRON_PRODFACTOR."</b> <img src='".$imagepath."/iron2.gif'>/Tick (Ressourcenerzeugung, bei Verbrauch von doppelt soviel Eisenerz)<br>";
	if ($data1['res_stone']>0) $info['description'].="<b>".$data1['res_stone']*STONE_PRODFACTOR."</b> <img src='".$imagepath."/stone2.gif'>/Tick (Ressourcenerzeugung, bei Verbrauch von doppelt soviel Bruchstein)<br>";
	if ($data1['res_foodstorage']>0) $info['description'].="Lagerplatz für <b>".$data1['res_foodstorage']."</b> Nahrung<br>";
	if ($data1['res_storage']>0) $info['description'].="Allgemeiner Lagerplatz: <b>".$data1['res_storage']."</b> Einheiten (Ausrüstung etc.)<br>";
	if ($data1['res_rp']>0) $info['description'].="<b>".($data1['res_rp']/2)."</b> <img src='".$imagepath."/rp.gif'>/Tick (pro ".RESEARCHEW." Einwohner)<br>";
	if ($data1['res_attraction']>0) $info['description'].="<b>".$data1['res_attraction']."</b> Attraktivität<br>";
	if ($data1['res_defense']>0) $info['description'].="Verteidigungsbonus: <b>".$data1['res_defense']." Punkte</b><br>";
	if ($data1['res_training1']>0) $info['description'].="Erlaubt die Ausbildung von bis zu <b>".$data1['res_training1']."</b> Einheiten (gleichzeitig, Level1)<br>";
	if ($data1['res_training2']>0) $info['description'].="Erlaubt die Ausbildung von bis zu <b>".$data1['res_training2']."</b> Einheiten (gleichzeitig, Level2 oder niedriger)<br>";
	if ($data1['res_training3']>0) $info['description'].="Erlaubt die Ausbildung von bis zu <b>".$data1['res_training3']."</b> Einheiten (gleichzeitig, Level3 oder niedriger)<br>";
	if ($data1['res_scouttime']>0) $info['description'].="<b>".formatTime($data1['res_scouttime'])."</b> Stunden Voraufklärung<br>";
	if ($data1['res_shortrange']>0) $info['description'].="<b>".$data1['res_shortrange']*SHORTRANGE_PRODFACTOR."</b> <img src='".$imagepath."/sword.gif'>/Tick (Erzeugung, bei Verbrauch von ".SHORTRANGE_COST."mal soviel Eisen)<br>";
	if ($data1['res_longrange']>0) $info['description'].="<b>".$data1['res_longrange']*LONGRANGE_PRODFACTOR."</b> <img src='".$imagepath."/bow.gif'>/Tick (Erzeugung, bei Verbrauch von ".LONGRANGE_COST."mal soviel Holz)<br>";
	if ($data1['res_armor']>0) $info['description'].="<b>".$data1['res_armor']*ARMOR_PRODFACTOR."</b> <img src='".$imagepath."/armor.gif'>/Tick (Erzeugung, bei Verbrauch von ".ARMOR_COST."mal soviel Eisen)<br>";
	if ($data1['res_horse']>0) $info['description'].="<b>".$data1['res_horse']*HORSE_PRODFACTOR."</b> <img src='".$imagepath."/horse.gif'>/Tick (Erzeugung, bei Verbrauch von ".HORSE_COST."mal soviel Gold)<br>";
      }
      return $info;
      break;
    }
      

      // Einheiten
      
	case 2: {
	  $uid = isset($unit_id) ? $unit_id : $this->element[$this->active[0]][$this->active[1]][$this->active[2]][1];

	  $res1=do_mysqli_query("SELECT research.id as rid,research.name AS rname,unit.name AS name,unit.description AS description,unit.religion AS religion,type,level,damage,bonus1,bonus2,bonus3,life,speed,unit.time AS time,cost,unit.points AS points,gold,shortrange,longrange,armor,horse FROM unit LEFT JOIN research ON research.id=unit.req_research WHERE unit.id=".intval($uid) );

	  while($data1=mysqli_fetch_assoc($res1)) {
	    // Die Grafik für ne Einheit ergibt sich nach folgendem Muster
	    $img = getUnitImage($data1);

	    $info['topic']=$data1['name'];
	    // Einheiten-Icon in GROSS anhängen
	    $info['description'] = sprintf('<img src="%s/g%s"><br>', $GLOBALS['imagepath'], $img);
	    $info['description'].= $data1['description']==NULL ? "(kein Beschreibungstext vorhanden)" : $data1['description'];
	    
	    $info['description'].="<br><br><br>--- <b>Daten:</b> ---<br><br>";
	    $info['description'].="<b>".$data1['gold']."</b> <img src='".$imagepath."/gold2.gif'> (Gold)<br>";
	    $info['description'].="<b>".$data1['shortrange']."</b> <img src='".$imagepath."/sword.gif'> (Nahkampfwaffen)<br>";
	    $info['description'].="<b>".$data1['longrange']."</b> <img src='".$imagepath."/bow.gif'> (Fernkampfwaffen)<br>";
	    $info['description'].="<b>".$data1['armor']."</b> <img src='".$imagepath."/armor.gif'> (Rüstungen)<br>";
	    $info['description'].="<b>".$data1['horse']."</b> <img src='".$imagepath."/horse.gif'> (Pferde)<br>";
	    
	    $factor = defined("RECRUITSPEED") ? RECRUITSPEED : 1;
	    $info['description'].="<b>".formatTime($data1['time'] / $factor)."</b> <img src='".$imagepath."/time.gif'> (Bauzeit)<br>";
	    $info['description'].="<b>".$GLOBALS['speedtypes'][$data1['speed']-1]."</b> (Geschwindigkeit)<br><br>";
	    $info['description'].="<b>".$data1['points']."</b> Punkte<br><br>";
	    if ($data1['cost']==NULL) $info['description'].="keine Unterhaltskosten<br><br>";
	    else $info['description'].="<b>".$data1['cost']."</b> <img src='".$imagepath."/gold2.gif'>/Tick (Unterhaltskosten)<br><br>";
	    if ($data1['religion']==NULL) $info['description'].="keine Religionsvorraussetzung<br>";
	    else $info['description'].="<b>".$religions[$data1['religion']-1]."</b> (Religionsvorraussetzung)<br>";
	    $info['description'].="<br><b>Benötigte Forschung:</b><br>";
	    $info['description'].=$data1['rname']."<br><br>";
	    $info['description'].="<b>Typ:</b> ".$unittypes[$data1['type']-1]."<br>";
	    $info['description'].="<b>Level ".$data1['level']."</b><br><br>";
	    $info['description'].="<br><br>--- <b>Kampfwerte:</b> ---<br><br>";
	    $info['description'].="<b>".$data1['damage']."</b> <img src='".$imagepath."/damage.gif'> (Schaden) <br>";
	    if ($data1['bonus1']>0) $info['description'].="<b>".$data1['bonus1']."</b> <img src='".$imagepath."/damage2.gif'> gegen ".$unittypes[0]." (Bonusschaden)<br>";
	    if ($data1['bonus2']>0) $info['description'].="<b>".$data1['bonus2']."</b> <img src='".$imagepath."/damage2.gif'> gegen ".$unittypes[1]." (Bonusschaden)<br>";
	    if ($data1['bonus3']>0) $info['description'].="<b>".$data1['bonus3']."</b> <img src='".$imagepath."/damage2.gif'> gegen ".$unittypes[2]." (Bonusschaden)<br>";
	    $info['description'].="<b>".$data1['life']."</b> <img src='".$imagepath."/life.gif'> (Lebensenergie)<br>";
	  }
	  return $info;
	  break;
	}
	
	
	//Forschungen
    case 3: {
      $rid = isset($research_id) ? $research_id : $this->element[$this->active[0]][$this->active[1]][$this->active[2]][1];
      $res1=do_mysqli_query("SELECT id,name,description,rp,time,religion,points,management,category FROM research ".
                           " WHERE id = ".intval($rid) );
      while($data1=mysqli_fetch_assoc($res1)) {


	// die Fähigkeiten Anzeigen, die dadurch forschbar werden
	$res = do_mysqli_query("SELECT name,id,category FROM req_research LEFT JOIN research ON research.id=research_id ".
			      " WHERE req_research = ".intval($rid) );

	$rows = mysqli_num_rows($res);
	if ($rows) {
	  $info['description'] .="Diese Forschung ermöglicht: <br><b>";
	  while ($leadsto = mysqli_fetch_assoc($res)) {
	    $rows--;
	    $info['description'].='<a href="library.php?s1=3&s2='.$leadsto['category']."&s3=0&research_id=".$leadsto['id']."&standalone=".$standalone.'">'.$leadsto['name'];
	    
	    if ($rows)
	      $info['description'].="</a>, \n";
	    else
	      $info['description'].="</a></b><p>\n";	  

	  }	 
	}
	
	// Bei Militärforschungen Gebäude anzeigen
	// die Fähigkeiten Anzeigen, die dadurch forschbar werden
	$res = do_mysqli_query("SELECT name,id,religion FROM unit ".
			      " WHERE req_research = ".intval($rid) );
	$rows = mysqli_num_rows($res);
	if ($rows) {
	  $info['description'] .="Ermöglicht die Ausbildung von: <br><b>";
	  while ($leadsto = mysqli_fetch_assoc($res)) {
	    $rows--;
            $info['description'] .= '<a href="library.php?s1=2&s2='.$leadsto['religion'].'&s3=0&unit_id='.$leadsto['id']."&standalone=".$standalone.'">';

	    if ($rows)
	      $info['description'].=$leadsto['name']."</a>, ";
	    else
	      $info['description'].=$leadsto['name']."</a></b><p>";
	  }
	} // if


	// Bei manchen die Gebäude anzeigen
	$res = do_mysqli_query("SELECT name,id,category FROM building ".
			      " WHERE req_research = ".intval($rid) );
	$rows = mysqli_num_rows($res);
	if ($rows > 0) {
	  $info['description'] .="Ermöglicht die Errichtung von: <br><b>";
	  while ($leadsto = mysqli_fetch_assoc($res)) {
	    $rows--;	   
	    $info['description'].='<a href="library.php?s1=1&s2='.$leadsto['category']."&s3=0&building_id=".$leadsto['id']."&standalone=".$standalone.'">'.$leadsto['name'];
	    if ($rows)
	      $info['description'].="</a>, ";
	    else
	      $info['description'].="</a></b><p>";
	  }
	} // if
	
	       
	$info['topic']=$data1['name'];
	if ($data1['description']==NULL) $info['description'].="kein Beschreibungstext vorhanden";
	else $info['description'].=$data1['description'];
	$info['description'].="<br><br><br>--- <b>Daten:</b> ---<br><br>";
	$info['description'].="<b>".$data1['rp']."</b> <img src='".$imagepath."/rp.gif'> (Forschungspunkte)<br>";
	
	$factor = defined("RESEARCHSPEED") ? RESEARCHSPEED : 1;
	$info['description'].="<b>".formatTime(max(MIN_RESEARCH_TIME, $data1['time'] / $factor))."</b> <img src='".$imagepath."/time.gif'> (Entwicklungsdauer)<br>";
	$info['description'].="<b>".$data1['points']."</b> Punkte<br><br>";
	if ($data1['religion']==NULL) $info['description'].="keine Religionsvorraussetzung<br>";
	else $info['description'].="<b>".$religions[$data1['religion']-1]."</b> (Religionsvorraussetzung)<br>";
	$info['description'].="<br><br><b>Besonderheiten:</b><br>";
	if($data1['management']==NULL) $info['description'].="keine Besonderheiten<br>"; 
	else $info['description'].="Bis zu <b>".$GLOBALS['fpcost'][$data1['management']][0]." Städte können zu 100%</b> genutzt werden";

	$res2=do_mysqli_query("SELECT research.name AS name, research.id AS id, lib_link FROM research, req_research  WHERE research.id = req_research.req_research AND req_research.research_id = ".$data1['id']);
	if(mysqli_num_rows($res2)>0) {
	  $info['description'].="<p><b>Benötigte Forschungen:</b><br>";
	  while($data2=mysqli_fetch_assoc($res2)) {
	    if ($this->player==-1) {
	      $check_have=do_mysqli_query("SELECT id as research FROM research WHERE id = ".$data2['id']);
	    }
	    else {
	      $check_have=do_mysqli_query("SELECT research FROM playerresearch WHERE research = ".$data2['id']." AND player=".$this->player);
	    }
            
            // Wenn ein Spieler die Forschung nicht hat, dann rot markieren
	    if(mysqli_num_rows($check_have)>0) {
	      $info['description'].= '<a href="library.php?s1=3&s2='.$data2['category']."&s3=0&research_id=".$data2['id']."&standalone=".$standalone.'">'.$data2['name']."</a><br>\n";
	    }
	    else {
	      $info['description'].='<a class="red" href="library.php?s1=3&s2='.$data2['category']."&s3=0&research_id=".$data2['id']."&standalone=".$standalone.'">'.$data2['name']."</a><br>\n";
	      $red = true;
	    }
	  }
	  if ($red) {
	    $info['description'].="Rot markierte Forschungen müsst Ihr noch erschließen!";
	  }
	}
      }
      return $info;
      break;
    }
    }
		
  }
}
?>
