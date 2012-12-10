<style type="text/css">
<!--
div.breve ul {
	font-size: 10px;
	margin-left: 60px;
}
-->
</style>

<a href="-Communaute-des-spectateurs-"> &lt;&lt; Retour à la communauté des spectateurs</a>

<?php
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des Spectateurs qui ont ouvert un compte sur DLP
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

if (!empty ($_GET['id_spect']) AND $_GET['id_spect'] != NULL )
{
	$id_spectateur = htmlentities($_GET['id_spect'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id_spect'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>
		<a href="index.php" >Retour</a></div>' ;
		exit () ;
	}
}
else
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="index.php" >Retour</a></div>' ;
	exit () ;
}
	
$tab =' ';

$prenom_spectateur = $donnees ['prenom_spectateur'];
$nom_spectateur = $donnees ['nom_spectateur'];
$sexe_spectateur = $donnees ['sexe_spectateur'];
$pseudo_spectateur = $donnees ['pseudo_spectateur'];
$e_mail_spectateur = $donnees ['e_mail_spectateur'];
$tel_spectateur = $donnees ['tel_spectateur'];
$log_spectateur = $donnees ['log_spectateur'];
$pw_spectateur = $donnees ['pw_spectateur'];

$description_courte_spectateur = $donnees ['description_courte_spectateur'];
$description_longue_spectateur = $donnees ['description_longue_spectateur'];

$pic_spectateur = $donnees ['pic_spectateur'];

$artiste_prefere_spectateur = $donnees ['artiste_prefere_spectateur'];
$lieu_prefere_spectateur = $donnees ['lieu_prefere_spectateur'];

$compte_actif_spectateur = $donnees ['compte_actif_spectateur'];
$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];

$tab.= '<div class="breve">' ;
// ____________________________________________
// PHOTO SPECTATEUR
$id_spectateur = $donnees ['id_spectateur'] ;
$tab.= '<span class="breve_pic">' ;


// Afficher image spectateur
if (isset ($donnees ['pic_spectateur']) AND $donnees ['pic_spectateur'] == 'set' )
{
	$tab.= '<img src="agenda/' . $folder_pics_spectateurs . 'spect_' . $id_spectateur . '_1.jpg" alt="Photo de ' . $pseudo_spectateur . '" title="' . $pseudo_spectateur . '" />';
}
else
{
	if ($donnees ['sexe_spectateur'] == 0)
	{
		$tab.= '<img src="agenda/' . $folder_pics_spectateurs . 'spect_anonyme_homme.jpg" alt="spectateur anonyme" />';
	}
	else
	{
		$tab.= '<img src="agenda/' . $folder_pics_spectateurs . 'spect_anonyme_femme.jpg" alt="spectatrice anonyme" />';
	}
}			
$tab.= '</span>' ;
// ____________________________________________
// PSEUDO (NOM - PRENOM) - GRADE - NOMBRE AVIS

$prenom_spectateur = $donnees['prenom_spectateur'] ;// Raccourcir la chaine :
$max=20; // Longueur MAX de la chaîne de caractères
$chaine_raccourcie = raccourcir_chaine ($prenom_spectateur,$max); // retourne $chaine_raccourcie
 
$tab.= '<span style="font-size: 15px"><strong><a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '" title="voir le profil de ce spectateur" >' . $pseudo_spectateur . '</a></strong></span>' ;

// AFFICHER ID (DISCRETEMENT)
$tab.= '<span class="id_breve">(ID' . $id_spectateur . ')</span>';

// Nombre d'avis déposés par ce spectateur :
$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur'");
$donnees_3 = mysql_fetch_array($retour_3);
$_tot_entrees = $donnees_3['nbre_entrees'];

// Correspondance AVIS postés <-> Grade et icone des spectateurs
$result_categorie_spectateur = trouve_categorie_spectateur ($_tot_entrees) ; 

$tab.= '<br /><span style="font-size: 13px">' . $result_categorie_spectateur['categorie_spectateur'] . ' </span> 
<span class="help_cursor">
<img src="agenda/design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /></span></br />'; 


