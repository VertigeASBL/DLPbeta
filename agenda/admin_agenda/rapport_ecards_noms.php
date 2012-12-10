<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Rapport des e-cards</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>


<?php
require '../inc_var_dist_local.php';
require '../inc_var.php';
require '../inc_db_connect.php';


if (isset($_GET['lieu']) AND $_GET['lieu'] != NULL)
{
	$id_lieu = htmlentities($_GET['lieu'], ENT_QUOTES);
}
else
{
	echo '<br /><br /><br /><div class="alerte">erreur paramètre _GET</div><br /><br /><br />' ;
	exit() ;
}

$reponse_lieu = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = $id_lieu");
$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
echo '<h1>Nom des personnes qui ont réservé pour un événement</h1>' ;



echo'<div class="menu_back">
	<a href="rapport_ecards.php">Rapport des e-cards</a>
	<a href="index_admin.php">Menu Admin</a>
</div>';


echo '<br /><br /><h2>Liste des personnes qui ont envoyé une e-card pour l\'événement "' . $donnees_lieu['nom_lieu'] . '"
<i>(rapport du ' . date('d/m/Y à H\hi') . ')</i></h2>' ;


$resultat = mysql_query("SELECT ecards_nom, ecards_email, ecards_pour FROM `ag_rapport_ecards` WHERE ecards_lieu = $id_lieu");
$add = 0 ;
while ($donnees = mysql_fetch_array($resultat))
{
	echo '<br />' . $donnees['ecards_nom'] . ' [' . $donnees['ecards_email'] . '] pour ' . $donnees['ecards_pour'] ;
}
	
//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>