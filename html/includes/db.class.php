<?php
/*************************************************************************
    This file is part of "Holy-Wars 2", taken from "Strength and Honor Game"
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2009 
    by Markus Sinner

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


//include_once ("include/constants.inc.php");

define("DEBUG_DB", true);
define("DEBUG_SQL", false);
define("DB_TYPE", "mysql");


class DB {
  // Result
  var $res;

  // last error, if DB debugs
  var $last_error;

  // Datenbankenvariablen
  var $dbport, $dbname, $dbpass, $dbuser, $dbhost;

  // The type of the DB. Currently "pg"
  // FIXME: implement "mysql"
  var $type;

  // print errors to player?
  var $verbose;
  
  /* Constructor */
  function  DB ($dbtype) {
    switch ($dbtype) {
    case "mysql":
      $this->type ="mysql";
      $this->last_error = null;
      $this->verbose = false;
      break;
	
    default:
      $this->type ="unknown";
      die ("Interner Fehler: unbekannter Datenbankentyp".__FILE__);
    } // switch
  }


  // Execute query and fetch result
  function query_fetch_array($sql) {
    $res =  $this->query($sql);
    
    if($res) {
      return $this->fetch_array($res);    
    }
    else {
      return null;
    }
  }


  // Execute query and fetch result
  function query_fetch_assoc($sql) {
    $res =  $this->query($sql);
    
    if($res) {
      return $this->fetch_assoc($res);    
    }
    else {
      return null;
    }
  }


  // Execute query and fetch result
  function query_fetch_object($sql) {
    $res =  $this->query($sql);

    if($res) {
      return $this->fetch_object($res);    
    }
    else {
      return null;
    }
  }



  // Currently select is just execute ;-)
  function query($sql) {
    if (DEBUG_SQL)
      echo $sql."<br>";
    

    $res = mysqli_query ($GLOBALS['con'], $sql);
    $this->last_error = null;
    if (! $res) {
      $this->last_error = mysqli_error($GLOBALS['con']);
    }

    return $res;
  }
  
  function random_function_name() {
    switch ($this->type) {
    case "pg":
      return "random()";
    case "mysql":
      return "RAND()";
    default:
      return "random()";
    }
  }

  /* fetch an array
   *
   */
  function fetch_array ($res) {
    return mysqli_fetch_array ($res);
  }

  function fetch_assoc ($res) {
    return mysqli_fetch_assoc ($res);
  }

  function fetch_object ($res) {
    return mysqli_fetch_object ($res);
  }

  function affected_rows ($res) {
    if (!$res) return null;
    return mysqli_affected_rows ($res);
  }


  function num_rows ($res) {
    if (!$res) return null;
    return mysqli_num_rows ($res);
  }

  function get_db_type() {
    return $this->type;
  }
}
?>