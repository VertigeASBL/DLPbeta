<?
	require_once("../../admintool/conf.php");
	require_once("../../admintool/fonctions.php");
	
	// CONNEXION MYSQL
	$db_link = mysql_connect($sql_serveur, $sql_user, $sql_passwd); 
	if (! $db_link) {echo "Connexion impossible à la base de données <b>$sql_bdd</b> sur le serveur <b>$sql_serveur</b><br>Vérifiez les paramètres du fichier conf.php"; exit;}
	
	// on séléctionne la base 
	mysql_select_db($sql_bdd, $db_link); 
	
	$nom = "";
	
	if (isset($_GET['circuit'])){
		$circuit = $_GET['circuit'];
	}
	if (!isset($page_chemin)){
		$page_chemin = "";
	}	
	
	//Rendre les params valides
	if (! isset($type_contenu))
		$type_contenu = "";
	if (! isset($id_banner))
		$id_banner = "";	
	if (! isset($taille_flash))
		$taille_flash = "";
	if (! isset($enregister))
		$enregister = "";	
	if (! isset($action))
		$action = "";		
	if (! isset($nom))
		$nom = "";				
	
	//variables formulaire
	reset($_POST);
	while (list($cle, $val) = each($_POST)){
		if (get_magic_quotes_gpc() || is_array($val) ){
			$$cle = $val;
		}else{
			$$cle = addslashes($val);
		}
		
	};
	
	/*
	//affichage de tab POST
	reset($_POST);
	while(list($k,$g) = each($_POST)) {
		echo "indice $k valeur $g <br>"; 
	};
	*/
	
	
	//--- Navigateur MSIE ?
	$iebrowser = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false ? 0 : 1;
	

	if (isset($_POST['tabimgok'])){
		$tabimgok = $_POST['tabimgok'];
	}
	
	if (! isset($htmltexte)){
		$htmltexte = '';
	}
		
	if (isset($_GET['action'])){
		$action = $_GET['action'];
	}
	
	if (isset($_POST['action'])){
		$action = $_POST['action'];
	}
	
	
	// on séléctionne la base 
	mysql_select_db($sql_bdd, $db_link); 
	
	$sql = "SELECT * FROM t_banners";
	
	
	$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
	$nb_enreg = mysql_num_rows($query);
		
	if ($nb_enreg == 0){
		$action = "ajout";
	}	
	
		 /*** AJOUT D'UNE BANNER ***/
	if (isset($_POST['enregister']) && $action == "ajout"){
		
		//récup du no de pos de la derniere banner d'un circuit
		$max_pos = max_position($circuit,$emplacement);
		
		//ajout dans la table "t_banners"
		$sql_adr = "INSERT INTO t_banners (nom,circuit,emplacement,site,type_contenu,taille_flash)";	
	  	$sql_adr .= "VALUES ('$nom','$circuit','$emplacement','$site','$type_contenu','$taille_flash')";
		$query = mysql_query($sql_adr) or die('Erreur SQL !<br>'.$sql_adr.'<br>'.mysql_error());
		
		
		//récup de l'identifiant auto-incr lors de l'insertion 
		$id_banner = mysql_insert_id($db_link);
		
		//si upload fichier --> récup fichier (contenu)
		if ($type_contenu == "photo" || $type_contenu == "fichier"){
			if ($type_contenu == "photo"){
				$f_fich = 'f_photo';
			}else{
				$f_fich = 'f_fichier';
			}
		
			$fich = $_FILES[$f_fich]['name'];
			if ($fich <> ""){	
					
				//répertoire de destination 
				$extension = extension($fich);
				if ($extension == "swf"){ //fichier flash
					$rep_dest = '../../assets/banners/';
				}else{
					$rep_dest = '../../banner/';
				}
				
				//nom de fichier unique --> id_banner + nom_fichier
				//$fichier_dest = $rep_dest.$id_banner."_".$_FILES[$f_fich]['name'];	
				$fichier_dest = $rep_dest.$id_banner."_".$fich;	
				$fichier_temp = $_FILES[$f_fich]['tmp_name'];
							
				//upload fichier dans le répertoire "projecteur"
				$alerter = upload_fichier($fichier_temp,$fichier_dest);
				
				//ajout du nom du fichier dans la DB
				aj_fich_banner($fichier_dest,$id_banner);
			}
		}else{ //Ajouter le texte (venant de fckEditor)
			$sql = "UPDATE t_banners  SET contenu = '$htmltexte' ";
			$sql .= "WHERE id_banner = $id_banner ";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
			
								
			//--- Supprimer les images et embeds inutiles
			if (isset($tabimgok)){
				$sql = " FROM cmsmedia WHERE mcont = 2 AND mfich NOT IN (".implode(',', $tabimgok).')';
				$sql .= " AND mpage = $id_banner ";
			}else{
				$sql = " FROM cmsmedia WHERE mcont = 2 ";
				$sql .= " AND mpage = $id_banner ";
				
			}
			
			$req = mysql_query('SELECT mnom'.$sql) or die('Erreur SQL !<br>SELECT mnom'.$sql.'<br>'.mysql_error());
	
			while ($data = mysql_fetch_array($req)) {
				$k = '../media/'.$data['mnom'];
				if (! @unlink($k)) {
					$alerter .= '\n'."Il est impossible de supprimer le fichier $k";
					$okdirig = false;
				}
			}
			$req = mysql_query('DELETE'.$sql) or die('Erreur SQL !<br>DELETE'.$sql.'<br>'.mysql_error());
		
		
		}	
		
			
		//décaler les positions
		if ($position <= $max_pos){
			decale_pos_ajout($position,$circuit,$emplacement);
		}		
			
		//ajouter le n° de position
		$sql = "UPDATE t_banners  SET position = $position ";
		$sql .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
		
			
		header("location: banners_contenu.php?id=$id&circuit=$circuit&site=$site"); 
	} //Fin ajout banner
	
	
		
		/*** AFFICHAGE DONNEES D'UNE BANNER ***/
	if (isset($_GET['action'])){
		if ($_GET['action'] == "modif"){	
			
		   if (isset($_GET['id_banner'])){
			$id_banner = $_GET['id_banner'];
			
			//récup des infos de la banner 
			$query = info_banner($id_banner);
					
			$data = mysql_fetch_array($query);
			$nom = $data['nom'];
			$position = $data['position'];
			$taille_flash = $data['taille_flash'];
			$type_contenu = $data['type_contenu'];
			if ($type_contenu == "texte"){
				$htmltexte = $data['contenu'];
			}else{
				$contenu = $data['contenu'];
			}
			$emplacement = $data['emplacement'];
					
		   }
		}
	}
	
	
	if (isset($_GET['id_banner']))
		$id_banner = $_GET['id_banner'];
	elseif (isset($_POST['id_banner']))
		$id_banner = $_POST['id_banner'];
	
	
		//MODIF DES INFOS DE LA BANNER
	if (isset($_POST['enregister']) && $action=="modif"){
					
		if (isset($id_banner)){
					
		//$sql = "UPDATE t_banners SET  nom='$nom', position='$position' ";
		$sql = "UPDATE t_banners SET  nom='$nom', taille_flash='$taille_flash' ";
		$sql .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		
		//si upload fichier --> récup fichier (contenu)
		if ($type_contenu == "photo" || $type_contenu == "fichier"){
			if ($type_contenu == "photo"){
				$f_fich = 'f_photo';
			}else{
				$f_fich = 'f_fichier';
			}
			
			$fich = $_FILES[$f_fich]['name'];
			
			if ($fich <> ""){	
					
				//répertoire de destination 
				$extension = extension($fich);
				if ($extension == "swf"){
					$rep_dest = '../../assets/banners/';
				}else{
					$rep_dest = '../../banner/';
				}
				
				//effacer l'ancien fichier
				
				$query_banner = info_banner($id_banner);
				$data_banner = mysql_fetch_array($query_banner);
				$contenu = $data_banner['contenu'];	
				
				if ($contenu <> ""){	
					//suppression physique du fichier
					suppr_fichier($rep_dest,$contenu);
				}
				
				//nom de fichier unique --> id_banner + nom_fichier
				//$fichier_dest = $rep_dest.$id_banner."_".$_FILES[$f_fich]['name'];	
				$fichier_dest = $rep_dest.$id_banner."_".$fich;	
				$fichier_temp = $_FILES[$f_fich]['tmp_name'];
							
				//upload fichier dans le répertoire "banner"
				$alerter = upload_fichier($fichier_temp,$fichier_dest);
				
				//ajout du nom du fichier dans la DB
				aj_fich_banner($fichier_dest,$id_banner);
			}
		}else{ //Ajouter le texte (venant de fckEditor)
			$sql = "UPDATE t_banners  SET contenu = '$htmltexte' ";
			$sql .= "WHERE id_banner = $id_banner ";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
			
			
				//--- Supprimer les images et embeds inutiles
			if (isset($tabimgok)){
				$sql = " FROM cmsmedia WHERE mcont = 2 AND mfich NOT IN (".implode(',', $tabimgok).')';
				$sql .= " AND mpage = $id_banner";
			}else{
				$sql = " FROM cmsmedia WHERE mcont = 2 ";
				$sql .= " AND mpage = $id_banner";
			}
			$req = mysql_query('SELECT mnom'.$sql) or die('Erreur SQL !<br>SELECT mnom'.$sql.'<br>'.mysql_error());
	
			while ($data = mysql_fetch_array($req)) {
				$k = '../media/'.$data['mnom'];
				if (! @unlink($k)) {
					$alerter .= '\n'."Il est impossible de supprimer le fichier $k";
					$okdirig = false;
				}
			}
			$req = mysql_query('DELETE'.$sql) or die('Erreur SQL !<br>DELETE'.$sql.'<br>'.mysql_error());
			}
		
		}	
		
		
			//gestion position
		$query_banr = info_banner($id_banner);
		$data_banr = mysql_fetch_array($query_banr);
		$pos_actu = $data_banr['position'];
		//echo "position: $position <br>";
		//" "pos_actu: $pos_actu";
		
		if ($pos_actu < $position){
			$sql = "UPDATE t_banners SET position = position - 1 ";
			$sql .= "WHERE position > $pos_actu ";
			$sql .= "AND position <= $position ";
			$sql .= "AND circuit = '$circuit' AND emplacement = '$emplacement'";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
		}else{
			$sql = "UPDATE t_banners SET position = position +1 ";
			$sql .= "WHERE position < $pos_actu ";
			$sql .= "AND position >= $position ";
			$sql .= "AND circuit = '$circuit' AND emplacement = '$emplacement'";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
		}
		
		
				
		//ajouter le n° de position
		$sql = "UPDATE t_banners  SET position = $position ";
		$sql .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
		
		
			
		header("location: banners_contenu.php?id=$id&circuit=$circuit&site=$site"); 
				
	}
	
	
		//SUPPRESSION D'UNE BANNER
	if ($action == "supprimer"){
		if (isset($_GET['id_banner'])){
			$id_banner = $_GET['id_banner'];
					
			$query_ban = info_banner($id_banner);
			$data_ban = mysql_fetch_array($query_ban);
			$type_contenu = $data_ban['type_contenu'];
			$contenu = $data_ban['contenu'];	
			$type_action = $data_ban['type_action'];
			$action_banner = $data_ban['action'];	
			$circuit = $data_ban['circuit'];	
			$position = $data_ban['position'];	
			$emplacement = $data_ban['emplacement'];	
			
						
			//suppresssion physique du fichier
			if ($type_contenu == "photo" || $type_contenu == "fichier"){
				if ($type_contenu == "photo"){
					$f_fich = 'f_photo';
				}else{
					$f_fich = 'f_fichier';
				}
				
				//répertoire de destination
				$extension = extension($contenu);
				if ($extension == "swf"){
					$rep_dest = '../../assets/banners/';
				}else{
					$rep_dest = '../../banner/';
				}
										
				if ($contenu <> ""){	
					//suppression physique du fichier
					suppr_fichier($rep_dest,$contenu);
				}
							
			}else{
					/* suppression des données liées à fckEditor */
				//suppression physique des fichiers
				$sql = " FROM cmsmedia WHERE mcont = 2 ";
				$sql .= " AND mpage = $id_banner ";
				$query = mysql_query('SELECT *'.$sql) or die('Erreur SQL !<br>SELECT mnom'.$sql.'<br>'.mysql_error());
				
						
				while ($data = mysql_fetch_array($query)) {
					$k = '../media/'.$data['mnom'];
					if (! @unlink($k)) {
						$alerter .= '\n'."Il est impossible de supprimer le fichier $k";
						$okdirig = false;
					}
				}
				$query = mysql_query('DELETE'.$sql) or die('Erreur SQL !<br>DELETE'.$sql.'<br>'.mysql_error());		
			
				$sql = "DELETE FROM cmsmedia WHERE mcont = 2 ";
				$sql .= " AND mpage = $id_banner ";
				
				$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
						
			}
			
			//suppression du fichier lié à l'action
			if ($type_action == "fichier"){
				$extension = extension($action_banner);
				if ($extension == "swf"){
					$rep_dest = '../../assets/banners/';
				}else{
					$rep_dest = '../../banner/';
				}
									
				if ($action_banner <> ""){	
					//suppression physique du fichier
					suppr_fichier($rep_dest,$action_banner);
				}
			}
					
			//supression de la table "t_banners"	
			$sql = "DELETE FROM t_banners WHERE id_banner = $id_banner ";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
			
			//récup du no de pos de la derniere banner d'un circuit
			$max_pos = max_position($circuit,$emplacement);
			
			//si pas dernière banner du circuit --> décaler
			if ($position <> $max_pos){
				$sql = "UPDATE t_banners SET position = position - 1 ";
				$sql .= "WHERE position >= $position ";
				$sql .= "AND circuit = '$circuit' AND emplacement = '$emplacement'";
				$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
			}
		}
				
		header("location: banners_contenu.php?id=$id&circuit=$circuit&site=$site"); 
		
	}//Fin suppression banner
	
	// Deconnexion Mysql	
	//mysql_close($db_link);

