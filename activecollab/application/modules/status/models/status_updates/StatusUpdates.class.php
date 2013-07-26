<?php

  /**
   * StatusUpdates class
   * 
   * @package activeCollab.modules.status
   * @subpackage models
   */
  class StatusUpdates extends BaseStatusUpdates {
    
    /**
     * Return status updates created by $user
     *
     * @param User $user
     * @param integer $limit
     * @return array
     */
    function findByUser($user, $limit = null) {
      return StatusUpdates::find(array(
    	  'conditions' => array('(parent_id IS NULL OR parent_id = ?) AND created_by_id = ?', 0, $user->getID()),
    	  'order' => 'last_update_on DESC',
      ));
    } // findByUser
  
    /**
     * Return status updates that are visible to provided user
     *
     * @param User $user
     * @param boolean $include_himself if true include users status updates in result
     * @return array
     */
    function findVisibleForUser($user, $limit = null) {     
      $criteria = array(
    	  'conditions' => array('(parent_id IS NULL OR parent_id = ?) AND created_by_id IN (?)', 0, $user->visibleUserIds()),
    	  'order' => 'last_update_on DESC',
    	); // if
    	
    	if ($limit) {
    	  $criteria['limit'] = $limit;
    	} // if
    	
    	return StatusUpdates::find($criteria);
    } // findActiveByUser
    
    /**
     * Return messages by user ID-s
     *
     * @param array $user_ids
     * @return array
     */
    function findByUserIds($user_ids = null) {
      $status_updates_table = TABLE_PREFIX . 'status_updates';
      $status_update_ids = array();
      
      $rows = db_execute_all("SELECT id, parent_id FROM $status_updates_table WHERE created_by_id IN (?)", $user_ids);
      if(is_foreachable($rows)) {
        foreach($rows as $row) {
          if($row['parent_id'] == 0) {
            $status_update_ids[] = (integer) $row['id'];
          } else {
            $parent_id = (integer) $row['parent_id'];
            if(!in_array($parent_id, $status_update_ids)) {
              $status_update_ids[] = $parent_id;
            } // if
          } // if
        } // foreach
      } // if
      
      return StatusUpdates::find(array(
        'conditions' => array('id IN (?)', $status_update_ids),
        'order' => 'last_update_on DESC',
      ));
    } // findByUserIds
    
    /**
     * Return paginated status updates by user
     *
     * @param User $user
     * @param integer $page
     * @param integer $per_page
     * @return array
     */
    function paginateByUser($user, $page = 1, $per_page = 30) {
      return StatusUpdates::paginateByUserIds(array($user->getId()), $page, $per_page);
    } // paginateByProject
    
    /**
     * Return paginated status updates for user ids
     *
     * @param array $user_ids
     * @param integer $page
     * @param integer $per_page
     * @return array
     */
    function paginateByUserIds($user_ids = null, $page = 1, $per_page = 30) {
      $status_updates_table = TABLE_PREFIX . 'status_updates';
      $status_update_ids = array();
      
      $rows = db_execute_all("SELECT id, parent_id FROM $status_updates_table WHERE created_by_id IN (?)", $user_ids);
      if(is_foreachable($rows)) {
        foreach($rows as $row) {
          if($row['parent_id'] == 0) {
            $status_update_ids[] = (integer) $row['id'];
          } else {
            $parent_id = (integer) $row['parent_id'];
            if(!in_array($parent_id, $status_update_ids)) {
              $status_update_ids[] = $parent_id;
            } // if
          } // if
        } // foreach
      } // if
      
      return StatusUpdates::paginate(array(
        'conditions' => array('id IN (?)', $status_update_ids),
        'order' => 'last_update_on DESC',
      ), $page, $per_page);
    } // paginateByUserIds
    
    /**
     * Count new messages since date for provided user
     *
     * @param User $user
     * @param date $date
     * @return integer
     */
    function countNewMessagesForUser($user, $date) {
      return (integer) StatusUpdates::count(array(
        "created_by_id IN (?) AND created_on > ?", $user->visibleUserIds(), $date
      ));
    } // countNewMessages
    
    /**
     * Find replies to given parent message
     *
     * @param StatusUpdate $parent
     * @return array
     */
    function findByParent($parent) {
      return StatusUpdates::find(array(
        'conditions' => array('parent_id = ?', $parent->getId()),
        'order' => 'created_on'
      ));
    } // findByParent
    
    /**
     * Return number of replies for a given parent
     *
     * @param StatusUpdate $parent
     * @return integer
     */
    function countByParent($parent) {
      return StatusUpdates::count(array('parent_id = ?', $parent->getId()));
    } // countByParent
    
    /**
     * Drop all status messages by parent
     *
     * @param StatusUpdate $parent
     * @return boolean
     */
    function dropByParent($parent) {
      return StatusUpdates::delete(array('parent_id = ?', $parent->getId()));
    } // dropByParent
    
  }

?>