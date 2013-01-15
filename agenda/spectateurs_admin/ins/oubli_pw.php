<?php
echo '<h2>Rappel de mot de passe (réservé aux Spectateurs)</h2>';

	//--- changer le dossier de travail courant
	$dossier_courant = getcwd();
	chdir(dirname(__FILE__));

include_spip('inc/utils');

require '../../inc_var.php';
require '../../inc_var_dist_local.php';
require '../../inc_db_connect.php';
require 'inc_var_inscription.php';

$session = md5(time()); // numero d'identification du visieur
$ip = $_SERVER['REMOTE_ADDR'] ;
$timestamp = time();

$form_masquage = false ; // Rendre visible le formulaire

// code aleatoire pour l'image generee :
$nb_car = 5 ;
$txt = "abcdefghijkmnpqrstuvwxyz123456789"; 
$txt = str_shuffle($txt);
$code = substr($txt, 10, $nb_car);

mysql_query("INSERT INTO $table_im_crypt (session_crypt,code_crypt,timestamp,ip) VALUES ('$session','$code','$timestamp','$ip')");

$erreur = '' ;
if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'recevoir'))
{
	// ------------------------------------------------------------
	// Test du code recopié à partir de l'image cryptée

	$get_sess = $_POST['sid'];
	$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	$donnees = mysql_fetch_array($reponse);

	if ($donnees ['code_crypt']=="" OR $donnees ['code_crypt']!=$_POST['code']) // Code non valide
	{
		$code = '';
		echo '<div class="alerte">Le code que vous avez recopié à partir de l\'image est incorrect </div>';
		$erreur.= 'code recopié = faux' ;
	}
	else // Code valide
	{
		// Suppression de la DB
		$query = mysql_query("DELETE FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
		
		//  TEST EMAIL
		if (isset ($_POST['e_mail_spectateur']) AND $_POST['e_mail_spectateur'] != NULL 
		AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_spectateur'])))
		{
			$e_mail_spectateur = $_POST['e_mail_spectateur'];
			
			$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE e_mail_spectateur = '$e_mail_spectateur' LIMIT 1 ");
			$donnees = mysql_fetch_array($reponse);
			
			if (!empty ($donnees))
			{
				$nom_spectateur = $donnees ['nom_spectateur'];
				$e_mail_spectateur = $donnees ['e_mail_spectateur'];
				$tel_spectateur = $donnees ['tel_spectateur'];
				$log_spectateur = $donnees ['log_spectateur'];
				$pw_spectateur = $donnees ['pw_spectateur'];
			}
			else // Adresse au bon format mais inconnue dans la DB
			{
				echo '<p>&nbsp;</p><p>&nbsp;</p>
				<div class="alerte">Cette adresse e-mail n\'est pas présente dans notre base de données</div><br>' ;
				$erreur.= 'Pas dans DB' ;
			}
		}
		else // Erreur : adresse absente ou au mauvais format 
		{		
			echo '<p>&nbsp;</p><p>&nbsp;</p>
			<div class="alerte">Vous devez introduire l\'adresse e-mail sous laquelle vous êtes enregistré 
			de façon à recevoir vos codes</div><br>' ;
			$erreur.= 'Pas dans DB' ;
		}
	}
	if ($erreur == '')
	{
		// --------------------------------------------------------
		// Envoi e-mail de confirmation à l'abonné
		// --------------------------------------------------------
		$sujet='Rappel du mot de passe de votre compte SPECTATEUR';
	
		$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
		<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
		<!--
		' . $css_email . '
		-->
		</style></head><body>
				
		<p><b>' . $nom_spectateur . ', </b><br />
		
		<p>Vous vous &ecirc;tes connect&eacute; au site 
		<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a> 
		afin de recevoir  le mot de passe de votre compte SPECTATEUR : </p>
		<p><b>Identifiant </b>: ' . $log_spectateur . '<br />
		<b>Mot de passe : </b>' . $pw_spectateur . '</p>
		
		<p>Retrouvez votre <a href="'.generer_url_entite_absolue(121,'rubrique').'">espace personnel</a> via ce lien : 
		<a href="'.generer_url_entite_absolue(121,'rubrique').'">'.generer_url_entite_absolue(121,'rubrique').'</a>
		</p><br />
		<p>Bien &agrave; vous,</p>
		<p>L\'&eacute;quipe de com&eacute;dien.be</p><br />
		<p class="email_style_petit">Vertige asbl<br />
		163, rue de la Victoire 1060 Bruxelles<br />
		<a href="mailto:info@comedien.org">info@comedien.org</a></p>
		<p>&nbsp;</p>
		</body></html>
		</html>'; 

		$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin ;
		
		$test_mail = mail($e_mail_spectateur,$sujet,$corps,$entete,$email_retour_erreur);
		if ($test_mail)
		{
			echo '<p>&nbsp;</p><p>&nbsp;</p>
			<div class="info">Un e-mail contenant vos codes a été envoyé à l\'adresse ' . $e_mail_spectateur . '</div><br />' ;
			$form_masquage = true;
		}	
		else
		{
			echo '<p>&nbsp;</p><p>&nbsp;</p>
			<div class="alerte">Erreur lors de l\'envoi de l\'e-mail contenant vos codes. 
			Veuillez réessayer ultérieurement <br /></div><br />' ;
		}	
	}
}

//--- mysql_close($db2dlp);

if ($form_masquage != true)
{
?>

<div align="center"><p>Ce formulaire est exclusivement réservé aux <span style="font-weight: bold">Spectateurs</span>.
  Dans les autres cas, utilisez ces liens :  </p>
<ul>
  <li>Vous êtes  un <a href="agenda/user_admin/ins/oubli_pw.php">lieu culturel affilié</a></li>
  <li>Vous êtes inscrit sur le site <a href="http://www.comedien.be/-se-connecter-">comedien.be</a> </li>
</ul>
<p>&nbsp;</p>
<p>&nbsp;</p>
</div>

<form name="form1" method="post" action="">
  <table width="450" border="0" align="center" cellpadding="5" cellspacing="0" class="data_table" >
	<tr>
	  <th colspan="2">Introduisez l'adresse e-mail sous laquelle vous &ecirc;tes enregistr&eacute; et vous recevrez vos codes &agrave; cette m&ecirc;me adresse. </th>
	</tr>
	<tr>
	  <td>Votre a dresse e-mail
	  </td>
	  <td><input name="e_mail_spectateur" type="text" id="e_mail_spectateur" value="" size="30" maxlength="40"></td>
	</tr>
	    <tr>
      <td>Recopier le code de l'image : </td>
      <td><input name="code" type=text id="code" size="5" maxlength="<?php echo $nb_car; ?>">
          <img src="agenda/spectateurs_admin/ins/im_gen.php?session=<?php echo $session; ?>" hspace="10" align="top"> </td>
    </tr>
	<tr>
	  <td colspan="2"><div align="center"> <br />
              <input type="hidden" name="sid" value=<?php echo $session; ?>>

			  <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="recevoir">
			  <br />
	  </div></td>
	</tr>
  </table>
</form>
<?php
}

	//--- rétablir le dossier de travail courant
	chdir($dossier_courant);
?>
