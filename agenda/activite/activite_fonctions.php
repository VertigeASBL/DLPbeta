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
			return 'a ajout� � son agenda';
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
function activite_log ($type, $id_event = 'null', $id_concours = 'null') {
	$id_spectateur = isset($_SESSION['id_spectateur']) && $_SESSION['id_spectateur'] ? (int) $_SESSION['id_spectateur'] : 0;
/*	$champs = array(
					'id_spectateur' => $id_spectateur,
					'id_event' => $id_event,
					'id_concours' => $id_concours,
					'type' => $type
		);
*/
	if ($id_spectateur)
		mysql_query('INSERT INTO ag_activite VALUES(null, '.$id_spectateur.', \''.$id_event.'\', \''.$id_concours.'\', \''.$type.'\', CURRENT_TIMESTAMP() )') or die(mysql_error());
}


?>