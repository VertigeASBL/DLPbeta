<?php 
	echo '<h3>Donnez votre avis !</h3>',"\n";
	echo '<div class="detail_rubr_inc">',"\n";

// le session_start(); est dans squelette rubrique=97
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_var_dist_local.php';
require 'agenda/inc_fct_base.php';
require 'agenda/user_admin/ins/inc_var_inscription.php';

// Fonction panier /-- Didier
include_once('agenda/panier/fonctions_panier.php');

$avis_ok_masquer_formulaire = false ; // Masquer le formulaire pour ne pas que le visiteur ne reposte l'avis

// --------------------------------------------------------------------------------
// Le rédacteur est-il un SPECTATEUR authentifié ou un simple visiteur ?
// --------------------------------------------------------------------------------
if (isset($_SESSION['group_admin_spec']) AND $_SESSION['group_admin_spec'] == 1)
{
	$qui_redacteur = 'spectateur' ; // Le joueur est un SPECTATEUR authentifié
	$id_spectateur = $_SESSION['id_spectateur'] ;
	$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
	$donnees = mysql_fetch_array($reponse);
		
	$prenom_spectateur = $donnees['prenom_spectateur'];
	$nom_spectateur = $donnees['nom_spectateur'];
	$pseudo_spectateur = $donnees['pseudo_spectateur'];
	$e_mail_spectateur = $donnees['e_mail_spectateur'];
	$tel_spectateur = $donnees['tel_spectateur'];
	$log_spectateur = $donnees['log_spectateur'];
	$pw_spectateur = $donnees['pw_spectateur'];

	$description_courte_spectateur = $donnees['description_courte_spectateur'];
	$description_longue_spectateur = $donnees['description_longue_spectateur'];

	$pic_spectateur = $donnees['pic_spectateur'];

	$artiste_prefere_spectateur = $donnees['artiste_prefere_spectateur'];
	$lieu_prefere_spectateur = $donnees['lieu_prefere_spectateur'];
	$avis_valides_spectateur = $donnees['avis_valides_spectateur'];

	
	
	// Si le compte est bloqué, empêcher le spectateur d'y accéder
	if ($donnees['compte_actif_spectateur'] == 'non')
	{
		echo '<br /> <div class="alerte"><br />Votre comte a été bloqué par un administrateur du site. <br />Vous ne pouvez plus participer en tant que Spectateur. Pour plus d\'infos : info@demandezleprogramme.be<br /><br /></div>' ;
		exit () ;
	}
	
	// Si le compte est créé, mais pas totalement complété ($compte_actif_spectateur = "new"), inviter le Spectateur à finaliser la chose via sin admin
	if ($donnees['compte_actif_spectateur'] == 'new')
	{
		echo '<br /> <div class="alerte"><br />Votre comte n\'est pas encore totalement paramétré. Veuillez vous rendre dans votre espace d\'administration personnel et compléter votre profil : 
		<a href="agenda/spectateurs_admin/edit_profile_spectateur.php">espace personnel</a>.<br />
		Ensuite, il vous sera possible de participer aux concours tout en multipliant vos chances de gain !<br /><br /></div>' ;
		exit () ;
	}
}
else
{
	$qui_redacteur = 'visiteur' ; // Le joueur est un simple visiteur

}
$max = 1800 ; // Longueur max du texte (en nombre de caractères) que le visiteur peut poster
$allowedTags = '<br><br />'; // Balises de style que les visiteurs peuvent employer




//LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL
if (empty($_GET['id_event']) OR $_GET['id_event'] == NULL )
{
	$id_event = 0;
	$_GET['mode_avis'] = '';
?>
<script type="text/javascript">
$(document).ready(function(){
	var numeroajax = 0;

	function appel_php() {
		$('#event_preview_id_fleche').show();
		valeur_txt_libre_recup = $("#chp_txt_libre").val();

		$.post("agenda/moteur_2_3/requete_utf8/requete_home.php", {
			chaine_txt_libre: ""+valeur_txt_libre_recup+"",
			url_self: ""+window.location.search+"" //window.location.hostname+window.location.pathname+
		}, function(data){
			if (numeroajax > 1)
				{ numeroajax--; return; }
			var response_se = eval("(" + data + ")");
			// Nombre de résultats
			if (valeur_txt_libre_recup == "")
				$('#event_preview_id_fleche').hide();
			else
				if (response_se.nombre_resultats>0)
					$('#event_preview_id').html(response_se.preview_event+"<br /> <br /> ");
				else
					$('#event_preview_id').html("Aucun résultat");
			numeroajax--;
		},"json");
	}
	// TEST CHP LIBRE
	$("#chp_txt_libre").keyup(function() {
		chaine_txt_libre = $(this).val();
		// setTimeout = retarder une action
		numeroajax++;
		setTimeout(function attendre_un_peu() { appel_php(); }, 500);
	});

	$("#event_preview_close").click(function() { $('#event_preview_id_fleche').hide(); });
});
</script>

	<form id="form_moteur_avis" method="get" action="#">
	<h3>Choississez votre spectacle :</h3><input name="chp_txt_libre" type="text" size="30" value="" id="chp_txt_libre" />
	<div id="event_preview_cont">
		<div id="event_preview_id_fleche" style="display:none;">
			<div id="event_preview_close"></div>
			<div id="event_preview_id"></div>
		</div>
    </div>
	</form>
<?php
}
else
{
	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
	$event_avis = htmlentities($_GET['id_event'], ENT_QUOTES);
}
//LLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLL

$avis_mailing_adresse = '' ;

$session = md5(time()); // numero d'identification du visieur
$ip_avis = $_SERVER['REMOTE_ADDR'] ;
$t_stamp_avis = time();

// code aléatoire pour l'image generee :
$nb_car = 3 ;
$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
$txt = str_shuffle($txt);
$code = substr($txt, 10, $nb_car);

mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) VALUES ('$session','$code','$t_stamp_avis','$ip_avis')");

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
		
		// Tester si le nom est le pseudo d'un SPECTATEUR, s'il est loggé, OK, sinon, refuser
		if (empty ($_SESSION['group_admin_spec']) OR $_SESSION['group_admin_spec'] == NULL)
		{
			$reponse_test_nom_spect = mysql_query("SELECT id_spectateur FROM $table_spectateurs_ag WHERE pseudo_spectateur = '$nom_avis'");
			$donnees_test_nom_spect = mysql_fetch_array($reponse_test_nom_spect);
			if ($donnees_test_nom_spect['id_spectateur'] != NULL)
			{
				$error_nom_avis = '<div class="error_form">Vous utilisez le pseudonyme d\'un spectateur enregistré sur le site. 
				S\'il s\'agit de vous, veuillez vous authentifier via <a href="' . $racine_domaine . 'agenda/spectateurs_admin/votre_menu_spectateur.php">cette page</a>.</div>' ;
				$rec .= '- Vous utilisez le pseudonyme d\'un spectateur enregistré sur le site. <br>';
				$nom_avis = '' ;
			}
		}
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
		
		// Tester si l'adresse email appartient au SPECTATEUR, s'il est loggé, OK, sinon, refuser
		if (empty ($_SESSION['group_admin_spec']) OR $_SESSION['group_admin_spec'] == NULL)
		{
			$reponse_test_email_spect = mysql_query("SELECT id_spectateur FROM $table_spectateurs_ag WHERE e_mail_spectateur = '$email_avis'");
			$donnees_test_email_spect = mysql_fetch_array($reponse_test_email_spect);
			if ($donnees_test_email_spect['id_spectateur'] != NULL)
			{
				$error_email_avis_event = '<div class="error_form">Vous utilisez une adresse email appartenant 
				à un spectateur enregistré sur le site. 
				S\'il s\'agit de vous, veuillez vous authentifier via <a href="' . $racine_domaine . 'agenda/spectateurs_admin/votre_menu_spectateur.php">cette page</a>.</div>' ;
				$rec .= '- Vous utilisez une adresse email appartenant à un spectateur enregistré sur le site. <br>';
			}
		}
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
	
	if ($donnees['code_crypt']=="" OR $donnees['code_crypt']!=$_POST['code']) // Code non valide // (1==2) 
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
		$rec .= '- Vous devez approuver les contions d\'utilisation avant de pouvoir envoyer un message<br>';
		$error_conditions_gen = '<div class="error_form">Vous devez approuver les contions d\'utilisation
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
		$avis_ok_masquer_formulaire = true ; // On peut masquer le formulaire pour ne pas que le visiteur ne reposte l'avis

		/* Didier => En cas de changement de niveau on log pour l'afficher sur l'espace public. */
		$avant_avis = trouve_categorie_spectateur($avis_valides_spectateur);
		$apres_avis = trouve_categorie_spectateur($avis_valides_spectateur+1);

		/* On teste s'il y a un changement de niveau après l'ajout de l'avis de la personne. Si oui, on enregistre le changement de niveau */
		if ($avant_avis['categorie_spectateur'] != $apres_avis['categorie_spectateur']) {
/* prov richir
			include_once('agenda/activite/activite_fonctions.php');
			activite_log ('level');
*/
		}

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
		 mail_beta($retour_email_admin,$sujet,$mail_concat,$entete,$email_retour_erreur);
			
			
			// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
			// Informer le visiteur par E-mail ==> supprimé le 17-12-2010
			
			
				
			// ------------------------------------------------------------
			// RECEVOIR LETTRE D'INFO DE DLP (Philippe)
			if (isset($_POST['recevoir_publication']) AND ($_POST['recevoir_publication'] == 'ok')) 
			{
				//echo '<br>|||----------- ' . $nom_avis ; // nom du visiteur
				//echo '<br>|||----------- ' . $email_avis ; // adresse e-mail du visiteur

				//----- abonner à la mailing liste DLP tous
				$adrm = addslashes($email_avis);
				$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$adrm' AND lletr='DPts' AND letat='5'";
				$resp = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
				if (! mysql_num_rows($resp)) {
					$sql = time();
					$sql = "INSERT INTO cmsnletter SET ladrm='$adrm',lletr='DPts',letat='5',lcode='$sql'";
					$resp = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
				}
				unset($adrm, $sql, $resp);
			}	

/* prov richir
			include_once('agenda/activite/activite_fonctions.php');
			activite_log ('avis', $id_event);
*/
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

$reponse = mysql_query("SELECT * FROM $table_black_list WHERE ip = '$ip_avis'");
$donnees = mysql_fetch_array($reponse);

if (isset($donnees['ip'])) // Masquer formulaire
{
	echo '<br><br><br><br><br><p class="alerte"><br>Nous sommes au regret de vous informer que 
	<b>vous avez &eacute;t&eacute; 	mis sur liste noire</b>, et que par cons&eacute;quent,
	l&rsquo;acc&egrave;s au contenu de cette page vous est refus&eacute;.<br> 
	Pour plus d&rsquo;information, prenez contact avec 	le responsable de ce service : 
	<br><img src="adresse_mail_strat.gif" width="147" height="12"><br>';
	
	
	if (isset ($donnees['info'])and $donnees['info'] != NULL)
	{
		echo '<b>Motivation du black listage : </b>' . $donnees['info'];
	}
	echo '</p>';
	$avis_ok_masquer_formulaire = true;

	// INSERER JAVA POUR CLOSE WINDOW
?>
	<script language="JavaScript">
	window.alert('Vous n\'avez plus accès à ce service');
	window.close();	</script>
<?php
}




