<?php

if(!defined('SYSTEM_VERSION')) define('SYSTEM_VERSION','1.1.2');
if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME','doJazdy');

if (! defined('SECRET_CONFIG') )		define('SECRET_CONFIG','/secret_config.php');

if (is_readable(dirname(__FILE__).SECRET_CONFIG))
{
	require_once dirname(__FILE__).SECRET_CONFIG;
}else
{
	$database['password']	= 'secret_password_to_database';
	$pin							= '121212';
}

//Configuracja bazy danych
$database['host']		= 'localhost';
$database['name']		= 'doJazdy';
$database['user']		= 'dojazdy';
?>