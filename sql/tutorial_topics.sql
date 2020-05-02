-- phpMyAdmin SQL Dump
-- version 2.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 07. April 2008 um 10:44
-- Server Version: 5.0.32
-- PHP-Version: 5.2.0-8+etch10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `global`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tutorial_topics`
--

CREATE TABLE IF NOT EXISTS `tutorial_topics` (
  ` tut_id` int(11) NOT NULL auto_increment,
  `level` int(11) NOT NULL,
  `sublevel` int(11) NOT NULL,
  `page` varchar(100) collate latin1_german2_ci default NULL,
  `pagetitle` varchar(100) collate latin1_german2_ci default NULL COMMENT 'Titel der Seite, auf der dieses Topic angezeigt wird',
  `tut_text` text collate latin1_german2_ci,
  PRIMARY KEY  (` tut_id`),
  UNIQUE KEY `level` (`level`,`sublevel`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=74 ;

--
-- Daten für Tabelle `tutorial_topics`
--

INSERT INTO `tutorial_topics` (` tut_id`, `level`, `sublevel`, `page`, `pagetitle`, `tut_text`) VALUES
(1, 1, 1, 'main.php|settings.php', NULL, '     Gääääähn\\n\\nWillkommen! Ich bin die Laberbacke, die Dich ins Spiel einführen soll. Unter mir siehst Du Symbole zum Weiter- oder Zurückblättern dieses Textes. Blättere nun einmal weiter!'),
(2, 1, 3, 'main.php|settings.php', NULL, 'Wegschieben kannst Du mich, indem Du auf dem roten Balken über mir die Maustaste gedrückt hältst und mich wegbewegst.\\n\\nMit dem X geh ich Dir kurz aus dem Weg und wenn Du mich garnicht mehr brauchst, dann deaktiviere mich in MyHW2->Spieler.'),
(3, 1, 5, 'main.php|settings.php', NULL, '  Lass uns loslegen! Ich erkläre Dir zunächst die Bedienung: im Prinzip kannst Du überall hinklicken, wo ein Link hervorgehoben ist. An vielen Stellen verstecken sich auch "Tooltips", d.h. Erklärungen, die sichtbar werden, sobald Du die Maus darüber bewegst. Eigentlich sollte jedes Symbol eine solche Erklärung besitzen, wie z.B. die Symbole oben rechts.'),
(4, 1, 7, 'main.php|settings.php', NULL, '  Beginnen wir mit den Grundlagen. Anfangs besitzt Du ein Dorf, das nur spärlich ausgebaut ist. Die Welt wartet darauf, dass Du Sie besiedelst. Klicke einmal auf Kartographie im Menü!'),
(6, 3, 1, 'map.php', NULL, '  Nun befinden wir uns auf der Karte. Hier sieht man Dörfer und ihre Umgebung. Dein Dorf sollte farbig markiert sein und sich links neben mir befinden.\\nDie Umgebung beinflusst die Produktion der Dörfer, dazu aber später mehr.\\nBenutz die Pfeile unter mir!'),
(7, 5, 1, 'research.php', NULL, '  Wir befinden uns nun bei Deinen Forschern. Hier können neue Technologien erforscht werden, die für eine starke Entwicklung Deines Volkes notwendig sind. Benutz wieder die Pfeile unter mir, um mehr zu hören.'),
(8, 5, 3, 'research.php', NULL, '  Ganz unten am Rand des Browserfensters siehst Du Zahlen und auch ein Buch-Symbol. Dies symbolisiert die sogenannten Forschungspunkte. Davon hast Du im Moment nur wenige, die sinnvoll eingesetzt werden wollen. Weiter klicken.'),
(9, 5, 5, 'research.php', NULL, '  Forschungspunkte (genannt FP) erhältst Du z.B. durch den Bau von Schulen und Bibliotheken, aber erst im weiteren Verlauf. Die bereits stehende Residenz in Deiner Stadt bringt Dir ein wenig FP und sichert erstmal Dein Vorankommen.'),
(10, 5, 7, 'research.php', NULL, '  Im Moment ist es am sinnvollsten, wenn Du Ackerbau forschst. Damit steht Dir die Möglichkeit offen, weitere Dörfer zu gründen und Gebäude zur Nahrungsversorgung zu errichten. Achja - je mehr Einwohner ein Dorf hat, desto mehr FP erhältst Du.'),
(11, 5, 9, 'research.php', NULL, '  Klicke auf den schwarzen Pfeil hinter Ackerbau. Die Forschung startet dann und wird in einigen Minuten fertig sein. Wir machen solange woanders weiter.'),
(12, 7, 1, 'research.php.rid=|research.php.started=', NULL, '  Prima! Die erste Forschung läuft. Da das eine Weile dauert, beschäftigen wir uns mit einigen weiteren Grundlagen. Im Finanzhof erfährst Du mehr über die Entwicklung Deines Reiches.'),
(13, 7, 3, 'research.php.rid=|research.php.started=', NULL, '  Klicke im Menü auf Finanzhof.'),
(14, 9, 1, 'kingdom.php', NULL, '  Sieh doch! Soviel Information auf einem Fleck. Sieht kompliziert aus, wird aber mit der Zeit zur Routine. Du kannst mehrere Kategorien auswählen, die Dir Informationen über Deine Dörfer liefern.'),
(15, 9, 3, 'kingdom.php', NULL, '  Im Reiter Stadtverwaltung siehst Du grundlegende Infos zu Deinen Dörfern: Einwohner, Attraktivität, Loyalität, Wohlstand, Nahrung und auch die erwähnten Forschungspunkte (FP).'),
(16, 9, 5, 'kingdom.php', NULL, '  Deine Residenz versorgt derzeit Dein Dorf ganz gut, aber bald benötigst Du mehr Nahrung. In der letzten Spalte siehst Du, wieviel FP dein Dorf pro Tick produziert.'),
(17, 9, 7, 'kingdom.php', NULL, '  Ein Tick bezeichnet die Zeitspanne, bis Du Resourcen bekommst. Wie lange ein Tick dauert, kannst Du in der Legende nachlesen. Ebenso wachsen Deine Einwohner  (oder sie sterben). Im Moment interessiert uns das nicht weiter.'),
(18, 9, 9, 'kingdom.php', NULL, '  Wenn Du mehrere Städte hast, kannst Du diese untem am Rand des Browsers wechseln. Derzeit steht dort wahrscheinlich noch Startstadt 12345 oder ähnliches. Lass uns die Stadt umbenennen! Das machen wir im Rathaus.'),
(19, 9, 11, 'kingdom.php', NULL, '  Klicke auf Rathaus im Menü.'),
(20, 11, 1, 'townhall.php$', NULL, '  Hier kannst Du unter anderem Deine Stadt umbenennen. Weiter unten auf der Seite befindet sich eine Zeile Stadtname, dort schreibst Du den neuen Namen hinein und klickst auf ändern. Tu das nun!'),
(21, 11, 3, 'townhall.php$', NULL, '  Wie gesagt, trag einen neuen Namen ein und klick auf Ändern - oder Return auf der Tastatur.'),
(22, 13, 1, 'townhall.php.*setcityname=.*', NULL, '  Super! Unter dem Eingabefeld für den Stadtnamen sind Suchmöglichkeiten für Spieler, Dörfer und Koordinaten. Das wirst Du später benötigen, im Moment vielleicht nicht.'),
(23, 13, 3, 'townhall.php.*setcityname=.*', NULL, '  Hier kannst Du auch neue Städte gründen, indem Du Siedler sendest. Dazu benötigen wir aber erst die Forschung Ackerbau, die Du vorhin in Auftrag gegeben hast.'),
(24, 13, 5, 'townhall.php.*setcityname=.*', NULL, '  Lass uns mal den Baumeister anschauen!'),
(25, 15, 1, 'buildings.php', NULL, '  So. Hier wird gebaut. Derzeit hast Du nicht viele Möglichkeiten. Falls Ackerbau noch nicht fertig ist, steht derzeit nur die Residenz hier. Die 1 in der ersten Spalte bedeutet, dass eine Residenz gebaut wurde.'),
(26, 15, 3, 'buildings.php', NULL, '  Hinter dem Namen des Gebäudes stehen Kosten für ein weiteres, jeweils in Gold, Holz und Stein. Dahinter die Dauer und zum Schluss weitere Informationen darüber, wieviel Du davon errichten kannst. Eine Residenz besitzt Du bereits.'),
(27, 15, 5, 'buildings.php', NULL, '  Manche Gebäude kannst Du mehrfach errichten, andere wiederum nur einzeln. Dies steht in der Spalte Maximal. Dahinter sind Zahlen, über die Du den Bau in Auftrag gibst.'),
(28, 15, 7, 'buildings.php', NULL, '  Die Residenz kann nur einmal in Deinem Reich gebaut werden und bestimmt Deine Hauptstadt. Sie bringt die noch einige zusätzliche Vorteile. Das schauen wir uns mal in der Bibliothek an.'),
(29, 15, 9, 'buildings.php', NULL, '  Klick auf den Namen der Residenz. So gelangen wir in die Bibliothek.'),
(30, 17, 1, 'library.php.*building_id=1000|library.php.s1=1', NULL, '  Dies ist die Bibliothek! Sieh nur, was hier alles an alten Büchern rumsteht. Soviel Wissen! Nur sehr Wenige kennen alle Geheimnisse...'),
(31, 17, 3, 'library.php.*building_id=1000|library.php.s1=1', NULL, '  Hier steht so einiges drin. Selbst die größten Elitekämpfer müssen regelmässig hier nachschlagen. Stöber ruhig ein bischen umher, es kann nichts schaden.'),
(32, 17, 5, 'library.php.*building_id=1000|library.php.s1=1', NULL, '  Wenn Du fertig bist, dann machen wir weiter.\\nFalls die Bibliothek sich in einem neuen Fenster oder einem Tab Deines Browsers geöffnet hat, dann kannst Du dieses schließen. Kehre dann zum Spiel zurück und klicke erneut auf Forschung.'),
(33, 17, 7, 'library.php.*building_id=1000|library.php.s1=1', NULL, '  Wie gesagt, begib Dich in die Forschungsabteilung des Spiels!'),
(34, 21, 1, 'research.php', NULL, '  Da wären wir wieder. Läuft die Forschung Ackerbau noch? Falls ja, dann kannst Du hier die Restzeit einsehen, die Du noch warten musst. Wenn sie jedoch fertig ist, steht ein Häkchen hinter der Forschung.'),
(35, 21, 3, 'research.php', NULL, '  Weitermachen können wir erst, wenn die Forschung fertig ist. In diesem Fall gehts beim Baumeister weiter.'),
(36, 3, 5, 'map.php', NULL, '  Hier kannst Du auch Deine direkten Nachbarn sehen. Es empfielt sich, mit ihnen frühzeitig in Kontakt zu treten. Scroll ruhig ein bischen auf der Karte herum, um Nachbarn zu entdecken.'),
(37, 3, 7, 'map.php', NULL, '  Wir wollen siedeln... Klick auf Forschungen im Menü!'),
(38, 3, 3, 'map.php', NULL, '  Der Rahmen um die Städte herum definiert das Einflussgebiet. Alle Felder in diesem Gebiet können von der Stadt genutzt werden, indem entsprechenden Gebäude errichtet werden.'),
(39, 5, 11, 'research.php', NULL, '  Na los, forsch Ackerbau! Falls Ackerbau schon geforscht wurde, klick auf das Häkchen, dann gehts weiter.'),
(40, 23, 1, 'buildings.php', NULL, '  Wenn Ackerbau fertig geforscht hat, dann können wir nun Gebäude mit dem Namen Bauerngut errichten. Diese produzieren Nahrung. Wieviel das ist, kannst Du in der Bibliothek nachlesen.'),
(41, 23, 3, 'buildings.php', NULL, '  Da die Residenz nur einen Teil Deiner Einwohner versorgt, benötigen diese bald mehr Nahrung. Daher kann es nicht schaden, EIN Bauerngut zu errichten. Mehr hat keinen Sinn und verschwendet Gold!'),
(42, 23, 5, 'buildings.php', NULL, '  Bau also ein Bauerngut, indem Du auf die 1 (Eins) in der Spalte des Bauernguts klickst. Klick bitte nur EINMAL!'),
(43, 25, 1, 'buildings.php.build=1', NULL, '  Sehr gut! Siehst Du die Liste weiter unten auf der Seite? Dort findest Du die Zeit, bis der Bau abgeschlossen ist. Alles dauert seine Zeit, Geduld ist angebracht. Abbrechen wollen wir allerdings nicht.'),
(44, 25, 3, 'buildings.php.build=1', NULL, '  Achte darauf, dass Gebäude Dich regelmässig Gold kosten. Am Anfang darf man nicht zu stürmisch bauen, sonst ist schnell die Kohle alle. Deshalb auch erstmal nur EIN Bauerngut.'),
(45, 25, 5, 'buildings.php.build=1', NULL, '  Die Forschung Ackerbau ist aber zu noch was gut: man kann neue Städte gründen. Lass uns damit beginnen. Suchen wir uns übers Rathaus eine gute Stelle für eine zweite und dritte Stadt!'),
(46, 25, 7, 'buildings.php.build=1', NULL, '  Geh ins Rathaus!'),
(47, 25, 2, 'buildings.php.build=1', NULL, '  Neben dem Namen Bauerngut steht nun eine grüne Zahl. Diese zeigt Dir, wieviel Gebäude dieses Typs gerade in Bau sind. Hoffentlich steht da ne grüne Eins...'),
(48, 27, 1, 'townhall.php', NULL, '  Da wären wir wieder. Zum Siedeln benötigt man Einwohner und Baumaterial, aber vor allem eine gute Stelle, wo man siedeln kann. Die suchen wir nun.'),
(49, 27, 3, 'townhall.php', NULL, '  Klick ganz unten auf die Schaltfläche GITTER, damit wir danach auf der Karte einen geeigneten Ort finden können. Die andren Felder lässt Du leer, dann klickst Du auf SUCHEN.'),
(50, 27, 5, 'townhall.php', NULL, '  GITTER ankreuzen, SUCHEN klicken!'),
(51, 29, 1, 'map.php.*grid', NULL, '  Durch das Gitter kann man leicht eine Position für ein neues Dorf finden. Siehst Du die Koordinaten auf jedem Wiesenfeld? Die kann man anklicken, worauf eine Schaltfläche erscheint. '),
(53, 29, 5, 'map.php.*grid', NULL, '  Es ist nicht leicht, pauschal eine gute Position zu erklären. Zunächst Grundlagen: es gibt Wiesen, Berge, Wälder und Wasser. Jedes Feld kann ausgebaut werden und Produziert dann Rohstoffe wie Nahrung, Holz, Stein und Eisen.'),
(52, 29, 3, 'map.php.*grid', NULL, '  Klick mal auf eine Wiese mit solchen Koordinaten: es erscheint dann eine Qudrat, dass den Einflussbereich anzeigt, den eine Stadt an dieser Stelle hätte. Wir suchen eine gute Position, also klick erstmal wieder auf NEIN.'),
(54, 29, 7, 'map.php.*grid', NULL, '  Dann gibt es auf manchen Feldern sogenannte Sonder-Resourcen. Diese erlauben es, weitere Spezialgebäude zu errichten. Man sollte also möglichst viele solcher Felder mit dem Einflussgebiet der Stadt abdecken.'),
(55, 29, 9, 'map.php.*grid', NULL, '  Dörfer können nur auf Wiesen gegründet werden. Such nun nach Wiesen und klicke diese an, um eine gute Siedlungsposition zu finden. Achte auf genügend Wälder und Berge, um gute Rohstoffzufuhr zu gewährleisten.'),
(56, 29, 11, 'map.php.*grid', NULL, '  Wenn Du eine gute Position gefunden hast, klicke im erschienenen Quadrat auf JA. Damit gelangen wir wieder ins Rathaus, um Siedler loszusenden. Du kannst übrigens nur im Heimatsektor siedeln.'),
(57, 29, 13, 'map.php.*grid', NULL, '  Na los! Klick mehrer Wiesen an und suche Dir eine aus, auf der Du eine Stadt gründen möchtest. Bestätige die Schaltfläche zum Siedeln.'),
(58, 31, 1, 'townhall.php.newsettle=true', NULL, '  Wieder zurück im Rathaus. Die Koordinaten des ausgewählten Wiesenfeldes sollten bereits eingetragen sein. Wir müssen nun nur noch entscheiden, wieviel Mann wir entsenden.'),
(59, 31, 3, 'townhall.php.newsettle=true', NULL, '  Es hat keinen Sinn, mehr als 50 Mann zu senden - denn soviel Einwohner haben wir ja garnicht. Trag 50 Mann ein und klicke auf AUFBRECHEN. '),
(60, 31, 5, 'townhall.php.newsettle=true', NULL, '  Sollte es zu einer Fehlermeldung kommen, dann suche eine andrere Position aus und wiederhole den Vorgang, bis es geklappt hat.'),
(61, 31, 7, 'townhall.php.newsettle=true', NULL, '  Im Moment kannst Du übrigens drei Dörfer verwalten. Gründe keinesfalls mehr!'),
(62, 33, 1, 'townhall.php.*settler=.*settle=', NULL, '  Hat es funktioniert? Wenn nicht, dann gehe zurück auf die Karthographie und such Dir eine andre Stelle aus. Klick einfach im Menü auf Karthographie, das Gitter sollte noch vorhanden sein.'),
(63, 33, 3, 'townhall.php.*settler=.*settle=', NULL, '  Falls es geklappt hat, wiederhole das Ganze für eine zweite Dorfgründung! Achte darauf, dass die zweite Gründung sich nicht mit der ersten überschneidet. Auch dafür gilt: einfach im Menü auf Karthographie klicken.'),
(64, 33, 5, 'townhall.php.*settler=.*settle=', NULL, '  Sind zwei Siedlungstrupps unterwegs, dann kannst Du Dir diese im Generalstab anschauen. Klick dazu im Menü auf Generalstab.'),
(65, 33, 7, 'townhall.php.*settler=.*settle=', NULL, '  Also: insgesamt zwei Siedlungstrupps entsenden, dann auf Generalstab klicken!'),
(66, 29, 4, 'map.php.*grid', NULL, '  Die Einflussgebiete von Dörfern dürfen sich NICHT überschneiden. Jede Stadt zieht ein Gebiet von 5x5 Feldern ein, wobei sie in der Mitte platziert ist. Das siehst Du ja hier auf der Karte.'),
(67, 35, 1, 'general.php', NULL, '  Da das hier ein Kriegsspiel ist, wirst Du Dich mit diesem Teil bald auseinander setzen müssen. Im Moment kannst Du reichlich wenig ausrichten, da Du ohne Militär nicht losschlagen kannst.'),
(68, 35, 3, 'general.php', NULL, '  Hier siehst Du jetzt auch die Siedlungstrupps, sofern alles richtig gelaufen ist. Diese Bauern sind langsam, es dauert also ein wenig, bis die Dörfer gegründet sind.'),
(69, 35, 5, 'general.php', NULL, '  Die Zeit solltest Du nutzen, indem Du Dich in den Chat begibst, und ein wenig mit erfahrenen Spielern plauderst. Die helfen Dir dann weiter.'),
(70, 35, 7, 'general.php', NULL, '  Die Grundlagen kennst Du nun. Klick auf CHAT und versuche, mit anderen Spielern in Kontakt zu treten! Auf der Suche nach einem Orden ist das sehr wichtig!'),
(71, 37, 1, 'chat.php', NULL, '  Du siehst hier zwei Möglichkeiten, dem Chat beizuwohnen. Eine komfortable Möglichkeit ist der Java-Chat, der aber nicht auf jedem System läuft. Sollte das der Fall sein, dann verwende den rechten Chat.'),
(72, 37, 3, 'chat.php', NULL, '  Versuche nun, mit einem der beiden Chats zurecht zu kommen. Glaube mir - es macht Spass!'),
(73, 37, 5, 'chat.php', NULL, '  Wenn Du mich deaktivieren willst, kannst Du das in MyHW2 unter dem Reiter Spiel machen. Klicke dort auf Tutorial deaktivieren.');