// ------------------------------------------------
// ------------------------------------------------
// Récupération des données concernant ce spectacle
// ------------------------------------------------
// ------------------------------------------------
if ($avis_ok_masquer_formulaire==false)
{
	$reponse = mysql_query("SELECT E.*,L.nom_lieu,L.email_reservation FROM $table_evenements_agenda E LEFT JOIN ag_lieux L ON E.lieu_event = L.id_lieu WHERE id_event = '$id_event'");
	if ($donnees_event = mysql_fetch_array($reponse)) {

		//------------- debut afficher resultat -----------------
		$tab = '<div class="resultat_parent">'."\n";

		$id_event = (int) $donnees_event['id_event'];

		// ____________________________________________
		// VIGNETTE EVENEMENT	
		if ($donnees_event['pic_event_1'] == 'set' )
		{
			$nom_event = htmlspecialchars($donnees_event['nom_event']);
			$id_event = $donnees_event['id_event'];
			$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'"><img src="agenda/' . $folder_pics_event . 'event_' . $id_event . '_1.jpg" class="onglet6 pic_parent" title="' . $nom_event . '" alt="" /></a>'."\n";
		}

		$tab.= '<div class="res_droite_parent">'."\n";
	
		// ____________________________________________
		// ICONES FLOTTANTES (au niveau du titre)
		$tab.= '<div class="icodr_parent"><div class="icodr_parent_g"></div>'."\n";
		$tab.= '<span class="ico_float_droite_relative">'."\n";

/*		// Icone suivre - Modifier par Didier
		if (!empty($_SESSION['id_spectateur'])) {
			if (!statut_panier($_SESSION['id_spectateur'], $id_event)) $tab.= '<a href="?id_event='.$id_event.'&suivre=1" title="suivre" style="float:right;">Suivre ('.nombre_suivi($id_event).')</a> &nbsp; '."\n";
			else $tab.= '<a href="?id_event='.$id_event.'&plus_suivre=1" title="Ne plus suivre" style="float:right;">Ne plus suivre ('.nombre_suivi($id_event).')</a> &nbsp; '."\n";
		}
*/
		// Icone concours
		$reponse_2 = mysql_query("SELECT id_conc FROM ag_conc_fiches WHERE event_dlp_conc=$id_event AND flags_conc='actif' ORDER BY id_conc DESC LIMIT 1");
		if ($total_entrees = mysql_fetch_array($reponse_2))
			$tab.= '<a href="'.generer_url_entite(95, 'rubrique', 'id='.$total_entrees['id_conc']).'" class="ico_droite icodr_concours" title="Voir le concours"></a>'."\n";
	
		// Vos Avis : compter le nbre d'entrées :
		$t_saison_preced = saisonprecedente($id_event, 'avis');
		$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM '.$table_avis_agenda.' WHERE event_avis IN ('.$t_saison_preced.') AND publier_avis=\'set\'');
		$total_entrees = mysql_fetch_array($count_avis);
		$total_entrees = $total_entrees['total_entrees'];
		if ($total_entrees > 0)
			$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#avis" class="ico_droite icodr_avis" title="Nombre d\'avis postés par les visiteurs">'.$total_entrees.'</a>'."\n";
		
		// Icone Critique
		if ($donnees_event['critique_event'] != 0)
			$critique_event = $donnees_event['critique_event'];
		else
			$critique_event = saisonprecedente($id_event, 'critique');
		if ($critique_event)
			$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#critique" class="ico_droite icodr_critique" title="Lire la critique"></a>'."\n" ;
	
		// Icone chronique
		if ($donnees_event['chronique_event'] != 0)
			$chronique_event = $donnees_event['chronique_event'];
		else
			$chronique_event = saisonprecedente($id_event, 'chronique');
		if ($chronique_event)
			$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#chronique" class="ico_droite icodr_chronique" title="Lire la chronique"></a>'."\n" ;
	
		// Icone Interview
		if ($donnees_event['interview_event'] != 0)
			$interview_event = $donnees_event['interview_event'];
		else
			$interview_event = saisonprecedente($id_event, 'interview');
		if ($interview_event)
			$tab.= '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" class="ico_droite icodr_interview" title="Lire l\'interview"></a>'."\n" ;
	
		// Icone "J'ai vu et aimé"
		$t_saison_preced = saisonprecedente($id_event, 'jai_vu');
		$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM ag_jai_vu WHERE id_event_jai_vu IN ('.$t_saison_preced.')');
		$total_entrees = mysql_fetch_array($count_avis);
		$total_entrees = $total_entrees['total_entrees'];
		$tab.= '<span class="ico_droite icodr_jaivu" title="Nombre de votes pour cet événement">'.$total_entrees.'</span>'."\n" ;
	
		// Icone suivi - Modifier par Didier
		$tab.= '<span class="ico_droite icodr_suivi" title="Nombre de suivis de cet événement">'.nombre_suivi($id_event).'</span>'."\n";
	
		//--- fin ICONES FLOTTANTES
		$tab.= '<div class="icodr_parent_d"></div></div>'."\n";

		// ____________________________________________
		// NOM EVENEMENT
		$nom_event = monraccourcirchaine($donnees_event['nom_event'], 45);
		if ($requete_txt != '' AND $requete_txt != 'nom de l\'événement' AND stristr ($nom_event, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse
		{
			$pattern = "!$requete_txt!i" ;
			$souligne = '<span class="souligne">' . $requete_txt .'</span>';
			$nom_souligne = preg_replace($pattern, $souligne, $nom_event);
			
			$tab.= '<div class="breve_titre"><a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'" title="Voir en détail">' . $nom_souligne . '</a></div>'."\n";
		}
		else
		{
			$tab.= '<div class="breve_titre"><a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'" title="Voir en détail">' . $nom_event . '</a></div>'."\n";
		}

		// ____________________________________________
		// ID
		$tab.= ' <span class="id_breve">(id ' . $donnees_event['id_event'] . ')</span><br />'."\n" ;

		// ____________________________________________
		// LIEU
		$tab.= '<span class="breve_lieu"><a href="'.generer_url_entite(96, 'rubrique', 'id_lieu='.$donnees_event['lieu_event']).'" title="Producteur du spectacle">'.raccourcir_chaine($donnees_event['nom_lieu'], 35).'</a></span>'."\n";
	
		// ____________________________________________
		// GENRE
		if ($donnees_event['genre_event'] != NULL) 
		{
			$genre_name = $donnees_event['genre_event'];
			$tab.= '| <span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . '</acronym></span>'."\n";	
		}
	
		// ____________________________________________
		// DATES
		$date_event_debut = $donnees_event['date_event_debut'];	
		$date_event_debut_annee = substr($date_event_debut, 0, 4);
		$date_event_debut_mois = substr($date_event_debut, 5, 2);
		$date_event_debut_jour = substr($date_event_debut, 8, 2);
		
		$date_event_fin = $donnees_event['date_event_fin'];
		$date_event_fin_annee = substr($date_event_fin, 0, 4);
		$date_event_fin_mois = substr($date_event_fin, 5, 2);
		$date_event_fin_jour = substr($date_event_fin, 8, 2);
	
		// note : pour mois en LETTRES : $NomDuMois[$date_event_debut_mois+0]
		$tab.= '| <span class="breve_date"><acronym title="Période de représentation">du ' . $date_event_debut_jour . '/'
		. $date_event_debut_mois . '/'
		. $date_event_debut_annee . ' au ' . $date_event_fin_jour . '/'
		. $date_event_fin_mois . '/'
		. $date_event_fin_annee . '</acronym></span>'."\n";	
	
		// ____________________________________________
		// VILLE
		if ($donnees_event['ville_event'] != NULL) 
		{
			$ville_event_de_db = $donnees_event['ville_event'];
			$tab.= '| <span class="breve_ville"><acronym title="Ville où du spectacle">' . $regions[$ville_event_de_db] .'</acronym></span>'."\n";	
		}

		// ____________________________________________
		// TEXTE RESUME 
		// Afficher texte résumé et événtuellement souligner le mot rechercé par l'utilisateur
		$tab.= '<p class="breve_resume">'.raccourcir_chaine(strip_tags($donnees_event['resume_event']), 400).'</p>'."\n";

		$tab.= '<div class="en_savoir_plus">'."\n" ;
		// Icone suivre - Modifier par Didier
		if (!empty($_SESSION['id_spectateur'])) {
			if (!statut_panier($_SESSION['id_spectateur'], $id_event))
				$tab.= '<a href="?id_event='.$id_event.'&suivre=1" class="ico_gauche icoga_suivre" title="Suivre cet événement">Ajouter à mon agenda</a>'."\n";
			else
				$tab.= '<a href="?id_event='.$id_event.'&plus_suivre=1" class="ico_gauche icoga_suivre" title="Ne plus suivre cet événement">Retirer de mon agenda</a>'."\n";
	
			$tab.= '<a href="#voter" class="ico_gauche icoga_voter" onclick="popup_jai_vu(\'agenda/jai_vu/jai_vu_popup.php?id='.$id_event.'\',\'Votons\'); return false;" title="Voter pour cet événement">J\'ai vu et aimé</a>'."\n" ;
		}
		// Lien "en savoir plus"
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'">En savoir plus &#187;</a>'."\n";
		$tab.= '</div>'."\n";
	
		$tab.= '</div>'."\n"; //--- fin res_droite_..
		$tab.= '<div class="float_stop"></div>'."\n".'</div>'."\n\n";
		echo $tab ;
		//------------- fin afficher resultat, fin div breve -----------------
	}
	else
		echo '<br /><br />',"\n";
	
	// ------------------------------------------------
	
	// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
	// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
	// 1/ Le spectateur est déja connecté
	// Afficher formulaire AVEC champs préremplis
	// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
	if ($qui_redacteur == 'spectateur')
	{
	
		// Photo et nom du spectateur
		echo '<table class="pub" style="background-color: #D9D9D9" width="500" border="0" align="center" cellpadding="10" cellspacing="0" bordercolor="#000000" >
		  <tr>
			<td>';
			if (isset ($donnees['pic_spectateur']) AND $donnees['pic_spectateur'] == 'set' )
			{
				echo '<img src="agenda/' . $folder_pics_spectateurs . 'spect_' . $id_spectateur . '_1.jpg" alt="Photo de ' . $prenom_spectateur . ' ' . $nom_spectateur . '" title="' . $prenom_spectateur . ' ' . $nom_spectateur . '" />';
			}
			else
			{
				if ($donnees['sexe_spectateur'] == 0)
				{
					echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_homme.jpg" alt="spectateur anonyme" />';
				}
				else
				{
					echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_femme.jpg" alt="spectatrice anonyme" />';
				}
			}
			
			echo '
			</td>
			  <td>';
			$_tot_entrees = connaitre_nb_avis_spect ($pseudo_spectateur) ;
		
			
			// Correspondance AVIS postés <-> Grade et icone des spectateurs
			$result_categorie_spectateur = trouve_categorie_spectateur ($_tot_entrees) ; 
					
			$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
					
			echo '<p>Vous allez déposer un avis sous votre pseudo <strong>' . $pseudo_spectateur . '</strong> puisque vous vous êtes identifié' ;
			($sexe_spectateur)?($terminaison='e'):($terminaison='');
			echo $terminaison . ', ce qui sera comptabilisé pour votre « coefficient concours » dès que votre avis sera validé.</p>' ;
			
			
			echo '</td>
		  </tr>
		</table>
		<br />';
		
		require 'ecrire_avis_formulaire.php';
	}
	else
	{
		
		// 2/ Le spectateur n'est pas encore connecté
		if (isset($_GET['mode_avis']) AND $_GET['mode_avis'] == 'sans' )
		{
			require 'ecrire_avis_formulaire.php';
		}
		else
		{
			echo '<table style="background-color: #D9D9D9" width="500" border="0" align="center" cellpadding="10" cellspacing="0" bordercolor="#000000" >
			<tr><td valign="top">';
		
			// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
			// 2.1/ Je dépose un avis sans compte spectateur
			// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
			echo '- Je dépose un avis <a href="?id_event=' . $id_event . '&amp;mode_avis=sans">
		sans compte spectateur</a><br /> <br />' ;
		
			
			// 2.2/ Me connecter à mon compte spectateur avant de déposer un avis (et ainsi gagner des points)
				echo '<div id="open_form_log_dlp">Me connecter à mon compte spectateur</div> avant de déposer un avis.<br /> <br />
				' ;
		
			
			// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
			// 2.3/ Je crée compte spectateur maintenant
			// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
			echo '- <a href="-Inscription-spectateur-">Je crée mon compte spectateur</a> maintenant, je reviendrai ensuite écrire mon avis<br /> <br />' ;
			
			// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
			// 2.4/ J'ai oublié mon mot de passe
			// wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwww
			echo '- J\'ai oublié mon mot de passe, <a href="-Mot-de-passe-oublie-">je le récupère</a> et je reviendrai  écrire mon avis' ;
		
		echo '</tr></table>';
		
		}
	}

	// -------------------------------
	// Texte de mise en garde :
	// -------------------------------
	echo '<br /><div align="center" class="mini_info">&quot;Nous vous invitons ici &agrave; vous exprimer. Veillez &agrave; le faire dans un <br />langage clair et compr&eacute;hensible (pas de messages SMS) et en respectant <br />le sujet du fil de discussion, ainsi que vos interlocuteurs&quot; <br /></div><br />';
	
	
	// -------------------------------
	// Recherche du nombre d'avis présents dans la table :
	// -------------------------------
	$t_saison_preced = saisonprecedente($id_event, 'avis');
	$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM '.$table_avis_agenda.' WHERE event_avis IN ('.$t_saison_preced.') AND publier_avis=\'set\'');
	$total_entrees = mysql_fetch_array($count_avis);
	$total_entrees = $total_entrees['total_entrees'];
	if (empty ($total_entrees))
	{
		echo '<div align="center">Vous êtes le premier à rédiger un avis.<br /> Bienvenue !</div>' ;
	}
	else
	{
		echo '<div align="center">Il y a ' . $total_entrees . ' avis pour ce spectacle 
		<a href="#tous_avis_event"><br />&gt; &gt; Voir les avis déjà postés</a></div>' ;
	}

	// -------------------------------------------------------------
	// Affichage de tous les AVIS concernant ce spectacle
	if ($total_entrees)
	{
		$avis_concat = '<a name="tous_avis_event" id="tous_avis_event"></a>
		<h3>Voici les avis précédemment postés</h3>' ;

		$reponse_avis= mysql_query("SELECT * FROM $table_avis_agenda WHERE event_avis IN ($t_saison_preced) AND publier_avis='set' ORDER BY id_avis DESC");
		while ($donnees_avis = mysql_fetch_array($reponse_avis))
		{
			$avis_concat.= '<h4><b>' . $donnees_avis['nom_avis'] . '</b>
			<i>a écrit le ' .date('d/m/Y ', $donnees_avis['t_stamp_avis']) . ' :</i>
			<span class="id_breve">(id  :' . $donnees_avis['id_avis'] . ')</span></h4><br />'
			. $donnees_avis['texte_avis'] . '<br /><br />' ;
		}
		
		echo $avis_concat ;
	}
} //fin condition de masquage formulaire  ($avis_ok_masquer_formulaire)

	echo '</div>',"\n"; //--- fin detail_rubr_inc
?>
