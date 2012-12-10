<?
//----- Newsletter inscription (critère supplémentaire chercher "datnaiss") -----

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

	if (! isset($prmsys['inletter'])) {
		echo ' Erreur : le paramètre "identifiant(s) de liste de diffusion" est manquant. ';
		return;
	}
	//----- contrôler + obtenir les noms de liste
	$sql = "SELECT lletr,lcode FROM cmsnletter WHERE letat='0'";
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	$tlinom = array();
	while ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
		$k = array_search($data['lletr'], $prmsys['inletter']);
		if ($k !== false)
			$tlinom[$k] = htmlspecialchars($data['lcode']);
	}
	$liletr = '';
	reset($prmsys['inletter']);
	while (list($k, $g) = each($prmsys['inletter'])) {
		if (! isset($tlinom[$k])) {
			echo ' Erreur : l\'identifiant de liste de diffusion "',$g,'" n\'est pas reconnu. ';
			return;
		}
		if (isset($oper) && $oper == $g) {
			$liletr = $g;
			unset($oper);
			break;
		}
	}
	$jsalert = '';
	if (! isset($eadr))
		$eadr = '';
	else if (! preg_match('/^\S+@\S+\.\S+$/', $eadr) || strpos($eadr, '\'')!==false || strpos($eadr, '"')!==false)
		switch ($lng) {
			case 'fr': $jsalert = 'L\'adresse e-mail n\'est pas valide'; break;
			case 'nl': $jsalert = 'Het e-mail adres is niet correct'; break;
			default: $jsalert = 'The e-mail address is not valid';
		}
	if (! isset($datnaiss) || $datnaiss=='jj-mm-aaaa')
		$datnaiss = '';

	//----- inscrire ou désinscrire
	if ($liletr && ! $jsalert) {
		$sql = "SELECT ladrm,lletr,letat,lcode FROM cmsnletter WHERE (letat='0' OR ladrm='$eadr' AND letat<>'0') AND lletr='$liletr' ORDER BY letat";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$data = mysql_fetch_array($req, MYSQL_ASSOC);
		if ($data && $data['letat'] == '0') {
			$chn = $data['ladrm'].'?eadr='.urlencode($eadr);

			$data = mysql_fetch_array($req, MYSQL_ASSOC);
			if ($inscr == 'Y') //--- inscrire
				if ($data && $data['letat'] == '5')
					switch ($lng) {
						case 'fr': $jsalert = 'Vous êtes déjà inscrit à la liste de diffusion'; break;
						case 'nl': $jsalert = 'U bent al ingeschreven op de mailinglijst'; break;
						default: $jsalert = 'You are already registerd in the mailing list';
					}
				else if ($datnaiss && ! preg_match('/^\d{2}\D\d{2}\D\d{4}$/', $datnaiss))
					switch ($lng) {
						case 'fr': $jsalert = 'La date de naissance n\'est pas valide (jj-mm-aaaa)'; break;
						case 'nl': $jsalert = 'De geboortedatum is niet correct (dd-mm-jjjj)'; break;
						default: $jsalert = 'Birth date is not valid (dd-mm-yyyy)';
					}
				else {
					$k = $datnaiss ? substr($datnaiss, 6, 4).'-'.substr($datnaiss, 3, 2).'-'.substr($datnaiss, 0, 2) : '0000-00-00';
/*					if ($data)
						$g = $data['lcode'];
					else {
					} */
					$g = time();
					$sql = "INSERT INTO cmsnletter SET ladrm='$eadr',lletr='$liletr',letat='5',lcode='$g',datnaiss='$k'";
					$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					switch ($lng) {
						case 'fr': $jsalert = 'Ok, vous êtes inscrit à la liste de diffusion'; break;
						case 'nl': $jsalert = 'Ok, U bent ingeschreven op de mailinglijst'; break;
						default: $jsalert = 'Ok, you are registerd in the mailing list';
					}
/*					$k = 'From: '.NOMDUSITE.' <'.ADRDUSITE.'>'."\n";
					$k .= 'Content-Type: text/plain; charset="ISO-8859-1"'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";
					switch ($lng) {
						case 'fr': $msg = 'Bonjour.'."\n\n".'Vous désirez recevoir la newsletter de '.NOMDUSITE.' ?'."\n\n".'Pour vous inscrire, veuillez cliquer sur ce lien :'."\n".$chn.'&letrok='.$g; break;
						case 'nl': $msg = 'Hallo.'."\n\n".'U wenst onze nieuwsbrief van '.NOMDUSITE.' te ontvangen ?'."\n\n".'Klik op deze link om u in te schrijven :'."\n".$chn.'&letrok='.$g; break;
						default: $msg = 'Hello.'."\n\n".'Would you like to receive newsletter of '.NOMDUSITE.' ?'."\n\n".'To subscribe, please click on this link :'."\n".$chn.'&letrok='.$g; break;
					}
					if (@mail_beta($eadr, encodeHeader(NOMDUSITE.' - newsletter : inscription'), quotedPrintable($msg), $k, '-f '.ADRDUSITE))
						switch ($lng) {
							case 'fr': $jsalert = 'Merci de l\'intérêt que vous portez à '.NOMDUSITE.'\nVous allez recevoir un e-mail pour confirmer votre inscription à la liste de diffusion'; break;
							case 'nl': $jsalert = 'Bedankt voor uw interesse in '.NOMDUSITE.'\nU zal een e-mail krijgen om uw inschrijving te bevestigen.'; break;
							default: $jsalert = 'Thank you for you interest in '.NOMDUSITE.'\nYou will receive an e-mail to confirm your subscription to the mailing list';
						}
					else
						switch ($lng) {
							case 'fr': $jsalert = 'Désolé, votre inscription à la liste de diffusion a échoué'; break;
							case 'nl': $jsalert = 'Sorry, uw inschrijving in onze mailinglijst is mislukt'; break;
							default: $jsalert = 'Sorry, your subscription to the mailing list failed';
						} */
				}
			else //--- désinscrire
				if ($data && $data['letat'] == '5') {
					$k = 'From: '.NOMDUSITE.' <'.ADRDUSITE.'>'."\n";
					$k .= 'Content-Type: text/plain; charset="ISO-8859-1"'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";
					switch ($lng) {
						case 'fr': $msg = 'Bonjour.'."\n\n".'Vous désirez ne plus recevoir la newsletter de '.NOMDUSITE.' ?'."\n\n".'Pour vous désinscrire, veuillez cliquer sur ce lien :'."\n".$chn.'&noletr='.$data['lcode']; break;
						case 'nl': $msg = 'Hallo.'."\n\n".'U wenst onze nieuwsbrief van '.NOMDUSITE.' niet meer te ontvangen ?'."\n\n".'Klik op deze link om uw inschrijving te annuleren :'."\n".$chn.'&noletr='.$data['lcode']; break;
						default: $msg = 'Hello.'."\n\n".'You don\'t want to receive anymore the newsletter of '.NOMDUSITE.' ?'."\n\n".'To unsubsribe, please click on this link :'."\n".$chn.'&noletr='.$data['lcode'];
					}
					if (@mail_beta($eadr, encodeHeader(NOMDUSITE.' - newsletter : desinscription'), quotedPrintable($msg), $k, '-f '.ADRDUSITE))
						switch ($lng) {
							case 'fr': $jsalert = 'Vous allez recevoir un e-mail pour confirmer votre désinscription de la liste de diffusion'; break;
							case 'nl': $jsalert = 'U zal een e-mail krijgen om de annulering van uw inschrijving te bevestigen.'; break;
							default: $jsalert = 'You will receive an e-mail to confirm your unsubscription from the mailing list';
						}
					else
						switch ($lng) {
							case 'fr': $jsalert = 'Désolé, votre désinscription de la liste de diffusion a échoué'; break;
							case 'nl': $jsalert = 'Sorry, de annulering van uw inschrijving in onze mailinglijst is mislukt'; break;
							default: $jsalert = 'Sorry, your unsubscription from the mailing list failed';
						}
				}
				else
					switch ($lng) {
						case 'fr': $jsalert = 'Vous n\'êtes pas inscrit à la liste de diffusion'; break;
						case 'nl': $jsalert = 'U bent niet ingeschreven op de mailinglijst'; break;
						default: $jsalert = 'You are not registerd in the mailing list';
					}
		}
		else
			switch ($lng) {
				case 'fr': $jsalert = 'Désolé, l\'opération n\'a pas pu aboutir'; break;
				case 'nl': $jsalert = 'Sorry, de handeling is mislukt'; break;
				default: $jsalert = 'Sorry, the operation could not succeed';
			}
	}
	//----- confirmer l'inscription
	if (isset($letrok) && ! $jsalert) {
		$sql = "SELECT L.lletr,A.letat FROM cmsnletter L,cmsnletter A WHERE L.letat='0' AND L.lletr=A.lletr AND A.ladrm='$eadr' AND A.letat<>'0' AND A.lcode='$letrok'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
			if ($data['letat'] != '5') {
				$sql = "UPDATE cmsnletter SET letat='5' WHERE ladrm='$eadr' AND lcode='$letrok' AND letat<>'0'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
			}
			$liletr = $data['lletr'];
			$inscr = 'Y';
			switch ($lng) {
				case 'fr': $jsalert = 'Ok, maintenant vous êtes inscrit à la liste de diffusion'; break;
				case 'nl': $jsalert = 'Ok, U bent nu ingeschreven op de mailinglijst'; break;
				default: $jsalert = 'Ok, now you are registerd in the mailing list';
			}
		}
		else
			switch ($lng) {
				case 'fr': $jsalert = 'Désolé, l\'opération n\'a pas pu aboutir'; break;
				case 'nl': $jsalert = 'Sorry, de handeling is mislukt'; break;
				default: $jsalert = 'Sorry, the operation could not succeed';
			}
		unset($letrok);
	}
	//----- confirmer la désinscription
	if (isset($noletr) && ! $jsalert) {
		$sql = "SELECT L.lletr,A.letat FROM cmsnletter L,cmsnletter A WHERE L.letat='0' AND L.lletr=A.lletr AND A.ladrm='$eadr' AND A.letat<>'0' AND A.lcode='$noletr'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
			$k = time() - 604800;
			$sql = "DELETE FROM cmsnletter WHERE ladrm='$eadr' AND lcode='$noletr' AND letat<>'0' OR letat='4' AND lcode<'$k'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$liletr = $data['lletr'];
			$inscr = 'N';
			switch ($lng) {
				case 'fr': $jsalert = 'Ok, vous n\'êtes plus inscrit à la liste de diffusion'; break;
				case 'nl': $jsalert = 'Ok, U bent niet meer ingeschreven op de mailinglijst'; break;
				default: $jsalert = 'Ok, you are not registerd in the mailing list anymore';
			}
		}
		else
			switch ($lng) {
				case 'fr': $jsalert = 'Désolé, l\'opération n\'a pas pu aboutir'; break;
				case 'nl': $jsalert = 'Sorry, de handeling is mislukt'; break;
				default: $jsalert = 'Sorry, the operation could not succeed';
			}
		unset($noletr);
	}
	echo '<script type="text/javascript">',"\n",'<!--',"\n",'function pagechargee() { ',$jsalert ? 'alert("'.$jsalert.'"); }' : '}',"\n",'//-->',"\n",'</script>',"\n";
	unset($jsalert, $data, $msg);

	//--- formulaire
	echo '<form action="',$_SERVER['REQUEST_URI'],'" method="post"><fieldset>',"\n";
	switch ($lng) {
		case 'de': $k = 'Email-Adresse'; break;
		case 'fr': $k = 'Adresse&nbsp;email'; break;
		case 'nl': $k = 'E-mail&nbsp;adres'; break;
		default: $k = 'Email&nbsp;address';
	}
	echo '<label for="leadrid" style="margin:4px">',$k,'</label><br />',"\n",'<input type="text" name="eadr" id="leadrid" size="48" value="',$liletr ? htmlspecialchars(stripslashes($eadr)) : '','" class="sys-foinput" style="margin:4px" /><br />',"\n";

