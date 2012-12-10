<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Notes sur le syst&egrave;me de lien entre DLP et Kidonaki </title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" /></head>

<body>
<div id="head_admin_agenda"></div>

<h1>Notes sur le syst&egrave;me de lien entre DLP et Kidonaki </h1>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h2>Remarques g&eacute;n&eacute;rales :</h2>
<ol>
  <li>Le formulaire de DLP destin&eacute; aux Lieux ne fait qu&rsquo;un recopiage de donn&eacute;es. Il n&rsquo;est pas destin&eacute; &agrave; tenir &agrave; jour les donn&eacute;es ou les ench&egrave;res. Pour cela, le Lieu doit se connecter &agrave; son compte Kidonaki.</li>
  <li>Pour un &eacute;v&eacute;nement, un Lieu peut cr&eacute;er 1 seul et unique lot de une ou plusieurs places. Les modifications ult&eacute;rieures ne peuvent se faire que via Kidonaki</li>
</ol>
<p>&nbsp;</p>
<hr />
<h2>&nbsp;</h2>
<h2>Comment faire&nbsp;pour  lier un LIEU &agrave; un Auteur Kidonaki ?</h2>
<h3>Etape 1&nbsp;: Cr&eacute;er  un compte. </h3>
<p>Il est n&eacute;cessaire de poss&eacute;der, ou de cr&eacute;er un compte sur  Kidonaki.</p>
<h3>Etape 2&nbsp;: Lien  DLP vers Kidonaki.</h3>
<p>R&eacute;serv&eacute;e &agrave; un administrateur du site DLP. Le num&eacute;ro d&rsquo;ID  du compte Kidonaki est encod&eacute; via l&rsquo;interface (c&ocirc;t&eacute; admin) d&rsquo;&eacute;dition des donn&eacute;es  d'un LIEU culturel (cette page par exemple pour 1 Lieu&nbsp;: <a href="http://www.demandezleprogramme.be/agenda/admin_agenda/edit_profil_lieu.php?id=1">http://www.demandezleprogramme.be/agenda/admin_agenda/edit_profil_lieu.php?id=1</a> ). <br />
  Attention&nbsp;: une fois le lien cr&eacute;&eacute;, il  ne sera plus possible de le supprimer&nbsp;!</p>
<h3>Etape 3&nbsp;: Lien  Kidonaki vers DLP.</h3>
<p>Dans la Table &laquo;&nbsp;spip_auteurs&nbsp;&raquo;  de la DB de Kidonaki, dans le champ &laquo;&nbsp;id_lieu&nbsp;&raquo;, il faut introduire  le num&eacute;ro d&rsquo;ID du LIEU Culturel (proc&eacute;dure &agrave; d&eacute;terminer avec Rainer).</p>
<hr />
<h2>&nbsp;</h2>
<h2>Eclaircissement sur  les&nbsp;dates&nbsp;:</h2>
<ul>
  <li>[Date 1] Date de fin de l&rsquo;&eacute;v&eacute;nement&nbsp;: la date choisie  lorsqu&rsquo;on cr&eacute;e un &eacute;v&eacute;nement sur DLP</li>
  <li>    [Date 2] Dernier jour de repr&eacute;sentation : la date d&eacute;signant le dernier jour auquel un  spectateur peut assister &agrave; une repr&eacute;sentation d&rsquo;un &eacute;v&eacute;nement pour lequel il a achet&eacute;  des places sur Kidonaki. Cette date est choisie par le Lieu dans le formulaire  de &laquo;&nbsp;lien DLP- Kidonaki&nbsp;&raquo;. Cette date apparait dans le CHAPO de l'article Kidonaki <br />
  Note&nbsp;: la &quot;date de d&eacute;but&quot; indiqu&eacute;e en regard de la date de &quot;Dernier jour de repr&eacute;sentation&quot; correspond  toujours au premier jour encod&eacute; lors de la cr&eacute;ation de l&rsquo;&eacute;v&eacute;nement sur DLP.</li>
  <li>[Date 3] Date de fin des ench&egrave;res&nbsp;: d&eacute;pend de la [Date  2]. Elle est de 15 jours au maximum, et calcul&eacute;e en fonction du nombre de jours  restant avant la [Date 2]. <br />
    Elle est stock&eacute;e dans &laquo;&nbsp;date_fin&nbsp;&raquo;  de la DB de Kidonaki.</li>
	<li>[Date 4] Date limite de mise en vente des places sur  Kidonaki. Celle-ci est calcul&eacute;e automatiquement sur base de la     [Date 2], &agrave;  laquelle on soustrait une marge de 12 jours. Elle est stock&eacute;e dans  &laquo;&nbsp;date_stop_vente&nbsp;&raquo; de la DB de Kidonaki.<br />
    </li>

</ul>
<hr />
<h2>Divers :</h2>
<p>L&rsquo;ID (pour les tests) sur Kidonaki = 1011</p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
