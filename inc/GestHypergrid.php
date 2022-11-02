<?php 

 // Create an image cache object
require 'inc/class.cacheimg.php';

if (isset($_SESSION['authentification']))
{
	echo Affichage_Entete($_SESSION['opensim_select']);
	$cacheimg = new CacheIMG();
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
	
    echo '<h1>'.$osmw_index_21.'</h1>';
    echo '<div class="clearfix"></div>';

	echo Select_Simulateur($_SESSION['opensim_select']);
	
    //******************************************************
    //  Affichage page principale
    //******************************************************
			
	//grid	secondlife://Red%20Dragon%20Nite%20Club/236/79/23
	//hg	secondlife://hg.osgrid.org:80/Red%20Dragon%20Nite%20Club/236/79/23
	//v3hg	secondlife://http|!!hg.osgrid.org|80+Red+Dragon+Nite+Club
	//Hop 	hop://hg.osgrid.org:80/Red%20Dragon%20Nite%20Club/236/79/23
	
    // *******************************************************	
    // Lecture des regions.ini et enregistrement dans Matrice
    // *******************************************************
    $db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
    mysqli_select_db($db, $database);
	$sql = 'SELECT * FROM moteurs WHERE id_os="'.$_SESSION['opensim_select'].'"';
    $req = mysqli_query($db,$sql) or die('Erreur SQL !<p>'.$sql.'</p>'.mysqli_error($db));

		
    while ($data = mysqli_fetch_assoc($req))
	{
        $hypergrid = "";
		$hypergrid = $data['hypergrid'];
        $i = 0;

        if ($hypergrid <> "")
        {
			$tableauIni = parse_ini_file($data['address']."/bin/"."Regions/".$FichierINIRegions, true);

            if ($tableauIni == FALSE && $data['name'] == $_SESSION['opensim_select'])
            {
                echo '<div class="alert alert-danger alert-anim" role="alert">';
                echo 'Probleme de lecture du fichier .ini <strong>'.$FichierINIRegions.'</strong> ('.$data['address'].'Regions/)';
                echo '</div>';
            }

            $cpt = 0;
            echo  '<div class="row">';

            foreach( $tableauIni as $keyi => $vali )
            {
                $filename = $data['address']."/bin/".$FichierINIOpensim;
                if (!$fp = fopen($filename, "r"))
                {
                    echo '<div class="alert alert-danger alert-anim" role="alert">';
                    echo "Echec d'ouverture du fichier <strong>".$filename."</strong>";
                    echo '</div>';
                }	
				else	
				{
					$srvOS  = RecupPortHTTP_Opensim($filename, "http_listener_port");
				}

                //Recuperation des images de regions 
                $uuid = str_replace("-", "", $tableauIni[$keyi]['RegionUUID']);
           
                $location                               = explode(",", $tableauIni[$keyi]['Location']);
		$coordX                                 = $location[0];
		$coordY                                 = $location[1];

		$ImgMap1 = "http://".$hostnameSSH.":".trim($srvOS)."/map-1-".$coordX."-".$coordY."-objects.jpg";
		$ImgMap2 = $cacheimg->get_cache( $uuid , $ImgMap1 , 0  );
		$uuid2 = trim($uuid, "-");
		$ImgMap = "https://".$hostnameSSH."/cache/".$uuid2;

                if (Test_Url($ImgMap1) == false) {$ImgMap = "img/offline.jpg";}

                $TD_Hypergrid  = "";
                $TD_Hypergrid .= '<div class="col-sm-6 col-md-4">';
                $TD_Hypergrid .= '<div class="thumbnail">';
                $TD_Hypergrid .= '<img class=" btn3d btn btn-default img-rounded" alt="" src="'.$ImgMap.'">';
                $TD_Hypergrid .= '<div class="caption text-center">';
                $TD_Hypergrid .= '<h4>Region: <strong>'.$keyi.'</strong></h4>';
                $TD_Hypergrid .= '<p>Location: <strong>'.$tableauIni[$keyi]['Location'].'</strong></p>';
                $TD_Hypergrid .= '<div class="btn-group" role="group" aria-label="...">';
                $TD_Hypergrid .= '<a class="btn btn-primary"	href="secondlife://'.$keyi.'/128/128/25">Grid</a>';
                $TD_Hypergrid .= '<a class="btn btn-success"	href="secondlife://'.$hypergrid.'/'.$keyi.'/128/128/25">Hg</a>';
                $TD_Hypergrid .= '<a class="btn btn-warning"	href="secondlife://'.$hypergrid.'/'.$keyi.'">v3Hg</a>';
				$TD_Hypergrid .= '<a class="btn btn-danger"		href="hop://'.$hypergrid.'/'.$keyi.'/128/128/25">hop</a>';
                $TD_Hypergrid .= '</div>';
                $TD_Hypergrid .= '</div>';
                $TD_Hypergrid .= '</div>';
                $TD_Hypergrid .= '</div>';

                if ($cpt == 3)
                {
                    echo $TD_Hypergrid;
                    $cpt = 0;
                }

                else
                {
                    echo $TD_Hypergrid;
                    $cpt++;
                }
            }
            echo '</div>';
        }
    }
    mysqli_close($db);	
	
}
?>
