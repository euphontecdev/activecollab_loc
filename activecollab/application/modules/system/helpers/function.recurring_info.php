<?php
function smarty_function_recurring_info($params, &$smarty) {
	$object = array_var($params, 'object');
    
    if(!instance_of($object, 'ProjectObject')) {
		return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject', true);
	}
	$info = '';
	$sql = "select recurring_period, recurring_period_type, recurring_period_condition, recurring_end_date from healingcrystals_project_object_misc where object_id=?";
	$arguments = array($object->getId());
	$sql = db_prepare_string($sql, $arguments);
	$row = db_execute_all($sql);
	if (!empty($row)){
		$entry = $row[0];
		$recurring_period = array_var($entry, 'recurring_period');
		$recurring_period_type = array_var($entry, 'recurring_period_type');
		$recurring_period_condition = array_var($entry, 'recurring_period_condition');
		$recurring_end_date = array_var($entry, 'recurring_end_date');
		
		if (!empty($recurring_period) && !empty($recurring_period_type) && $recurring_period_condition){
			$info = 'Recurring every ' . $recurring_period . ' ';
			switch($recurring_period_type){
				case 'D':
					$info .= ' day(s) ';
					break;
				case 'W':
					$info .= ' week(s) ';
					break;
				case 'M':
					$info .= ' month(s) ';
					break;
			}
			switch($recurring_period_condition){
				case 'after_due_date':
					$info .= 'after Task is Due';
					break;
				case 'after_task_complete':
					$info .= 'after Task has been Completed';
					break;
			}
			$info = '<span class="recurring">' . $info . '</span>';
		}
	}
	return $info;
	
      
    
    if(instance_of($due_date, 'DateValue')) {
      require_once SMARTY_PATH . '/plugins/modifier.date.php';
      
      $date = smarty_modifier_date($due_date, 0); // just printing date, offset is 0!
	  
	  $reminder_string_begining = '';
	  $reminder_string_end = '';
	  $sql = "select auto_email_status, email_reminder_period, email_reminder_unit, email_reminder_time from healingcrystals_project_object_misc where object_id=?";
	  $arguments = array($object->getId());
	  $sql = db_prepare_string($sql, $arguments);
	  $row = db_execute_all($sql);
	  if (!empty($row)){
		$entry = $row[0];
		$auto_email_status = array_var($entry, 'auto_email_status');
		$email_reminder_period = array_var($entry, 'email_reminder_period', '0');
		$email_reminder_unit = array_var($entry, 'email_reminder_unit', 'D');
		$email_reminder_time = array_var($entry, 'email_reminder_time', '06:00');
		$meridian = '';
		list($h, $m) = explode(':', $email_reminder_time);
		$h = (int)$h;
		if ($h>12){
			$h -= 12;
			$meridian = 'PM';
		} elseif ($h==0){
			$meridian='PM';
		} else {
			$meridian='AM';
		}
		$email_reminder_time = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . $m . ' ' . $meridian;
		$reminder_string_begining = 'Reminder set for ' . $email_reminder_period . ' ' . ($email_reminder_unit=='D' ? 'Day(s)' : ($email_reminder_unit=='W' ? 'Week(s)' : ($email_reminder_unit=='M' ? 'Month(s)' : ''))) . " from Due Date: ";
		$reminder_string_end = " at " . $email_reminder_time;
	  }
      
      if($due_date->isToday($offset)) {
		if (!empty($reminder_string_begining)){
			return '<span class="today">' . $reminder_string_begining . '<span class="number">' . lang('Today') . '</span>' . $reminder_string_end . '</span>';
		} else{
			return '<span class="today"><span class="number">' . lang('Due Today') . '</span></span>';
		}
      } elseif($due_date->isYesterday($offset)) {
		if (!empty($reminder_string_begining)){
			return '<span class="late" title="' . clean($date) . '">' . $reminder_string_begining . lang('<span class="number">1 Day Late</span>') . $reminder_string_end . '</span>';
		} else {
			return '<span class="late" title="' . clean($date) . '">' . lang('<span class="number">1 Day Late</span>') . '</span>';
		}
      } elseif($due_date->isTomorrow($offset)) {
		if (!empty($reminder_string_begining)){
			return '<span class="upcoming" title="' . clean($date) . '">' . $reminder_string_begining . '<span class="number">' . lang('Tomorrow') . '</span>' . $reminder_string_end . '</span>';
		} else {
			return '<span class="upcoming" title="' . clean($date) . '"><span class="number">' . lang('Due Tomorrow') . '</span></span>';
		}
      } else {
        $now = new DateTimeValue();
        $now->advance($offset);
        $now = $now->beginningOfDay();
        
        $due_date->beginningOfDay();
        
        if($due_date->getTimestamp() > $now->getTimestamp()) {
          //return '<span class="upcoming" title="' . clean($date) . '">' . lang('Due in <span class="number">:days</span> Days', array('days' => floor(($due_date->getTimestamp() - $now->getTimestamp()) / 86400))) . '</span>';
          //return '<span class="upcoming" title="' . clean($date) . '">' . lang('<span class="number">:days</span> Days', array('days' => floor(($due_date->getTimestamp() - $now->getTimestamp()) / 86400))) . '</span>';
		  if (!empty($reminder_string_begining)){
			return '<span class="upcoming" title="' . clean($date) . '">' . $reminder_string_begining . date('F d, Y', $due_date->getTimestamp()) . lang(' (<span class="number">:days</span> Days)', array('days' => floor(($due_date->getTimestamp() - $now->getTimestamp()) / 86400))) . $reminder_string_end . '</span>';
		  } else {
			return '<span class="upcoming" title="' . clean($date) . '">Due ' . date('F d, Y', $due_date->getTimestamp()) . lang(' (<span class="number">:days</span> Days)', array('days' => floor(($due_date->getTimestamp() - $now->getTimestamp()) / 86400))) . '</span>';
		  }
        } else {
          //return '<span class="late" title="' . clean($date) . '">' . lang('<span class="number">:days</span> Days Late', array('days' => floor(($now->getTimestamp() - $due_date->getTimestamp()) / 86400))) . '</span>';
		  if (!empty($reminder_string_begining)){
			return '<span class="late" title="' . clean($date) . '">' . $reminder_string_begining . date('F d, Y', $due_date->getTimestamp()) . lang(' (<span class="number">:days</span> Days Late)', array('days' => floor(($now->getTimestamp() - $due_date->getTimestamp()) / 86400))) . $reminder_string_end . '</span>';
		  } else {
			return '<span class="late" title="' . clean($date) . '">Due ' . date('F d, Y', $due_date->getTimestamp()) . lang(' (<span class="number">:days</span> Days Late)', array('days' => floor(($now->getTimestamp() - $due_date->getTimestamp()) / 86400))) . '</span>';
		  }
        } // if
      } // if
    } else {
      //return lang('No Due Date');
      return lang('--');
    } // if
  } // smarty_function_due

?>