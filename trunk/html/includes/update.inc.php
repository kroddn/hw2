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
* Copyright (c) 2005-2007 by Holy-Wars2.de
*
* Markus Sinner
* Gordon Meiser
*
* This File must not be used without permission	!
***************************************************/
include_once("includes/session.inc.php");

$res1=do_mysql_query("SELECT cc_messages, cc_resources, cc_towns, holiday, coalesce(holiday, 0) > UNIX_TIMESTAMP() AS holidaymode, status ".
                     " FROM player WHERE id=".$_SESSION['player']->getID());


if ($cc=mysql_fetch_assoc($res1)) {
  if ($cc['cc_towns']==1) {
    $_SESSION['cities']->updateCities();
    echo "<script type='text/javascript'>\n<!--\n";
    echo "if(parent != self) parent.borderbottom.location.href='borderbottom.php';\n";
    echo "//-->\n</script>\n";
    $cc['cc_towns']=0;
  }
  if ($cc['cc_messages']==1) {
    include_once("includes/research.class.php");
    include_once("includes/player.class.php");
    $_SESSION['research']->update();
    $_SESSION['player']->updateNewMessages();
    echo "<script type='text/javascript'>\n<!--\n";
    echo "if(parent != null && parent != self) parent.borderbottom.location.href='borderbottom.php';\n";
    echo "//-->\n</script>\n";
    $cc['cc_messages']=0;

  }
  if ($cc['cc_resources']==1) {
    include_once("includes/player.class.php");
    $_SESSION['player']->updateResources();
    echo "<script type='text/javascript'>\n<!--\n";
    echo "if(parent != null && parent != self) parent.res.location.href='top.php';\n";
    echo "//-->\n</script>\n";
    echo "<script type='text/javascript'>\n<!--\n";
    echo "if(parent != null && parent != self) parent.borderbottom.location.href='borderbottom.php';\n";
    echo "//-->\n</script>\n";
    $cc['cc_resources']=0;
  }
  if ($cc['holiday'] && $cc['holidaymode']) {
    session_destroy();
    $GLOBALS['error'] = "holiday";
    goto_login();
  }
  if( $cc['status'] == 2 ) {
    session_destroy();
    $GLOBALS['error'] = "locked";
    goto_login();
  }
  
  do_mysql_query("UPDATE player SET cc_resources=".$cc['cc_resources'].", cc_messages=".$cc['cc_messages'].", cc_towns=".$cc['cc_towns']." WHERE id=".$_SESSION['player']->getID());
}


// Bei nem Angriff die Topleiste aktualisieren
// FIMXE: das wird JEDESMAL gemacht... muss das sein?
$cit = $_SESSION['cities']->getCities();
if ($cit) {
  foreach ($cit as $key=>$city) {
    if ($_SESSION['cities']->underAttack($city['id'])) {
      $underattack = true;
      if (!isset($session_underattack) || !$session_underattack ) {
        $session_underattack = true;
        session_register("session_underattack");
        echo "<script type='text/javascript'>\n<!--\n";
        echo "if(parent != null && parent != self) parent.res.location.href='top.php';\n";
        echo "//-->\n</script>\n";     
      }
      
      continue;
    }
  }
}
?>
