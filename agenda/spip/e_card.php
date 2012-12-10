<?php

require 'agenda/inc_var.php';
require 'agenda/inc_var_dist_local.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';


// Si la valeur de $_GET['id_event'] ne correspond à aucune entrée de la TABLE :
if (!isset($_GET['id_event']) OR !preg_match('/[0-9]$/', $_GET['id_event']))
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>
	<a href="index.php" >Retour</a></div>' ;
	
	exit() ;
}

// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
// Préparation image anti-robots
$recevoir_publication = '' ;
$session = md5(time()); // numero d'identification du visieur
$ip_destinateur = $_SERVER['REMOTE_ADDR'] ;
$time_stamp_ecard = time();
// code aléatoire pour l'image generee :
$nb_car = 3 ;
$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
$txt = str_shuffle($txt);
$code = substr($txt, 10, $nb_car);
mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) 
VALUES ('$session','$code','$time_stamp_ecard','$ip_destinateur')");
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS

$form_masquage = false ;

$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
$donnees = mysql_fetch_array($reponse);


// ------------------------------------------------
// Lecture des infos de la DB pour cette entrée
// ------------------------------------------------

$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
$donnees = mysql_fetch_array($reponse);	

$lieu_event = $donnees ['lieu_event'];
$nom_event = $donnees ['nom_event'];
$ville_event = $donnees ['ville_event'];
$description_event = $donnees ['description_event'];
$genre_event = $donnees ['genre_event'];
$pic_event_1 = $donnees ['pic_event_1'];
$pic_event_2 = $donnees ['pic_event_2'];
$pic_event_3 = $donnees ['pic_event_3'];
$critique_event = $donnees ['critique_event'];
$interview_event = $donnees ['interview_event'];

$date_event_debut = $donnees ['date_event_debut'];
$date_event_fin = $donnees ['date_event_fin'];

/*$AAAA_debut = substr($date_event_debut, 0, 4);
$AAAA_fin = substr($date_event_fin, 0, 4);
$MM_debut = substr($date_event_debut, 5, 2);	
$MM_fin = substr($date_event_fin, 5, 2);
$JJ_debut = substr($date_event_debut, 8, 2);
$JJ_fin = substr($date_event_fin, 8, 2);
$AAAA_MM_debut = substr($date_event_debut, 0, 7);
*/

$date_event_debut_annee = substr($date_event_debut, 0, 4);
$date_event_debut_mois = substr($date_event_debut, 5, 2);
$date_event_debut_jour = substr($date_event_debut, 8, 2);

$date_event_fin_annee = substr($date_event_fin, 0, 4);
$date_event_fin_mois = substr($date_event_fin, 5, 2);
$date_event_fin_jour = substr($date_event_fin, 8, 2);


$jours_actifs_event = $donnees ['jours_actifs_event'];
$jours_actifs_event = explode(",", $jours_actifs_event);

$saison_preced_event = $donnees ['saison_preced_event'] ;


// Données sur le LIEU culturel
$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $lieu_event");
$donnees_lieu = mysql_fetch_array($reponse_lieu) ;

$nom_lieu = $donnees_lieu['nom_lieu'] ;

// Y a-t-il des avis ?
$avis_concat ='<a name="avis" id="avis"></a>' ;// compter le nbre d'entrées :
$count_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE 
(event_avis = $id_event OR event_avis = $saison_preced_event)
AND publier_avis = 'set'");
$nbr_avis = mysql_fetch_array($count_avis);
$total_entrees = $nbr_avis['nbre_entrees'];


// compter le nombre de caractères dans le cas où il n'y a que 1 seul avis (à faire avant de lancer les conditions d'après !)
if ($total_entrees >= 1)
{
	//echo '<a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '">Ce qu\'en disent les spectateurs ' . $total_entrees . ' ('. $total_entrees . ' avis)</a><br />';
}



