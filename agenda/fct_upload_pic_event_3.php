<?php 

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Upload image et vignette pour les FICHES EVENEMENTS
// L'image est remise à une taille correspondant à une largeur strictement égale à $w_absolue
// La vignette est redimenstionnée en W et en H selon $max_w_vi_event et $max_h_vi_event comme dans la version 2
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function uploader_3 ($id_update,$num_pic)
{		
	require '../inc_db_connect.php';
	require '../inc_var.php';
	
	$fichier_source = 'source_pic_' . $num_pic ;
	$taille_max = 4000000 ;
	$taille_min = 2 ;
	$destination = '../' . $folder_pics_event . 'event_' . $id_update . '_' . $num_pic . '.jpg' ; // Chemin & nom de image de destination
	$destination_vi = '../' . $folder_pics_event . 'vi_event_' . $id_update . '_' . $num_pic . '.jpg' ; // Chemin & nom de Vignette

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

	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// Test taille de l'image (en ne passant pas par $_FILES['fichier_source']['size'] pour éviter les failles de sécurité)
	// 	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT

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
	
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
	// Test du type de l'image à l'aide de la fonction getimagesize()
	// TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
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
	if ($error_info!= '')	// Affichage des erreurs constatées => Message :
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


			if ($largeur_uploaded >= $w_absolue) // Largeur Suffisante
			{
				if ($largeur_uploaded == $w_absolue) // Largeur OK
				{	
					$debug_concat.=  '<br />- <b>Largeur strictement OK</b>)';
					$new_H = $hauteur_uploaded ;
				}
				
				elseif ($largeur_uploaded >= $w_absolue) // Largeur suffisante. L'image va être redimensionnée
				{	
					$debug_concat.=  '<br />- <b>Largeur suffisante. L\'image va être redimensionnée</b>';
					
					// On recalcule la Hauteur proportionnellement
					$new_H = round($hauteur_uploaded * $w_absolue / $largeur_uploaded);
					$debug_concat.=  '<br />- <b>nouvelle hauteur</b> = '.$new_H ;
				}
			
				// Redimensionner et ré-enregistrer dans le répertoire
				$resample = imagecreatetruecolor($w_absolue, $new_H); // Création image vide
				imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $w_absolue, $new_H, $largeur_uploaded, $hauteur_uploaded);
				imagejpeg($resample, $destination, '90');// Enregistrer la miniature sous le nom	
									
	
				// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
				// Recalcul de la taille de la vignette
				// Les dimensions sont contraintes en W et en H (comme auparavant)
				// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV
				$debug_concat.= '<br />----------------- VIGNETTE ------------------' ;
				if ($largeur_uploaded<$max_w_vi_event AND $hauteur_uploaded<$max_h_vi_event)
				{	
					$debug_concat.=  '<br />- <b>Dimensions Vignette OK</b>';
					$new_W_Vignette = $largeur_uploaded ;
					$new_H_Vignette = $hauteur_uploaded ;
				}
				else
				{
					$debug_concat.=  '<br />-<b> ! La Vignette va être redimensionnée  ! </b>';
					
					// H > maximum et W > maximum. On cherche la plus grande valeur
					if ($largeur_uploaded>$max_w_vi_event AND $hauteur_uploaded>$max_h_vi_event)
					{
						if ($hauteur_uploaded>$largeur_uploaded) 
						{
							$debug_concat.=  '<br />- Resamp::: V 1:::';
							// La hauteur est plus grande
							$new_H_Vignette = $max_h_vi_event;
							// On recalcule la Largeur proportionnellement
							$new_W_Vignette = round($largeur_uploaded * $max_h_vi_event / $hauteur_uploaded);
							$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
							$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
							
						}
						else
						{
							$debug_concat.=  '<br />- Resamp::: V 2:::';
							// La largeur est plus grande
							$new_W_Vignette = $max_w_vi_event;
							// On recalcule la Hauteur proportionnellement
							$new_H_Vignette = round($hauteur_uploaded * $max_w_vi_event / $largeur_uploaded);
							$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
							$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
						}
					}
					else if ($largeur_uploaded<$max_w_vi_event AND $hauteur_uploaded>$max_h_vi_event)
					{
						$debug_concat.=  '<br />- Resamp::: V 3:::';
						$new_H_Vignette = $max_h_vi_event;
						// On recalcule la Largeur proportionnellement
						$new_W_Vignette = round($largeur_uploaded * $max_h_vi_event / $hauteur_uploaded);
							$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
							$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ;
					}
					else if ($largeur_uploaded>$max_w_vi_event AND $hauteur_uploaded<$max_h_vi_event)
					{
						$debug_concat.=  '<br />- Resamp::: V 4:::';
						$new_W_Vignette = $max_w_vi_event;
						// On recalcule la Hauteur proportionnellement
						$new_H_Vignette = round($hauteur_uploaded * $max_w_vi_event / $largeur_uploaded);
						$debug_concat.=  '<br /><b>- nouvelle hauteur Vignette</b> = '.$new_H_Vignette ;
						$debug_concat.=  '<br /><b>- nouvelle largeur Vignette</b> = '.$new_W_Vignette ; 
					}
				}
				$resample = imagecreatetruecolor($new_W_Vignette, $new_H_Vignette); // Création image vide
				imagecopyresampled($resample, $uploaded_pic, 0, 0, 0, 0, $new_W_Vignette, $new_H_Vignette, $largeur_uploaded, $hauteur_uploaded);
				imagejpeg($resample, $destination_vi, '90');// Enregistrer la miniature sous le nom
				chmod ($destination_vi, 0644); // Pour que l'image ait un CHMOD 644 et non 600 

	
				// VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV Fin vignette
	
				// -----------------------------------------------------
				// Mettre le FLAG de la TABLE à SET
				$image_db = 'pic_event_' .  $num_pic ;
				mysql_query("UPDATE $table_evenements_agenda SET $image_db = 'set' WHERE id_event = '$id_update' LIMIT 1 ");
			}
			else
			{
				// La largeur n'est pas suffisante => refuser l'image
				@unlink($destination) ; // J'efface l'image importée AVANT les tests de dimension
				@unlink($destination_vi) ;
	
				$image_db = 'pic_event_' .  $num_pic ;
				mysql_query("UPDATE $table_evenements_agenda SET $image_db = '' WHERE id_event = '$id_update' LIMIT 1 ");

				$debug_concat.=  '<br />- <b>Largeur insuffisante => image refusée</b>';
				$error_info .=  '<br />La largeur de votre image doit être égale ou supérieure à <b>' . $w_absolue . ' pixels</b>.
				Veuillez recommencer l\'opération avec une image aux bonnes dimensions.';
			}
			
		}				
		else // l'image n'a pas pu être uploadée (le ifmoveuploaded = 0)
		{
			$error_info .=  '<br />ATTENTION ! Erreur lors de la copie du fichier. <br /> 
			Veuillez recommencer l\'operation '; //Erreur
		}
	}
	if ($error_info != '')
	{
		echo '<div class="alerte"><p>' . $error_info . '</p><br /></div>' ;
	}
	
	$debug_concat.=  '</div> <br />';
	//echo $debug_concat ;
}
?>