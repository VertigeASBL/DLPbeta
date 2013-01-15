<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Confirmation de votre inscription (b)</title>
<link href="../../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1 align="center">Confirmation par utilisateur de son inscription (b)</h1>

<?php 
require '../../inc_var.php';
require '../../inc_var_dist_local.php';
require 'inc_var_inscription.php';
require '../../inc_fct_base.php';

//ajouté aux messages d'erreurs de type : pas d'ID, pas de PW, mauvais PW
$message_erreur = 'Veuillez <a href="a.php">recommencer l\'op&eacute;ration</a> 
ou prendres contact avec notre administrateur : ' .$retour_email_admin . '<br>' ;
$adr_retour = '<a href="../../../-Demandez-le-programme-">&gt; &gt; Retour au site &lt; &lt; </a>';

if (empty ($_GET['s_id']) OR $_GET['s_id'] == NULL OR empty ($_GET['s_pw']) OR $_GET['s_pw'] == NULL)
{
	echo '<p class="alerte">Erreur [GET]<br>'.$message_erreur . '</p>' ;
	exit() ; 
}


require '../../inc_db_connect.php';
$s_id = htmlentities($_GET['s_id'], ENT_QUOTES);
$s_pw = htmlentities($_GET['s_pw'], ENT_QUOTES);

$reponse = mysql_query("SELECT * FROM $table_conf_inscript WHERE session_crypt = '$s_id'");
$donnees = mysql_fetch_array($reponse);

