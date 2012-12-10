<?php 

/*require '../inc_db_connect.php'; /// 8888
require '../inc_fct_base.php'; /// 8888*/

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction envoi un e-mail d'info au Spectateur pour dire qu'il a reçu 1 point
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function avis_info_1_point_send_mail ($id_avis, $id_spectateur)
{
	require '../inc_var.php';
	require '../inc_var_dist_local.php';


	$reponse_qui_spectateur = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
	$donnees_qui_spectateur = mysql_fetch_array($reponse_qui_spectateur);
	//$prenom_spectateur = $donnees_qui_spectateur ['prenom_spectateur'];
	//$nom_spectateur = $donnees_qui_spectateur ['nom_spectateur'];
	$pseudo_spectateur = $donnees_qui_spectateur ['pseudo_spectateur'];

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
	// Nombre d'avis déposés par ce spectateur :
	$retour_nb_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur'");
	$donnees_nb_avis = mysql_fetch_array($retour_nb_avis);
	$_tot_entrees_avis = $donnees_nb_avis['nbre_entrees'];


	// Nombre d'avis APPROUVES pour ce spectateur :
	$avis_valides_spectateur = $donnees_qui_spectateur['avis_valides_spectateur'];

	$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; 
	
	
	// RECUPERATION DES DONNEES SUR L'EVENEMENT :
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
	
	$lien_event = $racine_domaine . '-Detail-agenda-?id_event=' . $event_avis ;

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
	
	$date_avis = date('d/m/Y', $donnees_avis ['t_stamp_avis']) ;
	$mail_concat.= '<tr><td colspan="2" >
	<p><strong>Bravo ' . $pseudo_spectateur . ' ! </strong></p>
	<p>Nous avons validé l\'avis que vous avez déposé sur le site 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> 
	pour l\'événement &quot;<a href="' . $lien_event . '">' . $nom_event . '</a>&quot; (' . $nom_lieu . ')
	le ' . $date_avis . '.<br /> <br />
	
	Vous avez déposé un total de <strong>' . $_tot_entrees_avis . '</strong> avis sur le site.<br />
	<strong>' . $avis_valides_spectateur . '</strong> avis a (ont) été approuvé(s) cette saison, 
	ce qui porte votre coefficient concours à <strong>' . $result_fact_chance['valeur_facteur_chance'] . '</strong>, 
	multipliant ainsi d\'autant vos chances de gain aux concours.<br /> <br />
	
	Pour tout savoir sur le fonctionnement des avis et concours, vous pouvez 
	<a href="http://www.demandezleprogramme.be/Pour-les-spectateurs-membre">
	cliquer ici</a>.<br /> <br />
	
	<p>A très bientôt ! </p>
	<p><em>L\'&eacute;quipe de Demandezle programme.</em> </p> <br />
	<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br />
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Vertige asbl <br />
	Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et
	Visitez &eacute;galement <a href="http://www.vertige.org">www.vertige.org</a> </p>
	
	</td></tr></table>
	</body> </html>' ;
	// FIN CONCATENATION
	
	
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = ' : ) ' . html_entity_decode($pseudo_spectateur, ENT_QUOTES) . ', votre avis sur ' . html_entity_decode($nom_event, ENT_QUOTES) . ' !' ;

	$test_mail = mail_beta($email_avis,$sujet,$mail_concat,$entete,$email_retour_erreur);
	if ($test_mail)
	{
		$resultat_fct = '<br /><div class="info">Le message destiné à informer le Spectateur est bien envoyéa bien été envoyé à : ' . $email_avis . '</div>' ; 
		
		// Update de la DB : Marquer le FLAG afin de ne plus envoyer à nouveau le message 
		/*$flags_avis_modif = $flags_avis . ',refus'; // "refus" n'y est pas car testé avant appel de cette fonction 
		$sql_check = mysql_query("UPDATE $table_avis_agenda SET flags_avis = '$flags_avis_modif' 
		WHERE id_avis = '$id_avis' LIMIT 1 ") ;
		if (!$sql_check) { echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Erreur d\enregistrement dans la DB</div>' ; }*/
	}
	else
	{
		$resultat_fct = '<br /><div class="alerte">Erreur lors de l\'envoi du message destiné à informer le Spectateur (' . $email_avis . ')</div>' ; 
	}
	
	echo $mail_concat .'<br>' ;
	echo $resultat_fct ;
}

//avis_info_1_point_send_mail (101, '14') ; // 88888888888888888 Appel de la fonction pour faire les tests 

?>
