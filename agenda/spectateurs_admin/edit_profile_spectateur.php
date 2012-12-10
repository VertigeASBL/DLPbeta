<?php 
	session_start();

	//--- changer le dossier de travail courant
	$dossier_courant = getcwd();
	chdir(dirname(__FILE__));

	require '../auth/auth_fonctions.php';
	test_spectateur_acces_page_auth(1) ;
?>

<div id="head_admin_spectateur"></div>

<?php 

// Affichage Nom, Groupe et Log Off du user
//voir_infos_spectateur () ;

require '../inc_var.php';
require '../inc_db_connect.php';
require 'ins/inc_var_inscription.php';
require '../inc_fct_base.php';
require '../fct_uploader_vignette_spectateur.php';

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'édition des données des utilisateurs (côté PUBLIC)
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

//echo '<hr />print_r SESSION '; print_r($_SESSION);

$session_id_spectateur = $_SESSION['id_spectateur'] ;
$id_spectateur = $_SESSION['id_spectateur'] ;

$id = $session_id_spectateur ;
$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$session_id_spectateur'");
$donnees = mysql_fetch_array($reponse);

// Si le compte est bloqué, empêcher le spectateur d'y accéder
if ($donnees['compte_actif_spectateur'] == 'non')
{
	echo '<br /> <div class="alerte"><br />Votre comte a été bloqué par un administrateur du site. <br />Vous pouvez prendre contact avec lui : info@demandezleprogramme.be<br /><br /></div>' ;
	//--- rétablir le dossier de travail courant
	chdir($dossier_courant);
	return;
}

$prenom_spectateur = $donnees ['prenom_spectateur'];
$nom_spectateur = $donnees ['nom_spectateur'];
$pseudo_spectateur = $donnees ['pseudo_spectateur'];
$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];

?>

<h2>Compte du spectateur <?php echo $prenom_spectateur . ' ' . $nom_spectateur ; ?></h2>

<?php


/* 
	Supprimer un lieu favoris. /-- Codé par Didier
*/
$supp = _request('supprimer');
if (isset($supp)) {
	/* On supprime l'enregistrement de la base de donnée */
	sql_delete('ag_lieux_favoris', 'id_lieu = '._request('supprimer').' AND id_spectateur = '.$_SESSION['id_spectateur']);
	/* Un petit message d'information qui va bien. */
	echo '<div class="info">Ce favoris à été supprimé.</div>';
}

