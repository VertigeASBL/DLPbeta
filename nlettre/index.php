<?
	require('conf.php');

	//--- rendre les paramètres valides
	if (! isset($conm)) $conm = '';

	if (! isset($cnlogin) || ! is_string($cnlogin)) $cnlogin = '';
	if (preg_match('/[^A-Za-z0-9àâçèéêëîïôùûü_ -]/', $cnlogin)) $cnlogin = '';

	if (! isset($cnpassw) || ! is_string($cnpassw)) $cnpassw = '';
	if (preg_match('/[^a-z0-9]/', $cnpassw)) $cnpassw = '';

	if (! isset($cnmix) || ! is_numeric($cnmix)) $cnmix = 0;

	//--- initialiser le message
	$alerter = '';

	if ($cnmix) {
		//--- Connexion à la DB
		$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
		if (! $db_link) {
			echo 'Connexion impossible à la base de données ',$sql_bdd,' sur le serveur ',$sql_server;
			exit;
		}
		mysql_select_db($sql_bdd, $db_link);
	}
	$maintenant = time();

//***** encoder selon RFC2047 ******
function encodeHeader($chn) {
	preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $chn, $tab);
	while (list(, $s1) = each($tab[1])) {
		$s2 = preg_replace('/([\x20\x80-\xFF])/e', '"=".strtoupper(dechex(ord("\1")))', $s1);
		$chn = str_replace($s1, '=?ISO-8859-1?Q?'.$s2.'?=', $chn);
	}
	return $chn;
}
//***** encoder quoted-printable ******
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
}

	/****************************************
	****** Limiter l'accès aux admins *******
	****************************************/
	if ($conm == 'y' && $maintenant < $cnmix + 300) {
		$k = substr(md5($_SERVER['SERVER_NAME']), 0, 16);
		$sql = "SELECT id_membre FROM c_membres WHERE pseudo='$cnlogin' AND MD5(CONCAT(DECODE(passw,'pw'),'$cnmix'))='$cnpassw' AND acces & 2048<>0 OR protect='$k'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if (mysql_num_rows($req) > 1) {
			//--- Créer un identifiant aléatoire
			for ($cnpassw = '', $k = 0; $k < 16; $k++)
				$cnpassw .= substr('0123456789', rand(0, 9), 1);
			$tci = $maintenant;
			$chn = $_SERVER['REMOTE_ADDR'];

			session_start();
			$_SESSION['cnpromen'] = $cnpassw;

			$sql = "UPDATE c_membres SET protect='$cnpassw',adrip='$chn',temps='$tci' WHERE pseudo='$cnlogin'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			//--- Rediriger vers une page protégée
			mysql_close($db_link);
			header('Location:letrMenu.php?tci='.$tci);
			exit;
		}
	}

	/************************************
	****** Changer le mot de passe ******
	************************************/
	if ($conm == '1' && $maintenant < $cnmix + 300) {
		if (! isset($motpas) || ! is_string($motpas)) $motpas = '';

		$sql = "SELECT id_membre,DECODE(passw,'pw') AS passe FROM c_membres WHERE MD5(CONCAT(DECODE(passw,'pw'),'$cnmix'))='$cnpassw' AND pseudo='$cnlogin'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
			$tabpw = explode(',', $motpas);
			$g = strlen($data['passe']); $g--;
			$tabpw[0] = (ord($data['passe']{$g}) ^ $tabpw[0]) & 15;
			$motpas = '';
			$g = 0; $k = 1;
			while ($k <= $tabpw[0] && isset($tabpw[$k])) {
				$motpas .= chr(ord($data['passe']{$g}) ^ $tabpw[$k]);
				$k++; $g++;
				if ($g >= strlen($data['passe']))
					$g = 0;
			}
			if (preg_match('/[^A-Za-z0-9]/', $motpas))
				$alerter = 'Le nouveau mot de passe contient des caractères non autorisés';
			if (strlen($motpas) < 6)
				$alerter = 'Le nouveau mot de passe n\'est pas valide';
			if ($tabpw[0] < 6)
				$alerter = 'Le nouveau mot de passe doit contenir 6 caractères au minimum';
			unset($tabpw);
		}
		else
			$alerter = 'Le nom d\'utilisateur ou l\'ancien mot de passe n\'est pas reconnu';

		if (! $alerter) {
			$k = $data['id_membre'];
			$sql = "UPDATE c_membres SET passw=ENCODE('$motpas','pw') WHERE id_membre=$k";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$alerter = 'Le changement de mot de passe est réussi';
			$conm = '';
		}
	}

	/************************************
	****** Envoyer le mot de passe ******
	************************************/
	if ($conm == '2' && $cnmix) {
		$sql = "SELECT admail,DECODE(passw,'pw') AS passe FROM c_membres WHERE pseudo='$cnlogin'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
			if ($data['admail']) {
				$chn = 'Bonjour.'."\n\n".'Votre mot de passe est oublié ?'."\n\n";
				$chn .= 'Pour pouvoir vous connecter à la gestion du site, vous aurez besoin des ces données:'."\n\n";
				$chn .= 'Nom d\'utilisateur: '.$cnlogin."\n".'Mot de passe: '.$data['passe']."\n\n";

				$k = strpos($_SERVER['PHP_SELF'], '/nlettre/');
				$chn .= 'Et l\'adresse pour vous connecter en tant qu\'administrateur:'."\n".'http://'.$_SERVER['SERVER_NAME'].($k !== false ? substr($_SERVER['PHP_SELF'], 0, $k).'/nlettre/' : '/nlettre/');

				$k = 'From: '.NOMDUSITE.' <'.ADRDUSITE.'>'."\n";
				$k .= 'Content-Type: text/plain; charset="ISO-8859-1"'."\n".'Content-Transfer-Encoding: quoted-printable'."\n";

				if (@mail_beta($data['admail'], encodeHeader(NOMDUSITE.' - gestion du site'), quotedPrintable($chn), $k, '-f '.ADRDUSITE))
					$alerter = 'Votre mot de passe a été envoyé à votre adresse email';
				else
					$alerter = 'L\'envoi de votre mot de passe par email a échoué';
			}
			else
				$alerter = 'Votre adresse email n\'a pas été trouvée';
			$conm = '';
		}
		else
			$alerter = 'Le nom d\'utilisateur n\'est pas reconnu';
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />

