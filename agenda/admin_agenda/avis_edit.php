<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Edition d'un AVIS post&eacute; par un visiteur</title>

<!-- tinyMCE -->
<script language="javascript" type="text/javascript" src="../vertiny/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		elements : "ajaxfilemanager",
		theme : "advanced",
		plugins : "paste,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_buttons1 : "bold,italic,separator,"
		+ "justifyleft,justifycenter,justifyright,justifyfull,",
		theme_advanced_buttons2 : "undo,redo,separator,bullist,numlist,outdent,indent,separator,fullscreen,cleanup,code",
		theme_advanced_buttons3 : "",
		extended_valid_elements : "hr[class|width|size|noshade]",
		paste_use_dialog : false,
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : true,
		apply_source_formatting : true,
		force_br_newlines : true,
		force_p_newlines : false,	
		relative_urls : true,
		
		content_css : "../vertiny/css_vertiny.css"

	});

	function ajaxfilemanager(field_name, url, type, win) {
		var ajaxfilemanagerurl = "../../../../jscripts/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php";
		switch (type) {
			case "image":
				ajaxfilemanagerurl += "?type=img";
				break;
			case "media":
				ajaxfilemanagerurl += "?type=media";
				break;
			case "flash": //for older versions of tinymce
				ajaxfilemanagerurl += "?type=media";
				break;
			case "file":
				ajaxfilemanagerurl += "?type=files";
				break;
			default:
				return false;
		}
		var fileBrowserWindow = new Array();
		fileBrowserWindow["file"] = ajaxfilemanagerurl;
		fileBrowserWindow["title"] = "Ajax File Manager";
		fileBrowserWindow["width"] = "782";
		fileBrowserWindow["height"] = "440";
		fileBrowserWindow["close_previous"] = "no";
		tinyMCE.openWindow(fileBrowserWindow, {
		  window : win,
		  input : field_name,
		  resizable : "yes",
		  inline : "yes",
		  editor_id : tinyMCE.getWindowArg("editor_id")
		});
		
		return false;
	}
</script>
<!-- /tinyMCE -->
	
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="head_admin_agenda"></div>

<!-- h1 voir plus bas -->

<?php
require '../inc_var.php';
require '../inc_fct_base.php';
require '../inc_db_connect.php';

$max = 2800 ; // Longueur max du texte (en nombre de caractères) que le visiteur peut poster
$allowedTags = '<em><strong><li><ol><ul><br><br />'; // Balises de style que les visiteurs peuvent employer


//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
// Editer un avis posté par un visiteurs dans le fil de discussion
//iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii

if (empty ($_GET['id_avis']) OR $_GET['id_avis'] == NULL )
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Mauvais paramètre GET<br>
	<a href="avis_list_aprob.php">Retour</a></div>' ;
	exit();
}
else
{
	$id_avis = htmlentities($_GET['id_avis'], ENT_QUOTES);
}

//---------------------------------------------------------
// L'entrée donnée par GET existe-t-elle :

$rep_test_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_avis'");
$test_avis = mysql_fetch_array($rep_test_avis);	
if (empty($test_avis))
{
	echo '<p>&nbsp;</p><p>&nbsp;</p><div class="alerte">Cette entrée n\'existe pas<br>
	<a href="avis_list_aprob.php" >Retour</a></div>' ;
	exit();
}
else
{
	$event_avis = $test_avis['event_avis'];
}

