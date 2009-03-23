if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    scp $1 hw2:/home/hw2_oldgame1/html/admintools/$1
    shift
done

