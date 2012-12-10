<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Récupération des lieux de représentation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php 
	require '../inc_var.php';
	require '../inc_db_connect.php'; //--- ___off___

	function filtrer($chn) {
		return addslashes(str_replace('&#039;', '\'', html_entity_decode($chn)));
	}
	$reponse = mysql_query('SELECT id_lieu,nom_lieu,adresse_lieu,tel_lieu,e_mail_lieu,web_site_lieu FROM ag_lieux ORDER BY nom_lieu');
	$ok = true;
	while ($data = mysql_fetch_array($reponse)) {
//		$sql = 'INSERT INTO ag_representation SET lieu_pres='.$data['id_lieu'];
//		$sql .= ',nom_pres=\''.filtrer($data['nom_lieu']).'\'';

		$adresse = filtrer($data['adresse_lieu']);
		echo "\n",'<hr />',filtrer($data['nom_lieu']);
		$tab = array();
		preg_match('|^(.*\D)(\d+)(\D+)$|', $adresse, $tab);
		echo '<br />ID : ',$data['id_lieu'],' : ',count($tab);
		echo '<br />',htmlspecialchars($adresse),'<br />--- RECUP ---<br />';
//		print_r($tab); echo '<br />';

		if (count($tab) == 4) {
			$tab[1] = trim($tab[1]);
			$k = strlen($tab[1]); $k--;
			if ($tab[1]{$k} == 'à' || $tab[1]{$k} == '-' || $tab[1]{$k} == ',' || $tab[1]{$k} == '/')
				$tab[1] = substr($tab[1], 0, -1);
			$tab[1] = trim($tab[1]);
			echo $tab[1],'<br />';

			echo $tab[2],'<br />';

			$tab[3] = trim($tab[3]);
			if ($tab[3]{0} == ',' || $tab[3]{0} == '-' || $tab[3]{0} == ',' || $tab[3]{0} == '/')
				$tab[3] = substr($tab[3], 1);
			$k = strlen($tab[3]);
			if ($k) {
				$k--;
				if ($tab[3]{$k} == ',' || $tab[3]{$k} == '-' || $tab[3]{$k} == ',' || $tab[3]{$k} == '/' || $tab[3]{$k} == ')')
					$tab[3] = substr($tab[3], 0, -1);
			}
			$tab[3] = trim($tab[3]);
			if (strtolower(substr($tab[3], -8)) == 'belgique')
				$tab[3] = substr($tab[3], 0, -8);
			$tab[3] = trim($tab[3]);
			$k = strlen($tab[3]);
			if ($k) {
				$k--;
				if ($tab[3]{$k} == ',' || $tab[3]{$k} == '-' || $tab[3]{$k} == ',' || $tab[3]{$k} == '/' || $tab[3]{$k} == '(')
					$tab[3] = substr($tab[3], 0, -1);
				$tab[3] = trim($tab[3]);
			}
			echo $tab[3],'<br />';
		}
		else {
			$tab[1] = $adresse; $tab[2] = ''; $tab[3] = '';
			echo 'erreur<br />';
		}
/*
		$sql .= ',adresse_pres=\''.$tab[1].'\'';
		$sql .= ',localite_pres=\''.$tab[2].'\'';
		$sql .= ',postal_pres=\''.$tab[3].'\'';

		$sql .= ',pays_pres=1';
		$sql .= ',tel_pres=\''.filtrer($data['tel_lieu']).'\'';
		$sql .= ',e_mail_pres=\''.filtrer($data['e_mail_lieu']).'\'';
		$sql .= ',web_site_pres=\''.filtrer($data['web_site_lieu']).'\'';
		$ok = mysql_query($sql);
		if (! $ok) break;
*/
//		echo $sql,'<br />';
	}
	echo "\n",'<hr />';
	mysql_close($db2dlp);
?>
<p>&nbsp;</p>
</body>
</html>
