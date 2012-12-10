<?				
		/*** FONCTIONS POUR LES FICHIERS ***/	
	//upload fichier dans le répertoire "projecteur"
	function upload_fichier($fichier_temp,$fichier_dest){
		if (move_uploaded_file($fichier_temp,$fichier_dest)) {
	   		$alerter = "";
		}else{
			$alerter = "Erreur upload fichier";
		}
		
		return $alerter;
	}
	
	
	//suppression physique d'un fichier
	function suppr_fichier($repertoire,$fichier){
		if(file_exists($repertoire.$fichier))
			unlink($repertoire.$fichier);
	}
	
		/*** FONCTIONS POUR INVITES DU MOIS + COUP PROJECTEUR ***/
		
	/* ajout du nom de la photo dans la DB*/
	function aj_photo($fichier_dest,$id_projecteur){
		//basename sépare le nom du fichier du dossier
		$fich_db = basename($fichier_dest);
		$sql= "UPDATE t_projecteur SET photo_cadre='$fich_db' ";
		$sql .= "WHERE id_projecteur = $id_projecteur";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
	}
		
	//renvoie l'invité ou le coup de proj courant (en fct du type)
	function dernier_proj($type){
		$sql = "SELECT max(id_projecteur) FROM t_projecteur P ";
		$sql .= "WHERE P.type = '$type' ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		$data = mysql_fetch_row($query);
		$dernier = $data[0];
		return $dernier;
	}
	
	function info_proj($id_projecteur){
		$sql = "SELECT * FROM t_projecteur  ";
		$sql .= "WHERE id_projecteur = $id_projecteur";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		return $query;
	}
	
	
		/*** FONCTIONS POUR LA GSETION DES BANNERS ***/
		
	//ajout du fichier correspondant à la banner
	function aj_fich_banner($fichier_dest,$id_banner){
		//basename sépare le nom du fichier du dossier
		$fich_db = basename($fichier_dest);
		$sql= "UPDATE t_banners SET contenu='$fich_db' ";
		$sql .= "WHERE id_banner = $id_banner";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
	}
		
	//renvoie le n° de position de la dernière banner d'un circuit
	function max_position($circuit,$emplacement){
		$sql = "SELECT max(position) FROM t_banners ";
		$sql .= "WHERE circuit = '$circuit' AND emplacement = '$emplacement'";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		$data = mysql_fetch_row($query);
		$max = $data[0];
		return $max;
	}
	
	function info_banner($id_banner){
		$sql = "SELECT * FROM t_banners  ";
		$sql .= "WHERE id_banner = $id_banner";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		return $query;
	}
	
	//renvoie les banners appartenant à un circuit
	function info_banner_circuit($circuit,$emplacement){
		$sql = "SELECT * FROM t_banners ";
		$sql .= "WHERE circuit = '$circuit' AND emplacement = '$emplacement'";
		$sql .= "ORDER BY position ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		return $query;
	}
	
	//décale les no de position pour l'ajout 
	function decale_pos_ajout($position,$circuit,$emplacement){
		$sql = "UPDATE t_banners SET position = position +1 ";
		$sql .= "WHERE position >= $position ";
		$sql .= "AND circuit = '$circuit' AND emplacement = '$emplacement'";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
	}
	
	function extension($fichier){
		$pos = strrpos($fichier, "."); 
		$long = strlen($fichier);
		$extension = substr ( $fichier,$pos+1,$long-$pos) ; 
		return $extension;
	}
	
	
		/*** FONCTIONS POUR LA GESTION DES ACTIONS DES BANNERS (clic banner) ***/
		
	//ajout du fichier correspondant à l'action de la banner
	function aj_fich_action($fichier_dest,$id_banner){
		//basename sépare le nom du fichier du dossier
		$fich_db = basename($fichier_dest);
		$sql= "UPDATE t_banners SET action='$fich_db' ";
		$sql .= "WHERE id_banner = $id_banner";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());	
	}
	
	
	function update_lien($id_banner,$lien){
		$sql = "UPDATE t_banners  SET action = '$lien' ";
		$sql .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
	}
	
	//ajout dans la table qui gère la page de texte
	function ajout_texte ($id_banner,$titre,$circuit_gauche,$circuit_droite){
		$sql = "INSERT INTO t_texte_banner (id_banner,titre,circuit_gauche,circuit_droite) ";
		$sql .= "VALUES ($id_banner,'$titre','$circuit_gauche','$circuit_droite') ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
	}
	
	//afficher les infos sur la page de texte
	function info_texte ($id_banner){
		$sql = "SELECT * FROM t_texte_banner ";
		$sql .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		return $query;
	}
	
	function update_texte ($id_banner,$titre,$circuit_gauche,$circuit_droite){
		$sql = "UPDATE t_texte_banner ";
		$sql .= "SET titre = '$titre', circuit_gauche= '$circuit_gauche', circuit_droite = '$circuit_droite' ";
		$sql .= "WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
	}
	
	function suppr_texte ($id_banner){
		$sql = "DELETE FROM t_texte_banner WHERE id_banner = $id_banner ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
	}
	
	
	
	//renvoie les banners appartenant à une page 
	function info_banner_page($page){
		$sql = "SELECT * FROM t_banners ";
		$sql .= "WHERE page = '$page' ";
		$sql .= "ORDER BY position ";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		return $query;
	}
		

?>