<?
	require('admEntete.php');

	//--- rendre les paramètres valides
	if (! isset($oper))
		$oper = '';

	if (! ($protectacces & 4096)) {
		//--- accès limité : rediriger
		mysql_close($db_link);
		header('Location:letrMenu.php?tci='.$tci);
		exit;
	}

	if (! isset($lletr))
		$lletr = isset($_SESSION['idnletter']) ? $_SESSION['idnletter'] : '';
	else if ($lletr)
		$_SESSION['idnletter'] = $lletr;

	//--- initialiser le message et la redirection
	$alerter = '';
	$diriger = '';

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

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />
</head>

<body onload="pagechargee()">
<div class="cmsdivtab">
<table cellspacing="0" cellpadding="0" class="cmstabtab">
<?
	echo '<tr><td class="cmsentete"><img src="matos/admlogo.gif" class="cmslogo" alt="" /><span class="cmstetitr">',ADMINENTETE,'</span></td></tr>',"\n",'<tr><td class="cmstabbox">',"\n";

	echo '<div class="divcentre"><a href="letrMenu.php?tci=',$tci,'"><b>Aller au menu</b></a></div>',"\n";
	echo '<div class="divgauche"><img src="matos/puce.gif" alt="" /> Inscrire ou désinscrire un membre à une liste de diffusion<a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

	//----- obtenir les listes
	$sql = "SELECT lletr,lcode FROM cmsnletter WHERE letat='0' ORDER BY lcode";
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	$tliletr = array(); $tlinom = array();
	$g = $lletr; $lletr = '';
	for ($k = 0; $data = mysql_fetch_array($req, MYSQL_ASSOC); $k++) {
		$tliletr[$k] = $data['lletr'];
		if ($tliletr[$k] == $g)
			$lletr = $g;
		$tlinom[$k] = htmlspecialchars($data['lcode']);
	}
	if ($lletr && $lletr{0} == 'D')
		{ $nomdusite = 'demandezleprogramme.be'; $adrdusite = 'info@demandezleprogramme.be'; }
	else
		{ $nomdusite = NOMDUSITE; $adrdusite = ADRDUSITE; }
	if (! isset($eadr))
		$eadr = '';
	else if (! preg_match('/^\S+@\S+\.\S+$/', $eadr) || strpos($eadr, '\'')!==false || strpos($eadr, '"')!==false)
		$alerter = 'L\'adresse e-mail n\'est pas valide';
	if (! isset($llang))
		$llang = 'fr';
