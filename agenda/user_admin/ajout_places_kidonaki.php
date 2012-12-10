<?php 
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Proposer des places pour cet événement sur Kidonaki</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php
require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
$au_moins_un_champ_mauvais = '' ;
$marge_date_vente = 12 ; // Limite en nombre de jours minimum avant fin de vente 
?>

<div id="head_admin_agenda"></div>

<h1>Proposer des places pour cet événement sur Kidonaki</h1>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';

// ----------------------------------------------------------------------------------------------
// info : basé sur "edit_jours_gp.php"
// ----------------------------------------------------------------------------------------------

if (empty ($_GET['id']) OR $_GET['id'] == NULL) // La variable GET qui donne l'ID à confirmer. 
{
	echo '<br><br><br><div class="alerte"><p>error : GET id absent </p></div><br>' ;
}
else
{
	$id = htmlentities($_GET['id'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees = mysql_fetch_array($reponse);
	$lieu_event = $donnees['lieu_event'] ;
	$article_kidonaki = $donnees['article_kidonaki'] ;

	$reponse_test = mysql_query("SELECT lieu_event FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees_test = mysql_fetch_array($reponse_test); 
	
	// Pour tester si le LIEU possède bien un compte KIDONAKI :
	$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $lieu_event");
	$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
	$auteur_kidonaki = $donnees_lieu['auteur_kidonaki'];

	// Si la valeur de $_GET['id'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Cette entrée n\'existe pas</div><br>' ;
	}

	// Tester si cet événement peut être édité par ce USER
	elseif ($donnees_test['lieu_event'] != $_SESSION['lieu_admin_spec']) 
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Vous ne pouvez pas modifier un événement rattaché à un autre lieu culturel</div><br>' ;
		exit () ;
	}	
	
	// Tester si ce LIEU possède bien un compte KIDONAKI
	elseif ($auteur_kidonaki == 0)
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">
		Vous ne semblez pas posséder un compte Kidonaki, <br />
		il vous est donc impossible de réaliser cette action</div><br>' ;
		exit () ;
	}
		
	// Tester si cet événement ne contient pas déjà un lot de place sur Kidonaki
	elseif ($article_kidonaki != 0)
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">
		Il semble que des places pour cet événement aient déjà été mises en ligne sur Kidonaki. <br />
		Vous ne pouvez donc plus en rajouter via ce formulaire, mais vous pouvez le faire via votre compte Kidonaki</div><br>' ;
		exit () ;
	}
	
	else
	{
			
		// ------------------------------------------------
		// Lecture des infos de la DB KIDONAKI pour la liste déroulante des projets à soutenir
		// ------------------------------------------------	
		//--- mysql_close($db2dlp);
		require '../kidonaki/inc_db_connect_kidonaki.php';

		/*$string_list_kido = '<multi>[fr]Soutien à l entreprenariat au Burkina Faso  [nl]Steun een ondernemer in Burkina Faso  [en]Support small Entrepreneurs in Burkina Faso </multi>' ;*/
		$pattern_list_kido[0] = '!<multi>!' ;		
		$pattern_list_kido[1] = '!</multi>!' ;
		$pattern_list_kido[2] = '!\[fr\]!' ;
		$pattern_list_kido[3] = '!\[nl\]!' ;
		$replacement_list_kido = '' ;			
		
		$reponse_kido_1 = mysql_query("SELECT id_rubrique, titre FROM spip_rubriques WHERE id_parent  = '19'");
		// On fetch la DB ici, et les données sont utilisées plus loin dans le formulaire.

		
		require '../inc_db_connect.php';
		// ------------------------------------------------
		// Lecture des infos de la DB pour l'événement :
		// ------------------------------------------------
		$rec = '';
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
		$donnees = mysql_fetch_array($reponse);
		
		$nom_event_db = $donnees['nom_event'];
		$nom_event = $donnees['nom_event']; // enlever si à nouveau champ Titre dans formulaire
		$description_event_db = strip_tags($donnees['description_event']);
		$genre_event = $donnees ['genre_event'];
		
		$date_event_debut = $donnees['date_event_debut'];
		$date_event_fin = $donnees['date_event_fin'];

		$AAAA_debut = substr($date_event_debut, 0, 4);
		$AAAA_fin = substr($date_event_fin, 0, 4);
		$MM_debut = substr($date_event_debut, 5, 2);
		$MM_fin = substr($date_event_fin, 5, 2);
		$JJ_debut = substr($date_event_debut, 8, 2);
		$JJ_fin = substr($date_event_fin, 8, 2);
		$AAAA_MM_debut = substr($date_event_debut, 0, 7);

		$date_fin_kido_db = $JJ_fin . '-' . $MM_fin . '-' . $AAAA_fin ;

		$jours_actifs_event = $donnees ['jours_actifs_event'];
		$jours_actifs_event = explode(",", $jours_actifs_event);
		
		$article_kidonaki = $donnees ['article_kidonaki'];
		
		$pic_event[1] = $donnees ['pic_event_1'];
		$pic_event[2] = $donnees ['pic_event_2'];
		$pic_event[3] = $donnees ['pic_event_3'];


		// ------------------------------------------------
		// Remplissage du formulaire
		// ------------------------------------------------		
		$AAAA_traite = substr($date_event_debut, 0, 4);
		$MM_traite = substr($date_event_debut, 5, 2);
		$AAAA_MM_traite = substr($date_event_debut, 0, 7);
		
		$jour_fin_kido = $JJ_fin ;
		$mois_fin_kido = $MM_fin ;
		$annee_fin_kido = $AAAA_fin ;
		
		
		// ------------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------------
		// Si appuyé sur bouton "Enregistrer"
		// ------------------------------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------------------------------
		if (isset($_POST['modif_form']) AND ($_POST['modif_form'] == 'Enregistrer'))
		{
			/*// TEST DU NOM DE L'EVENEMENT 
			if (isset($_POST['nom_event']) AND ($_POST['nom_event'] != NULL)) 
			{
				$nom_event_de_form = $_POST['nom_event'];
				$nom_event_de_form = str_replace("’", "\'", $nom_event_de_form);
				$nom_event_de_form = str_replace("&#039;", "'", $nom_event_de_form);
				$nom_event_de_form = str_replace("&rsquo;", "'", $nom_event_de_form);
				$nom_event_de_form = strip_tags($nom_event_de_form);
				$nom_event_de_form = htmlspecialchars($nom_event_de_form);
				//echo $nom_event_de_form ;
				
				//$nom_event_pour_db_kido = addslashes($nom_event_de_form); // pour DB Kidonaki
				$nom_event_pour_db_kido = $nom_event_de_form;
				
				$nom_event = stripslashes($nom_event_de_form) ; // pour remettre dans formulaire
			}
			else
			{
				$error_nom_event = '<div class="error_form">Vous devez indiquer un nom pour désigner l\'événement. <br />
				Nous vous proposons de mettre le même nom que sur le site DemandezLeProgramme</div>';
				$rec .= '- Vous devez indiquer un nom pour désigner l\'événement<br>';
				$nom_event = $nom_event_db ;
			}
			*/
				
			// -----------------------------------------
			// TEST PROCEDURE ENLEVEMENT DES PLACES
			if (isset($_POST['procedure_places']) AND ($_POST['procedure_places'] != NULL)) 
			{
				$procedure_places_form = $_POST['procedure_places'] ;
				$procedure_places_form = str_replace("’", "'", $procedure_places_form); // Modifié le 15-09-09
				$procedure_places_form = str_replace("&#039;", "'", $procedure_places_form); // Modifié le 15-09-09
				$allowedTags = '<br><br />'; // Balises de style que les USERS peuvent employer
				$procedure_places_form = strip_tags($procedure_places_form,$allowedTags);
				$procedure_places_form = htmlspecialchars($procedure_places_form);
				
				$max=1800 ; 
				if (strlen($procedure_places_form)>=$max)
				{	
					$char_en_trop = strlen($procedure_places_form) - $max ; // Tester longueur de la chaîne de caractères
					$error_procedure_places = '<div class="error_form">La taille du texte de description est trop grande 
					(limite autorisée : ' . $max . 'caractères) . 
					Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir.</div>';
					$rec .= '- La taille du texte dépasse la limite autorisée<br>';			
					$procedure_places = $procedure_places_form ;
				}
				else
				{
					$procedure_places = $procedure_places_form ;
				}		
			}
			else
			{
				$error_procedure_places = '<div class="error_form">
				Vous devez introduire un texte descriptif de l\'événement</div>';
				$rec .= '- Vous devez introduire un texte descriptif de l\'événement<br>';
				$procedure_places_form = $procedure_places_db;
			}
			
			
			// -----------------------------------------
			// TEST NOMBRE DE PLACES QUE VOUS METTEZ EN VENTE 
			if (isset($_POST['nombre_places_kidonaki']) 
			AND preg_match('`^[[:alnum:]]{1,2}$`', $_POST['nombre_places_kidonaki'])
			AND $_POST['nombre_places_kidonaki'] !=0 ) 
			{
				$nombre_places_kidonaki_form = htmlspecialchars($_POST['nombre_places_kidonaki']);
				$nombre_places_kidonaki = $nombre_places_kidonaki_form ;
			}
			else
			{
				$error_nombre_places_kidonaki = '<div class="error_form">
				Vous devez indiquer le nombre de places que vous mettez en vente';
				$rec .= '- Vous devez indiquer le nombre de places que vous mettez en vente<br>';
				$nombre_places_kidonaki = '' ;
			}

			
			// -----------------------------------------
			// TEST PROJET SOUTENU 
			if (isset($_POST['projet_kido']) AND $_POST['projet_kido'] != 'vide' ) 
			{
				$projet_kido_form = htmlspecialchars($_POST['projet_kido']);
				$projet_kido = $projet_kido_form ;
			}
			else
			{
				$error_projet_kido = '<div class="error_form">
				Vous devez choisir un projet à soutenir dans la liste déroulante';
				$rec .= '- Vous devez choisir un projet à soutenir dans la liste déroulante<br>';
				$projet_kido = '' ;
			}



			// -----------------------------------------
			// PRIX POUR 1 PLACE
			if (isset($_POST['prix_place_kidonaki']) AND preg_match('`^[[:alnum:]]{1,5}$`', $_POST['prix_place_kidonaki'])) 
			{
				$prix_place_kidonaki_form = htmlspecialchars($_POST['prix_place_kidonaki']);
				$prix_place_kidonaki = $prix_place_kidonaki_form ;
				
				if ($prix_place_kidonaki_form < 5)
				{
					$error_prix_place_kidonaki = '<div class="error_form">
					Le prix de vente pour une place ne peut être inférieur à 5€';
					$rec .= '- Le prix de vente pour une place ne peut être inférieur à 5€<br>';
					$prix_place_kidonaki = $prix_place_kidonaki_form ;
				}
			}
			else
			{
				$error_prix_place_kidonaki = '<div class="error_form">
				Vous devez indiquer le prix de vente pour une place (chiffres uniquement, sans virgule ni centimes)';
				$rec .= '- Vous devez indiquer le prix de vente pour une place<br>';
				$prix_place_kidonaki = '' ;
			}



//NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN
			// -----------------------------------------
			// TEST DATE DEBUT VALIDITE
			if (
			isset($_POST['jour_debut_kido']) AND preg_match('/[0-9]$/', $_POST['jour_debut_kido'])
			AND isset($_POST['mois_debut_kido']) AND preg_match('/[0-9]$/', $_POST['mois_debut_kido'])
			AND isset($_POST['annee_debut_kido']) AND preg_match('/[0-9]$/', $_POST['annee_debut_kido'])
			) 
			{
				$jour_debut_kido = htmlspecialchars($_POST['jour_debut_kido']);
				$mois_debut_kido = htmlspecialchars($_POST['mois_debut_kido']);
				$annee_debut_kido = htmlspecialchars($_POST['annee_debut_kido']);
				
				/* Tester si cette date est bien supérieure ou égale à la date de début de l'event 
				et inférieure ou égale à la date de fin de l'event */
				
				$date_debut_evenement = date(mktime(0, 0, 0, $MM_debut, $JJ_debut, $AAAA_debut));
				$date_fin_evenement = date(mktime(0, 0, 0, $MM_fin, $JJ_fin, $AAAA_fin));
				$date_debut_encodée = date(mktime(0, 0, 0, $mois_debut_kido, $jour_debut_kido, $annee_debut_kido));
				
				$date_debut_kido = $jour_debut_kido . '-' . $mois_debut_kido . '-' . $annee_debut_kido ; // est écrasé en cas d'erreur au test suivant

				if ($date_debut_encodée<$date_debut_evenement)
				{
					$error_date_debut_kido = '<div class="error_form">La date de début de validité des places ne peut pas être antérieure à la date de début de l\'événement<br /></div>';
					$rec .= '- La date de début de validité des places ne peut pas être antérieure à la date de début de l\'événement<br>';
					$date_debut_kido = $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut ; ;
				}

				if ($date_debut_encodée>$date_fin_evenement)
				{
					$error_date_debut_kido = '<div class="error_form">La date de début de validité des places ne peut pas être postérieure à la date de fin de l\'événement<br /></div>';
					$rec .= '- La date de début de validité des places ne peut pas être postérieure à la date de fin de l\'événement<br>';
					$date_debut_kido = $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut ; ;
				}
			}
			else
			{
				$error_date_debut_kido = '<div class="error_form">Vous devez indiquer une date de début de validité des places, par exemple, le premier jour de représentation de l\'événement. 
L\'acheteur devra donc assister à la représentation de cet événement AVANT cette date limite.<br /></div>';
				$rec .= '- Pas de date début<br>';
				$date_debut_kido = $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut ;
			}
			
			// Mise au format pour la DB : 
			$date_debut_kido_format_db = date('Y-m-d H:i:s', mktime(0, 0, 0, $mois_debut_kido, $jour_debut_kido, $annee_debut_kido));

			
//NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN



			// -----------------------------------------
			// TEST DATE FIN VALIDITE
			if (
			isset($_POST['jour_fin_kido']) AND preg_match('/[0-9]$/', $_POST['jour_fin_kido'])
			AND isset($_POST['mois_fin_kido']) AND preg_match('/[0-9]$/', $_POST['mois_fin_kido'])
			AND isset($_POST['annee_fin_kido']) AND preg_match('/[0-9]$/', $_POST['annee_fin_kido'])
			) 
			{
				$jour_fin_kido = htmlspecialchars($_POST['jour_fin_kido']);
				$mois_fin_kido = htmlspecialchars($_POST['mois_fin_kido']);
				$annee_fin_kido = htmlspecialchars($_POST['annee_fin_kido']);
				
				/* Tester si cette date est bien supérieure ou égale à la date de début de l'event 
				et inférieure ou égale à la date de fin de l'event */
				
				$date_debut_evenement = date(mktime(0, 0, 0, $MM_debut, $JJ_debut, $AAAA_debut));
				$date_fin_evenement = date(mktime(0, 0, 0, $MM_fin, $JJ_fin, $AAAA_fin));
				$date_fin_encodée = date(mktime(0, 0, 0, $mois_fin_kido, $jour_fin_kido, $annee_fin_kido));
				
				$date_fin_kido = $jour_fin_kido . '-' . $mois_fin_kido . '-' . $annee_fin_kido ; // est écrasé en cas d'erreur au test suivant
				$date_de_fin_de_vente = mktime(0, 0, 0, $mois_fin_kido, $jour_fin_kido-$marge_date_vente, $annee_fin_kido) ;
				$aujourdhui = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$nombre_jours_limite_vente = ($date_de_fin_de_vente+0 -$aujourdhui+0)/(24*3600) ;
				//echo 'Il reste ' . $nombre_jours_limite_vente . ' jours pour vendre ces places (en tenant compte d\'une marge de ' . $marge_date_vente . ' jours).' ;
				
				if ($date_fin_encodée<$date_debut_evenement)
				{
					$error_date_fin_kido = '<div class="error_form">La date limite pour la validité des places ne peut pas être antérieure à la date de début de l\'événement<br /></div>';
					$rec .= '- La date limite pour la validité des places ne peut pas être antérieure à la date de début<br>';
					$date_fin_kido = $date_fin_kido_db ;
				}

				if ($date_fin_encodée>$date_fin_evenement)
				{
					$error_date_fin_kido = '<div class="error_form">La date limite pour la validité des places ne peut pas être postérieure à la date de fin de l\'événement<br /></div>';
					$rec .= '- La date limite pour la validité des places ne peut pas être postérieure à la date de fin<br>';
					$date_fin_kido = $date_fin_kido_db ;
				}
				if ($nombre_jours_limite_vente<0)
				{
					$error_date_fin_kido = '<div class="error_form">La date limite que vous avez encodée pour la validité des places  ne laisse pas une période suffisante pour la vente des places sur Kidonaki. Il faut en effet <strong>un minimum de '.$marge_date_vente.' jours</strong> entre le début des ventes et la clôture des ventes.<br /></div>';
					$rec .= '- Pas minimum 10 jours<br>';
					$date_fin_kido = $date_fin_kido_db ;
				}
			}
			else
			{
				$error_date_fin_kido = '<div class="error_form">Vous devez indiquer une date limite pour la validité des places, ou laisser la date de fin de représentation de l\'événement qui est proposée par défaut. L\'acheteur devra donc assister à la représentation de cet événement AVANT cette date limite.<br /></div>';
				$rec .= '- Pas de date limite<br>';
				$date_fin_kido = $date_fin_kido_db ;
			}
				

	
			//-------------------------------------------------------------------------------------------------
			//-------------------------------------------------------------------------------------------------
			// TEST FORMULAIRE ET CONVERSION DE FORMAT POUR SAUVEGARDE DANS DB KIDONAKI :
			//-------------------------------------------------------------------------------------------------
			if ($rec == NULL) // Enregistrement les données dans la DB 
			{ 
				
				// Sélection des JOURS ACTIFS compris dans la période [ $date_debut_kido_format_db ==> $date_fin_kido ]
				$jours_actifs_kido = '';

				$AAAA_kido_debut = substr($date_debut_kido_format_db, 0, 4);
				$MM_kido_debut = substr($date_debut_kido_format_db, 5, 2);	
				$JJ_kido_debut = substr($date_debut_kido_format_db, 8, 2);
				$AAAA_kido_fin = substr($date_fin_kido, 6, 4);
				$MM_kido_fin = substr($date_fin_kido, 3, 2);
				$JJ_kido_fin = substr($date_fin_kido, 0, 2);
				//echo '<br>Début = (date_debut_kido_format_db) '.$JJ_kido_debut . '-' . $MM_kido_debut . '-' . $AAAA_kido_debut ;
				//echo '<br>Fin (date_fin_kido) = '.$JJ_kido_fin . '-' . $MM_kido_fin . '-' . $AAAA_kido_fin ;
				
				$date_fin_kido_format_db = date('Y-m-d H:i:s', mktime(0, 0, 0, $MM_kido_fin, $JJ_kido_fin, $AAAA_kido_fin)); // pour DB

				$time_event_debut = date(mktime(0, 0, 0, $MM_kido_debut, $JJ_kido_debut, $AAAA_kido_debut));
				$time_event_fin = date(mktime(0, 0, 0, $MM_kido_fin, $JJ_kido_fin, $AAAA_kido_fin));
				
				foreach($jours_actifs_event as $un_jour)
				{
					$AAAA_un_jour_actif = substr($un_jour, 0, 4);
					$MM_un_jour_actif = substr($un_jour, 5, 2);
					$JJ_un_jour_actif = substr($un_jour, 8, 2);
					//echo '<br>le jour : '.$JJ_un_jour_actif . '-' . $MM_un_jour_actif . '-' . $AAAA_un_jour_actif ;
					$time_un_jour_actif = date(mktime(0, 0, 0, $MM_un_jour_actif, $JJ_un_jour_actif, $AAAA_un_jour_actif));
					
					if (($time_un_jour_actif >= $time_event_debut) AND ($time_un_jour_actif <= $time_event_fin))
					{
						//echo ' ==> Est un jour ok pour la période<br>' ;
						$jours_actifs_kido.=  $un_jour . ',' ;
					}
					else
					{
						//echo ' ==> XXXXXXXXXXX <br>' ;
					}
				}
				//echo '<br><br><br><br><strong>Nouvelle chaine des jours actifs</strong> : ' . $jours_actifs_kido . '<br><br>';
			
				
				// Description des particularités (à metre dans le CHAPO : 
				// date validité
				$concat_description = 'La place vendue est valables pour la période allant du ' . $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut . ' au ' . $date_fin_kido . ' inclu. Consultez également le <a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . $id . '#calendrier" target="_blank">calendrier</a> de cet événement pour connaître le détail des jours de représentation. <br /> <br /> ';
				
				// + procédure enlèvement
				//$pour_chapo = stripslashes($procedure_places) ;
				$pour_chapo = $procedure_places ;
				
				
				// + descriptif DLP
				$description_chaine_nettoyee = $description_event_db ; 
				$description_chaine_nettoyee = str_replace("’", "'", $description_chaine_nettoyee);
				$description_chaine_nettoyee = str_replace("&#039;", "'", $description_chaine_nettoyee);
				$description_chaine_nettoyee = str_replace("&rsquo;", " ", $description_chaine_nettoyee);
				$description_chaine_nettoyee = str_replace("&quot;", " ", $description_chaine_nettoyee);
				$description_chaine_nettoyee = raccourcir_chaine(html_entity_decode(strip_tags($description_chaine_nettoyee)),600) ;
				$concat_description = $description_chaine_nettoyee . ' <a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . $id . '" target="_blank">Pour plus de détail</a> <br /> ';
				$concat_description = addslashes($concat_description);
				
				
				// ****************************************************************************
				// ****************************************************************************
				// Rec dans la DB :
				// ****************************************************************************
				// ****************************************************************************
				
				// Connexion : 
				require '../kidonaki/inc_db_connect_kidonaki.php';
				
				// Mise en forme de "$nom_event" :
				$nom_event_nettoye = strip_tags($nom_event);
				//$nom_event_nettoye = addslashes($nom_event_nettoye) . ' : 1 place' ;
				$nom_event_nettoye = $nom_event_nettoye . ' : 1 place' ;
				$nom_event_nettoye = str_replace("'", "\'", $nom_event_nettoye);
				$nom_event_nettoye = str_replace("’", "\'", $nom_event_nettoye);
				$nom_event_nettoye = str_replace("&#039;", "\'", $nom_event_nettoye);
				$nom_event_nettoye = str_replace("&quot;", " ", $nom_event_nettoye);
				$nom_event_nettoye = str_replace("&rsquo;", "\'", $nom_event_nettoye);
				$nom_event_nettoye = html_entity_decode($nom_event_nettoye);
				$nom_event_nettoye = htmlspecialchars($nom_event_nettoye);
				
				//echo '<br>------------- '.$nom_event_nettoye;
				
				//$nom_event_pour_db_kido = addslashes($nom_event_de_form); // pour DB Kidonaki
				//$nom_event_pour_db_kido = $nom_event_de_form ;
				$nom_event_pour_db_kido = $nom_event_nettoye ; // enlever si à nouveau champ Titre dans formulaire

				//$nom_event = stripslashes($nom_event_de_form) ; // pour remettre dans formulaire
				$nom_event = stripslashes($nom_event) ; // enlever si à nouveau champ Titre dans formulaire
				
				// Création de "spip_articles" dans Kidonaki :
				mysql_query("INSERT INTO `ki3naki`.`spip_articles` (`id_article` ,`surtitre` ,`titre` ,`soustitre` ,`id_rubrique`,`descriptif` ,`chapo` ,`texte` ,`ps` ,`date` ,`statut` ,`id_secteur` ,`maj` ,`export` ,`date_redac`,`visites` ,`referers` ,`popularite` ,`accepter_forum` ,`date_modif` ,`lang` ,`langue_choisie` ,`id_trad` ,`extra` ,`id_version` ,`nom_site` ,`url_site` ,`j_mots` ,`id_evenement`)
VALUES ('' , '', '$nom_event_pour_db_kido', '', '$projet_kido', '', '$pour_chapo', '$concat_description', '', '2009-11-10 12:18:15', 'publie', '19', NOW( ) , 'oui', '2009-11-10 12:18:15', '0', '0', '0', '', '0000-00-00 00:00:00', 'fr', 'non', '0', NULL , '0', '', '', '', '$id'
)") or die('Erreur écriture 1 : ' . mysql_error() . ' ');
				
				$id_nouvel_article = mysql_insert_id();
				// Lier l’auteur à l'article dans "spip_auteurs_articles"
				mysql_query("INSERT INTO `ki3naki`.`spip_auteurs_articles` (`id_auteur` ,`id_article`)
VALUES ('$auteur_kidonaki' , '$id_nouvel_article')") or die('Erreur écriture 2 : ' . mysql_error() . ' ');
				


				// !!!!!!!!!!!!!!!!!!!!!!! a modifier !!!!!!!!!!!!!!!!!!!!!!!!! 
				// Genre : écrire dans "spip_mots_articles"
				// !!!!!!!!!!!!!!!!!!!!!!! a modifier !!!!!!!!!!!!!!!!!!!!!!!!! 
				$correspondance_genres = array (
					"g01" => 670,
					"g02" => 671,
					"g04" => 672,
					"g14" => 673,
					"g09" => 674,
					"g03" => 675,
					"g10" => 676,
					"g06" => 677,
					"g11" => 527,
					"g07" => 591,
					"g05" => 678,
					"g12" => 679,
					"g13" => 680,
					"g08" => 641
				);
				//echo $correspondance_genres[$genre_event] ; // TEST
				mysql_query("INSERT INTO `ki3naki`.`spip_mots_articles` (`id_mot`,`id_article`) 
				VALUES ($correspondance_genres[$genre_event], $id_nouvel_article)") 
				or die('Erreur écriture 3 : ' . mysql_error() . ' ');

				
				// Par "place" mise à disposition, créer un objet dans "spip_encheres_objets"
				
				// 1) Créer le premier objet
				
				// Calculer la "durée" de l'enchère : s'il reste plus de 15 jours, mettre 15 jours, 
				// if($nombre_jours_limite_vente>15) { $duree_enchere = 15 ; } // modifié le 27-01-10 pour Xavier
				if($nombre_jours_limite_vente>15) { $duree_enchere = $nombre_jours_limite_vente ; }
				elseif($nombre_jours_limite_vente==0 ) { $duree_enchere = 0 ; }
				else { $duree_enchere = $nombre_jours_limite_vente ; }
				
				// Date de la fin des enchères
				$date_stop_vente =  date('Y-m-d H:i:s', mktime(0, 0, 0, $mois_fin_kido, $jour_fin_kido-$marge_date_vente, $annee_fin_kido));
				
				//$date_fin_enchere = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') + $duree_enchere, date('Y')));
				$date_fin_enchere = $date_stop_vente; // Modifié pour Xavier 27-01-10 (voir ligne du haut)

				//echo '<br /><br />$date_fin_enchere = ' . $date_fin_enchere .' ******************** $date_stop_vent = ' . $date_stop_vente . ' La durée des encheres = '.$duree_enchere.'<br />';
				
				
				mysql_query("INSERT INTO `ki3naki`.`spip_encheres_objets` (`id_objet` ,`id_objet_source` ,`id_article` ,`id_acheteur` ,`id_auteur` ,`prix_depart` ,`prix_achat_inmediat` ,`montant_mise` ,`mode_livraison` ,`courrier_velo` ,`prix_livraison` ,`nombre` ,`date_creation` ,`duree` ,`date_debut` ,`date_debut_evenement` ,`date_fin_evenement` ,`jours_actifs` ,`date_fin` ,`date_stop_vente` ,`date_vente` ,`maj` ,`statut` ,`type` ,`paiement_paypal` ,`paiement_virement` ,`evaluation_vendeur` ,`evaluation_acheteur` ,`statut_payement_livraison` ,`date_paiement_livraison` ,`statut_payement_objet` ,`date_paiement_objet` ,`statut_livraison` ,`date_livraison` ,`envoi_rappels` ,`remise_vente_automatique`)
				VALUES ('', '0', '$id_nouvel_article', '', '$auteur_kidonaki', '$prix_place_kidonaki', 'inmediat', '$prix_place_kidonaki', 'email', '', '0', '$nombre_places_kidonaki', NOW() , '$duree_enchere', NOW() , '$date_debut_kido_format_db', '$date_fin_kido_format_db', '$jours_actifs_kido', '$date_fin_enchere', '$date_stop_vente', '0000-00-00 00:00:00', NOW(), 'mise_en_vente', '', '0', '0', NULL , NULL , 'ok', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '')") 
				or die('Erreur écriture 4 : ' . mysql_error() . ' ');


				// 2) Créer éventuellement des copies de cet objet
				if ($nombre_places_kidonaki >1)
				{
					$id_nouvel_objet = mysql_insert_id();
					
					// début chaine
					$req_copies_objet = 'INSERT INTO `ki3naki`.`spip_encheres_objets` (`id_objet` ,`id_objet_source` ,`id_article` ,`id_acheteur` ,`id_auteur` ,`prix_depart` ,`prix_achat_inmediat` ,`montant_mise` ,`mode_livraison` ,`courrier_velo` ,`prix_livraison` ,`nombre` ,`date_creation` ,`duree` ,`date_debut` ,`date_debut_evenement` ,`date_fin_evenement` ,`jours_actifs` ,`date_fin` ,`date_stop_vente` ,`date_vente` ,`maj` ,`statut` ,`type` ,`paiement_paypal` ,`paiement_virement` ,`evaluation_vendeur` ,`evaluation_acheteur` ,`statut_payement_livraison` ,`date_paiement_livraison` ,`statut_payement_objet` ,`date_paiement_objet` ,`statut_livraison` ,`date_livraison` ,`envoi_rappels` ,`remise_vente_automatique`)
					VALUES' ;
					
					// boucler sur le milieu de la chaine
					for($i=1 ; $i<$nombre_places_kidonaki ; $i++)
					{
						($i < 3) ? ($statut_places='mise_en_vente') : ($statut_places='stand_by') ;
						($i < 3) ? ($date_debut_enchere=date('Y-m-d H:i:s')) : ($date_debut_enchere='') ;
						($i < 3) ? ($date_fin_enchere=$date_fin_enchere) : ($date_fin_enchere='') ;
						$req_copies_objet.= "
						
						('', '$id_nouvel_objet', '$id_nouvel_article', '', '$auteur_kidonaki', '$prix_place_kidonaki', 'inmediat', '$prix_place_kidonaki', 'email', '', '0', '1', NOW() , '$duree_enchere', '$date_debut_enchere', '$date_debut_kido_format_db', '$date_fin_kido_format_db', '$jours_actifs_kido', '$date_fin_enchere', '$date_stop_vente', '0000-00-00 00:00:00', NOW(), '$statut_places', '', '0', '0', NULL , NULL , 'ok', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '')" ;
						if ($i < ($nombre_places_kidonaki-1))
						$req_copies_objet.=' , ' ;			
					}
					
					//echo $req_copies_objet ;
					mysql_query("$req_copies_objet") 
				or die('Erreur écriture 5 : ' . mysql_error() . ' ');
				
				}
				

				// ++++++++++++++++++++++++++++++++++++++++++++++++++
				// Appel de la fonction de transfert des images
				// ++++++++++++++++++++++++++++++++++++++++++++++++++
				// Quels sont les images disponibles pour l'événement ? Vérifier en premier que la première image existe
				if(isset ($pic_event[1]) AND $pic_event[1] == 'set')
				{
					require '../kidonaki/fct_transfert_pics.php';
					$num_image = $id ;
				}
				for($i=1 ; $i<=3 ; $i++)
				{
					if(isset($pic_event[$i]) AND $pic_event[$i] == 'set')
					{
						$num_vignette = $i ;
						$nom_image = 'event_' . $num_image . '_' . $num_vignette . '.jpg' ;
						$nom_vignette = 'vi_event_' . $num_image . '_' . $num_vignette . '.jpg' ;
						
						transferer_image_sur_kido ($nom_vignette, $id_nouvel_article, 0) ;
						$test_fonction = transferer_image_sur_kido ($nom_image, $id_nouvel_article, 1) ;
						if( $test_fonction == 'transfert_ok')
						{
							echo 'Image ' . $nom_image . ' transférée <br />' ;
						}
					}
				}

				
				
				// ++++++++++++++++++++++++++++++++++++++++++++++++++
				// Pour cet événement, updater sa case "article_kidonaki" de DLP
				// ++++++++++++++++++++++++++++++++++++++++++++++++++
				// Connexion :
				//--- mysql_close($db2dlp);
				require '../inc_db_connect.php';

				mysql_query("UPDATE ag_event SET article_kidonaki = '$id_nouvel_article' 
				WHERE id_event = '$id' LIMIT 1 ") or die ('Erreur écriture 34 : ' . mysql_error() . ' ');
			
				//echo stripslashes($concat_description) ; // TEST
				//$concat_description = utf8_encode($concat_description) ;
				
				


				// Message de confirmation :
				echo '<br /><br /><div class="info"><br />Le lot de places est à présent encodé sur le site KIDONAKI 
				à <a href="http://www.kidonaki.be/spip.php?article' . $id_nouvel_article . '">cette adresse</a>. <br />
				Si vous désirez effectuer des modifications, rendez-vous sur 
				<a href="http://www.kidonaki.be/spip.php?page=login">votre compte Kidonaki</a>.<br /><br />
				<a href="listing_events_gp.php?lieu=' . $donnees_test['lieu_event'] . '">Retour à la liste des événements</a>
				</div>' ;
			}
			else
			{
				// Au moins un champ n'a pas été encodé correctement
				$au_moins_un_champ_mauvais = 'Au moins un champ du formulaire n\'a pas été complété correctement<br />' ;
			}
		}
		// Si le bouton ENREGISTRER n'a pas été enfoncé
		else
		{
			// Reprendre les infos de la DB pour alimenter le formulaire
			$nom_event = $nom_event_db ;
			$date_fin_kido = $date_fin_kido_db ;
			//$description_event = $description_event_db ;
		}
	// --------------------------------------------------------------------
	// ----------------------- AFFICHER FORMULAIRE ------------------------
	// --------------------------------------------------------------------
	?>
	<p align="center" class="rouge"> <?php echo $au_moins_un_champ_mauvais ; ?> </p>
	<form id="cases_jours" name="cases_jours" method="post" action="">
	  <table width="750" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
		<tr>
		  <th colspan="2"> <?php 
			echo  $nom_event_db . '<br />' ;
			echo '(du '. $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut . ' 
			au '. $JJ_fin . '-' . $MM_fin . '-' . $AAAA_fin . ')<br />' ;
			?>			</th>
		</tr>
		<tr>
		  <td colspan="2" valign="top"><div align="center">Ce formulaire vous permet de mettre en vente des places pour cet &eacute;v&eacute;nement de fa&ccedil;on tr&egrave;s simple. <br />
		      <strong>Attention</strong>, vous ne pouvez cr&eacute;er qu'un seul et unique lot de une ou plusieurs places gr&acirc;ce &agrave; ce formulaire. Les modifications ult&eacute;rieures ne pourront se faire que via votre compte Kidonaki sur le site <a href="http://www.kidonaki.be/" target="_blank">www.kidonaki.be</a>.
			 <br /> <a href="http://www.kidonaki.be/spip.php?article3953" target="_blank">Retrouvez le mode d'emploi et les conseils à cette page</a>
			  <br /><a href="http://www.kidonaki.be/spip.php?article3946" target="_blank">Conditions d'utilisation</a>
			  
			  </div></td>
	    </tr>
		<tr>
		  <td valign="top">Nom de l'&eacute;v&eacute;nement <br />
		  <?php if (isset ($error_nom_event) AND $error_nom_event != NULL) {echo $error_nom_event ; } ?>		  </td>
		  <td valign="top">
		  <?php 
		  //echo '<input name="nom_event" type="text" id="nom_event" value="' . $nom_event . '" size="70" maxlength="200" />'; 
		  echo $nom_event ; 
		  ?>		  </td>
		</tr>
		<tr>
		  <td valign="top">Remarque sur la proc&eacute;dure de mise &agrave; disposition des places  <br />
		  <?php if (isset ($error_procedure_places) AND $error_procedure_places != NULL) {echo $error_procedure_places ; } ?>		  </td>
		  <td valign="top">
		  <textarea name="procedure_places" rows="10" id="procedure_places" style="width: 600px; "><?php 
		  if (isset($procedure_places) AND $procedure_places != NULL) 
		  {
		  	echo br2nl(stripslashes($procedure_places)); 
		  }
		  else
		  {
		  	// texte par défaut
			echo 'Pour obtenir votre place, il vous suffira d\'imprimer un email spécial qui vous sera envoyé lorsque l\'association aura validé la réception de votre payement. Cet email imprimé sera donc un "bon à valoir", faisant office de place.
N\'oubliez cependant pas de réserver! Vos places achetées sur Kidonaki ne seront valables que dans la période de validité indiquée (voir annonce) et uniquement pour les jours où le lieu culturel n\'affiche pas encore complet.' ;
		  }
		  ?></textarea>		  </td>
		</tr>
		<tr>
		  
		  <td valign="top">Nombre de places que vous mettez en vente<br />
  		  <?php if (isset ($error_nombre_places_kidonaki) AND $error_nombre_places_kidonaki != NULL) 
		  {
		  	echo $error_nombre_places_kidonaki ; 
		  } ?>			</td>
		  <td valign="top">
	      <input name="nombre_places_kidonaki" type="text" id="nombre_places_kidonaki" value="<?php 
			if (isset ($nombre_places_kidonaki) AND $nombre_places_kidonaki != 0)
		{echo $nombre_places_kidonaki;}?>" size="3" maxlength="2"> (fois 1 place) </td>
		</tr>
		
		<tr>
		  <td valign="top">Prix pour une place<br />
          <?php if (isset ($error_prix_place_kidonaki) AND $error_prix_place_kidonaki != NULL) 
		  {
		  	echo $error_prix_place_kidonaki ; 
		  } ?></td>
		  <td valign="top"><input name="prix_place_kidonaki" type="text" id="prix_place_kidonaki" value="<?php 
			if (isset ($prix_place_kidonaki) AND $prix_place_kidonaki != 0)
		{echo $prix_place_kidonaki;}?>" size="5" maxlength="4" /> 
		  (&euro;) </td>
	    </tr>
		<tr>
		  <td colspan="2" valign="top">Choisissez le projet ou l'association &agrave; soutenir. Plus de d&eacute;tails sur les <a href="http://www.kidonaki.be/-Associations-.html" target="_blank">projets kidonaki</a> <br />
		  <p>
		  <?php if (isset ($error_projet_kido) AND $error_projet_kido != NULL) 
		  {
		  	echo $error_projet_kido ; 
		  } 
		  
		  echo '<select name="projet_kido">
		  <option value="vide">Choisissez un projet à soutenir</option>'; 
		while ($donnees_kido_1 = mysql_fetch_array($reponse_kido_1))
		{
			// Liste déroulante des projets à soutenir
			echo '<option value="' . $donnees_kido_1['id_rubrique'] . '"';		
			// Faut-il preselectionner
			if (isset($projet_kido_form) AND $projet_kido_form == $donnees_kido_1['id_rubrique'])
			{
				echo 'selected';
			}
			echo '>' . raccourcir_chaine(preg_replace($pattern_list_kido, $replacement_list_kido, $donnees_kido_1['titre']), 70).'</option>'; 
		}
		echo '</select>';
		?>
		</p>
	      </td>
	    </tr>
		
		<tr>
		  <td colspan="2" valign="top">Les places seront valables pour assister aux repr&eacute;sentation de cet &eacute;v&eacute;nement : <br />
		  du 
		  		  
		<input name="jour_debut_kido" type="text" id="jour_debut_kido" value="<?php 
		if (isset ($jour_debut_kido) AND $jour_debut_kido != 0)
		{echo $jour_debut_kido;}
		else {echo $JJ_debut;} ?>" size="2" maxlength="2">		 

		<input name="mois_debut_kido" type="text" id="mois_debut_kido" value="<?php 
		if (isset ($mois_debut_kido) AND $mois_debut_kido != 0)
		{echo $mois_debut_kido;}
		else {echo $MM_debut;} ?>" size="2" maxlength="2">		 

		<input name="annee_debut_kido" type="text" id="annee_debut_kido" value="<?php 
		if (isset ($annee_debut_kido) AND $annee_debut_kido != 0)
		{echo $annee_debut_kido;}
		else {echo $AAAA_debut;} ?>" size="4" maxlength="4">		 
		
		<?php if (isset ($error_date_debut_kido) AND $error_date_debut_kido != NULL) 
		{
			echo ' ' . $error_date_debut_kido ; 
		} ?>
		
		<br /> au 
		  
		<input name="jour_fin_kido" type="text" id="jour_fin_kido" value="<?php 
		if (isset ($jour_fin_kido) AND $jour_fin_kido != 0)
		{echo $jour_fin_kido;}?>" size="2" maxlength="2">		 

		<input name="mois_fin_kido" type="text" id="mois_fin_kido" value="<?php 
		if (isset ($mois_fin_kido) AND $mois_fin_kido != 0)
		{echo $mois_fin_kido;}?>" size="2" maxlength="2">		 

		<input name="annee_fin_kido" type="text" id="annee_fin_kido" value="<?php 
		if (isset ($annee_fin_kido) AND $annee_fin_kido != 0)
		{echo $annee_fin_kido;}?>" size="4" maxlength="4">		

		<?php if (isset ($error_date_fin_kido) AND $error_date_fin_kido != NULL) 
		{
			echo ' ' . $error_date_fin_kido ; 
		} ?>		
		  
		  </td>
	    </tr>

		<tr>
		  <td colspan="2" valign="top"><div align="center"> <br /> 
			  <input name="modif_form" type="submit" id="modif_form" value="Enregistrer">
			  <br /> <br />
		  </div></td>
		</tr>
	  </table>
	</form>
	<br />
	<?php 


	}
	//--- mysql_close($db2dlp);
} 

?>
<p>&nbsp;</p>
</body>
</html>
