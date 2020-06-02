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
 * Copyright (c) 2007-2009 by Holy-Wars 2
 *
 * written by Markus Sinner <sinner@holy-wars2.de>
 *
 * This File must not be used without permission!
 ***************************************************/
require_once("includes/db.inc.php");
$player_file = "includes/player.class.php";
if(file_exists($player_file)) {
  @include_once($player_file);
  session_start();
}
else {
  session_start();
  unset($_SESSION['player']);
}



define("HTML", 1);

// Für jede Adresse ein eigenes Email versenden?
define("SPAWN", 1);



$boundary = uniqid("");
$headers  =
"MIME-Version: 1.0\r\n".
"X-Mailer: PHP/" . phpversion() . "\r\n".
"FROM: Holy-Wars 2 Newsletter <mail@holy-wars2.de>\r\n".(HTML == 1 ?
"Content-Type: multipart/alternative; boundary=\"". $boundary."\"\r\n" : "Content-Type: text/plain\r\n");


$pre_body = "<html>\r
<head>\r
<title>Holy-Wars 2 Newsletter</title>\r
<meta name=\"description\" content=\"Holy-Wars 2 Newsletter\">\r
<meta name=\"author\" content=\"Das Holy-Wars 2 Team\">\r
<meta name=\"keywords\" content=\"HW2 Holy Wars Newsletter\">\r
<style type=\"text/css\">\r
body\r
{\r
 margin: 0;\r
 font-size: 12px;\r
 font-family: Arial,Tahoma,Verdana;\r
}\r
</style>\r
</head>\r
<body text=\"#000000\" bgcolor=\"#bfaa6a\" link=\"#303030\" alink=\"#303030\" vlink=\"#303030\">\r
";

if(!isset($_SESSION) || !isset($_SESSION['player']) || !$_SESSION['player']->isAdmin() || isset($_REQUEST['showall'])) {
  // Zeige Newsletter an
  echo $pre_body;
  $res = do_mysql_query("SELECT * FROM global.newsletter WHERE published IS NOT NULL ORDER BY id DESC");
  
  echo "<table align=\"center\">";
  while($n = mysqli_fetch_object($res)) {
    echo "<tr><td style=\"padding: 10px;\">\n";

    if($_REQUEST['text'] == 1) {
      $out = "<textarea style=\"width: 700px; height: 1000px; \">\n".text_version($n->body)."</textarea>\n";
    }

    printf("Datum: <i>%s</i><br>\nBetreff: <i>%s</i>\n<p>\n%s</td></tr><tr><td><hr style=\"border: 4px solid black;\">\n", date("d.m.Y H:i:s", $n->published), $n->topic, $_REQUEST['text'] == 1 ? $out :$n->body );
    echo "</td></tr>\n";
  }
  
  die("</table></body></html>");
}
else {
// ADMIN-Teil:
  include_once("includes/page.func.php");
  start_page();
  
?>
<style type="text/css">
tr { vertical-align: top; }
#preview {
 position: relative;
 top: 0px;
 left: 0px;
 width:  600px;
 border: 1px solid black;
}
#newsletterform textarea {
  font-size: 12px;
  width:  600px;
  height: 400px;
  border: 1px solid black;  
}
#newsletterform #topic  {
  width:  400px;
}
#prevtxt {
  width:  600px;
  height: 400px;
  border: 1px solid black;
}
</style>
  <?
  start_body(false);

  print_input_form();


  if(isset($_REQUEST['topic']) && strlen($_REQUEST['topic']) && isset($_REQUEST['body']) && strlen($_REQUEST['body']) ) {
    if(isset($_REQUEST['btnsave'])) {
      $sql = sprintf("INSERT INTO global.newsletter (topic, body, time, author) VALUES ('%s', '%s', UNIX_TIMESTAMP(), %d) ",
      mysqli_escape_string($GLOBALS['con'], stripslashes($_REQUEST['topic'])),
      mysqli_escape_string($GLOBALS['con'], stripslashes($_REQUEST['body'])),
      $_SESSION['player']->getID()
      );
      do_mysql_query($sql);
    }
    else if(isset($_REQUEST['btnpreview'])) {
      print_preview( stripslashes($topic), stripslashes($body) );
    }
    else if(isset($_REQUEST['btntestsend']) ) {
      print_preview( stripslashes($topic), stripslashes($body) );
      echo "<p>";
      // Explizit NULL als Parameter, damit nur Testversendet wird.
      real_send_mail( stripslashes($topic), stripslashes($body), null );
    }
    else if(isset($_REQUEST['btnrealsend']) && isset($_REQUEST['confirm'])) {
      $emails = getMailAdresses();

      real_send_mail( stripslashes($topic), stripslashes($body), $emails);
    }
  }

  echo "</body></html>\n";
}// else




