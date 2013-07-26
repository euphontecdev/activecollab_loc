<?php

  /**
   * object_reminder helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * Render object reminder information
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   * 12 April 2012 (SA) Ticket #784: check Recurring Reminder email script in AC
   */
  function smarty_function_object_reminder($params, &$smarty) {
  	$ticket = array_var($params, 'object');

    if(!instance_of($ticket, 'Ticket')) {
      return 'N/A';
    } // if
    require_once SMARTY_PATH . '/plugins/function.select_date.php';
  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
  	mysql_select_db(DB_NAME);
  	$query = "select * from healingcrystals_project_object_misc where object_id='" . (int)$ticket->getId() . "'";
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info 					= mysql_fetch_assoc($result);
		$reminder_date 			= dateval($info['reminder_date']);
		$recurring_period 		= $info['recurring_period'];
		$recurring_period_type 	= $info['recurring_period_type'];
	}
  	mysql_close($link);
	$resp = '<form name="frmReminder" action="' . $ticket->getEditUrl() . '&mode=reminder_only_update_mode" method=post>' .
                   // smarty_function_select_date(array('name' => 'reminder', 'value' => (!empty($reminder_date) ? date('Y/m/d', strtotime($reminder_date)) : '') , 'id' => 'reminder', 'onchange' => 'this.form.submit();'), $smarty) .
                    smarty_function_select_date(array('name' => 'reminder', 'value' => (!empty($reminder_date) ? date('m/d/Y', strtotime($reminder_date)) : '') , 'id' => 'reminder', 'onchange' => 'this.form.submit();'), $smarty) .
		'</form>';
    return $resp;
  }

?>