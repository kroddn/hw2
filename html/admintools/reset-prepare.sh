# Vorbereitungen fuer einen Reset
#
#
# 

DB=hw2_2006_06

# Alle unbenoetigten Tabellen leeren
TABLES="
addressbook
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
exceptions
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
premiumacc_old
relation
req_clanrel
req_relation
researching
sms_send
sms_settings
zitate
"

for TABLE in $TABLES; do
    echo "TRUNCATE "$TABLE";"
#    echo TRUNCATE $TABLE | mysql $DB
done

echo 
# MAP ueberpruefen
echo "SELECT count(*) FROM map"
echo "SELECT count(*) FROM map" | mysql $DB



# MAP loeschen und neu erstellung
echo "- admintools/import.php ausführen!"
echo "- Startpositionen neu generieren"

echo "- Tabelle player manuell löschen, sobald alles okay."
echo "   ID 1-3 sollten Adminaccounts sein (kroddn+morlock?)"
echo "   DELETE FROM player WHERE id > 3; -- alle löschen  ausser Admin"
echo "   DELETE FROM premiumacc WHERE player > 3; -- alle löschen ausser Admin"
echo "   ALTER TABLE player auto_increment = 10; -- Autoincrement resetten"
echo "   UPDATE player SET gold=40000,wood=4000,stone=4000,iron=0,rp=0,points=0,pointsavg=0,pointsupd=0,cc_towns=1,cc_resources =1, nooblevel=5, clan=NULL, clanstatus=0, clanapplication=NULL, lastres=unix_timestamp() WHERE id < 10;"


echo "- Logfiles löschen (Unterordner logs)"
echo "- Avatare löschen (Unterordner avatar)"
echo "- ClanForum resetten"
echo "- Tabelle bookings rüberkopieren"
echo "- Services starten"
echo "- MAX_PLAYER in includes/config.inc.php auf 0 setzen"
echo "- Voranmeldungen aktivieren. Und zwar aus der NEUEN Runde heraus"
