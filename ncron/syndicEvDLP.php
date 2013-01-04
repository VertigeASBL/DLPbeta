<?
/* ========== sortie Atom / RSS ==========
---------- CRON ----------	https://jesus.all2all.org:10000/cron/
wget --help
wget -t 3 -q -O - http://www.demandezleprogramme.be/ncron/syndicEvDLP.php?prm=msfdg54qs
(-t 3) = nombre d'essai : 3
(-q) = quiet, sans output
(-O -) = output documents to standard output instead of to files
http://www.gnu.org/software/wget/manual/wget.html
*/
	require('../nlettre/conf.php');

	if (! isset($prm) || $prm != 'msfdg54qs')
		die("Erreur: la tâche syndicEvDLP n'a pas été exécutée");

	$syn0titre = 'Demandez le programme !';
	$syn0descr = 'Théâtre, musique classique, danse, cirque, concerts, spectacles jeune public... Demandez le programme ! Il y en a pour tous les goûts ! Retrouvez également sur le site les critiques de notre équipe et les avis des spectateurs.';
	$syn0cright = 'Vertige asbl';
	$syn0logo = 'http://www.demandezleprogramme.be/squelettes/assets/logo_header_turquoise.jpg';

	$prm = 3; //--- 1: atom, 2: rss
	if (! isset($synaff))
		$synaff = 0; //--- 0: rien, 1: xml, 2: content-html
	$racin = 'http://www.demandezleprogramme.be/';

$genres = array (
	"g01" => "Théâtre",
	"g02" => "Danse",
	"g04" => "Cirque",
	"g09" => "Musique classique, Opéra",
	"g03" => "Electro-Pop-Rock",
	"g10" => "Jazz",
	"g06" => "Chanson française",
	"g11" => "Autres concerts",
	"g07" => "Expos",
	"g05" => "Pour enfants",
	"g12" => "Cinéma",
	"g13" => "Conférences",
	"g08" => "Evénements divers"
); //--- cf. agenda/inc_var.php

	//--- Connexion à la DB
	$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
	if (! $db_link) {
		echo 'Connexion impossible à la base de données ',$sql_bdd,' sur le serveur ',$sql_server;
		exit;
	}
	mysql_select_db($sql_bdd, $db_link);

	$sql = 'WHERE date_event_debut>=SUBDATE(CURDATE(), INTERVAL 1 MONTH) AND date_event_debut<=ADDDATE(CURDATE(), INTERVAL 2 MONTH) AND date_event_fin>=CURDATE()';
	$sql = 'SELECT id_event,lieu_event,nom_event,date_event_debut,date_event_fin,resume_event,genre_event,pic_event_1,nom_lieu FROM ag_event INNER JOIN ag_lieux ON lieu_event=id_lieu AND cotisation_lieu>SUBDATE(CURDATE(), INTERVAL 1 MONTH) '.$sql.' ORDER BY date_event_debut';
