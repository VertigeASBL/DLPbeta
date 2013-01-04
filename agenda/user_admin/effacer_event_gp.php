<?php 
session_start();
?>

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

<?php 

require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?>

<div id="head_admin_agenda"></div>

<!-- h1 plus bas -->

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


//-----------------------------------------------------------------------------------
// Verifier que l'événement est bien rattaché au lieu culturel auquel l'utilateur loggé appartient
//-----------------------------------------------------------------------------------
if (isset($_GET['id']) AND ($_GET['id'] != NULL ))
{
	$id = htmlentities($_GET['id'], ENT_QUOTES) ; // Correspond à l'ID du LIEU (provient du listing ou de nouvelle entrée vide.
	$reponse_test = mysql_query("SELECT lieu_event FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees_test = mysql_fetch_array($reponse_test);
	if ($donnees_test['lieu_event'] != $_SESSION['lieu_admin_spec'])
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">ERREUR <br>ID nul ou non attribué à votre compte</div><br>' ;
		exit () ;
	}
}
else
{
	echo '<p>&nbsp;</p><p>&nbsp;</p>
	<div class="alerte">Vous ne pouvez pas accéder à cette page de cette façon.<br>(GET indéfini)</div><br>' ;
	exit () ;
}


echo '<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><div class="alerte">
	<p>Cette opération est désactivée.</p> 
	<p>Si vous souhaitez toutefois y recourir, l\'administrateur du site demandezleprogramme peut effectuer cette opération pour vous. Contactez-le à l\'adresse <a href="' .$retour_email_admin . '">' .$retour_email_admin . '</a> en n\'oubliant pas de lui transmettre le numéro d\'identification du spectacle que voici : <b>'. $id . '</b>.</p>
	
	<p><a href="listing_events_gp.php">Retour</a></p></div>' ;
//<a href="#" onClick="history.go(-1)>Retour</a>

exit () ;

//--------------------------------------------------------------------------------------------------------------
//EFFACEMENT de l'entrée si appuyé bouton

if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'effacer'))
{
	$reponse = mysql_query("SELECT pic_event_1 FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees = mysql_fetch_array($reponse);
	
	$lieu_admin_spec_session = $_SESSION['lieu_admin_spec'] ;
	
	$test_mysql = mysql_query("DELETE FROM $table_evenements_agenda WHERE id_event = $id
	AND lieu_event = $lieu_admin_spec_session ");
	if ($test_mysql)
	{		
		// Effacer vignette et image
//		$vignette_2_delete = '../' . $folder_pics_event . 'vi_event_' . $id . '_1.jpg' ;
		$pic_2_delete = '../' . $folder_pics_event . 'event_' . $id . '_1.jpg' ;
		if (file_exists($pic_2_delete))
 		{
			echo '<br>Des images sont liées à cet événement' ;
//			$rep_vi = unlink ($vignette_2_delete) ;
			$rep_im = unlink ($pic_2_delete) ;
			
			if ($rep_vi) { echo '<br>Vignette et image effacées' ; }
		}

		echo '<br><br><br><div class="info"><p>L\'entrée a bien été effacée 
		<a href="listing_events_gp.php">Retour au listing</a></p></div><br>' ;
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
$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
$donnees = mysql_fetch_array($reponse);

echo '<h1 align="center">Effacement de la fiche spectacle : ' . $donnees ['nom_event'] . '</h1><p>&nbsp;</p>' ;

$message_confirmation = '<p>&nbsp;</p><p>&nbsp;</p><div class ="alerte">
Voulez-vous effacer définitivement la fiche descriptive de l\'événement suivant ?<br /><br />';
$message_confirmation.= '<b>' . $donnees ['nom_event'] . '</b>' ;
$message_confirmation.= '<i>(ID=' . $donnees ['id_event'] . ')</i></div>' ;

echo $message_confirmation ;

?>
  <br />
</p>
<form name="form1" method="post" action="">
<div align="center"> 
<input name="bouton_effacer" type="submit" id="bouton_effacer" value="effacer">
</div>
</form>
<br />
<br />
<form name="form2" method="post" action="listing_events.php?lieu=<?php echo $id_event ; ?>">
<div align="center"> 
<input name="bouton_annuler" type="button" id="bouton_annuler" value="annuler" onClick="history.go(-1)">
</div>
</form>

<p>&nbsp; </p>

</body>
</html>
