<?php 


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Le USER du LIEU peut-il accéder à la page 
// C'est destiné à une page complète
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function test_acces_page_auth ($niveau_requis) 
{
	require '../inc_var.php';
	if (isset($_SESSION['group_admin_spec'])) // L'utilisateur est-il déjà identifié ?
	{
		if ($_SESSION['group_admin_spec'] == $niveau_requis OR $_SESSION['group_admin_spec'] == '5') 
		{
			echo '' ;
		}
		else
		{
			echo '<p><br /></p><p><br /></p><div class="alerte">Vous ne pouvez pas avoir accès à cette page.</div><br />' ;
			//$_SESSION['group_admin_spec'] = '';
			//include ("auth_formulaire.php");

			exit();
		}
	}
	else // La variable "$_SESSION['group_admin_spec']" n'existe pas
	{
		echo '<br /><div class="alerte"><p> L\'accès à cette page requiert une authentification. </p> </div><br />' ;
		include ("auth_formulaire.php");
		exit();
	}
}



/* FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
	Le SPECTATEUR peut-il accéder à la page. Basé sur function "test_acces_page_auth" et 
	modifié car formulaire différent. C'est destiné à une page complète
	FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF */
function test_spectateur_acces_page_auth ($niveau_requis) 
{
	require '../inc_var.php';
	if (isset($_SESSION['group_admin_spec']) AND ($_SESSION['group_admin_spec'] == $niveau_requis OR $_SESSION['group_admin_spec'] == '5'))
	{
		echo '' ;
	}
	else // La variable "$_SESSION['group_admin_spec']" n'existe pas
	{
		echo '<br />
		 <br /><div class="alerte"><p> L\'accès à cette page requiert une authentification. <br />
		 En vous connectant ci-dessous vous accédez à votre compte spectateur sur Demandezleprogramme.be</p> </div><br />' ;
/*		include ("auth_formulaire_spectateur.php");
//		exit(); */
	}
} 


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Test du niveau d'administration du USER (avec la variable "$_SESSION['group_admin_spec']")
// C'est destiné à une portion de code
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function niveau_auth_test ($niveau_requis) 
{
	$retour_auth = '';
	require '../inc_var.php';
	//echo '-- '.$_SESSION['group_admin_spec'].' ---<br>';
	//echo '-- '.$niveau_requis.' ---<br>';
	
	if (isset($_SESSION['group_admin_spec']))
	{
		if ($_SESSION['group_admin_spec'] == $niveau_requis )
		{ 
			$retour_auth = 'ok' ; 
			//echo '-- [1] --<br>';
		}
		
		elseif ($_SESSION['group_admin_spec'] == '5')
		{ 
			$retour_auth = 'ok' ;
			//echo '-- [2] --<br>';
		 }
		
		else
		{ 
			$retour_auth = 'invalide' ;
		}
	}
	else
	{
		$retour_auth = 'invalide' ;
	}
	return $retour_auth ;
}



// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Affichage Nom, Groupe et Log Off du USER
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function voir_infos_user ()
{
	 $afficher_log = '<div class="log_off">' ;
	 
	 $afficher_log.= ' - <a href="../user_admin/edit_user_gp.php" title="Modifier mon compte utilisateur">'
	 . (isset($_SESSION['nom_admin_spec']) ? $_SESSION['nom_admin_spec'] : 'aucun') . '</a> ';
	 
	// $afficher_log.= '<i>(id:' . $_SESSION['id_admin_spec'] . ')</i> | ';
	
	 $afficher_log.= ' | <a href="../user_admin/listing_events_gp.php" title="Modifier l\'agenda">'
	 . $_SESSION['lieu_admin_spec_name'] .  '</a> | ';
	 // $afficher_log.= 'Nom Groupe : ' . $_SESSION['group_admin_spec_name'] .  ' | ';
	// $afficher_log.= '(Niveau : ' . $_SESSION['group_admin_spec'] .  ') | ';
	 $afficher_log.= '<a href="../auth/auth_log_off.php">Déconnexion</a> - </div>';
	 
	 echo $afficher_log;
}



/*
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Affichage Nom, Groupe et Log Off du SPECTATEUR
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function voir_infos_spectateur ()
{
	 $afficher_log = '<div class="log_off">' ;
	 
	 $afficher_log.= ' - <a href="../spectateurs_admin/edit_profile_spectateur.php" title="Modifier mes infos spectateur">'
	 . $_SESSION['pseudo_spectateur'] . '</a></b> - ';

	// $afficher_log.= 'Nom Groupe : ' . $_SESSION['group_admin_spec_name'] .  ' | ';
	// $afficher_log.= '(Niveau : ' . $_SESSION['group_admin_spec'] .  ') | ';
	include_spip('inc/utils');
	$afficher_log.= '<a href="'.generer_url_entite(121,'rubrique','logout=y').'" title="Me déconnecter de mon compte">Déconnexion</a> - </div>';
	 
	 echo $afficher_log;
}
*/

// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
// Affichage tous les paramètres de la SESSION (pour tests)
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF

function montre_var_session ()
{
	echo '<p>
	nom_admin_spec = '. $_SESSION['nom_admin_spec'].
	'<br />id_admin_spec = '. $_SESSION['id_admin_spec'] .
	'<br />group_admin_spec = '. $_SESSION['group_admin_spec'] .
	'<br />group_admin_spec_name = '. $_SESSION['group_admin_spec_name'] .
	'<br />lieu_admin_spec = '. $_SESSION['lieu_admin_spec'] .
	'<br />lieu_admin_spec_name = '. $_SESSION['lieu_admin_spec_name'] ;
	
	
echo '<br /><pre>';
print_r($_SESSION['group_admin_spec']);
echo '</pre>';
}	


// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
/* Introduction du formulaire d'authentification DANS pages SPIP pour
indentification des Spectateurs SANS quitter la page. Basé sur function "test_acces_page_auth" */
// FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF
function test_spectateur_acces_in_spip ($niveau_requis) 
{
	require 'agenda/inc_var.php';
	if (isset($_SESSION['group_admin_spec']) AND ($_SESSION['group_admin_spec'] == $niveau_requis OR $_SESSION['group_admin_spec'] == '5'))
	{
		echo '' ;
	}
	else // La variable "$_SESSION['group_admin_spec']" n'existe pas
	{	
		include ("agenda/auth/auth_formulaire_spectateur_in_spip.php");
	}
}

?>
