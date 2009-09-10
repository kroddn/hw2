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
<table id="vote" cellpadding="0" cellspacing="1" width="100%" border="0">
<tr class="tblhead" style="height: 18px;">
<td align="center" <? if(!defined("VOTE_VERTICAL")) echo 'colspan="5"'; ?>><a target="_blank" href="http://www.holy-wars2.de/vote.php">Vote for HW2!</a></td>
</tr>
<tr>

<td align="center">
 <a href="http://www.galaxy-news.de/?page=charts&op=vote&game_id=451" target="_blank" title="Vote@Galaxy-News">
  <img src="http://www.galaxy-news.de/images/vote.gif" border="0">
 </a>
</td>

<? if(defined("VOTE_VERTICAL")) echo "</tr><tr>\n"; ?>

<td align="center">
 <a href="http://www.gamingfacts.de/charts.php?was=abstimmen2&spielstimme=349" target="_blank">
  <img src="http://www.holy-wars2.de/gamingfacts_charts.gif" border="0" alt="[ Gamingfacts.de Vote ]">
 </a>
</td>

<? if(defined("VOTE_VERTICAL")) echo "</tr><tr>\n"; ?>

<td align="center">
 <a href="http://www.browsergames24.de/modules.php?name=Web_Links&lid=808&l_op=ratelink" target="_blank">
  <img border="0" src="http://www.browsergames24.de/votebg.gif" alt="Das Infoportal rund um Browsergames">
 </a>
</td>

<? if(defined("VOTE_VERTICAL")) echo "</tr><tr>\n"; ?>

<td align="center">
 <a href="http://bgs.gdynamite.de/charts_vote_293.html" target="_blank">
 <img src="<? echo GFX_PATH_LOCAL; ?>/gd_animbutton.gif" border="0" alt="GDynamite">
 </a>
</td>

<? if(defined("VOTE_VERTICAL")) echo "</tr><tr>\n"; ?>

<td align="center">
<a href="http://www.browserwelten.net/?ac=vote&gameid=334" target="_blank"><img src="http://www.browserwelten.net/img/bw_votebutton.gif" border="0" alt="www.browserwelten.net"></a>
</td>
</tr>
</table>
