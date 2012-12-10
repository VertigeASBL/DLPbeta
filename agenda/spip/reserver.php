
<?php 
require 'agenda/inc_var.php';
require 'agenda/inc_var_dist_local.php';
require 'agenda/inc_fct_base.php';
require 'agenda/user_admin/ins/inc_var_inscription.php';
require 'agenda/calendrier/inc_calendrier.php';

$max = 350 ; // Longueur max du texte (en nombre de caractères) que le visiteur peut poster
$allowedTags = '<br><br />'; // Balises de style que les visiteurs peuvent employer

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
//Page contenant le formulaire permettant aux visiteurs de réserver ses places de spectacle on-line
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage des cases à cocher dans le calendrier
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function mettre_check_box ($jours_actifs, $MM_traite, $AAAA_traite)
{
	echo '<span class="alignLeftMargin">' ;
	
	global $date_event_debut;
	global $date_event_fin;	
	global $date_reservation;	
	$date_event_debut_condition = str_replace("-","",$date_event_debut); 
	$date_event_fin_condition = str_replace("-","",$date_event_fin); 
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{

		// Composer la chaine qui sera cherchée dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$date_traite = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
		settype($JJ_traite, "integer"); // Pour éviter problèmes avec les nombres précédés de "0"

		$date_traite_condition = str_replace("-","",$date_traite); 

		// Préparation des variables pour test "jour actif dépassé ou pas ?"
		$time_date_traite = date(mktime(0, 0, 0, $MM_traite, $JJ_traite, $AAAA_traite));
		$time_date_aujourdhui = date(mktime(0, 0, 0, date("m"), date("d"), date("Y")));

		/*$AAAA_time_jours_actifs = substr($jours_actifs, 0, 4);
		$MM_time_jours_actifs = substr($jours_actifs, 5, 2);
		$JJ_time_jours_actifs = substr($jours_actifs, 8, 2);
		$time_jour_actif = date(mktime(0, 0, 0, $MM_time_jours_actifs, $JJ_time_jours_actifs, $AAAA_time_jours_actifs));*/

		//echo $time_date_traite .' - '.$time_date_aujourdhui .'<br>';


		// jour HORS période
		if (($date_traite < $date_event_debut)OR($date_traite > $date_event_fin))
		{
			//echo $date_traite_condition .' - ' .$date_event_debut_condition .'<br>';
			$tableau_jours[$JJ_traite] = array(NULL,'hors_periode',$JJ_traite);
		}
		// jour ACTIF non dépassé (donc cochable)	
		elseif (in_array($date_traite, $jours_actifs) AND (($time_date_traite+86400) > ($time_date_aujourdhui+0)))
		{
			$uioooooo = $JJ_traite . '<br /> <input name="date_reservation" type="radio" value="' . $date_traite . '"';
			if (isset($date_reservation) AND $date_reservation == $date_traite) {$uioooooo.= ' checked="checked" ' ; }
			$uioooooo.= '/>' ;
			//echo $uioooooo ;
			$tableau_jours[$JJ_traite] = array(NULL,'actif', $uioooooo);
		}

		// jour actif dépassé
		elseif (in_array($date_traite, $jours_actifs) AND (($time_date_traite+0) < ($time_date_aujourdhui+0)))
		{
			$tableau_jours[$JJ_traite] = array(NULL,'actif_depasse', $JJ_traite . '<br />X<br />');
		}
		
		// jour non actif DANS période
		elseif (($date_traite > $date_event_debut) AND ($date_traite < $date_event_fin) AND (!in_array($date_traite, $jours_actifs)))
		{
			$tableau_jours[$JJ_traite] = array(NULL,'hors_periode',$JJ_traite);
		}
	}
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1); // Affichage du calendrier
	echo '</span>' ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction des lecture des cases à cocher du calendrier
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function lire_check_box ($jours_actifs, $MM_traite, $AAAA_traite)
{ 
	// Lire le les checkbox cochées et en faire une chaine
	global $comp_chaine_date ;
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		// Composer la chaine qui sera cherchée dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$date_traite = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
		//settype($JJ_traite, "integer"); // Pour éviter problèmes avec les nombres précédés de "0"
				
		if (isset ($_POST[$date_traite]))
		{
			// echo $_POST[$date_traite] . ' <-----> ' . $date_traite . '<br>';
			$comp_chaine_date.= $_POST[$date_traite] . ','; 
		}
	} // echo $comp_chaine_date ;
}
// ----------------------------------------------------------------------------------------------


