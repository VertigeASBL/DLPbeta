

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Admin : Edition du compte d'un Spectateur</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>



<div id="head_admin_agenda"></div>

<?php
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../fct_uploader_vignette_spectateur.php';

//---------------------------------------------------------
// Test sur variable GET :
//---------------------------------------------------------
//L'entr�e donn�e par GET existe-t-elle :
if (empty ($_GET['spect']) OR $_GET['spect'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais param�tre GET<br>
	<a href="spectateurs_listing.php">Retour</a></div>' ;
	exit();
}
else
{
	$id_spectateur = htmlentities($_GET['spect'], ENT_QUOTES);
}


// Quel est le PSEUDO du spectateur ? n�cessaire pour obtenir la correspondance entre tables
$reponse_spectat = mysql_query("SELECT * FROM ag_spectateurs WHERE id_spectateur = $id_spectateur ");
$donnees_spectat = mysql_fetch_array($reponse_spectat) ;

$pseudo_spectateur = $donnees_spectat['pseudo_spectateur'] ;
$prenom_spectateur = $donnees_spectat['prenom_spectateur'] ;
$nom_spectateur = $donnees_spectat['nom_spectateur'] ;
$avis_valides_spectateur = $donnees_spectat['avis_valides_spectateur'];


?>
<h1>Vous modifiez le compte de <?php echo $pseudo_spectateur . ' (' . $prenom_spectateur . ' ' . $nom_spectateur . ')' ; ?></h1>

<div class="menu_back">
<a href="../../-Detail-d-un-spectateur-?id_spect=<?php echo $id_spectateur ; ?>">Visualiser la page</a> | 
<a href="spectateurs_listing.php" >Listing des spectateurs</a> | 
<a href="index_admin.php">Menu Admin</a>

</div>

<p>

<?php 


//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'�dition des donn�es des utilisateurs (c�t� PUBLIC)
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
$donnees = mysql_fetch_array($reponse);

//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entr�e
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer'))
{
	$id = $id_spectateur ;

	//--------------------------------------------------------------------------------------------------------------
	// Faut-il EFFACER UNE IMAGE
	//--------------------------------------------------------------------------------------------------------------

	$eeuu = 1; 
	if (isset($_POST['effacer_image' . $eeuu]) AND ($_POST['effacer_image' . $eeuu] != NULL)) 
	{

		$pic_a_effacer = '../' . $folder_pics_spectateurs . 'spect_' . $id . '_' . $eeuu . '.jpg' ;
		$vignette_a_effacer = '../' . $folder_pics_spectateurs . 'vi_spect_' . $id . '_' . $eeuu . '.jpg' ; 
	
		// Effacement de l'image
		if (unlink ($pic_a_effacer))
		{ echo '<div class="info">L\'image '.$id.'-' . $eeuu . ' a bien �t� effac�e</div>'; }
		else 
		{ echo '<div class="alerte">ERREUR : L\'image '.$id.'-' . $eeuu . ' n\'a pas �t� effac�e</div>'; }
	
		// Effacement de la vignette
		if (unlink ($vignette_a_effacer))
		{ echo '<div class="info">La VIGNETTE '.$id.'-' . $eeuu . ' a bien �t� effac�e</div>'; }
		else 
		{ echo '<div class="alerte">ERREUR : La VIGNETTE '.$id.'-' . $eeuu . ' n\'a pas �t� effac�e</div>'; }
		
		// Enlever le SET de le FLAG de la TABLE
		$image_db = 'pic_spectateur_' .  $eeuu ;
		mysql_query("UPDATE $table_spectateurs_ag SET pic_spectateur = '' WHERE id_spectateur = '$id' LIMIT 1 ") or die (mysql_error()) ;	
	}


	//-----------------------------------------------------------------------------------
	// Verification des donn�es entr�es par l'utilateur
	//-----------------------------------------------------------------------------------
	// = initialisation de la var qui sera test�e avant d'enregistrer les donn�es dans la DB
	// Si elle est vide => enregistrer. Sinon, elle contient le message d'erreur, et on l'affiche.
	$rec = '';
	
	
	// -----------------------------------------
	// TEST DU PRENOM DU SPECTATEUR
	if (isset($_POST['prenom_spectateur']) AND ($_POST['prenom_spectateur'] != NULL)) 
	{
		$prenom_spectateur = htmlentities($_POST['prenom_spectateur'], ENT_QUOTES); 
	//mysql_query("UPDATE `$table_spectateurs_ag` SET `prenom_spectateur` = '$prenom_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$prenom_spectateur = $prenom_spectateur;
		$rec .= '- Vous devez introduire votre prenom<br>';
		$error_prenom_spectateur = '<div class="error_form">Vous devez introduire votre pr�nom</div>';
	}


	// -----------------------------------------
	// TEST DU NOM DU SPECTATEUR
	if (isset($_POST['nom_spectateur']) AND ($_POST['nom_spectateur'] != NULL)) 
	{
		$nom_spectateur = htmlentities($_POST['nom_spectateur'], ENT_QUOTES); 
	//mysql_query("UPDATE `$table_spectateurs_ag` SET `nom_spectateur` = '$nom_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$nom_spectateur = '';
		$rec .= '- Vous devez introduire votre nom<br>';
		$error_nom_spectateur = '<div class="error_form">Vous devez introduire votre pr�nom</div>';
	}
	
	
	// -----------------------------------------
	// TEST DU PSEUDO DU SPECTATEUR
	/*if (isset($_POST['pseudo_spectateur']) AND ($_POST['pseudo_spectateur'] != NULL)) 
	{
		$pseudo_spectateur = htmlentities($_POST['pseudo_spectateur'], ENT_QUOTES); 
	//mysql_query("UPDATE `$table_spectateurs_ag` SET `pseudo_spectateur` = '$pseudo_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$pseudo_spectateur = '';
		$rec .= '- Vous devez introduire votre pseudo. Il servira de signature � vos messages<br>';
		$error_pseudo_spectateur = '<div class="error_form">Vous devez introduire votre pseudo. Il servira de signature � vos messages</div>';
	}*/
	
	
	// -----------------------------------------
	// TEST DU SEXE DU SPECTATEUR
	if (isset($_POST['sexe_spectateur']) AND ($_POST['sexe_spectateur'] != NULL)) 
	{
		$sexe_spectateur = htmlentities($_POST['sexe_spectateur'], ENT_QUOTES); 
	}
	else
	{
		$sexe_spectateur = '';
		$rec .= '- Vous devez s�lectionner votre sexe<br>';
		$error_nom_spectateur = '<div class="error_form">Vous devez s�lectionner votre sexe</div>';
	}
	
	
	// -----------------------------------------
	// TEST DATES ANNIVERSAIRE DU SPECTATEUR
	
	if (isset($_POST['select_AAAA_spectateur']) 
	AND $_POST['select_AAAA_spectateur'] != NULL 
	AND preg_match('/[0-9]{4}$/', $_POST['select_AAAA_spectateur']) 
	AND $_POST['select_AAAA_spectateur'] != 'vide' 
	AND isset($_POST['select_MM_spectateur']) 
	AND $_POST['select_MM_spectateur'] != NULL 
	AND preg_match('/[0-9]{2}$/', $_POST['select_MM_spectateur']) 
	AND $_POST['select_MM_spectateur'] != 'vide'
	AND isset($_POST['select_JJ_spectateur']) 
	AND $_POST['select_JJ_spectateur'] != NULL 
	AND preg_match('/[0-9]{2}$/', $_POST['select_JJ_spectateur']) 
	AND $_POST['select_JJ_spectateur'] != 'vide')
	{
		$AAAA_spectateur = htmlentities($_POST['select_AAAA_spectateur'], ENT_QUOTES);
		$MM_spectateur = htmlentities($_POST['select_MM_spectateur'], ENT_QUOTES);
		$JJ_spectateur = htmlentities($_POST['select_JJ_spectateur'], ENT_QUOTES);
		/*$date_event_spectateur = $AAAA_spectateur.$MM_spectateur.$JJ_spectateur ;
		$time_event_spectateur = date(mktime(0, 0, 0, $MM_spectateur, $JJ_spectateur, $AAAA_spectateur));
		echo $time_event_spectateur .'<br>'.$time_event_fin .'<br>'.$periode_max .'<br>';*/ 
	}
	else
	{
		$AAAA_spectateur = '0000';
		$MM_spectateur = '00';
		$JJ_spectateur = '00';
		/*$error_date = '<div class="error_form">Vous devez indiquer votre date anniversaire</div>';
		$rec .= '- Vous devez indiquer votre date anniversaire<br>';*/
	}
	$date_naissance_spectateur = $AAAA_spectateur.'-'.$MM_spectateur.'-'.$JJ_spectateur ;



	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail_spectateur']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_spectateur']))))
	{
		$e_mail_spectateur = $_POST['e_mail_spectateur'];
		// Test doublon de adresse email
		$req_doublon = mysql_query("SELECT e_mail_spectateur FROM $table_spectateurs_ag 
		WHERE e_mail_spectateur = '$e_mail_spectateur' AND id_spectateur != '$id_spectateur' ");
		$email_doublon = mysql_fetch_array($req_doublon);
		if (isset($email_doublon['e_mail_spectateur']))
		{
			$rec .= '- L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est d�j� pr�sente dans notre base de donn�es. 
			Veuillez en introduire une autre.<br />';

			$error_email_doublon = '<div class="error_form">L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est d�j� pr�sente dans notre base de donn�es. 
			Veuillez en introduire une autre.</div>';
			
			$e_mail_spectateur = '<div class="error_form">L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est d�j� pr�sente dans notre base de donn�es. Veuillez en introduire une autre.</div>';
			
			$e_mail_spectateur = '';
		}
		else
		{
			//mysql_query("UPDATE `$table_spectateurs_ag` SET `e_mail_spectateur` = '$e_mail_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}
	}
	else
	{
		$e_mail_spectateur = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_e_mail = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
	}
	
	
	// -----------------------------------------
	// TEST TELEPHONE 
	if (isset($_POST['tel_spectateur']) AND ($_POST['tel_spectateur'] != NULL)) 
	{
		$tel_spectateur = htmlentities($_POST['tel_spectateur'], ENT_QUOTES);
		//mysql_query("UPDATE `$table_spectateurs_ag` SET `tel_spectateur` = '$tel_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$tel_spectateur = '';
		/*$rec .= '- Vous devez introduire de num�ro de t�l�phone <br>';
		$error_tel = '<div class="error_form">Vous devez introduire de num�ro de t�l�phone</div>';*/
	}
	
	
	// -----------------------------------------
	// TEST DU LOGIN
	if (isset($_POST['log_spectateur']) AND ($_POST['log_spectateur'] != NULL)) 
	{
		$log_spectateur = htmlentities($_POST['log_spectateur'], ENT_QUOTES);
		
		// Tester si le LOGIN existe d�j� dans la DB ?			
		$req_existe = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE log_spectateur = '$log_spectateur' AND id_spectateur != '$id' ");
		$user_existe = mysql_fetch_array($req_existe);
		
		if (isset($user_existe['log_spectateur']))
		{
			$rec .= '- Le LOGIN ('.$log_spectateur.') a d�j� �t� choisi. Veuillez en introduire un autre<br>';
			$error_log = '<div class="error_form">Le LOGIN ('.$log_spectateur.') a d�j� �t� choisi. Veuillez en introduire un autre</div>';
		}
		
		elseif (!preg_match('`^\w{4,8}$`', $log_spectateur)) // \w classe pr�d�finie utilis�e avec les PCRE et d�terminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
		{
			$rec .= '- Votre LOGIN doit contenir entre 4 et 8 caracteres alphanumeriques <br>';
			$error_log = '<div class="error_form">Votre LOGIN doit contenir entre 4 et 8 caracteres alphanumeriques</div>';
		}
		
		else
		{
			//mysql_query("UPDATE `$table_spectateurs_ag` SET `log_spectateur` = '$log_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}	
	}
	else
	{
		$log_spectateur = '';
		$rec .= '- Vous devez introduire un LOGIN <br>';
		$error_log = '<div class="error_form">Vous devez introduire un LOGIN</div>';
	}
	
	
	// -----------------------------------------
	// TEST PW

	if ($_POST['pw_spectateur'] != NULL OR $_POST['pw_spectateur_double'] != NULL) // s'il est vide, ne pas le r�enregistrer
	{		
		
		$pw_spectateur = htmlentities($_POST['pw_spectateur'], ENT_QUOTES);
		$pw_spectateur_double = htmlentities($_POST['pw_spectateur_double'], ENT_QUOTES);

		if (!preg_match('`^\w{4,8}$`', $pw_spectateur)) // \w classe pr�d�finie utilis�e avec les PCRE et d�terminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
		{
			$rec .= '- Votre MOT DE PASSE doit contenir entre 4 et 8 caracteres alphanumeriques <br>';
			$error_pw = '<div class="error_form">Votre MOT DE PASSE doit contenir entre 4 et 8 caracteres alphanumeriques</div>';
		}
		
		elseif ($pw_spectateur_double == NULL OR !preg_match('`^\w{4,8}$`', $pw_spectateur_double)) 
		{
			$rec .= '- Vous devez confirmer le mote de passe <br>';
			$error_conf_pw = '<div class="error_form">Vous devez confirmer le mote de passe</div>';
		}
		
		elseif ($pw_spectateur_double != $pw_spectateur) 
		{
		$rec .= '- Le mot de passe confirm� ne correspond pas <br>';
				$error_conf_pw = '<div class="error_form">Le mot de passe confirm� ne correspond pas</div>';
		}
		else
		{
			mysql_query("UPDATE `$table_spectateurs_ag` SET `pw_spectateur` = '$pw_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}
	}

	// -----------------------------------------
	// TEST DESCRIPTION COURTE SPECTATEUR
	if (isset($_POST['description_courte_spectateur_chp']) AND ($_POST['description_courte_spectateur_chp'] != NULL)) 
	{
		$allowedTags = '<br><br />'; // Balises de style que les USERS peuvent employer
		$description_courte_spectateur = strip_tags($_POST['description_courte_spectateur_chp'],$allowedTags);

		$description_courte_spectateur = htmlentities($description_courte_spectateur);

		$description_courte_spectateur = wordwrap($description_courte_spectateur, 50, " ", 1);
		$description_courte_spectateur = stripslashes($description_courte_spectateur);
		
		$description_courte_spectateur_2_db = addslashes($description_courte_spectateur);
		$description_courte_spectateur_2_db = nl2br($description_courte_spectateur_2_db);

		$max=300 ; 
		if (strlen($_POST['description_courte_spectateur_chp'])>=$max)
		{	
			$char_en_trop = strlen($description_courte_spectateur) - $max ; // Tester longueur de la cha�ne de caract�res
			$error_description_courte_spectateur = '<div class="error_form">La taille du texte "r�sum� 
			de l\'�v�nement" d�passe la limite autoris�e (' . $max . 'caract�res) . 
			Il y a ' . $char_en_trop . ' caract�res en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte "r�sum�" de l\'�v�nement d�passe la limite autoris�e<br>';			
			
		}		
	}
	else
	{
		$description_courte_spectateur = '';
		$error_description_courte_spectateur = '<div class="error_form">Vous devez introduire ici une description rapide de vous</div>';
		$rec .= '- Vous devez introduire un texte descriptif de l\'�v�nement<br>';
	}
	


	// -----------------------------------------
	// TEST DESCRIPTION LONGUE SPECTATEUR
	if (isset($_POST['description_longue_spectateur_chp']) AND ($_POST['description_longue_spectateur_chp'] != NULL)) 
	{
		$allowedTags = '<br><br />'; // Balises de style que les USERS peuvent employer
		$description_longue_spectateur = strip_tags($_POST['description_longue_spectateur_chp'],$allowedTags);
		
				//$description_longue_spectateur = htmlentities($_POST['description_longue_spectateur_chp']);
			$description_longue_spectateur = htmlentities($description_longue_spectateur);

		$description_longue_spectateur = wordwrap($description_longue_spectateur, 50, " ", 1);
		$description_longue_spectateur = stripslashes($description_longue_spectateur);
		
		$description_longue_spectateur_2_db = addslashes($description_longue_spectateur);
		$description_longue_spectateur_2_db = nl2br($description_longue_spectateur_2_db);
		
		$max=1000 ; 
		if (strlen($_POST['description_longue_spectateur_chp'])>=$max)
		{	
			$char_en_trop = strlen($description_longue_spectateur) - $max ; // Tester longueur de la cha�ne de caract�res
			$error_description_longue_spectateur = '<div class="error_form">La taille du texte "r�sum� 
			de l\'�v�nement" d�passe la limite autoris�e (' . $max . 'caract�res) . 
			Il y a ' . $char_en_trop . ' caract�res en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte "r�sum�" de l\'�v�nement d�passe la limite autoris�e<br>';			
		}
		else
		{
			//mysql_query("UPDATE `$table_spectateurs_ag` SET `description_longue_spectateur` = '$description_longue_spectateur_2_db' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}		
	}
	else
	{
		$description_longue_spectateur = '';
		$error_description_longue_spectateur = '<div class="error_form">Vous devez introduire ici votre description compl�te</div>';
		$rec .= '- Vous devez introduire un texte descriptif de l\'�v�nement<br>';
	}
	



	
	// -----------------------------------------
	// TEST IMAGES et VIGNETTE
	$id_update = $id ;
	
		$source_im = 'source_pic_1'  ;
		if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
		{
			$num_pic = 1 ; // correspond � l'extension du nom du futur fichier JPEG upload�
			uploader_vignette_spect ($id_update,1);	// Upload et construction vignette
		}
		else
		{
			$pic_spectateur = $donnees ['pic_spectateur'];
		}



	// -----------------------------------------
	// TEST DE L'ARTISTE PREFERE
	if (isset($_POST['artiste_prefere_spectateur']) AND ($_POST['artiste_prefere_spectateur'] != NULL)) 
	{
		$artiste_prefere_spectateur = htmlentities($_POST['artiste_prefere_spectateur'], ENT_QUOTES); 
	//mysql_query("UPDATE `$table_spectateurs_ag` SET `artiste_prefere_spectateur` = '$artiste_prefere_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$artiste_prefere_spectateur = '';
		/*$rec .= '- Vous devez introduire le nom de votre artiste pr�f�r�<br>';
		$error_artiste_prefere_spectateur = '<div class="error_form">Vous devez introduire le nom de votre artiste pr�f�r�</div>';*/
	}
	
	// -----------------------------------------
	// TEST DU NOM DU LIEU CULTUREL PREFERE :
	if (isset($_POST['lieu_prefere_spectateur']) AND ($_POST['lieu_prefere_spectateur'] != NULL)) 
	{
		$lieu_prefere_spectateur = htmlentities($_POST['lieu_prefere_spectateur'], ENT_QUOTES); 
	//mysql_query("UPDATE `$table_spectateurs_ag` SET `lieu_prefere_spectateur` = '$lieu_prefere_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$lieu_prefere_spectateur = '';
		/*$rec .= '- Vous devez introduire votre lieu culturel pr�f�r�<br>';
		$error_lieu_prefere_spectateur = '<div class="error_form">Vous devez introduire votre lieu culturel pr�f�r�</div>';*/
	}



	// -----------------------------------------
	// VALIDITE DU COMPTE :
	if (isset ($_POST['compte_actif_spectateur']) AND ($_POST['compte_actif_spectateur'] != NULL))
	{
		$compte_actif_spectateur = htmlentities($_POST['compte_actif_spectateur'], ENT_QUOTES); 
	}



	// -----------------------------------------
	// TEST DU NOMBRE D'AVIS VALIDES :
	if (isset($_POST['avis_valides_spectateur']) AND ($_POST['avis_valides_spectateur'] != NULL)) 
	{
		$avis_valides_spectateur = htmlentities($_POST['avis_valides_spectateur'], ENT_QUOTES); 
	}


	//-----------------------------------------------------------------------------------------------------------
	// Traitement du r�sultat des donn�es entr�es par l'utilateur
	//-----------------------------------------------------------------------------------------------------------

	if ($rec == NULL) // Il y a au moins un champ du formulaire qui est mal rempli
	{	
		//`pseudo_spectateur` = '$pseudo_spectateur', 

		if (mysql_query("UPDATE `$table_spectateurs_ag` SET 
		`nom_spectateur` = '$nom_spectateur',
		`prenom_spectateur` = '$prenom_spectateur',
		`sexe_spectateur` = '$sexe_spectateur',
		`date_naissance_spectateur` = '$date_naissance_spectateur',
		`e_mail_spectateur` = '$e_mail_spectateur', 
		`tel_spectateur` = '$tel_spectateur', 
		`log_spectateur` = '$log_spectateur', 
		`description_courte_spectateur` = '$description_courte_spectateur_2_db', 
		`description_longue_spectateur` = '$description_longue_spectateur_2_db', 
		`artiste_prefere_spectateur` = '$artiste_prefere_spectateur', 
		`lieu_prefere_spectateur` = '$lieu_prefere_spectateur',
		`compte_actif_spectateur` = '$compte_actif_spectateur',
		`avis_valides_spectateur` = '$avis_valides_spectateur' 

		WHERE `id_spectateur` = '$id' LIMIT 1 "))
		 {		
		// Message confirmation
		echo '<div class="info"><p>Vos donn�es sont mises � jour.</p>
		<p>Veuillez patienter</p></div>' ;
		echo '<META http-equiv="Refresh" content="1">' ; // Rafraichissement pour relancer la page avec les nouvelles $_SESSION

		exit();
		}
		else
		{
			echo mysql_error();
		}
	}	
	else
	{
		// Message erreur
		echo '<div class="alerte"><p><br />
		<strong>Merci de remplir correctement tous les champs marqu�es d\'un ast�risque(*)</strong><br /><br /></p></div>' ;
	}

}
else
{
	$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
	$donnees = mysql_fetch_array($reponse);
	
	// Si la valeur ne correspond � aucune entr�e de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Cette entr�e n\'existe pas</div><br>' ;
	}
	else
	{
		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entr�e
		// ------------------------------------------------
		
		$prenom_spectateur = $donnees ['prenom_spectateur'];
		$nom_spectateur = $donnees ['nom_spectateur'];
		$sexe_spectateur = $donnees ['sexe_spectateur'];
		$pseudo_spectateur = $donnees ['pseudo_spectateur'];

		if (isset($donnees ['date_naissance_spectateur']) AND ($donnees ['date_naissance_spectateur'] != NULL))
		{ $date_naissance_spectateur = $donnees ['date_naissance_spectateur']; }
		else { $date_naissance_spectateur = '0000-00-00'; }
		//$date_naissance_spectateur = $donnees ['date_naissance_spectateur'];
		$AAAA_spectateur = substr($date_naissance_spectateur, 0, 4);
		$MM_spectateur = substr($date_naissance_spectateur, 5, 2);	
		$JJ_spectateur = substr($date_naissance_spectateur, 8, 2);
				
		$e_mail_spectateur = $donnees ['e_mail_spectateur'];
		$tel_spectateur = $donnees ['tel_spectateur'];
		$log_spectateur = $donnees ['log_spectateur'];
		$pw_spectateur = $donnees ['pw_spectateur'];
	
		$description_courte_spectateur = $donnees ['description_courte_spectateur'];
		$description_longue_spectateur = $donnees ['description_longue_spectateur'];

		$pic_spectateur = $donnees ['pic_spectateur'];
	
		$artiste_prefere_spectateur = $donnees ['artiste_prefere_spectateur'];
		$lieu_prefere_spectateur = $donnees ['lieu_prefere_spectateur'];
		
		$compte_actif_spectateur = $donnees ['compte_actif_spectateur'];
		$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];
	}
}
	// ------------------------------------------------
	// Remplissage du formulaire
	// ------------------------------------------------

?>
</p>
<form name="form1" method="post" action="" enctype="multipart/form-data">
  <table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
	<tr>
	  <th colspan="2"><?php 
			echo 'Compte de <b>' . $prenom_spectateur . ' ' . $nom_spectateur . '</b><br />'; 
		?></th>
	</tr>
	<tr>
	  <td colspan="2">Nombre d'avis post&eacute;s :
        <?php 
	$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur'");
	$donnees_3 = mysql_fetch_array($retour_3);
	$_tot_entrees = $donnees_3['nbre_entrees'];
	echo $_tot_entrees . ' '; 
		
        
	// Score pour la saison actuelle :
	?>
	<br />Avis approuv�s pour la saison actuelle :
	<?php $result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
	echo $avis_valides_spectateur . ' ' ;
	
	// Correspondance AVIS post�s <-> Grade et icone des spectateurs
	$result_categorie_spectateur = trouve_categorie_spectateur ($avis_valides_spectateur) ; 	
	?> <br />Niveau : <?php echo $result_categorie_spectateur['categorie_spectateur'] . ' ' ; 
	echo '<img src="../design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /><br />'; 

	// Augmentation des chances
	($result_fact_chance['valeur_facteur_chance'] != 1) ? (print 'Vous augmentez donc vos chances de gain lors de la participation aux concours d\'un facteur <strong> ' . $result_fact_chance['valeur_facteur_chance'] . '</strong>') : (print 'Vos chances de gain lors de la participation aux concours ne seront pas augment&eacute;es') ; 
 ?>
 
</td>
    </tr>
	<tr>
      <td>Pseudo<span class="champ_obligatoire">*</span> : </td>
	  <td><?php if (isset($pseudo_spectateur)){echo $pseudo_spectateur;}?>
        <span class="mini">(le pseudo ne peut pas �tre modifi� car il fait le lien avec les AVIS)</span></td>
    </tr>
	<tr>
	  <td>Pr&eacute;nom<span class="champ_obligatoire">*</span> :	  <?php if (isset ($error_prenom_spectateur) AND $error_prenom_spectateur != NULL) {echo $error_prenom_spectateur ; } ?>	  </td>
	  <td><input name="prenom_spectateur" type="text" id="prenom_spectateur" value="<?php if (isset($prenom_spectateur)){echo $prenom_spectateur;}?>" size="30" maxlength="50"></td>
	</tr>
	<tr>
	  <td>Nom<span class="champ_obligatoire">*</span> :	  <?php if (isset ($error_nom_spectateur) AND $error_nom_spectateur != NULL) {echo $error_nom_spectateur ; } ?>	  </td>
	  <td><input name="nom_spectateur" type="text" id="nom_spectateur" value="<?php if (isset($nom_spectateur)){echo $nom_spectateur;}?>" size="30" maxlength="50"></td>
	</tr>
	<tr>
      <td valign="middle">Sexe<span class="champ_obligatoire">*</span> : </td>
	  <td><?php 
echo '<select name="sexe_spectateur">';
	
	// HOMME
	echo '<option value="0"';		
	if ($sexe_spectateur == 0 )
	{ echo ' selected="selected" '; }
	echo '>Homme</option>';
	
	// FEMME
	echo '<option value="1"';		
	if ($sexe_spectateur == 1 )
	{ echo ' selected="selected" '; }
	echo '>Femme</option>';
	
echo '</select>';
?>      </td>
    </tr>
	<!--<tr>
      <td>Pseudo<span class="mini"> (votre pseudo sera affich&eacute; comme signature de vos messages)</span> 
        <?php// if (isset ($error_pseudo_spectateur) AND $error_pseudo_spectateur != NULL) {echo $error_pseudo_spectateur ; } ?>      </td>
	  <td><input name="pseudo_spectateur" type="text" id="pseudo_spectateur" value="<?php// if (isset($pseudo_spectateur)){echo $pseudo_spectateur;}?>" size="30" maxlength="50"></td>
    </tr> -->
	<tr>
	  
	  
	  <td align="center" valign="middle"><?php 		
			// Afficher image visiteur
			 if (isset ($donnees ['pic_spectateur']) AND $donnees ['pic_spectateur'] == 'set' )
			{
				echo '<img src="../' . $folder_pics_spectateurs . 'vi_spect_' . $id_spectateur . '_1.jpg" />';
			}
			else
			{
				if ($donnees ['sexe_spectateur'] == 0)
				{
					echo '<img src="../' . $folder_pics_spectateurs . 'vi_spect_anonyme_homme.jpg" alt="spectateur anonyme" />';
				}
				else
				{
					echo '<img src="../' . $folder_pics_spectateurs . 'vi_spect_anonyme_femme.jpg" alt="spectatrice anonyme" />';
				}
			}	
							
			?> </td>
            <td><p>Image 
              <input name="source_pic_1" type="file" id="source_pic_1" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image1" /></label></p></td>
    </tr>
	<tr>
	  <td valign="middle">Date de naissance : </td>
	  <td>      
	  <?php // LISTE JOURS

echo '<select name="select_JJ_spectateur">
<option value="vide">Jour</option>';
for ($list_j_comp=1 ; $list_j_comp<=31 ; $list_j_comp++)
{
	$list_j_comp = add_chaine_2_car ($list_j_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_j_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($JJ_spectateur == $list_j_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_j_comp.'</option>';
}
echo '</select>';
?>
        <?php // LISTE MOIS
echo '<select name="select_MM_spectateur">
<option value="vide">Mois</option>';
for ($list_m_comp=1 ; $list_m_comp<=12 ; $list_m_comp++)
{
	$list_m_comp = add_chaine_2_car ($list_m_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_m_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($MM_spectateur == $list_m_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_m_comp.'</option>';
}
echo '</select>';

?>
       <?php // LISTE d&eacute;roulante des ANNEES
echo '<select name="select_AAAA_spectateur">
<option value="vide">Ann�e</option>';
$annee_moins_10 = date('Y') - 10 ;
for ($list_a_comp=1930 ; $list_a_comp<=$annee_moins_10 ; $list_a_comp++)
{
	echo '<option value="' . $list_a_comp .'"';		
	// Faut-il pr&eacute;s&eacute;lectionner ?
	// Si aucune date encore introduite, d&eacute;j&agrave; s&eacute;lectionner l'ann&eacute;e en cours
	if ($AAAA_spectateur + 0 == 0)
	{ $AAAA_spectateur = date('Y'); }
	
	if ($AAAA_spectateur == $list_a_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_a_comp.'</option>';
}
echo '</select>';

?></td>
    </tr>
	<tr>
	  <td>Adresse e-mail<span class="champ_obligatoire">*</span> :	  <?php
	  if (isset ($error_e_mail) AND $error_e_mail != NULL) {echo $error_e_mail ; }  
	  if (isset ($error_email_doublon) AND $error_email_doublon != NULL) {echo $error_email_doublon ; } 
	  ?>	  </td>
	  <td><input name="e_mail_spectateur" type="text" id="e_mail_spectateur" value="<?php if (isset($e_mail_spectateur)){echo $e_mail_spectateur;}?>" size="30" maxlength="40"></td>
	</tr>
	<tr>
	  <td>T&eacute;l&eacute;phone :	  <?php if (isset ($error_tel) AND $error_tel != NULL) {echo $error_tel ; } ?>	  </td>
	  <td><input name="tel_spectateur" type="text" id="tel_spectateur" value="<?php if (isset($tel_spectateur)){echo $tel_spectateur;}?>" size="30" maxlength="30"></td>
	</tr>
	<tr>
	  <td>Login<span class="champ_obligatoire">*</span> :	  <?php if (isset ($error_log) AND $error_log != NULL) {echo $error_log ; } ?>	  </td>
	  <td><input name="log_spectateur" type="text" id="log_spectateur" value="<?php if (isset($log_spectateur)){echo $log_spectateur;}?>" size="30" maxlength="8"></td>
	</tr>
	<tr>
	  <td>Mot de passe (laisser vide pour le garder inchang&eacute;) 
	  <?php if (isset ($error_pw) AND $error_pw != NULL) {echo $error_pw ; } ?>	  </td>
	  <td><input name="pw_spectateur" type="password" id="pw_spectateur" value="" size="8" maxlength="9"></td>
	</tr>
		<tr>
	  <td>Confirmer le  mot de passe 
	  <?php if (isset ($error_conf_pw) AND $error_conf_pw != NULL) {echo $error_conf_pw ; } ?>	  </td>
	  <td>		  <input name="pw_spectateur_double" type="password" id="pw_spectateur_double" size="8" maxlength="9"></td>
	</tr>
	    <tr>
          <td colspan="2"><strong>Votre description</strong> <span class="champ_obligatoire">*</span>: (&agrave; titre d'exemple : votre activit&eacute; ou secteur professionnel, vos centres d'int&eacute;r&ecirc;ts, vos go&ucirc;ts culturels, votre &acirc;ge...)<br />
              <?php if (isset ($error_description_longue_spectateur) AND $error_description_longue_spectateur != NULL) {echo $error_description_longue_spectateur ; } ?>
          <textarea name="description_longue_spectateur_chp" cols="75" rows="6" id="description_longue_spectateur_chp"><?php if (isset($description_longue_spectateur)){echo br2nl($description_longue_spectateur);}?></textarea>          </td>
    </tr>
    <tr>
	  <td colspan="2"><p><strong>Description rapide</strong> (une phrase r&eacute;sumant de votre description) <span class="champ_obligatoire">*</span> : <br />
	      <?php if (isset ($error_description_courte_spectateur) AND $error_description_courte_spectateur != NULL) {echo $error_description_courte_spectateur ; } ?>
        <textarea name="description_courte_spectateur_chp" cols="75" rows="3" id="textarea"><?php if (isset($description_courte_spectateur)){echo br2nl($description_courte_spectateur);}?></textarea>
</td>
	</tr>
	
	<tr>
      <td>Artiste(s) appr&eacute;ci&eacute;(s)
        <?php if (isset ($error_artiste_prefere_spectateur) AND $error_artiste_prefere_spectateur != NULL) {echo $error_artiste_prefere_spectateur ; } ?>      </td>
	  <td><input name="artiste_prefere_spectateur" type="text" id="artiste_prefere_spectateur" value="<?php if (isset($artiste_prefere_spectateur)){echo $artiste_prefere_spectateur;}?>" size="30" maxlength="50"></td>
    </tr>

	
	<tr>
      <td>Lieu(x) culturel(s) favori(s) 
        <?php if (isset ($error_lieu_prefere_spectateur) AND $error_lieu_prefere_spectateur != NULL) {echo $error_lieu_prefere_spectateur ; } ?>      </td>
	  <td><input name="lieu_prefere_spectateur" type="text" id="lieu_prefere_spectateur" value="<?php if (isset($lieu_prefere_spectateur)){echo $lieu_prefere_spectateur;}?>" size="30" maxlength="65"></td>
    </tr>
	<tr>
	  <td>Avis valid&eacute;s     </td>
	  <td><input name="avis_valides_spectateur" type="text" id="avis_valides_spectateur" value="<?php if (isset($avis_valides_spectateur)){echo $avis_valides_spectateur;}?>" size="3" maxlength="4"></td>
    </tr>
	<tr>
	  <td>Etat du compte : </td>
	  <td>

	    <label><input name="compte_actif_spectateur" type="radio" value="oui" <?php if (isset ($compte_actif_spectateur) AND $compte_actif_spectateur == 'oui') {echo 'checked="checked"' ; } ?>>Actif</label><br>
        <label><input name="compte_actif_spectateur" type="radio" value="non" <?php if (isset ($compte_actif_spectateur) AND $compte_actif_spectateur == 'non') {echo 'checked="checked"' ; } ?>>Bloqu&eacute;</label><br>
        <label><input name="compte_actif_spectateur" type="radio" value="new"<?php if (isset ($compte_actif_spectateur) AND $compte_actif_spectateur == 'new') {echo 'checked="checked"' ; } ?>>Nouvellement cr&eacute;&eacute; (pour obliger l'utilisateur &agrave; l'&eacute;diter avant qu'il ne redevienne actif </label></td>
    </tr>
	<tr>
	  <td colspan="2"><div align="center"> <br />
			  <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
			  <br />
	  </div></td>
	</tr>
  </table>
</form>
 	
<?php 

//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>