/*	if (! isset($datnaiss) || $datnaiss == '' || ($lletr && $lletr{0} == 'S'))
		$datnaiss = '0000-00-00'; AND datnaiss='$datnaiss' */

	//----- inscrire ou désinscrire
	if ($oper == 'enreg' && $lletr && ! $alerter) {
		$sql = "SELECT ladrm,lletr,letat,lcode FROM cmsnletter WHERE (letat='0' OR ladrm='$eadr' AND letat<>'0') AND lletr='$lletr' ORDER BY letat";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$data = mysql_fetch_array($req, MYSQL_ASSOC);
		if ($data && $data['letat'] == '0') {
			$chn = $data['ladrm'].'?eadr='.urlencode($eadr);

			$data = mysql_fetch_array($req, MYSQL_ASSOC);
			if ($inscr == 'Y') //--- inscrire
				if ($data && $data['letat'] == '5')
					$alerter = 'Ce membre est déjà inscrit à cette liste de diffusion';
/*				else if (! preg_match('/^\d{4}\D\d{2}\D\d{2}$/', $datnaiss))
					$alerter = 'La date de naissance n\'est pas valide (aaaa-mm-jj)';
				else if ($lletr{0} != 'S' && ($datnaiss < '1800' || $datnaiss > '2200'))
					$alerter = 'La date de naissance ne convient pas'; ,datnaiss='$datnaiss' */
				else {
					if ($data)
						$g = $data['lcode'];
					else {
						$g = time();
						$k = isset($lconfirm) ? '4' : '5';
						$sql = "INSERT INTO cmsnletter SET ladrm='$eadr',lletr='$lletr',letat='$k',lcode='$g'";
						$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
					}
					if (isset($lconfirm)) {
						$k = 'From: '.$nomdusite.' <'.$adrdusite.'>'."\n";
						$k .= 'Content-Type: text/plain; charset="ISO-8859-1"'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";

						switch ($llang) {
							case 'fr': $msg = 'Bonjour.'."\n\n".'Vous désirez recevoir la newsletter de '.$nomdusite.' ?'."\n\n".'Pour vous inscrire, veuillez cliquer sur ce lien :'."\n".$chn.'&letrok='.$g; break;
							case 'nl': $msg = 'Hallo.'."\n\n".'U wenst onze nieuwsbrief van '.$nomdusite.' te ontvangen ?'."\n\n".'Klik op deze link om u in te schrijven :'."\n".$chn.'&letrok='.$g; break;
							default: $msg = 'Hello.'."\n\n".'You want to receive newsletter from '.$nomdusite.' ?'."\n\n".'To subscribe, please click on this link :'."\n".$chn.'&letrok='.$g;;
						}
						if (@mail_beta($eadr, encodeHeader($nomdusite.' - newsletter : inscription'), quotedPrintable($msg), $k, '-f '.$adrdusite))
							$alerter = 'Ce membre va recevoir un e-mail pour confirmer son inscription à cette liste de diffusion';
						else
							$alerter = 'L\'envoi d\'un email a échoué';
					}
					else
						$alerter = 'Ok, ce membre est inscrit à cette liste de diffusion';
					$eadr = '';
				}
			else //--- désinscrire
				if ($data && $data['letat'] == '5') {
					if (isset($lconfirm)) {
						$k = 'From: '.$nomdusite.' <'.$adrdusite.'>'."\n";
						$k .= 'Content-Type: text/plain; charset="ISO-8859-1"'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";

						switch ($llang) {
							case 'fr': $msg = 'Bonjour.'."\n\n".'Vous désirez ne plus recevoir la newsletter de '.$nomdusite.' ?'."\n\n".'Pour vous désinscrire, veuillez cliquer sur ce lien :'."\n".$chn.'&noletr='.$data['lcode']; break;
							case 'nl': $msg = 'Hallo.'."\n\n".'U wenst onze nieuwsbrief van '.$nomdusite.' niet meer te ontvangen ?'."\n\n".'Klik op deze link om uw inschrijving te annuleren :'."\n".$chn.'&noletr='.$data['lcode']; break;
							default: $msg = 'Hello.'."\n\n".'You don\'t want to receive newsletter from '.$nomdusite.' anymore ?'."\n\n".'To unsubscribe, please click on this link :'."\n".$chn.'&noletr='.$data['lcode'];
						}
						if (@mail_beta($eadr, encodeHeader($nomdusite.' - newsletter : desinscription'), quotedPrintable($msg), $k, '-f '.$adrdusite))
							$alerter = 'Ce membre va recevoir un e-mail pour confirmer sa désinscription de cette liste de diffusion';
						else
							$alerter = 'L\'envoi d\'un email a échoué';
					}
					else {
						$k = time() - 604800;
						$sql = "DELETE FROM cmsnletter WHERE lletr='$lletr' AND letat='5' AND ladrm='$eadr' OR letat='4' AND lcode<'$k'";
						$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

						$alerter = 'Ok, ce membre n\'est plus inscrit à cette liste de diffusion';
					}
					$eadr = '';
				}
				else
					$alerter = 'Ce membre n\'est pas inscrit à cette liste de diffusion';
		}
		else
			$alerter = 'Il manque cette liste';
	}
	unset($data, $msg);

	//--- formulaire
	echo '<form action="letrInscr.php?tci=',$tci,'" method="post"><fieldset style="border:0;"><table border="0" align="center" width="80%" cellpadding="6" cellspacing="0">',"\n";
	echo '<tr><td><label for="leadrid">Adresse&nbsp;e-mail</label></td>',"\n",'<td><input type="text" name="eadr" id="leadrid" size="70" value="',isset($eadr) ? htmlspecialchars(stripslashes($eadr)) : '','" class="saisie" /></td></tr>',"\n";

