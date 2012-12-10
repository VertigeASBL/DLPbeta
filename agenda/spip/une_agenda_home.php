<?php
require_once 'agenda/inc_var.php';
require_once 'agenda/inc_fct_base.php';
require_once 'agenda/calendrier/inc_calendrier.php'; 

/*require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';*/ // 88888888888888888888888

$tab = '' ;

// !!!!!!
// PS : Le / Nombre Max de spectacles à la UNE est défini dans "rubrique=64.html" avec $limit_nb 
// !!!!!!

// ----------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------
// Sélection des spectacles dont la période de représentation est comprise entre aujourd'hui et j+3
// ET dont un jour ACTIF correspond à aujourd'hui OU à j+x
// On peut fournir les valeurs $_GET['lieu'] ; $_GET['region'] ; $_GET['genre']
// ----------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------

$saisie_date_1_aaammjj = date ('Y-m-d'); // Aujourd'hui
$saisie_date_2_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+35, date("Y"))); // 

$date_0_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d"), date("Y"))); // j
$date_1_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))); // j+1
$date_2_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+2, date("Y"))); // j+2
$date_3_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+3, date("Y"))); // j+3
$date_4_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+4, date("Y"))); // j+4



// MODELE 2 
$query = " WHERE 
NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
AND (date_event_fin < '$saisie_date_1_aaammjj') 
OR (date_event_debut > '$saisie_date_2_aaammjj') 
AND (date_event_fin > '$saisie_date_2_aaammjj')) 
AND(date_event_debut > SUBDATE(CURDATE(), INTERVAL 2 DAY)
AND ((lieu_event < 51) OR (lieu_event > 58))
)
";


