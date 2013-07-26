<?php

  /**
   * Load and passthru captcha
   */
  
  session_start();
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  
  // Make sure that we have timezone set (PHP 5.3.0 compatibility)
  ini_set('date.timezone', 'GMT');
  if(function_exists('date_default_timezone_set')) {
    date_default_timezone_set('GMT');
  } else {
    @putenv('TZ=GMT'); // Don't throw a warning if system in safe mode
  } // if
  
  require_once '../config/config.php';
  require_once ROOT . '/angie.php';
  
  require_once ANGIE_PATH . '/functions/files.php';
  
  require_once ANGIE_PATH . '/classes/AngieObject.class.php';
  require_once ANGIE_PATH . '/classes/captcha/Captcha.class.php';

  $captcha = new Captcha(200,30);
  $captcha->Create();

?>