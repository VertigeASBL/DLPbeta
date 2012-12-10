<?php

require 'agenda/inc_db_connect.php';
require 'agenda/inc_var.php';

//SELECT * FROM table 
$date_in_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d"), date("Y"))); // j
$date_out_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+15, date("Y"))); // j+15

//$genre_de_la_case = 'AND genre_event = "g02" ' ;
$genre_de_la_case = array (
'AND genre_event = "g01" ', 
'AND genre_event = "g07" ', 
'AND (genre_event = "g05" OR genre_event = "g04" OR genre_event = "g14")', 
'AND (genre_event = "g02" OR genre_event = "g08")', 
'AND (genre_event = "g09" OR genre_event = "g03" OR genre_event = "g10" OR genre_event = "g06" OR genre_event = "g11")', 
'AND (genre_event = "g12" OR genre_event = "g13")') ;
	
	
function requete_selon_genre($case_proch_event)
{
	// On transmet à la fonction le numéro de la case, et elle affiche le genre correspondant
	// ou "g01" si aucun résultat n'existe pour ce genre

	global $date_in_aaammjj ;
	global $date_out_aaammjj ;
	global $genre_de_la_case ;
	global $genres ;
	
	$reponse_hasard = mysql_query("SELECT * FROM ag_event 
	LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu
	WHERE 
	(ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) 
	$genre_de_la_case[$case_proch_event]
	AND NOT ((date_event_debut < '$date_in_aaammjj') 
	AND (date_event_fin < '$date_in_aaammjj') 
	OR (date_event_debut > '$date_out_aaammjj') 
	AND (date_event_fin > '$date_out_aaammjj'))
	ORDER BY RAND() LIMIT 1 
	") or die('Erreur SQL '.mysql_error());	

	$donnees_hasard = mysql_fetch_array($reponse_hasard);
	if(isset($donnees_hasard['id_event']) AND $donnees_hasard['id_event'] != NULL)
	{
		//echo ' > ' . $donnees_hasard['date_event_debut'] . ' -> ' . $donnees_hasard['genre_event'] . '<br>' ;
		
		
			
		$id_event = $donnees_hasard ['id_event'] ;	
		$nom_event = $donnees_hasard ['nom_event'] ;
		
		// ____________________________________________
		// VIGNETTE EVENEMENT	
		if (isset ($donnees_hasard ['pic_event_1']) AND $donnees_hasard ['pic_event_1'] == 'set' )
		{
			$tab.= '<span class="actu_photo"><a href="-Detail-agenda-?id_event=' . $id_event . '">
			<img src="agenda/pics_events/vi_event_' . $id_event . '_1.jpg" title="' . $nom_event . '" alt="' . $nom_event . '" />
			</a></span>';
		}		
	
		// ____________________________________________
		// GENRE
		
		//$genre_name = $donnees_hasard[$genre_event] ;
		$tab.= '<h2 class="titre_cadre">
		<acronym title="Genre du spectacle">' . $genres[$donnees_hasard ['genre_event']] . '</acronym>
		</h2> ';	
		
		
		// ____________________________________________
		// NOM EVENEMENT
		
		$tab.= '<h3 class="titre_actu">' . $donnees_hasard['nom_event'] . '</h3>';
	
	
		// ____________________________________________
		// LIEU
		$tab.= '<div class="evt_lieu"><a href="-Details-lieux-culturels-?id_lieu='. $donnees_hasard['lieu_event'] .'" title="Lieu où se joue le spectacle">'
		. $donnees_hasard['nom_lieu'] . '</a></div> ';	
	
	
		// ____________________________________________
		// DATES
		
		$date_event_debut = $donnees_hasard ['date_event_debut'];	
		$date_event_debut_annee = substr($date_event_debut, 0, 4);
		$date_event_debut_mois = substr($date_event_debut, 5, 2);
		$date_event_debut_jour = substr($date_event_debut, 8, 2);
		
		$date_event_fin = $donnees_hasard ['date_event_fin'];
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
		$tab.= '</div>' ;
		
		echo $tab ;	
	}
	else
	{
		return('rien_malheureusement');
	}
}
	

function appel_fonction_requete_selon_genre($case_proch_event)
{
	$resultat_fonction = requete_selon_genre($case_proch_event) ;
	
	// Test de la réponse de la fonction :
	// S'il n'y a pas d'événement pour un Genre, mettre du Théâtre à la place
	if(isset($resultat_fonction) AND $resultat_fonction == 'rien_malheureusement')
	{
		echo '' ; 
		requete_selon_genre(0) ; 
	}
}


?>



<table width="100%" border="0" cellpadding="8" cellspacing="10">
  <tr>
    <td width="33%" valign="top" bgcolor="#E8E8E8"><?php appel_fonction_requete_selon_genre('0') ; ?></td>
    <td width="33%" valign="top" bgcolor="#E8E8E8"><?php appel_fonction_requete_selon_genre('1') ; ?></td>
    <td width="33%" valign="top" bgcolor="#E8E8E8"><?php appel_fonction_requete_selon_genre('2') ; ?></td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#E8E8E8"><?php appel_fonction_requete_selon_genre('3') ; ?></td>
    <td valign="top" bgcolor="#E8E8E8"><?php appel_fonction_requete_selon_genre('4') ; ?></td>
    <td valign="top" bgcolor="#E8E8E8"><?php appel_fonction_requete_selon_genre('5') ; ?></td>
  </tr>
</table>



