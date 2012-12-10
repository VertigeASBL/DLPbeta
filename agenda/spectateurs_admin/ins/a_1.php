<?php 
echo '<h2>Création d\'un compte "spectateur"</h2>';

session_start();

include_spip('inc/utils');

	//--- changer le dossier de travail courant
	$dossier_courant = getcwd();
	chdir(dirname(__FILE__));

require '../../inc_var.php';
require '../../inc_db_connect.php';
require 'inc_var_inscription.php';
require '../../inc_fct_base.php';
require '../../fct_uploader_vignette_spectateur.php';

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Inscription d'un SPECTATEUR
/* Le spectateur choisit ici son PSEUDO définitif. Il doit ensuite éditer son compte via "edit_profile_spectateur.php".
Une fois édité, le Flag "compte_actif_spectateur" passe de "new" à "oui", et il peut poster des messages. Si l'Administrateur désactive le compte, le flag passe à "non"
*/
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

// ---------------------------------------------------------
// Initialisation de variables
// ---------------------------------------------------------
require '../../inc_var_dist_local.php';

$form_masquage = false ; // Rendre visible le formulaire

$session = md5(time()); // numero d'identification du visieur
$ip = $_SERVER['REMOTE_ADDR'] ;
$timestamp = time();

// code aleatoire pour l'image generee :
$nb_car = 4 ;
$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
$txt = str_shuffle($txt);
$code = substr($txt, 10, $nb_car);

mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) VALUES ('$session','$code','$timestamp','$ip')");

$s_pw = str_shuffle($code); // code pour la confirmation par e-mail


