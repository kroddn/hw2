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
 * Copyright (c) 2003-2008
 *
 * Stefan Neubert
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/monument.inc.php");
include_once("includes/diplomacy.common.php");


class MapVersion3 {
  var $user;
  var $sizex;
  var $sizey;
  var $window_hor;
  var $window_ver;
  var $dbsize;
  var $shift;
  var $actx;
  var $acty;
  var $wantx;
  var $wanty;
  var $fieldsizex;
  var $fieldsizey;
  var $grid;
  var $activeplayer;

  // Konstruktor (zentriert auf Hauptstadt)
  function MapVersion3($sizex, $sizey, $window_hor, $window_ver, $fieldsizex, $fieldsizey, $player) {
    $this->sizex        = $sizex;
    $this->sizey        = $sizey;
    $this->window_hor   = $window_hor;
    $this->window_ver   = $window_ver;
    $this->fieldsizex   = $fieldsizex;
    $this->fieldsizey   = $fieldsizey;
    $this->shift        = ($window_hor-$window_ver)/2;
    $this->dbsize       = min($window_hor,$window_ver)+abs($this->shift);
    $this->actx         = 25;
    $this->acty         = 25;
    $this->grid         = false;
    $this->activeplayer = $player;
  }


  /* eagle 27.11.04: Gibt den Dateiname der entsprechenden Stadtgrafik zurück
   * param:  Religion
   *         Einwohnerzahl
   * return: Dateiname der entsprechenden Stadtgrafik */
  function get_city_grafik($rel,$pop) {
    return '99999_'.$rel.'_'.get_citysize_level($pop).'.gif';
  }


  /* eagle 27.11.04: Hilfsfunktion: gibt html Code mit Link zum Karteverschieben aus
   *  param:  Richtung: left,right,up,down     für ganzen Sektor oder
   *                    hleft,hright,hup,hdown für halben Sektor
   *  return: Ausgabestring mit Link zum verschieben der Karte, verwendet im Kontextmenue */
  function move_sektor($direction) {
    $tr_url  = array( 'left'    => '?moveleft=true',
		      'right'   => '?moveright=true',
		      'up'      => '?movetop=true',
		      'down'    => '?movebottom=true',
		      'hleft'   => '?movehalfleft=true',
		      'hright'  => '?movehalfright=true',
		      'hup'     => '?movehalftop=true',
		      'hdown'   => '?movehalfbottom=true' );
    $tr_desc = array( 'left'    => 'Sektor nach links',
		      'right'   => 'Sektor nach rechts',
		      'up'      => 'Sektor nach oben',
		      'down'    => 'Sektor nach unten',
		      'hleft'   => 'Halber Sektor links',
		      'hright'  => 'Halber Sektor rechts',
		      'hup'     => 'Halber Sektor oben',
		      'hdown'   => 'Halber Sektor unten' );
    return ( '<a href="'.$PHP_SELF.$tr_url[$direction].'"'.
	     ' onmouseover = "this.style.backgroundColor=\'white\';"'.
	     ' onmouseout  = "this.style.backgroundColor=\'\';"'.
	     ' style="width:100%; display:block; padding:1px; padding-left:4px;">'.$tr_desc[$direction].'</a>'."\n" );
  }


