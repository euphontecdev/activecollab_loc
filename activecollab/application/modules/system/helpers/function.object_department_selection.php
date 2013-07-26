<?php
  function smarty_function_object_department_selection($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is not valid instance of ProjectObject class', true);
    }
	
	$select = '';
	$select = '<select onchange="modify_department_association(this);">';
	$select .= '<option value="">-- Select Department --</option>';
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	
	$selected_department_id = '';
	$query = mysql_query("select category_id from healingcrystals_project_object_categories where object_id='" . $object->getId() . "'");
	if (mysql_num_rows($query)){
		$info = mysql_fetch_assoc($query);
		$selected_department_id = $info['category_id'];
	}
	
	$query = mysql_query("select id, category_name from healingcrystals_project_milestone_categories where project_id='" . $object->getProjectId() . "' order by category_name");
	while($entry = mysql_fetch_assoc($query)){
		$select .= '<option value="' . $entry['id'] . '" ' . ($selected_department_id==$entry['id'] ? ' selected ' : '') . ' >' . $entry['category_name'] . '</option>';
	}
	mysql_close($link);
	$select .= '</select>';
    return $select;
  }
?>