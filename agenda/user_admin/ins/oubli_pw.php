<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Rappel de mot de passe (Lieux culturels)</title>
<link href="../../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>


<div id="head_admin_agenda"></div>

<h1>Rappel de mot de passe (réservé aux lieux culturels)</h1>

<div class="menu_back">
<a href="../../../-Agenda-">Retour au site</a>
</div>


<p>
<?php
require '../../inc_var.php';
require '../../inc_var_dist_local.php';
require '../../inc_db_connect.php';
require 'inc_var_inscription.php';
require '../../inc_fct_base.php';

$session = md5(time()); // numero d'identification du visieur
$ip = $_SERVER['REMOTE_ADDR'] ;
$timestamp = time();


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
		echo '<div class="alerte">Le code que vous avez recopié à partir de l\'image est incorrect </div';
		$erreur.= 'code recopié = faux' ;
	}
	
	else // Code valide
	{
		// Suppression de la DB
		$query = mysql_query("DELETE FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
		
		
		//  TEST EMAIL
		if (isset ($_POST['e_mail_admin_spec']) AND $_POST['e_mail_admin_spec'] != NULL 
		AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_admin_spec'])))
		{
			$e_mail_admin_spec = $_POST['e_mail_admin_spec'];
			
			$reponse = mysql_query("SELECT * FROM $table_user_agenda WHERE e_mail_admin_spec = '$e_mail_admin_spec' LIMIT 1 ");
			$donnees = mysql_fetch_array($reponse);
			
			if (!empty ($donnees))
			{
				$nom_admin_spec = $donnees ['nom_admin_spec'];
				$e_mail_admin_spec = $donnees ['e_mail_admin_spec'];
				$tel_admin_spec = $donnees ['tel_admin_spec'];
				$log_admin_spec = $donnees ['log_admin_spec'];
				$pw_admin_spec = $donnees ['pw_admin_spec'];
				$group_admin_spec = $donnees ['group_admin_spec'];
				$adr_factur_admin_spec = $donnees ['adr_factur_admin_spec'];
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
		$sujet='Rappel de votre mot de passe';
	
		$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
		<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
		<!--
		' . $css_email . '
		-->
		</style></head><body>
				
		<p><b>' . $nom_admin_spec . ', </b><br />
		
		<p>Vous vous &ecirc;tes connect&eacute; au site 
		<a href="http://www.demandezleprogramme.be/">www.demandezleprogramme.be</a> 
		afin de recevoir  vos mots de passe : </p>
		<p><b>Identifiant </b>: ' . $log_admin_spec . '<br />
		<b>Mot de passe : </b>' . $pw_admin_spec . '</p>
		
		<p>Retrouvez votre <a href="' . $racine_domaine . 'agenda/user_admin/">espace personnel</a> via ce lien</p><br />
		<p>Bien &agrave; vous,</p>
		<p>L\'&eacute;quipe de com&eacute;dien.be</p><br />
		<p class="email_style_petit">Vertige asbl<br />
		163, rue de la Victoire 1060 Bruxelles<br />
		tel/fax 02 544 00 34<br />
		<a href="mailto:info@comedien.org">info@comedien.org</a></p>
		<p>&nbsp;</p>
		</body></html>
		</html>'; 

		$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin ;
		//echo $corps ;
		
		$test_mail = mail_beta($e_mail_admin_spec,$sujet,$corps,$entete,$email_retour_erreur);
		
		if ($test_mail)
		{
			echo '<p>&nbsp;</p><p>&nbsp;</p>
			<div class="info">Un e-mail contenant vos codes a été envoyé à l\'adresse ' . $e_mail_admin_spec . '
			<br /><a href="http://www.demandezleprogramme.be/">Retour</a></div><br>' ;
			exit () ;			
		}	
		else
		{
			echo '<p>&nbsp;</p><p>&nbsp;</p>
			<div class="alerte">Erreur lors de l\'envoi de l\'e-mail contenant vos codes. 
			Veuillez réessayer ultérieurement <br /></div><br>' ;
		}	
	}
}


//--- mysql_close($db2dlp);

?>
  </p>
</p>
<p>&nbsp;</p>
<form name="form1" method="post" action="">
  <table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
	<tr>
	  <th colspan="2">Introduisez l'adresse e-mail sous laquelle vous &ecirc;tes enregistr&eacute; et vous recevrez vos codes &agrave; cette m&ecirc;me adresse. </th>
	</tr>
	<tr>
	  <td>Votre a dresse e-mail
	  </td>
	  <td><input name="e_mail_admin_spec" type="text" id="e_mail_admin_spec" value="" size="30" maxlength="40"></td>
	</tr>
	    <tr>
      <td>Recopier le code de l'image : </td>
      <td><input name="code" type=text id="code" size="5" maxlength="<?php echo $nb_car; ?>">
          <img src=im_gen.php?session=<?php echo $session; ?> hspace="10" align="top"> </td>
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
 	


<p>&nbsp;</p>
</body>
</html>
