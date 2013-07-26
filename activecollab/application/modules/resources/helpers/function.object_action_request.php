<?php

  /**
   * object_action_request helper
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
  function smarty_function_object_action_request($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject class', true);
    } // if
    
    $user = array_var($params, 'user');
    
    $language = array_var($params, 'language', $smarty->get_template_vars('current_language')); // maybe we need to print this in a specific language?
    
      $users_table = TABLE_PREFIX . 'users';
      $assignments_table = TABLE_PREFIX . 'assignments';
      $action_request_table = TABLE_PREFIX . 'assignments_action_request';
      
      //BOF:task_1260
      /*
      //EOF:task_1260
      $action_request_user_id = '';
      //BOF:task_1260
      */
      $action_request_user_id = array();
      $resp = '';
      /*
      //EOF:task_1260
      $query = db_execute_all("select $action_request_table.user_id from $action_request_table where $action_request_table.object_id=? and is_action_request='1'", $object->getId());
      //BOF:task_1260
      */
      $query = db_execute_all("select distinct $action_request_table.user_id from $action_request_table inner join healingcrystals_project_objects on $action_request_table.comment_id=healingcrystals_project_objects.id where healingcrystals_project_objects.parent_id=? and $action_request_table.selected_by_user_id=? and is_action_request='1'", $object->getId(), $user->getId());
      //EOF:task_1260
      if(is_foreachable($query)) {
      	foreach($query as $entry) {
      		//BOF:task_1260
      		/*
      		//EOF:task_1260
      		$action_request_user_id = $entry['user_id'];
      		//BOF:task_1260
      		*/
      		$action_request_user_id[] = $entry['user_id'];
      		//EOF:task_1260
      	}
      }      
      
	$rows = db_execute_all("SELECT $assignments_table.is_owner AS is_assignment_owner, $users_table.id AS user_id, $users_table.company_id, $users_table.first_name, $users_table.last_name, $users_table.email FROM $users_table, $assignments_table WHERE $users_table.id = $assignments_table.user_id AND $assignments_table.object_id = ? ORDER BY $assignments_table.is_owner DESC", $object->getId());
	if(is_foreachable($rows)) {
		//BOF:task_1260
		/*
		//EOF:task_1260
        $users_dropdown_for_tickets = '';
        //BOF:task_1260
        */
        //EOF:task_1260
        foreach($rows as $row) {
			//BOF:task_1260
			/*
			//EOF:task_1260
			if (empty($users_dropdown_for_tickets)){
	        	$users_dropdown_for_tickets = '<select onchange="modify_action_request(this);">';
	        	if (empty($action_request_user_id)){
	        		$users_dropdown_for_tickets .= '<option value="0">-- Select --</option>';
	        	}
	        }
	        $users_dropdown_for_tickets .= '<option value="' . $row['user_id'] . '"' . ($row['user_id']==$action_request_user_id ? ' selected ' : '') . '>';
          	if(empty($row['first_name']) && empty($row['last_name'])) {
            	$users_dropdown_for_tickets .= clean($row['email']);
          	} else {
            	$users_dropdown_for_tickets .= clean($row['first_name'] . ' ' . $row['last_name']);
          	}
	        $users_dropdown_for_tickets .= '</option>';
			//BOF:task_1260
			*/
			if (in_array($row['user_id'], $action_request_user_id)){
          		if(empty($row['first_name']) && empty($row['last_name'])) {
					$resp .= '<a href="' . assemble_url('project_people', array('project_id' => $object->getProjectId())) . '">' . clean($row['email'])  . '</a>, ';
          		} else {
					$resp .= '<a href="' . assemble_url('project_people', array('project_id' => $object->getProjectId())) . '">' . clean($row['first_name'] . ' ' . $row['last_name'])  . '</a>, ';
          		}
			}
			//EOF:task_1260
        } // foreach
	}
	//BOF:task_1260
	/*
	//EOF:task_1260
    if (!empty($users_dropdown_for_tickets)){
    	if (!empty($action_request_user_id)){
    		$users_dropdown_for_tickets .= '<option value="-1">Unset</option></select>';
    	}
		$resp = $users_dropdown_for_tickets;
    } else {
    	$$resp = '--';
    }
    //BOF:task_1260
    */
    if (empty($resp)){
    	$resp = '--';
    } else {
    	$resp = substr($resp, 0, -2);
    }
    //EOF:task_1260
	return $resp;

  } // smarty_function_object_assignees

?>