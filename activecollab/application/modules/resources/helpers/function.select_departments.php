<?php

  /**
   * select_departments helper
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
  function smarty_function_select_departments($params, &$smarty) {
  	$project = array_var($params, 'project');
    $project_id = 0;
    if(instance_of($project, 'Project')) {
      $project_id = $project->getId();
    } // if
    $object = array_var($params, 'object');
    
  	$categories = array();
  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
  	mysql_select_db(DB_NAME);
  	$sql = "select id as id, category_name as text from healingcrystals_project_milestone_categories where project_id='" . do_json_encode($project_id) . "' order by category_name";
  	$result = mysql_query($sql, $link);
  	while($entry = mysql_fetch_array($result, MYSQL_ASSOC)){
  		$categories[] = $entry;
  	}
  	//$categories[] = array('id' => '0', 'text' => 'None');
  	
  	$reg_vals = array();
  	if ($object && $object->getId()){
  		$query = "select category_id from healingcrystals_project_object_categories where object_id='" . $object->getId() . "'";
  		$result = mysql_query($query, $link);
  		while($info = mysql_fetch_assoc($result)){
  			$reg_vals[] = $info['category_id'];
  		}  		
  	}
  	
  	mysql_close($link);
    
    $params['multiple'] = 'true';
    $params['size'] = '5';
    $options = array();
    for($i=0; $i<count($categories); $i++){
    	$option_attribites = in_array($categories[$i]['id'], $reg_vals) ? array('selected' => true) : null;
    	$options[] = option_tag($categories[$i]['text'], $categories[$i]['id'], $option_attribites);
    }
    
    return select_box($options, $params);
  }

?>