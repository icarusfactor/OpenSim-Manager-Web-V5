<?php

	function debug($variable)
	{
		echo '<pre>' . print_r($variable, true) . '</pre>';
	}

	function str_random($length)
	{
		$alphabet = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
		return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
	}
	
	/* ************************************ */
	/* FONCTION choix du simulateur */
	/* ************************************ */
	function Select_Simulateur($simu)
	{	
		if ($translator && isset($_SESSION['authentification']))
		{
			require_once ('./inc/translator.php');
			echo('<div class="pull-right">');
			include_once("./inc/flags.php");
			echo('</div>');
		}
		require 'inc/config.php';
		
		// Formulaire de choix du moteur a selectionne
		// On se connecte a MySQL
		$db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
		mysqli_select_db($db,$database);
		
		$sql = 'SELECT * FROM moteurs';
		$req = mysqli_query($db,$sql) or die('Erreur SQL !<p>'.$sql.'</p>'.mysqli_error($db));
		

		echo '<form class="form-group" method="post" action="">';	
		echo '<div class="form-inline">';
		echo '<label for="OSSelect"></label>';
		echo '<select class="form-control" name="OSSelect">';

		while($data = mysqli_fetch_assoc($req))
		{
			$sel = "";
			if ($data['id_os'] == $_SESSION['opensim_select']) {$sel = "selected";}
			echo '<option value="'.$data['id_os'].'" '.$sel.'>'.$data['name'].' '.$data['version'].'</option>';
		}
		
		echo'</select>';
		echo' <button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-ok"></i></button>';
		echo '</div>';
		echo'</form>';

		mysqli_close($db);
	}		
	
	/* ************************************ */
	/* FONCTION affichage Entete Simulateur Selectionn� et Niveau de securit� */
	/* ************************************ */
	function Affichage_Entete($simu)
	{		
		if (isset($_POST['OSSelect'])) {$_SESSION['opensim_select'] = trim($_POST['OSSelect']);}
		
		return '<table style="width:100%"><tr>
		<td align=left><p> <strong class="label label-info">'.INI_Conf_Moteur($simu, "name").' -- '.INI_Conf_Moteur($simu, "version").'</strong></p></td>
		<td align=right><p class="pull-right"><span class="label label-danger">Security level <span class="label label-default">'.$_SESSION['privilege'].'</span></span></p></td>
		</tr></table>';
		 
	}	

	/* ************************************ */
	/* FONCTION Defini affichage bouton en fonction du Niveau de securit� */
	/* ************************************ */
	function Securite_Simulateur()
	{		
		if($_SESSION['osAutorise'] != '')
		{
			$osAutorise = explode("|", $_SESSION['osAutorise']);
			// echo count($osAutorise);
			// echo $_SESSION['osAutorise'];
			for ($i = 0; $i < count($osAutorise); $i++)
			{
				if (INI_Conf_Moteur($_SESSION['opensim_select'], "osAutorise") == $osAutorise[$i])
				{
					$moteursOK = "OK";
				}
			}
		}
		else {$moteursOK = "NOK";}
		return $moteursOK;
	}		
	
	/* ************************************ */
	/* FONCTION Recuperation du port Http du simulateur */
	/* ************************************ */
	function RecupPortHTTP_Opensim($filenameINPUT, $valRecherche)
	{
		if (file_exists($filenameINPUT)) {$filename = $filenameINPUT ;}
	
		if (!$fp = fopen($filename, "r")) 
		{			echo '<p>Erreur: '.$filename.'</p>';		}
	
		$tabfich = file($filename); 
		
		for( $i = 1 ; $i < count($tabfich) ; $i++ )
		{
			$porthttp = strstr($tabfich[$i], $valRecherche);
			
			if($porthttp)
			{
				$posEgal = strpos($porthttp,'=');
				$longueur = strlen($porthttp);
			    $RecupPortHTTP_Opensim = substr($porthttp, $posEgal + 1);
			}
		}
		fclose($fp);
		return $RecupPortHTTP_Opensim;
	}
	
	/* ************************************ */
	/* FONCTION Recuperation du port Http RAdmin et du Password RAdmin du simulateur */
	/* ************************************ */
	function RecupRAdminParam_Opensim($filenameINPUT, $valRecherche)
	{
		if (file_exists($filenameINPUT)) {$filename = $filenameINPUT ;}

		// **** Recuperation du port http du serveur ******		
		if (!$fp = fopen($filename, "r")) 
		{			echo '<p>Erreur: '.$filename.'</p>';		}
	
		$tabfich = file($filename); 
		
		for( $i = 1 ; $i < count($tabfich) ; $i++ )
		{
			$porthttp = strstr($tabfich[$i], $valRecherche);
			
			if($porthttp)
			{
				$posEgal = strpos($porthttp,'=');
				$longueur = strlen($porthttp);
			    $RecupRAdminParam_Opensim = substr($porthttp, $posEgal + 1);
			}
		}
		fclose($fp);
		return $RecupRAdminParam_Opensim;
	}

	/* ************************************ */
	/* FONCTION Recuperation en BDD de la config de OSMW */
	/* ************************************ */
    function INI_Conf($cles, $valeur)
    {
        require 'inc/config.php';
        // on se connecte � MySQL
        $db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
        mysqli_select_db($db, $database);
        
        $sql = "SELECT * FROM config";
        $req = mysqli_query($db,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($db));
        $data = mysqli_fetch_array($req);
        
        switch ($valeur)
        {
            default:
                $Version = "N.C";
            case "cheminAppli":
                $Versions = $data['cheminAppli'];
                break;
            case "destinataire":
                $Version = $data['destinataire'];
                break;
            case "Autorized":
                $Version = $data['Autorized'];
                break;
            case "NbAutorized":
                $Version = $data['NbAutorized'];
                break;
            case "VersionOSMW":
                $Version = $data['VersionOSMW'];
                break;
            case "urlOSMW":
                $Version = $data['urlOSMW'];
                break;
            }
            mysqli_close($db);
        return $Version;
    }

	/* ************************************ */
	/* FONCTION Recuperation en BDD en fonction du simulateur s�lectionn� */
	/* ************************************ */
    function INI_Conf_Moteur($cles, $valeur)
    {
        require 'inc/config.php';
        // On se connecte � MySQL
        $db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
        mysqli_select_db($db, $database);
        $sql = "SELECT * FROM moteurs WHERE id_os ='".$cles."'";
        $req = mysqli_query($db,$sql) or die('Erreur SQL !<p>'.$sql.'</p>'.mysqli_error($db));
        $data = mysqli_fetch_array($req);
        $Version = "";

        switch ($valeur)
        {
            default:
                $Version = "N.C";
            case "name":
                $Version = $data['name'];
                break;
            case "version":
                $Version = $data['version'];
                break;
            case "address":
                $Version = $data['address'];
                break;
            case "DB_OS":
                $Version = $data['DB_OS'];
                break;
            case "osAutorise":
                $Version = $data['osAutorise'];
                break;
			case "id_os":
                $Version = $data['id_os'];
                break;
            }
            mysqli_close($db);
        return $Version;
    }

	/* ************************************ */
	/* FONCTION Retourne le nombre de simulateur */
	/* ************************************ */
    function NbOpensim()
    {
        require 'inc/config.php';
        // on se connecte � MySQL
        $db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
        mysqli_select_db($db, $database);
        $sql = "SELECT * FROM moteurs";
        $req = mysqli_query($db,$sql); 
		$num_rows = mysqli_num_rows($req);
		mysqli_close($db);
        return $num_rows;
    }

	/* ************************************ */
	/* FONCTION generation de UUID pour region */
	/* ************************************ */
    function GenUUID()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

	/* ************************************ */
	/* FONCTION Test url complete pour image de la region s�lectionn� */
	/* ************************************ */
    function Test_Url($server)
    {
        $tab = parse_url($server);
        $tab['port'] = isset($tab['port']) ? $tab['port'] : 40;

		error_reporting(E_ERROR | E_PARSE);
		if (!fsockopen($tab['host'], $tab['port'], $errno, $errstr, 5))
		{
			 return false;
		} else 
		{
			 return true;
		}
	
	   error_reporting(-1);
    }
	
 	/* ************************************ */
	/* FONCTION Matrice pour transfert de fichiers */
	/* ************************************ */   
    function gen_matrice($cur)
    {
        global $PHP_SELF, $order, $asc, $order0;

        if ($dir = opendir($cur))
        {
            /* tableaux */
            $tab_dir = array();
            $tab_file = array();

            /* extraction */
            while($file = readdir($dir))
            {
                if (is_dir($cur."/".$file))
                {
                    if (!in_array($file, array(".", "..")))
                    {
                        $tab_dir[] = addScheme($file, $cur, 'dir');
                    }
                }
                else {$tab_file[] = addScheme($file, $cur, 'file');}
            }

            /* affichage */
            foreach($tab_file as $elem) 
            {
                if (assocExt($elem['ext']) <> 'inconnu')
                {
                    // echo "<p><input type='checkbox' name='matrice[]' value='".$elem['name']."'> ".$elem['name']."</p>";
                    echo '<div class="checkbox">';
                    echo '<label><input type="checkbox" name="matrice[]" value="'.$elem['name'].'">';
                    echo ' <i class="glyphicon glyphicon-saved text-success"></i> '.$elem['name'].' ';
                    echo '</label> ';
                    echo formatSize($elem['size']);
                    echo '</div>';
                }
            }
             closedir($dir);
        }
    }
	
  	/* ************************************ */
	/* FONCTION GestDirectory.php */
	/* ************************************ */   
    /* Files List */
    function list_file($cur)
    {
        global $PHP_SELF, $order, $asc, $order0;

        if ($dir = opendir($cur))
        {
            /* tableaux */
            $tab_dir = array();
            $tab_file = array();

            /* extraction */
            while($file = readdir($dir))
            {
                if (is_dir($cur."/".$file))
                {
                    if (!in_array($file, array(".", "..")))
                    {
                        $tab_dir[] = addScheme($file, $cur, 'dir');
                    }
                }
                else {$tab_file[] = addScheme($file, $cur, 'file');}
            }

            /* affichage */
            echo "<table class='table table-hover'>";
            echo "<tr>";
            echo "<th>".(($order == 'name') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Nom</th>";
            echo "<th>".(($order == 'size') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Taille</th>";
            echo "<th>".(($order == 'date') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Date</th>";
            echo "<th>".(($order == 'time') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Time</th>";
            echo "<th>".(($order == 'ext') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Type</th>";
            echo "<th>".(($order == 'name') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Download</th>";
            echo "<th>".(($order == 'name') ? (($asc == 'a')?'/\\ ':'\\/ '):'')."Delete</th>";
            echo "</tr>";

            foreach($tab_file as $elem) 
            {
                if (assocExt($elem['ext']) <> 'inconnu')
                {
                    echo '<tr>';
                    echo '<td>';
                    echo '<h5><i class="glyphicon glyphicon-saved text-success"></i>';
                    echo ' <input type="hidden" value="'.$_SESSION['opensim_select'].'" name="name_sim">';
                    echo '<input type="hidden" value="'.$elem['name'].'" name="name_file">'.$elem['name'].'';
                    echo '</h5></td>';
                    echo '<td><h5>'.formatSize($elem['size']).'</h5></td>';
                    echo '<td><h5><span class="badge">'.date("d-m-Y", $elem['date']).'</span></h5></td>';
                    echo '<td><h5><span class="badge">'.date("H:i:s a", $elem['date']).'</span></h5></td>';
                    echo '<td><h5>'.assocExt($elem['ext']).'</h5></td>';
                    echo '<td>';

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

                    if ($_SESSION['privilege'] >= 3)
                    {
                        $action = "inc/download.php?file=".INI_Conf_Moteur($_SESSION['opensim_select'], "address").$elem['name'];
                        // $btnN3 = "";
                        echo '<form method="post" action="'.$action.'">';
                        echo '<input type="hidden" value="'.$_SESSION['opensim_select'].'" name="name_sim">';
                        echo '<input type="hidden" value="'.$elem['name'].'" name="name_file">';
                        echo '<button class="btn btn-success" type="submit" value="download" name="cmd" >';
                        echo '<i class="glyphicon glyphicon-download-alt"></i> Download</button>';
                        echo '</form>';
                        echo '<td>';
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" value="'.$_SESSION['opensim_select'].'" name="name_sim">';
                        echo '<input type="hidden" value="'.$elem['name'].'" name="name_file">';
                        echo ' <button class="btn btn-danger" type="submit" value="delete" name="cmd" >';
                        echo '<i class="glyphicon glyphicon-trash"></i> Delete</button>';
                        echo '</td>';
                        echo '</form>';
                    }

                    else if ($moteursOK == "OK")
                    {

                        echo '<form method="post" action="">';
                        echo '<input type="hidden" value="'.$_SESSION['opensim_select'].'" name="name_sim">';
                        echo '<input type="hidden" value="'.$elem['name'].'" name="name_file">';
                        echo '<button class="btn btn-success" type="submit" value="download" name="cmd" '.$btnN2.'>';
                        echo '<i class="glyphicon glyphicon-download-alt"></i> Download</button>';
                        echo '<td>';
                        echo ' <button class="btn btn-danger" type="submit" value="delete" name="cmd" '.$btnN2.'>';
                        echo '<i class="glyphicon glyphicon-trash"></i> Delete</button>';
                        echo '</td>';
                        echo '</form>';
                    }
                    else
                    {
                        echo '<form method="post" action="">';
                        echo '<button class="btn btn-success" type="submit" name="cmd" disabled>';
                        echo '<i class="glyphicon glyphicon-download-alt"></i> Download</button>';
                        echo '<td>';
                        echo ' <button class="btn btn-danger" type="submit" name="cmd" disabled>';
                        echo '<i class="glyphicon glyphicon-trash"></i> Delete</button>';
                        echo '</td>';
                        echo '</form>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
            closedir($dir);
        }
    }

    /* Directory List */
    function list_dir($base, $cur, $level = 0)
    {
        global $PHP_SELF, $order, $asc;

        if ($dir = opendir($base)) 
        {
            $tab = array();

            while($entry = readdir($dir)) 
            {
                if (is_dir($base."/".$entry) && !in_array($entry, array(".", "..")))
                {
                    $tab[] = addScheme($entry, $base, 'dir');
                }
            }
            /* tri */
            usort($tab, "cmp_name");
            foreach($tab as $elem) 
            {
                $entry = $elem['name'];
                /* chemin relatif a la racine */
                $file = $base."/".$entry;
                /* marge gauche */
                for ($i = 1; $i <= (4*$level); $i++) {echo "&nbsp;";}
                
                /* l'entree est-elle le dossier courant */
                if ($file == $cur)
                {
                    echo "<p><i class='glyphicon glyphicon-star'></i> $entry</p>\n";
                }

                else
                {
                    echo "<p><i class='glyphicon glyphicon-star'></i>";
                    echo " <a href=\"$PHP_SELF?dir=". rawurlencode($file) ."&order=$order&asc=$asc\">$entry</a></p>\n";
                }

                /* l'entree est-elle dans la branche dont le dossier courant est la feuille */
                if (ereg($file."/", $cur."/")) {list_dir($file, $cur, $level + 1);}
            }
            closedir($dir);
        }
    }

    /* Extract Infos */
    function addScheme($entry,$base,$type)
    {
        $tab['name']    = $entry;
        $tab['type']    = filetype($base."/".$entry);
        $tab['date']    = filemtime($base."/".$entry);
        $tab['size']    = filesize($base."/".$entry);
        $tab['perms']   = fileperms($base."/".$entry);
        $tab['access']  = fileatime($base."/".$entry);
        $exp            = explode(".", $entry);
        $tab['ext']     = $exp[count($exp) - 1];
        return $tab;
    }

    /* Format Size */
    function formatSize($s)
    {
        /* unites */
        $u = array('Octets', 'Ko', 'Mo', 'Go', 'To');
        /* compteur de passages dans la boucle */
        $i = 0;
        /* nombre a afficher */
        $m = 0;
        /* division par 1024 */
        while($s >= 2) 
        {
            $m = $s;
            $s /= 1024;
            $i++;
        }
        if (!$i) $i = 1;
        $d = explode(".", $m);
        /* S'il y a des decimales */
        if ($d[0] != $m)
            $m = number_format($m, 2, ",", " ");
        return "<span class='badge'>".$m." ".$u[$i-1]."</span>";
    }

    /* Formate Type */
    function assocType($type) {
      /* tableau de conversion */
      $t = array(
        'fifo'      => "file",
        'char'      => "fichier special en mode caractere",
        'dir'       => "dossier",
        'block'     => "fichier special en mode bloc",
        'link'      => "lien symbolique",
        'file'      => "fichier",
        'unknown'   => "inconnu"
      );
      return $t[$type];
    }

    /* Description des Extension */
    function assocExt($ext)
    {
        $e = array(
            ''      => "inconnu",
            'oar'   => "<i class='glyphicon glyphicon-compressed'></i> Archive OAR",
            'iar'   => "<i class='glyphicon glyphicon-compressed'></i> Archive IAR",
            'xml2'  => "<i class='glyphicon glyphicon-compressed'></i> Archive IAR",
            'jpg'   => "<i class='glyphicon glyphicon-picture'></i> Image JPG",
            'bmp'   => "<i class='glyphicon glyphicon-picture'></i> Image BMP",
            'gz'    => "<i class='glyphicon glyphicon-compressed'></i> Backup GZ",
            'raw'   => "<i class='glyphicon glyphicon-picture'></i> Image BMP"
        );

        if (in_array($ext, array_keys($e)))
            return $e[$ext];
        return $e[''];
    }

    /* */
    function cmp_name($a, $b)
    {
        global $asc;
        if ($a['name'] == $b['name']) return 0;
        if ($asc == 'a') return ($a['name'] < $b['name']) ? -1 : 1;
        return ($a['name'] > $b['name']) ? -1 : 1;
    }

    /* */
    function cmp_size($a, $b)
    {
        global $asc;
        if ($a['size'] == $b['size']) return cmp_name($a, $b);
        if ($asc == 'a') return ($a['size'] < $b['size']) ? -1 : 1;
        return ($a['size'] > $b['size']) ? -1 : 1;
    }

    /* */
    function cmp_date($a, $b)
    {
        global $asc;
        if ($a['date'] == $b['date']) return cmp_name($a, $b);
        if ($asc == 'a') return ($a['date'] < $b['date']) ? -1 : 1;
        return ($a['date'] > $b['date']) ? -1 : 1;
    }

    /* */
    function cmp_access($a, $b)
    {
        global $asc;
        if ($a['access'] == $b['access']) return cmp_name($a, $b);
        if ($asc == 'a') return ($a['access'] < $b['access']) ? -1 : 1;
        return ($a['access'] > $b['access']) ? -1 : 1;
    }

    /* */
    function cmp_perms($a, $b)
    {
        global $asc;
        if ($a['perms'] == $b['perms']) return cmp_name($a, $b);
        if ($asc == 'a') return ($a['perms'] < $b['perms']) ? -1 : 1;
        return ($a['perms'] > $b['perms']) ? -1 : 1;
    }

    /* */
    function cmp_type($a, $b)
    {
        global $asc;
        if ($a['type'] == $b['type']) return cmp_name($a, $b);
        if ($asc == 'a') return ($a['type'] < $b['type']) ? -1 : 1;
        return ($a['type'] > $b['type']) ? -1 : 1;
    }

    /* */
    function cmp_ext($a, $b)
    {
        global $asc;
        if ($a['ext'] == $b['ext']) return cmp_name($a, $b);
        if ($asc == 'a') return ($a['ext'] < $b['ext']) ? -1 : 1;
        return ($a['ext'] > $b['ext']) ? -1 : 1;
    }

    /* FORMULAIRES */
    /* Fonctionp pour nettoyer et enregistrer un texte */
    function Rec($text)
    {
        $text = trim($text); // Delete white spaces after & before text
        
        if (1 === get_magic_quotes_gpc())
        {
            $stripslashes = create_function('$txt', 'return stripslashes($txt);');
        }
        
        else
        {
            $stripslashes = create_function('$txt', 'return $txt;');
        }

        // Magic quotes ?
        $text = $stripslashes($text);
        // Converts to string with " and ' as well
        $text = htmlspecialchars($text, ENT_QUOTES);
        $text = nl2br($text);
        return $text;
    }

    /* Fonction pour verifier la syntaxe d'un email */
    function IsEmail($email)
    {
        $pattern = "^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,7}$";
        return (preg_match($pattern,$email)) ? true : false;
    }
