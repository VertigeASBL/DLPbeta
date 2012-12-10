<?php

// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Infos sur le déroulement du script :
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

/*
La recherche peut être effectuée par URL, calendrier de dates, par formulaire... Le test s'effectue ainsi :
Test : Les variables pour la requête viennent-elles
 - 4-a) de l'URL, exemple : une URL pour afficher une sélection spécifique
 - 4-b) de l'URL, uniquement le jour à afficher, qui est donné en cliquant sur le calendrier
 - 3) du formulaire qui est posté en appuyant sur le bouton "Rechercher" 3)
 - 2) de sessions : $_SESSION['recherche']=='oui' en est le témoin. C'est le cas lorsqu'on revient sur la page après l'avoir quittée
 - 1) de nulle part car inexistantes. Si $_SESSION['recherche'] est différent de'oui', alors c'est le cas, et donc c'est la première visite

	Afin de pouvoir ordonner le résultat de la recherche (pas de répétition de LIEU...) on récolte les ID données par la requête, on les réordonne,
	puis on les stocke dans un Array qui sera mis en SESSION et lu à chaque demande de pagination. cfrr "Début du Tri"
*/

// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP
// PARAMETRES : 
// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP
$items_par_page = 10 ; // Nombre d'événements affichés par page. /!\ que ça joue sur les requêtes via l'URL que les moteurs pourraient faire

$nb_jours_ahead = 15 ; // Pour cas 4-a)  N'est plus actif ! 

// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP

/*error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
*/

$voir_debug = '


' ; // Initialisation de la variable de concaténation des messages de débogage
require 'agenda/inc_db_connect.php';
require 'agenda/inc_var.php';
require 'agenda/inc_fct_base.php';
require 'agenda/calendrier/inc_calendrier.php';


/*
http://docs.jquery.com/Events/change#examples

Doc pour autocomplete :
http://www.dator.fr/les-requetes-ajax-avec-jquery/
http://www.dynamicajax.com/fr/JSON_AJAX_Web_Chat-.html
*/
?>



  


<?php

/* 4-a) et 4-b)
Récupérer les variables transmises dans l'URL, les enregistrer en variables SESSION (car utile pour la pagination)
*/

if ((isset($_GET['req']) AND $_GET['req'] == 'ext')
OR (isset($_GET['req']) AND $_GET['req'] == 'mini_calendr'))
{
	$voir_debug.= '<br />
	*************Cas 4-a) et 4-b)*************';
	$voir_debug.= '<br />
	Requete extérieur';
	$_SESSION['recherche'] = 'non' ; // Les paramètres SESSIONS ne sont pas TOUS utiles dans ce cas. Uniquement celui de requête finale est utile pour la pagination
	unset ($_SESSION['page_aff']) ; // On les écrase pour ne pas biaiser la requête
	
	// genre_event
	if (isset($_GET['genre']) AND $_GET['genre'] != NULL)
	{ 
		$genre_event = htmlentities($_GET['genre'], ENT_QUOTES) ;
		$_SESSION['genre_event'] = $genre_event ;
		$voir_debug.= '<br />
		La Variable _GET "genre_event" est précisée et vaut "' . $genre_event . '" ';
	}
	else
	{
		$_SESSION['genre_event'] = '' ;
		$genre_event = '' ;
	}

	// lieu
	if (isset($_GET['lieu']) AND $_GET['lieu'] != NULL)
	{ 
		$lieu_event = htmlentities($_GET['lieu'], ENT_QUOTES) ;
		$_SESSION['lieu_event'] = $lieu_event ;
		$voir_debug.= '<br />
		La Variable _GET "lieu" est précisée et vaut "' . $lieu_event . '" ';
	}
	else
	{
		$lieu_event = '' ;
		$_SESSION['lieu_event'] = '' ;
	}
	
	// ville
	if (isset($_GET['ville']) AND $_GET['ville'] != NULL)
	{ 
		$ville_event = htmlentities($_GET['ville'], ENT_QUOTES) ;
		$_SESSION['ville_event'] = $ville_event ;
		$voir_debug.= '<br />
		La Variable _GET "ville" est précisée et vaut "' . $ville_event . '" ';
	}
	else
	{
		$ville_event = '' ;
		$_SESSION['ville_event'] = '' ;
	}
		
	// date_debut
	if (isset($_GET['date_debut']) AND $_GET['date_debut'] != NULL)
	{ 
		$date_debut = htmlentities($_GET['date_debut'], ENT_QUOTES) ;
		$_SESSION['date_debut'] = $date_debut ;
		$voir_debug.= '<br />
		La Variable _GET "date_debut" est précisée et vaut "' . $date_debut . '" ';
	}
	else
	{
		$date_debut = date ('d-m-Y', $date_debut = mktime(0, 0, 0, date("m"), date("d"), date("Y"))); 
		//$date_debut = '01-01-2007' ; // 1er event de DLP car on affiche TOUT (avec pagination)
		$_SESSION['date_debut'] = $date_debut ;
	}
		
	// date_fin
	if (isset($_GET['date_fin']) AND $_GET['date_fin'] != NULL)
	{ 
		$date_fin = htmlentities($_GET['date_fin'], ENT_QUOTES) ;
		$_SESSION['date_fin'] = $date_fin ;
		$voir_debug.= '<br />
		La Variable _GET "date_fin" est précisée et vaut "' . $date_fin . '" ';
	}
	else
	{
		// Aujourd'hui + 15 jours afin d'afficher les "prochains événements"
		//$date_fin = date(mktime(0, 0, 0, date('m'), date('d') + $nb_jours_ahead, date('Y')));
		//$date_fin = date('d-m-Y', $date_fin);
		$date_fin = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m")+3 , date("d"), date("Y"))); // Ceci est valable quand on vient de la page http://www.demandezleprogramme.be/-Les-lieux-partenaires- (=liste des LIEUX)
		$_SESSION['date_fin'] = $date_fin ;
	}
}
else
{
	/* Reprendre le contenu des variables SESSION provenant du formulaire posté, du retour à la page ou d'un lien de pagination. 
	Si elles sont inexistantes, les déclarer comme vides : */
	
	$voir_debug.= '<br />
	*************Cas ELSE de 4-a) et 4-b)*************';

	//$_SESSION['recherche'] = 'oui' ;
	
	// genre_event
	if (isset($_SESSION['genre_event']) AND $_SESSION['genre_event'] != NULL AND $_SESSION['genre_event'] != 'non_selct')
	{ 
		$genre_event = htmlentities($_SESSION['genre_event'], ENT_QUOTES) ;
		$voir_debug.= '<br />
		La Variable _SESSION "genre_event" reçoit la valeur "' . $genre_event . '" ';
	}
	else
	{
		$genre_event = '' ;
	}
	
	// lieu_event
	if (isset($_SESSION['lieu_event']) AND $_SESSION['lieu_event'] != NULL AND $_SESSION['lieu_event'] != 'non_selct')
	{ 
		$lieu_event = htmlentities($_SESSION['lieu_event'], ENT_QUOTES) ;
		$voir_debug.= '<br />
		La Variable _SESSION "lieu_event" reçoit la valeur "' . $lieu_event . '" ';
	}
	else
	{
		$lieu_event = '' ;
	}
	
	// ville_event
	if (isset($_SESSION['ville_event']) AND $_SESSION['ville_event'] != NULL AND $_SESSION['ville_event'] != 'non_selct')
	{ 
		$ville_event = htmlentities($_SESSION['ville_event'], ENT_QUOTES) ;
		$voir_debug.= '<br />
		La Variable _SESSION "ville_event" reçoit la valeur "' . $ville_event . '" ';
	}
	else
	{
		$ville_event = '' ;
	}
	
	// date_debut
	if (isset($_SESSION['date_debut']) AND $_SESSION['date_debut'] != NULL)
	{ 
		$date_debut = htmlentities($_SESSION['date_debut'], ENT_QUOTES) ;
		$voir_debug.= '<br />
		La Variable _SESSION "date_debut" reçoit la valeur "' . $date_debut . '" ';
	}
	else
	{
		$date_debut = '' ;
	}
	
	// date_fin
	if (isset($_SESSION['date_fin']) AND $_SESSION['date_fin'] != NULL)
	{ 
		$date_fin = htmlentities($_SESSION['date_fin'], ENT_QUOTES) ;
		$voir_debug.= '<br />
		La Variable _SESSION "date_fin" reçoit la valeur "' . $date_fin . '" ';
	}
	else
	{
		$date_fin = '' ;
	}
	// # SESSION /// SESSION /// SESSION /// SESSION /// SESSION /// SESSION /// SESSION /// SESSION /// SESSION /// SESSION /// 

}
// 4-b)
/* Spécificités : 
a/ On doit écrire les paramètres de SESSION nécessaires à la pagination. 
b/ Le paramètre de l'URL "&requ=calendrier" est aussi nécessaire
*/



