<?php

  /**
   * check_fyi_actionrequest_status helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * Render inline select assignees
   * 
   * Parameters:
   * 
   * - user_id
   * - name
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_check_fyi_actionrequest_status($params, &$smarty) {
  	/*
    $object_id = array_var($params, 'object_id');
    $user_id = array_var($params, 'user_id');
    $name = array_var($params, 'name');
    $flag_set = false;
    
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	$query = "select flag_fyi from healingcrystals_assignments_flag_fyi_actionrequest where user_id='" . $user_id . "' and object_id='" . $object_id . "'";
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info = mysql_fetch_assoc($result);
		if ($info['flag_fyi']=='1'){
			$flag_set = true;
		} 
	}
	mysql_close($link);
	return '<input type="checkbox" name="' . $name . '" value="' . $user_id . '" class="input_checkbox" ' . ($flag_set ? ' checked="true" ' : '') . ' /><span style="margin:0 5px 0 5px;font-size:10px;color:gray;">Mark for FYI</span>';
	*/
	$resp = '';
	$object_id = array_var($params, 'object_id');
	$subscriber_id = array_var($params, 'subscriber_id');
	$type = array_var($params, 'type');
        $column_name = '';
	switch ($type){
		case 'fyi':
		case 'actionrequest':
                        $column_name = 'flag_' . $type;
			break;
                case 'email':
                        $column_name = 'email_flag';
                        break;
		default:
			$type = '';			
	}
	if (!empty($object_id) && !empty($subscriber_id) && !empty($type) && !empty($column_name)){
		$flag_set = false;
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		
		$query = "select " . $column_name . " from healingcrystals_assignments_flag_fyi_actionrequest where user_id='" . $subscriber_id . "' and object_id='" . $object_id . "'";
		$result = mysql_query($query, $link);
		if (mysql_num_rows($result)){
			$info = mysql_fetch_assoc($result);
			if ($info[$column_name]=='1'){
				$flag_set = true;
			}
		}
		mysql_close($link);
		if ($flag_set){
			$resp = ' checked="true" ';
		}		
	}
	return $resp;
  } // smarty_function_check_fyi_actionrequest_status

?>