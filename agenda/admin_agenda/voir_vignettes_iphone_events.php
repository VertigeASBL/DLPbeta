<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Mise a jour des vignettes iphone &eacute;v&eacute;nements culturels</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<h1>Voir les vignettes iphone &eacute;v&eacute;nements d'un lieu culturel</h1>

<div class="menu_back">
<a href="listing_lieux_culturels.php" >Listing des lieux affiliés</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>


<?php 
if (! isset($_GET['param']) || $_GET['param'] != 'g6tycv5s')
	exit('Erreur : paramètre attendu');

require '../inc_var.php';
require '../inc_db_connect.php';
//require '../user_admin/ins/inc_var_inscription.php';
//require '../inc_fct_base.php';

//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des lieux + événements
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

/*
$repons0 = mysql_query("SELECT * FROM $table_lieu ORDER BY id_lieu DESC");
while ($donnee0 = mysql_fetch_array($repons0))
{
}

// ----------------------------------------------------------
	$repons1 = mysql_query("SELECT * FROM $table_evenements_agenda WHERE lieu_event != 0");
	$donnee1 = mysql_fetch_array($repons1);
*/
$date_fin_choix = '2010-10-28' ;

		echo '<table border="1" align="center" cellpadding="4" cellspacing="0" style="white-space:nowrap; vertical-align:top;">',"\n";

$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE date_event_fin >= '$date_fin_choix' ORDER BY date_event_debut DESC");

		while ($donnees = mysql_fetch_array($reponse))
		{
			echo '<tr>'."\n";
			echo '<td><i>' . $donnees['id_event'] . '</i></td>'."\n";
			echo '<td>' . $donnees['date_event_debut'] .' - '. $donnees['date_event_fin'] . '</td>'."\n";
			echo '<td>',$donnees['nom_event'],'</td>',"\n";
			
			// ____________________________________________
			// VIGNETTE EVENEMENT
			echo '<td>1',"\n";
			if (isset ($donnees['pic_event_1']) AND $donnees['pic_event_1'] == 'set' )
			{
				$id_event = $donnees['id_event'] ;
				$src_img = '../' . $folder_pics_event . 'event_' . $id_event . '_1.jpg';
				echo '<img src="',$src_img,'" alt="" />',"\n";
				$src_img = '../' . $folder_pics_event . 'micro_event_' . $id_event . '_1.jpg';
				if (file_exists($src_img))
					echo 'micro:<img src="',$src_img,'" alt="" />',"\n";
				else
					echo 'micro:manque',"\n";
			}
			else
				echo '1',"\n";
			echo '</td>',"\n";
			
			// ____________________________________________
			// VIGNETTE EVENEMENT
			echo '<td>2',"\n";
			if (isset ($donnees['pic_event_2']) AND $donnees['pic_event_2'] == 'set' )
			{
				$id_event = $donnees['id_event'] ;
				$src_img = '../' . $folder_pics_event . 'event_' . $id_event . '_2.jpg';
				echo '<img src="',$src_img,'" alt="" />',"\n";
				$src_img = '../' . $folder_pics_event . 'micro_event_' . $id_event . '_2.jpg';
				if (file_exists($src_img))
					echo 'micro:<img src="',$src_img,'" alt="" />',"\n";
				else
					echo 'micro:manque',"\n";
			}
			else
				echo '2',"\n";
			echo '</td>',"\n";

			// ____________________________________________
			// VIGNETTE EVENEMENT
			echo '<td>3',"\n";
			if (isset ($donnees['pic_event_3']) AND $donnees['pic_event_3'] == 'set' )
			{
				$id_event = $donnees['id_event'] ;
				$src_img = '../' . $folder_pics_event . 'event_' . $id_event . '_3.jpg';
				echo '<img src="',$src_img,'" alt="" />',"\n";
				$src_img = '../' . $folder_pics_event . 'micro_event_' . $id_event . '_3.jpg';
				if (file_exists($src_img))
					echo 'micro:<img src="',$src_img,'" alt="" />',"\n";
				else
					echo 'micro:manque',"\n";
			}
			else
				echo '3',"\n";
			echo '</td>',"\n";

			// ____________________________________________
			echo '</tr>',"\n";
		}
		echo '</table>',"\n";

//--- mysql_close($db2dlp);

?>

<p>&nbsp;</p>
</body>
</html>
