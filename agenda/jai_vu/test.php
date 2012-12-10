<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>

<script type="text/javascript">
<!--
var popup_jai_vu = function popup_jai_vu(Adresse_de_la_page,Nom_de_la_fenetre) {
	ma_fenetre = window.open(Adresse_de_la_page,Nom_de_la_fenetre,'height=500,width=500,toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,directories=no,status=yes,top=100px,left=250px');
	/* Donner le focus */
	if(ma_fenetre.window.focus){
		ma_fenetre.window.focus();
	}
}
-->
</script>


</head>

<body>

<?php
// http://www.zone-webmasters.net/publications/26-ouverture-de-popup-en-javascript.html

$id_dddddddddddd = 2125 ;

$adresse_et_parametres = 'jai_vu_popup.php?id=' . $id_dddddddddddd ;

echo'<a href="#" onClick="popup_jai_vu' ;
echo "('" . $adresse_et_parametres . "','J ai vu et aimé !!! ');" ;
echo'">Lien Popup</a><br /><br />' ;

?>
</body>
</html>
