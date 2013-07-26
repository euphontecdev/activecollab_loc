<?php

  /**
   * select_milestone_category helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Select milestone category control
   * 
   * Params:
   * 
   * - Commong SELECT attributes
   * - Value - Selected milestone category id
   *
   * @param array $params
   * @return string
   */
  function smarty_function_select_milestone_category($params, &$smarty) {
  	$project = array_var($params, 'project');
    $project_id = 0;
    if(instance_of($project, 'Project')) {
      $project_id = $project->getId();
    } // if
  	$categories = array();
  	$categories[] = array('id' => '0', 'text' => '-- select  --');
  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
  	mysql_select_db(DB_NAME);
  	$sql = "select id as id, category_name as text from healingcrystals_project_milestone_categories where project_id='" . do_json_encode($project_id) . "' order by category_name";
  	$result = mysql_query($sql, $link);
  	while($entry = mysql_fetch_array($result, MYSQL_ASSOC)){
  		$categories[] = $entry;
  	}
  	mysql_close($link);
    $value = 0;
    if(isset($params['value'])) {
      $value = $params['value'];
      unset($params['value']);
    } // if
    
    $options = array();
    for($i=0; $i<count($categories); $i++){
    	$option_attribites = $categories[$i]['id'] == $value ? array('selected' => true) : null;
    	$options[] = option_tag($categories[$i]['text'], $categories[$i]['id'], $option_attribites);
    }
    
    return select_box($options, $params);
  } // smarty_function_select_milestone_category

?>