<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Recopier les donn&eacute;es d'un &eacute;v&eacute;nements vers un concours</title>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.colore {color: #00A9AA}
-->
</style>

</head>

<body>
<div id="head_admin_agenda"></div>

<h1>Recopier les données d'un événements vers un concours</h1>

<div class="menu_back">
<a href="index_admin.php">Menu Admin</a>
</div>

<p class="mini">Quand j'ajoute un concours, j'ai la possibilit&eacute; <strong>d'indiquer l'ID</strong> de l'event li&eacute;. Je  voudrais que si je fais cela, je puisse pousser sur un <strong>bouton &quot;lier&quot;</strong> qui aille rechercher la <strong>premi&egrave;re photo</strong>, la <strong>description courte</strong> surplomb&eacute;e d'une <strong>phrase dynamique &quot;XXXXNom du Lieu: Gagnez des places!&quot;</strong>,  qui compl&egrave;te <strong>l'adresse du lieu de  l'event</strong>, le <strong>titre de l'event</strong>,  l'adresse de la <strong>personne de contact</strong>,  &nbsp;et que je puisse ensuite compl&eacute;t&eacute; le formulaire pour le reste des champs:  les dates, le nbre de places, la date de tirage.
<p class="mini">L'ev&eacute;nement DLP li&eacute; est aussi recopi&eacute;<br />
<p>


<?php

