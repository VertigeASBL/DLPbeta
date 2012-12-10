<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Effacement de la fiche d'un &eacute;v&eacute;nement culturel</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<link href="../css_calendrier.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="head_admin_agenda"></div>

<!-- h1 plus bas -->

<div class="menu_back">
<a href="spectateurs_listing.php">Listing des Spectateurs</a>
<a href="index_admin.php">Menu Admin</a>
</div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


//---------------------------------------------------------
// Test sur variable GET :
//---------------------------------------------------------
//L'entrée donnée par GET existe-t-elle :
if (empty ($_GET['id_spect']) OR $_GET['id_spect'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="spectateurs_listing.php">Retour</a></div>' ;
	exit();
}
else
{
	$id_spectateur = htmlentities($_GET['id_spect'], ENT_QUOTES);
}


//--------------------------------------------------------------------------------------------------------------
//EFFACEMENT de l'entrée si appuyé bouton

if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'effacer'))
{
	$test_mysql = mysql_query("DELETE FROM `$table_spectateurs_ag` WHERE `id_spectateur` = $id_spectateur");

	if ($test_mysql)
	{		
		// Effacer vignette et image
		for ($eeuu = 1; $eeuu < 4; $eeuu++)
		{
			$vignette_2_delete = '../' . $folder_pics_spectateurs . 'spect_' . $id_spectateur . '_' . $eeuu . '.jpg' ;
			$pic_2_delete = '../' . $folder_pics_spectateurs . 'vi_spect_' . $id_spectateur . '_' . $eeuu . '.jpg' ;
			if (file_exists($vignette_2_delete))
			{
				$rep_im = unlink ($pic_2_delete) ;
				$rep_vi = unlink ($vignette_2_delete) ;
				
				if ($rep_im) { echo '<br>Image ' . $id_spectateur . '_' . $eeuu . ' effacée' ; }
				if ($rep_vi) { echo '<br>Vignette ' . $id_spectateur . '_' . $eeuu . ' effacée' ; }
			}
		}
		echo '<br><br><br><div class="info"><p>L\'entrée a bien été effacée 
		<a href="spectateurs_listing.php">Retour au listing</a></p></div><br>' ;
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
$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
$donnees = mysql_fetch_array($reponse);

echo '<h1 align="center">Effacement du profil du spectateur : '
. $donnees ['nom_spectateur'] . ' ' . $donnees ['prenom_spectateur'] . '</h1><p>&nbsp;</p>' ;

$message_confirmation = '<p>&nbsp;</p><p>&nbsp;</p><div class ="alerte">
! Les images liées seront effacées ! <br />
Voulez-vous effacer définitivement le profil du spectateur suivant : <strong>'
. $donnees ['nom_spectateur'] . ' ' . $donnees ['prenom_spectateur'] . '</strong> ?<br /><br />';
$message_confirmation.= '<i>(ID=' . $donnees ['id_spectateur'] . ')</i>' ;

echo 	$message_confirmation ;

?>
<br />	<br />
<form name="form1" method="post" action="">
<div align="center"> 
<input name="bouton_effacer" type="submit" id="bouton_effacer" value="effacer">
</div>
</form>
<br />
<form name="form2" method="post" action="listing_events.php?lieu=<?php echo $id_spectateur ; ?>">
<div align="center"> 
<input name="bouton_annuler" type="button" id="bouton_annuler" value="annuler" onClick="history.go(-1)">
</div>
</form>
	
<?php 
echo '</div>';


//--- mysql_close($db2dlp);

?>
</body>
</html>