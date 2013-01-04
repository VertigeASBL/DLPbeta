<link href="../css_1_inspip.css" rel="stylesheet" type="text/css">

<?php
require 'agenda/inc_var.php';
require 'agenda/inc_fct_base.php';
require 'agenda/calendrier/inc_calendrier.php'; 

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

$saisie_date_1_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"))); // 
$saisie_date_2_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+30, date("Y"))); // 

$date_0_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d"), date("Y"))); // j
$date_1_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))); // j+1
$date_2_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+2, date("Y"))); // j+2
$date_3_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+3, date("Y"))); // j+3
$date_4_tester = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+4, date("Y"))); // j+4


/* MODELE 1
$query = " WHERE 
NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
AND (date_event_fin < '$saisie_date_1_aaammjj') 
OR (date_event_debut > '$saisie_date_2_aaammjj') 
AND (date_event_fin > '$saisie_date_2_aaammjj')) 

AND (
(jours_actifs_event LIKE '%$date_1_tester%' )  
OR (jours_actifs_event LIKE '%$date_2_tester%' ) 
OR (jours_actifs_event LIKE '%$date_3_tester%' )
OR (jours_actifs_event LIKE '%$date_4_tester%' )

)"; */



// MODELE 2 
$query = " WHERE 
NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
AND (date_event_fin < '$saisie_date_1_aaammjj') 
OR (date_event_debut > '$saisie_date_2_aaammjj') 
AND (date_event_fin > '$saisie_date_2_aaammjj')) 
AND (date_event_debut > SUBDATE(CURDATE(), INTERVAL 2 DAY))
AND ((lieu_event < 51) OR (lieu_event > 58))

";



/* MODELE 3 
$query = " WHERE 
NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
AND (date_event_fin < '$saisie_date_1_aaammjj') 
OR (date_event_debut > '$saisie_date_2_aaammjj') 
AND (date_event_fin > '$saisie_date_2_aaammjj'))

AND(date_event_debut > SUBDATE(CURDATE(), INTERVAL 3 DAY))

AND(
(jours_actifs_event LIKE '%$date_0_tester%' )  
OR (jours_actifs_event LIKE '%$date_1_tester%' ) 
OR (jours_actifs_event LIKE '%$date_2_tester%' ) 
OR (jours_actifs_event LIKE '%$date_3_tester%' )
OR (jours_actifs_event LIKE '%$date_4_tester%' )


)
";*/


/*
// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
// LIEU ?

if (isset ($_GET['lieu']) AND $_GET['lieu'] != '' )
{
	$lieu_event = htmlentities($_GET['lieu'], ENT_QUOTES);
	$query.= " AND lieu_event = '$lieu_event'" ;
	
	$requete_lieu = $lieu_event ; // pour visualiser les critères de recherche 
	$lieu_event_form = $lieu_event ; // pour re-remplire le formulaire
}


// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
// VILLE ?

if (isset ($_GET['region']) AND $_GET['region'] != '')
{	
	$ville_event = htmlentities($_GET['region'], ENT_QUOTES);
	$query.= " AND ville_event = '$ville_event' " ;
	
	$requete_ville = $ville_event ; // pour visualiser les critères de recherche 
}


// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
// GENRE ?

if (isset ($_GET['genre']) AND $_GET['genre'] != '')
{	
	$genre_event = htmlentities($_GET['genre'], ENT_QUOTES);
	$query.= " AND genre_event = '$genre_event'" ;
	
	$requete_genre = $genre_event ;
}

// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
// FIN DE LA REQUETE SQL :


// --------------------------------------
// Affichage des critères de recherche
$requ_via_form =  '' ;

// Lieu culturel :
if (isset($requete_lieu) AND $requete_lieu != NULL)
{ 
	$reponse_lieu = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = $requete_lieu");
	$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
	$requ_via_form.= '<br>Lieu culturel : <b>' . $donnees_lieu['nom_lieu'] . '</b>' ;
}

// Ville :
if (isset($requete_ville) AND $requete_ville != NULL)
{ 
	$requ_via_form.= '<br>Ville : <b>' . $regions[$requete_ville] . '</b>' ;
}
 
// Genre :
if (isset($requete_genre) AND $requete_genre != NULL)
{ 
	$requ_via_form.= '<br>Genre : <b>' . $genres[$requete_genre] . '</b>' ;
}

// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
// Montrer à l'utilisateur sa requete et le nombre réponses
// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM			 
$query_count = "SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda INNER JOIN  $table_lieu L
             ON (cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND lieu_event = id_lieu
			 $query " ;
	//echo $query_count ;	 
$reponse_count = mysql_query($query_count) or die($query_count . " ----- " . mysql_error());
$donnees_count = mysql_fetch_array($reponse_count);
$tot_entrees = $donnees_count['nbre_entrees'];

echo '<div class ="afficher_requete">' . $tot_entrees . ' événements dans les prochains jours<br />'
. $requ_via_form.'</div>' ;

*/ 



