<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Rapport des modifications effectu&eacute;es par les Users</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Rapport des modifications  effectu&eacute;es par les Users 
<?php echo '<i>(le ' . date('d/m/Y à H\hi') . ')</i>' ;?></h1>

<div class="menu_back">
<a href="listing_logs.php" >Actualiser</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>


<?php
require '../inc_var_dist_local.php'; // /!\ l'include doit être placée avant "inc_var.php"
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';
require '../logs/fct_logs.php';

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des modifications effectuées par les utilisateurs
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

//////////////////////////////////////////////////
// Faut-il effacer le contenu de la TABLE ?
//////////////////////////////////////////////////

if (isset($_POST['bouton_effacer']) AND ($_POST['bouton_effacer'] == 'effacer'))
{
	 mysql_query("TRUNCATE TABLE $table_logs ") ;
}

//////////////////////////////////////////////////
// AFFICHAGE TABLEAU :
//////////////////////////////////////////////////

// ____________________________________________
// EN TETE TABLE
$tab ='<table width="950" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
	<th>ID</th>
	<th>Lieu<br />culturel</th>
	<th>Type <br />modification</th>
	<th>Date <br />modification</th>
	<th>Description</th>
	<th>Action <br />effectuée</th>
  </tr>' ;


$reponse_log = mysql_query("SELECT * FROM $table_logs ORDER BY timestamp_log DESC");
while ($donnees_log = mysql_fetch_array($reponse_log))
{
	// ____________________________________________
	// ID
	$tab.= '<tr class="tr_hover"><td><i>' . $donnees_log ['id_log'] . '</i></td>' ;
	
	
	// ____________________________________________
	// LIEU CULTUREL
	$liloooluu = $donnees_log ['lieu_log'] ;
	$reponse_lieu_log = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = $liloooluu");
	$donnees_lieu_log = mysql_fetch_array($reponse_lieu_log);
	$lieu_log_modif = $donnees_lieu_log ['nom_lieu'] ;

	$tab.= '<td align="center">' . $lieu_log_modif . '</td>';
	
	
	// ____________________________________________
	// TYPE DE MODIFICATION
	$qwaaaa = $donnees_log ['type_log'] ;
	$lien_log = $racine_domaine . $type_log_array[$qwaaaa]['1'] . $donnees_log ['context_id_log'] ;
	$tab.= '<td align="center"><a href="' . $lien_log . '" title = "Voir la page en ligne">
	' . $type_log_array[$qwaaaa]['0'] . ' [' . $donnees_log ['context_id_log']  . ']</a></td>';
	
	
	// ____________________________________________
	// DATE 
	$tab.= '<td align="center" class="mini">' . date('d/m/Y - H\hi', $donnees_log ['timestamp_log']) . '</td>';
	
	
	// ____________________________________________
	// DESCRIPTION
	$tab.= '<td class="mini">' . urldecode ($donnees_log ['description_log']) . '</td>';
	
	
	// ____________________________________________
	// ACTION
	$tab.= '<td align="center" class="mini">&nbsp;' . $donnees_log ['action_log'] . '</td>';

}
$tab.= '</table>' ;
echo $tab ;

//--- mysql_close($db2dlp);


?>
<!-- /!\ il faut le JavaScript pour bénéficier de la confirmation -->
<br /> <form name="form1" method="post" action="" id="supprime"><div align="center"> 
	<input name="bouton_effacer" type="submit" id="bouton_effacer" value="effacer"
	onclick="if (confirm('Êtes vous sûr de vouloir effacer tous les rapports ?')) { document.forms.supprime.submit(); } else  { return false; }" >
	</div></form>

<p>Note : S'il s'agit d'une erreur de cr&eacute;ation d'&eacute;v&eacute;nement via RSS, le lien de ce rapport n'est pas utilisable </p>
</body>
</html>
