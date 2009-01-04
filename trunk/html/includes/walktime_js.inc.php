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
?>

<script language="JavaScript">
<!--
var cities = new Array();

var army_time_to_prepare = <? echo ARMY_TIME_TO_PREPARE; ?>;
var army_time_per_1000   = <? echo ARMY_TIME_PER_1000; ?>;
var army_max_time        = <? echo ARMY_MAX_TIME; ?>;
var army_speed_factor    = <? echo ARMY_SPEED_FACTOR; ?>;
var this_x               = <? echo $_SESSION['cities']->city_x; ?>;
var this_y               = <? echo $_SESSION['cities']->city_y; ?>;
var unitspeed;
var max_unitspeed = 5;
var unitcount;
var unitspeeds           = new Array();

var speedmatrix          = new Array();
<? 
  foreach($GLOBALS['speedmatrix'] AS $key => $val)
  {
    printf("speedmatrix[%d] = %s;\n", $key, $val);
  }
?>


function getUnitParams() 
{
  var elem;
  i=0;
  unitcount = 0;
  unitspeed = max_unitspeed;
  while(null != (elem = document.getElementById("unit"+i)) )
  {
    count = parseInt(elem.value);
    if(count > 0)
    {
      if(unitspeeds[i])
        unitspeed = Math.min(unitspeed, unitspeeds[i]);
        
      unitcount += count;
    }
    i++;
  }
}

function updateTravelTime() {
  x = parseInt(document.getElementById("coordx").value);
  y = parseInt(document.getElementById("coordy").value);
 
  getUnitParams();
  
  if(isNaN(x) || isNaN(y) || isNaN(unitcount))
  {
    document.getElementById("traveltime").value = "";
    return;
  }
  
  distance = Math.sqrt( (x-this_x)*(x-this_x) + (y-this_y)*(y-this_y)  );
  
  // Vorbereitungszeit
  time = Math.max( army_time_to_prepare, Math.min(Math.round(unitcount * army_time_per_1000 / 1000), army_max_time) );
  
  if(distance > 0) {
    time += Math.ceil ( distance * 3600 * speedmatrix[unitspeed] / army_speed_factor );
  }
  
  hours = Math.floor( time/3600 );
  mins  = Math.floor(time/60) % 60;
  secs  = time % 60;
  
  document.getElementById("traveltime").value = hours+":"+(mins<10 ? "0"+mins : mins)+ ":" + (secs <10 ? "0" + secs : secs);

  if(document.getElementById("armyspeed"))
    document.getElementById("armyspeed").innerHTML = unitspeed; 
}

// -->
</script>
