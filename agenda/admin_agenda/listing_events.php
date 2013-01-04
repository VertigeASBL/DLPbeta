<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Listing des &eacute;v&eacute;nements culturels</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Listing des &eacute;v&eacute;nements d'un lieu culturel</h1>

<div class="menu_back">
<a href="listing_lieux_culturels.php" >Listing des lieux affiliés</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>


<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';

$case_hors_periode = 'DDDDDD'; // couleur de fond en fonction de l'état du paiement
$case_periode_actuelle = '999999'; // couleur de fond en fonction de l'état du paiement

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des événements du lieu culturel sélectionné
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

// ----------------------------------------------------------
if (empty ($_GET['lieu']) OR $_GET['lieu'] == NULL) // La variable GET qui donne l'ID du lieu culturel pour lequel on va afficher les fiches de spectacle. 
{
	echo '<br><br><br><div class="alerte">Erreur GET ID</div><br>' ;
}
else
{
	$id = $_GET['lieu'];
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['lieu'] ne correspond à aucune entrée de la TABLE, proposer de créer un nouvel événement
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Il n\'existe aucun événement rattaché à ce lieu culturel. 
		<a href="edit_event.php?new=creer&amp;lieu=' . $id . '">Créer un nouvel événement</a></div><br>' ;
	}
	else
	{
		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entrée
		// ------------------------------------------------
		
		$lieu_correspondant = $id ;
		
		$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$lieu_correspondant'");
		$donnees_lieu = mysql_fetch_array($reponse_lieu);
		
		echo '<div align="center"><strong>Evénements proposés par <br />' . $donnees_lieu['nom_lieu'] . 
		'<i> (ID ' . $donnees_lieu['id_lieu'] . '</i>)</strong></div><br />' ;

		// ____________________________________________
		// EN TETE TABLE
		$tab ='<table width="650" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
		  <tr>
			<th>ID</th>
			<th>Vignette</th>
			<th>Date début-fin</th>
			<th>Nom de l\'&eacute;v&eacute;nement</th>
		  </tr>' ;
		  
		  // Créer un nouvel événement
		  $tab.='
		    <tr>
    <td colspan="4">
	<div align="right"><a href="edit_event.php?new=creer&amp;lieu=' . $donnees_lieu['id_lieu'] . '">
	<img src="../design_pics/bouton_new.gif" width="34" height="14"  hspace="3" title="Encoder un nouvel &eacute;v&eacute;nement" alt="" /></a></div>
	</td>
  </tr>' ;
		

 

// N'afficher que les événements pour 1 saison :

if (isset($_POST['go_annee']) AND ($_POST['go_annee'] == 'Afficher'))
{
	$annee_debut_choix = htmlentities($_POST['go_annee'], ENT_QUOTES);
	$choix_annee_postee = htmlentities($_POST['choix_annee'], ENT_QUOTES);
	$date_debut_choix = $choix_annee_postee . '-08-01' ;
	$date_fin_choix = $choix_annee_postee + 1 . '-08-01' ;
	//echo $date_debut_choix . ' ************ ' . $date_fin_choix ;
}
else
{
	if (date('m') <= 7)
	{
		$annee_saison_en_cours = date('Y')-1 ;
	}
	else
	{
		$annee_saison_en_cours = date('Y') ;
	}

	$date_debut_choix = $annee_saison_en_cours . '-08-01' ;
	$date_fin_choix = ($annee_saison_en_cours + 1) . '-08-0' ;
	$choix_annee_postee = $annee_saison_en_cours ;
	// echo $annee_saison_en_cours ;
}



$tab.= '<tr><td colspan="4" align="center">' ;

	$tab.= '<form action="" method="post">';
	
	$tab.= '<em>Choisissez ici la saison à afficher : </em><select name="choix_annee">
	<option value="' . (date ('Y')+1) . '">Saison ' . (date ('Y')+1) . ' - ' . (date ('Y')+2) . '</option>'; 
		
	for ($liste_annee=(date('Y')) ; $liste_annee>=2007 ; $liste_annee--)
	{
		$tab.= '<option value="' . $liste_annee .'"';		
		// Faut-il pr&eacute;-s&eacute;lectionner
		if (isset($choix_annee_postee) AND $choix_annee_postee == $liste_annee)
		{
			$tab.= ' selected="selected" ';
		}
		$liste_annee_plus_1 = $liste_annee +1 ;
		$tab.= '>Saison '.$liste_annee.' - ' . $liste_annee_plus_1 . '</option>';
	}
	$tab.= '</select>';
	
	$tab.= '<input name="go_annee" value="Afficher" type="submit">' ;

$tab.= '</form>';
$tab.= '</td></tr>' ;

/*
$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id' AND date_event_debut > SUBDATE(CURDATE(), INTERVAL 4 MONTH) ");

$date_debut_choix = '2007-06-01' ;
$date_fin_choix = '2009-06-01' ;

$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id' AND ((date_event_debut > '$date_debut_choix') AND (date_event_debut < '$date_fin_choix')) ORDER BY date_event_debut DESC");
*/
$reponse = mysql_query("SELECT A.id_event,A.parent_event,A.nom_event,A.date_event_debut,A.date_event_fin,A.pic_event_1
	FROM $table_evenements_agenda A,$table_evenements_agenda B
	WHERE A.lieu_event='$id' AND A.date_event_debut>'$date_debut_choix' AND A.date_event_debut<'$date_fin_choix' AND (B.id_event=A.parent_event OR A.parent_event=0 AND B.id_event=A.id_event)
	ORDER BY IF(A.parent_event=0,A.date_event_debut,B.date_event_debut) DESC,A.parent_event,A.date_event_debut");

		$nextdonnees = mysql_fetch_array($reponse);
		while ($nextdonnees) {
			$donnees = $nextdonnees;
			$nextdonnees = mysql_fetch_array($reponse);

			// ____________________________________________
			// ID
			$tab.= '<tr class="tr_hover"><td'.($donnees['parent_event'] ? ' class="evenfant"' : ($nextdonnees && $nextdonnees['parent_event'] ? ' class="evparent"' : '')).'><i>' . $donnees['id_event'] . '</i></td>' ;
			
			
			// ____________________________________________
			// VIGNETTE EVENEMENT
			$tab.= '<td align="center">';
			
			if (isset ($donnees['pic_event_1']) AND $donnees['pic_event_1'] == 'set' )
			{
				$nom_event = $donnees['nom_event'] ;
				$id_event = $donnees['id_event'] ;
				$tab.= '<img src="../' . $folder_pics_event . 'event_' . $id_event . '_1.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" width="100" />';
			}
			$tab.= '</td>';
			
			
			// ____________________________________________
			// PERIODE DE REPRESENTATION DU SPECTACLE
			$date_event_debut = $donnees['date_event_debut'];	
			$date_event_debut_annee = substr($date_event_debut, 0, 4);
			$date_event_debut_mois = substr($date_event_debut, 5, 2);
			$date_event_debut_jour = substr($date_event_debut, 8, 2);
			$time_debut=date(mktime(0, 0, 0, $date_event_debut_mois, $date_event_debut_jour, $date_event_debut_annee));
			
			$date_event_fin = $donnees['date_event_fin'];
			$date_event_fin_annee = substr($date_event_fin, 0, 4);
			$date_event_fin_mois = substr($date_event_fin, 5, 2);
			$date_event_fin_jour = substr($date_event_fin, 8, 2);
			$time_fin=date(mktime(0, 0, 0, $date_event_fin_mois, $date_event_fin_jour, $date_event_fin_annee));
			
		
			// Date actuelle
			$month_now=date("n"); 
			$year_now=date("Y");
			$date_actuelle= time();
			
			/*echo $date_actuelle . ' --- ' . $time_debut . ' --- ' . $time_fin . '<br>';*/
		
			// La date actuelle fait-elle partie de la période de représentation ?
			if (($date_actuelle >= $time_debut) AND ($date_actuelle <= $time_fin))
			{ $tab.= '<td class="mini" valign="top" align="center" bgcolor="#'.$case_periode_actuelle.'">' ; }
			else
			{ $tab.= '<td class="mini" valign="top" align="center" bgcolor="#'.$case_hors_periode.'">' ; }

			$tab.= $date_event_debut_jour . '-' . $date_event_debut_mois . '-' . $date_event_debut_annee . 
			' &agrave; ' . $date_event_fin_jour . '-' . $date_event_fin_mois . '-' . $date_event_fin_annee . '</td>' ;
			

			// ____________________________________________
			// NOM EVENEMENT
			$tab.= '<td valign="top">';
			
			$nom_event_court = $donnees['nom_event'] ;// Raccourcir la chaine :
			$max=50; // Longueur MAX de la chaîne de caractères
			$chaine_raccourcie = raccourcir_chaine ($nom_event_court,$max); // retourne $chaine_raccourcie
			$tab.= '<a href="edit_event.php?id='. $donnees['id_event'] . '">
			<img src="../design_pics/bouton_edit.gif" width="20" height="14"  hspace="3" title="Editer la fiche de l\'&eacute;v&eacute;nement" alt="" /></a> 
			<a href="effacer_event.php?id_event='. $donnees['id_event'] . '">
			<img src="../design_pics/bouton_delete.gif" width="15" height="14" hspace="3" title="Effacer la fiche de l\'&eacute;v&eacute;nement" alt="" /></a> -
			' . $chaine_raccourcie . '</td></tr>';
			
			
		}
		$tab.= '</table>' ;
		echo $tab ;
		
		// Légende du calendrier 
		$table_legende = '<br /><table border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#FFFFFF">
		  <tr>
			<th align="center">Legende :</th>
		  </tr>
		  <tr>
			<td bgcolor="#'.$case_periode_actuelle.'">Le spectacle est joué actuellement</td>
		  </tr>
		  <tr>
			<td bgcolor="#'.$case_hors_periode.'">Le spectacle n\'est pas joué actuellement</td>
		  </tr>
		</table>';
		echo $table_legende ;
	}
}

//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>
