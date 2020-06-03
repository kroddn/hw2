<?php
// ACHTUNG! Diese Datei nur auf dem Server editieren!
// NICHT überschreiben mit lokaler Kopier!!!
// umzubenennen in db.config.php

// Voranmeldung gibts nicht mehr
define("BOOKING_ALLOWED", 0);

//define("MAP_DEFAULT", "kanada.png");

// Karte vor dem 14. Maerz 2009
//define("MAP_DEFAULT", "map_marmarameer_400x400.png");
//define("SPECIAL_DEFAULT", "speed_special_default.png");

// ab 14.03.2009
define("MAP_DEFAULT",     "map_shinta_europa_400x400.png");
define("SPECIAL_DEFAULT", "special_shinta_europa_400x400.png");
define("REGISTER_MAP",    "registermap_shinta_europa_400x400.jpg");
define("WORLD_MAP",       "clanmap_shinta_europa_400x400.jpg");

// Default Session Time
define("SESSION_DEFAULT_TIME", 3600);

// Test aktivieren
define("TEST_MODE", 0);

// Apocalypse. Test-Mode wird allerdings noch vorher gewertet
define("APOCALYPSE", 0);

// Speed aktivieren
define("SPEED", 1);
define("HISPEED", 1);

// DEV setzen
//define("HW2DEV", 0);

define('BUGZILLAUSER', '****');
define('BUGZILLAPASS', '****');
define('BUGZILLADB',   '****');

// Tuning fuer Tests
define("NO_SECURITY", 1);

// SMS Versand
define("SMS_SERVICE", 0);
define("SMS_PREMIUM_KEYWORD", "HOLY SPEED");
define("PREMIUM_TEST_DURATION", "2");


// Maximale Inaktivitaet 21 Tage
define("MAX_INACTIVE", 21*24*3600);

define("NOOB_TIME", 1 * 24 * 60 * 60);


define("TICK", 20);
define("SERVICE_SLEEP_MAX", 5);
define("RESEARCHSPEED", 65);
define("BUILDINGSPEED", 20);
define("RECRUITSPEED",  12);
define("MIN_RESEARCH_TIME", 30);

define("RESEARCHEW", 200);

// Minimale Belagerungszeit vor einem Ausfall
define("SALLY_MIN_SIEGE_TIME", 600);

// Im Gegensatz zur letzten Runde folgende Aenderung:
// Truppenvorbereitung 2/3. Unter 3000 Einheiten macht
// es keinen Unterschied - immer 2 Stunden Vorberitung
define("ARMY_TIME_TO_PREPARE", 360); // mind. 6 Mins Vorbereitung
define("ARMY_TIME_PER_1000", 120);   // Zeit pro 1000 Einheiten in Sekunden
define("ARMY_MAX_TIME", 1800);    // Maximale Vorbereitungszeit
define("ARMY_SPEED_FACTOR", 100);       // Armeen x mal schnell

define("LOYALITY_GROWTH", 25);


// Kosten pro Einwohner
define("CONVERT_COST", 100);

// Maximaler Wohlstand = FAKTOR*ATTR
define("PROSPERITY_MAX_FACTOR", 1000);


define("BLOCKSETTLEARMY", 36000);  // Angriffssperre für Begleitschutz

//Ressourcenproduktion
define("RAWWOOD_PRODFACTOR", 1);
define("WOOD_PRODFACTOR", 1);
define("RAWIRON_PRODFACTOR", 1);
define("IRON_PRODFACTOR", 1);
define("RAWSTONE_PRODFACTOR", 1);
define("STONE_PRODFACTOR", 1);
define("GOLD_PRODFACTOR", 1);
define("SHORTRANGE_PRODFACTOR", 1);
define("LONGRANGE_PRODFACTOR", 1);
define("ARMOR_PRODFACTOR", 1);
define("HORSE_PRODFACTOR", 1);

// Produktionskosten
define("SHORTRANGE_COST", 2);
define("LONGRANGE_COST", 2);
define("ARMOR_COST", 5);
define("HORSE_COST", 20);

// Maximale Marktangebote
define("MARKET_LIMIT", 4);

// ID des ersten Hauptstadt-Gebaeudes
define("BUILDING_HS_LVL1", 1000);
define("BUILDING_SCHOOL", 57);
define("BUILDING_BIGSCHOOL", 58);

define("RESEARCH_SCHOOL", 6);
define("RESEARCH_BIGSCHOOL", 33);
define("RESEARCH_LIBRARY", 79);

define("DORFBEWOHNER_ID", 100);
define("MARKETRESEARCH", 10);
define("CLANLEADRESEARCH", 117);

define("RECRUIT_BONUSPOINTS", 1);
define("FIGHTSIM_NEED_POINTS", 10);

define("AVATAR_TOP_POINTS", 2000000);

// Bonuspunkte fuer Click alle X Sekunden
//define("CLICK_BONUSPOINTS", 1);
//define("CLICK_BONUSPOINTS_TIME", 3600);
define("CLICK_BONUSPOINTS", 2);
define("CLICK_BONUSPOINTS_TIME", 1800);

// Belagerung aktivieren
define("ENABLE_SIEGE", 1);

// Belagerungen kosten nichts, wenn angekommen
define("SIEGE_ARMIES_NO_COST", 0);

// Loyalitaet aktiv?
define("ENABLE_LOYALITY", 1);
// Loyalitaet bei Uebernahme der Stadt
define("MIN_LOYALITY", 0);
// Loy zum Abreissen von Gebaeuden. In Prozent!!!
define("DESTRUCT_LOYALITY", 25);


// Tourniere aktivieren
define("ENABLE_TOURNAMENT", 1);

// Neues Pluernderscript aktiv
define("NEW_DESPOIL", 1);

// Neues Positionierungsskript?
define("START_POS_NEW", 1);

// Verbiete angreife aus dem Siedlungsbereich heraus
define("DISABLE_SETTLEAREA_ATTACK", 1);

// Dies sind die Registrierungslimits, um zu steuern,
// ob ein Spieler im Hinterland starten muss oder ins
// Kriegsgebiet darf.
define("REGISTER_RATIO_LIMIT_HARD", 2.8);
define("REGISTER_RATIO_LIMIT_MEDIUM", 1.6);
define("REGISTER_RATIO_LIMIT_LIGHT", 1.3);

?>
