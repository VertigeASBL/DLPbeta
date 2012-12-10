<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<br /> <br /> 
<?php 

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Envoi d'un e-mail à un spectateur qui n'a pas fifn son inscription
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

require '../inc_db_connect.php'; 
require '../inc_fct_base.php';
require '../inc_var.php';
require '../inc_var_dist_local.php';



//---------------------------------------------------------
// Récupération ID via GET :
if (empty ($_GET['id_rappel']) OR $_GET['id_rappel'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET</div>' ;
	exit();
}
else
{
	$id_rappel = htmlentities($_GET['id_rappel'], ENT_QUOTES);
}


//---------------------------------------------------------
// Récupérations des données :

$reponse_qui_spectateur = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_rappel'");
$donnees_qui_spectateur = mysql_fetch_array($reponse_qui_spectateur);
//$prenom_spectateur = $donnees_qui_spectateur ['prenom_spectateur'];
//$nom_spectateur = $donnees_qui_spectateur ['nom_spectateur'];
$e_mail_spectateur = $donnees_qui_spectateur ['e_mail_spectateur'];
$log_spectateur = $donnees_qui_spectateur ['log_spectateur'];
$pw_spectateur = $donnees_qui_spectateur ['pw_spectateur'];



// Corps de l'e-mail :
$mail_concat = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
<!--
' . $css_email . '
-->
</style>
</head>
<body>' ;


$mail_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;

// Logo 
$mail_concat.= '<a href="http://www.demandezleprogramme.be">
<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';


$mail_concat.= '<td class="email_style_petit" align="center">Vous recevez cet e-mail 
car vous vous êtes inscrit sur le site de 
<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a>.</td></tr>';

$mail_concat.= '<tr><td colspan="2" >
<p>Cher (ère) membre spectateur (trice),</p><br />

<p>Nous avons bien enregistré votre demande d\'inscription à la Communauté des spectateurs. <br />
Votre formulaire n\'a pas été rempli de façon complète, c\'est pourquoi nous vous proposons de le faire en cliquant sur
<strong> <a href="http://www.demandezleprogramme.be/agenda/spectateurs_admin/edit_profile_spectateur.php">ce lien</a></strong>. <br />
Pour vous connecter à votre compte, utilisez <br />
<strong> - votre login</strong> : ' . $log_spectateur . ' <br />
<strong> - votre mot de passe</strong> : ' . $pw_spectateur . '<br /></p>
<p>Les champs obligatoires doivent être tous complétés pour rendre l\'inscription active et pour que vos avis soient comptabilisés. Dès que vous aurez complété votre profil, celui-ci apparaîtra sur le site.</p>

<p>Pour tout savoir sur le fonctionnement des avis et concours, vous pouvez 
<a href="http://www.demandezleprogramme.be/Pour-les-spectateurs-membre">
cliquer ici</a>.</p> <br /> <br />

<p>Un grand merci ! </p>
<p><em>L\'&eacute;quipe de Demandezle programme.</em> </p> <br />
<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br />
<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
Vertige asbl <br />
Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et
Visitez &eacute;galement <a href="http://www.vertige.org">www.vertige.org</a> </p>

</td></tr></table>
</body> </html>' ;

$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
$sujet = '! Merci de terminer votre inscription' ;

$test_mail = mail_beta($e_mail_spectateur,$sujet,$mail_concat,$entete,$email_retour_erreur);
if ($test_mail)
{
	$resultat_envoi_rappel = '<br /><div class="info">Le message de rappel pour le Spectateur a bien été envoyé</div>' ; 
}
else
{
	$resultat_envoi_rappel = '<br /><div class="alerte">Erreur lors de l\'envoi du message destiné à informer le Spectateur</div>' ; 
}

echo $resultat_envoi_rappel  .'<br>';

echo $mail_concat .'<br>' ;

?>