//---------------------------------------------------------
// Si bouton enfoncé, alors lancer l'analyse des données
//---------------------------------------------------------
if (isset($_POST['bouton_enregistrer']) AND ($_POST['bouton_enregistrer'] == 'Enregistrer')) 
{
	//---------------------------------------------------------
	// Verification des données entrées par l'utilateur
	//---------------------------------------------------------
	$rec = ''; 
	
	// ------------------------------------------------------------
	// TEST DU NOM
	if (isset($_POST['nom_avis']) AND ($_POST['nom_avis'] != NULL)) 
	{
		$nom_avis = stripslashes(htmlentities($_POST['nom_avis'], ENT_QUOTES));
	}
	else
	{
		//$rec .= '- Vous devez introduire un nom <br>';
		$error_nom_avis = '<div class="error_form">Vous devez introduire un nom</div>';
	}


	// ------------------------------------------------------------
	//  TEST EMAIL
	if ((isset($_POST['email_avis']) AND (preg_match("!^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$!", $_POST['email_avis']))))
	{
		$email_avis = $_POST['email_avis'];
	}
	else
	{
		$email_avis = '';
		//$rec .= '- Vous devez introduire une adresse e-mail valide <br>';
		$error_email_avis_event = '<div class="error_form">Vous devez introduire une adresse e-mail valide</div>';
	}


	// -----------------------------------------
	// TEST TEXTE AVIS 
	if (empty($_POST['ajaxfilemanager']) OR ($_POST['ajaxfilemanager'] == NULL)) 
	{
		$error_texte_avis = '<div class="error_form">Vous devez introduire votre texte ci dessous</div>';
		$rec .= '- Pas de texte AVIS';
	}
	elseif (strlen($_POST['ajaxfilemanager'])>=$max)
	{
		$texte_avis = str_replace("</p>","<br />",$_POST['ajaxfilemanager']); 
		$texte_avis = stripslashes(strip_tags($texte_avis,$allowedTags));
		//$texte_avis = wordwrap($texte_avis, 50, " ", 1);
		
		$char_en_trop = strlen($texte_avis) - $max ; // Tester longueur de la chaîne de caractères
		$error_texte_avis = '<div class="error_form">
		La taille du texte dépasse la limite autorisée. Il y a ' . $char_en_trop . ' caractères en trop. Veuillez le raccourcir</div>';
		$rec .= '- taille  texte trop grande';
	}
	else
	{
		$texte_avis = str_replace("</p>","<br />",$_POST['ajaxfilemanager']); 
		$texte_avis = stripslashes(strip_tags($texte_avis,$allowedTags));
		//$texte_avis = wordwrap($texte_avis, 50, " ", 1);

		$texte_avis_2_db = addslashes($texte_avis) ;
	}

	
	// ------------------------------------------------------------
	/*// TEST INFORMEZ-MOI
	if (isset($_POST['avis_mailing_adresse']) AND ($_POST['avis_mailing_adresse'] == 'ok')) 
	{
		$avis_mailing_adresse = 'set';
	}
	else
	{
		$avis_mailing_adresse = '';
	}*/


	//---------------------------------------------------------
	// Update des données
	//---------------------------------------------------------
	if ($rec == NULL) // Enregistrement les données dans la DB 
	{
		$approuv_check = mysql_query("UPDATE $table_avis_agenda SET
		nom_avis = '$nom_avis' ,
		texte_avis = '$texte_avis_2_db' ,
		nom_avis = '$nom_avis' 
		WHERE id_avis = '$id_avis' LIMIT 1 ") or die($query_1 . " ----- " . mysql_error());
		
		if ($approuv_check) { echo '<div class="info">Les modifications sont bien enregistrées</div><br>' ; }
		else { echo '<div class="alerte">Erreur ! Les données n\'ont pas été enregistrées</div><br>' ; }
		

		/* // Insérer l'adresse e-mai du visiteur dans la table $table_avis_mailing SI elle n'y est pas déjà
		 if ($avis_mailing_adresse == 'set')
		 {
			$reponse_avis_mailing = mysql_query("SELECT COUNT(*) AS test_exist_email FROM $table_avis_mailing 
			WHERE avis_mailing_adresse = '$email_avis'") or ($query_1 . " --Count-- " . mysql_error());
			$donnees_avis_mailing = mysql_fetch_array($reponse_avis_mailing);
	
			if ($donnees_avis_mailing['test_exist_email'] > 0) 
			{ // Déjà abonné
				echo '<div class="info">Vous recevez déjà les nouveaux avis publiés par e-mail</div>' ;
			}
			else
			{ // Pas encore abonné
				echo '<div class="info">Vous allez recevoir les prochains avis publiés par e-mail</div>' ;
				
				// code référence du USER
				$ref_user_avis_mail = str_shuffle(md5(time())); 
				$ref_user_avis_mail = substr($ref_user_avis_mail, 10, 10);
				mysql_query("INSERT INTO `$table_avis_mailing` ( `id_avis_mailing`, `avis_mailing_adresse`, `ref_avis_mailing` ) 
				VALUES ('', '$email_avis', '$ref_user_avis_mail')") or die($query_1 . " --2-- " . mysql_error());
			}
		} */

	}
	else
	{
		echo '<div class="alerte">Vous devez remplir le formulaire correctement</div><br>' ;
	}
}
else
{
	// ----------------------------------------------------
	// Récupérer les données de la DB (Si on n'a pas appuyé sur le bouton)
	$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE id_avis = '$id_avis'");
	$donnees_avis = mysql_fetch_array($reponse_avis) ;
	
	$event_avis = $donnees_avis ['event_avis'];
	$nom_avis = $donnees_avis ['nom_avis'];
	$texte_avis = stripslashes ($donnees_avis ['texte_avis']);
	$t_stamp_avis = $donnees_avis ['t_stamp_avis'];
	$publier_avis = $donnees_avis ['publier_avis'];
	$email_avis = $donnees_avis ['email_avis'];
	$emailing = $donnees_avis ['emailing']; // pour savoir si l'emailing a déjà été lancé


	// Le visiteur s'est-il abonné à l'emailing ? Utiliser l'adresse e-mail pour la recherche dans la DB $table_avis_mailing	
	$reponse_avis_mailing = mysql_query("SELECT COUNT(*) AS test_exist_email FROM $table_avis_mailing 
	WHERE avis_mailing_adresse = '$email_avis'") or ($query_1 . " --Count-- " . mysql_error());
	$donnees_avis_mailing = mysql_fetch_array($reponse_avis_mailing);
	if ($donnees_avis_mailing['test_exist_email'] > 0) 
	{ // Déjà abonné
		$avis_mailing_adresse = 'set' ;
	}
	else
	{ // Pas encore abonné
		$avis_mailing_adresse = '' ;
	}
}

// Récupération des données concernant ce spectacle
$reponse = mysql_query("SELECT * FROM $table_evenements_agenda WHERE id_event = '$event_avis'");
$donnees_event = mysql_fetch_array($reponse);	

$lieu_event = $donnees_event ['lieu_event'] ;
$nom_event = $donnees_event ['nom_event'] ;

// Recherche nom du lieu culturel
$reponse_lieu = mysql_query("SELECT nom_lieu FROM $table_lieu WHERE id_lieu = '$lieu_event'");
$donnees_lieu = mysql_fetch_array($reponse_lieu);	
$nom_lieu = $donnees_lieu ['nom_lieu'] ;

// Recherche du nombre d'avis concernant le même événement :
$count_avis = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM $table_avis_agenda WHERE event_avis = $event_avis 
AND publier_avis = 'set'") or die($query_1 . " ---sql2-- " . mysql_error());
$nbr_avis = mysql_fetch_array($count_avis);
$total_entrees = $nbr_avis['nbre_entrees'];


// ------------------------------------------------------
// Titre et menu
echo'<h1 align="center">Modification d\'un avis proposé par un visiteur (' . $donnees_event['nom_event'] .')</h1>

<div class="menu_back"><a href="avis_list_aprob.php">Retour &agrave; la liste des avis &agrave; approuver</a> | 
<a href="../../-Detail-agenda-?id_event=' . $donnees_event['id_event'] .'">Page en ligne</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>';
?>

<!-- -----------------------------------------------------------------
// Afficher formulaire
// ----------------------------------------------------------------- -->

</p>
<form name="form1" method="post" action="">
<table width="450" border="1" align="center" cellpadding="5" cellspacing="0" class="data_table" >
<tr>
  <td colspan="2"><?php echo 'ID de cet avis = <strong>' . $id_avis . '</strong><br />
  	Evénement : <strong>' . $nom_event . '</strong><br />
  	Lieu : <strong>' . $nom_lieu . '</strong>.<br />
	Il y a <strong>' . $total_entrees . ' </strong>avis pour cet événement.<br />
	- <a href="#tous_avis_event">Voir les avis déjà postés</a> - <br />' ;
?></td>
</tr>
<tr>
  <td><?php if (isset ($error_nom_avis) AND $error_nom_avis != NULL) {echo $error_nom_avis ; } ?>
  Nom <span class="champ_obligatoire">*</span> :	  </td>
  <td><input name="nom_avis" type="text" id="nom_avis" value="<?php if (isset($nom_avis)){echo $nom_avis;}?>" size="30" maxlength="30"></td>
</tr>
	<tr>
  <td><?php if (isset ($error_email_avis_event) AND $error_email_avis_event != NULL) {echo $error_email_avis_event ; } ?>Adresse e-mail<span class="champ_obligatoire">*</span> : </td>
  <td><input name="email_avis" type="text" id="email_avis" value="<?php if (isset($email_avis)){echo $email_avis;}?>" size="30" maxlength="50"></td>
</tr>
<!--	<tr>
	      <td colspan="2"><label><input type="checkbox" name="avis_mailing_adresse" value="ok" <?php if (isset($avis_mailing_adresse) AND $avis_mailing_adresse == 'set') { echo 'checked="checked"' ; } ?>/>
          Informez-moi par e-mail de l'arriv&eacute;e de nouveaux messages</label></td>
</tr>
-->
<tr>
  <td colspan="2"><?php if (isset ($error_texte_avis) AND $error_texte_avis != NULL) {echo $error_texte_avis ; } ?>
  
  <textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 600px; height: 400px"><?php if (isset($texte_avis)){echo $texte_avis;} ?></textarea></td>
</tr>
<tr>
  <td colspan="2"><div align="center">
  <br />
		  <input name="bouton_enregistrer" type="submit" id="bouton_enregistrer" value="Enregistrer">
  <br />
  </div></td>
</tr>
</table>
</form>

<?php 
// -------------------------------------------------------------
// Affichage de tous les AVIS concernant ce spectacle

$avis_concat = '<a name="tous_avis_event" id="tous_avis_event"></a>
<br /><h3>Voici les avis précédemment postés</h3>' ;

$reponse_avis = mysql_query("SELECT * FROM $table_avis_agenda WHERE event_avis = $event_avis AND publier_avis = 'set' ORDER BY id_avis DESC");
while ($donnees_avis = mysql_fetch_array($reponse_avis))
{
	$avis_concat.= '<b>' . $donnees_avis['nom_avis'] . ' 
	a écrit le ' .date('d/m/Y ', $donnees_avis ['t_stamp_avis']) . ' - id ' . $donnees_avis['id_avis'] . '</b><br />'
	. $donnees_avis['texte_avis'] . '<br /><br />' ;
}

echo $avis_concat ;

//--- mysql_close($db2dlp);
?>
</body>
</html>
