<?php 

// Upload image et vignette
function uploader_2 ($id_update,$num_pic)
{		
	require '../inc_db_connect.php';
	require '../inc_var.php';
	
	$fichier_source = 'source_pic_' . $num_pic ;
	$taille_max = 3000000 ;
	$taille_min = 2 ;
	
	$destination = '../' . $folder_vignettes_concours . 'conc_' . $id_update . '_' . $num_pic . '.jpg' ; // Chemin & nom de image de destination
	$destination_vi = '../' . $folder_vignettes_concours . 'vi_conc_' . $id_update . '_' . $num_pic . '.jpg' ; // Chemin & nom de Vignette

	$error_info = ''; // RAZ de la var qui contiendra les messages d'erreur
	$debug_concat =  '<div class="mini_info">IMAGE TRAITEE = ' . $num_pic . '<br />_____________________';
	
	// Afficher les infos (pour le débug)
	$debug_concat.=  '<br />- <b>$num_pic</b> = '.$num_pic ;
	$name = $_FILES[$fichier_source]['name'] ; // renvoie le nom du fichier stocké sur la machine du client qui envoit le fichier. 
	$debug_concat.=  '<br />- <b>name</b> = ' . $name ;
	$tmp_name = $_FILES[$fichier_source]['tmp_name'] ; // renvoie le nom du fichier stocké sur le serveur (le fichier a donc été reçu). 
	$debug_concat.=  '<br />- <b>tmp_name</b> = ' . $tmp_name ;
	$type = $_FILES[$fichier_source]['type']; // renvoie le type mime du fichier envoyé. Attention aux failles de sécurité. 
	$debug_concat.=  '<br />- <b>type</b> = ' . $type ;
	$size = $_FILES[$fichier_source]['size'] ;// renvoie la taille du fichier en octets. 
	$debug_concat.=  '<br />- <b>size</b> = ' . ($size/1000) . ' ko' ;
	$error = $_FILES[$fichier_source]['error'] ;// renvoie éventuellement un code d'erreur pendant le transfert. 
	$debug_concat.=  '<br />- <b>error</b> = ' . $error ;
	$debug_concat.= '<hr>';


	// -----------------------------------------------------------------------
	// Vérifications du fichier uploadé :
	// -----------------------------------------------------------------------
	// Test taille de l'image (en ne passant pas par $_FILES['fichier_source']['size'] pour éviter les failles de sécurité)
	if(filesize($_FILES[$fichier_source]['tmp_name'])>$taille_max )
	{
		$taille_error = $taille_max/1000 ;
		$error_info .=  'ATTENTION ! Le fichier est trop volumineux. \n La taille maximum est de ' . $taille_error .' ko \n';
	}
	elseif (filesize($_FILES[$fichier_source]['tmp_name'])<$taille_min )
	{
		$error_info .=  'ERREUR : poids du fichier anormalement faible \n';
	}
	else
	{ 
		 $debug_concat.=  '<br />- <b>Poids</b>: OK';
	}
	
	// Test du type de l'image à l'aide de la fonction getimagesize()
	list($largeur, $hauteur, $type, $attr)=getimagesize($_FILES[$fichier_source]['tmp_name']);
	if($type===2)	// Type 2 correspond au format JPEG
	{
		 $debug_concat.=  '<br />- <b>Format</b>: OK';
	}
	else
	{
		$error_info .=  'ATTENTION ! L image n est pas au format JPEG \n';
	}
	
	// -------------------------------------------
	// Test des erreurs :
	// -------------------------------------------
	if ($error_info!=NULL)	// Affichage des erreurs constatées => Message :
	{
		echo '<script language="javascript">';
		echo "alert('$error_info');";
		//echo 'history.go(-1)';
		echo '</script>' ;
	}

	//------------------------------------------------------------------------
	// Aucune erreur => copier le fichier dans le répertoire de destination
	//------------------------------------------------------------------------
	else 
	{
		if(move_uploaded_file($_FILES[$fichier_source]['tmp_name'], $destination)) // Copie de l'image sur le serveur
		{
			chmod ($destination, 0644); // Pour que l'image ait un CHMOD 644 et non 600 
			// (ce qui empêcherait la sauvegarde FTP des images)
			$debug_concat.=  ' <br />- <b>Transfert image ' .$num_pic.'</b>: OK';
			
			// Largeur et hauteur initiales
			$uploaded_pic = imagecreatefromjpeg($destination); // = photo uploadée 
			$largeur_uploaded = imagesx($uploaded_pic);
			$hauteur_uploaded = imagesy($uploaded_pic);
			$debug_concat.=  '<br />- <b>Largeur initiale</b>: '.$largeur_uploaded. '<br />	- 
			<b>hauteur initiale</b>: '.$hauteur_uploaded ;

			if ($largeur_uploaded<$maxWidth_conc_pics AND $hauteur_uploaded<$maxHeight_conc_pics) // H > maximum et W > maximum. On cherche la plus grande valeur ==> Inutile de redimentionner l'image
			{	
				$debug_concat.=  '<br />- <b>Dimensions OK</b>)';
			}
			else  // H > maximum ET/OU W > maximum ==> Il faut redimentionner l'image
			{
				$debug_concat.=  '<br />- <b>! L\'image va être redimensionnée  ! </b>)';
				
				if ($largeur_uploaded>$maxWidth_conc_pics AND $hauteur_uploaded>$maxHeight_conc_pics) // H > maximum et W > maximum. On cherche la plus grande valeur
				{
					// La hauteur est plus grande
					if ($hauteur_uploaded>$largeur_uploaded) 
					{
						$debug_concat.=  '<br />- Resamp:::1:::';
						$new_H = $maxHeight_conc_pics;
						// On recalcule la Largeur proportionnellement
						$new_W = round($largeur_uploaded * $maxHeight_conc_pics / $hauteur_uploaded);
						$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
						$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
					}
					else // La largeur est plus grande
					{
						$debug_concat.=  '<br />- Resamp:::2:::';
						$new_W = $maxWidth_conc_pics;
						// On recalcule la Hauteur proportionnellement
						$new_H = round($hauteur_uploaded * $maxWidth_conc_pics / $largeur_uploaded);
						$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
						$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
					}
				}
				else if ($largeur_uploaded<$maxWidth_conc_pics AND $hauteur_uploaded>$maxHeight_conc_pics) // H > maximum et W < maximum. On cherche la plus grande valeur

				{
					$debug_concat.=  '<br />- Resamp:::3:::';
					$new_H = $maxHeight_conc_pics;
					// On recalcule la Largeur proportionnellement
					$new_W = round($largeur_uploaded * $maxHeight_conc_pics / $hauteur_uploaded);
					$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
					$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
				}
				else if ($largeur_uploaded>$maxWidth_conc_pics AND $hauteur_uploaded<$maxHeight_conc_pics) // H < maximum et W > maximum. On cherche la plus grande valeur
				{ 
					$debug_concat.=  '<br />- Resamp:::4:::';
					$new_W = $maxWidth_conc_pics;
					// On recalcule la Hauteur proportionnellement
					$new_H = round($hauteur_uploaded * $maxWidth_conc_pics / $largeur_uploaded);
					$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
					$debug_concat.=  '<br />- <b>nouvelle largeur</b> = '.$new_W ;
				}
		
				// Redimensionner et ré-enregistrer dans le répertoire
				$resample = imagecreatetruecolor($new_W, $new_H); // Création image vide
				imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W, $new_H, $largeur_uploaded, $hauteur_uploaded);
				imagejpeg($resample, $destination, '90');// Enregistrer la miniature sous le nom	
			}


			// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
			// Recalcul de la taille de la vignette :			
			$debug_concat.= '<br />----------------- VIGNETTE ------------------' ;
			if ($largeur_uploaded<$maxWidth_conc_vignette AND $hauteur_uploaded<$maxHeight_conc_vignette)
			{	
				$debug_concat.=  '<br />- <b>Dimensions Vignette OK</b>';
				$new_W_Vignette = $largeur_uploaded ;
				$new_H_Vignette = $hauteur_uploaded ;
			}
			else
			{
				$debug_concat.=  '<br />-<b> ! La Vignette va être redimensionnée  ! </b>';
				
			  	// H > maximum et W > maximum. On cherche la plus grande valeur
				if ($largeur_uploaded>$maxWidth_conc_vignette AND $hauteur_uploaded>$maxHeight_conc_vignette)
				{
					if ($hauteur_uploaded>$largeur_uploaded) 
					{
						$debug_concat.=  '<br />- Resamp::: V 1:::';
						// La hauteur est plus grande
						$new_H_Vignette = $maxHeight_conc_vignette;
						// On recalcule la Largeur proportionnellement
						$new_W_Vignette = round($largeur_uploaded * $maxHeight_conc_vignette / $hauteur_uploaded);
						$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
						$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
						
					}
					else
					{
						$debug_concat.=  '<br />- Resamp::: V 2:::';
						// La largeur est plus grande
						$new_W_Vignette = $maxWidth_conc_vignette;
						// On recalcule la Hauteur proportionnellement
						$new_H_Vignette = round($hauteur_uploaded * $maxWidth_conc_vignette / $largeur_uploaded);
						$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
						$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
					}
				}
				else if ($largeur_uploaded<$maxWidth_conc_vignette AND $hauteur_uploaded>$maxHeight_conc_vignette)
				{
					$debug_concat.=  '<br />- Resamp::: V 3:::';
					$new_H_Vignette = $maxHeight_conc_vignette;
					// On recalcule la Largeur proportionnellement
					$new_W_Vignette = round($largeur_uploaded * $maxHeight_conc_vignette / $hauteur_uploaded);
						$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
						$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
				}
				else if ($largeur_uploaded>$maxWidth_conc_vignette AND $hauteur_uploaded<$maxHeight_conc_vignette)
				{
					$debug_concat.=  '<br />- Resamp::: V 4:::';
					$new_W_Vignette = $maxWidth_conc_vignette;
					// On recalcule la Hauteur proportionnellement
					$new_H_Vignette = round($hauteur_uploaded * $maxWidth_conc_vignette / $largeur_uploaded);
					$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
					$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ; 
				}
			}
			$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
			imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W_Vignette, $new_H_Vignette, $largeur_uploaded, $hauteur_uploaded);

			if (file_exists($destination_vi))
				@unlink($destination_vi);
			imagejpeg($resample, $destination_vi, '100');// Enregistrer la miniature sous le nom

			// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV Fin vignette

			// -----------------------------------------------------
			// Mettre le FLAG de la TABLE à SET
			/*$image_db = 'pic_event_' .  $num_pic ;
			mysql_query("UPDATE $table_evenements_agenda SET $image_db = 'set' WHERE id_event = '$id_update' LIMIT 1 ");*/
			mysql_query("UPDATE `$table_ag_conc_fiches` SET `pic_conc` = 'set' WHERE `id_conc` = '$id_update' LIMIT 1 ");
		}			
		else // l'image n'a pas pu être uploadée
		{
			$error_info .=  'ATTENTION ! Erreur lors de la copie du fichier. \n 
			Veuillez recommencer l\'operation \n'; //Erreur
		}
	}
	$debug_concat.=  '</div> <br />';
	//echo $debug_concat ;
}
?>