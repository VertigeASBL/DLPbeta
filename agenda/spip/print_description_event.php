<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex,nofollow" />

  <?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';


if (empty ($_GET['id_event']) OR $_GET['id_event'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="index.php" >Retour</a></div>' ;
	exit() ;
}
else
{
	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id_event'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>
		<a href="index.php" >Retour</a></div>' ;
		exit() ;
	}
}



		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entrée
		// ------------------------------------------------
		
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
		$donnees = mysql_fetch_array($reponse);	
	
		$lieu_event = $donnees ['lieu_event'];
		$nom_event = $donnees ['nom_event'];
		$ville_event = $donnees ['ville_event'];
		$description_event = $donnees ['description_event'];
		$genre_event = $donnees ['genre_event'];
		$pic_event_1 = $donnees ['pic_event_1'];
		
		$date_event_debut = $donnees ['date_event_debut'];
		$date_event_fin = $donnees ['date_event_fin'];

		$AAAA_debut = substr($date_event_debut, 0, 4);
		$AAAA_fin = substr($date_event_fin, 0, 4);
		$MM_debut = substr($date_event_debut, 5, 2);	
		$MM_fin = substr($date_event_fin, 5, 2);
		$JJ_debut = substr($date_event_debut, 8, 2);
		$JJ_fin = substr($date_event_fin, 8, 2);
		$AAAA_MM_debut = substr($date_event_debut, 0, 7);

		$jours_actifs_event = $donnees ['jours_actifs_event'];
		$jours_actifs_event = explode(",", $jours_actifs_event);


		// TABLE LIEU
		$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $lieu_event");
		$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
		$nom_lieu = $donnees_lieu['nom_lieu'] ;



echo '<title> ::: Demandez le programme : '. $nom_event .' -- ' . $nom_lieu  . ' ::: </title>' ;
?>
<link href="../css_impression_agenda.css" rel="stylesheet" type="text/css" />
</head>
<body onLoad="window.print()">

<?php
		// ------------------------------------------------
		// Affichage contenu de l'événement
		// ------------------------------------------------

?>
<div class="print_style_conteneur">

<?php require 'print_head.php'; ?>

<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td>
	
	<?php

		// NOM EVENEMENT (titre)
		echo '<div class="print_style_nom_event">' . $nom_event . '</div>';
	
		// ID
		echo '<span class="id_print_style">(id ' . $id_event . ')</span>' ;

		// LIEU
		echo '<br /><span class="print_style_rubriques">Lieu : </span>' . $nom_lieu ;	

		// GENRE
		if (isset($genre_event) AND ($genre_event != NULL)) 
		{
			echo '<br /><span class="print_style_rubriques">Genre : </span>' . $genres[$genre_event];	
		}
		
		// DATES
		$date_event_debut_annee = substr($date_event_debut, 0, 4);
		$date_event_debut_mois = substr($date_event_debut, 5, 2);
		$date_event_debut_jour = substr($date_event_debut, 8, 2);
		
		$date_event_fin_annee = substr($date_event_fin, 0, 4);
		$date_event_fin_mois = substr($date_event_fin, 5, 2);
		$date_event_fin_jour = substr($date_event_fin, 8, 2);
	
		
		echo '<br /><span class="print_style_rubriques">Dates : </span> du '
		. $date_event_debut_jour . ' '
		. $NomDuMois[$date_event_debut_mois+0] . ' '
		. $date_event_debut_annee . ' au ' . $date_event_fin_jour . ' '
		. $NomDuMois[$date_event_fin_mois+0] . ' '
		. $date_event_fin_annee . '</span>';

		?></td>
  </tr>
    <tr>
    <td>	</td>
  </tr>
  <tr>
    <td>
	<?php
		// Photo
		if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
		{
			echo '<span class="alignLeftMargin"><br /><img src="../' . $folder_pics_event . 'vi_event_' . $id_event . 
			'_1.jpg" /></span>';
		}
		
		// Description
		echo  '<span class="print_style_rubriques">Description : </span><br /> ' . $donnees['description_event'] ;// Raccourcir la chaine :

		
		?>	</td>
  </tr>
 
</table>

<?php require 'print_pied.php'; ?>

</div>

</body>
</html>
