timera = new Array();
timerb = new Array();
countdown();

function setDivText(c1, text) {
  document.getElementById(c1).innerHTML=text;
}

function addTimer(seconds, divName) {
  timera[timera.length] = seconds;
  timerb[timerb.length] = divName;
}

function countdown() {
    timeout = 1000;

    if(timera.length > 0) {
	for (i=0; i<timera.length;i++) {
		if (timera[i] > 0) {
			if (--timera[i] > 0) {
				lSeconds = timera[i]%60;   // bisserl komplizierte Formatierung
				lMinutes = Math.floor(timera[i] / 60);
				lHours = Math.floor(lMinutes / 60);
				lMinutes%=60;
				lMinutes= ((lMinutes<10)?"0":"") +  (lMinutes);
				lSeconds =((lSeconds<10)?"0":"") +  (lSeconds);
				setDivText(timerb[i], "<span>" + lHours + ":" + lMinutes + ":" + lSeconds +"</span>");
			}
			else {
                                if (timerb[i]=='session_duration') {
        				setDivText(timerb[i], "<span>abgelaufen</span>");
                                }
                                else {
        				setDivText(timerb[i], "<span>bereit</span>");
                                }
				timera[i]=-1; // als 'wieder frei' markieren
			}
		}
		else if (timera[i] < -3600) {
			setDivText(timerb[i], '<span class="timer_error">Fehler!</span>');
		}
		else {
			setDivText(timerb[i], "<span>bereit</span>");
		}
	}
        if(timera.length > 20) {
            timeout = 999;
        }
        else {
            timeout = 1000;
        }
    }
    else {
        /*  Kein Timer - trotzdem Timeout setzen, weil die Timer
         *  vielleicht nocht aktiviert werden
         */
        timeout = 1000;
    }

    setTimeout("countdown()", timeout);
}
