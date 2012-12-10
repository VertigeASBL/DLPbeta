<style type="text/css">
<!--
div.breve ul {
	font-size: 10px;
	margin-left: 60px;
}
-->
</style>
<!-- <img src="agenda/temp_modele_spectateur.jpg" width="673" height="235" /> -->

<?php
require 'agenda/inc_var.php';
require 'agenda/inc_db_connect.php';
require 'agenda/inc_fct_base.php';


$items_par_page = 20; // Nombre de profils à afficher par page


//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Listing des Spectateurs qui ont ouvert un compte sur DLP
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii


// --------------------------------------------------------------------------------
// Le rédacteur est-il un SPECTATEUR authentifié ou un simple visiteur ?
// --------------------------------------------------------------------------------
if (isset($_SESSION['group_admin_spec']) AND $_SESSION['group_admin_spec'] == 1)
{
	$qui_redacteur = 'spectateur' ; // Le joueur est un SPECTATEUR authentifié
	$id_spectateur = $_SESSION['id_spectateur'] ;
	$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE id_spectateur = '$id_spectateur'");
	$donnees = mysql_fetch_array($reponse);
		
	$prenom_spectateur = $donnees ['prenom_spectateur'];
	$nom_spectateur = $donnees ['nom_spectateur'];
	$pseudo_spectateur = $donnees ['pseudo_spectateur'];
	$e_mail_spectateur = $donnees ['e_mail_spectateur'];
	$tel_spectateur = $donnees ['tel_spectateur'];
	$log_spectateur = $donnees ['log_spectateur'];
	$pw_spectateur = $donnees ['pw_spectateur'];
	$sexe_spectateur = $donnees ['sexe_spectateur'];
	$description_courte_spectateur = $donnees ['description_courte_spectateur'];
	$description_longue_spectateur = $donnees ['description_longue_spectateur'];
	$pic_spectateur = $donnees ['pic_spectateur'];
	$artiste_prefere_spectateur = $donnees ['artiste_prefere_spectateur'];
	$lieu_prefere_spectateur = $donnees ['lieu_prefere_spectateur'];
	
	// Si le compte est bloqué, empêcher le spectateur d'y accéder
	if ($donnees['compte_actif_spectateur'] == 'non')
	{
		echo '<br /> <div class="alerte"><br />Votre comte a été bloqué par un administrateur du site. <br />Vous ne pouvez plus participer en tant que Spectateur. Pour plus d\'infos : info@demandezleprogramme.be<br /><br /></div>' ;
		exit () ;
	}
	
	// Si le compte est créé, mais pas totalement complété ($compte_actif_spectateur = "new"), inviter le Spectateur à finaliser la chose via sin admin
	if ($donnees['compte_actif_spectateur'] == 'new')
	{
		echo '<br /> <div class="alerte"><br />Votre comte n\'est pas encore totalement paramétré. Veuillez vous rendre dans votre espace d\'administration personnel et compléter votre profil : 
		<a href="agenda/spectateurs_admin/edit_profile_spectateur.php">espace personnel</a>.<br />
		Ensuite, il vous sera possible de participer aux concours tout en multipliant vos chances de gain !<br /><br /></div>' ;
		exit () ;
	}
}
else
{
	$qui_redacteur = 'visiteur' ; // Le joueur est un simple visiteur
}


	


echo '<table width="100%" style="background-color: #EEEEEE; margin-left:2px" border="0" cellpadding="5" cellspacing="0">
  <tr>' ;
  
// **************************************************
// Affichage du nombre de spectateurs :
// **************************************************
$retour_compte_spectateurs = mysql_query("SELECT COUNT(*) AS nbre_spectateurs_visibles FROM ag_spectateurs WHERE compte_actif_spectateur = 'oui'");
$donnees_compte_spectateurs = mysql_fetch_array($retour_compte_spectateurs);
$_tot_spectateurs_visibles = $donnees_compte_spectateurs['nbre_spectateurs_visibles'];
echo '<td><p><strong>Il y a actuellement 
<span style="color:#099A99">' . $_tot_spectateurs_visibles . '</span> Spectateurs inscrits</strong></p></td>' ;
	
  
 // **************************************************
// Afficher les étoiles :
// **************************************************
 
echo '<td align="right">' ;

