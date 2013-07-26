<?php

  /**
   * set_assignee_actionrequest_html helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * Render inline select assignees
   * 
   * Parameters:
   * 
   * - ticket_id
   * - user_id
   * - name
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_set_assignee_actionrequest_html($params, &$smarty) {
    $object_id = array_var($params, 'object_id');
    $user_id = array_var($params, 'user_id');
    $flag_set = false;
    
    $priority = -99;
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	$query = "select flag_actionrequest, priority_actionrequest from healingcrystals_assignments_flag_fyi_actionrequest where user_id='" . $user_id . "' and object_id='" . $object_id . "'";
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info = mysql_fetch_assoc($result);
		if ($info['flag_actionrequest']=='1'){
			$flag_set = true;
		}
		$priority = $info['priority_actionrequest'];
	}
	mysql_close($link);
	$resp = '&nbsp;<input type="checkbox" name="assignee[flag_actionrequest][]" value="' . $user_id . '" class="input_checkbox" ' . ($flag_set ? ' checked="true" ' : '') . '  onclick="auto_select_checkboxes(this);" />
			 <select name="assignee[priority_actionrequest][]" onchange="auto_select_checkboxes(this);" style="display:none;">
			 	<option value="' . $user_id . '_-99"' . (is_null($priority) || $priority=='-99' ? ' selected ' : '') . '>-- Set Priority --</option>
			 	<option value="' . $user_id . '_' . PRIORITY_HIGHEST . '" ' . ($priority==PRIORITY_HIGHEST ? ' selected ' : '') . '>Highest Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_HIGH . '" ' . ($priority==PRIORITY_HIGH ? ' selected ' : '') . '>High Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_NORMAL . '" ' . ($priority==PRIORITY_NORMAL ? ' selected ' : '') . '>Normal Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_LOW . '" ' . ($priority==PRIORITY_LOW ? ' selected ' : '') . '>Low Priority</option>
			 	<option value="' . $user_id . '_' . PRIORITY_LOWEST . '" ' . ($priority==PRIORITY_LOWEST ? ' selected ' : '') . '>Lowest Priority</option>
			 </select>';
	return $resp;;
  } // smarty_function_set_assignee_fyi_html

?>