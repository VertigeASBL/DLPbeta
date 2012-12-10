
<?php 
// Si bouton enfoncé, alors lancer l'analyse du LOGIN et PW
if (isset($_POST['auth_req']) AND ($_POST['auth_req'] == 'Log')) 
{
	$rec = '';

	// TEST log_spectateur
	if (isset($_POST['log_spectateur']) AND (preg_match('`^\w{4,8}$`', $_POST['log_spectateur']))) // caractères alphanum et underscore.
	{
		$log_spectateur = htmlentities($_POST['log_spectateur'], ENT_QUOTES);
	}
	else
	{
		$log_spectateur = '';
		$rec .= 'Vous devez introduire un LOGIN contenant de 4 à 8 caractères alphanumériques. <br>';
	}

	//  TEST du pw_spectateur
	if ((isset($_POST['pw_spectateur']) AND (preg_match('`^\w{4,8}$`', $_POST['pw_spectateur'])))) // caractères alphanum et underscore.
	{
		$pw_spectateur = htmlentities($_POST['pw_spectateur'], ENT_QUOTES);
		//$pw_spectateur = md5($pw_spectateur) ;
	}
	else
	{
		// $pw_spectateur = '';
		$rec .= 'Le MOT DE PASSE doit contenir entre 4 à 8 caractères alphanumériques. <br>';
	}

	//---------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	if ($rec!=NULL)
	{
		echo '<br /><div class="alerte">' . $rec . '</div><br />' ;
	}
	else // Le formulaire est correctement rempli, donc on peut interroger la DB
	{
		require '../inc_db_connect.php';

		$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE log_spectateur = '$log_spectateur'");
		$donnees = mysql_fetch_array($reponse); 
		
		if ($donnees ['log_spectateur'] == $log_spectateur AND $donnees ['pw_spectateur'] == $pw_spectateur)
		{
		    $_SESSION['nom_spectateur'] = $donnees ['nom_spectateur'];
		    $_SESSION['prenom_spectateur'] = $donnees ['prenom_spectateur'];
		    $_SESSION['id_spectateur'] = $donnees ['id_spectateur'];
		    $_SESSION['pseudo_spectateur'] = $donnees ['pseudo_spectateur'];
			
			/*echo $_SESSION['nom_spectateur'] ;
			echo $_SESSION['id_spectateur'] ;*/

		    $_SESSION['group_admin_spec'] = 1 ;
			$_SESSION['group_admin_spec_name'] = $group_admin_spec_noms[1] ;
			
			echo '<META http-equiv="Refresh" content="0">' ; // Rafraichissement pour relancer la page avec les nouvelles $_SESSION
		} 
		else
		{
			echo '<br /><div class="alerte">Le mot de passe et le login ne correspondent pas.</div><br />' ;
		}
	}
}
?>


<form name="form_rec_user" method="post" action="">
  <table  class="data_table" width="200" border="0" align="center" cellpadding="5" cellspacing="0">
    <tr>
      <th colspan="2">Veuillez vous authentifier</th>
    </tr>
    <tr>
      <td>Log in</td>
      <td><input name="log_spectateur" type="text" id="name" size="9" maxlength="9" /></td>
    </tr>
    <tr>
      <td>Password</td>
      <td><input name="pw_spectateur" type="password" id="pw" size="9" maxlength="9" /></td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
        <input name="hiddenField" type="hidden" value="<?php echo $page_appel ?>" />
        <input name="auth_req" type="submit" id="auth_req" value="Log" />
      </div></td>
    </tr>
    <tr>
      <td height="50" colspan="2" align="center" valign="bottom">
	  <p><a href="../spectateurs_admin/ins/oubli_pw.php">Mot de passe oubli&eacute; ?</a></p>
	  <p><a href="../spectateurs_admin/ins/a_1.php">Créer un nouveau compte</a></p>
	  </td>
    </tr>
  </table>
</form>



<?php 
exit () ;
?>