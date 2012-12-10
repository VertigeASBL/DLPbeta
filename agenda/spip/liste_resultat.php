
<?php 
require 'agenda/inc_var.php';
// require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';
require 'agenda/calendrier/inc_calendrier.php';

// aller à periode_affichage_requete_get pour régler la "periode_affichage_requete_get"

$tab = '' ;

// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Moteur de recherche multicritères insensible à la casse avec mise en évidence du résultat 
// Si le formulaire est posté, construire la requete selon les critères.
// Sinon, afficher les derniers événements de la semaine


/*
Choisir des spectacles via l'URL :
************************************
Important : ajouter "?req=ext" dans le début de la requête, ce qui donne
http://www.demandezleprogramme.be/-Agenda-?req=ext&...

Ensuite, insérer les paramètres de sélection suivants selon ce qu'on veut

&genre=...
&region=...
&lieu=...


Pour la date :
--------------
&date_rech=tout  ==> donne toutes les dates à partir d'aujourd'hui (enfin, aujourd'hui moins 5 jours)

Pour une recherche autour d'1 date :/-Agenda-?jour=9&mois=01&annee=2009
!! c'est sans le "?req=ext"

Sans rien préciser pour la date, on a les 10 prochains jours
--------------------------------------
Exemple les événements du LIEU CULTUREL n°24 : http://www.demandezleprogramme.be/-Agenda-?req=ext&date_rech=tout&lieu=24
*/
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
?>
<?php

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage du calendrier avec cases colorées en fonction des jours actifs
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function affich_jours_spectacles ($MM_traite, $AAAA_traite)
{	
	require 'agenda/inc_var.php';
	global $date_test_periode_debut ;
	global $date_test_periode_fin ;
	$tableau_jours = array() ;	

	// .......................................................................
	// Flèches de "mois précédent" et "mois suivant"
	
	// 1) mois suivant :
	if ($MM_traite == 12 )
	{
		$mois_next = 1;
		$annee_next = $AAAA_traite + 1;
	}
	else
	{
		$mois_next = $MM_traite + 1;
		$annee_next = $AAAA_traite ;
	}
	$next = '?mois=' . $mois_next . '&annee=' . $annee_next . '&mois_chgt=1' ;
	
	// 1) mois précédant :
	if ($MM_traite == 1 )
	{
		$mois_prev = 12;
		$annee_prev = $AAAA_traite - 1;
	}
	else
	{
		$mois_prev = $MM_traite - 1;
		$annee_prev = $AAAA_traite ;
	}
	$prev = '?mois=' . $mois_prev . '&annee=' . $annee_prev . '&mois_chgt=1' ;
	
	// echo '<p>'.$prev . ' <<==>> ' . $next . '</p>';
	$pn = array('&lt;&lt;'=> $prev, '&gt;&gt;'=> $next);
// .......................................................................

	// Initialiser le tableau. (Car un simple "else" n'aurait pas été)
	$j=1;
	for ($j=1 ; $j<=31 ; $j++)
	{
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
		settype($JJ_traite, "integer"); // Pour éviter problèmes de foncion "calebdar" avec les nombres précédés de "0"	
		$tableau_jours[$JJ_traite] = array(NULL,'linked-day non_event_cal',$JJ_traite);
	}

	$reponse_fct = mysql_query("SELECT jours_actifs_event, id_event FROM $table_evenements_agenda 
	INNER JOIN  $table_lieu L
    ON cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND lieu_event = id_lieu
	WHERE NOT ((date_event_debut < '$date_test_periode_debut') AND (date_event_fin < '$date_test_periode_debut') 
	OR (date_event_debut > '$date_test_periode_fin') AND (date_event_fin > '$date_test_periode_fin'))") ;		
		
	while ($donnees_fct = mysql_fetch_array($reponse_fct))
	{ 
		$j=1;
		for ($j=1 ; $j<=31 ; $j++)
		{
			// Composer la chaine qui sera cherchée dans la DB :
			$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
			$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
			$date_traite = $AAAA_traite.'-'.$MM_traite.'-'.$JJ_traite ;
			settype($JJ_traite, "integer"); // Pour éviter problèmes de foncion "calebdar" avec les nombres précédés de "0"	
			$jours_actifs_event = $donnees_fct ['jours_actifs_event'];
			$jours_actifs_event = explode(",", $jours_actifs_event);

			if (in_array($date_traite, $jours_actifs_event))
			{
				// echo '<br>id('.$donnees_fct ['id_event'] .') '.$date_traite.' y est '; // test
				// jour ACTIF
				$link = '?jour='.$j.'&mois='.$MM_traite.'&annee='.$AAAA_traite ;
				$tableau_jours[$JJ_traite] = array($link,'linked-day event_cal',$JJ_traite);
			}
		}
	}
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1, $pn); // Affichage du calendrier
	echo '<br />' ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF



// ********************************************************************************************************
// Paramètres passés par $_GET
// ********************************************************************************************************
// IF { $_GET['jour'] ET $_GET['mois'] ET $_GET['annee'] } existent, ils donnent le mois et l'année au calendrier.
// ELSEIF : { $_GET['mois'] ET $_GET['annee']  ET $_GET['mois_chgt'] = '1' } => afficher le mois car on a cliqué sur le bouton de changement de mois du calendrier
// ELSEIF : $_GET['date_rech'] = "tout" => afficher totalité
// ELSE -> afficher semaine actuelle
// !! NOTE : $_GET['req'] doit être égal à 'ext' pour autoriser TOUTE requête extérieure !!
//
// Le ORDER by est différent pour la requête via POST ou GET. Pour POST, on classe par "date_event_debut DESC", et inversément pour GET
//


// //////////////////////////////////////////////
// Paramètres DATE passés par $_GET    /!\ ils sont aussi utiles via le formulaire
// //////////////////////////////////////////////
if (isset ($_GET['mois']) AND $_GET['mois'] != NULL
AND isset ($_GET['jour']) AND $_GET['jour'] != NULL
AND isset ($_GET['annee']) AND $_GET['annee'] != NULL )
{
	$JJ_traite = htmlentities($_GET['jour'], ENT_QUOTES);
	$JJ_traite = str_pad($JJ_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
	$MM_traite = htmlentities($_GET['mois'], ENT_QUOTES);
	$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
	$AAAA_traite = htmlentities($_GET['annee'], ENT_QUOTES);
	
	$date_a_tester = $AAAA_traite . '-' . $MM_traite . '-' . $JJ_traite ;
	
	if (isset ($_GET['req']) AND $_GET['req'] == 'ext')
	{
		$query_get = "WHERE (jours_actifs_event LIKE '%$date_a_tester%' ) "; // Utile pour différencier la requete via GET
	}
	else
	{ 
		$query = "WHERE (jours_actifs_event LIKE '%$date_a_tester%' ) ";
	}


	$requ_via_calendar = '<b>'.$JJ_traite.'-'.$MM_traite.'-'.$AAAA_traite.' </b>' ;
	
	$_POST['go'] = 'annuler' ; // pour éviter de tester le formulaire
}

// On n'a pas le jour, mais bien la variable disant que l'on a cliqué sur le bouton de changement de mois du calendrier
// On doit afficher tous les spectacles du mois (employer le code utilisé pour tester le formulaire) 
elseif (isset ($_GET['mois_chgt']) AND $_GET['mois_chgt'] == '1'
AND isset ($_GET['mois']) AND $_GET['mois'] != NULL
AND isset ($_GET['annee']) AND $_GET['annee'] != NULL)
{
	$MM_traite = htmlentities($_GET['mois'], ENT_QUOTES);
	$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
	$AAAA_traite = htmlentities($_GET['annee'], ENT_QUOTES);
	
	$_POST['saisie_date_1'] = '01-'.$MM_traite.'-'.$AAAA_traite ;
	$_POST['saisie_date_2'] = '31-'.$MM_traite.'-'.$AAAA_traite ;
	
	// $query_get = ....... ; // requete via GET : ce cas n'est normalement pas à envisager

	$_POST['go'] = 'Lancer la recherche' ;
}

// On veut toutes les dates à partir d'aujourd'hui
elseif (isset ($_GET['date_rech']) AND $_GET['date_rech'] == 'tout')
{	
	$query_get = "WHERE (date_event_fin > SUBDATE(CURDATE(), INTERVAL 5 DAY)) "; // requete via GET : aucun critère de date nécessaire

	$_POST['go'] = 'annuler' ;

	// pour affichage calendrier
	$MM_traite = date('m');
	$AAAA_traite = date('Y');
}

else // Sinon -> Semaine actuelle pour le calendrier. 
{
	// pour affichage calendrier
	$date_debut = date ('Y-m-d');
	$MM_traite = date('m');
	$AAAA_traite = date('Y');
	
	// date pour requete via GET (de j à j+7)
	$date_debut_get = date ('Y-m-d');
	$date_fin_get = date ('Y-m-d', $date_fin_get = mktime(0, 0, 0, date("m")  , date("d")+25, date("Y"))); // periode_affichage_requete_get
	$query_get = " WHERE 
	NOT ((date_event_debut < '$date_debut_get') 
	AND (date_event_fin < '$date_debut_get') 
	OR (date_event_debut > '$date_fin_get') 
	AND (date_event_fin > '$date_fin_get')) ";
	
}

// Variables pour afficher jours actifs de calendrier
$date_test_periode_debut = $AAAA_traite . '-' . $MM_traite . '-01';
$date_test_periode_fin = date ('Y-m-d', mktime(0, 0, 0, $MM_traite, 31, $AAAA_traite)); // un mois plus tard
// echo '$date_test_periode_debut = '.$date_test_periode_debut.'<br>$date_test_periode_fin = '.$date_test_periode_fin ; // TEST


// //////////////////////////////////////////////
// Paramètres LIEU passés par $_GET
// //////////////////////////////////////////////
if (isset ($_GET['lieu']) AND $_GET['lieu'] != NULL)
{
	$lieu_get = htmlentities($_GET['lieu'], ENT_QUOTES);
	$query_get.= " AND lieu_event = '$lieu_get'" ;
	
	$_POST['go'] = 'annuler' ; // pour éviter de tester le formulaire

	$lieu_event_form = $lieu_get ;// Transmettre au formulaire afin de pré-sélectionner le champ pour le visiteur
}


// //////////////////////////////////////////////
// Paramètres REGION passés par $_GET
// //////////////////////////////////////////////
if (isset ($_GET['region']) AND $_GET['region'] != NULL)
{
	$region_get = htmlentities($_GET['region'], ENT_QUOTES);
	$query_get.= " AND ville_event = '$region_get'" ;
	
	$_POST['go'] = 'annuler' ; // pour éviter de tester le formulaire

	$ville_event = $region_get ;// Transmettre au formulaire afin de pré-sélectionner le champ pour le visiteur
}


// //////////////////////////////////////////////
// Paramètres GENRE passés par $_GET
// //////////////////////////////////////////////
if (isset ($_GET['genre']) AND $_GET['genre'] != NULL)
{
	$genre_get = htmlentities($_GET['genre'], ENT_QUOTES);
	$query_get.= " AND genre_event = '$genre_get'" ;
	
	$_POST['go'] = 'annuler' ; // pour éviter de tester le formulaire
	
	$genre_event = $genre_get ;// Transmettre au formulaire afin de pré-sélectionner le champ pour le visiteur
}



// ********************************************************************************************************
// Lecture des données du formulaire si posté
// ********************************************************************************************************

// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
// Construction de la requête
// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR

if (isset($_POST['go']) AND ($_POST['go'] == 'Lancer la recherche' OR $_POST['go'] == ' '))
{
	// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
	// PERIODE ?
	/* La période est le paramètre obligatoire. Si aucune période n'est mentionnée, date de début = date actuelle et date fin = 1 semaine en +
	Si la date de fin est < à date début, la remplacer par date actuelle + 1 semaine */
	
	if (isset ($_POST['saisie_date_1']) AND $_POST['saisie_date_1'] != NULL 
	AND isset ($_POST['saisie_date_2']) AND $_POST['saisie_date_2'] != NULL
	AND preg_match("!^([0-9]{2}-){2}[0-9]{4}$!", $_POST['saisie_date_1'])
	AND preg_match("!^([0-9]{2}-){2}[0-9]{4}$!", $_POST['saisie_date_2']) )
	{
		$saisie_date_1 = htmlentities($_POST['saisie_date_1'], ENT_QUOTES);
		$saisie_date_1_annee = substr($saisie_date_1, 6, 4);
		$saisie_date_1_mois = substr($saisie_date_1, 3, 2);	
		$saisie_date_1_jour = substr($saisie_date_1, 0, 2);
		$requete_date_debut = $saisie_date_1 ;
		$saisie_date_1_aaammjj = $saisie_date_1_annee.'-'.$saisie_date_1_mois.'-'.$saisie_date_1_jour ;
		
		$saisie_date_2 = htmlentities($_POST['saisie_date_2'], ENT_QUOTES);
		$saisie_date_2_annee = substr($saisie_date_2, 6, 4);
		$saisie_date_2_mois = substr($saisie_date_2, 3, 2);	
		$saisie_date_2_jour = substr($saisie_date_2, 0, 2);
		$saisie_date_2_aaammjj = $saisie_date_2_annee.'-'.$saisie_date_2_mois.'-'.$saisie_date_2_jour ;
		$requete_date_fin = $saisie_date_2 ;

		if ($saisie_date_1_aaammjj <= $saisie_date_2_aaammjj) // Tout OK
		{
			// echo '(TEST : <b>dates OK</b> : '.$saisie_date_1_aaammjj.'-- '.$saisie_date_2_aaammjj.')<br> ' ; // Test
		}
		else // erreur date fin -> date fin = date debut +7 jours
		{
			$saisie_date_2_aaammjj = date ('Y-m-d', mktime(0, 0, 0, $saisie_date_1_mois, $saisie_date_1_jour+7, $saisie_date_1_annee)); // une semaine plus tard
			$requete_date_fin = date ('d-m-Y', mktime(0, 0, 0, $saisie_date_1_mois, $saisie_date_1_jour+7, $saisie_date_1_annee)) ;
			$saisie_date_2 = $requete_date_fin ; // pour re-remplire le formulaire
			echo '(TEST : Recalcul des dates : '.$saisie_date_1_aaammjj.'-- '.$saisie_date_2_aaammjj .')' ; // Test
		}
	}
	else // pas de date de début (pas utile de tester la date fin...) -> date début = now 
	{
		$saisie_date_1_aaammjj = date ('Y-m-d'); // Aujourd'hui
		$saisie_date_2_aaammjj = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+7, date("Y"))); // une semaine plus tard
	}

	$query = " WHERE 
	NOT ((date_event_debut < '$saisie_date_1_aaammjj') 
	AND (date_event_fin < '$saisie_date_1_aaammjj') 
	OR (date_event_debut > '$saisie_date_2_aaammjj') 
	AND (date_event_fin > '$saisie_date_2_aaammjj')) ";


	// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
	// LIEU ?
	
	if (isset ($_POST['lieu_event']) AND $_POST['lieu_event'] != 'non_selct' )
	{
		$lieu_event = htmlentities($_POST['lieu_event'], ENT_QUOTES);
		$query.= " AND lieu_event = '$lieu_event'" ;
		
		$requete_lieu = $lieu_event ; // pour visualiser les critères de recherche 
		$lieu_event_form = $lieu_event ; // pour re-remplire le formulaire
	}
	
	
	// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
	// VILLE ?
	
	if (isset ($_POST['ville_event']) AND $_POST['ville_event'] != 'non_selct')
	{	
		$ville_event = htmlentities($_POST['ville_event'], ENT_QUOTES);
		$query.= " AND ville_event = '$ville_event' " ;
		
		$requete_ville = $ville_event ; // pour visualiser les critères de recherche 
	}
	
	
	// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
	// GENRE ?
	
	if (isset ($_POST['genre_event']) AND $_POST['genre_event'] != 'non_selct')
	{	
		$genre_event = htmlentities($_POST['genre_event'], ENT_QUOTES);
		$query.= " AND genre_event = '$genre_event'" ;
		
		$requete_genre = $genre_event ;
	}
	
	
	// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
	// Par MOT ?
	
	if (isset ($_POST['txt']) AND $_POST['txt'] != 'nom de l\'événement' AND $_POST['txt'] != NULL)
	{	
		$txt = strip_tags(htmlentities($_POST['txt'], ENT_QUOTES));
		$query.= " AND (description_event LIKE '%$txt%' OR nom_event LIKE '%$txt%' OR resume_event LIKE '%$txt%' )" ;
		
		$requete_txt = strtolower ($txt) ;
	}

	
	// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
	// FIN DE LA REQUETE SQL :


	// --------------------------------------
	// Affichage des critères de recherche
	// Période :
	$requ_via_form =  'Vous avez sélectionné les spectales ' ;
	if (isset($_GET['mois_chgt']) AND $_GET['mois_chgt'] != NULL) // Afficher le nom du mois sélectionné dans le calendar
	{
		$requ_via_form.= 'du mois de <b>' . $NomDuMois[$saisie_date_1_mois+0] . '</b>' ;
	}
	elseif (isset ($requete_date_debut) AND isset ($requete_date_debut))
	{
		$requ_via_form.= 'du <b>' . $requete_date_debut . '</b> au <b>' . $requete_date_fin . '</b>' ;	}
	else
	{
		$requ_via_form.= 'de <b>cette semaine</b>' ;
		$saisie_date_1 = '' ;
		$saisie_date_2 = '' ;
	}
	
	// Lieu culturel :
	if (isset($requete_lieu) AND $requete_lieu != NULL)
	{ 
		$reponse_lieu = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = $requete_lieu");
		$donnees_lieu = mysql_fetch_array($reponse_lieu) ;
		$requ_via_form.= '<br>Lieu culturel : <b>' . $donnees_lieu['nom_lieu'] . '</b>' ;
	}
	 
	 
	 
	// Ville :
	if (isset($requete_ville) AND $requete_ville != NULL)
	{ 
		$requ_via_form.= '<br>Ville : <b>' . $regions[$requete_ville] . '</b>' ;
	}
	 
	 
	// Genre :
	if (isset($requete_genre) AND $requete_genre != NULL)
	{ 
		$requ_via_form.= '<br>Genre : <b>' . $genres[$requete_genre] . '</b>' ;
	}
	 
	 
	// Texte :
	if (isset($requete_txt) AND $requete_txt != 'nom de l\'événement')
	{ 
		$requ_via_form.= '<br>Mot recherché : <b>' . $requete_txt . '</b>' ;
	}
}


