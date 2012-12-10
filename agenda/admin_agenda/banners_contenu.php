<?
		
	require_once("../../admintool/conf.php");
	require_once("../../admintool/fonctions.php");
	
		
	// CONNEXION MYSQL
	$db_link = mysql_connect($sql_serveur, $sql_user, $sql_passwd); 
	if (! $db_link) {echo "Connexion impossible à la base de données <b>$sql_bdd</b> sur le serveur <b>$sql_serveur</b><br>Vérifiez les paramètres du fichier conf.php"; exit;}
	
	$alerter = "";
	
	
	if (! isset($site)) $site = '2';	
	if (! isset($circuit))
		if ($site == '1')  $circuit = 'home';
		else $circuit = 'la_une';
			
	
	// on séléctionne la base 
	mysql_select_db($sql_bdd, $db_link); 
		
	
	
	//renvoie les banners correspondant à une circuit
	function banner_circuit($circuit, $emplacement,$site){
		$sql = "SELECT * FROM t_banners ";
		$sql .= "WHERE circuit = '$circuit' AND emplacement='$emplacement' AND site='$site' ";
		$sql .= "ORDER BY position";
		$query = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
		return $query;
	}
	
	  function affich_banner($id,$circuit,$query,$site){
		while($line = mysql_fetch_assoc($query)) {
	   		$id_banner = $line['id_banner'];
			echo "<li>Banner ".$line['position']." : ".$line['nom']." &nbsp; ";  
			echo "<a href='action_banner.php?id=$id&id_banner=$id_banner&site=$site'> action</a> ";
			echo "<a href='banners.php?id=$id&action=modif&id_banner=$id_banner&circuit=$circuit&site=$site'> |modifier </a> ";
			echo "<a href='banners.php?id=$id&action=supprimer&id_banner=$id_banner&circuit=$circuit&site=$site'> |supprimer </a> </li>";
		}	   		  
	  }
	
		
		
	// DECONNEXION MYSQL	
	//mysql_close($db_link);

?>


<html>
<head>
<title>Banners DLP</title>
<? $page_chemin = '../../'; require($page_chemin.'page_head.php'); ?>	
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../comedien.css" rel="stylesheet" type="text/css">

<!-- Includes pour les banners -->
<link rel="stylesheet" href="../../squelettes/jd.gallery.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="../../squelettes/js/mootools-v1-11.js"></script>
<script src="../../squelettes/js/jd.gallery.js" type="text/javascript"></script>

<style type="text/css">
<!--
	#contenu_banners{
		width:160px;
		background-color:transparent;
		background-repeat:no-repeat;		
	}
	.banner_flash{
		width:160px;
		border: solid 1px #000000;
	}
