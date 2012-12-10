<?
	require('conf.php');

	//--- Connexion à la DB
	$db_link = mysql_connect($sql_server, $sql_user, $sql_passw);
	if (! $db_link) {
		echo 'Connexion impossible à la base de données ',$sql_bdd,' sur le serveur ',$sql_server;
		exit;
	}
	mysql_select_db($sql_bdd, $db_link);

	//--- Limiter l'accès aux admins
	session_start();
	$k = isset($_SESSION['cnpromen']) ? $_SESSION['cnpromen'] : '';

	if (is_numeric($k) && strlen($k) == 16 && isset($tci) && is_numeric($tci)) {
		$chn = $_SERVER['REMOTE_ADDR'];

		$sql = "SELECT id_membre,acces,temps FROM c_membres WHERE protect='$k' AND adrip='$chn' AND temps='$tci'";
		$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

		if ($data = mysql_fetch_array($req, MYSQL_ASSOC))
			$_SESSION['cntimeci'] = $tci;
		else
			if (isset($_SESSION['cntimeci']) && $tci == $_SESSION['cntimeci'] && (! isset($oper) || $oper == '')) {
				$_SESSION['cntimeci'] = rand();

				$sql = "SELECT id_membre,acces,temps FROM c_membres WHERE protect='$k' AND adrip='$chn'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

				$data = mysql_fetch_array($req, MYSQL_ASSOC);
			}
		if ($data) {
			$tci = time();
			if ($tci < $data['temps'] + 3600) {
				$protectacces = $data['acces'];
				$protectadmin = $data['id_membre'];

				$sql = "UPDATE c_membres SET temps='$tci' WHERE id_membre='$protectadmin'";
				$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));

				$chn = '';
			}
			else $chn = 't';
		}
		else $chn = 's';
	}
	else $chn = 'p';

	if ($chn) {
		if (is_numeric($k) && strlen($k) == 16) {
			$tci = time() >> 1;
			$sql = "UPDATE c_membres SET protect=RAND(),adrip=RAND(),temps='$tci' WHERE protect='$k'";
			$req = mysql_query($sql) or die('Erreur SQL: '.(DEBUGSQL ? $sql.'<br />'.mysql_error() : 'voir DEBUGSQL'));
		}
		//--- Rediriger vers la connexion
		mysql_close($db_link);
		header(isset($dconm) ? 'Location:index.php' : 'Location:index.php?conm='.$chn);
		exit;
	}
?>
