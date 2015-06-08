#include "hwha.h"

#include <stdio.h>
#include <mysql.h>
#include <stdlib.h>
#include <time.h>
#include <unistd.h>
#include <stdarg.h>
#include <errno.h>
#include <signal.h>
#include <string.h>
#include <libgen.h>

void segfault(int par)
{
        signal(SIGSEGV,segfault);
        printf("An error occured, terminating. (errno: %s)\n", strerror(errno));
        exit(0);
}

int con(MYSQL *hw)
{
	hw = mysql_init(hw);
	if (!mysql_real_connect(hw, MYSQL_HOST, MYSQL_USER, MYSQL_PW, MYSQL_DATABASE,0,NULL,0))
	{
		printf(mysql_error(hw));
		return 0;
	}

	if (mysql_select_db(hw, MYSQL_DATABASE))
	{
		printf(mysql_error(hw));
		return 0;
	}

	return 1;
}

void send_query(MYSQL *hw, const char *s, ...)
{
        va_list args;
        char *buf = malloc(sizeof(char)*strlen(s)*2+1);
        char *dummy = malloc(sizeof(char)*strlen(s)+1000);

        va_start(args, s);
        vsprintf(dummy, s, args);

//      mysql_escape_string(buf, dummy, strlen(dummy));

        if((mysql_real_query(hw, dummy, strlen(dummy)+1)))
        {
               printf("%s", mysql_error(hw));
        }
        free(buf);
        free(dummy);
}


void updatePts(void)
{
  static MYSQL hahw, hahw2;
  MYSQL_RES *res=NULL, *res2=NULL;
  MYSQL_ROW row, row2;
  int incpoints=0;
  int tmp;
	
  con(&hahw);

	

  send_query(&hahw, "SELECT id,gold FROM player WHERE name IS NOT NULL");

  res = mysql_use_result(&hahw);

  while ((row=mysql_fetch_row(res)))
    {
      // Spielern mit sehr wenig Gold nen Malus geben
      int malus_points = atoi(row[1]);
      if (malus_points < -1000000) malus_points = -malus_points / 10;
      else malus_points = 0;


      con(&hahw2);
      send_query(&hahw2, "SELECT sum(citybuilding.count * building.points) AS incpoints FROM city LEFT JOIN citybuilding ON city.id = citybuilding.city LEFT JOIN building ON building.id = citybuilding.building WHERE city.owner =%s", row[0]);
		
      res2 = mysql_use_result(&hahw2);
      while((row2=mysql_fetch_row(res2)))
	{
	  if(row2[0]) {
	    // Punkte fuer Truppen nur gutschreiben, wenn
	    // Sich der Kontostand entsprechend wohlhaben gestaltet
	    tmp = atoi(row2[0]);
                    
	    if (malus_points > tmp) {
	      tmp = 0;
	      malus_points -= tmp;
	    }
	    else if (malus_points > 0) {
	      malus_points = 0;
	      tmp -= malus_points;
	    }
                    
	    incpoints += tmp;
	  }
	}
      if (res2)
	mysql_free_result(res2);
		
      send_query(&hahw2, "SELECT sum(research.points) AS incpoints FROM playerresearch LEFT JOIN research ON playerresearch.research=research.id WHERE playerresearch.player =%s", row[0]);

      res2 = mysql_use_result(&hahw2);
      while((row2=mysql_fetch_row(res2)))
	{
	  if(row2[0])
	    incpoints += atoi(row2[0]);
	}
      if (res2)
	mysql_free_result(res2);

      send_query(&hahw2, "SELECT sum(armyunit.count * unit.points) AS incpoints FROM army LEFT JOIN armyunit ON army.aid = armyunit.aid LEFT JOIN unit ON armyunit.unit = unit.id WHERE army.owner =%s", row[0]);
		
      res2 = mysql_use_result(&hahw2);
      while ((row2=mysql_fetch_row(res2)))
	{
	  if(row2[0]) {
	    // Punkte fuer Truppen nur gutschreiben, wenn
	    // Sich der Kontostand entsprechend wohlhaben gestaltet
	    tmp = atoi(row2[0]);
                      
	    if (malus_points > tmp) {
	      tmp = 0;
	      malus_points -= tmp;
	    }
	    else if (malus_points > 0) {
	      malus_points = 0;
	      tmp -= malus_points;
	    }
                      
	    incpoints += tmp;
	  }
	}
      if (res2)
	mysql_free_result(res2);

      send_query(&hahw2, "SELECT sum(cityunit.count * unit.points) AS incpoints FROM cityunit LEFT JOIN unit ON cityunit.unit = unit.id WHERE cityunit.owner =%s", row[0]);
		
      res2 = mysql_use_result(&hahw2);
      while ((row2=mysql_fetch_row(res2)))
	{
	  if(row2[0]) {
	    // Punkte fuer Truppen nur gutschreiben, wenn
	    // Sich der Kontostand entsprechend wohlhaben gestaltet
	    tmp = atoi(row2[0]);
                      
	    if (malus_points > tmp) {
	      tmp = 0;
	      malus_points -= tmp;
	    }
	    else if (malus_points > 0) {
	      malus_points = 0;
	      tmp -= malus_points;
	    }
                      
	    incpoints += tmp;
	  }
	}
      if (res2)
	mysql_free_result(res2);

      send_query(&hahw2, "UPDATE player SET points=%d, pointsavg=pointsavg+%d, pointsupd=pointsupd+1 WHERE id=%s", incpoints, incpoints, row[0]);
      mysql_close(&hahw2);
      incpoints=0;
    }
  if (res)
    mysql_free_result(res);
		
  mysql_close(&hahw);	
}



