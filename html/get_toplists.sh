#!/bin/sh


NR=000

if [ ! -e toplists ]; then
    mkdir toplists
    echo "Options +Indexes" >toplists/.htaccess
    ln -s ../images toplists/images
fi

(
    cd toplists
    for TOP in player player_avg town population div
    do
	wget \
	    -O toplist_${NR}_${TOP}.html \
	    "http://hispeed.holy-wars2.de/toplist.php?show=${TOP}"
	
    done 

)

