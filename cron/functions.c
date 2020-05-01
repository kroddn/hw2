#include <mysql.h>
#include <time.h>
#include <stdlib.h>
#include <stdio.h>

#include "hwha.h"

#define DEBUG 1

// send query to mysql server
extern void send_query(MYSQL *hw, const char *s, ...);
// connect to mysql server
extern int con(MYSQL *hw);

extern void do_log(const char *msg, ...);


/*
  buiding.name AS bname                   0
  city.name AS cname                      1
  citybuilding_ordered.time AS time       2
  owner                                   3
  building                                4
  bid                                     5
  city.id AS city                         6
  type                                    7
  typelevel                               8
  count                                   9
*/


/* gebäudeupdates */
void completeBuidings(void)
{        
  static MYSQL hahw, hahw2;
  MYSQL_RES *res=NULL, *res2=NULL;
  MYSQL_ROW row, row2;
  con(&hahw);
  char tmp[8192];
      
  /* alle Aufträge auswählen, die fertig sind */
  send_query(&hahw, "SELECT "
             " building.name, city.name, citybuilding_ordered.time, owner, "
             " building, bid, city.id, type, typelevel, count, makescapital "
             "FROM citybuilding_ordered, building, city "
             "WHERE city.id=citybuilding_ordered.city "
             "  AND building.id=citybuilding_ordered.building "
             "  AND citybuilding_ordered.time<=%d "
             "ORDER BY time", (int)time(NULL));
        
  res = mysql_use_result(&hahw);

        
  while ((row = mysql_fetch_row(res)))
    {       
      const char* moreText = "";

      con(&hahw2);
      /* Auftrag löschen, row[5] = bid */
      send_query(&hahw2, "DELETE FROM citybuilding_ordered WHERE bid=%s", row[5]);
                
      /* Wenn das Gebaeude ein Hauptstadtgebaeude war, dann alle 
         anderen Hauptstadtgebaeude loeschen und Capital verlegen*/
      if ( row[10] && atoi(row[10]) ) 
        {
#ifdef DEBUG
          do_log("Gebäude führt zu Hauptstadt. Lösche übrige Gebäude.");
#endif
          send_query(&hahw2, "UPDATE city SET capital = 0 WHERE owner = %s", row[3]);
          send_query(&hahw2, "UPDATE city SET capital = 1 WHERE id = %s", row[6]);

          // Alle anderen Hauptstadtgebäude abreisen
          send_query(&hahw2, "DELETE FROM citybuilding WHERE city IN "
                     "(SELECT id FROM city WHERE owner=%s AND id <> %s) "
                     "AND building IN "
                     "(SELECT id FROM building WHERE makescapital=1)", row[3], row[6]);

          send_query(&hahw2, "DELETE FROM citybuilding_ordered WHERE city IN "
                     "(SELECT id FROM city WHERE owner=%s AND id <> %s) "
                     "AND building IN "
                     "(SELECT id FROM building WHERE makescapital=1)", row[3], row[6]);
          
          moreText="\nSire, die Stadt wurde zu Eurer Hauptstadt. Beschützt Sie gut.";
        }


      /* Prüfen ob das Gebäude ein anderes ersetzen muss (ist der Fall wenn der Typlevel größerer als 1 is */
      if (row[8] && atoi(row[8])>1)
        {       
          send_query(&hahw2, "SELECT building, count FROM citybuilding, building WHERE citybuilding.building=building.id AND city=%s AND type=%s AND typelevel=%d", row[6], row[7], atoi(row[8])-1);

          /* falls ja, nachschauen ob man alle (Anzahl!) Gebäude des Vorlevels ersetzen muss oder nicht */
          if(!(res2 = mysql_store_result(&hahw2)))
            {
#ifdef DEBUG
              do_log("fehl0r");
#endif
            }
	  else {
	    if(!(row2 = mysql_fetch_row(res2)))
	      {
#ifdef DEBUG
		do_log("konnte SELECT building, count... nicht ausfuehren.");
		do_log(mysql_error(&hahw2));
#endif
	      }
	    else 
	      {
		/* Wenn die Anzahl der bestehenden Gebäude größerer ist als Ersetzungsanzahl... 
		 * dann die bestehenden Gebäude updaten 
		 */
		if (row2[1] && row[9] && atoi(row2[1])>atoi(row[9]))
		  {
		    send_query(&hahw2, "UPDATE citybuilding SET count=count-%s WHERE building=%s AND city=%s", row[9], row2[0], row[6]);
		  }
		else
		  {
		    send_query(&hahw2, "DELETE FROM citybuilding WHERE building=%s AND city=%s", row2[0], row[6]);
		  }
	      } // else # if row2 = mysql_fetch_row(res2)

	    mysql_free_result(res2);
	  }
        }

      /* Prüfen ob schon diese Art Gebäude existieren... */
      send_query(&hahw2, "SELECT count FROM citybuilding WHERE building=%s AND city=%s", row[4], row[6]);


      if(!(res2 = mysql_store_result(&hahw2)))
        {
#ifdef DEBUG
          do_log("fehler bei res2!");
#endif
        }
      /* ...falls ja, dann Anzahl aktualisieren */
      if (mysql_num_rows(res2) == 1)
        {
          send_query(&hahw2, "UPDATE citybuilding SET count=count+%s  WHERE city=%s AND building=%s", row[9], row[6], row[4]);
          if(res2)
            mysql_free_result(res2);
        }
      /* ...ansonsten neuen Eintrag anlegen */
      else
        {
          send_query(&hahw2, "INSERT INTO citybuilding VALUES ( %s, %s, %s )", row[6], row[4], row[9]);
          if(res2)
            mysql_free_result(res2);
        }
      

      /* wenn die Stadt jemanden gehört */
      if(row[3]!=NULL)
        {
          if (atoi(row[9])>1) {
            send_query(&hahw2, 
                       "INSERT INTO message (sender,recipient,date,header,body,category) "
                       "VALUES ('SERVER',%s,%s,'Fertigstellung: %s (%s)','Neue Gebäude wurde in %s fertiggestellt:\n\n<b>%s</b>\nAnzahl: %s\n%s',3)",
                       row[3], row[2], row[0], row[9], row[1], row[0], row[9], moreText);
          }
          else {
            send_query(&hahw2, 
                       "INSERT INTO message (sender,recipient,date,header,body,category) "
                       "VALUES ('SERVER',%s,%s,'Fertigstellung: %s','Ein Gebäude wurde in %s fertiggestellt:\n\n<b>%s</b>\n%s',3)",
                       row[3], row[2], row[0], row[1], row[0], moreText);

          }
            
          /* Nachricht generieren */
          send_query(&hahw2, "UPDATE player SET cc_messages=1 WHERE id=%s", row[3]);
        }
      mysql_close(&hahw2);
    }

  if (res)
    mysql_free_result(res);

  mysql_close(&hahw);
}



