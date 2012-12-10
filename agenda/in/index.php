<?php 
session_start();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Authentification</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php /* Page de "première authentification" qui redirige vers "Votre menu" si l'authentification OK */ ?>

<div id="head_admin_agenda"></div>

<h1>Authentification</h1>

<div class="menu_back">
<a href="../../-Agenda-">Retour au site</a>
</div>

<p align="center">
Ce formulaire est exclusivement r&eacute;serv&eacute; aux <strong>lieux culturels  affili&eacute;s</strong>.<br />
Si vous &ecirc;tes inscrit sur le site comedien.be, connectez-vous via <a href="http://www.comedien.be/-se-connecter-">ce  formulaire</a>.<br />
Si vous &ecirc;tes inscrit en tant que Spectateur, connectez-vous via <a href="http://www.demandezleprogramme.be/-Vous-connecter-">ce  formulaire</a>.
</p>


<?php 
require '../auth/auth_fonctions.php';
test_acces_page_auth (3) ;
?>

<meta http-equiv="refresh" content="0; url=../user_admin/votre_menu.php">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center">Si vous n'&ecirc;tes pas automatiquement redirig&eacute;, <a href="../user_admin/votre_menu.php">cliquez ici</a>.</p>


</body>
</html>
