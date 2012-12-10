<?php 
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>S&eacute;lection des jours de repr&eacute;sentation de l'&eacute;v&eacute;nement</title>
<link href="../css_calendrier.css" rel="stylesheet" type="text/css" />
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php
require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?>

<div id="head_admin_agenda"></div>

<h1>S&eacute;lection des jours de repr�sentation de l'�v�nement</h1>

<?php 
require '../calendrier/inc_calendrier.php';
require '../inc_var.php';
require '../inc_db_connect.php';


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage des cases � cocher dans le calendrier
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function mettre_check_box ($jours_actifs, $MM_traite, $AAAA_traite)
{
	global $date_event_debut;
	global $date_event_fin;	
	$date_event_debut_condition = str_replace("-","",$date_event_debut); 
	$date_event_fin_condition = str_replace("-","",$date_event_fin); 
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		// Composer la chaine qui sera cherch�e dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Compl�te la cha�ne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Compl�te la cha�ne
		$date_traite = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
		settype($JJ_traite, "integer"); // Pour �viter probl�mes avec les nombres pr�c�d�s de "0"

		$date_traite_condition = str_replace("-","",$date_traite); 

		// jour HORS p�riode
		if (($date_traite < $date_event_debut)OR($date_traite > $date_event_fin))
		{
			//echo $date_traite_condition .' - ' .$date_event_debut_condition .'<br>';
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day nonchecked',$JJ_traite);
		}
		
		// jour ACTIF
		elseif (in_array($date_traite, $jours_actifs))
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day checked','<input name="'.$date_traite.'" type="checkbox" id="'
			.$date_traite.'" value="'.$date_traite.'" checked="checked" />'.$JJ_traite);
		}

		// jour NON actif
		else
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day unchecked','<input name="'.$date_traite.'" type="checkbox" id="'
			.$date_traite.'" value="'.$date_traite.'" />'.$JJ_traite);
		}
	}
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1); // Affichage du calendrier
	echo '<br />' ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction des lecture des cases � cocher du calendrier
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function lire_check_box ($jours_actifs, $MM_traite, $AAAA_traite)
{ 
	// Lire le les checkbox coch�es et en faire une chaine
	global $comp_chaine_date ;
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		// Composer la chaine qui sera cherch�e dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Compl�te la cha�ne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Compl�te la cha�ne
		$date_traite = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
		//settype($JJ_traite, "integer"); // Pour �viter probl�mes avec les nombres pr�c�d�s de "0"
				
		if (isset ($_POST[$date_traite]))
		{
			// echo $_POST[$date_traite] . ' <-----> ' . $date_traite . '<br>';
			$comp_chaine_date.= $_POST[$date_traite] . ','; 
		}
	} // echo $comp_chaine_date ;
}
// ----------------------------------------------------------------------------------------------