/*
<div class="menu_back">
<a href="votre_menu_spectateur.php">Votre menu</a> | 
<a href="../../-Detail-d-un-spectateur-?id_spect=< ? php echo $id ; ? >">Visualiser ma page</a> | 
<a href="../../-Spectateurs-">Tous les spectateurs</a>
</div>
*/
//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entrée
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer'))
{
	$id = $_SESSION['id_spectateur'] ;

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
		{ echo '<div class="info">L\'image '.$id.'-' . $eeuu . ' a bien été effacée</div>'; }
		else 
		{ echo '<div class="alerte">ERREUR : L\'image '.$id.'-' . $eeuu . ' n\'a pas été effacée</div>'; }
	
		// Effacement de la vignette
		if (unlink ($vignette_a_effacer))
		{ echo '<div class="info">La VIGNETTE '.$id.'-' . $eeuu . ' a bien été effacée</div>'; }
		else 
		{ echo '<div class="alerte">ERREUR : La VIGNETTE '.$id.'-' . $eeuu . ' n\'a pas été effacée</div>'; }
		
		// Enlever le SET de le FLAG de la TABLE
		$image_db = 'pic_spectateur_' .  $eeuu ;
		mysql_query("UPDATE $table_spectateurs_ag SET pic_spectateur = '' WHERE id_spectateur = '$id' LIMIT 1 ") or die (mysql_error()) ;	
	}


	//-----------------------------------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------
	// = initialisation de la var qui sera testée avant d'enregistrer les données dans la DB
	// Si elle est vide => enregistrer. Sinon, elle contient le message d'erreur, et on l'affiche.
	$rec = '';
	
	
	// -----------------------------------------
	// TEST DU PRENOM DU SPECTATEUR
	if (isset($_POST['prenom_spectateur']) AND ($_POST['prenom_spectateur'] != NULL)) 
	{
		$prenom_spectateur = trim(htmlentities($_POST['prenom_spectateur'], ENT_QUOTES)); 
	}
	else
	{
		$prenom_spectateur = '';
	}


	// -----------------------------------------
	// TEST DU NOM DU SPECTATEUR
	if (isset($_POST['nom_spectateur'])) 
	{
		$nom_spectateur = trim(htmlentities($_POST['nom_spectateur'], ENT_QUOTES)); 
	}
	if (! $nom_spectateur)
	{
		$nom_spectateur = $pseudo_spectateur;
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
	// TEST DU PSEUDO DU SPECTATEUR
	/*if (isset($_POST['pseudo_spectateur']) AND ($_POST['pseudo_spectateur'] != NULL)) 
	{
		$pseudo_spectateur = htmlentities($_POST['pseudo_spectateur'], ENT_QUOTES); 
	//mysql_query("UPDATE `$table_spectateurs_ag` SET `pseudo_spectateur` = '$pseudo_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
	}
	else
	{
		$pseudo_spectateur = '';
		$rec .= '- Vous devez introduire votre pseudo. Il servira de signature à vos messages<br>';
		$error_pseudo_spectateur = '<div class="error_form">Vous devez introduire votre pseudo. Il servira de signature à vos messages</div>';
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
	}
	
	
	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail_spectateur']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_spectateur']))))
	{
		$e_mail_spectateur = $_POST['e_mail_spectateur'];
		// Test doublon de adresse email
		$req_doublon = mysql_query("SELECT e_mail_spectateur FROM $table_spectateurs_ag 
		WHERE e_mail_spectateur = '$e_mail_spectateur' AND id_spectateur != '$id' ");
		$email_doublon = mysql_fetch_array($req_doublon);
		if (isset($email_doublon['e_mail_spectateur']))
		{
			$rec .= '- L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est déjà présente dans notre base de données. 
			Veuillez en introduire une autre.<br />';

			$error_email_doublon = '<div class="error_form">L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est déjà présente dans notre base de données. 
			Veuillez en introduire une autre.</div>';
			
			$e_mail_spectateur = '<div class="error_form">L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est déjà présente dans notre base de données. Veuillez en introduire une autre.</div>';
			
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
		/*$rec .= '- Vous devez introduire de numéro de téléphone <br>';
		$error_tel = '<div class="error_form">Vous devez introduire de numéro de téléphone</div>';*/
	}
	
	
	// -----------------------------------------
	// TEST DU LOGIN
	if (isset($_POST['log_spectateur']) AND ($_POST['log_spectateur'] != NULL)) 
	{
		$log_spectateur = htmlentities($_POST['log_spectateur'], ENT_QUOTES);
		
		// Tester si le LOGIN existe déjà dans la DB ?			
		$req_existe = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE log_spectateur = '$log_spectateur' AND id_spectateur != '$id' ");
		$user_existe = mysql_fetch_array($req_existe);
		
		if (isset($user_existe['log_spectateur']))
		{
			$rec .= '- Le LOGIN ('.$log_spectateur.') a déjà été choisi. Veuillez en introduire un autre<br>';
			$error_log = '<div class="error_form">Le LOGIN ('.$log_spectateur.') a déjà été choisi. Veuillez en introduire un autre</div>';
		}
		
		elseif (!preg_match('`^\w{4,8}$`', $log_spectateur)) // \w classe prédéfinie utilisée avec les PCRE et déterminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
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

	if ($_POST['pw_spectateur'] != NULL OR $_POST['pw_spectateur_double'] != NULL) // s'il est vide, ne pas le réenregistrer
	{		
		
		$pw_spectateur = htmlentities($_POST['pw_spectateur'], ENT_QUOTES);
		$pw_spectateur_double = htmlentities($_POST['pw_spectateur_double'], ENT_QUOTES);

		if (!preg_match('`^\w{4,8}$`', $pw_spectateur)) // \w classe prédéfinie utilisée avec les PCRE et déterminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
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
		$rec .= '- Le mot de passe confirmé ne correspond pas <br>';
				$error_conf_pw = '<div class="error_form">Le mot de passe confirmé ne correspond pas</div>';
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
			$char_en_trop = strlen($description_courte_spectateur) - $max ; // Tester longueur de la chaîne de caractères
			$error_description_courte_spectateur = '<div class="error_form">La taille du texte de votre description succincte 
			dépasse la limite autorisée (' . $max . ' caractères) . 
			Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte "résumé" de l\'événement dépasse la limite autorisée<br>';			
		}
	}
	else
	{
		$description_courte_spectateur = '';
	}
	
	
	// -----------------------------------------
	// TEST DESCRIPTION LONGUE SPECTATEUR
	if (isset($_POST['description_longue_spectateur_chp']) AND ($_POST['description_longue_spectateur_chp'] != NULL)) 
	{
		$allowedTags = '<br><br />'; // Balises de style que les USERS peuvent employer
		$description_longue_spectateur = strip_tags($_POST['description_longue_spectateur_chp'],$allowedTags);
		
		$description_longue_spectateur = htmlentities($description_longue_spectateur);

		$description_longue_spectateur = wordwrap($description_longue_spectateur, 50, " ", 1);
		$description_longue_spectateur = stripslashes($description_longue_spectateur);
		
		$description_longue_spectateur_2_db = addslashes($description_longue_spectateur);
		$description_longue_spectateur_2_db = nl2br($description_longue_spectateur_2_db);
		
		
		$max=1000 ; 
		if (strlen($_POST['description_longue_spectateur_chp'])>=$max)
		{
			$char_en_trop = strlen($description_longue_spectateur) - $max ; // Tester longueur de la chaîne de caractères
			$error_description_longue_spectateur = '<div class="error_form">La taille du texte de votre description complète 
			dépasse la limite autorisée (' . $max . ' caractères) . 
			Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte "résumé" de l\'événement dépasse la limite autorisée<br>';			
		}
	}
	else
	{
		$description_longue_spectateur = '';
	}
	


	// -----------------------------------------
	// TEST IMAGES et VIGNETTE
	$id_update = $id ;
	
		$source_im = 'source_pic_1'  ;
		if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
		{
			$num_pic = 1 ; // correspond à l'extension du nom du futur fichier JPEG uploadé
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
		/*$rec .= '- Vous devez introduire le nom de votre artiste préféré<br>';
		$error_artiste_prefere_spectateur = '<div class="error_form">Vous devez introduire le nom de votre artiste préféré</div>';*/
	}
	
	// -----------------------------------------
	// TEST DU NOM DU LIEU CULTUREL PREFERE /-- Modifier par Didier 
	if (isset($_POST['lieu_prefere_spectateur'])) 
	{
		$alert = '<div class="alerte">';
		$alert_affichage = false;
		/* Les lieux sont sous forme de tableau */
		foreach (_request('lieu_prefere_spectateur') as $key => $value) {
			if (!empty($value)) {
				/* On convertit le titre en format "base de donnée DLP" */
				$value = htmlentities($value, ENT_QUOTES);
				
				/* On va chercher l'ID dans la base de donnée */
				$id_lieu_pref = sql_getfetsel('id_lieu', 'ag_lieux', 'nom_lieu = \''.$value.'\'');

				/* On vérifie que le lieu n'est pas déjà en favoris. */
				$deja_fav = sql_getfetsel('id', 'ag_lieux_favoris', 'id_spectateur = '.$_SESSION['id_spectateur'].' and id_lieu='.$id_lieu_pref);

				/* On vérifie que le lieu est bien dans la base de donnée */
				if (empty($id_lieu_pref)) {
					$alert .= '<p><strong>Le lieu '.$value.' n\'est pas référencé dans la base de donnée.</strong></p>'; 
					$alert_affichage = true;
				}
				/* On vérifie que le lieu n'est pas déjà dans les favoris */
				else if (!empty($deja_fav)) {
					$alert .= '<p><strong>Le lieux '.$value.' est déjà dans vos favoris.</strong></p>';
					$alert_affichage = true;
				}
				else {
					/* On ajoute le lieux aux favoris. */
					sql_insertq('ag_lieux_favoris', array('id_spectateur' => $_SESSION['id_spectateur'], 'id_lieu' => $id_lieu_pref));
				}
			}
		}
		$alert .= '</div>';

		/* S'il le faut on affiche la boite erreur */
		if ($alert_affichage) echo $alert;
	}
	else
	{
		$lieu_prefere_spectateur = '';
		/*$rec .= '- Vous devez introduire votre lieu culturel préféré<br>';
		$error_lieu_prefere_spectateur = '<div class="error_form">Vous devez introduire votre lieu culturel préféré</div>';*/
	}

	/* On ajoute les genres favoris */
	if (isset($_POST['genre_fav'])) {
		/* On supprime tout les favoris */
		sql_delete('ag_genre_favoris', 'id_spectateur='.$_SESSION['id_spectateur']);
		/* On ajoute les nouveaux favoris */
		foreach (_request('genre_fav') as $key => $value) {
			/* On vérifie que le code faboris est valide, il y a toujours des petits malins */
			if (!array_key_exists($value, $genres)) {
				echo '<div class="alerte"><p><strong>Le code '.$value.' n\'existe pas !</strong></p></div>';
			}
			/* On ajoute l'entrée dans la base de donnée si tout va bien */
			else sql_insertq('ag_genre_favoris', array('id_spectateur' => $_SESSION['id_spectateur'], 'code_genre' => $value));
		}
	}


	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
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
		`compte_actif_spectateur` = 'oui'
		

		WHERE `id_spectateur` = '$id' LIMIT 1 "))
		{	
		
		// richir : inscrire aux listes de diffusion, récupérer plus de données que lors de l'enregistrement... prendre les variables de la requête juste au dessus
		$adrm = addslashes($e_mail_spectateur);
		$sql = "UPDATE cmsnletter SET datnaiss='$date_naissance_spectateur' WHERE ladrm='$adrm' AND lletr='DPsp' AND letat='5'";
		$query = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		$sql = "UPDATE cmsnletter SET datnaiss='$date_naissance_spectateur' WHERE ladrm='$adrm' AND lletr='DPts' AND letat='5'";
		$query = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());

		// Message confirmation
		echo '<div class="info"><p>Vos données sont mises à jour.</p></div>' ;
		