?>
<html>
<head>
	<title>COMEDIEN.BE</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../comedien.css" rel="stylesheet" type="text/css">

	<script language="JavaScript">
	
	//fonction qui récupère la valeur du boutton radio sélecionné
	function radioVal (radiobutton){ 
	        var returnValue = ""; 
	        if (radiobutton.length == 1){ 
	            returnValue = radiobutton.value; 
	        } else { 
	            for (i=0;i<radiobutton.length;i++){ 
	                if (radiobutton[i].checked==true) { 
	                    returnValue=radiobutton[i].value; 
	                } 
	            } 
	        } 
	        return returnValue; 
    	}
	    	

	//gestion fckEditor
	function enregistrer() {
		
	var bouton = radioVal(document.fo.type_contenu);
	
	var type_contenu = '<? echo $type_contenu; ?>';
	
	if (bouton == "texte" || type_contenu == "texte" ){
	//if (bouton == "texte"){

	 if (<? echo $iebrowser; ?> == 1) {
		//--- Copier le contenu dans SPAN(msietexte) sans "http://host..."
		oFCKeditor.recupererEditeur();
		
		//----- Transmettre par hidden les ID des IMG
		msietxt = document.getElementById("msietexte");
		tab = msietxt.getElementsByTagName("IMG");

		for (k = 0; k < tab.length; k++) {
			g = tab[k].getAttribute("id", 2);
			if (g != "" && ! isNaN(g)) {
				elm = document.createElement("input");

				elm.setAttribute("type", "hidden");
				elm.setAttribute("value", g);
				elm.setAttribute("name", "tabimgok[]");
				
				//alert (parent.document.fo);
				
				//parent.document.fo.appendChild(elm);
				document.fo.appendChild(elm);
			}
		}
		
		//--- Remplacer IMG(%MULTI%) par EMBED
		oFCKeditor.cloturerEditeur();

		document.fo.htmltexte.value = msietxt.innerHTML;
		msietxt.innerHTML = '';
	}
      }
      
      document.fo.submit();
      }
		
		
		//montrer-cacher une portion de doc
	var dom = (document.getElementById && !document.all)? 1: 0;

	function show_hide(the_id)
	{
	 
	 var obj = (dom)? document.getElementById(the_id): document.all[the_id];
	 
	 //rendre les autres zones invisible (une seule zone visible à la fois)
	 switch (the_id){
	 	case "t_photo":
	 		document.getElementById("t_fichier").style.visibility = "hidden";
	 		document.getElementById("t_fichier").style.display = "none";
	 		document.getElementById("t_texte").style.visibility = "hidden";
	 		document.getElementById("t_texte").style.display = "none";
	 		break;
	 	case "t_fichier":
	 		document.getElementById("t_texte").style.visibility = "hidden";
	 		document.getElementById("t_texte").style.display = "none";
	 		document.getElementById("t_photo").style.visibility = "hidden";
	 		document.getElementById("t_photo").style.display = "none";
	 		break;
	 	default:
	 		document.getElementById("t_photo").style.visibility = "hidden";
	 		document.getElementById("t_photo").style.display = "none";
	 		document.getElementById("t_fichier").style.visibility = "hidden";
	 		document.getElementById("t_fichier").style.display = "none";
	 		break;
	 }
	 
	 //afficher la zone sélectionnée
	 obj.style.visibility = "visible";
	 obj.style.display = "block";  
	}
	
	</script>
	
	<script type="text/javascript" src="../../editor/fckeditor.js"></script>
