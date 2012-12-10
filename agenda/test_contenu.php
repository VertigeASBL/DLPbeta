<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TEST CONTENU</title>
</head>

<body>

<?php

require 'inc_var.php';
require 'inc_db_connect.php';
require 'inc_fct_base.php';
require 'calendrier/inc_calendrier.php';
require 'fct_upload_pic_event_4.php';
require 'fct_upload_video.php';
	
		
		
// ------------------------------------------------
// Lecture des infos de la DB pour cette entrée
// ------------------------------------------------

$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event > 1600");
while ($donnees = mysql_fetch_array($reponse))	
{
	$resume_event = $donnees ['resume_event'];
	$description_event = $donnees ['description_event'];
	
	/*
	$lieu_event = $donnees ['lieu_event'];
	$nom_event = $donnees ['nom_event'];
	$ville_event = $donnees ['ville_event'];
	$genre_event = $donnees ['genre_event'];
	$pic_event_1 = $donnees ['pic_event_1'];
	
	$critique_event = $donnees ['critique_event'];
	$interview_event = $donnees ['interview_event'];
	
	$date_event_debut = $donnees ['date_event_debut'];
	$date_event_fin = $donnees ['date_event_fin'];
	*/
	
	echo '<br /> <em> <strong>' . $donnees ['id_event'] . '</strong></em> <br />
	<strong>Résumé :</strong>  ' . $resume_event . '<br />
	<strong>Description complète :</strong> ' . $description_event . '<br /> <hr> <br /> ' ;
}

?>

<p>&nbsp;</p>
</body>
</html>
