#!/bin/sh
#
# Create SQL Code for shifting the time

# 24 Std = 86400
# 10 Std = 36000
#  1 Std =  3600

if [ $# -eq 1 ]; then
    TIME=$1
else
    TIME=68000
fi

echo "UPDATE army SET starttime = starttime + "$TIME", endtime=endtime +"$TIME";"
echo "UPDATE citybuilding_ordered SET time = time + "$TIME";"
echo "UPDATE cityunit_ordered SET time = time + "$TIME";"
echo "UPDATE researching SET endtime = endtime + "$TIME";"
echo "UPDATE player SET lastres = lastres + "$TIME";"

