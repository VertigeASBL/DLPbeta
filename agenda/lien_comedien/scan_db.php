<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Recalcul de la table "ag_comedien_lien"</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" /></head>

</head>

<body>

<div id="head_admin_agenda"></div>

<h1>Recalcul de la table "ag_comedien_lien"</h1>

<div class="menu_back">
<a href="../admin_agenda/index_admin.php">Menu Admin</a>
</div>

<p class="alerte">
<br />Ce script passe en revue tous les événements liés à un Comédien de www.comedien.be 
et recalcule l'entièreté de la table "ag_comedien_lien" (après l'avoir effacée). <br /> <br />
<strong>Attention (1)</strong>, le temps de calcul est  long (+-3minutes), <br />
donc,  faire tourner ce script 
sur un serveur local <br />

<br />
<br />
Enlever la ligne &quot;exit()&quot; pour démarrer l'ex&eacute;cution.. <strong> <br />
Attention (2)</strong>, Remettre &quot;exit()&quot; ensuite car le script est dans un dossier accessible à tout le monde.<br />
<br />
<br />
</p>
<?php
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
/* Ce script passe en revue tous les événements liés à un Comédien de www.comedien.be 
et recalcule l'entièreté de la table "ag_comedien_lien" (après l'avoir effacée). 
Attention, le temps de calcul est très long, donc, ne faire tourner ce script que
sur un serveur local
Enlever la ligne exit() pour démarrer
*/ 
// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

exit() ;

$time_start = microtime(true); // Pour afficher le temps d'exécution du script

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Ajouter toutes les entrées de la DB liées à cet événement
function insert_table_ag_comedien_lien_pour_un_event($id_event, $id_comedien, $url_comedien)
{
	//echo 'rrrrrrrrrr ' .$id_comedien . 'tttttttttt' ; ;
	global $debug;
	$debug.= '-> Fonction "insert_table_ag_comedien_lien_pour_un_event" appelée <br />';
	require '../inc_db_connect.php';
	mysql_query("INSERT INTO ag_comedien_lien (`id_lien` ,`id_event_lien` ,`id_comedien_lien` ,`url_comedien_lien`) VALUES ('','$id_event','$id_comedien','$url_comedien')") or die ("Erreur SQL -2- !" . mysql_error());

	require '../inc_db_connect_to_comedien.php';
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

require '../inc_db_connect_to_comedien.php';
$debug = '' ;
$array_id_comediens_a_ajouter = array() ; // Cet array sera transmis à la fonction d'update, après avoir effacé toutes les entrées de la DB liées à cet événement

// PS : Dans la Requête, ID 1726 est là pour éviter Marion
$reponse_comedien_lien = mysql_query("SELECT * FROM comediens WHERE (accord > 0 ) AND (ID != 1726) ");
while ($donnees_comedien_lien = mysql_fetch_array($reponse_comedien_lien))
{
	$id_comedien = $donnees_comedien_lien['ID'] ;
	$prenom_comedien = $donnees_comedien_lien['prenom'] ;
	$nom_comedien = $donnees_comedien_lien['nom'] ;
	$prenom_nom_comedien = htmlentities($prenom_comedien) . ' ' . htmlentities($nom_comedien) ;
	$url_comedien = $donnees_comedien_lien['url'] ;

	require '../inc_db_connect.php';
	
	$reponse_dlp = mysql_query("SELECT id_event, description_event, resume_event FROM ag_event ");
	while ($donnees_dlp = mysql_fetch_array($reponse_dlp))
	{
		$description_event = $donnees_dlp['description_event'] ;
		$resume_event = $donnees_dlp['resume_event'] ;
		$id_event = $donnees_dlp['id_event'] ;

		if (preg_match("!$prenom_nom_comedien+[^a-zA-Z]!", $description_event) OR
		    preg_match("!$prenom_nom_comedien+[^a-zA-Z]!", $resume_event))
		{
			$debug.= ' --> le comedien "' . $prenom_nom_comedien . ' (ID ' . $id_comedien . ') 
			a été trouvé dans la <strong>description</strong> <br />' ;
			
			$new_array_lien = array (
			"id_event" => $id_event,
			"id_comedien" => $id_comedien,
			"url_comedien" => $url_comedien ) ;
			
			array_push ($array_id_comediens_a_ajouter, $new_array_lien);
		}
	}
}

/*echo '<pre>';
print_r($array_id_comediens_a_ajouter);
echo '</pre>';*/

if (!empty ($array_id_comediens_a_ajouter))
{
	mysql_query("TRUNCATE TABLE ag_comedien_lien") or die ("Erreur SQL -TRUNCATE- !" . mysql_error());
	
	foreach ($array_id_comediens_a_ajouter as $array_lien)
	{
		$debug.= ' + '. $array_lien['id_event'] . ' ** ' . 
		$array_lien['id_comedien'] . ' ** ' . 
		$array_lien['url_comedien'] . ' ** <br />' ;
		
		insert_table_ag_comedien_lien_pour_un_event($array_lien['id_event'], $array_lien['id_comedien'], $array_lien['url_comedien']);
	}
	

}

//--- mysql_close($db2dlp);

echo $debug ;

$time_end = microtime(true);
$time = $time_end - $time_start;
echo '<div class="info">Opération effectuée en ' . substr($time,0,5)/60 . ' minutes.</div>';

?>

</body>
</html>
