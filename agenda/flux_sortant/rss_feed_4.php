<?php

// sur base de http://ghostdogpr.developpez.com/articles/rss/

$items_par_page = 40 ; // Nombre d'événements affichés par page.
$ancien_max = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")-2 , date("d"), date("Y"))) ; // Sert à éliminer les events commencés depuis trop longtemps (pour les requêtes à date non précisée).

/*error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);*/

$voir_debug = '' ; // Initialisation de la variable de cond-caténation des messages de débogage
require '../inc_db_connect.php';
require '../inc_var.php';

$allowedTags = '<br><br />'; // Balises de style que les USERS peuvent employer

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function raccourcir_chaine ($chaine_a_raccourcir,$max)
{
	if(strlen($chaine_a_raccourcir)>=$max)
	{
		$chaine_a_raccourcir=substr($chaine_a_raccourcir,0,$max);
		$espace=strrpos($chaine_a_raccourcir," ");
		if($espace)
		{ 
			$chaine_a_raccourcir=substr($chaine_a_raccourcir,0,$espace);
		}
		$chaine_a_raccourcir .= '...';
	}
	$chaine_raccourcie = $chaine_a_raccourcir ;
	return $chaine_raccourcie ;
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function parser_titre ($chaine_a_parser)
{
	$chaine_a_parser = strip_tags($chaine_a_parser) ;
	$chaine_a_parser = str_replace(" & ", " + ", $chaine_a_parser);
	$chaine_a_parser = str_replace("&#039;", "'", $chaine_a_parser);
	$chaine_a_parser = str_replace("&rsquo;", "'", $chaine_a_parser);
	$chaine_a_parser = str_replace("&hellip;", "...", $chaine_a_parser);
	$chaine_a_parser = str_replace("&euro;", "Euro", $chaine_a_parser);
	$chaine_a_parser = str_replace("&amp;", " + ", $chaine_a_parser);
	$chaine_a_parser = str_replace("&ndash;", "-", $chaine_a_parser);
	$chaine_a_parser = str_replace("œ", "oe", $chaine_a_parser);
	$chaine_a_parser = str_replace("&oelig;", "oe", $chaine_a_parser);
	$chaine_a_parser = str_replace("&OElig;", "Oe", $chaine_a_parser);
	$chaine_a_parser = str_replace("&ldquo;", "“", $chaine_a_parser);
	$chaine_a_parser = str_replace("&rdquo;", "”", $chaine_a_parser);
	$chaine_a_parser = str_replace("&lsquo;", "‘", $chaine_a_parser);
	$chaine_a_parser = str_replace("&rsquo;", "‘", $chaine_a_parser);
	$chaine_a_parser = str_replace("&Icirc;", "I", $chaine_a_parser);
	$chaine_a_parser = str_replace("&icirc;", "i", $chaine_a_parser);
	$chaine_a_parser = str_replace("&laquo;", "\"", $chaine_a_parser);
	$chaine_a_parser = str_replace("&raquo;", "\"", $chaine_a_parser);
	$chaine_a_parser = str_replace("&quot;", "\"", $chaine_a_parser);
	$chaine_a_parser = str_replace("…", "...", $chaine_a_parser);
	
	return $chaine_a_parser;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function parser_description ($chaine_a_parser)
{
	$chaine_a_parser = str_replace("…", "...", $chaine_a_parser);
	$chaine_a_parser = str_replace("’", "'", $chaine_a_parser);
	$chaine_a_parser = str_replace("&nbsp;", " ", $chaine_a_parser);
	$chaine_a_parser = str_replace("œ", "oe", $chaine_a_parser);

	return $chaine_a_parser;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

// ---------------------------------------------------------------------------------------------------------
$channel_description = '' ;

if (1==1)
{	
	// genre_event
	if (isset($_GET['genre']) AND $_GET['genre'] != NULL)
	{ 
		$genre_event = htmlentities($_GET['genre'], ENT_QUOTES) ;
		$voir_debug.= '<br />La Variable _GET "genre_event" est précisée et vaut "' . $genre_event . '" ';
		
		
		$genre_event_array = explode("_", $genre_event); // On peut demander plusieurs genres
		/*echo '<pre>';
		print_r($genre_event_array);
		echo '</pre>';*/		
		$nbre_genres_demande = count($genre_event_array) ;
		//echo $nbre_genres_demande;
		if ($nbre_genres_demande==1)
		{
			// Possibilité 1) : Un seule genre demandé
			$channel_description = $genres[$genre_event] . ' - ' ;
			$voir_debug.= '<br />Possibilité 1) : Un seule genre demandé';
			$requete_genre = " AND genre_event = '$genre_event' " ;
			$requete_top5_genre = " AND genre_event = '$genre_event' " ;
		}
		else
		{
			// Possibilité 2) : plusieurs genres demandés 
			$voir_debug.= '<br />Possibilité 2) : plusieurs genres demandés (' . $nbre_genres_demande . ')';
			$channel_description = 'Catégories/Genres multiples' ;
			$requete_genre = " AND (" ;
			$requete_top5_genre = " AND (" ;
			$cpt_nb_genre = 0 ;
			foreach($genre_event_array as $cle_genres_array)
			{
				//echo $cle_genres_array . ' ' ;
				$requete_genre.= "genre_event = '$cle_genres_array' " ;
				$requete_top5_genre.= "genre_event = '$cle_genres_array' " ;
				$cpt_nb_genre ++ ;
				if ($cpt_nb_genre < $nbre_genres_demande)
				{
					$requete_genre.= 'OR ' ;
					$requete_top5_genre.= 'OR ' ;
				}
				else
				{
					$requete_genre.= ') ' ;
					$requete_top5_genre.= ') ' ;
				}
			}
		}
	}
	else
	{
		$genre_event = '' ;
	}

	// lieu
	if (isset($_GET['lieu']) AND $_GET['lieu'] != NULL)
	{ 
		$lieu_event = htmlentities($_GET['lieu'], ENT_QUOTES) ;
		$voir_debug.= '<br />La Variable _GET "lieu" est précisée et vaut "' . $lieu_event . '" ';
	}
	else
	{
		$lieu_event = '' ;
	}
	
	// ville
	if (isset($_GET['ville']) AND $_GET['ville'] != NULL)
	{ 
		$ville_event = htmlentities($_GET['ville'], ENT_QUOTES) ;
		$voir_debug.= '<br />La Variable _GET "ville" est précisée et vaut "' . $ville_event . '" ';
		$channel_description = $regions[$ville_event] . ' - ' ;
	}
	else
	{
		$ville_event = '' ;
	}
		
	// date_debut
	if (isset($_GET['date_debut']) AND $_GET['date_debut'] != NULL)
	{ 
		$date_debut = htmlentities($_GET['date_debut'], ENT_QUOTES) ;
		$voir_debug.= '<br />La Variable _GET "date_debut" est précisée et vaut "' . $date_debut . '" ';
	}
	else
	{
		$date_debut = '' ;
	}
		
	// date_fin
	if (isset($_GET['date_fin']) AND $_GET['date_fin'] != NULL)
	{ 
		$date_fin = htmlentities($_GET['date_fin'], ENT_QUOTES) ;
		$voir_debug.= '<br />La Variable _GET "date_fin" est précisée et vaut "' . $date_fin . '" ';
	}
	else
	{
		$date_fin = '' ;
	}
}



/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* 										   Requête      												  */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/* RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR */
/*

// date_debut & date_fin
// +++++++++++++++++++++

Note sur les dates : 
1] On souhaite les événements dont la PERIODE de représentation est comprise entre
les dates limites de début et de fin. Donc utiliser la requête "NOT ()()()()"
2] C'est ici que s'il n'y a pas d'indication de date de début, 
on impose comme date de début la date du jour. Celle-ci est fournie alors au FORMULAIRE
*/

// Date de début : si rien n'et précisé, date début = aujourd'hui
if ($date_debut == '')
{
	$date_debut_to_requete = date ('Y-m-d'); // Aujourd'hui
	$date_debut = date ('d-m-Y'); // Aujourd'hui
	$date_debut_annee = substr($date_debut, 6, 4); // Si nécessaire pour mini calendrier
	$date_debut_mois = substr($date_debut, 3, 2); // Si nécessaire pour mini calendrier
	$date_debut_jour = substr($date_debut, 0, 2); // Si nécessaire pour mini calendrier
}
else
{
	$date_debut_annee = substr($date_debut, 6, 4);
	$date_debut_mois = substr($date_debut, 3, 2);	
	$date_debut_jour = substr($date_debut, 0, 2);
	$date_debut_to_requete = $date_debut_annee.'-'.$date_debut_mois.'-'.$date_debut_jour ;
	
	$ancien_max = '2007-01-01';
}
// Date de fin : si rien n'et précisé, date fin = aujourd'hui +6 mois
if ($date_fin == '')
{
	$date_fin_to_requete = date ('Y-m-d', $date_fin = mktime(0, 0, 0, date("m")+6 , date("d"), date("Y")));
	$date_fin = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m")+6 , date("d"), date("Y"))); 
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
NOT ((date_event_debut < '$date_debut_to_requete') 
AND (date_event_fin < '$date_debut_to_requete') 
OR (date_event_debut > '$date_fin_to_requete') 
AND (date_event_fin > '$date_fin_to_requete')) 
AND (date_event_debut > '$ancien_max')";

$requete_top5_concat = " WHERE 
NOT ((date_event_debut < '$date_debut_to_requete') 
AND (date_event_fin < '$date_debut_to_requete') 
OR (date_event_debut > '$date_fin_to_requete') 
AND (date_event_fin > '$date_fin_to_requete')) ";


// genre_event
// ++++++++++++
if (isset($genre_event) AND $genre_event != '')
{
	$requete_concat.= $requete_genre ;
	$requete_top5_concat.= $requete_top5_genre ;
}

// lieu_event
// ++++++++++++
if (isset($lieu_event) AND $lieu_event != '')
{
	$requete_concat.= " AND lieu_event = '$lieu_event' " ;
	$requete_top5_concat.= " AND lieu_event = '$lieu_event' " ;
}

// ville_event
// ++++++++++++
if (isset($ville_event) AND $ville_event != '')
{
	$requete_concat.= " AND ville_event = '$ville_event' " ;
	$requete_top5_concat.= " AND ville_event = '$ville_event' " ;
}


// Fin de concaténation de la requête
// ++++++++++++++++++++++++++++++++++

if (isset($_GET['top']) AND $_GET['top'] != NULL)
{
	// Requete pour le Top 5
	$nombre_items_pour_top = htmlentities($_GET['top'], ENT_QUOTES) ;

	$requete_concat = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L 
	ON (cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND lieu_event = id_lieu 
	AND jai_vu_event > 0
	$requete_top5_concat ORDER BY jai_vu_event DESC LIMIT $nombre_items_pour_top " ;
}
else
{
	// Requete conventionnelle
	$requete_concat = "SELECT * FROM $table_evenements_agenda INNER JOIN  $table_lieu L 
	ON (cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH)) AND lieu_event = id_lieu 
	
	$requete_concat ORDER BY date_event_debut LIMIT $items_par_page " ;
}





// édition du début du fichier XML

header('Content-Type: application/rss+xml');  
$xml = "<"."?xml version=\"1.0\" encoding=\"ISO-8859-1\"?".">
<rss version=\"2.0\" xmlns:dc=\"http://www.demandezleprogramme.be/agenda/flux_sortant/\">"; 

$xml .= '<channel>
<title>DemandezLeProgramme</title>
<link>http://www.demandezleprogramme.be</link>
<description>Votre agenda culturel critique et interractif</description>
<language>FR</language>
<webMaster>info@demandezleprogramme.be  (Renaud)</webMaster>
<generator>Vertige-pgm</generator>
<copyright>Vertige asbl</copyright>
<image>
	<title>DemandezLeProgramme</title>
	<url>http://www.demandezleprogramme.be/squelettes/assets/logo_header_turquoise.jpg</url>
	<link>http://www.demandezleprogramme.be</link>
</image>';
$today= date("D, d M Y H:i:s +0100");
$xml .= "
<pubDate>$today</pubDate>
";

$nr_item = 1 ;
$reponse_dlp = mysql_query($requete_concat) or die (mysql_error());
while ($donnees_dlp = mysql_fetch_array($reponse_dlp))
{
   
   $xml.= '<item>
';
	
	// ********** TITRE **********	
	$titre_dlp_channel_xml = $donnees_dlp['nom_event'] ;
	$titre_dlp_channel_xml = parser_titre($titre_dlp_channel_xml) ;
	$titre_dlp_channel_xml = html_entity_decode($titre_dlp_channel_xml, ENT_QUOTES, 'iso-8859-1') ;
	//$titre_dlp_channel_xml = strip_tags($titre_dlp_channel_xml) ;
	//$titre_dlp_channel_xml = html_entity_decode($genre_to_xml, ENT_QUOTES, 'iso-8859-1') ;
	/*$xml .= '<title>(item ' . $nr_item . ') ' . $titre_dlp_channel_xml . ' (' . $donnees_dlp['id_event'] . ')</title>
';*/
		$xml .= '<title>' . $titre_dlp_channel_xml . '</title>
';

	
	// ********** LIEN **********	
	$xml .= '<link>http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . $donnees_dlp['id_event'] . '</link>
';

	
	
	// ********** VOTES **********	
	$xml .= '<dc:votes>' . $donnees_dlp['jai_vu_event'] . '</dc:votes>
';


	

	// ********** DESCRIPTION **********	
	$AAAA_debut = substr($donnees_dlp['date_event_debut'], 0, 4);
	$MM_debut = substr($donnees_dlp['date_event_debut'], 5, 2);	
	$JJ_debut = substr($donnees_dlp['date_event_debut'], 8, 2);
	//$date_rss = date ("D, d M Y H:i:s", $date_event_debut = mktime(23, 0, 0, $MM_debut , $JJ_debut, $AAAA_debut)); 
	$date_rss = date ("D, d M Y H:i:s"); 
	$xml.= '<pubDate>'.$date_rss.' GMT</pubDate>
' ;

	$AAAA_fin = substr($donnees_dlp['date_event_fin'], 0, 4);
	$MM_fin = substr($donnees_dlp['date_event_fin'], 5, 2);	
	$JJ_fin = substr($donnees_dlp['date_event_fin'], 8, 2);

	// On veut afficher tout le descriptif ?
	if (isset($_GET['desc_type']) AND $_GET['desc_type']=="comp")
	{ 
		$descr_format_xml = $donnees_dlp['description_event'] ;
		$descr_format_xml = strip_tags($descr_format_xml, $allowedTags) ;
		$descr_format_xml = ($descr_format_xml) ;
		$descr_format_xml = htmlspecialchars($descr_format_xml, ENT_QUOTES, 'iso-8859-1') ;
	}
	else 
	{ 
		$descr_format_xml = $donnees_dlp['resume_event']; 
		$descr_format_xml = strip_tags($descr_format_xml) ;
		$descr_format_xml = ($descr_format_xml) ;
		$descr_format_xml = htmlspecialchars($descr_format_xml, ENT_QUOTES, 'iso-8859-1') ;

	}
	


//	$descr_format_xml = html_entity_decode($descr_format_xml) ;
//	$descr_format_xml = htmlentities($descr_format_xml, ENT_QUOTES, 'iso-8859-1') ;
	////$descr_format_xml = utf8_encode($descr_format_xml) ;
	
	$description_event = '<![CDATA[' ;
	
	// image dans description
	$image_event = '../pics_events/event_' . $donnees_dlp['id_event'] . '_1.jpg';
	if (file_exists($image_event))
	{
		$description_event .= '<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . $donnees_dlp['id_event'] . '"><img src="http://www.demandezleprogramme.be/agenda/pics_events/event_' . $donnees_dlp['id_event'] . '_1.jpg" alt="DemandezLeProgramme" width="100" /></a><br />';
	}
	
	// date de représentation pour la description
	$description_event .= 'Du ' . $JJ_debut . '-' . $MM_debut . '-' . $AAAA_debut . ' au ' . $JJ_fin . '-' . $MM_fin . '-' . $AAAA_fin . '<br />' ; 

	
	$description_event .= ']]>' ;
	
	//--------------------------------------------------------------------
	// Critiques, interview...
	//--------------------------------------------------------------------
	$critiq_interv = '' ;
	// critique_event
	if (isset($donnees_dlp['critique_event']) AND $donnees_dlp['critique_event'] != 0)
	{
		$critique_event = $donnees_dlp['critique_event'];
		$critiq_interv.= '<br /> - <a href="http://www.demandezleprogramme.be/-Critiques-?id_article='. $critique_event . '">Lire la critique</a>';
	}
	
	// interview_event
	if (isset($donnees_dlp['interview_event']) AND $donnees_dlp['interview_event'] != 0)
	{
		$interview_event = $donnees_dlp['interview_event'];
		$critiq_interv.= '<br /> - <a href="http://www.demandezleprogramme.be/spip.php?page=interview&qid=' . $interview_event . '">Lire l\'interview</a>';
	}
	
	// interview_espace_livres
	if (isset($donnees_dlp['espace_livres']) AND $donnees_dlp['espace_livres'] != 0)
	{
		$espace_livres = $donnees_dlp['espace_livres'];
		$critiq_interv.= '<br /> - <a href="http://www.demandezleprogramme.be/spip.php?article' . $espace_livres . '">Ecouter l\'interview Espace Livres</a>';
	}
		
	

	$critiq_interv = '<![CDATA[' . $critiq_interv . ']]>' ;
	//--------------------------------------------------------------------
	// #Critiques, interview...
	//--------------------------------------------------------------------


	$xml .= '<description>' . $description_event . $descr_format_xml . ' ' . $critiq_interv . '</description>
';
	

	if (isset($donnees_dlp['genre_event']) AND $donnees_dlp['genre_event'] != NULL AND $donnees_dlp['genre_event'] != '')
	{
		$genre_to_xml = $donnees_dlp['genre_event'] ;
		$genre_to_xml = $genres[$genre_to_xml] ;
	}
	else
	{
		$genre_to_xml = 'non precise' ;
	}

	$genre_to_xml = html_entity_decode($genre_to_xml, ENT_QUOTES, 'iso-8859-1') ;

	$xml .= '<category>' . $genre_to_xml . '</category>
';
	$xml .= '<guid>http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . $donnees_dlp['id_event'] . '</guid>
';
	
	/*$xml .= '<datedebut>' . $donnees_dlp['date_event_debut'] . '</datedebut>
<datefin>' . $donnees_dlp['date_event_fin'] . '</datefin>
<idlieu>' . $donnees_dlp['genre_event'] . '</idlieu>
';*/



   $xml .= '</item>
';	
	$nr_item ++ ;
}	




$xml .= '</channel>
</rss>';


$fp = fopen("flux.xml", 'w+');
fputs($fp, $xml);
fclose($fp);

echo $xml ;

?>