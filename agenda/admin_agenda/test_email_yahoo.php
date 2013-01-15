

<?php

require '../inc_var.php';
require '../inc_var_dist_local.php';
require '../inc_fct_base.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../calendrier/inc_calendrier.php';



		$mail_concat = '';
		$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml"> <head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<style type="text/css"> <!-- ' . $css_email . '--> </style> </head> <body> ';
		$mail_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
		
		// Logo 
		$mail_concat.= '<a href="http://www.demandezleprogramme.be">
		<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';
		

		
		$mail_concat.= '<tr><td colspan="2" >
		
		Nous testons notre fonction d\'email<br />
		Merci de nous retransmettre ce message dès que vous le recevez et de nous indiquer s\'il était classé dans vos indésirables';
		

		
		$mail_concat.= '<br /> <p class="email_style_petit"><br />
		<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
		<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
		Vertige asbl <br />
		Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
		<a href="http://www.vertige.org">www.vertige.org</a>
		
		<br /><em> Date de l\'envoi : ' . date('d/m/Y - H\hi') ;
		$mail_concat.= '</td></tr></table>' ;
		$mail_concat.= '</body></html>' ;

		$retour_email_moderateur = 'renaud.jeanlouis@gmail.com' ;
		$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
		$sujet = 'IMORTANT : test email envoyé par Demandezleprogramme' ;

//		$email_reservation = 'productions_strategiques@yahoo.fr' ;
//		$email_reservation = 'philippe@vertige.org' ;
//		$email_reservation = 'reservations@arriere-scene.be, ' ;
 $email_reservation = 'info@strategique.be';
//$email_reservation = 'info@atelier210.be';
//	 mail($email_reservation,$sujet,$mail_concat,$entete, '-f info@demandezleprogramme.be');

$email_retour_erreur = 'renaud.jeanlouis@belgacom.net' ;
		$ret = mail($email_reservation,$sujet,$mail_concat,$entete, $email_retour_erreur);
		echo $ret ? 'OK !' : 'KO','<hr />',$mail_concat ;
?>
