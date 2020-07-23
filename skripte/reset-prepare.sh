#!/bin/bash
# Vorbereitungen fuer einen Reset
#
#
#
DB=hw2_speed
# addressbook
# sms_settings
# sms_send

echo "this is not needed anymore - USe reset command over admin"
exit 0

# Alle unbenoetigten Tabellen leeren
TABLES="
armyunit 
army
cityunit 
cityunit_ordered 
city 
citybuilding 
citybuilding_ordered 
clan 
clanlog 
clanlog_admin
clanrel 
log_army
log_armyunit
log_bofh_player
log_browser
log_cputime
log_delete
log_err
log_lock
log_login
log_market_accept
log_market_send
log_marketmod
log_multi_market
log_mysqlerr
log_player_deleted
log_reactivate
relation
market
message
multi_exceptions
multi_exceptions_players
multihunter
namechange
news
player_monument
player_online
playerresearch
relation
req_clanrel
req_relation
researching
rpg
tournament
tournament_players
zitate
"

for TABLE in $TABLES; do
    echo "TRUNCATE "$TABLE";"
#    echo TRUNCATE $TABLE | mysql $DB
done

echo 

# MAP loeschen und neu erstellung und ueberpruefen
echo "SELECT count(*) FROM map"
echo "SELECT count(*) FROM map" | mysql $DB
echo
echo "-- Standard .png files kopieren und umbenennen"
echo "-- admintools/fillmapwithres.php ausführen!"
echo "-- admintools/import.php ausführen!"
echo "-- admintools/startpos.php ausführen!"

echo
echo "-- Alle gesperrten Spieler löschen"
echo "--  DELETE FROM player WHERE status = 2 ;"
echo
echo "-- Tabelle player wird geupdated und auf Anfangswerte gesetzt"
echo "   UPDATE player SET "
echo "       gold=40000,wood=4000,iron=0,stone=4000,rp=30,toplist=NULL,"
echo "       cc_towns=1,points=0,pointsavg=0,pointsupd=0,"
echo "       name=NULL,religion=NULL,signature=NULL,regtime=UNIX_TIMESTAMP(),"
echo "       clan=NULL,clanstatus=0,clanapplication=NULL,avatar=0, "
echo "       holiday=0,nooblevel=5,lastres=unix_timestamp(),description=NULL ;"
echo


#echo "- Tabelle player manuell löschen, sobald alles okay."
#echo "   ID 1-3 sollten Adminaccounts sein (kroddn+morlock?)"
#echo "   DELETE FROM player WHERE id > 3; -- alle löschen"
#echo "   ALTER TABLE player auto_increment = 4; -- Autoincrement resetten"

echo "-- Adressbuch-Einträge löschen, die Spieler-bezogen sind"
echo "  DELETE FROM addressbook WHERE player IS NOT NULL ;"
echo
echo "-- Logfiles löschen (Unterordner logs)"
echo "-- Avatare löschen (Unterordner avatar)"
echo "-- ClanForum resetten"
echo "-- Services starten"
echo "-- Turniere generieren"
echo

#echo "-- Tabelle bookings rüberkopieren"
#echo "-- MAX_PLAYER in includes/config.inc.php auf 0 setzen und Voranmeldungen aktivieren"

