# OpenSim-Manager-Web-V5 dot 7.5.1

      OSMW  OpenSim Remote Admin that works with php7.x now Linux only. 
      
       Changlog: 
        * Rewrite SendCommand, usesPHP  CURL instead of raw php commands. 
        * Added Console Log page to send admin commands and view Console output fle created by screen command log. /bin/OpenSim.Console.log 
        * Now calling it version 5.7 the 7 meaning it is compatible with php7, will make this version solid with 7 and then move to a new version.   
        * Added cache to get around mixed content 
        * To get OpenSim Map and Console admin page to work you have to follow steps in this tutorial to setup screen. https://github.com/icarusfactor/opensim-install  
        * Updated map section to show regions in a scrolling window and create Regions and change Terrain. Does need a restart of Opensim after change to take effect.  
        * All the core functionality is working now. Saving,updaing,removing,editing and viewing.
        * I have not tested the NPC section just got it to stop erroring out. 
        * Can work with Windows or Linux or Mac you have to manually set the slash in install.php or after install inc/config.php but Map and Console options will only work on Linux.
        * Will have to manually edit cache directory variable in index.php or after install inc/config.php
