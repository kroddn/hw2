#!/bin/sh

if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    echo "game2 "; scp $1 kroddn@mysql:/home/hw2_game1/html/maps/$1 &
    echo "game1 "; scp $1 kroddn@mysql:/home/hw2_game2/html/maps/$1 &
    echo "speed "; scp $1 kroddn@mysql:/home/hw2_speed/html/maps/$1
    shift
done

