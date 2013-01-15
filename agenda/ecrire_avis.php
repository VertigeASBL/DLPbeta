
  <?php 
require 'agenda/inc_var.php';
require 'agenda/inc_var_dist_local.php';
require 'agenda/inc_fct_base.php';
require 'agenda/user_admin/ins/inc_var_inscription.php';

$max = 1800 ; // Longueur max du texte (en nombre de caractères) que le visiteur peut poster
$allowedTags = '<br><br />'; // Balises de style que les visiteurs peuvent employer

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
//Page contenant le formulaire permettant aux visiteurs d'insérer leur avis dans le fil de discussion
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


if (empty ($_GET['id_event']) OR $_GET['id_event'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="index.php" >Retour</a></div>' ;
	exit();
}
else
{
	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
	$event_avis = htmlentities($_GET['id_event'], ENT_QUOTES);
}

//LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL

$form_masquage = false ; // Rendre visible le formulaire
$avis_mailing_adresse = '' ;

$session = md5(time()); // numero d'identification du visieur
$ip_avis = $_SERVER['REMOTE_ADDR'] ;
$t_stamp_avis = time();

// code aléatoire pour l'image generee :
$nb_car = 3 ;
$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
$txt = str_shuffle($txt);
$code = substr($txt, 10, $nb_car);

mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) VALUES ('$session','$code','$timestamp','$ip')");

