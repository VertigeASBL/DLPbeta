<?php 
session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Bienvenue dans l'espace des Spectateurs</title>
<link href="../css_back_spectateurs.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php 
require '../auth/auth_fonctions.php';  
test_spectateur_acces_page_auth (1) ;
?>

<div id="head_admin_spectateur"></div>


<!-- menu comme sur le site -->
<div class="menu_site_spectateurs">
<?php 
require 'inc_menu_site_spectateurs.php';
$chemin_vers_page = '../../' ;
echo affiche_menu_site_spectateur ($chemin_vers_page) ;
?>
</div>


<!-- <div class="menu_back">
<a href="edit_profile_spectateur.php">Votre profile </a> | 
<a href="../../-Agenda-">Le site</a>
</div> -->

<?php 
// Affichage Nom, Groupe et Log Off du user
voir_infos_spectateur () ;


echo '<h1>' . $_SESSION['prenom_spectateur'] . ' ' . $_SESSION['nom_spectateur'] . ', bienvenue dans votre menu Spectateur</h1>' ;
?>


<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="1" align="center" cellpadding="10" cellspacing="0" class="table_spectateur" >
  <tr>
    <td align="center"><a href="edit_profile_spectateur.php"><img src="../design_pics/g_bouton_spectateur.jpg" alt="Votre profil d'utilisateur" width="100" height="101" border="0"></a></td>
    <td align="center"><a href="edit_profile_spectateur.php">Modifier votre <br>
    profil de Spectateur </a></td>
  </tr>
  <tr>
    <td align="center"><a href="../../../Pour-les-spectateurs-membre" target="_blank"><img src="../design_pics/g_bouton_help_spect.jpg" alt="Aide en ligne" width="100" height="100" border="0"></a></td>
    <td align="center"><a href="../../../Pour-les-spectateurs-membre" target="_blank">Aide en ligne</a></td>
  </tr>
  <tr>
    <td align="center"><a href="../../-Spectateurs-"><img src="../design_pics/bouton_communaute_spectateurs.jpg" alt="Votre profil d'utilisateur" width="100" height="66" border="0"></a></td>
    <td align="center"><a href="../../-Communaute-des-spectateurs-">La communaut&eacute; <br>
    des Spectateurs </a></td>
  </tr>
</table>


</body>
</html>
