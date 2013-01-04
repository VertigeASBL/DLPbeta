<?php
//--- passer une variable pour #COMENV (cf balise_ENV_dist() dans inc-balises.php3)
function balise_COMENV($p) {
	if ($a = $p->param) {
		$nom = array_shift($a);
		if  (! array_shift($nom)) {
			$p->fonctions = $a;
			array_shift($p->param);
			$nom = array_shift($nom);
			$nom = ($nom[0]->type=='texte') ? $nom[0]->texte : '';
			$p->code = '$GLOBALS[\'contexte\'][\'' . addslashes($nom) . '\']';
			$p->statut = 'php';
		}
	}
	return $p;
}
/* --- retourner l'identifiant d'un article spip interview, critique ou chronique d'un événement d'une saison précédente lié à un événement donné
   --- ou pour trouver tous les avis sur un événement donné, retourner la liste des identifiants des événements de saisons précédentes liés en cascade */
function saisonprecedente($id_event, $quoi) {
	if (! $id_event)
		return 0;
	if ($quoi!='interview' && $quoi!='critique' && $quoi!='chronique' && $quoi!='avis' && $quoi!='jai_vu') //--- + espace_livres, video_spip
		return 0;

	if (! isset($GLOBALS['saisonpreced_'.$id_event])) { //--- obtenir les événements de saisons précédentes liés en cascade
		$mem_id_event = $id_event;
		$GLOBALS['saisonpreced_'.$id_event] = array($id_event);
		while ($mem_id_event) {
			$req = 'SELECT B.id_event,B.saison_preced_event FROM ag_event A,ag_event B WHERE A.id_event='.$mem_id_event.' AND B.id_event=A.saison_preced_event';
			$req = mysql_query($req); //spip_query($req); spip_fetch_array($req) - saisonprecedente() appelé par agenda/admin_agenda/edit_event.php
			$data = mysql_fetch_array($req);
			if ($data && $data['saison_preced_event'] != $mem_id_event) {
				$mem_id_event = $data['saison_preced_event'];
				$GLOBALS['saisonpreced_'.$id_event][] = $data['id_event'];
				if ($mem_id_event)
					$GLOBALS['saisonpreced_'.$id_event][] = $mem_id_event;
			}
			else
				$mem_id_event = 0;
		}
	}
	if ($quoi=='avis' || $quoi=='jai_vu')
		return implode(',', $GLOBALS['saisonpreced_'.$id_event]);

	//--- 1 article lié : interview, critique, chronique
	$req = 'SELECT id_event,genre_event,'.$quoi.'_event FROM ag_event WHERE id_event IN ('.implode(',', $GLOBALS['saisonpreced_'.$id_event]).') ORDER BY date_event_debut DESC';
	$req = mysql_query($req); //spip_query($req); spip_fetch_array($req)
	while ($data = mysql_fetch_array($req))
		if ($quoi=='chronique' && $data['id_event']==$id_event && $data['genre_event']!='g09' && $data['genre_event']!='g03' && $data['genre_event']!='g10' && $data['genre_event']!='g06' && $data['genre_event']!='g11' && $data['genre_event']!='g07' && $data['genre_event']!='g12' && $data['genre_event']!='g13')
			return 0; //--- en fonction du genre, il ne faut pas lier les chroniques des saisons précédentes
		else if ($data[$quoi.'_event'])
			return $data[$quoi.'_event'];

	return 0;
}
//------ créer TITLE+META pour événement (META1PLUS, META2PLUS affichés par inc_tur/agheader_meta)
function evenementmeta($void, $id_event) {
	include('agenda/inc_var.php');

	$GLOBALS['contexte']['meta1plus'] = '';
	$GLOBALS['contexte']['meta2plus'] = '';
	$id_event = is_numeric($id_event) ? (int) $id_event : 0;
	$reponse = mysql_query('SELECT nom_event,ville_event,genre_event,nom_lieu FROM ag_event INNER JOIN ag_lieux ON lieu_event=id_lieu WHERE id_event='.$id_event);

	if ($data = mysql_fetch_array($reponse)) {
		$GLOBALS['contexte']['meta1plus'] = $data['nom_event'].', '.$data['nom_lieu'];
		if (isset($genres[$data['genre_event']]))
			$GLOBALS['contexte']['meta2plus'] = ', '.$genres[$data['genre_event']];
		if (isset($regions[$data['ville_event']]))
			$GLOBALS['contexte']['meta2plus'] .= ', '.$regions[$data['ville_event']];
	}
	if ($GLOBALS['contexte']['meta2plus'])
		$GLOBALS['contexte']['meta2plus'] .= ', ';
}
//------ créer TITLE+META pour agenda, un genre (META1PLUS, META2PLUS affichés par inc_tur/agheader_meta)
function genreagmeta($void) {
	include('agenda/inc_var.php');

	$envlieu = '';
	if (isset($_POST['lieu_event'])) $envlieu = $_POST['lieu_event'];
	if (isset($_GET['lieu'])) $envlieu = $_GET['lieu'];
	$envgenre = '';
	if (isset($_POST['genre_event'])) $envgenre = $_POST['genre_event'];
	if (isset($_GET['genre'])) $envgenre = $_GET['genre'];
	$envregion = '';
	if (isset($_POST['ville_event'])) $envregion = $_POST['ville_event'];
	if (isset($_GET['region'])) $envregion = $_GET['region'];

	$chn = '';
	if ($envlieu && is_numeric($envlieu)) {
		$reponse = mysql_query('SELECT nom_lieu FROM ag_lieux WHERE id_lieu='.$envlieu);
		if ($data = mysql_fetch_array($reponse))
			$chn = $data['nom_lieu'];
	}
	if (isset($genres[$envgenre])) $chn .= ($chn ? ', ' : '').$genres[$envgenre];
	if (isset($regions[$envregion])) $chn .= ($chn ? ', ' : '').$regions[$envregion];

	$GLOBALS['contexte']['meta1plus'] = 'Demandez le programme - Agenda culturel : '.($chn ? $chn : 'théâtre, musique, danse, expos, concerts');
	$GLOBALS['contexte']['meta2plus'] = ', ';
}
function lieuagmeta($void, $id_lieu) {
	$GLOBALS['contexte']['meta1plus'] = 'Demandez le programme : ';
	$GLOBALS['contexte']['meta2plus'] = '';
	$id_lieu = is_numeric($id_lieu) ? (int) $id_lieu : 0;
	$reponse = mysql_query('SELECT nom_lieu,adresse_lieu FROM ag_lieux WHERE id_lieu='.$id_lieu);

	if ($data = mysql_fetch_array($reponse)) {
		$GLOBALS['contexte']['meta1plus'] = $data['nom_lieu'];
		$GLOBALS['contexte']['meta2plus'] = ', '.$data['adresse_lieu'].', ';
	}
}
/* -----------------------------------------------------------------------------
//--- afficher le contexte
function affichercontexte($void) {
	echo '<br />Contexte (',count($GLOBALS['contexte']),'):';
	reset($GLOBALS['contexte']);
	while (list($key, $val) = each($GLOBALS['contexte']))
		if (is_array($GLOBALS['contexte'][$key])) {
			echo '<br />--- Tableau (',count($GLOBALS['contexte'][$key]),') ------ ',$key,' => ',$val;
			while (list($k, $v) = each($GLOBALS['contexte'][$key]))
				echo '<br />---------------- ',gettype($GLOBALS['contexte'][$key][$k]),' : ',$key,'[',$k,'] => ',$v;
		}
		else
			echo '<br />---------------- ',gettype($GLOBALS['contexte'][$key]),' : ',$key,' => ',$val;
	echo '<br />';
}
*/
/* ------------------------------------------------------------------------------------------------------------------
 Ajout paramètres dans les URL du sous-menu -> passer les critères de recherche au moteur interne 
 "g01" => "théâtre"==71	"g02" => "Danse"==73	"g03" => "Concert"==	74 	"g04" => "Cirque"== 
72 "g05" => "Pour enfants"==	75	"g06" => "Conférence"== 77 "g07" => "Expos"== 78 "g08" => "Divers"== 79	
Musique classique == 108 = g09
Cinéma = g12=rubrique 123
Conférences = g13=rubrique 124
------------------------------------------------------------------------------------------------------------------*/
function redirect_menu_ag ($void, $adr_test_redirect, $url_rubrique_sous_menu)
{
	$liste_genre = array ( "71" => "g01", "73" => "g02", "72" => "g04", "108" => "g09", "74" => "g03", "116" => "g10", "77" => "g06", "117" => "g11", "78" => "g07", "75" => "g05", "123" => "g12", "124" => "g13", "79" => "g08" , "142" => "g14" ) ;

	if (array_key_exists ($adr_test_redirect, $liste_genre)) 
	{
		$adr_sous_menu = '-Agenda-?req=ext&amp;genre=' . $liste_genre[$adr_test_redirect] ;
		
		//echo $adr_test_redirect . ' - ' ;
	}
	elseif ($adr_test_redirect == 91) 
	{
		$adr_sous_menu = 'http://www.comedien.be/' ; // Vers Acceuil de COMEDIEN /-Professionnels-du-spectacle-
	}	
	else
	{
		$adr_sous_menu = $url_rubrique_sous_menu ;
	}
	return $adr_sous_menu ;
}
/* ------------------------------------------------------------------------------------------------------------------
   Ajout paramètres dans les URL du menu 
------------------------------------------------------------------------------------------------------------------*/
function redirect_menu_ag_2 ($void, $adr_test_redirect, $url_rubrique_sous_menu)
{
/*	if ($adr_test_redirect == 66) $adr_menu_remplacer = '-Interviews-?page=rubrique=66'; else... // Pour afficher interview dans le design turquoise */
	$adr_menu_remplacer = $url_rubrique_sous_menu ;
	return $adr_menu_remplacer ;
}
/*
function zerosi($void, $var, $nbr) {
//--- Exemple de critère de boucle {0,#PUCE|zerosi{print,99999}} Si 'print' dans url, aucun résultat. ! un seul {a,b}
	return isset($_GET[$var]) ? 0 : $nbr;
}
*/
function getcvar($num, $var) { //--- [(#COMPTEUR_BOUCLE|getcvar{'nom'})]
	return isset($GLOBALS['TY_'.$var.$num]) ? $GLOBALS['TY_'.$var.$num] : '';
}
function freecvar($void, $var0='',$var1='',$var2='',$var3='',$var4='',$var5='',$var6='',$var7='',$var8='',$var9='') { //--- [(#PUCE|freecvar{'nom1','nom2'..})]
	for ($g = 0; ${'var'.$g}; $g++)
		for ($k = 1; isset($GLOBALS['TY_'.${'var'.$g}.$k]); $k++)
			unset($GLOBALS['TY_'.${'var'.$g}.$k]);
	return '';
}
function envoiform($id_spect, $pseudo) {
	$id_spect = is_numeric($id_spect) ? (int) $id_spect : 0;
	$pseudo = addslashes($pseudo);

	$req = mysql_query("SELECT pseudo_spectateur,e_mail_spectateur FROM ag_spectateurs WHERE id_spectateur=$id_spect AND pseudo_spectateur LIKE '$pseudo' AND compte_actif_spectateur='oui'") or die('Erreur SQL');

	for ($k = 1; $data = mysql_fetch_array($req); $k++) {
		$GLOBALS['TY_pseudo'.$k] = $data['pseudo_spectateur'];
		$GLOBALS['TY_email'.$k] = $data['e_mail_spectateur'];
	} $k--;
	return $k;
}

