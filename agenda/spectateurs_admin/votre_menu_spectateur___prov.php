<?php 
/*
session_start();
<title>Bienvenue dans l'espace des Spectateurs</title>
<link href="../css_back_spectateurs.css" rel="stylesheet" type="text/css">

echo '<br />',getcwd();
chdir('agenda/spectateurs_admin');
test_spectateur_acces_page_auth(1);
<div id="head_admin_spectateur"></div>
*/
require 'agenda/auth/auth_fonctions.php';  

test_spectateur_acces_in_spip(1);

// Affichage Nom, Groupe et Log Off du user
voir_infos_spectateur () ;

/*
echo '<h1>' . $_SESSION['prenom_spectateur'] . ' ' . $_SESSION['nom_spectateur'] . ', bienvenue dans votre menu Spectateur</h1>' ;
<p>&nbsp;</p>
<p>&nbsp;</p>
*/
?>
