<?php 
session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition des donn&eacute;es d'un utilisateur de l'agenda</title>


<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="../vertiny/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		elements : "ajaxfilemanager",
		theme : "advanced",
		plugins : "advhr,advimage,advlink,paste,fullscreen,noneditable,contextmenu",
		theme_advanced_toolbar_location : "top",
		theme_advanced_buttons1 : "undo,redo,separator,bold,separator,fullscreen,cleanup,",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		extended_valid_elements : "hr[class|width|size|noshade]",
		paste_use_dialog : false,
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : true,
		apply_source_formatting : true,
		force_br_newlines : true,
		force_p_newlines : false,	
		relative_urls : true,
		
		content_css : "../vertiny/css_vertiny.css"

	});

	function ajaxfilemanager(field_name, url, type, win) {
		var ajaxfilemanagerurl = "../../../../jscripts/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php";
		switch (type) {
			case "image":
				ajaxfilemanagerurl += "?type=img";
				break;
			case "media":
				ajaxfilemanagerurl += "?type=media";
				break;
			case "flash": //for older versions of tinymce
				ajaxfilemanagerurl += "?type=media";
				break;
			case "file":
				ajaxfilemanagerurl += "?type=files";
				break;
			default:
				return false;
		}
		var fileBrowserWindow = new Array();
		fileBrowserWindow["file"] = ajaxfilemanagerurl;
		fileBrowserWindow["title"] = "Ajax File Manager";
		fileBrowserWindow["width"] = "782";
		fileBrowserWindow["height"] = "440";
		fileBrowserWindow["close_previous"] = "no";
		tinyMCE.openWindow(fileBrowserWindow, {
		  window : win,
		  input : field_name,
		  resizable : "yes",
		  inline : "yes",
		  editor_id : tinyMCE.getWindowArg("editor_id")
		});
		
		return false;
	}
</script>
<!-- /tinyMCE -->


<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php
require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?>

<div id="head_admin_agenda"></div>

<!-- h1 plus bas -->

<div class="menu_back">
<a href="listing_events_gp.php">Vos &eacute;v&eacute;nements </a> | 
<a href="votre_menu.php">Votre menu </a>
</div>

<?php 
// Affichage Nom, Groupe et Log Off du user
voir_infos_user () ;
?>


<p>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../fct_upload_vign_fiche_lieu.php';
require '../logs/fct_logs.php';

