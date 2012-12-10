<?
	require('admEntete.php');

	//--- rendre les param�tres valides
	if (! isset($oper))
		$oper = '';
	if (! isset($modl) || ! is_numeric($modl))
		$modl = 0;

	if (! ($protectacces & 4096)) {
		//--- acc�s limit� : rediriger
		mysql_close($db_link);
		header('Location:letrMenu.php?tci='.$tci);
		exit;
	}
	//--- initialiser le message et la redirection
	$alerter = '';
	$diriger = '';

	//***** T�l�charger fichier attach� ******
	function telechargerfich($ftemp, $fnom, $ftaille, $ferreur) {
		global $alerter;

		if ($ferreur) {
			$alerter = 'Erreur lors du t�l�chargement de '.addslashes($fnom);
			switch ($ferreur) {
				case 1:
				case 2: $alerter .= '\nLa taille maximum du fichier � t�l�charger est d�pass�e'; break;
				case 3: $alerter .= '\nLe fichier n\'a pas pu �tre t�l�charg� enti�rement'; break;
				case 4: $alerter .= '\nIl n\'y a aucun fichier � t�l�charger'; break;
			}
		}
		else
			if (strpos($fnom, '.php'))
		 		$alerter = 'Ce type de fichier n\'est pas accept�';
			else if ($ftaille > 1048576)
		 		$alerter = 'La taille maximum d\'un fichier attach� est de 1 Mo';

		if (! $alerter) {
			$chn = preg_replace('/[^A-Z\.a-z_0-9]/', '', basename($fnom));
			$k = strrpos($chn, '.');
			if ($k)
				$chn = 'a_'.substr($chn, 0, $k).'_'.substr(rand(), -5).substr($chn, $k);
			else
				$alerter = 'Le nom du fichier '.addslashes($fnom).' n\'est pas valide';
		}
		if (! $alerter)
			if (! @copy($ftemp, '../nmedia/'.$chn))
				$alerter = 'Il est impossible de t�l�charger le fichier '.addslashes($fnom);

		return $alerter ? '' : $chn;
	}

	/*************************
	****** Enregistrer *******
	*************************/
	if ($oper == 'enreg') {
		//----- contr�les
		if (! $alerter) {
			if (! $nltext && ! $nlhtml)
				$alerter = 'Il faut remplir le message au format texte et/ou au format HTML';
			if (! $nlsuj)
				$alerter = 'Il faut remplir le sujet du message';
		}
		//----- fichiers attach�s
		while (! $alerter && list($k) = each($tattele['name']))
			if ($tattele['name'][$k] && $tattele['size'][$k]) {
				$g = $tattele['type'][$k];
				$chn = telechargerfich($tattele['tmp_name'][$k], $tattele['name'][$k], $tattele['size'][$k], $tattele['error'][$k]);
				if ($chn) {
					$g = filesize('../nmedia/'.$chn).'^|~'.$g;
					$sql = "INSERT INTO cmsnlmsg SET quoi='attach',modl=$modl,nmulti='2',texte='$chn',html='$g'";
					$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					$tattnom[$k] = $chn;
				}
			}
		while (isset($tattnom) && (list($k, $chn) = each($tattnom)))
			if (! isset($tattbox[$k]))
				if (@unlink('../nmedia/'.$chn)) {
					$sql = "DELETE FROM cmsnlmsg WHERE quoi='attach' AND modl=$modl AND texte='$chn'";
					$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

					unset($tattnom[$k]);
					if (! count($tattnom))
						unset($tattnom);
				}
				else
					$alerter .= '\nIl est impossible de supprimer le fichier nmedia/'.$chn;

		if (! $alerter) {
			//--- m�moriser sujet, message
			$sql = "UPDATE cmsnlmsg SET nmulti='$nlang',sujet='$nlsuj',texte='$nltext',html='$nlhtml' WHERE quoi='prepar' AND modl=$modl";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			//--- supprimer les images inutiles
			if (preg_match_all('/<img[^<>]* id=\\\"(dans|hors)(\d+)\\\"/', $nlhtml, $chn)) {
				unset($chn[0], $chn[1]);
				$sql = ' FROM cmsnlmsg WHERE nlid NOT IN ('.implode(',', $chn[2]).') AND quoi=\'image\' AND modl='.$modl;
				unset($chn);
			}
			else
				$sql = " FROM cmsnlmsg WHERE quoi='image' AND modl=$modl";
			$req = mysql_query('SELECT texte'.$sql) or die('Erreur SQL: '.(DEBUGSQL ? 'SELECT texte'.$sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
				if (! @unlink('../nmedia/'.$data['texte']))
					$alerter .= '\nIl est impossible de supprimer le fichier nmedia/'.$data['texte'];

			$req = mysql_query('DELETE'.$sql) or die('Erreur SQL: '.(DEBUGSQL ? 'DELETE'.$sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			//--- confirmer les attachements et les images
			$sql = "UPDATE cmsnlmsg SET nmulti=nmulti|4 WHERE (quoi='attach' OR quoi='image') AND modl=$modl";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$diriger = 'letrMenu.php?tci='.$tci;
		}
		if (! $alerter && $diriger) {
			mysql_close($db_link);
			header('Location:'.$diriger);
			exit;
		}
	}

	/********************************
	****** Obtenir le contenu *******
	********************************/
	if (! $oper) {
		$sql = "SELECT quoi,nmulti,sujet,texte,html FROM cmsnlmsg WHERE quoi='prepar' AND modl=$modl OR quoi='envoi' ORDER BY quoi";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		$data = mysql_fetch_array($req, MYSQL_ASSOC);
		if ($data && $data['quoi'] == 'prepar') {
			$nlang = $data['nmulti'];
			$nlsuj = addslashes($data['sujet']);
			$nltext = addslashes($data['texte']);
			$nlhtml = addslashes($data['html']);

			//----- obtenir les fichiers attach�s
			$sql = "SELECT texte FROM cmsnlmsg WHERE quoi='attach' AND modl=$modl ORDER BY nlid";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

			$tattnom = array();
			while ($data = mysql_fetch_array($req, MYSQL_ASSOC))
				$tattnom[] = addslashes($data['texte']);
		}
		else {
			$nlang = $nlsuj = $nltext = $nlhtml = '';
			$alerter = ! mysql_num_rows($req) ? 'La newsletter li�e � ce mod�le manque' : 'L\'�criture d\'une newsletter est impossible pour le moment,\nil faut d\'abord poursuivre ou annuler l\'envoi en cours';
			$diriger = 'letrMenu.php?tci='.$tci;
		}
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',"\n";
	echo '<html><head><title>',ADMINENTETE,'</title>',"\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="matos/admin.css" type="text/css" />

<script language="javascript" type="text/javascript" src="tiny_mce.js"></script>
<script type="text/javascript">
<!--
tinyMCE.init({
mode:"exact", elements:"nlhtml", theme:"advanced", width:"680", height:"550", language:"fr",
relative_urls:false, remove_script_host:false, document_base_url:"<? $chn = $_SERVER['PHP_SELF']; $k = strpos($chn, '/nlettre/'); echo 'http://',$_SERVER['SERVER_NAME'],$k !== false ? substr($chn, 0, $k).'/' : '/'; ?>",
dialog_type:"modal", visual:true, theme_advanced_toolbar_location:"top", theme_advanced_toolbar_align:"left", theme_advanced_path_location:"bottom",
extended_valid_elements:"#p[id|style|dir|class|align|onmouseover|onmouseout],-span[style|class|align|onmouseover|onmouseout],-div[id|dir|class|align|style|onmouseover|onmouseout],-td[id|lang|dir|class|colspan|rowspan|width|height|align|valign|style|bgcolor|background|bordercolor|scope|onmouseover|onmouseout]",
plugins:"advimage,advlink,contextmenu,paste,searchreplace,style,table",
theme_advanced_buttons1:"fontselect,fontsizeselect,bold,italic,underline,sub,sup,forecolor,backcolor,|,removeformat,|,styleselect",
theme_advanced_buttons2:"tablecontrols,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent",
theme_advanced_buttons3:"help,|,link,unlink,anchor,|,image,|,hr,|,charmap,|,undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,cleanup,|,styleprops",
theme_advanced_resize_horizontal:false, theme_advanced_resizing:true, theme_advanced_resizing_use_cookie:false,
convert_fonts_to_spans:true, fix_list_elements:true, fix_table_elements:true,
inline_styles:true, content_css:"<? echo '../nmodele/modele',$modl,'.css'; ?>",
file_browser_callback:"imgparcourir", debug:false });

function imgparcourir(fieldname, url, type, win) {
	if (type == 'image') {
		var template = new Array();
		template['file'] = 'letrimgsave.php';
		template['width'] = 400;
		template['height'] = 210 + (tinyMCE.isNS7 ? 30 : 0);
		template['close_previous'] = "no";
		tinyMCE.openWindow(template, {editor_id:"nlhtml", inline:"yes", fenetre:win<? echo ', mnletrmodl:',$modl; ?>});
	}
}
function enregistrer() {
	ofo = document.getElementById("iofo");
	ofo.oper.value = "enreg";
	ofo.submit();
}
function ajouterfich() {
	chn = '<br />T�l�charger<input name="tattele['+nbrfich+']" type="file" class="saisie" size="60" style="margin:4px 0 4px 48px;" /><input name="tattbox['+nbrfich+']" type="hidden" value="Y" />';
	obj = document.createElement("span");
	obj.innerHTML = chn;
	document.getElementById("cellfich").appendChild(obj);
	nbrfich++;
	return false;
}
function copiertxt() {
	chn = tinyMCE.getContent("nlhtml");
chn = chn.replace(/<br \/>/gi, "\n"); chn = chn.replace(/<\/p>/gi, "\n"); chn = chn.replace(/<[^<>]+>/gi, "");
chn = chn.replace(/&lt;/g, "<"); chn = chn.replace(/&gt;/g, ">"); chn = chn.replace(/&amp;/g, "&");
chn = chn.replace(/&quot;/g, '"'); chn = chn.replace(/&#39;/g, "'"); chn = chn.replace(/&nbsp;/g, " ");
chn = chn.replace(/&agrave;/g, "�"); chn = chn.replace(/&acirc;/g, "�"); chn = chn.replace(/&ccedil;/g, "�");
chn = chn.replace(/&egrave;/g, "�"); chn = chn.replace(/&eacute;/g, "�"); chn = chn.replace(/&ecirc;/g, "�");
chn = chn.replace(/&euml;/g, "�"); chn = chn.replace(/&icirc;/g, "�"); chn = chn.replace(/&iuml;/g, "�");
chn = chn.replace(/&ocirc;/g, "�"); chn = chn.replace(/&ugrave;/g, "�"); chn = chn.replace(/&ucirc;/g, "�");
chn = chn.replace(/&uuml;/g, "�"); chn = chn.replace(/&copy;/g, "�"); chn = chn.replace(/&reg;/g, "�");
chn = chn.replace(/&Agrave;/g, "�"); chn = chn.replace(/&Acirc;/g, "�"); chn = chn.replace(/&Ccedil;/g, "�");
chn = chn.replace(/&Egrave;/g, "�"); chn = chn.replace(/&Eacute;/g, "�"); chn = chn.replace(/&Ecirc;/g, "�");
chn = chn.replace(/&Euml;/g, "�"); chn = chn.replace(/&Icirc;/g, "�"); chn = chn.replace(/&Iuml;/g, "�");
chn = chn.replace(/&Ocirc;/g, "�"); chn = chn.replace(/&Ugrave;/g, "�"); chn = chn.replace(/&Ucirc;/g, "�");
chn = chn.replace(/&Uuml;/g, "�"); chn = chn.replace(/&deg;/g, "�"); chn = chn.replace(/&acute;/g, "�");
chn = chn.replace(/&laquo;/g, "�"); chn = chn.replace(/&raquo;/g, "�"); chn = chn.replace(/&euro;/g, "�");
chn = chn.replace(/&oelig;/g, "�"); chn = chn.replace(/&OElig;/g, "�"); chn = chn.replace(/&rsquo;/g, "�");
chn = chn.replace(/&ldquo;/g, "�"); chn = chn.replace(/&rdquo;/g, "�"); chn = chn.replace(/&bdquo;/g, "�");
	ofo = document.getElementById("iofo");
	ofo.nltext.value = chn;
	return false;
}
function effacer() {
	ofo = document.getElementById("iofo");
	ofo.nlsuj.value = "";
	tinyMCE.setContent("");
	ofo.nltext.value = "";
	for (k = ofo.elements.length, k--; k >= 0; k--)
		if (ofo.elements[k].type == "checkbox")
			ofo.elements[k].checked = false;
	return false;
}
function rappelsauv() {
	window.focus();
	alert("Veuillez enregistrer la newsletter dans les 5 prochaines minutes svp");
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
	echo '<div class="divgauche"><img src="matos/puce.gif" alt="" /> R�diger une newsletter<a href="http://www.vertige.org/aidecms/aideCMS.php?apg=diffusion" target="waide"><img src="matos/aide.gif" class="ico" alt="" title="aide" /></a></div>',"\n";

	echo '<form id="iofo" action="letrRedig.php?tci=',$tci,'" method="post" enctype="multipart/form-data">',"\n","\n";
	echo '<table border="0" align="center" width="94%" cellpadding="2" cellspacing="0">',"\n";

	echo '<tr><td>Langue &nbsp; &nbsp; &nbsp; &nbsp; <select name="nlang" class="liste"><option value="fr"',$nlang == 'fr' ? ' selected>' : '>','fran�ais</option><option value="nl"',$nlang == 'nl' ? ' selected>' : '>','n�erlandais</option><option value="en"',$nlang == 'en' ? ' selected>' : '>','anglais</option></select></td></tr>',"\n";

	echo '<tr><td style="padding-top:12px;">Sujet / objet</td></tr>',"\n",'<tr><td style="padding-left:30px;"><input name="nlsuj" type="text" class="saisie" value="',htmlspecialchars(stripslashes($nlsuj)),'" maxlength="255" style="width:680px;" /></td></tr>',"\n";
	echo '<tr><td style="padding-top:12px;">Message au format HTML</td></tr>',"\n",'<tr><td style="padding-left:30px;"><textarea name="nlhtml" class="txtarea" rows="20" style="width:680px;display:none;">',htmlspecialchars(stripslashes($nlhtml)),'</textarea></td></tr>',"\n";
	echo '<tr><td style="padding-top:12px;"><span style="float:right;margin-right:26px;"><a href="#copier" onclick="return copiertxt();">copier ci-dessous le message html sans la mise en forme</a></span>Message au format texte</td></tr>',"\n",'<tr><td style="padding-left:30px;"><textarea name="nltext" class="txtarea" rows="10" style="width:680px;">',htmlspecialchars(stripslashes($nltext)),'</textarea></td></tr>',"\n";

	echo '<tr><td style="padding-top:12px;">Attachements</td></tr>',"\n",'<tr><td id="cellfich" style="padding-left:30px;">',"\n";
	$nbrfich = 0;
	if (isset($tattnom)) {
		reset($tattnom);
		while (list($k, $chn) = each($tattnom)) {
			echo 'Fichier<input name="tattnom[',$k,']" type="hidden" value="',$chn,'" />',"\n",'<input name="tattbox[',$k,']" type="checkbox" class="cocher" value="Y" checked style="margin:4px 0 4px 80px;" />',"\n",'Conserver &nbsp; &nbsp; <a href="../nmedia/',$chn,'" target="_blank">',$chn,'</a><br />',"\n";
			$nbrfich = $k;
		}
		$nbrfich++;
	}
	echo 'T�l�charger<input name="tattele[',$nbrfich,']" type="file" class="saisie" size="60" style="margin:4px 0 4px 48px;" /><input name="tattbox[',$nbrfich,']" type="hidden" value="Y" /></td></tr>',"\n";
	echo '<tr><td style="text-align:center;">Ajouter un attachement <a href="#ajouter" onclick="return ajouterfich()"><img src="matos/ajout.gif" class="bajout" alt="" title="ajouter" onmouseover="this.src=\'matos/ajover.gif\'" onmouseout="this.src=\'matos/ajout.gif\'" /></a></td></tr>',"\n",'</table>',"\n";

	echo '<div class="divcentre"><a href="#effacer" onclick="return effacer();">Effacer</a> &nbsp; &nbsp; &nbsp; &nbsp; <input type="button" class="bouton" onclick="enregistrer()" value="Enregistrer" /></div>',"\n",'<input name="modl" type="hidden" value="',$modl,'" /><input name="oper" type="hidden" value="" /></form>',"\n";
?>
		</td>
	</tr>
	<tr><td style="text-align:right"><a href="http://www.vertige.org/" target="_blank" class="cmsareal">conception Vertige asbl</a> &nbsp;</td></tr>
</table>
</div>
<script type="text/javascript">
<!--
<?
	//--- D�connexion de la DB
	mysql_close($db_link);

	echo 'var nbrfich = ',++$nbrfich,';',"\n",'function pagechargee() { window.setTimeout("rappelsauv()", 3300000); ';
	if ($alerter) echo 'alert("',$alerter,'"); ';
	echo $diriger ? 'window.location.href="'.$diriger.'"; }' : '}',"\n";
?>
//-->
</script>
</body>
</html>
