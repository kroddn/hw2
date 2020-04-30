<?php
// Start von Funktion um alle Files einzulesen
$fileData = fillArrayWithFileNodes( new DirectoryIterator( 'D:\Webhosting\Xampp\htdocs\hw2\sql' ) );

//Funktion um alle Files einzulesen
function fillArrayWithFileNodes( DirectoryIterator $dir )
{
	//Definition Array
	$data = array();
	foreach ( $dir as $node )
	{
		// erste IF für Unterordner - Nicht probiert - Lieber ohne Unterordner
		if ( $node->isDir() && !$node->isDot() )
		{
			$data[$node->getFilename()] = fillArrayWithFileNodes( new DirectoryIterator( $node->getPathname() ) );
		}
		// Falls gefundener Knoten eine Datei ist in Array reinschreiben
		else if ( $node->isFile() )
		{
			// Dateipfad Manuell eingeben und Filename einlesen
			$data[] = 'D:/Webhosting/Xampp/htdocs/hw2/sql/' . $node->getFilename();
		}
	}
	return $data;
}

// MYSQL Daten
// MySQL host
$mysql_host = '127.0.0.1';
// MySQL username
$mysql_username = 'root';
// MySQL password
$mysql_password = '';
// Database name
$mysql_database = 'hw2';

// Connect to MySQL server
mysqli_connect($mysql_host, $mysql_username, $mysql_password) or die('Error connecting to MySQL server: ' . mysqli_error($GLOBALS['con']));
// Select database
mysqli_select_db($mysql_database) or die('Error selecting MySQL database: ' . mysqli_error($GLOBALS['con']));

// Zähler von einzulesenen Files
$fileDataCount = 0;

echo 'Files to Import to MySQL: <br />';

// Ausgabe von allen einzulesenen Files
foreach ($fileData as $value)
{
	$fileDataCount = $fileDataCount + 1;
	echo $fileDataCount . '. File: ' . $value . '<br />';
}

// Eigentliche Ausführung von den Files
foreach ($fileData as $filename)
{
	// Temporäre Variable um die Abfrage zu speichern
	$templine = '';
	// Die ganze Datei auslesen 
	$lines = file($filename);
	// Jede Line durch loopen
	foreach ($lines as $line)
	{
		// Überspringen falls Kommentar
		if (substr($line, 0, 2) == '--' || $line == '')
			continue;

		// Derzeitige Line zum Segment hinzufügen
		$templine .= $line;
		// Falls Semikolon ist dies das Ende der Abfrage
		if (substr(trim($line), -1, 1) == ';')
		{
			// Abfrage ausführen
			mysqli_query($GLOBALS['con']$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($GLOBALS['con']) . '<br /><br />');
			// Temporäre Variable zurücksetzen
			$templine = '';
		}
	}
}
// Ausgabe wie viele Dateien Erfolgreich waren - Sehr simpel gehalten
echo ' All ' . $fileDataCount . ' SQL-Files imported successfully';
?>