<?php
//session_start(); //--- déjà fait dans squelettes/rubrique=65.html
/*
echo '<br />genre_event : ',$_SESSION['genre_event'];
echo '<br />ville_event : ',$_SESSION['ville_event'];
echo '<br />lieu_event : ',$_SESSION['lieu_event'];
echo '<br />date_debut : ',$_SESSION['date_debut'];
echo '<br />date_fin : ',$_SESSION['date_fin'];
echo '<br />chp_txt_libre : ',$_SESSION['chp_txt_libre'];
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Infos sur le déroulement du script :
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

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
$maintenant = time();

//$nb_jours_ahead = 15 ; // Pour cas 4-a)  N'est plus actif ! 

// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP

/*error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
*/
$voir_debug = '';

require 'agenda/inc_db_connect.php';
require 'agenda/inc_var.php';
require_once 'agenda/inc_fct_base.php';
require_once 'agenda/calendrier/inc_calendrier.php';
//require_once 'ecrire/inc/filtres.php';
require_once 'ecrire/inc/utils.php';

// Fonction panier /-- Didier
include_once('agenda/panier/fonctions_panier.php');

/* 4-a) et 4-b)
Récupérer les variables transmises dans l'URL, les enregistrer en variables SESSION (car utile pour la pagination)
*/

