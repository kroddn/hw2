#!/bin/sh

BASEPATH=`dirname $0`
cd $BASEPATH
cd ../html

php ../cron/update_nooblevel.php  >>../log/update_nooblevel.log
