
<?php

require 'tricheurs_array.php';


// Affichage de la liste des tricheurs :
echo '<p>&nbsp;</p>
<h2>La liste des tricheurs</h2>
<ul>';
foreach ($liste_tricheurs as $un_tricheur)
{
	echo '
	<li>' . $un_tricheur . '</li>' ;
}
echo '
</ul>';


?>