function getMailAdresses() {
  // Erstmal mit den aktuelle Daten synchronisieren
  syncEmailAddresses();
  
  flush();
  
  $emails = array();
  $res = do_mysql_query("SELECT email.email FROM global.email LEFT JOIN global.mailfail USING(email) WHERE active = 1 AND mailfail.type IS NULL");
  while($next = mysqli_fetch_array($res)) {
    array_push($emails, $next[0]);
  }
  return $emails;
}


function syncEmailAddresses() {
  $dbtables = array(
  "forum" => array("forum_users"),

  "hw2_oldgame1" => array("player", 
                          "log_player_deleted"), 	
  
  "hw2_speed" => array("player", 
                       "log_player_deleted"),

  "hw2_hispeed" => array("player", 
                       "log_player_deleted"),

  "hw2_game1" => array("player", 
                       "log_player_deleted"),
  
/*  
  "hw2_game2_inactive" => array("player", 
                                "log_player_deleted"),

  "_hw2_2006_11_b4_20070406" => array("player",
                                    "log_player_deleted"),

  "_hw2_2006_11_b4_20071222" => array("player",
                                    "log_player_deleted"),

  "_hw2_2006_speed_051106" => array("player",
                                    "log_player_deleted"),

  "_hw2_2006_speed_150506" => array("player",
                                    "log_player_deleted"),

  "_hw2_2006_speed_300706" => array("player",
                                    "log_player_deleted"),

  "_hw2_auf_host_hw2"      => array("player",
                                    "log_player_deleted"),

  "_hw2_game1_b4_20080320" => array("player",
                                    "log_player_deleted"),

  "_hw2_game1_b4_20080926" => array("player",
                                    "log_player_deleted"),

  "_hw2_game1_b4_20090807" => array("player",
                                    "log_player_deleted"),
*/
  
  "_hw2_game1_b4_20091016" => array("player",
                                    "log_player_deleted"),

  /*
  "_hw2_game2_b4_20080620" => array("player",
                                    "log_player_deleted"),

  "_hw2_game2_b4_20081114" => array("player",
                                    "log_player_deleted"),
  */
  
  /*
  "_hw2_speed_b4_20061209" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20070128" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20070323" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20070511" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20070623" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20070929" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20071116" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20070805" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080111" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080223" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080320" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080430" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080430" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080617" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080725" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20080926" => array("player",
                                    "log_player_deleted"),

  "_hw2_speed_b4_20081114" => array("player",
                                    "log_player_deleted"),
  
  "_hw2_speed_b4_20081224" => array("player",
                                    "log_player_deleted"),
  
  "_hw2_speed_b4_20090130" => array("player",
                                    "log_player_deleted"),
  
  "_hw2_speed_b4_20090314" => array("player",
                                    "log_player_deleted"),
  */
  
  "_hw2_speed_b4_20090613" => array("player",
                                    "log_player_deleted"),
  
  "_hw2_speed_b4_20090821" => array("player",
                                    "log_player_deleted"),
  
  "_hw2_speed_b4_20090925" => array("player",
                                    "log_player_deleted")
  
  
  );


  
  $preg = "/[_a-zA-Z0-9-]+[_a-zA-Z0-9-.]*@[a-zA-Z0-9-]+\.+[a-zA-Z]{2,4}/";

  foreach($dbtables AS $db => $tables) {
    foreach($tables AS $table) {
      echo "DB: $db  Table: $table<br>\n";
      $resE = do_mysql_query("SELECT * FROM ".$db.".".$table);

      while($row = mysqli_fetch_array($resE)) {
        foreach($row AS $field) {
          if(strpos($field, "@")) {
            preg_match_all($preg, $field, $regs);
            if(sizeof($regs) > 0) {
              for($i=0;$i<sizeof($regs[0]);$i++) {
                $mail = trim($regs[0][$i]);
                $res = do_mysql_query("SELECT * FROM global.email WHERE email = '".mysqli_escape_string($GLOBALS['con'], $mail)."'");
                if($res && mysqli_num_rows($res) == 0) {
                  $code = uniqid("");
                  $from = $db.".".$table;
                  echo "Sync: $mail from $from<br>\n";
                  $sql = sprintf("INSERT INTO global.email (source, email, time, code) VALUES ('%s', '%s', UNIX_TIMESTAMP(), '%s')",
                                 mysqli_escape_string($GLOBALS['con'], $from),
                                 mysqli_escape_string($GLOBALS['con'], $mail),
                                 mysqli_escape_string($GLOBALS['con'], $code)
                                 );
                  do_mysql_query($sql);
                }
              } // for
            } // if(sizeof($regs) > 0)
          } // if(strpos($field, "@"))
        } // foreach $row
      } // while
    } // foreach $tables
  } // foreach $dbtables
} // function syncEmailAddresses() {


