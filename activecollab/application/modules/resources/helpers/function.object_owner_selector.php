<?php

  /**
   * object_owner_selector helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * Render object assignees list
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_owner_selector($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject class', true);
    } // if
    
    $language = array_var($params, 'language', $smarty->get_template_vars('current_language')); // maybe we need to print this in a specific language?
    
      $users_table = TABLE_PREFIX . 'users';
      $assignments_table = TABLE_PREFIX . 'assignments';
      $owner_exists = false;
      $rows = db_execute_all("SELECT $assignments_table.is_owner AS is_assignment_owner, $users_table.id AS user_id, $users_table.company_id, $users_table.first_name, $users_table.last_name, $users_table.email FROM $users_table, $assignments_table WHERE $users_table.id = $assignments_table.user_id AND $assignments_table.object_id = ? ORDER BY $assignments_table.is_owner DESC", $object->getId());
      if(is_foreachable($rows)) {
        $owner = null;
        $other_assignees = array();
        $users_dropdown_for_tickets = '';
        foreach($rows as $row) {
          
          if($row['is_assignment_owner']) {
			$owner_exists = true;
          }
          
	        if (empty($users_dropdown_for_tickets)){
	        	$users_dropdown_for_tickets = '<select onchange="modify_responsible_status(this);">';
	        }
	        $users_dropdown_for_tickets .= '<option value="' . $row['user_id'] . '"' . ($row['is_assignment_owner'] ? ' selected ' : '') . '>';
          	if(empty($row['first_name']) && empty($row['last_name'])) {
            	$users_dropdown_for_tickets .= clean($row['email']);
          	} else {
            	$users_dropdown_for_tickets .= clean($row['first_name'] . ' ' . $row['last_name']);
          	}
	        $users_dropdown_for_tickets .= '</option>';

        } // foreach
        }
        if ($owner_exists){
        	$users_dropdown_for_tickets .= '</select>';
        	$owner = $users_dropdown_for_tickets;
        } else {
        	$owner = '--';
        }

      
      return $owner;

  } // smarty_function_object_assignees

?>