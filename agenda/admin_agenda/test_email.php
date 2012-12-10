<?php
error_reporting(E_ALL);

	/**********************************************
	****** Envoyer test ******
echo '<hr />1 : ',ini_get('SMTP');
ini_set('SMTP', 'desmondtutu.all2all.org');
echo '<hr />2 : ',ini_get('SMTP'),'<hr />';
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
	$msgtet = 'Content-Type:text/plain; charset='.CHAR7MAIL."\n".'Content-Transfer-Encoding:quoted-printable'."\n".'From:'.$retour_email_admin."\n".'Reply-To:'.$retour_email_admin."\n";

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
/*
//	if ($adremail && mail_beta($adremail, encodeHeader($msgsuj), $msgmsg, $msgtet, '-f philippe@vertige.org'))
	if ($adremail && imap_mail($adremail, encodeHeader($msgsuj), quotedPrintable($msgmsg), $msgtet, NULL, NULL, 'philippe@vertige.org'))
		echo 'Le formulaire est envoyé.\nMerci pour votre participation.';
	else
		echo 'L\'envoi du formulaire a échoué';
*/


/*
//$host='{desmondtutu.all2all.org:993/imap/ssl/novalidate-cert}';
//$host='{maximusconfessor.all2all.org:143/imap}';
//$host='{desmondtutu.all2all.org:143/imap}';
$host='{desmondtutu.all2all.org}';

//$user='y-media';
//$user='vertige0007';
$user='';

//$pass='Jufeui4KJ15eUYapd21';
//$pass='pixojeune';
$pass='';

if ($mbox=imap_open($host, $user, $pass)) {
    imap_mail($adremail, encodeHeader($msgsuj), quotedPrintable($msgmsg), $msgtet);
} else {
	echo '<hr />ERREUR imap_last_error : ',imap_last_error();
}
	echo '<hr />imap_last_error : ',imap_last_error();
*/

echo 'test mail factory 6 : ';

require("Mail.php");

/* mail setup recipients, subject etc */
$recipients = "philippe@vertige.org";
$headers["From"] = "info@demandezleprogramme.be";
$headers["To"] = "philippe@vertige.org";
$headers["Subject"] = "test mail factory";
$mailmsg = "Hello, This is a test.";

/* SMTP server name, port, user/passwd */
$smtpinfo["host"] = "desmondtutu.all2all.org";
$smtpinfo["port"] = "25";
$smtpinfo["date"] = gmdate("m/d/Y g:i:s A");
/*
$smtpinfo["auth"] = true;
$smtpinfo["username"] = "";
$smtpinfo["password"] = "";
*/
echo 'avant',$smtpinfo["date"];

/* Create the mail object using the Mail::factory method */
$mail_object =& Mail::factory("smtp", $smtpinfo);
//echo gettype($mail_object),' : ',$mail_object;

/* Ok send mail */
$res = $mail_object->send($recipients, $headers, $mailmsg); 
echo gettype($res),' : ',$res;

echo 'apres';
?>
