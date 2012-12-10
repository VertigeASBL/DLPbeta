<?php

require '../../inc_db_connect.php';
require '../../inc_var.php';



$time_actuel_moins_5jours = time() - 3600*24*5;
//echo date('d-m-Y', $time_actuel_moins_5jours); 

	 

$reponse = mysql_query("SELECT * FROM ag_conc_avis WHERE cloture_conc < $time_actuel_moins_5jours");
while ($donnees = mysql_fetch_array($reponse))
{
	$id_conc_avis = $donnees['id_conc_avis'] ;
	echo '<br />Traitement ID ' . $id_conc_avis . '<br />' ;
	$id_joueur = $donnees['id_joueur'] ;
	$cloture_conc = $donnees['cloture_conc'] ;
	
	$reponse_ag_conc_joueur = mysql_query("SELECT * FROM $table_ag_conc_joueur 
	WHERE id_conc_joueur = '$id_joueur'");
	$donnees_ag_conc_joueur = mysql_fetch_array($reponse_ag_conc_joueur);
	
	$id_conc_joueur = $donnees_ag_conc_joueur['id_conc_joueur'] ;
	$id_fiche_conc_joueur = $donnees_ag_conc_joueur['id_fiche_conc_joueur'] ;
	$nom_joueur_conc = $donnees_ag_conc_joueur['nom_joueur_conc'] ;
	$mail_joueur_conc = $donnees_ag_conc_joueur['mail_joueur_conc'] ;
	$time_stamp_joueur = $donnees_ag_conc_joueur['time_stamp_joueur'] ;
	
	// plus d'infos sur le concours ?
	$reponse_ag_conc_fiches = mysql_query("SELECT * FROM $table_ag_conc_fiches 
	WHERE id_conc = '$id_fiche_conc_joueur'");
	$donnees_ag_conc_fiches = mysql_fetch_array($reponse_ag_conc_fiches);
	
	$cloture_conc_pour_avis = $donnees_ag_conc_fiches['cloture_conc'] ;
	$nom_event_conc = $donnees_ag_conc_fiches['nom_event_conc'] ;
	$event_dlp_conc = $donnees_ag_conc_fiches['event_dlp_conc'] ;

	
	
	// Tester s'il existe un événement DLP, ou si c'est un événement extérieur
	$concat = '' ;
	if ($event_dlp_conc!=0)
	{
		$concat.='<html><head>
		<title>Donnez votre avis</title>
		</head>
		<body><table align="center" width="600" border="0" cellspacing="0" cellpadding="10">
		<tr>
		<td colspan="2"><h3 align="center" style="color:#009A99; font-family:Arial; font-size:18px; ">Vous avez gagné des places sur 
		www.demandezleprogramme.be<br />Laissez votre avis sur cet événement !</h3></td>
		</tr>
		<tr>
		<td valign="top" ><img src="http://www.demandezleprogramme.be/agenda/concours/rappel_avis/perso_dlp.gif" alt="illustration" /></td>
		
		<td valign="top" ><p style="color:#009A99; font-family:Arial; font-size:13px; ">
		Bonjour ' . $nom_joueur_conc . ', <br /><br />
		
		Grâce à <em>demandezleprogramme</em>, vous avez remporté des places pour aller voir l\'événement <br />
		<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . $event_dlp_conc . '"><strong>' . $nom_event_conc . '</strong></a> <br /> <br /> 
			
		
		Aidez-nous à développer la communauté des spectateurs en déposant votre avis sur le site. <br /> 
		<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event=' . 
		$event_dlp_conc . '">cliquez ici pour déposer votre avis !</a>
		<br />  <br /> 
		 
		 En vous remerciant d\'avance,</p>
		
		<p style="color:#009A99; font-family:Arial; font-size:13px; "><em>L\'équipe de demandezleprogramme.be</em></p></td>
		</tr>
		<tr>
		<td colspan="2">
		<p style="color:#666666; font-family:Arial; font-size:10px; ">	Vertige asbl <br />
		<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
		<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
		Visitez également <a href="http://www.comedien.be">www.comedien.be</a> et 
		<a href="http://www.vertige.org">www.vertige.org</a><br />
		<em>[id joueur : ' . $id_joueur . ' - Concours : ' . $id_fiche_conc_joueur . ' - 
		Fiche jeux :' . $id_conc_joueur . ' - Date :' . $time_stamp_joueur . ' - 
		Clôture : ' . date('d-m-Y', $cloture_conc) . ']</em></p>
		</td>
		</tr>
		</table>
		
		</body>
		</html>';
		
		echo $concat ;


		$retour_email_moderateur = 'info@demandezleprogramme.be' ; 
		$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
		$sujet = 'Merci de déposer votre avis sur demandezleprogramme.be' ;
	 mail_beta($mail_joueur_conc,$sujet,$concat,$entete,$email_retour_erreur);	
	}
	
	// effacer l'entrée de la BD
	mysql_query("DELETE FROM ag_conc_avis WHERE id_conc_avis = '$id_conc_avis' LIMIT 1");

}
echo ' OK ' ;


						
?>