#!/bin/sh
if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    scp $1 kroddn@www4:/home/portal_hw2/html/$1
    shift
done

