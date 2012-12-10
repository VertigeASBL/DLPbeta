<?php
/*
	Cette fonction convertit un type d'évenement en phrase pour afficher dans le log.
*/
function getLog ($type) {
	switch($type) {
		case 'avis':
			return 'a ajout� un avis a';
		case 'level':
			return 'a chang� de niveau';
		case 'concours':
			return 'participe au concours';
		case 'favoris':
			return 'a mis en favoris';
		case 'vu':
			return 'a vu et aim�';
		case 'nouveau':
			return 'est arriv� sur DLP';
		case 'profil':
			return 'a mis �jour son profil';
		default: 
			return 'Type d\'activit� inconu';
	}
}


/*
	Cette fonction va cr�er un log.
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