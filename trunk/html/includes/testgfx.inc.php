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
<html><body marginwidth="4" marginheight="4" topmargin="4" leftmargin="4" bgcolor="#F0F0AA">
<? 
$gfx_path_test = Player::NormalizeGfxPath($gfx_path);
if($gfx_path_test == null) $gfx_path_test = defined("GFX_PATH") ? GFX_PATH : "images/ingame";
?>
 Teste Grafikpfad: &lt;<i><? echo $gfx_path_test;?>&gt;</i><p>Wenn Sie hier zwei Städte erkennen können, dann ist Ihr gewählter Grafikpfad in Ordnung. <b>Klicken Sie dann auf eine der Städte</b> um die Änderungen zu übernehmen.<p>
 <img onClick="opener.document.theGFXForm.submit(); window.close()" alt="Fehler1" src="<?php echo $gfx_path_test;?>/99999_1_8.gif"/>&nbsp;
 <img onClick="opener.document.theGFXForm.submit(); window.close()" alt="Fehler2" src="<?php echo $gfx_path_test;?>/99999_2_8.gif"/>
<hr style="margin-bottom: 1px; margin-top: 1px;">
<h3 style="margin-bottom: 2px; margin-top: 2px;">Probleme mit FireFox ab 1.5</h3>
Bei den neueren FireFox Versionen sind neue Sicherheitsvorkehrungen eingebaut worden. Eine Anleitung,
wie man den Grafikpfad freigibt, ist unter 
<a target="_blank" href="http://www.firefox-browser.de/wiki/Lokale_Bilder">
http://www.firefox-browser.de/wiki/Lokale_Bilder</a>
zu finden. Dort einfach <code><? echo URL_BASE; ?></code> als Server eintragen.
<h3 style="margin-bottom: 2px; margin-top: 2px;">Sonstige Probleme?</h3>
Sollte an dieser Stelle kein Bild erscheinen, kann dies auch mit einem Problem Ihres Browsers zu tun haben.
    Bei neueren Mozilla oder Firefox basierten Browsern muss zunächst eine Einstellung geändert werden:<p>
 <ul>
  <li><a href="about:blank" target="_new">hier klicken</a> und <i>about:config</i> in die Adressleiste tippen
  <li>In die Zeile <i>Filter</i> &quot;checkl&quot; eingeben (der letzte Buchstabe ist ein kleines L)
  <li>auf den Wert "true" gehen, rechte Maustaste klicken und "umschalten" wählen. Der Wert ändert sich auf "false"
  <li>Dann <a href="javascript:window.location.reload()">hier klicken</a>, um erneut den Grafikpfad zu testen
</ul>

</body></html>