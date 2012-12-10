<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Récapitulatif de statistiques</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Récapitulatif de statistiques 
<?php echo '<i>(le ' . date('d/m/Y à H\hi') . ')</i>' ;?></h1>

<div class="menu_back">
	<a href="index_admin.php">Menu Admin</a></div>

<p class="mini"><strong>Attention</strong> pour les avis : l'agenda comptabilise &eacute;galement les avis de la saison pr&eacute;c&eacute;dente, contrairement &agrave; cette page de stats </p>
<?php
// Choix de la saison à afficher :
echo '<p align="center">Choix de la saison : ' ;
for ($annee_affich = date('Y')+1 ; $annee_affich >=2007 ; $annee_affich -- )
	{
		echo '<a href="rapport_complet.php?annee=' . $annee_affich . '">' . $annee_affich . '</a> - ' ;
	}
echo '</p>' ;
?>

<p>

<?php
require '../inc_var_dist_local.php';
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


// Quelle année afficher ?
if (isset($_GET['annee']) AND $_GET['annee'] != NULL)
{
	$annee_selectionnee = htmlentities($_GET['annee'], ENT_QUOTES);
}
else
{
	$annee_selectionnee = date('Y') ;
}

// Transformer année en saison :
$saison_debut = $annee_selectionnee . '-09-01' ;
$saison_fin = $annee_selectionnee + 1 . '-08-31' ;
//echo $saison_debut . ' à ' . $saison_fin ;

echo '<table width="890" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
    <td colspan="5" align="center"><br /><h2>Saison ' . $annee_selectionnee . '-' . ($annee_selectionnee+1) . '</h2></td>
  </tr>
  <tr>
	<th>Lieu culturel</th>
	<th>Réservations</th>
	<th>Votes</th>
	<th>Avis déposés</th>
	<th>Envoyer à un ami</th>
  </tr>' ;
  

$reponse_lieu = mysql_query("SELECT id_lieu, nom_lieu FROM ag_lieux 
WHERE (ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) 
ORDER BY nom_lieu") or die (mysql_error());
while ($donnees_lieu = mysql_fetch_array($reponse_lieu))
{
	$id_lieu = $donnees_lieu['id_lieu'] ;
	echo '
  <tr class="tr_hover">
	<td><strong>' . $donnees_lieu['nom_lieu'] . '</strong> (id ' . $donnees_lieu['id_lieu'] . ')</td> ' ;



	// +++++++++++++++++++++++++		
	// Réservations :
	// +++++++++++++++++++++++++
	$resultat_nbre_reservations = mysql_query("SELECT reserv_nombre FROM ag_rapport_reservations
	WHERE ((reserv_date > '$saison_debut') AND (reserv_date < '$saison_fin')) 
	AND reserv_lieu = $id_lieu");
	$add = 0 ;
	while ($donnees_resultat_nbre_reservations = mysql_fetch_array($resultat_nbre_reservations))
	{
		$add = $add + $donnees_resultat_nbre_reservations['reserv_nombre'] ;
	}
	echo '<td><strong>' . $add . '</strong> Réservation(s) </td>' ;
	

	
	// +++++++++++++++++++++++++		
	// J'ai vu et j'ai aimé/Votes :
	// +++++++++++++++++++++++++
	$resultat_nbre_votes = mysql_query("SELECT jai_vu_event FROM ag_event 
	WHERE ((date_event_debut > '$saison_debut') AND (date_event_debut < '$saison_fin')) 
	AND lieu_event = $id_lieu");
	$add = 0 ;
	while ($donnees_resultat_nbre_votes = mysql_fetch_array($resultat_nbre_votes))
	{
		$add = $add + $donnees_resultat_nbre_votes['jai_vu_event'] ;
	}
	echo '<td><strong>' . $add . '</strong> Vote(s) </td>' ;



	// +++++++++++++++++++++++++		
	// Avis :
	// +++++++++++++++++++++++++
	$resultat_nbre_avis = mysql_query("SELECT id_event FROM ag_event 
	WHERE ((date_event_debut > '$saison_debut') AND (date_event_debut < '$saison_fin')) 
	AND lieu_event = $id_lieu");
	$add = 0 ;
	//echo '<br /><strong>'.$id_lieu. ' :</strong><br /> ' ;
	while ($donnees_resultat_nbre_avis = mysql_fetch_array($resultat_nbre_avis))
	{
		$id_event_liiouuu = $donnees_resultat_nbre_avis['id_event'] ;
		
		$resultat_nbre_avis_2 = mysql_query("SELECT COUNT(*) AS un_event_nbr_avis FROM ag_avis 
		WHERE event_avis = $id_event_liiouuu") ;
		$donnees_resultat_nbre_avis_2 = mysql_fetch_array($resultat_nbre_avis_2) ;
		$add = $add + $donnees_resultat_nbre_avis_2['un_event_nbr_avis'] ;
		//echo $donnees_resultat_nbre_avis_2['un_event_nbr_avis'] . ' (event'.$id_event_liiouuu . ') + ' ;
	}
	echo '<td><strong>' . $add . '</strong> Avis </td>' ;
	


	// +++++++++++++++++++++++++		
	// e-cards :
	// +++++++++++++++++++++++++		
	$resultat_nbree_cards = mysql_query("SELECT COUNT(*) AS ecard_nombre FROM ag_rapport_ecards 
	WHERE ((ecards_date > '$saison_debut') AND (ecards_date < '$saison_fin')) 
	AND ecards_lieu = $id_lieu") or die('Erreur SQL 2 :<br>'.mysql_error());
	$donnees_resultat_nbre_cards = mysql_fetch_array($resultat_nbree_cards) ;
	echo '<td><strong>' . $donnees_resultat_nbre_cards['ecard_nombre'] . '</strong> ecard(s) </td> ' ;
				
	
	
	echo ' </tr>';
}

echo '</table>' ;

//--- mysql_close($db2dlp);


?>
<p>&nbsp;</p>
</body>
</html>
