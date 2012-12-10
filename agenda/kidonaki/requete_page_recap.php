<?php

require 'agenda/inc_var.php';
require 'agenda/inc_fct_base.php';
require 'agenda/calendrier/inc_calendrier.php'; 
$marge_date_vente = 12 ; // Limite en nombre de jours minimum avant fin de vente 
$tab = '' ;


$saisie_date_1_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"))); // 
$saisie_date_2_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")+3  , date("d"), date("Y"))); // 

require 'agenda/inc_db_connect.php';
$query_1 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L 
ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu

WHERE 
NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
AND (date_event_fin < '$saisie_date_1_aaammjj') 
OR (date_event_debut > '$saisie_date_2_aaammjj') 
AND (date_event_fin > '$saisie_date_2_aaammjj')) 

AND ((lieu_event < 51) OR (lieu_event > 58))
AND article_kidonaki > 0
ORDER BY date_event_fin " ;
//AND (date_event_debut > SUBDATE(CURDATE(), INTERVAL 2 DAY))

$reponse = mysql_query($query_1) ;
while ($donnees = mysql_fetch_array($reponse))
{
	$id_event = $donnees['id_event'] ;
	// Infos de la TABLE DLP
	// Voir s'il reste des places pour l'OBJET KIDONADI
	//--- mysql_close($db2dlp);
	require 'inc_db_connect_kidonaki.php';
	
	$reponse_kido_article = mysql_query("SELECT id_article FROM spip_articles WHERE id_evenement = $id_event") 
	or die('Erreur 1 : ' . mysql_error() . ' ');
	$donnees_kido_article = mysql_fetch_array($reponse_kido_article) ;
	
	if (isset ($donnees_kido_article['id_article']) AND $donnees_kido_article['id_article'] !=0 )
	{
		$id_article_de_objet_kido = $donnees_kido_article['id_article'] ;
		// echo $id_event.'rrrrr' .$id_article_de_objet_kido . 'ffff';
		
		// Connaitre nombre de places qui restent :
		$count_places_kido = mysql_query("SELECT COUNT(*) AS nbre_places_kido FROM spip_encheres_objets 
		WHERE id_article = '$id_article_de_objet_kido' AND (statut = 'mise_en_vente' OR statut = 'stand_by') 
		AND (date_stop_vente  > CURDATE()) ") 
		or die('Erreur 2 : ' . mysql_error() . ' ');
		$donnees_count_places_kido = mysql_fetch_array($count_places_kido) ;
		
		if (isset ($donnees_count_places_kido['nbre_places_kido']) AND $donnees_count_places_kido['nbre_places_kido'] !=0 )
		{
			$nombre_places_kido = $donnees_count_places_kido['nbre_places_kido'] ;
			//echo '<br>Nombre de places disponibles = ' . $nombre_places_kido ;
			if ($nombre_places_kido == 1)
			{
				$nombre_places_kido_phrase = 'Il reste <span class="places_dispo_chiffre">1</span> place' ;
			}
			else
			{
				$nombre_places_kido_phrase = 'Il reste 
				<span class="places_dispo_chiffre">' . $nombre_places_kido . '</span> 
				places' ;
			}
			

			// prix des places...
			$info_places_kido = mysql_query("SELECT id_objet, prix_depart, date_stop_vente FROM spip_encheres_objets 
			WHERE id_article = '$id_article_de_objet_kido' AND (statut = 'mise_en_vente' OR statut = 'stand_by') 
		AND (date_stop_vente  > CURDATE()) ") 
			or die('Erreur 2 : ' . mysql_error() . ' ') ;
			$donnees_info_places_kido = mysql_fetch_array($info_places_kido) ;
			
			$concat_prix = '
			<div class="zone_prix">
			  <div class="prix_place">
			    <acronym title="Prix pour une place">' . $donnees_info_places_kido['prix_depart'] . '
			    <span class="euro_prix_place">
			    €
			    </span></acronym>
			  </div>
			 </div>';

			// Fin de vente
			$date_fin_v = $donnees_info_places_kido['date_stop_vente'] ;
			$date_fin_vente = substr($date_fin_v, 8, 2) . '-' . substr($date_fin_v, 5, 2) . '-' . substr($date_fin_v, 0, 4);
			//echo '<br>Fin de vente = ' . $date_fin_vente ;
			
			
			// Date de fin de validité des places
			$date_de_fin_validite_places = mktime(0, 0, 0, substr($date_fin_v, 5, 2), (substr($date_fin_v, 8, 2))+$marge_date_vente, substr($date_fin_v, 0, 4)) ;
			$date_de_fin_validite_places_f = date('d-m-Y', $date_de_fin_validite_places); 
			//echo '<br> fin de validité des places = ' . $date_de_fin_validite_places_f ;

			
			// Infos de la TABLE DLP
			//--- mysql_close($db2dlp);
			require 'agenda/inc_db_connect.php';

			$tab.= '<div class="breve">' ;	
			$id_event = $donnees ['id_event'] ;
		
			// ____________________________________________
			// ICONES FLOTTANTES (au niveau du titre)
			$tab.= '<span class="ico_float_droite_relative">' ;
			
			$tab.= '</span>' ;
		
			// ____________________________________________
			// VIGNETTE EVENEMENT	
			if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
			{
				$nom_event = $donnees ['nom_event'] ;
				$id_event = $donnees ['id_event'] ;
				$tab.= '<span class="breve_pic"><a href="-Detail-agenda-?id_event=' . $id_event . '"><img src="agenda/' . $folder_pics_event . 'vi_event_' . $id_event . '_1.jpg" title="' . $nom_event . '" /></a></span>';
			}
			
			
			// ____________________________________________
			// NOM EVENEMENT
			
				if (isset($requete_txt) AND $requete_txt != 'nom du spectacle' AND stristr ($donnees['nom_event'], $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse
				{
		
					$pattern = "!$requete_txt!i" ;
					$souligne = '<span class="souligne">' . $requete_txt .'</span>' ;
					$nom_origin = $donnees['nom_event'] ;
					
					$nom_souligne = preg_replace($pattern, $souligne, $nom_origin);
					
					$tab.= '<div class="breve_titre"><a href="-Detail-agenda-?id_event=' . $id_event . '" title="Voir en détail">
					' . $nom_souligne . '</a></div>';
				}
				else
				{
					$tab.= '<div class="breve_titre"><a href="-Detail-agenda-?id_event=' . $id_event . '" title="Voir en détail">
					' . $donnees['nom_event'] . '</a></div>';
				}
		
			// ____________________________________________
			// ID
			$tab.= ' <span class="id_breve">(id ' . $donnees ['id_event'] . ')</span><br />' ;
		
		
			// ____________________________________________
			// LIEU
			$id_lieu = $donnees['lieu_event'] ;
			$reponse_2 = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $id_lieu");
			$donnees_2 = mysql_fetch_array($reponse_2) ;
					
			$tab.= '<span class="breve_lieu"><a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="Lieu où se joue le spectacle">' . $donnees_2['nom_lieu'] . '</a></span> ';	
		
		
			// ____________________________________________
			// GENRE
			
			if (isset($donnees['genre_event']) AND ($donnees['genre_event'] != NULL)) 
			{
				$genre_name = $donnees['genre_event'] ;
				$tab.= '<span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . 
				'</acronym></span> ';	
			}
		
		
			// ____________________________________________
			// DATES
			
			$date_event_debut = $donnees ['date_event_debut'];	
			$date_event_debut_annee = substr($date_event_debut, 0, 4);
			$date_event_debut_mois = substr($date_event_debut, 5, 2);
			$date_event_debut_jour = substr($date_event_debut, 8, 2);
			
			$date_event_fin = $donnees ['date_event_fin'];
			$date_event_fin_annee = substr($date_event_fin, 0, 4);
			$date_event_fin_mois = substr($date_event_fin, 5, 2);
			$date_event_fin_jour = substr($date_event_fin, 8, 2);
		
			
			// note : pour mois en LETTRES : $NomDuMois[$date_event_debut_mois+0]
			$tab.= ' <span class="date_validite"><acronym title="Période de validité des places">Places valables du ' . 
			$date_event_debut_jour . '/'. $date_event_debut_mois . '/' . $date_event_debut_annee . ' au ' . 
			$date_de_fin_validite_places_f . '</acronym></span><br />';	
		
	
			// ____________________________________________
			// NOMBRE PLACES
			$tab.= ' <span class="places_dispo">' . $nombre_places_kido_phrase . ' en vente jusqu\'au ' . $date_fin_vente . '</span> <br /> <br />' ;
	
	
			// ____________________________________________
			// DESCRIPTION
			
			// Prix de la place
			$tab.= $concat_prix . '' ;
			
			$resum_txt = $donnees['resume_event'] ;
			$array_retour_ligne = array("<br>", "<br />", "<BR>", "<BR />");
			$tab.= str_replace($array_retour_ligne, " - ", $resum_txt);
			
			
			
			$tab.= '<div class="enfilade_de_boutons_kido">
					<a href="http://www.kidonaki.be/spip.php?article' . $donnees['article_kidonaki'] . '&id_objet=' . $donnees_info_places_kido['id_objet'] . '" target="_blank">
					<img src="agenda/kidonaki/acheter_sur_kidonaki_mini.jpg" title="Achetez vos places sur Kidonaki" alt="Achetez vos places sur Kidonaki"  hspace="2" align="middle" /></a>
			
					<a href="-Detail-agenda-?id_event=' . $id_event . '">
					<img src="agenda/design_pics/ensavoirplus.jpg" title="En savoir plus" alt="En savoir plus" hspace="2" align="middle" /></a>
					
					</div>
					<div class="float_stop"><br /></div></div>' ;

			
		}
	}		
}
echo $tab ;

?>