<script type="text/javascript">
<!--
function montrerchang() {
	if (obj = document.getElementById('id2chang')) obj.style.display = 'block';
	if (obj = document.getElementById("io1fo")) obj.cnlogin.focus();
	if (obj = document.getElementById('id1chang')) obj.style.display = 'none';
	return false;
}
function montreroubli() {
	if (obj = document.getElementById('id2oubli')) obj.style.display = 'block';
	if (obj = document.getElementById("io2fo")) obj.cnlogin.focus();
	if (obj = document.getElementById('id1oubli')) obj.style.display = 'none';
	return false;
}
function enregistrer() {
	ofo = document.getElementById("io1fo");
	st1 = ofo.cnmotp.value;
	st2 = ofo.cn2mot.value;
	if (! st1 || ! st2) {
		alert("Il faut remplir les mots de passe");
		return;
	}
	if (st2 != ofo.cn3mot.value) {
		alert("Il faut recopier le nouveau mot de passe sans différence");
		return;
	}
	if (st2.length > 15)
		st2 = st2.substr(0, 15);

	g = st1.length; g--;
	k = Math.floor(16 * Math.random()) << 4 | st2.length;
	chn = st1.charCodeAt(g) ^ k;

	g = 0; k = 0;
	while (k < st2.length) {
		chn += ","+(st1.charCodeAt(g) ^ st2.charCodeAt(k));
		k++; g++;
		if (g >= st1.length) g = 0;
	}
	st1 = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	while (k < 15) {
		chn += ","+(st1.charCodeAt(Math.floor(62 * Math.random())) ^ st1.charCodeAt(Math.floor(62 * Math.random())));
		k++;
	}
	ofo.cn2mot.value = "";
	ofo.cn3mot.value = "";
	ofo.motpas.value = chn;
	cnentrer(ofo);
}
//-->
</script>
<script type="text/javascript" src="connexion.js"></script>
<style type="text/css">
	.tablogin { border:0; }
	.tablogin td { border:0; padding:6px 30px 6px 30px; }
</style>
</head>

<body onload="pagechargee()">
<div class="cmsdivtab">
<table cellspacing="0" cellpadding="0" class="cmstabtab">
<?
	echo '<tr><td class="cmsentete"><img src="matos/admlogo.gif" class="cmslogo" alt="" /><span class="cmstetitr">',ADMINENTETE,'</span></td></tr>',"\n";
?>
	<tr>
		<td class="cmstabbox">
			<div class="divgauche"><img src="matos/puce.gif" alt="" />
				Se connecter à l'administration du site<a href="aide/conn.html" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a>
			</div>

			<form id="io0fo" action="index.php?conm=y" method="post">
			<table align="center" cellspacing="0" class="tablogin">
				<tr> 
					<td>Nom d'utilisateur</td>
					<td>
