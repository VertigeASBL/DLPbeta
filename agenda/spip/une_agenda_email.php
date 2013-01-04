<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php 

require '../inc_var.php';
require '../inc_var_dist_local.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';

$tab = '' ;

//$racine_domaine = 'http://127.0.0.1/vertige/agenda_comediens/';  // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!! enlever après tests !!!!!!!!!!!!!!!!!!


// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Une de l'agenda destinée à l'e-mailing
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

// *********************************************************************************************
// ---------------------------------------------------------------------------------------------
// 		Affichage résultat
// ---------------------------------------------------------------------------------------------
// *********************************************************************************************


// Dates : un mois complet en partant du 5 e jour du mois courant
$date_debut = date ('Y-m-05', $date_fin = mktime(0, 0, 0, date("m"), date("d"), date("Y")));		
$date_fin = date ('Y-m-05', $date_fin = mktime(0, 0, 0, date("m")+1, date("d"), date("Y")));		


// Compter le nombre d'entrées
$query_count = "SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda INNER JOIN  $table_lieu L
ON cotisation_lieu > CURDATE() AND lieu_event = id_lieu
WHERE NOT ((date_event_debut < '$date_debut') AND (date_event_fin < '$date_debut') 
OR (date_event_debut > '$date_fin') AND (date_event_fin > '$date_fin'))" ;		

$reponse_count = mysql_query($query_count) or die($query_count . " ----- " . mysql_error());
$donnees_count = mysql_fetch_array($reponse_count);
$tot_entrees = $donnees_count['nbre_entrees'];

