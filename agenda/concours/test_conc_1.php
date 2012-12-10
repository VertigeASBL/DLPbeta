<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>

<?php

require '../inc_var.php';
require '../inc_db_connect.php';
require '../inc_fct_base.php';


			// **********************************************************************************
			// Traitement de la liste des joueurs pour ce lot
			// **********************************************************************************
			$liste_joueurs_lot_en_cours = array() ;
			$reponse_joueurs = mysql_query("SELECT * FROM $table_ag_conc_joueur 
			WHERE id_fiche_conc_joueur = '6' AND  lot_conc_joueur = '0'") or die (mysql_error());
			while ($donnees_joueurs = mysql_fetch_array($reponse_joueurs))
			{
				// Constituer un Array pour ce LOT
				array_push ($liste_joueurs_lot_en_cours, $donnees_joueurs['id_conc_joueur']);
			}
			
			shuffle ($liste_joueurs_lot_en_cours); // Shuffle du tableau des participants
			$nombre_participants = count ($liste_joueurs_lot_en_cours);
			
			echo $nombre_participants	;
						
?>


</body>
</html>
