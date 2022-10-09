# OpenSim-Manager-Web-V5 dot 7

      OSMW  OpenSim Remote Admin that works with php7.x
      
       Changlog: 
        * Rewrite SendCommand, usesPHP  CURL instead of raw php commands. 
        * Lots of fixes,more needed to get all pages working with SendCommand
        * Added Console Log page to send admin commands and view Console output fle created by screen command log. /bin/OpenSim.Console.log 
        * Now calling it version 5.7 the 7 meaning it is compatible with php7, will make this version solid with 7 and then move to a new version.   
        *images pulling from opensim still using http, will find a way to get them to cache to OSMW and then use them for SSL which has to be used for modern sites today.  
        *To get OpenSim Console admin page to work you have to follow steps in this tutorial to setup screen. https://github.com/icarusfactor/opensim-install  
