<?php 
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';


	//---------------------------------------------------------------------------
	// Listing des concours (n'est pas affiché quand le visiteur joue pour un élément sélectionné
	//---------------------------------------------------------------------------
	
	$tab = '';
	
	// DEBUT LISTE	
	
	//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
	// Listing des concours
	//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii 
	$limit_afficher = (time() - (3600*24*3000)); // Date actuelle moins quelques jours
	
	$public_cible_like = '%'. $public_cible . '%' ;

	$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE lots_conc LIKE '$public_cible_like' 
	AND pic_conc = 'set' 
	AND cloture_conc > $limit_afficher
	AND flags_conc LIKE '%actif%'
	ORDER BY id_conc DESC LIMIT 1");

	
	while ($donnees = mysql_fetch_array($reponse))
	{
		$tab.='<div class="cadre_actu">';
		$tab.= '<h2 class="titre_cadre">Concours</h2>';
	
		
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
			$image = 'agenda/' . $folder_vignettes_concours . 'conc_' . $id_conc .'_1.jpg';
			//$haut_img = vignette_home($image,100,'concours');
			$haut_img = vignette_home($image,91,'concours');
						
			$tab.= '<span class="actu_photo"><a href="-Concours,95-" style="text-decoration:none;"><img src="agenda/vignettes_home/concours.jpg" alt="concours" /></a></span>';
	
		}

	
		//INTITULE CADRE
		$tab.= '<div class="texte2_actu">';
		//$tab.= '<h2 class="titre_cadre">Concours</h2>';

		// TITRE
		$nom_event_conc = stripslashes ($donnees ['nom_event_conc']) ;
		$tab.= '<h3 class="titre_actu">' . $nom_event_conc .'</h3>' ;
	
		// ____________________________________________
		// TEXTE DESCRIPTIF
	
		if (isset ($donnees['description_conc']) AND $donnees['description_conc'] != NULL )
		{	
			$descr = $donnees['description_conc'];
			/*
			$needle = '<br />';
			$short_descr = substr($descr, 0, strpos($descr, $needle));		
			$tab.= '<div class="descriptif_actu">' . $short_descr . '</div>' ;
			*/

			$nb_car = nb_cars($haut_img);
			$short_descr = couper_txt($descr,$nb_car);
			$tab.= '<div class="descriptif_actu">' . $short_descr . '</div>' ;
		}
		$tab.='<p><a href="-Concours,95-">Afficher la suite &gt;&gt;</a></p>';
		$tab.='</div><div class="float_stop"></div> </div>';
		
		echo $tab . '</div>' ;
	}
	


?>

