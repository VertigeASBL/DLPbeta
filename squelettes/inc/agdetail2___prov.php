<!-- #CACHE{0} -->

<script type="text/javascript">
var pretjquery = false;
$(document).ready(function() {
	pretjquery = true;

	$(".onglettitre").click(function() {
		var idonglet = -1, cetobj = this;
		$(".onglettitre").each( function(vid) {
			if (this == cetobj)
				idonglet = vid;
		});
		$(".ongletevent").each( function(vid) {
			if (vid == idonglet)
				$(this).show();
			else
				$(this).hide();
		});
	});
});

function naviguermois(prec, annee, mois) {
	if (! pretjquery)
		return;
	var chn = ""+annee+mois;
	if (prec)
		if (mois > 1) mois--; else { annee--; mois = 12; }
	else
		if (mois < 12) mois++; else { annee++; mois = 1; }
	var obj = $("#mois"+annee+mois);
	if (obj.length != 0) {
		$("#mois"+chn).hide();
		obj.show();
	}
	return false;
}

/* Toggle entre 2 blocs */
function toggle_zone(Zone_id,txtReplier,txtDeplier) {
	var targetElement; var targetElementLink;
	targetElement = document.getElementById(Zone_id) ;
	targetElementB = document.getElementById(Zone_id+'B');
	targetElementLink = document.getElementById(Zone_id+'Link');
	if (targetElement.style.display == "none") {
		targetElement.style.display = "" ;
		targetElementB.style.display = "none" ;
	} else {
		targetElement.style.display = "none" ;
		targetElementB.style.display = "" ;
	}
}
</script>

