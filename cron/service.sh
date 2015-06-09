#!/bin/bash
if [ ! -f hwroot.sh ]; then
    echo "Datei hwroot.sh fehlt. Anlegen!"
    exit
fi

. hwroot.sh

if [[ $HWROOT == "" ]] ; then
    echo "\$HWROOT ist nicht gesetzt. Datei hwroot.sh anlegen!"
    exit
fi

cd $HWROOT/html
LOG=$HWROOT/log/service.log
if [ -e $LOG ]; then
    chmod 0666 $LOG
fi

export DEBUG=1

if [ ! "$REMOTE_ROOT" == "0" ]; then
  if [ "$UID" == "0" ]; then
      HWUSER=$SUDO_USER
  else
      HWUSER=$USER
  fi

  echo "Hole neueste Service von Host hw2 und vergleiche"
  echo "Sie muessen nun das Passwort fuer $HWUSER@hw2.holy-wars2.de eingeben"
  scp $HWUSER@hw2.holy-wars2.de:$HWROOT/html/includes/db.config.php $HWROOT/html/includes/db.config.php.host-hw2

  if [ -e $HWROOT/html/includes/db.config.php.host-hw2 ]; then
    if ! diff -b $HWROOT/html/includes/db.config.php $HWROOT/html/includes/db.config.php.host-hw2; then
      echo
      echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
      echo "ACHTUNG! db.config.php unterscheiden sich zwischen beiden Servern"
      echo "Ueberpruefen Sie die Unterschiede und loesen sie diesen Konflikt"
      echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
      echo
      exit
    fi
  else
    echo "config Ueberpruefung ausgelassen."  
  fi
fi

# Zaehle die bereits laufenden Instanzen von Service
NUM_SERV=`ps xa|grep "php.*$HWROOT"|grep -vc grep`
if [ $NUM_SERV -gt 0 ] ; then
    echo
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo "Es laufen bereits $NUM_SERV Services fuer $HWROOT"
    echo "Diese mit \"sudo kill <PID>\" abschiessen"
    ps xa | grep "php.*$HWROOT" | grep -v grep
    echo -n "PID = "
    ps xa | grep "php.*$HWROOT" | grep -v grep | awk "{ print \$1; }"
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo
    exit
fi

if [ "$UID" == "0" ]; then
    # Start process nice -5, that means it should run faster than normally
    # Only as root possible
    NICE="nice -n 5"
else
    NICE=""
fi


$NICE /usr/bin/php -c /etc/php4/apache2/ \
    $HWROOT/html/includes/service.inc.php >>$LOG &

echo "Service '$HWROOT/html/includes/service.inc.php' gestartet"