-->
</style>
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
	              </tr>
	              
	                          	
	              
	              <tr>
	              
	              
				<!-- page de gauche -->
				<td align="center" valign="top" bgcolor="#FFFFFF" width="20">
			
				</td>
			
			
              
	              
	             <td valign="top"> 
		
			
	   	    <?

			echo '<p class="normal">',"\n";
				echo '<img alt="" src="../../assets/puce_bordeau.gif" width="7" height="7" align="absmiddle">',"\n";
					/*echo "<b><a href='banners_contenu.php?site=1&amp;id=$id'>Banners \"Comedien.be\"</a>","\n";*/
					echo "<b><a href='banners_contenu.php?site=2'>Banners \"Demandez le programme\"</a>","\n";
					echo ' / <a href="index_admin.php">Menu Admin</a></b>';
			 echo '</p>',"\n";
	   	  	   	    			
			echo "<p class='normal'><b>CIRCUIT: ".$circuit."</b></p>";
	
			
				/*** PAGES DROITE ***/			
			
			/**** Site Comedien.be ***/
			if ($site == '1'){
				//page droite home
				echo "<li><a href='banners_contenu.php?circuit=home&amp;id=$id&amp;site=$site'>Circuit \"HOME\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 2; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=home&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('home',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'home',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
								
					
				//page droite casting		
				echo "<li><a href='banners_contenu.php?circuit=casting&amp;id=$id&amp;site=$site'>Circuit \"CASTING\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 2; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=casting&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('casting',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'casting',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
							
				
				//page droite annonces
				echo "<li><a href='banners_contenu.php?circuit=annonces&amp;id=$id&amp;site=$site'>Circuit \"PETITES ANNONCES\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 2; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=annonces&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('annonces',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'annonces',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
				
					
				//page droite comédien
				echo "<li><a href='banners_contenu.php?circuit=comediens&amp;id=$id&amp;site=$site'>Circuit \"COMEDIENS\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 2; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=comediens&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('comediens',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'comediens',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
	
				
				//page droite métier
				echo "<li><a href='banners_contenu.php?circuit=stages&amp;id=$id&amp;site=$site'>Circuit \"STAGES\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 2; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=stages&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('stages',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'stages',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
				
				
				echo "</ul>";
				
				echo "<p>&nbsp;</p>";
				echo "<p><a href='liste_circuits.php?site=$site' target='_blank'>Liste des pages & cictuits</a></p>";
			}else{	
				/**** Site "Demandez le pgm" ***/
				//page droite "A la une"
				echo "<li><a href='banners_contenu.php?circuit=la_une&amp;id=$id&amp;site=$site'>Circuit \"A LA UNE\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 3; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=la_une&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('la_une',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'la_une',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
								
					
				//page droite Agenda		
				echo "<li><a href='banners_contenu.php?circuit=agenda&amp;id=$id&amp;site=$site'>Circuit \"AGENDA\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 3; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=agenda&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('agenda',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'agenda',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
							
				
				//page droite Concours
				echo "<li><a href='banners_contenu.php?circuit=concours&amp;id=$id&amp;site=$site'>Circuit \"CONCOURS\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 3; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=concours&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('concours',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'concours',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
				
				
				//page droite Contenus
				echo "<li><a href='banners_contenu.php?circuit=contenus&amp;id=$id&amp;site=$site'>Circuit \"CONTENUS\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 3; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=contenus&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('contenus',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'contenus',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";
				
				
				//page droite Details
				echo "<li><a href='banners_contenu.php?circuit=details&amp;id=$id&amp;site=$site'>Circuit \"DETAILS\"</a>";
				echo "<br />";
				for ($i = 1; $i <= 3; $i++){
					echo "<div style='margin-left: 15px;'>";
					echo "<p><strong>- Emplacement ".$i."</strong>";
					echo "<a href='banners.php?id=$id&amp;circuit=details&amp;action=ajout&amp;emplacement=$i&amp;site=$site'> &nbsp;&nbsp;[ajouter] </a></p>";
					$query_circuit = banner_circuit('details',$i,$site);
					if (mysql_num_rows($query_circuit) <> 0){
						echo "<ul>";
							affich_banner($id,'details',$query_circuit,$site);
						echo "</ul>";
					   }
					echo "</div>";
				}		   
				echo "</li>";							
				
				
				echo "</ul>";	
				
				echo "<p>&nbsp;</p>";
				echo "<p><a href='liste_circuits.php?site=$site' target='_blank'>Liste des pages & cictuits</a></p>";
			} //Fin site "demandez le pgm"
			
			
			
			
		     ?>
		     	</td>
		     	
		     	
		     		<!-- page de droite -->
	     		<td align="center" valign="top" bgcolor="#FFFFFF" width="170">
				<? /*require($page_chemin.'page_droite'.$droite.'.php'); */
				require_once("../../admintool/fonctions.php");
				require_once("affiche_banner2.php");
				banners_comedien($circuit);
				?>
			</td>
					
		      </tr>
		      
		          
		      
		
        	</table></td>
        	<td width="2" bgcolor="#000000"><img src="../../assets/spacer.gif" width="2" height="5"></td>
              <tr> 
                <td colspan="4"><img src="../../assets/popup/gris_bas.gif" width="690" height="3"></td>
              </tr>
            </table></tr>
      </table></td>
  </tr>
</table>

<?
	if ($alerter) {
		echo "<script language='JavaScript'>\n";
		echo "<!--\n";
		echo "alert(\"$alerter\");\n";
		echo "//-->\n";
		echo "</script>\n";
	}
?>
</body>
</html>