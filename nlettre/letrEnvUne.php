<?
//---------- Envoi de newsletter / DLP la Une (attention "letrEnvoi" => "letrEnvUne") ----------
	define('CHAR7MAIL', 'ISO-8859-1;');
	require('admEntete.php');

	//--- rendre les paramètres valides
	if (! isset($oper))
		$oper = '';
	if (! isset($modl) || ! is_numeric($modl))
		$modl = 0;

	if (! ($protectacces & 4096)) {
		//--- accès limité : rediriger
		mysql_close($db_link);
		header('Location:letrMenu.php?tci='.$tci);
		exit;
	}
	//--- initialiser le message et la redirection
	$alerter = '';
	$diriger = '';

	$adrdusite = ADRDUSITE;
//$adrdusite = 'info@demandezleprogramme.be';
//$adrdusite = 'interview@demandezleprogramme.be';

	//--- le mois courant
	$moiscdat = (int) date('Y');
	$k = (int) date('n');
	if ((int) date('j') > 10)
		if (++$k > 12) { $moiscdat++; $k = 1; }
	$moisctxt = array(1=>'de janvier','de février','de mars','d\'avril','de mai','de juin','de juillet','d\'août','de septembre','d\'octobre','de novembre','de décembre');
	$moisctxt = $moisctxt[$k];
	$moiscdat .= '-'.($k <= 9 ? '0'.$k : $k).'-';

require('../agenda/inc_var.php');
unset($email_admin_site,$retour_email_admin,$email_moderateur_site,$retour_email_moderateur,$email_retour_erreur,$table_lieu,$table_user_agenda,$table_evenements_agenda,$table_avis_agenda,$table_avis_mailing,$table_logs,$table_ag_conc_fiches,$table_ag_conc_joueur,$table_ag_conc_historique,$table_im_crypt,$maxWidth,$maxHeight,$maxWidth_pic,$maxHeight_pic,$folder_pics_vignettes_lieux_culturels,$folder_pics_event,$w_absolue,$w_vi_absolue,$maxWidth_conc_vignette,$maxHeight_conc_vignette,$maxWidth_conc_pics,$maxHeight_conc_pics,$folder_vignettes_concours,$group_admin_spec_noms,$regions,$NomDuMois,$css_email,$folder_videos,$type_log_array,$groupes_joueurs);
if (! isset($genres)) {
	echo 'Il manque les genres dans agenda/inc_var.php<br />'; exit;
}