$indetermine = '' ; // Texte par défaut (-- INDETERMINE --)

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'édition des fiches des lieux culturels (côté PUBLIC)
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entrée
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer'))
{
	$id = $_SESSION['lieu_admin_spec'] ;

	//-----------------------------------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------

	$rec = ''; 
	// = initialisation de la var qui sera testée avant d'enregistrer les données dans la DB
	// Si elle est vide => enregistrer. Sinon, elle contient le message d'erreur, et on l'affiche.
	
	// -----------------------------------------
	// TEST DU NOM DU LIEU CULTUREL 
	if (isset($_POST['nom_lieu']) AND ($_POST['nom_lieu'] != NULL)) 
	{
		$nom_lieu_db = htmlentities($_POST['nom_lieu'], ENT_QUOTES);
		$nom_lieu = stripslashes($nom_lieu_db) ; //Avant de remettre dans le formulaire
	}
	else
	{
		$nom_lieu = $indetermine;
		$error_nom_lieu = '<div class="error_form">Vous devez sélectionner un Lieu culturel</div>';
		$rec .= '- Vous devez sélectionner un Lieu culturel<br>';
	}
	
	// -----------------------------------------
	// TEST DE LA VIGNETTE
	$source_im = 'source_pic_1' ;
	if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
	{
		$id_update = $id ;
		$num_pic = '1' ; // correspond à l'extension du nom du futur fichier JPEG uploadé
		
		uploader_2 ($id_update,$num_pic);	// Upload et construction vignette
		
		// Afficher vignette
		echo '<img src="../vignettes_lieux_culturels/vignette_fiche_lieu1' . $id_update . '_' . $num_pic . 'jpg"  title="' . $nom_lieu . '" />';
	}


	// -----------------------------------------
	// TEST DIRECTEUR LIEU 
	if (isset($_POST['directeur_lieu']) AND ($_POST['directeur_lieu'] != NULL)) 
	{
		$directeur_lieu_db = htmlentities($_POST['directeur_lieu'], ENT_QUOTES);
		$directeur_lieu = stripslashes($directeur_lieu_db) ; // Avant de remettre dans le formulaire.
	}
	else
	{
		$directeur_lieu = $indetermine;
		$error_directeur_lieu = '<div class="error_form">Vous devez introduire le nom de la personne responsable du lieu culturel</div>';
		$rec .= '- Vous devez introduire le nom de la personne responsable du lieu culturel<br>';
	} 
	
	
	// -----------------------------------------
	// TEST CONTACT LIEU
	if (isset($_POST['ajaxfilemanager']) AND ($_POST['ajaxfilemanager'] != NULL)) 
	{
		$allowedTags = '<strong><br><br />'; // Balises de style que les USERS peuvent employer

		$contact_lieu = strip_tags($_POST['ajaxfilemanager'],$allowedTags);
		$contact_lieu = wordwrap($contact_lieu, 50, " ", 1);
		$contact_lieu = stripslashes($contact_lieu);
		$contact_lieu_2_db = addslashes($contact_lieu);
		
		$max=4000 ; 
		if (strlen($_POST['ajaxfilemanager'])>=$max)
		{	
			$char_en_trop = strlen($contact_lieu) - $max ; // Tester longueur de la chaîne de caractères
			$error_contact_lieu = '<div class="error_form">La taille du texte descriptif 
			de l\'événement dépasse la limite autorisée (' . $max . 'caractères) . 
			Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte descriptif de l\'événement dépasse la limite autorisée<br>';			
		}
	}
	else
	{
		$contact_lieu = $indetermine;
		$error_contact_lieu = '<div class="error_form">Vous devez introduire un texte descriptif de l\'événement</div>';
		$rec .= '- Vous devez introduire un texte descriptif de l\'événement<br>';
	}
	
	
	// -----------------------------------------
	// TEST TELEPHONE LIEU 
	if (isset($_POST['tel_lieu']) AND ($_POST['tel_lieu'] != NULL)) 
	{
		$tel_lieu = htmlentities($_POST['tel_lieu'], ENT_QUOTES);
	}
	else
	{
		$tel_lieu = $indetermine;
		$error_tel_lieu = '<div class="error_form">Vous devez indiquer le numéro de téléphone de votre lieu culturel</div>';
		$rec .= '- Vous devez indiquer le numéro de téléphone de votre lieu culturel<br>';
	}
	
	
	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail_lieu']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_lieu']))))
	{
		$e_mail_lieu = $_POST['e_mail_lieu'];
	}
	else
	{
		$e_mail_lieu = $indetermine;
		$error_e_mail_lieu = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
	}
	
	
	
	// -----------------------------------------
	//  TEST WEB SITE
	if (isset($_POST['web_site_lieu']) AND ($_POST['web_site_lieu'] != NULL)) 
	{
		$web_site_lieu = htmlentities($_POST['web_site_lieu'], ENT_QUOTES);
	}
	else
	{
		$web_site_lieu = $indetermine;
		//$error_web_site_lieu = '<div class="error_form">Vous devez indiquer l\'URL complète du site Web de votre lieu culturel</div>';
		//$rec .= '- Vous devez indiquer l\'URL complète du site Web de votre lieu culturel<br>';
	}
	
	
	
	// -----------------------------------------
	//  ADRESSE D'ADMINISTRATION
	if (isset($_POST['adresse_lieu']) AND ($_POST['adresse_lieu'] != NULL)) 
	{
		$adresse_lieu_db = htmlentities($_POST['adresse_lieu'], ENT_QUOTES);
		$adresse_lieu = stripslashes($adresse_lieu_db) ; // Avant de remettre dans le formulaire.
	}
	else
	{
		$adresse_lieu = $indetermine;
		$error_adresse_lieu = '<div class="error_form">Vous devez indiquer l\'adresse postale de votre lieu culturel</div>';
		$rec .= '- Vous devez indiquer l\'adresse postale de votre lieu culturel<br>';
	}

	// -----------------------------------------
	//  TEL RESERVATION
	if (isset($_POST['tel_reserv_lieu']) AND ($_POST['tel_reserv_lieu'] != NULL)) 
	{
		$tel_reserv_lieu_db = htmlentities($_POST['tel_reserv_lieu'], ENT_QUOTES);
		$tel_reserv_lieu = stripslashes($tel_reserv_lieu_db) ; // Avant de remettre dans le formulaire.
	}
	else
	{
		$tel_reserv_lieu = $indetermine;
		$error_tel_reserv_lieu = '<div class="error_form">Vous devez indiquer le numéro de téléphone de réservation</div>';
		$rec .= '- Vous devez indiquer le numéro de téléphone de réservation<br>';
	}


	
	// -----------------------------------------
	//  TEST EMAIL DE RESERVATION

		if (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_reservation'])OR $_POST['email_reservation'] == '')
		{
			$email_reservation = $_POST['email_reservation'];
		}
		else
		{
			$email_reservation = '';
			$error_email_reservation = '<div class="error_form">L\'adresse e-mail pour les réservations n\'est pas obligatoire.
			Cependant, si vous mettez une adresse, celle-ci doit être valide. </div>';
			$rec .= '- L\'adresse e-mail pour les réservations n\'est pas valide<br>';
		}
	

	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------------------------------
	if ($rec == NULL) // Le formulaire  est bien rempli -> updater la TABLE 
	{
		$approuv_check = mysql_query("UPDATE $table_lieu SET
		nom_lieu = '$nom_lieu_db' ,
		directeur_lieu = '$directeur_lieu_db' ,
		contact_lieu = '$contact_lieu_2_db' ,
		tel_lieu = '$tel_lieu' ,
		e_mail_lieu = '$e_mail_lieu' ,
		web_site_lieu = '$web_site_lieu' ,
		adresse_lieu = '$adresse_lieu_db' ,
		tel_reserv_lieu = '$tel_reserv_lieu_db' ,
		email_reservation = '$email_reservation'
		WHERE id_lieu = '$id' LIMIT 1 ");	
		
		if ($approuv_check)
		{
			// Enregistrer cette modifivation dans le rapport
			log_write ($id, '1', $id, 'Modification profil Lieu culturel', '') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
			
			
			// Message confirmation
			echo ' <br /> <br /> <br /> <div class="info"><br />Vos données sont mises à jour<br /> <br /> 
			Veuillez vous assurer que la mise en page <br /> est telle que vous le souhaitez 
			en vérifiant <a href="../../-Details-lieux-culturels-?id_lieu=' . $id . '" target="_blank">cette page</a><br /> <br /> 
			<a href="votre_menu.php">Retour au menu</a> <br /> <br /> </div>' ; 
			exit();
		}
		else 
		{			
			// Enregistrer l'erreur dans le rapport
			$error_2_rapport = 'Erreur lors de modification profil Lieu culturel. Requête = ' . urlencode(mysql_error()) ;
			log_write ($id, '1', $id, $error_2_rapport, 'send_mail') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
			echo '<div class="alerte">Erreur ! Les données n\'ont pas été enregistrées</div><br>' ; }
	}
}
else 
{ 	// Si on n'a pas appuyé sur le bouton
	$id = $_SESSION['lieu_admin_spec'] ;
	
	$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$id'");
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
		
		$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$id'");
		$donnees = mysql_fetch_array($reponse);
		
		$nom_lieu = stripslashes($donnees ['nom_lieu']);
		$directeur_lieu = stripslashes($donnees ['directeur_lieu']);
		$contact_lieu = stripslashes($donnees ['contact_lieu']);
		$tel_lieu = $donnees ['tel_lieu'];
		$e_mail_lieu = $donnees ['e_mail_lieu'];
		$web_site_lieu = $donnees ['web_site_lieu'];
		$adresse_lieu = stripslashes($donnees ['adresse_lieu']);
		$tel_reserv_lieu = stripslashes($donnees ['tel_reserv_lieu']);
		$pic_lieu = $donnees ['pic_lieu'];
		
		$date_edit = $donnees['cotisation_lieu'];
		$fin_cotisation_annee = substr($date_edit, 0, 4);
		$fin_cotisation_mois = substr($date_edit, 5, 2);
		$email_reservation = $donnees ['email_reservation'];
	}
}