if ($tot_entrees == 0)
{
	echo '<div class="breve"><p>&nbsp;</p>ERREUR - Aucun événement pour le moment <p>&nbsp;</p></div>' ;
	exit() ;
}
else
{
	$mois_emailing = $NomDuMois[date('m')+0] ;
	
	$mail_concat = '<title>Une de l\'agenda de COMEDIEN - mois de  ' . $mois_emailing . '</title>
	
<style type="text/css">
body {
	font-size: 12px;
	color: #333300;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
a img {
	border: none;
	text-decoration:none;
}
h1 {
	color: #999999;
	font-size: 24px;
	text-align:center;
}

.breve {
	font-size: 11px;
	color: #333333;
	border: 1px solid #CCCCCC;
	font-family: Geneva, Arial, Helvetica, sans-serif;
}

.breve h3 {
	font-size: 16px;
	color: #666666;
	font-weight: bold;
}

.en_savoir_plus a {
	color: #000099;
	font-style: italic;
}

.en_savoir_plus a:hover {
	color: #0000FF;
	text-decoration: underline;
}

.breve_lieu a{
	font-size: 9px;
	color: #666666;
	background-color: #C5E0C5;
	font-style: italic;
	border: 1px solid #FFFFFF;
	line-height: 25px;
	padding-left: 5px;
	padding-right: 5px;
	margin-left: 5px;
}
.corps_texte {
	text-align:justify;
}

.breve_date {
	font-size: 9px;
	color: #666666;
	background-color: #DDDDDD;
	font-style: italic;
	border: 1px solid #FFFFFF;
	line-height: 25px;
	padding-left: 5px;
	padding-right: 5px;
	margin-left: 5px;
}
.breve_genre {
	font-size: 9px;
	color: #666666;
	background-color: #CCCCAA;
	font-style: italic;
	border: 1px solid #FFFFFF;
	line-height: 25px;
	padding-left: 5px;
	padding-right: 5px;
	margin-left: 5px;
}
</style>
	</head>
	<body>
	<h1 align="center">COMEDIEN vous pr&eacute;sente <br />la Une de son l\'agenda pour le <br />	
	mois de ' . $mois_emailing . '</h1>
	<p align="center">&nbsp;</p><table border="0" align="center" width="500"><tr><td>' ;
	
	
	
	
	$query_mail = "SELECT * FROM $table_evenements_agenda 
	INNER JOIN  $table_lieu L
	ON cotisation_lieu > CURDATE() AND lieu_event = id_lieu
	WHERE NOT ((date_event_debut < '$date_debut') AND (date_event_fin < '$date_debut') 
	OR (date_event_debut > '$date_fin') AND (date_event_fin > '$date_fin'))
	ORDER BY date_event_debut DESC " ;		

	/* $reponse = mysql_query($query_mail) or die($query_mail . " ----- " . mysql_error()); // TEST JOINTURE 
		while ($donnees = mysql_fetch_array($reponse))
	{ echo $donnees ['id_event'] .' -- ' .$donnees ['id_lieu'].' -- ' .$donnees ['cotisation_lieu'].'<br>' ; } */

	$reponse = mysql_query($query_mail) or die($query_mail . " ----- " . mysql_error());
	while ($donnees = mysql_fetch_array($reponse))
	{
		$mail_concat.= '<table class="breve" width="700"  border="0" cellpadding="10" cellspacing="0"><tr>' ;		
		
		// ____________________________________________
		// VIGNETTE EVENEMENT	
		if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
		{
			$nom_event = $donnees ['nom_event'] ;
			$id_event = $donnees ['id_event'] ;
			$mail_concat.= '<td width="120"><a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . 
			'"<img src="' . $racine_domaine . 'agenda/' . $folder_pics_event . 'event_' . $id_event . '_1.jpg" title="' . $nom_event . '" alt="" width="100" />
			</a></td>';
		}
		
	
		// ____________________________________________
		// NOM EVENEMENT
		
		if (isset($donnees['nom_event']) AND $donnees['nom_event'] != NULL)
		{
			$mail_concat.= '<td align="left"><h3>' . $donnees['nom_event'] . '</h3>';
		}
	
	
		// ____________________________________________
		// LIEU
		if (isset($donnees['nom_lieu']) AND $donnees['nom_lieu'] != NULL)
		{
			$mail_concat.= '<span class="breve_lieu"><a href="' . $racine_domaine . 'detail_lieu.php?id_lieu='.$donnees['id_lieu'].'" 
			title="Lieu où se joue le spectacle">' . $donnees['nom_lieu'] . '</a></span> ';	
		}


		// ____________________________________________
		// GENRE
		
		if (isset($donnees['genre_event']) AND ($donnees['genre_event'] != NULL)) 
		{
			$genre_name = $donnees['genre_event'] ;
			$mail_concat.= '<span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . 
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
	
		
		$mail_concat.= ' <span class="breve_date"><acronym title="Période de représentation">du ' 
		. $date_event_debut_jour . '-' . $date_event_debut_mois . '-'
		. $date_event_debut_annee . ' au ' . $date_event_fin_jour . '-' . $date_event_fin_mois . '-'
		. $date_event_fin_annee . '</acronym></span><br /></td></tr>';	
	

		// ____________________________________________
		// TEXTE INTRODUCTIF  !!!!!!!!!!!!! si wysiwyg  !!!!!!!!!!!!!
		
		if (isset($donnees['description_event']) AND $donnees['description_event'] != NULL)
		{
			$chapeau = strip_tags(stripslashes($donnees['description_event'])) ;// Raccourcir la chaine :
			$max=350; // Longueur MAX de la chaîne de caractères
			$chapeau = raccourcir_chaine ($chapeau,$max); // retourne $chaine_raccourcie
			$mail_concat.= '<tr><td colspan="2" class="corps_texte">' . $chapeau . '<span class="en_savoir_plus"><a href="' . $racine_domaine .
			'detail_event.php?id_event=' . $id_event . '"> En savoir plus</a></span></td>';	
		}
		$mail_concat.= '</tr></table><br />' ;
	}
	echo $mail_concat ;
}

?></td>
</tr>
</table>
<p>&nbsp;</p>
</body>
</html>