<?php 
require 'agenda/inc_var.php';
//require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';
require 'agenda/calendrier/inc_calendrier.php';
require_once 'ecrire/inc/filtres.php';

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction d'affichage du calendrier avec cases colorées en fonction des jours actifs
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function affich_jours_actifs($jours_actifs, $MM_traite, $AAAA_traite, $datev_debut, $datev_fin, $affich, $pn)
{
	$date_event_debut_condition = str_replace("-","",$datev_debut);
	$date_event_fin_condition = str_replace("-","",$datev_fin);
	
	for ($j=1; $j<=31; $j++)
	{
		// Composer la chaine qui sera cherchée dans la DB :
		$MM_traite = str_pad($MM_traite, 2, "0", STR_PAD_LEFT);  // Complète la chaîne
		$JJ_traite = str_pad($j, 2, "0", STR_PAD_LEFT);  // Complète la chaîne
		$date_traite = $AAAA_traite.'-'.$MM_traite.'-'.$JJ_traite;
		settype($JJ_traite, "integer"); // Pour éviter problèmes avec les nombres précédés de "0"

		$date_traite_condition = str_replace("-","",$date_traite); 

		// jour HORS période
		if (($date_traite < $datev_debut)OR($date_traite > $datev_fin))
		{
			//echo $date_traite_condition .' - ' .$date_event_debut_condition .'<br>';
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day nonchecked',$JJ_traite);
		}
		// jour ACTIF
		elseif (in_array($date_traite, $jours_actifs))
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day checked',$JJ_traite);
		}
		else
		{
			$tableau_jours[$JJ_traite] = array(NULL,'linked-day unchecked',$JJ_traite);
		}
	}
	// Affichage du calendrier / old : echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1);
	$j = $AAAA_traite.(int) $MM_traite;
	echo '<div id="mois'.$j.'" style="display:'.($affich ? 'block' : 'none').';">';
	$j = $AAAA_traite.','.(int) $MM_traite;
	$pn = array('&lt;&lt;' => $pn & 1 ? 'return naviguermois(true,'.$j.');' : '',
				'&gt;&gt;' => $pn & 2 ? 'return naviguermois(false,'.$j.');' : '');
	echo generate_calendar($AAAA_traite, $MM_traite, $tableau_jours, 2, NULL, 1, $pn);
	echo '</div>'."\n";
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


	if (empty ($_GET['id_event']) OR $_GET['id_event'] == NULL )
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>',"\n",'<div class="alerte">Mauvais paramètre GET<br><a href="index.php" >Retour</a></div>',"\n";
		return;
	}

	$id_event = htmlentities($_GET['id_event'], ENT_QUOTES);
	$reponse = mysql_query('SELECT * FROM '.$table_evenements_agenda.' WHERE id_event='.$id_event);
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id_event'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p>',"\n",'<div class="alerte">Cette entrée n\'existe pas<br><a href="index.php" >Retour</a></div>'."\n";
		return;
	}

	// ------------------------------------------------
	// Lecture des infos de la DB pour cette entrée
	// ------------------------------------------------
	$genre_event = $donnees['genre_event'];

	$date_event_debut = $donnees['date_event_debut'];
	$date_event_fin = $donnees['date_event_fin'];
	$AAAA_debut = substr($date_event_debut, 0, 4);
	$AAAA_fin = substr($date_event_fin, 0, 4);
	$MM_debut = substr($date_event_debut, 5, 2);	
	$MM_fin = substr($date_event_fin, 5, 2);
	$JJ_debut = substr($date_event_debut, 8, 2);
	$JJ_fin = substr($date_event_fin, 8, 2);
	$AAAA_MM_debut = substr($date_event_debut, 0, 7);

	$jours_actifs_event = $donnees['jours_actifs_event'];
	$jours_actifs_event = explode(",", $jours_actifs_event);

	$saison_preced_event = $donnees['saison_preced_event'];

	//----- S'il s'agit d'un sous-événement, obtenir l'événement-parent, le festival
	$reponse = mysql_query('SELECT nom_event FROM '.$table_evenements_agenda.' WHERE id_event='.$donnees['parent_event']);
	$donnees_parent = mysql_fetch_array($reponse);

	//----- TABLE LIEU
	$reponse = mysql_query('SELECT * FROM '.$table_lieu.' WHERE id_lieu = '.$donnees['lieu_event']);
	$donnees_prod = mysql_fetch_array($reponse);

	//----- lieu de representation
	$reponse = mysql_query('SELECT * FROM  ag_representation WHERE id_pres='.$donnees['pres_event']);
	$donnees_repres = mysql_fetch_array($reponse);

	// ------------------------------------------------
	// Affichage contenu de l'événement
	// ------------------------------------------------
	echo '<div class="detail_event_bloc_1">',"\n";


	// titre parent
	echo $donnees_parent ? '<h2><a href="-Detail-agenda-?id_event='.$donnees['parent_event'].'" title="Voir en détail">'.$donnees_parent['nom_event'].'</a></h2>' : '';

	// ______________________
	// ICONES FLOTTANTES
	echo '<div class="ico_float_droite_relative">'."\n";
	
	// Icone suivre
	echo '<a href="#suivre" title="suivre" style="float:right;">Suivre</a> &nbsp; '."\n";

	// Icone "J'ai vu et aimé"
	$t_saison_preced = saisonprecedente($id_event, 'jai_vu');
	$reponse_avis= mysql_query('SELECT COUNT(*) AS total_entrees FROM ag_jai_vu WHERE id_event_jai_vu IN ('.$t_saison_preced.')');
	$total_entrees = mysql_fetch_array($reponse_avis);
	$total_entrees = $total_entrees['total_entrees'];
	echo '<div class="nombre_votes"><a href="#vote" onclick="popup_jai_vu(\'agenda/jai_vu/jai_vu_popup.php?id='.$id_event.'\',\'Votons\'); return false;">'
	.'<span class="nombre_votes_bulle_detail"> &nbsp; '.($total_entrees ? $total_entrees : ' ').'</span>'
	.'<img src="agenda/design_pics/ico_jai_vu.jpg" style="vertical-align:middle;" title="cliquez pour voter pour cet événement" alt="cliquez pour voter pour cet événement" /></a>'
	.'</div>'."\n";

	// Icone Interview
	if (! $interview_event)
		$interview_event = saisonprecedente($id_event, 'interview');
	if ($interview_event)
		echo '<a href="spip.php?page=interview&amp;qid='.$interview_event.'&amp;rtr=y" title="Cliquez ici pour lire l\'interview" style="float:right;"><img src="agenda/design_pics/ico_interview.jpg" align="middle"/>Voir l\'interview</a> &nbsp; '."\n";

	// Icone concours
	echo '<a href="#concours" style="float:right;" title="concours">Concours</a> &nbsp; '."\n";

	// facebook j'aime
	echo '<div class="fb-like" style="clear:both; float:right;" data-href="[(#SELF|url_absolue|rawurlencode)]" data-send="false" data-layout="button_count" data-width="90" data-show-faces="false"></div> &nbsp; '."\n";

	// afficher Envoyer à un ami;
	echo '<a href="-Envoyer-a-un-ami-?id_event='.$id_event.'" style="float:right;"><img src="agenda/e_card/pics/ico_envoyer_ami.jpg" title="Informer un ami" alt="Informer un ami" hspace="2" align="middle" /></a>'."\n";

	echo '</div>'."\n\n"; //--- fin ICONES FLOTTANTES


	// ____________________________________________
	// NOM EVENEMENT (titre)
	echo '<span class="detail_event_titre">'.$donnees['nom_event'].'</span>'."\n";

	// ____________________________________________
	// VILLE
	if (isset($regions[$donnees['ville_event']]))
	echo '<br /><span class="detail_corps_event"><acronym title="Ville où du spectacle">'.$regions[$donnees['ville_event']].'</acronym></span>'."\n";	

	// ____________________________________________
	// GENRE
	if (isset($genres[$genre_event]))
		echo '<span class="detail_event_genre"><acronym title="Genre du spectacle">'.$genres[$genre_event].'</acronym></span> '."\n";	

	// ____________________________________________
	// LIEU
	echo '<span class="detail_event_lieu"><a href="-Details-lieux-culturels-?id_lieu='.$donnees['lieu_event'].'" title="Producteur du spectacle">'.$donnees_prod['nom_lieu'].'</a></span> '."\n";	

	echo '<br style="clear:both;" /><br />',"\n";

	// ____________________________________________
	// Vignette
	if (isset ($donnees['pic_event_1']) AND $donnees['pic_event_1'] == 'set' )
		echo '<span class="detail_event_photo"><a href="-Detail-agenda-?id_event='.$id_event.'"><img src="agenda/'.$folder_pics_event.'vi_event_'.$id_event .'_1.jpg" title="'.htmlspecialchars($donnees['nom_event']).'" /></a></span>'."\n";
	

	//_________________ Infos utiles _____________________
	$time_date_event_fin = date(mktime(0, 0, 0, $MM_fin, $JJ_fin, $AAAA_fin));
	$time_date_aujourdhui = date(mktime(0, 0, 0, date("m"), date("d"), date("Y")));

	echo '<table class="tableinfosevent" border="0" cellspacing="0" cellpadding="0" summary="">'."\n";
	
	echo '<tr><td>Quand</td><td>du ',affdate_court($date_event_debut),' au ',affdate_court($date_event_fin),'</td></tr>'."\n";

	if ($donnees['heure_minute_event'] != 'nn-nn')
		echo '<tr><td>Horaire</td><td>',$donnees['heure_minute_event'],'</td></tr>'."\n";

	if ($donnees_repres)
		echo '<tr><td>Où</td><td>',$donnees_repres['nom_pres'],'<br />',$donnees_repres['adresse_pres'],'<br />',$donnees_repres['postal_pres'],' ',$donnees_repres['localite_pres'],'</td></tr>'."\n";

	if ($donnees['prix_min_event'] || $donnees['prix_max_event']) {
		echo '<tr><td>Prix</td><td>';
		if ($donnees['prix_min_event'] == $donnees['prix_max_event'] || ! $donnees['prix_max_event'])
			echo $donnees['prix_min_event'];
		else if (! $donnees['prix_min_event'])
			echo $donnees['prix_max_event'];
		else
			echo 'de ',$donnees['prix_min_event'],' à ',$donnees['prix_max_event'],' &euro;';
		echo '</td></tr>',"\n";
	}

	if (!empty($donnees['tel_reserv_event']) AND $donnees['tel_reserv_event'] != NULL 
	AND  ($time_date_event_fin+86400) > ($time_date_aujourdhui+0) AND ($genre_event != 'g07'))
		echo '<tr><td>Réservation</td><td>'.$donnees['tel_reserv_event'].'</td></tr>'."\n";

	// Afficher bouton de Réservation
	if (!empty($donnees_prod['email_reservation']) AND $donnees_prod['email_reservation'] != NULL 
	AND  ($time_date_event_fin+86400) > ($time_date_aujourdhui+0) AND ($genre_event != 'g07'))
		echo '<tr><td colspan="2"><a href="-Reserver-?id_event='. $id_event .'" title="Réservez vos places en ligne !!" ><img src="agenda/design_pics/bouton_reserver.jpg" hspace="2" align="middle" /></a></td></tr>'."\n";

	echo '</table>'."\n\n";

	echo '</div>'."\n\n"; //--- fin detail_event_bloc_1
	

	// ***************************************************************************************
	// CALENDRIERS :
	echo'<div class="detail_event_bloc_2">
		<div class="head_detail_event_bloc_2">
			<!-- div class="top_left_bloc_calendrier"></div -->
			<span class="titre_paragr_detail_event">Calendrier</span>
		</div>';
	echo '<div class="detail_corps_event">';

	/* --------------------------------------------------------------------
	   ----------------------- AFFICHER CALENDRIERS -----------------------
	   -------------------------------------------------------------------- */
	// [A] Si p&eacute;riode comprise dans le m&ecirc;me mois : traiter les jours de JJ_debut &agrave; JJ_fin
	if (($MM_debut == $MM_fin) && ($AAAA_debut == $AAAA_fin))
	{
		$AAAA_traite = $AAAA_debut;
		$MM_traite = $MM_debut;

		/*echo ' [A] P&eacute;riode couvrant 1 mois unique. Mois trait&eacute; = '.$MM_traite.' 
		et Ann&eacute;e trait&eacute;e = '.$AAAA_traite.'<br>'; */
		
		affich_jours_actifs($jours_actifs_event, $MM_traite, $AAAA_traite, $date_event_debut, $date_event_fin, true, 0);
	}
	// ------------------------------------------------------------------------------------------------------
	else
	{
		// [B1] si la p&eacute;riode s'&eacute;tend sur plusieurs mois, afficher 1 calendrier &agrave; chaque passage dans la boucle. 
		// Commencer par traiter le mois de d&eacute;but de p&eacute;riode
		$cemois = date('Ym');
		$AAAA_MM_traite = substr($date_event_debut, 0, 7);
		$AAAA_traite = $AAAA_debut;
		$MM_traite = $MM_debut;
		// echo '<b>[B1] Mois trait&eacute; (1er mois de la p&eacute;riode) = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite.'</b><br>';
		
		$tableau_jours = array();	
	
		affich_jours_actifs($jours_actifs_event, $MM_traite, $AAAA_traite, $date_event_debut, $date_event_fin, $cemois==$AAAA_traite.$MM_traite || $cemois<$AAAA_debut.$MM_debut || $cemois>$AAAA_fin.$MM_fin, 2);
	
		// Incr&eacute;menter le mois :		
		if	($MM_traite == 12)
		{
			$MM_traite = 1;
			$AAAA_traite = $AAAA_traite + 1;
		}
		else
		{
			$MM_traite = $MM_traite + 1;
		}
		// -------------------------------------------------------------------------------------------------
		// [B2] traiter tous les mois suivants jusqu'&agrave; ce qu'on arrive au mois de fin de PERIODE
		// La boucle s'arr&ecirc;te quand (($MM_traite == $MM_debut) && ($AA_fin == $AAAA_traite))
	
		while (($MM_traite != $MM_fin) OR ($AAAA_traite != $AAAA_fin))
		{
			/*unset ($tableau_jours[$JJ_db]);	*/
			$tableau_jours = array();
		
			//echo '<b>[B2] Mois "suivant" trait&eacute; = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite.'</b><br>';
			
			affich_jours_actifs($jours_actifs_event, $MM_traite, $AAAA_traite, $date_event_debut, $date_event_fin, $cemois==$AAAA_traite.$MM_traite, 3);
	
			// Incr&eacute;menter le mois :		
			if	($MM_traite == 12)
			{
				$MM_traite = 1;
				$AAAA_traite = $AAAA_traite + 1;
			}
			else
			{
				$MM_traite = $MM_traite + 1;
			}
		}
		// -------------------------------------------------------------------------------------------------
		// [B3] traiter le dernier mois de JJ = 1 &agrave; JJ = JJ_fin
		$tableau_jours = array();
		$AAAA_MM_traite = substr($date_event_fin, 0, 7);
	
		//echo '<b> [B3] Mois trait&eacute; (Dernier mois de la p&eacute;riode) = '.$MM_traite.' et Ann&eacute;e trait&eacute;e = '.$AAAA_traite.'</b><br>';
	
		affich_jours_actifs($jours_actifs_event, $MM_traite, $AAAA_traite, $date_event_debut, $date_event_fin, $cemois==$AAAA_traite.$MM_traite, 1);
	}
	// -----------------------------
	// Légende du calendrier 
	echo '<br /><em><span class="checked">Jour de représentation</span><br /><span class="unchecked">Pas de représentation</span></em>';
	echo '</div>',"\n"; //--- fin detail_corps_event
	echo '</div>',"\n\n"; //--- fin agenda detail_event_bloc_2 CALENDRIERS

	/* --------------------------------------------------------------------
	   ----------------------------- ONGLETS ------------------------------
	   -------------------------------------------------------------------- */
	echo '<div class="onglettitre">Descriptif</div>',"\n";
	echo '<div class="onglettitre">Avis (n)</div>',"\n";
	echo '<div class="onglettitre">Critique (n) Chronique</div>',"\n";
	echo '<div class="onglettitre">Vidéo</div>',"\n";
	echo '<div class="onglettitre">Mapping</div>',"\n";

