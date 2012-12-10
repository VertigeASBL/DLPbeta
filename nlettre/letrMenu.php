<? /*
	table DB "cmsnlmsg" :
	quoi =	"modele" : nmulti = délai pour envoi, texte = numéros et noms de modèles
			"modimg" : image de modèle à incorporer aux emails
			"prepar" : newsletter courante liée à un modèle
			"image" : image de newsletter (*)
			"attach" : fichier attaché (*)
				(*) : nmulti = bit 1-à-charger 2-incorporé 4-confirmé, texte = nom du fichier, html = volume et type MIME
			"envoi" : nmulti = date d'envoi, texte = listes de diffusion destinataires, html = critères supplémentaires
	archivage : préfixe "-"
		sujet = identifiant du "-prepar" correspondant càd de la newsletter parent archivée

	Clic sur "Menu" + "Recalculer les modèles".
	Attention, après leur reconnaissance, ne plus changer le nom des fichiers php dans "nmodele/".
	Il faut conserver "nmodele/defaut.php".
	Les fichiers images d'un modèle doivent se trouver dans "nmodele/".
	Si on veut les incorporer dans l'email, les images d'un modèle doivent avoir cette forme :
		<img ... id="dedans..." ... src=".../nmodele/..." ...
	Ce qui se trouve entre <style>...</style> dans un modèle est recopié automatiquement dans un fichier "nmodele/modeleN.css".
	Un modèle doit contenir strictement :
		"<? $chn = 'intro'; include('../nlettre/letrContenu.php'); ?>" juste après <body>
		"<? $chn = 'html'; include('../nlettre/letrContenu.php'); ?>" à la place du contenu
*/
	require('admEntete.php');

	if (! ($protectacces & 4096)) {
		//--- Limiter l'acces
		mysql_close($db_link);
		header('Location:letrMenu.php?tci='.$tci);
		exit;
	}

	//--- initialiser le message
	$alerter = '';

	if (isset($init) || ! isset($_SESSION['sessmodl'])) {
		$modl = 0; $tmodl = array();

		//----- obtenir les modèles de la DB
		$sql = "SELECT texte FROM cmsnlmsg WHERE quoi='modele'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$data = mysql_fetch_array($req, MYSQL_ASSOC);
		if (! $data) {
			$sql = "INSERT INTO cmsnlmsg SET quoi='modele',nmulti='25'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$data['texte'] = '';
		}
		$chn = explode('^|~', addslashes($data['texte']));
		reset($chn); $maxmodl = 0;
		while (list($k) = each($chn))
			if ($k & 1) {
				$tmodl[$chn[$k]] = isset($init) ? - $chn[$g] : $chn[$g];
				if ($maxmodl < $chn[$g])
					$maxmodl = $chn[$g];
			}
			else
				$g = $k;

		//----- obtenir les fichiers modèles
		if (isset($init) && $rep = @opendir('../nmodele')) {
			while (($g = readdir($rep)) !== false)
				if ($g != '.' && $g != '..' && substr($g, -4) == '.php' && $g != 'defaut.php') {
					$g = substr($g, 0, -4);
					if (isset($tmodl[$g]))
						$tmodl[$g] = - $tmodl[$g];
					else {
						$tmodl[$g] = ++$maxmodl;

						$sql = "INSERT INTO cmsnlmsg SET quoi='prepar',modl=$maxmodl,nmulti='fr',sujet='sujet',html='message'";
						$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
					}
					$chn = file_get_contents('../nmodele/'.$g.'.php');

			 		//----- recueillir les noms de classe du modèle
					if ($k = @fopen('../nmodele/modele'.$tmodl[$g].'.css', 'wb')) {
						flock($k, 2);
						@fwrite($k, '/*----- CSS du modèle "'.$g.'.php" : -----*/'."\n");
						if (preg_match(',<style[^>]*>(.*)</style>,Uims', $chn, $tab))
							{ @fwrite($k, trim($tab[1])."\n"); unset($tab); }
						flock($k, 3);
						fflush($k);
						fclose($k);
						if (! @chmod('../nmodele/modele'.$tmodl[$g].'.css', 0666))
							$alerter = 'Il est impossible de changer les permissions du fichier nmodele/modele'.$tmodl[$g].'.css';
					}
					else
						$alerter = 'Création impossible du fichier \'nmodele/modele'.$tmodl[$g].'.css\'';

			 		//----- recueillir les images du modèle
					if (preg_match_all('/<img[^<>]* id="dedans\w+"[^<>]* src="[^<">]*\/nmodele\/([^<">]*)"/', $chn, $tab)) {
						unset($tab[0]);
						while (list(, $chn) = each($tab[1])) {
							$sql = "SELECT nlid FROM cmsnlmsg WHERE quoi='modimg' AND modl=$tmodl[$g] AND texte='$chn'";
							$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

							if (! mysql_num_rows($req)) {
								$k = filesize('../nmodele/'.$chn).'^|~image';
								switch (strtolower(substr($chn, strrpos($chn, '.')))) {
									case '.png': $k .= '/png'; break;
									case '.jpg': $k .= '/jpeg'; break;
									case '.gif': $k .= '/gif'; break;
								}
								$sql = "INSERT INTO cmsnlmsg SET quoi='modimg',modl=$tmodl[$g],nmulti='6',texte='$chn',html='$k'";
								$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
							}
						}
						$sql = "DELETE FROM cmsnlmsg WHERE quoi='modimg' AND modl=$tmodl[$g] AND texte NOT IN ('".implode('\',\'', $tab[1]).'\')';
					}
					else
						$sql = "DELETE FROM cmsnlmsg WHERE quoi='modimg' AND modl=$tmodl[$g]";
					$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
				}
			closedir($rep);

			//----- comparer et actualiser les modèles
			ksort($tmodl);
			reset($tmodl); $chn = '';
			while (list($k) = each($tmodl))
				if ($tmodl[$k] > 0)
					$chn .= $tmodl[$k].'^|~'.addslashes($k).'^|~';
				else
					unset($tmodl[$k]);

			$sql = "UPDATE cmsnlmsg SET texte='$chn' WHERE quoi='modele'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			unset($maxmodl, $init, $rep, $tab);

			if (! $alerter)
				$alerter = 'Les modèles sont actualisés';
		}
		$tmodl = array_flip($tmodl);
		if (! $modl)
			{ reset($tmodl); $modl = key($tmodl); if (! $modl) $modl = 0; }

		$_SESSION['sessmodl'] = $modl;
		$_SESSION['sesstmodl'] = $tmodl;
	}
	else {
		$tmodl = $_SESSION['sesstmodl'];

		if (! isset($modl))
 			$modl = $_SESSION['sessmodl'];
		else if (isset($tmodl[$modl]))
			$_SESSION['sessmodl'] = $modl;
		else
			$_SESSION['sessmodl'] = $modl = 0;
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />
<script type="text/javascript">
<!--
function voir(chn, k) {
	chn = "../nmodele/"+chn+".php?modl="+k+"&vis=";
	k = String(Math.random());
	k = k.substr(k.length - 6);
	fen = window.open(chn+k, "fprinc");
	fen.focus();
	return false;
}
//-->
</script>
</head>

<body onLoad="pagechargee();">
<div class="cmsdivtab">
<table cellspacing="0" cellpadding="0" class="cmstabtab">
<?
	echo '<tr><td class="cmsentete"><img src="matos/admlogo.gif" class="cmslogo" alt="" /><span class="cmstetitr">',ADMINENTETE,'</span></td></tr>',"\n",'<tr><td class="cmstabbox">',"\n";
	if (! (ADMOPTGEN & 8))
		echo '<div class="divcentre"><a href="letrMenu.php?tci=',$tci,'"><b>Aller au menu général</b></a></div>',"\n";
		
	echo '<div class="divgauche"><img src="matos/puce.gif" alt="" /> ',$protectacces & 256 ? '<a href="letrMenu.php?tci='.$tci.'&amp;plus=y" style="color:#000000;text-decoration:none">Menu listes de diffusion / newsletters</a>' : 'Menu listes de diffusion / newsletters';
	echo '<a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

	if (count($tmodl) > 1) {
		reset($tmodl); $chn = '';
		while (list($k, $g) = each($tmodl))
			$chn .= '<option value="'.$k.($modl == $k ? '" selected="selected">' : '">').htmlspecialchars($g).'</option>'."\n";

		echo '<form action="letrMenu.php" method="get">',"\n",'<div class="divmenu">Modèle de newsletter <select name="modl" class="liste" onchange="this.form.submit();">',"\n",$chn,'</select></div>',"\n",'<input name="tci" type="hidden" value="',$tci,'" /></form>',"\n";
	}
	echo '<div class="divmenu">&#8226; <a href="letrRedig.php?modl=',$modl,'&amp;tci=',$tci,'">Rédiger une newsletter</a></div>',"\n";

	$sql = "SELECT nlid FROM cmsnlmsg WHERE quoi='prepar' AND modl=$modl AND html<>''";
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	$k = mysql_num_rows($req) ? rawurlencode($tmodl[$modl]) : 'defaut';
	echo '<div class="divmenu">&#8226; <a href="#voir" onclick="return voir(\'',$k,'\',',$modl,');">Visualiser la newsletter</a></div>',"\n";

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrEnvoi.php?modl=',$modl,'&amp;tci=',$tci,'">Envoyer la newsletter</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Envoyer la newsletter</div>',"\n";

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrEnvUne.php?modl=',$modl,'&amp;tci=',$tci,'">Envoyer la Une de DLP</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Envoyer la Une de DLP</div>',"\n";

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrEnvUneSem.php?modl=',$modl,'&amp;tci=',$tci,'">Envoyer la Une / semaine de DLP</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Envoyer la Une/semaine de DLP</div>',"\n";

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrEnvConc.php?modl=',$modl,'&amp;tci=',$tci,'">Envoyer tous les concours de DLP</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Envoyer tous les concours de DLP</div>',"\n";
	
	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrEnvConc.php?modl=',($modl | 256),'&amp;tci=',$tci,'">Envoyer les concours sélectionnés de DLP</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Envoyer les concours sélectionnés de DLP</div>',"\n";
	
	echo '<div class="divmenu">&#8226; <a href="letrEnvLivres.php?modl=',$modl,'&amp;tci=',$tci,'">Envoyer les livres de DLP</a></div>',"\n";	

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrListe.php?tci=',$tci,'">Gérer les listes de diffusion</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Gérer les listes de diffusion</div>',"\n";

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrInscr.php?tci=',$tci,'">Inscrire / désinscrire</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Inscrire / désinscrire</div>',"\n";

	if ($protectacces & 1)
		echo '<div class="divmenu">&#8226; <a href="letrArchiv.php?modl=',$modl,'&amp;tci=',$tci,'">Consulter les archives</a></div>',"\n";
	else
		echo '<div class="divmenu">&#8226; Consulter les archives</div>',"\n";

	if (ADMOPTGEN & 8)
		echo '<div class="divmenu">&#8226; <a href="letrMenu.php?dconm=y">Se déconnecter</a></div>',"\n";

	if ($protectacces & 256 && isset($plus))
		echo '<br /><div class="divmenu">&#8226; <a href="letrMenu.php?tci=',$tci,'&amp;init=y">Recalculer les modèles de newsletters</a></div>',"\n";
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
