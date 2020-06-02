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
 * Copyright (c) 2005-2008 by Holy-Wars2.de
 *
 * Stefan Neubert
 * Stefan Hasenstab
 * Gordon Meiser
 * Markus Sinner
 *
 * This File must not be used without permission!
 ***************************************************/
include_once("includes/db.inc.php");
include_once("includes/research.class.php");
include_once("includes/player.class.php");
include_once("includes/clan.class.php");
include_once("includes/session.inc.php");
include_once("includes/util.inc.php");
include_once("includes/banner.inc.php");


define("TEAM_SENDER", "HW2-Team");

if(isset($show)) {
  $res1=do_mysqli_query("SELECT id,sender,recipient,date,header,body FROM message
      WHERE id=".$show." AND ((recipient=".$player->getID()." AND !(status&".MSG_RECIPIENT_DELETED."))
      OR (sender='".$player->getName()."' AND date>=".$player->getRegTime().") AND !(status&".MSG_SENDER_DELETED."))"
      );
  if (mysqli_num_rows($res1)==1) {
    do_mysqli_query("UPDATE message SET status=status|".MSG_RECIPIENT_READ." WHERE id=".$show." AND recipient=".$player->getID());
    do_mysqli_query("UPDATE message SET status=status|".MSG_SENDER_READ." WHERE id=".$show." AND sender='".$player->getName()."' AND date>=".$player->getRegTime());
    do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID());
    $db_msg=mysqli_fetch_assoc($res1);
    $showmsg=1;
  }
}
elseif (isset($msgre)) {
  $msgheader="Re: ".$msgheader;
  $msgbody="\n\n--------------- ".$sender." schrieb ---------------\n".$msgbody;
  $msgbody = ereg_replace("&quot","\"",stripslashes($msgbody));
  $msgheader = ereg_replace("&quot","\"",stripslashes($msgheader));
}
elseif (isset($msgfw)) {
  $msgheader="FW: ".$msgheader;
  $msgbody="\n\n--------------- ".$sender." schrieb ---------------\n".$msgbody;
  unset($msgrecipient);
  $msgbody = ereg_replace("&quot","\"",stripslashes($msgbody));
  $msgheader = ereg_replace("&quot","\"",stripslashes($msgheader));
}
elseif (isset($msgdel)) {
  $res1=do_mysqli_query("SELECT id FROM message WHERE id=".$msgid." AND recipient=".$player->getID());
  if (mysqli_num_rows($res1)==1) {
    do_mysqli_query("UPDATE message SET status=status|".MSG_RECIPIENT_DELETED." WHERE id=".$msgid." AND recipient=".$player->getID());
    do_mysqli_query("UPDATE message SET status=status|".MSG_SENDER_DELETED." WHERE id=".$msgid." AND sender='".$player->getName()."' AND date>=".$player->getRegTime());
    do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID());    
    header("location:messages.php");
  }
}


// Nachricht Voransicht
if (isset($msgpreview)) {
?>
<link rel="stylesheet" href="<? echo $csspath; ?>/hw.css" type="text/css">
<body margin="2" background="<? echo $imagepath; ?>/bg.gif">
   <div align="center">
   <? echo show_banner(0); ?>
   </div>
       
<?
  $db_msg['body'] = stripslashes($msgbody);
  $db_msg['header'] = $msgheader;
  $db_msg['recipient'] = $msgrecipient;
  $db_msg['sender'] = $player->getName();
  $db_msg['date'] = time();
  showMessage(true);
  die("</body></html>");
}

