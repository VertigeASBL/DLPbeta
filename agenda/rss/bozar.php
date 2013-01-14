<?php
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
// Protection rudimentaire de l'accès à la page
///agenda/rss/bozar.php?pw=pvlkb534qd

if (isset($_GET['pw']) AND ($_GET['pw'] != NULL))
{
	$pw = htmlentities($_GET['pw'], ENT_QUOTES);
	if ($pw != 'pvlkb534qd')
	{
		echo '<p align="center"><br /><br /><br /><br /><br />Accès impossible</p>' ;
		exit () ;
		$permission = 'ok' ;
	}
}
else
{
	echo '<p align="center"><br /><br /><br /><br /><br />Accès impossible</p>' ;
	exit () ;
}
// SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS
?>


<?php
ini_set("max_execution_time", "780");
define('BOZAR_ID', 70);
require '../inc_fct_base.php';
require '../inc_db_connect.php';
require '../logs/fct_logs.php';
require '../inc_var.php';

$allowedTags = '<br />'; // Balises de style autorisées

// Renaud
function parser_contenu ($chaine_a_parser)
{
	$chaine_a_parser = str_replace("&#160;", " ", $chaine_a_parser);

	$chaine_a_parser = str_replace("<br>", "line_f", $chaine_a_parser);
	$chaine_a_parser = str_replace("<br/>", "line_f", $chaine_a_parser);
	$chaine_a_parser = str_replace("<p>", "line_f", $chaine_a_parser);
	$chaine_a_parser = str_replace("</p>", "line_f", $chaine_a_parser);

	$chaine_a_parser = str_replace("&laquo;", "\"", $chaine_a_parser);
	$chaine_a_parser = str_replace("&raquo;", "\"", $chaine_a_parser);
	$chaine_a_parser = str_replace("&quot;", "\"", $chaine_a_parser);
	
	$chaine_a_parser = str_replace("&#156;", "oe", $chaine_a_parser);

	return $chaine_a_parser;
}




function wget($url) {
	$reponse = '';
	$u = parse_url($url);
	$fp = @fsockopen($u['host'], 80, $errno, $errstr);
	if (!is_resource($fp)) {
		echo "Impossible d'acceder a <a href='$url'>$url</a> : $errno / $errstr<br/>\r\n";
		return;
	}
	$q = empty($u['query'])?'':('?'.$u['query']);
	fwrite($fp,
		'GET ' . $u['path'] . $q . ' HTTP/1.0' . "\r\n" .
		'HOST: ' . $u['host'] . "\r\n" .
		'Connection: close' . "\r\n\r\n"
	);
	while (!feof($fp)) {
		$reponse .= fgets($fp, 0x1000);
	}
	fclose($fp);
	$reponse = explode("\r\n\r\n", $reponse, 2);
	return $reponse;
}

function item_isInAgenda($lieu, $url) {
	$requete = "SELECT `id_rss` FROM `ag_rss` WHERE `lieu_rss`='$lieu' AND `unique_rss`='$url'";
	$reponse = mysql_query($requete);
	$nb = mysql_num_rows($reponse);
	return ($nb>0);
}

function item_regToAgenda($id, $lieu, $url) {
	$requete = "INSERT INTO `ag_rss` SET `lieu_rss`='$lieu', `unique_rss`='$url'"; 
	mysql_query($requete);
}