/*	echo '<tr><td><label for="ldatnaissid">Date de naissance</label></td>',"\n",'<td><input type="text" name="datnaiss" id="ldatnaissid" size="15" value="',$datnaiss != '0000-00-00' ? htmlspecialchars(stripslashes($datnaiss)) : '','" class="saisie" />&nbsp;<a href="#calendrier" onclick="return calendate(\'ldatnaissid\')"><img src="matos/calendr.gif" class="imgcal" alt="" title="calendrier" /></a></td></tr>',"\n";
<script type="text/javascript">
<!--
function calendate(cal) {
	window.open('calendate.html?cal='+cal, 'wcal', 'left=200,top=200,width=300,height=190,toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=1');
	return false;
}
//-->
</script> */
	$k = count($tliletr);
	if ($k > 1) {
		$chn = '';
		reset($tliletr);
		while (list($k, $g) = each($tliletr))
			$chn .= '<option value="'.$g.($lletr == $g ? '" selected>' : '">').$tlinom[$k].'</option>';
		echo '<tr><td><label for="lletrid">Listes de diffusion</label></td>',"\n",'<td><select name="lletr" id="lletrid" class="liste">',$chn,'</select></td></tr>',"\n";
	}
	else if ($k)
		echo '<tr><td>Liste de diffusion</td>',"\n",'<td>',$tlinom[0],'<input name="lletr" type="hidden" value="',$tliletr[0],'" /></td></tr>',"\n";
	else {
		echo '<tr><td>Liste de diffusion</td>',"\n",'<td>aucune<input name="lletr" type="hidden" value="" /></td></tr>',"\n";
		$alerter = 'Il n\'y a aucune liste de diffusion pour inscrire ou désinscrire';
		$diriger = 'letrMenu.php?tci='.$tci;
	}
	echo '<tr><td style="vertical-align:top;">Action</td><td><input type="radio" name="inscr" id="linscr1id" value="Y" class="cocher" ',isset($inscr) && $inscr=='N' ? '/>' : 'checked="checked" />','&nbsp;<label for="linscr1id">Inscrire</label>',"\n";
	echo '<br /><input type="radio" name="inscr" id="linscr2id" value="N" class="cocher" ',isset($inscr) && $inscr=='N' ? 'checked="checked" />' : '/>','&nbsp;<label for="linscr2id">Désinscrire</label></td></tr>',"\n";

	echo '<tr><td colspan="2"><input type="checkbox" name="lconfirm" id="lconfirmid" value="Y" class="cocher" ',isset($lconfirm) ? 'checked="checked" />' : '/>','<label for="lconfirmid"> Envoyer un email pour demander une confirmation</label> &nbsp; &nbsp; &nbsp; &nbsp; Langue <select name="llang" id="llangid" class="liste"><option value="fr"',$llang == 'fr' ? ' selected>' : '>','français</option><option value="nl"',$llang == 'nl' ? ' selected>' : '>','néerlandais</option><option value="en"',$llang == 'en' ? ' selected>' : '>','anglais</option></select></td></tr>',"\n";
	echo '<tr><td></td><td><input type="submit" value="Enregistrer" class="bouton" /></td></tr>',"\n",'</table></fieldset><input name="oper" type="hidden" value="enreg" /></form>',"\n";
?>
		</td>
	</tr>
	<tr><td style="text-align:right"><a href="http://www.vertige.org/" target="_blank" class="cmsareal">conception Vertige asbl</a> &nbsp;</td></tr>
</table>
</div>
<script type="text/javascript">
<!--
<?
	//--- Déconnexion de la DB
	mysql_close($db_link);

	echo $alerter ? 'function pagechargee() { alert("'.$alerter.'"); ' : 'function pagechargee() { ';
	echo $diriger ? 'window.location.href="'.$diriger.'"; }' : '}',"\n";
?>
//-->
</script>
</body>
</html>