//		echo '<META http-equiv="Refresh" content="1">' ; // Rafraichissement pour relancer la page avec les nouvelles $_SESSION

			
		// ****************************************************************************************			
		// Avertir le WebMaster de la modification du profil
		// ****************************************************************************************			

		$sujet='-- Modif profil spectateur : '. 
		html_entity_decode($prenom_spectateur) . " " . html_entity_decode($nom_spectateur) . ' -';
		
		$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
		<!--
		' . $css_email . '
		-->
		</style></head><body>
		<p>&nbsp;</p><p>&nbsp;</p>
		<h2>Un Spectateur a modifié son profil</h2>
		<p>&nbsp;</p>';
		
		$corps.='<p><b>Nom</b> : ' . $nom_spectateur . ' <br />';
		$corps.='<b>Pr&eacute;nom</b> : ' . $prenom_spectateur . ' <br />';
		$corps.='<b>E-mail</b> : ' . $e_mail_spectateur . ' </p>';

		$corps.='<p><b>Description rapide</b> : ' . $description_courte_spectateur . ' <br />';
		$corps.='<b>Description complète</b> : ' . $description_longue_spectateur . ' </p>';

		$corps.='<p><b>Artiste(s) apprécié(s)</b> : ' . $artiste_prefere_spectateur . ' <br />';
		$corps.='<b>Lieu(x) culturel(s) favori(s)</b> : ' . $lieu_prefere_spectateur . ' </p>';


		$corps.='<p align="center" style="font-size: 16px; font-weight: bold">
		<a href="http://www.demandezleprogramme.be/agenda/admin_agenda/spectateurs_edit_profile.php?spect=' . $session_id_spectateur . '"> &gt;&gt; Voir son compte</a></p>' ;
		$corps.='<p>&nbsp;</p> 
		</body></html>
		</html>'; 

		$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;

		//echo $corps ;
		$adresse_xavier = 'info@demandezleprogramme.be' ;
		 mail_beta($adresse_xavier,$sujet,$corps,$entete,$email_retour_erreur);

			//--- rétablir le dossier de travail courant
			chdir($dossier_courant);
			return;
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
		<strong>Merci de remplir correctement tous les champs marquées d\'un astérisque(*)</strong><br /><br /></p></div>' ;
	}
}
else
{
	$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$session_id_spectateur'");
	$donnees = mysql_fetch_array($reponse);
	
	// Si la valeur ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Cette entrée n\'existe pas</div><br>' ;
	}
	else
	{
		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entrée
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
		$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];

	}
}
	// ------------------------------------------------
	// Remplissage du formulaire
	// ------------------------------------------------

