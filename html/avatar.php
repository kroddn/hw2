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
* Copyright (c) 2003-2006 by holy-wars2.de
*
* written by franzl, Markus Sinner
*
* This File must not be used without permission	!
***************************************************/

header('Content-Type: image/jpeg'); 

include_once("includes/db.inc.php");
include_once("includes/player.class.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");


function showDummy() {
  $dummy = GFX_PATH_LOCAL."/avatar_dummy.jpg";
 
  if(file_exists($dummy )) {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($dummy)).' GMT'); 
    $f = fopen($dummy, "r");
    if($f) {
      $content = fread($f, filesize($dummy) );
      fclose($f);
      echo $content;
    }
  }
  // On-the-fly Erzeugung
  else {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', 0).' GMT'); 

    $im = @ImageCreate (100, 100);
    $background_color = ImageColorAllocate ($im, 0, 0, 0);
    $text_color = ImageColorAllocate ($im, 255, 255, 255);
    ImageString ($im, 3, 20, 40, "Kein Bild", $text_color);
    imagejpeg($im);
  }
}



function showAvatarAdmin($id) {  
  $upload_dir = AVATAR_DIR; 
  $filename=$upload_dir.$id.".jpg";
  if(is_file($filename)) {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s',  time() ).' GMT' ); 
    $im=@imagecreatefromjpeg($filename);
    $res=do_mysql_query("SELECT avatar FROM player WHERE id='".$id."'");
    $status=do_mysql_fetch_assoc($res);
    if($status['avatar']==1) {
      $color_back = ImageColorAllocate ($im, 255, 255, 255);
      ImageFilledRectangle($im, 0, 78, 100, 100, $color_back); 
      $text_color = ImageColorAllocate ($im, 255, 0, 0);
      ImageString ($im, 3, 35, 77, "Nicht", $text_color);
      ImageString ($im, 3, 15, 86, "Freigegeben", $text_color);
    }
    imagejpeg($im); 
  } 
  else {
    showDummy();
  }
}



function showAvatar($id) { 
  $upload_dir = AVATAR_DIR; 
  $filename=$upload_dir.$id.".jpg";
  if(is_file($filename)) {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s',  filemtime($filename)).' GMT' ); 
  
    $im=@imagecreatefromjpeg($filename);
    $res=do_mysql_query("SELECT avatar FROM player WHERE id='".$id."'");
    $status=do_mysql_fetch_assoc($res);
    if($status['avatar']==2) {
      imagejpeg($im); 
    }
  } 
  else {
    showDummy();
  }
}


// Verarbeite Request
if($_GET['id']) {
  // Namehunter-Version zum Freischalten
  if($_SESSION['player']->isNamehunter()) {
    showAvatarAdmin($_GET['id']);
  }
  // Eigener Avatar  
  else if($_SESSION['player']->getID() == $_GET['id']) {
    showAvatarAdmin($_GET['id']);
  } 
  // Avatar anzeigen
  else {
    showAvatar($_GET['id']);
  }
} 
else {
  // Dummy-Grafik "Will ich auch"
  showDummy();
}
?>