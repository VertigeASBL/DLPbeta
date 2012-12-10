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

<div id="head_admin_agenda"></div>

<h1>Edition des donn&eacute;es d'un lieu culturel</h1>

<div class="menu_back">
<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a> | 
<a href="../../-Les-lieux-partenaires-">Les lieux partenaires (public)</a> |
<a href="index_admin.php">Menu Admin</a>
</div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../fct_upload_vign_fiche_lieu.php';

$indetermine = '' ; // Texte par défaut (-- INDETERMINE --)

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'édition des fiches des lieux culturels
// edit_profil_lieu.php?new=creer pour créer une nouvelle entrée
// edit_profil_lieu.php?id=... pour éditer l'entrée
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


//-----------------------------------------
// Créer une nouvelle entrée
//-----------------------------------------
if (isset ($_GET['new']) AND $_GET['new'] == 'creer') // La variable GET qui donne l'ID à confirmer. Si NULL -> nouvelle entrée
{
	
	// Créer une entrée vide dans TABLE "table_lieu"
	mysql_query("INSERT INTO `$table_lieu` ( `nom_lieu`, `cotisation_lieu` ) VALUES ('NOUVEAU LIEU', 'no')");

	$nouvel_id_table_lieu = mysql_insert_id() ; // sera utile pour créer un lien d'accès pour éditer les données
	echo '<br><br><br><div class="info"><p>Un nouveau compte de lieu culturel a été créé et est prêt à être édité 
	<a href="edit_profil_lieu.php?id='.$nouvel_id_table_lieu.'">Continuer</a></p></div><br>' ;

	//--- mysql_close($db2dlp);
	exit();
}

