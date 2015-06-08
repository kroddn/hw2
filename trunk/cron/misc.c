#include <stdio.h>
#include <time.h>
#include <stdarg.h>
#include <unistd.h>


void do_log(const char *msg, ...)
{
        va_list args;
        char buf[1024];
        struct tm *tmnow;
        time_t tnow;
        FILE *logf;

        if((logf=fopen("hwha.log","a"))==NULL)
                return;

        va_start(args, msg);
        vsnprintf(buf, sizeof(buf), msg, args);

        time(&tnow);
        tmnow = localtime(&tnow);
	
	fprintf(logf,"[%02d.%02d. %02d:%02d:%02d]: %s\n",
		tmnow->tm_mday, tmnow->tm_mon+1, tmnow->tm_hour, tmnow->tm_min, tmnow->tm_sec, buf);

        fflush(logf);
        fclose(logf);
}



/* send the process in the background */
void start_daemon(void)
{
        printf("Sende den Prozess in den Hintergrund...\n");
        do_log("Sende den Prozess in den Hintergrund...");

        switch (fork())
        {
                case 0:  break;
                case -1: do_log("Couldn't become a daemon process. Terminating...");
                         _exit(0);
                         break;
                default: _exit(0);          /* exit the original process */
        }

        if (setsid() < 0)               /* shoudn't fail */
        {
                do_log("setsid() failed. Terminating...");

                _exit(0);
        }

        switch (fork())
        {
                case 0:  break;
                case -1: do_log("Couldn't become a daemon process. Terminating...")
;
                         _exit(0);
                         break;
                default: _exit(0);
        }
        do_log("Done.");
}

