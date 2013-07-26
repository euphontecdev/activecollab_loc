<?php
/**
*
* @param array $params
* @param Smarty $smarty
* @return string
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
        $query = "select reminder_date from healingcrystals_project_object_misc where object_id='" . $object->getId() . "'";
        $result = mysql_query($query);
        if (mysql_num_rows($result)){
            $info = mysql_fetch_assoc($result);
            if (!empty($info['reminder_date'])){
                $reminder_date = DateTimeValue::makeFromString($info['reminder_date']);
            }
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