<?php
exit(); //--- fichier inutilis�

ini_set("max_execution_time", "780");

require '../inc_fct_base.php';
require '../inc_db_connect.php';
require '../logs/fct_logs.php';
require '../inc_var.php';

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
			<p>R�f�rence du lieu culturel : ' . $lieu_rss . '</p>
			<p>Adresse du flux : ' . $adresse_flux . '</p>
			<p>Date : le ' . date("d-m-Y � H-i") . '</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<div class="pied_mail">www.demandezleprogramme.be</div>
			</div></td>
		  </tr>
		</table>
		<p>&nbsp; </p>
		</body></html>'; 

		$entete = "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
	 mail($email_admin_site,$sujet,$corps,$entete,$email_retour_erreur);
		//echo $corps ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Rechercher les donn�es d'un LIEU culturel via son ID
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function cherche_data_lieu ($id_corresondant)
{
	$reponse_lieu = mysql_query("SELECT * FROM ag_lieux WHERE id_lieu = $id_corresondant");
	$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
	return $donnees_lieu ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'enregistrement de l'ITEM dans la DB (ssi l'entr�e n'y est pas pr�sente)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function rss_test_et_ecrire_db($lieu_rss, $rss_item_nr, $rss_item_title, $rss_item_link, $rss_item_description, $rss_item_description_courte, $rss_item_periode_debut, $rss_item_periode_fin, $ville_event_rss, $genre_event_rss, $rss_item_periode=null)
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
	{ // Cet ITEM est d�j� enregistr� dans la DB de DLP
		$message_traitement_item.=  '<div class="info_rss">Cet ITEM est d�j� enregistr� dans la DB de DLP</div>' ;
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
		// ! Mod�le simplifi� pour l'instant : 1 jour ACTIF, qui est le jour de d�but
		if (is_null($rss_item_periode)) {
			$rss_item_jours_actifs = $rss_item_periode_debut ;		
		} else {
			$rss_item_jours_actifs = $rss_item_periode;
		}
		
		
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
			// Notifier la cr�ation dans le rapport + e-mail
			$nouvel_id_table_evenements_agenda = mysql_insert_id() ; // Dernirer ID cr��
			log_write ($lieu_rss, '4', $nouvel_id_table_evenements_agenda, 'Cr�ation nouvel �v�nement via RSS', 'send_mail') ; 
			//($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
	
			$message_traitement_item.=  '<div class="info_rss">Nouvelle entr�e cr��e dans la DB : ' . 
			$nouvel_id_table_evenements_agenda . '</div>' ;
			
			// Noter dans la TABLE "ag_rss" que cet ITEM est enregistr� sur DLP
			mysql_query("INSERT INTO `ag_rss` ( `id_rss`, `lieu_rss`, `unique_rss`) 
			VALUES ('', '$lieu_rss', '$rss_item_link')") or die(mysql_error());
			
		}
		else 
		{ 
			$message_traitement_item.=  '<div class="alert_rss">Erreur ! Les donn�es n\'ont pas �t� enregistr�es !<br />
			Erreur : <br /> <br />' . mysql_error() . ' <br /> <br /> </div>' ; 

			$error_2_rapport = 'Erreur lors de modification �v�nement. Requ�te = ' . urlencode(mysql_error()) ;
			log_write ($lieu_rss, '4', '0', $error_2_rapport, 'send_mail') ; //($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
		}		
	}
			
	// *******************************************
	// Affichage pour visualisation sur la page 
	// *******************************************
	echo '<div class="bloc_item"><div class="bloc_item_title">
	<a name="item_n_' . $rss_item_nr . '" id="item_n_' . $rss_item_nr . '"></a>' .
	$donnees_lieu['nom_lieu'] . ' (ID' . $donnees_lieu['id_lieu'] . ')<br />(' . $rss_item_nr . ') ' . $rss_item_title . '</div>
	<div align="right"><strong>Du ' . $rss_item_periode_debut . ' � ' . $rss_item_periode_fin . ' </strong></div> <br />
	<strong>R�sum� : </strong><em>'. stripslashes($rss_item_description_courte) . '</em> <br /> <br />
	<strong>Description compl�te : </strong>' . stripslashes($rss_item_description) . '<br />
	<a href="' . $rss_item_link . '">' . $rss_item_link . '</a><br />' . 
	$message_traitement_item . '</div>' ;
	
	return $nouvel_id_table_evenements_agenda ; // Valeur de cette fonction qui est retourn�e pour signaler que OK
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction "uploader_pic_lieu_2_event" : cr�e une image de pour l'�v�nement � partir de l'image du LIEU
// param�tre 1 = �v�nement de destination
// param�tre 2 = Lieu culturel rattach�
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
	$destination_vi = '../' . $folder_pics_event . 'vi_event_' . $id_update . '_1.jpg' ; // Chemin & nom de Vignette
	$destination_micro = '../' . $folder_pics_event . 'micro_event_' . $id_update . '_1.jpg' ; //--- richir
	$error_info = ''; // RAZ de la var qui contiendra les messages d'erreur
	$debug_concat =  '';

	//------------------------------------------------------------------------
	// Aucune erreur => copier le fichier dans le r�pertoire de destination
	//------------------------------------------------------------------------
	//Tester si l'image n'est pas d�ja pr�sente sur le serveur
	if (file_exists('../' . $folder_pics_event . 'event_' . $id_update . '_1.jpg'))
	{
		echo 'CETTE IMAGE EXISTE DEJA sur le serveur!!! ' ;
	}
	else
	{
		$uploaded_pic = imagecreatefromjpeg($fichier_source); // = photo upload�e 

		chmod ($fichier_source, 0644); // Pour que l'image ait un CHMOD 644 et non 600 
		// (ce qui emp�cherait la sauvegarde FTP des images)
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
		
		elseif ($largeur_uploaded >= $w_absolue) // Largeur suffisante. L'image va �tre redimensionn�e
		{	
			$debug_concat.=  '<br />- <b>Largeur suffisante. L\'image va �tre redimensionn�e</b>';
			
			// On recalcule la Hauteur proportionnellement
			$new_H = round($hauteur_uploaded * $w_absolue / $largeur_uploaded);
			$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
		}
	
		// Redimensionner et r�-enregistrer dans le r�pertoire
		$resample = imagecreatetruecolor($w_absolue, $new_H); // Cr�ation image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $w_absolue, $new_H, $largeur_uploaded, $hauteur_uploaded);
		imagejpeg($resample, $destination, '90');// Enregistrer la miniature sous le nom	
							

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
			$debug_concat.=  '<br />-<b> ! La Vignette va �tre redimensionn�e  ! </b>';
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
		$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Cr�ation image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W_Vignette, $new_H_Vignette, $largeur_uploaded, $hauteur_uploaded);
		imagejpeg($resample, $destination_vi, '90');// Enregistrer la miniature sous le nom
		chmod ($destination_vi, 0644); // Pour que l'image ait un CHMOD 644 et non 600 


		/* ---------- richir : vignette micro suppl�mentaire pour iphone / d�but ---------- */
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
		$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Cr�ation image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, $xsrc, $ysrc, $new_W_Vignette, $new_H_Vignette, $wsrc, $hsrc);
		if (file_exists($destination_micro))
			@unlink($destination_micro);
		imagejpeg($resample, $destination_micro, 90);// Enregistrer la miniature sous le nom
		chmod($destination_micro, 0644); // Pour que l'image ait un CHMOD 644 et non 600 
		/* ---------- richir : vignette micro suppl�mentaire pour iphone / fin ---------- */



		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV Fin vignette

		// -----------------------------------------------------
		// Mettre le FLAG de la TABLE � SET
		$image_db = 'pic_event_1' ;
		mysql_query("UPDATE $table_evenements_agenda SET $image_db = 'set' WHERE id_event = '$id_update' LIMIT 1 ");
	}
$debug_concat.=  '<br />';
// echo $debug_concat ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