require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Prendre image de l'événement et créer image pour concours + vignette
function recopier_image ($id_update,$num_pic, $event_lie)
{		
	require '../inc_db_connect.php';
	require '../inc_var.php';
	
	$fichier_source = '../pics_events/event_' . $event_lie . '_1.jpg' ;
	//$fichier_source = 'source_pic_' . $num_pic ;
	
	$taille_max = 3000000 ;
	$taille_min = 2 ;
	
	$destination = '../' . $folder_vignettes_concours . 'conc_' . $id_update . '_' . $num_pic . '.jpg' ; // Chemin & nom de image de destination
	$destination_vi = '../' . $folder_vignettes_concours . 'vi_conc_' . $id_update . '_' . $num_pic . '.jpg' ; // Chemin & nom de Vignette

	$error_info = ''; // RAZ de la var qui contiendra les messages d'erreur
	$debug_concat =  '<div class="mini_info">IMAGE TRAITEE = ' . $num_pic . '<br />_____________________';


	//------------------------------------------------------------------------
	// copier le fichier dans le répertoire de destination
	//------------------------------------------------------------------------
	{
		$debug_concat.=  ' <br />- <b>Transfert image ' .$num_pic.'</b>: OK';
		
		// Largeur et hauteur initiales
		$uploaded_pic = imagecreatefromjpeg($fichier_source); // = photo uploadée 
		$largeur_uploaded = imagesx($uploaded_pic);
		$hauteur_uploaded = imagesy($uploaded_pic);
		$debug_concat.=  '<br />- <b>Largeur initiale</b>: '.$largeur_uploaded. '<br />	- 
		<b>hauteur initiale</b>: '.$hauteur_uploaded ;
	
		if ($largeur_uploaded<$maxWidth_conc_pics AND $hauteur_uploaded<$maxHeight_conc_pics) // H > maximum et W > maximum. On cherche la plus grande valeur ==> Inutile de redimentionner l'image
		{	
			$debug_concat.=  '<br />- <b>Dimensions OK</b>)';
			imagejpeg($uploaded_pic, $destination, '90');// Enregistrer la miniature sous le nom	
			chmod ($destination, 0644); // Pour que l'image ait un CHMOD 644 et non 600 
		}
		else  // H > maximum ET/OU W > maximum ==> Il faut redimentionner l'image
		{
			$debug_concat.=  '<br />- <b>! L\'image va être redimensionnée  ! </b>)';
			
			if ($largeur_uploaded>$maxWidth_conc_pics AND $hauteur_uploaded>$maxHeight_conc_pics) // H > maximum et W > maximum. On cherche la plus grande valeur
			{
				// La hauteur est plus grande
				if ($hauteur_uploaded>$largeur_uploaded) 
				{
					$debug_concat.=  '<br />- Resamp:::1:::';
					$new_H = $maxHeight_conc_pics;
					// On recalcule la Largeur proportionnellement
					$new_W = round($largeur_uploaded * $maxHeight_conc_pics / $hauteur_uploaded);
					$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
					$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
				}
				else // La largeur est plus grande
				{
					$debug_concat.=  '<br />- Resamp:::2:::';
					$new_W = $maxWidth_conc_pics;
					// On recalcule la Hauteur proportionnellement
					$new_H = round($hauteur_uploaded * $maxWidth_conc_pics / $largeur_uploaded);
					$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
					$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
				}
			}
			else if ($largeur_uploaded<$maxWidth_conc_pics AND $hauteur_uploaded>$maxHeight_conc_pics) // H > maximum et W < maximum. On cherche la plus grande valeur
	
			{
				$debug_concat.=  '<br />- Resamp:::3:::';
				$new_H = $maxHeight_conc_pics;
				// On recalcule la Largeur proportionnellement
				$new_W = round($largeur_uploaded * $maxHeight_conc_pics / $hauteur_uploaded);
				$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
				$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
			}
			else if ($largeur_uploaded>$maxWidth_conc_pics AND $hauteur_uploaded<$maxHeight_conc_pics) // H < maximum et W > maximum. On cherche la plus grande valeur
			{ 
				$debug_concat.=  '<br />- Resamp:::4:::';
				$new_W = $maxWidth_conc_pics;
				// On recalcule la Hauteur proportionnellement
				$new_H = round($hauteur_uploaded * $maxWidth_conc_pics / $largeur_uploaded);
				$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
				$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
			}
	
			// Redimensionner et ré-enregistrer dans le répertoire
			$resample = imagecreatetruecolor($new_W, $new_H); // Création image vide
			imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W, $new_H, $largeur_uploaded, $hauteur_uploaded);
			imagejpeg($resample, $destination, '90');// Enregistrer la miniature sous le nom	
		}
	
	
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
		// Recalcul de la taille de la vignette :			
		$debug_concat.= '<br />----------------- VIGNETTE ------------------' ;
		if ($largeur_uploaded<$maxWidth_conc_vignette AND $hauteur_uploaded<$maxHeight_conc_vignette)
		{	
			$debug_concat.=  '<br />- <b>Dimensions Vignette OK</b>';
			$new_W_Vignette = $largeur_uploaded ;
			$new_H_Vignette = $hauteur_uploaded ;
		}
		else
		{
			$debug_concat.=  '<br />-<b> ! La Vignette va être redimensionnée  ! </b>';
			
			// H > maximum et W > maximum. On cherche la plus grande valeur
			if ($largeur_uploaded>$maxWidth_conc_vignette AND $hauteur_uploaded>$maxHeight_conc_vignette)
			{
				if ($hauteur_uploaded>$largeur_uploaded) 
				{
					$debug_concat.=  '<br />- Resamp::: V 1:::';
					// La hauteur est plus grande
					$new_H_Vignette = $maxHeight_conc_vignette;
					// On recalcule la Largeur proportionnellement
					$new_W_Vignette = round($largeur_uploaded * $maxHeight_conc_vignette / $hauteur_uploaded);
					$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
					$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
					
				}
				else
				{
					$debug_concat.=  '<br />- Resamp::: V 2:::';
					// La largeur est plus grande
					$new_W_Vignette = $maxWidth_conc_vignette;
					// On recalcule la Hauteur proportionnellement
					$new_H_Vignette = round($hauteur_uploaded * $maxWidth_conc_vignette / $largeur_uploaded);
					$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
					$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
				}
			}
			else if ($largeur_uploaded<$maxWidth_conc_vignette AND $hauteur_uploaded>$maxHeight_conc_vignette)
			{
				$debug_concat.=  '<br />- Resamp::: V 3:::';
				$new_H_Vignette = $maxHeight_conc_vignette;
				// On recalcule la Largeur proportionnellement
				$new_W_Vignette = round($largeur_uploaded * $maxHeight_conc_vignette / $hauteur_uploaded);
					$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
					$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
			}
			else if ($largeur_uploaded>$maxWidth_conc_vignette AND $hauteur_uploaded<$maxHeight_conc_vignette)
			{
				$debug_concat.=  '<br />- Resamp::: V 4:::';
				$new_W_Vignette = $maxWidth_conc_vignette;
				// On recalcule la Hauteur proportionnellement
				$new_H_Vignette = round($hauteur_uploaded * $maxWidth_conc_vignette / $largeur_uploaded);
				$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
				$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ; 
			}
		}
		$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W_Vignette, $new_H_Vignette, $largeur_uploaded, $hauteur_uploaded);
		imagejpeg($resample, $destination_vi, '100');// Enregistrer la miniature sous le nom
	
		// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV Fin vignette
	
		// -----------------------------------------------------
		// Mettre le FLAG de la TABLE à SET
		/*$image_db = 'pic_event_' .  $num_pic ;
		mysql_query("UPDATE $table_evenements_agenda SET $image_db = 'set' WHERE id_event = '$id_update' LIMIT 1 ");*/
		mysql_query("UPDATE `$table_ag_conc_fiches` SET `pic_conc` = 'set' WHERE `id_conc` = '$id_update' LIMIT 1 ");
	
		$debug_concat.=  '</div> <br />';
		// echo $debug_concat ;
	}
}
	
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
		
