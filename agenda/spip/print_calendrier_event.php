<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex,nofollow" />

  <?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage du calendrier avec cases colorées en fonction des jours actifs
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function affich_jours_actifs ($jours_actifs, $MM_traite, $AAAA_traite)
{
	global $date_event_debut;
	global $date_event_fin;	
	$date_event_debut_condition = str_replace("-","",$date_event_debut); 
	$date_event_fin_condition = str_replace("-","",$date_event_fin); 
	
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		// Composer la chaine qui sera cherchée dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		$date_traite = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
		settype($JJ_traite, "integer"); // Pour éviter problèmes avec les nombres précédés de "0"

		$date_traite_condition = str_replace("-","",$date_traite); 

		// jour HORS période
		if (($date_traite < $date_event_debut)OR($date_traite > $date_event_fin))
		{
			//echo $date_traite_condition .' - ' .$date_event_debut_condition .'<br>';
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day nonchecked',$JJ_traite);
		}
		
		// jour ACTIF
		elseif (in_array($date_traite, $jours_actifs))
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day checked',$JJ_traite);
		}
		else
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day unchecked',$JJ_traite);
		}
	}
	echo '<span class ="alignLeftMargin">' ;
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1); // Affichage du calendrier
	echo '</span>' ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


if (empty ($_GET['id_event']) OR $_GET['id_event'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="index.php" >Retour</a></div>' ;
	exit() ;
}
else
{
	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id_event'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>
		<a href="index.php" >Retour</a></div>' ;
		exit() ;
	}
}



		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entrée
		// ------------------------------------------------
		
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$id_event'");
		$donnees = mysql_fetch_array($reponse);	
	
		$lieu_event = $donnees ['lieu_event'];
		$nom_event = $donnees ['nom_event'];
		$ville_event = $donnees ['ville_event'];
		$description_event = $donnees ['description_event'];
		$genre_event = $donnees ['genre_event'];
		$pic_event_1 = $donnees ['pic_event_1'];
		
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


		// TABLE LIEU
		$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $lieu_event");
		$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
		$nom_lieu = $donnees_lieu['nom_lieu'] ;



echo '<title> ::: Demandez le programme : '. $nom_event .' -- ' . $nom_lieu  . ' : Dates de représentation ::: </title>' ;
?>
<link href="../css_impression_agenda.css" rel="stylesheet" type="text/css" />
</head>
<body onLoad="window.print()">

<?php
		// ------------------------------------------------
		// Affichage contenu de l'événement
		// ------------------------------------------------

?>
<div class="print_style_conteneur">

<?php require 'print_head.php'; ?>

<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td>
	
	<?php

		// NOM EVENEMENT (titre)
		echo '<div class="print_style_nom_event">' . $nom_event . '<br />
		Le calendrier de l\'événement</div>';
	
		// ID
		echo '<span class="id_print_style">(id ' . $id_event . ')</span>' ;

		// LIEU
		echo '<br /> <br /> <span class="print_style_rubriques"> Où ? </span>' . $nom_lieu ;	

		// GENRE
		if (isset($genre_event) AND ($genre_event != NULL)) 
		{
			echo '<br /><span class="print_style_rubriques">Genre : </span>' . $genres[$genre_event];	
		}
		
		// DATES
		$date_event_debut_annee = substr($date_event_debut, 0, 4);
		$date_event_debut_mois = substr($date_event_debut, 5, 2);
		$date_event_debut_jour = substr($date_event_debut, 8, 2);
		
		$date_event_fin_annee = substr($date_event_fin, 0, 4);
		$date_event_fin_mois = substr($date_event_fin, 5, 2);
		$date_event_fin_jour = substr($date_event_fin, 8, 2);
	
		
		echo '<br /><span class="print_style_rubriques">Quand ? </span> du '
		. $date_event_debut_jour . ' '
		. $NomDuMois[$date_event_debut_mois+0] . ' '
		. $date_event_debut_annee . ' au ' . $date_event_fin_jour . ' '
		. $NomDuMois[$date_event_fin_mois+0] . ' '
		. $date_event_fin_annee . '</span>';

		?></td>
  </tr>
    <tr>
    <td>	</td>
  </tr>

  <tr>
    <td>
	<?php /* 
	// -----------------------------
	// Titre du calendrier 
	echo '<br /><div align="left"><span class="print_style_rubriques">Jours de repr&eacute;sentation : </span></div><br />' ;
	*/ ?>
	
	<table border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td class="bloc_calendrier">
	<?php
	
	
	// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC

			  
	// --------------------------------------------------------------------
	// ----------------------- AFFICHER CALENDRIERS -----------------------
	// --------------------------------------------------------------------
	// [A] Si p&eacute;riode comprise dans le m&ecirc;me mois : traiter les jours de JJ_debut &agrave; JJ_fin
	if (($MM_debut == $MM_fin) && ($AAAA_debut == $AAAA_fin))
	{
		$AAAA_traite = $AAAA_debut ;
		$MM_traite = $MM_debut ;

		/*echo  ' [A] P&eacute;riode couvrant 1 mois unique. Mois trait&eacute; = '.$MM_traite.' 
		et Ann&eacute;e trait&eacute;e = '.$AAAA_traite . '<br>' ; */
		
		affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	}
	
	// ------------------------------------------------------------------------------------------------------
	else
	{
		// [B1] si la p&eacute;riode s'&eacute;tend sur plusieurs mois, afficher 1 calendrier &agrave; chaque passage dans la boucle. 
		// Commencer par traiter le mois de d&eacute;but de p&eacute;riode
		$AAAA_MM_traite = substr($date_event_debut, 0, 7);
		$AAAA_traite = $AAAA_debut ;
		$MM_traite = $MM_debut ;
		// echo '<b>[B1] Mois trait&eacute; (1er mois de la p&eacute;riode) = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite . '</b><br>' ;
		
		$tableau_jours = array() ;	
	
		affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	
		// Incr&eacute;menter le mois :		
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
		// [B2] traiter tous les mois suivants jusqu'&agrave; ce qu'on arrive au mois de fin de PERIODE
		// La boucle s'arr&ecirc;te quand (($MM_traite == $MM_debut) && ($AA_fin == $AAAA_traite))
	
		while (($MM_traite != $MM_fin) OR ($AAAA_traite != $AAAA_fin))
		{
			/*unset ($tableau_jours[$JJ_db]);	*/
			$tableau_jours = array() ;
		
			//echo  '<b>[B2] Mois "suivant" trait&eacute; = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite.'</b><br>' ;
			
			affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	
			// Incr&eacute;menter le mois :		
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
		// [B3] traiter le dernier mois de JJ = 1 &agrave; JJ = JJ_fin
		$tableau_jours = array() ;
		$AAAA_MM_traite = substr($date_event_fin, 0, 7);
	
		//echo  '<b> [B3] Mois trait&eacute; (Dernier mois de la p&eacute;riode) = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite . '</b><br>' ;
	
		affich_jours_actifs ($jours_actifs_event, $MM_traite, $AAAA_traite) ;
	}
	
	
	// CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
	echo '<div class="float_stop"></div>
	<div align="center"><span class="checked">Jour de repr&eacute;sentation</span> / 
	<span class="unchecked">Pas de repr&eacute;sentation</span></div>' ;
	
	echo '<div class="float_stop"><br /></div>';
?>

</td>
  </tr>
</table>

</td>
  </tr>
</table>

<?php require 'print_pied.php'; ?>

</div>

</body>
</html>