//-------------------------
// Titre de la page
echo'<h1 align="center">' . $nom_lieu . ' - Edition de la fiche descriptive de votre lieu culturel</h1>' ;


// ------------------------------------------------
// Remplissage du formulaire
// ------------------------------------------------
?>
</p>
<form name="form1" method="post" action=""  enctype="multipart/form-data" >
<table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
<tr>
  <th colspan="2" align="center"><?php 
	
	echo $nom_lieu.' <i>(id'.$id.')</i>'; 
	
	?></th>
</tr>
<tr>
  <td>Nom du lieu culturel
<?php if (isset ($error_nom_lieu) AND $error_nom_lieu != NULL) {echo $error_nom_lieu ; } ?></td>
  <td><input name="nom_lieu" type="text" id="nom_lieu" value="<?php if (isset($nom_lieu)){echo $nom_lieu;}?>" size="70" maxlength="200"></td>
</tr>
<tr>
  <td>Vignette </td>
  <td>		  

<input name="source_pic_1" type="file" id="source_pic_1" />

  <?php if (isset ($donnees ['pic_lieu']) AND $donnees ['pic_lieu'] == 'set' )
	{
		$destination = '../vignettes_lieux_culturels/vignette_fiche_lieu_' . $id .'_1.jpg' ;
		echo '<img src="'. $destination . '" title="' . $nom_lieu . '">';
	}
	?>  </td>
