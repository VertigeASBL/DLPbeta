<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Notes sur le moteur AJAX DLP</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" /></head>

<body>
<div id="head_admin_agenda"></div>
<h1>Notes sur le moteur de recherche AJAX</h1>
<?php
require '../inc_var.php';
?>

<div class="mes_notes">
<p>Actuellement, le moteur retient tous les param&egrave;tres de recherche ($_SESSION), donc, en revenant &agrave; la page, il remplit le formulaire tel qu'il &eacute;tait lors de la derni&egrave;re recherche.</p>
<p>&nbsp;</p>

</div>

<table width="850" border="1" cellpadding="5" cellspacing="1" bordercolor="#CCCCCC">
  <tr>
    <td height="48" colspan="2"><div align="center"><strong>Comment effectuer une recherche au moyen d'une  URL ?</strong></div></td>
  </tr>
  <tr>
    <td valign="top">req=ext</td>
    <td>Obligatoire : il faut le mettre dans la requ&ecirc;te </td>
  </tr>
  <tr>
    <td valign="top">&amp;genre=</td>
    <td>valeur num&eacute;rique  qui correspond au genre de l'&eacute;v&eacute;nement<br />
    <?php
	echo '<pre>' ;
	print_r ($genres) ;
	echo '</pre>' ;

	?>	</td>
  </tr>
  <tr>
    <td valign="top">&amp;lieu=</td>
    <td>valeur num&eacute;rique  qui correspond au lieu culturel (la liste est <a href="http://www.demandezleprogramme.be/-Les-lieux-partenaires-">ici </a>et le num&eacute;ro est dans l'URL perso des liens de chaque lieu culturel )</td>
  </tr>
  <tr>
    <td valign="top">&amp;ville=</td>
    <td>valeur num&eacute;rique  qui correspond &agrave; la ville dans laquelle l'&eacute;v&eacute;nement se d&eacute;roule<br />
    <?php
	echo '<pre>' ;
	print_r ($regions) ;
	echo '</pre>' ;
	?>    </td>
  </tr>
  <tr>
    <td valign="top">&amp;date_debut=</td>
    <td>jj-mm-aa</td>
  </tr>
  <tr>
    <td valign="top">&amp;date_fin=</td>
    <td>jj-mm-aa</td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top"><strong>Exemple complet </strong></td>
    <td>-Agenda-?req=ext&amp;genre=g01&amp;lieu=16&amp;ville=be1&amp;date_debut=12-05-07&amp;date_fin=19-02-09</td>
  </tr>
</table>
<p>Utilis&eacute; squelette 65</p>
<p>&nbsp;</p>
</body>
</html>
