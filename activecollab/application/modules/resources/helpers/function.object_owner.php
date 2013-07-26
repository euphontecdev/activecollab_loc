<?php

  /**
   * object_owner helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * Render object assignees list
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_owner($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject class', true);
    } // if
    
      $users_table = TABLE_PREFIX . 'users';
      $assignments_table = TABLE_PREFIX . 'assignments';
      
      $rows = db_execute_all("SELECT $assignments_table.is_owner AS is_assignment_owner, $users_table.id AS user_id, $users_table.company_id, $users_table.first_name, $users_table.last_name, $users_table.email FROM $users_table, $assignments_table WHERE $users_table.id = $assignments_table.user_id AND $assignments_table.object_id = ? and $assignments_table.is_owner='1' ORDER BY $assignments_table.is_owner DESC", $object->getId());
      if(is_foreachable($rows)) {
        $owner = null;
        
        foreach($rows as $row) {
          if(empty($row['first_name']) && empty($row['last_name'])) {
            $user_link = '<a href="' . assemble_url('people_company', array('company_id' => $row['company_id'])) . '#user' . $row['user_id'] . '">' . clean($row['email'])  . '</a>';
          } else {
            $user_link = '<a href="' . assemble_url('people_company', array('company_id' => $row['company_id'])) . '#user' . $row['user_id'] . '">' . clean($row['first_name'])  . '</a>';
          } // if
          $owner .= $user_link . '&nbsp;';
        } // foreach
      } // if
      
      if(empty($owner)) {
        $owner = '--';
      } // if
     
      return $owner;
  } // smarty_function_object_owner

?>