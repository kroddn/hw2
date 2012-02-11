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
 **************************************************************************/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<title>Holy-Wars 2 - Online-Strategie im Mittelalter</title>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
<META NAME="author" CONTENT="Markus Sinner">
<META NAME="publisher" CONTENT="Holy-wars2.de">
<META NAME="copyright" CONTENT="Holy-wars2.de">
<META NAME="page-type" CONTENT="Private Homepage">
<META NAME="audience" CONTENT="Alle">
<META NAME="description" CONTENT="Wir schreiben tiefstes Mittelalter. Papst Urban II. hat zu den Kreuzz&uuml;gen aufgerufen und der heilige Krieg beginnt. W&auml;hlet zwischen dem christlichen oder dem islamischen Glauben und &uuml;bernehmet die Herrschaft &uuml;ber Euer eigenes Reich. Gebietet Ihr am Anfang nur &uuml;ber ein einfaches Dorf, werdet Ihr mit der Zeit ein gewaltiges Reich Euer Eigen nennen. Gr&uuml;ndet neue St&auml;dte oder erobert bereits bestehende. Seht Eure D&ouml;rfer zu prunkvollen St&auml;dten heranwachsen und gewaltige Heere werden auf Euer Wort h&ouml;ren. F&uuml;hret die Einwohner zum rechten Glauben und zerschmettert die Ungl&auml;ubigen. Doch Ihr seid nicht alleine. Erbittet Aufnahme in einem der m&auml;chtigen Orden Eures Glaubens und erklimmt die Hierarchie! Oder gr&uuml;ndet Euren eigenen Orden und f&uuml;hret ihn zu Macht, Ruhm und Ansehen. Es liegt allein in Eurer Hand....">
<meta name="keywords" content="holy,wars,thousand,spiel,everquest,Camelot,artikel,Knight,verkauf,starcraft,computec,jpg,baldurs gate,action games,golf,Multiplayer,mmog,Spielhalle,debian,atx,strategy games,MMORPG,massively multiplayer,pcs,Ultima,community,final fantasy,MMPG,browsergame,browser,game,datenverarbeitung,world-of-warcraft,wow,Warhammer,warcraft,online,Lineage2,Mythic,celeron,kostenlos">
<meta name="robots" content="index, follow">
<meta name="revisit-after" content="7 days">
<META NAME="page-topic" CONTENT="Spiele">

<LINK rel="SHORTCUT ICON" href="favicon.ico">

<style type="text/css">
body {
 margin-top: 10px;
 background: black;
}
form {
 padding: 0px;
 margin: 0px;
}
img {
 border:  0px;
}

span.fat_u {
  font-weight: bold;
  text-decoration: underline;
}

#root {
 position: relative;
 text-align: center;
 vertical-align: middle;
}

#main {
 position: relative;
 width: 800px;
 height: 570px;
 background-image: url(castle_bg.jpg);
}

#game1 {
 position: absolute;
 top:  275px;
 left: 350px;
 z-index: 2;
 border:0;
}

#game2 {
 position: absolute;
 top: 275px;
 left: 490px;
 z-index: 2;
 border: 0;
}

#speed {
 position: absolute;
 top: 420px;
 left: 405px;
 z-index: 2;
 border: 0;
}

#menu1 {
 position: absolute;
 top: 490px;
 left: 210px;
 z-index: 2;
 border: 0;
}

#menu2 {
 position: absolute;
 top: 490px;
 left: 580px;
 z-index: 2;
 border: 0;
}

#vote {
 padding: 4px;
 font-family: Tahoma;
 font-size: 11px;
 text-align: center;
 background: #404040;
 color: white;
 position: absolute;
 top: 330px;
 left: 6px;
 z-index: 2;
 border: 0;
 width: 106px;
 height: 220px;
}
#vote br {
 padding: 0px;
 margin: 0px 1px 0px 0px;
}

#links {
 padding: 1px;
 font-family: Tahoma;
 font-size: 11px;
 line-height: 16px;
 text-align: center;
 background: #404040;
 color: white;
 position: absolute;
 top: 120px;
 left: 655px;
 z-index: 2;
 border: 0;
 width:  132px;
 height: 145px;
}
#links a { color: white; }
#links a:hover { color: #CCCCCC; font-weight: bold; }
#links br {
 padding: 0px;
 margin: 0px 2px 0px 0px;
}
#links img {
 margin-top: 3px;
}

#icons {
 position: absolute;
 top: 490px;
 left: 700px;
 z-index: 2;
 border: 0;
 width: 106px;
 height: 85px;
}

#icons p {
 margin: 4px 0px 0px 0px;
}

