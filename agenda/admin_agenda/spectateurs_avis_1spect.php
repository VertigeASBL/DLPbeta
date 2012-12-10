<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Avis publi&eacute;s par 1 spectateur</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">


</head>
<body>

<div id="head_admin_agenda"></div>

<?php

require '../inc_var.php';
require '../inc_fct_base.php';
require '../inc_db_connect.php';
require 'avis_emailing.php';
require 'avis_refus_mailto.php';

$td_color_refus = '#BBBBBB' ; // Couleur de case si REFUS de publier un avis
$avis_concat = '' ;

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// ************** AFFICHAGE DES AVIS PUBLIES PAR 1 SPECTATEUR ************** 
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

//---------------------------------------------------------
// Test sur variable GET :
//---------------------------------------------------------
//L'entrée donnée par GET existe-t-elle :
if (empty ($_GET['spect']) OR $_GET['spect'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="spectateurs_listing.php">Retour</a></div>' ;
	exit();
}
else
{
	$id_spectateur = htmlentities($_GET['spect'], ENT_QUOTES);
}


// Quel est le PSEUDO du spectateur ? nécessaire pour obtenir la correspondance entre tables
$reponse_spectat = mysql_query("SELECT * FROM ag_spectateurs WHERE id_spectateur = $id_spectateur ");
$donnees_spectat = mysql_fetch_array($reponse_spectat) ;

$pseudo_spectateur = $donnees_spectat['pseudo_spectateur'] ;
$prenom_spectateur = $donnees_spectat['prenom_spectateur'] ;
$nom_spectateur = $donnees_spectat['nom_spectateur'] ;

?>
<h1>Voici les avis publiés par <?php echo $pseudo_spectateur . ' (' . $prenom_spectateur . ' ' . $nom_spectateur . ')' ; ?></h1>

<div class="menu_back">
<a href="avis_list_aprob.php?affichage=complet">Affichage complet</a> | 
<a href="spectateurs_listing.php" >Listing des spectateurs</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>

<p>

<?php

// EN TETE TABLE
$avis_concat.='<table width="850" border="1" align="center" cellpadding="10" cellspacing="0" class="data_table" >
  <tr>
	<th colspan="2">' ;
	// Nombre d'avis déposés par ce spectateur + nom... :
	$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur'");
	$donnees_3 = mysql_fetch_array($retour_3);
	$_tot_entrees = $donnees_3['nbre_entrees'];
	$avis_concat.= '<p>' . $_tot_entrees . ' avis laissés par 
	<a href="spectateurs_edit_profile.php?spect="' . $id_spectateur . ' title="Editer le profil" >' . $pseudo_spectateur . '</a> (' . $prenom_spectateur . ' ' . $nom_spectateur . ')
	</p></th></tr>' ;

$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur' ORDER BY id_avis DESC ");
while ($donnees_avis = mysql_fetch_array($reponse_avis))
{
	$flags_avis = $donnees_avis ['flags_avis'];
	$flags_avis_array = explode(",", $flags_avis);
	
	$avis_concat.= '<tr class="tr_hover"><td width="250" valign="top">
	<a name="ancre' . $donnees_avis['nom_avis'] . '" id="ancre' . $donnees_avis['nom_avis'] . '"></a>
	
	<ul>
	<li><b>Avis numéro : </b>' . $donnees_avis['id_avis'] . '</li>
	<li><b>Date : </b>' . date('d/m/Y à H\hi', $donnees_avis ['t_stamp_avis']) . '</li>';

	// Récupération du nom du ce spectacle
	$event_avis = $donnees_avis['event_avis'] ;
	$nom_avis = $donnees_avis['nom_avis'] ;
	
	$reponse = mysql_query("SELECT nom_event FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
	$donnees_event = mysql_fetch_array($reponse);
	

	
	$avis_concat.= '<li><b>Evenement : </b><i>' . $donnees_event ['nom_event'] . '</i>(id ' . $event_avis . ')</li>
	<li><b>IP : </b>' . $donnees_avis['ip_avis'] . '</li>
	</ul></td>
	
	<td valign="top"' ;
	 	
	// Colorer la case si REFUS de publier un avis
	if (in_array('refus', $flags_avis_array)) { $avis_concat.= 'bgcolor="'.$td_color_refus.'"' ;}
	$avis_concat.='><div align="justify">' . stripslashes($donnees_avis['texte_avis']) . '</div></td></tr>';



}
$avis_concat.= '</table> <br />' ;
echo $avis_concat ;	

?>


<?php //--- mysql_close($db2dlp); ?>

<p>&nbsp;</p>
</body>
</html>