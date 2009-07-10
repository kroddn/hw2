<?php
/***************************************************
 * Copyright (c) 2003-2008
 *
 * Stefan Neubert
 * Markus Sinner <kroddn@cmi.gotdns.org>
 *
 * This File must not be used without permission!
 ***************************************************/
class MapVersion3 {

  var $user;
  var $sizex;
  var $sizey;
  var $window_hor;
  var $window_ver;
  var $dbsize;
  var $shift;
  var $actx;
  var $acty;
  var $wantx;
  var $wanty;
  var $fieldsizex;
  var $fieldsizey;
  var $grid;
  var $activeplayer;

  // Konstruktor (zentriert auf Hauptstadt)
  function MapVersion3($sizex, $sizey, $window_hor, $window_ver, $fieldsizex, $fieldsizey, $player) {
    $this->sizex        = $sizex;
    $this->sizey        = $sizey;
    $this->window_hor   = $window_hor;
    $this->window_ver   = $window_ver;
    $this->fieldsizex   = $fieldsizex;
    $this->fieldsizey   = $fieldsizey;
    $this->shift        = ($window_hor-$window_ver)/2;
    $this->dbsize       = min($window_hor,$window_ver)+abs($this->shift);
    $this->actx         = 25;
    $this->acty         = 25;
    $this->grid         = false;
    $this->activeplayer = $player;
  }
  
  
  // zentriert die Karte auf die Hauptstadt des angegebenen Spielers
  function centerOnCapital($ply) {
    $res1=do_mysql_query("SELECT id FROM city WHERE capital=1 AND owner=".$this->activeplayer);
    if (mysql_num_rows($res1)>0) {
      $cityid=mysql_fetch_assoc($res1);
      $res2=do_mysql_query("SELECT x, y FROM map WHERE id=".$cityid['id']);
      $mapxy=mysql_fetch_assoc($res2);
      
      $this->actx=$mapxy['x'];
      $this->acty=$mapxy['y'];
    }
  }
}