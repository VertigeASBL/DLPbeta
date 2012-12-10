<?php

/*
require '../inc_db_connect.php';

old : Délimiter les dates extêmes :
$date_debut_top_5 = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m") , date("d"), date("Y")));
$date_debut_top_5_annee = substr($date_debut_top_5, 6, 4);
$date_debut_top_5_mois = substr($date_debut_top_5, 3, 2);	
$date_debut_top_5_jour = substr($date_debut_top_5, 0, 2);
$date_debut_top_5_to_requete = $date_debut_top_5_annee.'-'.$date_debut_top_5_mois.'-'.$date_debut_top_5_jour ;

$date_fin_top_5 = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m") , date("d"), date("Y")+1));
$date_fin_top_5_annee = substr($date_fin_top_5, 6, 4);
$date_fin_top_5_mois = substr($date_fin_top_5, 3, 2);	
$date_fin_top_5_jour = substr($date_fin_top_5, 0, 2);

$date_fin_top_5_to_requete = $date_fin_top_5_annee.'-'.$date_fin_top_5_mois.'-'.$date_fin_top_5_jour ;
 AND NOT ((date_event_debut < '$date_debut_top5') AND (date_event_fin < '$date_debut_top5') 
OR (date_event_debut > '$date_fin_top5') AND (date_event_fin > '$date_fin_top5'))
------------------------------------------------------------------------------

	Selectionner les evenements actifs en ce moment, avec une marge moins 1 semaine, plus 1 semaine.
	60 sec * 60 min * 24 h * 7 jours == 604800
*/
$top_5_concat = time();
$date_debut_top5 = date('Y-m-d', $top_5_concat - 604800);
$date_fin_top5 = date('Y-m-d', $top_5_concat + 604800);

$requete_top_5 = "SELECT id_event,lieu_event,nom_lieu,nom_event,date_event_debut,date_event_fin,ville_event,resume_event,pic_event_1,jai_vu_event FROM ag_event 
LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND jai_vu_event > 0 
AND date_event_fin >= '$date_debut_top5' AND date_event_debut <= '$date_fin_top5' ORDER BY jai_vu_event DESC LIMIT 5";

//echo '<hr />date_debut_top5 ',$date_debut_top5,' / date_fin_top5 ',$date_fin_top5,'<hr />',$requete_top_5,'<hr />';

$top_5_concat = '' ;
$reponse_requete_top_5 = mysql_query($requete_top_5) or die ('Erreur requete requete_top_5 '. mysql_error());
while ($donnees_top_5 = mysql_fetch_array($reponse_requete_top_5))
{
	$id_event = $donnees_top_5['id_event'] ;
	$lieu_event = $donnees_top_5['lieu_event'] ;
	$nom_lieu = $donnees_top_5['nom_lieu'];

	$nom_event = raccourcir_chaine ($donnees_top_5['nom_event'],45); // retourne $chaine_raccourcie
	$nom_event = $nom_event;

	$date_event_debut = $donnees_top_5['date_event_debut'] ;
	$date_event_debut_annee = substr($date_event_debut, 0, 4);
	$date_event_debut_mois = substr($date_event_debut, 5, 2);
	$date_event_debut_jour = substr($date_event_debut, 8, 2);

	$date_event_fin = $donnees_top_5['date_event_fin'] ;	
	$date_event_fin_annee = substr($date_event_fin, 0, 4);
	$date_event_fin_mois = substr($date_event_fin, 5, 2);
	$date_event_fin_jour = substr($date_event_fin, 8, 2);

	//$region_nom = $regions[$donnees_top_5['ville_event']];

	$resume_event = strip_tags($donnees_top_5['resume_event']) ;

	$resume_event = raccourcir_chaine($resume_event,65); // retourne $chaine_raccourcie
	$resume_event = $resume_event;

	if (isset($donnees_top_5['pic_event_1']) AND $donnees_top_5 ['pic_event_1'] == 'set' )
	{
		$pic_event_1 = '<a href="-Detail-agenda-?id_event=' . $id_event . '">
		<img src="agenda/' . $folder_pics_event . 'vi_event_' . $id_event . '_1.jpg" title="' . $nom_event . '" /></a>';
	}
	else
	{
		$pic_event_1 = '<a href="-Detail-agenda-?id_event=' . $id_event . '">
		<img src="agenda/moteur_2_3/pics/event_sans_image.gif" title="' . $nom_event . '" />
		</a>';
	}
	
	$top_5_concat.= '<div class="un_event_preview">
	<span class="image_flottante_preview">' . $pic_event_1 . '</span>' ; 
	
	
	$top_5_concat.= '<span class="nombre_de_jai_vu">' . $donnees_top_5['jai_vu_event'] .'</span><a href="#vote" onClick="popup_jai_vu';
	$top_5_concat.= "('agenda/jai_vu/jai_vu_popup.php?id=" . $id_event . "','Votons');";
	$top_5_concat.= '"><img src="agenda/design_pics/ico_jai_vu_mini.jpg" style="vertical-align:middle;" title="cliquez pour voter pour cet événement" alt="cliquez pour voter pour cet événement" /></a> ';

	$top_5_concat.= '<strong><a href="-Detail-agenda-?id_event=' . $id_event . '">' . $nom_event . '</a></strong>' . ' | 
	du ' . $date_event_debut_jour . '-' . $date_event_debut_mois . '-' . $date_event_debut_annee . ' au ' 
	. $date_event_fin_jour . '-' . $date_event_fin_mois . '-' . $date_event_fin_annee 
	. ' | <strong>' . $nom_lieu . '</strong> <!-- (' . $region_nom . ') --> | '
	. $resume_event . '</div>' ;

}
echo  $top_5_concat ;

?>
