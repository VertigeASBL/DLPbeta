<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Confirmation de l'inscription par l'administrateur (c)</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Approbation d'une  inscription par l'administrateur (c)</h1>

<div class="menu_back">
<a href="index_admin.php">Menu Admin</a> | 
<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a>
</div>

<?php
require '../inc_var.php';
require '../inc_var_dist_local.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';

require '../../comgestion/approComCie.php'; // Système "gestion des abonnements" de Philippe

$group_admin_spec = '3' ;// Goupe auquel appartiendra le USER inscrit

// ---------------------------------------------------------------------------
// Page de confirmation de l'inscription par l'admin -> envoi email avec codes
// Note : on peut confirmer une inscription direxctement via "c.php?id=..."
// ---------------------------------------------------------------------------

//-------------------------------------------------------------------------------------------
// Effacement d'une demande d'affiliation si l'administrateur a appuyé sur le bouton
//-------------------------------------------------------------------------------------------
if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'Ok')) 
{
	$id = htmlentities($_GET['id'], ENT_QUOTES);
	if (mysql_query("DELETE FROM $table_conf_inscript WHERE id = $id"))
	{
		echo '<div class="info"><p>L\'entrée a été effacée<br />
		<a href="c.php">Retour</a></div>' ;
		exit ();
	}
	else { echo '<div class="alerte">ERREUR EFFACEMENT</div>'  ;}
}