$etoile_haut = trouve_categorie_spectateur (150) ; 
echo '<img src="agenda/design_pics/spectateurs/' . $etoile_haut['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $etoile_haut['categorie_spectateur'] . '" /> ' ;

$etoile_haut = trouve_categorie_spectateur (49) ; 
echo '<img src="agenda/design_pics/spectateurs/' . $etoile_haut['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $etoile_haut['categorie_spectateur'] . '" /> '; 

$etoile_haut = trouve_categorie_spectateur (19) ; 
echo '<img src="agenda/design_pics/spectateurs/' . $etoile_haut['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $etoile_haut['categorie_spectateur'] . '" /> '; 

$etoile_haut = trouve_categorie_spectateur (9) ; 
echo '<img src="agenda/design_pics/spectateurs/' . $etoile_haut['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $etoile_haut['categorie_spectateur'] . '" /> '; 

$etoile_haut = trouve_categorie_spectateur (0) ; 
echo '<img src="agenda/design_pics/spectateurs/' . $etoile_haut['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $etoile_haut['categorie_spectateur'] . '" /> '; 

echo '<br /> </td></tr></table>' ; 

?>
<!-- En tête avec photo du SPECTATEUR s'il est loggé + liens... -->
<table class="pub" width="100%" style="background-color: #D9D9D9; margin-left:2px" border="0" cellpadding="10" cellspacing="0">
<?php if ($qui_redacteur == 'spectateur')
{
?>  
  <tr>
    <td><?php
// Afficher image spectateur
if (isset ($donnees ['pic_spectateur']) AND $donnees ['pic_spectateur'] == 'set' )
{
	echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_' . $id_spectateur . '_1.jpg" alt="Photo de ' . $pseudo_spectateur . '" title="' . $pseudo_spectateur . '" />';
}
else
{
	if ($donnees ['sexe_spectateur'] == 0)
	{
		echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_homme.jpg" alt="spectateur anonyme" />';
	}
	else
	{
		echo '<img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_femme.jpg" alt="spectatrice anonyme" />';
	}
}		?>
	</td>
      <td>
	  <?php
   		$_tot_entrees = connaitre_nb_avis_spect ($pseudo_spectateur) ;
	
	// Correspondance AVIS postés <-> Grade et icone des spectateurs
	$result_categorie_spectateur = trouve_categorie_spectateur ($_tot_entrees) ; 
	 
	/*echo '<img src="agenda/design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /><br />'; */
	
	$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
	
	echo '<p><strong>Bienvenue ' . $pseudo_spectateur . ' ! </strong><br /> <br />
	Vous êtes actuellement connecté' ;
	($sexe_spectateur)?($terminaison='e'):($terminaison='');
	echo $terminaison . ' sous votre pseudo.<br />Si vous désirez, vous pouvez 
	<a href="agenda/spectateurs_admin/votre_menu_spectateur.php">
	mettre à jour votre profil</a>.</p>' ;
	
	/*Vous êtes connecté(e) et vous augmentez donc vos chances de gain lors des tirages 
	de ' .  $result_fact_chance['valeur_facteur_chance'] */
	
	?>
	  </td>
  </tr>

<?php  
}
else
{
?>

  <tr class="pub">
    <td><img src="agenda/design_pics/communaute_spectateurs.gif" alt="Communaut&eacute; des spectateurs de Demandezleprogramme" border="0" /></span></td>
    <td valign="top"><p><strong>Rejoignez la communauté des spectateurs ! 
	Créer votre profil et augmentez vos chances aux concours !</strong></p>
        <ul>
          <li><a href="agenda/spectateurs_admin/votre_menu_spectateur.php">Me connecter &agrave; mon compte (Identification)</a></li>
          <li><a href="agenda/spectateurs_admin/ins/a_1.php">M'inscrire gratuitement</a> </li>
    </ul></td>
  </tr>
  

<?php  
}
?>
</table><br />
<?php


// **************************************************
// Affichage des infos sur les spectateurs :
// **************************************************

$tab =' ';
$nombreDePages  = ceil($_tot_spectateurs_visibles / $items_par_page); // Nombre de pages pour lesquelles créer un lien

if (isset($_GET['page_en_cours']) AND preg_match('`^\w+$`', $_GET['page_en_cours']))
{
	$page_en_cours = htmlentities($_GET['page_en_cours']); 
}
else // La variable n'existe pas, donc afficher la  première page
{
	$page_en_cours = 1;
}

$premier_spectateur_page = ($page_en_cours - 1) * $items_par_page;

// Affichage liens vers pages
$tab_liens = '<div align="center" style="font-size:14px"> - ' ;
for ($i_page = 1 ; $i_page <= $nombreDePages ; $i_page++)
{
	$debut_sequence = ((($i_page - 1) * $items_par_page)+1) ;
	// S'il s'agit de la dernière page, afficher exactement le dernier item
	if (((($i_page - 1) * $items_par_page) + $items_par_page) > $_tot_spectateurs_visibles)
	{ $fin_sequence = $_tot_spectateurs_visibles ; }
	else { $fin_sequence = ((($i_page - 1) * $items_par_page) + $items_par_page) ; }	
	
    if ($i_page == $page_en_cours)
	{
		// Affichage avec numéros exacts des éléments
		/*$tab_liens.= $debut_sequence . '..' . $fin_sequence
		 . ' - ';	*/
		$tab_liens.= $i_page
		 . ' - ';
	}
	else
	{
		// Affichage avec numéros exacts des éléments
		/*$tab_liens.= '<a href="-Communaute-des-spectateurs-?page_en_cours=' . $i_page . '">' . 
		$debut_sequence . '..' . $fin_sequence
		 . '</a> - ';*/
		 
		$tab_liens.= '<a href="-Communaute-des-spectateurs-?page_en_cours=' . $i_page . '">' . 
		$i_page
		 . '</a> - ';
		 
	}
}
$tab_liens.= '</div>' ;

$tab.= $tab_liens ;

$reponse = mysql_query("SELECT * FROM $table_spectateurs_ag WHERE compte_actif_spectateur = 'oui' 
ORDER BY avis_valides_spectateur DESC
LIMIT $premier_spectateur_page , $items_par_page");
while ($donnees = mysql_fetch_array($reponse))
{
	$prenom_spectateur = $donnees ['prenom_spectateur'];
	$nom_spectateur = $donnees ['nom_spectateur'];
	$sexe_spectateur = $donnees ['sexe_spectateur'];
	$pseudo_spectateur = $donnees ['pseudo_spectateur'];
	$e_mail_spectateur = $donnees ['e_mail_spectateur'];
	$tel_spectateur = $donnees ['tel_spectateur'];
	$log_spectateur = $donnees ['log_spectateur'];
	$pw_spectateur = $donnees ['pw_spectateur'];
	$description_courte_spectateur = $donnees ['description_courte_spectateur'];

	if (isset($donnees ['description_longue_spectateur']) AND ($donnees ['description_longue_spectateur'] != NULL))
	{	$description_longue_spectateur = $donnees['description_longue_spectateur']; }
	else {	$description_longue_spectateur = '' ; }
	
	$pic_spectateur = $donnees ['pic_spectateur'];

	if (isset($donnees ['artiste_prefere_spectateur']) AND ($donnees ['artiste_prefere_spectateur'] != NULL))
	{	$artiste_prefere_spectateur = $donnees ['artiste_prefere_spectateur']; }
	else {	$artiste_prefere_spectateur = '' ; }
	
	if (isset($donnees ['lieu_prefere_spectateur']) AND ($donnees ['lieu_prefere_spectateur'] != NULL))
	{	$lieu_prefere_spectateur = $donnees ['lieu_prefere_spectateur']; }
	else {	$lieu_prefere_spectateur = '' ; }


	$compte_actif_spectateur = $donnees ['compte_actif_spectateur'];
	$avis_valides_spectateur = $donnees ['avis_valides_spectateur'];

	$tab.= '<div class="breve">' ;
	// ____________________________________________
	// PHOTO SPECTATEUR
	$id_spectateur = $donnees ['id_spectateur'] ;
	$tab.= '<span class="breve_pic">' ;
	
	if (isset ($donnees ['pic_spectateur']) AND $donnees ['pic_spectateur'] == 'set' )
	{
		$tab.= '<a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '"><img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_' . $id_spectateur . '_1.jpg" alt="Photo de ' . $pseudo_spectateur . '" title="' . $pseudo_spectateur . '" /></a>';
	}
	else
	{
		if ($donnees ['sexe_spectateur'] == 0)
		{
			$tab.= '<a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '"><img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_homme.jpg" alt="spectateur anonyme" /></a>';
		}
		else
		{
			$tab.= '<a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '"><img src="agenda/' . $folder_pics_spectateurs . 'vi_spect_anonyme_femme.jpg" alt="spectatrice anonyme" /></a>';
		}
	}			
	$tab.= '</span>' ;
	// ____________________________________________
// PSEUDO - GRADE - NOMBRE AVIS

	$prenom_spectateur = $donnees['prenom_spectateur'] ;// Raccourcir la chaine :
	$max=20; // Longueur MAX de la chaîne de caractères
	$chaine_raccourcie = raccourcir_chaine ($prenom_spectateur,$max); // retourne $chaine_raccourcie
	 
	$tab.= '<span style="font-size: 15px"><strong><a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '" title="voir le profil de ce spectateur" >' . $pseudo_spectateur . '</a></strong></span>' ;


	// AFFICHER ID (DISCRETEMENT)
	$tab.= '<span class="id_breve">(ID' . $id_spectateur . ')</span>';

	// Nombre d'avis déposés par ce spectateur :
	$retour_3 = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur'");
	$donnees_3 = mysql_fetch_array($retour_3);
	$_tot_entrees = $donnees_3['nbre_entrees'];

	// Correspondance AVIS postés <-> Grade et icone des spectateurs
	
	$result_categorie_spectateur = trouve_categorie_spectateur ($avis_valides_spectateur) ; 
 
	$tab.= '<br /><span style="font-size: 13px">' . $result_categorie_spectateur['categorie_spectateur'] . ' </span> 
	<span class="help_cursor">
	<img src="agenda/design_pics/spectateurs/' . $result_categorie_spectateur['icone_spectateur'] . '" alt="Votre score" align="top" title="' . $result_categorie_spectateur['categorie_spectateur'] . '" /></span> '; 

	// Score pour la saison actuelle / Total :
	$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; // Appel fonction correspondance AVIS <-> CHANCE
	$tab.= '<strong><span title="Avis déposés cette saison" class="help_cursor"> ' . $avis_valides_spectateur . '</span></strong><br />' ;

	// Appel fonction correspondance AVIS <-> CHANCE
	$result_fact_chance = calcul_facteur_chance ($avis_valides_spectateur) ; 
	$tab.= '<span style="font-size: 10px">
	Coefficient concours : <strong>X ' . $result_fact_chance['valeur_facteur_chance'] . '
	</strong></span><br />' ;

	// Nombre total des avis pour ce spectateur :
	$tab.= '<span style="font-size: 10px">
	<strong> ' . $_tot_entrees . '</strong> avis déposé(s) actuellement</span><br />' ;
	
	
	// DESCRIPTIONS COURTE OU LONGUE DU SPECTATEUR
	$tab.= '<br />';
	// Utiliser ID à partir de 100000
	// *** Texte COURT ***
	$zone = 'Zone' . ($id_spectateur + 100000) ;
	$zoneB = 'Zone' . ($id_spectateur + 100000) . 'B' ;
	$ZoneLink = 'Zone' . ($id_spectateur + 100000) . 'Link' ;

	$tab.= '<div id="'.$zone.'" style="display:inline;">' . $description_courte_spectateur . ' <a href="javascript:toggle_zone(';
	$tab.= "'$zone','Afficher la suite','Replier'); " ;
	$tab.= '" id="' . $ZoneLink . '"> &gt;&gt; Lire plus </a></div> <br />' ;
	
	// *** Texte LONG ***
	$tab.= '<div id="'.$zoneB.'" style="display:none;">' . $description_longue_spectateur . '<a href="javascript:toggle_zone(' ;
	$tab.= "'$zone','Afficher la suite','Replier'); " ;
	$tab.= '" id="' . $ZoneLink . '"> &lt;&lt; Afficher le résumé</a></div> <br />' ;


	$tab.= '<div class="float_stop"></div>';
	
	// THEATRE PREFERES
	if ($lieu_prefere_spectateur != '')
	{
		$tab.= '<strong>Lieu(x) culturel(s) favori(s)</strong> : ' . $lieu_prefere_spectateur . '<br />' ;
	}

	// ARTISTES PREFERES
	if ($artiste_prefere_spectateur != '')
	{
		$tab.= '<strong>Artiste(s) apprécié(s)</strong> : ' . $artiste_prefere_spectateur . '<br />' ;
	}	
	
	// Derniers spectacles évalués :
	if ($_tot_entrees = $donnees_3['nbre_entrees'] > 0)
	{
	
		$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE nom_avis = '$pseudo_spectateur' AND publier_avis = 'set' ORDER BY id_avis DESC LIMIT 3");
		
		$tab.= '<strong>Derniers événements évalués</strong> : <ul style="margin:0 0 0 0;">' ;
		while ($donnees_avis = mysql_fetch_array($reponse_avis))
		{	
			$event_avis = $donnees_avis['event_avis'] ; // Récupération du nom du ce spectacle
			$nom_avis = $donnees_avis['nom_avis'] ;
			$reponse_spectacle = mysql_query("SELECT nom_event FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
			$donnees_spectacle = mysql_fetch_array($reponse_spectacle);
			
			$tab.= '<li><a href="-Detail-agenda-?id_event=' . $event_avis . '#avis" title="Voir son avis">' . $donnees_spectacle ['nom_event'] . '</a> 
			(' . date('d/m/Y', $donnees_avis ['t_stamp_avis']) . ')</li>';	
		}
		$tab.= '</ul>' ;
	}
	
	$tab.= '<div align="right">
	<a href="-Detail-d-un-spectateur-?id_spect=' . $id_spectateur . '" title="voir le profil de ce spectateur" >
	<img src="agenda/design_pics/spectateurs/bouton_profil_complet.gif" alt="Profil complet du spectateur" title="Voir le profil complet de ' . $pseudo_spectateur . '" >
	</a></div>' ;
	$tab.= '</div>' ;
}

$tab.= $tab_liens ;
echo $tab . ' '  ;

?>