<?php

  /**
   * select_billable_status helper implementation
   *
   * @package activeCollab.modules.timetracking
   * @subpackage helpers
   */

  /**
   * Render select billable status field
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_select_billable_status($params, &$smarty) {
    $statuses = array(
      BILLABLE_STATUS_NOT_BILLABLE => lang('Not Billable'),
      BILLABLE_STATUS_BILLABLE => lang('Billable'),
      BILLABLE_STATUS_PENDING_PAYMENT => lang('Pending Payment'),
      BILLABLE_STATUS_BILLED => lang('Billed'),
    );
    
    $value = array_var($params, 'value', null, true);
    
    $options = array();
    foreach($statuses as $status => $text) {
      $options[] = option_tag($text, $status, array(
        'selected' => $value == $status,
      ));
    } // foreach
    
    return select_box($options, $params);
  } // smarty_function_select_billable_status

?>