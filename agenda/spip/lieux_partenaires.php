
<p>

<?php 
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';


//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des Lieux culturels
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// EN TETE TABLE
$tab ='<br /><table width="650" border="0" align="center" cellpadding="5" cellspacing="1" class="table_public" >
  <!-- <tr>
    <th>Logo</th>
    <th>Nom</th>
    <th>Contact</th>
  </tr> -->' ;
		

$reponse = mysql_query("SELECT * FROM $table_lieu WHERE cotisation_lieu > SUBDATE(CURDATE(), INTERVAL 1 MONTH) ORDER BY nom_lieu ");
while ($donnees = mysql_fetch_array($reponse))
{
	$id_lieu = $donnees ['id_lieu'];
	$nom_lieu = $donnees ['nom_lieu'];
	$directeur_lieu = $donnees ['directeur_lieu'];
	$contact_lieu = $donnees ['contact_lieu'];
	$tel_lieu = $donnees ['tel_lieu'];
	$e_mail_lieu = $donnees ['e_mail_lieu'];
	$web_site_lieu = $donnees ['web_site_lieu'];
	$adresse_lieu = $donnees ['adresse_lieu'];
	$pic_lieu = $donnees ['pic_lieu'];
	
	
	$tab.= '<tr><td align="center" height="110" width="110">' ;

	// LOGO
	if (isset ($donnees ['pic_lieu']) AND $donnees ['pic_lieu'] == 'set' )
	{
		$destination = 'vignettes_lieux_culturels/vignette_fiche_lieu_' . $id_lieu .'_1.jpg' ;
		$tab.= '<a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="' . $nom_lieu . '" style="text-decoration:none;"><img src="agenda/'. $destination . '" style = "border: 1px solid #000000; 	background-color: #FFFFFF; padding: 1px; " ></a>';

	}	
	
	$tab.= '</td><td valign="top" class="table_public" width="260" >' ;


	// NOM
	$tab.= '<a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="Cliquer pour obtenir plus d\'infos sur ce lieu culturel"><h2>'.$nom_lieu.'</h2></a>' ;
	
	// Nombre de fiches d'événements pour ce lieu culturel :
	$id_lieu = $donnees ['id_lieu'] ;
	$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_evenements_agenda WHERE lieu_event = '$id_lieu'
	AND date_event_debut != '0000-00-00' AND date_event_fin != '0000-00-00' AND date_event_fin > CURDATE() ");
	$donnees_3 = mysql_fetch_array($retour_3);
	$_tot_entrees = $donnees_3['nbre_entrees'];
	($_tot_entrees <=1) ? ($priiip = événement) : ($priiip = événements) ;

	$date_debut_to_url = date ('d-m-Y');
	$date_fin_to_url = date ('d-m-Y', $date_fin = mktime(0, 0, 0, date("m") , date("d"), date("Y")+2));

	$tab.= '<a href="-Agenda-?req=ext&date_debut=' . $date_debut_to_url . '&date_fin=' . $date_fin_to_url . '&lieu=' . $id_lieu . '"> &gt;&gt; ' . $_tot_entrees . ' ' . $priiip . '
	 à venir</a><br />
	<a href="-Details-lieux-culturels-?id_lieu='.$id_lieu.'" title="Cliquer pour obtenir plus d\'infos sur ce lieu culturel"> &gt;&gt; Infos</a>';

	
	$tab.= '</div></td><td>' ;

	// ____________________________________________
	// INFOS PRATIQUES
	
	$tab.=  '<ul>' ;

	// ADRESSE	
	if (isset ($adresse_lieu) AND $adresse_lieu != NULL)
	{
		$tab.=  '<li><b>Adresse</b> : ' . $adresse_lieu . '</li>' ;
	}
	// TELEPHONE	
	if (isset ($tel_lieu) AND $tel_lieu != NULL)
	{
		$tab.=  '<li><b>Téléphone</b> : ' . $tel_lieu . '</li>' ;
	}
	// E-MAIL	
	if (isset ($e_mail_lieu) AND $e_mail_lieu != NULL)
	{
		$tab.=  '<li><b>E-mail</b> : <a href="mailto:' . $e_mail_lieu . '">' . $e_mail_lieu . '</a> </li>' ;
	}
	// WEB SITE	
	if (isset ($web_site_lieu) AND $web_site_lieu != NULL)
	{
		$tab.=  '<li><b>Site Web</b> : <a href="'. $web_site_lieu . '" title="Visiter le site Web de '
		. $nom_lieu . '">' . $web_site_lieu . '</a></li>'  ;
	}
	$tab.=  '</ul>' ;

	$tab.= '&nbsp;</td></tr>' ;
}
$tab.= '</table>' ;

echo $tab ;

?>
