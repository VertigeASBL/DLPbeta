<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Liens utiles pour l'agenda</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
<link rel="SHORTCUT ICON" href="favicon_admin.ico">
</head>

<body>

<?php
require '../inc_var.php';
require '../inc_db_connect.php';

?>

<h1 align="center">Liens utiles pour la gestion de l'agenda</h1>
<p align="center">&nbsp;</p>
<p>&nbsp;</p>


<?php
// Checker s'il faut procéder au tirage de concours
$time_actuel = time() ;
$reponse = mysql_query("SELECT * FROM $table_ag_conc_fiches WHERE cloture_conc < $time_actuel
AND flags_conc LIKE '%actif%' ORDER BY id_conc");
$donnees_var_exist= mysql_fetch_array($reponse) ;
if (!empty ($donnees_var_exist))
{
	echo '<div class="alerte"><br /> <br /> <br /> <br /> Il faut procéder au tirage d\'un concours. <br /> <br /> <br /> 
	<a href="../concours/conc_2_tirage.php?pw=s5fah7r6s3p6ax2">Cliquez ici</a><br /> <br /> <br /><br /> <br />
	</div>' ;
}

?>



<h2>&nbsp;</h2>
<h2>C&ocirc;t&eacute; ADMIN</h2>
<p><a href="listing_lieux_culturels.php">Liste des lieux culturels affili&eacute;s</a> (listing_lieux_culturels.php)</p>
<ul>
  <li>la visualisation de l'&eacute;tat des cotisations du lieu culturel</li>
  <li>les liens vers l'&eacute;diteur de fiches de lieux culturels</li>
  <li>le lien vers la liste de tous les &eacute;v&eacute;nements publi&eacute;s par un lieu culturel</li>
  <li>l' adresse e-mail de l'utilisateur du compte du lieu culturel, lien pour  l'&eacute;dition de son compte </li>
</ul>
<p><strong><br /> 
  Avis<br />
</strong><a href="avis_list_aprob.php">Avis &agrave; approuver par le mod&eacute;rateur</a></p>
<p>&nbsp;</p>
<p><a href="c.php">Liste des demandes d'affiliation en cours</a> (c.php) </p>
<p><a href="lister_video_folder.php">Liste du contenu du r&eacute;pertoire vid&eacute;o</a></p>
<p><a href="listing_logs.php">Rapport des modifications effectu&eacute;es par les Users</a></p>
<p><a href="rapport_reservations.php">Rapport des r&eacute;servations effectu&eacute;es sur le site</a> <br />
  <a href="rapport_ecards.php">Rapport des e-cards envoy&eacute;es sur le site</a><br />
</p>
<!-- <p><strong>CONCOURS : </strong><br />
  <a href="concours_listing.php">Listing des concours</a><br />
    <a href="concours_lire_historique.php">Historique des concours</a> : liste des concours dont le tirage a &eacute;t&eacute; effectu&eacute;. La liste mentionne les gagnant et les perdants<br />
    <a href="../concours/conc_tirage.php?pw=s5fah7r6s3p6ax2">Tirage du concours</a> : l'acc&egrave;s &agrave; cette page d&eacute;clenche le tirage au sort de tous les concours arriv&eacute;s &agrave; &eacute;ch&eacute;ance</p>
--> <p>&nbsp;</p>
<p><strong>Gestion des banners</strong><br />
    <a href="banners_contenu.php">Gestion des banners</a></p>
<p><br />
</p>
<p><strong>CONCOURS Vs II</strong><br />
[squelette rubrique=95.html + agenda/spip/conc_public.php]<br />
PS: l'authentification se fait via la fonction &quot;test_spectateur_acces_in_spip&quot; et le formulaire &quot;auth_formulaire_spectateur_in_spip.php&quot; <br />
    <a href="conc_2_listing.php">Listing des concours</a><br />
    <a href="concours_lire_historique.php">Historique des concours</a> : liste des concours dont le tirage a &eacute;t&eacute; effectu&eacute;. La liste mentionne les gagnant et les perdants<br />
  <a href="../concours/conc_2_tirage.php?pw=s5fah7r6s3p6ax2">Tirage du concours</a> : l'acc&egrave;s &agrave; cette page d&eacute;clenche le tirage au sort de tous les concours arriv&eacute;s &agrave; &eacute;ch&eacute;ance<br />
  <a href="../concours/test_tricheurs/affichage_liste_tricheurs.php" target="_blank">Affichage liste des tricheurs</a><br />
  <a href="http://www.demandezleprogramme.be/agenda/concours/rappel_avis/envoi_rappels.php">Rappel post concours</a> : envoyer email pour rappeler aux gagnant 5 jours ap&egrave;s cloture du concours de d&eacute;poser leur avis. C'est un CHRON qui lance le script </p>
<p>&nbsp;</p>
<p><strong>Flux RSS pour alimenter DLP</strong> <br />
  <a href="../rss/rss_2_db.php?pw=6wqrv4x7p2">page de lecture de Flux RSS et alimentation de la DB</a><br />
  <a href="../rss/bozar.php?pw=pvlkb534qd">lire RSS BOZAR </a><br />
  <span class="MsoNormal"><a href="../rss/info_pour_lieux.php">Notes   pour les LIEUX qui veulent nous fournir leur contenu via flux RSS</a></span></p>
<p>&nbsp;</p>
<p><strong>Flux RSS sortant de DLP</strong></p>
<p><a href="../flux_sortant/notes.php">Notes sur le Flux RSS &eacute;manant de Demandez Le Programme</a></p>
<p>&nbsp;</p>
<p><a href="../supprime_entites.php"><strong>Supprimer entit&eacute;s html</strong></a> (r&eacute;serv&eacute; aux programmeurs !) <br />
</p>
<p><strong>Vider la TABLE antispam (image crypt) </strong><br />
  
 
 
<?php

/////////////////////////////////////////////////////////
// Faut-il effacer le contenu de la TABLE 'ag_im_crypt' ?
/////////////////////////////////////////////////////////
$quand_effacer = 500 ; // la table se vide si elle contient plus de x entrées
$reponse_nb_entree_im_crypt = mysql_query("SELECT COUNT(*) AS nb_entree_im_crypt FROM $table_im_crypt ") ;
$donnees_nb_entree_im_crypt = mysql_fetch_array($reponse_nb_entree_im_crypt);

$nb_actuel_im_crypt = $donnees_nb_entree_im_crypt['nb_entree_im_crypt'] ; 
if (($nb_actuel_im_crypt > 0) AND (isset ($_GET['raz_im_crypt']) AND $_GET['raz_im_crypt'] == 'effacer') 
OR ($nb_actuel_im_crypt > $quand_effacer))
{
	 mysql_query("TRUNCATE TABLE $table_im_crypt ") ;
	 echo '<div class="info"> Effacement réussi ! </div> ' ;
}
else
{
echo 'Cliquez sur <a href="index_admin.php?raz_im_crypt=effacer">ce lien</a> pour lib&eacute;rer la place utilis&eacute;e par le syst&egrave;me anti-spam.<br />
Il y a actuellement <strong> ' . $nb_actuel_im_crypt . '</strong> entr&eacute;es effa&ccedil;ables. <br />
Le syst&egrave;me vide automatiquement la table s\'il y a plus de ' . $quand_effacer . ' entr&eacute;es. Il est pr&eacute;f&eacute;rable de ne pas proc&eacute;der &agrave; des vidages manuels trop fr&eacute;quemment  car cela peut perturber la navigation des visiteurs.</p>' ;
}
?>
<p>&nbsp;</p>


<p><strong>COMMUNAUTE des SPECTATEURS</strong>
<p><a href="spectateurs_listing.php">Listing</a> avec lien vers les avis et &eacute;dition des comptes (= rubrique 120) <br />
  d&eacute;tail d'un compte = rubrique 122<br />
<a href="/-Inscription-spectateur-">Cr&eacute;ation</a> d'un nouveau compte<br />
<a href="spectateurstest_affichage_complet.php">Affichage de toutes les descriptions</a> (pour les tests)</p>
<p><strong>E-CARDS</strong></p>
<p><a href="../e_card/rec_logs/logs_flyers.txt">Fichier log des envois d'e-cards</a><br />
  <a href="info_renaud.html#ecards">notes techniques</a></p>
<p><strong>Moteur de recherche AJAX</strong></p>
<p><a href="../moteur_2_3/notes.php">Notes sur le moteur de recherche AJAX</a></p>
<p><strong>Divers</strong><br />
  <a href="emails_export_avis.php">Adresses emails des visiteurs qui ont d&eacute;pos&eacute; leur avis sur DLP</a><br />
  <a href="set_period.php">R&eacute;glage de la p&eacute;riode d'affichage des &eacute;v&eacute;nements</a> &agrave; la page <a href="../../-Agenda">-Agenda</a> lors de l'affichage aux valeurs par d&eacute;faut (premier chargement) [voir admin_agenda/set_period.php et agenda/ctrl_periode/ ]<br />
<a href="redondance_titres.php">Test de redondance des titres</a></p>
<p><a href="rapport_complet.php"><strong>  Statistiques r&eacute;capitulatives des Lieux</strong></a></p>
<p><a href="../kidonaki/notes_kidonaki.php">Notes sur le syst&egrave;me de lien entre DLP et Kidonaki </a></p>
<p><strong>Lien entre DLP et Comedien.be<br />
</strong><a href="../lien_comedien/recap_liens.php">R&eacute;capitulatif des liens entre &eacute;v&eacute;nements DLP et Com&eacute;diens</a><br />
  <a href="../lien_comedien/notes_lien_comedien.php">Notes<br />
</a><a href="../lien_comedien/scan_db.php">Recalcul de la table &quot;ag_comedien_lien&quot;</a><br />
<a href="../lien_comedien/exemple_pour_un_comedien.php">Toutes les donn&eacute;es techniques pour l'affichage des &eacute;v&eacute;nements dans le profil d'un Com&eacute;diens</a></p>
<hr />
<h2>&nbsp;</h2>
<h2>C&ocirc;t&eacute; Lieux culturels </h2>
<p><a href="../user_admin/listing_events_gp.php">Liste des &eacute;v&eacute;nements pour 1 centre culturel</a> (le LIEU est d&eacute;termin&eacute; par SESSION) </p>
<p><a href="afficher_lieux_representation.php" target="_blank">Liste des lieux de représentation</a></p>
<p><a href="recuperer_lieux_representation.php" target="_blank">Adresse d'administration et récupération one shoot des lieux de représentation</a></p>
<hr />
<h2>&nbsp;</h2>
<h2>C&ocirc;t&eacute; SPECTATEUR </h2>
<p><a href="/-Menu-spectateur-">Menu Spectateur </a> (est d&eacute;termin&eacute; par SESSION)<br />  
<a href="../../-Spectateurs-">Liste des Spectateurs</a> [squelette rubrique=118.html + agenda/spip/spectateurs_listing_communaute.php]<br />
D&eacute;tail pour 1 spectateur  [squelette rubrique=119.html + agenda/spip/spectateurs_details_1.php]</p>
<p>&nbsp;</p>
<hr />
<h2>&nbsp;</h2>
<h2>C&ocirc;t&eacute; PUBLIC </h2>
<p><a href="../../-Demandez-le-programme-">Accueil du site demandezleprogramme.be </a></p>
<p><a href="../spip/une_agenda_email.php">La Une de l'agenda</a> (pour e-mailing)  </p>
<p><strong>Avis<br />
</strong><a href="avis_list_aprob.php"></a><br />
[squelette rubrique=97.html + agenda/spip/ecrire_avis.php] </p>
<p>&nbsp;</p>
<hr /><p>&nbsp;</p>

<h2>Les adresses e-mails utilisées dans les applications :</h2>
<ul>

	<li><strong>Automatismes d'inscriptions -> administrateurs</strong> : une ou plusieurs adresse(s) 
	[$email_admin_site] : <?php echo $email_admin_site ; ?></li>
	
	<li><strong>Visiteur -> administrateur</strong> : adresse unique de retour de courrier 
	[$retour_email_admin] : <?php echo '<a href="mailto:' . $retour_email_admin . '">' . $retour_email_admin . '</a>' ; ?></li>
	
	<li><strong>Automatismes du forum -> modérateur</strong> : une ou plusieurs adresse(s)
	[$email_moderateur_site] : <?php echo $email_moderateur_site ; ?></li>
	
	<li><strong>Visiteur  du forum -> modérateur</strong> : adresse unique de retour de courrier 
	[$retour_email_moderateur] : <?php echo '<a href="mailto:' . $retour_email_moderateur . '">' . $retour_email_moderateur . '</a>' ; ?></li>

</ul>


<p>&nbsp;</p>
<hr /><p>&nbsp;</p>

<h2> Renaud :</h2>
<p><strong>Renaud Jean Louis</strong><br />
    <a href="mailto:info@strategique.be">info@strategique.be</a><br />
  T&eacute;l : +32 (0) 2 537.14.56<br />
  GSM : + 32 (0) 475 / 98.55.38<br />
  <a href="http://www.strategique.be/">www.strategique.be</a><br />
  <em>Productions Strategiques<br />
    11 avenue de l'H&ocirc;pital Fran&ccedil;ais 1081 Koekelberg<br />
    BE 876.863.073</em><br />
  ING : 310- 0739075-21</p>
<p>&nbsp;</p>
<hr />
<p>&nbsp;</p>
</body>
</html>
