#!/bin/sh

if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    scp $1 www4:/home/hw2_dev/html/admintools/$1
    shift
done

