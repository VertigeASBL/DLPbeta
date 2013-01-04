<?php 
	session_start();
	require 'agenda/inc_var.php';
?>
<a href="-Communaute-des-spectateurs-"> &lt;&lt; Retour à la communauté des spectateurs</a>
<h2>Toutes les suggestions</h2>

<?php
/* On récupère les id des lieux favoris de la personne. */
$favoris = array();
$sql_liste_favoris = sql_select('id_lieu', 'ag_lieux_favoris', 'id_spectateur='.$_SESSION['id_spectateur']);
while ($res = sql_fetch($sql_liste_favoris)) {
	$favoris[] = $res['id_lieu'];
}

/* On séléctionne une suggestion en fonction des lieux favoris et des dates proches. */
/* On limite l'affichage a seulement 3 spectacle et on affiche aléatoirement. */
$spectacle = sql_allfetsel('*', 'ag_event INNER JOIN ag_lieux ON lieu_event = id_lieu', sql_in('id_lieu', $favoris).' AND date_event_fin > CURDATE() AND date_event_debut < DATE_ADD(CURDATE(), INTERVAL 7 DAY)', '', 'rand()');
$tab = '';
foreach ($spectacle as $key => $donnees
	) {
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
echo $tab;
?>