<?php

define("STYLES_MAIL_CONC_PUBLIC", '
body { background-color: #FFFFFF; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;} 
a { color: #009aa8; } 
.email_style_titre { font-size: 18px; color:#009A99; font-weight: bold; }
.email_style_rubriques { font-size: 12px; color:#AA0033; font-weight: bold; }
.email_style_petit { font-size: 11px; color: #666666; } 
'); // CSS pour e-mail public


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Informer le visiteur par E-mail
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function informer_joueur ($id_liloolou, $nom_liloolou, $mail_liloolou, $time_liloolou, $est_gagnant, $liloolou_contacter)
{
	global $css_email ;
	global $racine_domaine ;
	global $nom_event_pour_mail_joueur ;
	global $descriptif_lot_pour_mail_joueur ;
	global $adresse_event_pour_mail_joueur ;
	global $retour_email_moderateur, $email_retour_erreur;

	$mail_joueur_concat = '';
	$mail_joueur_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css"> <!-- '
	. STYLES_MAIL_CONC_PUBLIC . 
	'--> </style> </head> <body> ';
	
	$mail_joueur_concat.= '<table width="797" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td><a href="http://www.demandezleprogramme.be" target="_blank"><img src="http://www.demandezleprogramme.be/agenda/concours/conc_head.jpg" alt="Demandez le programme" width="797" height="168" border="0" /></a></td>
  </tr>
  <tr>
    <td height="239"><table width="100%" border="0" cellspacing="4" cellpadding="0">
      <tr>
        <td width="275" valign="top"><div class="email_style_petit">(<em>Réf:';
	($est_gagnant) ? ($mail_joueur_concat.= 'G - ') : ($mail_joueur_concat.= 'P - ') ;
	$mail_joueur_concat.= $id_liloolou . '</em>)<br /> <br />
		
		<img src="http://www.demandezleprogramme.be/agenda/concours/';
	($est_gagnant) ? ($mail_joueur_concat.= 'conc_gagne') : ($mail_joueur_concat.= 'conc_perd') ;
	$mail_joueur_concat.= '.jpg"/></td>
        <td valign="top">';
		
	$mail_joueur_concat.= '<p class="email_style_petit" align="center">Vous recevez cet e-mail car vous avez participé à un concours sur le site de <a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a>. <br /> <br /></p>';
	
	$mail_joueur_concat.= '<p class="email_style_titre">Bonjour ' . $nom_liloolou . ', </p> 
	<p>Vous avez participé le ' .date('d/m/Y à H\hi', $time_liloolou) . ' à un concours sur le site de 
	<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a> afin de remporter le lot suivant :</p>
	<p align="center"><strong>' . $nom_event_pour_mail_joueur . '</strong></p>' ;
	
	// Choix message selon que gagné ou perdu
	if ($est_gagnant)
	{
		$mail_joueur_concat.= '<br /><p align="center"><strong>Nous sommes heureux de vous annoncer que <br />
		vous avez remporté ce concours !</strong></p>
		<br /> <br /> <p><strong>Renseignements utiles : </strong></p>
		<p>Nom de l\'événement : <b>' . $nom_event_pour_mail_joueur . '</b><br />
		Lot gagné : ' . $descriptif_lot_pour_mail_joueur . ' <br />
		Adresse : ' . $adresse_event_pour_mail_joueur . ' <br />
		Votre nom : ' . $nom_liloolou . ' <br />
		Votre adresse e-mail : ' . $mail_liloolou . ' <br /> <br />
		
		<p>Si le lot consiste en des places gratuites, <strong>ATTENTION</strong>, le règlement a été modifié : 
		La liste des gagnants est transmise au lieu culturel, <strong>mais vous êtes tenu de confirmer votre présence 
		par email ou par téléphone</strong> afin de bénéficier des places que vous avez gagnées. <br /> ' 
		. $liloolou_contacter . '<br /> <br />
		En aucun cas ces places ne peuvent être revendues ou monnayées à une tierce personne. 
		Toute personne qui enfreindrait cette  règle s\'expose à des poursuites. <br /> <br />
		
		<strong>Il est recommandé de contacter le lieu pour confirmer votre présence.</strong>		
		Afin d\'éviter tout malentendu, nous vous demandons également d\'imprimer cet email 
		pour le présenter à la billetterie. En cas de problème, de désistement, ou autre, (cela doit vraiment rester exceptionnel) 
		ne nous appelez pas, mais prenez directement contact avec le théâtre ou le centre culturel.</p>

		<p>A très bientôt pour d\'autres concours sur Demandez le programme !</p>
		<p><b>Important</b> : Pour soutenir le projet Demandez le programme, merci de bien vouloir laisser un avis 
		après avoir assisté à cet événement culturel. C\'est fondamental pour aider les autres spectateurs à faire un 
		choix et pour enrichir notre agenda. Il suffit de cliquer sur "donnez votre avis" en dessous de la description 
		de l\'événement. </p> <br />' ;		
	}
	else
	{
		$mail_joueur_concat.= '<br /><p>Malheureusement, vous ne faites pas partie des gagnants, cette fois. </p>
		<p>Nous vous remercions pour votre participation. N\'hésitez pas à retenter votre chance lors d\'un prochain concours sur 
		<a href="http://www.demandezleprogramme.be">demandezleprogramme.be</a> !</p>
		
		<p>N\'oubliez pas également de déposer votre avis sur le site afin de faire profiter les autres spectateurs 
		de vos expériences.</p><br /> <br />
		
		<p>A très bientôt !</p>' ;	
	}

	$mail_joueur_concat.= '<p>L\'&eacute;quipe de Demandezle programme. </p> 
	<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Vertige asbl <br />
	Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
	<a href="http://www.vertige.org">www.vertige.org</a> </p>

		</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><img src="http://www.comedien.be/newsletter/ligneBordeau.jpg" width="797" height="9" /></td>
  </tr>
</table>' ;

	$test_envoi_joueur = '' ;
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = '-Votre participation au concours de demandezleprogramme.be' ;
	
	$test_envoi_joueur = mail_beta($mail_liloolou.',charleshenry@comedien.be',$sujet,$mail_joueur_concat,$entete,$email_retour_erreur);
	echo $mail_joueur_concat ;
		
	return ($test_envoi_joueur) ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF





// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Informer le LIEU culturel
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function informer_lieu ()
{
	global $css_email ;
	global $racine_domaine ;
	global $retour_email_moderateur ;	
	global $liste_infos_gagnants ;
	global $id_conc ;
	global 	$nom_event_conc ;
	global 	$mail_lieu_conc ;
	global 	$adresse_conc, $email_retour_erreur ;

	$mail_pour_lieu_concat = '';
	$mail_pour_lieu_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css"> <!-- '
	. $css_email . 
	'--> </style> </head> <body> ';
	
	$mail_pour_lieu_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
	
	// Logo 
	$mail_pour_lieu_concat.= '<a href="http://www.demandezleprogramme.be">
	<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';
	
	$mail_pour_lieu_concat.= '<td class="email_style_petit">
	<div align="right"><em>Réf:Concours ' . $id_conc . '</em></div><br />
	<div align="center">&nbsp;</div></td></tr>';
	
	$mail_pour_lieu_concat.= '<tr><td colspan="2" ><p align="center" class="email_style_titre">
	IMPORTANT : Liste des gagnant du concours <br />"' . $nom_event_conc . '" 
	(réf.' . $id_conc . ') </p> <br /> 
	<p>Bonjour,</p> <br />
	<p>Voici comme convenu la liste des gagnants de notre concours sur le site Demandez le programme/Comedien.be. 
	Chaque nom correspond à une unité gagnée (2 places = une unité , s\'il s\'agit de places gratuites).
</p>' ;
	
	$mail_pour_lieu_concat.= $liste_infos_gagnants ;
	
	$mail_pour_lieu_concat.= ' <br /> <br /> 
	<p><b>S\'il s\'agit de places gratuites :</b> <br />
	Les gagnants ont été avertis que leurs places ont été réservées à leur nom. 
	Un email de confirmation à imprimer leur a été envoyé. </p>
	<p>Merci de bien vouloir nous confirmer par retour d\'email que vous avez bien pris note du nom des gagnants. </p>
	
	<p>Nous vous remercions encore pour ce partenariat. </p> <br />
	
	<p>L\'&eacute;quipe de Demandezle programme. </p> 
	<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Vertige asbl <br />
	Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
	
	<a href="http://www.vertige.org">www.vertige.org</a> </p>' ;
	
	$mail_pour_lieu_concat.= '</td></tr></table>' ;
	$mail_pour_lieu_concat.= '</body></html>' ;
	//echo $mail_pour_lieu_concat ;
	$test_envoi_lieu = '';
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = 'Liste des gagnants du concours organisé en partenariat avec demandezleprogramme.be' ;
	$test_envoi_lieu = mail_beta($mail_lieu_conc.',charleshenry@comedien.be',$sujet,$mail_pour_lieu_concat,$entete,$email_retour_erreur);
	
	echo $mail_pour_lieu_concat ;
	return ($test_envoi_lieu) ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF







// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Informer l'Administrateur
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function informer_admin ($rapport_complet_concat)
{
	global $css_email ;
	global $racine_domaine ;
	global $retour_email_moderateur ;
	global $email_admin_site, $email_retour_erreur;

	$mail_pour_admin_concat = '';
	$mail_pour_admin_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css"> <!-- '
	. $css_email . 
	'--> </style> </head> <body> ';
	
	$mail_pour_admin_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
	
	// Logo 
	$mail_pour_admin_concat.= '<a href="http://www.demandezleprogramme.be">
	<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';

	$mail_pour_admin_concat.= '<td align="center" class="email_style_petit"><p align="center" class="email_style_titre">
	Rapport du tirage au sort réalisé le ' . date('d/m/Y - H\hi') . '</p></td></tr>';

	$mail_pour_admin_concat.= '<tr><td colspan="2" >' ;
	
	$mail_pour_admin_concat.= $rapport_complet_concat ;

	$mail_pour_admin_concat.= ' <br /> <br /> 
	<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Vertige asbl <br />
	Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
	
	<a href="http://www.vertige.org">www.vertige.org</a> </p>' ;
	
	$mail_pour_admin_concat.= '</td></tr></table>' ;
	$mail_pour_admin_concat.= '</body></html>' ;
	
	$test_envoi_admin = '' ;
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = 'Résultats du concours de demandezleprogramme.be' ;
	$test_envoi_admin = mail_beta($email_admin_site.',charleshenry@comedien.be',$sujet,$mail_pour_admin_concat,$entete,$email_retour_erreur);

	echo $mail_pour_admin_concat ;

	return ($test_envoi_admin) ;	
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

?>
	