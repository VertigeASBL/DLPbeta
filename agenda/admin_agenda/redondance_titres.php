<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Test de redondance des titres</title>

<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">

<style type="text/css">
<!--
.style2 {
	color: #990000;
	background-color: #DFDFDF;
	padding: 5px;
	margin-left: 50px;
	margin: 20px;
	width: 600px;
}
.style2 a {
	color: #00807F;
	font-size: 1.2em;
	font-weight: bold;
	font-family: "Courier New", Courier, monospace;
}

.temps_calcul_page {
	font-size: 0.9em;
	color: #4444;
	margin: 0;
	padding: 0;
	text-align: center;
}
-->
</style>

</head>

<body>
<div id="head_admin_agenda"></div>

<h1>Test de redondance des titres</h1>

<div class="menu_back">
<a href="index_admin.php">Menu Admin</a>
</div>


<form id="form1" name="form1" method="post" action="">
  <p>Vérifier les ID 
    de              
    <input name="id_depart" type="text" id="id_depart" value="" size="5" maxlength="4" />
 &agrave;
 <input name="id_fin" type="text" id="id_fin" value="" size="5" maxlength="4" /> 
  (pas trop &agrave; la fois de pr&eacute;f&eacute;rence)</p>
  <p> Tolérance :  
    <input name="tolerance" type="text" id="tolerance" value="" size="3" maxlength="2" />
    % (correspond au pourcentage  de lettres qui doivent diff&eacute;rer entre deux titres pour consid&eacute;rer qu'ils sont diff&eacute;rents) </p>
  <p><input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="update"></p>
</form>


<?php

//Doc : http://be.php.net/manual/fr/function.levenshtein.php

$affichage_debug = 0 ;
$time_start = microtime(true); // Pour afficher le temps d'exécution du script

if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'update'))
{
	$rec = '';
	
	// -----------------------------------------
	// TEST ID DE DEPART 
	if (isset($_POST['id_depart']) AND preg_match('/[0-9]$/', $_POST['id_depart'])) 
	{
		$id_depart = htmlentities($_POST['id_depart'], ENT_QUOTES);
	}
	else
	{
		$rec.= '- Vous devez déterminer un ID de départ<br>';
	}


	// -----------------------------------------
	// TEST ID DE FIN 
	if (isset($_POST['id_fin']) AND preg_match('/[0-9]$/', $_POST['id_fin'])) 
	{
		$id_fin = htmlentities($_POST['id_fin'], ENT_QUOTES);
	}
	else
	{
		$rec.= '- Vous devez déterminer un ID de fin<br>';
	}

	// -----------------------------------------
	// TEST ID DE FIN 
	if ($_POST['id_depart'] > $_POST['id_fin'])
	{
		$rec.= '- L\'ID de départ doit avoir une valeur inférieure à l\'ID de fin<br>';
	}
	

	// -----------------------------------------
	// TEST ID DE DEPART 
	if (isset($_POST['tolerance']) AND preg_match('/[0-9]$/', $_POST['tolerance'])) 
	{
		$tolerance = htmlentities($_POST['tolerance'], ENT_QUOTES);
		if ($tolerance > 45)
		{
			$rec.= '- Les tolérance supérieures à 45% ne sont pas admises<br>';
		}	
	}

	else
	{
		$rec.= '- Vous devez déterminer une tolérance<br>';
	}
	

	if ($rec != '')
	{
		echo '<div class="alerte"><br />' . $rec . '<br /></div>' ;
	}
	else
	{
		echo '<h2>Vous vérifiez les titres des événements de ' . $id_depart . ' à ' . $id_fin . '<br />
		La tolérance introduite est de ' . $tolerance . '%</h2>' ;
		
		// Mettre les titres dans un Array
		// ------------------------------------
		
		// Premier tableau contenant juste les titres que l'on veut checker

		include ("../inc_db_connect.php") ; // Connection DB
		$test_ini = ini_set("max_execution_time", "360") ; // Booster le temps d'exécution
		if($test_ini == false)
		{
			echo 'Impossible de modifier "max_execution_time". 
			Valeur actuelle = ' . $test_ini . ' secondes.' ;
		}

		$tableau_titres = array();
		$reponse = mysql_query("SELECT id_event, nom_event FROM ag_event 
		WHERE id_event >= $id_depart AND id_event <= $id_fin 
		AND saison_preced_event = 0 
		ORDER BY id_event");
		while ($donnees = mysql_fetch_array($reponse))
		{
			$id_event = $donnees['id_event'];
			$nom_event = $donnees['nom_event'];
			$temp_array = array ("id" => $id_event,	"titre" => $nom_event); // Pour créer un tableau multidimentionel
			array_push($tableau_titres, $temp_array) ;
		}

		// Second tableau contenant tous les titres
		$tableau_titres_2 = array();
		$reponse = mysql_query("SELECT id_event, nom_event FROM ag_event ORDER BY id_event");
		while ($donnees = mysql_fetch_array($reponse))
		{
			$id_event = $donnees['id_event'];
			$nom_event = $donnees['nom_event'];
			$temp_array = array ("id" => $id_event,	"titre" => $nom_event); // Pour créer un tableau multidimentionel
			array_push($tableau_titres_2, $temp_array) ;
		}
				
		
		/*echo '<pre>' ;
		print_r ($tableau_titres) ;
		echo '</pre>' ;
		exit();*/
		 
		// Test des titres
		$i=0;
		while ($i<sizeof($tableau_titres))
		{
			$afficher_grand_titres = '<h3>' . $i . ' - EVENT ' . $tableau_titres[$i]['id'] . ' : "' 
			. $tableau_titres[$i]['titre'] . '"</h3>' ; 
			($affichage_debug) ? print $afficher_grand_titres : print '' ;
			
			$j=0;
			while ($j<sizeof($tableau_titres_2))
			{
				// On compare le titre choisi dans la boucle parente à tous les autres titres
				$affiche_comparaison_titre = '<br><strong>i='.$i.' et j='.$j.'</strong> ::::  '
				. $tableau_titres[$i]['titre'] . ' Comparé à '. $tableau_titres_2[$j]['titre'] . ' (id='
				 . $tableau_titres_2[$j]['id'] . '). ';
				($affichage_debug) ? print $affiche_comparaison_titre : print '' ;
			
				if ($tableau_titres_2[$j]['id'] == $tableau_titres[$i]['id'] 
				OR $tableau_titres[$i]['titre'] == ''
				OR $tableau_titres_2[$j]['titre'] == '' ) // Test pour ne pas comparer une entrée avec elle même
				{
					// echo '<br>Inutile de tester, c\'est le même ID !!' ;
				}
				else
				{
					$distance_de_levenshtein = levenshtein($tableau_titres_2[$j]['titre'], $tableau_titres[$i]['titre']);
					$affiche_distance = 'La distance est de ' . $distance_de_levenshtein . '' ;
					($affichage_debug) ? print $affiche_distance : print '' ;
					
					$distance_proport_a_lg_titre = ceil(($distance_de_levenshtein * 100) / (strlen(html_entity_decode($tableau_titres[$i]['titre'])))) ; // le html_entity_decode est utile pour faire comter pour'1' les caractères spéciaux. Il s'agit ici d'un résultat en %
					if ($distance_proport_a_lg_titre < $tolerance)
					{
						$ressemblance_proport_a_lg_titre = 100 - $distance_proport_a_lg_titre ;
						echo '
						<div class="style2">' ; 
						
						// construire une barre graphique proportionnelle à la ressemblance entre les chaines
						for ($z=0; $z<3*$ressemblance_proport_a_lg_titre; $z++) // On peut introduire une facteur multiplicateur afin d'augmenter la taille des barres
						{ echo '<img src="../design_pics/barre_redondance.gif">'; }

						
						echo '<br />' .$distance_de_levenshtein . ' lettres différentes 
						(Différence à ' . $distance_proport_a_lg_titre . '%)
						<br />' ;
						
						/* Ligne 2 */
						/* +++++++ */
							
						echo '<a href="../../-Detail-agenda-?id_event=' . $tableau_titres[$i]['id'] . '" title="Voir en ligne" >
						' . $tableau_titres[$i]['titre'] . '</a>
						(id=' . $tableau_titres[$i]['id'] . ') - 
						<a href="../../-Detail-agenda-?id_event=' . $tableau_titres[$i]['id'] . '" title="Voir en ligne" >
						Page en ligne</a> - 
						<a href="edit_event.php?id=' . $tableau_titres[$i]['id'] . '" title="Aller à la page d\'admin de cet événement" >Edition</a>
						
						 - <a href="redondance_titres_recopier.php?from=' . $tableau_titres[$i]['id'] . '&amp;to=' . $tableau_titres_2[$j]['id'] . '" target="_blank">Recopier de ' . $tableau_titres[$i]['id'] . ' à ' . $tableau_titres_2[$j]['id'] . '</a>		
							
							<br />' ;
							
							/* Ligne 2 */
							/* +++++++ */ 
							echo '<a href="../../-Detail-agenda-?id_event=' . $tableau_titres_2[$j]['id'] . '" title="Voir en ligne" >' . 							$tableau_titres_2[$j]['titre'] . '</a> (id=' . $tableau_titres_2[$j]['id'] . ') - 
							<a href="../../-Detail-agenda-?id_event=' . $tableau_titres_2[$j]['id'] . '" title="Voir en ligne" >
							Page en ligne</a> - 
							<a href="edit_event.php?id=' . $tableau_titres_2[$j]['id'] . '" title="Aller à la page d\'admin de cet événement" >Edition</a>
							
							 - <a href="redondance_titres_recopier.php?from=' . $tableau_titres_2[$j]['id'] . '&amp;to=' . $tableau_titres[$i]['id'] . '" target="_blank">Recopier de ' . $tableau_titres_2[$j]['id'] . ' à ' . $tableau_titres[$i]['id'] . '</a>
							
						</div>' ;
					}
				}
		$j++ ;
			}
	$i++ ;
		}	
	}
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	echo '<div class="temps_calcul_page">Page g&eacute;n&eacute;r&eacute;e en '.substr($time,0,5).' sec.</div>';
}


?>




</body>
</html>