// Nombre total des avis pour ce spectateur :
$tab.= '<span style="font-size: 11px">
<strong> ' . $_tot_entrees . '</strong> avis déposé(s) actuellement</span><br />' ;

// Score pour la saison actuelle / Total :
$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
$tab.= '<span style="font-size: 11px">
<strong> ' . $avis_valides_spectateur . '</strong> avis déposé(s) pour cette saison</span><br />' ;



// Appel fonction correspondance AVIS <-> CHANCE
$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; 
$tab.= '<span style="font-size: 11px">
Coefficient concours : <strong>X ' . $result_fact_chance['valeur_facteur_chance'] . '
</strong></span><br /> <br />' ;


// DATE DE NAISSANCE DU SPECTATEUR
if (isset($donnees ['date_naissance_spectateur']) AND ($donnees ['date_naissance_spectateur'] != '0000-00-00'))
{		
	$AAAA_spectateur = substr($donnees ['date_naissance_spectateur'], 0, 4);
	$MM_spectateur = substr($donnees ['date_naissance_spectateur'], 5, 2);	
	$JJ_spectateur = substr($donnees ['date_naissance_spectateur'], 8, 2);
	$tab.= '<span style="font-size: 11px">Date de naissance : ' . $JJ_spectateur . '/'.$MM_spectateur .'/'. $AAAA_spectateur .'</span><br />';
}
	

// DESCRIPTION LONGUE SPECTATEUR
/*$tab.= '<h4>Ma description rapide : </h4> <br />' . $description_courte_spectateur . ' <br /> <br /> ' ;*/


// DESCRIPTION LONGUE SPECTATEUR
$tab.= '<h4>Description : </h4> ' . $description_longue_spectateur . '<div class="float_stop"> <br /> </div>' ;


// THEATRE PREFERES
if ($lieu_prefere_spectateur != '')
{
	$tab.= '<strong>Lieu(x) culturel(s) favori(s)</strong> : ' . $lieu_prefere_spectateur . '<br />' ;
}

// ARTISTES PREFERES
if ($artiste_prefere_spectateur != '')
{
	$tab.= '<strong>Artiste(s) apprécié(s)</strong> : ' . $artiste_prefere_spectateur . '<br />' ;
}	


// email spectateur --- 
//--- richir $tab.= '<div align="right"><a href="mailto:' . $e_mail_spectateur . '?subject=Via%20le%20site%20demandezleprogramme.be">
$tab.= '<div align="right"><a href="Envoyer-un-message?id_spect='.$id_spectateur.'&amp;pseudo='.rawurlencode(html_entity_decode($pseudo_spectateur)).'" target="_blank">
<img src="agenda/design_pics/ecrire-a-ce-membre.gif" />
</a></div>';


// Derniers spectacles évalués :
if ($_tot_entrees = $donnees_3['nbre_entrees'] > 0)
{
	$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur' AND publier_avis = 'set' ORDER BY id_avis DESC LIMIT 100");
	
	$tab.= '<br /><strong>Derniers événements évalués</strong> : <ul style="margin:0 0 0 0;">' ;
	while ($donnees_avis = mysql_fetch_array($reponse_avis))
	{	
		$event_avis = $donnees_avis['event_avis'] ; // Récupération du nom du ce spectacle
		$nom_avis = $donnees_avis['nom_avis'] ;
		$reponse_spectacle = mysql_query("SELECT nom_event FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
		$donnees_spectacle = mysql_fetch_array($reponse_spectacle);
		
		$tab.= '<li><a href="-Detail-agenda-?id_event=' . $event_avis . '#avis" title="Voir son avis">' . $donnees_spectacle ['nom_event'] . '</a> 
		(' . date('d/m/Y', $donnees_avis ['t_stamp_avis']) . ')</li>';	
	}
	$tab.= '</ul>' ;
}
else
{
	$tab.= '<br /> <em>' . $pseudo_spectateur . ' n\'a encore écrit aucun avis</em> <br />' ;
}
	
$tab.= '</div>' ;


echo $tab . ' '  ;

?>