  /**
   * Ausgabe des HTML codes für den aktuellen Kartenausschnitt
   * 
   * @return unknown_type
   */
  function output() {
    global $imagepath;
    global $csspath;

    include_once("includes/config.inc.php");

/*
    // gif-Schablone finden
    $f = $imagepath.'/e41002050.gif';
    $f_dimensions = ( "width='" .($this->fieldsizex*($this->window_hor+1))."' ".
		      "height='".($this->fieldsizey*($this->window_ver+1))."'" );
*/
    
    // Imagemap erstellen
    $mapstr  = "<map name='m'>\n";
    $map2str = "<map name='m2'>\n";

    // Ausgabekoordinaten festlegen
    $borderx = 0;
    $bordery = 1;
    $startx  = -41;
    $starty  = -41;
		
    // Tageszeit ermitteln
    $hour =  $uhrzeit = date("H", time());
    echo( "<!-- hour:".$hour."-->" );

    // Grafiken vom Server erzwingen (Ueberaschung Winter-Theme)
    //$imagepath = 'images/ingame';

    // Morgenstunden
    /* erstmal keine Morgenbilder
    if($hour >= 5 && $hour < 9) {
      $folder = '/morning';
    }
    */

    // Im Winter...
    //$folder = '/winter';

    // Nachtstunden
    if($hour < 5 || $hour > 21) {
      $folder = '/night';
    }
    else {
      // Tagesanzeige
      $folder = '/';
    }


    // Kartendaten auslesen und im Array aufbereiten, oberer Teil
    $gox = $this->actx;
    $goy = $this->acty;
    $i = 1;
    $j = 1;

    $fields = mysqli_query($GLOBALS['con'], 'SELECT x, y, type, special, pic,'.
			   '       city.population as population,'.
			   '       city.prosperity as prosperity,'.
			   '       city.id as cityid,'.
			   '       city.name as name,'.
			   '       city.owner as ownerid,'.
			   '       city.religion as religion,'.
			   '       player.name as ownername,'.
               '       player.clan as clan '.
			   'FROM map '.
			   'LEFT JOIN city   ON map.id=city.id '.
			   'LEFT JOIN player ON player.id=city.owner '.
			   'WHERE x >= '.$gox.' AND x <= '.($gox+39).' AND y >= '.$goy.' AND y <= '.($goy+39).' '.
			   'ORDER BY y,x');


    echo( "\n\n".
	  '<table id="map" border="0" cellpadding="0" cellspacing="0" width="1600" '.
	  '       style="color:white; background-image:url(\''.$imagepath.$folder.'/22222.gif\');">'."\n".
	  "\t".
	  '<tr>'."\n" );

    while ( $field = mysqli_fetch_array($fields) ) {
      // Wenn Grid an und Grasfläche dann Koordinaten ausgeben
      if( $GLOBALS['gox'] == $field['x'] && $GLOBALS['goy'] == $field['y']){
        $kclass = "koord_hilight";
        $hilight = true;
      }
      else {
        $kclass = "koord";
        $hilight = false;
      }

      // Vorbelegung Gras
      $background_image = "22222";

      // Wenn wir keine Berge ausgeben wollen und ein pic definiert ist
      if ( $field['type'] != 4 && isset($field['pic']) && $field['pic'] != '' ) {
        $background_image = $imagepath.$folder.'/'.$field['pic'].'.gif';
      }

      // eine Grafikzelle in der Tabelle oeffnen und Hintergrund eintragen
      echo( "\t\t".            
            '<td style="background-image:url(\''.$background_image."');\">\n");
      
      // Wenn wir Berge ausgeben wollen, dann diese in einem neuen Layer ausgeben
      if ( $field['type'] == 4 ) {
        echo '<div style="position:absolute; z-index:1; top:'.(($j*40)-42).'; left:'.(($i*40)-54).';">'.
          '  <img src="'.$imagepath.$folder.'/'.$field['pic'].'.gif">';
        echo '</div>';
      }

      // Stadteinrahmung anzeigen
      if ( $this->grid && $field['type'] == 2) {
        echo '<span><a onclick="showCityBorder('.$i.','.$j.','.$field['x'].','.$field['y'].'); return false; "'.
          '  href="townhall.php?newsettle=true&x='.$field['x'].'&y='.$field['y'].'" class="'.$kclass.'">'.
          $field['x'].':'.$field['y'].
          '</a></span>';
      }
      // Falls koordinaten gesucht wurden, einen weißen Rahmen um das Feld ziehen
      else if ($hilight) {
        echo '<div style="position:absolute; border: 1px solid white; width: 40px; height: 40px; z-index:6; '.
          'top:'.(($j*40)-40).'; left:'.(($i*40)-40).
          ';" class="'.$kclass.'">'.$field['x'].':'.$field['y']."</div>\n";
      }

      // Spezialressourcen ausgeben
      if ( $field['special'] > 0) {
        echo '<div style="z-index:51;position:absolute; left:'.($starty+($i*40)-3).'; top:'.($startx+($j*40)-5).'">'.
          '  <img src="'.$imagepath.$folder.'/s'.$field['special'].'.gif">'.       
          "</div>\n";
        //'.sprintf('title="S%d bei %d:%d"', $field['special'], $field['x'], $field['y']).'
      }

      // Anzahl Quadranten-Teile
      $tx = $this->sizex/200;
      $ty = $this->sizey/200;

      // Stadt ausgeben
      if( $field['cityid'] > 0 ) {
        // Roter Rahmen für aktuelle selektierte Stadt
        if ( $this->wantx == ($gox+$i-1) && $this->wanty == ($goy+$j-1) ) {
          $bordcol = "red";
        }
        else {
          $bordcol = "black";
        }

        echo( '<div style="z-index:19; position:absolute; left:'.($starty+($i*40)-80).
              '; top:'.($startx+($j*40)-80).'; '.
              '            width:200px; height:200px; border:1px solid '.$bordcol.';">'.
              "</div>\n" );

        // Default Werte
        $fontColor = 'black';
        $backColor = 'white';

        if($field['ownername'] == null) {
          $ownername = "Barbaren";
          $clanlink  = "";
          $ownerlink    = "Herrenlose Stadt";
          $js_ownerlink = "Herrenlose Stadt";

          // Beziheung zur Herrenlosen Stadt ist Krieg
          $backColor = "red";
          $fontColor = "yellow";          
        }
        else {
          // Spielerbeziehung ermitteln
          $relation = getPlayerRelation($this->activeplayer,$field['ownerid']);
          switch( $relation ) {
            case 0:
              // Krieg
              $backColor = "red";
              $fontColor = "white";
              break;
            case 2:
              // NAP
              $backColor = "green";
              break;
            case 3:
              // Bündniss
              $backColor = "#6D9BFF";
              break;
            case 4:
              // Ordensbruder
              $backColor = "#8DBBFF";
              break;
            case 5:
              // aktiver Spieler
              $backColor = "yellow";
              break;
          }
          
          // Ordeninfo vorbereiten       
          if ( !isset($field['clan']) || $field['clan'] == '' ) {
            $clanlink = ( '<br>'.
			'geh&ouml;rt zur Zeit<br>'.
			'keinem Orden an!<br>&nbsp;' );
          }
          else {
            $clan_name = get_clan_name($field['clan']);
            $clanlink = ( '&nbsp;ist<br>'.
			'Anh&auml;nger des Ordens<br>'.$clan_name.'<br>'.
			'<a href=info.php?show=clan&id='.$field['clan'].'>Zur Ordensseite</a>' );
          }
          
          // Spielernamen säubern
          $ownername = str_replace(" ", "%20", $field['ownername']);
          $ownerlink = 'Herrscher: <a class="hell" href="javascript:playerinfo(\''.urlencode($field['ownername']).'\')">'.$field['ownername'].'</a>';
          // Spezielle konvertierung für den "Zurück" Button, der per JavaScript code HTML in ein div einfügt
          $js_ownerlink = htmlentities('Herrscher: <a class="hell" href="javascript:playerinfo(\\\''.urlencode($field['ownername']).'\\\')">'.$field['ownername'].'</a>', ENT_QUOTES);
        }
        
        echo( '<div id="i'.$field['cityid'].'" class="cityinfo" '.
	      '     style="left:'.($starty + $i*40 - 79).'; top:'.(($startx+($j*40))+66).'; color:'.$fontColor.';'.
	      '     width:198px; height:53px; z-index:306; background-color:'.$backColor.';">'."\n".
	      '  <a class="hell" style="" href="javascript:towninfo('.$field['cityid'].')">'.$field['name'].'</a><br>'.
	      '  '.$ownerlink.'<br>'.
	      '  <i>kein Spionagebericht</i><br>'.
	      '  Status: '.get_population_string($field['population'], $field['prosperity'])."\n".
	      '</div>'."\n" );

        /// Was soll das ganze hier... nochmal denselben Inhalt als id=jXXXXX ????
/*
        echo( '<div id="j'.$field['cityid'].'" class="cityinfo" '.
	      '     style="left:'.($starty + $i*40 - 79 ).'; top:'.(($startx+($j*40))+66).'; color:'.$fontColor.';'.	      
	      '     width:198px; z-index:307; background:transparent;">'."\n".
	      '  <a class="hell" href="javascript:towninfo('.$field['cityid'].')">'.$field['name'].'</a><br>'.
	      '  '.$ownerlink.'<br>'.
	      '  <i>kein Spionagebericht</i><br>'.
	      '  Status: '.get_population_string($field['population'], $field['prosperity'])."\n".
	      '</div>'."\n" );
*/

        echo( '<div id="a'.$field['cityid'].'" style="cursor:pointer; border-top: 1px solid white;'.
	      '     border-left: 1px solid white; position:absolute; background-color:#F0F0AA;'.
	      '     left:'.( $starty + $i*40 + 102).'; top:'.(($startx+($j*40))+66).'; color:black;'.
	      '     -moz-opacity:0.5; filter:Alpha(opacity=50, finishopacity=50, style=2);'.
	      '     display:block; width:17px; height:17px; z-index:308; text-align:center;"'.
	      '     onclick="tipThis(\'<strong>Mylord '.$_SESSION['player']->getName().'!</strong><br>'.
	      '     Soll diese '.get_population_string($field['population'], $field['prosperity']).' auf Euer'.
	      '     Gehei&szlig; hin attakiert werden?<br>'.
	      '     <a href=\\\'general.php?selectx='.($i+$gox-1).'&selecty='.($j+$goy-1).'\\\'>ATTACKE!'.
	      '     </a>\',\''.$field['cityid'].'\');">'.
	      'A'.
	      '</div>'."\n" );
        
        // Ordensinfo nur bei Spielern, herrenlose Städte nicht
        if($field['ownername']) {
          echo( '<div id="b'.$field['cityid'].'" style="cursor:pointer; border-top: 1px solid white;'.
	           '     border-left: 1px solid white; position:absolute; background-color:#F0F0AA;'.
	           '     left:'.( $starty + $i*40 + 102).'; top:'.(($startx+($j*40))+84).'; color:black;'.
	           '     -moz-opacity:0.5; filter:Alpha(opacity=50, finishopacity=50, style=2);'.
	           '     display:block; width:17px; height:17px; z-index:308; text-align:center;"'.
	           '     onclick="tipThis(\'<a href=\\\'info.php?show=player&name='.$ownername.'\\\'>'.$field['ownername'].
	           '     </a>'.$clanlink.'\', \''.$field['cityid'].'\');">'.
	      'O'.
	      '</div>'."\n" );
        }

        // Den Button mit <         
        echo( '<div id="c'.$field['cityid'].'" style="cursor:pointer; border-top: 1px solid white;'.
	      ' border-left: 1px solid white; position:absolute; background-color:#F0F0AA;'.
	      ' left:'.( $starty + $i*40 + 102).'; top:'.(($startx+($j*40))+102).'; color:black;'.
	      ' -moz-opacity:0.5; filter:Alpha(opacity=50, finishopacity=50, style=2);'.
	      ' display:block; width:17px; height:16px; z-index:308; text-align:center;"'.
	      ' onclick="tipThis(\''.
	      ' <a href=\\\'javascript:towninfo('.$field['cityid'].')\\\'>'.$field['name'].'</a><br>'.
	      $js_ownerlink."<br>".
	      ' <i>kein Spionagebericht</i><br>'.
	      ' Status: '.get_population_string($field['population'], $field['prosperity']).'\',\''.$field['cityid'].'\');">'.
	      '&lt;'.
	      '</div>'."\n" );

        echo( '<div onclick="hideCity('.$field['cityid'].')" id="city" style="z-index:305; position:absolute;'.
	      '     left:'.($starty+($i*40)-22).'; top:'.(($startx+($j*40))-22).'">'.
	      '  <img src="'.$imagepath.$folder.'/'.$this->get_city_grafik($field['religion'],$field['population']).'">'.
	      '</div>'."\n" );

        echo( '</div>'."\n" );
      }
      // Ende Stadt ausgeben

      // die Grafikzelle schliessen
      echo( '</td>'."\n" );

      if ( $i == 40 ) {
        $i = 0;
        echo( "\t".'</tr>'."\n" );
        if( $j != 40 ) {
          echo( "\t".'<tr>'."\n" );
        }
        $j++;
      }
      $i++;

    } // while
    echo "</table>\n\n";



    // Momumente ausgeben
    print_monuments($gox, $goy);


    // Kontext Menu
    echo( '<div id="menu" style="position:absolute;width:150px;top:-250;left:0;z-index:500; '.
	  '     background-image:url(\''.$imagepath.'/bg.gif\'); border:1px solid black;">'."\n".
    $this->move_sektor('left').
    $this->move_sektor('right').
    $this->move_sektor('up').
    $this->move_sektor('down').
	  "\t".'<hr style="border:1px solid black;">'."\n&nbsp;".
    $this->move_sektor('hleft').
    $this->move_sektor('hright').
    $this->move_sektor('hup').
    $this->move_sektor('hdown').
	  "\t".'<hr style="border:1px solid black;">'."\n&nbsp;".
	  '<span id="optNavElements" style="font-weight:bold; cursor:pointer; width:100%; display:block; padding:1px; padding-left:4px;"'.
	  '      onclick="hideNavElements();"'.
	  '      onmouseover = "this.style.backgroundColor=\'white\';"'.
	  '      onmouseout  = "this.style.backgroundColor=\'\';"'.
	  '      >Navigation ausblenden</span>'."\n".
	  '<span style="font-weight:bold; cursor:pointer; width:100%; display:block; padding:1px; padding-left:4px;"'.
	  '      onclick="popUp(\'world\');"'.
	  '      onmouseover = "this.style.backgroundColor=\'white\';"'.
	  '      onmouseout  = "this.style.backgroundColor=\'\';"'.
	  '      >Weltkarte anzeigen</span>'."\n".
	  '<span style="font-weight:bold; cursor:pointer; width:100%; display:block; padding:1px; padding-left:4px;"'.
	  '      onclick="popUp(\'help\');"'.
	  '      onmouseover="this.style.backgroundColor=\'white\';"'.
	  '      onmouseout="this.style.backgroundColor=\'\';"'.
	  '      >Hilfefunktion</span>'."\n".
	  '<hr style="border:1px solid black;">'."\n".
	  '<center>' );

    // Gitterbutton
    if ( $this->grid ) {
      echo( '<a href="map.php?grid=0"><img border="0" style="margin:3px;" src="'.$imagepath.'/gridon.gif"></a>' );
    } else {
      echo( '<a href="map.php?grid=1"><img border="0" style="margin:3px;" src="'.$imagepath.'/gridoff.gif"></a>' );
    }

    // "Zentrieren auf Hauptstadt"-Button
    echo( '<a href="map.php"><img src="'.$imagepath.'/centeroncapital.gif" border="0" style="margin:3px;"></a>'.
	  '</center>'.
	  '</div>' );
    // Ende Kontext Menu


    // Windrose
    echo( '<map name="rose">'."\n".
	  ' <area shape="poly" coords="0,0,70,0,35,35"   href="map.php?movetop=true"    title="nach oben">'."\n".
	  ' <area shape="poly" coords="70,0,70,70,35,35" href="map.php?moveright=true"  title="nach rechts">'."\n".
	  ' <area shape="poly" coords="65,65,0,65,35,35" href="map.php?movebottom=true" title="nach unten">'."\n".
	  ' <area shape="poly" coords="0,70,0,0,35,35"   href="map.php?moveleft=true"   title="nach links">'."\n".
	  '</map>'."\n".
	  '<div id="windrose" style="z-index:1000; top:0px; left:0px;">'.
	  '<img src="'.$imagepath.'/windrose.gif" usemap="#rose" border="0">'.
	  '</div>'."\n" );
    // Ende Windrose

    // Navigationskarte
    echo( '<div id="nav11" style="z-index:10; position:absolute; '.
	  '     top:'.($bordery+$this->fieldsizey*($this->window_ver)-100).';'.
	  '     left:'.($borderx+$this->fieldsizex*($this->window_hor)-100).';">'.
	  '<img src="'.$imagepath.
	  ($this->sizex == 800 ? '/navmap.gif' : '/navmap'.$this->sizex.$this->sizey.'.gif' ).
	  '" usemap="#m2" border="0">'.
	  '</div>'."\n".
	  '<div id="nav12" style="z-index:11;  position:absolute;'.
	  '     top:'.($bordery+$this->fieldsizey*($this->window_ver)-100+9*floor(($this->acty/200))+3).';'.
	  '     left:'.($borderx+$this->fieldsizex*($this->window_hor)-100+9*(floor($this->actx/200))+61).';">'.  
	  '<img src="'.$imagepath.'/locatorsmall.gif">'.
	  '</div>'."\n".
	  '<div id="nav13" style="z-index:11; position:absolute;'.
	  '     top:'.($bordery+$this->fieldsizey*($this->window_ver)-100+42+11*(((($this->acty)%200))/40)).';'.
	  '     left:'.($borderx+$this->fieldsizex*($this->window_hor)-100+42+11*(((($this->actx)%200))/40)).';">'.
	  '<img src="'.$imagepath.'/locatorbig.gif">'.
	  '</div>'."\n" );

    // Navigation kleine Karte
    for ($j=0; $j < $ty; ++$j) {
      for ($i=0; $i < $tx; ++$i) {
        $mapstr .= ( '<area shape="rect" coords="'.(100+61+$i*9).','.(100+3+$j*9).','.(100+70+$i*9).','.(100+12+$j*9).
                     '" href="map.php?gox='.($i*200).'&goy='.($j*200).'" title="Quadrant '.(1+$i+$j*$ty).'">'."\n" );
      }    
      $startx=-41;
      $starty=-41;
    }

    // Navigation große Karte
    for ($j=0;$j<5;++$j) {
      for ($i=0;$i<5;++$i) {
        $mapstr .= ( '<area shape="rect" coords="'.
                     ($this->fieldsizex*($this->window_hor)-100+42+$i*11).','.
                     ($this->fieldsizey*($this->window_ver)-100+42+$j*11).','.
                     ($this->fieldsizex*($this->window_hor)-100+53+$i*11).','.
                     ($this->fieldsizey*($this->window_ver)-100+53+$j*11).
                     '" href="map.php?gox='.($i*40+floor($this->actx/200)*200).
                     '&goy='.($j*40+floor($this->acty/200)*200).'" title="Sektor '.(1+$i+$j*5).'">'."\n" );
      }
    }
	
    $offsettop = 1520;
    $offsetleft = 1635;
    
    // Navigationskarte
    echo( '<div id="nav21" style="z-index:20;'.
	  '     top:'.($bordery+$this->fieldsizey*($this->window_ver)-$offsettop).';'.
	  '     left:'.($borderx+$this->fieldsizex*($this->window_hor)-$offsetleft).';">'.
	  '<img src="'.$imagepath.
	  ($this->sizex == 800 ? '/navmap.gif' : '/navmap'.$this->sizex.$this->sizey.'.gif' ).
	  '" usemap="#m2" border="0">'.
	  '</div>'."\n".
	  '<div id="nav22" style="z-index:21; '.
	  '     top:'.($bordery+$this->fieldsizey*($this->window_ver)-$offsettop+9*floor(($this->acty/200))+3).';'.
	  '     left:'.($borderx+$this->fieldsizex*($this->window_hor)-$offsetleft+9*(floor($this->actx/200))+61).';">'.  
	  '<img src="'.$imagepath.'/locatorsmall.gif">'.
	  '</div>'."\n".
	  '<div id="nav23" style="z-index:21; '.
	  '     top:'.($bordery+$this->fieldsizey*($this->window_ver)-$offsettop+42+11*(((($this->acty)%200))/40)).';'.
	  '     left:'.($borderx+$this->fieldsizex*($this->window_hor)-$offsetleft+42+11*(((($this->actx)%200))/40)).';">'.
	  '<img src="'.$imagepath.'/locatorbig.gif">'.
	  '</div>'."\n" );

    // Navigation kleine Karte
    $tx = $this->sizex/200;
    $ty = $this->sizey/200;

    for ($j=0;$j < $ty;++$j) {
      for ($i=0;$i < $tx;++$i) {
        $map2str .= ( '<area shape="rect" coords="'.(61+$i*9).','.(3+$j*9).','.(70+$i*9).','.(12+$j*9).
                      '" href="map.php?gox='.($i*200).'&goy='.($j*200).'" title="Quadrant '.(1+$i+$j*$ty).'">'."\n" );
      }
    }
    
    // Navigation große Karte
    for ($j=0;$j<5;++$j) {
      for ($i=0;$i<5;++$i) {
        $map2str .= ( '<area shape="rect" coords="'.(42+$i*11).','.(42+$j*11).','.(53+$i*11).','.(53+$j*11).
                      '" href="map.php?gox='.($i*40+floor($this->actx/200)*200).
                      '&goy='.($j*40+floor($this->acty/200)*200).'" title="Sektor '.(1+$i+$j*5).'">'."\n" );
      }
    }

    $map2str .= ( '</map>'."\n" );
    $mapstr  .= ( '<area shape="rect" coords="'.
		  ($this->fieldsizex/8*3+45).','.
		  ($this->fieldsizey*($this->window_ver+0.25)).','.
		  ($this->fieldsizex/8*3+88).','.
		  ($this->fieldsizey*($this->window_ver+0.25)+22).
		  '" href="map.php?coc=('.$this->activeplayer.')" title="Auf Hauptstadt zentrieren">'.
		  '</map>'."\n" );

    echo $mapstr;
    echo $map2str;

    echo( "\n\n".
	  '<div id="cityBorder"></div>'.
	  "\n\n".
	  '<div id="citySettle"></div>'.
	  "\n\n" );
  }


