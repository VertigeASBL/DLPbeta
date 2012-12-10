<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Formulaire d'inscription pour les partenaires (a)</title>
<link href="../../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Formulaire d'inscription pour les partenaires</h1>

<div class="menu_back">
<a href="../../../-Demandez-le-programme-">Retour au site</a>
</div>

  
<?php 
// ---------------------------------------------------------
// Application : Inscription avec securite anti-robot et confirmation par envoi d'e-mail + black list

// ---------------------------------------------------------
// Initialisation de variables
// ---------------------------------------------------------
require '../../inc_var.php';
require '../../inc_var_dist_local.php';
require '../../inc_db_connect.php';
require 'inc_var_inscription.php';
require '../../inc_fct_base.php';

$form_masquage = false ; // Rendre visible le formulaire
$adr_retour = '<a href="../../../-Demandez-le-programme-">&gt; &gt; Retour au site &lt; &lt; </a>';


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
	if (isset($_POST['nom']) AND ($_POST['nom'] != NULL)) 
	{
		$nom = htmlentities($_POST['nom'], ENT_QUOTES);
		$nom_form = stripslashes($nom);
	}
	else
	{
		$nom = '';
		$rec .= '- Vous devez introduire un nom <br>';
	}
	
	
	// ------------------------------------------------------------
	// TEST DU PRENOM
	if (isset($_POST['prenom']) AND ($_POST['prenom'] != NULL)) 
	{
		$prenom = htmlentities($_POST['prenom'], ENT_QUOTES);
		$prenom_form = stripslashes($prenom);
	}
	else
	{
		$prenom = '';
		$rec .= '- Vous devez introduire un prénom <br>';
	}
	
	
	// ------------------------------------------------------------
	// TEST SOCIETE (lieu culturel)
	if (isset($_POST['societe']) AND ($_POST['societe'] != NULL)) 
	{
		$societe = htmlentities($_POST['societe'], ENT_QUOTES);
		$societe_form = stripslashes($societe);
	}
	else
	{
		$societe = '';
		$rec .= '- Vous devez introduire un nom de société <br>';
	}	
	

	// ------------------------------------------------------------
	// N TVA (facultatif)
	if (isset($_POST['n_tva']) AND ($_POST['n_tva'] != NULL)) 
	{
		$n_tva = htmlentities($_POST['n_tva'], ENT_QUOTES);
		$n_tva_form = stripslashes($n_tva);
	}
	else
	{
		$n_tva = '';
	}	
	

	// ------------------------------------------------------------
	// WEB SITE (facultatif)
	if (isset($_POST['web_site']) AND ($_POST['web_site'] != NULL)) 
	{
		$web_site = htmlentities($_POST['web_site'], ENT_QUOTES);
		$web_site_form = stripslashes($web_site);
	}
	else
	{
		$web_site = '';
	}	
	

	// ------------------------------------------------------------
	// TEST ADRESSE
	if (isset($_POST['adresse']) AND ($_POST['adresse'] != NULL)) 
	{
		$adresse = htmlentities($_POST['adresse'], ENT_QUOTES);
		$adresse_form = stripslashes($adresse);
	}
	else
	{
		$adresse = '';
		$rec .= '- Vous devez introduire votre adresse<br>';
	}
	

	// ------------------------------------------------------------
	// TEST TELEPHONE 1 (tel bureau)
	if (isset($_POST['tel_1']) AND ($_POST['tel_1'] != NULL)) 
	{
		$tel_1 = htmlentities($_POST['tel_1'], ENT_QUOTES);
		$tel_1_form = stripslashes($tel_1);
	}
	else
	{
		$tel_1 = '';
		$rec .= '- Vous devez introduire de numéro de téléphone<br>';
	}
	
	
	// ------------------------------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail']))))
	{
		$e_mail = $_POST['e_mail'];
		
		// Cette adresse est-elle déjà dans la TABLE "ag_lieux" ou "ag_users"
		// Tester si le LOGIN existe déjà dans la DB ?			
		$req_doublon = mysql_query("SELECT e_mail_admin_spec FROM $table_user_agenda 
		WHERE e_mail_admin_spec = '$e_mail' ");
		$email_doublon = mysql_fetch_array($req_doublon);
		if (isset($email_doublon['e_mail_admin_spec']))
		{
			$rec .= '- L\'adresse e-mail que vous avez introduite ('.$e_mail.') est déjà présente dans notre base de données. 
			Veuillez en introduire une autre<br>';
			$e_mail = '';
		}
	}
	else
	{
	$e_mail = '';
	$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
	}

	
	// ------------------------------------------------------------
	// Test du code recopié à partir de l'image cryptée
	
	$get_sess = $_POST['sid'];

	$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	$donnees = mysql_fetch_array($reponse);
	
	if ($donnees ['code_crypt']=="" OR $donnees ['code_crypt']!=$_POST['code']) // Code non valide
	{
		$code = '';
		$rec .= '- Le code que vous avez recopié à partir de l\'image est incorrect <br>';
	}
	
	else // Code valide
	{
		// Suppression de la DB
		$query = mysql_query("DELETE FROM $table_im_crypt WHERE session_crypt = '$get_sess'");
	}
	
	//---------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	if ($rec != NULL) // Il y a au moins un champ du formulaire qui est mal rempli
	{
		echo '<div class="alerte">' . $rec . '</div><br>' ;
	}
	else // Authentification OK 
	{
		//---------------------------------------------------------
		// Enregistrement les données dans la DB temporaire
		//---------------------------------------------------------
		mysql_query("INSERT INTO `$table_conf_inscript` ( `session_crypt` , `timestamp` , `ip` , `nom` , `prenom` , `societe` , `n_tva` , `adresse` , `tel_1` , `web_site` , `e_mail` , `session_pw` , `confirm` ) 
		VALUES ('$session', '$timestamp', '$ip', '$nom', '$prenom', '$societe', '$n_tva',  '$adresse', '$tel_1',  '$web_site', '$e_mail', '$s_pw', 'no')");
		
		
		
		//---------------------------------------------------------
		// Inscription à la newsletter
		//---------------------------------------------------------
		// richir : inscrire aux listes de diffusion, récupérer les adresses emails et noms... qui sont dans les variables de la requête juste au dessus
		/*$adrm = addslashes($e_mail_spectateur);
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
		unset($adrm, $sql, $query, $maint);*/



		//---------------------------------------------------------
		// Envoi de l'e-mail de confirmation au visiteur
		//---------------------------------------------------------
		$adresse= $e_mail;
		  
		$sujet="-- Confirmez votre inscription à l'agenda de demandezleprogramme.be";
		$corps="Bonjour " . html_entity_decode($prenom) . " " . html_entity_decode($nom) .", \n 
		\nVotre demande d'affiliation à l'agenda de demandezleprogramme.be a bien été reçue. 
		\nNous vous invitons également à consulter la liste des contenus acceptés ou refusés dans l'agenda afin d'éviter tout malentendu : ".$racine_domaine."article=205.
		\nAfin de confirmer votre demande, veuillez cliquer sur le lien suivant : 
		\n". $racine_domaine."agenda/user_admin/ins/b.php?s_id=".$session."&s_pw=".$s_pw." 
		\nVous recevrez ensuite un e-mail vous expliquant les modalités de paiement de votre cotisation. \n";
		$corps.='Si le lien ne fonctionne pas, contactez-nous à l\'adresse suivante : ' . $retour_email_admin ;
		$corps.=" \n \n \nBien à vous, \n \nL'équipe de http://www.demandezleprogramme.be" ;
		
		$entete="From:".$retour_email_admin."\r\nReply-To:".$retour_email_admin ; 
		$test_mail = mail_beta($adresse,$sujet,$corps,$entete,$email_retour_erreur); 
		
		if ($test_mail)
		{
			echo '<br><br><br><div class="info"><p><b>Bonjour ' . $prenom . ' '. $nom . "</b>,</p><br />";
			echo '<p>Nous vous remercions d\'avoir compl&eacute;t&eacute; ce formulaire d\'inscription.<br /> 
			<br />Un e-mail de confirmation est envoy&eacute; &agrave; votre adresse e-mail : <b>'. $e_mail. '</b>. 
			<br />Cet e-mail vous indiquera la marche à suivre pour mener &agrave; bien votre inscription.<br><br>
			Bien à vous, <br /> <br /> <br />
			<i> L\'équipe de <a href="http://www.demandezleprogramme.be/">demandezleprogramme</a></i><br /><br />
			' . $adr_retour . '</div>';
		}
		else
		{
			echo '<div class="alerte">Une erreur s\'est produite. <br />
			Veuillez recommencer l\'opération.</div>' ;
		}
		
		//---------------------------------------------------------
		// Masquer le formulaire
		//---------------------------------------------------------
		$form_masquage = true;
	}
}
?>
</p>
<!-- ----------------- FORMULAIRE ----------------- -->
<?php 
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
	Pour plus d&rsquo;information, prenez contact avec nous<br />';
	
	
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
	 exit ();
}


