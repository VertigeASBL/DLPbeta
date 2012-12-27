<?php 
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';
include_spip('inc/utils');

	/*** Redimensionner les photos à 100px de larg ***/
	$larg_max = 100;
	function vignette_home($image,$w_vi_absolue,$nom){
		// Largeur et hauteur initiales
		$uploaded_pic = imagecreatefromjpeg($image); // = photo uploadée 
		$largeur_uploaded = imagesx($uploaded_pic);
		$hauteur_uploaded = imagesy($uploaded_pic);	
	
		if ($largeur_uploaded<=$w_vi_absolue)
		{	
			$new_W_Vignette = $largeur_uploaded ;
			$new_H_Vignette = $hauteur_uploaded ;
		}
		else
		{		
			// W > maximum
			if ($largeur_uploaded>$w_vi_absolue)
			{
					$new_W_Vignette = $w_vi_absolue;
					// On recalcule la Hauteur proportionnellement
					$new_H_Vignette = round($hauteur_uploaded * $w_vi_absolue / $largeur_uploaded);
			}
		}
				
		$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
		imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W_Vignette, $new_H_Vignette, $largeur_uploaded, $hauteur_uploaded);
		$destination_vi = 'agenda/vignettes_home/'.$nom.'.jpg';
		@unlink($destination_vi);
		imagejpeg($resample, $destination_vi);
		
		//Renvoie la hauteur de la vignette		
		return $new_H_Vignette;
	}	

	/**** Couper le texte pour que la hauteur s'adapte à la hauteur de l'image ***/
	function nb_cars($haut_img){
		$hors_txt = 65;		//nb px hors texte à adapter
		$px_ligne = 15;		//nb px par ligne
		$car_ligne = 50;	//nb car par ligne
		
		if ($haut_img < $hors_txt){	//1 ligne min
			$nb_car = $car_ligne;
		}else{
			//Nb de lignes à afficher
			$nb_ligne = floor(($haut_img - $hors_txt) / $px_ligne);
			//Nb de caractères à garder
			if ($nb_ligne < 1) 
				$nb_car = $car_ligne;
			else
				$nb_car = $nb_ligne * $car_ligne;
		}
		return $nb_car;
	}	

	function couper_txt($texte,$nb_car){
		//Texte découpé à afficher
		$order   = array("\r\n", "\n", "\r");
		$texte = str_replace($order, '', $texte);
		
		//remplacer les <br> avec espace
		$br   = array("<br />", "<BR />", "<BR >","<br >");
		$texte = str_replace($br, '<-xx->', $texte);
		
		//texte raccourci
		$texte = substr ($texte, 0, $nb_car);		
		
		$a_couper = strlen(strrchr($texte, " "));
		$texte = substr($texte, 0, strlen($texte)-$a_couper);	//tronquer au dernier mot
		$texte = $texte.' ...';
			
		//remettre les br
		$texte = str_replace('<-xx->', '<br />', $texte);
		$texte = str_replace("<br /><br />", "<br />'", $texte);
		
		return $texte; 
	}	

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
		$tab.= '<h2 class="titre_cadre" id="titre_concours">Concours</h2>';
	
		
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
		$tab.='<br />'.affdate($date_cloture_annee.'-'.$date_cloture_mois.'-'.$date_cloture_jour).' | <a href="'.generer_url_entite(95,'rubrique').'" title="Voir en détail">suite&nbsp;&gt;&gt;</a>';
		$tab.='</div><div class="float_stop"></div> </div>';
		
		echo $tab . '</div>' ;
	}
	


?>
