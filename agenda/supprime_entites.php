<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Supprime Entités</title>
<link href="css_back_agenda.css" rel="stylesheet" type="text/css">
</head>

<body>


<div id="head_admin_agenda"></div>
<h1>Supprime Entités</h1>

<p> <em>!! Ce script doit &ecirc;tre modifi&eacute; avant d'&ecirc;tre lanc&eacute; !!</em> </p>

<?php

require 'inc_var.php';
require 'inc_fct_base.php';
require 'inc_db_connect.php';


$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event >= 1 AND id_event <= 2 ") ;
while ($donnees = mysql_fetch_array($reponse))
{
	$id = $donnees['id_event'] ;
	echo '<div><p><strong>' . $id . '</strong> : </p>' ;

	// NOM DE L'EVENEMENT 
	if (isset($donnees['nom_event']) AND ($donnees['nom_event'] != NULL)) 
	{
		$nom_event = $donnees['nom_event'];
		$nom_event = str_replace("&#039;", "\'", $nom_event);
		$nom_event = str_replace("’", "\'", $nom_event);
		$nom_event = strip_tags($nom_event);
		$nom_event = html_entity_decode($nom_event);
		$nom_event = mysql_real_escape_string($nom_event);
		echo '<p><strong>Nom</strong> :' . stripslashes($nom_event) . '</p>' ;
		mysql_query("UPDATE `$table_evenements_agenda` SET `nom_event` = '$nom_event' WHERE `id_event` = '$id' LIMIT 1 ") or die ('<div class="alerte"><strong>Erreur requ NOM DE L EVENEMENT : ' .mysql_error().'</strong></div>') ;
	}
	else
	{
		echo '<div class="alerte">Pas de NOM ! </div>';
	}
	if(preg_match('!&!', $nom_event))
	{
		echo '<div class="alerte">********************************<br />
		il semble subsister une entité pour l\'id ' . $id . ' (titre)<br />********************************</div>';
	}
	
	
	
	// -----------------------------------------
	// TEST RESUME EVENEMENT
	if (isset($donnees['resume_event']) AND ($donnees['resume_event'] != NULL)) 
	{
		$resume_event = $donnees['resume_event'] ;
		$resume_event = str_replace("&#039;", "'", $resume_event);
		$resume_event = str_replace("&rsquo;", "'", $resume_event);
		$resume_event = str_replace("’", "'", $resume_event);
		$resume_event = str_replace("\r\n", "", $resume_event);
		$resume_event = str_replace("\n", "", $resume_event);	
		$resume_event = strip_tags($resume_event);
		$resume_event = html_entity_decode($resume_event);
		$resume_event = addslashes($resume_event);

		echo '<p><strong>Résumé</strong> :' . stripslashes($resume_event) . '</p>' ;

		mysql_query("UPDATE `$table_evenements_agenda` SET `resume_event` = '$resume_event' 
		WHERE `id_event` = '$id' LIMIT 1 ") or die ('<div class="alerte"><strong>Erreur requ RESUME EVENEMENT : ' .mysql_error().'</strong></div>') ;
	}
	else
	{
		echo '<div class="alerte">Pas de RESUME ! </div>';
	}
	if(preg_match('!&!', $resume_event))
	{
		echo '<div class="alerte">********************************<br />
		il semble subsister une entité pour l\'id ' . $id . ' (résumé)<br />********************************</div>';
	}
	


	// -----------------------------------------
	// TEST DESCRIPTION EVENEMENT 
	if (isset($donnees['description_event']) AND ($donnees['description_event'] != NULL)) 
	{
		$description_event = $donnees['description_event'] ;
		$description_event = str_replace("&#039;", "'", $description_event);
		$description_event = str_replace("&rsquo;", "'", $description_event);
		$description_event = str_replace("’", "'", $description_event);
		$description_event = str_replace("\r\n", "", $description_event);
		$description_event = str_replace("\n", "", $description_event);
		
		$allowedTags = '<strong><br><br />';
		$description_event = strip_tags($description_event, $allowedTags);
		
		$description_event = html_entity_decode($description_event);
		//$description_event = addslashes($description_event);
		//$description_event = stripslashes($description_event);
		$description_event = mysql_real_escape_string($description_event);

		echo '<p><strong>Descriptif</strong> :' . stripslashes($description_event) . '</p>' ;

		mysql_query("UPDATE `$table_evenements_agenda` SET `description_event` = '$description_event' 
		WHERE `id_event` = '$id' LIMIT 1 ") or die ('<div class="alerte"><strong>Erreur requ DESCRIPTION EVENEMENT : ' .mysql_error().'</strong></div>') ;
	}
	else
	{
		echo '<div class="alerte">Pas de DESCRIPTION ! </div>';
	}
	if(preg_match('!&!', $resume_event))
	{
		echo '<div class="alerte">********************************<br />
		il semble subsister une entité pour l\'id ' . $id . ' (descriptif)<br />********************************</div>';
	}
	echo '<hr /></div>';
}




?>
  
</body>
</html>