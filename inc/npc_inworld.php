 
 <?php
include 'config.php';

	$db = mysqli_connect($hostnameBDD, $userBDD, $passBDD);
    mysqli_select_db($db,$database);	

if($_POST["parameter"] )
{
//*******************************************************************************
//#####################################################################################
// ENREGISTREMENT DU GESTIONNAIRE DE NPC 
//#####################################################################################
	if ($_POST["parameter"] == "REG_WEB_NPC" )
	{ 
		$sql ="INSERT INTO `gestionnaire` (`uuid`, `region`) VALUES ('".$_POST["uuid"]."', '".$_POST["region"]."');";
		$req = mysqli_query($db,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($db));
		
		$FILE_NPC_TIMER = $_POST["region"].".txt";
		$f = fopen($FILE_NPC_TIMER, "x+");
		fclose($f);
		
		echo 'Info_NPC,Enregistrement,Effectué,Votre Gestionnaire de NPC est opérationnel Bonne Utilisation !!!,';
	}
//#####################################################################################
// ENREGISTREMENT DU NPC CREER
//#####################################################################################
	if ($_POST["parameter"] == "NPC_CREATE" )
	{ 
		$sql ="INSERT INTO `npc` (`uuid_npc`,`firstname`,`lastname`,`region`) VALUES ('".$_POST["uuid"]."','".$_POST["firstname"]."','".$_POST["lastname"]."','".$_POST["region"]."');";
		$req = mysqli_query($db,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($db));
		echo 'Votre NPC est Enregistré et opérationnel, Bonne Utilisation !!!';
	}
//#####################################################################################
// AFFICHAGE DES NPC CREER
//#####################################################################################
	if ($_POST["parameter"] == "LISTE_NPC" )
		{

			$sql0 = "SELECT * FROM `npc` WHERE region='".$_POST["region"]."'" ;
			$req0 = mysqli_query($db,$sql0) or die('Erreur SQL !<br>'.$sql0.'<br>'.mysqli_error($db));
			$numrow0 = mysqli_num_rows($req0);
			$listeNPC ="";
			while ($data0 = mysqli_fetch_assoc($req0)) 
			{
				$listeNPC = $listeNPC.$data0["uuid_npc"]." -> ".$data0["firstname"]." ".$data0["lastname"].";";  
			}
			echo 'Info_NPC,Liste NPC,'.$numrow0.' NPCs,'.$listeNPC.',';
		}	
//#####################################################################################
// ENREGISTREMENT DES OBJETS / APPARENCE / ANIMATION
//#####################################################################################
	if ($_POST["parameter"] == "LISTE_OBJ" )
		{
			// supprimer liste de l'objet appelant
			$sql ="DELETE FROM `inventaire` WHERE `uuid_parent` = '".$_POST["uuid"]."';";
			$req = mysqli_query($db,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($db));

			// ajouter la liste dans la bdd
			$listinventaire = explode(";", $_POST["datas"]);
			
			for ($i = 0; $i <= count($listinventaire); $i++) 
			{
				if($listinventaire[$i] == "apparence")
				{
					$nb_apparence = $listinventaire[$i+1] ;
					for ($j = 1; $j <= $nb_apparence; $j++) 
					{
						$sql ="INSERT INTO `inventaire` (`uuid_parent`, `type`, `nom`, `region`) VALUES ('".$_POST["uuid"]."', 'apparence', '".$listinventaire[$i+$j+1]."','".$_POST["region"]."');";
						$req = mysqli_query($db,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($db));
					}
				}
				if($listinventaire[$i] == "animation")
				{
					$nb_animation = $listinventaire[$i+1] ;
					for ($j = 1; $j <= $nb_animation; $j++) 
					{
						$sql ="INSERT INTO `inventaire` (`uuid_parent`, `type`, `nom`, `region`) VALUES ('".$_POST["uuid"]."', 'animation', '".$listinventaire[$i+$j+1]."','".$_POST["region"]."');";
						$req = mysqli_query($db,$sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysqli_error($db));
					}
				}
			}
			echo 'Info_NPC,Inventaire,UUID Objet: '.$_POST["uuid"].',Inventaire Enregistré;,';
		}			
//#####################################################################################
// APPEL INWORLD + LECTURE ORDRE + EFFACEMENT DU DERNIER ORDRE
//#####################################################################################
	if ($_POST["parameter"] == "TIMER" )
	{
	$FILE_NPC_TIMER = $_POST["region"].".txt";
		if (file_exists($FILE_NPC_TIMER))
		{
		$monfichier = fopen($FILE_NPC_TIMER, 'r+');
		$ligne = fgets($monfichier);
		fclose($monfichier);
		
		$monfichier = fopen($FILE_NPC_TIMER, 'w+');
		fseek($monfichier, 0); // On remet le curseur au début du fichier
		fputs($monfichier, ""); // On écrit le nouveau nombre de pages vues
		fclose($monfichier);
		
		echo $ligne;
		}
	}
//*******************************************************************************
}
mysqli_close($db);


?> 