void completeResearches(void) {
  static MYSQL hwha, hwha2;
  int rid;
  MYSQL_RES *res=NULL;
  MYSQL_ROW row;
  //	char logstring[300];

  con(&hwha);

  /* Alle Forschungen auswählen die fertig sind */

  send_query(&hwha, "SELECT researching.player, researching.rid, researching.endtime, research.name, research.id, research.category, player.nooblevel, player.recruiter FROM researching LEFT JOIN research ON research.id=researching.rid LEFT JOIN player ON player.id = researching.player WHERE researching.endtime<= UNIX_TIMESTAMP() ORDER BY endtime");

  res = mysql_use_result(&hwha);

  while ((row = mysql_fetch_row(res)))
    {
      con(&hwha2);
      /* Auftrag löschen row[1] = rid */
      //sprintf(logstring, "Forschung %s mit rid %s von Spieler %s ist fertig", row[3], row[1], row[0]);
      //do_log(logstring);
      //send_query(&hwha2, "DELETE FROM researching WHERE player=%s", row[0]);
      send_query(&hwha2, "DELETE FROM researching WHERE player=%s AND rid = %s", row[0], row[1]); // edit franzl 15.08.04
      send_query(&hwha2, "INSERT INTO playerresearch VALUES ( %s, %s)", row[0], row[1]);
      send_query(&hwha2, "INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER', %s,%s,'Fertigstellung: %s', 'Die Forschung %s wurde soeben fertiggestellt',3)", row[0], row[2], row[3], row[3]);

      // kroddn @ 04.04.2005
      // Set nooblevel to 0 if player finished verwaltung
      if ( atoi(row[5]) == 0 && atoi(row[6]) > 0 && atoi(row[1]) != 1 ) {
        do_log("Noobschutz weg");
        send_query(&hwha2, "INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER', %s,%s,'Neulingsschutz abgelaufen', 'Durch die Erforschung von %s wurde Euer Neulingsschutz aufgehoben. Ihr seid nun der willkürlichen Gewalt der anderen Spieler ausgeliefert.\n\nIhr solltet als nächstes für die Verteidigung Eurer Städte sorgen, indem Ihr mehrere Kasernen in Euren Städten errichtet und Truppen aushebt!',4)", row[0], row[2], row[3]);

        send_query(&hwha2, "UPDATE player SET cc_messages=1, nooblevel=0 WHERE id=%s", row[0]);
      }
      else {
        send_query(&hwha2, "UPDATE player SET cc_messages=1 WHERE id=%s", row[0]);
      } // Neulingsschutz
                
      // Bei bestimmten Forschungen dem Recruiter Bonuspunkte zuteilen
      if (row[7]) {
        rid = atoi(row[1]);                
      }
      else {
        rid = 0;
      }

      // Forschungen von 80 bis 81 ergeben XXXX Bonuspunkte
      int bonuspoints = 1000;
      if(rid == 80 || rid == 81 || rid == 93) {
        do_log("Forschung %s fertig, Recruiter %s bekommt Bonuspunkte\n", row[1], row[7]);
        send_query(&hwha2, "UPDATE player SET cc_messages=1, cc_resources=1, bonuspoints = bonuspoints + %d WHERE id=%s", bonuspoints, row[7]);                  
        send_query(&hwha2, "INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER', %s,%s,'%d Bonuspunkte', 'Ihr habt %d Bonuspunkte bekommen, weil ein geworbener Spieler eine höhere Entwicklungsstufe erreicht hat.', 3)", row[7], row[2], bonuspoints, bonuspoints);
        do_log("Done\n");
      }

      mysql_close(&hwha2);
    }

  if (res)
    mysql_free_result(res);

  mysql_close(&hwha);

}

