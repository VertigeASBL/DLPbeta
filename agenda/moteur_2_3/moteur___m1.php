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

//$nb_jours_ahead = 15 ; // Pour cas 4-a)  N'est plus actif ! 

// PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP

/*error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
*/
$voir_debug = '';

require 'agenda/inc_db_connect.php';
require 'agenda/inc_var.php';
require 'agenda/inc_fct_base.php';
require 'agenda/calendrier/inc_calendrier.php';

/* 4-a) et 4-b)
Récupérer les variables transmises dans l'URL, les enregistrer en variables SESSION (car utile pour la pagination)
*/

if ((isset($_GET['req']) AND $_GET['req'] == 'ext')OR (isset($_GET['req']) AND $_GET['req'] == 'mini_calendr')) {
	$voir_debug.= '<br />*************Cas 4-a) et 4-b)*************';
	$voir_debug.= '<br />Requete extérieur';

	$_SESSION['recherche'] = 'non' ; // Les paramètres SESSIONS ne sont pas TOUS utiles dans ce cas. Uniquement celui de requête finale est utile pour la pagination
	unset ($_SESSION['page_aff']) ; // On les écrase pour ne pas biaiser la requête
	
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
		$date_debut = date ('d-m-Y', $date_debut = mktime(0, 0, 0, date("m"), date("d"), date("Y"))); 
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
		$date_fin = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m")+3 , date("d"), date("Y"))); // Ceci est valable quand on vient de la page http://www.demandezleprogramme.be/-Les-lieux-partenaires- (=liste des LIEUX)
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


//--- faire remonter les éléments [DBU..FIN[ à la position OFS
function remonter_tab(&$tab, $dbu, $fin) {
	static $ofs = 0;

	echo '<br />/ dbu : ',$dbu,' / fin : ',$fin,' / ofs : ',$ofs,' / ';
	if ($ofs < $dbu) {
		for ($i = $ofs, $j = $dbu, $j--; $i < $j; $i++, $j--)
			{ $mem = $tab[$i]; $tab[$i] = $tab[$j]; $tab[$j] = $mem; echo '<br />echanger ',$i,' --- ',$j; }
		for ($i = $dbu, $j = $fin, $j--; $i < $j; $i++, $j--)
			{ $mem = $tab[$i]; $tab[$i] = $tab[$j]; $tab[$j] = $mem; echo '<br />echanger ',$i,' --- ',$j; }
		for ($i = $ofs, $j = $fin, $j--; $i < $j; $i++, $j--)
			{ $mem = $tab[$i]; $tab[$i] = $tab[$j]; $tab[$j] = $mem; echo '<br />echanger ',$i,' --- ',$j; }
	}
	$ofs += $fin - $dbu;
}

/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
 										   Requête Synchone
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */

// Si c'est une demande de changement de pagination, inutile de refaire toute la requete SQL 
if (isset($_GET['page_aff']) AND $_GET['page_aff'] != NULL) {
	$voir_debug.= '<br />Il s\'agit d\'un changement de pagination, donc PAS DE REQETE SQL';
}
else {

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
	$requete_where = " WHERE (L.cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND
	NOT ((E.date_event_debut < '$date_debut_to_requete') AND (E.date_event_fin < '$date_debut_to_requete') 
	OR (E.date_event_debut > '$date_fin_to_requete') AND (E.date_event_fin > '$date_fin_to_requete')) ";
	
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
		$requete_where.= " AND E.genre_event = '$genre_event' " ;
	}
	
	// lieu_event
	// ++++++++++++
	if (isset($lieu_event) AND $lieu_event != '')
	{
		$requete_where.= " AND E.lieu_event = '$lieu_event' " ;
	}
	
	// ville_event
	// ++++++++++++
	if (isset($ville_event) AND $ville_event != '')
	{
		$requete_where.= " AND E.ville_event = '$ville_event' " ;
	}

	// chp_txt_libre
	// ++++++++++++
	if (isset($chp_txt_libre) AND $chp_txt_libre != '')
	{
		$requete_txt = $chp_txt_libre ; // pour mise en évidence du résultat (surlignage)
		$chp_txt_libre_echap = addslashes($requete_txt);
		$requete_select = ",E.nom_event LIKE '%$chp_txt_libre_echap%' AS condmot";
		$requete_where .= " AND (E.nom_event LIKE '%$chp_txt_libre_echap%' OR E.description_event LIKE '%$chp_txt_libre_echap%' OR E.resume_event LIKE '%$chp_txt_libre_echap%') ";
	}
	else
		$requete_select = ',0 AS condmot';

	// Fin de concaténation de la requête
	// ++++++++++++++++++++++++++++++++++

/*
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	// 		Requête de recherche
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
	//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA

Trier parents et sous-évén dès les requêtes comme dans listing_events.php ? ou ajouter l'info parent dans tresult et regrouper les 
parents et leurs sous-évén après dans le tri, et veiller à ajouter des sous-évén si moins que 3 ont été trouvés ?

chercher "tridate"

d'abord les événements dont txt_libre est trouvé dans nom_event
classés par date de début
*/


/*
	$requete_concat = 'SELECT id_event,lieu_event'.$requete_select;
	$requete_concat .= ' FROM ag_event LEFT JOIN ag_lieux ON E.lieu_event=L.id_lieu';
	$requete_concat .= $requete_where;
	$requete_concat .= ' ORDER BY condmot DESC, date_event_debut';
*/
	$requete_concat = 'SELECT E.id_event'.$requete_select.',IF(E.parent_event=0,E.date_event_debut,F.date_event_debut) AS tridate,E.parent_event,E.date_event_debut,E.lieu_event';
	$requete_concat .= ' FROM ag_event F,ag_event E LEFT JOIN ag_lieux L ON E.lieu_event=L.id_lieu';
	$requete_concat .= $requete_where;
	$requete_concat .= 'AND (F.id_event=E.parent_event OR E.parent_event=0 AND F.id_event=E.id_event) ';
	$requete_concat .= ' ORDER BY tridate DESC,E.parent_event,E.date_event_debut';

echo '<hr />',$requete_concat,'<hr />',"\n";

	$reponse_synchone = mysql_query($requete_concat) or die ('err requ sync : ' . mysql_error());
	
	// Requête de comptage du nombre d'entrées - prov
	$nombre_evenements_a_afficher = mysql_num_rows($reponse_synchone);
	$_SESSION['nombre_evenements_a_afficher'] = $nombre_evenements_a_afficher;

	$voir_debug.= '<br />Nombre d\'événements à afficher : ' . $nombre_evenements_a_afficher ;
	$voir_debug.= '<br />La Variable _SESSION "nombre_evenements_a_afficher" reçoit la valeur "' . $nombre_evenements_a_afficher . '"' ;

//echo '<table cellspacing="0" cellpadding="8" border="1">',"\n";
//echo '<tr><td>N°</td><td>id_event</td><td>condmot</td><td>tridate</td><td>parent_event</td><td>date_event_debut</td><td>lieu_event</td></tr>',"\n";
$k = 0;
	$res_tab = array() ;
	while ($donnees_synchone = mysql_fetch_array($reponse_synchone))
	{
		array_push($res_tab, array($donnees_synchone['id_event'], $donnees_synchone['condmot'], $donnees_synchone['parent_event'], $donnees_synchone['lieu_event']));
//echo '<tr><td>',$k++,'</td><td>',$donnees_synchone['id_event'],'</td><td>',$donnees_synchone['condmot'],'</td><td>',$donnees_synchone['tridate'],'</td><td>',$donnees_synchone['parent_event'],'</td><td>',$donnees_synchone['date_event_debut'],'</td><td>',$donnees_synchone['lieu_event'],'</td></tr>',"\n";
	}
//echo '</table>',"\n";

$res_tab[5][1] = 1;
$res_tab[9][1] = 1;

echo '<table cellspacing="0" cellpadding="8" border="1">',"\n";
echo '<tr><td>N°</td><td>id_event</td><td>condmot</td><td>parent_event</td><td>lieu_event</td></tr>',"\n";
for ($k = 0; isset($res_tab[$k]); $k++)
	echo '<tr><td>',$k,'</td><td>',$res_tab[$k][0],'</td><td>',$res_tab[$k][1],'</td><td>',$res_tab[$k][2],'</td><td>',$res_tab[$k][3],'</td></tr>',"\n";
echo '</table>',"\n";


	//--- [0]:id_event - [1]:condmot - [2]:parent_event - [3]:lieu_event

	//--- faire remonter les événements (le groupe parent et enfants) dont nom_event contient le mot de la recherche libre
	$res_pos = -1;
	$res_len = count($res_tab);
	reset($res_tab);
	for ($k = 0; $k <= $res_len; $k++) {
		if ($res_pos == -1 && $k < $res_len && $res_tab[$k][1] == 1)
			$res_pos = $k;
		if ($res_pos != -1 && ($k == $res_len || $res_tab[$k][1] == 0)) {
			//--- monter pour trouver le parent
			for ($res_dbu = $res_pos; $res_dbu >= 0 && $res_tab[$res_dbu][2]; $res_dbu--)
				;
			//--- descendre pour trouver le dernier enfant
			for ($res_fin = $k; $res_fin < $res_len && $res_tab[$res_fin][2]; $res_fin++)
				;
			remonter_tab($res_tab, $res_dbu, $res_fin);
			$res_pos = -1;
		}
	}
//remonter_tab($res_tab, $res_pos, $k ? $k : count($res_tab));
//echo '<br />dbu:',$res_dbu,' fin:',$res_fin,' ***';


echo '<table cellspacing="0" cellpadding="8" border="1">',"\n";
echo '<tr><td>N°</td><td>id_event</td><td>condmot</td><td>parent_event</td><td>lieu_event</td></tr>',"\n";
for ($k = 0; isset($res_tab[$k]); $k++)
	echo '<tr><td>',$k,'</td><td>',$res_tab[$k][0],'</td><td>',$res_tab[$k][1],'</td><td>',$res_tab[$k][2],'</td><td>',$res_tab[$k][3],'</td></tr>',"\n";
echo '</table>',"\n";


	$_SESSION['array_id_ok'] = $res_tab;

	$voir_debug.= '<br /><br />La requête effectuée et mise en SESSION est : ' . $requete_concat;
	
} // Fin du "saut de la requête SQL" effectué lorsqu'un saut de pagination est effectué, ! $_GET['page_aff']

