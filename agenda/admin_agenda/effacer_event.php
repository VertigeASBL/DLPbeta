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

<div class="menu_back"><a href="index_admin.php">Retour au menu Admin</a></div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


/* POUR USERS : VERIFIER QUE id_event APPARTIENT BIEN AU LIEU CULTUREL LOGGé (via session) */
if (isset ($_GET['id_event']) AND $_GET['id_event'] != NULL) // La variable GET qui donne l'ID de l'événement à effacer de la TABLE
{
	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = $id_event");
	$donnees = mysql_fetch_array($reponse);
	$id_lieu = $donnees['lieu_event'] ;
	
	/*$reponse_2 = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $id_lieu");
	$donnees_2 = mysql_fetch_array($reponse_2) ;
	
	echo $donnees_2['nom_lieu'] . '<br><i>' ;*/

	//--------------------------------------------------------------------------------------------------------------
	//EFFACEMENT de l'entrée si appuyé bouton
	
	if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'effacer'))
	{
		$test_mysql = mysql_query("DELETE FROM `$table_evenements_agenda` WHERE `id_event` = $id_event");
	
		if ($test_mysql)
		{		
			// Effacer vignette et image
			for ($eeuu = 1; $eeuu < 4; $eeuu++)
			{
				
//				$vignette_2_delete = '../' . $folder_pics_event . 'vi_event_' . $id_event . '_' . $eeuu . '.jpg' ;
				$pic_2_delete = '../' . $folder_pics_event . 'event_' . $id_event . '_' . $eeuu . '.jpg' ;
				if (file_exists($pic_2_delete))
				{
					$rep_im = unlink ($pic_2_delete) ;
//					$rep_vi = unlink ($vignette_2_delete) ;
					
					if ($rep_im) { echo '<br>Image ' . $id_event . '_' . $eeuu . ' effacée' ; }
//					if ($rep_vi) { echo '<br>Vignette ' . $id_event . '_' . $eeuu . ' effacée' ; }
				}
			}
			echo '<br><br><br><div class="info"><p>L\'entrée a bien été effacée 
			<a href="listing_events.php?lieu=' . $id_lieu . '">Retour au listing (' . $id_lieu . ')</a></p></div><br>' ;
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
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
	$donnees = mysql_fetch_array($reponse);
	
	echo '<h1 align="center">Effacement de la fiche spectacle : ' . $donnees ['nom_event'] . '</h1><p>&nbsp;</p>' ;
	
	$message_confirmation = '<p>&nbsp;</p><p>&nbsp;</p><div class ="alerte">
	! Les images liées à cet événement seront effacées ! <br />
	Voulez-vous effacer définitivement la fiche descriptive de l\'événement suivant ?<br /><br />';
	$message_confirmation.= '<b>' . $donnees ['nom_event'] . '</b> - ' ;
	$message_confirmation.= '<i>(ID=' . $donnees ['id_event'] . ')</i>' ;
	
	echo 	$message_confirmation ;
	
	?>
	<br />	<br />
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
		
	<?php 
	echo '</div>';
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