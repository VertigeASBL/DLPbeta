<?php
$session = $_GET['session'];
if ($session!="")
{
	require '../../inc_db_connect.php';
	require 'inc_var_inscription.php';

	$reponse = mysql_query("SELECT * FROM $table_im_crypt WHERE session_crypt = '$session'");
	$donnees = mysql_fetch_array($reponse);
	if ($donnees ['code_crypt']!="")
	{	
		// création de l'image
		$img = imagecreate(72, 25);
		
		// Défintion des couleurs 
		$bgc = imagecolorallocate($img, 200, 200, 211);
		$black = imagecolorallocate($img, 0, 0, 0);
		$gris = imagecolorallocate($img, 128, 128, 230);
		$orange = imagecolorallocate($img, 230, 230, 230);
		$vert = imagecolorallocate($img, 50, 120, 20);
		
		// Remplissage du fond 
		imagefilledrectangle($img, 0, 0, 72, 25, $orange);
				
		$code = $donnees ['code_crypt'];
		
		imagestring($img, 5, 12, 5, $code, $black); //bool imagestring ( resource image, int font, int x, int y, string s, int col )
		
		// Ajout d'un bruit 
		for($i=0;$i<70;$i++) 
		{ 
			imagesetpixel($img, rand(0,72), rand(0,25), $vert); 
		} 
		
		for($i=0;$i<30;$i++) 
		{ 
			imagesetpixel($img, rand(0,72), rand(0,25), $black); 
		} 
			
		imagepng($img); //renvoie une image sous format png
		imagedestroy($img); //détruit l'image, libérant ainsi de la mémoire
	}
}
?>