function picture_resize($uploaded_pic, $fileName) {
	global $max_w_pic_event, $max_h_pic_event;

	$jpeg_quality = 90;
	$rapport_max = $max_w_pic_event / $max_h_pic_event;
	$rapport_min = $rapport_max;

	$largeur_uploaded = imagesx($uploaded_pic);
	$hauteur_uploaded = imagesy($uploaded_pic);
	$rapport_uploaded = $largeur_uploaded / $hauteur_uploaded;

/*	echo '<br />largeur_uploaded : ',$largeur_uploaded;
	echo '<br />hauteur_uploaded : ',$hauteur_uploaded;
	echo '<br />rapport_uploaded : ',$rapport_uploaded;
*/
	if ($rapport_uploaded < $rapport_min) {
		$wsrc = $largeur_uploaded;
		$hsrc = round($largeur_uploaded / $rapport_min);
		$xsrc = 0;
		$ysrc = round(($hauteur_uploaded - $hsrc) / 2);
	}
	else if ($rapport_uploaded > $rapport_max) {
		$wsrc = round($hauteur_uploaded * $rapport_max);
		$hsrc = $hauteur_uploaded;
		$xsrc = round(($largeur_uploaded - $wsrc) / 2);
		$ysrc = 0;
	}
	else {
		$wsrc = $largeur_uploaded;
		$hsrc = $hauteur_uploaded;
		$xsrc = 0;
		$ysrc = 0;
	}
/*	echo '<br />xsrc : ',$xsrc;
	echo '<br />ysrc : ',$ysrc;
	echo '<br />max_w_pic_event : ',$max_w_pic_event;
	echo '<br />max_h_pic_event : ',$max_h_pic_event;
	echo '<br />wsrc : ',$wsrc;
	echo '<br />hsrc : ',$hsrc;
*/
	$resample = imagecreatetruecolor($max_w_pic_event, $max_h_pic_event); // Création image vide
	imagecopyresampled($resample, $uploaded_pic, 0, 0, $xsrc, $ysrc, $max_w_pic_event, $max_h_pic_event, $wsrc, $hsrc);
	if (file_exists($fileName))
		unlink($fileName);

	imagejpeg($resample, $fileName, $jpeg_quality);// Enregistrer la miniature sous le nom
//	echo '<br /><img src="',$fileName,'" alt="" /><br />';
	chmod($fileName, 0644);
}
/*
function picture_micro($uploaded_pic, $destination_micro, $new_W_Vignette, $new_H_Vignette) { // ---------- richir : vignette micro pour iphone
	$rapport = $new_W_Vignette / $new_H_Vignette;
	$largeur_uploaded = imagesx($uploaded_pic);
	$hauteur_uploaded = imagesy($uploaded_pic);

	if ($largeur_uploaded / $hauteur_uploaded < $rapport) {
		$wsrc = $largeur_uploaded;
		$hsrc = $largeur_uploaded / $rapport;
		$xsrc = 0;
		$ysrc = round(($hauteur_uploaded - $hsrc) / 4);
	}
	else {
		$wsrc = $hauteur_uploaded * $rapport;
		$hsrc = $hauteur_uploaded;
		$xsrc = round(($largeur_uploaded - $wsrc) / 2);
		$ysrc = 0;
	}
	$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
	imagecopyresampled($resample, $uploaded_pic, 0, 0, $xsrc, $ysrc, $new_W_Vignette, $new_H_Vignette, $wsrc, $hsrc);
	if (file_exists($destination_micro))
		@unlink($destination_micro);
	imagejpeg($resample, $destination_micro, 90);// Enregistrer la miniature sous le nom
	chmod($destination_micro, 0644); // Pour que l'image ait un CHMOD 644 et non 600
}
*/
function item_savePicture($id_event, $url_picture) {
	global $folder_pics_event;
	$path = '../' . $folder_pics_event ; // Pour créer les images en test -> .'Y'
	$file = 'event_' . $id_event . '_1.jpg';
	if (!file_exists($path . $file)) {
		list($header, $data) = wget($url_picture);
		$uploaded_pic = imagecreatefromstring($data);
		picture_resize($uploaded_pic, $path . $file);
/*		picture_resize($uploaded_pic, $w_vi_absolue, $path . 'vi_' . $file);
		picture_micro($uploaded_pic, $path.'micro_'.$file, 60, 60); */
		imagedestroy($uploaded_pic);
		$requete = "UPDATE `ag_event` SET `pic_event_1`='set' WHERE `id_event`=$id_event LIMIT 1";
		mysql_query($requete);
		echo " - <a href='$path.$file'>Image AJOUTEE</a><br />";
	} else {
		echo " - <a href='$path.$file'>Image existante</a><br />";
	}
}