if (empty ($_GET['id_event']) OR $_GET['id_event'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="index.php" >Retour</a></div>' ;
	exit();
}
else
{
	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);

	// Récupération des données concernant ce spectacle
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
	ON lieu_event = id_lieu WHERE id_event = '$id_event' ") ;
	
	$donnees_event = mysql_fetch_array($reponse) ;

	if (empty($donnees_event['email_reservation']) OR $donnees_event['email_reservation'] == NULL )
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Ce LIEU n\'accepte pas ce type réservation<br>
		<a href="index.php" >Retour</a></div>' ;
		exit();
	}
	
	$id_lieu = $donnees_event['id_lieu'];
	$lieu_event = $donnees_event['lieu_event'];
	$nom_event = $donnees_event['nom_event'];
	$nom_lieu = $donnees_event['nom_lieu'];
	$email_reservation_lieu = $donnees_event['email_reservation'];
	$tel_lieu = $donnees_event['tel_lieu'];
	$adresse_lieu = $donnees_event['adresse_lieu'];

	$date_event_debut = $donnees_event ['date_event_debut'];
	$date_event_fin = $donnees_event ['date_event_fin'];

	$AAAA_debut = substr($date_event_debut, 0, 4);
	$AAAA_fin = substr($date_event_fin, 0, 4);
	$MM_debut = substr($date_event_debut, 5, 2);
	$MM_fin = substr($date_event_fin, 5, 2);
	$JJ_debut = substr($date_event_debut, 8, 2);
	$JJ_fin = substr($date_event_fin, 8, 2);
	$AAAA_MM_debut = substr($date_event_debut, 0, 7);


	$jours_actifs_event = $donnees_event ['jours_actifs_event'];
	$jours_actifs_event = explode(",", $jours_actifs_event);

}

//LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL

$form_masquage = false ; // Rendre visible le formulaire

$session = md5(time()); // numero d'identification du visieur
$ip = $_SERVER['REMOTE_ADDR'] ;
$t_stamp = time();

// code aléatoire pour l'image generee :
$nb_car = 3 ;
$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
$txt = str_shuffle($txt);
$code = substr($txt, 10, $nb_car);

mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) VALUES ('$session','$code','$t_stamp','$ip')");