//--------------------------------------------------------------------------------------------------------------
// Test des données
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer'))
{
	//$id = $_SESSION['id_spectateur'] ;

	//-----------------------------------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------
	// = initialisation de la var qui sera testée avant d'enregistrer les données dans la DB
	// Si elle est vide => enregistrer. Sinon, elle contient le message d'erreur, et on l'affiche.
	$rec = '';
	
	
	// -----------------------------------------
	// TEST DU PSEUDO DU SPECTATEUR
	if (isset($_POST['pseudo_spectateur']) AND ($_POST['pseudo_spectateur'] != NULL)) 
	{
		$pseudo_spectateur = trim(htmlentities($_POST['pseudo_spectateur'], ENT_QUOTES)); 
	
		// Tester si le PSEUDO existe déjà dans les 2 TABLES ?			
		// - "$table_spectateurs_ag"
		$req_doublon_pseudo_spectateur = mysql_query("SELECT pseudo_spectateur FROM $table_spectateurs_ag 
		WHERE pseudo_spectateur = '$pseudo_spectateur' ");
		$result_doublon_pseudo_spect = mysql_fetch_array($req_doublon_pseudo_spectateur);

		// - "$table_avis_agenda"
		$req_doublon_nom_avis = mysql_query("SELECT nom_avis FROM $table_avis_agenda 
		WHERE nom_avis = '$pseudo_spectateur' ");
		$result_doublon_nom_avis = mysql_fetch_array($req_doublon_nom_avis) ;
		
		if (isset($result_doublon_pseudo_spect['pseudo_spectateur']) OR isset($result_doublon_nom_avis['nom_avis']))
		{
			$rec .= '- Le pseudo que vous avez choisi ('.$pseudo_spectateur.') existe déjà dans notre base de données. 
			Veuillez en introduire un autre<br>';
			$error_doublon_pseudo = '<div class="error_form">Le pseudo que vous avez choisi ('.$pseudo_spectateur.') 
			existe déjà dans notre base de données. Veuillez en introduire un autre</div>';
			$pseudo_spectateur = '';
		}
	}
	else
	{
		$pseudo_spectateur = '';
		$rec .= '- Vous devez introduire votre pseudo. Il servira de signature à vos messages<br>';
		$error_pseudo_spectateur = '<div class="error_form">Vous devez introduire votre pseudo. Il servira de signature à vos messages</div>';
	}
	
	
	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail_spectateur']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_spectateur']))))
	{
		$e_mail_spectateur = $_POST['e_mail_spectateur'];
		// Test doublon de adresse email
		$req_doublon = mysql_query("SELECT e_mail_spectateur FROM $table_spectateurs_ag 
		WHERE e_mail_spectateur = '$e_mail_spectateur' ");
		$email_doublon = mysql_fetch_array($req_doublon);
		if (isset($email_doublon['e_mail_spectateur']))
		{
			$rec .= '- L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est déjà présente dans notre base de données. 
			Veuillez en introduire une autre.<br />';

			$error_email_doublon = '- <div class="error_form">L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est déjà présente dans notre base de données.</div> 
			Veuillez en introduire une autre.<br />';

			$e_mail_spectateur = '<div class="error_form">L\'adresse e-mail que vous avez introduite ('.$e_mail_spectateur.') 
			est déjà présente dans notre base de données. Veuillez en introduire une autre.</div>';
			
			$e_mail_spectateur = '';
		}
		else
		{
			//mysql_query("UPDATE `$table_spectateurs_ag` SET `e_mail_spectateur` = '$e_mail_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}
	}
	else
	{
		$e_mail_spectateur = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_e_mail = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
	}
	

	// -----------------------------------------
	// TEST DU LOGIN
	if (isset($_POST['log_spectateur']) AND ($_POST['log_spectateur'] != NULL)) 
	{
		$log_spectateur = htmlentities($_POST['log_spectateur'], ENT_QUOTES);
		
		// Tester si le LOGIN existe déjà dans la DB ?			
		$req_existe = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE log_spectateur = '$log_spectateur' ");
		$user_existe = mysql_fetch_array($req_existe);
		
		if (isset($user_existe['log_spectateur']))
		{
			$rec .= '- Le LOGIN ('.$log_spectateur.') a déjà été choisi. Veuillez en introduire un autre<br>';
			$error_log = '<div class="error_form">Le LOGIN ('.$log_spectateur.') a déjà été choisi. Veuillez en introduire un autre</div>';
			$log_spectateur = '' ;
		}
		
		elseif (!preg_match('`^\w{4,8}$`', $log_spectateur)) // \w classe prédéfinie utilisée avec les PCRE et déterminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
		{
			$rec .= '- Votre LOGIN doit contenir entre 4 et 8 caracteres alphanumeriques <br>';
			$error_log = '<div class="error_form">Votre LOGIN doit contenir entre 4 et 8 caracteres alphanumeriques</div>';
			$log_spectateur = '' ;
		}
		
		else
		{
			//mysql_query("UPDATE `$table_spectateurs_ag` SET `log_spectateur` = '$log_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}	
	}
	else
	{
		$log_spectateur = '';
		$rec .= '- Vous devez introduire un LOGIN <br>';
		$error_log = '<div class="error_form">Vous devez introduire un LOGIN</div>';
	}
	
	
	// -----------------------------------------
	// TEST PW

	if (isset( $_POST['pw_spectateur']) AND $_POST['pw_spectateur'] != NULL )  
	{		
		
		$pw_spectateur = htmlentities($_POST['pw_spectateur'], ENT_QUOTES);
		$pw_spectateur_double = htmlentities($_POST['pw_spectateur_double'], ENT_QUOTES);

		if (!preg_match('`^\w{4,8}$`', $pw_spectateur)) // \w classe prédéfinie utilisée avec les PCRE et déterminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
		{
			$rec .= '- Votre MOT DE PASSE doit contenir entre 4 et 8 caracteres alphanumeriques <br>';
			$error_pw = '<div class="error_form">Votre MOT DE PASSE doit contenir entre 4 et 8 caracteres alphanumeriques</div>';
		}
		
		elseif ($pw_spectateur_double == NULL OR !preg_match('`^\w{4,8}$`', $pw_spectateur_double)) 
		{
			$rec .= '- Vous devez confirmer le mote de passe <br>';
			$error_conf_pw = '<div class="error_form">Vous devez confirmer le mote de passe</div>';
		}
		
		elseif ($pw_spectateur_double != $pw_spectateur) 
		{
		$rec .= '- Le mot de passe confirmé ne correspond pas <br>';
				$error_conf_pw = '<div class="error_form">Le mot de passe confirmé ne correspond pas</div>';
		}
		else
		{
			//mysql_query("UPDATE `$table_spectateurs_ag` SET `pw_spectateur` = '$pw_spectateur' WHERE `id_spectateur` = '$id' LIMIT 1 ");
		}
	}
	else
	{
		$pw_spectateur = '';
		$rec .= '- Vous devez introduire un mot de passe <br>';
		$error_pw = '<div class="error_form">Vous devez introduire un mot de passe</div>';
	}


	
	// ------------------------------------------------------------
	// Test du code recopié à partir de l'image cryptée
	if (isset ($_POST['sid']) AND $_POST['sid'] != NULL)
	{
		$get_sess = htmlentities($_POST['sid'], ENT_QUOTES);
	}
	else
	{
		$get_sess = '';
	}

	$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	$donnees = mysql_fetch_array($reponse);
	
	if ($donnees ['code_crypt']=="" OR $donnees ['code_crypt']!=$_POST['code']) // Code non valide
	{
		$code = '';
		$rec .= '- Le code que vous avez recopié à partir de l\'image est incorrect <br>';
		$erreur_code = '<div class="error_form">Le code que vous avez recopié à partir de l\'image est incorrect</div><br>';
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
		$rec .= '- Vous devez approuver les contions d\'utilisation	avant de pouvoir envoyer un message<br>';
		$error_conditions_gen = '<div class="error_form">Vous devez approuver les contions d\'utilisation
		avant de pouvoir envoyer un message</div>';
	}
	else
	{
		$conditions_gen = 'set' ;
	}
	
	

	//---------------------------------------------------------
	// Enregistrement les données dans la DB 
	//---------------------------------------------------------
	if ($rec == NULL) // Tous les champs du formulaire sont correctement remplis
	{	

		mysql_query("INSERT INTO `$table_spectateurs_ag` ( `nom_spectateur` , `prenom_spectateur`, `pseudo_spectateur` , `e_mail_spectateur` , `tel_spectateur` , `log_spectateur` , `pw_spectateur` , `compte_actif_spectateur`) 
		VALUES ('$pseudo_spectateur', '', '$pseudo_spectateur', '$e_mail_spectateur', '', '$log_spectateur', '$pw_spectateur', 'new')");

		$valeur_id_spect_inscrit = mysql_insert_id() ; // Utile pour créer le lien de l'admin après	 
		
		// richir : inscrire aux listes de diffusion, récupérer les adresses emails et noms... qui sont dans les variables de la requête juste au dessus
		$adrm = addslashes($e_mail_spectateur);
		$maint = time();
		$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$adrm' AND lletr='DPsp' AND letat='5'";
		$query = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		if (! mysql_num_rows($query)) {
			$sql = "INSERT INTO cmsnletter SET ladrm='$adrm',lletr='DPsp',letat='5',lcode='$maint'";
			$query = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		}
		$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$adrm' AND lletr='DPts' AND letat='5'";
		$query = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		if (! mysql_num_rows($query)) {
			$sql = "INSERT INTO cmsnletter SET ladrm='$adrm',lletr='DPts',letat='5',lcode='$maint'";
			$query = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		}
		unset($adrm, $sql, $query, $maint);

		//---------------------------------------------------------
		// Envoi de l'e-mail avec codes au SPECTATEUR
		//---------------------------------------------------------
		$adresse = $e_mail_spectateur;
		$sujet=" -- Votre compte SPECTATEUR sur demandezleprogramme.be";
		$corps="Bonjour " . html_entity_decode($pseudo_spectateur).", 

Votre compte de spectateur affilié à l'agenda de demandezleprogramme.be a bien été créé. 
Pour l'activer, rendez-vous sur cette page : ".generer_url_entite_absolue(157,'rubrique').'
Pour vous connecter à votre compte, utilisez
ce login  : ' . $log_spectateur . '
et ce mot de passe : ' . $pw_spectateur . '

';
	
		$corps.= 'En cas de besoin, contactez-nous à l\'adresse suivante : ' . $retour_email_admin ;
		$corps.="

Bien à vous,

L'équipe de http://www.demandezleprogramme.be" ;

		$entete="From:".$retour_email_admin."\r\nReply-To:".$retour_email_admin ; 
		//echo $corps ;

		$test_mail = mail_beta($adresse,$sujet,$corps,$entete,$email_retour_erreur); 
		if ($test_mail)
		{
			echo '<br><br><br><div class="info"><p><b>Bonjour ' . $pseudo_spectateur . "</b>,</p><br />";
			echo '<p>Nous vous remercions d\'avoir compl&eacute;t&eacute; ce formulaire d\'inscription.<br /> 
			<br />Un e-mail de confirmation reprenant vos codes a été envoyé 
			à votre adresse e-mail : ' . $e_mail_spectateur. '. 
			<br /> <br />Pour valider votre compte nouveau compte personnel, veuillez compléter les autres données concernant votre profil.
			<br /> <br /><a href="',generer_url_entite(157,'rubrique'),'">Cliquez ici pour éditer votre profil</a>.<br /><br />
			Bien à vous, <br /> <br /> <br />
			<i> L\'équipe de <a href="http://www.demandezleprogramme.be/">demandezleprogramme</a></i><br /><br />
			</div>';
			
				
			// ****************************************************************************************			
			// Avertir le WebMaster qu'une indcription est confirmée en lui envoyant un e-mail
			// ****************************************************************************************			

			$sujet='-- Nouveau spectateur : '. 
			html_entity_decode($pseudo_spectateur) . ' -';
			
			$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
			"http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<style type="text/css">
			<!--
			' . $css_email . '
			-->
			</style></head><body>
			<p>&nbsp;</p><p>&nbsp;</p>
			<h1>Un Spectateur s\'est inscrit</h1>
			<p>&nbsp;</p>';
			
			$corps.='<p><b>Pseudo</b> : ' . $pseudo_spectateur . ' </p>';
			$corps.='<b>e-mail</b> : ' . $e_mail_spectateur . ' <br />';
	
			$corps.='<b>IP</b> =: ' . $ip . ' ';
			
			$corps.='<p>&nbsp;</p>
			<a href="http://www.demandezleprogramme.be/agenda/admin_agenda/spectateurs_edit_profile.php?spect=' . $valeur_id_spect_inscrit . '">Voir son compte</a>' ;
			$corps.='<p>&nbsp;</p> 
			</body></html>
			</html>'; 

			$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;

			//echo $corps ;
		 mail_beta($retour_email_admin,$sujet,$corps,$entete,$email_retour_erreur);
			
			
			// ********************************************************************************************
			// Fin : ouvrir la session et rediriger vers formulaire "../edit_profile_spectateur.php"
			// ********************************************************************************************
			$_SESSION['nom_spectateur'] = $pseudo_spectateur;
		    $_SESSION['prenom_spectateur'] = '';
		    $_SESSION['id_spectateur'] = $valeur_id_spect_inscrit;
		    $_SESSION['pseudo_spectateur'] = $pseudo_spectateur;
		    $_SESSION['group_admin_spec'] = 1 ;
			$_SESSION['group_admin_spec_name'] = $group_admin_spec_noms[1] ;

			// ********************************************************************************************
		}
		else
		{
			echo '<div class="alerte">Une erreur s\'est produite lors de l\'envoi de l\'e-mail. En cas de nécessité, contactez-nous : <a href="mailto:info@demandezleprogramme.be>info@demandezleprogramme.be</a>. <br />
			Veuillez recommencer l\'opération.</div>' ;
		}
		
		//---------------------------------------------------------
		// Masquer le formulaire
		//---------------------------------------------------------
		$form_masquage = true;
	}
}


// ------------------------------------------------
// formulaire
// ------------------------------------------------
if ($form_masquage != true)
{
?>
	<form name="form1" method="post" action="" enctype="multipart/form-data">
	  <table border="0" align="center" cellpadding="8" cellspacing="0" class="table_spectateur" style="font-size:1.3em;">
		<tr>
		  <th colspan="2"><p>Complétez ce formulaire afin de <br />créer votre compte de SPECTATEUR à l'agenda de <br />
		  <a href="http://www.demandezleprogramme.be/">demandezleprogramme.be</a></p>		  </th>
		</tr>
		<tr>
		  <td colspan="2"><div align="center"><span class="mini">Les informations collect&eacute;es par ce formulaire ne seront pas affich&eacute;es sur le site. Seul votre &quot;pseudo&quot; sera utilis&eacute; comme signature des messages que vous laissez dans la rubrique &quot;Avis&quot;. </span></div></td>
	    </tr>
		<tr>
		  <td>Pseudo<span class="mini"> (Seul nom qui sera affich&eacute; sur le site. Attention, il ne pourra plus &ecirc;tre modifi&eacute; par la suite)</span> 
			<?php if (isset ($error_pseudo_spectateur) AND $error_pseudo_spectateur != NULL) {echo $error_pseudo_spectateur ; } ?>
			<?php if (isset ($error_doublon_pseudo) AND $error_doublon_pseudo != NULL) {echo $error_doublon_pseudo ; } ?>		</td>
		  <td><input name="pseudo_spectateur" type="text" id="pseudo_spectateur" value="<?php if (isset($pseudo_spectateur)){echo $pseudo_spectateur;}?>" size="30" maxlength="50"></td>
		</tr>
		<tr>
		  <td>Adresse e-mail
		  <?php 
		  if (isset ($error_e_mail) AND $error_e_mail != NULL) {echo $error_e_mail ; } 
		  if (isset ($error_email_doublon) AND $error_email_doublon != NULL) {echo $error_email_doublon ; } 
		  ?> <br />
		  
		  <span class="mini">Attention, si vous possédez une adresse YAHOO ou HOTMAIL, nous attirons votre attention sur le risque que nos messages ne vous arrivent pas ou qu'ils soient classés systématiquement dans vos spams. Nous vous invitons donc à inscrire et à utiliser une autre adresse pour le site.</span>
		  
		  </td>
		  <td><input name="e_mail_spectateur" type="text" id="e_mail_spectateur" value="<?php if (isset($e_mail_spectateur)){echo $e_mail_spectateur;}?>" size="30" maxlength="40"></td>
		</tr>
		<tr>
		  <td>Login <span class="mini"> (exemple : &quot;votre nom&quot;)</span>		  <?php if (isset ($error_log) AND $error_log != NULL) {echo $error_log ; } ?>	  </td>
		  <td><input name="log_spectateur" type="text" id="log_spectateur" value="<?php if (isset($log_spectateur)){echo $log_spectateur;}?>" size="30" maxlength="8"></td>
		</tr>
		<tr>
		  <td>Mot de passe  
		  <?php if (isset ($error_pw) AND $error_pw != NULL) {echo $error_pw ; } ?>	  </td>
		  <td><input name="pw_spectateur" type="password" id="pw_spectateur" value="<?php if (isset($pw_spectateur)){echo $pw_spectateur;}?>" size="8" maxlength="9"></td>
		</tr>
			<tr>
		  <td>Confirmer le  mot de passe 
		  <?php if (isset ($error_conf_pw) AND $error_conf_pw != NULL) {echo $error_conf_pw ; } ?>	  </td>
		  <td><input name="pw_spectateur_double" type="password" id="pw_spectateur_double" value="<?php if (isset($pw_spectateur_double)){echo $pw_spectateur_double;}?>" size="8" maxlength="9"></td>
		</tr>
			<tr>
			  <td>Recopier le code de l'image : 
			  <?php if (isset ($erreur_code) AND $erreur_code != NULL) {echo $erreur_code ; } ?> </td>
			  
      <td><input name="code" type=text id="code" size="5" maxlength="<?php echo $nb_car; ?>">
          <img src="agenda/spectateurs_admin/ins/im_gen.php?session=<?php echo $session; ?>" hspace="10" align="top"> </td>
	    </tr>
			<tr class="table_public">
              <td colspan="2" align="center"><?php if (isset ($error_conditions_gen) AND $error_conditions_gen != NULL) {echo $error_conditions_gen ; } ?>
                <label>
                    <input name="conditions_gen" type="checkbox" value="ok" <?php if (isset($conditions_gen) AND $conditions_gen == 'set') { echo 'checked="checked"' ; } ?>/>
              Je d&eacute;clare accepter les <a href="<?php
              	echo generer_url_entite(171,'article','page=article-3');
              ?>" target="_blank">conditions g&eacute;n&eacute;rales d'utilisation (mentions l&eacute;gales)</a> d'utilisation de demandezleprogramme</label></td>
	    </tr>
		<tr>
		  <td colspan="2"><div align="center"> <br />
				  <input type=hidden name=sid value=<?php echo $session; ?>>
				  <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
				  <br />
		  </div></td>
		</tr>
	  </table>
	</form>
		
<?php 
}

//--- mysql_close($db2dlp);

	//--- rétablir le dossier de travail courant
	chdir($dossier_courant);

	if ($rec == NULL) {
		include_spip('inc/session');
	    session_set('nom_spectateur', $pseudo_spectateur);
	    session_set('prenom_spectateur', '');
	    session_set('id_spectateur', $valeur_id_spect_inscrit);
	    session_set('pseudo_spectateur', $pseudo_spectateur);
	    session_set('group_admin_spec', 1);
	    session_set('group_admin_spec_name', $group_admin_spec_noms[1]);
//		echo '<meta http-equiv="refresh" content="4;url=',generer_url_entite(157,'rubrique'),'">' ;
	}
?>
