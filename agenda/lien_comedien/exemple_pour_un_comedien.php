<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Exemple d'affichage des événements liés à  un Comédiens</title>
<style type="text/css">
<!--
body {
	margin: 55px;
}
-->
</style></head>
<body>

<h1>Exemple d'affichage des événements liés à  un Comédiens</h1> 

<p>Exemple avec "Pietro Pizzuti" <br />Son ID est 2664 ==> $id_du_comedien = 2664</p>
<br /> <br /> 


<?php
$id_du_comedien = 2664 ;

require '../inc_db_connect.php';
require '../inc_var.php';

$reponse_comedien_lien = mysql_query("SELECT * FROM ag_comedien_lien 
LEFT JOIN ag_event ON ag_comedien_lien.id_event_lien = ag_event.id_event 
LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu 
WHERE id_comedien_lien = '$id_du_comedien' 
ORDER BY date_event_debut DESC");

while ($donnees_comedien_lien = mysql_fetch_array($reponse_comedien_lien))
{
	echo '<h3>' . $donnees_comedien_lien['nom_event'] . ' (id '. $donnees_comedien_lien['id_event'] . ')</h3>';
	
	echo '<p>Lieu culturel : <a href="http://www.demandezleprogramme.be/-Details-lieux-culturels-?id_lieu=' . $donnees_comedien_lien['id_lieu'] . '" title="Lieu où est joué le spectacle">' . $donnees_comedien_lien['nom_lieu'] . '</a></p>';
	
	echo '<p>Ville : <acronym title="Ville où du spectacle">' . $regions[$donnees_comedien_lien['ville_event']] . 
			'</acronym></span></p>';	
	
	// PHOTO EVENEMENT :	
	if (isset ($donnees_comedien_lien['pic_event_1']) AND $donnees_comedien_lien['pic_event_1'] == 'set' )
	{
		echo '<img src="http://www.demandezleprogramme.be/agenda/pics_events/event_' . $donnees_comedien_lien['id_event'] . 
		'_1.jpg" title="' . $donnees_comedien_lien['nom_event'] . '" />';
		
//		echo '<img src="http://www.demandezleprogramme.be/agenda/pics_events/vi_event_' . $donnees_comedien_lien['id_event'] . '_1.jpg" title="' . $donnees_comedien_lien['nom_event'] . '" />';
	}
	if (isset ($donnees_comedien_lien['pic_event_2']) AND $donnees_comedien_lien['pic_event_2'] == 'set' )
	{
		echo '<img src="http://www.demandezleprogramme.be/agenda/pics_events/event_' . $donnees_comedien_lien['id_event'] . 
		'_2.jpg" title="' . $donnees_comedien_lien['nom_event'] . '" />';
		
//		echo '<img src="http://www.demandezleprogramme.be/agenda/pics_events/vi_event_' . $donnees_comedien_lien['id_event'] . '_2.jpg" title="' . $donnees_comedien_lien['nom_event'] . '" />';
	}
	if (isset ($donnees_comedien_lien['pic_event_3']) AND $donnees_comedien_lien['pic_event_3'] == 'set' )
	{
		echo '<img src="http://www.demandezleprogramme.be/agenda/pics_events/event_' . $donnees_comedien_lien['id_event'] . 
		'_3.jpg" title="' . $donnees_comedien_lien['nom_event'] . '" />';
		
//		echo '<img src="http://www.demandezleprogramme.be/agenda/pics_events/vi_event_' . $donnees_comedien_lien['id_event'] . '_3.jpg" title="' . $donnees_comedien_lien['nom_event'] . '" />';
	}
	
	
	// Genre :
	echo '<br />
	<acronym title="Genre du spectacle">' . $genres[$donnees_comedien_lien['genre_event']] . '</acronym> ';	
	

	// DATES
	
	$date_event_debut_annee = substr($donnees_comedien_lien['date_event_debut'], 0, 4);
	$date_event_debut_mois = substr($donnees_comedien_lien['date_event_debut'], 5, 2);
	$date_event_debut_jour = substr($donnees_comedien_lien['date_event_debut'], 8, 2);
	
	$date_event_fin_annee = substr($donnees_comedien_lien['date_event_fin'], 0, 4);
	$date_event_fin_mois = substr($donnees_comedien_lien['date_event_fin'], 5, 2);
	$date_event_fin_jour = substr($donnees_comedien_lien['date_event_fin'], 8, 2);

	echo '<br />
	<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . 
	$donnees_comedien_lien['id_event'] . '#calendrier" title="Cliquez pour accéder au calendrier">'
	. $date_event_debut_jour . ' '
	. $NomDuMois[$date_event_debut_mois+0] . ' '
	. $date_event_debut_annee . ' &gt;&gt; ' . $date_event_fin_jour . ' '
	. $NomDuMois[$date_event_fin_mois+0] . ' '
	. $date_event_fin_annee . '</a><br /><br />';


	echo '<p><strong>Description courte :</strong><br />' . $donnees_comedien_lien['resume_event'] . '</p>' ;

	echo '<p><strong>Description complète :</strong><br />' . $donnees_comedien_lien['description_event'] . '</p>' ;

	echo '<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . 
	$donnees_comedien_lien['id_event'] . 
	'" title="Cliquez pour en savoir plus sur l\'événement">&gt; &gt; Tous les détails sur l\'événement</a>' ;
	
	echo '<br />&nbsp;<br />&nbsp;<hr>' ;
}

//--- mysql_close($db2dlp);


?>

</body>
</html>