void completeUnits(void)
{
  static MYSQL hwha, hwha2;
  MYSQL_RES *res=NULL, *res2=NULL;
  MYSQL_ROW row;

  con(&hwha);

  send_query(&hwha, "SELECT city.name, unit.name, cityunit_ordered.time, cityunit_ordered.count, cityunit_ordered.city, city.owner, cityunit_ordered.unit, uid FROM unit, city, cityunit_ordered WHERE city.id=cityunit_ordered.city AND unit.id=cityunit_ordered.unit AND cityunit_ordered.time<=%d ORDER BY time", (int)time(NULL));

  res = mysql_use_result(&hwha);

  while ((row = mysql_fetch_row(res)))
    {
      con(&hwha2);
      send_query(&hwha2, "DELETE FROM cityunit_ordered WHERE uid=%s", row[7]);
      send_query(&hwha2, "SELECT count FROM cityunit WHERE unit=%s AND city=%s AND owner=%s", row[6], row[4], row[5]);
      if(!(res2 = mysql_store_result(&hwha2)))
		{
#ifdef DEBUG
          do_log("fehl0r");
#endif
        }


      if (res2 && mysql_num_rows(res2))
        {
          //do_log("QUERY_UPDATE");
          mysql_free_result(res2);
          send_query(&hwha2, "UPDATE cityunit SET count=count+%s WHERE city=%s AND unit=%s AND owner=%s", row[3], row[4], row[6], row[5]);
        }

      else
        {
          //do_log("QUERY INSERT");
          if(res2)
            mysql_free_result(res2);
          send_query(&hwha2, "INSERT INTO cityunit VALUES (%s,%s,%s,%s)", row[4], row[6], row[3], row[5]);
        }
      send_query(&hwha2, "INSERT INTO message (sender,recipient,date,header,body,category) VALUES ('SERVER',%s,%s,'Ausbildung: %s (%s)','Eure Truppen in %s wurden fertig ausgebildet:\n\n<b>%s</b>\nAnzahl: %s',4)", row[5], row[2], row[1], row[3], row[0], row[1], row[3]);
      send_query(&hwha2, "UPDATE player SET cc_messages=1 WHERE id=%s", row[5]);
    }

  if (res)
    mysql_free_result(res);

  mysql_close(&hwha2);
  mysql_close(&hwha);

}


void checkonlineplayers(void)
{
  static MYSQL hahw, hahw2;
  MYSQL_RES *res=NULL;
  MYSQL_ROW row;
  int lastclick=(int)time(NULL) - 900;

  con(&hahw);

  send_query(&hahw, "SELECT uid FROM player_online WHERE lastclick<%d", lastclick);

  if((res = mysql_use_result(&hahw)))
	{
      con(&hahw2);

      while ((row = mysql_fetch_row(res)))
        {
          send_query(&hahw2, "DELETE FROM player_online WHERE uid=%s", row[0]);
        }
	}

  if (res)
    mysql_free_result(res);

  mysql_close(&hahw);
  mysql_close(&hahw2);
}

// work in progress...

