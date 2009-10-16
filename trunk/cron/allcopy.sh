#!/bin/sh

if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    scp $1 kroddn@www4:/home/hw2_game1/cron/$1
#    scp $1 kroddn@www4:/home/hw2_game2/cron/$1
    scp $1 kroddn@www4:/home/hw2_speed/cron/$1
    shift
done

echo "Achtung! Old ist nicht in allcopy.sh integriert!"
echo "./oldcopy.sh verwenden"
