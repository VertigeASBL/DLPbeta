<?php 
function crop_image($source) {
	
	/* Création de l'image source */
	$targ_w = 161;
	$targ_h = 230;
	$jpeg_quality = 90;

	$img_r = imagecreatefromjpeg($source);
	$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

	imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
	$targ_w,$targ_h,$_POST['w'],$_POST['h']);

	/* Créer l'image recadrée */
	$image = imagejpeg($dst_r, str_replace('.tmp', '', $source), $jpeg_quality);
	/* Supprimer l'image temporaire. */
	@unlink($source);
}

?>