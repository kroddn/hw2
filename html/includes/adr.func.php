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
 * Copyright (c) 2006
 *
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 ***************************************************/

/**
 * Adressbuch-Funktionen
 */
function adr_add_name($name, $nicename = null) {
  return adr_real_add($name, $nicename);
}

function adr_add_sms($name, $sms) {
  if ($sms == null) return "Ungültiger Wert für SMS";
  return adr_real_add($name, $name, $sms);
}


function adr_real_add($name, $nicename = null, $sms = null) {
  $me = $_SESSION['player']->GetID();

  if (strlen($name) == 0) {
    return "Ungültiger Name";
  }

  
  if ($sms == null) {
    $p = do_mysqli_query_fetch_assoc("SELECT * FROM player WHERE name LIKE '".mysqli_escape_string($GLOBALS['con'], $name)."'");
  }
  else {
    $p_res = do_mysqli_query("SELECT nicename FROM addressbook WHERE owner = ".$me.
                            " AND sms LIKE '".mysqli_escape_string($GLOBALS['con'], $sms)."'");
    if (mysqli_num_rows($p_res)){
      $aname = mysqli_fetch_assoc($p_res);
      $aname = $aname['nicename'];
      return "Diese SMS-Nummer haben Sie bereits in Ihrem Adressbuch ($aname).";
    }
  }

  if($sms == null && $p['id']) {
    $p_res = do_mysqli_query("SELECT * FROM addressbook WHERE owner = ".$me.
                            " AND player = ".$p['id']);
    if (mysqli_num_rows($p_res) > 0) {
      return "Dieser Spieler befindet sich bereits in Eurem Adressbuch.";
    }    
  }
  else if ($sms == null) {
    return "Ein Spieler namens <i>".$name."</i> existiert nicht.";
  }

  $sql = sprintf("INSERT INTO addressbook (owner, player, nicename, sms) VALUES (%d, %s, %s, %s)",
                 $me,
                 $p['id'] ? $p['id'] : "NULL",
                 $nicename == null ? "NULL" : "'".mysqli_escape_string($GLOBALS['con'], $nicename)."'",
                 $sms == null ? "NULL" : "'".mysqli_escape_string($GLOBALS['con'], $sms)."'"
                 );
  do_mysqli_query($sql);
  
  return null;
}


// FIMXE: Muss noch implementiert werden
function adr_edit_entry($id) {
  $id = intval($id);
  if ($id) {
    $me = $_SESSION['player']->GetID();
    $res = do_mysqli_query("SELECT * FROM addressbook WHERE id = $id AND owner = ".$me);
    if (mysqli_num_rows($res) == 1) {
      echo "<h2>Eintrag bearbeiten</h2>";

      echo "<p>";
      return null;
    }
    else {
      return "Dieser Eintrag existiert nicht.";
    }
  }
  else {
    return "Ungültige ID.";
  }
}



function adr_del_entry($id) {
  $id = intval($id);
  if ($id) {
    $me = $_SESSION['player']->GetID();
    do_mysqli_query("DELETE FROM addressbook WHERE id = $id AND owner = ".$me);
    if (mysqli_affected_rows($GLOBALS['con']) == 0)
      return "Dieser Eintrag existiert nicht.";
  }
  else {
    return "Ungültige ID.";
  }
}

/**
 * Formular ausgeben zum Editieren des Adressbuchs
 */
function adr_new_entry_form() {
  global $iname, $ismsnr, $ismsprefix;
?>
<div style="width: 500px;">
<hr>
<center><h2>Neuen SMS-Eintrag anlegen</h2></center>
<form action="edit_adr.php" method="get">
<table width="300">
<tr>
 <td>Bezeichnung</td>
 <td>
  <input type="text" name="iname" <? if(isset($iname)) echo 'value="'.$iname.'"'; ?>>
 </td>
</tr>
<tr>
 <td>SMS</td>
 <td>
  <select name="ismsprefix">
   <option value="">Vorwahl</option>
     <? 
     foreach(get_prefix_array() as $text) {
       $prefix = preg_replace("/^0/", "+49", $text);
       echo '   <option value="'.$prefix.'"';
       if ($prefix == $ismsprefix) echo " selected";
       echo ">$text</option>\n";
     }
     ?>
  </select>
  <input name="ismsnr" <? if(isset($ismsnr)) echo 'value="'.$ismsnr.'"'; ?>
   type="text" maxlength="8" size="9">
 </td>
</tr>
<tr>
 <td colspan="2" align="center">
  <input type="submit" name="addentry" value=" Eintrag nun anlegen ">&nbsp;&nbsp;
  <input type="submit" name="abort" value=" Abbrechen ">
 </td>
</tr>
</table>
</form>
<p><center>
Hinweis: einen <b>Spieler</b> können Sie ganz einfach über die <a href="townhall.php">Spielerinfo/Spielersuche (hier klicken)</a> hinzuf�gen.
</center>
<hr>
</div>
<?
}


