-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 09. Juni 2015 um 19:13
-- Server Version: 5.1.72
-- PHP-Version: 5.3.3-7+squeeze17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;

--
-- Datenbank: `hw2_game1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `research`
--

CREATE TABLE IF NOT EXISTS `research` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `description` text COLLATE latin1_german2_ci,
  `rp` int(10) NOT NULL DEFAULT '0',
  `time` int(10) NOT NULL DEFAULT '0',
  `religion` tinyint(3) DEFAULT NULL,
  `points` int(10) NOT NULL DEFAULT '0',
  `typ` tinyint(3) NOT NULL DEFAULT '0',
  `typlevel` tinyint(3) NOT NULL DEFAULT '0',
  `category` tinyint(3) NOT NULL DEFAULT '0',
  `management` tinyint(3) DEFAULT NULL,
  `lib_link` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=267 ;

--
-- Daten für Tabelle `research`
--

INSERT INTO `research` (`id`, `name`, `description`, `rp`, `time`, `religion`, `points`, `typ`, `typlevel`, `category`, `management`, `lib_link`) VALUES
(1, 'Leibeigenschaft (1)', 'Ermöglicht die effiziente Verwaltung von 3 Städten', 0, 0, NULL, 0, 0, 0, 0, 1, '?s1=3&s2=0&s3=0'),
(2, 'Lehenswesen (2)', 'Ermöglicht die effiziente Verwaltung von 5 Städten', 2500, 36000, NULL, 2000, 0, 1, 0, 2, '?s1=3&s2=0&s3=1'),
(3, 'Holzabbau', 'Ermöglicht die Erstellung von <B>Holzfällern</B>', 6, 1200, NULL, 0, 1, 0, 1, NULL, '?s1=3&s2=1&s3=0'),
(4, 'Steinabbau (für den Norden)', 'Ermöglicht die Erstellung von <B>kleinen Steinbrüchen</B>', 6, 1200, NULL, 0, 3, 0, 1, NULL, '?s1=3&s2=1&s3=5'),
(5, 'Ackerbau', 'Nach der Erforschung von Ackerbau ist es erstmals möglich, Einwohner zur <b>Gründung neuer Siedlungen</b> innerhalb des Heimatsektors zu entsenden.', 8, 1500, NULL, 0, 4, 0, 1, NULL, '?s1=3&s2=1&s3=10'),
(6, 'Schulbildung', 'Das Gebäude <B>kleine Schule</B> kann gebaut werden', 25, 1800, NULL, 0, 5, 0, 2, NULL, '?s1=3&s2=2&s3=0'),
(7, 'Lagerräume', 'Ermöglicht das Bauen von <B>Lagerräumen</B>', 100, 5400, NULL, 20, 6, 0, 1, NULL, '?s1=3&s2=1&s3=15'),
(8, 'Gebetshäuser', 'Das Gebäude <B>Kapelle/Gebetsraum</B> kann gebaut werden', 400, 14400, NULL, 300, 7, 0, 2, NULL, '?s1=3&s2=2&s3=5'),
(9, 'Gasthäuser', 'Das Gebäude <B>Wirtshaus</B> bzw. <B>Kaffeehaus</B> kann gebaut werden', 200, 10800, NULL, 200, 8, 0, 2, NULL, '?s1=3&s2=2&s3=10'),
(10, 'Märkte', 'Der <B>Marktplatz/Basar</B> kann gebaut werden. Ausserdem ist dann der Marktplatz freigeschalten.', 500, 14400, NULL, 350, 9, 0, 2, NULL, '?s1=3&s2=2&s3=15'),
(11, 'Speertruppen', 'Neue Einheit <B>Speerträger/Abessinen</b>', 200, 7200, NULL, 300, 10, 0, 3, NULL, '?s1=3&s2=3&s3=0'),
(12, 'Kampfbögen', 'Neue Einheit <B>Bogenschützen/Turkmenen</B>', 400, 9000, NULL, 350, 11, 0, 3, NULL, '?s1=3&s2=3&s3=3'),
(13, 'Leichte Reiterei', 'Neue Einheit <B>Berittene Hauptmänner/Faris</B>', 500, 10800, NULL, 400, 12, 0, 3, NULL, '?s1=3&s2=3&s3=7'),
(14, 'Schmiedekunst', 'Neue Waffenproduktion <B>Schwertschmied und Rüstungsschmied</B> ', 1000, 14400, NULL, 500, 13, 0, 3, NULL, '?s1=3&s2=3&s3=10'),
(15, 'Bogenbau', 'Neue Waffenproduktion <B>Bogner</B>', 1000, 14400, NULL, 500, 14, 0, 3, NULL, '?s1=3&s2=3&s3=13'),
(16, 'Pferdezucht', 'Neue Ausbildung <B>Pferdezüchter</B> ', 1000, 14400, NULL, 500, 15, 0, 3, NULL, '?s1=3&s2=3&s3=17'),
(17, 'Militärische Ausbildung', 'Neues Militärgebäude <B>kleine Kaserne</B>', 500, 9000, NULL, 350, 16, 0, 3, NULL, '?s1=3&s2=3&s3=20'),
(18, 'Erkundung', 'Neues Militärgebäude <B>Aussichtsturm</B>', 1000, 14400, NULL, 500, 17, 0, 3, NULL, '?s1=3&s2=3&s3=23'),
(19, 'Verteidigungsanlagen', 'Ermöglicht den Bau einer <B>Steinmauer</B> um die Städte', 1000, 14400, NULL, 500, 18, 0, 4, NULL, '?s1=3&s2=4&s3=0'),
(20, 'Burgenbau', 'Ermöglicht den ersten Schritt des Ausbaus einer Stadt zur Festung', 12000, 43200, NULL, 1200, 19, 0, 4, NULL, '?s1=3&s2=4&s3=1'),
(21, 'Wildjagd', 'Ermöglicht die Erstellung von <B>Jägern</B>', 45, 1800, NULL, 30, 20, 0, 1, NULL, '?s1=3&s2=1&s3=18'),
(22, 'Fischfang', 'Ermöglicht die Erstellung von <B>Fischern</B>', 45, 1800, NULL, 30, 21, 0, 1, NULL, '?s1=3&s2=1&s3=23'),
(23, 'Feudalismus (3)', 'Ermöglicht die effiziente Verwaltung von 7 Städten', 25000, 72000, NULL, 2500, 0, 2, 0, 3, '?s1=3&s2=0&s3=2'),
(24, 'Holzfälleräxte', 'Ermöglicht die Erstellung von <B>Holzfällern (verbessert)</B>', 2200, 18000, NULL, 220, 1, 1, 1, NULL, '?s1=3&s2=1&s3=1'),
(25, 'Brechwerkzeuge (für den Norden)', 'Ermöglicht die Erstellung von <B>kleinen Steinbrüchen (verbessert)</B>', 2200, 18000, NULL, 220, 3, 1, 1, NULL, '?s1=3&s2=1&s3=6'),
(26, 'Bergbau', 'Ermöglicht die Erstellung von <B>kleinen Eisenminen</B>', 200, 3600, NULL, 150, 22, 0, 1, NULL, '?s1=3&s2=1&s3=28'),
(27, 'Bergbauwerkzeuge', 'Ermöglicht die Erstellung von <B>kleinen Eisenminen (verbessert)</B>', 3000, 28800, NULL, 300, 22, 1, 1, NULL, '?s1=3&s2=1&s3=29'),
(28, 'Goldabbau', 'Ermöglicht die Erstellung von <B>kleinen Goldminen</B>', 2600, 25200, NULL, 260, 23, 0, 1, NULL, '?s1=3&s2=1&s3=33'),
(29, 'Edelsteinabbau', 'Ermöglicht die Erstellung von <B>kleinen Edelsteinmine</B>', 2600, 25200, NULL, 260, 24, 0, 1, NULL, '?s1=3&s2=1&s3=35'),
(30, 'Getreidezucht', 'Ermöglicht die Erstellung von <B>kleinen Brauereien</B>', 2500, 25200, NULL, 250, 25, 0, 1, NULL, '?s1=3&s2=1&s3=37'),
(31, 'Pflüge', 'Ermöglicht die Erstellung von <B>Bauerngütern (verbessert)</B>', 2000, 21600, NULL, 200, 4, 1, 1, NULL, '?s1=3&s2=1&s3=11'),
(32, 'Weinanbau', 'Ermöglicht die Erstellung von <B>kleinen Weingütern</B>', 2500, 25200, NULL, 250, 26, 0, 1, NULL, '?s1=3&s2=1&s3=39'),
(33, 'Klassensystem', 'Das Gebäude <B>grosse Schule</B> kann gebaut werden', 1500, 28800, NULL, 750, 5, 1, 2, NULL, '?s1=3&s2=2&s3=1'),
(34, 'Medizinische Versorgung', 'Das Gebäude <B>Medikus</B> kann gebaut werden', 3000, 28800, NULL, 300, 8, 1, 2, NULL, '?s1=3&s2=2&s3=11'),
(35, 'Gildenhandel', 'Das Gebäude <B>Handelsgilde</B> kann gebaut werden', 4000, 36000, NULL, 400, 9, 1, 2, NULL, '?s1=3&s2=2&s3=16'),
(37, 'Christliche Gemeindeverwaltung', NULL, 9000, 36000, 1, 900, 27, 1, 4, NULL, '?s1=3&s2=4&s3=6'),
(38, 'Islamische Gemeindeverwaltung', NULL, 9000, 36000, 2, 900, 27, 1, 4, NULL, '?s1=3&s2=4&s3=7'),
(39, 'Kräuterkunde', 'Das Gebäude <B>Kräutersammler</B> kann gebaut werden', 2700, 25200, NULL, 270, 29, 0, 1, NULL, '?s1=3&s2=2&s3=20'),
(40, 'Jagdhunde', 'Ermöglicht die Erstellung von <B>Jägern (verbessert)</B>', 2000, 25200, NULL, 200, 20, 1, 1, NULL, '?s1=3&s2=1&s3=19'),
(41, 'Pelzjagd', 'Ermöglicht die Erstellung von <B>Pelzjägern</B>', 2400, 25200, NULL, 240, 30, 0, 1, NULL, '?s1=3&s2=1&s3=41'),
(42, 'Fischerboote', 'Ermöglicht die Erstellung von <B>Fischern (verbessert)</B>', 2000, 25200, NULL, 200, 21, 1, 1, NULL, '?s1=3&s2=1&s3=24'),
(43, 'Fischzucht', 'Ermöglicht die Erstellung von <B>kleinen Fischzüchtereien</B>', 2500, 25200, NULL, 250, 31, 0, 1, NULL, '?s1=3&s2=1&s3=43'),
(44, 'Perlenfang', 'Ermöglicht die Erstellung von <B>Perlenfischern</B>', 2500, 25200, NULL, 250, 32, 0, 1, NULL, '?s1=3&s2=1&s3=45'),
(45, 'Christliche Stadtverwaltung', NULL, 30000, 129600, 1, 3000, 27, 2, 4, NULL, '?s1=3&s2=4&s3=8'),
(46, 'Islamische Stadtverwaltung', NULL, 30000, 129600, 2, 3000, 27, 2, 4, NULL, '?s1=3&s2=4&s3=9'),
(48, 'Christliche Länderverwaltung', NULL, 60000, 259200, 1, 6000, 27, 3, 4, NULL, '?s1=3&s2=4&s3=10'),
(36, 'Dorfzentren', NULL, 1000, 14400, NULL, 500, 27, 0, 4, NULL, NULL),
(49, 'Christliche Prunkbauten', NULL, 150000, 604800, 1, 15000, 27, 4, 4, NULL, '?s1=3&s2=4&s3=12'),
(50, 'Islamische Prunkbauten', NULL, 150000, 604800, 2, 15000, 27, 4, 4, NULL, '?s1=3&s2=4&s3=13'),
(51, 'Kräuteranbau', 'Das Gebäude <B>Kräutergärtner</B> kann gebaut werden', 39000, 151200, NULL, 3900, 29, 1, 1, NULL, '?s1=3&s2=2&s3=21'),
(52, 'Gruppenjagd', 'Ermöglicht die Erstellung von <B>Jägertrupps</B>', 9000, 36000, NULL, 900, 20, 2, 1, NULL, '?s1=3&s2=1&s3=20'),
(53, 'Jagdbögen', 'Ermöglicht die Erstellung von <B>Jägertrupps (verbessert)</B>', 30000, 129600, NULL, 3000, 20, 3, 1, NULL, '?s1=3&s2=1&s3=21'),
(54, 'Jägergilden', 'Ermöglicht die Erstellung von <B>Jägermeistern</B>', 50000, 216000, NULL, 5000, 20, 4, 1, NULL, '?s1=3&s2=1&s3=22'),
(55, 'Großtierjagd', 'Ermöglicht die Erstellung von <B>Großwildjägern</B>', 34000, 151200, NULL, 3400, 30, 1, 1, NULL, '?s1=3&s2=1&s3=42'),
(56, 'Zuchtteiche', 'Ermöglicht die Erstellung von <B>großen Fischzüchtereien</B>', 35000, 151200, NULL, 3500, 31, 1, 1, NULL, '?s1=3&s2=1&s3=44'),
(57, 'Fischereien', 'Ermöglicht die Erstellung von <B>Fischereien', 9000, 36000, NULL, 900, 21, 2, 1, NULL, '?s1=3&s2=1&s3=25'),
(58, 'Fischernetze', 'Ermöglicht die Erstellung von <B>Fischereien (verbessert)', 25000, 108000, NULL, 2500, 21, 3, 1, NULL, '?s1=3&s2=1&s3=26'),
(59, 'Hoheitsrechte', 'Ermöglicht die Erstellung von <B>Großfischereien</B>', 50000, 216000, NULL, 5000, 21, 4, 1, NULL, '?s1=3&s2=1&s3=27'),
(60, 'Perlenzucht', 'Ermöglicht die Erstellung von <B>Perlenfischereien</B>', 35000, 151200, NULL, 3500, 32, 1, 1, NULL, '?s1=3&s2=1&s3=46'),
(47, 'Islamische Länderverwaltung', NULL, 60000, 259200, 2, 6000, 27, 3, 4, NULL, '?s1=3&s2=4&s3=11'),
(61, 'Holzfällergemeinschaften', 'Ermöglicht die Erstellung von <B>Holzfällertrupps</B>', 9500, 36000, NULL, 950, 1, 2, 1, NULL, '?s1=3&s2=1&s3=2'),
(62, 'Baumsägen', 'Ermöglicht die Erstellung von <B>Holzfällertrupps (verbessert)</B>', 27500, 129600, NULL, 2750, 1, 3, 1, NULL, '?s1=3&s2=1&s3=3'),
(63, 'Holzfällersiedlungen', 'Ermöglicht die Erstellung von <B>Holzfällerlagern</B>', 54000, 216000, NULL, 5400, 1, 4, 1, NULL, '?s1=3&s2=1&s3=4'),
(64, 'Etagensysteme', NULL, 9500, 36000, NULL, 950, 3, 2, 1, NULL, '?s1=3&s2=1&s3=7'),
(65, 'Transporttechniken', 'Ermöglicht die Erstellung von <B>großen Steinbrüchen (verbessert)</B>', 27500, 129600, NULL, 2750, 3, 3, 1, NULL, '?s1=3&s2=1&s3=8'),
(266, 'Förderwerke', 'Ermöglicht die Erstellung von <B>Lehmförderwerken</B>', 54000, 216000, NULL, 5400, 3, 4, 1, NULL, '?s1=3&s2=1&s3=9'),
(67, 'Verbesserte Metalltrennung', 'Ermöglicht die Erstellung von <B>großen Goldminen</B>', 38000, 151200, NULL, 3800, 23, 1, 1, NULL, '?s1=3&s2=1&s3=34'),
(68, 'Stollennetze', 'Ermöglicht die Erstellung von <B>großen Eisenminen</B>', 12000, 43200, NULL, 1200, 22, 2, 1, NULL, '?s1=3&s2=1&s3=30'),
(69, 'Stollenentwässerung', 'Ermöglicht die Erstellung von <B>großen Eisenminen (verbessert)</B>', 30000, 129600, NULL, 3000, 22, 3, 1, NULL, '?s1=3&s2=1&s3=31'),
(70, 'Tiefenbergbau', 'Ermöglicht die Erstellung von <B>tiefen Eisenminen</B>', 60000, 259200, NULL, 6000, 22, 4, 1, NULL, '?s1=3&s2=1&s3=32'),
(71, 'Verbesserte Fundtechniken', 'Ermöglicht die Erstellung von <B>großen Edelsteinminen</B>', 38000, 151200, NULL, 3800, 24, 1, 1, NULL, '?s1=3&s2=1&s3=36'),
(72, 'Braurechte', 'Ermöglicht die Erstellung von <B>großen Brauereien</B> ', 37000, 151200, NULL, 3700, 25, 1, 1, NULL, '?s1=3&s2=1&s3=38'),
(73, 'Tierzucht', 'Ermöglicht die Erstellung von <B>Farmen</B>', 9000, 36000, NULL, 900, 4, 2, 1, NULL, '?s1=3&s2=1&s3=12'),
(74, 'Tierställe', 'Ermöglicht die Erstellung von <B>Farmen (verbessert)</B> ', 25000, 108000, NULL, 2500, 4, 3, 1, NULL, '?s1=3&s2=1&s3=13'),
(75, 'Dreifelderwirtschaft', 'Ermöglicht die Erstellung von <B>Großfarmen</B>', 50000, 216000, NULL, 5000, 4, 4, 1, NULL, '?s1=3&s2=1&s3=14'),
(76, 'Rebzucht', 'Ermöglicht die Erstellung von <B>großen Weingütern</B>', 37000, 151200, NULL, 3700, 26, 1, 1, NULL, '?s1=3&s2=1&s3=40'),
(77, 'Lagergebäude', 'Ermöglicht das Bauen von <B>kleinen Lagern</B>', 9000, 36000, NULL, 900, 6, 1, 1, NULL, '?s1=3&s2=1&s3=16'),
(78, 'Lagerkomplexe', 'Ermöglicht das Bauen von </B>großen Lagern</B>', 80000, 216000, NULL, 8000, 6, 2, 1, NULL, '?s1=3&s2=1&s3=17'),
(79, 'Bibliotheken', '', 10000, 37200, NULL, 1000, 5, 2, 2, NULL, '?s1=3&s2=2&s3=2'),
(80, 'Höhere Bildung', '', 25000, 108000, NULL, 2500, 5, 3, 2, NULL, '?s1=3&s2=2&s3=3'),
(81, 'Fachrichtungen', '', 50000, 216000, NULL, 5000, 5, 4, 2, NULL, '?s1=3&s2=2&s3=4'),
(82, 'Kirchen', 'Das Gebäude <B>Kirche</B> kann gebaut werden', 16000, 86400, 1, 1600, 7, 1, 2, NULL, '?s1=3&s2=2&s3=6'),
(83, 'Inquisition', 'Das Gebäude <B>Dom</B> kann gebaut werden', 65000, 259200, 1, 6500, 7, 2, 2, NULL, '?s1=3&s2=2&s3=8'),
(84, 'Moscheen', 'Das Gebäude <B>kleine Moschee</B> kann gebaut werden', 16000, 86400, 2, 1600, 7, 1, 2, NULL, '?s1=3&s2=2&s3=7'),
(85, 'Dschihad', NULL, 65000, 259200, 2, 6500, 7, 2, 2, NULL, '?s1=3&s2=2&s3=9'),
(86, 'Schauspielkunst', 'Das Gebäude <B>Theater</B> kann gebaut werden', 11000, 37200, NULL, 1100, 8, 2, 2, NULL, '?s1=3&s2=2&s3=12'),
(87, 'Öffentliche Badeanstalten', 'Das Gebäude <B>Badehaus</B> kann gebaut werden', 28000, 129600, NULL, 2800, 8, 3, 2, NULL, '?s1=3&s2=2&s3=13'),
(88, 'Erholungseinrichtungen', 'Der <B>Stadtpark</B> kann gebaut werden', 55000, 216000, NULL, 5500, 8, 4, 2, NULL, '?s1=3&s2=2&s3=14'),
(89, 'Geldverleih', 'Das Gebäude <B>Geldleiher</B> kann gebaut werden', 13000, 64800, NULL, 1300, 9, 2, 2, NULL, '?s1=3&s2=2&s3=17'),
(90, 'Handelszentren', 'Das Gebäude <B>Handelszentrum</B> kann gebaut werden', 35000, 151200, NULL, 3500, 9, 3, 2, NULL, '?s1=3&s2=2&s3=18'),
(91, 'Banken', 'Das Gebäude <B>Bankhaus</B> kann gebaut werden', 61000, 259200, NULL, 6100, 9, 4, 2, NULL, '?s1=3&s2=2&s3=19'),
(92, 'Monarchie (4)', 'Ermöglicht die effiziente Verwaltung von 9 Städten', 80000, 144000, NULL, 8000, 0, 3, 0, 4, '?s1=3&s2=0&s3=3'),
(93, 'Konstitutionelle Monarchie (5)', 'Ermöglicht die effiziente Verwaltung von 11 Städten', 160000, 360000, NULL, 16000, 0, 4, 0, 5, '?s1=3&s2=0&s3=4'),
(94, 'Schwere Langwaffen', '', 10000, 36000, NULL, 1000, 10, 1, 3, NULL, '?s1=3&s2=3&s3=1'),
(95, 'Elitetruppen', 'Neue Einheit <B>Panzerikeniere/Janitscharen-Infanterie</B>', 55000, 216000, NULL, 5500, 10, 2, 3, NULL, '?s1=3&s2=3&s3=2'),
(96, 'Verbesserte Schmiedetechniken', 'Neue Waffenproduktion <B>Schwertschmiede und Rüstungsschmiede</B>   ', 18000, 86400, NULL, 1800, 13, 1, 3, NULL, '?s1=3&s2=3&s3=11'),
(97, 'Schwere Waffen und Rüstungen', 'Neuartige Schmiedemethoden und gehärteter Stahl ermöglichen die Konstruktion neuartiger und fast unverwüstlicher Rüstungen', 75000, 237600, NULL, 7500, 13, 2, 3, NULL, '?s1=3&s2=3&s3=12'),
(98, 'Armbrüste', 'Armbrüste nutzen ein ähnliches Prinzip wie Bogen. Es werden jedoch Bolzen statt Pfeile verwendet, die zunächst in eine Vorrichtung eingespannt werden. Das Nachladen von Armbrüsten ist komplizierter und dauert länger, jedoch wird das durch die höhere Präzision beim Zielen wieder ausgeglichen.', 12000, 37200, 1, 1200, 11, 1, 3, NULL, '?s1=3&s2=3&s3=4'),
(99, 'Kompositionsbögen', 'Neue Einheit <B>Janitscharen-Bogenschützen</B>', 12000, 37200, 2, 1200, 11, 2, 3, NULL, '?s1=3&s2=3&s3=5'),
(100, 'Schwere Armbrüste', 'Armbrüste wurden stets verbessert und perfektioniert. Schwere Armbrüste haben ein besseres Spannwerk und ermöglichen so eine noch zielgenaueres und wuchtigeres Abfeuern von Bolzen.', 63000, 259200, 1, 6300, 11, 3, 3, NULL, '?s1=3&s2=3&s3=6'),
(101, 'Verbesserte Schusswaffen', '', 18000, 86400, NULL, 1800, 14, 1, 3, NULL, '?s1=3&s2=3&s3=14'),
(102, 'Schwere Schusswaffen', '', 75000, 237600, NULL, 7500, 14, 2, 3, NULL, '?s1=3&s2=3&s3=15'),
(103, 'Feuerwaffen', 'Mit Perfektionierung der Herstellung von Schwarzpulver gelingt der Einsatz dieser neuen Art von Waffen.', 63000, 259200, 2, 6300, 14, 3, 3, NULL, '?s1=3&s2=3&s3=16'),
(104, 'Gepanzerte Reiterei', 'Neue Einheit <B>Feudalritter/Ghulam</B>', 15000, 64800, NULL, 1500, 12, 1, 3, NULL, '?s1=3&s2=3&s3=8'),
(105, 'Schwere Reiterei', 'Neue Einheit <B>Königliche Ritter/Sipahi</B>', 70000, 237600, NULL, 7000, 12, 2, 3, NULL, '?s1=3&s2=3&s3=9'),
(106, 'Angriffs- und Verteidigungstechniken', 'Neues Militärgebäude <B>große Kaserne</B> ', 15000, 64800, NULL, 1500, 16, 1, 3, NULL, '?s1=3&s2=3&s3=21'),
(107, 'Ordensausbildung', 'Neues Militärgebäude <B>Ordenskaserne</B>  ', 65000, 259200, NULL, 6500, 16, 2, 3, NULL, '?s1=3&s2=3&s3=22'),
(108, 'Verbesserte Zuchttechniken', 'Neue Ausbildung <B>Pferdezüchterei</B>  ', 18000, 86400, NULL, 1800, 15, 1, 3, NULL, '?s1=3&s2=3&s3=18'),
(109, 'Militärzucht', 'Neue Ausbildung <B>Pferdezuchtmeister</B>', 75000, 259200, NULL, 7500, 15, 2, 3, NULL, '?s1=3&s2=3&s3=19'),
(110, 'Erkundungstruppen', 'Neue Einheit <B>Kundschafter</B>', 15000, 64800, NULL, 1500, 17, 1, 3, NULL, '?s1=3&s2=3&s3=24'),
(111, 'Geheimdienst', 'Neues Gebäude <B>Aufklärungszentrale</B> ', 70000, 237600, NULL, 7000, 17, 2, 3, NULL, '?s1=3&s2=3&s3=25'),
(112, 'Festungsbau', 'Ermöglicht den Ausbau zu Festungen', 50000, 151200, NULL, 5000, 19, 1, 4, NULL, '?s1=3&s2=4&s3=3'),
(113, 'Verbesserte Festungsanlagen', NULL, 120000, 432000, NULL, 12000, 19, 2, 4, NULL, '?s1=3&s2=4&s3=4'),
(114, 'Pechnasen', NULL, 250000, 864000, NULL, 25000, 19, 3, 4, NULL, '?s1=3&s2=4&s3=5'),
(115, 'Mauerzinnen', NULL, 12500, 43200, NULL, 1250, 33, 0, 4, NULL, '?s1=3&s2=4&s3=14'),
(116, 'Festungsmauern', NULL, 75000, 237600, NULL, 7500, 33, 1, 4, NULL, '?s1=3&s2=4&s3=15'),
(117, 'Ordensführung', 'Diese Forschung wird nur für zukünftige Führungspositionen in Eurem Orden benötigt. Ihr lernt psychologische wie auch wirtschaftliche Aspekte der enormen Herausforderung einen Orden zu leiten.<br>Nur werdende Ordensgründer und solche, die Ministerposten innehalten wollen, müssen dieses Gebiet erforschen!', 3000, 18000, NULL, 1000, 1, 0, 0, NULL, '?s1=3&s2=0&s3=5'),
(204, 'Lehmgewinnung (für den Süden)', 'Ermöglicht die Erstellung von <B>kleinen Lehmgruben</B>', 6, 1200, NULL, 0, 3, 0, 1, NULL, '?s1=3&s2=1&s3=5'),
(66, 'Steinbergbau', 'Ermöglicht die Erstellung von <B>Steinbergwerken</B>', 54000, 216000, NULL, 5400, 3, 4, 1, NULL, '?s1=3&s2=1&s3=9'),
(225, 'Grubenwerkzeuge (für den Süden)', 'Ermöglicht die Erstellung von <B>kleinen Lehmgruben (verbessert)</B>', 2200, 18000, NULL, 220, 3, 1, 1, NULL, '?s1=3&s2=1&s3=6'),
(264, 'Schienensystem', NULL, 9500, 36000, NULL, 950, 3, 2, 1, NULL, '?s1=3&s2=1&s3=7'),
(265, 'Förderkräne', 'Ermöglicht die Erstellung von <B>großen Lehmgruben (verbessert)</B>', 27500, 129600, NULL, 2750, 3, 3, 1, NULL, '?s1=3&s2=1&s3=8');
