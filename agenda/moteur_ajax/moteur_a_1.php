<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Moteur asynchrone DLP</title>

<link type="text/css" href="../js/css/theme/ui.all.css" rel="Stylesheet" />	

<script type="text/javascript" src="../js/css/jquery-1.3.1.js"></script>
<script type="text/javascript" src="../js/jquery.ui.all.js"></script>

<script>

//window.loadFirebugConsole();
$(document).ready(function (){
	

 

	/* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	Cette fonction est appelée par les "mouvements" du visiteur
	Elle va chercher les variables dans tous les champs du formulaire, 
	et les envoie au PHP qui va ensuite les tester (==0 ?)
	+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
	function appel_php() {
		//console.debug("Fonction appel_php lancée") ;
		
		$('#nbre_resultats_id').fadeOut(100);
		//$('#suggestions').fadeOut(100);
		// ---------------------------------------------
		// On récupère toutes les valeurs des champs
		// ---------------------------------------------
		
		
		// RAZ champ d'info
		$("#montrer_selection").html(""); 
		votre_selection = '';
		

		// Date Début
		selecteur_date_in = $("#selecteur_date_in").val();
		//console.debug("Date fin : "+selecteur_date_out) ;
		if(selecteur_date_in) {
			votre_selection+= "Date de début : "+selecteur_date_in+"<br />";
		}
		else {
			//votre_selection+= "Aucune date de début choisie<br />";
			selecteur_date_in = 'non_selct' ;
		}

		// Date fin
		selecteur_date_out = $("#selecteur_date_out").val();
		//console.debug("Date fin : "+selecteur_date_out) ;
		if(selecteur_date_out) {
			votre_selection+= "Date de fin : "+selecteur_date_out+"<br />";
		}
		else {
			//votre_selection+= "Aucune date de fin choisie<br />";
			selecteur_date_out = 'non_selct' ;
		}



		// LIEU
		valeur_lieu_recup = $("#selecteur_lieu").val();
		//console.debug("lieu : "+valeur_lieu_recup) ;
		if(valeur_lieu_recup!='non_selct') {
			votre_selection+= "Lieu : "+valeur_lieu_recup+"<br />";
		}
		else {
			//votre_selection+= "Aucun lieu choisi<br />";
		}
		
	
		// REGION
		valeur_region_recup = $("#selecteur_region").val();
		//console.debug("Region : "+valeur_region_recup) ;
		if(valeur_region_recup!='non_selct') {
			votre_selection+= "region : "+valeur_region_recup+"<br />";
		}
		else {
			//votre_selection+= "Aucune region choisie<br />";
		}
		

		// GENRE
		valeur_genre_recup = $("#selecteur_genre").val();
		//console.debug("Genre : "+valeur_genre_recup) ;
		if(valeur_genre_recup!='non_selct') {
			votre_selection+= "Genre : "+valeur_genre_recup+"<br />";
		}
		else {
			//votre_selection+= "Aucun genre choisi<br />";
		}
		
		// TEXTE LIBRE
		valeur_txt_libre_recup = $("#chp_txt_libre").val();
		//console.debug("Genre : "+valeur_txt_libre_recup) ;
		if(valeur_txt_libre_recup!='') {
			votre_selection+= "Texte libre : "+valeur_txt_libre_recup+"<br />";
		}
		else {
			//votre_selection+= "Aucun texte libre choisi<br />";
		}
		
		
		// AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
		// Afficher le message "Votre sélection) :
		// AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
		$("#montrer_selection").append("<strong>Votre sétection</strong><br />"+votre_selection+"");
		$("#montrer_selection").fadeIn("slow");



		// ---------------------------------------------


		// ---------------------------------------------
		// Requête AJAX
		// ---------------------------------------------
		$.post("requete_a_1.php", {
		lieu: ""+valeur_lieu_recup+"", 
		region: ""+valeur_region_recup+"",
		genre: ""+valeur_genre_recup+"",
		chaine_txt_libre: ""+valeur_txt_libre_recup+"",
		date_in: ""+selecteur_date_in+"",
		date_out: ""+selecteur_date_out+""
		}, function(data){
			var response_se = eval("(" + data + ")");
			//console.log(response_se.messages.message[0].dlp_list_events);
			//console.log(response_se.messages.message[0].nombre_resultats);

			// Nombre de résultats
			// ---------------------------------
			$('#nbre_resultats_id').fadeIn(300);
			if(response_se.messages.message[0].nombre_resultats>0) {
				$('#nbre_resultats_id').html(response_se.messages.message[0].nombre_resultats+" evenement(s)");
			}
			else {
				$('#nbre_resultats_id').html("Aucun résultat");
			}
			
			// Liste de proposition de titres
			// ---------------------------------
			if(response_se.messages.message[0].dlp_list_events.length != 0) {
				var response_se = eval("(" + data + ")");
	
				$('#suggestions').fadeIn(200);
				$('#autoSuggestionsList').html(response_se.messages.message[0].dlp_list_events);
			}
		},"json");



	/* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */		
	}
	/* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */


	
	/* Sélecteur de DATE IN
	********************* */
	// Loader l'UI Datepicker
	$("#selecteur_date_in").datepicker();
	/* CModification des paramètres (Attention à la sybntaxe ! http://www.nabble.com/Syntax-confusion-td19960925s27240.html) */
	$("#selecteur_date_in").datepicker("change", {dateFormat: "dd-mm-yy"});
	$("#selecteur_date_in").datepicker("change", {dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa']});
	$("#selecteur_date_in").datepicker("change", {monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']});
	$("#selecteur_date_in").datepicker("change", {monthNamesShort: ['Janv','Fév','Mars','Avril','Mai','Juin','Juillet','Août','Sept','Oct','Nov','Déc']});
	$("#selecteur_date_in").datepicker("change", {changeMonth: true});
	$("#selecteur_date_in").datepicker("change", {changeYear: true});
	$("#selecteur_date_in").datepicker("change", {yearRange: '2007:2011'});	


	// Imposer une date de DEBUT > à date de FIN
	$("#selecteur_date_in").change(function () {
		date_in = $("#selecteur_date_in").val();

		day_debut = date_in.substring(0,2);
		month_debut = (date_in.substring(3,5))-1;
		year_debut = date_in.substring(6,10);
		//alert ('d= '+day_debut+ ' m= '+month_debut+ ' y= '+year_debut) ;
		date_to_out = new Date();
		date_to_out.setDate(day_debut);
		date_to_out.setMonth(month_debut);
		date_to_out.setFullYear(year_debut);
		
		$("#selecteur_date_out").datepicker("change", {minDate: date_to_out});

		// Appel PHP
		appel_php() ;
	});
	

	
	/* Sélecteur de DATE OUT
	********************* */
	// Loader l'UI Datepicker
	$("#selecteur_date_out").datepicker();
	/* CModification des paramètres (Attention à la sybntaxe ! http://www.nabble.com/Syntax-confusion-td19960925s27240.html) */
	$("#selecteur_date_out").datepicker("change", {dateFormat: "dd-mm-yy"});
	$("#selecteur_date_out").datepicker("change", {dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa']});
	$("#selecteur_date_out").datepicker("change", {monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']});
	$("#selecteur_date_out").datepicker("change", {monthNamesShort: ['Janv','Fév','Mars','Avril','Mai','Juin','Juillet','Août','Sept','Oct','Nov','Déc']});
	$("#selecteur_date_out").datepicker("change", {changeMonth: true});
	$("#selecteur_date_out").datepicker("change", {changeYear: true});
	$("#selecteur_date_out").datepicker("change", {yearRange: '2007:2011'});

	$("#selecteur_date_out").change(function () {
		
		// Imposer une date de DEBUT < à date de FIN		
		date_out = $("#selecteur_date_out").val();

		day_debut = date_out.substring(0,2);
		month_debut = (date_out.substring(3,5))-1;
		year_debut = date_out.substring(6,10);
		//alert ('d= '+day_debut+ ' m= '+month_debut+ ' y= '+year_debut) ;
		date_to_in = new Date();
		date_to_in.setDate(day_debut);
		date_to_in.setMonth(month_debut);
		date_to_in.setFullYear(year_debut);
		
		$("#selecteur_date_in").datepicker("change", {maxDate: date_to_in});
		
		// Appel PHP
		appel_php() ;
	});




	
	/* Sélecteur de LIEU
	********************* */
	$("#selecteur_lieu").change(function() {
		valeur_lieu = $(this).val();
		  /*if (valeur_lieu != 0) {
			 // console.debug(valeur_lieu) ;
			  appel_php() ;
		  }*/
		appel_php() ;
	});
	
	
	/* Sélecteur de REGION
	********************* */
	$("#selecteur_region").change(function() {
		valeur_region = $(this).val();
		appel_php() ;
	});
	
	
	/* Sélecteur de GENRE
	********************* */
	$("#selecteur_genre").change(function() {
		valeur_genre = $(this).val();
		appel_php() ;
	});
	
	
	
	/* TEST CHP LIBRE
	********************* */
	$("#chp_txt_libre").keyup(function() {
		chaine_txt_libre = $(this).val();
		//console.log("mot = "+chaine_txt_libre);
		
		appel_php() ;
	});



		
	/* Champ texte libre
	********************* */
	$("#chp_txt_libre").keyup(function lookup(valeur_chp_txt_libre) {
	valeur_chp_txt_libre = $(this).val();
	//console.log("Lookup = "+valeur_chp_txt_libre);
	if(valeur_chp_txt_libre.length ==0) {
		// Hide the suggestion box.
		$('#suggestions').fadeOut(1000);
	} else {
		
		}
	}) // # fct lookup

	
	/* Comportement quand on clique sur un lien de la liste des termes proposés 
	$("#chp_txt_libre").blur(function fill() {
		valeur_de_liste_cliquee = $(this).val();
	});*/


	

});

function fill(thisValue) {
	//$("input[name='chp_txt_libre']").html("rr");
	$("input[name='chp_txt_libre']").val(thisValue)
	setTimeout("$('#suggestions').hide();", 200);
	//alert(thisValue);
}	





</script>

<link href="../../squelettes/styles_tur.css" rel="stylesheet" type="text/css" media="screen" />

<style type="text/css">
body {
	font-size: 12px;
	margin:50px;
}

#montrer_selection, #nbre_resultats_id, .suggestionsBox {
	margin: 10px 0px 0px 0px;
	padding: 5px 10px 5px 10px;
	background-color: #009A99;
	-moz-border-radius: 7px;
	-webkit-border-radius: 7px;
	border: 1px solid #000;
	color: #000;
	width: 200px;
	display: none;
}

#montrer_selection {
}

#nbre_resultats_id {
	float:right;
}

.suggestionsBox {
	position: relative;
	left: 160px;
	margin: 10px 0px 0px 0px;
	padding: 1px 10px 5px 10px;
	width: 300px;
}

.suggestionList {
	margin: 0px;
	padding: 0px;
}

.suggestionList li {
	font-size: 11px;
	padding: 1px;
	list-style-image: url(puce_transpa.gif);
	list-style: none;
	background: transparent url(puce_transpa.gif) no-repeat scroll left 2px;
}



.suggestionList li:hover {
	color: #FFFFFF;
}

</style>
</head>
<body>

<p>

<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require '../inc_db_connect.php';
require '../inc_var.php';
// require 'agenda/inc_db_connect.php';
require '../inc_fct_base.php';
require '../calendrier/inc_calendrier.php';

/*
http://docs.jquery.com/Events/change#examples

Doc pour autocomplete :
http://www.dator.fr/les-requetes-ajax-avec-jquery/
http://www.dynamicajax.com/fr/JSON_AJAX_Web_Chat-.html
*/ ?>
  
  
  
</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<form id="form_moteur_dlp_ajax" name="form_moteur_dlp_ajax" method="post" action="">
	<table width="650" border="0" align="center" cellpadding="10" cellspacing="0" bgcolor="#EEEEEE">
  <tr>
    <td>


	<div id="nbre_resultats_id" style="display: none;"></div>
	<div id="montrer_selection"></div>

	<?php
	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_genre
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="genre_event" id="selecteur_genre">
	<option value="non_selct">tous les genres</option>';
	foreach($genres as $cle_genre => $element_genre)
	{
		echo '<option value="' . $cle_genre .'"';		
		// Faut-il preselectionner
		if (isset($genre_event) AND $genre_event == $cle_genre)
		{
			echo 'selected';
		}
		$max=34; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
		$element_genre = raccourcir_chaine ($element_genre,$max); // retourne $chaine_raccourcie
		echo '>'.$element_genre.'</option>';
	}
	echo '</select> ';



	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_lieu
	// sélectionner uniquement ceux qui sont en ordre de paiement
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="lieu_event" id="selecteur_lieu">
	<option value="non_selct">tous les lieux/partenaires</option>';
	
	$reponse_2 = mysql_query("SELECT id_lieu, nom_lieu FROM ag_lieux 
	WHERE cotisation_lieu > CURDATE() ORDER BY nom_lieu") or die (mysql_error());
	
	while ($donnees_2 = mysql_fetch_array($reponse_2))
	{
		// Raccourcir la chaine :
		$nom_lieu_court = $donnees_2['nom_lieu'] ;
		$max=34; // Longueur MAX de la cha&icirc;ne de caract&egrave;res
		$chaine_raccourcie = raccourcir_chaine_net ($nom_lieu_court,$max); // retourne $chaine_raccourcie
		
		echo '<option value="' . $donnees_2['id_lieu'] .'"';		
		// Faut-il pr&eacute;-s&eacute;lectionner
		if (isset($lieu_event_form) AND $donnees_2['id_lieu'] == $lieu_event_form )
		{ echo ' selected="selected" '; }
		echo '>'.$chaine_raccourcie.'</option>';
	}
	echo '</select> ';
	
	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_region
	// on pourrait rajouter multiple="multiple"
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo '<select name="ville_event" id="selecteur_region">
	<option value="non_selct">toutes les villes</option>';
	foreach($regions as $cle_region => $element_region)
	{
		echo '<option value="' . $cle_region .'"';		
		// Faut-il preselectionner
		if (isset($ville_event) AND $ville_event == $cle_region)
		{
			echo 'selected';
		}
		echo '>'.$element_region.'</option>';
	}
	echo '</select> <br /> <br />';


	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// selecteur_date_in et selecteur_date_out
	// http://docs.jquery.com/UI/Datepicker
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	echo'La date de début : <input type="text" id="selecteur_date_in" /> ' ;
	echo'La date de fin : <input type="text" id="selecteur_date_out" /> <br /> <br /> ' ;
	
	

	?>
		
	
	
	<!-- Champ pour le texte libre -->	
	<div>Rechercher un événement : <input name="chp_txt_libre" type="text" size="30" value="" id="chp_txt_libre" /></div>

	<div class="suggestionsBox" id="suggestions" style="display: none;">
		<img src="upArrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
		
		<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
	</div>
		
	  </td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>

<p>&nbsp;</p>
<p>Voici l&rsquo;&eacute;bauche du moteur asynchrone.<br />
  Le moteur ne fonctionne pour le moment que sur FireFox (et en  local chez moi sur IE7).<br />
  Les accents ne sont pas encore reconnus dans le champ de  texte libre.</p>
</body>
</html>
