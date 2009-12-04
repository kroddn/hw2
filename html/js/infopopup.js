// JavaScript-Funktion öffnet Kindfenster für die Suche nach einer Städten
function towninfo(id) {
  var win = window.open("info.php?popup=1&show=town&id="+id,"Info","width=500,height=500,left=0,top=0,scrollbars=yes,dependent=yes");
  win.focus();
//  return win;
}

function playerinfo(name) {
  var win = window.open("info.php?popup=1&show=player&name="+name,"Info","width=500,height=500,left=0,top=0,scrollbars=yes,dependent=yes");
  win.focus();
//  return win;
}

function showhide(id) {
  for(i = 1; i<=5; i++) {
    if(document.getElementById("tbl"+i)) {
      document.getElementById("tbl"+i).style.display = "none";
    }
  }
  if(document.getElementById("tbl"+id)) {
    document.getElementById("tbl"+id).style.display = "inline";
  }
}