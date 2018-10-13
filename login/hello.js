var page = require('webpage').create();
  phantom.outputEncoding="gbk";
  page.open("www.rrbj.net", function(status) {
     if ( status === "success" ) {
        console.log(page); 
    } else {
       console.log("Page failed to load."); 
    }
    phantom.exit(0);
});