<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition d'une fiche concours </title>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>


<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../fct_upload_vign_concours.php';

$indetermine = '' ;

$id_conc = htmlentities($_GET['id_conc'], ENT_QUOTES);
$id_lot = htmlentities($_GET['lot'], ENT_QUOTES);


?>
<div id="head_admin_agenda"></div>
<h1>Edition d'une fiche concours </h1>

<div class="menu_back">
<?php echo '<a href="conc_2_edit_a.php?id_conc='.$id_conc.'">D&eacute;tail du  concours  </a>' ; ?> | 
<a href="conc_2_listing.php" >Listing des concours  </a> | 
<a href="index_admin.php">Menu Admin</a></div>
<?php


//--------------------------------------------------------------------------------------------------------------
// UPDATE d'une entrée
//--------------------------------------------------------------------------------------------------------------

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'update'))
{

	//-----------------------------------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//-----------------------------------------------------------------------------------

	$rec = '';
	
	
	// -----------------------------------------
	// TEST Intitulé du LOT pour site
	if (isset($_POST['decription_lot']) AND ($_POST['decription_lot'] != NULL)) 
	{
		$decription_lot = htmlentities($_POST['decription_lot'], ENT_QUOTES);
	}
	else
	{
		$decription_lot = $indetermine;
		$error_decription_lot = '<div class="error_form">Vous devez mettre une description de ce LOT</div>';
		$rec .= '- Vous devez mettre une description de ce LOT<br>';
	}

	
	// -----------------------------------------
	// TEST Intitulé du LOT pour e-mail
	if (isset($_POST['txt_mail_lot']) AND ($_POST['txt_mail_lot'] != NULL)) 
	{
		$txt_mail_lot = htmlentities($_POST['txt_mail_lot'], ENT_QUOTES);
	}
	else
	{
		$txt_mail_lot = $indetermine;
		$error_txt_mail_lot = '<div class="error_form">Vous devez mettre une description de ce LOT</div>';
		$rec .= '- Vous devez mettre une description de ce LOT<br>';
	}


	// -----------------------------------------
	// TEST du Groupe de joueurs 
	if (isset($_POST['groupe_joueur']) AND ($_POST['groupe_joueur'] != NULL))	
	{
		$groupe_joueur = htmlentities($_POST['groupe_joueur'], ENT_QUOTES);
		$groupe_lot = $groupe_joueur ;
	}
	else
	{
		$groupe_joueur = $indetermine;
		$error_groupe_joueur = '<div class="error_form">Vous devez mettre une description de ce LOT</div>';
		$rec .= '- Vous devez sélectionner un groupe de joueurs<br>';
	}


	// -----------------------------------------
	// TEST du nombre d'unités à gagner 
	if (isset($_POST['nombre_places']) AND ($_POST['nombre_places'] != NULL)
	AND preg_match('/[0-9]$/', $_POST['nombre_places']))
	{
		$nombre_places = htmlentities($_POST['nombre_places'], ENT_QUOTES);
	}
	else
	{
		$nombre_places = $indetermine;
		$error_nombre_places = '<div class="error_form">Vous devez choisir le numbre d\'unités à mettre en jeu (correspond au nombre de gagnants)>/div>';
		$rec .= '- Vous devez choisir le numbre d\'unités à mettre en jeu (correspond au nombre de gagnants)<br>';
	}



	//-----------------------------------------------------------------------------------------------------------
	// Traitement du résultat des données entrées par l'utilateur
	//---------------------------------------------------------
	// Update des données
	//---------------------------------------------------------
	if ($rec == NULL) // Enregistrement les données dans la DB 
	{
		// ----------------------------------------------------
		// reconstruire la variable array contenant les LOTS
		// ----------------------------------------------------
				
		// Enlever les caractères qui pourraient poser problème lors de la mise en Array
		$find = '\\';
		$replace = "-";
		$decription_lot = strtr($decription_lot,$find,$replace);
		$decription_lot = stripslashes($decription_lot);
		$txt_mail_lot = strtr($txt_mail_lot,$find,$replace);
		$txt_mail_lot = stripslashes($txt_mail_lot);

				
		$new_array_lot = array (
		"decription_lot" => $decription_lot,
		"txt_mail_lot" => $txt_mail_lot,
		"groupe_joueur" => $groupe_joueur,
		"nombre_places" => $nombre_places ) ;
		
		$new_array_lot = array ($new_array_lot) ; 


		$remplace_lot_db = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
		$donnees_remplace_lot_db = mysql_fetch_array($remplace_lot_db) ;
		$contenu_lots_conc = $donnees_remplace_lot_db['lots_conc'] ;
	
		if (isset($contenu_lots_conc) AND ($contenu_lots_conc != NULL))
		{
			$unserialized_contenu_lots_conc = unserialize($contenu_lots_conc) ; // récupération de la variable Lot de la DB
			//var_dump($contenu_lots_conc);
				
			/*echo '<pre>';
			print_r($unserialized_contenu_lots_conc);
			echo '</pre>';*/
	
			array_splice ($unserialized_contenu_lots_conc, $id_lot,1,$new_array_lot); // array array_splice ( array $input, int $offset [, int $length [, array $replacement]] )    http://be.php.net/manual/fr/function.array-splice.php
			
			/*echo '<pre>';
			print_r($unserialized_contenu_lots_conc);
			echo '</pre>';*/
			
			
			$new_array_lot_serialized = serialize ($unserialized_contenu_lots_conc) ;
			
			//var_dump ($new_array_lot_serialized) ;
		
			$approuv_update_lot = mysql_query("UPDATE $table_ag_conc_fiches SET
			lots_conc = '$new_array_lot_serialized'
			WHERE id_conc = '$id_conc' LIMIT 1 ") or print($approuv_update_lot . " -- update du LOT -- " . mysql_error());
			
			if ($approuv_update_lot)
			{ 
				echo '<div class="info">Les modifications ont bien été enregistrées</div>';
			}
			else
			{
				$lot_conc = $indetermine;
				/*
				$error_lot_conc = '<div class="error_form">La partie de sélection du lot est mal remplie</div>';
				$rec .= '- La partie de sélection du lot est mal remplie<br>';
				*/
			}
		}
		else
		{
			echo 'ERREUR DE VARIABLE LOT' ;
		}
	}
	else // Il y a au moins un champ du formulaire qui est mal rempli
	{
		echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
	}
	// Réintroduire variables dans le formulaire en enlevant les "\"
	$nom_event_conc = stripslashes ($donnees_remplace_lot_db['nom_event_conc']) ;
}

else // Si on n'a pas appuyé sur le bouton UPDATE -> récupérer les données de la DB
{

	// ------------------------------------------------
	// Lecture des infos de la DB pour cette entrée
	// ------------------------------------------------
	
	$reponse_conc_fiches = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
	$donnees_conc_fiches = mysql_fetch_array($reponse_conc_fiches) ;
	
	$nom_event_conc = stripslashes ($donnees_conc_fiches['nom_event_conc']) ;
	$mail_lieu_conc = $donnees_conc_fiches['mail_lieu_conc'] ;
	$pic_conc = $donnees_conc_fiches['pic_conc'] ;
	$description_conc = $donnees_conc_fiches['description_conc'] ;
	$adresse_conc = stripslashes ($donnees_conc_fiches['adresse_conc']) ;
	
	$lots_conc = $donnees_conc_fiches['lots_conc'] ;
	$tetete = unserialize($lots_conc) ; // récupération de la variable Lot de la DB
	$nombre_places = $tetete[$id_lot]['nombre_places'] ;
	$groupe_lot = $tetete[$id_lot]['groupe_joueur'] ;

		/*echo '<pre>';
		print_r($tetete);
		echo '</pre>';*/
		
	$cloture_conc = $donnees_conc_fiches['cloture_conc'] ;
	
	$flags_conc = $donnees_conc_fiches['flags_conc'] ;
	
}

?>
<table width="800" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td valign="top"><form name="form1" method="post" action=""  enctype="multipart/form-data" >
        <table width="300" border="1" align="center" cellpadding="10" cellspacing="0" class="data_table" >
          <tr>
            <th><?php 
			
			echo '<p>Concours : ' . $nom_event_conc . 
			' (id ' . $id_conc . ')<br />' ;
			
			echo 'Lot numéro ' . $id_lot . '</p>' ;

			?></th>
          </tr>
          <tr>
            <td bgcolor="#DDDDDD"><p align="left">
			
			  <?php 
			$reponse_conc_lot = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE `id_conc` = '$id_conc'");
			$donnees_conc_lot = mysql_fetch_array($reponse_conc_lot) ;
			if (isset($donnees_conc_lot['lots_conc']) AND ($donnees_conc_lot['lots_conc'] != NULL))
			{
				$var_lot_unserialized = unserialize($donnees_conc_lot['lots_conc']) ;
				
				$element_lot = $var_lot_unserialized[$id_lot] ; // Sélection du LOT en cours

				echo str_pad($element_lot['nombre_places'], 3, "0", STR_PAD_LEFT) . ' unité(s) à gagner ';

				echo ' pour le groupe "';
				echo $groupes_joueurs[$element_lot['groupe_joueur']] . '"<br />' ; 
				
				
				$decription_lot = stripslashes ($element_lot['decription_lot']) ;
				$txt_mail_lot = stripslashes ($element_lot['txt_mail_lot']) ;
				echo '<i>' . $decription_lot . '</i> </p>  ' ;


				/*print_r($element_lot);
				echo '<br /><br /><br />' ;*/

				
				/*echo '<pre>';
				print_r($var_lot_unserialized);
				echo '</pre>';*/
				
				//var_dump ($var_lot_unserialized) ;				
			}
			?>
			</td>
          </tr>
          
		
		
		<?php
		// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP
		// SI "concours ACTIF", empêcher les modifs de LOTS  
		 if (888==999) //(preg_match('!actif!', $flags_conc)) 
		{		
			echo '<tr><td colspan="2"><p class="rouge">Le concours est ACTIF. Impossible de modifier les LOTS</p></td></tr>' ;
		}
		else
		{ ?>
				  
		  <tr>
		    <td><p>
			
			<?php // Texte descriptif du LOT pour afficher sur le SITE
			 if (isset ($error_decription_lot) AND $error_decription_lot != NULL) {echo $error_decription_lot ; } ?>
			Descriptif du &quot;Lot&quot;<br>
			Exemple : 5 places pour le 25/12/2007 &agrave; 21h30<br />
			 
<input name="decription_lot" type="text" id="decription_lot" value="<?php echo $decription_lot ; ?>" size="80" maxlength="180">
		    </p>
			
			
			<?php // Texte descriptif du LOT pour l'email envoyé aux gagnants et aux partenaires offrant les places. 
			 if (isset ($error_txt_mail_lot) AND $error_decript_lot != NULL) {echo $error_txt_mail_lot ; } ?>
			Descriptif du &quot;Lot&quot;<br>
			Exemple : 5 places pour le 25/12/2007 &agrave; 21h30<br />
			 
<input name="txt_mail_lot" type="text" id="txt_mail_lot" value="<?php echo $txt_mail_lot ; ?>" size="80" maxlength="180">
		    </p>
			
			
		      <p>
                Groupe de joueurs :
                <?php 
					
			// Liste des GROUPES de joueurs pouvant participer aux concours
			echo '<select name="groupe_joueur">';
		foreach($groupes_joueurs as $cle_groupes_joueurs => $element_groupes_joueurs)
		{
			echo '<option value="' . $cle_groupes_joueurs .'"';		
			// Faut-il preselectionner
			if ($groupe_lot == $cle_groupes_joueurs)
			{
				echo 'selected="selected"';
			}
			$max=20; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
			$element_groupes_joueurs = raccourcir_chaine ($element_groupes_joueurs,$max); // retourne $chaine_raccourcie
			echo '>'.$element_groupes_joueurs.'</option>';
		}
		echo '</select><br /></p>';
				
			
			//  Nombre de places :
			
				
                echo '<p>Nombre d\'unités à gagner : <input name="nombre_places" type="text" id="nombre_places" value="'
				. $nombre_places . '" size="3" maxlength="3">' ;
				?>
				
              </p></td>
          </tr>
          <?php // introduire une rang&eacute;e pour le message d'erreur
				if (isset ($error_date) AND $error_date != NULL)
				{
					echo '<tr><td colspan="2" align="center">' . $error_date . ' </td></tr>'; 
				}
				?>
				
	<?php 
	// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP
	// Fin de partie à enlever si "concours actif"
	}
	?>
          <tr>
            <td><div align="center"> 
              
                
                <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update">				  
              <br /><a href="conc_2_edit_a?id_conc=<?php echo $id_conc ; ?> ">Retour &agrave; la fiche   concours</a><br />
                </div>
            </td>
          </tr>
          
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
      </form></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
