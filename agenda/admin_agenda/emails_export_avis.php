<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Adresses emails des visiteurs qui ont d&eacute;pos&eacute; leur avis sur DLP</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="head_admin_agenda"></div>

<h1>Adresses emails des visiteurs qui ont déposé leur avis sur DLP</h1>
<p>  
  

  <?php


require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';

$reponse_avis = mysql_query("SELECT DISTINCT (email_avis) FROM $table_avis_agenda ORDER BY email_avis DESC  ");


while ($donnees_avis = mysql_fetch_array($reponse_avis))
{	
	$email_avis = $donnees_avis['email_avis'] ; // Récupération du nom du ce spectacle
	echo '<a href="mailto:' . $email_avis . '">' . $email_avis . '</a>; ';	
}


?>
  <br />
</p>
</body>
</html>
