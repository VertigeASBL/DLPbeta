<?php 
session_start();

/* Inclusion de la base de donnée */
require '../inc_var.php';
require '../inc_db_connect.php';

/* Fonction pour recadrer une image avec PHP. */
include_once('function_crop.php');

/* On extrait les informations de l'URL */
$id_event = explode('_', $_GET['source']);
$type = $id_event[1];
$id_event = $id_event[2];


/* On fix le ratio de l'image. Si le ratio ne fait pas partie de la liste, on jete la personne par la fenêtre */
if ($type == 'spectateurs/spect') $ratio = '1.5';
else $ratio = '0.7';
?>
<html>
<head>
	<title>Recadrer l'image</title>
	<link rel="stylesheet" href="css/jquery.Jcrop.min.css" type="text/css" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.Jcrop.min.js"></script>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#cropbox').Jcrop({
			aspectRatio: <?php echo $ratio; ?>,
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

	/* Si l'image envoyé n'appartient pas a un spectateur, on vérifie que le lieu peu modifier l'image */
	
	if ($type != 'spectateurs/spect') {
		/* On vérifie que l'image appartient a l'organisateur */
		$reponse = mysql_query("SELECT lieu_event FROM $table_evenements_agenda WHERE id_event = '$id_event'");
		$donnees = mysql_fetch_array($reponse);
		if (! $donnees || $donnees['lieu_event'] != $_SESSION['lieu_admin_spec']) {
			echo '<div class="alerte">Vous ne pouvez pas modifier un événement rattaché à un autre lieu culturel</div><br>';
			exit();
		}
	}
	/* Sinon, il s'agit d'une image de spectateur, on mofifie. $id_event contient en faite l'id du spectateur */
	else {
		if ($_SESSION['id_spectateur'] != $id_event) {
			echo '<div class="alerte">Erreur !</div><br>';
			exit();
		}
		if ($_SESSION['id_spectateur'] == 0) exit();
	}

	/* On découpe l'image */
	if (isset($_POST['source'])):
		if ($type == 'spectateurs/spect') crop_image($_POST['source'], 93, 62);
		else crop_image($_POST['source'], 161, 230);
		
		/* On affiche l'image recadrée. */
		echo '<img src="'.str_replace('.tmp', '', $_POST['source']).'?time='.time().'" alt="Prévisualisation" />';
		if ($type == 'spectateurs/spect') 
			echo '
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						 window.opener.parent.location.href = "../../-Modifier-mes-infos-spectateur-";
					});
				</script>';
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