//---------------------------------------------------------
// Si bouton enfoncé, alors lancer l'analyse des données
//---------------------------------------------------------
if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer')) 
{
	//---------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//---------------------------------------------------------
	$rec = ''; 
	
	// ------------------------------------------------------------
	// TEST DU NOM
	if (isset($_POST['nom_reservation']) AND ($_POST['nom_reservation'] != NULL)) 
	{
		$nom_reservation = stripslashes(htmlentities($_POST['nom_reservation'], ENT_QUOTES));
	}
	else
	{
		$rec .= '- Vous devez introduire un nom pour la réservation<br>';
		$error_nom_reservation = '<div class="error_form">Vous devez introduire un nom pour la réservation</div>';
	}


	// ------------------------------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['email_reservation']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_reservation']))))
	{
		$email_reservation = $_POST['email_reservation'];
	}
	else
	{
		$email_reservation = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_email_reservation_event = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
	}



	// ------------------------------------------------------------
	//  TEST TELEPHONE
	if (isset($_POST['tel_reservation']) AND $_POST['tel_reservation'] != NULL)
	{
		$tel_reservation = $_POST['tel_reservation'];
	}
	else
	{
		$tel_reservation = '';
		$rec .= '- Vous devez introduire un numéro de téléphone<br>';
		$error_tel_reservation_event = '<div class="error_form">Vous devez introduire un numéro de téléphone afin que l\'organisateur puisse confirmer votre commande</div>';
	}



	// ------------------------------------------------------------
	//  NOMBRE DE PLACES
	if (isset($_POST['nombre_places']) AND is_numeric($_POST['nombre_places']) AND ($_POST['nombre_places'] !=0 ))
	{
		$nombre_places = $_POST['nombre_places'];
	}
	else
	{
		echo $nombre_places ;
		$nombre_places = '';
		$rec .= '- Vous devez préciser le nombre de places que vous souhaitez réserver<br>';
		$error_nombre_places_event = '<div class="error_form">Vous devez préciser le nombre de places que vous souhaitez réserver</div>';
	}



	/*// ------------------------------------------------------------
	// DATE DE L'EVENEMENT 

	if (isset($_POST['select_AAAA']) AND ($_POST['select_AAAA'] != NULL) AND preg_match('/[0-9]{4}$/', $_POST['select_AAAA']) AND 
	isset($_POST['select_MM']) AND ($_POST['select_MM'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['select_MM']) AND
	isset($_POST['select_JJ']) AND ($_POST['select_JJ'] != NULL) AND preg_match('/[0-9]{2}$/', $_POST['select_JJ'])) 
	{
		$JJ_reservation = htmlentities($_POST['select_JJ'], ENT_QUOTES);
		$MM_reservation = htmlentities($_POST['select_MM'], ENT_QUOTES);
		$AAAA_reservation = htmlentities($_POST['select_AAAA'], ENT_QUOTES);
	}
	else
	{
		$rec .= '- Vous devez indiquer la date de l\'événement que vous réservez<br>';
		$error_date = '<div class="error_form">Vous devez indiquer la date de l\'événement que vous réservez</div>';
	}*/
	
	
	
	// ------------------------------------------------------------
	// DATE DE L'EVENEMENT 
	if (isset($_POST['date_reservation']) AND ($_POST['date_reservation'] != NULL)) 
	{
		$date_reservation = htmlentities($_POST['date_reservation'], ENT_QUOTES);

		$JJ_reservation = substr($date_reservation, 8, 2);
		$MM_reservation = substr($date_reservation, 5, 2);
		$AAAA_reservation = substr($date_reservation, 0, 4);
	}
	else
	{
		$rec .= '- Vous devez indiquer la date de l\'événement que vous réservez<br>';
		$error_date = '<div class="error_form">Vous devez indiquer la date de l\'événement que vous réservez</div>';
	}
	//echo '<br /><span class="email_style_rubriques">Date de l\'événement : </span>' . 		$JJ_reservation . '-' . $MM_reservation . '-' . $AAAA_reservation  ; 
		


	// -----------------------------------------
	// TEST TEXTE RESERVER 
	
	if (strlen($_POST['ajaxfilemanager'])>=$max)
	{
		$texte_libre = str_replace("</p>","<br />",$_POST['ajaxfilemanager']); 
		$texte_libre = stripslashes(strip_tags($texte_libre,$allowedTags));
		$texte_libre = wordwrap($texte_libre, 50, " ", 1);
		
		$char_en_trop = strlen($texte_libre) - $max ; // Tester longueur de la chaîne de caractères
		$error_texte_libre = '<div class="error_form">
		La taille du texte dépasse la limite autorisée. Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir</div>';
		$rec .= '- taille  texte trop grande';
	}
	else
	{
		$texte_libre = str_replace("</p>","<br />",$_POST['ajaxfilemanager']); 
		$texte_libre = stripslashes(strip_tags($texte_libre,$allowedTags));
		$texte_libre = wordwrap($texte_libre, 50, " ", 1);

		$texte_libre_2_db = addslashes($texte_libre) ;
	}



	// ------------------------------------------------------------
	// Test du code recopié à partir de l'image cryptée
	
	$get_sess = $_POST['sid'];

	$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	$donnees = mysql_fetch_array($reponse);
	
	if ($donnees ['code_crypt']=="" OR $donnees ['code_crypt']!=$_POST['code']) // Code non valide //(1==2)
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



	//---------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	if ($rec == NULL) // Enregistrement les données dans la DB 
	{
		$form_masquage = true; // Masquer le formulaire
		
		echo '<div class="info">Votre demande a bien été envoyée.
		<br /><a href="-Agenda-">Retour au site</a>
		</div><br>' ;
		
		
		
		// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
		// Informer le LIEU ainsi que l'administrateur DLP par E-mail
		$mail_concat = '';
		$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml"> <head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<style type="text/css"> <!-- ' . $css_email . '--> </style> </head> <body> ';
		$mail_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
		
		// Logo 
		$mail_concat.= '<a href="http://www.demandezleprogramme.be">
		<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';
		
		
		$mail_concat.= '<td align="center"><p class="email_style_titre">Au responsable du service de réservations de ' . 
		$nom_lieu . '</p></td></tr>';
		
		$mail_concat.= '<tr><td colspan="2" ><p>Bonjour, <br />Une réservation a été effectuée par un visiteur du site 
		<a href="http://www.demandezleprogramme.be">Demandez le programme</a> dont vous êtes partenaire. 
		Voici les infos utiles afin de gérer cette réservation qui est désormais sous votre contrôle. 
		En cliquant sur "répondre" votre mail partira directement vers le spectateur.<br /> </p>';
		
		$mail_concat.= '<p><span class="email_style_rubriques">Nom : </span>' . $nom_reservation ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Nombre de places : </span>' . $nombre_places ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Ev&eacute;nement : </span>' . $nom_event ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Lieu concern&eacute; : </span>' . $nom_lieu ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Date de l\'événement : </span>' . 
		$JJ_reservation . '-' . $MM_reservation . '-' . $AAAA_reservation  ;
		$mail_concat.= '<br /><span class="email_style_rubriques">E-mail : </span>' . $email_reservation ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Téléphone : </span>' . $tel_reservation ;
	
		if (isset($texte_libre) AND $texte_libre != NULL)
		{
			$mail_concat.= '<br /> <br /> <span class="email_style_rubriques">Le visiteur a laissé la remarque suivante : </span>
			' . $texte_libre . '';
		}

		$mail_concat.= '<br /> <br /> <p>- Les visiteurs de notre site sont informés du fait qu\'après 16h, 
		peu de lieux prennent en considération des réservations pour le jour même.<br />
		- Nous insistons aussi sur le fait que leur réservation ne sera effective qu\'après confirmation 
		de votre part, par email ou par téléphone.' ;


		$mail_concat.= '<br /> <br /> <br /><p class="email_style_petit">L\'&eacute;quipe de Demandezle programme. <br />
		<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
		<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
		Vertige asbl <br />
		Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
		<a href="http://www.vertige.org">www.vertige.org</a>
		
		<br /><em> Date de l\'envoi : ' . date('d/m/Y - H\hi', $t_stamp) .
		' - Adresse IP : ' . $ip . '</em><br /></p>' ;
		$mail_concat.= '</td></tr></table>' ;
		$mail_concat.= '</body></html>' ;
		
		$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $email_reservation ;
		$sujet = encodeHeader('Une réservation via Demandez le programme');

		// $email_destinataires = $email_reservation_lieu . '; ' . $retour_email_admin ; 
	 mail_beta($email_reservation_lieu,$sujet,$mail_concat,$entete,$email_retour_erreur);
		//echo $mail_concat ;

		
		// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
		// E-mail de confirmation au visiteur
		$mail_concat = '';
		$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml"> <head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<style type="text/css"> <!-- ' . $css_email . '--> </style> </head> <body> ';
		$mail_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
		
		// Logo 
		$mail_concat.= '<a href="http://www.demandezleprogramme.be">
		<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';
		
		$mail_concat.= '<td align="center"><p class="email_style_titre">Votre réservation en ligne, <br />
		effectuée le ' . date('d/m/Y - H\hi', $t_stamp) . ' sur le site de 
		<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a></p></td></tr>';
		
		$mail_concat.= '<tr><td colspan="2" >';
		
		$mail_concat.= '<p>Bonjour, <br /> <br />Votre demande de réservation a bien été envoyée. <br />
		Vous recevrez prochainement une confirmation de celle-ci par l\'organisateur de l\'événement. 
		Cette confirmation est nécessaire pour vous assurer que la réservation a bien été prise en compte. 
		Si, cas exceptionnel, cette confirmation ne vous parvenait pas, vous pouvez joindre l\'organisateur 
		au ' . $tel_lieu . '. <br /> <br /> Nous vous remercions de faire appel à nos services.</p>
		<p><em>L\'&eacute;quipe de Demandezle programme. </em></p> <br /> <br />' ;
		
		$mail_concat.= '<p><span class="email_style_rubriques">Nom : </span>' . $nom_reservation ;
		$mail_concat.= '<br /><span class="email_style_rubriques">E-mail : </span>' . $email_reservation ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Nombre de places : </span>' . $nombre_places ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Date de l\'événement : </span>' . 
		$JJ_reservation . '-' . $MM_reservation . '-' . $AAAA_reservation  ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Ev&eacute;nement : </span>' . $nom_event  ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Lieu concern&eacute; : </span>' . $nom_lieu . ' ' ;
		$mail_concat.= '<br /><span class="email_style_rubriques">Adresse : </span>' . $adresse_lieu . '<br />' ;

		if (isset($texte_libre) AND $texte_libre != NULL)
		{
			$mail_concat.= '<span class="email_style_rubriques">Votre remarque : </span>
			' . $texte_libre . '';
		}
		
		$mail_concat.= '<br /> <p class="email_style_petit"><br />
		<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
		<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
		Vertige asbl <br />
		Visitez &eacute;galement <a href="http://www.comedien.be">www.comedien.be</a> et 
		<a href="http://www.vertige.org">www.vertige.org</a>
		
		<br /><em> Date de l\'envoi : ' . date('d/m/Y - H\hi', $t_stamp) .
		' - Adresse IP : ' . $ip . '</em><br /></p>' ;
		$mail_concat.= '</td></tr></table>' ;
		$mail_concat.= '</body></html>' ;
		
				
		$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
		$sujet = 'Votre réservation en ligne à partir du site Demandezleprogramme' ;
	 mail_beta($email_reservation,$sujet,$mail_concat,$entete,$email_retour_erreur);
		//echo $mail_concat ;
		
		
		// -------------------------------------------
		// Enregistrement dans les stats :
		// -------------------------------------------
		/*$reserv_event = 94 ;
		$reserv_lieu = 9 ;
		$reserv_nombre = 2 ;
		$reserv_date = '2008-01-22' ;*/
		
		$reserv_date_stat = $AAAA_reservation . '-' . $MM_reservation . '-' . $JJ_reservation ;
		
		mysql_query("INSERT INTO `ag_rapport_reservations` ( `id_reservation` , `reserv_event` , `reserv_lieu` , `reserv_nombre` , `reserv_date` , `reserv_nom` , `reserv_email` ) 
		VALUES ('', '$id_event', '$id_lieu', '$nombre_places', '$reserv_date_stat', '$nom_reservation', '$email_reservation' )") 
		or die('Erreur SQL 1 <br>'.$sql_type.'<br>'.mysql_error());

		// -------------------------------------------





	}
	else
	{
		echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
	}
}




if ($form_masquage == false )
{
?>

<!-- -----------------------------------------------------------------
// Afficher formulaire
// ----------------------------------------------------------------- -->
<form name="form1" method="post" action="">
  <table width="500" border="0" align="center" cellpadding="5" cellspacing="1" class="table_public" >
    
	    <tr>
	      <td colspan="2"><div align="center"><br />Vous r&eacute;servez vos places pour l'&eacute;v&eacute;nement :<br />
	        <?php echo '<strong>&quot;<a href="-Detail-agenda-?id_event=' . $id_event . '">' . $nom_event . '</a>&quot;</strong>, proposé par <strong><a href="-Details-lieux-culturels-?id_lieu=' . $id_lieu . '">'  . $nom_lieu . '</a></strong>' ; ?></div>          </td>
    </tr>
	
    <tr>
      <td><?php if (isset ($error_nom_reservation) AND $error_nom_reservation != NULL) {echo $error_nom_reservation ; } ?>
      La r&eacute;servation se fait au nom/pr&eacute;nom de  : <span class="champ_obligatoire">*  </span>	  </td>
      <td><input name="nom_reservation" type="text" id="nom_reservation" value="<?php if (isset($nom_reservation)){echo $nom_reservation;}?>" size="30" maxlength="30"></td>
    </tr>
	    <tr>
      <td><?php if (isset ($error_email_reservation_event) AND $error_email_reservation_event != NULL) {echo $error_email_reservation_event ; } ?>        Votre adresse e-mail<span class="champ_obligatoire">*</span> : </td>
      <td><input name="email_reservation" type="text" id="email_reservation" value="<?php if (isset($email_reservation)){echo $email_reservation;}?>" size="30" maxlength="50"></td>
    </tr>
	
	    <tr>
	      <td><?php if (isset ($error_tel_reservation_event) AND $error_tel_reservation_event != NULL) {echo $error_tel_reservation_event ; } ?>T&eacute;l&eacute;phone <span class="champ_obligatoire">*</span> : </td>
	      <td><input name="tel_reservation" type="text" id="tel_reservation" value="<?php if (isset($tel_reservation)){echo $tel_reservation;}?>" size="30" maxlength="50"></td>
    </tr>
	    

    <tr>
	      <td><?php if (isset ($error_nombre_places_event) AND $error_nombre_places_event != NULL) {echo $error_nombre_places_event ; } ?>Nombre de places  <span class="champ_obligatoire">*</span> : </td>
	      <td><input name="nombre_places" type="text" id="nombre_places" value="<?php if (isset($nombre_places)){echo $nombre_places;}?>" size="2" maxlength="2"></td>
    </tr>

	
    <tr>
      <td colspan="2">Choisissez votre date : <br />
        <div align="center">
          <?php
	  setlocale(LC_TIME, 'fr_BE.ISO-8859-1');
	  
	  // [A] Si période comprise dans le même mois : traiter les jours de JJ_debut à JJ_fin
			if (($MM_debut == $MM_fin) && ($AAAA_debut == $AAAA_fin))
			{
				// echo  '<b> [A] Période couvrant 1 mois unique. Mois traité = '.$MM_traite.' et Année traitée = '.$AAAA_traite . '</b><br>' ;
				$AAAA_traite = $AAAA_debut ;
				$MM_traite = $MM_debut ;
				
				mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			}
			
			// ------------------------------------------------------------------------------------------------------
			else
			{
				// [B1] si la période s'étend sur plusieurs mois, afficher 1 calendrier à chaque passage dans la boucle. 
				// Commencer par traiter le mois de début de période
				$AAAA_MM_traite = substr($date_event_debut, 0, 7);
				$AAAA_traite = $AAAA_debut ;
				$MM_traite = $MM_debut ;
				// echo '<b>[B1] Mois traité (1er mois de la période) = '.$MM_traite.' et Année traitée = '.$AAAA_traite . '</b><br>' ;
				
				$tableau_jours = array() ;	
			
				mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			
				// Incrémenter le mois :		
				if	($MM_traite == 12)
				{
					$MM_traite = 1 ;
					$AAAA_traite = $AAAA_traite + 1 ;
				}
				else
				{
					$MM_traite = $MM_traite + 1 ;
				}
			
				// -------------------------------------------------------------------------------------------------
				// [B2] traiter tous les mois suivants jusqu'à ce qu'on arrive au mois de fin de PERIODE
				// La boucle s'arrête quand (($MM_traite == $MM_debut) && ($AA_fin == $AAAA_traite))
			
				while (($MM_traite != $MM_fin) OR ($AAAA_traite != $AAAA_fin))
				{
					/*unset ($tableau_jours[$JJ_db]);	*/
					$tableau_jours = array() ;
				
					//echo  '<b>[B2] Mois "suivant" traité = '.$MM_traite.' et Année traitée = '.$AAAA_traite.'</b><br>' ;
					
					mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			
					// Incrémenter le mois :		
					if	($MM_traite == 12)
					{
						$MM_traite = 1 ;
						$AAAA_traite = $AAAA_traite + 1 ;
					}
					else
					{
						$MM_traite = $MM_traite + 1 ;
					}
				}
				// -------------------------------------------------------------------------------------------------
				// [B3] traiter le dernier mois de JJ = 1 à JJ = JJ_fin
				$tableau_jours = array() ;
				$AAAA_MM_traite = substr($date_event_fin, 0, 7);
			
				//echo  '<b> [B3] Mois traité (Dernier mois de la période) = '.$MM_traite.' et Année traitée = '.$AAAA_traite . '</b><br>' ;
			
				mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			}
			
			
	  ?>
        </div>
        </td>
    </tr>

	
	
    <tr>
      <td colspan="2"><?php if (isset ($error_texte_libre) AND $error_texte_libre != NULL) {echo $error_texte_libre ; } ?>
        Facultatif : indiquez une remarque ou une question ici&nbsp; (exemple:&nbsp;  places &quot;&eacute;tudiants&quot;, &quot;seniors&quot;, demande de pr&eacute;cision...)<br />
        <div align="center"><textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 450px; height: 100px"><?php if (isset($texte_libre)){echo $texte_libre;} ?></textarea></div></td>
    </tr>

    <tr>
      <td>
		<?php if (isset ($error_image_crypt) AND $error_image_crypt != NULL) {echo $error_image_crypt ; } ?>
		Recopier le code de l'image<span class="champ_obligatoire">*</span> : </td>
      <td><input name=code type=text id="code" size="3" maxlength="<?php echo $nb_car; ?>">
          <img src=agenda/user_admin/ins/im_gen.php?session=<?php echo $session; ?> hspace="10" align="top">      </td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
	    Attention, la plupart des lieux ne prennent pas en 
         consid&eacute;ration les r&eacute;servations par Internet, apr&egrave;s 16h, pour le jour-m&ecirc;me.<br />
	      <input type=hidden name=sid value=<?php echo $session; ?>>
	      <br /><input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
         <br />
         <p><br /></p>
      </div></td>
    </tr>
  </table>
</form>

<?php	
}
?>