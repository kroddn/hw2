<?
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2013
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



/**
 * Funktionen zum Importieren der Karte
 *
 * 
 */

function initColors() 
{
  // Farben definieren
  if(!isset($_SESSION['init_cols'])) 
    {
      $img=getPNG("admintools/colors.png");

      $_SESSION['water']    =imagecolorat($img,0,0);
      $_SESSION['grassland']=imagecolorat($img,1,0);
      $_SESSION['forest']   =imagecolorat($img,2,0);
      $_SESSION['mountain'] =imagecolorat($img,3,0);
      // Specials definieren
      $_SESSION['pearls']=imagecolorat($img,0,1);
      $_SESSION['fish']  =imagecolorat($img,1,1);
      $_SESSION['wine']  =imagecolorat($img,2,1);
      $_SESSION['wheat'] =imagecolorat($img,3,1);
      $_SESSION['furs']  =imagecolorat($img,4,1);
      $_SESSION['herbs'] =imagecolorat($img,5,1);
      $_SESSION['metal'] =imagecolorat($img,6,1);
      $_SESSION['gems']  =imagecolorat($img,7,1);
      $_SESSION['init_cols'] = 1;
    }
}

function initColorsOld() {
  global $water, $grassland, $forest, $mountain, $pearls, $fish, $wine, $wheat, $furs, $herbs, $metal, $gems;

  // Farben definieren
  $imgC=getPNG("admintools/colors.png");

  $water=imagecolorat($imgC,0,0);
  $grassland=imagecolorat($imgC,1,0);
  $forest=imagecolorat($imgC,2,0);
  $mountain=imagecolorat($imgC,3,0);

  // Specials definieren
  $pearls=imagecolorat($imgC,0,1);
  $fish=imagecolorat($imgC,1,1);
  $wine=imagecolorat($imgC,2,1);
  $wheat=imagecolorat($imgC,3,1);
  $furs=imagecolorat($imgC,4,1);
  $herbs=imagecolorat($imgC,5,1);
  $metal=imagecolorat($imgC,6,1);
  $gems=imagecolorat($imgC,7,1);
}
?>