if (isset($msgsend)) {
  if (strlen(trim($msgheader))<100) {
  	// Leeres Topic abfanden
    if(strlen(trim($msgheader)) == 0) {
      $msgheader = "[ohne Titel]";
    }
    
    if (strlen(trim($msgbody)) > 0) {
      $tst=$msgbody." ".$msgheader;

      // Normale Nachricht
      if (isset($msgsend) && !isset($clanbroadcast) && !isset($adminbroadcastmail) && !isset($adminbroadcast) && !isset($minbroadcast) ) {
      	$rcp_array = explode(",", $msgrecipient);
      	
      	if(sizeof($rcp_array) > 2 && !is_premium()) {
      		$msgerror = "Versenden an mehr als 2 verschiedene Adressaten nur mit Premium-Account!";
      	} 
      	else { 
      		$rcp_ids = array();
      		foreach($rcp_array as $rcp_name) {
      			$rcp_name = trim($rcp_name);
      			$res1=do_mysqli_query("SELECT id FROM player WHERE name = '".mysqli_escape_string($GLOBALS['con'],$rcp_name)."'");      		      			
      			if (mysqli_num_rows($res1)>0) {
      				$rec_id = mysqli_fetch_assoc($res1);
      				array_push($rcp_ids, $rec_id['id']);
      				$cachercp = $rcp_name;      			
      			}
      			else {
      				if($msgerror) {
      					$msgerror.=", es existiert kein Spieler mit Namen  <u>".$rcp_name."</u>";      					
      				}
      				else {
      					$msgerror="Es existiert kein Spieler mit Namen <u>".$rcp_name."</u>";
      				}
      			}
      		} //foreach
      		
      		if(!$msgerror) {
      			foreach($rcp_ids AS $id) {
      				do_mysqli_query("INSERT INTO message (sender,recipient,date,header,body) VALUES ('".$player->getName()."',".$id.", UNIX_TIMESTAMP(),'".htmlentities($msgheader,ENT_QUOTES)."','".$msgbody."')");
      				do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID()." OR id=".$id);
      			}
      			$player->incSentMessages( sizeof($rcp_ids) );

      			unset($msgheader);
      			unset($msgbody);
      			unset($msgrecipient);
      			unset($msgsend);
      			$text = sizeof($rcp_ids) > 1 ? sizeof($rcp_ids)." Adressaten" : $cachercp;
      			$msgnoerror="Die Nachricht wurde erfolgreich an <b>".$text."</b> abgeschickt.";
      		} // if(!$msgerror)    		    
      	}
    }
    //Ordensnachricht
    if ($clanbroadcast=="1" && isset($msgsend)) {
     	$playerids = do_mysqli_query("SELECT id,name FROM player WHERE clan=".$clan->getID()." ORDER BY name ASC");
    	$header = "(ON) ".htmlentities($msgheader,ENT_QUOTES);
    	while ($get_playerids = mysqli_fetch_assoc($playerids)) {
    		do_mysqli_query("INSERT INTO message (sender,recipient,date,header,body,status) VALUES ('".$player->getName()."',".$get_playerids['id'].",".time().",'".$header."','".$msgbody."',".MSG_CLANMSG.")");
    		do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID()." OR id=".$get_playerids['id']);
    	}
    	$player->incSentMessages();
    	unset($msgheader);
    	unset($msgbody);
    	unset($msgrecipient);
    	unset($msgsend);
    	unset($clanbroadcast);
    	$msgnoerror="Die Ordensnachricht wurde erfolgreich an alle Mitglieder des Ordens versandt.";
    }
    if ($minbroadcast=="1" and isset($msgsend)) {
    	$playerids = do_mysqli_query("SELECT id,name FROM player WHERE clan=".$clan->getID()." AND clanstatus > '0' ORDER BY name ASC");
    	$header = "(MN) ".htmlentities($msgheader,ENT_QUOTES);
    	while ($get_playerids = mysqli_fetch_assoc($playerids)) {
    		do_mysqli_query("INSERT INTO message (sender,recipient,date,header,body,status) VALUES ('".$player->getName()."',".$get_playerids['id'].",".time().",'".$header."','".$msgbody."',".MSG_CLANMINISTERMSG.")");
    		do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID()." OR id=".$get_playerids['id']);
    	}
    	$player->incSentMessages();

    	unset($msgheader);
    	unset($msgbody);
    	unset($msgrecipient);
    	unset($msgsend);
    	unset($clanbroadcast);
    	$msgnoerror="Die Ordensnachricht wurde erfolgreich an alle Minister des Ordens versandt.<br />";
    }

    // Adminrundmail
    if ($adminbroadcastmail=="1" && isset($msgsend)) {
    	if ($player->isAdmin()) {
    		include("includes/adminmail.php");
    		if ( ( $ok = admin_mail($msgheader, $msgbody) ) == null ) {
    			$msgnoerror="Die Nachricht wurde <b>per Mail</b> erfolgreich an alle Spieler aus Holy-Wars versandt.";
    			$player->incSentMessages();
    		}
    		else {
    			$msgerror = $ok;
    		}
    	}
    	else {
    		$msgerror = "Ihr seid dazu nicht befugt!";
    	}

    	unset($msgheader);
    	unset($msgbody);
    	unset($msgrecipient);
    	unset($msgsend);
    	unset($adminbroadcast);
    	unset($adminbroadcastmail);
    }


    //Adminnachricht
    if ($adminbroadcast=="1" and isset($msgsend)) {
    	if($_SESSION['player']->isAdmin()) {
    		//An alle
    		if (!isset($onlycf)) {
    			$playerids = do_mysqli_query("SELECT id FROM player WHERE 1");
    			$header = "(AN) ".htmlentities($msgheader,ENT_QUOTES);
    			while ($get_playerids = mysqli_fetch_assoc($playerids)) {
    				do_mysqli_query("INSERT INTO message (sender,recipient,date,header,body,status) VALUES ('".TEAM_SENDER."',".$get_playerids['id'].",".time().",'".$header."','".$msgbody."',".MSG_CLANFOUNDERMSG.")");
    				do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID()." OR id=".$get_playerids['id']);
    			}
    			$player->incSentMessages();
    			$msgnoerror="Die Adminnachricht wurde erfolgreich an alle Spieler aus Holy-Wars 2 versandt.";
    		}
    		else {
    			//Nur an Ordensgr�nder(Clanfounder) 
    			$playerids = do_mysqli_query("SELECT id FROM player WHERE clanstatus=63");
    			$header = "(AN) ".htmlentities($msgheader,ENT_QUOTES);
    			while ($get_playerids = mysqli_fetch_assoc($playerids)) {
    				do_mysqli_query("INSERT INTO message (sender,recipient,date,header,body,status) VALUES ('".TEAM_SENDER."',".$get_playerids['id'].",".time().",'".$header."','".$msgbody."',".MSG_ADMINMSG.")");
    				do_mysqli_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID()." OR id=".$get_playerids['id']);
    			}
    			$player->incSentMessages();
    			$msgnoerror="Die Adminnachricht wurde erfolgreich an alle Ordensgr�nder in Holy-Wars 2 versandt.";
    		}
    	}
    	else {
    		$msgerror = "Ihr seid dazu nicht befugt!";
    	}
     
    	unset($msgheader);
    	unset($msgbody);
    	unset($msgrecipient);
    	unset($msgsend);
    	unset($adminbroadcast);
    	unset($onlycf);
    }
  }
  else {
  	$msgerror="Geben Sie Ihren Text ein.";
  }
}
else {
	$msgerror="Der Titel ist zu lang.";
}

