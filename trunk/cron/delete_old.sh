#!/bin/sh

BASEPATH=`dirname $0`
cd $BASEPATH
cd ../html

php ../cron/delete_old.php  >>../log/delete_old.log
