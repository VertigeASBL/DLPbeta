<?php
require 'agenda/inc_var.php';
require 'agenda/inc_var_dist_local.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';

// --------------------------------------------------------------------------------
// Le joueur est-il un SPECTATEUR authentifié ou un simple visiteur ?
// --------------------------------------------------------------------------------
if (isset($_SESSION['group_admin_spec']) AND $_SESSION['group_admin_spec'] == 1)
{
	$qui_joueur = 'spectateur' ; // Le joueur est un SPECTATEUR authentifié
	$id_spectateur = $_SESSION['id_spectateur'] ;
	$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
	$donnees = mysql_fetch_array($reponse);
		
	$prenom_spectateur = $donnees ['prenom_spectateur'];
	$nom_spectateur = $donnees ['nom_spectateur'];
	$pseudo_spectateur = $donnees ['pseudo_spectateur'];
	$e_mail_spectateur = $donnees ['e_mail_spectateur'];
	$tel_spectateur = $donnees ['tel_spectateur'];
	$log_spectateur = $donnees ['log_spectateur'];
	$pw_spectateur = $donnees ['pw_spectateur'];

	$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];

	$description_courte_spectateur = $donnees ['description_courte_spectateur'];
	$description_longue_spectateur = $donnees ['description_longue_spectateur'];

	$pic_spectateur = $donnees ['pic_spectateur'];

	$artiste_prefere_spectateur = $donnees ['artiste_prefere_spectateur'];
	$lieu_prefere_spectateur = $donnees ['lieu_prefere_spectateur'];
	
	// Si le compte est bloqué, empêcher le spectateur d'y accéder
	if ($donnees['compte_actif_spectateur'] == 'non')
	{
		echo '<br /> <div class="alerte"><br />Votre comte a été bloqué par un administrateur du site. <br />Vous ne pouvez plus participer en tant que Spectateur. Pour plus d\'infos : info@demandezleprogramme.be<br /><br /></div>' ;
		exit () ;
	}
	
	// Si le compte est créé, mais pas totalement complété ($compte_actif_spectateur = "new"), inviter le Spectateur à finaliser la chose via sin admin
	if ($donnees['compte_actif_spectateur'] == 'new')
	{
		echo '<br /> <div class="alerte"><br />Votre comte n\'est pas encore totalement paramétré. Veuillez vous rendre dans votre espace d\'administration personnel et compléter votre profil : 
		<a href="agenda/spectateurs_admin/edit_profile_spectateur.php">espace personnel</a>.<br />
		Ensuite, il vous sera possible de participer aux concours tout en multipliant vos chances de gain !<br /><br /></div>' ;
		exit () ;
	}
}
else
{
	$qui_joueur = 'visiteur' ; // Le joueur est un simple visiteur
}

$form_masquage = false ;


