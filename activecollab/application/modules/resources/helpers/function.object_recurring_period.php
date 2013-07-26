<?php

  /**
   * object_recurring_period helper
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
   */
  function smarty_function_object_recurring_period($params, &$smarty) {
  	$ticket = array_var($params, 'object');

    if(!instance_of($ticket, 'Ticket')) {
      return 'N/A';
    } // if
  	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
  	mysql_select_db(DB_NAME);
  	$query = "select * from healingcrystals_project_object_misc where object_id='" . (int)$ticket->getId() . "'";
	$result = mysql_query($query, $link);
	if (mysql_num_rows($result)){
		$info 					= mysql_fetch_assoc($result);
		$reminder_date 			= $info['reminder_date'];
		$recurring_period 		= $info['recurring_period'];
		$recurring_period_type 	= $info['recurring_period_type'];
	}
  	mysql_close($link);
	$resp = '<form name="frmRecurringPeriod" action="' . $ticket->getEditUrl() . '&mode=recurring_period_update_mode" method=post>
                    <input type="text" name="recurring_period" value="' . $recurring_period . '" maxlength="5" style="width:50px;" onchange="this.form.submit();" />&nbsp;
                    <select name="recurring_period_type" style="width:80px;" onchange="this.form.submit();">
                        <option value="D" ' . ($recurring_period_type=='D' ? ' selected ' : '') . '>Days</option>
			<option value="W" ' . ($recurring_period_type=='W' ? ' selected ' : '') . '>Weeks</option>
			<option value="M" ' . ($recurring_period_type=='M' ? ' selected ' : '') . '>Months</option>
			<option value="Y" ' . ($recurring_period_type=='Y' ? ' selected ' : '') . '>Years</option>
                    </select>
		</form>';
    return $resp;
  }

?>