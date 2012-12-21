<?php 
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>J'ai vu et aimé !!! </title>

<style type="text/css">
<!--
body {
	background-color: #01A7A7;
	background:#FFFFFF url(bg_general2.jpg) repeat-x scroll center top;
	font-family:Arial,sans-serif;
	margin:5px;
	font-size:14px;
}

a {
	color:#009A99;
	text-decoration:none;
}
a:hover {
	color:#009ABB;
	text-decoration:underline;
}
h1 {
	color: #FFFFFF;
	height:50px;
	padding:10px;
	overflow:hidden;
	font-size: 20px;
	text-align: center;
}
h2 {
color:#333333;
font-family:Georgia,Arial,Times,serif;
font-size:1.8em;
margin-top:0;
}
h3 {
color:#8F0033;
font-family:Georgia,Arial,Times,serif;
font-size:1.4em;
text-transform:uppercase;
}-->
</style></head>

<body>

<?php
if (isset($_GET['id']) AND preg_match('/[0-9]$/', $_GET['id']))
{
	$id_event_en_cours = htmlentities($_GET['id'], ENT_QUOTES);
	require '../inc_var.php';
	require '../inc_db_connect.php';	
	require '../inc_fct_base.php';


	$reponse = mysql_query("SELECT id_event,nom_event,description_event,pic_event_1,jai_vu_event FROM $table_evenements_agenda 
	WHERE id_event = '$id_event_en_cours'");
	$donnees = mysql_fetch_array($reponse);
	
	// Si l'entrée n'existe pas, bloquer le script car ça peut être une attaque...
	if (empty($donnees['id_event']))
	{
		echo '<br /> <br /> <br /> <br /> <br /> <br />  
		<div align="center">
			<strong>!! ERREUR !! <br /> Cette entrée ne semble pas exister.<br /> 
			Merci d\'informer l\'administrateur du site de cette erreur : 
			<a href="mailto:' . $retour_email_admin . '">' . $retour_email_admin . '</a></strong>
		</div>';
		exit() ;
	}
	

	$id_event = $donnees['id_event'];
	$nom_event = $donnees['nom_event'];
	
	echo '
	<h1>J\'ai vu et aimé <br />"' . raccourcir_chaine($nom_event,50) . '" </h1>' ;

	// Le visiteur a appuyé sur le bouton "Voter"
	if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Voter !')) 
	{
		// Vérifier si le visiteur a déja voté pour cet événement. Le test porte sur IP, Event et date (1 vote par jour) 
		$timestamp_jai_vu = time();
		$timestamp_hier = time()-(24*3600);
		$ip_jai_vu = $_SERVER['REMOTE_ADDR'] ;
		$navigateur_jai_vu = $_SERVER['HTTP_USER_AGENT'] ;// Type de navigateur
		
		$reponse_test_2 = mysql_query("SELECT COUNT(*) AS nombre_reponse FROM ag_jai_vu WHERE
		id_event_jai_vu = '$id_event_en_cours' AND		
		timestamp_jai_vu > SUBDATE( timestamp(now()), INTERVAL 24 HOUR) AND
		ip_jai_vu = '$ip_jai_vu' ") or die ('Erreur 1 -- ' . mysql_error());
	//	

		$donnees_test_2 = mysql_fetch_array($reponse_test_2);
		
		if ($donnees_test_2['nombre_reponse'] > 0) 
		//if (1==5)
		{
			echo '
			<br /> <br />
			<div align="center">
			<strong>Vous avez déjà voté</strong> pour cet événement il y a moins de 24 heures. <br /> 
			Vous pouvez voter pour d\'autres événements, ou même revoter pour celui-ci en revenant demain. <br /> <br />
			
			L\'équipe de <em><a href="http://www.demandezleprogramme.be/">Demandezleprogramme !</a></em>
			</div>' ;
		}
		else
		{
			echo '
			<br /> <br />
			<div align="center">Merci d\'avoir voté pour cet événement ! <br /> <br />
			
			L\'équipe de <em><a href="http://www.demandezleprogramme.be/">Demandezleprogramme !</a></em>
			</div>' ;
		
			// Mettre à jour la Table "ag_jai_vu" afin de tenir compte du vote
			mysql_query("INSERT INTO ag_jai_vu (id_event_jai_vu,timestamp_jai_vu,ip_jai_vu) 
			VALUES ('$id_event_en_cours',NOW( ),'$ip_jai_vu')") or die ('Erreur 2 -- ' . mysql_error());
		
			// Mettre à jour la le nombre de votes recueillis par l'événement
			mysql_query("UPDATE $table_evenements_agenda SET jai_vu_event = jai_vu_event+1 WHERE `id_event` = '$id_event_en_cours' LIMIT 1 ");
			
			include_once('../activite/activite_fonctions.php');
			activite_log ('vu', $id_event);
			
			// Prévenir Xavier
			/*$mail_concat = '<h1>Un visiteur a voté pour un événement</h1> <br />
			<p>Date : ' . date('d-m-Y @ H\hi',$timestamp_jai_vu) . '</p>
			<p>Nom de l\'événement : <strong>' . $nom_event . '</strong> (id ' . $id_event . ')</p> <br />
			<p align="center"><a href="http://www.demandezleprogramme.be/agenda/admin_agenda/edit_event.php?id=' . 
			$id_event . '" target="_blank"> &gt; &gt; Modifier &lt; &lt; </a></p> <br />
			
			<p style="font-size:9px">IP du visiteur : ' . $ip_jai_vu . '<br />
			Navigateur du visiteur : ' . $navigateur_jai_vu . ' </p>' ;
			
			// $email_admin_site='renaud.jeanlouis@gmail.com' ; 
			$entete= "Content-type:text/html\nFrom:" . $retour_email_admin . "\r\nReply-To:" . $email_admin_site ;
			$sujet_encode = '¤ VOTE sur demandezleprogramme';
			$sujet = html_entity_decode($sujet_encode, ENT_QUOTES) ;
		 mail_beta($email_admin_site,$sujet,$mail_concat,$entete);*/

		}
		
		// Bouton pour fermer la fenêtre et relancer la page quand le popup de vote est fermé, et ainsi afficher le nouveau nombre de votes
		echo '<br /> <br /> <form><div align="center"><input type="button" value="fermer la fenêtre" onclick="window.close(); window.opener.location.reload();" /></div></form>';
	}
	else
	{
		// Si le visiteur n'a pas appuyé sur le bouton "Voter", lui montrer le formulaire
		
		echo '
		<table width="95%" border="0" cellspacing="0" cellpadding="5">
		<tr>
		<td valign="top">' ;
		
			
		if (isset ($donnees ['pic_event_1']) AND $donnees ['pic_event_1'] == 'set' )
		{
			echo '<img src="../' . $folder_pics_event . 'event_' . $id_event_en_cours . 
			'_1.jpg" title="' . $nom_event . '" />';
		}
		
		echo '
		</td>
		<td align="center" valign="top"><br /> <br /> Vous allez voter pour l\'événement <br />
		<strong> "' . $nom_event . '" </strong></span> <br /> <br />' ;
		
		if($donnees ['jai_vu_event'] == 0)
		{
			echo 'Il n\'y a encore aucun vote';
		}
		else
		{
			echo 'Il y a déjà ' . $donnees ['jai_vu_event'] . ' votes';
		}
		
		
		
		 echo '<br /> <br /> 
		 <form name="form1" method="post" action="">
			<div align="center"> <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Voter !"> </div>
		 </form>
	
		</td>
		</table>';

	}
}


//--- mysql_close($db2dlp);
 
?>


</body>
</html>
