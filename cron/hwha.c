/******************************************************************************
 *** Copyright etc :)
 ***
 ******************************************************************************/

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

#include "hwha.h"

#undef DEBUG

/*	static MYSQL hahw, hahw2;
	MYSQL_RES *res, *res2;
	MYSQL_ROW row, row2;*/


extern void completeBuidings(void);
extern void completeResearches(void);
extern void completeUnits(void);
extern void checkonlineplayers(void);
/* function to log stuff (recall: our bot is a daemon :) */
extern void do_log(const char *msg, ...);
/* send the process in the background */
extern void start_daemon(void);


void segfault(int par)
{
  signal(SIGSEGV,segfault);
  do_log("An error occured, terminating. (errno %d: %s)\n", errno, strerror(errno));
        
  exit(0);
}


int con(MYSQL *hw)
{
  hw = mysql_init(hw);
  if (!mysql_real_connect(hw, MYSQL_HOST, MYSQL_USER, MYSQL_PW, MYSQL_DATABASE,0,NULL,0))
    {
      do_log(mysql_error(hw));
      return 0;
    }

  if (mysql_select_db(hw, MYSQL_DATABASE))
    {
      do_log(mysql_error(hw));
      return 0;
    }

  return 1;
}


void send_query(MYSQL *hw, const char *s, ...)
{
  va_list args;
  char *dummy = malloc(sizeof(char)*strlen(s)+2000);

  va_start(args, s);
  vsprintf(dummy, s, args);


  if((mysql_real_query(hw, dummy, strlen(dummy)+1)))
    {
      do_log("MySQL Error. Query:%s", dummy);
      do_log(mysql_error(hw));
    }
#ifdef DEBUG	
  do_log(dummy);
#endif
  free(dummy);
}


int main(int argc, char** argv)
{
  printf("Datenbank %s / %s\n", MYSQL_HOST,MYSQL_DATABASE);
  do_log("Datenbank %s / %s", MYSQL_HOST,MYSQL_DATABASE);

  FILE *pid;
	
  signal(SIGSEGV,segfault);

  // Change into dir where this exe is
  if(argv[0]) {
    
    chdir( dirname( argv[0]) );
  }

  start_daemon();

  pid = fopen("hwha.pid", "w");
  fprintf(pid, "%d\n", (int)getpid());
  fclose(pid);

  static MYSQL hahw_main;
  MYSQL_RES *res=NULL;
  MYSQL_ROW row;


  /* alle TICK sekunden Gebäude updates prüfen */
  while (1)
    {
      int starttime = 0, endtime = 0, currtime = time(NULL);
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
  
      /*
      do_log("Time: %d, Starttime %d, Endtime %d", 
	     currtime, starttime, endtime);
      */
      
      if( starttime > 0 && currtime < starttime ) {
	do_log("Runde noch nicht gestartet, schlafe");
	if(starttime - currtime > 60)
	  sleep(60);
	else
	  sleep(5*TICK);
      }
      else if( endtime > 0 && currtime > endtime ) { 
	do_log("Runde beendet, schlafe\n");
	break;
      }
      else {
	completeBuidings();
	completeResearches();
	completeUnits();
	checkonlineplayers();
	sleep(TICK);
      }
    } // while

  return 0;
}
