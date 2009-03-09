if [ ! -f $q ]; then
    echo Nonexistent file
    exit 0;
fi

while [ $# -gt 0 ]; do
    scp $1 mysql:/home/hw2_speed/html/maps/$1
    shift
done