/*	echo '<script type="text/javascript">',"\n",'<!--',"\n",'function calendatp(cal) { window.open("../publiq/calendatp.html?cal="+cal, "calw", "left=200,top=200,width=300,height=190,toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=1"); return false; }',"\n",'//-->',"\n",'</script>',"\n";	*/
	if ($liletr && $liletr{0} == 'D') {
		switch ($lng) {
			case 'fr': $k = 'Date de naissance'; break;
			case 'nl': $k = 'Geboortedatum'; break;
			default: $k = 'Birth date';
		}
		echo '<label for="ldatnaissid" style="margin:4px">',$k,'</label><br />',"\n",'<input type="text" name="datnaiss" id="ldatnaissid" size="15" value="',isset($datnaiss) ? htmlspecialchars(stripslashes($datnaiss)) : 'jj-mm-aaaa','" class="sys-foinput" style="margin:4px" />&nbsp;(facultatif)<br />',"\n";
	}

	if (count($prmsys['inletter']) > 1) {
		$chn = '';
		reset($prmsys['inletter']);
		while (list($k, $g) = each($prmsys['inletter']))
			$chn .= '<option value="'.$g.($liletr == $g ? '" selected>' : '">').$tlinom[$k].'</option>';
		switch ($lng) {
			case 'fr': $k = 'Listes de diffusion'; break;
			case 'nl': $k = 'Mailinglijsten'; break;
			default: $k = 'Mailing lists';
		}
		echo '<label for="loperid" style="margin:4px">',$k,'</label><br />',"\n",'<select name="oper" id="loperid" class="sys-foselect" style="margin:4px">',$chn,'</select><br />',"\n";
	}
	else
		echo '<input name="oper" type="hidden" value="',$prmsys['inletter'][0],'" />',"\n";
	switch ($lng) {
		case 'de': $k = 'Sich&nbsp;eintragen'; break;
		case 'fr': $k = 'S\'inscrire'; break;
		case 'nl': $k = 'Inschrijven'; break;
		default: $k = 'Subscribe';
	}
	echo '<input type="radio" name="inscr" id="linscr1id" value="Y" class="sys-foradio" ',isset($inscr) && $inscr=='N' ? '/>' : 'checked="checked" />','&nbsp;<label for="linscr1id">',$k,'</label>',"\n";
	switch ($lng) {
		case 'de': $k = 'Aus&nbsp;der&nbsp;Liste&nbsp;herausnehmen'; break;
		case 'fr': $k = 'Se&nbsp;désinscrire'; break;
		case 'nl': $k = 'Inschrijving&nbsp;annuleren'; break;
		default: $k = 'Unsubscribe';
	}
	echo ' &nbsp; &nbsp; <input type="radio" name="inscr" id="linscr2id" value="N" class="sys-foradio" ',isset($inscr) && $inscr=='N' ? 'checked="checked" />' : '/>','&nbsp;<label for="linscr2id">',$k,'</label>',"\n";
	echo ' &nbsp; &nbsp; <input type="submit" value=" Ok " class="sys-fobouton" />',"\n",'</fieldset></form>',"\n";
?>