//---------------------------------------------------------
// Si bouton enfoncé, alors lancer l'analyse des données
//---------------------------------------------------------
if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer')) 
{
	//---------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//---------------------------------------------------------
	
	// = initialisation de la var qui sera testée avant d'enregistrer les données dans la DB
	// Si elle est vide => enregistrer Sinon, elle contient le message d'erreur, et on l'affiche.
	$rec = ''; 
	
	// ------------------------------------------------------------
	// TEST DU NOM
	if (isset($_POST['nom_avis']) AND ($_POST['nom_avis'] != NULL)) 
	{
		$nom_avis = stripslashes(htmlentities($_POST['nom_avis'], ENT_QUOTES));
	}
	else
	{
		$rec .= '- Vous devez introduire un nom <br>';
		$error_nom_avis = '<div class="error_form">Vous devez introduire un nom</div>';
	}


	// ------------------------------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['email_avis']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_avis']))))
	{
		$email_avis = $_POST['email_avis'];
	}
	else
	{
		$email_avis = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_email_avis_event = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
	}


	// -----------------------------------------
	// TEST TEXTE AVIS 
	if (empty($_POST['ajaxfilemanager']) OR ($_POST['ajaxfilemanager'] == NULL)) 
	{
		$error_texte_avis = '<div class="error_form">Vous devez introduire votre texte ci dessous</div>';
		$rec .= '- Pas de texte AVIS';
	}
	elseif (strlen($_POST['ajaxfilemanager'])>=$max)
	{
		$texte_avis = str_replace("</p>","<br />",$_POST['ajaxfilemanager']); 
		$texte_avis = stripslashes(strip_tags($texte_avis,$allowedTags));
		$texte_avis = wordwrap($texte_avis, 50, " ", 1);
		
		$char_en_trop = strlen($texte_avis) - $max ; // Tester longueur de la chaîne de caractères
		$error_texte_avis = '<div class="error_form">
		La taille du texte dépasse la limite autorisée. Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir</div>';
		$rec .= '- taille  texte trop grande';
	}
	else
	{
		$texte_avis = str_replace("</p>","<br />",$_POST['ajaxfilemanager']); 
		$texte_avis = stripslashes(strip_tags($texte_avis,$allowedTags));
		$texte_avis = wordwrap($texte_avis, 50, " ", 1);

		$texte_avis_2_db = addslashes($texte_avis) ;
	}


	
	// ------------------------------------------------------------
	// TEST INFORMEZ-MOI
	if (isset($_POST['avis_mailing_adresse']) AND ($_POST['avis_mailing_adresse'] == 'ok')) 
	{
		$avis_mailing_adresse = 'set';
	}

	
	// ------------------------------------------------------------
	// Test du code recopié à partir de l'image cryptée
	
	$get_sess = $_POST['sid'];

	$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	$donnees = mysql_fetch_array($reponse);
	
	if ($donnees ['code_crypt']=="" OR $donnees ['code_crypt']!=$_POST['code']) // Code non valide // (1==2) 
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
	
	
	// ------------------------------------------------------------
	// ACCEPTATION CONDITIONS GENERALES
	if (empty($_POST['conditions_gen']) OR ($_POST['conditions_gen'] != 'ok')) 
	{
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_conditions_gen = '<div class="error_form">Vous devez approuver les contions de vente
		avant de pouvoir envoyer un message</div>';
	}
	else
	{
		$conditions_gen = 'set' ;
	}
	
	
	//---------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	if ($rec == NULL) // Enregistrement les données dans la DB 
	{
		$approuv_check = mysql_query("INSERT INTO `$table_avis_agenda` 
		( `id_avis` , `event_avis` , `nom_avis` , `texte_avis` , `t_stamp_avis` , `publier_avis` , `email_avis` , `ip_avis` ) 
		VALUES ('', '$event_avis', '$nom_avis', '$texte_avis_2_db', '$t_stamp_avis', 'set', '$email_avis', '$ip_avis')")
		 or die(mysql_error());
		 
		 $dernier_id_table_avis_agenda = mysql_insert_id() ; // sera utile pour la construction de l'e-mail pour le modérateur

		 
		// Insérer l'adresse e-mail du visiteur dans la table $table_avis_mailing SI elle n'y est pas déjà
		 if ($avis_mailing_adresse == 'set')
		 {
			$reponse_avis_mailing = mysql_query("SELECT COUNT(*) AS test_exist_email FROM $table_avis_mailing 
			WHERE avis_mailing_adresse = '$email_avis' AND event_avis_mailing = '$id_event'") or die (mysql_error());
			$donnees_avis_mailing = mysql_fetch_array($reponse_avis_mailing);
	
			if ($donnees_avis_mailing['test_exist_email'] > 0) 
			{ // Déjà abonné
				echo '<div class="info">Vous recevez déjà les nouveaux avis publiés par e-mail</div>' ;
			}
			else
			{ // Pas encore abonné
				echo '<div class="info">Vous allez recevoir les prochains avis publiés par e-mail</div>' ;
				
				// code référence du USER
				$ref_user_avis_mail = str_shuffle(md5(time())); 
				$ref_user_avis_mail = substr($ref_user_avis_mail, 10, 10);
				mysql_query("INSERT INTO `$table_avis_mailing` ( `id_avis_mailing`, `event_avis_mailing`, `avis_mailing_adresse`, `ref_avis_mailing`, `numero_avis`) 
				VALUES ('', '$id_event', '$email_avis', '$ref_user_avis_mail', '$dernier_id_table_avis_agenda')") or die(mysql_error());
			}
		}
	
		
		if ($approuv_check)
		{
			$form_masquage = true; // Masquer le formulaire
			
			echo '<div class="info">Votre avis a bien été posté et est en ligne sur le site
			<br /><a href="-Agenda-">Retour au site</a>
			</div><br>' ;
			
			
			
			// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
			// Informer le modérateur par E-mail
			$mail_concat = '';
			$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml"> <head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
			<style type="text/css"> <!-- '
			. $css_email . 
			'--> </style> </head> <body> ';
			
			$mail_concat.= '<p>&nbsp;</p> <table width="500" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#EEEEEE"><tr><td>' ;
			$mail_concat.= '<p class="email_style_titre">Cher modérateur,<br /> 
			un visiteur a post&eacute; ce message &agrave; la rubrique avis : <br /></p>';
			
			$mail_concat.= '<p>' . $texte_avis . '</p> <br />';
			
			$mail_concat.= '<br /><span class="email_style_rubriques">Nom du visiteur : </span>' . $nom_avis ;
			$mail_concat.= '<br /><span class="email_style_rubriques">E-mail : </span>' . $email_avis ;
			$mail_concat.= '<br /><span class="email_style_rubriques">Ev&eacute;nement concern&eacute;: </span>' . $event_avis ;
			$mail_concat.= '<br /><span class="email_style_rubriques">Date de l\'envoi : </span>' .date('d/m/Y - h\hi', $t_stamp_avis) ;
			$mail_concat.= '<br /><span class="email_style_rubriques">ID du message : </span>' . $dernier_id_table_avis_agenda ;
			$mail_concat.= '<br /><p align="center"><br /><a href="' . $racine_domaine .
			'agenda/admin_agenda/avis_list_aprob.php#ancre' . $dernier_id_table_avis_agenda . '">
			&gt;&gt; Interface de mod&eacute;ration</a></p>' ;
			$mail_concat.= '</td></tr></table>' ;
			$mail_concat.= '</body></html>' ;
			//echo $mail_concat ;
			
			$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
			$sujet = '+ Nouveau message pour la rubrique avis (ID' . $dernier_id_table_avis_agenda . ')' ;
		 mail($email_moderateur_site,$sujet,$mail_concat,$entete,$email_retour_erreur);
			
			
			// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
			// Informer le visiteur par E-mail
			$mail_concat = '';
			$mail_concat.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml"> <head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
			<style type="text/css"> <!-- '
			. $css_email . 
			'--> </style> </head> <body> ';
			
			$mail_concat.= '<table width="550" border="0" align="center" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF"><tr><td>' ;
			
			// Logo 
			$mail_concat.= '<a href="http://www.demandezleprogramme.be">
			<img src="' . $racine_domaine . 'agenda/design_pics/logo_print.jpg" title="Visitez le site !" /></a></td>';
			
			
			$mail_concat.= '<td class="email_style_petit" align="center">Vous recevez cet e-mail 
			car vous avez envoy&eacute; un message sur le forum du site de 
			<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a>.</td></tr>';
			
			$mail_concat.= '<tr><td colspan="2" >
			<p class="email_style_titre">' . $nom_avis . ', </p> <br /> 
			<p>Nous vous remercions d&rsquo;avoir particip&eacute; &agrave; la vie du site 
			<a href="http://www.demandezleprogramme.be/">demandezleprogramme</a>  en y 
			d&eacute;posant votre avis le ' .date('d/m/Y', $t_stamp_avis) . '.</p>' ;
			
			$mail_concat.= '<p>Retrouvez votre message en cliquant <a href="' . $racine_domaine .
			'-Detail-agenda-?id_event=' . $event_avis . '">ici</a></p> <br />' ;
			
			
			$mail_concat.= '<p>A tr&egrave;s bient&ocirc;t !</p> <br />
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
			$sujet = 'Merci d\'avoir déposé votre avis sur demandezleprogramme.be' ;
		 mail($email_avis,$sujet,$mail_concat,$entete,$email_retour_erreur);
			
			
				
			// ------------------------------------------------------------
			// RECEVOIR LETTRE D'INFO DE DLP (Philippe)
			if (isset($_POST['recevoir_publication']) AND ($_POST['recevoir_publication'] == 'ok')) 
			{
				//echo '<br>|||----------- ' . $nom_avis ; // nom du visiteur
				//echo '<br>|||----------- ' . $email_avis ; // adresse e-mail du visiteur

				//----- abonner à la mailing liste DLP tous
				$adrm = addslashes($email_avis);
				$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$adrm' AND lletr='DPts' AND letat='5'";
				$resp = mysql_query($sql);
				if (! mysql_num_rows($resp)) {
					$sql = time();
					$sql = "INSERT INTO cmsnletter SET ladrm='$adrm',lletr='DPts',letat='5',lcode='$sql'";
					$resp = mysql_query($sql);
				}
				unset($adrm, $sql, $resp);
			}	
			
		}
		
		else
		{
			echo '<div class="alerte">Une erreur s\'est produite. Veuillez recommencer l\'opération ultérieurement</div><br>' ;
		}
	
	}
	else
	{
		echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
	}
}


//---------------------------------------------------------
// Si l'adresse IP est black listee, cacher le formulaire
//---------------------------------------------------------

$reponse = mysql_query("SELECT * FROM $table_black_list WHERE ip = '$ip'");
$donnees = mysql_fetch_array($reponse);

if (isset($donnees ['ip'])) // Masquer formulaire
{
	echo '<br><br><br><br><br><p class="alerte"><br>Nous sommes au regret de vous informer que 
	<b>vous avez &eacute;t&eacute; 	mis sur liste noire</b>, et que par cons&eacute;quent,
	l&rsquo;acc&egrave;s au contenu de cette page vous est refus&eacute;.<br> 
	Pour plus d&rsquo;information, prenez contact avec 	le responsable de ce service : 
	<br><img src="adresse_mail_strat.gif" width="147" height="12"><br>';
	
	
	if (isset ($donnees ['info'])and $donnees ['info'] != NULL)
	{
		echo '<b>Motivation du black listage : </b>' . $donnees ['info'];
	}
	echo '</p>';

	// INSERER JAVA POUR CLOSE WINDOW
	?>

	<script language="JavaScript">
	window.alert('Vous n\'avez plus accès à ce service');
	window.close();	</script>

	<?php
	exit() ;
}



if ($form_masquage == false )
{
	
	// ------------------------------------------------
	// Lecture des AVIS déjà en ligne
	// ------------------------------------------------
	
	// Récupération des données concernant ce spectacle
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
	$donnees_event = mysql_fetch_array($reponse);	
	
	$lieu_event = $donnees_event ['lieu_event'];
	$nom_event = $donnees_event ['nom_event'];
	
	// Recherche du nombre d'avis présents dans la table :
	$count_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE event_avis = $id_event 
	AND publier_avis = 'set'");
	$nbr_avis = mysql_fetch_array($count_avis);
	$total_entrees = $nbr_avis['nbre_entrees'];
	
	echo '<div class="detail_rubr_inc">' ;
	if (empty ($total_entrees))
	{
		echo '<h3 align="center">Vous êtes le premier à rédiger un avis.<br /> Bienvenue !</h3>' ;
	}
	else
	{
		echo '<div align="center"><h3>Il y a ' . $total_entrees . ' avis pour le spactacle : '
		. $nom_event . ' <span class="id_breve">(' . $lieu_event . ')</span></h3>
		<a href="#tous_avis_event">Voir les avis déjà postés</a></div><br />' ;
	}
	
	// Texte de mise en garde :
	echo '<div align="center" class="mini_info">&quot;Nous vous invitons ici &agrave; vous exprimer. Veillez &agrave; le faire dans un <br />langage clair et compr&eacute;hensible (pas de messages SMS) et en respectant <br />le sujet du fil de discussion, ainsi que vos interlocuteurs&quot; <br /></div><br />';
	?>
	

<!-- -----------------------------------------------------------------
// Afficher formulaire
// ----------------------------------------------------------------- -->
<form name="form1" method="post" action="">
  <table width="450" border="0" align="center" cellpadding="5" cellspacing="1" class="table_public" >
    <tr>
      <th colspan="2">Rédigez votre message</th>
    </tr>
    <tr>
      <td><?php if (isset ($error_nom_avis) AND $error_nom_avis != NULL) {echo $error_nom_avis ; } ?>
      Nom (ou pseudo) <span class="champ_obligatoire">*</span> :	  </td>
      <td><input name="nom_avis" type="text" id="nom_avis" value="<?php if (isset($nom_avis)){echo $nom_avis;}?>" size="30" maxlength="30"></td>
    </tr>
	    <tr>
      <td><?php if (isset ($error_email_avis_event) AND $error_email_avis_event != NULL) {echo $error_email_avis_event ; } ?>Adresse e-mail<span class="champ_obligatoire">*</span> : </td>
      <td><input name="email_avis" type="text" id="email_avis" value="<?php if (isset($email_avis)){echo $email_avis;}?>" size="30" maxlength="50"></td>
    </tr>
	    <tr>
	      <td colspan="2"><label><input name="avis_mailing_adresse" type="checkbox" value="ok" checked="checked" <?php if (isset($avis_mailing_adresse) AND $avis_mailing_adresse == 'set') { echo 'checked="checked"' ; } ?>/>
          Informez-moi par e-mail de l'arriv&eacute;e de nouveaux messages</label></td>
    </tr>
    <tr>
      <td colspan="2"><?php if (isset ($error_texte_avis) AND $error_texte_avis != NULL) {echo $error_texte_avis ; } ?>
	  
	  <textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 450px; height: 200px"><?php if (isset($texte_avis)){echo $texte_avis;} ?></textarea></td>
    </tr>

    <tr>
      <td colspan="2" align="center">
	  		<?php if (isset ($error_conditions_gen) AND $error_conditions_gen != NULL) {echo $error_conditions_gen ; } ?>
			<span class="champ_obligatoire">*</span> <label><input name="conditions_gen" type="checkbox" value="ok" <?php if (isset($conditions_gen) AND $conditions_gen == 'set') { echo 'checked="checked"' ; } ?>/> 
			Je d&eacute;clare accepter les 
			<a href="Mentions-legales">conditions g&eacute;n&eacute;rales</a> d'utilisation de comeavis_mailingn.be/demandezleprogramme</label></td>
    </tr>
    <tr>
      <td>
		<?php if (isset ($error_image_crypt) AND $error_image_crypt != NULL) {echo $error_image_crypt ; } ?>
		Recopier le code de l'image<span class="champ_obligatoire">*</span> : </td>
      <td><input name=code type=text id="code" size="3" maxlength="<?php echo $nb_car; ?>">
          <img src=agenda/user_admin/ins/im_gen.php?session=<?php echo $session; ?> hspace="10" align="top">
      </td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
	  <br/ >
              <input type=hidden name=sid value=<?php echo $session; ?>>
              <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
	  <br/ >
      </div></td>
    </tr>
		    <tr>
	      <td colspan="2"><label>
	      <div align="center">
            <input type="checkbox" name="recevoir_publication" value="ok" checked="checked" />
          Je souhaite recevoir la lettre d'information de <a href="http://www.demandezleprogramme.be/">demandezleprogramme.be</a></div>
	      </label></td>
    </tr>
  </table>
</form>

<p><hr /></p>
	<?php 
	// -------------------------------------------------------------
	// Affichage de tous les AVIS concernant ce spectacle
	if (!empty ($total_entrees))
	{
		$avis_concat = '<a name="tous_avis_event" id="tous_avis_event"></a>
		<br /><h3>Voici les avis précédemment postés</h3>' ;

		$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE event_avis = '$id_event' AND publier_avis = 'set' ORDER BY id_avis DESC");
		while ($donnees_avis = mysql_fetch_array($reponse_avis))
		{
			$avis_concat.= '<h4><b>' . $donnees_avis['nom_avis'] . '</b>
			<i>a écrit le ' .date('d/m/Y ', $donnees_avis ['t_stamp_avis']) . ' :</i>
			<span class="id_breve">(id  :' . $donnees_avis['id_avis'] . ')</span></h4><br />'
			. $donnees_avis['texte_avis'] . '<br /><br />' ;
		}
		
		echo $avis_concat ;
	}	
	echo '</div>';
}
?>