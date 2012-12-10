<?php 
session_start();
?>

<?php 
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'édition des fiches de spectacles
// L'ID du lieu culturel est passé par l'adresse et vérifié par la SESSION !!!A FAIRE!!!
// edit_event.php?new=creer pour créer une nouvelle entrée
// edit_event.php?id=... pour éditer l'entrée
// /!\ les variable SESSION "lieu_admin_spec" et "group_admin_spec" sont modifiées afin d'accéder au répertoire d'images du USER actuel
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition de la fiche d'un &eacute;v&eacute;nement culturel</title>


<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="../vertiny/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		elements : "ajaxfilemanager",
		theme : "advanced",
		plugins : "advhr,advimage,advlink,paste,noneditable,contextmenu",
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


<script type="text/javascript">
// Fonction pour masquer le champ des heures si on choisit 'En journée"
function masquer_choix_heure()
{
	if(document.getElementById('checkbox_en_journee').checked)
	{
		document.getElementById('zone_heure').style.visibility = 'hidden';
	}
	else
	{
		document.getElementById('zone_heure').style.visibility = 'visible';
	}
}


function forcer_masquer_choix_heure()
{
	document.getElementById('zone_heure').style.visibility = 'hidden';
}


</script>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<link href="../css_calendrier.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="head_admin_agenda"></div>

<p class="error_form">/!\ En acc&eacute;dant &agrave; cette page, les attributs de SuperAdmin sont remplac&eacute;s par ceux du USER concern&eacute; par l'&eacute;v&eacute;nement. <a href="open_sess.php"> Modifier la session</a></p>

<p>
  <?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';
require '../fct_upload_pic_event_4.php';
require '../fct_upload_video.php';

$indetermine = '' ; // Texte par défaut (-- INDETERMINE --)
$periode_max = (mktime(0, 0, 0, 6, 1, 1970)); // Intervalle (en mois) maximum entre début et fin d'un événement


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



