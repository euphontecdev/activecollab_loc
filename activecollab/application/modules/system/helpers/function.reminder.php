<?php
/**
*
* @param array $params
* @param Smarty $smarty
* @return string
* HISTORY
* 22 May 2012 (SA) Ticket #841: check recurring task reminder script
*/
function smarty_function_reminder($params, &$smarty) {
    $object = array_var($params, 'object');
    
    $reminder_date = null;
    if(instance_of($object, 'ProjectObject')) {
        if($object->isCompleted()) {
            return '';
        } // if
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        mysql_select_db(DB_NAME, $link);
		//BOF:mod 20120904
		/*
		//EOF:mod 20120904
        $query = "select * from healingcrystals_project_object_misc where object_id='" . $object->getId() . "'";
		//BOF:mod 20120904
		*/
		$query = "select a.reminder_date, a.recurring_period, a.recurring_period_type, b.completed_on from healingcrystals_project_object_misc a inner join healingcrystals_project_objects b on a.object_id=b.id where a.object_id='" . $object->getId() . "'";
		//EOF:mod 20120904
        $result = mysql_query($query);
        if (mysql_num_rows($result)){
            $info = mysql_fetch_assoc($result);
			if (!empty($info['reminder_date']) && $info['reminder_date']!='0000-00-00 00:00:00'){
                $reminder_date = DateTimeValue::makeFromString($info['reminder_date']);
            }
			//BOF:mod 20120904
			if (!empty($info['completed_on'])){
			//EOF:mod 20120904
            if($info['recurring_period_type'] != '' && $info['recurring_period']!=''){
	            $recurring_period = $info['recurring_period'];
				$recurring_period_type = $info['recurring_period_type'];
				switch($recurring_period_type){
					case 'D':
						$recurring_period_type = 'day' .  ($recurring_period==1 ? '' : 's');
						break;
					case 'W':
						$recurring_period_type = 'week' .  ($recurring_period==1 ? '' : 's');
						break;
					case 'M':
						$recurring_period_type = 'month' .  ($recurring_period==1 ? '' : 's');
						break;
					case 'Y':
						$recurring_period_type = 'year' .  ($recurring_period==1 ? '' : 's');
						break;
				}
				$datetime = new DateTime();
				$datetime->modify('+' . $recurring_period . ' ' . $recurring_period_type);
				//BOF:mod 20120703
				if (!empty($reminder_date)){
					$temp_date = $reminder_date;
				}
				//EOF:mod 20120703
				$reminder_date = DateTimeValue::makeFromString($datetime->format('Y-m-d H:00'));
				//BOF:mod 20120703
				if (!empty($temp_date) && $temp_date->getTimestamp()>=time()){
					$reminder_date = $temp_date;
				}
				//EOF:mod 20120703
            }
			//BOF:mod 20120904
			}
			//EOF:mod 20120904
        }
        mysql_close($link);
    } else {
        return new InvalidParamError('object', $object, '$object is not expected to be an instance of ProjectObject', true);
    } // if
      
    $offset = get_user_gmt_offset();
    
    if(instance_of($reminder_date, 'DateTimeValue')) {
        require_once SMARTY_PATH . '/plugins/modifier.datetime.php';
      
        $date = smarty_modifier_datetime($reminder_date, 0); // just printing date, offset is 0!
      
        if($reminder_date->isToday($offset)) {
            return '<span class="today"><span class="number">Reminder set for: ' . lang('Today') . ' ' . date('h:i A', $reminder_date->getTimestamp()) .  '</span></span>';
        } elseif($reminder_date->isYesterday($offset)) {
            return '<span class="late" title="' . clean($date) . '">Reminder set for: ' . lang('<span class="number">Yesterday ' . date('h:i A', $reminder_date->getTimestamp()) . '</span>') . '</span>';
        } elseif($reminder_date->isTomorrow($offset)) {
            return '<span class="upcoming" title="' . clean($date) . '">Reminder set for: <span class="number">' . lang('Tomorrow') . ' ' . date('h:i A', $reminder_date->getTimestamp()) . '</span></span>';
        } else {
            $now = new DateTimeValue();
            $now->advance($offset);
            $now = $now->beginningOfDay();
        
            $reminder_date->beginningOfDay();
        
            if($reminder_date->getTimestamp() > $now->getTimestamp()) {
                return '<span class="upcoming" title="' . clean($date) . '">Reminder set for: ' . date('F d, Y h:i A', $reminder_date->getTimestamp()) . lang(' (<span class="number">:days</span> Days)', array('days' => floor(($reminder_date->getTimestamp() - $now->getTimestamp()) / 86400))) . '</span>';
            } else {
                return '<span class="late" title="' . clean($date) . '">Reminder set for: ' . date('F d, Y h:i A', $reminder_date->getTimestamp()) . lang(' (<span class="number">:days</span> Days Late)', array('days' => floor(($now->getTimestamp() - $reminder_date->getTimestamp()) / 86400))) . '</span>';
            } // if
        } // if
    } else {
        return '';
    } // if
}
?>