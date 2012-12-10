<!-- -----------------------------------------------------------------
// Afficher formulaire
// ----------------------------------------------------------------- -->

<form name="form1" method="post" action="">
  <table width="500" border="0" align="center" cellpadding="5" cellspacing="1" class="table_public" >
    <tr>
      <td colspan="2" align="center"><strong>Rédigez votre message
        </th>
        </strong></tr>

	<?php 
	// Afficher le pseudo du spectateur, ou un champ à remplir 
    echo '<tr>' ;
	if ($qui_redacteur == 'spectateur')
	{
		echo '<td>Votre pseudo : </td>
		<td> ' . $_SESSION['pseudo_spectateur'] . ' 
		<input name="nom_avis" type="hidden" id="nom_avis" value="' . $_SESSION['pseudo_spectateur'] . '"></td>' ;
	}
	else
	{
		echo '<td>' ;
		if (isset ($error_nom_avis) AND $error_nom_avis != NULL) {echo $error_nom_avis ; }
		echo 'Nom (ou pseudo) <span class="champ_obligatoire">*</span> :	  </td>
      	<td><input name="nom_avis" type="text" id="nom_avis" value="';
		if (isset($nom_avis)){echo $nom_avis;} ;
		echo '" size="30" maxlength="30"></td>' ;
	}
    echo '</tr>' ;
	?>
		
	<?php 
	// Afficher l'adresse email du spectateur, ou un champ à remplir 
    echo '<tr>' ;
	if ($qui_redacteur == 'spectateur')
	{
		echo '<td>Votre adresse e-mail : </td>
		<td> ' . $e_mail_spectateur . ' 
		<input name="email_avis" type="hidden" id="email_avis" value="' . $e_mail_spectateur . '"></td>' ;
	}
	else
	{
		echo '<td>' ;
		if (isset ($error_email_avis_event) AND $error_email_avis_event != NULL) {echo $error_email_avis_event ; }
		echo 'Adresse e-mail<span class="champ_obligatoire">*</span> : </td>
      	<td><input name="email_avis" type="text" id="email_avis" value"';
		if (isset($email_avis)){echo $email_avis;} ;
		echo '" size="30" maxlength="50"></td>' ;
	}
    echo '</tr>' ;
	?>
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
			<a href="Mentions-legales-pour-Demandez-le?page=article-3">conditions g&eacute;n&eacute;rales</a> d'utilisation de demandezleprogramme</label></td>
    </tr>
    <tr>
      <td>
		<?php if (isset ($error_image_crypt) AND $error_image_crypt != NULL) {echo $error_image_crypt ; } ?>
		Recopier le code de l'image<span class="champ_obligatoire">*</span> : </td>
      <td><input name=code type=text id="code" size="3" maxlength="<?php echo $nb_car; ?>">
          <img src=agenda/user_admin/ins/im_gen.php?session=<?php echo $session; ?> hspace="10" align="top">      </td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
	  <br />
              <input type=hidden name=sid value=<?php echo $session; ?>>
              <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
	  <br />
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