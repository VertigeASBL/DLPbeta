
<?php
 
function affiche_menu_site_spectateur ($chemin_vers_page)
{
	$menu_tab = '<a href="' . $chemin_vers_page . '-Une-">A la Une</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Agenda-">Agenda</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Interviews-">Interviews</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Critiques-">Critiques</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Lieux-partenaires-">Lieux et partenaires</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Contactez-nous,70-">Infos</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Concours,95-">Concours</a>' ;
	$menu_tab.= '<a href="' . $chemin_vers_page . '-Communaute-des-spectateurs-">Spectateurs</a>' ;
	
	return $menu_tab ;
}
?>