/* 3)
Récupération des variables postées
*/
if (isset($_POST['go']) AND ($_POST['go'] == 'Lancer la recherche' OR $_POST['go'] == ' '))
{
	$voir_debug.= '<br />
	*************Cas 3) *************<br />';
	$_SESSION['page_aff'] = 1 ; //Quand on lance la recherche, on affiche toujours la première page 

	$voir_debug.= '<br>
	Le bouton "Lancer la recherche" a été cliqué ';
	
	// Tester les variables postées :
	
	// genre_event
	if (isset($_POST['genre_event']) AND $_POST['genre_event'] != NULL AND $_POST['genre_event'] != 'non_selct')
	{ 
		$genre_event = htmlentities($_POST['genre_event'], ENT_QUOTES) ;
		$_SESSION['genre_event'] = $genre_event ;
		$voir_debug.= '<br />
		La Variable _POST "genre_event" est précisée et vaut "' . $genre_event . '" ';
	}
	else
	{
		$_SESSION['genre_event'] = '' ;
		$genre_event = '' ;
	}
		
	// lieu_event
	if (isset($_POST['lieu_event']) AND $_POST['lieu_event'] != NULL AND $_POST['lieu_event'] != 'non_selct')
	{ 
		$lieu_event = htmlentities($_POST['lieu_event'], ENT_QUOTES) ;
		$_SESSION['lieu_event'] = $lieu_event ;
		$voir_debug.= '<br />
		La Variable _POST "lieu_event" est précisée et vaut "' . $lieu_event . '" ';
	}
	else
	{
		$_SESSION['lieu_event'] = '' ;
		$lieu_event = '' ;
	}
	
	// ville_event
	if (isset($_POST['ville_event']) AND $_POST['ville_event'] != NULL AND $_POST['ville_event'] != 'non_selct')
	{ 
		$ville_event = htmlentities($_POST['ville_event'], ENT_QUOTES) ;
		$_SESSION['ville_event'] = $ville_event ;
		$voir_debug.= '<br />
		La Variable _POST "ville_event" est précisée et vaut "' . $ville_event . '" ';
	}
	else
	{
		$_SESSION['ville_event'] = '' ;
		$ville_event = '' ;
	}
		
	// date_debut
	if (isset($_POST['date_debut']) AND $_POST['date_debut'] != NULL)
	{ 
		$date_debut = htmlentities($_POST['date_debut'], ENT_QUOTES) ;
		$_SESSION['date_debut'] = $date_debut ;
		$voir_debug.= '<br />
		La Variable _POST "date_debut" est précisée et vaut "' . $date_debut . '" ';
	}
	else
	{
		$_SESSION['date_debut'] = '' ;
		$date_debut = '' ;
	}
		
	// date_fin
	if (isset($_POST['date_fin']) AND $_POST['date_fin'] != NULL)
	{ 
		$date_fin = htmlentities($_POST['date_fin'], ENT_QUOTES) ;
		$_SESSION['date_fin'] = $date_fin ;
		$voir_debug.= '<br />
		La Variable _POST "date_fin" est précisée et vaut "' . $date_fin . '" ';
	}
	else
	{
		$_SESSION['date_fin'] = '' ;
		$date_fin = '' ;
	}


	// nom_event
	if (isset($_POST['chp_txt_libre']) AND $_POST['chp_txt_libre'] != NULL)
	{ 
		//$chp_txt_libre = htmlentities($_POST['chp_txt_libre'], ENT_QUOTES) ;
		$chp_txt_libre = strip_tags($_POST['chp_txt_libre']);
		$chp_txt_libre = mysql_real_escape_string($chp_txt_libre);
		$chp_txt_libre = stripslashes($chp_txt_libre);
		$chp_txt_libre = trim($chp_txt_libre); 

		$voir_debug.= '<br />
		Le nom de l\'événement est précisé et vaut "' . $chp_txt_libre . '" ';
	}
	else
	{
		$chp_txt_libre = '' ;
	}
	
	$_SESSION['recherche'] = 'oui' ;
	
}

// 2)
elseif (isset($_SESSION['recherche']) AND $_SESSION['recherche'] == 'oui')
{
	$voir_debug.= '
	<br />*************Cas 2) *************<br />';
	$voir_debug.= '<br />
	Aucune action, mais des paramètres de SESSION existent';
}

// 1)
else
{
	$voir_debug.= '<br />
	*************Cas 1) *************<br />';
	$voir_debug.= '<br />
	Aucune action, et aucun paramètres de SESSION n\'existe. C\'est la première visite, via la page -Agenda-' ;
	// On introduit la variable qui va empêcher tout affichage d'événement
	$rien_voir = true ;
}


/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* 										   Requête Synchone												  */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */

// Si c'est une demande de changement de pagination, inutile de refaire toute la requete SQL 
if (isset($_GET['page_aff']) AND $_GET['page_aff'] != NULL) 
{
	$voir_debug.= '<br />
	Il s\'agit d\'un changement de pagination, donc PAS DE REQETE SQL';
}
else
{

	$voir_debug.= '<br />
	Il ne s\'agit PAS d\'un changement de pagination, donc REQETE SQL effectuée';
	
	// date_debut & date_fin
	// +++++++++++++++++++++
	/*
	Note sur les dates : 
	1] On souhaite les événements dont la PERIODE de représentation est comprise entre
	les dates limites de début et de fin. Donc utiliser la requête "NOT ()()()()"
	2] C'est ici que s'il n'y a pas d'indication de date de début, 
	on impose comme date de début la date du jour. Celle-ci est fournie alors au FORMULAIRE
	*/
	
	// Date de début :
	if ($date_debut == '') // si rien n'est précisé, alors...
	{
		// Date début pour requête = première année d'existance de DLP
		$date_debut = date ('d-m-Y', $date_debut = mktime(0, 0, 0, date("m"), date("d"), date("Y"))); 
		$date_debut_to_requete = date ('Y-m-d', $date_debut_to_requete = mktime(0, 0, 0, date("m"), date("d"), date("Y"))); 
		
		// Pour mini calendrier
		$date_mini_calendrier = date('d-m-Y');
		$date_mini_calendrier_annee = substr($date_mini_calendrier, 6, 4); 
		$date_mini_calendrier_mois = substr($date_mini_calendrier, 3, 2); 
		$date_mini_calendrier_jour = substr($date_mini_calendrier, 0, 2); 
	}
	else
	{
		// Date début pour requête = première année d'existance de DLP
		$date_debut_annee = substr($date_debut, 6, 4);
		$date_debut_mois = substr($date_debut, 3, 2);	
		$date_debut_jour = substr($date_debut, 0, 2);
		$date_debut_to_requete = $date_debut_annee.'-'.$date_debut_mois.'-'.$date_debut_jour ;

		// Pour mini calendrier
		$date_mini_calendrier_annee = substr($date_debut, 6, 4);
		$date_mini_calendrier_mois = substr($date_debut, 3, 2);	
	}
	// Date de fin : si rien n'et précisé, date fin = aujourd'hui + 3 mois
	if ($date_fin == '')
	{
		$date_fin_to_requete = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")+3 , date("d"), date("Y")));
		$date_fin = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m")+3 , date("d"), date("Y"))); 
	}
	else
	{
		$date_fin_annee = substr($date_fin, 6, 4);
		$date_fin_mois = substr($date_fin, 3, 2);	
		$date_fin_jour = substr($date_fin, 0, 2);
		$date_fin_to_requete = $date_fin_annee.'-'.$date_fin_mois.'-'.$date_fin_jour ;
	}
	
	// Début de la requête :
	$requete_concat = " WHERE 
	(ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND
	NOT ((date_event_debut < '$date_debut_to_requete') 
	AND (date_event_fin < '$date_debut_to_requete') 
	OR (date_event_debut > '$date_fin_to_requete') 
	AND (date_event_fin > '$date_fin_to_requete')) ";
	
	
/*	// Pour le tri par titre
	$requete_concat_ordre_titre = " WHERE 
	(ag_lieux.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND
	NOT ((date_event_debut < '$date_debut_to_requete') 
	AND (date_event_fin < '$date_debut_to_requete') 
	OR (date_event_debut > '$date_fin_to_requete') 
	AND (date_event_fin > '$date_fin_to_requete')) "; */
	

	// jour précis cliqué sur calendrier (req=mini_calendr) (Nov 2010)
	// ++++++++++++
	if (isset($_GET['req']) AND $_GET['req'] == 'mini_calendr')
	{
		$date_a_tester_dans_jours_actifs = substr($date_debut, 6, 4) . '-' . substr($date_debut, 3, 2) . '-' . substr($date_debut, 0, 2); 
		$voir_debug.= 'Jour unique cherché : ' . $date_a_tester_dans_jours_actifs . '<br />'  ;
		$requete_concat.= " AND jours_actifs_event LIKE '%$date_a_tester_dans_jours_actifs%' " ;

		// Pour le tri par titre $requete_concat_ordre_titre.= " AND jours_actifs_event LIKE '%$date_a_tester_dans_jours_actifs%' " ;
	}

	// genre_event
	// ++++++++++++
	if (isset($genre_event) AND $genre_event != '')
	{
		$requete_concat.= " AND genre_event = '$genre_event' " ;

		// Pour le tri par titre $requete_concat_ordre_titre.= " AND genre_event = '$genre_event' " ;

	}
	
	// lieu_event
	// ++++++++++++
	if (isset($lieu_event) AND $lieu_event != '')
	{
		$requete_concat.= " AND lieu_event = '$lieu_event' " ;

		// Pour le tri par titre $requete_concat_ordre_titre.= " AND lieu_event = '$lieu_event' " ;

	}
	
	// ville_event
	// ++++++++++++
	if (isset($ville_event) AND $ville_event != '')
	{
		$requete_concat.= " AND ville_event = '$ville_event' " ;

		// Pour le tri par titre $requete_concat_ordre_titre.= " AND ville_event = '$ville_event' " ;

	}

	$requete_concat_ordre_titre = $requete_concat;

	// chp_txt_libre
	// ++++++++++++
	if (isset($chp_txt_libre) AND $chp_txt_libre != '')
	{
		$requete_txt = $chp_txt_libre ; // pour mise en évidence du résultat (surlignage)
		$chp_txt_libre_echap = addslashes($requete_txt);
		$requete_concat.= " AND (nom_event LIKE '%$chp_txt_libre_echap%' OR description_event LIKE '%$chp_txt_libre_echap%' OR resume_event LIKE '%$chp_txt_libre_echap%') ";

		// Pour le tri par titre
		$requete_concat_ordre_titre.= " AND (nom_event LIKE '%$chp_txt_libre_echap%') ";

	}

/*	// page_aff  
	// ++++++++++++++++++
	
	if (isset($_GET['page_aff']) AND $_GET['page_aff'] != NULL)
	{ $page_aff = htmlentities($_GET['page_aff'], ENT_QUOTES) ; }
	elseif (isset($_SESSION['page_aff']) AND $_SESSION['page_aff'] != '')
	{ $page_aff = htmlentities($_SESSION['page_aff'], ENT_QUOTES) ; }
	else { $page_aff = 1 ; // Premier événement à afficher  
	}
	
	
	$_SESSION['page_aff'] = $page_aff ;
	$premierMessageAafficher = ($page_aff - 1) * $items_par_page;
	$requete_concat_limit = " LIMIT $premierMessageAafficher,$items_par_page "; // Ne sert plus 
	*/
	// Fin de concaténation de la requête
	// ++++++++++++++++++++++++++++++++++
	// Requête de comptage du nombre d'entrées
	$requete_concat_compter = "SELECT COUNT(*) AS nombre_evenements_a_afficher FROM ag_event 
	LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu
	$requete_concat " ;

	$reponse_compter = mysql_query($requete_concat_compter) or die (mysql_error());
	$donnees_compter = mysql_fetch_array($reponse_compter);
	$nombre_evenements_a_afficher = $donnees_compter['nombre_evenements_a_afficher'] ;
	$_SESSION['nombre_evenements_a_afficher'] = $donnees_compter['nombre_evenements_a_afficher'] ;

	$voir_debug.= '<br />
	Nombre d\'événements à afficher : ' . $nombre_evenements_a_afficher ;
	$voir_debug.= '<br />
	La Variable _SESSION "nombre_evenements_a_afficher" reçoit la valeur "' . $nombre_evenements_a_afficher . '"' ;

/*
SELECT A.id_event,A.parent_event,A.nom_event,A.date_event_debut,A.date_event_fin,A.pic_event_1,IF(A.parent_event=0,A.date_event_debut,B.date_event_debut) AS tridate
	FROM $table_evenements_agenda A,$table_evenements_agenda B
	WHERE A.lieu_event='$id' AND A.date_event_debut>'$date_debut_choix' AND A.date_event_debut<'$date_fin_choix' AND (B.id_event=A.parent_event OR A.parent_event=0 AND B.id_event=A.id_event)
	ORDER BY tridate DESC,A.parent_event,A.date_event_debut

Trier parents et sous-évén dès les requêtes comme dans listing_events.php ? ou ajouter l'info parent dans array_id_selection_tout et regrouper les 
parents et leurs sous-évén après dans le tri, et veiller à ajouter des sous-évén si moins que 3 ont été trouvés ?
chercher "tridate"


*/

	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	// 		Mettre tous les ID sélectionnés par la requête dans un Array
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	/*en premier, les événements en cours, classés par date de fin
	en second, les événements pas encore commencés, classés par date de fin */

	$array_id_selection_tout = array () ;	

	// Si le champ de texte libre est rempli, commencer le classement par les événement dont le titre contient la chaine "chp_txt_libre". On devra dédoublonner après la requête complète qui suit celle-ci . cfr "Dédoublonner le résultat des 2 requêtes"
	if (isset($chp_txt_libre) AND $chp_txt_libre != '')
	{
//		$requete_add_ordre_titre= " AND (nom_event LIKE '%$chp_txt_libre_echap%') "; --- ??? inutile

echo '<hr />requete_concat_ordre_titre :<br />',$requete_concat_ordre_titre,'<hr />',"\n";
		$requete_concat_ordre_titre = "SELECT id_event, lieu_event FROM ag_event 
		LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu
		$requete_concat_ordre_titre ORDER BY date_event_debut <= DATE_ADD(CURDATE(),INTERVAL 1 MONTH) DESC, date_event_fin" ;//test +1 mois
//		$requete_concat_ordre_titre ORDER BY date_event_debut <= CURDATE() DESC, date_event_fin" ;

		$reponse_synchone_titre = mysql_query($requete_concat_ordre_titre) or die ('err ordre titre : ' . mysql_error());
		
		while ($donnees_synchone_titre = mysql_fetch_array($reponse_synchone_titre))
		{
			$array_un_id_un_lieu = array ($donnees_synchone_titre['id_event'], $donnees_synchone_titre['lieu_event']) ;
			array_push($array_id_selection_tout, $array_un_id_un_lieu);
			//echo '<br>' . $donnees_synchone_titre['id_event'] .' <===> ' . $donnees_synchone_titre['lieu_event'] ;
		}
	}
//echo $requete_concat ;
print_r($array_id_selection_tout); echo '<hr />';

echo '<hr />requete_concat :<br />',$requete_concat,'<hr />',"\n";
	$requete_concat = "SELECT id_event, lieu_event FROM ag_event 
	LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu
	$requete_concat ORDER BY date_event_debut <= DATE_ADD(CURDATE(),INTERVAL 1 MONTH) DESC, date_event_fin" ;//test +1 mois
//	$requete_concat ORDER BY date_event_debut <= CURDATE() DESC, date_event_fin" ;

	$reponse_synchone = mysql_query($requete_concat) or die ('err requ sync : ' . mysql_error());
	
	//$array_id_selection_tout = array () ;	
	while ($donnees_synchone = mysql_fetch_array($reponse_synchone))
	{
		$array_un_id_un_lieu = array ($donnees_synchone['id_event'], $donnees_synchone['lieu_event']) ;
		array_push($array_id_selection_tout, $array_un_id_un_lieu);
		//echo '<br>*' . $donnees_synchone['id_event'] .' <===> ' . $donnees_synchone['lieu_event'] ;
	}
print_r($array_id_selection_tout); echo '<hr />';

	// Dédoublonner le résultat des 2 requêtes
	$array_id_dedoublonne = array();
	$ce_qui_y_est_deja = array();
	foreach ($array_id_selection_tout as $array_un_id_un_lieu_test)
	{
		//echo $array_un_id_un_lieu_test[0] . '<br>';
		if (!in_array($array_un_id_un_lieu_test["0"], $ce_qui_y_est_deja))
		{
			$array_id_dedoublonne[] = $array_un_id_un_lieu_test;
			$ce_qui_y_est_deja[] = $array_un_id_un_lieu_test[0];
		}
	}
echo 'array_id_dedoublonne<hr />';
print_r($array_id_dedoublonne); echo '<hr />';

/*
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT       Début du Tri      TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	$debug_table_des_id = '' ;
	
	// Table qui contient les ID rejetés car "LIEU identique consécutif"
	$array_id_doublons_temp = array ();
	
	// Table définitive ordonnée
	$array_id_ok = array ();
	
	
	$lieu_precedant = 10000 ; // initialisation à une valeur ne correspondant à aucun lieu
	$cpt_bcl = 0 ;
	
	// echo '<hr /><em> '.$cpt_bcl.') </em><pre>'; print_r($array_id_selection_tout); echo '</pre><hr />';
		
	//foreach($array_id_selection_tout as $elemt_array_en_cours)
	foreach($array_id_dedoublonne as $elemt_array_en_cours)
	{	
		$debug_table_des_id.= '<br><em> '.$cpt_bcl.') </em>ID ' . $elemt_array_en_cours['0'] . ' => lieu ' . $elemt_array_en_cours['1'] . ' ';
		
		//echo '<br><em> '.$cpt_bcl.') </em>ID ' . $elemt_array_en_cours['0'] . ' => lieu ' . $elemt_array_en_cours['1'] . ' ';
		
		if ($elemt_array_en_cours['1'] != $lieu_precedant) 
		{
			$debug_table_des_id.= ' - <strong>Rajout normal de la valeur</strong> - ';
			array_push($array_id_ok,array($elemt_array_en_cours['0'],$elemt_array_en_cours['1']));
		
			// Voir si on peut repêcher un event de la TABLE Temporaire
			$lieu_precedant = $elemt_array_en_cours['1'] ;
			
			// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
			// S'il y a un événement précédemment rejeté pour cause de "LIEU identique consécutif", il peut être repêché
			// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
			$taille_array_temp = sizeof($array_id_doublons_temp) ;
			if ($taille_array_temp > 0)
			{
				$debug_table_des_id.= ' +++ Fonction repêchage (Lieu différent de ' . $lieu_precedant . ') +++ ';
	
				//foreach($array_id_doublons_temp as $elemt_array_doublon_en_cours)
				reset($array_id_doublons_temp);
				while ($elemt_array_doublon_en_cours = current($array_id_doublons_temp))
				{
					$position_actu = key($array_id_doublons_temp) ;
					$debug_table_des_id.= '[Clé actuelle AA : ' . $position_actu . ' = ' . $elemt_array_doublon_en_cours[0] . ']';
			
					if ($lieu_precedant != $elemt_array_doublon_en_cours['1']) // si le lieu ne fait suite consécutive
					{
						$debug_table_des_id.= ' - <strong>Repêchage [AA] événement ID ' . $elemt_array_doublon_en_cours['0'] . ' => lieu ' . $elemt_array_doublon_en_cours['1'] . '</strong> - ';
						array_push($array_id_ok,array($elemt_array_doublon_en_cours['0'],$elemt_array_doublon_en_cours['1']));
						//$position_actu -- ;
						$debug_table_des_id.= ' - UNSET [AA] effectué sur entrée n° ' . $position_actu . ' de la table Temp';
	
						unset($array_id_doublons_temp[$position_actu]); 
						$lieu_precedant = $elemt_array_doublon_en_cours['1'] ;
						$debug_table_des_id.= ' - Le Lieu "précédant" devient "'.$lieu_precedant.'"';
					}
				next($array_id_doublons_temp);
				}
			}
			// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
		}
		else
		{
			// Cet événement provoque une suite consécutive de Lieu ! Il est refusé (donc mis dans Array Temp. On vérifie quand même si on peut repêcher un Lieu dela Table Temporaire
			
			$debug_table_des_id.= '<strong>LIEU déjà présent avant</strong>';
			
			// Voir si on peut repêcher un event de la TABLE Temporaire
			$lieu_precedant = $elemt_array_en_cours['1'] ;
			
			
			// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
			// S'il y a un événement précédemment rejeté pour cause de "LIEU identique consécutif", il peut être repêché
			// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
			$taille_array_temp = sizeof($array_id_doublons_temp) ;
			if ($taille_array_temp > 0)
			{
				$debug_table_des_id.= ' +++ Fonction repêchage (Lieu différent de ' . $lieu_precedant . ') +++ ';
	
				//foreach($array_id_doublons_temp as $elemt_array_doublon_en_cours)
				reset($array_id_doublons_temp);
	
				while ($elemt_array_doublon_en_cours = current($array_id_doublons_temp))
				{
					$position_actu = key($array_id_doublons_temp) ;
					$debug_table_des_id.= '[Clé actuelle BB : ' . $position_actu . ' = ' . $elemt_array_doublon_en_cours[0] . ']';
								
					if ($lieu_precedant != $elemt_array_doublon_en_cours['1']) // si le lieu ne fait suite consécutive
					{
						$debug_table_des_id.= ' - <strong>Repêchage [BB] événement ID ' . $elemt_array_doublon_en_cours['0'] . ' => lieu ' . $elemt_array_doublon_en_cours['1'] . '</strong> - ';
						array_push($array_id_ok,array($elemt_array_doublon_en_cours['0'],$elemt_array_doublon_en_cours['1']));
						//$position_actu -- ;
						$debug_table_des_id.= ' - UNSET [BB] effectué sur entrée n° ' . $position_actu . ' de la table Temp';
	
						unset($array_id_doublons_temp[$position_actu]); 
						$lieu_precedant = $elemt_array_doublon_en_cours['1'] ;
						$debug_table_des_id.= ' - Le Lieu "précédant" devient "'.$lieu_precedant.'"';
					}
				next($array_id_doublons_temp);
				}
			}
			// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
	
			array_push($array_id_doublons_temp,array($elemt_array_en_cours['0'],$elemt_array_en_cours['1']));
		
		}
		if (!isset($lieu_precedant))
		{	
			$lieu_precedant = $elemt_array_en_cours['1'] ;
		}
		
		//echo '<em> '.$cpt_bcl.') </em><pre>'; print_r($array_id_doublons_temp); echo '</pre>';
	
		$cpt_bcl ++ ;
	}					
	
	// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
	// Si finalement, il reste des Lieux consécutifs dans la table TEMP, les mettre malgré tout de façon consécutive :
	// RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
	$taille_array_temp = sizeof($array_id_doublons_temp) ;
	if ($taille_array_temp > 0)
	{
		$debug_table_des_id.= '<br> <br> ------------Consécutifs------------' ;
		foreach($array_id_doublons_temp as $elemt_array_doublon_restant)
		{
			$debug_table_des_id.= '<br> - ID ' . $elemt_array_doublon_restant['0'] . ' => lieu ' . $elemt_array_doublon_restant['1'] . ' <strong> Est placé de façon consécutive : </strong>';
			array_push($array_id_ok,array($elemt_array_doublon_restant['0'],$elemt_array_doublon_restant['1']));
			$debug_table_des_id.= 'Dépilement effectué sur la table Temp';
			array_shift($array_id_doublons_temp); 
		}
	}
	
	$_SESSION['array_id_ok'] = $array_id_ok;
	$voir_debug.= '<br />
	La Variable _SESSION "array_id_ok" reçoit le tableau contenant les id_event';
	
	$debug_table_des_id.= '<hr>' ;
	
	// Visualiser tableau de fin :
	$debug_table_des_id.= '<strong><em>Table finale</em></strong>' ;

	$cpt_bcl = 0 ;
	$pirlilou = '' ;
	foreach($array_id_ok as $array_id_ok_element)
	{
		$debug_table_des_id.= '<br><em> '.$cpt_bcl.') </em>ID ' . $array_id_ok_element['0'] . ' => lieu ' . $array_id_ok_element['1'] . ' ';
		$pirlilou.= '<br><em> '.$cpt_bcl.') </em>ID ' . $array_id_ok_element['0'] . ' => lieu ' . $array_id_ok_element['1'] . ' ';
		$cpt_bcl ++ ;
	}
	
	$debug_table_des_id.= '<hr>' ;
	
	// Petit test pour voir s'il ne reste rien dans la DB Temp
	$taille_array_temp = sizeof($array_id_doublons_temp) ; 
	$debug_table_des_id.= ($taille_array_temp == 0) ? ('OK : Table Temp Vide') : ('<h3>!!! Table Temp pas Vide !!! </h3>') ;
	
echo 'array_id_ok<hr />';
print_r($array_id_ok); echo '<hr />';
	//echo $debug_table_des_id ;

	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT         FIN du Tri      TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
*/
	$_SESSION['array_id_ok'] = $array_id_dedoublonne;

	$_SESSION['requete_totale_effectuee'] = $requete_concat ;
	//$voir_debug.= '<br /><br />La requête effectuée et mise en SESSION est : ' . $requete_concat ;
	
} // Fin du "saut de la requête SQL" effectué lorsqu'un saut de pagination est effectué, ! $_GET['page_aff']

/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */


/* ####################################################################################################### */
/* ####################################################################################################### */
/* 												 Formulaire  												*/
/* ####################################################################################################### */
/* ####################################################################################################### */
?>

<form id="form_moteur_dlp_ajax" name="form_moteur_dlp_ajax" method="post" action="-Agenda-">
	
	<?php
	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_genre
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="genre_event" id="selecteur_genre">
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
	echo '</select> ';



	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_lieu
	// sélectionner uniquement ceux qui sont en ordre de paiement
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="lieu_event" id="selecteur_lieu">
	<option value="non_selct">tous les lieux/partenaires</option>';
	
	$reponse_2 = mysql_query("SELECT id_lieu, nom_lieu FROM ag_lieux 
	WHERE cotisation_lieu > CURDATE() ORDER BY nom_lieu") or die (mysql_error());

	while ($donnees_2 = mysql_fetch_array($reponse_2))
	{
		// Raccourcir la chaine :
		$nom_lieu_court = $donnees_2['nom_lieu'] ;
		$max=34; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
		$chaine_raccourcie = raccourcir_chaine_net ($nom_lieu_court,$max); // retourne $chaine_raccourcie
		
		echo '<option value="' . $donnees_2['id_lieu'] .'"';		
		// Faut-il pr&eacute;-s&eacute;lectionner
		if (isset($lieu_event) AND $donnees_2['id_lieu'] == $lieu_event )
		{ echo ' selected="selected" '; }
		echo '>'.$chaine_raccourcie.'</option>';
	}
	echo '</select> ';
	
	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_region
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="ville_event" id="selecteur_region">
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
	echo '</select> <br /> <br />';


	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_date_in et selecteur_date_out
	// http://docs.jquery.com/UI/Datepicker
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	if (isset($date_debut) AND $date_debut != NULL) 
	{ $valeur_date_debut = ' value="' . $date_debut . '" ' ; }
	else
	{ $valeur_date_debut = '' ; }
	echo 'La date de début : <input type="text" name="date_debut" id="selecteur_date_in" ' . $valeur_date_debut . ' /> ' ;

	if (isset($date_fin) AND $date_fin != NULL) 
	{ $valeur_date_fin = ' value="' . $date_fin . '" ' ; }
	else
	{ $valeur_date_fin = '' ; }	
	echo 'La date de fin : <input type="text" name="date_fin" id="selecteur_date_out" ' . $valeur_date_fin . ' /> <br /> <br /> ' ;
	
	

	?>
		
	
	
	<!-- Champ pour le texte libre -->	
	<div>Rechercher un événement : <input name="chp_txt_libre" type="text" size="30" value="" id="chp_txt_libre" /></div>

	<div class="suggestionsBox" id="suggestions" style="display: none;">
		<img src="agenda/moteur_2_3/pics/up_arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
		
		<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
	</div>	  
	<br />

	<div align="center">
	 <div class="bouton_en_file">
      <input id="effacer_tous_champs" name="effacer_tous_champs" value="Effacer tout" class="effacer_tous_champs" type="button" alt="Cliquez pour lancer la recherche" onSubmit="return prog_submit()">
	 </div>
	 
	 <div class="bouton_en_file">
      <input id="go" name="go" value="Lancer la recherche" class="go_recherche_ajax" type="submit" alt="Cliquez pour lancer la recherche">
	 </div>
	  

	 <div class="bouton_en_file">
	  	<div id="nbre_resultats_fleche" style="display: none;">
		  <div id="nbre_resultats_id" style="display: none;"></div>
		</div>
	 </div>
	 
	 <div class="float_stop">&nbsp;</div>
	
	<!-- Enlevé pour DLP <div id="montrer_selection_fleche" style="display: none;">
		<div id="montrer_selection"></div>
		<img src="agenda/moteur_2_3/pics/fleche_verte_b.gif" style="position: relative; top: 15px; left: 30px;" alt="upArrow" />
	</div> -->
	
	
	  
    </div>
</form>


	<div id="event_preview_id_fleche" style="display: none;">
		<img src="agenda/moteur_2_3/pics/fleche_grise_h.gif" style="position: relative; top: 3px; left: 50px;" alt="upArrow" />
		<div id="event_preview_id" style="display: none;"></div>
	</div>


<?php

/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */
/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */
/* 											Affichage du résultat										   */
/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */
/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */

// Calcul des liens de pagination :
$ilot_de_liens = 3 ; // représente le nombre de liens avant et après la position en cours pour le cas n°2 ci-dessous
$re_afficher_en_dessous = true ; // Pour tester s'il est nécessaire d'afficher une seconde barre de pagination sous les résultats

$nombre_evenements_a_afficher = $_SESSION['nombre_evenements_a_afficher'] ;
$nombre_de_pages  = ceil($nombre_evenements_a_afficher / $items_par_page);
$lien_de_pagination = '' ;
if ($nombre_de_pages > 0)
{
	$lien_de_pagination.= '<p class="style_liens_pagination">';
	// Si résultat unique : résultat au singulier
	if ($nombre_evenements_a_afficher == 1)
	{
		$re_afficher_en_dessous = false ; // Pour tester s'il est nécessaire d'afficher une seconde barre de pagination sous les résultats
		$lien_de_pagination.= '<strong>Un seul résultat</strong>';
	}
	// S'il y a plus que 1 résultat : résultats au pluriel
	else
	{
		$lien_de_pagination.= '<strong>' . $nombre_evenements_a_afficher . '</strong> résultats : ' ;
		// S'il y a plus de résultats que d'items par pages, afficher les paginations :
		if ($nombre_evenements_a_afficher > $items_par_page)
		{
			// Premier cas : il y a un nombre raisonable liens de pages à afficher
			if($nombre_de_pages < 10 )
			{
				for ($i_page = 1 ; $i_page <= $nombre_de_pages ; $i_page++)
				{
					$debut_sequence = ((($i_page - 1) * $items_par_page)+1) ;
					// S'il s'agit de la dernière page, afficher exactement le dernier item
					if (((($i_page - 1) * $items_par_page) + $items_par_page) > $nombre_evenements_a_afficher)
					{ $fin_sequence = $nombre_evenements_a_afficher ; }
					else { $fin_sequence = ((($i_page - 1) * $items_par_page) + $items_par_page) ; }	
					
					$add_couleur = ($page_aff == $i_page) ? ' class="encours" ' : '';
					$lien_de_pagination.= '<a href="-Agenda-?page_aff=' . $i_page . '" ' . $add_couleur . '> ' . 
					$debut_sequence . '..' . $fin_sequence . '</a> -  ';
				}
			}
			else
			{
			// Second cas : il y a trop de liens de pages à afficher, donc, n'afficher que le premier, le dernier et les 5 englobant l'actuel

				// Premier :
				$add_couleur = ($page_aff == 1) ? ' class="encours" ' : '';
				$lien_de_pagination.= '<a href="-Agenda-?page_aff=1" ' . $add_couleur . '> 
				1..' . $items_par_page . '</a> ';
				
				/* Doit on rajouter le caractère pour signifier qu'il y a des 
				valeurs "sautées" entre la première et le groupe de suivantes ?*/
				if(($page_aff - $ilot_de_liens)>1)
				{ $lien_de_pagination.= ' - --------' ; }
				
				$lien_de_pagination.= ' - ' ;

				// Ilot central centré autour de de la pagination actuelle
				for ($i_page = ($page_aff-$ilot_de_liens) ; $i_page <= ($page_aff+$ilot_de_liens) ; $i_page++)
				{
					// Ne pas déborder des limites de la première et dernière pagination
					if((($i_page - 1) * $items_par_page) < 1 
					OR ((($i_page - 1) * $items_par_page) + $items_par_page) > $nombre_evenements_a_afficher)
					{
						// PAS afficher
						//$lien_de_pagination.= ' PAS AFFICHER ';
					} 
					else
					{						
						$debut_sequence = ((($i_page - 1) * $items_par_page)+1) ;
						// S'il s'agit de la dernière page, afficher exactement le dernier item
						$fin_sequence = ((($i_page - 1) * $items_par_page) + $items_par_page) ;
						
						$add_couleur = ($page_aff == $i_page) ? ' class="encours" ' : '';
						$lien_de_pagination.= '<a href="-Agenda-?page_aff=' . $i_page . '" ' . $add_couleur . '> ' . 
						$debut_sequence . '..' . $fin_sequence . '</a> - ';
					}
				}
				/* Doit on rajouter le caractère pour signifier qu'il y a des 
				valeurs "sautées" entre la première et le groupe de suivantes ?*/
				if(($page_aff + $ilot_de_liens) < $nombre_de_pages-1)
				{ $lien_de_pagination.= '--------' ; }

				$lien_de_pagination.= ' - ' ;

				// Afficher le dernier lien
				$add_couleur = ($page_aff == $nombre_de_pages) ? ' class="encours" ' : '';

				$lien_de_pagination.= '<a href="-Agenda-?page_aff=' . $nombre_de_pages . '" ' . $add_couleur . '> ' . 
				((($nombre_de_pages - 1) * $items_par_page)+1) . '..' . $nombre_evenements_a_afficher . '</a> ';

			}
		}
	}
	$lien_de_pagination.= '</p>';
}
else
{
	$re_afficher_en_dessous = false ; // Pour tester s'il est nécessaire d'afficher une seconde barre de pagination sous les résultats
	$lien_de_pagination.= '<p class="style_liens_pagination">Aucun événement trouvé</p>';
}
echo $lien_de_pagination ;


$tab = '' ;	




//RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
// 			Rêquete d'affichage des résultats
//RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR

// La pagination fournit la plage des ID à récupérer dans l'Array trié
if (isset($_GET['page_aff']) AND $_GET['page_aff'] != NULL)
{ $page_aff = htmlentities($_GET['page_aff'], ENT_QUOTES) ; }
else { $page_aff = 1 ; /* Premier événement à afficher */ }

$premierMessageAafficher = ($page_aff - 1) * $items_par_page;


// convertir la portion d'ID pour la pagination actuelle en instruction "id_event=..." afin de placer celà dans la requête
$array_id_selection_limit = array_slice($_SESSION['array_id_ok'], $premierMessageAafficher, $items_par_page);

$id_event_concat = '' ; // Pour initialiser la suite
$cpt_iouuu = 0 ;
$tritouta = '' ;
while ($elemt_array_id_selection_limit = current($array_id_selection_limit))
{
	$tritouta.= '<br>' . $cpt_iouuu . ') ID : ' . $elemt_array_id_selection_limit[0] . ' <=> ' . $elemt_array_id_selection_limit[1] ;

	$cpt_iouuu++ ;
	next($array_id_selection_limit);
	
	$requete_concat = "SELECT * FROM ag_event 
	LEFT JOIN ag_lieux ON ag_event.lieu_event = ag_lieux.id_lieu
	LEFT JOIN ag_representation ON ag_event.pres_event = ag_representation.id_pres
	WHERE id_event= $elemt_array_id_selection_limit[0]" ;	

	$reponse_synchone = mysql_query($requete_concat) or die (mysql_error());
	$donnees_synchone = mysql_fetch_array($reponse_synchone);
	
	/*$id_event_concat.= ' id_event='.$elemt_array_id_selection_limit[0] ;
	if (key($array_id_selection_limit) < ($items_par_page-1))
	{
		$id_event_concat.= ' OR' ;
	}*/

		
	$tab.= '<div class="breve">' ;	
	$id_event = $donnees_synchone['id_event'] ;
	$saison_preced_event = $donnees_synchone['saison_preced_event'] ;

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
	if (isset ($donnees_synchone['interview_event']) AND $donnees_synchone['interview_event'] != 0 )
	{
		$interview_event = $donnees_synchone['interview_event'] ;
//--- richir	$tab.= '<a href="-Interviews-?id_article=' . $interview_event . '&amp;page=article-3" title...
		$tab.= '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" title="Cliquez ici pour lire l\'interview"><img src="agenda/design_pics/ico_interview_mini.jpg"/></a>' ;
	}

	// Icone Critique
	if (isset ($donnees_synchone['critique_event']) AND $donnees_synchone['critique_event'] != 0 )
	{
		$critique_event = $donnees_synchone['critique_event'] ;
		$tab.= '<a href="-Critiques-?id_article=' . $critique_event . '#anc_' . $critique_event .'" title="Cliquez ici pour lire la critique">
		<img src="agenda/design_pics/ico_critique_mini.jpg"/></a>' ;
	}



	// Icone "J'ai vu et aimé"
	if (isset ($donnees_synchone['jai_vu_event']) AND $donnees_synchone['jai_vu_event'] != 0 )
	{
		$jai_vu_event = $donnees_synchone['jai_vu_event'] ;
	}
	else
	{
		$jai_vu_event = ' ' ;
	}


	$lien_jai_vu = '<a href="#" onClick="popup_jai_vu';
	$lien_jai_vu.= "('agenda/jai_vu/jai_vu_popup.php?id=" . $id_event . "','Votons');";
	$lien_jai_vu.= ' return(false)">';
	
	/*$adresse_jai_vu = 'agenda/jai_vu/jai_vu_popup.php?id=' . $id_event ;
	$lien_jai_vu = '<a href="#voter" onClick="popup_jai_vu' ;
	$lien_jai_vu.= "('" . $adresse_jai_vu . "','Vote');" ; // Pas d'"espaces" dans le nom de la fenêtre sinon ça bloque dans IE
	$lien_jai_vu.='">' ;*/
	
	$tab.= '<div class="nombre_votes">' . 
	$lien_jai_vu . '
	<img src="agenda/design_pics/ico_jai_vu.jpg" title="cliquez pour voter pour cet événement" alt="cliquez pour voter pour cet événement" /></a>
	<div class="nombre_votes_bulle">' . $jai_vu_event .'</div></div>' ;



	$tab.= '</span>' ;


	// ____________________________________________
	// VIGNETTE EVENEMENT	
	if (isset ($donnees_synchone ['pic_event_1']) AND $donnees_synchone ['pic_event_1'] == 'set' )
	{
		$nom_event = $donnees_synchone ['nom_event'] ;
		$id_event = $donnees_synchone ['id_event'] ;
		$tab.= '<span class="breve_pic"><a href="-Detail-agenda-?id_event=' . $id_event . '"><img src="agenda/' . $folder_pics_event . 'vi_event_' . $id_event . '_1.jpg" title="' . $nom_event . '" /></a></span>';
	}
	
	
	// ____________________________________________
	// NOM EVENEMENT
	
	if (isset($requete_txt) AND $requete_txt != '' AND $requete_txt != 'nom de l\'événement' AND stristr ($donnees_synchone['nom_event'], $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse
	{

		$pattern = "!$requete_txt!i" ;
		$souligne = '<span class="souligne">' . $requete_txt .'</span>' ;
		$nom_origin = $donnees_synchone['nom_event'] ;
		
		$nom_souligne = preg_replace($pattern, $souligne, $nom_origin);
		
		$tab.= '<div class="breve_titre"><a href="-Detail-agenda-?id_event=' . $id_event . '" title="Voir en détail">
		' . $nom_souligne . '</a></div>';
	}
	else
	{
		$tab.= '<div class="breve_titre"><a href="-Detail-agenda-?id_event=' . $id_event . '" title="Voir en détail">
		' . $donnees_synchone['nom_event'] . '</a></div>';
	}


	// ____________________________________________
	// ID
	$tab.= ' <span class="id_breve">(id ' . $donnees_synchone ['id_event'] . ')</span><br />' ;


	// ____________________________________________
	// LIEU
	$pres_event = $donnees_synchone['pres_event'] ;
	$lieu_pres = 0;
	if ($pres_event) {
		$reponse_synchrone_2 = mysql_query("SELECT lieu_pres,nom_pres,nom_lieu FROM ag_representation,ag_lieux WHERE id_pres=$pres_event AND id_lieu=lieu_pres");
		if ($donnees_synchrone_2 = mysql_fetch_array($reponse_synchrone_2)) {
			$lieu_pres = $donnees_synchrone_2['lieu_pres'];
			$nom_pres = $donnees_synchrone_2['nom_pres'];
			$nom_lieu_pres = $donnees_synchrone_2['nom_pres'];
		}
	}

	$id_lieu = $donnees_synchone['lieu_event'] ;
	$reponse_synchrone_2 = mysql_query("SELECT nom_lieu,email_reservation FROM $table_lieu WHERE id_lieu=$id_lieu");
	if ($donnees_synchrone_2 = mysql_fetch_array($reponse_synchrone_2)) {
//		$tab.= '<span class="breve_lieu"><a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="Lieu où se joue le spectacle">' . $donnees_synchrone_2['nom_lieu'] . '</a></span> ';
		$tab.= '<span class="breve_lieu">producteur='.$donnees_synchrone_2['nom_lieu'];
		if ($lieu_pres && $id_lieu != $lieu_pres)
			$tab.= '<br />lieu repres='.$nom_pres.'/'.$nom_lieu_pres;
		$tab.= '</span> ';
	}
$tab.= '<br />parent='.$donnees_synchone['parent_event'].'<br />';	


	// ____________________________________________
	// GENRE
	
	if (isset($donnees_synchone['genre_event']) AND ($donnees_synchone['genre_event'] != NULL)) 
	{
		$genre_name = $donnees_synchone['genre_event'] ;
		$tab.= '<span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . 
		'</acronym></span> ';	
	}


	// ____________________________________________
	// DATES
	
	$date_event_debut = $donnees_synchone ['date_event_debut'];	
	$date_event_debut_annee = substr($date_event_debut, 0, 4);
	$date_event_debut_mois = substr($date_event_debut, 5, 2);
	$date_event_debut_jour = substr($date_event_debut, 8, 2);
	
	$date_event_fin = $donnees_synchone ['date_event_fin'];
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
	
	if (isset($donnees_synchone['ville_event']) AND ($donnees_synchone['ville_event'] != NULL)) 
	{
		$ville_event_de_db = $donnees_synchone['ville_event'] ;
		$tab.= '<span class="breve_date"><acronym title="Ville où du spectacle">' . $regions[$ville_event_de_db] . 
		'</acronym></span><br /><br />';	
	}
	
	
	// ____________________________________________
	// TEXTE RESUME 
	
	// Afficher texte résumé et événtuellement souligner le mot rechercé par l'utilisateur
	$txt_decod = $donnees_synchone['resume_event'] ;
	if (isset($requete_txt) AND $requete_txt != 'nom de l\'événement' AND stristr ($txt_decod, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse) = test d'existence
	{
		$txt_resume = stripslashes($donnees_synchone['resume_event']) ;
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
			$resum_txt = $donnees_synchone['resume_event'] ;
			$array_retour_ligne = array("<br>", "<br />", "<BR>", "<BR />");
			$uuuuueeeeeeee = str_replace($array_retour_ligne, " ", $resum_txt);
			$tab.= $uuuuueeeeeeee ;

	}


	// **************************************************************************************************
	//Si l'expression recherchée par le visiteur se trouve dans le TEXTE DE DESCRIPTION, afficher la portion concernée	
	// **************************************************************************************************

	$txt_decod = $donnees_synchone['description_event'] ;
	if (isset($requete_txt) AND $requete_txt != 'nom de l\'événement' AND stristr ($txt_decod, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse) = test d'existence
	{
		$txt_description = strip_tags(stripslashes($donnees_synchone['description_event'])) ;
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
	if (!empty($donnees_synchrone_2['email_reservation']) AND $donnees_synchrone_2['email_reservation'] != NULL 
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
	<img src="agenda/design_pics/ensavoirplus.jpg" title="En savoir plus" alt="En savoir plus" /></a>';
	
	$tab.= '</div><div class="float_stop"></div></div>' ;
}
	echo $tab ;
	
// Remettre les liens de pagnation si nécessaire
if ($re_afficher_en_dessous)
{
	echo '' . $lien_de_pagination ;
}


/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */


// LiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLi
// LiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLi
// Y a-t-il un article SPIP de la rubrique "Espace livres" dont le contenu contiendrait la chaine recherchée ?
// LiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLi

if (isset($chp_txt_libre) AND $chp_txt_libre != '')
{	
	$chaine_txt_libre = addslashes(htmlspecialchars($_POST['chp_txt_libre'])) ; // PS : le htmlentities ne va pas avec la DB SPIP
	
	$chaine_txt_libre_db = $chaine_txt_libre ;
	
	$nombre_resultats_requ_livre = "SELECT COUNT(*) AS nbre_entrees
	FROM spip_articles
	WHERE 
	(id_rubrique=126 OR id_rubrique=136 OR id_rubrique=135 OR id_rubrique=131)
	AND statut = 'publie' 
	AND (surtitre LIKE '%$chaine_txt_libre_db%' OR titre LIKE '%$chaine_txt_libre_db%' OR soustitre LIKE '%$chaine_txt_libre_db%' OR descriptif LIKE '%$chaine_txt_libre_db%'  OR chapo LIKE '%$chaine_txt_libre_db%'  OR texte LIKE '%$chaine_txt_libre_db%' OR ps LIKE '%$chaine_txt_libre_db%')" ;
	
	$reponse_nombre_resultats_requ_livre = mysql_query($nombre_resultats_requ_livre) or die(" Erreur requête NOMBRE Livre " . mysql_error());

	$nombre_resultats_livre = mysql_fetch_array($reponse_nombre_resultats_requ_livre);
	$nombre_resultats_livre = $nombre_resultats_livre['nbre_entrees'];
	if ($nombre_resultats_livre > 0)
	{
		$requ_livre = "SELECT id_article, titre, descriptif
		FROM spip_articles
		WHERE 
		(id_rubrique=126 OR id_rubrique=136 OR id_rubrique=135 OR id_rubrique=131)
		AND statut = 'publie' 
		AND (surtitre LIKE '%$chaine_txt_libre_db%' OR titre LIKE '%$chaine_txt_libre_db%' OR soustitre LIKE '%$chaine_txt_libre_db%' OR descriptif LIKE '%$chaine_txt_libre_db%'  OR chapo LIKE '%$chaine_txt_libre_db%'  OR texte LIKE '%$chaine_txt_libre_db%' OR ps LIKE '%$chaine_txt_libre_db%')" ;

		$reponse_livre = mysql_query($requ_livre) or die(" Erreur requête Livre " . mysql_error());
	
		echo '
		<div class="style_livre_bloc">
		 <div class="style_livre_titre_bloc">
			L\'expression &quot;' . stripslashes($chaine_txt_libre) . '&quot; donne '
			. $nombre_resultats_livre . ' résultat(s) dans l\'Espace Livres
		 </div>
		' ;
		
		while ($donnees_livre = mysql_fetch_array($reponse_livre))
		{
			echo '<a href="spip.php?article' . $donnees_livre['id_article'] . '"><span class="breve_titre"> ' . $donnees_livre['titre'] . '</span> </a>
			<span class="id_breve">(id' . $donnees_livre['id_article'] . ')</span>
			<br />';
		}
		echo '</div>' ;
	}
}

// LiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLi
// LiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLiLi




// Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-
// Y a-t-il un article SPIP de la rubrique "Espace livres" dont le contenu contiendrait la chaine recherchée ?
// Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-

if (isset($chp_txt_libre) AND $chp_txt_libre != '')
{	
	$chaine_txt_libre = addslashes(htmlspecialchars($_POST['chp_txt_libre'])) ; 
	$chaine_txt_libre_db = $chaine_txt_libre ;
	

		
	$req_nombre_resultats_texte_dans_lieu = "SELECT COUNT(*) AS nbre_entrees_lieu FROM ag_lieux 
	WHERE (
	(cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH))
	AND (
	(nom_lieu LIKE '%$chaine_txt_libre%') 
	OR (directeur_lieu LIKE '%$chaine_txt_libre%') 
	OR (contact_lieu LIKE '%$chaine_txt_libre%') 
	OR (adresse_lieu LIKE '%$chaine_txt_libre%') ))" ;

	$reponse_nombre_dans_lieu = mysql_query($req_nombre_resultats_texte_dans_lieu) or die(" Erreur requête NOMBRE LIEU " . mysql_error());

	$donnees_nombre_dans_lieu = mysql_fetch_array($reponse_nombre_dans_lieu);
	$nombre_resultats_dans_lieu = $donnees_nombre_dans_lieu['nbre_entrees_lieu'];
	if ($nombre_resultats_dans_lieu > 0)
	{
		$req_resultats_texte_dans_lieu = "SELECT id_lieu, nom_lieu FROM ag_lieux 
		WHERE (
		(cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH))
		AND (
		(nom_lieu LIKE '%$chaine_txt_libre%') 
		OR (directeur_lieu LIKE '%$chaine_txt_libre%') 
		OR (contact_lieu LIKE '%$chaine_txt_libre%') 
		OR (adresse_lieu LIKE '%$chaine_txt_libre%') ))" ;

		$reponse_dans_lieu = mysql_query($req_resultats_texte_dans_lieu) or die(" Erreur requête RESULTAT LIEU " . mysql_error());
	
		echo '
		<div class="style_livre_bloc">
		 <div class="style_livre_titre_bloc">
			L\'expression &quot;' . stripslashes($chaine_txt_libre) . '&quot; correspond à '
			. $nombre_resultats_dans_lieu . ' résultat(s) parmi les lieux culturels abonnés :
		 </div>
		' ;
		
		while ($donnees_dans_lieu = mysql_fetch_array($reponse_dans_lieu))
		{
			echo '<a href="-Details-lieux-culturels-?id_lieu=' . $donnees_dans_lieu['id_lieu'] . '">
			<span class="breve_titre"> ' . $donnees_dans_lieu['nom_lieu'] . '</span> </a>
			<span class="id_breve">(id' . $donnees_dans_lieu['id_lieu'] . ')</span>
			<br />';
		}
		echo '</div>' ;
	}
}

// Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-
// Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-


// Afficher texte de débogage :
echo '<div class="style_debug">' . $voir_debug . '<br /> <br /></div>' ;

/*print("<pre>");
print_r($_SESSION);
print("</pre>"); 

echo '</div>' ; */


// Débug : Comparer les valeurs Array originales et après tri + pagination
/*echo'<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td>'.$pirlilou.'</td>
    <td valign="top">'.$tritouta.'</td>
  </tr>
</table>';*/

?>