//-------------------------------------------------------------------------------------------
// Enregistrer dans DB si l'administrateur a confirmé l'inscription en appuyant sur le bouton
//-------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Ok')) 
{
	$id = htmlentities($_GET['id'], ENT_QUOTES);

	// Récupération données :
	$reponse = mysql_query("SELECT * FROM $table_conf_inscript WHERE id = '$id'");
	$donnees = mysql_fetch_array($reponse);
	
	$nom = $donnees ['nom'];
	$prenom = $donnees ['prenom'];
	$nom_lieu = $donnees ['societe'];
	$tel_lieu = $donnees ['tel_1'];
	$email_lieu = $donnees ['e_mail'];
	
	if (isset($donnees ['web_site']) AND $donnees ['web_site'] != NULL )
	{ $web_site = $donnees ['web_site']; }
	else
	{ $web_site = ''; }
	
	$adresse_lieu = $donnees ['adresse'];

	// Vérifier que l'inscription n'est pas déjà confirmé (utile en cas d'accès direct sans le tableau récapitulatif)
	if ($donnees ['confirm'] == 'fin')
	{
		echo '<div class="alerte">Vous avez déjà approuvé l\'inscription de <b>' . $donnees ['nom'] . ' '. $donnees ['prenom'] . 
		'</b> (' . $donnees ['societe'] . ' - ID = ' . $donnees ['id'] . ')<br /> <br />
		<a href="listing_lieux_culturels.php">Voir la liste des lieux affiliés</a> - 
		<a href="c.php">Retour</a></div>' ;
		exit () ;
	}
	else
	{	
		// écriture des données dans la TABLE définitive :
		$date_fin_cotisation = '';
		//$date_fin_cotisation = date ('Y-m-d', mktime(0, 0, 0, date("m")  , date("d"), date("Y")+1)); // j+1an
		
		if (mysql_query("INSERT INTO `$table_lieu` ( `nom_lieu` , `tel_lieu` , `e_mail_lieu` , `web_site_lieu` , `adresse_lieu` , `cotisation_lieu` )
		VALUES ('$nom_lieu', '$tel_lieu', '$email_lieu', '$web_site', '$adresse_lieu', '$date_fin_cotisation')"))
		{
			$dernier_id_table_lieu = mysql_insert_id() ; // sera utile pour lier la TABLE "user_agenda"
				
			// --------------------------------------------------------
			// Générer USER et PW 
			
			// USER
			$txt = "abcdefghijkmnpqrstuvwxyzaeiou"; 
			$txt = str_shuffle($txt);
			$log_string = substr($txt, 10, 5);
	
			// PW
			$txt = "abcdefghijkmnpqrstuvwxyz123456789123458"; 
			$txt = str_shuffle($txt);
			$pw_string = substr($txt, 10, 6);
			//$pw_string_md5 = md5($pw_string); // pour stockage dans DB
	
	
			// --------------------------------------------------------
			// Créer une entrée dans la TABLE "user_agenda" pour l'authentification du responsable du théâtre
			// --------------------------------------------------------
			
			mysql_query("INSERT INTO `$table_user_agenda` ( `lieu_admin_spec` , `nom_admin_spec` , `e_mail_admin_spec` ,
			`tel_admin_spec` , `log_admin_spec` , `pw_admin_spec` , `group_admin_spec` , `adr_factur_admin_spec`) 
	
			VALUES ('$dernier_id_table_lieu', '$nom', '$email_lieu', '$tel_lieu', '$log_string', '$pw_string',
			'$group_admin_spec',  '$adresse_lieu')");
			
			
			// --------------------------------------------------------
			// Envoi e-mail de confirmation à l'abonné
			// --------------------------------------------------------
			$sujet='-- Votre inscription à l\'agenda de comedien.be est confirmée';
		
			$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
			"http://www.w3.org/TR/html4/loose.dtd">
			<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<style type="text/css">
			<!--
			' . $css_email . '
			-->
			</style></head><body>
					
			<p><b>Pour ' . $prenom . ' ' . $nom . ' </b><br />
			
			<p>Votre inscription &agrave; <a href="http://www.demandezleprogramme.be/">http://www.demandezleprogramme.be/</a> 
			est &agrave; pr&eacute;sent confirm&eacute;e. Voici vos codes personnels : </p>
			<p><b>Identifiant </b>: ' . $log_string . '<br />
			<b>Mot de passe : </b>' . $pw_string . '</p>
			<p>Ces codes personnels sont &agrave; introduire dans le formulaire  destin&eacute; &agrave; vous identifier. 
			Pour acc&eacute;der &agrave; ce formulaire, cliquez sur les  petites cl&eacute;s en haut &agrave; droite du site, 
			&agrave; c&ocirc;t&eacute; de l\'enveloppe. <br />Vous pouvez d&egrave;s maintenant modifier et configurer vos 
			&eacute;v&eacute;nements.</p>
			
			<p>Attention, si vous utilisez un Mac, entrez dans votre compte via le navigateur Firefox 
			que vous pouvez t&eacute;l&eacute;charger gratuitement <a href="http://www.mozilla-europe.org/fr/">ici</a>. 
			Sous Safari, les changements que vous op&eacute;rez ne sont pas pris en compte.</p>
			
			<p>Bien &agrave; vous,</p>
			<p>L\'&eacute;quipe de com&eacute;dien.be</p><br />
			<p class="email_style_petit">Vertige asbl<br />
			163, rue de la Victoire 1060 Bruxelles<br />
			tel/fax 02 544 00 34<br />
			<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a></p>
			<p>&nbsp;</p>
			</body></html>
			</html>'; 
	
			$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin ;
		 mail_beta($email_lieu,$sujet,$corps,$entete,$email_retour_erreur); 
			// echo $corps ;
			
			// --------------------------------------------------------
			// Indiquer dans la TABLE que l'inscription est définitivement approuvée avec le tag 'fin'
			// --------------------------------------------------------
			mysql_query("UPDATE $table_conf_inscript SET confirm = 'fin' 
			WHERE id ='" . $id . "' LIMIT 1 ") ;
			
			
			// --------------------------------------------------------
			// Lien avec le système "gestion des abonnements" de Philippe
			// (fichier /public/comgestion/approComCie.php)
			// --------------------------------------------------------
			$alerter = '';
			approuverLieu($dernier_id_table_lieu, false); // La fonction renvoie vrai si le lieu était désapprouvé et qu'il a été activé).
			if ($alerter)
				echo '<div class="alerte">',$alerter,'</div>';
			unset($alerter);

			// --------------------------------------------------------
			// Afficher message confirmation et bouton de retour
			echo '<div class="info"><p>Les données ont bien été enregistrées<br />
			<a href="c.php">Retour</a></div>' ;
			exit ();
		}
		else
		{
			echo '<div class="alerte">Erreur d\'écriture dans la DB</div>';
		}
	}
}

// -------------------------------------------------------------------------------------------------
// Récupération de la variable $_GET['id'] pour savoir quelle entrée APPROUVER ou EFFACER
// -------------------------------------------------------------------------------------------------

if (isset($_GET['id']) AND $_GET['id'] != NULL) // Tester la variable GET qui donne l'ID à confirmer
{
	$id = htmlentities($_GET['id'], ENT_QUOTES);

	// Récupération données :
	$reponse = mysql_query("SELECT * FROM $table_conf_inscript WHERE id = '$id'");
	$donnees = mysql_fetch_array($reponse);
	
	$nom = $donnees ['nom'];
	$prenom = $donnees ['prenom'];
	$nom_lieu = $donnees ['societe'];
	$tel_lieu = $donnees ['tel_1'];
	$email_lieu = $donnees ['e_mail'];
	
	if (isset($donnees ['web_site']) AND $donnees ['web_site'] != NULL )
	{ $web_site = $donnees ['web_site']; }
	else
	{ $web_site = ''; }
	
	$adresse_lieu = $donnees ['adresse'];
	
		
	// Afficher les infos et bouton de confirmation pour approbation ou effacement :
	$var_info='<p><b>Nom</b> : ' . $nom . ' <br />';
	$var_info.='<b>Pr&eacute;nom</b> : ' . $prenom . ' </p>';
	$var_info.='<p><b>D&eacute;nomination du lieu culturel</b> : ' . $nom_lieu . ' <br />';
	$var_info.='<b>Adresse</b> : ' . $adresse_lieu . ' <br />';
	$var_info.='<b>e-mail</b> : ' . $email_lieu . ' <br /></p>';
	$var_info.='<p><b>ID</b> : ' . $donnees ['id'] . '</p>';

	// Afficher bouton de confirmation "Approuver"
	if (isset($_GET['action']) AND $_GET['action'] == 'approuver') 
	{
		echo '<br><br><div class="alerte"><p>' . $var_info . '</p>
		<p><form name="form1" method="post" action="">
		<b>Approuver l\'inscription</b><br />
		<input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Ok">
		</form></p></div>';
	}
	
	// Afficher bouton de confirmation "Effacement"
	if (isset($_GET['action']) AND $_GET['action'] == 'effacer') // Afficher bouton de confirmation "Effacer la demande"
	{
		echo '<br><br><div class="alerte"><p>' . $var_info . '</p>
		<p><form name="form1" method="post" action="">
		<b>Refuser et effacer cette demande d\'inscription</b><br />
		<input name="bouton_effacer" type="submit" id="bouton_effacer" value="Ok">
		</form></p></div>';
	}
	echo '<div class="info"><a href="c.php">Annuler</a>'; 
}
else
{
	// ----------------------------------------------
	// Affichage de toutes les demandes d'inscription
	echo '<table width="750" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
	<tr><th>Prénom - Nom</th><th>Lieu culturel</th><th>e-mail</th><th>Date</th><th>Approuver</th></tr>' ;
	  
	$reponse = mysql_query("SELECT * FROM $table_conf_inscript WHERE confirm='ok'");
	while ($donnees = mysql_fetch_array($reponse))
	{
		echo '<tr>
				<td>' . $donnees['prenom'] . ' ' . $donnees['nom'] . ' - (ID:' . $donnees['id'] . ')</td>
				<td><b>' . $donnees['societe'] . '</b></td>
				<td><a href="mailto:' . $donnees['e_mail'] . '">' . $donnees['e_mail'] . '</a></td>
				<td><i>' . date ('d-m-Y', $donnees['timestamp']) . '</i></td>
				<td align="center">
				
				<a href="c.php?id=' . $donnees['id'] . '&action=approuver">
				<img src="../design_pics/bouton_ok.gif" title="Activer le compte et poster les codes"></a>
				
				<a href="c.php?id=' . $donnees['id'] . '&action=effacer">
				<img src="../design_pics/bouton_delete.gif" title="Effacer cette demande d\'inscription"></a>
				
				</td>
			  </tr>' ;
	}
	echo '</table>' ;
}

//--- mysql_close($db2dlp);

?>
</p> 
</body>
</html>
