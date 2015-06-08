C      =gcc

LIBS    = -I/usr/include/mysql -L/usr/lib -lmysqlclient

all:
	$(CC) -Wall -O2 -o hwha_dev hwha.c misc.c functions.c  $(LIBS)
	$(CC) -Wall -O2 -o points_hw_dev points_hw.c $(LIBS)
