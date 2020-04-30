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


// Made by Bernhard Knasmüller
function makeMapImg($filename)
{
  $img = imagecreatefrompng($filename);
  if($img) 
    {
      $height = 400;
      $width = 400;
      for($i=0;$i<$height;$i++)
        {	for($e=0;$e<$width;$e++)
            {
              $rgb = imagecolorat($img, $e, $i);
              $r = ($rgb >> 16) & 0xFF;
              $g = ($rgb >> 8) & 0xFF;
              $b = $rgb & 0xFF;
          
              if($b>= 200)
                {
                  $col = imagecolorallocate($img, 102, 102, 153);
                }
              else
                {
                  $col = imagecolorallocate($img, 102, 153, 102);
                }
              imagesetpixel($img, $e, $i, $col);
            }
        }
      
      imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);

      //optionaler Weichzeichner
      $new_width = 160;
      $new_height = 160;
      $img2 = imagecreatetruecolor($new_width, $new_height);
      imagecopyresampled($img2, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
  
      imagejpeg($img2, '/tmp/map_small.jpg');
      imagepng($img2, '/tmp/map_small.png');
      imagedestroy($img2);
  
      imagejpeg($img, '/tmp/map.jpg');
      imagepng($img, '/tmp/map.png');
      imagedestroy($img);
      echo "Bild erfolgreich konvertiert.";
    }
  else 
    {
      echo "Image erzeugen fehlgeschlagen.";
    }
}

?>