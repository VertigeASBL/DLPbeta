
<?php 
// Si bouton enfoncé, alors lancer l'analyse du LOGIN et PW
if (isset($_POST['auth_req']) AND ($_POST['auth_req'] == 'Log')) 
{
	$rec = '';

	// TEST user_log
	if (isset($_POST['user_log']) AND (preg_match('`^\w{4,8}$`', $_POST['user_log']))) // caractères alphanum et underscore.
	{
		$user_log = htmlentities($_POST['user_log'], ENT_QUOTES);
	}
	else
	{
		$user_log = '';
		$rec .= 'Vous devez introduire un LOGIN contenant de 4 à 8 caractères alphanumériques. <br>';
	}

	//  TEST du user_password
	if ((isset($_POST['user_password']) AND (preg_match('`^\w{4,8}$`', $_POST['user_password'])))) // caractères alphanum et underscore.
	{
		$user_password = htmlentities($_POST['user_password'], ENT_QUOTES);
		//$user_password = md5($user_password) ;
	}
	else
	{
		// $user_password = '';
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

		$reponse = mysql_query("SELECT * FROM $table_user_agenda WHERE log_admin_spec = '$user_log'");
		$donnees = mysql_fetch_array($reponse); 
		
		if ($donnees ['log_admin_spec'] == $user_log AND $donnees ['pw_admin_spec'] == $user_password)
		{
		    $_SESSION['nom_admin_spec'] = $donnees ['nom_admin_spec'];
		    $_SESSION['id_admin_spec'] = $donnees ['id_admin_spec'];
			
		    $_SESSION['group_admin_spec'] = $donnees ['group_admin_spec'];
			$_SESSION['group_admin_spec_name'] = $group_admin_spec_noms [$donnees ['group_admin_spec']] ;
			
			// retrouver le nom du lieu culturel :
		    $_SESSION['lieu_admin_spec'] = $donnees ['lieu_admin_spec'] ;
			$lieu_admin_spec = $donnees ['lieu_admin_spec'] ;
			$reponse_2 = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$lieu_admin_spec'");
			$donnees_2 = mysql_fetch_array($reponse_2);
		    $_SESSION['lieu_admin_spec_name'] = $donnees_2 ['nom_lieu'] ;
			
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
      <td><input name="user_log" type="text" id="name" size="9" maxlength="9" /></td>
    </tr>
    <tr>
      <td>Password</td>
      <td><input name="user_password" type="password" id="pw" size="9" maxlength="9" /></td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
        <input name="hiddenField" type="hidden" value="<?php if (isset($page_appel)) echo $page_appel ?>" />
        <input name="auth_req" type="submit" id="auth_req" value="Log" />
      </div></td>
    </tr>
    <tr>
      <td height="50" colspan="2" align="center" valign="bottom"><a href="../user_admin/ins/oubli_pw.php">Mot de passe oubli&eacute; ?</a></td>
    </tr>
  </table>
</form>



<?php 
exit () ;
?>