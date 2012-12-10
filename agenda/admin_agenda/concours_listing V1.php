<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Listing des concours</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">

<script language='javascript'>
function confirmation_new() {
if (confirm("Voulez-vous vraiment créer un nouvel concours ?")) {
	window.location.href='concours_listing.php?new=creer';
	}
}

function confirmation_effacer(ma_var) {
if (confirm("Voulez-vous vraiment effacer ce concours ?")) {
	window.location.href='concours_listing.php?effacer='+ma_var;
	}
}

</script>

<style type="text/css">
<!--
.actif {
	color: #00AA00;
	font-size: 14px;
	text-align: center;
}

.non_actif {
	color: #CC0000;
	font-size: 14px;
	text-align: center;
}

-->
</style>
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Listing des concours</h1>

<div class="menu_back">
<a href="concours_lire_historique.php" >Historique des tirages</a> | 
<a href="index_admin.php">Menu Admin</a> | 
<a href="../concours/conc_tirage.php?pw=s5fah7r6s3p6ax2">Tirage du concours</a>
</div>

<p align="center" class="mini">Les concours &quot;actifs&quot; sont les concours qui sont accessibles au public. Leur nom apparait en vert. 
Les concours qui sont clotur&eacute;s apparaissent dans une case fonc&eacute;e. </p>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';

$case_hors_periode = 'DDDDDD'; // couleur de fond en fonction de l'état du paiement
$case_periode_actuelle = '999999'; // couleur de fond en fonction de l'état du paiement



//--------------------------------------------------------------------
// Effacer un concours (si GET ...php?effacer=...)
//--------------------------------------------------------------------
if (isset($_GET['effacer']) AND ($_GET['effacer'] != NULL))
{
	$entree_effacer = htmlentities($_GET['effacer'], ENT_QUOTES);
	$test_mysql = mysql_query("DELETE FROM `$table_ag_conc_fiches` WHERE `id_conc` = $entree_effacer");
	if ($test_mysql)
	{		
		// Effacer vignette et image
		$vignette_2_delete = '../' . $folder_vignettes_concours . 'vi_conc_' . $entree_effacer . '_1.jpg' ;
		$pic_2_delete = '../' . $folder_vignettes_concours . 'conc_' . $entree_effacer . '_1.jpg' ;
		if (file_exists($vignette_2_delete))
		{
			$rep_im = unlink ($pic_2_delete) ;
			$rep_vi = unlink ($vignette_2_delete) ;
			
			if ($rep_im) { echo '<br>Image effacée' ; }
			if ($rep_vi) { echo '<br>Vignette effacée' ; }
		}
		echo '<br><br><br><div class="info"><p>La fiche concours (' . $entree_effacer . ') a bien été effacée
		<a href="concours_listing.php">Retour</a></p></div><br>' ;
		
		//--- mysql_close($db2dlp);
		exit();
	}
	else
	{
		echo 'echec effacement' ;
	}
}
	

//--------------------------------------------------------------------
// Créer une nouveau concours (si GET ...php?new=creer)
//--------------------------------------------------------------------
if (isset ($_GET['new']) AND $_GET['new'] == 'creer')
{
	$new_conc = mysql_query("INSERT INTO `$table_ag_conc_fiches` (`nom_event_conc`) VALUES ('- -NOUVEAU CONCOURS- -')")
	or print($new_conc . " ----- " . mysql_error());

	$nouvel_id_table_concours = mysql_insert_id() ;
	echo '<br><br><br><div class="info"><p>Une nouvelle FICHE CONCOURS a été créé et peut être  
	<a href="concours_edit.php?id_conc='.$nouvel_id_table_concours.'">éditée</a></p></div><br>' ;
		
	//--- mysql_close($db2dlp);
	exit();
}

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des concours
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

// ____________________________________________
// EN TETE TABLE
$tab ='<table width="950" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
	<th>ID</th>
	<th>Intitulé et date de cloture du concours</th>
	<th>Lots</th>
  </tr>' ;
  
  // Créer un nouvel événement
  $tab.='
	<tr>
<td colspan="4">
<div align="right"><a href="javascript:confirmation_new(); ">
<img src="../design_pics/bouton_new.gif" hspace="3" title="Créer un nouveau concours"></a></div>
</td>
</tr>' ;
		
