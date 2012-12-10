<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Listing des Spectateurs de DLP</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Listing des Spectateurs de DLP</h1>


<div class="menu_back"><a href="index_admin.php">Menu Admin</a> | 
<a href="../../-Communaute-des-spectateurs-">Listing côté public</a></div>

<?php
require '../inc_var.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';

$case_rouge = 'FFAA77'; // couleur de fond en fonction de l'état du paiement
$case_verte = '99CC99'; // couleur de fond en fonction de l'état du paiement

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des Spectateurs qui ont ouvert un compte sur DLP
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// EN TETE TABLE
$tab ='<table width="850" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
    <td colspan="4" height="30" align="center" >		
	<a href="../spectateurs_admin/ins/a_1.php">
	<img src="../design_pics/bouton_new_user.gif" hspace="5" title="Encoder un nouveau spectateur" ></a>
	</td>
  </tr>' ;

// Créer un nouveau LIEU
$tab.='
  <tr>
    <th width="50">ID</th>
    <th>Nom - Prénom <em>(pseudo)</em></th>
    <th>Avis<br />(total/saison)</th>
    <th>Coordonnées</th>
  </tr>' ;
  

$reponse = mysql_query("SELECT * FROM ag_spectateurs ORDER BY id_spectateur DESC ");
while ($donnees = mysql_fetch_array($reponse))
{
	// ____________________________________________
	// ID
	$tab.= '<tr class="tr_hover"><td ';
	if ($donnees ['compte_actif_spectateur'] == 'new')
	{
		$tab.= 'bgcolor="#FFCC00">' ;
		$tab.= '<a href="spectateurs_rappel_inscript.php?id_rappel=' . $donnees ['id_spectateur'] . '" target="_blank" ><img src="../design_pics/bouton_email_rappel.gif" ></a> ' ;
		
	}
	else
	{
		$tab.= '>' ;
	}
	$tab.= '<i>' . $donnees ['id_spectateur'] . '</i></td>' ;
	
	
	
	// ____________________________________________
	// NOM - PRENOM (PSEUDO)
	$tab.= '<td valign="top">';

	$prenom_spectateur = $donnees['prenom_spectateur'] ;// Raccourcir la chaine :
	$max=12; // Longueur MAX de la chaîne de caractères
	$chaine_raccourcie = raccourcir_chaine ($prenom_spectateur,$max); // retourne $chaine_raccourcie
	$chaine_raccourcie_pseudo = raccourcir_chaine ($donnees['pseudo_spectateur'],$max); // retourne $chaine_raccourcie
	$tab.= '<a href="spectateurs_effacer.php?id_spect='. $donnees['id_spectateur'] . '"><img src="../design_pics/bouton_delete.gif" width="15" height="14" hspace="3" title="Effacer le profil de ce spectateur" ></a>
	
	<a href="spectateurs_edit_profile.php?spect='. $donnees['id_spectateur'] . '">
	<img src="../design_pics/bouton_edit.gif" width="20" height="14" hspace="3" title="Editer le profil de ce spectateur" ></a> 
	 
	<a href="spectateurs_avis_1spect.php?spect=' . $donnees['id_spectateur'] . '">
	<img src="../design_pics/bouton_liste_avis_spectateurs.gif" width="27" height="14" hspace="3" title="Liste des AVIS laissés par ce spectateur" ></a>
	<span class="titre_turquoise" style="font-size:11px"><a href="../../-Detail-d-un-spectateur-?id_spect=' . $donnees ['id_spectateur'] . '">' . 
	$donnees['nom_spectateur'] . ' - ' . 
	$chaine_raccourcie . ' <em>(' . 
	$chaine_raccourcie_pseudo . ')</em></a></span></td>' ;

	// Nombre d'avis déposés par ce spectateur :
	$id_spectateur = $donnees ['id_spectateur'] ;
	$pseudo_spectateur = $donnees ['pseudo_spectateur'] ;
	$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur'");
	$donnees_3 = mysql_fetch_array($retour_3);
	$_tot_entrees = $donnees_3['nbre_entrees'];
	
	$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];
	$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
	$result_categorie_spectateur = trouve_categorie_spectateur ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE

	$tab.= '<td><img src="../design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Niveau" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /> ' . $_tot_entrees . '/' . $avis_valides_spectateur . ' ';

	$tab.= '</td>';
	

	// ____________________________________________
	// Coordonnées
	$tab.= '<td valign="top">';
	$e_mail_spectateur = $donnees ['e_mail_spectateur'] ;
	$tel_spectateur = $donnees ['tel_spectateur'] ;
	$e_mail_spectateur = $donnees ['e_mail_spectateur'] ;

	$tab.= '<span class="mini">tél : ' . $tel_spectateur . 
	' - email : <a href="mailto:' . $e_mail_spectateur . '">' . $e_mail_spectateur . '</a> </span>' ;
	
	$tab.= '</td></tr>' ;
}
$tab.= '</table>' ;

echo $tab ;

//--- mysql_close($db2dlp);

?>

</body>
</html>