/*	____________________________________________
		ONGLET DESCRIPTIF
	____________________________________________ */
	echo '<div class="ongletevent">',"\n";

	/* COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN
	Rajouter des liens vers les pages perso des comédiens si leur nom apparait dans le texte sous la forme "prénom nom" 
	PS : Dans la Requête, ID 1726 est là pour éviter Marion
	
	Connecter à la DB des Comediens */
	require 'agenda/inc_db_connect_to_comedien.php';
	
	$reponse_comedien = mysql_query('SELECT nom,prenom,url FROM comediens WHERE accord > 0 AND ID != 1726');
	while ($donnees_comedien = mysql_fetch_array($reponse_comedien))
	{
		$comedien_prenom_nom = $donnees_comedien['prenom'].' '.$donnees_comedien['nom'];
	
//		if (preg_match("!$comedien_prenom_nom+[^a-zA-Z]!", $donnees['description_event']))
		if (strpos($donnees['description_event'], $comedien_prenom_nom) !== false) {
			$comedien_url = '<span class="comedien_dans_description"><a href="http://www.comedien.be/'.$donnees_comedien['url'].'" title="Voir le profil sur le site comedien.be" target="_blank" style="color: #E38E0F;"><img src="agenda/design_pics/voir_comedien.gif" / height="12" align="bottom">'.$comedien_prenom_nom.'</a></span>';
			$donnees['description_event'] = preg_replace('/\b'.$comedien_prenom_nom.'\b/', $comedien_url, $donnees['description_event']);
		}
	}
	require 'agenda/inc_db_connect.php'; // reconnecter à la DB DLP
	// COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN  -  COMEDIEN

	echo '<div class="detail_corps_event">'.$donnees['description_event'].'</div>'."\n\n";
	echo '<br style="clear:both;" />
	</div>',"\n"; //--- fin ongletevent descriptif

