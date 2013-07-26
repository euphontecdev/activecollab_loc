<?php

  /**
   * date_set_format helper implementation
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */

  /**
   * date format helper
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_date_set_format($params, &$smarty) {
    $value = array_var($params, 'value');
    if (empty($value)){
    	$value = time();
    } else {
    	$value = strtotime($value);
    }
    $format = array_var($params, 'format');
    $resp = '';
    switch($format){
    	case 'mmddyyyy':
    	default:
    	$resp = date('m-d-Y', $value);
    }
    return $resp;
  } // smarty_function_date_set_format

?>