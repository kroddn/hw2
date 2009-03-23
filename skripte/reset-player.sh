#!/bin/sh

PLAYER=1000

echo '
DELETE FROM citybuilding WHERE city IN (SELECT id FROM city WHERE owner = '$PLAYER');
DELETE FROM citybuilding_ordered WHERE city IN (SELECT id FROM city WHERE owner = '$PLAYER');
DELETE FROM cityunit WHERE city IN (SELECT id FROM city WHERE owner = '$PLAYER');
DELETE FROM cityunit_ordered WHERE city IN (SELECT id FROM city WHERE owner = '$PLAYER');
DELETE FROM city WHERE owner = '$PLAYER';

DELETE FROM armyunit WHERE aid IN (SELECT aid FROM army WHERE owner = '$PLAYER');
DELETE FROM army WHERE owner = '$PLAYER';

UPDATE player SET
       gold=40000,wood=4000,iron=0,stone=4000,rp=30,toplist=NULL,
       cc_towns=1,points=0,pointsavg=0,pointsupd=0,
       name=NULL,religion=NULL,signature=NULL,regtime=UNIX_TIMESTAMP(),
       clan=NULL,clanstatus=0,clanapplication=NULL,avatar=0,
       holiday=0,nooblevel=5,lastres=unix_timestamp(),description=NULL 
WHERE id = '$PLAYER';
'