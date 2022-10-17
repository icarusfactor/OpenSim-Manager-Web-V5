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
        // while (list($keyi, $vali) = each($tableauIni))
        // while (list($keyi) = each($tableauIni))
        foreach( $tableauIni as $keyi => $vali )
        {
                        
			// *** Recuperation du port Http du Simulateur
			$srvOS  = RecupPortHTTP_Opensim($data['address']."/bin/".$FichierINIOpensim, "http_listener_port");

            // Recuperation des valeurs ET enregistrement des valeurs dans le tableau
            echo $key.$tableauIni[$key]['RegionUUID'].$tableauIni[$key]['Location'].$tableauIni[$key]['InternalPort'].'<br>';
            $location                               = explode(",", $tableauIni[$keyi]['Location']);
            $coordX                                 = $location[0] - $px - $ox;
            $coordY                                 = $location[1] - $py - $oy;
            $Matrice[$coordX][$coordY]['name']      = $keyi;
            $uuid                                   = str_replace("-", "", $tableauIni[$keyi]['RegionUUID']);
            $ImgMap1                                 = "http://".$tableauIni[$keyi]['ExternalHostName'].":".trim($srvOS).$slash."map-1-".$location[0]."-".$location[1]."-objects.jpg";
            $ImgMap2 = $cacheimg->get_cache( $uuid , $ImgMap1 , 4  );
            $uuid2 = trim($uuid, "-"); 
	    $ImgMap = "https://".$hostnameSSH."/cache/".$uuid2;

            $Matrice[$coordX][$coordY]['img']       = $ImgMap;
            $Matrice[$coordX][$coordY]['ip']        = $tableauIni[$keyi]['ExternalHostName'];
            $Matrice[$coordX][$coordY]['port']      = $tableauIni[$keyi]['InternalPort'];	
            $Matrice[$coordX][$coordY]['uuid']      = $key.$tableauIni[$keyi]['RegionUUID'];
            $Matrice[$coordX][$coordY]['hypergrid'] = $data["hypergrid"];
        }
    }
    mysqli_close($db);

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
                echo '<img class="img-responsive" src="'.$Matrice[$x][$y]['img'].'" style="height: '.$heightMap.'px;min-width: '.$widthMap.'px;" alt="'.$textemap.'" title="'.$textemap.' '.$locX.' '.$locY.'" data-toggle="tooltip" data-placement="top">';
            }

            else
            {
                $textemap = "Water (Free)";
                //echo '<img class="img-responsive" src="./img/water.jpg" width="'.$widthMap.'" height="'.$heightMap.'" alt="'.$textemap.'" title="'.$textemap.' '.$locX.' '.$locY.'" data-toggle="tooltip" data-placement="top">';
                echo '<img class="img-responsive" src="./img/water.jpg" style="height: '.$heightMap.'px;min-width: '.$widthMap.'px;" alt="'.$textemap.'" title="'.$textemap.' '.$locX.' '.$locY.'" data-toggle="tooltip" data-placement="top">';
           }
            echo '</td>';
        } 
        echo '</tr>';
    } 
    echo '</table>';
    echo '</div>';
    echo '<script type="text/javascript" >';
    echo 'document.onreadystatechange = function () {';
    echo 'if (document.readyState == "complete") {';
    echo 'console.log("Hello world!");';
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
