<?php
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
// Protection rudimentaire de l'accès à la page
///agenda/rss/rss_2_db.php?pw=6wqrv4x7p2

if (isset($_GET['pw']) AND ($_GET['pw'] != NULL))
{
	$pw = htmlentities($_GET['pw'], ENT_QUOTES);
	if ($pw != '6wqrv4x7p2')
	{
		echo '<p align="center"><br /><br /><br /><br /><br />Accès impossible</p>' ;
		exit () ;
		$permission = 'ok' ;
	}
}
else
{
	echo '<p align="center"><br /><br /><br /><br /><br />Accès impossible</p>' ;
	exit () ;
}
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>RSS 2.0 -- DB de DLP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />

<style type="text/css">
<!--
body,td,th {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}

.titre_1_flux {
	color: #CCC;
	font-size: 16px;
	font-weight: bold;
	text-align: center;
	margin:10px;
	margin-left:50px;
	margin-right:50px;
	border: 1px solid #666;
	padding:20px;
	background-color: #9B0238;
}
.bloc_item{
	color:#333;
	margin:10px;
	margin-left:50px;
	margin-right:50px;
	border: 1px solid #666;
	padding:5px;
	background: #E9E9E9 url('http://www.demandezleprogramme.be/agenda/design_pics/conc_bg_gagne.jpg') repeat-x top;
}
.bloc_item a {
	color:#CCFFFF;
}
.bloc_item_title{
	font-size:16px;
	font-weight:bold;
	padding:3px;
	margin-left:30px;
	margin-right:10px;
	color:#000;
	margin-bottom:5px;
}
.info_rss {
	color: #00AA00;
	background-color: #FFFFFF;
	margin-top:20px;
	margin-left:60px;
	margin-right:100px;
	padding:5px ;
}
.alert_rss {
	color: #CC0000;
	background-color: #FFFFFF;
	margin-top:20px;
	margin-left:60px;
	margin-right:100px;
	padding:5px ;
	border: 1px solid #F00;
}
-->
</style>

</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Lecture de Flux RSS pour alimentation de la DB</h1>
<p>&nbsp;</p>


<?php
ini_set("max_execution_time", "780");

require '../inc_fct_base.php';
require '../inc_db_connect.php';
require '../logs/fct_logs.php';
require '../inc_var.php';

$max_lenght_description_event = 3000 ; // Nombre Max de caractères pour la dercription
$max_lenght_resume_event = 480 ; // Nombre Max de caractères pour le résumé de la dercription
$allowedTags_description_event = '<br><br />'; // Balises de style que les USERS peuvent employer

// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Fonctionnement général
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
/* Avant de mettre dans la DB, on teste si l'ITEM n'y est pas déjà. Le test s'effectue sur l'adresse URL des ITEMs stockés dans la TABLE "ag_rss"*/

// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Gestion des erreurs : 
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function signaler_erreur($lieu_rss,$adresse_flux, $type_erreur)
{
	global $retour_email_admin, $email_admin_site, $email_retour_erreur ;
	
	$lieu_erreur = cherche_data_lieu ($lieu_rss) ;
	$lieu_erreur = $lieu_erreur['nom_lieu'] ;
	echo '<div class="alert_rss">Echec de la lecture du flux RSS de <strong>"'.$lieu_erreur.'"</strong></div>' ; 

	// envoi e-mail
	$sujet = 'Erreur lors de la lecture du Flux RSS sur DLP !' ;
	$corps='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css"> <!--
		body,td,th { color: #000000; font-family: Geneva, Arial, Helvetica, sans-serif; font-size: 20px; }
		.pied_mail { color: #CCCCCC; font-size: 10px; text-align: center; }
		-->
		</style> </head> <body>
		<p>&nbsp;</p>';

		$corps.='<p>&nbsp;</p>
		<table width="500" border="0" align="center" cellpadding="30" bgcolor="#CC0000">
		  <tr>
			<td><div align="center"><p>Erreur lors de la lecture du flux RSS alimentant l\'agenda de DLP 
			<br /> <br /> Type de l\'erreur : "' . $type_erreur . '"
			<p>Référence du lieu culturel : ' . $lieu_rss . '</p>
			<p>Adresse du flux : ' . $adresse_flux . '</p>
			<p>Date : le ' . date("d-m-Y à H-i") . '</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<div class="pied_mail">www.demandezleprogramme.be</div>
			</div></td>
		  </tr>
		</table>
		<p>&nbsp; </p>
		</body></html>'; 

		$entete = "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
	 mail_beta($email_admin_site,$sujet,$corps,$entete,$email_retour_erreur);
		//echo $corps ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Rechercher les données d'un LIEU culturel via son ID
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function cherche_data_lieu ($id_corresondant)
{
	$reponse_lieu = mysql_query("SELECT * FROM ag_lieux WHERE id_lieu = $id_corresondant");
	$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
	return $donnees_lieu ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Lecture du fichier RSS
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function lire_rss($flux,$champs)
{
	unset($tmp3) ;

	/* if($chaine = @implode("",@file($fichier))) // Lecture fichier XML */
 	if($flux) 
	{
		$tmp = preg_split("/<\/?"."item".">/",$flux); // explode sur <item>

		$tmp = preg_replace("/<!\[CDATA\[|\]\]>/","",$tmp); // Supprimer les "<![CDATA[" et "]]>"
			
		// pour chaque <item>
		for($i=1;$i<sizeof($tmp)-1;$i+=2) 
		{
			$tmp3[$i-1][] = 1+($i-1)/2; // Création ID pour l'ancre nommée
			
			// Lire chaque champ
			foreach($champs as $champ) 
			{
				$tmp2 = preg_split("/<\/?".$champ.">/",$tmp[$i]);
	
				/*echo '___ ' . $i . ' ___<br>';
				echo '<pre>';
				print_r($tmp2);
				echo '</pre>';*/
							
				$tmp3[$i-1][] = @$tmp2[1]; // Ajouter à la variable tableau
			}
		}
		/*echo '___ TABLEAU "$tmp3" ___<br>';
		echo '<pre>';
		print_r($tmp3);
		echo '</pre>';*/
				  
		return $tmp3; // Retourner la variable tableau
   }
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'enregistrement de l'ITEM dans la DB (ssi l'entrée n'y est pas présente)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function rss_test_et_ecrire_db($lieu_rss, $rss_item_nr, $rss_item_title, $rss_item_link, $rss_item_description, $rss_item_description_courte, $rss_item_periode_debut, $rss_item_periode_fin, $ville_event_rss, $genre_event_rss)
{
	global $max_lenght_resume_event ; 
	global $allowedTags_description_event;
	global $max_lenght_description_event;
	$donnees_lieu = cherche_data_lieu ($lieu_rss) ;
	$message_traitement_item = '' ;
	$nouvel_id_table_evenements_agenda = false ;

	$reponse_rss_test = mysql_query("SELECT COUNT(*) AS test_exist_rss FROM ag_rss 
	WHERE lieu_rss = '$lieu_rss' AND unique_rss = '$rss_item_link' ") or die (mysql_error());
	$donnees_rss_test = mysql_fetch_array($reponse_rss_test);

	if ($donnees_rss_test['test_exist_rss'] > 0) 
	{ // Cet ITEM est déjà enregistré dans la DB de DLP
		$message_traitement_item.=  '<div class="info_rss">Cet ITEM est déjà enregistré dans la DB de DLP</div>' ;
	}
	else
	{ // Cet ITEM n'est pas encore dans la DB de DLP
		$message_traitement_item.=  '<div class="info_rss">Cet ITEM n\'est pas encore dans la DB de DLP</div>' ;
		
		// ----------------------------------------------------------------------------------------------
		// Rajouter l'ITEM dans la DB :
		// ----------------------------------------------------------------------------------------------

		// VIGNETTE DE L'EVENEMENT
		// ! Dans le CMS existant, IMPOSSIBLE de faire un lien vers l'image du LIEU !!!
		
		
		// Calcul des JOURS ACTIFS
		// ! Modèle simplifié pour l'instant : 1 jour ACTIF, qui est le jour de début
		$rss_item_jours_actifs = $rss_item_periode_debut ;		
		
		
		// DESCRIPTIF EVENEMENT
		$rss_item_description = strip_tags($rss_item_description,$allowedTags_description_event);
		$rss_item_description = wordwrap($rss_item_description, 100, " ", 1);

		if (strlen($rss_item_description)>=$max_lenght_description_event)
		{ $rss_item_description = raccourcir_chaine($rss_item_description,$max_lenght_description_event) ; }
		//$rss_item_description_affichage = $rss_item_description ; // Pour affichage
		//$rss_item_description = addslashes($rss_item_description) ; // Pour DB
		
		
		// RESUME EVENEMENT
		$rss_item_description_courte = strip_tags($rss_item_description_courte,$allowedTags_description_event);
		$rss_item_description_courte = wordwrap($rss_item_description_courte, 100, " ", 1);

		if (strlen($rss_item_description_courte)>=$max_lenght_resume_event)
		{ $rss_item_description_courte = raccourcir_chaine($rss_item_description_courte,$max_lenght_resume_event) ; }

	
		// *********************************
		// Enregistrement dans la DB :
		// *********************************
		// ag_event_test
		$sql_check = mysql_query("INSERT INTO `ag_event` (
		`lieu_event` ,`nom_event` ,`date_event_debut` ,
		`date_event_fin` ,`jours_actifs_event` ,`ville_event` ,`description_event` ,
		`resume_event` ,`genre_event`)
		VALUES ('$lieu_rss', '$rss_item_title', '$rss_item_periode_debut',
		'$rss_item_periode_fin', '$rss_item_jours_actifs', '$ville_event_rss', '$rss_item_description',
		'$rss_item_description_courte', '$genre_event_rss')");

		if ($sql_check)
		{
			// Notifier la création dans le rapport + e-mail
			$nouvel_id_table_evenements_agenda = mysql_insert_id() ; // Dernirer ID créé
			log_write ($lieu_rss, '4', $nouvel_id_table_evenements_agenda, 'Création nouvel événement via RSS', 'send_mail') ; 
			//($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
	
			$message_traitement_item.=  '<div class="info_rss">Nouvelle entrée créée dans la DB : ' . 
			$nouvel_id_table_evenements_agenda . '</div>' ;
			
			// Noter dans la TABLE "ag_rss" que cet ITEM est enregistré sur DLP
			mysql_query("INSERT INTO `ag_rss` ( `id_rss`, `lieu_rss`, `unique_rss`) 
			VALUES ('', '$lieu_rss', '$rss_item_link')") or die(mysql_error());
			
		}
		else 
		{ 
			$message_traitement_item.=  '<div class="alert_rss">Erreur ! Les données n\'ont pas été enregistrées !<br />
			Erreur : <br /> <br />' . mysql_error() . ' <br /> <br /> </div>' ; 

			$error_2_rapport = 'Erreur lors de modification événement. Requête = ' . urlencode(mysql_error()) ;
			log_write ($lieu_rss, '4', '0', $error_2_rapport, 'send_mail') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
		}		
	}
			
	// *******************************************
	// Affichage pour visualisation sur la page 
	// *******************************************
	echo '<div class="bloc_item"><div class="bloc_item_title">
	<a name="item_n_' . $rss_item_nr . '" id="item_n_' . $rss_item_nr . '"></a>' .
	$donnees_lieu['nom_lieu'] . ' (ID' . $donnees_lieu['id_lieu'] . ')<br />(' . $rss_item_nr . ') ' . $rss_item_title . '</div>
	<div align="right"><strong>Du ' . $rss_item_periode_debut . ' à ' . $rss_item_periode_fin . ' </strong></div> <br />
	<strong>Résumé : </strong><em>'. stripslashes($rss_item_description_courte) . '</em> <br /> <br />
	<strong>Description complète : </strong>' . stripslashes($rss_item_description) . '<br />
	<a href="' . $rss_item_link . '">' . $rss_item_link . '</a><br />' . 
	$message_traitement_item . '</div>' ;
	
	return $nouvel_id_table_evenements_agenda ; // Valeur de cette fonction qui est retournée pour signaler que OK
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction "uploader_pic_lieu_2_event" : crée une image de pour l'événement à partir de l'image du LIEU
// paramètre 1 = événement de destination
// paramètre 2 = Lieu culturel rattaché
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function uploader_pic_lieu_2_event ($id_update,$lieu_culturel)
{		
	global $folder_pics_event ;
	global $w_absolue ;
	global $w_vi_absolue ;
	global $table_evenements_agenda ;

	$fichier_source = '../vignettes_lieux_culturels/pic_fiche_lieu_' . $lieu_culturel . '_1.jpg' ;
	//echo '<img src="../vignettes_lieux_culturels/pic_fiche_lieu_18_1.jpg">' ;

 	/*88888	$destination = '../pics_events_test/event_' . $id_update . '_1.jpg' ; // 888888 pour Folder Test
	$destination_vi = '../pics_events_test/vi_event_' . $id_update . '_1.jpg' ; // 88888 pour Folder Test */

	$destination = '../' . $folder_pics_event . 'event_' . $id_update . '_1.jpg' ; // Chemin & nom de image de destination
/*
	$destination_vi = '../' . $folder_pics_event . 'vi_event_' . $id_update . '_1.jpg' ; // Chemin & nom de Vignette
	$destination_micro = '../' . $folder_pics_event . 'micro_event_' . $id_update . '_1.jpg' ; //--- richir
*/
	$error_info = ''; // RAZ de la var qui contiendra les messages d'erreur
	$debug_concat =  '';

	//------------------------------------------------------------------------
	// Aucune erreur => copier le fichier dans le répertoire de destination
	//------------------------------------------------------------------------
	//Tester si l'image n'est pas déja présente sur le serveur
	if (file_exists('../' . $folder_pics_event . 'event_' . $id_update . '_1.jpg'))
	{
		echo 'CETTE IMAGE EXISTE DEJA sur le serveur!!! ' ;
	}
	else
	{
		$uploaded_pic = imagecreatefromjpeg($fichier_source); // = photo uploadée 

		chmod ($fichier_source, 0644); // Pour que l'image ait un CHMOD 644 et non 600 
		// (ce qui empêcherait la sauvegarde FTP des images)
		$debug_concat.=  ' <br />- <b>Transfert image ' . $id_update . ' </b>: OK';
		echo ' <br />- <b>Transfert image ' . $id_update . ' </b>: OK' ;
		
		// Largeur et hauteur initiales
		$largeur_uploaded = imagesx($uploaded_pic);
		$hauteur_uploaded = imagesy($uploaded_pic);
		$debug_concat.=  '<br />- <b>Largeur initiale</b>: '.$largeur_uploaded. '<br />	- 
		<b>hauteur initiale</b>: '.$hauteur_uploaded ;

		if ($largeur_uploaded == $w_absolue) // Largeur OK
		{	
			$debug_concat.=  '<br />- <b>Largeur strictement OK</b>)';
			$new_H = $hauteur_uploaded ;
		}
		
		elseif ($largeur_uploaded >= $w_absolue) // Largeur suffisante. L'image va être redimensionnée
		{	
			$debug_concat.=  '<br />- <b>Largeur suffisante. L\'image va être redimensionnée</b>';
			
			// On recalcule la Hauteur proportionnellement
			$new_H = round($hauteur_uploaded * $w_absolue / $largeur_uploaded);
			$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
		}
	
		// Redimensionner et ré-enregistrer dans le répertoire
		$resample = imagecreatetruecolor($w_absolue, $new_H); // Création image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $w_absolue, $new_H, $largeur_uploaded, $hauteur_uploaded);
		imagejpeg($resample, $destination, '90');// Enregistrer la miniature sous le nom	
							
/*
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// Recalcul de la taille de la vignette
		// Les dimensions sont contraintes en W et en H (comme auparavant)
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		$debug_concat.= '<br />----------------- VIGNETTE ------------------' ;
		if ($largeur_uploaded<=$w_vi_absolue)
		{	
			$debug_concat.=  '<br />- <b>Dimensions Vignette OK</b>';
			$new_W_Vignette = $largeur_uploaded ;
			$new_H_Vignette = $hauteur_uploaded ;
		}
		else
		{
			$debug_concat.=  '<br />-<b> ! La Vignette va être redimensionnée  ! </b>';
			// W > maximum
			if ($largeur_uploaded>$w_vi_absolue)
			{
				$new_W_Vignette = $w_vi_absolue;
				// On recalcule la Hauteur proportionnellement
				$new_H_Vignette = round($hauteur_uploaded * $w_vi_absolue / $largeur_uploaded);
				$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
				$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
			}
		}
		$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W_Vignette, $new_H_Vignette, $largeur_uploaded, $hauteur_uploaded);
		imagejpeg($resample, $destination_vi, '90');// Enregistrer la miniature sous le nom
		chmod ($destination_vi, 0644); // Pour que l'image ait un CHMOD 644 et non 600 


		//---------- richir : vignette micro supplémentaire pour iphone / début ----------
		$new_W_Vignette = 60; $new_H_Vignette = 60;
		$rapport = $new_W_Vignette / $new_H_Vignette;

		if ($largeur_uploaded / $hauteur_uploaded < $rapport) {
			$wsrc = $largeur_uploaded;
			$hsrc = $largeur_uploaded / $rapport;
			$xsrc = 0;
			$ysrc = round(($hauteur_uploaded - $hsrc) / 4);
		}
		else {
			$wsrc = $hauteur_uploaded * $rapport;
			$hsrc = $hauteur_uploaded;
			$xsrc = round(($largeur_uploaded - $wsrc) / 2);
			$ysrc = 0;
		}
		$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, $xsrc, $ysrc, $new_W_Vignette, $new_H_Vignette, $wsrc, $hsrc);
		if (file_exists($destination_micro))
			@unlink($destination_micro);
		imagejpeg($resample, $destination_micro, 90);// Enregistrer la miniature sous le nom
		chmod($destination_micro, 0644); // Pour que l'image ait un CHMOD 644 et non 600 
		//---------- richir : vignette micro supplémentaire pour iphone / fin ----------


		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV Fin vignette
*/
		// -----------------------------------------------------
		// Mettre le FLAG de la TABLE à SET
		$image_db = 'pic_event_1' ;
		mysql_query("UPDATE $table_evenements_agenda SET $image_db = 'set' WHERE id_event = '$id_update' LIMIT 1 ");
	}
$debug_concat.=  '<br />';
// echo $debug_concat ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


##################################################################################################
######   Clubplasma
##################################################################################################
$correspondance_ville = array ( "1" => "be3", "2" => "be5", "4" => "be4", "5" => "be1", "6" => "be7", "7" => "be2", "8" => "be11", "9" => "be1" ) ;

$genre_event_rss = 'g03'; // Catégorie-genre de l'événement

// $adresse_flux = 'flux_3.xml' ;
$adresse_flux = 'http://www.clubplasma.be/rss/agenda.xml' ;

echo '<div class="titre_1_flux">Traitement du flux "' . $adresse_flux . '"</div>'; //titre


// Ouvrir une socket de connexion Internet ou Unix (fsockopen)
$flux = ''; $errno = 0; $errstr = '';
if ($fp = @fsockopen('www.clubplasma.be', 80, $errno, $errstr, 10))
{
	fputs($fp, 'GET /rss/agenda.xml HTTP/1.0'."\r\n".'HOST: www.clubplasma.be '."\r\n"."Connection: close\r\n\r\n"); // Requête
	while (! feof($fp)) // Réponse
	$flux .= fgets($fp, 4096);
	fclose($fp);
}
else
{
	echo '--- "erreur n3" : Connexion impossible : '. $errno . ' : ' . $errstr ;
	signaler_erreur($lieu_rss,$adresse_flux, 'erreur n3') ;
}
unset($fp, $errno, $errstr);


// Appel de la fonction de lecture du flux
$array_rss = lire_rss($flux,array("title","link","pubDate","description",)); 

				/*echo '<pre>';
				print_r($array_rss);
				echo '</pre>';*/

if (isset ($array_rss) AND $array_rss != NULL)
{

	foreach($array_rss as $tab)
	{
		if (isset ($tab[0]) AND $tab[0] != NULL	AND isset ($tab[1]) AND $tab[1] != NULL	AND isset ($tab[2]) AND $tab[2] != NULL
		AND isset ($tab[3]) AND $tab[3] != NULL	AND isset ($tab[4]) AND $tab[4] != NULL	)
		{
			//---------------------------------
			// Conversion de format
			//---------------------------------
			// Numéro de l'ITEM dans le CHANNEL
			$rss_item_nr = $tab[0] ;
			
			// <title> = Nom de l'événement 
			$rss_item_title = $tab[1] ;
			//$rss_item_title = utf8_decode ($rss_item_title) ;
			$rss_item_title = htmlentities($rss_item_title, ENT_QUOTES);
			$rss_item_title = substr($rss_item_title, 12);
						
			// <link> = Lien
			$rss_item_link = $tab[2] ;
	
		
			// A quel LIEU l'événement est-il lié ? Chercher la chaine de type "logos/4.jpg" dans la DESCRIPTION
			$rss_quel_lieu = $tab[4] ;
			$motif= '!logos/(.+).jpg!' ; 
			preg_match_all($motif,$rss_quel_lieu,$numero_lieu);
			//echo $numero_lieu['1']['0'];
			$correspondance_lieu = array ( "1" => "51", "2" => "52", "4" => "53", "5" => "54", "6" => "55", "7" => "56", "8" => "57", "9" => "58" ) ;
			$lieu_rss = $correspondance_lieu[$numero_lieu['1']['0']];
			//echo $correspondance_lieu[$numero_lieu['1']['0']];



			/* <periode:debut> = Date de DEBUT de l'événement. 
			PS : on pourrait employer le champ "pubDate", mais le format est + complqué à convertir...*/
			$rss_item_periode_debut_initial = substr($tab[1], 0, 10) ; // extraire la date du titre
			$rss_item_periode_debut = substr($tab[1], 6, 4) . '-' . substr($tab[1], 3, 2) . '-' . substr($tab[1], 0, 2);

			//<periode:fin> (Date de FIN de l'événement = date début, donc, 1 jour ACTIF)
			$rss_item_periode_fin = $rss_item_periode_debut ;
			
			
			// Ville de l'événement :
			$ville_event_rss = $correspondance_ville[$numero_lieu['1']['0']];
		
			
			// <description> = Description COMPLETE de l'événement : 

$correspondance_description = array (
'51' => 'Concert au Soundstation, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter Soundstation en formant le 04/232.13.21 ou par email : info@soundstation.be',


'52' => 'Concert à l\'Atelier Rock, qui fait partie du &quot;Club Plasma&quot;, notre partenaire.
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter l\'Atelier Rock en formant le 085/25.03.59 ou par email : info@atelierrock.be', 


'53' => 'Concert à l\'Entrepôt, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter l\'Entrepôt en formant le 063/45.60.84 ou par email : info@losange.net', 


'54' => 'Concert au Recyclart, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter Recyclart en formant le 02/502.57.34 ou par email : info@recyclart.be', 


'55' => 'Concert au Coliseum, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter Coliseum en formant le 0900/84100 ou par email : info@coliseum.be', 


'56' => 'Concert organisé par Panama, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter Panama en formant le 0495/247.359 ou par email : info@panama.mu', 


'57' => 'Concert organisé par Forward Agency, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter Forward Agency en formant le 0498/058.108 ou par email : yannick.seutin@skynet.be', 


'58' => 'Concert au Magasin 4, qui fait partie du &quot;Club Plasma&quot;, notre partenaire. 
Les concerts sont généralement à 20h. Pour plus d\'informations sur le concert <em>' . $rss_item_title . '</em>, vous pouvez contacter Magasin 4 en formant le 02/223.34.74 ou par email : info@magasin4.be' 
) ;


// Texte statique pour la description courte (= résumé affiché dans la liste des résultats de l'agenda)
/*$correspondance_description_courte = array (
"51" => "Soundstation",
"52" => "L&rsquo;Atelier Rock de Huy", 
"53" => "L\'entrep&ocirc;t, dans le Sud Luxembourg", 
"54" => "L\'asbl Recyclart, Bruxelles.", 
"55" => "Le Coliseum de CHARLEROI", 
"56" => "L\'asbl Panama de Namur", 
"57" => "L\'asbl Forward au Th&eacute;&acirc;tre royal de Mons", 
"58" => "Le Magasin 4, Bruxelles" 
) ;*/



		
			
			$rss_item_description = addslashes ($correspondance_description[$lieu_rss]) ;

			// <description> = Description COURTE de l'événement : 
			//$rss_item_description_courte = $correspondance_description_courte[$lieu_rss] ;
			$rss_item_description_courte = addslashes ($correspondance_description[$lieu_rss]) ; //C'est également la description longue


			//-------------------------------------------------------------------------------------------------------------
			// Appel de la fonction d'enregistrement de l'ITEM dans la DB (après avoir testé si l'ITEM n'y est pas déjà)
			//-------------------------------------------------------------------------------------------------------------
			$resultat_rec_db = rss_test_et_ecrire_db ($lieu_rss, $rss_item_nr, $rss_item_title, $rss_item_link, $rss_item_description,$rss_item_description_courte, $rss_item_periode_debut, $rss_item_periode_fin, $ville_event_rss, $genre_event_rss) ;
			// Paramètre retourné = ID due l'événement créé par fonction. Si == nombre entier, appeler fct pour recopier pics
			if (is_numeric("$resultat_rec_db"))
			{
				uploader_pic_lieu_2_event ($resultat_rec_db, $lieu_rss) ;
			}	
		}
		else { signaler_erreur($lieu_rss,$adresse_flux, 'erreur n1') ; }
	}		
}
else { signaler_erreur($lieu_rss, $adresse_flux, 'erreur n2') ; }

####### Fin flux CLUB PLASMA######################################################################


/*
##################################################################################################
######   ICI Un autre flux
##################################################################################################
*/



##################################################################################################


echo '<hr><div align="center">---- [fin] ----</div><hr>' ;
?>

</body>
</html>
