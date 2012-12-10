<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" >
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$lang_insert_imgsave_title}</title>
	<script language="javascript" type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<base target="_self" />
<script language="javascript" type="text/javascript">
<!--
function init() {
	tinyMCEPopup.resizeToInnerSize();

	if (obj = document.getElementById("in1tit"))
		obj.innerHTML = tinyMCE.getLang("lang_insert_imgsave_title");
	if (obj = document.getElementById("in2tit"))
		obj.innerHTML = tinyMCE.getLang("lang_insert_imgsave_fich");
	document.forms[0].insert.value = tinyMCE.getLang("lang_update");
	document.forms[0].cancel.value = tinyMCE.getLang("lang_cancel");
}
function aprescharg() {
<?
	//--- richir : upload image
	require('../../conf.php');
	if (isset($oper) && $oper == 'enreg') {
		$erreur = '';
		//--- Connexion à la DB
		$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
		if ($db_link)
			mysql_select_db($sql_bdd, $db_link);
		else
			$erreur = 'Connexion impossible à la base de données';

		//--- Enregistrer le fichier image
		if (! $erreur && $imgfich['error'])
			switch ($imgfich['error']) {
				case 1:
				case 2: $erreur = 'La taille maximum du fichier à télécharger est dépassée'; break;
				case 3: $erreur = 'Le fichier n\'a pas pu être téléchargé entièrement'; break;
				case 4: $erreur = 'Il n\'y a aucun fichier à télécharger'; break;
				default: $erreur = 'Erreur lors du téléchargement';
			}

		if (! $erreur && ! ($imgfich['name'] && $imgfich['size']))
			$erreur = 'Il n\'y a aucun fichier à télécharger';

		if (! $erreur && ! ($mpage && $mcont))
			$erreur = 'Les paramètres page et contenu n\'ont pas été transmis';

		if (! $erreur) {
			$mtype = $imgfich['type'];
			$format = substr($mtype, 0, 5);
			if ($format != 'image')
				$erreur = 'Le format du fichier n\'est pas valide';
			else if ($imgfich['size'] > 409600)
			 	$erreur = 'La taille maximum d\'un fichier image est de 400 Ko (JPEG, PNG, GIF)';
		}
		$fident = 0;
		if (! $erreur) 
			if (mysql_query("INSERT INTO cmszmedia (mcont,mpage) VALUES ($mcont,$mpage)"))
				$fident = mysql_insert_id();
			else
				$erreur = 'Erreur SQL insert: '.(DEBUGSQL ? mysql_error() : 'voir DEBUGSQL');

		if (! $erreur) {
			$fnom = preg_replace('/[^A-Z\.a-z_0-9]/', '', basename($imgfich['name']));
			$k = strrpos($fnom, '.');
			if ($k)
				$fnom = 'z_'.substr($fnom, 0, $k).'_'.$fident.substr($fnom, $k);
			else
				$erreur = 'Le nom du fichier '.addslashes($imgfich['name']).' n\'est pas valide';
		}
		if (! $erreur)
			if (! @copy($imgfich['tmp_name'], '../../../nmedia/'.$fnom))
				$erreur = 'Il est impossible de copier le fichier '.addslashes($imgfich['name']);
		@unlink($imgfich['tmp_name']);

		if ($erreur) {
			if ($fident && ! mysql_query("DELETE FROM cmszmedia WHERE mfich=$fident"))
				$erreur .= ' / Erreur SQL delete: '.(DEBUGSQL ? mysql_error() : 'voir DEBUGSQL');
		}
		else if (! mysql_query("UPDATE cmszmedia SET mformat='$format',mtype='$mtype',mnom='$fnom' WHERE mfich=$fident"))
			$erreur = 'Erreur SQL update: '.(DEBUGSQL ? mysql_error() : 'voir DEBUGSQL');

		mysql_close($db_link);

		if ($erreur)
			echo 'alert("Le téléchargement a échoué\n',$erreur,'");',"\n";
		else {
			echo 'fen = tinyMCE.getWindowArg("fenetre");',"\n";
			echo 'if (typeof(fen) != "undefined" && ! fen.closed) {',"\n";
			echo 'fen.document.forms[0].elements["src"].value = "nmedia/',$fnom,'";',"\n";
			echo 'fen.document.forms[0].elements["id"].value = "i',$fident,'";',"\n";
			echo 'fen.showPreviewImage("nmedia/',$fnom,'", false); }',"\n";
			echo 'tinyMCEPopup.close();',"\n";
		}
	}
?>
}
function uploadImage() {
	ofo = document.getElementById("iofo");
	ofo.mpage.value = tinyMCE.getWindowArg("mpage");
	ofo.mcont.value = tinyMCE.getWindowArg("mcont");
	ofo.oper.value = "enreg";
	ofo.submit();
}
//-->
</script>
</head>
<body onload="tinyMCEPopup.executeOnLoad('init();'); aprescharg();" style="display: none">
<form id="iofo" onsubmit="uploadImage(); return false;" action="#" method="post" enctype="multipart/form-data">
	<input name="oper" type="hidden" value="" />
	<input name="mpage" type="hidden" value="0" />
	<input name="mcont" type="hidden" value="0" />

	<table border="0" cellpadding="4" cellspacing="0">
		<tr>
			<td colspan="2" class="title" id="in1tit">{$lang_insert_imgsave_title}</td>
		</tr>
		<tr>
			<td nowrap="nowrap" id="in2tit">{$lang_insert_imgsave_fich}:</td>
			<td><input name="imgfich" type="file" id="imgfich" value="" size="44" /></td>
		</tr>
	</table>
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="insert" name="insert" value="{$lang_update}" onclick="uploadImage();" />
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="{$lang_cancel}" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>
</body>
</html>
