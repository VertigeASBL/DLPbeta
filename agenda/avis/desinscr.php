<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>D&eacute;sinscription : ne plus recevoir les avis post&eacute;s par les visiteurs</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1>D&eacute;sinscription : ne plus recevoir les avis post&eacute;s par les visiteurs </h1>
<p>&nbsp;</p>


<?php 

require '../inc_var.php';
require '../inc_db_connect.php';


//--------------------------------------------------------------------------------
// Application pour d�sinscription aux AVIS re�us par e-mail
// L'adresse e-mail � supprimer est v�rifi�e au moyen de la variable "ref" pour �viter l'abus
//--------------------------------------------------------------------------------

// exemple de lien : http://127.0.0.1/comedien/agenda/desinscr.php?ad=pierre@quiroule.com&ref=125982c970

if (empty ($_GET['ad']) OR $_GET['ad'] == NULL OR empty ($_GET['ref']) OR $_GET['ref'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Erreur [GET]<br / > <br / >
	Probl�me lors de votre d�sinscription. Veuillez contactez l\'administrateur du site : 
	<a href="mailto:' . $retour_email_admin . '">' . $retour_email_admin . '</a><br / > <br / >
	<a href="http://www.demandezleprogramme.be/">Retour</a></div>' ;
	exit();
}
else
{
	$adresse = htmlentities($_GET['ad'], ENT_QUOTES);
	$reference = htmlentities($_GET['ref'], ENT_QUOTES);
}


$reponse_mailing = mysql_query("SELECT * FROM $table_avis_mailing 
WHERE avis_mailing_adresse = '$adresse' AND ref_avis_mailing = '$reference' ") ;
$donnees_mailing = mysql_fetch_array($reponse_mailing) ;
if ($donnees_mailing == '')
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Vou n\'�tes pas repris dans notre base de donn�es.<br />
	Si vous continuez toutefois � recevoir ces publications, veuillez le signaler � l\'administrateur du site : 
	<a href="mailto:' . $retour_email_admin . '">' . $retour_email_admin . '</a><br / > <br / >
	<a href="http://www.demandezleprogramme.be/">Retour</a></div>' ;
	exit();
}
else
{
	$resultat_effacement = mysql_query("DELETE FROM $table_avis_mailing 
	WHERE avis_mailing_adresse = '$adresse' AND ref_avis_mailing = '$reference' ");
	if ($resultat_effacement)
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="info">Votre d�sinscription a �t� effectu�e avec succ�s <br / > <br / >
		<a href="http://www.demandezleprogramme.be/">Retour</a></div>' ;
	}
}


?>

<?PHP //--- mysql_close($db2dlp); ?> 

</body>
</html>