function obtenirune(&$chn) {
	global $moiscdat, $genres;

	$sql = " WHERE date_event_debut LIKE '$moiscdat%' AND pic_event_1='set'";
	$sql = 'SELECT id_event,lieu_event,nom_event,date_event_debut,date_event_fin,resume_event,genre_event,pic_event_1,nom_lieu FROM ag_event INNER JOIN ag_lieux ON lieu_event=id_lieu AND cotisation_lieu>SUBDATE(CURDATE(),INTERVAL 1 MONTH) '.$sql.' ORDER BY date_event_debut';
//--- echo $sql,'<hr />';

	$tid_lieu = array();
	$reponse = mysql_query($sql);
	while ($donnees = mysql_fetch_array($reponse, MYSQL_ASSOC)) {
		if (isset($tid_lieu[$donnees['lieu_event']]))
			$tid_lieu[$donnees['lieu_event']]++;
		else
			$tid_lieu[$donnees['lieu_event']] = 1;
		if ($tid_lieu[$donnees['lieu_event']] > 1) //--- 1 max par lieu
			continue;

		$id_event = $donnees['id_event'];
		$chn .= '<div class="breve">'."\n";

		//--- VIGNETTE EVENEMENT / <img id="unedans'.$id_event.'" src="../agenda/pics_events/vi_event_'.$id_event.'_1.jpg" title="'.$donnees['nom_event'].'" alt="'.$donnees['nom_event'].'" />
		if (isset($donnees['pic_event_1']) AND $donnees['pic_event_1'] == 'set')
			$chn .= '<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event='.$id_event.'" class="breve_pic"><img src="http://www.demandezleprogramme.be/agenda/pics_events/vi_event_'.$id_event.'_1.jpg" title="'.$donnees['nom_event'].'" alt="'.$donnees['nom_event'].'" /></a>'."\n";

		//--- NOM EVENEMENT
		$chn .= '<a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event='.$id_event.'" title="Voir en détail" class="breve_titre">'.$donnees['nom_event'].'</a><br />'."\n";

		//--- LIEU
		$chn .= '<a href="http://www.demandezleprogramme.be/-Details-lieux-culturels-?id_lieu='.$donnees['lieu_event'].'" title="Lieu où se joue le spectacle" class="breve_lieu">'.$donnees['nom_lieu'].'</a>'."\n";

		//--- GENRE
		if (isset($donnees['genre_event']) AND $donnees['genre_event'] != NULL AND isset($genres[$donnees['genre_event']]))
			$chn .= '<acronym title="Genre du spectacle" class="breve_genre">'.$genres[$donnees['genre_event']].'</acronym>'."\n";

		//--- DATES
		$sql = $donnees['date_event_debut'];
		$chn .= '<acronym title="Période de représentation" class="breve_date">'.substr($sql, 8, 2).'/'.substr($sql, 5, 2).'/'.substr($sql, 0, 4).' &gt;&gt; ';
		$sql = $donnees['date_event_fin'];
		$chn .= substr($sql, 8, 2).'/'.substr($sql, 5, 2).'/'.substr($sql, 0, 4).'</acronym><br /><br />'."\n";

		//--- TEXTE INTRODUCTIF
		if ($donnees['resume_event'])
			$chn .= str_replace(array('<br>', '<br />', '<BR>', '<BR />'), ' - ', $donnees['resume_event'])."\n";

		$chn .= '<div class="en_savoir_plus"><a href="http://www.demandezleprogramme.be/-Detail-agenda-?id_event='.$id_event.'"><img src="http://www.demandezleprogramme.be/nmodele/DLPuneplus.jpg" title="En savoir plus" alt="En savoir plus" /></a></div>'."\n".'<br style="clear:both;line-height:1px;" />'."\n".'</div>'."\n";
	}
}

	//-------- boundary --------
	function getboundary($chn) {
		$chn = '=_'.$chn.'_';
		for ($k = 0; $k < 20; $k++)
			$chn .= substr('abcdefghijklmnopqrstuvwxyz0123456789', rand(0, 35), 1);
		return $chn;
	}
	//-------- encoder selon RFC2047 --------
	function encodeHeader($chn) {
		preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $chn, $tab);
		while (list(, $s1) = each($tab[1])) {
			$s2 = preg_replace('/([\x20\x80-\xFF])/e', '"=".strtoupper(dechex(ord("\1")))', $s1);
			$chn = str_replace($s1, '=?ISO-8859-1?Q?'.$s2.'?=', $chn);
		}
		return $chn;
	}
	//-------- lignes de 990 caractères maximum --------
	function couper990ligne(&$chn) {
		$ofs = 0;
		$jsq = strlen($chn) - 990; if ($jsq < 0) $jsq = 0;
		while ($ofs < $jsq) {
			$k = $ofs; $k += 990;
			while ($k >= $ofs && $chn{$k} != "\n")
				$k--;
			if ($k >= $ofs)
				$ofs = ++$k;
			else {
				$k = $ofs; $k += 990;
				while ($k >= $ofs && $chn{$k} != ' ')
					$k--;
				if ($k >= $ofs)
					$chn{$k} = "\n";
				else {
					$k = $ofs; $k += 990;
					while ($k >= $ofs && $chn{$k} != '<')
						$k--;
					if ($k < $ofs)
						{ $k = $ofs; $k += 990; }
					$chn = substr($chn, 0, $k)."\n".substr($chn, $k);
					$jsq++;
				}
				$ofs = ++$k;
			}
		}
	}
	/**********************************************
	****** Obtenir sujet, message, fichiers *******
	**********************************************/
	function imagelaune($fich) {
		global $tficid, $tfmcid, $volume;

		$fich = $fich[1];
		$tficid[] = addslashes($fich);
		$tfmcid[] = 'Uimage/jpeg';
		$volume += (int) (filesize('../agenda/pics_events/'.$fich) * 1.37 + 190);

		return '<img src="cid:'.$fich.'"';
	}
	function obtenirmessage() {
		global $modl, $nlsuj, $nlang, $nltext, $nlhtml, $tficid, $tfmcid, $tnfich, $lidelai, $linblot, $nbrenv, $alerter, $sql, $req, $data, $chn, $volume, $moisctxt;

		$sql = "SELECT nlid,quoi,nmulti,sujet,texte,html FROM cmsnlmsg WHERE quoi='modele' OR quoi='prepar' AND modl=$modl ORDER BY quoi";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$fich = ''; $nlid = 0; $volume = 265;
		while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
			if ($data['quoi'] == 'modele') {
				$lidelai = $data['nmulti']; if ($lidelai < 1) $lidelai = 30;
				$linblot = $data['sujet']; if ($linblot < 1) $linblot = 10;
				$chn = explode('^|~', addslashes($data['texte']));
				reset($chn);
				while (list($k) = each($chn))
					if (! ($k & 1) && $chn[$k] == $modl)
						{ $fich = $chn[++$k]; break; }
			}
			else if ($fich) {
				$nlid = $data['nlid'];
				if ($nlsuj = addslashes($data['sujet']))
					$volume += strlen($nlsuj) + 157;
				$nlang = $data['nmulti'];
				if ($nltext = addslashes($data['texte']))
					$volume += strlen($nltext) + 157;
				$nlhtml = $data['html'];
			}
		mysql_free_result($req); unset($data);

		if ($nlid && $nlhtml) {
			$fich = 'nmodele/'.$fich.'.php';
			if (file_exists('../'.$fich) && $chn = file_get_contents('../'.$fich)) {
				//----- images du modèle : src = cid
				$chn = preg_replace('/<img([^<>]*) id="dedans\w+"([^<>]*) src="[^<">]*\/nmodele\/([^<">]*)"/', '<img\\1 \\2 src="cid:\\3"', $chn);
				//----- images de html : src = cid
				$sql = preg_replace('/<img([^<>]*) id="dans\d+"([^<>]*) src="[^<">]*\/nmedia\/([^<">]*)"/', '<img\\1 \\2 src="cid:\\3"', $nlhtml);

				$g = '<? $chn = \'intro\'; include(\'../nlettre/letrContenu.php\'); ?>';
				if ($k = strpos($chn, $g)) {
					$nlhtml = substr($chn, 0, $k);
					$k += strlen($g);
					$chn = substr($chn, $k);

					$g = '<? $chn = \'html\'; include(\'../nlettre/letrContenu.php\'); ?>';
					if ($k = strpos($chn, $g)) {
						$nlhtml .= substr($chn, 0, $k);
						$k += strlen($g);
						$nlhtml .= $sql.substr($chn, $k);
					}
					else
						$alerter = 'Il manque \'html\'-include(letrContenu) dans le fichier modèle '.$fich;
				}
				else
					$alerter = 'Il manque \'intro\'-include(letrContenu) dans le fichier modèle '.$fich;
			}
			else
				$alerter = 'Le fichier modèle '.$fich.' est introuvable';
			$chn = ''; $sql = ''; $g = '';

			//--- le mois courant
			$nlhtml = str_replace('###_MOIS_###', $moisctxt, $nlhtml);

			//--- les spectacles de la Une
			$sql = '';
			obtenirune($sql);
			$nlhtml = str_replace('###_LA_UNE_###', $sql, $nlhtml);
			$sql = '';
/* --- télécharger les images à la lecture du message
			$nlhtml = addslashes(preg_replace_callback('/<img id="unedans\w+" src="..\/agenda\/pics_events\/([^<">]*)"/', 'imagelaune', $nlhtml));
*/
			if ($k = strlen($nlhtml))
				$volume += $k + ($k >> 4) + 157;
		}
		else if (! $nlid) {
			$nlsuj = $nlang = $nltext = $nlhtml = '';
			$alerter = $fich ? 'Il manque la newsletter à envoyer' : 'Il manque le modèle';
		}

		if (! $alerter) {
			//----- obtenir les images incorporées et les fichiers attachés
			$sql = "SELECT quoi,texte,html FROM cmsnlmsg WHERE (quoi='modimg' OR quoi='image' OR quoi='attach') AND modl=$modl AND nmulti='6'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			while ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
				$chn = explode('^|~', $data['html']);
				if ($data['quoi'] != 'attach') {
					$tficid[] = addslashes($data['texte']);
					$tfmcid[] = ($data['quoi'] == 'modimg' ? 'M' : 'I').(isset($chn[1]) ? addslashes($chn[1]) : 'image');
				}
				else {
					$tnfich[] = addslashes($data['texte']);
					$nbrenv++;
				}
				$k = isset($chn[0]) ? (int) $chn[0] : 0;
				if ($nlhtml || $data['quoi'] == 'attach')
					$volume += (int) ($k * 1.37 + 190);
			}
		}
	}

	/*****************************
	****** Envoyer par lot *******
	*****************************/
	if ($oper == 'auto') {
		//----- Construire l'email (chn == en-tête)
		$chn = '';
		$msg = '';
		if (isset($tnfich)) {
			$lim_mix = getboundary('mix');
			$k = 'Content-Type: multipart/mixed;'."\n".' boundary="-----'.$lim_mix.'"'."\n";
			if ($chn) $msg .= $k."\r\n"; else $chn = $k;
			$msg .= '-------'.$lim_mix."\n";
		}
		if (isset($tficid) && $nlhtml) {
			$lim_rel = getboundary('rel');
			$k = 'Content-Type: multipart/related;'."\n".' boundary="-----'.$lim_rel.'"'."\n";
			if ($chn) $msg .= $k."\r\n"; else $chn = $k;
			$msg .= '-------'.$lim_rel."\n";
		}
		if ($nltext && $nlhtml) {
			$lim_alt = getboundary('alt');
			$k = 'Content-Type: multipart/alternative;'."\n".' boundary="-----'.$lim_alt.'"'."\n";
			if ($chn) $msg .= $k."\r\n"; else $chn = $k;
			$msg .= '-------'.$lim_alt."\n";
		}

		switch ($nlang) {
			case 'fr': $g = 'Pour ne plus recevoir la newsletter, veuillez cliquer sur ce lien :'; break;
			case 'nl': $g = 'Om de nieuwsbrief niet meer te ontvangen, klik op deze link :'; break;
			default: $g = 'If you don\'t want to receive the newsletter anymore, please click on this link :';
		}
		if ($nltext) {
			$k = 'Content-Type: text/plain; charset='.CHAR7MAIL."\n".'Content-Transfer-Encoding: 8bit'."\n";
			if ($chn) $msg .= $k; else $chn = $k;
			$sql = str_replace("\r", '', "\n".stripslashes(trim($nltext))."\n".'_______________'."\n".$g."\n".'%TDINSCR%'."\n");
			couper990ligne($sql);
			$msg .= str_replace("\n", "\r\n", $sql);
			$sql = '';
		}
		if ($nltext && $nlhtml)
			$msg .= '-------'.$lim_alt."\n";

		if ($nlhtml) {
			$k = 'Content-Type: text/html; charset='.CHAR7MAIL."\n".'Content-Transfer-Encoding: 8bit'."\n";
			if ($chn) $msg .= $k; else $chn = $k;
			$g = '<div><hr />'.$g.'<br />'."\n".'<a href="%LDINSCR%">'."\n".'%SDINSCR%</a></div>'."\n";
			$sql = str_replace("\r", '', "\n".str_replace('</body>', $g.'</body>', stripslashes(trim($nlhtml)))."\n");
			couper990ligne($sql);
			$msg .= str_replace("\n", "\r\n", $sql);
			$sql = '';
		}
		if ($nltext && $nlhtml)
			$msg .= '-------'.$lim_alt.'--'."\n";

		if (isset($tficid) && $nlhtml) {
			reset($tficid);
			while (list($k, $g) = each($tficid)) {
				$msg .= '-------'.$lim_rel."\n";
				$msg .= 'Content-Type: '.substr($tfmcid[$k], 1).'; name="'.$g.'"'."\n";
				$msg .= 'Content-ID: <'.$g.'>'."\n";
				$msg .= 'Content-Transfer-Encoding: base64'."\n";
				if ($tfmcid[$k]{0} == 'U')
					$g = '../agenda/pics_events/'.$g;
				else
					$g = ($tfmcid[$k]{0} == 'M' ? '../nmodele/' : '../nmedia/').$g;
				$msg .= "\r\n".chunk_split(base64_encode(file_get_contents($g)))."\r\n";
			}
			$msg .= '-------'.$lim_rel.'--'."\n";
		}
		if (isset($tnfich)) {
			reset($tnfich);
			while (list(, $g) = each($tnfich)) {
				$msg .= '-------'.$lim_mix."\n";
				$msg .= 'Content-Type: application/octet-stream; name="'.$g.'"'."\n";
				$msg .= 'Content-Disposition: attachment; filename="'.$g.'"'."\n";
				$msg .= 'Content-Transfer-Encoding: base64'."\n";
				$msg .= "\r\n".chunk_split(base64_encode(file_get_contents('../nmedia/'.$g)))."\r\n";
			}
			$msg .= '-------'.$lim_mix.'--'."\n";
		}
		$k = 'From: '.NOMDUSITE.' <'.$adrdusite.'>'."\n".'MIME-Version: 1.0'."\n";
		if ($nltext && $nlhtml || isset($tficid) && $nlhtml || isset($tnfich))
			$chn = $k.$chn.'This is a multi-part message in MIME format.'."\r\n";
		else
			$chn = $k.$chn;

		//----- Envoyer un lot d'emails (constante : 25 secondes maximum pour éviter un timeout)
		$delaiproch = time() + 25;
		if ($nlamail) { //--- vers le destinataire de test
			$k = 'exemple';
			$g = '#exemple';
			if (@mail_beta($nlamail, encodeHeader(stripslashes($nlsuj)), str_replace('%LDINSCR%', $g, str_replace('%SDINSCR%', $k, str_replace('%TDINSCR%', $k, $msg))), $chn, '-f '.$adrdusite)) {
				$nlamail = '';
				$nbrenv++;
			}
			else
				$alerter = 'Attention, l\'envoi de la newsletter a échoué';
		}
		if (! $alerter)
			if (! isset($tnokli))
				$oper = 'fini';
			else { //--- vers les destinataires des listes
				reset($tnokli);
				while (list($k) = each($tnokli))
					$tlipage[$tnokli[$k]] = $tnlurl[$k].'?eadr=';

				$linblot++;
				$sql = 'SELECT ladrm,lletr,lcode FROM cmsnletter WHERE letat=\'5\' AND lletr IN (\''.implode('\',\'',$tnokli).'\') AND lenv<>\'Y\''.stripslashes($ncritr).' GROUP BY ladrm LIMIT '.$linblot;
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				$linblot--;

				$data = mysql_fetch_array($req, MYSQL_ASSOC);
				for ($nk = 0; ! $alerter && $data && $nk < $linblot && time() <= $delaiproch; $nk++) {
					$k = $tlipage[$data['lletr']].$data['ladrm'].'&noletr='.$data['lcode'];
					$g = $tlipage[$data['lletr']].rawurlencode($data['ladrm']).'&noletr='.$data['lcode'];
					if (@mail_beta($data['ladrm'], encodeHeader(stripslashes($nlsuj)), str_replace('%LDINSCR%', htmlspecialchars($g), str_replace('%SDINSCR%', htmlspecialchars($k), str_replace('%TDINSCR%', $k, $msg))), $chn, '-f '.$adrdusite))
						$nbrenv++;
					else
						$alerter = 'Attention, l\'envoi de la newsletter a échoué (après '.$nbrenv.' envois réussis)';

					$k = addslashes($data['ladrm']);
					$sql = "UPDATE cmsnletter SET lenv='Y' WHERE ladrm='$k'";
					$k = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					$data = mysql_fetch_array($req, MYSQL_ASSOC);
				}
				if (! $alerter && ! $data) //--- si envoi complet
					$oper = 'fini';
				mysql_free_result($req); unset($tlipage);
			}
		if ($alerter)
			$oper = 'cours';
		unset($chn, $msg, $nk, $delaiproch);
	}

	/*********************
	****** Envoyer *******
	*********************/
	if ($oper == 'envoi') {
		//----- contrôles
/*		if ($datnaiss1 && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $datnaiss1))
			$alerter = 'La date de début n\'est pas valide (aaaa-mm-jj)';
		if ($datnaiss2 && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $datnaiss2))
			$alerter = 'La date de fin n\'est pas valide (aaaa-mm-jj)'; */
		if ($nlamail) {
			$k = explode(',', $nlamail);
			while (list(, $g) = each($k))
				if (! preg_match('/^\S+@\S+\.\S+$/', trim($g)) || strpos($g, '\'')!==false || strpos($g, '"')!==false || strpos($g, ';')!==false)
					$alerter = 'L\'adresse email n\'est pas valide (plusieurs ,)';
		}
		else if (! isset($tnokli))
			$alerter = 'Il n\'y aucun destinataire, il faut sélectionner au moins une liste de diffusion\net/ou il faut entrer au moins une adresse email';
		if ($nlhtml && substr_count($nlhtml, '</body>') != 1)
			$alerter = 'Le message au format HTML doit contenir une et une seule balise </body>';

		if (! $alerter) {
			//----- mémoriser les destinataires
			if (isset($tnidli)) {
				reset($tnidli);
				while (list($k) = each($tnidli))
					if (! isset($tnokli[$k]))
						unset($tnlurl[$k]);
			}
			unset($tnidli, $tnlist);
			$chn = $nlamail;
			if (isset($tnokli))
				$chn .= '^||~'.implode('^|~', $tnokli).'^||~'.implode('^|~', $tnlurl);

			//----- critères supplémentaires
			$ncritr = '';
/*			if ($datnaiss1 || $datnaiss2) {
				if (! $datnaiss1) $datnaiss1 = '1000-01-01';
				if (! $datnaiss2) $datnaiss2 = '3000-12-31';
				$ncritr = addslashes(" AND (datnaiss>='$datnaiss1' AND datnaiss<='$datnaiss2' OR datnaiss='0000-00-00')");
			} */
			$k = date('Y-m-d H:i');
			$sql = "INSERT INTO cmsnlmsg SET quoi='envoi',modl=$modl,nmulti='$k',texte='$chn',html='$ncritr'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$nbrenv = 0;
			$oper = 'auto';
		}
	}

	/*************************************
	****** Poursuivre ou annuler ? *******
	*************************************/
	if (! $oper && isset($cmd)) {
		if ($cmd == 'suivr') {
			//----- récupérer les destinataires
			$sql = 'SELECT modl,texte,html FROM cmsnlmsg WHERE quoi=\'envoi\'';
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
				$modl = $data['modl'];

				$chn = explode('^||~', $data['texte']);
				$nlamail = addslashes($chn[0]);
				if (isset($chn[1])) {
					$tnokli = explode('^|~', $chn[1]);
					$tnlurl = explode('^|~', addslashes($chn[2]));
				}
				unset($chn);
				$ncritr = addslashes($data['html']);

				//----- récupérer le message
				$lidelai = 30; $linblot = 10;
				obtenirmessage();
				$nbrenv = 0;
				if ($alerter)
					$diriger = 'letrMenu.php?tci='.$tci;
				else
					$oper = 'auto';
			}
			else
				$alerter = 'Il n\'y a aucun envoi à poursuivre';
		}
		else if ($cmd == 'arret')
			$oper = 'azero';
		unset($cmd);
	}

	/**********************
	****** Clôturer *******
	**********************/
	if ($oper == 'fini' || $oper == 'azero') {
		$sql = 'SELECT P.modl,P.sujet,E.texte FROM cmsnlmsg E,cmsnlmsg P WHERE E.quoi=\'envoi\' AND E.modl=P.modl AND P.quoi=\'prepar\'';
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
			$nlsuj = addslashes($data['sujet']);
			$modl = $data['modl'];

			if ($data['texte'] && $oper == 'fini') {
				//----- archiver le message
				$sql = 'SELECT nmulti,sujet,texte,html FROM cmsnlmsg WHERE quoi=\'prepar\' AND modl='.$modl;
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				$data = mysql_fetch_array($req, MYSQL_ASSOC);

				//--- le mois courant
				$data['html'] = '<hr /><b>----- La Une '.$moisctxt.' -----</b><hr />'.$data['html'];
				$sql = 'INSERT INTO cmsnlmsg SET quoi=\'-prepar\',modl='.$modl.',nmulti=\''.$data['nmulti'].'\',sujet=\''.addslashes($data['sujet']).'\',texte=\''.addslashes($data['texte']).'\',html=\''.addslashes($data['html']).'\'';
				$sql = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				$suj = mysql_insert_id();

				$sql = 'SELECT nmulti,sujet,texte,html FROM cmsnlmsg WHERE quoi=\'envoi\' AND modl='.$modl;
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				$data = mysql_fetch_array($req, MYSQL_ASSOC);

				$chn = explode('^||~', $data['texte']);
				$chn = isset($chn[1]) ? addslashes($chn[1]) : '@test';
				$sql = 'INSERT INTO cmsnlmsg SET quoi=\'-envoi\',modl='.$modl.',nmulti=\''.$data['nmulti'].'\',sujet=\''.$suj.'\',texte=\''.$chn.'\',html=\''.addslashes($data['html']).'\'';
				$sql = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

				//----- archiver les fichiers
				$sql = "SELECT nlid,quoi,nmulti,sujet,texte,html FROM cmsnlmsg WHERE (quoi='image' OR quoi='attach') AND modl=$modl AND (nmulti&4)<>0";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

				while ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
					$nlid = $data['nlid'];
					$sql = 'UPDATE cmsnlmsg SET quoi=\'-'.$data['quoi'].'\',sujet=\''.$suj.'\' WHERE nlid='.$nlid;
					$sql = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					$sql = 'INSERT INTO cmsnlmsg SET quoi=\''.$data['quoi'].'\',modl='.$modl.',nmulti=\''.$data['nmulti'].'\',html=\''.addslashes($data['html']).'\'';
					$sql = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
					$k = mysql_insert_id();

					$g = addslashes($data['texte']);
					$chn = str_replace('_'.$nlid.'.', '_'.$k.'.', $g);
					if ($chn == $g)
						$chn = preg_replace('/_\d*\.([^\.]*)$/', '_'.$k.'.\\1', $g);

					$sql = "UPDATE cmsnlmsg SET texte='$chn' WHERE nlid=$k";
					$sql = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					if ($data['quoi'] != 'attach') {
						$sql = "UPDATE cmsnlmsg SET html=REPLACE(REPLACE(REPLACE(html,'/nmedia/$g\"','/nmedia/$chn\"'),'id=\"dans$nlid\"','id=\"dans$k\"'),'id=\"hors$nlid\"','id=\"hors$k\"') WHERE quoi='prepar' AND modl=$modl";
						$sql = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
					}
					if (! @copy('../nmedia/'.$g, '../nmedia/'.$chn))
						$alerter .= '\nMais l\'archivage du fichier '.$g.' a échoué';
				}
			}
			//----- initialiser
			$sql = 'DELETE FROM cmsnlmsg WHERE quoi=\'envoi\'';
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
		}
		//----- Nettoyer
		$sql = 'UPDATE cmsnletter SET lenv=\'\' WHERE lenv=\'Y\'';
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($oper == 'fini')
			$alerter = 'L\'envoi de la newsletter est réussi ('.$nbrenv.' envoi'.($nbrenv > 1 ? 's)' : ')').$alerter;
		else
			$alerter = 'Le processus d\'envoi de newsletter est réinitialisé'.$alerter;
		$diriger = 'letrMenu.php?tci='.$tci;
		$oper = 'stop';
	}

	/********************************
	****** Obtenir le contenu *******
	********************************/
	if (! $oper) {
		$sql = 'SELECT P.sujet,P.modl FROM cmsnlmsg E,cmsnlmsg P WHERE E.quoi=\'envoi\' AND E.modl=P.modl AND P.quoi=\'prepar\'';
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) { //--- Poursuivre ou annuler ?
			$nlsuj = addslashes($data['sujet']);

			$alerter = 'Il y a déjà un envoi de newsletter en cours';
			if ($data['modl'] != 3 || $modl != 3)
				$alerter .= '\nAttention, il faut poursuivre autrement et avec le bon modèle';
			$oper = 'cours';
		}
		else {
			$lidelai = 30; $linblot = 10;
			$nbrenv = 0; //--- nombre de fichiers attachés
			$volume = 0;
			obtenirmessage();
			if ($alerter)
				$diriger = 'letrMenu.php?tci='.$tci;

			//----- obtenir les listes de diffusion
			$sql = 'SELECT ladrm,lletr,lcode FROM cmsnletter WHERE letat=\'0\''; //--- AND lletr=\'DPts\'
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			for ($k = 0; $data = mysql_fetch_array($req, MYSQL_ASSOC); $k++) {
				$tnidli[$k] = $data['lletr'];
				$tnlist[$k] = addslashes($data['lcode']);
				$tnlurl[$k] = addslashes($data['ladrm']);
			}
			$nlamail = $adrdusite;
//--- prov	$nlamail = 'philippe@vertige.org';

			if (! $alerter && $modl != 3)
				$alerter = 'Attention, il faut envoyer autrement la newsletter avec ce modèle';
		}
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',$oper == 'auto' ? $nbrenv.($nbrenv > 1 ? ' envois - ' : ' envoi - ') : '',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />
<script type="text/javascript">
<!--
function envoyer() {
	obj = document.getElementById("iofo");
	obj.oper.value = "envoi";
	obj.submit();
}
var vlidelai = 30;
function rebcompter() {
	vlidelai--;
	obj = document.getElementById("irebours");
	if (obj)
		obj.innerHTML = vlidelai < 0 ? -vlidelai : vlidelai;
	if (vlidelai == 0) {
		obj = document.getElementById("iofo");
		obj.submit();
	}
}
function interrompre() {
	if (confirm("Voulez-vous vraiment interrompre l'envoi de la newsletter ?"))
		window.location.href = "index.php";
}
/* function calendate(cal) { //--- pour datnaiss
	window.open('calendate.html?cal='+cal, 'wcal', 'left=200,top=200,width=300,height=190,toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=1');
	return false;
} */
//-->
</script>
</head>

