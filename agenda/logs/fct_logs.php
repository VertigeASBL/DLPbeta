<?php

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction pour enregistrer les modifications effectuées par les USER
// Exemple : log_write (qui provoque l'erreur, Type d'erreur cfr "$type_log_array", ID à passer, Description de l'erreur, Action à effectuer)
// $type_log fournit le type de modification : event, profil lieu, profil user...
// $context_id_log fournit l'ID du lieu, ou de l'événement... en fonction du contexte
// $description_log permet de mettre un message spécifique dans le rapport
// $action_log mentionne les actions que cette fonction va effectuer (envoyer e-mail, rapporter une erreur...)
// $type_log_array[$type_log]['1']
/* La variable des erreurs possibles est dans "agenda/inc_var"
Array
(
    [1] => Array
        (
            [0] => Fiche descriptive du lieu
            [1] => -Details-lieux-culturels-?id_lieu=
        )

    [2] => Array
        (
            [0] => Fiche événement
            [1] => -Detail-agenda-?id_event=
        )

    [3] => Array
        (
            [0] => Profil d'un lieu culturel
            [1] => agenda/admin_agenda/edit_user_agenda.php?id=
        )

)
*/

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function log_write ($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
{	
	require '../inc_var.php';
	require '../inc_var_dist_local.php';
	require '../inc_db_connect.php';

	/*echo '<pre>';
	print_r($type_log_array);
	echo '</pre>';*/
	

	// Recherche nom du LIEU correspondant
	$reponse_lieu_log = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = '$lieu_log'");
	$donnees_lieu_log = mysql_fetch_array($reponse_lieu_log);	
	$lieu_log_modif = $donnees_lieu_log ['nom_lieu'] ;
	
	$lieu_log_modif_info = $lieu_log_modif ;
	$id_lieu_log_modif_info = $lieu_log ;
	$page_modif_info = $type_log_array[$type_log]['0'] ;
	$context_id_log_info = $context_id_log ;
	$description_modif_info = $description_log ;
	
	$lien_page_modif_info = $racine_domaine . $type_log_array[$type_log]['1'] . $context_id_log ;

	$ip_log = $_SERVER['REMOTE_ADDR'] ;
	$timestamp_log = time();

	// ***********************************************************************************************
	// Mise en DB des infos
	// ***********************************************************************************************
	mysql_query("INSERT INTO `$table_logs` ( `id_log` , `lieu_log` , `type_log` , `context_id_log` , `timestamp_log` , `description_log` , `ip_log` , `action_log` ) 
	VALUES ('', '$lieu_log', '$type_log', '$context_id_log', '$timestamp_log', '$description_log', '$ip_log', '$action_log' )");


	// ***********************************************************************************************
	// Quelle action faut-il exécuter ?
	// ***********************************************************************************************

	// --------------------------------------------
	// Faut-il informer l'administrateur par e-mail
	// --------------------------------------------

	if (preg_match ('!send_mail!', $action_log))	
	{		
		$sujet=' | Activité sur le site DEMANDEZLEPROGRAMME.BE';
		
		$corps='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<style type="text/css">
		<!--
		' . $css_email . '
		-->
		</style></head><body>
		<p>&nbsp;</p><p>&nbsp;</p>
		<h1>Un utilisateur a effectué une modification sur le site :</h1>
		<p>&nbsp;</p>';
		
		//$type_log  $description_log
		
		$corps.= '<b>Lieu culturel concerné : </b>' . $lieu_log_modif_info . ' (id=' . $id_lieu_log_modif_info . ')' ;
		
		$corps.= '<br /> <br /><b>Page modifiée : </b><a href="' . $lien_page_modif_info . '">' . $page_modif_info . '</a> (id=' . $context_id_log_info . ')' ;

		$corps.= '<br /> <br /><b>Lien : </b>' . $page_modif_info . ' (id=' . $context_id_log_info . ')' ;

		$corps.= '<br /> <br /><b>Type de modification : </b>' . $description_modif_info ;

		$corps.='<br /> <br /><b>Date :</b> ' . date('d/m/Y - H\hi', $timestamp_log) . '<br />';

		$corps.='<p>&nbsp;</p>
		<p align="center"><a href="'.$racine_domaine.'agenda/admin_agenda/listing_logs.php"> 
		&gt; &gt; Listing &lt; &lt; </a></p>
		<p>&nbsp;</p> 
		</body></html>
		</html>'; 

		$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $retour_email_admin;
		
		//echo $corps ;
		/*$destinataire = 'renaud.jl@strategique.be' ;
	 mail($destinataire,$sujet,$corps,$entete); */
	 mail($email_admin_site,$sujet,$corps,$entete,$email_retour_erreur);
	}
}

//function log_write ($lieu_log, $type_log, $context_id_log, $description_log, $action_log)
//log_write ('1', '2', '3', 'description écrite', 'send_mail');

?>