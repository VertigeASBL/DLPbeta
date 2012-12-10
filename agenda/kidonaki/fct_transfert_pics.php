<?php

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// sur base de http://forum.alsacreations.com/topic-20-28925-1-Uploader-une-image-depuis-une-url-avec-Php.html
// Le 3e argument de la fonction dit s'il faut créer les "documents SPIP" dans la DB de Kidonaki
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function transferer_image_sur_kido ($nom_image, $article_spip, $updater_db)
{	
	$debug_transfert_pics = '' ;
	$maxsize = 200000;
	$repertoire = "../kidonaki_pics/";
	//$numero_pic_et_indice = '2825_1' ;

	$url_image = 'http://www.demandezleprogramme.be/agenda/pics_events/' . $nom_image ;
	// echo ' <br>' . $url_image  . ' <br>';
	if(!preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $url_image, $url_ary)) 
	{
		echo '<div class="alerte">Erreur 1/ Impossible de télécharger l\'image... Mauvaise URL !</div' ;
	}
	$base_filename = substr($url_ary[4],strrpos($url_ary[4],"/")+1);
	//var_dump ($url_ary) ;

	$base_get = '/' . $url_ary[4];
	$port = (!empty($url_ary[3]) ) ? $url_ary[3] : 80;
	$debug_transfert_pics = 'Transfert de l\'image ' . $base_filename . ' : <br />' ;

	if ( !($fsock = fsockopen($url_ary[2], $port, $errno, $errstr)) )
	{
		echo '<div class="alerte">Erreur 2/ Impossible de télécharger l\'image... Pas de connexion</div>' ;
		return ;
	}

	// Écrit un fichier en mode binaire
	fputs($fsock, "GET $base_get HTTP/1.1\r\n");
	fputs($fsock, "Host: " . $url_ary[2] . "\r\n");
	fputs($fsock, "Accept-Language: fr\r\n");
	fputs($fsock, "Accept-Encoding: none\r\n");
	fputs($fsock, "User-Agent: PHP\r\n");
	fputs($fsock, "Connection: close\r\n\r\n");

	$data = '' ;
	while( !feof($fsock) )
	{
		$data .= fread($fsock, $maxsize);
	}
	fclose($fsock);

	if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $data, $file_data2))
	{
		echo '<div class="alerte">3/ Impossible de télécharger l\'image... Aucune donnée.</div>' ;
		return ;
	}

	$filesize = $file_data1[1]; 
	$filetype = $file_data2[1]; 

	if ( empty($error) && $filesize > 0 && $filesize < $maxsize )
	{
		$data = substr($data, strlen($data) - $filesize, $filesize);
		$filename = $repertoire.$base_filename;

		if(file_exists($filename)) 
		{
			$debug_transfert_pics = '<p>Le fichier ' . $base_filename . ' existe déjà. Il va être remplacé.</p>';
		}
		$fptr = fopen($filename, 'wb');
		$bytes_written = fwrite($fptr, $data, $filesize);
		fclose($fptr);
		if ( $bytes_written != $filesize )
		{
			unlink($tmp_filename);
			echo '<div class="alerte">4/ Impossible de télécharger l\'image... Echec d\'écriture.</div>' ;
			return ;
		}
		$debug_transfert_pics = $base_filename . '<br /> Image enregistrée avec succès ! <br />  
		<img src="' . $repertoire.$base_filename . '"><br /> ';
	}
	
	// ++++++++++++++++++++++++++++++++++++++++++++++++++
	// Créer les "documents spips" qui y correspondent s'il ne s'agit pas d'une vignette...
	// ++++++++++++++++++++++++++++++++++++++++++++++++++
	if ($updater_db)
	{
		require 'inc_db_connect_kidonaki.php';
		
		// ...dans "spip_documents" (pour les images) ...
		// ----------------------------------------------
		mysql_query("INSERT INTO `ki3naki`.`spip_documents` (`id_document` ,`id_vignette` ,`extension` ,`titre` ,`date` ,`descriptif` ,`fichier` ,`taille` ,`largeur` ,`hauteur` ,`mode` ,`distant` ,`maj` ,`statut` ,`date_publication` ,`brise` ,`credits`)
		VALUES (
		'' , '0', 'jpg', '$nom_image', CURDATE(), '', '$url_image', NULL , NULL , NULL , 'image', 'oui', NOW( ) , 'publie', 'CURDATE()', '0', '')") or die('Erreur 1 écriture transfert pic : ' . mysql_error() . ' ');
		
		$id_nouveau_document = mysql_insert_id();

		
		// ...et dans "spip_documents_liens" ( pour lien avec article)
		// -----------------------------------------------------------
		mysql_query("INSERT INTO `ki3naki`.`spip_documents_liens` (
		`id_document` ,`id_objet` ,`objet` ,`vu`)
		VALUES (
		'$id_nouveau_document', '$article_spip', 'article', 'non')") or die('Erreur 2 écriture transfert pic : ' . mysql_error() . ' ');
	}
	
	
	return('transfert_ok') ;
	// echo $debug_transfert_pics ;
}
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

?>
