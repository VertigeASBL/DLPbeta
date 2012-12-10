<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>

<?php
setlocale(LC_TIME, 'fr_BE.ISO-8859-1');
$JourL = strftime("%A");
$Jour = strftime("%d");
$Mois = strftime("%B");
$Annee = strftime("%Y");
$heure=gmdate("H:i");
echo " Nous sommes le " . $JourL . " " . $Jour . " " . $Mois . " " . $Annee . " il est " . $heure=gmdate("H:i");
?>

<?php
/*

if (setlocale(LC_TIME, "C"))
{
	echo '<br>C : ' . strftime("%A");
}

if (setlocale(LC_TIME, "fi_FI"))
{
	echo '<br>finnish : ' .  strftime(" in Finnish is %A,");
}

if (setlocale(LC_TIME, "fr_FR"))
{
	echo '<br>fr : ' .  strftime(" in French %A and");
}

if (setlocale(LC_TIME, "de_DE"))
{
	echo '<br>germ : ' .  strftime(" in German %A.\n");
}
*/
?> 


</body>
</html>