  // bewegt die Karte eine ganze Entfernung nach rechts
  function moveRight() {
    $temp=$this->actx+ceil($this->window_hor);
    if (($temp<=$this->sizex-1)/* && ($temp2>=0)*/) {
      $this->actx=$temp;
    }
  }

  // bewegt die Karte eine halbe Entfernung nach rechts
  function moveHalfRight() {
    $temp=$this->actx+ceil($this->window_hor);
    if (($temp<=$this->sizex-1)/* && ($temp2>=0)*/) {
      $this->actx=$temp-20;
    }
  }

  // bewegt die Karte eine ganze Entfernung nach links
  function moveLeft() {
    $temp=$this->actx-ceil($this->window_hor);
    if (($temp>=0)) {
      $this->actx=$temp;
    }
  }

  // bewegt die Karte eine ganze Entfernung nach links
  function moveHalfLeft() {
    $temp=$this->actx-ceil($this->window_hor);
    if (($temp>=0)) {
      $this->actx=$temp+20;
    }
  }

  // bewegt die Karte eine ganze Entfernung nach oben
  function moveTop() {
    $temp2=$this->acty-ceil($this->window_ver);
    if (($temp2>=0)) {
      $this->acty=$temp2;
    }
  }

  // bewegt die Karte eine ganze Entfernung nach oben
  function moveHalfTop() {
    $temp2=$this->acty-ceil($this->window_ver);
    if (($temp2>=0)) {
      $this->acty=$temp2+20;
    }
  }