</tr>
<tr>
  <td>Responsable
	<?php if (isset ($error_directeur_lieu) AND $error_directeur_lieu != NULL) {echo $error_directeur_lieu ; } ?></td>
  <td><input name="directeur_lieu" type="text" id="directeur_lieu" value="<?php if (isset($directeur_lieu)){echo $directeur_lieu;}?>" size="30" maxlength="40"></td>
</tr>
<tr>
  <td colspan="2" align="center"><p>Infos pour les visiteurs : acc&egrave;s, transport, etc.
    <?php if (isset ($error_contact_lieu) AND $error_contact_lieu != NULL) {echo $error_contact_lieu ; } ?>
</p>
	<p>
	
	<textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 500px; height: 400px"><?php if (isset($contact_lieu)){echo $contact_lieu;}?></textarea>
  </p></td>
</tr>
<tr>
  <td>Numéro de téléphone
<?php if (isset ($error_tel_lieu) AND $error_tel_lieu != NULL) {echo $error_tel_lieu ; } ?>  </td>
  <td><input name="tel_lieu" type="text" id="tel_lieu" value="<?php if (isset($tel_lieu)){echo $tel_lieu;}?>" size="30" maxlength="30"></td>
</tr>
<tr>
  <td>Adresse e-mail
<?php if (isset ($error_e_mail_lieu) AND $error_e_mail_lieu != NULL) {echo $error_e_mail_lieu ; } ?>  </td>
  <td><input name="e_mail_lieu" type="text" id="e_mail_lieu" value="<?php if (isset($e_mail_lieu)){echo $e_mail_lieu;}?>" size="30" maxlength="50"></td>
</tr>
<tr>
  <td colspan="2"><p>Adresse URL  compl&egrave;te (avec http://www)
    <?php if (isset ($error_web_site_lieu) AND $error_web_site_lieu != NULL) {echo $error_web_site_lieu ; } ?></p>
    <p>
      <input name="web_site_lieu" type="text" id="web_site_lieu" value="<?php if (isset($web_site_lieu)){echo $web_site_lieu;}?>" size="60" maxlength="70">
    </p></td>
  </tr>
<tr>
  <td colspan="2"><p>Adresse d'administration (n&deg;, rue, code postal, ville, pays) :</p>
	  <p><?php if (isset ($error_adresse_lieu) AND $error_adresse_lieu != NULL) {echo $error_adresse_lieu ; } ?>
		<input name="adresse_lieu" type="text" id="adresse_lieu" value="<?php if (isset($adresse_lieu)){echo $adresse_lieu;}?>" size="90" maxlength="200">
	</p></td>
</tr>

<tr>
  <td><?php if (isset ($error_email_reservation) AND $error_email_reservation != NULL) {echo $error_email_reservation ; } ?>  
  R&eacute;servations en ligne </td>
  <td><input name="email_reservation" type="text" id="email_reservation" value="<?php if (isset($email_reservation)){echo $email_reservation;}?>" size="30" maxlength="40">
    <br>
    <span class="mini">Indiquez l'adresse email sur laquelle vous d&eacute;sirez recevoir les r&eacute;servations effectu&eacute;es par les visiteurs sur le site. Laissez ce champ vide si vous n'acceptez pas les r&eacute;servations en ligne.</span></td>
</tr>
<tr>
  <td>Numéro de téléphone de réservation
<?php if (isset ($error_tel_reserv_lieu) AND $error_tel_reserv_lieu != NULL) {echo $error_tel_reserv_lieu ; } ?>  </td>
  <td><input name="tel_reserv_lieu" type="text" id="tel_reserv_lieu" value="<?php if (isset($tel_reserv_lieu)){echo $tel_reserv_lieu;}?>" size="30" maxlength="30"></td>
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