// ----------------------------------------------------
// Récupération des valeurs POST et des données utiles
// ----------------------------------------------------
if (isset($_GET['event_correspondant']) AND preg_match('/[0-9]$/', $_GET['event_correspondant']) AND isset($_GET['concours_modifie']) AND preg_match('/[0-9]$/', $_GET['concours_modifie']))
{
	$event_correspondant = htmlentities($_GET['event_correspondant'], ENT_QUOTES) ;
	$id_concours = htmlentities($_GET['concours_modifie'], ENT_QUOTES) ;
	

	// Récupération des valeurs liées à l'événement correspondant
	$reponse_event = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$event_correspondant'");
	$donnees_event = mysql_fetch_array($reponse_event);

	$lieu_event = $donnees_event['lieu_event'] ;
	$nom_event = $donnees_event['nom_event'] ;
	$resume_event = $donnees_event['resume_event'] ;
	$pic_event_1 = $donnees_event['pic_event_1'] ;
	$email_reservation = $donnees_event['email_reservation'];

	// Récupération des valeurs liées au LIEU de l'événement
	$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$lieu_event'");
	$donnees_lieu = mysql_fetch_array($reponse_lieu);

	$nom_lieu = $donnees_lieu['nom_lieu'] ;
	$e_mail_lieu = $donnees_lieu['e_mail_lieu'] ;
	$adresse_lieu = $donnees_lieu['adresse_lieu'] ;


	// Récupération des valeurs liées au CONCOURS
	/*
	$reponse_concours = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE id_conc = '$lieu_event'");
	$donnees_concours = mysql_fetch_array($reponse_concours);
*/

	echo '<br />
	<br />
	<br />
	<h3 align="center">Vous allez recopier les infos de l\'événement ' . $nom_event . ' (id ' . $event_correspondant .')</h3>' ;


	// Si on a appuyé sur le bouton ENREGISTRER
	if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Recopier les données'))
	{
		// ---------------------------------------------------------------------------
		// Recopier les données
		// ---------------------------------------------------------------------------
		
		// Recopier première photo
		if (isset($pic_event_1) AND $pic_event_1 =='set')
		{
			$num_pic = '1' ; // correspond à l'extension du nom du futur fichier JPEG uploadé
			recopier_image ($id_concours,$num_pic, $event_correspondant);	// Upload et construction vignette
		}
		else
		{
			echo '<div class="alerte">Cet événement n\'a pas d\'image. Il faut en choisir une et la mettre manuellement</div>';
		}


		// ---------------------------------------------------------------------------
		// titre de l'event
		// ---------------------------------------------------------------------------
		$nom_event_conc = addslashes($nom_event) ;
		echo '<br /><strong>Titre de l\'événement : </strong>' . stripslashes($nom_event_conc) . '<br />' ; 

		
		// ---------------------------------------------------------------------------
		// Description courte surplombée de"XXXXNom du Lieu: Gagnez des places!"
		// ---------------------------------------------------------------------------
		$concat_description = addslashes($nom_lieu) . ' : Gagnez des places ! <br /> <br />' . 
		addslashes($resume_event) ;
		echo '<br /><strong>Description courte de l\'événement : </strong>' . stripslashes($concat_description) . '<br />' ; 

		
		// ---------------------------------------------------------------------------
		// Adresse du lieu de l'event
		// ---------------------------------------------------------------------------
		$adresse_conc = addslashes($adresse_lieu) ;
		echo '<br /><strong>Adresse du LIEU : </strong>' . stripslashes($adresse_conc) . '<br />' ; 


		// ---------------------------------------------------------------------------
		// Email de la personne de contact
		// ---------------------------------------------------------------------------
		$contact_lieu = $e_mail_lieu ;
		echo '<br /><strong>Email de la personne de contact : </strong>' . $contact_lieu . '<br />' ; 





		// Recopiage des données en DB

		$reussi_1 = mysql_query("UPDATE $table_ag_conc_fiches SET
		description_conc = '$concat_description',
		adresse_conc = '$adresse_conc',
		nom_event_conc = '$nom_event_conc',
		event_dlp_conc = '$event_correspondant',
		mail_lieu_conc = '$e_mail_lieu'
		WHERE id_conc = '$id_concours' LIMIT 1 ") or die('<div class="alerte">Erreur écriture 1 : ' . mysql_error() . '</div>');
		
		if($reussi_1)
		{
			echo '<div class="info">Transfert OK</div>' ;
		}


	}
	else
	{
		echo
		'<div align="center">
			<form action="" method="post">
				<input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Recopier les données">
			</form>
		</div>' ;
	}
}


?>

		
<div align="center"><p><strong><a href="conc_2_edit_a.php?id_conc=<?php echo $id_concours ?>">Retour</a></strong></p></div>

	
	
