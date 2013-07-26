<?php

  /**
   * object_owner helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Show the owner of the object
   *
   * Parameters:
   * 
   * - object - object
   * 
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_owner($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is not valid instance of ProjectObject class', true);
    } // if
    
    $resp = '';
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	mysql_select_db(DB_NAME);
	$query = "select a.user_id, b.first_name, b.last_name from healingcrystals_assignments a inner join healingcrystals_users b on a.user_id=b.id where a.object_id='" . $object->getId() . "' and a.is_owner='1'";
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info = mysql_fetch_assoc($result);
		$user_obj = new User($info['user_id']);
		//BOF:mod 20110704 ticketid215
		if (instance_of($object, 'Ticket')){
			$resp = ' <a href="' . $user_obj->getViewUrl() . '">' . $info['first_name'] . '</a>';
		} else {
		//EOF:mod 20110704 ticketid215
			$resp = ' owned by <a href="' . $user_obj->getViewUrl() . '">' . $info['first_name'] . ' ' . $info['last_name'] . '</a>';
		//BOF:mod 20110704 ticketid215
		}
		//EOF:mod 20110704 ticketid215
		unset($user_obj);
	}
	mysql_close($link);
    return $resp;
  }
?>