?>
</p>
<form name="form1" method="post" action="-Modifier-mes-infos-spectateur-" enctype="multipart/form-data">
  <table border="0" align="center" cellpadding="6" cellspacing="0" class="table_spectateur" style="font-size:1.3em;">
	<tr>
	  <th colspan="2" align="center"><?php 
			echo 'Veuillez compléter tous les champs obligatoires <br />
			afin de rendre votre profil visible sur le site<br />
			<span style="color:#009A99">(ref-' . $id_spectateur . ')</span><br />'; 
		?></th>
	</tr>
	<tr>
      <td colspan="2"><strong>Nombre d'avis post&eacute;s</strong> :
        <?php
	$tot_entrees_avis_1_spect = connaitre_nb_avis_spect ($pseudo_spectateur) ;
	echo $tot_entrees_avis_1_spect . ' '; 


// Score pour la saison actuelle :
?> <br /><strong>Avis approuv&eacute;s pour la saison actuelle</strong> :
        <?php $result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
	echo $avis_valides_spectateur . ' ' ;
          
	

	// Correspondance AVIS post&eacute;s <-> Grade et icone des spectateurs
	$result_categorie_spectateur = trouve_categorie_spectateur ($avis_valides_spectateur) ;  
    ?> <br /><strong>Niveau</strong> : <?php echo $result_categorie_spectateur['categorie_spectateur'] . ' ' ; 
	echo '<img src="agenda/design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /><br />'; 
	
	
        
      	($result_fact_chance['valeur_facteur_chance'] != 1) ? (print 'Vous augmentez donc vos chances de gain lors de la participation aux concours d\'un facteur <strong> ' . $result_fact_chance['valeur_facteur_chance'] . '</strong>') : (print 'Vos chances de gain lors de la participation aux concours ne seront pas augment&eacute;es')
	 ; ?>      </td>
    </tr>
	<tr>
	  <td><strong>Pseudo</strong></td>
	  <td>
	  <?php if (isset($pseudo_spectateur)){echo $pseudo_spectateur;}?> <span class="mini">(le pseudo ne plus être modifié)</span></td>
    </tr>
	<tr>
	  <td><strong>Pr&eacute;nom</strong> :	  <?php if (isset ($error_prenom_spectateur) AND $error_prenom_spectateur != NULL) {echo $error_prenom_spectateur ; } ?>	  </td>
	  <td><input name="prenom_spectateur" type="text" id="prenom_spectateur" value="<?php if (isset($prenom_spectateur)){echo $prenom_spectateur;}?>" size="30" maxlength="50"></td>
	</tr>
	<tr>
	  <td><strong>Nom</strong> :	  <?php if (isset ($error_nom_spectateur) AND $error_nom_spectateur != NULL) {echo $error_nom_spectateur ; } ?>	  </td>
	  <td><input name="nom_spectateur" type="text" id="nom_spectateur" value="<?php if (isset($nom_spectateur)){echo $nom_spectateur;}?>" size="30" maxlength="50"></td>
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
				echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_' . $id . '_1.jpg" />';
			}
			else
			{
				if ($donnees ['sexe_spectateur'] == 0)
				{
					echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_homme.jpg" alt="spectateur anonyme" />';
				}
				else
				{
					echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_femme.jpg" alt="spectatrice anonyme" />';
				}
			}	
							
			?> </td>
            <td>Image 
              <input name="source_pic_1" type="file" id="source_pic_1" />
  			  <br />Effacer l'image<input type="checkbox" name="effacer_image1" /></td>
    </tr>
	<tr>
      <td valign="middle"><strong>Date de naissance</strong> : </td>
	  <td><?php // LISTE JOURS

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

