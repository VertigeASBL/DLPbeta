<?
	//**************************************************************
	//********************* Créer une facture **********************	
	//**************************************************************

	function insererfacture($mbid, $abid, $prixh, $prixc, $datfact, $descr, $fpaye) {
		global $db1com;

		$sql = "SELECT numfact FROM ger_factur WHERE mbfid=$mbid AND abfid=$abid AND extpass<>''";
		$req = mysql_query($sql, $db1com) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error($db1com));

		if ($data = mysql_fetch_array($req))
			return $data['numfact']; //--- éviter de REcréer la facture

		$fannee = substr($datfact, 0, 4);

		//--- mot de passe pour membre
		srand(time());
		for ($extpass = '', $k = 0; $k < 20; $k++)
			$extpass .= substr('abcdefghijklmnopqrstuvwxyz0123456789', rand() % 36, 1);

		//--- obtenir le prochain numéro de facture
		$sql = "SELECT MIN(numfact) AS fmin,MAX(numfact) AS fmax,extpass FROM ger_factur WHERE YEAR(datfact)='$fannee' GROUP BY extpass=''";
		$req = mysql_query($sql, $db1com) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error($db1com));

		$k = 0;
		$numfact = 0;
		while ($data = mysql_fetch_array($req))
			if ($data['extpass'] == '')
				$k = $numfact = $data['fmin'];
			else if (! $numfact)
				$numfact = $data['fmax'];

		if ($k)
			$sql = "UPDATE ger_factur SET mbfid=$mbid,abfid=$abid,prixh='$prixh',prixc='$prixc',datfact='$datfact',descr='$descr',fpaye='$fpaye',extpass='$extpass' WHERE numfact=$numfact AND YEAR(datfact)='$fannee'";
		else {
			$numfact++;
			$sql = "INSERT INTO ger_factur (numfact,mbfid,abfid,prixh,prixc,datfact,descr,fpaye,extpass) VALUES ($numfact,$mbid,$abid,'$prixh','$prixc','$datfact','$descr','$fpaye','$extpass')";
		}
		$req = mysql_query($sql, $db1com) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error($db1com));

		return $numfact;
	}

	function creerfacture($mbid, $acat, $formule, $abid, $atype, $a2type, $aprix, $is_tvac) {
		global $db1com;

		// type d'abonnement / service
		$typeNom = array('#t1'=>'Abonnement annuel sur Comedien.be','#t2'=>'Diffusion: abonnement annuel à la rubrique stages et annonces','#t3'=>'Bannière publicitaire','#t4'=>'Abonnement annuel sur demandezleprogramme.be: promotion de spectacles','#t5'=>'Abonnement sur le site Comoedia, formule partenariat Comedien.be','#t6'=>'Promotion de votre salle','#t7'=>'Diffusion: accès à la rubrique stages et annonces','#t8'=>'Formation');
		// Taux de tva
		$tabtva = array('#t1'=>1,'#t2'=>1.21,'#t3'=>1.21,'#t4'=>1.21,'#t5'=>1.21,'#t6'=>1.21,'#t7'=>1.21,'#t8'=>1);
		// Formules
		$formNom = array(1=>'10 photos : 35euros/an','multimedia : 50euros/an','10 photos UA : 17.50euros/an','multimedia UA : 25euros/an',9=>'basique 25euros/an','multimedia 40euros/an',16=>'/',24=>'/');

		$tauxtva = isset($tabtva[$atype]) ? $tabtva[$atype] : 1.21;
		$prixh = $is_tvac ? number_format($aprix / $tauxtva, 2, '.', '') : $aprix;
		if (substr($prixh, -3, 3) == '.00')
			$prixh = substr($prixh, 0, strlen($prixh) - 3);
		$prixc = $is_tvac ? $aprix : number_format($aprix * $tauxtva, 2, '.', '');
		if (substr($prixc, -3, 3) == '.00')
			$prixc = substr($prixc, 0, strlen($prixc) - 3);

		$descr = isset($typeNom[$atype]) ? $typeNom[$atype] : '';
		if ($a2type)
			$descr .= ($descr ? ' : ' : '').$a2type;
		if ($atype == '#t1' && isset($formNom[$formule]))
			$descr .= ', formule '.$formNom[$formule];

/*if ($atype == '#t5') echo 'TEST : création de facture pour Comoedia désactivée'; else*/
		insererfacture($mbid, $abid, $prixh, $prixc, date('Y-m-d'), $descr, $atype == '#t4' ? 'N' : 'Y');
	}
?>