//-----------------------------------------
// Créer une nouvelle entrée (si GET ...php?new=creer&lieu=...)
//-----------------------------------------
if (isset ($_GET['new']) AND $_GET['new'] == 'creer') // La variable GET qui donne l'ID à confirmer. Si NULL -> nouvelle entrée
{
	if (isset ($_GET['lieu']) AND $_GET['lieu'] != NULL) // La variable GET qui donne l'ID du Lieu culturel
	{
		$id_lieu = htmlentities($_GET['lieu'], ENT_QUOTES);

		// Créer une entrée vide dans TABLE "table_evenements_agenda"
		mysql_query("INSERT INTO `$table_evenements_agenda` (`lieu_event`) VALUES ($id_lieu)");
	
		$nouvel_id_table_evenements_agenda = mysql_insert_id() ; // sera utile pour créer un lien d'accès pour éditer les données
		echo '<br><br><br><div class="info"><p>Une nouvelle fiche descriptive d\'événement a été créé et peut être éditée 
		<a href="edit_event.php?id='.$nouvel_id_table_evenements_agenda.'">Continuer</a></p></div><br>' ;
		
		mysql_close($db2dlp);
		exit();
	}
	else
	{
		mysql_close($db2dlp);
		exit();
	}
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
	// TEST DU LIEU CULTUREL RATTACHE A L'EVENEMENT
	if (isset($_POST['lieu_event']) AND ($_POST['lieu_event'] != NULL)) 
	{
		$lieu_event = htmlentities($_POST['lieu_event'], ENT_QUOTES);
		
		mysql_query("UPDATE `$table_evenements_agenda` SET `lieu_event` = '$lieu_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}
	else
	{
		$lieu_event = $indetermine;
		mysql_query("UPDATE `$table_evenements_agenda` SET `nom_event` = '$nom_event' WHERE `id_event` = '$id' LIMIT 1 ");
		$error_lieu_event = '<div class="error_form">Aucun lieu culturel n\'est rattaché à cet événement l\'événement</div>';
		$rec .= '- Aucun lieu culturel n\'est rattaché à cet événement l\'événement<br>';
	}
	// -----------------------------------------
	// TEST DU NOM DE L'EVENEMENT 
	if (isset($_POST['nom_event']) AND ($_POST['nom_event'] != NULL)) 
	{
		$nom_event = strip_tags($_POST['nom_event']);
		$nom_event = stripslashes($nom_event);
		$nom_event = mysql_real_escape_string($nom_event);
		$nom_event = str_replace("’", "'", $nom_event);
		mysql_query("UPDATE `$table_evenements_agenda` SET `nom_event` = '$nom_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}
	else
	{
		$nom_event = $indetermine;
		mysql_query("UPDATE `$table_evenements_agenda` SET `nom_event` = '$nom_event' WHERE `id_event` = '$id' LIMIT 1 ");
		$error_nom_event = '<div class="error_form">Vous devez indiquer un nom pour désigner l\'événement</div>';
		$rec .= '- Vous devez indiquer un nom pour désigner l\'événement<br>';
	}
	
	
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
		//$MM_debut = str_pad($MM_debut, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$MM_fin = htmlentities($_POST['select_MM_fin'], ENT_QUOTES);
		//$MM_fin = str_pad($MM_fin, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$JJ_debut = htmlentities($_POST['select_JJ_debut'], ENT_QUOTES);
		//$JJ_debut = str_pad($JJ_debut, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$JJ_fin = htmlentities($_POST['select_JJ_fin'], ENT_QUOTES);
		//$JJ_fin = str_pad($JJ_fin, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne

		// La date de début est-elle inférieure à la date de fin ?
		$date_event_debut = $AAAA_debut.$MM_debut.$JJ_debut ;
		$date_event_fin = $AAAA_fin.$MM_fin.$JJ_fin ;

		$time_event_debut = date(mktime(0, 0, 0, $MM_debut, $JJ_debut, $AAAA_debut));
		$time_event_fin = date(mktime(0, 0, 0, $MM_fin, $JJ_fin, $AAAA_fin));

		//echo $time_event_debut .'<br>'.$time_event_fin .'<br>'.$periode_max .'<br>'; 

		if (($time_event_debut <= $time_event_fin ) AND ($time_event_fin - ($time_event_debut + $periode_max)<=0))
		{
			$date_event_debut = $AAAA_debut.'-'.$MM_debut.'-'.$JJ_debut ;
			$date_event_fin = $AAAA_fin.'-'.$MM_fin.'-'.$JJ_fin ;
			
			mysql_query("UPDATE `$table_evenements_agenda` SET `date_event_debut` = '$date_event_debut' WHERE `id_event` = '$id' LIMIT 1 ");
			mysql_query("UPDATE `$table_evenements_agenda` SET `date_event_fin` = '$date_event_fin' WHERE `id_event` = '$id' LIMIT 1 ");
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
	// TEST HEURE EVENEMENT 
	if (isset($_POST['checkbox_en_journee']) AND ($_POST['checkbox_en_journee'] == "en_journee"))
	{
		$heure_minute_event = 'jj-jj' ;
	}
	elseif (isset($_POST['select_heure_event']) AND preg_match('/[0-9]{2}$/', $_POST['select_heure_event']) AND isset($_POST['select_minute_event']) AND preg_match('/[0-9]{2}$/', $_POST['select_minute_event']))
	{
		$heure_minute_event = htmlentities($_POST['select_heure_event'], ENT_QUOTES) . 'h' . htmlentities($_POST['select_minute_event'], ENT_QUOTES) ;
	}
	else
	{
		$heure_minute_event = 'nn-nn' ;
		$error_heure_minute_event = '<div class="error_form">Il faut indiquer une heure pour l\'événement, ou alors cocher la case pour dire que l\'événement se joue en journée, ou qu\'il y a plusieurs représentations</div>';
		$rec .= '- Heure de l\'événement<br>';
	}
	
	mysql_query("UPDATE `$table_evenements_agenda` SET `heure_minute_event` = '$heure_minute_event' WHERE `id_event` = '$id' LIMIT 1 ");



	// -----------------------------------------
	// CHOIX DE LA REGION OU A LIEU L'EVENEMENT 
	
	// Liste déroulante des régions
	if (isset($_POST['ville_event']) AND ($_POST['ville_event'] != 'NULL')) 
	{
		$ville_event = htmlentities($_POST['ville_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `ville_event` = '$ville_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}
	else
	{
		$ville_event = $indetermine;
		$error_ville_event = '<div class="error_form">Vous devez indiquer la région dans laquelle a lieu l\'événement</div>';
		mysql_query("UPDATE `$table_evenements_agenda` SET `ville_event` = '$ville_event' WHERE `id_event` = '$id' LIMIT 1 ");
		$rec .= '- Vous devez indiquer la région dans laquelle a lieu l\'événement<br>';
	}





	
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
	// TEST DESCRIPTION EVENEMENT 
	if (isset($_POST['ajaxfilemanager']) AND ($_POST['ajaxfilemanager'] != NULL)) 
	{
		$allowedTags = '<strong><br><br />'; // Balises de style que les USERS peuvent employer

		//$description_event = nl2br($_POST['ajaxfilemanager']);
		$description_event = str_replace("&nbsp;", " ", $_POST['ajaxfilemanager']);
		$description_event = preg_replace('/\s+/', ' ', $description_event); // supprimer espaces multiples
		$description_event = html_entity_decode($description_event);
		$description_event = strip_tags($description_event, $allowedTags) . '' ;
		$description_event = str_replace("\r\n", "", $description_event);
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
			
			mysql_query("UPDATE `$table_evenements_agenda` SET `description_event` = '$description_event_2_db' 
			WHERE `id_event` = '$id' LIMIT 1 ");		
		}
		else
		{
			mysql_query("UPDATE `$table_evenements_agenda` SET `description_event` = '$description_event_2_db' 
			WHERE `id_event` = '$id' LIMIT 1 ");
		}	
		
	
		// COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - 
		// Vérifier si l'événement contient un "Prénom Nom" de comedien, et effectuer une mise à jour de "ag_comedien_lien"
		include ('../lien_comedien/inc_update_table_lien.php') ;
		// ps : il est nécessaire de concaténer toutes les chaines susceptibles de contenir les noms avant d'appeler la fonction
		$chaine_a_tester_comedien = $description_event_2_db . ' ' . $resume_event_2_db ;
		update_table_ag_comedien_lien_pour_un_event ($id, $chaine_a_tester_comedien) ;
		require '../inc_db_connect.php';
		// COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - COMEDIEN - 
	
	}
	else
	{
		$description_event = $indetermine;
		$error_description_event = '<div class="error_form">Vous devez introduire un texte descriptif de l\'événement</div>';
		$rec .= '- Vous devez introduire un texte descriptif de l\'événement<br>';
	}
	
	

	// -----------------------------------------
	// TEST GENRE EVENEMENT 
	if (isset($_POST['genre_event']) AND ($_POST['genre_event'] != NULL)) 
	{
		$genre_event = htmlentities($_POST['genre_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `genre_event` = '$genre_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}
	else
	{
		$genre_event = $indetermine;
		mysql_query("UPDATE `$table_evenements_agenda` SET `genre_event` = '$genre_event' WHERE `id_event` = '$id' LIMIT 1 ");
		$error_genre_event = '<div class="error_form">Vous devez décrire le GENRE de l\'événement</div>';
		$rec .= '- Vous devez décrire le GENRE de l\'événement<br>';
	}
	
	
	// -----------------------------------------
	// TEST IMAGE et VIGNETTE
	$id_update = $_GET['id'] ;
	// Checker les 3 champs d'upload
	for ($uii = 1; $uii < 4; $uii++)
	{
		$source_im = 'source_pic_' . $uii  ;
		if(!empty($_FILES[$source_im]['tmp_name']) AND is_uploaded_file($_FILES[$source_im]['tmp_name']))
		{
			$num_pic = $uii ; // correspond à l'extension du nom du futur fichier JPEG uploadé
			uploader_4 ($id_update,$uii);	// Upload et construction vignette
		}
	}


	// -----------------------------------------
	// TEST DE LA CRITIQUE 
	if (isset($_POST['critique_event']) AND preg_match('/[0-9]$/', $_POST['critique_event'])) 
	{
		$critique_event = htmlentities($_POST['critique_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `critique_event` = '$critique_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}



	// -----------------------------------------
	// TEST DE LA VIDEO SPIP 
	if (isset($_POST['video_spip_event']) AND preg_match('/[0-9]$/', $_POST['video_spip_event'])) 
	{
		$video_spip_event = htmlentities($_POST['video_spip_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `video_spip_event` = '$video_spip_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}


	// -----------------------------------------
	// TEST DE L'INTERVIEW 
	if (isset($_POST['interview_event']) AND preg_match('/[0-9]$/', $_POST['interview_event'])) 
	{
		$interview_event = htmlentities($_POST['interview_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `interview_event` = '$interview_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}




	// -----------------------------------------
	// TEST DE L'INTERVIEW ESPACE LIVRE
	if (isset($_POST['espace_livres']) AND preg_match('/[0-9]$/', $_POST['espace_livres'])) 
	{
		$espace_livres = htmlentities($_POST['espace_livres'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `espace_livres` = '$espace_livres' WHERE `id_event` = '$id' LIMIT 1 ");
	}




	// -----------------------------------------
	// TEST DU LIEN VERS UN EVENEMENT D'UNE SAISON ANTERIEURE 
	if (isset($_POST['saison_preced_event']) AND preg_match('/[0-9]$/', $_POST['saison_preced_event'])) 
	{
		$saison_preced_event = htmlentities($_POST['saison_preced_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `saison_preced_event` = '$saison_preced_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}





	// -----------------------------------------
	// TEST NOMBRE DE VOTE DU PUBLIC
	if (isset($_POST['jai_vu_event']) AND preg_match('/[0-9]$/', $_POST['jai_vu_event'])) 
	{
		$jai_vu_event = htmlentities($_POST['jai_vu_event'], ENT_QUOTES);
		mysql_query("UPDATE `$table_evenements_agenda` SET `jai_vu_event` = '$jai_vu_event' WHERE `id_event` = '$id' LIMIT 1 ");
	}





	// -----------------------------------------
	// TEST VIDEO

	$source_video = 'source_video' ;
	if( isset($_FILES[$source_video]['tmp_name']) AND !empty($_FILES[$source_video]['tmp_name']) AND is_uploaded_file($_FILES[$source_video]['tmp_name']))
	{
		$debug_concat = uploader_video ($id_update);	// Fonction Upload vidéo
		echo '<div class="error_form">' . $debug_concat . '</div>';
	}



	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------------------------------
	if ($rec != NULL) // Il y a au moins un champ du formulaire qui est mal rempli
	{
		//echo '<div class="alerte"><p><br /></p>' . $rec . '</div><br>' ;
	}
	else // Tout OK -> updater la TABLE 
	{		
		echo '<div class="info">L\'entrée '.$id.' est mise à jour</div>' ; // Message confirmation
	}
}


// ----------------------------------------------------------

// Récupération des données à partir de la TABLE
if (empty ($_GET['id']) OR $_GET['id'] == NULL) // La variable GET qui donne l'ID du lieu. Si NULL -> nouvelle entrée
{
	$id_lieu = htmlentities($_GET['lieu'], ENT_QUOTES);
	echo '<br><br><br><div class="info"><p><a href="edit_event.php?new=creer&amp;lieu=' . $id_lieu . '">Voulez-vous encoder une nouvelle entrée ?</a></p></div><br>' ;
	
	// RAZ des données
	
	$nom_event = '';
	$jours_actifs_event = '';
	$ville_event = '';
	$resume_event = '';
	$description_event = '';
	$genre_event = '';
	$pic_event_1 = '';
}
else
{
	$id = $_GET['id'];
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
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
		
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
		$donnees = mysql_fetch_array($reponse);	

		$lieu_event = $donnees ['lieu_event'];
		$nom_event = $donnees ['nom_event'];
		$ville_event = $donnees ['ville_event'];
		$resume_event = $donnees ['resume_event'];
		$description_event = $donnees ['description_event'];
		$genre_event = $donnees ['genre_event'];
		$pic_event_1 = $donnees ['pic_event_1'];

		$critique_event = $donnees ['critique_event'];
		$video_spip_event = $donnees ['video_spip_event'];
		$interview_event = $donnees ['interview_event'];
		$espace_livres = $donnees ['espace_livres'];

		$saison_preced_event = $donnees ['saison_preced_event'];

		$jai_vu_event = $donnees ['jai_vu_event'];
		
		$date_event_debut = $donnees ['date_event_debut'];
		$date_event_fin = $donnees ['date_event_fin'];

		$AAAA_debut = substr($date_event_debut, 0, 4);
		$AAAA_fin = substr($date_event_fin, 0, 4);
	
		$MM_debut = substr($date_event_debut, 5, 2);
		//$MM_debut = add_chaine_2_car ($MM_debut) ; // fonction pour compléter la chaine pour longueur == 2 caractères
	
		$MM_fin = substr($date_event_fin, 5, 2);
		//$MM_fin = add_chaine_2_car ($MM_fin) ; // fonction pour compléter la chaine pour longueur == 2 caractères

		$JJ_debut = substr($date_event_debut, 8, 2);
		//$JJ_debut = add_chaine_2_car ($JJ_debut) ; // fonction pour compléter la chaine pour longueur == 2 caractères

		$JJ_fin = substr($date_event_fin, 8, 2);
		//$JJ_fin = add_chaine_2_car ($JJ_fin) ; // fonction pour compléter la chaine pour longueur == 2 caractères

		$AAAA_MM_debut = substr($date_event_debut, 0, 7);

		$jours_actifs_event = $donnees ['jours_actifs_event'];
		$jours_actifs_event = explode(",", $jours_actifs_event);
		
		
		
		if ($donnees['heure_minute_event'] == 'jj-jj')
		{
			$en_journee_est_selected = true ; // cocher la case "En Journée"
			$heure_event = 'jj' ;
			$minute_event = 'jj' ;
			
			// Masquer listes des heures et minutes :
			echo '<script type="text/javascript">
			onload=forcer_masquer_choix_heure ;
			</script>
			' ;
			
		}
		elseif ($donnees['heure_minute_event'] == 'nn-nn')
		{
			$en_journee_est_selected = false ; // décocher la case "En Journée"
			$heure_event = 'nn' ;
			$minute_event = 'nn' ;
		}
		else
		{
			$en_journee_est_selected = false ; // décocher la case "En Journée"
			$heure_event = substr($donnees['heure_minute_event'], 0, 2);
			$minute_event = substr($donnees['heure_minute_event'], 3, 2);
		}
		
		


		$article_kidonaki = $donnees ['article_kidonaki'];

		$reponse_2 = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $lieu_event");
		$donnees_2 = mysql_fetch_array($reponse_2) ;
		
		$_SESSION['lieu_admin_spec'] = $lieu_event ; // Variable SESSION pour accès au bon répertoire d'images 
		$_SESSION['lieu_admin_spec_name'] = $donnees_2['nom_lieu'] ; 
		$_SESSION['group_admin_spec'] = '3';  // ouverture session USER

		
		//--------------------------------------------------------------------------------------------------------------
		// Faut-il EFFACER UNE IMAGE ?
		//--------------------------------------------------------------------------------------------------------------
		for ($eeuu = 2; $eeuu < 4; $eeuu++)
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
			$video_a_effacer = '../videos/' . $donnees ['video_event'] ;
		
			if (unlink ($video_a_effacer))
			{ echo '<div class="info">La vidéo "' . $donnees ['video_event'] . '" a bien été effacée</div>'; }
			else 
			{ echo '<div class="alerte">ERREUR :<br /> La vidéo "' . $donnees ['video_event'] . '" n\'a pas été effacée</div>'; }
		

			// Enlever le FLAG de la TABLE
			mysql_query("UPDATE $table_evenements_agenda SET video_event = '' WHERE id_event = '$id' LIMIT 1 ") ;	
		}
		
		
		
		// -------------------------------------------------------------------------------------------------------------
		// Titre H1 complété avec variables
		echo '<h1>Edition de la fiche spectacle : ' . $nom_event . '</h1>
		
		<div class="menu_back">
		<a href="listing_events.php?lieu=' . $donnees['lieu_event'] . '" >Retour au listing des événements</a> | 
		<a href="../../-Detail-agenda-?id_event=' . $id . '">Page en ligne</a> |
		<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a> | 
		<a href="index_admin.php">Menu Admin</a>
		</div>' ;
		// -------------------------------------------------------------------------------------------------------------
		
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
			
			echo $donnees_2['nom_lieu'] . '<br><i>' ;
			
			if (empty ($_GET['id']) OR $_GET['id'] == NULL)
			{ echo 'nouvelle entr&eacute;e</i>' ; }
			else
			{
				echo 'Vous modifiez l\'entr&eacute;e <b>'.$id.'</b><br />('.$nom_event.')</i>'; 
			}
			?></th>
			
			
			<tr>
			<td colspan="2">
			<?php
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
						<a href="../user_admin/ajout_places_kidonaki.php?id=' . $id . '" target="_blank"><img src="../kidonaki/bouton_lier_event.jpg" alt="Proposer des places pour cet événement sur Kidonaki" title="Proposer des places pour cet événement sur Kidonaki"></a><br />
						
						Vous êtes <a href="http://www.kidonaki.be/spip.php?auteur' . $auteur_kidonaki . '">Kidonateur</a>. Voulez-vous <a href="../user_admin/ajout_places_kidonaki.php?id=' . $id . '" target="_blank">proposer des places pour cet événement sur <strong>Kidonaki</strong></a>? </div>' ;
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

			?>
			  
			</td>
		  </tr>
          </tr>
          <tr>
            <td>Lieu culturel auquel est rattach&eacute; l'&eacute;v&eacute;nement
			<?php if (isset ($error_lieu_event) AND $error_lieu_event != NULL) {echo $error_lieu_event ; } ?></td>
            <td><?php 
					
			// LISTE d&eacute;roulante des lieux culturels
			echo '<select name="lieu_event">';
			
			$reponse_2 = mysql_query("SELECT * FROM $table_lieu ");
			while ($donnees_2 = mysql_fetch_array($reponse_2))
			{
				// Raccourcir la chaine :
				$nom_lieu_court = $donnees_2['nom_lieu'] ;
				$max=35; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
				$chaine_raccourcie = raccourcir_chaine ($nom_lieu_court,$max); // retourne $chaine_raccourcie
				
				echo '<option value="' . $donnees_2['id_lieu'] .'"';		
				// Faut-il pr&eacute;-s&eacute;lectionner
				if ($donnees_2['id_lieu'] == $lieu_event )
				{
					echo ' selected="selected" ';
				}
				echo '>'.$chaine_raccourcie.'</option>';
			}
			echo '</select>';
			?>            </td>
          </tr>
          <tr>
            <td>Nom de l'&eacute;v&eacute;nement
			<?php if (isset ($error_nom_event) AND $error_nom_event != NULL) {echo $error_nom_event ; } ?></td>
            <td><input name="nom_event" type="text" id="nom_event" value="<?php if (isset($nom_event)){echo $nom_event;}?>" size="70" maxlength="200"></td>
          </tr>
          <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_1.jpg"  
				title="' . $nom_event . '" />';
			}
							
			?>            </td>
            <td><p>
				Image 1	 
              <input name="source_pic_1" type="file" id="source_pic_1" />
			  			  
			  <a href="#" onclick="document.location.reload();return(false)">Rafraîchir</a> </p></td>
          </tr>
		  
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees ['pic_event_2']) AND $donnees ['pic_event_2'] == 'set' )
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_2.jpg"  
				title="' . $nom_event . '" />';
			}
							
			?>            </td>
            <td><p>Image 2
              <input name="source_pic_2" type="file" id="source_pic_2" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image2" /></label></p></td>
          </tr>
		  
		            <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher vignette
			 if (isset ($donnees ['pic_event_3']) AND $donnees ['pic_event_3'] == 'set' )
			{
				echo '<img src="../' . $folder_pics_event . 'vi_event_' . $id . '_3.jpg"  
				title="' . $nom_event . '" />';
			}
							
			?>            </td>
            <td><p>Image 3
              <input name="source_pic_3" type="file" id="source_pic_3" />
			  
  			  <label>Effacer l'image<input type="checkbox" name="effacer_image3" /></label></p></td>
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
	if ($JJ_debut == $list_j_comp )
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
	if ($MM_debut == $list_m_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_m_comp.'</option>';
}
echo '</select>';


?>
          <?php // LISTE d&eacute;roulante des ANNEES
echo '<select name="select_AAAA_debut">';
for ($list_a_comp=2007 ; $list_a_comp<= date ('Y')+1; $list_a_comp++)
{
	echo '<option value="' . $list_a_comp .'"';		
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($AAAA_debut == $list_a_comp )
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
	if ($JJ_fin == $list_j_comp )
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
	if ($MM_fin == $list_m_comp )
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
	// Faut-il pr&eacute;-s&eacute;lectionner
	if ($AAAA_fin == $list_a_comp )
	{
		echo ' selected="selected" ';
	}
	echo '>'.$list_a_comp.'</option>';
}
echo '</select>';
?>          </tr>
          <tr>
            <td colspan="2" align="center"><span class="champ_obligatoire">/!\</span> N'oubliez pas de <a href="edit_jours_admin.php?id=<?php echo $id ;?>">s&eacute;lectionner les jours actifs</a></td>
          </tr>

          <tr>
            <td>
			 Choisissez <strong>l'heure</strong> de l'événement ou cochez "En journée" s'il n'y a pas d'heure à préciser
			 <?php if (isset ($error_heure_minute_event) AND $error_heure_minute_event != NULL) {echo $error_heure_minute_event ; } ?>
			</td>
            <td>
						
			<?php
			
			// Début de "zone toggle"
			echo'<div id="zone_heure">';
			// Liste des heures
			echo '<br /><select name="select_heure_event"><option value="">h</option>';
			for ($liste_heure_comp=1 ; $liste_heure_comp<=23 ; $liste_heure_comp++)
			{
				$liste_heure_comp = add_chaine_2_car ($liste_heure_comp) ; // fonction pour compléter la chaine pour longueur == 2 caract&egrave;res
				echo '<option value="' . $liste_heure_comp .'"';		
				// Faut-il présélectionner
				if ($heure_event == $liste_heure_comp )
				{
					echo ' selected="selected" ';
				}
				echo '>'.$liste_heure_comp.'</option>';
			}
			echo '</select>';
			
			
			// Liste des minutes
			$valeurs_minutes_disponibles = array(0,15,30,45);
			echo ' : <select name="select_minute_event"><option value="">m</option>';
			foreach ($valeurs_minutes_disponibles as $elem_valeurs_minutes_disponibles)
			{
				$elem_valeurs_minutes_disponibles = add_chaine_2_car ($elem_valeurs_minutes_disponibles) ; // fonction pour compléter la chaine pour longueur == 2 caract&egrave;res
				echo '<option value="' . $elem_valeurs_minutes_disponibles .'"';		
				// Faut-il présélectionner
				if ($minute_event == $elem_valeurs_minutes_disponibles )
				{
					echo ' selected="selected" ';
				}
				echo '>'.$elem_valeurs_minutes_disponibles.'</option>';
			}
			echo '</select>';
			
			// Fin de "zone toggle"
			echo '</div>' ;
			
			
			// Case pour "en journée
			echo ' <input id="checkbox_en_journee" name="checkbox_en_journee" type="checkbox" value="en_journee" ';
			if ($en_journee_est_selected) 
			echo 'checked="checked" ' ;
			echo 'onclick="masquer_choix_heure();" /><label for="checkbox_en_journee"><strong>En journée</strong></label>' ;
			

			?>			
			
			</td>
          </tr>
		  
		  		  
          <tr>
            <td>R&eacute;gion dans laquelle se d&eacute;roule l'&eacute;v&eacute;nement
			<?php if (isset ($error_ville_event) AND $error_ville_event != NULL) {echo $error_ville_event ; } ?></td>
            <td><?php 
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
	echo '</select>';
	
	?>            </td>
          </tr>
          <tr>
            <td colspan="2">R&eacute;sum&eacute; de l'&eacute;v&eacute;nement <br />
			<?php if (isset ($error_resume_event) AND $error_resume_event != NULL) {echo $error_resume_event ; } ?>
			
            <textarea name="resume_event_chp" rows="3" id="resume_event_chp" style="width: 700px; "><?php if (isset($resume_event)){echo br2nl($resume_event);} ?>
</textarea></td>
          </tr>
          <tr>
            <td colspan="2">Description de l'&eacute;v&eacute;nement
			<?php if (isset ($error_description_event) AND $error_description_event != NULL) {echo $error_description_event ; } ?>
			<br>
              <textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 700px; height: 280px"><?php if (isset($description_event)){echo $description_event;} ?></textarea></td>
          </tr>
          <tr>
            <td>Genre de l'&eacute;v&eacute;nement (th&eacute;&acirc;tre, concert...)
			<?php if (isset ($error_genre_event) AND $error_genre_event != NULL) {echo $error_genre_event ; } ?></td>
            <td>			
			<?php 
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

?></td>
          </tr>
          <tr>
            <td>Critique li&eacute;e </td>
            <td>indiquer un num&eacute;ro d'article SPIP
:              
  <input name="critique_event" type="text" id="critique_event" value="<?php 
			if (isset ($critique_event) AND $critique_event != 0)
		{echo $critique_event;}?>" size="5" maxlength="5">
            (z&eacute;ro pour effacer)  </td>
          </tr>


          <tr>
            <td>Vidéo li&eacute;e </td>
            <td>indiquer un num&eacute;ro d'article SPIP
:              
  <input name="video_spip_event" type="text" id="video_spip_event" value="<?php 
			if (isset ($video_spip_event) AND $video_spip_event != 0)
		{echo $video_spip_event;}?>" size="5" maxlength="5">
            (z&eacute;ro pour effacer)  </td>
          </tr>


          <tr>
            <td>Interview li&eacute;e </td>
            <td>indiquer un num&eacute;ro d'article SPIP
:
<input name="interview_event" type="text" id="interview_event" value="<?php 
			if (isset ($interview_event) AND $interview_event != 0)
		{echo $interview_event;}?>" size="5" maxlength="5">
              (z&eacute;ro pour effacer) </td>
          </tr>
		
		
		
		
		<!-- Lier un événement d'une saison antérieure afin d'afficher aussi ses AVIS -->
		   <tr>
            <td>Ev&eacute;nement saison pr&eacute;c&eacute;dente  </td>
            <td>indiquer un ID d'&eacute;v&eacute;nement
:
              <input name="saison_preced_event" type="text" id="saison_preced_event" value="<?php 
			if (isset ($saison_preced_event) AND $saison_preced_event != 0)
		{echo $saison_preced_event;}?>" size="5" maxlength="5">
              (z&eacute;ro pour effacer) </td>
          </tr>
				  
		  
		<!-- Modifier le nombre de votes que les visiteurs ont donné à cet Event -->
		<tr>
		  <td>Nombre de votes reçus</td>
            <td><input name="jai_vu_event" type="text" id="jai_vu_event" value="<?php 
			if (isset ($jai_vu_event) AND $jai_vu_event != 0)
		{echo $jai_vu_event;}?>" size="5" maxlength="8">
              (z&eacute;ro pour effacer) </td>
          </tr>



		  <tr>
            <td>Interview espace livres</td>
            <td>indiquer un num&eacute;ro d'article SPIP
              :
              <input name="espace_livres" type="text" id="espace_livres" value="<?php 
			if (isset ($espace_livres) AND $espace_livres != 0)
		{echo $espace_livres;}?>" size="5" maxlength="5">
              (z&eacute;ro pour effacer) </td>
          </tr>
		  
		     

          <tr>
            <td align="center" valign="middle"><?php 		
			// Afficher le lien vers la vidéo
			 if (isset ($donnees ['video_event']) AND $donnees ['video_event'] != NULL )
			{
				echo '<a href="../' . $folder_videos . $donnees ['video_event'] .'" target="_blank" 
				title="Voir la vidéo liée à cet événement" /><img src="../design_pics/ico_video.jpg"></a>';
			}
							
			?>            </td>
            <td>
			<p>Vid&eacute;o
				<input type="hidden" name="MAX_FILE_SIZE" value="">
                <input name="source_video" type="file" id="source_video" />
                    <label>Effacer la vid&eacute;o
                      <input type="checkbox" name="effacer_video" />
                </label>
            </p></td>
          </tr>
          <tr>
            <td colspan="2"><div align="center"> <br />
                <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update">
                <br />
              </div></td>
          </tr>
        </table>
      </form></td>
    <td valign="top"><?php 
			
if (($date_event_debut == '0000-00-00') OR ($date_event_fin == '0000-00-00') OR $error_date != '')
{
	echo '<p><br /></p><div class="mini_info">Afin de s&eacute;lectionner les jours &laquo;&nbsp;actifs&nbsp;&raquo; de la  p&eacute;riode de spectacle, veuillez d&rsquo;abord s&eacute;lectionner une date de d&eacute;but et de  fin, puis appuyer sur le bouton &laquo;&nbsp;update&nbsp;&raquo;.</div>' ;

}
else
{
	// Bouton vers calendrier sélection des jours
	?>
      <form action="edit_jours_admin.php?id=<?php echo $id ;?>" method="post">
        <div align="center">
          <input name="modif_jours_actifs" type="submit" id="modif_jours_actifs" value="Modifier jours actifs">
        </div>
      </form>
      <br />
      <?php 

		// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
	// param&egrave;tres locaux (pour avoir les noms des jours dans la langue de l'utilisateur
	$oldlocale = setlocale(LC_TIME, NULL); #save current locale
	setlocale(LC_TIME, 'nl_NL'); #dutch
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
	// Code qui doit suivre l'affichage du calendrier
	setlocale(LC_TIME, $oldlocale);
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
	}
} 

mysql_close($db2dlp);

?>
<p>&nbsp;</p>
</body>
</html>