//--- echo $sql,'<hr />';
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	$av1 = array('<br>','<br />','<BR>','<BR />');
	$ap1 = array("\n"  ,"\n"    ,"\n"  ,"\n");
	$av2 = array('’' ,'–','œ' ,'…');
	$ap2 = array('\'','-','oe','...');

	function texteplain($chn) {
		global $av1, $ap1, $av2, $ap2;
		$chn = html_entity_decode($chn);
		$chn = str_replace('&#039;', '\'', str_replace('&#156;', 'oe', $chn));
		$chn = preg_replace('/&#\d+;/', ' ', $chn);
		$chn = str_replace($av1, $ap1, $chn);
		$chn = strip_tags($chn);
		$chn = str_replace('&', '&amp;', str_replace('<', '&lt;', $chn));
		$chn = str_replace($av2, $ap2, $chn);
		$g = strlen($chn);
		for ($k = 0; $k < $g; $k++) {
			$n = ord($chn{$k});
			if ($n < 32 || $n >= 127 && $n <= 159 || $n > 255)
				$chn{$k} = ' ';
		}
		return $chn;
	}
	function textehtml($chn) {
		global $av2, $ap2;
		$chn = html_entity_decode($chn);
		$chn = str_replace('&#039;', '\'', str_replace('&#156;', 'oe', $chn));
		$chn = preg_replace('/&#\d+;/', ' ', $chn);
		$chn = str_replace('&', '&amp;', $chn);
		$chn = str_replace('<br>', '<br />', $chn);
		$chn = str_replace($av2, $ap2, $chn);
		$g = strlen($chn);
		for ($k = 0; $k < $g; $k++) {
			$n = ord($chn{$k});
			if ($n < 32 || $n >= 127 && $n <= 159 || $n > 255)
				$chn{$k} = ' ';
		}
		return $chn;
	}

	$tbalis = array('syntitre','synident','synlien','syndate','synddbu','syndfin','syncateg','synidlieu','synlieu','synauteur','syndescr','syncont','synpodcast');
	if ($synaff != 0)
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n",'<html>',"\n",'<head><title>RSS Atom</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /></head>',"\n",'<body>',"\n";
	if ($synaff == 1)
		echo '<pre>';

	/*************************************************
	********************** Atom **********************
	*************************************************/
	if ($prm & 1) {
		$lg = 'fr';
		if (! $fich = @fopen('../syndication/syndicevAtom.xml', 'wb'))	//----- Ecraser
			echo 'Erreur: il est impossible de créer le fichier Atom<br />';
		else {
			$xml = '<?xml version="1.0" encoding="iso-8859-1"?>'."\n".'<feed xmlns="http://www.w3.org/2005/Atom" xmlns:vrtc="http://www.vertige.org/">'."\n";
			$xml .= "\t".'<title xml:lang="'.$lg.'">'.texteplain($syn0titre).'</title>'."\n";
			$xml .= "\t".'<id>'.$racin.'-Une-</id>'."\n";
			$xml .= "\t".'<link rel="self" href="'.$racin.'syndication/syndicevAtom.xml" />'."\n";
			$xml .= "\t".'<link rel="alternate" href="'.$racin.'-Une-" title="demandezleprogramme.be : LA UNE" />'."\n";
			$xml .= "\t".'<subtitle xml:lang="'.$lg.'">'.texteplain($syn0descr).' (Atom)</subtitle>'."\n";
			$xml .= "\t".'<updated>'.date('Y-m-d\TH:i:s\Z').'</updated>'."\n";
			$xml .= "\t".'<author>'."\n\t\t".'<name>demandezleprogramme.be</name>'."\n\t\t".'<email>info@demandezleprogramme.be</email>'."\n\t".'</author>'."\n";
			$xml .= "\t".'<generator>Vertige-pgm</generator>'."\n";
			if ($syn0cright)
				$xml .= "\t".'<rights>'.texteplain($syn0cright).'</rights>'."\n";
			if ($syn0logo)
				$xml .= "\t".'<logo>'.$syn0logo.'</logo>'."\n";

			flock($fich, 2);	//----- Verrouiller
			if (! @fwrite($fich, $xml))
				echo 'Erreur: il est impossible d\'écrire (1) dans le fichier Atom<br />';
			if ($synaff != 0)
				echo "\n",'<br />&#8226; Flux Atom : ';
			if ($synaff == 1)
				echo "\n",htmlspecialchars($xml);
			else if ($synaff == 2)
				echo '<div style="clear:both; line-height:1px; font-size:1px;">&#160;</div>';

			$mtnt = time();
			while ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
				$xml = "\t".'<entry>'."\n";
				reset($tbalis);
				while (list(, $k) = each($tbalis)) {
					switch ($k) {
						case 'syntitre': $balis = 'title'; $col = 'nom_event'; break;
						case 'synident': $balis = 'id'; $col = 'id_event'; break;
						case 'synlien': $balis = 'link'; $col = 'y'; break;
						case 'syndate': $balis = 'updated'; break;
						case 'synddbu': $balis = 'vrtc:datedebut'; $col = 'date_event_debut'; break;
						case 'syndfin': $balis = 'vrtc:datefin'; $col = 'date_event_fin'; break;
						case 'syncateg': $balis = 'category'; $col = 'genre_event'; break;
						case 'synidlieu': $balis = 'vrtc:idlieu'; $col = 'lieu_event'; break;
						case 'synlieu': $balis = 'vrtc:nomlieu'; $col = 'nom_lieu'; break;
						case 'synauteur': $balis = 'autname'; $col = ''; break;
						case 'syndescr': $balis = 'summary'; $col = 'resume_event'; break;
						case 'syncont': $balis = 'content'; $col = 'resume_event'; break;
						case 'synpodcast': $balis = 'enclosure'; $col = ''; break;
						default: continue 2;
					}
					if ($balis == 'updated')
						$xml .= "\t\t".'<updated>'.date('Y-m-d\TH:i:s\Z', $mtnt).'</updated>'."\n\t\t".'<published>'.date('Y-m-d\TH:i:s\Z', $mtnt++).'</published>'."\n";
					else if ($balis == 'id') {
						$id_event = $data[$col];
						$xml .= "\t\t".'<id>tag:demandezleprogramme.be,2000:agenda_'.$id_event.'</id>'."\n";
					}
					else if ($balis == 'link') {
						$xml .= "\t\t".'<link rel="alternate" type="text/html" hreflang="'.$lg.'" href="'.$racin.'-Detail-agenda-?id_event='.$id_event.'" />'."\n";
					}
					else if ($col && $data[$col])
						switch ($balis) {
						case 'content':
							$xml .= "\t\t".'<content type="xhtml"><div xmlns="http://www.w3.org/1999/xhtml">';
							$g = strlen($xml);
							if ($data['pic_event_1'] == 'set')
								$xml .= '<a href="'.$racin.'-Detail-agenda-?id_event='.$id_event.'"><img src="'.$racin.'agenda/pics_events/event_'.$id_event.'_1.jpg" style="float:left; margin:0 16px 6px 0; border:none;" alt="" width="100" /></a>'."\n";
							$xml .= '<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event='.$id_event.'" title="Voir" style="font-weight:bold; color:#8F0133;">'.texteplain($data['nom_event']).'</a><br />'."\n";
							$xml .= '<a href="http://www.demandezleprogramme.be/-Details-lieux-culturels-?id_lieu='.$data['lieu_event'].'" title="Lieu" style="font-weight:bold; color:#009A99;">'.texteplain($data['nom_lieu']).'</a>'."\n";
							if (isset($genres[$data['genre_event']]))
								$xml .= ' &#160; &#160; <span title="Genre du spectacle">'.$genres[$data['genre_event']].'</span>'."\n";
							$k = $data['date_event_debut'];
							$xml .= ' &#160; &#160; <span title="Période de représentation">'.substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).' &gt;&gt; ';
							$k = $data['date_event_fin'];
							$xml .= substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).'</span><br />';
							$xml .= "\n".texteplain($data[$col]).'<div style="clear:both; line-height:1px; font-size:1px;">&#160;</div>';
							if ($synaff == 2)
								echo substr($xml, $g),"\n";
							$xml .= '</div></content>'."\n";
							break;
						case 'category':
							if ($col == 'genre_event' && isset($genres[$data['genre_event']]))
								$xml .= "\t\t".'<category term="'.texteplain($genres[$data['genre_event']]).'" />'."\n";
							break;
						case 'autname':
							$xml .= "\t\t".'<author><name>'.texteplain($data[$col]).'</name></author>'."\n";
							break;
						case 'enclosure':
							$xml .= "\t\t".'<link rel="alternate" href="'.$racin.'media/'.$data[$col].'" type="image/jpeg" />'."\n";
							$xml .= "\t\t".'<link rel="enclosure" href="'.$racin.'media/'.$data[$col].'" type="image/jpeg" length="4545" />'."\n"; //---4545 '.filesize('../media/'
							break;
						default:
							if ($balis == 'summary') {
								$xml .= "\t\t".'<summary>'.texteplain($data['nom_lieu']);
								if (isset($genres[$data['genre_event']]))
									$xml .= ' - '.$genres[$data['genre_event']];
								$k = $data['date_event_debut'];
								$xml .= ' - '.substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).' &gt;&gt; ';
								$k = $data['date_event_fin'];
								$xml .= substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).' - ';
								$xml .= "\n".texteplain($data[$col]).'</summary>'."\n";
							}
							else
								$xml .= "\t\t".'<'.$balis.'>'.texteplain($data[$col]).'</'.$balis.'>'."\n";
						}
				}
				$xml .= "\t".'</entry>'."\n";
				if (! @fwrite($fich, $xml))
					echo 'Erreur: il est impossible d\'écrire (2) dans le fichier Atom<br />';
				if ($synaff == 1)
					echo htmlspecialchars($xml);
			}
			$xml = '</feed>';
			if (! @fwrite($fich, $xml))
				echo 'Erreur: il est impossible d\'écrire (2) dans le fichier Atom<br />';
			flock($fich, 3);	//----- Déverrouiller
			if (! fflush($fich))
				echo 'Erreur: il est impossible d\'écrire (flush) dans le fichier Atom<br />';
			fclose($fich);
			if ($synaff == 1)
				echo htmlspecialchars($xml),"\n";
			if ($synaff != 0)
				echo '<a href="',$racin,'syndication/syndicevAtom.xml" target="_blank">syndicevAtom.xml</a> &nbsp; <a href="http://validator.w3.org/feed/check.cgi?url=',urlencode($racin),'syndication/syndicevAtom.xml" target="_blank">valider</a>',"\n\n";
		}
	}

	/************************************************
	********************** RSS **********************
	************************************************/
	if ($prm & 2) {
		if (mysql_num_rows($req))
			mysql_data_seek($req, 0);

		$lg = 'fr';
		if (! $fich = @fopen('../syndication/syndicevRSS.xml', 'wb'))	//----- Ecraser
			echo 'Erreur: il est impossible de créer le fichier RSS<br />';
		else {
			$xml = '<?xml version="1.0" encoding="iso-8859-1"?>'."\n".'<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:vrtc="http://www.vertige.org/">'."\n".'<channel>'."\n";
			$xml .= "\t".'<title>'.texteplain($syn0titre).'</title>'."\n";
			$xml .= "\t".'<link>'.$racin.'-Une-</link>'."\n";
			$xml .= "\t".'<description>'.texteplain($syn0descr).' (RSS)</description>'."\n";
			$xml .= "\t".'<lastBuildDate>'.date('r').'</lastBuildDate>'."\n";
			$xml .= "\t".'<language>'.$lg.'</language>'."\n";
			$xml .= "\t".'<managingEditor>info@demandezleprogramme.be (demandezleprogramme.be)</managingEditor>'."\n";
			$xml .= "\t".'<generator>Vertige-pgm</generator>'."\n";
			if ($syn0cright)
				$xml .= "\t".'<copyright>'.texteplain($syn0cright).'</copyright>'."\n";

			if ($syn0logo) {
				$xml .= "\t".'<image>'."\n";
				$xml .= "\t\t".'<title>'.texteplain($syn0titre).'</title>'."\n";
				$xml .= "\t\t".'<url>'.$syn0logo.'</url>'."\n";
				$xml .= "\t\t".'<link>'.$racin.'-Une-</link>'."\n";
				$xml .= "\t".'</image>'."\n";
			}
			flock($fich, 2);	//----- Verrouiller
			if (! @fwrite($fich, $xml))
				echo 'Erreur: il est impossible d\'écrire (1) dans le fichier RSS<br />';
			if ($synaff != 0)
				echo "\n",'<br />&#8226; Flux RSS : ';
			if ($synaff == 1)
				echo "\n",htmlspecialchars($xml);
			else if ($synaff == 2)
				echo '<div style="clear:both; line-height:1px; font-size:1px;">&#160;</div>';

			$mtnt = time();
			while ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
				$xml = "\t".'<item>'."\n";
				reset($tbalis);
				while (list(, $k) = each($tbalis)) {
					switch ($k) {
						case 'syntitre': $balis = 'title'; $col = 'nom_event'; break;
						case 'synident': $balis = 'guid'; $col = 'id_event'; break;
						case 'synlien': $balis = 'link'; $col = 'y'; break;
						case 'syndate': $balis = 'pubDate'; break;
						case 'synddbu': $balis = 'vrtc:datedebut'; $col = 'date_event_debut'; break;
						case 'syndfin': $balis = 'vrtc:datefin'; $col = 'date_event_fin'; break;
						case 'syncateg': $balis = 'category'; $col = 'genre_event'; break;
						case 'synidlieu': $balis = 'vrtc:idlieu'; $col = 'lieu_event'; break;
						case 'synlieu': $balis = 'vrtc:nomlieu'; $col = 'nom_lieu'; break;
						case 'synauteur': $balis = 'dc:creator'; $col = ''; break;
						case 'syndescr': $balis = 'description'; $col = 'resume_event'; break;
						case 'syncont': $balis = 'content'; $col = 'resume_event'; break;
						case 'synpodcast': $balis = 'enclosure'; $col = ''; break;
						default: continue 2;
					}
					if ($balis == 'pubDate')
						$xml .= "\t\t".'<pubDate>'.date('r', $mtnt++).'</pubDate>'."\n";
					else if ($balis == 'guid') {
						$id_event = $data[$col];
						$xml .= "\t\t".'<guid isPermaLink="true">'.$racin.'-Detail-agenda-?id_event='.$id_event.'</guid>'."\n";
					}
					else if ($balis == 'link') {
						$xml .= "\t\t".'<link>'.$racin.'-Detail-agenda-?id_event='.$id_event.'</link>'."\n";
					}
					else if ($col && $data[$col])
						switch ($balis) {
						case 'content':
							$xml .= "\t\t".'<content:encoded><![CDATA[';
							$g = strlen($xml);
							if ($data['pic_event_1'] == 'set')
								$xml .= '<a href="'.$racin.'-Detail-agenda-?id_event='.$id_event.'"><img src="'.$racin.'agenda/pics_events/event_'.$id_event.'_1.jpg" style="float:left; margin:0 16px 6px 0; border:none;" alt="" width="100" /></a>';
							$xml .= '<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event='.$id_event.'" title="Voir" style="font-weight:bold; color:#8F0133;">'.texteplain($data['nom_event']).'</a><br />'."\n";
							$xml .= '<a href="http://www.demandezleprogramme.be/-Details-lieux-culturels-?id_lieu='.$data['lieu_event'].'" title="Lieu" style="font-weight:bold; color:#009A99;">'.texteplain($data['nom_lieu']).'</a>'."\n";
							if (isset($genres[$data['genre_event']]))
								$xml .= ' &#160; &#160; <span title="Genre du spectacle">'.$genres[$data['genre_event']].'</span>'."\n";
							$k = $data['date_event_debut'];
							$xml .= ' &#160; &#160; <span title="Période de représentation">'.substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).' &gt;&gt; ';
							$k = $data['date_event_fin'];
							$xml .= substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).'</span><br />';
							$xml .= "\n".texteplain($data[$col]).'<div style="clear:both; line-height:1px; font-size:1px;">&#160;</div>';
							if ($synaff == 2)
								echo substr($xml, $g),"\n";
							$xml .= ']]></content:encoded>'."\n";
							break;
						case 'category':
							if ($col == 'genre_event' && isset($genres[$data['genre_event']]))
								$xml .= "\t\t".'<category>'.texteplain($genres[$data['genre_event']]).'</category>'."\n";
							break;
						case 'enclosure':
							$xml .= "\t\t".'<guid isPermaLink="true">'.$racin.'media/'.$data[$col].'</guid>'."\n";
							$xml .= "\t\t".'<enclosure url="'.$racin.'media/'.$data[$col].'" length="4545" type="image/jpeg" />'."\n"; //---4545 '.filesize('../media/'.
							break;
						default:
							if ($balis == 'description') {
								$xml .= "\t\t".'<description>'.texteplain($data['nom_lieu']);
								if (isset($genres[$data['genre_event']]))
									$xml .= ' - '.$genres[$data['genre_event']];
								$k = $data['date_event_debut'];
								$xml .= ' - '.substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).' &gt;&gt; ';
								$k = $data['date_event_fin'];
								$xml .= substr($k, 8, 2).'/'.substr($k, 5, 2).'/'.substr($k, 0, 4).' - ';
								$xml .= "\n".texteplain($data[$col]).'</description>'."\n";
							}
							else
								$xml .= "\t\t".'<'.$balis.'>'.texteplain($data[$col]).'</'.$balis.'>'."\n";
						}
				}
				$xml .= "\t".'</item>'."\n";
				if (! @fwrite($fich, $xml))
					echo 'Erreur: il est impossible d\'écrire (2) dans le fichier RSS<br />';
				if ($synaff == 1)
					echo htmlspecialchars($xml);
			}
			$xml = '</channel>'."\n".'</rss>';
			if (! @fwrite($fich, $xml))
				echo 'Erreur: il est impossible d\'écrire (2) dans le fichier RSS<br />';
			flock($fich, 3);	//----- Déverrouiller
			if (! fflush($fich))
				echo 'Erreur: il est impossible d\'écrire (flush) dans le fichier RSS<br />';
			fclose($fich);
			if ($synaff == 1)
				echo htmlspecialchars($xml),"\n";
			if ($synaff != 0)
				echo '<a href="',$racin,'syndication/syndicevRSS.xml" target="_blank">syndicevRSS.xml</a> &nbsp; <a href="http://validator.w3.org/feed/check.cgi?url=',urlencode($racin),'syndication/syndicevRSS.xml" target="_blank">valider</a>',"\n\n";
		}
	}
	if ($synaff == 1)
		echo '</pre>',"\n";
	if ($synaff != 0)
		echo '</body>',"\n",'</html>',"\n";
	unset($xml, $lg, $tbalis, $balis, $racin, $fich, $mtnt);

	//--- Déconnexion de la DB
	mysql_close($db_link);

	if ($synaff == 0)
		echo 'Fin normale de la tâche : syndication événements DLP';
?>