/*	____________________________________________
		ONGLET AVIS
	____________________________________________ */
	echo '<div class="ongletevent">',"\n";
	$avis_concat ='<a name="avis" id="avis"></a>' ;

	// compter le nbre d'entrées :
	$t_saison_preced = saisonprecedente($id_event, 'avis');
	$reponse_avis= mysql_query('SELECT COUNT(*) AS total_entrees FROM '.$table_avis_agenda.' WHERE event_avis IN ('.$t_saison_preced.') AND publier_avis=\'set\'');
	$donnees_avis = mysql_fetch_array($reponse_avis) ;

	// compter le nombre de caractères dans le cas où il n'y a que 1 seul avis (à faire avant de lancer les conditions d'après !)
	$total_entrees = $donnees_avis['total_entrees'];

	// S'il n'y a aucun avis, proposer d'en écrire un
	if ($total_entrees == 0) {
		$avis_concat.= '
			<div class="head_detail_event_bloc_2">
				<div class="top_left_bloc_donner_avis"></div>
				<span class="ico_float_droite">
					<a href="-Donnez-votre-avis-?id_event=' . $id_event . '"><img src="agenda/design_pics/bouton_donnez_avis.jpg"  vspace="4"/></a>
				</span>
				<span class="titre_paragr_detail_event" style="width:440px; ">Vos avis sur "' .$donnees['nom_event'] . '"</span>
			</div>
		
			<br />
			<div align="center" style="font-size:13px">
				<b><a href="-Donnez-votre-avis-?id_event=' . $id_event . '">&gt;&gt; Aucun lecteur n\'a encore fait part de son avis, soyez le premier !! &lt;&lt; </a></b>
			</div>
			<br />'."\n\n" ;  		
	}