//---------------------------------------------------------
// Afficher formulaire s'il n'a pas été déjà rempli
//---------------------------------------------------------
if ($form_masquage != true)
{
?>
<form name="form1" method="post" action="">
  <table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
    <tr>
      <th colspan="2">Formulaire d'inscription pour les partenaires</th>
    </tr>
    <tr>
      <td>Nom du lieu culturel <span class="champ_obligatoire">*</span> :</td>
      <td><input name="societe" type="text" id="societe" value="<?php if (isset($societe_form)){echo $societe_form;}?>" size="30" maxlength="150"></td>
    </tr>
    <tr>
      <td>Nom<span class="champ_obligatoire">*</span> :</td>
      <td><input name="nom" type="text" id="nom" value="<?php if (isset($nom_form)){echo $nom_form;}?>" size="30" maxlength="150"></td>
    </tr>
    <tr>
      <td>Pr&eacute;nom<span class="champ_obligatoire">*</span> :</td>
      <td><input name="prenom" type="text" id="prenom" value="<?php if (isset($prenom_form)){echo $prenom_form;}?>" size="30" maxlength="30"></td>
    </tr>
    <tr>
      <td>Num&eacute;ro de TVA : </td>
      <td><input name="n_tva" type="text" id="n_tva" value="<?php if (isset($n_tva_form)){echo $n_tva_form;}?>" size="30" maxlength="15"></td>
    </tr>
    <tr>
      <td colspan="2"><p> Adresse de facturation <span class="champ_obligatoire">*</span> (n&deg;, rue, code postal, ville, pays) :</p>
          <p>
            <input name="adresse" type="text" id="adresse" value="<?php if (isset($adresse_form)){echo $adresse_form;}?>" size="75" maxlength="75">
          </p></td>
    </tr>
    <tr>
      <td>T&eacute;l&eacute;phone <span class="champ_obligatoire">*</span> :</td>
      <td><input name="tel_1" type="text" id="tel_1" value="<?php if (isset($tel_1_form)){echo $tel_1_form;}?>" size="30" maxlength="30"></td>
    </tr>
    <tr>
      <td colspan="2"><p>Adresse compl&egrave;te de votre site Web :</p>
          <p>
            <input name="web_site" type="text" id="web_site" value="<?php 
			if (isset($web_site_form))
			{echo $web_site_form;}
			else {echo'http://' ;}
			?>" size="75" maxlength="75">
          </p></td>
    </tr>
    <tr>
      <td><p>Adresse e-mail<span class="champ_obligatoire">*</span> :<br>
        (Cette adresse sera employ&eacute;e pour vous envoyer l'e-mail d'activation).</p></td>
      <td><input name="e_mail" type="text" id="e_mail" value="<?php if (isset($e_mail)){echo $e_mail;}?>" size="30" maxlength="50"></td>
    </tr>
    <tr>
      <td>Recopier le code de l'image<span class="champ_obligatoire">*</span> : </td>
      <td><input name="code" type=text id="code" size="5" maxlength="<?php echo $nb_car; ?>">
          <img src=im_gen.php?session=<?php echo $session; ?> hspace="10" align="top"> </td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
	  <br />
              <input type=hidden name=sid value=<?php echo $session; ?>>
              <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
	  <br />
      </div></td>
    </tr>
  </table>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
        </form>

<?php 
}

//--- mysql_close($db2dlp);

?>
</body>
</html>
