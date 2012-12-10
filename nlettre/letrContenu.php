<?
if ($chn == 'intro') {
	require('conf.php');

	if (! isset($modl) || ! is_numeric($modl))
		$modl = 0;
	if (! isset($archv) || ! is_numeric($archv))
		$archv = 0;

	//--- Connexion à la DB
	$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
	if (! $db_link) {
		echo 'Connexion impossible à la base de données ',$sql_bdd,' sur le serveur ',$sql_server;
		exit;
	}
	mysql_select_db($sql_bdd, $db_link);

	if ($archv)
		$sql = "SELECT nlid,modl,nmulti,sujet,texte,html FROM cmsnlmsg WHERE nlid=$archv AND quoi='-prepar' OR quoi='-envoi' AND sujet='$archv' ORDER BY nlid DESC";
	else
		$sql = 'SELECT nlid,modl,nmulti,sujet,texte,html FROM cmsnlmsg WHERE quoi=\'prepar\' AND modl='.$modl;
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	if ($archv && $data = mysql_fetch_array($req, MYSQL_ASSOC))
		$archv = $data['nmulti']; //--- date

	if ($data = mysql_fetch_array($req, MYSQL_ASSOC)) {
		$nlid = $data['nlid'];
		$modl = $data['modl'];
		$vide = $data['html'] == '';

		echo '<div style="line-height:150%;margin:0;padding:4px 8px 16px 8px;color:#000000;background:#FFFFFF;font-family:sans-serif;font-weight:normal;font-size:medium;font-style:normal;text-align:left;text-decoration:none;">',"\n";
		if ($archv)
			echo '&#8226; <b>',htmlspecialchars($data['sujet']),'</b> - ',$archv,"\n";
		else {
			switch ($data['nmulti']) {
			case 'fr':
				$k = 'français';
				$chn = 'Pour ne plus recevoir la newsletter, veuillez cliquer sur ce lien :';
				break;
			case 'nl':
				$k = 'néerlandais';
				$chn = 'Om de nieuwsbrief niet meer te ontvangen, klik op deze link :';
				break;
			default:
				$k = 'anglais';
				$chn = 'If you don\'t want to receive the newsletter anymore, please click on this link :';
			}
			echo '<span style="float:right;"> (',$k,')</span>',"\n",'<b>&#8226; Sujet :</b> ',"\n",htmlspecialchars($data['sujet']),"\n";
		}

		//----- fichiers attachés
		if ($archv)
			$sql = "SELECT texte FROM cmsnlmsg WHERE quoi='-attach' AND modl=$modl AND sujet='$nlid' ORDER BY nlid";
		else
			$sql = "SELECT texte FROM cmsnlmsg WHERE quoi='attach' AND modl=$modl ORDER BY nlid";
		$r2q = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($k = mysql_num_rows($r2q))
			echo '<br /><b>&#8226; Attachement',$k > 1 ? 's :</b> ' : ' :</b> ',"\n";
		while ($dat2 = mysql_fetch_array($r2q, MYSQL_ASSOC))
			echo '&nbsp;+&nbsp;<a href="../nmedia/',$dat2['texte'],'" target="_blank" style="text-decoration:underline;color:#000071;">',$dat2['texte'],'</a>',"\n";

		$k = '';
		if (! $archv) {
			echo '<br /><b>&#8226; Message au format texte :</b>',"\n";
			if (! $vide) {
				echo '<script type="text/javascript">',"\n",'<!--',"\n",'var vistxt = false; function voirtexte() {',"\n",'if (obj = document.getElementById("nltxtmsg")) obj.style.display = vistxt ? "none" : "block";',"\n",'if (obj = document.getElementById("nltxtlk")) obj.innerHTML = vistxt ? "montrer" : "cacher";',"\n",'vistxt = ! vistxt; return false; }',"\n",'//-->',"\n",'</script>',"\n";
				echo ' <a id="nltxtlk" href="#" onclick="return voirtexte();" title="montrer / cacher" style="text-decoration:underline;color:#000071;">montrer</a>',"\n";
				$k = 'display:none;" id="nltxtmsg';
			}
		}
		if (! $archv || $vide)
			echo '<div style="line-height:100%;margin-left:20px;',$k,'">',"\n",nl2br(htmlspecialchars($data['texte'])),"\n",'</div>',"\n";

		if (! $archv)
			echo '<div style="line-height:100%;margin:4px 0 0 16px;font-size:smaller;">',$chn,'<br /><a href="#" style="text-decoration:underline;color:#000071;">http://exemple_page_desinscription</a></div>',"\n";
		echo '</div>',"\n";
	}
	else
		$nlid = 0;
}
else if ($chn == 'html' && isset($nlid)) {
	if ($nlid)
		echo $data['html'],"\n";

	//--- Déconnexion de la DB
	mysql_close($db_link);
}
else
	echo ' <span style="color:#900000;">Erreur : il manque "intro" / "html" dans le fichier modèle.</span> ';
?>
