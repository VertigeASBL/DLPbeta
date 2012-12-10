<?php
/*
	Cette fonction convertit un type d'Ã©venement en phrase pour afficher dans le log.
*/
function getLog ($type) {
	switch($type) {
		case 'avis':
			return 'a ajouté un avis a';
		case 'level':
			return 'a changé de niveau';
		case 'concours':
			return 'participe au concours';
		case 'favoris':
			return 'a mis en favoris';
		case 'vu':
			return 'a vu et aimé';
		case 'nouveau':
			return 'est arrivé sur DLP';
		case 'profil':
			return 'a mis à jour son profil';
		default: 
			return 'Type d\'activité inconu';
	}
}


/*
	Cette fonction va créer un log.
*/
function activite_log ($type, $id_event = null, $id_concours = null) {
	$champs = array(
					'id_spectateur' => $_SESSION['id_spectateur'],
					'id_event' => $id_event,
					'id_concours' => $id_concours,
					'type' => $type
		);

	sql_insertq('ag_activite', $champs);
}


?>