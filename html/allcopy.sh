#!/bin/sh

if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    echo "game1   "; scp $1 kroddn@www4:/home/hw2_game1/html/$1 &
    echo "game2   "; scp $1 kroddn@www4:/home/hw2_game2/html/$1 &
    echo "speed   "; scp $1 kroddn@www4:/home/hw2_speed/html/$1 &
    echo "hispeed "; scp $1 kroddn@www4:/home/hw2_hispeed/html/$1
    shift
done

