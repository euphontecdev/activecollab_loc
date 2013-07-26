<?php
function smarty_function_is_recurring_task($params, &$smarty) {
	$is_recurring_task = false;
	
	$task = array_var($params, 'task');	
	if(instance_of($task, 'Task')) {
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		
		$sql = "select * from healingcrystals_project_object_misc where object_id='" . $task->getId() . "'";
		$result = mysql_query($sql, $link);
		if (mysql_num_rows($result)){
			$entry = mysql_fetch_assoc($result);
			if (!empty($entry['recurring_period']) && !empty($entry['recurring_period_type']) && !empty($entry['recurring_period_condition']) ){
				if (empty($entry['recurring_end_date']) || $entry['recurring_end_date']=='0000-00-00'){
					$is_recurring_task = true;
				} elseif (strtotime($entry['recurring_end_date'])>= time() ) {
					$is_recurring_task = true;
				}
			}
		}
		mysql_close($link);
    }
	return $is_recurring_task ? '<img src="assets/images/icons/priority/ongoing.png" />' : '&nbsp;';
}
?>