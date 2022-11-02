<?php 

 // Create an image cache object
require 'inc/class.cacheimg.php';

if (isset($_SESSION['authentification']))
{
        $cacheimg = new CacheIMG();
	echo Affichage_Entete($_SESSION['opensim_select']);
	$moteursOK = Securite_Simulateur();
    /* ************************************ */
	//SECURITE MOTEUR
	$btnN1 = "disabled";$btnN2 = "disabled";$btnN3 = "disabled";
	if ($_SESSION['privilege'] == 4) {$btnN1 = ""; $btnN2 = ""; $btnN3 = "";} // Niv 4
	if ($_SESSION['privilege'] == 3) {$btnN1 = ""; $btnN2 = ""; $btnN3 = "";} // Niv 3
	if ($_SESSION['privilege'] == 2) {$btnN1 = ""; $btnN2 = "";}              // Niv 2
	if ($moteursOK == "OK" )
	{
		if($_SESSION['privilege'] == 1)
		{$btnN1 = "";$btnN2 = "";$btnN3 = "";}
	}
     //SECURITE MOTEUR
    /* ************************************ */

    echo '<h1>'.$osmw_index_22.'</h1>';
    echo '<div class="clearfix"></div>';
        

    // *******************************************************
    // Initialisation des variables ET du tableau
    // *******************************************************

    // Offset
    $ox = 0;
    $oy = 0;

    // Limite de 50x50
    $max = 20;
    
    for ($x = -$max; $x < ($max - 1); $x++)
    {
        // echo "<hr>X:".$x.'<hr>';
        // Limite de 50x50
        for($y = -$max; $y < ($max - 1); $y++)
        {
            //echo "<hr>Y:".$y.'<hr>';
            $Matrice[$x][$y]['name'] = "";	
            $Matrice[$x][$y]['img']  = "";
            $Matrice[$x][$y]['ip']   = "";
            $Matrice[$x][$y]['port'] = "";	
            $Matrice[$x][$y]['uuid'] = "";
        } 
    } 
    //*******************************************************

    // *******************************************************	
    // Lecture des regions.ini et enregistrement dans Matrice
    // *******************************************************
    // Parcours des serveur installes

    $db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
    mysqli_select_db($db,$database);

    $sql = 'SELECT * FROM moteurs';
    $req = mysqli_query($db,$sql) or die('Erreur SQL !<p>'.$sql.'</p>'.mysqli_error($db));

    while($data = mysqli_fetch_assoc($req))
    {
        // Pour chaque serveur
        $tableauIni = parse_ini_file($data['address']."/bin/"."Regions/Regions.ini", true);

        if ($tableauIni == FALSE)
        {
            echo 'Probleme Lecture Fichier .ini '.$data['address']."/bin/"."Regions/Regions.ini".'<br>';
        }

        //echo '<p>Serveur Name:'.$data['name'].' - Version:'.$data['version'].'</p>';
        foreach( $tableauIni as $keyi => $vali )
        {
                        
			// *** Recuperation du port Http du Simulateur
			$srvOS  = RecupPortHTTP_Opensim($data['address']."/bin/".$FichierINIOpensim, "http_listener_port");

            // Recuperation des valeurs ET enregistrement des valeurs dans le tableau
    //        echo $keyi."&nbsp;".$tableauIni[$keyi]['RegionUUID']."&nbsp;".$tableauIni[$keyi]['Location']."&nbsp;".$tableauIni[$keyi]['InternalPort'];
            $location                               = explode(",", $tableauIni[$keyi]['Location']);
            $coordX                                 = $location[0] - $px - $ox;
            $coordY                                 = $location[1] - $py - $oy;
            $Matrice[$coordX][$coordY]['name']      = $keyi;
            $uuid                                   = str_replace("-", "", $tableauIni[$keyi]['RegionUUID']);
            $ImgMap1                                 = "http://".$tableauIni[$keyi]['ExternalHostName'].":".trim($srvOS).$slash."map-1-".$location[0]."-".$location[1]."-objects.jpg";
     //       echo " ".$ImgMap1;
            $cacheimg->get_cache( $uuid , $ImgMap1 , 0  );
	    $ImgMap = "https://".$hostnameSSH."/cache/".$uuid;
      //      echo " ".$ImgMap."</BR>";

            $Matrice[$coordX][$coordY]['img']       = $ImgMap;
            $Matrice[$coordX][$coordY]['ip']        = $tableauIni[$keyi]['ExternalHostName'];
            $Matrice[$coordX][$coordY]['port']      = $tableauIni[$keyi]['InternalPort'];	
            $Matrice[$coordX][$coordY]['uuid']      = $tableauIni[$keyi]['RegionUUID'];
            //$Matrice[$coordX][$coordY]['uuid']      = $keyi.$tableauIni[$keyi]['RegionUUID'];
            $Matrice[$coordX][$coordY]['hypergrid'] = $data["hypergrid"];

            $Matrice[$coordX][$coordY]['locX'] = $location[0];
            $Matrice[$coordX][$coordY]['locY'] = $location[1];

        }
    }
    mysqli_close($db);

    $RemotePort = RecupRAdminParam_Opensim(INI_Conf_Moteur($_SESSION['opensim_select'], "address")."/bin/".$FichierINIOpensim, " port = ");
    $access_password2 = RecupRAdminParam_Opensim(INI_Conf_Moteur($_SESSION['opensim_select'], "address")."/bin/".$FichierINIOpensim, " access_password = ");
    $myRemoteAdmin = new RemoteAdmin(trim($hostnameSSH), trim($RemotePort), trim($access_password2));

    if (isset($_POST['purgecache']))
    {

    array_map( 'unlink', array_filter((array) glob(  $cache_dir."*") ) );

    }

    if (isset($_POST['loadterrain']))
    {
    $parameters = array('command' => "change region ".$_POST['region_name'] );
    $myRemoteAdmin->SendCommand('admin_console_command', $parameters );


    $parameters = array('command' => "terrain load ".$_POST['terrainpath'] );
    $myRemoteAdmin->SendCommand('admin_console_command', $parameters );


    }

    if (isset($_POST['changetoregion']))
    {
    //This will use send command to only change to region.

    $parameters = array('command' => "change region ".$_POST['region_name'] );
    $myRemoteAdmin->SendCommand('admin_console_command', $parameters );

    }

    if (isset($_POST['createregion']))
    {
    //This will add new region data to Regions.ini 

    //Find all the ports of each Region. 
    $numBox = 0;
    $usedPorts=[];
    foreach( $tableauIni as $keyi => $vali )
       {
            $usedPorts[$numBox] = $tableauIni[$keyi]['InternalPort'];	
            $numBox++;
       }
     //Find out max port and add one to it. 
     $maxPort = max( $usedPorts );
     $maxPort = $maxPort + 1;
    //Write New Region Data to Region.ini file. 
    // Enregistrement du nouveau fichier 
    // Open for for append  
    $fp = fopen (INI_Conf_Moteur($_SESSION['opensim_select'], "address")."/bin/"."Regions/Regions.ini", "a");
 
                fputs($fp, "[".$_POST['regionname']."]\r\n");
                fputs($fp, "RegionUUID = ".GenUUID()."\r\n");
                fputs($fp, "Location = ".$_POST['locX'].",".$_POST['locY']."\r\n");
                fputs($fp, "InternalAddress = 0.0.0.0\r\n");
                fputs($fp, "InternalPort = ".$maxPort."\r\n");
                fputs($fp, "AllowAlternatePorts = False\r\n");
                fputs($fp, "ExternalHostName = ".$hostnameSSH."\r\n");
                fputs($fp, "SizeX = ".$_POST['locx']."\r\n");
                fputs($fp, "SizeY = ".$_POST['locy']."\r\n");
                fputs($fp, "SizeZ = ".$_POST['locz']."\r\n");
     fclose ($fp);
 
    //Maybe use the create region to read from Regions.ini file new region. 
    }

    // ****************************
    // *** Map en construction ****
    // ****************************
    // echo $_POST['zooming'];
    if (isset($_POST['zooming']))
    {
        $widthMap = $_POST['zooming'];
        $heightMap = $_POST['zooming'];

        // $select = "";
        $_SESSION['zooming_select'] = trim($_POST['zooming']);
        if ($_SESSION['zooming_select'] == 30) {$select1 = "selected";}
        if ($_SESSION['zooming_select'] == 40) {$select2 = "selected";}
        if ($_SESSION['zooming_select'] == 50) {$select3 = "selected";}
        if ($_SESSION['zooming_select'] == 60) {$select4 = "selected";}
        if ($_SESSION['zooming_select'] == 70) {$select5 = "selected";}
    }

    else
    {
        if ($_SESSION['zooming_select'] == 30) {$select1 = "selected";}
        if ($_SESSION['zooming_select'] == 40) {$select2 = "selected";}
        if ($_SESSION['zooming_select'] == 50) {$select3 = "selected";}
        if ($_SESSION['zooming_select'] == 60) {$select4 = "selected";}
        if ($_SESSION['zooming_select'] == 70) {$select5 = "selected";}
        $widthMap = $_SESSION['zooming_select'];
        $heightMap = $_SESSION['zooming_select'];
    }

    echo '<div style="display: flex;" >';

    echo '<form class="form-group" method=post action="">';
    echo '<div class="form-inline">';
    echo '<select class="form-control" name="zooming">';
    echo '<option value="30" name="zooming" '.$select1.'>Zoom 1</option>';
    echo '<option value="40" name="zooming" '.$select2.'>Zoom 2</option>';
    echo '<option value="50" name="zooming" '.$select3.'>Zoom 3</option>';
    echo '<option value="60" name="zooming" '.$select4.'>Zoom 4</option>';
    echo '<option value="70" name="zooming" '.$select5.'>Zoom 5</option>';
    echo '</select>';
    echo ' <button type="submit" class="btn btn-success" name="goto">';
    echo '<i class="glyphicon glyphicon-ok"></i> '.$osmw_btn_map_zoom;
    echo '</button>';

    echo '</div>';
    echo '</form>';
   
    echo '&nbsp;';

    echo '<form class="form-group" method="post" action="">';
    echo '<input type="hidden" value="'.versionrelog.'" name="versionreLog">';
    echo '<button type="submit" class="btn btn-danger" value="Purge" name="purgecache" '.$btnN3.'><i class="glyphicon glyphicon-refresh"></i> Purge <strong>Cache</strong></button>';
    echo '</form>';

    echo '</div>';
 
    echo '<br />';
    echo '<center>';
    echo '<div id="mapView" style="max-height:600px; max-width:1200px; border: 1px solid #ccc; overflow:scroll;" >';
    echo '<table>';
    for ($y = $max; $y > (-$max - 1); $y--) // Limite Y
    //for ($x = -$max; $x < ($max + 1); $x++) // Limite X
    {
        echo '<tr>';
        for ($x = -$max; $x < $max+1; $x++) // Limite X
        //for($y = -$max; $y < ($max + 1); $y++) // Limite Y
        {
            echo '<td>';


            if ($Matrice[$x][$y]['img'])
            {
                $textemap = $Matrice[$x][$y]['name'];
                $locX = $Matrice[$x][$y]['locX'];
                $locY = $Matrice[$x][$y]['locY'];

                //echo '<img class="img-responsive" src="'.$Matrice[$x][$y]['img'].'" width="'.$widthMap.'" height="'.$heightMap.'" alt="'.$textemap.'" title="'.$textemap.' '.$locX.' '.$locY.'" data-toggle="tooltip" data-placement="top">';
            echo '<a onclick="openFormTerrain( \''.$textemap.'\' , '.$locX.','.$locY.')"><img class="img-responsive" src="'.$Matrice[$x][$y]['img'].'" style="height: '.$heightMap.'px;min-width: '.$widthMap.'px;" alt="'.$textemap.'" title="'.$textemap.' '.$locX.' '.$locY.'" data-toggle="tooltip" data-placement="top"></a>';
            }

            else
            {

                $x_off = $x + 1000;
                $y_off = $y + 1000;
                $textemap = "Water (Free)";
                //echo '<img class="img-responsive" src="./img/water.jpg" width="'.$widthMap.'" height="'.$heightMap.'" alt="'.$textemap.'" title="'.$textemap.' '.$locX.' '.$locY.'" data-toggle="tooltip" data-placement="top">';
                echo '<a onclick="openForm( '.$x_off.','.$y_off.')" ><img class="img-responsive" src="./img/water.jpg" style="height: '.$heightMap.'px;min-width: '.$widthMap.'px;" alt="'.$textemap.'" title="'.$textemap.' '.$x_off.' '.$y_off.'" data-toggle="tooltip" data-placement="top"></a>';
           }
            echo '</td>';
        } 
        echo '</tr>';
    } 
    echo '</table>';
    echo '</div>';
echo '<style>';
echo '.form-popup {';
echo 'display: none;';
echo 'position: fixed;';
echo 'bottom: 0;';
echo 'right: 15px;';
echo 'border: 3px solid #f1f1f1;';
echo 'z-index: 9;';
echo "}";

echo '.form-container {';
echo 'min-width: 400px;';
echo 'max-width: 500px;';
echo 'padding: 10px;';
echo 'background-color: black;';
echo '}';


echo '.form-container #xsz,#ysz,#zsz  {';
echo 'width: 30%;';
echo 'padding: 5px;';
echo 'margin: 5px 0 0 0;';
echo 'border: none;';
echo '}';

echo '.form-container input[type=text] {';
echo 'width: 100%;';
echo 'padding: 5px;';
echo 'margin: 5px 0 0 0;';
echo 'border: none;';
echo 'background: #f1f1f1;';
echo '}';

echo '.form-container input[type=number] {';
echo 'width: 30%;';
echo 'padding: 5px;';
echo 'margin: 5px 0 0 0;';
echo 'border: none;';
echo 'background: #f1f1f1;';
echo '}';

echo "</style>";

echo '<div class="form-popup" id="MakeRegion">';
echo '<form class="form-container" method=post action="" >';
echo '<label id="regionname" ><b>Region Name</b></label><br>';
echo '<input id="locationX" type="hidden" value="" name="locX">';
echo '<input id="locationY" type="hidden" value="" name="locY">';
echo '<input type="text" placeholder="Enter Region Name" name="regionname" required><br>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<label id="xsz" ><b>X Size</b></label>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<label id="ysz" ><b>Y Size</b></label>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<label id="zsz" ><b>Z Size</b></label><br>';
echo '<input type="number" value="256" name="locx" required>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<input type="number" value="256" name="locy" required>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<input type="number" value="256" name="locz" required><br>';
echo '<BR>';
echo '<button type="submit" name="createregion" class="btn btn-success">';
echo '<i class="glyphicon glyphicon-ok"></i>';
echo 'Create</button>';

echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<button type="button" class="btn btn-danger" onclick="closeForm()">';
echo '<i class="glyphicon glyphicon-remove"></i>';
echo 'Close</button><br>';
echo '</form>';
echo '</div>';

echo '<div class="form-popup" id="SetTerrain">';
echo '<form class="form-container" method=post action=""   >';
echo '<label id="terrainpath" ><b>Load Terrain</b></label><br>';
echo '<label ><b>Load Terrain</b></label><br>';
echo '<input id="locationX2" type="hidden" value="" name="locX">';
echo '<input id="locationY2" type="hidden" value="" name="locY">';
echo '<input id="region_name" type="hidden" value="" name="region_name">';
echo '<input type="text" placeholder="Terrain Path" name="terrainpath"  value="'.getenv("HOME").$slash.$hostnameSSH.$slash.$baseBackups.$slash.'" required><br>';
echo '<BR>';
echo '<button type="submit" class="btn btn-primary" value="ChangeToRegion" name="changetoregion" >';
echo '<i class="glyphicon glyphicon-map-marker"></i>';
echo 'Set Region</button>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<button type="submit" class="btn btn-success" value="LoadTerrain" name="loadterrain" >';
echo '<i class="glyphicon glyphicon-picture"></i>';
echo 'Set Terrain </button>';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '&nbsp;';
echo '<button type="button" class="btn btn-danger" onclick="closeFormTerrain()">';
echo '<i class="glyphicon glyphicon-remove"></i>';
echo 'Close</button><br>';
echo '</form>';
echo '</div>';



    echo '<script type="text/javascript" >';

    echo 'function openForm( xset , yset ) { set_region(xset,yset );document.getElementById("MakeRegion").style.display = "block";}';
    echo 'function openFormTerrain( regname , xset , yset ) { set_terrain(regname,xset,yset );document.getElementById("SetTerrain").style.display = "block";}';
    echo 'function closeForm() {document.getElementById("MakeRegion").style.display = "none";}';
    echo 'function closeFormTerrain() {document.getElementById("SetTerrain").style.display = "none";}';

    echo ' function set_region(xset,yset) {';
    echo '       var regname = "Lake View";';
    echo '       var titleset = document.getElementById(\'regionname\');';
    echo '       titleset.textContent = "Region "+regname+" At "+xset+","+yset;';
    
    echo '       var locXset = document.getElementById(\'locationX\');';
    echo '       var locYset = document.getElementById(\'locationY\');';

    echo '       locXset.value = xset;';
    echo '       locYset.value = yset;';


    echo '};';

    echo ' function set_terrain( regname,xset,yset) {';
    echo '       var titleset = document.getElementById(\'terrainpath\');';
    echo '       titleset.textContent = "Region "+regname+" At "+xset+","+yset;';

    echo '       var locXset = document.getElementById(\'locationX2\');';
    echo '       var locYset = document.getElementById(\'locationY2\');';
    echo '       var region_name = document.getElementById(\'region_name\');';

    echo '       locXset.value = xset;';
    echo '       locYset.value = yset;';
    echo '       region_name.value = regname;';

    echo '};';



    echo 'document.onreadystatechange = function () {';

    echo 'if (document.readyState == "complete") {';
    echo 'const el = document.querySelector("#mapView");';
    if($heightMap == "70" ) {
    echo 'el.scrollTo(850,1150);';
          }
    
    if($heightMap == "60" ) {
    echo 'el.scrollTo(650,950);';
          }

    if($heightMap == "50" ) {
    echo 'el.scrollTo(450,700);';
          }

    if($heightMap == "40" ) {
    echo 'el.scrollTo(250,500);';
          }

    if($heightMap == "30" ) {
    echo 'el.scrollTo(50,300);';
          }

    echo '}}';

    echo '</script>';

    echo '</center>';		
}
else {header('Location: index.php');}
?>
