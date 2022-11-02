<?php 
if (isset($_SESSION['authentification']))
{
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
	
    echo '<h1>'.$osmw_index_23.'</h1>';
    echo '<div class="clearfix"></div>';
	


	if (isset($_POST['exe']))
	{
        //This section requires the backend inotify script running. 
        require 'inc/config.php';
       
        if ($_POST['exe'] == "Play")
            {   
             file_put_contents( $exec_dir."opensim_start","" );
            } 

        if ($_POST['exe'] == "Stop")
            {
             file_put_contents( $exec_dir."opensim_stop", "");
            } 
  
        }
	
    /* CONSTRUCTION de la commande pour ENVOI sur la console via  SSH */
	if (isset($_POST['cmd']))
	{
        // *** Affichage mode debug ***
        // echo '# '.$_POST['cmd'].' #<br />';
	

        $RemotePort = RecupRAdminParam_Opensim(INI_Conf_Moteur($_SESSION['opensim_select'], "address")."/bin/".$FichierINIOpensim, " port = ");
        $access_password2 = RecupRAdminParam_Opensim(INI_Conf_Moteur($_SESSION['opensim_select'], "address")."/bin/".$FichierINIOpensim, " access_password = ");

        $myRemoteAdmin = new RemoteAdmin(trim($hostnameSSH), trim($RemotePort), trim($access_password2)); 

	
        if (isset($_POST['versionLog']))
		{ 
			$cheminWIN = "";
			
			if( DIRECTORY_SEPARATOR == "\\")
			{
				$cheminWIN = str_replace('/','\\', INI_Conf_Moteur($_SESSION['opensim_select'], "address")."bin/" );
			}
                        else
                        {
                              
				$cheminWIN = INI_Conf_Moteur($_SESSION['opensim_select'], "address")."bin/";
                        }
   
			unlink($cheminWIN."OpenSim.Console.log");
                        touch($cheminWIN."OpenSim.Console.log");	
		}  

        if (isset($_POST['versionreLog']))
		{ 
			$cheminWIN = "";
			
			if( DIRECTORY_SEPARATOR == "\\")
			{
				$cheminWIN = str_replace('/','\\', INI_Conf_Moteur($_SESSION['opensim_select'], "address")."bin/" );
			}
                        else
                        {
                              
				$cheminWIN = INI_Conf_Moteur($_SESSION['opensim_select'], "address")."bin/";
                        }
   
                        //	Stuff here if any 
		}


        if (isset($_POST['admin_send']))
		{ 
                        //	Stuff here if any 
                        //$myRemoteAdmin->SendCommand('admin_console_command', $parameters);
                        $parameters = array('command' => $_POST['admin_send'] );
                        $myRemoteAdmin->SendCommand('admin_console_command',$parameters);


		}



  
	}

    //******************************************************
    //  Affichage page principale
    //******************************************************

	echo Select_Simulateur($_SESSION['opensim_select']);
	
	$fichierLog = INI_Conf_Moteur($_SESSION['opensim_select'], "address").'bin/'.'OpenSim.Console.log';
	
    if (file_exists(INI_Conf_Moteur($_SESSION['opensim_select'], "address").'bin/'.'OpenSim.Console.log'))
    {
        echo '<div class="alert alert-success alert-anim" role="alert">';
        echo "File exist: <strong>" .$fichierLog.'</strong>';
        echo '</div>';
    }
    else if ($_POST['cmd'])
    {
        echo '<div class="alert alert-danger alert-anim" role="alert">';
        echo "File not exist: <strong>" .$fichierLog.'</strong>';
        echo '</div>';
    }
	
    $taille_fichier = filesize($fichierLog);

    if ($taille_fichier >= 1073741824) {$taille_fichier = round($taille_fichier / 1073741824 * 100) / 100 . " Go";}
    else if ($taille_fichier >= 1048576) {$taille_fichier = round($taille_fichier / 1048576 * 100) / 100 . " Mo";}
    else if ($taille_fichier >= 1024) {$taille_fichier = round($taille_fichier / 1024 * 100) / 100 . " Ko";}
    else {$taille_fichier = $taille_fichier . " o";}

	
	if (isset($_SESSION['authentification']) && $_SESSION['privilege']>= 3)
	{	
                echo '<div style="display: flex;" >';	
		echo '<form class="form-group" method="post" action="">';
		echo '<input type="hidden" value="'.$versionlog.'" name="versionLog">';
		echo '<button type="submit" class="btn btn-danger" value="Delete" name="cmd" '.$btnN3.'><i class="glyphicon glyphicon-trash"></i> Delete <strong>Log</strong></button>';
                echo '</form>';               

                echo '&nbsp;';

		echo '<form class="form-group" method="post" action="">';
		echo '<input type="hidden" value="'.$versionrelog.'" name="versionreLog">';
		echo '<button type="submit" class="btn btn-warning" value="Refresh" name="cmd" '.$btnN3.'><i class="glyphicon glyphicon-refresh"></i> Refresh <strong>Log</strong></button>';
		echo '</form>';
		
                echo '&nbsp;';
                echo '&nbsp;';
                echo '&nbsp;';
                echo '&nbsp;';
                echo '&nbsp;';

		echo '<form class="form-group" method="post" action="">';
		echo '<input type="hidden" value="'.$versionrelog.'" name="versionreLog">';
		echo '<button type="submit" class="btn btn-success" value="Play" name="exe" '.$btnN3.'><i class="glyphicon glyphicon-play"></i> Play </button>';
		echo '</form>';

                echo '&nbsp;';

		echo '<form class="form-group" method="post" action="">';
		echo '<input type="hidden" value="'.$versionrelog.'" name="versionreLog">';
		echo '<button type="submit" class="btn btn-danger" value="Stop" name="exe" '.$btnN3.'><i class="glyphicon glyphicon-stop"></i> Stop </button>';
		echo '</form>';

                echo '</div>';

    echo '<pre>';
    echo '<form class="form-group" method="post" action="">';
    echo '<div class="btn-group " role="group" aria-label="...">';
        echo '<div class="input-group col-xs-50">';
        echo '<input type="text" class="form-control" name="admin_send" placeholder="'.$osmw_label_cmd_send.'">';
        echo '<span class="input-group-btn">';
    echo '<button type="submit" class="btn btn-primary" value="Send Command" name="cmd" '.$btnN2.'><i class="glyphicon glyphicon-play"></i> '.$osmw_btn_msg_send.'</button>';
    echo '</span>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    echo '</pre>';


	echo '<p>'.$osmw_label_file_size.' <span class="badge">'.$taille_fichier.'</span></p>';

       }

	
	$fcontents = file($fichierLog);
	$i = sizeof($fcontents) ;
        $aff = "";

        foreach ($fcontents as $line_num => $line) {
        # Remove ANSI code from text. 
        $noansi = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $line);  
         $aff .= $noansi."\n";
         }


	if (!$aff)
    {
        if (!$fichierLog) $aff = "File not exist...";
        else $aff = "File Log ".$fichierLog." is empty ...";
    }


    echo '<pre id="scrollscreen" style="max-height: 550px;overflow-y: scroll;" >'.$aff.'</pre>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

        echo '<script>let scroll_to_bottom = document.getElementById(\'scrollscreen\');scroll_to_bottom.scrollTop = scroll_to_bottom.scrollHeight;</script>';
}
else {header('Location: index.php');}
?>