// ---------------------------------------
// Le spectateur veut-il se logguer ?
// ---------------------------------------
if (isset($_GET['login']) AND $_GET['login'] == 'go') 
{
	require 'agenda/auth/auth_fonctions.php';  
	test_spectateur_acces_in_spip (1) ;
}


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

	$erreur_url = '' ;
	if (empty ($donnees))
	{
		$erreur_url.= '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas</div><br>' ;
	}
	else
	{
		$lots_conc_test_url = $donnees['lots_conc'] ;
		$array_lots_conc_test_url = unserialize($lots_conc_test_url) ; // récupération de la variable Lot de la DB
		//print_r($array_lots_conc_test_url);
		$fiwwwee =  $array_lots_conc_test_url[$id_lot]['nombre_places'] ;
		
		if (empty($fiwwwee) OR ($fiwwwee == 0))
		{
			$erreur_url.= '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Ce lot n\'existe pas</div><br>' ;
		}
	}
	if ($erreur_url != '')
	{
		echo $erreur_url ;
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
				// Tester si le nom est le pseudo d'un SPECTATEUR, s'il est loggé, OK, sinon, refuser
				if (empty ($_SESSION['group_admin_spec']) OR$_SESSION['group_admin_spec'] == NULL)
				{
					$reponse_test_nom_spect = mysql_query("SELECT id_spectateur FROM $table_spectateurs_ag WHERE pseudo_spectateur = '$nom_joueur'");
					$donnees_test_nom_spect = mysql_fetch_array($reponse_test_nom_spect);
					if ($donnees_test_nom_spect ['id_spectateur'] != NULL)
					{
						$error_nom_joueur = '<div class="error_form">Vous utilisez le pseudonyme d\'un spectateur enregistré sur le site. 
						S\'il s\'agit de vous, veuillez vous authentifier via <a href="' . $racine_domaine . 'agenda/spectateurs_admin/votre_menu_spectateur.php">cette page</a>.</div>' ;
						$rec .= '- Vous utilisez le pseudonyme d\'un spectateur enregistré sur le site. <br>';
					}
				}
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
				
				// Tester si l'adresse email appartient au SPECTATEUR, s'il est loggé, OK, sinon, refuser
				if (empty ($_SESSION['group_admin_spec']) OR $_SESSION['group_admin_spec'] == NULL)
				{
					$reponse_test_email_spect = mysql_query("SELECT id_spectateur FROM $table_spectateurs_ag WHERE e_mail_spectateur = '$email_joueur'");
					$donnees_test_email_spect = mysql_fetch_array($reponse_test_email_spect);
					if ($donnees_test_email_spect ['id_spectateur'] != NULL)
					{
						$error_email_joueur_event = '<div class="error_form">Vous utilisez une adresse email appartenant 
						à un spectateur enregistré sur le site. 
						S\'il s\'agit de vous, veuillez vous authentifier via <a href="' . $racine_domaine . 'agenda/spectateurs_admin/votre_menu_spectateur.php">cette page</a>.</div>' ;
						$rec .= '- Vous utilisez une adresse email appartenant à un spectateur enregistré sur le site. <br>';
						$email_joueur = '' ;
					}
				}
				
				// Tester si c'est un tricheur (selon la liste de Caro) :
				require 'agenda/concours/test_tricheurs/tricheurs_array.php';
				foreach ($liste_tricheurs as $un_tricheur)
				{
					if ($email_joueur == $un_tricheur)
					{
						echo '
						<div class="alerte">L\'équipe de Demandez le programme considère que vous avez outrepassé le règlement des concours : vous avez participé abusivement à certains concours en utilisant différentes adresses email.<br />
						En cas d\'erreur de notre part, veuillez contacter : <a href="info@demandezleprogramme.be">info@demandezleprogramme.be</a>.<br />Le cas échéant, votre bannissement est considéré comme définitif.<br /><br />
						<em>L\'équipe de Demandez le programme</em>
						</div>' ;
						
						$rec .= '- Tricherie <br>';

					}
				}
				

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
		
			$reponse_captcha = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
			$donnees_captcha = mysql_fetch_array($reponse_captcha);
			
			if ($donnees_captcha ['code_crypt']=="" OR $donnees_captcha ['code_crypt']!=$_POST['code']) // Code non valide // (1==2) 
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
						VALUES ('$id_conc', '$id_lot', '$nom_joueur', '$email_joueur', '$time_stamp_joueur', '$public_cible')")
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
			$tab.='<div class="cloture_non">';
	

			
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
			<img src="agenda/design_pics/' . $bouton_retour . '" align="top" /></a><span class="descriptif_concours"><br /></div> <br />' ;

			// Repréciser l'heure au joueur
			$var_lot_unserialized_confirm = unserialize($donnees['lots_conc']) ;
			$conf_date = $var_lot_unserialized_confirm[$id_lot]['new_date_lot'] ;
			
			/*echo '<pre>' ;
			var_dump($var_lot_unserialized_confirm) ;
			echo '</pre>' ;	*/
		
			$form_concat = '<form name="form1" method="post" action="">
			<br /><div align="center"><b>Si vous jouez pour des places gratuites, 
			assurez-vous d\'être libre(s) aux dates déterminées</b></div><br /> <br />';

			//_________ NOM _________
			if ($qui_joueur == 'spectateur')
			{
				$form_concat.= 'Votre pseudo : <strong>' . $_SESSION['pseudo_spectateur'] . '</strong> 
				<input name="nom_joueur" type="hidden" id="nom_joueur" value="' . $_SESSION['pseudo_spectateur'] . '"><br />' ;
			}
			else
			{

				$form_concat.= 'Prénom et nom <span class="champ_obligatoire">*</span> : 
				<input name="nom_joueur" type="text" id="nom_joueur" ';
				if (isset($nom_joueur))
				{ $form_concat.= 'value="' . $nom_joueur . '"'; }
				$form_concat.= ' size="30" maxlength="30"> <br />';
				// Message erreur
				if (isset ($error_nom_joueur) AND $error_nom_joueur != NULL) {$form_concat.= $error_nom_joueur; }
			}
		
		
			//_________ EMAIL _________
			if ($qui_joueur == 'spectateur')
			{
				$form_concat.= 'Votre adresse e-mail : <strong> ' . $e_mail_spectateur . ' </strong> 
				<input name="email_joueur" type="hidden" id="email_joueur" value="' . $e_mail_spectateur . '">' ;
			}
			else
			{
				$form_concat.= '<br />Adresse e-mail<span class="champ_obligatoire">*</span> : 
				<input name="email_joueur" type="text" id="email_joueur" ';
				if (isset($email_joueur))
				{ $form_concat.= 'value="' . $email_joueur . '"'; }
				$form_concat.= ' size="30" maxlength="350">';
				// Message erreur
				if (isset ($error_email_joueur_event) AND $error_email_joueur_event != NULL) {$form_concat.= $error_email_joueur_event ; }
			}
		
		
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
			</label><br />
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
	
	

			// En tête avec photo du SPECTATEUR s'il est loggé + liens... -->
			
			$tab_2 = '<table class="pub" style="background-color: #D9D9D9" border="0" cellpadding="10" cellspacing="0">';
			if ($qui_joueur == 'spectateur')
			{
				$tab_2.= '<tr><td>' ;
				
								
				if (isset ($donnees ['pic_spectateur']) AND $donnees ['pic_spectateur'] == 'set' )
				{
					$tab_2.= '<img src="agenda/' . $folder_pics_spectateurs . 'spect_' . $id_spectateur . '_1.jpg" alt="Photo de ' . $prenom_spectateur . ' ' . $nom_spectateur . '" title="' . $prenom_spectateur . ' ' . $nom_spectateur . '" />';
				}
				else
				{
					if ($donnees ['sexe_spectateur'] == 0)
					{
						$tab_2.= '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_homme.jpg" alt="spectateur anonyme" />';
					}
					else
					{
						$tab_2.= '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_femme.jpg" alt="spectatrice anonyme" />';
					}
				}	



				$tab_2.= '</td><td>' ;
				$_tot_entrees = connaitre_nb_avis_spect ($pseudo_spectateur) ;
			
				// Correspondance AVIS postés <-> Grade et icone des spectateurs
				$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; 
				$tab_2.= '<p><strong>Bonne chance ' . $pseudo_spectateur . ' !</strong> <br /> <br />
				Vous jouez actuellement sous votre profil et vous multipliez ainsi vos chances par 
				<strong>' .  $result_fact_chance['valeur_facteur_chance'] . '</strong> 
				grâce à votre coefficient concours. </p></p></td></tr>' ;
			}
			else
			{
			$tab_2.= '<tr><td>
			<img src="agenda/design_pics/communaute_spectateurs.gif" alt="Communaut&eacute; des spectateurs de Demandezleprogramme"/></td>
		<td><p>Rejoignez la <a href="-Spectateurs-">communaut&eacute; des spectateurs</a> 
		afin d\'augmenter vos chances à ces concours. Si vous êtes membre, identifiez-vous avant de jouer !</p>
			<ul>
			  <li><a href="-Concours,95-?login=go">
			  Me connecter &agrave; mon compte (identification)</a></li>
			  <li><a href="agenda/spectateurs_admin/ins/a_1.php">M\'inscrire gratuitement</a> </li>
			</ul></td></tr>' ;
			}
	$tab_2.= '</table> <br  /> ' ;
	
	echo $tab_2 ;
	// Fin entête -----------------------------------------



	$tab = '';
	
	// DEBUT LISTE
	$tab.='<br /><div class="conc_conteneur">';
			
	
	
	///////////////////////////////////////////////////////////////////////////////
	// Afficher les concours EN COURS
	// A l'origine, les concours étaient affichés par ordre chronologique inversé...
	///////////////////////////////////////////////////////////////////////////////
	$limit_afficher = (time() - (3600*24*3000)); // Date actuelle moins quelques jours
	
	$public_cible_like = '%'. $public_cible . '%' ;

	$tab = '' ;

	$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE lots_conc LIKE '$public_cible_like' 
	AND cloture_conc > $limit_afficher
	AND flags_conc LIKE '%actif%'
	ORDER BY id_conc DESC ");
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
			<img src="agenda/design_pics/' . $bouton_tout_savoir . '" align="top" /></a>';			
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
			le ' . $date_cloture_jour . ' ' . $NomDuMois[$date_cloture_mois+0] . ' ' . $date_cloture_annee . ' 
			à ' . $date_cloture_heure .'h00 </span><br /> <br /> '; 
		}

	
		// ____________________________________________
		// LOTS compris dans ce concours
		
		if (isset($donnees['lots_conc']) AND ($donnees['lots_conc'] != NULL))
		{
			$tab.= '<span class ="liste_lots">';
			$tab.= '<ul>';
			$var_lot_unserialized = unserialize($donnees['lots_conc']) ;
				
			$i_lot = 0; // sera incrémenté dans la boucle
			foreach ($var_lot_unserialized as $element_lot)
			{
				//sélectionner seulement les joueurs du groupe spécifié
				//$tab.=  '--'.$i_lot.'--' ;
				if ($element_lot['groupe_joueur'] == $public_cible AND $element_lot['nombre_places'] !=0 )
				{	
					$tab.= '<li>' . $element_lot['decription_lot'] ;
					
					// Recherche du nombre de personnes qui ont déjà joué
					$reponse_nb_joueurs = mysql_query("SELECT COUNT(*) AS nb_joueurs_actuels FROM $table_ag_conc_joueur 
					WHERE id_fiche_conc_joueur = '$id_conc' AND  lot_conc_joueur = '$i_lot'");
					$donnees_nb_joueurs = mysql_fetch_array($reponse_nb_joueurs);
					$tab.= '<i> - actuellement <b>' . $donnees_nb_joueurs['nb_joueurs_actuels'] . ' </b>joueur(s)</i>' ;

		
					// Ajout du lien si le concours est encore en cours
					if ($date_actuelle <= $date_cloture)
					{
						$tab.= '<a href="' . $page_contenant_concours . '?id=' . $id_conc . '&amp;lot=' . $i_lot . '">
						<img src="agenda/design_pics/conc_jouer.gif" align="top" /></a><br />';
					}
					else
					{
						$tab.= '<br />';
					}
					$tab.= '</li>' ;
					//$tab.= '[' . $groupes_joueurs[$element_lot['groupe_joueur']] . ']';
					/*print_r($element_lot);
					echo '<br /><br /><br />' ;*/
				}
				$i_lot++ ;
			}
			$tab.='</ul> </span>';
		}
		else
		{
			$tab.='<br /><div class="alert">ERREUR (Aucun lot pour ce concours)</div>';
		}
		$tab.='</span> <div class="float_stop"> <br /> </div> </div> <br />';
	}
	echo $tab . '<p><br /></p>' ;
	
		
	///////////////////////////////////////////////////////////////////////////////
	// Afficher les concours TERMINES
	///////////////////////////////////////////////////////////////////////////////
	$tab = '' ;
	
	$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE lots_conc LIKE '$public_cible_like' 
	AND cloture_conc > $limit_afficher
	AND flags_conc LIKE '%cloture%'
	ORDER BY id_conc DESC LIMIT 15");
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
			<img src="agenda/design_pics/' . $bouton_tout_savoir . '" align="top" /></a>';			
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
			le ' . $date_cloture_jour . ' ' . $NomDuMois[$date_cloture_mois+0] . ' ' . $date_cloture_annee . ' 
			à ' . $date_cloture_heure .'h00 </span><br /> <br /> '; 
		}

	
		// ____________________________________________
		// LOTS compris dans ce concours
		
		if (isset($donnees['lots_conc']) AND ($donnees['lots_conc'] != NULL))
		{
			$tab.= '<span class ="liste_lots">';
			$tab.= '<ul>';
			$var_lot_unserialized = unserialize($donnees['lots_conc']) ;
				
			$i_lot = 0; // sera incrémenté dans la boucle
			foreach ($var_lot_unserialized as $element_lot)
			{
				//sélectionner seulement les joueurs du groupe spécifié
				//$tab.=  '--'.$i_lot.'--' ;
				if ($element_lot['groupe_joueur'] == $public_cible AND $element_lot['nombre_places'] !=0 )
				{	
					$tab.= '<li>' . $element_lot['decription_lot'] ;
					
					// Recherche du nombre de personnes qui ont déjà joué
					$reponse_nb_joueurs = mysql_query("SELECT COUNT(*) AS nb_joueurs_actuels FROM $table_ag_conc_joueur 
					WHERE id_fiche_conc_joueur = '$id_conc' AND  lot_conc_joueur = '$i_lot'");
					$donnees_nb_joueurs = mysql_fetch_array($reponse_nb_joueurs);
					$tab.= '<i> - actuellement <b>' . $donnees_nb_joueurs['nb_joueurs_actuels'] . ' </b>joueur(s)</i>' ;

		
					// Ajout du lien si le concours est encore en cours
					if ($date_actuelle <= $date_cloture)
					{
						$tab.= '<a href="' . $page_contenant_concours . '?id=' . $id_conc . '&amp;lot=' . $i_lot . '">
						<img src="agenda/design_pics/conc_jouer.gif" align="top" /></a><br />';
					}
					else
					{
						$tab.= '<br />';
					}
					$tab.= '</li>' ;
					//$tab.= '[' . $groupes_joueurs[$element_lot['groupe_joueur']] . ']';
					/*print_r($element_lot);
					echo '<br /><br /><br />' ;*/
				}
				$i_lot++ ;
			}
			$tab.='</ul> </span>';
		}
		else
		{
			$tab.='<br /><div class="alert">ERREUR (Aucun lot pour ce concours)</div>';
		}
		$tab.='</span> <div class="float_stop"> <br /> </div> </div> <br />';
	}
	echo $tab . '' ;
}

?>

