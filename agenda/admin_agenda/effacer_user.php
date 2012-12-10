<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Effacement d'un USER</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<link href="../css_calendrier.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="head_admin_agenda"></div>

<!-- h1 plus bas -->

<div class="menu_back"><a href="index_admin.php">Retour au menu Admin</a>
</div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


if (isset ($_GET['id_user']) AND $_GET['id_user'] != NULL) // La variable GET qui donne l'ID de l'événement à effacer de la TABLE
{
	$id_user = htmlentities($_GET['id_user'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_user_agenda WHERE id_admin_spec = $id_user");
	$donnees = mysql_fetch_array($reponse);

	//--------------------------------------------------------------------------------------------------------------
	//EFFACEMENT de l'entrée si appuyé bouton
	
	if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'effacer'))
	{
		$test_mysql = mysql_query("DELETE FROM $table_user_agenda WHERE id_admin_spec = $id_user");
		if ($test_mysql)
		{	
			echo '<br><br><br><div class="info"><p>L\'utilisateur a bien été effacée 
			<a href="listing_lieux_culturels.php">Retour au listing (' . $id_user . ')</a></p></div><br>' ;
		}
		else
		{
			echo 'echec effacement' ;
		}
		//--- mysql_close($db2dlp);
		exit();
	}
	
	
	// -------------------------------------------------------------------------------
	// Afficher message de confirmation et bouton :
	$reponse = mysql_query("SELECT * FROM $table_user_agenda WHERE id_admin_spec = '$id_user'");
	$donnees = mysql_fetch_array($reponse);
	
	echo '<h1 align="center">Effacement de l\'utilisateur : ' . $donnees ['nom_admin_spec'] . '</h1><p>&nbsp;</p>' ;
	
	$message_confirmation = '<p>&nbsp;</p><p>&nbsp;</p><div class ="alerte">
	Voulez-vous effacer définitivement l\'utilisateur suivant ?<br /><br />';
	$message_confirmation.= '<b>' . $donnees ['nom_admin_spec'] . '</b> - ' ;
	$message_confirmation.= '<i>(ID=' . $donnees ['id_admin_spec'] . ')</i><br />' ;
	
	echo 	$message_confirmation ;
	
	?>
	<br />
	<form name="form1" method="post" action="">
	<div align="center"> 
	<input name="bouton_effacer" type="submit" id="bouton_effacer" value="effacer">
	</div>
	</form>
	<br />
	<br />
	<form name="form2" method="post" action="listing_lieux_culturels.php">
	<div align="center"> 
	<input name="bouton_annuler" type="button" id="bouton_annuler" value="annuler" onClick="history.go(-1)">
	</div>
	</form>
	

	
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