// ********************************************************************************************************
// Au chargement de la page, si aucune requête n'est faite => afficher les événements des x prochains jours
// ********************************************************************************************************
if (!isset($query)) // Si la valeur existe, c'est qu'elle a été fournie par le calendrier
{
/* Ancien :
	$date_debut = date ('Y-m-d', $date_debut_minimum = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));;
	$date_fin = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+10, date("Y")));		
 	// "départ" de la période ; c'est rajouté à la requete pour éviter les événements qui ont débutés il y a longtemps
	$date_debut_minimum = date ('Y-m-d', $date_debut_minimum = mktime(0, 0, 0, date("m")  , date("d")-10, date("Y")));	

	$query = "WHERE 
	NOT ((date_event_debut < '$date_debut') AND (date_event_fin < '$date_debut') OR (date_event_debut > '$date_fin') AND (date_event_fin > '$date_fin')) 
	AND (date_event_debut >= '$date_debut_minimum')";
*/
	
	// Les valeurs min et max sont contenues dans les fichiers "date_debut.txt" et "date_fin.txt" du répertoire "ctrl_periode". Le script gérant ce contenu est au même endroit

	$fichier_date_debut = fopen('agenda/ctrl_periode/date_debut.txt', 'r+');
	$fichier_date_fin = fopen('agenda/ctrl_periode/date_fin.txt', 'r+');

	fseek($fichier_date_debut, 0); // remettre curseur au début du fichier
	fseek($fichier_date_fin, 0); // remettre curseur au début du fichier
	
	$var_date_debut = fgets($fichier_date_debut); // lecture de la première ligne 
	$var_date_fin = fgets($fichier_date_fin); // lecture de la première ligne 

	if (!empty($var_date_debut) AND preg_match('/[0-9]$/', $var_date_debut)	
	AND !empty($var_date_fin) AND preg_match('/[0-9]$/', $var_date_fin))
	{
		$date_debut = htmlentities($var_date_debut, ENT_QUOTES);
		$date_fin = htmlentities($var_date_fin, ENT_QUOTES);
		$date_debut = date ('Y-m-d', $date_debut_minimum = mktime(0, 0, 0, date("m")  , date("d")-$date_debut, date("Y")));;
		$date_fin = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+$date_fin, date("Y")));
	}
	else
	{
		$date_debut = date ('Y-m-d', $date_debut_minimum = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));;
		$date_fin = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")  , date("d")+4, date("Y")));
		echo '<p>erreur lecture fichiers texte période !</p>' ;	
	}

	$query = "WHERE ((
	date_event_debut >= '$date_debut') 
	AND (date_event_debut <= '$date_fin') 
	AND (date_event_fin >= '$date_debut') 
	) ";
}


