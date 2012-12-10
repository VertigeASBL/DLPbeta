<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Lister le contenu du r&eacute;pertoire vid&eacute;o</title>
<link href="../css_back_agenda.css" rel="stylesheet" type="text/css">
</head>

<body>

<div id="head_admin_agenda"></div>

<h1>Liste du contenu du répertoire vidéo</h1>

<div class="menu_back">
<a href="lister_video_folder.php">Actualiser la page</a> |
<a href="listing_lieux_culturels.php" >Listing des lieux culturels</a> | 
<a href="index_admin.php">Menu Admin</a>
</div>

<?php

if ($handle = opendir('../videos')) {
echo '<table width="750" border="1" align="center" cellpadding="1" cellspacing="0" class="data_table" ><tr>
    <th>Nom</th><th>Poids</th><th>Date</th></tr>' ;

    while (false !== ($file = readdir($handle)))
	{
        if ($file != "." && $file != "..") 
		{
            echo '<tr class="tr_hover"><td align="right"><a href="../videos/'.$file . '">'.$file . '</a></td>
			<td>' . (filesize('../videos/'.$file)/1000000) . ' Mo </td>
			<td>'. date("d F Y H:i:s", filemtime('../videos/'.$file)) . ' </td></tr>' ;
        }
    }
    closedir($handle);
	echo '</table>' ;
}
?> 
</body>
</html>