if ($donnees ['session_pw'] != $s_pw) // Erreur PW
{
	echo '<p class="alerte">Erreur PW <br>'.$message_erreur . '</p>';
	exit() ; 
}
else
{
	// Avertir le nouvel inscrit par e-mail de la procédure de paiement :
	$e_mail = $donnees ['e_mail'] ;
	
	$sujet='- - Modalités de paiement de votre cotisation à Demandezleprogramme.be';
	
	$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<style type="text/css">
	<!--
	' . $css_email . '
	-->
	</style></head><body>' ;
	
	$corps.= '<p>&nbsp;</p>
	<p><b> - - A l\'attention de ' . $donnees ['prenom'] . ' ' . $donnees ['nom'] . ' - - </b></p> <br />
	<p>Bonjour, </p> <br />
			
	<p>Nous avons bien enregistr&eacute; votre inscription sur le site 
	<a href="http://www.demandezleprogramme.be/">demandezleprogramme.be</a>.</p>
	
	<p>Vous pourrez encoder vos spectacles dans l&rsquo;agenda d&egrave;s que nous aurons valid&eacute; votre inscription. 
	Vous recevrez alors des mots de passe qui vous donneront acc&egrave;s &agrave; votre compte personnel.
	<br />Les spectacles ne s\'afficheront dans l\'agenda qu\'&agrave; partir du moment o&ugrave; 
	votre cotisation aura &eacute;t&eacute; vers&eacute;e.</p>
	
	<p>Celle-ci est &agrave; virer sur le compte de demandezleprogramme : <b>001-5030933-07</b>. <br />
	Pour les virements de <b>l\'&eacute;tranger sans frais</b>, vous devez mentionner les infos suivantes : 
	IBAN BE74 0015-0309-3307 et fortis bank BIC GEBABEBB. </p>
	
	<p>Merci de pr&eacute;ciser <b>en communication : &laquo; DLP : ' . $donnees ['societe'] . ' &raquo;</b></p>
	
	<p><br /><b>Tarif des cotisations : </b></p>
	
	<p> <b>* 242&euro; TTC/an *</b><br /> 
	- Salles de grande envergure, concert/show<br />
	- Th&eacute;&acirc;tres et lieux culturels subventionn&eacute;s par un contrat-programme<br />
	- Grands festivals<br />
	- Institutions culturelles subventionn&eacute;es (expos, &eacute;v&eacute;nements...)</p>
	
	<p> <b>* 121&euro; TTC/an *</b><br />
	- Autre lieux culturels<br />
	- Caf&eacute;-th&eacute;&acirc;tres<br /></p>
	
	<p>Apr&egrave;s payement, votre lieu appara&icirc;tra &eacute;galement dans la liste des lieux partenaires.

	<p>&nbsp;</p>
	<p>Nous vous remercions de la confiance que vous nous accordez.</p>
	<p><i>L\'&eacute;quipe de <a href="http://www.demandezleprogramme.be/">demandezleprogramme</a></i></p>
	<p class="email_style_petit">02/5440034<br />
	info@demandezleprogramme.be<br />
	www.demandezleprogramme.be<br />
	Une initiative de l\'ASBL Vertige: www.vertige.org</p>	

	<p>&nbsp;</p>
	</body></html>
	</html>'; 

	$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
	
	//echo $corps ;
 mail($e_mail,$sujet,$corps,$entete,$email_retour_erreur);

	
	
	// Vérifier si l'inscription a déja été confirmée. 
	// Si oui, l'afficher et ne pas avertir une nouvelle fois le Web master par e-mail
	if ($donnees ['confirm'] == 'ok' OR $donnees ['confirm'] == 'fin') 
	{
		echo '<br><br><br><br><br><br>
		<div class="info"><p>Vous avez déjà confirmé votre inscription. <br />
		Un e-mail reprenant les modalités de paiement vient de vous être envoyé</p></div>';
	}
	else
	{
		mysql_query("UPDATE $table_conf_inscript SET confirm = 'ok' WHERE session_crypt ='" . $s_id . "' LIMIT 1 ");
		
		echo '<br><br><div class="info"><p><b>Bonjour ' ;
		echo $donnees ['prenom'] . ' '. $donnees ['nom'] . "</b>,</p>";
		echo '<p>Votre demande d\'inscription est maintenant confirm&eacute;e. <br />
		Un e-mail reprenant les modalités de paiement de la cotisation vous est envoyé. <br /><br />
		Dès que l\'administrateur du site aura validé votre inscription, vous recevrez des mots de passe 
		pour encoder vos  événements.
		<br><br>Bien à vous, <br><br><br><i> L\'équipe de Demandezleprogramme</i><br /><br /><br />'
		. $adr_retour . '</div>';
		
		// Avertir le WebMaster qu'une indcription est confirmée en lui envoyant un e-mail
		// Construction de l'e-mail
		$sujet='_ Nouvelle inscription à l\'agenda : '. html_entity_decode($donnees ['societe'], ENT_QUOTES) . ' -';
		
		$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
		<!--
		' . $css_email . '
		-->
		</style></head><body>
		<p>&nbsp;</p><p>&nbsp;</p>
		<h1>Une inscription à l\'agenda a &eacute;t&eacute; confirm&eacute;e</h1>
		<p>&nbsp;</p>';
		
		$corps.='<p><b>Nom</b> : ' . $donnees ['nom'] . ' <br />';
		$corps.='<b>Pr&eacute;nom</b> : ' . $donnees ['prenom'] . ' </p>';
		$corps.='<p><b>D&eacute;nomination du lieu culturel</b> : ' . $donnees ['societe'] . ' <br />';
		$corps.='<b>Adresse</b> : ' . $donnees ['adresse'] . ' <br />';
		$corps.='<b>e-mail</b> : ' . $donnees ['e_mail'] . ' <br />';
		$corps.='<b>T&eacute;l&eacute;phone</b> : ' . $donnees ['tel_1'] . ' </p>';
		
		if (isset($donnees ['n_tva']) AND $donnees ['n_tva'] != NULL )
		{$corps.= '<p><b>Num&eacute;ro de TVA</b> : ' . $donnees ['n_tva'];}

		if (isset($donnees ['web_site']) AND $donnees ['web_site'] != NULL )
		{$corps.= '<p><b>Site Web</b> : ' . $donnees ['web_site'];}
		
		$corps.='<p><b>Date d\'inscription</b> : ' .  date('d/m/Y - h\hi', $donnees ['timestamp']) . '<br />';
		$corps.='<b>IP</b> =: ' . $donnees ['ip'] . '<br />';
		$corps.='<b>R&eacute;f&eacute;rence</b> : ' . $s_id . '<br />';
		$corps.='<b>ID</b> : ' . $donnees ['id'] . '</p>';
		$corps.='<p>&nbsp;</p>
		<p align="center"><a href="'.$racine_domaine.'agenda/admin_agenda/c.php"> 
		&gt; &gt; Activer le compte &lt; &lt; </a></p>
		<p>&nbsp;</p> 
		</body></html>
		</html>'; 

		$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
		
		//echo $corps ;
	 mail($email_admin_site,$sujet,$corps,$entete,$email_retour_erreur);
	}
	//--- mysql_close($db2dlp);
}

?>

<p>&nbsp;</p>
</body>
</html>
