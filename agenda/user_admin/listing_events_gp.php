<?php 
session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Listing des &eacute;v&eacute;nements culturels</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">

<script language='javascript'>
function confirmation() {
if (confirm("Voulez-vous vraiment cr�er un nouvel �v�nement ?")) {
	window.location.href='edit_event_gp_tiny.php?new=creer';
	}
} 
</script>

</head>

<body>

<div id="head_admin_agenda"></div>

<?php 
require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?>

<!-- h1 plus bas -->
<div class="menu_back"><a href="../../-Agenda-">Le site</a>| 
<a href="votre_menu.php">Votre menu</a> </div>


<?php 
// Affichage Nom, Groupe et Log Off du user
voir_infos_user () ;


require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';

$case_hors_periode = 'DDDDDD'; // couleur de fond en fonction de l'�tat du paiement
$case_periode_actuelle = '999999'; // couleur de fond en fonction de l'�tat du paiement

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des �v�nements d'un LIEU culturel (c�t� PUBLIC)
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

$id = $_SESSION['lieu_admin_spec'];
//$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id' ORDER BY date_event_debut");
$reponse = mysql_query("SELECT id_event FROM $table_evenements_agenda WHERE lieu_event = '$id'");
$donnees = mysql_fetch_array($reponse);

if (empty($donnees))
{
	echo '<p>&nbsp;</p><p>&nbsp;</p>
	<div class="alerte">Votre agenda est vide. 
	Voulez-vous <a href="edit_event_gp_tiny.php?new=creer">cr�er un �v�nement</a> ? </div>' ;
	exit() ;
}

$reponse_lieu = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$id'");
$donnees_lieu = mysql_fetch_array($reponse_lieu);

echo '<h1 align="center">' . $donnees_lieu['nom_lieu'] . ' - Liste de vos �v�nements</h1>' ;



