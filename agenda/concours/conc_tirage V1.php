<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Tirage du concours</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php 

// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
// Protection rudimentaire de la page
if (isset($_GET['pw']) AND ($_GET['pw'] != NULL))
{
	$pw = htmlentities($_GET['pw'], ENT_QUOTES);
	if ($pw == 's5fah7r6s3p6ax2')
	{
		$permission = 'ok' ;
	}
	else
	{
		echo '<p align="center"><br /><br /><br /><br /><br />Accès impossible</p>' ;
		exit () ;
	}
}
else
{
	echo '<p align="center"><br /><br /><br /><br /><br />Accès impossible</p>' ;
	exit () ;
}
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS



// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
if (isset($permission) AND $permission == 'ok') ; {
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
?>
<div id="head_admin_agenda"></div>
<h1>Tirage du concours</h1>

<div class="menu_back">
<a href="../admin_agenda/concours_listing.php" >Listing des concours  </a> | 
<a href="../admin_agenda/concours_lire_historique.php">Historique des tirages de concours</a>
</div>


<p>
  <?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require 'conc_emails.php';
require '../inc_var_dist_local.php';

$rapport_complet_concat = '';

// Récupérer les "concours" non traités
$time_actuel = time() ;
$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE cloture_conc < $time_actuel
AND flags_conc LIKE '%actif%' ORDER BY id_conc") 
or print($reponse . " --  -- " . mysql_error());

$donnees_var_exist= mysql_fetch_array($reponse) ;

if (!empty ($donnees_var_exist))
{	
	$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE cloture_conc < $time_actuel
	AND flags_conc LIKE '%actif%' ORDER BY id_conc") ;

	while ($donnees = mysql_fetch_array($reponse))
	{
		// Création diverses variables :
		$liste_infos_gagnants = '' ; //RAZ
		$adresse_event_pour_mail_joueur = $donnees ['adresse_conc'] ; // Pour email pour joueur
		$adresse_conc = $donnees ['adresse_conc'] ;
		$nom_event_conc = $donnees ['nom_event_conc'] ;
		$id_conc = $donnees ['id_conc'] ;
		$rapport_1_concours_rec_db = '';
	
		
		// Pour informer Admin
		$rapport_complet_concat.= '<br /> <br />****************************************************************************<br />
		<strong> ' . $nom_event_conc . '</strong> (Réf:concours ' . $id_conc . ')<br />
		 Adresse : ' . $adresse_conc . '
		<br /> ***************************************************************************<br /> <br />';
		
			
		// Pour sauvegarde dans DB
		$rapport_1_concours_rec_db.= '<br /> <br />======================================================= <br />
		Sauvegarde du rapport du tirage du ' . date('d-m-Y @ H\hi') . '.<br /> 
		Concours intitulé "' . $nom_event_conc . '" (ID ' . $id_conc . ')<br /> 
		Adresse du lieu concerné : ' . $adresse_conc . '
		<br />======================================================= <br /> <br />';
		
	
		// Titre de l'EVENEMENT
		$id_conc = $donnees ['id_conc'] ;
		$nom_event_conc = $donnees ['nom_event_conc'] ; 
		$mail_lieu_conc = $donnees ['mail_lieu_conc'] ; 
		echo '<h2>
		__________________________________________________________________________________________________________<br /> | <br />
		| <b>'.$donnees ['nom_event_conc'] . ' - id '. $id_conc . ' - </b> <br />
		|__________________________________________________________________________________________________________</h2>';
	
		
		// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
		// Pour CHAQUE LOT de ce concours
		// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
	
		$var_lot_unserialized = unserialize($donnees['lots_conc']) ;
		$i_lot = 0; // sera incrémenté dans la boucle
		
		foreach ($var_lot_unserialized as $element_lot)
		{
			echo '<br /> <br /> <div class="titre_turquoise" align="center"> :::::::::::::: Lot ' . $i_lot . ' :::::::::::::: <b>'
			 . str_pad($element_lot['nombre_places'], 3, "0", STR_PAD_LEFT) . '</b> places ';
			echo 'le ' . substr($element_lot['new_date_lot'], 8, 2) . '-' . 
			substr($element_lot['new_date_lot'], 5, 2) . '-' . 
			substr($element_lot['new_date_lot'], 0, 4) . ' à ';
			echo substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
			substr($element_lot['new_heure_lot'], 3, 2) . ' pour le groupe "';
			echo $groupes_joueurs[$element_lot['groupe_joueur']] . '"</div>' ;
	
							
			// Données pour le LIEU culturel
			$liste_infos_gagnants.= '<br /> <br /><div class="email_style_rubriques">Places pour le ' . 
			substr($element_lot['new_date_lot'], 8, 2) . '-' . 
			substr($element_lot['new_date_lot'], 5, 2) . '-' . 
			substr($element_lot['new_date_lot'], 0, 4) . ' à ' . 
			substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
			substr($element_lot['new_heure_lot'], 3, 2) . ' <br />Lieu de l\'événement : '. $adresse_conc . '</div> ';
	
	
			// Données pour e-mail joueurs
			$nom_event_pour_mail_joueur = $donnees ['nom_event_conc'] ;
			
			$date_event_pour_mail_joueur = 'le ' . 
			substr($element_lot['new_date_lot'], 8, 2) . '-' . 
			substr($element_lot['new_date_lot'], 5, 2) . '-' . 
			substr($element_lot['new_date_lot'], 0, 4) . ' à ' . 
			substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
			substr($element_lot['new_heure_lot'], 3, 2)  ;
	
	
			// Données pour l'Admin
			$rapport_complet_concat.= 'Places pour le <strong>' . 
			substr($element_lot['new_date_lot'], 8, 2) . '-' . 
			substr($element_lot['new_date_lot'], 5, 2) . '-' . 
			substr($element_lot['new_date_lot'], 0, 4) . ' à ' . 
			substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
			substr($element_lot['new_heure_lot'], 3, 2) . ' </strong> [Lot ' . $i_lot . '] (groupe "' .
			$groupes_joueurs[$element_lot['groupe_joueur']] . '")<br />';
	
	
			// Pour sauvegarde dans DB
			$rapport_1_concours_rec_db.= '<br /> ______ Places pour le ' . 
			substr($element_lot['new_date_lot'], 8, 2) . '-' . 
			substr($element_lot['new_date_lot'], 5, 2) . '-' . 
			substr($element_lot['new_date_lot'], 0, 4) . ' @ ' . 
			substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
			substr($element_lot['new_heure_lot'], 3, 2) . ' [Lot ' . $i_lot . '] (groupe "' .
			$groupes_joueurs[$element_lot['groupe_joueur']] . '") ';
			
	
			// **********************************************************************************
			// Traitement de la liste des joueurs pour ce lot
			// **********************************************************************************
			$liste_joueurs_lot_en_cours = array() ;
			$reponse_joueurs = mysql_query("SELECT * FROM $table_ag_conc_joueur 
			WHERE id_fiche_conc_joueur = '$id_conc' AND  lot_conc_joueur = '$i_lot'") or die (mysql_error());
			while ($donnees_joueurs = mysql_fetch_array($reponse_joueurs))
			{
				// Constituer un Array pour ce LOT
				array_push ($liste_joueurs_lot_en_cours, $donnees_joueurs['id_conc_joueur']);
			}
			
			shuffle ($liste_joueurs_lot_en_cours); // Shuffle du tableau des participants
			$nombre_participants = count ($liste_joueurs_lot_en_cours);		
						
			// Données pour le LIEU culturel et Admin
			if ($nombre_participants > 0 )
			{
				echo '';
			}
			else
			{
				$liste_infos_gagnants.= ' ----- Aucun gagnant ----- <br />' ;
				$rapport_complet_concat.= '<br /><em> - Aucun gagnant </em><br />' ;
				$rapport_1_concours_rec_db.= '<br /> Aucun gagnant <br />' ;
			}
			
	
			// S'il y a des gagnants pour ce LOT, répartir les LOTS entre gagnants :
			$nb_places_gagnees = '' ;
			if ($liste_joueurs_lot_en_cours != NULL)
			{
				$nombre_places_dispo = $element_lot['nombre_places'] ;
				
				// ++++++  Il y a PLUS de participants que de places disponibles  ++++++ 
				if ($nombre_places_dispo < $nombre_participants)
				{ 
					echo '<br />[option 1] <strong>Il y a ' . $nombre_participants . ' participant(s). 
					Toutes les places (' . $nombre_places_dispo . ') ont été remportées</strong>' ;
					$nb_places_gagnees = $nombre_places_dispo ;
					$nb_perdants = $nombre_participants - $nombre_places_dispo ;
				}
	
				// ++++++   Il y a PLUS de places disponibles que de participants  ++++++ 
				if ($nombre_places_dispo > $nombre_participants)
				{ 
					echo '<br />[option 2] <strong>Il y a ' . $nombre_participants . ' participant(s) 
					et il reste ' . ($nombre_places_dispo - $nombre_participants) . ' places</strong>' ;
					$nb_places_gagnees = $nombre_participants ;
					$nb_perdants = 0 ;
				}
				
				// ++++++   Il y a AUTANT de places disponibles que de participants  ++++++ 
				if ($nombre_places_dispo == $nombre_participants)
				{ 
					echo '<br />[option 3] <strong>Il y a ' . $nombre_participants . ' participant(s). 
					Toutes les places ont été remportées.</strong>' ;
					$nb_places_gagnees = $nombre_participants ;
					$nb_perdants = 0 ;
				}
	
				// Pour info Admin
				$rapport_complet_concat.= '<br />' . $nombre_participants . ' participant(s) 
				pour ' . $nombre_places_dispo . ' place(s) disponible(s)<br />';
	
	
				// Pour sauvegarde dans DB
				$rapport_1_concours_rec_db.= ' - ' . $nombre_participants . ' participant(s) 
				pour ' . $nombre_places_dispo . ' place(s) disponible(s)  ______<br /> <br />';
				
	
				// Liste des gagnants (les premiers de la liste qui a subi le shuffle)
				$id_gagnants = array_slice ($liste_joueurs_lot_en_cours, 0, $nombre_places_dispo); 
				sort ($id_gagnants) ;
				echo '<pre>' ;
				print_r($id_gagnants);
				echo '</pre>' ;	
				
				
				// Données pour le LIEU culturel
				$liste_infos_gagnants.= '<strong> ' . $nb_places_gagnees . ' gagnant(s) : </strong> <br />';
	
				// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
				// Récolte des données de chaque gagnant pour ce lot
				$liste_e_mail_gagnants_lot = array(); // RAZ variables
				foreach ($id_gagnants as $gagnant_en_cours)
				{
					$reponse_info_joueur = mysql_query("SELECT * FROM $table_ag_conc_joueur 
					WHERE id_conc_joueur = '$gagnant_en_cours'");
					$donnees_info_joueur = mysql_fetch_array($reponse_info_joueur);
					
					$id_conc_joueur = $donnees_info_joueur['id_conc_joueur'] ;
					$id_fiche_conc_joueur = $donnees_info_joueur['id_fiche_conc_joueur'] ;
					$lot_conc_joueur = $donnees_info_joueur['lot_conc_joueur'] ;
					$nom_joueur_conc = $donnees_info_joueur['nom_joueur_conc'] ;
					$mail_joueur_conc = $donnees_info_joueur['mail_joueur_conc'] ;
					$time_stamp_joueur = $donnees_info_joueur['time_stamp_joueur'] ;
					$nature_joueur = $donnees_info_joueur['nature_joueur'] ;
					
					// adresses pour envoi aux gagnants
					array_push ($liste_e_mail_gagnants_lot,  $mail_joueur_conc) ;
	
					// Liste des données des gagnants pour attaché de presse
					$liste_infos_gagnants.= '- ' . $nom_joueur_conc . ' :::: ' . $mail_joueur_conc . 
					'  ::::  [ref.' . $id_conc_joueur . ']<br />' ;
	
				}
				// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
	
				// Calcul du nombre de perdants + liste
				if ($nb_perdants > 0 )
				{ 
					echo '<br /><strong>Voici la liste des ' . $nb_perdants . ' perdant(s)</strong>';
					$id_perdants = array_slice ($liste_joueurs_lot_en_cours, $nb_places_gagnees ); 
					sort ($id_perdants) ;
					$nb_places_gagnees = $nombre_places_dispo ;
	
					echo '<pre>' ;
					print_r($id_perdants);
					echo '</pre>' ;	
				}
	
	
				// -------------------------------------------------------------------------------
				// Informer les joueurs
				// Note : les infos du joueur doivent être ressorties de la boucle, contrairement aux infos sur le spectacle
				// -------------------------------------------------------------------------------
	
				// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
				// Informer les gagnants
				// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
				$est_gagnant = true ; // pour choix du message dans l'e-mail
				$rapport_complet_concat.= 'Liste des gagnants : <br /><ul>' ; // Données pour Admin
				$rapport_1_concours_rec_db.= ':::::::::::::: Liste des gagnants :::::::::::::: <br />' ; // Pour sauvegarde dans DB
				foreach ($id_gagnants as $liloolou)
				{
					$reponse_liloolou = mysql_query("SELECT * FROM $table_ag_conc_joueur 
					WHERE id_conc_joueur = '$liloolou'");
					$donnees_liloolou = mysql_fetch_array($reponse_liloolou);
					
					$id_liloolou = $donnees_liloolou['id_conc_joueur'] ;
					$nom_liloolou = $donnees_liloolou['nom_joueur_conc'] ;
					$mail_liloolou = $donnees_liloolou['mail_joueur_conc'] ;
					$time_liloolou = $donnees_liloolou['time_stamp_joueur'] ;
					
					// Données pour Admin
					$rapport_complet_concat.= '<li>' . $donnees_liloolou['nom_joueur_conc'] . ' 
					::::::::: ' . $donnees_liloolou['mail_joueur_conc'] . 
					' ::::::::: (Réf:G-' . $donnees_liloolou['id_conc_joueur'] . ') </li>' ;
					
					// Pour sauvegarde dans DB
					$rapport_1_concours_rec_db.= '<br /> - ' . $donnees_liloolou['nom_joueur_conc'] . ' 
					 - ' . $donnees_liloolou['mail_joueur_conc'] . 
					' (Ref:G-' . $donnees_liloolou['id_conc_joueur'] . ') <br />' ;
					
				
					// Appel de la fonction d'envoi des e-mails
					$test_envoi_joueur = '' ; // raz
					$test_envoi_joueur = informer_joueur($id_liloolou, $nom_liloolou, $mail_liloolou, $time_liloolou, $est_gagnant) ; 
	
					if ($test_envoi_joueur)
					{
						echo '<div class="info">Confirmation : e-mail envoyé au <strong>joueur gagnant</strong> ' . 
						$nom_liloolou . ' (id ' . $id_liloolou . ')</div>' ;
					}
					else
					{
						echo '<div class="alerte">!!!!!!!!!!!!!!!! échec <strong>envoi e-mail  
						au joueur</strong> ' . $id_liloolou . ' (id ' . $id_liloolou . ')</div>' ;
					}
				}
				$rapport_complet_concat.= '</ul>' ; // Données pour Admin
				$rapport_1_concours_rec_db.= '<br />' ; // Pour sauvegarde dans DB
	
	
				// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
				// Informer les perdants
				// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
				if (isset ($nb_perdants) AND $nb_perdants > 0)
				{
					$rapport_complet_concat.= 'Liste des perdants : <br /><ul> ' ; //Données pour Admin
					$rapport_1_concours_rec_db.= ':::::::::::::: Liste des perdants :::::::::::::: <br /> ' ; // Pour sauvegarde dans DB
	
					$est_gagnant = false ; // pour choix du message dans l'e-mail
					foreach ($id_perdants as $liloolou)
					{
						$reponse_liloolou = mysql_query("SELECT * FROM $table_ag_conc_joueur 
						WHERE id_conc_joueur = '$liloolou'");
						$donnees_liloolou = mysql_fetch_array($reponse_liloolou);
						
						$id_liloolou = $donnees_liloolou['id_conc_joueur'] ;
						$nom_liloolou = $donnees_liloolou['nom_joueur_conc'] ;
						$mail_liloolou = $donnees_liloolou['mail_joueur_conc'] ;
						$time_liloolou = $donnees_liloolou['time_stamp_joueur'] ;
						
						// Données pour Admin
						$rapport_complet_concat.= '<li>' . $donnees_liloolou['nom_joueur_conc'] . ' 
						::::::::: ' . $donnees_liloolou['mail_joueur_conc'] . 
						' ::::::::: (Réf:P-' . $donnees_liloolou['id_conc_joueur'] . ') </li>' ;
	
						
						// Pour sauvegarde dans DB
						$rapport_1_concours_rec_db.= '<br /> - ' . $donnees_liloolou['nom_joueur_conc'] . ' 
						 - ' . $donnees_liloolou['mail_joueur_conc'] . 
						' - (Ref:P-' . $donnees_liloolou['id_conc_joueur'] . ') <br />' ;
	
	
						// Appel de la fonction d'envoi des e-mails
						$test_envoi_joueur = informer_joueur ($id_liloolou, $nom_liloolou, $mail_liloolou, $time_liloolou, $est_gagnant) ; 
						if ($test_envoi_joueur)
						{
							echo '<div class="info">Confirmation : e-mail envoyé au <strong>joueur perdant</strong> ' . 
							$nom_liloolou . ' (id ' . $id_liloolou . ')</div>' ;
						}
						else
						{
							echo '<div class="alerte">!!!!!!!!!!!!!!!! échec <strong>envoi e-mail  
							au joueur</strong> ' . $id_liloolou . ' (id ' . $id_liloolou . ')</div>' ;
						} 
					}
					$rapport_complet_concat.= '</ul>' ; // Données pour Admin
					$rapport_1_concours_rec_db.= '<br />' ; // Pour sauvegarde dans DB
				}
			}
			$i_lot++ ;
			
			// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
			// Fin boucle LOT
			// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
		
		}
		// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
		// Fin boucle 1 concours
		// BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
	
		// Indiquer dans la DB que ce concours est CLOTURE
		mysql_query("UPDATE $table_ag_conc_fiches SET flags_conc = 'cloture' WHERE id_conc = '$id_conc' LIMIT 1 ") ;
		
		// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
		// Enregistrer le rapport sur le concours en cours dans la DB
		// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
		echo $rapport_1_concours_rec_db ;
	
		// Formater la chaine pour rentrer dans la DB
		//	$rapport_2_db = var_export($rapport_1_concours_rec_db, ENT_QUOTES); // Cette syntaxe n'est pas trop correcte
		$rec_2_db = '';
		$rapport_1_concours_rec_db = addslashes( $rapport_1_concours_rec_db ); 
		$rapport_2_db = var_export($rapport_1_concours_rec_db, ENT_QUOTES);
		
		$rec_2_db = mysql_query("INSERT INTO `$table_ag_conc_historique` (`id_fiche_conc_histo`, `detail_conc_histo`) 
		VALUES ($id_conc, $rapport_2_db)") ;
		if ($rec_2_db)
		{
			echo '<div class="info">Le rapport du tirage pour le concours intitulé "' . $nom_event_conc . '" (ID ' . $id_conc . ')
			a bien été <strong>sauvegardé dans la base de données</strong></div>' ;
		}
		else
		{
			echo '<div class="alerte"> !!!!!!!! Echec de la <strong>sauvegarde du rapport</strong> du tirage pour le concours intitulé "' . 
			$nom_event_conc . '" (ID ' . $id_conc . '). <br />  <br /> ' . 
			$rec_2_db . "Erreur : " . mysql_error() . '</div>' ;
		} 
	
	
		// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
		// Informer le LIEU qui propose ces places
		// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
		$test_attache_culturel = '<div class="resume_event"><br /><b> 
		********************** Informations destinées à l\'attaché de presse : ********************** </b><br />' ;
		
		$test_attache_culturel.= ' Voici la liste des gagnants des places pour l\'événement ' . 
		$donnees ['nom_event_conc'] . ' (Réf:concours '. $id_conc . ')<br /> <br /> ' ;
	
		$test_attache_culturel.= $liste_infos_gagnants ;
		
		$test_attache_culturel.= '</div><br />' ;
		//echo $test_attache_culturel ;
		
		$test_envoi_lieu = informer_lieu () ; 
		if ($test_envoi_lieu)
		{
			echo '<div class="info">Confirmation : e-mail envoyé au <strong>LIEU</strong> : ' . 
			$donnees ['mail_lieu_conc'] . '</div>' ;
		}
		else
		{
			echo '<div class="alerte">!!!!!!!!!!!!!!!! échec envoi e-mail  
			au <strong>LIEU</strong> : ' . $donnees ['mail_lieu_conc'] . '</div>' ;
		}
	}
	
	// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
	// Informer l'Administrateur
	// IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII
	echo '<br /> 
	=================================================================== <br />
	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ <br />
	|| RAPPORT COMPLET DU TIRAGE DU ' . date('d-m-Y à H\hi') . ' :<br />
	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br />
	=================================================================== <br />'
	. $rapport_complet_concat ;
	
	$test_envoi_admin = informer_admin ($rapport_complet_concat) ; 
	if ($test_envoi_admin)
	{
		echo '<div class="info">Confirmation : e-mail envoyé à l\'<strong>ADMINISTRATEUR</strong> : ' . 
		$donnees ['mail_lieu_conc'] . '</div>' ;
	}
	else
	{
		echo '<div class="alerte">!!!!!!!!!!!!!!!! échec envoi e-mail  
		à l\'<strong>ADMINISTRATEUR</strong> : ' . $donnees ['mail_lieu_conc'] . '</div>' ;
	}
}
else
{
	echo '<br /> <br /> <br /> <div class="info">Il n\'y a aucun tirage à effectuer pour le moment</div>' ;
}


// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
} // fin partie protégée de la page
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS


?>

<p >&nbsp;</p>
</body>
</html>
