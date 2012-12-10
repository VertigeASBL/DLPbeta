<? /*
---------- CRON ----------	https://jesus.all2all.org:10000/cron/
wget --help
wget -t 3 -q -O - http://www.demandezleprogramme.be/ncron/alertUneDLP.php?prm=sr3h92d8dhq5
(-t 3) = nombre d'essai : 3
(-q) = quiet, sans output
(-O -) = output documents to standard output instead of to files
http://www.gnu.org/software/wget/manual/wget.html
*/
	require('../nlettre/conf.php');

	if (! isset($prm) || $prm != 'sr3h92d8dhq5')
		die("Erreur: la tâche alertUneDLP n'a pas été exécutée");

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
	$entete .= 'Content-Type: text/plain; charset=ISO-8859-1;'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";

	//----- envoyer un email d'alerte
	$adrml = 'info@demandezleprogramme.be';
//--- $adrml = 'philippe@vertige.org';

	$corps = "\r\n\t".'Oyez oyez.'."\r\n\r\n".'Nous sommes le '.date('d-m-Y').'.'."\r\n".'SVP, n\'oubliez pas d\'envoyer la newsletter LA UNE de DLP.'."\r\n".'http://www.demandezleprogramme.be/nlettre/'."\r\n\r\n".'Merci, au revoir.'."\r\n\t".'CRON.'."\r\n";

	if (! mail_beta($adrml, encodeHeader('DLP : envoyer la newsletter LA UNE'), quotedPrintable($corps), $entete, '-f xavier@vertige.org'))
		die("Erreur: il est impossible d'envoyer un message d'alerte pour DLP la Une");

	echo 'Fin normale de la tâche : alerte pour DLP la Une';
?>
