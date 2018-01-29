<?php 

function autoload($class)
{
	require_once 'includes/class/' . $class . '.php';
}
spl_autoload_register('autoload');

?>