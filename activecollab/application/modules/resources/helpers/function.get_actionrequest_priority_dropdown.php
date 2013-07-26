<?php

  /**
   * get_actionrequest_priority_dropdown helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * 
   * Parameters:
   * 
   * - user_is
   * - object
   * - name
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_get_actionrequest_priority_dropdown($params, &$smarty) {
    $user_id = array_var($params, 'user_id');
    $object_id = array_var($params, 'object_id');
    
    if (empty($object_id) || empty($user_id)){
		return 'ERROR';
	}

	$priority = '-99';
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	$query = "select priority_actionrequest from healingcrystals_assignments_flag_fyi_actionrequest where user_id='" . $user_id . "' and object_id='" . $object_id. "'";
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info = mysql_fetch_assoc($result);
		$priority = $info['priority_actionrequest'];
	}
	mysql_close($link);
	$resp = ' <select name="comment[priority_actionrequest][]" onchange="auto_select_action_request_checkbox(this);">
				<option value="' . $user_id . '_' . '-99" ' . (is_null($priority) ? ' selected ' : '') . '>-- Set Priority --</option>
			 	<option value="' . $user_id . '_' . PRIORITY_HIGHEST . '" ' . ($priority==PRIORITY_HIGHEST ? ' selected ' : '') . '>Highest Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_HIGH . '" ' . ($priority==PRIORITY_HIGH ? ' selected ' : '') . '>High Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_NORMAL . '" ' . ($priority==PRIORITY_NORMAL ? ' selected ' : '') . '>Normal Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_LOW . '" ' . ($priority==PRIORITY_LOW ? ' selected ' : '') . '>Low Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_LOWEST . '" ' . ($priority==PRIORITY_LOWEST ? ' selected ' : '') . '>Lowest Priority</option>
			 </select>';
	return $resp;
  } // smarty_function_get_actionrequest_priority_dropdown

?>