function item_saveEvent($lieu, $url, $title, $periode, $ville, $desc, $resume, $genre) {
	// gestion des dates
	$periode_debut = reset($periode);
	$periode_fin = end($periode);
	$periode = implode(',', $periode);
	// gestion des textes
	//$title = mysql_real_escape_string($title);
	$title = htmlentities($title, ENT_QUOTES);
	//$desc = mysql_real_escape_string($desc);

$allowedTags = '<br /><br><br/>'; // Balises de style autorisées

	$desc = parser_contenu($desc) ;	
	$desc = strip_tags($desc,$allowedTags) ;
	$desc = html_entity_decode($desc, ENT_QUOTES, 'iso-8859-1') ;	
	$desc = htmlentities($desc, ENT_QUOTES, 'iso-8859-1') ;
	$desc = str_replace("line_f", "<br />", $desc); // c'est pas beau mais ça fonctionne


	//$resume = mysql_real_escape_string($resume);
	$resume = parser_contenu($resume) ;	
	$resume = strip_tags($resume,$allowedTags) ;
	$resume = html_entity_decode($resume, ENT_QUOTES, 'iso-8859-1') ;	
	$resume = htmlentities($resume, ENT_QUOTES, 'iso-8859-1') ;
	$resume = str_replace("line_f", " ", $resume); // c'est pas beau mais ça fonctionne

	
	
	
	// preparation requete
	$requete = "INSERT INTO `ag_event` SET
		`lieu_event`='$lieu',
		`nom_event`='$title',
		`date_event_debut`='$periode_debut',
		`date_event_fin`='$periode_fin',
		`jours_actifs_event`='$periode',
		`ville_event`='$ville',
		`description_event`='$desc',
		`resume_event`='$resume',
		`genre_event`='$genre';";
	if (mysql_query($requete)) 
	{
		$id = mysql_insert_id();
		item_regToAgenda($id, $lieu, $url); 
		
		// Notifier la création dans le rapport + e-mail
		log_write (70, '4', $id, 'Création nouvel événement via RSS', 'send_mail') ; 
		//($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
		
		
		
		
		return $id;
	}
	return 0;
}

function process_item($item, $genre) {
	// lien
	$url = $item->link;
	if (item_isInAgenda(BOZAR_ID, $url)) {
		echo "<a href='$url'>$url</a> deja dans l'agenda<br/><hr/>";
		return false;
	}

	// titre
	list($date, $title) = explode('|', $item->title, 2);
	$title = utf8_decode(trim(strip_tags($title)));
	echo ' "<strong>'.$title.'</strong> "<br />';

	// dates
	$dates = array();
	foreach ($item->openingdates->date as $date) 
	{
		$dates[] = $date;
	}



	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// Tester si la période de représentation de cet événement dépasse 3 mois, et la racourcire si nécessaire
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

	$periode_max = (mktime(0, 0, 0, 4, 1, 1970)); // Intervalle (en mois) maximum entre début et fin d'un événement
	
	$periode = $dates ;
	
	$periode_debut_test = reset($dates);
	$periode_fin_test = end($dates);

	$AAAA_debut = substr($periode_debut_test, 0, 4);
	$MM_debut = substr($periode_debut_test, 5, 2);
	$JJ_debut = substr($periode_debut_test, 8, 2);
	
	$AAAA_fin = substr($periode_fin_test, 0, 4);
	$MM_fin = substr($periode_fin_test, 5, 2);
	$JJ_fin = substr($periode_fin_test, 8, 2);
	
	$time_event_debut = date(mktime(0, 0, 0, $MM_debut, $JJ_debut, $AAAA_debut));
	$time_event_fin = date(mktime(0, 0, 0, $MM_fin, $JJ_fin, $AAAA_fin));
	
	if ($time_event_fin > ($time_event_debut + $periode_max))
	{
		$date_max_event = date('Y-m-d', ($time_event_debut + $periode_max)) ;
		echo '<strong><br />La periode de representation de cet evenement depasse 3 mois. 
		Elle sera raccourcie et s\'etendra du ' . $periode_debut_test . ' au ' . $date_max_event . '<br /></strong>' ;
		foreach ($date as $date_en_cours) 
		{
			if ($date_en_cours > $date_max_event )
			{
				//echo ' <em>'.$date_en_cours . '</em><br />';
				$entree_actuelle_tableau = array_search($date_en_cours, $date);
				unset ($date[$entree_actuelle_tableau]) ;

				$periode_fin_test = $date_max_event;
			}
		}
	}
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	
	
	
	// description
	$desc = $item->description;
	$desc = utf8_decode($desc);
		// extraire l'image
		$s = 'src="http://';
		$url_img = '';
		$posImgA = strpos($desc, $s);
		if ($posImgA) {
			$posImgA += strlen($s);
			$posImgB = strpos($desc, '"', $posImgA);
			$url_img = 'http://' . substr($desc, $posImgA, $posImgB - $posImgA);
		}
		// nettoyer la description
		$desc = str_replace(array('<p>', '</p>', '<BR>', '<br>', '<BR/>'), '<br/>', $desc);
		$desc = strip_tags($desc, '<br/><b></b>');
		$s = "Plus d'info";
		$desc = trim($desc);
		$pos = strrpos($desc, $s);
		if ($pos + strlen($s) == strlen($desc)) {
			$desc = trim(substr($desc, 0, $pos));
		}
		
	// Limiter la longueur de la description complète
	$max_lenght_description_event = 3000 ; // Nombre Max de caractères pour la dercription
	if (strlen($desc)>=$max_lenght_description_event)
	{ $desc = raccourcir_chaine($desc,$max_lenght_description_event) ; }

	// description courte :
	$desc_courte = wordwrap($desc, 100, " ", 1);
	$desc_courte = strip_tags($desc_courte);
	$max_lenght_resume_event = 400 ; // Nombre Max de caractères pour le résumé de la description courte
	if (strlen($desc_courte)>=$max_lenght_resume_event)
	{ $desc_courte = raccourcir_chaine($desc_courte,$max_lenght_resume_event) ; }
		
		

	// genre
/*	$genres = array(
		// Expos
		'Exposition' => 'g07',
		// Autres concerts
		'Spectacle' => 'g11',
		'Concert' => 'g11',
		// Evenements divers
		'Activité pédagogique' => 'g08',
		'Visite guidée' => 'g08',
		'Aperçu du programme' => 'g08',
		'Evénement' => 'g08',
		'Conférence' => 'g08',
		'Projection' => 'g12',
		// tags refuses
		'Festival' => '',
	);*/
	$g = substr($item->category, 0);
	//$genre = isset($genres[$g])?$genres[$g]:0;

	echo 'Genre selon BOZAR = '. $g .' | Genre DLP = ' . $genre  .'<br />';

	// insertion
	if (empty($genre)) {
		echo "<a href='$url'>$url</a> n'est pas dans un genre voulu: $g<br/><hr/>";
	} else {
		$id = item_saveEvent(BOZAR_ID, $url, $title, $dates, 'be1', $desc, $desc_courte, $genre);		
		if ($id) {
			if (empty($url_img)) {
				echo "Pas d'image pour cet event : <a href='$url'>$url</a><br/><hr/>";
			} else {
				item_savePicture($id, $url_img);
			}
			echo "<a href='$url'>$url</a> est maintenant enregistree: $title<br/><hr/>";
		} else {
			echo "<a href='$url'>$url</a> n'a pas pu etre enregistree: $title<br/><hr/>";
		}
	}
}


