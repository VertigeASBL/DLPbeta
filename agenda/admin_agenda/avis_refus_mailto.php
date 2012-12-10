<?php 

//require '../inc_db_connect.php'; /// 8888

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction pour envoyer par e-mail le nouvel avis à la liste des abonnés 
// L'ID du nouvel avis est transmis à la fonction via $id_avis
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function avis_refus_send_mail ($id_avis)
{
	require '../inc_var.php';
	require '../inc_var_dist_local.php';

	// L'avis existe-t-il ?
	$rep_test_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_avis'") 
	or die($rep_test_avis . " ---- " . mysql_error());
	$test_avis = mysql_fetch_array($rep_test_avis);	
	if (empty($test_avis))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>' ;
		exit();
	}
	$event_avis = $test_avis['event_avis'];
	
	
	// RECUPERATION DES DONNEES :
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
	$donnees_event = mysql_fetch_array($reponse) ;	
	
	$lieu_event = $donnees_event ['lieu_event'] ;
	$nom_event = $donnees_event ['nom_event'] ;
	
	// Recherche nom du lieu culturel
	$reponse_lieu = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = '$lieu_event'");
	$donnees_lieu = mysql_fetch_array($reponse_lieu);	
	$nom_lieu = $donnees_lieu ['nom_lieu'] ;
	
	
	// -------------------------------------------------------------
	// Données sur le nouvel AVIS
	$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = $id_avis ");
	$donnees_avis = mysql_fetch_array($reponse_avis) ;
	
	$nom_avis = $donnees_avis['nom_avis'] ;
	$texte_avis = $donnees_avis['texte_avis'] ;
	$event_avis = $donnees_avis['event_avis'] ;
	$ip_avis = $donnees_avis['ip_avis'] ;
	$flags_avis = $donnees_avis['flags_avis'] ;
	$email_avis = $donnees_avis['email_avis'] ;
	
	$lien_event = $racine_domaine . 'spip/-Detail-agenda-?id_event=' . $event_avis ;

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
	car vous avez envoy&eacute; un message sur le forum du site de 
	<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a>.</td></tr>';
	
	$mail_concat.= '<tr><td colspan="2" >
	<p>Bonjour <b>' . $nom_avis . '</b> (IP '.$ip_avis.'), </p> <br />
	<p>Votre avis concernant <strong>&quot;<a href="' . $lien_event . '">' . $nom_event . '</a>&quot;</strong> (' . $nom_lieu . ')
	a &eacute;t&eacute; retir&eacute; du site 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a>.<br /> <br />
	L\'adminsitrateur du site a jug&eacute; que cet avis (commentaire) ne respectait pas les 
	r&egrave;gles d&eacute;taill&eacute;es dans les <a href="http://www.comedien.be/spip/Mentions-legales">Conditions 
	g&eacute;n&eacute;rales d\'utilisation</a>.  A titre d\'exemple, il se peut que votre message contenait des contenus 
	impropres ou incomplets ou ait &eacute;t&eacute; formul&eacute; dans un langage inappropri&eacute; (abr&eacute;viations, 
	langage &quot;sms&quot; ou incompr&eacute;hensible, etc.).</p>
	<p>Vous pouvez bien entendu reformuler votre message en respectant les r&egrave;gles d\'utilisation.</p>
	<p>Nous vous remercions d\'avance pour votre compr&eacute;hension.</p> <br />
	<p>L\'&eacute;quipe de Demandezle programme. </p> 
	<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br />
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Vertige asbl <br />
	Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et
	Visitez &eacute;galement <a href="http://www.vertige.org">www.vertige.org</a> </p>
	
	</td></tr></table>
	</body> </html>' ;
	// FIN CONCATENATION
	
	
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = ' ! ' . html_entity_decode($nom_avis, ENT_QUOTES) . ', votre avis sur ' . html_entity_decode($nom_event, ENT_QUOTES) . ' !' ;

	$test_mail = mail_beta($email_avis,$sujet,$mail_concat,$entete,$email_retour_erreur);
	if ($test_mail)
	{
		$resultat_fct = '<br /><div class="alerte">Le message a bien été envoyé à : ' . $email_avis . '</div>' ; 
		
		// Update de la DB : Marquer le FLAG afin de ne plus envoyer à nouveau le message 
		$flags_avis_modif = $flags_avis . ',refus'; // "refus" n'y est pas car testé avant appel de cette fonction 
		$sql_check = mysql_query("UPDATE $table_avis_agenda SET flags_avis = '$flags_avis_modif' 
		WHERE id_avis = '$id_avis' LIMIT 1 ") ;
		if (!$sql_check) { echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Erreur d\enregistrement dans la DB</div>' ; }
	}
	
	echo $mail_concat .'<br>' ;
	echo $resultat_fct ;
}

 //avis_refus_send_mail (21) ; // 88888888888888888 Appel de la fonction pour faire les tests 

?>
