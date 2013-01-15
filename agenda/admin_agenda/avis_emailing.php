<?php 

//require '../inc_db_connect.php'; /// 8888

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction pour envoyer par e-mail le nouvel avis à la liste des abonnés 
// L'ID du nouvel avis est transmis à la fonction via $id_avis
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function avertir_avis_listing ($id_avis)
{
	require '../inc_var.php';
	require '../inc_var_dist_local.php';

	// L'avis existe-t-il ?
	$rep_test_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_avis'") 
	or die(mysql_error());
	$test_avis = mysql_fetch_array($rep_test_avis);	
	if (empty($test_avis))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>' ;
		exit();
	}
	$event_avis = $test_avis['event_avis']; // = référence à l'événement concerné
	
	// RECUPERATION DES DONNEES :

	// Données sur l'événement
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
	$donnees_event = mysql_fetch_array($reponse) ;	
	
	$lieu_event = $donnees_event ['lieu_event'] ;
	$nom_event = $donnees_event ['nom_event'] ;
	$id_event = $donnees_event ['id_event'] ; 
	
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
		
		
	$mail_concat.= '<td class="email_style_petit" align="center">Vous recevez cet e-mail car vous avez 
	demand&eacute; d\'&ecirc;tre inform&eacute; 
	de l\'arriv&eacute;e des  r&eacute;ponses &agrave; l\'avis que vous avez publi&eacute; 
	sur le site de 	<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a>. <br />
	Pour vous d&eacute;sabonner, _de_inscript_avis_mailing_ </td></tr>';
	

	$mail_concat.= '<tr><td align="center" colspan="2">
	<table width="350" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#EEEEEE">
  <tr>
    <td >' ;
	
	$lien_event = $racine_domaine . '-Detail-agenda-?id_event=' . $id_event ;
	// Vignette	
	if (isset ($donnees_event['pic_event_1']) AND $donnees_event['pic_event_1'] == 'set' )
	{
		$nom_event = $donnees_event['nom_event'] ;
		$id_event = $donnees_event['id_event'] ;
		$mail_concat.= '<a href="' . $lien_event . '">
		<img src="' . $racine_domaine . 'agenda/' . $folder_pics_event . '/event_' . $id_event . '_1.jpg" title="' . $nom_event . '" width="100" /></a>';
	}	
		
	$mail_concat.= '</td><td><p class="turquoise_style"><strong>' . $nom_avis . '</strong> a post&eacute; un nouvel avis sur 
	l\'&eacute;v&eacute;nement : <br /><strong><a href="' . $lien_event . '">' . $nom_event . '</a></strong> (' . $nom_lieu . ')</p></td></tr>
	</td>
	  </tr>
	</table> 

	<tr><td colspan="2"><p class="turquoise_style"><strong>Voici son message : </strong></p>
	<p align="justify">' . stripslashes($texte_avis) . 
	'</p>
	
	<div align="center" class="turquoise_style"><strong>
	Vous souhaitez <a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '">r&eacute;agir&nbsp;</a> &agrave; ce message ?<br />
	Retrouvez les autres messages <a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '">ici !</a>
	</strong></div>
	
	<br /><br /><p>Bien &agrave; vous, </p>
	<p>L\'&eacute;quipe de <a href="http://www.demandezleprogramme.be/">demandezleprogramme</a></p>
	<p><strong>Demandez le programme&nbsp;!</strong><br />
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be<br />
	</a><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a></p><br />
	<p class="email_style_petit" align="center">
	Vertige asbl - 163, rue de la Victoire 1060 Bruxelles - Tel/fax&nbsp;: 02/544 00 34</p></tr></td>' ;
	
	$mail_concat.= '</body> </html>' ;
	// FIN CONCATENATION

	
	// récupération des adresses pour envoi des e-mails
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;

	$sujet = ':: ' . html_entity_decode($nom_event, ENT_QUOTES) . ' - Lisez l\'avis de ' . html_entity_decode($nom_avis) . ' !' ;
	$resultat_fct = '<br /><b>Message envoyé à :</b><br /> <br />' ;

	$reponse_mailing = mysql_query("SELECT * FROM $table_avis_mailing WHERE event_avis_mailing = '$event_avis' AND numero_avis != '$id_avis'")
	or die(" --envoi-- " . mysql_error());
	while ($donnees_mailing = mysql_fetch_array($reponse_mailing))
	{
		$avis_mailing_adresse = $donnees_mailing['avis_mailing_adresse'] ;
		$ref_avis_mailing = $donnees_mailing['ref_avis_mailing'] ;

		$lien_desinscription = '<a href="' . $racine_domaine . 'agenda/avis/desinscr.php?ad='
		. $avis_mailing_adresse . '&amp;ref=' . $ref_avis_mailing . '">cliquez ici</a>' ;//  Lien de désinscription
		$message = str_replace("_de_inscript_avis_mailing_","$lien_desinscription",$mail_concat); // Lien de désinscription

		 $test_mail = mail($avis_mailing_adresse,$sujet,$message,$entete,$email_retour_erreur);
		if($test_mail)
		{ $resultat_fct.= $avis_mailing_adresse . '<br />' ; }
	}
	//echo $mail_concat .'<br>' ;
	echo 'Résultat de l\'action : '.$resultat_fct ;
	
	return (1) ;
}
// Fin fonction
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

 //avertir_avis_listing (11) ; // Appel de la fonction pour faire les tests (cfr TABLE 'ag_avis')

?>
