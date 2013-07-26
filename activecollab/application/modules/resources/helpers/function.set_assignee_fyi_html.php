<?php

  /**
   * set_assignee_fyi_html helper
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
  function smarty_function_set_assignee_fyi_html($params, &$smarty) {
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
	return '&nbsp;<input type="checkbox" name="assignee[flag_fyi][]" value="' . $user_id . '" class="input_checkbox" ' . ($flag_set ? ' checked="true" ' : '') . '  onclick="auto_select_checkboxes(this);" />';
  } // smarty_function_set_assignee_fyi_html

?>