function print_mail_info() {
  $sql = "SELECT count(*) AS cnt, TRIM( REPLACE(SUBSTRING(email, INSTR(email, '@') + 1), '\n', '') ) AS domain FROM global.email GROUP BY domain HAVING cnt > 40 ORDER BY cnt DESC";

  printf("<table><tr><td>Domain</td><td>Vorkommnis</td></tr>\n");
  $res = do_mysql_query($sql);
  while($d = mysqli_fetch_assoc($res)) {
    printf("<tr><td>%s</td><td>%s</td></tr>\n", $d['domain'], $d['cnt']);
  }
  echo "</table>\n";
}


function print_input_form() {
  $lastid = 0;

  if(!isset($_REQUEST['topic']) && !isset($_REQUEST['body'])) {
    $last = do_mysql_query_fetch_assoc("SELECT * FROM global.newsletter WHERE author = ".$_SESSION['player']->getID()." ORDER BY time DESC LIMIT 1");
    if($last != null && $last['topic'] && $last['body']) {
      $body  = $last['body'];
      $topic = $last['topic'];
      $lastid= $last['id'];
    }    
  }
  else {
    $topic = isset($_REQUEST['topic']) ? stripslashes($_REQUEST['topic']): "Holy-Wars 2 Newsletter";
    $body  = isset($_REQUEST['body'])  ? stripslashes($_REQUEST['body']) : "Hallo!";
    if(isset($_REQUEST['lastid'])) $lastid = $_REQUEST['lastid'];
  }
?>
<p>
<form name="newsletterform" id="newsletterform" method="post">
<input type="hidden" name="lastid" value="<? echo $lastid; ?>"/>
<table>
<tr>
   <td>Betreff:</td>
   <td><input type="text" id="topic" name="topic" value="<? echo $topic; ?>"/></td>
</tr>
<tr>
   <td>Text:</td>
   <td>
   <textarea name="body"><?php echo $body; ?></textarea>
   </td>
</tr>
<tr>
   <td colspan="2" align="center" valign="middle">
     <input type="submit" name="btnpreview"  value=" Vorschau "/>&nbsp;&nbsp;&nbsp;
     <input type="submit" name="btntestsend" value=" Testversand "/>&nbsp;&nbsp;&nbsp;
     <input type="submit" name="btnsave" value=" Speichern "/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     <input type="submit" name="btnrealsend"  value=" Raus damit! "/>&nbsp;Bestätigen:&nbsp;
     <input type="checkbox" name="confirm" value="1" /> 
   </td>
</tr>
</table>
</form>
<?  
}


function print_preview($topic, $body) {
  echo "<p>\n<pre>\n";
  echo "Header: ".$GLOBALS['headers']."\n";
  echo "To: ".$to."\nBetreff: ".$topic."\n</pre><textarea id=\"prevtxt\">";
  echo text_version($body);
  echo "</textarea>\n<p><div id=\"preview\" name=\"preview\">";
  echo $body;
  echo "</div>\n<p>\n";  
}

/**
 * Die Email wirklich versenden
 * 
 * @param $topic
 * @param $body
 * @param $recipients
 * @return unknown_type
 */
