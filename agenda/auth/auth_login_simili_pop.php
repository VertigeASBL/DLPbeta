<?php 

include_spip('inc/utils');

$cacher_formulaire = false ;
require 'agenda/inc_var.php';

// ---------------------------------------------------------------
// Si bouton enfoncé, alors lancer l'analyse du LOGIN et PW
// ---------------------------------------------------------------
if (isset($_POST['auth_req']) AND ($_POST['auth_req'] == 'Envoyer')) 
{
	$rec = '';
	echo '<div id="hold_open_form_dlp">1</div>' ;


	// TEST log_spectateur
	if (isset($_POST['log_spectateur']) AND $_POST['log_spectateur'] != NULL)
	{
		$log_spectateur = htmlentities($_POST['log_spectateur'], ENT_QUOTES);
	}
	else
	{
		$log_spectateur = '';
		$rec .= 'Vous devez introduire votre LOGIN ou votre Pseudo<br>';
	}
	//  TEST du pw_spectateur
	if ((isset($_POST['pw_spectateur']) AND (preg_match('`^\w{4,8}$`', $_POST['pw_spectateur'])))) // caractères alphanum et underscore.
	{
		$pw_spectateur = htmlentities($_POST['pw_spectateur'], ENT_QUOTES);
		//$pw_spectateur = md5($pw_spectateur) ;
	}
	else
	{
		$pw_spectateur = '';
		$rec .= 'Le MOT DE PASSE doit contenir entre 4 à 8 caractères alphanumériques. <br>';
	}

	//---------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	if ($rec!=NULL)
	{
		echo '<div class="alerte">' . $rec . '</div>' ;
	}
	else // Le formulaire est correctement rempli, donc on peut interroger la DB
	{
		require 'agenda/inc_db_connect.php';
		include_spip('inc/session');

		$reponse = mysql_query("SELECT * FROM ag_spectateurs 
		WHERE (log_spectateur = '$log_spectateur' OR pseudo_spectateur = '$log_spectateur')
		") or die (mysql_error());
		$donnees = mysql_fetch_array($reponse); 
		
		if ( ($donnees['log_spectateur'] == $log_spectateur OR $donnees['pseudo_spectateur'] == $log_spectateur) 
		AND $donnees['pw_spectateur'] == $pw_spectateur)
		{
			$_SESSION['nom_spectateur'] = $donnees ['nom_spectateur'];
			$_SESSION['prenom_spectateur'] = $donnees ['prenom_spectateur'];
			$_SESSION['id_spectateur'] = $donnees ['id_spectateur'];
			$_SESSION['pseudo_spectateur'] = $donnees ['pseudo_spectateur'];
			
			/*echo $_SESSION['nom_spectateur'] ;
			echo $_SESSION['id_spectateur'] ;*/

			$_SESSION['group_admin_spec'] = 1 ;
			$_SESSION['group_admin_spec_name'] = $group_admin_spec_noms[1] ;

		    session_set('nom_spectateur', $donnees ['nom_spectateur']);
		    session_set('prenom_spectateur', $donnees ['prenom_spectateur']);
		    session_set('id_spectateur', $donnees ['id_spectateur']);
		    session_set('pseudo_spectateur', $donnees ['pseudo_spectateur']);
		    session_set('group_admin_spec', 1);
		    session_set('group_admin_spec_name', $group_admin_spec_noms[1]);

			//--- mysql_close($db2dlp);
			
			//echo '<META http-equiv="Refresh" content="0">' ; // Rafraichissement pour relancer la page avec les nouvelles $_SESSION

			// Bouton pour fermer la fenêtre :
			echo ' <br /> <br /><div class="info">Bonjour ' . $donnees ['pseudo_spectateur'] . '</div><br /> <br />';		

			$cacher_formulaire = true ;
			echo '<div id="fermer_form_ok_log">1</div>' ;

		} 
		else
		{
			echo '<div class="alerte">Le mot de passe et le login ne correspondent pas.</div>' ;
		}
	}

}


// Formulaire
if (!$cacher_formulaire)
{
	
	echo '
<form name="form_rec_user" method="post" action="">
  <div id="close_form_log_dlp">X</div><br />

  <table  class="spect_login_table" border="0" align="center" cellpadding="5" cellspacing="0">
	<tr>
	  <th colspan="2" bgcolor="#009A99">
	  Je me connecte à <br />mon compte Spectateur <br /></th>
	</tr>
	<tr>
	  <td> <br />Login ou Pseudo</td>
	  <td> <br /><input name="log_spectateur" type="text" id="name" size="9" maxlength="9" 
	  value="';
	  if (isset($log_spectateur)){echo $log_spectateur;} 
	  echo '" />
	</tr>
	<tr>
	  <td>Mot de passe</td>
	  <td><input name="pw_spectateur" type="password" id="pw" size="9" maxlength="9" 
	  value="';
	  if (isset($pw_spectateur)){echo $pw_spectateur;} 
	  echo '" />
	  </td>
	</tr>
	<tr>
	  <td colspan="2"><div align="center">
		<input name="hiddenField" type="hidden" value="<?php echo $page_appel ?>" />
		<input name="auth_req" type="submit" id="auth_req" value="Envoyer" />
	  </div></td>
	</tr>
	<tr>
	  <td height="50" colspan="2" align="center" valign="bottom">
	  
	  <p><a href="',generer_url_entite(158,'rubrique'),'">Mot de passe oublié ?</a></p>
	  
	  <p><a href="',generer_url_entite(159,'rubrique'),'">Créer un nouveau compte ?</a></p>
	  
	  </td>
	</tr>
  </table>
</form>';

}


 
?>