  // bewegt die Karte eine ganze Entfernung nach unten
  function moveBottom() {
    $temp2=$this->acty+ceil($this->window_ver);
    if (($temp2<=$this->sizey-1)) {
      $this->acty=$temp2;
    }
  }

  // bewegt die Karte eine ganze Entfernung nach unten
  function moveHalfBottom() {
    $temp2=$this->acty+ceil($this->window_ver);
    if (($temp2<=$this->sizey-1)) {
      $this->acty=$temp2-20;
    }
  }
	
  // zentriert die Karte auf die Hauptstadt des angegebenen Spielers
  function centerOnCapital($ply) {
    $res1=do_mysqli_query("SELECT id FROM city WHERE capital=1 AND owner=".$this->activeplayer);
    if (mysqli_num_rows($res1)>0) {
      $cityid=mysqli_fetch_assoc($res1);
      $res2=mysqli_query($GLOBALS['con'], "SELECT x, y FROM map WHERE id=".$cityid['id']) or die(mysqli_error($GLOBALS['con']));
      $mapxy=mysqli_fetch_assoc($res2);
      $this->actx=$mapxy['x'];
      $this->acty=$mapxy['y'];
    }
  }

  function moveXY($x,$y) {
    if (($x<=$this->sizex-1) && ($y<=$this->sizey-1) && ($x>=0) && ($y>=0)) {
      $this->actx=$x;
      $this->acty=$y;
      $this->wantx=$x;
      $this->wanty=$y;
      $mapx = 0;
      while($mapx <= $x) { $mapx =($mapx+40);}
      $this->actx = ($mapx-40);
      $mapy = 0;
      while($mapy <= $y) { $mapy = ($mapy+40);}
      $this->acty=($mapy-40);
    }
  }	
		