// ********************************************************************************************************
// Rajout du paramètre ORDER à la requête $query 
// ********************************************************************************************************

	$query = $query . ' ORDER BY lieu_event=70, date_event_debut ' ;

// ********************************************************************************************************
// Si une requête $query_get a été construite, la transformer en $query pour l'affichage du résultat
// ********************************************************************************************************

if (isset ($_GET['req']) AND $_GET['req'] == 'ext')
{
	$query = $query_get . ' ORDER BY lieu_event=70, date_event_debut ' ;
	//echo '<br><b>************* $query = '. $query .' ****************</b>' ;
}


// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
// Montrer à l'utilisateur sa requete et le nombre de résultats
// MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
			 
/*$query_count = "SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda INNER JOIN  $table_lieu L
             ON cotisation_lieu > CURDATE() AND lieu_event = id_lieu
			 $query " ;*/
		 
$query_count = "SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda INNER JOIN  $table_lieu L
             ON (cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND lieu_event = id_lieu
			 $query " ;		 
		 
		 
$reponse_count = mysql_query($query_count) or die($query_count . " ----- " . mysql_error());
$donnees_count = mysql_fetch_array($reponse_count);
$tot_entrees = $donnees_count['nbre_entrees'];

// l'utilisateur a utilisé le formulaire de recherche
if ( isset($requ_via_form) AND $requ_via_form != NULL ) 
{
	echo '<h3>Il y a ' . $tot_entrees . ' résultats pour cette recherche</h3>' ;
	echo '<div class ="afficher_requete">'.$requ_via_form.'</div>' ;
}

// l'utilisateur a utilisé le calendrier
if ( isset($requ_via_calendar) AND $requ_via_calendar != NULL ) 
{
	echo '<h3>Il y a ' . $tot_entrees . ' résultats pour la date du ' . $requ_via_calendar . '</h3>' ;
}

if ( isset($requ_absente) AND $requ_absente != NULL ) 
 // l'utilisateur n'a formulé aucune requête
{
	echo '<h3>Voici les spectacles des prochains jours : </h3>' ;
}

?>

<?php 
// *********************************************************************************************
// ---------------------------------------------------------------------------------------------
// 		Affichage résultat
// ---------------------------------------------------------------------------------------------
// *********************************************************************************************

$query_1 = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L
             ON (cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND lieu_event = id_lieu
			 $query " ;

/* $reponse = mysql_query($query_1) or die($query_1 . " ----- " . mysql_error()); // TEST JOINTURE 
	while ($donnees = mysql_fetch_array($reponse))
{ echo $donnees ['id_event'] .' -- ' .$donnees ['id_lieu'].' -- ' .$donnees ['cotisation_lieu'].'<br>' ; } */

$reponse = mysql_query($query_1) ;
if ($tot_entrees == 0)
{
	echo '<div class="breve" align="center"><p>&nbsp;</p>Aucun résultat ne correspond à la recherche <p>&nbsp;</p></div>' ;
}
else
{
	while ($donnees = mysql_fetch_array($reponse))
	{
			
		$tab.= '<div class="breve">' ;	
		$id_event = $donnees ['id_event'] ;
		$saison_preced_event = $donnees ['saison_preced_event'] ;

		// ____________________________________________
		// ICONES FLOTTANTES (au niveau du titre)
	
		$tab.= '<span class="ico_float_droite_relative">' ;



		// Vos Avis :
		// compter le nbre d'entrées :
		$count_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE 
		(event_avis = $id_event OR event_avis = $saison_preced_event)
		AND publier_avis = 'set'");
		$nbr_avis = mysql_fetch_array($count_avis);
		$total_entrees = $nbr_avis['nbre_entrees'];

		if ($total_entrees > 0)
		{
			$tab.= '<a href="-Detail-agenda-?id_event=' . $id_event . '#avis" title="Nombre d\'avis postés par les visiteurs">
			<img src="agenda/design_pics/ico_avis_mini.jpg"/>
			<div class="nombre_avis_breve">' . $total_entrees .'</div></a>' ;
			
		}
		
		
		// Icone Interview
		if (isset ($donnees['interview_event']) AND $donnees['interview_event'] != 0 )
		{
			$interview_event = $donnees['interview_event'] ;
//--- richir	$tab.= '<a href="-Interviews-?id_article=' . $interview_event . '&amp;page=article-3" title...
			$tab.= '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" title="Cliquez ici pour lire l\'interview"><img src="agenda/design_pics/ico_interview_mini.jpg"/></a>' ;
		}

		// Icone Critique
		if (isset ($donnees['critique_event']) AND $donnees['critique_event'] != 0 )
		{
			$critique_event = $donnees['critique_event'] ;
			$tab.= '<a href="-Critiques-?id_article=' . $critique_event . '#anc_' . $critique_event .'" title="Cliquez ici pour lire la critique">
			<img src="agenda/design_pics/ico_critique_mini.jpg"/></a>' ;
		}

		$tab.= '</span>' ;

		// ____________________________________________
		// VIGNETTE EVENEMENT	
		if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
		{
			$nom_event = $donnees ['nom_event'] ;
			$id_event = $donnees ['id_event'] ;
			$tab.= '<span class="breve_pic"><a href="-Detail-agenda-?id_event=' . $id_event . '"><img src="agenda/' . $folder_pics_event . 'vi_event_' . $id_event . '_1.jpg" title="' . $nom_event . '" /></a></span>';
		}
		
		
		// ____________________________________________
		// NOM EVENEMENT
		
		if (isset($requete_txt) AND $requete_txt != '' AND $requete_txt != 'nom de l\'événement' AND stristr ($donnees['nom_event'], $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse
		{

			$pattern = "!$requete_txt!i" ;
			$souligne = '<span class="souligne">' . $requete_txt .'</span>' ;
			$nom_origin = $donnees['nom_event'] ;
			
			$nom_souligne = preg_replace($pattern, $souligne, $nom_origin);
			
			$tab.= '<div class="breve_titre"><a href="-Detail-agenda-?id_event=' . $id_event . '" title="Voir en détail">
			' . $nom_souligne . '</a></div>';
		}
		else
		{
			$tab.= '<div class="breve_titre"><a href="-Detail-agenda-?id_event=' . $id_event . '" title="Voir en détail">
			' . $donnees['nom_event'] . '</a></div>';
		}
	
	
		// ____________________________________________
		// ID
		$tab.= ' <span class="id_breve">(id ' . $donnees ['id_event'] . ')</span><br />' ;
	
	
		// ____________________________________________
		// LIEU
		$id_lieu = $donnees['lieu_event'] ;
		$reponse_2 = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = $id_lieu");
		$donnees_2 = mysql_fetch_array($reponse_2) ;
				
		$tab.= '<span class="breve_lieu"><a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="Lieu où se joue le spectacle">' . $donnees_2['nom_lieu'] . '</a></span> ';	
	
	
		// ____________________________________________
		// GENRE
		
		if (isset($donnees['genre_event']) AND ($donnees['genre_event'] != NULL)) 
		{
			$genre_name = $donnees['genre_event'] ;
			$tab.= '<span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . 
			'</acronym></span> ';	
		}


		// ____________________________________________
		// DATES
		
		$date_event_debut = $donnees ['date_event_debut'];	
		$date_event_debut_annee = substr($date_event_debut, 0, 4);
		$date_event_debut_mois = substr($date_event_debut, 5, 2);
		$date_event_debut_jour = substr($date_event_debut, 8, 2);
		
		$date_event_fin = $donnees ['date_event_fin'];
		$date_event_fin_annee = substr($date_event_fin, 0, 4);
		$date_event_fin_mois = substr($date_event_fin, 5, 2);
		$date_event_fin_jour = substr($date_event_fin, 8, 2);
	
		// note : pour mois en LETTRES : $NomDuMois[$date_event_debut_mois+0]
		$tab.= ' <span class="breve_date"><acronym title="Période de représentation">' . $date_event_debut_jour . '/'
		. $date_event_debut_mois . '/'
		. $date_event_debut_annee . ' &gt;&gt; ' . $date_event_fin_jour . '/'
		. $date_event_fin_mois . '/'
		. $date_event_fin_annee . '</acronym></span>';	
	

		// ____________________________________________
		// VILLE
		
		if (isset($donnees['ville_event']) AND ($donnees['ville_event'] != NULL)) 
		{
			$ville_event_de_db = $donnees['ville_event'] ;
			$tab.= '<span class="breve_date"><acronym title="Ville où du spectacle">' . $regions[$ville_event_de_db] . 
			'</acronym></span><br /><br />';	
		}
		
		
		// ____________________________________________
		// TEXTE RESUME 
		
		// Afficher texte résumé et événtuellement souligner le mot rechercé par l'utilisateur
		$txt_decod = $donnees['resume_event'] ;
		if (isset($requete_txt) AND $requete_txt != 'nom de l\'événement' AND stristr ($txt_decod, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse) = test d'existence
		{
			$txt_resume = stripslashes($donnees['resume_event']) ;
			/*$endroit = strpos($txt_resume, $requete_txt) ; // Retourne la position numérique de la première occurrence. La fonction "mb_strpos" est mieux, si PHP 5 -> remplacer par "mb_stripos" (insensible à la casse)

			if (($endroit-150) < 0) // tester si l'expression recherchée ne se trouve pas en tout début d'expression
			{
				$chaine_reduite = substr($txt_resume, 0, 350); // prendre seulement le segment de la chaine qui entoure le mot clé (départ posision 0)
			}
			else
			{
				$chaine_reduite = substr($txt_description, ($endroit-150), 350); // prendre seulement le segment de la chaine qui entoure le mot clé
				$chaine_reduite = ' ... ' . strstr($chaine_reduite,' ');// Couper après PREMIER espace (pour pas couper un mot en deux)

			}
			$espace=strrpos($chaine_reduite," "); // Couper après DERNIER espace (pour pas couper un mot en deux)
			$chaine_reduite = substr($chaine_reduite,0,$espace) . ' ... ' ;*/

			$pattern = "!$requete_txt!i" ;
			$souligne = '<span class="souligne">' . $requete_txt .'</span>' ;
			
			$tab.= preg_replace($pattern, $souligne, $txt_resume);
			
			 
		}
		else
		{
			// Si pas de recherche contextuelle, simplement afficher résumé

			// Remplacer les retours de ligne
			$resum_txt = $donnees['resume_event'] ;
			$array_retour_ligne = array("<br>", "<br />", "<BR>", "<BR />");
			$uuuuueeeeeeee = str_replace($array_retour_ligne, " ", $resum_txt);
			$tab.= $uuuuueeeeeeee ;

		}
		
		
		
	
	
		// **************************************************************************************************
		//Si l'expression recherchée par le visiteur se trouve dans le TEXTE DE DESCRIPTION, afficher la portion concernée	
		// **************************************************************************************************

		$txt_decod = $donnees['description_event'] ;
		if (isset($requete_txt) AND $requete_txt != 'nom de l\'événement' AND stristr ($txt_decod, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse) = test d'existence
		{
			$txt_description = strip_tags(stripslashes($donnees['description_event'])) ;
			$endroit = strpos($txt_description, $requete_txt) ; // Retourne la position numérique de la première occurrence. La fonction "mb_strpos" est mieux, si PHP 5 -> remplacer par "mb_stripos" (insensible à la casse)

			if (($endroit-150) < 0) // tester si l'expression recherchée ne se trouve pas en tout début d'expression
			{
				$chaine_reduite = substr($txt_description, 0, 350); // prendre seulement le segment de la chaine qui entoure le mot clé (départ posision 0)
			}
			else
			{
				$chaine_reduite = substr($txt_description, ($endroit-150), 350); // prendre seulement le segment de la chaine qui entoure le mot clé
				$chaine_reduite = ' ... ' . strstr($chaine_reduite,' ');// Couper après PREMIER espace (pour pas couper un mot en deux)

			}
			$espace=strrpos($chaine_reduite," "); // Couper après DERNIER espace (pour pas couper un mot en deux)
			$chaine_reduite = substr($chaine_reduite,0,$espace) . ' ... ' ;

			$pattern = "!$requete_txt!i" ;
			$souligne = '<span class="souligne">' . $requete_txt .'</span>' ;
			
			$texte_souligne = preg_replace($pattern, $souligne, $chaine_reduite);
			
			$tab.= '<br />' . $texte_souligne ;	
		}

		
	$tab.= '<div class="en_savoir_plus">' ;
	
			// Afficher bouton de Réservation
			if (!empty($donnees_2['email_reservation']) AND $donnees_2['email_reservation'] != NULL 
			AND ($donnees['genre_event'] != 'g07'))
			{
				$tab.= '<a href="-Reserver-?id_event='. $id_event .'" title="Réservez vos places en ligne !!" >
				<img src="agenda/design_pics/bouton_reserver.jpg"  hspace="10"  /></a>' ;
			}

			
			// Lien e-card
			$tab.= '<a href="-Envoyer-a-un-ami-?id_event=' . $id_event . '">
			<img src="agenda/e_card/pics/ico_envoyer_ami.jpg" title="Informer un ami" alt="Informer un ami" /></a>' ;
			
						
			// Lien "en savoir plus"
			$tab.= '<a href="-Detail-agenda-?id_event=' . $id_event . '">
			<img src="agenda/design_pics/ensavoirplus.jpg" title="En savoir plus" alt="En savoir plus" /></a>
			</div><div class="float_stop"></div></div>' ;
	
	}
	

	 
	echo $tab ;
}

?>
