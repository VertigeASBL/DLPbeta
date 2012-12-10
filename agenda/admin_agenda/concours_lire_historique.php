<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Affichage de l'historique des tirages de concours</title>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="head_admin_agenda"></div>
<h1>Affichage de l'historique des tirages de concours </h1>

<div class="menu_back">
<a href="conc_2_listing.php" >Listing des concours  </a></div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../fct_upload_vign_concours.php';


$reponse_conc_histo = mysql_query("SELECT * FROM $table_ag_conc_historique ORDER BY id_conc_histo DESC");
while ($donnees_conc_histo = mysql_fetch_array($reponse_conc_histo))
{
	$id_conc_histo = $donnees_conc_histo['id_conc_histo'] ;
	$id_fiche_conc_histo = $donnees_conc_histo['id_fiche_conc_histo'] ;
	$detail_conc_histo = $donnees_conc_histo['detail_conc_histo'] ;
	
	echo '<br /> <br /> <div class="titre_bordeau"> ID ' . $id_conc_histo . ' - 
	Fiche Concours ' . $id_fiche_conc_histo . ' </div> ' .
	stripslashes ($detail_conc_histo) ;
}




?>
<p> </p>
</body>
</html>
