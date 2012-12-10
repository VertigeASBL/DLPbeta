<?
	/*************************************************************************************************/
	/******** FONCTION POUR L'AFFICHAGE DES BANNERS (appellé dans les pages gauche et droite)*********/
	/*************************************************************************************************/

	$taille = "";
	$hauteur_flash = "";
	
	
	function affich_banners_flash($circuit, $emplacement){
		//renvoie les banners du circuit
		$query = info_banner_circuit($circuit,$emplacement);		

		$data = mysql_fetch_array($query);
		$id_banner = $data['id_banner'];
		$contenu = $data['contenu'];
		//hauteur de la banner en flash
		$taille = $data['taille_flash']; 
		
		//répertoire de destination
		$rep_dest = 'assets/banners/';
		$chemin_cont = $rep_dest.$contenu;
		
		$classe_flash = 'banner_flash';
		
		//Affichage de la banner
		if ($emplacement == 3){
			if ($taille == "petit"){
				$largeur_flash = 468;
				$hauteur_flash = 60;
			}else{				
				$largeur_flash = 736;
				$hauteur_flash = 124;				
				$classe_flash = 'grand_flash';
			}
		}else{
			$largeur_flash = 153;
			if ($taille == "grand"){
				$hauteur_flash = 216;
			}else{
				$hauteur_flash = 94;
			}
		}
				
		//fichier flash
		echo '<div class="'.$classe_flash.'">',"\n";
			echo '<!--[if !IE]> Standard XHTML object instanciation <!-->',"\n";
			echo '<object id="flash00" type="application/x-shockwave-flash" data="',$chemin_cont,'" width="'.$largeur_flash.'" height="'.$hauteur_flash.'">',"\n";
			echo '<!--><![endif]-->',"\n";
			
			echo '<!--[if IE]>',"\n";
			echo '<object id="flash00"  classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  width="'.$largeur_flash.'" height="'.$hauteur_flash.'">',"\n";
			echo '<![endif]-->',"\n";
			
			echo '<param name="movie" value=" ',$chemin_cont,' "/>',"\n";
			echo '<param name="quality" value="high" />',"\n";
			echo '</object>',"\n";
		echo '</div>',"\n";	
	}
	
	/***** Affichage d'une seule banner de type photo ******/
	function affich_une_photo($circuit,$emplacement){
		//renvoie les banners du circuit
		$query = info_banner_circuit($circuit,$emplacement);
		$data = mysql_fetch_array($query);
		
		$nom = $data['nom'];
		$contenu = $data['contenu'];
		$action_banner = $data['action'];
		
		if ($action_banner == ''){
			echo '<img src="banner/',$contenu,'" width="153" border="0" alt="',$nom,'" />',"\n";
		}else{	
			echo'<a href="',$action_banner,'"><img src="banner/',$contenu,'" width="153" border="0" alt="',$nom,'" /></a>',"\n";
		}
		
	}
	
	
	
	/***********************************************************************/
	/********** Affichage des photos en les faisant défiler avec JS ********/
	/***********************************************************************/
	function affich_banners_photos($circuit,$emplacement){  	     	           
	//renvoie les banners du circuit
	$query = info_banner_circuit($circuit,$emplacement);
	
	//parcours des banners du circuit
	$idgalerie = "myGallery".$emplacement;
	echo '<div id="',$idgalerie,'">',"\n";	
	while ($data = mysql_fetch_array($query)) {
		$id_banner = $data['id_banner'];
		//type contenu: fichier(php, flash,...), photo, texte
		$type_contenu = $data['type_contenu'];
		$contenu = $data['contenu'];
		//type action: fichier(php,flash,...), texte, lien
		$type_action = $data['type_action'];
		$action_banner = $data['action'];
		$nom = $data['nom'];
		
		if ($action_banner == '')
			$action_banner = "#";
				

		//emplacament banner
		$rep_dest = 'banner/';
		$chemin_cont = $rep_dest.$contenu;
	
			echo '<a href="',$action_banner,'" title="',$nom,'" class="open" ><img src="',$chemin_cont,'" class="full" alt="',$nom,'" /></a>',"\n";
		
	}//Fin du while
	echo '</div>',"\n";
	

    }//FIN FONCTION AFFICHAGE DES BANNERS PHOTOS	
		
	
	/**** AFFICHER LES BANNERS EN FONCTION DU CIRCUIT ****/
	function banners_comedien($circuit){
/*		echo '<script type="text/javascript">',"\n";
			echo "function startGallery() {
				var myGallery1 = new gallery($(\'myGallery1\'), {
					timed: true,
					showArrows: false,
					showInfopane: false,
					showCarousel: false,
					embedLinks: true,
					delay: 10000
				});var myGallery2 = new gallery($(\'myGallery2\'), {
					timed: true,
					showArrows: false,
					showInfopane: false,
					showCarousel: false,
					embedLinks: true,
					delay: 12000
				});","\n";
			echo '}',"\n";
		echo '</script>',"\n"; */
	
		echo '<div id="contenu_banners">',"\n";
		for ($i = 1; $i <= 2; $i++){	
			//Voir si l'emplacement contient du flash ou des images
			$sql = "SELECT type_contenu FROM t_banners WHERE position = 1";
			$sql .= " AND circuit = '$circuit' AND emplacement = '$i'";
			$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());

			//Récup de nb de banners par circuit
			$query2 = info_banner_circuit($circuit,$i);
			$nb_enreg = mysql_num_rows($query2);
			
			if ($data = mysql_fetch_row($query)){
				$type_contenu = $data[0];
				
				echo '<div align="center">',"\n";
				if ($type_contenu == 'fichier'){
						affich_banners_flash($circuit, $i);
				}else{
					if ($nb_enreg == 1)
						affich_une_photo($circuit,$i);
					// else affich_banners_photos($circuit,$i);
				}
				echo '</div>',"\n";
				echo '<div style="height:10px;"></div>',"\n";
			}
		}
		echo '</div>',"\n";
			
	}
	
	
	
	
	/***** Affichage d'une seule banner horizontale ******/
	function affich_banner_horiz($circuit){
		//renvoie les banners du circuit
		$query = info_banner_circuit($circuit,3);
		$data = mysql_fetch_array($query);
		$nb_enreg = mysql_num_rows($query);		
		
		if ($nb_enreg > 0){
			$nom = $data['nom'];
			$contenu = $data['contenu'];
			$action_banner = $data['action'];
			$type_contenu = $data['type_contenu'];	
			
			if ($type_contenu == 'fichier'){
				echo '<div id="banner_flash">',"\n";
					affich_banners_flash($circuit, 3);
				echo '</div>',"\n";	
			}else{	
				echo '<div id="banner">',"\n";
					if ($action_banner == ''){
						echo '<img src="banner/',$contenu,'" width="468" height="60" border="0" alt="',$nom,'" />',"\n";
					}else{	
						echo'<a href="',$action_banner,'"><img src="banner/',$contenu,'" width="468" height="60" border="0" alt="',$nom,'" /></a>',"\n";
				echo '</div>',"\n";					
				}
			}
		}
		
	}
     
	 
?>