// Im Fehlerfall Body und Header wieder setzen, damit der Spieler
// nicht nochmal tippen muss
if($msgerror){
	$msgbody = ereg_replace("&quot","\"",stripslashes($msgbody));
	$msgheader = ereg_replace("&quot","\"",stripslashes($msgheader));
}
}
else {
	// If new Message or Forward/Reply append the Signature
	$msgbody=$player->getMsgSignature().$msgbody;
}

start_page();

if($showmsg!=1) {
  
?>
<script language="JavaScript">
<!-- Begin
var pvw = null;
function openPreview() {
  if(pvw = window.open("about:blank","previewWindow","width=500,height=500,left=0,top=0,scrollbars=yes,dependent=yes")) {
    document.form.target = "previewWindow";
    document.form.submit();
    pvw.focus();
  }
}

function addRecipient(rcp) {
	elem = document.form.msgrecipient;
	
    if(rcp != '' && elem != null) {
        if(elem.value != '') {
        	elem.value += "," + rcp;
        }   
        else {     
        	elem.value = rcp;
        }
      
    }
}

function checktype(){
if(document.form.clanbroadcast) {
  if(document.form.clanbroadcast.checked){
    document.form.msgrecipient.value = "[Ordensnachricht]";
    document.form.msgrecipient.disabled = 1;
	document.form.minbroadcast.checked = '';
  }
  else if(document.form.minbroadcast.checked){
    document.form.msgrecipient.value = "[Ministernachricht]";
    document.form.msgrecipient.disabled = 1;
	document.form.clanbroadcast.checked = '';
  }
  else{
    document.form.msgrecipient.disabled = 0;
    document.form.msgrecipient.value = "";
  }
}
}

function checktype2(){
  if(document.form.adminbroadcast.checked){
    document.form.msgrecipient.value = "[Adminnachricht]";
    document.form.msgrecipient.disabled = 1;
	<?php if ($player->isAdmin()) {
          echo "document.form.onlycf.disabled = 0;\n"; 
          echo "document.form.adminbroadcastmail.ckecked = 0;\n"; 
          echo "document.form.adminbroadcastmail.disabled = 1;\n"; 
        } ?>
  }
  else if(document.form.adminbroadcastmail.checked){
    document.form.msgrecipient.value = "[Admin-Mail]";
    document.form.msgrecipient.disabled = 1;
    <?php if ($player->isAdmin()) {
      echo "document.form.adminbroadcast.ckecked = 0;\n"; 
      echo "document.form.adminbroadcast.disabled = 1;\n"; 
      echo "document.form.onlycf.disabled = 1;";
    } ?>
        }
  else{
    document.form.msgrecipient.disabled = 0;
    document.form.msgrecipient.value = "";
    <?php
        if ($player->isAdmin()) {
          echo "document.form.onlycf.disabled = 0;";
          echo "document.form.adminbroadcast.disabled = 0;\n"; 
          echo "document.form.adminbroadcastmail.disabled = 0;\n"; 
        }?>
    }
}

function check_titel(){
  if(document.form.msgheader.value == ''){
    document.form.msgheader.value = "[ohne Titel]";
  }
}


/**
* From http://www.massless.org/mozedit/
*/
function mozWrap(txtarea, open, close)
{
    var selLength = txtarea.textLength;
    var selStart = txtarea.selectionStart;
    var selEnd = txtarea.selectionEnd;
    var scrollTop = txtarea.scrollTop;

    if (selEnd == 1 || selEnd == 2) 
    {
        selEnd = selLength;
    }

    var s1 = (txtarea.value).substring(0,selStart);
    var s2 = (txtarea.value).substring(selStart, selEnd)
    var s3 = (txtarea.value).substring(selEnd, selLength);

    txtarea.value = s1 + open + s2 + close + s3;
    txtarea.selectionStart = selEnd + open.length + close.length;
    txtarea.selectionEnd = txtarea.selectionStart;
    txtarea.focus();
    txtarea.scrollTop = scrollTop;

    return;
}



function bbCode(start,end,ext) {
    var txtarea = document.getElementById('theText');

    if( txtarea == null)
        return true;
    
    txtarea.focus();
	if(ext) {
		if(ext == 1) {
			start = prompt("Geben Sie eine Farbe an. zb: red,blue,green,...","");
			if(start == null) {
				return
			} else {
				start = "[color="+start+"]";
			}
		}
		if(ext == 2) {
			start = prompt("Geben Sie hier die URL des Bildes ein!\nzb: http://www.domain.com/myimage.jpg","");
			if(start == null) {
				return
			} else {
				start = "[img]"+start;
			}
		}
		if(ext == 3) {
			start = prompt("Geben Sie hier die URL an.\nzb: http://www.domain.com","");
			if(start == null) {
				return
			} else {
				start = "[url="+start+"]"+start;
			}
		}
	}

  try {
   if (window.getSelection) {
	   text = window.getSelection();

	   /* Workaround for newer Firefoxes
	    * http://joemaller.com/2005/04/24/getselection-workaround/
	    */
	   if(text.type= 'undefined') {
		   if (!isNaN(txtarea.selectionStart))
		    {
		        var sel_start = txtarea.selectionStart;
		        var sel_end = txtarea.selectionEnd;

		        var selection = start +  txtarea.value.substring(sel_start, sel_end) + end;
		        txtarea.value = txtarea.value.substring(0, sel_start) + selection + txtarea.value.substring(sel_end)
		        txtarea.focus();
		        txtarea.selectionStart = txtarea.selectionEnd = (sel_start + selection.length);
		    }   		    		   				       
	   }
	   else {
   	       window.getSelection = start+text+end;
	   }
	} 
	else if (document.getSelection) {
		text = document.getSelection();
		document.getSelection() = start+text+end;
	} 
	else if (document.selection) {
		text = document.selection.createRange().text;
		document.selection.createRange().text = start+text+end;
	}

  } 
  catch(ex) {
	    alert(ex);
  }
	

    return false;
}

// End -->
</script>
<?php
}
start_body();
if($showmsg!=1) {
?>
<form name="form" id="theForm" target="_self" action="<? echo $PHP_SELF; ?>" method="POST">

<table cellspacing="0" cellpadding="0" border="0">
<tr valign="top">
<td>

<table cellspacing="1" cellpadding="1" border="0" width="460">
<tr><td class="tblhead" colspan="2"><b>Nachricht schreiben</b></td></tr>
<tr><td colspan="2">
<? if($msgerror) echo "<br><div class='error'><b>".$msgerror."</b></div>"; 
if($msgnoerror) {
  echo "<br><div class='noerror'><b>".$msgnoerror."</b></div>";
  if (!is_premium_noads()) {
    include("includes/ebay.flash.html");
  }
}
?>
&nbsp;</td></tr>


<tr><td class="tblhead" colspan="2">&nbsp;</td></tr>
<tr>
	<td class="tblbody" width="200">
		<b>Empf&auml;nger</b><br></br>
		<span style="font-size: 10px;">Mehrere Emp�nger k�nnen durch Komma getrennt werden</span>
	</td>
	<td class="tblbody">
		<input type="text" name="msgrecipient" value="<? echo $msgrecipient; ?>" style="width:100%;">
  		<br>
  		<select onChange="addRecipient(this.value)" name="addressbook" size="1" stlye="width:100%;">
 		<option value="">bitte w�hlen</option>
<?
if (is_premium_adressbook()) {
  echo "<option value=\"\">  <- Eigenes Adressbuch -></option>\n";
  $adress = $player->getAdressbookPlayers();
  foreach ($adress as $i=>$adr) {
    echo '<option value="'.$adr['name'].'">'.$adr['nicename']."</option>\n";     
  }    

  echo "<option value=\"\">  <- Alliierte -></option>\n";
  $allies = $player->getAlliedPlayers();
  foreach ($allies as $i=>$ally) {
    echo '<option value="'.$ally[1].'">'.$ally[1].($ally[2] ? " (O)" : " (A)")."</option>\n";
  }
}
else {	
  echo "<option value=\"\">Adressbuch nur</option>\n";  
  echo "<option value=\"\">f�r Besitzer eines</option>\n";  
  echo "<option value=\"\">Premium-Accounts.</option>\n";  
}
?>
	</select> <a href="edit_adr.php" title="Adressbuch editieren"><img alt="Editieren" align="bottom" src="<?php echo GFX_PATH_LOCAL; ?>/ad_fixed.png" border="0"></a>
     <span style="margin: 2px; font-size: 10px;">Adressbuch nun editierbar!
       <a style="font-size: 10px;" href="edit_adr.php">Hier klicken</a>.
     </span>
	</td>
</tr>
<tr>
    <td class="tblbody" width="200"><b>Titel</b></td><td class="tblbody"><input type="text" name="msgheader" value="<? if (isset($msgheader))echo $msgheader; else echo '[ohne Titel]'; ?>" onclick="this.form.msgheader.value=''" stlye="width:100%;">
    </td>
</tr>
<!-- Ordensrundmail -->
<?
if ($clan->getStatus() >= 1) {
  ?>
  <tr>
  	<td class="tblbody"><b>Ordensnachricht</b>
  	</td>
  	<td class="tblbody"><input type="checkbox" name="clanbroadcast" value="1" onclick="checktype()">
  	</td>
  </tr>
  <tr>
  	<td class="tblbody"><b>Ordensf&uuml;hrungsnachricht</b>
  	</td>
  	<td class="tblbody"><input type="checkbox" name="minbroadcast" value="1" onclick="checktype()">
  	</td>
  </tr>
  <? } ?>
<!-- Adminrundmail -->
<?
if ($player->isAdmin()) {
  ?>
  <tr>
      <td class="tblbody"><b>Admin-Ingame-Nachricht</b>
      </td>
      <td class="tblbody"><input type="checkbox" name="adminbroadcast" value="1" onclick="checktype2()">
      </td>
  </tr>
  <tr>
      <td class="tblbody" width="150" colspan="2">
     <input type="checkbox" name="onlycf" value="1"> <b>nur Ordensgr�nder</b>&nbsp;
      </td>
  </tr>
  <tr>
      <td class="tblbody"><b>Admin-Rundschreiben per Email</b>
      </td>
      <td class="tblbody"><input type="checkbox" name="adminbroadcastmail" value="1" onclick="checktype2()">
      </td>
  </tr>


  <? } ?>
<tr><td colspan="2" class="tblbody"><textarea id="theText" name="msgbody" cols="50" rows="14" style="width:100%;"><? echo $msgbody; ?></textarea></td></tr>
<?php insertBBForm(2) ?>
<tr><td class="tblhead" align="center" colspan="2"><input onClick="openPreview()" type="submit" name="msgpreview" value=" Vorschau "> <input type="submit" name="msgsend" value=" Nachricht versenden (ALT+S)" accesskey="s" onClick="document.form.target ='main'; if(pvw) pvw.close();"> <input onClick="document.form.target ='main'; return confirm('Nachricht wirklich zur�cksetzen?')" type="reset" value=" Zur�cksetzen "></td></tr>
</table>
<br>
<a target="main" href="messages.php"><b>zur�ck</b></a>
</td>
<td>
<? skyscraper(); ?>
</td>
</tr></table>
</form>
<script language="JavaScript">
<!-- Begin
  window.focus();
// End -->
</script>
<?php
} // if