<body onload="pagechargee()">
<div class="cmsdivtab">
<table cellspacing="0" cellpadding="0" class="cmstabtab">
<?
	echo '<tr><td class="cmsentete"><img src="matos/admlogo.gif" class="cmslogo" alt="" /><span class="cmstetitr">',ADMINENTETE,'</span></td></tr>',"\n",'<tr><td class="cmstabbox">',"\n";

	if ($oper != 'auto')
		echo '<div class="divcentre"><a href="letrMenu.php?tci=',$tci,'"><b>Aller au menu</b></a></div>',"\n";
	echo '<div class="divgauche"><img src="matos/puce.gif" alt="" /> Envoyer une newsletter DLP - La Une<a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

	if ($oper == 'stop')
		echo '<div class="divindent" style="margin-bottom:80px;">Sujet : <b>',htmlspecialchars(stripslashes($nlsuj)),'</b><br /><br />Le traitement est terminé.</div>',"\n";
	else if ($oper == 'cours') {
		echo '<div class="divindent">Sujet : <b>',htmlspecialchars(stripslashes($nlsuj)),'</b><br /><br />L\'envoi de la dernière newsletter a été interrompu.<br /><br />Voulez-vous poursuivre ou arrêter définitivement cet envoi ?<br /><br />Avant de choisir de poursuivre, vous pouvez <a href="letrListe.php?tci=',$tci,'">modifier le délai entre 2 lots et le nombre d\'envois par lot</a>.</div>',"\n";
		echo '<div class="divcentre"><br /><input type="button" class="bouton" onclick="window.location.href=\'letrEnvUne.php?cmd=suivr&amp;tci=',$tci,'\';" value="Poursuivre" /> &nbsp; &nbsp; &nbsp; &nbsp; <input type="button" class="bouton" onclick="window.location.href=\'letrEnvUne.php?cmd=arret&amp;tci=',$tci,'\';" value="Arrêter" /> &nbsp; &nbsp; &nbsp; &nbsp; <input type="button" class="bouton" onclick="window.location.href=\'letrMenu.php?tci=',$tci,'\';" value="Retour" /></div>',"\n";
	}
	else {
		echo '<form id="iofo" action="letrEnvUne.php?tci=',$tci,'" method="post">',"\n";

		if ($oper == 'auto') {
			echo '<div class="divindent">Sujet : <b>',htmlspecialchars(stripslashes($nlsuj)),'</b><br /><br />Le traitement est en cours, veuillez patienter svp.<br /><br />Il faut laisser cette fenêtre ouverte.<br /><br />',"\n";
			echo 'Cette page se relance automatiquement toutes les ',$lidelai,' secondes. Et ',$linblot,' emails sont envoyés par lot.<br /><br />',"\n",'La newsletter a déjà été envoyée <b>',$nbrenv,'</b> fois.</div>',"\n";
			echo '<div id="irebours" style="width:200px;text-align:right;margin:16px 0 0 0;font-size:large;">',$nbrenv ? $lidelai : 1,'</div>',"\n";
			echo '<div class="divcentre"><input type="button" class="bouton" onclick="interrompre()" value="Interrompre" /></div>',"\n";

			echo '<input name="nlamail" type="hidden" value="',htmlspecialchars(stripslashes($nlamail)),'" />',"\n";
			echo '<input name="ncritr" type="hidden" value="',htmlspecialchars(stripslashes($ncritr)),'" />',"\n";

			if (isset($tnokli)) {
				reset($tnokli);
				while (list($k, $g) = each($tnokli))
					echo '<input name="tnokli[',$k,']" type="hidden" value="',$g,'" /><input name="tnlurl[',$k,']" type="hidden" value="',htmlspecialchars(stripslashes($tnlurl[$k])),'" />',"\n";
			}
		}
		else {
			echo '<table border="0" align="center" width="94%" cellpadding="4" cellspacing="0">',"\n",'<tr><td width="200">Sujet</td><td><b>',htmlspecialchars(stripslashes($nlsuj)),'</b></td></tr>',"\n";

			$k = $nbrenv > 1 ? 's' : '';
			if ($volume >= 1048576)
				$g = number_format($volume / 1048576, 2, '.', '').' Mo';
			else if ($volume >= 1024)
				$g = number_format($volume / 1024, 2, '.', '').' Ko';
			else
				$g = $volume.' octets';
			echo '<tr><td>Nombre de fichier',$k,' attaché',$k,'</td><td>',$nbrenv,'</td></tr>',"\n",'<tr><td style="vertical-align:top;">Volume total</td><td style="padding-bottom:24px;">+/- ',$g,'<input name="volume" type="hidden" value="',$volume,'" /></td></tr>',"\n";
			echo '<tr><td>Destinataires</td>';
			if (isset($tnidli)) {
				reset($tnidli);
				while (list($k) = each($tnidli))
					echo $k ? '<tr><td></td><td>' : '<td>','<input name="tnokli[',$k,']" id="id',$k,'okli" type="checkbox" class="cocher" value="',$tnidli[$k],isset($tnokli[$k]) ? '" checked="checked" />' : '" />',' <label for="id',$k,'okli">',stripslashes($tnlist[$k]),'</label><input name="tnidli[',$k,']" type="hidden" value="',$tnidli[$k],'" /><input name="tnlist[',$k,']" type="hidden" value="',htmlspecialchars(stripslashes($tnlist[$k])),'" /><input name="tnlurl[',$k,']" type="hidden" value="',htmlspecialchars(stripslashes($tnlurl[$k])),'" /></td></tr>',"\n";
			}
			else echo '<td></td></tr>',"\n";

			echo '<tr><td>Adresse(s) email</td>',"\n",'<td><input name="nlamail" type="text" class="saisie" value="',htmlspecialchars(stripslashes($nlamail)),'" size="78" /></td></tr>',"\n";

			//----- critères supplémentaires
/*			echo '<tr><td>Critères supplémentaires</td>',"\n",'<td>Date de naissance entre <input type="text" name="datnaiss1" id="i1datnaiss" size="15" value="',isset($datnaiss1) ? htmlspecialchars(stripslashes($datnaiss1)) : '','" class="saisie" />&nbsp;<a href="#calendrier" onclick="return calendate(\'i1datnaiss\')"><img src="matos/calendr.gif" class="imgcal" alt="" title="calendrier" /></a> et <input type="text" name="datnaiss2" id="i2datnaiss" size="15" value="',isset($datnaiss2) ? htmlspecialchars(stripslashes($datnaiss2)) : '','" class="saisie" />&nbsp;<a href="#calendrier" onclick="return calendate(\'i2datnaiss\')"><img src="matos/calendr.gif" class="imgcal" alt="" title="calendrier" /></a></td></tr>',"\n"; */
			echo '</table>',"\n",'<div class="divcentre"><input type="button" class="bouton" onclick="envoyer()" value="Envoyer" /></div>',"\n";
		}
		echo '<input name="nlsuj" type="hidden" value="',htmlspecialchars(stripslashes($nlsuj)),'" />',"\n";
		echo '<input name="nltext" type="hidden" value="',htmlspecialchars(stripslashes($nltext)),'" />',"\n";
		echo '<input name="nlhtml" type="hidden" value="',htmlspecialchars(stripslashes($nlhtml)),'" />',"\n";

		if (isset($tnfich)) {
			reset($tnfich);
			while (list($k, $g) = each($tnfich))
				echo '<input name="tnfich[',$k,']" type="hidden" value="',stripslashes($g),'" />',"\n";
		}
		if (isset($tficid)) {
			reset($tficid);
			while (list($k, $g) = each($tficid))
				echo '<input name="tficid[',$k,']" type="hidden" value="',stripslashes($g),'" /><input name="tfmcid[',$k,']" type="hidden" value="',stripslashes($tfmcid[$k]),'" />',"\n";
		}
		echo '<input name="lidelai" type="hidden" value="',$lidelai,'" /><input name="linblot" type="hidden" value="',$linblot,'" /><input name="nbrenv" type="hidden" value="',$nbrenv,'" /><input name="nlang" type="hidden" value="',$nlang,'" />',"\n";
		echo '<input name="modl" type="hidden" value="',$modl,'" /><input name="oper" type="hidden" value="',$oper,'" /></form>',"\n";
	}
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

	if ($oper == 'auto')
		echo 'function pagechargee() { vlidelai = ',$nbrenv ? $lidelai : 1,'; window.setInterval("rebcompter()", 1000); }',"\n";
	else {
		echo $alerter ? 'function pagechargee() { alert("'.$alerter.'"); ' : 'function pagechargee() { ';
		echo $diriger ? 'window.location.href="'.$diriger.'"; }' : '}',"\n";
	}
?>
//-->
</script>
</body>
</html>