  function centerJavascript($x,$y) {
    global $imagepath;
    if (($x<=$this->sizex-1) && ($y<=$this->sizey-1) && ($x>=0) && ($y>=0)) {
      $mapx = 0;
      while($mapx <= $x) { $mapx =($mapx+40);}
      $jx = ($x-($mapx-40));
      $mapy = 0;
      while($mapy <= $y) { $mapy = ($mapy+40);}
      $jy = ($y-($mapy-40));
    }
 ?>
 	<script language="JavaScript">
    <!--
       scrollit(<? echo $jx.','.$jy; ?>);
    // -->
    </script>
 <? 
//    echo( '<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="'.$imagepath.'/bg.gif"'.
//	  '      onload="scrollit('.$jx.','.$jy.');">'."\n" );
    echo "<!-- MapSize (".$this->sizex.":".$this->sizey.")-->\n";
  }
	
  // schaltet das Gitter an oder aus
  function switchGrid($b) {
    $this->grid=$b;
  }
	
  function checkMapID($id) {
    $res1 = do_mysqli_query( 'SELECT map.id '.
			 'FROM city,map '.
			 'WHERE city.id=map.id AND map.x>='.($ax-4).' AND map.x<='.($ax+4).
			 ' AND map.y>='.($ay-4).' AND map.y<='.($ay+4) );
    $res2 = do_mysqli_query( 'SELECT id '.
			 'FROM map '.
			 'WHERE x='.$ax.' AND y='.$ay." AND type='2'" );
    if ( (mysqli_num_rows($res1)>0) || (mysqli_num_rows($res2)==0) ) {
      return false;
    } else {
      return true;
    }
  }
}

?>