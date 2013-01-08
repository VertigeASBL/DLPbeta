<?php
	$type_urls = "propres";
/*--- old : voir "richir" adaptation dans ecrire\urls\propres.php
	@include_once dirname(__FILE__).'/ecran_securite.php'; */
	define ('_ID_WEBMESTRES' ,'1:42:8:46');

//--- Intercepter l'envoi de mail, forcer le destinataire, ajouter "beta", forcer l'adresse de notification
if (! function_exists('mail_beta')) {
function mail_beta($email, $sujet, $texte, $headers, $retour = '-f philippe@vertige.org') {
	if (strpos($headers, 'text/html')!==false)
		str_replace('<body>', '<body>Pour : '.$email.' (provisoire)'."\n\n", $texte);
	else
		$texte = 'Pour : '.$email.' (provisoire)'."\n\n".$texte;
	$email = 'philippe@vertige.org, charleshenry@comedien.be';
	$sujet = '-BETA-'.(strpos($_SERVER['HTTP_HOST'],'localhost')!==false || strpos($_SERVER['HTTP_HOST'],'127.0.0')!==false ? 'LOC' : 'DIST').'- '.$sujet;
	$retour = '-f philippe@vertige.org';
	return mail($email, $sujet, $texte, $headers, $retour);
	} 
}


/* Activation des erreurs */
// error_reporting(E_ALL^E_NOTICE);
// ini_set ("display_errors", "On");
?>