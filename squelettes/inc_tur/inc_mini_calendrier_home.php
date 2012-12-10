<?php
require 'agenda/calendrier/inc_calendrier.php';

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage du calendrier avec cases colorées en fonction des jours actifs
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
//--- voir agenda/moteur_2_3/inc_mini_calendrier.php

function affich_jours_spectacles ($MM_traite, $AAAA_traite)
{	
	require 'agenda/inc_var.php';
	$date_test_periode_debut = $AAAA_traite . '-' . $MM_traite . '-01';
	$date_test_periode_fin = date ('Y-m-d', mktime(0, 0, 0, $MM_traite, 31, $AAAA_traite)); // un mois plus tard
	$tableau_jours = array() ;	

	// .......................................................................
	// Flèches de "mois précédent" et "mois suivant"
	
	// 1) Début du mois suivant :
	if ($MM_traite == 12 )
	{
		$mois_next = 1;
		$annee_next = $AAAA_traite + 1;
	}
	else
	{
		$mois_next = $MM_traite + 1;
		$annee_next = $AAAA_traite ;
	}

	//$next = '?mois=' . $mois_next . '&annee=' . $annee_next . '&mois_chgt=1' ;
	$mois_next = str_pad($mois_next, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
	$annee_next = str_pad($annee_next, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne

	// Recherche de la valeur du dernier jour du mois afin de préremplir la date de fin de recherche
	$valeur_dernier_jour_mois = date("t",mktime(0,0,0,$mois_next + 1,0,$annee_next));
	$next = '-Agenda-?req=mini_calendr&date_debut=01-' . $mois_next . '-' . $annee_next . '&date_fin=' . $valeur_dernier_jour_mois . '-' . $mois_next . '-' . $annee_next ;
	
	// 1) mois précédant :
	if ($MM_traite == 1 )
	{
		$mois_prev = 12;
		$annee_prev = $AAAA_traite - 1;
	}
	else
	{
		$mois_prev = $MM_traite - 1;
		$annee_prev = $AAAA_traite ;
	}

	$mois_prev = str_pad($mois_prev, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
	$annee_prev = str_pad($annee_prev, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne

	// Recherche de la valeur du dernier jour du mois afin de préremplir la date de fin de recherche
	$valeur_dernier_jour_mois = date("t",mktime(0,0,0,$mois_prev + 1,0,$annee_prev));
	$prev = '?req=mini_calendr&date_debut=01-' . $mois_prev . '-' . $annee_prev . '&date_fin=' . $valeur_dernier_jour_mois . '-' . $mois_prev . '-' . $annee_prev ;

	
	// echo '<p>'.$prev . ' <<==>> ' . $next . '</p>';
	$pn = array('&lt;&lt;'=> $prev, '&gt;&gt;'=> $next);
// .......................................................................

	// Initialiser le tableau. (Car un simple "else" n'aurait pas été)
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		settype($JJ_traite, "integer"); // Pour éviter problèmes de foncion "calebdar" avec les nombres précédés de "0"	
		$tableau_jours[$JJ_traite] = array(NULL,'linked-day non_event_cal',$JJ_traite);
	}

	$reponse_fct = mysql_query("SELECT jours_actifs_event, id_event FROM ag_event 
	LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu 
	WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND
	 NOT ((date_event_debut < '$date_test_periode_debut') AND (date_event_fin < '$date_test_periode_debut') 
	OR (date_event_debut > '$date_test_periode_fin') AND (date_event_fin > '$date_test_periode_fin'))") ;		
		
	while ($donnees_fct = mysql_fetch_array($reponse_fct))
	{ 
		$j=1;
		for ($j=1 ; $j<=31 ; $j++)
		{
			// Composer la chaine qui sera cherchée dans la DB :
			$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
			$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
			$date_traite = $AAAA_traite.'-'.$MM_traite.'-'.$JJ_traite ;
			settype($JJ_traite, "integer"); // Pour éviter problèmes de foncion "calebdar" avec les nombres précédés de "0"	
			$jours_actifs_event = $donnees_fct ['jours_actifs_event'];
			$jours_actifs_event = explode(",", $jours_actifs_event);

			if (in_array($date_traite, $jours_actifs_event))
			{
				// echo '<br>id('.$donnees_fct ['id_event'] .') '.$date_traite.' y est '; // test
				// jour ACTIF
				//$link = '?jour='.$j.'&mois='.$MM_traite.'&annee='.$AAAA_traite ;
				$j = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
				$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
				$link = '-Agenda-?req=mini_calendr&date_debut=' . $j . '-' . $MM_traite . '-' . $AAAA_traite . '&date_fin=' . $j . '-' . $MM_traite . '-' . $AAAA_traite ;


				$tableau_jours[$JJ_traite] = array($link,'linked-day event_cal',$JJ_traite);
			}
		}
	}
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1, $pn); // Affichage du calendrier
	echo '<br />' ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

$date_mini_calendrier_mois = date ('m'); // Mois en cours
$date_mini_calendrier_annee = date ('Y'); // Année en cours

affich_jours_spectacles ($date_mini_calendrier_mois, $date_mini_calendrier_annee) ;
?>