// *********************************************************************************************
// ---------------------------------------------------------------------------------------------
// 		Affichage résultat
// ---------------------------------------------------------------------------------------------
// *********************************************************************************************


$query_1 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
             ON  (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu

			WHERE 
			NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
			AND (date_event_fin < '$saisie_date_1_aaammjj') 
			OR (date_event_debut > '$saisie_date_2_aaammjj') 
			AND (date_event_fin > '$saisie_date_2_aaammjj')) 
			
			AND ((lieu_event < 51) OR (lieu_event > 58))
			
			AND (date_event_debut > SUBDATE(CURDATE(), INTERVAL 2 DAY))

		 	GROUP BY lieu_event ORDER BY date_event_debut " ;

$reponse = mysql_query($query_1) ;
while ($donnees = mysql_fetch_array($reponse))
{
		
	$tab.= '<div class="breve">' ;	
	$id_event = $donnees ['id_event'] ;

	// ____________________________________________
	// ICONES FLOTTANTES (au niveau du titre)

	$tab.= '<span class="ico_float_droite_relative">' ;


	// Vos Avis :
	// compter le nbre d'entrées :
	$count_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE event_avis = $id_event 
	AND publier_avis = 'set'");
	$nbr_avis = mysql_fetch_array($count_avis);
	$total_entrees = $nbr_avis['nbre_entrees'];

	if ($total_entrees > 0)
	{
		$tab.= '<a href="-Detail-agenda-?id_event=' . $id_event . '#avis" title="Ce qu\'en disent les autres visiteurs...">
		<img src="agenda/design_pics/ico_avis_mini.jpg"/>
		<div class="nombre_avis_breve">' . $total_entrees .'</div></a>' ;
		
	}
	
	// Icone Interview
	if (isset ($donnees['interview_event']) AND $donnees['interview_event'] != 0 )
	{
		$interview_event = $donnees['interview_event'] ;
//--- richir	$tab.= '<a href="-Interviews-?id_article=' . $interview_event . '" title...
		$tab.= '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" title="Cliquez ici pour lire l\'interview"><img src="agenda/design_pics/ico_interview_mini.jpg"/></a>' ;
	}

	// Icone Critique
	if (isset ($donnees['critique_event']) AND $donnees['critique_event'] != 0 )
	{
		$critique_event = $donnees['critique_event'] ;
		$tab.= '<a href="-Critiques-?id_article=' . $critique_event . '" title="Cliquez ici pour lire la critique">
		<img src="agenda/design_pics/ico_critique_mini.jpg"/></a>' ;
	}


	$tab.= '</span>' ;

	// ____________________________________________
	// VIGNETTE EVENEMENT	
	if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
	{
		$nom_event = $donnees ['nom_event'] ;
		$id_event = $donnees ['id_event'] ;
		$tab.= '<span class="breve_pic"><a href="-Detail-agenda-?id_event=' . $id_event . '"><img src="agenda/' . $folder_pics_event . 'event_' . $id_event . '_1.jpg" title="' . $nom_event . '" alt="" width="100" /></a></span>';
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
	$tab.= ' <span class="breve_date"><acronym title="Période de représentation">' . $date_event_debut_jour . '/'
	. $date_event_debut_mois . '/'
	. $date_event_debut_annee . ' &gt;&gt; ' . $date_event_fin_jour . '/'
	. $date_event_fin_mois . '/'
	. $date_event_fin_annee . '</acronym></span><br /><br />';	


	// ____________________________________________
	// TEXTE INTRODUCTIF 
	
	
	// Remplacer les retours de ligne
	$resum_txt = $donnees['resume_event'] ;
	$array_retour_ligne = array("<br>", "<br />", "<BR>", "<BR />");
	$uuuuueeeeeeee = str_replace($array_retour_ligne, " - ", $resum_txt);
	$tab.= $uuuuueeeeeeee ;
	
	$tab.= '<div class="en_savoir_plus">
			<a href="-Detail-agenda-?id_event=' . $id_event . '">
			<img src="agenda/design_pics/ensavoirplus.jpg" title="En savoir plus" alt="" /></a></div>
			<div class="float_stop"><br /></div></div>' ;
}
echo $tab ;

?>