if($showmsg==1) {
  showMessage();
}
if($showmsg!=1) {
?>
<script language="JavaScript">
<!-- Begin
document.form.msgbody.focus();
<?php if ($player->isAdmin()) {
  echo "document.form.onlycf.disabled = 1;\n"; 
} ?>
// End -->
</script>
<?php
}
echo "</body></html>";


function showMessage($preview=false) {
  global $db_msg, $player, $PHP_SELF;
?>
<!-- ***** Message Show Begin ***** -->
<table cellspacing="0" cellpadding="0" border="0">
<tr valign="top">
<td>
<table width='460' cellpadding='0' cellspacing='1' border='0'>
<form target='_self' action='<? echo $PHP_SELF;?>' method='POST'>
<tr><td class='tblhead' colspan='2'><h2><? if($preview) echo "Vorschau der Nachricht"; else echo "Nachricht lesen"; ?></h2></td>
<td width="100" class="tblhead" rowspan="5" style="padding: 0;" align="center">

<?php
  $resA=do_mysqli_query("SELECT id, avatar FROM player WHERE name='".$db_msg['sender']."'");
  if(mysqli_num_rows($resA) > 0) 
    {
      $avatar=mysqli_fetch_assoc($resA);
      if($avatar['avatar']==2) {
        echo "<img title=\"Avatar\" src=\"avatar.php?id=".$avatar['id']."\" />"; 
      }
      else {
        printf('<img title="Avatar" src="%s/avatar_dummy.jpg">', $GLOBALS['imagepath']);
      }
    }
  else 
    {
      printf('<img title="HW2" src="%s/logo_80x80.gif">', $GLOBALS['imagepath']);
    }
  
  echo "<br><a href=\"settings.php?show=game#newavatar\">Will ich auch!</a></td></tr>\n";

  if($db_msg['recipient']==$player->getID() && !$preview) {
    $playername = $db_msg['sender'];
    echo "<tr><td width='60' class='tblbody'><b>Absender</b></td><td width='400' class='tblbody'>";
    echo "<a onClick=\"playerinfo('".urlencode($playername)."'); return false;\" href=\"info.php?show=player&name=".urlencode($playername)."\">".$playername."</a>\n";
    echo "</td></tr>\n";
  }
  else {
    $playername = resolvePlayerName($db_msg['recipient']);
    echo "<tr><td width='60' class='tblbody'><b>Empf&auml;nger</b></td><td width='400' class='tblbody'>";
    echo "<a onClick=\"playerinfo('".urlencode($playername)."'); return false;\" href=\"info.php?show=player&name=".urlencode($playername)."\">".$playername."</a>\n";
    echo "</td></tr>\n";
  }

  echo "<tr class='tblbody'><td width='60'><b>Datum</b></td><td width='400'>".date("d.m.y H:i", $db_msg['date'])."</td></tr>\n";
  echo "<tr class='tblbody'><td width='60'><b>Titel</b></td><td width='400'>".$db_msg['header']."</td></tr>\n";
  
  
    echo "<tr class='tblbody'><td width='60'><b>Aktion</b></td><td width='400'>";
    if($db_msg['sender'] != TEAM_SENDER) {  
        echo player_to_adr($db_msg['sender']);
    }
    else {
        echo "&nbsp;"; 
    }
    
    echo "</td></tr>\n";
  
  

  echo "<tr><td width='460' height='200' valign='top' colspan='3' class='msg'>".bbCode($db_msg['body'])."</td></tr>\n";
  echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
  echo "<tr><td align='center' colspan='3'>\n";
  if ($preview) {
    echo "<input type='button' onClick='window.close()' value=' Vorschau Schliessen '>";
    echo "<input type=\"button\" onClick=\"opener.document.form.msgsend.click(); window.close(); \" value=\" Nachricht versenden \">";
  }
  else {
    echo "<input type='hidden' name='msgid' value='".$db_msg['id']."'>";
    if($db_msg['recipient']!=$player->getID())
      echo "<input type='hidden' name='msgrecipient' value='".resolvePlayerName($db_msg['recipient'])."'>";
    else
      echo "<input type='hidden' name='msgrecipient' value='".$db_msg['sender']."'>"; 

    echo "<input type='hidden' name='sender' value='".$db_msg['sender']."'>";
    echo "<input type='hidden' name='msgheader' value=\"".ereg_replace("\"","&quot",$db_msg['header'])."\">";
    echo "<input type='hidden' name='msgbody' value=\"[quote]".ereg_replace("\"","&quot",$db_msg['body'])."[/quote]\">";
    if($db_msg['sender'] != TEAM_SENDER) {  
        echo "<input type='submit' name='msgre' value=' antworten '> ";              
    }
    echo "<input type='submit' name='msgfw' value=' weiterleiten '> <input type='submit' name='msgdel' value=' l�schen '>\n";
  }
  echo "</td></tr>";
  echo "</form>\n";
if (!is_premium_noads()) {
?>
  <tr><td align='center' colspan='3'>
<br>
  <!-- BEGIN PARTNER PROGRAM - DO NOT CHANGE THE PARAMETERS OF THE HYPERLINK -->
  <A style="color: blue" HREF="http://partners.webmasterplan.com/click.asp?ref=241980&site=3749&type=text&tnb=14" TARGET="_top">BASE.de - hier gibts die Flatrate f&uuml;rs Handy<br></a><IMG SRC="http://banners.webmasterplan.com/view.asp?site=3749&ref=241980&b=0&type=text&tnb=14" BORDER="0" WIDTH="1" HEIGHT="1">
  <!-- END PARTNER PROGRAM -->
  
<?
}
  if (!$preview) {
    echo "<br><a target=\"main\" href=\"messages.php\"><b>zur�ck</b></a>\n\n";
  }
  echo "</td></tr></table>";
?>
  <!-- ***** Message Show End ***** -->
  </td>
  <td>
  <? skyscraper();?>
  </td></tr></table>

<?
}
function skyscraper() {
  if (!is_premium_noads()) {
    $timemod = time() % 3600;
    if($timemod < 600)
      include("includes/friendscout_120x600.html"); 
    else if($timemod < 1500)
      include("includes/sponsorads-skyscraper.html");        
    else if($timemod < 2500)
      include("ads/openinventory_120x600.php"); 
    else if($timemod < 3000)
      include("includes/mobile-skyscraper.html");       
    else 
      include("includes/affilimatch-skyscraper.html");       
  }  
}

?>
