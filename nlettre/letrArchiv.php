<? /*
	On peut supprimer les archives de plus de 15 jours, ou les envois de test, ou superadmin
*/
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

	//----- obtenir les modèles
	$tmodl = isset($_SESSION['sesstmodl']) ? $_SESSION['sesstmodl'] : array();

	/************************************
	****** Supprimer des archives *******
	************************************/
	if ($oper == 'suppr' && isset($chbid)) {
		$chbid = implode(',', $chbid);

		//--- supprimer les fichiers
		$sql = "SELECT texte FROM cmsnlmsg WHERE (quoi='-image' OR quoi='-attach') AND sujet IN ($chbid)";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
			if (! @unlink('../nmedia/'.$data['texte']))
				$alerter .= '\nMais il est impossible de supprimer le fichier nmedia/'.$data['texte'];

		//--- supprimer les messages
		$sql = "DELETE FROM cmsnlmsg WHERE nlid IN ($chbid) AND quoi='-prepar' OR quoi IN ('-envoi','-image','-attach') AND sujet IN ($chbid)";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$alerter = 'La suppression des archives sélectionnées est réussie'.$alerter;
		$diriger = 'letrMenu.php?tci='.$tci;
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />
<script type="text/javascript">
<!--
function voir(chn, k) {
	chn = "../nmodele/"+chn+".php?archv="+k+"&vis=";
	k = String(Math.random());
	k = k.substr(k.length - 6);
	fen = window.open(chn+k, "fprinc");
	fen.focus();
	return false;
}
function supprimer() {
	ofo = document.getElementById("iofo");
	ofo.oper.value = "suppr";
	ofo.submit();
}
//-->
</script>
</head>

<body onload="pagechargee()">
<div class="cmsdivtab">
<table cellspacing="0" cellpadding="0" class="cmstabtab">
<?
	echo '<tr><td class="cmsentete"><img src="matos/admlogo.gif" class="cmslogo" alt="" /><span class="cmstetitr">',ADMINENTETE,'</span></td></tr>',"\n",'<tr><td class="cmstabbox">',"\n";

	echo '<div class="divcentre"><a href="letrMenu.php?tci=',$tci,'"><b>Aller au menu</b></a></div>',"\n";
	$k = 'Consulter les newsletters archivées';
	if ($protectacces & 256)
		$k = '<a href="letrArchiv.php?modl='.$modl.'&amp;tci='.$tci.'&amp;supera=y" style="color:#000000;text-decoration:none">'.$k.'</a>';
	echo '<div class="divgauche"><img src="matos/puce.gif" alt="" /> ',$k,'<a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

	echo '<form id="iofo" action="letrArchiv.php?tci=',$tci,'" method="post">',"\n",'<table border="0" align="center" cellpadding="2" cellspacing="0">',"\n",'<tr><td width="16"></td><td width="120"><b>Envoi</b></td><td width="24"></td><td width="550"><b>Sujet</b></td></tr>',"\n";

	//----- obtenir les listes de diffusion
	$sql = 'SELECT ladrm,lletr,lcode FROM cmsnletter WHERE letat=\'0\'';
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
		$tnomli[$data['lletr']] = htmlspecialchars($data['lcode']);

	//----- obtenir les newsletters archivées
	reset($tmodl); $tlist = '0';
	while (list($k) = each($tmodl))
		$tlist .= ','.$k;
	$sql = "SELECT nlid,quoi,modl,nmulti,sujet,IF(quoi='-envoi',texte,'') AS tlist,IF(quoi='-envoi',html,IF(html='',0,1)) AS critq FROM cmsnlmsg WHERE (quoi='-prepar' OR quoi='-envoi') AND (modl=$modl OR modl NOT IN ($tlist)) ORDER BY nlid DESC";
	$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

	$datelim = date('Y-m-d H:i', time() - 1296000); $sql = false;
	$nlid = 0;
	while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
		if ($data['quoi'] == '-envoi') {
			$nlid = $data['sujet'];
			$sdat = $data['nmulti'];
			$tlist = $data['tlist'];
			$critq = $data['critq'] ? '<br />Critères : '.htmlspecialchars($data['critq']) : '';
		}
		else if ($data['nlid'] == $nlid) {
			if ($tlist != '@test') {
				$g = explode('^|~', $tlist);
				reset($g); $tlist = '';
				while (list($k) = each($g))
					if (isset($tnomli[$g[$k]]))
						$tlist .= $tlist ? ', '.$tnomli[$g[$k]] : $tnomli[$g[$k]];
			}
			$g = $data['modl'] == $modl && isset($tmodl[$modl]) && $data['critq'] ? $tmodl[$modl] : 'defaut';
			if ($sdat <= $datelim || $tlist == '@test' || $protectacces & 256 && isset($supera)) {
				$k = '<input name="chbid[]" type="checkbox" class="cocher" value="'.$nlid.'" />';
				$sql = true;
			}
			else
				$k = '&#8226;';
			echo '<tr><td>',$k,'</td><td>',$sdat,'</td><td>',$data['nmulti'],'</td><td><a href="#voir" onclick="return voir(\'',rawurlencode($g),'\',',$nlid,');">',htmlspecialchars($data['sujet']),'</a></td></tr>',"\n";
			echo '<tr><td></td><td colspan="3" style="padding-bottom:10px;">Destinataires : ',$tlist == '@test' ? 'TEST' : $tlist,$critq,'</td></tr>',"\n";
		}
	if ($nlid)
		echo '</table>',"\n",$sql ? '<div class="divcentre"><input type="button" class="bouton" onclick="supprimer()" value="Supprimer la sélection" /></div>'."\n" : '';
	else
		echo '<tr><td></td><td colspan="3"><br />Il n\'y a aucune newsletter archivée</td></tr></table>',"\n";
	echo '<input name="modl" type="hidden" value="',$modl,'" /><input name="oper" type="hidden" value="',$oper,'" /></form>',"\n";
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