// ******************************************************************************
// ******************************************************************************
// Si bouton enfoncé, alors lancer l'analyse des données
// ******************************************************************************
// ******************************************************************************

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Envoyer')) 
{
	$rec = ''; 

	// ------------------------------------------------------------
	// TEST NOM DESTINATAIRE
	if (isset($_POST['nom_destinataire']) AND ($_POST['nom_destinataire'] != NULL)) 
	{
		$nom_destinataire = stripslashes(htmlentities($_POST['nom_destinataire'], ENT_QUOTES));
	}
	else
	{
		$rec .= '- Vous devez introduire le nom du destinataire <br>';
		$error_nom_destinataire = '<div class="error_form">Vous devez introduire le nom du destinataire</div>';
	}


	// ------------------------------------------------------------
	//  TEST EMAIL DESTINATAIRE
		if ((isset($_POST['email_destinataire']) 
	AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_destinataire']))))
	{
		$email_destinataire = $_POST['email_destinataire'];
	}
	else
	{
		$email_destinataire = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide pour le destinataire<br>';
		$error_email_destinataire_event = '<div class="error_form">Vous devez introduire une adresse e-mail valide pour le destinataire</div>';
	}

// ------------------------------------------------------------
	// TEST NOM DESTINATEUR
	if (isset($_POST['nom_destinateur']) AND ($_POST['nom_destinateur'] != NULL)) 
	{
		$nom_destinateur = stripslashes(htmlentities($_POST['nom_destinateur'], ENT_QUOTES));
	}
	else
	{
		$rec .= '- Vous devez introduire le nom du destinataire <br>';
		$error_nom_destinateur = '<div class="error_form">Vous devez introduire le nom du destinataire</div>';
	}


	// ------------------------------------------------------------
	//  TEST EMAIL DESTINATEUR
	if ((isset($_POST['email_destinateur']) 
	AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_destinateur']))))
	{
		$email_destinateur = $_POST['email_destinateur'];
	}
	else
	{
		$email_destinateur = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide pour le destinataire<br>';
		$error_email_destinateur_event = '<div class="error_form">Vous devez introduire une adresse e-mail valide pour le destinataire</div>';
	}



	// -----------------------------------------
	// TEST TEXTE MESSAGE PERSO 
	if (!empty($_POST['champ_texte_perso']) AND ($_POST['champ_texte_perso'] != NULL)) 
	{
		$texte_message_perso = nl2br($_POST['champ_texte_perso']) ;

		$longueur_max_message = 600 ;
		$allowedTags_message_perso = '<br><br /><p>';

		if (strlen($_POST['champ_texte_perso'])>=$longueur_max_message)
		{
			//$texte_message_perso = str_replace("</p>","<br />",$_POST['champ_texte_perso']); 
			$texte_message_perso = stripslashes(strip_tags($texte_message_perso,$allowedTags_message_perso));
			$texte_message_perso = wordwrap($texte_message_perso, 50, " ", 1);
			
			$char_en_trop = strlen($texte_message_perso) - $longueur_max_message ; // Tester longueur de la chaîne de caractères
			$error_texte_message_perso = '<div class="error_form">
			La taille du texte dépasse la limite autorisée. Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir</div>';
			$rec .= '- taille  texte trop grande';
		}
		else
		{
			//$texte_message_perso = str_replace("</p>","<br />",$_POST['champ_texte_perso']); 
			$texte_message_perso = stripslashes(strip_tags($texte_message_perso,$allowedTags_message_perso));
			$texte_message_perso = wordwrap($texte_message_perso, 50, " ", 1);
			//$texte_message_perso_2_db = addslashes($texte_message_perso) ;
		}
	}

	// ------------------------------------------------------------
	// Test du code recopié à partir de l'image cryptée
	
	$get_sess = $_POST['sid'];
	$reponse_captcha = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	$donnees_captcha = mysql_fetch_array($reponse_captcha);
	//if(1==2)
	if ($donnees_captcha ['code_crypt']=="" OR $donnees_captcha ['code_crypt']!=$_POST['code']) // Code non valide
	{
		$code = '';
		$rec .= '- erreur image';
		$error_image_crypt = '<div class="error_form">Le code que vous avez recopié à partir 
		de l\'image est incorrect</div>';
	}
		else // Code valide
	{
		// Suppression de la DB
		$query = mysql_query("DELETE FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	}
	
	
	
	if ($rec == NULL)
	//if (1==1) ; 
	{
	
	$form_masquage = true ;
	$t_stamp_envoi = date('d/m/Y à H\hi') ;
	
	// ------------------------------------------------------------
	// RECEVOIR LETTRE D'INFO DE DLP (Philippe) si la case est cochée
	if (isset($_POST['recevoir_publication']) AND ($_POST['recevoir_publication'] == 'ok') AND $email_destinateur) 
	{
		//----- abonner à la mailing liste DLP tous
		$adrm = addslashes($email_destinateur);
		$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$adrm' AND lletr='DPts' AND letat='5'";
		$resp = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		if (! mysql_num_rows($resp)) {
			$sql = time();
			$sql = "INSERT INTO cmsnletter SET ladrm='$adrm',lletr='DPts',letat='5',lcode='$sql'";
			$resp = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		}
		unset($adrm, $sql, $resp);
	}

	
	
	// LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL
	// LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL
	// Enregistrement des logs dans le fichier text "e_card/rec_logs/logs_flyers.txt"
	// LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL

	$txt_pour_logs = $t_stamp_envoi . ' | ID=' . $id_event . ' de ' . $nom_destinateur . ' (' . $email_destinateur . ') à ' . $nom_destinataire . ' (' . $email_destinataire . ')' ; 

	/* http://www.commentcamarche.net/contents/php/phpfich.php3 */
	$fichier_logs_flyers = fopen('agenda/e_card/rec_logs/logs_flyers.txt', 'a+');
	
	fputs($fichier_logs_flyers, "\n"); // Introduire saut de ligne
	fputs($fichier_logs_flyers, $txt_pour_logs); // On écrit le nouveau nombre de pages vues
	
	fclose($fichier_logs_flyers);
	
	
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// Corps de l'e-mail envoyé au destinataire :
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	
	// Styles à insérer dans les balises
	$style_bonjour= ' font-family: Georgia, Times, serif; font-size: 14px; margin: 0px;
	color:#333333; font-weight: bold; margin-left: 30px; margin-right: 20px; ' ;
	$style_message_perso = ' font-family: Arial, Helvetica, sans-serif; color: #333333; 
	font-size: 12px; font-style: italic;  margin-left: 30px; margin-right: 30px; ' ;
	$style_titre_evenement = ' font-family: Georgia, Times, serif; font-size: 16px; margin: 0px;
	color:#C1094B; font-weight: bold; margin-left: 10px; margin-right: 20px; text-decoration: none; ' ;
	
	$style_texte_evenement = ' font-family: Arial, Helvetica, sans-serif; color: #333333; 
	font-size: 12px;  margin-left: 10px; margin-right: 20px; ' ;

	$style_bas = ' font-family: Arial, Helvetica, sans-serif; color: #666666; 
	font-size: 10px; text-align: center; ' ;



	$mail_concat = '<html>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">

<STYLE>
 a { color:#009A99; text-decoration: none}
</STYLE>

<table width="95%" cellpadding="10" cellspacing="0" class="backgroundTable" ';
$mail_concat.= "bgcolor='#FFFFFF' >" ;
$mail_concat.= '<tr>
<td valign="top" align="center">';
	
	$mail_concat.= '<table width="709" border="0" cellpadding="0" cellspacing="0" align="center" >
	<tr>
		<td colspan="3">
			<a href="http://www.demandezleprogramme.be/"><img src="' . $racine_domaine . 'agenda/e_card/pics/carte-c_01.gif" height="117" alt="Visitez le site de demandezleprogramme !" title="Visitez le site de demandezleprogramme !"  style="border: 0;"></td>
	</tr>
	<tr>
		<td colspan="3" valign="top" background="' . $racine_domaine . 'agenda/e_card/pics/carte-c_02_1.gif" >
		
		 <br /> <br /> <span style="' . $style_bonjour . '">Bonjour ' . $nom_destinataire . ',</span> <br /> <br />' ;

	
	// S'il existe un message perso, l'inclure dans l'email :
	if (isset($texte_message_perso) AND $texte_message_perso != NULL)
	{
		$mail_concat.= '<span style="' . $style_bonjour . '">' . $nom_destinateur . ' pense que l\'événement suivant pourra vous intéresser. </span> <br />
		<span style="' . $style_bonjour . '">Voici son message : </span> <br /> <br />
		
		<div style="' . $style_message_perso . '">' . $texte_message_perso . '</div>  <br /> <br />' ;
	}
	else
	{
		$mail_concat.= '<span style="' . $style_bonjour . '">' . $nom_destinateur . ' pense que l\'événement suivant pourra vous intéresser : </span> <br /> <br />';
	}


	$mail_concat.= '</td>
	</tr>
	<tr>
		<td width="222" valign="top" background="' . $racine_domaine . 'agenda/e_card/pics/carte-c_03.gif">
		
		<div style=" margin-top: 0px; margin-left: 18px; height: 320px; overflow: hidden; ">
		<a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '">
	<img src="' . $racine_domaine . 'agenda/' . $folder_pics_event . 'event_' . $id_event . 
	'_1.jpg" title="' . $nom_event . '"  style="border: 0;" />
	</a></div>
	
	</td>
		<td width="363" valign="top" background="' . $racine_domaine . 'agenda/e_card/pics/carte-c_04.gif">
				
		<a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '"><span style="' . $style_titre_evenement . '"> ' . $nom_event . '</span> </a> <br /> <br />' ;
	
	$max=750; // Longueur MAX pour description courte
	//$allowed_tags = '<p>, <br>, <br />, <BR />, <BR />';
	$allowed_tags = '';
	$descript_strip_tags = strip_tags($description_event,$allowed_tags) ;
	$chaine_raccourcie = raccourcir_chaine ($descript_strip_tags,$max); // retourne $chaine_raccourcie	
	$mail_concat.= '<div style="' . $style_texte_evenement . '">' . $chaine_raccourcie . '<br />' ;
	
	// ***************************************************************************************
		// En savoir plus :
	$mail_concat.= '<br /> <br /> <a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '">&gt;&gt; En savoir plus</a>
	<br /> <br /> ';

	// ***************************************************************************************
	// Boutons
	$mail_concat.= '<div align="center">' ;

	// Critiques :
	if (isset($critique_event) AND $critique_event != 0)
	{
		$mail_concat.= '<a href="' . $racine_domaine . '-Critiques-?id_article=' . $id_event . '#critique">
		<img src="' . $racine_domaine . 'agenda/e_card/pics/ico_critique.jpg"  style="border: 0;" />
		</a>' ;			
	}
	
	//  Avis :
	$count_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE 
	(event_avis = $id_event OR event_avis = $saison_preced_event)
	AND publier_avis = 'set'");
	$nbr_avis = mysql_fetch_array($count_avis);
	$total_entrees = $nbr_avis['nbre_entrees'];
	
	if ($total_entrees >= 1)
	{
		$mail_concat.= '<a href="' . $racine_domaine . '-Detail-agenda-?id_event=' . $id_event . '#avis">
		<img src="' . $racine_domaine . 'agenda/e_card/pics/ico_avis.jpg"  style="border: 0;" />
		</a>
	';
	}
	
	// Bouton de Réservation
	$time_date_event_fin = date(mktime(0, 0, 0, $date_event_fin_mois, $date_event_fin_jour, $date_event_fin_annee));
	$time_date_aujourdhui = date(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
	
	if (!empty($donnees_lieu['email_reservation']) AND $donnees_lieu['email_reservation'] != NULL 
	AND  ($time_date_event_fin+86400) > ($time_date_aujourdhui+0)
	AND ($genre_event != 'g07'))
	{
		$mail_concat.= '<a href="' . $racine_domaine . '-Reserver-?id_event='. $id_event . '" >
		<img src="' . $racine_domaine . 'agenda/e_card/pics/ico_reservez.jpg"  style="border: 0;" />
		</a>' ;
	}

	// Bouton Concours (afficher si concours actif)
	
	$limit_afficher = (time()-(3600*24*3000)); // Date actuelle moins quelques jours
	$public_cible_like = '%'. $public_cible . '%' ;

	$reponse_conc = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE lots_conc LIKE '$public_cible_like' 
	AND cloture_conc > $limit_afficher
	AND flags_conc LIKE '%actif%'
	AND event_dlp_conc = $id_event
	LIMIT 1");
	$donnees_conc = mysql_fetch_array($reponse_conc) ;
	if (isset ($donnees_conc['nom_event_conc']) AND !empty($donnees_conc['nom_event_conc']))
	{	
		$mail_concat.= '<a href="' . $racine_domaine . '-Concours,95-?id_event='. $id_event . '" >
		<img src="' . $racine_domaine . 'agenda/e_card/pics/ico_concours.jpg" style="border: 0;" />
		</a>' ;
	}

	
	// Bouton lire l'interview
	if (isset ($donnees['interview_event']) AND $donnees['interview_event'] != 0 )
	{ 
		$interview_event = $donnees['interview_event'] ;
		$mail_concat.= '<a href="' . $racine_domaine . 'spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" title="Cliquez ici pour lire l\'interview">
		<img src="' . $racine_domaine . 'agenda/e_card/pics/ico_interview.jpg" style="border: 0;" />
		</a>' ;
	}
	
	
	
	
	
	$mail_concat.='</div>';


	$mail_concat.= '</td>
		<td width="124" valign="top" background="' . $racine_domaine . 'agenda/e_card/pics/carte-c_05.gif">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3">
			<img src="' . $racine_domaine . 'agenda/e_card/pics/carte-c_06.gif" height="26"  style="border: 0;" ></td>
	</tr>
	
</table>

</td>
</tr>

</table>

';


	// Bas de page
	$mail_concat.= '<div style="' . $style_bas . '"><strong>Demandez le programme&nbsp;!</strong> 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> - 
	<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a><br />
	Vertige asbl - 163, rue de la Victoire 1060 Bruxelles - 
	Tel/fax&nbsp;: 02/544 00 34<br />
	IP du destinantaire : ' . $ip_destinateur . '</div></tr></td>' ;
	
	$mail_concat.= '</body> </html>' ;
	// FIN CONCATENATION
	//echo $mail_concat ; 
	
	
	// Envoi e-mail
	$entete= "Content-type:text/html\nFrom:" . $email_destinateur . "\r\nReply-To:" . $email_destinataire ;

	$sujet_encode = $nom_destinateur . ' vous conseille l\'événement suivant : ' . $nom_event ;
	$sujet = '>> ' . html_entity_decode($sujet_encode, ENT_QUOTES) ;
	$test_mail = mail_beta($email_destinataire,$sujet,$mail_concat,$entete,$email_retour_erreur);
	//if(1==1)
	if($test_mail)
	{
		echo '<div class="info">
		Un courriel reprenant l\'événement culturel que vous recommandez a bien été envoyé à 
		' . $nom_destinataire . ' (' . $email_destinataire . ')<br />
		<br />
		Merci d\'utiliser notre agenda culturel <br />
		<em>L\'équipe de <a href="http://www.demandezleprogramme.be">demandezleprogramme !</a></em><br />
		</div>' ;
		
		
		
		
		// -------------------------------------------
		// Enregistrement dans les stats :
		// -------------------------------------------
		$date_stat = date('Y-m-d') ;
		
		mysql_query("INSERT INTO `ag_rapport_ecards` ( `id_ecards` , `ecards_event` , `ecards_lieu` , `ecards_pour` , `ecards_date` , `ecards_nom` , `ecards_email` ) 
		VALUES ('', '$id_event', '$lieu_event', '$email_destinataire', '$date_stat', '$nom_destinateur', '$email_destinateur' )") 
		or die('Erreur SQL 1 :<br>'. mysql_error());

		// -------------------------------------------



		
	}
	//echo $mail_concat . '<br>' ; // test_aff 

	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE

	
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// Informer l'administrateur par E-mail
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	/*$mail_concat = '';
	$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css"> <!-- '
	. $css_email . 
	'--> </style> </head> <body> ';
	
	$mail_concat.= '<p>&nbsp;</p> <table width="500" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#EEEEEE"><tr><td>' ;
	$mail_concat.= '<h2 class="email_style_titre">
	E-card envoyée : <br /></h2> <br /> ';
	
	$mail_concat.= '<p><strong>De ' . $nom_destinateur . '</strong> (' . $email_destinateur . ')
	<strong>à ' . $nom_destinataire . '</strong> (' . $email_destinataire . ')<br />		
	<strong>Ev&eacute;nement concern&eacute; :</strong> "' . $nom_event . '"<br />
	<strong>Date :</strong> ' . $t_stamp_envoi . '</p>' ;
	
	if (isset($texte_message_perso) AND $texte_message_perso != NULL)
	{
		$mail_concat.= '<p><strong>Son message :</strong> <br /><em>' . $texte_message_perso . '</em></p>' ;
	}
	
	$mail_concat.= '<div class="email_style_petit" align="center"><br /><br /><a href="' . $racine_domaine . 'agenda/e_card/rec_logs/logs_flyers.txt">Fichier log des envois d\'e-cards</a></div>' ; 

	$mail_concat.= '</td></tr></table>' ;
	$mail_concat.= '</body></html>' ;
	//echo '<br />' . $mail_concat . '<br /><br />'; 
	
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = '>> ' . html_entity_decode('e-card envoyée', ENT_QUOTES) ;
	
 mail_beta($email_moderateur_site,$sujet,$mail_concat,$entete); */

	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE


	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	// Informer le visiteur par E-mail 
	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE
	$mail_concat = '';
	$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"> <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css"> <!-- '
	. $css_email  . '
	 a { color:#009A99; text-decoration: none}
	 .email_style_2 { font-size: 12px; color:#AA0033; font-weight: bold; }
	 .email_style_2_tur { font-size: 12px; color:#009A99; font-weight: bold; }
	--> </style> </head> <body> ';
	
	$mail_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
	
	// Logo 
	$mail_concat.= '<a href="http://www.demandezleprogramme.be">
	<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';
	
	
	$mail_concat.= '<td class="email_style_petit" align="center">
	Vous recevez ce message car vous avez envoyé un "courriel recommandant un événement culturel" sur le site de 
	<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a>.</td></tr>';
	
	$mail_concat.= '<tr><td colspan="2" >
	<p><strong>' . $nom_destinateur . '</strong>,</p>
	<p>Nous vous remercions d\'avoir utilis&eacute; notre site pour recommander
	"' . $nom_event . ' à ' . $nom_destinataire . '</strong>
	(<a href="mailto:' . $email_destinataire . '">' . $email_destinataire . '</a>)
	le ' . $t_stamp_envoi . '</p>' ;

	// Concours
	$mail_concat.= ' <br /><p align="center">N\'oubliez pas que vous pouvez participer à nos 
	<a href="' . $racine_domaine . '-Concours,95-?id_event='. $id_event . '" >
	nombreux concours !
	</a></p>' ;


	$mail_concat.= '<p> <br />A tr&egrave;s bient&ocirc;t !</p> <br />
	<p>L\'&eacute;quipe de Demandezle programme. </p> 
	<p class="email_style_petit"><a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Vertige asbl <br />
	Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
	<a href="http://www.vertige.org">www.vertige.org</a> </p>' ;

	$mail_concat.= '</td></tr></table>' ;
	$mail_concat.= '</body></html>' ;
	//echo $mail_concat ;
	
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet_encode = 'Merci d\'avoir envoyé une e-card sur demandezleprogramme.be' ;
	$sujet = html_entity_decode($sujet_encode, ENT_QUOTES) ;
 mail_beta($email_destinateur,$sujet,$mail_concat,$entete,$email_retour_erreur);

	echo '<div align="center">
	<strong><a href="-Envoyer-a-un-ami-?id_event=' . $id_event . '">&gt; &gt; Envoyer une autre e-card &lt; &lt; </a> </strong>
	</div>' ;
	}
	else
	{
		echo '<div class="alerte">Le formulaire n\'a pas été correctement complété. <br />Veuillez vérifier les données</div>' ;
	}
}

	// EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE



	// ////////////////////////////////////////////////////////////////////////////////////////////
	// ////////////////////////////////////////////////////////////////////////////////////////////
	// Afficher le formulaire
	// ////////////////////////////////////////////////////////////////////////////////////////////
	$form_concat = '' ;
	$tab = '' ;
	
	if ($form_masquage == false)
	{		
	$form_concat.='<form name="form1" method="post" action=""><table width="80%" border="0" cellspacing="0" cellpadding="5" class="table_public" align="center">
	  <tr>
		<td width="50%">' ;
		
		//_________ Afficher récapitulatif événement _________
		// Image
		if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
		{
			$form_concat.= '<img src="' . $racine_domaine . 'agenda/' . $folder_pics_event . 'vi_event_' . $id_event . 
			'_1.jpg" title="' . $nom_event . '" />';
		}
		$form_concat.= '&nbsp; </td> ';
		
		// Nom de l'événement
		$form_concat.= '<td><span class="detail_event_titre" align="center">' . $nom_event . '</span>
		</td> </tr>';

		//_________ nom destinataire _________
		$form_concat.= '<tr><td align="center">
		<br />Prénom et nom de votre ami<span class="champ_obligatoire">*</span> :<br /> 
		<input name="nom_destinataire" type="text" id="nom_destinataire" ';
		if (isset($nom_destinataire))
		{ $form_concat.= 'value="' . $nom_destinataire . '"'; }
		$form_concat.= ' size="30" maxlength="30"> <br />';
		// Message erreur
		if (isset ($error_nom_destinataire) AND $error_nom_destinataire != NULL) {$form_concat.= $error_nom_destinataire; }	


		//_________ email destinataire _________
		$form_concat.= '<br />Adresse e-mail de votre ami<span class="champ_obligatoire">*</span> : <br />
		<input name="email_destinataire" type="text" id="email_destinataire" ';
		if (isset($email_destinataire))
		{ $form_concat.= 'value="' . $email_destinataire . '"'; }
		$form_concat.= ' size="30" maxlength="350">';
		// Message erreur
		if (isset ($error_email_destinataire_event) AND $error_email_destinataire_event != NULL) {$form_concat.= $error_email_destinataire_event ; }
		

		$form_concat.='</td>
		<td align="center"><br />' ;
		//_________ nom destinateur _________
		$form_concat.= 'Votre prénom et votre nom <span class="champ_obligatoire">*</span> :<br /> 
		<input name="nom_destinateur" type="text" id="nom_destinateur" ';
		if (isset($nom_destinateur))
		{ $form_concat.= 'value="' . $nom_destinateur . '"'; }
		$form_concat.= ' size="30" maxlength="30"> <br />';
		// Message erreur
		if (isset ($error_nom_destinateur) AND $error_nom_destinateur != NULL) {$form_concat.= $error_nom_destinateur; }	


		//_________ email destinateur _________
		$form_concat.= '<br />Votre adresse e-mail<span class="champ_obligatoire">*</span> : <br />
		<input name="email_destinateur" type="text" id="email_destinateur" ';
		if (isset($email_destinateur))
		{ $form_concat.= 'value="' . $email_destinateur . '"'; }
		$form_concat.= ' size="30" maxlength="350">';
		// Message erreur
		if (isset ($error_email_destinateur_event) AND $error_email_destinateur_event != NULL) {$form_concat.= $error_email_destinateur_event ; }
				
		$form_concat.='</td>
	  </tr>';


		//_________ Message personnel _________
		$form_concat.='<tr>
		<td colspan="2" align="center"><br />Votre message personnel (facultatif) : ';
		if (isset($error_texte_message_perso) AND $error_texte_message_perso != NULL) 
		{
			$form_concat.= $error_texte_message_perso ;
		}
	  
		$form_concat.='<textarea name="champ_texte_perso" style="width: 450px; height: 200px">';
		if (isset($texte_message_perso)){$form_concat.= br2nl($texte_message_perso);}
		$form_concat.='</textarea>		
		</td>
		</tr>';
			  
	
		//_________ IMAGE ROBOTS _________
		$form_concat.= '<tr><td colspan="2" align="center"> 
		<br /> <br /> Recopier le code de l\'image<span class="champ_obligatoire">*</span> : 
		<input name=code type=text id="code" size="3" maxlength="3"> 
		<img src=agenda/user_admin/ins/im_gen.php?session=' . $session . ' hspace="10" align="top">';
		// Message erreur
		if (isset ($error_image_crypt) AND $error_image_crypt != NULL) {$form_concat.= $error_image_crypt ; } 
	
		$form_concat.= '
		<input type=hidden name=sid value=' . $session . '><div align="center"> <br /> <br />
		<input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Envoyer">
		<br /></div>
		<label>
		<div align="center"> <br /> <br />
		<input type="checkbox" name="recevoir_publication" value="ok" checked="checked" />
		Je souhaite recevoir la lettre d\'information de 
		<a href="http://www.demandezleprogramme.be/">demandezleprogramme.be</a></div>
		</label><br />
		' ;
	  
	  $form_concat.='</td>
	  </tr>
	</table></form>' ;
	
	echo $form_concat . '<br />' ;
}

?>
