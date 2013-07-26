<?php

  /**
   * select_priority helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Select priority control
   * 
   * Params:
   * 
   * - Commong SELECT attributes
   * - Value - Selected priority
   *
   * @param array $params
   * @return string
   */
  function smarty_function_select_priority($params, &$smarty) {
    $priorities = array(
	  //BOF:mod 20121107
	  PRIORITY_URGENT => lang('Urgent'),
	  //EOF:mod 20121107
      PRIORITY_HIGHEST => lang('Highest'),
      PRIORITY_HIGH    => lang('High'),
      PRIORITY_NORMAL  => lang('Normal'),
      PRIORITY_LOW     => lang('Low'),
      PRIORITY_LOWEST  => lang('Lowest'),
	  //BOF:mod 20121107
	  /*
	  //EOF:mod 20121107
      PRIORITY_ONGOING  => lang('Ongoing'),
	  //BOF:mod 20121107
	  */
	  //EOF:mod 20121107
      PRIORITY_HOLD  => lang('Hold'),
	  '-99'  => lang('Unknown'),
    );
    
	if (isset($params['task_id'])){
		$priorities_images = array(
		  PRIORITY_URGENT => 'assets/images/icons/priority/urgent.png',
		  PRIORITY_HIGHEST => 'assets/images/icons/priority/highest.gif',
		  PRIORITY_HIGH    => 'assets/images/icons/priority/high.gif',
		  PRIORITY_NORMAL  => 'assets/images/icons/priority/normal.gif',
		  PRIORITY_LOW     => 'assets/images/icons/priority/low.gif',
		  PRIORITY_LOWEST  => 'assets/images/icons/priority/lowest.gif',
		  PRIORITY_HOLD  => 'assets/images/icons/priority/hold.png',
		  '-99'  => 'assets/images/icons/priority/unknown.png',
		);
		/*$priority_not_set = false;
		if ($params['value']=='0'){
			$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
			mysql_select_db(DB_NAME, $link);
			$query = "select isnull(priority) as priority_not_set from healingcrystals_project_objects where id='" . (int)$params['task_id'] . "'";
			$result = mysql_query($query, $link);
			$info = mysql_fetch_assoc($result);
			if ($info['priority_not_set']=='1') $priority_not_set = true;
			mysql_close($link);
		}
		if (!$priority_not_set){
			$params['sel-image'] = $priorities_images[$params['value']];
    $value = 0;
    if(isset($params['value'])) {
      $value = (integer) $params['value'];
			  if($value > PRIORITY_URGENT || $value < PRIORITY_HOLD) {
				$value = 0;
			  } // if
			} // if
		} else {
			$value = '-99';
		}
		unset($params['value']);
		return '<span id="' . $params['task_id'] . '" url="' . $params['url'] . '" class="cur_priority" style="cursor:pointer;"><img class="cur_priority" src="' . $priorities_images[$value] . '" /></span>';*/
		return '<span id="' . $params['task_id'] . '" url="' . $params['url'] . '" class="cur_priority" style="cursor:pointer;"><img class="cur_priority" src="' . $priorities_images[$params['value']] . '" /></span>';
	} else {
		$value = 0;
		if(isset($params['value'])) {
		  $value = (integer) $params['value'];
      //if($value > PRIORITY_HIGHEST || $value < PRIORITY_LOWEST) {
	  //BOF:mod 20121122
	  /*
	  //EOF:mod 20121122
      if($value > PRIORITY_HIGHEST || $value < PRIORITY_HOLD) {
	  //BOF:mod 20121122
	  */
	  if($value > PRIORITY_URGENT || $value < PRIORITY_HOLD) {
	  //EOF:mod 20121122
        $value = 0;
      } // if
      unset($params['value']);
    } // if
    
    $options = array();
    foreach($priorities as $priority => $priority_text) {
      $option_attribites = $priority == $value ? array('selected' => true) : null;
      $options[] = option_tag($priority_text, $priority, $option_attribites);
    } // if
    
    return select_box($options, $params);
	}
  } // smarty_function_select_priority

?>