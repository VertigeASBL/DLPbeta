<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Recopier les donn&eacute;es entre &eacute;v&eacute;nements</title>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.colore {color: #00A9AA}
-->
</style>

</head>

<body>
<div id="head_admin_agenda"></div>

<h1>Recopier les données entre événements</h1>

<div class="menu_back">
<a href="index_admin.php">Menu Admin</a>
</div>

<p class="mini"><strong>Principe</strong> :  Pour Interview et Critique, une valeur non nulle remplace une valeur nulle. L' ID d'&eacute;v&eacute;nement de valeur la plus petite aura priorit&eacute;, car consid&eacute;r&eacute; comme &quot;&eacute;v&eacute;nement original&quot;. Pour  &quot;Saison pr&eacute;c&eacute;dente&quot;, il s'agit juste de croiser les ID, donc recopier l'ID de l'un dans l'autre.
<p>

<?php

$masquer_formulaire = false ;

//-----------------------------------------------------------------------------------
// Verification
//-----------------------------------------------------------------------------------
if (isset($_GET['from']) AND preg_match('/[0-9]$/', $_GET['from']) AND isset($_GET['to']) AND preg_match('/[0-9]$/', $_GET['to']))
{
	require '../inc_var.php';
	require '../inc_db_connect.php';

	$from = htmlentities($_GET['from'], ENT_QUOTES) ;
	$to = htmlentities($_GET['to'], ENT_QUOTES) ;

	// Récupération des valeurs à transférer
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$from'");
	$donnees = mysql_fetch_array($reponse);
	
	$critique_event_from = $donnees['critique_event'] ;
	$interview_event_from = $donnees['interview_event'] ;
	$saison_preced_event_from = $donnees['saison_preced_event'] ;
	 
	unset ($reponse) ;
	unset ($donnees) ;

	// Récupération des valeurs qui seront écrasées
	$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$to'");
	$donnees = mysql_fetch_array($reponse);
	
	$critique_event_to = $donnees['critique_event'] ;
	$interview_event_to = $donnees['interview_event'] ;
	$saison_preced_event_to = $donnees['saison_preced_event'] ;


	// Créer bouton pour relancer la page
	/* <meta http-equiv="refresh" content="1; url=redondance_titres_recopier.php?from=' . $from . '&amp;to=' . $to . '">*/
	echo '<p align="center"><br />
<a href="redondance_titres_recopier.php?from=' . $from . '&amp;to=' . $to . '">Recharger la page sans renvoyer le formulaire</a>
<br /> <br /> <br />';


	// Si on a appuyé sur le bouton ENREGISTRER
	if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer'))
	{
		// Recopier les données
		// ---------------------
		// Principe : Pour Interview et Critique, la valeur non nulle remplace la valeur nulle. 
		// Commencer le test par l'ID d'événement le plus bas car considéré comme l'original
		
		// Quel est le plus petit ID ? Il correspond à l'événement le plus ancien et donc l'original
		if($from > $to) 
		{
			$origine = $to ;
			$destination = $from ;
		}
		else
		{
			$origine = $from ;
			$destination = $to ;
		}
		
		// Récupération des valeurs d'origine
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$origine'");
		$donnees = mysql_fetch_array($reponse);

		$critique_event_origine = $donnees['critique_event'] ;
		$interview_event_origine = $donnees['interview_event'] ;
		$saison_preced_event_origine = $donnees['saison_preced_event'] ;
		 
		unset ($reponse) ;
		unset ($donnees) ;
	
		// Récupération des valeurs de destination
		$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$destination'");
		$donnees = mysql_fetch_array($reponse);

		$critique_event_destination = $donnees['critique_event'] ;
		$interview_event_destination = $donnees['interview_event'] ;
		$saison_preced_event_destination = $donnees['saison_preced_event'] ;


		// CRITIQUE
		// ********
		if ($critique_event_origine != 0)
		{
			$reussi = mysql_query("UPDATE $table_evenements_agenda SET
			critique_event = '$critique_event_origine'
			WHERE id_event = '$destination' LIMIT 1 ") or die('Erreur écriture 1 : ' . mysql_error());
			if ($reussi)
			{
				echo '<div class="info">La valeur de "critique" de l\'événement ' . $origine . ' est recopiéé à l\'événement ' . $destination . '</div>' ;
				$masquer_formulaire = true ;
			}		
		}
		elseif ($critique_event_destination != 0)
		{
			$reussi = mysql_query("UPDATE $table_evenements_agenda SET
			critique_event = '$critique_event_destination'
			WHERE id_event = '$origine' LIMIT 1 ") or die('Erreur écriture 2 : ' . mysql_error());
			if ($reussi)
			{
				echo '<div class="info">La valeur de "critique" de l\'événement ' . $destination . ' est recopiéé à l\'événement ' . $origine . '</div>' ;
				$masquer_formulaire = true ;
			}		
		}
		else
		{
			echo '<div class="info">Aucune valeur recopiée pour la "critique"</div>' ;
		}		
		
		


		// INTERVIEW
		// *********
		if ($interview_event_origine != 0)
		{
			$reussi1 = mysql_query("UPDATE $table_evenements_agenda SET
			interview_event = '$interview_event_origine'
			WHERE id_event = '$destination' LIMIT 1 ") or die('Erreur écriture 3 : ' . mysql_error());
			if ($reussi1)
			{
				echo '<div class="info">La valeur de "interview" de l\'événement ' . $origine . ' est recopiéé à l\'événement ' . $destination . '</div>' ;
				$masquer_formulaire = true ;
			}		
		}
		elseif ($interview_event_destination != 0)
		{
			$reussi2 = mysql_query("UPDATE $table_evenements_agenda SET
			interview_event = '$interview_event_destination'
			WHERE id_event = '$origine' LIMIT 1 ") or die('Erreur écriture 4 : ' . mysql_error());
			if ($reussi2)
			{
				echo '<div class="info">La valeur de "interview" de l\'événement ' . $destination . ' est recopiéé à l\'événement ' . $origine . '</div>' ;
				$masquer_formulaire = true ;
			}		
		}
		else
		{
			echo '<div class="info">Aucune valeur recopiée pour la "interview"</div>' ;
		}		
		
		

		// SAISON PRECEDENTE
		// *****************
		// Ici, il s'agit juste de croiser les ID, donc recopier l'ID de l'un dans l'autre
		
		//erreur d'avant : saison_preced_event = '$saison_preced_event_origine' 

		$reussi3 = mysql_query("UPDATE $table_evenements_agenda SET
		saison_preced_event = '$origine' 
		WHERE id_event = '$destination' LIMIT 1 ") or die('Erreur écriture 5 : ' . mysql_error());
		
		
		$reussi4 = mysql_query("UPDATE $table_evenements_agenda SET
		saison_preced_event = '$destination' 
		WHERE id_event = '$origine' LIMIT 1 ") or die('Erreur écriture 6 : ' . mysql_error());
		
		if ($reussi3 AND $reussi4)
		{
			echo '<div class="info">Le croisement des valeurs de "Saison précédente" a été exécuté</div>' ;
		}
		
		
		$masquer_formulaire = true ;

	}


	// Afficher le tout pour vérification
	$formulaire = '<table width="600" border="1" cellspacing="0" cellpadding="5" align="center">
	  <tr>
		<th colspan="3">Les valeurs de l\'événement <a href="../../-Detail-agenda-?id_event=' . $from . '">' . $from . '</a> 
		vont être recopiées dans  l\'événement <a href="../../-Detail-agenda-?id_event=' . $to . '">' . $to . '</a> 
		</th>
	  </tr>
	  <tr>
		<th width="130">Critique</th>
		<td>' . $critique_event_from . '</td>
		<td>' . $critique_event_to . '</td>
	  </tr>
	  <tr>
		<th>Interview</th>
		<td>' . $interview_event_from . '</td>
		<td>' . $interview_event_to . '</td>
	  </tr>
	  <tr>
		<th>Saison précédente</th>
		<td>' . $saison_preced_event_from . '</td>
		<td>' . $saison_preced_event_to . '</td>
	  </tr>
	</table>' ;



	// Afficher le bouton de confirmation
	$bouton = '<br /> <form action="" method="post">
	<input name="hidden_from" type="hidden" value="" />
	<input name="hidden_to" type="hidden" value="" />
	<div align="center"><input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer"></div>
	</form>';
	
	// afficher ssi formulaire pas envoyé 
	if(!$masquer_formulaire)
	echo $formulaire . $bouton ;



}
else
{
	echo '<p>&nbsp;</p><p>&nbsp;</p>
	<div class="alerte"> <br /> Vous ne pouvez pas accéder à cette page de cette façon.<br /> <br />
	<a href="../index.php">Retour à l\'Admin Page</a> <br /> <br /> 
	</div>' ;
	exit () ;
}

?>


</body>
</html>
