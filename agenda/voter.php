<?php

	

	require '../agenda/inc_var.php';
	require '../agenda/inc_db_connect.php';	
	require '../agenda/inc_fct_base.php';
	$ip_jai_vu = $_SERVER['REMOTE_ADDR'] ;
	mysql_query("INSERT INTO ag_jai_vu (id_event_jai_vu,timestamp_jai_vu,ip_jai_vu)  VALUES (100,NOW( ),'$ip_jai_vu')") or die ('Erreur 2 -- ' . mysql_error());
		
// Le visiteur a appuy� sur le bouton "Voter"
	if (isset($_POST['action']) AND ($_POST['action'] == 'voter')) 
	{
		$id_event_en_cours=$_GET['id'];
		// V�rifier si le visiteur a d�ja vot� pour cet �v�nement. Le test porte sur IP, Event et date (1 vote par jour) 
		$timestamp_jai_vu = time();
		$timestamp_hier = time()-(24*3600);
		$ip_jai_vu = $_SERVER['REMOTE_ADDR'] ;
		$navigateur_jai_vu = $_SERVER['HTTP_USER_AGENT'] ;// Type de navigateur
		
		$reponse_test_2 = mysql_query("SELECT COUNT(*) AS nombre_reponse FROM ag_jai_vu WHERE
		id_event_jai_vu = '$id_event_en_cours' AND		
		timestamp_jai_vu > SUBDATE( timestamp(now()), INTERVAL 24 HOUR) AND
		ip_jai_vu = '$ip_jai_vu' ") or die ('Erreur 1 -- ' . mysql_error());
	//	

		$donnees_test_2 = mysql_fetch_array($reponse_test_2);
		
		if ($donnees_test_2['nombre_reponse'] > 0) 
		//if (1==5)
		{
			echo '
			<br /> <br />
			<div align="center">
			<strong>Vous avez d�j� vot�</strong> pour cet �v�nement il y a moins de 24 heures. <br /> 
			Vous pouvez voter pour d\'autres �v�nements, ou m�me revoter pour celui-ci en revenant demain. <br /> <br />
			
			L\'�quipe de <em><a href="http://www.demandezleprogramme.be/">Demandezleprogramme !</a></em>
			</div>' ;
		}
		else
		{
			echo '
			<br /> <br />
			<div align="center">Merci d\'avoir vot� pour cet �v�nement ! <br /> <br />
			
			L\'�quipe de <em><a href="http://www.demandezleprogramme.be/">Demandezleprogramme !</a></em>
			</div>' ;
		
			// Mettre � jour la Table "ag_jai_vu" afin de tenir compte du vote
			mysql_query("INSERT INTO ag_jai_vu (id_event_jai_vu,timestamp_jai_vu,ip_jai_vu) 
			VALUES ('$id_event_en_cours',NOW( ),'$ip_jai_vu')") or die ('Erreur 2 -- ' . mysql_error());
		
			// Mettre � jour la le nombre de votes recueillis par l'�v�nement
			mysql_query("UPDATE ag_event SET jai_vu_event = jai_vu_event+1 WHERE `id_event` = '$id_event_en_cours' LIMIT 1 ");

		}
	}
		
?>