// LECTEURS MP3 & FLV
function mp3($texte) {
	$texte = preg_replace("'<mp3=([^\]>]+)>([^\[]+)<\/mp3>'Ui",'<br /><br /><object type="application/x-shockwave-flash" data="IMG/mp3/dewplayer.swf?son=IMG/mp3/\\1" width="200" height="20"><param name="movie" value="IMG/mp3/dewplayer.swf?son=IMG/mp3/\\1"></object><span valign="middle"><br>\\2</span>',$texte);
	return $texte;
}
function callvideo($tab) {
	static $numflv = 0;
	global $largflv;
	$numflv++;
	return '<div id="idflv'.$numflv.'">'.$tab[2].'</div>'."\n".'<script type="text/javascript">'."\n".'var s1 = new SWFObject("squelettes/js/jwplayer3.swf","objflv'.$numflv.'","'.$largflv.'","271","8"); s1.addParam("allowfullscreen","true"); s1.addParam("allowscriptaccess","always"); s1.addParam("allownetworking","all"); s1.addParam("wmode","opaque"); s1.addParam("flashvars","file='.rawurlencode($tab[1]).'"); s1.write("idflv'.$numflv.'");'."\n".'</script>'."\n";
}
function video($texte) {
	global $largflv;
	$texte = preg_replace("'<video=([^\]>]+)>([^\[]+)<\/video>'Ui",'<br /><br /><object type="application/x-shockwave-flash" data="IMG/flv/video.swf?video=\\1" width="321" height="271"><param name="movie" value="IMG/flv/video.swf?video=\\1" /></object>',$texte);
	$largflv = '321';
	$texte = preg_replace_callback("'<video1=([^\]>]+)>([^\[]+)<\/video1>'Ui", 'callvideo', $texte);
	$largflv = '480';
	$texte = preg_replace_callback("'<video2=([^\]>]+)>([^\[]+)<\/video2>'Ui", 'callvideo', $texte);
	return $texte;
}
function mon_apres_propre($html) {
	$html = mp3($html);
	$html = video($html);
	return $html;
}
/*
  Ajoute une fonction aux traitements automatiques d'une balise.
*/
function ajouter_traitement_auto($nom_balise, $nom_fonction) {
	foreach ($GLOBALS['table_des_traitements'][$nom_balise] as $nom_table => $traitement)
		$GLOBALS['table_des_traitements'][$nom_balise][$nom_table] = "$nom_fonction($traitement)";
	if (! isset($GLOBALS['table_des_traitements'][$nom_balise][0]))
		$GLOBALS['table_des_traitements'][$nom_balise][0] = $nom_fonction.'(%s)';
}
if (isset($GLOBALS['table_des_traitements'])) {
	ajouter_traitement_auto('CHAPO', 'mon_apres_propre');
	ajouter_traitement_auto('TEXTE', 'mon_apres_propre');
}

