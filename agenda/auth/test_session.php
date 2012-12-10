<?php 
session_start();
?>

<?php 
require 'auth_fonctions.php';  


echo $_SESSION['nom_spectateur'] . '<br />';
echo $_SESSION['group_admin_spec_name'] . '<br />';



?>


