<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition des donn&eacute;es d'un utilisateur de l'agenda</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Edition des donn&eacute;es d'un utilisateur de l'agenda</h1>

<div class="menu_back"><a href="index_admin.php">Menu Admin</a> | 
<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a>
</div>


<p>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Module d'édition des données des utilisateurs
// edit_user_agenda.php?new=creer pour créer une nouvelle entrée
// edit_user_agenda.php?id=... pour éditer l'entrée
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii




//-----------------------------------------
// Créer une nouvelle entrée
//-----------------------------------------
if (isset ($_GET['new']) AND $_GET['new'] == 'creer') // La variable GET qui donne l'ID à confirmer. Si NULL -> nouvelle entrée
{
	mysql_query("INSERT INTO `$table_user_agenda` (`lieu_admin_spec`, `nom_admin_spec`) 
	VALUES ('0', 'Nouvelle entree')");
	
	$nouvel_id_table_lieu = mysql_insert_id() ; // sera utile pour créer un lien d'accès pour éditer les données
	echo '<br><br><br><div class="info"><p>Un nouvel utilisateur a été créé et peut être édité <a href="edit_user_agenda.php?id='.$nouvel_id_table_lieu.'">Continuer</a></p></div><br>' ;

	//--- mysql_close($db2dlp);
	exit();
}

//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entrée
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'update'))
{
$id = $_GET['id'];

	//-----------------------------------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------
	// = initialisation de la var qui sera testée avant d'enregistrer les données dans la DB
	// Si elle est vide => enregistrer. Sinon, elle contient le message d'erreur, et on l'affiche.
	$rec = '';
	
	// -----------------------------------------
	// TEST DU LIEU CULTUREL (liste déroulante)
	if (isset($_POST['list_lieux_culturel']) AND ($_POST['list_lieux_culturel'] != NULL)) 
	{
		$list_lieux_culturel = htmlentities($_POST['list_lieux_culturel'], ENT_QUOTES);
		mysql_query("UPDATE `$table_user_agenda` SET `lieu_admin_spec` = '$list_lieux_culturel' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$list_lieux_culturel = '';
		$error_lieu = '<div class="error_form">Vous devez sélectionner un Lieu culturel</div>';
	}
	
	// -----------------------------------------
	// TEST DU RESPONSABLE
	if (isset($_POST['nom_admin_spec']) AND ($_POST['nom_admin_spec'] != NULL)) 
	{
		$nom_admin_spec = htmlentities($_POST['nom_admin_spec'], ENT_QUOTES); 
	mysql_query("UPDATE `$table_user_agenda` SET `nom_admin_spec` = '$nom_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$nom_admin_spec = '';
		$rec .= '- Vous devez introduire le nom de la personne de contact (responsable) <br>';
		$error_responsable = '<div class="error_form">Vous devez introduire le nom de la personne de contact (responsable)</div>';
	}
	
	// -----------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['e_mail_admin_spec']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['e_mail_admin_spec']))))
	{
		$e_mail_admin_spec = $_POST['e_mail_admin_spec'];
		mysql_query("UPDATE `$table_user_agenda` SET `e_mail_admin_spec` = '$e_mail_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$e_mail_admin_spec = '';
		$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_e_mail = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
	}
	
	
	// -----------------------------------------
	// TEST TELEPHONE 
	if (isset($_POST['tel_admin_spec']) AND ($_POST['tel_admin_spec'] != NULL)) 
	{
		$tel_admin_spec = htmlentities($_POST['tel_admin_spec'], ENT_QUOTES);
		mysql_query("UPDATE `$table_user_agenda` SET `tel_admin_spec` = '$tel_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$tel_admin_spec = '';
		$rec .= '- Vous devez introduire de numéro de téléphone <br>';
		$error_tel = '<div class="error_form">Vous devez introduire de numéro de téléphone</div>';
	}
	
	
	// -----------------------------------------
	// TEST DU LOGIN
	if (isset($_POST['log_admin_spec']) AND ($_POST['log_admin_spec'] != NULL)) 
	{
		$log_admin_spec = htmlentities($_POST['log_admin_spec'], ENT_QUOTES);
		
		// Tester si le LOGIN existe déjà dans la DB ?			
		$req_existe = mysql_query("SELECT * FROM $table_user_agenda WHERE log_admin_spec = '$log_admin_spec' AND id_admin_spec != '$id' ");
		$user_existe = mysql_fetch_array($req_existe);
		if (isset($user_existe['log_admin_spec']))
		{
			$rec .= '- Le LOGIN ('.$log_admin_spec.') a déjà été choisi. Veuillez en introduire un autre<br>';
			$error_log = '<div class="error_form">Le LOGIN ('.$log_admin_spec.') a déjà été choisi. Veuillez en introduire un autre</div>';
		}
		else
		{
			mysql_query("UPDATE `$table_user_agenda` SET `log_admin_spec` = '$log_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
		}	
	}
	else
	{
		$log_admin_spec = '';
		$rec .= '- Vous devez introduire un LOGIN <br>';
		$error_log = '<div class="error_form">Vous devez introduire un LOGIN</div>';
	}
	
	
	// -----------------------------------------
	// TEST PW

	if ($_POST['pw_admin_spec'] != NULL OR $_POST['pw_admin_spec_double'] != NULL) // s'il est vide, ne pas le réenregistrer
	{		
		
		$pw_admin_spec = htmlentities($_POST['pw_admin_spec'], ENT_QUOTES);
		$pw_admin_spec_double = htmlentities($_POST['pw_admin_spec_double'], ENT_QUOTES);

		if (!preg_match('`^\w{4,8}$`', $pw_admin_spec)) // \w classe prédéfinie utilisée avec les PCRE et déterminant l'usage exclusif des lettres et chiffres, ainsi que de l'underscore.
		{
			$rec .= '- Votre MOT DE PASSE doit contenir entre 4 et 8 caracteres alphanumeriques <br>';
			$error_pw = '<div class="error_form">Votre MOT DE PASSE doit contenir entre 4 et 8 caracteres alphanumeriques</div>';
		}
		
		elseif ($pw_admin_spec_double == NULL OR !preg_match('`^\w{4,8}$`', $pw_admin_spec_double)) 
		{
			$rec .= '- Vous devez confirmer le mote de passe <br>';
			$error_conf_pw = '<div class="error_form">Vous devez confirmer le mote de passe</div>';
		}
		
		elseif ($pw_admin_spec_double != $pw_admin_spec) 
		{
		$rec .= '- Le mot de passe confirmé ne correspond pas <br>';
				$error_conf_pw = '<div class="error_form">Le mot de passe confirmé ne correspond pas</div>';
		}
		else
		{
			mysql_query("UPDATE `$table_user_agenda` SET `pw_admin_spec` = '$pw_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
		}
	}

	// -----------------------------------------
	// TEST GROUP D'AUTORISATION
	if (isset($_POST['group_admin_spec']) AND ($_POST['group_admin_spec'] != NULL)) 
	{
		$group_admin_spec = htmlentities($_POST['group_admin_spec'], ENT_QUOTES);
		mysql_query("UPDATE `$table_user_agenda` SET `group_admin_spec` = '$group_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$group_admin_spec = '';
		$rec .= '- Vous devez introduire un groupe d\'autorisation<br>';
		$error_groupe = '<div class="error_form">Vous devez introduire un groupe d\'autorisation</div>';
	}


	// -----------------------------------------
	// TEST ADRESSE FACTURATION 
	if (isset($_POST['adr_factur_admin_spec']) AND ($_POST['adr_factur_admin_spec'] != NULL)) 
	{
		$adr_factur_admin_spec = htmlentities($_POST['adr_factur_admin_spec'], ENT_QUOTES);
mysql_query("UPDATE `$table_user_agenda` SET `adr_factur_admin_spec` = '$adr_factur_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$adr_factur_admin_spec = '';
		$error_adresse = '<div class="error_form">Vous devez introduire une adresse de facturation</div>';
	}
	
	/*
	// -----------------------------------------
	// TEST Fin cotisation
	if (isset($_POST['fin_cotisation_annee']) AND ($_POST['fin_cotisation_annee'] != NULL) 
	AND isset($_POST['fin_cotisation_mois']) AND ($_POST['fin_cotisation_mois'] != NULL)) 
	{ 
		$fin_cotisation_admin_spec = $_POST['fin_cotisation_annee'] . '-' . $_POST['fin_cotisation_mois'] .'-01';
		$fin_cotisation_admin_spec = htmlentities($fin_cotisation_admin_spec, ENT_QUOTES);
		mysql_query("UPDATE `$table_user_agenda` SET `fin_cotisation_admin_spec` = '$fin_cotisation_admin_spec' WHERE `id_admin_spec` = '$id' LIMIT 1 ");
	}
	else
	{
		$fin_cotisation_admin_spec = '0000-00-00';
		$rec .= '- Vous devez introduire une date d\'échéance de l\'abonnement<br>';
	}
	*/
	
	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------------------------------
	if ($rec != NULL) // Il y a au moins un champ du formulaire qui est mal rempli
	{
		//echo '<div class="alerte">' . $rec . '</div><br>' ;
	}
	else // Tout OK
	{		
		echo '<div class="info"><p>L\'entrée '.$id.' est mise à jour</p></div>' ; // Message confirmation
	}
}
// ----------------------------------------------------------





// ----------------------------------------------------------
if (empty ($_GET['id']) OR $_GET['id'] == NULL) // La variable GET qui donne l'ID à confirmer. Si NULL -> nouvelle entrée
{
	echo '<br><br><br><div class="info"><p><a href="edit_user_agenda.php?new=creer">Voulez-vous encoder une nouvelle entrée ?</a></p></div><br>' ;
	
	// RAZ des données
	$lieu_admin_spec = '';
	$nom_admin_spec  = '';
	$e_mail_admin_spec = '';
	$tel_admin_spec  = '';
	$log_admin_spec  = '';
	$pw_admin_spec  = '';
	$group_admin_spec  = '';
	$adr_factur_admin_spec = '';
	$fin_cotisation_admin_spec = '';
}
else
{
	$id = $_GET['id'];
	$reponse = mysql_query("SELECT * FROM $table_user_agenda WHERE id_admin_spec = '$id'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Cette entrée n\'existe pas</div><br>' ;
	}
	else
	{
		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entrée
		// ------------------------------------------------
		
		$reponse = mysql_query("SELECT * FROM $table_user_agenda WHERE id_admin_spec = '$id'");
		$donnees = mysql_fetch_array($reponse);
		
		$nom_admin_spec = $donnees ['nom_admin_spec'];
		$e_mail_admin_spec = $donnees ['e_mail_admin_spec'];
		$tel_admin_spec = $donnees ['tel_admin_spec'];
		$log_admin_spec = $donnees ['log_admin_spec'];
		$pw_admin_spec = $donnees ['pw_admin_spec'];
		$group_admin_spec = $donnees ['group_admin_spec'];
		$adr_factur_admin_spec = $donnees ['adr_factur_admin_spec'];
	
	
		$date_edit = $donnees['fin_cotisation_admin_spec'];
		$fin_cotisation_annee = substr($date_edit, 0, 4);
		$fin_cotisation_mois = substr($date_edit, 5, 2);
	
		$lieu_admin_spec = $donnees ['lieu_admin_spec'];
		// On a l'ID, mais le nom est dans la TABLE $table_lieu
		$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$lieu_admin_spec'");
		$donnees = mysql_fetch_array($reponse);
		$nom_lieu_actuel = $donnees ['nom_lieu'];
	
		// ------------------------------------------------
		// Remplissage du formulaire
		// ------------------------------------------------
	
	?>
</p>
	<form name="form1" method="post" action="">
	  <table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
		<tr>
		  <th colspan="2"><?php 
			if (empty ($_GET['id']) OR $_GET['id'] == NULL)
			{ echo 'nouvelle entrée'; }
			else
			{
				echo 'Vous modifiez l\'entrée <b>'.$id.'</b><br />('.$nom_lieu_actuel.')'; 
			}
			?></th>
		</tr>
		<tr>
		  <td>Changer le lieu culturel</td>
		  <td>
		  <?php 
					
			// LISTE déroulante des lieux culturels
			echo '<select name="list_lieux_culturel">';
			
			$reponse = mysql_query("SELECT * FROM $table_lieu ");
			while ($donnees = mysql_fetch_array($reponse))
			{
				// Raccourcir la chaine :
				$nom_lieu_court = $donnees['nom_lieu'] ;
				$max=35; // Longueur MAX de la chaîne de caractères
				$chaine_raccourcie = raccourcir_chaine ($nom_lieu_court,$max); // retourne $chaine_raccourcie
				
				echo '<option value="' . $donnees ['id_lieu'] .'"';		
				// Faut-il pré-sélectionner
				if ($donnees ['nom_lieu'] == $nom_lieu_actuel )
				{
					echo ' selected="selected" ';
				}
				echo '>'.$chaine_raccourcie.'</option>';
			}
			echo '</select>';
			?>		</td>
		</tr>
		<tr>
		  <td>Responsable
		  <?php if (isset ($error_responsable) AND $error_responsable != NULL) {echo $error_responsable ; } ?>
		  </td>
		  <td><input name="nom_admin_spec" type="text" id="nom_admin_spec" value="<?php if (isset($nom_admin_spec)){echo $nom_admin_spec;}?>" size="30" maxlength="40"></td>
		</tr>
		<tr>
		  <td>Adresse e-mail
		  <?php if (isset ($error_e_mail) AND $error_e_mail != NULL) {echo $error_e_mail ; } ?>
		  </td>
		  <td><input name="e_mail_admin_spec" type="text" id="e_mail_admin_spec" value="<?php if (isset($e_mail_admin_spec)){echo $e_mail_admin_spec;}?>" size="30" maxlength="40"></td>
		</tr>
		<tr>
		  <td>T&eacute;l&eacute;phone
		  <?php if (isset ($error_tel) AND $error_tel != NULL) {echo $error_tel ; } ?>
		  </td>
		  <td><input name="tel_admin_spec" type="text" id="tel_admin_spec" value="<?php if (isset($tel_admin_spec)){echo $tel_admin_spec;}?>" size="30" maxlength="30"></td>
		</tr>
		<tr>
		  <td>Login
		  <?php if (isset ($error_log) AND $error_log != NULL) {echo $error_log ; } ?>
		  </td>
		  <td><input name="log_admin_spec" type="text" id="log_admin_spec" value="<?php if (isset($log_admin_spec)){echo $log_admin_spec;}?>" size="30" maxlength="8"></td>
		</tr>
		<tr>
		  <td>Mot de passe (laisser vide pour le garder inchang&eacute;) 
		  <?php if (isset ($error_pw) AND $error_pw != NULL) {echo $error_pw ; } ?>
		  </td>
		  <td><input name="pw_admin_spec" type="password" id="pw_admin_spec" value="" size="8" maxlength="9">		  </td>
		</tr>
			<tr>
		  <td>Confirmer le  mot de passe 
		  <?php if (isset ($error_conf_pw) AND $error_conf_pw != NULL) {echo $error_conf_pw ; } ?>
		  </td>
		  <td>		  <input name="pw_admin_spec_double" type="password" id="pw_admin_spec_double" size="8" maxlength="9"></td>
		</tr>
		
		<tr>
		  <td>Groupe d'autorisation / droits
		  <?php if (isset ($error_groupe) AND $error_groupe != NULL) {echo $error_groupe ; } ?>
		  </td>
		  <td><input name="group_admin_spec" type="text" id="group_admin_spec" value="<?php if (isset($group_admin_spec)){echo $group_admin_spec;}?>" size="30" maxlength="5"></td>
		</tr>
		<tr>
		  <td colspan="2">Adresse de facturation  (n&deg;, rue, code postal, ville, pays) :<br />
		  <?php if (isset ($error_adresse) AND $error_adresse != NULL) {echo $error_adresse ; } ?>
			<input name="adr_factur_admin_spec" type="text" id="adr_factur_admin_spec" value="<?php if (isset($adr_factur_admin_spec)){echo $adr_factur_admin_spec;}?>" size="75" maxlength="230">
		  </td>
		</tr>
		<!--<tr>
		  <td>Ech&eacute;ance de la cotisation (utilis&eacute; pour validation du compte perso) </td>
		  <td>
		  
			MM <input name="fin_cotisation_mois" type="text" id="fin_cotisation_mois" 
			value="<?php if (isset($fin_cotisation_mois)){echo $fin_cotisation_mois;}?>" size="2" maxlength="2">
			
			AAAA <input name="fin_cotisation_annee" type="text" id="fin_cotisation_annee" 
			value="<?php if (isset($fin_cotisation_annee)){echo $fin_cotisation_annee;}?>" size="4" maxlength="4">
			
		 </td>
		</tr> -->
		<tr>
		  <td colspan="2"><div align="center"> <br />
				  <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update">
				  <br />
		  </div></td>
		</tr>
	  </table>
	</form>
 	
<?php 
	}
} 

//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>