void updateClanPts(void)
{
  static MYSQL hahw, hahw2;
  MYSQL_RES *res=NULL;
  MYSQL_ROW row;

  con(&hahw);

  send_query(&hahw, "SELECT SUM(player.points), clan.id FROM player, clan WHERE player.clan = clan.id GROUP BY clan.id");
  res=mysql_use_result(&hahw);

  while((row=mysql_fetch_row(res)))
    {
      con(&hahw2),
	send_query(&hahw2, "UPDATE clan SET points=%s WHERE id=%s", row[0], row[1]);
      mysql_close(&hahw2);
    }

  if(res)
    mysql_free_result(res);

  mysql_close(&hahw);
}



void do_log2(const char *msg, ...)
{
  va_list args;
  char buf[1024];
  struct tm *tmnow;
  time_t tnow;
  FILE *logf;

  if((logf=fopen("points.log","a"))==NULL)
    return;

  va_start(args, msg);
  vsnprintf(buf, sizeof(buf), msg, args);

  time(&tnow);
  tmnow = localtime(&tnow);
  
  fprintf(logf,"[%d.%d. %02d:%02d:%02d]: %s\n",tmnow->tm_mday, tmnow->tm_mon+1,tmnow->tm_hour,tmnow->tm_min, tmnow->tm_sec, buf);

  fflush(logf);
  fclose(logf);
}






int main(int argc, char** argv)
{
  static MYSQL hahw_main;
  MYSQL_RES *res=NULL;
  MYSQL_ROW row;
  int starttime = 0, endtime = 0, currtime = time(NULL);

  signal(SIGSEGV,segfault);

  // Change into dir where this exe is
  if(argv[0]) {
    
    chdir( dirname( argv[0]) );
  }

  con(&hahw_main);


  // Start und Endzeit holen
  send_query(&hahw_main, 
	     "SELECT value FROM config WHERE name = 'starttime'");
  res = mysql_use_result(&hahw_main);
  if( res && (row = mysql_fetch_row(res)) ) {
    starttime = atoi(row[0]);
    mysql_free_result(res);
  }

  send_query(&hahw_main, 
	     "SELECT value FROM config WHERE name = 'endtime'");
  res = mysql_use_result(&hahw_main);
  if( res && (row = mysql_fetch_row(res)) ) {
    endtime = atoi(row[0]);
    mysql_free_result(res);
  }

  mysql_close(&hahw_main);

  if( starttime > 0 && currtime < starttime ) {
    do_log2("Runde noch nicht gestartet");        
    return 0;
  }
  if( endtime > 0 && currtime > endtime ) { 
    do_log2("Runde beendet");
    return 0;
  }
  
  
	
  updatePts();
  updateClanPts();

  return 0;
}
	

