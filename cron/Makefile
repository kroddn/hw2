C      =gcc

# Use library depending on configuration
LIBS    = -I/usr/include/mysql $(mysql_config --libs) -lmysqlclient

all:
	$(CC) -g -Wall -O2 -o hwha_dev hwha.c misc.c functions.c  $(LIBS)
	$(CC) -Wall -O2 -o points_hw_dev points_hw.c $(LIBS)

clean:
	rm points_hw_dev hwha_dev