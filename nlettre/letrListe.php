<? /*
	letat =
		0 : liste de diffusion
		4 : inscrit mais pas encore confirmé
		5 : inscrit et confirmé
	(cf. publiq/inletter.php)

	critères supplémentaires : ajouter par exemple "datnaiss" dans la table DB "cmsnletter"
*/
	if (isset($_GET['exportadr'])) {
		//===== Exporter des adresses =====
		require('conf.php');

		$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
		if (! $db_link) {
			echo 'Connexion impossible à la base de données ',$sql_bdd,' sur le serveur ',$sql_server;
			exit;
		}
		mysql_select_db($sql_bdd, $db_link);

		$g = false;
		if (isset($lletr) && isset($adrord)) {
			//--- Limiter l'accès (admEntete.php)
			session_start();
			$k = isset($_SESSION['cnpromen']) ? $_SESSION['cnpromen'] : '';

			if (is_numeric($k) && strlen($k) == 16 && isset($tci) && is_numeric($tci)) {
				$chn = $_SERVER['REMOTE_ADDR'];

				$sql = "SELECT acces,temps FROM c_membres WHERE protect='$k' AND adrip='$chn' AND temps='$tci'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				$data = mysql_fetch_array($req, MYSQL_ASSOC);

				if ($data && ($data['acces'] & 4096) && time() < $data['temps'] + 3600)
					$g = true;
			}
		}
		if (! $g) {
			mysql_close($db_link);
			header('Location:index.php?conm=i');
			exit;
		}
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Content-Disposition: attachment; filename="liste_'.$lletr.'.txt";');
		header('Content-Type: text/plain; charset="ISO-8859-1";');
		header('Content-Transfer-Encoding: binary');
		header("Expires: 0");
		header('Pragma: no-cache');

		$sql = "SELECT ladrm FROM cmsnletter WHERE lletr='$lletr' AND letat='5' ORDER BY ".($adrord ? 'ladrm' : 'lcode DESC');
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		echo '----- Liste ',$lletr,' - ',date('Y-m-d H:i:s'),' -----';
		while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
			echo "\r\n",$data['ladrm'];
		echo "\r\n",'----- fin -----';

		mysql_close($db_link);
		exit;
	}

	require('admEntete.php');

	//--- rendre les paramètres valides
	if (! isset($oper))
		$oper = '';
	$pgmnoadr = $oper != 'deladr' && $oper != 'adr';

	if (! ($protectacces & 4096)) {
		//--- accès limité : rediriger
		mysql_close($db_link);
		header('Location:letrMenu.php?tci='.$tci);
		exit;
	}

	if (! isset($lletr))
		$lletr = isset($_SESSION['idnletter']) ? $_SESSION['idnletter'] : 'X';
	else if ($lletr && $pgmnoadr)
		$_SESSION['idnletter'] = $lletr;

	if (! $pgmnoadr) {
		$maxpage = 50;
		$ofs = isset($ofs) && is_numeric($ofs) ? (int) $ofs : 0;
		if (! isset($adrord) || $adrord != 1)
			$adrord = 0;
	}

	//--- initialiser le message
	$alerter = '';

	/********************************
	****** Supprimer la liste *******
	********************************/
	if ($oper == 'enreg' && ! $linom  && $lletr) {
		$sql = "SELECT lletr FROM cmsnletter WHERE lletr='$lletr' AND letat<>'4'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if (mysql_num_rows($req) == 1) {
			$k = time() - 604800;
			$sql = "DELETE FROM cmsnletter WHERE lletr='$lletr' OR letat='4' AND lcode<'$k'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$alerter = 'La suppression de la liste est réussie';
			$lletr = 'X';
			$_SESSION['idnletter'] = 'X';
		}
		else
			$alerter = 'Il est impossible de supprimer une liste qui possède des adresses email';
		$oper = '';
	}

	/*********************************
	****** Enregistrer la liste ******
	*********************************/
	if ($oper == 'enreg') {
		if (! $lipage)
			$alerter = 'Il faut entrer l\'adresse de la page contenant le formulaire d\'inscription';
		else if (substr($lipage, 0, 7) != 'http://')
			$alerter = 'Il faut que l\'adresse de la page contenant le formulaire d\'inscription commence par \'http://\'';
		if (! $lletr)
			if (! preg_match('/^[a-zA-Z][a-zA-Z0-9]{3}$/', $nouvletr))
				$alerter = 'L\'identifiant n\'est pas valide (1 lettre puis 3 lettres ou chiffres)';
			else {
				$sql = "SELECT lletr FROM cmsnletter WHERE lletr='$nouvletr' AND letat='0'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

				if (mysql_num_rows($req) || $nouvletr == 'nouv')
					$alerter = 'Il faut donner un autre identifiant, celui-ci est déjà utilisé';
			}
		if (! $linom)
			$alerter = 'Il faut remplir le nom de la liste';
		$linom = str_replace('@', '_', $linom);

		if (! $alerter)
			if ($lletr) {
				$sql = "UPDATE cmsnletter SET ladrm='$lipage',lcode='$linom' WHERE lletr='$lletr' AND letat='0'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
			}
			else {
				$sql = "INSERT INTO cmsnletter SET ladrm='$lipage',lletr='$nouvletr',letat='0',lcode='$linom'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				$lletr = $nouvletr;
				unset($nouvletr);
			}

		if (! $alerter && ($lidelai != $memdelai || $linblot != $memnblot)) //--- modifier le délai et le nombre/lot
			if (is_numeric($lidelai) && $lidelai >= 1)
				if (is_numeric($linblot) && $linblot >= 1) {
					$lidelai = (int) $lidelai;
					$linblot = (int) $linblot;
					$sql = "UPDATE cmsnlmsg SET nmulti='$lidelai',sujet='$linblot' WHERE quoi='modele'";
					$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					if (mysql_affected_rows())
						{ $memdelai = $lidelai; $memnblot = $linblot; }
					else
						$alerter = 'La modification du délai et du nombre/lot a échoué';
				}
				else
					$alerter = 'Le nombre d\'envois par lot n\'est pas valide, il faut entrer un nombre >= 1';
			else
				$alerter = 'Le délai entre 2 lots d\'envois n\'est pas valide, il faut entrer un nombre >= 1';
	}

	/************************************
	****** Supprimer des adresses *******
	************************************/
	if ($oper == 'deladr' && isset($tad)) {
		$k = time() - 604800;
		$sql = "DELETE FROM cmsnletter WHERE lletr='$lletr' AND letat='5' AND ladrm IN ('".implode('\',\'', $tad)."') OR letat='4' AND lcode<'$k'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$alerter = 'Les adresses sélectionnées ont été supprimées';
	}

	/**********************************************
	****** Ajouter / supprimer des adresses *******
	**********************************************/
	function verifich() {
		global $fich, $alerter;

		if ($fich = @fopen($fich, 'rb')) {
			$k = 0;
			while ($k < 10 && ! feof($fich))
				if ($chn = trim(fgets($fich, 1024)))
					if (! preg_match('/^\S+@\S+\.\S+$/', $chn) || strpos($chn,'\'')!==false || strpos($chn,'"')!==false) {
						$alerter .= '\n'.str_replace('"', '\"', $chn);
						$k++;
					}
			if ($k)
				$alerter = 'L\'opération a échoué\nCertaines adresses email ne sont pas valides:'.$alerter.'\n...';
			else
				fseek($fich, 0);
		}
		else
			$alerter = 'Il est impossible d\'ouvrir le fichier reçu';
	}
	if ($oper == 'fajout') {
		if ($fajout['name'] && ! $fajout['error'] && $fajout['size']) {
			$fich = $fajout['tmp_name'];
			verifich();
			if (! $alerter) {
				$tp = time();
				$k = 0; $g = 0;
				while (! feof($fich))
					if ($chn = trim(fgets($fich, 1024))) {
						$sql = "SELECT letat FROM cmsnletter WHERE ladrm='$chn' AND lletr='$lletr' AND letat<>'0'";
						$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

						if ($data = mysql_fetch_array($req, MYSQL_ASSOC))
							if ($data['letat'] != '5') {
								$sql = "UPDATE cmsnletter SET letat='5' WHERE ladrm='$chn' AND lletr='$lletr' AND letat<>'0'";
								$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
								$k++;
							}
							else
								$g++;
						else {
							$sql = "INSERT INTO cmsnletter SET ladrm='$chn',lletr='$lletr',letat='5',lcode='$tp'";
							$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
							$k++; $tp++;
						}
					}
				$alerter = 'L\'opération est réussie\n'.$k.' adresses ont été ajoutées à la liste, '.$g.' adresses y appartenaient déjà';
			}
			fclose($fich);
		}
		$oper = '';
	}
	if ($oper == 'fsuppr') {
		if ($fsuppr['name'] && ! $fsuppr['error'] && $fsuppr['size']) {
			$fich = $fsuppr['tmp_name'];
			verifich();
			if (! $alerter) {
				$k = 0; $g = 0;
				while (! feof($fich))
					if ($chn = trim(fgets($fich, 1024))) {
						$sql = "DELETE FROM cmsnletter WHERE ladrm='$chn' AND lletr='$lletr' AND letat<>'0'";
						$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

						if (mysql_affected_rows()) $k++; else $g++;
					}
				$alerter = 'L\'opération est réussie\n'.$k.' adresses ont été supprimées de la liste, '.$g.' adresses n\'ont pas été trouvées';

				$k = time() - 604800;
				$sql = "DELETE FROM cmsnletter WHERE letat='4' AND lcode<'$k'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
			}
			fclose($fich);
		}
		$oper = '';
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />
<script type="text/javascript">
<!--
	function enregistrer() {
		ofo = document.getElementById("iofo");
		ofo.oper.value = "enreg";
		ofo.submit();
	}
	function affadr(k, op) {
		ofo = document.getElementById("iofo");
		if (op == 2)
			ofo.adrord.value ^= 1;
		ofo.oper.value = op == 0 ? "deladr" : "adr";
		if (typeof(ofo.ofs) != "undefined")
			ofo.ofs.value = k;
		ofo.submit();
		return false;
	}
	function changer() {
		ofo = document.getElementById("iofo");
		if (! ofo.seletr.length)
			return;
		ofo.lletr.value = ofo.seletr.options[ofo.seletr.selectedIndex].value;
		ofo.submit();
	}
	function ajouter() {
		ofo = document.getElementById("iofo");
		ofo.lletr.value = "";
		ofo.submit();
		return false;
	}
	function fichajout() {
		of2 = document.getElementById("iof2");
		if (of2.fajout.value != "" && confirm("Voulez-vous vraiment ajouter ces adresses à cette liste ?"))
		{	of2.oper.value = "fajout"; of2.submit(); }
	}
	function fichexport() {
		of2 = document.getElementById("iof2");
		of2.oper.value = "fexport";
		of2.target = "_blank";
		of2.submit();
	}
	function fichsuppr() {
		of2 = document.getElementById("iof2");
		if (of2.fsuppr.value != "" && confirm("Voulez-vous vraiment retirer ces adresses de cette liste ?"))
		{	of2.oper.value = "fsuppr"; of2.submit(); }
	}
	function initialiserlocal(nro, opt, nom) {
		obj = document.getElementById("loc"+nro);
		k = 0;
		if (opt)
			obj.options[k++] = new Option(opt, "");
		nro = vatimcelilink.length;
		for (g = 0; g < nro; g++)
			obj.options[k++] = new Option(vatimcelilink[g]);
		if (nom != "")
			for (k--; k >= 0; k--)
				if (obj.options[k].text == nom)
					obj.options[k].selected = true;
	}
	function verifier() {
		ofo = document.getElementById("iofo");
		if (ofo.lipage.value != "")
			document.getElementById("veriflipage").href = ofo.lipage.value;
		return true;
	}
//-->
</script>
</head>

<body onload="pagechargee()">
<div class="cmsdivtab">
<table cellspacing="0" cellpadding="0" class="cmstabtab">
<?
	echo '<tr><td class="cmsentete"><img src="matos/admlogo.gif" class="cmslogo" alt="" /><span class="cmstetitr">',ADMINENTETE,'</span></td></tr>',"\n",'<tr><td class="cmstabbox">',"\n";

	if ($pgmnoadr) {
		if (! $oper) { //----- délai, nombre/lot
			$sql = 'SELECT nmulti,sujet FROM cmsnlmsg WHERE quoi=\'modele\'';
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
			$data = mysql_fetch_array($req, MYSQL_ASSOC);
			$lidelai = $data ? (int) $data['nmulti'] : 30;
			$memdelai = $lidelai;
			$linblot = $data ? (int) $data['sujet'] : 10;
			$memnblot = $linblot;
		}
		//----- formulaire liste
		echo '<div class="divcentre"><a href="letrMenu.php?tci=',$tci,'"><b>Aller au menu</b></a></div>',"\n";

		echo '<form id="iofo" action="letrListe.php?tci=',$tci,'" method="post">',"\n",'<div class="divgauche"><img src="matos/puce.gif" alt="" /> ';

		$sql = "SELECT ladrm,lletr,lcode FROM cmsnletter WHERE letat='0' ORDER BY lletr";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$chn = ''; $k = $lletr; $lletr = '';

		while ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
			$chn .= '<option value="'.$data['lletr'];
			if ($data['lletr'] == $k || $k == 'X') {
				$chn .= '" selected="selected">';
				$lletr = $data['lletr'];
				if (! $oper) {
					$linom = addslashes($data['lcode']);
					$lipage = $data['ladrm'];
				}
				$k = '';
			}
			else
				$chn .= '">';
			$chn .= htmlspecialchars($data['lcode']).'</option>';
		}
		if ($lletr)
			echo 'Modifier la liste de diffusion <select name="seletr" class="liste" onchange="changer()">'.$chn;
		else {
			echo 'Ajouter une liste de diffusion <select name="seletr" class="liste" onchange="changer()"><option value="" selected="selected">nouvelle</option>'.$chn;
			if (! $oper) {
				$nouvletr = 'nouv';
				$linom = '';
				$chn = $_SERVER['PHP_SELF']; $k = strpos($chn, '/nlettre/');
				$lipage = 'http://'.$_SERVER['SERVER_NAME'].($k !== false ? substr($chn, 0, $k).'/' : '/');
			}
		}
		echo '</select><a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

		echo '<table border="0" align="center" width="90%" cellpadding="4" cellspacing="0">',"\n";

		echo '<tr><td width="35%">Nom de la liste</td><td><input name="linom" type="text" class="saisie" size="50" value="',htmlspecialchars(stripslashes($linom)),'" /></td></tr>',"\n";
		if ($lletr)
			echo '<tr><td>Identifiant de la liste</td><td>',$lletr,'</td></tr>',"\n";
		else
			echo '<tr><td>Identifiant de la liste</td><td><input name="nouvletr" type="text" class="saisie" size="10" maxlength="4" value="',htmlspecialchars(stripslashes($nouvletr)),'" /></td></tr>',"\n";
		echo '<tr><td colspan="2">Adresse URL de la page contenant le formulaire d\'inscription<br /><input name="lipage" type="text" class="saisie" size="85" value="',htmlspecialchars(stripslashes($lipage)),'" style="margin-top:4px;" /> <a href="#" id="veriflipage" target="_blank" onclick="return verifier();">vérifier</a></td></tr>',"\n";
		echo '<tr><td>Délai entre 2 lots d\'envois</td><td><input name="lidelai" type="text" class="saisie" size="10" value="',htmlspecialchars(stripslashes($lidelai)),'" /> secondes<input name="memdelai" type="hidden" value="',$memdelai,'" /></td></tr>',"\n";
		echo '<tr><td>Nombre d\'envois par lot</td><td><input name="linblot" type="text" class="saisie" size="10" value="',htmlspecialchars(stripslashes($linblot)),'" /><input name="memnblot" type="hidden" value="',$memnblot,'" /></td></tr>',"\n";

		echo '<tr><td colspan="2" style="text-align:center;"><input name="lletr" type="hidden" value="',$lletr,'" /><input name="oper" type="hidden" value="" /><input type="button" value="Enregistrer" class="bouton" onclick="enregistrer()" /></td></tr>',"\n";

		if ($lletr) {
			$sql = "SELECT COUNT(*) AS nbr FROM cmsnletter WHERE lletr='$lletr' AND letat='5'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
			$data = mysql_fetch_array($req, MYSQL_ASSOC);
			echo '<tr><td>Nombre d\'inscrits à la liste</td><td><b>',$data ? $data['nbr'] : '0','</b></td></tr>',"\n",'</table>',"\n";

			echo '<div class="divcentre"><a href="#voir" onclick="return affadr(0, 1)"><b>Voir et supprimer des adresses email de la liste</b></a><input name="mlinom" type="hidden" value="',htmlspecialchars(stripslashes($linom)),'" /></div>',"\n";
			echo '<div class="divcentre">Ajouter une liste de diffusion <a href="#ajouter" onclick="return ajouter()"><img src="matos/ajout.gif" class="bajout" alt="" title="ajouter" onmouseover="this.src=\'matos/ajover.gif\'" onmouseout="this.src=\'matos/ajout.gif\'" /></a></div>',"\n",'</form>',"\n";
		}
		else
			echo '</table></form>',"\n";
	}
	else {
		//----- formulaire adresses
		echo '<div class="divcentre"><a href="letrListe.php?tci=',$tci,'"><b>Revenir à la liste de diffusion</b></a></div>',"\n";

		echo '<div class="divgauche" style="margin-bottom:0px"><img src="matos/puce.gif" alt="" /> Supprimer des adresses email de la liste <b>',stripslashes($mlinom),'</b><a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

		echo '<form id="iofo" action="letrListe.php?tci=',$tci,'" method="post">',"\n",'<input name="ofs" type="hidden" value="0" /><input name="mlinom" type="hidden" value="',htmlspecialchars(stripslashes($mlinom)),'" />',"\n";

		echo '<div class="divcentre"><input name="adrord" type="hidden" value="',$adrord,'" />Par <a href="#ordre" onclick="return affadr(',$ofs,', 2)">ordre</a> ',$adrord ? 'alphabétique' : 'de date','</div>',"\n";

		if ($ofs < 0) {
			$sql = "SELECT COUNT(*) AS max FROM cmsnletter WHERE lletr='$lletr' AND letat='5'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			if ($data = mysql_fetch_array($req, MYSQL_ASSOC))
				$ofs = $data['max'] > $maxpage ? $data['max'] - $maxpage : 0;
		}
		$maxpage++;
		$sql = "SELECT ladrm FROM cmsnletter WHERE lletr='$lletr' AND letat='5' ORDER BY ".($adrord ? 'ladrm' : 'lcode DESC')." LIMIT $ofs,$maxpage";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
		$maxpage--;

		echo '<table border="0" align="center" width="90%" cellpadding="2" cellspacing="0">',"\n";
		for ($k = 0; ($data = mysql_fetch_array($req, MYSQL_ASSOC)) && $k < $maxpage; $k++) {
			echo $k & 1 ? '<td>' : '<tr><td>';
			echo '<input name="tad[]" type="checkbox" class="cocher" value="',htmlspecialchars($data['ladrm']),'" /> ',htmlspecialchars($data['ladrm']);
			echo $k & 1 ? '</td></tr>' : '</td>',"\n";
		}
		echo $k & 1 ? '<td></td></tr></table>' : '</table>',"\n",'<div class="divcentre">';
		if ($ofs)
			echo '<a href="#premiere" onclick="return affadr(0, 1)">première</a> &nbsp; <a href="#precedente" onclick="return affadr(',$ofs > $maxpage ? $ofs - $maxpage : 0,', 1)">précédente</a> &nbsp; ';
		else
			echo '<span class="inactif">première &nbsp; précédente &nbsp; </span>';
		echo 'page &nbsp; ';
		if ($data)
			echo '<a href="#suivante" onclick="return affadr(',$ofs + $maxpage,', 1)">suivante</a> &nbsp; <a href="#derniere" onclick="return affadr(-1, 1)">dernière</a>';
		else
			echo '<span class="inactif">suivante &nbsp; dernière</span>';
		echo '</div>',"\n",'<div class="divcentre"><input name="lletr" type="hidden" value="',$lletr,'" /><input name="oper" type="hidden" value="" /><input type="button" value="Supprimer la sélection" class="bouton" onclick="affadr(',$ofs,', 0)" /></div></form>',"\n";
	}
	if ($pgmnoadr && $lletr) {
		//----- formulaire fichiers
		echo '<form action="letrListe.php" method="get">',"\n",'<input name="exportadr" type="hidden" value="y" /><input name="lletr" type="hidden" value="',$lletr,'" />',"\n",'<div style="margin-left:34px;padding:3px;margin-top:40px">&#8226; Exporter les adresses email de la liste vers un fichier texte</div>',"\n";
		echo '<div style="margin-left:44px;padding:3px">Par ordre <input name="adrord" type="radio" class="cocher" value="0" checked="checked" /> de date / <input name="adrord" type="radio" class="cocher" value="1" /> alphabétique &nbsp; <input type="submit" value="Exporter" class="bouton" /></div>',"\n",'<input name="tci" type="hidden" value="',$tci,'" /></form>',"\n";

		echo '<form id="iof2" action="letrListe.php?tci=',$tci,'" method="post" enctype="multipart/form-data">',"\n",'<input name="lletr" type="hidden" value="',$lletr,'" /><input name="oper" type="hidden" value="" />',"\n";

		echo '<div style="margin-left:34px;padding:3px;margin-top:30px">&#8226; Ajouter à la liste les adresses email qui se trouvent dans le fichier texte</div>',"\n";
		echo '<div style="margin-left:44px;padding:3px"><input name="fajout" type="file" class="saisie" size="60" /> &nbsp; <input type="button" value="Ajouter" class="bouton" onclick="fichajout()" style="vertical-align:bottom" /></div>',"\n";

		echo '<div style="margin-left:34px;padding:3px;margin-top:30px">&#8226; Supprimer de la liste les adresses email qui se trouvent dans le fichier texte</div>',"\n";
		echo '<div style="margin-left:44px;padding:3px"><input name="fsuppr" type="file" class="saisie" size="60" /> &nbsp; <input type="button" value="Supprimer" class="bouton" onclick="fichsuppr()" style="vertical-align:bottom" /></div></form>',"\n";
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

	echo 'function pagechargee() { ',$alerter ? 'alert("'.$alerter.'"); }' : '}',"\n";
?>
//-->
</script>
</body>
</html>