if ((isset($_GET['req']) AND $_GET['req'] == 'ext')OR (isset($_GET['req']) AND $_GET['req'] == 'mini_calendr')) {
	$voir_debug.= '<br />*************Cas 4-a) et 4-b)*************';
	$voir_debug.= '<br />Requete extérieur';

	$_SESSION['recherche'] = 'non' ; // Les paramètres SESSIONS ne sont pas TOUS utiles dans ce cas. Uniquement celui de requête finale est utile pour la pagination
	unset($_SESSION['page_aff']) ; // On les écrase pour ne pas biaiser la requête
	
	// genre_event
	if (isset($_GET['genre']) AND $_GET['genre'] != NULL)
	{ 
		$genre_event = htmlentities($_GET['genre'], ENT_QUOTES) ;
		$_SESSION['genre_event'] = $genre_event ;
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
	}
	else
	{
		$date_debut = date ('d-m-Y'); //, $date_debut = mktime(0, 0, 0, date("m"), date("d"), date("Y"))
		//$date_debut = '01-01-2007' ; // 1er event de DLP car on affiche TOUT (avec pagination)
		$_SESSION['date_debut'] = $date_debut ;
	}
		
	// date_fin
	if (isset($_GET['date_fin']) AND $_GET['date_fin'] != NULL)
	{ 
		$date_fin = htmlentities($_GET['date_fin'], ENT_QUOTES) ;
		$_SESSION['date_fin'] = $date_fin ;
	}
	else
	{
		// Aujourd'hui + 15 jours afin d'afficher les "prochains événements"
		//$date_fin = date(mktime(0, 0, 0, date('m'), date('d') + $nb_jours_ahead, date('Y')));
		//$date_fin = date('d-m-Y', $date_fin);
		$date_fin = date ('d-m-Y', $maintenant + 7948800); // 92 jours / mktime(0, 0, 0, date("m")+3 , date("d"), date("Y")) Ceci est valable quand on vient de la page http://www.demandezleprogramme.be/-Les-lieux-partenaires- (=liste des LIEUX)
		$_SESSION['date_fin'] = $date_fin ;
	}
}
else {
	/* Reprendre le contenu des variables SESSION provenant du formulaire posté, du retour à la page ou d'un lien de pagination. 
	Si elles sont inexistantes, les déclarer comme vides : */
	
	$voir_debug.= '<br />*************Cas ELSE de 4-a) et 4-b)*************';

	//$_SESSION['recherche'] = 'oui' ;
	
	// genre_event
	if (isset($_SESSION['genre_event']) AND $_SESSION['genre_event'] != NULL AND $_SESSION['genre_event'] != 'non_selct')
	{ 
		$genre_event = htmlentities($_SESSION['genre_event'], ENT_QUOTES) ;
	}
	else
	{
		$genre_event = '' ;
	}
	
	// lieu_event
	if (isset($_SESSION['lieu_event']) AND $_SESSION['lieu_event'] != NULL AND $_SESSION['lieu_event'] != 'non_selct')
	{ 
		$lieu_event = htmlentities($_SESSION['lieu_event'], ENT_QUOTES) ;
	}
	else
	{
		$lieu_event = '' ;
	}
	
	// ville_event
	if (isset($_SESSION['ville_event']) AND $_SESSION['ville_event'] != NULL AND $_SESSION['ville_event'] != 'non_selct')
	{ 
		$ville_event = htmlentities($_SESSION['ville_event'], ENT_QUOTES) ;
	}
	else
	{
		$ville_event = '' ;
	}
	
	// date_debut
	if (isset($_SESSION['date_debut']) AND $_SESSION['date_debut'] != NULL)
	{ 
		$date_debut = htmlentities($_SESSION['date_debut'], ENT_QUOTES) ;
	}
	else
	{
		$date_debut = '' ;
	}
	
	// date_fin
	if (isset($_SESSION['date_fin']) AND $_SESSION['date_fin'] != NULL)
	{ 
		$date_fin = htmlentities($_SESSION['date_fin'], ENT_QUOTES) ;
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
if (isset($_POST['go']) AND ($_POST['go'] == 'Lancer la recherche' OR $_POST['go'] == ' ')) {
	$voir_debug.= '<br />*************Cas 3) *************<br />';

	$_SESSION['page_aff'] = 1 ; //Quand on lance la recherche, on affiche toujours la première page 

	$voir_debug.= '<br />Le bouton "Lancer la recherche" a été cliqué ';
	
	// Tester les variables postées :
	
	// genre_event
	if (isset($_POST['genre_event']) AND $_POST['genre_event'] != NULL AND $_POST['genre_event'] != 'non_selct')
	{ 
		$genre_event = htmlentities($_POST['genre_event'], ENT_QUOTES) ;
		$_SESSION['genre_event'] = $genre_event ;
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
	}
	else
	{
		$chp_txt_libre = '' ;
	}
	
	$_SESSION['recherche'] = 'oui' ;
}

// 2)
elseif (isset($_SESSION['recherche']) AND $_SESSION['recherche'] == 'oui') {
	$voir_debug.= '<br />*************Cas 2) *************<br />';
	$voir_debug.= '<br />Aucune action, mais des paramètres de SESSION existent';
}

// 1)
else {
	$voir_debug.= '<br />*************Cas 1) *************<br />';
	$voir_debug.= '<br />Aucune action, et aucun paramètres de SESSION n\'existe. C\'est la première visite, via la page -Agenda-' ;

	// On introduit la variable qui va empêcher tout affichage d'événement
	$rien_voir = true ;
}

/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
 										   Requête Synchone
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */

// Si c'est une demande de changement de pagination, inutile de refaire toute la requete SQL 
// La pagination fournit la plage des ID à récupérer dans l'Array trié

if (isset($_GET['page_aff']) AND $_GET['page_aff'] != NULL) {
	$page_aff = (int) $_GET['page_aff'];
	$voir_debug.= '<br />Il s\'agit d\'un changement de pagination, donc PAS DE REQETE SQL';
}
else {
	$page_aff = 1 ; /* Premier événement à afficher */
	$voir_debug.= '<br />Il ne s\'agit PAS d\'un changement de pagination, donc REQETE SQL effectuée';
	
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
		$date_debut = date ('d-m-Y'); //, $date_debut = mktime(0, 0, 0, date("m"), date("d"), date("Y"))
		$date_debut_to_requete = date ('Y-m-d'); //, $date_debut_to_requete = mktime(0, 0, 0, date("m"), date("d"), date("Y"))
		
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
		$date_fin = date ('d-m-Y', $maintenant + 7948800); // 92 jours / mktime(0, 0, 0, date("m")+3 , date("d"), date("Y"))); 
		$date_fin_to_requete = date ('Y-m-d', $maintenant + 7948800); // 92 jours / mktime(0, 0, 0, date("m")+3 , date("d"), date("Y")));
	}
	else
	{
		$date_fin_annee = substr($date_fin, 6, 4);
		$date_fin_mois = substr($date_fin, 3, 2);	
		$date_fin_jour = substr($date_fin, 0, 2);
		$date_fin_to_requete = $date_fin_annee.'-'.$date_fin_mois.'-'.$date_fin_jour ;
	}
	
	// Début de la requête :
//	$requete_where = " WHERE L.cotisation_lieu>SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND NOT (E.date_event_debut<'$date_debut_to_requete' AND E.date_event_fin<'$date_debut_to_requete' OR E.date_event_debut>'$date_fin_to_requete' AND E.date_event_fin>'$date_fin_to_requete') ";
	$requete_where = " WHERE L.cotisation_lieu>SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND E.date_event_fin>='$date_debut_to_requete' AND E.date_event_debut<='$date_fin_to_requete' ";	

	// jour précis cliqué sur calendrier (req=mini_calendr) (Nov 2010)
	// ++++++++++++
	if (isset($_GET['req']) AND $_GET['req'] == 'mini_calendr')
	{
		$date_a_tester_dans_jours_actifs = substr($date_debut, 6, 4) . '-' . substr($date_debut, 3, 2) . '-' . substr($date_debut, 0, 2); 
		$requete_where.= " AND E.jours_actifs_event LIKE '%$date_a_tester_dans_jours_actifs%' " ;
	}

	// genre_event
	// ++++++++++++
	if (isset($genre_event) AND $genre_event != '')
	{
		$requete_where.= " AND E.genre_event='$genre_event' " ;
	}
	
	// lieu_event
	// ++++++++++++
	if (isset($lieu_event) AND $lieu_event != '')
	{
		$requete_where.= " AND E.lieu_event='$lieu_event' " ;
	}
	
	// ville_event
	// ++++++++++++
	if (isset($ville_event) AND $ville_event != '')
	{
		$requete_where.= " AND E.ville_event='$ville_event' " ;
	}

	// chp_txt_libre
	// ++++++++++++
	if (isset($chp_txt_libre) AND $chp_txt_libre != '')
	{
		$_SESSION['requete_txt'] = $chp_txt_libre ; // pour mise en évidence du résultat (surlignage)
		$chp_txt_libre_echap = addslashes($chp_txt_libre);
		$requete_select = ",E.nom_event LIKE '%$chp_txt_libre_echap%' AS trouvemot";
		$requete_where .= " AND (E.nom_event LIKE '%$chp_txt_libre_echap%' OR E.description_event LIKE '%$chp_txt_libre_echap%' OR E.resume_event LIKE '%$chp_txt_libre_echap%') ";
	}
	else {
		$_SESSION['requete_txt'] = '';
		$requete_select = ',0 AS trouvemot';
	}

	// Fin de concaténation de la requête
	// ++++++++++++++++++++++++++++++++++

/*
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	// 		Requête de recherche
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA

	Grouper les événements-parents et leurs événements-enfants. Trier ensuite sur la date de début.
	Si le parent n'est pas sorti par la recherche, on le rajoutera dans les résultats.
	Si le parent est sorti seul par la recherche, on lui rajoutera 3 enfants dans les résultats.
	Ensuite on fera remonter les événements dont le mot de la recherche libre est trouvé dans le nom de l'événement.
	JOIN ag_lieux pour obtenir cotisation_lieu
	100 résultats maximum
*/
	//--- initialiser les tableaux de résultats
	$t_id_event = array();
	$t_trouvemot = array();
	$t_parent_event = array();
	//$t_lieu_event = array();

	$requete_concat = 'SELECT E.id_event'.$requete_select.',E.parent_event,E.lieu_event FROM ag_event F,ag_event E LEFT JOIN ag_lieux L ON E.lieu_event=L.id_lieu';
	$requete_concat .= $requete_where.'AND (F.id_event=E.parent_event OR E.parent_event=0 AND F.id_event=E.id_event) ';
	$requete_concat .= 'ORDER BY IF(E.parent_event=0,E.date_event_debut,F.date_event_debut),E.parent_event,E.date_event_debut LIMIT 100';

//echo '<hr />',nl2br($requete_concat),'<hr />',"\n";
	$reponse_synchone = mysql_query($requete_concat) or die ('err requ sync : ' . mysql_error());

	// Requête de comptage du nombre d'entrées - prov
	$nombre_evenements_a_afficher = mysql_num_rows($reponse_synchone);
	$voir_debug.= '<br />Nombre d\'événements à afficher : ' . $nombre_evenements_a_afficher ;
	$voir_debug.= '<br />La Variable _SESSION "nombre_evenements_a_afficher" reçoit la valeur "' . $nombre_evenements_a_afficher . '"' ;

	//--- enregistrer dans t_id_event - t_trouvemot - t_parent_event - t_lieu_event
	$donnees_1 = mysql_fetch_array($reponse_synchone);
	$mem_id_event = -1;
	while ($donnees_1) {
		if ($donnees_1['parent_event'] != $mem_id_event) {
			$mem_id_event = $donnees_1['id_event'];

			//--- ajouter le parent s'il manque
			if ($donnees_1['parent_event']) {
				$t_id_event[] = $donnees_1['parent_event'];
				$t_trouvemot[] = 0;
				$t_parent_event[] = 0;
//				$t_lieu_event[] = $donnees_1['lieu_event'];

				$mem_id_event = $donnees_1['parent_event'];

				$nombre_evenements_a_afficher++;
			}
		}
		//--- ajouter l'événemment trouvé
		$t_id_event[] = $donnees_1['id_event'];
		$t_trouvemot[] = $donnees_1['trouvemot'];
		$t_parent_event[] = $donnees_1['parent_event'];
//		$t_lieu_event[] = $donnees_1['lieu_event'];

		$mem_parent = $donnees_1['parent_event'];

		$donnees_1 = mysql_fetch_array($reponse_synchone);

		//--- ajouter des enfants si le parent est seul
		if ($mem_parent != $mem_id_event && ($donnees_1 && $donnees_1['parent_event'] != $mem_id_event || ! $donnees_1)) {
			$reponse_2 = mysql_query('SELECT id_event,lieu_event FROM ag_event E WHERE parent_event='.$mem_id_event.' ORDER BY date_event_debut LIMIT 3') or die (mysql_error());
			while ($donnees_2 = mysql_fetch_array($reponse_2)) {
				$t_id_event[] = $donnees_2['id_event'];
				$t_trouvemot[] = 0;
				$t_parent_event[] = $mem_id_event;
//				$t_lieu_event[] = $donnees_2['lieu_event'];

				$nombre_evenements_a_afficher++;
			}
		}
	}
/*
echo '<table cellspacing="0" cellpadding="2" border="1">',"\n";
echo '<tr><td>N°</td><td>id_event</td><td>trouvemot</td><td>parent_event</td><td>lieu_event</td></tr>',"\n";
for ($k = 0; isset($t_id_event[$k]); $k++)
	echo '<tr><td>',$k,'</td><td>',$t_id_event[$k],'</td><td>',$t_trouvemot[$k],'</td><td>',$t_parent_event[$k],'</td><td>',$t_lieu_event[$k],'</td></tr>',"\n";
echo '</table>',"\n";
*/

	//--- faire remonter les événements (le groupe parent et enfants) dont nom_event contient txt_libre
	$res_ofs = 0;
	$res_pos = -1;
	$res_len = count($t_id_event);
	reset($t_id_event);
	for ($k = 0; $k <= $res_len; $k++) {
		if ($res_pos == -1 && $k < $res_len && $t_trouvemot[$k] == 1)
			$res_pos = $k;
		if ($res_pos != -1 && ($k == $res_len || $t_trouvemot[$k] == 0)) {
			//--- monter pour trouver le parent
			for ($res_dbu = $res_pos; $res_dbu >= 0 && $t_parent_event[$res_dbu]; $res_dbu--)
				;
			//--- descendre pour trouver le dernier enfant
			for ($res_fin = $k; $res_fin < $res_len && $t_parent_event[$res_fin]; $res_fin++)
				;
			//--- Remonter les éléments [DBU..FIN[ vers la position OFS. Inverser l'ordre des éléments : 1) de OFS à DBU, 2) de DBU à FIN, 3) de OFS à FIN.
			if ($res_ofs < $res_dbu) {
				for ($i = $res_ofs, $j = $res_dbu, $j--; $i < $j; $i++, $j--) {
					$mem = $t_id_event[$i]; $t_id_event[$i] = $t_id_event[$j]; $t_id_event[$j] = $mem;
					$mem = $t_trouvemot[$i]; $t_trouvemot[$i] = $t_trouvemot[$j]; $t_trouvemot[$j] = $mem;
					$mem = $t_parent_event[$i]; $t_parent_event[$i] = $t_parent_event[$j]; $t_parent_event[$j] = $mem;
//					$mem = $t_lieu_event[$i]; $t_lieu_event[$i] = $t_lieu_event[$j]; $t_lieu_event[$j] = $mem;
				}
				for ($i = $res_dbu, $j = $res_fin, $j--; $i < $j; $i++, $j--) {
					$mem = $t_id_event[$i]; $t_id_event[$i] = $t_id_event[$j]; $t_id_event[$j] = $mem;
					$mem = $t_trouvemot[$i]; $t_trouvemot[$i] = $t_trouvemot[$j]; $t_trouvemot[$j] = $mem;
					$mem = $t_parent_event[$i]; $t_parent_event[$i] = $t_parent_event[$j]; $t_parent_event[$j] = $mem;
//					$mem = $t_lieu_event[$i]; $t_lieu_event[$i] = $t_lieu_event[$j]; $t_lieu_event[$j] = $mem;
				}
				for ($i = $res_ofs, $j = $res_fin, $j--; $i < $j; $i++, $j--) {
					$mem = $t_id_event[$i]; $t_id_event[$i] = $t_id_event[$j]; $t_id_event[$j] = $mem;
					$mem = $t_trouvemot[$i]; $t_trouvemot[$i] = $t_trouvemot[$j]; $t_trouvemot[$j] = $mem;
					$mem = $t_parent_event[$i]; $t_parent_event[$i] = $t_parent_event[$j]; $t_parent_event[$j] = $mem;
//					$mem = $t_lieu_event[$i]; $t_lieu_event[$i] = $t_lieu_event[$j]; $t_lieu_event[$j] = $mem;
				}
			}
			$res_ofs += $res_fin - $res_dbu;

			$res_pos = -1;
		}
	}
	//----- Enregistrer en session pour ne pas avoir à refaire la recherche en cas de pagination
	$_SESSION['t_id_event'] = $t_id_event;
//	$_SESSION['t_trouvemot'] = $t_trouvemot;
	$_SESSION['t_parent_event'] = $t_parent_event;
//	$_SESSION['t_lieu_event'] = $t_lieu_event;
	$_SESSION['nombre_evenements_a_afficher'] = $nombre_evenements_a_afficher;

	$voir_debug.= '<br /><br />La requête effectuée et mise en SESSION est : ' . $requete_concat;
	
} // Fin du "saut de la requête SQL" effectué lorsqu'un saut de pagination est effectué, ! $_GET['page_aff']

/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
*/
	require_once 'agenda/calendrier/inc_calendrier.php';
	echo '<div class="recherche_et_calendrier" style="float:right;">',"\n";
	include('agenda/moteur_2_3/inc_mini_calendrier.php'); 
	echo '</div>',"\n";

 
/*   #######################################################################################################
   #######################################################################################################
  												 Formulaire
   #######################################################################################################
   ####################################################################################################### */

	echo '<form id="form_moteur_dlp_ajax" name="form_moteur_dlp_ajax" method="post" action="'.generer_url_entite(65, 'rubrique').'">',"\n";
	
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
	echo '</select>&nbsp;';


	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_region
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo ' <select name="ville_event" id="selecteur_region">
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
	echo '</select><br />';


	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_lieu
	// sélectionner uniquement ceux qui sont en ordre de paiement
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="lieu_event" id="selecteur_lieu">
	<option value="non_selct">tous les lieux / partenaires</option>';
	
	$reponse_2 = mysql_query("SELECT id_lieu, nom_lieu FROM ag_lieux WHERE cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH) ORDER BY nom_lieu") or die (mysql_error());

	while ($donnees_2 = mysql_fetch_array($reponse_2))
	{
		// Raccourcir la chaine :
		$nom_lieu_court = $donnees_2['nom_lieu'];
		$max=40; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
		$chaine_raccourcie = raccourcir_chaine_net ($nom_lieu_court,$max); // retourne $chaine_raccourcie
		
		echo '<option value="' . $donnees_2['id_lieu'] .'"';		
		// Faut-il pr&eacute;-s&eacute;lectionner
		if (isset($lieu_event) AND $donnees_2['id_lieu'] == $lieu_event )
		{ echo ' selected="selected" '; }
		echo '>'.$chaine_raccourcie.'</option>';
	}
	echo '</select><br />';
	
	

	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_date_in et selecteur_date_out
	// http://docs.jquery.com/UI/Datepicker
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	if (isset($date_debut) AND $date_debut != NULL) 
	{ $valeur_date_debut = ' value="' . $date_debut . '" ' ; }
	else
	{ $valeur_date_debut = '' ; }
	echo 'Date de début <input type="text" name="date_debut" id="selecteur_date_in" ' . $valeur_date_debut . ' /> ' ;

	if (isset($date_fin) AND $date_fin != NULL) 
	{ $valeur_date_fin = ' value="' . $date_fin . '" ' ; }
	else
	{ $valeur_date_fin = '' ; }	
	echo '&nbsp; Date de fin <input type="text" name="date_fin" id="selecteur_date_out" ' . $valeur_date_fin . ' /><br />' ;

//isset($_POST['recherche']) ? htmlspecialchars($_POST['recherche']) : (
//',isset($requete_txt) ? htmlspecialchars($requete_txt) : '','
//	echo '<input name="recherche" type="text" value="ma" />',"\n";
?>
	<!-- Champ pour le texte libre -->	
	<div>Rechercher un événement <input name="chp_txt_libre" type="text" size="30" value="" id="chp_txt_libre" /></div>

	<div class="suggestionsBox" id="suggestions" style="display: none;">
		<img src="agenda/moteur_2_3/pics/up_arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
		
		<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
	</div>	  
	<br />

	<div align="center">
	 <div class="bouton_en_file">
      <input id="effacer_tous_champs" name="effacer_tous_champs" value="Effacer les critères" class="effacer_tous_champs" type="button">
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

	/* Affiche un message d'alerte quand on veux suivre l'événement -- Didier Vertige */
	if (isset($_GET['suivre'])) {
		echo '<div class="spip_forms"><div class="spip_form_ok">';
		if (ajouter_panier($_SESSION['id_spectateur'], $_GET['id_event'])) {
			echo 'Cette événement à été ajouté à vos favoris.';
		}
		else {
			echo 'Une erreur est survenue';
		}
		echo '</div></div>';
	}

	/* Ne plus suivre l'événément */
	if (isset($_GET['plus_suivre'])) {
		echo '<div class="spip_forms"><div class="spip_form_ok">';
		if (enlever_panier($_SESSION['id_spectateur'], $_GET['id_event'])) {
			echo 'Cette événement à été retiré de vos favoris.';
		}
		else {
			echo 'Une erreur est survenue';
		}
		echo '</div></div>';
	}

/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
   AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
   											Affichage du résultat
   AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
   AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */

// Calcul des liens de pagination :
$ilot_de_liens = 3 ; // représente le nombre de liens avant et après la position en cours pour le cas n°2 ci-dessous
$re_afficher_en_dessous = true ; // Pour tester s'il est nécessaire d'afficher une seconde barre de pagination sous les résultats

$nombre_evenements_a_afficher = $_SESSION['nombre_evenements_a_afficher'];
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
	else // S'il y a plus que 1 résultat : résultats au pluriel
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
					$lien_de_pagination.= '<a href="?page_aff=' . $i_page . '" ' . $add_couleur . '> '.$debut_sequence . '..' . $fin_sequence . '</a> -  ';
				}
			}
			else
			{
			// Second cas : il y a trop de liens de pages à afficher, donc, n'afficher que le premier, le dernier et les 5 englobant l'actuel

				// Premier :
				$add_couleur = ($page_aff == 1) ? ' class="encours" ' : '';
				$lien_de_pagination.= '<a href="?page_aff=1" ' . $add_couleur . '> 1..' . $items_par_page . '</a> ';
				
				// Doit on rajouter le caractère pour signifier qu'il y a des valeurs "sautées" entre la première et le groupe de suivantes ?
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
						$lien_de_pagination.= '<a href="?page_aff=' . $i_page . '" ' . $add_couleur . '> '.$debut_sequence . '..' . $fin_sequence . '</a> - ';
					}
				}
				// Doit on rajouter le caractère pour signifier qu'il y a des valeurs "sautées" entre la première et le groupe de suivantes ?
				if(($page_aff + $ilot_de_liens) < $nombre_de_pages-1)
				{ $lien_de_pagination.= '--------' ; }

				$lien_de_pagination.= ' - ' ;

				// Afficher le dernier lien
				$add_couleur = ($page_aff == $nombre_de_pages) ? ' class="encours" ' : '';

				$lien_de_pagination.= '<a href="?page_aff=' . $nombre_de_pages . '" ' . $add_couleur . '> ' .((($nombre_de_pages - 1) * $items_par_page)+1) . '..' . $nombre_evenements_a_afficher . '</a> ';

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