<?
	//----- se connecter
	echo '<input name="cnlogin" type="text" value="',$cnlogin,'" class="saisie" size="20" maxlength="20" onkeypress="cnpresser(this.form,event)" />',"\n";
	echo '<input name="cnpassw" type="hidden" value="" /><input name="cnmix" type="hidden" value="',$maintenant,'" />',"\n";
?>
					</td>
				</tr>
				<tr> 
					<td>Mot de passe</td>
					<td><input name="cnmotp" type="password" class="saisie" size="20" maxlength="15" onkeypress="cnpresser(this.form,event)" /></td>
				</tr>
			</table>
			<div class="divcentre"><input type="button" onclick="cnentrer(this.form)" value="Entrer" class="bouton" /></div>
			</form>

			<div id="id2chang" style="display:<? echo $conm == 1 ? 'block' : 'none'; ?>;">
				<div style="margin:30px 0 0 34px;">&#8226; Changer le mot de passe</div>
				<form id="io1fo" action="index.php?conm=1" method="post">
				<table align="center" cellspacing="0" class="tablogin">
					<tr>
						<td>Nom d'utilisateur</td>
						<td>
<?
	//----- changer le mot de passe
	echo '<input name="cnlogin" type="text" value="',$cnlogin,'" class="saisie" size="20" maxlength="20" />',"\n";
	echo '<input name="cnpassw" type="hidden" value="" /><input name="cnmix" type="hidden" value="',$maintenant,'" /><input name="motpas" type="hidden" value="" />',"\n";
?>
						</td>
					</tr>
					<tr>
						<td>Ancien mot de passe</td>
						<td><input name="cnmotp" type="password" class="saisie" size="20" maxlength="15" /></td>
					</tr>
					<tr>
						<td>Nouveau mot de passe</td>
						<td><input name="cn2mot" type="password" class="saisie" size="20" maxlength="15" value="" /></td>
					</tr>
					<tr>
						<td>Nouveau mot de passe encore</td>
						<td><input name="cn3mot" type="password" class="saisie" size="20" maxlength="15" value="" /></td>
					</tr>
				</table>
				<div class="divcentre"><input type="button" onclick="enregistrer()" value="Enregistrer" class="bouton" /></div>
				</form>
			</div>

			<div id="id2oubli" style="display:<? echo $conm == 2 ? 'block' : 'none'; ?>;">
				<div style="margin:30px 0 0 34px;">&#8226; Recevoir par email votre mot de passe oublié</div>
				<form id="io2fo" action="index.php?conm=2" method="post">
				<table align="center" cellspacing="0" class="tablogin">
					<tr>
						<td>Nom d'utilisateur</td>
						<td>
<?
	//----- mot de passe oublié
	echo '<input name="cnlogin" type="text" value="',$cnlogin,'" class="saisie" size="20" maxlength="20" /><input name="cnmix" type="hidden" value="1" />',"\n";
?>
						</td>
					</tr>
				</table>
				<div class="divcentre"><input type="submit" value="Envoyer" class="bouton" /></div>
				</form>
			</div>

			<br />
			<div id="id1chang" style="display:<? echo $conm == 1 ? 'none' : 'block'; ?>;" class="divcentre">
				<a href="#changer" onclick="return montrerchang();">Changer le mot de passe</a>
			</div>
			<div id="id1oubli" style="display:<? echo $conm == 2 ? 'none' : 'block'; ?>;" class="divcentre">
				<a href="#oubli" onclick="return montreroubli();">Mot de passe oublié ?</a>
			</div>
		</td>
	</tr>
	<tr><td style="text-align:right"><a href="http://www.vertige.org/" target="_blank" class="cmsareal">conception Vertige asbl</a> &nbsp;</td></tr>
</table>
</div>
<script type="text/javascript">
<!--
<?
	//--- Déconnexion de la DB
	if ($cnmix)
		mysql_close($db_link);

	switch ($conm) {
		case 'y': $alerter = 'Le nom d\'utilisateur ou le mot de passe n\'est pas reconnu'; break;
		case 't': $alerter = '60 minutes d\'inactivité, veuillez vous reconnecter svp'; break;
		case 's': $alerter = 'Votre session a été fermée, veuillez vous reconnecter svp'; break;
		case 'p': $alerter = 'Votre session est fermée, veuillez vous connecter svp'; break;
		case 'i': $alerter = 'Désolé, cette opération ne vous est pas autorisée'; break;
	}
	echo $alerter ? 'function pagechargee() { alert("'.$alerter.'"); ' : 'function pagechargee() { ';
	echo "\n",'window.name = "fadmin"; if (ofo = document.getElementById("io',$conm == '1' || $conm == '2' ? $conm : '0','fo")) ofo.cnlogin.focus(); }',"\n";
?>
//-->
</script>
</body>
</html>