if (empty ($_GET['id']) OR $_GET['id'] == NULL) // La variable GET qui donne l'ID � confirmer. 
{
	echo '<br><br><br><div class="alerte"><p>error : GET id absent </p></div><br>' ;

}
else
{
	$id = htmlentities($_GET['id'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees = mysql_fetch_array($reponse);

	$reponse_test = mysql_query("SELECT lieu_event FROM $table_evenements_agenda WHERE id_event = '$id'");
	$donnees_test = mysql_fetch_array($reponse_test); 

	// Si la valeur de $_GET['id'] ne correspond � aucune entr�e de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Cette entr�e n\'existe pas</div><br>' ;
	}

	// tester si cet �v�nement peut �tre �dit� par ce USER
	elseif ($donnees_test['lieu_event'] != $_SESSION['lieu_admin_spec']) 
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>
		<div class="alerte">Vous ne pouvez pas modifier un �v�nement rattach� � un autre lieu culturel</div><br>' ;
		exit () ;
	}	
	
	else
	{
		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entr�e
		// ------------------------------------------------
		
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id'");
		$donnees = mysql_fetch_array($reponse);
		
		$nom_event = $donnees ['nom_event'];
		$date_event_debut = $donnees ['date_event_debut'];
		$date_event_fin = $donnees ['date_event_fin'];

		$AAAA_debut = substr($date_event_debut, 0, 4);
		$AAAA_fin = substr($date_event_fin, 0, 4);
		$MM_debut = substr($date_event_debut, 5, 2);
		$MM_fin = substr($date_event_fin, 5, 2);
		$JJ_debut = substr($date_event_debut, 8, 2);
		$JJ_fin = substr($date_event_fin, 8, 2);
		$AAAA_MM_debut = substr($date_event_debut, 0, 7);


		$jours_actifs_event = $donnees ['jours_actifs_event'];
		$jours_actifs_event = explode(",", $jours_actifs_event);

		// ------------------------------------------------
		// Remplissage du formulaire
		// ------------------------------------------------
 
				
		// *****************************************************************************************************************
		
		// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
		
		$AAAA_traite = substr($date_event_debut, 0, 4);
		$MM_traite = substr($date_event_debut, 5, 2);
		$AAAA_MM_traite = substr($date_event_debut, 0, 7);
		
		// ------------------------------------------------------------------------------------------------------
		
		// Si appuy� sur bouton "Enregistrer"
		$comp_chaine_date = ''; // Initialisation de la variable qui contiendra la chaine des dates
		
		if (isset($_POST['modif_form']) AND ($_POST['modif_form'] == 'Enregistrer'))
		{
			// --------------------------------------------------------------------
			// -----------------------  Lire les Checkbox   -----------------------
			// --------------------------------------------------------------------
		
			// [A] Si p�riode comprise dans le m�me mois : traiter les jours de JJ_debut � JJ_fin
			if (($MM_debut == $MM_fin) && ($AAAA_debut == $AAAA_fin))
			{
				// echo  '<b> [A] P�riode couvrant 1 mois unique. Mois trait� = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite . '</b><br>' ;
				$AAAA_traite = $AAAA_debut ;
				$MM_traite = $MM_debut ;
				
				lire_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			}
			
			// ------------------------------------------------------------------------------------------------------
			else
			{
				// [B1] si la p�riode s'�tend sur plusieurs mois, afficher 1 calendrier � chaque passage dans la boucle. 
				// Commencer par traiter le mois de d�but de p�riode
				$AAAA_MM_traite = substr($date_event_debut, 0, 7);
				// echo '<b>[B1] Mois trait� (1er mois de la p�riode) = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite . '</b><br>' ;
				
				$tableau_jours = array() ;	
			
				lire_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			
				// Incr�menter le mois :		
				if	($MM_traite == 12)
				{
					$MM_traite = 1 ;
					$AAAA_traite = $AAAA_traite + 1 ;
				}
				else
				{
					$MM_traite = $MM_traite + 1 ;
				}
			
				// -------------------------------------------------------------------------------------------------
				// [B2] traiter tous les mois suivants jusqu'� ce qu'on arrive au mois de fin de PERIODE
				// La boucle s'arr�te quand (($MM_traite == $MM_debut) && ($AA_fin == $AAAA_traite))
			
				while (($MM_traite != $MM_fin) OR ($AAAA_traite != $AAAA_fin))
				{
					/*unset ($tableau_jours[$JJ_db]);	*/
					$tableau_jours = array() ;
					// echo  '<b>[B2] Mois "suivant" trait� = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite.'</b><br>' ;
					
					lire_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			
					// Incr�menter le mois :		
					if	($MM_traite == 12)
					{
						$MM_traite = 1 ;
						$AAAA_traite = $AAAA_traite + 1 ;
					}
					else
					{
						$MM_traite = $MM_traite + 1 ;
					}
				}
				// -------------------------------------------------------------------------------------------------
				// [B3] traiter le dernier mois de JJ = 1 � JJ = JJ_fin
				$tableau_jours = array() ;
				$AAAA_MM_traite = substr($date_event_fin, 0, 7);
			
				// echo  '<b> [B3] Mois trait� (Dernier mois de la p�riode) = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite . '</b><br>' ;
			
				lire_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			}
			
		
			$comp_chaine_date = htmlentities($comp_chaine_date, ENT_QUOTES);
			mysql_query("UPDATE `$table_evenements_agenda` SET `jours_actifs_event` = '$comp_chaine_date' WHERE `id_event` = '$id' LIMIT 1 ");	
			$jours_actifs_event = explode(",", $comp_chaine_date) ; // Pour si on veut afficher les calendriers � la suite
		
			echo '<br /><br /><div class="info"><br />Les changements ont bien �t� enregistr�s. <br /></div><br />
			
			<p align="center" class="mini_info">Si vous n\'�tes pas redirig� automatiquement, cliquez 
			<a href="listing_events_gp.php?lieu=' . $donnees_test['lieu_event'] . '">ici</a></p>
			
			<meta http-equiv="refresh" content="1; url=listing_events_gp.php?lieu=' . $donnees_test['lieu_event'] . '">' ; 
			
		}
		
		
		// Si le bouton ENREGISTRER n'a pas �t� enfonc�, alors, visualiser le calendrier
		else
		{
			// --------------------------------------------------------------------
			// ----------------- AFFICHER TABLE AVEC CALENDRIERS ------------------
			// --------------------------------------------------------------------
			?>
		<p align="center">&nbsp;</p>
		<p align="center">Veuillez cocher les jours pendant lesquels cet &eacute;v&eacute;nement aura lieu.</p>
		<p align="center">&nbsp;</p>
		<form id="cases_jours" name="cases_jours" method="post" action="">
		  <table border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
			<tr>
			  <th> <?php 
				echo  $nom_event . '<br>' ;
				echo 'Date de d�but = <i>'. $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut . '</i><br>' ;
				echo 'Date de fin = <i>'. $JJ_fin . '-' . $MM_fin . '-' . $AAAA_fin . '</i><br>' ;
				?></th>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><?php 
			// [A] Si p�riode comprise dans le m�me mois : traiter les jours de JJ_debut � JJ_fin
			if (($MM_debut == $MM_fin) && ($AAAA_debut == $AAAA_fin))
			{
				// echo  '<b> [A] P�riode couvrant 1 mois unique. Mois trait� = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite . '</b><br>' ;
				$AAAA_traite = $AAAA_debut ;
				$MM_traite = $MM_debut ;
				
				mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			}
			
			// ------------------------------------------------------------------------------------------------------
			else
			{
				// [B1] si la p�riode s'�tend sur plusieurs mois, afficher 1 calendrier � chaque passage dans la boucle. 
				// Commencer par traiter le mois de d�but de p�riode
				$AAAA_MM_traite = substr($date_event_debut, 0, 7);
				$AAAA_traite = $AAAA_debut ;
				$MM_traite = $MM_debut ;
				// echo '<b>[B1] Mois trait� (1er mois de la p�riode) = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite . '</b><br>' ;
				
				$tableau_jours = array() ;	
			
				mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			
				// Incr�menter le mois :		
				if	($MM_traite == 12)
				{
					$MM_traite = 1 ;
					$AAAA_traite = $AAAA_traite + 1 ;
				}
				else
				{
					$MM_traite = $MM_traite + 1 ;
				}
			
				// -------------------------------------------------------------------------------------------------
				// [B2] traiter tous les mois suivants jusqu'� ce qu'on arrive au mois de fin de PERIODE
				// La boucle s'arr�te quand (($MM_traite == $MM_debut) && ($AA_fin == $AAAA_traite))
			
				while (($MM_traite != $MM_fin) OR ($AAAA_traite != $AAAA_fin))
				{
					/*unset ($tableau_jours[$JJ_db]);	*/
					$tableau_jours = array() ;
				
					//echo  '<b>[B2] Mois "suivant" trait� = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite.'</b><br>' ;
					
					mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			
					// Incr�menter le mois :		
					if	($MM_traite == 12)
					{
						$MM_traite = 1 ;
						$AAAA_traite = $AAAA_traite + 1 ;
					}
					else
					{
						$MM_traite = $MM_traite + 1 ;
					}
				}
				// -------------------------------------------------------------------------------------------------
				// [B3] traiter le dernier mois de JJ = 1 � JJ = JJ_fin
				$tableau_jours = array() ;
				$AAAA_MM_traite = substr($date_event_fin, 0, 7);
			
				//echo  '<b> [B3] Mois trait� (Dernier mois de la p�riode) = '.$MM_traite.' et Ann�e trait�e = '.$AAAA_traite . '</b><br>' ;
			
				mettre_check_box ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
			}
			
			?>
			  </td>
			</tr>
			<tr>
			  <td><div align="center"><br />
				  <input name="modif_form" type="submit" id="modif_form" value="Enregistrer">
				  <br />
				</div></td>
			</tr>
		  </table>
		</form>
		<br />
		
		<table border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#FFFFFF">
          <tr>
            <td class="calendar-month" align="center">Legende :</td>
          </tr>
          <tr>
            <td class="checked">Jour de repr&eacute;sentation</td>
          </tr>
          <tr>
            <td class="unchecked">Pas de repr&eacute;sentation</td>
          </tr>
          <tr>
            <td class="nonchecked">Hors p&eacute;riode de repr&eacute;sentation</td>
          </tr>
        </table>
		<?php 
		}	

// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC

	}
	//--- mysql_close($db2dlp);
} 

?>
<p>&nbsp;</p>
</body>
</html>
