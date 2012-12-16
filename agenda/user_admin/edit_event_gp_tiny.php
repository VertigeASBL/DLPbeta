<?php
//----- retourner les lieux de représentation pour autocomplete sur nom_pres
if (isset($_GET['term'])) {
	$term = addslashes(strtolower(utf8_decode($_GET['term'])));
	if (! $term)
		return;
	require '../inc_db_connect.php';

	$result = array();
	$reponse = mysql_query('SELECT id_pres,nom_pres FROM ag_representation WHERE ok_pres=1 AND nom_pres LIKE \'%'.$term.'%\' ORDER BY nom_pres');
	while ($donnees = mysql_fetch_array($reponse)) {
		array_push($result, array('id'=>$donnees['id_pres'], 'label'=>utf8_encode($donnees['nom_pres']), 'value'=>utf8_encode($donnees['nom_pres'])));
		if (count($result) > 15)
			break;
	}
	echo json_encode($result);
	exit();
}
//----- retourner le nom, l'adresse, code postal, localité, pays du lieu de représentation sélectionné par nom_pres
if (isset($_GET['id_pres'])) {
	$id_pres = (int) $_GET['id_pres'];
	if (! $id_pres)
		return;
	require '../inc_db_connect.php';

	$reponse = mysql_query('SELECT nom_pres,adresse_pres,localite_pres,postal_pres,pays_pres FROM ag_representation WHERE id_pres='.$id_pres);
	if ($donnees = mysql_fetch_array($reponse))
		echo json_encode(array('nom_pres'=>utf8_encode($donnees['nom_pres']), 'adresse_pres'=>utf8_encode($donnees['adresse_pres']), 'localite_pres'=>utf8_encode($donnees['localite_pres']), 'postal_pres'=>utf8_encode($donnees['postal_pres']), 'pays_pres'=>$donnees['pays_pres']));
	exit();
}

session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition de la fiche d'un &eacute;v&eacute;nement culturel (GP)</title>

<script src="../js/new/jquery-1.8.0.min.js"></script>
<script src="../js/new/jquery-ui-1.8.23.custom.min.js"></script>
<link rel="stylesheet" href="../js/new/jquery-ui-1.8.23.custom.css" />

<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="../vertiny/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
$(function() {
	//----- obtenir des lieux de représentation à sélectionner et ensuite les données du lieu sélectionné
	$("#nom_pres").autocomplete({
		source: "edit_event_gp_tiny.php"
		, minLength: 2
		, search: function(event, ui) {
			$("#id_pres_event").val(0);
			$("#lieu_pres").val(0);
			$("#adresse_pres").val("");
			$("#localite_pres").val("");
			$("#postal_pres").val("");
			$("#pays_pres").val("");
		}
		, select: function(event, ui) {
			if (ui.item)
				$("#id_pres_event").val(ui.item.id);
			else
				$("#id_pres_event").val(0);
		}
		, close: function(event, ui) { //--- si on ferme sans sélectionner, essayer de trouver le lieu
			$.getJSON("edit_event_gp_tiny.php?term="+encodeURI($("#nom_pres").val()), function(json){
				if (json.length == 1) {
					$("#id_pres_event").val(json[0].id);
					$.getJSON("edit_event_gp_tiny.php?id_pres="+json[0].id, function(json){
						if (json) {
							$("#nom_pres").val(json.nom_pres);
							$("#adresse_pres").val(json.adresse_pres);
							$("#localite_pres").val(json.localite_pres);
							$("#postal_pres").val(json.postal_pres);
							$("#pays_pres").val(json.pays_pres);
						}
					});
				}
				else {
					$("#id_pres_event").val(0);
					$("#adresse_pres").val("");
					$("#localite_pres").val("");
					$("#postal_pres").val("");
					$("#pays_pres").val("");
				}
			});
		}
	});
	//----- remplir le lieu de représentation au chargement
	var id_pres = $("#id_pres_event").val();
	if (id_pres != 0)
		$.getJSON("edit_event_gp_tiny.php?id_pres="+id_pres, function(json){
			$("#nom_pres").val(json.nom_pres);
			$("#adresse_pres").val(json.adresse_pres);
			$("#localite_pres").val(json.localite_pres);
			$("#postal_pres").val(json.postal_pres);
			$("#pays_pres").val(json.pays_pres);
		});
});
	tinyMCE.init({
		mode : "exact",
		elements : "ajaxfilemanager",
		theme : "advanced",
		plugins : "advhr,advimage,advlink,paste,noneditable,contextmenu",
		theme_advanced_toolbar_location : "top",
		theme_advanced_buttons1 : "undo,redo,separator,bold,separator,cleanup,",
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

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
<link href="../css_calendrier.css" rel="stylesheet" type="text/css" />
</head>
<body>


<?php

require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?>

<div id="head_admin_agenda"></div>
<!-- h1 plus bas -->

<?php 
// Affichage Nom, Groupe et Log Off du user
voir_infos_user () ;
?>


<p>
  <?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';
require '../fct_upload_pic_event_4.php';
require '../fct_upload_video.php';
require '../logs/fct_logs.php';

$indetermine = '' ; // Texte par défaut (-- INDETERMINE --)
$periode_max = (mktime(0, 0, 0, 6, 1, 1970)); // Intervalle (en mois) maximum entre début et fin d'un événement


//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'édition des fiches de spectacles
// edit_event.php?new=creer pour créer une nouvelle entrée
// edit_event.php?id=... pour éditer l'entrée
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage du calendrier avec cases colorées en fonction des jours actifs
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function affich_jours_actifs ($jours_actifs, $MM_traite, $AAAA_traite)
{
	global $date_event_debut;
	global $date_event_fin;	
	$date_event_debut_condition = str_replace("-","",$date_event_debut); 
	$date_event_fin_condition = str_replace("-","",$date_event_fin); 
	
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		// Composer la chaine qui sera cherchée dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$date_traite = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
		settype($JJ_traite, "integer"); // Pour éviter problèmes avec les nombres précédés de "0"

		$date_traite_condition = str_replace("-","",$date_traite); 

		// jour HORS période
		if (($date_traite < $date_event_debut)OR($date_traite > $date_event_fin))
		{
			//echo $date_traite_condition .' - ' .$date_event_debut_condition .'<br>';
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day nonchecked',$JJ_traite);
		}
		
		// jour ACTIF
		elseif (in_array($date_traite, $jours_actifs))
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day checked',$JJ_traite);
		}
		else
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day unchecked',$JJ_traite);
		}
	}
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1); // Affichage du calendrier
	echo '<br />' ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


//--------------------------------------------------------------------
// Créer une nouvelle entrée (si GET ...php?new=creer&lieu=...)
//--------------------------------------------------------------------
if (isset ($_GET['new']) AND $_GET['new'] == 'creer') // La variable GET qui donne l'ID à confirmer. Si NULL -> nouvelle entrée
{
	// Créer une entrée vide dans TABLE "table_evenements_agenda"
	
	$lieu_admin_spec_session = $_SESSION['lieu_admin_spec'] ;
	mysql_query("INSERT INTO `$table_evenements_agenda` (`lieu_event`) VALUES ($lieu_admin_spec_session)");

	$nouvel_id_table_evenements_agenda = mysql_insert_id() ; // sera utile pour créer un lien d'accès pour éditer les données
	echo '<br><br><br><div class="info"><p>Une nouvelle fiche descriptive d\'événement a été créé et peut être éditée <br>
<br>

	<a href="edit_event_gp_tiny.php?id='.$nouvel_id_table_evenements_agenda.'">Vous devez cliquer ici pour continuer</a></p><br>
<br>
</div><br>' ;
	
	
	// Notifier la création dans le rapport + e-mail
	log_write ($lieu_admin_spec_session, '2', $nouvel_id_table_evenements_agenda, 'Création nouvel événement', 'send_mail') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)

	
	//--- mysql_close($db2dlp);
	exit();
}


