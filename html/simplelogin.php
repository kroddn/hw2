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
 * Diese Datei soll demonstrieren, wie ein Login funktioniert.
 * Es wird nichts getan ausser Login und Passwort abgefragt.
 * 
 * Die Unterstützung eines Security Codes ist sinnvoll, in dem Fall
 * einfach ein Captcha einbinden und $_SESSION['sec_key'] setzen.
 * In dieser Demo ist $_SESSION['sec_key'] einfach auf 12345 gesetzt.
 */
define("FORCE_ENABLE_SECURITY", 1);

session_start();
$_SESSION['sec_key'] = 12345;

if(isset($login_submit)) {
    include("includes/login.func.php");
    $loginerror = hw2_login($loginname, $loginpassword, $sec_code);
}


?>
<html>
<head>
<title>Holy-Wars 2 Simple Login</title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 <meta http-equiv="imagetoolbar" content="no">
</head>
<body>
<h1>Simple Login</h1>
<?php if($loginerror != null) echo '<h2 style="color: DD0000;">Fehler: '.$loginerror."</h2>"; ?>

<form method="POST" name="login">
Name: <input type="text" name="loginname"><br>
Passwort: <input type="password" name="loginpassword"><br>
Seccode:  <input type="text" name="sec_code"><br>
<input type="submit" name="login_submit">
</form>

Als Security Code <?php  echo $_SESSION['sec_key']; ?> eingeben. 

</body></html>
