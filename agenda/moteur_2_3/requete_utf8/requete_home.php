<?php
require '../../inc_db_connect.php';
require '../../inc_var.php';
require '../../inc_fct_base.php';

//sleep(0.7);

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

if (isset($_POST['url_self']) && $_POST['url_self']!=NULL)
	$url_self = $_POST['url_self'];
else if (isset($_GET['url_self']) && $_GET['url_self']!=NULL)
	$url_self = $_GET['url_self'];
else
	$url_self = '';
$url_self = str_replace('?id_event=0', '', str_replace('&id_event=0', '', $url_self));
if ($url_self && ($url_self{0} == '?' || $url_self{0} == '&'))
	$url_self = substr($url_self, 1);
$url_self = ($url_self ? '?'.$url_self.'&amp;' : '?').'id_event=';


// Critères obligatoires :
// ******************************************************

$req_construct_count = ' LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu ' ;
$req_construct_titre = ' LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu ' ;


// Test : le visiteur a-t-il sélectionné une période ?
// ******************************************************
	// date de début
	$date_in_aaammjj = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y")-2));

	// date de fin
	$date_out_aaammjj = date('Y-m-d', mktime(0, 0, 0, date("m")+6 , date("d"), date("Y")));
	
//	$req_construct_count.= " WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND NOT ((date_event_debut < '$date_in_aaammjj') AND (date_event_fin < '$date_in_aaammjj') OR (date_event_debut > '$date_out_aaammjj') AND (date_event_fin > '$date_out_aaammjj')) ";
	$req_construct_count.= " WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND date_event_debut >= '$date_in_aaammjj' AND date_event_fin <= '$date_out_aaammjj' ";


// *****************************************************************************************
// Test : le visiteur a-t-il entré des caractères dans le champ de recherche contextuelle ?
// *****************************************************************************************

if (isset($_POST['chaine_txt_libre']) && $_POST['chaine_txt_libre']!=NULL)
	$chaine_txt_libre = $_POST['chaine_txt_libre'];
else if (isset($_GET['chaine_txt_libre']) && $_GET['chaine_txt_libre']!=NULL)
	$chaine_txt_libre = $_GET['chaine_txt_libre'];
else
	$chaine_txt_libre = '';
if ($chaine_txt_libre) {
	// Cas[2]
	$chaine_txt_libre = mysql_real_escape_string($chaine_txt_libre); 
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

// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
// Composer la liste des x premiers résultats pour prévisualisation :
// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

$query_preview = "SELECT id_event, lieu_event, nom_lieu, nom_event, date_event_debut,date_event_fin, ville_event, resume_event, pic_event_1 FROM ag_event $req_construct_count ORDER BY date_event_debut ASC LIMIT 12 " ;
//echo $query_preview,'<br />';

$preview_concat = '';

$reponse_preview = mysql_query($query_preview) or die (mysql_error());
$nombre_resultats = mysql_num_rows($reponse_preview);
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
	

	if (isset($donnees_preview['pic_event_1']) AND $donnees_preview ['pic_event_1'] == 'set')
	{
		$pic_event_1 = '<a href="'.$url_self.$id_event.'">
		<img src="agenda/'.$folder_pics_event.'event_'.$id_event.'_1.jpg" title="'.$nom_event.'" alt="" width="43" /></a>';
	}
	else
	{
		$pic_event_1 = '<a href="'.$url_self.$id_event.'">
		<img src="agenda/moteur_2_3/pics/event_sans_image.gif" title="'.$nom_event.'" /></a>';
	}

	$preview_concat.= '<div class="un_event_preview">
	<span class="image_flottante_preview">'.$pic_event_1.'</span> 
	<strong><a href="'.$url_self.$id_event.'">'.$nom_event.'</a></strong> | du '
	.$date_event_debut_jour.'-'.$date_event_debut_mois.'-'.$date_event_debut_annee.' au '.$date_event_fin_jour.'-'.$date_event_fin_mois.'-'.$date_event_fin_annee.
	' | <strong>'.$nom_lieu.'</strong> ('.$region_nom.')<br />'.$resume_event.'<br style="clear:both;" /></div>';

}
$preview_concat = str_replace($jsonReplaces[0], $jsonReplaces[1], $preview_concat);

// ****************************************************************************************************************
// ****************************************************************************************************************
// ****************************************************************************************************************

$json = '{"nombre_resultats": "'.$nombre_resultats.'","preview_event": "'.$preview_concat.'"}';
echo json_encode($json) ;

// ****************************************************************************************************************
// ****************************************************************************************************************
// ****************************************************************************************************************

// //--- mysql_close($db2dlp);
?>
