<?php

  /**
   * list_objects helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Show whether checkbox is to be displayed against the object
   *
   * Parameters:
   * 
   * - objects - Array of objects that need to be listed
   * - id - Table ID, if not present ID will be generated
   * - show_header - Show table header
   * - show_star - Show star
   * - show_priority - Show star
   * - show_checkboxes - Show checkboxes column (this will also init checkboxes 
   *   behavior)
   * - show_project - Show project information
   * - del_completed - DEL completed object links
   * 
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_show_checkbox($params, &$smarty) {
  	$resp = '';
  	
    $milestone_object = array_var($params, 'milestone');
    if(!instance_of($milestone_object, 'ProjectObject')) {
      return new InvalidParamError('object', $milestone_object, '$object is not valid instance of ProjectObject class', true);
    } // if
    
    $milestone_id = $milestone_object->getId();
    $section_name = array_var($params, 'section_name');
    $is_checked = array_var($params, 'is_checked');
    
    if (!empty($milestone_id) && !empty($section_name)){
    	switch(strtolower($section_name)){
    		case 'pages':
		      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		      	mysql_select_db(DB_NAME);
		      	$query = "select count(*) as count from healingcrystals_project_objects where milestone_id='" . $milestone_id . "' and type='Page' and boolean_field_1='1' and state='3' and visibility='1'";
		      	$result = mysql_query($query, $link);
		      	$info = mysql_fetch_assoc($result);
		      	mysql_close($link);
		      	if ($info['count']){
		      		$resp = '<input type="checkbox" style="width:30px;" onclick="set_pages_view_status(this);" ' . ($is_checked ? ' checked ' : '') .  ' />See Archived Pages';
		      	}
		      	break;
    		case 'tickets':
		      	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		      	mysql_select_db(DB_NAME);
		      	$query = "select count(*) as count from healingcrystals_project_objects where milestone_id='" . $milestone_id . "' and type='Ticket' and completed_on is not null and state='3' and visibility='1'";
		      	$result = mysql_query($query, $link);
		      	$info = mysql_fetch_assoc($result);
		      	mysql_close($link);
		      	if ($info['count']){
		      		$resp = '<input type="checkbox" style="width:30px;" onclick="set_tickets_view_status(this);" ' . ($is_checked ? ' checked ' : '') .  ' />See Completed Tickets';
		      	}
		      	break;
    		default:
    			$resp = '';
    	}
    }

    return $resp;
  }

?>