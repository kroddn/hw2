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
 * Copyright (c) 2003
 *
 * Stefan Neubert, Stefan Hasenstab
 *
 * This File must not be used without permission   
 *
 * log.inc.php
 *
 * This file contains logging-functions
 ***************************************************/

if(!function_exists("show_fatal_error")) {
  function show_fatal_error($string) {
    global $HTTP_SERVER_VARS, $csspath, $imagepath;
    if ($HTTP_SERVER_VARS['HTTP_REFERER'] == 'http://www.holy-wars.info/holywars/navigation.php')
      $goback = 'habt &uuml;berhaupt keine Idee was ihr tun sollt';
    else
      $goback = 'entscheidet, das ihr <u><a href="'.$HTTP_SERVER_VARS['HTTP_REFERER'].'">zur&uuml;ck</a></u> wollt.</i></b>';
    echo "<html>";
    echo "<head>";
    echo "<title>Fehler</title>";
    echo "</head>";
    //TODO: get $csspath and $imagepath from player
    echo '<link rel="stylesheet" href="/images/ingame/css/hw.css" type="text/css">';
    echo '<body marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" background="/images/ingame/bg.gif">';
    echo '<b class="error"><i>Ihr wacht auf, reibt euch den Kopf und versucht euch zu erinnern, was passiert ist. Da hört ihr eine Stimme in eurem Kopf: </i>"Ihr wurdet soeben Zeugen eines aussergew&ouml;hnlichen Ereignisses, wendet euch mit folgenem Code an die Admins: <p>\''.$string.'\'."<p><i>Ihr reibt euch die Augen und '.$goback;
    echo "</body>";
    echo "</html>";
    if(defined("DEBUG_SERVICE")) {
        debug_print_backtrace();
    }
    
    die();
  }
 }

if (!function_exists("show_log_fatal_error")) {
  function show_log_fatal_error($logstr, $showstr=null) {    
    global $HTTP_SERVER_VARS;
    if ($showstr == null) {
      $showstr = $logstr;
    }

    do_mysqli_query("INSERT INTO log_err(errstr,time,referer) VALUES ('".mysqli_escape_string($GLOBALS['con'], $logstr)."',UNIX_TIMESTAMP(),'".mysqli_escape_string($GLOBALS['con'], $HTTP_SERVER_VARS['HTTP_REFERER'])."')");
    $errid = mysqli_insert_id($GLOBALS['con']);
    $showstr .= ":".$errid;
    show_fatal_error($showstr);    
  }
 }

if (!function_exists("log_fatal_error")) {
  function log_fatal_error($logstr) {
    global $HTTP_SERVER_VARS;
    do_mysqli_query("INSERT INTO log_err(errstr,time,referer) VALUES ('".mysqli_escape_string($GLOBALS['con'], $logstr)."',UNIX_TIMESTAMP(),'".mysqli_escape_string($GLOBALS['con'], $HTTP_SERVER_VARS['HTTP_REFERER'])."')");
  }
 }

if (!function_exists("show_error")) {
  function show_error($error) {
    echo '<b class="error">'.$error.'</b><br/>';
  }
}
if (!function_exists("show_no_error")) {
  function show_no_error($string) {
    echo '<b class="noerror">'.$string.'</b><br/>';
  }
}
?>