/*
appeler sans ENV pour écraser une balise (par exemple #ID_MOT) dans le contexte d'une boucle : #SET_BALISE{id_mot,#SESSION{idprofil}}
appeler avec ENV pour écraser une balise d'environnement (par exemple #ENV{id_mot}) du contexte racine : #SET_BALISE{id_mot, #SESSION{idprofil}, ENV}
function balise_SET_BALISE($p) {
    $_var = interprete_argument_balise(1,$p);
    $_val = interprete_argument_balise(2,$p);
    $_niv = interprete_argument_balise(3,$p) == '\'ENV\'' ? '0' : '$SP';
    if ($_var AND $_val)
        $p->code = 'vide($Pile['.$_niv.']['.$_var.'] = '.$_val.')';
    else
        $p->code = "''";
    $p->interdire_scripts = false;
    return $p;
}
*/

/*
	Couper le texte pour que la hauteur s'adapte à la hauteur de l'image
	hors_txt		nb px hors texte à adapter ou espace total
	car_ligne	 	nb car par ligne
	px_ligne		nb px par ligne
*/
function mon_nb_cars($haut_img, $hors_txt = -65, $car_ligne = 50){
	$px_ligne = 15;
	if (! $haut_img && $hors_txt < 0)
		return 300;
	//Nb de lignes à afficher
	$nb_ligne = floor(($hors_txt < 0 ? $haut_img + $hors_txt : $hors_txt - $haut_img) / $px_ligne);
	//Nb de caractères à garder
	if ($nb_ligne < 0)
		$nb_ligne = 1;
	return $nb_ligne * $car_ligne;
}
function monraccourcirchaine($chn, $max) {
	if (strlen($chn)>=$max) {
		$chn=substr($chn,0,$max);
		$espace=strrpos($chn,' ');
		if($espace)
			$chn=substr($chn,0,$espace);
		$chn .= '...';
	}
	return $chn;
}
/*
	Obtenir les articles liés aux événéments
	rub 67 : critiques - rub 103 : Espace critiques
	rub 147 : chroniques - rub 113 : Espace chroniques
	rub 155 : vidéos
*/
function obtenirarticleslies($id_rubrique, $champlien, $max = 21, $home=0) {
	$req = 'SELECT A.id_article,A.titre,A.date,MAX(E.id_event) AS id_event,E.nom_event,E.pic_event_1,L.id_lieu,L.nom_lieu';
	if ($id_rubrique == 155)
		$req .= ',A.chapo,A.texte';
	else
		$req .= ',MAX(R.id_auteur),P.nom,A.chapo';
	$req .= ' FROM ag_event AS E,ag_lieux AS L,spip_articles AS A';
	if ($id_rubrique != 155)
		$req .= ' LEFT JOIN spip_auteurs_articles AS R ON A.id_article=R.id_article LEFT JOIN spip_auteurs AS P ON P.id_auteur=R.id_auteur';
	$req .= ' WHERE L.id_lieu=E.lieu_event AND L.cotisation_lieu>SUBDATE(CURDATE(),INTERVAL 1 MONTH) AND A.statut=\'publie\' AND A.id_rubrique='.$id_rubrique.' AND E.'.$champlien.'=A.id_article AND E.pic_event_1=\'set\'';
	if ($home) {
		$maintenant = time(); //--- 1296000 == 15 jours
		$req .= ' AND E.date_event_fin>=\''.date('Y-m-d', $maintenant).'\' AND E.date_event_debut<=\''.date('Y-m-d', $maintenant + 1296000).'\'';
	}
	$req .= ' GROUP BY A.id_article';
	$req .= $home ? ' ORDER BY RAND() LIMIT 1' : ' ORDER BY A.date DESC LIMIT '.$max;
	$req = spip_query($req);

	$tab = array();
	while ($data = spip_fetch_array($req))
		$tab[] = $data;
	return $tab;
}
/*
	Obtenir les événéments de la période par nombre d'avis desc cumulés avec saison_preced
	rub 160 : Avis des spectateurs			604800 == 7 jours
*/
function obteniravislies($max = 21) {
	$maintenant = time();
	$req = 'SELECT E.id_event,E.nom_event,E.pic_event_1,L.id_lieu,L.nom_lieu FROM ag_event AS E,ag_lieux AS L';
	$req .= ' WHERE L.id_lieu=E.lieu_event AND L.cotisation_lieu>SUBDATE(CURDATE(),INTERVAL 1 MONTH) AND E.date_event_fin>=\''.date('Y-m-d', $maintenant - 604800).'\' AND E.date_event_debut<=\''.date('Y-m-d', $maintenant + 604800).'\'';
	$req = spip_query($req);

	$t_ev = array(); $t_nom = array(); $t_pic = array(); $t_idlieu = array(); $t_lieu = array(); $t_nbr = array();
	while ($data = spip_fetch_array($req)) {
		$r2q = 'SELECT COUNT(id_avis) AS nombre FROM ag_avis WHERE event_avis IN ('.saisonprecedente($data['id_event'], 'avis').')';
		if ($r2q = spip_fetch_array(spip_query($r2q)))
			$r2q = $r2q['nombre'];
		if ($r2q) {
			$t_ev[] = $data['id_event']; $t_nom[] = $data['nom_event']; $t_pic[] = $data['pic_event_1']; $t_idlieu[] = $data['id_lieu']; $t_lieu[] = $data['nom_lieu'];
			$t_nbr[] = $r2q;
		}
	}
	array_multisort($t_nbr, SORT_NUMERIC, SORT_DESC, $t_ev, $t_nom, $t_pic, $t_idlieu, $t_lieu);

	reset($t_ev);
	$tab = array();
	for ($req = 0; $req < $max && list($k) = each($t_ev); $req++)
		$tab[] = array('id_event'=>$t_ev[$k], 'nom_event'=>$t_nom[$k], 'pic_event_1'=>$t_pic[$k], 'id_lieu'=>$t_idlieu[$k], 'nom_lieu'=>$t_lieu[$k], 'avis'=>$t_nbr[$k]);
	return $tab;
}
/*
	Obtenir les événéments de la période par jai_vu desc cumulés avec saison_preced
	60 sec * 60 min * 24 h * 7 jours == 604800
*/
function obtenirjaivulies($max = 5) {
	$maintenant = time();
	$req = 'SELECT E.id_event,E.nom_event,E.pic_event_1,E.date_event_debut,E.date_event_fin,E.resume_event,L.nom_lieu FROM ag_event AS E,ag_lieux AS L';
	$req .= ' WHERE L.id_lieu=E.lieu_event AND L.cotisation_lieu>SUBDATE(CURDATE(),INTERVAL 1 MONTH) AND E.date_event_fin>=\''.date('Y-m-d', $maintenant - 604800).'\' AND E.date_event_debut<=\''.date('Y-m-d', $maintenant + 604800).'\'';
	$req = spip_query($req);

	$t_ev = array(); $t_nom = array(); $t_pic = array(); $t_dbu = array(); $t_fin = array(); $t_txt = array(); $t_lieu = array(); $t_nbr = array();
	while ($data = spip_fetch_array($req)) {
		$r2q = 'SELECT COUNT(id_jai_vu) AS nombre FROM ag_jai_vu WHERE id_event_jai_vu IN ('.saisonprecedente($data['id_event'], 'jai_vu').')';
		if ($r2q = spip_fetch_array(spip_query($r2q)))
			$r2q = $r2q['nombre'];
		if ($r2q) {
			$t_ev[] = $data['id_event']; $t_nom[] = $data['nom_event']; $t_pic[] = $data['pic_event_1']; $t_lieu[] = $data['nom_lieu'];
			$t_dbu[] = $data['date_event_debut']; $t_fin[] = $data['date_event_fin']; $t_txt[] = $data['resume_event'];
			$t_nbr[] = $r2q;
		}
	}
	array_multisort($t_nbr, SORT_NUMERIC, SORT_DESC, $t_ev, $t_nom, $t_pic, $t_dbu, $t_fin, $t_txt, $t_lieu);

	reset($t_ev);
	$tab = array();
	for ($req = 0; $req < $max && list($k) = each($t_ev); $req++)
		$tab[] = array('id_event'=>$t_ev[$k], 'nom_event'=>$t_nom[$k], 'pic_event_1'=>$t_pic[$k], 'date_event_debut'=>$t_dbu[$k], 'date_event_fin'=>$t_fin[$k], 'resume_event'=>$t_txt[$k], 'nom_lieu'=>$t_lieu[$k], 'jai_vu'=>$t_nbr[$k]);
	return $tab;
}
/* repris de comedien.be --- $vx/$vy == 16/9 */
function replace_lien_video($codevideo, $vx, $vy) {
	include_spip('inc_tur/video_embed');

	$r = url_to_video_id($codevideo);
	if ($r !== NULL)
		return video_embed($r['which'], $r['video_id'], $vx, $vy);
	else
		if (strpos($codevideo, '<')===false && stripos($codevideo, 'http://')!==false)
			return  '<a href="'.htmlspecialchars($codevideo).'" target="_blank">Voir la vidéo</a>';
		else
			return preg_replace(',\swidth(:|=)(\'|")?(\d+)(\D),Ui', ' width${1}${2}'.$vx.'${4}', preg_replace(',\sheight(:|=)(\'|")?(\d+)(\D),Ui', ' height${1}${2}'.$vy.'${4}', $codevideo));
}
?>
