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
  function smarty_function_select_priority_images($params, &$smarty) {
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
	  //BOF:mod
	  '-99'  => lang('Unknown'),
	  //EOF:mod
    );
    $priorities_images = array(
	  //BOF:mod 20121107
	  PRIORITY_URGENT => 'assets/images/icons/priority/urgent.png',
	  //EOF:mod 20121107
      PRIORITY_HIGHEST => 'assets/images/icons/priority/highest.gif',
      PRIORITY_HIGH    => 'assets/images/icons/priority/high.gif',
      PRIORITY_NORMAL  => 'assets/images/icons/priority/normal.gif',
      PRIORITY_LOW     => 'assets/images/icons/priority/low.gif',
      PRIORITY_LOWEST  => 'assets/images/icons/priority/lowest.gif',
	  //BOF:mod 20121107
	  /*
	  //EOF:mod 20121107
      PRIORITY_ONGOING  => 'assets/images/icons/priority/ongoing.png',
	  //BOF:mod 20121107
	  */
	  //EOF:mod 20121107
      PRIORITY_HOLD  => 'assets/images/icons/priority/hold.png',
	  //BOF:mod
	  '-99'  => 'assets/images/icons/priority/unknown.png',
	  //EOF:mod
    );
	//BOF:mod
	$priority_not_set = false;
	if ($params['value']=='0'){
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME, $link);
		$query = "select isnull(priority) as priority_not_set from healingcrystals_project_objects where id='" . (int)$params['name'] . "'";
		$result = mysql_query($query, $link);
		$info = mysql_fetch_Assoc($result);
		if ($info['priority_not_set']=='1') $priority_not_set = true;
		mysql_close($link);
	}
	if (!$priority_not_set){
	//EOF:mod
		$params['sel-image'] = $priorities_images[$params['value']];
		$value = 0;
		if(isset($params['value'])) {
		  $value = (integer) $params['value'];
		  //if($value > PRIORITY_HIGHEST || $value < PRIORITY_HOLD) {
		  if($value > PRIORITY_URGENT || $value < PRIORITY_HOLD) {
			$value = 0;
		  } // if
		} // if
	//BOF:mod
	} else {
		$value = '-99';
		$params['sel-image'] = $priorities_images[$value];
	}
	unset($params['value']);
	//EOF:mod
    $params['id'] = $params['name'];
	
	$obj = new ProjectObject($params['id']);
    $params['name'] = strtolower($obj->getType()) . 'priorityimages_'.$params['name'];
    $params['class'] = 'imageList';
    
    $options = array();
    $options[] = option_tag('Priority', '-', array('data-skip'=>'true'));
    foreach($priorities as $priority => $priority_text) {
      $option_attribites = $priority == $value ? array('selected' => true) : array();
      $option_attribites['data-icon'] = $priorities_images[$priority];
      $options[] = option_tag($priority_text, $priority, $option_attribites);
    } // if    
    return select_box($options, $params);
  } // smarty_function_select_priority

?>