// ____________________________________________
// EN TETE TABLE

	// Date limite
	//$date_actuelle=date(mktime(0, 0, 0, date('m'), 1, date('Y')));
	$date_actuelle= time();

	$date_edit = $donnees_lieu['cotisation_lieu'];
	$fin_cotisation_annee = substr($date_edit, 0, 4);
	$fin_cotisation_mois = substr($date_edit, 5, 2);
	$fin_cotisation_jour = substr($date_edit, 8, 2);	
		
	$date_limite=date(mktime(0, 0, 0, $fin_cotisation_mois, $fin_cotisation_jour, $fin_cotisation_annee));
	$date_rappel=date(mktime(0, 0, 0, $fin_cotisation_mois-2, $fin_cotisation_jour, $fin_cotisation_annee));
	$cotis = '';
	$cotis_ok = '';

	if (($date_limite-$date_actuelle) < 0) // pas en ordre de cotisation
	{ $cotis.= '<br><div class="rouge"><br>
	
	<p><strong>Votre abonnement sur le site &quot;Demandez le programme!&quot; a expir&eacute;. </strong></p>
	<p>Si vos codes d\'acc&egrave;s personnels demeurent valides, les spectacles que vous encodez ne sont plus visibles dans l\'agenda.</p>
<p>Pour renouveller votre abonnement, nous vous invitons &agrave; payer votre cotisation sur le compte de Vertige asbl (comedien.be/demandezleprogramme): 001-5030933-07. Pour les virements de l\'&eacute;tranger sans frais, vous devez mentionner les infos suivantes: IBAN BE74 0015-0309-3307 et fortis bank BIC GEBABEBB. En communication, merci de pr&eacute;ciser: &quot;demandezleprogramme: Lieu num&eacute;ro ' . $donnees_lieu['id_lieu'] . ' &quot;</p>

<br /><p><strong>Tarifs :</strong></p>
<p><strong>242&euro; TTC/an</strong></p>
<ul>
  <li>Salles de grande envergure, concert/show</li>
  <li> Th&eacute;&acirc;tres et lieux culturels subventionn&eacute;s par un contrat-programme</li>
  <li> Grands festivals</li>
  <li> Institutions culturelles subventionn&eacute;es (expos, �v�nements ponctuels)</li>
</ul>
<p><strong><br />
  121&euro; TTC/an</strong></p>
<ul>
  <li> Autre lieux culturels</li>
  <li> Caf&eacute;-th&eacute;&acirc;tres</li>
</ul>

<br></div><br /> <br />' ; }
	
	elseif (($date_rappel-$date_actuelle) < 0) // il reste moins d'un mois pour renouveler la cotisation
	{ $cotis.= '<br><div class="alerte"><br /> <br /> <br /> <strong>Attention, vous devez renouveler votre abonnement.<br />
	Il expire le ' . date('d-m-Y', $date_limite) . '. 
	<a href="../../Fonctionnement-du-site-et">Infos</a></strong><br /> <br /> <br /> </div>' ;
	 }
	
	else // OK
	{ $cotis_ok = 'Fin cotisation : ' . $fin_cotisation_jour . ' ' . $NomDuMois[$fin_cotisation_mois+0] . ' ' . $fin_cotisation_annee ; }	

$tab = $cotis . '<br />';



// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
//Afficher �v�nements vides :
$reponse_des_vides = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id' 
AND ((date_event_debut = '0000-00-00') OR (date_event_fin = '0000-00-00')) ");

$nb_lignes_resultat = mysql_num_rows($reponse_des_vides);
//var_dump($nb_lignes_resultat) ;
if ($nb_lignes_resultat > 0)
{
	$tab.= '<div class="alerte"><br />Par le pass�, vous avez cr�� des "fiches �v�nements" que vous n\'avez pas remplies pour la raison que vous avez interrompu la proc�dure en cours de route. Nous vous demandons d\'utiliser prioritairement les �v�nements list�s ci-dessous, et de ne pas cr�er de nouvel �v�nement tant qu\'il reste des �l�ments dans cette liste. Cliquez simplement sur un �l�ment de la liste et vous pourrez l\'�diter.<br /><br />';
	
	while ($donnees_des_vides = mysql_fetch_array($reponse_des_vides))
	{
		$tab.= '<a href="edit_event_gp_tiny.php?id='. $donnees_des_vides['id_event'] . '">Ev�nement '. $donnees_des_vides['id_event'] . '</a> <br />';
	}
	$tab.= '</div><br /><br /><br />';
}

// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV


$tab.= '<table width="650" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" ><tr>
<td height="30" colspan="3">
<a href="edit_profil_lieu_gp.php">
<img src="../design_pics/bouton_home.gif" hspace="15" title="Modifier le profil du lieu culturel" alt="" /></a>
<span class="mini">' . $cotis_ok . '</span>
</td>


<td valign="top">
<div align="right"><a href="javascript:confirmation(); ">
<img src="../design_pics/bouton_new.gif" hspace="5" title="Encoder un nouvel &eacute;v&eacute;nement" alt="" /></a></div>
</td>



</tr>' ;

  $tab.='
  <tr>
	<th>ID</th>
	<th>Vignette</th>
	<th>Date d�but-fin</th>
	<th>Nom de l\'&eacute;v&eacute;nement</th>
  </tr>' ;
  
 

// N'afficher que les �v�nements pour 1 saison :

if (isset($_POST['go_annee']) AND ($_POST['go_annee'] == 'Afficher'))
{
	$annee_debut_choix = htmlentities($_POST['go_annee'], ENT_QUOTES);
	$choix_annee_postee = htmlentities($_POST['choix_annee'], ENT_QUOTES);
	$date_debut_choix = $choix_annee_postee . '-07-01' ;
	$date_fin_choix = $choix_annee_postee + 1 . '-06-30' ;
	//echo $date_debut_choix . ' ************ ' . $date_fin_choix ;
}
else
{
	if (date('m') <= 7)
	{
		$annee_saison_en_cours = date('Y')-1 ;
	}
	else
	{
		$annee_saison_en_cours = date('Y') ;
	}

	$date_debut_choix = $annee_saison_en_cours . '-08-01' ;
	$date_fin_choix = ($annee_saison_en_cours + 1) . '-08-0' ;
	$choix_annee_postee = $annee_saison_en_cours ;
	// echo $annee_saison_en_cours ;
}



$tab.= '<tr><td colspan="4" align="center">' ;

	$tab.= '<form action="" method="post">';
	
	$tab.= '<em>Choisissez ici la saison � afficher : </em><select name="choix_annee">
	<option value="' . (date ('Y')+1) . '">Saison ' . (date ('Y')+1) . ' - ' . (date ('Y')+2) . '</option>'; 
		
	for ($liste_annee=(date('Y')) ; $liste_annee>=2007 ; $liste_annee--)
	{
		$tab.= '<option value="' . $liste_annee .'"';		
		// Faut-il pr&eacute;-s&eacute;lectionner
		if (isset($choix_annee_postee) AND $choix_annee_postee == $liste_annee)
		{
			$tab.= ' selected="selected" ';
		}
		$liste_annee_plus_1 = $liste_annee +1 ;
		$tab.= '>Saison '.$liste_annee.' - ' . $liste_annee_plus_1 . '</option>';
	}
	$tab.= '</select>';
	
	$tab.= '<input name="go_annee" value="Afficher" type="submit">' ;

$tab.= '</form>';
$tab.= '</td></tr>' ;

/*
$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id' AND date_event_debut > SUBDATE(CURDATE(), INTERVAL 4 MONTH) ");

$date_debut_choix = '2007-06-01' ;
$date_fin_choix = '2009-06-01' ;

$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event = '$id' AND ((date_event_debut > '$date_debut_choix') AND (date_event_debut < '$date_fin_choix')) ORDER BY parent_event,date_event_debut DESC");
*/
$reponse = mysql_query("SELECT A.id_event,A.parent_event,A.nom_event,A.date_event_debut,A.date_event_fin,A.pic_event_1
	FROM $table_evenements_agenda A,$table_evenements_agenda B
	WHERE A.lieu_event='$id' AND A.date_event_debut>'$date_debut_choix' AND A.date_event_debut<'$date_fin_choix' AND (B.id_event=A.parent_event OR A.parent_event=0 AND B.id_event=A.id_event)
	ORDER BY IF(A.parent_event=0,A.date_event_debut,B.date_event_debut) DESC,A.parent_event,A.date_event_debut");

$nextdonnees = mysql_fetch_array($reponse);
while ($nextdonnees) {
	$donnees = $nextdonnees;
	$nextdonnees = mysql_fetch_array($reponse);

	// ____________________________________________
	// ID
	$tab.= '<tr class="tr_hover"><td'.($donnees['parent_event'] ? ' class="evenfant"' : ($nextdonnees && $nextdonnees['parent_event'] ? ' class="evparent"' : '')).'><i>'.$donnees['id_event'].'</i></td>' ;
	
	
	// ____________________________________________
	// VIGNETTE EVENEMENT
	$tab.= '<td align="center">';
	
	if (isset ($donnees['pic_event_1']) AND $donnees['pic_event_1'] == 'set' )
	{
		$nom_event = $donnees['nom_event'] ;
		$id_event = $donnees['id_event'] ;
		$tab.= '<img src="../' . $folder_pics_event . 'event_' . $id_event . '_1.jpg" title="' . htmlspecialchars($nom_event) . '" alt="" width="100" />';
	}
	$tab.= '</td>';
	
	
	// ____________________________________________
	// PERIODE DE REPRESENTATION DU SPECTACLE
	$date_event_debut = $donnees['date_event_debut'];	
	$date_event_debut_annee = substr($date_event_debut, 0, 4);
	$date_event_debut_mois = substr($date_event_debut, 5, 2);
	$date_event_debut_jour = substr($date_event_debut, 8, 2);
	$time_debut=date(mktime(0, 0, 0, $date_event_debut_mois, $date_event_debut_jour, $date_event_debut_annee));
	
	$date_event_fin = $donnees['date_event_fin'];
	$date_event_fin_annee = substr($date_event_fin, 0, 4);
	$date_event_fin_mois = substr($date_event_fin, 5, 2);
	$date_event_fin_jour = substr($date_event_fin, 8, 2);
	$time_fin=date(mktime(0, 0, 0, $date_event_fin_mois, $date_event_fin_jour, $date_event_fin_annee));
	

	// Date actuelle
	$month_now=date("n"); 
	$year_now=date("Y");
	$date_actuelle= time();
	
	/*echo $date_actuelle . ' --- ' . $time_debut . ' --- ' . $time_fin . '<br>';*/

	// La date actuelle fait-elle partie de la p�riode de repr�sentation ?
	if (($date_actuelle >= $time_debut) AND ($date_actuelle <= $time_fin))
	{ $tab.= '<td class="mini" valign="top" align="center" bgcolor="#'.$case_periode_actuelle.'">' ; }
	else
	{ $tab.= '<td class="mini" valign="top" align="center" bgcolor="#'.$case_hors_periode.'">' ; }

	$tab.= $date_event_debut_jour . '-' . $date_event_debut_mois . '-' . $date_event_debut_annee . 
	' &agrave; ' . $date_event_fin_jour . '-' . $date_event_fin_mois . '-' . $date_event_fin_annee . '</td>' ;
	

	// ____________________________________________
	// NOM EVENEMENT
	$tab.= '<td valign="top">';
	
	$nom_event_court = $donnees['nom_event'] ;// Raccourcir la chaine :
	$max=50; // Longueur MAX de la cha�ne de caract�res
	$chaine_raccourcie = raccourcir_chaine ($nom_event_court,$max); // retourne $chaine_raccourcie
	$tab.= '<a href="edit_event_gp_tiny.php?id='. $donnees['id_event'] . '">
	<img src="../design_pics/bouton_edit.gif" width="20" height="14"  hspace="3" title="Editer la fiche de l\'&eacute;v&eacute;nement" alt="" /></a> 
	<a href="effacer_event_gp.php?id='. $donnees['id_event'] . '">
	<img src="../design_pics/bouton_delete.gif" width="15" height="14" hspace="3" title="Effacer la fiche de l\'&eacute;v&eacute;nement" alt="" /></a> -
	' . $chaine_raccourcie . '</td></tr>';
	
	
}
$tab.= '</table>' ;
echo $tab ;

// L�gende du calendrier 
$table_legende = '<br /><table border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#FFFFFF">
  <tr>
	<th align="center">Legende :</th>
  </tr>
  <tr>
	<td bgcolor="#'.$case_periode_actuelle.'">Le spectacle est jou� actuellement</td>
  </tr>
  <tr>
	<td bgcolor="#'.$case_hors_periode.'">Le spectacle n\'est pas jou� actuellement</td>
  </tr>
</table>';
echo $table_legende ;

//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>