#headline {
 color: white;
 font-size: 14px;
 line-height: 16px;
 text-align: left;

 position: absolute;
 top: 112px;
 left: 10px;
 z-index: 2;
 border: 0;
}

#shortnews {
 color: white;
 font-size: 14px;
 line-height: 16px;
 text-align: left;

 position: absolute;
 top: 132px;
 left: 10px;
 z-index: 2;
 border: 0;
}

#shortnews a {
 color: #909090;
}
#shortnews a:hover {
 color: #CBCBCB;
}



#tooltip {
 color: #EEEED0;
 font-size: 14px;
 line-height: 16px;
 text-align: left;

 position: absolute;
 top: 170px;
 left: 10px;
 z-index: 2;
 border: 0;

 width:  260px;
 height: 220px;
}
#tooltip td {
 color: #EEEED0;
 font-size: 14px;
 line-height: 16px;
 /* padding-right: 4px; */
 padding: 0px 4px 0px 0px;
}


#framelayer {
 padding: 2px;
 border: 1px solid white;
 position: absolute;

 top: 110px;
 left: 128px;
 width: 660px;
 height: 350px;
 z-index: 2;
}

#framelayerclose {
 padding: 1px;
 position: absolute;
 border: 1px solid white;
 color: white;

 top:  0px;
 left: 0px;
 width: 16px;
 height: 16px;
 z-index: 4;
}


#layeriframe {
  border: none;
  background: transparent;
  position: absolute;
  top:  0px;
  left: 0px;
  z-index: 3;
  width: 100%;
  height: 100%;
}

</style>

<script type="text/javascript" src="portal_js.php">
</script>

</HEAD>

<BODY id="root">

<DIV ALIGN="CENTER">
  <DIV id="main">
    <DIV id="game1">
      <A TITLE="Klicken Sie hier, um auf game1 zu gelangen." 
       HREF="http://game1.holy-wars2.de"
      >
        <IMG SRC="s1_01.gif" alt="Logo game1" 
         onMouseout="chgfx(this, 's1_01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 's1_02.gif'); tooltip(1);"
        >
<!--  -->
      </A>
    </DIV>

    <DIV id="game2">
      <A TITLE="Klicken Sie hier, um auf game2 zu gelangen." 
       HREF="http://game2.holy-wars2.de/portal.php"
       onClick="alert('Diese Runde game2 wurde am 14.11.2008 deaktiviert. Nutzt game1 zum Spielen!'); return false;"
      >
        <IMG SRC="s2_01_gray.gif" alt="Logo game2"
         onMouseout="chgfx(this, 's2_01_gray.gif'); tooltip(0);"
         onMouseover="chgfx(this, 's2_01_gray.gif'); tooltip(22);"
        >
<!--         onMouseover="chgfx(this, 's2_02.gif'); tooltip(2);" -->
      </A>
    </DIV>

    <DIV id="speed">
      <A TITLE="Klicken Sie hier, um auf Speed zu gelangen."
       HREF="http://speed.holy-wars2.de/portal.php"
      >
        <IMG SRC="speed_01.gif" alt="Logo der Speed"
         onMouseout="chgfx(this, 'speed_01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 'speed_02.gif'); tooltip(3);"
        >
      </A>
    </DIV>

    <DIV id="menu1">
      <a target="_blank" href="http://forum.holy-wars2.de/">
        <img src="forum01.gif" alt="Bild Forum"
         onMouseout="chgfx(this, 'forum01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 'forum02.gif'); tooltip('Nehmt an Diskussionen zum Spiel teil und lest Verlautbarungen der Spielführung.');"
        >
      </a><br>
      <a target="_blank" href="http://www.holy-wars2.de/cgi-bin/cgiirc/irc.cgi"
