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
* Copyright (c) 2003-2004 by holy-wars2.de
*
* written by Stefan Neubert, Markus Sinner
*
***************************************************/

// Diese Seite nicht zu den Klicks hinzuzählen
$GLOBALS['noclickcount'] = 1;

include_once("includes/db.inc.php");
include_once("includes/session.inc.php");
include_once("includes/cities.class.php");
?>
<html>
<head>

<style type="text/css">
  /* body { -moz-binding: url("js/IEemu.xml#IEemu"); } */
  a.premiumstatusline { color: #FF4020; }
  a.haspremiumstatusline { color: #20DD20; }  

  div.statusline {
    width: calc(100vw - 155px); 
    background-image: url(images/ingame/top.gif);
  }

  .statusline_left > span {
    float: left;
    margin: 5px 0 0 5px;
    vertical-align: middle;
  }

  .statusline_right {
    float: right;
    margin: 4px 5px 0 0;
    position: absolute;
    right: 0px;
  }

  .statusline_right > span {
    margin-right: 5px;
  }

  .statusline_right > span > b {
    vertical-align: top;
    margin-top: 1px;
  }

  .status_troops {
    margin-top: 2px;
  }

</style>

<script language="javascript">
<!--
function updateframe(newpage) {
	window.location.href = "top.php";
}
-->
</script>
</head>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body class="nopadding" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="<? echo $imagepath; ?>/top.gif">
<div class="statusline">
<div class="statusline_left">
<?

$cit = $_SESSION['cities']->getCities();
$underattack = false;
$attnum = 0;

if(ADVANCED_TOP)
{
  $statusarmys = $_SESSION['cities']->getArmyTopView();
  if ($statusarmys[0] > 0) {
    echo '<span class="statusline status_troops" title="Achtung: Feind im Anmarsch!"><a target="main" href="general.php?enemy=1"><img border="0" src="'.$imagepath.'/attack.gif"></a></span><span><font color="orange">'.$statusarmys[0].'</font></span>';
  }
  if ($statusarmys[1] > 0) {
    echo '<span class="statusline status_troops" title="Achtung: Es sind neutrale Truppen auf dem Weg zu uns!"><a target="main" href="general.php?enemy=1"><img border="0" src="'.$imagepath.'/neutral.gif"></a></span><span><font color="green">'.$statusarmys[1].'</font></span>';
  }
  if ($statusarmys[2] > 0) {
    echo '<span class="statusline status_troops" title="Hinweis: Verbündete Truppen erreichen uns bald."><a target="main" href="general.php?enemy=1"><img border="0" src="'.$imagepath.'/help.gif"></a></span><span><font color="grey">'.$statusarmys[2].'</font></span>';
  }
  else {
    $session_underattack = false;
  }
}
else
{
  if ($cit) {
    foreach ($cit as $key=>$city) {
      $attnum += $_SESSION['cities']->underAttack($city['id']);
    }
  }
  // Falls ein Angriff stattfindet dann dies anzeigen. Ansonsten ein marker für später setzen
  if ($attnum > 0) {
    echo '<span class="statusline status_troops" title="Achtung: Angriff!"><a target="main" href="general.php?enemy=1"><img border="0" src="'.$imagepath.'/attack.gif"></a></span><span><font color="orange">'.$attnum.'</font></span>';
  }
  else {
    // Diese Variable wird von update.inc.php verarbeitet und ist gesetzt
    // wenn der spieler schonmal angegriffen wurde in dieser Sitzung
    $session_underattack = false;
  }
}



function getNewClanfTopics($pid) {
  // Deactivate for the moment
  return "";
	$string="<a href=\"cf_index.php\" target=\"main\" onclick=\"updateframe();\" class=\"statusline\">Keine neuen Forenbeitr&auml;ge</a>";
	//get players last forum visit
	$res2=mysqli_query($GLOBALS['con'], "SELECT user_lastvisit FROM clanf_users WHERE username='".$pid."'");
        if ($res2) {
          $data2=do_mysql_fetch_assoc($res2);
          $lastseen=date("d.m.Y H:i",$data2['user_lastvisit']);
        }
        else {
          $lastseen="never";
        }

	//get cat_id of players clan (put it in session next review)
	$res1=mysqli_query($GLOBALS['con'], "SELECT cat_id FROM clanf_categories WHERE cat_order = '".$_SESSION['player']->clan."'");
        if ($res1) {
          $data1=do_mysql_fetch_assoc($res1);
          $clan_cat=$data1['cat_id'];
        }

	$res3=mysqli_query($GLOBALS['con'], "SELECT clanf_posts.post_time as lastpost FROM `clanf_posts` LEFT JOIN clanf_forums ON clanf_forums.forum_id = clanf_posts.forum_id WHERE clanf_forums.cat_id='".$clan_cat."' AND clanf_posts.post_time > '".$data2['user_lastvisit']."'");
        if ($res3) {
          $data3=do_mysql_fetch_assoc($res3);
          if(mysqli_num_rows($res3)>0)
            $string="<a href=\"cf_index.php\" onclick=\"updateframe();\" target=\"main\" class=\"statusline\">Neue Forenbeitr&auml;ge</a>";
        }
        return $string;
}

$player = $_SESSION['player'];
if($player->isPremSeller())
     echo '<span class="statusline" title="PremiumSeller"><a target="main" href="premiumadmin.php"><img alt="PS" border="0" src="'.$imagepath.'/ps.gif"></a></span>';
if($player->isAdmin() || $player->isMaintainer())
     echo '<span class="statusline" title="Admin"><a target="main" href="adminindex.php"><img alt="AD" border="0" src="'.$imagepath.'/ad2.gif"></a></span>';
if($player->isMultihunter())
     echo '<span class="statusline" title="Multihunter"><a target="main" href="multihunter.php?sh_playerinfo=1"><img alt="MH" border="0" src="'.$imagepath.'/mh.gif"></a></span>';

if($player->isNamehunter())
     echo '<span class="statusline" title="Namehunter"><a target="main" href="namehunter.php"><img alt="NH" border="0" src="'.$imagepath.'/nh.gif"></a></span>';

if( defined("SMS_SERVICE") && SMS_SERVICE && (!defined("HISPEED") || !HISPEED) ) {
  echo '<span class="statusline" title="SMS Senden"><a target="main" href="sms.php"><img border="0" alt="SMS Versand" src="'.GFX_PATH_LOCAL.'/sms_mini.gif"></a></span>';
} 
?>
    <span title="Punktestand_Bezeichnung" class="statusline"><b>Punkte:&nbsp</b></span>
    <span title="Punktestand" class="statusline"><? echo $player->getPoints();?></span>
    <span title="Forum" class="statusline"> - <? echo getNewClanfTopics($_SESSION['player']->name); ?></span>
    <span> - 
      <? if( is_premium() ) {
        echo '<a title="Premium-Account Einstellungen" class="haspremiumstatusline" target="main" href="settings.php?show=premium">Premium!</a>';
      }
      else {
        $sql = "SELECT count(*) AS cnt FROM premiumacc WHERE player = ".$_SESSION['player']->getID();
        $cnt = do_mysql_query_fetch_assoc($sql);
        
        $cantest = $cnt['cnt'] == 0 ? " Kostenlos testen!" : "";
        
        echo '<a title="Vorteile eines Premiumaccounts ansehen" class="premiumstatusline" target="main" href="premium.php">Premium?'.$cantest.'</a>';
      }
      ?>
    </span>
  </div>
  <div class="statusline_right"></td>
    <span title="Gold" class="statusline"><b><? echo prettyNumber($player->getGold());?></b> <img alt="Gold" id="gold" name="Gold" src="<? echo $imagepath; ?>/gold.gif"></span>
    <span title="Holz" class="statusline"><b><? echo prettyNumber($player->getWood());?></b> <img alt="Holz" name="Holz" src="<? echo $imagepath; ?>/wood.gif"></span>
    <span title="Eisen" class="statusline"><b><? echo prettyNumber($player->getIron());?></b> <img alt="Eisen" name="Eisen" src="<? echo $imagepath; ?>/iron.gif"></span>
    <span title="Stein" class="statusline"><b><? echo prettyNumber($player->getStone());?></b> <img alt="Stein" name="Stein" src="<? echo $imagepath; ?>/stone.gif"></span>
  </div>
</div>
</body>
</html>