/**
 * Adressbuch des aktuellen Spielers ausgeben.
 *
 */
function print_address_book() {
  $me = $_SESSION['player']->GetID();

  $res = do_mysqli_query("SELECT p.name AS nick, coalesce(a.nicename, p.name) AS nicename, ".
                        "   a.id AS id, a.player AS pid, a.sms AS sms, ".
                        " p.sms IS NOT NULL AS ownsms ".
                        " FROM addressbook a ".
                        "  LEFT JOIN player p ON p.id = a.player ".
                        " WHERE owner = $me ORDER BY nicename");
?>
    <table id="adr" width="500" cellspacing="0" cellpadding="0" >
<?php   
  if (mysqli_num_rows($res) == 0) {
    echo "\n<tr><td colstan=\"4\" align=\"center\"><b>Ihr Adressbuch enthält keine Einträge</b></td></tr>\n";
  }
  else {   
    echo '<tr><td height="40" colspan="4" align="center" valign="middle">';
    print_add_entry_buttons();
    echo "</td></tr>\n";
    
    echo "\n<tr><th>&nbsp;</th><th>Bezeichnung</th><th>Spielername</th><th>SMS-Nummer</th></tr>\n";
    
    // Einträge ausgeben
    $i=0;
    while( $a = mysqli_fetch_assoc($res) ) {
      $nicename = $a['nicename'] ;
      $nick     = $a['nick'] ? $a['nick'] : "-";

      if ($a['sms']) {
        $smsrec =  $a['sms'];
        $sms =  $a['sms'];
      }
      else {
        if( $a['ownsms'] ) {
          $smsrec = "hat Nummer hinterlegt";
          $sms = $nick;
        }
        else {
          $sms = null;
        }
      }

      echo '<tr class="line'.($i++ % 2).'"><td>';

      echo '<a title="Eintrag löschen" '.
        ' onClick="return confirm(\'Eintrag -'.$nicename.'- löschen?\')"'.
        ' href="edit_adr.php?del='.$a['id'].'">L</a>&nbsp;';
      echo '<a title="Eintrag bearbeiten" '.
        ' href="edit_adr.php?edit='.$a['id'].'">B</a>';

      echo "</td>\n";

      echo "<td>$nicename</td><td>$nick</td>";
      echo "<td>".($sms == null ? "-" : '<a href="sms.php?recipient='.urlencode($sms).'">'.$smsrec.'</a>')."</td>";
      echo "</tr>\n";
      
    }

  }
  ?>
   <tr><td height="40" colspan="4" align="center" valign="middle">
      <? print_add_entry_buttons(); ?>
   </td></tr>
   <tr><td colspan="4">
    Ein Eintrag im Adressbuch kann an mehreren Stellen im Spiel verwendet werden.<p>
    <ul>
      <li>
      Das Adressbuch kann für SMS-Nummern oder für Spielernamen verwendet werden.
      <li>
      In der ersten Spalte der Tabelle befinden Buttons zum Löschen und Bearbeiten
      der Einträge.
      <li>
      Durch die Spalte <b>Bezeichnung</b> können Sie einem Eintrag einen individuellen Namen
      geben. Wenn Sie einen Spieler ins Adressbuch eintragen, dann steht bei Bezeichnung
      normalerweise der Name des Spielers. Sie können den Eintrag jedoch umbenennen, um
      z.B. den echten Namen dieser Person einzutragen 
      (z.B. &quot;Max Mustermann&quot; als Bezeichnung für eine Person, die im Spiel den
       Namen &quot;Herr Max der Erste&quot; trägt).
      <li>
      Die Spalte <b>Spielername</b> gibt den Namen des Spielers ein, für den Sie diesen
      Eintrag angelegt haben. Hier steht in diesem Fall der Name des Spielers.
      <li>
      Falls in der Spalte <b>SMS-Nummer</b> eine Nummer eingetragen ist, dann wird dieser
      Eintrag im Adressbuch auf der SMS-Versandseite angezeigt. Ebenso werden Spieler angezeigt,
      die selbst eine SMS-Nummer hinterlegt haben und in Ihrem Adressbuch vorhanden sind. 
      In solchen Füllen erschein <b>hat Nummer hinterlegt</b> anstelle der SMS-Nummer.
    </ul>
   </td></tr>                                   
 </table>

<?
}
function print_add_entry_buttons() {
?>
 <form action="edit_adr.php" method="get">
    <input type="hidden" name="addnew" value="1">
    <input type="submit" name="addentry" value=" Eintrag anlegen ">
    <input type="submit" name="addentry" value=" SMS-Eintrag anlegen ">
 </form>
<? 
} // print_add_entry_buttons() 
?>