<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Notes sur le syst&egrave;me de lien entre DLP et comedien.be</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="head_admin_agenda"></div>

<h1>Notes sur le syst&egrave;me de lien entre DLP et comedien.be</h1>
<p>&nbsp;</p>
<p>&nbsp;</p>


<p><strong>Principe de fonctionnement :</strong></p>
<p>Une table sql &quot;ag_comedien_lien&quot; est tenue &agrave; jour &agrave; chaque modification par un Lieu d'un de ses &eacute;v&eacute;nements via le script &quot;<a href="inc_update_table_lien.php">agenda/lien_comedien/inc_update_table_lien.php</a>&quot;. Cette table contient les infos n&eacute;cessaires pour afficher dans le profil, les &eacute;v&eacute;nements dans lesquels a jou&eacute; le com&eacute;dien :</p>
<ul>
  <li> id_lien : Simple ID unique</li>
  <li>id_event_lien : l'ID de l'&eacute;v&eacute;nement</li>
  <li>id_comedien_lien : l'ID du comedien dans la TABLE &quot;comedien&quot; de comedien.be </li>
  <li>url_comedien_lien :  l'URL propre du comedien</li>
</ul>
<p>&nbsp;</p>
<p><strong>Remarque</strong> : </p>
<ul>
  <li>Il y a peut-&ecirc;tre des com&eacute;diens qui sont repris alors qu&rsquo;ils n&rsquo;ont pas pay&eacute; leur cotisation. Comment arranger &ccedil;a ? Normalement, juste en actualisant l&rsquo;&eacute;v&eacute;nement via le formulaire habituel.</li>
  <li>S'il faut reconstituer la table  &quot;ag_comedien_lien&quot;, utiliser le script &quot;<a href="scan_db.php">agenda/lien_comedien/scan_db.php</a>&quot;</li>
</ul>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
