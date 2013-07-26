<?php

  /**
   * select_team helper
   *
   * @package activeCollab.modules.milestones
   * @subpackage helpers
   */
  
  /**
   * Render select team control
   * 
   * Params:
   * 
   * - project - Project instance that need to be used
   * - active_only - Return only active milestones, true by default
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_select_team($params, &$smarty) {
    $project = array_var($params, 'project');
    if(!instance_of($project, 'Project')) {
      return new InvalidParamError('project', $project, '$project value is expected to be an instance of Project class', true);
    } // if
    unset($params['project']);
    
    $value = null;
    if(isset($params['value'])) {
      $value = $params['value'];
      unset($params['value']);
    } // if
    
    $options = array();

    $logged_user = $smarty->get_template_vars('logged_user');
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	$query = "select a.id, a.name from healingcrystals_projects a inner join healingcrystals_project_users b on (a.id=b.project_id and b.user_id='" . $logged_user->getId() . "') where a.status='active' and a.completed_on is null and b.role_id<>'9' order by a.name";
	$result = mysql_query($query, $link);
	while ($info = mysql_fetch_assoc($result)){
		if ($info['id']==$project->getId()){
			$option_attributes = array('selected' => true);
			$options[] = option_tag(lang($info['name']), $info['id'], $option_attributes);
		} else {
			$options[] = option_tag(lang($info['name']), $info['id']);
		}
		
	}
	
	mysql_close($link);

    return select_box($options, $params);
  } // smarty_function_select_team

?>