/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
		 			Rêquete d'affichage des résultats
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */

	$requete_txt = isset($_SESSION['requete_txt']) ? $_SESSION['requete_txt'] : '';
	$premier_even = ($page_aff - 1) * $items_par_page;
	$dernier_even = $premier_even + $items_par_page;
	$ok_premier_even = false;

/*
echo '--- prem : ',$premier_even,' dern : ',$dernier_even,'---';
echo '<hr />'; print_r($_SESSION['t_id_event']);
//echo '<hr />'; print_r($_SESSION['t_trouvemot']);
echo '<hr />'; print_r($_SESSION['t_parent_event']);
//echo '<hr />'; print_r($_SESSION['t_lieu_event']);
echo '<hr />';
*/
// convertir la portion d'ID pour la pagination actuelle en instruction "id_event=..." afin de placer celà dans la requête
//$cpt_iouuu = 0 ;
//$tritouta = '' ;

for ($key_s = $premier_even; isset($_SESSION['t_id_event'][$key_s]) && $key_s < $dernier_even; $key_s++) {
//$tritouta.= '<br />'.$cpt_iouuu.') ID : '.$_SESSION['t_id_event'][$key_s].' - '.$_SESSION['t_trouvemot'][$key_s].' - '.$_SESSION['t_parent_event'][$key_s].' - '.$_SESSION['t_lieu_event'][$key_s];
//$cpt_iouuu++ ;

	//----- si le parent du 1er événement manque dans cette page de résultats paginés, on le réaffiche d'abord
	if ($ok_premier_even) {
		$key_s = $premier_even;
		$ok_premier_even = false;
	}
	else if ($key_s == $premier_even && $_SESSION['t_parent_event'][$key_s]) {
		for ($k = $key_s; $k >= 0 && $_SESSION['t_parent_event'][$k]; $k--)
			;
		if ($k >= 0)
			$key_s = $k;
		$ok_premier_even = true; //--- attention : astuce pour redémarrer la boucle après avoir rajouter le parent du premier événement
	}

//	$requete_concat = 'SELECT * FROM ag_event E LEFT JOIN ag_lieux L ON E.lieu_event = L.id_lieu LEFT JOIN ag_representation ON E.pres_event = ag_representation.id_pres WHERE id_event='.$_SESSION['t_id_event'][$key_s];
	$requete_concat = 'SELECT E.*,L.nom_lieu,L.email_reservation FROM ag_event E LEFT JOIN ag_lieux L ON E.lieu_event = L.id_lieu WHERE id_event='.$_SESSION['t_id_event'][$key_s];
	$reponse_synchone = mysql_query($requete_concat) or die (mysql_error());
	$donnees_1 = mysql_fetch_array($reponse_synchone);
	if (! $donnees_1)
		break;
	
	$tab = '<div class="breve'.($donnees_1['parent_event'] ? ' brenfant' : '').'">'."\n";
//$tab.= $key_s.' / '.$premier_even.' : id_event : '.$_SESSION['t_id_event'][$key_s].' - trouvemot : '.$_SESSION['t_trouvemot'][$key_s].' - parent_event : '.$_SESSION['t_parent_event'][$key_s].' - lieu_event : '.$_SESSION['t_lieu_event'][$key_s].'<br />';

	$id_event = (int) $donnees_1['id_event'];

	// ____________________________________________
	// ICONES FLOTTANTES (au niveau du titre)

	$tab.= '<span class="ico_float_droite_relative">'."\n";

	// Icone suivre - Modifier par Didier
	if (!empty($_SESSION['id_spectateur'])) {
		if (!statut_panier($_SESSION['id_spectateur'], $id_event)) $tab.= '<a href="?id_event='.$id_event.'&suivre=1" title="suivre" style="float:right;">Suivre ('.nombre_suivi($id_event).')</a> &nbsp; '."\n";
		else $tab.= '<a href="?id_event='.$id_event.'&plus_suivre=1" title="Ne plus suivre" style="float:right;">Ne plus suivre ('.nombre_suivi($id_event).')</a> &nbsp; '."\n";
	}
	// Icone concours
	$reponse_2 = mysql_query("SELECT id_conc FROM ag_conc_fiches WHERE event_dlp_conc=$id_event AND flags_conc='actif' ORDER BY id_conc DESC LIMIT 1");
	if ($total_entrees = mysql_fetch_array($reponse_2))
		$tab.= '<a href="'.generer_url_entite(95, 'rubrique', 'id='.$total_entrees['id_conc']).'" style="float:right;" title="Cliquez ici pour voir le concours">Concours</a> &nbsp; '."\n";

	// Vos Avis : compter le nbre d'entrées :
	$t_saison_preced = saisonprecedente($id_event, 'avis');
	$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM '.$table_avis_agenda.' WHERE event_avis IN ('.$t_saison_preced.') AND publier_avis=\'set\'');
	$total_entrees = mysql_fetch_array($count_avis);
	$total_entrees = $total_entrees['total_entrees'];
	if ($total_entrees > 0)
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#avis" title="Nombre d\'avis postés par les visiteurs"><img src="agenda/design_pics/ico_avis_mini.jpg"/><div class="nombre_avis_breve">'.$total_entrees.'</div></a>'."\n";
	
	
	// Icone Interview
	if ($donnees_1['interview_event'] != 0)
		$interview_event = $donnees_1['interview_event'];
	else
		$interview_event = saisonprecedente($id_event, 'interview');
	if ($interview_event)
		$tab.= '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" title="Cliquez ici pour lire l\'interview"><img src="agenda/design_pics/ico_interview_mini.jpg"/></a>'."\n" ;


	// Icone Critique
	if ($donnees_1['critique_event'] != 0)
		$critique_event = $donnees_1['critique_event'];
	else
		$critique_event = saisonprecedente($id_event, 'critique');
	if ($critique_event)
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#critique" title="Cliquez ici pour lire la critique"><img src="agenda/design_pics/ico_critique_mini.jpg"/></a>'."\n" ;


	// Icone chronique
	if ($donnees_1['chronique_event'] != 0)
		$chronique_event = $donnees_1['chronique_event'];
	else
		$chronique_event = saisonprecedente($id_event, 'chronique');
	if ($chronique_event)
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#chronique" title="Cliquez ici pour lire la chronique"><img src="agenda/design_pics/ico_chronique_mini.jpg"/></a>'."\n" ;


	// Icone "J'ai vu et aimé"
	$t_saison_preced = saisonprecedente($id_event, 'jai_vu');
	$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM ag_jai_vu WHERE id_event_jai_vu IN ('.$t_saison_preced.')');
	$total_entrees = mysql_fetch_array($count_avis);
	$total_entrees = $total_entrees['total_entrees'];
	$tab.= '<div class="nombre_votes"><a href="#vote" onclick="popup_jai_vu(\'agenda/jai_vu/jai_vu_popup.php?id='.$id_event.'\',\'Votons\'); return false;">'
	.'<img src="agenda/design_pics/ico_jai_vu.jpg" title="cliquez pour voter pour cet événement" alt="cliquez pour voter pour cet événement" /></a>'
	.'<div class="nombre_votes_bulle">'.($total_entrees ? $total_entrees : ' ').'</div></div>'."\n" ;

	$tab.= '</span>'."\n"; //--- fin ICONES FLOTTANTES


	// ____________________________________________
	// VIGNETTE EVENEMENT	
	if ($donnees_1['pic_event_1'] == 'set' )
	{
		$nom_event = htmlspecialchars($donnees_1['nom_event']);
		$id_event = $donnees_1['id_event'];
		$tab.= '<span class="breve_pic"><a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'"><img src="agenda/' . $folder_pics_event . 'vi_event_' . $id_event . '_1.jpg" title="' . $nom_event . '" /></a></span>'."\n";
	}
	
	
	// ____________________________________________
	// NOM EVENEMENT
	$nom_event = monraccourcirchaine($donnees_1['nom_event'], 45);
	if ($requete_txt != '' AND $requete_txt != 'nom de l\'événement' AND stristr ($nom_event, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse
	{
		$pattern = "!$requete_txt!i" ;
		$souligne = '<span class="souligne">' . $requete_txt .'</span>';
		$nom_souligne = preg_replace($pattern, $souligne, $nom_event);
		
		$tab.= '<div class="breve_titre"><a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'" title="Voir en détail">' . $nom_souligne . '</a></div>'."\n";
	}
	else
	{
		$tab.= '<div class="breve_titre"><a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'" title="Voir en détail">' . $nom_event . '</a></div>'."\n";
	}


	// ____________________________________________
	// ID
	$tab.= ' <span class="id_breve">(id ' . $donnees_1['id_event'] . ')</span><br />'."\n" ;

	// ____________________________________________
	// LIEU
	if (! $donnees_1['parent_event'])
		$tab.= '<span class="breve_lieu"><a href="'.generer_url_entite(96, 'rubrique', 'id_lieu='.$donnees_1['lieu_event']).'" title="Producteur du spectacle">'.$donnees_1['nom_lieu'].'</a></span>'."\n";

	// ____________________________________________
	// GENRE
	
	if ($donnees_1['genre_event'] != NULL) 
	{
		$genre_name = $donnees_1['genre_event'];
		$tab.= '<span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . '</acronym></span>'."\n";	
	}


	// ____________________________________________
	// DATES
	
	$date_event_debut = $donnees_1['date_event_debut'];	
	$date_event_debut_annee = substr($date_event_debut, 0, 4);
	$date_event_debut_mois = substr($date_event_debut, 5, 2);
	$date_event_debut_jour = substr($date_event_debut, 8, 2);
	
	$date_event_fin = $donnees_1['date_event_fin'];
	$date_event_fin_annee = substr($date_event_fin, 0, 4);
	$date_event_fin_mois = substr($date_event_fin, 5, 2);
	$date_event_fin_jour = substr($date_event_fin, 8, 2);

	// note : pour mois en LETTRES : $NomDuMois[$date_event_debut_mois+0]
	$tab.= ' <span class="breve_date"><acronym title="Période de représentation">' . $date_event_debut_jour . '/'
	. $date_event_debut_mois . '/'
	. $date_event_debut_annee . ' &gt;&gt; ' . $date_event_fin_jour . '/'
	. $date_event_fin_mois . '/'
	. $date_event_fin_annee . '</acronym></span>'."\n";	


	// ____________________________________________
	// VILLE
	
	if ($donnees_1['ville_event'] != NULL && ! $donnees_1['parent_event']) 
	{
		$ville_event_de_db = $donnees_1['ville_event'];
		$tab.= '<span class="breve_date"><acronym title="Ville où du spectacle">' . $regions[$ville_event_de_db] .'</acronym></span>'."\n";	
	}
	$tab.= '<br />';

	// ____________________________________________
	// TEXTE RESUME 
	
	// Afficher texte résumé et événtuellement souligner le mot rechercé par l'utilisateur
	$txt_decod = $donnees_1['resume_event'];
	if ($requete_txt != '' AND $requete_txt != 'nom de l\'événement' AND stristr ($txt_decod, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse) = test d'existence
	{
		$txt_resume = stripslashes($donnees_1['resume_event']) ;

		$pattern = "!$requete_txt!i" ;
		$souligne = '<span class="souligne">' . $requete_txt .'</span>';
		$tab.= '<br />'.preg_replace($pattern, $souligne, $txt_resume)."\n";
	}
	else if (! $donnees_1['parent_event'])
	{
		// Si pas de recherche contextuelle, simplement afficher résumé

			// Remplacer les retours de ligne
			$resum_txt = $donnees_1['resume_event'];
			$array_retour_ligne = array("<br>", "<br />", "<BR>", "<BR />");
			$uuuuueeeeeeee = str_replace($array_retour_ligne, " ", $resum_txt);
			$tab.= '<br />'.$uuuuueeeeeeee ;
	}


	// **************************************************************************************************
	//Si l'expression recherchée par le visiteur se trouve dans le TEXTE DE DESCRIPTION, afficher la portion concernée	
	// **************************************************************************************************

	$txt_decod = $donnees_1['description_event'];
	if ($requete_txt != '' AND $requete_txt != 'nom de l\'événement' AND stristr ($txt_decod, $requete_txt)) // stristr Trouve la première occurrence dans une chaîne (insensible à la casse) = test d'existence
	{
		$txt_description = strip_tags(stripslashes($donnees_1['description_event'])) ;
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
		$souligne = '<span class="souligne">' . $requete_txt .'</span>';
		$texte_souligne = preg_replace($pattern, $souligne, $chaine_reduite);
		
		$tab.= '<br />'.$texte_souligne."\n" ;	
	}

	
	$tab.= '<div class="en_savoir_plus">'."\n" ;
/*
	// Afficher bouton de Réservation
	if (!empty($donnees_1['email_reservation']) AND $donnees_1['email_reservation'] != NULL 
	AND ($donnees_1['genre_event'] != 'g07'))
	{
		$tab.= '<a href="-Reserver-?id_event='. $id_event .'" title="Réservez vos places en ligne !!" ><img src="agenda/design_pics/bouton_reserver.jpg"  hspace="10"  /></a>'."\n" ;
	}
	// Lien e-card
	$tab.= '<a href="-Envoyer-a-un-ami-?id_event=' . $id_event . '"><img src="agenda/e_card/pics/ico_envoyer_ami.jpg" title="Informer un ami" alt="Informer un ami" /></a>'."\n" ;
*/
	// Lien "en savoir plus"
	$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'"><img src="agenda/design_pics/ensavoirplus.jpg" title="En savoir plus" alt="En savoir plus" /></a>'."\n";
	
	$tab.= '</div>'."\n".'<div class="float_stop"></div>'."\n".'</div>'."\n\n";
	echo $tab ;
}
	
// Remettre les liens de pagnation si nécessaire
if ($re_afficher_en_dessous)
	echo $lien_de_pagination ;


// Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-Lieu-
// Y a-t-il un producteur / lieu dont le contenu contiendrait la chaine recherchée ?

if (isset($chp_txt_libre) AND $chp_txt_libre != '')
{	
	$chaine_txt_libre = addslashes(htmlspecialchars($_POST['chp_txt_libre'])) ; 
	$chaine_txt_libre_db = $chaine_txt_libre ;
	

		
	$req_nombre_resultats_texte_dans_lieu = "SELECT COUNT(*) AS total_entrees FROM ag_lieux 
	WHERE (
	(cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH))
	AND (
	(nom_lieu LIKE '%$chaine_txt_libre%') 
	OR (directeur_lieu LIKE '%$chaine_txt_libre%') 
	OR (contact_lieu LIKE '%$chaine_txt_libre%') 
	OR (adresse_lieu LIKE '%$chaine_txt_libre%') 
	))" ;

	$reponse_nombre_dans_lieu = mysql_query($req_nombre_resultats_texte_dans_lieu) or die(" Erreur requête NOMBRE LIEU " . mysql_error());

	$donnees_nombre_dans_lieu = mysql_fetch_array($reponse_nombre_dans_lieu);
	$total_entrees = $donnees_nombre_dans_lieu['total_entrees'];
	if ($total_entrees > 0)
	{
		$req_resultats_texte_dans_lieu = "SELECT id_lieu, nom_lieu FROM ag_lieux 
		WHERE (
		(cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH))
		AND (
		(nom_lieu LIKE '%$chaine_txt_libre%') 
		OR (directeur_lieu LIKE '%$chaine_txt_libre%') 
		OR (contact_lieu LIKE '%$chaine_txt_libre%') 
		OR (adresse_lieu LIKE '%$chaine_txt_libre%') 
		))" ;

		$reponse_dans_lieu = mysql_query($req_resultats_texte_dans_lieu) or die(" Erreur requête RESULTAT LIEU " . mysql_error());
	
		echo '
		<div class="style_livre_bloc">
		 <div class="style_livre_titre_bloc">
			L\'expression &quot;' . stripslashes($chaine_txt_libre) . '&quot; correspond à '. $total_entrees . ' résultat(s) parmi les lieux culturels abonnés :
		 </div>
		' ;
		
		while ($donnees_dans_lieu = mysql_fetch_array($reponse_dans_lieu))
		{
			echo '<a href="',generer_url_entite(96, 'rubrique', 'id_lieu='.$donnees_dans_lieu['id_lieu']),'">
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
//echo '<div>debug : ' . $voir_debug . '<br /> <br /></div>' ;

// Débug : Comparer les valeurs Array originales et après tri + pagination
/*echo'<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td>'.$pirlilou.'</td>
    <td valign="top">'.$tritouta.'</td>
  </tr>
</table>';*/

?>
