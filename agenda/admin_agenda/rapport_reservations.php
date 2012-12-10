<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Rapport des r&eacute;servations</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Rapport des r&eacute;servations 
<?php echo '<i>(le ' . date('d/m/Y à H\hi') . ')</i>' ;?></h1>

<div class="menu_back">
	<a href="index_admin.php">Menu Admin</a>
</div>

<?php
// Choix de l'année à afficher :
echo ' <p align="center">Choix de l\'année : ' ;
for ($annee_affich = date('Y') ; $annee_affich >=2009 ; $annee_affich -- )
	{
		echo '<a href="rapport_reservations.php?annee=' . $annee_affich . '">' . $annee_affich . '</a> - ' ;
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

/*$reserv_event = 94 ;
$reserv_lieu = 9 ;
$reserv_nombre = 2 ;
$reserv_date = '2008-01-22' ;

mysql_query("INSERT INTO `ag_rapport_reservations` ( `id_reservation` , `reserv_event` , `reserv_lieu` , `reserv_nombre` , `reserv_date` ) 
VALUES ('', '$reserv_event', '$reserv_lieu', '$reserv_nombre', '$reserv_date' )");*/

$tab ='<table width="650" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
    <td colspan="4" align="center">Année ' . $annee_selectionnee . '</td>
  </tr>
  <tr>
	<th>Voir les noms</th>
	<th>Lieu culturel</th>
	<th>Totalité<br />places<br />réservées</th>
  </tr>' ;
  

$reponse_lieu = mysql_query("SELECT id_lieu, nom_lieu FROM $table_lieu WHERE cotisation_lieu > CURDATE() 
AND (email_reservation != '' )
ORDER BY nom_lieu");
while ($donnees_lieu = mysql_fetch_array($reponse_lieu))
{
	$id_lieu = $donnees_lieu['id_lieu'] ;
	
	/*$resultat_nbre = mysql_query("SELECT COUNT(*) AS nbre_reserv_lieu FROM `ag_rapport_reservations` 
	WHERE YEAR(reserv_date) = $annee_selectionnee AND reserv_lieu = $id_lieu");
	$donnees_resultat_nbre = mysql_fetch_array($resultat_nbre);*/

	$tab.='<tr><td><a href="rapport_reservations_noms.php?lieu=' . $donnees_lieu['id_lieu'] . '" target="_blank">&gt;&gt; Noms</a></td>
	<td>' . $donnees_lieu['nom_lieu'] . ' <em>(id ' . $id_lieu . ')</em></td>' ;
	
		
	$resultat_nbre = mysql_query("SELECT reserv_nombre FROM `ag_rapport_reservations` 
	WHERE YEAR(reserv_date) = $annee_selectionnee AND reserv_lieu = $id_lieu");
	$add = 0 ;
	while ($donnees_resultat_nbre = mysql_fetch_array($resultat_nbre))
	{
		$add = $add + $donnees_resultat_nbre['reserv_nombre'] ;
	}
	$tab.='<td><strong>' . $add . '</strong></td>' ;
	
	// visualisation graphique 
	/*$tab.='<td>&nbsp;
	/*for ($nb = $add ; $nb >0 ; $nb -- )
	{
		$tab.= '|' ;
	}
	$tab.= '</td></tr>' ;*/
}

$tab.= '</table>' ;
echo $tab ;

//--- mysql_close($db2dlp);


?>
<p class="mini"><strong>Notes</strong> : L'ann&eacute;e est l'ann&eacute;e de repr&eacute;sentation du spectacle et non l'ann&eacute;e du jour de la r&eacute;servation. 

Les LIEUX apparaissant ici sont uniquement les lieux  qui acceptent les r&eacute;servations en ligne (donc qui poss&egrave;dent  une adresse e-mail de destination pour les r&eacute;servations) <u>et</u> qui sont en ordre de cotisation.
<p>&nbsp;</p>
</body>
</html>

</p>
