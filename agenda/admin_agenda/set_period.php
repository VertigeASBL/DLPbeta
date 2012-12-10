<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>R&eacute;glage p&eacute;riode affichage page par d&eacute;faut de la page AGENDA</title>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">

</head>

<body>

<div id="head_admin_agenda"></div>
<h1>Ajustement du nombre d'&eacute;v&eacute;nements affich&eacute;s sur la page AGENDA par défaut </h1>
<div class="menu_back">
<a href="index_admin.php">Menu Admin</a></div>

<?php
require '../inc_var.php';
require '../inc_db_connect.php';

/*
*************************** info  **************************** 
**************************************************************
Voir fichier "spip/lecture_resultats.php" ligne "Au chargement de la page"
*/


// Si on a appuyé sur le bouton d'enregistrement : 
if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'update'))
{
	if (!empty($_POST['chp_var_date_debut']) AND preg_match('/[0-9]$/', $_POST['chp_var_date_debut'])	
	AND !empty($_POST['chp_var_date_fin']) AND preg_match('/[0-9]$/', $_POST['chp_var_date_fin']))	
	{
		/* w+ = ouverture en lecture et écriture (la fonction crée le fichier s'il n'existe pas)
		http://www.commentcamarche.net/contents/php/phpfich.php3 */
		$fichier_date_debut = fopen('../ctrl_periode/date_debut.txt', 'w+'); 
		$fichier_date_fin = fopen('../ctrl_periode/date_fin.txt', 'w+');

		$chp_var_date_debut = htmlentities($_POST['chp_var_date_debut'], ENT_QUOTES);
		$chp_var_date_fin = htmlentities($_POST['chp_var_date_fin'], ENT_QUOTES);

		fseek($fichier_date_debut, 0); // remettre curseur au début du fichier
		fseek($fichier_date_fin, 0); // remettre curseur au début du fichier
		
		fputs($fichier_date_debut, $chp_var_date_debut); // On écrit le nouveau nombre de pages vues
		fputs($fichier_date_fin, $chp_var_date_fin); // On écrit le nouveau nombre de pages vues
		
		fclose($fichier_date_debut);
		fclose($fichier_date_fin);
	}
	else echo '<div class="error_form">Vous devez encoder un nombre (supérieur à zéro) dans chacun des champs</div>';
}

/* r = ouverture en lecture seulement
http://www.commentcamarche.net/contents/php/phpfich.php3 */
$fichier_date_debut = fopen('../ctrl_periode/date_debut.txt', 'r'); 
$fichier_date_fin = fopen('../ctrl_periode/date_fin.txt', 'r');

fseek($fichier_date_debut, 0); // remettre curseur au début du fichier
fseek($fichier_date_fin, 0); // remettre curseur au début du fichier

$var_date_debut = fgets($fichier_date_debut); // lecture de la première ligne 
$var_date_fin = fgets($fichier_date_fin); // lecture de la première ligne 
echo '
<form action="" method="post">

<p>Distance maximum entre le début d\'un événement déjà commencé et aujourd\'hui : <input name="chp_var_date_debut" type="text" id="chp_var_date_debut" value="' . $var_date_debut . '" size="4" maxlength="3"> (en jours)</p>

<p>Distance maximum entre aujourd\'hui et le début un événement futur : <input name="chp_var_date_fin" type="text" id="chp_var_date_fin" value="' . $var_date_fin . '" size="4" maxlength="3"> (en jours)

<p><input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update"></p>

</form>' ;

fclose($fichier_date_debut);
fclose($fichier_date_fin);

//----------------------------------------------------------------------
// Afficher le nombre d'événement affichés pour la période délimitée :
//----------------------------------------------------------------------

$date_debut = date ('Y-m-d', $date_debut_minimum = mktime(0, 0, 0, date("m")  , date("d")-$var_date_debut, date("Y")));
$date_fin = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+$var_date_fin, date("Y")));

$query_count = "SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda INNER JOIN  $table_lieu L
	ON (cotisation_lieu > CURDATE()) AND lieu_event = id_lieu
	WHERE (
	date_event_debut >= '$date_debut') 
	AND (date_event_debut <= '$date_fin') 
	AND (date_event_fin >= '$date_debut') 
	";
		 
$reponse_count = mysql_query($query_count) or die($query_count . " ----- " . mysql_error());
$donnees_count = mysql_fetch_array($reponse_count);
$total_entrees = $donnees_count['nbre_entrees'];
		 
echo '<strong> ==> Nombre d\'événements : ' . $total_entrees . '</strong>' ;


?>
<br />
<br />
<br />
<br />

<p align="center"><img src="../ctrl_periode/support_visuel_periode_agenda.jpg" alt="Support visuel" width="570" height="400" /></p>
</body>
</html>
