<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Effacement de la fiche d'un lieu culturel</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<link href="../css_calendrier.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="head_admin_agenda"></div>

<!-- h1 plus bas -->

<div class="menu_back"><a href="index_admin.php">Retour au menu  Admin</a>
</div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';

require '../../comgestion/approComCie.php'; // Système "gestion des abonnements" de Philippe

/* POUR USERS : VERIFIER QUE id_lieu APPARTIENT BIEN AU LIEU CULTUREL LOGGé (via session) */
if (isset ($_GET['id_lieu']) AND $_GET['id_lieu'] != NULL) // La variable GET qui donne l'ID du LIEU culturel à effacer de la TABLE
{
	$id_lieu = htmlentities($_GET['id_lieu'], ENT_QUOTES);


	//--------------------------------------------------------------------------------------------------------------
	//EFFACEMENT de l'entrée si appuyé bouton
	
	if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'effacer'))
	{
		mysql_query("DELETE FROM `$table_lieu` WHERE `id_lieu` = $id_lieu");
	
		echo '<br><br><br><div class="info"><p>L\'entrée a bien été effacée 
		<a href="listing_lieux_culturels.php">Retour au listing</a></p></div><br>' ;
		
		// --------------------------------------------------------
		// Lien avec le système "gestion des abonnements" de Philippe
		// (fichier /public/comgestion/approComCie.php)
		// --------------------------------------------------------
		supprimerLieu($id_lieu); // La fonction renvoie vrai si le lieu était désapprouvé et qu'il a été activé).
		exit();
	}
	
	
	// -------------------------------------------------------------------------------
	// Afficher message de confirmation et bouton :
	$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $id_lieu");
	$donnees = mysql_fetch_array($reponse);
	
	echo '<h1 align="center">Effacement de la fiche du lieu culturel : ' . $donnees ['nom_lieu'] . '</h1><p>&nbsp;</p>' ;
	
	$message_confirmation = '<p>&nbsp;</p><p>&nbsp;</p><div class ="alerte">
	Veuillez effacer les utilisateurs liés à ce compte avant de supprimer le compte !<br><br>
	Voulez-vous effacer définitivement la fiche descriptive de du lieu culturel suivant ?<br /><br />';
	$message_confirmation.= '<b>' . $donnees ['nom_lieu'] . '</b> - ' ;
	$message_confirmation.= '<i>(ID=' . $donnees ['id_lieu'] . ')</i>' ;
	
	echo 	$message_confirmation ;
	
	?>
	<br /> <br />
	<form name="form1" method="post" action="">
	<div align="center"> 
	<input name="bouton_effacer" type="submit" id="bouton_effacer" value="effacer">
	</div>
	</form>
	<br />
	<form name="form2" method="post" action="listing_events.php?lieu=<?php echo $id_event ; ?>">
	<div align="center"> 
	<input name="bouton_annuler" type="button" id="bouton_annuler" value="annuler" onClick="history.go(-1)">
	</div>
	</form>
	
	<p>&nbsp; </p>
	
	
<?php 
echo '</div>' ;
}
else
{
	echo '<br><br><br><div class="alerte">Erreur GET</div><br>' ;

	//--- mysql_close($db2dlp);
	exit();
}
?>

</body>
</html>
