<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>test</title>
</head>

<body>


<?php

	//$ghj = fopen("http://www.clubplasma.be/rss/agenda.xml", "r");
	
	/*$ghj = file_get_contents ("http://www.clubplasma.be/rss/agenda.xml");
echo $ghj  ;*/

//if($chaine = @implode("",@file($fichier))) // Lecture fichier XML


 
 
   //--- Se connecter au serveur
   $flux = ''; $errno = 0; $errstr = '';
   if ($fp = @fsockopen('www.clubplasma.be', 80, $errno, $errstr, 10)) {
    //--- Envoyer la requête
    fputs($fp, 'GET /rss/agenda.xml HTTP/1.0'."\r\n".'HOST: www.clubplasma.be '."\r\n"."Connection: close\r\n\r\n");
    //--- Recevoir la réponse
    while (! feof($fp))
     $flux .= fgets($fp, 4096);
    fclose($fp);
   }
   else
    echo '--- Connexion impossible : ',$errno,' : ',$errstr,' ---';
   unset($fp, $errno, $errstr);
   if ($flux)
      echo nl2br(htmlspecialchars($flux));
 
   
   
?>


</body>
</html>
