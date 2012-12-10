
	<?php
	if (isset($_SESSION['group_admin_spec']) AND ($_SESSION['group_admin_spec'] == '1'))
	{
		echo '<br /><div align="center">' . 
		$_SESSION['nom_spectateur'] . ' - 
		<a href="agenda/auth/auth_log_off.php">Déconnexion</a>
		</div> ' ;
	}
	?>
