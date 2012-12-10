
<span class="lien_retour_public"><a href="-Agenda-" > &lt;&lt; Retour</a></span>

<?php 
require 'agenda/inc_var.php';
require 'agenda/inc_fct_base.php';

// Récupération de l'ID du lieu à afficher via $_GET
if (empty ($_GET['id_lieu']) OR $_GET['id_lieu'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="index.php" >Retour</a></div>' ;
}
else
{
	$id_lieu = htmlentities($_GET['id_lieu'], ENT_QUOTES);
	$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$id_lieu'");
	$donnees = mysql_fetch_array($reponse);
 
	// Si la valeur de $_GET['id_lieu'] ne correspond à aucune entrée de la TABLE :
	if (empty ($donnees))
	{
		echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>
		<a href="index.php" >Retour</a></div>' ;
	}
	else
	{
		// ------------------------------------------------
		// Lecture des infos de la DB pour cette entrée
		// ------------------------------------------------
		
		$reponse = mysql_query("SELECT * FROM $table_lieu WHERE id_lieu = '$id_lieu'");
		$donnees = mysql_fetch_array($reponse);
		
		$nom_lieu = $donnees ['nom_lieu'];
		$directeur_lieu = $donnees ['directeur_lieu'];
		$contact_lieu = $donnees ['contact_lieu'];
		$tel_lieu = $donnees ['tel_lieu'];
		$e_mail_lieu = $donnees ['e_mail_lieu'];
		$web_site_lieu = $donnees ['web_site_lieu'];
		$adresse_lieu = $donnees ['adresse_lieu'];
		$pic_lieu = $donnees ['pic_lieu'];


		$tab= '<div class="detail_rubr_inc">
		<div class="head_detail_rubr_inc">' ;
		// ____________________________________________
		// PHOTO LIEU	
		if (isset ($donnees ['pic_lieu']) AND $donnees ['pic_lieu'] == 'set' )
		{
			$destination = 'vignettes_lieux_culturels/vignette_fiche_lieu_' . $id_lieu .'_1.jpg' ;
			$pic_fiche_lieu = 'vignettes_lieux_culturels/pic_fiche_lieu_' . $id_lieu .'_1.jpg' ;
			$tab.= '<a href="agenda/' . $pic_fiche_lieu . '" target="_blank" style="text-decoration:none;" >
			<img src="agenda/'. $destination . '" title="' . $nom_lieu . '" style = "border: 1px solid #000000; 	background-color: #FFFFFF; padding: 1px; text-decoration:none; 	float:left; margin:0px 20px 10px 0px;" >
			</a>';
		}
		
	
		// ____________________________________________
		// NOM DU LIEU
		$tab.= '<div class="head_detail_rubr_inc_h2">' . $nom_lieu . '</div>
		</div> 
		<div class="float_stop"><br /></div>';
			
		
		$tab.= '<br />' ;
	
	
		// ____________________________________________
		// ADRESSE	
		if (isset ($adresse_lieu) AND $adresse_lieu != NULL)
		{
			$tab.=  '<b>Adresse</b> : ' . $adresse_lieu ;
		}
	
	
		// ____________________________________________
		// TELEPHONE	
		if (isset ($tel_lieu) AND $tel_lieu != NULL)
		{
			$tab.=  '<br /><br /><b>Téléphone</b> : ' . $tel_lieu ;
		}
	
	
		// ____________________________________________
		// E-MAIL	
		if (isset ($e_mail_lieu) AND $e_mail_lieu != NULL)
		{
			$tab.=  '<br /><br /><b>E-mail</b> : <a href="mailto:' . $e_mail_lieu . '">' . $e_mail_lieu . '</a>';
		}
	
	
		// ____________________________________________
		// WEB SITE	
		if (isset ($web_site_lieu) AND $web_site_lieu != NULL)
		{
			$tab.=  '<br /><br /><b>Site Web</b> : <a href="' . $web_site_lieu . '" title="Visiter le site Web de '
			. $nom_lieu . '">' . $web_site_lieu . '</a>'  ;
		}
		$tab.= '<br />' ;
	
	
		// ____________________________________________
		// AFFICHER LES PROCHAINS EVENEMENTS
		$date_debut_to_url = date ('d-m-Y');
		$date_fin_to_url = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m") , date("d"), date("Y")+2));
	
		$tab.=  '<br /><img src="agenda/design_pics/loupe.jpg" /> 
		<a href="-Agenda-?req=ext&date_debut=' . $date_debut_to_url . '&date_fin=' . $date_fin_to_url . '&lieu=' . $id_lieu . '"> 
		Afficher les prochains événements du lieu</a><br />' ;
		
		// ____________________________________________
		// CONTACT DU LIEU (Infos utiles sur le lieu)
		if (isset ($contact_lieu) AND $contact_lieu != NULL)
		{
			$tab.= '<br /> <br /> <b>Infos utiles</b> : <br /> <br /> ' . $contact_lieu ;
		}
		
		echo $tab .  '</div>' ;

	}
} 

?>

