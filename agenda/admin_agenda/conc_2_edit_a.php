<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition d'une fiche concours </title>
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


<script language='javascript'>
function confirmation_effacer(conc_id, lot_id) {
if (confirm("Voulez-vous vraiment effacer le LOT num�ro "+lot_id+" ?")) {
	window.location.href='conc_2_edit_a.php?id_conc='+conc_id+'&enlever_element_lot='+lot_id;
	}
}
</script>



<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="head_admin_agenda"></div>
<h1>Edition d'une fiche concours </h1>

<div class="menu_back">
<a href="conc_2_listing.php" >Listing des concours  </a> | <a href="index_admin.php">Menu Admin</a></div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../fct_upload_vign_concours.php';

$indetermine = '' ;

$id_conc = htmlentities($_GET['id_conc'], ENT_QUOTES);


//---------------------------------------------------------------
// Effacer un �l�ment de LOT
//---------------------------------------------------------------
if (isset($_GET['enlever_element_lot']) AND ($_GET['enlever_element_lot'] != NULL))
{
	$element_lot_a_enlever = htmlentities($_GET['enlever_element_lot'], ENT_QUOTES);
	$id_conc = htmlentities($_GET['id_conc'], ENT_QUOTES);

	echo 'Enlever array [' .$element_lot_a_enlever . ']';

	$enleve_lot_db = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
	$donnees_enleve_lot_db = mysql_fetch_array($enleve_lot_db) ;
	$lots_conc = $donnees_enleve_lot_db['lots_conc'] ;

	if (isset($lots_conc) AND ($lots_conc != NULL))
	{
		$array_lot_de_DB = unserialize($lots_conc) ; // r�cup�ration de la variable Lot de la DB
		//print_r($array_lot_de_DB);
		//array_push ($array_lot_de_DB, $array_lot_de_DB); // remettre le(s) lot(s) contenu dans la DB
		
		array_splice ($array_lot_de_DB, $element_lot_a_enlever,1); // array array_splice ( array &$input, int $offset [, int $length [, array $replacement]] )    http://be.php.net/manual/fr/function.array-splice.php
		
		$new_array_lot_serialized = serialize ($array_lot_de_DB) ;
		 
		$approuv_update_lot = mysql_query("UPDATE $table_ag_conc_fiches SET
		lots_conc = '$new_array_lot_serialized'
		WHERE id_conc = '$id_conc' LIMIT 1 ") or print($approuv_update_lot . " -- effacer un LOT -- " . mysql_error());
		
				
		echo '<meta http-equiv="refresh" content="5; url=conc_2_edit_a.php?id_conc=' . $id_conc . '">' ;

		echo '<div class="info"><br /> L\'�l�ment du lot est en cours d\'effacement.<br /> </div>' ; // Message confirmation
	
		/*echo '<pre>';
		print_r($array_lot_de_DB);
		echo '</pre>';*/
				
		exit() ;
	
	}
	else
	{
		echo ' erreur : array vide ' ;
	}
}


//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entr�e
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'update'))
{

	//-----------------------------------------------------------------------------------
	// Verification des donn�es entr�es par l'utilateur
	//-----------------------------------------------------------------------------------

	$rec = '';
	// = initialisation de la var qui sera test�e avant d'enregistrer les donn�es dans la DB
	// Si elle est vide => enregistrer. Sinon, elle contient le message d'erreur, et on l'affiche.
	
	
		
	// ------------------------------------------------------------
	// TEST concours "actif" ou pas (c�d, accessible au public)
	if (isset($_POST['concours_actif']) AND ($_POST['concours_actif'] == 'actif')) 
	{
		mysql_query("UPDATE $table_ag_conc_fiches SET flags_conc = 'actif' WHERE id_conc = '$id_conc' LIMIT 1 ") ;
	}


	
	// -----------------------------------------
	// TEST Intitul� du concours 
	if (isset($_POST['nom_event_conc']) AND ($_POST['nom_event_conc'] != NULL)) 
	{
		$nom_event_conc = htmlentities($_POST['nom_event_conc'], ENT_QUOTES);
	}
	else
	{
		$nom_event_conc = $indetermine;
		$error_nom_event_conc = '<div class="error_form">Vous devez indiquer un nom pour d�signer l\'�v�nement</div>';
		$rec .= '- Vous devez indiquer un nom pour d�signer l\'�v�nement<br>';
	}


	
	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['mail_lieu_conc']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['mail_lieu_conc']))))
	{
		$mail_lieu_conc = $_POST['mail_lieu_conc'];
	}
	else
	{
		$e_mail_lieu = $indetermine;
		$error_mail_lieu_conc = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
	}
	
	


	// -----------------------------------------
	// TEST date de cloture 
	if (isset($_POST['AAAA_cloture_conc']) AND ($_POST['AAAA_cloture_conc'] != NULL) AND preg_match('/[0-9]{4}$/', $_POST['AAAA_cloture_conc']) AND
	isset($_POST['MM_cloture_conc']) AND ($_POST['MM_cloture_conc'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['MM_cloture_conc']) AND
	isset($_POST['JJ_cloture_conc']) AND ($_POST['JJ_cloture_conc'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['JJ_cloture_conc']))
	{
		$AAAA_cloture_conc = htmlentities($_POST['AAAA_cloture_conc'], ENT_QUOTES);
		$MM_cloture_conc = htmlentities($_POST['MM_cloture_conc'], ENT_QUOTES);
		$JJ_cloture_conc = htmlentities($_POST['JJ_cloture_conc'], ENT_QUOTES);
		$HH_cloture_conc = htmlentities($_POST['HH_cloture_conc'], ENT_QUOTES);
		$cloture_conc = date(mktime($HH_cloture_conc, 0, 0, $MM_cloture_conc, $JJ_cloture_conc, $AAAA_cloture_conc));
		//$cloture_conc = $AAAA_cloture_conc . '-' . $MM_cloture_conc . '-' . $JJ_cloture_conc;
	}
	else
	{
		$error_cloture_conc = '<div class="error_form">La date de cloture est erron�e</div>';
		$rec .= '- La date de cloture est erron�e<br>';
	}


	// -----------------------------------------
	// TEST description du concours
	
	if (isset($_POST['ajaxfilemanager']) AND ($_POST['ajaxfilemanager'] != NULL)) 
	{
		$allowedTags = '<strong><br><br />'; // Balises de style que les USERS peuvent employer

		$description_conc = strip_tags($_POST['ajaxfilemanager'],$allowedTags);
		$description_conc = wordwrap($description_conc, 50, " ", 1);
		$description_conc = stripslashes($description_conc);
		$description_conc_2_db = addslashes($description_conc);
		
		$max=1500 ; 
		if (strlen($_POST['ajaxfilemanager'])>=$max)
		{	
			$char_en_trop = strlen($description_conc) - $max ; // Tester longueur de la cha�ne de caract�res
			$error_description_conc = '<div class="error_form">La taille du texte descriptif 
			de l\'�v�nement d�passe la limite autoris�e (' . $max . 'caract�res) . 
			Il y a ' . $char_en_trop . ' caract�res en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte descriptif de l\'�v�nement d�passe la limite autoris�e<br>';	
		}		
	}
	else
	{
		$description_conc = $indetermine;
		$error_description_conc = '<div class="error_form">Vous devez introduire un texte descriptif du concours</div>';
		$rec .= '- Vous devez introduire un texte descriptif du concours<br>';
	}
	
	
	
		
	// -----------------------------------------
	// TEST DE LA VIGNETTE
	$source_im = 'source_pic_1' ;
	if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
	{
		$num_pic = '1' ; // correspond � l'extension du nom du futur fichier JPEG upload�
		uploader_2 ($id_conc,$num_pic);	// Upload et construction vignette
				 
		// Afficher vignette
		echo '<img src="../vignettes_concours/vignette_conc' . $id_conc . '_' . $num_pic . 'jpg" />';
	}



	// -----------------------------------------
	// TEST LIEN INTERNE (vers un �v�nement de l'agenda)
	
	if (isset($_POST['event_dlp_conc']) AND preg_match('/[0-9]$/', $_POST['event_dlp_conc'])
	AND $_POST['event_dlp_conc'] != 0) 
	{
		$event_dlp_conc = htmlentities($_POST['event_dlp_conc'], ENT_QUOTES);
	}
	else
	{
		$event_dlp_conc = '';
	}
	
	
	
	
	// -----------------------------------------
	// TEST LIEN EXTERIEUR
	
	if (isset($_POST['lien_externe_conc']) AND $_POST['lien_externe_conc'] != NULL 
	AND $_POST['lien_externe_conc'] != '0') 
	{
		$lien_externe_conc = htmlentities($_POST['lien_externe_conc'], ENT_QUOTES);
	}
	else
	{
		$lien_externe_conc = '';
	}
	// -----------------------------------------
	// RECUP selection_conc
	$selection_conc = isset($_POST['selection_conc']) ? 1 : 0;


	// -----------------------------------------
	// Faut-il rajouter un "lot"
	// -----------------------------------------
	// Variable "lot"
	// variable array : chaque entr�e est un LOT d'un certain nombre de places pour un jour unique et pour un "groupe de joueur" unique
	
	if (isset($_POST['groupe_joueur']) AND ($_POST['groupe_joueur'] != NULL)	
	AND
	isset($_POST['nombre_places']) AND ($_POST['nombre_places'] != NULL)	AND preg_match('/[0-9]$/', $_POST['nombre_places'])
	AND isset($_POST['decription_lot']) AND ($_POST['decription_lot'] != NULL) 
	AND isset($_POST['txt_mail_lot']) AND ($_POST['txt_mail_lot'] != NULL) 

	)
	{
		// Si tous les champs sont bien compl�t�s, construire un array, qui sera pouss� dans l'array "lot"
			
		$groupe_joueur = htmlentities($_POST['groupe_joueur'], ENT_QUOTES);
		$nombre_places = htmlentities($_POST['nombre_places'], ENT_QUOTES);
		
		// Enlever les caract�res qui pourraient poser probl�me lors de la mise en Array
		$decription_lot = $_POST['decription_lot'] ;
		$txt_mail_lot = $_POST['txt_mail_lot'] ;
		$find = '\\';
		$replace = "-";
		$decription_lot = strtr($decription_lot,$find,$replace);
		$decription_lot = stripslashes(htmlentities($decription_lot, ENT_QUOTES));
		
		$txt_mail_lot = strtr($txt_mail_lot,$find,$replace);
		$txt_mail_lot = stripslashes(htmlentities($txt_mail_lot, ENT_QUOTES));
	
		echo '<br /> ==> ' . $decription_lot ;

	
		$new_array_lot = array (
		"decription_lot" => $decription_lot,
		"txt_mail_lot" => $txt_mail_lot,
		"groupe_joueur" => $groupe_joueur,
		"nombre_places" => $nombre_places ) ;
	
	
		/*echo '<pre>';
		print_r($new_array_lot);
		echo '</pre>';*/
	
	
		$test_lot_db = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
		$donnees_test_lot_db = mysql_fetch_array($test_lot_db) ;
		$lots_conc = $donnees_test_lot_db['lots_conc'] ;

		if (isset($lots_conc) AND ($lots_conc != NULL))
		{
			$array_lot_de_DB = unserialize($lots_conc) ; // r�cup�ration de la variable Lot de la DB
			//print_r($array_lot_de_DB);
			//array_push ($array_lot_de_DB, $array_lot_de_DB); // remettre le(s) lot(s) contenu dans la DB
			array_push ($array_lot_de_DB, $new_array_lot); // et y rajouter le nouveau lot (provenant du formulaire
			$new_array_lot_serialized = serialize ($array_lot_de_DB) ;
			 
		}
		else
		{
			//echo ' ----------- Premier lot ----------- ' ;
			$array_2_DB = array();
			array_push ($array_2_DB, $new_array_lot);
			
			//$new_array_lot_serialized = serialize ($new_array_lot) ; 
			$new_array_lot_serialized = serialize ($array_2_DB) ; 
		}

		$approuv_update_lot = mysql_query("UPDATE $table_ag_conc_fiches SET
		lots_conc = '$new_array_lot_serialized'
		WHERE id_conc = '$id_conc' LIMIT 1 ") or print($approuv_update_lot . " -- update du LOT -- " . mysql_error());
		
		if ($approuv_update_lot)
		{ 
			echo '<div class="info">Le nouveau lot a bien �t� enregistr�</div>';
		}
		else
		{
			$lot_conc = $indetermine;
			/*
			$error_lot_conc = '<div class="error_form">La partie de s�lection du lot est mal remplie</div>';
			$rec .= '- La partie de s�lection du lot est mal remplie<br>';
			*/
		}
	}


	// -----------------------------------------
	// TEST description du concours
	

	if (isset($_POST['adresse_conc']) AND ($_POST['adresse_conc'] != NULL)) 
	{
		$adresse_conc = htmlentities($_POST['adresse_conc'], ENT_QUOTES);
	}		
	else
	{
		$adresse_conc = $indetermine;
		$error_adresse_conc = '<div class="error_form">Vous devez indiquer l\'adresse du lieu o� se d�roule l\'�v�nement</div>';
		$rec .= '- Vous devez indiquer l\'adresse du lieu o� se d�roule l\'�v�nement<br>';
	}
	
	



	//-----------------------------------------------------------------------------------------------------------
	// Traitement du r�sultat des donn�es entr�es par l'utilateur
	//---------------------------------------------------------
	// Update des donn�es
	//---------------------------------------------------------
	if ($rec == NULL) // Enregistrement les donn�es dans la DB 
	{
		$approuv_check = mysql_query("UPDATE $table_ag_conc_fiches SET
		nom_event_conc = '$nom_event_conc' ,
		mail_lieu_conc = '$mail_lieu_conc' ,
		description_conc = '$description_conc_2_db' ,
		cloture_conc = '$cloture_conc',
		adresse_conc = '$adresse_conc',
		lien_externe_conc = '$lien_externe_conc',
		event_dlp_conc = '$event_dlp_conc',
		selection_conc = $selection_conc
		WHERE id_conc = '$id_conc' LIMIT 1 ") or print($approuv_check . " ----- " . mysql_error());
		
		if ($approuv_check)
		{
			echo '<br /><br /><div class="info">Les donn�es sont mises � jour.<br /></div>' ;
		}
		else 
		{ 
			echo '<div class="alerte">Erreur ! Les donn�es n\'ont pas �t� enregistr�es</div><br>' ;		
		}
	}
	else // Il y a au moins un champ du formulaire qui est mal rempli
	{
		echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
		// R�introduire variables pour le LOT dans le formulaire quan erreur
		$introduire_decription_lot = stripslashes($decription_lot);
	}
	// R�introduire variables dans le formulaire en enlevant les "\"
	$nom_event_conc = stripslashes($nom_event_conc);
	$adresse_conc = stripslashes($adresse_conc) ;
	
	$reponse_temp = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
	$donnees_temp = mysql_fetch_array($reponse_temp) ;
	$flags_conc = $donnees_temp['flags_conc'] ;
	// SI "concours ACTIF", emp�cher les modifs de LOTS  
	(preg_match('!actif!', $flags_conc)) ? ($flag_actif = true ) : ($flag_actif = false ) ;		


}

else // Si on n'a pas appuy� sur le bouton UPDATE -> r�cup�rer les donn�es de la DB
{

	// ------------------------------------------------
	// Lecture des infos de la DB pour cette entr�e
	// ------------------------------------------------
	
	$reponse_conc_fiches = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
	$donnees_conc_fiches = mysql_fetch_array($reponse_conc_fiches) ;
	
	$nom_event_conc = stripslashes ($donnees_conc_fiches['nom_event_conc']) ;
	$mail_lieu_conc = $donnees_conc_fiches['mail_lieu_conc'] ;
	$pic_conc = $donnees_conc_fiches['pic_conc'] ;
	$description_conc = $donnees_conc_fiches['description_conc'] ;
	$adresse_conc = stripslashes ($donnees_conc_fiches['adresse_conc']) ;
	
	$lots_conc = $donnees_conc_fiches['lots_conc'] ;		
	
	/*$date_conc = $donnees_conc_fiches['date_conc'] ;
	$AAAA_date_conc = substr($date_conc, 0, 4);
	$MM_date_conc = substr($date_conc, 5, 2);
	$JJ_date_conc = substr($date_conc, 8, 2);
	
	$heure_conc = $donnees_conc_fiches['heure_conc'] ;
	$HH_heure_conc = substr($heure_conc, 0, 2);
	$MM_heure_conc = substr($heure_conc, 3, 2);
	
	$nb_places_conc = $donnees_conc_fiches['nb_places_conc'] ;*/
	
	$cloture_conc = $donnees_conc_fiches['cloture_conc'] ;
	$AAAA_cloture_conc = date('Y',$cloture_conc);
	$MM_cloture_conc = date('m',$cloture_conc);
	$JJ_cloture_conc = date('d',$cloture_conc);
	$HH_cloture_conc = date('H',$cloture_conc);
	

	$flags_conc = $donnees_conc_fiches['flags_conc'] ;
	// SI "concours ACTIF", emp�cher les modifs de LOTS  
	(preg_match('!actif!', $flags_conc)) ? ($flag_actif = true ) : ($flag_actif = false ) ;		
		
	
	$event_dlp_conc = $donnees_conc_fiches['event_dlp_conc'] ;
	$lien_externe_conc = $donnees_conc_fiches['lien_externe_conc'] ;
	$selection_conc = $donnees_conc_fiches['selection_conc'] ;
}

?>
<form name="form2" method="get" action="conc_2_recopier_de_event.php" >
	<p align="center">Copier les donn�es li�es � l'�v�nement dont l'ID est :
	  <input name="event_correspondant" type="text">
	<input name="bouton_recopier" type="submit" value="Recopier">
	</p>
	<input name="concours_modifie" type="hidden" value="<?php echo $id_conc ; ?>">
</form>

<table width="800" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td valign="top"><form name="form1" method="post" action=""  enctype="multipart/form-data" >
        <table width="600" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
          <tr>
            <th colspan="2"><?php 
			
			echo 'Nom du concours : ' . $nom_event_conc . 
			' (id ' . $id_conc . ')<br />' ;
			echo 'e_mail du Lieu : ' . $mail_lieu_conc . '<br />' ;
			
			?></th>
          </tr>
          <tr>
            <td>Intitul&eacute; du concours
              <?php if (isset ($error_nom_event_conc) AND $error_nom_event_conc != NULL) {echo $error_nom_event_conc ; } ?></td>
            <td><input name="nom_event_conc" type="text" id="nom_event_conc" value="<?php if (isset($nom_event_conc)){echo $nom_event_conc;}?>" size="70" maxlength="180"></td>
          </tr>
          <tr>
            <td align="center" valign="middle"><?php 		
			
			// Afficher vignette
			$reponse_test_vignette = mysql_query("SELECT pic_conc FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
			$donnees_test_vignette = mysql_fetch_array($reponse_test_vignette) ;
			if (isset ($donnees_test_vignette ['pic_conc']) AND $donnees_test_vignette ['pic_conc'] == 'set' )
			{
				echo '<img src="../' . $folder_vignettes_concours . 'vi_conc_' . $id_conc . '_1.jpg" />';
			}
							
			?>            </td>
            <td><p> Image
                <input name="source_pic_1" type="file" id="source_pic_1" />
                <a herf="#" onclick="document.location.reload();return(false)"></a> </p></td>
          </tr>
          <tr>
            <td>e-mail du LIEU offrant les places
              <?php if (isset ($error_mail_lieu_conc) AND $error_mail_lieu_conc != NULL) {echo $error_mail_lieu_conc ; } ?>            </td>
            <td><input name="mail_lieu_conc" type="text" id="mail_lieu_conc" value="<?php if (isset($mail_lieu_conc)){echo $mail_lieu_conc;}?>" size="30" maxlength="45"></td>
          </tr>
          <tr>
            <td>Date de <strong>cloture</strong> du concours 
			<?php if (isset ($error_cloture_conc) AND $error_cloture_conc != NULL) {echo $error_cloture_conc ; } ?>			</td>
            <td>Jour - Mois - Ann&eacute;e
              <?php // LISTE d&eacute;roulante des JOURS
echo '<select name="JJ_cloture_conc">';
for ($list_j_comp=1 ; $list_j_comp<=31 ; $list_j_comp++)
{
	$list_j_comp = add_chaine_2_car ($list_j_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_j_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($JJ_cloture_conc == $list_j_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_j_comp.'</option>';
}
echo '</select>';
?>
              <?php // LISTE d&eacute;roulante des MOIS
echo '<select name="MM_cloture_conc">';
for ($list_m_comp=1 ; $list_m_comp<=12 ; $list_m_comp++)
{
	$list_m_comp = add_chaine_2_car ($list_m_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_m_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($MM_cloture_conc == $list_m_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_m_comp.'</option>';
}
echo '</select>';
?>
          <?php // LISTE d&eacute;roulante des ANNEES
echo '<select name="AAAA_cloture_conc">';
for ($list_a_comp=2008 ; $list_a_comp<=(date('Y')+2) ; $list_a_comp++)
{
	echo '<option value="' . $list_a_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($AAAA_cloture_conc == $list_a_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_a_comp.'</option>';
}
echo '</select>';
?> &agrave;      
    
<?php // LISTE d&eacute;roulante des HEURES
echo '<select name="HH_cloture_conc">';
for ($list_h_comp=0 ; $list_h_comp<=23 ; $list_h_comp++)
{
	echo '<option value="' . $list_h_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($HH_cloture_conc == $list_h_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_h_comp.'</option>';
}
echo '</select>';
?>
heures          </tr>
          <tr>
            <td colspan="2" bgcolor="#DDDDDD">Liste des &quot;lots&quot; mis en jeu : <br /> 
              <br />
			
			<?php 
			$reponse_conc_lot = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
			$donnees_conc_lot = mysql_fetch_array($reponse_conc_lot) ;
			if (isset($donnees_conc_lot['lots_conc']) AND ($donnees_conc_lot['lots_conc'] != NULL))
			{
				//$array_lot_de_DB = unserialize($donnees_conc_fiches['lots_conc']) ;
				$var_lot_unserialized = unserialize($donnees_conc_lot['lots_conc']) ;
				
				$i_lot = 0; // sera incr�ment� dans la boucle
				echo '<ul>';
				foreach ($var_lot_unserialized as $element_lot)
				{
					
					echo '<li><b>' . str_pad($element_lot['nombre_places'], 3, "0", STR_PAD_LEFT) . '</b> unit�s � remporter ';

					echo ' pour le groupe "<b>' . $groupes_joueurs[$element_lot['groupe_joueur']] . '</b>"' ; 
					
					echo '<br /> <em>' . $element_lot['decription_lot'] . '</em> <br /> ' ;
					
					
					// si "concours en ligne"  : Pouvoir EDITER mais PASEFFACER
					// si "concours pas encore en ligne"  : Pouvoir EFFACER ET EDITER
					if ($flag_actif)
					{
						echo ' <span style="text-decoration: line-through ;">Effacer</span>' ;
						echo ' | <a href="conc_2_edit_b.php?id_conc=' . $id_conc . '&lot=' . $i_lot . '">Modifier</a> ' ;
					}
					else
					{
						echo ' <a href="#voir" onclick="confirmation_effacer(' . $id_conc . ',' . $i_lot . '); ">Effacer</a>';

						echo ' | <a href="conc_2_edit_b?id_conc=' . $id_conc . '&lot=' . $i_lot . '">Modifier</a> ' ;
					}
					
					echo '</li>';
					/*print_r($element_lot);
					echo '<br /><br /><br />' ;*/
					$i_lot++ ;

				}
				echo '</ul>';
				
				/*echo '<pre>';
				print_r($var_lot_unserialized);
				echo '</pre>';*/
				
				//var_dump ($var_lot_unserialized) ;				
			}
			?>
</td>
          </tr>
          
		

				  
		  <tr>
            <td colspan="2" bgcolor="#DDDDDD"><p>Compl&eacute;ter tous les champs ci-dessous afin de cr&eacute;er un nouveau &quot;Lot&quot; pour ce concours
              <?php if (isset ($error_lot_conc) AND $error_lot_conc != NULL) {echo $error_lot_conc ; } ?>
              </p>
              <p>
                Groupe de joueurs :
                <?php 
					
			// Liste des GROUPES de joueurs pouvant participer aux concours
			echo '<select name="groupe_joueur">';
		foreach($groupes_joueurs as $cle_groupes_joueurs => $element_groupes_joueurs)
		{
			echo '<option value="' . $cle_groupes_joueurs .'"';		
			// Faut-il preselectionner
			if (isset($t888888) AND $t888888 == $cle_groupes_joueurs)
			{
				echo 'selected';
			}
			$max=20; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
			$element_groupes_joueurs = raccourcir_chaine ($element_groupes_joueurs,$max); // retourne $chaine_raccourcie
			echo '>'.$element_groupes_joueurs.'</option>';
		}
		echo '</select>';
				
			
			?>
              </p>
              <p>
                Nombre d'unit&eacute;s &agrave; gagner :
                  <input name="nombre_places" type="text" id="nombre_places" size="3" maxlength="3">
                <br>
              (Mettre &quot;0&quot; pour ne plus afficher le LOT sur le site et emp&ecirc;cher de jouer pour ce LOT) </p>
              <p>Texte descriptif du LOT pour afficher sur le <strong>SITE</strong>.<br>
                <span class="mini">Par exemple : 5 fois 2 places pour le 25/12/2007 &agrave; 21h30</span>
		        <?php if (isset ($error_decript_lot) AND $error_decript_lot != NULL) {echo $error_decript_lot ; } ?>
                    <br>
					
                  <input name="decription_lot" type="text" id="decription_lot" value="<?php if (isset ($introduire_decription_lot)) {echo $introduire_decription_lot ; } ?>" size="95" maxlength="180">
		    </p>
			
			     <p>Texte descriptif du LOT pour l'<strong>E-MAIL</strong> envoy&eacute; aux gagnants et aux partenaires offrant les places. <br>
			       <span class="mini">Par exemple : 2 places pour le 25/12/2007 &agrave; 21h30</span>
			      <?php if (isset ($error_txt_mail_lot) AND $error_txt_mail_lot != NULL) {echo $error_txt_mail_lot ; } ?>
                    <br>
					
                  <input name="txt_mail_lot" type="text" id="txt_mail_lot" value="<?php if (isset ($introduire_txt_mail_lot)) {echo $introduire_txt_mail_lot ; } ?>" size="95" maxlength="180">
		    </p>
			
			</td>
	      </tr>
          <?php // introduire une rang&eacute;e pour le message d'erreur
				if (isset ($error_date) AND $error_date != NULL)
				{
					echo '<tr><td colspan="2" align="center">' . $error_date . ' </td></tr>'; 
				}
				?>
				
				
					
			
				
          <tr>
            <td colspan="2">Description du concours
              <?php if (isset ($error_description_conc) AND $error_description_conc != NULL) {echo $error_description_conc ; } ?>
              <br>
              <textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 600px; height: 150px"><?php if (isset($description_conc)){echo $description_conc;} ?>
</textarea></td>
          </tr>
          <tr>
            <td>Adresse</td>
            <td><input name="adresse_conc" type="text" id="adresse_conc" value="<?php if (isset ($adresse_conc) AND $adresse_conc != NULL) {echo $adresse_conc;}?>" size="70" maxlength="255">
			<?php if (isset ($error_adresse_conc) AND $error_adresse_conc != NULL) {echo $error_adresse_conc ; } ?></td>
          </tr>
          <tr>
            <td>Ev&eacute;nement DLP li&eacute; </td>
            <td>indiquer un num&eacute;ro ID d'&eacute;v&eacute;nement : 
              <input name="event_dlp_conc" type="text" id="event_dlp_conc" value="<?php 
			if (isset ($event_dlp_conc) AND $event_dlp_conc != '0')
		{echo $event_dlp_conc;}?>" size="5" maxlength="5">
              (z&eacute;ro pour effacer)</td>
          </tr>
          <tr>
            <td colspan="2">Lien ext&eacute;rieur (avec le http://)
              <input name="lien_externe_conc" type="text" id="lien_externe_conc" value="<?php 
			if (isset ($lien_externe_conc) AND $lien_externe_conc != '0')
		{echo $lien_externe_conc;}?>" size="70" maxlength="100">
              (z&eacute;ro pour effacer)</td>
          </tr>
          <tr><td colspan="2">
              <label><input name="selection_conc" type="checkbox" value="1"<?php if (isset($selection_conc) && $selection_conc) echo ' checked="checked"' ; ?> />
              Concours s�lectionn� pour newsletter</label>
          </td></tr>
          <tr>
            <td colspan="2"><div align="center"> <br />

					<input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update">				  
				  
                <br />
              </div></td>
          </tr>
          <tr>
            <td colspan="2">
              <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FF0000">
                <tr>
                  <td>
			
					<?php 
					if (preg_match('!actif!', $flags_conc))
					{
						echo ' Concours ACTIF' ;
					}
					else
					{
						echo'<label><input type="checkbox" name="concours_actif" value="actif" ';
						if (isset($flags_conc) AND preg_match('!actif!', $flags_conc)) { echo 'checked="checked"' ; } 
						echo '/>Cocher pour rendre le concours &quot;Actif&quot;</label><br>';
					}
					?></td>
                </tr>
              </table>

              <p class="mini"><span class="rouge"><strong>ATTENTION</strong></span> : Il est n&eacute;cessaire de cocher cette case pour rendre le concours accessible au public. Il deviendra alors impossible d'effacer un LOT. </p>            </td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
        </table>
      </form></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
