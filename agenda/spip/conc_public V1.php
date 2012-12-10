
<?php 
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';

$page_contenant_concours = '-Concours,95-' ; // 888888
//$page_contenant_concours = 'conc_test.php' ;

$form_masquage = false ;

//--------------------------------------------------------------------------------------
// Si le joueur a cliqué sur un lien pour jouer, lui faire compléter le formulaire
//--------------------------------------------------------------------------------------

if (isset($_GET['id']) AND preg_match('/[0-9]$/', $_GET['id'])  AND
    isset($_GET['lot']) AND preg_match('/[0-9]$/', $_GET['lot']))
{
	$id_conc = htmlentities($_GET['id'], ENT_QUOTES);
	$id_lot = htmlentities($_GET['lot'], ENT_QUOTES);

	// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
	// Préparation image anti-robots
	$recevoir_publication = '' ;
	
	$session = md5(time()); // numero d'identification du visieur
	$ip = $_SERVER['REMOTE_ADDR'] ;
	$time_stamp_joueur = time();
	
	// code aléatoire pour l'image generee :
	$nb_car = 3 ;
	$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
	$txt = str_shuffle($txt);
	$code = substr($txt, 10, $nb_car);
	
	mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) 
	VALUES ('$session','$code','$time_stamp_joueur','$ip')");

	// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR

	$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE id_conc = $id_conc");
	$donnees = mysql_fetch_array($reponse) ;

	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Cette entrée n\'existe pas</div><br>' ;
	}
	else
	{		
		$pic_conc = $donnees['pic_conc'] ;
		$nom_event_conc = stripslashes ($donnees ['nom_event_conc']) ;
	
		echo '[ref ' . $id_conc . '-' . $id_lot  . ']' ;

		// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
		// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
		//---------------------------------------------------------
		// Si bouton enfoncé, alors lancer l'analyse des données
		//---------------------------------------------------------
		if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Jouer !')) 
		{
			
			//---------------------------------------------------------
			// Verification des données entrées par l'utilateur
			//---------------------------------------------------------
			$rec = ''; 
		
			// ------------------------------------------------------------
			// TEST DU NOM
			if (isset($_POST['nom_joueur']) AND ($_POST['nom_joueur'] != NULL)) 
			{
				$nom_joueur = stripslashes(htmlentities($_POST['nom_joueur'], ENT_QUOTES));
			}
			else
			{
				$rec .= '- Vous devez introduire un nom <br>';
				$error_nom_joueur = '<div class="error_form">Vous devez introduire un nom</div>';
			}
		
		
			// ------------------------------------------------------------
			//  TEST EMAIL
			if ((isset($_POST['email_joueur']) 
			AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_joueur']))))
			{
				$email_joueur = $_POST['email_joueur'];
			}
			else
			{
				$email_joueur = '';
				$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
				$error_email_joueur_event = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
			}
		
			

		
			
			// ------------------------------------------------------------
			// Test du code recopié à partir de l'image cryptée
			
			$get_sess = $_POST['sid'];
		
			$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
			$donnees = mysql_fetch_array($reponse);
			
			if ($donnees ['code_crypt']=="" OR $donnees ['code_crypt']!=$_POST['code']) // Code non valide // (1==2) 
			{
				$code = '';
				$rec .= '- erreur image';
				$error_image_crypt = '<div class="error_form">Le code que vous avez recopié à partir 
				de l\'image est incorrect</div>';
			}
			else // Code valide
			{
				// Suppression de la DB
				$query = mysql_query("DELETE FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
			}
			
		
			//----------------------------------------------------------------------------------------------
			// Enregistrement de la demande de participation du joueur (si le joueur peut participer)
			//----------------------------------------------------------------------------------------------
			if ($rec == NULL) // Enregistrement les données dans la DB 
			{
				// Est-ce la première fois que le joueur tente sa chance pour ce spectacle ?
				// WHERE email= email et spectacle = spectacle

				$reponse_test_1 = mysql_query("SELECT COUNT(*) AS test_exist_1 FROM $table_ag_conc_joueur 
				WHERE mail_joueur_conc = '$email_joueur' AND id_fiche_conc_joueur = '$id_conc'") or die (mysql_error());
				$donnees_test_1 = mysql_fetch_array($reponse_test_1);
		
				if ($donnees_test_1['test_exist_1'] > 0) 
				{ // A déjà tenté sa chance pour cet événement
					echo '<div class="alerte"><b>Vous avez déjà tenté votre chance pour cet événement</b>. <br />
					Vous ne pouvez pas participer plus d\'une fois par jour au concours 
					et il ne vous est pas possible de jouer plusieurs fois pour un même événement. <br /> <br />
					<a href="' . $page_contenant_concours . '?id">Jouer pour un autre événement</a>
					</div>' ;

					$form_masquage = true ;
				}
				else
				{ // Pas encore participé -> tester la 2e condition : a-t-il déja participé à un jeu aujourd'hui ?
					
					// Date actuelle :
					$date_actuelle = date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));
					//echo '<p>' . $date_actuelle . ' </p>';
					
					$reponse_test_2 = mysql_query("SELECT COUNT(*) AS test_exist_2 FROM $table_ag_conc_joueur 
					WHERE mail_joueur_conc = '$email_joueur' AND time_stamp_joueur > '$date_actuelle'");
					$donnees_test_2 = mysql_fetch_array($reponse_test_2);
					
					if ($donnees_test_2['test_exist_2'] > 0)
					{
						 // Il ne peut plus jouer
						echo '<div class="alerte"><b>Vous avez déjà participé à un concours aujourd\'hui</b>. <br />
						Vous ne pouvez pas participer plus d\'une fois par jour au concours 
						et il ne vous est pas possible de jouer plusieurs fois pour un même événement. </div>' ;
					}
					else 
					{
						// OK, le joueur peut tenter sa chance
						
						$approuv_check = mysql_query("INSERT INTO `$table_ag_conc_joueur` 
						(`id_fiche_conc_joueur` , `lot_conc_joueur` , `nom_joueur_conc` , `mail_joueur_conc` , `time_stamp_joueur` , `nature_joueur`) 
						VALUES ('$id_conc', '$id_lot', '$nom_joueur', '$email_joueur', '$time_stamp_joueur', 'jou01')")
						 or die(mysql_error());
						 
						// $dernier_id_table_ag_conc_joueur = mysql_insert_id() ;			 
					
						if ($approuv_check)
						{
							// recherche date cloture pour la rappeler au joueur
							$rep = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE id_conc = $id_conc");
							$donn = mysql_fetch_array($rep);
							$date_cloture = $donn['cloture_conc'] ;

							$date_cloture_annee = date('Y',$date_cloture);
							$date_cloture_mois = date('m',$date_cloture);
							$date_cloture_jour = date('d',$date_cloture);
							
							$form_masquage = true; // Masquer le formulaire
							echo '<div class="info">Votre participation au concours a bien été prise en compte. 
							A la cloture de ce concours, le ' . $date_cloture_jour . '/' . $date_cloture_mois . '/' .
							$date_cloture_annee . ', vous recevrez un e-mail vous indiquant si vous avez remporté les places.
							<br /><a href="-Agenda-">Retour au site</a>
							</div><br>' ;
							
							
							// ------------------------------------------------------------
							// RECEVOIR LETTRE D'INFO DE DLP (Philippe) si la case est cochée
							if (isset($_POST['recevoir_publication']) AND ($_POST['recevoir_publication'] == 'ok')) 
							{
								// echo '<br>|||----------- ' . $nom_joueur ; // nom du visiteur
								// echo '<br>|||----------- ' . $email_joueur ; // adresse e-mail du visiteur

								//----- abonner à la mailing liste DLP tous
								$adrm = addslashes($email_joueur);
								$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$adrm' AND lletr='DPts' AND letat='5'";
								$resp = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
								if (! mysql_num_rows($resp)) {
									$sql = time();
									$sql = "INSERT INTO cmsnletter SET ladrm='$adrm',lletr='DPts',letat='5',lcode='$sql'";
									$resp = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
								}
								unset($adrm, $sql, $resp);
							}
						}
						
						else
						{
							echo '<div class="alerte">Une erreur s\'est produite. 
							Veuillez recommencer l\'opération ultérieurement</div><br>' ;
						}
					}
				}
			}
			else
			{
				echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
			}
		}
		// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
		// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT


		// ///////////////////////////////////////////////////////////
		// Afficher le formulaire
		// ///////////////////////////////////////////////////////////
		
		$form_concat = '' ;
		$tab = '' ;
		if ($form_masquage == false )
		{
			$tab = '<div class="cloture_non">';
			
			//*****************
			// Image			
		
			if (isset ($pic_conc) AND $pic_conc == 'set' )
			{		
				$tab.= '<span class="grande_image_conc"><img src="agenda/' . $folder_vignettes_concours . 'conc_' . $id_conc . '_1.jpg" 
				style = "border: 1px solid #000000; 	background-color: #FFFFFF; padding: 1px; " ></span>';
			}
	

			
			//*****************
			// Titre
			$tab.= '<div class="titre_conc" align="center">Vous jouez pour l\'événement : <br />' . 
			$nom_event_conc .' <br /><a href="' . $page_contenant_concours . '" title="Corriger"> 
			<img src="agenda/design_pics/conc_retour.gif" align="top" /></a><span class="descriptif_concours"><br /></div> <br />' ;


			// Repréciser l'heure au joueur
			$var_lot_unserialized_confirm = unserialize($donnees['lots_conc']) ;
			$conf_date = $var_lot_unserialized_confirm[$id_lot]['new_date_lot'] ;
			
			/*echo '<pre>' ;
			var_dump($var_lot_unserialized_confirm) ;
			echo '</pre>' ;	*/
		
			$form_concat = '<form name="form1" method="post" action="">
			<br /><div align="center"><b>Si vous êtes disponible le ' . substr($conf_date, 8, 2) . '/' . 
					substr($conf_date, 5, 2) . '/' . 
					substr($conf_date, 0, 4) . ' à ' . 
					$var_lot_unserialized_confirm[$id_lot]['new_heure_lot'] . '</span>, 
					compl&eacute;tez le formulaire</b></div><br /> <br />';

			//_________ NOM _________
			$form_concat.= 'Prénom et nom <span class="champ_obligatoire">*</span> : 
			<input name="nom_joueur" type="text" id="nom_joueur" ';
			if (isset($nom_joueur))
			{
				$form_concat.= 'value="' . $nom_joueur . '"';
			}
			$form_concat.= ' size="30" maxlength="30"> <br />';
			// Message erreur
			if (isset ($error_nom_joueur) AND $error_nom_joueur != NULL) {$form_concat.= $error_nom_joueur; }
		
		
		
			//_________ EMAIL _________
			$form_concat.= '<br />Adresse e-mail<span class="champ_obligatoire">*</span> : 
			<input name="email_joueur" type="text" id="email_joueur" ';
			if (isset($email_joueur))
			{
				$form_concat.= 'value="' . $email_joueur . '"';
			}
			$form_concat.= ' size="30" maxlength="350">';
			// Message erreur
			if (isset ($error_email_joueur_event) AND $error_email_joueur_event != NULL) {$form_concat.= $error_email_joueur_event ; } 
		
		
			//_________ IMAGE ROBOTS _________
			$form_concat.= ' <br /> <br /> Recopier le code de l\'image<span class="champ_obligatoire">*</span> : 
			<input name=code type=text id="code" size="3" maxlength="3"> 
			<img src=agenda/user_admin/ins/im_gen.php?session=' . $session . ' hspace="10" align="top">';
			// Message erreur
			if (isset ($error_image_crypt) AND $error_image_crypt != NULL) {$form_concat.= $error_image_crypt ; } 
		
			$form_concat.= '<br />
			<input type=hidden name=sid value=' . $session . '><div align="center"> <br /> <br />
			<input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Jouer !">
			<br /></div>
			<label>
			<div align="center"> <br /> <br />
			<input type="checkbox" name="recevoir_publication" value="ok" checked="checked" />
			Je souhaite recevoir la lettre d\'information de 
			<a href="http://www.demandezleprogramme.be/">demandezleprogramme.be</a></div>
			</label>
			</form>' ;

		}
		$tab.= $form_concat . ' </div>' ;
		echo $tab;
	}
}
else
{
	//---------------------------------------------------------------------------
	// Listing des concours (n'est pas affiché quand le visiteur joue pour un élément sélectionné
	//---------------------------------------------------------------------------
	
	$tab = '';
	
	// DEBUT LISTE
	$tab.='<br /><div class="conc_conteneur">';
			
	
	
	//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
	// Listing des concours
	//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii 
	$limit_afficher = (time() - (3600*24*3000)); // Date actuelle moins quelques jours
	
	$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE lots_conc LIKE '%jou01%' 
	AND cloture_conc > $limit_afficher
	AND (flags_conc LIKE '%actif%' OR flags_conc LIKE '%cloture%')
	ORDER BY cloture_conc DESC ");
	while ($donnees = mysql_fetch_array($reponse))
	{
		$id_conc = $donnees['id_conc'] ;
		
		
		// ____________________________________________
		// NOM + CLOTURE CONCOURS
	
		$date_cloture = $donnees ['cloture_conc'];
		
		$date_cloture_annee = date('Y',$date_cloture);
		$date_cloture_mois = date('m',$date_cloture);
		$date_cloture_jour = date('d',$date_cloture);
		$date_cloture_heure = date('H',$date_cloture);

		/*$date_cloture_annee = substr($date_cloture, 0, 4);
		$date_cloture_mois = substr($date_cloture, 5, 2);
		$date_cloture_jour = substr($date_cloture, 8, 2);
		$time_debut=date(mktime(0, 0, 0, $date_cloture_mois, $date_cloture_jour, $date_cloture_annee));*/
	
		// Date actuelle
		$date_actuelle= time();
		
		/*echo $date_actuelle . ' --- ' . $time_debut . ' --- ' . $time_fin . '<br>';*/
	
		// La date actuelle fait-elle partie de la période de représentation ?
		
		if ($date_actuelle < $date_cloture)
		{
			$tab.='<div class="cloture_non">' ;
			$cloture = 'en_cours' ;
		}
		else
		{
			$tab.='<div class="cloture_oui">' ; 
			$cloture = 'fini' ;
		}
	
	
	// PHOTO
		if (isset ($donnees['pic_conc']) AND $donnees['pic_conc'] == 'set' )
		{		
			$tab.= '<span class="conc_photo"><a href="agenda/' . $folder_vignettes_concours . 'conc_' . $id_conc . 
			'_1.jpg" style="text-decoration:none;"><img src="agenda/' . $folder_vignettes_concours . 'vi_conc_' . $id_conc . 
			'_1.jpg" style = "border: 1px solid #000000; 	background-color: #FFFFFF; padding: 1px; " ></a></span>';
		}


		// TITRE
		
		$nom_event_conc = stripslashes ($donnees ['nom_event_conc']) ;
		$tab.= '<span class="bloc_texte"><span class="titre_conc">' . $nom_event_conc .'</span><br />' ;
		$tab.= '<div align="right">(réf ' . $id_conc . ')</div><br />' ;
	
		// ____________________________________________
		// TEXTE DESCRIPTIF
	
		if (isset ($donnees['description_conc']) AND $donnees['description_conc'] != NULL )
		{		
			$tab.= '<span class="descriptif_concours">' . $donnees['description_conc'] . '</span>' ;
		}
	

		// ____________________________________________
		// BOUTON LIEN INTERNE
	
		if (isset ($donnees['event_dlp_conc']) AND $donnees['event_dlp_conc'] != 0 )
		{		
			$tab.= '<br /> <br /> <a href="-Detail-agenda-?id_event=' . $donnees['event_dlp_conc'] . '">
			<img src="agenda/design_pics/conc_tout_savoir.gif" align="top" /></a>';			
		}

		// ____________________________________________
		// BOUTON LIEN EXTERNE
	
		if (isset ($donnees['lien_externe_conc']) AND $donnees['lien_externe_conc'] != NULL )
		{		
			$tab.= '<br /> <br /> <a href="' . $donnees['lien_externe_conc'] . '"> &gt;&gt; Voir les infos sur le site de l\'événement
			</a><br />';			
		}
			
		
		// ____________________________________________
		// DATE DE CLOTURE
		
		if ($cloture == 'fini')
		{
			$tab.= '<br /> <br /> <span class="date_cloture">Concours Clôturé</span><br /> <br /> '; 
		}
		else
		{
			$tab.= '<br /> <br /> <span class="date_cloture">Date de cloture : 
			le ' . $date_cloture_jour . ' ' . $NomDuMois[$date_cloture_mois] . ' ' . $date_cloture_annee . ' 
			à ' . $date_cloture_heure .'h00 </span><br /> <br /> '; 
		}


	
		// ____________________________________________
		// LOTS compris dans ce concours
		
		if (isset($donnees['lots_conc']) AND ($donnees['lots_conc'] != NULL))
		{
			$tab.= '<span class ="liste_lots">';
		
			$var_lot_unserialized = unserialize($donnees['lots_conc']) ;
				
			$i_lot = 0; // sera incrémenté dans la boucle
			foreach ($var_lot_unserialized as $element_lot)
			{
				//sélectionner seulement les joueurs "groupe 01" (public)
				if ($element_lot['groupe_joueur'] == 'jou01')
				{	
					$tab.= 'Le ' . substr($element_lot['new_date_lot'], 8, 2) . '/' . 
					substr($element_lot['new_date_lot'], 5, 2) . '/' . 
					substr($element_lot['new_date_lot'], 0, 4) . ' à ';
										
					$tab.= substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
					substr($element_lot['new_heure_lot'], 3, 2) . '
					(' . str_pad($element_lot['nombre_places'], 3, "0", STR_PAD_LEFT) . ' fois 2 places) ';
					
					// Ajout du lien si le concours est encors en cours
					if ($date_actuelle <= $date_cloture)
					{
						$tab.= '<a href="' . $page_contenant_concours . '?id=' . $id_conc . '&amp;lot=' . $i_lot . '">
						<img src="agenda/design_pics/conc_jouer.gif" align="top" /></a><br />';
					}
					else
					{
						$tab.= '<br />';
					}
					//$tab.= '[' . $groupes_joueurs[$element_lot['groupe_joueur']] . ']';
					/*print_r($element_lot);
					echo '<br /><br /><br />' ;*/
					$i_lot++ ;
				}
			}
			$tab.='</span>';
		}
		else
		{
			$tab.='<br /><div class="alert">ERREUR (Aucun lot pour ce concours)</div>';
		}
		$tab.='</span> <div class="float_stop"> <br /> </div> </div> <br />';
	}
	echo $tab . '</div>' ;
}

?>

