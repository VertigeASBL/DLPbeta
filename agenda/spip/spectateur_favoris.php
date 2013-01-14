<?php 
session_start();
require 'agenda/inc_var.php';
// Fonction panier /-- Didier
include_once('agenda/panier/fonctions_panier.php');
require_once 'agenda/inc_fct_base.php';
require_once 'agenda/calendrier/inc_calendrier.php';
//require_once 'ecrire/inc/filtres.php';
require_once 'ecrire/inc/utils.php';
?>

<h2>Mon agenda</h2>

<?php
$list_favoris = sql_allfetsel(
	'*', 
	'ag_panier INNER JOIN ag_event ON ag_panier.id_event=ag_event.id_event', 
	'ag_panier.id_spectateur = '.$_SESSION['id_spectateur'], 
	'',
	'YEAR(date_event_debut) DESC, MONTH(date_event_debut) DESC');
$tab = '';
$mois = '';

foreach ($list_favoris as $key => $donnees_1) {
	
	if ($mois != mois($donnees_1['date_event_debut'])) $date_fav = '<h3>'.affdate_mois_annee($donnees['date_event_debut']).'</h3>';
	else $date_fav = '';
	$mois = mois($donnees['date_event_debut']);

	$type_event = 'parent';

	$tab = $date_fav.'<div class="resultat_'.$type_event.'">'."\n";
//$tab.= $key_s.' / '.$premier_even.' : id_event : '.$_SESSION['t_id_event'][$key_s].' - trouvemot : '.$_SESSION['t_trouvemot'][$key_s].' - parent_event : '.$_SESSION['t_parent_event'][$key_s].' - lieu_event : '.$_SESSION['t_lieu_event'][$key_s].'<br />';

	$id_event = (int) $donnees_1['id_event'];

	// ____________________________________________
	// VIGNETTE EVENEMENT	
	$nom_event = htmlspecialchars($donnees_1['nom_event']);
	$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'"><img src="agenda/'.($donnees_1['pic_event_1']=='set' ? $folder_pics_event.'event_'.$id_event.'_1.jpg' : 'moteur_2_3/pics/event_sans_image.gif').'" class="onglet6 pic_'.$type_event.'" title="'.$nom_event.'" alt="" /></a>'."\n";

	$tab.= '<div class="res_droite_'.$type_event.'">'."\n";

	// ____________________________________________
	// ICONES FLOTTANTES (au niveau du titre)
	if ($type_event == 'enfant')
		$tab.= '<div class="icodr_enfant">'."\n";
	else
		$tab.= '<div class="icodr_parent"><div class="icodr_parent_g"></div>'."\n";

	// Icone concours
	$reponse_2 = mysql_query("SELECT id_conc FROM ag_conc_fiches WHERE event_dlp_conc=$id_event AND flags_conc='actif' ORDER BY id_conc DESC LIMIT 1");
	if ($total_entrees = mysql_fetch_array($reponse_2))
		$tab.= '<a href="'.generer_url_entite(95, 'rubrique', 'id='.$total_entrees['id_conc']).'" class="ico_droite icodr_concours" title="Un concours est actuellement en cours pour cet événement"></a>'."\n";

	// Vos Avis : compter le nbre d'entrées :
	$t_saison_preced = saisonprecedente($id_event, 'avis');
	$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM '.$table_avis_agenda.' WHERE event_avis IN ('.$t_saison_preced.') AND publier_avis=\'set\'');
	$total_entrees = mysql_fetch_array($count_avis);
	$total_entrees = $total_entrees['total_entrees'];
	if ($total_entrees > 0)
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#avis" class="ico_droite icodr_avis" title="'.$total_entrees.' spectateurs ont donné leur avis sur cet événement">'.$total_entrees.'</a>'."\n";
	
	// Icone Critique
	if ($donnees_1['critique_event'] != 0)
		$critique_event = $donnees_1['critique_event'];
	else
		$critique_event = saisonprecedente($id_event, 'critique');
	if ($critique_event)
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#critique" class="ico_droite icodr_critique" title="Lire la critique"></a>'."\n" ;

	// Icone chronique
	if ($donnees_1['chronique_event'] != 0)
		$chronique_event = $donnees_1['chronique_event'];
	else
		$chronique_event = saisonprecedente($id_event, 'chronique');
	if ($chronique_event)
		$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'#chronique" class="ico_droite icodr_chronique" title="Lire la chronique"></a>'."\n" ;

	// Icone Interview
	if ($donnees_1['interview_event'] != 0)
		$interview_event = $donnees_1['interview_event'];
	else
		$interview_event = saisonprecedente($id_event, 'interview');
	if ($interview_event)
		$tab.= '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" class="ico_droite icodr_interview" title="Lire l\'interview"></a>'."\n" ;

	// Icone "J'ai vu et aimé"
	$t_saison_preced = saisonprecedente($id_event, 'jai_vu');
	$count_avis = mysql_query('SELECT COUNT(*) AS total_entrees FROM ag_jai_vu WHERE id_event_jai_vu IN ('.$t_saison_preced.')');
	$total_entrees = mysql_fetch_array($count_avis);
	$total_entrees = $total_entrees['total_entrees'];
	$tab.= '<span class="ico_droite icodr_jaivu" title="'.$total_entrees.' spectateurs ont vu et aimé cet événement">'.$total_entrees.'</span>'."\n" ;

	// Icone suivi - Modifier par Didier
	$total_entrees = nombre_suivi($id_event);
	$tab.= '<span class="ico_droite icodr_suivi" title="'.$total_entrees.' spectateurs ont inscrit cet événement à leur agenda ">'.$total_entrees.'</span>'."\n";

	//--- fin ICONES FLOTTANTES
	if ($type_event == 'enfant')
		$tab.= '</div>'."\n";
	else
		$tab.= '<div class="icodr_parent_d"></div></div>'."\n";

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
	//if ($type_event != 'enfant')
	$tab.= '<span class="breve_lieu"><a href="'.generer_url_entite(96, 'rubrique', 'id_lieu='.$donnees_1['lieu_event']).'" title="Producteur du spectacle">'.raccourcir_chaine($donnees_1['nom_lieu'], 35).'</a></span>'."\n";

	// ____________________________________________
	// GENRE
	if ($donnees_1['genre_event'] != NULL) 
	{
		$genre_name = $donnees_1['genre_event'];
		$tab.= '| <span class="breve_genre"><acronym title="Genre du spectacle">' . $genres[$genre_name] . '</acronym></span>'."\n";	
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
	$tab.= '| <span class="breve_date"><acronym title="Période de représentation">du ' . $date_event_debut_jour . '/'
	. $date_event_debut_mois . '/'
	. $date_event_debut_annee . ' au ' . $date_event_fin_jour . '/'
	. $date_event_fin_mois . '/'
	. $date_event_fin_annee . '</acronym></span>'."\n";	

	// ____________________________________________
	// VILLE
	if ($donnees_1['ville_event'] != NULL) 
	{
		$ville_event_de_db = $donnees_1['ville_event'];
		$tab.= '| <span class="breve_ville"><acronym title="Ville où du spectacle">' . $regions[$ville_event_de_db] .'</acronym></span>'."\n";	
	}

	// ____________________________________________
	// TEXTE RESUME 
/*	
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
			$resum_txt = raccourcir_chaine($donnees_1['resume_event'], strip_tags($donnees_1['parent_event']) ? 100 : 300);
			$array_retour_ligne = array("<br>", "<br />", "<BR>", "<BR />");
			$uuuuueeeeeeee = str_replace($array_retour_ligne, " ", $resum_txt);
			$tab.= '<br />'.$uuuuueeeeeeee ;
 */
	$tab.= '<p class="breve_resume">'.raccourcir_chaine(strip_tags($donnees_1['resume_event']), $type_event == 'enfant' ? 100 : 400).'</p>'."\n";
/*
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
*/
	$tab.= '<div class="en_savoir_plus">'."\n" ;
	// Lien "en savoir plus"
	$tab.= '<a href="'.generer_url_entite(92, 'rubrique', 'id_event='.$id_event).'" style="float:right;">En savoir plus &#187;</a>'."\n";
	// Icone suivre - Modifier par Didier
	if (!empty($_SESSION['id_spectateur'])) {
		if (!statut_panier($_SESSION['id_spectateur'], $id_event))
			$tab.= '<a href="?id_event='.$id_event.'&suivre=1" class="ico_gauche icoga_suivre" title="Suivre cet événement">Ajouter à mon agenda</a>'."\n";
		else
			$tab.= '<a href="?id_event='.$id_event.'&plus_suivre=1" class="ico_gauche icoga_suivre" title="Ne plus suivre cet événement">Retirer de mon agenda</a>'."\n";

		$tab.= '<a href="#voter" class="ico_gauche icoga_voter" onclick="popup_jai_vu(\'agenda/jai_vu/jai_vu_popup.php?id='.$id_event.'\',\'Votons\'); return false;" title="Voter pour cet événement">J\'ai vu et aimé</a>'."\n" ;
	}
	$tab.= '</div>'."\n";

	$tab.= '</div>'."\n"; //--- fin fin res_droite_..
	$tab.= '<div class="float_stop"></div>'."\n".'</div>'."\n\n";
	echo $tab;
	}
	
	?>