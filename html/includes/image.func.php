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

function makeMapImg($filename, $resize_height, $resize_width) {
  if (strpos($filename, 'png')) {
    $orig_img = imagecreatefrompng($filename);
  } else {
    $orig_img = imagecreatefromjpeg($filename);
  }
  $size = getimagesize($filename);
  $orig_width = $size[0];
  $orig_height = $size[1];

  $orig_img_scaled = imagescale($orig_img, $resize_width, $resize_height);
  imagedestroy($orig_img);

  if(!$orig_img_scaled) 
  {
    echo "Es gab einen Fehler beim laden des Original Bildes";
  }

  // Root Image for map creation (pure blue and green)
  $root_img = imagecreatetruecolor($resize_width, $resize_height);
  imagecopyresampled($root_img, $orig_img_scaled, 0, 0, 0, 0, $resize_width, $resize_height, $resize_width, $resize_height);
  modifyMapImage($root_img, "root", $resize_height, $resize_width);
  imagepng($root_img, 'maps/map_root.png'); 
  imagedestroy($root_img);

  // Create Clanmap with Weichzeichner
  $clan_img = imagecreatetruecolor($resize_width, $resize_height);
  imagecopyresampled($clan_img, $orig_img_scaled, 0, 0, 0, 0, $resize_width, $resize_height, $resize_width, $resize_height);
  modifyMapImage($clan_img, "smooth", $resize_height, $resize_width);
  imagefilter($clan_img, IMG_FILTER_GAUSSIAN_BLUR);
  imagejpeg($clan_img, 'maps/clanmap.jpg'); 

  // Create RegisterMap with Weichzeichner
  imagescale($clan_img, 160, 160);
  imagejpeg($clan_img, 'maps/registermap.jpg'); 
  imagedestroy($clan_img);

  imagedestroy($orig_img_scaled);
  echo "Bild erfolgreich konvertiert.";
}

// Made by Bernhard Knasm�ller
// Type: 
//   - root: Creates basic image with just pure blue and green
//   - smooth: Creates smooth image for clan/register map 
function modifyMapImage($img, $type, $height, $width)
{
  for($i=0;$i<$height;$i++)
  {	for($e=0;$e<$width;$e++)
    {
      $rgb = imagecolorat($img, $e, $i);
      $r = ($rgb >> 16) & 0xFF;
      $g = ($rgb >> 8) & 0xFF;
      $b = $rgb & 0xFF;

      // Make everything what is blue - Full Blue, else Full green
      if($b>= 200)
        {
          // imagecolorallocate(imagepath, red, green, blue)
          if ($type == "root") {
            $col = imagecolorallocate($img, 0, 0, 254);
          } else {
            $col = imagecolorallocate($img, 102, 102, 153);
          }
        }
      else
        {
          if ($type == "root") {
            $col = imagecolorallocate($img, 0, 254, 0);
          } else {
            $col = imagecolorallocate($img, 102, 153, 102);
          }
        }
      imagesetpixel($img, $e, $i, $col);
    }
  }
  return $img;
}

?>