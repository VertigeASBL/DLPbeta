<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>TEST affichage complet</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>TEST affichage complet</h1>


<div class="menu_back"><a href="index_admin.php">Menu Admin</a> | 
<a href="../../-Communaute-des-spectateurs-">Listing côté public</a></div>

<?php
require '../inc_var.php';
require '../inc_db_connect.php';
require '../user_admin/ins/inc_var_inscription.php';
require '../inc_fct_base.php';


$tab = '';

$reponse = mysql_query("SELECT * FROM ag_spectateurs ORDER BY id_spectateur DESC ");
while ($donnees = mysql_fetch_array($reponse))
{
	$prenom_spectateur = $donnees['prenom_spectateur'] ;// Raccourcir la chaine :
	$pseudo_spectateur = $donnees ['pseudo_spectateur'] ;

	$tab.= '<strong>' . $donnees['prenom_spectateur'] . ' ' . $donnees['nom_spectateur'] . '</strong>
	(id ' . $donnees ['id_spectateur'] . ') - <em>' . $donnees['pseudo_spectateur'] . '</em>
	<a href="spectateurs_edit_profile.php?spect='. $donnees['id_spectateur'] . '">
	<img src="../design_pics/bouton_edit.gif" width="20" height="14" hspace="3" title="Editer le profil de ce spectateur" ></a>
	<br /> <br />
	
	<em>Description courte : </em>' . $donnees['description_courte_spectateur'] . ' <br /> <br />
	<em>Description complète : </em>' . $donnees['description_longue_spectateur'] . ' <br /> <br /><hr>' ;
}
echo $tab ;

//--- mysql_close($db2dlp);

?>

</body>
</html>
