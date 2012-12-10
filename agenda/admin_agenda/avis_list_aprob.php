<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Avis &agrave; approuver par le mod&eacute;rateur</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">

<SCRIPT language="JavaScript"><!--   //
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
//--></SCRIPT>
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Voici les avis qui n'ont pas encore été autorisés par le modérateur</h1>

<div class="menu_back"><a href="avis_list_aprob.php">Actualiser la page</a> |
<a href="avis_list_aprob.php?affichage=complet">Affichage complet</a> | 
<a href="#legende">Légende des symboles</a> | 
<a href="#abonnes">Abonn&eacute;s aux publications</a> | 
<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>


<p>Les avis s'empilent  par ordre d'arriv&eacute;e afin  de suivre le fil naturel de la discussion. Il est imp&eacute;ratif  de &laquo;&nbsp;nettoyer&nbsp;&raquo; les messages au fur et &agrave; mesure avec les outils dont  la l&eacute;gende figure au bas de la page. <br>
Je fais aussi appara&icirc;tre dans le tableau la liste des  adresses e-mail auxquelles serait envoy&eacute; l&rsquo;AVIS (quand tu l'envois avec l&rsquo;ic&ocirc;ne MAILING). Tu peux ainsi notamment contr&ocirc;ler que la personne qui a  post&eacute; un avis ne le recevra pas, ou encore voir qu&rsquo;il y a bien corr&eacute;lation avec  la&nbsp;&laquo;&nbsp;Liste des abonn&eacute;s aux publications&nbsp;&raquo; reprise en bas de  page.</p>
<p>

  <?php
require '../inc_var.php';
require '../inc_fct_base.php';
require '../inc_db_connect.php';
require 'avis_emailing.php';
require 'avis_refus_mailto.php';
require 'avis_send_mail_info_1_point.php';

$td_color_refus = '#FFBBBB' ; // Couleur de case si REFUS de publier un avis

		
//----------------------------------------------
// Flags de "flags_avis" : 
// "mailing" si avis envoyé à mailing liste
// "check" si le modérateur a checké. Utile pour lui afficher uniquement les messages non vus. Ce flag influence uniquement l'affichage dans la liste d'admistration.

// Si le champ "publier_avis" = 'non' => c'est une désapprobation


/*// --------------------------------------------------
// Récupération du nom du ce spectacle
$reponse = mysql_query("SELECT nom_event FROM $table_evenements_agenda WHERE id_event = '$id_event'");
$donnees_event = mysql_fetch_array($reponse);	
$nom_event = $donnees_event ['nom_event'];*/


// --------------------------------------------------
// Faut-il sortir un avis de la liste des avis non checkés ET rajouter 1 point au Spectateur ?
if (isset ($_GET['id_aprob_add_1']) AND $_GET['id_aprob_add_1'] != NULL )
{
	//echo '<br>UNO -------------------------------------------------------<br>' ;
	$sql_check_1 = '' ;
	$sql_check_2 = '' ;
	$id_aprob_add_1 = htmlentities($_GET['id_aprob_add_1'], ENT_QUOTES);
	
	$reponse_flags_avis_modif = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_aprob_add_1' ");
	$donnees_flags_avis = mysql_fetch_array($reponse_flags_avis_modif) ;
	$flags_avis_modif = $donnees_flags_avis ['flags_avis'] ;
		
	// essai : Vérifier que la valeur "check" n'est pas déjà présente
	$test_flag_reponse = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_aprob_add_1' ");
	$test_flag = mysql_fetch_array($test_flag_reponse) ;
	$test_flag = $test_flag['flags_avis'] ;
	$pseudo_spectateur = $donnees_flags_avis['nom_avis'] ; // Utile ensuite pour rajouter le point 
	//var_dump ($test_flag) ;
	$test_flag = explode(",",$test_flag);
	
	$key = array_search('check', $test_flag);    // $key = ...;
	if ($key)
	{ 
		echo '<div class="alerte">L\'avis [ ' . $id_aprob_add_1 . ' ] est déjà marqué comme "vu" (checked)</div>' ; 
	}
	else
	{
		// Faire passer le drapeau à "check"
		array_push ($test_flag, "check");
		$test_flag = implode(",",$test_flag);

		$sql_check_1 = mysql_query("UPDATE $table_avis_agenda SET flags_avis = '$test_flag' 
		WHERE id_avis = '$id_aprob_add_1' LIMIT 1 ") ;
		if ($sql_check_1) 
		{
			echo '<div class="info">L\'avis [ ' . $id_aprob_add_1 . ' ] est sorti de la liste des "avis non lus"</div>' ; 
		}
		
		// Rajouter 1 point au Spectateur :
		// Qui est le spectateur (trouver "id_spectateur")
		$reponse_qui_spectateur = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE pseudo_spectateur = '$pseudo_spectateur'");
		$donnees_qui_spectateur = mysql_fetch_array($reponse_qui_spectateur);
		$prenom_spectateur = $donnees_qui_spectateur ['prenom_spectateur'];
		$nom_spectateur = $donnees_qui_spectateur ['nom_spectateur'];
		$id_spectateur = $donnees_qui_spectateur ['id_spectateur'];
		//$prenom_et_nom_spect = $donnees_qui_spectateur ['prenom_spectateur'] . ' ' . $donnees_qui_spectateur ['nom_spectateur']; // Pour fonction info par email

		// Si la valeur ne correspond à aucune entrée de la TABLE :
		if (empty ($donnees_qui_spectateur))
		{
			echo '<div class="alerte">Le rédacteur <strong>' . $pseudo_spectateur . '</strong> ne fait pas partie de la Communauté des Spectateurs</div><br />' ;
		}
		else
		{
			$sql_check_2 = mysql_query("UPDATE $table_spectateurs_ag SET avis_valides_spectateur=avis_valides_spectateur+1 
			WHERE pseudo_spectateur = '$pseudo_spectateur' LIMIT 1 ") or die(" erreur sql_check_2 " . mysql_error()); ;
			if ($sql_check_2) 
			{
				echo '<div class="info">Le rédacteur <strong>' . $pseudo_spectateur . '</strong> 
				a reçu 1 point supplémentaire</div>' ; 
			}
			/* Informer le Spectateur qu'il a reçu 1 point */
			avis_info_1_point_send_mail ($id_aprob_add_1, $id_spectateur) ; // = fonction d'envoi de l'e-mail info "plus 1 point"
		}
	}	
}

// --------------------------------------------------
// Faut-il sortir un avis de la liste des avis non checkés SANS rajouter 1 point au Spectateur ?
if (isset ($_GET['id_aprob_add_0']) AND $_GET['id_aprob_add_0'] != NULL )
{
	//echo '<br>ZERO -------------------------------------------------------<br>' ;
	$sql_check = '' ;
	$id_aprob_add_0 = htmlentities($_GET['id_aprob_add_0'], ENT_QUOTES);
	
	$reponse_flags_avis_modif = mysql_query("SELECT flags_avis FROM $table_avis_agenda WHERE id_avis = '$id_aprob_add_0' ");
	$donnees_flags_avis = mysql_fetch_array($reponse_flags_avis_modif) ;
	$flags_avis_modif = $donnees_flags_avis ['flags_avis'] ;
		
	
	// essai : Vérifier que la valeur "check" n'est pas déjà présente
	$test_flag_reponse = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_aprob_add_0' ");
	$test_flag = mysql_fetch_array($test_flag_reponse) ;
	$test_flag = $test_flag['flags_avis'] ;
	
	$test_flag = explode(",",$test_flag);
	
	$key = array_search('check', $test_flag);    // $key = ...;
	if ($key)
	{ 
		echo '<div class="alerte">L\'avis [ ' . $id_aprob_add_0 . ' ] est déjà marqué comme "vu" (checked)</div>' ; 
	}
	else
	{
		// Faire passer le drapeau à "check"
		array_push ($test_flag, "check");
		$test_flag = implode(",",$test_flag);

		$sql_check_3 = mysql_query("UPDATE $table_avis_agenda SET flags_avis = '$test_flag' 
		WHERE id_avis = '$id_aprob_add_0' LIMIT 1 ") ;
		if ($sql_check_3) 
		{
			echo '<div class="info">L\'avis [ ' . $id_aprob_add_0 . ' ] est sorti de la liste des "avis non lus"</div>' ; 
		}	
	}	
}

// --------------------------------------------------
// Faut-il EFFACER un avis ?
if (isset ($_GET['avis_effacer']) AND $_GET['avis_effacer'] != NULL )
{
	$avis_effacer = htmlentities($_GET['avis_effacer'], ENT_QUOTES);
	$delete_check = mysql_query("DELETE FROM $table_avis_agenda WHERE id_avis = '$avis_effacer'") or die($query_count . " ----- " . mysql_error());
	
	if ($delete_check)
	{ echo '<div class="info"> Effacement réussi</div>' ; }
}


// --------------------------------------------------
// Faut-il APPROUVER un avis ?
if (isset ($_GET['avis_approuver']) AND $_GET['avis_approuver'] != NULL )
{
	$avis_approuver = htmlentities($_GET['avis_approuver'], ENT_QUOTES);
	
	$approuv_check = mysql_query("UPDATE $table_avis_agenda SET publier_avis = 'set' 
	WHERE id_avis = '$avis_approuver' LIMIT 1 ") ;
	if ($approuv_check) { echo '<div class="info"> Approbation effectuée</div>' ; }
}

// --------------------------------------------------
// Faut-il lancer un e-mailing à la liste des inscrits pour cet avis ?
// Tester avant s'il s'agit de la première publication.
// --------------------------------------------------
if (isset ($_GET['id_mailing']) AND $_GET['id_mailing'] != NULL )
{
	$sql_check_4 = '' ;
	$id_mailing = htmlentities($_GET['id_mailing'], ENT_QUOTES);
	
	$reponse_flags_avis_modif = mysql_query("SELECT flags_avis FROM $table_avis_agenda WHERE id_avis = '$id_mailing' ");
	$donnees_flags_avis = mysql_fetch_array($reponse_flags_avis_modif) ;
	$flags_avis_modif = $donnees_flags_avis ['flags_avis'] ;
		
	
	// Vérifier que la valeur "mailing" n'est pas déjà présente
	$test_flag_reponse = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_mailing' ");
	$test_flag = mysql_fetch_array($test_flag_reponse) ;
	$test_flag = $test_flag['flags_avis'] ;
	
	$test_flag = explode(",",$test_flag);
	
	$key = array_search('mailing', $test_flag);    // $key = ...;
	if ($key)
	{ 
		echo '<div class="alerte">L\'avis [ ' . $id_mailing . ' ] est déjà été envoyé à la mailing list (mailing)</div>' ; 
	}
	else
	{ 
		// Appel de la fonction d'envoi de l'e-mailing
		if (avertir_avis_listing ($id_mailing))
		{
			array_push ($test_flag, "mailing");
			$test_flag = implode(",",$test_flag);
	
			$sql_check_4 = mysql_query("UPDATE $table_avis_agenda SET flags_avis = '$test_flag' WHERE id_avis = '$id_mailing' LIMIT 1 ") ;
			if ($sql_check_4) 
			{
				echo '<div class="info">L\'avis [ ' . $id_mailing . ' ] a bien été envoyé aux abonnés"</div>' ; 
			}

		}
		else
		{
			echo '<div class="alerte">ERREUR : l\'avis [ ' . $id_mailing . ' ] n\'a pas été envoyé aux abonnés</div>' ; 
		}
	}
}


// --------------------------------------------------
// Mettre l'avis hors ligne et envoyer un e-mailing de notification de REFUS de publication
// --------------------------------------------------
if (isset ($_GET['id_refus']) AND $_GET['id_refus'] != NULL )
{
	$id_refus = htmlentities($_GET['id_refus'], ENT_QUOTES);

	$sql_check_5 = '' ;
	$sql_check_5 = mysql_query("UPDATE $table_avis_agenda SET publier_avis = '' 
	WHERE id_avis = '$id_refus' LIMIT 1 ") ;
	if ($sql_check_5) { echo '<div class="info">L\'avis ' .$id_refus . ' n\'est plus en ligne.</div>' ; }
			
	$reponse_flags_avis_modif = mysql_query("SELECT flags_avis FROM $table_avis_agenda WHERE id_avis = '$id_refus' ");
	$donnees_flags_avis = mysql_fetch_array($reponse_flags_avis_modif) ;
	$flags_avis_modif = $donnees_flags_avis ['flags_avis'] ;
		
	// Vérifier que la valeur "refus" n'est pas déjà présente
	$flags_avis_array = explode(",", $flags_avis_modif);
	
	if (!in_array('refus', $flags_avis_array))
	{
		avis_refus_send_mail ($id_refus) ; // = fonction d'envoi de l'e-mail de refus
	}
}



// ************************************************************************
// Affichage des AVIS pour le modérateur
// ************************************************************************

$avis_concat = '' ;

// EN TETE TABLE
$avis_concat.='<table width="750" border="1" align="center" cellpadding="10" cellspacing="0" class="data_table" >
  <tr>
	<th>Infos visiteur</th>
	<th>Message</th>
	<th>Administration</th>
  </tr>' ;
  
if (isset ($_GET['affichage']) AND $_GET['affichage'] == 'complet')
{
	// ************** AFFICHAGE COMPLET DES AVIS ************** 
	$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda ORDER BY id_avis DESC");
}
else
{
	//  ************** AFFICHER UNIQUEMENT LES AVIS NON VUS ************** 
	$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE flags_avis NOT REGEXP 'check' ORDER BY id_avis");
}

while ($donnees_avis = mysql_fetch_array($reponse_avis))
{
	$flags_avis = $donnees_avis ['flags_avis'];
	$flags_avis_array = explode(",", $flags_avis);
	
	$avis_concat.= '<tr class="tr_hover"><td width="200" valign="top">
	<a name="ancre' . $donnees_avis['id_avis'] . '" id="ancre' . $donnees_avis['id_avis'] . '"></a>
	
	<ul>
	<li><b>Avis numéro : </b>' . $donnees_avis['id_avis'] . '</li>
	<li><b>Nom : </b>' . $donnees_avis['nom_avis'] . '</li>
	<li><b>e-mail : </b><a href="mailto:' . $donnees_avis['email_avis'] . '">' . $donnees_avis['email_avis'] . '</a></li>
	<li><b>Date : </b>' . date('d/m/Y à H\hi', $donnees_avis ['t_stamp_avis']) . '</li>';

	// Récupération du nom du ce spectacle
	$event_avis = $donnees_avis['event_avis'] ;
	$id_avis = $donnees_avis['id_avis'] ;
	
	$reponse = mysql_query("SELECT nom_event FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
	$donnees_event = mysql_fetch_array($reponse);
	

	
	$avis_concat.= '<li><b>Evenement : </b><i>' . $donnees_event ['nom_event'] . '</i>(id ' . $event_avis . ')</li>
	<li><b>IP : </b>' . $donnees_avis['ip_avis'] . '</li>
	</ul></td>
	
	<td valign="top"' ;
	 	
	// Colorer la case si REFUS de publier un avis
	if (in_array('refus', $flags_avis_array)) { $avis_concat.= 'bgcolor="'.$td_color_refus.'"' ;}
	$avis_concat.='><div align="justify">' . stripslashes($donnees_avis['texte_avis']) . '</div></td>
	
	<td valign="top" align="center">';

	
	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
	/* Bouton : Checker l'AVIS pour enlever de la liste des NON VUS et rajouter 1 point au Spectateur */
	if (!in_array('check', $flags_avis_array))
	{
		$avis_concat.= '<a href="avis_list_aprob.php?id_aprob_add_1='. $donnees_avis['id_avis'] . '">
		<img src="../design_pics/bouton_checked_add_1.gif"  hspace="3" title="Retirer de la liste des nouveaux AVIS et rajouter 1 point au Spectateur " ></a>' ;
	}

	

	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
	/* Checker l'AVIS pour enlever de la liste des NON VUS SANS rajouter 1 point au Spectateur */
	if (!in_array('check', $flags_avis_array))
	{
		$avis_concat.= '<a href="avis_list_aprob.php?id_aprob_add_0='. $donnees_avis['id_avis'] . '">
		<img src="../design_pics/bouton_checked_add_0.gif"  hspace="3" title="Retirer de la liste des nouveaux AVIS SANS rajouter 1 point au Spectateur " ></a>' ;
	}



	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
	// ------------------------- ENVOYER A LA MAILING LISTE -------------------------
	//echo $donnees_avis['id_avis'] . '<br>';
	$liste_voulant_recevoir = '' ;
	if (!in_array('mailing', $flags_avis_array) AND !in_array('refus', $flags_avis_array))
	{	
		$test_numero_avis = mysql_query("SELECT * FROM $table_avis_mailing WHERE event_avis_mailing = '$event_avis' 
		AND numero_avis != '$id_avis'");
		while ($donnees_test_numero_avis = mysql_fetch_array($test_numero_avis))
		{
			$liste_voulant_recevoir.= $donnees_test_numero_avis ['avis_mailing_adresse'] . '<br />' ;
		}
			
		if ($liste_voulant_recevoir != '')
		{
			$liste_voulant_recevoir = '<div class="mini"> Informer : <br /> ' . $liste_voulant_recevoir . '</div>';

			// Utiliser ID à partir de 100000
			// *** Icone approuver ***
			$zone = 'Zone' . ($id_avis + 100000) ;
			$zoneB = 'Zone' . ($id_avis + 100000) . 'B' ;
			$ZoneLink = 'Zone' . ($id_avis + 100000) . 'Link' ;
		
			$avis_concat.= '<div id="'.$zone.'" style="display:inline;"><a href="javascript:toggle_zone(';
			$avis_concat.= "'$zone','Afficher la suite','Replier'); " ;
			$avis_concat.= '" id="' . $ZoneLink . '">
			<img src="../design_pics/bouton_poster_mailing.gif" hspace="3" title="Envoyer cet AVIS à la mailing list Et donner 1 point au rédacteur" ></a></div>' ;
			
			// *** Confirmer Envoyer ***
			$avis_concat.= '<div id="'.$zoneB.'" style="display:none;"><div class="alerte_data_table">
			<a href="avis_list_aprob.php?id_aprob_add_1='. $donnees_avis['id_avis'] . '&amp;id_mailing='. $donnees_avis['id_avis'] . '">
			Envoyer cet AVIS à la mailing list ?</a></div>
			<a href="javascript:toggle_zone(' ;
			$avis_concat.= "'$zone','Afficher la suite','Replier'); " ;
			$avis_concat.= '" id="' . $ZoneLink . '"><div class="info_data_table">Annuler</div></a></div> ' ;
			
		}		
	}


	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
	// ------------------------- METTRE HORS LIGNE ET ENVOYER E-MAIL REFUS DE PUBLICATION------------------------- 
	// Utiliser ID à partir de 200000
	// *** Icone approuver ***
	
	if (!in_array('refus', $flags_avis_array)) 
	{
		$zone = 'Zone' . ($id_avis + 200000) ;
		$zoneB = 'Zone' . ($id_avis + 200000) . 'B' ;
		$ZoneLink = 'Zone' . ($id_avis + 200000) . 'Link' ;
	
		$avis_concat.= '<div id="'.$zone.'" style="display:inline;"><a href="javascript:toggle_zone(';
		$avis_concat.= "'$zone','Afficher la suite','Replier'); " ;
		$avis_concat.= '" id="' . $ZoneLink . '">
		<img src="../design_pics/bouton_signifier_refus.gif" hspace="3" title="Envoyer un e-mail notifiant le refus de publication de l\'avis" ></a></div>' ;
		
		// *** Confirmer Envoyer ***
		$avis_concat.= '<div id="'.$zoneB.'" style="display:none;"><div class="alerte_data_table">
		<a href="avis_list_aprob.php?id_refus='. $donnees_avis['id_avis'] . '">
		Voulez-vous envoyer la notification de refus de publication ?</a></div>
		<a href="javascript:toggle_zone(' ;
		$avis_concat.= "'$zone','Afficher la suite','Replier'); " ;
		$avis_concat.= '" id="' . $ZoneLink . '"><div class="info_data_table">Annuler</div></a></div> ' ;
	}

	

	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
	// ------------------------- MODIFIER ------------------------- 
	$avis_concat.= '<a href="avis_edit.php?id_avis='. $donnees_avis['id_avis'] . '">
	<img src="../design_pics/bouton_edit.gif"  hspace="3" title="Modifier cet avis" ></a>' ;
			


	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
	// ------------------------- EFFACER ------------------------- 
	// *** Icone effacer ***
	$zone = 'Zone' . $id_avis ;
	$zoneB = 'Zone' . $id_avis . 'B' ;
	$ZoneLink = 'Zone' . $id_avis . 'Link' ;

	$avis_concat.= '<div id="'.$zone.'" style="display:inline;"><a href="javascript:toggle_zone(';
	$avis_concat.= "'$zone','Afficher la suite','Replier'); " ;
	$avis_concat.= '" id="' . $ZoneLink . '">
	<img src="../design_pics/bouton_delete.gif" hspace="3" title="Effacer cet avis" ></a></div>' ;
	
	// *** Icone confirmer effacer ***
	$avis_concat.= '<div id="'.$zoneB.'" style="display:none;"><div class="alerte_data_table">
	<a href="avis_list_aprob.php?avis_effacer='. $donnees_avis['id_avis'] . '">
	Voulez-vous EFFACER cet AVIS ?</a></div>
	<a href="javascript:toggle_zone(' ;
	$avis_concat.= "'$zone','Afficher la suite','Replier'); " ;
	$avis_concat.= '" id="' . $ZoneLink . '"><div class="info_data_table">Annuler</div></a></div> ' ;
	

	$avis_concat.= $liste_voulant_recevoir;

	$avis_concat.= '</td></tr>' ;

}
$avis_concat.= '</table> <br />' ;
echo $avis_concat ;	

?>
  
  
<p><a name="legende"></a> </p>
<p><br>
  <img src="../design_pics/bouton_checked_add_1.gif" width="42" height="14"> : Cliquer pour enlever de la liste des nouveaux AVIS et ajouter 1 point en plus au Spectateur. Un email est envoy&eacute; au spectateur pour lui dire qu'il a re&ccedil;u 1 point. Le message est ensuite retir&eacute; de la liste ci dessus.<br>
  <img src="../design_pics/bouton_checked_add_0.gif" width="42" height="14"> : Cliquer pour enlever de la liste des nouveaux AVIS. Sert uniquement &agrave; la visualisation c&ocirc;t&eacute; admin.<br>
  <img src="../design_pics/bouton_poster_mailing.gif" width="32" height="14"> : Cliquer pour envoyer cet avis &agrave; la liste des abonn&eacute;s.<br>
  <img src="../design_pics/bouton_signifier_refus.gif" width="27" height="14"> :  Cliquer pour retirer l'avis du site et envoyer un email notifiant le refus de publier l'avis. L'icone <img src="../design_pics/bouton_poster_mailing.gif" width="32" height="14"> sera enlev&eacute;e <br>
  <img src="../design_pics/bouton_edit.gif" width="20" height="14"> : Cliquer pour &eacute;diter l'avis.<br>
<img src="../design_pics/bouton_delete.gif" width="15" height="14"> : Cliquer pour effacer l'avis de la base de donn&eacute;es.<br>
<em>((<img src="../design_pics/bouton_checked.gif" width="33" height="14"> : Cliquer pour enlever de la liste des nouveaux AVIS. Sert uniquement &agrave; la visualisation c&ocirc;t&eacute; admin. N'EXISTE PLUS !!!!!! ))</em></p>
<a name="abonnes"></a><br />



<!-- ****************************************************************************************************************** -->
<!-- ****************************************************************************************************************** -->
			<hr><h2>Liste des abonn&eacute;s aux publications :</h2>
<!-- ****************************************************************************************************************** -->
<!-- ****************************************************************************************************************** -->
<?php
echo '<ul class="mini">' ;

$reponse_mailing = mysql_query("SELECT * FROM $table_avis_mailing ORDER BY event_avis_mailing DESC");
while ($donnees_mailing = mysql_fetch_array($reponse_mailing))
{
	echo '<li>Event <b>' . $donnees_mailing['event_avis_mailing'] . '</b> - ' . $donnees_mailing['avis_mailing_adresse'] . ' </li>' ;
}
echo '</ul>';


?>

<?PHP
//--- mysql_close($db2dlp); //--- ajouter ressource sinon plantage apache/mysql status 3221225477
?>

<p>&nbsp;</p>
</body>
</html>