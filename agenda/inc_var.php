<?php
// ------------------------------------
// Variables générales - globales
// ------------------------------------


// ------------------------------------
// Définition des adresses e-mail pour gestion du site

//$email_admin_site = 'renaud.jeanlouis@gmail.com; info@demandezleprogramme.be' ; // 1 ou plusieurs destinataire(s). !!! QUE pour fonction mail_beta() et pas pour afficher dans un lien !!!
$email_admin_site = 'info@demandezleprogramme.be' ; // 1 ou plusieurs destinataire(s). !!! QUE pour fonction mail_beta() et pas pour afficher dans un lien !!!
$retour_email_admin = 'info@demandezleprogramme.be' ; // Adresse unique de retour du courrier  (FROM)
$email_moderateur_site = 'info@demandezleprogramme.be' ;  // 1 ou plusieurs destinataire(s). !!! QUE pour fonction mail_beta() et pas pour afficher dans un lien !!!
$retour_email_moderateur = 'info@demandezleprogramme.be' ; // Adresse unique de retour du courrier (FROM)

$email_retour_erreur = '-f info@demandezleprogramme.be' ; // Adresse de retour des emails "non arrivés" (cette variable doit contenir '-f ')


// ------------------------------------
// Correspondance entre TABLES des DB
$table_lieu = 'ag_lieux' ;
$table_user_agenda = 'ag_users';
$table_evenements_agenda = 'ag_event';
$table_avis_agenda = 'ag_avis' ;
$table_avis_mailing = 'ag_avis_mailing' ;
$table_logs = 'ag_logs' ;
$table_ag_conc_fiches = 'ag_conc_fiches' ;
$table_ag_conc_joueur = 'ag_conc_joueur' ;
$table_ag_conc_historique = 'ag_conc_historique' ;
$table_im_crypt = 'ag_im_crypt' ;
$table_spectateurs_ag = 'ag_spectateurs' ;

// ------------------------------------
// Taille Max des VIGNETTES des Fiches des lieux culturels
$maxWidth = 120;
$maxHeight = 120;
$maxWidth_pic = 500;
$maxHeight_pic = 500;

$folder_pics_vignettes_lieux_culturels = 'vignettes_lieux_culturels/';

// ------------------------------------
// Taille Max des VIGNETTES des Fiches des événements culturels
/*$max_w_vi_event = 100;
$max_h_vi_event = 100;
$max_w_pic_event = 200;
$max_h_pic_event = 200;*/

$folder_pics_event = 'pics_events/';

// Pour version III
$w_absolue = 200; // Largeur qui sera imposée
$w_vi_absolue = 100; // Largeur qui sera imposée pour les vignettes


// ------------------------------------
// Taille Max des VIGNETTES des Fiches CONCOURS
$maxWidth_conc_vignette = 120;
$maxHeight_conc_vignette = 120;
$maxWidth_conc_pics = 320;
$maxHeight_conc_pics = 320;

$folder_vignettes_concours = 'vignettes_concours/' ;


// ------------------------------------
// Taille Max des VIGNETTES des Fiches SPECTATEURS
$w_absolue = 200; // Largeur qui sera imposée
$w_vi_absolue = 100; // Largeur imposée pour les vignettes

$folder_pics_spectateurs = 'vignettes_spectateurs/';


// ------------------------------------
// Niveaux d’authentification
$group_admin_spec_noms = array (
	'0' => 'bannis',
	'1' => 'spectateur',
	'2' => 'inutilisé',
	'3' => 'administrateur lieu culturel',
	'4' => 'inutilisé',
	'5' => 'Super Admin',
	'7' => 'inutilisé',
	'8' => 'inutilisé',
);


// ------------------------------------
// Noms des régions pour les événements
$regions = array (
	'be1' => 'Bruxelles',
	'be7' => 'Charleroi',
	'be8' => 'Louvain-la-Neuve',
	'be9' => 'Fleurus',
	'b10' => 'Ittre',
	'b11' => 'Mons',
	'be2' => 'Namur',
	'be3' => 'Liège',
	'be4' => 'Arlon',
	'be5' => 'Huy',
	'be6' => 'La Louvière',
	'be13' => 'Mouscron',
	'be12' => 'Thoricourt',
	'be15' => 'Tournai',
	'be14' => 'Colfontaine',
	'be16' => 'Beauvechain',
	'be17' => 'Spa',
	'be18' => 'Estinnes'
);

// ------------------------------------
// Pays pour lieux de représentation
$payspresent = array (
	1 => 'Belgique',
	2 => 'France',
	3 => 'Pays-Bas',
	4 => 'Luxembourg',
	5 => 'Allemagne',
	6 => 'Suisse'
);

// ------------------------------------
// Définition des GENRES d'événements

$genres = array (
	'g01' => 'Th&eacute;&acirc;tre',
	'g02' => 'Danse',
	'g04' => 'Cirque',
	'g14' => 'Conte',
	'g09' => 'Musique classique, Op&eacute;ra',
	'g03' => 'Electro-Pop-Rock',
	'g10' => 'Jazz',
	'g06' => 'Chanson fran&ccedil;aise',
	'g11' => 'Autres concerts',
	'g07' => 'Expos',
	'g05' => 'Pour enfants',
	'g12' => 'Cin&eacute;ma',
	'g13' => 'Conf&eacute;rences',
	'g15' => 'Festival',
	'g08' => 'Ev&eacute;nements Divers'
);

// ------------------------------------
// Mois de l'année
$NomDuMois=array('erreur','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre');
// $monthname=$NomDuMois[$month+0];

	
// ------------------------------------
// CSS pour e-mailing
$css_email = '
body { font-size: 13px; color:#005655; font-family: Geneva, Arial, Helvetica, sans-serif; }
.email_style_titre { font-size: 18px; color:#009A99; font-weight: bold; }
.email_style_rubriques { font-size: 12px; color:#AA0033; font-weight: bold; }
.email_style_petit { font-size: 11px; color: #666666; } 
.turquoise_style {color: #009A99 ;} 
a img { border: none; text-decoration:none; }

';

// ------------------------------------
// Vidéos
$folder_videos = 'videos/' ;

	
// ------------------------------------
// Variable LOG (pour la correspondance entre PAGE modifiée, nom de la page et URL)
// Il faut rajouter $racine_domaine . ' ...........  . $context_id_log à l'URL
$type_log_array = array (
'1' => array ('Fiche descriptive du lieu', '-Details-lieux-culturels-?id_lieu=' ),
'2' => array ('Fiche événement', '-Detail-agenda-?id_event=' ),
'3' => array ('Profil d\'un lieu culturel', 'agenda/admin_agenda/edit_user_agenda.php?id='),
'4' => array ('RSS - Fiche event', '-Detail-agenda-?id_event=' )
) ;


// ------------------------------------
// Définition des GROUPES de joueurs pouvant participer aux concours
$groupes_joueurs = array (
	'jou01' => 'Visiteur',
	'jou02' => 'Comedien',
);

// ------------------------------------
// Facteur CHANCE lors de participation aux concours ====> voir dans les fonctions !



?>
