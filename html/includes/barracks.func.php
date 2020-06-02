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


/**
 * Print a table to disarm units.
 * @return unknown_type
 */
function barracks_disarm_table($from)
{
  $sql = "SELECT unit.id AS id,unit.name AS name,city.name AS cname, unit.cost as cost,count,type,unit.level AS level,unit.religion AS religion ".
         " FROM cityunit,unit,city ".
         " WHERE unit.id=cityunit.unit AND city.id=cityunit.city AND cityunit.city=".intval($from)." AND cityunit.owner=".$_SESSION['player']->getID().
         " ORDER BY cityunit.unit";
  
  echo "\n<!-- $sql -->\n";
  
  $cityunit_res = do_mysql_query($sql);
?>                       
<table cellspacing="1" cellpadding="0" border="0" style="width: 660px">
  <tr><td colspan="4" class="tblhead"><strong>Einheiten entlassen</strong></td></tr>
<?php
  // Einheiten entlassen
  if (mysqli_num_rows($cityunit_res) > 0) {
    echo "<form action=".$GLOBALS['PHP_SELF'].' method="POST">';
    echo '<input type="hidden" name="from" value="'.$from.'">';
    echo '<tr class="tblhead">';
    echo '<td width="150">Typ</td>';
    echo '<td width="70" style="text-align:center;">Kosten / Tick</td>';
    echo '<td width="40">&nbsp;</td>';
    echo '<td width="40">Stationiert</td>';
    echo '</tr>';
    $sumcount = 0;
    $sumcost = 0;
    while ($data1 = mysqli_fetch_assoc($cityunit_res)) {
      echo "<tr class='tblbody'>\n";
      $href = getUnitLibLink($data1);
      $img  = getUnitImage($data1);
      printf("<td><a href=\"%s\"><img src=\"%s/%s\" border=\"0\"> %s</a></td>", $href, $GLOBALS['imagepath'], $img, $data1['name']);
      echo "<td style=\"text-align:right; padding-right:15px;\">".number_format(($data1['cost']*$data1['count']),2,",",".")."</td>\n";
      echo "<td><input type='text' name='unit[".$data1['id']."]' size='8'></td><td style=\"text-align:right; padding-right:15px;\">".$data1['count']."</td>\n";
      echo "</tr>\n";
      $sumcount += $data1['count'];
      $sumcost += ($data1['cost']*$data1['count']);
    }
    echo "<tr class='tblhead'><td></td><td style=\"font-weight:bold; text-align:right; padding-right:15px;\">".number_format($sumcost,2,",",".")."</td><td></td><td style=\"font-weight:bold; text-align:right; padding-right:15px;\">".$sumcount."</td></tr>";
    echo '<tr class="tblbody"><td colspan="4" style="text-align:center;"><input style="margin-bottom:5px; margin-top:5px;" type="submit" name="disarm" value="Truppen entlassen">';
    echo "</td></tr>";
    echo "</form>";
  }
  else {
    echo "<tr class=\"tblbody\"><td colspan=\"4\" style=\"text-align:center;\"><br /><strong style=\"color:red\">In dieser Stadt sind keine Einheiten stationiert!</strong><br /><br /></td></tr>";
  }
  ?>
</table>

<?php
} // barracks_disarm_table()
?>