//-----------------------------------------------------------------------------------
// Verifier que l'événement est bien rattaché au lieu culturel auquel l'utilateur loggé appartient
//-----------------------------------------------------------------------------------
$lieu_event = 0;
if (isset($_GET['id']) AND ($_GET['id'] != NULL))
{
	$id = (int) $_GET['id']; // Correspond à l'ID du LIEU (provient du listing ou de nouvelle entrée vide.
	$reponse = mysql_query("SELECT lieu_event FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees = mysql_fetch_array($reponse);
	if (! $donnees || $donnees['lieu_event'] != $_SESSION['lieu_admin_spec'])
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Vous ne pouvez pas modifier un événement rattaché à un autre lieu culturel</div><br>' ;
		exit () ;
	}
	else
		$lieu_event = $donnees['lieu_event'];
}
else
{
	echo '<p>&nbsp;</p><p>&nbsp;</p>
	<div class="alerte"> <br /> Vous ne pouvez pas accéder à cette page de cette façon.<br /> <br />
	<a href="index.php">Votre menu </a> <br /> <br /> 
	</div>' ;
	exit () ;
}

//--------------------------------------------------------------------------------------------------------------
// Si bouton UPDATE enfoncé, alors lancer l'analyse des données
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer'))
{
	
	//--------------------------------------------------------------------------------------------------------------
	// Faut-il EFFACER UNE IMAGE
	//--------------------------------------------------------------------------------------------------------------
	for ($eeuu = 2; $eeuu <= 10; $eeuu++)
	{
		if (isset($_POST['effacer_image' . $eeuu]) AND ($_POST['effacer_image' . $eeuu] != NULL)) 
		{
			$pic_a_effacer = '../' . $folder_pics_event . 'event_' . $id . '_' . $eeuu . '.jpg' ;
			$vignette_a_effacer = '../' . $folder_pics_event . 'vi_event_' . $id . '_' . $eeuu . '.jpg' ; 
		
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

			/* ---------- richir : supprimer vignette micro pour iphone ---------- */
			$vignette_a_effacer = '../' . $folder_pics_event . 'micro_event_' . $id . '_' . $eeuu . '.jpg' ;
			@unlink($vignette_a_effacer);

			// Enlever le SET de le FLAG de la TABLE
			$image_db = 'pic_event_' .  $eeuu ;
			mysql_query("UPDATE $table_evenements_agenda SET $image_db = '' WHERE id_event = '$id' LIMIT 1 ") ;	
		}
	}

	
	//--------------------------------------------------------------------------------------------------------------
	// Faut-il EFFACER UNE VIDEO ?
	//--------------------------------------------------------------------------------------------------------------

	if (isset($_POST['effacer_video']) AND ($_POST['effacer_video'] != NULL)) 
	{
		$video_a_effacer = '../videos/' . $donnees['video_event'] ;

		if (unlink ($video_a_effacer))
		{ echo '<div class="info">La vidéo "' . $donnees['video_event'] . '" a bien été effacée</div>'; }
		else 
		{ echo '<div class="alerte">ERREUR :<br /> La vidéo "' . $donnees['video_event'] . '" n\'a pas été effacée</div>'; }
	

		// Enlever le FLAG de la TABLE
		mysql_query("UPDATE $table_evenements_agenda SET video_event = '' WHERE id_event = '$id' LIMIT 1 ") ;	
	}
	
	
	
	
	$rec = '';
	// -----------------------------------------
	// TEST DU NOM DE L'EVENEMENT 
	if (isset($_POST['nom_event']) AND ($_POST['nom_event'] != NULL)) 
	{
		$nom_event = strip_tags($_POST['nom_event']);
		$nom_event = stripslashes($nom_event);
		$nom_event = mysql_real_escape_string($nom_event);
		$nom_event = str_replace("’", "'", $nom_event);
	
	}
	else
	{
		$nom_event = $indetermine;
		$error_nom_event = '<div class="error_form">Vous devez indiquer un nom pour désigner l\'événement</div>';
		$rec .= '- Vous devez indiquer un nom pour désigner l\'événement<br>';
	}

	// -----------------------------------------
	// CHOIX DU LIEU DE REPRESENTATION 
	if (isset($_POST['id_pres_event']) AND $_POST['id_pres_event'])
		$id_pres_event = (int) $_POST['id_pres_event'];
	else {
		if (isset($_POST['nom_pres']) && $_POST['nom_pres'])
			$nom_pres = mysql_real_escape_string(str_replace('’', '\'', $_POST['nom_pres']));
		else {
			$nom_pres = $indetermine;
			$error_nom_pres = '<div class="error_form">Vous devez indiquer le nom du lieu de représentation</div>';
			$rec .= '- Vous devez indiquer le nom du lieu de représentation<br />';
		}
		if (isset($_POST['adresse_pres']) && $_POST['adresse_pres'])
			$adresse_pres = mysql_real_escape_string(str_replace('’', '\'', $_POST['adresse_pres']));
		else {
			$adresse_pres = $indetermine;
			$error_adresse_pres = '<div class="error_form">Vous devez indiquer l\'adresse du lieu de représentation</div>';
			$rec .= '- Vous devez indiquer l\'adresse du lieu de représentation<br />';
		}
		if (isset($_POST['localite_pres']) && $_POST['localite_pres'])
			$localite_pres = mysql_real_escape_string(str_replace('’', '\'', $_POST['localite_pres']));
		else {
			$localite_pres = $indetermine;
			$error_localite_pres = '<div class="error_form">Vous devez indiquer la localité du lieu de représentation</div>';
			$rec .= '- Vous devez indiquer la localité du lieu de représentation<br />';
		}
		if (isset($_POST['postal_pres']) && $_POST['postal_pres'])
			$postal_pres = mysql_real_escape_string(str_replace('’', '\'', $_POST['postal_pres']));
		else {
			$postal_pres = $indetermine;
			$error_postal_pres = '<div class="error_form">Vous devez indiquer le code postal du lieu de représentation</div>';
			$rec .= '- Vous devez indiquer le code postal du lieu de représentation<br />';
		}
		$pays_pres = isset($_POST['pays_pres']) && $_POST['pays_pres'] ? (int) $_POST['pays_pres'] : 1;

		$id_pres_event = 0;
		if (! $rec && mysql_query("INSERT INTO ag_representation SET lieu_pres=$lieu_event,nom_pres='$nom_pres',adresse_pres='$adresse_pres',localite_pres='$localite_pres',postal_pres='$postal_pres',pays_pres=$pays_pres,ok_pres=0")) {
			$id_pres_event = mysql_insert_id();

			$reponse_2 = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = $lieu_event");
			$donnees_2 = mysql_fetch_array($reponse_2) ;
			$nom_lieu = $donnees_2 ? $donnees_2['nom_lieu'] : '';

			//--- nouveau lieu de représentation : avertir l'administrateur
			$entete = "Content-type:text/plain; charset=iso-8859-1;\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
			$corps = 'Bonjour.'."\n\n".'Il existe un nouveau lieu de représentation :'."\n\n";
			$corps .= $nom_lieu.' (ID '.$lieu_event.')'."\n\n".$nom_pres.' (ID '.$id_pres_event.')'."\n\n";
			$corps .= $adresse_pres."\n".$localite_pres."\n".$postal_pres."\n".$payspresent[$pays_pres]."\n\n";
			$corps .= 'Voir http://www.demandezleprogramme.be/agenda/admin_agenda/edit_event.php?id='.$id."\n";
			mail_beta($email_admin_site, 'Nouveau lieu de representation ID '.$id_pres_event, $corps, $entete, $email_retour_erreur);
		}
	}

	// -----------------------------------------
	// PARENT DE L'EVENEMENT 
	$parent_event = isset($_POST['parent_event']) ? (int) $_POST['parent_event'] : 0;
	
	// -----------------------------------------
	// TEST DATES DEBUT ET FIN EVENEMENT 

	if (isset($_POST['select_AAAA_debut']) AND ($_POST['select_AAAA_debut'] != NULL) AND preg_match('/[0-9]{4}$/', $_POST['select_AAAA_debut']) AND
	isset($_POST['select_AAAA_fin']) AND ($_POST['select_AAAA_fin'] != NULL) AND preg_match('/[0-9]{4}$/', $_POST['select_AAAA_fin']) AND 
	isset($_POST['select_MM_debut']) AND ($_POST['select_MM_debut'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['select_MM_debut']) AND
	isset($_POST['select_MM_fin']) AND ($_POST['select_MM_fin'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['select_MM_fin']) AND
	isset($_POST['select_JJ_debut']) AND ($_POST['select_JJ_debut'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['select_JJ_debut']) AND
	isset($_POST['select_JJ_fin']) AND ($_POST['select_JJ_fin'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['select_JJ_fin'])) 
	{
		$AAAA_debut = htmlentities($_POST['select_AAAA_debut'], ENT_QUOTES);
		$AAAA_fin = htmlentities($_POST['select_AAAA_fin'], ENT_QUOTES);
		$MM_debut = htmlentities($_POST['select_MM_debut'], ENT_QUOTES);
		$MM_fin = htmlentities($_POST['select_MM_fin'], ENT_QUOTES);
		$JJ_debut = htmlentities($_POST['select_JJ_debut'], ENT_QUOTES);
		$JJ_fin = htmlentities($_POST['select_JJ_fin'], ENT_QUOTES);

		// La date de début est-elle inférieure à la date de fin ?
		$date_event_debut = $AAAA_debut.$MM_debut.$JJ_debut ;
		$date_event_fin = $AAAA_fin.$MM_fin.$JJ_fin ;

		$time_event_debut = date(mktime(0, 0, 0, $MM_debut, $JJ_debut, $AAAA_debut));
		$time_event_fin = date(mktime(0, 0, 0, $MM_fin, $JJ_fin, $AAAA_fin));

		//echo $time_event_debut .'<br>'.$time_event_fin .'<br>'.$periode_max .'<br>'; 

		if (($time_event_debut <= $time_event_fin) AND ($time_event_fin - ($time_event_debut + $periode_max)<=0))
		{
			$date_event_debut = $AAAA_debut.'-'.$MM_debut.'-'.$JJ_debut ;
			$date_event_fin = $AAAA_fin.'-'.$MM_fin.'-'.$JJ_fin ;
		}
		else
		{
			$error_date = '<div class="error_form">La date de début de période ne peut pas être ultérieure à la date de fin de période et la période ne peut pas dépasser 5 mois</div>';
			$rec .= '- La date de début de période ne peut pas être ultérieure à la date de fin de période et la période ne peut pas dépasser 3 mois<br>';
		}
	}
	else
	{
		$error_date = '<div class="error_form">Les dates indiquées pour la période de l\'événement sont erronées</div>';
		$rec .= '- Les dates indiquées pour la période de l\'événement sont erronées<br>';
	}
		
	// -----------------------------------------
	// CHOIX DE LA REGION OU A LIEU L'EVENEMENT 
	
	// Liste déroulante des régions
	if (isset($_POST['ville_event']) AND ($_POST['ville_event'] != 'NULL')) 
	{
		$ville_event = htmlentities($_POST['ville_event'], ENT_QUOTES);
	}
	else
	{
		$ville_event = $indetermine;
		$error_ville_event = '<div class="error_form">Vous devez indiquer la région dans laquelle a lieu l\'événement</div>';
		$rec .= '- Vous devez indiquer la région dans laquelle a lieu l\'événement<br>';
	}

	// -----------------------------------------
	// TEST DESCRIPTION EVENEMENT 
	
	if (isset($_POST['ajaxfilemanager']) AND ($_POST['ajaxfilemanager'] != NULL)) 
	{
		$allowedTags = '<strong><br><br />'; // Balises de style que les USERS peuvent employer

		//$description_event = nl2br($_POST['ajaxfilemanager']);
		$description_event = str_replace("&nbsp;", " ", $_POST['ajaxfilemanager']);
		$description_event = preg_replace('/\s+/', ' ', $description_event); // supprimer espaces multiples
		$description_event = html_entity_decode($description_event);
		$description_event = strip_tags($description_event, $allowedTags) . '' ;
		$description_event = str_replace("\r\n", " ", $description_event);
		$description_event = str_replace("’", "'", $description_event);
		$description_event = str_replace("&oelig;", "œ", $description_event);

		$description_event = wordwrap($description_event, 80, " ", 1);
		$description_event = stripslashes($description_event);
		$description_event_2_db = mysql_real_escape_string($description_event);
		//$description_event_2_db = addslashes($description_event_2_db);
				
		$max=3000 ; 
		if (strlen($_POST['ajaxfilemanager'])>=$max)
		{	
			$char_en_trop = strlen($description_event) - $max ; // Tester longueur de la chaîne de caractères
			$error_description_event = '<div class="error_form">La taille du texte descriptif 
			de l\'événement dépasse la limite autorisée (' . $max . 'caractères) . 
			Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte descriptif de l\'événement dépasse la limite autorisée<br>';			
		}		
	}
	else
	{
		$description_event = $indetermine;
		$error_description_event = '<div class="error_form">Vous devez introduire un texte descriptif de l\'événement</div>';
		$rec .= '- Vous devez introduire un texte descriptif de l\'événement<br>';
	}
	




//var_dump ($_POST['resume_event_chp']) ;
	
	// -----------------------------------------
	// TEST RESUME EVENEMENT
	if (isset($_POST['resume_event_chp']) AND ($_POST['resume_event_chp'] != NULL)) 
	{
		$resume_event = str_replace("&nbsp;", " ", $_POST['resume_event_chp']);
		$resume_event = preg_replace('/\s+/', ' ', $resume_event); // supprimer espaces multiples		
		$resume_event = nl2br($resume_event) ;
		$resume_event = strip_tags($resume_event);

		$resume_event = str_replace("’", "'", $resume_event);

		$resume_event = wordwrap($resume_event, 80, " ", 1);
		$resume_event = stripslashes($resume_event);
		//$resume_event_2_db = addslashes($resume_event);
		$resume_event_2_db = mysql_real_escape_string($resume_event);
		
		
		$max=350 ; 
		if (strlen($_POST['resume_event_chp'])>=$max)
		{	
			$char_en_trop = strlen($resume_event) - $max ; // Tester longueur de la chaîne de caractères
			$error_resume_event = '<div class="error_form">La taille du texte "résumé 
			de l\'événement" dépasse la limite autorisée (' . $max . 'caractères) . 
			Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir.</div>';
			$rec .= '- La taille du texte "résumé" de l\'événement dépasse la limite autorisée<br>';			
			
			mysql_query("UPDATE `$table_evenements_agenda` SET `resume_event` = '$resume_event_2_db' 
			WHERE `id_event` = '$id' LIMIT 1 ");
		}
		else
		{
			mysql_query("UPDATE `$table_evenements_agenda` SET `resume_event` = '$resume_event_2_db' 
			WHERE `id_event` = '$id' LIMIT 1 ");
		}		
	}
	else
	{
		$resume_event = $indetermine;
		$error_resume_event = '<div class="error_form">Vous devez introduire un texte descriptif de l\'événement</div>';
		$rec .= '- Vous devez introduire un texte descriptif de l\'événement<br>';
	}
	
	
	// -----------------------------------------
	// TEST GENRE EVENEMENT 
	if (isset($_POST['genre_event']) AND ($_POST['genre_event'] != NULL)) 
	{
		$genre_event = htmlentities($_POST['genre_event'], ENT_QUOTES);
	}
	else
	{
		$genre_event = $indetermine;
		$error_genre_event = '<div class="error_form">Vous devez décrire le GENRE de l\'événement</div>';
		$rec .= '- Vous devez décrire le GENRE de l\'événement<br>';
	}
	
	// -----------------------------------------
	// TETEST TEL RESERVATION EVENEMENT
	if (isset($_POST['tel_reserv_event']) AND ($_POST['tel_reserv_event'] != NULL)) 
	{
		$tel_reserv_event = htmlentities($_POST['tel_reserv_event'], ENT_QUOTES);
	}
	else
		$tel_reserv_event = $indetermine;


	/*
		Didier: On ajoute l'email de réservation pour un evenement.
	*/
	if (!empty($_POST['email_reservation'])) {
		$email_reservation = htmlentities($_POST['email_reservation'], ENT_QUOTES);
	}
	else $email_reservation = $indetermine;

	// -----------------------------------------
	// TEST PRIX MINIMUM
	if (isset($_POST['prix_min_event'])) 
	{
		$prix_min_event = (float) str_replace(',', '.', $_POST['prix_min_event']);
		$prix_min_event = $prix_min_event >= 0.01 ? number_format($prix_min_event, 2, ',', '') : '';
	}
	// -----------------------------------------
	// TEST PRIX MAXIMUM
	if (isset($_POST['prix_max_event']))
	{
		$prix_max_event = (float) str_replace(',', '.', $_POST['prix_max_event']);
		$prix_max_event = $prix_max_event >= 0.01 ? number_format($prix_max_event, 2, ',', '') : '';
	}

	// -----------------------------------------
	// TEST LIEN AVEC SAISON PRECEDENTE
	$saison_preced_event = isset($_POST['saison_preced_event']) ? $_POST['saison_preced_event'] : '';

	if (isset($_POST['saison_preced_oui']) && ! $saison_preced_event) {
		$reponse_2 = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = $lieu_event");
		$donnees_2 = mysql_fetch_array($reponse_2) ;
		$nom_lieu = $donnees_2 ? $donnees_2['nom_lieu'] : '';

		//--- avertir l'administrateur - echo '<hr />',nl2br($corps),'<hr />';
		$entete = "Content-type:text/plain; charset=iso-8859-1;\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
		$corps = 'Bonjour.'."\n\n".'Un producteur a indiqué que son événement est une reprise.'."\n\n";
		$corps .= $nom_event.' (ID '.$id.')'."\n".$nom_lieu.' (ID '.$lieu_event.')'."\n\n";
		$corps .= 'Voir http://www.demandezleprogramme.be/agenda/admin_agenda/edit_event.php?id='.$id."\n";
		mail_beta($email_admin_site, 'Un evenement est une reprise ID '.$id, $corps, $entete, $email_retour_erreur);
	}

	// -----------------------------------------
	// TEST IMAGES et VIGNETTE
	$id_update = $_GET['id'] ;
	// Checker les 3 champs d'upload
	for ($uii = 1; $uii <= 10; $uii++)
	{
		$source_im = 'source_pic_' . $uii  ;
		if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
		{
			$num_pic = $uii ; // correspond à l'extension du nom du futur fichier JPEG uploadé
			uploader_4 ($id_update,$uii);	// Upload et construction vignette
		}
	}
	
	
	// -----------------------------------------
	// TEST DE LA VIGNETTE
	$source_im = 'source_pic_1' ;
	if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
	{
		$id_update = $_GET['id'] ;
		$num_pic = '1' ; // correspond à l'extension du nom du futur fichier JPEG uploadé

		uploader_2 ($id_update,$num_pic);	// Upload et construction vignette
	}
	
	
	
	// -----------------------------------------
	// TEST VIDEO

	$source_video = 'source_video' ;
	if(isset($_FILES[$source_video]['tmp_name']) AND !empty($_FILES[$source_video]['tmp_name']) AND is_uploaded_file($_FILES[$source_video]['tmp_name']))
	{
			echo 'DDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD' ;

		$debug_concat = uploader_video ($id_update);	// Fonction Upload vidéo
		echo '<div class="error_form">' . $debug_concat . '</div>';	
		
		
		if ($_FILES['source_video']['error']) 
		{
			$err_case = '' ;
			switch ($_FILES['source_video']['error'])
			{
				case 1: // UPLOAD_ERR_INI_SIZE
				$err_case.= "<br />Le fichier dépasse la limite autorisée par le serveur."; // fichier php.ini
				break;
				case 2: // UPLOAD_ERR_FORM_SIZE
				$err_case.= "<br />Le fichier dépasse la limite autorisée dans le formulaire HTML";
				break;
				case 3: // UPLOAD_ERR_PARTIAL
				$err_case.= "<br />L'envoi du fichier a été interrompu pendant le transfert; Le fichier n'a été que partiellement téléchargé.";
				break;
				case 4: // UPLOAD_ERR_NO_FILE
				$err_case.= "<br />Le fichier que vous avez envoyé a une taille nulle; Aucun fichier n'a été téléchargé.";
				break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
				$err_case.= "<br />Un dossier temporaire est manquant."; // Introduit en PHP 4.3.10 et PHP 5.0.3
				break;
				case 7: // UPLOAD_ERR_CANT_WRITE
				$err_case.= "<br />Échec de l'écriture du fichier sur le disque."; // Introduit en PHP 5.1.0.
				break;
				case 8: // UPLOAD_ERR_EXTENSION
				$err_case.= "<br />L'envoi de fichier est arrêté par l'extension."; // Introduit en PHP 5.2.0..
				break;
			}
		}
			echo 'err = '.$err_case ;	
	}
	
	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	// Update des données
	//---------------------------------------------------------
	if ($rec == NULL) // Enregistrement les données dans la DB 
	{ 
		$approuv_check = mysql_query("UPDATE $table_evenements_agenda SET
		parent_event = $parent_event ,
		nom_event = '$nom_event' ,
		date_event_debut = '$date_event_debut' ,
		date_event_fin = '$date_event_fin' ,
		ville_event = '$ville_event' ,
		pres_event = $id_pres_event ,
		resume_event = '$resume_event_2_db' ,
		description_event = '$description_event_2_db' ,
		genre_event = '$genre_event' ,
		tel_reserv_event = '$tel_reserv_event' ,
		prix_min_event = '$prix_min_event' ,
		prix_max_event = '$prix_max_event',
		email_reservation = '$email_reservation'

		WHERE id_event = '$id' LIMIT 1 ") ;

		if ($approuv_check)
		{
			// Enregistrer cette modifivation dans le rapport
			log_write ($donnees['lieu_event'], '2', $id, 'Modification événement', 'send_mail') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)

			// COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - 
			// Vérifier si l'événement contient un "Prénom Nom" de comedien, et effectuer une mise à jour de "ag_comedien_lien"
			include ('../lien_comedien/inc_update_table_lien.php') ;
			// ps : il est nécessaire de concaténer toutes les chaines susceptibles de contenir les noms avant d'appeler la fonction
			$chaine_a_tester_comedien = $description_event_2_db . ' ' . $resume_event_2_db ;
			update_table_ag_comedien_lien_pour_un_event ($id, $chaine_a_tester_comedien) ;
			require '../inc_db_connect.php';
			// COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - 
			
			
			echo '<div class="menu_back"><a href="listing_events_gp.php?lieu=',$donnees['lieu_event'],'" >Vos événements</a></div>',"\n";
			echo '<br /><br /><div class="info">Les données sont mises à jour.<br />
			Veuillez maintenant cocher les jours pendant lesquels l\'événement aura lieu.<br /><br />
			<a href="edit_jours_gp.php?id=' . $id . '">Cliquez ici pour continuer</a></div>' ;
			
			//echo '<meta http-equiv="refresh" content="4; url=edit_jours_gp.php?id=' . $id . '">' ; 
			
			exit() ;
		}
		else 
		{ 
			echo '<div class="alerte">Erreur ! Les données n\'ont pas été enregistrées</div><br>' ; 

			$error_2_rapport = 'Erreur lors de modification événement. Requête = ' . urlencode(mysql_error()) ;
			log_write ($id, '2', $id, $error_2_rapport, 'send_mail') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)

		
		}

		// Réintroduire variables dans le formulaire en enlevant les "\" (n'est plus nécessaire si on redirige après Update)
	}
	else // Il y a au moins un champ du formulaire qui est mal rempli
	{
		echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
	}
	$nom_event = stripslashes($nom_event);		
}

else // Si on n'a pas appuyé sur le bouton UPDATE -> récupérer les données de la DB
{

	// ------------------------------------------------
	// Lecture des infos de la DB pour cette entrée
	// ------------------------------------------------
	
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees = mysql_fetch_array($reponse);	

	$parent_event = $donnees['parent_event'];
	$lieu_event = $donnees['lieu_event'];
	$nom_event = stripslashes($donnees['nom_event']);
	$ville_event = $donnees['ville_event'];
	$id_pres_event = $donnees['pres_event'];
	$resume_event = $donnees['resume_event'];
	$description_event = $donnees['description_event'];
	$genre_event = $donnees['genre_event'];
	$tel_reserv_event = $donnees['tel_reserv_event'];
	$email_reservation = $donnees['email_reservation'];
	$prix_min_event = $donnees['prix_min_event'];
	$prix_max_event = $donnees['prix_max_event'];
	$pic_event_1 = $donnees['pic_event_1'];
	
	$date_event_debut = $donnees['date_event_debut'];
	$date_event_fin = $donnees['date_event_fin'];

	if ($date_event_debut == '0000-00-00') {
		$date_event_debut = date('Y-m-d');
		if ($date_event_fin == '0000-00-00')
			$date_event_fin = $date_event_debut;
	}
	$AAAA_debut = substr($date_event_debut, 0, 4);
	$AAAA_fin = substr($date_event_fin, 0, 4);

	$MM_debut = substr($date_event_debut, 5, 2);	
	$MM_fin = substr($date_event_fin, 5, 2);
	$JJ_debut = substr($date_event_debut, 8, 2);
	$JJ_fin = substr($date_event_fin, 8, 2);
	$AAAA_MM_debut = substr($date_event_debut, 0, 7);

	$jours_actifs_event = $donnees['jours_actifs_event'];
	
//	$article_kidonaki = $donnees['article_kidonaki'];
	$saison_preced_event = $donnees ['saison_preced_event'];
}
/*
$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
$donnees = mysql_fetch_array($reponse);	
$jours_actifs_event = $donnees['jours_actifs_event'];
$jours_actifs_event = explode(",", $jours_actifs_event);
*/
if (! isset($jours_actifs_event))
	$jours_actifs_event = isset($_POST['jours_actifs_event']) ? $_POST['jours_actifs_event'] : '';
$jours_actifs_event = explode(',', $jours_actifs_event);

// -----------------------------------------------
// Affichage TITRE, lien retour...

echo '<h1>Edition d\'une fiche spectacle</h1>

<div class="menu_back">
<a href="listing_events_gp.php?lieu=' . $donnees['lieu_event'] . '" >Vos événements</a> | 
<a href="../../-Detail-agenda-?id_event=' . $id . '">Page en ligne</a> | 
<a href="../../-Agenda-">Le site</a>
</div>' ;


// ------------------------------------------------
// Remplissage du formulaire
// ------------------------------------------------
	?>
</p>
<table width="800" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td valign="top"><form name="form1" method="post" action=""  enctype="multipart/form-data" >
        <table width="600" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
          <tr>
            <th colspan="2"><?php 

			$reponse_2 = mysql_query("SELECT nom_lieu,tel_reserv_lieu, email_reservation FROM $table_lieu WHERE id_lieu = $lieu_event");
			$donnees_2 = mysql_fetch_array($reponse_2) ;
			if (! $nom_event && ! $tel_reserv_event)
				$tel_reserv_event = $donnees_2['tel_reserv_lieu'];
			if (! $nom_event && ! $email_reservation) $email_reservation = $donnees_2['email_reservation'];
			echo '- ' . $donnees_2['nom_lieu'] . ' -<br>' ;
			
			if (empty ($_GET['id']) OR $_GET['id'] == NULL)
			{ echo 'nouvelle entr&eacute;e</i>' ; }
			else
			{
				echo 'Vous modifiez l\'événement "<b>' .$nom_event. '</b>" <br />(id'.$id.')'; 
			}
			?></th>
          </tr>
		  
		  <!-- tr>
			<td colspan="2">
			<?php /*
			$auteur_kidonaki = $donnees_2['auteur_kidonaki'];
			// Si le LIEU possède un compte, lui proposer de vendre des places sur Kido
			if (isset($auteur_kidonaki) AND $auteur_kidonaki != 0)
			{
				// Vérifier que l'événement est encore joué au moins 15 jours
				$JJ_fin = substr($date_event_fin, 8, 2);
				$MM_fin = substr($date_event_fin, 5, 2);
				$AAAA_fin = substr($date_event_fin, 0, 4);
				$date_actuelle = time();
				$date_limite_vente = date(mktime(0, 0, 0, $MM_fin, $JJ_fin-15, $AAAA_fin));
				if (($date_limite_vente-$date_actuelle) < 0)
				{
					echo '<div align="center">
					<img src="../kidonaki/bouton_lier_event.jpg"> <br />
					Cet événement se termine dans moins de 10 jours. <br />
					Il n\'est donc plus possible de mettre des places en vente sur Kidonaki
					</div>' ;
				}
				else
				{		
					// Vérifier qu'il n'y a pas déjà des places pour cet événement sur Kido			
					if (isset($article_kidonaki) AND $article_kidonaki == 0)
					{
						echo '<div align="center">
						<a href="ajout_places_kidonaki.php?id=' . $id . '" target="_blank"><img src="../kidonaki/bouton_lier_event.jpg" alt="Proposer des places pour cet événement sur Kidonaki" title="Proposer des places pour cet événement sur Kidonaki"></a><br />
						
						Vous êtes <a href="http://www.kidonaki.be/spip.php?auteur' . $auteur_kidonaki . '">Kidonateur</a>. Voulez-vous <a href="ajout_places_kidonaki.php?id=' . $id . '" target="_blank">proposer des places pour cet événement sur <strong>Kidonaki</strong></a>? </div>' ;
					}
					
					// Sinon préciser qu'il y a déjà des places pour cet événement sur Kido
					else
					{
						echo '<div align="center">
						<img src="../kidonaki/bouton_lier_event.jpg" alt="Vous avez déjà mis des places en vente sur Kidonaki pour cet événement" title="Vous avez déjà mis des places en vente sur Kidonaki pour cet événement"> <br />
						  Vous avez déjà mis des places en vente sur Kidonaki pour cet événement
						</div>' ;
					}
				}
			}
			else
			{
				// Pas encore kidonateur
				echo '<div align="center">
				<img src="../kidonaki/bouton_lier_event.jpg" alt="Proposer des places pour cet événement sur Kidonaki" title="Proposer des places pour cet événement sur Kidonaki"><br />
Si vous ne possédez pas encore de compte Kidonaki, suivez ce lien pour 
<a href="http://www.kidonaki.be/spip.php?page=inscription&inscription=oui&type_auteur=lieu&choix_type=ok">ouvrir un compte pour "lieux culturels" sur le site Kidonaki</a>. Vous devrez ensuite transmettre l\'identifiant de votre nouveau compte Kidonaki à <a href="mailto:info@demandezleprogramme">info@demandezleprogramme</a> afin que nous puissions réaliser le lien entre les deux services.
				</div>' ;
			}

		*/	?>
			  
			</td>
		  </tr -->
		  

          <tr>
            <td>Nom de l'&eacute;v&eacute;nement
			<?php if (isset ($error_nom_event) AND $error_nom_event != NULL) {echo $error_nom_event ; } ?></td>
            <td>
			
			<?php
			// Si la période re représentation de l'événement est dépassée, empêcher l'utilisateur de modifier le titre de l'événement. Ceci évite que les LIEUX "n'effacent leurs événements" en les remplaçant par d'autres
	$aujourdhui=date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));

	$limit_edit_annee = substr($date_event_fin, 0, 4);
	$limit_edit_mois = substr($date_event_fin, 5, 2);
	$limit_edit_jour = substr($date_event_fin, 8, 2);	
		
	$date_limite = date(mktime(0, 0, 0, $limit_edit_mois, $limit_edit_jour+1, $limit_edit_annee));

	// Afin d'éviter un refus d'édition du titre quand il s'agit d'un événement fraichement créé :
	$date_nouvelle_creation = $limit_edit_mois . $limit_edit_jour . $limit_edit_annee ;
	//echo $date_nouvelle_creation ;
	
	//echo '<br /> Date fin = ' . $limit_edit_jour .'-'.$limit_edit_mois .'-' . $limit_edit_annee .'<br />' ;
	//echo '<br /> ----- ' . $date_limite .' ****** ' . $aujourdhui .'<br>' ;
	//$kjhgjhgfy = ($date_limite - $aujourdhui) ;
	//echo '<br /> ----- ' . $kjhgjhgfy .'<br>' ;
	if (($date_limite-$aujourdhui) > 0 OR $date_nouvelle_creation == '00000000')
	{
	// On peut encore modifier le titre
	echo '<input name="nom_event" type="text" id="nom_event" value="';
	if (isset($nom_event)) echo htmlspecialchars($nom_event);
	echo '" size="70" maxlength="200" />';
	}
	else
	{
	// Interdiction de modifier le titre
	echo '<strong>' . $nom_event . '</strong><br /> <div class="mini">Le titre ne peut plus être modifié. Si toutefois vous souhaitez y apporter une modification, contactez l\'administrateur à info@demandezleprogramme.be en n\'oubliant pas de lui fournir le numéro de l\'événement.</div>' ;
	echo '<input type="hidden" name="nom_event" value=" ' . htmlspecialchars($nom_event) . '" />' ;
	}	
	?>
	</td>
          </tr>

          <tr>
            <td>
            	Pour faire de cet événement un sous-événement, choisissez ici son événement parent (festival)
            </td>
            <td>
<?php 		
			// sous-événement : choisir l'événement parent / voir listing_events_gp.php
			$chn = (int) substr($date_event_debut, 0, 4);
			if ((int) substr($date_event_debut, 5, 2) <= 7)
				$chn--;
			$date_debut_choix = $chn++.'-08-01';
			$date_fin_choix = $chn.'-08-0';

			$chn = '';
			$reponse_2 = mysql_query("SELECT id_event,parent_event,nom_event FROM $table_evenements_agenda 
			WHERE lieu_event=$lieu_event AND id_event<>$id AND (parent_event=0 AND date_event_debut>'$date_debut_choix' AND date_event_debut<'$date_fin_choix' OR parent_event=$id)
			ORDER BY parent_event DESC,date_event_debut DESC");
			while ($donnees_2 = mysql_fetch_array($reponse_2)) {
				if ($donnees_2['parent_event']) {
					echo 'Cet événement est parent, il ne peut pas devenir un sous-événement';
					break;
				}
				$chaine_raccourcie = raccourcir_chaine ($donnees_2['nom_event'], 50); // retourne la chaine raccourcie
				$chn .= '<option value="' . $donnees_2['id_event'] .'"';		
				if ($donnees_2['id_event'] == $parent_event) // pré-sélectionner ?
					$chn .= ' selected="selected" ';
				$chn .= '>'.htmlspecialchars($chaine_raccourcie).'</option>';
			}
			if ($chn)
				echo '<select name="parent_event"><option value="0">-</option>',$chn,'</select>',"\n";
?>
            </td>
          </tr>

          <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_1']) AND $donnees['pic_event_1'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_1.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>
				Image de l'affiche
	            <input name="source_pic_1" type="file" id="source_pic_1" /></p></td>
          </tr>
		  
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_2']) AND $donnees['pic_event_2'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_2.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 2
              <input name="source_pic_2" type="file" id="source_pic_2" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image2" /></label></p></td>
          </tr>
		  
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_3']) AND $donnees['pic_event_3'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_3.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 3
              <input name="source_pic_3" type="file" id="source_pic_3" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image3" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_4']) AND $donnees['pic_event_4'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_4.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 4
              <input name="source_pic_4" type="file" id="source_pic_4" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image4" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_5']) AND $donnees['pic_event_5'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_5.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 5
              <input name="source_pic_5" type="file" id="source_pic_5" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image5" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_6']) AND $donnees['pic_event_6'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_6.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 6
              <input name="source_pic_6" type="file" id="source_pic_6" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image6" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_7']) AND $donnees['pic_event_7'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_7.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 7
              <input name="source_pic_7" type="file" id="source_pic_7" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image7" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_8']) AND $donnees['pic_event_8'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_8.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 8
              <input name="source_pic_8" type="file" id="source_pic_8" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image8" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_9']) AND $donnees['pic_event_9'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_9.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 9
              <input name="source_pic_9" type="file" id="source_pic_9" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image9" /></label></p></td>
          </tr>
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees['pic_event_10']) AND $donnees['pic_event_10'] == 'set')
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_10.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" />';
			}
							
			?>            </td>
            <td><p>Image 10
              <input name="source_pic_10" type="file" id="source_pic_10" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image10" /></label></p></td>
          </tr>

          <tr>
            <td>Date de <strong>d&eacute;but</strong> de l'&eacute;v&eacute;nement </td>
            <td> Jour
              - Mois -
              Ann&eacute;e
              <?php // LISTE d&eacute;roulante des JOURS
echo '<select name="select_JJ_debut">';
for ($list_j_comp=1 ; $list_j_comp<=31 ; $list_j_comp++)
{
	$list_j_comp = add_chaine_2_car ($list_j_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_j_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($JJ_debut == $list_j_comp)
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_j_comp.'</option>';
}
echo '</select>';
?>
              <?php // LISTE d&eacute;roulante des MOIS
echo '<select name="select_MM_debut">';
for ($list_m_comp=1 ; $list_m_comp<=12 ; $list_m_comp++)
{
	$list_m_comp = add_chaine_2_car ($list_m_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_m_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($MM_debut == $list_m_comp)
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_m_comp.'</option>';
}
echo '</select>';

?>
          <?php // LISTE d&eacute;roulante des ANNEES
echo '<select name="select_AAAA_debut">';
for ($list_a_comp=2007 ; $list_a_comp<= date ('Y')+1 ; $list_a_comp++)
{
	echo '<option value="' . $list_a_comp .'"';		
	// Faut-il présélectionner ?
	// Si aucune date encore introduite, déjà sélectionner l'année en cours
	if ($AAAA_debut + 0 == 0)
	{ $AAAA_debut = date('Y'); }
	
	if ($AAAA_debut == $list_a_comp)
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_a_comp.'</option>';
}
echo '</select>';

?>          </tr>
		  		<?php // introduire une rangée pour le message d'erreur
				if (isset ($error_date) AND $error_date != NULL)
				{
					echo '<tr><td colspan="2" align="center">' . $error_date . ' </td></tr>'; 
				}
				?>
          <tr>
            <td>Date de <strong>fin</strong> de l'&eacute;v&eacute;nement </td>
            <td>Jour - Mois - Ann&eacute;e
              <?php // LISTE d&eacute;roulante des JOURS
echo '<select name="select_JJ_fin">';
for ($list_j_comp=1 ; $list_j_comp<=31 ; $list_j_comp++)
{
	$list_j_comp = add_chaine_2_car ($list_j_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_j_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($JJ_fin == $list_j_comp)
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_j_comp.'</option>';
}
echo '</select>';
?>
              <?php // LISTE d&eacute;roulante des MOIS
echo '<select name="select_MM_fin">';
for ($list_m_comp=1 ; $list_m_comp<=12 ; $list_m_comp++)
{
	$list_m_comp = add_chaine_2_car ($list_m_comp) ; // fonction pour compl&eacute;ter la chaine pour longueur == 2 caract&egrave;res
	echo '<option value="' . $list_m_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($MM_fin == $list_m_comp)
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_m_comp.'</option>';
}
echo '</select>';
?>
          <?php // LISTE d&eacute;roulante des ANNEES
echo '<select name="select_AAAA_fin">';
for ($list_a_comp=2007 ; $list_a_comp<= date ('Y')+1 ; $list_a_comp++)
{
	echo '<option value="' . $list_a_comp .'"';		
	// Faut-il présélectionner ?
	// Si aucune date encore introduite, déjà sélectionner l'année en cours
	if ($AAAA_fin + 0 == 0)
	{ $AAAA_fin = date('Y'); }
	if ($AAAA_fin == $list_a_comp)
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_a_comp.'</option>';
}
echo '</select>';

//--- jours calendriers
echo '<input type="hidden" name="jours_actifs_event" value="',implode(',', $jours_actifs_event),'" />';
?>          </tr>
          
          <tr>
            <td>R&eacute;gion dans laquelle se d&eacute;roule l'&eacute;v&eacute;nement
<?php
	if (isset ($error_ville_event) AND $error_ville_event != NULL) {echo $error_ville_event ; }
	echo '</td>',"\n",'<td>',"\n";
	// Liste d&eacute;roulante des r&eacute;gions
	echo '<select name="ville_event">';
	foreach($regions as $cle_region => $element_region)
	{
		echo '<option value="' . $cle_region .'"';		
		// Faut-il preselectionner
		if (isset($ville_event) AND $ville_event == $cle_region)
		{
			echo 'selected';
		}
		echo '>'.$element_region.'</option>';
	}
	echo '</select>',"\n",'</td>',"\n",'</tr>',"\n";


	//----- autocomplete sur nom_pres : lieux de représentation : nom, adresse, code postal, localité, pays
	echo '<tr>',"\n",'<td colspan="2">',"\n",'<span class="mini">Entrez quelques lettres ci-dessous pour sélectionner un lieu de représentation existant.<br />Ou remplissez les données complètes d\'un nouveau lieu de représentation (nom, adresse, localité, code postal, pays)</span>',"\n",'</td>',"\n",'</tr>',"\n";
	echo '<tr>',"\n",'<td>Nom du lieu de représentation',"\n";
	if (isset($error_pres_event) AND $error_pres_event != NULL)
		echo $error_pres_event;

	echo '<input type="hidden" name="id_pres_event" id="id_pres_event" value="',$id_pres_event,'" />',"\n",'</td>',"\n";
	echo '<td class="ui-widget">',"\n",'<input type="text" id="nom_pres" name="nom_pres" value="" size="60" />',"\n",'</td>',"\n",'</tr>',"\n";

	echo '<tr>',"\n",'<td>Adresse de représentation',"\n";
	if (isset($error_adresse_pres) AND $error_adresse_pres != NULL)
		echo $error_adresse_pres;
	echo '</td>',"\n",'<td>',"\n",'<input type="text" name="adresse_pres" id="adresse_pres" value="" size="60" />',"\n",'</td>',"\n",'</tr>',"\n";

	echo '<tr>',"\n",'<td>Code postal de représentation',"\n";
	if (isset($error_postal_pres) AND $error_postal_pres != NULL)
		echo $error_postal_pres;
	echo '</td>',"\n",'<td>',"\n",'<input type="text" name="postal_pres" id="postal_pres" value="" size="20" />',"\n",'</td>',"\n",'</tr>',"\n";

	echo '<tr>',"\n",'<td>Localité de représentation',"\n";
	if (isset($error_localite_pres) AND $error_localite_pres != NULL)
		echo $error_localite_pres;
	echo '</td>',"\n",'<td>',"\n",'<input type="text" name="localite_pres" id="localite_pres" value="" size="30" />',"\n",'</td>',"\n",'</tr>',"\n";

	echo '<tr>',"\n",'<td>Pays de représentation',"\n";
	if (isset($error_pays_pres) AND $error_pays_pres != NULL)
		echo $error_pays_pres;
	echo '</td>',"\n",'<td>',"\n",'<select name="pays_pres" id="pays_pres">',"\n";
	reset($payspresent);
	while (list($k, $chn) = each($payspresent))
		echo '<option value="',$k,'">',$chn,'</option>',"\n";
	echo '</select>',"\n",'</td>',"\n",'</tr>',"\n";
?>

          <tr>
            <td>Genre de l'&eacute;v&eacute;nement (th&eacute;&acirc;tre, concert...)
              <?php if (isset ($error_genre_event) AND $error_genre_event != NULL) {echo $error_genre_event ; } ?>            </td>
            <td><?php 
				echo '<select name="genre_event">';
	foreach($genres as $cle_genre => $element_genre)
	{
		echo '<option value="' . $cle_genre .'"';		
		// Faut-il preselectionner
		if (isset($genre_event) AND $genre_event == $cle_genre)
		{
			echo 'selected';
		}
		$max=30; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
		$genre_event = raccourcir_chaine ($genre_event,$max); // retourne $chaine_raccourcie
		echo '>'.$element_genre.'</option>';
	}
	echo '</select>';

?>            </td>
          </tr>
		  
          <tr>
            <td>Numéro de téléphone de réservation</td>
            <td>
  <input name="tel_reserv_event" type="text" id="tel_reserv_event" value="<?php 
			if (isset ($tel_reserv_event)) echo $tel_reserv_event; ?>" size="30" maxlength="50" /> <span class="mini">(facultatif)</span>
            </td>
          </tr>
          <tr>
          	<td>Adresse e-mail de réservation</td>
          	<td>
          		<input type="text" value="<?php if (isset($email_reservation)) echo $email_reservation; ?>" name="email_reservation" />
          	</td>
          </tr>
          <tr>
            <td>Prix minimum</td>
            <td>
				<input name="prix_min_event" type="text" id="prix_min_event" value="<?php 
			if (isset($prix_min_event)) echo $prix_min_event;
				?>" size="10" /> &euro; &nbsp; <span class="mini">Indiquez une tranche de prix ou le prix unique.</span>
            </td>
          </tr>
          <tr>
            <td>Prix maximum</td>
            <td>
				<input name="prix_max_event" type="text" id="prix_max_event" value="<?php 
			if (isset($prix_max_event)) echo $prix_max_event;
				?>" size="10" /> &euro;
            </td>
          </tr>
		<tr>
			<td style="text-align:right; vertical-align:top;"><?php // Lier à un événement d'une saison précédente
			echo '<input type="hidden" name="saison_preced_event" value="',isset($saison_preced_event) ? $saison_preced_event : '','" />';
			echo '<input type="checkbox" name="saison_preced_oui" id="saison_preced_oui" value="y"',isset($_POST['saison_preced_oui']) ? ' checked="checked" />' : ' />';
			?></td>
			<td>
				<label for="saison_preced_oui">Cet événement est une reprise.</label>
				<br /><span class="mini">Cette information nous permet d'associer les avis, votes, critique et interview des représentations passées à l'événement actuel.</span>
			</td>
		</tr>
		  <tr>
            <td colspan="2">R&eacute;sum&eacute; de l'&eacute;v&eacute;nement
            <br /><span class="rouge"> /!\ </span><span class="mini"> Description en quelques phrases (300 
  caract&egrave;res), sans paragraphe ni retour &agrave; la ligne, sans renoter le 
  titre, ni les dates. Ce r&eacute;sum&eacute; s'affichera lorsque le moteur de 
  recherche affiche des r&eacute;sultats, et dans &quot;la Une&quot;. Le but est donc avant 
  tout de d&eacute;crire de quoi il s'agit plut&ocirc;t que de donner la liste 
              exhaustive des intervenants. 
              Exemple: &quot;Un spectacle de Jean Dupont qui revisite le mythe de Faust en 
              m&ecirc;lant la danse, le cirque et les grands personnages historiques. Une 
              aventure pluridisciplinaire, mise en voix par Anne Durand, avec Louis 
              Dupuis.&quot;</span>

              <?php if (isset ($error_resume_event) AND $error_resume_event != NULL) {echo $error_resume_event ; } ?>
			
              <textarea name="resume_event_chp" rows="3" id="resume_event_chp" style="width: 700px; "><?php if (isset($resume_event)){echo br2nl($resume_event);} ?>
</textarea></td>
          </tr>
		  
		  
          <tr>
            <td colspan="2"><p><br />
                Description de l'&eacute;v&eacute;nement <br />
			    <span class="rouge"> /!\ </span><span class="mini">N'oubliez donc pas ici, outre la 
                description du sujet et la distribution, de pr&eacute;ciser les heures, le prix 
              et le num&eacute;ro de r&eacute;servation</span><span class="mini">.</span>
                <?php if (isset ($error_description_event) AND $error_description_event != NULL) {echo $error_description_event ; } ?>
                <br>
                <textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 700px; height: 280px"><?php if (isset($description_event)){echo $description_event;} ?></textarea>
              </p>
            </td>
          </tr>

                <tr>
                  <td align="center" valign="middle"><?php 		
			// Afficher le lien vers la vid&eacute;o
			 if (isset ($donnees['video_event']) AND $donnees['video_event'] != NULL)
			{
				echo '<a href="../' . $folder_videos . $donnees['video_event'] .'" target="_blank" 
				title="Voir la vid&eacute;o li&eacute;e &agrave; cet &eacute;v&eacute;nement" /><img src="../design_pics/ico_video.jpg" alt="" /></a>';
			}
							
			?>                  </td>
                  <td><!-- Vid&eacute;o
          <INPUT type=hidden name=MAX_FILE_SIZE  VALUE="">
		                  <input name="source_video" type="file" id="source_video" />
                          <label>Effacer la vid&eacute;o
                            <input type="checkbox" name="effacer_video" />
                          </label>

                  <div class="error_form" align="center">/!\ L&rsquo;upload peut durer plusieurs minutes...</div> -->				  </td>
                </tr>
          <tr>
            <td colspan="2"><div align="center"> <br />
                <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
                <br />
              </div></td>
          </tr>
        </table>
      </form></td>
    <td valign="top"><?php 
if (($date_event_debut == '0000-00-00') OR ($date_event_fin == '0000-00-00') OR isset($error_date) && $error_date != '')
{
	echo '<p><br /></p><div class="mini_info">Afin de s&eacute;lectionner les jours &laquo;&nbsp;actifs&nbsp;&raquo; de la  p&eacute;riode de spectacle, veuillez d&rsquo;abord s&eacute;lectionner une date de d&eacute;but et de  fin, puis appuyer sur le bouton &laquo;&nbsp;Enregistrer&nbsp;&raquo;.</div>' ;
}
else
{
	echo ' <br />',"\n";
	// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
	
			  
	// --------------------------------------------------------------------
	// ----------------------- AFFICHER CALENDRIERS -----------------------
	// --------------------------------------------------------------------
	// [A] Si p&eacute;riode comprise dans le m&ecirc;me mois : traiter les jours de JJ_debut &agrave; JJ_fin
	if (($MM_debut == $MM_fin) && ($AAAA_debut == $AAAA_fin))
	{
		$AAAA_traite = $AAAA_debut ;
		$MM_traite = $MM_debut ;

		/*echo  ' [A] P&eacute;riode couvrant 1 mois unique. Mois trait&eacute; = '.$MM_traite.' 
		et Ann&eacute;e trait&eacute;e = '.$AAAA_traite . '<br>' ; */
		
		affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	}
	
	// ------------------------------------------------------------------------------------------------------
	else
	{
		// [B1] si la p&eacute;riode s'&eacute;tend sur plusieurs mois, afficher 1 calendrier &agrave; chaque passage dans la boucle. 
		// Commencer par traiter le mois de d&eacute;but de p&eacute;riode
		$AAAA_MM_traite = substr($date_event_debut, 0, 7);
		$AAAA_traite = $AAAA_debut ;
		$MM_traite = $MM_debut ;
		// echo '<b>[B1] Mois trait&eacute; (1er mois de la p&eacute;riode) = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite . '</b><br>' ;
		
		$tableau_jours = array() ;	
	
		affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	
		// Incr&eacute;menter le mois :		
		if	($MM_traite == 12)
		{
			$MM_traite = 1 ;
			$AAAA_traite = $AAAA_traite + 1 ;
		}
		else
		{
			$MM_traite = $MM_traite + 1 ;
		}
	
		// -------------------------------------------------------------------------------------------------
		// [B2] traiter tous les mois suivants jusqu'&agrave; ce qu'on arrive au mois de fin de PERIODE
		// La boucle s'arr&ecirc;te quand (($MM_traite == $MM_debut) && ($AA_fin == $AAAA_traite))
	
		while (($MM_traite != $MM_fin) OR ($AAAA_traite != $AAAA_fin))
		{
			/*unset ($tableau_jours[$JJ_db]);	*/
			$tableau_jours = array() ;
		
			//echo  '<b>[B2] Mois "suivant" trait&eacute; = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite.'</b><br>' ;
			
			affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	
			// Incr&eacute;menter le mois :		
			if	($MM_traite == 12)
			{
				$MM_traite = 1 ;
				$AAAA_traite = $AAAA_traite + 1 ;
			}
			else
			{
				$MM_traite = $MM_traite + 1 ;
			}
		}
		// -------------------------------------------------------------------------------------------------
		// [B3] traiter le dernier mois de JJ = 1 &agrave; JJ = JJ_fin
		$tableau_jours = array() ;
		$AAAA_MM_traite = substr($date_event_fin, 0, 7);
	
		//echo  '<b> [B3] Mois trait&eacute; (Dernier mois de la p&eacute;riode) = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite . '</b><br>' ;
	
		affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	}
	
	
	// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
	
	?>
      <?php // Légende du calendrier ?>
      <br>
      <table border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#FFFFFF">
        <tr>
          <td class="calendar-month" align="center">Legende :</td>
        </tr>
        <tr>
          <td class="checked">Jour de représentation</td>
        </tr>
        <tr>
          <td class="unchecked">Pas de représentation</td>
        </tr>
        <tr>
          <td class="nonchecked">Hors période de représentation</td>
        </tr>
      </table>
      <?php } ?>
    </td>
  </tr>
</table>
<?php 


//--- mysql_close($db2dlp);

?>
<a name="bas" id="bas"></a>
<p>&nbsp;</p>
</body>
</html>
