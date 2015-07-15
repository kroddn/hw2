<?php
include_once("includes/db.inc.php");
include_once("includes/config.inc.php");
include_once("includes/session.inc.php");

$player = $_SESSION['player'];

/* ------------------------------ NACHRICHTEN LESEN ------------------------------ */

// Javascript zum Select/Unselect All messages 
echo '<script>';
echo 'function togglecheckboxes(master,group){'; // Get master and Group von Input Checkboxen
echo '	var cbarray = document.getElementsByClassName(group);'; 
echo '	for(var i = 0; i < cbarray.length; i++){';
echo '		var cb = document.getElementById(cbarray[i].id);';
echo '		cb.checked = master.checked;'; // Alle Group_slaves dem Master gleichsetzen
echo '	}';
echo '}';
echo '</script>';

function message_read($id)
{
	// Check and Configure Settings from DB
	$sql_settings=("SELECT 
					playerid AS playerid, 
					rows_per_page AS rows_per_page,
					ignore_read AS ignore_read,
					show_archive AS show_archive,
					ignore_build AS ignore_build,
					show_fights AS show_fights
					FROM config_message
					WHERE playerid =".$id);
	
	// SQL-Query zusatz von den Settings
	$sql_settings_add =""; 
	
	$result_settings = do_mysql_query($sql_settings);
	$get_settings = mysql_fetch_assoc($result_settings);
	
	if($get_settings['ignore_read']==1) $sql_settings_add = $sql_settings_add."AND status NOT LIKE '1' ";
	if($get_settings['show_archive']==0) $sql_settings_add = $sql_settings_add."AND status NOT LIKE '2' ";
	if($get_settings['ignore_build']==1) $sql_settings_add = $sql_settings_add."AND category NOT LIKE '3' ";
	
	// SQL-Query Base
	$sql_base=("SELECT 
			message.id AS id, 
			message.sender AS sender, 
			message.date AS date, 
			message.status AS status, 
			message.category AS category, 
			message.header AS subject, 
			message.body AS message 
			FROM message 
			WHERE message.recipient = ".$id."			
				AND status NOT LIKE '3' ");
	
	// SQL-Query Ende
	$sql_end=("GROUP BY id DESC");	
	$sql = $sql_base.$sql_settings_add.$sql_end;

	// SQL-Query ausführen
	$result = do_mysql_query($sql);

	// Vorbereitung der Seiten Anzeige
	if($get_settings['rows_per_page']>0)
	{
		$daten_pro_seite = $get_settings['rows_per_page'];
	}
	else 
	{
		$daten_pro_seite = 25;	
	}
	// Nachrichten Pro Seite
	$menge = mysql_num_rows($result);	// Anzahl der Datensätze
	$seitenanzahl = ceil($menge/$daten_pro_seite); 	// Wie viele Seiten gibt es/werden benötigt

	$aktuelle_seite = isset($_GET["seite"]) ? mysql_real_escape_string($_GET["seite"]) : 1; // Aktuelle Seite
	$start = $aktuelle_seite * $daten_pro_seite - $daten_pro_seite; // Start berechnen

	// Start Table
	echo '<table cellspacing = "1" cellpadding="0" border="0">';
	echo '<tr height="20" class="tblhead"><td></td><td><a>Von:</a></td><td><a>Datum:</a></td><td><a>Betreff:</a></td><td><a>Nachricht:</a></td></tr>';

	// Start Form von Checkboxen + Manage
	echo '<form name="set_mess_manage" action="'.$PHP_SELF.'" method="post">';
	
	// Den Inhalt jeder Spalte der Tabelle anzeigen
	$datensaetze = do_mysql_query($sql. " LIMIT "  . $start . ", " . $daten_pro_seite);

	// Select Counter für IDs der Checkboxen
	$select_counter = 0;
	
	// Eigentliche Ausgabe der Nachrichten und Checkboxen
	while ($datensatz = mysql_fetch_assoc($datensaetze)) 
	{
		echo "<tr>";
		
		// Checkbox ausgabe - id für checkall, Class für checkall group, In Value steht die Message ID aus der DB
		echo '<td class="tblbody"><input type="checkbox" id="'.$select_counter.'" class="check_group" name="checked[]" value="'.$datensatz['id'].'"></td>';
		
		if($datensatz['sender'] == "SERVER") // Falls die Nachricht vom Server ist nicht SERVER schreiben
		{ 
			echo "<td class='tblbody'>Server</td>";
		} 
		else 
		{
			$senderid = $datensatz['sender'];
			$sendername = get_user_name($senderid);
			echo "<td class='tblbody'><a href='".$PHP_SELF."?show=write&sender=".$sendername."'>".$sendername."</a></td>";
		}
		//echo "<td>".$datensatz['status']."</td>"; -- Das nur für Testzwecke benutzen 
		echo "<td class='tblbody'>".gmdate("d.m.Y - H:i:s", $datensatz['date'])."</td>";
		echo "<td class='tblbody'>".$datensatz['subject']."</td>";
		echo "<td class='tblbody'>".$datensatz['message']."</td>";
		echo "</tr>";
		$select_counter++;
	}
	// Tabelle schließen
	echo "</table>";
	
	// Hier lief irgendetwas schief über echo - Habe es erstmal als html code normal ausgegeben
	?>
	<input type='checkbox' onchange='togglecheckboxes(this,"check_group")'>Toggle All<br/>
	<?php
	 
	 // Message_manage Auswahl zum archiviern löschen etc
	echo '<tr class="tblhead">';
	echo '<td class="tblbody"><select size="Höhe" name="message_todo"></td>';
		echo '<option value="markasread">Als gelesen markieren</option>';
		echo '<option value="archive">Archivieren</option>';
		echo '<option value="delete">Löschen</option>';
		echo '<option value="favorite">Favorit</option>';
	echo '</select></td></tr>';
	echo '<tr class="tblhead"><td class="tblbody"><input type="submit" name="message_manage" value="Senden"></td></tr>';
	echo '</form>';
	
	 // Formular.- und Blätterfunktion
	 echo '<form name="Form" action="' . $_SERVER["SCRIPT_NAME"] . '" method="GET" autocomplete="off">' . 
		(($aktuelle_seite - 1) > 0 ?
		'<a href="?seite=' . ($aktuelle_seite - 1) . '">&laquo;</a>' :
		' &laquo;') .
		' <label>Seite <input type="text" value="' . $aktuelle_seite . '" name="seite" size="3" 
		maxlength="3" title="Seitenzahl eingeben und Eingabetaste betätigen"> von ' . $seitenanzahl . '</label>' .
		(($aktuelle_seite + 1) <= $seitenanzahl ?
		' <a href="?seite=' . ($aktuelle_seite + 1) . '">&raquo;</a>' :
		' &raquo;') .
	 '</form>';
}

function message_manage($todo, $checked)
{
	// If todo ist markasread Alle gecheckten Nachrichten-IDs als gelesen markieren (Status 1)
	if($todo == "markasread")
	{
		$sql="UPDATE message SET status='1' WHERE id=";
		foreach ($checked as $do)
		{
			// echo "mark as read: ".$do."</br>"; Testzwecke!
			if(!do_mysql_query($sql.$do)) echo "ERROR!!!";
		}
	} 
	else if ($todo == "archive")
	{
		$sql="UPDATE message SET status='2' WHERE id=";
		foreach ($checked as $do)
		{
			//echo "mark as archive: ".$do."</br>";
			if(!do_mysql_query($sql.$do)) echo "ERROR!!!";
		}
	}
	else if ($todo == "delete")
	{
		$sql="UPDATE message SET status='3' WHERE id=";
		foreach ($checked as $do)
		{
			//echo "mark as delete: ".$do."</br>";
			if(!do_mysql_query($sql.$do)) echo "ERROR!!!";
		}
	} 
	else if ($todo == "favorite")
	{
		$sql="UPDATE message SET status='4' WHERE id=";
		foreach ($checked as $do)
		{
			//echo "mark as favorite: ".$do."</br>";
			if(!do_mysql_query($sql.$do)) echo "ERROR!!!";
		}
	} 
	else die("Sie haben keine Option ausgewählt");
}

if (isset($_POST['message_manage'])) 
{
	do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$player->getID());
	
	
	$checks = array();
	$manage = $_POST['message_todo'];
	$checks = $_POST['checked'];
	
	// an Message_manage alle Nachrichten IDs übertragen die gecheckt sind und was zu tun ist (archiviern, del etc)
	message_manage($manage, $checks);
}	

/* ------------------------------ NACHRICHTEN SCHREIBEN ------------------------------ */

function get_user_id($name)
{
	$sql = "SELECT
			player.id as playerid,
			player.name as playername
			FROM player
			WHERE player.name = '$name'";
			
	$result = do_mysql_query($sql);
	$getid = mysql_fetch_assoc($result);
	
	// echo "PLayerID: ".$getid['playerid']; Testzwecke
	
	return $getid['playerid'];
}

function get_user_name($id)
{
	$sql = "SELECT
			player.id as playerid,
			player.name as playername
			FROM player
			WHERE player.id = '$id'";
			
			
	$result = do_mysql_query($sql);
	$getname = mysql_fetch_assoc($result);
	
	return $getname['playername'];
}
  
//Wenn der Send Button gedrückt wurde
if (isset($_POST['messagesend']))
{
	$message 		= mysql_real_escape_string(trim($_POST['message']));
	$subject      	= mysql_real_escape_string(trim($_POST['subject']));
	$recipient      = mysql_real_escape_string(trim($_POST['recipient']));
	$recipient_id	= get_user_id($recipient);
	$sender  		= $player->getID();
	$sql       		= "INSERT 
						INTO message (sender, recipient, date, status, category, header, body) 
						VALUES ('".$sender."', '".$recipient_id."', UNIX_TIMESTAMP(), '0', '0', '".$subject."', '".$message."')";
	
	// Falls kein Text gefunden Die function!
	if (!$message) die("Sie haben keinen Text angegeben");
	else
	{
		// Falls User ID Nicht gefunden Die!
		if(!$recipient_id) die("Kann angegebenen User nicht finden");
		else
		{						
			//Wenn es nicht geschrieben werden konnte
			if (!do_mysql_query($sql)) {
				die("Konnte nicht eingetragen werden");
			}   
			//Wenn es drinnsteht             
			else{
				echo "Nachricht wurde erfolgreich abgeschickt!";
			}
		}
	}
	do_mysql_query("UPDATE player SET cc_messages=1 WHERE id=".$recipient_id);
}

function message_write($id)
{
	if($_GET['sender']) $recipient_name = $_GET['sender'];
	echo "<table>";
	echo "<form method='POST' action=".$PHP_SELF.">";
		echo '<tr class="tblbody">';
			echo '<td class="tblbody"><label>Empf&auml;nger: </label>';
			echo '<td class="tblbody"><textarea name="recipient" maxlength="15" cols="45" rows="1">'.$recipient_name.'</textarea></td></tr>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody"><label>Betreff: </label></td>';
			echo '<td class="tblbody"><textarea name="subject" maxlength="30" cols="45" rows="1"></textarea></td></tr>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody"><label>Nachricht: </label></td>';
			echo '<td class="tblbody"><textarea name="message" maxlength="256" cols="45" rows="6"></textarea></td></tr>';

		echo '<tr class="tblbody"><td class="tblbody"><input type="submit" name="messagesend" value="Senden"></td></tr>';
	echo "</table>";
	echo '</form>';
}
/* ------------------------------ NACHRICHTEN ARCHIV ------------------------------ */

function message_archiv($id)
{
	// Check and Configure Settings from DB
	$sql_settings=("SELECT 
					playerid AS playerid, 
					rows_per_page AS rows_per_page,
					ignore_read AS ignore_read,
					show_archive AS show_archive,
					ignore_build AS ignore_build,
					show_fights AS show_fights
					FROM config_message
					WHERE playerid =".$id);
	
	$result_settings = do_mysql_query($sql_settings);
	$get_settings = mysql_fetch_assoc($result_settings);
	
	// SQL-Query Base
	$sql=("SELECT 
			message.id AS id, 
			message.sender AS sender, 
			message.date AS date, 
			message.status AS status, 
			message.category AS category, 
			message.header AS subject, 
			message.body AS message 
			FROM message 
			WHERE message.recipient = ".$id."			
				AND status LIKE '2' 
			GROUP BY id DESC");

	// SQL-Query ausführen
	$result = do_mysql_query($sql);

	// Vorbereitung der Seiten Anzeige
	if($get_settings['rows_per_page']>0)
	{
		$daten_pro_seite = $get_settings['rows_per_page'];
	}
	else 
	{
		$daten_pro_seite = 25;	
	}
	$menge = mysql_num_rows($result);	// Anzahl der Datensätze
	$seitenanzahl = ceil($menge/$daten_pro_seite); 	// Wie viele Seiten gibt es/werden benötigt

	$aktuelle_seite = isset($_GET["seite"]) ? mysql_real_escape_string($_GET["seite"]) : 1; // Aktuelle Seite
	$start = $aktuelle_seite * $daten_pro_seite - $daten_pro_seite; // Start berechnen

	// Start Table
	echo '<table cellspacing = "1" cellpadding="0" border="0">';
	echo '<tr height="20" class="tblhead"><td></td><td><a>Von:</a></td><td><a>Datum:</a></td><td><a>Betreff:</a></td><td><a>Nachricht:</a></td></tr>';

	// Start Form von Checkboxen + Manage
	echo '<form name="set_mess_manage" action="'.$PHP_SELF.'" method="post">';
	
	// Den Inhalt jeder Spalte der Tabelle anzeigen
	$datensaetze = do_mysql_query($sql. " LIMIT "  . $start . ", " . $daten_pro_seite);

	// Select Counter für IDs der Checkboxen
	$select_counter = 0;
	
	// Eigentliche Ausgabe der Nachrichten und Checkboxen
	while ($datensatz = mysql_fetch_assoc($datensaetze)) 
	{
		echo "<tr class='tblbody'>";
		
		// Checkbox ausgabe - id für checkall, Class für checkall group, In Value steht die Message ID aus der DB
		echo '<td class="tblbody"><input type="checkbox" id="'.$select_counter.'" class="check_group" name="checked[]" value="'.$datensatz['id'].'"></td>';
		
		if($datensatz['sender'] == "SERVER") // Falls die Nachricht vom Server ist nicht SERVER schreiben
		{ 
			echo "<td class='tblbody'>Server</td>";
		} 
		else 
		{
			$senderid = $datensatz['sender'];
			$sendername = get_user_name($senderid);
			echo "<td class='tblbody'>".$sendername."</td>";
		}
		//echo "<td>".$datensatz['status']."</td>"; -- Das nur für Testzwecke benutzen 
		echo "<td class='tblbody'>".gmdate("d.m.Y - H:i:s", $datensatz['date'])."</td>";
		echo "<td class='tblbody'>".$datensatz['subject']."</td>";
		echo "<td class='tblbody'>".$datensatz['message']."</td>";
		echo "</tr>";
		$select_counter++;
	}
	// Tabelle schließen
	echo "</table>";
	
	// Hier lief irgendetwas schief über echo - Habe es erstmal als html code normal ausgegeben
	?>
	<input type='checkbox' onchange='togglecheckboxes(this,"check_group")'>Toggle All<br/>
	<?php
	 
	 // Message_manage Auswahl zum archiviern löschen etc
	echo '<select size="Höhe" name="message_todo">';
		echo '<option value="markasread">Als gelesen markieren</option>';
		echo '<option value="archive">Archivieren</option>';
		echo '<option value="delete">Löschen</option>';
		echo '<option value="favorite">Favorit</option>';
	echo '</select>';
	echo '<input type="submit" name="message_manage" value="Senden">';
	echo '</form>';
	
	 // Formular.- und Blätterfunktion
	 echo '<form name="Form" action="' . $_SERVER["SCRIPT_NAME"] . '" method="GET" autocomplete="off">' . 
	 (($aktuelle_seite - 1) > 0 ?
	 '<a href="?seite=' . ($aktuelle_seite - 1) . '">&laquo;</a>' :
	 ' &laquo;') .
	 ' <label>Seite <input type="text" value="' . $aktuelle_seite . '" name="seite" size="3" 
	 maxlength="3" title="Seitenzahl eingeben und Eingabetaste betätigen"> von ' . $seitenanzahl . '</label>' .
	 (($aktuelle_seite + 1) <= $seitenanzahl ?
	 ' <a href="?seite=' . ($aktuelle_seite + 1) . '">&raquo;</a>' :
	 ' &raquo;') .
	 '</form>';
	
}

/* ------------------------------ KAMPFBERICHT ANZEIGEN------------------------------ */

function get_show_fights($id)
{
	// SQL-query schreiben
	$sql=("SELECT 
		playerid AS playerid, 
		rows_per_page AS rows_per_page,
		ignore_read AS ignore_read,
		show_archive AS show_archive,
		ignore_build AS ignore_build,
		show_fights AS show_fights
		FROM config_message
		WHERE playerid =".$id);
		
	$result = do_mysql_query($sql);
	$get_settings = mysql_fetch_assoc($result);
	
	return $get_settings['show_fights'];
}

function message_show_fights($id)
{	
	// SQL-Query Base
	$sql=("SELECT 
			message.id AS id, 
			message.sender AS sender, 
			message.date AS date, 
			message.status AS status, 
			message.category AS category, 
			message.header AS subject, 
			message.body AS message 
			FROM message 
			WHERE message.category = '4'		
				AND status NOT LIKE '3'
				AND header NOT LIKE 'Ausbildung%'
			GROUP BY id DESC");

	// SQL-Query ausführen
	$result = do_mysql_query($sql);

	// Vorbereitung der Seiten Anzeige
	if($get_settings['rows_per_page']>0)
	{
		$daten_pro_seite = $get_settings['rows_per_page'];
	}
	else 
	{
		$daten_pro_seite = 25;	
	}
	$menge = mysql_num_rows($result);	// Anzahl der Datensätze
	$seitenanzahl = ceil($menge/$daten_pro_seite); 	// Wie viele Seiten gibt es/werden benötigt

	$aktuelle_seite = isset($_GET["seite"]) ? mysql_real_escape_string($_GET["seite"]) : 1; // Aktuelle Seite
	$start = $aktuelle_seite * $daten_pro_seite - $daten_pro_seite; // Start berechnen

	// Start Table
	echo '<table cellspacing = "1" cellpadding="0" border="0">';
	echo '<tr height="20" class="tblhead"><td></td><td><a>Von:</a></td><td><a>Datum:</a></td><td><a>Betreff:</a></td><td><a>Nachricht:</a></td></tr>';

	// Start Form von Checkboxen + Manage
	echo '<form name="set_mess_manage" action="'.$PHP_SELF.'" method="post">';
	
	// Den Inhalt jeder Spalte der Tabelle anzeigen
	$datensaetze = do_mysql_query($sql. " LIMIT "  . $start . ", " . $daten_pro_seite);

	// Select Counter für IDs der Checkboxen
	$select_counter = 0;
	
	// Eigentliche Ausgabe der Nachrichten und Checkboxen
	while ($datensatz = mysql_fetch_assoc($datensaetze)) 
	{
		echo "<tr class='tblbody'>";
		
		// Checkbox ausgabe - id für checkall, Class für checkall group, In Value steht die Message ID aus der DB
		echo '<td class="tblbody"><input type="checkbox" id="'.$select_counter.'" class="check_group" name="checked[]" value="'.$datensatz['id'].'"></td>';
		
		if($datensatz['sender'] == "SERVER") // Falls die Nachricht vom Server ist nicht SERVER schreiben
		{ 
			echo "<td class='tblbody'>Server</td>";
		} 
		else 
		{
			$senderid = $datensatz['sender'];
			$sendername = get_user_name($senderid);
			echo "<td class='tblbody'>".$sendername."</td>";
		}
		//echo "<td>".$datensatz['status']."</td>"; -- Das nur für Testzwecke benutzen 
		echo "<td class='tblbody'>".gmdate("d.m.Y - H:i:s", $datensatz['date'])."</td>";
		echo "<td class='tblbody'>".$datensatz['subject']."</td>";
		echo "<td class='tblbody'>".$datensatz['message']."</td>";
		echo "</tr>";
		$select_counter++;
	}
	// Tabelle schließen
	echo "</table>";
	
	// Hier lief irgendetwas schief über echo - Habe es erstmal als html code normal ausgegeben
	?>
	<input type='checkbox' onchange='togglecheckboxes(this,"check_group")'>Toggle All<br/>
	<?php
	 
	 // Message_manage Auswahl zum archiviern löschen etc
	echo '<select size="Höhe" name="message_todo">';
		echo '<option value="markasread">Als gelesen markieren</option>';
		echo '<option value="archive">Archivieren</option>';
		echo '<option value="delete">Löschen</option>';
		echo '<option value="favorite">Favorit</option>';
	echo '</select>';
	echo '<input type="submit" name="message_manage" value="Senden">';
	echo '</form>';
	
	 // Formular.- und Blätterfunktion
	 echo '<form name="Form" action="' . $_SERVER["SCRIPT_NAME"] . '" method="GET" autocomplete="off">' . 
	 (($aktuelle_seite - 1) > 0 ?
	 '<a href="?seite=' . ($aktuelle_seite - 1) . '">&laquo;</a>' :
	 ' &laquo;') .
	 ' <label>Seite <input type="text" value="' . $aktuelle_seite . '" name="seite" size="3" 
	 maxlength="3" title="Seitenzahl eingeben und Eingabetaste betätigen"> von ' . $seitenanzahl . '</label>' .
	 (($aktuelle_seite + 1) <= $seitenanzahl ?
	 ' <a href="?seite=' . ($aktuelle_seite + 1) . '">&raquo;</a>' :
	 ' &raquo;') .
	 '</form>';
}

/* ------------------------------ NACHRICHTEN SETTINGS ------------------------------ */

function message_settings($id)
{
	// SQL-query schreiben
	$sql=("SELECT 
		playerid AS playerid, 
		rows_per_page AS rows_per_page,
		ignore_read AS ignore_read,
		show_archive AS show_archive,
		ignore_build AS ignore_build,
		show_fights AS show_fights
		FROM config_message
		WHERE playerid =".$id);
		
	$result = do_mysql_query($sql);
	$get_settings = mysql_fetch_assoc($result);
	
	//if($get_settings['show_fights']==1) message_show_fights($id);
	
	// Einstellungen auflisten mit Forms
	echo '<form method="POST" action='.$PHP_SELF.'>';
	echo '<table class="tblhead>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody">Wie viele Nachrichten Pro Seite angezeigt werden sollen</td>';
			echo '<td class="tblbody"><select size="Höhe" name="setting_mess_per_page">';
				if($get_settings['rows_per_page']==25) // diese IFs sind zur default Auswahl gedacht
				{
					echo '<option selected="selected" value="25">25 Nachrichten</option>';
					echo '<option value="50">50 Nachrichten</option>';
					echo '<option value="100">100 Nachrichten</option>';
					echo '<option value="200">200 Nachrichten</option>';
				}
				else if($get_settings['rows_per_page']==50) 
				{
					echo '<option value="25">25 Nachrichten</option>';
					echo '<option selected="selected" value="50">50 Nachrichten</option>';
					echo '<option value="100">100 Nachrichten</option>';
					echo '<option value="200">200 Nachrichten</option>';
				}
				else if($get_settings['rows_per_page']==100)
				{
					echo '<option value="25">25 Nachrichten</option>';
					echo '<option value="50">50 Nachrichten</option>';
					echo '<option selected="selected" value="100">100 Nachrichten</option>';
					echo '<option value="200">200 Nachrichten</option>';
				}
				else
				{
					echo '<option value="25">25 Nachrichten</option>';
					echo '<option value="50">50 Nachrichten</option>';
					echo '<option value="100">100 Nachrichten</option>';
					echo '<option selected="selected" value="200">200 Nachrichten</option>';
				}
			echo '</select></td></tr>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody">Wollen sie Gelesene Nachrichten ausblenden?';
			echo '<td class="tblbody"><select size="Höhe" name="setting_show_read">';
				if($get_settings['ignore_read']==1) 
				{
					echo '<option selected="selected" value="1">Ja</option>';
					echo '<option value="0">Nein</option>';
				}
				else 
				{
					echo '<option value="1">Ja</option>';
					echo '<option selected="selected" value="0">Nein</option>';
				}
			echo '</select></td></tr>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody"">Wollen sie Archivierte Nachrichten einblenden?';
			echo '<td class="tblbody""><select size="Höhe" name="setting_show_archive">';
				if($get_settings['show_archive']==1) 
				{
					echo '<option selected="selected" value="1">Ja</option>';
					echo '<option value="0">Nein</option>';
				}
				else 
				{
					echo '<option value="1">Ja</option>';
					echo '<option selected="selected" value="0">Nein</option>';
				}
			echo '</select></td></tr>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody">Wollen sie Bau und Forsch-Nachrichten ausblenden?';
			echo '<td class="tblbody"><select size="Höhe" name="setting_ignore_build">';
				if($get_settings['ignore_build']==1) 
				{
					echo '<option selected="selected" value="1">Ja</option>';
					echo '<option value="0">Nein</option>';
				}
				else 
				{
					echo '<option value="1">Ja</option>';
					echo '<option value="0" selected="selected">Nein</option>';
				}
			echo '</select></td></tr>';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody">Wollen sie alle Kampf-Berichte extra anzeigen?';
			echo '<td class="tblbody"><select size="Höhe" name="setting_show_fights">';
				if($get_settings['show_fights']==1) 
				{
					echo '<option selected="selected" value="1">Ja</option>';
					echo '<option value="0">Nein</option>';
				}
				else 
				{
					echo '<option value="1">Ja</option>';
					echo '<option value="0" selected="selected">Nein</option>';
				}
			echo '</select></td></tr>';
		echo '<tr class="tblbody">';
		echo '<tr class="tblbody">';
			echo '<td class="tblbody"><input type="submit" name="mess_setting_set" value="Senden">';
	echo '</table>';
	echo '</form>';
}

if (isset($_POST['mess_setting_set'])) 
{
	// Alle Settings in Variablen schreiben damit es später einfacher ist
	$setting_mess_per_page = $_POST['setting_mess_per_page'];
	$setting_show_read = $_POST['setting_show_read'];
	$setting_show_archive = $_POST['setting_show_archive'];
	$setting_ignore_build = $_POST['setting_ignore_build'];
	$setting_show_fights = $_POST['setting_show_fights'];
	$player_id = $player->getID();
	echo "playerid: ".$player_id;
	
	// Wenn der User noch keine Settings geschrieben hat dann INSERT else UPDATE
	if(mysql_num_rows(do_mysql_query("SELECT playerid FROM config_message WHERE playerid =".$player_id))==0)
	{
		$sql = "INSERT 
				INTO config_message (playerid, rows_per_page, ignore_read, show_archive, ignore_build, show_fights) 
				VALUES ('".$player_id."', '".$setting_mess_per_page."', '".$setting_show_read."', '".$setting_show_archive."', '".$setting_ignore_build."', '".$setting_show_fights."')";
	
		do_mysql_query($sql);
	}
	else
	{
		$sql = "UPDATE config_message 
			SET rows_per_page='".$setting_mess_per_page."', ignore_read='".$setting_show_read."', show_archive='".$setting_show_archive."', ignore_build='".$setting_ignore_build."', show_fights='".$setting_show_fights."'
			WHERE playerid=".$player_id;
		
		do_mysql_query($sql);
	}
}	
?>