//echo "<h1>Analyse de <a href='$flux_url'>$flux_url</a></h1>\r\n";
		/*// Autres concerts
		'Spectacle' => 'g11',
		'Concert' => 'g11',
		// Evenements divers
		'Activité pédagogique' => 'g08',
		'Visite guidée' => 'g08',
		'Aperçu du programme' => 'g08',
		'Evénement' => 'g08',
		'Conférence' => 'g08',
		'Projection' => 'g12',
		// tags refuses
		'Festival' => '',*/
	
$url_des_genres = array(
	// Jazz
	'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=category-37-3' => 'g10',
	// Electro - Pop - Rock
	'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=category-111-3' => 'g03',
	// Danse
	'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=section-45' => 'g02',
	// Musique classique
	'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=category-36-3' => 'g09',
	// Cinéma
	'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=section-59' => 'g12',
	/* Conférences = pas assez précis...'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=category-100-56' => 'g13', */
	// Expos
	'http://www.bozar.be/rss_demandez.php?external=0&lng=fr&bozar=home&category=section-2' => 'g07',
);
foreach ($url_des_genres as $key => $genre_en_cours)
{
	echo '<br><h3 align="center">Flux trait&eacute; : <a href="' . $key . '">' . $key . '</a>
	<br />(genre DLP=' . $genres[$genre_en_cours] . ')</h3><br>' ;
	$flux_url = $key ;
	list($header, $data) = wget($flux_url);
	$flux_xml = $data;
	// On retire le gestionnaire de stats de bozar
	$flux_xml = preg_replace('#http://be.sitestat.com/.*?ns_url=#i', '', $flux_xml);
	$xml = new SimpleXMLElement($flux_xml);
	foreach ($xml->channel->item as $item)
	{
		process_item($item, $genre_en_cours);
	}
}
/*
	$uploaded_pic = imagecreatefromjpeg('../pics_events_test/img_neige.jpg');
	picture_resize($uploaded_pic, '../pics_events_test/resultat.jpg');
*/
