<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Flux RSS de Demandez Le Programme </title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />

<link rel="alternate" type="application/rss+xml" href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php" />
</head>

<body>
<div id="head_admin_agenda"></div>
<h1>Flux RSS de Demandez Le Programme </h1>
<?php
require '../inc_var.php';
?>

<div class="mes_notes">
  <p>&nbsp;</p>

</div>

<table width="850" border="1" align="center" cellpadding="5" cellspacing="1" bordercolor="#CCCCCC">
  
  <tr>
    <td colspan="2" valign="top"><p align="center">&nbsp;</p>
      <p align="center"><strong>L'adresse g&eacute;n&eacute;rale du flux : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php"><br />
        http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php</a></strong></p>
      <p align="center">&nbsp;</p>    </td>
  </tr>
  <tr>
    <td colspan="2" valign="top">
      <p>A cette adresse, on peut rajouter des param&egrave;tres qui permettent de filtrer les &eacute;v&eacute;nements affich&eacute;s.<br /> 
      Ainsi, il est possible de s&eacute;lectionner les &eacute;v&eacute;nements : </p>
    <ul>
      <li>pour un <a href="#genre">genre</a> en particulier (th&eacute;&acirc;tre, Jazz, cirque...) </li>
      <li>pour un  <a href="#lieu">lieu</a> culturel sp&eacute;cifique</li>
      <li>pour une <a href="#ville">ville</a> sp&eacute;cifique</li>
      <li>pour une <a href="#date_debut">date</a> ant&eacute;rieure</li>
      </ul>    </td>
  </tr>
  <tr>
    <td valign="top"><a name="genre" id="genre"></a>genre=</td>
    <td><p>En ajoutant &agrave; l'url de base &quot;genre=&quot; suivi du code qui correspond au genre de l'&eacute;v&eacute;nement<br />
	Exemple : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?genre=g01">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?genre=g01</a>
	</p>
      <p>
        <?php
	echo '<ul>';
	foreach($genres as $cle_genres => $element_genres)
	{
		echo '<li><strong>' . $element_genres . '</strong> : genre=' . $cle_genres . '</li>' ;
	}
	echo '</ul>';

	/*echo '<pre>' ;
	print_r ($genres) ;
	echo '</pre>' ;*/

	?></p>
      <p>Pour <strong>combiner plusieurs genres</strong>, mettre le caract&egrave;re &quot;_&quot; entre chaque genre voulu : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?genre=g01_g02_g04_g14_g09_g03g10_g06_g11_g05">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?genre=g01_g02_g04_g14_g09_g03g10_g06_g11_g05</a></p></td>
  </tr>
  <tr>
    <td valign="top"><a name="lieu" id="lieu"></a>lieu=</td>
    <td><p>En ajoutant &agrave; l'url de base &quot;lieu=&quot; suivi de la valeur num&eacute;rique  qui correspond au lieu culturel. <br />
      La liste des lieux est reprise <a href="http://www.demandezleprogramme.be/-Les-lieux-partenaires-">ici</a>. En cliquant sur le lien de l'un des lieux culturels, vous trouverez le num&eacute;ro correspondant &agrave; ce lieu dans l'URL qui appara&icirc;tra.<br />
    Exemple : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?lieu=73">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?lieu=73</a></p>    </td>
  </tr>
  <tr>
    <td valign="top"><a name="ville" id="ville"></a>ville=</td>
    <td><p>En ajoutant &agrave; l'url de base  &quot;ville=&quot; suivi du code qui correspond &agrave; la ville dans laquelle l'&eacute;v&eacute;nement se d&eacute;roule<br />
      Exemple : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?ville=be1">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?ville=be1</a> </p>
      <p>
        <?php
	echo '<ul>';
	foreach($regions as $cle_regions => $element_regions)
	{
		echo '<li><strong>' . $element_regions . '</strong> : ville=' . $cle_regions . '</li>' ;
	}
	echo '<ul>';

	?></p>    </td>
  </tr>
  <tr>
    <td valign="top"><p><a name="date_debut" id="date_debut"></a>date_debut=</p>
    <p>  <a name="date_fin" id="date_fin"></a>date_fin=</p></td>
    <td><p>En rajoutant &agrave; l'url de base &quot;date_debut=&quot; suivi de la date d&eacute;sir&eacute;e (sous la forme jj-mm-aa) et &quot;date_fin=&quot; suivi de la date d&eacute;sir&eacute;e (sous la forme jj-mm-aa), on peut limiter l'affichage &agrave; une p&eacute;riode de temps pr&eacute;cise, ou aussi &eacute;tendre l'affichage au del&agrave; de la plage par d&eacute;faut..<br />
    Exemple : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?date_debut=01-03-2008&amp;date_fin=01-05-2008">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?date_debut=01-03-2008&amp;date_fin=01-05-2008</a></p>    </td>
  </tr>
  <tr>
    <td valign="top">desc_type=comp</td>
    <td>En rajoutant &agrave; l'url de base &quot;desc_type=comp&quot;, le flux affichera la &quot;decription coml&egrave;te&quot; des &eacute;v&eacute;nements &agrave; la place du &quot;r&eacute;sum&eacute;&quot; des &eacute;v&eacute;nements. <br />
      Exemple : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?desc_type=comp">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?desc_type=comp</a></td>
  </tr>
  <tr>
    <td colspan="2" valign="top"><p><em><strong>Remarque</strong></em> : il est possible d'utiliser <strong>plusieurs param&egrave;tres &agrave; la fois</strong> et utilisant entre chacun d'eux le signe <strong>&amp;</strong><br />
      Exemple : pour s&eacute;lectionner les &eacute;v&eacute;nements r&eacute;cents dont le genre est &quot;th&eacute;&acirc;tre&quot; et qui se d&eacute;roulent dans la ville de &quot;Bruxelles&quot; : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?genre=g01&amp;ville=be1">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?genre=g01&amp;ville=be1</a></p>
      <p></p></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">
      <p>&nbsp;</p>
      <p align="center"><strong>Concernant le Top5</strong></p>
      <p>Pour effectuer un tri selon les<em> votes des spectateurs</em>, le param&egrave;tre indispensable est &quot;top=&quot; auquel on donne comme valeur le nombre de r&eacute;sultats que l'on souhaite voir appara&icirc;tre dans le flux g&eacute;n&eacute;r&eacute;.<br />
      Exemple de base : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?top=5">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?top=5</a></p>
      <p>On peut rajouter &agrave; cette URL de base tous les param&egrave;tres mentionn&eacute;s plus haut afin de rendre le flux plus sp&eacute;cifique.<br />
Exemple pour obtenir le <em>top 5 &agrave; Bruxelles</em> : <a href="http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?top=5&amp;ville=be1">http://www.demandezleprogramme.be/agenda/flux_sortant/rss_feed_4.php?top=5&amp;ville=be1</a></p>
      <p>Il est &agrave; noter que le nombre de personnes ayant vot&eacute; pour l'&eacute;v&eacute;nement n'appara&icirc;t pas dans le titre (&lt;title&gt;) mais bien dans un <em>namespace</em> &lt;dc:votes&gt;. On peut le v&eacute;rifier en observant le code source de la page.</p>
      <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
