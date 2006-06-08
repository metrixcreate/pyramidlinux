function disabler() {
  var argv=disabler.arguments;
  var argc=argv.length;
  
  for(var i=1;i < argc; i++) {
    if(argv[0].checked) {
      argv[i].disabled=false;
    }
    else {
      argv[i].disabled=true;
    }
  }
}

function enabler() {
  var argv=enabler.arguments;
  var argc=argv.length;
  
  for(var i=1;i < argc; i++) {
    if(argv[0].checked) {
      argv[i].disabled=true;
    }
    else {
      argv[i].disabled=false;
    }
  }
}
