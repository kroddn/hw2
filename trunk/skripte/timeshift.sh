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

# TODO
#
# JAVA_HOME überprüfen, eventuell in bashrc aufnehmen
# export JAVA_HOME=/usr/lib/j2sdk1.5-sun
#
# - Apache Konfig anpassen
# - Exim4 Config überprüfen 
#   /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
#   /etc/exim4/exim4.conf.template
# - PHP.ini
#   /etc/php4/cli/php.ini
#   /etc/php4/apache2/php.ini
# 
# - IRCD
#   /etc/ircd-hybrid/ircd.conf
#   /etc/hybserv/hybserv.conf
#   /etc/hybserv/settings.conf
#
# - Webalizer
#   /etc/webalizer 
#
#
#

