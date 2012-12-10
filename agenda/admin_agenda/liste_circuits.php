<?php
	require_once("../../admintool/conf.php");
	
	if (! isset($site)){
		$site = '1';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Circuits</title>
</head>

<body style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;">
<?
	if ($site == '1'){
?>
<p><strong>CIRCUITS DE &quot;COMEDIEN.BE</strong>&quot;</p>
<p><strong>1) Circuit &quot;Home&quot;: <br />
</strong>- Homepage<br />
- Autres m&eacute;tiers<br />
- Forum<br />
- Contact<br />
- Equipe</p>
<p>(--&gt; Circuit par d&eacute;faut, il h&eacute;rite de tous les pauvres orphelins)</p>
<p><strong>2) Circuit &quot;Casting&quot; <br />
</strong>- Castings<br />
- News<br />
  - Concours<br />
  - Projet<br />
  - Inscription &quot;com&eacute;diens&quot;</p>
<p><strong>3) Circuits &quot;Petites Annonces&quot; </strong><br />
- Petites Annonces<br />
  - Coup de projecteurs<br />
  - Ressources<br />
  - Aide<br />
  - Inscription &quot;autres m&eacute;tiers&quot;</p>
<p><strong>4) Circuit &quot;Com&eacute;diens&quot; </strong><br />
- Com&eacute;diens<br />
  - Invit&eacute;<br />
  - Annonces spectacles<br />
  - Ce qu'on en dit<br />
- Inscription &quot;compagnies&quot;</p>
<p><strong>5) Circuit &quot;Stages&quot;  </strong><br />
- Stages et Formations<br />
- Stages Comedien.be
<br />
  - Compagnies<br />
  - Salles<br />
  - Liens</p>

<p>
<?
	}else{
?>
<strong>CIRCUITS DE &quot;DEMANDEZ LE PROGRAMME&quot;</strong></p>
<p><strong>1) Circuit &quot;A la Une&quot;<br />
</strong>- A la une</p>
<p>(--&gt; Circuit par d&eacute;faut)<br />
</p>
<p><strong>2) Circuit &quot;Agenda&quot; </strong><br />
- Agenda + r&eacute;sultats de recherche <br />
  - The&acirc;tre <br />
- Danse<br />
- Concerts<br />
- Cirque<br />
- Pour enfants<br />
- Conf&eacute;rences<br />
- Expos<br />
- Dives
</p>
<p><strong>3) Circuit &quot;Concours&quot;</strong><br />
  - Concours<br />
- Critiques<br />
- Aide</p>
<p><strong>4) Circuit &quot;Contenus&quot;</strong><br />
- Les lieux + sous-rubriques<br />
  - D&eacute;tails Lieux<br />
  - Interviews<br />
- Infos + sous-rubriques</p>
<p><strong>4) Circuit &quot;D&eacute;tails&quot;<br />
</strong>- D&eacute;tails spectacles de th&eacute;&acirc;tre, cirque, ... </p>

<?
}
?>
</body>
</html>
