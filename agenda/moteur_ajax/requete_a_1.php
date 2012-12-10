<?php

require '../inc_db_connect.php';

sleep(0.3);
$liste_titre_compose = '' ;




// Critères obligatoires :
// ******************************************************

// critère par défaut. NB : "lieu_event != 0" sert pour le AND qui arrive après
$req_construct_count = ' INNER JOIN  ag_lieux L
    ON cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND lieu_event = id_lieu
	WHERE lieu_event != 0 ' ;
$req_construct_titre = ' INNER JOIN  ag_lieux L
    ON cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND lieu_event = id_lieu
	WHERE lieu_event != 0 ' ;



// Test : le visiteur a-t-il sélectionné une période ?
// ******************************************************
	// Il existe une date de début
	//echo $_POST['date_in'] ;
	if (isset($_POST['date_in']) AND $_POST['date_in'] != 'non_selct')
	{
		$date_in = htmlentities($_POST['date_in'], ENT_QUOTES);
		$date_in_annee = substr($date_in, 6, 4);
		$date_in_mois = substr($date_in, 3, 2);	
		$date_in_jour = substr($date_in, 0, 2);
		$date_in_aaammjj = $date_in_annee.'-'.$date_in_mois.'-'.$date_in_jour ;

		$req_construct_count.= " AND (date_event_debut > '$date_in_aaammjj') ";
		$req_construct_titre.= " AND (date_event_debut > '$date_in_aaammjj') ";
	}

	// Il existe une date de début
	if (isset($_POST['date_out']) AND $_POST['date_out'] != 'non_selct')
	{
		$date_out = htmlentities($_POST['date_out'], ENT_QUOTES);
		$date_out_annee = substr($date_out, 6, 4);
		$date_out_mois = substr($date_out, 3, 2);	
		$date_out_jour = substr($date_out, 0, 2);
		$date_out_aaammjj = $date_out_annee.'-'.$date_out_mois.'-'.$date_out_jour ;
		
		$req_construct_count.= " AND (date_event_debut < '$date_out_aaammjj') ";
		$req_construct_titre.= " AND (date_event_debut < '$date_out_aaammjj') ";
	}
	
	// critère par défaut : les $x_prochains_jours
	/*$req_construct_count.= " (date_event_fin > SUBDATE(CURDATE(), INTERVAL 100 DAY)) ";
	$req_construct_titre.= " (date_event_fin > SUBDATE(CURDATE(), INTERVAL 100 DAY)) "; */


// Test : le visiteur a-t-il sélectionné un lieu ?
// ******************************************************
$lieu = mysql_real_escape_string($_POST['lieu']);
if($lieu!='non_selct')
{
	$req_construct_count.= " AND lieu_event='$lieu'";
	$req_construct_titre.= " AND lieu_event='$lieu'";
}



// Test : le visiteur a-t-il sélectionné une région ?
// ******************************************************
$region = mysql_real_escape_string($_POST['region']);
if($region!='non_selct')
{
	$req_construct_count.= " AND ville_event='$region'";
	$req_construct_titre.= " AND ville_event='$region'";
}



// Test : le visiteur a-t-il sélectionné un genre ?
// ******************************************************
$genre = mysql_real_escape_string($_POST['genre']);
if($genre!='non_selct')
{
	$req_construct_count.= " AND genre_event='$genre'";
	$req_construct_titre.= " AND genre_event='$genre'";
}

// Test : le visiteur a-t-il entré des caractères dans le champ de recherche contextuelle ?
// *****************************************************************************************
$chaine_txt_libre = mysql_real_escape_string($_POST['chaine_txt_libre']);

if($chaine_txt_libre!='')
{
	//$req_construct_count.= " AND nom_event ='$chaine_txt_libre'";
	$req_construct_count.= " AND nom_event LIKE '%$chaine_txt_libre%'";
	//echo 'console.log("fffffff = '.$chaine_txt_libre.')';
}


//echo $req_construct_count ;


// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
// Mise en forme des données pour le retour de requête JSON :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

// Compter le nombre d'entrées :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

/*$reponse_test_1 = mysql_query("SELECT COUNT(*) AS test_exist_1 FROM ag_event 
WHERE (date_event_fin > SUBDATE(CURDATE(), INTERVAL 100 DAY)) ".$req_construct_count." 
") or die (mysql_error());*/

$query_nbre = "SELECT COUNT(*) AS test_exist_1 FROM ag_event  
$req_construct_count ";

//echo $query_nbre ;

$reponse_test_1 = mysql_query($query_nbre) or die (mysql_error());
$donnees_test_1 = mysql_fetch_array($reponse_test_1);
$contenu_xml = $donnees_test_1['test_exist_1'] ;

/* if ($donnees_test_1['test_exist_1'] > 0) 
{
	$contenu_xml = $donnees_test_1['test_exist_1'] ;
}
else
{
	$contenu_xml = '<br />Pas de résultat<br />' ;
}*/


// Composer la "liste" des noms d'événements :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
if($chaine_txt_libre!='')
{		//$chaine_txt_libre
	$query_titres_events = "SELECT nom_event FROM ag_event  
	$req_construct_count LIMIT 12 " ;
	$reponse_titres_events = mysql_query($query_titres_events) or die (mysql_error());

	$liste_titre_compose = '' ;
	while ($donnees_titres_events = mysql_fetch_array($reponse_titres_events))
	{
		$nom_event_list = $donnees_titres_events['nom_event'] ;
		$liste_titre_compose.= '<li onClick="fill(\'' . $nom_event_list . '\');">' . $nom_event_list . '</li>';
	}
	// Parser chaine (http://be.php.net/manual/fr/function.json-encode.php#82904)
	$jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
	$liste_titre_compose = str_replace($jsonReplaces[0], $jsonReplaces[1], $liste_titre_compose);
}



$json = '{"messages": {';
$json .= '"message":[ {';
$json .= '"id":  "0",
	"nombre_resultats": "' . $contenu_xml . '",
	"dlp_list_events": "' . $liste_titre_compose . '"
}]}}';

echo json_encode($json) ;

//--- mysql_close($db2dlp);

?>