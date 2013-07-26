<?php

  /**
   * object_last_comment helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Show the last comment by user and date of the object
   *
   * Parameters:
   * 
   * - object - object
   * 
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_last_comment($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is not valid instance of ProjectObject class', true);
    } // if
    
    $resp = '';
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
        //BOF:mod 20111104
        /*
        //EOF:mod 20111104
	$query = "select created_by_id, created_on from healingcrystals_project_objects where parent_id='" . $object->getId() . "' and type='Comment' order by created_on desc limit 0, 1";
        //BOF:mod 20111104
        */
        $query = "select created_by_id, created_on from healingcrystals_project_objects where parent_id='" . $object->getId() . "' and type='Comment' and state='" . STATE_VISIBLE . "' order by created_on desc limit 0, 1";
        //EOF:mod 20111104
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info = mysql_fetch_assoc($result);
                if (!empty($info['created_by_id'])){
                    $created_on = strtotime($info['created_on']);
                    //BOF:mod 20111215
                    $offset = get_system_gmt_offset();
                    $created_on += $offset;
                    //EOF:mod 20111215
                    $user_obj = new User($info['created_by_id']);
                    //BOF:mod 20111215
                    /*
                    //EOF:mod 20111215
                    $resp = ' <a href="' . $user_obj->getViewUrl() . '">' . $user_obj->getFirstName() . '</a> ' . date('m-d', strtotime($created_on)) . ' ';
                    //BOF:mod 20111215
                    */
                    $resp = ' <a href="' . $user_obj->getViewUrl() . '">' . $user_obj->getFirstName() . '</a> ' . date('m-d', $created_on) . ' ';
                    //EOF:mod 20111215
                    unset($user_obj);
                } else {
                    $resp = '--';
                }
	} else {
		$resp = '--';
	}
	mysql_close($link);
    return $resp;
  }
?>