/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR


   #######################################################################################################
   #######################################################################################################
  												 Formulaire
   #######################################################################################################
   ####################################################################################################### */
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
	<option value="non_selct">tous les lieux / partenaires</option>';
	
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
      <input id="effacer_tous_champs" name="effacer_tous_champs" value="Effacer tout" class="effacer_tous_champs" type="button" alt="Cliquez pour lancer la recherche" onsubmit="return prog_submit()">
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

/* AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
   AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
   											Affichage du résultat
   AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
   AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA */

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




/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR
		 			Rêquete d'affichage des résultats
   RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */

// La pagination fournit la plage des ID à récupérer dans l'Array trié
if (isset($_GET['page_aff']) AND $_GET['page_aff'] != NULL) {
	$page_aff = (int) $_GET['page_aff'];
}
else {
	$page_aff = 1 ; /* Premier événement à afficher */ }

$premierMessageAafficher = ($page_aff - 1) * $items_par_page;

//echo '<hr /><pre>'; print_r($_SESSION['array_id_ok']); echo '</pre><hr />';

// convertir la portion d'ID pour la pagination actuelle en instruction "id_event=..." afin de placer celà dans la requête
$array_id_selection_limit = array_slice($_SESSION['array_id_ok'], $premierMessageAafficher, $items_par_page);

