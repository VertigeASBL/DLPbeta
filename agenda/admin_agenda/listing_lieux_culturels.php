<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Listing des lieux affili&eacute;s &agrave; l'agenda</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Listing des lieux affili&eacute;s &agrave; l'agenda</h1>


<div class="menu_back">
<a href="c.php">Lieux à approuver</a> | 
<a href="index_admin.php">Menu Admin</a> | 
<a href="../../-Les-lieux-partenaires-">Les lieux partenaires (côté public)</a>
</div>

<?php
require '../inc_var.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';

$case_rouge = 'FFAA77'; // couleur de fond en fonction de l'état du paiement
$case_verte = '99CC99'; // couleur de fond en fonction de l'état du paiement

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des Lieux culturels
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// EN TETE TABLE
$tab ='<table width="850" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
    <td colspan="4" height="30" align="center" >
	
	<a href="edit_profil_lieu.php?new=creer">
	<img src="../design_pics/bouton_new_lieu.gif" hspace="5" title="Encoder un nouveau lieu culturel" ></a>
		
	<a href="edit_user_agenda.php">
	<img src="../design_pics/bouton_new_user.gif" hspace="5" title="Encoder un nouvel utilisateur" ></a>
	
	
	</td>
  </tr>' ;

// Créer un nouveau LIEU
$tab.='
  <tr>
    <th>ID</th>
    <th>Cotisation <sup>*</sup> </th>
    <th>D&eacute;nomination</th>
    <th>Personne(s) de contact </th>
  </tr>' ;
  

$reponse = mysql_query("SELECT * FROM $table_lieu ORDER BY id_lieu DESC");
while ($donnees = mysql_fetch_array($reponse))
{
	// ____________________________________________
	// ID
	$tab.= '<tr class="tr_hover"><td><i>' . $donnees ['id_lieu'] . '</i></td>' ;
	
	
	// ____________________________________________
	// COTISATION : afficher couleur de fond en fonction de l'état du paiement !! pas sup à 2037

	$date_actuelle=date(mktime(0, 0, 0, date('m'), date('d'), date('Y')));

	// Date limite
	$date_edit = $donnees['cotisation_lieu'];
	$fin_cotisation_annee = substr($date_edit, 0, 4);
	$fin_cotisation_mois = substr($date_edit, 5, 2);	
	$fin_cotisation_jour = substr($date_edit, 8, 2);	
		
	$date_limite=date(mktime(0, 0, 0, $fin_cotisation_mois, $fin_cotisation_jour, $fin_cotisation_annee));

	if (($date_limite-$date_actuelle) < 0)
	{ $tab.= '<td valign="top" align="center" bgcolor="#'.$case_rouge.'">' ; }
	else
	{ $tab.= '<td valign="top" align="center" bgcolor="#'.$case_verte.'">' ; }
	
	$tab.= $fin_cotisation_jour . '/' . $fin_cotisation_mois . '/' . $fin_cotisation_annee . '</td>' ;
		
	// ____________________________________________
	// DENOMINATION
	$tab.= '<td valign="top">';
	
	$nom_lieu_court = $donnees['nom_lieu'] ;// Raccourcir la chaine :
	$max=50; // Longueur MAX de la chaîne de caractères
	$chaine_raccourcie = raccourcir_chaine ($nom_lieu_court,$max); // retourne $chaine_raccourcie
	$tab.= '<a href="effacer_lieu.php?id_lieu='. $donnees ['id_lieu'] . '"><img src="../design_pics/bouton_delete.gif" width="15" height="14" hspace="3" title="Effacer la fiche du lieu culturel" ></a>
	
	<a href="edit_profil_lieu.php?id='. $donnees ['id_lieu'] . '">
	<img src="../design_pics/bouton_edit.gif" width="20" height="14" hspace="3" title="Editer le profil du lieu culturel" ></a> 
	 
	<a href="listing_events.php?lieu=' . $donnees ['id_lieu'] . '">
	<img src="../design_pics/bouton_liste.gif" width="27" height="14" hspace="3" title="Liste des &eacute;v&eacute;nements" ></a> 
	' . $chaine_raccourcie ;
	
	// Nombre de fiches d'événements pour ce lieu culturel :
	$id_lieu = $donnees ['id_lieu'] ;
	$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda WHERE lieu_event = '$id_lieu'");
	$donnees_3 = mysql_fetch_array($retour_3);
	$_tot_entrees = $donnees_3['nbre_entrees'];
	$tab.= ' - <span class="mini"><i>(' . $_tot_entrees . ' Evénements)</span>';

	$tab.= '</td>';
	

	// ____________________________________________
	// PERSONNE(S) DE CONTACT  ! c'est la TABLE "$table_user_agenda" !
	$tab.= '<td valign="top"><ul>';

	$entree_lieu_actuel = $donnees ['id_lieu'];
	
	$reponse_2 = mysql_query("SELECT * FROM $table_user_agenda WHERE lieu_admin_spec = '$entree_lieu_actuel' ");
	while ($donnees_2 = mysql_fetch_array($reponse_2))
	{
		$tab.= '<li>
		
		<a href="effacer_user.php?id_user=' .$donnees_2['id_admin_spec'] . '"><img src="../design_pics/bouton_delete.gif" width="15" height="14" hspace="3" title="Effacer cet utilisateur" ></a>
		
		
		<a href="mailto:'.$donnees_2['e_mail_admin_spec'] . ' ">
		<img src="../design_pics/bouton_email.gif" width="16" height="14" hspace="3" title="Envoyer un e-mail à '.$donnees_2['nom_admin_spec'].'" ></a>' ;
		$tab.= '<a href="edit_user_agenda.php?id='.$donnees_2['id_admin_spec'].'">
		<img src="../design_pics/bouton_edit.gif" width="20" height="14" hspace="3" title="Editer le profil de '.$donnees_2['nom_admin_spec'].'" ></a>
		'.$donnees_2['nom_admin_spec'] . '</li>' ;
	}
	$tab.= '</ul></td></tr>' ;
}
$tab.= '</table>' ;

echo $tab ;

//--- mysql_close($db2dlp); //--- ajouter ressource sinon plantage apache/mysql status 3221225477

?>

<p>&nbsp;</p>
<span class="champ_obligatoire">*</span><span class="mini"> Le syst&egrave;me ne g&egrave;re pas les date au del&agrave; de 2037.</span>
</body>
</html>
