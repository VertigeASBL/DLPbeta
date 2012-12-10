<?	
	/**************************************************************************************/
	/************** GESTION DE L'ACTION D'UNE BANNER (Clic sur une banner) ****************/
	/**************************************************************************************/
	
	require_once("../../admintool/conf.php");
	require_once("../../admintool/fonctions.php");

	
	// CONNEXION MYSQL
	$db_link = mysql_connect($sql_serveur, $sql_user, $sql_passwd); 
	if (! $db_link) {echo "Connexion impossible à la base de données <b>$sql_bdd</b> sur le serveur <b>$sql_serveur</b><br>Vérifiez les paramètres du fichier conf.php"; exit;}

	$action = "";
	//variables pour le texte
	$titre = "";
	$page_gauche = "";
	$page_droite = "";
	
	/*
	//variables formulaire
	reset($_POST);
	while (list($cle, $val) = each($_POST)){
		if (get_magic_quotes_gpc() || is_array($val) ){
			$$cle = $val;
		}else{
			$$cle = addslashes($val);
		}	
	};
	*/
	
	/*
	//affichage de tab POST
	reset($_POST);
	while(list($k,$g) = each($_POST)) {
		echo "indice $k valeur $g <br>"; 
	};
	*/
	
	
	//--- Navigateur MSIE ?
	$iebrowser = strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false ? 0 : 1;

	if (! isset($htmltexte)){
		$htmltexte = '';
	}
	
	if (isset($_POST['tabimgok'])){
		$tabimgok = $_POST['tabimgok'];
	}
	
		
	// on séléctionne la base 
	mysql_select_db($sql_bdd, $db_link); 
	
	$query = info_banner($id_banner);
	$data = mysql_fetch_array($query);
	$nom = $data['nom'];
	$type_action = $data['type_action'];
	$action_banner = $data['action'];
		
	if ($type_action == ""){
		$action = "ajout";
		$action_banner = "http://";
	}else{
		$action = "affich";
	}
	
		/*** AJOUT D'UNE ACTION A UNE BANNER ***/
	if (isset($_POST['b_ajout']) && $action == "ajout"){
		//variables formulaire
		reset($_POST);
		while (list($cle, $val) = each($_POST)){
			if (get_magic_quotes_gpc() || is_array($val) ){
				$$cle = $val;
			}else{
				$$cle = addslashes($val);
			}	
		};
	
		
		$sql_type = "UPDATE t_banners  SET type_action = '$type_action' ";
		$sql_type .= "WHERE id_banner = $id_banner ";
		$query_type = mysql_query($sql_type) or die('Erreur SQL !<br>'.$sql_type.'<br>'.mysql_error());
			
		if ($type_action == "lien"){ //Lien
			$query = update_lien($id_banner,$lien);
		}elseif($type_action == "fichier"){	//Fichier
		
			if ($_FILES['f_fichier']['name'] <> ""){	
								
				//répertoire de destination 
				$fich = $_FILES['f_fichier']['name'];
				$extension = extension($fich);
				
				if ($extension == "swf"){
					$rep_dest = '../../assets/banners/';
				}else{
					$rep_dest = '../../banner/';
				}
				
				//nom de fichier unique --> "A" + id_banner + nom_fichier
				//$fichier_dest = $rep_dest."A".$id_banner."_".$_FILES['f_fichier']['name'];	
				$fichier_dest = $rep_dest."A".$id_banner."_".$fich;	
				$fichier_temp = $_FILES['f_fichier']['tmp_name'];
							
				//upload fichier dans le répertoire "projecteur"
				$alerter = upload_fichier($fichier_temp,$fichier_dest);
				
				//ajout du nom du fichier dans la DB
				aj_fich_action($fichier_dest,$id_banner);
			}
			
		}else{	//TEXTE --> page banner gauche + droite + texte
		
			//Enreg les infos sur titre + page gauche + droite --> table "t_texte_banner"
			ajout_texte ($id_banner,$titre,$page_gauche,$page_droite);
			
		
			//Ajouter le texte (venant de fckEditor)
			$sql = "UPDATE t_banners  SET action = '$htmltexte' ";
			$sql .= "WHERE id_banner = $id_banner ";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
				
								
			//--- Supprimer les images et embeds inutiles
			if (isset($tabimgok)){
				$sql = " FROM cmsmedia WHERE mcont = 3 AND mfich NOT IN (".implode(',', $tabimgok).')';
				$sql .= " AND mpage = $id_banner ";
			}else{
				$sql = " FROM cmsmedia WHERE mcont = 3 ";
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
		
		$query_info = info_banner($id_banner);
		$data = mysql_fetch_array($query_info);
		$circuit = $data['circuit'];
		
		header("location: banners_contenu.php?id=$id&circuit=$circuit&site=$site"); 
		
	}//Fin ajout
	
	
	
		/*** AFFICHAGE DES INFOS SUR L'ACTION D'UNE BANNER ***/
	if ($action == "affich"){
		$query = info_banner($id_banner);
				
		$data = mysql_fetch_array($query);
		$type_action = $data['type_action'];
				
		if ($type_action == "texte"){
			$htmltexte = $data['action'];
		}else{
			$action_banner = $data['action'];
		}
		
		$query_txt = info_texte($id_banner);
		$data_txt = mysql_fetch_array($query_txt);
		
		$titre = $data_txt['titre'];
		$page_gauche = $data_txt['page_gauche'];
		$page_droite = $data_txt['page_droite'];
	}
	
	
		/*** MODIFICATION DES INFOS ***/
	
	if (isset($_POST['b_modif']) ){
				
		switch ($type_action){

		//Modif d'un fichier			
		case "fichier" :	
				
		   if ($_FILES['f_fichier']['name'] <> ""){	
							
			//répertoire de destination 
			$fich = $_FILES['f_fichier']['name'];
			$extension = extension($fich);
			if ($extension == "swf"){
				$rep_dest = '../../assets/banners/';
			}else{
				$rep_dest = '../../banner/';
			}
						
			//effacer l'ancien fichier
			$query_action = info_banner($id_banner);
			$data_action = mysql_fetch_array($query_action);
			$action_banner = $data_action['action'];	
						
			if ($action_banner <> ""){	
				//suppression physique du fichier
				suppr_fichier($rep_dest,$action_banner);
			}
			
			//nom de fichier unique --> "A" + id_banner + nom_fichier
			//$fichier_dest = $rep_dest."A".$id_banner."_".$_FILES['f_fichier']['name'];	
			$fichier_dest = $rep_dest."A".$id_banner."_".$fich;	
			$fichier_temp = $_FILES['f_fichier']['tmp_name'];
						
			//upload fichier dans le répertoire "projecteur"
			$alerter = upload_fichier($fichier_temp,$fichier_dest);
			
			//ajout du nom du fichier dans la DB
			aj_fich_action($fichier_dest,$id_banner);
		   }
		   
		   break;
		
		//Modif d'un lien
		case "lien" :
			$query = update_lien($id_banner,$lien);
			break;
		
		//Modif du texte
		case "texte" :
			
			//variables formulaire
			reset($_POST);
			while (list($cle, $val) = each($_POST)){
				if (get_magic_quotes_gpc() || is_array($val) ){
					$$cle = $val;
				}else{
					$$cle = addslashes($val);
				}	
			};
				
			//Modif du texte (venant de fckEditor)	
			if (isset($_POST['htmltexte']))
				$htmltexte = $_POST['htmltexte'];
			
			$sql = "UPDATE t_banners  SET action = '$htmltexte' ";
			$sql .= "WHERE id_banner = $id_banner ";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
			
								
			//--- Supprimer les images et embeds inutiles
			if (isset($tabimgok)){
				$sql = " FROM cmsmedia WHERE mcont = 3 AND mfich NOT IN (".implode(',', $tabimgok).')';
				$sql .= " AND mpage = $id_banner ";
			}else{
				$sql = " FROM cmsmedia WHERE mcont = 3 ";
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
			
			
			//modif dans la table "t_texte_banner"
			update_texte($id_banner,$titre,$page_gauche,$page_droite);
			
			break;
		}
		
		
		$query_info = info_banner($id_banner);
		$data = mysql_fetch_array($query_info);
		$circuit = $data['circuit'];
		
		header("location: banners_contenu.php?id=$id&circuit=$circuit&site=$site"); 
		
	}// Fin modif
	
	
		/*** SUPPRESSION ACTION ***/
	if (isset($_POST['b_suppr']) ){
		$query = info_banner($id_banner);
		$data = mysql_fetch_array($query);
		$type_action = $data['type_action'];
		$action_banner = $data['action'];
		$circuit = $data['circuit'];
				
		if ($type_action == "fichier"){	//suppresssion physique du fichier 
					
			//répertoire de destination 
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
			
		}elseif ($type_action == "texte"){ 
			/* suppression des données liées à fckEditor */
			//suppression physique des fichiers
			$sql = " FROM cmsmedia WHERE mcont = 3 ";
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
		
			$sql = "DELETE FROM cmsmedia WHERE mcont = 3 ";
			$sql .= " AND mpage = $id_banner ";
			
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
			
			//suppression de la table "t_texte_banner"
			suppr_texte($id_banner);
		}
		
		//suppression de la table "t_banners"
		$sql_del = 'UPDATE t_banners B SET B.type_action = NULL , B.action = NULL '; 
		$sql_del .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql_del) or die('Erreur SQL !<br>'.$sql_del.'<br>'.mysql_error());	
		
			
		header("location: banners_contenu.php?id=$id&circuit=$circuit&site=$site"); 
		
	} //Fin suppression
	
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
	
	var bouton = radioVal(document.fo.type_action);
	
	var type_act = '<? echo $type_action; ?>';
	
	if (bouton == "texte" || type_act == "texte" ){
	//if (bouton == "texte" ){
		
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
	 	case "t_lien":
	 		document.getElementById("t_fichier").style.visibility = "hidden";
	 		document.getElementById("t_fichier").style.display = "none";
	 		document.getElementById("t_texte").style.visibility = "hidden";
	 		document.getElementById("t_texte").style.display = "none";
	 		break;
	 	case "t_fichier":
	 		document.getElementById("t_texte").style.visibility = "hidden";
	 		document.getElementById("t_texte").style.display = "none";
	 		document.getElementById("t_lien").style.visibility = "hidden";
	 		document.getElementById("t_lien").style.display = "none";
	 		break;
	 	default:
	 		document.getElementById("t_lien").style.visibility = "hidden";
	 		document.getElementById("t_lien").style.display = "none";
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
					Action pour la banner <?= $nom ?>
				</p>
					
			
				<!-- FORMULAIRE ACTION BANNER (Quand in click sur une banner) -->
			
			<form  name="fo" method="post" enctype="multipart/form-data" action="action_banner.php">
			<input type="hidden" name="id" value="<? echo $id ?>"/>
			<input type="hidden" name="id_banner" value="<? echo $id_banner ?>"/>
			<input type="hidden" name="action" value="<? echo $action; ?>"/>
			<input type="hidden" name="type_action" value="<? echo $type_action; ?>"/>
			<input type="hidden" name="site" value="<? echo $site; ?>"/>
			
			
			<table border="0" cellspacing="0" cellpadding="4" width="100%" align="center">
			
			<tr><td align="center">
			
			
						<!-- Boutons radios pour le type d'action d'une banner -->
									
			<blockquote>
			<div id="radios" align="center" class="normal">	
			<table width="30%" align="center"><tr class="normal"><td align="center">			
									
				<tr class="normal">
								
				<input type="radio" name="type_action" id="rad_lien" value="lien" onClick="show_hide('t_lien')" class="checkbox"/>
				<label class='label1' label for='rad_lien'>Lien</label>
						
				
				</tr>
			</td></tr></table>
			</div>
			</blockquote>
			
					<!-- Lien -->
			<blockquote>
			<div id="t_lien" style="visibility:hidden; display:none;">
			<table width='50%' border='0' cellspacing='0' cellpadding='4' align='center'><tr>	
			  <tr>
				<td class="normal">  Lien </td>
				<td colspan="2" class="normal">
					<input type="text" size="30" name="lien" value="<?echo $action_banner; ?>" class="champs2"/>
				</td>
			  </tr>
			</tr></table>
			</div>
			</blockquote>
			
			
					<!-- Fichier -->
			<blockquote>
			<div id="t_fichier" style="visibility:hidden; display:none;">
			<table width='50%' border='0' cellspacing='0' cellpadding='4' align='center'><tr>
			
				<? if($action == "affich" && $type_action == "fichier"){ ?>
					<tr>
						<td class="normal">
							Fichier  <?= $action_banner ?>
						</td>
					<tr>
				<? } ?>
				
				<tr>
				<td class="normal">Fichier (php,...): </td>
				<td class="normal">
					<input type="file" name="f_fichier" class="champs2">
				</td>
			  </tr>
			</tr></table>
			</div>
			</blockquote>
			
			
					<!-- Texte -->
			<blockquote>
			<div id="t_texte" style="visibility:hidden; display:none;">
			<table width='50%' border='0' cellspacing='0' cellpadding='4' align='center'><tr>
			
				<tr class="normal">
				<td> Titre </td>
				<td class="normal">
					<input type="text" name="titre" value="<?echo $titre; ?>" size='30' class="champs2"/>
				</td>			
				</tr>
				<tr class="normal">
					<td>Page de gauche </td>
					<td>
					<select name="page_gauche" class="liste">
						<option value="<?echo $page_gauche; ?>" selected="selected"><?echo $page_gauche; ?></option>\n";
						<option value="page_gauche_abonne.php">page_gauche_abonne.php</option>
						<option value="page_gauche_annonce.php">page_gauche_annonce.php</option>
						<option value="page_gauche_home.php">page_gauche_home.php</option>
					</select>
					</td>
				</tr>
				<tr class="normal">
					<td>Page de droite </td>
					<td>
					<select name="page_droite" class="liste">
						<option value="<?echo $page_droite; ?>" selected="selected"><?echo $page_droite; ?></option>\n";
						<option value="page_droite_casting.php">page_droite_casting.php</option>
						<option value="page_droite_comedien.php">page_droite_comedien.php</option>
						<option value="page_droite_compagnie.php">page_droite_compagnie.php</option>
						<option value="page_droite_home.php">page_droite_home.php</option>
						<option value="page_droite_metier.php">page_droite_metier.php</option>
					</select>
					</td>
				</tr>
				
			
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
					   //mcont=1 pour invité/proj; mcont=3 pour action banners
					echo 'oFCKeditor.mcont = "3";',"\n";
					   
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
			
			
					
			
			<tr align="center">
			
				<? if ($action == "ajout"){ ?>
				  <td> 
					<input type="submit" name="b_ajout" value="Ajouter" class="bouton" onClick="enregistrer()"/>
			   	  </td>
			   	<? }else{ ?>
			   	  <td> 
					<input type="submit" name="b_modif" value="Modifier" class="bouton" onClick="enregistrer()"/>
					<input type="submit" name="b_suppr" value="Supprimer" class="bouton" onClick="enregistrer()"/>
			   	  </td>
			   	 <? } ?>
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
	if ( $action == "affich" ){
		$query = info_banner($id_banner);

		$data = mysql_fetch_array($query);
		$type_action = $data['type_action'];
			
		switch ($type_action){
			case "lien" :
				$zone = "t_lien";
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