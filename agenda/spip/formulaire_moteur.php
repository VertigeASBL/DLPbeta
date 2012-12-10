
<!-- Calendrier -->
<table width="100%" border="0" cellpadding="2" cellspacing="0">
  <tr>
    <td><div align="center">
    <?php  
	//  CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
	
		affich_jours_spectacles ($MM_traite, $AAAA_traite) ;
	
	// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
	?>
    </div></td>
  </tr>
</table>

<hr />

<!-- Formulaire pour le morteur -->
<form id="form_recherche" name="form_recherche" method="post" action="-Agenda-">
        <table class="form_recherche" border="0" cellpadding="2" cellspacing="0" >
          <tr>
            <td colspan="2">
            <input name="txt" type="text" size="20" maxlength="30" value="<?php if (isset($requete_txt) AND $requete_txt != NULL) {echo $requete_txt ; } else {?>nom de l'événement" 
 onfocus="if (this.value == 'nom de l\'événement') this.value = '';" onblur="if (this.value == '') this.value = 'nom de l\'événement';" <?php } ?>" />
			

 </td>
          </tr>
          <tr>
            <td colspan="2">
            <?php 
					
			// LISTE déroulante des lieux culturels
			
			// sélectionner uniquement ceux qui sont en ordre de paiement
			
			echo '<select name="lieu_event" class="option_col_2">
			<option value="non_selct">tous les lieux/partenaires</option>';
			$reponse_2 = mysql_query("SELECT id_lieu, nom_lieu FROM $table_lieu WHERE cotisation_lieu > CURDATE() ORDER BY nom_lieu");
			while ($donnees_2 = mysql_fetch_array($reponse_2))
			{
				// Raccourcir la chaine :
				$nom_lieu_court = $donnees_2['nom_lieu'] ;
				$max=34; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
				$chaine_raccourcie = raccourcir_chaine_net ($nom_lieu_court,$max); // retourne $chaine_raccourcie
				
				echo '<option value="' . $donnees_2['id_lieu'] .'"';		
				// Faut-il pr&eacute;-s&eacute;lectionner
				if (isset($lieu_event_form) AND $donnees_2['id_lieu'] == $lieu_event_form )
				{
					echo ' selected="selected" ';
				}
				echo '>'.$chaine_raccourcie.'</option>';
			}
			echo '</select>';
			?></td>
          </tr>
          <tr>
            <td colspan="2">
		<?php 
	// Liste d&eacute;roulante des r&eacute;gions
	echo '<select name="ville_event" class="option_col_2">
	<option value="non_selct">toutes les villes</option>';
	foreach($regions as $cle_region => $element_region)
	{
		echo '<option value="' . $cle_region .'"';		
		// Faut-il preselectionner
		if (isset($ville_event) AND $ville_event == $cle_region)
		{
			echo 'selected';
		}
		echo '>'.$element_region.'</option>';
	}
	echo '</select>';
	
	?>            </td>
          </tr>
          <tr>
          <td colspan="2">
		<?php 
	// Liste des GENRES
	echo '<select name="genre_event" class="option_col_2">
	<option value="non_selct">tous les genres</option>';
	foreach($genres as $cle_genre => $element_genre)
	{
		echo '<option value="' . $cle_genre .'"';		
		// Faut-il preselectionner
		if (isset($genre_event) AND $genre_event == $cle_genre)
		{
			echo 'selected';
		}
		$max=34; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
		$element_genre = raccourcir_chaine ($element_genre,$max); // retourne $chaine_raccourcie
		echo '>'.$element_genre.'</option>';
	}
	echo '</select>';
	
	?>            </td>
          </tr>
          <tr>
            <td>Date début</td>
            <td><?php 
		// Pré-remplire les champs de dates
		if (isset ($saisie_date_1) AND $saisie_date_1 != NULL ) // valeurs déjà envoyées par formulaire
		{		
			// echo '<br>-- '.$saisie_date_1.'-- <br>-- '.$saisie_date_2.'-- <br> ' ;
			$date_debut_defaut = $saisie_date_1 ;
			$date_fin_defaut = $saisie_date_2 ;
		}
		else // sinon, mettre date actuelle
		{
			$date_debut_defaut = date("d").'-'.date("m").'-'.date("Y"); // Aujourd'hui
			$date_fin_defaut = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m")+6, date("d"), date("Y"))); // une semaine plus tard
		}

?>
              <input name="saisie_date_1" id="saisie_date_1" type="text" size="12" value="<?php echo $date_debut_defaut ; ?>" />
              &nbsp;<a href="#calendrier" onclick="return calendatp('saisie_date_1');"><img src="agenda/calendate/calendp.gif" class="cmsicmg" alt="" title="calendrier" /></a></td>
          </tr>
          <tr>
            <td>Date fin</td>
            <td><input name="saisie_date_2" id="saisie_date_2" type="text" size="12" value="<?php echo $date_fin_defaut ; ?>" />
              &nbsp;<a href="#calendrier" onclick="return calendatp('saisie_date_2');"><img src="agenda/calendate/calendp.gif" class="cmsicmg" alt="" title="calendrier" /></a></td>
          </tr>
          <tr>
            <td colspan="2">     <div align="center"><br />
			
	<input id="go" name="go" value="Lancer la recherche" class="go_recherche" type="submit" alt="Cliquez pour lancer la recherche">
		  
        <br /></div></td>
          </tr>
  </table>

</form>