/*	YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY
	// s'il n'existe qu'un avis et que celui-ci est court, ne pas afficher le lien "Lire la suite" c'est enlevé
	YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY */
	else	
	{			
		$avis_concat.= '
			<div class="head_detail_event_bloc_2">
				<div class="top_left_bloc_avis"></div>
				<span class="nombre_avis">' . $total_entrees .'</span>
				<span class="titre_paragr_detail_event" style="width:440px; ">Il y a ' . $total_entrees .' avis sur "' .$donnees['nom_event'] . '"</span>
				<span class="ico_float_droite">
					<a href="-Donnez-votre-avis-?id_event=' . $id_event . '"><img src="agenda/design_pics/bouton_donnez_avis.jpg"  vspace="4"/></a>
					<a href="agenda/spip/print_avis_event.php?id_event=' . $id_event . '" target="_blank" title="Imprimer les avis des lecteurs">
						<img src="agenda/design_pics/ico_impr.jpg"  hspace="10" title="Imprimer les avis des visiteurs" />
					</a>
				</span>	
			</div>'."\n\n";

		$avis_concat.= '<div class="detail_corps_event">';			

		// Afficher tous les avis (au complet)(Avant, c'était 1, puis 4) On se limitera à 25 quand même
		$reponse_avis= mysql_query('SELECT * FROM '.$table_avis_agenda.' WHERE event_avis IN ('.$t_saison_preced.') AND publier_avis=\'set\' ORDER BY id_avis DESC');
		while ($donnees_avis = mysql_fetch_array($reponse_avis))
		{		
			//$debut_avis = strip_tags($donnees_avis['texte_avis']) ;
			// -----------------------------------------------------------------------------------------------
			/* Si l'auteur du message est un SPECTATEUR enregistré sur le site 
			ET muni d'un compte en ordre, afficher un lien vers sa page "détail" */
			// -----------------------------------------------------------------------------------------------
			$nom_pseudo_comp = $donnees_avis['nom_avis'] ;				
			$reponse_spectateur = mysql_query("SELECT * FROM ag_spectateurs WHERE compte_actif_spectateur = 'oui' AND pseudo_spectateur = '$nom_pseudo_comp'");
			$donnees_spectateur = mysql_fetch_array($reponse_spectateur) ;
			if (!empty($donnees_spectateur['id_spectateur']))
			{
				$id_spectateur = $donnees_spectateur['id_spectateur'] ;
				$pseudo_spectateur = $donnees_spectateur['pseudo_spectateur'] ;
				
				$avis_valides_spectateur = $donnees_spectateur['avis_valides_spectateur'];
	    		$_tot_entrees_pour_lui = connaitre_nb_avis_spect ($pseudo_spectateur) ;
				$result_categorie_spectateur = trouve_categorie_spectateur ($_tot_entrees_pour_lui) ; 

				$qui_spectateur = '<a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '"
				title="Cliquez pour voir le profil de ce spectateur" >' . $pseudo_spectateur . '</a>' ;
				
				$qui_spectateur.= '<span class="help_cursor"><img src="agenda/design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /></span> 
				<span title="Totalité des avis déposés" class="help_cursor"> ' . $_tot_entrees_pour_lui . '</span>'; 
			}
			else
				$qui_spectateur = $donnees_avis['nom_avis'] ;

			$avis_concat.= '<span class="nom_avis">' . $qui_spectateur . '</span> 
			<span class="date_avis">' .date('d/m/Y - H\hi', $donnees_avis['t_stamp_avis']) . '</span>
			<span class="id_breve">(id  :' . $donnees_avis['id_avis'] . ')</span> <br /> <br />' ;
			
			$avis_concat.= $donnees_avis['texte_avis'] . ' <br /> <br /> ' ;
		}
		$avis_concat.= '<div align="right">' ;
		$avis_concat.= '<a href="-Donnez-votre-avis-?id_event=' . $id_event . '">
		<img src="agenda/design_pics/bouton_donnez_avis.jpg" vspace="6"/>
		</a>
		</div>'."\n" ; //--- align right
		
		$avis_concat.=  '<br style="clear:both;" />
		</div>'."\n" ; //--- fin detail_corps_event
	}
	echo $avis_concat."\n";
	echo '<br style="clear:both;" />
	</div>',"\n"; //--- fin ongletevent avis