onClick="return framelayer('http://www.holy-wars2.de/cgi-bin/cgiirc/irc.cgi')">
        <img src="chat01.gif" alt="Bild Chat"
         onMouseout="chgfx(this, 'chat01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 'chat02.gif'); tooltip('Sprecht mit den Verantwortlichen und trefft andere Spieler im <b>Web-Chat</b> (keine Programminstallation erforderlich!)');"
        >
      </a><br>
      <a target="_blank" href="http://www.holy-wars2.de/wiki/">
        <img src="wiki01.gif" alt="Bild Wiki"
         onMouseout="chgfx(this, 'wiki01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 'wiki02.gif'); tooltip('Im <b>Holy-Wars 2 Wiki</b> sind viele Informationen rund um das Spiel gesammelt.');"
        >
      </a>
    </DIV>


    <DIV id="menu2">
      <a target="_blank" href="http://www.holy-wars2.de/impressum.php"
       onClick="return framelayer('impressum.php')"
        >
        <img src="impressum01.gif" alt="Bild Impressum"
         onMouseout="chgfx(this, 'impressum01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 'impressum02.gif'); tooltip('Impressum und Kontaktinformationen der Macher von Holy-Wars 2.');"
        >

      </a><br>
      <a target="_blank" href="http://game1.holy-wars2.de/screenshots.php">
        <img src="screenshots01.gif" alt="Bild Screenshots"
         onMouseout="chgfx(this, 'screenshots01.gif'); tooltip(0);"
         onMouseover="chgfx(this, 'screenshots02.gif'); tooltip('Für einen ersten Eindruck über das Spiel wählt dies hier.');"
        >
      </a>
    </DIV>


    <DIV id="vote" 
         onMouseover="tooltip('Stimmt für Holy-Wars 2 ab, falls Euch das Spiel gefällt.');" 
         onMouseout="tooltip(0);"
    >
      <span style="font-size: 14px;">Vote for HW2</span><br><hr><br>
      <a href="http://www.galaxy-news.de/index.php?page=charts&amp;op=vote&amp;game_id=451" 
           target="_blank">
        <img src="http://www.galaxy-news.de/images/vote.gif" border="0" alt="Vote@Galaxy-News">
      </a><br>
      <a href="http://www.gamingfacts.de/charts.php?was=abstimmen2&amp;spielstimme=349" 
           target="_blank">
        <img src="gamingfacts_charts.gif" border="0" alt="[ Gamingfacts.de Vote ]">
      </a><br>
      <a href="http://www.browsergames24.de/modules.php?name=Web_Links&amp;lid=808&amp;l_op=ratelink&amp;ttitle=Holy-Wars_2" target="_blank">
         <img border="0" src="http://www.browsergames24.de/votebg.gif" alt="Das Infoportal rund um Browsergames">
      </a>
      <br>
      <a href="http://bgs.gdynamite.de/charts_vote_293.html" target="_blank">
         <img src="gd_animbutton.gif" border="0" alt="GDynamite">
      </a>
      <br>
      <a href="http://www.browserwelten.net/?ac=vote&amp;gameid=334" target="_blank">
        <img src="http://www.browserwelten.net/img/bw_votebutton.gif" 
             alt="www.browserwelten.net">
      </a>
    </DIV>

    <DIV id="icons">
      <p>
      <a href="http://validator.w3.org/check?uri=www.holy-wars2.de"><img
        src="http://www.w3.org/Icons/valid-html401" border="0"
        alt="Valid HTML 4.01 Transitional" height="31" width="88"></a>
      </p>

      <p>
      <a href="http://jigsaw.w3.org/css-validator/"><img         
             src="http://jigsaw.w3.org/css-validator/images/vcss-blue" border="0"
             alt="CSS ist valide!" height="31" width="88"></a>
      </p>
    </DIV>

    <DIV id="links">
      <span style="font-size: 13px;">Partner</span><br><hr>
      <a href="http://www.mightofmagic.de/" style="color: #50FFB0" target="_blank">Might of Magic</a><br>
      <a title="Kneipengame" href="http://www.kneipengame.com/" style="color: #B050FF" target="_blank"><img border="0" src="gfx/kneipengame_88x31.jpg" alt="Kneipengame"/></a><br>
      <a title="Industrie Tycoon 2" href="http://www.itycoon2.de/" style="color: #FFFF50" target="_blank"><img border="0" src="http://www.itycoon2.de/pr/88x31.gif" alt="Industrie Tycoon 2"/></a><br>
      <br>
      <a href="http://www.psitronic.de/" style="color: #EE4040" target="_blank">psitronic IT-Solutions</a><br>           
    </DIV>
    
    
    <DIV id="headline" >
      <b><u>Kostenlose</u> Mittelalter-Echtzeit-Strategie im Browser.</b>
    </DIV>      

    <DIV id="shortnews">
      <font color="#FF8F2F">
      <b>Reset speed UND game1 25. Juni 2010</font><br>
      Weitere Informationen <a target="_blank" href="http://forum.holy-wars2.de/viewtopic.php?f=1&t=11085">hier im Forum</a>.      
    </DIV>


    <DIV id="tooltip">
    <? echo $GLOBALS['default_tooltip']; ?>
    </DIV>


    <DIV id="framelayer" style="visibility: hidden; ">
      <DIV id="framelayerclose" onClick="hideFrameLayer()">X</DIV>
      <IFRAME id="layeriframe" src="opening.php">
      Ihr Browser unterstützt keine Frames.
      </IFRAME>
    </DIV>

  </DIV><!-- main -->
</DIV>

<script type="text/javascript">
tooltip(0);
</script>

    <? include("includes/sponsorads-magiccorner.html"); ?>
</BODY>
</HTML>

