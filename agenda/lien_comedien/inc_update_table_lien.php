<?php

// iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
/* Si un événement de DLP contient un "Prénom Nom" de comedien, alors il faut effectuer une mise à jour
de la DB "ag_comedien_lien". 
Mais avant d'updater, il est nécessaire de supprimer toutes les entrées de la DB liées à cet événement
PS : passer par un Array permet de ne pas switcher de DB continuellement
PS : il est nécessaire de concaténer toutes les chaines susceptibles de contenir les noms avant d'appeler la fonction
*/
// # iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonctions d'update de la DB

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Supprimer toutes les entrées de la DB liées à cet événement
function delete_table_ag_comedien_lien_pour_un_event($id_event)
{
	/*global $debug;
	$debug.= '-> Fonction "delete_table_ag_comedien_lien_pour_un_event" appelée <br />';*/
	require '../inc_db_connect.php';
	mysql_query("DELETE FROM ag_comedien_lien WHERE 
	id_event_lien = $id_event ") or die ('Erreur SQL -1- !' . mysql_error());
	
	require '../inc_db_connect_to_comedien.php';
	return '-> Fonction "delete_table_ag_comedien_lien_pour_un_event" appelée 
	(event '. $id_event . ')<br />';

}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Ajouter toutes les entrées de la DB liées à cet événement
function insert_table_ag_comedien_lien_pour_un_event($id_event, $id_comedien, $url_comedien)
{
	//echo 'rrrrrrrrrr ' .$id_comedien . 'tttttttttt' ; ;
	/*global $debug;
	$debug.= '-> Fonction "insert_table_ag_comedien_lien_pour_un_event" appelée <br />';*/
	require '../inc_db_connect.php';
	mysql_query("INSERT INTO ag_comedien_lien (`id_lien` ,`id_event_lien` ,`id_comedien_lien` ,`url_comedien_lien`) VALUES ('','$id_event','$id_comedien','$url_comedien')") or die ("Erreur SQL -2- !" . mysql_error());

	require '../inc_db_connect_to_comedien.php';
	return '-> Fonction "insert_table_ag_comedien_lien_pour_un_event" appelée 
	(event '. $id_event . ' - comedien ' . $id_comedien . ' <br />';
}

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF


function update_table_ag_comedien_lien_pour_un_event($id_event, $contenu_texte)
{	
	// Connecter à la DB des Comediens
	require '../inc_db_connect_to_comedien.php';
	$debug = '' ;
	$array_id_comediens_a_ajouter = array() ; // Cet array sera transmis à la fonction d'update, après avoir effacé toutes les entrées de la DB liées à cet événement
	
	
	// PS : Dans la Requête, ID 1726 est là pour éviter Marion
	$reponse_comedien_lien = mysql_query("SELECT * FROM comediens WHERE (accord > 0) AND (ID != 1726) ");
	while ($donnees_comedien_lien = mysql_fetch_array($reponse_comedien_lien))
	{
		$id_comedien = $donnees_comedien_lien['ID'] ;
		$prenom_comedien = $donnees_comedien_lien['prenom'] ;
		$nom_comedien = $donnees_comedien_lien['nom'] ;
		$prenom_nom_comedien = $prenom_comedien . ' ' . $nom_comedien ;
		$url_comedien = $donnees_comedien_lien['url'] ;
		//echo ' <br />----- Prénom et Nom : ' . $prenom_nom_comedien ;
	
		/*$comedien_url = '<span class="un_nom_de_comedien_dans_description"><a href="http://www.comedien.be/' . $donnees_comedien_lien['url'] . '" title="Voir le profil sur le site comedien.be" target="_blank" style="color: #E38E0F;"><img src="agenda/design_pics/voir_comedien.gif" / height="12" align="bottom">' . $prenom_nom_comedien . '</a></span>' ; */
		//echo ' -- URL : ' . $comedien_url ;
		
		if (preg_match("!$prenom_nom_comedien+[^a-zA-Z]!", $contenu_texte))
		{
			$debug.= ' --> le comedien "<strong>' . $prenom_nom_comedien . '</strong>" <em>(ID ' . $id_comedien . ')</em> 
			a été trouvé dans la chaîne de texte<br />' ;
			echo ' --> le comedien "' . $prenom_nom_comedien . '" <em>(ID ' . $id_comedien . ')</em> 
			a été trouvé <br />' ;
			
			$new_array_lien = array (
			"id_event" => $id_event,
			"id_comedien" => $id_comedien,
			"url_comedien" => $url_comedien ) ;
			
			array_push ($array_id_comediens_a_ajouter, $new_array_lien);
		}
	}
	
	/*echo '<pre>';
	print_r($array_id_comediens_a_ajouter);
	echo '</pre>';*/
	
	$debug.= delete_table_ag_comedien_lien_pour_un_event($id_event);

	if (!empty ($array_id_comediens_a_ajouter))
	{
		foreach ($array_id_comediens_a_ajouter as $array_lien)
		{
			$debug.= '++ Ajouter pour l\'<strong>event '. $array_lien['id_event'] . '</strong> : ' . 
			$array_lien['url_comedien'] . ' <em>(id ' . $array_lien['id_comedien'] . ')</em><br /> ' ;
			
			$debug.= insert_table_ag_comedien_lien_pour_un_event($array_lien['id_event'], $array_lien['id_comedien'], $array_lien['url_comedien']);
		}
	}
	//echo $debug . ' <br /> ' ;
}
//--- mysql_close($db2dlp);

// # FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

//require 'agenda/inc_db_connect.php' ; // reconnecter à la DB DLP


/*$desciption = 'Votre site Web enregistre donc des visites de clients potentiels ciblés. Nicolas Mispelaere tarification au coût par clic (CPC) signifie que vous payez uniquement lorsque les utilisateurs cliquent sur votre annonce. Elle permet également de contrôler  Michelangelo Marchese les coûts plus facilement Yves Degen.' ;
$id_event = 222 ;

update_table_ag_comedien_lien_pour_un_event ($id_event, $desciption) ;
*/

?>
