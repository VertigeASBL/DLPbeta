<? /*
---------- CRON ----------	https://jesus.all2all.org:10000/cron/
wget --help
wget -t 3 -q -O - http://www.demandezleprogramme.be/ncron/bonAnnivDLP.php?prm=sr3h92d8dhq5
(-t 3) = nombre d'essai : 3
(-q) = quiet, sans output
(-O -) = output documents to standard output instead of to files
http://www.gnu.org/software/wget/manual/wget.html
*/
	require('../nlettre/conf.php');

	if (! isset($prm) || $prm != 'sr3h92d8dhq5')
		die("Erreur: la tâche bonAnnivDLP n'a pas été exécutée");

	//--- Connexion à la DB
	$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
	if (! $db_link) {
		echo 'Connexion impossible à la base de données ',$sql_bdd,' sur le serveur ',$sql_server;
		exit;
	}
	mysql_select_db($sql_bdd, $db_link);

//-***** encoder selon RFC2047 ******
if (! function_exists('encodeHeader')) {
function encodeHeader($chn) {
	preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $chn, $tab);
	while (list(, $s1) = each($tab[1])) {
		$s2 = preg_replace('/([\x20\x80-\xFF])/e', '"=".strtoupper(dechex(ord("\1")))', $s1);
		$chn = str_replace($s1, '=?ISO-8859-1?Q?'.$s2.'?=', $chn);
	}
	return $chn;
} }
//-***** encoder quoted-printable ******
if (! function_exists('quotedPrintable')) {
function quotedPrintable($texte, $ncar=76) {
	$tab = preg_split("/\r?\n/", $texte);
	$chn = '';
	while (list(, $ligne) = each($tab)) {
		$g = strlen($ligne); $g--;
		$lign2 = '';
		for ($k = 0; $k <= $g; $k++) {
			$car = substr($ligne, $k, 1);
			$dec = ord($car);
			if ($dec == 32 && $k == $g)
				$car = '=20';
			else if ($dec == 9)
				;
			elseif ($dec == 61 || $dec < 32 || $dec > 126)
				$car = '='.strtoupper(sprintf('%02s', dechex($dec)));
			if (strlen($lign2) + strlen($car) >= $ncar) {
				$chn .= $lign2.'='."\r\n";
				$lign2 = '';
			}
			$lign2 .= $car;
		}
		$chn .= $lign2."\r\n";
	}
	return substr($chn, 0, -2);
} }

	//----- entete de l'email
	$entete = "From: demandezleprogramme.be <info@demandezleprogramme.be>\n";
	$entete .= "X-Sender: demandezleprogramme.be <info@demandezleprogramme.be>\n";
	$entete .= "Return-Path: demandezleprogramme.be <info@demandezleprogramme.be>\n";
	$entete .=  "MIME-Version: 1.0\n";
	$entete .=  "Content-Type: multipart/mixed;\n";
	$entete .=  " boundary=\"----=_md87fqs6sd78hgfd65\"\n";

	//----- obtenir certains membres
	$sql = 'SELECT DISTINCT ladrm,datnaiss FROM cmsnletter WHERE lletr=\'DPts\' AND letat=\'5\' AND DAYOFMONTH(datnaiss)=DAYOFMONTH(CURDATE()) AND MONTH(datnaiss)=MONTH(CURDATE())';
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	//----- envoyer un email aux membres
	$chn = '';
	while ($data = mysql_fetch_array($req)) {
		$adrml = trim($data['ladrm']);
//--- $adrml = 'philippe@vertige.org';

		$corps = 'This is a multi-part message in MIME format.'."\r\n".'DemandezLeProgramme.be vous souhaite un joyeux anniversaire !'."\r\n\r\n";
		if (! preg_match('/^\S+@\S+\.\S+$/', $adrml)) {
			$corps .= "------=_md87fqs6sd78hgfd65\n";
			$corps .= 'Content-Type: text/plain; charset=ISO-8859-1;'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";
			$corps .= quotedPrintable("\r\n\t".'Bonjour.'."\r\n\r\n".'La carte d\'anniversaire de DemandezLeProgramme.be n\'a pas pu être envoyée.'."\r\n".'Adresse email non valide: '.$adrml."\r\n".'Date de naissance : '.$data['datnaiss']."\r\n\r\n");

			$adrml = 'info@demandezleprogramme.be';
		}
		$corps .= "------=_md87fqs6sd78hgfd65\n";
		$corps .= "Content-Type: image/jpeg; name=\"carteDemandez.jpg\"\n";
		$corps .= "Content-Transfer-Encoding: base64\n";
		$corps .= "Content-Disposition: attachment; filename=\"carteDemandez.jpg\"\n";
		$fp = fopen('carteDemandez.jpg', "rb");
		$corps .= "\r\n".chunk_split(base64_encode(fread($fp, filesize('carteDemandez.jpg'))))."\r\n";
		fclose($fp);
		$corps .= "------=_md87fqs6sd78hgfd65--\n";

		if (! mail($adrml, encodeHeader('DemandezLeProgramme.be : Bon anniversaire !'), $corps, $entete, '-f info@demandezleprogramme.be'))
			die("Erreur: il est impossible d'envoyer un message (bon anniversaire) au membre");

		$chn .= $data['datnaiss'].', '.$adrml."\n";
	}
	echo $chn,' / Fin normale de la tâche Bon Anniversaire / DemandezLeProgramme.be';

	//--- Déconnexion de la DB
	mysql_close($db_link);
?>
