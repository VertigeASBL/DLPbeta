<?php
	// require '../inc_db_connect.php';
	
	
	
	$subject= 'Donnez votre avis' ;
	$message= '<html>
	<head>
	<title>Donnez votre avis</title>
	</head>
	<body>
	<table align="center" width="600" border="0" cellspacing="0" cellpadding="10">
  <tr>
    <td colspan="2">
	<h3 style="color:#009A99; font-family:Arial; font-size:18px; ">Vous avez gagné des places sur www.demandezleprogramme.be<br />
	Laissez votre avis sur cet événement !</h3></td>
  </tr>
  <tr>
    <td valign="top" ><img src="http://www.demandezleprogramme.be/agenda/concours/rappel_avis/perso_dlp.gif" alt="illustration" /></td>
    
	<td valign="top" ><p style="color:#009A99; font-family:Arial; font-size:13px; ">Grâce à <em>demandezleprogramme</em>, vous avez remporté des places pour aller voir l\'événement 
	DDDDDDDDDDD.</p>
	
	<p style="color:#009A99; font-family:Arial; font-size:13px; ">Aidez-nous à développer la communauté des spectateurs en déposant votre avis sur le site : DDDDDDDDDDD;</p>
	
	<p style="color:#009A99; font-family:Arial; font-size:13px; ">En vous remerciant d\'avance,</p>
	
	<p style="color:#009A99; font-family:Arial; font-size:13px; "><em>L\'équipe de demandezleprogramme.be</em></p></td>
  </tr>
  <tr>
    <td colspan="2">
	<p align="center" style="color:#666666; font-family:Arial; font-size:11px; ">	Vertige asbl <br />
<a href="mailto:info@demandezleprogramme.be">info@demandezleprogramme.be</a> <br /> 
	<a href="http://www.demandezleprogramme.be">www.demandezleprogramme.be</a> <br />
	Visitez également <a href="http://www.comedien.be">www.comedien.be</a> et 
	<a href="http://www.vertige.org">www.vertige.org</a></p>
	</td>
  </tr>
</table>
	
	</body>
	</html>' ;
	
	$retour_email_moderateur = 'info@demandezleprogramme.be' ; // 8888888888888888888888
	
	$entete= "Content-type:text/html\nFrom:" . $retour_email_moderateur . "\r\nReply-To:" . $retour_email_moderateur ;
	$sujet = 'Merci de déposer votre avis sur demandezleprogramme.be' ;
 mail_beta('renaud.jeanlouis@gmail.com',$sujet,$message,$entete,$email_retour_erreur);



	echo $message ;
	
?>