// LISTE MOIS
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

// LISTE d&eacute;roulante des ANNEES
echo '<select name="select_AAAA_spectateur">
<option value="vide">Ann&eacute;e</option>';
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
	  <td valign="middle"><strong>Sexe</strong> : </td>
	  <td>
	  
	  
<?php 
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
?></td>
    </tr>
	<tr>
	  <td><strong>Adresse e-mail</strong><span class="champ_obligatoire">*</span> :	  <br>
      <span class="mini">(Votre adresse n'apparaitra pas sur le site)</span>        <?php
	  if (isset ($error_e_mail) AND $error_e_mail != NULL) {echo $error_e_mail ; }  
	  if (isset ($error_email_doublon) AND $error_email_doublon != NULL) {echo $error_email_doublon ; } 
	  ?> <br />
	  
	  <span class="mini">Attention, si vous possédez une adresse YAHOO ou HOTMAIL, nous attirons votre attention sur le risque que nos messages ne vous arrivent pas ou qu'ils soient classés systématiquement dans vos spams. Nous vous invitons donc à inscrire et à utiliser une autre adresse pour le site.</span>
	  
	  </td>
	  <td><input name="e_mail_spectateur" type="text" id="e_mail_spectateur" value="<?php if (isset($e_mail_spectateur)){echo $e_mail_spectateur;}?>" size="30" maxlength="40"></td>
	</tr>
	<tr>
	  <td><strong>T&eacute;l&eacute;phone</strong> :	  <br>
      <span class="mini">(Votre t&eacute;l&eacute;phone n'apparaitra pas sur le site)</span>        <?php if (isset ($error_tel) AND $error_tel != NULL) {echo $error_tel ; } ?>	  </td>
	  <td><input name="tel_spectateur" type="text" id="tel_spectateur" value="<?php if (isset($tel_spectateur)){echo $tel_spectateur;}?>" size="30" maxlength="30"></td>
	</tr>
	<tr>
	  <td><strong>Login</strong><span class="champ_obligatoire">*</span> :	  <?php if (isset ($error_log) AND $error_log != NULL) {echo $error_log ; } ?>	  </td>
	  <td><input name="log_spectateur" type="text" id="log_spectateur" value="<?php if (isset($log_spectateur)){echo $log_spectateur;}?>" size="30" maxlength="8"></td>
	</tr>
	<tr>
	  <td><strong>Mot de passe</strong>  : <br />
	    <span class="mini">(laisser vide pour le garder inchang&eacute;)</span>
	  <?php if (isset ($error_pw) AND $error_pw != NULL) {echo $error_pw ; } ?>	  </td>
	  <td><input name="pw_spectateur" type="password" id="pw_spectateur" value="" size="8" maxlength="9"></td>
	</tr>
		<tr>
	  <td><strong>Confirmer le  mot de passe</strong> 
	  : <?php if (isset ($error_conf_pw) AND $error_conf_pw != NULL) {echo $error_conf_pw ; } ?>	  </td>
	  <td>		  <input name="pw_spectateur_double" type="password" id="pw_spectateur_double" size="8" maxlength="9"></td>
	</tr>
	    <tr>
          <td colspan="2"><strong>Votre description</strong> : (&agrave; titre d'exemple : votre activit&eacute; ou secteur professionnel, vos centres d'int&eacute;r&ecirc;ts, vos go&ucirc;ts culturels, votre &acirc;ge...)<br />
              <?php if (isset ($error_description_longue_spectateur) AND $error_description_longue_spectateur != NULL) {echo $error_description_longue_spectateur ; } ?>
          <textarea name="description_longue_spectateur_chp" cols="75" rows="6" id="description_longue_spectateur_chp"><?php if (isset($description_longue_spectateur)){echo br2nl($description_longue_spectateur);}?></textarea>          </td>
    </tr>
    <tr>
	  <td colspan="2"><p><strong>Description rapide</strong> (une phrase r&eacute;sumant de votre description) : <br />
	      <?php if (isset ($error_description_courte_spectateur) AND $error_description_courte_spectateur != NULL) {echo $error_description_courte_spectateur ; } ?>
        <textarea name="description_courte_spectateur_chp" cols="75" rows="3" id="textarea"><?php if (isset($description_courte_spectateur)){echo br2nl($description_courte_spectateur);}?></textarea>
</td>
	</tr>
	
	<tr>
      <td><strong>Artiste(s) appr&eacute;ci&eacute;(s)</strong> :  
        <?php if (isset ($error_artiste_prefere_spectateur) AND $error_artiste_prefere_spectateur != NULL) {echo $error_artiste_prefere_spectateur ; } ?>      </td>
	  <td><input name="artiste_prefere_spectateur" type="text" id="artiste_prefere_spectateur" value="<?php if (isset($artiste_prefere_spectateur)){echo $artiste_prefere_spectateur;}?>" size="30" maxlength="50"></td>
    </tr>

    <tr>
    	<td valign="top"><strong>Genre favorit:</strong></td>
    	<td>
    		<ul>
	    		<?php
	    		/* On récupère la liste actuel des favoris */
	    		$genreFav = sql_allfetsel('code_genre', 'ag_genre_favoris', 'id_spectateur='.$_SESSION['id_spectateur']);

	    		/* On affiche des checkbox pour choisir les genres que l'on préfère. */
	    		foreach ($genres as $key => $value) {
	    			$selected = '';
	    			foreach ($genreFav as $fav) {
	    				if ($key == $fav['code_genre']) $selected = 'checked="true"';
	    			}
	    			echo '<li><input type="checkbox" name="genre_fav[]" value="'.$key.'" '.$selected.' /> '.$value.'</li>';
	    		}
	    		?>
    		</ul>
    	</td>
    </tr>
	<script>
		jQuery(document).ready(function($) {
			$(".lieu_prefere_spectateur").live("keyup.autocomplete", function () {
				$(this).autocomplete({
					source: [
						<?php 
							/*
								Codé par Didier:
								On va chercher tout les producteurs et on les affiches pour faire de l'autocompletion sur les champs.
							*/
							$all_producteur = sql_allfetsel('nom_lieu', 'ag_lieux');
							foreach ($all_producteur as $key => $value) {
								echo '"'.addslashes(html_entity_decode($value['nom_lieu'], ENT_QUOTES)).'",';
							}
						?>
					],
					delay: 0
				});
			});

			$("#add_fav_lieu").live("click", function () {
				$(".lieu_culturel").append('<li><input name="lieu_prefere_spectateur[]" type="text" class="lieu_prefere_spectateur" value="" size="29" maxlength="65"></li>');
			});
		});
	</script>
	<tr>
		<td valign="top">
			<strong>Lieu(x) culturel(s) favori(s)</strong>: 
			<?php if (isset ($error_lieu_prefere_spectateur) AND $error_lieu_prefere_spectateur != NULL) {echo $error_lieu_prefere_spectateur ; } ?>
		</td>
		<td>
			<ul>
				<?php 
					/* Ici on affiche la liste actuel des favoris */
					/* On va chercher la liste dans la base de donnée */
					$listFav = sql_allfetsel(array('fav.id_lieu', 'nom_lieu'), 'ag_lieux_favoris as fav INNER JOIN ag_lieux as l ON fav.id_lieu = l.id_lieu', 'id_spectateur = '.$_SESSION['id_spectateur']);

					foreach ($listFav as $key => $value) {
						echo '<li>'.$value['nom_lieu'].' <a href="?supprimer='.$value['id_lieu'].'"><img src="'.find_in_path('assets/puce_close.jpg').'" alt="Supprimer" /></a></li>';
					}
				?>
			</ul>
			<ul class="lieu_culturel">
				<li><input name="lieu_prefere_spectateur[]" type="text" class="lieu_prefere_spectateur" value="" size="29" maxlength="65"></li>
			</ul>
			<img src="pimg/icon_add.gif" id="add_fav_lieu" alt="Ajouter" style="float: right; cursor: pointer; width: 20px;" />
		</td>
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
	//--- rétablir le dossier de travail courant
	chdir($dossier_courant);
?>
