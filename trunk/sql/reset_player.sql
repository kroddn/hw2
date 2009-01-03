-- Diese Datei könnte veraltet sein!
-- Siehe html/includes/reset.inc.php
-- 

update player set 
regtime =  UNIX_TIMESTAMP(),
lastres = UNIX_TIMESTAMP()+1*3600, 
gold=40000, 
wood=4000, 
stone=4000, 
iron=0, 
rp=0, 
points=0, 
pointsavg = 0,
pointsupd = 0,
clan=NULL,
clanstatus=0,
clanapplication=NULL,
clanapplicationtext=NULL,
nooblevel =5,
holiday =0,
cc_towns =1,
cc_messages=1,
cc_resources=1,
toplist =NULL,
avatar=0,
lastseen =0,
recruiter = NULL,
bonuspoints=0,
lastclickbonuspoints=0,
pos=NULL,
pos = religion *3 -1;

truncate city;
truncate citybuilding;
truncate playerresearch;
truncate message;
truncate log_login;
truncate log_mysqlerr;

