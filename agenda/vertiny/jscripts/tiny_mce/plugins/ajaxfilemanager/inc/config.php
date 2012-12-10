<?
	/**
	 * sysem  config setting
	 * @author Logan Cai (cailongqun@yahoo.com.cn)
	 * @link www.phpletter.com
	 * @since 22/April/2007
	 *
	 */
	
	//FILESYSTEM CONFIG	

	session_start();	
	header('Content-Type: text/html; charset=utf-8');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.base.php");
	require_once(DIR_AJAX_LANGS . CONFIG_LANG_DEFAULT . ".php");
	require_once(DIR_AJAX_INC . "function.base.php");	
	require_once(dirname(__FILE__) .  DIRECTORY_SEPARATOR . "class.session.php");
	$session = new Session();


	// stratmodif
	// Diriger vers le bon répertoire via la variable "$_SESSION['lieu_admin_spec']"
	if  (isset($_SESSION['group_admin_spec']) AND $_SESSION['group_admin_spec'] == '3') // c'est un USER
	{
		$conf_sys_def_path = '../../../../../uploaded/user'.$_SESSION['lieu_admin_spec'] ; 
		$conf_sys_root_path = '../../../../../uploaded/user'.$_SESSION['lieu_admin_spec'] ; 
	}
	elseif (isset($_SESSION['group_admin_spec']) AND $_SESSION['group_admin_spec'] == '5') // c'est un SUPERADMIN
	{
		$conf_sys_def_path = '../../../../../uploaded' ; 
		$conf_sys_root_path = '../../../../../uploaded' ; 	
	}
	
	else // c'est un INCONNU
	{
		echo '<p align="center"><b><br /><br /><br />Merci de bien vouloir vous authentifier <br />
		avant d\'acceder aux r&eacute;pertoires</b></p>' ;
		exit();
		//header('location: login_page.php');
	}
	
	// -----------------------------------


	
	if(CONFIG_ACCESS_CONTROL_MODE == 1)
	{//access control enabled
		if(empty($_SESSION[CONFIG_LOGIN_INDEX]) && strtolower(basename($_SERVER['PHP_SELF']) != strtolower(basename(CONFIG_LOGIN_PAGE))))
		{//
			header('Location: ' . CONFIG_LOGIN_PAGE);
			exit;
		}
	}
	addNoCacheHeaders();


?>