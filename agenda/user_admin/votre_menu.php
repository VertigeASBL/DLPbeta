<?php 
session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Bienvenue dans l'espace des affili&eacute;s</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php 
require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?> 

<div id="head_admin_agenda"></div>

<h1>Bienvenue dans l'espace des affili&eacute;s</h1>

<div class="menu_back">
<a href="listing_events_gp.php">Vos &eacute;v&eacute;nements </a> | 
<a href="../../-Agenda-">Le site</a>
</div>

<?php
// Affichage Nom, Groupe et Log Off du user
voir_infos_user () ;
?>




<h2>&nbsp;</h2>
<h2 align="center">Les liens utiles pour administrer vos spectacles </h2>
<p>&nbsp;</p>
<table width="400" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
    <td align="center"><a href="listing_events_gp.php"><img src="../design_pics/g_bouton_listing_events.gif" alt="Liste de vos &eacute;v&eacute;nements" width="78" height="40" border="0"></a></td>
    <td align="center"><a href="listing_events_gp.php">Liste de vos &eacute;v&eacute;nements</a></td>
  </tr>
  <tr>
    <td align="center"><a href="edit_user_gp.php"><img src="../design_pics/g_bouton_user.gif" alt="Votre profil d'utilisateur" width="21" height="40" border="0"></a></td>
    <td align="center"><a href="edit_user_gp.php">Votre profil d'utilisateur</a></td>
  </tr>
  <tr>
    <td align="center"><a href="edit_profil_lieu_gp.php"><img src="../design_pics/g_bouton_lieu.gif" alt="Profil de votre lieu culturel" width="79" height="40" border="0"></a></td>
    <td align="center"><a href="edit_profil_lieu_gp.php">Profil de votre lieu culturel</a></td>
  </tr>
  <tr>
    <td align="center"><a href="../../../Pour-les-lieux-annonceurs?" target="_blank"><img src="../design_pics/g_bouton_help.gif" alt="Aide en ligne" width="85" height="40" border="0"></a></td>
    <td align="center"><a href="../../../Pour-les-lieux-annonceurs?" target="_blank">Aide en ligne</a></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>



</body>
</html>
