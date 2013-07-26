<?php
  define('ROOT', '/home/ffbhpro/public_html/activecollab'); 
  define('PUBLIC_FOLDER_NAME', 'public'); 
  define('DB_HOST', 'localhost'); 
  define('DB_USER', 'ffbhpro_ffbhpro'); 
  define('DB_PASS', 'crystals2010'); 
  define('DB_NAME', 'ffbhpro_ac'); 
  define('DB_CAN_TRANSACT', true); 
  define('TABLE_PREFIX', 'healingcrystals_'); 
  define('ROOT_URL', 'http://projects.ffbh.org/public'); 
  define('PATH_INFO_THROUGH_QUERY_STRING', true); 
  define('FORCE_QUERY_STRING', true); 
  define('LOCALIZATION_ENABLED', false); 
  define('ADMIN_EMAIL', 'shawn@ffbh.org'); 
  define('DEBUG', 1); 
  define('API_STATUS', 1); 
  define('PROTECT_SCHEDULED_TASKS', true); 
  define('DB_CHARSET', 'utf8'); 
  
  //BOF:mod 20120809
  define('TASK_LIST_PROJECT_ID', '62');
  //EOF:mod 20120809

  require_once 'defaults.php';
  require_once 'license.php';
?>