//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entrée
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'update'))
{
$id = $_GET['id'];

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
		$nom_lieu = htmlentities($_POST['nom_lieu'], ENT_QUOTES);
		mysql_query("UPDATE `$table_lieu` SET `nom_lieu` = '$nom_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$nom_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `nom_lieu` = '$nom_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_nom_lieu = '<div class="error_form">Vous devez sélectionner un Lieu culturel</div>';
		$rec .= '- Vous devez sélectionner un Lieu culturel<br>';
	}
	
	// -----------------------------------------
	// TEST DE LA VIGNETTE
	$source_im = 'source_pic_1' ;
	if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
	{
		$id_update = $_GET['id'] ;
		$num_pic = '1' ; // correspond à l'extension du nom du futur fichier JPEG uploadé
		
		uploader_2 ($id_update,$num_pic);	// Upload et construction vignette

		// Afficher vignette
		echo '<img src="../vignettes_lieux_culturels/vignette_fiche_lieu1' . $id_update . '_' . $num_pic . 'jpg"  title="' . $nom_lieu . '" />';
	}
	/*else
	{
		$rec .= '- Problème UPLOAD PIC <br>';
	}*/


	// -----------------------------------------
	// TEST DIRECTEUR LIEU 
	if (isset($_POST['directeur_lieu']) AND ($_POST['directeur_lieu'] != NULL)) 
	{
		$directeur_lieu = htmlentities($_POST['directeur_lieu'], ENT_QUOTES);
		mysql_query("UPDATE `$table_lieu` SET `directeur_lieu` = '$directeur_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$directeur_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `directeur_lieu` = '$directeur_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_directeur_lieu = '<div class="error_form">Vous devez introduire le nom de la personne responsable du lieu culturel</div>';
		$rec .= '- Vous devez introduire le nom de la personne responsable du lieu culturel<br>';
	}
	
	

	// -----------------------------------------
	// TEST CONTACT LIEU 
	/*if (isset($_POST['contact_lieu']) AND ($_POST['contact_lieu'] != NULL)) 
	{
		$contact_lieu = nl2br (htmlentities($_POST['contact_lieu'], ENT_QUOTES));
		mysql_query("UPDATE `$table_lieu` SET `contact_lieu` = '$contact_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$contact_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `contact_lieu` = '$contact_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_contact_lieu = '<div class="error_form">Vous devez indiquer les moyens de contact de votre lieu culturel</div>';
		$rec .= '- Vous devez indiquer les moyens de contact de votre lieu culturel<br>';
	}
	*/
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
		else
		{
			mysql_query("UPDATE `$table_lieu` SET `contact_lieu` = '$contact_lieu_2_db' WHERE `id_lieu` = '$id' LIMIT 1 ");
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
		mysql_query("UPDATE `$table_lieu` SET `tel_lieu` = '$tel_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$tel_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `tel_lieu` = '$tel_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_tel_lieu = '<div class="error_form">Vous devez indiquer le numéro de téléphone de votre lieu culturel</div>';
		$rec .= '- Vous devez indiquer le numéro de téléphone de votre lieu culturel<br>';
	}
	
	
	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail_lieu']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_lieu']))))
	{
		$e_mail_lieu = $_POST['e_mail_lieu'];
		mysql_query("UPDATE `$table_lieu` SET `e_mail_lieu` = '$e_mail_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$e_mail_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `e_mail_lieu` = '$e_mail_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_e_mail_lieu = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
	}
	
	
	
	// -----------------------------------------
	//  TEST WEB SITE
	if (isset($_POST['web_site_lieu']) AND ($_POST['web_site_lieu'] != NULL)) 
	{
		$web_site_lieu = htmlentities($_POST['web_site_lieu'], ENT_QUOTES);
		mysql_query("UPDATE `$table_lieu` SET `web_site_lieu` = '$web_site_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$web_site_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `web_site_lieu` = '$web_site_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		//$error_web_site_lieu = '<div class="error_form">Vous devez indiquer l\'URL complète du site Web de votre lieu culturel</div>';
		//$rec .= '- Vous devez indiquer l\'URL complète du site Web de votre lieu culturel<br>';
	}
	
	
	// -----------------------------------------
	//  ADRESSE D'ADMINISTRATION
	if (isset($_POST['adresse_lieu']) AND ($_POST['adresse_lieu'] != NULL)) 
	{
		$adresse_lieu = htmlentities($_POST['adresse_lieu'], ENT_QUOTES);
		mysql_query("UPDATE `$table_lieu` SET `adresse_lieu` = '$adresse_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$adresse_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `adresse_lieu` = '$adresse_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_adresse_lieu = '<div class="error_form">Vous devez indiquer l\'adresse postale de votre lieu culturel</div>';
		$rec .= '- Vous devez indiquer l\'adresse postale de votre lieu culturel<br>';
	}

	// -----------------------------------------
	//  TEL RESERVATION
	if (isset($_POST['tel_reserv_lieu']) AND ($_POST['tel_reserv_lieu'] != NULL)) 
	{
		$tel_reserv_lieu = htmlentities($_POST['tel_reserv_lieu'], ENT_QUOTES);
		mysql_query("UPDATE `$table_lieu` SET `tel_reserv_lieu` = '$tel_reserv_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$tel_reserv_lieu = $indetermine;
		mysql_query("UPDATE `$table_lieu` SET `tel_reserv_lieu` = '$tel_reserv_lieu' WHERE `id_lieu` = '$id' LIMIT 1 ");
		$error_tel_reserv_lieu = '<div class="error_form">Vous devez indiquer le numéro de téléphone de réservation</div>';
		$rec .= '- Vous devez indiquer le numéro de téléphone de réservation<br>';
	}


	// -----------------------------------------
	// TEST Fin cotisation
	
	if (isset ($_POST['fin_cotisation_annee']) AND $_POST['fin_cotisation_annee'] != '0000'
	AND isset ($_POST['fin_cotisation_mois']) AND $_POST['fin_cotisation_mois'] != '00' 
	AND isset ($_POST['fin_cotisation_jour']) AND $_POST['fin_cotisation_jour'] != '00')
	{ 
		$fin_cotisation_admin_spec = htmlentities($_POST['fin_cotisation_annee'], ENT_QUOTES) . '-' . 
		htmlentities($_POST['fin_cotisation_mois'], ENT_QUOTES) . '-' . 
		htmlentities($_POST['fin_cotisation_jour'], ENT_QUOTES);
		
		mysql_query("UPDATE `$table_lieu` SET `cotisation_lieu` = '$fin_cotisation_admin_spec' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
	else
	{
		$fin_cotisation_admin_spec = '0000-00-00';
		$error_fin_cotisation_admin_spec = '<div class="error_form">Vous devez introduire une date d\'échéance de l\'abonnement</div>';
		$rec .= '- Vous devez introduire une date d\'échéance de l\'abonnement<br>';
	}



	// -----------------------------------------
	//  TEST EMAIL DE RESERVATION

		if (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_reservation']) OR $_POST['email_reservation'] == '')
		{
			$email_reservation = $_POST['email_reservation'];
		mysql_query("UPDATE `$table_lieu` SET `email_reservation` = '$email_reservation' WHERE `id_lieu` = '$id' LIMIT 1 ");
		}
		else
		{
			$email_reservation = '';
			$error_email_reservation = '<div class="error_form">L\'adresse e-mail pour les réservations n\'est pas obligatoire.
			Cependant, si vous mettez une adresse, celle-ci doit être valide. </div>';
			$rec .= '- L\'adresse e-mail pour les réservations n\'est pas valide<br>';
			mysql_query("UPDATE `$table_lieu` SET `email_reservation` = '$email_reservation' WHERE `id_lieu` = '$id' LIMIT 1 ");

		}


/*
	// -----------------------------------------
	// TEST LIEN DU LIEU AVEC KIDONAKI
	if (isset($_POST['lier_lieu_de_kidonaki'])
	AND $_POST['lier_lieu_de_kidonaki'] != 0 	
	AND preg_match('/[0-9]$/', $_POST['lier_lieu_de_kidonaki'])) 
	{
		$lier_lieu_de_kidonaki = htmlentities($_POST['lier_lieu_de_kidonaki'], ENT_QUOTES);
		mysql_query("UPDATE `$table_lieu` SET `auteur_kidonaki` = '$lier_lieu_de_kidonaki' WHERE `id_lieu` = '$id' LIMIT 1 ");
	}
*/


	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------------------------------
	if ($rec != NULL) // Il y a au moins un champ du formulaire qui est mal rempli
	{
		//echo '<div class="alerte">' . $rec . '</div><br>' ;
	}
	else // Tout OK -> updater la TABLE 
	{		
		echo '<div class="info">L\'entrée '.$id.' est mise à jour</div>' ; // Message confirmation
	}
}


// ----------------------------------------------------------
if (empty ($_GET['id']) OR $_GET['id'] == NULL) // La variable GET qui donne l'ID à confirmer. Si NULL -> nouvelle entrée
{
	echo '<br><br><br><div class="info"><p><a href="edit_profil_lieu.php?new=creer">Voulez-vous encoder une nouvelle entrée ?</a></p></div><br>' ;
	
	// RAZ des données
	
	$nom_lieu = '';
	$directeur_lieu = '';
	$contact_lieu = '';
	$tel_lieu = '';
	$e_mail_lieu = '';
	$web_site_lieu = '';
	$adresse_lieu = '';
	$tel_reserv_lieu = '';
	$pic_lieu = '';
	$date_edit = '';
}
else
{
	$id = $_GET['id'];
	$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$id'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id'] ne correspond à aucune entrée de la TABLE :
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
		
		// Si texte trop long et non mis dans DB, lire la variable $contact_lieu - richir +isset
		if (! isset($contact_lieu) OR $contact_lieu == NULL OR $contact_lieu == '')
		{ $contact_lieu = stripslashes($donnees ['contact_lieu']);}
		$tel_lieu = $donnees ['tel_lieu'];
		$e_mail_lieu = $donnees ['e_mail_lieu'];
		$web_site_lieu = $donnees ['web_site_lieu'];
		$adresse_lieu = stripslashes($donnees ['adresse_lieu']);
		$tel_reserv_lieu = stripslashes($donnees ['tel_reserv_lieu']);
		$pic_lieu = $donnees ['pic_lieu'];
		
		$date_edit = $donnees['cotisation_lieu'];
		$fin_cotisation_annee = substr($date_edit, 0, 4);
		$fin_cotisation_mois = substr($date_edit, 5, 2);
		$fin_cotisation_jour = substr($date_edit, 8, 2);
		
		$email_reservation = $donnees ['email_reservation'];
//		$auteur_kidonaki = $donnees ['auteur_kidonaki'];
		

		// ------------------------------------------------
		// Remplissage du formulaire
		// ------------------------------------------------
	?>
</p>
	<form name="form1" method="post" action=""  enctype="multipart/form-data" >
	  <table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
		<tr>
		  <th colspan="2"><?php 
			if (empty ($_GET['id']) OR $_GET['id'] == NULL)
			{ echo 'nouvelle entrée'; }
			else
			{
				echo 'Vous modifiez l\'entrée <b>'.$id.'</b><br />('.$nom_lieu.')'; 
			}
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
			?>		  </td>
	    </tr>
		<tr>
          <td>Responsable
		  	<?php if (isset ($error_directeur_lieu) AND $error_directeur_lieu != NULL) {echo $error_directeur_lieu ; } ?></td>
		  <td><input name="directeur_lieu" type="text" id="directeur_lieu" value="<?php if (isset($directeur_lieu)){echo $directeur_lieu;}?>" size="30" maxlength="40"></td>
	    </tr>
		<tr>
		  <td colspan="2"><p>Texte &quot;contact&quot; pour les visiteurs
		  <?php if (isset ($error_contact_lieu) AND $error_contact_lieu != NULL) {echo $error_contact_lieu ; } ?>
</p>
	        <p>			
			<textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 500px; height: 400px"><?php if (isset($contact_lieu)){echo $contact_lieu;}?></textarea>
	      </p></td>
	    </tr>
		<tr>
          <td>Numéro de t&eacute;l&eacute;phone
		<?php if (isset ($error_tel_lieu) AND $error_tel_lieu != NULL) {echo $error_tel_lieu ; } ?>		  </td>
		  <td><input name="tel_lieu" type="text" id="tel_lieu" value="<?php if (isset($tel_lieu)){echo $tel_lieu;}?>" size="30" maxlength="30"></td>
	    </tr>
		<tr>
          <td>Adresse e-mail
		<?php if (isset ($error_e_mail_lieu) AND $error_e_mail_lieu != NULL) {echo $error_e_mail_lieu ; } ?>		  </td>
		  <td><input name="e_mail_lieu" type="text" id="e_mail_lieu" value="<?php if (isset($e_mail_lieu)){echo $e_mail_lieu;}?>" size="30" maxlength="50"></td>
	    </tr>
		<tr>
		  <td>URL  compl&egrave;te du site Web
		<?php if (isset ($error_web_site_lieu) AND $error_web_site_lieu != NULL) {echo $error_web_site_lieu ; } ?>		  </td>
		  <td><input name="web_site_lieu" type="text" id="web_site_lieu" value="<?php if (isset($web_site_lieu)){echo $web_site_lieu;}?>" size="30" maxlength="70"></td>
	    </tr>
		<tr>
          <td colspan="2"><p>Adresse (n&deg;, rue, code postal, ville, pays) :</p>
              <p><?php if (isset ($error_adresse_lieu) AND $error_adresse_lieu != NULL) {echo $error_adresse_lieu ; } ?>
                <input name="adresse_lieu" type="text" id="adresse_lieu" value="<?php if (isset($adresse_lieu)){echo $adresse_lieu;}?>" size="90" maxlength="200">
            </p></td>
	    </tr>
		<tr>
		  <td>Ech&eacute;ance de la cotisation
		<?php if (isset ($error_fin_cotisation_admin_spec) AND $error_fin_cotisation_admin_spec != NULL) {echo $error_fin_cotisation_admin_spec ; } ?>		  </td>
		  <td>
		  
			jj 
			  <input name="fin_cotisation_jour" type="text" id="fin_cotisation_jour" 
			value="<?php if (isset($fin_cotisation_jour)){echo $fin_cotisation_jour;}?>" size="2" maxlength="2">
			
			mm 
			<input name="fin_cotisation_mois" type="text" id="fin_cotisation_mois" 
			value="<?php if (isset($fin_cotisation_mois)){echo $fin_cotisation_mois;}?>" size="2" maxlength="2">
			
			
			aaaa 
			<input name="fin_cotisation_annee" type="text" id="fin_cotisation_annee" 
			value="<?php if (isset($fin_cotisation_annee)){echo $fin_cotisation_annee;}?>" size="4" maxlength="4">		 </td>
		</tr>
		
<tr>
  <td><?php if (isset ($error_email_reservation) AND $error_email_reservation != NULL) {echo $error_email_reservation ; } ?>  
  R&eacute;servations en ligne : </td>
  <td><input name="email_reservation" type="text" id="email_reservation" value="<?php if (isset($email_reservation)){echo $email_reservation;}?>" size="30" maxlength="40">
    <br> 
    <span class="mini">Indiquez l'adresse email sur laquelle vous d&eacute;sirez recevoir les r&eacute;servations effectu&eacute;es par les visiteurs sur le site. Laissez ce champ vide si vous n'acceptez pas les r&eacute;servations en ligne.</span></td>
</tr>
		<tr>
          <td>Numéro de téléphone de réservation
		<?php if (isset ($error_tel_reserv_lieu) AND $error_tel_reserv_lieu != NULL) {echo $error_tel_reserv_lieu ; } ?>		  </td>
		  <td><input name="tel_reserv_lieu" type="text" id="tel_reserv_lieu" value="<?php if (isset($tel_reserv_lieu)){echo $tel_reserv_lieu;}?>" size="30" maxlength="30"></td>
	    </tr>

		<!-- tr>
		  <td>Lier ce LIEU &agrave; un compte Kidonaki</td>
		  <td>
		<?php	/*	if (isset($auteur_kidonaki) AND $auteur_kidonaki != 0)
		{
			echo 'Ce lieu est lié à l\'audeur numéro <strong>' . $auteur_kidonaki . ' dans Kidonaki</strong>' ;
		}
		else
		{
			echo '<input name="lier_lieu_de_kidonaki" type="text" id="lier_lieu_de_kidonaki" value="" size="5" maxlength="5">
			<span class="mini">Ce lieu n\'est actuellement lié à aucun Auteur de Kidonaki. <br />
			<span class="rouge">Une fois le lien créé, il ne sera plus possible de le supprimer. </span></span>' ;		
		}
		
	*/	?>
		  </td>
		</tr -->

		<tr>
		  <td colspan="2"><div align="center"> <br />
				  <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update">
				  <br />
		  </div></td>
		</tr>
	  </table>
	</form>
 	
<?php 
	}
} 

//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>
