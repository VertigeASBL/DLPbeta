<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Liste des lieux de représentation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php 
	require '../inc_var.php';
	require '../inc_db_connect.php'; //--- ___off___

	echo '<hr />',"\n";

	$reponse = mysql_query('SELECT * FROM ag_representation ORDER BY nom_pres');
	while ($data = mysql_fetch_array($reponse)) {
		echo 'id_pres : ',$data['id_pres'];
		echo '<br />lieu_pres : ',$data['lieu_pres'];
		echo '<br />nom_pres : ',$data['nom_pres'];
		echo '<br />adresse_pres : ',$data['adresse_pres'];
		echo '<br />postal_pres : ',$data['postal_pres'];
		echo '<br />localite_pres : ',$data['localite_pres'];
		echo '<br />pays_pres : ',$data['pays_pres'];
		echo '<br />ok_pres : ',$data['ok_pres'];
/*
		echo '<br />tel_pres : ',$data['tel_pres'];
		echo '<br />e_mail_pres : ',$data['e_mail_pres'];
		echo '<br />web_site_pres : ',$data['web_site_pres'];
*/
		echo '<hr />',"\n";
	}
	mysql_close($db2dlp);
?>
<p>&nbsp;</p>
</body>
</html>
