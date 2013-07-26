<?php

define('PUBLIC_PATH', dirname(__FILE__));

$config_file = realpath(PUBLIC_PATH . '/../config/config.php');

 if(is_file($config_file)) {
    require_once($config_file);
    ini_set("default_charset", "UTF-8");
	ini_set("error_reporting", "");
	
    require ROOT . '/angie.php';
    require ANGIE_PATH . '/init.php';
    
    
    $application =& application();
  	$application->prepare(array(
	    'initialize_resources' => true,
	    'connect_to_database'  => true,
	    'initialize_smarty'    => true,
	    'init_modules'         => true,
	    'authenticate'         => true,
	    'init_locale'          => true,
	    'load_hooks'           => true,
  	));//array
    
    $application->init();
    include_once WEBDAV_MODULE_PATH.'/models/authenticate.php';
    $not_installed_modules = Modules::findNotInstalled();
    foreach ($not_installed_modules as $module) {
	    if ($module->name == 'webdav') {
	    	header("HTTP/1.1 503 Service Unavailable");
	    	die('Module is not installed');
	    }
    }
    if (!ConfigOptions::getValue('webdav_enabled')) {
    	header("HTTP/1.1 503 Service Unavailable");
	    die('Module has been disabled by administrator');
    }//if
    
	// Activate if your PHP is CGI mode
	$phpcgi = 0;
	$realm = 'activeCollab webdav area';
	$user = AuthenticationBasicHTTP($realm);
	require_once WEBDAV_MODULE_PATH . '/models/filesystem.php';
    $server = new HTTP_WebDAV_Server_Filesystem();
    $server->ServeRequest(false,$user);
	
 } else {
    header("HTTP/1.1 503 Service Unavailable");
    die();
 } // if
?>