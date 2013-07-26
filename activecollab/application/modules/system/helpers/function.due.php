<?php

  /**
   * due helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Print due on string (due in, due today or late) for a given object
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_due($params, &$smarty) {
    $object = array_var($params, 'object');
    
    $due_date = null;
    if(instance_of($object, 'ProjectObject')) {
      if($object->can_be_completed) {
        if($object->isCompleted()) {
          return lang('Completed');
        } // if
        
        $due_date = $object->getDueOn();
      } else {
        return '--';
      } // if
    } elseif(instance_of($object, 'Invoice')) {
      if($object->getStatus() == INVOICE_STATUS_ISSUED) {
        $due_date = $object->getDueOn();
      } else {
        return '--';
      } // if
    } else {
      return new InvalidParamError('object', $object, '$object is not expected to be an instance of ProjectObject or Invoice class', true);
    } // if
      
    $offset = get_user_gmt_offset();
    
    if(instance_of($due_date, 'DateValue')) {
      require_once SMARTY_PATH . '/plugins/modifier.date.php';
      
      $date = smarty_modifier_date($due_date, 0); // just printing date, offset is 0!
      
	  $reminder_string_begining = '';
	  $reminder_string_end = '';
	  $sql = "select auto_email_status, email_reminder_period, email_reminder_unit, email_reminder_time from healingcrystals_project_object_misc where object_id=? and auto_email_status='1'";
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
		} elseif ($h==12){
			$meridian='PM';
		} elseif ($h==0){
			$meridian='AM';
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