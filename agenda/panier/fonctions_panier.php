<?php 
/*
	Ce fichier contient les fonctions de gestion du panier de DLP (Les favoris des spectateurs).
	Creer par Debondt Didier.

	Ce fichier utilise les fonctions native de SPIP pour faire des requêtes SQL.
*/

/*
	Cette fonction sert à ajouter un élément au panier
*/
function ajouter_panier ($id_spectateur, $id_evenement) {
	/* On test les éléments, au cas ou... */
	if (empty($id_spectateur) or !is_numeric($id_spectateur)) return false;
	if (empty($id_evenement) or !is_numeric($id_evenement)) return false;
	if (statut_panier($id_spectateur, $id_evenement)) return false;
	
	$sql = sql_insertq('ag_panier', array('id_spectateur' => $id_spectateur, 'id_event' => $id_evenement));

	/* On log l'ajout en favoris */
	include_once('agenda/activite/activite_fonctions.php');
	activite_log('favoris', $id_evenement);

	/* On renvoie quand même quelques choses */
	return true;
}

/*
	Cette fonction enlève un élément du panier
*/
function enlever_panier($id_spectateur, $id_evenement) {

	/* On test les éléments, au cas ou... */
	if (empty($id_spectateur) or !is_numeric($id_spectateur)) return false;
	if (empty($id_evenement) or !is_numeric($id_evenement)) return false;

	/* On supprime l'entrer de la base de donnée */
	$sql = sql_delete('ag_panier', 'id_spectateur='.$id_spectateur.' AND id_event='.$id_evenement);

	/* On renvoie quelques choses. */
	return true;
}

/*
	Cette fonction renvoie le statut: true si l'utilisateur a mis l'événement en favoris. False sinon.
*/
function statut_panier($id_spectateur, $id_evenement) {
	/* On test les éléments, au cas ou... */
	if (empty($id_spectateur) or !is_numeric($id_spectateur)) return false;
	if (empty($id_evenement) or !is_numeric($id_evenement)) return false;

	$sql = sql_fetsel('id', 'ag_panier', 'id_spectateur='.$id_spectateur.' AND id_event='.$id_evenement);

	if ($sql) return true;
	else return false;
}

/*
	Cette fonction compte le nombre de personne qui suive un événement
*/
function nombre_suivi($id_evenement) {
	return sql_countsel('ag_panier', 'id_event='.$id_evenement);
}
?>