function real_send_mail($topic, $body, $recipients = null) {
  // $skip = 5100;
  $skip = 0; // skip kann manuell genutzt werden, wenn der Mailversand nach einer gewissen Dauer fehlgeschlagen hat

  set_time_limit(3600);
  if($recipients == null) {
    $simu = false;
    $recipients = array(//"carlos@diener.li", // Monk
                        "emonkey@gmx.ch", // Monk
                        //"punkt@pfadi.ch", // Monk
                        //"stella-luna@hotmail.de",
                        //"staudenmaier.pia@gmail.com", // Sebastian                      
                        //"sebitest@web.de", 
                        //"sebitest@gmx.de",
                        //"sebbe2sss@yahoo.de",
                        "s3sebastian@gmail.com",
                        //"sebitest@freenet.de",
                        //"sebitest@arcor.de",
                        //"sebitest@hotmail.de",
                        //"info@hstuning.de",
                        "morlock@holy-wars2.de",  
                        "kroddn@psitronic.dyndns.org",
                        "howly@howlywood.de",
                        "blackbush@gmx.de,",
                        "u-hartl@t-online.de",
                        "L.Grey@gmx.de"
                        );

  }
  else {
    $simu = false;
  }


  // ENTFERNEN!!!
  //$simu = true;


  $body = trim($body);
  $asciiversion = text_version($body);

  // Den HTML-Newsletter in Text und HTML sendne
  if(HTML) {
    $sendbody = "--".$GLOBALS['boundary']."\r\nContent-Type: text/plain; charset=iso-8859-1\r\n\r\n".$asciiversion."\r\n--".$GLOBALS['boundary']."\r\nContent-type: text/html; charset=iso-8859-1\r\n\r\n".$GLOBALS['pre_body'].$body."</body></html>\r\n\r\n--".$GLOBALS['boundary']."--";
  }
  else {
    $sendbody = $asciiversion;
  }
  
  if(SPAWN != 1) {
    $header = "BCC:".$to."\r\n".$GLOBALS['headers'];
  }
  else {
    $header = $GLOBALS['headers'];
  }

  $cnt = 0;
  $hlogfile = fopen("/tmp/newsletter.log", "a");

  foreach($recipients AS $r) {
    if(SPAWN != 1) {
      if($to != null) {
        $to.= "," .trim($r);
      }
      else {
        $to = trim($r);
      }
    }
    else {
      $cnt++;
      if(!$simu) {
        if($skip < $cnt) {
          mail($r, $topic, $sendbody, $header);          
          if($hlogfile) {            
            fputs($hlogfile, "OK\t$cnt\t$r\n");
          }
        }
        else {
          if($hlogfile) {
            fputs($hlogfile, "SKIP\t$cnt\t$r\n");
          }
        }
      }
      else {
        echo "Simulation Sending: $r<br>\n";
      }
      
      if($cnt%100 == 99) {
        echo "$cnt Emails raus<br>";
        flush(); // dem Browser die möglichkeit zum aktualisieren geben
      }
    }
  } // foreach

  if($cnt > 0) {
    if(SPAWN == 1) {
      echo "<h1>Insgesamt $cnt Emails raus (SPAWN).</h1>";
    }
    else {
      echo "<h1>$cnt Emailempfänder generiert.</h1>";
    }
  }

  if(SPAWN != 1) {
    if($to && !$simu ) {
      if(mail('newsletter-preview@holy-wars2.de', $topic, $sendbody, $header)) {
        echo "Vorschau per Email versendet!";
      }
      else {
        echo "Fehler.";
      }
    }
    else {
      echo "Keine Empfänger bzw. Simulationsmodus.";
    }
  }

  if($hlogfile) {
    fclose($hlogfile);
  }
}


function text_version($html) {
  $replace = array(
                   "%[[:space:]]*<p>[[:space:]]*%i"  => "\r\n\r\n",
                   "%[[:space:]]*<br>[[:space:]]*%i" => "\r\n",
                   "%<li>%i" => " * ",
                   "%<ul>%i" => "",
                   "%</ul>%i"=> "",
                   "% *<b>%i"  => " *",
                   "%</b> *%i"=> "* ",
                   "%<h[1-3]>%i"=> "**",
                   "%</h[1-3]>[[:space:]]*%i"=> "**\r\n",
                   "%<a [^>]*>%i" => "",
                   "%<a [^>]*>%i" => "",
                   "%<table[^>]*>[[:space:]]*%i"=> "",
                   "%</table>%i"=> "",
                   "%<tr>[[:space:]]*%i"=> "",
                   "%<td>[[:space:]]*%i"=> "",
                   "%</tr>%i"=> "",
                   "%</td>%i"=> "",
                   '%<img [^>]*alt="([^"]*)"[^>]*>%i' => "\\1\r\n",
                   "%<hr>[[:space:]]*%" => "________________________________________\r\n\r\n",
                   "%</?[^>]*>%i" =>""
                   
);
  
  
  $from = array();
  $to   = array();
  foreach($replace AS $f => $t) {
    array_push($from, $f);
    array_push($to, $t);
  }
 

  return preg_replace($from, $to, $html);
}

?>
</body>
</html>
