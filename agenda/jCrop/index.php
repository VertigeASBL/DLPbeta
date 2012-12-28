<?php 
session_start();
include_once('function_crop.php');

/*
	Protection contre le reformatage d'autre image: la personne à t'elle le droit de modifier cette image ?
*/

/* On récupère l'id de l'évement dans le nom du fichier */
$id_event[3] = explode('_', $_GET['source']);

if (isset($_POST['source'])) {
	crop_image($_POST['source']);
}
?>
<html>
<head>
	<title>Recadrer l'image</title>
	<link rel="stylesheet" href="css/jquery.Jcrop.min.css" type="text/css" />
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.Jcrop.min.js"></script>
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

<?php if (!isset($_POST['source'])): ?>
	<img src="<?php echo urldecode($_GET['source']); ?>" alt="jCrop" id="cropbox" />
	
	<form action="index.php?source=<?php echo urlencode($_GET['source']); ?>" method="post">
		<input type="hidden" id="source" name="source" value="<?php echo urldecode($_GET['source']); ?>" />
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />
		<input type="submit" value="recadrer" />
	</form>
	<?php else: 
		echo '<img src="'.str_replace('.tmp', '', $_POST['source']).'" alt="Prévisualisation" />';
	?>
	<br />
	<a href="javascript: window.close();">[Fermer]</a>
<?php endif; ?>
</body>
</html>