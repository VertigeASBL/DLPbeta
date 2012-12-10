<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Récapitulatif des liens entre événements DLP et Comédiens</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" /></head>

</head>

<body>

<div id="head_admin_agenda"></div>

<h1>Récapitulatif des liens entre événements DLP et Comédiens</h1>

<div class="menu_back">
<a href="../admin_agenda/index_admin.php">Menu Admin</a>
</div>
<p class="mini_info">Il y a peut-être des comédiens qui sont repris alors qu’ils n’ont pas payé leur cotisation. Comment arranger ça ? Normalement, juste en actualisant l’événement via le formulaire habituel.</p>

<?php

require '../inc_db_connect.php';
require '../inc_fct_base.php';

echo '
<table width="750" border="1" align="center" cellpadding="2" cellspacing="0" class="data_table" >
  <tr>
	<th>ID Lien</th>
	<th>ID Comédien</th>
	<th>URL Comédien</th>
	<th>ID événement</th>
	<th>Nom événement</th>
  </tr>' ;

$reponse_comedien_lien = mysql_query("SELECT * FROM ag_comedien_lien 
LEFT JOIN ag_event ON ag_comedien_lien.id_event_lien = ag_event.id_event ORDER BY url_comedien_lien ASC");
while ($donnees_comedien_lien = mysql_fetch_array($reponse_comedien_lien))
{
	echo '
  <tr class="tr_hover">
	<td>' . $donnees_comedien_lien['id_lien'] . '</td>
	<td align="center">' . $donnees_comedien_lien['id_comedien_lien'] . '</td>
	<td><a href="http://www.comedien.be/' . $donnees_comedien_lien['url_comedien_lien'] . '/">' . 
	$donnees_comedien_lien['url_comedien_lien'] . '</a></td>
	<td align="center">' . $donnees_comedien_lien['id_event_lien'] . '</td>
	<td><a href="../../-Detail-agenda-?id_event=' . $donnees_comedien_lien['id_event_lien'] . '">' . raccourcir_chaine ($donnees_comedien_lien['nom_event'],40) . '</a></td>
  </tr>' ;
}

//--- mysql_close($db2dlp);

echo '</table><p>&nbsp;</p>' ;

?>

</body>
</html>
