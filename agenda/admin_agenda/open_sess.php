<?php 
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">

<title>Ouvrir une Session</title>
</head>

<body>

<div id="head_admin_agenda"></div>

<h1>Ouvrir une Session</h1>

<div class="menu_back">
<a href="index_admin.php">Menu Admin</a> | 
<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a>
</div>

<?php 
require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';

if (isset($_POST['bouton_lancer']) AND ($_POST['bouton_lancer'] == 'Lancer'))
{
	$_SESSION['group_admin_spec'] = '5'; 
	$_SESSION['group_admin_spec_name'] = $group_admin_spec_noms ['5'] ;
	$_SESSION['lieu_admin_spec'] = $_POST['list_lieux_culturel'] ; 

	$lieu = $_POST['list_lieux_culturel'] ;
	$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$lieu'");
	$donnees = mysql_fetch_array($reponse); 
	$_SESSION['lieu_admin_spec_name'] = $donnees['nom_lieu'] ;
	echo '<br>--- ' . $_POST['list_lieux_culturel'] . ' ---<br>';
}
?>


<?php 
			//$_SESSION['group_admin_spec'] = '999';  // ouverture session USER

echo 'Vous êtes loggé comme <i>' . $_SESSION['group_admin_spec_name'] . '</i><br>
et pouvez accéder au compte de<b> ' . $_SESSION['lieu_admin_spec_name'] . '</b> 
(id=' . $_SESSION['lieu_admin_spec'] . ') <br><br><br>';

?>

<form name="form1" method="post" action="">
	
	<?php 
	// LISTE déroulante des lieux culturels
	echo '<select name="list_lieux_culturel">';
	
	$reponse = mysql_query("SELECT * FROM $table_lieu ");
	while ($donnees = mysql_fetch_array($reponse))
	{
		// Raccourcir la chaine :
		$nom_lieu_court = $donnees['nom_lieu'] ;
		$max=35; // Longueur MAX de la chaîne de caractères
		$chaine_raccourcie = raccourcir_chaine ($nom_lieu_court,$max); // retourne $chaine_raccourcie
		
		echo '<option value="' . $donnees ['id_lieu'] .'"';		
		
		echo '>'.$chaine_raccourcie.'</option>';
	}
	echo '</select>';
		
	?>
<input name="bouton_lancer" type="submit" id="bouton_lancer" value="Lancer">
</form>

<p><br />
  <br />
  <br />
  <br />
  <br />
  <?php 
echo '<hr><b>Voici la liste des variables SESSION actuellement actives : </b><br><br>' ;
foreach($_SESSION as $key => $value)
{
      echo $key." = ".$value."<br />";
}
?>
</p>
<p>&nbsp;</p>
<p><strong>Notes</strong> : Avec un niveau 5, on peut acc&eacute;der &agrave; la racine des fichiers import&eacute;s via Wysiwyg</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