//--- 0:id_event - 1:condmot - 2:parent_event - 3:lieu_event

$id_event_concat = '' ; // Pour initialiser la suite
$cpt_iouuu = 0 ;
$tritouta = '' ;
while ($elemt_array_id_selection_limit = current($array_id_selection_limit)) {
	$tritouta.= '<br />'.$cpt_iouuu.') ID : '.$elemt_array_id_selection_limit[0].' - '.$elemt_array_id_selection_limit[1].' - '.$elemt_array_id_selection_limit[2].' - '.$elemt_array_id_selection_limit[3];
$tab.= '<br />'.$cpt_iouuu.') id_event : '.$elemt_array_id_selection_limit[0].' - condmot : '.$elemt_array_id_selection_limit[1].' - parent_event : '.$elemt_array_id_selection_limit[2].' - lieu_event : '.$elemt_array_id_selection_limit[3];

	$cpt_iouuu++ ;
	next($array_id_selection_limit);
	
	$requete_concat = "SELECT * FROM ag_event E
	LEFT JOIN ag_lieux L ON E.lieu_event = L.id_lieu
	LEFT JOIN ag_representation ON E.pres_event = ag_representation.id_pres
	WHERE id_event= $elemt_array_id_selection_limit[0]" ;	

	$reponse_synchone = mysql_query($requete_concat) or die (mysql_error());
	$donnees_synchone = mysql_fetch_array($reponse_synchone);
	
	/*$id_event_concat.= ' id_event='.$elemt_array_id_selection_limit[0] ;
	if (key($array_id_selection_limit) < ($items_par_page-1))
	{
		$id_event_concat.= ' OR' ;
	}*/

		
	$tab.= '<div class="breve">' ;	
if ($elemt_array_id_selection_limit[2]) $tab.= '<img src="agenda/design_pics/evenfant.gif" alt="" style="float:left; margin:0 20px 0 0;" height="110" />';

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


	$lien_jai_vu = '<a href="#" onclick="popup_jai_vu';
	$lien_jai_vu.= "('agenda/jai_vu/jai_vu_popup.php?id=" . $id_event . "','Votons');";
	$lien_jai_vu.= ' return(false)">';
	
	/*$adresse_jai_vu = 'agenda/jai_vu/jai_vu_popup.php?id=' . $id_event ;
	$lien_jai_vu = '<a href="#voter" onclick="popup_jai_vu' ;
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



// Afficher texte de débogage :
echo '<div>debug : ' . $voir_debug . '<br /> <br /></div>' ;

// Débug : Comparer les valeurs Array originales et après tri + pagination
/*echo'<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td>'.$pirlilou.'</td>
    <td valign="top">'.$tritouta.'</td>
  </tr>
</table>';*/

?>