/*	____________________________________________
		ONGLET CRITIQUE
	____________________________________________ */
	echo '<div class="ongletevent">',"\n";

	echo 'critique <br style="clear:both;" />
	</div>',"\n"; //--- fin ongletevent critique

/*	____________________________________________
		ONGLET VIDEO
	____________________________________________ */
	echo '<div class="ongletevent">',"\n";

	echo 'video <br style="clear:both;" />
	</div>',"\n"; //--- fin ongletevent video

/*	____________________________________________
		ONGLET MAPPING
	____________________________________________ */
	echo '<div class="ongletevent">',"\n";

	echo 'mapping <br style="clear:both;" />
	</div>',"\n"; //--- fin ongletevent mapping



	// ____________________________________________
	// Affichage lien retour au listing
	if (isset($_SERVER['HTTP_REFERER']) AND $_SERVER['HTTP_REFERER'] != NULL AND preg_match('-Agenda-', $_SERVER['HTTP_REFERER']))
		$lien_retour_moteur = $_SERVER['HTTP_REFERER'];
	else
		$lien_retour_moteur = '-Agenda-';
	echo '<div class="lien_retour_public"><a href="'.$lien_retour_moteur.'"><img src="agenda/design_pics/loupe_fond_ec.jpg" align="absmiddle" /> Retour à la recherche</a></div>',"\n";
?>
