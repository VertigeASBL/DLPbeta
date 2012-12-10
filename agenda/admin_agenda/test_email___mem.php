<?
	/**********************************************
	****** Envoyer test ******
	**********************************************/
define('CHAR7MAIL', 'ISO-8859-1;');

require '../inc_var.php';
require '../inc_fct_base.php';

//***** encoder selon RFC2047 ******
if (! function_exists('encodeHeader')) {
function encodeHeader($chn) {
	preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $chn, $tab);
	while (list(, $s1) = each($tab[1])) {
		$s2 = preg_replace('/([\x20\x80-\xFF])/e', '"=".strtoupper(dechex(ord("\1")))', $s1);
		$chn = str_replace($s1, '=?ISO-8859-1?Q?'.$s2.'?=', $chn);
	}
	return $chn;
} }
//***** encoder quoted-printable ******
if (! function_exists('quotedPrintable')) {
function quotedPrintable(&$texte, $ncar=76) {
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
				$chn .= $lign2.'='."\n";
				$lign2 = '';
			}
			$lign2 .= $car;
		}
		$chn .= $lign2."\n";
	}
	return substr($chn, 0, -1);
} }

	$adremail = 'philippe@vertige.org';

	$msgsuj = 'test envoi';

	//--- texte
	$msgmsg = 'Bonjour.'."\n\n".'hello'."\n\n";
	$msgtet = 'Content-Type:text/plain; charset='.CHAR7MAIL."\n".'From:'.$retour_email_admin."\n".'Reply-To:'.$retour_email_admin."\n";
	//.'Content-Transfer-Encoding:quoted-printable'."\n";	quotedPrintable($msgmsg)

/*
	//--- html
	$msgmsg='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head><body>
	<h1>test email</h1>
	<p>&nbsp;</p>
	</body></html>
	</html>';
	$msgtet = 'Content-Type:text/html; charset='.CHAR7MAIL."\n".'From:'.$retour_email_admin."\n".'Reply-To:'.$retour_email_admin."\n";
*/

	//--- Envoi d'un email
	if ($adremail && mail_beta($adremail, encodeHeader($msgsuj), $msgmsg, $msgtet, '-f philippe@vertige.org'))
		echo 'Le formulaire est envoyé.\nMerci pour votre participation.';
	else
		echo 'L\'envoi du formulaire a échoué';
?>