</head>
		

<body bgcolor="#484444" link="#700B0B" vlink="#700B0B" alink="#700B0B" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr valign="top">
	    <td width="17" align="center"><img src="../../assets/spacer.gif" width="17" height="100"></td> 
      <td align="center"> <table width="700" border="0" bgcolor="#FFFFFF" align="center" cellpadding="0" cellspacing="5">
          <tr> 
            <td valign="top" align="left">
			  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
		      <tr> 
		        <td colspan="3"><img src="../../assets/popup/gris_haut.gif" width="690" height="3"></td>
		      </tr>
                <tr> 
                  <td width="2" bgcolor="#000000"><img src="../../assets/spacer.gif" width="2" height="5"></td>
                  <td width="686" align="left" valign="top"> <table width="100%" border="0" cellpadding="0" cellspacing="5" class="normal">
                      <tr> 
                        <td align="left" valign="top">
				<p class="normal">
					<img alt="" src="../../assets/puce_bordeau.gif" width="7" height="7" align="absmiddle">
					Bannière de <?= $circuit ?>
				</p>
					
			
				<!-- FORMULAIRE BANNER -->
			
			<form  name="fo" method="post" enctype="multipart/form-data" action="banners.php">
			<input type="hidden" name="id" value="<? echo $id ?>"/>
			<input type="hidden" name="id_banner" value="<? echo $id_banner ?>"/>
			<input type="hidden" name="circuit" value="<? echo $circuit ?>"/>
			<input type="hidden" name="action" value="<? echo $action; ?>"/>
			<input type="hidden" name="type_contenu" value="<? echo $type_contenu; ?>"/>
			<input type="hidden" name="emplacement" value="<? echo $emplacement; ?>"/>
			<input type="hidden" name="site" value="<? echo $site; ?>"/>			
			
			
			<table border="0" cellspacing="0" cellpadding="4" width="100%" align="center">
			
			<tr><td align="center">
			
			
					<!-- Boutons radios pour le type de contenu d'une banner -->
									
			<blockquote>
			<div id="radios" align="center" class="normal">	
			<table width="30%" align="center"><tr class="normal"><td align="center">			
									
				<tr class="normal">
								
				<input type="radio" name="type_contenu" id="rad_photo" value="photo" onClick="show_hide('t_photo')" class="checkbox"/>
				<label for='rad_photo'>Photo</label>
						
						
				<input type="radio" name="type_contenu" id="rad_fich" value="fichier" onClick="show_hide('t_fichier')" class="checkbox"/>
				<label for='rad_fich'>Fichier</label>
				
				</tr>
			</td></tr></table>
			</div>
			</blockquote>
			
					<!-- Photo -->
			<blockquote>
			<div id="t_photo" style="visibility:hidden; display:none;">
			<table width='50%' border='0' cellspacing='0' cellpadding='4' align='center'><tr>
			
				<!-- Afficher la photo -->
			<? if($action == "modif" && $type_contenu == "photo"){ ?>
				<tr>
					<td class="normal">
						Photo : <br/>
						<img src="../../banner/<?= $contenu ?>" width="153"  border="0"></a><br>		
					</td>
				<tr>
			<? } ?>
			
			  <tr>
				<td class="normal">Photo: </td>
				<td class="normal">
					<input type="file" name="f_photo" class="champs2">
				</td>
			  </tr>
			</tr></table>
			</div>
			</blockquote>
			
			
					<!-- Fichier -->
			<blockquote>
			<div id="t_fichier" style="visibility:hidden; display:none;">
			<table width='50%' border='0' cellspacing='0' cellpadding='4' align='center'><tr>
			
				<? if($action == "modif" && $type_contenu == "fichier"){ ?>
					<tr>
						<td class="normal">
							Fichier : <?= $contenu ?>
						</td>
					<tr>
				<? } ?>
				
				<tr>
				<td class="normal">Fichier (Flash): </td>
				<td class="normal">
					<input type="file" name="f_fichier" class="champs2">
				</td>
				
			  	</tr>
			  	
			  	<tr class="normal">	
				<td>Taille </td>
				<td>
				<select name="taille_flash" class="liste">
					<option value="<?echo $taille_flash; ?>" selected="selected"><?echo $taille_flash; ?></option>\n";
					<option>petit</option>
					<option>grand</option>
				</select>
				</td>
				
				</tr>
			</tr></table>
			</div>
			</blockquote>
			
			
					<!-- Texte -->
			<blockquote>
			<div id="t_texte" style="visibility:hidden; display:none;">
			<table width='50%' border='0' cellspacing='0' cellpadding='4' align='center'><tr>
				
				<tr>	
					<td colspan="4" class="normal" align="center" >
					<br/> Texte: <br/>
					<?
																											
					echo '<script type="text/javascript">',"\n";	
									
					echo 'var oFCKeditor = new FCKeditor();',"\n";
					echo 'oFCKeditor.BasePath = "../editor/";',"\n";
					
					
					echo 'oFCKeditor.outils = "CMS";',"\n";
					echo 'oFCKeditor.Height = "400";',"\n";
					echo 'oFCKeditor.Width = "480";',"\n";
							
					echo 'oFCKeditor.mpage = "',$id_banner,'";',"\n";
					//echo 'oFCKeditor.mpage = "1";',"\n";
					   //mcont=1 pour invité/proj; mcont=2 pour banners
					echo 'oFCKeditor.mcont = "2";',"\n";
					   
					echo 'oFCKeditor.Create() ;',"\n";
					echo '</script>';
					
	
					echo '<span id="msietexte" style="display:none">';
					echo stripslashes($htmltexte);
					echo '</span>';
					echo '<input type="hidden" name="htmltexte" value="" />';
					
			
					?>
	
					</td>
					
				</tr>
			</tr></table>
			</div>
			</blockquote>
			
					<!-- Partie commune -->			
			<table align="center" border="0" cellspacing="0" cellpadding="4" width="50%" > 		
	
			  <tr >			
				<td width="30%" >
				  <tr>	
				  	<td class="normal"> Nom </td>
					<td colspan="2" class="normal">
						<input type="text" name="nom" value="<?echo $nom; ?>" class="champs2"/>
					</td>
				  </tr>
				</td>
			  </tr>	
			  
			  		
			  <tr class="normal">
				<td>Position </td>
				<td>
				 <select name="position" size="1" class="liste">
					<?		
					$temp = '';
					$max_pos = max_position($circuit,$emplacement);
					$temp = '';
					for($i=1; $i<=$max_pos+1; ++$i) {
						if ($action == "modif"){
							if($i == $position)
								$temp .= "<option value=\"$i\" selected=\"selected\">$position</option>\n";
							else
								$temp .= "<option value=\"$i\">$i</option>\n";
						}elseif ($action == "ajout"){
							if($i == $max_pos+1)
								$temp .= "<option value=\"$i\" selected=\"selected\">$i</option>\n";
							else{
								$temp .= "<option value=\"$i\">$i</option>\n";
							}
						}
					}
					echo $temp;
					?>
				</select>&nbsp;
			   </td>				
			  </tr>

	
			</table>
				
					
			
			<tr align="center">
				<td> 
					<input type="submit" name="enregister" value="Enregister" class="bouton" onClick="enregistrer()"/>
				</td>
			</tr>
					
			
		</td></tr></table>
			
		</form>
			
			<!-- FIN FORMULAIRE BANNER -->	
						
						
			
                          </td>
                      </tr>
                    </table></td>
                  <td width="2" bgcolor="#000000"><img src="../../assets/spacer.gif" width="2" height="5"></td>
                </tr>
                <tr> 
                  <td colspan="4"><img src="../../assets/popup/gris_bas.gif" width="690" height="3"></td>
                </tr>
              </table></tr>
        </table></td>
    </tr>
  </table>
 
</body>
</html>


	<!-- Affichage d'une zone en fonction du type de banner -->

<?	
	if (isset($_GET['id_banner'])){
		$query = info_banner($id_banner);

		$data = mysql_fetch_array($query);
		$type_contenu = $data['type_contenu'];
			
		switch ($type_contenu){
			case "photo" :
				$zone = "t_photo";
				break;
			case "fichier":
				$zone = "t_fichier";
				break;
			default:
				$zone = "t_texte";
				break;
		}
				
?>

	<script language="javascript">	
		//rendre les radios buttons invisibles
		document.getElementById("radios").style.visibility = "hidden";
		document.getElementById("radios").style.display = "none";
		  
		show_hide("<?=$zone?>");
	</script>
	
<?
} //fin if affichage zone
?>