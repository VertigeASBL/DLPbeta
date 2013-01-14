<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>moulinette images</title>
	<!-- meta http-equiv="Content-Type" content="text/html; charset=utf-8" / -->
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<!-- link rel="stylesheet" href="squelettes/styles_tur.css" type="text/css" media="screen" / -->
	<!-- link href="squelettes/favicon_dlp.ico" rel="shortcut icon" / -->
</head>
<body>
	<?php
//	echo '<pre>';

	function mysql_fconnect($local) {
		$sql_server = 'localhost';
		$GLOBALS['sql_user'] = $local ? 'root' : 'demandezleprogra';
		$sql_passw = $local ? 'root' : 'Gz4WDtcjvAHUIy73';
		$sql_bdd = $local ? 'demandez2p' : 'demandez2p';

		$dblk = mysql_connect('localhost', 'demandezleprogra', 'Gz4WDtcjvAHUIy73');
		if (! $dblk)
			die('Erreur : Connexion impossible à la base de données');
		if (! mysql_select_db('demandezleprogramme', $dblk))
			die('Erreur : Sélection impossible de la base de données');
		return $dblk;
	}

	$local = strpos($_SERVER['REMOTE_ADDR'], '127.0.0.1') !== false || strpos($_SERVER['REMOTE_ADDR'], 'localhost') !== false;
	echo $local ? 'LOCAL' : 'DISTANT','<hr />',"\n";

	$db_link = mysql_fconnect($local);

	function redim_image($source) {
		$targ_w = 200;
		$targ_h = 132;
		$jpeg_quality = 90;

	$w_absolue = $targ_w; // Largeur qui sera imposée
	$rapport_max = $targ_w / $targ_h; // 1.4;
	$rapport_min = $rapport_max; // 0.7;

	$resultat = '../vignettes_spectateurs/'.$source;
	$uploaded_pic = imagecreatefromjpeg('../vignettes_spectateurs.old/'.$source);
	if (! $uploaded_pic) {
		echo '<br />file_exists ',file_exists('../vignettes_spectateurs.old/'.$source) ? 'oui' : 'non';
		return false;
	}
//	echo '<br /><img src="../pics_events/',$source,'" alt="" />';
//	echo '<br />',$source;
	$largeur_uploaded = imagesx($uploaded_pic);
	$hauteur_uploaded = imagesy($uploaded_pic);

	$rapport_uploaded = $largeur_uploaded / $hauteur_uploaded;

/*	echo '<br />largeur_uploaded : ',$largeur_uploaded;
	echo '<br />hauteur_uploaded : ',$hauteur_uploaded;
	echo '<br />rapport_uploaded : ',$rapport_uploaded; */

	if ($rapport_uploaded < $rapport_min) {
//		echo '<br />----- inf rapport_min';
		$new_H = round($w_absolue / $rapport_min);
		$wsrc = $largeur_uploaded;
		$hsrc = round($largeur_uploaded / $rapport_min);
		$xsrc = 0;
		$ysrc = round(($hauteur_uploaded - $hsrc) / 2);
	}
	else if ($rapport_uploaded > $rapport_max) {
//		echo '<br />----- sup rapport_max';
		$new_H = round($w_absolue / $rapport_max);
		$wsrc = round($hauteur_uploaded * $rapport_max);
		$hsrc = $hauteur_uploaded;
		$xsrc = round(($largeur_uploaded - $wsrc) / 2);
		$ysrc = 0;
	}
	else {
		$new_H = round($hauteur_uploaded * $w_absolue / $largeur_uploaded);
		$wsrc = $largeur_uploaded;
		$hsrc = $hauteur_uploaded;
		$xsrc = 0;
		$ysrc = 0;
	}

/*	echo '<br />xsrc : ',$xsrc;
	echo '<br />ysrc : ',$ysrc;
	echo '<br />w_absolue : ',$w_absolue;
	echo '<br />new_H : ',$new_H;
	echo '<br />wsrc : ',$wsrc;
	echo '<br />hsrc : ',$hsrc; */

	$resample = imagecreatetruecolor($w_absolue, $new_H); // Création image vide
	imagecopyresampled($resample, $uploaded_pic, 0, 0, $xsrc, $ysrc, $w_absolue, $new_H, $wsrc, $hsrc);
	if (file_exists($resultat))
		unlink($resultat);
	imagejpeg($resample, $resultat, $jpeg_quality);// Enregistrer la miniature sous le nom
//	echo '<br /><img src="',$resultat,'" alt="" /><br />';
//	echo '<br />X : ',imagesx($resample),' Y : ',imagesy($resample);
	if (imagesx($resample) != $targ_w || imagesy($resample) != $targ_h)
		return false;
	return true;
}


	$req = mysql_query('SELECT * FROM ag_spectateurs WHERE pic_spectateur=\'set\' ORDER BY id_spectateur') or die('Erreur SQL : '.mysql_error());
	while ($data = mysql_fetch_array($req)) {
		echo ' / ',$data['id_spectateur'];
		if (! redim_image('spect_'.$data['id_spectateur'].'_1.jpg')) {
			echo '<br />erreur redim_image event_'.$data['id_spectateur'].'_1.jpg<hr />',"\n";
		}
	}

	echo '<hr />',"\n";
	mysql_close($db_link);
//	echo '</pre>';
/*
	UPDATE ag_event SET pic_prov=''
	SELECT pic_prov,COUNT(*) AS nbr FROM ag_event GROUP BY pic_prov

	http://localhost/DLPbeta/DLPbeta/agenda/admin_agenda/moulinette_images.php?prm=6gms87z3x
	http://www.demandezleprogramme.be/beta/agenda/admin_agenda/moulinette_images.php?prm=6gms87z3x
*/
	?>
</body>
</html>