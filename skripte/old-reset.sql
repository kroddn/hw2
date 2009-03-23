
TRUNCATE `army`;
TRUNCATE `armyunit`;
TRUNCATE `booking`;
TRUNCATE `bugzilla_map`;
TRUNCATE `city`;
TRUNCATE `citybuilding`;
TRUNCATE `citybuilding_ordered`;
TRUNCATE `cityunit`;
TRUNCATE `cityunit_ordered`;
TRUNCATE `clan`;
TRUNCATE `clanlog`;
TRUNCATE `clanlog_admin`;
TRUNCATE `clanrel`;
TRUNCATE `content`;
TRUNCATE `exceptions`;
TRUNCATE `log_army`;
TRUNCATE `log_armyunit`;
TRUNCATE `log_browser`;
TRUNCATE `log_cputime`;
TRUNCATE `log_delete`;
TRUNCATE `log_err`;
TRUNCATE `log_login`;
TRUNCATE `log_marketmod`;
TRUNCATE `log_market_accept`;
TRUNCATE `log_market_send`;
TRUNCATE `log_multi_market`;
TRUNCATE `log_mysqlerr`;
TRUNCATE `market`;
TRUNCATE `message`;
TRUNCATE `multi_trap`;
TRUNCATE `multi_trap_caught`;
TRUNCATE `multi_trap_nocookie`;
TRUNCATE `news`;
TRUNCATE `playerresearch`;
TRUNCATE `player_online`;
TRUNCATE `relation`;
TRUNCATE `req_clanrel`;
TRUNCATE `req_relation`;
TRUNCATE `req_research`;
TRUNCATE `researching`;
TRUNCATE `zitate`;


UPDATE player SET
       gold=40000,wood=4000,iron=0,stone=4000,rp=0,toplist=NULL,
       cc_towns=1,points=0,pointsavg=0,pointsupd=0,
       signature=NULL,regtime=UNIX_TIMESTAMP(),
       clan=NULL,clanstatus=0,clanapplication=NULL,
       holiday=0,nooblevel=5,lastres=unix_timestamp(),description=NULL
;
