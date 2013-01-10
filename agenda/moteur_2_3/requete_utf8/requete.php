<?php
require '../../inc_db_connect.php';
require '../../inc_var.php';
require '../../inc_fct_base.php';

sleep(0.7);
$liste_titre_compose = '' ;


// Parser chaine (http://be.php.net/manual/fr/function.json-encode.php#82904)
$jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonctions
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function nettoyer_chaine_pour_json ($chaine_a_nettoyer)
{
	$chaine_a_nettoyer = strip_tags($chaine_a_nettoyer);
	$chaine_a_nettoyer = utf8_encode($chaine_a_nettoyer);
	$chaine_a_nettoyer = str_replace("", "oe",$chaine_a_nettoyer); 
	$chaine_a_nettoyer = str_replace("", "'",$chaine_a_nettoyer); 
	$chaine_a_nettoyer = str_replace("", " - ",$chaine_a_nettoyer); 
	$chaine_a_nettoyer = str_replace("", " \" ",$chaine_a_nettoyer); // “
	$chaine_a_nettoyer = str_replace("", " \" ",$chaine_a_nettoyer); // ”

	return $chaine_a_nettoyer ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF



// Critères obligatoires :
// ******************************************************

$req_construct_count = ' LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu ' ;
$req_construct_titre = ' LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu ' ;


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
	}
	else
	{
		$date_in_aaammjj = date ('Y-m-d'); // Aujourd'hui
	}

	// Il existe une date de fin
	if (isset($_POST['date_out']) AND $_POST['date_out'] != 'non_selct')
	{
		$date_out = htmlentities($_POST['date_out'], ENT_QUOTES);
		$date_out_annee = substr($date_out, 6, 4);
		$date_out_mois = substr($date_out, 3, 2);	
		$date_out_jour = substr($date_out, 0, 2);
		$date_out_aaammjj = $date_out_annee.'-'.$date_out_mois.'-'.$date_out_jour ;
	}
	else
	{
		$date_out = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")+3 , date("d"), date("Y")));
	}
	
//	$req_construct_count.= " WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND NOT ((date_event_debut < '$date_in_aaammjj') AND (date_event_fin < '$date_in_aaammjj') OR (date_event_debut > '$date_out_aaammjj') AND (date_event_fin > '$date_out_aaammjj')) ";
	$req_construct_count.= " WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND date_event_fin >= '$date_in_aaammjj' AND date_event_debut <= '$date_out_aaammjj' ";


// Test : le visiteur a-t-il sélectionné un lieu ?
// ******************************************************
$lieu = mysql_real_escape_string($_POST['lieu']);
if($lieu!='non_selct')
{
	$req_construct_count.= " AND lieu_event='$lieu' ";
	$req_construct_titre.= " AND lieu_event='$lieu' ";
}



// Test : le visiteur a-t-il sélectionné une région ?
// ******************************************************
$region = mysql_real_escape_string($_POST['region']);
if($region!='non_selct')
{
	$req_construct_count.= " AND ville_event='$region' ";
	$req_construct_titre.= " AND ville_event='$region' ";
}



// Test : le visiteur a-t-il sélectionné un genre ?
// ******************************************************
$genre = mysql_real_escape_string($_POST['genre']);
if($genre!='non_selct')
{
	$req_construct_count.= " AND genre_event='$genre' ";
	$req_construct_titre.= " AND genre_event='$genre' ";
}

// *****************************************************************************************
// Test : le visiteur a-t-il entré des caractères dans le champ de recherche contextuelle ?
// *****************************************************************************************

if(isset($_POST['chaine_txt_libre']) AND $_POST['chaine_txt_libre']!=NULL)
{
	// Cas[2]
	$chaine_txt_libre = mysql_real_escape_string($_POST['chaine_txt_libre']); 
	$chaine_txt_libre = trim($chaine_txt_libre); 
	//$chaine_txt_libre = htmlentities($chaine_txt_libre, ENT_QUOTES, "UTF-8");  

	// Cas[3]
	$chaine_txt_libre_c3 = utf8_decode($chaine_txt_libre);  
	$chaine_txt_libre_c3 = htmlentities($chaine_txt_libre_c3, ENT_QUOTES);  

	// Cas[4]
	$chaine_txt_libre_c4 = utf8_decode($chaine_txt_libre);  
	//$chaine_txt_libre_c4 = htmlentities($chaine_txt_libre_c4, ENT_QUOTES);  


	$req_construct_count.= " AND (nom_event LIKE '%$chaine_txt_libre%' 
	OR nom_event LIKE '%$chaine_txt_libre_c3%'
	OR nom_event LIKE '%$chaine_txt_libre_c4%'
	) ";
	//$req_construct_count.= " AND (nom_event LIKE '%$chaine_txt_libre%') ";
	//$req_construct_count.= " AND (nom_event REGEXP '[ ,;.:(-]$chaine_txt_libre' OR nom_event REGEXP '^$chaine_txt_libre' )"; 
	//echo 'console.log("fffffff = '.$chaine_txt_libre_c4.')';
}
else
{
	$chaine_txt_libre = '' ;
}
$variable_test = $_POST['chaine_txt_libre'] ;
//$variable_test = 'ee' ;

//echo $req_construct_count ;



// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
// Compter le nombre de résultats selon les critères sélectionnés par le visiteur :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

/*$reponse_test_1 = mysql_query("SELECT COUNT(*) AS test_exist_1 FROM ag_event 
WHERE (date_event_fin > SUBDATE(CURDATE(), INTERVAL 100 DAY)) ".$req_construct_count." 
") or die (mysql_error());*/

$query_nbre = "SELECT COUNT(*) AS test_exist_1 FROM ag_event $req_construct_count ";

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


// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
// Composer la "liste" des noms d'événements à proposer (autocomplétion) :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
/*
	
if($chaine_txt_libre!='')
{		//$chaine_txt_libre
	$query_titres_events = "SELECT nom_event FROM ag_event $req_construct_count LIMIT 12 " ;
	$reponse_titres_events = mysql_query($query_titres_events) or die (mysql_error());

	$liste_titre_compose = '' ;
	while ($donnees_titres_events = mysql_fetch_array($reponse_titres_events))
	{
		$nom_event_list = nettoyer_chaine_pour_json($donnees_titres_events['nom_event']);
		$liste_titre_compose.= '<li onClick="fill(\'' . $nom_event_list . '\');">' . $nom_event_list . '</li>';
	}

	$liste_titre_compose = str_replace($jsonReplaces[0], $jsonReplaces[1], $liste_titre_compose);
}
*/ $liste_titre_compose = 'fonction enlevée' ;

// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
// Composer la liste des x premiers résultats pour prévisualisation :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

$query_preview = "SELECT id_event, lieu_event, nom_lieu, nom_event, date_event_debut,date_event_fin, ville_event, resume_event, pic_event_1 FROM ag_event $req_construct_count ORDER BY date_event_debut ASC LIMIT 12 " ;

//echo 'console.log("fffffff = '.$query_preview.')';



$preview_concat = '' ;

$reponse_preview = mysql_query($query_preview) or die (mysql_error());
while ($donnees_preview = mysql_fetch_array($reponse_preview))
{
	$id_event = $donnees_preview['id_event'] ;
	$lieu_event = $donnees_preview['lieu_event'] ;
	$nom_lieu = nettoyer_chaine_pour_json($donnees_preview['nom_lieu']);

	$nom_event = raccourcir_chaine ($donnees_preview['nom_event'],45); // retourne $chaine_raccourcie
	$nom_event = nettoyer_chaine_pour_json($nom_event);


	$date_event_debut = $donnees_preview['date_event_debut'] ;
	$date_event_debut_annee = substr($date_event_debut, 0, 4);
	$date_event_debut_mois = substr($date_event_debut, 5, 2);
	$date_event_debut_jour = substr($date_event_debut, 8, 2);

	$date_event_fin = $donnees_preview['date_event_fin'] ;	
	$date_event_fin_annee = substr($date_event_fin, 0, 4);
	$date_event_fin_mois = substr($date_event_fin, 5, 2);
	$date_event_fin_jour = substr($date_event_fin, 8, 2);

	$region_nom = nettoyer_chaine_pour_json($regions[$donnees_preview['ville_event']]);

	$resume_event = raccourcir_chaine ($donnees_preview['resume_event'],300); // retourne $chaine_raccourcie
	$resume_event = nettoyer_chaine_pour_json ($resume_event);
	

	if (isset($donnees_preview['pic_event_1']) AND $donnees_preview ['pic_event_1'] == 'set' )
	{
		$pic_event_1 = '<a href="-Detail-agenda-?id_event=' . $id_event . '">
		<img src="agenda/' . $folder_pics_event . 'event_' . $id_event . '_1.jpg" title="' . $nom_event . '" alt="" width="100" /></a>';
	}
	else
	{
		$pic_event_1 = '<a href="-Detail-agenda-?id_event=' . $id_event . '">
		<img src="agenda/moteur_2_3/pics/event_sans_image.gif" title="' . $nom_event . '" />
		</a>';
	}
	
	
	$preview_concat.= '<div class="un_event_preview">
	<span class="image_flottante_preview">' . $pic_event_1 . '</span> 
	<strong><a href="-Detail-agenda-?id_event=' . $id_event . '">' . $nom_event . '</a></strong>' . ' | 
	du ' . $date_event_debut_jour . '-' . $date_event_debut_mois . '-' . $date_event_debut_annee . ' au ' 
	. $date_event_fin_jour . '-' . $date_event_fin_mois . '-' . $date_event_fin_annee 
	. ' | <strong>' . $nom_lieu . '</strong> (' . $region_nom . ')<br />'
	. $resume_event . '<br style="clear:both;" /></div>' ;

}



$preview_concat = str_replace($jsonReplaces[0], $jsonReplaces[1], $preview_concat);




// ****************************************************************************************************************
// ****************************************************************************************************************
// ****************************************************************************************************************

$json = '{"nombre_resultats": "' . $contenu_xml . '","variable_test": "' . $variable_test . '","preview_event": "' . $preview_concat . '",	"dlp_list_events": "' . $liste_titre_compose . '"}';
echo json_encode($json) ;

// ****************************************************************************************************************
// ****************************************************************************************************************
// ****************************************************************************************************************

// //--- mysql_close($db2dlp);

?>