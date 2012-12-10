<?php 
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<!-- Lien de redirection -->
<meta http-equiv="refresh" content="2; url=../../-Agenda-">

<title>LOG OFF page</title>
<link href="css_auth.css" rel="stylesheet" type="text/css" />
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>

<body>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h1 align="center">LOG OFF</h1>

<?php 
//---------------------------------------------------------
// Procédure de LOG OFF
//---------------------------------------------------------
$_SESSION['group_admin_spec'] = NULL;
$_SESSION['nom_admin_spec'] = NULL;
$_SESSION['group_admin_spec_name'] = NULL;
$_SESSION['nom_spectateur'] = NULL;
$_SESSION['prenom_spectateur'] = NULL;
$_SESSION['id_spectateur'] = NULL;
$_SESSION['pseudo_spectateur'] = NULL;

session_destroy();
?>

<p>&nbsp;  </p>

<div class="info">
  <div align="center"> -- D&eacute;connexion en cours -- </div>
</div>

<p>&nbsp;  </p>

<div align="center"><a href="http://www.demandezleprogramme.be/">Accueil</a></div>

</body>
</html>
