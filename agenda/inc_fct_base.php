<?php
// -------------------------------------------------------------------------
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
//                              Fonctions
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// -------------------------------------------------------------------------

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Raccourcir la chaine et couper à un "espace"
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function raccourcir_chaine ($chaine_a_raccourcir,$max)
{
	if(strlen($chaine_a_raccourcir)>=$max)
	{
		$chaine_a_raccourcir=substr($chaine_a_raccourcir,0,$max);
		$espace=strrpos($chaine_a_raccourcir," ");
		if($espace)
		{ 
			$chaine_a_raccourcir=substr($chaine_a_raccourcir,0,$espace);
		}
		$chaine_a_raccourcir .= '...';
	}
	$chaine_raccourcie = $chaine_a_raccourcir ;
	return $chaine_raccourcie ;
}



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Raccourcir la chaine en coupant "net"
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function raccourcir_chaine_net ($chaine_a_raccourcir,$max)
{
	
	$chaine_a_raccourcir = html_entity_decode ($chaine_a_raccourcir) ;
	if(strlen($chaine_a_raccourcir)>=$max)
	{
		$chaine_a_raccourcir=substr($chaine_a_raccourcir,0,$max);
		$chaine_a_raccourcir .= '...';
	}
	$chaine_raccourcie = $chaine_a_raccourcir ;
	return $chaine_raccourcie ;
}


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Transformer les <br> en retour avant de remettre le texte du
// message dans le formulaire. (Fonction de doc PHP)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function br2nl($text)/* br2nl for use with HTML forms, etc. */
{
   $text = str_replace("<br />","",$text); // Remove XHTML linebreak tags
   $text = str_replace("<br>","",$text);// Remove HTML 4.01 linebreak tags. //
   return $text;
}




// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction de conversion HTML -> BBCODE (transforme hyperliens et mail-to en BBCODE)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function remove_link_mail ($expr)
{
	$expr = preg_replace('!<a href="(.+)">(.+)</a>!isU', '[url]$1]$2\[url]', $expr); // options "s" = jamais de retour à la ligne au milieu d'une URL
	$expr = preg_replace('!<a href="mailto:(.+)</a>!isU', '[email]$1]$2\[email]', $expr);
//	$expr = preg_replace('!<a href="mailto:(.+)</a>!isU', '[email]$1">$1[/email]', $expr);
	$expr = preg_replace('!<b>(.+)</b>!isU', '[b]$1[/b]', $expr);
	$expr = preg_replace('!<i>(.+)</i>!isU', '[i]$1[/i]', $expr);
	return $expr;
}




// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Fonction de conversion BBCODE -> HTML (hyperliens et mail to)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function add_link_mail ($expr)
{
	$expr = preg_replace('!\[url\](.+)\](.+)\[/url\]!isU', '<a href="$1">$2</a>', $expr); // options "s" = jamais de retour à la ligne au milieu d'une URL
	$expr = preg_replace('!\[email\](.+)\](.+)\[/email\]!isU', '<a href="mailto:$1">$2</a>', $expr);
//	$expr = preg_replace('!\[email\](.+)\[/email\]!isU', '<a href="mailto:$1">$1</a>', $expr);
	$expr = preg_replace('!\[b\](.+)\[/b\]!isU', '<b>$1</b>', $expr);
	$expr = preg_replace('!\[i\](.+)\[/i\]!isU', '<i>$1</i>', $expr);
	return $expr;
}


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Rajouter un zéro devant chiffre pour obtenir chaîne de 2 caracteres
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function add_chaine_2_car ($chaine_1_ou_2_c)
{
	$chaine_2_car = str_pad($chaine_1_ou_2_c, 2, "0", STR_PAD_LEFT) ;  // Complète la chaîne
	return $chaine_2_car;
}



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Remplacer les caractères accentués dans une chaine 
function enlever_accents($chaine)
{
	$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ";
	$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
	return(strtr($chaine,$tofind,$replac));
}



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Encoder selon RFC2047 (cfr Phil)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function encodeHeader($chn) 
{
    preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $chn, $tab);
    while (list(, $s1) = each($tab[1])) 
	{
        $s2 = preg_replace('/([\x20\x80-\xFF])/e', '"=".strtoupper(dechex(ord("\1")))', $s1);
        $chn = str_replace($s1, '=?ISO-8859-1?Q?'.$s2.'?=', $chn);
    }
    return $chn;
}


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Calcul du facteur CHANCE lors de participation aux concours
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
/* Trouver la correspondance entre le nombre d'avis APPROUVES et le coefficient 
multiplicateur de chance lors de la participation aux concours
0			*1
1 à 10		*2
11 à 20		*3
21 à 50		*5
51 et plus	*7
*/
function calcul_facteur_chance($nb_avis_approuve)
{
	if ($nb_avis_approuve > 0 AND $nb_avis_approuve <= 10)
	{
		$valeur_facteur_chance  = 2;
	}
	elseif ($nb_avis_approuve > 10 AND $nb_avis_approuve <= 20)
	{
		$valeur_facteur_chance  = 4;
	}
	elseif ($nb_avis_approuve > 20 AND $nb_avis_approuve <= 50)
	{
		$valeur_facteur_chance  = 7;
	}
	elseif ($nb_avis_approuve > 50)
	{
		$valeur_facteur_chance  = 12;
	}
	else
	{
		$valeur_facteur_chance  = 1;
	}	
	
	return array (
	"valeur_facteur_chance" => $valeur_facteur_chance,
	);
}



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Grade et icone des spectateurs
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
/* Trouver la correspondance entre le nombre d'avis POSTES et les
grade et icone des spectateurs
cat 1	0			Nouveau Membre
cat 2	1 à 10		Membre actif
cat 3	11 à 20	 	Membre régulier
cat 4	21 à …50	Membre assidu
cat 5	51 et plus	Membre passionné
*/
function trouve_categorie_spectateur($nb_avis_postes)
{
	if ($nb_avis_postes > 0 AND $nb_avis_postes <= 10)
	{
		$categorie_spectateur = 'Membre actif';
		$icone_spectateur = 'etoile_2.jpg';
	}
	elseif ($nb_avis_postes > 10 AND $nb_avis_postes <= 20)
	{
		$categorie_spectateur = 'Membre régulier';
		$icone_spectateur = 'etoile_3.jpg';
	}
	elseif ($nb_avis_postes > 20 AND $nb_avis_postes <= 50)
	{
		$categorie_spectateur = 'Membre assidu';
		$icone_spectateur = 'etoile_4.jpg';
	}
	elseif ($nb_avis_postes > 50)
	{
		$categorie_spectateur = 'Membre passionné';
		$icone_spectateur = 'etoile_5.jpg';
	}
	else
	{
		$categorie_spectateur = 'Nouveau Membre';
		$icone_spectateur = 'etoile_1.jpg';
	}	
	
	return array (
	"categorie_spectateur" => $categorie_spectateur,
	"icone_spectateur" => $icone_spectateur
	);
}


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Connaitre le nombre d'avis laissés par 1 spectateur (via "pseudo_spectateur")
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF	global $table_avis_agenda;
function connaitre_nb_avis_spect ($pseudo_spectateur)
{
	$retour_nb_avis_spect = mysql_query("SELECT COUNT(*) AS nbre_entrees FROM ag_avis WHERE nom_avis = '$pseudo_spectateur'");
	$donnees_nb_avis_spect = mysql_fetch_array($retour_nb_avis_spect);
	$_tot_entrees = $donnees_nb_avis_spect['nbre_entrees'];
	return $_tot_entrees ; 
}

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
} }
?>