$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches ORDER BY id_conc DESC");
while ($donnees = mysql_fetch_array($reponse))
{
	// ____________________________________________
	// ID
	$id_conc = $donnees['id_conc'] ;
	$tab.= '<tr class="tr_hover"><td><i>' . $id_conc . '</i>';

	$tab.='</td>' ;


	// ____________________________________________
	// INTITULE + CLOTURE CONCOURS

	// Actif ou pas ?
	preg_match("!actif!", $donnees['flags_conc']) ? $actif_ou_pas= 'actif' : $actif_ou_pas= 'non_actif' ;
	
	
	// La date actuelle fait-elle partie de la période de représentation ?
	$cloture_conc = $donnees['cloture_conc'] ;
	$date_cloture_annee = date('Y',$cloture_conc);
	$date_cloture_mois = date('m',$cloture_conc);
	$date_cloture_jour = date('d',$cloture_conc);
	$date_cloture_heure = date('H',$cloture_conc);
	
	$date_actuelle = time(); // Date actuelle
	
	if ($date_actuelle >= $cloture_conc)
	{ $tab.= '<td valign="top" align="center" bgcolor="#'.$case_periode_actuelle.'">' ; }
	else
	{ $tab.= '<td valign="top" align="center" bgcolor="#'.$case_hors_periode.'">' ; }

	
	// Intitulé	
	$nom_event_conc = stripslashes ($donnees ['nom_event_conc']) ;

	$tab.= '<br /><div class="' . $actif_ou_pas . '"><b>' . $nom_event_conc . '</b></div><br />';
	
	$tab.= 'Clôturé le ' . $date_cloture_jour . '-' . $date_cloture_mois . '-' . $date_cloture_annee . ' 
	à ' . $date_cloture_heure . 	'h00<br /><a href="concours_edit.php?id_conc=' . $id_conc . '">Editer</a>';

	// Lien pour effacer un concours :
	$tab.= '<br /><a href="#voir" onclick="confirmation_effacer(' . $id_conc . '); ">Effacer</a>' ;
	
	$tab.='</td>' ;


	// ____________________________________________
	// LOTS compris dans ce concours
	$tab.= '<td valign="top">';
	
	if (isset($donnees['lots_conc']) AND ($donnees['lots_conc'] != NULL))
	{
	
		$tab.= '<ul>';
	
		$var_lot_unserialized = unserialize($donnees['lots_conc']) ;
			
		$i_lot = 0; // sera incrémenté dans la boucle
		foreach ($var_lot_unserialized as $element_lot)
		{
			$tab.= '<li>[lot ' . $i_lot . '] <b>' . str_pad($element_lot['nombre_places'], 3, "0", STR_PAD_LEFT) . '</b> places ';
	
			$tab.= 'le ' . substr($element_lot['new_date_lot'], 8, 2) . '-' . 
			substr($element_lot['new_date_lot'], 5, 2) . '-' . 
			substr($element_lot['new_date_lot'], 0, 4) . ' à ';
								
			$tab.= substr($element_lot['new_heure_lot'], 0, 2) . 'h' . 
			substr($element_lot['new_heure_lot'], 3, 2) . ' pour le groupe <em>"';
			
			$tab.= $groupes_joueurs[$element_lot['groupe_joueur']] . '"</em>' ;
			
			// Recherche du nombre de personnes qui ont déjà joué
			$reponse_nb_joueurs = mysql_query("SELECT COUNT(*) AS nb_joueurs_actuels FROM $table_ag_conc_joueur 
			WHERE id_fiche_conc_joueur = '$id_conc' AND  lot_conc_joueur = '$i_lot'") or die (mysql_error());
			$donnees_nb_joueurs = mysql_fetch_array($reponse_nb_joueurs);
			$tab.= '<i> (actuellement <b>' . $donnees_nb_joueurs['nb_joueurs_actuels'] . ' </b>joueurs)</i>' ;

		
		
		'"</li>';
			
			/*print_r($element_lot);
			echo '<br /><br /><br />' ;*/
			$i_lot++ ;
	
		}
		$tab.='</ul>';
	}
	else
	{
		$tab.='<br /><div align="center">Aucun lot pour ce concours<br />
		<a href="concours_edit.php?id_conc=' . $donnees ['id_conc'] . '">Editer</a></div>';
	}

	
	
	
	$tab.='</td></tr>';

}

echo $tab ;


//--- mysql_close($db2dlp);

?>

</body>
</html>
