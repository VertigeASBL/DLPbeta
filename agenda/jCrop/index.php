<?php 
session_start();

/* Inclusion de la base de donnée */
require '../inc_var.php';
require '../inc_db_connect.php';

/* Fonction pour recadrer une image avec PHP. */
include_once('function_crop.php');
?>
<html>
<head>
	<title>Recadrer l'image</title>
	<link rel="stylesheet" href="css/jquery.Jcrop.min.css" type="text/css" />
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.Jcrop.min.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#cropbox').Jcrop({
			aspectRatio: 0.7,
			onSelect: updateCoords,
			boxWidth: 450, 
			boxHeight: 400 
		});

		function updateCoords(c) {
			$('#x').val(c.x);
			$('#y').val(c.y);
			$('#w').val(c.w);
			$('#h').val(c.h);
		}
	});
	</script>
</head>
<body>

	<?php
	/* Protection contre le reformatage d'autre image: la personne à t'elle le droit de modifier cette image ? */

	/* On récupère l'id de l'évement dans le nom du fichier */
	$id_event = explode('_', $_GET['source']);
	$id_event = $id_event[2];

	/* On vérifie que l'image appartient a l'organisateur */
	$reponse = mysql_query("SELECT lieu_event FROM $table_evenements_agenda WHERE id_event = '$id_event'");
	$donnees = mysql_fetch_array($reponse);
	if (! $donnees || $donnees['lieu_event'] != $_SESSION['lieu_admin_spec']) {
		echo '<div class="alerte">Vous ne pouvez pas modifier un événement rattaché à un autre lieu culturel</div><br>';
		exit();
	}


	/* On découpe l'image */
	if (isset($_POST['source'])) {
		crop_image($_POST['source']);
	}

	/* Si une image à été envoyer pour être recadrée. On affiche l'image recadrée. */
	if (isset($_POST['source'])): 
		echo '<img src="'.str_replace('.tmp', '', $_POST['source']).'" alt="Prévisualisation" />';
	?>
	<br />
	<a href="javascript: window.close();">[Fermer]</a>
	<?php
	/* Si l'image à déjà été recadré on affiche une erreur. */
	elseif (!file_exists($_GET['source'])): 
		echo '<p>Cet image à déjà été recadrée.</p>';
	/* Sinon, on affiche le système de recadrage. */
	else: ?>
	<img src="<?php echo urldecode($_GET['source']); ?>" alt="jCrop" id="cropbox" />
	
	<form action="index.php?source=<?php echo urlencode($_GET['source']); ?>" method="post">
		<input type="hidden" id="source" name="source" value="<?php echo urldecode($_GET['source']); ?>" />
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />
		<input type="submit" value="recadrer" />
	</form>

<?php endif; ?>
</body>
</html>