function affich_event($reponse,$table_avis_agenda,$table_lieu,$genres,$folder_pics_event,$ordre,$hauteur_max){
while ($donnees = mysql_fetch_array($reponse))
{		
	if ($ordre==1 || $ordre==3) 
		$classe_cadre = 'cadre_evt';
	else 
		$classe_cadre = 'cadre_evt2';
	//$tab.= '<div class="'.$classe_cadre.'" style="height:'.$hauteur_max.'px;">' ;	
	$tab.= '<div class="'.$classe_cadre.'" style="height:120px;">' ; // Fait par Renaud pour Xavier car les titres sont quelques fois trop longs
	$id_event = $donnees ['id_event'] ;	
	
	// ____________________________________________
	// VIGNETTE EVENEMENT	
	if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
	{
		$nom_event = $donnees ['nom_event'] ;
		$id_event = $donnees ['id_event'] ;
		$tab.= '<span class="actu_photo"><a href="-Detail-agenda-?id_event=' . $id_event . '"><img src="agenda/vignettes_home/'.$ordre.'_evenement.jpg" title="' . $nom_event . '" alt=""/></a></span>';
	}
	
	
	$tab.= '<div class="texte_evt">';
	

	// ____________________________________________
	// GENRE
	
	if (isset($donnees['genre_event']) AND ($donnees['genre_event'] != NULL)) 
	{
		$genre_name = $donnees['genre_event'] ;
		$tab.= '<h2 class="titre_cadre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . 
		'</acronym></h2> ';	
	}	
	
	
	// ____________________________________________
	// NOM EVENEMENT
	
		if (isset($requete_txt) AND $requete_txt != 'nom du spectacle' AND stristr ($donnees['nom_event'], $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse
		{

			$pattern = "!$requete_txt!i" ;
			$souligne = '<span class="souligne">' . $requete_txt .'</span>' ;
			$nom_origin = $donnees['nom_event'] ;
			
			$nom_souligne = preg_replace($pattern, $souligne, $nom_origin);
			
			$tab.= '<h3 class="titre_actu">' . $nom_souligne . '</h3>';
		}
		else
		{
			$tab.= '<h3 class="titre_actu">' . $donnees['nom_event'] . '</h3>';
		}

	// ____________________________________________
	// LIEU
	$id_lieu = $donnees['lieu_event'] ;
	$reponse_2 = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $id_lieu");
	$donnees_2 = mysql_fetch_array($reponse_2) ;
			
	$tab.= '<div class="evt_lieu"><a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="Lieu où se joue le spectacle">' . $donnees_2['nom_lieu'] . '</a></div> ';	


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
	$tab.= ' <span class="evt_date"><acronym title="Période de représentation">' . $date_event_debut_jour . '/'
	. $date_event_debut_mois . '/'
	. $date_event_debut_annee . ' &gt;&gt; ' . $date_event_fin_jour . '/'
	. $date_event_fin_mois . '/'
	. $date_event_fin_annee . '</acronym></span><br />';	

	$tab.='<p><a href="-Detail-agenda-?id_event=' . $id_event . '">Afficher la suite &gt;&gt;</a></p>';
	$tab.= '</div></div>' ;
}
echo $tab ;
}

	 
//Dernier THEATRE
$query_1 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
			 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
			 $query 
			 AND genre_event = 'g01' 
			 AND pic_event_1 = 'set' 
			 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;
		 
//Dernier CONCERT			 
$query_2 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
			 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
			 $query 
			 AND (genre_event = 'g03' OR genre_event = 'g10') 
			 AND pic_event_1 = 'set'
			 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;				 

//NI Theâtre, NI CONCERT
$query_3 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
			ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
			 $query 
			 AND genre_event <> 'g01' AND genre_event <> 'g03' AND genre_event <> 'g05' AND genre_event <> 'g10'
			 AND pic_event_1 = 'set' 
			 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;		 			 	
	 
//Dernier "POUR ENFANTS"
$query_4 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
			 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu  
			 $query 
			 AND genre_event = 'g05' 
			 AND pic_event_1 = 'set' 
			 GROUP BY lieu_event ORDER BY date_event_debut " ;		
			 
$reponse4 = mysql_query($query_4) or die('Erreur SQL !<br>'.$query_4.'<br>'.mysql_error());				 

// Si pas de spectacles pour enfants --> Dernier Théâtre (!= 1er --> déjà affiché)
$nb_rep = mysql_num_rows($reponse4);
if ($nb_rep == 0){		 
	//echo "Pas de spectacles pour enfants";
/*	
	$sql5 = "SELECT date_event_debut,id_event FROM $table_evenements_agenda INNER JOIN  $table_lieu L
				 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
				 $query 
				 AND genre_event = 'g01' 
				 AND pic_event_1 = 'set' 
				 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;
*/
	$sql5 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
				 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
				 $query 
				 AND genre_event = 'g01' 
				 AND pic_event_1 = 'set' 
				 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;
			 
			 				 
	$query_5 = mysql_query($sql5) or die('Erreur SQL !<br>'.$sql5.'<br>'.mysql_error());				 
	$data = mysql_fetch_array($query_5);
	$date_deb = $data['date_event_debut'];
	$id_evt = $data['id_event'];
	
	$query_4 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
				 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
				 $query 
				 AND genre_event = 'g01' 
				 AND date_event_debut >= '$date_deb' AND id_event != $id_evt 
				 AND pic_event_1 = 'set' 
				 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;	
}else{
	$query_4 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
				 ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu 
				 $query 
				 AND genre_event = 'g05' 
				 AND pic_event_1 = 'set'
				 GROUP BY lieu_event ORDER BY date_event_debut LIMIT 1" ;	
}



///Récupérer la hauteur max pour fixer la hauteur des cadres + Création VIGNETTE
$nb_event = 4;
$hauteur_max = 0;
for ($i=1; $i<= $nb_event; $i++){
	$rep = 'query_'.$i;
	//$rep = mysql_query($$rep);
	
	$rep = mysql_query($$rep) or die('Erreur SQL !<br>'.$$rep.'<br>'.mysql_error());				 
	$nb_rep = mysql_num_rows($rep);
	
	$data = mysql_fetch_array($rep);
	$id_event = $data['id_event'];
	
	$image = 'agenda/' . $folder_pics_event . 'event_' . $id_event . '_1.jpg';
	
//	echo $image."<br>";
	
	//Ne rien afficher si aucun evénements ne correspondent aux criètes
	if ($nb_rep != 0){
		$hauteur_v = vignette_home($image,100,$i.'_evenement');
	}
	if ($hauteur_v > $hauteur_max) 
		$hauteur_max = $hauteur_v;
}

$reponse1 = mysql_query($query_1);
$reponse2 = mysql_query($query_2);
$reponse3 = mysql_query($query_3);
$reponse4 = mysql_query($query_4);

//echo $query_2;

affich_event($reponse1,$table_avis_agenda,$table_lieu,$genres,$folder_pics_event,1,$hauteur_max);
affich_event($reponse2,$table_avis_agenda,$table_lieu,$genres,$folder_pics_event,2,$hauteur_max);
affich_event($reponse3,$table_avis_agenda,$table_lieu,$genres,$folder_pics_event,3,$hauteur_max);
affich_event($reponse4,$table_avis_agenda,$table_lieu,$genres,$folder_pics_event,4,$hauteur_max);

?>